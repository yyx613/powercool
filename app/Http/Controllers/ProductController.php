<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Branch;
use App\Models\InventoryCategory;
use App\Models\Product;
use App\Models\ProductChild;
use App\Models\ProductCost;
use App\Models\Production;
use App\Models\ProductionMilestoneMaterial;
use App\Models\TaskMilestoneInventory;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Picqer\Barcode\Renderers\DynamicHtmlRenderer;
use Picqer\Barcode\Renderers\HtmlRenderer;
use Picqer\Barcode\Types\TypeCode128;

class ProductController extends Controller
{
    protected $prod;
    protected $prodChild;
    protected $invCat;
    protected $production;
    protected $productionMsMaterial;
    protected $prodCost;
    protected $taskMsInventory;

    public function __construct()
    {
        $this->prod = new Product();
        $this->prodChild = new ProductChild();
        $this->invCat = new InventoryCategory();
        $this->production = new Production();
        $this->productionMsMaterial = new ProductionMilestoneMaterial();
        $this->prodCost = new ProductCost();
        $this->taskMsInventory = new TaskMilestoneInventory();
    }

    public function index()
    {
        return view('inventory.list');
    }

    public function getData(Request $req)
    {
        $records = $this->prod->with('category');

        if ($req->boolean('is_product') == true) {
            $records = $records->where('type', Product::TYPE_PRODUCT);
        } else {
            $records = $records->where('type', Product::TYPE_RAW_MATERIAL);
        }

        // Search
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];

