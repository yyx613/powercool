<?php

namespace App\Services;

class ProductMergeService
{
    /**
     * Reverse the admin's symbol-safe filename substitutions to recover the
     * original SKU. Filenames cannot contain /, *, or " so the admin writes
     * them as !, @, and ' (or nothing) respectively.
     */
    public static function normalizeFromPhotoFilename(string $name): string
    {
        $normalized = trim($name);
        $normalized = strtr($normalized, [
            '!' => '/',
            '@' => '*',
            "'" => '"',
        ]);

        return $normalized;
    }

    /**
     * Ordered list of candidate SKU variants to try when matching a photo
     * filename against the product SKU map. The seeder tries each in order
     * and stops at the first hit.
     *
     * Variants cover the two recurring ambiguities from `sp-rm-feedback.txt`:
     *   - `"` is sometimes dropped in filenames ("NO NEED BORDER")
     *   - `_` substitutes for either `/` or `"` (filesystems don't accept `/`)
     *
     * @return string[] distinct candidates, already normalized by
     *                 normalizeFromPhotoFilename().
     */
    public static function photoFilenameCandidates(string $name): array
    {
        $base = self::normalizeFromPhotoFilename($name);

        $variants = [
            $base,
            str_replace('"', '', $base),
            str_replace('_', '/', $base),
            str_replace('_', '"', $base),
        ];

        // Also try each variant with a trailing "-N" photo-index stripped
        // (e.g. COIL-100-7.5P-400-1 → COIL-100-7.5P-400) to keep the existing
        // multi-photo fallback behaviour from SparePartPhotoSeeder.
        $extras = [];
        foreach ($variants as $v) {
            $stripped = preg_replace('/[-_ ](\d+)$/', '', $v);
            if ($stripped !== null && $stripped !== $v) {
                $extras[] = $stripped;
            }
        }

        return array_values(array_unique(array_merge($variants, $extras)));
    }
}
