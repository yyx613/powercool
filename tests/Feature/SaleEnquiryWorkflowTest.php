<?php

namespace Tests\Feature;

use App\Models\Approval;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\SaleEnquiry;
use App\Models\User;
use App\Notifications\SaleEnquiryAcceptedNotification;
use App\Notifications\SaleEnquiryAssignedNotification;
use App\Notifications\SaleEnquiryNoDealNotification;
use App\Notifications\SaleEnquiryRejectedNotification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Session;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role as SpatieRole;
use Tests\TestCase;

/**
 * Covers the sale enquiry assignment + acceptance workflow:
 *  - assigning a salesperson notifies them
 *  - reassigning notifies the new salesperson and clears the prior acceptance
 *  - the assigned salesperson can accept, which records the acceptance, moves the
 *    enquiry into progress, and notifies the enquiry creator (management)
 *  - a non-assigned user cannot accept
 *  - the view page renders the full enquiry details
 */
class SaleEnquiryWorkflowTest extends TestCase
{
    use DatabaseTransactions;

    private function ensurePermissions(): void
    {
        foreach (['sale_enquiry.view', 'sale_enquiry.create', 'sale_enquiry.edit'] as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }
    }

    private function userWith(array $permissions, bool $withBranch = false): User
    {
        $this->ensurePermissions();
        $user = User::factory()->create();
        foreach ($permissions as $perm) {
            $user->givePermissionTo($perm);
        }

        if ($withBranch) {
            Branch::create([
                'object_type' => User::class,
                'object_id'   => $user->id,
                'location'    => Branch::LOCATION_KL,
            ]);
            $user = $user->fresh(['branch', 'roles']);
        }

        return $user;
    }

    private function validPayload(int $assignedUserId, array $overrides = []): array
    {
        return array_merge([
            'enquiry_date' => now()->format('Y-m-d\TH:i'),
            'enquiry_source' => SaleEnquiry::SOURCE_WEBSITE,
            'name' => 'Walk In Customer',
            'phone_number' => '0123456789',
            'email' => 'cust@example.com',
            'preferred_contact_method' => SaleEnquiry::CONTACT_WHATSAPP,
            'category' => SaleEnquiry::TYPE_PRODUCT_PRICING,
            'description' => 'Interested in a chiller',
            'product_service_interested' => 'Display Chiller',
            'assigned_user_id' => $assignedUserId,
            'priority' => SaleEnquiry::PRIORITY_MEDIUM,
            'status' => SaleEnquiry::STATUS_NEW,
            'quality' => SaleEnquiry::QUALITY_SEEN_AND_REPLY,
        ], $overrides);
    }

    public function test_creating_an_enquiry_notifies_the_assigned_salesperson(): void
    {
        Notification::fake();

        $manager = $this->userWith(['sale_enquiry.create'], withBranch: true);
        $salesperson = User::factory()->create();

        $this->actingAs($manager);
        Session::put('as_branch', Branch::LOCATION_KL);

        $response = $this->post(route('sale_enquiry.store'), $this->validPayload($salesperson->id));

        $response->assertRedirect(route('sale_enquiry.index'));
        $this->assertDatabaseHas('sale_enquiries', [
            'assigned_user_id' => $salesperson->id,
            'name' => 'Walk In Customer',
        ]);

        Notification::assertSentTo($salesperson, SaleEnquiryAssignedNotification::class);
    }

    public function test_reassigning_notifies_new_salesperson_and_clears_acceptance(): void
    {
        Notification::fake();

        $manager = $this->userWith(['sale_enquiry.edit']);
        $original = User::factory()->create();
        $replacement = User::factory()->create();

        $enquiry = SaleEnquiry::factory()->create([
            'assigned_user_id' => $original->id,
            'created_by' => $manager->id,
            'accepted_at' => now(),
            'accepted_by' => $original->id,
            'status' => SaleEnquiry::STATUS_IN_PROGRESS,
        ]);

        $this->actingAs($manager);

        $response = $this->post(
            route('sale_enquiry.update', ['enquiry' => $enquiry]),
            $this->validPayload($replacement->id, ['status' => SaleEnquiry::STATUS_IN_PROGRESS])
        );

        $response->assertRedirect(route('sale_enquiry.index'));

        $enquiry->refresh();
        $this->assertEquals($replacement->id, $enquiry->assigned_user_id);
        $this->assertNull($enquiry->accepted_at);
        $this->assertNull($enquiry->accepted_by);

        Notification::assertSentTo($replacement, SaleEnquiryAssignedNotification::class);
        Notification::assertNotSentTo($original, SaleEnquiryAssignedNotification::class);
    }

