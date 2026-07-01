<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Product;
use App\Models\Production;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role as SpatieRole;
use Tests\TestCase;

/**
 * Server-side sort coverage for the report listings:
 *  - Production Report  (getDataProduction)
 *  - Sales Report       (getDataSales)
 *  - Earning Report     (getDataEarning)
 *  - Stock Card         (getDataStockCard, finished good)  + materials (getDataStock)
 *
 * Each report is smoke-tested 200 on an order for every data-column index, plus
 * faithful asc/desc assertions on representative columns where a naive sort key
 * would disagree with the displayed cell value.
 *
 * Faithful tests run against the shared dev DB, so fixtures are stamped with a unique
 * far-future created_at date and the report is filtered to that single date — this
 * isolates the assertion to only the rows created by the test.
 */
class ListingSortReportsTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * A date no real record uses (and within MySQL TIMESTAMP range, max 2038-01-19),
     * so a same-day range returns only this test's rows.
     */
    private string $isoDate = '2037-12-31';

    private function stamp(): string
    {
        return $this->isoDate.' 10:00:00';
    }

    /** Force created_at via raw update so Eloquent's auto-timestamps don't override it. */
    private function stampRow(string $table, int $id): void
    {
        DB::table($table)->where('id', $id)->update(['created_at' => $this->stamp()]);
    }

    /** Branchless user (no Branch row) so BranchScope does not filter fixtures. */
    private function userWith(array $perms): User
    {
        foreach ($perms as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }
        $role = SpatieRole::firstOrCreate(['name' => 'Report Sort Tester', 'guard_name' => 'web']);
        $role->givePermissionTo($perms);

        $user = User::factory()->create();
        $user->assignRole($role);

        return $user->fresh(['roles']);
    }

    private function makeProduct(array $attrs = []): Product
    {
        $categoryId = DB::table('inventory_categories')->value('id');
        if ($categoryId === null) {
            $categoryId = DB::table('inventory_categories')->insertGetId(['name' => 'Cat '.uniqid()]);
        }

        return Product::create(array_merge([
            'inventory_category_id' => $categoryId,
            'type' => Product::TYPE_PRODUCT,
            'sku' => 'SKU-'.uniqid(),
            'model_desc' => 'Desc '.uniqid(),
            'cost' => 0,
        ], $attrs));
    }

    /** Order params, optionally scoped to the unique fixture date. */
    private function order(int $column, string $dir, bool $scoped = false): array
    {
        return [
            'order' => [['column' => $column, 'dir' => $dir]],
            'start_date' => $scoped ? $this->isoDate : 'null',
            'end_date' => $scoped ? $this->isoDate : 'null',
        ];
    }

    // ---------------------------------------------------------------- Production

    public function test_production_every_column_sorts_200(): void
    {
        $this->actingAs($this->userWith(['report.production']));

        foreach ([0, 1] as $col) {
            foreach (['asc', 'desc'] as $dir) {
                $this->getJson(route('report.production_report.get_data', $this->order($col, $dir)))
                    ->assertOk();
            }
        }
    }

    public function test_production_sorts_by_product_name_and_code_faithfully(): void
    {
        $this->actingAs($this->userWith(['report.production']));

        // model_desc alpha order is the REVERSE of insertion (id) order, so a naive
        // id-based key would disagree with the displayed Product Name column.
        $pZ = $this->makeProduct(['model_desc' => 'ZZZ Compressor', 'sku' => 'AAA-001']);
        $pA = $this->makeProduct(['model_desc' => 'AAA Blower', 'sku' => 'ZZZ-999']);

        $prodZ = Production::create(['product_id' => $pZ->id, 'sku' => 'PO-'.uniqid()]);
        $prodA = Production::create(['product_id' => $pA->id, 'sku' => 'PO-'.uniqid()]);
        $this->stampRow('productions', $prodZ->id);
        $this->stampRow('productions', $prodA->id);

        $names = collect($this->getJson(route('report.production_report.get_data', $this->order(0, 'asc', true)))
            ->assertOk()->json('data'))->pluck('product_name');
        $this->assertSame(['AAA Blower', 'ZZZ Compressor'], $names->values()->all());

        $codes = collect($this->getJson(route('report.production_report.get_data', $this->order(1, 'asc', true)))
            ->assertOk()->json('data'))->pluck('product_code');
        $this->assertSame(['AAA-001', 'ZZZ-999'], $codes->values()->all());
    }

    // -------------------------------------------------------------------- Sales

    /** Create an SO (stamped at the fixture date) with one line and a sales agent. */
    private function makeSale(string $agentName, int $qty, float $unitPrice, float $cost = 0): Sale
    {
        $agentId = DB::table('sales_agents')->insertGetId(['name' => $agentName]);
        $product = $this->makeProduct();

        $sale = Sale::create([
            'sku' => 'SO-'.uniqid(),
            'type' => Sale::TYPE_SO,
            'sale_id' => $agentId,
            'status' => Sale::STATUS_ACTIVE,
            'is_draft' => 0,
        ]);
        $this->stampRow('sales', $sale->id);

        DB::table('sale_products')->insert([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'qty' => $qty,
            'unit_price' => $unitPrice,
            'cost' => $cost,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Sales report inner-joins sale_payment_amounts, so each SO needs a payment row.
        DB::table('sale_payment_amounts')->insert([
            'sale_id' => $sale->id,
            'amount' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $sale;
    }

    public function test_sales_every_column_sorts_200(): void
    {
        $this->actingAs($this->userWith(['report.sales']));
        $this->makeSale('Agent Smith', 3, 100);

        foreach ([0, 1, 2, 3, 4] as $col) {
            foreach (['asc', 'desc'] as $dir) {
                $this->getJson(route('report.sales_report.get_data', $this->order($col, $dir)))
                    ->assertOk();
            }
        }
    }

    public function test_sales_sorts_by_amount_faithfully(): void
    {
        $this->actingAs($this->userWith(['report.sales']));

        // High-amount agent inserted FIRST (lower id) so a naive id key disagrees.
        $this->makeSale('AAA Big', 10, 100);  // amount 1,000.00
        $this->makeSale('BBB Small', 1, 50);  // amount 50.00

        $asc = collect($this->getJson(route('report.sales_report.get_data', $this->order(3, 'asc', true)))
            ->assertOk()->json('data'))->pluck('amount')->values()->all();
        $this->assertSame(['50.00', '1,000.00'], $asc);

        $desc = collect($this->getJson(route('report.sales_report.get_data', $this->order(3, 'desc', true)))
            ->assertOk()->json('data'))->pluck('amount')->values()->all();
        $this->assertSame(['1,000.00', '50.00'], $desc);
    }

    public function test_sales_sorts_by_salesperson_faithfully(): void
    {
        $this->actingAs($this->userWith(['report.sales']));
        $this->makeSale('Zeta Agent', 1, 10);
        $this->makeSale('Alpha Agent', 1, 10);

        $names = collect($this->getJson(route('report.sales_report.get_data', $this->order(0, 'asc', true)))
            ->assertOk()->json('data'))->pluck('salesperson')->values()->all();
        $this->assertSame(['Alpha Agent', 'Zeta Agent'], $names);
    }

    // ------------------------------------------------------------------ Earning

    private function makeEarningSale(string $desc, string $sku, int $qty, float $unitPrice, float $cost): void
    {
        $product = $this->makeProduct(['model_desc' => $desc, 'sku' => $sku]);
        $sale = Sale::create([
            'sku' => 'SO-'.uniqid(),
            'type' => Sale::TYPE_SO,
            'status' => Sale::STATUS_ACTIVE,
            'is_draft' => 0,
        ]);
        $this->stampRow('sales', $sale->id);
        DB::table('sale_products')->insert([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'qty' => $qty,
            'unit_price' => $unitPrice,
            'cost' => $cost,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_earning_every_column_sorts_200(): void
    {
        $this->actingAs($this->userWith(['report.earning']));
        $this->makeEarningSale('Prod', 'SKU-E1', 2, 100, 50);

        foreach ([0, 1, 2, 3, 4] as $col) {
            foreach (['asc', 'desc'] as $dir) {
                $this->getJson(route('report.earning_report.get_data', $this->order($col, $dir)))
                    ->assertOk();
            }
        }
    }

    public function test_earning_sorts_by_sales_and_earning_faithfully(): void
    {
        $this->actingAs($this->userWith(['report.earning']));

        // Large: sales = 1*200 - 0 = 200 ; cost 0 ; earning 200
        // Small: sales = 1*30 - 0 = 30 ; cost 0 ; earning 30
        // Insert the larger one first (lower id) so a naive id sort disagrees.
        $this->makeEarningSale('Large', 'SKU-EL-'.uniqid(), 1, 200, 0);
        $this->makeEarningSale('Small', 'SKU-ES-'.uniqid(), 1, 30, 0);

        $salesAsc = collect($this->getJson(route('report.earning_report.get_data', $this->order(2, 'asc', true)))
            ->assertOk()->json('data'))->pluck('sales')->values()->all();
        $this->assertSame(['30.00', '200.00'], $salesAsc);

        $earningDesc = collect($this->getJson(route('report.earning_report.get_data', $this->order(4, 'desc', true)))
            ->assertOk()->json('data'))->pluck('earning')->values()->all();
        $this->assertSame(['200.00', '30.00'], $earningDesc);
    }

    // --------------------------------------------------------------- Stock Card

    /**
     * A GRN row (stamped at the fixture date) produces an inbound movement. The Stock
     * Card is date-filtered, so a same-day range isolates these fixtures.
     */
    private function makeGrnProduct(int $type, string $desc, string $sku, int $qty, float $totalPrice): Product
    {
        $product = $this->makeProduct(['type' => $type, 'model_desc' => $desc, 'sku' => $sku]);
        $supplierId = DB::table('suppliers')->value('id')
            ?? DB::table('suppliers')->insertGetId(['name' => 'Sup '.uniqid(), 'created_at' => now(), 'updated_at' => now()]);

        DB::table('grn')->insert([
            'product_id' => $product->id,
            'supplier_id' => $supplierId,
            'sku' => 'GR-'.uniqid(),
            'qty' => $qty,
            'unit_price' => $qty > 0 ? $totalPrice / $qty : 0,
            'total_price' => $totalPrice,
            'created_at' => $this->stamp(),
            'updated_at' => $this->stamp(),
        ]);

        return $product;
    }

    /** Stock card order params scoped to a same-day range around the fixture date. */
    private function stockOrder(int $column, string $dir, bool $scoped = false): array
    {
        return [
            'order' => [['column' => $column, 'dir' => $dir]],
            'start_date' => $scoped ? $this->isoDate : 'null',
            'end_date' => $scoped ? $this->isoDate : 'null',
        ];
    }

    public function test_stock_card_every_column_sorts_200(): void
    {
        $this->actingAs($this->userWith(['report.stock_card']));
        $this->makeGrnProduct(Product::TYPE_PRODUCT, 'FG One', 'FG-1', 5, 500);

        for ($col = 0; $col <= 12; $col++) {
            foreach (['asc', 'desc'] as $dir) {
                $this->getJson(route('report.stock_card_report.get_data', $this->stockOrder($col, $dir)))
                    ->assertOk();
            }
        }
    }

    public function test_stock_card_sorts_by_in_qty_and_code_faithfully(): void
    {
        $this->actingAs($this->userWith(['report.stock_card']));

        // Big-qty product has alpha-LATER sku, inserted first => naive sku/id key disagrees with in_qty key.
        $this->makeGrnProduct(Product::TYPE_PRODUCT, 'Big FG', 'FG-ZZZ-'.uniqid(), 100, 1000);
        $this->makeGrnProduct(Product::TYPE_PRODUCT, 'Small FG', 'FG-AAA-'.uniqid(), 2, 20);

        $inQty = collect($this->getJson(route('report.stock_card_report.get_data', $this->stockOrder(6, 'asc', true)))
            ->assertOk()->json('data'))->pluck('in_qty')->values()->all();
        $this->assertSame([2, 100], $inQty);

        $inQtyDesc = collect($this->getJson(route('report.stock_card_report.get_data', $this->stockOrder(6, 'desc', true)))
            ->assertOk()->json('data'))->pluck('in_qty')->values()->all();
        $this->assertSame([100, 2], $inQtyDesc);
    }

    public function test_stock_card_materials_every_column_sorts_200(): void
    {
        $this->actingAs($this->userWith(['report.stock']));
        $this->makeGrnProduct(Product::TYPE_RAW_MATERIAL, 'Raw One', 'RM-1', 5, 500);

        for ($col = 0; $col <= 12; $col++) {
            foreach (['asc', 'desc'] as $dir) {
                $this->getJson(route('report.stock_report.get_data', $this->stockOrder($col, $dir)))
                    ->assertOk();
            }
        }
    }

    public function test_stock_card_materials_sorts_by_in_qty_faithfully(): void
    {
        $this->actingAs($this->userWith(['report.stock']));
        $this->makeGrnProduct(Product::TYPE_RAW_MATERIAL, 'Big RM', 'RM-ZZZ-'.uniqid(), 80, 800);
        $this->makeGrnProduct(Product::TYPE_RAW_MATERIAL, 'Small RM', 'RM-AAA-'.uniqid(), 3, 30);

        $inQty = collect($this->getJson(route('report.stock_report.get_data', $this->stockOrder(6, 'asc', true)))
            ->assertOk()->json('data'))->pluck('in_qty')->values()->all();
        $this->assertSame([3, 80], $inQty);
    }
}
