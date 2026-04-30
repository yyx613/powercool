<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\InventoryCategory;
use App\Models\InventoryType;
use App\Models\Product;
use App\Models\ProductCost;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use Tests\TestCase;

class ReimportRawMaterialsCommandTest extends TestCase
{
    use DatabaseTransactions;

    private const COMPANY_GROUP_POWERCOOL = 1;

    /** @var string|null Path to the fixture xlsx written in setUp. */
    private ?string $fixturePath = null;

    protected function tearDown(): void
    {
        if ($this->fixturePath !== null && File::exists($this->fixturePath)) {
            File::delete($this->fixturePath);
        }
        parent::tearDown();
    }

    public function test_fresh_import_creates_products_with_correct_sparepart_split(): void
    {
        $this->ensureSeedReferenceData();
        $path = $this->writeFixture($this->sampleRows());

        $beforeType2 = Product::withoutGlobalScopes()->where('type', Product::TYPE_RAW_MATERIAL)->count();

        Artisan::call('products:reimport-raw-materials', [
            'path' => $path,
            '--skip-photos' => true,
        ]);

        $afterType2 = Product::withoutGlobalScopes()->where('type', Product::TYPE_RAW_MATERIAL)->count();
        // Fixture has 10 rows; all existing type=2 were wiped first.
        $this->assertSame(10, $afterType2, 'Expected 10 raw-material rows after import, before was '.$beforeType2);

        $sparepart = Product::withoutGlobalScopes()
            ->where('type', Product::TYPE_RAW_MATERIAL)
            ->where('is_sparepart', true)
            ->count();
        $this->assertSame(5, $sparepart, 'Expected 5 spare parts');

        $rawOnly = Product::withoutGlobalScopes()
            ->where('type', Product::TYPE_RAW_MATERIAL)
            ->where('is_sparepart', false)
            ->count();
        $this->assertSame(5, $rawOnly, 'Expected 5 raw-materials-only');
    }

    public function test_existing_type2_rows_are_purged_before_insert(): void
    {
        $this->ensureSeedReferenceData();
        $cat = InventoryCategory::withoutGlobalScopes()->where('company_group', self::COMPANY_GROUP_POWERCOOL)->first();

        $stale = Product::create([
            'inventory_category_id' => $cat->id,
            'type' => Product::TYPE_RAW_MATERIAL,
            'sku' => 'STALE-PRE-IMPORT-'.uniqid(),
            'model_desc' => 'Will be wiped',
            'is_active' => true,
            'is_sparepart' => false,
            'company_group' => self::COMPANY_GROUP_POWERCOOL,
        ]);
        (new Branch)->assign(Product::class, $stale->id, Branch::LOCATION_KL);

        $path = $this->writeFixture($this->sampleRows());

        Artisan::call('products:reimport-raw-materials', [
            'path' => $path,
            '--skip-photos' => true,
        ]);

        $this->assertNull(
            Product::withoutGlobalScopes()->find($stale->id),
            'Stale raw-material row should have been hard-deleted'
        );
    }

    public function test_finished_products_are_untouched(): void
    {
        $this->ensureSeedReferenceData();
        $cat = InventoryCategory::withoutGlobalScopes()->where('company_group', self::COMPANY_GROUP_POWERCOOL)->first();

        $fg = Product::create([
            'inventory_category_id' => $cat->id,
            'type' => Product::TYPE_PRODUCT,
            'sku' => 'FG-KEEP-'.uniqid(),
            'model_desc' => 'Finished good',
            'is_active' => true,
            'company_group' => self::COMPANY_GROUP_POWERCOOL,
        ]);
        (new Branch)->assign(Product::class, $fg->id, Branch::LOCATION_KL);

        $path = $this->writeFixture($this->sampleRows());

        Artisan::call('products:reimport-raw-materials', [
            'path' => $path,
            '--skip-photos' => true,
        ]);

        $this->assertNotNull(
            Product::withoutGlobalScopes()->find($fg->id),
            'type=1 finished product must not be deleted'
        );
    }

