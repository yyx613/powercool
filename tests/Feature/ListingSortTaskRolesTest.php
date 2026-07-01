<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role as SpatieRole;
use Tests\TestCase;

/**
 * Server-side sorting of ALL data columns on the three Task listings.
 *
 * Column indices per role layout (see resources/views/task/list.blade.php):
 *   driver:     0 sku, 1 name, 2 due_date, 3 amount_to_collect,
 *               4 whatsapp_click_count, 5 status, 6 action
 *   technician: 0 checkbox, 1 sku, 2 name, 3 due_date,
 *               4 amount_to_collect, 5 status, 6 action
 *   sale:       0 sku, 1 name, 2 due_date, 3 amount_to_collect,
 *               4 status, 5 action
 *
 * Every sort key must reproduce the value shown in the cell. amount_to_collect
 * is displayed as number_format(value, 2) so numeric ordering of the raw column
 * is faithful; status is a code rendered as a label, and the code order
 * (1 To Do .. 4 Completed) matches the displayed grouping/progression.
 */
class ListingSortTaskRolesTest extends TestCase
{
    use DatabaseTransactions;

    private function userFor(string $role): User
    {
        $perm = 'task_' . $role . '.view';
        Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        $spatie = SpatieRole::firstOrCreate(['name' => 'Task Sort ' . $role, 'guard_name' => 'web']);
        $spatie->givePermissionTo($perm);

        // No branch attached => BranchScope is a no-op for this user.
        $user = User::create([
            'name' => 'Task Sort ' . $role,
            'email' => 'task_sort_' . $role . '_' . uniqid() . '@test.com',
            'password' => Hash::make('password'),
            'sku' => 'UTS' . uniqid(),
        ]);
        $user->assignRole($spatie);

        return $user->fresh('roles');
    }

    private function makeCustomer(): Customer
    {
        return Customer::create([
            'name' => 'Task Sort Customer',
            'phone' => '0123456789',
            'sku' => 'CTS' . uniqid(),
            'status' => Customer::STATUS_ACTIVE,
        ]);
    }

    private function makeTask(Customer $customer, int $type, string $marker, array $attrs): Task
    {
        return Task::create(array_merge([
            'customer_id' => $customer->id,
            'type' => $type,
            'sku' => 'T' . $marker . uniqid(),
            'name' => $marker . ' ' . uniqid(),
            'desc' => 'desc',
            'start_date' => '2026-07-01',
            'due_date' => '2026-07-10',
            'status' => Task::STATUS_TO_DO,
            'amount_to_collect' => 0,
            'whatsapp_click_count' => 0,
        ], $attrs));
    }

    private function fetch(User $user, string $role, string $marker, int $col, string $dir): array
    {
        return collect($this->actingAs($user)->getJson(route('task.get_data', [
            'role'   => $role,
            'search' => ['value' => $marker],
            'order'  => [['column' => $col, 'dir' => $dir]],
        ]))->assertOk()->json('data'))->all();
    }

    /** column index => data type metadata, per role. */
    private function dataColumns(string $role): array
    {
        $driver = [
            0 => 'sku',
            1 => 'name',
            2 => 'due_date',
            3 => 'amount_to_collect',
            4 => 'whatsapp_click_count',
            5 => 'status',
        ];
        $technician = [
            1 => 'sku',
            2 => 'name',
            3 => 'due_date',
            4 => 'amount_to_collect',
            5 => 'status',
        ];
        $sale = [
            0 => 'sku',
            1 => 'name',
            2 => 'due_date',
            3 => 'amount_to_collect',
            4 => 'status',
        ];

        return ['driver' => $driver, 'technician' => $technician, 'sale' => $sale][$role];
    }

    /**
     * (a) Smoke: ordering by every data-column index returns 200 for each role.
     *
     * @dataProvider roleProvider
     */
    public function test_get_data_ok_for_every_data_column(string $role): void
    {
        $user = $this->userFor($role);
        $type = constant(Task::class . '::TYPE_' . strtoupper($role));
        $customer = $this->makeCustomer();
        $marker = 'SMOKE' . strtoupper($role) . uniqid();

        $this->makeTask($customer, $type, $marker, ['amount_to_collect' => 5, 'whatsapp_click_count' => 2]);

        foreach (array_keys($this->dataColumns($role)) as $col) {
            foreach (['asc', 'desc'] as $dir) {
                $this->actingAs($user)->getJson(route('task.get_data', [
                    'role'  => $role,
                    'order' => [['column' => $col, 'dir' => $dir]],
                ]))->assertOk();
            }
        }
    }

    public static function roleProvider(): array
    {
        return [
            'driver' => ['driver'],
            'technician' => ['technician'],
            'sale' => ['sale'],
        ];
    }

