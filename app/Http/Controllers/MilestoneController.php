<?php

namespace App\Http\Controllers;

use App\Models\Branch;
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
        $records = $this->ms::where('type', Milestone::TYPE_PRODUCTION)->whereNotNull('inventory_category_id')->whereNotNull('inventory_type_id');

        // Search
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];

            $records = $records->whereHas('inventoryCategory', function ($q) use ($keyword) {
                $q->where('name', 'like', '%' . $keyword . '%');
            })
                ->orWhereHas('inventoryType', function ($q) use ($keyword) {
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
        $records = $records->groupBy('inventory_category_id')->groupBy('inventory_type_id');

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
                'category' => $record->inventoryCategory->name,
                'type' => $record->inventoryType->name,
                'category_id' => $record->inventory_category_id,
                'type_id' => $record->inventory_type_id,
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

            for ($i = 0; $i < count($milestones); $i++) {
                $ms = $this->ms::create([
                    'name' => $milestones[$i],
                    'type' => Milestone::TYPE_PRODUCTION,
                    'is_custom' => true,
                    'inventory_category_id' => $req->category,
                    'inventory_type_id' => $req->type,

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

    public function edit($category_id, $type_id)
    {
        $milestones = Milestone::where('inventory_category_id', $category_id)->where('inventory_type_id', $type_id)->orderBy('id', 'desc')->get();

        return view('milestone.form', [
            'category_id' => $category_id,
            'type_id' => $type_id,
            'milestones' => $milestones
        ]);
    }

    public function update(Request $req, $category_id, $type_id)
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
            Milestone::where('inventory_category_id', $category_id)->where('inventory_type_id', $type_id)->delete();

            // Create
            $milestones = explode(',', $req->milestones);

            for ($i = 0; $i < count($milestones); $i++) {
                $ms = $this->ms::create([
                    'name' => $milestones[$i],
                    'type' => Milestone::TYPE_PRODUCTION,
                    'is_custom' => true,
                    'inventory_category_id' => $req->category,
                    'inventory_type_id' => $req->type,

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
            ->where('inventory_category_id', $category_id)
            ->where('inventory_type_id', $type_id)
            ->orderBy('id', 'desc')
            ->get();

        return Response::json([
            'milestones' => $milestones,
        ]);
    }
}
