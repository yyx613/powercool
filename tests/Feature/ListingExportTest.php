<?php

namespace Tests\Feature;

use App\Exports\CreditorListingExport;
use App\Exports\DealerListingExport;
use App\Exports\DebtorListingExport;
use App\Exports\Listings\AutoCountListingExport;
use App\Exports\Listings\ListingLayout;
use App\Exports\Listings\ListingRecord;
use App\Models\Customer;
use App\Models\Dealer;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

/**
 * The Debtor / Creditor listing exports clone the AutoCount .xls templates,
 * emitting one record block per filtered customer / supplier. These tests cover
 * the filter pass-through, the model→record mapping, and that the generated
 * workbook keeps the template header while dropping its sample data.
 */
class ListingExportTest extends TestCase
{
    use DatabaseTransactions;

    public function test_debtor_listing_only_maps_records_from_the_filtered_query(): void
    {
        $tag = 'DLST' . uniqid();

        $powercool = Customer::create([
            'name' => 'PC Customer',
            'company_name' => 'PC Cust Co',
            'company_group' => 1,
            'sku' => $tag . '-PC',
        ]);
        $hiten = Customer::create([
            'name' => 'HT Customer',
            'company_name' => 'HT Cust Co',
            'company_group' => 2,
            'sku' => $tag . '-HT',
        ]);

        $query = Customer::where('company_group', 1)->whereIn('sku', [$powercool->sku, $hiten->sku]);
        $codes = array_map(fn (ListingRecord $r) => $r->code, (new DebtorListingExport($query))->records());

        $this->assertContains($powercool->sku, $codes);
        $this->assertNotContains($hiten->sku, $codes);
    }

    public function test_creditor_listing_only_maps_records_from_the_filtered_query(): void
    {
        $tag = 'CLST' . uniqid();

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
        $codes = array_map(fn (ListingRecord $r) => $r->code, (new CreditorListingExport($query))->records());

        $this->assertContains($hiten->sku, $codes);
        $this->assertNotContains($powercool->sku, $codes);
    }

    public function test_generated_workbook_stamps_header_and_drops_sample_records(): void
    {
        $records = [
            new ListingRecord(
                code: '300-T999',
                name: 'TEST DEBTOR SDN BHD',
                addressLines: ['NO 1 JALAN UJIAN', 'TAMAN TEST', '30000 IPOH', 'PERAK'],
                area: 'IPOH',
                agent: 'TESTER',
                terms: 'C.O.D.',
                phones: ['012-3456789'],
            ),
        ];

        $export = new AutoCountListingExport(
            templatePath: resource_path('exports/templates/debtor_listing.xls'),
            layout: ListingLayout::debtor(),
            records: $records,
            dateText: '09-06-2026 10:00:00',
            userId: 'TESTUSER',
            companyName: 'POWER COOL EQUIPMENTS (M) SDN BHD (383045-D)',
        );

        $path = tempnam(sys_get_temp_dir(), 'listing_test') . '.xlsx';
        $export->save($path);

        $sheet = IOFactory::load($path)->getActiveSheet();

        // Header preserved + stamped.
        $this->assertSame('Debtor Listing', $sheet->getCell('E4')->getValue());
        $this->assertSame('09-06-2026 10:00:00', $sheet->getCell('V1')->getValue());
        $this->assertSame('TESTUSER', $sheet->getCell('V3')->getValue());
        // The company title reflects the company passed in, not the template's
        // hardcoded value.
        $this->assertSame('POWER COOL EQUIPMENTS (M) SDN BHD (383045-D)', $sheet->getCell('A7')->getValue());

        // The header rule under the column band is redrawn (the template's shape
        // line is dropped by the .xls reader) and spans the full width.
        $this->assertSame(
            \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
            $sheet->getStyle('A17')->getBorders()->getBottom()->getBorderStyle(),
        );
        $this->assertSame(
            \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
            $sheet->getStyle('X17')->getBorders()->getBottom()->getBorderStyle(),
        );

        // The one record renders into the first block.
        $this->assertSame('300-T999', $sheet->getCell('A19')->getValue());
        $this->assertSame('TEST DEBTOR SDN BHD', $sheet->getCell('B19')->getValue());
        $this->assertSame('IPOH', $sheet->getCell('K20')->getValue());

        // Sample data (and its pre-formatted tail to row ~3499) is gone: with a
        // single record the sheet must end just past the first block.
        $this->assertLessThan(40, $sheet->getHighestRow());

        @unlink($path);
    }

    public function test_dealer_listing_reuses_the_debtor_template_retitled(): void
    {
        $tag = 'DEAL' . uniqid();
        $dealer = Dealer::create([
            'name' => 'Test Dealer',
            'company_name' => 'TEST DEALER SDN BHD',
            'company_group' => 1,
            'sku' => $tag,
        ]);

        $query = Dealer::whereIn('sku', [$dealer->sku]);
        $export = new DealerListingExport($query, 'POWER COOL EQUIPMENTS (M) SDN BHD (383045-D)');

        // download() writes the workbook to a temp file and wraps it in a
        // response; read that file straight back to inspect the contents.
        $response = $export->download('dealer.xlsx');
        $sheet = IOFactory::load($response->getFile()->getPathname())->getActiveSheet();

        // Debtor template, retitled.
        $this->assertSame('Dealer Listing', $sheet->getCell('E4')->getValue());
        $this->assertSame('POWER COOL EQUIPMENTS (M) SDN BHD (383045-D)', $sheet->getCell('A7')->getValue());
        // The dealer maps into the first record block (company name, name fallback).
        $this->assertSame($tag, $sheet->getCell('A19')->getValue());
        $this->assertSame('TEST DEALER SDN BHD', $sheet->getCell('B19')->getValue());

        @unlink($response->getFile()->getPathname());
    }

    public function test_company_title_is_cleared_when_no_company_is_given(): void
    {
        // With no company filter the listing spans both companies, so the
        // template's hardcoded company title must be cleared rather than left
        // showing the wrong company.
        $export = new AutoCountListingExport(
            templatePath: resource_path('exports/templates/creditor_listing.xls'),
            layout: ListingLayout::creditor(),
            records: [new ListingRecord(code: '400-T001', name: 'TEST CREDITOR')],
            companyName: null,
        );

        $path = tempnam(sys_get_temp_dir(), 'listing_test') . '.xlsx';
        $export->save($path);

        $sheet = IOFactory::load($path)->getActiveSheet();
        $this->assertSame('', (string) $sheet->getCell('A6')->getValue());

        @unlink($path);
    }
}
