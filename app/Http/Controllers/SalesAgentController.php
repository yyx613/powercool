<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\SalesAgent;
use App\Support\TableSearch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
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
        $page = Session::get('sales-agent-page');

        return view('sales_agent.list', [
            'default_page' => $page ?? null,
        ]);
    }

    public function getData(Request $req)
    {
        $records = $this->salesAgent;

        Session::put('sales-agent-page', $req->page);

        // Search
        $keyword = $req->has('search') ? ($req->search['value'] ?? null) : null;
        $records = TableSearch::apply($records, $keyword, [
            'name',
        ], [
            'company_group' => [
                1 => 'Power Cool',
                2 => 'Hi-Ten',
            ],
        ]);
        // Order
        if ($req->has('order')) {
            $map = [
                0 => 'name',
                1 => 'company_group',
            ];
            foreach ($req->order as $order) {
                if (isset($map[$order['column']])) {
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
        $company_group_labels = [
            1 => 'Power Cool',
            2 => 'Hi-Ten',
        ];

        foreach ($records_paginator as $key => $record) {
            $data['data'][] = [
                'id' => $record->id,
                'name' => $record->name,
                'company_group' => $record->company_group ? ($company_group_labels[$record->company_group] ?? null) : null,
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
            'company_group' => 'required|in:1,2',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $sa = $this->salesAgent::create([
                'name' => $req->name,
                'company_group' => $req->company_group,
            ]);
            (new Branch)->assign(SalesAgent::class, $sa->id);

            DB::commit();

            if ($req->create_again == true) {
                return redirect(route('sales_agent.create'))->with('success', __('Sales Agent created'));
            }
            return redirect(route('sales_agent.index'))->with('success', __('Sales Agent created'));
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', __('Something went wrong. Please contact administrator'))->withInput();
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
            'company_group' => 'required|in:1,2',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $agent->update([
                'name' => $req->name,
                'company_group' => $req->company_group,
            ]);

            DB::commit();

            return redirect(route('sales_agent.index'))->with('success', __('Sales Agent updated'));
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', __('Something went wrong. Please contact administrator'))->withInput();
        }
    }
}
