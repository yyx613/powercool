<?php

namespace Tests\Feature;

use App\Models\AdhocService;
use App\Models\AgentDebtor;
use App\Models\Dealer;
use App\Models\InventoryType;
use App\Models\Milestone;
use App\Models\SalesAgent;
use App\Models\Service;
use App\Models\Setting;
use App\Models\Supplier;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role as SpatieRole;
use Tests\TestCase;

/**
 * Sweep that proves every data column on the settings/listing pages is server-side
 * sortable: each data-column index answers 200 for an order request (smoke), and the
 * columns wired in this branch return the seeded rows in the requested asc/desc order
 * (faithfulness).
 *
 * The dev MySQL database already holds real records, so each faithfulness check seeds a
 * couple of rows sharing a unique token and search-filters down to just those rows before
 * asserting their relative order. The user is branch-less and not a super admin, so
 * BranchScope is skipped and every seeded row is visible.
 */
class ListingSortSweepSettingsBTest extends TestCase
{
    use DatabaseTransactions;

    /** Branch-less user holding the given permission(s). */
    private function userWith(array $permissions): User
    {
        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $role = SpatieRole::firstOrCreate(['name' => 'Sort Sweep Tester', 'guard_name' => 'web']);
        $role->givePermissionTo($permissions);

        $user = User::factory()->create();
        $user->assignRole($role);

        return $user->fresh('roles');
    }

    /** Hit a get_data route ordering by the given column index (optionally search-filtered). */
    private function order(User $user, string $route, int $col, string $dir = 'asc', ?string $search = null)
    {
        $params = ['order' => [['column' => $col, 'dir' => $dir]]];
        if ($search !== null) {
            $params['search'] = ['value' => $search];
        }

        return $this->actingAs($user)->getJson(route($route, $params));
    }

    /** Ordered list of one field from a get_data JSON response. */
    private function column($response, string $field): array
    {
        return collect($response->json('data'))->pluck($field)->all();
    }

    /** Smoke: every data-column index answers 200 (asc + desc). */
    private function smokeIndices(User $user, string $route, array $dataIndices): void
    {
        foreach ($dataIndices as $col) {
            $this->order($user, $route, $col)->assertOk();
            $this->order($user, $route, $col, 'desc')->assertOk();
        }
    }

    /**
     * Assert that the two seeded rows (identified by $token in $field) come back in the
     * expected relative order for asc, and reversed for desc.
     */
    private function assertFaithful(User $user, string $route, int $col, string $field, string $token, string $first): void
    {
        $asc = $this->column($this->order($user, $route, $col, 'asc', $token), $field);
        $desc = $this->column($this->order($user, $route, $col, 'desc', $token), $field);

        $this->assertCount(2, $asc, "Expected exactly the 2 seeded rows for {$route}");
        $this->assertSame($first, $asc[0], "Wrong asc order for {$route}");
        $this->assertSame(array_reverse($asc), $desc, "desc should reverse asc for {$route}");
    }

    public function test_setting_columns_sortable(): void
    {
        $user = $this->userWith(['setting.tax_rate.view']);
        $t = 'SORTSWEEP'.uniqid();

        Setting::create(['key' => 'k1_'.uniqid(), 'name' => $t.' ZZZ', 'value' => '1']);
        Setting::create(['key' => 'k2_'.uniqid(), 'name' => $t.' AAA', 'value' => '9']);

        $this->smokeIndices($user, 'setting.get_data', [0, 1]);
        $this->assertFaithful($user, 'setting.get_data', 0, 'name', $t, $t.' AAA');
    }

    public function test_service_columns_sortable(): void
    {
        $user = $this->userWith(['setting.service.view']);
        $t = 'SORTSWEEP'.uniqid();

        Service::create(['name' => $t.' ZZZ', 'amount' => 10, 'is_active' => 1]);
        Service::create(['name' => $t.' AAA', 'amount' => 99, 'is_active' => 1]);

        $this->smokeIndices($user, 'service.get_data', [0, 1, 2]);
        $this->assertFaithful($user, 'service.get_data', 0, 'name', $t, $t.' AAA');
    }

    public function test_adhoc_service_columns_sortable(): void
    {
        $user = $this->userWith(['adhoc_service.view']);
        $t = 'SORTSWEEP'.uniqid();

        AdhocService::create(['sku' => 'ADZ'.uniqid(), 'name' => $t.' ZZZ', 'min_amount' => 5, 'is_active' => 1]);
        AdhocService::create(['sku' => 'ADA'.uniqid(), 'name' => $t.' AAA', 'min_amount' => 50, 'is_active' => 1]);

        $this->smokeIndices($user, 'adhoc_service.get_data', [0, 1, 2, 3, 4]);
        $this->assertFaithful($user, 'adhoc_service.get_data', 1, 'name', $t, $t.' AAA');
    }

    public function test_ticket_columns_sortable(): void
    {
        $user = $this->userWith(['ticket.view']);
        $t = 'SORTSWEEP'.uniqid();

        $customer = \App\Models\Customer::create(['status' => 1, 'company_name' => 'Ticket Cust '.uniqid()]);

        Ticket::create(['customer_id' => $customer->id, 'last_touch_by' => $user->id, 'sku' => 'TKZ'.uniqid(), 'subject' => $t.' ZZZ', 'is_active' => 1, 'body' => 'x']);
        Ticket::create(['customer_id' => $customer->id, 'last_touch_by' => $user->id, 'sku' => 'TKA'.uniqid(), 'subject' => $t.' AAA', 'is_active' => 1, 'body' => 'x']);

        $this->smokeIndices($user, 'ticket.get_data', [0, 1, 2, 3]);
        $this->assertFaithful($user, 'ticket.get_data', 1, 'subject', $t, $t.' AAA');
    }

