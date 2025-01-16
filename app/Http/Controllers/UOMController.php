<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\UOM;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UOMController extends Controller
{
    protected $uom;

    public function __construct() {
        $this->uom = new UOM();
    }

    public function index() {
        return view('uom.list');
    }

    public function getData(Request $req) {
        $records = $this->uom;

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
        return view('uom.form');
    }

    public function store(Request $req) {
        // Validate request
        $validator = Validator::make($req->all(), [
            'name' => 'required|max:250',
            'company_group' => 'required',
            'status' => 'required',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $dt = $this->uom::create([
                'name' => $req->name,
                'company_group' => $req->company_group,
                'is_active' => $req->status,
            ]);
            (new Branch)->assign(UOM::class, $dt->id);

            DB::commit();

            if ($req->create_again == true) {
                return redirect(route('uom.create'))->with('success', 'UOM created');
            }
            return redirect(route('uom.index'))->with('success', 'UOM created');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator')->withInput();
        }
    }

    public function edit(UOM $uom) {
        return view('uom.form', [
            'uom' => $uom
        ]);
    }

    public function update(Request $req, UOM $uom) {
        // Validate request
        $validator = Validator::make($req->all(), [
            'name' => 'required|max:250',
            'company_group' => 'required',
            'status' => 'required',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $uom->update([
                'name' => $req->name,
                'company_group' => $req->company_group,
                'is_active' => $req->status,
            ]);

            DB::commit();

            return redirect(route('uom.index'))->with('success', 'UOM updated');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator')->withInput();
        }
    }
}
