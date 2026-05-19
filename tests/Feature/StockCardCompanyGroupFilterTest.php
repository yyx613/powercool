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

class StockCardCompanyGroupFilterTest extends TestCase
{
    use DatabaseTransactions;

    private function makeProduct(?int $companyGroup): Product
    {
        return Product::create([
            'inventory_category_id' => InventoryCategory::first()->id,
            'type' => Product::TYPE_PRODUCT,
            'sku' => 'TEST-CG-'.uniqid(),
            'model_desc' => 'Stock Card Company Group Test',
            'is_sparepart' => false,
            'is_active' => true,
            'cost' => 100,
            'company_group' => $companyGroup,
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

    public function test_company_label_for_returns_expected_labels(): void
    {
        $this->assertSame('Power Cool', StockCardService::companyLabelFor(1));
        $this->assertSame('Hi-Ten', StockCardService::companyLabelFor(2));
        $this->assertSame('Unassigned', StockCardService::companyLabelFor(null));
        $this->assertSame('Unassigned', StockCardService::companyLabelFor(''));
        $this->assertSame('Unassigned', StockCardService::companyLabelFor(99));
    }

    public function test_company_header_for_returns_legal_names_with_power_cool_fallback(): void
    {
        $powerCool = 'POWER COOL EQUIPMENTS (M) SDN BHD';
        $hiTen = 'HI-TEN TRADING SDN BHD';

        $this->assertSame($powerCool, StockCardService::companyHeaderFor(1));
        $this->assertSame($hiTen, StockCardService::companyHeaderFor(2));
        $this->assertSame($powerCool, StockCardService::companyHeaderFor(null));
        $this->assertSame($powerCool, StockCardService::companyHeaderFor(''));
        $this->assertSame($powerCool, StockCardService::companyHeaderFor(99));
    }

    public function test_get_movements_filters_by_company_group_power_cool(): void
    {
        $powerCool = $this->makeProduct(1);
        $hiTen = $this->makeProduct(2);
        $this->makeGrnForProduct($powerCool);
        $this->makeGrnForProduct($hiTen);

        $items = (new StockCardService)->getMovements(null, null, null, 1);

        $productIds = array_map(fn ($i) => $i['product']->id, $items);
        $this->assertContains($powerCool->id, $productIds);
        $this->assertNotContains($hiTen->id, $productIds);
        foreach ($items as $item) {
            $this->assertSame('Power Cool', $item['company_label']);
        }
    }

    public function test_get_movements_filters_by_company_group_hi_ten(): void
    {
        $powerCool = $this->makeProduct(1);
        $hiTen = $this->makeProduct(2);
        $this->makeGrnForProduct($powerCool);
        $this->makeGrnForProduct($hiTen);

        $items = (new StockCardService)->getMovements(null, null, null, 2);

        $productIds = array_map(fn ($i) => $i['product']->id, $items);
        $this->assertContains($hiTen->id, $productIds);
        $this->assertNotContains($powerCool->id, $productIds);
        foreach ($items as $item) {
            $this->assertSame('Hi-Ten', $item['company_label']);
        }
    }

    public function test_get_movements_unfiltered_includes_unassigned_with_label(): void
    {
        $unassigned = $this->makeProduct(null);
        $this->makeGrnForProduct($unassigned);

        $items = (new StockCardService)->getMovements(null, null, null, null);

        $found = null;
        foreach ($items as $item) {
            if ($item['product']->id === $unassigned->id) {
                $found = $item;
                break;
            }
        }

        $this->assertNotNull($found, 'Unassigned product should appear when no company filter is applied');
        $this->assertSame('Unassigned', $found['company_label']);
    }
}
