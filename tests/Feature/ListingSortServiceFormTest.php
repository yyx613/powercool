<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\ServiceForm;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * Locks in server-side sorting of the Service Form listing for the columns made
 * sortable: SR (idx 4, sr_sku DB column), Customer (idx 2, correlated subquery on
 * customers.company_name/name) and Technician (idx 3, correlated subquery on
 * users.name).
 *
 * Rows are isolated from any pre-existing data via a unique search keyword baked
 * into each created record's sku so search[value]= filters down to just our rows.
 */
class ListingSortServiceFormTest extends TestCase
{
    use DatabaseTransactions;

    private function userWithViewPermission(): User
    {
        Permission::firstOrCreate(['name' => 'service_form.view', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->givePermissionTo('service_form.view');

        return $user->fresh('roles');
    }

    private function makeCustomer(string $company): Customer
    {
        return Customer::create([
            'name' => $company . ' contact',
            'phone' => '000',
            'status' => 1,
            'company_name' => $company,
        ]);
    }

    private function makeTechnician(string $name): User
    {
        return User::factory()->create(['name' => $name]);
    }

    /**
     * Creates 3 service forms sharing the keyword so they can be isolated, with
     * distinct sr_sku, customer company_name and technician name.
     *
     * @return string the unique search keyword
     */
    private function makeThreeForms(): string
    {
        $kw = 'SFSORT' . uniqid();

        $rows = [
            ['sr' => 'B', 'customer' => 'Mango Sdn Bhd', 'tech' => 'Charlie'],
            ['sr' => 'C', 'customer' => 'Apple Sdn Bhd', 'tech' => 'Alice'],
            ['sr' => 'A', 'customer' => 'Zebra Sdn Bhd', 'tech' => 'Bob'],
        ];

        foreach ($rows as $i => $r) {
            ServiceForm::create([
                'sku' => $kw . '-' . $i,
                'date' => now(),
                'customer_id' => $this->makeCustomer($r['customer'])->id,
                'technician_id' => $this->makeTechnician($r['tech'])->id,
                'sr_sku' => $kw . '-SR-' . $r['sr'],
            ]);
        }

        return $kw;
    }

    private function ordered(string $kw, int $column, string $dir, string $field): array
    {
        return collect(
            $this->getJson(route('service_form.get_data', [
                'search' => ['value' => $kw],
                'order' => [['column' => $column, 'dir' => $dir]],
            ]))->assertOk()->json('data')
        )->pluck($field)->all();
    }

    public function test_sr_column_sorts_ascending_and_descending(): void
    {
        $kw = $this->makeThreeForms();
        $this->actingAs($this->userWithViewPermission());

        $asc = $this->ordered($kw, 4, 'asc', 'generated_service_form');
        $this->assertSame([
            $kw . '-SR-A', $kw . '-SR-B', $kw . '-SR-C',
        ], $asc);

        $desc = $this->ordered($kw, 4, 'desc', 'generated_service_form');
        $this->assertSame([
            $kw . '-SR-C', $kw . '-SR-B', $kw . '-SR-A',
        ], $desc);
    }

    public function test_customer_column_sorts_ascending_and_descending(): void
    {
        $kw = $this->makeThreeForms();
        $this->actingAs($this->userWithViewPermission());

        $asc = $this->ordered($kw, 2, 'asc', 'customer_name');
        $this->assertSame(['Apple Sdn Bhd', 'Mango Sdn Bhd', 'Zebra Sdn Bhd'], $asc);

        $desc = $this->ordered($kw, 2, 'desc', 'customer_name');
        $this->assertSame(['Zebra Sdn Bhd', 'Mango Sdn Bhd', 'Apple Sdn Bhd'], $desc);
    }

    public function test_technician_column_sorts_ascending(): void
    {
        $kw = $this->makeThreeForms();
        $this->actingAs($this->userWithViewPermission());

        $asc = $this->ordered($kw, 3, 'asc', 'technician');
        $this->assertSame(['Alice', 'Bob', 'Charlie'], $asc);
    }
}
