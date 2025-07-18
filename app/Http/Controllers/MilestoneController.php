<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\InventoryCategory;
use App\Models\Milestone;
use App\Models\ProductMilestone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class MilestoneController extends Controller
{
    protected $ms;

    public function __construct()
    {
        $this->ms = new Milestone;
    }

    public function index()
    {
        $page = Session::get('milestone-page');

        return view('milestone.list', [
            'default_page' => $page ?? null,
        ]);
    }

    public function getData(Request $req)
    {
        $records = $this->ms::where('type', Milestone::TYPE_PRODUCTION)
            ->whereNotNull('batch');

        Session::put('milestone-page', $req->page);

        // Search
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];

            $ids = InventoryCategory::where('name', 'like', '%' . $keyword . '%')->pluck('id')->toArray();

            $records = $records->where(function ($q) use ($keyword, $ids) {
                for ($i = 0; $i < count($ids); $i++) {
                    $q->orWhereRaw('FIND_IN_SET(?, inventory_category_id)', [$ids[$i]]);
                }
                $q->orWhereHas('inventoryType', function ($q) use ($keyword) {
                    $q->where('name', 'like', '%' . $keyword . '%');
                });
            });
        }
        // Order
        if ($req->has('order')) {
            $map = [
                1 => 'type',
            ];
            foreach ($req->order as $order) {
                $records = $records->orderBy($map[$order['column']], $order['dir']);
            }
        } else {
            $records = $records->orderBy('id', 'desc');
        }
        $records = $records->groupBy('batch');

        $records_ids = $records->pluck('batch');
        $records_count = count($records_ids);
        $records_paginator = $records->simplePaginate(10);

        $data = [
            "recordsTotal" => $records_count,
            "recordsFiltered" => $records_count,
            "data" => [],
            'records_ids' => $records_ids,
        ];
        foreach ($records_paginator as $key => $record) {
            $categories = InventoryCategory::whereIn('id', explode(',', $record->inventory_category_id))->pluck('name')->toArray();

            $data['data'][] = [
                'category' => join(', ', $categories),
                'type' => $record->inventoryType->name ?? null,
                'batch' => $record->batch,
            ];
        }

        return response()->json($data);
    }

    public function create()
    {
        return view('milestone.form');
    }

    public function store(Request $req)
    {
        // Validate request
        $validator = Validator::make($req->all(), [
            'category' => 'required',
            'type' => 'required',
            'milestones' => 'required',
        ], [], [
            'category' => 'Inventory category',
            'type' => 'Inventory type',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $milestones = explode(',', $req->milestones);
            $batch = Milestone::max('batch') ?? 0;
            $batch++;

            for ($i = 0; $i < count($milestones); $i++) {
                $ms = $this->ms::create([
                    'name' => $milestones[$i],
                    'type' => Milestone::TYPE_PRODUCTION,
                    'is_custom' => true,
                    'inventory_category_id' => join(',', $req->category),
                    'inventory_type_id' => $req->type,
                    'batch' => $batch,
                ]);
                (new Branch)->assign(Milestone::class, $ms->id);
            }

            DB::commit();

            return redirect(route('milestone.index'))->with('success', 'Milestone created');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator')->withInput();
        }
    }

    public function edit($batch)
    {
        $milestones = Milestone::where('batch', $batch)->get();
        $category_ids = explode(',', $milestones[0]->inventory_category_id);
        $type_id = $milestones[0]->inventory_type_id;

        return view('milestone.form', [
            'batch' => $batch,
            'category_ids' => $category_ids,
            'type_id' => $type_id,
            'milestones' => $milestones
        ]);
    }

    public function update(Request $req, $batch)
    {
        // Validate request
        $validator = Validator::make($req->all(), [
            'category' => 'required',
            'type' => 'required',
            'milestones' => 'required',
        ], [], [
            'category' => 'Inventory category',
            'type' => 'Inventory type',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            // Delete old milestone
            Milestone::where('batch', $batch)->delete();

            // Create
            $milestones = explode(',', $req->milestones);
            $batch = Milestone::max('batch') ?? 0;
            $batch++;

            for ($i = 0; $i < count($milestones); $i++) {
                $ms = $this->ms::create([
                    'name' => $milestones[$i],
                    'type' => Milestone::TYPE_PRODUCTION,
                    'is_custom' => true,
                    'inventory_category_id' => join(',', $req->category),
                    'inventory_type_id' => $req->type,
                    'batch' => $batch,
                ]);
                (new Branch)->assign(Milestone::class, $ms->id);
            }

            DB::commit();

            return redirect(route('milestone.index'))->with('success', 'Milestone updated');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator')->withInput();
        }
    }

    public function get(Request $req, $category_id, $type_id)
    {
        $milestones = Milestone::withTrashed()->where(function ($q) use ($category_id, $type_id) {
            $q->where('type', Milestone::TYPE_PRODUCTION)
                ->whereRaw('FIND_IN_SET(?, inventory_category_id)', [$category_id])
                ->where('inventory_type_id', $type_id)
                ->whereNull('deleted_at');
        });

        if ($req->product_id != null) {
            $product_milestones = ProductMilestone::where('product_id', $req->product_id)->get();
            $product_current_milestone_ids = $product_milestones->pluck('milestone_id')->toArray();
            $milestones = $milestones->orWhere(function ($q) use ($product_current_milestone_ids) {
                $q->whereIn('id', $product_current_milestone_ids)->whereNotNull('deleted_at');
            });
        }
        $milestones = $milestones->get();

        // Sort order
        if ($req->product_id != null && isset($product_milestones) && count($product_milestones) > 0) {
            $sorted_milestones = [];

            for ($i = 0; $i < count($product_milestones); $i++) {
                for ($j = 0; $j < count($milestones); $j++) {
                    if ($product_milestones[$i]->milestone_id == $milestones[$j]->id) {
                        $sorted_milestones[] = $milestones[$j];
                        break;
                    }
                }
            }
        }

        return Response::json([
            'milestones' => $sorted_milestones ?? $milestones,
        ]);
    }
}
