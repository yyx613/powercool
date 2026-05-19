<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StockCardReportExport implements FromArray, WithStyles, WithStrictNullComparison
{
    protected $items;
    protected string $companyHeader;
    protected ?string $brandHeader;

    public function __construct(array $items, string $companyHeader, ?string $brandHeader = null)
    {
        $this->items = $items;
        $this->companyHeader = $companyHeader;
        $this->brandHeader = $brandHeader;
    }

    public function array(): array
    {
        $rows = [
            ['Company: ' . $this->companyHeader],
        ];
        if ($this->brandHeader !== null) {
            $rows[] = ['Brand: ' . $this->brandHeader];
        }
        $rows[] = [
            'Item Code',
            'Item Description',
            'Company',
            'Brand',
            'Location',
            'UOM',
            'Date',
            'Type',
            'Doc. No.',
            'Description',
            'In/Out Qty',
            'Bal Qty',
            'Unit Cost',
            'Total Cost',
            'Bal Cost',
        ];

        foreach ($this->items as $item) {
            $product = $item['product'];
            $companyLabel = $item['company_label'] ?? 'Unassigned';
            $brandLabel = $item['brand_label'] ?? 'Unassigned';
            foreach ($item['locations'] as $loc) {
                $rows[] = [
                    $product->sku,
                    $product->model_desc,
                    $companyLabel,
                    $brandLabel,
                    $loc['location_label'],
                    $loc['uom'],
                    '',
                    'B/F',
                    '',
                    'Brought Forward',
                    '',
                    $loc['bf_qty'],
                    '',
                    '',
                    number_format($loc['bf_cost'] ?? 0, 4, '.', ''),
                ];

                foreach ($loc['movements'] as $mv) {
                    $rows[] = [
                        $product->sku,
                        $product->model_desc,
                        $companyLabel,
                        $brandLabel,
                        $loc['location_label'],
                        $loc['uom'],
                        $mv['date'],
                        $mv['type'],
                        $mv['doc_no'],
                        $mv['description'],
                        $mv['in_out_qty'],
                        $mv['bal_qty'],
                        number_format($mv['unit_cost'] ?? 0, 4, '.', ''),
                        number_format($mv['total_cost'] ?? 0, 4, '.', ''),
                        number_format($mv['bal_cost'] ?? 0, 4, '.', ''),
                    ];
                }
            }
        }

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        if ($this->brandHeader === null) {
            return [
                1 => ['font' => ['bold' => true, 'size' => 14]],
                2 => ['font' => ['bold' => true]],
            ];
        }
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            2 => ['font' => ['bold' => true, 'size' => 12]],
            3 => ['font' => ['bold' => true]],
        ];
    }
}
