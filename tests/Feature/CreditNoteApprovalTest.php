<?php

namespace Tests\Feature;

use App\Models\Approval;
use App\Models\Branch;
use App\Models\CreditNote;
use App\Models\Customer;
use App\Models\DebitNote;
use App\Models\EInvoice;
use App\Models\Sale;
use App\Models\SaleProduct;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * Covers the admin-approval gate added in front of credit/debit note creation:
 *  - submitting a note no longer edits invoice line items or calls MyInvois;
 *    it creates a PENDING note + a pending Approval instead
 *  - rejecting the approval marks the note 'rejected' (nothing was applied)
 *  - the approval listing is gated by approval.type_credit_note / _debit_note
 *
 * The happy-path approval (which replays the request, generates the XML and
 * submits to MyInvois) is exercised through manual verification because of the
 * FK-heavy invoice/delivery-order graph and live PDF rendering it requires.
 */
class CreditNoteApprovalTest extends TestCase
{
    use DatabaseTransactions;

    private function fakeMyInvois(): void
    {
        // Block all real network. The EInvoiceController constructor fetches an
        // access token on instantiation, so the token endpoint must respond.
        Http::fake([
            '*connect/token*' => Http::response(['access_token' => 'test-token', 'expires_in' => 3600], 200),
            '*' => Http::response([], 200),
        ]);
    }

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

    /**
     * Minimal graph the request phase needs: an e-invoice (looked up by uuid)
     * and a sale product (whose qty/price the request wants to change).
     */
    private function seedInvoiceAndProduct(): array
    {
        $customer = Customer::create([
            'sku' => '300-CNT' . uniqid(),
            'company_name' => 'Credit Note Test Debtor',
        ]);

        $sale = Sale::create([
            'sku' => 'SO-CNT' . uniqid(),
            'type' => Sale::TYPE_SO,
            'customer_id' => $customer->id,
            'status' => Sale::STATUS_ACTIVE,
        ]);

        $saleProduct = SaleProduct::create([
            'sale_id' => $sale->id,
            'product_id' => 1,
            'qty' => 10,
            'unit_price' => 100,
        ]);

        $eInvoice = EInvoice::create([
            'uuid' => 'EINV-CNT' . uniqid(),
        ]);

        return [$customer, $sale, $saleProduct, $eInvoice];
    }

    private function notePayload(EInvoice $eInvoice, SaleProduct $saleProduct, int $newQty = 5): array
    {
        return [
            'invoices' => [
                [
                    'invoice_uuid' => $eInvoice->uuid,
                    'items' => [
                        [
                            'product_id' => $saleProduct->id,
                            'qty' => $newQty,
                            'price' => 100,
                        ],
                    ],
                ],
            ],
        ];
    }

    public function test_submitting_a_credit_note_creates_pending_note_and_approval_without_submitting(): void
    {
        $this->fakeMyInvois();

        [$customer, $sale, $saleProduct, $eInvoice] = $this->seedInvoiceAndProduct();

        $user = $this->userWith([]);
        $this->actingAs($user);

        $response = $this->withSession([
            'note_type' => 'credit',
            'invoice_type' => 'eInvoice',
            'fromBilling' => false,
            'company' => 'powercool',
            'as_branch' => Branch::LOCATION_KL,
        ])->post(route('submit.note'), $this->notePayload($eInvoice, $saleProduct));

        $response->assertOk();
        $response->assertJson(['pending_approval' => true]);

        // A pending note exists, but it was NOT submitted to MyInvois.
        // (Tests run against the shared dev DB, so identify THIS test's note via
        // its freshly-created e-invoice rather than CreditNote::first().)
        $note = $eInvoice->creditNotes()->first();
        $this->assertNotNull($note);
        $this->assertEquals(CreditNote::STATUS_PENDING, $note->status);
        $this->assertEmpty($note->uuid);

        // The e-invoice was attached to the pending note.
        $this->assertDatabaseHas('credit_note_e_invoice', [
            'credit_note_id' => $note->id,
            'einvoice_id' => $eInvoice->id,
        ]);

        // A pending Approval row was raised against the note.
        $approval = Approval::withoutGlobalScope(\App\Models\Scopes\BranchScope::class)
            ->where('object_type', CreditNote::class)
            ->where('object_id', $note->id)
            ->first();
        $this->assertNotNull($approval);
        $this->assertEquals(Approval::STATUS_PENDING_APPROVAL, $approval->status);
        $this->assertStringContainsString('is_note', $approval->data);

        // Line items must NOT be mutated until approved.
        $saleProduct->refresh();
        $this->assertEquals(10, $saleProduct->qty);

        // Nothing was sent to the government submission endpoint.
        Http::assertNotSent(fn ($request) => str_contains($request->url(), 'documentsubmissions'));
    }

    public function test_submitting_a_debit_note_creates_pending_debit_note(): void
    {
        $this->fakeMyInvois();

        [$customer, $sale, $saleProduct, $eInvoice] = $this->seedInvoiceAndProduct();

        $user = $this->userWith([]);
        $this->actingAs($user);

        $this->withSession([
            'note_type' => 'debit',
            'invoice_type' => 'eInvoice',
            'fromBilling' => false,
            'company' => 'powercool',
            'as_branch' => Branch::LOCATION_KL,
        ])->post(route('submit.note'), $this->notePayload($eInvoice, $saleProduct))
            ->assertOk()
            ->assertJson(['pending_approval' => true]);

        $note = $eInvoice->debitNotes()->first();
        $this->assertNotNull($note);
        $this->assertEquals(DebitNote::STATUS_PENDING, $note->status);

        $this->assertDatabaseHas('approvals', [
            'object_type' => DebitNote::class,
            'object_id' => $note->id,
            'status' => Approval::STATUS_PENDING_APPROVAL,
        ]);
    }