    public function test_updating_without_reassignment_does_not_notify(): void
    {
        Notification::fake();

        $manager = $this->userWith(['sale_enquiry.edit']);
        $salesperson = User::factory()->create();

        $enquiry = SaleEnquiry::factory()->create([
            'assigned_user_id' => $salesperson->id,
            'created_by' => $manager->id,
        ]);

        $this->actingAs($manager);

        $this->post(
            route('sale_enquiry.update', ['enquiry' => $enquiry]),
            $this->validPayload($salesperson->id, ['name' => 'Updated Name'])
        )->assertRedirect(route('sale_enquiry.index'));

        Notification::assertNothingSent();
    }

    public function test_assigned_salesperson_can_accept_and_creator_is_notified(): void
    {
        Notification::fake();

        $manager = User::factory()->create();
        $salesperson = $this->userWith(['sale_enquiry.view']);

        $enquiry = SaleEnquiry::factory()->create([
            'assigned_user_id' => $salesperson->id,
            'created_by' => $manager->id,
            'status' => SaleEnquiry::STATUS_NEW,
            'accepted_at' => null,
        ]);

        $this->actingAs($salesperson);

        $response = $this->post(route('sale_enquiry.accept', ['enquiry' => $enquiry]));

        $response->assertRedirect(route('sale_enquiry.view', ['enquiry' => $enquiry]));

        $enquiry->refresh();
        $this->assertNotNull($enquiry->accepted_at);
        $this->assertEquals($salesperson->id, $enquiry->accepted_by);
        $this->assertEquals(SaleEnquiry::STATUS_IN_PROGRESS, $enquiry->status);

        Notification::assertSentTo($manager, SaleEnquiryAcceptedNotification::class);
    }

    public function test_non_assigned_user_cannot_accept(): void
    {
        Notification::fake();

        $salesperson = User::factory()->create();
        $intruder = $this->userWith(['sale_enquiry.view']);

        $enquiry = SaleEnquiry::factory()->create([
            'assigned_user_id' => $salesperson->id,
            'created_by' => User::factory()->create()->id,
            'accepted_at' => null,
        ]);

        $this->actingAs($intruder);

        $this->post(route('sale_enquiry.accept', ['enquiry' => $enquiry]))->assertStatus(403);

        $enquiry->refresh();
        $this->assertNull($enquiry->accepted_at);
        Notification::assertNothingSent();
    }

    public function test_assigned_salesperson_can_reject_and_creator_is_notified(): void
    {
        Notification::fake();

        $manager = User::factory()->create();
        $salesperson = $this->userWith(['sale_enquiry.view']);

        $enquiry = SaleEnquiry::factory()->create([
            'assigned_user_id' => $salesperson->id,
            'created_by' => $manager->id,
            'status' => SaleEnquiry::STATUS_NEW,
            'accepted_at' => null,
            'rejected_at' => null,
        ]);

        $this->actingAs($salesperson);

        $response = $this->post(route('sale_enquiry.reject', ['enquiry' => $enquiry]), [
            'reason' => 'Out of my coverage area',
        ]);

        $response->assertRedirect(route('sale_enquiry.index'));

        $enquiry->refresh();
        $this->assertNotNull($enquiry->rejected_at);
        $this->assertEquals($salesperson->id, $enquiry->rejected_by);
        $this->assertEquals('Out of my coverage area', $enquiry->reject_reason);
        $this->assertNull($enquiry->accepted_at);

        Notification::assertSentTo($manager, SaleEnquiryRejectedNotification::class);
    }

