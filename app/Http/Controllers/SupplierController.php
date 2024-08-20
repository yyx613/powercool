<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Supplier;
use App\Models\CustomerLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\ValidationException;

class SupplierController extends Controller
{
    public function index() {
        return view('supplier.list');
    }

    public function getData(Request $req) {
        $records = new Supplier;

        // Search
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];

            $records = $records->where(function($q) use ($keyword) {
                $q->where('name', 'like', '%' . $keyword . '%')
                    ->orWhere('phone', 'like', '%' . $keyword . '%')
                    ->orWhere('company_name', 'like', '%' . $keyword . '%');
            });
        }
        // Order
        if ($req->has('order')) {
            $map = [
                0 => 'name',
                1 => 'phone',
                2 => 'company_name',
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
                'phone_number' => $record->phone,
                'company_name' => $record->company_name,
            ];
        }

        return response()->json($data);
    }

    public function create() {
        return view('supplier.form');
    }

    public function edit(Supplier $supplier) {
        $supplier->load('pictures');
        
        return view('supplier.form', [
            'supplier' => $supplier
        ]);
    }

    public function delete(Supplier $supplier) {
        $supplier->delete();

        return back()->with('success', 'Supplier deleted');
    }

    public function upsert(Request $req, Supplier $supplier) {
        // Validate request
        $req->validate([
            'prefix' => 'nullable',
            'customer_name' => 'required|max:250',
            'company_name' => 'nullable|max:250',
            'company_registration_number' => 'nullable|max:250',
            'phone_number' => 'required|max:250',
            'email' => 'nullable|email|max:250',
            'website' => 'nullable|max:250',
            'under_warranty' => 'required',
            'status' => 'required',
            'picture' => 'nullable',
            'picture.*' => 'file|extensions:jpg,png,jpeg',
            'location' => 'required',
        ], [], [
            'picture.*' => 'picture'
        ]);

        try {
            DB::beginTransaction();

            if ($supplier->id == null) {
                $supplier = Supplier::create([
                    'name' => $req->customer_name,
                    'phone' => $req->phone_number,
                    'under_warranty' => $req->boolean('under_warranty'),
                    'is_active' => $req->boolean('status'),
                    'company_name' => $req->company_name,
                    'company_registration_number' => $req->company_registration_number,
                    'website' => $req->website,
                    'prefix' => $req->prefix,
                    'email' => $req->email,
                    'remark' => $req->remark,
                    'location' => $req->location,
                ]);
            } else {
                $supplier->update([
                    'name' => $req->customer_name,
                    'phone' => $req->phone_number,
                    'under_warranty' => $req->boolean('under_warranty'),
                    'is_active' => $req->boolean('status'),
                    'company_name' => $req->company_name,
                    'company_registration_number' => $req->company_registration_number,
                    'website' => $req->website,
                    'prefix' => $req->prefix,
                    'email' => $req->email,
                    'remark' => $req->remark,
                    'location' => $req->location,
                ]);
            }

            if ($req->hasFile('picture')) {
                if ($supplier != null) {
                    Attachment::where([
                        ['object_type', Supplier::class],
                        ['object_id', $supplier->id]
                    ])->delete();
                }

                foreach ($req->file('picture') as $key => $file) {
                    $path = Storage::putFile(Attachment::SUPPLIER_PATH, $file);
                    Attachment::create([
                        'object_type' => Supplier::class,
                        'object_id' => $supplier->id,
                        'src' => basename($path),
                    ]);
                }
            }

            DB::commit();

            return redirect(route('supplier.index'))->with('success', 'Supplier created');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator')->withInput();
        }
    }
}
