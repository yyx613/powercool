<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class GrnStockInPermissionTest extends TestCase
{
    use DatabaseTransactions;

    private function ensurePermissions(): void
    {
        Permission::firstOrCreate(['name' => 'grn.view', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'grn.stock_in', 'guard_name' => 'web']);
    }

    public function test_user_with_grn_view_only_cannot_stock_in(): void
    {
        $this->ensurePermissions();

        $user = User::factory()->create();
        $user->givePermissionTo('grn.view');
        $this->actingAs($user);

        $response = $this->post(route('grn.stock_in'));

        $response->assertStatus(403);
    }

    public function test_user_with_grn_stock_in_permission_passes_the_gate(): void
    {
        $this->ensurePermissions();

        $user = User::factory()->create();
        $user->givePermissionTo(['grn.view', 'grn.stock_in']);
        $this->actingAs($user);

        $response = $this->post(route('grn.stock_in'));

        $this->assertNotEquals(
            403,
            $response->status(),
            'User with grn.stock_in must not be blocked by the can:grn.stock_in gate'
        );
    }
}
