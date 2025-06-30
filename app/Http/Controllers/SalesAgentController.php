<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\SalesAgent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SalesAgentController extends Controller
{
    protected $salesAgent;

    public function __construct()
    {
        $this->salesAgent = new SalesAgent();
    }

    public function index()
    {
        return view('sales_agent.list');
    }

    public function getData(Request $req)
    {
        $records = $this->salesAgent;

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
            "recordsTotal" => $records_count,
            "recordsFiltered" => $records_count,
            "data" => [],
            'records_ids' => $records_ids,
        ];
        foreach ($records_paginator as $key => $record) {
            $data['data'][] = [
                'id' => $record->id,
                'name' => $record->name,
            ];
        }

        return response()->json($data);
    }

    public function create()
    {
        return view('sales_agent.form');
    }

    public function store(Request $req)
    {
        // Validate request
        $validator = Validator::make($req->all(), [
            'name' => 'required|max:250',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $sa = $this->salesAgent::create([
                'name' => $req->name,
            ]);
            (new Branch)->assign(SalesAgent::class, $sa->id);

            DB::commit();

            if ($req->create_again == true) {
                return redirect(route('sales_agent.create'))->with('success', 'Sales Agent created');
            }
            return redirect(route('sales_agent.index'))->with('success', 'Sales Agent created');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator')->withInput();
        }
    }

    public function edit(SalesAgent $agent)
    {
        return view('sales_agent.form', [
            'agent' => $agent
        ]);
    }

    public function update(Request $req, SalesAgent $agent)
    {
        // Validate request
        $validator = Validator::make($req->all(), [
            'name' => 'required|max:250',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $agent->update([
                'name' => $req->name,
            ]);

            DB::commit();

            return redirect(route('sales_agent.index'))->with('success', 'Sales Agent updated');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator')->withInput();
        }
    }
}
