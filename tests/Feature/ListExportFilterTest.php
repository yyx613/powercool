<?php

namespace Tests\Feature;

use App\Exports\CustomerExport;
use App\Exports\DealerExport;
use App\Exports\SupplierExport;
use App\Models\Customer;
use App\Models\Dealer;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Debtor / supplier / dealer exports must honour the same filters as their
 * list views. Each Export now receives a pre-filtered query (built from the
 * shared applyFilters() used by getData), so feeding it a filtered query must
 * yield only the matching records.
 */
class ListExportFilterTest extends TestCase
{
    use DatabaseTransactions;

    public function test_dealer_export_only_contains_records_from_the_filtered_query(): void
    {
        $tag = 'DEXP' . uniqid();

        $powercool = Dealer::create([
            'name' => 'PC Dealer',
            'company_name' => 'PC Co',
            'company_group' => 1,
            'sku' => $tag . '-PC',
        ]);
        $hiten = Dealer::create([
            'name' => 'HT Dealer',
            'company_name' => 'HT Co',
            'company_group' => 2,
            'sku' => $tag . '-HT',
        ]);

        $query = Dealer::where('company_group', 1)->whereIn('sku', [$powercool->sku, $hiten->sku]);
        $dealers = (new DealerExport($query))->view()->getData()['dealers'];

        $skus = $dealers->pluck('sku');
        $this->assertContains($powercool->sku, $skus);
        $this->assertNotContains($hiten->sku, $skus);
    }

    public function test_supplier_export_only_contains_records_from_the_filtered_query(): void
    {
        $tag = 'SEXP' . uniqid();

        $powercool = Supplier::create([
            'name' => 'PC Supplier',
            'company_name' => 'PC Supply Co',
            'company_group' => 1,
            'sku' => $tag . '-PC',
        ]);
        $hiten = Supplier::create([
            'name' => 'HT Supplier',
            'company_name' => 'HT Supply Co',
            'company_group' => 2,
            'sku' => $tag . '-HT',
        ]);

        $query = Supplier::where('company_group', 2)->whereIn('sku', [$powercool->sku, $hiten->sku]);
        $suppliers = (new SupplierExport($query))->view()->getData()['suppliers'];

        $skus = $suppliers->pluck('sku');
        $this->assertContains($hiten->sku, $skus);
        $this->assertNotContains($powercool->sku, $skus);
    }

    public function test_customer_export_only_contains_records_from_the_filtered_query(): void
    {
        $tag = 'CEXP' . uniqid();

        $powercool = Customer::create([
            'name' => 'PC Customer',
            'phone' => '0123456789',
            'company_name' => 'PC Cust Co',
            'company_group' => 1,
            'sku' => $tag . '-PC',
        ]);
        $hiten = Customer::create([
            'name' => 'HT Customer',
            'phone' => '0123456780',
            'company_name' => 'HT Cust Co',
            'company_group' => 2,
            'sku' => $tag . '-HT',
        ]);

        $query = Customer::where('company_group', 1)->whereIn('sku', [$powercool->sku, $hiten->sku]);
        $customers = (new CustomerExport($query))->view()->getData()['customers'];

        $skus = $customers->pluck('sku');
        $this->assertContains($powercool->sku, $skus);
        $this->assertNotContains($hiten->sku, $skus);
    }

    public function test_export_without_a_query_falls_back_to_all_records(): void
    {
        // The no-arg constructor preserves the previous "export everything"
        // behaviour, so callers that do not pass a query are unaffected.
        $tag = 'DALL' . uniqid();
        $dealer = Dealer::create([
            'name' => 'Fallback Dealer',
            'company_name' => 'Fallback Co',
            'company_group' => 1,
            'sku' => $tag,
        ]);

        $dealers = (new DealerExport)->view()->getData()['dealers'];

        $this->assertContains($dealer->sku, $dealers->pluck('sku'));
    }
}
