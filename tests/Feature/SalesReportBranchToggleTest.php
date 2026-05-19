<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Session;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role as SpatieRole;
use Tests\TestCase;

class SalesReportBranchToggleTest extends TestCase
{
    use DatabaseTransactions;

    private const TOGGLE_MARKER = 'id="as_branch"';

    private function ensureRoleAndPermissions(): SpatieRole
    {
        Permission::firstOrCreate(['name' => 'report.sales', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'report.production', 'guard_name' => 'web']);

        $role = SpatieRole::firstOrCreate(['name' => 'Marketing Manager', 'guard_name' => 'web']);
        $role->givePermissionTo(['report.sales', 'report.production']);

        return $role;
    }

    private function marketingManagerInKL(): User
    {
        $role = $this->ensureRoleAndPermissions();

        $user = User::factory()->create();
        $user->assignRole($role);
        Branch::create([
            'object_type' => User::class,
            'object_id'   => $user->id,
            'location'    => Branch::LOCATION_KL,
        ]);

        return $user->fresh(['branch', 'roles']);
    }

    private function saleUserInKL(): User
    {
        Permission::firstOrCreate(['name' => 'report.sales', 'guard_name' => 'web']);
        $role = SpatieRole::firstOrCreate(['name' => 'Sale', 'guard_name' => 'web']);
        $role->givePermissionTo('report.sales');

        $user = User::factory()->create();
        $user->assignRole($role);
        Branch::create([
            'object_type' => User::class,
            'object_id'   => $user->id,
            'location'    => Branch::LOCATION_KL,
        ]);

        return $user->fresh(['branch', 'roles']);
    }

    public function test_marketing_manager_sees_branch_toggle_on_sales_report(): void
    {
        $this->actingAs($this->marketingManagerInKL());
        Session::put('as_branch', Branch::LOCATION_KL);

        $response = $this->get(route('report.sales_report.index'));

        $response->assertOk();
        $response->assertSee(self::TOGGLE_MARKER, false);
    }

    public function test_marketing_manager_does_not_see_branch_toggle_on_other_report(): void
    {
        $this->actingAs($this->marketingManagerInKL());
        Session::put('as_branch', Branch::LOCATION_KL);

        $response = $this->get(route('report.production_report.index'));

        $response->assertOk();
        $response->assertDontSee(self::TOGGLE_MARKER, false);
    }

    public function test_sale_role_with_branch_does_not_see_toggle_on_sales_report(): void
    {
        $this->actingAs($this->saleUserInKL());
        Session::put('as_branch', Branch::LOCATION_KL);

        $response = $this->get(route('report.sales_report.index'));

        $response->assertOk();
        $response->assertDontSee(self::TOGGLE_MARKER, false);
    }

    public function test_marketing_manager_can_call_as_branch_toggle_endpoint(): void
    {
        $this->actingAs($this->marketingManagerInKL());
        Session::put('as_branch', Branch::LOCATION_KL);

        $response = $this->get(route('user_management.as_branch', ['branch' => Branch::LOCATION_PENANG]));

        $response->assertOk();
        $this->assertEquals(Branch::LOCATION_PENANG, Session::get('as_branch'));
    }
}
