<?php

namespace Tests\Feature;

use App\Models\InventoryCategory;
use App\Models\Product;
use App\Models\ProductChild;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ProductChildCreatedByTest extends TestCase
{
    use DatabaseTransactions;

    private function makeProduct(): Product
    {
        return Product::create([
            'inventory_category_id' => InventoryCategory::first()->id,
            'type' => Product::TYPE_PRODUCT,
            'sku' => 'TEST-CB-'.uniqid(),
            'model_desc' => 'Created-By Test',
            'is_active' => true,
        ]);
    }

    public function test_product_child_persists_created_by_and_created_at(): void
    {
        $user = User::first();
        $this->assertNotNull($user, 'Seeded user required for this test');

        $product = $this->makeProduct();

        $child = ProductChild::create([
            'product_id' => $product->id,
            'sku' => 'SN-CB-'.uniqid(),
            'location' => ProductChild::LOCATION_WAREHOUSE,
            'created_by' => $user->id,
        ]);

        $child->refresh();

        $this->assertSame($user->id, (int) $child->created_by);
        $this->assertNotNull($child->created_at);
    }

    public function test_created_by_relationship_returns_user(): void
    {
        $user = User::first();
        $this->assertNotNull($user);

        $product = $this->makeProduct();

        $child = ProductChild::create([
            'product_id' => $product->id,
            'sku' => 'SN-CB-REL-'.uniqid(),
            'location' => ProductChild::LOCATION_WAREHOUSE,
            'created_by' => $user->id,
        ]);

        $this->assertInstanceOf(User::class, $child->createdBy);
        $this->assertSame($user->id, $child->createdBy->id);
    }

    public function test_created_by_is_nullable(): void
    {
        $product = $this->makeProduct();

        $child = ProductChild::create([
            'product_id' => $product->id,
            'sku' => 'SN-CB-NULL-'.uniqid(),
            'location' => ProductChild::LOCATION_WAREHOUSE,
        ]);

        $child->refresh();

        $this->assertNull($child->created_by);
        $this->assertNull($child->createdBy);
    }
}
