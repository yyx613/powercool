<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Currency;
use App\Support\TableSearch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CurrencyController extends Controller
{
    protected $curr;

    public function __construct() {
        $this->curr = new Currency;
    }

    public function index() {
        return view('currency.list');
    }

    public function getData(Request $req) {
        $records = $this->curr;

        // Search
        $keyword = $req->has('search') ? ($req->search['value'] ?? null) : null;
        $records = TableSearch::apply($records, $keyword, [
            'name',
            'country',
            'currency_name',
            'code',
            'symbol',
        ], [
            'is_active' => [0 => 'Inactive', 1 => 'Active'],
        ]);
        // Order
        if ($req->has('order')) {
            $map = [
                0 => 'name',
                1 => 'country',
                2 => 'currency_name',
                3 => 'code',
                4 => 'symbol',
                5 => 'is_active',
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
                'country' => $record->country,
                'currency_name' => $record->currency_name,
                'code' => $record->code,
                'symbol' => $record->symbol,
                'status' => $record->is_active,
            ];
        }
                
        return response()->json($data);
    }

    public function create() {
        return view('currency.form');
    }

    public function store(Request $req) {
        // Validate request
        $validator = Validator::make($req->all(), [
            'name' => 'required|max:250',
            'country' => 'nullable|max:250',
            'currency_name' => 'nullable|max:250',
            'code' => 'nullable|max:250',
            'symbol' => 'nullable|max:250',
            'status' => 'required',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $currency = $this->curr::create([
                'name' => $req->name,
                'country' => $req->country,
                'currency_name' => $req->currency_name,
                'code' => $req->code,
                'symbol' => $req->symbol,
                'is_active' => $req->status,
            ]);
            (new Branch)->assign(Currency::class, $currency->id);

            DB::commit();

            if ($req->create_again == true) {
                return redirect(route('currency.create'))->with('success', __('Currency created'));
            }
            return redirect(route('currency.index'))->with('success', __('Currency created'));
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', __('Something went wrong. Please contact administrator'))->withInput();
        }
    }

    public function edit(Currency $currency) {
        return view('currency.form', [
            'curr' => $currency
        ]);
    }

    public function update(Request $req, Currency $currency) {
        // Validate request
        $validator = Validator::make($req->all(), [
            'name' => 'required|max:250',
            'country' => 'nullable|max:250',
            'currency_name' => 'nullable|max:250',
            'code' => 'nullable|max:250',
            'symbol' => 'nullable|max:250',
            'status' => 'required',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $currency->update([
                'name' => $req->name,
                'country' => $req->country,
                'currency_name' => $req->currency_name,
                'code' => $req->code,
                'symbol' => $req->symbol,
                'is_active' => $req->status,
            ]);

            DB::commit();

            return redirect(route('currency.index'))->with('success', __('Currency updated'));
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', __('Something went wrong. Please contact administrator'))->withInput();
        }
    }
}
