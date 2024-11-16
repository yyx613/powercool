<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ServiceController extends Controller
{
    protected $service;

    public function __construct() {
        $this->service = new Service();
    }

    public function index() {
        return view('service.list');
    }

    public function getData(Request $req) {
        $records = $this->service;

        // Search
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];

            $records = $records->where(function($q) use ($keyword) {
                $q->where('name', 'like', '%' . $keyword . '%')
                    ->orWhere('amount', 'like', '%' . $keyword . '%');
            });
        }
        // Order
        if ($req->has('order')) {
            $map = [
                0 => 'name',
                1 => 'amount',
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
                'amount' => $record->amount,
                'status' => $record->is_active,
            ];
        }

        return response()->json($data);
    }

    public function create() {
        return view('service.form');
    }

    public function store(Request $req) {
        // Validate request
        $validator = Validator::make($req->all(), [
            'name' => 'required|max:250',
            'amount' => 'required',
            'status' => 'required',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $service = $this->service::create([
                'name' => $req->name,
                'amount' => $req->amount,
                'is_active' => $req->boolean('status'),
            ]);
            (new Branch())->assign(Service::class, $service->id);

            DB::commit();

            if ($req->create_again == true) {
                return redirect(route('service.create'))->with('success', 'Service created');
            }
            return redirect(route('service.index'))->with('success', 'Service created');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator')->withInput();
        }
    }

    public function edit(Service $service) {
        return view('service.form', [
            'service' => $service
        ]);
    }

    public function update(Request $req, Service $service) {
        // Validate request
        $validator = Validator::make($req->all(), [
            'name' => 'required|max:250',
            'status' => 'required',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $service->update([
                'name' => $req->name,
                'amount' => $req->amount,
                'is_active' => $req->boolean('status'),
            ]);

            DB::commit();

            return redirect(route('service.index'))->with('success', 'Service updated');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator')->withInput();
        }
    }

    public function delete(Service $service) {
        $service->delete();

        return back()->with('success', 'Service deleted');
    }
}
