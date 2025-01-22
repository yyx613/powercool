<?php

namespace App\Exports;

use App\Models\Supplier;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;

class SupplierExport implements FromView, WithStyles
{
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
        $suppliers = Supplier::get();

        return view('export.supplier', [
            'suppliers' => $suppliers,
        ]);

    }
}
