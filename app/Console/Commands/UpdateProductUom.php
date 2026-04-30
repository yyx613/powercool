<?php

namespace App\Console\Commands;

use App\Models\Branch;
use App\Models\Product;
use App\Models\UOM;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use PhpOffice\PhpSpreadsheet\IOFactory;

class UpdateProductUom extends Command
{
    protected $signature = 'app:update-product-uom
        {--fg-path= : Override path to FINISH GOOD LIST.xlsx}
        {--rm-path= : Override path to RAW MATERIALS & SPARE PART LIST.xlsx}
        {--skip-fg : Skip the finish-good (type=1) phase}
        {--skip-rm : Skip the raw-material/spare-part (type=2) phase}
        {--dry-run : Parse and report what would change, without writing}
        {--force : Skip the YES confirmation prompt}';

    protected $description = 'Realign products.uom (uom.id stored as string) from the master spreadsheets. FG (type=1) reads FINISH GOOD LIST.xlsx; RM/SP (type=2) reads RAW MATERIALS & SPARE PART LIST.xlsx and auto-creates any new UOM names found there.';

    private const FG_DEFAULT_PATH = '/Users/yapyixian/Herd/powercool/FINISH GOOD LIST.xlsx';
    private const FG_SHEET = 'MASTER';
    private const FG_HEADER_ROWS = 1;
    private const FG_SKU_COL = 'D';
    private const FG_UOM_COL = 'L';

    private const RM_DEFAULT_PATH = '/Users/yapyixian/Herd/powercool/RAW MATERIALS & SPARE PART LIST.xlsx';
    private const RM_SHEET = 'RAW MATERIALS & SPARE PART LIST';
    private const RM_HEADER_ROWS = 7;
    private const RM_SKU_COL = 'C';
    private const RM_UOM_COL = 'D';

    public function handle(): int
    {
        $doFg = ! $this->option('skip-fg');
        $doRm = ! $this->option('skip-rm');

        if (! $doFg && ! $doRm) {
            $this->error('Both phases skipped — nothing to do.');
            return 1;
        }

        $uomLookup = $this->buildUomLookup();

        // Phase 1: FG (type=1) — aborts on unknown UOM (the FG xlsx has a tightly-controlled vocabulary).
        $fgPlan = null;
        if ($doFg) {
            $fgPath = $this->option('fg-path') ?: self::FG_DEFAULT_PATH;
            if (! File::exists($fgPath)) {
                $this->error("FG file not found: {$fgPath}");
                return 1;
            }
            $this->info("Reading FG: {$fgPath}");
            $fgMap = $this->parseExcel($fgPath, self::FG_SHEET, self::FG_HEADER_ROWS, self::FG_SKU_COL, self::FG_UOM_COL);
            $this->info('Parsed ' . count($fgMap) . ' SKU rows from sheet "' . self::FG_SHEET . '".');

            $unknown = $this->detectUnknownUoms($fgMap, $uomLookup);
            if (! empty($unknown)) {
                $this->error('Aborting: FG xlsx contains UOM names not present in the uom table:');
                foreach ($unknown as $name => $n) {
                    $this->line("  - {$name}: {$n} rows");
                }
                $this->line('Known UOMs: ' . implode(', ', array_keys($uomLookup)));
                return 1;
            }

            $fgPlan = $this->planByName($fgMap, Product::TYPE_PRODUCT, $uomLookup);
            $this->reportPlan('Finish goods (type=1)', $fgPlan);
        }

        // Phase 2: RM/SP (type=2) — auto-creates missing UOMs (free-form vocabulary).
        $rmPlan = null;
        if ($doRm) {
            $rmPath = $this->option('rm-path') ?: self::RM_DEFAULT_PATH;
            if (! File::exists($rmPath)) {
                $this->error("RM file not found: {$rmPath}");
                return 1;
            }
            $this->info("Reading RM/SP: {$rmPath}");
            $rmMap = $this->parseExcel($rmPath, self::RM_SHEET, self::RM_HEADER_ROWS, self::RM_SKU_COL, self::RM_UOM_COL);
            $this->info('Parsed ' . count($rmMap) . ' SKU rows from sheet "' . self::RM_SHEET . '".');

            $rmPlan = $this->planByName($rmMap, Product::TYPE_RAW_MATERIAL, $uomLookup);
            $this->reportPlan('Raw materials & spare parts (type=2)', $rmPlan);
        }

        $totalUpdates = ($fgPlan['updateCount'] ?? 0) + ($rmPlan['updateCount'] ?? 0);
        $totalToCreate = count($rmPlan['uomsToCreate'] ?? []);

        if ($this->option('dry-run')) {
            $this->warn('DRY RUN — no changes were made.');
            return 0;
        }

        if ($totalUpdates === 0 && $totalToCreate === 0) {
            $this->info('Nothing to update.');
            return 0;
        }

        if (! $this->option('force')) {
            $msg = "Type YES to update {$totalUpdates} product rows";
            if ($totalToCreate > 0) {
                $msg .= " (and create {$totalToCreate} new UOM rows)";
            }
            if ($this->ask($msg) !== 'YES') {
                $this->info('Aborted.');
                return 0;
            }
        }

        try {
            DB::transaction(function () use ($fgPlan, $rmPlan) {
                $lookup = $this->buildUomLookup();

                if (! empty($rmPlan['uomsToCreate'] ?? [])) {
                    foreach ($rmPlan['uomsToCreate'] as $name) {
                        $this->ensureUom($name);
                    }
                    $lookup = $this->buildUomLookup();
                }

                if ($fgPlan !== null) {
                    $this->applyUpdates($fgPlan['idsByTargetName'], $lookup);
                }
                if ($rmPlan !== null) {
                    $this->applyUpdates($rmPlan['idsByTargetName'], $lookup);
                }
            });
        } catch (\Throwable $e) {
            $this->error('Update failed: ' . $e->getMessage());
            return 1;
        }

        $this->info("Updated {$totalUpdates} product rows" . ($totalToCreate > 0 ? ", created {$totalToCreate} new UOM rows" : '') . '.');
        return 0;
    }

