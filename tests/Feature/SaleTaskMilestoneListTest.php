<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Milestone;
use App\Models\Role as AppRole;
use App\Models\Task;
use App\Models\TaskMilestone;
use App\Models\User;
use App\Models\UserTask;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role as SpatieRole;
use Tests\TestCase;

/**
 * Covers the Site Visit (sale task) default milestone list.
 *
 * The sale task flow was updated to:
 *   Check In -> Purpose/Business Nature -> Photo (Customer, Shop)
 *   -> Payment Collection -> Check Out -> Result (Potential/No Potential)
 *
 * The list is ordered by the milestones.sort column so the sequence is
 * deterministic regardless of insertion id.
 */
class SaleTaskMilestoneListTest extends TestCase
{
    use DatabaseTransactions;

    /** The default (non-custom) site visit milestones in display order. */
    private function siteVisitDefaults(): array
    {
        return Milestone::withoutGlobalScopes()
            ->where('type', Milestone::TYPE_SITE_VISIT)
            ->where('is_custom', false)
            ->orderBy('sort')
            ->orderBy('id')
            ->pluck('name')
            ->toArray();
    }

    public function test_site_visit_milestones_match_new_sale_flow_in_order(): void
    {
        $this->assertSame([
            'Check In',
            'Purpose / Business Nature',
            'Photo (Customer, Shop)',
            'Payment Collection',
            'Check Out',
            'Result (Potential / No Potential)',
        ], $this->siteVisitDefaults());
    }

    public function test_obsolete_site_visit_steps_are_gone(): void
    {
        $names = $this->siteVisitDefaults();
        $this->assertNotContains('Measurement Remark (Attach Photo)', $names);
        $this->assertNotContains('Survey Feedback', $names);
    }

    public function test_payment_collection_milestone_is_recognised(): void
    {
        // getPaymentCollectionIds() keys off the exact 'Payment Collection' name.
        \Illuminate\Support\Facades\Cache::forget('payment_collection_ids');

        $paymentId = Milestone::withoutGlobalScopes()
            ->where('type', Milestone::TYPE_SITE_VISIT)
            ->where('name', 'Payment Collection')
            ->value('id');

        $this->assertNotNull($paymentId);
        $this->assertContains($paymentId, getPaymentCollectionIds());
    }

    /**
     * Default milestones have no branch of their own, so a branch-scoped user
     * must still see the whole checklist on the sale create form.
     */
    public function test_branch_scoped_user_sees_default_milestone_checklist(): void
    {
        Session::put('as_branch', Branch::LOCATION_KL);

        // A sales person whose account is tied to a branch (this is the case
        // that previously hid the branch-less default milestones).
        Permission::firstOrCreate(['name' => 'task_sale.create', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'task_sale.view', 'guard_name' => 'web']);
        DB::table('roles')->updateOrInsert(
            ['id' => AppRole::SALE],
            ['name' => 'Sale', 'guard_name' => 'web']
        );
        $role = SpatieRole::findById(AppRole::SALE, 'web');
        $role->givePermissionTo(['task_sale.create', 'task_sale.view']);

        $user = User::create([
            'name' => 'Branch Sale',
            'email' => 'branch_sale_' . uniqid() . '@test.com',
            'password' => Hash::make('password'),
            'sku' => 'UBSALE' . uniqid(),
        ]);
        $user->assignRole($role);
        Branch::create([
            'object_type' => User::class,
            'object_id' => $user->id,
            'location' => Branch::LOCATION_KL,
        ]);

        $response = $this->actingAs($user->fresh('roles'))->get(route('task.sale.create'));

        $response->assertOk();
        $response->assertSee('Check In');
        $response->assertSee('Purpose / Business Nature', false);
        $response->assertSee('Photo (Customer, Shop)', false);
        $response->assertSee('Payment Collection');
        $response->assertSee('Check Out');
        $response->assertSee('Result (Potential / No Potential)', false);
    }

