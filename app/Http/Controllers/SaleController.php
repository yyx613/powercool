<?php

namespace App\Http\Controllers;

use App\Models\AgentDebtor;
use App\Models\Approval;
use App\Models\Billing;
use App\Models\BillingProduct;
use App\Models\Branch;
use App\Models\CashSaleLocation;
use App\Models\ConsolidatedEInvoice;
use App\Models\CreditNote;
use App\Models\CreditTerm;
use App\Models\Customer;
use App\Models\CustomerLocation;
use App\Models\Dealer;
use App\Models\DebitNote;
use App\Models\DeliveryOrder;
use App\Models\DeliveryOrderProduct;
use App\Models\DeliveryOrderProductChild;
use App\Models\DraftEInvoice;
use App\Models\EInvoice;
use App\Models\InventoryCategory;
use App\Models\InventoryServiceReminder;
use App\Models\Invoice;
use App\Models\ObjectCreditTerm;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductChild;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SaleOrderCancellation;
use App\Models\SalePaymentAmount;
use App\Models\SaleProduct;
use App\Models\SaleProductChild;
use App\Models\SaleProductionRequest;
use App\Models\SaleProductWarrantyPeriod;
use App\Models\SalesAgent;
use App\Models\Scopes\ApprovedScope;
use App\Models\Scopes\BranchScope;
use App\Models\Setting;
use App\Models\Target;
use App\Models\TransportAcknowledgement;
use App\Models\TransportAcknowledgementProduct;
use App\Models\UOM;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Exception;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

class SaleController extends Controller
{
    const DELIVERY_ORDER_PATH = '/public/delivery_order/';

    const TRANSPORT_ACKNOWLEDGEMENT_PATH = '/public/transport_acknowledgement/';

    const INVOICE_PATH = '/public/invoice/';

    const BILLING_PATH = '/public/billing/';

    public function index(Request $req)
    {
        Session::put('quo-sku', null);
        if ($req->has('sku')) {
            Session::put('quo-sku', $req->sku);
        }
        $page = Session::get('quotation-page');

        return view('quotation.list', [
            'default_page' => $page ?? null,
        ]);
    }

    public function getData(Request $req)
    {
        Session::put('quotation-page', $req->page);

        $records = DB::table('sales')
            ->select(
                'sales.id AS id',
                'sales.sku AS doc_no',
                'sales.created_at AS date',
                'sales.open_until AS validity',
                'sales.store AS store',
                'customers.id AS customer_id',
                'customers.sku AS debtor_code',
                'customers.company_group AS company_group',
                'customers.company_name AS debtor_name',
                'sales.convert_to AS transfer_to',
                'sales_agents.name AS agent',
                'currencies.name AS curr_code',
                'sales.status AS status',
                'sales.is_draft AS is_draft',
                'sales.created_by AS created_by',
                'sales.expired_at AS expired_at',
                DB::raw('SUM(sale_products.unit_price * sale_products.qty - COALESCE(sst_amount, 0)) AS total_amount'),
            )
            ->where('sales.type', Sale::TYPE_QUO)
            ->where('branches.object_type', 'like', '%Sale')
            ->whereNull('sales.deleted_at')
            ->whereNull('sale_products.deleted_at')
            ->leftJoin('sale_products', 'sale_products.sale_id', '=', 'sales.id')
            ->leftJoin('customers', 'customers.id', '=', 'sales.customer_id')
            ->leftJoin('currencies', 'customers.currency_id', '=', 'currencies.id')
            ->leftJoin('sales_agents', 'sales_agents.id', '=', 'sales.sale_id')
            ->leftJoin('branches', 'sales.id', '=', 'branches.object_id')
            ->groupBy('sales.id');

        if (getCurrentUserBranch() != Branch::LOCATION_EVERY) {
            $records = $records->where('branches.location', getCurrentUserBranch());
        }
        if (Session::get('quo-sku') != null) {
            $records = $records->where('sales.sku', Session::get('quo-sku'));
        }

        // Search
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];

