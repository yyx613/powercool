<?php

namespace Tests\Feature;

use App\Models\Approval;
use App\Models\CreditNote;
use App\Models\DebitNote;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * Locks in server-side sorting of the approval-listing "Type" column (idx 1).
 *
 * The Type column shows a human label derived client-side from object_type, but
 * the underlying sortable column is `object_type` on the approvals table. This
 * test creates two approvals with distinct object_type classes and asserts the
 * returned rows are ordered by object_type asc & desc.
 */
class ListingSortApprovalTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * getData() checks every approval.type_* permission, and Spatie throws on an
     * unknown permission name, so all of them must exist for the query to run.
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

    /**
     * Creates one CreditNote-backed and one DebitNote-backed approval whose SKUs
     * share a unique token, so a single search keyword isolates exactly these two
     * rows onto one page (the listing hard-paginates at 10).
     *
     * @return string the shared search token
     */
    private function makeTwoApprovals(): string
    {
        $token = 'SORTTYPE' . uniqid();

        $creditNote = CreditNote::create(['sku' => 'CN-' . $token, 'status' => CreditNote::STATUS_PENDING]);
        Approval::create([
            'object_type' => CreditNote::class,
            'object_id' => $creditNote->id,
            'status' => Approval::STATUS_PENDING_APPROVAL,
            'data' => json_encode(['is_note' => true, 'note_kind' => 'credit', 'description' => 'Credit note request.']),
        ]);

        $debitNote = DebitNote::create(['sku' => 'DN-' . $token, 'status' => DebitNote::STATUS_PENDING]);
        Approval::create([
            'object_type' => DebitNote::class,
            'object_id' => $debitNote->id,
            'status' => Approval::STATUS_PENDING_APPROVAL,
            'data' => json_encode(['is_note' => true, 'note_kind' => 'debit', 'description' => 'Debit note request.']),
        ]);

        return $token;
    }

    /**
     * Pull the returned 'type' values, ordered by the listing, restricted to our
     * two rows via the shared search token.
     */
    private function orderedTypes(string $token, string $dir): array
    {
        return collect(
            $this->getJson(route('approval.get_data', [
                'search' => ['value' => $token],
                'order' => [['column' => 1, 'dir' => $dir]],
            ]))->assertOk()->json('data')
        )->pluck('type')->values()->all();
    }

    public function test_type_column_sorts_ascending_by_object_type(): void
    {
        $token = $this->makeTwoApprovals();
        $this->actingAs($this->managerSeeingAllTypes());

        $types = $this->orderedTypes($token, 'asc');

        // Exactly our two rows come back, CreditNote ("App\Models\C...") before
        // DebitNote ("App\Models\D...") ascending.
        $this->assertSame([CreditNote::class, DebitNote::class], $types);
    }

    public function test_type_column_sorts_descending_by_object_type(): void
    {
        $token = $this->makeTwoApprovals();
        $this->actingAs($this->managerSeeingAllTypes());

        $types = $this->orderedTypes($token, 'desc');

        // Descending flips the order: DebitNote comes before CreditNote.
        $this->assertSame([DebitNote::class, CreditNote::class], $types);
    }

    /**
     * Creates three CreditNote-backed approvals sharing a unique SKU token (so a
     * single search isolates them onto one page) with distinct json description
     * values and distinct created_at timestamps. Descriptions are picked so
     * alphabetical order differs from created_at order, proving the sort acts on
     * the description, not insertion order.
     *
     * @return array{0:string,1:array<string>} [searchToken, [oldestDesc, middleDesc, newestDesc]]
     */
    private function makeThreeDescribedApprovals(): array
    {
        $token = 'SORTDESC' . uniqid();

        $rows = [
            ['desc' => $token . ' C oldest', 'created_at' => now()->subMinutes(30)],
            ['desc' => $token . ' A middle', 'created_at' => now()->subMinutes(20)],
            ['desc' => $token . ' B newest', 'created_at' => now()->subMinutes(10)],
        ];

        foreach ($rows as $i => $row) {
            $note = CreditNote::create(['sku' => 'CN-' . $token . '-' . $i, 'status' => CreditNote::STATUS_PENDING]);
            Approval::create([
                'object_type' => CreditNote::class,
                'object_id' => $note->id,
                'status' => Approval::STATUS_PENDING_APPROVAL,
                'data' => json_encode(['is_note' => true, 'description' => $row['desc']]),
                'created_at' => $row['created_at'],
                'updated_at' => $row['created_at'],
            ]);
        }

        return [$token, [$rows[0]['desc'], $rows[1]['desc'], $rows[2]['desc']]];
    }

    private function orderedDescriptions(string $token, string $dir, int $column): array
    {
        return collect(
            $this->getJson(route('approval.get_data', [
                'search' => ['value' => $token],
                'order' => [['column' => $column, 'dir' => $dir]],
            ]))->assertOk()->json('data')
        )->pluck('description')->values()->all();
    }

    public function test_description_column_sorts_ascending(): void
    {
        [$token, [$oldest, $middle, $newest]] = $this->makeThreeDescribedApprovals();
        $this->actingAs($this->managerSeeingAllTypes());

        $descs = $this->orderedDescriptions($token, 'asc', 4);

        // Alphabetical asc: "A middle", "B newest", "C oldest".
        $this->assertSame([$middle, $newest, $oldest], $descs);
    }

    public function test_description_column_sorts_descending(): void
    {
        [$token, [$oldest, $middle, $newest]] = $this->makeThreeDescribedApprovals();
        $this->actingAs($this->managerSeeingAllTypes());

        $descs = $this->orderedDescriptions($token, 'desc', 4);

        // Alphabetical desc: "C oldest", "B newest", "A middle".
        $this->assertSame([$oldest, $newest, $middle], $descs);
    }

    public function test_no_column_sort_runs_and_uses_created_at_order(): void
    {
        [$token, [$oldest, $middle, $newest]] = $this->makeThreeDescribedApprovals();
        $this->actingAs($this->managerSeeingAllTypes());

        // Col 0 maps to created_at. Asc => oldest first.
        $ascDescs = $this->orderedDescriptions($token, 'asc', 0);
        $this->assertSame([$oldest, $middle, $newest], $ascDescs);

        // Desc => newest first.
        $descDescs = $this->orderedDescriptions($token, 'desc', 0);
        $this->assertSame([$newest, $middle, $oldest], $descDescs);
    }

    /** SKU (idx 2) sorts by the morphed object's sku, resolved per object_type. */
    public function test_sku_column_sorts_by_object_sku(): void
    {
        $this->actingAs($this->managerSeeingAllTypes());

        $token = 'APSKU'.uniqid();
        // Create in scrambled order so id-order can't mask a real sku sort.
        foreach (['C', 'A', 'B'] as $s) {
            $note = CreditNote::create(['sku' => 'CN-'.$token.'-'.$s, 'status' => CreditNote::STATUS_PENDING]);
            Approval::create([
                'object_type' => CreditNote::class,
                'object_id'   => $note->id,
                'status'      => Approval::STATUS_PENDING_APPROVAL,
                'data'        => json_encode(['is_note' => true, 'description' => 'd '.$s]),
            ]);
        }

        $asc = collect($this->getJson(route('approval.get_data', [
            'search' => ['value' => $token],
            'order'  => [['column' => 2, 'dir' => 'asc']],
        ]))->assertOk()->json('data'))->pluck('object_sku')->all();
        $this->assertSame(['CN-'.$token.'-A', 'CN-'.$token.'-B', 'CN-'.$token.'-C'], $asc);

        $desc = collect($this->getJson(route('approval.get_data', [
            'search' => ['value' => $token],
            'order'  => [['column' => 2, 'dir' => 'desc']],
        ]))->assertOk()->json('data'))->pluck('object_sku')->all();
        $this->assertSame(['CN-'.$token.'-C', 'CN-'.$token.'-B', 'CN-'.$token.'-A'], $desc);
    }
}
