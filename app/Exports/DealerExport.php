<?php

namespace App\Exports;

use App\Models\Dealer;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;

class DealerExport implements FromView, WithStyles
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
        $dealers = Dealer::get();

        return view('export.dealer', [
            'dealers' => $dealers,
        ]);

    }
}
