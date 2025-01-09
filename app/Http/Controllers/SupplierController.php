<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Branch;
use App\Models\GRN;
use App\Models\ObjectCreditTerm;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class SupplierController extends Controller
{
    public function index()
    {
        return view('supplier.list');
    }

    public function getData(Request $req)
    {
        $records = new Supplier;

        // Search
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];

            $records = $records->where(function ($q) use ($keyword) {
                $q->where('name', 'like', '%'.$keyword.'%')
                    ->orWhere('sku', 'like', '%'.$keyword.'%')
                    ->orWhere('phone', 'like', '%'.$keyword.'%')
                    ->orWhere('company_name', 'like', '%'.$keyword.'%');
            });
        }
        // Order
        if ($req->has('order')) {
            $map = [
                0 => 'sku',
                1 => 'name',
                2 => 'phone',
                3 => 'company_name',
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
                'sku' => $record->sku,
                'name' => $record->name,
                'phone_number' => $record->phone,
                'company_name' => $record->company_name,
                'can_edit' => hasPermission('supplier.edit'),
                'can_delete' => hasPermission('supplier.delete'),
            ];
        }

        return response()->json($data);
    }

    public function create()
    {
        return view('supplier.form');
    }

    public function edit(Supplier $supplier)
    {
        $supplier->load('pictures');

        return view('supplier.form', [
            'supplier' => $supplier,
        ]);
    }

    public function delete(Supplier $supplier)
    {
        $supplier->delete();

        return back()->with('success', 'Supplier deleted');
    }

    public function upsert(Request $req, Supplier $supplier)
    {
        $is_create = $supplier->id == null;

        // Validate request
        $req->validate([
            'company_group' => 'required',
            'prefix' => 'nullable',
            'customer_name' => 'required|max:250',
            'company_name' => 'required|max:250',
            'company_registration_number' => 'nullable|max:250',
            'phone_number' => 'required|max:250',
            'mobile_number' => 'nullable|max:250',
            'email' => 'required|email|max:250',
            'website' => 'nullable|max:250',
            'currency' => 'required',
            'status' => 'required',
            'type' => 'nullable',
            'picture' => 'nullable',
            'picture.*' => 'file|extensions:jpg,png,jpeg',
            'location' => 'required',
            'credit_term' => 'nullable',
            'sale_agent' => 'nullable',
            'area' => 'nullable',
            'debtor_type' => 'nullable',
            'category' => 'required',
            'tin_number' => 'required_if:category,==,1|max:250',
            'local_oversea' => 'required_if:category,==,1',
            'msic_code' => 'required_unless:category,!=,2',
            'business_activity_desc' => 'required_unless:category,!=,2',
            'company_registration_number' => 'required_unless:category,!=,2|max:250',
            'sst_number' => 'nullable|max:250',
            'category' => 'required|max:250',
            'business_activity_desc' => 'required|max:250',
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
                    'tin_number' => $err->title,
                    'tin_number_hasil' => true,
                ]);
            }
        }

        try {
            DB::beginTransaction();

            if ($supplier->id == null) {
                $supplier = Supplier::create([
                    'sku' => (new Supplier)->generateSku($req->company_name[0]),
                    'name' => $req->customer_name,
                    'phone' => $req->phone_number,
                    'mobile_number' => $req->mobile_number,
                    'currency_id' => $req->currency,
                    'is_active' => $req->boolean('status'),
                    'type' => $req->local_oversea,
                    'company_name' => $req->company_name,
                    'company_registration_number' => $req->company_registration_number,
                    'website' => $req->website,
                    'prefix' => $req->prefix,
                    'email' => $req->email,
                    'remark' => $req->remark,
                    'tin_number' => $req->tin_number,
                    'location' => $req->location,
                    'sale_agent' => $req->sale_agent,
                    'area_id' => $req->area,
                    'debtor_type_id' => $req->debtor_type,
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

                (new Branch)->assign(Supplier::class, $supplier->id);
            } else {
                $supplier->update([
                    'name' => $req->customer_name,
                    'phone' => $req->phone_number,
                    'mobile_number' => $req->mobile_number,
                    'currency_id' => $req->currency,
                    'is_active' => $req->boolean('status'),
                    'type' => $req->local_oversea,
                    'company_name' => $req->company_name,
                    'company_registration_number' => $req->company_registration_number,
                    'website' => $req->website,
                    'prefix' => $req->prefix,
                    'email' => $req->email,
                    'remark' => $req->remark,
                    'tin_number' => $req->tin_number,
                    'location' => $req->location,
                    'sale_agent' => $req->sale_agent,
                    'area_id' => $req->area,
                    'debtor_type_id' => $req->debtor_type,
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

            if ($req->hasFile('picture')) {
                if ($supplier != null) {
                    Attachment::where([
                        ['object_type', Supplier::class],
                        ['object_id', $supplier->id],
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

            // Credit Terms
            if ($req->credit_term != null) {
                ObjectCreditTerm::where('object_type', Supplier::class)->where('object_id', $supplier->id)->delete();

                $terms = [];
                for ($i = 0; $i < count($req->credit_term); $i++) {
                    $terms[] = [
                        'object_type' => Supplier::class,
                        'object_id' => $supplier->id,
                        'credit_term_id' => $req->credit_term[$i],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                ObjectCreditTerm::insert($terms);
            }

            DB::commit();

            return redirect(route('supplier.index'))->with('success', 'Supplier '.($is_create ? 'created' : 'updated'));
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator')->withInput();
        }
    }

    public function grnHistory(Supplier $supplier)
    {
        $formatted_grns = [];
        $formatted_products = [];

        $grns = GRN::where('supplier_id', $supplier->id)->orderBy('id', 'desc')->get();

        $product_ids = [];
        for ($i = 0; $i < count($grns); $i++) {
            $formatted_grns[$grns[$i]->product_id][] = $grns[$i];
            $product_ids[] = $grns[$i]->product_id;
        }
        $product_ids = array_unique($product_ids);

        $products = Product::withTrashed()->whereIn('id', $product_ids)->get();
        for ($i = 0; $i < count($products); $i++) {
            $formatted_products[$products[$i]->id] = $products[$i];
        }

        return view('supplier.grn_history', [
            'supplier' => $supplier,
            'formatted_grns' => $formatted_grns,
            'formatted_products' => $formatted_products,
        ]);
    }
}