    public function test_reject_requires_a_reason(): void
    {
        Notification::fake();

        $salesperson = $this->userWith(['sale_enquiry.view']);

        $enquiry = SaleEnquiry::factory()->create([
            'assigned_user_id' => $salesperson->id,
            'created_by' => User::factory()->create()->id,
            'status' => SaleEnquiry::STATUS_NEW,
            'accepted_at' => null,
            'rejected_at' => null,
        ]);

        $this->actingAs($salesperson);

        $this->post(route('sale_enquiry.reject', ['enquiry' => $enquiry]), [
            'reason' => '',
        ])->assertSessionHasErrors('reason');

        $enquiry->refresh();
        $this->assertNull($enquiry->rejected_at);
        $this->assertNull($enquiry->reject_reason);
        Notification::assertNothingSent();
    }

    public function test_non_assigned_user_cannot_reject(): void
    {
        Notification::fake();

        $salesperson = User::factory()->create();
        $intruder = $this->userWith(['sale_enquiry.view']);

        $enquiry = SaleEnquiry::factory()->create([
            'assigned_user_id' => $salesperson->id,
            'created_by' => User::factory()->create()->id,
            'rejected_at' => null,
        ]);

        $this->actingAs($intruder);

        $this->post(route('sale_enquiry.reject', ['enquiry' => $enquiry]))->assertStatus(403);

        $enquiry->refresh();
        $this->assertNull($enquiry->rejected_at);
        Notification::assertNothingSent();
    }

    public function test_assignee_must_act_before_viewing_a_pending_enquiry(): void
    {
        $salesperson = $this->userWith(['sale_enquiry.view']);

        $enquiry = SaleEnquiry::factory()->create([
            'assigned_user_id' => $salesperson->id,
            'created_by' => User::factory()->create()->id,
            'accepted_at' => null,
            'rejected_at' => null,
        ]);

        $this->actingAs($salesperson);

        $this->get(route('sale_enquiry.view', ['enquiry' => $enquiry]))
            ->assertRedirect(route('sale_enquiry.index'));
    }

    public function test_assignee_can_view_after_accepting(): void
    {
        $this->withoutVite();

        $salesperson = $this->userWith(['sale_enquiry.view']);

        $enquiry = SaleEnquiry::factory()->create([
            'assigned_user_id' => $salesperson->id,
            'created_by' => User::factory()->create()->id,
            'accepted_at' => now(),
            'accepted_by' => $salesperson->id,
        ]);

        $this->actingAs($salesperson);

        $this->get(route('sale_enquiry.view', ['enquiry' => $enquiry]))->assertOk();
    }

    public function test_assignee_can_still_view_after_rejecting(): void
    {
        $this->withoutVite();

        $salesperson = $this->userWith(['sale_enquiry.view']);

        $enquiry = SaleEnquiry::factory()->create([
            'assigned_user_id' => $salesperson->id,
            'created_by' => User::factory()->create()->id,
            'rejected_at' => now(),
            'rejected_by' => $salesperson->id,
        ]);

        $this->actingAs($salesperson);

        $this->get(route('sale_enquiry.view', ['enquiry' => $enquiry]))->assertOk();
    }

    public function test_non_assignee_can_view_a_pending_enquiry(): void
    {
        $this->withoutVite();

        $manager = $this->userWith(['sale_enquiry.view']);
        $salesperson = User::factory()->create();

        $enquiry = SaleEnquiry::factory()->create([
            'assigned_user_id' => $salesperson->id,
            'created_by' => $manager->id,
            'accepted_at' => null,
            'rejected_at' => null,
        ]);

        $this->actingAs($manager);

        $this->get(route('sale_enquiry.view', ['enquiry' => $enquiry]))->assertOk();
    }

    public function test_view_page_renders_enquiry_details(): void
    {
        $this->withoutVite();

        $viewer = $this->userWith(['sale_enquiry.view']);
        $salesperson = User::factory()->create(['name' => 'Jes Salesperson']);

        $enquiry = SaleEnquiry::factory()->create([
            'name' => 'Detail Customer',
            'phone_number' => '0199998888',
            'enquiry_source' => SaleEnquiry::SOURCE_WEBSITE,
            'assigned_user_id' => $salesperson->id,
            'created_by' => $viewer->id,
        ]);

        $this->actingAs($viewer);

        $response = $this->get(route('sale_enquiry.view', ['enquiry' => $enquiry]));

        $response->assertOk();
        $response->assertSee('Detail Customer');
        $response->assertSee('0199998888');
        $response->assertSee('Jes Salesperson');
        $response->assertSee('Website');
        $response->assertSee('Enquiry Details');
    }

