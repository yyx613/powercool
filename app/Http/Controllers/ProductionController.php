<?php

namespace App\Http\Controllers;

use App\Exports\ProductionExport;
use App\Models\Approval;
use App\Models\Branch;
use App\Models\MaterialUse;
use App\Models\MaterialUseProduct;
use App\Models\Milestone;
use App\Models\Product;
use App\Models\ProductChild;
use App\Models\Production;
use App\Models\ProductionDueDate;
use App\Models\ProductionMilestone;
use App\Models\ProductionMilestoneMaterial;
use App\Models\ProductionMilestoneMaterialPreview;
use App\Models\ProductionMilestoneReject;
use App\Models\RawMaterialRequest;
use App\Models\RawMaterialRequestMaterial;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SaleProduct;
use App\Models\SaleProductChild;
use App\Models\Scopes\BranchScope;
use App\Models\User;
use App\Models\UserProduction;
use App\Notifications\ProductionCompleteNotification;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;
use Picqer\Barcode\Renderers\DynamicHtmlRenderer;
use Picqer\Barcode\Types\TypeCode128;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

class ProductionController extends Controller
{
    protected $prod;
    protected $userProd;
    protected $ms;
    protected $prodMs;
    protected $prodMsReject;
    protected $prodMsMaterial;
    protected $prodMsMaterialPreview;
    protected $product;
    protected $productChild;

    public function __construct()
    {
        $this->prod = new Production;
        $this->userProd = new UserProduction;
        $this->ms = new Milestone;
        $this->prodMs = new ProductionMilestone;
        $this->prodMsReject = new ProductionMilestoneReject;
        $this->prodMsMaterial = new ProductionMilestoneMaterial;
        $this->prodMsMaterialPreview = new ProductionMilestoneMaterialPreview;
        $this->product = new Product;
        $this->productChild = new ProductChild;
    }

    public function index(Request $req)
    {
        Session::remove('production-type');
        if ($req->type != null) {
            Session::put('production-type', $req->type);
        }

        if (isProductionWorker()) {
            $all_count = $this->prod->whereHas('users', function ($q) {
                $q->where('user_id', Auth::user()->id);
            })->count();
            $to_do_count = $this->prod->whereHas('users', function ($q) {
                $q->where('user_id', Auth::user()->id);
            })->where('status', $this->prod::STATUS_TO_DO)->count();
            $doing_count = $this->prod->whereHas('users', function ($q) {
                $q->where('user_id', Auth::user()->id);
            })->where('status', $this->prod::STATUS_DOING)->count();
            $completed_count = $this->prod->whereHas('users', function ($q) {
                $q->where('user_id', Auth::user()->id);
            })->where('status', $this->prod::STATUS_COMPLETED)->count();
        } else {
            $all_count = $this->prod->count();
            $to_do_count = $this->prod->where('status', $this->prod::STATUS_TO_DO)->count();
            $doing_count = $this->prod->where('status', $this->prod::STATUS_DOING)->count();
            $completed_count = $this->prod->where('status', $this->prod::STATUS_COMPLETED)->count();
        }

        $can_start_task = count(array_intersect([Role::SUPERADMIN, Role::PRODUCTION_SUPERVISOR], getUserRoleId(Auth::user()))) > 0;

        return view('production.list', [
            'productin_left' => $this->prod::where('due_date', now()->format('Y-m-d'))->count(),
            'all' => $all_count,
            'to_do' => $to_do_count,
            'doing' => $doing_count,
            'completed' => $completed_count,
            'can_start_task' => $can_start_task,
            'is_sales_only' => isSalesOnly(),
        ]);
    }

    public function getData(Request $req)
    {
        $records = $this->prod;

        $is_production_worker = isProductionWorker();
        if ($is_production_worker) {
            $records = $records->whereHas('users', function ($q) {
                $q->where('user_id', Auth::user()->id);
            });
        }

        $type = Session::get('production-type');
        if ($type != null) {
            if ($type == 'new') {
                $records = $records->where('status', $this->prod::STATUS_TO_DO);
            } else if ($type == 'in-progress') {
                $records = $records->where('status', $this->prod::STATUS_DOING);
            } else if ($type == 'completed') {
                $records = $records->where('status', $this->prod::STATUS_COMPLETED);
            }
        }

        // Search
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];

