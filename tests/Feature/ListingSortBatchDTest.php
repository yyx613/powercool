<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\InventoryCategory;
use App\Models\InventoryType;
use App\Models\MaterialUse;
use App\Models\MaterialUseProduct;
use App\Models\Milestone;
use App\Models\Product;
use App\Models\ProductCost;
use App\Models\Production;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role as SpatieRole;
use Tests\TestCase;

/**
 * Faithful server-side sort sweep for four listings whose sort keys must reproduce
 * the DISPLAYED cell value (not a naive column):
 *
 *   - Production Finish Good : Qty (completed-production count), Status, Created By
 *   - User Management        : Role (comma-joined spatie roles), Branch (morph label)
 *   - B.O.M Material Use      : Product ('(sku) model_desc' coalesce), Avg Cost aggregate
 *   - Milestone              : Inventory Type (related name, not id); Category stays
 *                              non-sortable by design (FIND_IN_SET comma list)
 *
 * Each faithfulness case seeds rows where a NAIVE sort key would order differently
 * from the displayed value, then search-filters down to just those rows. The user is
 * branchless and not a super admin, so BranchScope is a no-op and seeded rows are
 * visible.
 */
class ListingSortBatchDTest extends TestCase
{
    use DatabaseTransactions;

    /** Branchless user holding the given permission(s). */
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

    /** Hit a get_data route ordering by $column; returns the plucked field list. */
    private function ordered(string $route, array $params, int $column, string $dir, string $pluck): array
    {
        return collect(
            $this->getJson(route($route, array_merge($params, [
                'order' => [['column' => $column, 'dir' => $dir]],
            ])))->assertOk()->json('data')
        )->pluck($pluck)->values()->all();
    }

    private function makeProduct(string $sku, int $type, string $modelDesc, array $attrs = []): Product
    {
        $cat = InventoryCategory::create(['name' => 'BatchDCat '.uniqid(), 'company_group' => 1, 'is_active' => 1]);

        return Product::create(array_merge([
            'inventory_category_id' => $cat->id,
            'type'                  => $type,
            'sku'                   => $sku,
            'model_desc'            => $modelDesc,
            'is_active'             => 1,
            'min_price'             => 0,
            'max_price'             => 0,
            'qty'                   => 0,
        ], $attrs));
    }

    // =====================================================================
    // Production Finish Good  (is_product=true, is_production=true)
    // =====================================================================

    /** Smoke: every data-column index answers 200 in the finish-good production mode. */
    public function test_production_finish_good_each_data_column_returns_200(): void
    {
        $this->actingAs($this->userWith(['production_material.view']));

        // After the per-mode splices: 0 sku, 1 model, 2 category, 3 qty, 4 status, 5 created by.
        foreach ([0, 1, 2, 3, 4, 5] as $col) {
            $this->getJson(route('production_finish_good.get_data', [
                'is_product'    => 'true',
                'is_production' => 'true',
                'order'         => [['column' => $col, 'dir' => 'asc']],
            ]))->assertOk();
        }
    }

    /**
     * Qty (idx 3) shows the number of COMPLETED productions for the product. Seed two
     * finish goods with 1 vs 2 completed productions so a faithful key orders by that count.
     */
    public function test_production_finish_good_qty_sorts_faithfully(): void
    {
        $this->actingAs($this->userWith(['production_material.view']));
        $kw = 'PFGQTY'.uniqid();

        $one = $this->makeProduct($kw.'-ONE', Product::TYPE_PRODUCT, $kw.' one');
        $two = $this->makeProduct($kw.'-TWO', Product::TYPE_PRODUCT, $kw.' two');

        Production::create(['product_id' => $one->id, 'sku' => 'PRD'.uniqid(), 'name' => 'p', 'start_date' => now(), 'due_date' => now(), 'status' => Production::STATUS_COMPLETED]);
        Production::create(['product_id' => $two->id, 'sku' => 'PRD'.uniqid(), 'name' => 'p', 'start_date' => now(), 'due_date' => now(), 'status' => Production::STATUS_COMPLETED]);
        Production::create(['product_id' => $two->id, 'sku' => 'PRD'.uniqid(), 'name' => 'p', 'start_date' => now(), 'due_date' => now(), 'status' => Production::STATUS_COMPLETED]);

        $params = ['is_product' => 'true', 'is_production' => 'true', 'search' => ['value' => $kw]];

        $asc = $this->ordered('production_finish_good.get_data', $params, 3, 'asc', 'qty');
        $this->assertSame([1, 2], $asc, 'Qty asc must order by completed-production count');

        $desc = $this->ordered('production_finish_good.get_data', $params, 3, 'desc', 'qty');
        $this->assertSame([2, 1], $desc, 'Qty desc must reverse');
    }

