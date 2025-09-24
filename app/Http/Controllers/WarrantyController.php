<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Product;
use App\Models\ProductChild;
use App\Models\Sale;
use App\Models\SaleProduct;
use App\Models\SaleProductChild;
use App\Models\Task;
use App\Models\TaskMilestone;
use App\Models\TaskMilestoneInventory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class WarrantyController extends Controller
{
    protected $so;

    protected $sp;

    protected $spc;

    protected $product;

    protected $task;

    protected $taskMs;

    protected $taskMsInventory;

    public function __construct(Sale $sale, SaleProduct $sale_product, SaleProductChild $sale_product_child, Product $product, Task $task, TaskMilestone $taskMs, TaskMilestoneInventory $taskMsInventory)
    {
        $this->so = $sale;
        $this->sp = $sale_product;
        $this->spc = $sale_product_child;
        $this->product = $product;
        $this->task = $task;
        $this->taskMs = $taskMs;
        $this->taskMsInventory = $taskMsInventory;
    }

    public function index()
    {
        $page = Session::get('warranty-page');

        return view('warranty.list', [
            'default_page' => $page ?? null,
        ]);
    }

    public function getData(Request $req)
    {
        Session::put('warranty-page', $req->page);

        $cus = DB::table('customers')->select('id', 'name');
        $prods = DB::table('products')->select('id', 'model_name');

        $sps = DB::table('sale_products')
            ->select('sale_products.id', 'warranty_periods.name', 'warranty_periods.period')
            ->leftJoin('sale_product_warranty_periods', 'sale_product_warranty_periods.sale_product_id', 'sale_products.id')
            ->leftJoin('warranty_periods', 'warranty_periods.id', 'sale_product_warranty_periods.warranty_period_id');

        $sos = DB::table('sales')
            ->select('sales.id', 'cus.name AS customer_name')
            ->leftJoinSub($cus, 'cus', function ($join) {
                $join->on('sales.customer_id', '=', 'cus.id');
            });

        $invs = DB::table('invoices')
            ->select('id AS inv_id', 'sku AS inv_sku', 'status AS inv_status', 'created_at')
            ->whereNull('deleted_at');

        $dos = DB::table('delivery_orders')
            ->select('delivery_orders.id AS do_id', 'inv.inv_id AS inv_id', 'inv.inv_sku AS inv_sku', 'inv.inv_status AS inv_status', 'inv.created_at AS inv_created_at')
            ->whereNotNull('invoice_id')
            ->leftJoinSub($invs, 'inv', function ($join) {
                $join->on('delivery_orders.invoice_id', '=', 'inv.inv_id');
            });

        $dops = DB::table('delivery_order_products')
            ->select(
                'delivery_order_products.id AS dop_id',
                'dos.do_id AS do_id', 'dos.inv_id', 'dos.inv_sku AS inv_sku', 'dos.inv_status AS inv_status', 'dos.inv_created_at',
                'sos.id AS so_id', 'sos.customer_name',
                'sps.name AS warranty', 'sps.period AS warranty_period',
            )
            ->joinSub($dos, 'dos', function ($join) {
                $join->on('delivery_order_products.delivery_order_id', '=', 'dos.do_id');
            })
            ->joinSub($sos, 'sos', function ($join) {
                $join->on('delivery_order_products.sale_order_id', '=', 'sos.id');
            })
            ->joinSub($sps, 'sps', function ($join) {
                $join->on('delivery_order_products.sale_product_id', '=', 'sps.id');
            });

        $dopcs = DB::table('delivery_order_product_children')
            ->select(
                'delivery_order_product_children.product_children_id AS product_child_id',
                'dops.inv_id AS inv_id', 'dops.inv_sku AS inv_sku', 'dops.inv_status AS inv_status', 'dops.so_id', 'dops.customer_name',
                'dops.warranty', 'dops.warranty_period', 'dops.inv_created_at AS inv_created_at'
            )
            ->joinSub($dops, 'dops', function ($join) {
                $join->on('delivery_order_product_children.delivery_order_product_id', '=', 'dops.dop_id');
            });

        $records = DB::table('product_children')
            ->select(
                'product_children.id AS pc_id', 'product_children.sku AS serial_no',
                'dopcs.inv_id AS inv_id', 'dopcs.inv_sku AS inv_sku', 'dopcs.inv_status AS inv_status',
                'dopcs.so_id', 'dopcs.customer_name AS customer_name',
                'dopcs.warranty', 'dopcs.warranty_period', 'dopcs.inv_created_at',
                'prods.model_name AS product_name',
            )
            ->joinSub($dopcs, 'dopcs', function ($join) {
                $join->on('product_children.id', '=', 'dopcs.product_child_id');
            })
            ->joinSub($prods, 'prods', function ($join) {
                $join->on('product_children.product_id', '=', 'prods.id');
            });

        // Search
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];

            $records = $records->where(function ($q) use ($keyword) {
                $q->where('product_children.sku', 'like', '%'.$keyword.'%')
                    ->orWhere('dopcs.inv_sku', 'like', '%'.$keyword.'%')
                    ->orWhere('dopcs.customer_name', 'like', '%'.$keyword.'%')
                    ->orWhere('dopcs.warranty', 'like', '%'.$keyword.'%')
                    ->orWhere('prods.model_name', 'like', '%'.$keyword.'%');
            });
        }
        // Order
        if ($req->has('order')) {
            $map = [
                0 => 'dopcs.inv_sku',
                1 => 'dopcs.customer_name',
                2 => 'prods.model_name',
                3 => 'product_children.sku',
                4 => 'dopcs.warranty',
            ];
            foreach ($req->order as $order) {
                $records = $records->orderBy($map[$order['column']], $order['dir']);
            }
        } else {
            $records = $records->orderBy('inv_id', 'desc');
        }

        $records_count = $records->count();
        $records_ids = $records->pluck('inv_id');
        $records_paginator = $records->simplePaginate(10);

        $data = [
            'recordsTotal' => $records_count,
            'recordsFiltered' => $records_count,
            'data' => [],
            'records_ids' => $records_ids,
        ];
        foreach ($records_paginator as $key => $record) {
            $data['data'][] = [
                'sale_order_id' => $record->so_id,
                'invoice_sku' => $record->inv_sku,
                'pc_id' => $record->pc_id,
                'is_voided' => $record->inv_status == Invoice::STATUS_VOIDED,
                'customer_name' => $record->customer_name,
                'product_name' => $record->product_name,
                'serial_no' => $record->serial_no,
                'warranty' => $record->warranty,
                'warranty_date' => Carbon::parse($record->inv_created_at)->addMonths($record->warranty_period)->format('d M Y h:i A'),
            ];
        }

        return response()->json($data);
    }

    public function view(Sale $sale, ProductChild $pc)
    {
        Session::put('warranty-create-sale-id', $sale->id);
        Session::put('warranty-create-pc-id', $pc->id);

        return view('warranty.view', [
            'sale' => $sale,
        ]);
    }

    public function viewGetData(Request $req)
    {
        if ($req->sale_id == null) {
            abort(404);
        }

        $task_ids = $this->task::where('sale_order_id', $req->sale_id)->pluck('id');
        $task_ms_ids = $this->taskMs::whereIn('task_id', $task_ids)->pluck('id');
        $records = $this->taskMsInventory::whereIn('task_milestone_id', $task_ms_ids)
            ->orWhere(function($q) {
                $q->where('pc_id', Session::get('warranty-create-pc-id'));
            });

        // Search
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];

            $records = $records->whereHasMorph(
                'inventory',
                [Product::class, ProductChild::class],
                function ($q) use ($keyword) {
                    $q->where('sku', 'like', '%'.$keyword.'%');
                }
            );
        }
        // Order
        $records = $records->orderBy('id', 'desc');

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
                'product' => $record->inventory->sku,
                'qty' => $record->qty,
            ];
        }

        return response()->json($data);
    }

    public function create()
    {
        $pcs = ProductChild::get();

        return view('warranty.form', [
            'pcs' => $pcs,
            'back_url' => route('warranty.view', [
                'sale' => Session::get('warranty-create-sale-id'),
                'pc' => Session::get('warranty-create-pc-id'),
            ]),
        ]);
    }

    public function store(Request $req)
    {
        $req->validate([
            'serial_no' => 'required',
            'qty' => 'required',
        ], [], [
            'qty' => 'quantity',
        ]);

        TaskMilestoneInventory::create([
            'inventory_type' => ProductChild::class,
            'inventory_id' => $req->serial_no,
            'qty' => $req->qty,
            'pc_id' => Session::get('warranty-create-pc-id'),
        ]);

        return redirect(route('warranty.view', [
            'sale' => Session::get('warranty-create-sale-id'),
            'pc' => Session::get('warranty-create-pc-id'),
        ]))->with('success', 'Material used created');
    }
}
