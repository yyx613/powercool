<?php

namespace App\Http\Controllers;

use App\Exports\TicketExport;
use App\Models\Attachment;
use App\Models\Branch;
use App\Models\DeliveryOrder;
use App\Models\DeliveryOrderProduct;
use App\Models\DeliveryOrderProductChild;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\ProductChild;
use App\Models\Sale;
use App\Models\SaleProduct;
use App\Models\SaleProductChild;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class TicketController extends Controller
{
    const FORM_RULES = [
        'subject' => 'required|max:250',
        'body' => 'required',
        'customer' => 'required',
        'status' => 'required',
        'attachment' => 'nullable',
        'attachment.*' => 'file',
        'so_inv' => 'required',
        'so_inv.*' => 'nullable',
        'product' => 'required',
        'product.*' => 'nullable|required_with:so_inv.*',
        'serial_no' => 'required',
        'serial_no.*' => 'nullable|required_with:so_inv.*',
        'so_inv_type' => 'required',
        'so_inv_type.*' => 'nullable|required_with:so_inv.*',
    ];

    const FORM_ATTRIBUTE = [
        'so_inv.*' => 'sale order / invoice',
        'product.*' => 'product',
        'serial_no.*' => 'serial no',
    ];

    public function index()
    {
        $page = Session::get('ticket-page');

        return view('ticket.list', [
            'default_page' => $page ?? null,
        ]);
    }

    public function getData(Request $req)
    {
        Session::put('ticket-page', $req->page);

        $records = new Ticket;

        // Search
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];

            $records = $records->where(function ($q) use ($keyword) {
                $q->where('sku', 'like', '%'.$keyword.'%')
                    ->orWhere('subject', 'like', '%'.$keyword.'%')
                    ->orWhere('body', 'like', '%'.$keyword.'%');
            });
        }
        // Order
        if ($req->has('order')) {
            $map = [
                0 => 'sku',
                1 => 'subject',
                2 => 'created_at',
                3 => 'is_active',
            ];
            foreach ($req->order as $order) {
                $records = $records->orderBy($map[$order['column']], $order['dir']);
            }
        } else {
            $records = $records->orderBy('id', 'desc');
        }

        $records_count = $records->count();
        $records_ids = $records->pluck('id');
        $records_paginator = $records->simplePaginate(10);

        $data = [
            'recordsTotal' => $records_count,
            'recordsFiltered' => $records_count,
            'data' => [],
            'records_ids' => $records_ids,
        ];
        foreach ($records_paginator as $key => $record) {
            $data['data'][] = [
                'id' => $record->id,
                'sku' => $record->sku,
                'subject' => $record->subject,
                'created_at' => Carbon::parse($record->created_at)->format('d M Y H:i'),
                'status' => $record->is_active,
                'can_edit' => hasPermission('ticket.edit'),
                'can_delet' => hasPermission('ticket.delete'),
            ];
        }

        return response()->json($data);
    }

    public function create()
    {
        return view('ticket.form');
    }

    public function store(Request $req)
    {
        // Validate request
        $validator = Validator::make($req->all(), self::FORM_RULES, [], self::FORM_ATTRIBUTE);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $ticket = Ticket::create([
                'sku' => (new Ticket)->generateSku(),
                'subject' => $req->subject,
                'customer_id' => $req->customer,
                'is_active' => $req->boolean('status'),
                'body' => $req->body,
                'last_touch_by' => Auth::user()->id,
                'so_inv' => implode(',', $req->so_inv) ?? null,
                'so_inv_type' => implode(',', $req->so_inv_type) ?? null,
                'product_id' => implode(',', $req->product) ?? null,
                'product_child_id' => implode(',', $req->serial_no) ?? null,
            ]);
            (new Branch)->assign(Ticket::class, $ticket->id);

            if ($req->hasFile('attachment')) {
                foreach ($req->file('attachment') as $key => $file) {
                    $path = Storage::putFile(Attachment::TICKET_PATH, $file);
                    Attachment::create([
                        'object_type' => Ticket::class,
                        'object_id' => $ticket->id,
                        'src' => basename($path),
                    ]);
                }
            }

            // SO / INV

            DB::commit();

            return redirect(route('ticket.index'))->with('success', 'Ticket created');
        } catch (\Throwable $th) {
            dd($th);
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator')->withInput();
        }
    }

    public function edit(Ticket $ticket)
    {
        $ticket->load('attachments');

        return view('ticket.form', [
            'ticket' => $ticket,
        ]);
    }

    public function update(Request $req, Ticket $ticket)
    {
        // Validate request
        $validator = Validator::make($req->all(), self::FORM_RULES, [], self::FORM_ATTRIBUTE);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $ticket->update([
                'subject' => $req->subject,
                'customer_id' => $req->customer,
                'is_active' => $req->boolean('status'),
                'body' => $req->body,
                'last_touch_by' => Auth::user()->id,
                'so_inv' => implode(',', $req->so_inv) ?? null,
                'so_inv_type' => implode(',', $req->so_inv_type) ?? null,
                'product_id' => implode(',', $req->product) ?? null,
                'product_child_id' => implode(',', $req->serial_no) ?? null,
            ]);

            if ($req->hasFile('attachment')) {
                Attachment::where([
                    ['object_type', Ticket::class],
                    ['object_id', $ticket->id],
                ])->delete();

                foreach ($req->file('attachment') as $key => $file) {
                    $path = Storage::putFile(Attachment::TICKET_PATH, $file);
                    Attachment::create([
                        'object_type' => Ticket::class,
                        'object_id' => $ticket->id,
                        'src' => basename($path),
                    ]);
                }
            }

            DB::commit();

            return redirect(route('ticket.index'))->with('success', 'Ticket updated');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator')->withInput();
        }
    }

    public function delete(Ticket $ticket)
    {
        $ticket->delete();

        return back()->with('success', 'Ticket deleted');
    }

    public function getProducts(Request $req)
    {
        $products = collect();
        $type = $req->type;
        $id = $req->val;

        if ($type == 'so') {
            $sale = Sale::where('id', $id)->first();
            $model = Product::whereIn('id', SaleProduct::where('sale_id', $sale->id)->orderBy('id', 'desc')->pluck('product_id')->toArray());
            $products = $products->merge($model->get());
            $products = $products->unique('id');
        } elseif ($type == 'inv') {
            $inv = Invoice::where('id', $id)->first();
            $do_ids = DeliveryOrder::where('invoice_id', $inv->id)->pluck('id')->toArray();
            $sp_ids = DeliveryOrderProduct::whereIn('delivery_order_id', $do_ids)->pluck('sale_product_id')->toArray();

            $model = Product::whereIn('id', SaleProduct::whereIn('id', $sp_ids)->orderBy('id', 'desc')->pluck('product_id')->toArray());
            $products = $products->merge($model->get());
            $products = $products->unique('id');
        }

        return response()->json([
            'products' => $products->toArray(),
        ]);
    }

    public function getProductChildren(Request $req)
    {
        if ($req->type == 'so') {
            $sp_ids = SaleProduct::where('sale_id', $req->so_inv_id)->where('product_id', $req->product_id)->pluck('id');
            $pc_ids = SaleProductChild::whereIn('sale_product_id', $sp_ids)->pluck('product_children_id');
        } elseif ($req->type == 'inv') {
            $inv = Invoice::where('id', $req->so_inv_id)->first();
            $do_ids = DeliveryOrder::where('invoice_id', $inv->id)->pluck('id')->toArray();
            $dop_ids = DeliveryOrderProduct::whereIn('delivery_order_id', $do_ids)->pluck('id')->toArray();
            $pc_ids = DeliveryOrderProductChild::whereIn('delivery_order_product_id', $dop_ids)->pluck('product_children_id')->toArray();
        }
        $product_children = ProductChild::whereIn('id', $pc_ids)->get();

        return response()->json([
            'product_children' => $product_children,
        ]);
    }

    public function export()
    {
        return Excel::download(new TicketExport, 'ticket.xlsx');
    }
}