            $records = $records->where(function ($q) use ($keyword) {
                $q->where('sku', 'like', '%' . $keyword . '%')
                    ->orWhere('model_name', 'like', '%' . $keyword . '%')
                    ->orWhere('model_desc', 'like', '%' . $keyword . '%')
                    ->orWhere('min_price', 'like', '%' . $keyword . '%')
                    ->orWhere('max_price', 'like', '%' . $keyword . '%')
                    ->orWhere('barcode', 'like', '%' . $keyword . '%')
                    ->orWhereHas('category', function ($qq) use ($keyword) {
                        $qq->where('name', 'like', '%' . $keyword . '%');
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
                'image' => $record->image ?? null,
                'model_name' => $record->model_name,
                'category' => $record->category->name,
                'qty' => $record->qty,
                'min_price' => number_format($record->min_price, 2),
                'max_price' => number_format($record->max_price, 2),
                'is_sparepart' => $record->is_sparepart,
                'status' => $record->is_active,
                'can_edit' => $req->boolean('is_product') ? hasPermission('inventory.product.edit') : hasPermission('inventory.raw_material.edit'),
                'can_delete' => $req->boolean('is_product') ? hasPermission('inventory.product.delete') : hasPermission('inventory.raw_material.delete'),
            ];
        }

        return response()->json($data);
    }

    public function create(Request $req)
    {
        if ($req->has('id')) {
            $dup_prod = Product::where('id', $req->id)->first();
        }

        return view('inventory.form', [
            'dup_prod' => $dup_prod ?? null
        ]);
    }

    public function edit(Product $product)
    {
        $product->load('image', 'children');

        return view('inventory.form', [
            'prod' => $product,
        ]);
    }

    public function view(Product $product)
    {
        $product->load('image');

        return view('inventory.view', [
            'prod' => $product,
            'warehouse_available_stock' => $product->warehouseAvailableStock(),
            'warehouse_reserved_stock' => $product->warehouseReservedStock(),
            'warehouse_on_hold_stock' => $product->warehouseOnHoldStock(),
            'production_stock' => $product->productionStock(),
            'production_reserved_stock' => $product->productionReservedStock(),
        ]);
    }

    public function viewGetData(Request $req) {
        $records = $this->prodChild::where('product_id', $req->product_id);

        // Search
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];

            $records = $records->where(function ($q) use ($keyword) {
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
            if ($record->location == 'factory') {
                $production = $this->production->where('product_child_id', $record->id)->first();
            }
            $data['data'][] = [
                'id' => $record->id,
                'sku' => $record->sku,
                'location' => $record->location,
                'order_id' => $record->assignedTo(),
                'status' => $record->status,
                'done_by' => $record->status == ProductChild::STATUS_STOCK_OUT ? $record->stockOutBy : ($record->status == ProductChild::STATUS_IN_TRANSIT ? $record->transferredBy : null),
                'done_at' => $record->status == ProductChild::STATUS_STOCK_OUT ? Carbon::parse($record->stock_out_at)->format('d M Y, h:i A') : ($record->status == ProductChild::STATUS_IN_TRANSIT ? Carbon::parse($record->stock_out_at)->format('d M Y, h:i A') : null),
                'progress' => $record->location != 'factory' ? null : $production->getProgress($production),
            ];
        }

        return response()->json($data);
    }

    public function viewGetDataRawMaterial(Request $req) {
        $pmm_records = $this->productionMsMaterial::where('product_id', $req->product_id)->orderBy('id', 'desc');
        $tmi_records = $this->taskMsInventory::where('inventory_type', Product::class)->where('inventory_id', $req->product_id)->orderBy('id', 'desc');

        $records_count = $pmm_records->count() + $tmi_records->count();
        $records_ids = $pmm_records->pluck('id')->merge($tmi_records->pluck('id'));

        $data = [
            "recordsTotal" => $records_count,
            "recordsFiltered" => $records_count,
            "data" => [],
            'records_ids' => $records_ids,
        ];
        $pmm_records_paginator = $pmm_records->simplePaginate(10);
        $tmi_records_paginator = $tmi_records->simplePaginate(10);
        $pmm_idx = 0;
        $tmi_idx = 0;

        while (count($data['data']) < 10) {
            if ($pmm_records_paginator[$pmm_idx] == null && $tmi_records_paginator[$tmi_idx] == null) {
                break;
            }
            if ($tmi_records_paginator[$tmi_idx] == null || $pmm_records_paginator[$pmm_idx]->created_at >= $tmi_records_paginator[$tmi_idx]->created_at) {
                $order_id = $pmm_records_paginator[$pmm_idx]->productionMilestone->production->sku;
                $qty = $pmm_records_paginator[$pmm_idx]->qty;
                $on_hold = $pmm_records_paginator[$pmm_idx]->on_hold;
                $at = $pmm_records_paginator[$pmm_idx]->updated_at;
                $pmm_idx++;
            } else if ($pmm_records_paginator[$pmm_idx] == null || $pmm_records_paginator[$pmm_idx]->created_at < $tmi_records_paginator[$tmi_idx]->created_at) {
                $order_id = $tmi_records_paginator[$tmi_idx]->taskMilestone->task->sku;
                $qty = $tmi_records_paginator[$tmi_idx]->qty;
                $on_hold = null;
                $at = $tmi_records_paginator[$tmi_idx]->updated_at;
                $tmi_idx++;
            }

            $data['data'][] = [
                'order_id' => $order_id,
                'qty' => $qty,
                'on_hold' => $on_hold,
                'at' => Carbon::parse($at)->format('d M Y, h:i A'),
            ];
        }

        return response()->json($data);
    }

    public function viewGetDataCost(Request $req)
    {
        $records = $this->prodCost::where('product_id', $req->product_id)->orderBy('id', 'desc');

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
                'qty_sku' => $record->qty ?? $record->sku,
                'unit_price' => number_format($record->unit_price, 2),
                'total_price' => number_format($record->total_price, 2),
                'at' => Carbon::parse($record->created)->format('d M Y, h:i A'),
            ];
        }

