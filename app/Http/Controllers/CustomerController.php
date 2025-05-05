<?php

namespace App\Http\Controllers;

use App\Exports\CustomerExport;
use App\Models\Attachment;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\CustomerLocation;
use App\Models\Dealer;
use App\Models\DebtorType;
use App\Models\DeliveryOrder;
use App\Models\ObjectCreditTerm;
use App\Models\Sale;
use App\Models\Scopes\BranchScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

class CustomerController extends Controller
{
    public function index()
    {
        return view('customer.list');
    }

    public function getData(Request $req)
    {
        $records = new Customer;

        // Search
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];

            $records = $records->where(function ($q) use ($keyword) {
                $q->where('name', 'like', '%'.$keyword.'%')
                    ->orWhere('sku', 'like', '%'.$keyword.'%')
                    ->orWhere('phone', 'like', '%'.$keyword.'%')
                    ->orWhere('company_name', 'like', '%'.$keyword.'%')
                    ->orWhereHas('platform', function ($q) use ($keyword) {
                        $q->where('name', 'like', '%'.$keyword.'%');
                    });
            });
        }
        // Order
        if ($req->has('order')) {
            $map = [
                0 => 'sku',
                1 => 'name',
                2 => 'phone',
                3 => 'company_name',
                4 => 'platform',
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
                'code' => $record->sku,
                'name' => $record->name,
                'phone_number' => $record->phone,
                'company_name' => $record->company_name,
                'debt_type' => $record->debtorType->name ?? '-',
                'company_group' => $record->company_group == 1 ? 'Power Cool' : ($record->company_group == 2 ? 'Hi-Ten' : null),
                'platform' => $record->platform->name ?? '-',
                'status' => $record->status == Customer::STATUS_INACTIVE ? 'Inactive' : ($record->status == Customer::STATUS_ACTIVE ? 'Active' : 'Pending Fill Up Info'),
                'can_edit' => hasPermission('customer.edit'),
                'can_delete' => hasPermission('customer.delete'),
            ];
        }

        return response()->json($data);
    }

    public function create()
    {
        return view('customer.form');
    }

    public function edit(Customer $customer)
    {
        $customer->load('pictures', 'locations');

        return view('customer.form', [
            'customer' => $customer,
        ]);
    }

    public function view(Customer $customer)
    {
        $customer->load('pictures', 'locations');

        return view('customer.form', [
            'customer' => $customer,
            'mode' => 'view',
        ]);
    }

    public function delete(Customer $customer)
    {
        $customer->delete();

        return back()->with('success', 'Customer deleted');
    }

    public function upsertInfo(Request $req)
    {
        // Validate request
        $req->validate([
            'customer_id' => 'nullable',
            'company_group' => 'required',
            'category' => 'required',
            'prefix' => 'nullable',
            'customer_name' => 'required|max:250',
            'company_name' => 'nullable|max:250',
            'phone_number' => 'required|max:250',
            'mobile_number' => 'nullable|max:250',
            'email' => 'required|email|max:250',
            'website' => 'nullable|max:250',
            'currency' => 'nullable',
            'tin_number' => 'required_if:category,==,1|max:250',
            'status' => 'required',
            'picture' => 'nullable',
            'picture.*' => 'file|extensions:jpg,png,jpeg',
            'credit_term' => 'nullable',
            'sale_agent' => 'nullable',
            'area' => 'nullable',
            'debtor_type' => 'nullable',
            'platform' => 'nullable',
            'local_oversea' => 'required_if:category,==,1',
            'msic_code' => 'required_unless:category,!=,2',
            'business_activity_desc' => 'required_unless:category,!=,2',
            'company_registration_number' => 'required_unless:category,!=,2|max:250',
            'sst_number' => 'nullable|max:250',
            'category' => 'required|max:250',
            'tourism_tax_reg_no' => 'nullable|max:250',
            'prev_gst_reg_no' => 'nullable|max:250',
            'registered_name' => 'required|max:250',
            'trade_name' => 'nullable|max:250',
            'identity_type' => 'required_if:category,==,2|max:250',
            'identity_no' => 'nullable|max:250',
        ], [
            'required_if' => 'The :attribute is required',
            'required_unless' => 'The :attribute is required',
        ], [
            'picture.*' => 'picture',
            'company_number' => 'business reg no',
            'email' => 'email address',
            'tin_number' => 'TIN',
            'msic_code' => 'MSIC code',
            'local_oversea' => 'type',
        ]);

        // Validate tin with hasil
        if ($req->boolean('neglect_tin_validation') == false) {
            $res = (new EInvoiceController)->validateTIN($req->tin_number, 'BRN', $req->company_registration_number, $req->company_group == 1 ? 'powercool' : 'hi-ten');
            if ($res->status() != 200) {
                $err = json_decode($res->getData()->message);

                throw ValidationException::withMessages([
                    'tin_number' => $err->title ?? $err->message,
                    'tin_number_hasil' => true,
                ]);
            }
        }

        try {
            DB::beginTransaction();

            if ($req->customer_id == null || $req->customer_id == 'null') {
                $customer = Customer::create([
                    'sku' => (new Customer)->generateSku($req->company_name != null ? $req->company_name[0] : $req->customer_name[0]),
                    'name' => $req->customer_name,
                    'phone' => $req->phone_number,
                    'mobile_number' => $req->mobile_number,
                    'currency_id' => $req->currency,
                    'status' => $req->status,
                    'company_name' => $req->company_name ?? $req->customer_name,
                    'company_registration_number' => $req->company_registration_number,
                    'website' => $req->website,
                    'prefix' => $req->prefix,
                    'email' => $req->email,
                    'remark' => $req->remark,
                    'tin_number' => $req->tin_number,
                    'sale_agent' => $req->sale_agent,
                    'area_id' => $req->area,
                    'debtor_type_id' => $req->debtor_type,
                    'platform_id' => $req->platform,
                    'type' => $req->local_oversea,
                    'msic_id' => $req->msic_code,
                    'sst_number' => $req->sst_number,
                    'company_group' => $req->company_group,
                    'category' => $req->category,
                    'business_act_desc' => $req->business_activity_desc,
                    'tourism_tax_reg_no' => $req->tourism_tax_reg_no,
                    'prev_gst_reg_no' => $req->prev_gst_reg_no,
                    'registered_name' => $req->registered_name,
                    'trade_name' => $req->trade_name,
                    'identity_type' => $req->identity_type,
                    'identity_no' => $req->identity_no,
                ]);

                (new Branch)->assign(Customer::class, $customer->id, $req->branch ?? null);
            } else {
                $customer = Customer::where('id', $req->customer_id)->first();
                $customer->update([
                    'name' => $req->customer_name,
                    'phone' => $req->phone_number,
                    'mobile_number' => $req->mobile_number,
                    'currency_id' => $req->currency,
                    'status' => $req->status,
                    'company_name' => $req->company_name ?? $req->customer_name,
                    'company_registration_number' => $req->company_registration_number,
                    'website' => $req->website,
                    'prefix' => $req->prefix,
                    'email' => $req->email,
                    'remark' => $req->remark,
                    'tin_number' => $req->tin_number,
                    'sale_agent' => $req->sale_agent,
                    'area_id' => $req->area,
                    'debtor_type_id' => $req->debtor_type,
                    'platform_id' => $req->platform,
                    'type' => $req->local_oversea,
                    'msic_id' => $req->msic_code,
                    'sst_number' => $req->sst_number,
                    'company_group' => $req->company_group,
                    'category' => $req->category,
                    'business_act_desc' => $req->business_activity_desc,
                    'tourism_tax_reg_no' => $req->tourism_tax_reg_no,
                    'prev_gst_reg_no' => $req->prev_gst_reg_no,
                    'registered_name' => $req->registered_name,
                    'trade_name' => $req->trade_name,
                    'identity_type' => $req->identity_type,
                    'identity_no' => $req->identity_no,
                ]);
            }

            // Create dealer if debtor type is dealer
            if ($req->debtor_type != null) {
                $debt_type_name = DebtorType::withoutGlobalScope(BranchScope::class)->where('id', $req->debtor_type)->value('name');
                $dealer_exists = Dealer::where('name', $req->customer_name)->exists();

                if (strtolower($debt_type_name) == 'dealer' && ! $dealer_exists) {
                    $new_dealer = Dealer::create([
                        'name' => $req->customer_name,
                        'sku' => (new Dealer)->generateSku(),
                    ]);
                    (new Branch)->assign(Dealer::class, $new_dealer->id);
                }
            }

            if ($req->hasFile('picture')) {
                if ($req->customer_id != null) {
                    Attachment::where([
                        ['object_type', Customer::class],
                        ['object_id', $customer->id],
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

            // Credit Terms
            if ($req->credit_term != null) {
                ObjectCreditTerm::where('object_type', Customer::class)->where('object_id', $customer->id)->delete();

                $terms = [];
                for ($i = 0; $i < count($req->credit_term); $i++) {
                    $terms[] = [
                        'object_type' => Customer::class,
                        'object_id' => $customer->id,
                        'credit_term_id' => $req->credit_term[$i],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                ObjectCreditTerm::insert($terms);
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
                'result' => false,
            ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function upsertLocation(Request $req)
    {
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
        $bill_and_deli_has_default = false;
        $bill_has_default = false;
        $deli_has_default = false;
        for ($i = 0; $i < count($req->address); $i++) {
            if ($req->is_default[$i] == true && $req->type[$i] == CustomerLocation::TYPE_BILLING_ADN_DELIVERY) {
                if ($bill_and_deli_has_default == true) {
                    return Response::json([
                        'is_default' => 'Only 1 default Billing & Delivery address is allow',
                    ], HttpFoundationResponse::HTTP_BAD_REQUEST);
                }
                $bill_and_deli_has_default = true;
            } elseif ($req->is_default[$i] == true && $req->type[$i] == CustomerLocation::TYPE_BILLING) {
                if ($bill_has_default == true) {
                    return Response::json([
                        'is_default' => 'Only 1 default Billing address is allow',
                    ], HttpFoundationResponse::HTTP_BAD_REQUEST);
                }
                $bill_has_default = true;
            } elseif ($req->is_default[$i] == true && $req->type[$i] == CustomerLocation::TYPE_DELIVERY) {
                if ($deli_has_default == true) {
                    return Response::json([
                        'is_default' => 'Only 1 default Delivery address is allow',
                    ], HttpFoundationResponse::HTTP_BAD_REQUEST);
                }
                $deli_has_default = true;
            }
        }
        if ($bill_and_deli_has_default && ($bill_has_default || $deli_has_default)) {
            return Response::json([
                'is_default' => 'Billing & Delivery is selected as default, Please make sure not default is set on Billing or Delivery',
            ], HttpFoundationResponse::HTTP_BAD_REQUEST);
        }

        try {
            DB::beginTransaction();

            if ($req->location_id != null) {
                $order_idx = array_filter($req->location_id, function ($val) {
                    return $val != null;
                });
                CustomerLocation::where('customer_id', $req->customer_id)->whereNotIn('id', $order_idx ?? [])->delete();
            }

            $now = now();
            $data = [];
            for ($i = 0; $i < count($req->address); $i++) {
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
                'default_billing_and_delivery_address_id' => CustomerLocation::where('customer_id', $req->customer_id)->where('type', CustomerLocation::TYPE_BILLING_ADN_DELIVERY)->where('is_default', true)->value('id'),
                'default_billing_address_id' => CustomerLocation::where('customer_id', $req->customer_id)->where('type', CustomerLocation::TYPE_BILLING)->where('is_default', true)->value('id'),
                'default_delivery_address_id' => CustomerLocation::where('customer_id', $req->customer_id)->where('type', CustomerLocation::TYPE_DELIVERY)->where('is_default', true)->value('id'),
            ], HttpFoundationResponse::HTTP_OK);
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return Response::json([
                'result' => false,
            ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getLocation(Request $req)
    {
        try {
            DB::beginTransaction();

            if ($req->type == 'delivery') {
                $locations = CustomerLocation::where('customer_id', $req->customer_id)
                    ->whereIn('type', [CustomerLocation::TYPE_BILLING_ADN_DELIVERY, CustomerLocation::TYPE_DELIVERY])
                    ->get();
            } else {
                $locations = CustomerLocation::where('customer_id', $req->customer_id)
                    ->whereIn('type', [CustomerLocation::TYPE_BILLING_ADN_DELIVERY, CustomerLocation::TYPE_BILLING])
                    ->get();
            }

            return Response::json([
                'result' => true,
                'locations' => $locations,
            ], HttpFoundationResponse::HTTP_OK);
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return Response::json([
                'result' => false,
            ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function createLink(Request $req)
    {
        if ($req->branch == null) {
            abort(403);
        }
        $branch = Crypt::decrypt($req->branch);

        $branches = [Branch::LOCATION_KL, Branch::LOCATION_PENANG];
        if (! in_array($branch, $branches, true)) {
            abort(403);
        }

        return view('customer.link', [
            'default_branch' => $branch,
        ]);
    }

    public function getSaleAndDeliveryOrder(Request $req, Customer $cus)
    {
        try {
            if (str_contains($req->type, 'so')) {
                $sale_orders = Sale::select('id', 'sku')->where('type', Sale::TYPE_SO)->where('customer_id', $cus->id)->get();
            }
            if (str_contains($req->type, 'do')) {
                $convert_to = Sale::where('type', Sale::TYPE_SO)->whereNotNull('convert_to')->where('customer_id', $cus->id)->pluck('convert_to');
                $do_ids = collect();

                for ($i = 0; $i < count($convert_to); $i++) {
                    $con = collect(explode(',', $convert_to[$i]));
                    $do_ids->push($con);
                }
                $delivery_orders = DeliveryOrder::select('id', 'sku')->whereIn('id', $do_ids->flatten()->toArray())->get();
            }

            return Response::json([
                'result' => true,
                'so' => $sale_orders ?? [],
                'do' => $delivery_orders ?? [],
            ], HttpFoundationResponse::HTTP_OK);
        } catch (\Throwable $th) {
            report($th);

            return Response::json([
                'result' => false,
            ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function sync(Request $request)
    {
        $request->validate([
            'customers' => 'required|array',
            'customers.*.id' => 'required|integer',
        ]);

        $selectedInvoices = $request->input('customers');

        foreach ($selectedInvoices as $invoiceData) {
            $invoice = Customer::find($invoiceData['id']);
            if ($invoice) {
                $invoice->sync = 0;
                $invoice->save();
            }
        }

        return response()->json([
            'message' => 'Customers updated successfully.',
        ]);
    }

    public function export()
    {
        return Excel::download(new CustomerExport, 'user.xlsx');
    }
}
