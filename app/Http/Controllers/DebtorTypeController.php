<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\DebtorType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DebtorTypeController extends Controller
{
    protected $debtor;

    public function __construct() {
        $this->debtor = new DebtorType;
    }

    public function index() {
        return view('debtor_type.list');
    }

    public function getData(Request $req) {
        $records = $this->debtor;

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
        return view('debtor_type.form');
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

            $dt = $this->debtor::create([
                'name' => $req->name,
                'is_active' => $req->status,
            ]);
            (new Branch)->assign(DebtorType::class, $dt->id);

            DB::commit();

            return redirect(route('debtor_type.index'))->with('success', 'Debtor Type created');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator')->withInput();
        }
    }

    public function edit(DebtorType $debtor) {
        return view('debtor_type.form', [
            'debtor' => $debtor
        ]);
    }

    public function update(Request $req, DebtorType $debtor) {
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

            $debtor->update([
                'name' => $req->name,
                'is_active' => $req->status,
            ]);

            DB::commit();

            return redirect(route('debtor_type.index'))->with('success', 'Debtor Type updated');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator')->withInput();
        }
    }
}