        return response()->json($data);
    }

    public function generateBarcode(Request $req) {
        if ($req->has('is_rm')) {
            $products = $this->prod::whereIn('id', [$req->id])->get();
        } else {
            $ids = explode(',', $req->ids);
            $products = $this->prodChild::whereIn('id', $ids)->get();
        }

        $data = [
            'barcode' => [],
            'renderer' => [],
        ];
        for ($i=0; $i < count($products); $i++) { 
            $prod = $req->has('is_rm') ? $products[$i] : $products[$i]->parent;

            $barcode = (new TypeCode128)->getBarcode($products[$i]->sku);
    
            // Output the barcode as HTML in the browser with a HTML Renderer
            $renderer = new DynamicHtmlRenderer;
            
            $data['renderer'][] = $renderer->render($barcode);
            $data['product_name'][] = $prod->model_name;
            $data['product_code'][] = $prod->sku;
            $data['barcode'][] = $products[$i]->sku;
        }
        $pdf = Pdf::loadView('inventory.barcode', $data);
        $pdf->setPaper('A4', 'letter');

        return $pdf->stream();
    }

    public function upsert(Request $req)
    {
        // dd($req->input());
        if ($req->order_idx != null) {
            $req->merge(['order_idx' => json_decode($req->order_idx)]);
        }
        if ($req->serial_no != null) {
            $serial_no = array_filter($req->serial_no, function ($val) {
                return $val != null;
            });
            $req->merge(['serial_no' => $serial_no]);
        }
        
        $rules = [
            'product_id' => 'nullable',
            'model_code' => 'required|max:250',
            'model_name' => 'required|max:250',
            'model_desc' => 'required|max:250',
            'barcode' => 'nullable|max:250',
            'uom' => 'required|max:250',
            'category_id' => 'required',
            'supplier_id' => 'required',
            'low_stock_threshold' => 'nullable',
            'min_price' => 'required',
            'max_price' => 'required|gt:min_price',
            'cost' => 'required',
            'weight' => 'nullable',
            'dimension_length' => 'nullable',
            'dimension_width' => 'nullable',
            'dimension_height' => 'nullable',
            'status' => 'required',
            'is_sparepart' => 'required',
            'image' => 'nullable',
            'image.*' => 'file|mimes:jpg,png,jpeg',

            'order_idx' => 'nullable',
            'serial_no' => 'nullable',
            'serial_no.*' => 'nullable|max:250',

            'lazada_sku' => 'nullable',
            'shopee_sku' => 'nullable',
            'tiktok_sku' => 'nullable',
            'woo_commerce_sku' => 'nullable',
        ];
        if ($req->product_id != null) {
            $rules['image'] = 'nullable';
        }
        if ($req->boolean('is_product') == true) {
            $rules['supplier_id'] = 'nullable';
            $rules['is_sparepart'] = 'nullable';
        } else if (!$req->boolean('is_sparepart')) {
            $rules['qty'] = 'required';
            $rules['cost'] = 'nullable';
        }
        // Validate request
        $req->validate($rules, [], [
            'model_desc' => 'model description',
            'category_id' => 'category',
            'qty' => 'quantity',
        ]);
        // Validate model code is unique in the branch
        $current_branch = Auth::user()->branch;

        $branch_product = Product::where(DB::raw('BINARY `sku`'), $req->model_code)
            ->whereHas('branch', function ($q) use ($current_branch) {
                $q->where('location', isSuperAdmin() ? Session::get('as_branch') : $current_branch->location);
            })
            ->first();

        if ($branch_product != null && $req->product_id != null && $branch_product->id != $req->product_id && $branch_product->sku == $req->model_code) {
            return Response::json([
                'errors' => [
                    'model_code' => "The model code has already taken"
                ],
            ], HttpFoundationResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            DB::beginTransaction();

            if ($req->product_id == null) {
                $prod = $this->prod::create([
                    'sku' => $req->model_code,
                    'type' => $req->boolean('is_product') == true ? Product::TYPE_PRODUCT : Product::TYPE_RAW_MATERIAL,
                    'model_name' => $req->model_name,
                    'model_desc' => $req->model_desc,
                    'barcode' => $req->barcode == null ? $req->model_code : $req->barcode,
                    'uom' => $req->uom,
                    'inventory_category_id' => $req->category_id,
                    'supplier_id' => $req->supplier_id,
                    'qty' => $req->qty,
                    'low_stock_threshold' => $req->low_stock_threshold,
                    'min_price' => $req->min_price,
                    'max_price' => $req->max_price,
                    'cost' => $req->cost == null ? 0 : $req->cost,
                    'weight' => $req->weight,
                    'length' => $req->dimension_length,
                    'width' => $req->dimension_width,
                    'height' => $req->dimension_height,
                    'is_active' => $req->boolean('status'),
                    'is_sparepart' => $req->is_sparepart == null ? null : $req->boolean('is_sparepart'),
                    'lazada_sku' => $req->lazada_sku,
                    'shopee_sku' => $req->shopee_sku,
                    'tiktok_sku' => $req->tiktok_sku,
                    'woo_commerce_sku' => $req->woo_commerce_sku
                ]);

                (new Branch())->assign(Product::class, $prod->id);
            } else {
                $prod = $this->prod->where('id', $req->product_id)->first();

                $prod->update([
                    'sku' => $req->model_code,
                    'model_name' => $req->model_name,
                    'model_desc' => $req->model_desc,
                    'barcode' => $req->barcode == null ? $req->model_code : $req->barcode,
                    'uom' => $req->uom,
                    'inventory_category_id' => $req->category_id,
                    'supplier_id' => $req->supplier_id,
                    'qty' => $req->qty,
                    'low_stock_threshold' => $req->low_stock_threshold,
                    'min_price' => $req->min_price,
                    'max_price' => $req->max_price,
                    'cost' => $req->cost == null ? 0 : $req->cost,
                    'weight' => $req->weight,
                    'length' => $req->dimension_length,
                    'width' => $req->dimension_width,
                    'height' => $req->dimension_height,
                    'is_active' => $req->boolean('status'),
                    'is_sparepart' => $req->is_sparepart == null ? null : $req->boolean('is_sparepart'),
                    'lazada_sku' => $req->lazada_sku,
                    'shopee_sku' => $req->shopee_sku,
                    'tiktok_sku' => $req->tiktok_sku,
                    'woo_commerce_sku' => $req->woo_commerce_sku
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

            // Serial No
            if ($req->serial_no != null) {
                if ($req->order_idx != null) {
                    $order_idx = array_filter($req->order_idx, function ($val) {
                        return $val != null;
                    });
                    $this->prodChild::where('product_id', $prod->id)->whereNotIn('id', $order_idx ?? [])->delete();
                }
    
                $now = now();
                $data = [];
                for ($i = 0; $i < count($req->serial_no); $i++) {
                    if ($req->serial_no[$i] == null) {
                        continue;
                    }

                    if ($req->order_idx != null && $req->order_idx[$i] != null) {
                        $pc = $this->prodChild::where('id', $req->order_idx[$i])->first();
    
                        $pc->update([
                            'sku' => $req->serial_no[$i],
                        ]);
                    } else {
                        $data[] = [
                            'product_id' => $prod->id,
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
            } else {
                $this->prodChild::where('product_id', $prod->id)->delete();
            }

            DB::commit();

            if ($req->boolean('is_product') == true) {
                if ($req->create_again == true) {
                    return redirect(route('product.create'))->with('success', 'Product created');
                }
                return redirect(route('product.index'))->with('success', 'Product ' . ($req->product_id == null ? 'created' : 'updated'));
            }
            if ($req->create_again == true) {
                return redirect(route('raw_material.create'))->with('success', 'Raw Material created');
            }
            return redirect(route('raw_material.index'))->with('success', 'Raw Material ' . ($req->product_id == null ? 'created' : 'updated'));
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator')->withInput();
        }
    }

    public function delete(Product $product)
    {
        $product->delete();

        return back()->with('success', 'Product deleted');
    }
}
