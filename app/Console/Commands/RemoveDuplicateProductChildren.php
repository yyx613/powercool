<?php

namespace App\Console\Commands;

use App\Models\DeliveryOrderProductChild;
use App\Models\GRN;
use App\Models\ProductChild;
use App\Models\ProductionMilestoneMaterial;
use App\Models\SaleProductChild;
use App\Models\Scopes\BranchScope;
use App\Models\TaskMilestoneInventory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RemoveDuplicateProductChildren extends Command
{
    protected $signature = 'product-children:dedupe
                            {--apply : Actually soft-delete the chosen duplicates (default is dry-run)}
                            {--report= : Write the full report to this TXT path}';

    protected $description = 'Identify and soft-delete duplicate ProductChild rows (same sku, transferred_from IS NULL). Keeps the row that is referenced by downstream records; otherwise keeps the oldest. Skips pairs where both sides are referenced.';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        $reportPath = $this->option('report');

        $this->line($apply ? '[APPLY] Soft-deleting duplicates.' : '[DRY-RUN] No changes will be made. Use --apply to commit.');

        $groups = ProductChild::withoutGlobalScope(BranchScope::class)
            ->whereNull('deleted_at')
            ->whereNull('transferred_from')
            ->select('sku')
            ->groupBy('sku')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('sku');

        if ($groups->isEmpty()) {
            $this->info('No duplicate SKUs found.');
            return self::SUCCESS;
        }

        $this->info("Found {$groups->count()} duplicate SKU groups.");

        $rows = [];
        $kept = 0;
        $deleted = 0;
        $skipped = 0;

        foreach ($groups as $sku) {
            $dups = ProductChild::withoutGlobalScope(BranchScope::class)
                ->whereNull('deleted_at')
                ->whereNull('transferred_from')
                ->where('sku', $sku)
                ->orderBy('created_at')
                ->orderBy('id')
                ->get();

            $refs = $dups->mapWithKeys(fn ($pc) => [$pc->id => $this->referenceCount($pc->id)]);
            $referencedIds = $refs->filter(fn ($n) => $n > 0)->keys();

            if ($referencedIds->count() > 1) {
                foreach ($dups as $pc) {
                    $rows[] = $this->row($pc, 'SKIP_BOTH_REFERENCED', $refs[$pc->id]);
                }
                $skipped += $dups->count();
                continue;
            }

            // Keep: referenced one if any, else the oldest (first by created_at, id).
            $keepId = $referencedIds->first() ?? $dups->first()->id;

            foreach ($dups as $pc) {
                if ($pc->id === $keepId) {
                    $rows[] = $this->row($pc, 'KEEP', $refs[$pc->id]);
                    $kept++;
                } else {
                    $rows[] = $this->row($pc, $apply ? 'DELETE' : 'WOULD_DELETE', $refs[$pc->id]);
                    if ($apply) {
                        DB::transaction(function () use ($pc) {
                            ProductChild::withoutGlobalScope(BranchScope::class)
                                ->where('id', $pc->id)
                                ->update(['deleted_at' => now()]);
                        });
                    }
                    $deleted++;
                }
            }
        }

        $this->table(
            ['id', 'sku', 'product_id', 'parent_sku', 'created_at', 'created_by', 'references', 'action'],
            array_map(fn ($r) => [
                $r['id'],
                $r['sku'],
                $r['product_id'],
                $r['parent_sku'],
                $r['created_at'],
                $r['created_by'],
                $r['references'],
                $r['action'],
            ], $rows)
        );

        $this->info("Kept: {$kept}");
        $this->info(($apply ? 'Deleted' : 'Would delete').": {$deleted}");
        $this->info("Skipped (both referenced): {$skipped}");

        if ($reportPath) {
            $this->writeTxtReport($reportPath, $rows, $kept, $deleted, $skipped, $apply);
            $this->info("Report written to {$reportPath}");
        }

        return self::SUCCESS;
    }

    private function writeTxtReport(string $path, array $rows, int $kept, int $deleted, int $skipped, bool $apply): void
    {
        $headers = ['id', 'sku', 'product_id', 'parent_sku', 'created_at', 'created_by', 'references', 'action'];
        $widths = array_fill_keys($headers, 0);
        foreach ($headers as $h) {
            $widths[$h] = strlen($h);
        }
        foreach ($rows as $r) {
            foreach ($headers as $h) {
                $widths[$h] = max($widths[$h], strlen((string) $r[$h]));
            }
        }

        $pad = fn (string $v, string $h) => str_pad($v, $widths[$h], ' ', STR_PAD_RIGHT);
        $sep = '+';
        foreach ($headers as $h) {
            $sep .= str_repeat('-', $widths[$h] + 2).'+';
        }

        $fh = fopen($path, 'w');
        fwrite($fh, "Duplicate ProductChildren Report\n");
        fwrite($fh, 'Generated: '.now()->format('Y-m-d H:i:s')."\n");
        fwrite($fh, 'Mode: '.($apply ? 'APPLY' : 'DRY-RUN')."\n");
        fwrite($fh, "Summary: kept={$kept}, ".($apply ? 'deleted' : 'would_delete')."={$deleted}, skipped_both_referenced={$skipped}\n\n");

        fwrite($fh, $sep."\n");
        $headerLine = '|';
        foreach ($headers as $h) {
            $headerLine .= ' '.$pad($h, $h).' |';
        }
        fwrite($fh, $headerLine."\n".$sep."\n");

        foreach ($rows as $r) {
            $line = '|';
            foreach ($headers as $h) {
                $line .= ' '.$pad((string) $r[$h], $h).' |';
            }
            fwrite($fh, $line."\n");
        }
        fwrite($fh, $sep."\n");
        fclose($fh);
    }

    private function referenceCount(int $productChildId): int
    {
        $count = 0;
        $count += SaleProductChild::where('product_children_id', $productChildId)->count();
        $count += DeliveryOrderProductChild::where('product_children_id', $productChildId)->count();
        $count += ProductionMilestoneMaterial::where('product_child_id', $productChildId)->count();
        $count += TaskMilestoneInventory::where('inventory_type', ProductChild::class)
            ->where('inventory_id', $productChildId)
            ->count();
        // transferred_from children pointing at this one
        $count += ProductChild::withoutGlobalScope(BranchScope::class)
            ->where('transferred_from', $productChildId)
            ->count();
        return $count;
    }

    private function row(ProductChild $pc, string $action, int $references): array
    {
        $parentSku = DB::table('products')->where('id', $pc->product_id)->value('sku');
        $createdBy = $this->guessCreatedBy($pc);

        return [
            'id' => $pc->id,
            'sku' => $pc->sku,
            'product_id' => $pc->product_id,
            'parent_sku' => $parentSku,
            'created_at' => (string) $pc->created_at,
            'created_by' => $createdBy,
            'references' => $references,
            'action' => $action,
        ];
    }

    // Prefer a real created_by column when present (ProductChild::createdBy()
    // relation). Otherwise fall back to the closest prior GRN row matching
    // product_id as a best-effort attribution.
    private function guessCreatedBy(ProductChild $pc): string
    {
        if (Schema::hasColumn('product_children', 'created_by') && $pc->created_by) {
            $user = $pc->createdBy()->first();
            return $user ? "{$user->name} (user#{$user->id})" : "user#{$pc->created_by}";
        }

        $grn = GRN::withoutGlobalScope(BranchScope::class)
            ->where('product_id', $pc->product_id)
            ->where('created_at', '<=', $pc->created_at)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($grn === null) {
            return 'unknown';
        }
        return "GRN#{$grn->id}(branch={$grn->branch_id},sku={$grn->sku})";
    }
}
