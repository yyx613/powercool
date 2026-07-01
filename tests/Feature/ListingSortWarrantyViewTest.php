<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Milestone;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Task;
use App\Models\TaskMilestone;
use App\Models\TaskMilestoneInventory;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * Warranty view detail page (warranty.view_get_data): the "Product Code / Product Serial No"
 * column (idx 0) shows the morphed inventory sku and must be sortable, matching the display.
 */
class ListingSortWarrantyViewTest extends TestCase
{
    use DatabaseTransactions;

    private function user(): User
    {
        Permission::firstOrCreate(['name' => 'warranty.view', 'guard_name' => 'web']);
        $u = User::factory()->create();
        $u->givePermissionTo('warranty.view');

        return $u->fresh();
    }

    public function test_product_column_sorts_by_inventory_sku(): void
    {
        $this->actingAs($this->user());

        $kw = 'WVSORT'.uniqid();
        $cust = Customer::create(['status' => 1, 'company_name' => 'C '.$kw]);
        $sale = Sale::create(['sku' => 'SO-'.$kw, 'type' => Sale::TYPE_SO, 'customer_id' => $cust->id, 'status' => Sale::STATUS_ACTIVE, 'is_draft' => 0]);

        $task = Task::create([
            'customer_id'   => $cust->id,
            'sku'           => 'TK-'.$kw,
            'type'          => 1,
            'name'          => 'T '.$kw,
            'desc'          => 'd',
            'status'        => 1,
            'sale_order_id' => $sale->id,
        ]);
        $tm = TaskMilestone::create(['task_id' => $task->id, 'milestone_id' => Milestone::value('id')]);

        // Two products with skus in reverse of insertion order.
        $pZ = Product::create(['inventory_category_id' => \App\Models\InventoryCategory::value('id'), 'type' => Product::TYPE_PRODUCT, 'sku' => 'ZZZ-'.$kw, 'model_desc' => 'z', 'in_production' => 0, 'cost' => 0, 'sst' => 0, 'is_active' => 1]);
        $pA = Product::create(['inventory_category_id' => \App\Models\InventoryCategory::value('id'), 'type' => Product::TYPE_PRODUCT, 'sku' => 'AAA-'.$kw, 'model_desc' => 'a', 'in_production' => 0, 'cost' => 0, 'sst' => 0, 'is_active' => 1]);

        foreach ([$pZ, $pA] as $p) {
            TaskMilestoneInventory::create([
                'task_milestone_id' => $tm->id,
                'inventory_type'    => Product::class,
                'inventory_id'      => $p->id,
                'qty'               => 1,
            ]);
        }

        $fetch = fn (string $dir) => collect($this->getJson(route('warranty.view_get_data', [
            'sale_id' => $sale->id,
            'search'  => ['value' => $kw],
            'order'   => [['column' => 0, 'dir' => $dir]],
        ]))->assertOk()->json('data'))->pluck('product')->all();

        $this->assertSame(['AAA-'.$kw, 'ZZZ-'.$kw], $fetch('asc'));
        $this->assertSame(['ZZZ-'.$kw, 'AAA-'.$kw], $fetch('desc'));
    }
}
