<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    const FORM_RULES = [
        'prefix' => 'nullable',
        'customer_name' => 'required|max:250',
        'company_name' => 'nullable|max:250',
        'company_address' => 'nullable|max:250',
        'city' => 'nullable|max:250',
        'state' => 'nullable|max:250',
        'zip_code' => 'nullable|max:250',
        'company_registration_number' => 'nullable|max:250',
        'phone_number' => 'required|max:250',
        'email' => 'nullable|email|max:250',
        'website' => 'nullable|max:250',
        'under_warranty' => 'required',
        'status' => 'required',
        'status' => 'nullable|max:250',
        'picture' => 'nullable',
        'picture.*' => 'file'
    ];
    
    public function index() {
        return view('customer.list');
    }

    public function getData(Request $req) {
        $records = new Customer;

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
        return view('customer.form');
    }

    public function store(Request $req) {
        // Validate request
        $validator = Validator::make($req->all(), self::FORM_RULES);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $customer = Customer::create([
                'name' => $req->customer_name,
                'phone' => $req->phone_number,
                'under_warranty' => $req->boolean('under_warranty'),
                'is_active' => $req->boolean('status'),
                'company_name' => $req->company_name,
                'company_address' => $req->company_address,
                'city' => $req->city,
                'state' => $req->state,
                'zip_code' => $req->zip_code,
                'company_registration_number' => $req->company_registration_number,
                'website' => $req->website,
                'prefix' => $req->prefix,
                'email' => $req->email,
                'remark' => $req->remark,
            ]);

            if ($req->hasFile('picture')) {
                foreach ($req->file('picture') as $key => $file) {
                    $path = Storage::putFile(Attachment::CUSTOMER_PATH, $file);
                    Attachment::create([
                        'object_type' => Customer::class,
                        'object_id' => $customer->id,
                        'src' => basename($path),
                    ]);
                }
            }

            DB::commit();

            return redirect(route('customer.index'))->with('success', 'Customer created');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator')->withInput();
        }
    }

    public function edit(Customer $customer) {
        $customer->load('pictures');
        
        return view('customer.form', [
            'customer' => $customer
        ]);
    }

    public function update(Request $req, Customer $customer) {
        // Validate request
        $validator = Validator::make($req->all(), self::FORM_RULES);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $customer->update([
                'name' => $req->customer_name,
                'phone' => $req->phone_number,
                'under_warranty' => $req->boolean('under_warranty'),
                'is_active' => $req->boolean('status'),
                'company_name' => $req->company_name,
                'company_address' => $req->company_address,
                'city' => $req->city,
                'state' => $req->state,
                'zip_code' => $req->zip_code,
                'company_registration_number' => $req->company_registration_number,
                'website' => $req->website,
                'prefix' => $req->prefix,
                'email' => $req->email,
                'remark' => $req->remark,
            ]);

            if ($req->hasFile('picture')) {
                Attachment::where([
                    ['object_type', Customer::class],
                    ['object_id', $customer->id]
                ])->delete();

                foreach ($req->file('picture') as $key => $file) {
                    $path = Storage::putFile(Attachment::CUSTOMER_PATH, $file);
                    Attachment::create([
                        'object_type' => Customer::class,
                        'object_id' => $customer->id,
                        'src' => basename($path),
                    ]);
                }
            }

            DB::commit();

            return redirect(route('customer.index'))->with('success', 'Customer updated');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator')->withInput();
        }
    }

    public function delete(Customer $customer) {
        $customer->delete();

        return back()->with('success', 'Customer deleted');
    }
}