    /**
     * (b) Faithful asc + desc on every data column for every role.
     * Values are inserted in scrambled order so a naive (insertion/id) order
     * would differ from the displayed order.
     *
     * @dataProvider roleProvider
     */
    public function test_faithful_sort_on_every_data_column(string $role): void
    {
        $user = $this->userFor($role);
        $type = constant(Task::class . '::TYPE_' . strtoupper($role));
        $customer = $this->makeCustomer();
        $cols = $this->dataColumns($role);

        // sku: distinct, scrambled insertion order.
        $marker = 'SKU' . strtoupper($role) . substr(uniqid(), -6);
        $skuB = $this->makeTask($customer, $type, $marker, ['sku' => 'Z' . $marker]);
        $skuA = $this->makeTask($customer, $type, $marker, ['sku' => 'A' . $marker]);
        $skuM = $this->makeTask($customer, $type, $marker, ['sku' => 'M' . $marker]);
        $skuCol = array_search('sku', $cols, true);
        $this->assertSame(
            ['A' . $marker, 'M' . $marker, 'Z' . $marker],
            collect($this->fetch($user, $role, $marker, $skuCol, 'asc'))->pluck('sku')->all()
        );
        $this->assertSame(
            ['Z' . $marker, 'M' . $marker, 'A' . $marker],
            collect($this->fetch($user, $role, $marker, $skuCol, 'desc'))->pluck('sku')->all()
        );

        // name: shared marker prefix, distinct ordering tail, scrambled insert.
        $nmk = 'NM' . strtoupper($role) . substr(uniqid(), -6) . ' ';
        $this->makeTask($customer, $type, '', ['name' => $nmk . 'mango']);
        $this->makeTask($customer, $type, '', ['name' => $nmk . 'apple']);
        $this->makeTask($customer, $type, '', ['name' => $nmk . 'zebra']);
        $nameCol = array_search('name', $cols, true);
        $this->assertSame(
            [$nmk . 'apple', $nmk . 'mango', $nmk . 'zebra'],
            collect($this->fetch($user, $role, $nmk, $nameCol, 'asc'))->pluck('name')->all()
        );
        $this->assertSame(
            [$nmk . 'zebra', $nmk . 'mango', $nmk . 'apple'],
            collect($this->fetch($user, $role, $nmk, $nameCol, 'desc'))->pluck('name')->all()
        );

        // due_date: distinct dates, scrambled insert.
        $dmk = 'DD' . strtoupper($role) . substr(uniqid(), -6);
        $this->makeTask($customer, $type, $dmk, ['due_date' => '2026-08-15']);
        $this->makeTask($customer, $type, $dmk, ['due_date' => '2026-08-01']);
        $this->makeTask($customer, $type, $dmk, ['due_date' => '2026-08-31']);
        $dueCol = array_search('due_date', $cols, true);
        $this->assertSame(
            ['2026-08-01', '2026-08-15', '2026-08-31'],
            collect($this->fetch($user, $role, $dmk, $dueCol, 'asc'))->pluck('due_date')->all()
        );
        $this->assertSame(
            ['2026-08-31', '2026-08-15', '2026-08-01'],
            collect($this->fetch($user, $role, $dmk, $dueCol, 'desc'))->pluck('due_date')->all()
        );

        // amount_to_collect: distinct numeric values, scrambled insert.
        // Displayed as number_format(.,2); 0 shows as null. Use non-zero values
        // plus boundary so numeric (not lexical) order is verified (9 vs 100).
        $amk = 'AMT' . strtoupper($role) . substr(uniqid(), -6);
        $this->makeTask($customer, $type, $amk, ['amount_to_collect' => 100]);
        $this->makeTask($customer, $type, $amk, ['amount_to_collect' => 9]);
        $this->makeTask($customer, $type, $amk, ['amount_to_collect' => 50.5]);
        $amtCol = array_search('amount_to_collect', $cols, true);
        $this->assertSame(
            ['9.00', '50.50', '100.00'],
            collect($this->fetch($user, $role, $amk, $amtCol, 'asc'))->pluck('amount_to_collect')->all()
        );
        $this->assertSame(
            ['100.00', '50.50', '9.00'],
            collect($this->fetch($user, $role, $amk, $amtCol, 'desc'))->pluck('amount_to_collect')->all()
        );

        // status: code rendered as label; code order matches displayed grouping.
        $stk = 'ST' . strtoupper($role) . substr(uniqid(), -6);
        $this->makeTask($customer, $type, $stk, ['status' => Task::STATUS_COMPLETED]);  // 4
        $this->makeTask($customer, $type, $stk, ['status' => Task::STATUS_TO_DO]);       // 1
        $this->makeTask($customer, $type, $stk, ['status' => Task::STATUS_IN_REVIEW]);   // 3
        $statusCol = array_search('status', $cols, true);
        $this->assertSame(
            [Task::STATUS_TO_DO, Task::STATUS_IN_REVIEW, Task::STATUS_COMPLETED],
            collect($this->fetch($user, $role, $stk, $statusCol, 'asc'))->pluck('status')->all()
        );
        $this->assertSame(
            [Task::STATUS_COMPLETED, Task::STATUS_IN_REVIEW, Task::STATUS_TO_DO],
            collect($this->fetch($user, $role, $stk, $statusCol, 'desc'))->pluck('status')->all()
        );

        // whatsapp_click_count: driver only.
        if (in_array('whatsapp_click_count', $cols, true)) {
            $wmk = 'WA' . strtoupper($role) . substr(uniqid(), -6);
            $this->makeTask($customer, $type, $wmk, ['whatsapp_click_count' => 100]);
            $this->makeTask($customer, $type, $wmk, ['whatsapp_click_count' => 9]);
            $this->makeTask($customer, $type, $wmk, ['whatsapp_click_count' => 50]);
            $waCol = array_search('whatsapp_click_count', $cols, true);
            $this->assertSame(
                [9, 50, 100],
                collect($this->fetch($user, $role, $wmk, $waCol, 'asc'))->pluck('whatsapp_click_count')->all()
            );
            $this->assertSame(
                [100, 50, 9],
                collect($this->fetch($user, $role, $wmk, $waCol, 'desc'))->pluck('whatsapp_click_count')->all()
            );
        }
    }
}
