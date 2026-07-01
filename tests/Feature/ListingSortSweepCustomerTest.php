<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\DeliveryOrder;
use App\Models\DeliveryOrderProduct;
use App\Models\DeliveryOrderProductChild;
use App\Models\ProductChild;
use App\Models\Sale;
use App\Models\SaleProduct;
use App\Models\SaleProductChild;
use App\Models\SalesAgent;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Session;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role as SpatieRole;
use Tests\TestCase;

/**
 * Server-side sort sweep for the listing pages whose remaining data columns were
 * just wired up. Smoke-asserts that ordering by every data-column index returns
 * 200, and proves a faithful asc+desc ordering on each newly sortable column.
 *
 *   customer/list        — already fully sortable; smoke every data col (1..10).
 *   user_management/list — Role (2) + Branch (3) newly sortable.
 *   cash_sale/list       — Serial No Qty (4) + Remaining Qty (5) newly sortable.
 */
class ListingSortSweepCustomerTest extends TestCase
{
    use DatabaseTransactions;

    /** Branchless user (BranchScope no-ops) with the given permissions. */
    private function branchlessUser(array $perms): User
    {
        foreach ($perms as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }
        $role = SpatieRole::firstOrCreate(['name' => 'Sweep Tester '.uniqid(), 'guard_name' => 'web']);
        $role->givePermissionTo($perms);

        $user = User::factory()->create();
        $user->assignRole($role);

        return $user->fresh('roles');
    }

    /** User attached to the KL branch (for branch-scoped listings). */
    private function userInKL(array $perms): User
    {
        $user = $this->branchlessUser($perms);
        Branch::create(['object_type' => User::class, 'object_id' => $user->id, 'location' => Branch::LOCATION_KL]);

        return $user->fresh(['branch', 'roles']);
    }

    // ------------------------------------------------------------------ customer

    public function test_customer_every_data_column_sorts_ok(): void
    {
        $this->actingAs($this->branchlessUser(['customer.view']));

        // Columns 0=checkbox, 11=action are non-data; 1..10 are data columns.
        foreach (range(1, 10) as $col) {
            $this->getJson(route('customer.get_data', [
                'order' => [['column' => $col, 'dir' => 'asc']],
            ]))->assertOk();
            $this->getJson(route('customer.get_data', [
                'order' => [['column' => $col, 'dir' => 'desc']],
            ]))->assertOk();
        }
    }

    // ----------------------------------------------------------- user_management

    /** Seed three users with distinct roles and branches, isolated by keyword in name. */
    private function seedUsers(string $kw): void
    {
        $roleA = SpatieRole::firstOrCreate(['name' => 'AAA '.$kw, 'guard_name' => 'web']);
        $roleB = SpatieRole::firstOrCreate(['name' => 'BBB '.$kw, 'guard_name' => 'web']);
        $roleC = SpatieRole::firstOrCreate(['name' => 'CCC '.$kw, 'guard_name' => 'web']);

        // Branch labels: Every(0) < Kuala Lumpur(1) < Penang(2) — code order == label order.
        $rows = [
            [$roleB, Branch::LOCATION_KL],     // BBB / Kuala Lumpur
            [$roleA, Branch::LOCATION_EVERY],  // AAA / Every
            [$roleC, Branch::LOCATION_PENANG], // CCC / Penang
        ];
        foreach ($rows as $i => [$role, $loc]) {
            $u = User::factory()->create(['name' => $kw.' U'.$i, 'email' => $kw.$i.'@t.test']);
            $u->assignRole($role);
            Branch::create(['object_type' => User::class, 'object_id' => $u->id, 'location' => $loc]);
        }
    }

    private function userSorted(int $col, string $dir, string $kw, string $field): array
    {
        return collect($this->getJson(route('user_management.get_data', [
            'search' => ['value' => $kw],
            'order'  => [['column' => $col, 'dir' => $dir]],
        ]))->assertOk()->json('data'))->pluck($field)->all();
    }

    public function test_user_management_every_data_column_sorts_ok(): void
    {
        $this->actingAs($this->branchlessUser(['user_role_management.view']));

        // 0=Name, 1=Email, 2=Role, 3=Branch are data cols; 4=action is not.
        foreach (range(0, 3) as $col) {
            $this->getJson(route('user_management.get_data', [
                'order' => [['column' => $col, 'dir' => 'asc']],
            ]))->assertOk();
            $this->getJson(route('user_management.get_data', [
                'order' => [['column' => $col, 'dir' => 'desc']],
            ]))->assertOk();
        }
    }

    public function test_user_management_role_column_sorts_both_directions(): void
    {
        $this->actingAs($this->branchlessUser(['user_role_management.view']));
        $kw = 'USRROLE'.uniqid();
        $this->seedUsers($kw);

        $asc = $this->userSorted(2, 'asc', $kw, 'role');
        $this->assertSame(['AAA '.$kw, 'BBB '.$kw, 'CCC '.$kw], $asc);

        $desc = $this->userSorted(2, 'desc', $kw, 'role');
        $this->assertSame(['CCC '.$kw, 'BBB '.$kw, 'AAA '.$kw], $desc);
    }

