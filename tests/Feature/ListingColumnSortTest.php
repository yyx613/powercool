<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * Covers server-side sortability of listing columns that previously had
 * `orderable: false` in the view and/or were missing from the controller order map.
 * Each test isolates its own rows (via a unique search keyword) so it is independent
 * of existing data in the shared dev DB.
 */
class ListingColumnSortTest extends TestCase
{
    use DatabaseTransactions;

    private function userWith(array $permissions): User
    {
        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }
        $user = User::factory()->create();
        foreach ($permissions as $perm) {
            $user->givePermissionTo($perm);
        }

        return $user;
    }

    /** Vehicle Service: "Service" column (idx 1) sorts by vehicle_services.type. */
    public function test_vehicle_service_service_column_is_sortable_by_type(): void
    {
        $this->actingAs($this->userWith(['vehicle.view']));

        $plate = 'SORT-'.uniqid();
        $vehicle = Vehicle::create(['plate_number' => $plate]);

        // Insert out of order: Inspection(3), Insurance(1), Roadtax(2)
        foreach ([3, 1, 2] as $type) {
            VehicleService::create([
                'vehicle_id' => $vehicle->id,
                'type'       => $type,
                'date'       => now(),
                'amount'     => 100,
            ]);
        }

        $asc = collect($this->getJson(route('vehicle_service.get_data', [
            'search' => ['value' => $plate],
            'order'  => [['column' => 1, 'dir' => 'asc']],
        ]))->assertOk()->json('data'))->pluck('service')->all();

        $this->assertSame(['Insurance', 'Roadtax', 'Inspection'], $asc);

        $desc = collect($this->getJson(route('vehicle_service.get_data', [
            'search' => ['value' => $plate],
            'order'  => [['column' => 1, 'dir' => 'desc']],
        ]))->assertOk()->json('data'))->pluck('service')->all();

        $this->assertSame(['Inspection', 'Roadtax', 'Insurance'], $desc);
    }
}
