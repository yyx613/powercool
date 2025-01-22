<?php

namespace App\Exports;

use App\Models\Product;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;

class ProductExport implements FromView, WithStyles
{
    protected $is_product;

    public function __construct($is_product)
    {
        $this->is_product = $is_product;
    }

    public function styles($sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
            ],
        ];
    }

    public function view(): View
    {
        $products = Product::where('type', $this->is_product ? Product::TYPE_PRODUCT : Product::TYPE_RAW_MATERIAL)->get();

        return view('export.product', [
            'products' => $products,
        ]);

    }
}
