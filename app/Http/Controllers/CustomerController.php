<?php

namespace App\Http\Controllers;

use App\Exports\CustomerExport;
use App\Models\Attachment;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\CustomerLocation;
use App\Models\CustomerSaleAgent;
use App\Models\Dealer;
use App\Models\DebtorType;
use App\Models\DeliveryOrder;
use App\Models\ObjectCreditTerm;
use App\Models\Sale;
use App\Models\SalesAgent;
use App\Models\Scopes\BranchScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

class CustomerController extends Controller
{
    public function index()
    {
        if (Session::get('debtor-debt_type') != null) {
            $debt_type = Session::get('debtor-debt_type');
        }
        if (Session::get('debtor-company_group') != null) {
            $company_group = Session::get('debtor-company_group');
        }
        if (Session::get('debtor-category') != null) {
            $category = Session::get('debtor-category');
        }
        if (Session::get('debtor-sales_agent') != null) {
            $sales_agent = Session::get('debtor-sales_agent');
        }

        return view('customer.list', [
            'default_debt_type' => $debt_type ?? null,
            'default_company_group' => $company_group ?? null,
            'default_category' => $category ?? null,
            'default_sales_agent' => $sales_agent ?? null,
        ]);
    }

    public function getData(Request $req)
    {
        $records = new Customer;

        if (isSalesOnly()) {
            $sales_agents_ids = DB::table('sales_sales_agents')->where('sales_id', Auth::user()->id)->pluck('sales_agent_id')->toArray();
            $customer_ids = CustomerSaleAgent::whereIn('sales_agent_id', $sales_agents_ids)->pluck('customer_id')->toArray();

            $records = $records->whereIn('id', $customer_ids);
        }
        // Search
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];

