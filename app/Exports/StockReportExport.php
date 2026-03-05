<?php

namespace App\Exports;

use App\Models\Product;
use App\Models\ProductionMilestoneMaterial;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StockReportExport implements FromCollection, WithMapping, WithHeadings, WithStyles, WithStrictNullComparison
{
    protected $data;
    protected $productionMsMaterial;
    protected $product;

    public function __construct($data)
    {
        $this->data = $data;
        $this->productionMsMaterial = new ProductionMilestoneMaterial();
        $this->product = new Product();
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
        return ['Product Name', 'Product Code', 'Available Qty', 'Reserved Qty', 'On Hold Qty'];
    }

    public function map($data): array {
        return [
            $data->model_desc,
            $data->sku,
            $data->warehouseAvailableStock(),
            $data->warehouseReservedStock(),
            $data->warehouseOnHoldStock(),
        ];
    }
}
