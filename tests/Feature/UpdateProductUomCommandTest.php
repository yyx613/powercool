<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\InventoryCategory;
use App\Models\Product;
use App\Models\UOM;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use Tests\TestCase;

class UpdateProductUomCommandTest extends TestCase
{
    use DatabaseTransactions;

    private const COMPANY_GROUP_HITEN = 2;

    /** @var string[] paths to fixture xlsx files (cleaned up in tearDown) */
    private array $fixturePaths = [];

    protected function tearDown(): void
    {
        foreach ($this->fixturePaths as $p) {
            if (File::exists($p)) {
                File::delete($p);
            }
        }
        parent::tearDown();
    }

    public function test_finish_good_phase_realigns_uom_id(): void
    {
        [$uomMth, $uomPcs, $uomTrip, $uomUnit] = $this->seedUoms();
        $cat = $this->seedCategory();

        $p1 = $this->seedProduct(Product::TYPE_PRODUCT, 'FG-UNIT-001', $uomMth->id, $cat->id);
        $p2 = $this->seedProduct(Product::TYPE_PRODUCT, 'FG-PCS-001', '7', $cat->id); // dangling
        $p3 = $this->seedProduct(Product::TYPE_PRODUCT, 'FG-TRIP-001', $uomTrip->id, $cat->id);

        $fg = $this->writeFgFixture([
            ['sku' => 'FG-UNIT-001', 'uom' => 'UNIT'],
            ['sku' => 'FG-PCS-001',  'uom' => 'PCS'],
            ['sku' => 'FG-TRIP-001', 'uom' => 'TRIP'],
        ]);
        $rm = $this->writeRmFixture([]); // empty RM file is still a valid xlsx

        $exit = Artisan::call('app:update-product-uom', [
            '--fg-path' => $fg,
            '--rm-path' => $rm,
            '--force' => true,
        ]);

        $this->assertSame(0, $exit);
        $this->assertSame((string) $uomUnit->id, $this->currentUom($p1->id));
        $this->assertSame((string) $uomPcs->id, $this->currentUom($p2->id));
        $this->assertSame((string) $uomTrip->id, $this->currentUom($p3->id));
    }

    public function test_raw_material_phase_writes_uom_id_and_auto_creates_missing(): void
    {
        [, $uomPcs] = $this->seedUoms();
        $cat = $this->seedCategory();

        // PCS already exists in uom table; ROLL and KG do not — they should be auto-created.
        $rm1 = $this->seedProduct(Product::TYPE_RAW_MATERIAL, 'RM-FOO', 'PCS', $cat->id);   // legacy name-based value
        $rm2 = $this->seedProduct(Product::TYPE_RAW_MATERIAL, 'RM-BAR', (string) $uomPcs->id, $cat->id); // already id, but xlsx says ROLL
        $rm3 = $this->seedProduct(Product::TYPE_RAW_MATERIAL, 'RM-BAZ', 'KG', $cat->id);

        $fg = $this->writeFgFixture([]);
        $rm = $this->writeRmFixture([
            ['sku' => 'RM-FOO', 'uom' => 'PCS'],
            ['sku' => 'RM-BAR', 'uom' => 'ROLL'],
            ['sku' => 'RM-BAZ', 'uom' => 'KG'],
        ]);

        $exit = Artisan::call('app:update-product-uom', [
            '--fg-path' => $fg,
            '--rm-path' => $rm,
            '--force' => true,
        ]);

        $this->assertSame(0, $exit);

        $rollId = (string) DB::table('uom')->where('name', 'ROLL')->value('id');
        $kgId = (string) DB::table('uom')->where('name', 'KG')->value('id');
        $this->assertNotEmpty($rollId, 'ROLL should have been auto-created');
        $this->assertNotEmpty($kgId, 'KG should have been auto-created');

        $this->assertSame((string) $uomPcs->id, $this->currentUom($rm1->id));
        $this->assertSame($rollId, $this->currentUom($rm2->id));
        $this->assertSame($kgId, $this->currentUom($rm3->id));

        // Auto-created UOMs must come with KL + Penang branch morph rows so they appear under BranchScope.
        foreach (['ROLL', 'KG'] as $name) {
            $uomId = DB::table('uom')->where('name', $name)->value('id');
            $branches = DB::table('branches')
                ->where('object_type', UOM::class)
                ->where('object_id', $uomId)
                ->pluck('location')
                ->all();
            $this->assertContains(Branch::LOCATION_KL, $branches, "{$name} missing KL branch row");
            $this->assertContains(Branch::LOCATION_PENANG, $branches, "{$name} missing Penang branch row");
        }
    }

    public function test_dry_run_does_not_create_uom_rows(): void
    {
        $this->seedUoms();
        $cat = $this->seedCategory();
        $this->seedProduct(Product::TYPE_RAW_MATERIAL, 'RM-NEW-001', 'PCS', $cat->id);

        $fg = $this->writeFgFixture([]);
        $rm = $this->writeRmFixture([
            ['sku' => 'RM-NEW-001', 'uom' => 'BRANDNEWUOM'],
        ]);

        Artisan::call('app:update-product-uom', [
            '--fg-path' => $fg,
            '--rm-path' => $rm,
            '--dry-run' => true,
        ]);

        $this->assertNull(
            DB::table('uom')->where('name', 'BRANDNEWUOM')->value('id'),
            'Dry run must not create UOM rows'
        );
    }

