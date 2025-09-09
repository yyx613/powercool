<?php

namespace App\Http\Controllers;

use App\Models\Approval;
use App\Models\Customer;
use App\Models\DeliveryOrder;
use App\Models\DeliveryOrderProduct;
use App\Models\DeliveryOrderProductChild;
use App\Models\Sale;
use App\Models\SaleProduct;
use App\Models\SaleProductChild;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class CashSaleController extends Controller
{
    public function indexCashSale()
    {
        $page = Session::get('cash-sale-page');

        return view('cash_sale.list', [
            'default_page' => $page ?? null,
        ]);
    }

    public function getDataCashSale(Request $req)
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
                'sales.store AS store',
                'sales.payment_method AS payment_method',
                'sales.payment_due_date AS payment_due_date',
                'customers.id AS customer_id',
                'customers.sku AS debtor_code',
                'customers.company_name AS debtor_name',
                'customers.company_group AS company_group',
                'sales.convert_to AS transfer_to',
                'sales_agents.name AS agent',
                'currencies.name AS curr_code',
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
            ->leftJoin('customers', 'customers.id', '=', 'sales.customer_id')
            ->leftJoin('currencies', 'customers.currency_id', '=', 'currencies.id')
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
                    ->orWhere('customers.sku', 'like', '%' . $keyword . '%')
                    ->orWhere('customers.company_name', 'like', '%' . $keyword . '%')
                    ->orWhere('sales_agents.name', 'like', '%' . $keyword . '%')
                    ->orWhere('currencies.name', 'like', '%' . $keyword . '%');

                for ($i = 0; $i < count($do_ids); $i++) {
                    $q->orWhereRaw("find_in_set('" . $do_ids[$i] . "', convert_to)");
                }
            });
        }
        // Order
        if ($req->has('order')) {
            $map = [
                0 => 'sales.sku',
                1 => 'sales.created_at',
                4 => 'customers.sku',
                5 => 'customers.name',
                6 => 'sales_agents.name',
                11 => DB::raw('SUM(sale_products.qty * sale_products.unit_price)'),
                12 => DB::raw('SUM(sale_payment_amounts.amount)'),
                13 => 'payment_methods.name',
                14 => 'sales.payment_status',
                17 => 'sales.status',
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
                'transfer_from' => implode(', ', Sale::where('convert_to', $record->id)->pluck('sku')->toArray()),
                'transfer_to' => implode(', ', DeliveryOrder::whereIn('id', explode(',', $record->transfer_to))->pluck('sku')->toArray()),
                'debtor_code' => $record->debtor_code,
                'debtor_name' => $record->debtor_name,
                'debtor_company_group' => $record->company_group,
                'agent' => $record->agent,
                'store' => $record->store,
                'curr_code' => $record->curr_code ?? null,
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
                'can_edit' => hasPermission('sale.sale_order.edit'),
                'can_view' => hasPermission('sale.sale_order.view_record'),
                'can_cancel' => hasPermission('sale.sale_order.cancel') && $record->status == Sale::STATUS_ACTIVE,
                'can_delete' => false, // SO no need delete btn
                'can_view_pdf' => $record->is_draft == false && $record->status != Sale::STATUS_APPROVAL_PENDING && $record->status != Sale::STATUS_APPROVAL_REJECTED,
                'conditions_to_convert' => [
                    'is_draft' => $record->is_draft,
                    'payment_method_filled' => $record->payment_method != null,
                    'payment_due_date_filled' => $record->paid_amount >= $record->total_amount || $record->payment_due_date != null,
                    'has_product' => count($sp_ids) > 0,
                    'has_serial_no' => SaleProductChild::whereIn('sale_product_id', $sp_ids)->exists(),
                    'is_active_or_approved' => $record->status == Sale::STATUS_APPROVAL_APPROVED || $record->status == Sale::STATUS_ACTIVE,
                    'no_pending_approval' => !Approval::where('object_type', Sale::class)->where('object_id', $record->id)->where('data', 'like', '%is_quo%')->where('status', Approval::STATUS_PENDING_APPROVAL)->exists(),
                    'not_in_production' => !in_array($record->id, $this->getSaleInProduction()),
                    'filled_for_e_invoice' => Customer::forEinvoiceFilled($record->customer_id),
                    'by_pass_for_unpaid' => $record->payment_status != Sale::PAYMENT_STATUS_UNPAID || ($record->payment_status == Sale::PAYMENT_STATUS_UNPAID && $record->by_pass_conversion)
                ]
            ];
        }

        return response()->json($data);
    }

    public function createCashSale(Request $req)
    {
        return view('cash_sale.form');
    }
}