    /**
     * Creates a user whose only role is Sale, which isSalesOnly() detects by Role::SALE id.
     */
    private function salesOnlyUser(): User
    {
        $this->ensurePermissions();
        $role = SpatieRole::firstOrCreate(
            ['id' => \App\Models\Role::SALE],
            ['name' => 'Sale', 'guard_name' => 'web']
        );
        $role->givePermissionTo('sale_enquiry.view');

        $user = User::factory()->create();
        $user->assignRole($role);

        return $user->fresh('roles');
    }

    public function test_related_sales_section_is_hidden_for_salespeople(): void
    {
        $this->withoutVite();

        $salesperson = $this->salesOnlyUser();

        $enquiry = SaleEnquiry::factory()->create([
            'assigned_user_id' => $salesperson->id,
            'created_by' => $salesperson->id,
            'accepted_at' => now(),
        ]);

        $this->actingAs($salesperson);

        $response = $this->get(route('sale_enquiry.view', ['enquiry' => $enquiry]));

        $response->assertOk();
        $response->assertSee('Enquiry Details');
        $response->assertDontSee('Related Sales');
    }

    public function test_related_sales_section_is_visible_for_management(): void
    {
        $this->withoutVite();

        $manager = $this->userWith(['sale_enquiry.view']);
        $salesperson = User::factory()->create();

        $enquiry = SaleEnquiry::factory()->create([
            'assigned_user_id' => $salesperson->id,
            'created_by' => $manager->id,
        ]);

        $this->actingAs($manager);

        $response = $this->get(route('sale_enquiry.view', ['enquiry' => $enquiry]));

        $response->assertOk();
        $response->assertSee('Related Sales');
        $response->assertSee('Sales Order Number');
        $response->assertSee('Amount (RM)');
    }

    public function test_view_data_is_blocked_for_salespeople(): void
    {
        $salesperson = $this->salesOnlyUser();

        $enquiry = SaleEnquiry::factory()->create([
            'assigned_user_id' => $salesperson->id,
            'created_by' => $salesperson->id,
        ]);

        $this->actingAs($salesperson);

        $response = $this->get(route('sale_enquiry.view_get_data', ['enquiry_id' => $enquiry->id]));

        $response->assertOk();
        $response->assertJson([
            'recordsTotal' => 0,
            'data' => [],
        ]);
    }

    // ---------------------------------------------------------------------
    // Salesperson status progress workflow
    // ---------------------------------------------------------------------

    public function test_assigned_salesperson_can_advance_status_to_in_progress(): void
    {
        $salesperson = $this->userWith(['sale_enquiry.view']);

        $enquiry = SaleEnquiry::factory()->create([
            'assigned_user_id' => $salesperson->id,
            'created_by' => User::factory()->create()->id,
            'status' => SaleEnquiry::STATUS_NEW,
            'accepted_at' => now(),
            'accepted_by' => $salesperson->id,
        ]);

        $this->actingAs($salesperson);

        $this->post(route('sale_enquiry.update_status', ['enquiry' => $enquiry]), [
            'status' => SaleEnquiry::STATUS_CLOSED_CONVERTED,
        ]);

        $enquiry->refresh();
        $this->assertEquals(SaleEnquiry::STATUS_CLOSED_CONVERTED, $enquiry->status);
    }

    public function test_non_assignee_cannot_update_status(): void
    {
        $salesperson = User::factory()->create();
        $intruder = $this->userWith(['sale_enquiry.view']);

        $enquiry = SaleEnquiry::factory()->create([
            'assigned_user_id' => $salesperson->id,
            'created_by' => User::factory()->create()->id,
            'status' => SaleEnquiry::STATUS_NEW,
            'accepted_at' => now(),
            'accepted_by' => $salesperson->id,
        ]);

        $this->actingAs($intruder);

        $this->post(route('sale_enquiry.update_status', ['enquiry' => $enquiry]), [
            'status' => SaleEnquiry::STATUS_IN_PROGRESS,
        ])->assertStatus(403);

        $enquiry->refresh();
        $this->assertEquals(SaleEnquiry::STATUS_NEW, $enquiry->status);
    }

