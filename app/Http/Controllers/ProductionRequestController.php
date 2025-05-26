<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\ProductionRequest;
use App\Models\ProductionRequestMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class ProductionRequestController extends Controller
{
    public function index()
    {
        return view('production_request.list');
    }

    public function getData(Request $req)
    {
        $records = ProductionRequest::orderBy('id', 'desc');
        // Search
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];
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
                'qty_to_collect' => $record->materials->count(),
                'qty_collected' => $record->completedMaterials()->count(),
                'status' => $record->status,
                'remark' => $record->remark,
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

        dd($req->all());
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
                'id' => $record->id,
                'product_name' => $record->material->model_name ?? null,
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

    public function materialComplete(ProductionRequestMaterial $pqm)
    {
        $pqm->status = ProductionRequestMaterial::STATUS_COMPLETED;
        $pqm->save();

        return back()->with('success', 'Request completed');
    }

    public function materialIncomplete(ProductionRequestMaterial $pqm)
    {
        $pqm->status = ProductionRequestMaterial::STATUS_IN_PROGRESS;
        $pqm->save();

        return back()->with('success', 'Request incompleted');
    }
}
