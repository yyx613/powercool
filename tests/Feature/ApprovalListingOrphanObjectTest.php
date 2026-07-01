<?php

namespace Tests\Feature;

use App\Models\Approval;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * The approval listing (ApprovalController::getData) must not crash when an
 * approval's referenced object no longer exists (e.g. the invoice was deleted
 * after the approval was raised). Previously several get_class($obj) calls ran
 * without a null guard and a single orphaned approval 500'd the whole listing.
 */
class ApprovalListingOrphanObjectTest extends TestCase
{
    use DatabaseTransactions;

    /** getData() probes every approval.type_* permission; Spatie throws on an
     *  unknown permission name, so they must all exist for the query to run. */
    private function userWithApprovalAccess(): User
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
        $user = User::factory()->create();
        $user->givePermissionTo($perms);

        return $user->fresh('roles');
    }

    public function test_listing_does_not_crash_on_an_approval_whose_object_was_deleted(): void
    {
        // An invoice-return approval pointing at an invoice id that does not exist.
        $approval = Approval::create([
            'object_type' => Invoice::class,
            'object_id' => 2000000000,
            'status' => Approval::STATUS_PENDING_APPROVAL,
            'data' => json_encode([
                'is_invoice_return' => true,
                'invoice_id' => 2000000000,
                'reason' => 'orphan',
                'description' => 'Invoice Return request for a since-deleted invoice.',
            ]),
        ]);

        $this->actingAs($this->userWithApprovalAccess());

        // The whole listing must still return 200 (it used to 500 here).
        $rows = $this->getJson(route('approval.get_data'))->assertOk()->json('data');

        // The orphaned row is rendered defensively: its type is null, not a crash.
        $row = collect($rows)->firstWhere('id', $approval->id);
        $this->assertNotNull($row, 'orphaned approval should still be listed');
        $this->assertNull($row['type']);
        $this->assertNull($row['view_url']);
    }
}
