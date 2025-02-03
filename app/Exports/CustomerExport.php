<?php

namespace App\Exports;

use App\Models\Customer;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;

class CustomerExport implements FromView, WithStyles
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
        $customers = Customer::with('locations')->get();

        return view('export.customer', [
            'customers' => $customers,
        ]);

    }
}