    public function test_status_cannot_be_updated_before_accepting(): void
    {
        $salesperson = $this->userWith(['sale_enquiry.view']);

        $enquiry = SaleEnquiry::factory()->create([
            'assigned_user_id' => $salesperson->id,
            'created_by' => User::factory()->create()->id,
            'status' => SaleEnquiry::STATUS_NEW,
            'accepted_at' => null,
            'rejected_at' => null,
        ]);

        $this->actingAs($salesperson);

        $this->post(route('sale_enquiry.update_status', ['enquiry' => $enquiry]), [
            'status' => SaleEnquiry::STATUS_IN_PROGRESS,
        ]);

        $enquiry->refresh();
        $this->assertEquals(SaleEnquiry::STATUS_NEW, $enquiry->status);
    }

    public function test_no_deal_requires_a_reason(): void
    {
        $salesperson = $this->userWith(['sale_enquiry.view']);

        $enquiry = SaleEnquiry::factory()->create([
            'assigned_user_id' => $salesperson->id,
            'created_by' => User::factory()->create()->id,
            'status' => SaleEnquiry::STATUS_IN_PROGRESS,
            'accepted_at' => now(),
            'accepted_by' => $salesperson->id,
        ]);

        $this->actingAs($salesperson);

        $this->post(route('sale_enquiry.update_status', ['enquiry' => $enquiry]), [
            'status' => SaleEnquiry::STATUS_CLOSED_DROPPED,
            'reason' => '',
        ])->assertSessionHasErrors('reason');

        $this->assertDatabaseMissing('approvals', [
            'object_type' => SaleEnquiry::class,
            'object_id' => $enquiry->id,
        ]);
    }

    public function test_no_deal_creates_pending_approval_and_notifies_creator(): void
    {
        Notification::fake();

        $manager = User::factory()->create();
        $salesperson = $this->userWith(['sale_enquiry.view']);

        $enquiry = SaleEnquiry::factory()->create([
            'assigned_user_id' => $salesperson->id,
            'created_by' => $manager->id,
            'status' => SaleEnquiry::STATUS_IN_PROGRESS,
            'accepted_at' => now(),
            'accepted_by' => $salesperson->id,
        ]);

        $this->actingAs($salesperson);

        // as_branch lets Branch::assign() stamp the new Approval's branch.
        $this->withSession(['as_branch' => Branch::LOCATION_KL])
            ->post(route('sale_enquiry.update_status', ['enquiry' => $enquiry]), [
                'status' => SaleEnquiry::STATUS_CLOSED_DROPPED,
                'reason' => 'Customer bought elsewhere',
            ]);

        // Enquiry must NOT drop until management approves.
        $enquiry->refresh();
        $this->assertEquals(SaleEnquiry::STATUS_IN_PROGRESS, $enquiry->status);

        $approval = Approval::withoutGlobalScope(\App\Models\Scopes\BranchScope::class)
            ->where('object_type', SaleEnquiry::class)
            ->where('object_id', $enquiry->id)
            ->first();

        $this->assertNotNull($approval);
        $this->assertEquals(Approval::STATUS_PENDING_APPROVAL, $approval->status);
        $this->assertStringContainsString('is_no_deal', $approval->data);

        Notification::assertSentTo($manager, SaleEnquiryNoDealNotification::class);
    }

