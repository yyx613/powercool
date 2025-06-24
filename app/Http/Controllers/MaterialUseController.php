<?php

namespace App\Http\Controllers;

use App\Models\Approval;
use App\Models\Branch;
use App\Models\MaterialUse;
use App\Models\MaterialUseProduct;
use App\Models\Product;
use App\Models\SaleProductionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;
use Illuminate\Support\Facades\Response;

class MaterialUseController extends Controller
{
    protected $mu;
    protected $mup;

    public function __construct()
    {
        $this->mu = new MaterialUse;
        $this->mup = new MaterialUseProduct;
    }

    public function index()
    {
        return view('material_use.list');
    }

    public function getData(Request $req)
    {
        $records = $this->mu::with('approval');

        // Search
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];

            $records = $records->where(function ($q) use ($keyword) {
                $q->where('name', 'like', '%' . $keyword . '%');
            });
        }
        // Order
        if ($req->has('order')) {
            $map = [
                0 => 'name',
            ];
            foreach ($req->order as $order) {
                $records = $records->orderBy(Product::select('model_name')->whereColumn('material_uses.product_id', 'products.id'), $order['dir']);
            }
        } else {
            $records = $records->orderBy('id', 'desc');
        }

        $records_paginator = $records->simplePaginate(10);

        $data = [
            "data" => [],
        ];
        foreach ($records_paginator as $key => $record) {
            if ($record->approval != null && $record->approval->status == Approval::STATUS_PENDING_APPROVAL) {
                continue;
            }
            $data['data'][] = [
                'id' => $record->id,
                'product' => $record->product()->withTrashed()->first(),
                'avg_cost' => number_format($record->avgCost(), 2),
            ];
        }
        $data["recordsTotal"] = count($data['data']);
        $data["recordsFiltered"] = count($data['data']);
        $data["records_ids"] = count($data['data']);

        return response()->json($data);
    }

    public function create(Request $req)
    {
        if ($req->has('sprid') && $req->has('to_pid')) {
            $spr = SaleProductionRequest::where('id', $req->sprid)->first();
            $material_use = MaterialUse::with('materials')->where('product_id', $req->to_pid)->first();
        }
        return view('material_use.form', [
            'material' => $material_use ?? null,
            'for_pid' => isset($spr) ? $spr->product_id : null,
            'spr_id' => isset($spr) ? $spr->id : null,
        ]);
    }

    public function edit(MaterialUse $material)
    {
        $material->load('materials');

        return view('material_use.form', [
            'material' => $material
        ]);
    }

    public function delete(MaterialUse $material)
    {
        $material->delete();

        return back()->with('success', 'Material deleted');
    }

    public function upsert(Request $req)
    {
        // Validate request
        $req->validate([
            'spr_id' => 'nullable',
            'material_use_id' => 'nullable',
            'order_idx' => 'nullable',
            'product' => 'required',
            'material' => 'required',
            'material.*' => 'required',
            'qty' => 'required',
            'qty.*' => 'required',
            'active' => 'required',
            'active.*' => 'required',
        ], [], [
            'material.*' => 'material',
            'qty.*' => 'quantity',
        ]);
        // No duplicate product is allow
        if ($this->mu::where('product_id', $req->product)->whereNot('id', $req->material_use_id)->exists()) {
            return Response::json([
                'product' => 'product is already exists',
            ], HttpFoundationResponse::HTTP_BAD_REQUEST);
        }
        // No duplicate material is allow
        if (count(array_unique($req->material)) != count($req->material)) {
            return Response::json([
                'material' => 'no duplicate materials are allowed',
            ], HttpFoundationResponse::HTTP_BAD_REQUEST);
        }

        try {
            DB::beginTransaction();

            if ($req->material_use_id == null) {
                $mu = $this->mu::create([
                    'product_id' => $req->product,
                ]);
                (new Branch)->assign(MaterialUse::class, $mu->id);
            } else {
                $mu = $this->mu::where('id', $req->material_use_id)->first();

                $mu->update([
                    'product_id' => $req->product,
                ]);
            }
            // Materials
            if ($req->order_idx != null) {
                $order_idx = array_filter($req->order_idx, function ($val) {
                    return $val != null;
                });
                $this->mup::where('material_use_id', $mu->id)->whereNotIn('id', $order_idx)->delete();
            }
            for ($i = 0; $i < count($req->material); $i++) {
                if ($req->order_idx != null && $req->order_idx[$i] != null) {
                    $mup = $this->mup::where('id', $req->order_idx[$i])->first();

                    $mup->update([
                        'product_id' => $req->material[$i],
                        'qty' => $req->qty[$i],
                        'status' => $req->active[$i] == 'true' ? null : MaterialUseProduct::STATUS_DISABLED,
                    ]);
                } else {
                    $mup = $this->mup::create([
                        'material_use_id' => $mu->id,
                        'product_id' => $req->material[$i],
                        'qty' => $req->qty[$i],
                        'status' => $req->active[$i] == 'true' ? null : MaterialUseProduct::STATUS_DISABLED,
                    ]);
                }
            }
            // Approval if from sale production request
            if ($req->spr_id != null) {
                $has_approval = Approval::where('object_type', MaterialUse::class)->where('object_id', $mu->id)->exists();

                $spr = SaleProductionRequest::where('id', $req->spr_id)->first();

                if (!$has_approval) {
                    $approval = Approval::create([
                        'object_type' => MaterialUse::class,
                        'object_id' => $mu->id,
                        'status' => Approval::STATUS_PENDING_APPROVAL,
                        'data' => json_encode([
                            'description' => Auth::user()->name . ' has requested to create B.O.M for (' . $spr->product->sku . ') ' . $spr->product->model_name,
                            'user_id' => Auth::user()->id,
                            'sale_production_request_id' => $spr->id,
                        ])
                    ]);
                    (new Branch)->assign(Approval::class, $approval->id);
                }
            }

            $new_mup_ids = $this->mup::where('material_use_id', $mu->id)
                ->pluck('id')
                ->toArray();

            DB::commit();

            return Response::json([
                'result' => true,
                'material' => $mu,
                'material_use_ids' => $new_mup_ids,
                'redirect_to' => isset($spr) ? route('product.edit', ['product' => $spr->product_id]) : null
            ], HttpFoundationResponse::HTTP_OK);
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return Response::json([
                'result' => false
            ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
