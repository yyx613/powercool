<?php

namespace App\Http\Controllers;

use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class CountryController extends Controller
{
    protected $country;

    public function __construct()
    {
        $this->country = new Country;
    }

    public function index()
    {
        $page = Session::get('country-page');

        return view('country.list', [
            'default_page' => $page ?? null,
        ]);
    }

    public function getData(Request $req)
    {
        $records = $this->country->withCount('states');

        Session::put('country-page', $req->page);

        // Search
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];

            $records = $records->where(function ($q) use ($keyword) {
                $q->where('name', 'like', '%' . $keyword . '%')
                    ->orWhere('code', 'like', '%' . $keyword . '%');
            });
        }
        // Order
        if ($req->has('order')) {
            $map = [
                0 => 'name',
                1 => 'code',
                2 => 'states_count',
                3 => 'is_active',
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
                'code' => $record->code,
                'states_count' => $record->states_count,
                'status' => $record->is_active,
            ];
        }

        return response()->json($data);
    }

    public function create()
    {
        return view('country.form');
    }

    public function store(Request $req)
    {
        // Validate request
        $validator = Validator::make($req->all(), [
            'name' => 'required|max:250',
            'code' => 'nullable|max:10',
            'status' => 'required',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $this->country::create([
                'name' => $req->name,
                'code' => $req->code,
                'is_active' => $req->status,
            ]);

            DB::commit();

            if ($req->create_again == true) {
                return redirect(route('country.create'))->with('success', 'Country created');
            }
            return redirect(route('country.index'))->with('success', 'Country created');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator')->withInput();
        }
    }

    public function edit(Country $country)
    {
        return view('country.form', [
            'country' => $country
        ]);
    }

    public function update(Request $req, Country $country)
    {
        // Validate request
        $validator = Validator::make($req->all(), [
            'name' => 'required|max:250',
            'code' => 'nullable|max:10',
            'status' => 'required',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $country->update([
                'name' => $req->name,
                'code' => $req->code,
                'is_active' => $req->status,
            ]);

            DB::commit();

            return redirect(route('country.index'))->with('success', 'Country updated');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator')->withInput();
        }
    }

    public function delete(Country $country)
    {
        // Check if country has states
        if ($country->states()->count() > 0) {
            return back()->with('warning', 'Cannot delete country with existing states. Please delete states first.');
        }

        try {
            DB::beginTransaction();

            $country->delete();

            DB::commit();

            return redirect(route('country.index'))->with('success', 'Country deleted');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator');
        }
    }

    public function getStates(Country $country)
    {
        $states = $country->activeStates()->orderBy('name')->get(['id', 'name', 'code']);

        return response()->json($states);
    }
}
