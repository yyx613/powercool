<?php

namespace Tests\Feature;

use App\Models\CustomizeProduct;
use App\Models\InventoryCategory;
use App\Models\InventoryServiceReminder;
use App\Models\MaterialUse;
use App\Models\MaterialUseProduct;
use App\Models\Product;
use App\Models\ProductChild;
use App\Models\ProductCost;
use App\Models\Production;
use App\Models\Sale;
use App\Models\Task;
use App\Models\TaskMilestone;
use App\Models\TaskMilestoneInventory;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * Listing sort sweep for the inventory-family listings.
 *
 * Newly wired columns covered here:
 *   - inventory Finish Good       idx 3  Qty              -> products.qty (was JS-disabled)
 *   - inventory Raw Material      idx 3  Qty              -> products.qty (was JS-disabled)
 *   - inventory Raw Material      idx 5  Is Spare part    -> products.is_sparepart (was orderable:false)
 *   - customize_inventory         idx 2  SO               -> subquery sales.sku via production
 *   - customize_inventory         idx 3  Production SKU   -> subquery productions.sku
 *   - material_use               idx 1  Avg Cost          -> subquery sum(qty * AVG(product_costs.unit_price))
 *   - service_history            idx 0  Serial No         -> subquery product_children.sku (fixed bad 'sku' map)
 *   - service_history            idx 1  Task ID           -> subquery tasks.sku via task_milestone (was orderable:false)
 *   - service_reminder           idx 0  SKU               -> CASE subquery objectable sku (was orderable:false)
 *   - service_reminder           idx 1  Next Service Date -> subquery latest reminder (was orderable:false)
 *   - service_reminder           idx 2  Last Service Date -> subquery second-latest reminder (was orderable:false)
 *
 * A branchless, non-superadmin user is used: BranchScope is a no-op for such a
 * user, so freshly created rows are visible without branch assignment.
 */
