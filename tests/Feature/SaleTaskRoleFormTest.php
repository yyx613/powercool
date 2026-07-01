<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Milestone;
use App\Models\Role as AppRole;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Session;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role as SpatieRole;
use Tests\TestCase;

/**
 * Covers role-based behaviour of the sale task form:
 * - Sales person: no Status field (defaults To Do), no Assigned field (auto self-assign).
 * - Marketing Manager: no Status field, but picks the assignees.
 * - Service task milestones carry an editable date & time saved to the pivot.
 */
class SaleTaskRoleFormTest extends TestCase
{
    use DatabaseTransactions;

    private function makeUser(): User
    {
        return User::create([
            'name' => 'Sale Tester',
            'email' => 'sale_role_' . uniqid() . '@test.com',
            'password' => Hash::make('password'),
            'sku' => 'USALE' . uniqid(),
        ]);
    }

    private function salesPerson(): User
    {
        Permission::firstOrCreate(['name' => 'task_sale.view', 'guard_name' => 'web']);
        // isSalesOnly() requires exactly one role whose id == Role::SALE.
        DB::table('roles')->updateOrInsert(
            ['id' => AppRole::SALE],
            ['name' => 'Sale', 'guard_name' => 'web']
        );
        $role = SpatieRole::findById(AppRole::SALE, 'web');
        $role->givePermissionTo('task_sale.view');

        $user = $this->makeUser();
        $user->assignRole($role);

        return $user->fresh('roles');
    }

    private function marketingManager(): User
    {
        Permission::firstOrCreate(['name' => 'task_sale.view', 'guard_name' => 'web']);
        $role = SpatieRole::firstOrCreate(['name' => 'Marketing Manager', 'guard_name' => 'web']);
        $role->givePermissionTo('task_sale.view');

        $user = $this->makeUser();
        $user->assignRole($role);

        return $user->fresh('roles');
    }

    private function makeCustomer(): Customer
    {
        $customer = Customer::create([
            'name' => 'Sale Task Customer',
            'phone' => '0123456789',
            'sku' => 'CSALE' . uniqid(),
            'status' => Customer::STATUS_ACTIVE,
        ]);
        Branch::create([
            'object_type' => Customer::class,
            'object_id' => $customer->id,
            'location' => Branch::LOCATION_KL,
        ]);

        return $customer;
    }

    /** @return Milestone[] */
    private function makeSiteVisitMilestones(int $count = 2): array
    {
        $milestones = [];
        for ($i = 0; $i < $count; $i++) {
            $ms = Milestone::withoutGlobalScopes()->create([
                'type' => Milestone::TYPE_SITE_VISIT,
                'name' => 'Site Visit ' . uniqid(),
                'is_custom' => false,
            ]);
            Branch::create([
                'object_type' => Milestone::class,
                'object_id' => $ms->id,
                'location' => Branch::LOCATION_KL,
            ]);
            $milestones[] = $ms;
        }

        return $milestones;
    }

    private function basePayload(Customer $customer, array $milestones): array
    {
        return [
            'customer' => $customer->id,
            'name' => 'My Sale Task',
            'desc' => 'Visit the customer site',
            'start_date' => '2026-07-01',
            'due_date' => '2026-07-10',
            'milestone' => array_map(fn($m) => $m->id, $milestones),
        ];
    }

    public function test_sales_person_create_defaults_status_and_self_assigns(): void
    {
        Notification::fake();
        Session::put('as_branch', Branch::LOCATION_KL);

        $user = $this->salesPerson();
        $customer = $this->makeCustomer();
        $milestones = $this->makeSiteVisitMilestones();

        // No status, no assign submitted (fields hidden for a sales person).
        $payload = $this->basePayload($customer, $milestones);

        $response = $this->actingAs($user)->post(route('task.sale.store'), $payload);
        $response->assertRedirect(route('task.sale.index'));

        $task = Task::where('name', 'My Sale Task')->where('type', Task::TYPE_SALE)->latest('id')->first();
        $this->assertNotNull($task);
        $this->assertEquals(Task::STATUS_TO_DO, $task->status);
        // Auto-assigned to the creator only.
        $this->assertEquals([$user->id], $task->users()->pluck('user_id')->toArray());
    }

