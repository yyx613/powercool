<?php

namespace App\Http\Controllers;

use App\Models\DeliveryOrder;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\ProductChild;
use App\Models\Sale;
use App\Models\SaleProduct;
use App\Models\SaleProductChild;
use App\Models\Task;
use App\Models\TaskMilestone;
use App\Models\TaskMilestoneInventory;
use App\Models\Warranty;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WarrantyController extends Controller
{
    protected $so;
    protected $sp;
    protected $spc;
    protected $product;
    protected $task;
    protected $taskMs;
    protected $taskMsInventory;
    
    public function __construct(Sale $sale, SaleProduct $sale_product, SaleProductChild $sale_product_child, Product $product, Task $task, TaskMilestone $taskMs, TaskMilestoneInventory $taskMsInventory) {
        $this->so = $sale;
        $this->sp = $sale_product;
        $this->spc = $sale_product_child;
        $this->product = $product;
        $this->task = $task;
        $this->taskMs = $taskMs;
        $this->taskMsInventory = $taskMsInventory;
    }

    public function index() {
        return view('warranty.list');
    }

    public function getData(Request $req) {
        $cus = DB::table('customers')->select('id', 'name');
        $prods = DB::table('products')->select('id', 'model_name');
        $wars = DB::table('warranty_periods')->select('id', 'name', 'period');

        $sps = DB::table('sale_products')
            ->select('sale_products.id', 'wars.name', 'wars.period')
            ->leftJoinSub($wars, 'wars', function ($join) {
                $join->on('sale_products.warranty_period_id', '=', 'wars.id');
            });

        $sos = DB::table('sales')
            ->select('sales.id', 'cus.name AS customer_name')
            ->leftJoinSub($cus, 'cus', function ($join) {
                $join->on('sales.customer_id', '=', 'cus.id');
            });

        $invs = DB::table('invoices')
            ->select('id AS inv_id', 'sku AS inv_sku', 'created_at');
        
        $dos = DB::table('delivery_orders')
            ->select('delivery_orders.id AS do_id', 'inv.inv_id AS inv_id', 'inv.inv_sku AS inv_sku', 'inv.created_at AS inv_created_at')
            ->whereNotNull('invoice_id')
            ->leftJoinSub($invs, 'inv', function ($join) {
                $join->on('delivery_orders.invoice_id', '=', 'inv.inv_id');
            });

        $dops = DB::table('delivery_order_products')
            ->select(
                'delivery_order_products.id AS dop_id',
                'dos.do_id AS do_id', 'dos.inv_id', 'dos.inv_sku AS inv_sku', 'dos.inv_created_at',
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
                'dops.inv_id AS inv_id', 'dops.inv_sku AS inv_sku', 'dops.so_id', 'dops.customer_name',
                'dops.warranty', 'dops.warranty_period', 'dops.inv_created_at AS inv_created_at'
            )
            ->joinSub($dops, 'dops', function ($join) {
                $join->on('delivery_order_product_children.delivery_order_product_id', '=', 'dops.dop_id');
            });
            
        $records = DB::table('product_children')
            ->select(
                'product_children.sku AS serial_no', 
                'dopcs.inv_id AS inv_id', 'dopcs.inv_sku AS inv_sku', 'dopcs.so_id', 'dopcs.customer_name AS customer_name', 
                'dopcs.warranty', 'dopcs.warranty_period', 'dopcs.warranty_period', 'dopcs.inv_created_at',
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

            $records = $records->where(function($q) use ($keyword) {
                $q->where('product_children.sku', 'like', '%'.$keyword.'%')
                    ->orWhere('dopcs.inv_sku', 'like', '%'.$keyword.'%')
                    ->orWhere('dopcs.customer_name', 'like', '%'.$keyword.'%')
                    ->orWhere('dopcs.warranty', 'like', '%'.$keyword.'%')
                    ->orWhere('prods.model_name', 'like', '%'.$keyword.'%');
            });
        }
        // Order
        $records = $records->orderBy('inv_id', 'desc');

        $records_count = $records->count();
        $records_ids = $records->pluck('inv_id');
        $records_paginator = $records->simplePaginate(10);

        $data = [
            "recordsTotal" => $records_count,
            "recordsFiltered" => $records_count,
            "data" => [],
            'records_ids' => $records_ids,
        ];
        foreach ($records_paginator as $key => $record) {
            $data['data'][] = [
                'sale_order_id' => $record->so_id,
                'invoice_sku' => $record->inv_sku,
                'customer_name' => $record->customer_name,
                'product_name' => $record->product_name,
                'serial_no' => $record->serial_no,
                'warranty' => $record->warranty,
                'warranty_date' => Carbon::parse($record->inv_created_at)->addMonths($record->warranty_period)->format('d M Y h:i A')
            ];
        }
                
        return response()->json($data);
    }

    public function view(Sale $sale) {
        return view('warranty.view', [
            'sale' => $sale
        ]);
    }

    public function viewGetData(Request $req) {
        if ($req->sale_id == null) {
            abort(404);
        }

        $task_ids = $this->task::where('sale_order_id', $req->sale_id)->pluck('id'); 
        $task_ms_ids = $this->taskMs::whereIn('task_id', $task_ids)->pluck('id');
        $records = $this->taskMsInventory::whereIn('task_milestone_id', $task_ms_ids);

        // Search
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];

            $records = $records->whereHasMorph(
                'inventory',
                [Product::class, ProductChild::class],
                function($q) use ($keyword) {
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
            "recordsTotal" => $records_count,
            "recordsFiltered" => $records_count,
            "data" => [],
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

}
