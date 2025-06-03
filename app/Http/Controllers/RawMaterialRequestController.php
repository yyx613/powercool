<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Product;
use App\Models\Production;
use App\Models\RawMaterialRequest;
use App\Models\RawMaterialRequestMaterial;
use App\Models\RawMaterialRequestMaterialCollected;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            $total_request_qty = $record->materials->count();
            $fulfilled_qty = $record->completedMaterials()->count();

            $data['data'][] = [
                'no' => $key + 1,
                'id' => $record->id,
                'date' => Carbon::parse($record->created_at)->format('d M Y H:i'),
                'production_id' => $record->production->sku ?? null,
                'total_request_qty' => $total_request_qty,
                'balance_qty' => $total_request_qty - $fulfilled_qty,
                'fulfilled_qty' => $fulfilled_qty,
                'requested_by' => $record->requestedBy->name ?? null,
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
            'material' => 'required',
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
                'remark' => $req->remark ?? null,
                'requested_by' => Auth::user()->id,
            ]);
            (new Branch)->assign(RawMaterialRequest::class, $rmq->id);

            for ($i = 0; $i < count($req->material); $i++) {
                $product = Product::where('id', $req->material[$i])->first();

                if ($product->is_sparepart == true) {
                    $data = [];
                    for ($j = 0; $j < $req->qty[$i]; $j++) {
                        $data[] = [
                            'raw_material_request_id' => $rmq->id,
                            'product_id' => $req->material[$i],
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
                        'product_id' => $req->material[$i],
                        'status' => RawMaterialRequestMaterial::MATERIAL_STATUS_IN_PROGRESS,
                        'qty' => $req->qty[$i],
                    ]);
                }
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
            $qty_collected = 0;
            if (!$record->material->is_sparepart) {
                $qty_collected = RawMaterialRequestMaterialCollected::where('raw_material_request_material_id', $record->id)->sum('qty');
            } else if ($record->status == RawMaterialRequestMaterial::MATERIAL_STATUS_COMPLETED) {
                $qty_collected = 1;
            }

            $data['data'][] = [
                'no' => $key + 1,
                'id' => $record->id,
                'product_name' => $record->material->model_name ?? null,
                'total_request_qty' => $record->qty,
                'balance_qty' => $record->qty - $qty_collected,
                'fulfilled_qty' => $qty_collected,
                'requested_by' => $record->requestedBy->name ?? null,
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
            $collected_qty = RawMaterialRequestMaterialCollected::where('raw_material_request_material_id', $rmqm->id)->sum('qty');
            $remaining_qty = ($rmqm->qty - $collected_qty ?? 0);

            if ($req->qty <= 0) {
                return back()->with('warning', 'The quantity must be greater than 0');
            } else if ($req->qty > $remaining_qty) {
                return back()->with('warning', 'The quantity must be greater than ' . $remaining_qty);
            }

            RawMaterialRequestMaterialCollected::create([
                'raw_material_request_material_id' => $rmqm->id,
                'qty' => $req->qty,
                'logged_by' => Auth::user()->id,
            ]);
            $collected_qty = RawMaterialRequestMaterialCollected::where('raw_material_request_material_id', $rmqm->id)->sum('qty');

            if ($rmqm->qty - ($collected_qty ?? 0) <= 0) {
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

    public function viewLogs(RawMaterialRequest $rmq)
    {
        return view('raw_material_request.log', [
            'rmq' => $rmq
        ]);
    }

    public function viewLogsGetData(Request $req)
    {
        $records = RawMaterialRequestMaterialCollected::orderBy('id', 'desc');

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
                'qty' => $record->qty,
                'by' => $record->loggedBy->name ?? null,
                'date' => Carbon::parse($record->created_at)->format('d M Y H:i'),
            ];
        }

        return response()->json($data);
    }
}