    public function test_supplier_name_is_trimmed_and_deduped(): void
    {
        $this->ensureSeedReferenceData();
        $path = $this->writeFixture($this->sampleRows());

        Artisan::call('products:reimport-raw-materials', [
            'path' => $path,
            '--skip-photos' => true,
        ]);

        $byTrimmed = Product::withoutGlobalScopes()->where('sku', 'TEST-SP-MIA-TRIM')->first();
        $byClean = Product::withoutGlobalScopes()->where('sku', 'TEST-SP-MIA')->first();

        $this->assertNotNull($byTrimmed);
        $this->assertNotNull($byClean);
        $this->assertSame(
            $byTrimmed->supplier_id,
            $byClean->supplier_id,
            '"MIA " with trailing space must resolve to the same supplier as "MIA"'
        );
        $this->assertNotNull($byTrimmed->supplier_id);
    }

    public function test_blank_supplier_results_in_null_supplier_id(): void
    {
        $this->ensureSeedReferenceData();
        $path = $this->writeFixture($this->sampleRows());

        Artisan::call('products:reimport-raw-materials', [
            'path' => $path,
            '--skip-photos' => true,
        ]);

        $noSupplier = Product::withoutGlobalScopes()->where('sku', 'TEST-RM-NOSUP')->first();
        $this->assertNotNull($noSupplier, 'Row with blank supplier should still be inserted');
        $this->assertNull($noSupplier->supplier_id);
    }

    public function test_missing_category_is_auto_created(): void
    {
        $this->ensureSeedReferenceData();
        $missingName = 'BRAND-NEW-CAT-'.uniqid();
        $rows = $this->sampleRows();
        $rows[0]['category'] = $missingName;

        $path = $this->writeFixture($rows);

        Artisan::call('products:reimport-raw-materials', [
            'path' => $path,
            '--skip-photos' => true,
        ]);

        $cat = InventoryCategory::withoutGlobalScopes()
            ->where('name', $missingName)
            ->where('company_group', self::COMPANY_GROUP_POWERCOOL)
            ->first();

        $this->assertNotNull($cat, 'New category should have been auto-created');
    }

    public function test_cost_creates_product_costs_row(): void
    {
        $this->ensureSeedReferenceData();
        $path = $this->writeFixture($this->sampleRows());

        Artisan::call('products:reimport-raw-materials', [
            'path' => $path,
            '--skip-photos' => true,
        ]);

        $prod = Product::withoutGlobalScopes()->where('sku', 'TEST-SP-COST')->first();
        $this->assertNotNull($prod);

        $cost = ProductCost::where('product_id', $prod->id)->first();
        $this->assertNotNull($cost, 'Expected a product_costs audit row');
        $this->assertEquals(12.5, (float) $cost->unit_price);
    }

    public function test_dry_run_does_not_persist_any_writes(): void
    {
        $this->ensureSeedReferenceData();
        $path = $this->writeFixture($this->sampleRows());

        $beforeCount = Product::withoutGlobalScopes()
            ->where('type', Product::TYPE_RAW_MATERIAL)
            ->count();

        Artisan::call('products:reimport-raw-materials', [
            'path' => $path,
            '--dry-run' => true,
            '--skip-photos' => true,
        ]);

        $afterCount = Product::withoutGlobalScopes()
            ->where('type', Product::TYPE_RAW_MATERIAL)
            ->count();

        $this->assertSame($beforeCount, $afterCount, 'Dry run must not change DB state');
    }

    public function test_skip_photos_does_not_invoke_photo_seeder(): void
    {
        $this->ensureSeedReferenceData();
        $path = $this->writeFixture($this->sampleRows());

        $exitCode = Artisan::call('products:reimport-raw-materials', [
            'path' => $path,
            '--skip-photos' => true,
        ]);
        $output = Artisan::output();

        $this->assertSame(0, $exitCode);
        $this->assertStringNotContainsString('Spare Part Photo Seeder', $output);
        $this->assertStringNotContainsString('SparePartPhotoSeeder', $output);
    }

