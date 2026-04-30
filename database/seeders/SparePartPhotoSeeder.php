<?php

namespace Database\Seeders;

use App\Models\Attachment;
use App\Models\Product;
use App\Services\ProductMergeService;
use App\Services\SparePartPhotoOverrides;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class SparePartPhotoSeeder extends Seeder
{
    public function run(bool $dryRun = false): void
    {
        // Support: DRY_RUN=1 php artisan db:seed --class=SparePartPhotoSeeder
        if (env('DRY_RUN')) {
            $dryRun = true;
        }

        if ($dryRun) {
            $this->command->info('*** DRY RUN MODE — no files will be copied or records created ***');
        }
        $basePath = base_path('../photos/');
        $folders = [
            // $basePath . 'SPARE PART PHOTOS 1',
            // $basePath . 'SPARE PART PHOTOS 2',
            // $basePath . 'SPARE PART PHOTOS 3',
            // $basePath . 'SPARE PART PHOTOS 4',
            // $basePath . 'SPARE PART PHOTOS 5',
            $basePath . 'SPARE PART PHOTOS 6',
        ];

        // Build SKU lookup: lowercase SKU => [product IDs]
        $spareParts = Product::where('type', Product::TYPE_RAW_MATERIAL)->select('id', 'sku')->get();
        $skuMap = [];
        foreach ($spareParts as $part) {
            $key = mb_strtolower(trim($part->sku));
            $skuMap[$key][] = $part->id;
        }

        // Ensure destination directory exists
        $destDir = storage_path('app/' . Attachment::PRODUCT_PATH);
        if (!File::isDirectory($destDir)) {
            File::makeDirectory($destDir, 0755, true);
        }

        // Auto-reset: wipe previous product attachments + copied files so re-runs start clean.
        if (!$dryRun) {
            $deleted = Attachment::where('object_type', Product::class)->delete();
            foreach (File::files($destDir) as $existing) {
                File::delete($existing->getPathname());
            }
            $this->command->info("  Reset: deleted {$deleted} existing product attachments and cleared {$destDir}");
        } else {
            $existingCount = Attachment::where('object_type', Product::class)->count();
            $this->command->line("  [DRY RUN] Would delete {$existingCount} existing product attachments and clear {$destDir}");
        }

        $totalCopied = 0;
        $totalAttachments = 0;
        $skipped = 0;
        $unmatched = [];
        $deletedByOverride = 0;

        $overrides = SparePartPhotoOverrides::map();

        foreach ($folders as $folder) {
            if (!File::isDirectory($folder)) {
                $this->command->warn("Folder not found: {$folder}");
                continue;
            }

            $files = File::files($folder);

            foreach ($files as $file) {
                $filename = $file->getFilename();
                $extension = strtolower($file->getExtension());

                if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp'])) {
                    continue;
                }

                $baseName = pathinfo($filename, PATHINFO_FILENAME);

                // 1. Explicit overrides (from sp-rm-feedback.txt).
                // Try both the raw filename AND the normalized form as the
                // override key, since the override map is authored against
                // the SKU-style characters (`/`, `"`, `*`) while filenames on
                // disk use the safe replacements (`!`, `'`, `@`).
                $normalizedBase = ProductMergeService::normalizeFromPhotoFilename($baseName);
                $rawKey = mb_strtolower(trim($baseName));
                $normKey = mb_strtolower(trim($normalizedBase));
                $targets = $overrides[$rawKey] ?? $overrides[$normKey] ?? null;
                $productIds = null;
                $skuName = $baseName;

                if ($targets !== null) {
                    if ($targets === [SparePartPhotoOverrides::DELETE]) {
                        $deletedByOverride++;
                        continue;
                    }
                    $productIds = [];
                    foreach ($targets as $targetSku) {
                        $k = mb_strtolower(trim($targetSku));
                        if (isset($skuMap[$k])) {
                            $productIds = array_merge($productIds, $skuMap[$k]);
                        }
                    }
                    $productIds = array_values(array_unique($productIds));
                    $skuName = implode(' + ', $targets);
                }

                // 2. Normalizer candidates (!-/-@-*-'-" + _→/ + _→" + strip trailing -N).
                if ($productIds === null || empty($productIds)) {
                    foreach (ProductMergeService::photoFilenameCandidates($baseName) as $candidate) {
                        $k = mb_strtolower(trim($candidate));
                        if (isset($skuMap[$k])) {
                            $productIds = $skuMap[$k];
                            $skuName = $candidate;
                            break;
                        }
                    }
                }

                if ($productIds === null || empty($productIds)) {
                    $folderName = basename($folder);
                    $unmatched[$skuName] = $unmatched[$skuName] ?? $folderName;
                    continue;
                }

                foreach ($productIds as $productId) {
                    // Check if this exact image already exists for this product (idempotent)
                    $existingCheck = Attachment::where([
                        'object_type' => Product::class,
                        'object_id' => $productId,
                        'src' => $filename,
                    ])->exists();

                    if ($existingCheck) {
                        $skipped++;
                        continue;
                    }

                    if ($dryRun) {
                        $this->command->line("  [DRY RUN] Would assign {$filename} -> Product #{$productId}");
                        $totalAttachments++;
                        continue;
                    }

                    // Copy file with unique name to avoid collisions
                    $uniqueName = Str::uuid() . '.' . $extension;
                    File::copy($file->getPathname(), $destDir . '/' . $uniqueName);
                    $totalCopied++;

                    Attachment::create([
                        'object_type' => Product::class,
                        'object_id' => $productId,
                        'src' => $uniqueName,
                    ]);
                    $totalAttachments++;
                }
            }
        }

        $this->command->info(($dryRun ? '[DRY RUN] ' : '') . "Spare Part Photo Seeder Complete:");
        $this->command->info("  Attachments created: {$totalAttachments}");
        $this->command->info("  Files copied: {$totalCopied}");
        $this->command->info("  Skipped (already exist): {$skipped}");
        $this->command->info("  Deleted by override: {$deletedByOverride}");
        $this->command->info("  Unmatched photo names: " . count($unmatched));

        // Complementary: spare-part / raw-material SKUs that ended up with
        // zero attachments (no photo file exists for them on disk).
        $attachedProductIds = Attachment::where('object_type', Product::class)
            ->whereIn('object_id', $spareParts->pluck('id'))
            ->pluck('object_id')
            ->unique();

        $unattached = $spareParts->reject(fn ($p) => $attachedProductIds->contains($p->id));
        $this->command->info("  SKUs without any photo: " . $unattached->count());

        if (!$dryRun) {
            $this->writeMergedReport(
                base_path('../sp_rm_photo_report.txt'),
                'Spare Part / Raw Material Photo Report',
                $unmatched,
                $unattached->pluck('id')->all()
            );
        }

        // ── Finished Goods Photos ──
        $fgParent = $basePath . 'FINISHED GOOD PHOTOS - 14.04.2026';
        if (!File::isDirectory($fgParent)) {
            $this->command->warn("Finished goods folder not found: {$fgParent}");
            return;
        }

        $mfgFolders = collect(File::directories($fgParent));

        // Build finished-goods SKU lookup
        $finishedGoods = Product::withoutGlobalScopes()
            ->where('type', Product::TYPE_PRODUCT)
            ->select('id', 'sku')
            ->get();
        $mfgSkuMap = [];
        foreach ($finishedGoods as $fg) {
            $key = mb_strtolower(trim($fg->sku));
            $mfgSkuMap[$key][] = $fg->id;
        }

        $mfgCopied = 0;
        $mfgAttachments = 0;
        $mfgSkipped = 0;
        $mfgUnmatched = [];

        $mfgFiles = $mfgFolders->flatMap(fn ($dir) => File::files($dir));

        foreach ($mfgFiles as $file) {
            $filename = $file->getFilename();
            $extension = strtolower($file->getExtension());

            if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp'])) {
                continue;
            }

            $rawBase = pathinfo($filename, PATHINFO_FILENAME);
            $baseName = ProductMergeService::normalizeFromPhotoFilename($rawBase);
            $skuName = $baseName;
            $skuKey = mb_strtolower(trim($skuName));

            // Try every normalizer candidate (handles stripped `"` etc.).
            if (!isset($mfgSkuMap[$skuKey])) {
                foreach (ProductMergeService::photoFilenameCandidates($rawBase) as $candidate) {
                    $k = mb_strtolower(trim($candidate));
                    if (isset($mfgSkuMap[$k])) {
                        $skuName = $candidate;
                        $skuKey = $k;
                        break;
                    }
                }
            }
            if (!isset($mfgSkuMap[$skuKey])) {
                $stripped = preg_replace('/[-_ ](\d+)$/', '', $baseName);
                $strippedKey = mb_strtolower(trim($stripped));
                if (isset($mfgSkuMap[$strippedKey])) {
                    $skuName = $stripped;
                    $skuKey = $strippedKey;
                }
            }

            if (!isset($mfgSkuMap[$skuKey])) {
                $mfgUnmatched[$skuName] = $mfgUnmatched[$skuName] ?? basename($file->getPath());
                continue;
            }

            $productIds = $mfgSkuMap[$skuKey];

            foreach ($productIds as $productId) {
                $existingCheck = Attachment::where([
                    'object_type' => Product::class,
                    'object_id' => $productId,
                    'src' => $filename,
                ])->exists();

                if ($existingCheck) {
                    $mfgSkipped++;
                    continue;
                }

                if ($dryRun) {
                    $this->command->line("  [DRY RUN] Would assign MFG {$filename} -> Product #{$productId}");
                    $mfgAttachments++;
                    continue;
                }

                $uniqueName = Str::uuid() . '.' . $extension;
                File::copy($file->getPathname(), $destDir . '/' . $uniqueName);
                $mfgCopied++;

                Attachment::create([
                    'object_type' => Product::class,
                    'object_id' => $productId,
                    'src' => $uniqueName,
                ]);
                $mfgAttachments++;
            }
        }

        $this->command->info(($dryRun ? '[DRY RUN] ' : '') . "MFG Finished Goods Photo Seeder Complete:");
        $this->command->info("  Attachments created: {$mfgAttachments}");
        $this->command->info("  Files copied: {$mfgCopied}");
        $this->command->info("  Skipped (already exist): {$mfgSkipped}");
        $this->command->info("  Unmatched photo names: " . count($mfgUnmatched));

        // Same pattern as SP/RM: list finished-goods SKUs with no photo.
        $mfgAttachedIds = Attachment::where('object_type', Product::class)
            ->whereIn('object_id', $finishedGoods->pluck('id'))
            ->pluck('object_id')
            ->unique();
        $mfgUnattached = $finishedGoods->reject(fn ($fg) => $mfgAttachedIds->contains($fg->id));
        $this->command->info("  SKUs without any photo: " . $mfgUnattached->count());

        if (!$dryRun) {
            $this->writeMergedReport(
                base_path('../finished_good_photo_report.txt'),
                'Finished Good Photo Report',
                $mfgUnmatched,
                $mfgUnattached->pluck('id')->all()
            );
        }
    }

    /**
     * Write a single merged report that lists both photo files with no
     * matching SKU ("unmatched_file") and SKUs with no photo file
     * ("no_photo") — each row prefixed with the reason.
     *
     * @param  array<string,string>  $unmatchedFiles  base-filename → folder
     * @param  array<int,int>        $unattachedIds   product ids with no photo
     */
    private function writeMergedReport(string $path, string $title, array $unmatchedFiles, array $unattachedIds): void
    {
        ksort($unmatchedFiles);

        $skuRows = empty($unattachedIds) ? collect() : DB::table('products')
            ->leftJoin('inventory_categories', 'products.inventory_category_id', '=', 'inventory_categories.id')
            ->whereIn('products.id', $unattachedIds)
            ->orderByRaw('CASE WHEN products.type = 2 THEN products.is_sparepart ELSE NULL END')
            ->orderBy('inventory_categories.name')
            ->orderBy('products.sku')
            ->get(['products.sku', 'products.type', 'products.is_sparepart', 'products.model_desc', 'inventory_categories.name as cat']);

        $lines = [];
        $lines[] = "# {$title}";
        $lines[] = '# generated: ' . now()->toDateTimeString();
        $lines[] = '# reasons:';
        $lines[] = '#   unmatched_file = photo file exists on disk but no product SKU matches';
        $lines[] = '#   no_photo       = product SKU exists but no photo file attached';
        $lines[] = '# summary: unmatched_file=' . count($unmatchedFiles) . '  no_photo=' . $skuRows->count();
        $lines[] = '';

        foreach ($unmatchedFiles as $name => $folderName) {
            $lines[] = sprintf('[unmatched_file] [%s] %s', $folderName, $name);
        }

        if ($skuRows->isNotEmpty() && !empty($unmatchedFiles)) {
            $lines[] = '';
        }

        foreach ($skuRows as $r) {
            $tag = match (true) {
                (int) $r->type === Product::TYPE_PRODUCT => 'FG',
                (bool) $r->is_sparepart                  => 'SP',
                default                                  => 'RM',
            };
            $lines[] = sprintf('[no_photo]       [%s] [%s] %s — %s', $tag, $r->cat ?? '-', $r->sku, $r->model_desc);
        }

        File::put($path, implode(PHP_EOL, $lines) . PHP_EOL);
        $this->command->warn("Report written to: {$path}");
    }
}
