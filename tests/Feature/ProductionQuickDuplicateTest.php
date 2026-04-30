<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Production;
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
}