    /** Created By (idx 5) shows users.name via FK; sort must follow that name. */
    public function test_production_finish_good_created_by_sorts_faithfully(): void
    {
        $this->actingAs($this->userWith(['production_material.view']));
        $kw = 'PFGCB'.uniqid();

        $userZ = User::factory()->create(['name' => $kw.'-ZZZ']);
        $userA = User::factory()->create(['name' => $kw.'-AAA']);

        $pZ = $this->makeProduct($kw.'-PZ', Product::TYPE_PRODUCT, $kw.' pz', ['created_by' => $userZ->id]);
        $pA = $this->makeProduct($kw.'-PA', Product::TYPE_PRODUCT, $kw.' pa', ['created_by' => $userA->id]);

        Production::create(['product_id' => $pZ->id, 'sku' => 'PRD'.uniqid(), 'name' => 'p', 'start_date' => now(), 'due_date' => now(), 'status' => Production::STATUS_COMPLETED]);
        Production::create(['product_id' => $pA->id, 'sku' => 'PRD'.uniqid(), 'name' => 'p', 'start_date' => now(), 'due_date' => now(), 'status' => Production::STATUS_COMPLETED]);

        $params = ['is_product' => 'true', 'is_production' => 'true', 'search' => ['value' => $kw]];

        $asc = $this->ordered('production_finish_good.get_data', $params, 5, 'asc', 'sku');
        $this->assertSame([$kw.'-PA', $kw.'-PZ'], $asc, 'Created By asc must follow users.name');

        $desc = $this->ordered('production_finish_good.get_data', $params, 5, 'desc', 'sku');
        $this->assertSame([$kw.'-PZ', $kw.'-PA'], $desc, 'Created By desc must reverse');
    }

    // =====================================================================
    // User Management
    // =====================================================================

    /** Smoke: every data-column index answers 200. */
    public function test_user_management_each_data_column_returns_200(): void
    {
        $this->actingAs($this->userWith(['user_role_management.view']));

        // 0 name, 1 email, 2 role, 3 branch.
        foreach ([0, 1, 2, 3] as $col) {
            $this->getJson(route('user_management.get_data', [
                'order' => [['column' => $col, 'dir' => 'asc']],
            ]))->assertOk();
        }
    }

    /** Role (idx 2) shows a comma-joined role-name list; key must match that string. */
    public function test_user_management_role_sorts_faithfully(): void
    {
        $this->actingAs($this->userWith(['user_role_management.view']));
        $kw = 'UMROLE'.uniqid();

        $roleZ = SpatieRole::firstOrCreate(['name' => $kw.'-ZZZ', 'guard_name' => 'web']);
        $roleA = SpatieRole::firstOrCreate(['name' => $kw.'-AAA', 'guard_name' => 'web']);

        $userZ = User::factory()->create(['name' => $kw.'-userZ']);
        $userZ->assignRole($roleZ);
        $userA = User::factory()->create(['name' => $kw.'-userA']);
        $userA->assignRole($roleA);

        $asc = $this->ordered('user_management.get_data', ['search' => ['value' => $kw]], 2, 'asc', 'role');
        $this->assertSame([$kw.'-AAA', $kw.'-ZZZ'], $asc, 'Role asc must follow joined role names');

        $desc = $this->ordered('user_management.get_data', ['search' => ['value' => $kw]], 2, 'desc', 'role');
        $this->assertSame([$kw.'-ZZZ', $kw.'-AAA'], $desc, 'Role desc must reverse');
    }

    /**
     * Branch (idx 3) shows the morph label. The displayed-label order
     * (Every < Kuala Lumpur < Penang) matches the numeric location code, so the
     * code-based sort key reproduces the shown label order.
     */
    public function test_user_management_branch_sorts_faithfully(): void
    {
        $this->actingAs($this->userWith(['user_role_management.view']));
        $kw = 'UMBR'.uniqid();

        $userPenang = User::factory()->create(['name' => $kw.'-penang']);
        $userKl = User::factory()->create(['name' => $kw.'-kl']);
        (new Branch)->assign(User::class, $userPenang->id, Branch::LOCATION_PENANG);
        (new Branch)->assign(User::class, $userKl->id, Branch::LOCATION_KL);

        // Numeric location code (KL=1 < Penang=2) matches the displayed label order
        // (Kuala Lumpur < Penang), so the code-based sort key reproduces the labels.
        $asc = $this->ordered('user_management.get_data', ['search' => ['value' => $kw]], 3, 'asc', 'branch');
        $this->assertSame(['Kuala Lumpur', 'Penang'], $asc, 'Branch asc must follow label order');

        $desc = $this->ordered('user_management.get_data', ['search' => ['value' => $kw]], 3, 'desc', 'branch');
        $this->assertSame(['Penang', 'Kuala Lumpur'], $desc, 'Branch desc must reverse');
    }

    // =====================================================================
    // B.O.M Material Use
    // =====================================================================

    /** Smoke: every data-column index answers 200. */
    public function test_material_use_each_data_column_returns_200(): void
    {
        $this->actingAs($this->userWith(['setting.material_use.view']));

        foreach ([0, 1] as $col) {
            $this->getJson(route('material_use.get_data', [
                'order' => [['column' => $col, 'dir' => 'asc']],
            ]))->assertOk();
        }
    }

