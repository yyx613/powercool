<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\Factory;
use App\Models\InventoryCategory;
use App\Models\State;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * Settings listing sort sweep (batch A).
 *
 * Covers the two pages whose Factory / Country columns previously sorted by the
 * raw FK id instead of the displayed related name. They are now wired to a
 * correlated subquery so the sort matches what the user sees:
 *   - inventory_category/list : "Factory" column (idx 2)
 *   - state/list              : "Country" column (idx 2)
 *
 * All other settings listings in scope were already fully sortable (every data
 * column present in the controller order $map, only the trailing action column
 * non-orderable) so they need no change and are not exercised here.
 *
 * A branchless, non-superadmin user is used throughout: BranchScope is a no-op
 * for such users, so freshly created rows are visible without branch assignment.
 */
class ListingSortSweepSettingsATest extends TestCase
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

        return $user;
    }

    private function fetch(string $route, array $params, int $column, string $dir): array
    {
        return collect($this->getJson(route($route, array_merge($params, [
            'order' => [['column' => $column, 'dir' => $dir]],
        ])))->assertOk()->json('data'))->all();
    }

    // ---------------------------------------------------------------------
    // inventory_category/list  (data columns: 0 name, 1 company group, 2 factory, 3 status)
    // ---------------------------------------------------------------------

    public function test_inventory_category_each_data_column_returns_200(): void
    {
        $this->actingAs($this->userWith(['inventory.category.view']));

        foreach ([0, 1, 2, 3] as $col) {
            $this->getJson(route('inventory_category.get_data', [
                'order' => [['column' => $col, 'dir' => 'asc']],
            ]))->assertOk();
        }
    }

    public function test_inventory_category_factory_column_sorts_by_factory_name(): void
    {
        $this->actingAs($this->userWith(['inventory.category.view']));
        $kw = 'INVCATSORT'.uniqid();

        // Factory names deliberately ordered opposite to their ids so a faithful
        // name sort differs from the old FK-id sort.
        $fZ = Factory::create(['name' => 'ZZZ '.$kw]);
        $fA = Factory::create(['name' => 'AAA '.$kw]);

        $catWithZ = InventoryCategory::create(['name' => $kw.'-withZ', 'company_group' => 1, 'factory' => $fZ->id, 'is_active' => 1]);
        $catWithA = InventoryCategory::create(['name' => $kw.'-withA', 'company_group' => 1, 'factory' => $fA->id, 'is_active' => 1]);

        $asc = collect($this->fetch('inventory_category.get_data', ['search' => ['value' => $kw]], 2, 'asc'))->pluck('factory')->all();
        $this->assertSame([$fA->name, $fZ->name], $asc);

        $desc = collect($this->fetch('inventory_category.get_data', ['search' => ['value' => $kw]], 2, 'desc'))->pluck('factory')->all();
        $this->assertSame([$fZ->name, $fA->name], $desc);
    }

    // ---------------------------------------------------------------------
    // state/list  (data columns: 0 name, 1 code, 2 country, 3 status)
    // ---------------------------------------------------------------------

    public function test_state_each_data_column_returns_200(): void
    {
        $this->actingAs($this->userWith(['setting.state.view']));

        foreach ([0, 1, 2, 3] as $col) {
            $this->getJson(route('state.get_data', [
                'order' => [['column' => $col, 'dir' => 'asc']],
            ]))->assertOk();
        }
    }

    public function test_state_country_column_sorts_by_country_name(): void
    {
        $this->actingAs($this->userWith(['setting.state.view']));
        $kw = 'STATESORT'.uniqid();

        // Country names ordered opposite to ids.
        $cZ = Country::create(['name' => 'ZZZ '.$kw, 'code' => 'Z'.substr(uniqid(), -4), 'is_active' => 1]);
        $cA = Country::create(['name' => 'AAA '.$kw, 'code' => 'A'.substr(uniqid(), -4), 'is_active' => 1]);

        State::create(['name' => $kw.'-inZ', 'code' => 'SZ'.substr(uniqid(), -3), 'country_id' => $cZ->id, 'is_active' => 1]);
        State::create(['name' => $kw.'-inA', 'code' => 'SA'.substr(uniqid(), -3), 'country_id' => $cA->id, 'is_active' => 1]);

        $asc = collect($this->fetch('state.get_data', ['search' => ['value' => $kw]], 2, 'asc'))->pluck('country')->all();
        $this->assertSame([$cA->name, $cZ->name], $asc);

        $desc = collect($this->fetch('state.get_data', ['search' => ['value' => $kw]], 2, 'desc'))->pluck('country')->all();
        $this->assertSame([$cZ->name, $cA->name], $desc);
    }
}
