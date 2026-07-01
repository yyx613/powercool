<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role as SpatieRole;
use Tests\TestCase;

/**
 * Server-side sorting of the DRIVER task listing.
 *
 * Driver column indices: 0 sku, 1 name, 2 due_date, 3 amount_to_collect,
 * 4 whatsapp_click_count, 5 status, 6 action.
 * This test covers the two newly-sortable columns: 4 and 5.
 */
class ListingSortTaskTest extends TestCase
{
    use DatabaseTransactions;

    private function driverUser(): User
    {
        // The driver listing/get_data is gated behind task_driver.view.
        Permission::firstOrCreate(['name' => 'task_driver.view', 'guard_name' => 'web']);
        $role = SpatieRole::firstOrCreate(['name' => 'Driver Sort Tester', 'guard_name' => 'web']);
        $role->givePermissionTo('task_driver.view');

        // No branch attached => BranchScope is a no-op for this user.
        $user = User::create([
            'name' => 'Driver Sort Tester',
            'email' => 'driver_sort_' . uniqid() . '@test.com',
            'password' => Hash::make('password'),
            'sku' => 'UDRV' . uniqid(),
        ]);
        $user->assignRole($role);

        return $user->fresh('roles');
    }

    private function makeCustomer(): Customer
    {
        return Customer::create([
            'name' => 'Driver Sort Customer',
            'phone' => '0123456789',
            'sku' => 'CDRV' . uniqid(),
            'status' => Customer::STATUS_ACTIVE,
        ]);
    }

    /**
     * Create a driver task. $marker is a shared substring put in the name so
     * the three rows can be isolated via the search box.
     */
    private function makeDriverTask(Customer $customer, string $marker, int $clicks, int $status): Task
    {
        return Task::create([
            'customer_id' => $customer->id,
            'type' => Task::TYPE_DRIVER,
            'sku' => 'TDRV' . uniqid(),
            'name' => $marker . ' ' . uniqid(),
            'desc' => 'desc',
            'start_date' => '2026-07-01',
            'due_date' => '2026-07-10',
            'status' => $status,
            'amount_to_collect' => 0,
            'whatsapp_click_count' => $clicks,
        ]);
    }

    public function test_driver_list_sorts_by_whatsapp_click_count(): void
    {
        $user = $this->driverUser();
        $customer = $this->makeCustomer();
        $marker = 'WAMARK' . uniqid();

        $low = $this->makeDriverTask($customer, $marker, 1, Task::STATUS_TO_DO);
        $mid = $this->makeDriverTask($customer, $marker, 5, Task::STATUS_DOING);
        $high = $this->makeDriverTask($customer, $marker, 9, Task::STATUS_COMPLETED);

        $asc = collect($this->actingAs($user)->getJson(route('task.get_data', [
            'role'   => 'driver',
            'search' => ['value' => $marker],
            'order'  => [['column' => 4, 'dir' => 'asc']],
        ]))->assertOk()->json('data'))->pluck('whatsapp_click_count')->all();
        $this->assertSame([1, 5, 9], $asc);

        $desc = collect($this->actingAs($user)->getJson(route('task.get_data', [
            'role'   => 'driver',
            'search' => ['value' => $marker],
            'order'  => [['column' => 4, 'dir' => 'desc']],
        ]))->assertOk()->json('data'))->pluck('whatsapp_click_count')->all();
        $this->assertSame([9, 5, 1], $desc);
    }

    public function test_driver_list_sorts_by_status(): void
    {
        $user = $this->driverUser();
        $customer = $this->makeCustomer();
        $marker = 'STMARK' . uniqid();

        $this->makeDriverTask($customer, $marker, 2, Task::STATUS_TO_DO);       // 1
        $this->makeDriverTask($customer, $marker, 7, Task::STATUS_IN_REVIEW);   // 3
        $this->makeDriverTask($customer, $marker, 4, Task::STATUS_COMPLETED);   // 4

        $asc = collect($this->actingAs($user)->getJson(route('task.get_data', [
            'role'   => 'driver',
            'search' => ['value' => $marker],
            'order'  => [['column' => 5, 'dir' => 'asc']],
        ]))->assertOk()->json('data'))->pluck('status')->all();
        $this->assertSame([
            Task::STATUS_TO_DO,
            Task::STATUS_IN_REVIEW,
            Task::STATUS_COMPLETED,
        ], $asc);

        $desc = collect($this->actingAs($user)->getJson(route('task.get_data', [
            'role'   => 'driver',
            'search' => ['value' => $marker],
            'order'  => [['column' => 5, 'dir' => 'desc']],
        ]))->assertOk()->json('data'))->pluck('status')->all();
        $this->assertSame([
            Task::STATUS_COMPLETED,
            Task::STATUS_IN_REVIEW,
            Task::STATUS_TO_DO,
        ], $desc);
    }
}
