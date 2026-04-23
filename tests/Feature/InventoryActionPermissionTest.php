<?php

namespace Tests\Feature;

use App\Models\ProductChild;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class InventoryActionPermissionTest extends TestCase
{
    use DatabaseTransactions;

    private function aProductChildId(): int
    {
        $pc = ProductChild::first();
        $this->assertNotNull($pc, 'Expected at least one ProductChild in the test DB');

        return $pc->id;
    }

    private function userWithCategoryViewOnly(): User
    {
        Permission::firstOrCreate(['name' => 'inventory.category.view', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'inventory.view_action', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->givePermissionTo('inventory.category.view');

        return $user;
    }

    public function test_stock_in_requires_inventory_view_action(): void
    {
        $this->actingAs($this->userWithCategoryViewOnly());

        $response = $this->get(route('inventory_category.stock_in', $this->aProductChildId()));

        $response->assertStatus(403);
    }

    public function test_stock_out_requires_inventory_view_action(): void
    {
        $this->actingAs($this->userWithCategoryViewOnly());

        $response = $this->get(route('inventory_category.stock_out', $this->aProductChildId()));

        $response->assertStatus(403);
    }

    public function test_transfer_requires_inventory_view_action(): void
    {
        $this->actingAs($this->userWithCategoryViewOnly());

        $response = $this->get(route('inventory_category.transfer', $this->aProductChildId()));

        $response->assertStatus(403);
    }

    public function test_to_warehouse_requires_inventory_view_action(): void
    {
        $this->actingAs($this->userWithCategoryViewOnly());

        $response = $this->get(route('inventory_category.to_warehouse', $this->aProductChildId()));

        $response->assertStatus(403);
    }
}
