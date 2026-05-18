<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StockCardReportExport implements FromArray, WithHeadings, WithStyles, WithStrictNullComparison
{
    protected $items;

    public function __construct(array $items)
    {
        $this->items = $items;
    }

    public function headings(): array
    {
        return [
            'Item Code',
            'Item Description',
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
    }

    public function array(): array
    {
        $rows = [];

        foreach ($this->items as $item) {
            $product = $item['product'];
            foreach ($item['locations'] as $loc) {
                // B/F row
                $rows[] = [
                    $product->sku,
                    $product->model_desc,
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
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
