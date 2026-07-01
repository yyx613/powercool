<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomizeProduct;
use App\Models\Product;
use App\Models\ProductChild;
use App\Models\Production;
use App\Models\Sale;
use App\Models\SaleProduct;
use App\Models\SaleProductionRequest;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * Listing sort sweep for the production / task / production-request listings.
 *
 * Newly wired columns covered here:
 *   - production/list                 idx 4  Old Production ID  -> subquery on productions.sku (self-join via old_production)
 *   - production/list                 idx 6  Product Serial No  -> CASE subquery (R&D customize_products.sku / Normal product_children.sku)
 *   - task/list (technician & sale)   status column            -> tasks.status (was orderable:false)
 *   - production_request sale listing  idx 5  Remark            -> COALESCE(spr.remark, sale_products.remark)
 *
 * Smoke 200 checks exercise every data column index per page/role.
 *
 * A branchless, non-superadmin user is used: BranchScope is a no-op for such a
 * user, so freshly created rows are visible without branch assignment.
 */
class ListingSortSweepProductionTest extends TestCase
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

        return $user->fresh();
    }

    private function ordered(string $route, array $params, int $column, string $dir, string $pluck): array
    {
        return collect(
            $this->getJson(route($route, array_merge($params, [
                'order' => [['column' => $column, 'dir' => $dir]],
            ])))->assertOk()->json('data')
        )->pluck($pluck)->values()->all();
    }

    // =====================================================================
    // production/list
    // =====================================================================

    public function test_production_each_data_column_returns_200(): void
    {
        $this->actingAs($this->userWith(['production.view']));

        // 0 checkbox + 15 action are the only non-orderable columns.
        foreach ([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14] as $col) {
            $this->getJson(route('production.get_data', [
                'order' => [['column' => $col, 'dir' => 'asc']],
            ]))->assertOk();
        }
    }

    public function test_production_old_production_id_column_sorts_by_old_production_sku(): void
    {
        $this->actingAs($this->userWith(['production.view']));
        $token = 'OLDPRODSORT'.uniqid();
        $product = Product::first();
        $this->assertNotNull($product);

        // Two "old" productions whose SKUs sort opposite to their ids. Their own
        // SKUs deliberately omit the search token so only the child rows below
        // (which carry the token) appear in the filtered listing.
        $oldZ = Production::create([
            'product_id' => $product->id, 'sku' => 'ZZZoldparent'.uniqid(), 'name' => 'oldZ',
            'type' => Production::TYPE_NORMAL, 'status' => Production::STATUS_TO_DO,
            'start_date' => '2026-01-01', 'due_date' => '2026-01-02',
        ]);
        $oldA = Production::create([
            'product_id' => $product->id, 'sku' => 'AAAoldparent'.uniqid(), 'name' => 'oldA',
            'type' => Production::TYPE_NORMAL, 'status' => Production::STATUS_TO_DO,
            'start_date' => '2026-01-01', 'due_date' => '2026-01-02',
        ]);

        // Two child rows referencing them via old_production, sharing a search token.
        Production::create([
            'product_id' => $product->id, 'sku' => $token.'-childOfZ', 'name' => 'cz',
            'type' => Production::TYPE_NORMAL, 'status' => Production::STATUS_TO_DO,
            'old_production' => $oldZ->id, 'start_date' => '2026-01-01', 'due_date' => '2026-01-02',
        ]);
        Production::create([
            'product_id' => $product->id, 'sku' => $token.'-childOfA', 'name' => 'ca',
            'type' => Production::TYPE_NORMAL, 'status' => Production::STATUS_TO_DO,
            'old_production' => $oldA->id, 'start_date' => '2026-01-01', 'due_date' => '2026-01-02',
        ]);

        $asc = $this->ordered('production.get_data', ['search' => ['value' => $token]], 4, 'asc', 'old_production_sku');
        $this->assertSame([$oldA->sku, $oldZ->sku], $asc);

        $desc = $this->ordered('production.get_data', ['search' => ['value' => $token]], 4, 'desc', 'old_production_sku');
        $this->assertSame([$oldZ->sku, $oldA->sku], $desc);
    }

    public function test_production_product_serial_no_column_sorts_faithfully(): void
    {
        $this->actingAs($this->userWith(['production.view']));
        $token = 'SERIALSORT'.uniqid();
        $product = Product::first();
        $this->assertNotNull($product);

        // Normal production: serial no = product_children.sku ('MMM').
        $normal = Production::create([
            'product_id' => $product->id, 'sku' => $token.'-normal', 'name' => 'n',
            'type' => Production::TYPE_NORMAL, 'status' => Production::STATUS_TO_DO,
            'start_date' => '2026-01-01', 'due_date' => '2026-01-02',
        ]);
        $child = ProductChild::create([
            'product_id' => $product->id, 'sku' => 'MMM-'.$token,
            'location' => ProductChild::LOCATION_FACTORY,
        ]);
        $normal->product_child_id = $child->id;
        $normal->save();

        // R&D production: serial no = customize_products.sku ('AAA').
        $rnd = Production::create([
            'product_id' => $product->id, 'sku' => $token.'-rnd', 'name' => 'r',
            'type' => Production::TYPE_RND, 'status' => Production::STATUS_TO_DO,
            'start_date' => '2026-01-01', 'due_date' => '2026-01-02',
        ]);
        CustomizeProduct::create(['production_id' => $rnd->id, 'sku' => 'AAA-'.$token]);

        $asc = $this->ordered('production.get_data', ['search' => ['value' => $token]], 6, 'asc', 'product_serial_no');
        $this->assertSame(['AAA-'.$token, 'MMM-'.$token], $asc);

        $desc = $this->ordered('production.get_data', ['search' => ['value' => $token]], 6, 'desc', 'product_serial_no');
        $this->assertSame(['MMM-'.$token, 'AAA-'.$token], $desc);
    }

    // =====================================================================
    // task/list  (technician & sale layouts)
    // =====================================================================

    private function makeTwoTasks(int $type, string $token): void
    {
        $customer = Customer::create([
            'name' => 'Sweep Task Customer',
            'phone' => '0123456789',
            'sku' => 'CSWP'.uniqid(),
            'status' => Customer::STATUS_ACTIVE,
        ]);

        // status: one Completed (4), one To Do (1) so asc/desc are unambiguous.
        Task::create([
            'customer_id' => $customer->id,
            'sku' => $token.'-done', 'type' => $type, 'name' => 'done '.$token,
            'status' => Task::STATUS_COMPLETED, 'amount_to_collect' => 0,
            'start_date' => '2026-01-01', 'due_date' => '2026-01-02',
        ]);
        Task::create([
            'customer_id' => $customer->id,
            'sku' => $token.'-todo', 'type' => $type, 'name' => 'todo '.$token,
            'status' => Task::STATUS_TO_DO, 'amount_to_collect' => 0,
            'start_date' => '2026-01-01', 'due_date' => '2026-01-03',
        ]);
    }

    public function test_task_technician_each_data_column_returns_200(): void
    {
        $this->actingAs($this->userWith([]));

        // technician: 0 checkbox + 6 action non-orderable; 1..5 data columns.
        foreach ([1, 2, 3, 4, 5] as $col) {
            $this->getJson(route('task.get_data', [
                'role' => 'technician',
                'order' => [['column' => $col, 'dir' => 'asc']],
            ]))->assertOk();
        }
    }

    public function test_task_sale_each_data_column_returns_200(): void
    {
        $this->actingAs($this->userWith([]));

        // sale: 5 action non-orderable; 0..4 data columns.
        foreach ([0, 1, 2, 3, 4] as $col) {
            $this->getJson(route('task.get_data', [
                'role' => 'sale',
                'order' => [['column' => $col, 'dir' => 'asc']],
            ]))->assertOk();
        }
    }

    public function test_task_technician_status_column_sorts(): void
    {
        $this->actingAs($this->userWith([]));
        $token = 'TECHSORT'.uniqid();
        $this->makeTwoTasks(Task::TYPE_TECHNICIAN, $token);

        // technician status is column index 5.
        $asc = $this->ordered('task.get_data', ['role' => 'technician', 'search' => ['value' => $token]], 5, 'asc', 'status');
        $this->assertSame([Task::STATUS_TO_DO, Task::STATUS_COMPLETED], $asc);

        $desc = $this->ordered('task.get_data', ['role' => 'technician', 'search' => ['value' => $token]], 5, 'desc', 'status');
        $this->assertSame([Task::STATUS_COMPLETED, Task::STATUS_TO_DO], $desc);
    }

    public function test_task_sale_status_column_sorts(): void
    {
        $this->actingAs($this->userWith([]));
        $token = 'SALETASKSORT'.uniqid();
        $this->makeTwoTasks(Task::TYPE_SALE, $token);

        // sale status is column index 4.
        $asc = $this->ordered('task.get_data', ['role' => 'sale', 'search' => ['value' => $token]], 4, 'asc', 'status');
        $this->assertSame([Task::STATUS_TO_DO, Task::STATUS_COMPLETED], $asc);

        $desc = $this->ordered('task.get_data', ['role' => 'sale', 'search' => ['value' => $token]], 4, 'desc', 'status');
        $this->assertSame([Task::STATUS_COMPLETED, Task::STATUS_TO_DO], $desc);
    }

    // =====================================================================
    // production_request listings
    // =====================================================================

    public function test_production_request_normal_each_data_column_returns_200(): void
    {
        $this->actingAs($this->userWith(['production_request.view']));

        // idx 0 No (row counter) + idx 8 action non-orderable; 1..7 data columns.
        foreach ([1, 2, 3, 4, 5, 6, 7] as $col) {
            $this->getJson(route('production_request.get_data', [
                'order' => [['column' => $col, 'dir' => 'asc']],
            ]))->assertOk();
        }
    }

    public function test_production_request_sale_each_data_column_returns_200(): void
    {
        $this->actingAs($this->userWith(['production_request.view']));

        // idx 0 No (row counter) + idx 7 action non-orderable; 1..6 data columns.
        foreach ([1, 2, 3, 4, 5, 6] as $col) {
            $this->getJson(route('production_request.get_data_sale_production_request', [
                'order' => [['column' => $col, 'dir' => 'asc']],
            ]))->assertOk();
        }
    }

    public function test_production_request_sale_remark_column_sorts_faithfully(): void
    {
        $this->actingAs($this->userWith(['production_request.view']));
        $token = 'SPRREMARK'.uniqid();
        $product = Product::first();
        $this->assertNotNull($product);

        $saleA = Sale::create(['sku' => $token.'-sa']);
        $saleB = Sale::create(['sku' => $token.'-sb']);

        // Row A: own remark 'AAA'.
        SaleProductionRequest::create([
            'sale_id' => $saleA->id, 'product_id' => $product->id,
            'status' => 1, 'remark' => 'AAA '.$token,
        ]);

        // Row B: null own remark; falls back to sale_products.remark 'ZZZ'.
        SaleProduct::create([
            'sale_id' => $saleB->id, 'product_id' => $product->id,
            'qty' => 1, 'unit_price' => 0, 'remark' => 'ZZZ '.$token,
        ]);
        SaleProductionRequest::create([
            'sale_id' => $saleB->id, 'product_id' => $product->id,
            'status' => 1, 'remark' => null,
        ]);

        $asc = $this->ordered('production_request.get_data_sale_production_request', ['search' => ['value' => $token]], 5, 'asc', 'remark');
        $this->assertSame(['AAA '.$token, 'ZZZ '.$token], $asc);

        $desc = $this->ordered('production_request.get_data_sale_production_request', ['search' => ['value' => $token]], 5, 'desc', 'remark');
        $this->assertSame(['ZZZ '.$token, 'AAA '.$token], $desc);
    }
}
