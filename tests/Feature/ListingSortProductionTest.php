<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomizeProduct;
use App\Models\Factory;
use App\Models\Priority;
use App\Models\Product;
use App\Models\Production;
use App\Models\RawMaterialRequest;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * Locks in server-side sorting of the Production listing for the columns that are
 * backed by a real/derivable DB value:
 *   - idx 3  Type          -> productions.type (display via typeToHumanRead)
 *   - idx 5  Factory        -> correlated subquery on factories.name
 *   - idx 10 Days Left      -> proxied by productions.due_date (monotonic)
 *   - idx 12 Material Status -> correlated subquery on raw_material_requests.status
 *
 * Rows are isolated onto a single page via a unique SKU token (the listing
 * hard-paginates at 10 and searches across `sku`).
 */
class ListingSortProductionTest extends TestCase
{
    use DatabaseTransactions;

    /** @var int user id used as raw_material_requests.requested_by */
    private $requesterId;

    /**
     * A user with NO branch and NO super-admin role. BranchScope only filters when
     * the user is a super admin or has a branch, so such a user sees every row we
     * create regardless of branch records.
     */
    private function viewerWithoutBranch(): User
    {
        Permission::firstOrCreate(['name' => 'production.view', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->givePermissionTo('production.view');

        return $user->fresh();
    }

    /**
     * Creates three productions sharing a unique SKU token. Their `type`, `due_date`
     * and factory names are deliberately staggered so ascending/descending order is
     * unambiguous.
     *
     * @return string the shared search token
     */
    private function makeThreeProductions(): string
    {
        $token = 'SORTPROD' . uniqid();
        $product = Product::first();
        $this->assertNotNull($product, 'Expected at least one Product in the test DB');

        // Any valid user id works for raw_material_requests.requested_by.
        $this->requesterId = User::factory()->create()->id;

        // Factory names chosen so alphabetical order differs from creation order.
        $factoryA = Factory::create(['name' => 'AAA ' . $token]);
        $factoryB = Factory::create(['name' => 'MMM ' . $token]);
        $factoryC = Factory::create(['name' => 'ZZZ ' . $token]);

        // Row 1: Normal type, latest due date, factory ZZZ, RMR status Completed (2)
        $p1 = Production::create([
            'product_id' => $product->id,
            'sku' => $token . '-1',
            'name' => 'P1 ' . $token,
            'type' => Production::TYPE_NORMAL,
            'status' => Production::STATUS_TO_DO,
            'factory_id' => $factoryC->id,
            'start_date' => '2026-01-01',
            'due_date' => '2026-12-31',
        ]);
        RawMaterialRequest::create([
            'production_id' => $p1->id,
            'status' => RawMaterialRequest::STATUS_COMPLETED,
            'requested_by' => $this->requesterId,
        ]);

        // Row 2: R&D type, middle due date, factory AAA. R&D rows render a
        // customizeProduct SKU, so attach one to keep the controller happy.
        $rnd = Production::create([
            'product_id' => $product->id,
            'sku' => $token . '-2',
            'name' => 'P2 ' . $token,
            'type' => Production::TYPE_RND,
            'status' => Production::STATUS_TO_DO,
            'factory_id' => $factoryA->id,
            'start_date' => '2026-01-01',
            'due_date' => '2026-06-15',
        ]);
        CustomizeProduct::create([
            'production_id' => $rnd->id,
            'sku' => 'CP-' . $token,
        ]);

        // Row 3: Normal type, earliest due date, factory MMM, RMR status In Progress (1)
        $p3 = Production::create([
            'product_id' => $product->id,
            'sku' => $token . '-3',
            'name' => 'P3 ' . $token,
            'type' => Production::TYPE_NORMAL,
            'status' => Production::STATUS_TO_DO,
            'factory_id' => $factoryB->id,
            'start_date' => '2026-01-01',
            'due_date' => '2026-02-01',
        ]);
        RawMaterialRequest::create([
            'production_id' => $p3->id,
            'status' => RawMaterialRequest::STATUS_IN_PROGRESS,
            'requested_by' => $this->requesterId,
        ]);

        return $token;
    }

    private function ordered(string $token, int $column, string $dir, string $pluck): array
    {
        return collect(
            $this->getJson(route('production.get_data', [
                'search' => ['value' => $token],
                'order' => [['column' => $column, 'dir' => $dir]],
            ]))->assertOk()->json('data')
        )->pluck($pluck)->values()->all();
    }

    public function test_type_column_sorts_ascending_and_descending(): void
    {
        $token = $this->makeThreeProductions();
        $this->actingAs($this->viewerWithoutBranch());

        // type column stores 1 (Normal) and 2 (R&D); display is 'Normal'/'R&D'.
        $asc = $this->ordered($token, 3, 'asc', 'type');
        $this->assertSame(['Normal', 'Normal', 'R&D'], $asc);

        $desc = $this->ordered($token, 3, 'desc', 'type');
        $this->assertSame(['R&D', 'Normal', 'Normal'], $desc);
    }

    public function test_so_column_sorts_by_sale_sku(): void
    {
        $this->actingAs($this->viewerWithoutBranch());

        $token = 'SOSORT'.uniqid();
        $product = Product::first();
        $this->assertNotNull($product);

        $cust = Customer::create(['status' => 1, 'company_name' => 'C '.$token]);
        $saleHigh = Sale::create(['sku' => 'ZZZ-'.$token, 'type' => Sale::TYPE_SO, 'customer_id' => $cust->id, 'status' => Sale::STATUS_ACTIVE, 'is_draft' => 0]);
        $saleLow = Sale::create(['sku' => 'AAA-'.$token, 'type' => Sale::TYPE_SO, 'customer_id' => $cust->id, 'status' => Sale::STATUS_ACTIVE, 'is_draft' => 0]);

        foreach ([[1, $saleHigh->id], [2, $saleLow->id]] as [$i, $saleId]) {
            Production::create([
                'product_id' => $product->id,
                'sku'        => $token.'-'.$i,
                'name'       => 'P'.$i.' '.$token,
                'type'       => Production::TYPE_NORMAL,
                'status'     => Production::STATUS_TO_DO,
                'sale_id'    => $saleId,
                'start_date' => '2026-01-01',
                'due_date'   => '2026-12-31',
            ]);
        }

        $asc = $this->ordered($token, 2, 'asc', 'sale_sku');
        $this->assertSame(['AAA-'.$token, 'ZZZ-'.$token], $asc);

        $desc = $this->ordered($token, 2, 'desc', 'sale_sku');
        $this->assertSame(['ZZZ-'.$token, 'AAA-'.$token], $desc);
    }

    public function test_priority_column_sorts_by_priority_name(): void
    {
        $this->actingAs($this->viewerWithoutBranch());

        $token = 'PRSORT'.uniqid();
        $product = Product::first();
        $pHigh = Priority::create(['name' => 'ZZZ '.$token]);
        $pLow = Priority::create(['name' => 'AAA '.$token]);

        foreach ([[1, $pHigh->id], [2, $pLow->id]] as [$i, $priorityId]) {
            Production::create([
                'product_id'  => $product->id,
                'sku'         => $token.'-'.$i,
                'name'        => 'P'.$i.' '.$token,
                'type'        => Production::TYPE_NORMAL,
                'status'      => Production::STATUS_TO_DO,
                'priority_id' => $priorityId,
                'start_date'  => '2026-01-01',
                'due_date'    => '2026-12-31',
            ]);
        }

        $asc = $this->ordered($token, 11, 'asc', 'priority');
        $this->assertSame(['AAA '.$token, 'ZZZ '.$token], array_map(fn ($p) => $p['name'] ?? null, $asc));

        $desc = $this->ordered($token, 11, 'desc', 'priority');
        $this->assertSame(['ZZZ '.$token, 'AAA '.$token], array_map(fn ($p) => $p['name'] ?? null, $desc));
    }

    public function test_days_left_column_sorts_by_due_date(): void
    {
        $token = $this->makeThreeProductions();
        $this->actingAs($this->viewerWithoutBranch());

        // Days Left is sorted by due_date proxy: ascending = earliest due first.
        $asc = $this->ordered($token, 10, 'asc', 'due_date');
        $this->assertSame(['2026-02-01', '2026-06-15', '2026-12-31'], $this->dateStrings($asc));

        $desc = $this->ordered($token, 10, 'desc', 'due_date');
        $this->assertSame(['2026-12-31', '2026-06-15', '2026-02-01'], $this->dateStrings($desc));
    }

    public function test_factory_column_sorts_by_factory_name(): void
    {
        $token = $this->makeThreeProductions();
        $this->actingAs($this->viewerWithoutBranch());

        $asc = collect($this->ordered($token, 5, 'asc', 'factory'))
            ->map(fn ($f) => $f['name'])->all();
        $this->assertSame(['AAA ' . $token, 'MMM ' . $token, 'ZZZ ' . $token], $asc);

        $desc = collect($this->ordered($token, 5, 'desc', 'factory'))
            ->map(fn ($f) => $f['name'])->all();
        $this->assertSame(['ZZZ ' . $token, 'MMM ' . $token, 'AAA ' . $token], $desc);
    }

    public function test_material_status_column_sorts_by_raw_material_request_status(): void
    {
        $token = $this->makeThreeProductions();
        $this->actingAs($this->viewerWithoutBranch());

        // request_status: row1=2 (Completed), row3=1 (In Progress), row2=null (R&D, no RMR).
        // MySQL sorts NULLs first ascending, last descending.
        $asc = $this->ordered($token, 12, 'asc', 'request_status');
        $this->assertSame([null, 1, 2], $asc);

        $desc = $this->ordered($token, 12, 'desc', 'request_status');
        $this->assertSame([2, 1, null], $desc);
    }

    public function test_progress_column_sorts_by_completion_percentage(): void
    {
        $token = $this->makeThreeProductions();
        $this->actingAs($this->viewerWithoutBranch());

        // Look up the three productions created by the shared token.
        $p1 = Production::where('sku', $token . '-1')->firstOrFail(); // -> 100%
        $p2 = Production::where('sku', $token . '-2')->firstOrFail(); // -> 50%
        $p3 = Production::where('sku', $token . '-3')->firstOrFail(); // -> 0%

        // p1: 2 of 2 milestones submitted = 100%
        \DB::table('production_milestone')->insert([
            ['production_id' => $p1->id, 'milestone_id' => 1, 'sequence' => 1, 'submitted_at' => now(), 'created_at' => now(), 'updated_at' => now()],
            ['production_id' => $p1->id, 'milestone_id' => 2, 'sequence' => 2, 'submitted_at' => now(), 'created_at' => now(), 'updated_at' => now()],
        ]);

        // p2: 1 of 2 milestones submitted = 50%
        \DB::table('production_milestone')->insert([
            ['production_id' => $p2->id, 'milestone_id' => 1, 'sequence' => 1, 'submitted_at' => now(), 'created_at' => now(), 'updated_at' => now()],
            ['production_id' => $p2->id, 'milestone_id' => 2, 'sequence' => 2, 'submitted_at' => null, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // p3: 0 of 2 milestones submitted = 0%
        \DB::table('production_milestone')->insert([
            ['production_id' => $p3->id, 'milestone_id' => 1, 'sequence' => 1, 'submitted_at' => null, 'created_at' => now(), 'updated_at' => now()],
            ['production_id' => $p3->id, 'milestone_id' => 2, 'sequence' => 2, 'submitted_at' => null, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $asc = $this->ordered($token, 14, 'asc', 'progress');
        $this->assertSame([0.0, 50.0, 100.0], array_map('floatval', $asc));

        $desc = $this->ordered($token, 14, 'desc', 'progress');
        $this->assertSame([100.0, 50.0, 0.0], array_map('floatval', $desc));
    }

    /**
     * due_date is returned as a serialized date (array/string depending on cast);
     * normalize to Y-m-d strings for comparison.
     */
    private function dateStrings(array $dates): array
    {
        return array_map(function ($d) {
            if (is_array($d)) {
                $d = $d['date'] ?? reset($d);
            }

            return substr((string) $d, 0, 10);
        }, $dates);
    }
}
