<?php

namespace Tests\Feature;

use App\Models\GRN;
use App\Models\InventoryCategory;
use App\Models\Product;
use App\Models\Scopes\BranchScope;
use App\Models\Supplier;
use App\Services\StockCardService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class StockCardBrandFilterTest extends TestCase
{
    use DatabaseTransactions;

    private function makeProduct(?int $brand, ?int $companyGroup = 1): Product
    {
        return Product::create([
            'inventory_category_id' => InventoryCategory::first()->id,
            'type' => Product::TYPE_PRODUCT,
            'sku' => 'TEST-BR-'.uniqid(),
            'model_desc' => 'Stock Card Brand Test',
            'is_sparepart' => false,
            'is_active' => true,
            'cost' => 100,
            'company_group' => $companyGroup,
            'brand' => $brand,
        ]);
    }

    private function supplierId(): int
    {
        $supplier = Supplier::withoutGlobalScope(BranchScope::class)->first();
        $this->assertNotNull($supplier, 'At least one Supplier row required for this test');
        return (int) $supplier->id;
    }

    private function makeGrnForProduct(Product $product): void
    {
        GRN::create([
            'sku' => 'GR-TEST-'.uniqid(),
            'product_id' => $product->id,
            'supplier_id' => $this->supplierId(),
            'qty' => 5,
            'unit_price' => 100,
            'total_price' => 500,
            'status' => GRN::STATUS_ACTIVE,
        ]);
    }

    public function test_brand_label_for_returns_expected_labels(): void
    {
        $this->assertSame('IMAX', StockCardService::brandLabelFor(1));
        $this->assertSame('Hi-Ten', StockCardService::brandLabelFor(2));
        $this->assertSame('Unassigned', StockCardService::brandLabelFor(null));
        $this->assertSame('Unassigned', StockCardService::brandLabelFor(''));
        $this->assertSame('Unassigned', StockCardService::brandLabelFor(99));
    }

    public function test_get_movements_filters_by_brand_imax(): void
    {
        $imax = $this->makeProduct(1);
        $hiTen = $this->makeProduct(2);
        $this->makeGrnForProduct($imax);
        $this->makeGrnForProduct($hiTen);

        $items = (new StockCardService)->getMovements(null, null, null, null, 1);

        $productIds = array_map(fn ($i) => $i['product']->id, $items);
        $this->assertContains($imax->id, $productIds);
        $this->assertNotContains($hiTen->id, $productIds);
        foreach ($items as $item) {
            $this->assertSame('IMAX', $item['brand_label']);
        }
    }

    public function test_get_movements_filters_by_brand_hi_ten(): void
    {
        $imax = $this->makeProduct(1);
        $hiTen = $this->makeProduct(2);
        $this->makeGrnForProduct($imax);
        $this->makeGrnForProduct($hiTen);

        $items = (new StockCardService)->getMovements(null, null, null, null, 2);

        $productIds = array_map(fn ($i) => $i['product']->id, $items);
        $this->assertContains($hiTen->id, $productIds);
        $this->assertNotContains($imax->id, $productIds);
        foreach ($items as $item) {
            $this->assertSame('Hi-Ten', $item['brand_label']);
        }
    }

    public function test_get_movements_combines_company_group_and_brand_filters(): void
    {
        $powerCoolImax = $this->makeProduct(1, 1);
        $powerCoolHiTen = $this->makeProduct(2, 1);
        $hiTenImax = $this->makeProduct(1, 2);
        $this->makeGrnForProduct($powerCoolImax);
        $this->makeGrnForProduct($powerCoolHiTen);
        $this->makeGrnForProduct($hiTenImax);

        $items = (new StockCardService)->getMovements(null, null, null, 1, 1);

        $productIds = array_map(fn ($i) => $i['product']->id, $items);
        $this->assertContains($powerCoolImax->id, $productIds);
        $this->assertNotContains($powerCoolHiTen->id, $productIds);
        $this->assertNotContains($hiTenImax->id, $productIds);
    }
}
