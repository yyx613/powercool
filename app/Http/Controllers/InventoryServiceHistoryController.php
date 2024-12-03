<?php

namespace App\Http\Controllers;

use App\Models\InventoryServiceHistory;
use App\Models\Product;
use App\Models\ProductChild;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class InventoryServiceHistoryController extends Controller
{
    protected $inventoryServiceHistory;

    public function __construct(InventoryServiceHistory $inventoryServiceHistory) {
        $this->inventoryServiceHistory = $inventoryServiceHistory;        
    }

    public function index() {
        return view('service_history.list');
    }

    public function getData(Request $req) {
        $records = $this->inventoryServiceHistory;

        // Search
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];

            $records = $records->where(function($q) use ($keyword) {
                $q->whereHasMorph('objectable', [Product::class, ProductChild::class], function ($query) use ($keyword) {
                    $query->where('sku', 'like', '%'.$keyword.'%');
                });
            });
        }
        // Order
        $records = $records->groupBy('object_type')->groupBy('object_id')->orderBy('id', 'desc');

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
            $next_service_date = $this->inventoryServiceHistory::where('object_type', $record->object_type)->where('object_id', $record->object_id)->orderBy('id', 'desc')->first();
            $last_service_date = $this->inventoryServiceHistory::where('object_type', $record->object_type)->where('object_id', $record->object_id)->orderBy('id', 'desc')->skip(1)->first();

            $data['data'][] = [
                'id' => Crypt::encrypt($record->id),
                'sku' => $record->objectable->sku,
                'next_service_date' => Carbon::parse($next_service_date->next_service_date)->format('d M Y'),
                'last_service_date' => $last_service_date == null ? null : Carbon::parse($last_service_date->next_service_date)->format('d M Y'),
            ];
        }
                
        return response()->json($data);
    }

    public function create() {
        return view('service_history.form');
    }

    public function view($sh) {
        $sh = Crypt::decrypt($sh);

        $sh = $this->inventoryServiceHistory::findOrFail($sh);

        return view('service_history.view', [
            'sh' => $sh
        ]);
    }

    public function viewGetData(Request $req) {
        $record = $this->inventoryServiceHistory::where('id', $req->id)->first();

        $records = $this->inventoryServiceHistory::where([
            ['object_type', $record->object_type],
            ['object_id', $record->object_id],
        ]);
        // Order
        $records = $records->orderBy('id', 'desc');

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
                'service_date' => Carbon::parse($record->next_service_date)->format('d M Y'),
            ];
        }
                
        return response()->json($data);
    }

    public function upsert(Request $req) {
        // Validate form
        $rules = [
            'product' => 'required',
            'next_service_date' => 'required',
            'remind' => 'nullable',
        ];
        $req->validate($rules, [], [
            'product' => 'product code / product serial no',
            'remind' => 'reminding days',
        ]);

        try {
            DB::beginTransaction();

            $this->inventoryServiceHistory::create([
                'object_type' => ProductChild::class,
                'object_id' => $req->product,
                'next_service_date' => $req->next_service_date,
                'reminding_days' => $req->remind,
            ]);

            DB::commit();
            
            
            if ($req->create_again == true) {
                return redirect(route('service_history.create'))->with('success', 'Service Date created');
            }
            return redirect(route('service_history.index'))->with('success', 'Service Date created');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator')->withInput();
        }
    }
}