    // ─────────────────────────── Helpers ───────────────────────────

    /**
     * Ensure at least one InventoryCategory / Supplier / InventoryType exists
     * so the command has lookup targets. Scoped to PowerCool (company_group=1).
     */
    private function ensureSeedReferenceData(): void
    {
        if (InventoryCategory::withoutGlobalScopes()
            ->where('company_group', self::COMPANY_GROUP_POWERCOOL)
            ->where('name', 'TEST-CAT')
            ->doesntExist()
        ) {
            $cat = InventoryCategory::create([
                'name' => 'TEST-CAT',
                'is_active' => true,
                'company_group' => self::COMPANY_GROUP_POWERCOOL,
            ]);
            foreach ([Branch::LOCATION_KL, Branch::LOCATION_PENANG] as $loc) {
                Branch::create([
                    'object_type' => InventoryCategory::class,
                    'object_id' => $cat->id,
                    'location' => $loc,
                ]);
            }
        }

        if (Supplier::withoutGlobalScopes()
            ->where('name', 'MIA')
            ->where('company_group', self::COMPANY_GROUP_POWERCOOL)
            ->doesntExist()
        ) {
            $sup = Supplier::create([
                'name' => 'MIA',
                'phone' => '000',
                'is_active' => true,
                'company_group' => self::COMPANY_GROUP_POWERCOOL,
            ]);
            foreach ([Branch::LOCATION_KL, Branch::LOCATION_PENANG] as $loc) {
                Branch::create([
                    'object_type' => Supplier::class,
                    'object_id' => $sup->id,
                    'location' => $loc,
                ]);
            }
        }

        if (InventoryType::withoutGlobalScopes()
            ->where('name', 'MSP')
            ->where('company_group', self::COMPANY_GROUP_POWERCOOL)
            ->doesntExist()
        ) {
            $it = InventoryType::create([
                'name' => 'MSP',
                'is_active' => true,
                'company_group' => self::COMPANY_GROUP_POWERCOOL,
                'type' => InventoryType::TYPE_RAW_MATERIAL,
            ]);
            foreach ([Branch::LOCATION_KL, Branch::LOCATION_PENANG] as $loc) {
                Branch::create([
                    'object_type' => InventoryType::class,
                    'object_id' => $it->id,
                    'location' => $loc,
                ]);
            }
        }
    }

