<?php

namespace App\Http\Controllers;

use App\Models\InventoryCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;
use Illuminate\Support\Facades\Response;

class InventoryCategoryController extends Controller
{
    const FORM_RULES = [
        'category_id' => 'nullable',
        'name' => 'required|max:250',
        'status' => 'required',
    ];

    protected $invCat;

    public function __construct() {
        $this->invCat = new InventoryCategory;
    }

    public function index() {
        return view('inventory_category.list');
    }

    public function getData(Request $req) {
        $records = $this->invCat;

        // Search
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];

            $records = $records->where(function($q) use ($keyword) {
                $q->where('name', 'like', '%' . $keyword . '%');
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
                'name' => $record->name,
                'status' => $record->is_active,
            ];
        }

        return response()->json($data);
    }

    public function create() {
        return view('inventory_category.form');
    }

    public function edit(InventoryCategory $cat) {
        return view('inventory_category.form', [
            'cat' => $cat,
        ]);
    }

    public function upsert(Request $req) {
        // Validate request
        $req->validate(self::FORM_RULES);

        try {
            DB::beginTransaction();

            if ($req->category_id == null) {
                $cat = $this->invCat::create([
                    'name' => $req->name,
                    'is_active' => $req->boolean('status'),
                ]);
            } else {
                $cat = $this->invCat->where('id', $req->category_id)->first();

                $cat->update([
                    'name' => $req->name,
                    'is_active' => $req->boolean('status'),
                ]);
            }

            DB::commit();

            return Response::json([
                'result' => true,
                'category' => $cat,
            ], HttpFoundationResponse::HTTP_OK);
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return Response::json([
                'result' => false
            ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function delete(InventoryCategory $cat) {
        $cat->delete();

        return back()->with('success', 'Category deleted');
    }
}
