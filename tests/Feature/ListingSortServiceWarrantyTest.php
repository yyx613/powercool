<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\InventoryServiceReminder;
use App\Models\Product;
use App\Models\ProductChild;
use App\Models\TaskMilestoneInventory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * Faithful server-side sort coverage for three listings:
 *  - Service History  (service_history.get_data)  : Serial No (0), Task ID (1), Qty (3), Service Date (4)
 *  - Service Reminder (service_reminder.get_data)  : SKU (0), Next Service Date (1), Last Service Date (2)
 *  - Warranty         (warranty.get_data)          : Invoice (0), Customer (1), Product (2), Serial No (3), Warranty (4), Warranty Date (5)
 *
 * Non-sortable (verified, reported): Service History Technician (2, polymorphic user/stockOutTo)
 * and Photo (5, attachment list); Service Reminder action (3); Warranty action (6).
 */
class ListingSortServiceWarrantyTest extends TestCase
{
    use DatabaseTransactions;

    private function userWith(array $permissions): User
    {
        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }
        $user = User::factory()->create();
        foreach ($permissions as $perm) {
            $user->givePermissionTo($perm);
        }

        return $user;
    }

    private function fetch(string $route, int $column, string $dir, array $extra = []): array
    {
        return collect($this->getJson(route($route, array_merge([
            'order' => [['column' => $column, 'dir' => $dir]],
        ], $extra)))->assertOk()->json('data'))->all();
    }

    private function makeProduct(string $sku): Product
    {
        $catId = DB::table('inventory_categories')->insertGetId([
            'name' => 'Cat '.uniqid(),
            'company_group' => 1,
            'is_active' => 1,
        ]);

        return Product::create([
            'inventory_category_id' => $catId,
            'type' => Product::TYPE_PRODUCT,
            'sku' => $sku,
            'model_desc' => $sku.' desc',
            'in_production' => 0,
            'cost' => 0,
            'is_active' => 1,
            'min_price' => 0,
            'max_price' => 0,
        ]);
    }

    private function makeProductChild(Product $product, string $sku): ProductChild
    {
        return ProductChild::create([
            'product_id' => $product->id,
            'sku' => $sku,
            'location' => ProductChild::LOCATION_FACTORY,
        ]);
    }

    // ---------------------------------------------------------------------
    // Service History
    // ---------------------------------------------------------------------

    public function test_service_history_smoke_every_data_column_orderable(): void
    {
        $this->actingAs($this->userWith(['service_history.view']));

        // data column indices: 0 serial_no, 1 task_sku, 3 qty, 4 service_date
        foreach ([0, 1, 3, 4] as $col) {
            $this->getJson(route('service_history.get_data', [
                'order' => [['column' => $col, 'dir' => 'asc']],
            ]))->assertOk();
        }
    }

    public function test_service_history_serial_no_sort_matches_displayed_sku(): void
    {
        $this->actingAs($this->userWith(['service_history.view']));
        $kw = 'SHSER'.uniqid();

        // Insert in an order where id-asc differs from sku-asc.
        $p = $this->makeProduct($kw.'-P');
        $pcC = $this->makeProductChild($p, $kw.'-C'); // id 1
        $pcA = $this->makeProductChild($p, $kw.'-A'); // id 2
        $pcB = $this->makeProductChild($p, $kw.'-B'); // id 3

        foreach ([$pcC, $pcA, $pcB] as $pc) {
            TaskMilestoneInventory::create([
                'inventory_type' => ProductChild::class,
                'inventory_id' => $pc->id,
                'qty' => 1,
                'service_date' => '2026-01-01',
            ]);
        }

        $asc = collect($this->fetch('service_history.get_data', 0, 'asc', ['search' => ['value' => $kw]]))
            ->pluck('serial_no')->all();
        $this->assertSame([$kw.'-A', $kw.'-B', $kw.'-C'], $asc);

        $desc = collect($this->fetch('service_history.get_data', 0, 'desc', ['search' => ['value' => $kw]]))
            ->pluck('serial_no')->all();
        $this->assertSame([$kw.'-C', $kw.'-B', $kw.'-A'], $desc);
    }

    public function test_service_history_task_id_sort_matches_displayed_task_sku(): void
    {
        $this->actingAs($this->userWith(['service_history.view']));
        $kw = 'SHTASK'.uniqid();

        $p = $this->makeProduct($kw.'-P');

        // Two tasks with sku order opposite to id order.
        $taskHigh = $this->makeTaskWithMilestone($kw.'-Z', $p); // id smaller, sku larger
        $taskLow = $this->makeTaskWithMilestone($kw.'-A', $p);  // id larger, sku smaller

        foreach ([$taskHigh, $taskLow] as $tm) {
            $pc = $this->makeProductChild($p, 'PCT'.uniqid());
            TaskMilestoneInventory::create([
                'inventory_type' => ProductChild::class,
                'inventory_id' => $pc->id,
                'task_milestone_id' => $tm,
                'qty' => 1,
                'service_date' => '2026-01-01',
            ]);
        }

        $asc = collect($this->fetch('service_history.get_data', 1, 'asc', ['search' => ['value' => $kw]]))
            ->pluck('task_sku')->all();
        $this->assertSame([$kw.'-A', $kw.'-Z'], $asc);

        $desc = collect($this->fetch('service_history.get_data', 1, 'desc', ['search' => ['value' => $kw]]))
            ->pluck('task_sku')->all();
        $this->assertSame([$kw.'-Z', $kw.'-A'], $desc);
    }

    public function test_service_history_qty_and_service_date_sortable(): void
    {
        $this->actingAs($this->userWith(['service_history.view']));
        $kw = 'SHQTY'.uniqid();
        $p = $this->makeProduct($kw.'-P');

        $rows = [
            ['qty' => 30, 'service_date' => '2026-03-01'],
            ['qty' => 10, 'service_date' => '2026-01-01'],
            ['qty' => 20, 'service_date' => '2026-02-01'],
        ];
        foreach ($rows as $r) {
            $pc = $this->makeProductChild($p, $kw.'-'.$r['qty']);
            TaskMilestoneInventory::create([
                'inventory_type' => ProductChild::class,
                'inventory_id' => $pc->id,
                'qty' => $r['qty'],
                'service_date' => $r['service_date'],
            ]);
        }

        $qtyAsc = collect($this->fetch('service_history.get_data', 3, 'asc', ['search' => ['value' => $kw]]))
            ->pluck('qty')->all();
        $this->assertSame([10, 20, 30], $qtyAsc);

        $dateDesc = collect($this->fetch('service_history.get_data', 4, 'desc', ['search' => ['value' => $kw]]))
            ->pluck('service_date')->map(fn ($d) => substr($d, 0, 10))->all();
        $this->assertSame(['2026-03-01', '2026-02-01', '2026-01-01'], $dateDesc);
    }

    private function makeTaskWithMilestone(string $taskSku, Product $p): int
    {
        $milestoneId = DB::table('milestones')->insertGetId([
            'type' => 1,
            'name' => 'MS '.uniqid(),
            'is_custom' => 0,
        ]);
        $taskId = DB::table('tasks')->insertGetId([
            'customer_id' => $this->makeCustomer('CUS'.uniqid()),
            'sku' => $taskSku,
            'type' => 1,
            'name' => 'Task '.uniqid(),
            'desc' => 'd',
            'start_date' => '2026-01-01',
            'due_date' => '2026-01-02',
            'status' => 1,
        ]);

        return DB::table('task_milestone')->insertGetId([
            'task_id' => $taskId,
            'milestone_id' => $milestoneId,
        ]);
    }

    private function makeCustomer(string $name): int
    {
        return DB::table('customers')->insertGetId([
            'name' => $name,
            'company_group' => 1,
        ]);
    }

    // ---------------------------------------------------------------------
    // Service Reminder
    // ---------------------------------------------------------------------

    public function test_service_reminder_smoke_every_data_column_orderable(): void
    {
        $this->actingAs($this->userWith(['service_reminder.view']));

        foreach ([0, 1, 2] as $col) {
            $this->getJson(route('service_reminder.get_data', [
                'order' => [['column' => $col, 'dir' => 'asc']],
            ]))->assertOk();
        }
    }

    public function test_service_reminder_sku_sort_matches_displayed_sku(): void
    {
        $this->actingAs($this->userWith(['service_reminder.view']));
        $kw = 'SRSKU'.uniqid();

        // Products with id-order opposite to sku-order; one reminder each (one group per product).
        $pC = $this->makeProduct($kw.'-C');
        $pA = $this->makeProduct($kw.'-A');
        $pB = $this->makeProduct($kw.'-B');

        foreach ([$pC, $pA, $pB] as $i => $p) {
            InventoryServiceReminder::create([
                'object_type' => Product::class,
                'object_id' => $p->id,
                'next_service_date' => '2026-0'.($i + 1).'-01 00:00:00',
            ]);
        }

        $asc = collect($this->fetch('service_reminder.get_data', 0, 'asc', ['search' => ['value' => $kw]]))
            ->pluck('sku')->all();
        $this->assertSame([$kw.'-A', $kw.'-B', $kw.'-C'], $asc);

        $desc = collect($this->fetch('service_reminder.get_data', 0, 'desc', ['search' => ['value' => $kw]]))
            ->pluck('sku')->all();
        $this->assertSame([$kw.'-C', $kw.'-B', $kw.'-A'], $desc);
    }

    public function test_service_reminder_next_service_date_sort_matches_displayed(): void
    {
        $this->actingAs($this->userWith(['service_reminder.view']));
        $kw = 'SRNXT'.uniqid();

        // Each product = one group; the displayed Next Service Date is the latest (max id) reminder.
        $pHi = $this->makeProduct($kw.'-HI');
        $pLo = $this->makeProduct($kw.'-LO');

        // pLo group: latest reminder = 2026-01-10
        InventoryServiceReminder::create(['object_type' => Product::class, 'object_id' => $pLo->id, 'next_service_date' => '2025-12-01 00:00:00']);
        InventoryServiceReminder::create(['object_type' => Product::class, 'object_id' => $pLo->id, 'next_service_date' => '2026-01-10 00:00:00']);
        // pHi group: latest reminder = 2026-09-10
        InventoryServiceReminder::create(['object_type' => Product::class, 'object_id' => $pHi->id, 'next_service_date' => '2025-12-01 00:00:00']);
        InventoryServiceReminder::create(['object_type' => Product::class, 'object_id' => $pHi->id, 'next_service_date' => '2026-09-10 00:00:00']);

        $asc = collect($this->fetch('service_reminder.get_data', 1, 'asc', ['search' => ['value' => $kw]]))
            ->pluck('next_service_date')->all();
        $this->assertSame(['10 Jan 2026', '10 Sep 2026'], $asc);

        $desc = collect($this->fetch('service_reminder.get_data', 1, 'desc', ['search' => ['value' => $kw]]))
            ->pluck('next_service_date')->all();
        $this->assertSame(['10 Sep 2026', '10 Jan 2026'], $desc);
    }

    // ---------------------------------------------------------------------
    // Warranty
    // ---------------------------------------------------------------------

    public function test_warranty_smoke_every_data_column_orderable(): void
    {
        $this->actingAs($this->userWith(['warranty.view']));

        foreach ([0, 1, 2, 3, 4, 5] as $col) {
            $this->getJson(route('warranty.get_data', [
                'order' => [['column' => $col, 'dir' => 'asc']],
            ]))->assertOk();
        }
    }

    /**
     * Builds one full warranty chain row and returns the displayed serial_no for assertions.
     */
    private function makeWarrantyRow(string $kw, array $o): string
    {
        $customerId = $this->makeCustomer($o['customer']);
        $product = $this->makeProduct($o['product_sku']);
        $product->update(['model_desc' => $o['product_model']]);

        $invId = DB::table('invoices')->insertGetId([
            'sku' => $o['inv_sku'],
            'filename' => 'inv.pdf',
            'status' => Invoice::STATUS_VALID,
            'created_at' => $o['inv_created_at'],
            'updated_at' => $o['inv_created_at'],
        ]);

        $saleId = DB::table('sales')->insertGetId([
            'customer_id' => $customerId,
            'sku' => 'SO'.uniqid(),
            'type' => 1,
        ]);

        $saleProductId = DB::table('sale_products')->insertGetId([
            'sale_id' => $saleId,
            'product_id' => $product->id,
            'qty' => 1,
            'unit_price' => 0,
        ]);

        $wpId = DB::table('warranty_periods')->insertGetId([
            'name' => $o['warranty_name'],
            'period' => $o['warranty_period'],
            'is_active' => 1,
        ]);
        DB::table('sale_product_warranty_periods')->insert([
            'sale_product_id' => $saleProductId,
            'warranty_period_id' => $wpId,
        ]);

        $doId = DB::table('delivery_orders')->insertGetId([
            'invoice_id' => $invId,
            'customer_id' => $customerId,
            'sku' => 'DO'.uniqid(),
            'sale_id' => $saleId,
            'filename' => 'do.pdf',
        ]);
        $dopId = DB::table('delivery_order_products')->insertGetId([
            'delivery_order_id' => $doId,
            'sale_product_id' => $saleProductId,
            'sale_order_id' => $saleId,
            'qty' => 1,
        ]);

        $pc = $this->makeProductChild($product, $o['serial_no']);
        DB::table('delivery_order_product_children')->insert([
            'delivery_order_product_id' => $dopId,
            'product_children_id' => $pc->id,
        ]);

        return $o['serial_no'];
    }

    public function test_warranty_customer_sort_matches_displayed(): void
    {
        $this->actingAs($this->userWith(['warranty.view']));
        $kw = 'WAR'.uniqid();

        $this->makeWarrantyRow($kw, [
            'customer' => $kw.'-CustZ', 'product_sku' => $kw.'-P1', 'product_model' => $kw.'-MdlA',
            'inv_sku' => $kw.'-INV1', 'inv_created_at' => '2026-01-01 00:00:00',
            'warranty_name' => $kw.'-W1', 'warranty_period' => 12, 'serial_no' => $kw.'-SN1',
        ]);
        $this->makeWarrantyRow($kw, [
            'customer' => $kw.'-CustA', 'product_sku' => $kw.'-P2', 'product_model' => $kw.'-MdlB',
            'inv_sku' => $kw.'-INV2', 'inv_created_at' => '2026-02-01 00:00:00',
            'warranty_name' => $kw.'-W2', 'warranty_period' => 24, 'serial_no' => $kw.'-SN2',
        ]);

        $asc = collect($this->fetch('warranty.get_data', 1, 'asc', ['search' => ['value' => $kw]]))
            ->pluck('customer_name')->all();
        $this->assertSame([$kw.'-CustA', $kw.'-CustZ'], $asc);

        $desc = collect($this->fetch('warranty.get_data', 1, 'desc', ['search' => ['value' => $kw]]))
            ->pluck('customer_name')->all();
        $this->assertSame([$kw.'-CustZ', $kw.'-CustA'], $desc);
    }

    public function test_warranty_date_sort_matches_displayed_value(): void
    {
        $this->actingAs($this->userWith(['warranty.view']));
        $kw = 'WARD'.uniqid();

        // Row A: invoice 2026-01-01 + 24 months => 2028-01-01 (later end date, earlier invoice)
        // Row B: invoice 2026-06-01 + 1 month   => 2026-07-01 (earlier end date, later invoice)
        // Naive sort on invoice date alone would order them differently than the displayed end date.
        $this->makeWarrantyRow($kw, [
            'customer' => $kw.'-Cust1', 'product_sku' => $kw.'-P1', 'product_model' => $kw.'-Mdl1',
            'inv_sku' => $kw.'-INV1', 'inv_created_at' => '2026-01-01 00:00:00',
            'warranty_name' => $kw.'-W1', 'warranty_period' => 24, 'serial_no' => $kw.'-SN1',
        ]);
        $this->makeWarrantyRow($kw, [
            'customer' => $kw.'-Cust2', 'product_sku' => $kw.'-P2', 'product_model' => $kw.'-Mdl2',
            'inv_sku' => $kw.'-INV2', 'inv_created_at' => '2026-06-01 00:00:00',
            'warranty_name' => $kw.'-W2', 'warranty_period' => 1, 'serial_no' => $kw.'-SN2',
        ]);

        $early = Carbon::parse('2026-07-01 00:00:00')->format('d M Y h:i A');
        $late = Carbon::parse('2028-01-01 00:00:00')->format('d M Y h:i A');

        $asc = collect($this->fetch('warranty.get_data', 5, 'asc', ['search' => ['value' => $kw]]))
            ->pluck('warranty_date')->all();
        $this->assertSame([$early, $late], $asc);

        $desc = collect($this->fetch('warranty.get_data', 5, 'desc', ['search' => ['value' => $kw]]))
            ->pluck('warranty_date')->all();
        $this->assertSame([$late, $early], $desc);
    }

    public function test_warranty_invoice_product_serial_warranty_sortable(): void
    {
        $this->actingAs($this->userWith(['warranty.view']));
        $kw = 'WARM'.uniqid();

        $this->makeWarrantyRow($kw, [
            'customer' => $kw.'-C1', 'product_sku' => $kw.'-P1', 'product_model' => $kw.'-MdlZ',
            'inv_sku' => $kw.'-INVB', 'inv_created_at' => '2026-01-01 00:00:00',
            'warranty_name' => $kw.'-WB', 'warranty_period' => 12, 'serial_no' => $kw.'-SNB',
        ]);
        $this->makeWarrantyRow($kw, [
            'customer' => $kw.'-C2', 'product_sku' => $kw.'-P2', 'product_model' => $kw.'-MdlA',
            'inv_sku' => $kw.'-INVA', 'inv_created_at' => '2026-02-01 00:00:00',
            'warranty_name' => $kw.'-WA', 'warranty_period' => 24, 'serial_no' => $kw.'-SNA',
        ]);

        $this->assertSame([$kw.'-INVA', $kw.'-INVB'],
            collect($this->fetch('warranty.get_data', 0, 'asc', ['search' => ['value' => $kw]]))->pluck('invoice_sku')->all());
        $this->assertSame([$kw.'-MdlA', $kw.'-MdlZ'],
            collect($this->fetch('warranty.get_data', 2, 'asc', ['search' => ['value' => $kw]]))->pluck('product_name')->all());
        $this->assertSame([$kw.'-SNA', $kw.'-SNB'],
            collect($this->fetch('warranty.get_data', 3, 'asc', ['search' => ['value' => $kw]]))->pluck('serial_no')->all());
        $this->assertSame([$kw.'-WA', $kw.'-WB'],
            collect($this->fetch('warranty.get_data', 4, 'asc', ['search' => ['value' => $kw]]))->pluck('warranty')->all());
    }
}
