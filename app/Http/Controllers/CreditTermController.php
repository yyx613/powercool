<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\CreditTerm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CreditTermController extends Controller
{
    protected $credit;

    public function __construct() {
        $this->credit = new CreditTerm;
    }

    public function index() {
        return view('credit_term.list');
    }

    public function getData(Request $req) {
        $records = $this->credit;

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
        return view('credit_term.form');
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

            $ct = $this->credit::create([
                'name' => $req->name,
                'is_active' => $req->status,
            ]);
            (new Branch)->assign(CreditTerm::class, $ct->id);

            DB::commit();

            return redirect(route('credit_term.index'))->with('success', 'Credit Term created');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator')->withInput();
        }
    }

    public function edit(CreditTerm $credit) {
        return view('credit_term.form', [
            'credit' => $credit
        ]);
    }

    public function update(Request $req, CreditTerm $credit) {
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

            $credit->update([
                'name' => $req->name,
                'is_active' => $req->status,
            ]);

            DB::commit();

            return redirect(route('credit_term.index'))->with('success', 'Credit Term updated');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator')->withInput();
        }
    }
}
