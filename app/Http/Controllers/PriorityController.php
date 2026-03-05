<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Priority;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class PriorityController extends Controller
{
    protected $priority;

    public function __construct() {
        $this->priority = new Priority();
    }

    public function index() {
        $page = Session::get('priority-page');

        return view('priority.list', [
            'default_page' => $page ?? null,
        ]);
    }

    public function getData(Request $req) {
        $records = $this->priority;

        Session::put('priority-page', $req->page);

        // Search
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];

            $records = $records->where(function($q) use ($keyword) {
                $q->where('priority', 'like', '%' . $keyword . '%')
                    ->orWhere('name', 'like', '%' . $keyword . '%')
                    ->orWhere('description', 'like', '%' . $keyword . '%')
                    ->orWhere('response_time', 'like', '%' . $keyword . '%');
            });
        }
        // Order
        if ($req->has('order')) {
            $map = [
                0 => 'priority',
                1 => 'name',
                2 => 'description',
                3 => 'response_time',
                4 => 'order',
            ];
            foreach ($req->order as $order) {
                $records = $records->orderBy($map[$order['column']], $order['dir']);
            }
        } else {
            $records = $records->orderBy('order', 'asc')->orderBy('id', 'desc');
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
                'priority' => $record->priority,
                'name' => $record->name,
                'description' => $record->description,
                'response_time' => $record->response_time,
                'order' => $record->order,
            ];
        }
                
        return response()->json($data);
    }

    public function create() {
        return view('priority.form');
    }

    public function store(Request $req) {
        // Validate request
        $validator = Validator::make($req->all(), [
            'priority' => 'required|max:10',
            'name' => 'required|max:250',
            'description' => 'required|max:1000',
            'response_time' => 'required|max:100',
            'order' => 'required|integer|min:1',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $dt = $this->priority::create([
                'priority' => $req->priority,
                'name' => $req->name,
                'description' => $req->description,
                'response_time' => $req->response_time,
                'order' => $req->order ?? 0,
            ]);
            (new Branch)->assign(Priority::class, $dt->id);

            DB::commit();

            if ($req->create_again == true) {
                return redirect(route('priority.create'))->with('success', 'Priority created');
            }
            return redirect(route('priority.index'))->with('success', 'Priority created');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator')->withInput();
        }
    }

    public function edit(Priority $priority) {
        return view('priority.form', [
            'priority' => $priority
        ]);
    }

    public function update(Request $req, Priority $priority) {
        // Validate request
        $validator = Validator::make($req->all(), [
            'priority' => 'required|max:10',
            'name' => 'required|max:250',
            'description' => 'required|max:1000',
            'response_time' => 'required|max:100',
            'order' => 'required|integer|min:1',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $priority->update([
                'priority' => $req->priority,
                'name' => $req->name,
                'description' => $req->description,
                'response_time' => $req->response_time,
                'order' => $req->order ?? 0,
            ]);

            DB::commit();

            return redirect(route('priority.index'))->with('success', 'Priority updated');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator')->withInput();
        }
    }
}
