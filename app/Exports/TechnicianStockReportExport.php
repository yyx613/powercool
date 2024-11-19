<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TechnicianStockReportExport implements FromCollection, WithMapping, WithHeadings, WithStyles
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
        return ['Technician', 'Task ID', 'Product For Replacement', 'Material Used Qty'];
    }

    public function map($data): array
    {
        return [
            $data->technician,
            $data->sku,
            $data->product_for_replacement,
            $data->material_used_qty,
        ];
    }
}
