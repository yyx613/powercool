<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Session;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role as SpatieRole;
use Tests\TestCase;

/**
 * Covers the new "Any" transfer-type filter on the Sale Order listing, which should
 * surface every SO at the current branch (normal + transferred) in a single view while
 * keeping each row's actions consistent with its dedicated filter.
 */
class SaleOrderAnyFilterTest extends TestCase
{
    use DatabaseTransactions;

    private function saleManagerInKL(): User
    {
        foreach (['sale.sale_order.view', 'sale.sale_order.edit', 'sale.sale_order.cancel'] as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $role = SpatieRole::firstOrCreate(['name' => 'SO Any Tester', 'guard_name' => 'web']);
        $role->givePermissionTo(['sale.sale_order.view', 'sale.sale_order.edit', 'sale.sale_order.cancel']);

        $user = User::factory()->create();
        $user->assignRole($role);
        Branch::create([
            'object_type' => User::class,
            'object_id'   => $user->id,
            'location'    => Branch::LOCATION_KL,
        ]);

        return $user->fresh(['branch', 'roles']);
    }

    private function makeCustomer(): Customer
    {
        $customer = Customer::create(['status' => 1, 'company_name' => 'Test Co '.uniqid()]);
        Branch::create([
            'object_type' => Customer::class,
            'object_id'   => $customer->id,
            'location'    => Branch::LOCATION_KL,
        ]);

        return $customer;
    }

    private function makeSale(int $type, ?int $transferFrom): Sale
    {
        $sale = Sale::create([
            'sku'           => 'TST-'.uniqid(),
            'type'          => $type,
            'transfer_from' => $transferFrom,
            'customer_id'   => $this->makeCustomer()->id,
            'status'        => Sale::STATUS_ACTIVE,
            'is_draft'      => 0,
        ]);
        Branch::create([
            'object_type' => Sale::class,
            'object_id'   => $sale->id,
            'location'    => Branch::LOCATION_KL,
        ]);

        return $sale;
    }

    private function fetch(int $transferType): array
    {
        $response = $this->getJson(route('sale_order.get_data', ['transfer_type' => $transferType]));
        $response->assertOk();

        return $response->json('data');
    }

    public function test_any_filter_returns_both_normal_and_transferred_sale_orders(): void
    {
        $this->actingAs($this->saleManagerInKL());
        Session::put('as_branch', Branch::LOCATION_KL);

        $quo = $this->makeSale(Sale::TYPE_QUO, null);
        $normalSo = $this->makeSale(Sale::TYPE_SO, null);
        $transferredAwaySo = $this->makeSale(Sale::TYPE_SO, $quo->id); // original that was transferred out

        $normalRows = collect($this->fetch(Sale::TRANSFER_TYPE_NORMAL))->pluck('id');
        $this->assertTrue($normalRows->contains($normalSo->id));
        $this->assertFalse($normalRows->contains($transferredAwaySo->id), 'Normal filter must hide transferred SO');

        $anyRows = collect($this->fetch(Sale::TRANSFER_TYPE_ANY))->pluck('id');
        $this->assertTrue($anyRows->contains($normalSo->id), 'Any filter must include the normal SO');
        $this->assertTrue($anyRows->contains($transferredAwaySo->id), 'Any filter must include the transferred SO');
    }

    public function test_any_filter_resolves_row_actions_per_effective_type(): void
    {
        $this->actingAs($this->saleManagerInKL());
        Session::put('as_branch', Branch::LOCATION_KL);

        $quo = $this->makeSale(Sale::TYPE_QUO, null);
        $normalSo = $this->makeSale(Sale::TYPE_SO, null);
        $transferredAwaySo = $this->makeSale(Sale::TYPE_SO, $quo->id);

        $rows = collect($this->fetch(Sale::TRANSFER_TYPE_ANY))->keyBy('id');

        // Normal row behaves like the Normal tab: editable and transferable.
        $this->assertTrue($rows[$normalSo->id]['can_edit']);
        $this->assertTrue($rows[$normalSo->id]['can_transfer']);

        // Transferred-away (Transfer To) row stays locked, matching its dedicated tab.
        $this->assertFalse($rows[$transferredAwaySo->id]['can_edit']);
        $this->assertFalse($rows[$transferredAwaySo->id]['can_transfer']);
    }
}
