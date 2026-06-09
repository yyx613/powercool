<?php

namespace App\Exports;

use App\Models\Dealer;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;

class DealerExport implements FromView, WithStyles
{
    /**
     * Pre-filtered dealer query, built from the same filters as the list view
     * so the export only contains the records the user is currently viewing.
     */
    protected $query;

    public function __construct($query = null)
    {
        $this->query = $query ?? Dealer::query();
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
        $dealers = $this->query->get();

        return view('export.dealer', [
            'dealers' => $dealers,
        ]);

    }
}
