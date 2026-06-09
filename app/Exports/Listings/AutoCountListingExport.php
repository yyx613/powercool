<?php

namespace App\Exports\Listings;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Renders an AutoCount-style "Debtor / Creditor Listing" report by reusing the
 * original AutoCount .xls as a template.
 *
 * The template's header (company name, title, Date / User ID, column headers)
 * and its first sample record are kept verbatim. That first record acts as a
 * "prototype block": its per-cell styles, row heights and merge ranges are
 * captured, the sample rows are stripped, and the block is then cloned once per
 * record so the output is pixel-faithful to the original report.
 */
class AutoCountListingExport
{
    /** @var array<int, ListingRecord> */
    protected array $records;

    public function __construct(
        protected string $templatePath,
        protected ListingLayout $layout,
        array $records,
        protected ?string $dateText = null,
        protected ?string $userId = null,
        protected ?string $companyName = null,
        protected ?string $title = null,
    ) {
        $this->records = array_values($records);
    }

    public function spreadsheet(): Spreadsheet
    {
        $reader = IOFactory::createReaderForFile($this->templatePath);
        $spreadsheet = $reader->load($this->templatePath);
        $sheet = $spreadsheet->getActiveSheet();

        $this->fillHeader($sheet);
        $this->drawHeaderRule($sheet);

        $prototype = $this->capturePrototype($sheet);

        $this->stripSampleRecords($sheet);

        foreach ($this->records as $index => $record) {
            $startRow = $this->layout->firstDataRow + ($index * $this->layout->blockHeight);
            $this->writeBlock($sheet, $prototype, $startRow, $record);
        }

        return $spreadsheet;
    }

    public function save(string $path): void
    {
        // Written as .xlsx: a full debtor/creditor list runs to tens of
        // thousands of rows (≈9 per record), well past the old .xls (BIFF8)
        // 65,536-row ceiling. .xlsx opens identically in Excel.
        $writer = new Xlsx($this->spreadsheet());
        $writer->setPreCalculateFormulas(false);
        $writer->save($path);
    }

    /**
     * Strip the template's sample records, leaving only the header rows.
     *
     * The template is pre-formatted (merged + styled + per-row heights) all the
     * way down to its last sample record. Worksheet::removeRow() can't clear
     * that tail cleanly — when there are no rows below the deleted range to pull
     * up, it leaves cells in place — so we tear the data region down by hand:
     * unmerge, drop the cells, drop the row dimensions, then recompute the
     * cached sheet dimensions.
     */
    protected function stripSampleRecords(Worksheet $sheet): void
    {
        $first = $this->layout->firstDataRow;

        foreach ($sheet->getMergeCells() as $range) {
            $startRow = (int) preg_replace('/\D/', '', explode(':', $range)[0]);
            if ($startRow >= $first) {
                $sheet->unmergeCells($range);
            }
        }

        $cells = $sheet->getCellCollection();
        foreach ($cells->getCoordinates() as $coordinate) {
            $row = (int) preg_replace('/\D/', '', $coordinate);
            if ($row >= $first) {
                $cells->delete($coordinate);
            }
        }

        // No public API removes row-dimension objects below a row, and they
        // keep the sheet height pinned at the template's last sample row.
        $property = new \ReflectionProperty(Worksheet::class, 'rowDimensions');
        $property->setAccessible(true);
        $dimensions = $property->getValue($sheet);
        foreach (array_keys($dimensions) as $row) {
            if ((int) $row >= $first) {
                unset($dimensions[$row]);
            }
        }
        $property->setValue($sheet, $dimensions);

        $sheet->garbageCollect();
    }

    /**
     * Redraw the horizontal rule under the column-header band as a thin bottom
     * border (the template's original shape line is dropped by the .xls reader).
     */
    protected function drawHeaderRule(Worksheet $sheet): void
    {
        if ($this->layout->ruleRow === null) {
            return;
        }

        $row = $this->layout->ruleRow;
        $thin = \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN;

        $sheet->getStyle('A' . $row . ':' . $this->layout->ruleLastColumn . $row)
            ->getBorders()->getBottom()->setBorderStyle($thin);

        // A border set on a cell that is the non-anchor part of a vertical merge
        // (e.g. the TERMS K15:L17 block) won't render — it leaves a gap in the
        // rule. For any merge whose bottom edge lands on the rule row, border
        // the whole merge range so the line stays continuous.
        foreach ($sheet->getMergeCells() as $mergeRange) {
            if ((int) preg_replace('/\D/', '', explode(':', $mergeRange)[1]) === $row) {
                $sheet->getStyle($mergeRange)->getBorders()->getBottom()->setBorderStyle($thin);
            }
        }
    }

    /** Stamp the report date, exporting user and company into the template header. */
    protected function fillHeader(Worksheet $sheet): void
    {
        if ($this->dateText !== null && $this->layout->dateCell) {
            $sheet->setCellValue($this->layout->dateCell, $this->dateText);
        }
        if ($this->userId !== null && $this->layout->userCell) {
            $sheet->setCellValue($this->layout->userCell, $this->userId);
        }
        // Always overwrite the company title: the template ships with one company
        // hardcoded, so an unset company must clear it rather than mislead.
        if ($this->layout->companyCell) {
            $sheet->setCellValue($this->layout->companyCell, (string) $this->companyName);
        }
        // Override the report heading only when a caller asks (the dealer export
        // reuses the debtor template under the "Dealer Listing" title); otherwise
        // the template's own heading is kept.
        if ($this->title !== null && $this->layout->titleCell) {
            $sheet->setCellValue($this->layout->titleCell, $this->title);
        }
    }

