<?php

namespace Database\Seeders;

use App\Models\Attachment;
use App\Models\Product;
use Illuminate\Database\Seeder;
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
        $basePath = base_path('../');
        $folders = [
            $basePath . 'SPARE PART PHOTOS 1',
            $basePath . 'SPARE PART PHOTOS 2',
            $basePath . 'SPARE PART PHOTOS 3',
            $basePath . 'SPARE PART PHOTOS 4',
            $basePath . 'SPARE PART PHOTOS 5',
        ];

        // Build SKU lookup: lowercase SKU => [product IDs]
        $spareParts = Product::where('is_sparepart', true)->select('id', 'sku')->get();
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

                // Normalize filename to SKU
                $baseName = pathinfo($filename, PATHINFO_FILENAME);
                $baseName = trim($baseName);
                // Map characters: ! -> /, ' -> "
                $normalized = str_replace(['!', "'"], ['/', '"'], $baseName);
                // Strip trailing -N suffix (multi-photo indicator)
                $skuName = preg_replace('/[-_ ](\d+)$/', '', $normalized);
                $skuKey = mb_strtolower(trim($skuName));

                if (!isset($skuMap[$skuKey])) {
                    $folderName = basename($folder);
                    $unmatched[$skuName] = $unmatched[$skuName] ?? $folderName;
                    continue;
                }

                $productIds = $skuMap[$skuKey];

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
        $this->command->info("  Unmatched photo names: " . count($unmatched));

        if (count($unmatched) > 0) {
            $unmatchedFile = base_path('../unmatched_spare_part_photos.txt');
            ksort($unmatched);
            $lines = [];
            foreach ($unmatched as $name => $folderName) {
                $lines[] = "[{$folderName}] {$name}";
            }
            File::put($unmatchedFile, implode(PHP_EOL, $lines) . PHP_EOL);
            $this->command->warn("Unmatched photos written to: {$unmatchedFile}");
        }

        // ── MFG / Finished Goods Photos ──
        $mfgFolder = $basePath . 'MFG';
        if (!File::isDirectory($mfgFolder)) {
            $this->command->warn("MFG folder not found: {$mfgFolder}");
            return;
        }

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

        $mfgFiles = File::files($mfgFolder);

        foreach ($mfgFiles as $file) {
            $filename = $file->getFilename();
            $extension = strtolower($file->getExtension());

            if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp'])) {
                continue;
            }

            $baseName = trim(pathinfo($filename, PATHINFO_FILENAME));
            // Strip trailing -N suffix (multi-photo indicator)
            $skuName = preg_replace('/[-_ ](\d+)$/', '', $baseName);
            $skuKey = mb_strtolower(trim($skuName));

            if (!isset($mfgSkuMap[$skuKey])) {
                $mfgUnmatched[$skuName] = $mfgUnmatched[$skuName] ?? 'MFG';
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

        if (count($mfgUnmatched) > 0) {
            $unmatchedMfgFile = base_path('../unmatched_mfg_photos.txt');
            ksort($mfgUnmatched);
            $mfgLines = [];
            foreach ($mfgUnmatched as $name => $folderName) {
                $mfgLines[] = "[{$folderName}] {$name}";
            }
            File::put($unmatchedMfgFile, implode(PHP_EOL, $mfgLines) . PHP_EOL);
            $this->command->warn("Unmatched MFG photos written to: {$unmatchedMfgFile}");
        }
    }
}
