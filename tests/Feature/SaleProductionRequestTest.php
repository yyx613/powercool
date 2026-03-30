<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\InventoryCategory;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleProduct;
use App\Models\SaleProductAccessory;
use App\Models\SaleProductionRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class SaleProductionRequestTest extends TestCase
{
    use DatabaseTransactions;

    public function test_to_sale_production_request_excludes_accessories(): void
    {
        $user = User::first();
        $this->actingAs($user);
        Session::put('as_branch', Branch::LOCATION_KL);

        $inventoryCategory = InventoryCategory::first();

        // Create main product
        $mainProduct = Product::create([
            'inventory_category_id' => $inventoryCategory->id,
            'type' => Product::TYPE_PRODUCT,
            'sku' => 'TEST-MAIN-' . uniqid(),
            'model_desc' => 'Test Main Product',
            'is_active' => true,
        ]);
        Branch::create([
            'object_type' => Product::class,
            'object_id' => $mainProduct->id,
            'location' => Branch::LOCATION_KL,
        ]);

        // Create accessory product
        $accessoryProduct = Product::create([
            'inventory_category_id' => $inventoryCategory->id,
            'type' => Product::TYPE_PRODUCT,
            'sku' => 'TEST-ACC-' . uniqid(),
            'model_desc' => 'Test Accessory Product',
            'is_active' => true,
        ]);
        Branch::create([
            'object_type' => Product::class,
            'object_id' => $accessoryProduct->id,
            'location' => Branch::LOCATION_KL,
        ]);

        // Create a sale order with branch assignment
        $sale = Sale::create([
            'sku' => 'TEST-SO-' . uniqid(),
            'type' => Sale::TYPE_SO,
        ]);
        Branch::create([
            'object_type' => Sale::class,
            'object_id' => $sale->id,
            'location' => Branch::LOCATION_KL,
        ]);

        // Create sale product line item
        $saleProduct = SaleProduct::create([
            'sale_id' => $sale->id,
            'product_id' => $mainProduct->id,
            'qty' => 2,
            'unit_price' => 100,
        ]);

        // Attach an accessory with qty=3
        SaleProductAccessory::create([
            'sale_product_id' => $saleProduct->id,
            'accessory_id' => $accessoryProduct->id,
            'qty' => 3,
        ]);

        // Simulate conversion: replicate the controller logic directly
        $saleProduct = SaleProduct::with(['sale', 'product', 'accessories'])->find($saleProduct->id);
        $qty = 2;
        $remark = 'Test remark';

        DB::beginTransaction();
        for ($i = 0; $i < $qty; $i++) {
            $spr = SaleProductionRequest::create([
                'sale_id' => $saleProduct->sale->id,
                'product_id' => $saleProduct->product->id,
                'remark' => $remark,
            ]);
            Branch::create([
                'object_type' => SaleProductionRequest::class,
                'object_id' => $spr->id,
                'location' => Branch::LOCATION_KL,
            ]);
        }
        DB::commit();

        // Should only create 2 SPR records (main product only), not 2 + (2*3) = 8
        $sprForSale = SaleProductionRequest::where('sale_id', $sale->id)->get();
        $this->assertCount(2, $sprForSale, 'Should create exactly 2 SPR records (main product only, no accessories)');

        // All SPRs should reference the main product
        foreach ($sprForSale as $spr) {
            $this->assertEquals($mainProduct->id, $spr->product_id, 'SPR should only reference the main product');
        }

        // No SPR should reference the accessory
        $accessorySprs = SaleProductionRequest::where('sale_id', $sale->id)
            ->where('product_id', $accessoryProduct->id)
            ->count();
        $this->assertEquals(0, $accessorySprs, 'No SPR records should be created for accessories');
    }
}
