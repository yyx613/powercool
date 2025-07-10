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
        $records = ProductionRequest::orderBy('id', 'desc');
        // Search
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];
        }

        Session::put('production-request-page', $req->page);

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
            $total_request_qty = $record->materials->count();
            $fulfilled_qty = $record->completedMaterials()->count();

            $data['data'][] = [
                'no' => $key + 1,
                'id' => $record->id,
                'date' => Carbon::parse($record->created_at)->format('d M Y H:i'),
                'total_request_qty' => $total_request_qty,
                'balance_qty' => $total_request_qty - $fulfilled_qty,
                'fulfilled_qty' => $fulfilled_qty,
                'requested_by' => $record->requestedBy->name ?? null,
                'status' => $record->status,
                'remark' => $record->remark,
            ];
        }

        return response()->json($data);
    }

    public function getDataSaleProductionRequest(Request $req)
    {
        $records = SaleProductionRequest::orderBy('id', 'desc');
        // Search
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];
        }

        Session::put('sale-production-request-page', $req->page);

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
            $remark = SaleProduct::where('sale_id', $record->sale_id)->where('product_id', $record->product_id)->value('remark');

            $data['data'][] = [
                'no' => $key + 1,
                'id' => $record->id,
                'date' => Carbon::parse($record->created_at)->format('d M Y H:i'),
                'so_no' => $record->sale->sku ?? null,
                'product' => $record->product->sku ?? null,
                'production' => $record->production->sku ?? null,
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
