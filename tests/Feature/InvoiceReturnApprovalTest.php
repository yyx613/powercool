<?php

namespace Tests\Feature;

use App\Http\Controllers\InvoiceReturnController;
use App\Models\Approval;
use App\Models\Invoice;
use App\Models\Branch;
use App\Models\Product;
use App\Models\ReturnProduct;
use App\Models\Scopes\BranchScope;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * Covers the admin-approval gate added in front of invoice return creation:
 *  - submitting a return now requires a reason and creates a PENDING Approval
 *    instead of immediately writing return_products / deleting product children
 *  - approving the request creates the actual return
 *  - rejecting it leaves nothing behind (nothing was applied)
 *  - still-pending returns lock the items they cover so the same unit can't be
 *    submitted twice before a decision
 *  - the approval listing is gated by approval.type_invoice_return
 *
 * The full delivery-order graph (product-child force-deletion, the product
 * selection screen) is exercised through manual verification; here the locking
 * maths is covered directly on the controller helpers.
 */
class InvoiceReturnApprovalTest extends TestCase
{
    use DatabaseTransactions;

    private function userWith(array $permissions): User
    {
        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }
        $user = User::factory()->create();
        foreach ($permissions as $perm) {
            $user->givePermissionTo($perm);
        }

        return $user->fresh('roles');
    }

    private function makeInvoice(): Invoice
    {
        return Invoice::create([
            'sku' => 'INV-RET' . uniqid(),
            'filename' => 'inv-ret.pdf',
        ]);
    }

    private function invoiceReturnApproval(int $invoiceId, array $products, string $reason = 'Damaged on delivery'): Approval
    {
        return Approval::create([
            'object_type' => Invoice::class,
            'object_id' => $invoiceId,
            'status' => Approval::STATUS_PENDING_APPROVAL,
            'data' => json_encode([
                'is_invoice_return' => true,
                'invoice_id' => $invoiceId,
                'products' => $products,
                'reason' => $reason,
                'description' => 'Invoice Return request.',
            ]),
        ]);
    }

    public function test_submitting_without_a_reason_is_rejected(): void
    {
        $invoice = $this->makeInvoice();

        $this->actingAs($this->userWith(['sale.invoice_return.view']));

        $this->post(route('invoice_return.product_selection_submit', ['inv' => $invoice->id]), [
            'products' => json_encode([['is_raw_material' => true, 'id' => 1, 'qty' => 2]]),
            'reason' => '   ',
        ])->assertSessionHasErrors('reason');

        // Nothing is created when the reason is missing.
        $this->assertEquals(0, Approval::withoutGlobalScope(BranchScope::class)
            ->where('object_type', Invoice::class)->where('object_id', $invoice->id)->count());
        $this->assertEquals(0, ReturnProduct::where('invoice_id', $invoice->id)->count());
    }

    public function test_submitting_creates_a_pending_approval_without_creating_the_return(): void
    {
        $invoice = $this->makeInvoice();

        $this->actingAs($this->userWith(['sale.invoice_return.view']));

        $this->withSession(['as_branch' => Branch::LOCATION_KL])
            ->post(route('invoice_return.product_selection_submit', ['inv' => $invoice->id]), [
                'products' => json_encode([['is_raw_material' => true, 'id' => 7, 'qty' => 3]]),
                'reason' => 'Customer changed mind',
            ])->assertRedirect(route('invoice_return.index'))->assertSessionHas('success');

        $approval = Approval::withoutGlobalScope(BranchScope::class)
            ->where('object_type', Invoice::class)->where('object_id', $invoice->id)->first();

        $this->assertNotNull($approval);
        $this->assertEquals(Approval::STATUS_PENDING_APPROVAL, $approval->status);

        $payload = json_decode($approval->data, true);
        $this->assertTrue($payload['is_invoice_return']);
        $this->assertEquals('Customer changed mind', $payload['reason']);

        // The actual return is NOT created until approved.
        $this->assertEquals(0, ReturnProduct::where('invoice_id', $invoice->id)->count());
    }

    public function test_approving_creates_the_return(): void
    {
        $invoice = $this->makeInvoice();
        $approval = $this->invoiceReturnApproval($invoice->id, [
            ['is_raw_material' => true, 'id' => 42, 'qty' => 4],
        ]);

        $this->actingAs($this->userWith(['approval.view']));

        $this->get(route('approval.approve', ['approval' => $approval]))
            ->assertOk()->assertJson(['result' => true]);

        $approval->refresh();
        $this->assertEquals(Approval::STATUS_APPROVED, $approval->status);

        // The return row now exists with the approved quantity.
        $this->assertDatabaseHas('return_products', [
            'invoice_id' => $invoice->id,
            'object_type' => Product::class,
            'object_id' => 42,
            'qty' => 4,
        ]);
    }

    public function test_rejecting_does_not_create_the_return(): void
    {
        $invoice = $this->makeInvoice();
        $approval = $this->invoiceReturnApproval($invoice->id, [
            ['is_raw_material' => true, 'id' => 99, 'qty' => 1],
        ]);

        $this->actingAs($this->userWith(['approval.view']));

        $this->post(route('approval.reject', ['approval' => $approval]), [
            'remark' => 'Return not justified',
        ])->assertOk()->assertJson(['result' => true]);

        $approval->refresh();
        $this->assertEquals(Approval::STATUS_REJECTED, $approval->status);
        $this->assertEquals('Return not justified', $approval->reject_remark);

        // Still nothing returned.
        $this->assertEquals(0, ReturnProduct::where('invoice_id', $invoice->id)->count());
    }

    public function test_pending_approvals_lock_their_items(): void
    {
        $invoice = $this->makeInvoice();

        // Two pending approvals against the invoice plus one already approved
        // (the approved one is not a "pending lock" and must be ignored here).
        $this->invoiceReturnApproval($invoice->id, [['is_raw_material' => true, 'id' => 5, 'qty' => 2]]);
        $this->invoiceReturnApproval($invoice->id, [['is_raw_material' => false, 'id' => 8]]);
        $approved = $this->invoiceReturnApproval($invoice->id, [['is_raw_material' => true, 'id' => 9, 'qty' => 1]]);
        $approved->update(['status' => Approval::STATUS_APPROVED]);

        $controller = new InvoiceReturnController;
        $method = new \ReflectionMethod($controller, 'pendingReturnItems');
        $method->setAccessible(true);

        $items = $method->invoke($controller, $invoice->id);

        // Only the two pending payloads are collected, flattened into one list.
        $this->assertCount(2, $items);
        $ids = array_column($items, 'id');
        $this->assertContains(5, $ids);
        $this->assertContains(8, $ids);
        $this->assertNotContains(9, $ids);
    }

    public function test_deduct_returned_reduces_qty_and_marks_children(): void
    {
        // A raw-material line with 10 left and a serialised line with one child.
        $rawMaterial = (object) [
            'is_raw_material' => true,
            'qty' => 10,
            'product' => (object) ['id' => 5],
            'children' => [],
        ];
        $child = (object) ['id' => 8];
        $serialised = (object) [
            'is_raw_material' => false,
            'qty' => null,
            'product' => (object) ['id' => 6],
            'children' => [$child],
        ];
        $products = [$rawMaterial, $serialised];

        $controller = new InvoiceReturnController;
        $method = new \ReflectionMethod($controller, 'deductReturned');
        $method->setAccessible(true);

        // Raw material: 10 - 3 = 7 left.
        $method->invokeArgs($controller, [&$products, true, 5, 3]);
        $this->assertEquals(7, $products[0]->qty);

        // Product child: gets marked selected (hidden from the selectable list).
        $method->invokeArgs($controller, [&$products, false, 8, 0]);
        $this->assertTrue($products[1]->children[0]->selected);
    }

    public function test_approval_listing_is_gated_by_invoice_return_permission(): void
    {
        // getData() checks every approval.type_* permission; Spatie throws on an
        // unknown permission name, so they must all exist for the query to run.
        foreach ([
            'approval.type_quotation', 'approval.type_sale_order', 'approval.type_delivery_order',
            'approval.type_customer', 'approval.type_payment_record', 'approval.type_raw_material_request',
            'approval.type_complete_production_request', 'approval.type_grn', 'approval.type_sale_enquiry',
            'approval.production_material_transfer_request', 'approval.type_credit_debit_note',
            'approval.type_invoice_return',
        ] as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $invoice = $this->makeInvoice();
        $this->invoiceReturnApproval($invoice->id, [['is_raw_material' => true, 'id' => 1, 'qty' => 1]]);

        // Without the permission the invoice-return request is hidden.
        $this->actingAs($this->userWith(['approval.view']));
        $skus = collect($this->getJson(route('approval.get_data'))->assertOk()->json('data'))->pluck('object_sku');
        $this->assertNotContains($invoice->sku, $skus);

        // With it, the request shows up.
        $this->actingAs($this->userWith(['approval.view', 'approval.type_invoice_return']));
        $skus = collect($this->getJson(route('approval.get_data'))->assertOk()->json('data'))->pluck('object_sku');
        $this->assertContains($invoice->sku, $skus);
    }
}
