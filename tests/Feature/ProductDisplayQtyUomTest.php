<?php

namespace Tests\Feature;

use App\Models\InventoryCategory;
use App\Models\Product;
use App\Models\UOM;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ProductDisplayQtyUomTest extends TestCase
{
    use DatabaseTransactions;

    private function makeRawMaterial(array $overrides = []): Product
    {
        return Product::create(array_merge([
            'inventory_category_id' => InventoryCategory::first()->id,
            'type' => Product::TYPE_RAW_MATERIAL,
            'sku' => 'TEST-DISP-'.uniqid(),
            'model_desc' => 'Display Qty/UOM Test',
            'is_sparepart' => false,
            'is_active' => true,
        ], $overrides));
    }

    public function test_display_qty_and_display_uom_persist_on_raw_material(): void
    {
        $uom = UOM::first();
        $this->assertNotNull($uom, 'Seeded UOM required for this test');

        $prod = $this->makeRawMaterial([
            'display_qty' => 24.5,
            'display_uom' => $uom->id,
        ]);

        $prod->refresh();

        $this->assertEquals(24.5, (float) $prod->display_qty);
        $this->assertEquals($uom->id, (int) $prod->display_uom);
    }

    public function test_display_uom_unit_relationship_resolves(): void
    {
        $uom = UOM::first();
        $this->assertNotNull($uom);

        $prod = $this->makeRawMaterial([
            'display_qty' => 12,
            'display_uom' => $uom->id,
        ]);

        $this->assertInstanceOf(UOM::class, $prod->displayUomUnit);
        $this->assertSame($uom->id, $prod->displayUomUnit->id);
    }

    public function test_display_fields_nullable(): void
    {
        $prod = $this->makeRawMaterial();

        $prod->refresh();

        $this->assertNull($prod->display_qty);
        $this->assertNull($prod->display_uom);
        $this->assertNull($prod->displayUomUnit);
    }

    public function test_display_uom_unit_serializes_as_nested_relation(): void
    {
        $uom = UOM::first();
        $this->assertNotNull($uom);

        $prod = $this->makeRawMaterial([
            'display_qty' => 6,
            'display_uom' => $uom->id,
        ]);

        $arr = Product::with('displayUomUnit')->find($prod->id)->toArray();

        $this->assertArrayHasKey('display_uom_unit', $arr);
        $this->assertSame($uom->name, $arr['display_uom_unit']['name']);
        $this->assertSame('6.00', (string) $arr['display_qty']);
    }
}