    /**
     * @return array<int, array<string, mixed>> 10 fixture rows: 5 SP + 5 RAW MAT.
     */
    private function sampleRows(): array
    {
        return [
            ['sku' => 'TEST-SP-001',       'uom' => 'PCS', 'desc' => 'Spare part 1',  'category' => 'TEST-CAT', 'supplier' => 'MIA',  'cost' => 2.70, 'is_sp' => 'SP'],
            ['sku' => 'TEST-SP-COST',      'uom' => 'PCS', 'desc' => 'Spare part 2',  'category' => 'TEST-CAT', 'supplier' => 'MIA',  'cost' => 12.5, 'is_sp' => 'SP'],
            ['sku' => 'TEST-SP-MIA',       'uom' => 'PCS', 'desc' => 'Spare part 3',  'category' => 'TEST-CAT', 'supplier' => 'MIA',  'cost' => 1.00, 'is_sp' => 'SP'],
            ['sku' => 'TEST-SP-MIA-TRIM',  'uom' => 'PCS', 'desc' => 'Spare part 4',  'category' => 'TEST-CAT', 'supplier' => 'MIA ', 'cost' => 1.00, 'is_sp' => 'SP'],
            ['sku' => 'TEST-SP-SLASH 1/4', 'uom' => 'PCS', 'desc' => 'Slash SKU',     'category' => 'TEST-CAT', 'supplier' => 'MIA',  'cost' => 1.00, 'is_sp' => 'SP'],
            ['sku' => 'TEST-RM-001',       'uom' => 'SET', 'desc' => 'Raw mat 1',     'category' => 'TEST-CAT', 'supplier' => 'MIA',  'cost' => 5.00, 'is_sp' => 'RAW MAT'],
            ['sku' => 'TEST-RM-002',       'uom' => 'SET', 'desc' => 'Raw mat 2',     'category' => 'TEST-CAT', 'supplier' => 'MIA',  'cost' => 5.00, 'is_sp' => 'RAW MAT'],
            ['sku' => 'TEST-RM-003',       'uom' => 'SET', 'desc' => 'Raw mat 3',     'category' => 'TEST-CAT', 'supplier' => 'MIA',  'cost' => 5.00, 'is_sp' => 'RAW MAT'],
            ['sku' => 'TEST-RM-004',       'uom' => 'SET', 'desc' => 'Raw mat 4',     'category' => 'TEST-CAT', 'supplier' => 'MIA',  'cost' => 5.00, 'is_sp' => 'RAW MAT'],
            ['sku' => 'TEST-RM-NOSUP',     'uom' => 'SET', 'desc' => 'No supplier',   'category' => 'TEST-CAT', 'supplier' => null,   'cost' => 5.00, 'is_sp' => 'RAW MAT'],
        ];
    }

    /**
     * Write a fixture xlsx that mimics the shape of the real file:
     * - Rows 1–6 are filler/headers (ignored by the importer).
     * - Row 7 is the canonical header row.
     * - Data starts at row 8.
     *
     * Columns follow the real file's layout; we only populate the columns
     * the importer actually reads.
     *
     * @param array<int, array<string, mixed>> $rows
     */
    private function writeFixture(array $rows): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Sheet1');

        // Header row (row 7 in real file).
        $headers = [
            'A' => null,
            'B' => 'No.',
            'C' => 'Item Code',
            'D' => 'UOM',
            'E' => 'UOM Count',
            'F' => 'Description',
            'G' => 'CATEGORY',
            'H' => 'INVENTORY TYPE',
            'I' => null,
            'J' => 'COST',
            'K' => 'SUPPLIER ID',
            'M' => 'COMPANY',
            'AD' => 'ITEM_TYPE',
            'AH' => 'is_sparepart',
        ];
        foreach ($headers as $col => $val) {
            if ($val !== null) {
                $sheet->setCellValue($col.'7', $val);
            }
        }

        $rowNum = 8;
        foreach ($rows as $idx => $r) {
            $sheet->setCellValue('A'.$rowNum, 1);
            $sheet->setCellValue('B'.$rowNum, $idx + 1);
            $sheet->setCellValue('C'.$rowNum, $r['sku']);
            $sheet->setCellValue('D'.$rowNum, $r['uom']);
            $sheet->setCellValue('E'.$rowNum, 1);
            $sheet->setCellValue('F'.$rowNum, $r['desc']);
            $sheet->setCellValue('G'.$rowNum, $r['category']);
            $sheet->setCellValue('H'.$rowNum, 'RAW MATERIAL');
            $sheet->setCellValue('I'.$rowNum, $r['supplier']);
            $sheet->setCellValue('J'.$rowNum, $r['cost']);
            $sheet->setCellValue('M'.$rowNum, 'PC');
            $sheet->setCellValue('AD'.$rowNum, 'MSP');
            $sheet->setCellValue('AH'.$rowNum, $r['is_sp']);
            $rowNum++;
        }

        $tmpPath = tempnam(sys_get_temp_dir(), 'rm_import_').'.xlsx';
        (new XlsxWriter($spreadsheet))->save($tmpPath);
        $this->fixturePath = $tmpPath;

        return $tmpPath;
    }
}