    /**
     * Product (idx 0) shows '(sku) model_desc'. A naive model_desc-only key would
     * differ from the displayed string, so seed rows where sku order is the REVERSE
     * of model_desc order: '(AAA) zzz' must sort before '(ZZZ) aaa'.
     */
    public function test_material_use_product_sorts_faithfully(): void
    {
        $this->actingAs($this->userWith(['setting.material_use.view']));
        $kw = 'MUPROD'.uniqid();

        // sku leads with the keyword + A/Z; model_desc deliberately reversed.
        $pA = $this->makeProduct($kw.'-AAA', Product::TYPE_PRODUCT, 'zzz '.$kw);
        $pZ = $this->makeProduct($kw.'-ZZZ', Product::TYPE_PRODUCT, 'aaa '.$kw);
        MaterialUse::create(['product_id' => $pA->id]);
        MaterialUse::create(['product_id' => $pZ->id]);

        $asc = $this->ordered('material_use.get_data', ['search' => ['value' => $kw]], 0, 'asc', 'product');
        $this->assertSame(
            ['('.$kw.'-AAA) zzz '.$kw, '('.$kw.'-ZZZ) aaa '.$kw],
            $asc,
            'Product asc must sort by the displayed (sku) model_desc string'
        );

        $desc = $this->ordered('material_use.get_data', ['search' => ['value' => $kw]], 0, 'desc', 'product');
        $this->assertSame(array_reverse($asc), $desc, 'Product desc must reverse');
    }

    /** Avg Cost (idx 1) is the aggregate mirroring MaterialUse::avgCost(). */
    public function test_material_use_avg_cost_sorts_faithfully(): void
    {
        $this->actingAs($this->userWith(['setting.material_use.view']));
        $kw = 'MUAVGD'.uniqid();

        $low = $this->makeMaterialUse($kw.'-low', 10);
        $high = $this->makeMaterialUse($kw.'-high', 50);

        $asc = $this->ordered('material_use.get_data', ['search' => ['value' => $kw]], 1, 'asc', 'product');
        $this->assertSame(['('.$kw.'-low) '.$kw.'-low desc', '('.$kw.'-high) '.$kw.'-high desc'], $asc);

        $desc = $this->ordered('material_use.get_data', ['search' => ['value' => $kw]], 1, 'desc', 'product');
        $this->assertSame(array_reverse($asc), $desc);
    }

    private function makeMaterialUse(string $sku, float $unitPrice): string
    {
        $finishGood = $this->makeProduct($sku, Product::TYPE_PRODUCT, $sku.' desc');
        $material = $this->makeProduct('MAT-'.uniqid(), Product::TYPE_RAW_MATERIAL, 'mat desc');
        ProductCost::create(['product_id' => $material->id, 'unit_price' => $unitPrice]);

        $mu = MaterialUse::create(['product_id' => $finishGood->id]);
        MaterialUseProduct::create([
            'material_use_id' => $mu->id,
            'product_id' => $material->id,
            'qty' => 1,
            'status' => 0,
        ]);

        return $sku;
    }

    // =====================================================================
    // Milestone
    // =====================================================================

    /** Smoke: Inventory Type (idx 1) answers 200; Category (idx 0) is non-sortable by design. */
    public function test_milestone_inventory_type_returns_200(): void
    {
        $this->actingAs($this->userWith(['setting.milestone.view']));

        $this->getJson(route('milestone.get_data', [
            'order' => [['column' => 1, 'dir' => 'asc']],
        ]))->assertOk();
    }

    /** Inventory Type (idx 1) sorts by the RELATED name, not the id. */
    public function test_milestone_inventory_type_sorts_faithfully(): void
    {
        $this->actingAs($this->userWith(['setting.milestone.view']));
        $kw = 'MSTYPE'.uniqid();

        // Higher id gets the alphabetically-earlier name, so id-order != name-order.
        $zType = InventoryType::create(['name' => $kw.' ZZZ', 'is_active' => 1, 'company_group' => 1, 'type' => 1]);
        $aType = InventoryType::create(['name' => $kw.' AAA', 'is_active' => 1, 'company_group' => 1, 'type' => 1]);

        Milestone::create(['type' => Milestone::TYPE_PRODUCTION, 'name' => 'MS Z', 'inventory_category_id' => '1', 'inventory_type_id' => $zType->id, 'batch' => 970001]);
        Milestone::create(['type' => Milestone::TYPE_PRODUCTION, 'name' => 'MS A', 'inventory_category_id' => '1', 'inventory_type_id' => $aType->id, 'batch' => 970002]);

        $asc = $this->ordered('milestone.get_data', ['search' => ['value' => $kw]], 1, 'asc', 'type');
        $this->assertSame([$kw.' AAA', $kw.' ZZZ'], $asc, 'Inventory Type asc must follow related name');

        $desc = $this->ordered('milestone.get_data', ['search' => ['value' => $kw]], 1, 'desc', 'type');
        $this->assertSame([$kw.' ZZZ', $kw.' AAA'], $desc, 'Inventory Type desc must reverse');
    }
}
