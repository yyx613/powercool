<?php

namespace App\Exports;

use App\Models\Production;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;

class ProductionExport implements FromView, WithStyles
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
        $productions = Production::get();

        return view('export.production', [
            'productions' => $productions,
        ]);

    }
}
