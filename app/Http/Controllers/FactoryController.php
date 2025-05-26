<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Factory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class FactoryController extends Controller
{
    public function index()
    {
        return view('factory.list');
    }

    public function getData(Request $req)
    {
        $records = new Factory();

        // Search
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];

            $records = $records->where(function ($q) use ($keyword) {
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
            ];
        }

        return response()->json($data);
    }

    public function create()
    {
        return view('factory.form');
    }

    public function store(Request $req)
    {
        // Validate request
        $validator = Validator::make($req->all(), [
            'name' => 'required|max:250',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $area = Factory::create([
                'name' => $req->name,
            ]);
            (new Branch)->assign(Factory::class, $area->id);

            DB::commit();

            if ($req->create_again == true) {
                return redirect(route('factory.create'))->with('success', 'Factory created');
            }
            return redirect(route('factory.index'))->with('success', 'Factory created');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator')->withInput();
        }
    }

    public function edit(Factory $factory)
    {
        return view('factory.form', [
            'factory' => $factory
        ]);
    }

    public function update(Request $req, Factory $factory)
    {
        // Validate request
        $validator = Validator::make($req->all(), [
            'name' => 'required|max:250',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $factory->update([
                'name' => $req->name,
            ]);

            DB::commit();

            return redirect(route('factory.index'))->with('success', 'Area updated');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator')->withInput();
        }
    }
}