            $records = $records->where(function ($q) use ($keyword) {
                $q->where('sales.sku', 'like', '%'.$keyword.'%')
                    ->orWhere('sales.created_at', 'like', '%'.$keyword.'%')
                    ->orWhere('sales.open_until', 'like', '%'.$keyword.'%')
                    ->orWhere('customers.sku', 'like', '%'.$keyword.'%')
                    ->orWhere('customers.company_name', 'like', '%'.$keyword.'%')
                    ->orWhere('sales_agents.name', 'like', '%'.$keyword.'%')
                    ->orWhere('currencies.name', 'like', '%'.$keyword.'%');
            });
        }
        // Order
        if ($req->has('order')) {
            $map = [
                0 => 'sales.sku',
                1 => 'sales.created_at',
                2 => 'sales.open_until',
                4 => 'customers.sku',
                5 => 'customers.company_name',
                6 => 'sales_agents.name',
                9 => DB::raw('SUM(sale_products.unit_price * sale_products.qty)'),
                10 => 'sales.status',
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
            $owned = isSuperAdmin() || Auth::user()->id == $record->created_by;

            $is_approval_cancellation = false;
            if ($record->status == Sale::STATUS_APPROVAL_PENDING) {
                $is_approval_cancellation = Approval::where('object_type', Sale::class)->where('object_id', $record->id)
                    ->where('status', Approval::STATUS_PENDING_APPROVAL)
                    ->where('data', 'like', '%is_quo%')->where('data', 'like', '%is_cancellation%')->exists();
            }

            $data['data'][] = [
                'id' => $record->id,
                'doc_no' => $record->doc_no,
                'date' => Carbon::parse($record->date)->format('d M Y'),
                'validity' => Carbon::parse($record->validity)->format('d M Y'),
                'transfer_to' => implode(', ', Sale::whereIn('id', explode(',', $record->transfer_to))->pluck('sku')->toArray()),
                'debtor_code' => $record->debtor_code,
                'debtor_name' => $record->debtor_name,
                'debtor_company_group' => $record->company_group,
                'agent' => $record->agent,
                'store' => $record->store,
                'curr_code' => $record->curr_code ?? null,
                'total' => number_format($record->total_amount, 2),
                'status' => $record->status,
                'expired_at' => $record->expired_at,
                'is_draft' => $record->is_draft,
                'can_view_pdf' => $record->is_draft == false && ! in_array($record->status, [Sale::STATUS_APPROVAL_PENDING, Sale::STATUS_APPROVAL_REJECTED]),
                'can_edit' => hasPermission('sale.quotation.edit') && $owned,
                'can_view' => hasPermission('sale.quotation.view_record') && in_array($record->status, [Sale::STATUS_CANCELLED, Sale::STATUS_CONVERTED, Sale::STATUS_APPROVAL_PENDING]),
                // 'can_delete' => hasPermission('sale.quotation.delete'),
                'can_delete' => false,
                'can_cancel' => ! in_array($record->status, [Sale::STATUS_APPROVAL_PENDING, Sale::STATUS_CANCELLED]),
                'can_reuse' => $record->status == Sale::STATUS_CANCELLED,
                'is_approval_cancellation' => $is_approval_cancellation,
                'conditions_to_convert' => [
                    'is_draft' => $record->is_draft,
                    'is_expired' => $record->expired_at != null,
                    'has_product' => SaleProduct::where('sale_id', $record->id)->exists(),
                    'is_active_or_approved' => $record->status == Sale::STATUS_APPROVAL_APPROVED || $record->status == Sale::STATUS_ACTIVE,
                    'no_pending_approval' => ! Approval::where('object_type', Sale::class)->where('object_id', $record->id)->where('data', 'like', '%is_quo%')->where('status', Approval::STATUS_PENDING_APPROVAL)->exists(),
                    'not_in_production' => ! in_array($record->id, $this->getSaleInProduction()),
                    'filled_for_e_invoice' => Customer::forEinvoiceFilled($record->customer_id),
                ],
            ];
        }

        return response()->json($data);
    }

    public function create(Request $req)
    {
        $data = [];
        if ($req->has('quo')) {
            $replicate = Sale::where('id', $req->quo)->first();
            $data['replicate'] = $replicate->load('products.product.children', 'products.children', 'products.warrantyPeriods');

            $customers = Customer::with('creditTerms.creditTerm', 'salesAgents')
                ->where('id', $replicate->customer_id)
                ->orderBy('id', 'desc')->get();
            $customers = $customers->keyBy('id')->all();

            $data['customers'] = $customers;
        }
        $data['warehouse'] = getCurrentUserWarehouse();

        return view('quotation.form', $data);
    }

    public function edit(Sale $sale)
    {
        $is_view = false;
        if (str_contains(Route::currentRouteName(), 'view')) {
            $is_view = true;
        }

        $owned = isSuperAdmin() || Auth::user()->id == $sale->created_by;
        if (! $owned) {
            abort(403);
        }

        $customers = Customer::with('creditTerms.creditTerm', 'salesAgents')
            ->where('id', $sale->customer_id)
            ->orderBy('id', 'desc')->get();
        $customers = $customers->keyBy('id')->all();

        if ($sale->convert_to == null) {
            $transfer_to = null;
        } else {
            $transfer_to = implode(', ', Sale::whereIn('id', explode(',', $sale->convert_to))->pluck('sku')->toArray());
        }

        return view('quotation.form', [
            'sale' => $sale->load('products.product.children', 'products.children', 'products.warrantyPeriods'),
            'customers' => $customers,
            'is_view' => $is_view,
            'transfer_to' => $transfer_to,
            'warehouse' => getCurrentUserWarehouse(),
        ]);
    }

    public function cancel(Request $req, Sale $sale)
    {
        try {
            DB::beginTransaction();

            $approval = Approval::create([
                'object_type' => Sale::class,
                'object_id' => $sale->id,
                'status' => Approval::STATUS_PENDING_APPROVAL,
                'data' => json_encode([
                    'is_quo' => true,
                    'is_cancellation' => true,
                    'description' => Auth::user()->name.' has requested to cancel the quotation.',
                    'cancellation_remark' => $req->remark ?? null,
                ]),
            ]);
            (new Branch)->assign(Approval::class, $approval->id);

            $sale->status = Sale::STATUS_APPROVAL_PENDING;
            $sale->save();

            DB::commit();

            return back()->with('success', 'Quotation cancel request is submitted');
        } catch (\Throwable $th) {
            report($th);
            DB::rollBack();

            return back()->with('error', 'Something went wrong. Please contact administrator');
        }
    }

    public function reuse(Sale $sale)
    {
        if ($sale->status != Sale::STATUS_CANCELLED) {
            abort(403);
        }

        try {
            DB::beginTransaction();

            $approval = Approval::create([
                'object_type' => Sale::class,
                'object_id' => $sale->id,
                'status' => Approval::STATUS_PENDING_APPROVAL,
                'data' => json_encode([
                    'is_quo' => true,
                    'is_reuse' => true,
                    'description' => Auth::user()->name.' has requested to reuse quotation.',
                ]),
            ]);
            (new Branch)->assign(Approval::class, $approval->id);

            $sale->status = Sale::STATUS_APPROVAL_PENDING;
            $sale->save();

            DB::commit();

            return back()->with('success', 'Quotation reuse request is now waiting for approval');
        } catch (\Throwable $th) {
            report($th);
            DB::rollBack();

            return back()->with('error', 'Something went wrong. Please contact administrator');
        }
    }

    public function delete(Sale $sale)
    {
        try {
            DB::beginTransaction();

            Approval::where('object_type', Sale::class)->where('object_id', $sale->id)->delete();

            $sp_ids = SaleProduct::where('sale_id', $sale->id)->pluck('id');
            SaleProductChild::whereIn('sale_product_id', $sp_ids)->delete();
            SaleProduct::whereIn('id', $sp_ids)->delete();
            $sale->delete();

            DB::commit();

            return back()->with('success', 'Quotation deleted');
        } catch (\Throwable $th) {
            report($th);
            DB::rollBack();

            return back()->with('error', 'Something went wrong. Please contact administrator');
        }
    }

    public function pdf(Sale $sale)
    {
        if ($sale->is_draft == true || $sale->status == Sale::STATUS_APPROVAL_PENDING || $sale->status == Sale::STATUS_APPROVAL_REJECTED) {
            return abort(403);
        }

        $sale->load('saleperson');

        $show_payment_term = in_array($sale->payment_method, getPaymentMethodCreditTermIds());
        if ($show_payment_term) {
            $ct_ids = ObjectCreditTerm::where('object_type', Customer::class)->where('object_id', $sale->customer_id)->pluck('credit_term_id')->toArray();
            $payment_term = implode(', ', CreditTerm::whereIn('id', $ct_ids)->pluck('name')->toArray());
        }

        $pdf = Pdf::loadView('quotation.'.(isHiTen($sale->customer->company_group) ? 'hi_ten' : 'powercool').'_pdf', [
            'date' => now()->format('d/m/Y'),
            'sale' => $sale,
            'products' => $sale->products,
            'customer' => $sale->customer,
            'billing_address' => CustomerLocation::where('id', $sale->billing_address_id)->first(),
            'delivery_address' => CustomerLocation::where('id', $sale->delivery_address_id)->first(),
            'show_payment_term' => $show_payment_term,
            'payment_term' => $payment_term ?? null,
            'tax_code' => Setting::where('key', Setting::TAX_CODE_KEY)->value('value'),
            'sst_value' => Setting::where('key', Setting::SST_KEY)->value('value'),
        ]);
        $pdf->setPaper('A4', 'letter');

        return $pdf->stream($sale->sku.'.pdf');
    }

    public function toSaleOrder(Request $req)
    {
        if (Session::get('convert_customer_id') != null) {
            $selected_customer = Customer::where('id', Session::get('convert_customer_id'))->first();
        }
        if (Session::get('convert_salesperson_id') != null) {
            $selected_salesperson = SalesAgent::where('id', Session::get('convert_salesperson_id'))->first();
        }

        $step = 1;

        if ($req->has('sp')) {
            $step = 3;

            Session::put('convert_salesperson_id', $req->sp);

            $quotations = Sale::where('type', Sale::TYPE_QUO)
                ->where('is_draft', false)
                ->whereNull('expired_at')
                ->whereNotIn('id', $this->getSaleInProduction())
                ->whereHas('products')
                ->whereIn('status', [Sale::STATUS_ACTIVE, Sale::STATUS_APPROVAL_APPROVED])
                ->where('customer_id', Session::get('convert_customer_id'))
                ->where('sale_id', Session::get('convert_salesperson_id'))
                ->where(function ($q) {
                    $q->whereHas('approval', function ($q) {
                        $q->where('status', Approval::STATUS_APPROVED);
                    })->orDoesntHave('approval');
                })
                ->get();
        } elseif ($req->has('cus')) {
            $step = 2;

            Session::put('convert_customer_id', $req->cus);

            $salesperson_ids = Sale::where('type', Sale::TYPE_QUO)
                ->where('is_draft', false)
                ->whereNull('expired_at')
                ->whereNotIn('id', $this->getSaleInProduction())
                ->whereHas('products')
                ->whereIn('status', [Sale::STATUS_ACTIVE, Sale::STATUS_APPROVAL_APPROVED])
                ->where('customer_id', $req->cus)
                ->where(function ($q) {
                    $q->whereHas('approval', function ($q) {
                        $q->where('status', Approval::STATUS_APPROVED);
                    })->orDoesntHave('approval');
                })
                ->distinct()
                ->pluck('sale_id');

            $salespersons = SalesAgent::whereIn('id', $salesperson_ids)->get();
        } else {
            $customer_ids = Sale::where('type', Sale::TYPE_QUO)
                ->where('is_draft', false)
                ->whereNull('expired_at')
                ->whereNotIn('id', $this->getSaleInProduction())
                ->whereHas('products')
                ->whereIn('status', [Sale::STATUS_ACTIVE, Sale::STATUS_APPROVAL_APPROVED])
                ->where(function ($q) {
                    $q->whereHas('approval', function ($q) {
                        $q->where('status', Approval::STATUS_APPROVED);
                    })->orDoesntHave('approval');
                })
                ->distinct()
                ->pluck('customer_id');

            $customers = Customer::whereIn('id', $customer_ids)->get();
            // Filter for einvoice filled
            $temp = [];
            for ($i = 0; $i < count($customers); $i++) {
                if (Customer::forEinvoiceFilled($customers[$i]->id)) {
                    $temp[] = $customers[$i];
                }
            }
            $customers = $temp;
        }

        return view('quotation.convert', [
            'step' => $step,
            'customers' => $customers ?? [],
            'salespersons' => $salespersons ?? [],
            'quotations' => $quotations ?? [],
            'selected_customer' => $selected_customer ?? null,
            'selected_salesperson' => $selected_salesperson ?? null,
        ]);
    }

    public function converToSaleOrder(Request $req)
    {
        $quo_ids = explode(',', $req->quo);
        $quos = Sale::where('type', Sale::TYPE_QUO)->whereIn('id', $quo_ids)->get();

        try {
            $references = collect();
            $remarks = collect();
            $products = collect();

            DB::beginTransaction();

            for ($i = 0; $i < count($quos); $i++) {
                $references = $references->merge($quos[$i]->reference);
                $remarks = $remarks->merge($quos[$i]->remark);
                $products = $products->merge($quos[$i]->products);
            }

            // Create quotation details
            $request = new Request([
                'sale' => Session::get('convert_salesperson_id'),
                'customer' => Session::get('convert_customer_id'),
                'reference' => implode(',', $references->toArray()),
                'status' => true,
                'type' => 'so',
                'store' => count($quos) > 1 ? null : (count($quos) > 0 ? $quos[0]->store : null),
                'warehouse' => count($quos) > 1 ? null : (count($quos) > 0 ? $quos[0]->warehouse : null),
                'report_type' => $req->report_type,
                'product_id' => $products->map(function ($q) {
                    return $q->product_id;
                })->toArray(),
                'billing_address' => $quos[0]->billing_address_id,
                'delivery_address' => $quos[0]->delivery_address_id,
                'payment_method' => $quos[0]->payment_method,
            ]);
            // Check to use back transferred SO sku
            if ($quos[0]->convert_to != null) {
                $sku_to_use = Sale::withTrashed()->where('id', $quos[0]->convert_to)->value('sku');
            }
            $res = $this->upsertQuoDetails($request, false, true, isset($sku_to_use) ? $sku_to_use : null, false)->getData();
            if ($res->result != true) {
                throw new Exception('Failed to create quotation');
            }

            $sale_id = $res->sale->id;

            // Create product details
            $request = new Request([
                'sale_id' => $sale_id,
                'product_id' => $products->map(function ($q) {
                    return $q->product_id;
                })->toArray(),
                'product_desc' => $products->map(function ($q) {
                    return $q->desc;
                })->toArray(),
                'qty' => $products->map(function ($q) {
                    return $q->qty;
                })->toArray(),
                'uom' => $products->map(function ($q) {
                    return $q->uom;
                })->toArray(),
                'selling_price' => $products->map(function ($q) {
                    return $q->selling_price_id;
                })->toArray(),
                'unit_price' => $products->map(function ($q) {
                    return $q->unit_price;
                })->toArray(),
                'with_sst' => $products->map(function ($q) {
                    return $q->with_sst == true ? 'true' : 'false';
                })->toArray(),
                'sst_amount' => $products->map(function ($q) {
                    return $q->sst_amount;
                })->toArray(),
                'promotion_id' => $products->map(function ($q) {
                    return $q->promotion_id;
                })->toArray(),
                'product_serial_no' => $products->map(function ($q) {
                    return $q->children->pluck('product_children_id')->toArray();
                })->toArray(),
                'warranty_period' => $products->map(function ($q) {
                    return $q->warrantyPeriods->map(function ($q) {
                        return $q->warranty_period_id;
                    });
                })->toArray(),
                'discount' => $products->map(function ($q) {
                    return $q->discount;
                })->toArray(),
                'product_remark' => $products->map(function ($q) {
                    return $q->remark;
                })->toArray(),
                'override_selling_price' => $products->map(function ($q) {
                    return $q->override_selling_price;
                })->toArray(),
                'foc' => $products->map(function ($q) {
                    return $q->is_foc;
                })->toArray(),
            ]);
            $res = $this->upsertProDetails($request, false, false, false, true)->getData();
            if ($res->result != true) {
                throw new Exception('Failed to create product');
            }

            // Create remark details
            $request = new Request([
                'sale_id' => $sale_id,
                'remark' => count($remarks) <= 0 ? null : implode(',', $remarks->toArray()),
            ]);
            $res = $this->upsertRemark($request)->getData();
            if ($res->result != true) {
                throw new Exception('Failed to create remark');
            }
            // Change QUO's status to converted
            Sale::where('type', Sale::TYPE_QUO)->whereIn('id', $quo_ids)->update([
                'status' => Sale::STATUS_CONVERTED,
                'convert_to' => $sale_id,
            ]);

            DB::commit();

            return redirect(route('sale_order.index'))->with('success', 'Quotation has converted');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator');
        }
    }

    public function indexSaleOrder()
    {
        $page = Session::get('sale-order-page');

        return view('sale_order.list', [
            'default_page' => $page ?? null,
            'is_sale_coordinator_only' => isSalesCoordinatorOnly(),
        ]);
    }

    public function getDataSaleOrder(Request $req)
    {
        Session::put('sale-order-page', $req->page);

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
            ->where('sales.type', Sale::TYPE_SO)
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

            $do_ids = DeliveryOrder::where('sku', 'like', '%'.$keyword.'%')->pluck('id')->toArray();

            $records = $records->where(function ($q) use ($keyword, $do_ids) {
                $q->where('sales.sku', 'like', '%'.$keyword.'%')
                    ->orWhere('sales.created_at', 'like', '%'.$keyword.'%')
                    ->orWhere('customers.sku', 'like', '%'.$keyword.'%')
                    ->orWhere('customers.company_name', 'like', '%'.$keyword.'%')
                    ->orWhere('sales_agents.name', 'like', '%'.$keyword.'%')
                    ->orWhere('currencies.name', 'like', '%'.$keyword.'%');

                for ($i = 0; $i < count($do_ids); $i++) {
                    $q->orWhereRaw("find_in_set('".$do_ids[$i]."', convert_to)");
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

        $voided_do_ids = DeliveryOrder::where('status', DeliveryOrder::STATUS_VOIDED)->pluck('id')->toArray();
        foreach ($records_paginator as $record) {
            $sp_ids = SaleProduct::where('sale_id', $record->id)->pluck('id')->toArray();
            $dop_ids = DeliveryOrderProduct::whereIn('sale_product_id', $sp_ids)->pluck('id');
            $dopc_count = DeliveryOrderProductChild::whereIn('delivery_order_product_id', $dop_ids)->count();

            $data['data'][] = [
                'id' => $record->id,
                'doc_no' => $record->doc_no,
                'date' => $record->custom_date == null ? Carbon::parse($record->date)->format('d M Y') : Carbon::parse($record->custom_date)->format('d M Y'),
                'transfer_from' => implode(', ', Sale::where('convert_to', $record->id)->pluck('sku')->toArray()),
                'transfer_to' => implode(', ', DeliveryOrder::whereNotIn('id', $voided_do_ids)->whereIn('id', explode(',', $record->transfer_to))->pluck('sku')->toArray()),
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
                    'no_pending_approval' => ! Approval::where('object_type', Sale::class)->where('object_id', $record->id)->where('data', 'like', '%is_quo%')->where('status', Approval::STATUS_PENDING_APPROVAL)->exists(),
                    'not_in_production' => ! in_array($record->id, $this->getSaleInProduction()),
                    'filled_for_e_invoice' => Customer::forEinvoiceFilled($record->customer_id),
                    'by_pass_for_unpaid' => $record->payment_status != Sale::PAYMENT_STATUS_UNPAID || ($record->payment_status == Sale::PAYMENT_STATUS_UNPAID && $record->by_pass_conversion),
                ],
            ];
        }

        return response()->json($data);
    }

    public function createSaleOrder(Request $req)
    {
        $data = [];

        if ($req->has('qid')) {
            $quo = Sale::findOrFail($req->qid);
            $quo->load('products');

            $data['quo'] = $quo;
        }

        return view('sale_order.form', $data);
    }

    public function editSaleOrder(Sale $sale)
    {
        $is_view = false;
        if (str_contains(Route::currentRouteName(), '.view')) {
            $is_view = true;
        } else {
            $owned = isSuperAdmin() || isSalesCoordinator() || Auth::user()->id == $sale->created_by;
            if (! $owned) {
                abort(403);
            }
        }
        if ($sale->status == Sale::STATUS_CANCELLED) {
            $sale->load([
                'products' => function ($q) {
                    $q->withTrashed()->with(['product' => function ($q) {
                        $q->withTrashed()->with(['children' => function ($q) {
                            $q->withTrashed();
                        }]);
                    }]);
                },
                'products' => function ($q) {
                    $q->withTrashed()->with(['children' => function ($q) {
                        $q->withTrashed();
                    }]);
                },
                'products.warrantyPeriods',
                'paymentAmounts',
            ]);
        } else {
            $sale->load('products.product.children', 'products.children', 'products.warrantyPeriods', 'paymentAmounts');
        }

        $sale->products->each(function ($q) {
            $q->attached_to_do = $q->attachedToDo();
        });

        $has_pending_approval = Approval::where('object_type', Sale::class)->where('object_id', $sale->id)->where('status', Approval::STATUS_PENDING_APPROVAL)->exists();

        $customers = Customer::with('creditTerms.creditTerm', 'salesAgents')
            ->where('id', $sale->customer_id)
            ->orderBy('id', 'desc')->get();
        $customers = $customers->keyBy('id')->all();

        $transfer_from = implode(', ', Sale::where('convert_to', $sale->id)->pluck('sku')->toArray());
        if ($sale->convert_to == null) {
            $transfer_to = null;
        } else {
            $transfer_to = implode(', ', DeliveryOrder::whereIn('id', explode(',', $sale->convert_to))->pluck('sku')->toArray());
        }

        if ($sale->custom_date == null) {
            $sale->custom_date = $sale->created_at;
            $sale->save();
        }

        return view('sale_order.form', [
            'sale' => $sale,
            'convert_from_quo' => $sale->convertFromQuo(),
            // 'payment_editable_only' => $sale->convertFromQuo() || isSuperAdmin() || isFinance(),
            'can_edit_payment' => isSuperAdmin() || isFinance(),
            'is_view' => $is_view,
            'has_pending_approval' => $has_pending_approval,
            'customers' => $customers,
            'transfer_from' => $transfer_from,
            'transfer_to' => $transfer_to,
        ]);
    }

    public function cancelSaleOrder(Request $req)
    {
        $sale = Sale::where('sku', $req->involved_so_skus)->first();

        try {
            DB::beginTransaction();

            $approval = Approval::create([
                'object_type' => Sale::class,
                'object_id' => $sale->id,
                'status' => Approval::STATUS_PENDING_APPROVAL,
                'data' => json_encode([
                    'is_quo' => false,
                    'is_cancellation' => true,
                    'description' => Auth::user()->name.' has requested to cancel the sale order.',
                    'cancellation_remark' => $req->remark ?? null,
                    'charge' => $req->charge,
                ]),
            ]);
            (new Branch)->assign(Approval::class, $approval->id);

            $sale->status = Sale::STATUS_APPROVAL_PENDING;
            $sale->save();

            DB::commit();

            return back()->with('success', 'Sale Order cancelled');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator');
        }
    }

    public function transferBackSaleOrder(Request $req)
    {
        $sale = Sale::where('sku', $req->involved_so_skus)->first();

        try {
            DB::beginTransaction();

            $sale->status = Sale::STATUS_TRANSFERRED_BACK;
            $sale->save();

            // Change converted QUO back to active
            $quos = Sale::where('type', Sale::TYPE_QUO)->where('convert_to', $sale->id)->get();
            for ($i = 0; $i < count($quos); $i++) {
                if ($quos[$i]->open_until >= now()->format('Y-m-d')) {
                    $quos[$i]->expired_at = null;
                } else {
                    $quos[$i]->expired_at = now()->format('Y-m-d');
                }
                $quos[$i]->status = $quos[$i]->hasApprovalAndAllApproved() ? Sale::STATUS_APPROVAL_APPROVED : Sale::STATUS_ACTIVE;
                $quos[$i]->save();
            }

            $sale->delete();

            DB::commit();

            return back()->with('success', 'Sale Order transferred back.');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator');
        }
    }

    public function pdfSaleOrder(Sale $sale)
    {
        if ($sale->is_draft == true || $sale->status == Sale::STATUS_APPROVAL_PENDING || $sale->status == Sale::STATUS_APPROVAL_REJECTED) {
            return abort(403);
        }

        $sps = $sale->products()->withTrashed()->get();
        for ($i = 0; $i < count($sps); $i++) {
            $pc_ids = $sps[$i]->children->pluck('product_children_id');
            $sps[$i]->serial_no = ProductChild::whereIn('id', $pc_ids)->pluck('sku')->toArray();
        }
        $quo_skus = Sale::where('type', Sale::TYPE_QUO)->where('convert_to', $sale->id)->pluck('sku')->toArray();

        $pdf = Pdf::loadView('sale_order.'.(isHiTen($sale->customer->company_group) ? 'hi_ten' : 'powercool').'_pdf', [
            'date' => now()->format('d/m/Y'),
            'sale' => $sale,
            'products' => $sps,
            'saleperson' => $sale->saleperson,
            'customer' => $sale->customer,
            'billing_address' => CustomerLocation::where('id', $sale->billing_address_id)->first(),
            'delivery_address' => CustomerLocation::where('id', $sale->delivery_address_id)->first(),
            'terms' => $sale->paymentTerm ?? null,
            'tax_code' => Setting::where('key', Setting::TAX_CODE_KEY)->value('value'),
            'sst_value' => Setting::where('key', Setting::SST_KEY)->value('value'),
            'quo_skus' => implode(', ', $quo_skus),
            'is_paid' => $sale->payment_status == Sale::PAYMENT_STATUS_PAID,
        ]);
        $pdf->setPaper('A4', 'letter');

        return $pdf->stream($sale->sku.'.pdf');
    }

    public function toDeliveryOrder(Request $req)
    {
        $step = 1;
        $loop = true;

        while ($loop) {
            if ($req->has('pc')) {
                $errors = [];
                $pc_inputs = json_decode($req->pc, true);

                foreach ($pc_inputs as $sp_id => $ipt) {
                    $sp = SaleProduct::where('id', $sp_id)->first();
                    if ($sp->product->isRawMaterial() && $ipt > $sp->remainingQtyForRM()) {
                        $errors['sp_id_'.$sp_id] = 'The quantity is greater than '.$sp->qty;
                    } elseif (! $sp->product->isRawMaterial() && count($ipt) == 0) {
                        unset($pc_inputs[$sp_id]);
                    }
                }
                if (count($errors) > 0) {
                    throw ValidationException::withMessages($errors);
                }

                $step = 6;

                Session::put('convert_product_children', $pc_inputs);

                $cus = Customer::where('id', Session::get('convert_customer_id'))->first();
                $delivery_addresses = $cus->locations()->whereIn('type', [CustomerLocation::TYPE_DELIVERY, CustomerLocation::TYPE_BILLING_ADN_DELIVERY])->get();

                $loop = false;
            } elseif ($req->has('term')) {
                $step = 5;

                Session::put('convert_terms', $req->term);

                // Allowed spc ids
                $so_ids = Sale::where('is_draft', false)->where('type', Sale::TYPE_SO)->whereIn('status', [Sale::STATUS_ACTIVE, Sale::STATUS_APPROVAL_APPROVED])->pluck('id');
                $sp_ids = SaleProduct::whereIn('sale_id', $so_ids)->pluck('id');
                $spc_ids = SaleProductChild::distinct()
                    ->whereIn('sale_product_id', $sp_ids)
                    ->pluck('product_children_id')
                    ->toArray();

                $dopc_ids = DeliveryOrderProductChild::pluck('product_children_id')->toArray();

                $allowed_spc_ids = array_merge(array_diff($spc_ids, $dopc_ids), array_diff($dopc_ids, $spc_ids));

                // Get sp
                $products = collect();
                $sales = Sale::where('type', Sale::TYPE_SO)
                    ->where('is_draft', false)
                    ->whereNotNull('payment_method')
                    ->whereNotIn('id', $this->getSaleInProduction())
                    ->whereIn('status', [Sale::STATUS_ACTIVE, Sale::STATUS_APPROVAL_APPROVED])
                    ->where(function ($q) {
                        $q->where(function ($q) {
                            $q->whereHas('products.product', function ($q) {
                                $q->where('type', Product::TYPE_RAW_MATERIAL)->where('is_sparepart', true)
                                    ->orWhere('type', Product::TYPE_PRODUCT);
                            })->has('products.children');
                        })->orWhere(function ($q) {
                            $q->whereHas('products.product', function ($q) {
                                $q->where('type', Product::TYPE_RAW_MATERIAL)->where('is_sparepart', false);
                            });
                        });
                    })
                    ->whereIn('id', explode(',', $req->so))
                    ->get();

                for ($i = 0; $i < count($sales); $i++) {
                    // Check payment due date is required
                    if ($sales[$i]->payment_due_date == null && $sales[$i]->remainingAmountToPay() > 0) {
                        continue;
                    }
                    $products = $products->merge($sales[$i]->products);
                }

                $loop = false;
            } elseif ($req->has('so')) {
                $step = 4;

                Session::put('convert_sale_order_id', $req->so);

                $sales = Sale::where('type', Sale::TYPE_SO)
                    ->where('is_draft', false)
                    ->whereNotNull('payment_method')
                    ->whereNotIn('id', $this->getSaleInProduction())
                    ->whereIn('status', [Sale::STATUS_ACTIVE, Sale::STATUS_APPROVAL_APPROVED])
                    ->whereIn('id', explode(',', $req->so))
                    ->where('customer_id', Session::get('convert_customer_id'))
                    ->where('sale_id', Session::get('convert_salesperson_id'))
                    ->whereNotNull('payment_term')
                    ->where(function ($q) {
                        $q->wherehas('approval', function ($q) {
                            $q->where('status', Approval::STATUS_APPROVED);
                        })->ordoesnthave('approval');
                    })
                    ->where(function ($q) {
                        $q->where(function ($q) {
                            $q->whereHas('products.product', function ($q) {
                                $q->where('type', Product::TYPE_RAW_MATERIAL)->where('is_sparepart', true)
                                    ->orWhere('type', Product::TYPE_PRODUCT);
                            })->has('products.children');
                        })->orWhere(function ($q) {
                            $q->whereHas('products.product', function ($q) {
                                $q->where('type', Product::TYPE_RAW_MATERIAL)->where('is_sparepart', false);
                            });
                        });
                    })
                    ->distinct()
                    ->get();

                $payment_term_ids = [];
                for ($i = 0; $i < count($sales); $i++) {
                    // Check payment due date is required
                    if ($sales[$i]->payment_due_date == null && $sales[$i]->remainingAmountToPay() > 0) {
                        continue;
                    }
                    $payment_term_ids[] = $sales[$i]->payment_term;
                }

                $terms = CreditTerm::whereIn('id', $payment_term_ids)->get();
                if (count($terms) == 0) {
                    $loop = true;
                    $req->merge(['term' => null]);
                } else {
                    $loop = false;
                }
            } elseif ($req->has('sp')) {
                $step = 3;

                Session::put('convert_salesperson_id', $req->sp);

                $sales = Sale::where('type', Sale::TYPE_SO)
                    ->where('is_draft', false)
                    ->whereNotNull('payment_method')
                    ->whereNotIn('id', $this->getSaleInProduction())
                    ->whereIn('status', [Sale::STATUS_ACTIVE, Sale::STATUS_APPROVAL_APPROVED])
                    ->where('customer_id', Session::get('convert_customer_id'))
                    ->where('sale_id', Session::get('convert_salesperson_id'))
                    ->where(function ($q) {
                        $q->whereHas('approval', function ($q) {
                            $q->where('status', Approval::STATUS_APPROVED);
                        })->orDoesntHave('approval');
                    })
                    ->where(function ($q) {
                        $q->where(function ($q) {
                            $q->whereHas('products.product', function ($q) {
                                $q->where('type', Product::TYPE_RAW_MATERIAL)->where('is_sparepart', true)
                                    ->orWhere('type', Product::TYPE_PRODUCT);
                            })->has('products.children');
                        })->orWhere(function ($q) {
                            $q->whereHas('products.product', function ($q) {
                                $q->where('type', Product::TYPE_RAW_MATERIAL)->where('is_sparepart', false);
                            });
                        });
                    })
                    ->get();

                $sale_orders = [];
                for ($i = 0; $i < count($sales); $i++) {
                    // Check payment due date is required
                    if ($sales[$i]->payment_due_date == null && $sales[$i]->remainingAmountToPay() > 0) {
                        continue;
                    }
                    $sale_orders[] = $sales[$i];
                }

                $loop = false;
            } elseif ($req->has('cus')) {
                $step = 2;

                Session::put('convert_customer_id', $req->cus);

                $sales = Sale::where('type', Sale::TYPE_SO)
                    ->where('is_draft', false)
                    ->whereNotNull('payment_method')
                    ->whereNotIn('id', $this->getSaleInProduction())
                    ->whereIn('status', [Sale::STATUS_ACTIVE, Sale::STATUS_APPROVAL_APPROVED])
                    ->where('customer_id', $req->cus)
                    ->where(function ($q) {
                        $q->whereHas('approval', function ($q) {
                            $q->where('status', Approval::STATUS_APPROVED);
                        })->orDoesntHave('approval');
                    })
                    ->where(function ($q) {
                        $q->where(function ($q) {
                            $q->whereHas('products.product', function ($q) {
                                $q->where('type', Product::TYPE_RAW_MATERIAL)->where('is_sparepart', true)
                                    ->orWhere('type', Product::TYPE_PRODUCT);
                            })->has('products.children');
                        })->orWhere(function ($q) {
                            $q->whereHas('products.product', function ($q) {
                                $q->where('type', Product::TYPE_RAW_MATERIAL)->where('is_sparepart', false);
                            });
                        });
                    })
                    ->distinct()
                    ->get();

                $salesperson_ids = [];
                for ($i = 0; $i < count($sales); $i++) {
                    // Check payment due date is required
                    if ($sales[$i]->payment_due_date == null && $sales[$i]->remainingAmountToPay() > 0) {
                        continue;
                    }
                    $salesperson_ids[] = $sales[$i]->sale_id;
                }

                $salespersons = SalesAgent::whereIn('id', $salesperson_ids)->get();
                $loop = false;
            } else {
                $sales = Sale::where('type', Sale::TYPE_SO)
                    ->where('is_draft', false)
                    ->whereNotIn('id', $this->getSaleInProduction())
                    ->whereIn('status', [Sale::STATUS_ACTIVE, Sale::STATUS_APPROVAL_APPROVED])
                    ->whereNotNull('payment_method')
                    ->where(function ($q) {
                        $q->whereHas('approval', function ($q) {
                            $q->where('status', Approval::STATUS_APPROVED);
                        })->orDoesntHave('approval');
                    })
                    ->where(function ($q) {
                        $q->where(function ($q) {
                            $q->whereHas('products.product', function ($q) {
                                $q->where('type', Product::TYPE_RAW_MATERIAL)->where('is_sparepart', true)
                                    ->orWhere('type', Product::TYPE_PRODUCT);
                            })->has('products.children');
                        })->orWhere(function ($q) {
                            $q->whereHas('products.product', function ($q) {
                                $q->where('type', Product::TYPE_RAW_MATERIAL)->where('is_sparepart', false);
                            });
                        });
                    })
                    ->orderBy('id', 'desc')
                    ->distinct()
                    ->get();

                $customer_ids = [];
                for ($i = 0; $i < count($sales); $i++) {
                    // Check payment due date is required
                    if ($sales[$i]->payment_due_date == null && $sales[$i]->remainingAmountToPay() > 0) {
                        continue;
                    }

                    for ($j = 0; $j < count($sales[$i]->products); $j++) {
                        $is_rm = $sales[$i]->products[$j]->product->isRawMaterial();

                        if ($is_rm && $sales[$i]->products[$j]->remainingQty() > 0) {
                            $customer_ids[] = $sales[$i]->customer_id;
                        } elseif (! $is_rm) {
                            $customer_ids[] = $sales[$i]->customer_id;
                        }
                    }
                }
                $customers = Customer::whereIn('id', $customer_ids)->get();
                $loop = false;
            }
        }

        if (Session::get('convert_customer_id') != null) {
            $selected_customer = Customer::where('id', Session::get('convert_customer_id'))->first();
        }
        if (Session::get('convert_salesperson_id') != null) {
            $selected_salesperson = SalesAgent::where('id', Session::get('convert_salesperson_id'))->first();
        }
        if (Session::get('convert_terms') != null) {
            $selected_term = CreditTerm::where('id', Session::get('convert_terms'))->first();
        }
        if (Session::get('convert_sale_order_id') != null) {
            $selected_so_sku = Sale::where('type', Sale::TYPE_SO)->where('id', explode(',', Session::get('convert_sale_order_id')))->pluck('sku')->toArray();
            $selected_so_sku = implode(', ', $selected_so_sku);
        }

        return view('sale_order.convert', [
            'step' => $step,
            'customers' => $customers ?? [],
            'salespersons' => $salespersons ?? [],
            'sale_orders' => $sale_orders ?? [],
            'products' => $products ?? [],
            'terms' => $terms ?? [],
            'allowed_spc_ids' => $allowed_spc_ids ?? [],
            'delivery_addresses' => $delivery_addresses ?? [],
            'selected_customer' => $selected_customer ?? null,
            'selected_salesperson' => $selected_salesperson ?? null,
            'selected_term' => $selected_term ?? null,
            'selected_so_sku' => $selected_so_sku ?? null,
        ]);
    }

    public function converToDeliveryOrder(Request $req)
    {
        $sp_ids = [];
        $pc_inputs = Session::get('convert_product_children');

        foreach ($pc_inputs as $sp_id => $ipt) {
            $sp_ids[] = $sp_id;
        }

        try {
            DB::beginTransaction();

            // Approval
            $approval_required = false;
            $by_pass_payment_method_ids = PaymentMethod::where('by_pass_conversion', true)->pluck('id')->toArray();

            $delivery_address = CustomerLocation::where('id', $req->delivery_address)->first()->formatAddress();

            $products = collect();
            foreach (SaleProduct::whereIn('id', $sp_ids)->cursor() as $sp) {
                $products->push($sp->product);
            }

            // Prepare data
            $so_ids = explode(',', Session::get('convert_sale_order_id'));
            $sales = Sale::whereIn('id', $so_ids)->get();

            $is_hi_ten = false;
            for ($i = 0; $i < count($sales); $i++) {
                $is_hi_ten = isHiTen($sales[$i]->customer->company_group);
                if ($is_hi_ten == true) {
                    break;
                }
            }
            $existing_skus = DeliveryOrder::withoutGlobalScope(BranchScope::class)->withoutGlobalScope(ApprovedScope::class)->pluck('sku')->toArray();
            $sku = generateSku('DO', $existing_skus, $is_hi_ten);
            $filename = str_replace('/', '-', $sku).'.pdf';
            $soc_alter_qty = [];
            $sale_orders = collect();

            // Create DO
            $do = DeliveryOrder::create([
                'customer_id' => Session::get('convert_customer_id'),
                'sale_id' => Session::get('convert_salesperson_id'),
                'payment_terms' => Session::get('convert_terms'),
                'sku' => $sku,
                'filename' => $filename,
                'created_by' => Auth::user()->id,
                'delivery_address_id' => $req->delivery_address,
                'delivery_address' => $delivery_address,
            ]);
            (new Branch)->assign(DeliveryOrder::class, $do->id);

            // Create DO products
            foreach (SaleProduct::whereIn('id', $sp_ids)->cursor() as $sp) {
                $is_raw_material = $sp->product->isRawMaterial();

                $dop = DeliveryOrderProduct::create([
                    'delivery_order_id' => $do->id,
                    'sale_order_id' => $sp->sale->id,
                    'sale_product_id' => $sp->id,
                    'qty' => $is_raw_material ? $pc_inputs[$sp->id] : null,
                ]);

                if (! $is_raw_material) {
                    // Create DO product children
                    $dopc = [];
                    for ($i = 0; $i < count($pc_inputs[$sp->id]); $i++) {
                        $spc = SaleProductChild::where('id', $pc_inputs[$sp->id][$i]['sale_product_child_id'])->where('sale_product_id', $sp->id)->first();
                        $dopc[] = [
                            'delivery_order_product_id' => $dop->id,
                            'product_children_id' => $spc->product_children_id,
                            'remark' => $pc_inputs[$sp->id][$i]['remark'] == '' ? null : $pc_inputs[$sp->id][$i]['remark'],
                            'created_at' => $do->created_at,
                            'updated_at' => $do->updated_at,
                        ];
                    }
                    DeliveryOrderProductChild::insert($dopc);

                    $soc_alter_qty[$sp->id] = count($pc_inputs[$sp->id]);
                }

                $sale_orders->push($sp->sale);
            }

            // Create PDF
            $pdf_products = [];
            for ($i = 0; $i < count($do->products); $i++) {
                if ($do->products[$i]->saleProduct->product->isRawMaterial()) {
                    $pdf_products[] = [
                        'stock_code' => $do->products[$i]->saleProduct->product->sku,
                        'desc' => $do->products[$i]->saleProduct->product->model_desc,
                        'qty' => $do->products[$i]->qty,
                        'uom' => $do->products[$i]->saleProduct->uom,
                    ];
                } else {
                    $dopcs = $do->products[$i]->children;

                    $pc_ids = [];
                    $serial_no = [];
                    for ($j = 0; $j < count($dopcs); $j++) {
                        $pc_ids[] = $dopcs[$j]->product_children_id;
                        $serial_no[] = [
                            'sku' => ProductChild::where('id', $dopcs[$j]->product_children_id)->value('sku'),
                            'remark' => $dopcs[$j]->remark,
                        ];
                    }
                    $sp_id = SaleProductChild::where('product_children_id', $dopcs[count($dopcs) - 1]->product_children_id)->value('sale_product_id');
                    $sp = SaleProduct::where('id', $sp_id)->first();

                    $pdf_products[] = [
                        'stock_code' => $dopcs[count($dopcs) - 1]->productChild->parent->sku,
                        'desc' => $dopcs[count($dopcs) - 1]->productChild->parent->model_desc,
                        'qty' => count($serial_no),
                        'uom' => $sp->uom,
                        'warranty_periods' => $sp->warrantyPeriods,
                        'serial_no' => $serial_no,
                    ];
                }
            }
            $pdf = Pdf::loadView('sale_order.'.($is_hi_ten ? 'hi_ten' : 'powercool').'_do_pdf', [
                'date' => now()->format('d/m/Y'),
                'sku' => $sku,
                'customer' => Customer::where('id', Session::get('convert_customer_id'))->first(),
                'salesperson' => SalesAgent::where('id', Session::get('convert_salesperson_id'))->first(),
                'sale_orders' => $sale_orders,
                'products' => $pdf_products,
                'billing_address' => (new CustomerLocation)->defaultBillingAddress(Session::get('convert_customer_id')),
                'delivery_address' => CustomerLocation::where('id', $req->delivery_address)->first(),
                'terms' => Session::get('convert_terms'),
                'warehouse' => $sale_orders[0]->warehouse ?? '',
                'store' => $sale_orders[0]->store ?? '',
            ]);
            $pdf->setPaper('A4', 'letter');
            $content = $pdf->download()->getOriginalContent();
            Storage::put(self::DELIVERY_ORDER_PATH.$filename, $content);

            // Change SO's status to converted, if SO has no product left to convert
            $so_for_desc = null;
            for ($i = 0; $i < count($sale_orders); $i++) {
                if ($sale_orders[$i]->hasNoMoreQtyToConvertDO()) {
                    $sale_orders[$i]->status = Sale::STATUS_CONVERTED;
                } else {
                    $sale_orders[$i]->status = Sale::STATUS_PARTIALLY_CONVERTED;
                }

                $current_do_ids = [];
                if ($sale_orders[$i]->convert_to != null) {
                    $current_do_ids = explode(',', $sale_orders[$i]->convert_to);
                }
                $current_do_ids[] = $do->id;

                $sale_orders[$i]->convert_to = implode(',', $current_do_ids);
                $sale_orders[$i]->save();

                if ($approval_required == false && $sale_orders[$i]->payment_status == Sale::PAYMENT_STATUS_UNPAID && ! in_array($sale_orders[$i]->payment_method, $by_pass_payment_method_ids)) {
                    $approval_required = true;
                    $so_for_desc = $sale_orders[$i]->sku;
                }

                SaleOrderCancellation::calCancellation($sale_orders[$i], 2, $soc_alter_qty);
            }

            if ($approval_required) {
                $approval = Approval::create([
                    'object_type' => DeliveryOrder::class,
                    'object_id' => $do->id,
                    'status' => Approval::STATUS_PENDING_APPROVAL,
                    'data' => json_encode([
                        'description' => 'The status for '.$so_for_desc.' is unpaid and not able to by pass conversion',
                    ]),
                ]);
                (new Branch)->assign(Approval::class, $approval->id);
                // Update QUO/SO status
                $do->status = DeliveryOrder::STATUS_APPROVAL_PENDING;
                $do->save();
            }

            // Generate Invoice
            $ok = $this->convertToInvoice([$do->id], Session::get('convert_customer_id'), Session::get('convert_terms'));
            if (! $ok) {
                throw new Exception('Failed to generate invoice');
            }
            if ($approval_required) {
                DeliveryOrder::withoutGlobalScope(ApprovedScope::class)->whereIn('id', [$do->id])->update([
                    'status' => DeliveryOrder::STATUS_APPROVAL_PENDING,
                ]);
            }

            DB::commit();

            return redirect(route('delivery_order.index'))->with('success', 'Sale Order has converted');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator');
        }
    }

    public function upsertDetails(Request $req)
    {
        if ($req->type == 'so' || $req->type == 'cash-sale') {
            if ($req->sale_id == null) {
                $convert_from_quo = false;
            } else {
                $sale = Sale::where('id', $req->sale_id)->first();
                $convert_from_quo = $sale->convertFromQuo();
            }
        }
        // Validate form
        $rules = [
            // upsertQuoDetails
            'sale_id' => 'nullable',
            'quo_id' => 'nullable',
            'sale' => 'required',
            'customer' => 'required',
            'reference' => 'nullable',
            'store' => 'nullable',
            'warehouse' => 'nullable',
            'from' => 'nullable|max:250',
            'cc' => 'nullable|max:250',
            'status' => 'required',
            'report_type' => 'required',
            'payment_term' => 'nullable',
            // upsertRemark
            'remark' => 'nullable|max:250',
        ];
        if ($req->type == 'quo') {
            $rules['billing_address'] = 'nullable';
            $rules['new_billing_address1'] = 'required_if:billing_address,null|max:250';
            $rules['new_billing_address2'] = 'nullable|max:250';
            $rules['new_billing_address3'] = 'nullable|max:250';
            $rules['new_billing_address4'] = 'nullable|max:250';
            $rules['delivery_address'] = 'nullable';
            $rules['new_delivery_address1'] = 'required_if:delivery_address,null|max:250';
            $rules['new_delivery_address2'] = 'nullable|max:250';
            $rules['new_delivery_address3'] = 'nullable|max:250';
            $rules['new_delivery_address4'] = 'nullable|max:250';
        }
        if ($req->type == 'quo') {
            $rules['open_until'] = 'required';
        }
        if ($req->type == 'quo' || (isset($convert_from_quo) && ! $convert_from_quo)) {
            // upsertProDetails
            $rules['product_order_id'] = 'nullable';
            $rules['product_order_id.*'] = 'nullable';
            $rules['product_id'] = 'required';
            $rules['product_id.*'] = 'required';
            $rules['customize_product'] = 'required';
            $rules['customize_product.*'] = 'nullable|max:250';
            $rules['product_desc'] = 'required';
            $rules['product_desc.*'] = 'nullable|max:250';
            $rules['qty'] = 'required';
            $rules['qty.*'] = 'required';
            $rules['foc'] = 'required';
            $rules['foc.*'] = 'required';
            $rules['uom'] = 'required';
            $rules['uom.*'] = 'required';
            $rules['selling_price'] = 'nullable';
            $rules['selling_price.*'] = 'nullable';
            $rules['unit_price'] = 'required';
            $rules['unit_price.*'] = 'nullable';
            $rules['promotion_id'] = 'required';
            $rules['promotion_id.*'] = 'nullable';
            $rules['product_serial_no'] = 'nullable';
            $rules['product_serial_no.*'] = 'nullable';
            $rules['warranty_period'] = 'nullable';
            $rules['warranty_period.*'] = 'nullable';
            $rules['warranty_period.*.*'] = 'nullable';
            $rules['discount'] = 'required';
            $rules['discount.*'] = 'nullable';
            $rules['product_remark'] = 'required';
            $rules['product_remark.*'] = 'nullable|max:250';
            $rules['override_selling_price'] = 'nullable';
            $rules['override_selling_price.*'] = 'nullable';
        }
        if ($req->type == 'so' || $req->type == 'cash-sale') {
            // upsertPayDetails
            $credit_payment_method_ids = PaymentMethod::where('name', 'like', '%credit%')->pluck('id')->toArray();
            if (in_array($req->payment_method, $credit_payment_method_ids)) {
                $rules['payment_term'] = 'required';
            } else {
                $rules['payment_term'] = 'nullable';
            }
            $rules['payment_method'] = 'nullable';
            $rules['payment_due_date'] = 'nullable';
            $rules['payment_remark'] = 'nullable|max:250';
            if (in_array(Role::SUPERADMIN, getUserRoleId(Auth::user())) || in_array(Role::FINANCE, getUserRoleId(Auth::user()))) {
                $rules['account_amount'] = 'nullable';
                $rules['account_amount.*'] = 'nullable';
                $rules['account_date'] = 'nullable';
                $rules['account_date.*'] = 'nullable';
                $rules['account_ref_no'] = 'nullable';
                $rules['account_ref_no.*'] = 'nullable|max:250';
            }
        }
        if ($req->type == 'cash-sale') {
            $rules['customer'] = 'nullable';
            $rules['custom_customer'] = 'required|max:250';
            $rules['custom_mobile'] = 'nullable';
            $rules['company_group'] = 'required';
        }

        $req->validate($rules, [], [
            // upsertQuoDetails
            'sale' => 'sales agent',
            'report_type' => 'type',
            'customer' => 'company',
            'custom_customer' => 'company',
            'custom_mobile' => 'mobile',
            'open_until' => 'validity',
            // upsertProDetails
            'product_id.*' => 'product',
            'product_desc.*' => 'product description',
            'qty.*' => 'quantity',
            'uom.*' => 'UOM',
            'selling_price.*' => 'selling price',
            'unit_price.*' => 'unit price',
            'product_serial_no.*' => 'product serial no',
            'warranty_period.*.*' => 'warranty period',
            'discount.*' => 'discount',
            'remark' => 'remark',
            'override_selling_price' => 'override selling price',
            // upsertPayDetails
            'account_amount.*' => 'amount',
            'account_date.*' => 'date',
            'account_ref_no.*' => 'reference no',
        ]);

        // Check duplicate serial no is selected (upsertProDetails)
        if (isset($req->product_serial_no)) {
            $serial_no = [];
            for ($i = 0; $i < count($req->product_serial_no); $i++) {
                if ($req->product_serial_no[$i] == null) {
                    continue;
                }

                $match = array_intersect($serial_no, $req->product_serial_no[$i]);
                if (count($match) > 0) {
                    return Response::json([
                        'errors' => [
                            'product_serial_no' => 'Please make sure no duplicate serial no is selected',
                        ],
                    ], HttpFoundationResponse::HTTP_BAD_REQUEST);
                }
                $serial_no = array_merge($serial_no, $req->product_serial_no[$i]);
            }
        }
        // Check raw material has enough qty (upsertProDetails)
        // for ($i = 0; $i < count($req->product_id); $i++) {
        //     $prod = Product::where('id', $req->product_id[$i])->first();

        //     if ($prod->type == Product::TYPE_RAW_MATERIAL && $prod->is_sparepart == false) {
        //         $max_qty = $prod->warehouseAvailableStock($req->sale_id);

        //         if ($req->qty[$i] > $max_qty) {
        //             return Response::json([
        //                 'errors' => [
        //                     'qty.' . $i => 'The quantity has exceed the available quantity (' . $max_qty . ')',
        //                 ],
        //             ], HttpFoundationResponse::HTTP_BAD_REQUEST);
        //         }
        //     }
        // }

        try {
            DB::beginTransaction();

            $data = [];

            $res = $this->upsertQuoDetails($req, true, false, null, false)->getData();
            if ($res->result == false) {
                throw new \Exception('upsertQuoDetails failed');
            }
            if ($res->sale) {
                $data['sale'] = $res->sale;
                $req->merge(['sale_id' => $res->sale->id]);
            }
            if (isset($res->approval_required)) {
                $pending_approval_alrdy = $res->approval_required;
            }

            $res = $this->upsertProDetails($req, true, isset($convert_from_quo) ? $convert_from_quo : false, false, false)->getData();
            if ($res->result == false) {
                throw new \Exception('upsertProDetails failed');
            }
            if ($res->product_ids) {
                $data['product_ids'] = $res->product_ids;
            }
            $has_pending_and_rejected = SaleProduct::where('sale_id', $req->sale_id)->whereIn('status', [SaleProduct::STATUS_APPROVAL_PENDING, SaleProduct::STATUS_APPROVAL_REJECTED])->exists();
            if (isset($pending_approval_alrdy) && $pending_approval_alrdy == false && ! $has_pending_and_rejected) {
                Sale::where('id', $req->sale_id)->whereNot('status', Sale::STATUS_APPROVAL_APPROVED)->update([
                    'status' => Sale::STATUS_ACTIVE,
                ]);
            }

            if ($req->type == 'so' || $req->type == 'cash-sale') {
                $res = $this->upsertPayDetails($req, true)->getData();
                if (isset($res->err_msg) && $res->err_msg != null) {
                    DB::rollBack();

                    return Response::json([
                        'errors' => [
                            'account_err_msg' => $res->err_msg,
                        ],
                    ], HttpFoundationResponse::HTTP_BAD_REQUEST);
                } elseif ($res->result == false) {
                    throw new \Exception('upsertPayDetails failed');
                }
            }

            $res = $this->upsertRemark($req, true)->getData();
            if ($res->result == false) {
                throw new \Exception('upsertRemark failed');
            }

            DB::commit();

            return Response::json([
                'result' => true,
                'data' => $data,
            ], HttpFoundationResponse::HTTP_OK);
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return Response::json([
                'result' => false,
            ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function saveAsDraft(Request $req)
    {
        try {
            DB::beginTransaction();

            // insert into columns
            $data = [];

            $res = $this->upsertQuoDetails($req, true, false, null, true)->getData();
            if ($res->result == false) {
                throw new \Exception('upsertQuoDetails failed');
            }
            if ($res->sale) {
                $data['sale'] = $res->sale;
                $req->merge(['sale_id' => $res->sale->id]);
            }

            $res = $this->upsertProDetails($req, true, false, true, true)->getData();
            if ($res->result == false) {
                throw new \Exception('upsertProDetails failed');
            }
            if ($res->product_ids) {
                $data['product_ids'] = $res->product_ids;
            }

            if ($req->type == 'so' || $req->type == 'cash-sale') {
                $res = $this->upsertPayDetails($req, true)->getData();
                if (isset($res->err_msg) && $res->err_msg != null) {
                    DB::rollBack();

                    return Response::json([
                        'errors' => [
                            'account_err_msg' => $res->err_msg,
                        ],
                    ], HttpFoundationResponse::HTTP_BAD_REQUEST);
                } elseif ($res->result == false) {
                    throw new \Exception('upsertPayDetails failed');
                }
            }

            $res = $this->upsertRemark($req, true)->getData();
            if ($res->result == false) {
                throw new \Exception('upsertRemark failed');
            }

            DB::commit();

            return Response::json([
                'result' => true,
            ], HttpFoundationResponse::HTTP_OK);
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return Response::json([
                'result' => false,
            ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function upsertQuoDetails(Request $req, bool $validated = false, bool $convert_from_quo = false, ?string $so_sku_to_use = null, bool $is_draft = false)
    {
        if (! $validated) {
            // Validate form
            $rules = [
                'sale_id' => 'nullable',
                'quo_id' => 'nullable',
                'sale' => 'required',
                'customer' => 'required',
                'reference' => 'nullable',
                'store' => 'nullable',
                'warehouse' => 'nullable',
                'from' => 'nullable|max:250',
                'cc' => 'nullable|max:250',
                'status' => 'required',
                'report_type' => 'required',
                'payment_term' => 'nullable',
            ];
            if ($req->type == 'quo') {
                $rules['billing_address'] = 'nullable';
                $rules['new_billing_address1'] = 'required_if:billing_address,null|max:250';
                $rules['new_billing_address2'] = 'nullable|max:250';
                $rules['new_billing_address3'] = 'nullable|max:250';
                $rules['new_billing_address4'] = 'nullable|max:250';

                $rules['delivery_address'] = 'nullable';
                $rules['new_delivery_address1'] = 'required_if:delivery_address,null|max:250';
                $rules['new_delivery_address2'] = 'nullable|max:250';
                $rules['new_delivery_address3'] = 'nullable|max:250';
                $rules['new_delivery_address4'] = 'nullable|max:250';
            }
            if ($req->type == 'quo') {
                $rules['open_until'] = 'required';
            }
            if ($req->type == 'cash-sale') {
                $rules['customer'] = 'nullable';
                $rules['custom_customer'] = 'required|max:250';
                $rules['custom_mobile'] = 'nullable';
                $rules['company_group'] = 'required';
            }
            if ($convert_from_quo) {
                $rules['billing_address'] = 'nullable';
            }
            $req->validate($rules, [], [
                'report_type' => 'type',
                'customer' => 'company',
                'open_until' => 'validity',
            ]);
        }

        try {
            DB::beginTransaction();

            if ($req->type == 'cash-sale') {
                if ($req->sale_id != null) {
                    CashSaleLocation::where('cash_sale_id', $req->sale_id)->delete();
                }
                // Billing
                $bill_add = CashSaleLocation::create([
                    'address1' => $req->new_billing_address1,
                    'address2' => $req->new_billing_address2,
                    'address3' => $req->new_billing_address3,
                    'address4' => $req->new_billing_address4,
                    'type' => CashSaleLocation::TYPE_BILLING,
                ]);
                $req->merge(['billing_address' => $bill_add->id]);
                // Delivery
                $del_add = CashSaleLocation::create([
                    'address1' => $req->new_delivery_address1,
                    'address2' => $req->new_delivery_address2,
                    'address3' => $req->new_delivery_address3,
                    'address4' => $req->new_delivery_address4,
                    'type' => CashSaleLocation::TYPE_DELIVERY,
                ]);
                $req->merge(['delivery_address' => $del_add->id]);
            } else {
                // Billing
                if ($req->billing_address != null || $req->new_billing_address1 != null) {
                    if ($req->billing_address != null) {
                        $bill_add = CustomerLocation::where('id', $req->billing_address)->first();
                    } else {
                        $bill_add = CustomerLocation::create([
                            'customer_id' => $req->customer,
                            'address1' => $req->new_billing_address1,
                            'address2' => $req->new_billing_address2,
                            'address3' => $req->new_billing_address3,
                            'address4' => $req->new_billing_address4,
                            'type' => CustomerLocation::TYPE_BILLING,
                            'is_default' => false,
                        ]);
                        $req->merge(['billing_address' => $bill_add->id]);
                    }
                }
                // Delivery
                if ($req->delivery_address != null || $req->new_delivery_address1 != null) {
                    if ($req->delivery_address != null) {
                        $del_add = CustomerLocation::where('id', $req->delivery_address)->first();
                    } else {
                        $del_add = CustomerLocation::create([
                            'customer_id' => $req->customer,
                            'address1' => $req->new_delivery_address1,
                            'address2' => $req->new_delivery_address2,
                            'address3' => $req->new_delivery_address3,
                            'address4' => $req->new_delivery_address4,
                            'type' => CustomerLocation::TYPE_DELIVERY,
                            'is_default' => false,
                        ]);
                        $req->merge(['delivery_address' => $del_add->id]);
                    }
                }
            }

            if ($req->sale_id == null) {
                if ($so_sku_to_use != null) {
                    $sku = $so_sku_to_use;
                } else {
                    if ($req->type == 'cash-sale') {
                        $company_group = $req->company_group;
                    } else {
                        $company_group = Customer::where('id', $req->customer)->value('company_group');
                    }
                    $existing_skus = Sale::withoutGlobalScope(BranchScope::class)->where('type', $req->type == 'quo' ? Sale::TYPE_QUO : ($req->type == 'so' ? Sale::TYPE_SO : Sale::TYPE_CASH_SALE))->pluck('sku')->toArray();
                    $transferred_back_skus = Sale::withTrashed()->where('type', Sale::TYPE_SO)->where('status', Sale::STATUS_TRANSFERRED_BACK)->distinct()->pluck('sku')->toArray();
                    $all_skus = array_merge($existing_skus, $transferred_back_skus);
                    $sku = generateSku($req->type == 'quo' ? 'QT' : ($req->type == 'so' ? 'SO' : 'CS'), $all_skus, isHiTen($company_group));
                }

                $sales_id = DB::table('sales_sales_agents')->where('sales_agent_id', $req->sale)->value('sales_id');
                $created_by = $sales_id == null ? Auth::user()->id : $sales_id;

                $sale = Sale::create([
                    'type' => $req->type == 'quo' ? Sale::TYPE_QUO : ($req->type == 'so' ? Sale::TYPE_SO : Sale::TYPE_CASH_SALE),
                    'sku' => $sku,
                    'custom_date' => $req->type == 'so' || $req->type == 'cash-sale' ? now() : null,
                    'sale_id' => $req->sale,
                    'customer_id' => $req->type == 'cash-sale' ? null : $req->customer,
                    'custom_customer' => $req->type == 'cash-sale' ? $req->custom_customer : null,
                    'custom_mobile' => $req->type == 'cash-sale' ? $req->custom_mobile : null,
                    'open_until' => $req->open_until,
                    'store' => $req->store,
                    'warehouse' => $req->warehouse,
                    'reference' => $req->type == 'quo' ? $req->reference : json_encode(explode(',', $req->reference)),
                    'quo_from' => $req->from,
                    'quo_cc' => $req->cc,
                    'status' => $req->status,
                    'is_draft' => $is_draft,
                    'report_type' => $req->report_type,
                    'billing_address_id' => $req->billing_address ?? null,
                    'billing_address' => isset($bill_add) ? $bill_add->formatAddress() : null,
                    'delivery_address_id' => $req->delivery_address ?? null,
                    'delivery_address' => isset($del_add) ? $del_add->formatAddress() : null,
                    'payment_term' => $req->payment_term,
                    'payment_method' => $req->payment_method,
                    'created_by' => $created_by,
                ]);

                (new Branch)->assign(Sale::class, $sale->id);
            } else {
                $sale = Sale::where('id', $req->sale_id)->first();

                $ref = null;
                if ($req->type == 'quo') {
                    $ref = $req->reference;
                    if ($req->open_until >= now()->format('Y-m-d')) {
                        $sale->expired_at = null;
                        $sale->save();
                    } else {
                        $sale->expired_at = now()->format('Y-m-d');
                        $sale->save();
                    }
                } elseif ($req->type != 'quo' && $req->reference != null) {
                    $ref = json_encode(explode(',', $req->reference));
                }

                $sale->update([
                    'custom_date' => $req->custom_date ?? $sale->created_at,
                    'sale_id' => $req->sale,
                    'customer_id' => $req->type == 'cash-sale' ? null : $req->customer,
                    'custom_customer' => $req->type == 'cash-sale' ? $req->custom_customer : null,
                    'custom_mobile' => $req->type == 'cash-sale' ? $req->custom_mobile : null,
                    'open_until' => $req->open_until,
                    'reference' => $ref,
                    'store' => $req->store,
                    'warehouse' => $req->warehouse,
                    'quo_from' => $req->from,
                    'quo_cc' => $req->cc,
                    'status' => $sale->status == Sale::STATUS_APPROVAL_PENDING ? $sale->status : $req->status,
                    'is_draft' => $is_draft,
                    'report_type' => $req->report_type,
                    'billing_address_id' => $req->billing_address ?? null,
                    'billing_address' => isset($bill_add) ? $bill_add->formatAddress() : null,
                    'delivery_address_id' => $req->delivery_address ?? null,
                    'delivery_address' => isset($del_add) ? $del_add->formatAddress() : null,
                    'payment_term' => $req->payment_term,
                    'payment_method' => $req->payment_method,
                    'updated_by' => Auth::user()->id,
                ]);
            }
            if ($req->type == 'cash-sale') {
                $bill_add->cash_sale_id = $sale->id;
                $bill_add->save();
                $del_add->cash_sale_id = $sale->id;
                $del_add->save();
            }
            // Payment method approval
            if (! $is_draft && $req->payment_method != null && $req->type != 'cash-sale') {
                $quo_skip_approval = false;
                if ($req->type == 'quo') {
                    $quo_skip_approval = Approval::where('object_type', Sale::class)->where('object_id', $sale->id)
                        ->whereIn('status', [Approval::STATUS_APPROVED, Approval::STATUS_PENDING_APPROVAL])
                        ->where('data', 'like', '%is_quo%')
                        ->where('data', 'like', '%is_payment_method%')
                        ->exists();
                }
                if (! $quo_skip_approval) {
                    $approval_required = false;
                    if ($req->type == 'quo') {
                        $credit_term_payment_method_ids = getPaymentMethodCreditTermIds();
                        $approval_required = in_array($req->payment_method, $credit_term_payment_method_ids);
                    } elseif ($req->type == 'so' || $req->type == 'cash-sale') {
                        $customer_ct_ids = ObjectCreditTerm::where('object_type', Customer::class)->where('object_id', $req->customer)->pluck('credit_term_id')->toArray();
                        $approval_required = ($req->payment_term) > 0 && ! in_array($req->payment_term, $customer_ct_ids);
                    }

                    if ($approval_required) {
                        $customer = Customer::where('id', $sale->customer_id)->first();

                        $terms = [];
                        for ($i = 0; $i < count($customer->creditTerms); $i++) {
                            $terms[] = $customer->creditTerms[$i]->creditTerm->name;
                        }

                        $approval = Approval::create([
                            'object_type' => Sale::class,
                            'object_id' => $sale->id,
                            'status' => Approval::STATUS_PENDING_APPROVAL,
                            'data' => $req->type == 'quo' ? json_encode([
                                'is_quo' => true,
                                'is_payment_method' => true,
                                'description' => 'The payment method for '.$sale->sku.' is selected as Credit Term. ('.implode(',', $terms).')',
                            ]) : json_encode([
                                'is_payment_method' => true,
                                'description' => 'The payment method for '.$sale->sku.' is selected as Credit Term. ('.implode(',', $terms).')',
                            ]),
                        ]);
                        (new Branch)->assign(Approval::class, $approval->id);

                        if ($sale->payment_method_status == Sale::STATUS_APPROVAL_REJECTED) {
                            $sale->payment_method_revised = true;
                        }
                        $sale->status = Sale::STATUS_APPROVAL_PENDING;
                        $sale->payment_method_status = Sale::STATUS_APPROVAL_PENDING;
                        $sale->save();
                    }
                }
            }

            if ($req->quo_id != null) {
                Sale::where('id', $req->quo_id)->delete();
            }

            DB::commit();

            return Response::json([
                'result' => true,
                'sale' => $sale,
                'approval_required' => isset($approval_required) ? $approval_required : false,
            ], HttpFoundationResponse::HTTP_OK);
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return Response::json([
                'result' => false,
            ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function upsertProDetails(Request $req, bool $validated = false, bool $convert_from_quo = false, bool $is_draft = false, bool $skip_approval = false)
    {
        if (! $validated) {
            // Validate form
            $rules = [
                'sale_id' => 'required',
                'product_order_id' => 'nullable',
                'product_order_id.*' => 'nullable',
                'product_id' => 'required',
                'product_id.*' => 'required',
                'customize_product' => 'nullable',
                'customize_product.*' => 'nullable|max:250',
                'product_desc' => 'required',
                'product_desc.*' => 'nullable|max:250',
                'qty' => 'required',
                'qty.*' => 'required',
                'foc' => 'required',
                'foc.*' => 'required',
                'uom' => 'required',
                'uom.*' => 'required',
                'selling_price' => 'nullable',
                'selling_price.*' => 'nullable',
                'unit_price' => 'required',
                'unit_price.*' => 'nullable',
                'promotion_id' => 'required',
                'promotion_id.*' => 'nullable',
                'product_serial_no' => 'nullable',
                'product_serial_no.*' => 'nullable',
                'warranty_period' => 'nullable',
                'warranty_period.*' => 'nullable',
                'warranty_period.*.*' => 'nullable',
                'discount' => 'required',
                'discount.*' => 'nullable',
                'product_remark' => 'required',
                'product_remark.*' => 'nullable|max:250',
                'override_selling_price' => 'nullable',
                'override_selling_price.*' => 'nullable',
            ];
            $req->validate($rules, [], [
                'product_id.*' => 'product',
                'product_desc.*' => 'product description',
                'qty.*' => 'quantity',
                'uom.*' => 'UOM',
                'selling_price.*' => 'selling price',
                'unit_price.*' => 'unit price',
                'product_serial_no.*' => 'product serial no',
                'warranty_period.*' => 'warranty period',
                'discount.*' => 'discount',
                'product_remark.*' => 'remark',
                'override_selling_price.*' => 'override selling price',
            ]);
            // Check duplicate serial no is selected
            if (isset($req->product_serial_no)) {
                $serial_no = [];
                for ($i = 0; $i < count($req->product_serial_no); $i++) {
                    if ($req->product_serial_no[$i] == null) {
                        continue;
                    }

                    $match = array_intersect($serial_no, $req->product_serial_no[$i]);
                    if (count($match) > 0) {
                        return Response::json([
                            'product_serial_no' => 'Please make sure no duplicate serial no is selected',
                        ], HttpFoundationResponse::HTTP_BAD_REQUEST);
                    }
                    $serial_no = array_merge($serial_no, $req->product_serial_no[$i]);
                }
            }
            // Check raw material has enough qty (upsertProDetails)
            // for ($i = 0; $i < count($req->product_id); $i++) {
            //     $prod = Product::where('id', $req->product_id[$i])->first();

            //     if ($prod->type == Product::TYPE_RAW_MATERIAL && $prod->is_sparepart == false) {
            //         $max_qty = $prod->warehouseAvailableStock($req->sale_id);

            //         if ($req->qty[$i] > $max_qty) {
            //             return Response::json([
            //                 'errors' => [
            //                     'qty.' . $i => 'The quantity has exceed the available quantity (' . $max_qty . ')',
            //                 ],
            //             ], HttpFoundationResponse::HTTP_BAD_REQUEST);
            //         }
            //     }
            // }
        }

        try {
            DB::beginTransaction();

            if (! $convert_from_quo) {
                if ($req->product_order_id != null) {
                    $order_idx = array_filter($req->product_order_id, function ($val) {
                        return $val != null;
                    });

                    $sps = SaleProduct::where('sale_id', $req->sale_id)->whereNotIn('id', $order_idx ?? [])->get();
                    for ($i = 0; $i < count($sps); $i++) {
                        SaleProductChild::where('sale_product_id', $sps[$i]->id)->delete();
                        $sps[$i]->delete();
                    }
                }

                $now = now();
                for ($i = 0; $i < count($req->product_id); $i++) {
                    $product_id = $req->product_id[$i];
                    if ($is_draft && $product_id == null) {
                        continue;
                    }

                    if ($req->customize_product != null && $req->customize_product[$i] != null) {
                        $branch = getCurrentUserBranch();
                        if ($branch != null) {
                            $category = InventoryCategory::where('name', 'like', '%CUSTOMISE%')->whereHas('branch', function ($q) use ($branch) {
                                $q->where('location', $branch);
                            })->first();
                        }

                        $customize_prod = Product::create([
                            'inventory_category_id' => isset($category) ? $category->id : null,
                            'type' => Product::TYPE_PRODUCT,
                            'sku' => $req->customize_product[$i],
                            'model_name' => $req->customize_product[$i],
                            'model_desc' => $req->customize_product[$i],
                            'is_active' => true,
                        ]);
                        (new Branch)->assign(Product::class, $customize_prod->id);

                        $product_id = $customize_prod->id;
                    }

                    $prod = Product::where('id', $req->product_id[$i])->first();
                    if ($req->product_order_id != null && $req->product_order_id[$i] != null) {
                        $sp = SaleProduct::where('id', $req->product_order_id[$i])->first();
                        if ($req->sequence[$i] != $sp->sequence) {
                            $sp->sequence = $req->sequence[$i] ?? null;
                            $sp->save();
                        }
                        // If sale product is approved but amount changed, proceed
                        $skip_approved = true;
                        if ($req->selling_price[$i] == null) {
                            if ($req->override_selling_price != null && $req->override_selling_price[$i] != null & $req->override_selling_price[$i] != '' && ($req->override_selling_price[$i] < $prod->min_price || $req->override_selling_price[$i] > $prod->max_price) && $sp->override_selling_price != $req->override_selling_price[$i]) {
                                $skip_approved = false;
                            }
                        }
                        if ($skip_approved) {
                            if ($sp->status == SaleProduct::STATUS_APPROVAL_APPROVED) {
                                continue;
                            }
                        }

                        $sp->update([
                            'product_id' => $product_id,
                            'desc' => $req->product_desc[$i],
                            'qty' => $req->qty[$i],
                            'is_foc' => $req->foc[$i] === 'true',
                            'with_sst' => $req->with_sst[$i] === 'true',
                            'sst_amount' => $req->sst_amount[$i],
                            'sst_value' => $req->with_sst[$i] === 'true' ? Setting::where('key', Setting::SST_KEY)->value('value') : null,
                            'uom' => $req->uom[$i],
                            'unit_price' => $req->foc[$i] === 'true' ? 0 : $req->unit_price[$i],
                            'selling_price_id' => $req->foc[$i] === 'true' ? null : $req->selling_price[$i],
                            'override_selling_price' => $req->foc[$i] === 'true' || $req->override_selling_price == null ? null : $req->override_selling_price[$i],
                            'promotion_id' => $req->promotion_id[$i],
                            'discount' => $req->discount[$i],
                            'remark' => $req->product_remark[$i],
                        ]);
                        SaleProductWarrantyPeriod::where('sale_product_id', $sp->id)->delete();
                    } else {
                        $sp = SaleProduct::create([
                            'sale_id' => $req->sale_id,
                            'product_id' => $product_id,
                            'desc' => $req->product_desc[$i],
                            'qty' => $req->qty[$i],
                            'is_foc' => $req->foc[$i] === 'true',
                            'with_sst' => $req->with_sst[$i] === 'true',
                            'sst_amount' => $req->sst_amount[$i],
                            'sst_value' => $req->with_sst[$i] === 'true' ? Setting::where('key', Setting::SST_KEY)->value('value') : null,
                            'uom' => $req->uom[$i],
                            'unit_price' => $req->foc[$i] === 'true' ? 0 : $req->unit_price[$i],
                            'selling_price_id' => $req->foc[$i] === 'true' ? null : $req->selling_price[$i],
                            'override_selling_price' => $req->foc[$i] === 'true' || $req->override_selling_price == null ? null : $req->override_selling_price[$i],
                            'promotion_id' => $req->promotion_id[$i],
                            'discount' => $req->discount[$i],
                            'remark' => $req->product_remark[$i],
                            'sequence' => $req->sequence[$i] ?? null,
                        ]);
                    }
                    // Warranty Period
                    if ($req->warranty_period != null && count($req->warranty_period) >= ($i + 1) && $req->warranty_period[$i] != null) {
                        $spwp_data = [];
                        for ($j = 0; $j < count($req->warranty_period[$i]); $j++) {
                            if ($req->warranty_period[$i][$j] == null) {
                                continue;
                            }
                            $spwp_data[] = [
                                'sale_product_id' => $sp->id,
                                'warranty_period_id' => $req->warranty_period[$i][$j],
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }
                        if (count($spwp_data) > 0) {
                            SaleProductWarrantyPeriod::insert($spwp_data);
                        }
                    }

                    if (! $is_draft && ! $skip_approval && $req->type != 'cash-sale') {
                        if ($req->selling_price[$i] == null) {
                            if ($req->override_selling_price != null && $req->override_selling_price[$i] != null & $req->override_selling_price[$i] != '' && ($req->override_selling_price[$i] < $prod->min_price || $req->override_selling_price[$i] > $prod->max_price)) {
                                $is_greater = $req->override_selling_price[$i] < $prod->min_price ? false : ($req->override_selling_price[$i] > $prod->max_price ? true : false);

                                $approval = Approval::create([
                                    'object_type' => Sale::class,
                                    'object_id' => $req->sale_id,
                                    'status' => Approval::STATUS_PENDING_APPROVAL,
                                    'data' => $req->type == 'quo' ? json_encode([
                                        'is_quo' => true,
                                        'sale_product_id' => $sp->id,
                                        'description' => 'The override selling price for '.$prod->model_name.'('.$prod->sku.') is out of range, which '.$req->override_selling_price[$i].' is '.($is_greater ? 'greater' : 'lower').' '.($is_greater ? $prod->max_price : $prod->min_price),
                                    ]) : json_encode([
                                        'is_quo' => false,
                                        'sale_product_id' => $sp->id,
                                        'description' => 'The override selling price for '.$prod->model_name.'('.$prod->sku.') is out of range, which '.$req->override_selling_price[$i].' is '.($is_greater ? 'greater' : 'lower').' '.($is_greater ? $prod->max_price : $prod->min_price),
                                    ]),
                                ]);
                                (new Branch)->assign(Approval::class, $approval->id);
                                // Update QUO/SO status
                                Sale::where('id', $req->sale_id)->update([
                                    'status' => Sale::STATUS_APPROVAL_PENDING,
                                ]);
                                if ($sp->status == SaleProduct::STATUS_APPROVAL_REJECTED) {
                                    $sp->revised = true;
                                }
                                $sp->status = SaleProduct::STATUS_APPROVAL_PENDING;
                                $sp->save();
                            } else {
                                $sp->status = null;
                                $sp->save();
                            }
                        } else {
                            if ($sp->status == SaleProduct::STATUS_APPROVAL_REJECTED) {
                                $sp->revised = true;
                            }
                            $sp->status = null;
                            $sp->save();
                        }
                    }
                    // Sale product children
                    SaleProductChild::where('sale_product_id', $sp->id)->whereNotIn('product_children_id', $req->product_serial_no[$i] ?? [])->forceDelete();
                    $existing_spc_ids = SaleProductChild::where('sale_product_id', $sp->id)->pluck('product_children_id')->toArray();

                    if (isset($req->product_serial_no) && $req->product_serial_no[$i] != null) {
                        $data = [];
                        for ($j = 0; $j < count($req->product_serial_no[$i]); $j++) {
                            if (in_array($req->product_serial_no[$i][$j], $existing_spc_ids)) {
                                continue;
                            }

                            $data[] = [
                                'sale_product_id' => $sp->id,
                                'product_children_id' => $req->product_serial_no[$i][$j],
                                'created_at' => $now,
                                'updated_at' => $now,
                            ];
                        }
                        SaleProductChild::insert($data);
                    }
                }
            } else {
                $now = now();

                for ($i = 0; $i < count($req->product_id); $i++) {
                    if ($req->product_order_id != null && $req->product_order_id[$i] != null) {
                        $sp = SaleProduct::where('id', $req->product_order_id[$i])->first();
                        // Sale product children
                        SaleProductChild::where('sale_product_id', $sp->id)->whereNotIn('product_children_id', $req->product_serial_no[$i] ?? [])->forceDelete();
                        $existing_spc_ids = SaleProductChild::where('sale_product_id', $sp->id)->pluck('product_children_id')->toArray();

                        if (isset($req->product_serial_no) && $req->product_serial_no[$i] != null) {
                            $data = [];
                            for ($j = 0; $j < count($req->product_serial_no[$i]); $j++) {
                                if (in_array($req->product_serial_no[$i][$j], $existing_spc_ids)) {
                                    continue;
                                }

                                $data[] = [
                                    'sale_product_id' => $sp->id,
                                    'product_children_id' => $req->product_serial_no[$i][$j],
                                    'created_at' => $now,
                                    'updated_at' => $now,
                                ];
                            }
                            SaleProductChild::insert($data);
                        }
                        // Update product sequence
                        $sp->sequence = $req->sequence[$i] ?? null;
                        $sp->save();
                    }
                }
            }

            $new_prod_ids = SaleProduct::where('sale_id', $req->sale_id)
                ->pluck('id')
                ->toArray();

            DB::commit();

            return Response::json([
                'result' => true,
                'product_ids' => $new_prod_ids,
            ], HttpFoundationResponse::HTTP_OK);
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return Response::json([
                'result' => false,
            ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function upsertRemark(Request $req, bool $validated = false)
    {
        if (! $validated) {
            // Validate form
            $rules = [
                'sale_id' => 'required',
                'remark' => 'nullable|max:250',
            ];
            $req->validate($rules);
        }

        try {
            DB::beginTransaction();

            Sale::where('id', $req->sale_id)->update([
                'remark' => $req->type == 'quo' ? $req->remark : json_encode(explode(',', $req->remark)),
            ]);

            DB::commit();

            return Response::json([
                'result' => true,
            ], HttpFoundationResponse::HTTP_OK);
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return Response::json([
                'result' => false,
            ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function upsertPayDetails(Request $req, bool $validated = false)
    {
        if (! $validated) {
            // Validate form
            $rules = [
                'sale_id' => 'required',
                'payment_term' => 'required',
                'payment_method' => 'required',
                'payment_due_date' => 'required',
                'payment_remark' => 'nullable|max:250',
            ];
            if (in_array(Role::SUPERADMIN, getUserRoleId(Auth::user())) || in_array(Role::FINANCE, getUserRoleId(Auth::user()))) {
                $rules['account_amount'] = 'nullable';
                $rules['account_amount.*'] = 'nullable';
                $rules['account_date'] = 'nullable';
                $rules['account_date.*'] = 'nullable';
                $rules['account_ref_no'] = 'nullable';
                $rules['account_ref_no.*'] = 'nullable|max:250';
            }
            $req->validate($rules);
        }

        try {
            DB::beginTransaction();

            $sale = Sale::where('id', $req->sale_id)->first();

            if ($sale != null) {
                // Check amount is greater than 50% if payment method is deposit required
                $deposit_required = PaymentMethod::where('id', $req->payment_method)->value('deposit_required');

                if ($deposit_required == true && $req->account_amount != null) {
                    $amount_to_pay = $sale->getTotalAmount();
                    $total_amount = 0;
                    for ($i = 0; $i < count($req->account_amount); $i++) {
                        if ($req->account_amount[$i] == null) {
                            continue;
                        }
                        $total_amount += $req->account_amount[$i];
                    }
                    if ($total_amount < ($amount_to_pay / 2)) {
                        return Response::json([
                            'err_msg' => 'Please enter atleast 50% of the total amount, RM'.number_format($sale->getTotalAmount(), 2),
                        ], HttpFoundationResponse::HTTP_BAD_REQUEST);
                    }
                }
                // Payment Amounts
                if ($req->account_amount != null) {
                    SalePaymentAmount::where('sale_id', $sale->id)->delete();

                    $data = [];
                    for ($i = 0; $i < count($req->account_amount); $i++) {
                        if ($req->account_amount[$i] == null) {
                            continue;
                        }
                        $data[] = [
                            'sale_id' => $sale->id,
                            'amount' => $req->account_amount[$i],
                            'date' => $req->account_date[$i],
                            'reference_number' => $req->account_ref_no[$i],
                            'by_id' => Auth::user()->id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                    SalePaymentAmount::insert($data);
                }

                $to_be_paid_amount = $sale->getTotalAmount();
                $paid_amount = $sale->getPaidAmount();

                $status = Sale::PAYMENT_STATUS_UNPAID;
                if ($paid_amount >= $to_be_paid_amount) {
                    $status = Sale::PAYMENT_STATUS_PAID;
                } elseif (isset($data) && count($data) > 0 && $paid_amount < $to_be_paid_amount) {
                    $status = Sale::PAYMENT_STATUS_PARTIALLY_PAID;
                }

                $sale->payment_term = $req->payment_term;
                $sale->payment_method = $req->payment_method;
                $sale->payment_due_date = $req->payment_due_date;
                $sale->payment_status = $status;
                $sale->payment_remark = $req->payment_remark;
                $sale->save();
            }

            DB::commit();

            return Response::json([
                'result' => true,
            ], HttpFoundationResponse::HTTP_OK);
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return Response::json([
                'result' => false,
            ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function upsertDelSchedule(Request $req, bool $validated = false)
    {
        if (! $validated) {
            // Validate form
            $rules = [
                'sale_id' => 'required',
                'driver' => 'required',
                'delivery_date' => 'required',
                'delivery_time' => 'required',
                'delivery_instruction' => 'nullable|max:250',
                'delivery_address' => 'nullable',
                'delivery_status' => 'required',
            ];
            $req->validate($rules);
        }

        try {
            DB::beginTransaction();

            Sale::where('id', $req->sale_id)->update([
                'driver_id' => $req->driver,
                'delivery_date' => $req->delivery_date,
                'delivery_time' => $req->delivery_time,
                'delivery_instruction' => $req->delivery_instruction,
                'delivery_address_id' => $req->delivery_address,
                'delivery_is_active' => $req->boolean('delivery_status'),
            ]);

            DB::commit();

            return Response::json([
                'result' => true,
            ], HttpFoundationResponse::HTTP_OK);
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return Response::json([
                'result' => false,
            ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getProducts(Sale $sale)
    {
        $product_ids = SaleProduct::where('sale_id', $sale->id)->pluck('product_id')->toArray();
        $products = Product::whereIn('id', $product_ids)
            ->where(function ($q) {
                $q->where('type', Product::TYPE_PRODUCT)
                    ->orWhere(function ($q) {
                        $q->where('type', Product::TYPE_RAW_MATERIAL)->where('is_sparepart', true);
                    });
            })
            ->get();

        $sale_product_details = SaleProduct::select('id', 'product_id', 'qty')->with('children')->where('sale_id', $sale->id)->whereIn('product_id', $product_ids)->groupBy('product_id')->get();
        $requested_details = SaleProductionRequest::select('product_id', DB::raw('COUNT(*) as count'))->whereIn('product_id', $product_ids)->groupBy('product_id')->get();

        return Response::json([
            'products' => $products,
            'requested_details' => $requested_details,
            'sale_product_details' => $sale_product_details,
        ], HttpFoundationResponse::HTTP_OK);
    }

    public function toSaleProductionReqeust(Request $req, Sale $sale, Product $product)
    {
        try {
            DB::beginTransaction();

            for ($i = 0; $i < ($req->qty ?? 1); $i++) {
                $spr = SaleProductionRequest::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                ]);
                (new Branch)->assign(SaleProductionRequest::class, $spr->id);
            }

            DB::commit();

            return back()->with('success', 'Sale Production Request is created');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator');
        }
    }

    public function indexDeliveryOrder()
    {
        $page = Session::get('delivery-order-page');

        return view('delivery_order.list', [
            'default_page' => $page ?? null,
        ]);
    }

    public function getDataDeliveryOrder(Request $req)
    {
        $approvals = Approval::where('object_type', DeliveryOrder::class);

        $records = DB::table('delivery_orders')
            ->select(
                'delivery_orders.id AS id',
                'delivery_orders.sku AS doc_no',
                'delivery_orders.created_at AS date',
                'customers.sku AS debtor_code',
                'customers.company_name AS debtor_name',
                'customers.company_group AS company_group',
                'sales_agents.name AS agent',
                'currencies.name AS curr_code',
                'delivery_orders.status AS status',
                'delivery_orders.filename AS filename',
                'created_by.name AS created_by',
                'invoices.sku AS transfer_to',
                'delivery_orders.transport_ack_filename',
                'approvals.status AS approval_status',
            )
            ->where('sales.type', Sale::TYPE_SO)
            ->where('branches.object_type', DeliveryOrder::class)
            ->whereNull('delivery_orders.deleted_at')
            ->leftJoin('sales', DB::raw('FIND_IN_SET(delivery_orders.id, sales.convert_to)'), '>', DB::raw("'0'"))
            ->leftJoin('sale_products', 'sale_products.sale_id', '=', 'sales.id')
            ->leftJoin('customers', 'customers.id', '=', 'sales.customer_id')
            ->leftJoin('currencies', 'customers.currency_id', '=', 'currencies.id')
            ->leftJoin('sales_agents', 'sales_agents.id', '=', 'sales.sale_id')
            ->leftJoin('users AS created_by', 'created_by.id', '=', 'delivery_orders.created_by')
            ->leftJoin('invoices', 'invoices.id', '=', 'delivery_orders.invoice_id')
            ->leftJoin('branches', 'branches.object_id', '=', 'delivery_orders.id')
            ->leftJoinSub($approvals, 'approvals', function (JoinClause $join) {
                $join->on('approvals.object_id', '=', 'delivery_orders.id');
            });

        if (getCurrentUserBranch() != null) {
            $records = $records->where('branches.location', getCurrentUserBranch());
        }

        if ($req->has('sku')) {
            $records = $records->where('delivery_orders.sku', $req->sku);
        }
        $records = $records->groupBy('delivery_orders.id');

        Session::put('delivery-order-page', $req->page);
        // Search
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];

            $so_ids = Sale::where('type', Sale::TYPE_SO)->where('sku', 'like', '%'.$keyword.'%')->pluck('id')->toArray();

            $records = $records->where(function ($q) use ($keyword, $so_ids) {
                $q->where('delivery_orders.sku', 'like', '%'.$keyword.'%')
                    ->orWhere('delivery_orders.created_at', 'like', '%'.$keyword.'%')
                    ->orWhere('customers.sku', 'like', '%'.$keyword.'%')
                    ->orWhere('customers.company_name', 'like', '%'.$keyword.'%')
                    ->orWhere('users.name', 'like', '%'.$keyword.'%')
                    ->orWhere('created_by.name', 'like', '%'.$keyword.'%')
                    ->orWhere('currencies.name', 'like', '%'.$keyword.'%')
                    ->orWhere('invoices.sku', 'like', '%'.$keyword.'%')
                    ->orWhereIn('sales.id', $so_ids);
            });
        }
        // Order
        if ($req->has('order')) {
            $map = [
                0 => 'delivery_orders.sku',
                1 => 'delivery_orders.created_at',
                4 => 'customers.sku',
                5 => 'customers.company_name',
                6 => 'sales_agents.name',
                9 => 'created_by.name',
                10 => 'delivery_orders.status',
            ];
            foreach ($req->order as $order) {
                $records = $records->orderBy($map[$order['column']], $order['dir']);
            }
        } else {
            $records = $records->orderBy('delivery_orders.id', 'desc');
        }

        $records_paginator = $records->simplePaginate(10);

        $data = [
            'data' => [],
        ];
        $path = '/public/storage';
        if (config('app.env') == 'local') {
            $path = '/storage';
        }

        foreach ($records_paginator as $record) {
            if (! $req->has('sku') && $record->approval_status === 0) {
                continue;
            }
            $sos = Sale::where('type', Sale::TYPE_SO)->whereRaw("find_in_set('".$record->id."', convert_to)")->get();
            $so_skus = [];

            for ($i = 0; $i < count($sos); $i++) {
                $so_skus[] = $sos[$i]->sku;
            }

            $filename = config('app.url').str_replace('public', $path, self::DELIVERY_ORDER_PATH).$record->filename;
            $transport_ack_filename = $record->transport_ack_filename == null ? null : config('app.url').str_replace('public', $path, self::TRANSPORT_ACKNOWLEDGEMENT_PATH).$record->transport_ack_filename;

            $total_amount = 0;
            $dop_ids = DeliveryOrderProduct::withTrashed()->where('delivery_order_id', $record->id)->pluck('id')->toArray();
            $dopcs = DeliveryOrderProductChild::withTrashed()->whereIn('delivery_order_product_id', $dop_ids)->get();
            for ($i = 0; $i < count($dopcs); $i++) {
                $sp = SaleProduct::where('id', $dopcs[$i]->doProduct()->withTrashed()->value('sale_product_id'))->first();
                $unit_price = $sp->override_selling_price ?? $sp->unit_price;
                $discount = ($sp->discount / $sp->qty);
                $total_amount += $unit_price - $discount;
            }

            $data['data'][] = [
                'id' => $record->id,
                'doc_no' => $record->doc_no,
                'date' => Carbon::parse($record->date)->format('d M Y'),
                'transfer_from' => implode(', ', $so_skus),
                'transfer_to' => $record->transfer_to ?? null,
                'debtor_code' => $record->debtor_code,
                'debtor_name' => $record->debtor_name,
                'debtor_company_group' => $record->company_group,
                'agent' => $record->agent,
                'curr_code' => $record->curr_code ?? null,
                'total' => number_format($total_amount, 2),
                'created_by' => $record->created_by ?? null,
                'status' => $record->status,
                'filename' => $filename,
                'transport_ack_filename' => $transport_ack_filename,
            ];
        }

        $records_count = count($data['data']);
        $records_ids = count($data['data']);

        $data['recordsTotal'] = $records_count;
        $data['recordsFiltered'] = $records_count;
        $data['records_ids'] = $records_ids;

        return response()->json($data);
    }

    public function toInvoice(Request $req)
    {
        $step = 1;

        if ($req->has('term')) {
            $step = 3;

            Session::put('convert_terms', $req->term);

            $delivery_orders = DeliveryOrder::whereNull('invoice_id')
                ->whereNull('status')
                ->where('customer_id', Session::get('convert_customer_id'))
                ->where('payment_terms', $req->term)
                ->get();
        } elseif ($req->has('cus')) {
            $step = 2;

            Session::put('convert_customer_id', $req->cus);

            $term_ids = DeliveryOrder::where('customer_id', $req->cus)
                ->whereNull('status')
                ->whereNotNull('payment_terms')
                ->distinct()
                ->pluck('payment_terms');

            $terms = CreditTerm::whereIn('id', $term_ids)->get();
        } else {
            $customer_ids = DeliveryOrder::distinct()->pluck('customer_id');

            $customers = Customer::whereIn('id', $customer_ids)->get();
        }

        return view('delivery_order.convert_to_invoice', [
            'step' => $step,
            'terms' => $terms ?? [],
            'customers' => $customers ?? [],
            'delivery_orders' => $delivery_orders ?? [],
        ]);
    }

    private function convertToInvoice(array $do_ids, int $customer_id, ?int $term_id)
    {
        $dos = DeliveryOrder::withoutGlobalScope(ApprovedScope::class)->whereIn('id', $do_ids)->get();
        try {
            DB::beginTransaction();

            $is_hi_ten = isHiTen(Customer::where('id', $customer_id)->value('company_group'));

            // Create record
            $existing_skus = Invoice::withoutGlobalScope(BranchScope::class)->pluck('sku')->toArray();
            $sku = generateSku('I', $existing_skus, $is_hi_ten);
            $filename = str_replace('/', '-', $sku).'.pdf';

            $do_sku = DeliveryOrder::withoutGlobalScope(ApprovedScope::class)->whereIn('id', $do_ids)->pluck('sku')->toArray();

            $inv = Invoice::create([
                'sku' => $sku,
                'filename' => $filename,
                'date' => now(),
                'created_by' => Auth::user()->id,
                'company' => $is_hi_ten ? 'hi_ten' : 'powercool',
            ]);
            (new Branch)->assign(Invoice::class, $inv->id);

            // Create PDF
            $pdf_products = [];
            $overall_total = 0;
            $dos = DeliveryOrder::withoutGlobalScope(ApprovedScope::class)->whereIn('id', $do_ids)->get();

            for ($k = 0; $k < count($dos); $k++) {
                for ($i = 0; $i < count($dos[$k]->products); $i++) {
                    if ($dos[$k]->products[$i]->saleProduct->product->isRawMaterial()) {
                        $subtotal = ($dos[$k]->products[$i]->saleProduct->override_selling_price ?? ($dos[$k]->products[$i]->saleProduct->qty * $dos[$k]->products[$i]->saleProduct->unit_price)) - $dos[$k]->products[$i]->saleProduct->discountAmount();
                        $pdf_products[] = [
                            'stock_code' => $dos[$k]->products[$i]->saleProduct->product->sku,
                            'model_name' => $dos[$k]->products[$i]->saleProduct->product->model_name,
                            'qty' => $dos[$k]->products[$i]->qty,
                            'uom' => UOM::where('id', $dos[$k]->products[$i]->saleProduct->product->uom)->value('name'),
                            'unit_price' => number_format($dos[$k]->products[$i]->saleProduct->unit_price, 2),
                            'discount' => number_format($dos[$k]->products[$i]->saleProduct->discount, 2),
                            'promotion' => number_format($dos[$k]->products[$i]->saleProduct->promotionAmount(), 2),
                            'total_discount' => $dos[$k]->products[$i]->saleProduct->discountAmount(),
                            'total' => number_format($subtotal, 2),
                        ];
                        $overall_total += $subtotal;
                    } else {
                        $dopcs = $dos[$k]->products[$i]->children;

                        $serial_no = [];
                        for ($j = 0; $j < count($dopcs); $j++) {
                            $serial_no[] = [
                                'sku' => ProductChild::where('id', $dopcs[$j]->product_children_id)->value('sku'),
                                'remark' => $dopcs[$j]->remark,
                            ];

                            if ($j + 1 == count($dopcs)) {
                                $unit_price = $dopcs[$j]->doProduct->saleProduct->override_selling_price ?? $dopcs[$j]->doProduct->saleProduct->unit_price;
                                $discount = count($serial_no) * ($dopcs[$j]->doProduct->saleProduct->discount / $dopcs[$j]->doProduct->saleProduct->qty);
                                $total = (count($serial_no) * $unit_price) - $discount;
                                $pdf_products[] = [
                                    'stock_code' => $dopcs[$j]->productChild->parent->sku,
                                    'model_name' => $dopcs[$j]->productChild->parent->model_name,
                                    'qty' => count($serial_no),
                                    'uom' => UOM::where('id', $dopcs[$j]->productChild->parent->uom)->value('name'),
                                    'unit_price' => number_format($unit_price, 2),
                                    'discount' => number_format($discount, 2),
                                    'promotion' => number_format($dopcs[$j]->doProduct->saleProduct->promotionAmount(), 2),
                                    'total_discount' => $dopcs[$j]->doProduct->saleProduct->discountAmount(),
                                    'total' => number_format($total, 2),
                                    'warranty_periods' => $dopcs[$j]->doProduct->saleProduct->warrantyPeriods,
                                    'serial_no' => $serial_no,
                                ];
                                $overall_total += $total;
                            }
                        }

                    }
                }
            }

            $pc_inputs = Session::get('convert_product_children');
            $sp_ids = [];
            $sale_orders = collect();
            foreach ($pc_inputs as $sp_id => $ipt) {
                $sp_ids[] = $sp_id;
            }
            foreach (SaleProduct::whereIn('id', $sp_ids)->cursor() as $sp) {
                $sale_orders->push($sp->sale);
            }
            $pdf = Pdf::loadView('delivery_order.'.($is_hi_ten ? 'hi_ten' : 'powercool').'_inv_pdf', [
                'date' => now()->format('d/m/Y'),
                'sku' => $sku,
                'do_sku' => implode(', ', $do_sku),
                'dos' => $dos,
                'products' => $pdf_products,
                'customer' => Customer::where('id', $customer_id)->first(),
                'billing_address' => (new CustomerLocation)->defaultBillingAddress($customer_id),
                'terms' => $term_id,
                'overall_total' => $overall_total,
                'sale_orders' => $sale_orders,
                'warehouse' => $sale_orders[0]->warehouse ?? '',
                'store' => $sale_orders[0]->store ?? '',
                'salesperson' => SalesAgent::where('id', Session::get('convert_salesperson_id'))->first(),
            ]);
            $pdf->setPaper('A4', 'letter');
            $content = $pdf->download()->getOriginalContent();
            Storage::put(self::INVOICE_PATH.$filename, $content);

            DeliveryOrder::withoutGlobalScope(ApprovedScope::class)->whereIn('id', $do_ids)->update([
                'invoice_id' => $inv->id,
                'status' => DeliveryOrder::STATUS_CONVERTED,
            ]);

            // Create service reminder for 6 months & remind 3 days before
            $data = [];
            $now = now();
            foreach (DeliveryOrderProductChild::whereIn('delivery_order_product_id', DeliveryOrderProduct::whereIn('delivery_order_id', $do_ids)->pluck('id'))->cursor() as $dopc) {
                $data[] = [
                    'object_type' => ProductChild::class,
                    'object_id' => $dopc->id,
                    'attached_type' => Invoice::class,
                    'attached_id' => $inv->id,
                    'next_service_date' => Carbon::parse($now)->addMonths(6),
                    'reminding_days' => 3,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            InventoryServiceReminder::insert($data);

            DB::commit();

            return true;

            // return redirect(route('invoice.index'))->with('success', 'Delivery Order has converted');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return false;
            // return back()->with('error', 'Something went wrong. Please contact administrator');
        }
    }

    public function cancelDeliveryOrder(Request $req)
    {
        try {
            DB::beginTransaction();

            // Prepare data
            $involved = json_decode($req->involved, true);
            $do_to_cancel = [];
            $so_to_cancel = [];

            foreach ($involved as $key => $value) {
                $do_to_cancel[] = $key;
                $so_to_cancel = array_merge($so_to_cancel, $value);
            }
            $so_to_cancel = array_unique($so_to_cancel);

            // Cancellation
            $sales = Sale::where('type', Sale::TYPE_SO)->whereIn('sku', $so_to_cancel)->get();

            for ($i = 0; $i < count($sales); $i++) {
                $this->cancelSaleOrderFlow($sales[$i], true, null, $do_to_cancel);
            }
            DeliveryOrder::whereIn('sku', $do_to_cancel)->update([
                'status' => DeliveryOrder::STATUS_VOIDED,
            ]);

            DB::commit();

            return back()->with('success', 'Delivery Order cancelled');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator');
        }
    }

    public function cancelSaleOrderFlow(Sale $sale, bool $cancel_from_converted, ?float $charge = null, ?array $do_skus = null)
    {
        dd($sale);
        SaleOrderCancellation::calCancellation($sale, $cancel_from_converted ? 3 : 1, null);

        $sp_ids = SaleProduct::where('sale_id', $sale->id)->pluck('id');
        // Delete sp/spc
        SaleProductChild::whereIn('sale_product_id', $sp_ids)->delete();
        SaleProduct::whereIn('id', $sp_ids)->delete();
        // Delete dop/dopc
        if ($do_skus != null) {
            $do_ids = DeliveryOrder::whereIn('sku', $do_skus)->pluck('id');
            $dop_ids = DeliveryOrderProduct::whereIn('delivery_order_id', $do_ids)->pluck('id');
            DeliveryOrderProductChild::whereIn('delivery_order_product_id', $dop_ids)->delete();
            DeliveryOrderProduct::whereIn('id', $dop_ids)->delete();
        }

        $sale->cancellation_charge = $charge;
        $sale->status = Sale::STATUS_CANCELLED;
        $sale->save();
    }

    public function getCancellationInvolvedSO(Request $req, Sale $so)
    {
        $involved_quo_skus = Sale::where('type', Sale::TYPE_QUO)->where('convert_to', $so->id)->pluck('sku')->toArray();
        $data = [
            'involved' => [
                $so->sku => $involved_quo_skus,
            ],
            'involved_so_skus' => $so->sku,
            'involved_quo_skus' => $involved_quo_skus,
        ];

        return Response::json($data, HttpFoundationResponse::HTTP_OK);
    }

    public function getCancellationInvolvedDO(Request $req, DeliveryOrder $do)
    {
        $data = $this->getCancellationInvolved($do->invoice_id);

        return Response::json($data, HttpFoundationResponse::HTTP_OK);
    }

    private function getCancellationInvolvedDOFlow(int $do_id)
    {
        $so_skus = [];
        $do_ids = [];

        $sales = Sale::where('type', Sale::TYPE_SO)
            ->whereRaw("find_in_set('".$do_id."', convert_to)")
            ->get();

        for ($i = 0; $i < count($sales); $i++) {
            $sale_do_ids = null;
            if (str_contains($sales[$i]->convert_to, ',')) {
                $sale_do_ids = explode(',', $sales[$i]->convert_to);
            } else {
                $sale_do_ids = [$sales[$i]->convert_to];
            }

            $so_skus[] = $sales[$i]->sku;
            $do_ids = array_merge($do_ids, $sale_do_ids);
        }

        return [
            'so_skus' => $so_skus,
            'do_ids' => array_unique($do_ids),
        ];
    }

    public function indexInvoice()
    {
        $page = Session::get('invoice-page');

        return view('invoice.list', [
            'default_page' => $page ?? null,
        ]);
    }

    public function getDataInvoice(Request $req)
    {
        $records = DB::table('invoices')
            ->select(
                'invoices.id AS id',
                'invoices.sku AS doc_no',
                'invoices.date AS date',
                'customers.sku AS debtor_code',
                'customers.company_name AS debtor_name',
                'sales_agents.name AS agent',
                'currencies.name AS curr_code',
                'invoices.status AS status',
                'invoices.filename AS filename',
                'created_by.name AS created_by',
                'delivery_orders.status AS sos',
            )
            ->where('sales.type', Sale::TYPE_SO)
            ->whereNull('invoices.deleted_at')
            ->whereNot('delivery_orders.status', DeliveryOrder::STATUS_APPROVAL_PENDING)
            ->leftJoin('delivery_orders', 'invoices.id', '=', 'delivery_orders.invoice_id')
            ->leftJoin('sales', DB::raw('FIND_IN_SET(delivery_orders.id, sales.convert_to)'), '>', DB::raw("'0'"))
            ->leftJoin('customers', 'customers.id', '=', 'sales.customer_id')
            ->leftJoin('currencies', 'customers.currency_id', '=', 'currencies.id')
            ->leftJoin('sales_agents', 'sales_agents.id', '=', 'sales.sale_id')
            ->leftJoin('users AS created_by', 'created_by.id', '=', 'delivery_orders.created_by')
            ->groupBy('invoices.id');

        if ($req->has('is_return')) {
            Session::put('invoice-return-page', $req->page);
        } else {
            Session::put('invoice-page', $req->page);
        }
        // Search
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];

            $do_ids = DeliveryOrder::where('sku', 'like', '%'.$keyword.'%')->pluck('id')->toArray();

            $records = $records->where(function ($q) use ($keyword, $do_ids) {
                $q->where('invoices.sku', 'like', '%'.$keyword.'%')
                    ->orWhere('invoices.date', 'like', '%'.$keyword.'%')
                    ->orWhere('customers.sku', 'like', '%'.$keyword.'%')
                    ->orWhere('customers.company_name', 'like', '%'.$keyword.'%')
                    ->orWhere('users.name', 'like', '%'.$keyword.'%')
                    ->orWhere('created_by.name', 'like', '%'.$keyword.'%')
                    ->orWhere('currencies.name', 'like', '%'.$keyword.'%')
                    ->orWhere('invoices.company', 'like', '%'.$keyword.'%')
                    ->orWhereIn('delivery_orders.id', $do_ids);
            });
        }
        // Order
        if ($req->has('order')) {

            if ($req->has('is_return')) {
                $map = [
                    0 => 'invoices.sku',
                    1 => 'invoices.date',
                    3 => 'customers.sku',
                    4 => 'customers.company_name',
                    5 => 'sales_agents.name',
                    9 => 'invoices.status',
                ];
            } else {
                $map = [
                    1 => 'invoices.sku',
                    2 => 'invoices.date',
                    4 => 'customers.sku',
                    5 => 'customers.company_name',
                    6 => 'sales_agents.name',
                    10 => 'invoices.status',
                ];
            }
            foreach ($req->order as $order) {
                $records = $records->orderBy($map[$order['column']], $order['dir']);
            }
        } else {
            $records = $records->orderBy('invoices.id', 'desc');
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
        $path = '/public/storage';
        if (config('app.env') == 'local') {
            $path = '/storage';
        }
        foreach ($records_paginator as $record) {
            $dos = DeliveryOrder::where('invoice_id', $record->id)->get();
            $do_skus = [];

            for ($i = 0; $i < count($dos); $i++) {
                $do_skus[] = $dos[$i]->sku;
            }
            // Prepare pdf url
            $pdf_url = $record->filename == null ? null : config('app.url').str_replace('public', $path, self::INVOICE_PATH).'/'.$record->filename;
            // Total amount
            $total_amount = 0;
            if (count($dos) > 0) {
                $dop_ids = DeliveryOrderProduct::withTrashed()->where('delivery_order_id', $dos[0]->id)->pluck('id')->toArray();
                $dopcs = DeliveryOrderProductChild::withTrashed()->whereIn('delivery_order_product_id', $dop_ids)->get();
                for ($i = 0; $i < count($dopcs); $i++) {
                    $sp = SaleProduct::where('id', $dopcs[$i]->doProduct()->withTrashed()->value('sale_product_id'))->first();
                    $unit_price = $sp->override_selling_price ?? $sp->unit_price;
                    $discount = ($sp->discount / $sp->qty);
                    $total_amount += $unit_price - $discount;
                }
            }

            $data['data'][] = [
                'id' => $record->id,
                'doc_no' => $record->doc_no,
                'date' => Carbon::parse($record->date)->format('d M Y'),
                'debtor_code' => $record->debtor_code,
                'transfer_from' => implode(', ', $do_skus),
                'debtor_name' => $record->debtor_name,
                'agent' => $record->agent ?? null,
                'curr_code' => $record->curr_code ?? null,
                'total' => number_format($total_amount, 2),
                'created_by' => $record->created_by ?? null,
                'status' => $record->status,
                'filename' => $record->filename,
                'pdf_url' => $pdf_url,
            ];
        }

        return response()->json($data);
    }

    public function indexDraftEInvoice()
    {
        $page = Session::get('invoice-draft-e-invoice-page');

        return view('invoice.draft-e-invoice.list', [
            'default_page' => $page ?? null,
        ]);
    }

    public function getDataDraftEInvoice(Request $req)
    {
        Session::put('invoice-draft-e-invoice-page', $req->page);

        $records = DB::table('draft_e_invoices')
            ->select(
                'draft_e_invoices.id AS id',
                'invoices.id AS invoice_id',
                'invoices.sku AS transfer_from',
                'invoices.company AS company',
                'invoices.date AS date',
                'customers.sku AS debtor_code',
                'customers.company_name AS debtor_name',
                'customers.tin_number AS tin_number',
                'sales_agents.name AS agent',
                'currencies.name AS curr_code',
                'draft_e_invoices.status AS status',
                'invoices.filename AS filename',
                'created_by.name AS created_by',
            )
            ->where('sales.type', Sale::TYPE_SO)
            ->whereNull('invoices.deleted_at')
            ->whereNot('delivery_orders.status', DeliveryOrder::STATUS_APPROVAL_PENDING)
            ->leftJoin('invoices', 'draft_e_invoices.invoice_id', '=', 'invoices.id')
            ->leftJoin('delivery_orders', 'invoices.id', '=', 'delivery_orders.invoice_id')
            ->leftJoin('sales', DB::raw('FIND_IN_SET(delivery_orders.id, sales.convert_to)'), '>', DB::raw("'0'"))
            ->leftJoin('customers', 'customers.id', '=', 'sales.customer_id')
            ->leftJoin('currencies', 'customers.currency_id', '=', 'currencies.id')
            ->leftJoin('sales_agents', 'sales_agents.id', '=', 'sales.sale_id')
            ->leftJoin('users AS created_by', 'created_by.id', '=', 'delivery_orders.created_by')
            ->groupBy('draft_e_invoices.id');

        // Search
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];

            $do_ids = DeliveryOrder::where('sku', 'like', '%'.$keyword.'%')->pluck('id')->toArray();

            $records = $records->where(function ($q) use ($keyword, $do_ids) {
                $q->where('invoices.sku', 'like', '%'.$keyword.'%')
                    ->orWhere('invoices.date', 'like', '%'.$keyword.'%')
                    ->orWhere('customers.sku', 'like', '%'.$keyword.'%')
                    ->orWhere('customers.company_name', 'like', '%'.$keyword.'%')
                    ->orWhere('users.name', 'like', '%'.$keyword.'%')
                    ->orWhere('created_by.name', 'like', '%'.$keyword.'%')
                    ->orWhere('currencies.name', 'like', '%'.$keyword.'%')
                    ->orWhere('invoices.company', 'like', '%'.$keyword.'%')
                    ->orWhereIn('delivery_orders.id', $do_ids);
            });
        }
        // Order
        if ($req->has('order')) {
            $map = [
                1 => 'invoices.date',
                3 => 'customers.sku',
                4 => 'customers.company_name',
                5 => 'sales_agents.name',
                9 => 'invoices.status',
            ];
            foreach ($req->order as $order) {
                $records = $records->orderBy($map[$order['column']], $order['dir']);
            }
        } else {
            $records = $records->orderBy('draft_e_invoices.id', 'desc');
        }

        $records_count = $records->count();
        $records_ids = $records->pluck('draft_e_invoices.id');
        $records_paginator = $records->simplePaginate(10);

        $data = [
            'recordsTotal' => $records_count,
            'recordsFiltered' => $records_count,
            'data' => [],
            'records_ids' => $records_ids,
        ];
        $path = '/public/storage';
        if (config('app.env') == 'local') {
            $path = '/storage';
        }
        foreach ($records_paginator as $record) {
            $dos = DeliveryOrder::where('invoice_id', $record->invoice_id)->get();
            $total_amount = 0;

            for ($i = 0; $i < count($dos); $i++) {
                $sos = Sale::where('type', Sale::TYPE_SO)->whereRaw("find_in_set('".$dos[$i]->id."', convert_to)")->get();
                for ($j = 0; $j < count($sos); $j++) {
                    $total_amount += $sos[$j]->getTotalAmount();
                }
            }
            $pdf_url = $record->filename == null ? null : config('app.url').str_replace('public', $path, self::INVOICE_PATH).$record->filename;

            $data['data'][] = [
                'id' => $record->id,
                'date' => Carbon::parse($record->date)->format('d M Y'),
                'debtor_code' => $record->debtor_code,
                'transfer_from' => $record->transfer_from,
                'debtor_name' => $record->debtor_name,
                'company' => $record->company ?? null,
                'agent' => $record->agent ?? null,
                'curr_code' => $record->curr_code ?? null,
                'total' => number_format($total_amount, 2),
                'created_by' => $record->created_by ?? null,
                'status' => $record->status,
                'tin_number' => $record->tin_number,
                'invoice_id' => $record->invoice_id,
                'pdf_url' => $pdf_url,
            ];
        }

        return response()->json($data);
    }

    public function rejectDraftEInvoice(DraftEInvoice $draft)
    {
        try {
            DB::beginTransaction();

            $draft->status = DraftEInvoice::STATUS_REJECTED;
            $draft->save();

            Invoice::where('id', $draft->invoice_id)->update([
                'status' => Invoice::STATUS_REJECTED,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Document rejected',
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return response()->json([
                'error' => 'Failed to approve',
                'message' => $th->getMessage(),
            ]);
        }
    }

    public function approveDraftEInvoice(Request $request)
    {
        try {
            DB::beginTransaction();

            $draftIds = collect($request->input('invoices', []))
                ->pluck('id')
                ->filter()
                ->all();

            if (empty($draftIds)) {
                return response()->json([
                    'error' => 'No draft IDs provided',
                ], 422);
            }

            $drafts = DraftEInvoice::whereIn('id', $draftIds)->get();

            if ($drafts->isEmpty()) {
                return response()->json([
                    'error' => 'No drafts found for provided IDs',
                ], 404);
            }

            $company = $request->input('company');

            $invoicesData = [];
            foreach ($drafts as $draft) {
                $invoice = Invoice::find($draft->invoice_id);
                $delivery = DeliveryOrder::where('invoice_id', $draft->invoice_id)->first();
                $customer = Customer::find($delivery->customer_id);

                if (!$customer || !$customer->tin_number) {
                    $invoice?->update([
                        'status' => Invoice::STATUS_APPROVED,
                    ]);
                    $draft->status = DraftEInvoice::STATUS_APPROVED;
                    $draft->save();
                } else {
                    $invoicesData[] = ['id' => $draft->invoice_id];
                }
            }

            if (!empty($invoicesData)) {
                $req = new Request([
                    'invoices' => $invoicesData,
                    'company' => $company,
                ]);

                $res = (new EInvoiceController)->submit($req);
                $data = $res->getData(true);

                if (!empty($data['errorDetails'])) {
                    DB::rollBack();
                    return $res;
                } elseif ($res->getStatusCode() > 299) {
                    DB::rollBack();
                    return $res;
                }

                foreach ($invoicesData as $invData) {
                    $draft = DraftEInvoice::where('invoice_id', $invData['id'])->first();
                    if ($draft) {
                        $draft->status = DraftEInvoice::STATUS_VALID;
                        $draft->save();
                    }

                    Invoice::where('id', $invData['id'])->update([
                        'status' => Invoice::STATUS_APPROVED,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Documents approved',
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return response()->json([
                'error' => 'Failed to approve',
                'message' => $th->getMessage(),
            ], 500);
        }
    }



    public function indexEInvoice()
    {
        return view('invoice.e-invoice.list');
    }

    public function getDataEInvoice(Request $req)
    {
        $records = new EInvoice;

        // Search
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];

            $records = $records->where(function ($q) use ($keyword) {
                $q->where('uuid', 'like', '%'.$keyword.'%')
                    ->orWhere('status', 'like', '%'.$keyword.'%');
            });
        }
        // Order
        if ($req->has('order')) {
            $map = [
                0 => 'uuid',
                1 => 'status',
                2 => 'submission_date',
                3 => 'from',
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
            if( $record->einvoiceable instanceof Invoice){
                $invoice = $record->einvoiceable;
                $delivery = DeliveryOrder::where('invoice_id', $invoice->id)->first();
                $deliveryProduct = $delivery?->products()->first();
                $saleProduct = $deliveryProduct?->saleProduct;
                $sale = $saleProduct?->sale;
                $customer = $sale?->customer;
            }

            $dos = DeliveryOrder::where('invoice_id', $invoice->id)->get();
            $total_amount = 0;

            for ($i = 0; $i < count($dos); $i++) {
                $sos = Sale::where('type', Sale::TYPE_SO)->whereRaw("find_in_set('".$dos[$i]->id."', convert_to)")->get();
                for ($j = 0; $j < count($sos); $j++) {
                    $total_amount += $sos[$j]->getTotalAmount();
                }
            }

            $data['data'][] = [
                'uuid' => $record->uuid,
                'status' => $record->status,
                'submission_date' => $record->submission_date,
                'validation_link' => (new EInvoiceController)->generateValidationLink($record->uuid, $record->longId),
                'id' => $record->id,
                'from' => $record->einvoiceable instanceof Invoice ? 'Customer' : 'Billing',
                'debtor_name' => $customer?->company_name,
                'total' => $total_amount,
                'invoice_date'=>$invoice->date
            ];
        }

        return response()->json($data);
    }

    public function indexConsolidatedEInvoice()
    {
        return view('invoice.consolidated-e-invoice.list');
    }

    public function getDataConsolidatedEInvoice(Request $req)
    {
        $records = ConsolidatedEInvoice::with(['invoices']);

        // Search
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];

            $records = $records->where(function ($q) use ($keyword) {
                $q->where('uuid', 'like', '%'.$keyword.'%')
                    ->orWhere('status', 'like', '%'.$keyword.'%');
            });
        }

        // Order
        if ($req->has('order')) {
            $map = [
                0 => 'uuid',
                1 => 'status',
            ];
            foreach ($req->order as $order) {
                $column = $map[$order['column']];
                $records = $records->whereHas('invoices', function ($q) use ($column, $order) {
                    $q->orderBy($column, $order['dir']);
                });
            }
        } else {
            $records = $records->orderBy('id', 'desc');
        }

        $records_count = $records->count();
        $records_paginator = $records->simplePaginate(10);

        $data = [
            'recordsTotal' => $records_count,
            'recordsFiltered' => $records_count,
            'data' => [],
        ];

        foreach ($records_paginator as $consolidated) {
            $data['data'][] = [
                'uuid' => $consolidated->uuid,
                'status' => $consolidated->status,
            ];
        }

        return response()->json($data);
    }

    public function indexCreditNote()
    {
        return view('invoice.credit-note.list');
    }

    public function getDataCreditNote(Request $req)
    {
        $records = new CreditNote;

        // Search
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];

            $records = $records->where(function ($q) use ($keyword) {
                $q->where('uuid', 'like', '%'.$keyword.'%')
                    ->orWhere('status', 'like', '%'.$keyword.'%');
            });
        }
        // Order
        if ($req->has('order')) {
            $map = [
                0 => 'uuid',
                1 => 'status',
                2 => 'from',
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
                'uuid' => $record->uuid,
                'from' => $record->einvoices->count() > 0 ? 'E-Invoice' : 'Consolidated E-Invoice',
                'status' => $record->status,
                'date' => $record->created_at->format('Y-m-d H:i:s'),
            ];
        }

        return response()->json($data);
    }

    public function indexDebitNote()
    {
        return view('invoice.debit-note.list');
    }

    public function getDataDebitNote(Request $req)
    {
        $records = new DebitNote;

        // Search
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];

            $records = $records->where(function ($q) use ($keyword) {
                $q->where('uuid', 'like', '%'.$keyword.'%')
                    ->orWhere('status', 'like', '%'.$keyword.'%');
            });
        }
        // Order
        if ($req->has('order')) {
            $map = [
                0 => 'uuid',
                1 => 'status',
                2 => 'from',
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
                'uuid' => $record->uuid,
                'from' => $record->einvoices->count() > 0 ? 'E-Invoice' : 'Consolidated E-Invoice',
                'status' => $record->status,
                'date' => $record->created_at->format('Y-m-d H:i:s'),
            ];
        }

        return response()->json($data);
    }

    public function cancelInvoice(Request $req)
    {
        try {
            DB::beginTransaction();

            // Prepare data
            $inv_skus = json_decode($req->involved_inv_skus, true);
            $do_skus = array_unique(json_decode($req->involved_do_skus, true));
            $so_skus = array_unique(json_decode($req->involved_so_skus, true));

            $res = $this->cancelInvDO($req->type, $so_skus, $do_skus, $inv_skus);
            if ($res == false) {
                throw new Exception('cancelInvDO Failed');
            }

            $res_msg = null;
            if (str_contains(Route::currentRouteName(), 'invoice.')) {
                $res_msg = 'Invoice ';
            } elseif (str_contains(Route::currentRouteName(), 'delivery_order.')) {
                $res_msg = 'Delivery Order ';
            }
            if ($req->type == 'void') {
                $res_msg = $res_msg.' voided';
            } elseif ($req->type == 'transfer-back') {
                $res_msg = $res_msg.' transferred';
            }

            DB::commit();

            return back()->with('success', $res_msg);
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator');
        }
    }

    private function cancelInvDO(string $type, array $so_skus, array $do_skus, array $inv_skus)
    {
        try {
            DB::beginTransaction();

            $sales = Sale::where('type', Sale::TYPE_SO)->whereIn('sku', $so_skus)->get();

            $do_ids = DeliveryOrder::whereIn('sku', $do_skus)->pluck('id')->toArray();

            for ($i = 0; $i < count($sales); $i++) {
                $convert_to = explode(',', $sales[$i]->convert_to);
                $new_convert_to = [];

                for ($j = 0; $j < count($convert_to); $j++) {
                    if (! in_array($convert_to[$j], $do_ids)) {
                        $new_convert_to[] = $convert_to[$j];
                    }
                }

                if ($type == 'transfer-back') {
                    $sales[$i]->convert_to = count($new_convert_to) == 0 ? null : implode(',', $new_convert_to);
                }
                $sales[$i]->status = Sale::STATUS_ACTIVE;
                $sales[$i]->save();
            }
            $dop_ids = DeliveryOrderProduct::whereIn('delivery_order_id', $do_ids)->pluck('id');
            DeliveryOrderProductChild::whereIn('delivery_order_product_id', $dop_ids)->delete();
            DeliveryOrderProduct::whereIn('id', $dop_ids)->delete();

            if ($type == 'void') {
                DeliveryOrder::whereIn('sku', $do_skus)->update([
                    'status' => DeliveryOrder::STATUS_VOIDED,
                ]);
                Invoice::whereIn('sku', $inv_skus)->update([
                    'status' => Invoice::STATUS_VOIDED,
                ]);
            } elseif ($type == 'transfer-back') {
                DeliveryOrder::whereIn('sku', $do_skus)->delete();
                Invoice::whereIn('sku', $inv_skus)->delete();

                // Delete service reminder
                InventoryServiceReminder::where('attached_type', Invoice::class)
                    ->whereIn('attached_id', Invoice::whereIn('sku', $inv_skus)->pluck('id')->toArray())
                    ->delete();
            }

            DB::commit();

            return true;
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return false;
        }
    }

    public function getCancellationInvolvedInv(Request $req, Invoice $inv)
    {
        $data = $this->getCancellationInvolved($inv->id);

        return Response::json($data, HttpFoundationResponse::HTTP_OK);
    }

    private function getCancellationInvolved(int $inv_id)
    {
        $inv_sku = Invoice::where('id', $inv_id)->value('sku');
        $do_skus = DeliveryOrder::whereIn('invoice_id', [$inv_id])->pluck('sku')->toArray();
        $do_ids = DeliveryOrder::whereIn('invoice_id', [$inv_id])->pluck('id')->toArray();
        $so_skus = Sale::where(function ($q) use ($do_ids) {
            for ($i = 0; $i < count($do_ids); $i++) {
                $q->orWhereRaw("find_in_set('".$do_ids[$i]."', convert_to)");
            }
        })->pluck('sku')->toArray();

        $involved = [
            $inv_sku => array_merge($do_skus, $so_skus),
        ];
        $involved_inv_skus = [$inv_sku];
        $involved_do_skus = $do_skus;
        $involved_so_skus = $so_skus;
        
        return [
            'involved' => $involved,
            'involved_inv_skus' => $involved_inv_skus,
            'involved_do_skus' => $involved_do_skus,
            'involved_so_skus' => $involved_so_skus,
        ];
    }

    private function getCancellationInvolvedInvFlow(int $inv_id, array $voided_do_ids)
    {
        $so_skus = [];
        $do_skus = [];
        $inv_ids = [];

        $do_ids = DeliveryOrder::whereNotIn('id', $voided_do_ids)->where('invoice_id', $inv_id)->pluck('id')->toArray();

        $sales = Sale::where('type', Sale::TYPE_SO);

        $sales = $sales->where(function ($q) use ($do_ids) {
            for ($i = 0; $i < count($do_ids); $i++) {
                $q->orWhereRaw("find_in_set('".$do_ids[$i]."', convert_to)");
            }
        });
        $sales = $sales->get();

        for ($i = 0; $i < count($sales); $i++) {
            $sale_do_ids = null;
            if (str_contains($sales[$i]->convert_to, ',')) {
                $sale_do_ids = explode(',', $sales[$i]->convert_to);
            } else {
                $sale_do_ids = [$sales[$i]->convert_to];
            }

            $so_skus[] = $sales[$i]->sku;
            $do_skus = array_merge($do_skus, DeliveryOrder::whereNotIn('id', $voided_do_ids)->whereIn('id', $sale_do_ids)->pluck('sku')->toArray());
            $inv_ids = array_merge($inv_ids, DeliveryOrder::whereNotIn('id', $voided_do_ids)->whereIn('id', $sale_do_ids)->whereNotNull('invoice_id')->pluck('invoice_id')->toArray());
        }

        return [
            'so_skus' => $so_skus,
            'do_skus' => $do_skus,
            'inv_ids' => $inv_ids,
        ];
    }

    public function download(Request $req)
    {
        if ($req->type == 'do') {
            return Storage::download(self::DELIVERY_ORDER_PATH.'/'.$req->query('file'));
        } elseif ($req->type == 'inv') {
            return Storage::download(self::INVOICE_PATH.'/'.$req->query('file'));
        } elseif ($req->type == 'billing') {
            return Storage::download(self::BILLING_PATH.'/'.$req->query('file'));
        } elseif ($req->type == 'ta') {
            return Storage::download(self::TRANSPORT_ACKNOWLEDGEMENT_PATH.'/'.$req->query('file'));
        }
    }

    public function indexTarget()
    {
        $page = Session::get('target-page');

        return view('target.list', [
            'default_page' => $page ?? null,
        ]);
    }

    public function getDataTarget(Request $req)
    {
        Session::put('target-page', $req->page);

        $records = Target::with('salesperson');

        // Search
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];

            $records = $records->where(function ($q) use ($keyword) {
                $q->where('amount', 'like', '%'.$keyword.'%')
                    ->orWhereHas('salesperson', function ($q) use ($keyword) {
                        return $q->where('name', 'like', '%'.$keyword.'%');
                    });
            });
        }
        // Order
        if ($req->has('order')) {
            $map = [
                0 => 'salesperson',
                1 => 'amount',
                2 => 'date',
            ];
            foreach ($req->order as $order) {
                if ($order['column'] == 0) {
                    $records = $records->orderBy(User::select('name')->whereColumn('users.id', 'targets.sale_id'), $order['dir']);
                } else {
                    $records = $records->orderBy($map[$order['column']], $order['dir']);
                }
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
                'sales' => $record->salesperson->name,
                'amount' => number_format($record->amount, 2),
                'date' => Carbon::parse($record->date)->format('M Y'),
                'can_create' => hasPermission('sale.target.create'),
                'can_edit' => hasPermission('sale.target.edit'),
            ];
        }

        return response()->json($data);
    }

    public function createTarget(Request $req)
    {
        $data = [];

        $data['period'] = CarbonPeriod::create(now()->format('M Y'), '1 month', now()->addYear()->format('M Y'))->toArray();

        if ($req->has('t')) {
            $data['duplicate_target'] = Target::where('id', $req->t)->first();
        }

        return view('target.form', $data);
    }

    public function storeTarget(Request $req)
    {
        // Validate request
        $validator = Validator::make($req->all(), [
            'sale' => 'required',
            'date' => 'required',
            'amount' => 'required',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $exists = Target::where('sale_id', $req->sale)->where('date', Carbon::parse($req->date)->format('Y-m-d'))->exists();
        if ($exists) {
            return back()->with('warning', 'Target has set for salesperson and date')->withInput();
        }

        try {
            DB::beginTransaction();

            $target = Target::create([
                'sale_id' => $req->sale,
                'date' => Carbon::parse($req->date)->format('Y-m-d'),
                'amount' => $req->amount,
            ]);
            (new Branch)->assign(Target::class, $target->id);

            DB::commit();

            return redirect(route('target.index'))->with('success', 'Target created');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator')->withInput();
        }
    }

    public function editTarget(Target $target)
    {
        $period = CarbonPeriod::create(now()->format('M Y'), '1 month', now()->addYear()->format('M Y'))->toArray();

        return view('target.form', [
            'target' => $target,
            'period' => $period,
        ]);
    }

    public function updateTarget(Request $req, Target $target)
    {
        // Validate request
        $validator = Validator::make($req->all(), [
            'sale' => 'required',
            'date' => 'required',
            'amount' => 'required',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $exists = Target::whereNot('id', $target->id)->where('sale_id', $req->sale)->where('date', Carbon::parse($req->date)->format('Y-m-d'))->exists();
        if ($exists) {
            return back()->with('warning', 'Target has set for salesperson and date')->withInput();
        }

        try {
            DB::beginTransaction();

            $target->update([
                'sale_id' => $req->sale,
                'date' => Carbon::parse($req->date)->format('Y-m-d'),
                'amount' => $req->amount,
            ]);

            DB::commit();

            return redirect(route('target.index'))->with('success', 'Target updated');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator')->withInput();
        }
    }

    public function indexSaleCancellation()
    {
        $page = Session::get('sale-cancellation-page');

        return view('sale_cancellation.list', [
            'default_page' => $page ?? null,
        ]);
    }

    public function getDataSaleCancellation(Request $req)
    {
        Session::put('sale-cancellation-page', $req->page);

        $qty_to_on_hold = DB::table('sale_order_cancellation')
            ->select('id', 'product_id', DB::raw('SUM(qty - COALESCE(extra, 0)) AS qty'))
            ->whereNotNull('on_hold_sale_id')
            ->whereNull('deleted_at')
            ->groupBy('product_id');

        $qty_to_sell = DB::table('sale_order_cancellation')
            ->select('id', 'product_id', DB::raw('SUM(qty) AS qty'))
            ->whereNull('on_hold_sale_id')
            ->whereNull('deleted_at')
            ->groupBy('product_id');

        $records = DB::table('sale_order_cancellation')
            ->select('products.sku AS product_sku', 'users.name AS saleperson', DB::raw('(COALESCE(qty_to_sell.qty, 0) - COALESCE(qty_to_on_hold.qty, 0)) AS qty'))
            ->leftJoinSub($qty_to_sell, 'qty_to_sell', function ($join) {
                $join->on('sale_order_cancellation.product_id', '=', 'qty_to_sell.product_id');
            })
            ->leftJoinSub($qty_to_on_hold, 'qty_to_on_hold', function ($join) {
                $join->on('sale_order_cancellation.product_id', '=', 'qty_to_on_hold.product_id');
            })
            ->leftJoin('products', 'products.id', '=', 'sale_order_cancellation.product_id')
            ->leftJoin('users', 'users.id', '=', 'sale_order_cancellation.saleperson_id')
            ->whereNull('on_hold_sale_id')
            ->whereNull('sale_order_cancellation.deleted_at')
            ->having('qty', '>', 0)
            ->groupBy('sale_order_cancellation.product_id');

        // Search
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];

            $records = $records->where(function ($q) use ($keyword) {
                $q->where('users.name', 'like', '%'.$keyword.'%')
                    ->orWhere('products.sku', 'like', '%'.$keyword.'%');
            });
        }
        // Order
        if ($req->has('order')) {
            $map = [
                0 => 'users.name',
                1 => 'products.sku',
                2 => DB::raw('(COALESCE(qty_to_sell.qty, 0) - COALESCE(qty_to_on_hold.qty, 0))'),
            ];
            foreach ($req->order as $order) {
                $records = $records->orderBy($map[$order['column']], $order['dir']);
            }
        } else {
            $records = $records->orderBy('sale_order_cancellation.id', 'desc');
        }

        $records_count = $records->count();
        $records_ids = $records->pluck('sale_order_cancellation.id');
        $records_paginator = $records->simplePaginate(10);

        $data = [
            'recordsTotal' => $records_count,
            'recordsFiltered' => $records_count,
            'data' => [],
            'records_ids' => $records_ids,
        ];
        foreach ($records_paginator as $key => $record) {
            $data['data'][] = [
                'product' => $record->product_sku,
                'qty' => $record->qty,
                'saleperson' => $record->saleperson,
            ];
        }

        return response()->json($data);
    }

    public function indexBilling()
    {
        $page = Session::get('billing-page');

        return view('billing.list', [
            'default_page' => $page ?? null,
        ]);
    }

    public function getDataBilling(Request $req)
    {
        Session::put('billing-page', $req->page);
        $records = new Billing;

        // Search
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];

            $records = $records->where(function ($q) use ($keyword) {
                $q->where('sku', 'like', '%'.$keyword.'%');
            });
        }
        // Order
        if ($req->has('order')) {
            $map = [
                1 => 'sku',
                2 => 'date',
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
                'billing_date' => $record->date,
                'do_filename' => $record->do_filename,
                'inv_filename' => $record->inv_filename,
            ];
        }

        return response()->json($data);
    }

    public function toBilling(Request $req)
    {
        $step = 1;

        if ($req->has('info')) {
            $errors = [];
            if ($req->term == null) {
                $errors['term'] = 'Please select a term';
            }
            if ($req->your_ref == null) {
                $errors['your_ref'] = 'Please enter a Your Ref';
            }
            if ($req->our_do_no == null) {
                $errors['our_do_no'] = 'Please enter a Our D/O No';
            }
            if (count($errors) > 0) {
                throw ValidationException::withMessages($errors);
            }

            $step = 3;

            Session::put('billing_term', $req->term);
            Session::put('billing_your_ref', $req->your_ref);
            Session::put('billing_our_do_no', $req->our_do_no);

            $inv_ids = Session::get('invoice_ids');
            $do_ids = DeliveryOrder::whereIn('invoice_id', explode(',', $inv_ids))->pluck('id');
            $dops = DeliveryOrderProduct::whereIn('delivery_order_id', $do_ids)->get();

            $products = [];
            foreach ($dops as $dop) {
                $is_raw_material = $dop->qty != null;
                $prod = Product::where('id', $dop->saleProduct->product->id)->first();

                if (! $is_raw_material) {
                    $dpc_count = DeliveryOrderProductChild::where('delivery_order_product_id', $dop->id)->count();
                }

                $product_appears_before = false;
                for ($i = 0; $i < count($products); $i++) {
                    if ($products[$i]->product->id == $prod->id) {
                        $products[$i]->qty += ! $is_raw_material ? $dpc_count : $dop->qty;

                        $product_appears_before = true;
                        break;
                    }
                }
                if ($product_appears_before) {
                    continue;
                }

                $products[] = (object) [
                    'product' => $prod,
                    'qty' => ! $is_raw_material ? $dpc_count : $dop->qty,
                ];
            }
        } elseif ($req->has('inv')) {
            $step = 2;

            Session::put('invoice_ids', $req->inv);
        } else {
            $invoices = Invoice::whereNull('status')->orderBy('id', 'desc')->get();
        }

        return view('billing.convert', [
            'step' => $step,
            'dos' => $dos ?? [],
            'invoices' => $invoices ?? [],
            'products' => $products ?? [],
        ]);
    }

    public function convertToBilling(Request $req)
    {
        // Validate
        $bill_products = [];
        $errors = [];
        $product_ids = explode(',', $req->product_ids);

        for ($i = 0; $i < count($product_ids); $i++) {
            $qty = $req->input('qty_'.$product_ids[$i]);
            $price = $req->input('price_'.$product_ids[$i]);

            for ($j = 0; $j < count($qty); $j++) {
                if ($qty[$j] == null && $price[$j] == null) {
                    continue;
                }
                if ($qty[$j] == null) {
                    $errors['row_'.$product_ids[$i]] = 'Quantity is required at row '.($i + 1);
                } elseif ($price[$j] == null) {
                    $errors['row_'.$product_ids[$i]] = 'Price is required at row '.($i + 1);
                }
                if ($qty[$j] != null && $price[$j] != null) {
                    $bill_products[] = [
                        'product_id' => $product_ids[$i],
                        'qty' => $qty[$j],
                        'price' => $price[$j],
                    ];
                }
            }
        }
        if (count($errors) > 0 || count($bill_products) <= 0) {
            return response()->json($errors, 400);
        }
        try {
            DB::beginTransaction();

            $sku = generateSku('', Billing::withoutGlobalScope(BranchScope::class)->pluck('sku')->toArray(), true);
            $do_filename = 'BDO-'.str_replace('/', '-', $sku).'.pdf';
            $inv_filename = 'BINV-'.str_replace('/', '-', $sku).'.pdf';

            $bill = Billing::create([
                'sku' => $sku,
                'do_filename' => $do_filename,
                'inv_filename' => $inv_filename,
                'term_id' => Session::get('billing_term'),
                'our_do_no' => Session::get('billing_our_do_no'),
                'date' => now(),
            ]);
            (new Branch)->assign(Billing::class, $bill->id);

            $invoiceIds = explode(',', Session::get('invoice_ids'));
            $bill->invoices()->attach($invoiceIds);

            for ($i = 0; $i < count($bill_products); $i++) {
                $bill_products[$i]['billing_id'] = $bill->id;
            }
            BillingProduct::insert($bill_products);

            for ($i = 0; $i < count($product_ids); $i++) {
                $product = Product::where('id', $product_ids[$i])->first();

                for ($j = 0; $j < count($bill_products); $j++) {
                    if ($bill_products[$j]['product_id'] == $product_ids[$i]) {
                        $bill_products[$j]['product'] = $product;
                    }
                }
            }

            $this->generateDeliveryOrderBillingPDF('BDO-'.$sku, $do_filename, $bill_products);
            $this->generateInvoiceBillingPDF('BINV-'.$sku, $inv_filename, $bill_products);

            DB::commit();

            Session::flash('success', 'Billing converted');

            return response()->json([
                'msg' => 'Billing converted',
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return response()->json([
                'msg' => 'Something went wrong. Please contact administrator',
            ], 500);
        }
    }

    private function generateDeliveryOrderBillingPDF(string $sku, string $filename, array $bill_products)
    {
        $pdf = Pdf::loadView('billing.do_inv_pdf', [
            'is_do' => true,
            'date' => now()->format('d/m/Y'),
            'sku' => $sku,
            'your_ref' => Session::get('billing_your_ref'),
            'our_do_no' => Session::get('billing_our_do_no'),
            'term' => CreditTerm::where('id', Session::get('billing_term'))->value('name'),
            'bill_products' => $bill_products,
        ]);
        $pdf->setPaper('A4', 'letter');
        $content = $pdf->download()->getOriginalContent();
        Storage::put(self::BILLING_PATH.$filename, $content);
    }

    private function generateInvoiceBillingPDF(string $sku, string $filename, array $bill_products)
    {
        $pdf = Pdf::loadView('billing.do_inv_pdf', [
            'is_do' => false,
            'date' => now()->format('d/m/Y'),
            'sku' => $sku,
            'your_ref' => Session::get('billing_your_ref'),
            'our_do_no' => Session::get('billing_our_do_no'),
            'term' => CreditTerm::where('id', Session::get('billing_term'))->value('name'),
            'bill_products' => $bill_products,
        ]);
        $pdf->setPaper('A4', 'letter');
        $content = $pdf->download()->getOriginalContent();
        Storage::put(self::BILLING_PATH.$filename, $content);
    }

    /**
     * Get Sale ids which has no serial number in production
     */
    public function getSaleInProduction(): array
    {
        $pc_in_factory = ProductChild::where('location', ProductChild::LOCATION_FACTORY)->distinct()->pluck('id');
        $spc_in_factory = SaleProductChild::whereIn('product_children_id', $pc_in_factory)->pluck('sale_product_id');
        $sale_ids = SaleProduct::whereIn('id', $spc_in_factory)->pluck('sale_id')->toArray();

        return $sale_ids;
    }

    public function indexPendingOrder()
    {
        $page = Session::get('pending-order-page');

        return view('sale_pending.list', [
            'default_page' => $page ?? null,
        ]);
    }

    public function getDataPendingOrder(Request $req)
    {
        Session::put('pending-order-page', $req->page);

        $records = Sale::where('type', Sale::TYPE_PENDING);

        $records = $records
            ->select('sales.*', 'platforms.name AS platformName', DB::raw('SUM("sale_payment_amounts.amount") AS paymentAmounts'))
            ->leftJoin('platforms', 'platforms.id', '=', 'sales.platform_id')
            ->leftJoin('sale_payment_amounts', 'sale_payment_amounts.sale_id', '=', 'sales.id')
            ->whereNull('sale_payment_amounts.deleted_at');

        // Search
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];

            $records->where(function ($q) use ($keyword) {
                $q->where('sales.sku', 'like', '%'.$keyword.'%')
                    ->orWhere('platforms.name', 'like', '%'.$keyword.'%');
            });
        }
        // Order
        if ($req->has('order')) {
            $map = [
                1 => 'sales.sku',
                2 => DB::raw('SUM("sale_payment_amounts.amount")'),
                3 => 'platforms.name',
                4 => 'sales.status',
            ];
            foreach ($req->order as $order) {
                $records = $records->orderBy($map[$order['column']], $order['dir']);
            }
        } else {
            $records = $records->orderBy('sales.id', 'desc');
        }
        $records = $records->groupBy('sales.id');

        $records_count = $records->count();
        $records_ids = $records->pluck('sales.id');
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
                'total_amount' => $record->paymentAmount,
                'status' => $record->status,
                'platform' => $record->platformName ?? '-',
                'can_edit' => hasPermission('sale.sale_order.edit'),
                'can_delete' => hasPermission('sale.sale_order.delete'),
            ];
        }

        return response()->json($data);
    }

    public function createPendingOrder(Request $req)
    {
        $data = [];

        if ($req->has('qid')) {
            $quo = Sale::findOrFail($req->qid);
            $quo->load('products');

            $data['quo'] = $quo;
        }

        return view('sale_order.form', $data);
    }

    public function editPendingOrder(Sale $sale)
    {
        $sale->load('products.product.children', 'products.children');

        $sale->products->each(function ($q) {
            $q->attached_to_do = $q->attachedToDo();
        });

        return view('sale_order.form', [
            'sale' => $sale,
        ]);
    }

    public function pdfPendingOrder(Sale $sale)
    {
        $pdf = Pdf::loadView('sale_order.'.(isHiTen($sale->customer->company_group) ? 'hi_ten' : 'powercool').'_pdf', [
            'date' => now()->format('d/m/Y'),
            'sale' => $sale,
            'products' => $sale->products,
            'saleperson' => $sale->saleperson,
            'customer' => $sale->customer,
            'billing_address' => (new CustomerLocation)->defaultBillingAddress($sale->customer->id),
        ]);
        $pdf->setPaper('A4', 'letter');

        return $pdf->stream($sale->sku.'.pdf');
    }

    public function getSalePerson(Request $request)
    {
        $salePersons = User::whereHas('roles', function ($q) {
            $q->where('id', Role::SALE);
        })->orderBy('id', 'desc')->get();

        return response()->json(['salesPersons' => $salePersons]);
    }

    public function assignSalePerson(Request $request)
    {
        try {
            $validated = $request->validate([
                'salesPersonId' => 'required',
                'sales' => 'required|array',
                'sales.*.id' => 'required',
            ]);

            $salesPersonId = $validated['salesPersonId'];

            $saleIds = collect($validated['sales'])->pluck('id');

            Sale::whereIn('id', $saleIds)->update([
                'sale_id' => $salesPersonId,
                'type' => Sale::TYPE_SO,
            ]);

            return response()->json(['message' => 'Orders successfully assigned to sales person']);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()]);
        }
    }

    public function getPendingOrdersCount()
    {
        $pendingOrdersCount = Sale::where('type', Sale::TYPE_PENDING)->count();

        return response()->json(['count' => $pendingOrdersCount]);
    }

    public function transportAcknowledgement()
    {
        return view('delivery_order.generate_transport_acknowledgement');
    }

    public function generateTransportAcknowledgement(Request $req)
    {
        // Validate form
        $rules = [
            'delivery_order' => 'required',
            'dealer' => 'required',
            'type' => 'required',
        ];
        $req->validate($rules);

        try {
            DB::beginTransaction();

            if ($req->dealer != '-1' && $req->dealer != '-2') {
                $dealer = Dealer::where('id', $req->dealer)->first();

                $dealer_name = $dealer->name;
            } elseif ($req->dealer == '-1') {
                $dealer_name = 'Power Cool';
            } elseif ($req->dealer == '-2') {
                $dealer_name = 'Hi Ten Trading';
            }

            $do = DeliveryOrder::where('id', $req->delivery_order)->first();
            $first_so = Sale::where('type', Sale::TYPE_SO)->whereRaw("find_in_set('".$do->id."', convert_to)")->first();
            $dopcs = collect();

            for ($i = 0; $i < count($do->products); $i++) {
                for ($j = 0; $j < count($do->products[$i]->children); $j++) {
                    $dopcs->push($do->products[$i]->children[$j]);
                }
            }

            $existing_skus = transportAcknowledgement::withoutGlobalScope(BranchScope::class)->pluck('sku')->toArray();
            $sku = generateSku($req->type == DeliveryOrder::TRANSPORT_ACK_TYPE_DELIVERY ? 'DL' : 'CL', $existing_skus);
            $delivery_address = CustomerLocation::whereIn('type', [CustomerLocation::TYPE_BILLING_ADN_DELIVERY, CustomerLocation::TYPE_DELIVERY])->where('customer_id', $first_so->customer->id)->first();

            $pdf = Pdf::loadView('delivery_order.transport_acknowledgement_pdf', [
                'date' => now()->format('d/m/Y'),
                'sku' => $sku,
                'is_delivery' => $req->type == DeliveryOrder::TRANSPORT_ACK_TYPE_DELIVERY,
                'do_sku' => $do->sku,
                'address' => $delivery_address->formatAddress(),
                'dopcs' => $dopcs,
                'dealer_name' => $dealer_name,
            ]);
            $pdf->setPaper('A4', 'letter');
            $content = $pdf->download()->getOriginalContent();
            $filename = 'transport-ack-'.now()->format('ymdhis').'.pdf';
            Storage::put(self::TRANSPORT_ACKNOWLEDGEMENT_PATH.$filename, $content);

            TransportAcknowledgement::create([
                'sku' => $sku,
                'date' => now()->format('Y-m-d'),
                'dealer_id' => $req->dealer,
                'customer_id' => $first_so->customer->id,
                'filename' => $filename,
                'generated_by' => Auth::user()->id,
            ]);

            $do->transport_ack_filename = $filename;
            $do->save();

            DB::commit();

            return redirect(route('delivery_order.index'))->with('success', 'Transport Acknowledgement generated');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong, Please contact administrator');
        }
    }

    public function indexTransportAck()
    {
        $page = Session::get('transport-ack-page');

        return view('transport_ack.list', [
            'default_page' => $page ?? null,
        ]);
    }

    public function getDataTransportAck(Request $req)
    {
        Session::put('transport-ack-page', $req->page);

        $records = new TransportAcknowledgement;
        $records = $records
            ->select(
                'transport_acknowledgements.*',
                'users.name AS customerName',
                'generator.name AS generatedBy',
            )
            ->leftJoin('users', 'users.id', '=', 'transport_acknowledgements.customer_id')
            ->leftJoin('users AS generator', 'generator.id', '=', 'transport_acknowledgements.generated_by');

        // Order
        if ($req->has('order')) {
            $map = [
                1 => 'transport_acknowledgements.sku',
                2 => 'transport_acknowledgements.date',
                4 => 'users.name',
                5 => 'generator.name',
            ];
            foreach ($req->order as $order) {
                $records = $records->orderBy($map[$order['column']], $order['dir']);
            }
        } else {
            $records = $records->orderBy('transport_acknowledgements.id', 'desc');
        }

        $records_count = count($records->get());
        $records_ids = $records->pluck('transport_acknowledgements.id');
        $records_paginator = $records->simplePaginate(10);

        $data = [
            'recordsTotal' => $records_count,
            'recordsFiltered' => $records_count,
            'data' => [],
            'records_ids' => $records_ids,
        ];
        $path = '/public/storage';
        if (config('app.env') == 'local') {
            $path = '/storage';
        }
        foreach ($records_paginator as $key => $record) {
            $transport_ack_filename = $record->filename == null ? null : config('app.url').str_replace('public', $path, self::TRANSPORT_ACKNOWLEDGEMENT_PATH).'/'.$record->filename;

            $data['data'][] = [
                'no' => $key + 1,
                'id' => $record->id,
                'sku' => $record->sku,
                'date' => Carbon::parse($record->date)->format('d M Y'),
                'dealer' => $record->dealerName(),
                'customer' => $record->customerName,
                'created_by' => $record->generatedBy,
                'filename' => $transport_ack_filename,
            ];
        }

        return response()->json($data);
    }

    public function createTransportAck()
    {
        return view('transport_ack.generate');
    }

    public function editTransportAck(Request $req, TransportAcknowledgement $ack)
    {
        return view('transport_ack.generate', [
            'ack' => $ack->load('products'),
        ]);
    }

    public function generateTransportAcknowledgementTransportAck(Request $req, ?TransportAcknowledgement $ack = null)
    {
        // Validate form
        $rules = [
            'do_id' => 'nullable|max:250',
            'date' => 'required',
            'company_name' => 'required',
            'phone' => 'required',
            'delivery_to' => 'required',
            'dealer' => 'required',
            'type' => 'required',
            'product' => 'required',
            'product.*' => 'required',
            'qty' => 'required',
            'qty.*' => 'required',
            'description' => 'nullable',
            'description.*' => 'nullable|max:250',
            'remark' => 'nullable',
            'remark.*' => 'nullable|max:250',
        ];
        $req->validate($rules, [
            'product.*.required' => 'The product at row :position is required',
            'qty.*.required' => 'The quantity at row :position is required',
        ], [
            'do_id' => 'delivery order id',
            'delivery_to' => 'address',
            'company_name' => 'customer name',
        ]);

        try {
            DB::beginTransaction();

            if ($req->dealer != '-1' && $req->dealer != '-2') {
                $dealer = Dealer::where('id', $req->dealer)->first();

                $dealer_name = $dealer->name;
            } elseif ($req->dealer == '-1') {
                $dealer_name = 'Power Cool';
            } elseif ($req->dealer == '-2') {
                $dealer_name = 'Hi Ten Trading';
            }

            $items = [];
            $product_data = [];
            for ($i = 0; $i < count($req->product); $i++) {
                $product = Product::where('id', $req->product[$i])->first();
                $serial_no = $req->{'serial_no_'.$req->product[$i]} != null && $req->{'serial_no_'.$req->product[$i]} != '' ? ProductChild::whereIn('id', $req->{'serial_no_'.$req->product[$i]})->pluck('sku')->toArray() : [];

                $items[] = [
                    'item' => $product->sku,
                    'desc' => $req->description[$i] != null ? $req->description[$i] : $product->model_desc,
                    'remark' => $req->remark[$i] != null ? $req->remark[$i] : null,
                    'qty' => $req->qty[$i],
                    'serial_no' => count($serial_no) > 0 ? implode(', ', $serial_no) : null,
                ];
                $product_data[] = [
                    'product_id' => $product->id,
                    'desc' => $req->description[$i] != null ? $req->description[$i] : $product->model_desc,
                    'remark' => $req->remark[$i] != null ? $req->remark[$i] : null,
                    'qty' => $req->qty[$i],
                    'product_child_id' => $req->{'serial_no_'.$req->product[$i]} != null && $req->{'serial_no_'.$req->product[$i]} != '' ? $req->{'serial_no_'.$req->product[$i]} : null,
                ];
            }

            $existing_skus = transportAcknowledgement::withoutGlobalScope(BranchScope::class)->pluck('sku')->toArray();
            $sku = generateSku($req->type == DeliveryOrder::TRANSPORT_ACK_TYPE_DELIVERY ? 'DL' : 'CL', $existing_skus);

            $pdf = Pdf::loadView('transport_ack.transport_acknowledgement', [
                'sku' => $sku,
                'date' => Carbon::createFromFormat('Y-m-d', $req->date)->format('d/m/Y'),
                'is_delivery' => $req->type == DeliveryOrder::TRANSPORT_ACK_TYPE_DELIVERY,
                'do_sku' => $req->do_id ?? null,
                'address' => $req->delivery_to,
                'dealer_name' => $dealer_name,
                'items' => $items,
            ]);
            $pdf->setPaper('A4', 'letter');
            $content = $pdf->download()->getOriginalContent();
            $filename = 'transport-ack-'.generateRandomAlphabet(10).'-'.Auth::user()->id.'.pdf';
            Storage::put(self::TRANSPORT_ACKNOWLEDGEMENT_PATH.$filename, $content);

            // Save record
            if ($ack == null) {
                $ack = TransportAcknowledgement::create([
                    'sku' => $sku,
                    'date' => $req->date,
                    'dealer_id' => $req->dealer,
                    'delivery_order_id' => $req->do_id,
                    'type' => $req->type,
                    'company_name' => $req->company_name,
                    'phone' => $req->phone,
                    'address' => $req->delivery_to,
                    'filename' => $filename,
                    'generated_by' => Auth::user()->id,
                ]);
                for ($i = 0; $i < count($product_data); $i++) {
                    $product_data[$i]['transport_acknowledgement_id'] = $ack->id;
                }
                TransportAcknowledgementProduct::insert($product_data);
                (new Branch)->assign(TransportAcknowledgement::class, $ack->id);

                // Create agent debtor
                $agent = AgentDebtor::create([
                    'sku' => (new AgentDebtor)->generateSku(),
                    'company_name' => $req->company_name,
                    'phone' => $req->phone,
                    'address' => $req->delivery_to,
                    'dealer_id' => $req->dealer,
                ]);
                (new Branch)->assign(AgentDebtor::class, $agent->id);
            } else {
                $ack->update([
                    'sku' => $sku,
                    'date' => $req->date,
                    'dealer_id' => $req->dealer,
                    'delivery_order_id' => $req->do_id,
                    'type' => $req->type,
                    'company_name' => $req->company_name,
                    'phone' => $req->phone,
                    'address' => $req->delivery_to,
                    'filename' => $filename,
                    'generated_by' => Auth::user()->id,
                ]);
                for ($i = 0; $i < count($product_data); $i++) {
                    $product_data[$i]['transport_acknowledgement_id'] = $ack->id;
                }
                TransportAcknowledgementProduct::where('transport_acknowledgement_id', $ack->id)->delete();
                TransportAcknowledgementProduct::insert($product_data);
            }

            DB::commit();

            return redirect(route('transport_ack.index'))->with('success', 'Transport Acknowledgement generated');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong, Please contact administrator');
        }
    }

    public function getNextSku(Request $req)
    {
        $existing_skus = Sale::withoutGlobalScope(BranchScope::class)->where('type', $req->type == 'quo' ? Sale::TYPE_QUO : ($req->type == 'so' ? Sale::TYPE_SO : Sale::TYPE_CASH_SALE))->pluck('sku')->toArray();
        $next_sku = generateSku($req->type == 'quo' ? 'QT' : ($req->type == 'so' ? 'SO' : 'CS'), $existing_skus, $req->is_hi_ten);

        return $next_sku;
    }
}