    public function test_user_management_branch_column_sorts_both_directions(): void
    {
        $this->actingAs($this->branchlessUser(['user_role_management.view']));
        $kw = 'USRBR'.uniqid();
        $this->seedUsers($kw);

        $asc = $this->userSorted(3, 'asc', $kw, 'branch');
        $this->assertSame(['Every', 'Kuala Lumpur', 'Penang'], $asc);

        $desc = $this->userSorted(3, 'desc', $kw, 'branch');
        $this->assertSame(['Penang', 'Kuala Lumpur', 'Every'], $desc);
    }

    // ----------------------------------------------------------------- cash_sale

    /** Cash sale at KL branch with $serialCount serials and $convertedCount converted. */
    private function makeCashSale(string $sku, int $serialCount, int $convertedCount): Sale
    {
        $customer = Customer::create(['status' => 1, 'company_name' => 'CS Co '.uniqid()]);
        Branch::create(['object_type' => Customer::class, 'object_id' => $customer->id, 'location' => Branch::LOCATION_KL]);

        $sale = Sale::create([
            'sku'         => $sku,
            'type'        => Sale::TYPE_CASH_SALE,
            'customer_id' => $customer->id,
            'status'      => Sale::STATUS_ACTIVE,
            'is_draft'    => 0,
        ]);
        Branch::create(['object_type' => Sale::class, 'object_id' => $sale->id, 'location' => Branch::LOCATION_KL]);

        $sp = SaleProduct::create([
            'sale_id'       => $sale->id,
            'product_id'    => 1,
            'qty'           => max($serialCount, 1),
            'unit_price'    => 10,
            'discount_type' => 'fixed',
        ]);
        $pcId = ProductChild::value('id') ?? 1;
        for ($i = 0; $i < $serialCount; $i++) {
            SaleProductChild::create(['sale_product_id' => $sp->id, 'product_children_id' => $pcId]);
        }

        if ($convertedCount > 0) {
            $do = DeliveryOrder::create(['sku' => 'DO-'.$sku, 'customer_id' => $customer->id]);
            Branch::create(['object_type' => DeliveryOrder::class, 'object_id' => $do->id, 'location' => Branch::LOCATION_KL]);
            $dop = DeliveryOrderProduct::create([
                'delivery_order_id' => $do->id,
                'sale_product_id'   => $sp->id,
                'qty'               => $convertedCount,
            ]);
            for ($i = 0; $i < $convertedCount; $i++) {
                DeliveryOrderProductChild::create(['delivery_order_product_id' => $dop->id, 'product_children_id' => $pcId]);
            }
        }

        return $sale;
    }

    private function cashSorted(int $col, string $dir, string $kw, string $field): array
    {
        return collect($this->getJson(route('cash_sale.get_data', [
            'search' => ['value' => $kw],
            'order'  => [['column' => $col, 'dir' => $dir]],
        ]))->assertOk()->json('data'))->pluck($field)->all();
    }

    public function test_cash_sale_every_data_column_sorts_ok(): void
    {
        $this->actingAs($this->userInKL(['sale.cash_sale.view']));
        Session::put('as_branch', Branch::LOCATION_KL);

        // 0..12 are data cols; 13=action is not.
        foreach (range(0, 12) as $col) {
            $this->getJson(route('cash_sale.get_data', [
                'order' => [['column' => $col, 'dir' => 'asc']],
            ]))->assertOk();
            $this->getJson(route('cash_sale.get_data', [
                'order' => [['column' => $col, 'dir' => 'desc']],
            ]))->assertOk();
        }
    }

    public function test_cash_sale_serial_no_qty_column_sorts_both_directions(): void
    {
        $this->actingAs($this->userInKL(['sale.cash_sale.view']));
        Session::put('as_branch', Branch::LOCATION_KL);

        $kw = 'CSSER'.uniqid();
        $this->makeCashSale($kw.'-LOW', 1, 0);
        $this->makeCashSale($kw.'-HIGH', 3, 0);

        $asc = array_map('intval', $this->cashSorted(4, 'asc', $kw, 'serial_no_qty'));
        $this->assertSame([1, 3], $asc);

        $desc = array_map('intval', $this->cashSorted(4, 'desc', $kw, 'serial_no_qty'));
        $this->assertSame([3, 1], $desc);
    }

    public function test_cash_sale_remaining_qty_column_sorts_both_directions(): void
    {
        $this->actingAs($this->userInKL(['sale.cash_sale.view']));
        Session::put('as_branch', Branch::LOCATION_KL);

        $kw = 'CSREM'.uniqid();
        $this->makeCashSale($kw.'-FEW', 3, 1);  // 1 converted serial
        $this->makeCashSale($kw.'-MANY', 3, 2); // 2 converted serials

        $asc = array_map('intval', $this->cashSorted(5, 'asc', $kw, 'not_converted_serial_no_qty'));
        $this->assertSame([1, 2], $asc);

        $desc = array_map('intval', $this->cashSorted(5, 'desc', $kw, 'not_converted_serial_no_qty'));
        $this->assertSame([2, 1], $desc);
    }
}
