<?php

namespace Tests\Feature;

use App\Models\InventoryCategory;
use App\Models\Milestone;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * Milestone listing "Inventory Category" (idx 0) is a FIND_IN_SET list of category names.
 * The sort key must equal the displayed string: InventoryCategory::whereIn(ids)->pluck('name')
 * joined by ', ' — active categories in id order.
 */
class ListingSortMilestoneCategoryTest extends TestCase
{
    use DatabaseTransactions;

    private function user(): User
    {
        Permission::firstOrCreate(['name' => 'setting.milestone.view', 'guard_name' => 'web']);
        $u = User::factory()->create();
        $u->givePermissionTo('setting.milestone.view');

        return $u->fresh();
    }

    public function test_inventory_category_sorts_matching_displayed_string(): void
    {
        $this->actingAs($this->user());

        $kw = 'MCAT'.uniqid();
        $catA = InventoryCategory::create(['name' => 'AAA '.$kw, 'company_group' => 1, 'is_active' => 1]); // smaller id
        $catZ = InventoryCategory::create(['name' => 'ZZZ '.$kw, 'company_group' => 1, 'is_active' => 1]); // larger id

        // The list groups by `batch` (one row per batch), so each row needs a distinct batch.
        $mk = fn (string $cats, string $batch) => Milestone::create([
            'type'                 => Milestone::TYPE_PRODUCTION,
            'name'                 => 'M '.$kw,
            'is_custom'            => 0,
            'batch'                => $batch,
            'inventory_category_id' => $cats,
        ]);

        // batch is an integer column; use 3 distinct values so each is its own group/row.
        $mk((string) $catA->id, (string) $catA->id);                 // "AAA kw"
        $mk((string) $catZ->id, (string) $catZ->id);                 // "ZZZ kw"
        $mk($catZ->id.','.$catA->id, (string) ($catA->id + $catZ->id)); // stored Z,A -> "AAA kw, ZZZ kw"

        $fetch = fn (string $dir) => collect($this->getJson(route('milestone.get_data', [
            'search' => ['value' => $kw],
            'order'  => [['column' => 0, 'dir' => $dir]],
        ]))->assertOk()->json('data'))->pluck('category')->all();

        $this->assertSame([
            'AAA '.$kw,
            'AAA '.$kw.', ZZZ '.$kw,
            'ZZZ '.$kw,
        ], $fetch('asc'));

        $this->assertSame([
            'ZZZ '.$kw,
            'AAA '.$kw.', ZZZ '.$kw,
            'AAA '.$kw,
        ], $fetch('desc'));
    }
}
