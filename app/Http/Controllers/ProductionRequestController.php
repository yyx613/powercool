<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\MaterialUse;
use App\Models\Product;
use App\Models\ProductionRequest;
use App\Models\ProductionRequestMaterial;
use App\Models\Sale;
use App\Models\SaleProduct;
use App\Models\SaleProductionRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class ProductionRequestController extends Controller
{
    public function index()
    {
        $page = Session::get('production-request-page');
        $sale_page = Session::get('sale-production-request-page');

        return view('production_request.list', [
            'default_page' => $page ?? null,
            'default_sale_page' => $sale_page ?? null,
        ]);
    }

    public function getData(Request $req)
    {
        Session::put('production-request-page', $req->page);

        $totalRequested = DB::table('production_request_materials')
            ->select(
                'production_request_materials.production_request_id',
                DB::raw('COUNT(production_request_materials.production_request_id) AS totalRequested'),
            )
            ->groupBy('production_request_materials.production_request_id');


        $fulfilled = DB::table('production_request_materials')
            ->select(
                'production_request_materials.production_request_id',
                DB::raw('COUNT(production_request_materials.production_request_id) AS fulfilled'),
            )
            ->where('status', ProductionRequest::STATUS_COMPLETED)
            ->groupBy('production_request_materials.production_request_id');

        $qty = DB::table('production_request_materials')
            ->select(
                'production_request_materials.production_request_id',
                'totalRequested.totalRequested AS totalRequestedQty',
                DB::raw('IFNULL(fulfilled.fulfilled, 0) AS fulfilledQty'),
                DB::raw('IFNULL(SUM(totalRequested.totalRequested - fulfilled.fulfilled), totalRequested.totalRequested) AS balanceQty'),
            )
            ->leftJoinSub($totalRequested, 'totalRequested', function ($join) {
                $join->on('totalRequested.production_request_id', '=', 'production_request_materials.production_request_id');
            })
            ->leftJoinSub($fulfilled, 'fulfilled', function ($join) {
                $join->on('fulfilled.production_request_id', '=', 'production_request_materials.production_request_id');
            })
            ->groupBy('production_request_materials.production_request_id');


        $records = new ProductionRequest;

        $records = $records
            ->select(
                'production_requests.*',
                'qty.totalRequestedQty',
                'qty.balanceQty',
                'qty.fulfilledQty',
                'users.name AS requestedBy'
            )
            ->leftJoin('users', 'users.id', '=', 'production_requests.requested_by')
            ->leftJoinSub($qty, 'qty', function ($join) {
                $join->on('qty.production_request_id', '=', 'production_requests.id');
            });

        // Search
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];

            $records = $records->where(function ($q) use ($keyword) {
                $q->where('name', 'like', '%' . $keyword . '%')
                    ->orWhere('sku', 'like', '%' . $keyword . '%');
            });
        }
        // Order
        if ($req->has('order')) {
            $map = [
                1 => 'production_requests.created_at',
                2 => 'qty.totalRequestedQty',  
                3 => 'qty.balanceQty',
                4 => 'qty.fulfilledQty',
                5 => 'users.name',
                6 => 'production_requests.status',
            ];
            foreach ($req->order as $order) {
                $records = $records->orderBy($map[$order['column']], $order['dir']);
            }
        } else {
            $records = $records->orderBy('production_requests.id', 'desc');
        }
        $records = $records->groupBy('production_requests.id');

        $records_count = $records->count();
        $records_ids = $records->pluck('production_requests.id');
        $records_paginator = $records->simplePaginate(10);

        $data = [
            'recordsTotal' => $records_count,
            'recordsFiltered' => $records_count,
            'data' => [],
            'records_ids' => $records_ids,
        ];
        foreach ($records_paginator as $key => $record) {
            $data['data'][] = [
                'no' => $key + 1,
                'id' => $record->id,
                'date' => Carbon::parse($record->created_at)->format('d M Y H:i'),
                'total_request_qty' => $record->totalRequestedQty,
                'balance_qty' => $record->balanceQty,
                'fulfilled_qty' => $record->fulfilledQty ?? 0,
                'requested_by' => $record->requestedBy ?? null,
                'status' => $record->status,
                'remark' => $record->remark,
            ];
        }

        return response()->json($data);
    }

    public function getDataSaleProductionRequest(Request $req)
    {
        $records = new SaleProductionRequest;

        $records = $records
            ->select(
                'sale_production_requests.*', 'products.sku AS productSku', 'sales.sku AS soSku',
                'productions.sku AS productionSku', 'sale_production_requests.remark'
            )
            ->leftJoin('products', 'products.id', '=', 'sale_production_requests.product_id')
            ->leftJoin('productions', 'productions.id', '=', 'sale_production_requests.production_id')
            ->leftJoin('sales', 'sales.id', '=', 'sale_production_requests.sale_id');

        // Search
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];
        }

        Session::put('sale-production-request-page', $req->page);

        // Search
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];

            $records = $records->where(function ($q) use ($keyword) {
                $q->where('name', 'like', '%' . $keyword . '%')
                    ->orWhere('sku', 'like', '%' . $keyword . '%');
            });
        }
        // Order
        if ($req->has('order')) {
            $map = [
                1 => 'created_at',
                2 => 'sales.sku',
                3 => 'products.sku',
                4 => 'productions.sku',
                6 => 'status',
            ];
            foreach ($req->order as $order) {
                $records = $records->orderBy($map[$order['column']], $order['dir']);
            }
        } else {
            $records = $records->orderBy('sale_production_requests.id', 'desc');
        }

        $records_count = $records->count();
        $records_ids = $records->pluck('sale_production_requests.id');
        $records_paginator = $records->simplePaginate(10);

        $data = [
            'recordsTotal' => $records_count,
            'recordsFiltered' => $records_count,
            'data' => [],
            'records_ids' => $records_ids,
        ];
        foreach ($records_paginator as $key => $record) {
            if ($record->remark == null) {
                $remark = SaleProduct::where('sale_id', $record->sale_id)->where('product_id', $record->product_id)->value('remark');
            } else {
                $remark = $record->remark;
            }

            $data['data'][] = [
                'no' => $key + 1,
                'id' => $record->id,
                'date' => Carbon::parse($record->created_at)->format('d M Y H:i'),
                'so_no' => $record->soSku ?? null,
                'product' => $record->productSku ?? null,
                'production' => $record->productionSku ?? null,
                'remark' => $remark,
                'status' => $record->status ?? null,
                'sale_id' => $record->sale_id,
                'product_id' => $record->product_id,
                'has_material_use' => MaterialUse::where('product_id', $record->product_id)->exists(),
            ];
        }

        return response()->json($data);
    }

    public function create()
    {
        return view('production_request.form');
    }

    public function store(Request $req)
    {
        $rules = [
            'product' => 'required',
            'qty' => 'required',
            'remark' => 'nullable|max:250'
        ];
        // Validate request
        $req->validate($rules, [],);

        try {
            DB::beginTransaction();

            $pq = ProductionRequest::create([
                'status' => ProductionRequest::STATUS_IN_PROGRESS,
                'remark' => $req->remark ?? null
            ]);
            (new Branch)->assign(ProductionRequest::class, $pq->id);

            $data = [];
            for ($i = 0; $i < $req->qty; $i++) {
                $data[] = [
                    'production_request_id' => $pq->id,
                    'product_id' => $req->product,
                    'status' => ProductionRequestMaterial::STATUS_IN_PROGRESS,
                    'created_at' => now(),
                ];
            }
            ProductionRequestMaterial::insert($data);

            DB::commit();

            return redirect(route('production_request.index'))->with('success', 'Request created');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator')->withInput();
        }
    }

    public function view(ProductionRequest $pq)
    {
        Session::put('pq_id', $pq->id);
        return view('production_request.view');
    }

    public function viewGetData(Request $req)
    {
        $records = ProductionRequestMaterial::orderBy('id', 'desc');

        $records = $records->where('production_request_id', Session::get('pq_id'));

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
                'no' => $key + 1,
                'id' => $record->id,
                'product_name' => $record->material->model_name ?? null,
                'production_sku' => $record->production->sku ?? null,
                'total_request_qty' => 1,
                'balance_qty' => $record->status == ProductionRequestMaterial::STATUS_COMPLETED ? 0 : 1,
                'fulfilled_qty' => $record->status == ProductionRequestMaterial::STATUS_COMPLETED ? 1 : 0,
                'requested_by' => $record->requestedBy->name ?? null,
                'status' => $record->status,
            ];
        }

        return response()->json($data);
    }

    public function complete(ProductionRequest $pq)
    {
        $pq->status = ProductionRequest::STATUS_COMPLETED;
        $pq->save();

        return back()->with('success', 'Request completed');
    }

    public function materialComplete(Request $req, ProductionRequestMaterial $pqm)
    {
        if ($req->production_id == 'undefined') {
            return back()->with('warning', 'Please select a production');
        }
        $pqm->status = ProductionRequestMaterial::STATUS_COMPLETED;
        $pqm->production_id = $req->production_id ?? null;
        $pqm->save();

        return back()->with('success', 'Request completed');
    }

    public function materialIncomplete(ProductionRequestMaterial $pqm)
    {
        $pqm->status = ProductionRequestMaterial::STATUS_IN_PROGRESS;
        $pqm->save();

        return back()->with('success', 'Request incompleted');
    }

    public function toProduction(Sale $sale, Product $product)
    {
        return redirect(route('production.create', [
            'sale_id' => $sale->id,
            'product_id' => $product->id,
        ]));
    }

    public function toMaterialUse(SaleProductionRequest $sale_production_request, Product $product)
    {
        return redirect(route('material_use.create', [
            'sprid' => $sale_production_request->id,
            'to_pid' => $product->id
        ]));
    }
}
