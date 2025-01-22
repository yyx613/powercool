<?php

namespace App\Exports;

use App\Models\Invoice;
use App\Models\Product;
use App\Models\ProductChild;
use App\Models\Sale;
use App\Models\Ticket;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;

class TicketExport implements FromView, WithStyles
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
        $tickets = Ticket::get();

        for ($i = 0; $i < count($tickets); $i++) {
            $so_inv = explode(',', $tickets[$i]->so_inv);
            $so_inv_type = explode(',', $tickets[$i]->so_inv_type);
            $product_id = explode(',', $tickets[$i]->product_id);
            $product_child_id = explode(',', $tickets[$i]->product_child_id);
            $so = [];
            $inv = [];

            for ($j = 0; $j < count($so_inv_type); $j++) {
                if ($so_inv_type[$j] == 'so') {
                    $so[] = $so_inv[$j];
                } elseif ($so_inv_type[$j] == 'inv') {
                    $inv[] = $so_inv[$j];
                }
            }
            $new_so_inv = Sale::withTrashed()->whereIn('id', $so)->pluck('sku');
            $new_so_inv->merge(Invoice::withTrashed()->whereIn('id', $inv)->pluck('sku'));
            $tickets[$i]->new_so_inv = implode(', ', $new_so_inv->toArray());
            $tickets[$i]->product = implode(', ', Product::withTrashed()->whereIn('id', $product_id)->pluck('sku')->toArray());
            $tickets[$i]->product_children = implode(', ', ProductChild::withTrashed()->whereIn('id', $product_child_id)->pluck('sku')->toArray());
        }

        return view('export.ticket', [
            'tickets' => $tickets,
        ]);

    }
}
