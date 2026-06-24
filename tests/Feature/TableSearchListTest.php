<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role as SpatieRole;
use Tests\TestCase;

/**
 * End-to-end coverage for the shared TableSearch helper wired into list getData()
 * endpoints: the global search box must match plain text columns AND integer columns
 * that are rendered as text labels in the UI (e.g. status 1 => "Active").
 *
 * Uses a branchless, non-super-admin user so the BranchScope is a no-op (it only
 * filters when the user has a branch or is a super admin with an as_branch selected).
 */
class TableSearchListTest extends TestCase
{
    use DatabaseTransactions;

    private function userWith(string $permission): User
    {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        $role = SpatieRole::firstOrCreate(['name' => 'TableSearch Tester '.$permission, 'guard_name' => 'web']);
        $role->givePermissionTo($permission);

        $user = User::factory()->create();
        $user->assignRole($role);

        return $user->fresh(['roles']);
    }

    private function fetchIds(string $routeName, string $keyword): array
    {
        $response = $this->getJson(route($routeName, ['search' => ['value' => $keyword]]));
        $response->assertOk();

        return collect($response->json('data'))->pluck('id')->all();
    }

    public function test_area_search_matches_text_and_coded_status(): void
    {
        $this->actingAs($this->userWith('setting.area.view'));

        $active = Area::create(['name' => 'Northern Zone '.uniqid(), 'is_active' => 1]);
        $inactive = Area::create(['name' => 'Southern Zone '.uniqid(), 'is_active' => 0]);

        // Plain text column.
        $byName = $this->fetchIds('area.get_data', 'Northern Zone');
        $this->assertContains($active->id, $byName);
        $this->assertNotContains($inactive->id, $byName);

        // Coded status label: "inactive" must match only is_active = 0.
        $byInactive = $this->fetchIds('area.get_data', 'inactive');
        $this->assertContains($inactive->id, $byInactive);
        $this->assertNotContains($active->id, $byInactive);

        // "active" is a substring of both "Active" and "Inactive" -> both match.
        $byActive = $this->fetchIds('area.get_data', 'active');
        $this->assertContains($active->id, $byActive);
        $this->assertContains($inactive->id, $byActive);
    }

    public function test_vehicle_search_matches_coded_status_and_type(): void
    {
        $this->actingAs($this->userWith('vehicle.create'));

        $activeCar = Vehicle::create([
            'plate_number' => 'CAR'.uniqid(),
            'status' => Vehicle::STATUS_ACTIVE,
            'type' => Vehicle::TYPE_CAR,
        ]);
        $soldLorry = Vehicle::create([
            'plate_number' => 'LRY'.uniqid(),
            'status' => Vehicle::STATUS_SOLD,
            'type' => Vehicle::TYPE_LORRY,
        ]);

        // Coded status: "sold" -> only the sold vehicle.
        $bySold = $this->fetchIds('vehicle.get_data', 'sold');
        $this->assertContains($soldLorry->id, $bySold);
        $this->assertNotContains($activeCar->id, $bySold);

        // Coded type: "lorry" -> only the lorry.
        $byLorry = $this->fetchIds('vehicle.get_data', 'lorry');
        $this->assertContains($soldLorry->id, $byLorry);
        $this->assertNotContains($activeCar->id, $byLorry);

        // Plain text column still works.
        $byPlate = $this->fetchIds('vehicle.get_data', $activeCar->plate_number);
        $this->assertContains($activeCar->id, $byPlate);
        $this->assertNotContains($soldLorry->id, $byPlate);
    }
}
