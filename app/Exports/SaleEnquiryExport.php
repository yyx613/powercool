<?php

namespace App\Exports;

use App\Models\SaleEnquiry;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;

class SaleEnquiryExport implements FromView, WithStyles
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
        $enquiries = SaleEnquiry::with(['product', 'assignedUser', 'promotion', 'createdByUser'])
            ->orderBy('id', 'desc')
            ->get();

        return view('export.sale_enquiry', [
            'enquiries' => $enquiries,
        ]);
    }
}
