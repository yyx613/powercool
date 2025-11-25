<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Product;
use App\Models\SaleEnquiry;
use App\Models\Scopes\BranchScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class SaleEnquiryController extends Controller
{
    public function index()
    {
        $page = Session::get('sale-enquiry-page');
        $search = Session::get('sale-enquiry-search');

        return view('sale_enquiry.list', [
            'default_page' => $page ?? null,
            'default_search' => $search ?? null,
        ]);
    }

    public function getData(Request $req)
    {
        Session::put('sale-enquiry-page', $req->page);
        Session::put('sale-enquiry-search', $req->search['value'] ?? null);

        $records = SaleEnquiry::query();

        // Search
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];

            $records->where(function ($q) use ($keyword) {
                $q->where('sku', 'like', '%' . $keyword . '%')
                    ->orWhere('name', 'like', '%' . $keyword . '%')
                    ->orWhere('phone_number', 'like', '%' . $keyword . '%')
                    ->orWhere('email', 'like', '%' . $keyword . '%')
                    ->orWhere('description', 'like', '%' . $keyword . '%')
                    ->orWhere('category', 'like', '%' . $keyword . '%');
            });
        }

        // Order
        if ($req->has('order')) {
            $map = [
                0 => 'sku',
                1 => 'enquiry_date',
                2 => 'name',
                3 => 'phone_number',
                4 => 'email',
                5 => 'enquiry_source',
            ];
            foreach ($req->order as $order) {
                $records->orderBy($map[$order['column']], $order['dir']);
            }
        } else {
            $records->orderBy('id', 'desc');
        }

        $records->with(['product', 'assignedUser']);

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
            // Get priority label
            $priorityLabel = null;
            switch ($record->priority) {
                case 1:
                    $priorityLabel = 'Low';
                    break;
                case 2:
                    $priorityLabel = 'Medium';
                    break;
                case 3:
                    $priorityLabel = 'High';
                    break;
            }

            $data['data'][] = [
                'id' => $record->id,
                'sku' => $record->sku,
                'enquiry_date' => $record->enquiry_date->format('d M Y'),
                'name' => $record->name,
                'phone_number' => $record->phone_number,
                'email' => $record->email,
                'enquiry_source' => $record->enquiry_source,
                'product' => $record->product ? $record->product->model_name : null,
                'assigned_user' => $record->assignedUser ? $record->assignedUser->name : null,
                'priority' => $priorityLabel,
                'status' => $record->status,
                'can_view' => hasPermission('sale_enquiry.view'),
                'can_edit' => hasPermission('sale_enquiry.edit'),
                'can_delete' => hasPermission('sale_enquiry.delete'),
            ];
        }

        return response()->json($data);
    }

    public function view(SaleEnquiry $enquiry)
    {
        return view('sale_enquiry.view', [
            'enquiry' => $enquiry
        ]);
    }

    public function getViewData(Request $req)
    {
        $enquiry = SaleEnquiry::find($req->enquiry_id);

        if (!$enquiry) {
            return response()->json([
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
            ]);
        }

        $records = $enquiry->sales()->with('customer');

        // Search
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];

            $records->where(function ($q) use ($keyword) {
                $q->where('sku', 'like', '%' . $keyword . '%')
                    ->orWhereHas('customer', function ($q) use ($keyword) {
                        $q->where('company_name', 'like', '%' . $keyword . '%');
                    });
            });
        }

        // Order
        if ($req->has('order')) {
            $map = [
                0 => 'sku',
            ];
            foreach ($req->order as $order) {
                if (isset($map[$order['column']])) {
                    $records->orderBy($map[$order['column']], $order['dir']);
                }
            }
        } else {
            $records->orderBy('id', 'desc');
        }

        $records_count = $records->count();
        $records_paginator = $records->simplePaginate(10);

        $data = [
            'recordsTotal' => $records_count,
            'recordsFiltered' => $records_count,
            'data' => [],
        ];

        foreach ($records_paginator as $sale) {
            $data['data'][] = [
                'sku' => $sale->sku,
                'customer' => $sale->customer ? $sale->customer->company_name : null,
                'payment_status' => $sale->payment_status,
            ];
        }

        return response()->json($data);
    }

    public function create()
    {
        return view('sale_enquiry.form');
    }

    public function store(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'enquiry_date' => 'required|date',
            'enquiry_source' => 'required|in:1,2,3,4,5,6',
            'name' => 'required|max:250',
            'phone_number' => 'required|max:50',
            'email' => 'nullable|email|max:250',
            'preferred_contact_method' => 'nullable|in:1,2,3',
            'country' => 'nullable|max:250',
            'state' => 'nullable|max:250',
            'category' => 'nullable|max:250',
            'description' => 'nullable',
            'product_id' => 'nullable|exists:products,id',
            'assigned_user_id' => 'nullable|exists:users,id',
            'priority' => 'nullable|in:1,2,3',
            'status' => 'required|in:1,2,3,4',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $existing_skus = SaleEnquiry::withoutGlobalScope(BranchScope::class)->pluck('sku')->toArray();

            $enquiry = SaleEnquiry::create([
                'sku' => generateSku('ENQ', $existing_skus, false),
                'enquiry_date' => $req->enquiry_date,
                'enquiry_source' => $req->enquiry_source,
                'name' => $req->name,
                'phone_number' => $req->phone_number,
                'email' => $req->email,
                'preferred_contact_method' => $req->preferred_contact_method,
                'country' => $req->country,
                'state' => $req->state,
                'category' => $req->category,
                'description' => $req->description,
                'product_id' => $req->product_id,
                'assigned_user_id' => $req->assigned_user_id,
                'priority' => $req->priority,
                'status' => $req->status,
            ]);

            (new Branch)->assign(SaleEnquiry::class, $enquiry->id);

            DB::commit();

            return redirect()->route('sale_enquiry.index')
                ->with('success', 'Sale enquiry created successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function edit(SaleEnquiry $enquiry)
    {
        return view('sale_enquiry.form', [
            'enquiry' => $enquiry
        ]);
    }

    public function update(Request $req, SaleEnquiry $enquiry)
    {
        $validator = Validator::make($req->all(), [
            'enquiry_date' => 'required|date',
            'enquiry_source' => 'required|in:1,2,3,4,5,6',
            'name' => 'required|max:250',
            'phone_number' => 'required|max:50',
            'email' => 'nullable|email|max:250',
            'preferred_contact_method' => 'nullable|in:1,2,3',
            'country' => 'nullable|max:250',
            'state' => 'nullable|max:250',
            'category' => 'nullable|max:250',
            'description' => 'nullable',
            'product_id' => 'nullable|exists:products,id',
            'assigned_user_id' => 'nullable|exists:users,id',
            'priority' => 'nullable|in:1,2,3',
            'status' => 'required|in:1,2,3,4',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $enquiry->update([
                'enquiry_date' => $req->enquiry_date,
                'enquiry_source' => $req->enquiry_source,
                'name' => $req->name,
                'phone_number' => $req->phone_number,
                'email' => $req->email,
                'preferred_contact_method' => $req->preferred_contact_method,
                'country' => $req->country,
                'state' => $req->state,
                'category' => $req->category,
                'description' => $req->description,
                'product_id' => $req->product_id,
                'assigned_user_id' => $req->assigned_user_id,
                'priority' => $req->priority,
                'status' => $req->status,
            ]);

            DB::commit();

            return redirect()->route('sale_enquiry.index')
                ->with('success', 'Sale enquiry updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function getProducts(Request $req)
    {
        try {
            $keyword = $req->keyword;

            $products = Product::where('type', Product::TYPE_PRODUCT)
                ->where('is_active', true)
                ->where(function ($q) use ($keyword) {
                    $q->where('model_name', 'like', '%' . $keyword . '%')
                        ->orWhere('sku', 'like', '%' . $keyword . '%');
                })
                ->orderBy('model_name', 'asc')
                ->get();

            return response()->json([
                'products' => $products
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function delete(SaleEnquiry $enquiry)
    {
        try {
            $enquiry->delete();

            return redirect()->route('sale_enquiry.index')
                ->with('success', 'Sale enquiry deleted successfully');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
