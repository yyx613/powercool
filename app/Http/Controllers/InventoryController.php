<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\InventoryCategory;
use App\Models\InventoryType;
use App\Models\Product;
use App\Models\ProductChild;
use App\Models\Production;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

class InventoryController extends Controller
{
    protected $prod;

    protected $prodChild;

    protected $invCat;

    protected $invType;

    protected $production;

    public function __construct()
    {
        $this->prod = new Product;
        $this->prodChild = new ProductChild;
        $this->invCat = new InventoryCategory;
        $this->invType = new InventoryType;
        $this->production = new Production;
    }

    public function index()
    {
        return view('inventory_category.list');
    }

    public function getData(Request $req)
    {
        $records = $this->invCat;

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
                $records = $records->orderBy($map[$order['column']], $order['dir']);
            }
        } else {
            $records = $records->orderBy('id', 'desc');
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
                'name' => $record->name,
                'company_group' => $record->company_group,
                'status' => $record->is_active,
                'can_edit' => hasPermission('inventory.category.edit'),
                'can_delete' => hasPermission('inventory.category.delete'),
            ];
        }

        return response()->json($data);
    }

    public function getRemainingQty(Request $req)
    {
        $records = $this->prod;

        // Search
        if ($req->has('keyword') && $req->keyword != '') {
            $keyword = $req->keyword;

            $records = $records->where(function ($q) use ($keyword) {
                $q->where('model_name', 'like', '%' . $keyword . '%');
            });
        }
        // Category
        if ($req->has('category') && $req->category != null) {
            if ($req->category == 'product') {
                $records = $records->where('type', Product::TYPE_PRODUCT);
            } elseif ($req->category == 'raw_material') {
                $records = $records->where('type', Product::TYPE_RAW_MATERIAL)->where('is_sparepart', false);
            } elseif ($req->category == 'sparepart') {
                $records = $records->where('type', Product::TYPE_RAW_MATERIAL)->where('is_sparepart', true);
            }
        }
        // Order
        if ($req->has('order')) {
            $map = [
                0 => 'model_name',
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
            'recordsTotal' => $records_count,
            'recordsFiltered' => $records_count,
            'data' => [],
            'records_ids' => $records_ids,
        ];
        foreach ($records_paginator as $record) {
            $data['data'][] = [
                'name' => $record->model_name,
                'image' => $record->image,
                'remaining_qty' => $record->warehouseAvailableStock(),
            ];
        }

        return response()->json($data);
    }

    public function create()
    {
        return view('inventory_category.form');
    }

    public function edit(InventoryCategory $cat)
    {
        return view('inventory_category.form', [
            'cat' => $cat,
        ]);
    }

    public function upsert(Request $req)
    {
        // Validate request
        $req->validate([
            'category_id' => 'nullable',
            'name' => 'required|max:250',
            'company_group' => 'required',
            'status' => 'required',
        ]);

        try {
            DB::beginTransaction();

            if ($req->category_id == null) {
                $cat = $this->invCat::create([
                    'name' => $req->name,
                    'company_group' => $req->company_group,
                    'is_active' => $req->boolean('status'),
                ]);

                (new Branch)->assign(InventoryCategory::class, $cat->id);
            } else {
                $cat = $this->invCat->where('id', $req->category_id)->first();

                $cat->update([
                    'name' => $req->name,
                    'company_group' => $req->company_group,
                    'is_active' => $req->boolean('status'),
                ]);
            }

            DB::commit();

            if ($req->create_again == true) {
                return redirect(route('inventory_category.create'))->with('success', 'Inventory Category created');
            }

            return redirect(route('inventory_category.index'))->with('success', 'Inventory Category ' . ($req->category_id == null ? 'created' : 'updated'));
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return Response::json([
                'result' => false,
            ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function delete(InventoryCategory $cat)
    {
        $cat->delete();

        return back()->with('success', 'Product Category deleted');
    }

    public function indexSummary()
    {
        // Low stock
        $products = $this->prod->with('image')->where('type', Product::TYPE_PRODUCT)->get();
        $raw_materials = $this->prod->with('image')->where('type', Product::TYPE_RAW_MATERIAL)->get();
        // Summary
        $active_product_count = $this->prod->where('is_active', true)->count();
        $inactive_product_count = $this->prod->where('is_active', false)->count();
        // Category
        $categories = $this->invCat->get();
        for ($i = 0; $i < count($categories); $i++) {
            $category['label'][] = $categories[$i]->name;
            $category['data'][] = $this->prod->where('inventory_category_id', $categories[$i]->id)->count();
        }
        // Stock summary
        $warehouse_stock = 0;
        $warehouse_reserved_stock = 0;
        $production_stock = 0;
        $production_reserved_stock = 0;
        for ($i = 0; $i < count($products); $i++) {
            $warehouse_stock += $products[$i]->warehouseAvailableStock($products[$i]->id);
            $warehouse_reserved_stock += $products[$i]->warehouseReservedStock($products[$i]->id);
            $production_stock += $products[$i]->productionStock($products[$i]->id);
            $production_reserved_stock += $products[$i]->productionReservedStock($products[$i]->id);
        }
        for ($i = 0; $i < count($raw_materials); $i++) {
            $warehouse_stock += $raw_materials[$i]->warehouseAvailableStock($raw_materials[$i]->id);
            $warehouse_reserved_stock += $raw_materials[$i]->warehouseReservedStock($raw_materials[$i]->id);
            $production_stock += $raw_materials[$i]->productionStock($raw_materials[$i]->id);
            $production_reserved_stock += $raw_materials[$i]->productionReservedStock($raw_materials[$i]->id);
        }

        return view('inventory.summary', [
            'warehouse_available_stock' => $warehouse_stock,
            'warehouse_reserved_stock' => $warehouse_reserved_stock,
            'production_stock' => $production_stock,
            'production_reserved_stock' => $production_reserved_stock,
            'products' => $products,
            'raw_materials' => $raw_materials,
            'active_product_count' => $active_product_count,
            'inactive_product_count' => $inactive_product_count,
            'categories' => $category,
        ]);
    }

    public function stockIn(ProductChild $product_child)
    {
        try {
            DB::beginTransaction();

            if ($product_child->status == $this->prodChild::STATUS_TO_BE_RECEIVED) { // Stock in from another branch
                // Update status from transferred child
                $this->prodChild::where('id', $product_child->transferred_from)->update([
                    'status' => $this->prodChild::STATUS_RECEIVED,
                ]);
                // Remove self status
                $product_child->status = null;
                $product_child->save();
            } else { // Stock in from same branch, from produciton
                $product_child->location = $this->prodChild::LOCATION_WAREHOUSE;
                $product_child->save();

                $this->production->where('product_child_id', $product_child->id)->update([
                    'status' => $this->production::STATUS_TRANSFERRED,
                ]);
            }

            DB::commit();

            return back()->with('success', 'Stocked In');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator');
        }
    }

    public function stockOut(Request $req, ProductChild $product_child)
    {
        $under_powercool = $product_child->parent->company_group == 1;
        if (! $under_powercool && $req->has('stock_out_to') && $req->stock_out_to !== 'production' && (! $req->has('stock_out_to') || ! $req->has('stock_out_to_selection'))) {
            abort(404);
        }

        try {
            DB::beginTransaction();

            if ($under_powercool) {
                $product_child->product_id = $product_child->parent->hi_ten_stock_code;
                $product_child->save();
            } else {
                $product_child->status = $this->prodChild::STATUS_STOCK_OUT;
                $product_child->stock_out_by = Auth::user()->id;
                $product_child->stock_out_to_type = $req->stock_out_to === 'production' ? Production::class : ($req->stock_out_to == 'customer' ? Customer::class : User::class);
                $product_child->stock_out_to_id = $req->stock_out_to_selection;
                $product_child->stock_out_at = now();
                $product_child->save();

                if ($req->stock_out_to === 'production') {
                    $product_child->location = ProductChild::LOCATION_FACTORY;
                    $product_child->save();
                    $product_child->parent->in_production = true;
                    $product_child->parent->save();
                }
            }

            DB::commit();

            return back()->with('success', 'Stocked Out');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator');
        }
    }

    public function transfer(Request $req, ProductChild $product_child)
    {
        try {
            DB::beginTransaction();

            $product = Product::where('id', $product_child->product_id)->first();
            // Create inventory category for branch to transfer
            $cat = InventoryCategory::where('id', $product->inventory_category_id)->first();

            $cloned_cat = InventoryCategory::where(DB::raw('BINARY `name`'), $cat->name)
                ->whereHas('branch', function ($q) use ($req) {
                    $q->where('location', $req->branch);
                })
                ->first();

            if ($cloned_cat == null) {
                $cloned_cat = $cat->replicate();
                $cloned_cat->save();

                (new Branch)->assign(InventoryCategory::class, $cloned_cat->id, $req->branch);
            }
            // Create supplier for branch to transfer
            if ($product->supplier_id != null) {
                $supp = Supplier::where('id', $product->supplier_id)->first();

                $cloned_supp = Supplier::where(DB::raw('BINARY `sku`'), $supp->sku)
                    ->whereHas('branch', function ($q) use ($req) {
                        $q->where('location', $req->branch);
                    })
                    ->first();

                if ($cloned_supp == null) {
                    $cloned_supp = $supp->replicate();
                    $cloned_supp->save();

                    (new Branch)->assign(Supplier::class, $cloned_supp->id, $req->branch);
                    // Create supplier image for branch to transfer
                    for ($i = 0; $i < count($supp->pictures); $i++) {
                        $cloned_product_image = $supp->pictures[$i]->replicate();
                        $cloned_product_image->object_id = $cloned_supp->id;
                        $cloned_product_image->save();
                    }
                }
            }
            // Create product for branch to transfer
            $cloned_product = Product::where(DB::raw('BINARY `sku`'), $product->sku)
                ->whereHas('branch', function ($q) use ($req) {
                    $q->where('location', $req->branch);
                })
                ->first();

            if ($cloned_product == null) {
                $cloned_product = $product->replicate();
                $cloned_product->inventory_category_id = $cloned_cat->id;
                $cloned_product->save();
                // Create product image for branch to transfer
                $cloned_product_image = $product->image->replicate();
                $cloned_product_image->object_id = $cloned_product->id;
                $cloned_product_image->save();

                (new Branch)->assign(Product::class, $cloned_product->id, $req->branch);
            }

            // Create child for cloned product
            $cloned_child = $product_child->replicate();
            $cloned_child->product_id = $cloned_product->id;
            $cloned_child->status = $this->prodChild::STATUS_TO_BE_RECEIVED;
            $cloned_child->transferred_from = $product_child->id;
            $cloned_child->save();

            // Update status on current product child
            $product_child->status = $this->prodChild::STATUS_IN_TRANSIT;
            $product_child->transfer_by = Auth::user()->id;
            $product_child->transfer_at = now();
            $product_child->save();

            DB::commit();

            return back()->with('success', 'Product is in transit');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator');
        }
    }

    public function indexType()
    {
        return view('inventory_type.list');
    }

    public function getDataType(Request $req)
    {
        $records = $this->invType;

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
                $records = $records->orderBy($map[$order['column']], $order['dir']);
            }
        } else {
            $records = $records->orderBy('id', 'desc');
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
                'name' => $record->name,
                'company_group' => $record->company_group,
                'type' => $record->type,
                'status' => $record->is_active,
            ];
        }

        return response()->json($data);
    }

    public function createType()
    {
        return view('inventory_type.form');
    }

    public function editType(InventoryType $type)
    {
        return view('inventory_type.form', [
            'type' => $type,
        ]);
    }

    public function upsertType(Request $req)
    {
        // Validate request
        $req->validate([
            'type_id' => 'nullable',
            'name' => 'required|max:250',
            'company_group' => 'required',
            'type' => 'required',
            'status' => 'required',
        ]);

        try {
            DB::beginTransaction();

            if ($req->type_id == null) {
                $cat = $this->invType::create([
                    'name' => $req->name,
                    'company_group' => $req->company_group,
                    'type' => $req->type,
                    'is_active' => $req->boolean('status'),
                ]);

                (new Branch)->assign(InventoryType::class, $cat->id);
            } else {
                $cat = $this->invType->where('id', $req->type_id)->first();

                $cat->update([
                    'name' => $req->name,
                    'company_group' => $req->company_group,
                    'type' => $req->type,
                    'is_active' => $req->boolean('status'),
                ]);
            }

            DB::commit();

            if ($req->create_again == true) {
                return redirect(route('inventory_type.create'))->with('success', 'Inventory Type created');
            }

            return redirect(route('inventory_type.index'))->with('success', 'Inventory Type ' . ($req->type_id == null ? 'created' : 'updated'));
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return redirect(route('inventory_type.create'))->with('error', 'Something went wrong');
        }
    }

    public function deleteType(InventoryType $type)
    {
        $type->delete();

        return back()->with('success', 'Product Type deleted');
    }
}