    // ─────────────────────── parse + lookup ───────────────────────

    /** @return array<string,string> uppercased SKU -> uppercased UOM name */
    private function parseExcel(string $path, string $sheetName, int $headerRows, string $skuCol, string $uomCol): array
    {
        $reader = IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly(true);
        $reader->setLoadSheetsOnly([$sheetName]);
        $sheet = $reader->load($path)->getSheetByName($sheetName);

        $map = [];
        $last = $sheet->getHighestRow();
        $startRow = $headerRows + 1;

        for ($r = $startRow; $r <= $last; $r++) {
            $sku = trim((string) $sheet->getCell($skuCol . $r)->getCalculatedValue());
            $uom = trim((string) $sheet->getCell($uomCol . $r)->getCalculatedValue());
            if ($sku === '' || $uom === '') {
                continue;
            }
            $map[mb_strtoupper($sku)] = mb_strtoupper($uom);
        }

        return $map;
    }

    /** @return array<string,int> uppercased UOM name -> uom.id */
    private function buildUomLookup(): array
    {
        $out = [];
        foreach (DB::table('uom')->get(['id', 'name']) as $row) {
            $out[mb_strtoupper(trim((string) $row->name))] = (int) $row->id;
        }
        return $out;
    }

    /** @return array<string,int> unknown UOM name -> count */
    private function detectUnknownUoms(array $skuToUomName, array $uomLookup): array
    {
        $unknown = [];
        foreach ($skuToUomName as $name) {
            if (! isset($uomLookup[$name])) {
                $unknown[$name] = ($unknown[$name] ?? 0) + 1;
            }
        }
        return $unknown;
    }

    private function ensureUom(string $name): UOM
    {
        $uom = UOM::withoutGlobalScopes()->firstOrCreate(
            ['name' => $name],
            ['is_active' => true]
        );
        foreach ([Branch::LOCATION_KL, Branch::LOCATION_PENANG] as $loc) {
            Branch::withoutGlobalScopes()->firstOrCreate([
                'object_type' => UOM::class,
                'object_id' => $uom->id,
                'location' => $loc,
            ]);
        }
        return $uom;
    }

    // ─────────────────────── planning ───────────────────────

