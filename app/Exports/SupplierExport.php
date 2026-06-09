<?php

namespace App\Exports;

use App\Models\Supplier;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;

class SupplierExport implements FromView, WithStyles
{
    /**
     * Pre-filtered supplier query, built from the same filters as the list view
     * so the export only contains the records the user is currently viewing.
     */
    protected $query;

    public function __construct($query = null)
    {
        $this->query = $query ?? Supplier::query();
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
        $suppliers = $this->query->get();

        return view('export.supplier', [
            'suppliers' => $suppliers,
        ]);

    }
}
