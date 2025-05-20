<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\InventoryCategory;
use App\Models\Milestone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
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
        return view('milestone.list');
    }

    public function getData(Request $req)
    {
        $records = $this->ms::where('type', Milestone::TYPE_PRODUCTION)
            ->whereNotNull('batch');

        // Search
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];

            $records = $records->where(function ($q) use ($keyword) {
                $q->orWhereHas('inventoryType', function ($q) use ($keyword) {
                    $q->where('name', 'like', '%' . $keyword . '%');
                });
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
        $records = $records->groupBy('batch');

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
            $categories = InventoryCategory::whereIn('id', explode(',', $record->inventory_category_id))->pluck('name')->toArray();

            $data['data'][] = [
                'category' => join(', ', $categories),
                'type' => $record->inventoryType->name,
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
            $batch = Milestone::max('batch') ?? 1;

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
            $batch = Milestone::max('batch') ?? 1;

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

    public function get($category_id, $type_id)
    {
        $milestones = Milestone::where('type', Milestone::TYPE_PRODUCTION)
            ->whereRaw('FIND_IN_SET(?, inventory_category_id)', [$category_id])
            ->where('inventory_type_id', $type_id)
            ->orderBy('id', 'desc')
            ->get();

        return Response::json([
            'milestones' => $milestones,
        ]);
    }
}