            $records = $records->where(function ($q) use ($keyword) {
                $q->where('name', 'like', '%' . $keyword . '%')
                    ->orWhere('sku', 'like', '%' . $keyword . '%')
                    ->orWhere('phone', 'like', '%' . $keyword . '%')
                    ->orWhere('company_name', 'like', '%' . $keyword . '%')
                    ->orWhereHas('platform', function ($q) use ($keyword) {
                        $q->where('name', 'like', '%' . $keyword . '%');
                    });
            });
        }

        if ($req->has('debt_type')) {
            if ($req->debt_type == null) {
                Session::remove('debtor-debt_type');
            } else {
                $records = $records->where('debtor_type_id', $req->debt_type);
                Session::put('debtor-debt_type', $req->debt_type);
            }
        } else if (Session::get('debtor-debt_type') != null) {
            $records = $records->where('debtor_type_id', Session::get('debtor-debt_type'));
        }
        if ($req->has('company_group')) {
            if ($req->company_group == null) {
                Session::remove('debtor-company_group');
            } else {
                $records = $records->where('company_group', $req->company_group);
                Session::put('debtor-company_group', $req->company_group);
            }
        } else if (Session::get('debtor-company_group') != null) {
            $records = $records->where('company_group', Session::get('debtor-company_group'));
        }
        if ($req->has('category')) {
            if ($req->category == null) {
                Session::remove('debtor-category');
            } else {
                $records = $records->where('category', $req->category);
                Session::put('debtor-category', $req->category);
            }
        } else if (Session::get('debtor-category') != null) {
            $records = $records->where('category', Session::get('debtor-category'));
        }
        if ($req->has('sales_agent')) {
            if ($req->sales_agent == null) {
                Session::remove('debtor-sales_agent');
            } else {
                $records = $records->whereHas('salesAgents', function ($q) use ($req) {
                    $q->where('sales_agent_id', $req->sales_agent);
                });
                Session::put('debtor-sales_agent', $req->sales_agent);
            }
        } else if (Session::get('debtor-sales_agent') != null) {
            $records = $records->whereHas('salesAgents', function ($q) {
                $q->where('sales_agent_id', Session::get('debtor-sales_agent'));
            });
        }

        // Order
        if ($req->has('order')) {
            $map = [
                1 => 'sku',
                2 => 'name',
                3 => 'phone',
                4 => 'company_name',
            ];
            foreach ($req->order as $order) {
                $records = $records->orderBy($map[$order['column']], $order['dir']);
            }
        } else {
            $records = $records->orderBy('company_name', 'asc');
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
            $sales_agents = SalesAgent::whereIn('id', $record->salesAgents->pluck('sales_agent_id')->toArray())->pluck('name')->toArray();

            $data['data'][] = [
                'id' => $record->id,
                'code' => $record->sku,
                'name' => $record->name,
                'category' => $record->category == null ? null : Customer::BUSINESS_TYPES[$record->category],
                'phone_number' => $record->phone,
                'company_name' => $record->company_name,
                'debt_type' => $record->debtorType->name ?? '-',
                'company_group' => $record->company_group == 1 ? 'Power Cool' : ($record->company_group == 2 ? 'Hi-Ten' : null),
                'platform' => $record->platform->name ?? '-',
                'sales_agents' => join(', ', $sales_agents),
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
            'sales_agent_ids' => $customer->salesAgents->pluck('sales_agent_id')->toArray(),
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
            'email' => 'nullable|email|max:250',
            'website' => 'nullable|max:250',
            'currency' => 'nullable',
            'tin_number' => 'nullable',
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
            'business_activity_desc' => 'nullable',
            'company_registration_number' => 'nullable',
            'sst_number' => 'nullable|max:250',
            'category' => 'required|max:250',
            'tourism_tax_reg_no' => 'nullable|max:250',
            'prev_gst_reg_no' => 'nullable|max:250',
            'registered_name' => 'required|max:250',
            'trade_name' => 'nullable|max:250',
            'identity_type' => 'required_if:category,==,2|max:250',
            'identity_no' => 'nullable|max:250',
            'address' => 'nullable|max:250',
            'city' => 'nullable|max:250',
            'zip_code' => 'nullable|max:250',

            // 'customer_id' => 'nullable',
            // 'company_group' => 'required',
            // 'category' => 'required',
            // 'prefix' => 'nullable',
            // 'customer_name' => 'required|max:250',
            // 'company_name' => 'nullable|max:250',
            // 'phone_number' => 'required|max:250',
            // 'mobile_number' => 'nullable|max:250',
            // 'email' => 'required|email|max:250',
            // 'website' => 'nullable|max:250',
            // 'currency' => 'nullable',
            // 'tin_number' => 'required_if:category,==,1|max:250',
            // 'status' => 'required',
            // 'picture' => 'nullable',
            // 'picture.*' => 'file|extensions:jpg,png,jpeg',
            // 'credit_term' => 'nullable',
            // 'sale_agent' => 'nullable',
            // 'area' => 'nullable',
            // 'debtor_type' => 'nullable',
            // 'platform' => 'nullable',
            // 'local_oversea' => 'required_if:category,==,1',
            // 'msic_code' => 'required_unless:category,!=,2',
            // 'business_activity_desc' => 'required_unless:category,!=,2',
            // 'company_registration_number' => 'required_unless:category,!=,2|max:250',
            // 'sst_number' => 'nullable|max:250',
            // 'category' => 'required|max:250',
            // 'tourism_tax_reg_no' => 'nullable|max:250',
            // 'prev_gst_reg_no' => 'nullable|max:250',
            // 'registered_name' => 'required|max:250',
            // 'trade_name' => 'nullable|max:250',
            // 'identity_type' => 'required_if:category,==,2|max:250',
            // 'identity_no' => 'nullable|max:250',
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
                    'address' => $req->address,
                    'city' => $req->city,
                    'zipcode' => $req->zip_code,
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
                    'address' => $req->address,
                    'city' => $req->city,
                    'zipcode' => $req->zip_code,
                ]);
            }
            // Sales agent
            if ($req->sale_agent != null) {
                CustomerSaleAgent::where('customer_id', $customer->id)->delete();

                $sales_agent = [];
                for ($i = 0; $i < count($req->sale_agent); $i++) {
                    $sales_agent[] = [
                        'customer_id' => $customer->id,
                        'sales_agent_id' => $req->sale_agent[$i],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                CustomerSaleAgent::insert($sales_agent);
            }
            // Create dealer if debtor type is dealer
            if ($req->debtor_type != null) {
                $debt_type_name = DebtorType::withoutGlobalScope(BranchScope::class)->where('id', $req->debtor_type)->value('name');
                $dealer_exists = Dealer::where('name', $req->customer_name)->exists();

                if (strtolower($debt_type_name) == 'dealer' && ! $dealer_exists) {
                    $new_dealer = Dealer::create([
                        'name' => $req->customer_name,
                        'company_group' => $req->company_group,
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
            'address1' => 'required',
            'address1.*' => 'required|max:250',
            'address2' => 'nullable',
            'address2.*' => 'nullable|max:250',
            'address3' => 'nullable',
            'address3.*' => 'nullable|max:250',
            'address4' => 'nullable',
            'address4.*' => 'nullable|max:250',
            'type' => 'required',
            'type.*' => 'required',
            'is_default' => 'required',
            'is_default.*' => 'required',
        ], [], [
            'address1.*' => 'address 1',
            'address2.*' => 'address 2',
            'address3.*' => 'address 3',
            'address4.*' => 'address 4',
            'type.*' => 'type',
            'is_default.*' => 'is default',
        ]);

        // Validate only 1 billing address is default or 1 delivery address is default
        $bill_and_deli_has_default = false;
        $bill_has_default = false;
        $deli_has_default = false;
        for ($i = 0; $i < count($req->address1); $i++) {
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
            for ($i = 0; $i < count($req->address1); $i++) {
                if ($req->location_id != null && $req->location_id[$i] != null) {
                    CustomerLocation::where('id', $req->location_id[$i])->update([
                        'address1' => $req->address1[$i],
                        'address2' => $req->address2[$i],
                        'address3' => $req->address3[$i],
                        'address4' => $req->address4[$i],
                        'type' => $req->type[$i],
                        'is_default' => $req->is_default[$i],
                    ]);
                } else {
                    $data[] = [
                        'customer_id' => $req->customer_id,
                        'address1' => $req->address1[$i],
                        'address2' => $req->address2[$i],
                        'address3' => $req->address3[$i],
                        'address4' => $req->address4[$i],
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
            } else if ($req->type == 'billing') {
                $locations = CustomerLocation::where('customer_id', $req->customer_id)
                    ->whereIn('type', [CustomerLocation::TYPE_BILLING_ADN_DELIVERY, CustomerLocation::TYPE_BILLING])
                    ->get();
            } else {
                $locations = CustomerLocation::where('customer_id', $req->customer_id)->get();
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
