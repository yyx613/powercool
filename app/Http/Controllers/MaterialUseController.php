<?php

namespace App\Http\Controllers;

use App\Models\Approval;
use App\Models\Branch;
use App\Models\CustomizeProduct;
use App\Models\MaterialUse;
use App\Models\MaterialUseProduct;
use App\Models\Product;
use App\Models\SaleProductionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;

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
        $page = Session::get('material-use-page');

        return view('material_use.list', [
            'default_page' => $page ?? null,
        ]);
    }

    public function getData(Request $req)
    {
        $records = $this->mu::with('approval')
            ->select('material_uses.*', 'products.model_name AS productName', 'products.sku AS productSku', 'customize_products.sku AS customizeProductSku')
            ->leftJoin('products', 'products.id', '=', 'material_uses.product_id')
            ->leftJoin('customize_products', 'customize_products.id', '=', 'material_uses.customize_product_id');

        Session::put('material-use-page', $req->page);

        // Search
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];

            $records = $records->where(function ($q) use ($keyword) {
                $q->where('products.model_name', 'like', '%' . $keyword . '%')
                    ->orWhere('products.sku', 'like', '%' . $keyword . '%');
            });
        }
        // Order
        if ($req->has('order')) {
            foreach ($req->order as $order) {
                if ($order['column'] == 0) {
                    $records = $records->orderBy('products.model_name', $order['dir']);
                }
            }
        } else {
            $records = $records->orderBy('material_uses.id', 'desc');
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
                'product' => $record->product_id != null ? '('.$record->productSku.') ' . $record->productName : '('.$record->customizeProductSku.') Customize Product',
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

            if ($material_use != null) {
                $product_ids = [$material_use->product_id, ...$material_use->materials->pluck('product_id')->toArray()];
            } else {
                $product_ids = [$req->to_pid];
            }
            $product_and_materials = Product::whereIn('id', $product_ids)->get()->keyBy('id');
        }
        return view('material_use.form', [
            'material' => $material_use ?? null,
            'for_pid' => isset($spr) ? $spr->product_id : null,
            'spr_id' => isset($spr) ? $spr->id : null,
            'product_and_materials' => $product_and_materials ?? null,
        ]);
    }

    public function edit(MaterialUse $material)
    {
        $material->load('materials');

        $product_ids = $material->materials->pluck('product_id')->toArray(); // Materials
        if ($material->product_id != null) {
            $product_ids = [...$product_ids, $material->product_id];
        } 
        $product_and_materials = Product::whereIn('id', $product_ids)->get()->keyBy('id');
        if ($material->customize_product_id != null) {
            $customize_product = CustomizeProduct::where('id', $material->customize_product_id)->first();
        }

        return view('material_use.form', [
            'material' => $material,
            'product_and_materials' => $product_and_materials,
            'customize_product' => $customize_product ?? null,
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
        $duplicate = false;
        if ($req->type === 'customize-product') {
            $this->mu::where('customize_product_id', $req->product)->whereNot('id', $req->material_use_id)->exists();
        } else {
            $this->mu::where('product_id', $req->product)->whereNot('id', $req->material_use_id)->exists();
        }
        if ($duplicate) {
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

            if ($req->material_use_id == null) { // Create only for product, customize product can only be updated
                $mu = $this->mu::create([
                    'product_id' => $req->product,
                ]);
                (new Branch)->assign(MaterialUse::class, $mu->id);
            } else {
                $mu = $this->mu::where('id', $req->material_use_id)->first();
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

    public function searchProduct(Request $req)
    {
        $keyword = $req->keyword;
        $type = $req->type;

        if ($type == 'product') {
            $products = Product::where('type', Product::TYPE_PRODUCT)
                ->where(function ($q) use ($keyword) {
                    $q->where('model_name', 'like', '%' . $keyword . '%')
                        ->orWhere('sku', 'like', '%' . $keyword . '%');
                })
                ->orderBy('id', 'desc')->get();
        } else if ($type == 'raw_material') {
            $materials = Product::where('type', Product::TYPE_RAW_MATERIAL)
                ->where(function ($q) use ($keyword) {
                    $q->where('model_name', 'like', '%' . $keyword . '%')
                        ->orWhere('sku', 'like', '%' . $keyword . '%');
                })
                ->orderBy('id', 'desc')->get();
        }

        return Response::json([
            'products' => $products ?? null,
            'materials' => $materials ?? null,
        ], HttpFoundationResponse::HTTP_OK);
    }
}
