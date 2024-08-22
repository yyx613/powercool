<?php

namespace App\Http\Controllers;

use App\Models\InventoryCategory;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;
use Illuminate\Support\Facades\Response;

class InventoryController extends Controller
{
    protected $prod;
    protected $invCat;

    public function __construct() {
        $this->prod = new Product;
        $this->invCat = new InventoryCategory;
    }

    public function index() {
        return view('inventory_category.list');
    }

    public function getData(Request $req) {
        $records = $this->invCat;

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
                $records = $records->orderBy($map[$order['column']], $order['dir']);
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
                'name' => $record->name,
                'status' => $record->is_active,
            ];
        }

        return response()->json($data);
    }

    public function create() {
        return view('inventory_category.form');
    }

    public function edit(InventoryCategory $cat) {
        return view('inventory_category.form', [
            'cat' => $cat,
        ]);
    }

    public function upsert(Request $req) {
        // Validate request
        $req->validate([
            'category_id' => 'nullable',
            'name' => 'required|max:250',
            'status' => 'required',
        ]);

        try {
            DB::beginTransaction();

            if ($req->category_id == null) {
                $cat = $this->invCat::create([
                    'name' => $req->name,
                    'is_active' => $req->boolean('status'),
                ]);
            } else {
                $cat = $this->invCat->where('id', $req->category_id)->first();

                $cat->update([
                    'name' => $req->name,
                    'is_active' => $req->boolean('status'),
                ]);
            }

            DB::commit();

            return Response::json([
                'result' => true,
                'category' => $cat,
            ], HttpFoundationResponse::HTTP_OK);
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return Response::json([
                'result' => false
            ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function delete(InventoryCategory $cat) {
        $cat->delete();

        return back()->with('success', 'Category deleted');
    }

    public function indexSummary() {
        // Low stock
        $products = $this->prod->with('image')->where('type', Product::TYPE_PRODUCT)->get();
        $raw_materials = $this->prod->with('image')->where('type', Product::TYPE_RAW_MATERIAL)->get();
        // Summary
        $active_product_count = $this->prod->where('is_active', true)->count();
        $inactive_product_count = $this->prod->where('is_active', false)->count();
        // Category
        $categories = $this->invCat->get();
        for ($i=0; $i < count($categories); $i++) { 
            $category['label'][] = $categories[$i]->name;
            $category['data'][] = $this->prod->where('inventory_category_id', $categories[$i]->id)->count();
        }
        // Stock summary
        $total_stock = 0;
        $reserved_stock = 0;
        $production_stock = 0;
        for ($i=0; $i < count($products); $i++) { 
            $total_stock += $products[$i]->totalStockCount($products[$i]->id);
            $reserved_stock += $products[$i]->reservedStockCount($products[$i]->id);
            $production_stock += $products[$i]->productionStockCount($products[$i]->id);
        }
        for ($i=0; $i < count($raw_materials); $i++) { 
            $total_stock += $raw_materials[$i]->totalStockCount($raw_materials[$i]->id);
            $reserved_stock += $raw_materials[$i]->reservedStockCount($raw_materials[$i]->id);
            $production_stock += $raw_materials[$i]->productionStockCount($raw_materials[$i]->id);
        }
        return view('inventory.summary', [
            'total_stock' => $total_stock,
            'reserved_stock' => $reserved_stock,
            'production_stock' => $production_stock,
            'products' => $products,
            'raw_materials' => $raw_materials,
            'active_product_count' => $active_product_count,
            'inactive_product_count' => $inactive_product_count,
            'categories' => $category,
        ]);
    }
}
