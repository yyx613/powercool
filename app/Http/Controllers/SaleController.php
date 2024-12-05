<?php

namespace App\Http\Controllers;

use App\Models\Billing;
use App\Models\Branch;
use App\Models\ConsolidatedEInvoice;
use App\Models\CreditNote;
use App\Models\CreditTerm;
use App\Models\Customer;
use App\Models\CustomerLocation;
use App\Models\DebitNote;
use App\Models\DeliveryOrder;
use App\Models\DeliveryOrderProduct;
use App\Models\EInvoice;
use App\Models\Invoice;
use App\Models\Platform;
use App\Models\Product;
use App\Models\ProductChild;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SaleOrderCancellation;
use App\Models\SaleProduct;
use App\Models\SaleProductChild;
use App\Models\Target;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

use function PHPUnit\Framework\isEmpty;

class SaleController extends Controller
{
    const DELIVERY_ORDER_PATH = '/public/delivery_order/';
    const INVOICE_PATH = '/public/invoice/';
    const BILLING_PATH = '/public/billing/';

    public function index()
    {
        return view('quotation.list');
    }

    public function getData(Request $req)
    {
        $records = Sale::where('type', Sale::TYPE_QUO);

        // Search
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];

            $records->where(function ($q) use ($keyword) {
                $q->where('sku', 'like', '%' . $keyword . '%')
                    ->orWhere('reference', 'like', '%' . $keyword . '%')
                    ->orWhere('remark', 'like', '%' . $keyword . '%');
            });
        }
        // Order
        if ($req->has('order')) {
            $map = [
                0 => 'sku',
                1 => 'open_until',
            ];
            foreach ($req->order as $order) {
                $records->orderBy($map[$order['column']], $order['dir']);
            }
        } else {
            $records->orderBy('id', 'desc');
        }

        $records_count = $records->count();
        $records_ids = $records->pluck('id');
        $records_paginator = $records->simplePaginate(10);

        $data = [
            "recordsTotal" => $records_count,
            "recordsFiltered" => $records_count,
            "data" => [],
            'records_ids' => $records_ids,
        ];
        foreach ($records_paginator as $key => $record) {
            $data['data'][] = [
                'id' => $record->id,
                'sku' => $record->sku,
                'open_until' => $record->open_until,
                'status' => $record->status,
                'can_edit' => hasPermission('sale.quotation.edit'),
                'can_delete' => hasPermission('sale.quotation.delete'),
            ];
        }

        return response()->json($data);
    }

    public function create(Request $req)
    {
        return view('quotation.form');
    }

    public function edit(Sale $sale)
    {
        return view('quotation.form', [
            'sale' => $sale->load('products.product.children', 'products.children')
        ]);
    }

    public function delete(Sale $sale)
    {
        try {
            DB::beginTransaction();

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
        $pdf = Pdf::loadView('quotation.' . $this->getPdfType($sale->products) . '_pdf', [
            'date' => now()->format('d/m/Y'),
            'sale' => $sale,
            'products' => $sale->products,
            'customer' => $sale->customer,
            'billing_address' => (new CustomerLocation)->defaultBillingAddress($sale->customer->id),
        ]);
        $pdf->setPaper('A4', 'letter');

        return $pdf->stream();
    }

    public function toSaleOrder(Request $req)
    {
        $step = 1;

        if ($req->has('sp')) {
            $step = 3;

            Session::put('convert_salesperson_id', $req->sp);

            $quotations = Sale::where('type', Sale::TYPE_QUO)
                ->whereNotIn('id', $this->getSaleInProduction())
                ->whereHas('products')
                ->where('status', Sale::STATUS_ACTIVE)
                ->where('customer_id', Session::get('convert_customer_id'))
                ->where('sale_id', Session::get('convert_salesperson_id'))
                ->get();
        } else if ($req->has('cus')) {
            $step = 2;

            Session::put('convert_customer_id', $req->cus);

            $salesperson_ids = Sale::where('type', Sale::TYPE_QUO)
                ->whereNotIn('id', $this->getSaleInProduction())
                ->whereHas('products')
                ->where('status', Sale::STATUS_ACTIVE)
                ->where('customer_id', $req->cus)
                ->distinct()
                ->pluck('sale_id');

            $salespersons = User::whereIn('id', $salesperson_ids)->get();
        } else {
            $customer_ids = Sale::where('type', Sale::TYPE_QUO)
                ->whereNotIn('id', $this->getSaleInProduction())
                ->whereHas('products')
                ->where('status', Sale::STATUS_ACTIVE)
                ->distinct()
                ->pluck('customer_id');

            $customers = Customer::whereIn('id', $customer_ids)->get();
        }

        return view('quotation.convert', [
            'step' => $step,
            'customers' => $customers ?? [],
            'salespersons' => $salespersons ?? [],
            'quotations' => $quotations ?? [],
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
                'reference' => join(',', $references->toArray()),
                'status' => true,
                'type' => 'so',
                'report_type' => $req->report_type,
            ]);
            $res = $this->upsertQuoDetails($request)->getData();
            if ($res->result != true) {
                throw new Exception("Failed to create quotation");
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
                'unit_price' => $products->map(function ($q) {
                    return $q->unit_price;
                })->toArray(),
                'promotion_id' => $products->map(function ($q) {
                    return $q->promotion_id;
                })->toArray(),
                'product_serial_no' => $products->map(function ($q) {
                    return $q->children->pluck('product_children_id')->toArray();
                })->toArray(),
                'warranty_period' => $products->map(function ($q) {
                    return $q->warranty_period_id;
                })->toArray(),
            ]);
            $res = $this->upsertProDetails($request)->getData();
            if ($res->result != true) {
                throw new Exception("Failed to create product");
            }

            // Create remark details
            $request = new Request([
                'sale_id' => $sale_id,
                'remark' => count($remarks) <= 0 ? null : join(',', $remarks->toArray()),
            ]);
            $res = $this->upsertRemark($request)->getData();
            if ($res->result != true) {
                throw new Exception("Failed to create remark");
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
        return view('sale_order.list');
    }

    public function getDataSaleOrder(Request $req)
    {
        $records = Sale::where('type', Sale::TYPE_SO);
        // Search
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];

            $records->where(function ($q) use ($keyword) {
                $q->where('sku', 'like', '%' . $keyword . '%')
                    ->orWhereHas('platform', function ($q) use ($keyword) {
                        $q->where('name', 'like', '%' . $keyword . '%');
                    })
                    ->orWhere('reference', 'like', '%' . $keyword . '%')
                    ->orWhere('remark', 'like', '%' . $keyword . '%')
                    ->orWhere('payment_method', 'like', '%' . $keyword . '%')
                    ->orWhere('payment_amount', 'like', '%' . $keyword . '%')
                    ->orWhere('payment_remark', 'like', '%' . $keyword . '%')
                    ->orWhere('delivery_instruction', 'like', '%' . $keyword . '%');
            });
        }
        // Order
        if ($req->has('order')) {
            $map = [
                0 => 'sku',
                1 => 'payment_amount',
                2  => 'platform'
            ];
            foreach ($req->order as $order) {
                $records->orderBy($map[$order['column']], $order['dir']);
            }
        } else {
            $records->orderBy('id', 'desc');
        }

        $records_count = $records->count();
        $records_ids = $records->pluck('id');
        $records_paginator = $records->simplePaginate(10);

        $data = [
            "recordsTotal" => $records_count,
            "recordsFiltered" => $records_count,
            "data" => [],
            'records_ids' => $records_ids,
        ];
        foreach ($records_paginator as $key => $record) {
            $data['data'][] = [
                'id' => $record->id,
                'sku' => $record->sku,
                'total_amount' => $record->payment_amount,
                'status' => $record->status,
                'platform' => $record->platform->name ?? '-',
                'cancellation_charge' => number_format($record->cancellation_charge, 2),
                'can_edit' => hasPermission('sale.sale_order.edit'),
                'can_cancel' => hasPermission('sale.sale_order.cancel') && $record->status == Sale::STATUS_ACTIVE,
                'can_delete' => hasPermission('sale.sale_order.delete') && !in_array($record->status, [Sale::STATUS_CONVERTED, Sale::STATUS_CANCELLED]),
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
        if ($sale->status == Sale::STATUS_CANCELLED) {
            $sale->load([
                'products'=> function ($q) {
                    $q->withTrashed()->with(['product' => function ($q) {
                        $q->withTrashed()->with(['children' => function ($q) {
                            $q->withTrashed();
                        }]);
                    }]);
                },
                'products'=> function ($q) {
                    $q->withTrashed()->with(['children' => function ($q) {
                        $q->withTrashed();
                    }]);
                },
            ]);
        } else {
            $sale->load('products.product.children', 'products.children');
        }

        $sale->products->each(function ($q) {
            $q->attached_to_do = $q->attachedToDo();
        });

        return view('sale_order.form', [
            'sale' => $sale
        ]);
    }

    public function cancelSaleOrder(Request $req, Sale $sale) {
        try {
            DB::beginTransaction();

            $this->cancelSaleOrderFlow($sale, false, $req->charge);            

            DB::commit();

            return back()->with('success', 'Quotation cancelled');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator');
        }
    }

    public function pdfSaleOrder(Sale $sale)
    {
        $pdf = Pdf::loadView('sale_order.' . $this->getPdfType($sale->products) . '_pdf', [
            'date' => now()->format('d/m/Y'),
            'sale' => $sale,
            'products' => $sale->products,
            'saleperson' => $sale->saleperson,
            'customer' => $sale->customer,
            'billing_address' => (new CustomerLocation)->defaultBillingAddress($sale->customer->id),
        ]);
        $pdf->setPaper('A4', 'letter');

        return $pdf->stream();
    }

    public function toDeliveryOrder(Request $req)
    {
        $step = 1;

        if ($req->has('so')) {
            $step = 5;

            Session::put('convert_sale_order_id', $req->so);

            $products = collect();
            $sale_orders = Sale::where('type', Sale::TYPE_SO)
                ->whereNotIn('id', $this->getSaleInProduction())
                ->where('status', Sale::STATUS_ACTIVE)
                ->whereHas('products')
                ->whereIn('id', explode(',', $req->so))
                ->get();

            for ($i = 0; $i < count($sale_orders); $i++) {
                $products = $products->merge($sale_orders[$i]->products);
            }
        } else if ($req->has('term')) {
            $step = 4;

            Session::put('convert_terms', $req->term);

            $sale_orders = Sale::where('type', Sale::TYPE_SO)
                ->whereNotIn('id', $this->getSaleInProduction())
                ->where('status', Sale::STATUS_ACTIVE)
                ->whereHas('products')
                ->where('customer_id', Session::get('convert_customer_id'))
                ->where('sale_id', Session::get('convert_salesperson_id'))
                ->where('payment_term', Session::get('convert_terms'))
                ->get();
        } else if ($req->has('sp')) {
            $step = 3;

            Session::put('convert_salesperson_id', $req->sp);

            $term_ids = Sale::where('type', Sale::TYPE_SO)
                ->whereNotIn('id', $this->getSaleInProduction())
                ->where('status', Sale::STATUS_ACTIVE)
                ->where('customer_id', Session::get('convert_customer_id'))
                ->where('sale_id', Session::get('convert_salesperson_id'))
                ->whereHas('products')
                ->whereNotNull('payment_term')
                ->distinct()
                ->pluck('payment_term');

            $terms = CreditTerm::whereIn('id', $term_ids)->get();
        } else if ($req->has('cus')) {
            $step = 2;

            Session::put('convert_customer_id', $req->cus);

            $salesperson_ids = Sale::where('type', Sale::TYPE_SO)
                ->whereNotIn('id', $this->getSaleInProduction())
                ->where('status', Sale::STATUS_ACTIVE)
                ->where('customer_id', $req->cus)
                ->distinct()
                ->pluck('sale_id');

            $salespersons = User::whereIn('id', $salesperson_ids)->get();
        } else {
            $sales = Sale::where('type', Sale::TYPE_SO)
                ->whereNotIn('id', $this->getSaleInProduction())
                ->where('status', Sale::STATUS_ACTIVE)
                ->whereHas('products')
                ->distinct()
                ->get();

            $customer_ids = [];
            for ($i = 0; $i < count($sales); $i++) {
                for ($j = 0; $j < count($sales[$i]->products); $j++) {
                    if ($sales[$i]->products[$j]->remainingQty() > 0) {
                        $customer_ids[] = $sales[$i]->customer_id;
                    }
                }
            }

            $customers = Customer::whereIn('id', $customer_ids)->get();
        }

        return view('sale_order.convert', [
            'step' => $step,
            'customers' => $customers ?? [],
            'salespersons' => $salespersons ?? [],
            'sale_orders' => $sale_orders ?? [],
            'products' => $products ?? [],
            'terms' => $terms ?? [],
        ]);
    }

    public function converToDeliveryOrder(Request $req)
    {
        try {
            DB::beginTransaction();

            // Create PDF
            $product_ids = explode(',', $req->prod);
            $qtys = explode(',', $req->qty);
            $sale_order_ids_to_convert = explode(',', Session::get('convert_sale_order_id'));
            for ($i = 0; $i < count($product_ids); $i++) {
                $prod_qty[$product_ids[$i]] = $qtys[$i];
            }
            $sku = (new DeliveryOrder)->generateSku();

            $sale_orders = Sale::where('type', Sale::TYPE_SO)->whereIn('id', $sale_order_ids_to_convert)->get();

            $products = SaleProduct::whereIn('id', $product_ids)->get();

            $deli_addresses = [];
            $deli_address_not_same = false;
            for ($i = 0; $i < count($sale_orders); $i++) {
                if ($sale_orders[$i]->delivery_address_id != null) {
                    if (in_array($sale_orders[$i]->delivery_address_id, $deli_addresses)) {
                        $deli_address_not_same = true;
                    }
                    $deli_addresses[] = $sale_orders[$i]->delivery_address_id;
                }

                if ($deli_address_not_same) {
                    break;
                }
            }

            $pdf = Pdf::loadView('sale_order.' . $this->getPdfType($products) . '_do_pdf', [
                'date' => now()->format('d/m/Y'),
                'sku' => $sku,
                'customer' => Customer::where('id', Session::get('convert_customer_id'))->first(),
                'salesperson' => User::where('id', Session::get('convert_salesperson_id'))->first(),
                'products' => $products,
                'sale_orders' => $sale_orders,
                'prod_qty' => $prod_qty,
                'billing_address' => (new CustomerLocation)->defaultBillingAddress(Session::get('convert_customer_id')),
                'delivery_address' => !$deli_address_not_same || count($deli_addresses) <= 0 ? null : CustomerLocation::where('type', CustomerLocation::TYPE_DELIVERY)->where('id', $deli_addresses[0])->first(),
                'terms' => Session::get('convert_terms'),
            ]);
            $pdf->setPaper('A4', 'letter');
            $content = $pdf->download()->getOriginalContent();
            $filename = $sku . '.pdf';
            Storage::put(self::DELIVERY_ORDER_PATH . $filename, $content);

            // Create DO
            $do = DeliveryOrder::create([
                'customer_id' => Session::get('convert_customer_id'),
                'sale_id' => Session::get('convert_salesperson_id'),
                'payment_terms' => Session::get('convert_terms'),
                'sku' => $sku,
                'filename' => $filename
            ]);
            (new Branch)->assign(DeliveryOrder::class, $do->id);
            // Create DO products
            $dop = [];
            $soc_alter_qty = [];
            foreach ($prod_qty as $prod_id => $qty) {
                $dop[] = [
                    'delivery_order_id' => $do->id,
                    'sale_product_id' => $prod_id,
                    'qty' => $qty,
                    'created_at' => $do->created_at,
                    'updated_at' => $do->updated_at,
                ];
                $soc_alter_qty[$prod_id] = $qty;
            }
            DeliveryOrderProduct::insert($dop);

            // Change SO's status to converted, if SO has no product left to convert
            $sales = Sale::where('type', Sale::TYPE_SO)->whereIn('id', $sale_order_ids_to_convert)->get();
            
            for ($i=0; $i < count($sales); $i++) { 
                if ($sales[$i]->hasNoMoreQtyToConvertDO()) {
                    $sales[$i]->status = Sale::STATUS_CONVERTED;
                }

                $current_do_ids = [];
                if ($sales[$i]->convert_to != null) {
                    $current_do_ids = explode(',', $sales[$i]->convert_to);
                }
                $current_do_ids[] = $do->id;

                $sales[$i]->convert_to = join(',', $current_do_ids);
                $sales[$i]->save();

                SaleOrderCancellation::calCancellation($sales[$i], 2, $soc_alter_qty);
            }

            DB::commit();

            return redirect(route('delivery_order.index'))->with('success', 'Sale Order has converted');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator');
        }
    }

    public function upsertQuoDetails(Request $req)
    {
        // Validate form
        $rules = [
            'sale_id' => 'nullable',
            'quo_id' => 'nullable',
            'sale' => 'required',
            'customer' => 'required',
            'reference' => 'required',
            'from' => 'nullable|max:250',
            'cc' => 'nullable|max:250',
            'status' => 'required',
            'report_type' => 'required',
        ];
        if ($req->type == 'quo') {
            $rules['open_until'] = 'required';
        }
        $req->validate($rules, [], [
            'report_type' => 'type',
            'customer' => 'company',
        ]);

        try {
            DB::beginTransaction();

            if ($req->sale_id == null) {
                $sale = Sale::create([
                    'type' => $req->type == 'quo' ? Sale::TYPE_QUO : Sale::TYPE_SO,
                    'sku' => (new Sale)->generateSku($req->type == 'quo' ? Sale::TYPE_QUO : Sale::TYPE_SO),
                    'sale_id' => $req->sale,
                    'customer_id' => $req->customer,
                    'open_until' => $req->open_until,
                    'reference' => $req->type == 'quo' ? $req->reference : json_encode(explode(',', $req->reference)),
                    'quo_from' => $req->from,
                    'quo_cc' => $req->cc,
                    'status' => $req->status,
                    'report_type' => $req->report_type,
                ]);

                (new Branch)->assign(Sale::class, $sale->id);
            } else {
                $sale = Sale::where('id', $req->sale_id)->first();

                $sale->update([
                    'sale_id' => $req->sale,
                    'customer_id' => $req->customer,
                    'open_until' => $req->open_until,
                    'reference' => $req->type == 'quo' ? $req->reference : json_encode(explode(',', $req->reference)),
                    'quo_from' => $req->from,
                    'quo_cc' => $req->cc,
                    'status' => $req->status,
                    'report_type' => $req->report_type,
                ]);
            }

            if ($req->quo_id != null) {
                Sale::where('id', $req->quo_id)->delete();
            }

            DB::commit();

            return Response::json([
                'result' => true,
                'sale' => $sale
            ], HttpFoundationResponse::HTTP_OK);
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return Response::json([
                'result' => false
            ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function upsertProDetails(Request $req)
    {
        // Validate form
        $rules = [
            'sale_id' => 'required',
            'product_order_id' => 'nullable',
            'product_order_id.*' => 'nullable',
            'product_id' => 'required',
            'product_id.*' => 'required',
            'product_desc' => 'required',
            'product_desc.*' => 'nullable|max:250',
            'qty' => 'required',
            'qty.*' => 'required',
            'uom' => 'required',
            'uom.*' => 'required',
            'unit_price' => 'required',
            'unit_price.*' => 'required',
            'promotion_id' => 'required',
            'promotion_id.*' => 'nullable',
            'product_serial_no' => 'nullable',
            'product_serial_no.*' => 'nullable',
            'warranty_period' => 'required',
            'warranty_period.*' => 'required',
        ];
        $req->validate($rules, [], [
            'product_id.*' => 'product',
            'product_desc.*' => 'product description',
            'qty.*' => 'quantity',
            'uom.*' => 'UOM',
            'unit_price.*' => 'unit price',
            'product_serial_no.*' => 'product serial no',
            'warranty_period.*' => 'warranty period',
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

        try {
            DB::beginTransaction();

            if ($req->product_order_id != null) {
                $order_idx = array_filter($req->product_order_id, function ($val) {
                    return $val != null;
                });

                SaleProduct::where('sale_id', $req->sale_id)->whereNotIn('id', $order_idx ?? [])->delete();
                SaleProductChild::whereNotIn('sale_product_id', $order_idx ?? [])->delete();
            }

            $now = now();
            for ($i = 0; $i < count($req->product_id); $i++) {
                if ($req->product_order_id != null && $req->product_order_id[$i] != null) {
                    $sp = SaleProduct::where('id', $req->product_order_id[$i])->first();
                    
                    $sp->update([
                        'product_id' => $req->product_id[$i],
                        'desc' => $req->product_desc[$i],
                        'qty' => $req->qty[$i],
                        'uom' => $req->uom[$i],
                        'unit_price' => $req->unit_price[$i],
                        'warranty_period_id' => $req->warranty_period[$i],
                        'promotion_id' => $req->promotion_id[$i],
                    ]);
                } else {
                    $sp = SaleProduct::create([
                        'sale_id' => $req->sale_id,
                        'product_id' => $req->product_id[$i],
                        'desc' => $req->product_desc[$i],
                        'qty' => $req->qty[$i],
                        'uom' => $req->uom[$i],
                        'unit_price' => $req->unit_price[$i],
                        'warranty_period_id' => $req->warranty_period[$i],
                        'promotion_id' => $req->promotion_id[$i],
                    ]);
                }

                // Sale product children
                SaleProductChild::where('sale_product_id', $sp->id)->whereNotIn('product_children_id', $req->product_serial_no[$i] ?? [])->delete();
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

            $new_prod_ids = SaleProduct::where('sale_id', $req->sale_id)
                ->pluck('id')
                ->toArray();

            DB::commit();

            return Response::json([
                'result' => true,
                'product_ids' => $new_prod_ids
            ], HttpFoundationResponse::HTTP_OK);
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return Response::json([
                'result' => false
            ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function upsertRemark(Request $req)
    {
        // Validate form
        $rules = [
            'sale_id' => 'required',
            'remark' => 'nullable|max:250',
        ];
        $req->validate($rules);

        try {
            DB::beginTransaction();

            Sale::where('id', $req->sale_id)->update([
                'remark' => $req->type == 'quo' ? $req->remark : json_encode(explode(',', $req->remark)),
            ]);

            DB::commit();

            return Response::json([
                'result' => true
            ], HttpFoundationResponse::HTTP_OK);
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return Response::json([
                'result' => false
            ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function upsertPayDetails(Request $req)
    {
        // Validate form
        $rules = [
            'sale_id' => 'required',
            'payment_term' => 'required',
            'payment_method' => 'required',
            'payment_due_date' => 'required',
            'payment_amount' => 'required',
            'payment_remark' => 'nullable|max:250',
        ];
        $req->validate($rules);

        try {
            DB::beginTransaction();

            Sale::where('id', $req->sale_id)->update([
                'payment_term' => $req->payment_term,
                'payment_method' => $req->payment_method,
                'payment_due_date' => $req->payment_due_date,
                'payment_amount' => $req->payment_amount,
                'payment_remark' => $req->payment_remark,
            ]);

            DB::commit();

            return Response::json([
                'result' => true,
            ], HttpFoundationResponse::HTTP_OK);
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return Response::json([
                'result' => false
            ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function upsertDelSchedule(Request $req)
    {
        // Validate form
        $rules = [
            'sale_id' => 'required',
            'driver' => 'required',
            'delivery_date' => 'required',
            'delivery_time' => 'required',
            'delivery_instruction' => 'required|max:250',
            'delivery_address' => 'nullable',
            'status' => 'required',
        ];
        $req->validate($rules);

        try {
            DB::beginTransaction();

            Sale::where('id', $req->sale_id)->update([
                'driver_id' => $req->driver,
                'delivery_date' => $req->delivery_date,
                'delivery_time' => $req->delivery_time,
                'delivery_instruction' => $req->delivery_instruction,
                'delivery_address_id' => $req->delivery_address,
                'delivery_is_active' => $req->boolean('status'),
            ]);

            DB::commit();

            return Response::json([
                'result' => true,
            ], HttpFoundationResponse::HTTP_OK);
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return Response::json([
                'result' => false
            ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getProducts(Sale $sale)
    {
        return Response::json([
            'products' => $sale->products->load('product'),
        ], HttpFoundationResponse::HTTP_OK);
    }

    public function toProduction(Request $req, Sale $sale)
    {
        return redirect(route('production.create', [
            'sale_id' => $sale->id,
            'product_id' => $req->product
        ]));
    }

    public function indexDeliveryOrder()
    {
        return view('delivery_order.list');
    }

    public function getDataDeliveryOrder(Request $req)
    {
        $records = DeliveryOrder::withCount('products');

        // Search
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];

            $records->where(function ($q) use ($keyword) {
                $q->where('sku', 'like', '%' . $keyword . '%');
            });
        }
        // Order
        if ($req->has('order')) {
            $map = [
                0 => 'sku',
                1 => 'item_count',
            ];
            foreach ($req->order as $order) {
                if ($order['column'] == 1) {
                    $records->orderBy('products_count', $order['dir']);
                } else {
                    $records->orderBy($map[$order['column']], $order['dir']);
                }
            }
        } else {
            $records->orderBy('id', 'desc');
        }

        $records_count = $records->count();
        $records_ids = $records->pluck('id');
        $records_paginator = $records->simplePaginate(10);

        $data = [
            "recordsTotal" => $records_count,
            "recordsFiltered" => $records_count,
            "data" => [],
            'records_ids' => $records_ids,
        ];
        foreach ($records_paginator as $key => $record) {
            $data['data'][] = [
                'id' => $record->id,
                'sku' => $record->sku,
                'item_count' => $record->products()->count(),
                'filename' => $record->filename,
                'status' => $record->status,
            ];
        }

        return response()->json($data);
    }

    public function toInvoice(Request $req)
    {
        $step = 1;

        if ($req->has('term')) {
            $step = 3;

            Session::put('convert_terms', $req->term);

            $delivery_orders = DeliveryOrder::whereNull('invoice_id')->where('customer_id', Session::get('convert_customer_id'))->where('payment_terms', $req->term)->get();
        } else if ($req->has('cus')) {
            $step = 2;

            Session::put('convert_customer_id', $req->cus);

            $term_ids = DeliveryOrder::where('customer_id', $req->cus)
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

    public function convertToInvoice(Request $req)
    {
        $do_ids = explode(',', $req->do);
        $dos = DeliveryOrder::whereIn('id', $do_ids)->get();

        try {
            DB::beginTransaction();

            // Create record
            $sku = (new Invoice)->generateSku();
            $filename = $sku . '.pdf';

            $do_sku = DeliveryOrder::whereIn('id', $do_ids)->pluck('sku')->toArray();

            $do_products = DeliveryOrderProduct::with('saleProduct')->whereIn('delivery_order_id', $do_ids)->get();
            $sale_products = SaleProduct::whereIn('id', DeliveryOrderProduct::whereIn('delivery_order_id', $do_ids)->pluck('sale_product_id'))->get();

            $inv = Invoice::create([
                'sku' => $sku,
                'filename' => $filename,
                'company' => $this->getPdfType($sale_products)
            ]);
            (new Branch)->assign(Invoice::class, $inv->id);

            // Create PDF
            $pdf = Pdf::loadView('delivery_order.' . $this->getPdfType($sale_products) . '_inv_pdf', [
                'date' => now()->format('d/m/Y'),
                'sku' => $sku,
                'do_sku' => join(', ', $do_sku),
                'dos' => $dos,
                'do_products' => $do_products,
                'customer' => Customer::where('id', Session::get('convert_customer_id'))->first(),
                'billing_address' => (new CustomerLocation)->defaultBillingAddress(Session::get('convert_customer_id')),
                'terms' => Session::get('convert_terms'),
            ]);
            $pdf->setPaper('A4', 'letter');
            $content = $pdf->download()->getOriginalContent();
            Storage::put(self::INVOICE_PATH . $filename, $content);

            DeliveryOrder::whereIn('id', $do_ids)->update([
                'invoice_id' => $inv->id
            ]);

            DB::commit();

            return redirect(route('invoice.index'))->with('success', 'Delivery Order has converted');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator');
        }
    }

    public function cancelDeliveryOrder(Request $req) {
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

            for ($i=0; $i < count($sales); $i++) {
                $this->cancelSaleOrderFlow($sales[$i], true);
            }
            DeliveryOrder::whereIn('sku', $do_to_cancel)->update([
                'status' => DeliveryOrder::STATUS_CANCELLED
            ]);

            DB::commit();

            return back()->with('success', 'Delivery Order cancelled');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator');
        }
    }

    private function cancelSaleOrderFlow(Sale $sale, bool $cancel_from_converted, ?float $charge=null) {
        SaleOrderCancellation::calCancellation($sale, $cancel_from_converted ? 3 : 1, null);

        $sp_ids = SaleProduct::where('sale_id', $sale->id)->pluck('id');
        // Delete product/child
        SaleProductChild::where('sale_product_id', $sp_ids)->delete();
        SaleProduct::where('id', $sp_ids)->delete();

        $sale->cancellation_charge = $charge;
        $sale->status = Sale::STATUS_CANCELLED;
        $sale->save();
    }

    public function getCancellationInvolvedDO(Request $req, DeliveryOrder $do) {
        $involved = [];
        $to_search_do_ids = [$do->id];
        $searched_do_ids = [];

        while (true) {
            $do_id_to_search = null;
            if (count($to_search_do_ids) <= 0) {
                break;
            }
            $do_id_to_search = $to_search_do_ids[0];

            $data = $this->getCancellationInvolvedDOFlow($do_id_to_search);
            $searched_do_ids[] = $do_id_to_search;
            
            if (isset($data['do_ids'])) {
                $diff = array_values(array_diff($data['do_ids'], $searched_do_ids));
                $to_search_do_ids = array_merge($to_search_do_ids, $diff);
            }
            if (isset($data['so_skus'])) {
                $do_sku = DeliveryOrder::where('id', $do_id_to_search)->value('sku');
                
                $involved[$do_sku] = $data['so_skus'];
            }

            $to_search_do_ids = array_unique(array_values(array_diff($to_search_do_ids, [$do_id_to_search])));
        }
        
        return Response::json([
            'involved' => $involved,
        ], HttpFoundationResponse::HTTP_OK);
    }

    private function getCancellationInvolvedDOFlow(int $do_id) {
        $so_skus = [];
        $do_ids = [];

        $sales = Sale::where('type', Sale::TYPE_SO)
                ->whereRaw("find_in_set('".$do_id."', convert_to)")
                ->get();

        for ($i=0; $i < count($sales); $i++) {
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
        return view('invoice.list');
    }

    public function getDataInvoice(Request $req)
    {
        $records = Invoice::query();
        // Search
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];
            $records = $records->where('invoices.sku', 'like', '%' . $keyword . '%')
            ->orWhere('invoices.company', 'like', '%' . $keyword . '%');
        }

        // Order
        if ($req->has('order')) {
            $map = [
                0 => 'invoices.sku',
            ];
            foreach ($req->order as $order) {
                $records = $records->orderBy($map[$order['column']], $order['dir']);
            }
        } else {
            $records = $records->orderBy('invoices.id', 'desc');
        }

        $records_count = $records->count();
        $records_paginator = $records->simplePaginate(10);

        $data = [
            "recordsTotal" => $records_count,
            "recordsFiltered" => $records_count,
            "data" => [],
        ];

        foreach ($records_paginator as $record) {
            $convert_to = "-";
            $enable = true;

            $hasPlatformId = $record->deliveryOrders()
            ->whereHas('products.saleProduct.sale', function($query) {
                $query->whereNotNull('platform_id');
            })->exists();

            if ($record->einvoice) {
                $convert_to = "E-Invoice";
            } elseif ($record->consolidatedEInvoices && !$record->consolidatedEInvoices->isEmpty()) {
                $convert_to = "Consolidated E-Invoice";
            }

            $enable = $hasPlatformId;
            
            $data['data'][] = [
                'id' => $record->id,
                'sku' => $record->sku,
                'company' => $record->company,
                'convert_to' => $convert_to,
                'filename' => $record->filename,
                'enable' => $enable,
                'status' => $record->status,
            ];
        }

        return response()->json($data);
    }

    public function indexEInvoice()
    {
        return view('invoice.e-invoice.list');
    }

    public function getDataEInvoice(Request $req)
    {
        $records = new EInvoice();

        // Search
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];

            $records = $records->where(function ($q) use ($keyword) {
                $q->where('uuid', 'like', '%' . $keyword . '%')
                ->orWhere('status', 'like', '%' . $keyword . '%');
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
            "recordsTotal" => $records_count,
            "recordsFiltered" => $records_count,
            "data" => [],
            'records_ids' => $records_ids,
        ];
        foreach ($records_paginator as $key => $record) {
            $data['data'][] = [
                'uuid' => $record->uuid,
                'status' => $record->status,
                'submission_date' => $record->submission_date,
                'id' => $record->id,
                'from' => $record->einvoiceable instanceof Invoice ? 'Customer' : 'Billing'
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
                $q->where('uuid', 'like', '%' . $keyword . '%')
                ->orWhere('status', 'like', '%' . $keyword . '%');
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
            "recordsTotal" => $records_count,
            "recordsFiltered" => $records_count,
            "data" => [],
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
        $records = new CreditNote();

        // Search
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];

            $records = $records->where(function ($q) use ($keyword) {
                $q->where('uuid', 'like', '%' . $keyword . '%')
                ->orWhere('status', 'like', '%' . $keyword . '%');
            });
        }
        // Order
        if ($req->has('order')) {
            $map = [
                0 => 'uuid',
                0 => 'status',
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
            "recordsTotal" => $records_count,
            "recordsFiltered" => $records_count,
            "data" => [],
            'records_ids' => $records_ids,       
        ];
        
        foreach ($records_paginator as $key => $record) {
            $data['data'][] = [       
                'id' => $record->id,
                'uuid' => $record->uuid,
                'status' => $record->status,
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
        $records = new DebitNote();

        // Search
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];

            $records = $records->where(function ($q) use ($keyword) {
                $q->where('uuid', 'like', '%' . $keyword . '%')
                ->orWhere('status', 'like', '%' . $keyword . '%');
            });
        }
        // Order
        if ($req->has('order')) {
            $map = [
                0 => 'uuid',
                0 => 'status',
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
            "recordsTotal" => $records_count,
            "recordsFiltered" => $records_count,
            "data" => [],
            'records_ids' => $records_ids,       
        ];
        
        foreach ($records_paginator as $key => $record) {
            $data['data'][] = [       
                'id' => $record->id,
                'uuid' => $record->uuid,
                'status' => $record->status,
            ];
        }

        return response()->json($data);
    }

    public function cancelInvoice(Request $req) {
        try {
            DB::beginTransaction();

            // Prepare data
            $inv_to_cancel = json_decode($req->involved_inv_skus, true);
            $do_to_cancel = array_unique(json_decode($req->involved_do_skus, true));
            $so_to_cancel = array_unique(json_decode($req->involved_so_skus, true));

            // Cancellation
            $sales = Sale::where('type', Sale::TYPE_SO)->whereIn('sku', $so_to_cancel)->get();

            for ($i=0; $i < count($sales); $i++) {
                $this->cancelSaleOrderFlow($sales[$i], true);
            }
            DeliveryOrder::whereIn('sku', $do_to_cancel)->update([
                'status' => DeliveryOrder::STATUS_CANCELLED
            ]);
            Invoice::whereIn('sku', $inv_to_cancel)->update([
                'status' => Invoice::STATUS_CANCELLED
            ]);

            DB::commit();

            return back()->with('success', 'Invoice cancelled');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator');
        }
    }

    public function getCancellationInvolvedInv(Request $req, Invoice $inv) {
        $involved = [];
        $involved_inv_skus = [];
        $involved_do_skus = [];
        $involved_so_skus = [];
        $to_search_inv_ids = [$inv->id];
        $searched_inv_ids = [];

        while (true) {
            $inv_id_to_search = null;
            if (count($to_search_inv_ids) <= 0) {
                break;
            }
            $inv_id_to_search = $to_search_inv_ids[0];

            $data = $this->getCancellationInvolvedInvFlow($inv_id_to_search);
            $searched_inv_ids[] = $inv_id_to_search;
            // dd($data);
            
            if (isset($data['inv_ids'])) {
                $diff = array_values(array_diff($data['inv_ids'], $searched_inv_ids));
                $to_search_inv_ids = array_merge($to_search_inv_ids, $diff);
            }
            if (isset($data['do_skus'])) {
                $inv_sku = Invoice::where('id', $inv_id_to_search)->value('sku');
                
                $involved[$inv_sku] = array_merge($data['do_skus'], $data['so_skus']);

                $involved_inv_skus[] = $inv_sku;
                $involved_do_skus = array_merge($involved_do_skus, $data['do_skus']);
                $involved_so_skus = array_merge($involved_so_skus, $data['so_skus']);
            }

            $to_search_inv_ids = array_unique(array_values(array_diff($to_search_inv_ids, [$inv_id_to_search])));
        }
        
        return Response::json([
            'involved' => $involved,
            'involved_inv_skus' => $involved_inv_skus,
            'involved_do_skus' => $involved_do_skus,
            'involved_so_skus' => $involved_so_skus,
        ], HttpFoundationResponse::HTTP_OK);
    }

    private function getCancellationInvolvedInvFlow(int $inv_id) {
        $so_skus = [];
        $do_skus = [];
        $inv_ids = [];
        
        $do_ids = DeliveryOrder::where('invoice_id', $inv_id)->pluck('id')->toArray();

        $sales = Sale::where('type', Sale::TYPE_SO);

        $sales = $sales->where(function($q) use ($do_ids) {
            for ($i=0; $i < count($do_ids); $i++) {
                $q->orWhereRaw("find_in_set('".$do_ids[$i]."', convert_to)");
            }
        });
        $sales = $sales->get();

        for ($i=0; $i < count($sales); $i++) {
            $sale_do_ids = null;
            if (str_contains($sales[$i]->convert_to, ',')) {
                $sale_do_ids = explode(',', $sales[$i]->convert_to);
            } else {
                $sale_do_ids = [$sales[$i]->convert_to];
            }

            $so_skus[] = $sales[$i]->sku;
            $do_skus = array_merge($do_skus, DeliveryOrder::whereIn('id', $sale_do_ids)->pluck('sku')->toArray());
            $inv_ids = array_merge($inv_ids, DeliveryOrder::whereIn('id', $sale_do_ids)->whereNotNull('invoice_id')->pluck('invoice_id')->toArray());
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
            return Storage::download(self::DELIVERY_ORDER_PATH . '/' . $req->query('file'));
        } else if ($req->type == 'inv') {
            return Storage::download(self::INVOICE_PATH . '/' . $req->query('file'));
        } else if ($req->type == 'billing') {
            return Storage::download(self::BILLING_PATH . '/' . $req->query('file'));
        }
    }

    public function indexTarget()
    {
        return view('target.list');
    }

    public function getDataTarget(Request $req)
    {
        $records = Target::with('salesperson');

        // Search
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];

            $records = $records->where(function ($q) use ($keyword) {
                $q->where('amount', 'like', '%' . $keyword . '%')
                    ->orWhereHas('salesperson', function ($q) use ($keyword) {
                        return $q->where('name', 'like', '%' . $keyword . '%');
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
            "recordsTotal" => $records_count,
            "recordsFiltered" => $records_count,
            "data" => [],
            'records_ids' => $records_ids,
        ];
        foreach ($records_paginator as $key => $record) {
            $data['data'][] = [
                'id' => $record->id,
                'sales' => $record->salesperson->name,
                'amount' => number_format($record->amount, 2),
                'date' => Carbon::parse($record->date)->format('M Y'),
                'can_create' => hasPermission('sale.target.create'),
                'can_edit' => hasPermission('sale.target.edit')
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

    public function indexBilling() {
        return view('billing.list');
    }

    public function getDataBilling(Request $req)
    {
        $records = new Billing;

        // Search
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];

            $records = $records->where(function ($q) use ($keyword) {
                $q->where('sku', 'like', '%' . $keyword . '%');
            });
        }
        // Order
        if ($req->has('order')) {
            $map = [
                0 => 'sku',
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
            "recordsTotal" => $records_count,
            "recordsFiltered" => $records_count,
            "data" => [],
            'records_ids' => $records_ids,
        ];
        foreach ($records_paginator as $key => $record) {
            $data['data'][] = [
                'id' => $record->id,
                'sku' => $record->sku,
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
            if ($req->sale == null) {
                $errors['sale'] = 'Please select a salesperson';
            }
            if ($req->term == null) {
                $errors['term'] = 'Please select a term';
            }
            if ($req->your_po_no == null) {
                $errors['your_po_no'] = 'Please enter a Your P/O No';
            }
            if ($req->your_so_no == null) {
                $errors['your_so_no'] = 'Please enter a Your S/O No';
            }
            if ($req->our_do_no == null) {
                $errors['our_do_no'] = 'Please enter a Our D/O No';
            }
            if (count($errors) > 0) {
                throw ValidationException::withMessages($errors);
            }

            $step = 3;
            
            Session::put('billing_saleperson', $req->sale);
            Session::put('billing_term', $req->term);
            Session::put('billing_your_po_no', $req->your_po_no);
            Session::put('billing_your_so_no', $req->your_so_no);
            Session::put('billing_our_do_no', $req->our_do_no);

            $inv_ids = Session::get('invoice_ids');
            $do_ids = DeliveryOrder::whereIn('invoice_id', explode(',', $inv_ids))->pluck('id');

            $sale_ids = Sale::where('type', Sale::TYPE_SO);
            
            $sale_ids = $sale_ids->where(function($q) use ($do_ids) {
                for ($i=0; $i < count($do_ids); $i++) {
                    $q->orWhereRaw("find_in_set('".$do_ids[$i]."', convert_to)");
                }
            });
            $sale_ids = $sale_ids->pluck('id');

            $products = SaleProduct::whereIn('sale_id', $sale_ids)->get();
        } else if ($req->has('inv')) {
            $step = 2;

            Session::put('invoice_ids', $req->inv);
        } else {
            $invoices = Invoice::orderBy('id', 'desc')->get();
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
        try {
            DB::beginTransaction();

            $sku = (new Billing)->generateSku();
            $do_filename = $sku . 'DO.pdf';
            $inv_filename = $sku . 'INV.pdf';

            $bill = Billing::create([
                'sku' => $sku,
                'do_filename' => $do_filename,
                'inv_filename' => $inv_filename,
                'term_id' => Session::get('billing_term'),
                'sale_person_id' => Session::get('billing_saleperson'),
                'our_do_no' => Session::get('billing_our_do_no'),
            ]);

            $invoiceIds = explode(',', Session::get('invoice_ids'));
            $bill->invoices()->attach($invoiceIds);

            $saleProducts = $req->input('sale_product_id', []); 
            $pivotData = [];
            foreach ($saleProducts as $saleProductId) {
                $customUnitPrice = $req->input("custom-unit-price-{$saleProductId}", 0);
                $pivotData[$saleProductId] = ['custom_unit_price' => $customUnitPrice];
            }
            $bill->saleProducts()->attach($pivotData);

            (new Branch)->assign(Billing::class, $bill->id);

            $this->generateDeliveryOrderBillingPDF($sku, $do_filename, $req->sale_product_id);
            $this->generateInvoiceBillingPDF($sku, $inv_filename, $req->sale_product_id, $req->all());

            DB::commit();

            return redirect(route('billing.index'))->with('success', 'Billing converted');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);
            dd($th);
            return back()->with('error', 'Something went wrong. Please contact administrator');
        }
    }

    private function generateDeliveryOrderBillingPDF(string $sku, string $filename, array $sale_product_ids) {
        $pdf = Pdf::loadView('billing.do_pdf', [
            'date' => now()->format('d/m/Y'),
            'sku' => $sku,
            'your_po_no' => Session::get('billing_your_po_no'),
            'your_so_no' => Session::get('billing_your_so_no'),
            'term' => CreditTerm::where('id', Session::get('billing_term'))->value('name'),
            'salesperson' => User::where('id', Session::get('billing_saleperson'))->value('name'),
            'products' => SaleProduct::whereIn('id', $sale_product_ids)->get(),
        ]);
        $pdf->setPaper('A4', 'letter');
        $content = $pdf->download()->getOriginalContent();
        Storage::put(self::BILLING_PATH . $filename, $content);
    }

    private function generateInvoiceBillingPDF(string $sku, string $filename, array $sale_product_ids, array $custom_unit_price) {
        $pdf = Pdf::loadView('billing.inv_pdf', [
            'date' => now()->format('d/m/Y'),
            'sku' => $sku,
            'our_do_no' => Session::get('billing_our_do_no'),
            'term' => CreditTerm::where('id', Session::get('billing_term'))->value('name'),
            'salesperson' => User::where('id', Session::get('billing_saleperson'))->value('name'),
            'products' => SaleProduct::whereIn('id', $sale_product_ids)->get(),
            'custom_unit_price' => $custom_unit_price,
        ]);
        $pdf->setPaper('A4', 'letter');
        $content = $pdf->download()->getOriginalContent();
        Storage::put(self::BILLING_PATH . $filename, $content);
    }

    /**
     * Get Sale ids which has no serial number in production
     */
    private function getSaleInProduction(): array
    {
        $pc_in_factory = ProductChild::where('location', ProductChild::LOCATION_FACTORY)->distinct()->pluck('id');
        $spc_in_factory = SaleProductChild::whereIn('product_children_id', $pc_in_factory)->pluck('sale_product_id');
        $sale_ids = SaleProduct::whereIn('id', $spc_in_factory)->pluck('sale_id')->toArray();

        return $sale_ids;
    }

    private function getPdfType(Collection $sale_products): string
    {
        $is_hi_ten = false;

        for ($i = 0; $i < count($sale_products); $i++) {
            $product = $sale_products[$i]->product;

            if ($product->type == Product::TYPE_PRODUCT) {
                $is_hi_ten = true;
                break;
            }
        }
        return $is_hi_ten ? 'hi_ten' : 'powercool';
    }

    public function indexPendingOrder()
    {
        return view('sale_pending.list');
    }

    public function getDataPendingOrder(Request $req)
    {
        $records = Sale::where('type', Sale::TYPE_PENDING);

        // Search
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];

            $records->where(function ($q) use ($keyword) {
                $q->where('sku', 'like', '%' . $keyword . '%')
                    ->orWhereHas('platform', function ($q) use ($keyword) {
                        $q->where('name', 'like', '%' . $keyword . '%');
                    })
                    ->orWhere('reference', 'like', '%' . $keyword . '%')
                    ->orWhere('remark', 'like', '%' . $keyword . '%')
                    ->orWhere('payment_method', 'like', '%' . $keyword . '%')
                    ->orWhere('payment_amount', 'like', '%' . $keyword . '%')
                    ->orWhere('payment_remark', 'like', '%' . $keyword . '%')
                    ->orWhere('delivery_instruction', 'like', '%' . $keyword . '%');
            });
        }
        // Order
        if ($req->has('order')) {
            $map = [
                0 => 'sku',
                1 => 'payment_amount',
                2  => 'platform'
            ];
            foreach ($req->order as $order) {
                $records->orderBy($map[$order['column']], $order['dir']);
            }
        } else {
            $records->orderBy('id', 'desc');
        }

        $records_count = $records->count();
        $records_ids = $records->pluck('id');
        $records_paginator = $records->simplePaginate(10);

        $data = [
            "recordsTotal" => $records_count,
            "recordsFiltered" => $records_count,
            "data" => [],
            'records_ids' => $records_ids,
        ];
        foreach ($records_paginator as $key => $record) {
            $data['data'][] = [
                'id' => $record->id,
                'sku' => $record->sku,
                'total_amount' => $record->payment_amount,
                'status' => $record->status,
                'platform' => $record->platform->name ?? '-',
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
            'sale' => $sale
        ]);
    }

    public function pdfPendingOrder(Sale $sale)
    {
        $pdf = Pdf::loadView('sale_order.' . $this->getPdfType($sale->products) . '_pdf', [
            'date' => now()->format('d/m/Y'),
            'sale' => $sale,
            'products' => $sale->products,
            'saleperson' => $sale->saleperson,
            'customer' => $sale->customer,
            'billing_address' => (new CustomerLocation)->defaultBillingAddress($sale->customer->id),
        ]);
        $pdf->setPaper('A4', 'letter');

        return $pdf->stream();
    }

    
    public function getSalePerson(Request $request){
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
                'type' => Sale::TYPE_SO
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
}
