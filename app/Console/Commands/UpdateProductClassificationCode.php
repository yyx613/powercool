<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateProductClassificationCode extends Command
{
    protected $signature = 'app:update-product-classification-code
        {--code=022 : Classification code to apply to every product}
        {--dry-run : Show what would change without writing}
        {--force : Skip the YES confirmation prompt}';

    protected $description = 'Re-syncs every non-soft-deleted product (both finished goods and raw materials/spare parts, across all branches) so that its only classification code is the one given by --code (default 022).';

    private const CHUNK = 500;

    public function handle(): int
    {
        $code = trim((string) $this->option('code'));
        if ($code === '') {
            $this->error('--code cannot be empty.');
            return 1;
        }

        $target = DB::table('classification_codes')->where('code', $code)->first(['id', 'code']);
        if ($target === null) {
            $existing = DB::table('classification_codes')->count();
            $this->error("Classification code '{$code}' not found in classification_codes table ({$existing} codes total). Aborting — create the row first if this is a new code.");
            return 1;
        }
        $targetId = (int) $target->id;
        $this->info("Target classification code: {$target->code} (id={$targetId})");

        $productIds = DB::table('products')
            ->whereNull('deleted_at')
            ->pluck('id')
            ->map(fn ($v) => (int) $v)
            ->all();

        $totalProducts = count($productIds);
        if ($totalProducts === 0) {
            $this->info('No live products to update.');
            return 0;
        }
        $this->info("Live products: {$totalProducts}");

        $alreadyAttached = [];
        foreach (array_chunk($productIds, self::CHUNK) as $chunk) {
            $rows = DB::table('classification_code_product')
                ->whereIn('product_id', $chunk)
                ->where('classification_code_id', $targetId)
                ->pluck('product_id');
            foreach ($rows as $pid) {
                $alreadyAttached[(int) $pid] = true;
            }
        }

        $detachCount = 0;
        foreach (array_chunk($productIds, self::CHUNK) as $chunk) {
            $detachCount += DB::table('classification_code_product')
                ->whereIn('product_id', $chunk)
                ->where('classification_code_id', '!=', $targetId)
                ->count();
        }

        $attachIds = array_values(array_filter($productIds, fn ($id) => ! isset($alreadyAttached[$id])));
        $attachCount = count($attachIds);

        $noopCount = 0;
        foreach (array_chunk($productIds, self::CHUNK) as $chunk) {
            $rows = DB::table('classification_code_product')
                ->whereIn('product_id', $chunk)
                ->select('product_id', DB::raw('COUNT(*) as c'), DB::raw('MAX(classification_code_id) as max_id'), DB::raw('MIN(classification_code_id) as min_id'))
                ->groupBy('product_id')
                ->get();
            foreach ($rows as $r) {
                if ((int) $r->c === 1 && (int) $r->max_id === $targetId && (int) $r->min_id === $targetId) {
                    $noopCount++;
                }
            }
        }

        $this->newLine();
        $this->info('Plan:');
        $this->line("  Will attach {$target->code} to: {$attachCount} products");
        $this->line("  Will detach (other codes from these products): {$detachCount} pivot rows");
        $this->line("  Already exactly [{$target->code}] (no-op): {$noopCount} products");

        if ($this->option('dry-run')) {
            $this->warn('DRY RUN — no changes were made.');
            return 0;
        }

        if ($attachCount === 0 && $detachCount === 0) {
            $this->info('Nothing to update.');
            return 0;
        }

        if (! $this->option('force')) {
            $msg = "Type YES to apply: attach {$attachCount}, detach {$detachCount} pivot rows";
            if ($this->ask($msg) !== 'YES') {
                $this->info('Aborted.');
                return 0;
            }
        }

        $now = now();

        try {
            DB::transaction(function () use ($productIds, $targetId, $attachIds, $now) {
                foreach (array_chunk($productIds, self::CHUNK) as $chunk) {
                    DB::table('classification_code_product')
                        ->whereIn('product_id', $chunk)
                        ->where('classification_code_id', '!=', $targetId)
                        ->delete();
                }

                foreach (array_chunk($attachIds, self::CHUNK) as $chunk) {
                    $rows = array_map(fn ($pid) => [
                        'product_id' => $pid,
                        'classification_code_id' => $targetId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ], $chunk);
                    DB::table('classification_code_product')->insert($rows);
                }
            });
        } catch (\Throwable $e) {
            $this->error('Update failed: ' . $e->getMessage());
            return 1;
        }

        $this->info("Done. Attached {$attachCount} products to {$target->code}; removed {$detachCount} other-code pivot rows.");
        return 0;
    }
}