    public function test_marketing_manager_create_defaults_status_and_uses_provided_assign(): void
    {
        Notification::fake();
        Session::put('as_branch', Branch::LOCATION_KL);

        $manager = $this->marketingManager();
        $assignee = $this->makeUser();
        $customer = $this->makeCustomer();
        $milestones = $this->makeSiteVisitMilestones();

        // Status hidden, but the manager picks the assignee.
        $payload = $this->basePayload($customer, $milestones);
        $payload['assign'] = [$assignee->id];

        $response = $this->actingAs($manager)->post(route('task.sale.store'), $payload);
        $response->assertRedirect(route('task.sale.index'));

        $task = Task::where('name', 'My Sale Task')->where('type', Task::TYPE_SALE)->latest('id')->first();
        $this->assertNotNull($task);
        $this->assertEquals(Task::STATUS_TO_DO, $task->status);
        $this->assertEquals([$assignee->id], $task->users()->pluck('user_id')->toArray());
    }

    public function test_milestone_datetime_is_saved_on_create(): void
    {
        Notification::fake();
        Session::put('as_branch', Branch::LOCATION_KL);

        $manager = $this->marketingManager();
        $assignee = $this->makeUser();
        $customer = $this->makeCustomer();
        $milestones = $this->makeSiteVisitMilestones();

        $payload = $this->basePayload($customer, $milestones);
        $payload['assign'] = [$assignee->id];
        $payload['milestone_datetime'] = [
            $milestones[0]->id => '2026-07-05 14:30',
            // second milestone left blank
        ];

        $this->actingAs($manager)->post(route('task.sale.store'), $payload)
            ->assertRedirect(route('task.sale.index'));

        $task = Task::where('name', 'My Sale Task')->latest('id')->first();

        $this->assertEquals(
            '2026-07-05 14:30:00',
            DB::table('task_milestone')->where('task_id', $task->id)
                ->where('milestone_id', $milestones[0]->id)->value('datetime')
        );
        $this->assertNull(
            DB::table('task_milestone')->where('task_id', $task->id)
                ->where('milestone_id', $milestones[1]->id)->value('datetime')
        );
    }

    public function test_sales_person_create_with_only_custom_milestone(): void
    {
        Notification::fake();
        Session::put('as_branch', Branch::LOCATION_KL);

        $user = $this->salesPerson();
        $customer = $this->makeCustomer();

        // No predefined milestone[] selected, only a free-text custom milestone.
        $payload = [
            'customer' => $customer->id,
            'name' => 'Custom Only Task',
            'desc' => 'Visit site',
            'start_date' => '2026-07-01',
            'due_date' => '2026-07-10',
            'custom_milestone' => ['Bring brochures'],
        ];

        $response = $this->actingAs($user)->post(route('task.sale.store'), $payload);
        $response->assertRedirect(route('task.sale.index'));

        $task = Task::where('name', 'Custom Only Task')->latest('id')->first();
        $this->assertNotNull($task);
        // The custom milestone was created and linked.
        $this->assertEquals(1, $task->milestones()->count());
    }

    public function test_milestone_datetime_is_updated_on_edit(): void
    {
        Notification::fake();
        Session::put('as_branch', Branch::LOCATION_KL);

        $manager = $this->marketingManager();
        $assignee = $this->makeUser();
        $customer = $this->makeCustomer();
        $milestones = $this->makeSiteVisitMilestones();

        // Create first.
        $payload = $this->basePayload($customer, $milestones);
        $payload['assign'] = [$assignee->id];
        $payload['milestone_datetime'] = [$milestones[0]->id => '2026-07-05 14:30'];
        $this->actingAs($manager)->post(route('task.sale.store'), $payload);
        $task = Task::where('name', 'My Sale Task')->latest('id')->first();

        // Edit: change the datetime.
        $editPayload = $this->basePayload($customer, $milestones);
        $editPayload['assign'] = [$assignee->id];
        $editPayload['milestone_datetime'] = [$milestones[0]->id => '2026-08-01 09:00'];

        $this->actingAs($manager)->post(route('task.sale.update', ['task' => $task->id]), $editPayload)
            ->assertRedirect(route('task.sale.index'));

        $this->assertEquals(
            '2026-08-01 09:00:00',
            DB::table('task_milestone')->where('task_id', $task->id)
                ->where('milestone_id', $milestones[0]->id)->value('datetime')
        );
    }
}
