<?php

namespace App\Http\Controllers;

use App\Models\WarrantyPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class WarrantyPeriodController extends Controller
{
    const FORM_RULES = [
        'name' => 'required|max:250',
        'status' => 'required',
    ];

    protected $wp;

    public function __construct() {
        $this->wp = new WarrantyPeriod;
    }

    public function index() {
        return view('warranty_period.list');
    }

    public function getData(Request $req) {
        $records = $this->wp;

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
        return view('warranty_period.form');
    }

    public function store(Request $req) {
        // Validate request
        $validator = Validator::make($req->all(), self::FORM_RULES);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $this->wp::create([
                'name' => $req->name,
                'is_active' => $req->boolean('status'),
            ]);

            DB::commit();

            return redirect(route('warranty_period.index'))->with('success', 'Warranty created');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator')->withInput();
        }
    }

    public function edit(WarrantyPeriod $warranty) {
        return view('warranty_period.form', [
            'warranty' => $warranty
        ]);
    }

    public function update(Request $req, WarrantyPeriod $warranty) {
        // Validate request
        $validator = Validator::make($req->all(), self::FORM_RULES);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $warranty->update([
                'name' => $req->name,
                'is_active' => $req->boolean('status'),
            ]);

            DB::commit();

            return redirect(route('warranty_period.index'))->with('success', 'Warranty updated');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator')->withInput();
        }
    }

    public function delete(WarrantyPeriod $warranty) {
        $warranty->delete();

        return back()->with('success', 'Warranty deleted');
    }
}