    public function test_submitting_with_no_changes_does_not_create_a_note(): void
    {
        $this->fakeMyInvois();

        [$customer, $sale, $saleProduct, $eInvoice] = $this->seedInvoiceAndProduct();

        $user = $this->userWith([]);
        $this->actingAs($user);

        // Same qty/price as the existing product -> nothing to change.
        $this->withSession([
            'note_type' => 'credit',
            'invoice_type' => 'eInvoice',
            'fromBilling' => false,
            'company' => 'powercool',
            'as_branch' => Branch::LOCATION_KL,
        ])->post(route('submit.note'), $this->notePayload($eInvoice, $saleProduct, 10))
            ->assertOk()
            ->assertJson(['message' => 'Nothing to Change!']);

        // No note was created/attached for this test's e-invoice.
        $this->assertEquals(0, $eInvoice->creditNotes()->count());
    }

    public function test_rejecting_a_credit_note_marks_it_rejected(): void
    {
        $note = CreditNote::create(['sku' => 'CN-REJ' . uniqid(), 'status' => CreditNote::STATUS_PENDING]);

        $approval = Approval::create([
            'object_type' => CreditNote::class,
            'object_id' => $note->id,
            'status' => Approval::STATUS_PENDING_APPROVAL,
            'data' => json_encode(['is_note' => true, 'note_kind' => 'credit', 'description' => 'Credit note request.']),
        ]);

        $manager = $this->userWith(['approval.view']);
        $this->actingAs($manager);

        $this->post(route('approval.reject', ['approval' => $approval]), [
            'remark' => 'Adjustment not justified',
        ])->assertOk()->assertJson(['result' => true]);

        $note->refresh();
        $this->assertEquals(CreditNote::STATUS_REJECTED, $note->status);
        $this->assertEmpty($note->uuid);

        $approval->refresh();
        $this->assertEquals(Approval::STATUS_REJECTED, $approval->status);
        $this->assertEquals('Adjustment not justified', $approval->reject_remark);
    }

    public function test_rejecting_a_debit_note_marks_it_rejected(): void
    {
        $note = DebitNote::create(['sku' => 'DN-REJ' . uniqid(), 'status' => DebitNote::STATUS_PENDING]);

        $approval = Approval::create([
            'object_type' => DebitNote::class,
            'object_id' => $note->id,
            'status' => Approval::STATUS_PENDING_APPROVAL,
            'data' => json_encode(['is_note' => true, 'note_kind' => 'debit', 'description' => 'Debit note request.']),
        ]);

        $manager = $this->userWith(['approval.view']);
        $this->actingAs($manager);

        $this->post(route('approval.reject', ['approval' => $approval]), [
            'remark' => 'Not approved',
        ])->assertOk();

        $note->refresh();
        $this->assertEquals(DebitNote::STATUS_REJECTED, $note->status);
    }

    public function test_approval_listing_is_gated_by_credit_debit_note_permission(): void
    {
        // getData() checks every approval.type_* permission; Spatie throws on an
        // unknown permission name, so they must all exist for the query to run.
        foreach ([
            'approval.type_quotation', 'approval.type_sale_order', 'approval.type_delivery_order',
            'approval.type_customer', 'approval.type_payment_record', 'approval.type_raw_material_request',
            'approval.type_complete_production_request', 'approval.type_grn', 'approval.type_sale_enquiry',
            'approval.production_material_transfer_request', 'approval.type_credit_debit_note',
        ] as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $creditNote = CreditNote::create(['sku' => 'CN-GATE' . uniqid(), 'status' => CreditNote::STATUS_PENDING]);
        Approval::create([
            'object_type' => CreditNote::class,
            'object_id' => $creditNote->id,
            'status' => Approval::STATUS_PENDING_APPROVAL,
            'data' => json_encode(['is_note' => true, 'note_kind' => 'credit', 'description' => 'Credit note request.']),
        ]);

        $debitNote = DebitNote::create(['sku' => 'DN-GATE' . uniqid(), 'status' => DebitNote::STATUS_PENDING]);
        Approval::create([
            'object_type' => DebitNote::class,
            'object_id' => $debitNote->id,
            'status' => Approval::STATUS_PENDING_APPROVAL,
            'data' => json_encode(['is_note' => true, 'note_kind' => 'debit', 'description' => 'Debit note request.']),
        ]);

        // The list is newest-first, so these just-created notes land on page 1.
        // The single approval.type_credit_debit_note permission gates BOTH; without
        // it neither SKU appears, with it both do. (Asserted by SKU, not absolute
        // counts, since tests share the dev DB with other approvals.)
        $withoutPerm = $this->userWith(['approval.view']);
        $this->actingAs($withoutPerm);
        $skus = collect($this->getJson(route('approval.get_data'))->assertOk()->json('data'))
            ->pluck('object_sku');
        $this->assertNotContains($creditNote->sku, $skus);
        $this->assertNotContains($debitNote->sku, $skus);

        $withPerm = $this->userWith(['approval.view', 'approval.type_credit_debit_note']);
        $this->actingAs($withPerm);
        $skus = collect($this->getJson(route('approval.get_data'))->assertOk()->json('data'))
            ->pluck('object_sku');
        $this->assertContains($creditNote->sku, $skus);
        $this->assertContains($debitNote->sku, $skus);
    }
}