    /**
     * Snapshot the prototype record's styling so it can be replayed for every
     * real record.
     *
     * Only the populated anchor cells carry styling in the template (the rest of
     * the block is empty spacer cells with no borders or fills), so we capture
     * styles for those cells alone. Replaying ~12 styled cells per block instead
     * of ~270 keeps memory sane on large exports (thousands of records).
     *
     * @return array{styles: array<string,int>, heights: array<int,float>, merges: array<int,array{0:int,1:int,2:int,3:int}>}
     */
    protected function capturePrototype(Worksheet $sheet): array
    {
        $first = $this->layout->firstDataRow;
        $last = $first + $this->layout->blockHeight - 1;

        $heights = [];
        for ($row = $first; $row <= $last; $row++) {
            $heights[$row - $first] = $sheet->getRowDimension($row)->getRowHeight();
        }

        $styles = [];
        foreach ($this->anchorCells() as [$letter, $rel]) {
            $styles["$letter:$rel"] = $sheet->getCell($letter . ($first + $rel))->getXfIndex();
        }

        $merges = [];
        foreach ($sheet->getMergeCells() as $range) {
            [$start, $end] = explode(':', $range);
            [$startCol, $startRow] = Coordinate::coordinateFromString($start);
            [$endCol, $endRow] = Coordinate::coordinateFromString($end);
            if ($startRow < $first || $startRow > $last) {
                continue;
            }
            $merges[] = [
                Coordinate::columnIndexFromString($startCol),
                $startRow - $first,
                Coordinate::columnIndexFromString($endCol),
                $endRow - $first,
            ];
        }

        return ['styles' => $styles, 'heights' => $heights, 'merges' => $merges];
    }

    /**
     * Flat list of every anchor cell [column letter, row offset] in a block,
     * including the address slots.
     *
     * @return array<int, array{0:string,1:int}>
     */
    protected function anchorCells(): array
    {
        $cells = [];
        foreach ($this->layout->anchors as $key => $anchor) {
            if ($key === 'address') {
                foreach ($anchor as $slot) {
                    $cells[] = $slot;
                }
            } elseif ($anchor !== null) {
                $cells[] = $anchor;
            }
        }

        return $cells;
    }

    protected function writeBlock(Worksheet $sheet, array $prototype, int $startRow, ListingRecord $record): void
    {
        // Replay row heights.
        foreach ($prototype['heights'] as $rel => $height) {
            $sheet->getRowDimension($startRow + $rel)->setRowHeight($height);
        }

        // Replay merges, offset to this block.
        foreach ($prototype['merges'] as [$startCol, $relStart, $endCol, $relEnd]) {
            $range = Coordinate::stringFromColumnIndex($startCol) . ($startRow + $relStart)
                . ':' . Coordinate::stringFromColumnIndex($endCol) . ($startRow + $relEnd);
            $sheet->mergeCells($range);
        }

        $this->writeValues($sheet, $prototype['styles'], $startRow, $record);
    }

    protected function writeValues(Worksheet $sheet, array $styles, int $startRow, ListingRecord $record): void
    {
        $a = $this->layout->anchors;

        $this->put($sheet, $styles, $a['code'], $startRow, $record->code);
        $this->put($sheet, $styles, $a['name'], $startRow, $record->name);
        $this->put($sheet, $styles, $a['area'], $startRow, $record->area);
        $this->put($sheet, $styles, $a['agent'], $startRow, $record->agent);
        $this->put($sheet, $styles, $a['terms'], $startRow, $record->terms);
        $this->put($sheet, $styles, $a['contact'], $startRow, $record->contact);
        $this->put($sheet, $styles, $a['phone'], $startRow, $record->phone());
        $this->put($sheet, $styles, $a['fax'], $startRow, $record->fax());

        // Address lines drop into their fixed slots, in order, until we run out
        // of either lines or slots.
        $lines = $record->addressLines();
        foreach ($a['address'] as $slotIndex => $slot) {
            $value = $lines[$slotIndex] ?? '';
            $this->put($sheet, $styles, $slot, $startRow, $value);
        }
    }

    /**
     * @param array<string,int>           $styles prototype xf indexes keyed "COL:rel"
     * @param array{0:string,1:int}|null  $anchor [column letter, relative row]
     */
    protected function put(Worksheet $sheet, array $styles, ?array $anchor, int $startRow, string $value): void
    {
        if ($anchor === null || $value === '') {
            return;
        }
        [$col, $rel] = $anchor;
        $cell = $sheet->getCell($col . ($startRow + $rel));
        $cell->setValueExplicit($value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

        // Reuse the prototype anchor's shared style index (font / alignment).
        if (isset($styles["$col:$rel"])) {
            $cell->setXfIndex($styles["$col:$rel"]);
        }

        // Stacked values (e.g. Phone 1 & 2) only break onto a second line in
        // Excel when the cell wraps; the merged anchor is tall enough to show it.
        if (str_contains($value, "\n")) {
            $sheet->getStyle($cell->getCoordinate())->getAlignment()->setWrapText(true);
        }
    }
}
