<?php

namespace Tests\Feature;

use App\Models\CustomizeProduct;
use App\Models\Production;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * Locks in server-side sorting of the Customize Products listing "Dimensions"
 * column (idx 4).
 *
 * Dimensions is displayed as "L × W × H mm", a composite of three DB columns.
 * Sorting uses `length` as the leading-dimension proxy. This test creates three
 * completed-production customize products with distinct length values and asserts
 * the returned 'dimensions' come back ordered by length asc & desc.
 */
class ListingSortCustomizeProductTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * A user with no branch and no role bypasses BranchScope and only needs the
     * listing's view permission.
     */
    private function viewer()
    {
        Permission::firstOrCreate(['name' => 'inventory.customize.view', 'guard_name' => 'web']);

        $user = \App\Models\User::factory()->create();
        $user->givePermissionTo('inventory.customize.view');

        return $user->fresh('roles');
    }

    /**
     * Create a completed production plus a customize product with the given length,
     * tagged with a shared keyword so we can isolate this test's rows via search.
     */
    private function makeProduct(string $kw, $length): CustomizeProduct
    {
        $category = \App\Models\InventoryCategory::create([
            'name' => 'CP Sort Cat ' . uniqid(),
            'company_group' => 1,
            'is_active' => 1,
        ]);

        $product = \App\Models\Product::create([
            'inventory_category_id' => $category->id,
            'type' => 1,
            'sku' => 'P-' . uniqid(),
            'model_desc' => 'CP Sort Product',
            'is_active' => 1,
        ]);

        $production = Production::create([
            'product_id' => $product->id,
            'sku' => 'PRD-' . uniqid(),
            'name' => 'CP Sort Production',
            'start_date' => now()->toDateString(),
            'due_date' => now()->toDateString(),
            'status' => Production::STATUS_COMPLETED,
        ]);

        return CustomizeProduct::create([
            'production_id' => $production->id,
            'sku' => $kw . '-' . uniqid(),
            'name' => $kw,
            'length' => $length,
            'width' => 10,
            'height' => 10,
        ]);
    }

    private function orderedDimensions(string $kw, string $dir): array
    {
        return collect($this->getJson(route('customize.get_data', [
            'search' => ['value' => $kw],
            'order' => [['column' => 4, 'dir' => $dir]],
        ]))->assertOk()->json('data'))->pluck('dimensions')->all();
    }

    public function test_dimensions_column_sorts_ascending_by_length(): void
    {
        $kw = 'CPSORT' . uniqid();
        $this->makeProduct($kw, 300);
        $this->makeProduct($kw, 100);
        $this->makeProduct($kw, 200);

        $this->actingAs($this->viewer());

        $this->assertSame([
            '100.00 × 10.00 × 10.00 mm',
            '200.00 × 10.00 × 10.00 mm',
            '300.00 × 10.00 × 10.00 mm',
        ], $this->orderedDimensions($kw, 'asc'));
    }

    public function test_dimensions_column_sorts_descending_by_length(): void
    {
        $kw = 'CPSORT' . uniqid();
        $this->makeProduct($kw, 300);
        $this->makeProduct($kw, 100);
        $this->makeProduct($kw, 200);

        $this->actingAs($this->viewer());

        $this->assertSame([
            '300.00 × 10.00 × 10.00 mm',
            '200.00 × 10.00 × 10.00 mm',
            '100.00 × 10.00 × 10.00 mm',
        ], $this->orderedDimensions($kw, 'desc'));
    }
}
