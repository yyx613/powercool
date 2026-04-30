<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Milestone;
use App\Models\Production;
use App\Models\ProductionMilestone;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Session;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ProductionQuickDuplicateTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Session::put('as_branch', Branch::LOCATION_KL);
    }

    private function aProduction(): Production
    {
        $p = Production::first();
        $this->assertNotNull($p, 'Expected at least one Production in the test DB');

        return $p;
    }

    public function test_qty_default_one_keeps_existing_behaviour(): void
    {
        $this->actingAs(User::first());
        $source = $this->aProduction();
        $before = Production::count();

        $response = $this->get(route('production.quick_duplicate', $source->id));

        $response->assertRedirect(route('production.index'));
        $this->assertSame($before + 1, Production::count(), 'Without qty, should create exactly one copy');
    }

    public function test_qty_three_creates_three_copies_in_one_transaction(): void
    {
        $this->actingAs(User::first());
        $source = $this->aProduction();
        $before = Production::count();

        $response = $this->get(route('production.quick_duplicate', $source->id).'?qty=3');

        $response->assertRedirect(route('production.index'));
        $this->assertSame($before + 3, Production::count(), 'qty=3 should create three copies');
    }

    public function test_qty_zero_is_rejected(): void
    {
        $this->actingAs(User::first());
        $source = $this->aProduction();
        $before = Production::count();

        $response = $this->get(route('production.quick_duplicate', $source->id).'?qty=0');

        $response->assertStatus(302); // redirect back with validation error
        $this->assertSame($before, Production::count(), 'qty=0 should not create any copies');
    }

    public function test_qty_above_max_is_rejected(): void
    {
        $this->actingAs(User::first());
        $source = $this->aProduction();
        $before = Production::count();

        $response = $this->get(route('production.quick_duplicate', $source->id).'?qty=21');

        $response->assertStatus(302);
        $this->assertSame($before, Production::count(), 'qty=21 should not create any copies');
    }

    public function test_requires_production_create_permission(): void
    {
        Permission::firstOrCreate(['name' => 'production.create', 'guard_name' => 'web']);
        $source = $this->aProduction();

        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('production.quick_duplicate', $source->id));

        $response->assertStatus(403);
    }

    public function test_each_copy_gets_a_unique_sku(): void
    {
        $this->actingAs(User::first());
        $source = $this->aProduction();
        $beforeMaxId = (int) Production::max('id');

        $response = $this->get(route('production.quick_duplicate', $source->id).'?qty=4');
        $response->assertRedirect(route('production.index'));

        $newSkus = Production::where('id', '>', $beforeMaxId)->pluck('sku')->toArray();
        $this->assertCount(4, $newSkus);
        $this->assertCount(4, array_unique($newSkus), 'All four duplicated copies must have unique SKUs');
    }

    public function test_duplicate_has_status_to_do(): void
    {
        $this->actingAs(User::first());
        $source = $this->aProduction();

        // Force source to a non-TO_DO status so the assertion is meaningful
        $source->update(['status' => Production::STATUS_DOING]);

        $beforeMaxId = (int) Production::max('id');
        $response = $this->get(route('production.quick_duplicate', $source->id));
        $response->assertRedirect(route('production.index'));

        $newProduction = Production::where('id', '>', $beforeMaxId)->first();
        $this->assertNotNull($newProduction, 'Quick duplicate should have created a new production');
        $this->assertSame(
            Production::STATUS_TO_DO,
            $newProduction->status,
            'Duplicated production must start at STATUS_TO_DO regardless of source status'
        );
    }

    public function test_duplicate_milestones_are_unchecked(): void
    {
        $user = User::first();
        $this->actingAs($user);
        $source = $this->aProduction();

        $milestone = Milestone::first();
        $this->assertNotNull($milestone, 'Expected at least one Milestone in the test DB');

        // Ensure source has a milestone whose check-in state is populated,
        // so the assertion catches any leak of submitted_at / submitted_by.
        ProductionMilestone::updateOrCreate(
            ['production_id' => $source->id, 'milestone_id' => $milestone->id],
            ['sequence' => 1, 'submitted_at' => now(), 'submitted_by' => $user->id]
        );

        $beforeMaxId = (int) Production::max('id');
        $response = $this->get(route('production.quick_duplicate', $source->id));
        $response->assertRedirect(route('production.index'));

        $newProduction = Production::where('id', '>', $beforeMaxId)->first();
        $this->assertNotNull($newProduction);

        $newMilestones = ProductionMilestone::where('production_id', $newProduction->id)->get();
        $this->assertGreaterThan(0, $newMilestones->count(), 'Duplicate should have at least one milestone');
        foreach ($newMilestones as $pm) {
            $this->assertNull($pm->submitted_at, 'Duplicated milestone must not be checked in');
            $this->assertNull($pm->submitted_by, 'Duplicated milestone must not have a submitter');
        }
    }

    public function test_duplicate_can_be_started_manually(): void
    {
        $this->actingAs(User::first());
        $source = $this->aProduction();

        $beforeMaxId = (int) Production::max('id');
        $response = $this->get(route('production.quick_duplicate', $source->id));
        $response->assertRedirect(route('production.index'));

        $newProduction = Production::where('id', '>', $beforeMaxId)->first();
        $this->assertNotNull($newProduction);
        $this->assertSame(Production::STATUS_TO_DO, $newProduction->status);

        $start = $this->get(route('production.to_in_progress').'?productionIds='.$newProduction->id);
        $start->assertStatus(302);

        $newProduction->refresh();
        $this->assertSame(
            Production::STATUS_DOING,
            $newProduction->status,
            'Duplicate should transition to STATUS_DOING after Start Task'
        );
        $this->assertNotNull(
            $newProduction->product_child_id,
            'Start Task should attach a product_child to the duplicate'
        );
    }

    public function test_duplicate_progress_is_zero_percent(): void
    {
        $this->actingAs(User::first());
        $source = $this->aProduction();

        $beforeMaxId = (int) Production::max('id');
        $response = $this->get(route('production.quick_duplicate', $source->id));
        $response->assertRedirect(route('production.index'));

        $newProduction = Production::where('id', '>', $beforeMaxId)->first();
        $this->assertNotNull($newProduction);

        // Progress is 0 whether the duplicate copied milestones (none submitted)
        // or has zero milestones at all.
        $this->assertSame(
            0,
            (int) $newProduction->getProgress($newProduction),
            'Freshly duplicated production must show 0% progress'
        );
    }

    public function test_progress_is_zero_when_production_has_no_milestones(): void
    {
        // Use an in-memory Production with an id that has no milestone rows.
        // Avoids touching real records (per CLAUDE.md "don't delete records").
        $unsavedId = ((int) Production::max('id')) + 100000;
        $p = new Production();
        $p->id = $unsavedId;

        $this->assertSame(
            0,
            (int) $p->getProgress($p),
            'Production with zero milestones must report 0% progress, not 100%'
        );
    }

    public function test_edit_form_shows_update_button_for_to_do_production(): void
    {
        Permission::firstOrCreate(['name' => 'production.edit', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'production.view', 'guard_name' => 'web']);

        $this->actingAs(User::first());
        $source = $this->aProduction();

        // Quick-duplicate to get a TO_DO Normal-type production
        $beforeMaxId = (int) Production::max('id');
        $this->get(route('production.quick_duplicate', $source->id))
            ->assertRedirect(route('production.index'));

        $duplicate = Production::where('id', '>', $beforeMaxId)->first();
        $this->assertNotNull($duplicate);
        $this->assertSame(Production::STATUS_TO_DO, $duplicate->status);

        $response = $this->get(route('production.edit', $duplicate->id));
        $response->assertStatus(200);
        $response->assertSee('id="submit-btn"', false);
    }
}
