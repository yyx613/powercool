<?php

namespace Tests\Feature;

use App\Models\InventoryCategory;
use App\Models\Product;
use App\Models\ProductChild;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ProductChildUniqueSkuTest extends TestCase
{
    use DatabaseTransactions;

    private function makeProduct(string $suffix): Product
    {
        return Product::create([
            'inventory_category_id' => InventoryCategory::first()->id,
            'type' => Product::TYPE_PRODUCT,
            'sku' => 'TEST-SKU-DUP-'.$suffix.'-'.uniqid(),
            'model_desc' => 'Test '.$suffix,
            'is_active' => true,
        ]);
    }

    public function test_rejects_duplicate_sku_on_same_product(): void
    {
        $product = $this->makeProduct('same');
        $sku = 'SERIAL-'.uniqid();

        ProductChild::create([
            'product_id' => $product->id,
            'sku' => $sku,
            'location' => ProductChild::LOCATION_WAREHOUSE,
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/Duplicate product_children\.sku/');

        ProductChild::create([
            'product_id' => $product->id,
            'sku' => $sku,
            'location' => ProductChild::LOCATION_WAREHOUSE,
        ]);
    }

    public function test_rejects_duplicate_sku_across_different_products(): void
    {
        $productA = $this->makeProduct('A');
        $productB = $this->makeProduct('B');
        $sku = 'SERIAL-'.uniqid();

        ProductChild::create([
            'product_id' => $productA->id,
            'sku' => $sku,
            'location' => ProductChild::LOCATION_WAREHOUSE,
        ]);

        $this->expectException(\RuntimeException::class);

        ProductChild::create([
            'product_id' => $productB->id,
            'sku' => $sku,
            'location' => ProductChild::LOCATION_WAREHOUSE,
        ]);
    }

    public function test_allows_branch_transfer_replica_to_share_sku(): void
    {
        $productA = $this->makeProduct('src');
        $productB = $this->makeProduct('dst');
        $sku = 'SERIAL-'.uniqid();

        $original = ProductChild::create([
            'product_id' => $productA->id,
            'sku' => $sku,
            'location' => ProductChild::LOCATION_WAREHOUSE,
        ]);

        // Branch-transfer replica: carries transferred_from, so it's exempt.
        $replica = ProductChild::create([
            'product_id' => $productB->id,
            'sku' => $sku,
            'location' => ProductChild::LOCATION_WAREHOUSE,
            'transferred_from' => $original->id,
        ]);

        $this->assertNotNull($replica->id);
        $this->assertSame($sku, $replica->sku);
    }

    public function test_allows_reuse_after_soft_delete(): void
    {
        // Guard shouldn't block: the previous row is soft-deleted but still
        // physically present, yet SKU must be reusable for a fresh unit.
        // Current rule uses withoutGlobalScope(BranchScope) but respects the
        // model's default SoftDeletes scope, so soft-deleted rows are skipped.
        $product = $this->makeProduct('reuse');
        $sku = 'SERIAL-'.uniqid();

        $first = ProductChild::create([
            'product_id' => $product->id,
            'sku' => $sku,
            'location' => ProductChild::LOCATION_WAREHOUSE,
        ]);
        $first->delete();

        $second = ProductChild::create([
            'product_id' => $product->id,
            'sku' => $sku,
            'location' => ProductChild::LOCATION_WAREHOUSE,
        ]);

        $this->assertNotNull($second->id);
    }
}