class ListingSortSweepInventoryTest extends TestCase
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

    private function ordered(string $route, array $params, int $column, string $dir, string $pluck): array
    {
        return collect(
            $this->getJson(route($route, array_merge($params, [
                'order' => [['column' => $column, 'dir' => $dir]],
            ])))->assertOk()->json('data')
        )->pluck($pluck)->values()->all();
    }

    private function makeProduct(string $sku, int $type, array $attrs = []): Product
    {
        $cat = InventoryCategory::create(['name' => 'SweepCat '.uniqid(), 'company_group' => 1, 'is_active' => 1]);

        return Product::create(array_merge([
            'inventory_category_id' => $cat->id,
            'type'                  => $type,
            'sku'                   => $sku,
            'model_desc'            => $sku.' desc',
            'is_active'             => 1,
            'min_price'             => 0,
            'max_price'             => 0,
            'qty'                   => 0,
        ], $attrs));
    }

    // =====================================================================
    // inventory  Finish Good  (is_product=true, is_production=false)
    // =====================================================================

    public function test_finish_good_each_data_column_returns_200(): void
    {
        $this->actingAs($this->userWith(['inventory.product.view']));

        // 0 sku, 1 model, 2 category, 3 qty, 4 price, 5 status, 6 created by.
        foreach ([0, 1, 2, 3, 4, 5, 6] as $col) {
            $this->getJson(route('product.get_data', [
                'is_product' => 'true', 'is_production' => 'false',
                'order' => [['column' => $col, 'dir' => 'asc']],
            ]))->assertOk();
        }
    }

    public function test_finish_good_qty_column_sorts_faithfully(): void
    {
        // Finish Good displayed Qty is the product_children count (accessor), not the
        // raw products.qty column, so the sort key must mirror that count.
        $this->actingAs($this->userWith(['inventory.product.view']));
        $kw = 'FGQTY'.uniqid();

        $this->makeProductWithChildren($kw.'-C', Product::TYPE_PRODUCT, 3);
        $this->makeProductWithChildren($kw.'-A', Product::TYPE_PRODUCT, 1);
        $this->makeProductWithChildren($kw.'-B', Product::TYPE_PRODUCT, 2);

        $asc = $this->ordered('product.get_data', ['is_product' => 'true', 'is_production' => 'false', 'search' => ['value' => $kw]], 3, 'asc', 'qty');
        $this->assertSame([1, 2, 3], $asc);

        $desc = $this->ordered('product.get_data', ['is_product' => 'true', 'is_production' => 'false', 'search' => ['value' => $kw]], 3, 'desc', 'qty');
        $this->assertSame([3, 2, 1], $desc);
    }

    private function makeProductWithChildren(string $sku, int $type, int $childCount, array $attrs = []): Product
    {
        $product = $this->makeProduct($sku, $type, $attrs);
        for ($i = 0; $i < $childCount; $i++) {
            ProductChild::create([
                'product_id' => $product->id,
                'sku' => $sku.'-pc'.$i.'-'.uniqid(),
                'location' => ProductChild::LOCATION_FACTORY,
            ]);
        }

        return $product;
    }

    // =====================================================================
    // inventory  Raw Material  (is_product=false, is_production=false)
    // =====================================================================

    public function test_raw_material_each_data_column_returns_200(): void
    {
        $this->actingAs($this->userWith(['inventory.raw_material.view']));

        // 0 sku, 1 model, 2 category, 3 qty, 4 price, 5 is_sparepart, 6 status, 7 created by.
        foreach ([0, 1, 2, 3, 4, 5, 6, 7] as $col) {
            $this->getJson(route('raw_material.get_data', [
                'is_product' => 'false', 'is_production' => 'false',
                'order' => [['column' => $col, 'dir' => 'asc']],
            ]))->assertOk();
        }
    }

    public function test_raw_material_qty_column_sorts_faithfully(): void
    {
        // Non-spare-part raw materials display the raw products.qty column.
        $this->actingAs($this->userWith(['inventory.raw_material.view']));
        $kw = 'RMQTY'.uniqid();

        $this->makeProduct($kw.'-C', Product::TYPE_RAW_MATERIAL, ['qty' => 30, 'is_sparepart' => 0]);
        $this->makeProduct($kw.'-A', Product::TYPE_RAW_MATERIAL, ['qty' => 10, 'is_sparepart' => 0]);
        $this->makeProduct($kw.'-B', Product::TYPE_RAW_MATERIAL, ['qty' => 20, 'is_sparepart' => 0]);

        $asc = $this->ordered('raw_material.get_data', ['is_product' => 'false', 'is_production' => 'false', 'search' => ['value' => $kw]], 3, 'asc', 'qty');
        $this->assertSame([10, 20, 30], $asc);

        $desc = $this->ordered('raw_material.get_data', ['is_product' => 'false', 'is_production' => 'false', 'search' => ['value' => $kw]], 3, 'desc', 'qty');
        $this->assertSame([30, 20, 10], $desc);
    }

    public function test_raw_material_is_sparepart_column_sorts_faithfully(): void
    {
        $this->actingAs($this->userWith(['inventory.raw_material.view']));
        $kw = 'RMSPARE'.uniqid();

        $this->makeProduct($kw.'-yes', Product::TYPE_RAW_MATERIAL, ['is_sparepart' => 1]);
        $this->makeProduct($kw.'-no', Product::TYPE_RAW_MATERIAL, ['is_sparepart' => 0]);

        // is_sparepart is column index 5 in the Raw Material layout.
        $asc = $this->ordered('raw_material.get_data', ['is_product' => 'false', 'is_production' => 'false', 'search' => ['value' => $kw]], 5, 'asc', 'is_sparepart');
        $this->assertSame([0, 1], $asc);

        $desc = $this->ordered('raw_material.get_data', ['is_product' => 'false', 'is_production' => 'false', 'search' => ['value' => $kw]], 5, 'desc', 'is_sparepart');
        $this->assertSame([1, 0], $desc);
    }

    public function test_production_finish_good_each_data_column_returns_200(): void
    {
        $this->actingAs($this->userWith(['production_material.view']));

        // Production Finish Good layout: 0 sku, 1 model, 2 category, 3 qty, 4 status, 5 created by.
        foreach ([0, 1, 2, 3, 4, 5] as $col) {
            $this->getJson(route('production_finish_good.get_data', [
                'is_product' => 'true', 'is_production' => 'true',
                'order' => [['column' => $col, 'dir' => 'asc']],
            ]))->assertOk();
        }
    }

    public function test_production_material_each_data_column_returns_200(): void
    {
        $this->actingAs($this->userWith(['production_material.view']));

        // Production Raw Material layout: 0 sku, 1 model, 2 category, 3 qty,
        // 4 is_sparepart, 5 status, 6 created by.
        foreach ([0, 1, 2, 3, 4, 5, 6] as $col) {
            $this->getJson(route('production_material.get_data', [
                'is_product' => 'false', 'is_production' => 'true',
                'order' => [['column' => $col, 'dir' => 'asc']],
            ]))->assertOk();
        }
    }

    // =====================================================================
    // raw_material_request
    // =====================================================================

    public function test_raw_material_request_each_data_column_returns_200(): void
    {
        $this->actingAs($this->userWith(['inventory.raw_material_request.view']));

        // 1 date, 2 production id, 3 total req, 4 balance, 5 fulfilled, 6 requested by,
        // 7 status (0 No row-counter + 8 action non-orderable).
        foreach ([1, 2, 3, 4, 5, 6, 7] as $col) {
            $this->getJson(route('raw_material_request.get_data', [
                'order' => [['column' => $col, 'dir' => 'asc']],
            ]))->assertOk();
        }
    }

    // =====================================================================
    // grn
    // =====================================================================

    public function test_grn_each_data_column_returns_200(): void
    {
        $this->actingAs($this->userWith(['grn.view']));

        // 1 sku, 2 status (0 select-all + 3 action non-orderable).
        foreach ([1, 2] as $col) {
            $this->getJson(route('grn.get_data', [
                'order' => [['column' => $col, 'dir' => 'asc']],
            ]))->assertOk();
        }
    }

    // =====================================================================
    // customize_inventory
    // =====================================================================

    private function makeCustomize(string $kw, string $tag, ?Sale $sale = null): CustomizeProduct
    {
        $product = $this->makeProduct('CPP-'.uniqid(), Product::TYPE_PRODUCT);

        $production = Production::create([
            'product_id' => $product->id,
            'sku' => $kw.'-PRD-'.$tag,
            'name' => 'CP Sweep',
            'start_date' => now()->toDateString(),
            'due_date' => now()->toDateString(),
            'status' => Production::STATUS_COMPLETED,
            'sale_id' => $sale?->id,
        ]);

        return CustomizeProduct::create([
            'production_id' => $production->id,
            'sku' => $kw.'-CP-'.$tag.'-'.uniqid(),
            'name' => $kw,
            'length' => 10, 'width' => 10, 'height' => 10,
        ]);
    }

    public function test_customize_each_data_column_returns_200(): void
    {
        $this->actingAs($this->userWith(['inventory.customize.view']));

        // 0 serial, 1 name, 2 SO, 3 prod sku, 4 dims, 5 weight, 6 capacity,
        // 7 refrigerant, 8 power input, 9 power consumption, 10 voltage, 11 features.
        foreach (range(0, 11) as $col) {
            $this->getJson(route('customize.get_data', [
                'order' => [['column' => $col, 'dir' => 'asc']],
            ]))->assertOk();
        }
    }

    public function test_customize_production_sku_column_sorts_faithfully(): void
    {
        $this->actingAs($this->userWith(['inventory.customize.view']));
        $kw = 'CPPRODSKU'.uniqid();

        $this->makeCustomize($kw, 'ZZZ');
        $this->makeCustomize($kw, 'AAA');

        $asc = $this->ordered('customize.get_data', ['search' => ['value' => $kw]], 3, 'asc', 'production_sku');
        $this->assertSame([$kw.'-PRD-AAA', $kw.'-PRD-ZZZ'], $asc);

        $desc = $this->ordered('customize.get_data', ['search' => ['value' => $kw]], 3, 'desc', 'production_sku');
        $this->assertSame([$kw.'-PRD-ZZZ', $kw.'-PRD-AAA'], $desc);
    }

    public function test_customize_so_column_sorts_faithfully(): void
    {
        $this->actingAs($this->userWith(['inventory.customize.view']));
        $kw = 'CPSO'.uniqid();

        $saleZ = Sale::create(['sku' => $kw.'-SO-ZZZ']);
        $saleA = Sale::create(['sku' => $kw.'-SO-AAA']);
        $this->makeCustomize($kw, 'z', $saleZ);
        $this->makeCustomize($kw, 'a', $saleA);

        $asc = $this->ordered('customize.get_data', ['search' => ['value' => $kw]], 2, 'asc', 'sale_sku');
        $this->assertSame([$kw.'-SO-AAA', $kw.'-SO-ZZZ'], $asc);

        $desc = $this->ordered('customize.get_data', ['search' => ['value' => $kw]], 2, 'desc', 'sale_sku');
        $this->assertSame([$kw.'-SO-ZZZ', $kw.'-SO-AAA'], $desc);
    }

    // =====================================================================
    // material_use
    // =====================================================================

    public function test_material_use_each_data_column_returns_200(): void
    {
        $this->actingAs($this->userWith(['setting.material_use.view']));

        // 0 product, 1 avg cost.
        foreach ([0, 1] as $col) {
            $this->getJson(route('material_use.get_data', [
                'order' => [['column' => $col, 'dir' => 'asc']],
            ]))->assertOk();
        }
    }

    public function test_material_use_avg_cost_column_sorts_faithfully(): void
    {
        $this->actingAs($this->userWith(['setting.material_use.view']));
        $kw = 'MUAVG'.uniqid();

        // Two BOMs whose avg cost differs: low (10) and high (50).
        $low = $this->makeMaterialUse($kw.'-low', 10);
        $high = $this->makeMaterialUse($kw.'-high', 50);

        $asc = $this->ordered('material_use.get_data', ['search' => ['value' => $kw]], 1, 'asc', 'product');
        $this->assertSame(['('.$low.') '.$low.' desc', '('.$high.') '.$high.' desc'], $asc);

        $desc = $this->ordered('material_use.get_data', ['search' => ['value' => $kw]], 1, 'desc', 'product');
        $this->assertSame(['('.$high.') '.$high.' desc', '('.$low.') '.$low.' desc'], $desc);
    }

    /**
     * Build a MaterialUse for a finish-good product with a single active material
     * whose unit cost (one product_cost row) drives the avg-cost sort. Returns the
     * finish-good sku so the row can be identified by its 'product' display value.
     */
    private function makeMaterialUse(string $sku, float $unitPrice): string
    {
        $finishGood = $this->makeProduct($sku, Product::TYPE_PRODUCT);
        $material = $this->makeProduct('MAT-'.uniqid(), Product::TYPE_RAW_MATERIAL);
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
    // service_history
    // =====================================================================

    public function test_service_history_each_data_column_returns_200(): void
    {
        $this->actingAs($this->userWith(['service_history.view']));

        // 0 serial, 1 task id, 3 qty, 4 service date (2 technician + 5 photo non-orderable).
        foreach ([0, 1, 3, 4] as $col) {
            $this->getJson(route('service_history.get_data', [
                'order' => [['column' => $col, 'dir' => 'asc']],
            ]))->assertOk();
        }
    }

    public function test_service_history_serial_no_column_sorts_faithfully(): void
    {
        $this->actingAs($this->userWith(['service_history.view']));
        $kw = 'SHSERIAL'.uniqid();

        $product = $this->makeProduct('SHP-'.uniqid(), Product::TYPE_PRODUCT);
        $childZ = ProductChild::create(['product_id' => $product->id, 'sku' => $kw.'-Z', 'location' => ProductChild::LOCATION_FACTORY]);
        $childA = ProductChild::create(['product_id' => $product->id, 'sku' => $kw.'-A', 'location' => ProductChild::LOCATION_FACTORY]);

        TaskMilestoneInventory::create(['inventory_type' => ProductChild::class, 'inventory_id' => $childZ->id, 'qty' => 1, 'service_date' => '2026-01-01']);
        TaskMilestoneInventory::create(['inventory_type' => ProductChild::class, 'inventory_id' => $childA->id, 'qty' => 1, 'service_date' => '2026-01-02']);

        // Search by the shared child-sku prefix isolates these two rows.
        $asc = $this->ordered('service_history.get_data', ['search' => ['value' => $kw]], 0, 'asc', 'serial_no');
        $this->assertSame([$kw.'-A', $kw.'-Z'], $asc);

        $desc = $this->ordered('service_history.get_data', ['search' => ['value' => $kw]], 0, 'desc', 'serial_no');
        $this->assertSame([$kw.'-Z', $kw.'-A'], $desc);
    }

    public function test_service_history_task_id_column_sorts_faithfully(): void
    {
        $this->actingAs($this->userWith(['service_history.view']));
        $kw = 'SHTASK'.uniqid();

        $product = $this->makeProduct('SHTP-'.uniqid(), Product::TYPE_PRODUCT);
        $child = ProductChild::create(['product_id' => $product->id, 'sku' => $kw.'-child', 'location' => ProductChild::LOCATION_FACTORY]);

        $customer = \App\Models\Customer::create(['name' => 'SH Task Customer', 'phone' => '0123456789', 'sku' => 'CSH'.uniqid(), 'status' => \App\Models\Customer::STATUS_ACTIVE]);
        $taskZ = Task::create(['customer_id' => $customer->id, 'sku' => $kw.'-TASK-ZZZ', 'type' => Task::TYPE_TECHNICIAN, 'name' => 'z', 'status' => Task::STATUS_TO_DO, 'amount_to_collect' => 0, 'start_date' => '2026-01-01', 'due_date' => '2026-01-02']);
        $taskA = Task::create(['customer_id' => $customer->id, 'sku' => $kw.'-TASK-AAA', 'type' => Task::TYPE_TECHNICIAN, 'name' => 'a', 'status' => Task::STATUS_TO_DO, 'amount_to_collect' => 0, 'start_date' => '2026-01-01', 'due_date' => '2026-01-02']);

        // TaskMilestone is a Pivot (incrementing = false), so $model->id is not
        // populated after create(); fetch the persisted id by task_id.
        $milestone = \App\Models\Milestone::create(['name' => 'SH Milestone '.uniqid()]);
        TaskMilestone::create(['task_id' => $taskZ->id, 'milestone_id' => $milestone->id]);
        TaskMilestone::create(['task_id' => $taskA->id, 'milestone_id' => $milestone->id]);
        $tmZ = TaskMilestone::where('task_id', $taskZ->id)->first();
        $tmA = TaskMilestone::where('task_id', $taskA->id)->first();

        TaskMilestoneInventory::create(['inventory_type' => ProductChild::class, 'inventory_id' => $child->id, 'task_milestone_id' => $tmZ->id, 'qty' => 1, 'service_date' => '2026-01-01']);
        TaskMilestoneInventory::create(['inventory_type' => ProductChild::class, 'inventory_id' => $child->id, 'task_milestone_id' => $tmA->id, 'qty' => 1, 'service_date' => '2026-01-02']);

        $asc = $this->ordered('service_history.get_data', ['search' => ['value' => $kw.'-TASK']], 1, 'asc', 'task_sku');
        $this->assertSame([$kw.'-TASK-AAA', $kw.'-TASK-ZZZ'], $asc);

        $desc = $this->ordered('service_history.get_data', ['search' => ['value' => $kw.'-TASK']], 1, 'desc', 'task_sku');
        $this->assertSame([$kw.'-TASK-ZZZ', $kw.'-TASK-AAA'], $desc);
    }

    // =====================================================================
    // service_reminder
    // =====================================================================

    public function test_service_reminder_each_data_column_returns_200(): void
    {
        $this->actingAs($this->userWith(['service_reminder.view']));

        // 0 sku, 1 next service date, 2 last service date.
        foreach ([0, 1, 2] as $col) {
            $this->getJson(route('service_reminder.get_data', [
                'order' => [['column' => $col, 'dir' => 'asc']],
            ]))->assertOk();
        }
    }

    public function test_service_reminder_sku_column_sorts_faithfully(): void
    {
        $this->actingAs($this->userWith(['service_reminder.view']));
        $kw = 'SRMSKU'.uniqid();

        $product = $this->makeProduct('SRMP-'.uniqid(), Product::TYPE_PRODUCT);
        $childZ = ProductChild::create(['product_id' => $product->id, 'sku' => $kw.'-Z', 'location' => ProductChild::LOCATION_FACTORY]);
        $childA = ProductChild::create(['product_id' => $product->id, 'sku' => $kw.'-A', 'location' => ProductChild::LOCATION_FACTORY]);

        InventoryServiceReminder::create(['object_type' => ProductChild::class, 'object_id' => $childZ->id, 'next_service_date' => '2026-05-01']);
        InventoryServiceReminder::create(['object_type' => ProductChild::class, 'object_id' => $childA->id, 'next_service_date' => '2026-06-01']);

        $asc = $this->ordered('service_reminder.get_data', ['search' => ['value' => $kw]], 0, 'asc', 'sku');
        $this->assertSame([$kw.'-A', $kw.'-Z'], $asc);

        $desc = $this->ordered('service_reminder.get_data', ['search' => ['value' => $kw]], 0, 'desc', 'sku');
        $this->assertSame([$kw.'-Z', $kw.'-A'], $desc);
    }

    public function test_service_reminder_next_service_date_column_sorts_faithfully(): void
    {
        $this->actingAs($this->userWith(['service_reminder.view']));
        $kw = 'SRMNEXT'.uniqid();

        $product = $this->makeProduct('SRMNP-'.uniqid(), Product::TYPE_PRODUCT);
        $childEarly = ProductChild::create(['product_id' => $product->id, 'sku' => $kw.'-early', 'location' => ProductChild::LOCATION_FACTORY]);
        $childLate = ProductChild::create(['product_id' => $product->id, 'sku' => $kw.'-late', 'location' => ProductChild::LOCATION_FACTORY]);

        InventoryServiceReminder::create(['object_type' => ProductChild::class, 'object_id' => $childLate->id, 'next_service_date' => '2026-12-01']);
        InventoryServiceReminder::create(['object_type' => ProductChild::class, 'object_id' => $childEarly->id, 'next_service_date' => '2026-01-01']);

        $asc = $this->ordered('service_reminder.get_data', ['search' => ['value' => $kw]], 1, 'asc', 'sku');
        $this->assertSame([$kw.'-early', $kw.'-late'], $asc);

        $desc = $this->ordered('service_reminder.get_data', ['search' => ['value' => $kw]], 1, 'desc', 'sku');
        $this->assertSame([$kw.'-late', $kw.'-early'], $desc);
    }
}