            $records = $records->where(function ($q) use ($keyword) {
                $q->where('sku', 'like', '%' . $keyword . '%')
                    ->orWhere('name', 'like', '%' . $keyword . '%')
                    ->orWhere('start_date', 'like', '%' . $keyword . '%')
                    ->orWhere('due_date', 'like', '%' . $keyword . '%')
                    ->orWhereHas('priority', function ($q) use ($keyword) {
                        $q->where('name', 'like', '%' . $keyword . '%');
                    })
                    ->orWhereHas('productChild', function ($q) use ($keyword) {
                        $q->where('sku', 'like', '%' . $keyword . '%');
                    });
            });
        }
        // Order
        if ($req->has('order')) {
            $map = [
                1 => 'sku',
                5 => 'start_date',
                6 => 'due_date',
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
                'sku' => $record->sku,
                'old_production_sku' => $record->oldProduction->sku ?? null,
                'factory' => $record->product->category->fromFactory->name ?? null,
                'product_serial_no' => $record->productChild->sku ?? null,
                'name' => $record->name,
                'start_date' => $record->start_date,
                'due_date' => $record->due_date,
                'days_left' => Carbon::parse($record->due_date)->addDay()->diffInDays(now()),
                'progress' => $record->getProgress($record),
                'request_status' => $record->rawMaterialRequest->status ?? null,
                'status' => $record->status,
                'priority' => $record->priority,
                'can_edit' => hasPermission('production.edit') && $record->status == Production::STATUS_TO_DO,
                'can_delete' => hasPermission('production.delete'),
                'can_duplicate' => !$is_production_worker,
                'can_view' => !$is_production_worker || $record->status == Production::STATUS_DOING,
            ];
        }

        return response()->json($data);
    }

    public function create(Request $req)
    {
        $data = [];
        // Duplicate
        if ($req->id != null) {
            $production = $this->prod::where('id', $req->id)->first();
            $production->load('users', 'milestones');

            $data = [
                'production' => $production,
                'is_duplicate' => true,
            ];

            if ($req->is_modify != null) {
                $data['modify_from'] = $req->id;
            }
        }
        if ($req->sale_id != null && $req->product_id != null) {
            $sale = Sale::where('id', $req->sale_id)->first();
            $product = $this->product::where('id', $req->product_id)->first();
            $customer_name = $sale->customer->company_name ?? null;

            $data = [
                'default_product' => $product,
                'default_sale' => $sale,
                'default_start_date' => Carbon::parse($sale->created_at)->format('Y-m-d'),
                'default_due_date' => Carbon::parse($sale->created_at)->addWeekdays(3)->format('Y-m-d'),
                'customer_name' => $customer_name
            ];
        }

        return view('production.form', $data);
    }

    public function edit(Production $production)
    {
        if ($production->status == $this->prod::STATUS_TRANSFERRED) {
            return redirect(route('production.index'))->with('warning', 'Not allow to edit.');
        }

        $production->load('users');
        $production->load(['milestones' => function ($q) {
            $q->withTrashed();
        }]);

        $production_milestone_ids = $this->prodMs::where('production_id', $production->id)->pluck('id');
        $production_milestone_material_previews = $this->prodMsMaterialPreview::whereIn('production_milestone_id', $production_milestone_ids)->get();

        return view('production.form', [
            'production' => $production,
            'production_milestone_material_previews' => $production_milestone_material_previews,
        ]);
    }

    public function view(Production $production)
    {
        $production->load('users', 'product', 'dueDates');
        $production->load(['milestones' => function ($q) {
            $q->withTrashed();
        }]);
        $production->formatted_created_at = Carbon::parse($production->created_at)->format('d M Y');
        $production->status = ($this->prod)->statusToHumanRead($production->status);
        $production->progress = ($this->prod)->getProgress($production);

        $pm_ids = $this->prodMs::where('production_id', $production->id)->pluck('id');

        $involved_pc_ids = getInvolvedProductChild($production->id);

        $production->milestones->each(function ($q) use ($involved_pc_ids, $production) {
            $prodMs = $this->prodMs::where('production_id', $production->id)->where('milestone_id', $q->id)->first();
            $previews = $this->prodMsMaterialPreview::where('production_milestone_id', $prodMs->id)->get();

            $preview = [];
            for ($i = 0; $i < count($previews); $i++) {
                $product = Product::where('id', $previews[$i]->product_id)->first();
                $preview[] = [
                    'product' => $product,
                    'qty' => $previews[$i]->qty,
                    'children' => ProductChild::where('product_id', $product->id)
                        ->where(function ($q) {
                            $q->whereNull('status')->orWhere(function ($q) {
                                $q->where('status', ProductChild::STATUS_STOCK_OUT)->where('stock_out_to_type', Production::class);
                            });
                        })
                        ->whereNotIn('id', $involved_pc_ids)
                        ->where('location', ProductChild::LOCATION_FACTORY)
                        ->get(),
                ];
            }
            $q->preview = $preview;
            $q->pivot->submittedBy = $q->pivot->submitted_by == null ? null : User::withoutGlobalScope(BranchScope::class)->where('id', $q->pivot->submitted_by)->value('name');
            $q->pivot->submitted_at = $q->pivot->submitted_at == null ? null : Carbon::parse($q->pivot->submitted_at)->format('d M Y H:i');
            $q->pivot->rejects = $this->prodMsReject::with('rejectedBy', 'submittedBy', 'milestoneMaterials.product', 'milestoneMaterials.productChild.parent')->where('production_milestone_id', $q->pivot->id)->orderBy('id', 'desc')->get();
        });

        // Production Milestone Materials
        $pmms = $this->prodMsMaterial::whereIn('production_milestone_id', $pm_ids)->whereNotNull('product_child_id')->get();

        $production_milestone_materials = [];
        for ($i = 0; $i < count($pmms); $i++) {
            $production_milestone_materials[$pmms[$i]->production_milestone_id][] = $pmms[$i]->product_child_id;
        }

        $material_use = MaterialUse::with('materials.material')->where('product_id', $production->product_id)->first();

        $can_extend_due_date = now()->isBefore($production->due_date);

        return view('production.view', [
            'production' => $production,
            'production_milestone_materials' => $production_milestone_materials,
            'material_use' => $material_use,
            'can_extend_due_date' => $can_extend_due_date,
            'is_production_worker' => isProductionWorker()
        ]);
    }

    public function delete(Production $production)
    {
        if ($production->status == $this->prod::STATUS_TRANSFERRED) {
            return redirect(route('production.index'))->with('warning', 'Not allow to delete.');
        }

        try {
            DB::beginTransaction();

            $product_child_id = $production->product_child_id;

            $pm_ids = $this->prodMs::where('production_id', $production->id)->pluck('id');
            $this->prodMsMaterial::whereIn('production_milestone_id', $pm_ids)->delete();
            $production->delete();

            SaleProductChild::where('product_children_id', $product_child_id)->delete();
            ProductChild::where('id', $product_child_id)->delete();

            DB::commit();

            return back()->with('success', 'Production deleted');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator');
        }
    }

    public function upsert(Request $req, Production $production)
    {
        $rules = [
            'name' => 'required|max:250',
            'desc' => 'nullable|max:250',
            'remark' => 'nullable|max:250',
            'start_date' => 'required',
            'due_date' => 'required',
            'status' => 'required',
            'product' => 'required',
            'order' => 'nullable',
            'priority' => 'nullable',
            'assign' => 'required',
            'assign.*' => 'exists:users,id',
            'material_use_product' => 'required',
        ];
        // Validate request
        $req->validate($rules, [], [
            'desc' => 'description',
            'material_use_product' => 'milestone'
        ]);

        try {
            DB::beginTransaction();

            $now = now();

            if ($production->id == null) {
                $production = $this->prod::create([
                    'sku' => $this->prod->generateSku(),
                    'product_id' => $req->product,
                    'sale_id' => $req->order,
                    'name' => $req->name,
                    'desc' => $req->desc,
                    'remark' => $req->remark,
                    'start_date' => $req->start_date,
                    'due_date' => $req->due_date,
                    'status' => $req->status,
                    'priority_id' => $req->priority,
                    'old_production' => $req->modify_from == null ? null : $req->modify_from,
                ]);
                if ($req->modify_from != null) {
                    $this->prod::where('id', $req->modify_from)->update([
                        'status' => Production::STATUS_MODIFIED,
                    ]);
                }
                (new Branch)->assign(Production::class, $production->id);
            } else {
                $production->update([
                    'product_id' => $req->product,
                    'sale_id' => $req->order,
                    'name' => $req->name,
                    'desc' => $req->desc,
                    'remark' => $req->remark,
                    'start_date' => $req->start_date,
                    'due_date' => $req->due_date,
                    'status' => $req->status,
                    'priority_id' => $req->priority,
                ]);
            }
            $this->product::where('id', $req->product)->update([
                'in_production' => true,
            ]);

            // Assign
            $this->userProd::where('production_id', $production->id)->whereNotIn('user_id', $req->assign ?? [])->delete();
            foreach ($req->assign as $assign_id) {
                $up = $this->userProd::where('production_id', $production->id)->where('user_id', $assign_id)->first();
                if ($up == null) {
                    $this->userProd::create([
                        'user_id' => $assign_id,
                        'production_id' => $production->id,
                    ]);
                }
            }

            // Milestone
            $old_ms_ids = [];
            $milestones = json_decode($req->material_use_product);
            for ($i = 0; $i < count($milestones); $i++) {
                if (!$milestones[$i]->is_custom) {
                    $old_ms_ids[] = $milestones[$i]->id;
                }
            }

            // Create milestone
            $this->prodMs::where('production_id', $production->id)->whereNotIn('milestone_id', $old_ms_ids ?? [])->delete();
            for ($k = 0; $k < count((array) $milestones); $k++) {
                for ($i = 0; $i < count($milestones); $i++) {
                    if ($milestones[$i]->is_custom == true || $k + 1 != $milestones[$i]->sequence) { // Allow for non-custom and sequence matched
                        continue;
                    }

                    $ms = $this->prodMs::where('production_id', $production->id)->where('milestone_id', $milestones[$i]->id)->first();
                    if ($ms == null) {
                        $ms = $this->prodMs::create([
                            'production_id' => $production->id,
                            'milestone_id' => $milestones[$i]->id,
                        ]);
                    }
                    $ms_id = $this->prodMs::where('production_id', $ms->production_id)->where('milestone_id', $ms->milestone_id)->value('id');

                    $ms_material_use_product = [];
                    if ($milestones[$i]->id == ($ms == null ? $ms_id : $ms->milestone_id)) {
                        for ($j = 0; $j < count($milestones[$i]->value); $j++) {
                            $material_use_id = MaterialUse::where('product_id', $req->product)->value('id');

                            $ms_material_use_product[] = [
                                'production_milestone_id' => $ms_id,
                                'product_id' => $milestones[$i]->value[$j],
                                'qty' => MaterialUseProduct::where('material_use_id', $material_use_id)->where('product_id', $milestones[$i]->value[$j])->value('qty'),
                                'created_at' => $now,
                            ];
                        }
                    }
                    if ($ms == null) {
                        if (count($ms_material_use_product) > 0) {
                            $this->prodMsMaterialPreview::insert($ms_material_use_product);
                        }
                    } else {
                        $this->prodMsMaterialPreview::where('production_milestone_id', $ms_id)->delete();
                        $this->prodMsMaterialPreview::insert($ms_material_use_product);
                    }
                    break;
                }
            }

            // Create custom milestones
            for ($k = 0; $k < count((array) $milestones); $k++) {
                for ($i = 0; $i < count($milestones); $i++) {
                    if ($milestones[$i]->is_custom == false || $k + 1 != $milestones[$i]->sequence) { // Allow for custom and sequence matched
                        continue;
                    }

                    $custom_ms = $this->ms::create([
                        'type' => $this->ms::TYPE_PRODUCTION,
                        'name' => $milestones[$i]->title,
                        'is_custom' => true,
                        'product_id' => $req->product,
                    ]);
                    $pms = $this->prodMs::create([
                        'production_id' => $production->id,
                        'milestone_id' => $custom_ms->id,
                    ]);

                    $ms_material_use_product = [];
                    for ($j = 0; $j < count($milestones[$i]->value); $j++) {
                        $material_use_id = MaterialUse::where('product_id', $req->product)->value('id');

                        $ms_material_use_product[] = [
                            'production_milestone_id' => $this->prodMs::where('production_id', $production->id)->where('milestone_id', $custom_ms->id)->value('id'),
                            'product_id' => $milestones[$i]->value[$j],
                            'qty' => MaterialUseProduct::where('material_use_id', $material_use_id)->where('product_id', $milestones[$i]->value[$j])->value('qty'),
                            'created_at' => $now,
                        ];
                    }

                    if (count($ms_material_use_product) > 0) {
                        $this->prodMsMaterialPreview::insert($ms_material_use_product);
                    }
                    break;
                }
            }
            // Create Raw Material Request
            $material_use = MaterialUse::with('materials')->where('product_id', $req->product)->first();
            $rmq = RawMaterialRequest::create([
                'production_id' => $production->id,
                'material_use_id' => $material_use->id,
                'status' => RawMaterialRequest::STATUS_IN_PROGRESS,
                'requested_by' => Auth::user()->id,
            ]);
            (new Branch)->assign(RawMaterialRequest::class, $rmq->id);

            $data = [];
            for ($i = 0; $i < count($material_use->materials); $i++) {
                $data[] = [
                    'raw_material_request_id' => $rmq->id,
                    'product_id' => $material_use->materials[$i]->product_id,
                    'status' => RawMaterialRequestMaterial::MATERIAL_STATUS_IN_PROGRESS,
                    'qty' => $material_use->materials[$i]->material->is_sparepart ? 1 : $material_use->materials[$i]->qty,
                    'created_at' => now(),
                ];
            }
            RawMaterialRequestMaterial::insert($data);

            DB::commit();

            return redirect(route('production.index'))->with('success', isset($prod) ? 'Production created' : 'Production updated');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator')->withInput();
        }
    }

    public function checkInMilestone(Request $req)
    {
        // Check in is allowed only when production status is 'doing'
        $pm = $this->prodMs::where('id', $req->production_milestone_id)->first();
        $prod = $this->prod::where('id', $pm->production_id)->first();
        if (strtolower($this->prod->statusToHumanRead($prod->status)) != 'doing') {
            return Response::json([
                'errors' => [
                    'general' => 'Milestone is not allow to check in',
                ],
            ], HttpFoundationResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $rules = [
            'production_milestone_id' => 'required',
            'datetime' => 'required',
            'materials' => 'nullable',
            'materials.*' => 'nullable',
        ];
        // Validate request
        $req->validate($rules);

        // Validate serial no
        $prodMsMaterialPreview = $this->prodMsMaterialPreview::where('production_milestone_id', $pm->id)->get();

        if (count($prodMsMaterialPreview) > 0) {
            $batch_serial_no_ids = [];
            $product_ids = [];

            for ($i = 0; $i < count($prodMsMaterialPreview); $i++) {
                $product_ids[] = $prodMsMaterialPreview[$i]->product_id;
            }

            // If no material serial no is provided
            $products = Product::whereIn('id', $product_ids)->get();

            $has_spare_part = false;
            for ($i = 0; $i < count($products); $i++) {
                if ($products[$i]->is_sparepart == true) {
                    $has_spare_part = true;
                    break;
                }
            }
            if ($has_spare_part == true && $req->materials == null) {
                return Response::json([
                    'errors' => [
                        'materials' => "Material's serial no is required",
                    ],
                ], HttpFoundationResponse::HTTP_UNPROCESSABLE_ENTITY);
            }
            // If qty is not tally
            for ($i = 0; $i < count($products); $i++) {
                if ($products[$i]->is_sparepart == true) {
                    $qty_needed = 0;
                    for ($j = 0; $j < count($prodMsMaterialPreview); $j++) {
                        if ($prodMsMaterialPreview[$j]->product_id == $products[$i]->id) {
                            $qty_needed = $prodMsMaterialPreview[$j]->qty;
                            break;
                        }
                    }

                    if (! isset($req->materials[$products[$i]->id])) {
                        return Response::json([
                            'errors' => [
                                'materials.' . $products[$i]->id => "Material's serial no is required",
                            ],
                        ], HttpFoundationResponse::HTTP_UNPROCESSABLE_ENTITY);
                    } elseif ($qty_needed != count($req->materials[$products[$i]->id])) {
                        return Response::json([
                            'errors' => [
                                'materials.' . $products[$i]->id => 'Quantity needed is not tally',
                            ],
                        ], HttpFoundationResponse::HTTP_UNPROCESSABLE_ENTITY);
                    }
                    $batch_serial_no_ids = array_merge($batch_serial_no_ids, $req->materials[$products[$i]->id]);
                }
            }
        }

        try {
            DB::beginTransaction();

            if ($pm->submitted_at == null) {
                $pm->submitted_at = Carbon::parse($req->datetime)->format('Y-m-d H:i:s');
                $pm->submitted_by = Auth::user()->id;
                $pm->save();
            }

            $prod = $this->prod::where('id', $pm->production_id)->first();
            if ($this->prod->getProgress($prod) >= 100) {
                $prod->status = $this->prod::STATUS_COMPLETED;
                $prod->save();
            } elseif ($this->prod->getProgress($prod) > 0) {
                $prod->status = $this->prod::STATUS_DOING;
                $prod->save();
            }

            // Materials
            $now = now();
            if (count($prodMsMaterialPreview) > 0) {
                for ($i = 0; $i < count($products); $i++) {
                    $data = [];

                    if ($products[$i]->is_sparepart == true && isset($req->materials[$products[$i]->id])) {
                        $this->prodMsMaterial::where('production_milestone_id', $pm->id)
                            ->whereNull('product_id')
                            ->whereNotIn('product_child_id', $batch_serial_no_ids)
                            ->delete();

                        for ($j = 0; $j < count($req->materials[$products[$i]->id]); $j++) {
                            $pmm = $this->prodMsMaterial::where('production_milestone_id', $pm->id)
                                ->where('product_child_id', $req->materials[$products[$i]->id][$j])
                                ->first();
                            if ($pmm == null) {
                                $data[] = [
                                    'production_milestone_id' => $pm->id,
                                    'product_child_id' => $req->materials[$products[$i]->id][$j],
                                    'created_at' => $now,
                                    'updated_at' => $now,
                                ];
                            }
                        }
                    } elseif ($products[$i]->is_sparepart == false) {
                        $pmm = $this->prodMsMaterial::where('production_milestone_id', $pm->id)
                            ->where('product_id', $products[$i]->id)
                            ->first();

                        if ($pmm == null) {
                            $qty = 0;
                            for ($j = 0; $j < count($prodMsMaterialPreview); $j++) {
                                if ($prodMsMaterialPreview[$j]->product_id == $products[$i]->id) {
                                    $qty = $prodMsMaterialPreview[$j]->qty;
                                    break;
                                }
                            }

                            $data[] = [
                                'production_milestone_id' => $pm->id,
                                'product_id' => $products[$i]->id,
                                'qty' => $qty,
                                'created_at' => $now,
                                'updated_at' => $now,
                            ];
                        }
                    }

                    if (count($data) > 0) {
                        $this->prodMsMaterial::insert($data);
                    }
                }

                // foreach ($pm->production->product->materialUse->materials as $key => $material) {
                //     if ($material->material->is_sparepart == true && isset($req->materials[$material->id])) {
                //         $this->prodMsMaterial::where('production_milestone_id', $pm->id)
                //             ->whereNull('product_id')
                //             ->whereNotIn('product_child_id', $req->materials[$material->id])
                //             ->delete();

                //         for ($i=0; $i < count($req->materials[$material->id]); $i++) {
                //             $pmm = $this->prodMsMaterial::where('production_milestone_id', $pm->id)->where('product_child_id', $req->materials[$material->id][$i])->first();
                //             if ($pmm == null) {
                //                 $data[] = [
                //                     'production_milestone_id' => $pm->id,
                //                     'product_child_id' => $req->materials[$material->id][$i],
                //                     'created_at' => $now,
                //                     'updated_at' => $now,
                //                 ];
                //             }
                //         }
                //     } else if ($material->material->is_sparepart == false) {
                //         $pmm = $this->prodMsMaterial::where('production_milestone_id', $pm->id)
                //             ->where('product_id', $material->product_id)
                //             ->first();

                //         if ($pmm == null) {
                //             $data[] = [
                //                 'production_milestone_id' => $pm->id,
                //                 'product_id' => $material->product_id,
                //                 'qty' => $material->qty,
                //                 'created_at' => $now,
                //                 'updated_at' => $now,
                //             ];
                //         }
                //     }

                //     if (count($data) > 0) {
                //         $this->prodMsMaterial::insert($data);
                //     }
                // }
            }
            // Remove on hold if it's last milestone
            $is_last_milestone = ProductionMilestone::where('production_id', $pm->production->id)->whereNull('submitted_at')->count() == 0;
            if ($is_last_milestone) {
                $production_ms_ids = $this->prodMs::where('production_id', $pm->production->id)->pluck('id');

                $this->prodMsMaterial::whereIn('production_milestone_id', $production_ms_ids)->update([
                    'on_hold' => false,
                ]);
            }

            if ($this->prod->getProgress($prod) >= 100) {
                // Send notification
                $receivers = User::withoutGlobalScope(BranchScope::class)->whereHas('roles.permissions', function ($q) {
                    $q->whereIn('name', ['notification.production_complete_notification']);
                })->get();

                Notification::send($receivers, new ProductionCompleteNotification([
                    'production_id' => $prod->id,
                    'desc' => 'The production is completed',
                ]));
            }

            DB::commit();

            return Response::json([
                'result' => true,
                // 'status' => $this->prod->statusToHumanRead($prod->status),
                // 'progress' => $this->prod->getProgress($prod),
            ], HttpFoundationResponse::HTTP_OK);
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return Response::json([
                'result' => false,
            ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function rejectMilestone(Request $req)
    {
        // Check if reject reason is ticked, when it is spare part
        $pc_ids = $this->prodMsMaterial::where('production_milestone_id', $req->production_milestone_id)->whereNotNull('product_child_id')->pluck('product_child_id')->toArray();
        for ($i = 0; $i < count($pc_ids); $i++) {
            if ($req->{'product-child-' . $pc_ids[$i]} == null) {
                $pc = ProductChild::where('id', $pc_ids[$i])->first();

                return Response::json([
                    'errors' => [
                        'materials.' . $pc->parent->id => "Please fill up all reason",
                    ],
                ], HttpFoundationResponse::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        try {
            DB::beginTransaction();

            $pms = $this->prodMs::where('id', $req->production_milestone_id)->first();

            $pms_reject = $this->prodMsReject::create([
                'production_milestone_id' => $req->production_milestone_id,
                'submitted_by' => $pms->submitted_by,
                'submitted_at' => $pms->submitted_at,
                'rejected_by' => Auth::user()->id,
                'remark' => $req->remark ?? null,
            ]);
            for ($i = 0; $i < count($pc_ids); $i++) {
                $this->prodMsMaterial::where('production_milestone_id', $req->production_milestone_id)->where('product_child_id', $pc_ids[$i])->update([
                    'reject_reason' => $req->{'product-child-' . $pc_ids[$i]},
                ]);
                if ($req->{'product-child-' . $pc_ids[$i]} == 'broken') {
                    ProductChild::where('id', $pc_ids[$i])->update([
                        'status' => ProductChild::STATUS_BROKEN,
                    ]);
                }
            }
            $this->prodMsMaterial::where('production_milestone_id', $req->production_milestone_id)->update([
                'production_milestone_reject_id' => $pms_reject->id,
                'deleted_at' => now(),
            ]);

            $pms->submitted_by = null;
            $pms->submitted_at = null;
            $pms->save();

            DB::commit();

            return Response::json([
                'result' => true,
                // 'status' => $this->prod->statusToHumanRead($prod->status),
                // 'progress' => $this->prod->getProgress($prod),
            ], HttpFoundationResponse::HTTP_OK);
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return Response::json([
                'result' => false,
            ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function export()
    {
        return Excel::download(new ProductionExport, 'production.xlsx');
    }

    public function toInProgress(Request $req)
    {
        try {
            DB::beginTransaction();

            $production_ids = explode(',', $req->productionIds);
            $productions = Production::whereIn('id', $production_ids)->where('status', Production::STATUS_TO_DO)->get();

            for ($i = 0; $i < count($productions); $i++) {
                // Create product
                $prod = $this->product::where('id', $productions[$i]->product_id)->first();
                $prodChild = $this->productChild::create([
                    'product_id' => $prod->id,
                    'sku' => ($this->productChild)->generateSku($prod->initial_for_production ?? $prod->sku),
                    'location' => $this->productChild::LOCATION_FACTORY,
                ]);

                $productions[$i]->product_child_id = $prodChild->id;
                $productions[$i]->status = Production::STATUS_DOING;
                $productions[$i]->save();
                // Auto Assign product child to assigned Order
                if ($productions[$i]->sale_id != null) {
                    SaleProductChild::create([
                        'sale_product_id' => SaleProduct::where('sale_id', $productions[$i]->sale_id)->where('product_id', $productions[$i]->product_id)->value('id'),
                        'product_children_id' => $prodChild->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            DB::commit();

            return back()->with('success', 'Production is now in progress');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator')->withInput();
        }
    }

    public function generateBarcode(Request $req)
    {
        $production_ids = explode(',', $req->productionIds);
        $product_child_ids = Production::whereIn('id', $production_ids)->whereNotNull('product_child_id')->pluck('product_child_id');
        $product_children = ProductChild::whereIn('id', $product_child_ids)->get();

        $data = [
            'barcode' => [],
            'renderer' => [],
        ];
        for ($i = 0; $i < count($product_children); $i++) {
            $prod = $product_children[$i]->parent;

            $barcode = (new TypeCode128)->getBarcode($product_children[$i]->sku);

            // Output the barcode as HTML in the browser with a HTML Renderer
            $renderer = new DynamicHtmlRenderer;

            $data['renderer'][] = $renderer->render($barcode);
            $data['product_name'][] = $prod->model_name;
            $data['product_code'][] = $prod->sku;
            $data['barcode'][] = $product_children[$i]->sku;
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

    public function extendDueDate(Request $req, Production $production)
    {
        if ($req->new_due_date == null) {
            return back()->with('warning', 'Please enter the new due date');
        }

        try {
            DB::beginTransaction();

            ProductionDueDate::create([
                'production_id' => $production->id,
                'old_date' => $production->due_date,
                'new_date' => $req->new_due_date,
                'remark' => $req->remark ?? null,
                'done_by' => Auth::user()->id,
            ]);

            $production->due_date = $req->new_due_date;
            $production->save();

            DB::commit();

            return back()->with('success', 'Due date is extended');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator')->withInput();
        }
    }

    public function forceCompleteTask(Request $req, Production $production)
    {
        try {
            DB::beginTransaction();

            $approval = Approval::create([
                'object_type' => Production::class,
                'object_id' => $production->id,
                'status' => Approval::STATUS_PENDING_APPROVAL,
                'data' => json_encode([
                    'description' => Auth::user()->name . ' has requested to complete the production (' . $production->sku . ')',
                    'user_id' => Auth::user()->id,
                ])
            ]);
            (new Branch)->assign(Approval::class, $approval->id);

            $production->status = Production::STATUS_PENDING_APPROVAL;
            $production->save();

            DB::commit();

            return redirect(route('production.view', ['production' => $production->id]))->with('success', 'Complete Task request is created');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator')->withInput();
        }
    }
}
