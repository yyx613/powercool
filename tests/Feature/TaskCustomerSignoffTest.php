<?php

namespace Tests\Feature;

use App\Models\Attachment;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Milestone;
use App\Models\Task;
use App\Models\TaskMilestone;
use App\Models\User;
use App\Models\UserTask;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TaskCustomerSignoffTest extends TestCase
{
    use DatabaseTransactions;

    private function getOrCreateUser(): User
    {
        $user = User::first();
        if ($user) return $user;

        return User::create([
            'name' => 'Test Admin',
            'email' => 'test_signoff@test.com',
            'password' => Hash::make('password'),
            'sku' => 'UTEST' . uniqid(),
        ]);
    }

    private function getOrCreateCustomer(): Customer
    {
        $customer = Customer::first();
        if ($customer) return $customer;

        return Customer::create([
            'name' => 'Test Customer',
            'phone' => '0123456789',
            'sku' => 'CTEST' . uniqid(),
        ]);
    }

    private function ensureMilestones(int $milestoneType): void
    {
        $existing = Milestone::withoutGlobalScopes()->where('type', $milestoneType)->count();
        if ($existing >= 2) return;

        for ($i = $existing; $i < 2; $i++) {
            $ms = Milestone::withoutGlobalScopes()->create([
                'type' => $milestoneType,
                'name' => "Test Milestone $milestoneType-$i",
                'is_custom' => false,
            ]);
            Branch::create([
                'object_type' => Milestone::class,
                'object_id' => $ms->id,
                'location' => Branch::LOCATION_KL,
            ]);
        }
    }

    private function createTaskWithMilestones(User $user, int $taskType, int $milestoneType, bool $submitAll = false): array
    {
        $customer = $this->getOrCreateCustomer();

        $task = Task::create([
            'sku' => 'T' . now()->format('ym') . 'TEST' . uniqid(),
            'type' => $taskType,
            'task_type' => $milestoneType,
            'customer_id' => $customer->id,
            'name' => 'Test Task',
            'desc' => 'Test task for sign-off',
            'start_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'status' => Task::STATUS_DOING,
            'amount_to_collect' => 100,
        ]);
        Branch::create([
            'object_type' => Task::class,
            'object_id' => $task->id,
            'location' => Branch::LOCATION_KL,
        ]);

        UserTask::create([
            'user_id' => $user->id,
            'task_id' => $task->id,
        ]);

        // Ensure milestones exist for the given type
        $this->ensureMilestones($milestoneType);

        // Get milestones of the given type and ensure they have branch records
        $milestones = Milestone::withoutGlobalScopes()->where('type', $milestoneType)->take(2)->get();

        $taskMsIds = [];
        foreach ($milestones as $ms) {
            // Ensure milestone has a branch record for scope resolution
            if (!Branch::where('object_type', Milestone::class)->where('object_id', $ms->id)->exists()) {
                Branch::create([
                    'object_type' => Milestone::class,
                    'object_id' => $ms->id,
                    'location' => Branch::LOCATION_KL,
                ]);
            }
            $id = DB::table('task_milestone')->insertGetId([
                'task_id' => $task->id,
                'milestone_id' => $ms->id,
                'submitted_at' => $submitAll ? now() : null,
                'address' => $submitAll ? 'Test Address' : null,
                'datetime' => $submitAll ? now() : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $taskMsIds[] = $id;
        }

        if ($submitAll) {
            $task->update(['status' => Task::STATUS_IN_REVIEW]);
        }

        return ['task' => $task, 'task_ms_ids' => $taskMsIds];
    }

    // ==========================================
    // Milestone completion status tests
    // ==========================================

    public function test_technician_task_becomes_in_review_when_all_milestones_completed(): void
    {
        $user = $this->getOrCreateUser();
        Session::put('as_branch', Branch::LOCATION_KL);

        $data = $this->createTaskWithMilestones($user, Task::TYPE_TECHNICIAN, Milestone::TYPE_SERVICE_TASK);
        $task = $data['task'];
        $taskMsIds = $data['task_ms_ids'];

        // Submit first milestone directly
        DB::table('task_milestone')->where('id', $taskMsIds[0])->update([
            'submitted_at' => now(),
            'address' => 'Addr',
            'datetime' => now(),
        ]);

        // Submit last milestone via API
        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/task/update-milestone/{$taskMsIds[1]}", [
                'address' => 'Test Address',
                'datetime' => now()->format('d M Y, h:i a'),
                'amount_collected' => 0,
                'remark' => 'Done',
            ]);

        $response->assertStatus(200);

        $task->refresh();
        $this->assertEquals(Task::STATUS_IN_REVIEW, $task->status);
    }

    public function test_driver_task_still_becomes_completed_when_all_milestones_completed(): void
    {
        $user = $this->getOrCreateUser();
        Session::put('as_branch', Branch::LOCATION_KL);

        $data = $this->createTaskWithMilestones($user, Task::TYPE_DRIVER, Milestone::TYPE_DRIVER_TASK);
        $task = $data['task'];
        $taskMsIds = $data['task_ms_ids'];

        // Submit first milestone directly (if more than one)
        if (count($taskMsIds) > 1) {
            DB::table('task_milestone')->where('id', $taskMsIds[0])->update([
                'submitted_at' => now(),
                'address' => 'Addr',
                'datetime' => now(),
            ]);
        }

        // Submit last milestone via API
        $lastId = end($taskMsIds);
        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/task/update-milestone/{$lastId}", [
                'address' => 'Test Address',
                'datetime' => now()->format('d M Y, h:i a'),
                'amount_collected' => 0,
                'remark' => 'Done',
            ]);

        $response->assertStatus(200);

        $task->refresh();
        $this->assertEquals(Task::STATUS_COMPLETED, $task->status);
    }

    // ==========================================
    // Customer sign-off endpoint tests
    // ==========================================

    public function test_customer_signoff_succeeds_with_valid_data(): void
    {
        Storage::fake('local');

        $user = $this->getOrCreateUser();
        Session::put('as_branch', Branch::LOCATION_KL);

        $data = $this->createTaskWithMilestones($user, Task::TYPE_TECHNICIAN, Milestone::TYPE_SERVICE_TASK, submitAll: true);
        $task = $data['task'];

        $signature = UploadedFile::fake()->image('signature.png', 400, 200);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/task/customer-signoff/{$task->id}", [
                'signature' => $signature,
                'signed_off_by' => 'John Customer',
                'photos_approved' => true,
            ]);

        $response->assertStatus(200);

        $task->refresh();
        $this->assertEquals(Task::STATUS_COMPLETED, $task->status);
        $this->assertNotNull($task->customer_signature);
        $this->assertNotNull($task->photos_approved_at);
        $this->assertNotNull($task->signed_off_at);
        $this->assertEquals('John Customer', $task->signed_off_by);
    }

    public function test_customer_signoff_fails_when_milestones_not_complete(): void
    {
        $user = $this->getOrCreateUser();
        Session::put('as_branch', Branch::LOCATION_KL);

        $data = $this->createTaskWithMilestones($user, Task::TYPE_TECHNICIAN, Milestone::TYPE_SERVICE_TASK, submitAll: false);
        $task = $data['task'];

        $signature = UploadedFile::fake()->image('signature.png', 400, 200);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/task/customer-signoff/{$task->id}", [
                'signature' => $signature,
                'signed_off_by' => 'John Customer',
                'photos_approved' => true,
            ]);

        $response->assertStatus(400);
    }

    public function test_customer_signoff_fails_when_task_not_in_review(): void
    {
        $user = $this->getOrCreateUser();
        Session::put('as_branch', Branch::LOCATION_KL);

        $data = $this->createTaskWithMilestones($user, Task::TYPE_TECHNICIAN, Milestone::TYPE_SERVICE_TASK, submitAll: true);
        $task = $data['task'];
        $task->update(['status' => Task::STATUS_DOING]);

        $signature = UploadedFile::fake()->image('signature.png', 400, 200);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/task/customer-signoff/{$task->id}", [
                'signature' => $signature,
                'signed_off_by' => 'John Customer',
                'photos_approved' => true,
            ]);

        $response->assertStatus(400);
    }

    public function test_customer_signoff_fails_without_signature(): void
    {
        $user = $this->getOrCreateUser();
        Session::put('as_branch', Branch::LOCATION_KL);

        $data = $this->createTaskWithMilestones($user, Task::TYPE_TECHNICIAN, Milestone::TYPE_SERVICE_TASK, submitAll: true);
        $task = $data['task'];

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/task/customer-signoff/{$task->id}", [
                'signed_off_by' => 'John Customer',
                'photos_approved' => true,
            ]);

        $response->assertStatus(400);
    }

    public function test_customer_signoff_fails_without_signed_off_by(): void
    {
        $user = $this->getOrCreateUser();
        Session::put('as_branch', Branch::LOCATION_KL);

        $data = $this->createTaskWithMilestones($user, Task::TYPE_TECHNICIAN, Milestone::TYPE_SERVICE_TASK, submitAll: true);
        $task = $data['task'];

        $signature = UploadedFile::fake()->image('signature.png', 400, 200);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/task/customer-signoff/{$task->id}", [
                'signature' => $signature,
                'photos_approved' => true,
            ]);

        $response->assertStatus(400);
    }

    // ==========================================
    // Get milestone photos endpoint tests
    // ==========================================

    public function test_get_milestone_photos_returns_grouped_attachments(): void
    {
        $user = $this->getOrCreateUser();
        Session::put('as_branch', Branch::LOCATION_KL);

        $data = $this->createTaskWithMilestones($user, Task::TYPE_TECHNICIAN, Milestone::TYPE_SERVICE_TASK, submitAll: true);
        $task = $data['task'];
        $taskMsId = $data['task_ms_ids'][0];

        // Create an attachment for the first milestone
        Attachment::create([
            'object_type' => TaskMilestone::class,
            'object_id' => $taskMsId,
            'src' => 'test_photo.jpg',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/task/get-milestone-photos/{$task->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'photos' => [
                '*' => ['milestone_name', 'attachments'],
            ],
        ]);
    }

    // ==========================================
    // getDetail includes sign-off fields
    // ==========================================

    public function test_get_detail_includes_signoff_fields(): void
    {
        $user = $this->getOrCreateUser();
        Session::put('as_branch', Branch::LOCATION_KL);

        $data = $this->createTaskWithMilestones($user, Task::TYPE_TECHNICIAN, Milestone::TYPE_SERVICE_TASK, submitAll: true);
        $task = $data['task'];
        $task->update([
            'customer_signature' => 'test_sig.png',
            'photos_approved_at' => now(),
            'signed_off_at' => now(),
            'signed_off_by' => 'Test Customer',
            'status' => Task::STATUS_COMPLETED,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/task/get-detail/{$task->id}");

        $response->assertStatus(200);
        $responseData = $response->json('task');
        $this->assertNotNull($responseData['signature_url']);
        $this->assertNotNull($responseData['photos_approved_at']);
        $this->assertNotNull($responseData['signed_off_at']);
        $this->assertEquals('Test Customer', $responseData['signed_off_by']);
    }
}
