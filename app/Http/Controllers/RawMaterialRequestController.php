<?php

namespace App\Http\Controllers;

use App\Models\Production;
use App\Models\RawMaterialRequest;
use App\Models\RawMaterialRequestMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class RawMaterialRequestController extends Controller
{
    public function index()
    {
        return view('raw_material_request.list');
    }

    public function getData(Request $req)
    {
        $records = RawMaterialRequest::orderBy('id', 'desc');
        // Search
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];

            $records = $records->where(function ($q) use ($keyword) {
                $q->whereHas('production', function ($q) use ($keyword) {
                    $q->where('sku', 'like', '%' . $keyword . '%');
                });
            });
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
                'sku' => $record->production->sku,
                'qty_to_collect' => $record->materials->count(),
                'qty_collected' => $record->completedMaterials()->count(),
                'status' => $record->status,
            ];
        }

        return response()->json($data);
    }

    public function view(RawMaterialRequest $rmq)
    {
        Session::put('rmq_id', $rmq->id);
        return view('raw_material_request.view');
    }

    public function viewGetData(Request $req)
    {
        $records = RawMaterialRequestMaterial::orderBy('id', 'desc');

        $records = $records->where('raw_material_request_id', Session::get('rmq_id'));

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

    public function complete(RawMaterialRequest $rmq)
    {
        $rmq->status = RawMaterialRequest::STATUS_COMPLETED;
        $rmq->save();

        return back()->with('success', 'Request completed');
    }

    public function materialComplete(RawMaterialRequestMaterial $rmqm)
    {
        $rmqm->status = RawMaterialRequestMaterial::MATERIAL_STATUS_COMPLETED;
        $rmqm->save();

        return back()->with('success', 'Request completed');
    }

    public function materialIncomplete(RawMaterialRequestMaterial $rmqm)
    {
        $rmqm->status = RawMaterialRequestMaterial::MATERIAL_STATUS_IN_PROGRESS;
        $rmqm->save();

        return back()->with('success', 'Request incompleted');
    }
}
