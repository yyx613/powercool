<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class TransferModalComposerTest extends TestCase
{
    use DatabaseTransactions;

    private function ensurePermissions(): void
    {
        Permission::firstOrCreate(['name' => 'sale.sale_order.view', 'guard_name' => 'web']);
    }

    private function userWith(array $permissions, ?int $branchLocation = null): User
    {
        $this->ensurePermissions();
        $user = User::factory()->create();
        foreach ($permissions as $perm) {
            $user->givePermissionTo($perm);
        }
        if ($branchLocation !== null) {
            Branch::create([
                'object_type' => User::class,
                'object_id' => $user->id,
                'location' => $branchLocation,
            ]);
        }

        return $user;
    }

    public function test_so_listing_renders_for_user_without_branch(): void
    {
        $this->actingAs($this->userWith(['sale.sale_order.view']));

        $response = $this->get(route('sale_order.index'));

        $response->assertStatus(200);
        $response->assertSee('transfer-so-modal', false);
    }

    public function test_so_listing_renders_for_user_with_branch(): void
    {
        $this->actingAs($this->userWith(['sale.sale_order.view'], Branch::LOCATION_KL));

        $response = $this->get(route('sale_order.index'));

        $response->assertStatus(200);
        $response->assertSee('transfer-so-modal', false);
    }
}
