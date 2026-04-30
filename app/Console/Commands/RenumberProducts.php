<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RenumberProducts extends Command
{
    protected $signature = 'app:renumber-products
        {--dry-run : Print the renumber preview without touching the DB}
        {--force : Skip the YES confirmation prompt}';

    protected $description = 'Hard-delete trashed products/attachments, then renumber products.id (FG 1..N, RM N+1..M) and attachments.id (1..K) contiguously, propagating to every FK and morph.';

    private const OFFSET = 10_000_000;
    private const PRODUCT_MORPH = 'App\\Models\\Product';

    /**
     * Tables with a direct `product_id` FK to products.id.
     * Filtered by Schema::hasTable() at runtime.
     */
    private const PRODUCT_ID_TABLES = [
        'product_children',
        'product_costs',
        'product_selling_prices',
        'product_milestones',
        'classification_code_product',
        'factory_raw_materials',
        'material_uses',
        'material_use_products',
        'production_milestone_materials',
        'production_milestone_materials_preview',
        'promotions',
        'grn',
        'sale_products',
        'productions',
        'service_forms',
        'service_form_products',
        'service_form_service_items',
    ];

    /**
     * Polymorphic tables that may carry App\Models\Product rows.
     * Format: [table, type_column, id_column].
     */
    private const PRODUCT_MORPH_TABLES = [
        ['attachments', 'object_type', 'object_id'],
        ['branches', 'object_type', 'object_id'],
        ['inventory_service_reminders', 'object_type', 'object_id'],
        ['task_milestone_inventories', 'inventory_type', 'inventory_id'],
    ];

    public function handle(): int
    {
        if (app()->environment('production')) {
            $this->error('This command is disabled in production.');
            return 1;
        }

        $preview = $this->buildPreview();
        $this->printPreview($preview);

        if ($this->option('dry-run')) {
            $this->warn('DRY RUN — no changes were made.');
            return 0;
        }

        if (! $this->option('force')) {
            if ($this->ask('Type YES to execute the renumber') !== 'YES') {
                $this->info('Aborted.');
                return 0;
            }
        }

        try {
            DB::beginTransaction();
            Schema::disableForeignKeyConstraints();

            $this->purgeTrashed();
            $this->renumberProducts();
            $this->renumberAttachments();

            Schema::enableForeignKeyConstraints();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Schema::enableForeignKeyConstraints();
            $this->error('Renumber failed: ' . $e->getMessage());
            $this->line($e->getTraceAsString());
            return 1;
        }

        $this->resetAutoIncrements();
        $this->printSummary();

        return 0;
    }

    // ─── Preview ──────────────────────────────────────────────────────

    private function buildPreview(): array
    {
        $trashedProducts = DB::table('products')->whereNotNull('deleted_at')->count();
        $trashedAttachments = DB::table('attachments')->whereNotNull('deleted_at')->count();

        $liveProducts = DB::table('products')->whereNull('deleted_at')->count();
        $fgCount = DB::table('products')->whereNull('deleted_at')->where('type', 1)->count();
        $rmCount = DB::table('products')->whereNull('deleted_at')->where('type', 2)->count();

        $fkCounts = [];
        foreach (self::PRODUCT_ID_TABLES as $t) {
            if (Schema::hasTable($t)) {
                $fkCounts[$t] = DB::table($t)->whereNotNull('product_id')->count();
            }
        }
        foreach (self::PRODUCT_MORPH_TABLES as [$t, $typeCol, $idCol]) {
            if (Schema::hasTable($t)) {
                $fkCounts[$t . " ({$idCol})"] = DB::table($t)->where($typeCol, self::PRODUCT_MORPH)->count();
            }
        }

        // products.hi_ten_stock_code self-FK
        $fkCounts['products (hi_ten_stock_code)'] = DB::table('products')->whereNotNull('hi_ten_stock_code')->count();

        // sale_enquiries.product_service_interested
        if (Schema::hasTable('sale_enquiries')) {
            $fkCounts['sale_enquiries (product_service_interested)'] =
                DB::table('sale_enquiries')->whereNotNull('product_service_interested')->count();
        }

        return [
            'trashedProducts' => $trashedProducts,
            'trashedAttachments' => $trashedAttachments,
            'liveProducts' => $liveProducts,
            'fgCount' => $fgCount,
            'rmCount' => $rmCount,
            'fkCounts' => $fkCounts,
        ];
    }

    private function printPreview(array $p): void
    {
        $this->info('Renumber preview');
        $this->line("  Trashed to purge: products={$p['trashedProducts']}, attachments={$p['trashedAttachments']}");
        $this->line("  Products to renumber: {$p['liveProducts']}  (FG={$p['fgCount']}, RM={$p['rmCount']})");
        $this->line("    → new ids 1..{$p['liveProducts']} (FG 1..{$p['fgCount']}, RM " . ($p['fgCount'] + 1) . ".." . $p['liveProducts'] . ")");

        $attLive = DB::table('attachments')->whereNull('deleted_at')->count();
        $this->line("  Attachments to renumber: {$attLive} (after purge) → new ids 1..{$attLive}");

        $this->line('  FK tables that will be updated:');
        foreach ($p['fkCounts'] as $label => $n) {
            $this->line(sprintf('    - %-55s %d', $label, $n));
        }
        $this->newLine();
    }

    // ─── Phase 0: purge trashed ───────────────────────────────────────

    private function purgeTrashed(): void
    {
        // 0a — trashed products. Today this is 0, but handle defensively.
        $trashedProductIds = DB::table('products')->whereNotNull('deleted_at')->pluck('id')->all();

        if (! empty($trashedProductIds)) {
            $this->info('0a. Hard-deleting trashed products (' . count($trashedProductIds) . ')');
            $this->cascadeDeleteProductIds($trashedProductIds);
            $this->deleteAndLog('products (trashed)', fn () => DB::table('products')
                ->whereIn('id', $trashedProductIds)->delete());
        } else {
            $this->line('0a. No trashed products to purge.');
        }

        // 0b — trashed attachments (leaf).
        $trashedAttCount = DB::table('attachments')->whereNotNull('deleted_at')->count();
        if ($trashedAttCount > 0) {
            $this->info("0b. Hard-deleting trashed attachments ({$trashedAttCount})");
            $this->deleteAndLog('attachments (trashed)', fn () => DB::table('attachments')
                ->whereNotNull('deleted_at')->delete());
        } else {
            $this->line('0b. No trashed attachments to purge.');
        }
    }

    private function cascadeDeleteProductIds(array $ids): void
    {
        // Mirror of ImportRawMaterials delete order.
        $morph = self::PRODUCT_MORPH;

        $saleProductIds = Schema::hasTable('sale_products')
            ? DB::table('sale_products')->whereIn('product_id', $ids)->pluck('id')->all()
            : [];

        if (! empty($saleProductIds)) {
            if (Schema::hasTable('sale_product_children')) {
                DB::table('sale_product_children')->whereIn('sale_product_id', $saleProductIds)->delete();
            }
            if (Schema::hasTable('delivery_order_products')) {
                DB::table('delivery_order_products')->whereIn('sale_product_id', $saleProductIds)->delete();
            }
        }

        foreach (['sale_products', 'production_milestone_materials', 'production_milestone_materials_preview',
                  'material_use_products', 'grn', 'promotions', 'product_children', 'product_costs',
                  'product_selling_prices', 'product_milestones', 'classification_code_product',
                  'factory_raw_materials'] as $t) {
            if (Schema::hasTable($t)) {
                DB::table($t)->whereIn('product_id', $ids)->delete();
            }
        }

        foreach (self::PRODUCT_MORPH_TABLES as [$t, $typeCol, $idCol]) {
            if (Schema::hasTable($t)) {
                DB::table($t)->where($typeCol, $morph)->whereIn($idCol, $ids)->delete();
            }
        }
    }

    // ─── Phase 1–3: renumber products ─────────────────────────────────

    private function renumberProducts(): void
    {
        $this->info('1. Building products id map');
        $oldIds = DB::table('products')
            ->orderBy('type')
            ->orderBy('id')
            ->pluck('id')
            ->all();

        if (empty($oldIds)) {
            $this->warn('  No live products — skipping renumber.');
            return;
        }

        // old_id → new_id
        $map = [];
        foreach ($oldIds as $i => $oldId) {
            $map[$oldId] = $i + 1;
        }

        // Short-circuit if already contiguous 1..N.
        if ($oldIds[0] === 1 && end($oldIds) === count($oldIds)) {
            $this->line('  products.id already contiguous 1..' . count($oldIds) . ' — skipping.');
            return;
        }

        $this->info('2. Shifting products + all product-referencing columns by +' . self::OFFSET);
        $this->shiftAllProductRefs();

        $this->info('3. Applying final product ids (iterating ' . count($map) . ' rows)');
        $bar = $this->output->createProgressBar(count($map));
        $bar->start();

        foreach ($map as $oldId => $newId) {
            $shifted = $oldId + self::OFFSET;

            // Parent
            DB::table('products')->where('id', $shifted)->update(['id' => $newId]);

            // Self-FK
            DB::table('products')->where('hi_ten_stock_code', $shifted)->update(['hi_ten_stock_code' => $newId]);

            // product_id FK tables
            foreach (self::PRODUCT_ID_TABLES as $t) {
                if (Schema::hasTable($t)) {
                    DB::table($t)->where('product_id', $shifted)->update(['product_id' => $newId]);
                }
            }

            // sale_enquiries.product_service_interested
            if (Schema::hasTable('sale_enquiries')) {
                DB::table('sale_enquiries')
                    ->where('product_service_interested', $shifted)
                    ->update(['product_service_interested' => $newId]);
            }

            // Morph tables
            foreach (self::PRODUCT_MORPH_TABLES as [$t, $typeCol, $idCol]) {
                if (Schema::hasTable($t)) {
                    DB::table($t)
                        ->where($typeCol, self::PRODUCT_MORPH)
                        ->where($idCol, $shifted)
                        ->update([$idCol => $newId]);
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('  Renumbered ' . count($map) . ' products.');
    }

    private function shiftAllProductRefs(): void
    {
        DB::table('products')->update(['id' => DB::raw('id + ' . self::OFFSET)]);
        DB::table('products')
            ->whereNotNull('hi_ten_stock_code')
            ->update(['hi_ten_stock_code' => DB::raw('hi_ten_stock_code + ' . self::OFFSET)]);

        foreach (self::PRODUCT_ID_TABLES as $t) {
            if (Schema::hasTable($t)) {
                DB::table($t)
                    ->whereNotNull('product_id')
                    ->update(['product_id' => DB::raw('product_id + ' . self::OFFSET)]);
            }
        }

        if (Schema::hasTable('sale_enquiries')) {
            DB::table('sale_enquiries')
                ->whereNotNull('product_service_interested')
                ->update(['product_service_interested' => DB::raw('product_service_interested + ' . self::OFFSET)]);
        }

        foreach (self::PRODUCT_MORPH_TABLES as [$t, $typeCol, $idCol]) {
            if (Schema::hasTable($t)) {
                DB::table($t)
                    ->where($typeCol, self::PRODUCT_MORPH)
                    ->update([$idCol => DB::raw($idCol . ' + ' . self::OFFSET)]);
            }
        }
    }

    // ─── Phase 4: renumber attachments ────────────────────────────────

    private function renumberAttachments(): void
    {
        $this->info('4. Renumbering attachments.id');
        $oldIds = DB::table('attachments')->orderBy('id')->pluck('id')->all();

        if (empty($oldIds)) {
            $this->line('  No attachments — skipping.');
            return;
        }
        if ($oldIds[0] === 1 && end($oldIds) === count($oldIds)) {
            $this->line('  attachments.id already contiguous 1..' . count($oldIds) . ' — skipping.');
            return;
        }

        // Shift
        DB::table('attachments')->update(['id' => DB::raw('id + ' . self::OFFSET)]);

        $bar = $this->output->createProgressBar(count($oldIds));
        $bar->start();
        foreach ($oldIds as $i => $oldId) {
            DB::table('attachments')
                ->where('id', $oldId + self::OFFSET)
                ->update(['id' => $i + 1]);
            $bar->advance();
        }
        $bar->finish();
        $this->newLine();
        $this->info('  Renumbered ' . count($oldIds) . ' attachments.');
    }

    // ─── Phase 5: auto-increment reset + summary ──────────────────────

    private function resetAutoIncrements(): void
    {
        foreach (['products', 'attachments'] as $t) {
            $max = (int) DB::table($t)->max('id');
            DB::statement("ALTER TABLE `{$t}` AUTO_INCREMENT = " . ($max + 1));
        }
    }

    private function printSummary(): void
    {
        $products = DB::table('products')->selectRaw('MIN(id) mn, MAX(id) mx, COUNT(*) c')->first();
        $att = DB::table('attachments')->selectRaw('MIN(id) mn, MAX(id) mx, COUNT(*) c')->first();

        $this->newLine();
        $this->info('Renumber complete.');
        $this->line("  products:    min={$products->mn}  max={$products->mx}  count={$products->c}");
        $this->line("  attachments: min={$att->mn}  max={$att->mx}  count={$att->c}");
    }

    private function deleteAndLog(string $label, \Closure $run): void
    {
        $n = $run();
        $this->line(sprintf('  ✓ %-35s %d deleted', $label, $n));
    }
}
