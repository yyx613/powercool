<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StockCardReportExport implements WithEvents
{
    private const NEG_COLOR = 'FFB91C1C';
    private const ITEM_FILL = 'FFF3F3F3';
    private const QTY_FORMAT = '#,##0';
    private const COST_FORMAT = '#,##0.0000';

    protected array $items;
    protected string $companyHeader;
    protected ?string $brandHeader;
    protected ?string $startDate;
    protected ?string $endDate;
    protected string $companyGroupLabel;
    protected string $brandLabel;
    protected string $userName;

    public function __construct(
        array $items,
        string $companyHeader,
        ?string $brandHeader = null,
        ?string $startDate = null,
        ?string $endDate = null,
        string $companyGroupLabel = 'All',
        string $brandLabel = 'All',
        string $userName = ''
    ) {
        $this->items = $items;
        $this->companyHeader = $companyHeader;
        $this->brandHeader = $brandHeader;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->companyGroupLabel = $companyGroupLabel;
        $this->brandLabel = $brandLabel;
        $this->userName = $userName;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $this->build($event->sheet->getDelegate());
            },
        ];
    }

    private function build(Worksheet $sheet): void
    {
        $this->setupPage($sheet);

        $row = 1;
        $row = $this->writeTopMeta($sheet, $row);
        $row = $this->writeTitleBlock($sheet, $row);
        $row = $this->writeColumnHeaders($sheet, $row);
        $row = $this->writeBody($sheet, $row);
        $this->writeFooter($sheet, $row + 2);
    }

    private function setupPage(Worksheet $sheet): void
    {
        $sheet->getColumnDimension('A')->setWidth(12);
        $sheet->getColumnDimension('B')->setWidth(18);
        $sheet->getColumnDimension('C')->setWidth(36);
        $sheet->getColumnDimension('D')->setWidth(10);
        $sheet->getColumnDimension('E')->setWidth(12);
        $sheet->getColumnDimension('F')->setWidth(12);
        $sheet->getColumnDimension('G')->setWidth(14);
        $sheet->getColumnDimension('H')->setWidth(16);

        $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);
    }

    private function writeTopMeta(Worksheet $sheet, int $row): int
    {
        $meta = 'Date : ' . now()->format('d-m-Y H:i:s') . "\nUser ID : " . $this->userName;
        $sheet->mergeCells("F{$row}:H{$row}");
        $sheet->setCellValue("F{$row}", $meta);
        $sheet->getStyle("F{$row}")->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_RIGHT)
            ->setVertical(Alignment::VERTICAL_TOP)
            ->setWrapText(true);
        $sheet->getRowDimension($row)->setRowHeight(30);
        return $row + 1;
    }

    private function writeTitleBlock(Worksheet $sheet, int $row): int
    {
        $sheet->mergeCells("A{$row}:H{$row}");
        $sheet->setCellValue("A{$row}", 'Stock Card Finished Good');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(18);
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getRowDimension($row)->setRowHeight(26);
        $row++;

        $sheet->mergeCells("A{$row}:H{$row}");
        $sheet->setCellValue("A{$row}", 'Company: ' . $this->companyHeader);
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(11);
        $row++;

        if ($this->brandHeader !== null) {
            $sheet->mergeCells("A{$row}:H{$row}");
            $sheet->setCellValue("A{$row}", 'Brand: ' . $this->brandHeader);
            $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(11);
            $row++;
        }

        return $row + 1;
    }

    private function writeColumnHeaders(Worksheet $sheet, int $row): int
    {
        $headers = [
            'A' => "Location\nDate",
            'B' => "UOM\nType Doc. No.",
            'C' => "Batch No.\nDesc.",
            'D' => 'In/Out Qty',
            'E' => "B/F Qty\nBal Qty",
            'F' => 'Unit Cost',
            'G' => 'Total Cost',
            'H' => "B/F Cost\nBal Cost",
        ];
        foreach ($headers as $col => $label) {
            $sheet->setCellValue("{$col}{$row}", $label);
        }

        $range = "A{$row}:H{$row}";
        $sheet->getStyle($range)->getFont()->setBold(true);
        $sheet->getStyle($range)->getAlignment()
            ->setWrapText(true)
            ->setVertical(Alignment::VERTICAL_TOP);
        $sheet->getStyle($range)->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle($range)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
        foreach (['D', 'E', 'F', 'G', 'H'] as $col) {
            $sheet->getStyle("{$col}{$row}")->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        }
        $sheet->getRowDimension($row)->setRowHeight(28);

        return $row + 1;
    }

    private function writeBody(Worksheet $sheet, int $row): int
    {
        if (empty($this->items)) {
            $sheet->mergeCells("A{$row}:H{$row}");
            $sheet->setCellValue("A{$row}", 'No stock movements found for the selected period.');
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getRowDimension($row)->setRowHeight(28);
            return $row + 1;
        }

        foreach ($this->items as $item) {
            $row = $this->writeItemRow($sheet, $row, $item);
            foreach ($item['locations'] as $loc) {
                $row = $this->writeLocationRow($sheet, $row, $loc);
                foreach ($loc['movements'] as $mv) {
                    $row = $this->writeMovementRow($sheet, $row, $mv);
                }
                $row = $this->writeClosingRow($sheet, $row, $loc);
            }
        }

        return $row;
    }

    private function writeItemRow(Worksheet $sheet, int $row, array $item): int
    {
        $product = $item['product'];
        $companyLabel = $item['company_label'] ?? 'Unassigned';
        $brandLabel = $item['brand_label'] ?? 'Unassigned';

        $sheet->mergeCells("A{$row}:B{$row}");
        $sheet->setCellValue("A{$row}", 'Item : ' . $product->sku);
        $sheet->mergeCells("C{$row}:F{$row}");
        $sheet->setCellValueExplicit("C{$row}", (string) $product->model_desc, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->mergeCells("G{$row}:H{$row}");
        $sheet->setCellValue("G{$row}", 'Company : ' . $companyLabel . ' | Brand : ' . $brandLabel);

        $range = "A{$row}:H{$row}";
        $sheet->getStyle($range)->getFont()->setBold(true);
        $sheet->getStyle($range)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB(self::ITEM_FILL);
        $sheet->getStyle($range)->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN);

        return $row + 1;
    }

    private function writeLocationRow(Worksheet $sheet, int $row, array $loc): int
    {
        $sheet->setCellValue("A{$row}", strtoupper($loc['location_label']));
        $sheet->setCellValue("B{$row}", strtoupper($loc['uom']));
        $this->writeQty($sheet, "E{$row}", $loc['bf_qty']);
        $this->writeCost($sheet, "H{$row}", $loc['bf_cost']);

        $sheet->getStyle("A{$row}:H{$row}")->getFont()->setBold(true);
        $this->rightAlignNumericCols($sheet, $row);

        return $row + 1;
    }

    private function writeMovementRow(Worksheet $sheet, int $row, array $mv): int
    {
        $sheet->setCellValueExplicit("A{$row}", (string) $mv['date'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValue("B{$row}", trim($mv['type'] . '  ' . $mv['doc_no']));
        $sheet->setCellValueExplicit("C{$row}", (string) ($mv['description'] ?? ''), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $this->writeQty($sheet, "D{$row}", $mv['in_out_qty']);
        $this->writeQty($sheet, "E{$row}", $mv['bal_qty']);
        $this->writeCost($sheet, "F{$row}", $mv['unit_cost']);
        $this->writeCost($sheet, "G{$row}", $mv['total_cost']);
        $this->writeCost($sheet, "H{$row}", $mv['bal_cost']);

        $this->rightAlignNumericCols($sheet, $row);

        return $row + 1;
    }

    private function writeClosingRow(Worksheet $sheet, int $row, array $loc): int
    {
        $sheet->mergeCells("A{$row}:C{$row}");
        $sheet->setCellValue("A{$row}", 'Closing Balance');
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        $this->writeQty($sheet, "E{$row}", $loc['closing_qty']);
        $this->writeCost($sheet, "H{$row}", $loc['closing_cost']);

        $sheet->getStyle("A{$row}:H{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}:H{$row}")->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN);
        $this->rightAlignNumericCols($sheet, $row);

        return $row + 1;
    }

    private function writeFooter(Worksheet $sheet, int $row): void
    {
        $sheet->mergeCells("A{$row}:H{$row}");
        $sheet->getStyle("A{$row}:H{$row}")->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN);
        $sheet->setCellValue("A{$row}", 'End of Report');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true);
        $row++;

        $lines = [
            'Report Criteria:',
            'Filter Options: From Date: ' . ($this->startDate ?: '—') . ' To Date: ' . ($this->endDate ?: '—'),
            'Company: ' . $this->companyGroupLabel,
            'Brand: ' . $this->brandLabel,
            'Movement Types: GR (Goods Receipt), DO (Delivery Order), AS (Stock Assembly), ST (Stock Transfer)',
            'Include Zero Balance: No',
            'Note: AS/ST cost columns reflect current Product.cost (not historical at time of movement).',
        ];
        foreach ($lines as $line) {
            $sheet->mergeCells("A{$row}:H{$row}");
            $sheet->setCellValue("A{$row}", $line);
            $row++;
        }
    }

    private function writeQty(Worksheet $sheet, string $cell, $value): void
    {
        $value = (int) $value;
        $sheet->setCellValue($cell, $value);
        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode(self::QTY_FORMAT);
        if ($value < 0) {
            $sheet->getStyle($cell)->getFont()->getColor()->setARGB(self::NEG_COLOR);
        }
    }

    private function writeCost(Worksheet $sheet, string $cell, $value): void
    {
        $value = (float) $value;
        $sheet->setCellValue($cell, $value);
        $sheet->getStyle($cell)->getNumberFormat()->setFormatCode(self::COST_FORMAT);
        if ($value < 0) {
            $sheet->getStyle($cell)->getFont()->getColor()->setARGB(self::NEG_COLOR);
        }
    }

    private function rightAlignNumericCols(Worksheet $sheet, int $row): void
    {
        foreach (['D', 'E', 'F', 'G', 'H'] as $col) {
            $sheet->getStyle("{$col}{$row}")->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        }
    }
}
