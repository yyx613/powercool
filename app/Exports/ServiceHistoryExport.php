<?php

namespace App\Exports;

use App\Models\ProductChild;
use App\Models\TaskMilestoneInventory;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;

class ServiceHistoryExport implements FromView, WithStyles
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
        $tmi_ids = TaskMilestoneInventory::where('inventory_type', ProductChild::class)->pluck('inventory_id');

        $pcs = ProductChild::whereIn('id', $tmi_ids)->get();

        return view('export.service_history', [
            'pcs' => $pcs,
        ]);

    }
}
