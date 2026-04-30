<?php

namespace App\Console\Commands;

use App\Models\Attachment;
use App\Models\Branch;
use App\Models\InventoryCategory;
use App\Models\InventoryType;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportRawMaterials extends Command
{
    protected $signature = 'app:import-raw-materials
        {path? : Absolute path to the RAW MATERIALS & SPARE PART LIST.xlsx file}
        {--dry-run : Parse and report what would change, without touching the DB or files}
        {--force : Skip the YES confirmation prompt}';

    protected $description = 'Hard-delete all current raw-material products (with their branch morph rows and related data) and re-import from the Excel file.';

    private const DEFAULT_PATH = '/Users/yapyixian/Herd/powercool/RAW MATERIALS & SPARE PART LIST.xlsx';

    /**
     * Transactional tables that hold FKs to products.id and must be wiped
     * before a raw-material product can be hard-deleted.
     * Value = closure that returns affected-row count for a given $ids array.
     */
    private array $existingIds = [];

    public function handle(): int
    {
        $path = $this->argument('path') ?? self::DEFAULT_PATH;

        if (! File::exists($path)) {
            $this->error("File not found: {$path}");
            return 1;
        }

        $this->info("Reading: {$path}");
        $rows = $this->parseExcel($path);
        $this->info('Parsed ' . count($rows) . ' data rows from Sheet1.');

        [$catLookup, $typeLookup, $supplierLookup] = $this->buildLookups();

        [$resolved, $unmatchedCats, $unmatchedTypes, $skipped] =
            $this->resolveRows($rows, $catLookup, $typeLookup, $supplierLookup);

        $this->reportResolution($resolved, $unmatchedCats, $unmatchedTypes, $skipped);

        if (count($unmatchedCats) > 0) {
            $this->error('Aborting: unknown inventory categories — seed InventoryCategorySeeder or fix the sheet.');
            return 1;
        }

        $this->existingIds = DB::table('products')
            ->where('type', Product::TYPE_RAW_MATERIAL)
            ->pluck('id')
            ->all();

        $deletePreview = $this->previewDeletes($this->existingIds);
        $this->reportDeletePreview($deletePreview, count($this->existingIds));

        if ($this->option('dry-run')) {
            $this->warn('DRY RUN — no changes were made.');
            return 0;
        }

        if (! $this->option('force')) {
            if ($this->ask('Type YES to hard-delete the above and import the new rows') !== 'YES') {
                $this->info('Aborted.');
                return 0;
            }
        }

        try {
            DB::beginTransaction();
            Schema::disableForeignKeyConstraints();

            $this->hardDeleteExisting($this->existingIds);
            $this->insertRows($resolved);

            Schema::enableForeignKeyConstraints();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Schema::enableForeignKeyConstraints();
            $this->error('Import failed: ' . $e->getMessage());
            $this->line($e->getTraceAsString());
            return 1;
        }

        $this->resetAutoIncrement(['products', 'branches']);

        $this->newLine();
        $this->info('Import complete. Re-seed photos with:');
        $this->line('  php artisan db:seed --class=SparePartPhotoSeeder');

        return 0;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Phase 1 — Parse & resolve
    // ─────────────────────────────────────────────────────────────────────────

    private function parseExcel(string $path): array
    {
        $reader = IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly(true);
        $reader->setLoadSheetsOnly(['RAW MATERIALS & SPARE PART LIST']);
        $ss = $reader->load($path);
        $sheet = $ss->getSheetByName('RAW MATERIALS & SPARE PART LIST');

        $rows = [];
        $last = $sheet->getHighestRow();

        // Data rows start at row 8; row 1 carries DB-compatible column identifiers.
        for ($r = 8; $r <= $last; $r++) {
            $get = fn (string $col) => $sheet->getCell($col . $r)->getCalculatedValue();

            $sku = trim((string) $get('C'));
            if ($sku === '') {
                continue; // skip blank rows
            }

            $rows[] = [
                'row' => $r,
                'sku' => $sku,
                'uom' => $this->strOrNull($get('D')),
                'model_desc' => $this->strOrNull($get('F')),
                'category_name' => trim((string) $get('G')),
                'supplier_code' => $this->strOrNull($get('K')),
                'company_group' => $this->strOrNull($get('M')),
                'initial_for_production' => $this->strOrNull($get('O')),
                'qty' => $this->intOrNull($get('Q')),
                'low_stock_threshold' => $this->intOrNull($get('R')),
                'min_price' => $this->numOrNull($get('S')),
                'max_price' => $this->numOrNull($get('T')),
                'cost' => $this->numOrNull($get('J')),
                'weight' => $this->numOrNull($get('U')),
                'length' => $this->numOrNull($get('V')),
                'width' => $this->numOrNull($get('W')),
                'height' => $this->numOrNull($get('X')),
                'capacity' => $this->strOrNull($get('Y')),
                'refrigerant' => $this->strOrNull($get('Z')),
                'power_input' => $this->strOrNull($get('AA')),
                'voltage_frequency' => $this->strOrNull($get('AB')),
                'standard_features' => $this->strOrNull($get('AC')),
                'sst' => $this->boolish($get('AE')),
                'hi_ten_stock_code' => $this->strOrNull($get('AF')),
                'item_type_name' => trim((string) $get('AH')), // AH = "SP" or "RAW MAT"
                'lazada_sku' => $this->strOrNull($get('AI')),
                'shopee_sku' => $this->strOrNull($get('AJ')),
                'tiktok_sku' => $this->strOrNull($get('AK')),
                'woo_commerce_sku' => $this->strOrNull($get('AL')),
            ];
        }

        return $rows;
    }

    private function buildLookups(): array
    {
        // Inventory categories — resolved across all branches so a raw-material
        // product is always assigned a category that exists in both KL and
        // Penang. Preference order per name:
        //   1. Has branch rows for BOTH KL + Penang
        //   2. company_group = 1 (PC) over company_group = 2
        //   3. Lowest id (tie-break)
        $catRows = DB::table('inventory_categories')
            ->whereNull('deleted_at')
            ->get(['id', 'name', 'company_group']);

        $klCatIds = DB::table('branches')
            ->where('object_type', 'App\\Models\\InventoryCategory')
            ->where('location', Branch::LOCATION_KL)
            ->pluck('object_id')
            ->flip();

        $penangCatIds = DB::table('branches')
            ->where('object_type', 'App\\Models\\InventoryCategory')
            ->where('location', Branch::LOCATION_PENANG)
            ->pluck('object_id')
            ->flip();

        $catLookup = [];
        foreach ($catRows->groupBy(fn ($r) => mb_strtoupper(trim($r->name))) as $name => $group) {
            $scored = $group->map(function ($c) use ($klCatIds, $penangCatIds) {
                $hasBoth = isset($klCatIds[$c->id]) && isset($penangCatIds[$c->id]);
                $isPc = (int) $c->company_group === 1;
                return [
                    'id' => $c->id,
                    // sort key: (both-branches desc, pc-first desc, id asc)
                    'sort' => sprintf('%d-%d-%010d', $hasBoth ? 0 : 1, $isPc ? 0 : 1, $c->id),
                ];
            })->sortBy('sort')->values();
            $catLookup[$name] = $scored->first()['id'];
        }

        // Inventory types — pick the LOCATION_KL variant (first by id) for the FK.
        $typeLookup = [];
        $types = DB::table('inventory_types')
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->get(['id', 'name']);
        foreach ($types as $t) {
            $key = mb_strtoupper(trim($t->name));
            if (! isset($typeLookup[$key])) {
                $typeLookup[$key] = $t->id;
            }
        }

        // Suppliers — by sku code (pick first if duplicates).
        $supplierLookup = [];
        $suppliers = DB::table('suppliers')
            ->whereNull('deleted_at')
            ->whereNotNull('sku')
            ->orderBy('id')
            ->get(['id', 'sku']);
        foreach ($suppliers as $s) {
            $key = mb_strtoupper(trim($s->sku));
            if (! isset($supplierLookup[$key])) {
                $supplierLookup[$key] = $s->id;
            }
        }

        return [$catLookup, $typeLookup, $supplierLookup];
    }

    private function resolveRows(array $rows, array $catLookup, array $typeLookup, array $supplierLookup): array
    {
        $resolved = [];
        $unmatchedCats = [];
        $unmatchedTypes = [];
        $skipped = [];

        foreach ($rows as $row) {
            $catKey = mb_strtoupper($row['category_name']);
            if (! isset($catLookup[$catKey])) {
                $unmatchedCats[$row['category_name']] = ($unmatchedCats[$row['category_name']] ?? 0) + 1;
                $skipped[] = $row;
                continue;
            }

            $typeKey = mb_strtoupper($row['item_type_name']);
            $itemTypeId = $typeLookup[$typeKey] ?? null;
            if ($row['item_type_name'] !== '' && $itemTypeId === null) {
                $unmatchedTypes[$row['item_type_name']] = ($unmatchedTypes[$row['item_type_name']] ?? 0) + 1;
            }

            $supplierId = null;
            if ($row['supplier_code'] !== null) {
                $supplierId = $supplierLookup[mb_strtoupper($row['supplier_code'])] ?? null;
            }

            // is_sparepart: "SP" → true, "RAW MAT" → false, blank → null
            $isSparepart = match (mb_strtoupper($row['item_type_name'])) {
                'SP' => 1,
                'RAW MAT' => 0,
                default => null,
            };

            $resolved[] = $row + [
                'inventory_category_id' => $catLookup[$catKey],
                'item_type_id' => $itemTypeId,
                'resolved_supplier_id' => $supplierId,
                'is_sparepart' => $isSparepart,
            ];
        }

        return [$resolved, $unmatchedCats, $unmatchedTypes, $skipped];
    }

    private function reportResolution(array $resolved, array $unmatchedCats, array $unmatchedTypes, array $skipped): void
    {
        $sp = 0;
        $rm = 0;
        $noFlag = 0;
        foreach ($resolved as $r) {
            match ($r['is_sparepart']) {
                1 => $sp++,
                0 => $rm++,
                default => $noFlag++,
            };
        }

        $this->newLine();
        $this->info('Import preview:');
        $this->line("  Would import: " . count($resolved) . " products  (sparepart={$sp}, raw-material={$rm}, unflagged={$noFlag})");
        $this->line('  Unmatched categories: ' . count($unmatchedCats) . ' (' . array_sum($unmatchedCats) . ' rows)');
        foreach ($unmatchedCats as $name => $n) {
            $this->line("    - {$name}: {$n}");
        }
        $this->line('  Unmatched item types: ' . count($unmatchedTypes) . ' (' . array_sum($unmatchedTypes) . ' rows)');
        foreach ($unmatchedTypes as $name => $n) {
            $this->line("    - {$name}: {$n}");
        }
        $this->line('  Skipped rows (category miss): ' . count($skipped));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Phase 2 — Hard-delete preview + execution
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Return [tableOrFolder => rowCount] for each thing we'd delete.
     */
    private function previewDeletes(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        $out = [];
        $productMorph = 'App\\Models\\Product';

        // Transactional refs (FK-blocking) — force-delete path.
        $saleProductIds = Schema::hasTable('sale_products')
            ? DB::table('sale_products')->whereIn('product_id', $ids)->pluck('id')->all()
            : [];

        if (Schema::hasTable('sale_product_children') && ! empty($saleProductIds)) {
            $out['sale_product_children'] = DB::table('sale_product_children')->whereIn('sale_product_id', $saleProductIds)->count();
        }
        if (Schema::hasTable('delivery_order_products') && ! empty($saleProductIds)) {
            $out['delivery_order_products'] = DB::table('delivery_order_products')->whereIn('sale_product_id', $saleProductIds)->count();
        }
        if (Schema::hasTable('sale_products')) {
            $out['sale_products'] = count($saleProductIds);
        }
        $this->addDeleteCount($out, 'production_milestone_materials', 'product_id', $ids);
        $this->addDeleteCount($out, 'production_milestone_materials_preview', 'product_id', $ids);
        $this->addMorphDeleteCount($out, 'task_milestone_inventories', 'inventory_type', 'inventory_id', $productMorph, $ids);
        $this->addDeleteCount($out, 'material_use_products', 'product_id', $ids);
        $this->addDeleteCount($out, 'grn', 'product_id', $ids);
        $this->addDeleteCount($out, 'promotions', 'product_id', $ids);
        $this->addMorphDeleteCount($out, 'inventory_service_reminders', 'object_type', 'object_id', $productMorph, $ids);

        // Cascade children.
        $this->addMorphDeleteCount($out, 'attachments', 'object_type', 'object_id', $productMorph, $ids);
        $out['attachments (files on disk)'] = $this->countProductAttachmentFiles($ids);

        $this->addDeleteCount($out, 'product_children', 'product_id', $ids);
        $this->addDeleteCount($out, 'product_costs', 'product_id', $ids);
        $this->addDeleteCount($out, 'product_selling_prices', 'product_id', $ids);
        $this->addDeleteCount($out, 'product_milestones', 'product_id', $ids);
        $this->addDeleteCount($out, 'classification_code_product', 'product_id', $ids);

        if (Schema::hasTable('factory_raw_materials')) {
            $frmIds = DB::table('factory_raw_materials')->whereIn('product_id', $ids)->pluck('id')->all();
            if (Schema::hasTable('factory_raw_material_records')) {
                $out['factory_raw_material_records'] = empty($frmIds)
                    ? 0
                    : DB::table('factory_raw_material_records')->whereIn('factory_raw_material_id', $frmIds)->count();
            }
            $out['factory_raw_materials'] = count($frmIds);
        }

        $this->addMorphDeleteCount($out, 'branches', 'object_type', 'object_id', $productMorph, $ids);

        $out['products (raw material, type=2)'] = count($ids);

        return $out;
    }

    private function addDeleteCount(array &$out, string $table, string $fk, array $ids): void
    {
        if (Schema::hasTable($table)) {
            $out[$table] = DB::table($table)->whereIn($fk, $ids)->count();
        }
    }

    private function addMorphDeleteCount(array &$out, string $table, string $typeCol, string $idCol, string $morph, array $ids): void
    {
        if (Schema::hasTable($table)) {
            $out[$table] = DB::table($table)->where($typeCol, $morph)->whereIn($idCol, $ids)->count();
        }
    }

    private function countProductAttachmentFiles(array $ids): int
    {
        if (! Schema::hasTable('attachments') || empty($ids)) {
            return 0;
        }

        $dir = storage_path('app/' . Attachment::PRODUCT_PATH);
        if (! File::isDirectory($dir)) {
            return 0;
        }

        $srcs = DB::table('attachments')
            ->where('object_type', 'App\\Models\\Product')
            ->whereIn('object_id', $ids)
            ->pluck('src');

        $count = 0;
        foreach ($srcs as $src) {
            if ($src && File::exists($dir . '/' . $src)) {
                $count++;
            }
        }
        return $count;
    }

    private function reportDeletePreview(array $preview, int $productCount): void
    {
        $this->newLine();
        $this->info("Would hard-delete {$productCount} existing raw-material products plus:");
        foreach ($preview as $table => $n) {
            $this->line(sprintf('  - %-45s %d', $table, $n));
        }
    }

    private function hardDeleteExisting(array $ids): void
    {
        if (empty($ids)) {
            $this->info('No existing raw materials to delete.');
            return;
        }

        $productMorph = 'App\\Models\\Product';

        // Transactional refs first (order: children → parent).
        $saleProductIds = Schema::hasTable('sale_products')
            ? DB::table('sale_products')->whereIn('product_id', $ids)->pluck('id')->all()
            : [];

        if (! empty($saleProductIds)) {
            if (Schema::hasTable('sale_product_children')) {
                $this->deleteAndLog('sale_product_children', fn () => DB::table('sale_product_children')->whereIn('sale_product_id', $saleProductIds)->delete());
            }
            if (Schema::hasTable('delivery_order_products')) {
                $this->deleteAndLog('delivery_order_products', fn () => DB::table('delivery_order_products')->whereIn('sale_product_id', $saleProductIds)->delete());
            }
        }
        if (Schema::hasTable('sale_products')) {
            $this->deleteAndLog('sale_products', fn () => DB::table('sale_products')->whereIn('product_id', $ids)->delete());
        }

        foreach (['production_milestone_materials', 'production_milestone_materials_preview', 'material_use_products', 'grn', 'promotions'] as $t) {
            if (Schema::hasTable($t)) {
                $this->deleteAndLog($t, fn () => DB::table($t)->whereIn('product_id', $ids)->delete());
            }
        }

        if (Schema::hasTable('task_milestone_inventories')) {
            $this->deleteAndLog('task_milestone_inventories', fn () => DB::table('task_milestone_inventories')
                ->where('inventory_type', $productMorph)
                ->whereIn('inventory_id', $ids)
                ->delete());
        }

        if (Schema::hasTable('inventory_service_reminders')) {
            $this->deleteAndLog('inventory_service_reminders', fn () => DB::table('inventory_service_reminders')
                ->where('object_type', $productMorph)
                ->whereIn('object_id', $ids)
                ->delete());
        }

        // Attachment files on disk first, then rows.
        $this->deleteAttachmentFiles($ids);
        $this->deleteAndLog('attachments', fn () => DB::table('attachments')
            ->where('object_type', $productMorph)
            ->whereIn('object_id', $ids)
            ->delete());

        foreach (['product_children', 'product_costs', 'product_selling_prices', 'product_milestones', 'classification_code_product'] as $t) {
            if (Schema::hasTable($t)) {
                $this->deleteAndLog($t, fn () => DB::table($t)->whereIn('product_id', $ids)->delete());
            }
        }

        if (Schema::hasTable('factory_raw_materials')) {
            $frmIds = DB::table('factory_raw_materials')->whereIn('product_id', $ids)->pluck('id')->all();
            if (! empty($frmIds) && Schema::hasTable('factory_raw_material_records')) {
                $this->deleteAndLog('factory_raw_material_records', fn () => DB::table('factory_raw_material_records')->whereIn('factory_raw_material_id', $frmIds)->delete());
            }
            $this->deleteAndLog('factory_raw_materials', fn () => DB::table('factory_raw_materials')->whereIn('product_id', $ids)->delete());
        }

        // The "(with branch)" step.
        $this->deleteAndLog('branches (morph)', fn () => DB::table('branches')
            ->where('object_type', $productMorph)
            ->whereIn('object_id', $ids)
            ->delete());

        // Finally, the products themselves — use DB::table to bypass SoftDeletes.
        $this->deleteAndLog('products', fn () => DB::table('products')->whereIn('id', $ids)->delete());
    }

    private function deleteAndLog(string $label, \Closure $run): void
    {
        $n = $run();
        $this->line(sprintf('  ✓ %-45s %d deleted', $label, $n));
    }

    private function deleteAttachmentFiles(array $ids): void
    {
        $dir = storage_path('app/' . Attachment::PRODUCT_PATH);
        if (! File::isDirectory($dir)) {
            return;
        }

        $srcs = DB::table('attachments')
            ->where('object_type', 'App\\Models\\Product')
            ->whereIn('object_id', $ids)
            ->pluck('src');

        $deleted = 0;
        foreach ($srcs as $src) {
            $full = $dir . '/' . $src;
            if ($src && File::exists($full)) {
                File::delete($full);
                $deleted++;
            }
        }
        $this->line("  ✓ attachment files on disk                      {$deleted} deleted");
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Phase 3 — Insert rows + branch morphs
    // ─────────────────────────────────────────────────────────────────────────

    private function insertRows(array $resolved): void
    {
        $now = now();
        $bar = $this->output->createProgressBar(count($resolved));
        $bar->start();

        $created = 0;
        foreach ($resolved as $r) {
            $productId = DB::table('products')->insertGetId([
                'inventory_category_id' => $r['inventory_category_id'],
                'supplier_id' => $r['resolved_supplier_id'],
                'type' => Product::TYPE_RAW_MATERIAL,
                'sku' => $r['sku'],
                'model_desc' => $r['model_desc'] ?? $r['sku'],
                'qty' => $r['qty'],
                'low_stock_threshold' => $r['low_stock_threshold'],
                'min_price' => $r['min_price'],
                'max_price' => $r['max_price'],
                'cost' => $r['cost'] ?? 0,
                'weight' => $r['weight'],
                'length' => $r['length'],
                'width' => $r['width'],
                'height' => $r['height'],
                'capacity' => $r['capacity'],
                'refrigerant' => $r['refrigerant'],
                'power_input' => $r['power_input'],
                'voltage_frequency' => $r['voltage_frequency'],
                'standard_features' => $r['standard_features'],
                'is_active' => 1,
                'is_sparepart' => $r['is_sparepart'],
                'item_type' => $r['item_type_id'],
                'uom' => $r['uom'],
                'company_group' => $r['company_group'] ?? 'PC',
                'initial_for_production' => $r['initial_for_production'],
                'hi_ten_stock_code' => $r['hi_ten_stock_code'],
                'lazada_sku' => $r['lazada_sku'],
                'shopee_sku' => $r['shopee_sku'],
                'tiktok_sku' => $r['tiktok_sku'],
                'woo_commerce_sku' => $r['woo_commerce_sku'],
                'sst' => $r['sst'] ? 1 : 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            foreach ([Branch::LOCATION_KL, Branch::LOCATION_PENANG] as $loc) {
                DB::table('branches')->insert([
                    'object_type' => 'App\\Models\\Product',
                    'object_id' => $productId,
                    'location' => $loc,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
            $created++;
            $bar->advance();
        }
        $bar->finish();
        $this->newLine();
        $this->info("Inserted {$created} products with both KL + Penang branch rows.");
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Utilities
    // ─────────────────────────────────────────────────────────────────────────

    private function resetAutoIncrement(array $tables): void
    {
        foreach ($tables as $t) {
            if (! Schema::hasTable($t)) {
                continue;
            }
            try {
                $maxId = (int) DB::table($t)->max('id');
                DB::statement("ALTER TABLE `{$t}` AUTO_INCREMENT = " . ($maxId + 1));
            } catch (\Throwable) {
                // best-effort; skip silently
            }
        }
    }

    private function strOrNull($v): ?string
    {
        if ($v === null) return null;
        $s = trim((string) $v);
        return $s === '' ? null : $s;
    }

    private function numOrNull($v): ?float
    {
        if ($v === null || $v === '') return null;
        if (is_numeric($v)) return (float) $v;
        return null;
    }

    private function intOrNull($v): ?int
    {
        if ($v === null || $v === '') return null;
        if (is_numeric($v)) return (int) $v;
        return null;
    }

    private function boolish($v): bool
    {
        if ($v === null) return false;
        $s = mb_strtoupper(trim((string) $v));
        return in_array($s, ['Y', 'YES', '1', 'TRUE'], true);
    }
}
