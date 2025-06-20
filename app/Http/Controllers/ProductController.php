<?php

namespace App\Http\Controllers;

use App\Exports\ProductExport;
use App\Models\Approval;
use App\Models\Attachment;
use App\Models\Branch;
use App\Models\DeliveryOrder;
use App\Models\DeliveryOrderProduct;
use App\Models\FactoryRawMaterial;
use App\Models\FactoryRawMaterialRecord;
use App\Models\InventoryCategory;
use App\Models\Invoice;
use App\Models\MaterialUse;
use App\Models\Product;
use App\Models\ProductChild;
use App\Models\ProductCost;
use App\Models\Production;
use App\Models\ProductionMilestoneMaterial;
use App\Models\ProductMilestone;
use App\Models\ProductSellingPrice;
use App\Models\SaleProduct;
use App\Models\TaskMilestoneInventory;
use App\Models\UOM;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use Picqer\Barcode\Renderers\DynamicHtmlRenderer;
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

    protected $sellingPrice;

    protected $saleProduct;

    protected $deliveryOrderProduct;

    protected $invoice;

    public function __construct()
    {
        $this->prod = new Product;
        $this->prodChild = new ProductChild;
        $this->invCat = new InventoryCategory;
        $this->production = new Production;
        $this->productionMsMaterial = new ProductionMilestoneMaterial;
        $this->prodCost = new ProductCost;
        $this->taskMsInventory = new TaskMilestoneInventory;
        $this->sellingPrice = new ProductSellingPrice;
        $this->saleProduct = new SaleProduct;
        $this->deliveryOrderProduct = new DeliveryOrderProduct;
        $this->invoice = new Invoice;
    }

    public function index(Request $req)
    {
        if ($req->type == 'waiting') {
            Session::put('type', 'waiting');
        } else if ($req->type == 'usage') {
            Session::put('type', 'usage');
        } else {
            Session::remove('type');
        }
        return view('inventory.list', [
            'type' => $req->type ?? null
        ]);
    }

    public function getData(Request $req)
    {
        // Search for child
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];

            $pc = $this->prodChild::where('sku', $keyword)->first();
            if ($pc != null) {
                return response()->json([
                    'is_product' => $pc->parent()->withTrashed()->first()->type == Product::TYPE_PRODUCT,
                    'parent_id' => $pc->product_id,
                    'search' => $keyword,
                ]);
            }
        }

        $records = $this->prod->with(['category' => function ($q) {
            $q->withTrashed();
        }]);

        $filter_type = Session::get('type');
        if ($filter_type == 'waiting') {
            $approvals = Approval::where('object_type', FactoryRawMaterial::class)->where('status', Approval::STATUS_APPROVED)->get();
            $frm_ids = $approvals->pluck('object_id')->toArray();
            $frms = FactoryRawMaterial::whereIn('id', $frm_ids)->get();
            $product_ids_only1 = $frms->pluck('product_id');

            $pc_ids = Approval::where('object_type', ProductChild::class)->pluck('object_id')->toArray();
            $product_ids_only2 = ProductChild::whereIn('id', $pc_ids)->pluck('product_id');

            $product_ids_only = $product_ids_only1->merge($product_ids_only2);

            $records = $records->whereIn('id', $product_ids_only);
        }

        // Type
        if ($req->boolean('is_product') == true) {
            $records = $records->where('type', Product::TYPE_PRODUCT);
        } else {
            $records = $records->where('type', Product::TYPE_RAW_MATERIAL);
        }
        // Production
        if ($req->boolean('is_production') == true) {
            if ($req->boolean('is_product') == true) {
                $product_ids = Production::where('status', Production::STATUS_COMPLETED)->pluck('product_id')->toArray();
                $records = $records->whereIn('id', $product_ids);
            } else {
                $records = $records->withCount(['children' => function ($q) {
                    $q->where('location', ProductChild::LOCATION_FACTORY);
                }])->having('children_count', '>', 0);
            }
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
            'recordsTotal' => $records_count,
            'recordsFiltered' => $records_count,
            'data' => [],
            'records_ids' => $records_ids,
        ];
        foreach ($records_paginator as $key => $record) {
            $qty = 0;
            if ($req->boolean('is_production') == true) {
                if ($req->boolean('is_product') == true) {
                    $qty = Production::where('status', Production::STATUS_COMPLETED)->where('product_id', $record->id)->count();
                } else {
                    $pcs = ProductChild::where('product_id', $record->id)
                        ->where(function ($q) {
                            $q->where('location', ProductChild::LOCATION_FACTORY);
                        })
                        ->get();
                    for ($i = 0; $i < count($pcs); $i++) {
                        if ($pcs[$i]->assignedTo() == null) {
                            $qty++;
                        }
                    }
                }
            } else {
                if ($filter_type == 'waiting') {
                    $frm_id = $frms->where('product_id', $record->id)->value('id');
                    $d = json_decode($approvals->where('object_id', $frm_id)->value('data'));
                    $qty = $d->qty;
                } else {
                    $qty = $record->qty;
                }
            }
            // Ready for production
            $ready_for_production = false;
            if ($req->boolean('is_production') == false && $req->boolean('is_product') == true && $record->materialUse != null) {
                $ready_for_production = true;
            }

            $data['data'][] = [
                'id' => $record->id,
                'sku' => $record->sku,
                'image' => $record->image ?? null,
                'model_name' => $record->model_name,
                'category' => $record->category->name,
                'qty' => $qty,
                'min_price' => number_format($record->min_price, 2),
                'max_price' => number_format($record->max_price, 2),
                'is_sparepart' => $record->is_sparepart,
                'status' => $record->is_active,
                'can_edit' => $req->boolean('is_product') ? hasPermission('inventory.product.edit') : hasPermission('inventory.raw_material.edit'),
                'can_delete' => $req->boolean('is_product') ? hasPermission('inventory.product.delete') : hasPermission('inventory.raw_material.delete'),
                'approval_id' => $filter_type == 'waiting' ? $approvals->where('object_id', $frm_id)->value('id') : null,
                'ready_for_production' => $ready_for_production
            ];
        }
        if ($req->boolean('is_production') == true && $req->boolean('is_product') == false) {
            $frm_data = $this->getFactoryRawMaterial(
                $req->has('search') && $req->search['value'] != null ? $req->search['value'] : null,
                $req->order,
                isset($product_ids_only) ? $product_ids_only : null,
                $filter_type == 'usage' ? true : null,
            );

            $data['recordsTotal'] += $frm_data['recordsTotal'];
            $data['recordsFiltered'] += $frm_data['recordsFiltered'];
            $data['data'] = array_merge($data['data'], $frm_data['data']);
            $data['records_ids'] = $data['records_ids']->merge($frm_data['records_ids']);
        }

        return response()->json($data);
    }

    private function getFactoryRawMaterial($keyword = null, $orders = null, $product_ids_only = null, $show_usage = false)
    {
        $frms = FactoryRawMaterial::get();

        $records = $this->prod->whereIn('id', $frms->pluck('product_id')->toArray())->with(['category' => function ($q) {
            $q->withTrashed();
        }]);

        if ($product_ids_only != null) {
            $records = $records->whereIn('id', $product_ids_only);
        }
        // Search
        if ($keyword != null) {
            $records = $records->where(function ($q) use ($keyword) {
                $q->where('sku', 'like', '%' . $keyword . '%')
                    ->orWhere('model_name', 'like', '%' . $keyword . '%')
                    ->orWhere('model_desc', 'like', '%' . $keyword . '%')
                    ->orWhere('min_price', 'like', '%' . $keyword . '%')
                    ->orWhere('max_price', 'like', '%' . $keyword . '%')
                    ->orWhereHas('category', function ($qq) use ($keyword) {
                        $qq->where('name', 'like', '%' . $keyword . '%');
                    });
            });
        }
        // Order
        if ($orders != null) {
            $map = [
                0 => 'sku',
                1 => 'model_name',
                2 => 'category',
                3 => 'qty',
            ];
            foreach ($orders as $order) {
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

        $data = [];
        foreach ($records_paginator as $key => $record) {
            $qty = $frms->where('product_id', $record->id)->first()->remainingQty();
            if ($show_usage == true && $qty > 0) {
                continue;
            } else if (($show_usage == false || $show_usage == null) && $qty <= 0) {
                continue;
            }

            $data['data'][] = [
                'id' => $record->id,
                'sku' => $record->sku,
                'image' => $record->image ?? null,
                'model_name' => $record->model_name,
                'category' => $record->category->name,
                'qty' => $qty,
                'min_price' => number_format($record->min_price, 2),
                'max_price' => number_format($record->max_price, 2),
                'is_sparepart' => $record->is_sparepart,
                'status' => $record->is_active,
                'can_edit' => false,
                'can_delete' => false,
                'frm_id' => $frms->where('product_id', $record->id)->value('id')
            ];
        }
        $data['recordsTotal'] = isset($data['data']) ? count($data['data']) : 0;
        $data['recordsFiltered'] = isset($data['data']) ? count($data['data']) : 0;
        $data['records_ids'] = $records_ids;
        if (!isset($data['data'])) {
            $data['data'] = [];
        }

        return $data;
    }

    public function create(Request $req)
    {
        if ($req->has('id')) {
            $dup_prod = Product::where('id', $req->id)->first();
        }

        return view('inventory.form', [
            'dup_prod' => $dup_prod ?? null,
        ]);
    }

    public function edit(Product $product)
    {
        $product->load('image', 'children', 'sellingPrices', 'milestones');
        $material_uses = MaterialUse::with('materials.material', 'approval')->where('product_id', $product->id)->get();
        $material_use = [];

        for ($i = 0; $i < count($material_uses); $i++) {
            if ($material_uses[$i]->approval == null || $material_uses[$i]->approval->status == Approval::STATUS_APPROVED) {
                $material_use[] = $material_uses[$i];
            }
        }

        return view('inventory.form', [
            'prod' => $product,
            'material_use' => $material_use
        ]);
    }

    public function view(Product $product)
    {
        $product->load('image', 'stockHiTen');

        return view('inventory.view', [
            'prod' => $product,
            'warehouse_available_stock' => $product->warehouseAvailableStock(),
            'warehouse_reserved_stock' => $product->warehouseReservedStock(),
            'warehouse_on_hold_stock' => $product->warehouseOnHoldStock(),
            'production_stock' => $product->productionStock(),
            'production_reserved_stock' => $product->productionReservedStock(),
        ]);
    }

    public function viewGetData(Request $req)
    {
        $records = $this->prodChild::where('product_id', $req->product_id);

        if ($req->boolean('is_production') == true) {
            $records = $records->where('location', 'factory');
        }

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
            'recordsTotal' => $records_count,
            'recordsFiltered' => $records_count,
            'data' => [],
            'records_ids' => $records_ids,
        ];
        foreach ($records_paginator as $key => $record) {
            $production = null;
            if ($record->location == 'factory') {
                $production = $this->production->where('product_child_id', $record->id)->first();
            }
            $assigned_to = $record->assignedTo();
            if ($assigned_to != null && is_array($assigned_to)) {
                $skus = [];
                for ($i = 0; $i < count($assigned_to); $i++) {
                    $skus[] = $assigned_to[$i]->sku;
                }
                $assigned_to = implode(', ', $skus);
            } elseif ($assigned_to != null) {
                $assigned_to = $assigned_to->sku;
            }
            $data['data'][] = [
                'id' => $record->id,
                'sku' => $record->sku,
                'location' => $record->location,
                'order_id' => $assigned_to,
                'status' => $record->status,
                'stock_out_to' => $record->stock_out_to_type == Production::class ? 'production' : ($record->status != ProductChild::STATUS_STOCK_OUT ? null : $record->stockOutTo),
                'done_by' => $record->status == ProductChild::STATUS_STOCK_OUT ? $record->stockOutBy : ($record->status == ProductChild::STATUS_IN_TRANSIT ? $record->transferredBy : null),
                'done_at' => $record->status == ProductChild::STATUS_STOCK_OUT ? Carbon::parse($record->stock_out_at)->format('d M Y, h:i A') : ($record->status == ProductChild::STATUS_IN_TRANSIT ? Carbon::parse($record->stock_out_at)->format('d M Y, h:i A') : null),
                'progress' => $record->location != 'factory' || $production == null ? null : $production->getProgress($production),
            ];
        }

        return response()->json($data);
    }

    public function viewGetDataRawMaterial(Request $req)
    {
        $sp_records = $this->saleProduct::where('product_id', $req->product_id)->orderBy('id', 'desc');
        $dop_records = $this->deliveryOrderProduct::whereIn('sale_product_id', $sp_records->pluck('id'))->orderBy('id', 'desc');
        $inv_records = $this->invoice::whereIn('id', DeliveryOrder::whereIn('id', $dop_records->pluck('delivery_order_id')->toArray())->pluck('invoice_id'))->orderBy('id', 'desc');
        $pmm_records = $this->productionMsMaterial::where('product_id', $req->product_id)->orderBy('id', 'desc');
        $tmi_records = $this->taskMsInventory::where('inventory_type', Product::class)->where('inventory_id', $req->product_id)->orderBy('id', 'desc');

        $records_count = $pmm_records->count() + $tmi_records->count();
        $records_ids = $pmm_records->pluck('id')->merge($tmi_records->pluck('id'));

        $data = [
            'recordsTotal' => $records_count,
            'recordsFiltered' => $records_count,
            'data' => [],
            'records_ids' => $records_ids,
        ];
        $sp_records_paginator = $sp_records->simplePaginate(10);
        $dop_records_paginator = $dop_records->simplePaginate(10);
        $inv_records_paginator = $inv_records->simplePaginate(10);
        $pmm_records_paginator = $pmm_records->simplePaginate(10);
        $tmi_records_paginator = $tmi_records->simplePaginate(10);
        $sp_idx = 0;
        $dop_idx = 0;
        $inv_idx = 0;
        $pmm_idx = 0;
        $tmi_idx = 0;

        while (count($data['data']) < 10) {
            if (
                $sp_records_paginator[$sp_idx] == null && $dop_records_paginator[$dop_idx] == null && $inv_records_paginator[$inv_idx] == null &&
                $pmm_records_paginator[$pmm_idx] == null && $tmi_records_paginator[$tmi_idx] == null
            ) {
                break;
            }

            $category = null;
            $sp_created_at = $sp_records_paginator[$sp_idx] != null ? $sp_records_paginator[$sp_idx]->created_at : null;
            $dop_created_at = $dop_records_paginator[$dop_idx] != null ? $dop_records_paginator[$dop_idx]->created_at : null;
            $inv_created_at = $inv_records_paginator[$inv_idx] != null ? $inv_records_paginator[$inv_idx]->created_at : null;
            $pmm_created_at = $pmm_records_paginator[$pmm_idx] != null ? $pmm_records_paginator[$pmm_idx]->created_at : null;
            $tmi_created_at = $tmi_records_paginator[$tmi_idx] != null ? $tmi_records_paginator[$tmi_idx]->created_at : null;

            if ($sp_created_at > $dop_created_at && $sp_created_at > $inv_created_at && $sp_created_at > $pmm_created_at && $sp_created_at > $tmi_created_at) {
                $category = 'sp';
            } elseif ($dop_created_at > $sp_created_at && $dop_created_at > $inv_created_at && $dop_created_at > $pmm_created_at && $dop_created_at > $tmi_created_at) {
                $category = 'dop';
            } elseif ($inv_created_at > $sp_created_at && $inv_created_at > $dop_created_at && $inv_created_at > $pmm_created_at && $inv_created_at > $tmi_created_at) {
                $category = 'inv';
            } elseif ($pmm_created_at > $sp_created_at && $pmm_created_at > $dop_created_at && $pmm_created_at > $inv_created_at && $pmm_created_at > $tmi_created_at) {
                $category = 'pmm';
            } elseif ($tmi_created_at > $sp_created_at && $tmi_created_at > $dop_created_at && $tmi_created_at > $inv_created_at && $tmi_created_at > $pmm_created_at) {
                $category = 'tmi';
            }

            if ($category == 'sp') {
                $remaining_qty = $sp_records_paginator[$sp_idx]->remainingQtyForRM();

                if ($remaining_qty == 0) {
                    $sp_idx++;

                    continue;
                }

                $order_id = $sp_records_paginator[$sp_idx]->sale->sku;
                $qty = $remaining_qty;
                $on_hold = null;
                $at = $sp_records_paginator[$sp_idx]->updated_at;
                $sp_idx++;
            } elseif ($category == 'dop') {
                if ($dop_records_paginator[$dop_idx]->do->invoice_id != null) {
                    $dop_idx++;

                    continue;
                }
                $order_id = $dop_records_paginator[$dop_idx]->do->sku;
                $qty = $dop_records_paginator[$dop_idx]->qty;
                $on_hold = null;
                $at = $dop_records_paginator[$dop_idx]->updated_at;
                $dop_idx++;
            } elseif ($category == 'inv') {
                $do_ids = DeliveryOrder::where('invoice_id', $inv_records_paginator[$inv_idx]->id)->pluck('id')->toArray();

                $order_id = $inv_records_paginator[$inv_idx]->sku;
                $qty = $this->deliveryOrderProduct::whereIn('delivery_order_id', $do_ids)->sum('qty');
                $on_hold = null;
                $at = $inv_records_paginator[$inv_idx]->updated_at;
                $inv_idx++;
            } elseif ($category == 'pmm') {
                $order_id = $pmm_records_paginator[$pmm_idx]->productionMilestone->production->sku;
                $qty = $pmm_records_paginator[$pmm_idx]->qty;
                $on_hold = $pmm_records_paginator[$pmm_idx]->on_hold;
                $at = $pmm_records_paginator[$pmm_idx]->updated_at;
                $pmm_idx++;
            } elseif ($category == 'tmi') {
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
            'recordsTotal' => $records_count,
            'recordsFiltered' => $records_count,
            'data' => [],
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

    public function generateBarcode(Request $req)
    {
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
        for ($i = 0; $i < count($products); $i++) {
            $prod = $req->has('is_rm') ? $products[$i] : $products[$i]->parent;

            $barcode = (new TypeCode128)->getBarcode($products[$i]->sku);

            // Output the barcode as HTML in the browser with a HTML Renderer
            $renderer = new DynamicHtmlRenderer;

            $data['renderer'][] = $renderer->render($barcode);
            $data['product_name'][] = $prod->model_name;
            $data['product_code'][] = $prod->sku;
            $data['barcode'][] = $products[$i]->sku;
            $data['dimension'][] = ($prod->length ?? 0) . ' x ' . ($prod->width ?? 0) . ' x ' . ($prod->height ?? 0) . 'MM';
            $data['capacity'][] = $prod->capacity;
            $data['weight'][] = $prod->weight;
            $data['refrigerant'][] = $prod->refrigerant;
            $data['power_input'][] = $prod->power_input;
            $data['voltage_frequency'][] = $prod->voltage_frequency;
            $data['standard_features'][] = $prod->standard_features;
        }
        $pdf = Pdf::loadView('inventory.barcode', $data);
        $pdf->setPaper('A4', 'letter');

        return $pdf->stream();
    }

    public function upsert(Request $req)
    {
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
            'company_group' => 'required',
            'product_id' => 'nullable',
            'initial_for_production' => 'required|max:250',
            'model_code' => 'required|max:250',
            'model_name' => 'required|max:250',
            'model_desc' => 'required|max:250',
            'uom' => 'required|max:250',
            'category_id' => 'required',
            'item_type' => 'required',
            'supplier_id' => 'required',
            'low_stock_threshold' => 'nullable',
            'min_price' => 'required',
            'max_price' => 'required',
            'cost' => 'required',
            'status' => 'required',
            'is_sparepart' => 'required',
            'image' => 'nullable',
            'image.*' => 'file|mimes:jpg,png,jpeg',
            'hi_ten_stock_code' => 'nullable',

            'selling_price_name' => 'nullable',
            'selling_price_name.*' => 'nullable',
            'selling_price' => 'nullable',
            'selling_price.*' => 'required_with:selling_price_name.*',

            'weight' => 'nullable',
            'capacity' => 'nullable|max:250',
            'refrigerant' => 'nullable|max:250',
            'power_input' => 'nullable|max:250',
            'voltage_frequency' => 'nullable|max:250',
            'standard_features' => 'nullable|max:250',
            'dimension_length' => 'nullable',
            'dimension_width' => 'nullable',
            'dimension_height' => 'nullable',

            'order_idx' => 'nullable',
            'serial_no' => 'nullable',
            'serial_no.*' => 'nullable|max:250',

            'lazada_sku' => 'nullable',
            'shopee_sku' => 'nullable',
            'tiktok_sku' => 'nullable',
            'woo_commerce_sku' => 'nullable',

            'classification_code' => 'required|array',
            'classification_code.*' => 'exists:classification_codes,id',
        ];
        if ($req->product_id != null) {
            $rules['image'] = 'nullable';
        }
        if ($req->boolean('is_product') == true) {
            $rules['supplier_id'] = 'nullable';
            $rules['is_sparepart'] = 'nullable';
        } elseif (! $req->boolean('is_sparepart')) {
            $rules['initial_for_production'] = 'nullable';
            $rules['qty'] = 'required';
            $rules['min_price'] = 'nullable';
            $rules['max_price'] = 'nullable';
            $rules['cost'] = 'nullable';
        }
        // Validate request
        $req->validate($rules, [
            'hi_ten_stock_code.required_if' => 'The Hi-Ten stock code field is required',
        ], [
            'model_desc' => 'model description',
            'category_id' => 'category',
            'qty' => 'quantity',
            'classification_code' => 'classification codes',
            'selling_price_name.*' => 'name',
            'selling_price.*' => 'price',
        ]);

        // Validate model code is unique in the branch
        $current_branch = Auth::user()->branch;

        $branch_product = Product::where(DB::raw('BINARY `sku`'), $req->model_code)
            ->whereHas('branch', function ($q) use ($current_branch) {
                $q->where('location', isSuperAdmin() ? Session::get('as_branch') : $current_branch->location);
            })
            ->first();

        if ($branch_product != null && $req->product_id != null && $branch_product->id != $req->product_id && $branch_product->sku == $req->model_code) {
            throw ValidationException::withMessages([
                'model_code' => 'The model code has already taken',
            ]);
        }
        // Validate selling price is between min-max price
        if ($req->selling_price_name != null) {
            for ($i = 0; $i < count($req->selling_price_name); $i++) {
                if ($req->selling_price_name[$i] == null) {
                    continue;
                }
                if (! ($req->selling_price[$i] > $req->min_price && $req->selling_price[$i] < $req->max_price) && ! ($req->selling_price[$i] != $req->min_price || $req->selling_price[$i] != $req->max_price)) {
                    throw ValidationException::withMessages([
                        'selling_price.' . $i => 'The price is not between ' . $req->min_price . ' and ' . $req->max_price,
                    ]);
                }
            }
        }

        try {
            DB::beginTransaction();

            if ($req->product_id == null) {
                $prod = $this->prod::create([
                    'company_group' => $req->company_group,
                    'sku' => $req->model_code,
                    'initial_for_production' => $req->initial_for_production,
                    'type' => $req->boolean('is_product') == true ? Product::TYPE_PRODUCT : Product::TYPE_RAW_MATERIAL,
                    'model_name' => $req->model_name,
                    'model_desc' => $req->model_desc,
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
                    'capacity' => $req->capacity,
                    'refrigerant' => $req->refrigerant,
                    'power_input' => $req->power_input,
                    'voltage_frequency' => $req->voltage_frequency,
                    'standard_features' => $req->standard_features,
                    'is_active' => $req->boolean('status'),
                    'is_sparepart' => $req->is_sparepart == null ? null : $req->boolean('is_sparepart'),
                    'item_type' => $req->item_type,
                    'lazada_sku' => $req->lazada_sku,
                    'shopee_sku' => $req->shopee_sku,
                    'tiktok_sku' => $req->tiktok_sku,
                    'woo_commerce_sku' => $req->woo_commerce_sku,
                    'hi_ten_stock_code' => $req->hi_ten_stock_code,
                ]);

                (new Branch)->assign(Product::class, $prod->id);
            } else {
                $prod = $this->prod->where('id', $req->product_id)->first();

                $prod->update([
                    'company_group' => $req->company_group,
                    'sku' => $req->model_code,
                    'initial_for_production' => $req->initial_for_production,
                    'model_name' => $req->model_name,
                    'model_desc' => $req->model_desc,
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
                    'capacity' => $req->capacity,
                    'refrigerant' => $req->refrigerant,
                    'power_input' => $req->power_input,
                    'voltage_frequency' => $req->voltage_frequency,
                    'standard_features' => $req->standard_features,
                    'is_active' => $req->boolean('status'),
                    'is_sparepart' => $req->is_sparepart == null ? null : $req->boolean('is_sparepart'),
                    'item_type' => $req->item_type,
                    'lazada_sku' => $req->lazada_sku,
                    'shopee_sku' => $req->shopee_sku,
                    'tiktok_sku' => $req->tiktok_sku,
                    'woo_commerce_sku' => $req->woo_commerce_sku,
                    'hi_ten_stock_code' => $req->hi_ten_stock_code,
                ]);
            }

            // Selling Prices
            $this->sellingPrice::where('product_id', $prod->id)->delete();

            if ($req->selling_price_name != null) {
                $data = [];
                for ($i = 0; $i < count($req->selling_price_name); $i++) {
                    if ($req->selling_price_name[$i] == null) {
                        continue;
                    }
                    $data[] = [
                        'product_id' => $prod->id,
                        'name' => $req->selling_price_name[$i],
                        'price' => $req->selling_price[$i],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                $this->sellingPrice::insert($data);
            }

            // Classification code
            $classificationCodes = $req->input('classification_code', []);
            $prod->classificationCodes()->sync($classificationCodes);

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

            // Milestones
            $data = [];
            $milestones = json_decode($req->milestones);
            for ($i = 0; $i < count((array)$milestones); $i++) {
                foreach ($milestones as $key => $value) {
                    if ($value->sequence == $i + 1) {
                        $data[] = [
                            'product_id' => $prod->id,
                            'milestone_id' => $key,
                            'material_use_product_id' => json_encode($value->material_use_product_ids),
                            'created_at' => now(),
                        ];
                        break;
                    }
                }
            }

            if (count($data) > 0) {
                ProductMilestone::where('product_id', $prod->id)->delete();
                ProductMilestone::insert($data);
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

    public function export()
    {
        if (str_contains(Route::currentRouteName(), 'product.')) {
            return Excel::download(new ProductExport(true), 'product.xlsx');
        } elseif (str_contains(Route::currentRouteName(), 'raw_material.')) {
            return Excel::download(new ProductExport(false), 'product.xlsx');
        }
    }

    public function get(Product $product)
    {
        $material_use = MaterialUse::with('materials.material')->where('product_id', $product->id)->get();
        $milestones = ProductMilestone::with(['milestone' => function ($q) {
            $q->withTrashed();
        }])->where('product_id', $product->id)->get();

        return Response::json([
            'product_milestones' => $milestones,
            'product_material_use' => $material_use,
        ]);
    }

    public function transferToFactory(Request $req)
    {
        $product = Product::where('id', $req->product_id)->first();
        if ($req->qty <= 0) {
            return back()->with('warning', 'The quantity must not greater than 0')->withInput();
        }
        if ($product->qty < $req->qty) {
            return back()->with('warning', 'The quantity must not greater than ' . $product->qty)->withInput();
        }

        try {
            DB::beginTransaction();

            // Transfer
            $frm = FactoryRawMaterial::where('product_id', $product->id)->first();

            if ($frm != null) {
                $frm->qty += $req->qty;
                $frm->save();
            } else {
                $frm = FactoryRawMaterial::create([
                    'product_id' => $product->id,
                    'qty' => $req->qty,
                ]);
                (new Branch)->assign(FactoryRawMaterial::class, $frm->id);
            }

            $product->qty -= $req->qty;
            $product->save();

            DB::commit();

            return back()->with('success', 'Product transferred');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);
            return back()->with('error', 'Something went wrong. Please contact administrator')->withInput();
        }
    }

    public function transferToWarehouse(Request $req)
    {
        $product = Product::where('id', $req->product_id)->first();
        $frm = FactoryRawMaterial::where('id', $req->frm_id)->first();
        if ($req->qty <= 0) {
            return back()->with('warning', 'The quantity must not greater than 0')->withInput();
        }
        if ($frm->remainingQty() < $req->qty) {
            return back()->with('warning', 'The quantity must not greater than ' . $frm->remainingQty())->withInput();
        }

        try {
            DB::beginTransaction();

            $approval = Approval::create([
                'object_type' => FactoryRawMaterial::class,
                'object_id' => $frm->id,
                'status' => Approval::STATUS_PENDING_APPROVAL,
                'data' => json_encode([
                    'qty' => $req->qty,
                    'description' => Auth::user()->name . ' has requested to transfer ' . $req->qty . ' ' . $product->model_name . ' (' . $product->sku . ')',
                    'user_id' => Auth::user()->id
                ])
            ]);
            (new Branch)->assign(Approval::class, $approval->id);

            $frm->to_warehouse_qty += $req->qty;
            $frm->save();

            DB::commit();

            return back()->with('success', 'Transfer request is created');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);
            return back()->with('error', 'Something went wrong. Please contact administrator')->withInput();
        }
    }

    public function recordUsage(Request $req, FactoryRawMaterial $frm)
    {
        return view('inventory.record_usage', [
            'filter_usage' => Session::get('type') == 'usage' ? true : false,
            'frm' => $frm,
            'date' => now()->format('Y/m/d'),
            'by' => Auth::user()->name,
            'uoms' => UOM::orderBy('name', 'asc')->get(),
            'productions' => Production::orderBy('id', 'desc')->get(),
            'product' => Product::where('id', $frm->product_id)->first(),
        ]);
    }

    public function recordUsageSubmit(Request $req, FactoryRawMaterial $frm)
    {
        // Validate request
        $validator = Validator::make($req->all(), [
            'qty' => 'required|max:250',
            'production_id' => 'nullable',
            'uom' => 'nullable',
            'remark' => 'nullable|max:250',
        ], [], [
            'qty' => 'quantity'
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        if ($req->qty > $frm->qty) {
            throw ValidationException::withMessages([
                'qty' => 'The quantity must not greater than ' . $frm->qty,
            ]);
        }

        try {
            DB::beginTransaction();

            FactoryRawMaterialRecord::create([
                'factory_raw_material_id' => $frm->id,
                'qty' => $req->qty,
                'production_id' => $req->production_id,
                'uom' => $req->uom,
                'done_by' => Auth::user()->id,
                'remark' => $req->remark,
            ]);

            DB::commit();

            return redirect(route('production_material.index'))->with('success', 'Record created');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator')->withInput();
        }
    }

    public function recordUsageGetData(Request $req)
    {
        $records = new FactoryRawMaterialRecord;

        // Search
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];

            $records = $records->where(function ($q) use ($keyword) {
                $q->where('qty', 'like', '%' . $keyword . '%');
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
                'no' => $key + 1,
                'qty' => $record->qty,
                'production' => $record->production->sku ?? null,
                'uom' => $record->uomObj->name ?? null,
                'date' => Carbon::parse($record->created_at)->format('Y m d H:i'),
                'done_by' => $record->doneBy->name ?? null,
                'remark' => $record->remark ?? null,
            ];
        }

        return response()->json($data);
    }
}
