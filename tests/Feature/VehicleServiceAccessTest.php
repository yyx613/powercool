<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class VehicleServiceAccessTest extends TestCase
{
    use DatabaseTransactions;

    private function ensurePermissions(): void
    {
        Permission::firstOrCreate(['name' => 'vehicle.view', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'setting.service.view', 'guard_name' => 'web']);
    }

    private function userWith(array $permissions): User
    {
        $this->ensurePermissions();
        $user = User::factory()->create();
        foreach ($permissions as $perm) {
            $user->givePermissionTo($perm);
        }

        return $user;
    }

    public function test_vehicle_view_grants_access_to_vehicle_service_index(): void
    {
        $this->actingAs($this->userWith(['vehicle.view']));

        $response = $this->get(route('vehicle_service.index'));

        $this->assertNotEquals(403, $response->status(),
            'User with vehicle.view should not be forbidden from /vehicle-service');
    }

    public function test_no_permission_denied_from_vehicle_service_index(): void
    {
        $this->actingAs($this->userWith([]));

        $response = $this->get(route('vehicle_service.index'));

        $response->assertStatus(403);
    }

    public function test_vehicle_view_alone_does_not_grant_settings_service_index(): void
    {
        $this->actingAs($this->userWith(['vehicle.view']));

        $response = $this->get(route('service.index'));

        $response->assertStatus(403);
    }

    public function test_setting_service_view_grants_access_to_settings_service_index(): void
    {
        $this->actingAs($this->userWith(['setting.service.view']));

        $response = $this->get(route('service.index'));

        $this->assertNotEquals(403, $response->status(),
            'User with setting.service.view should not be forbidden from /service');
    }
}
