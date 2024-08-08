<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Customer;
use App\Models\CustomerLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\ValidationException;

class CustomerController extends Controller
{
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

    public function edit(Customer $customer) {
        $customer->load('pictures', 'locations');
        
        return view('customer.form', [
            'customer' => $customer
        ]);
    }

    public function delete(Customer $customer) {
        $customer->delete();

        return back()->with('success', 'Customer deleted');
    }

    public function upsertInfo(Request $req) {
        // Validate request
        $req->validate([
            'customer_id' => 'nullable',
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
            'picture.*' => 'file|extensions:jpg,png,jpeg'
        ], [], [
            'picture.*' => 'picture'
        ]);

        try {
            DB::beginTransaction();

            if ($req->customer_id == null) {
                $customer = Customer::create([
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
                ]);
            } else {
                $customer = Customer::where('id', $req->customer_id)->first();
                $customer->update([
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
                ]);
            }

            if ($req->hasFile('picture')) {
                if ($req->customer_id != null) {
                    Attachment::where([
                        ['object_type', Customer::class],
                        ['object_id', $customer->id]
                    ])->delete();
                }

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

            return Response::json([
                'result' => true,
                'customer' => $customer,
            ], HttpFoundationResponse::HTTP_OK);
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return Response::json([
                'result' => false
            ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function upsertLocation(Request $req) {
        // Validate request
        $req->validate([
            'customer_id' => 'required',
            'location_id' => 'nullable',
            'location_id.*' => 'nullable',
            'address' => 'required',
            'address.*' => 'required|max:250',
            'city' => 'required',
            'city.*' => 'required|max:250',
            'state' => 'required',
            'state.*' => 'required|max:250',
            'zip_code' => 'required',
            'zip_code.*' => 'required|max:250',
            'type' => 'required',
            'type.*' => 'required',
            'is_default' => 'required',
            'is_default.*' => 'required',
        ], [], [
            'address.*' => 'address',
            'city.*' => 'city',
            'state.*' => 'state',
            'zip_code.*' => 'zip code',
            'type.*' => 'type',
            'is_default.*' => 'is default',
        ]);

        // Validate only 1 billing address is default or 1 delivery address is default
        $bill_has_default = false;
        $deli_has_default = false;
        for ($i=0; $i < count($req->address); $i++) { 
            if ($req->is_default[$i] == true && $req->type[$i] == CustomerLocation::TYPE_BILLING) {
                if ($bill_has_default == true) {
                    return Response::json([
                        'is_default' => 'Only 1 default billing address is allow'
                    ], HttpFoundationResponse::HTTP_BAD_REQUEST);
                }
                $bill_has_default = true;
            } else if ($req->is_default[$i] == true && $req->type[$i] == CustomerLocation::TYPE_DELIVERY) {
                if ($deli_has_default == true) {
                    return Response::json([
                        'is_default' => 'Only 1 default delivery address is allow'
                    ], HttpFoundationResponse::HTTP_BAD_REQUEST);
                }
                $deli_has_default = true;
            }
        }

        try {
            DB::beginTransaction();

            CustomerLocation::where('customer_id', $req->customer_id)->whereNotIn('id', $req->location_id ?? [])->delete();

            $now = now();
            $data = [];
            for ($i=0; $i < count($req->address); $i++) { 
                if ($req->location_id != null && $req->location_id[$i] != null) {
                    CustomerLocation::where('id', $req->location_id[$i])->update([
                        'address' => $req->address[$i],
                        'city' => $req->city[$i],
                        'state' => $req->state[$i],
                        'zip_code' => $req->zip_code[$i],
                        'type' => $req->type[$i],
                        'is_default' => $req->is_default[$i],
                    ]);
                } else {
                    $data[] = [
                        'customer_id' => $req->customer_id,
                        'address' => $req->address[$i],
                        'city' => $req->city[$i],
                        'state' => $req->state[$i],
                        'zip_code' => $req->zip_code[$i],
                        'type' => $req->type[$i],
                        'is_default' => $req->is_default[$i],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            CustomerLocation::insert($data);

            $new_loc_ids = CustomerLocation::where('customer_id', $req->customer_id)
                ->pluck('id')
                ->toArray();

            DB::commit();

            return Response::json([
                'result' => true,
                'location_ids' => $new_loc_ids,
                'default_billing_address_id' => CustomerLocation::where('customer_id', $req->customer_id)->where('type', CustomerLocation::TYPE_BILLING)->where('is_default', true)->value('id'),
                'default_delivery_address_id' => CustomerLocation::where('customer_id', $req->customer_id)->where('type', CustomerLocation::TYPE_DELIVERY)->where('is_default', true)->value('id'),
            ], HttpFoundationResponse::HTTP_OK);
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return Response::json([
                'result' => false
            ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getLocation(Request $req) {
        try {
            DB::beginTransaction();

            $locations = CustomerLocation::where('customer_id', $req->customer_id)
                ->where('type', CustomerLocation::TYPE_DELIVERY)
                ->get();

            return Response::json([
                'result' => true,
                'locations' => $locations,
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
