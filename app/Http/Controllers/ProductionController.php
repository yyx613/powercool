<?php

namespace App\Http\Controllers;

use App\Exports\ProductionExport;
use App\Models\Branch;
use App\Models\MaterialUse;
use App\Models\Milestone;
use App\Models\Product;
use App\Models\ProductChild;
use App\Models\Production;
use App\Models\ProductionMilestone;
use App\Models\ProductionMilestoneMaterial;
use App\Models\ProductionMilestoneMaterialPreview;
use App\Models\Sale;
use App\Models\Scopes\BranchScope;
use App\Models\User;
use App\Models\UserProduction;
use App\Notifications\ProductionCompleteNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

class ProductionController extends Controller
{
    protected $prod;

    protected $userProd;

    protected $ms;

    protected $prodMs;

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
        $this->prodMsMaterial = new ProductionMilestoneMaterial;
        $this->prodMsMaterialPreview = new ProductionMilestoneMaterialPreview;
        $this->product = new Product;
        $this->productChild = new ProductChild;
    }

    public function index()
    {
        return view('production.list', [
            'productin_left' => $this->prod::where('due_date', now()->format('Y-m-d'))->count(),
            'to_do' => $this->prod::where('status', $this->prod::STATUS_TO_DO)->count(),
            'doing' => $this->prod::where('status', $this->prod::STATUS_DOING)->count(),
            'completed' => $this->prod::where('status', $this->prod::STATUS_COMPLETED)->count(),
        ]);
    }

    public function getData(Request $req)
    {
        $records = $this->prod;

        // Search
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];

            $records = $records->where(function ($q) use ($keyword) {
                $q->where('sku', 'like', '%'.$keyword.'%')
                    ->orWhere('name', 'like', '%'.$keyword.'%')
                    ->orWhere('start_date', 'like', '%'.$keyword.'%')
                    ->orWhere('due_date', 'like', '%'.$keyword.'%')
                    ->orWhereHas('priority', function ($q) use ($keyword) {
                        $q->where('name', 'like', '%'.$keyword.'%');
                    })
                    ->orWhereHas('productChild', function ($q) use ($keyword) {
                        $q->where('sku', 'like', '%'.$keyword.'%');
                    });
            });
        }
        // Order
        if ($req->has('order')) {
            $map = [
                0 => 'sku',
                3 => 'name',
                4 => 'start_date',
                5 => 'due_date',
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
                'product_serial_no' => $record->productChild->sku,
                'name' => $record->name,
                'start_date' => $record->start_date,
                'due_date' => $record->due_date,
                'days_left' => Carbon::parse($record->due_date)->addDay()->diffInDays(now()),
                'progress' => $record->getProgress($record),
                'status' => $record->status,
                'priority' => $record->priority,
                'can_edit' => hasPermission('production.edit'),
                'can_delete' => hasPermission('production.delete'),
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

            $data = [
                'default_product' => $product,
                'default_sale' => $sale,
            ];
        }

        return view('production.form', $data);
    }

    public function edit(Production $production)
    {
        if ($production->status == $this->prod::STATUS_TRANSFERRED) {
            return redirect(route('production.index'))->with('warning', 'Not allow to edit.');
        }

        $production->load('users', 'milestones');

        $production_milestone_ids = $this->prodMs::where('production_id', $production->id)->pluck('id');
        $production_milestone_material_previews = $this->prodMsMaterialPreview::whereIn('production_milestone_id', $production_milestone_ids)->get();

        return view('production.form', [
            'production' => $production,
            'production_milestone_material_previews' => $production_milestone_material_previews,
        ]);
    }

    public function view(Production $production)
    {
        $production->load('users', 'milestones', 'product');
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
                    'children' => ProductChild::where('product_id', $product->id)->whereNull('status')->whereNotIn('id', $involved_pc_ids)->where('location', ProductChild::LOCATION_WAREHOUSE)->get(),
                ];
            }
            $q->preview = $preview;
        });

        // Production Milestone Materials
        $pmms = $this->prodMsMaterial::whereIn('production_milestone_id', $pm_ids)->whereNotNull('product_child_id')->get();

        $production_milestone_materials = [];
        for ($i = 0; $i < count($pmms); $i++) {
            $production_milestone_materials[$pmms[$i]->production_milestone_id][] = $pmms[$i]->product_child_id;
        }

        return view('production.view', [
            'production' => $production,
            'production_milestone_materials' => $production_milestone_materials,
        ]);
    }

    public function delete(Production $production)
    {
        if ($production->status == $this->prod::STATUS_TRANSFERRED) {
            return redirect(route('production.index'))->with('warning', 'Not allow to delete.');
        }

        try {
            DB::beginTransaction();

            $pm_ids = $this->prodMs::where('production_id', $production->id)->pluck('id');
            $this->prodMsMaterial::whereIn('production_milestone_id', $pm_ids)->delete();
            $production->delete();

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
            'desc' => 'required|max:250',
            'remark' => 'nullable|max:250',
            'start_date' => 'required',
            'due_date' => 'required',
            'status' => 'required',
            'product' => 'required',
            'order' => 'nullable',
            'priority' => 'nullable',
            'assign' => 'required',
            'assign.*' => 'exists:users,id',
            'milestone' => 'required_without:custom_milestone',
            'custom_milestone' => 'required_without:milestone',
            'material_use_product' => 'nullable',
        ];
        // Validate request
        $req->validate($rules, [], [
            'desc' => 'description',
        ]);

        try {
            DB::beginTransaction();

            $now = now();

            if ($production->id == null) {
                // Create product
                $prod = $this->product::where('id', $req->product)->first();
                $prodChild = $this->productChild::create([
                    'product_id' => $prod->id,
                    'sku' => ($this->productChild)->generateSku($prod->initial_for_production ?? $prod->sku),
                    'location' => $this->productChild::LOCATION_FACTORY,
                ]);

                $production = $this->prod::create([
                    'sku' => $this->prod->generateSku(),
                    'product_id' => $req->product,
                    'product_child_id' => $prodChild->id,
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

            $material_use_product = $req->material_use_product == null ? null : json_decode($req->material_use_product);
            // Create milestone
            $this->prodMs::where('production_id', $production->id)->whereNotIn('milestone_id', $req->milestone ?? [])->delete();
            if ($req->milestone != null) {
                foreach ($req->milestone as $key => $ms_id) {
                    $ms = $this->prodMs::where('production_id', $production->id)->where('milestone_id', $ms_id)->first();

                    if ($ms == null) {
                        $ms = $this->prodMs::create([
                            'production_id' => $production->id,
                            'milestone_id' => $ms_id,
                        ]);
                    }

                    $ms_material_use_product = [];
                    for ($i = 0; $i < count($material_use_product); $i++) {
                        if ($material_use_product[$i]->is_custom == false && $material_use_product[$i]->id == ($ms == null ? $ms_id : $ms->milestone_id)) {
                            for ($j = 0; $j < count($material_use_product[$i]->value); $j++) {
                                $ms_material_use_product[] = [
                                    'production_milestone_id' => $ms->id,
                                    'product_id' => $material_use_product[$i]->value[$j]->productId,
                                    'qty' => $material_use_product[$i]->value[$j]->qty,
                                    'created_at' => $now,
                                ];
                            }
                            break;
                        }
                    }

                    if ($ms == null) {
                        if (count($ms_material_use_product) > 0) {
                            $this->prodMsMaterialPreview::insert($ms_material_use_product);
                        }
                    } else {
                        $this->prodMsMaterialPreview::where('production_milestone_id', $ms->id)->delete();
                        $this->prodMsMaterialPreview::insert($ms_material_use_product);
                    }
                }
            }
            // Create custom milestones
            if ($req->custom_milestone != null) {
                foreach ($req->custom_milestone as $key => $ms) {
                    $custom_ms = $this->ms::create([
                        'type' => $this->ms::TYPE_PRODUCTION,
                        'name' => $ms,
                        'is_custom' => true,
                        'product_id' => $req->product,
                    ]);
                    $this->prodMs::create([
                        'production_id' => $production->id,
                        'milestone_id' => $custom_ms->id,
                    ]);

                    $ms_material_use_product = [];
                    for ($i = 0; $i < count($material_use_product); $i++) {
                        if ($material_use_product[$i]->is_custom == true && $material_use_product[$i]->id == ($key + 1)) {
                            for ($j = 0; $j < count($material_use_product[$i]->value); $j++) {
                                $ms_material_use_product[] = [
                                    'production_milestone_id' => $ms->id,
                                    'product_id' => $material_use_product[$i]->value[$j]->productId,
                                    'qty' => $material_use_product[$i]->value[$j]->qty,
                                    'created_at' => $now,
                                ];
                            }
                            break;
                        }
                    }

                    if (count($ms_material_use_product) > 0) {
                        $this->prodMsMaterialPreview::insert($ms_material_use_product);
                    }
                }
            }

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
        $rules = [
            'production_milestone_id' => 'required',
            'datetime' => 'required',
            'materials' => 'nullable',
            'materials.*' => 'nullable',
        ];
        // Validate request
        $req->validate($rules);

        // Validate serial no
        $pm = $this->prodMs::where('id', $req->production_milestone_id)->first();
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
                                'materials.'.$products[$i]->id => "Material's serial no is required",
                            ],
                        ], HttpFoundationResponse::HTTP_UNPROCESSABLE_ENTITY);
                    } elseif ($qty_needed != count($req->materials[$products[$i]->id])) {
                        return Response::json([
                            'errors' => [
                                'materials.'.$products[$i]->id => 'Quantity needed is not tally',
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
                $receivers = User::withoutGlobalScope(BranchScope::class)->whereHas('roles.permissions', function ($q) {
                    $q->whereIn('name', ['production.complete_notification']);
                })->get();

                Notification::send($receivers, new ProductionCompleteNotification([
                    'production_id' => $prod->id,
                    'desc' => 'The production is completed',
                ]));
            }

            DB::commit();

            return Response::json([
                'result' => true,
                'status' => $this->prod->statusToHumanRead($prod->status),
                'progress' => $this->prod->getProgress($prod),
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
}
