<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesReportExport implements FromCollection, WithMapping, WithHeadings, WithStyles
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data;
    }

    public function styles(Worksheet $sheet) {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function headings(): array {
        return ['Salesperson', 'Qty', 'Promo', 'Amount', 'Outstanding Amount'];
    }

    public function map($data): array
    {
        return [
            $data->saleperson,
            $data->sum_qty,
            number_format($data->sum_promo_amount, 2),
            number_format($data->sum_amount - $data->sum_promo_amount, 2),
            number_format($data->sum_amount - $data->sum_promo_amount - ($data->payment_amount ?? 0), 2),
        ];
    }
}
