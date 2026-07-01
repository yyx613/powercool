<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\DeliveryOrder;
use App\Models\DeliveryOrderProduct;
use App\Models\DeliveryOrderProductChild;
use App\Models\DraftEInvoice;
use App\Models\Invoice;
use App\Models\Sale;
use App\Models\SaleProduct;
use App\Models\SaleProductChild;
use App\Models\TransportAcknowledgement;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Session;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role as SpatieRole;
use Tests\TestCase;

/**
 * Covers the new server-side column sorting on the sale-document listings.
 * Each test isolates its rows with a unique keyword and asserts that ascending
 * and descending order of the returned 'data' is correct, proving the sort
 * agrees with the displayed value (membership unchanged, only ordering).
 */
class ListingSortSaleDocsTest extends TestCase
{
    use DatabaseTransactions;

    private function userInKL(array $perms): User
    {
        foreach ($perms as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $role = SpatieRole::firstOrCreate(['name' => 'ListSort Tester '.uniqid(), 'guard_name' => 'web']);
        $role->givePermissionTo($perms);

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

    private function makeSale(string $sku, int $type, ?int $transferFrom = null, ?string $convertTo = null): Sale
    {
        $sale = Sale::create([
            'sku'           => $sku,
            'type'          => $type,
            'transfer_from' => $transferFrom,
            'convert_to'    => $convertTo,
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

    private function addSerials(Sale $so, int $count): SaleProduct
    {
        $sp = SaleProduct::create([
            'sale_id'       => $so->id,
            'product_id'    => 1,
            'qty'           => $count,
            'unit_price'    => 10,
            'discount_type' => 'fixed',
        ]);
        $pcId = \App\Models\ProductChild::value('id') ?? 1;
        for ($i = 0; $i < $count; $i++) {
            SaleProductChild::create(['sale_product_id' => $sp->id, 'product_children_id' => $pcId]);
        }

        return $sp;
    }

    private function convertSerials(DeliveryOrder $do, SaleProduct $sp, int $count): void
    {
        $dop = DeliveryOrderProduct::create([
            'delivery_order_id' => $do->id,
            'sale_product_id'   => $sp->id,
            'qty'               => $count,
        ]);
        $pcId = \App\Models\ProductChild::value('id') ?? 1;
        for ($i = 0; $i < $count; $i++) {
            DeliveryOrderProductChild::create(['delivery_order_product_id' => $dop->id, 'product_children_id' => $pcId]);
        }
    }

    /** Walk every page of the transport-ack listing and return the ordered list of skus. */
    private function collectAllTransportAck(array $order): array
    {
        $skus = [];
        $page = 1;
        do {
            $rows = $this->getJson(route('transport_ack.get_data', [
                'page'  => $page,
                'order' => [$order],
            ]))->assertOk()->json('data');
            foreach ($rows as $row) {
                $skus[] = $row['sku'];
            }
            $page++;
        } while (count($rows) === 10 && $page < 500);

        return $skus;
    }

    // ---------------------------------------------------------------------

    public function test_transport_ack_sorts_by_type(): void
    {
        $user = $this->userInKL(['sale.transport_acknowledgement.view']);
        $this->actingAs($user);
        Session::put('as_branch', Branch::LOCATION_KL);

        $kw = 'TAKSORT'.uniqid();
        // type 2 (Collection) and type 1 (Delivery)
        $takB = TransportAcknowledgement::create(['sku' => $kw.'-B', 'type' => DeliveryOrder::TRANSPORT_ACK_TYPE_COLLECTION, 'filename' => 'a.pdf', 'generated_by' => $user->id]);
        $takA = TransportAcknowledgement::create(['sku' => $kw.'-A', 'type' => DeliveryOrder::TRANSPORT_ACK_TYPE_DELIVERY, 'filename' => 'b.pdf', 'generated_by' => $user->id]);
        // TransportAcknowledgement is branch-scoped, so attach each to the KL branch.
        foreach ([$takB, $takA] as $tak) {
            Branch::create(['object_type' => TransportAcknowledgement::class, 'object_id' => $tak->id, 'location' => Branch::LOCATION_KL]);
        }

        // getDataTransportAck has no keyword filter, so walk all pages and compare the
        // relative position of our two rows (identified by sku).
        $skuA = $kw.'-A'; // Delivery (type 1)
        $skuB = $kw.'-B'; // Collection (type 2)

        $ascRows = $this->collectAllTransportAck(['column' => 5, 'dir' => 'asc']);
        $descRows = $this->collectAllTransportAck(['column' => 5, 'dir' => 'desc']);

        // Ascending by type: Delivery(1) before Collection(2).
        $this->assertLessThan(
            array_search($skuB, $ascRows),
            array_search($skuA, $ascRows)
        );
        // Descending by type: Collection(2) before Delivery(1).
        $this->assertLessThan(
            array_search($skuA, $descRows),
            array_search($skuB, $descRows)
        );
    }

    public function test_sale_order_sorts_by_serial_no_qty(): void
    {
        $this->actingAs($this->userInKL(['sale.sale_order.view']));
        Session::put('as_branch', Branch::LOCATION_KL);

        $kw = 'SOSER'.uniqid();
        $low = $this->makeSale($kw.'-LOW', Sale::TYPE_SO);
        $this->addSerials($low, 1);
        $high = $this->makeSale($kw.'-HIGH', Sale::TYPE_SO);
        $this->addSerials($high, 3);

        $asc = collect($this->getJson(route('sale_order.get_data', [
            'transfer_type' => Sale::TRANSFER_TYPE_NORMAL,
            'search' => ['value' => $kw],
            'order'  => [['column' => 9, 'dir' => 'asc']],
        ]))->assertOk()->json('data'))->pluck('serial_no_qty')->all();
        $desc = collect($this->getJson(route('sale_order.get_data', [
            'transfer_type' => Sale::TRANSFER_TYPE_NORMAL,
            'search' => ['value' => $kw],
            'order'  => [['column' => 9, 'dir' => 'desc']],
        ]))->assertOk()->json('data'))->pluck('serial_no_qty')->all();

        $this->assertSame([1, 3], array_map('intval', $asc));
        $this->assertSame([3, 1], array_map('intval', $desc));
    }

    public function test_sale_order_sorts_by_transfer_from_group_concat(): void
    {
        $this->actingAs($this->userInKL(['sale.sale_order.view']));
        Session::put('as_branch', Branch::LOCATION_KL);

        $kw = 'SOTF'.uniqid();
        $soA = $this->makeSale($kw.'-SOA', Sale::TYPE_SO);
        $soB = $this->makeSale($kw.'-SOB', Sale::TYPE_SO);
        // QUO whose convert_to points at the SO => becomes the SO's "Transfer From".
        $this->makeSale('AAA-'.$kw, Sale::TYPE_QUO, null, (string) $soA->id);
        $this->makeSale('ZZZ-'.$kw, Sale::TYPE_QUO, null, (string) $soB->id);

        $asc = collect($this->getJson(route('sale_order.get_data', [
            'transfer_type' => Sale::TRANSFER_TYPE_NORMAL,
            'search' => ['value' => $kw],
            'order'  => [['column' => 2, 'dir' => 'asc']],
        ]))->assertOk()->json('data'))->pluck('transfer_from')->all();
        $desc = collect($this->getJson(route('sale_order.get_data', [
            'transfer_type' => Sale::TRANSFER_TYPE_NORMAL,
            'search' => ['value' => $kw],
            'order'  => [['column' => 2, 'dir' => 'desc']],
        ]))->assertOk()->json('data'))->pluck('transfer_from')->all();

        $this->assertSame(['AAA-'.$kw, 'ZZZ-'.$kw], $asc);
        $this->assertSame(['ZZZ-'.$kw, 'AAA-'.$kw], $desc);
    }

    public function test_sale_order_sorts_by_converted_qty(): void
    {
        $this->actingAs($this->userInKL(['sale.sale_order.view']));
        Session::put('as_branch', Branch::LOCATION_KL);

        $kw = 'SOCV'.uniqid();
        $few = $this->makeSale($kw.'-FEW', Sale::TYPE_SO);
        $spFew = $this->addSerials($few, 2);
        $many = $this->makeSale($kw.'-MANY', Sale::TYPE_SO);
        $spMany = $this->addSerials($many, 2);

        $custId = $this->makeCustomer()->id;
        $doFew = DeliveryOrder::create(['sku' => 'DO-'.$kw.'-F', 'customer_id' => $custId]);
        Branch::create(['object_type' => DeliveryOrder::class, 'object_id' => $doFew->id, 'location' => Branch::LOCATION_KL]);
        $this->convertSerials($doFew, $spFew, 1);

        $doMany = DeliveryOrder::create(['sku' => 'DO-'.$kw.'-M', 'customer_id' => $custId]);
        Branch::create(['object_type' => DeliveryOrder::class, 'object_id' => $doMany->id, 'location' => Branch::LOCATION_KL]);
        $this->convertSerials($doMany, $spMany, 2);

        $asc = collect($this->getJson(route('sale_order.get_data', [
            'transfer_type' => Sale::TRANSFER_TYPE_NORMAL,
            'search' => ['value' => $kw],
            'order'  => [['column' => 10, 'dir' => 'asc']],
        ]))->assertOk()->json('data'))->pluck('not_converted_serial_no_qty')->all();
        $desc = collect($this->getJson(route('sale_order.get_data', [
            'transfer_type' => Sale::TRANSFER_TYPE_NORMAL,
            'search' => ['value' => $kw],
            'order'  => [['column' => 10, 'dir' => 'desc']],
        ]))->assertOk()->json('data'))->pluck('not_converted_serial_no_qty')->all();

        $this->assertSame([1, 2], array_map('intval', $asc));
        $this->assertSame([2, 1], array_map('intval', $desc));
    }

    /**
     * Smoke-test the remaining new sortable columns: each sort must compile and run
     * against MySQL (correlated GROUP_CONCAT / subquery SQL) without error.
     */
    public function test_other_listing_sorts_execute_without_error(): void
    {
        $this->actingAs($this->userInKL([
            'sale.sale_order.view',
            'sale.quotation.view',
            'sale.delivery_order.view',
            'sale.invoice.view',
            'sale.invoice_return.view',
            'sale.billing.view',
            'sale.target.view',
            'e_order.view',
        ]));
        Session::put('as_branch', Branch::LOCATION_KL);

        $cases = [
            ['sale_order.get_data', ['transfer_type' => Sale::TRANSFER_TYPE_NORMAL], [['column' => 3, 'dir' => 'asc']]], // SO Transfer To
            ['quotation.get_data', [], [['column' => 3, 'dir' => 'asc']]],          // Transfer To
            ['delivery_order.get_data', [], [['column' => 2, 'dir' => 'asc']]],     // Transfer From
            ['delivery_order.get_data', [], [['column' => 8, 'dir' => 'desc']]],    // Total
            ['invoice.get_data', [], [['column' => 3, 'dir' => 'asc']]],            // Transfer From
            ['invoice.get_data', [], [['column' => 8, 'dir' => 'desc']]],           // Total
            ['invoice.get_data', ['is_return' => 'true'], [['column' => 2, 'dir' => 'asc']]],  // return Transfer From
            ['invoice.get_data', ['is_return' => 'true'], [['column' => 7, 'dir' => 'desc']]], // return Total
            ['billing.get_data', [], [['column' => 3, 'dir' => 'asc']]],            // D/O No
            ['billing.get_data', [], [['column' => 4, 'dir' => 'asc']]],            // Invoice No
            ['billing.get_data', [], [['column' => 5, 'dir' => 'asc']]],            // Debtor Code
            ['billing.get_data', [], [['column' => 6, 'dir' => 'asc']]],            // Debtor Name
            ['billing.get_data', [], [['column' => 7, 'dir' => 'desc']]],           // Total
            ['billing.get_data', [], [['column' => 8, 'dir' => 'asc']]],            // Status
            ['billing.get_data', [], [['column' => 9, 'dir' => 'asc']]],            // Created User
            ['invoice.get_data_e-invoice', [], [['column' => 0, 'dir' => 'asc']]],  // e-invoice UUID
            ['invoice.get_data_e-invoice', [], [['column' => 3, 'dir' => 'asc']]],  // e-invoice Invoice Date
            ['invoice.get_data_e-invoice', [], [['column' => 4, 'dir' => 'asc']]],  // e-invoice Status
            ['invoice.get_data_e-invoice', [], [['column' => 5, 'dir' => 'asc']]],  // e-invoice From
            ['invoice.get_data_e-invoice', [], [['column' => 6, 'dir' => 'desc']]], // e-invoice Submission Date
            ['sale_cancellation.get_data', [], [['column' => 0, 'dir' => 'asc']]],  // cancellation SO/INV
            ['sale_cancellation.get_data', [], [['column' => 4, 'dir' => 'asc']]],  // cancellation Debtor
            ['sale_cancellation.get_data', [], [['column' => 5, 'dir' => 'desc']]], // cancellation Qty
            ['pending_order.get_data', [], [['column' => 2, 'dir' => 'desc']]],     // pending Total Amount
            ['pending_order.get_data', [], [['column' => 3, 'dir' => 'asc']]],      // pending Platform
        ];

        foreach ($cases as [$route, $extra, $order]) {
            $this->getJson(route($route, array_merge($extra, ['order' => $order])))
                ->assertOk();
        }
    }

    /** Collect a field from every page of the transport-ack listing, in returned order. */
    private function collectAllTransportAckField(array $order, string $field): array
    {
        $vals = [];
        $page = 1;
        do {
            $rows = $this->getJson(route('transport_ack.get_data', [
                'page'  => $page,
                'order' => [$order],
            ]))->assertOk()->json('data');
            foreach ($rows as $row) {
                $vals[] = $row[$field];
            }
            $page++;
        } while (count($rows) === 10 && $page < 500);

        return $vals;
    }

    public function test_transport_ack_sorts_by_dealer_label(): void
    {
        $user = $this->userInKL(['sale.transport_acknowledgement.view']);
        $this->actingAs($user);
        Session::put('as_branch', Branch::LOCATION_KL);

        $kw = 'TAKDLR'.uniqid();
        // dealer_id -1 => "Powercool", -2 => "Hi-Ten" (hardcoded labels in dealerLabel()).
        $pc = TransportAcknowledgement::create(['sku' => $kw.'-PC', 'dealer_id' => -1, 'type' => DeliveryOrder::TRANSPORT_ACK_TYPE_DELIVERY, 'filename' => 'a.pdf', 'generated_by' => $user->id]);
        $ht = TransportAcknowledgement::create(['sku' => $kw.'-HT', 'dealer_id' => -2, 'type' => DeliveryOrder::TRANSPORT_ACK_TYPE_DELIVERY, 'filename' => 'b.pdf', 'generated_by' => $user->id]);
        foreach ([$pc, $ht] as $tak) {
            Branch::create(['object_type' => TransportAcknowledgement::class, 'object_id' => $tak->id, 'location' => Branch::LOCATION_KL]);
        }

        $asc = $this->collectAllTransportAckField(['column' => 4, 'dir' => 'asc'], 'sku');
        // "Hi-Ten" < "Powercool" alphabetically, so the -2 row comes first ascending.
        $this->assertLessThan(array_search($kw.'-PC', $asc), array_search($kw.'-HT', $asc));

        $desc = $this->collectAllTransportAckField(['column' => 4, 'dir' => 'desc'], 'sku');
        $this->assertLessThan(array_search($kw.'-HT', $desc), array_search($kw.'-PC', $desc));
    }

    public function test_transport_ack_no_column_sorts_by_id(): void
    {
        $user = $this->userInKL(['sale.transport_acknowledgement.view']);
        $this->actingAs($user);
        Session::put('as_branch', Branch::LOCATION_KL);

        $kw = 'TAKNO'.uniqid();
        $first = TransportAcknowledgement::create(['sku' => $kw.'-1', 'type' => DeliveryOrder::TRANSPORT_ACK_TYPE_DELIVERY, 'filename' => 'a.pdf', 'generated_by' => $user->id]);
        $second = TransportAcknowledgement::create(['sku' => $kw.'-2', 'type' => DeliveryOrder::TRANSPORT_ACK_TYPE_DELIVERY, 'filename' => 'b.pdf', 'generated_by' => $user->id]);
        foreach ([$first, $second] as $tak) {
            Branch::create(['object_type' => TransportAcknowledgement::class, 'object_id' => $tak->id, 'location' => Branch::LOCATION_KL]);
        }

        // "No." sorts by id: ascending => earlier-created (smaller id) first.
        $asc = $this->collectAllTransportAckField(['column' => 0, 'dir' => 'asc'], 'sku');
        $this->assertLessThan(array_search($kw.'-2', $asc), array_search($kw.'-1', $asc));

        $desc = $this->collectAllTransportAckField(['column' => 0, 'dir' => 'desc'], 'sku');
        $this->assertLessThan(array_search($kw.'-1', $desc), array_search($kw.'-2', $desc));
    }

    /** Build a draft e-invoice whose total resolves to $qty * 10 (single SO, single product). */
    private function makeDraftWithTotal(string $invoiceSku, int $qty): DraftEInvoice
    {
        $cust = $this->makeCustomer();
        $invoice = Invoice::create(['sku' => $invoiceSku, 'filename' => 'inv.pdf', 'date' => now()]);

        $so = $this->makeSale('SO-'.$invoiceSku, Sale::TYPE_SO);
        $do = DeliveryOrder::create([
            'sku'         => 'DO-'.$invoiceSku,
            'customer_id' => $cust->id,
            'sale_id'     => $so->id,
            'filename'    => 'do.pdf',
            'invoice_id'  => $invoice->id,
            'status'      => DeliveryOrder::STATUS_CONVERTED,
        ]);
        Branch::create(['object_type' => DeliveryOrder::class, 'object_id' => $do->id, 'location' => Branch::LOCATION_KL]);
        $so->update(['convert_to' => (string) $do->id]);

        SaleProduct::create([
            'sale_id'       => $so->id,
            'product_id'    => 1,
            'qty'           => $qty,
            'unit_price'    => 10,
            'discount_type' => 'fixed',
        ]);

        return DraftEInvoice::create(['invoice_id' => $invoice->id]);
    }

    /**
     * Walk every page of the draft e-invoice listing (no keyword search — that path has a
     * pre-existing unrelated `users.name` bug), returning [transfer_from => total] in order.
     */
    private function collectDraftTotals(array $order, string $kw): array
    {
        $totals = [];
        $page = 1;
        do {
            $rows = $this->getJson(route('invoice.get_data_draft_e_invoice', [
                'page'  => $page,
                'order' => [$order],
            ]))->assertOk()->json('data');
            foreach ($rows as $row) {
                if (str_contains((string) $row['transfer_from'], $kw)) {
                    $totals[] = $row['total'];
                }
            }
            $page++;
        } while (count($rows) === 10 && $page < 500);

        return $totals;
    }

    public function test_draft_e_invoice_sorts_by_total(): void
    {
        $this->actingAs($this->userInKL(['sale.invoice.view']));
        Session::put('as_branch', Branch::LOCATION_KL);

        $kw = 'DFT'.uniqid();
        $this->makeDraftWithTotal($kw.'-HIGH', 3); // total 30.00
        $this->makeDraftWithTotal($kw.'-LOW', 1);  // total 10.00

        $this->assertSame(['10.00', '30.00'], $this->collectDraftTotals(['column' => 7, 'dir' => 'asc'], $kw));
        $this->assertSame(['30.00', '10.00'], $this->collectDraftTotals(['column' => 7, 'dir' => 'desc'], $kw));
    }
}
