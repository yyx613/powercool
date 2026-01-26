<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class StateController extends Controller
{
    protected $state;

    public function __construct()
    {
        $this->state = new State;
    }

    public function index()
    {
        $page = Session::get('state-page');

        return view('state.list', [
            'default_page' => $page ?? null,
        ]);
    }

    public function getData(Request $req)
    {
        $records = $this->state->with('country');

        Session::put('state-page', $req->page);

        // Search
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];

            $records = $records->where(function ($q) use ($keyword) {
                $q->where('name', 'like', '%' . $keyword . '%')
                    ->orWhere('code', 'like', '%' . $keyword . '%')
                    ->orWhereHas('country', function ($q2) use ($keyword) {
                        $q2->where('name', 'like', '%' . $keyword . '%');
                    });
            });
        }
        // Order
        if ($req->has('order')) {
            $map = [
                0 => 'name',
                1 => 'code',
                2 => 'country_id',
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
                'country' => $record->country->name ?? '-',
                'status' => $record->is_active,
            ];
        }

        return response()->json($data);
    }

    public function create()
    {
        $countries = Country::where('is_active', true)->orderBy('name')->get();

        return view('state.form', [
            'countries' => $countries,
        ]);
    }

    public function store(Request $req)
    {
        // Validate request
        $validator = Validator::make($req->all(), [
            'country_id' => 'required|exists:countries,id',
            'name' => 'required|max:250',
            'code' => 'nullable|max:10',
            'status' => 'required',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $this->state::create([
                'country_id' => $req->country_id,
                'name' => $req->name,
                'code' => $req->code,
                'is_active' => $req->status,
            ]);

            DB::commit();

            if ($req->create_again == true) {
                return redirect(route('state.create'))->with('success', 'State created');
            }
            return redirect(route('state.index'))->with('success', 'State created');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator')->withInput();
        }
    }

    public function edit(State $state)
    {
        $countries = Country::where('is_active', true)->orderBy('name')->get();

        return view('state.form', [
            'state' => $state,
            'countries' => $countries,
        ]);
    }

    public function update(Request $req, State $state)
    {
        // Validate request
        $validator = Validator::make($req->all(), [
            'country_id' => 'required|exists:countries,id',
            'name' => 'required|max:250',
            'code' => 'nullable|max:10',
            'status' => 'required',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $state->update([
                'country_id' => $req->country_id,
                'name' => $req->name,
                'code' => $req->code,
                'is_active' => $req->status,
            ]);

            DB::commit();

            return redirect(route('state.index'))->with('success', 'State updated');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator')->withInput();
        }
    }

    public function delete(State $state)
    {
        try {
            DB::beginTransaction();

            $state->delete();

            DB::commit();

            return redirect(route('state.index'))->with('success', 'State deleted');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator');
        }
    }
}
