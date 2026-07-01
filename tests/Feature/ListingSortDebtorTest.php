<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerSaleAgent;
use App\Models\DebtorType;
use App\Models\Platform;
use App\Models\SalesAgent;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role as SpatieRole;
use Tests\TestCase;

/**
 * Server-side sorting for the Debtor (customer) listing.
 *
 * Covers the four columns that became sortable: Debt Type (relation),
 * Platform (relation), Sales Agents (many-to-many) and Status (own column).
 * The acting user has no branch, so BranchScope is a no-op and branchless
 * fixtures are visible. Rows are isolated with a unique search keyword.
 */
class ListingSortDebtorTest extends TestCase
{
    use DatabaseTransactions;

    private string $kw;

    private function actingUser(): User
    {
        Permission::firstOrCreate(['name' => 'customer.view', 'guard_name' => 'web']);

        $role = SpatieRole::firstOrCreate(['name' => 'Debtor Sort Tester', 'guard_name' => 'web']);
        $role->givePermissionTo('customer.view');

        $user = User::factory()->create(); // no Branch row => BranchScope no-ops
        $user->assignRole($role);

        return $user->fresh('roles');
    }

    /**
     * Build 3 debtors carrying the unique keyword (in company_name so the search
     * matches), each with a distinct status, debt type, platform and one agent.
     */
    private function seedDebtors(): array
    {
        $this->kw = 'ZSORT' . uniqid();

        $debtA = DebtorType::create(['name' => 'AAA ' . $this->kw]);
        $debtB = DebtorType::create(['name' => 'BBB ' . $this->kw]);
        $debtC = DebtorType::create(['name' => 'CCC ' . $this->kw]);

        $platA = Platform::create(['name' => 'Alpha ' . $this->kw]);
        $platB = Platform::create(['name' => 'Bravo ' . $this->kw]);
        $platC = Platform::create(['name' => 'Charlie ' . $this->kw]);

        $agentA = SalesAgent::create(['name' => 'Anna ' . $this->kw]);
        $agentB = SalesAgent::create(['name' => 'Bob ' . $this->kw]);
        $agentC = SalesAgent::create(['name' => 'Cara ' . $this->kw]);

        // status values chosen so label order != insertion order:
        //   ACTIVE(1)='Active', INACTIVE(0)='Inactive', APPROVED(5)='Approved'
        $rows = [
            [Customer::STATUS_ACTIVE,   $debtB, $platB, $agentB], // Active   / BBB / Bravo   / Bob
            [Customer::STATUS_INACTIVE, $debtA, $platC, $agentC], // Inactive / AAA / Charlie / Cara
            [Customer::STATUS_APPROVAL_APPROVED, $debtC, $platA, $agentA], // Approved / CCC / Alpha / Anna
        ];

        foreach ($rows as $i => [$status, $debt, $plat, $agent]) {
            $cus = Customer::create([
                'company_name'   => $this->kw . ' Co ' . $i,
                'status'         => $status,
                'debtor_type_id' => $debt->id,
                'platform_id'    => $plat->id,
            ]);
            CustomerSaleAgent::create([
                'customer_id'    => $cus->id,
                'sales_agent_id' => $agent->id,
            ]);
        }

        return [];
    }

    /** Fetch the 'data' column values for a given DataTables order request. */
    private function sortedColumn(User $user, int $column, string $dir, string $field): array
    {
        return collect($this->actingAs($user)->getJson(route('customer.get_data', [
            'search' => ['value' => $this->kw],
            'order'  => [['column' => $column, 'dir' => $dir]],
        ]))->assertOk()->json('data'))->pluck($field)->all();
    }

    public function test_status_column_sorts_both_directions(): void
    {
        $user = $this->actingUser();
        $this->seedDebtors();

        // Status sorts by the underlying numeric column, not the label text:
        //   INACTIVE(0) < ACTIVE(1) < APPROVED(5).
        $asc = $this->sortedColumn($user, 10, 'asc', 'status');
        $this->assertSame(['Inactive', 'Active', 'Approved'], $asc);

        $desc = $this->sortedColumn($user, 10, 'desc', 'status');
        $this->assertSame(['Approved', 'Active', 'Inactive'], $desc);
    }

