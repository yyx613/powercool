<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Promotion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

class PromotionController extends Controller
{
    protected $promo;

    public function __construct() {
        $this->promo = new Promotion;
    }

    public function index() {
        return view('promotion.list');
    }

    public function getData(Request $req) {
        $records = $this->promo;

        // Search
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];

            $records = $records->where(function($q) use ($keyword) {
                $q->where('sku', 'like', '%' . $keyword . '%')
                    ->where('amount', 'like', '%' . $keyword . '%');
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
                'promo_code' => $record->sku,
                'product' => $record->product->model_name,
                'type' => $record->type,
                'amount' => $record->amount,
                'valid_till' => $record->valid_till,
                'status' => $record->status,
            ];
        }
                
        return response()->json($data);
    }

    public function create() {
        return view('promotion.form');
    }

    public function store(Request $req) {
        // Validate request
        $validator = Validator::make($req->all(), [
            'promo_code' => 'required|max:250|unique:promotions,sku',
            'amount_val' => 'nullable|numeric',
            'amount_perc' => 'nullable|max:100|numeric',
            'desc' => 'required|max:250',
            'valid_till' => 'nullable',
            'product' => 'required',
            'status' => 'required',
        ], [], [
            'amount_val' => 'amount value',
            'amount_perc' => 'amount percentange',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        // Make sure either val/perc amount is entered
        if ($req->amount_val == null && $req->amount_perc == null) {
            throw ValidationException::withMessages([
                'amount_val' => 'Please enter either discount in amount or discount in percentage'
            ]);
        }

        try {
            DB::beginTransaction();

            $promo = $this->promo::create([
                'sku' => $req->promo_code,
                'desc' => $req->desc,
                'type' => $req->amount_val != null ? 'val' : ($req->amount_perc != null ? 'perc' : null),
                'amount' => $req->amount_val ?? $req->amount_perc,
                'product_id' => $req->product,
                'valid_till' => $req->valid_till,
                'status' => $req->status,
            ]);
            (new Branch)->assign(Promotion::class, $promo->id);

            DB::commit();

            if ($req->create_again == true) {
                return redirect(route('promotion.create'))->with('success', 'Promotion created');
            }
            return redirect(route('promotion.index'))->with('success', 'Promotion created');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator')->withInput();
        }
    }

    public function edit(Promotion $promotion) {
        return view('promotion.form', [
            'promo' => $promotion
        ]);
    }

    public function update(Request $req, Promotion $promotion) {
        // Validate request
        $validator = Validator::make($req->all(), [
            'promo_code' => 'required|max:250|unique:promotions,sku,'.$promotion->id,
            'amount_val' => 'nullable|numeric',
            'amount_perc' => 'nullable|max:100|numeric',
            'desc' => 'required|max:250',
            'valid_till' => 'nullable',
            'product' => 'required',
            'status' => 'required',
        ], [], [
            'amount_val' => 'amount value',
            'amount_perc' => 'amount percentange',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $promotion->update([
                'sku' => $req->promo_code,
                'desc' => $req->desc,
                'type' => $req->amount_val != null ? 'val' : ($req->amount_perc != null ? 'perc' : null),
                'amount' => $req->amount_val ?? $req->amount_perc,
                'product_id' => $req->product,
                'valid_till' => $req->valid_till,
                'status' => $req->status,
            ]);

            DB::commit();

            return redirect(route('promotion.index'))->with('success', 'Promotion updated');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator')->withInput();
        }
    }

    public function delete(Promotion $promotion) {
        $promotion->delete();

        return back()->with('success', 'Promotion deleted');
    }
}