    public function test_sales_agent_columns_sortable(): void
    {
        $user = $this->userWith(['setting.sales_agent.view']);
        $t = 'SORTSWEEP'.uniqid();

        SalesAgent::create(['name' => $t.' ZZZ', 'company_group' => 1]);
        SalesAgent::create(['name' => $t.' AAA', 'company_group' => 2]);

        $this->smokeIndices($user, 'sales_agent.get_data', [0, 1]);
        $this->assertFaithful($user, 'sales_agent.get_data', 0, 'name', $t, $t.' AAA');
    }

    public function test_agent_debtor_columns_sortable(): void
    {
        $user = $this->userWith(['agent_debtor.view']);
        $t = 'SORTSWEEP'.uniqid();

        AgentDebtor::create(['sku' => 'AGZ'.uniqid(), 'company_name' => $t.' ZZZ', 'phone' => '011', 'address' => 'b', 'dealer_id' => 1]);
        AgentDebtor::create(['sku' => 'AGA'.uniqid(), 'company_name' => $t.' AAA', 'phone' => '019', 'address' => 'a', 'dealer_id' => 1]);

        $this->smokeIndices($user, 'agent_debtor.get_data', [0, 1, 2, 3]);
        $this->assertFaithful($user, 'agent_debtor.get_data', 1, 'company_name', $t, $t.' AAA');
    }

    public function test_supplier_columns_sortable(): void
    {
        $user = $this->userWith(['supplier.view']);
        $t = 'SORTSWEEP'.uniqid();

        Supplier::create(['sku' => 'SPZ'.uniqid(), 'name' => $t.' ZZZ', 'phone' => '011', 'company_name' => 'ZCo', 'company_group' => 1, 'is_active' => 1]);
        Supplier::create(['sku' => 'SPA'.uniqid(), 'name' => $t.' AAA', 'phone' => '019', 'company_name' => 'ACo', 'company_group' => 2, 'is_active' => 1]);

        // index 0 is the select-all checkbox column (non-sortable); data columns are 1..5
        $this->smokeIndices($user, 'supplier.get_data', [1, 2, 3, 4, 5]);
        $this->assertFaithful($user, 'supplier.get_data', 2, 'name', $t, $t.' AAA');
    }

    public function test_dealer_columns_sortable(): void
    {
        $user = $this->userWith(['dealer.view']);
        $t = 'SORTSWEEP'.uniqid();

        Dealer::create(['sku' => 'DLZ'.uniqid(), 'name' => $t.' ZZZ', 'company_name' => 'ZCo', 'company_group' => 1]);
        Dealer::create(['sku' => 'DLA'.uniqid(), 'name' => $t.' AAA', 'company_name' => 'ACo', 'company_group' => 2]);

        $this->smokeIndices($user, 'dealer.get_data', [0, 1, 2, 3]);
        $this->assertFaithful($user, 'dealer.get_data', 1, 'name', $t, $t.' AAA');
    }

    public function test_target_columns_sortable(): void
    {
        $user = $this->userWith(['sale.target.view']);

        // No seeding needed: prove each data-column index answers 200.
        $this->smokeIndices($user, 'target.get_data', [0, 1, 2]);
    }

    public function test_vehicle_columns_sortable(): void
    {
        $user = $this->userWith(['vehicle.view']);

        $this->smokeIndices($user, 'vehicle.get_data', range(0, 13));
    }

    public function test_warranty_columns_sortable_including_warranty_date(): void
    {
        $user = $this->userWith(['warranty.view']);

        // The warranty list is built from a deep join chain that is impractical to seed
        // here; assert every data-column index (incl. the newly wired Warranty Date at 5)
        // answers 200 with the new orderBy expression in place.
        $this->smokeIndices($user, 'warranty.get_data', [0, 1, 2, 3, 4, 5]);
    }

    public function test_milestone_inventory_type_sort_is_faithful(): void
    {
        $user = $this->userWith(['setting.milestone.view']);
        $t = 'SORTSWEEP'.uniqid();

        $zType = InventoryType::create(['name' => $t.' ZZZ', 'is_active' => 1, 'company_group' => 1, 'type' => 1]);
        $aType = InventoryType::create(['name' => $t.' AAA', 'is_active' => 1, 'company_group' => 1, 'type' => 1]);

        Milestone::create(['type' => Milestone::TYPE_PRODUCTION, 'name' => 'MS Z', 'inventory_category_id' => '1', 'inventory_type_id' => $zType->id, 'batch' => 990001]);
        Milestone::create(['type' => Milestone::TYPE_PRODUCTION, 'name' => 'MS A', 'inventory_category_id' => '1', 'inventory_type_id' => $aType->id, 'batch' => 990002]);

        // Category (index 0) stays non-sortable by design; Inventory Type (index 1) sorts.
        $this->order($user, 'milestone.get_data', 1, 'asc', $t)->assertOk();

        // Search keyword matches the inventory type name, isolating just our two batches.
        $asc = $this->column($this->order($user, 'milestone.get_data', 1, 'asc', $t), 'type');
        $desc = $this->column($this->order($user, 'milestone.get_data', 1, 'desc', $t), 'type');

        $this->assertSame([$t.' AAA', $t.' ZZZ'], $asc, 'Inventory Type should sort by name asc');
        $this->assertSame([$t.' ZZZ', $t.' AAA'], $desc, 'Inventory Type should sort by name desc');
    }
}
