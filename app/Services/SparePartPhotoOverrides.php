<?php

namespace App\Services;

/**
 * Manual filename → SKU overrides for the spare-part / raw-material photo
 * seeder. Compiled from `sp-rm-feedback.txt` in the repo root.
 *
 * Keys are the photo filename base (no extension), lowercased + trimmed.
 * Values are either:
 *   ['__delete__'] — skip the photo (user marked it for deletion / wrong code)
 *   [sku, ...]     — attach the photo to every listed SKU (1..N products)
 *
 * Automatic char-substitution rules (handled by
 * ProductMergeService::normalizeFromPhotoFilename) are NOT duplicated here —
 * this file only contains renames that can't be recovered deterministically.
 *
 * Sources merged (file wins on conflict):
 *   1. Hardcoded map below — historical entries for PHOTOS 1–5.
 *   2. `sp-rm-feedback.txt` — live, editable file at the repo root. Each line:
 *        [unmatched_file] [FOLDER] LHS = RHS
 *        [unmatched_file] [FOLDER] LHS = __DELETE__      (skip the photo)
 *        [unmatched_file] [FOLDER] LHS = RHS1 + RHS2     (one-to-many)
 */
class SparePartPhotoOverrides
{
    public const DELETE = '__delete__';

    private const FEEDBACK_PATH = __DIR__ . '/../../../sp-rm-feedback.txt';

    /**
     * @return array<string, array<int, string>>
     */
    public static function map(): array
    {
        $hardcoded = self::hardcodedMap();
        $fromFile = self::parseFeedbackFile();

        // File wins on conflict so edits to sp-rm-feedback.txt take effect
        // without a PHP change.
        return array_merge($hardcoded, $fromFile);
    }

    /**
     * Parse `sp-rm-feedback.txt` lines of the form:
     *   [unmatched_file] [SPARE PART PHOTOS N] LHS = RHS
     * Returns keyed map compatible with `map()`.
     *
     * @return array<string, array<int, string>>
     */
    private static function parseFeedbackFile(): array
    {
        if (! is_file(self::FEEDBACK_PATH)) {
            return [];
        }

        $out = [];
        $handle = fopen(self::FEEDBACK_PATH, 'r');
        if ($handle === false) {
            return [];
        }

        while (($line = fgets($handle)) !== false) {
            $line = rtrim($line, "\r\n");
            if ($line === '' || str_starts_with(ltrim($line), '#')) {
                continue;
            }
            // Only process unmatched_file rows — no_photo rows have no replacement target.
            if (! preg_match('/^\[unmatched_file\]\s*\[[^\]]+\]\s*(.+?)\s*=\s*(.+)$/', $line, $m)) {
                continue;
            }
            $lhs = trim($m[1]);
            $rhs = trim($m[2]);
            if ($lhs === '' || $rhs === '') {
                continue;
            }

            $key = mb_strtolower(trim($lhs));

            if (strcasecmp($rhs, '__DELETE__') === 0 || strcasecmp($rhs, 'DELETE') === 0) {
                $out[$key] = [self::DELETE];
                continue;
            }

            // Support `RHS1 + RHS2` one-to-many syntax (space-padded `+`).
            $targets = array_values(array_filter(array_map('trim', preg_split('/\s+\+\s+/', $rhs))));
            if (! empty($targets)) {
                $out[$key] = $targets;
            }
        }
        fclose($handle);

        return $out;
    }

