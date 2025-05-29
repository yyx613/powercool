<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Product;
use App\Models\Production;
use App\Models\RawMaterialRequest;
use App\Models\RawMaterialRequestMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
                'sku' => $record->production->sku ?? null,
                'qty_to_collect' => RawMaterialRequestMaterial::where('raw_material_request_id', $record->id)->value(DB::raw('SUM(qty - COALESCE(qty_collected, 0) )')),
                'qty_collected' => $record->completedMaterials()->count(),
                'status' => $record->status,
            ];
        }

        return response()->json($data);
    }

    public function create()
    {
        return view('raw_material_request.form');
    }

    public function store(Request $req)
    {
        $rules = [
            'production_id' => 'nullable',
            'product' => 'required',
            'qty' => 'required',
            'remark' => 'nullable|max:250'
        ];
        // Validate request
        $req->validate($rules);

        try {
            DB::beginTransaction();

            $product = Product::where('id', $req->product)->first();

            $rmq = RawMaterialRequest::create([
                'status' => RawMaterialRequest::STATUS_IN_PROGRESS,
                'remark' => $req->remark ?? null
            ]);
            (new Branch)->assign(RawMaterialRequest::class, $rmq->id);

            if ($product->is_sparepart == true) {
                $data = [];
                for ($i = 0; $i < $req->qty; $i++) {
                    $data[] = [
                        'raw_material_request_id' => $rmq->id,
                        'product_id' => $req->product,
                        'status' => RawMaterialRequestMaterial::MATERIAL_STATUS_IN_PROGRESS,
                        'qty' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                RawMaterialRequestMaterial::insert($data);
            } else {
                RawMaterialRequestMaterial::create([
                    'raw_material_request_id' => $rmq->id,
                    'product_id' => $req->product,
                    'status' => RawMaterialRequestMaterial::MATERIAL_STATUS_IN_PROGRESS,
                    'qty' => $req->qty,
                ]);
            }

            DB::commit();

            return redirect(route('raw_material_request.index'))->with('success', 'Request created');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator')->withInput();
        }
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
                'qty' => $record->qty - ($record->qty_collected ?? 0),
                'status' => $record->status,
                'is_sparepart' => $record->material->is_sparepart,
                'parent_completed' => $record->materialRequest->status == RawMaterialRequest::STATUS_COMPLETED
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

    public function materialComplete(Request $req, RawMaterialRequestMaterial $rmqm)
    {
        if ($req->has('qty')) {
            $remaining_qty = ($rmqm->qty - $rmqm->qty_collected ?? 0);

            if ($req->qty <= 0) {
                return back()->with('warning', 'The quantity must be greater than 0');
            } else if ($req->qty > $remaining_qty) {
                return back()->with('warning', 'The quantity must be greater than ' . $remaining_qty);
            }

            $rmqm->qty_collected += $req->qty;
            if ($rmqm->qty - ($rmqm->qty_collected ?? 0) <= 0) {
                $rmqm->status = RawMaterialRequestMaterial::MATERIAL_STATUS_COMPLETED;
            }
            $rmqm->save();

            if ($rmqm->qty <= 0) {
                return back()->with('success', 'Request completed');
            } else {
                return back()->with('success', 'Request updated');
            }
        } else {
            $rmqm->status = RawMaterialRequestMaterial::MATERIAL_STATUS_COMPLETED;
            $rmqm->save();

            return back()->with('success', 'Request completed');
        }
    }

    public function materialIncomplete(RawMaterialRequestMaterial $rmqm)
    {
        $rmqm->status = RawMaterialRequestMaterial::MATERIAL_STATUS_IN_PROGRESS;
        $rmqm->save();

        return back()->with('success', 'Request incompleted');
    }
}