    public function test_manager_approving_no_deal_drops_the_enquiry(): void
    {
        Notification::fake();

        $salesperson = User::factory()->create();
        $manager = $this->userWith(['approval.view']);

        $enquiry = SaleEnquiry::factory()->create([
            'assigned_user_id' => $salesperson->id,
            'created_by' => $manager->id,
            'status' => SaleEnquiry::STATUS_IN_PROGRESS,
            'accepted_at' => now(),
            'accepted_by' => $salesperson->id,
        ]);

        $approval = Approval::create([
            'object_type' => SaleEnquiry::class,
            'object_id' => $enquiry->id,
            'status' => Approval::STATUS_PENDING_APPROVAL,
            'data' => json_encode([
                'is_no_deal' => true,
                'reason' => 'No budget',
                'description' => 'Marked as No Deal.',
            ]),
        ]);

        $this->actingAs($manager);

        $this->get(route('approval.approve', ['approval' => $approval]))->assertOk();

        $enquiry->refresh();
        $this->assertEquals(SaleEnquiry::STATUS_CLOSED_DROPPED, $enquiry->status);
        $this->assertEquals('No budget', $enquiry->no_deal_reason);

        Notification::assertSentTo($salesperson, SaleEnquiryNoDealNotification::class);
    }

    public function test_manager_rejecting_no_deal_keeps_enquiry_in_progress(): void
    {
        Notification::fake();

        $salesperson = User::factory()->create();
        $manager = $this->userWith(['approval.view']);

        $enquiry = SaleEnquiry::factory()->create([
            'assigned_user_id' => $salesperson->id,
            'created_by' => $manager->id,
            'status' => SaleEnquiry::STATUS_IN_PROGRESS,
            'accepted_at' => now(),
            'accepted_by' => $salesperson->id,
        ]);

        $approval = Approval::create([
            'object_type' => SaleEnquiry::class,
            'object_id' => $enquiry->id,
            'status' => Approval::STATUS_PENDING_APPROVAL,
            'data' => json_encode([
                'is_no_deal' => true,
                'reason' => 'No budget',
                'description' => 'Marked as No Deal.',
            ]),
        ]);

        $this->actingAs($manager);

        $this->post(route('approval.reject', ['approval' => $approval]), [
            'remark' => 'Keep following up with the customer',
        ])->assertOk();

        $enquiry->refresh();
        $this->assertEquals(SaleEnquiry::STATUS_IN_PROGRESS, $enquiry->status);
        $this->assertNull($enquiry->no_deal_reason);

        $approval->refresh();
        $this->assertEquals(Approval::STATUS_REJECTED, $approval->status);
        $this->assertEquals('Keep following up with the customer', $approval->reject_remark);

        Notification::assertSentTo($salesperson, SaleEnquiryNoDealNotification::class);
    }

    public function test_progress_is_derived_from_linked_records(): void
    {
        $salesperson = User::factory()->create();

        $enquiry = SaleEnquiry::factory()->create([
            'assigned_user_id' => $salesperson->id,
            'created_by' => User::factory()->create()->id,
            'status' => SaleEnquiry::STATUS_IN_PROGRESS,
            'accepted_at' => now(),
        ]);

        $customer = Customer::create([
            'sku' => '300-T001',
            'company_name' => 'Test Debtor',
        ]);

        $quotation = Sale::create([
            'sku' => 'QUO-T1',
            'type' => Sale::TYPE_QUO,
            'sale_enquiry_id' => $enquiry->id,
            'customer_id' => $customer->id,
            'status' => Sale::STATUS_ACTIVE,
        ]);

        Sale::create([
            'sku' => 'SO-T1',
            'type' => Sale::TYPE_SO,
            'sale_enquiry_id' => $enquiry->id,
            'customer_id' => $customer->id,
            'status' => Sale::STATUS_ACTIVE,
        ]);

        $progress = collect($enquiry->progress())->keyBy('key');

        // Milestones are derived live from the linked Customer / Quotation / SO.
        // (The DO/INV milestones reuse the same earliest-record derivation and
        // are exercised through manual verification due to their FK-heavy setup.)
        $this->assertEquals('300-T001', $progress['debtor']['ref']);
        $this->assertTrue($progress['debtor']['done']);
        $this->assertEquals('QUO-T1', $progress['quotation']['ref']);
        $this->assertTrue($progress['quotation']['done']);
        $this->assertEquals('SO-T1', $progress['sale_order']['ref']);
        $this->assertTrue($progress['sale_order']['done']);
        $this->assertFalse($progress['delivery_order']['done']);
        $this->assertTrue($progress['in_progress']['done']);
    }
}
