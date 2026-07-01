<?php

namespace Tests\Feature;

use App\Models\InventoryCategory;
use App\Models\Product;
use App\Models\ProductChild;
use App\Models\Production;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * Finish Good inventory listing: Price (idx 4), Status (idx 5) and Created By (idx 6)
 * must be server-side sortable. The shared inventory view splices columns per mode, so
 * these indices are specific to the Finish Good layout (is_product=true, is_production=false).
 */
class ListingSortInventoryTest extends TestCase
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

    private function makeProduct(string $sku, array $attrs): Product
    {
        $cat = InventoryCategory::create(['name' => 'Cat '.uniqid(), 'company_group' => 1, 'is_active' => 1]);

        return Product::create(array_merge([
            'inventory_category_id' => $cat->id,
            'type'                  => Product::TYPE_PRODUCT,
            'sku'                   => $sku,
            'model_desc'            => $sku.' desc',
            'in_production'         => 0,
            'cost'                  => 0,
            'sst'                   => 0,
            'is_active'             => 1,
            'min_price'             => 0,
            'max_price'             => 0,
        ], $attrs));
    }

    private function fetch(string $keyword, int $column, string $dir): array
    {
        return collect($this->getJson(route('product.get_data', [
            'is_product'    => 'true',
            'is_production' => 'false',
            'search'        => ['value' => $keyword],
            'order'         => [['column' => $column, 'dir' => $dir]],
        ]))->assertOk()->json('data'))->all();
    }

    public function test_finish_good_price_column_is_sortable(): void
    {
        $this->actingAs($this->userWith(['inventory.product.view']));
        $kw = 'INVSORT'.uniqid();

        $this->makeProduct($kw.'-C', ['min_price' => 300]);
        $this->makeProduct($kw.'-A', ['min_price' => 100]);
        $this->makeProduct($kw.'-B', ['min_price' => 200]);

        $asc = collect($this->fetch($kw, 4, 'asc'))->pluck('sku')->all();
        $this->assertSame([$kw.'-A', $kw.'-B', $kw.'-C'], $asc);

        $desc = collect($this->fetch($kw, 4, 'desc'))->pluck('sku')->all();
        $this->assertSame([$kw.'-C', $kw.'-B', $kw.'-A'], $desc);
    }

    public function test_finish_good_status_column_is_sortable(): void
    {
        $this->actingAs($this->userWith(['inventory.product.view']));
        $kw = 'INVSORT'.uniqid();

        $this->makeProduct($kw.'-active', ['is_active' => 1]);
        $this->makeProduct($kw.'-inactive', ['is_active' => 0]);

        $asc = collect($this->fetch($kw, 5, 'asc'))->pluck('status')->all();
        $this->assertSame([0, 1], $asc);

        $desc = collect($this->fetch($kw, 5, 'desc'))->pluck('status')->all();
        $this->assertSame([1, 0], $desc);
    }

    public function test_finish_good_created_by_column_is_sortable(): void
    {
        $this->actingAs($this->userWith(['inventory.product.view']));
        $kw = 'INVSORT'.uniqid();

        $ua = User::factory()->create(['name' => 'AAA '.uniqid()]);
        $uc = User::factory()->create(['name' => 'ZZZ '.uniqid()]);

        $this->makeProduct($kw.'-2', ['created_by' => $uc->id]);
        $this->makeProduct($kw.'-1', ['created_by' => $ua->id]);

        $asc = collect($this->fetch($kw, 6, 'asc'))->pluck('created_by.name')->all();
        $this->assertSame([$ua->name, $uc->name], $asc);

        $desc = collect($this->fetch($kw, 6, 'desc'))->pluck('created_by.name')->all();
        $this->assertSame([$uc->name, $ua->name], $desc);
    }

    private function fetchProduction(string $keyword, bool $isProduct, int $column, string $dir): array
    {
        $route = $isProduct ? 'production_finish_good.get_data' : 'production_material.get_data';

        return collect($this->getJson(route($route, [
            'is_product'    => $isProduct ? 'true' : 'false',
            'is_production' => 'true',
            'search'        => ['value' => $keyword],
            'order'         => [['column' => $column, 'dir' => $dir]],
        ]))->assertOk()->json('data'))->all();
    }

    /**
     * Production Finish Good (is_product=true, is_production=true): Qty (idx 3) is the count of
     * COMPLETED productions for the product, and must be server-side sortable. Both production
     * modes are served by the production_* routes gated by the production_material.view permission.
     */
    public function test_production_finish_good_qty_column_is_sortable(): void
    {
        $this->actingAs($this->userWith(['production_material.view']));
        $kw = 'PRODFGSORT'.uniqid();

        // Differing numbers of completed productions => differing qty.
        $pA = $this->makeProduct($kw.'-A', ['type' => Product::TYPE_PRODUCT]);
        $pB = $this->makeProduct($kw.'-B', ['type' => Product::TYPE_PRODUCT]);
        $pC = $this->makeProduct($kw.'-C', ['type' => Product::TYPE_PRODUCT]);

        $this->makeCompletedProductions($pA, 1);
        $this->makeCompletedProductions($pB, 3);
        $this->makeCompletedProductions($pC, 2);

        $asc = collect($this->fetchProduction($kw, true, 3, 'asc'))->pluck('qty')->all();
        $this->assertSame([1, 2, 3], $asc);

        $desc = collect($this->fetchProduction($kw, true, 3, 'desc'))->pluck('qty')->all();
        $this->assertSame([3, 2, 1], $desc);
    }

    /**
     * Production Raw Material (is_product=false, is_production=true): Qty (idx 3) is the count of
     * unassigned factory children. The server-side sort uses a BEST-EFFORT subquery that
     * approximates ProductChild::assignedTo(); for plain factory children with no links it
     * matches the displayed count exactly. Served by production_material.get_data.
     */
    public function test_production_raw_material_qty_column_is_sortable(): void
    {
        $this->actingAs($this->userWith(['production_material.view']));
        $kw = 'PRODRMSORT'.uniqid();

        // Differing numbers of unassigned factory children => differing qty.
        $pA = $this->makeProduct($kw.'-A', ['type' => Product::TYPE_RAW_MATERIAL]);
        $pB = $this->makeProduct($kw.'-B', ['type' => Product::TYPE_RAW_MATERIAL]);
        $pC = $this->makeProduct($kw.'-C', ['type' => Product::TYPE_RAW_MATERIAL]);

        $this->makeFactoryChildren($pA, 2);
        $this->makeFactoryChildren($pB, 1);
        $this->makeFactoryChildren($pC, 3);

        $asc = collect($this->fetchProduction($kw, false, 3, 'asc'))->pluck('qty')->all();
        $this->assertSame([1, 2, 3], $asc);

        $desc = collect($this->fetchProduction($kw, false, 3, 'desc'))->pluck('qty')->all();
        $this->assertSame([3, 2, 1], $desc);
    }

    private function makeCompletedProductions(Product $product, int $count): void
    {
        for ($i = 0; $i < $count; $i++) {
            Production::create([
                'product_id' => $product->id,
                'sku'        => 'PO-'.uniqid(),
                'name'       => 'PO '.uniqid(),
                'status'     => Production::STATUS_COMPLETED,
            ]);
        }
    }

    private function makeFactoryChildren(Product $product, int $count): void
    {
        for ($i = 0; $i < $count; $i++) {
            ProductChild::create([
                'product_id' => $product->id,
                'sku'        => 'PCFAC-'.uniqid(),
                'location'   => ProductChild::LOCATION_FACTORY,
            ]);
        }
    }
}
