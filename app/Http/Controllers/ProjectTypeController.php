<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\ProjectType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class ProjectTypeController extends Controller
{
    protected $pt;

    public function __construct() {
        $this->pt = new ProjectType;
    }

    public function index() {
        $page = Session::get('project-type-page');

        return view('project_type.list', [
            'default_page' => $page ?? null,
        ]);
    }

    public function getData(Request $req) {
        $records = $this->pt;

        Session::put('project-type-page', $req->page);

        // Search
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];

            $records = $records->where(function($q) use ($keyword) {
                $q->where('name', 'like', '%' . $keyword . '%');
            });
        }
        // Order
        if ($req->has('order')) {
            $map = [
                0 => 'name',
                1 => 'is_active',
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
                'status' => $record->is_active,
            ];
        }
                
        return response()->json($data);
    }

    public function create() {
        return view('project_type.form');
    }

    public function store(Request $req) {
        // Validate request
        $validator = Validator::make($req->all(), [
            'name' => 'required|max:250',
            'status' => 'required',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $pt = $this->pt::create([
                'name' => $req->name,
                'is_active' => $req->boolean('status'),
            ]);
            (new Branch)->assign(ProjectType::class, $pt->id);

            DB::commit();

            if ($req->create_again == true) {
                return redirect(route('project_type.create'))->with('success', 'Project Type created');
            }
            return redirect(route('project_type.index'))->with('success', 'Project Type created');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator')->withInput();
        }
    }

    public function edit(ProjectType $type) {
        return view('project_type.form', [
            'pt' => $type
        ]);
    }

    public function update(Request $req, ProjectType $type) {
        // Validate request
        $validator = Validator::make($req->all(), [
            'name' => 'required|max:250',
            'status' => 'required',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $type->update([
                'name' => $req->name,
                'is_active' => $req->boolean('status'),
            ]);

            DB::commit();

            return redirect(route('project_type.index'))->with('success', 'Project Type updated');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator')->withInput();
        }
    }

    public function delete(ProjectType $type) {
        $type->delete();

        return back()->with('success', 'Project Type deleted');
    }
}