    /**
     * Plans updates for a product type, keyed by the target UOM **name** (so the plan stays
     * meaningful even when the UOM doesn't exist yet — see uomsToCreate).
     *
     * @param  array<string,string>  $skuToUomName  uppercased SKU -> uppercased UOM name (xlsx)
     * @param  int                   $productType   Product::TYPE_PRODUCT or Product::TYPE_RAW_MATERIAL
     * @param  array<string,int>     $uomLookup     uppercased name -> uom.id (current DB state)
     * @return array{idsByTargetName: array<string, int[]>, updateCount: int, noopCount: int, unmatchedSkus: array<string,int>, totalProducts: int, uomsToCreate: string[]}
     */
    private function planByName(array $skuToUomName, int $productType, array $uomLookup): array
    {
        $products = DB::table('products')
            ->where('type', $productType)
            ->whereNull('deleted_at')
            ->get(['id', 'sku', 'uom']);

        $idsByTargetName = [];
        $noopCount = 0;
        $unmatchedSkus = [];

        foreach ($products as $p) {
            $key = mb_strtoupper(trim((string) $p->sku));
            if (! isset($skuToUomName[$key])) {
                $unmatchedSkus[$p->sku] = ($unmatchedSkus[$p->sku] ?? 0) + 1;
                continue;
            }
            $targetName = $skuToUomName[$key];
            $targetId = $uomLookup[$targetName] ?? null; // null -> UOM doesn't exist yet, will be created

            $current = $p->uom === null ? null : (string) $p->uom;
            if ($targetId !== null && $current === (string) $targetId) {
                $noopCount++;
                continue;
            }
            $idsByTargetName[$targetName][] = (int) $p->id;
        }

        $uomsToCreate = [];
        foreach (array_keys($idsByTargetName) as $name) {
            if (! isset($uomLookup[$name])) {
                $uomsToCreate[] = $name;
            }
        }

        return [
            'idsByTargetName' => $idsByTargetName,
            'updateCount' => array_sum(array_map('count', $idsByTargetName)),
            'noopCount' => $noopCount,
            'unmatchedSkus' => $unmatchedSkus,
            'totalProducts' => $products->count(),
            'uomsToCreate' => $uomsToCreate,
        ];
    }

    // ─────────────────────── apply ───────────────────────

    /**
     * @param  array<string, int[]>  $idsByTargetName  UOM name -> product ids
     * @param  array<string, int>    $uomLookup        UOM name -> uom.id (must contain every key in idsByTargetName)
     */
    private function applyUpdates(array $idsByTargetName, array $uomLookup): void
    {
        foreach ($idsByTargetName as $name => $ids) {
            $id = $uomLookup[$name] ?? null;
            if ($id === null) {
                throw new \RuntimeException("UOM lookup missing entry for '{$name}' at apply time");
            }
            foreach (array_chunk($ids, 500) as $chunk) {
                DB::table('products')->whereIn('id', $chunk)->update([
                    'uom' => (string) $id,
                    'updated_at' => now(),
                ]);
            }
        }
    }

    // ─────────────────────── reporting ───────────────────────

    /**
     * @param array{idsByTargetName: array<string, int[]>, updateCount: int, noopCount: int, unmatchedSkus: array<string,int>, totalProducts: int, uomsToCreate: string[]} $plan
     */
    private function reportPlan(string $title, array $plan): void
    {
        $this->newLine();
        $this->info($title . ' — preview:');
        $this->line("  Live products: {$plan['totalProducts']}");
        $this->line("  Will update: {$plan['updateCount']}");
        $this->line("  Already correct (no-op): {$plan['noopCount']}");
        $this->line('  Unmatched SKUs (not in xlsx): ' . count($plan['unmatchedSkus']));

        if (! empty($plan['idsByTargetName'])) {
            $this->line('  By target UOM:');
            ksort($plan['idsByTargetName']);
            foreach ($plan['idsByTargetName'] as $name => $ids) {
                $marker = in_array($name, $plan['uomsToCreate'], true) ? ' (NEW — will be created)' : '';
                $this->line(sprintf('    - %s%s: %d', $name, $marker, count($ids)));
            }
        }

        if (! empty($plan['uomsToCreate'])) {
            $this->line('  UOMs to create: ' . implode(', ', $plan['uomsToCreate']));
        }

        if (! empty($plan['unmatchedSkus'])) {
            $sample = array_slice(array_keys($plan['unmatchedSkus']), 0, 30);
            $this->line('  Sample unmatched SKUs (first ' . count($sample) . ' of ' . count($plan['unmatchedSkus']) . '):');
            foreach ($sample as $sku) {
                $this->line('    - ' . $sku);
            }
        }
    }
}
