<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Platform;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class PlatformController extends Controller
{
    protected $platform;

    public function __construct() {
        $this->platform = new Platform;
    }

    public function index() {
        $page = Session::get('platform-page');

        return view('platform.list', [
            'default_page' => $page ?? null,
        ]);
    }

    public function getData(Request $req) {
        $records = $this->platform;

        Session::put('platform-page', $req->page);

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
                1 => 'can_submit_einvoice',
                2 => 'is_active',
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
                'eInvoice' => $record->can_submit_einvoice,
                'status' => $record->is_active,
            ];
        }
                
        return response()->json($data);
    }

    public function create() {
        return view('platform.form');
    }

    public function store(Request $req) {
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

            $dt = $this->platform::create([
                'name' => $req->name,
                'is_active' => $req->status,
                'can_submit_einvoice' => $req->can_submit_einvoice
            ]);
            (new Branch)->assign(Platform::class, $dt->id);

            DB::commit();

            if ($req->create_again == true) {
                return redirect(route('platform.create'))->with('success', 'Platform created');
            }
            return redirect(route('platform.index'))->with('success', 'Platform created');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator')->withInput();
        }
    }

    public function edit(Platform $platform) {
        return view('platform.form', [
            'platform' => $platform
        ]);
    }

    public function update(Request $req, Platform $platform) {
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

            $platform->update([
                'name' => $req->name,
                'is_active' => $req->status,
                'can_submit_einvoice' => $req->can_submit_einvoice
            ]);

            DB::commit();

            return redirect(route('platform.index'))->with('success', 'Platform updated');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator')->withInput();
        }
    }
}