    public function test_unmatched_db_skus_are_left_untouched(): void
    {
        [$uomMth] = $this->seedUoms();
        $cat = $this->seedCategory();

        $orphanFg = $this->seedProduct(Product::TYPE_PRODUCT, 'FG-NOT-IN-XLSX', $uomMth->id, $cat->id);
        $matchedFg = $this->seedProduct(Product::TYPE_PRODUCT, 'FG-MATCHED', $uomMth->id, $cat->id);

        $orphanRm = $this->seedProduct(Product::TYPE_RAW_MATERIAL, 'RM-NOT-IN-XLSX', 'PCS', $cat->id);
        $matchedRm = $this->seedProduct(Product::TYPE_RAW_MATERIAL, 'RM-MATCHED', 'PCS', $cat->id);

        $fg = $this->writeFgFixture([
            ['sku' => 'FG-MATCHED', 'uom' => 'UNIT'],
        ]);
        $rm = $this->writeRmFixture([
            ['sku' => 'RM-MATCHED', 'uom' => 'ROLL'],
        ]);

        Artisan::call('app:update-product-uom', [
            '--fg-path' => $fg,
            '--rm-path' => $rm,
            '--force' => true,
        ]);

        $rollId = (string) DB::table('uom')->where('name', 'ROLL')->value('id');

        $this->assertSame((string) $uomMth->id, $this->currentUom($orphanFg->id));
        $this->assertNotSame((string) $uomMth->id, $this->currentUom($matchedFg->id));
        $this->assertSame('PCS', $this->currentUom($orphanRm->id), 'orphan RM keeps its legacy value untouched');
        $this->assertSame($rollId, $this->currentUom($matchedRm->id));
    }

    public function test_dry_run_writes_nothing(): void
    {
        [$uomMth] = $this->seedUoms();
        $cat = $this->seedCategory();

        $fgRow = $this->seedProduct(Product::TYPE_PRODUCT, 'FG-DRY-001', $uomMth->id, $cat->id);
        $rmRow = $this->seedProduct(Product::TYPE_RAW_MATERIAL, 'RM-DRY-001', 'PCS', $cat->id);

        $fg = $this->writeFgFixture([['sku' => 'FG-DRY-001', 'uom' => 'UNIT']]);
        $rm = $this->writeRmFixture([['sku' => 'RM-DRY-001', 'uom' => 'ROLL']]);

        Artisan::call('app:update-product-uom', [
            '--fg-path' => $fg,
            '--rm-path' => $rm,
            '--dry-run' => true,
        ]);

        $this->assertSame((string) $uomMth->id, $this->currentUom($fgRow->id));
        $this->assertSame('PCS', $this->currentUom($rmRow->id));
    }

    public function test_skip_flags_isolate_phases(): void
    {
        [$uomMth] = $this->seedUoms();
        $cat = $this->seedCategory();

        $fgRow = $this->seedProduct(Product::TYPE_PRODUCT, 'FG-SKIP-001', $uomMth->id, $cat->id);
        $rmRow = $this->seedProduct(Product::TYPE_RAW_MATERIAL, 'RM-SKIP-001', 'PCS', $cat->id);

        $fg = $this->writeFgFixture([['sku' => 'FG-SKIP-001', 'uom' => 'UNIT']]);
        $rm = $this->writeRmFixture([['sku' => 'RM-SKIP-001', 'uom' => 'ROLL']]);

        // Skip RM phase: RM row stays, FG row updates.
        Artisan::call('app:update-product-uom', [
            '--fg-path' => $fg,
            '--skip-rm' => true,
            '--force' => true,
        ]);
        $this->assertNotSame((string) $uomMth->id, $this->currentUom($fgRow->id));
        $this->assertSame('PCS', $this->currentUom($rmRow->id));

        // Now skip FG phase: RM row updates to ROLL's auto-created id.
        Artisan::call('app:update-product-uom', [
            '--rm-path' => $rm,
            '--skip-fg' => true,
            '--force' => true,
        ]);
        $rollId = (string) DB::table('uom')->where('name', 'ROLL')->value('id');
        $this->assertNotEmpty($rollId);
        $this->assertSame($rollId, $this->currentUom($rmRow->id));
    }

    public function test_unknown_fg_xlsx_uom_aborts(): void
    {
        $this->seedUoms();
        $this->seedCategory();

        $fg = $this->writeFgFixture([['sku' => 'FG-WEIRD-001', 'uom' => 'BOX']]); // BOX not in uom table
        $rm = $this->writeRmFixture([]);

        $exit = Artisan::call('app:update-product-uom', [
            '--fg-path' => $fg,
            '--rm-path' => $rm,
            '--force' => true,
        ]);

        $this->assertNotSame(0, $exit, 'Command must abort when FG xlsx has an unknown UOM name');
    }

