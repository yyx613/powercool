<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;
use Illuminate\Support\Facades\Response;

class SaleController extends Controller
{
    public function index() {
        return view('quotation.list');
    }

    public function getData(Request $req) {
        $records = Sale::where('type', Sale::TYPE_QUO)->orderBy('id', 'desc');

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
                'sku' => $record->sku,
                'open_until' => $record->open_until,
                'status' => $record->is_active,
            ];
        }

        return response()->json($data);
    }

    public function create(Request $req) {
        return view('quotation.form');
    }

    public function edit(Sale $sale) {
        return view('quotation.form', [
            'sale' => $sale->load('products')
        ]);
    }

    public function delete(Sale $sale) {
        $sale->delete();

        return back()->with('success', 'Quotation deleted');
    }

    public function indexSaleOrder() {
        return view('sale_order.list');
    }

    public function getDataSaleOrder(Request $req) {
        $records = Sale::where('type', Sale::TYPE_SO)->orderBy('id', 'desc');

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
                'sku' => $record->sku,
                'open_until' => $record->open_until,
                'total_amount' => $record->payment_amount,
                'status' => $record->is_active,
            ];
        }

        return response()->json($data);
    }

    public function createSaleOrder(Request $req) {
        $data = [];

        if ($req->has('qid')) {
            $quo = Sale::findOrFail($req->qid);
            $quo->load('products');

            $data['quo'] = $quo;
        }

        return view('sale_order.form', $data);
    }

    public function editSaleOrder(Sale $sale) {
        return view('sale_order.form', [
            'sale' => $sale->load('products')
        ]);
    }

    public function upsertQuoDetails(Request $req) {
        // Validate form
        $rules = [
            'sale_id' => 'nullable',
            'quo_id' => 'nullable',
            'sale' => 'required',
            'customer' => 'required',
            'open_until' => 'required',
            'reference' => 'required',
            'status' => 'required',
        ];
        $req->validate($rules);

        try {
            DB::beginTransaction();

            if ($req->sale_id == null) {
                $sale = Sale::create([
                    'type' => $req->type == 'quo' ? Sale::TYPE_QUO : Sale::TYPE_SO,
                    'sku' => (new Sale)->generateSku($req->type == 'quo' ? Sale::TYPE_QUO : Sale::TYPE_SO),
                    'sale_id' => $req->sale,
                    'customer_id' => $req->customer,
                    'open_until' => $req->open_until,
                    'reference' => $req->reference,
                    'is_active' => $req->boolean('status'),
                ]);
            } else {
                $sale = Sale::where('id', $req->sale_id)->first();
                
                $sale->update([
                    'sale_id' => $req->sale,
                    'customer_id' => $req->customer,
                    'open_until' => $req->open_until,
                    'reference' => $req->reference,
                    'is_active' => $req->boolean('status'),
                ]);
            }

            if ($req->quo_id != null) {
                Sale::where('id', $req->quo_id)->delete();
            }

            DB::commit();

            return Response::json([
                'result' => true,
                'sale' => $sale
            ], HttpFoundationResponse::HTTP_OK);
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return Response::json([
                'result' => false
            ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function upsertProDetails(Request $req) {
        // Validate form
        $rules = [
            'sale_id' => 'required',
            'product_name' => 'required',
            'product_name.*' => 'required|max:250',
            'product_desc' => 'required',
            'product_desc.*' => 'nullable|max:250',
            'qty' => 'required',
            'qty.*' => 'required',
            'unit_price' => 'required',
            'unit_price.*' => 'required',
            'product_serial_no' => 'required',
            'product_serial_no.*' => 'nullable|max:250',
            'warranty_period' => 'required',
            'warranty_period.*' => 'nullable|max:250',
        ];
        $req->validate($rules, [], [
            'product_name.*' => 'product name',
            'product_desc.*' => 'product description',
            'qty.*' => 'quantity',
            'unit_price.*' => 'unit price',
            'product_serial_no.*' => 'product serial no',
            'warranty_period.*' => 'warranty period',
        ]);

        try {
            DB::beginTransaction();

            SaleProduct::where('sale_id', $req->sale_id)->delete();

            $now = now();
            $data = [];
            for ($i=0; $i < count($req->product_name); $i++) { 
                $data[] = [
                    'sale_id' => $req->sale_id,
                    'name' => $req->product_name[$i],
                    'desc' => $req->product_desc[$i],
                    'qty' => $req->qty[$i],
                    'unit_price' => $req->unit_price[$i],
                    'unit_price' => $req->unit_price[$i],
                    'serial_no' => $req->product_serial_no[$i],
                    'warranty_period' => $req->warranty_period[$i],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            SaleProduct::insert($data);

            DB::commit();

            return Response::json([
                'result' => true
            ], HttpFoundationResponse::HTTP_OK);
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return Response::json([
                'result' => false
            ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function upsertRemark(Request $req) {
        // Validate form
        $rules = [
            'sale_id' => 'required',
            'remark' => 'nullable|max:250',
        ];
        $req->validate($rules);

        try {
            DB::beginTransaction();

            Sale::where('id', $req->sale_id)->update([
                'remark' => $req->remark
            ]);

            DB::commit();

            return Response::json([
                'result' => true
            ], HttpFoundationResponse::HTTP_OK);
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return Response::json([
                'result' => false
            ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function upsertPayDetails(Request $req) {
        // Validate form
        $rules = [
            'sale_id' => 'required',
            'payment_term' => 'required|integer',
            'payment_method' => 'required',
            'payment_due_date' => 'required',
            'payment_amount' => 'required',
            'payment_remark' => 'required|max:250',
        ];
        $req->validate($rules);

        try {
            DB::beginTransaction();

            Sale::where('id', $req->sale_id)->update([
                'payment_term' => $req->payment_term,
                'payment_method' => $req->payment_method,
                'payment_due_date' => $req->payment_due_date,
                'payment_amount' => $req->payment_amount,
                'payment_remark' => $req->payment_remark,
            ]);

            DB::commit();

            return Response::json([
                'result' => true,
            ], HttpFoundationResponse::HTTP_OK);
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return Response::json([
                'result' => false
            ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function upsertDelSchedule(Request $req) {
        // Validate form
        $rules = [
            'sale_id' => 'required',
            'delivery_date' => 'required',
            'delivery_time' => 'required',
            'delivery_instruction' => 'required|max:250',
            'delivery_address' => 'required|max:250',
            'status' => 'required',
        ];
        $req->validate($rules);

        try {
            DB::beginTransaction();

            Sale::where('id', $req->sale_id)->update([
                'delivery_date' => $req->delivery_date,
                'delivery_time' => $req->delivery_time,
                'delivery_instruction' => $req->delivery_instruction,
                'delivery_address' => $req->delivery_address,
                'delivery_is_active' => $req->boolean('status'),
            ]);

            DB::commit();

            return Response::json([
                'result' => true,
            ], HttpFoundationResponse::HTTP_OK);
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return Response::json([
                'result' => false
            ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
