<?php

namespace App\Http\Controllers;

use App\Models\Approval;
use App\Models\CashSaleLocation;
use App\Models\Customer;
use App\Models\DeliveryOrder;
use App\Models\DeliveryOrderProduct;
use App\Models\DeliveryOrderProductChild;
use App\Models\ProductChild;
use App\Models\Sale;
use App\Models\SaleProduct;
use App\Models\SaleProductChild;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class CashSaleController extends Controller
{
    public function index()
    {
        $page = Session::get('cash-sale-page');

        return view('cash_sale.list', [
            'default_page' => $page ?? null,
        ]);
    }

    public function getData(Request $req)
    {
        Session::put('cash-sale-page', $req->page);

        $serial_no_qty_query = DB::table('sales')
            ->select('sales.id AS sale_id', DB::raw('COUNT(sale_product_children.id) AS serial_no_qty'))
            ->leftJoin('sale_products', 'sale_products.sale_id', '=', 'sales.id')
            ->leftJoin('sale_product_children', 'sale_product_children.sale_product_id', '=', 'sale_products.id')
            ->groupBy('sales.id');

        $paid_amount_query = DB::table('sales')
            ->select('sales.id AS sale_id', DB::raw('SUM(sale_payment_amounts.amount) AS paid_amount'))
            ->leftJoin('sale_payment_amounts', 'sale_payment_amounts.sale_id', '=', 'sales.id')
            ->whereNull('sale_payment_amounts.deleted_at')
            ->groupBy('sales.id');

        $records = DB::table('sales')
            ->select(
                'sales.id AS id',
                'sales.sku AS doc_no',
                'sales.custom_date AS custom_date',
                'sales.created_at AS date',
                'sales.payment_method AS payment_method',
                'sales.payment_due_date AS payment_due_date',
                'sales.custom_customer AS debtor_name',
                'sales_agents.name AS agent',
                'sales.status AS status',
                'sales.is_draft AS is_draft',
                'sales.payment_status',
                'payment_methods.name as payment_method',
                'payment_methods.by_pass_conversion as by_pass_conversion',
                'sales.created_by',
                'createdBy.name as created_by_name',
                'updatedBy.name as updated_by_name',
                'serial_no_qty_query.serial_no_qty',
                'paid_amount_query.paid_amount',
                DB::raw('SUM(sale_products.qty) AS qty'),
                DB::raw('SUM(sale_products.qty * sale_products.unit_price - COALESCE(sale_products.discount, 0) - COALESCE(sst_amount, 0)) AS total_amount'),
            )
            ->where('sales.type', Sale::TYPE_CASH_SALE)
            ->whereNull('sales.deleted_at')
            ->whereNull('sale_products.deleted_at')
            ->leftJoin('sale_products', 'sale_products.sale_id', '=', 'sales.id')
            ->leftJoin('sales_agents', 'sales_agents.id', '=', 'sales.sale_id')
            ->leftJoin('payment_methods', 'payment_methods.id', '=', 'sales.payment_method')
            ->leftJoin('users as createdBy', 'createdBy.id', '=', 'sales.created_by')
            ->leftJoin('users as updatedBy', 'updatedBy.id', '=', 'sales.updated_by')
            ->joinSub($serial_no_qty_query, 'serial_no_qty_query', function ($join) {
                $join->on('serial_no_qty_query.sale_id', '=', 'sales.id');
            })
            ->joinSub($paid_amount_query, 'paid_amount_query', function ($join) {
                $join->on('paid_amount_query.sale_id', '=', 'sales.id');
            })
            ->groupBy('sales.id');

        if ($req->has('sku')) {
            $records = $records->where('sales.sku', $req->sku);
        }
        if (isSalesOnly()) {
            $records = $records->where('sales.created_by', Auth::user()->id);
        }

        // Search
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];

            $do_ids = DeliveryOrder::where('sku', 'like', '%' . $keyword . '%')->pluck('id')->toArray();

            $records = $records->where(function ($q) use ($keyword, $do_ids) {
                $q->where('sales.sku', 'like', '%' . $keyword . '%')
                    ->orWhere('sales.created_at', 'like', '%' . $keyword . '%')
                    ->orWhere('sales.custom_customer', 'like', '%' . $keyword . '%')
                    ->orWhere('sales_agents.name', 'like', '%' . $keyword . '%');
            });
        }
        // Order
        if ($req->has('order')) {
            $map = [
                0 => 'sales.sku',
                1 => 'sales.created_at',
                2 => 'sales.custom_customer',
                3 => 'sales_agents.name',
                6 => DB::raw('SUM(sale_products.qty * sale_products.unit_price)'),
                7 => 'paid_amount_query.paid_amount',
                8 => 'payment_methods.name',
                9 => 'sales.payment_status',
                12 => 'sales.status',
            ];
            foreach ($req->order as $order) {
                $records = $records->orderBy($map[$order['column']], $order['dir']);
            }
        } else {
            $records = $records->orderBy('sales.id', 'desc');
        }

        $records_count = count($records->get());
        $records_ids = $records->pluck('sales.id');
        $records_paginator = $records->simplePaginate(10);

        $data = [
            'recordsTotal' => $records_count,
            'recordsFiltered' => $records_count,
            'data' => [],
            'records_ids' => $records_ids,
        ];
        foreach ($records_paginator as $record) {
            $sp_ids = SaleProduct::where('sale_id', $record->id)->pluck('id')->toArray();
            $dop_ids = DeliveryOrderProduct::whereIn('sale_product_id', $sp_ids)->pluck('id');
            $dopc_count = DeliveryOrderProductChild::whereIn('delivery_order_product_id', $dop_ids)->count();

            $data['data'][] = [
                'id' => $record->id,
                'doc_no' => $record->doc_no,
                'date' => $record->custom_date == null ? Carbon::parse($record->date)->format('d M Y') : Carbon::parse($record->custom_date)->format('d M Y'),
                'debtor_name' => $record->debtor_name,
                'agent' => $record->agent,
                'paid' => number_format($record->paid_amount, 2),
                'total' => number_format($record->total_amount, 2),
                'payment_method' => $record->payment_method,
                'payment_status' => $record->payment_status,
                'status' => $record->status,
                'is_draft' => $record->is_draft,
                'qty' => $record->qty,
                'serial_no_qty' => $record->serial_no_qty ?? 0,
                'not_converted_serial_no_qty' => $dopc_count ?? 0,
                'created_by' => $record->created_by_name,
                'updated_by' => $record->updated_by_name,
                'can_edit' => hasPermission('sale.sale_order.edit') && $record->status != Sale::STATUS_CANCELLED,
                'can_cancel' => hasPermission('sale.sale_order.cancel') && $record->status == Sale::STATUS_ACTIVE,
                'can_delete' => false, // SO no need delete btn
                'can_view_pdf' => $record->is_draft == false && $record->status != Sale::STATUS_APPROVAL_PENDING && $record->status != Sale::STATUS_APPROVAL_REJECTED && $record->status != Sale::STATUS_CANCELLED,
                'conditions_to_convert' => [
                    'is_draft' => $record->is_draft,
                    'payment_method_filled' => $record->payment_method != null,
                    'payment_due_date_filled' => $record->paid_amount >= $record->total_amount || $record->payment_due_date != null,
                    'has_product' => count($sp_ids) > 0,
                    'has_serial_no' => SaleProductChild::whereIn('sale_product_id', $sp_ids)->exists(),
                    'is_active_or_approved' => $record->status == Sale::STATUS_APPROVAL_APPROVED || $record->status == Sale::STATUS_ACTIVE,
                    'no_pending_approval' => !Approval::where('object_type', Sale::class)->where('object_id', $record->id)->where('data', 'like', '%is_quo%')->where('status', Approval::STATUS_PENDING_APPROVAL)->exists(),
                    'not_in_production' => !in_array($record->id, (new SaleController)->getSaleInProduction()),
                    'by_pass_for_unpaid' => $record->payment_status != Sale::PAYMENT_STATUS_UNPAID || ($record->payment_status == Sale::PAYMENT_STATUS_UNPAID && $record->by_pass_conversion)
                ]
            ];
        }

        return response()->json($data);
    }

    public function create(Request $req)
    {
        return view('cash_sale.form');
    }

    public function edit(Sale $sale)
    {
        $sale->load('products.product.children', 'products.children', 'products.warrantyPeriods', 'paymentAmounts');

        $sale->products->each(function ($q) {
            $q->attached_to_do = $q->attachedToDo();
        });

        if ($sale->custom_date == null) {
            $sale->custom_date = $sale->created_at;
            $sale->save();
        }

        return view('cash_sale.form', [
            'sale' => $sale,
            'billing_address' => CashSaleLocation::where('id', $sale->billing_address_id)->first(),
            'delivery_address' => CashSaleLocation::where('id', $sale->delivery_address_id)->first(),
        ]);
    }

    public function pdf(Sale $sale)
    {
        if ($sale->is_draft == true || $sale->status == Sale::STATUS_APPROVAL_PENDING || $sale->status == Sale::STATUS_APPROVAL_REJECTED) {
            return abort(403);
        }

        $sps = $sale->products()->withTrashed()->get();
        for ($i = 0; $i < count($sps); $i++) {
            $pc_ids = $sps[$i]->children->pluck('product_children_id');
            $sps[$i]->serial_no = ProductChild::whereIn('id', $pc_ids)->pluck('sku')->toArray();
        }

        $pdf = Pdf::loadView('cash_sale.' . (isHiTen($sale->company_group) ? 'hi_ten' : 'powercool') . '_pdf', [
            'date' => now()->format('d/m/Y'),
            'sale' => $sale,
            'products' => $sps,
            'saleperson' => $sale->saleperson,
            'billing_address' => CashSaleLocation::where('id', $sale->billing_address_id)->first(),
            'delivery_address' => CashSaleLocation::where('id', $sale->delivery_address_id)->first(),
            'terms' => $sale->paymentTerm ?? null,
            'tax_code' => Setting::where('key', Setting::TAX_CODE_KEY)->value('value'),
            'sst_value' => Setting::where('key', Setting::SST_KEY)->value('value'),
            'is_paid' => $sale->payment_status == Sale::PAYMENT_STATUS_PAID,
        ]);
        $pdf->setPaper('A4', 'letter');

        return $pdf->stream($sale->sku . '.pdf');
    }

    public function cancel(Sale $sale)
    {
        $sale->status = Sale::STATUS_CANCELLED;
        $sale->save();

        return redirect(route('cash_sale.index'))->with('success', 'Cash Sale cancelled');
    }
}
