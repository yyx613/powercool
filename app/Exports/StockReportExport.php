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

    public function map($data): array
    {
        $is_raw_material = $data->is_sparepart !== null && $data->is_sparepart == false;

        if ($is_raw_material) {
            $reserved_stock = $this->productionMsMaterial::where('product_id', $data->id)->where('on_hold', false)->sum('qty');
            $on_hold_stock = $this->productionMsMaterial::where('product_id', $data->id)->where('on_hold', true)->sum('qty');
            $available_stock = $data->qty - $reserved_stock - $on_hold_stock;
        }

        return [
            $data->model_name,
            $data->sku,
            $is_raw_material ? $available_stock : $this->product->warehouseAvailableStock($data->id),
            $is_raw_material ? $reserved_stock : $this->product->warehouseReservedStock($data->id),
            $is_raw_material ? $on_hold_stock : $this->product->warehouseOnHoldStock($data->id),
        ];
    }
}
