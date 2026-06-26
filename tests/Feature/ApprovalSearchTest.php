<?php

namespace Tests\Feature;

use App\Models\Approval;
use App\Models\CreditNote;
use App\Models\DebitNote;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * Regression cover for the approval-listing global search.
 *
 * The Type column ("Sale Order", "Credit Note", ...) is rendered client-side
 * from object_type and never stored on the row, so the original search — which
 * only matched the morphed object's `sku` — could never match a type name
 * (e.g. typing "sale" returned nothing). getData() now also resolves the
 * keyword against the human Type labels via a $type_matchers table; this test
 * locks that behaviour in through the Credit/Debit note matchers, which share
 * the exact same code path as the Sale Order / Sale Enquiry ones.
 */
class ApprovalSearchTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * getData() checks every approval.type_* permission, and Spatie throws on an
     * unknown permission name, so all of them must exist for the query to run.
     * Granting them all means nothing is filtered out by type-permission gating.
     */
    private function managerSeeingAllTypes()
    {
        $perms = [
            'approval.view',
            'approval.type_quotation', 'approval.type_sale_order', 'approval.type_delivery_order',
            'approval.type_customer', 'approval.type_payment_record', 'approval.type_raw_material_request',
            'approval.type_complete_production_request', 'approval.type_grn', 'approval.type_sale_enquiry',
            'approval.production_material_transfer_request', 'approval.type_credit_debit_note',
            'approval.type_invoice_return',
        ];
        foreach ($perms as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $user = \App\Models\User::factory()->create();
        $user->givePermissionTo($perms);

        return $user->fresh('roles');
    }

    private function searchSkus(string $keyword): \Illuminate\Support\Collection
    {
        return collect(
            $this->getJson(route('approval.get_data', ['search' => ['value' => $keyword]]))
                ->assertOk()
                ->json('data')
        )->pluck('object_sku');
    }

    public function test_searching_a_type_label_matches_rows_of_that_type(): void
    {
        $creditNote = CreditNote::create(['sku' => 'CN-SEARCH' . uniqid(), 'status' => CreditNote::STATUS_PENDING]);
        Approval::create([
            'object_type' => CreditNote::class,
            'object_id' => $creditNote->id,
            'status' => Approval::STATUS_PENDING_APPROVAL,
            'data' => json_encode(['is_note' => true, 'note_kind' => 'credit', 'description' => 'Credit note request.']),
        ]);

        $this->actingAs($this->managerSeeingAllTypes());

        // "credit" matches the "Credit Note" type label even though the keyword
        // appears nowhere in the note's SKU.
        $this->assertContains($creditNote->sku, $this->searchSkus('credit'));
        $this->assertContains($creditNote->sku, $this->searchSkus('Credit Note'));
    }

    public function test_searching_a_different_type_label_excludes_unrelated_rows(): void
    {
        $creditNote = CreditNote::create(['sku' => 'CN-EXCL' . uniqid(), 'status' => CreditNote::STATUS_PENDING]);
        Approval::create([
            'object_type' => CreditNote::class,
            'object_id' => $creditNote->id,
            'status' => Approval::STATUS_PENDING_APPROVAL,
            'data' => json_encode(['is_note' => true, 'note_kind' => 'credit', 'description' => 'Credit note request.']),
        ]);

        $debitNote = DebitNote::create(['sku' => 'DN-EXCL' . uniqid(), 'status' => DebitNote::STATUS_PENDING]);
        Approval::create([
            'object_type' => DebitNote::class,
            'object_id' => $debitNote->id,
            'status' => Approval::STATUS_PENDING_APPROVAL,
            'data' => json_encode(['is_note' => true, 'note_kind' => 'debit', 'description' => 'Debit note request.']),
        ]);

        $this->actingAs($this->managerSeeingAllTypes());

        // "debit" matches "Debit Note" only — the credit note must not leak in.
        $skus = $this->searchSkus('debit');
        $this->assertContains($debitNote->sku, $skus);
        $this->assertNotContains($creditNote->sku, $skus);
    }

    public function test_searching_the_description_text_matches(): void
    {
        $creditNote = CreditNote::create(['sku' => 'CN-DESC' . uniqid(), 'status' => CreditNote::STATUS_PENDING]);
        $needle = 'ReturnedFaultyCompressor' . uniqid();
        Approval::create([
            'object_type' => CreditNote::class,
            'object_id' => $creditNote->id,
            'status' => Approval::STATUS_PENDING_APPROVAL,
            'data' => json_encode(['is_note' => true, 'note_kind' => 'credit', 'description' => $needle . ' adjustment.']),
        ]);

        $this->actingAs($this->managerSeeingAllTypes());

        // The Description column is backed by data->description, so searching the
        // description text must surface the row.
        $this->assertContains($creditNote->sku, $this->searchSkus($needle));
    }

    public function test_searching_an_object_sku_still_matches(): void
    {
        $creditNote = CreditNote::create(['sku' => 'CN-SKU' . uniqid(), 'status' => CreditNote::STATUS_PENDING]);
        Approval::create([
            'object_type' => CreditNote::class,
            'object_id' => $creditNote->id,
            'status' => Approval::STATUS_PENDING_APPROVAL,
            'data' => json_encode(['is_note' => true, 'note_kind' => 'credit', 'description' => 'Credit note request.']),
        ]);

        $this->actingAs($this->managerSeeingAllTypes());

        // The pre-existing SKU search path must keep working alongside the new
        // type-label matching.
        $this->assertContains($creditNote->sku, $this->searchSkus($creditNote->sku));
    }
}
