<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\MaterialUse;
use App\Models\MaterialUseProduct;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;
use Illuminate\Support\Facades\Response;

class MaterialUseController extends Controller
{
    protected $mu;
    protected $mup;

    public function __construct() {
        $this->mu = new MaterialUse;
        $this->mup = new MaterialUseProduct;
    }

    public function index() {
        return view('material_use.list');
    }

    public function getData(Request $req) {
        $records = $this->mu;

        // Search
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];

            $records = $records->where(function($q) use ($keyword) {
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
                'id' => $record->id,
                'product' => $record->product()->withTrashed()->first()->model_name,
                'avg_cost' => number_format($record->avgCost(), 2),
            ];
        }
                
        return response()->json($data);
    }

    public function create() {
        return view('material_use.form');
    }

    public function edit(MaterialUse $material) {
        $material->load('materials');

        return view('material_use.form', [
            'material' => $material
        ]);
    }

    public function delete(MaterialUse $material) {
        $material->delete();

        return back()->with('success', 'Material deleted');
    }

    public function upsert(Request $req) {
        // Validate request
        $req->validate([
            'material_use_id' => 'nullable',
            'order_idx' => 'nullable',
            'product' => 'required',
            'material' => 'required',
            'material.*' => 'required',
            'qty' => 'required',
            'qty.*' => 'required',
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
        if ( count(array_unique($req->material)) != count($req->material) ) {
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
                $order_idx = array_filter($req->order_idx, function($val) { return $val != null; });
                $this->mup::where('material_use_id', $mu->id)->whereNotIn('id', $order_idx)->delete();
            }
            for ($i=0; $i < count($req->material); $i++) { 
                if ($req->order_idx != null && $req->order_idx[$i] != null) {
                    $mup = $this->mup::where('id', $req->order_idx[$i])->first();

                    $mup->update([
                        'product_id' => $req->material[$i],
                        'qty' => $req->qty[$i],
                    ]);
                } else {
                    $mup = $this->mup::create([
                        'material_use_id' => $mu->id,
                        'product_id' => $req->material[$i],
                        'qty' => $req->qty[$i],
                    ]);
                }
            }
            
            $new_mup_ids = $this->mup::where('material_use_id', $mu->id)
                ->pluck('id')
                ->toArray();

            DB::commit();

            return Response::json([
                'result' => true,
                'material' => $mu, 
                'material_use_ids' => $new_mup_ids
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
