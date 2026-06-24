<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\PaymentMethod;
use App\Support\TableSearch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class PaymentMethodController extends Controller
{
    protected $method;

    public function __construct()
    {
        $this->method = new PaymentMethod;
    }

    public function index()
    {
        $page = Session::get('payment-method-page');

        return view('payment_method.list', [
            'default_page' => $page ?? null,
        ]);
    }

    public function getData(Request $req)
    {
        $records = $this->method;

        Session::put('payment-method-page', $req->page);

        // Search
        $keyword = $req->has('search') ? ($req->search['value'] ?? null) : null;
        $records = TableSearch::apply($records, $keyword, [
            'name',
        ], [
            'by_pass_conversion' => [0 => 'No', 1 => 'Yes'],
            'deposit_required' => [0 => 'No', 1 => 'Yes'],
            'status' => [0 => 'Inactive', 1 => 'Active'],
        ]);
        // Order
        if ($req->has('order')) {
            $map = [
                0 => 'name',
                1 => 'by_pass_conversion',
                2 => 'deposit_required',
                3 => 'status',
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
            'recordsTotal' => $records_count,
            'recordsFiltered' => $records_count,
            'data' => [],
            'records_ids' => $records_ids,
        ];
        foreach ($records_paginator as $key => $record) {
            $data['data'][] = [
                'id' => $record->id,
                'name' => $record->name,
                'by_pass_conversion' => $record->by_pass_conversion,
                'deposit_required' => $record->deposit_required,
                'status' => $record->status,
            ];
        }

        return response()->json($data);
    }

    public function create()
    {
        return view('payment_method.form');
    }

    public function store(Request $req)
    {
        // Validate request
        $validator = Validator::make($req->all(), [
            'name' => 'required|max:250',
            'by_pass_conversion' => 'required',
            'deposit_required' => 'required',
            'status' => 'required',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $pm = $this->method::create([
                'name' => $req->name,
                'by_pass_conversion' => $req->by_pass_conversion,
                'deposit_required' => $req->deposit_required,
                'status' => $req->status,
            ]);
            (new Branch)->assign(PaymentMethod::class, $pm->id);

            DB::commit();

            if ($req->create_again == true) {
                return redirect(route('payment_method.create'))->with('success', __('Payment Method created'));
            }

            return redirect(route('payment_method.index'))->with('success', __('Payment Method created'));
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', __('Something went wrong. Please contact administrator'))->withInput();
        }
    }

    public function edit(PaymentMethod $method)
    {
        return view('payment_method.form', [
            'method' => $method,
        ]);
    }

    public function update(Request $req, PaymentMethod $method)
    {
        // Validate request
        $validator = Validator::make($req->all(), [
            'name' => 'required|max:250',
            'by_pass_conversion' => 'required',
            'deposit_required' => 'required',
            'status' => 'required',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $method->update([
                'name' => $req->name,
                'by_pass_conversion' => $req->by_pass_conversion,
                'deposit_required' => $req->deposit_required,
                'status' => $req->status,
            ]);

            DB::commit();

            return redirect(route('payment_method.index'))->with('success', __('Payment Method updated'));
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', __('Something went wrong. Please contact administrator'))->withInput();
        }
    }
}