    /**
     * @return array<string, array<int, string>>
     */
    private static function hardcodedMap(): array
    {
        // Build once with original-case SKUs so the data is readable.
        $raw = [
            // ── Spare part renames (photo file → target SKU) ────────────────
            'ACC-VKZ168CU' => ['COMP-VKZ168CU'],
            'ASC-617 X 266 C' => ['ASC-617 X 266 [1DT]', 'ASC-617 X 266 C'],
            'BCV-1D-1' => ['BCV-1D'],
            'COIL-100-7.5P-400-1' => ['COIL-100-7.5P-400'],
            'COIL-100-7.5P-400-2' => ['COIL-100-7.5P-400'],
            'COIL-2@50-4.5P-L900' => ['COIL-2*50-4.5P-L900'],
            'COIL-2@50-4.5P-L900-1' => ['COIL-2*50-4.5P-L900'],
            'COIL-2@50-4.5P-L900-2' => ['COIL-2*50-4.5P-L900'],
            'COIL-3/8 X 4R X 5TH' => ['COIL-3/8" X 4R X 5TH'],
            'COIL-3!8 x 4R x 5TH' => ['COIL-3/8" X 4R X 5TH'],
            'COIL-3/8 X 4R X 6TH-(1IN1OUT)' => ['COIL-3/8" x 4R x 6TH-(1IN1O)'],
            'COIL-3!8" x 4R x 6TH-(1IN1O)' => ['COIL-3/8" x 4R x 6TH-(1IN1O)'],
            'COIL-3/8 X 4R X 6TH-(1IN1OUT)-1' => ['COIL-3/8" x 4R x 6TH-(1IN1O)'],
            'COIL-3/8 X 4R X 6TH-(1IN1OUT)-2' => ['COIL-3/8" x 4R x 6TH-(1IN1O)'],
            'COIL-3/8 X 4TH X 1400MM' => ['COIL-3/8" x 4TH x 1400MM'],
            'COIL-3/8 X 4TH X 1400MM-1' => ['COIL-3/8" x 4TH x 1400MM'],
            'COIL-3/8 X 4TH X 1400MM-2' => ['COIL-3/8" x 4TH x 1400MM'],
            'COIL-3/8 X 4TH X 1400MM-3' => ['COIL-3/8" x 4TH x 1400MM'],
            'COIL-3/8 X 5R' => ['COIL-3/8" x 5R'],
            'COIL-3/8 X 5R-1' => ['COIL-3/8" x 5R'],
            'COIL-3/8 X 5R-2' => ['COIL-3/8" x 5R'],
            'COIL-3/8*4R* 550MM (3DFB)' => ['COIL-3/8*4R* 550MM [3DFB]'],
            'COIL-3/8*4R* 550MM (3DFB)-1' => ['COIL-3/8*4R* 550MM [3DFB]'],
            'COIL-3/8*4R* 550MM (3DFB)-2' => ['COIL-3/8*4R* 550MM [3DFB]'],
            'COIL-5 X 4 X 1200' => ['COIL-5 x 4 x 1280'],
            'COMP-L58CU1' => [self::DELETE],
            'COMP-NEU2178GK' => ['ACC-NEU2178GK'],

            'COND-3 X 10 X 360-L' => ['COND-3 x 10 x 360'],
            'COND-3 X 10 X 360-R' => ['COND-3 x 10 x 360'],
            'COND-3 X 12 X 360-L' => ['COND-3 x 12 x 360'],
            'COND-3 X 12 X 360-R' => ['COND-3 x 12 x 360'],
            'COND-4 X 10 X 320-L' => ['COND-4 x 10 x 320'],
            'COND-4 X 10 X 320-R' => ['COND-4 x 10 x 320'],
            'COND-4 X 10 X 580-L' => ['COND-4 x 10 x 580'],
            'COND-4 X 10 X 580-R' => ['COND-4 x 10 x 580'],
            'COND-4 X 10 X 800-L' => ['COND-4 x 10 x 800'],
            'COND-4 X 10 X 800-R' => ['COND-4 x 10 x 800'],

            'CP T - 1 4' => ['CP T - 1/4'],
            'CR-8" PLT' => ["CR-8' PLT"],
            'DIG-HK 183' => ['DIG-HK183'],
            'DIG-SF401' => ['DIG-SF401L'],
            'DIG-SF401-1' => ['DIG-SF401L'],
            'DIMMER 3 3 600W' => ['DIMMER 3*3 600W'],
            'DIV STICK-19' => ['DIV STICK-19"'],
            'DIV STICK-21' => ['DIV STICK-21"'],
            'DOOR STOPPER' => ['STICKER-DR ST'],
            'DR SPR 2.3@ X 14.4 X 16.5' => ['DR SPR 2.3*14.4*16.5'],
            'DRI BIT 5/32' => ['DRI BIT - 5/32'],
            'DRILL BIT 7/32' => ['DRI BIT - 7/32'],
            'DS-500X40' => ['DS-500*40'],
            'FM-FSY12038HA2BL' => ['VFAN 12038'],
            'FN-11' => ['FN-11"'],
            'FN-12' => ['FN-12"'],
            'FN-8_' => ['FN-8"'],
            'FN-8' => ['FN-8"'],
            // Additional mappings derived from existing SKUs following the
            // same NO-BORDER / rename conventions in sp-rm-feedback.txt.
            'ADJ LEG 4' => ['ADJ LEG 4"'],
            'ADJ LEG 6' => ['ADJ LEG 6"'],
            'ADJ LEG 7' => ['ADJ LEG 7"'],
            'ADJ LEG 8' => ['ADJ LEG 8"'],
            'LATCH-3' => ['LATCH-3"'],
            'LED-1800M' => ['LED-1800MM'],
            'VFAN NET SQ' => ['FN-VFAN NET SQ'],
            'STICKER AI+INVERTER' => ['STICKER-AI INVERTER'],
            // Alternate (no-quote) filenames for COIL-3/8" x 4R x 6TH-(1IN1O).
            'COIL-3/8 x 4R x 6TH-(1IN1O)' => ['COIL-3/8" x 4R x 6TH-(1IN1O)'],
            'COIL-3/8 x 4R x 6TH-(1IN1O)-1' => ['COIL-3/8" x 4R x 6TH-(1IN1O)'],
            'COIL-3/8 x 4R x 6TH-(1IN1O)-2' => ['COIL-3/8" x 4R x 6TH-(1IN1O)'],
            // Typo corrections + alternate-form filenames.
            'S.VLAVE-FDF3408' => ['S.VALVE-FDF3408'],
            'SCR-12 X 1 3/4' => ['SCR-12 X 1 3/4"'],
            'SHEL-17 X 22' => ['SHEL-17  X 22'], // SKU has two spaces between 17 and X
            'SHEL-21 6/8 X 21 B' => ['SHEL-21"6/8 X 21 B'],
            'FO-1/2x2x4' => ['FO-1/2x2x4'], // normalizer handles !→/
            'FO-2 1/2x2x4' => ['FO-21/2X2X4'],
            'FS-10' => ['FS-10"'],
            'G.BRACKET 14' => ['G.BRACKET 14"'],
            'G.BRACKET 16' => ['G.BRACKET 16"'],
            'G.BRACKET 18' => ['G.BRACKET 18"'],
            'GI-0.40 X 4" X 8"' => ["GI-0.40 X 4' X 8'"],
            'GI-0.50 X 4" X 8"' => ["GI-0.50 X 4' X 8'"],
            'GI-0.60 X 4" X 8"' => ["GI-0.60 X 4' X 8'"],

            'GLS-533 X 1418 X 3 CG' => ['GLS-533*1418*3 CG'],
            'GLS-533 X 1494 KG' => ['GLS-533*1494 KG'],
            'GLS-533 X 900 CG' => ['GLS-533*900 CG'],
            'GLS-546 X 1396 X 3 CG' => ['GLS-546*1396*3 CG'],
            'GLS-552 X 1403 CG' => ['GLS-552*1403 CG'],
            'GLS-552 X 1403 KG' => ['GLS-552*1403 KG'],
            'GLS-724 X 1479 CG' => ['GLS-724*1479 CG'],
            'GLS-LOW E 533 X 1494' => ['GLS-LOW E 533*1494'],

            'HDL-WHI RECESSED [DISPLAY]' => ['HDL-WHI RECCESSED [DISPLAY]'],
            'HEA-U' => ['HEA-U-CU'],

            'LED SC-FX 1200MM 2700K 12W' => ['LED-1200MM'],
            'LED SC-FX 1200MM 2700K 12W-1' => ['LED-1200MM'],
            'LED SC-FX 600MM 2700K 6W' => ['LED-600MM'],
            'LED SC-FX 600MM 2700K 6W-1' => ['LED-600MM'],
            'LED SC-FX 900MM 2700K 9W' => ['LED-900MM'],
            'LED SC-FX 900MM 2700K 9W-1' => ['LED-900MM'],

            'MSP-1.5X4X8' => ['MSP-1.5*4*8'],
            'MSP-2.5X4X8' => ['MSP-2.5*4*8'],
            'MSP-3X4X8' => ['MSP-3*4*8'],
            'OTH-TRUNKING 2 X 2' => ['OTH-TRUNKING 2*2'],

            'PLAS-WPIPE (1)' => ['PLAS-WPIPE'],
            'PLY-12MM X 4" X 8"' => ["PLY-12MM X 4' X 8'"],
            'PLY-12MM X 4" X 8" - 1' => ["PLY-12MM X 4' X 8'"],
            'PLY-15MM X 4" X 8"' => ["PLY-15MM X 4' X 8'"],
            'PLY-15MM X 4" X 8" - 1' => ["PLY-15MM X 4' X 8'"],
            'PLY-18MM X 4" X 8"' => ["PLY-18MM X 4' X 8'"],
            'PLY-18MM X 4" X 8" - 1' => ["PLY-18MM X 4' X 8'"],
            'PLY-25MM X 4" X 8"' => ["PLY-25MM X 4' X 8'"],
            'PLY-25MM X 4" X 8" - 1' => ["PLY-25MM X 4' X 8'"],
            'PLY-3MM X 4" X 8"' => ["PLY-3MM X 4' X 8'"],
            'PLY-3MM X 4" X 8" - 1' => ["PLY-3MM X 4' X 8'"],
            'PLY-6MM X 4" X 8"' => ["PLY-6MM X 4' X 8'"],
            'PLY-6MM X 4" X 8" - 1' => ["PLY-6MM X 4' X 8'"],
            'PLY-9MM X 4" X 8"' => ["PLY-9MM X 4' X 8'"],
            'PLY-9MM X 4" X 8" - 1' => ["PLY-9MM X 4' X 8'"],

            'RELAY 8 PIN' => ['ACC-RELAY 8PINS'],
            'S.FILM 4' => ['S.FILM 4"'],
            'S.FILM 4-1' => ['S.FILM 4"'],
            'S/S-3@63@6' => ['S/S-3*63*6'],

            'SCR-1 2" X 5"' => ['SCR-1/2"X5"'],
            'SCR-10 X 1 2' => ['SCR-10 X 1/2'],
            'SCR-10 X 5 8' => ['SCR-10 X 5/8'],
            'SCR-12 X 1 1 2' => ['SCR-12 X 1 1/2'],
            'SCR-12 X 1 3 4' => ['SCR-12 X 1 3/4"'],
            'SCR-3_4 X 4' => ['SCR-3/4 X 4'],
            'SCR-6 X 3 4' => ['SCR-6 X 3/4'],
            'SCR-6 X 3 8' => ['SCR-6 X 3/8'],
            'SCR-8 X 3 4' => ['SCR-8 X 3/4'],
            'SCR-M5 X 8MM SS' => ['SCR-M5 X 8MM S/S'],

            'SENSOR COVER' => ['ACC-DIG-SENS COV'],
            'SHEL-20 1_2 X 22' => ['SHEL-20 1/2 X 22'],
            'SHEL-20 6 8 X 22' => ['SHEL-20 6/8 X 22'],
            'SHEL-21 1_2 X 20 6_8' => ['SHEL-21 1/2 X 20 6/8'],
            'SHEL-21 6_8 X 21 DIV' => ['SHEL-21 6/8 X 21 DIV'],
            'SHEL-21 X 20 1 2' => ['SHEL-21 X 20 1/2'],
            'SHEL-21"6_8 X 21 B' => ['SHEL-21"6/8 X 21 B'],
            'SHEL-533 X 390 DIV' => ['SHEL-533*390 DIV'],
            'SHEL-584 X 390 DIV' => ['SHEL-584*390 DIV'],

            'ST-0.45 X 4 X 8 430 2B' => ['ST-0.45*4*8 430 2B'],
            'ST-0.5 X 4 X 8 430 BA PVC' => ['ST-0.5*4*8 430 BA PVC'],

            'STICKER FRAGILE' => ['STICKER-FRAGILE'],
            'STICKER I-MAX' => ['STICKER-IMAX'],
            'STICKER MAINTENANCE' => ['STICKER-MT V2'],
            'STICKER PENGUIN IMAX' => [self::DELETE],
            'STICKER TARIK' => ['STICKER-PULL (L)', 'STICKER-PULL (R)'],
            'STICKER-1212 X 275' => ['STICKER-1212*275'],
            'STICKER-1832 X 275' => ['STICKER-1832*275'],
            'STICKER-3YEAR - +INV' => ['STICKER-3YEAR'],
            'STICKER-B.MSIA (2)' => ['STICKER-B.MSIA'],
            'STICKER-IMAX 65 X 27' => ['STICKER-IMAX 65*27'],
            'STICKER-IMAX BLUE 72 X 32' => ['STICKER-IMAX BLUE 72*32'],
            'Service Sticker 3% 65x65 mm' => ['STICKER-SERV'],

            'TOOLS-1_4BEN' => ['TOOLS-1/4BEN'],
            'TOOLS-3_8BEN' => ['TOOLS-3/8BEN'],
            'WIRE-2C 23 0.14' => ['WIRE-2C 23/0.14'],
            'WIRE-6MM BLACK' => ['WIRE-6MM BK'],
        ];

        // Lowercase the keys once so lookup is case-insensitive.
        $out = [];
        foreach ($raw as $k => $v) {
            $out[mb_strtolower(trim($k))] = $v;
        }
        return $out;
    }
}