    public function test_finish_good_phase_does_not_touch_raw_material_rows(): void
    {
        [$uomMth] = $this->seedUoms();
        $cat = $this->seedCategory();

        $rm = Product::create([
            'inventory_category_id' => $cat->id,
            'type' => Product::TYPE_RAW_MATERIAL,
            'sku' => 'FG-UNIT-001', // overlapping SKU with FG xlsx row
            'model_desc' => 'Raw mat with overlapping SKU',
            'is_active' => true,
            'company_group' => self::COMPANY_GROUP_HITEN,
            'uom' => 'PCS',
        ]);
        Branch::create([
            'object_type' => Product::class,
            'object_id' => $rm->id,
            'location' => Branch::LOCATION_KL,
        ]);

        $fg = $this->writeFgFixture([['sku' => 'FG-UNIT-001', 'uom' => 'UNIT']]);
        $rmFile = $this->writeRmFixture([]); // RM xlsx empty

        Artisan::call('app:update-product-uom', [
            '--fg-path' => $fg,
            '--rm-path' => $rmFile,
            '--force' => true,
        ]);

        $this->assertSame('PCS', $this->currentUom($rm->id), 'type=2 row must not be touched by FG phase');
    }

    // ─────────────────────────── Helpers ───────────────────────────

    /** @return array{0: UOM, 1: UOM, 2: UOM, 3: UOM} [MTH, PCS, TRIP, UNIT] */
    private function seedUoms(): array
    {
        $rows = [];
        foreach (['MTH', 'PCS', 'TRIP', 'UNIT'] as $name) {
            $u = UOM::firstOrCreate(['name' => $name], ['is_active' => true]);
            foreach ([Branch::LOCATION_KL, Branch::LOCATION_PENANG] as $loc) {
                Branch::firstOrCreate([
                    'object_type' => UOM::class,
                    'object_id' => $u->id,
                    'location' => $loc,
                ]);
            }
            $rows[] = $u;
        }
        return $rows;
    }

    private function seedCategory(): InventoryCategory
    {
        $cat = InventoryCategory::firstOrCreate(
            ['name' => 'TEST-PROD-CAT', 'company_group' => self::COMPANY_GROUP_HITEN],
            ['is_active' => true]
        );
        foreach ([Branch::LOCATION_KL, Branch::LOCATION_PENANG] as $loc) {
            Branch::firstOrCreate([
                'object_type' => InventoryCategory::class,
                'object_id' => $cat->id,
                'location' => $loc,
            ]);
        }
        return $cat;
    }

    private function seedProduct(int $type, string $sku, int|string $uom, int $categoryId): Product
    {
        $p = Product::create([
            'inventory_category_id' => $categoryId,
            'type' => $type,
            'sku' => $sku,
            'model_desc' => $sku,
            'is_active' => true,
            'company_group' => self::COMPANY_GROUP_HITEN,
            'uom' => (string) $uom,
        ]);
        Branch::create([
            'object_type' => Product::class,
            'object_id' => $p->id,
            'location' => Branch::LOCATION_KL,
        ]);
        return $p;
    }

    private function currentUom(int $productId): ?string
    {
        $val = DB::table('products')->where('id', $productId)->value('uom');
        return $val === null ? null : (string) $val;
    }

    /**
     * FG xlsx: sheet "MASTER", row 1 header (D=ITEM CODE, L=UOM), data from row 2.
     *
     * @param array<int, array{sku: string, uom: string}> $rows
     */
    private function writeFgFixture(array $rows): string
    {
        $ss = new Spreadsheet();
        $sh = $ss->getActiveSheet();
        $sh->setTitle('MASTER');
        $sh->setCellValue('D1', 'ITEM CODE');
        $sh->setCellValue('L1', 'UOM');
        $r = 2;
        foreach ($rows as $row) {
            $sh->setCellValue('D'.$r, $row['sku']);
            $sh->setCellValue('L'.$r, $row['uom']);
            $r++;
        }
        return $this->saveTmp($ss, 'fg_uom_');
    }

    /**
     * RM xlsx: sheet "RAW MATERIALS & SPARE PART LIST", rows 1-7 filler/header,
     * data from row 8 (C=SKU, D=UOM).
     *
     * @param array<int, array{sku: string, uom: string}> $rows
     */
    private function writeRmFixture(array $rows): string
    {
        $ss = new Spreadsheet();
        $sh = $ss->getActiveSheet();
        $sh->setTitle('RAW MATERIALS & SPARE PART LIST');
        $sh->setCellValue('C7', 'SKU');
        $sh->setCellValue('D7', 'UOM');
        $r = 8;
        foreach ($rows as $row) {
            $sh->setCellValue('C'.$r, $row['sku']);
            $sh->setCellValue('D'.$r, $row['uom']);
            $r++;
        }
        return $this->saveTmp($ss, 'rm_uom_');
    }

    private function saveTmp(Spreadsheet $ss, string $prefix): string
    {
        $tmp = tempnam(sys_get_temp_dir(), $prefix).'.xlsx';
        (new XlsxWriter($ss))->save($tmp);
        $this->fixturePaths[] = $tmp;
        return $tmp;
    }
}