    public function test_debt_type_column_sorts_both_directions(): void
    {
        $user = $this->actingUser();
        $this->seedDebtors();

        $asc = $this->sortedColumn($user, 6, 'asc', 'debt_type');
        $this->assertSame([
            'AAA ' . $this->kw,
            'BBB ' . $this->kw,
            'CCC ' . $this->kw,
        ], $asc);

        $desc = $this->sortedColumn($user, 6, 'desc', 'debt_type');
        $this->assertSame([
            'CCC ' . $this->kw,
            'BBB ' . $this->kw,
            'AAA ' . $this->kw,
        ], $desc);
    }

    public function test_platform_column_sorts_both_directions(): void
    {
        $user = $this->actingUser();
        $this->seedDebtors();

        $asc = $this->sortedColumn($user, 8, 'asc', 'platform');
        $this->assertSame([
            'Alpha ' . $this->kw,
            'Bravo ' . $this->kw,
            'Charlie ' . $this->kw,
        ], $asc);

        $desc = $this->sortedColumn($user, 8, 'desc', 'platform');
        $this->assertSame([
            'Charlie ' . $this->kw,
            'Bravo ' . $this->kw,
            'Alpha ' . $this->kw,
        ], $desc);
    }

    public function test_sales_agents_column_sorts_both_directions(): void
    {
        $user = $this->actingUser();
        $this->seedDebtors();

        $asc = $this->sortedColumn($user, 9, 'asc', 'sales_agents');
        $this->assertSame([
            'Anna ' . $this->kw,
            'Bob ' . $this->kw,
            'Cara ' . $this->kw,
        ], $asc);

        $desc = $this->sortedColumn($user, 9, 'desc', 'sales_agents');
        $this->assertSame([
            'Cara ' . $this->kw,
            'Bob ' . $this->kw,
            'Anna ' . $this->kw,
        ], $desc);
    }

    /**
     * For a customer with MULTIPLE agents, the sort key must equal the DISPLAYED string,
     * which lists names in sales_agent.id order separated by ', ' (not alphabetised, not
     * comma-only). This case fails under the old `group_concat(... order by sa.name)` key.
     */
    public function test_sales_agents_multi_agent_sort_matches_displayed_order(): void
    {
        $user = $this->actingUser();
        $this->kw = 'MASORT' . uniqid();

        // Created in non-alphabetical order so id-order != name-order.
        $zoe = SalesAgent::create(['name' => 'Zoe ' . $this->kw]); // smallest id
        $amy = SalesAgent::create(['name' => 'Amy ' . $this->kw]);
        $mid = SalesAgent::create(['name' => 'Mph ' . $this->kw]); // largest id

        // Customer X: two agents -> display "Zoe ..., Amy ..." (id order, ', ' sep).
        $x = Customer::create(['company_name' => $this->kw . ' X', 'status' => Customer::STATUS_ACTIVE]);
        CustomerSaleAgent::create(['customer_id' => $x->id, 'sales_agent_id' => $zoe->id]);
        CustomerSaleAgent::create(['customer_id' => $x->id, 'sales_agent_id' => $amy->id]);

        // Customer Y: one agent -> "Mph ...".
        $y = Customer::create(['company_name' => $this->kw . ' Y', 'status' => Customer::STATUS_ACTIVE]);
        CustomerSaleAgent::create(['customer_id' => $y->id, 'sales_agent_id' => $mid->id]);

        $expectedX = 'Zoe ' . $this->kw . ', Amy ' . $this->kw;
        $expectedY = 'Mph ' . $this->kw;

        // Ascending by the displayed string: "Mph ..." (M) < "Zoe ..." (Z).
        $asc = $this->sortedColumn($user, 9, 'asc', 'sales_agents');
        $this->assertSame([$expectedY, $expectedX], $asc);

        $desc = $this->sortedColumn($user, 9, 'desc', 'sales_agents');
        $this->assertSame([$expectedX, $expectedY], $desc);
    }
}
