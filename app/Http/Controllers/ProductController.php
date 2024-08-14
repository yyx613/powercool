<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\InventoryCategory;
use App\Models\Product;
use App\Models\ProductChild;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    protected $prod;
    protected $prodChild;
    protected $invCat;

    public function __construct() {
        $this->prod = new Product;
        $this->prodChild = new ProductChild;
        $this->invCat = new InventoryCategory();
    }

    public function index() {
        return view('inventory.list');
    }

    public function getData(Request $req) {
        $records = $this->prod->with('category');

        if ($req->boolean('is_product') == true) {
            $records = $records->where('type', Product::TYPE_PRODUCT);
        } else {
            $records = $records->where('type', Product::TYPE_RAW_MATERIAL);
        }

        // Search
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];

            $records = $records->where(function($q) use ($keyword) {
                $q->where('sku', 'like', '%' . $keyword . '%')
                    ->orWhere('model_name', 'like', '%' . $keyword . '%')
                    ->orWhere('model_desc', 'like', '%' . $keyword . '%')
                    ->orWhere('price', 'like', '%' . $keyword . '%')
                    ->orWhereHas('category', function($qq) use ($keyword) {
                        $qq->where('name', 'like', '%'.$keyword.'%');
                    });
            });
        }
        // Order
        if ($req->has('order')) {
            $map = [
                0 => 'sku',
                1 => 'model_name',
                2 => 'category',
                3 => 'qty',
                4 => 'price',
            ];
            foreach ($req->order as $order) {
                if ($order['column'] == 2) {
                    $records = $records->orderBy($this->invCat::select('name')->whereColumn('inventory_categories.id', 'products.inventory_category_id'), $order['dir']);
                } else {
                    $records = $records->orderBy($map[$order['column']], $order['dir']);
                }
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
                'sku' => $record->sku,
                'image' => $record->image,
                'model_name' => $record->model_name,
                'category' => $record->category->name,
                'qty' => $record->qty,
                'price' => $record->price,
                'is_sparepart' => $record->is_sparepart,
                'status' => $record->is_active,
            ];
        }

        return response()->json($data);
    }

    public function create() {
        return view('inventory.form');
    }

    public function edit(Product $product) {
        $product->load('image', 'children');

        return view('inventory.form', [
            'prod' => $product,
        ]);
    }

    public function view(Product $product) {
        $product->load('image');

        return view('inventory.view', [
            'prod' => $product,
            'total_stock_count' => $this->prod->totalStockCount($product->id),
            'reserved_stock_count' => $this->prod->reservedStockCount($product->id),
        ]);
    }

    public function viewGetData(Request $req) {
        $records = $this->prodChild::where('product_id', $req->product_id);

        // Search
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];

            $records = $records->where(function($q) use ($keyword) {
                $q->where('sku', 'like', '%' . $keyword . '%')
                    ->orWhere('location', 'like', '%' . $keyword . '%');
            });
        }
        // Order
        if ($req->has('order')) {
            $map = [
                0 => 'sku',
                1 => 'location',
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
                'sku' => $record->sku,
                'location' => $record->location,
                'order_id' => $record->assignedTo(),
            ];
        }

        return response()->json($data);
    }

    public function upsert(Request $req) {
        $rules = [
            'product_id' => 'nullable',
            'model_name' => 'required|max:250',
            'model_desc' => 'required|max:250',
            'category_id' => 'required',
            'supplier_id' => 'required',
            'qty' => 'required',
            'price' => 'required',
            'weight' => 'nullable',
            'dimension_length' => 'nullable',
            'dimension_width' => 'nullable',
            'dimension_height' => 'nullable',
            'status' => 'required',
            'is_sparepart' => 'required',
            'image' => 'required',
            'image.*' => 'file|mimes:jpg,png,jpeg',
        ];
        if ($req->product_id != null) {
            $rules['image'] = 'nullable';            
        }
        if ($req->boolean('is_product') == true) {
            $rules['supplier_id'] = 'nullable';
            $rules['is_sparepart'] = 'nullable';
        }
        // Validate request
        $req->validate($rules, [], [
            'model_desc' => 'model description',
            'category_id' => 'category'
        ]);

        try {
            DB::beginTransaction();

            if ($req->product_id == null) {
                $prod = $this->prod::create([
                    'sku' => $this->prod->generateSku(),
                    'type' => $req->boolean('is_product') == true ? Product::TYPE_PRODUCT : Product::TYPE_RAW_MATERIAL,
                    'model_name' => $req->model_name,
                    'model_desc' => $req->model_desc,
                    'inventory_category_id' => $req->category_id,
                    'supplier_id' => $req->supplier_id,
                    'qty' => $req->qty,
                    'price' => $req->price,
                    'weight' => $req->weight,
                    'length' => $req->dimension_length,
                    'width' => $req->dimension_width,
                    'height' => $req->dimension_height,
                    'is_active' => $req->boolean('status'),
                    'is_sparepart' => $req->is_sparepart == null ? null : $req->boolean('is_sparepart'),
                ]);
            } else {
                $prod = $this->prod->where('id', $req->product_id)->first();

                $prod->update([
                    'model_name' => $req->model_name,
                    'model_desc' => $req->model_desc,
                    'inventory_category_id' => $req->category_id,
                    'supplier_id' => $req->supplier_id,
                    'qty' => $req->qty,
                    'price' => $req->price,
                    'weight' => $req->weight,
                    'length' => $req->dimension_length,
                    'width' => $req->dimension_width,
                    'height' => $req->dimension_height,
                    'is_active' => $req->boolean('status'),
                    'is_sparepart' => $req->is_sparepart == null ? null : $req->boolean('is_sparepart'),
                ]);
            }

            if ($req->hasFile('image')) {
                if ($req->product_id != null) {
                    Attachment::where([
                        'object_type' => Product::class,
                        'object_id' => $prod->id,
                    ])->delete();
                }

                foreach ($req->file('image') as $key => $file) {
                    $path = Storage::putFile(Attachment::PRODUCT_PATH, $file);
                    Attachment::create([
                        'object_type' => Product::class,
                        'object_id' => $prod->id,
                        'src' => basename($path),
                    ]);
                }
            }

            $data = [];
            $existing_skus = [];
            for ($i=0; $i < $req->qty; $i++) { 
                $sku = ($this->prodChild)->generateSku($prod->sku, $existing_skus);
                $data[] = [
                    'product_id' => $prod->id,
                    'sku' => $sku,
                    'location' => $this->prodChild::LOCATION_WAREHOUSE,
                    'created_at' => $prod->created_at,
                    'updated_at' => $prod->updated_at,
                ];
                $existing_skus[] = $sku;
            }
            $this->prodChild->insert($data);

            DB::commit();

            return Response::json([
                'result' => true,
                'product' => $prod,
            ], HttpFoundationResponse::HTTP_OK);
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return Response::json([
                'result' => false
            ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function upsertSerialNo(Request $req) {
        $rules = [
            'product_id' => 'required',
            'order_idx' => 'nullable',
            'serial_no' => 'required',
            'serial_no.*' => 'required|max:250',
        ];
        // Validate request
        $req->validate($rules);

        $prod = $this->prod::where('id', $req->product_id)->first();
        if ($prod->qty != count($req->serial_no)) {
            return Response::json([
                'serial_no' => 'Please make sure the qty of serial no and product qty is tally',
            ], HttpFoundationResponse::HTTP_BAD_REQUEST);
        }

        try {
            DB::beginTransaction();

            $this->prodChild::where('product_id', $req->product_id)->whereNotIn('id', $req->order_idx ?? [])->delete();
            
            $now = now();
            $data = [];
            for ($i=0; $i < count($req->serial_no); $i++) { 
                if ($req->order_idx != null && $req->order_idx[$i] != null) {
                    $pc = $this->prodChild::where('id', $req->order_idx[$i])->first();

                    $pc->update([
                        'sku' => $req->serial_no[$i],
                    ]);
                } else {
                    $data[] = [
                        'product_id' => $req->product_id,
                        'sku' => $req->serial_no[$i],
                        'location' => $this->prodChild::LOCATION_WAREHOUSE,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
            if (count($data) > 0) {
                $this->prodChild->insert($data);
            }

            $pc_ids = $this->prodChild::where('product_id', $req->product_id)
                ->orderBy('id', 'desc')
                ->pluck('id')
                ->toArray();

            DB::commit();

            return Response::json([
                'result' => true,
                'product_children_ids' => $pc_ids
            ], HttpFoundationResponse::HTTP_OK);
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return Response::json([
                'result' => false
            ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function delete(Product $product) {
        $product->delete();

        return back()->with('success', 'Product deleted');
    }
}
