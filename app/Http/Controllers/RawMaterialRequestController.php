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
        $page = Session::get('raw-material-request-page');

        return view('raw_material_request.list', [
            'default_page' => $page ?? null,
        ]);
    }

    public function getData(Request $req)
    {
        Session::put('raw-material-request-page', $req->page);

        $totalRequested = DB::table('raw_material_request_materials')
            ->select(
                'raw_material_request_materials.raw_material_request_id',
                DB::raw('COUNT(raw_material_request_materials.qty) AS totalRequested'),
            )
            ->groupBy('raw_material_request_materials.raw_material_request_id');

        $fulfilled = DB::table('raw_material_request_materials')
            ->select(
                'raw_material_request_materials.raw_material_request_id',
                DB::raw('COUNT(raw_material_request_materials.qty) AS fulfilled'),
            )
            ->where('status', RawMaterialRequest::STATUS_COMPLETED)
            ->groupBy('raw_material_request_materials.raw_material_request_id');

        $qty = DB::table('raw_material_request_materials')
            ->select(
                'raw_material_request_materials.raw_material_request_id',
                'totalRequested.totalRequested AS totalRequestedQty',
                DB::raw('IFNULL(fulfilled.fulfilled, 0) AS fulfilledQty'),
                DB::raw('IFNULL(totalRequested.totalRequested - fulfilled.fulfilled, totalRequested.totalRequested) AS balanceQty'),
            )
            ->leftJoinSub($totalRequested, 'totalRequested', function ($join) {
                $join->on('totalRequested.raw_material_request_id', '=', 'raw_material_request_materials.raw_material_request_id');
            })
            ->leftJoinSub($fulfilled, 'fulfilled', function ($join) {
                $join->on('fulfilled.raw_material_request_id', '=', 'raw_material_request_materials.raw_material_request_id');
            })
            ->groupBy('raw_material_request_materials.raw_material_request_id');


        $records = new RawMaterialRequest;

        $records = $records->select(
                'raw_material_requests.*',
                'qty.totalRequestedQty',
                'qty.balanceQty',
                'qty.fulfilledQty',
                'users.name AS requestedBy',
                'productions.sku AS productionSku'
            )
            ->leftJoin('users', 'users.id', '=', 'raw_material_requests.requested_by')
            ->leftJoin('productions', 'productions.id', '=', 'raw_material_requests.production_id')
            ->leftJoinSub($qty, 'qty', function ($join) {
                $join->on('qty.raw_material_request_id', '=', 'raw_material_requests.id');
            });

        // Search
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];

            $records = $records->where(function ($q) use ($keyword) {
                $q->whereHas('production', function ($q) use ($keyword) {
                    $q->where('sku', 'like', '%' . $keyword . '%');
                });
            });
        }
        // Order
        if ($req->has('order')) {
            $map = [
                1 => 'raw_material_requests.created_at',
                2 => 'productions.sku',  
                3 => 'qty.totalRequestedQty',  
                4 => 'qty.balanceQty',
                5 => 'qty.fulfilledQty',
                6 => 'users.name',
                7 => 'raw_material_requests.status',
            ];
            foreach ($req->order as $order) {
                $records = $records->orderBy($map[$order['column']], $order['dir']);
            }
        } else {
            $records = $records->orderBy('raw_material_requests.id', 'desc');
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
                'no' => $key + 1,
                'id' => $record->id,
                'date' => Carbon::parse($record->created_at)->format('d M Y H:i'),
                'production_id' => $record->productionSku ?? null,
                'total_request_qty' => $record->totalRequestedQty,
                'balance_qty' => $record->balanceQty,
                'fulfilled_qty' => $record->fulfilledQty ?? 0,
                'requested_by' => $record->requestedBy ?? null,
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

    public function viewLogs(RawMaterialRequestMaterial $rmqm)
    {
        Session::put('rmq-log-rmqm-id', $rmqm->id);
        return view('raw_material_request.log', [
            'rmq' => $rmqm->materialRequest,
            'rmqm' => $rmqm
        ]);
    }

    public function viewLogsGetData(Request $req)
    {
        $records = RawMaterialRequestMaterialCollected::orderBy('id', 'desc');

        $id = Session::get('rmq-log-rmqm-id');
        if ($id != null) {
            $records = $records->where('raw_material_request_material_id', $id);
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
                'no' => $key + 1,
                'qty' => $record->qty,
                'by' => $record->loggedBy->name ?? null,
                'date' => Carbon::parse($record->created_at)->format('d M Y H:i'),
            ];
        }

        return response()->json($data);
    }
}