    /**
     * The attached default milestones must load through the task relation for a
     * branch-scoped user — this drives the edit-form auto-select (pre-checked
     * boxes) and the mobile app's milestone list.
     */
    public function test_task_default_milestones_load_for_branch_scoped_user(): void
    {
        Session::put('as_branch', Branch::LOCATION_KL);

        DB::table('roles')->updateOrInsert(
            ['id' => AppRole::SALE],
            ['name' => 'Sale', 'guard_name' => 'web']
        );
        $user = User::create([
            'name' => 'Branch Sale Rel',
            'email' => 'branch_sale_rel_' . uniqid() . '@test.com',
            'password' => Hash::make('password'),
            'sku' => 'UBSALEREL' . uniqid(),
        ]);
        $user->assignRole(SpatieRole::findById(AppRole::SALE, 'web'));
        Branch::create([
            'object_type' => User::class,
            'object_id' => $user->id,
            'location' => Branch::LOCATION_KL,
        ]);

        // The six seeded default site-visit milestones (already branch-assigned).
        $milestoneIds = Milestone::withoutGlobalScopes()
            ->where('type', Milestone::TYPE_SITE_VISIT)
            ->where('is_custom', false)
            ->orderBy('sort')->pluck('id');

        $customer = \App\Models\Customer::create([
            'name' => 'Rel Customer',
            'phone' => '0123456789',
            'sku' => 'CREL' . uniqid(),
            'status' => \App\Models\Customer::STATUS_ACTIVE,
        ]);
        Branch::create([
            'object_type' => \App\Models\Customer::class,
            'object_id' => $customer->id,
            'location' => Branch::LOCATION_KL,
        ]);

        $task = Task::create([
            'sku' => (new Task)->generateSku(),
            'type' => Task::TYPE_SALE,
            'customer_id' => $customer->id,
            'name' => 'Rel Sale Task',
            'status' => Task::STATUS_TO_DO,
            'amount_to_collect' => 0,
        ]);
        Branch::create([
            'object_type' => Task::class,
            'object_id' => $task->id,
            'location' => Branch::LOCATION_KL,
        ]);
        UserTask::create(['user_id' => $user->id, 'task_id' => $task->id]);
        foreach ($milestoneIds as $id) {
            TaskMilestone::create(['task_id' => $task->id, 'milestone_id' => $id]);
        }

        // Load the relation as the branch-scoped user (BranchScope is active).
        $this->actingAs($user->fresh('roles'));
        $loaded = $task->fresh()->milestones;

        $this->assertCount(6, $loaded, 'All default milestones should be visible to a branch user');
        $this->assertSame([
            'Check In',
            'Purpose / Business Nature',
            'Photo (Customer, Shop)',
            'Payment Collection',
            'Check Out',
            'Result (Potential / No Potential)',
        ], $loaded->pluck('name')->toArray());
    }

    /**
     * The web task view must render each submitted milestone's captured details
     * (date/time, location, remark) and its photo(s).
     */
    public function test_task_view_shows_milestone_photo_and_details(): void
    {
        Session::put('as_branch', Branch::LOCATION_KL);

        Permission::firstOrCreate(['name' => 'task_sale.view', 'guard_name' => 'web']);
        DB::table('roles')->updateOrInsert(
            ['id' => AppRole::SALE],
            ['name' => 'Sale', 'guard_name' => 'web']
        );
        $role = SpatieRole::findById(AppRole::SALE, 'web');
        $role->givePermissionTo('task_sale.view');

        $user = User::create([
            'name' => 'View Sale ' . uniqid(),
            'email' => 'view_sale_' . uniqid() . '@test.com',
            'password' => Hash::make('password'),
            'sku' => 'UVSALE' . uniqid(),
        ]);
        $user->assignRole($role);
        Branch::create(['object_type' => User::class, 'object_id' => $user->id, 'location' => Branch::LOCATION_KL]);

        $customer = \App\Models\Customer::create([
            'name' => 'View Customer', 'phone' => '0123456789',
            'sku' => 'CVIEW' . uniqid(), 'status' => \App\Models\Customer::STATUS_ACTIVE,
        ]);
        Branch::create(['object_type' => \App\Models\Customer::class, 'object_id' => $customer->id, 'location' => Branch::LOCATION_KL]);

        $task = Task::create([
            'sku' => (new Task)->generateSku(), 'type' => Task::TYPE_SALE,
            'customer_id' => $customer->id, 'name' => 'View Sale Task',
            'status' => Task::STATUS_DOING, 'amount_to_collect' => 0,
        ]);
        Branch::create(['object_type' => Task::class, 'object_id' => $task->id, 'location' => Branch::LOCATION_KL]);

        $photoMs = Milestone::withoutGlobalScopes()
            ->where('type', Milestone::TYPE_SITE_VISIT)->where('name', 'Photo (Customer, Shop)')->first();

        TaskMilestone::create([
            'task_id' => $task->id,
            'milestone_id' => $photoMs->id,
            'address' => 'No. 12, Jalan Demo (3.139, 101.686)',
            'datetime' => '2026-07-01 10:30:00',
            'remark' => 'Shopfront captured',
            'submitted_at' => now(),
        ]);
        // Pivot models don't return the auto-increment id on create; re-fetch it.
        $pivotId = TaskMilestone::where('task_id', $task->id)
            ->where('milestone_id', $photoMs->id)->value('id');
        \App\Models\Attachment::create([
            'object_type' => TaskMilestone::class,
            'object_id' => $pivotId,
            'src' => 'demo_milestone_photo.jpg',
        ]);

        $response = $this->actingAs($user->fresh('roles'))->get(route('task.sale.view', $task));

        $response->assertOk();
        $response->assertSee('No. 12, Jalan Demo (3.139, 101.686)');
        $response->assertSee('Shopfront captured');
        $response->assertSee('task_milestone/demo_milestone_photo.jpg', false);
    }
}
