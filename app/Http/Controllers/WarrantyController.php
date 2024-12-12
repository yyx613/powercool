<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductChild;
use App\Models\Sale;
use App\Models\SaleProduct;
use App\Models\SaleProductChild;
use App\Models\Task;
use App\Models\TaskMilestone;
use App\Models\TaskMilestoneInventory;
use App\Models\Warranty;
use Illuminate\Http\Request;

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
        $so_ids = $this->so::where('type', Sale::TYPE_SO)->pluck('id'); 

        $records = $this->sp::whereIn('sale_id', $so_ids);

        // Search
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];

            $records = $records->where(function($q) use ($keyword) {
                $q->whereHas('sale', function($query) use ($keyword) {
                    $query->where('sku', 'like', '%'.$keyword.'%');
                })
                ->orWhereHas('product', function($query) use ($keyword) {
                    $query->where('sku', 'like', '%'.$keyword.'%');
                });
            });
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
                'sale_order_id' => $record->sale->id,
                'sale_order_sku' => $record->sale->sku,
                'product' => $record->product()->withTrashed()->first(),
                'warranty' => $record->warrantyPeriod == null ? null : $record->warrantyPeriod->name,
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
        $task_ms_ids = $this->taskMs::where('task_id', $task_ids)->pluck('id');
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
