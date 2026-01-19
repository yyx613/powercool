<?php

namespace App\Http\Controllers;

use App\Models\AdhocService;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

class AdhocServiceController extends Controller
{
    protected $service;

    public function __construct()
    {
        $this->service = new AdhocService;
    }

    public function index()
    {
        $page = Session::get('adhoc-service-page');

        return view('adhoc_service.list', [
            'default_page' => $page ?? null,
        ]);
    }

    public function getData(Request $req)
    {
        $records = $this->service;

        Session::put('adhoc-service-page', $req->page);

        // Search
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];

            $records = $records->where(function ($q) use ($keyword) {
                $q->where('sku', 'like', '%' . $keyword . '%')
                    ->orWhere('name', 'like', '%' . $keyword . '%');
            });
        }
        // Order
        if ($req->has('order')) {
            $map = [
                0 => 'sku',
                1 => 'name',
                2 => 'amount',
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
            'recordsTotal' => $records_count,
            'recordsFiltered' => $records_count,
            'data' => [],
            'records_ids' => $records_ids,
        ];
        foreach ($records_paginator as $record) {
            $data['data'][] = [
                'id' => $record->id,
                'sku' => $record->sku,
                'name' => $record->name,
                'amount' => number_format($record->amount, 2),
                'status' => $record->is_active,
            ];
        }

        return response()->json($data);
    }

    public function create()
    {
        $is_hi_ten = Session::get('is_hi_ten') ?? false;
        $sku = $this->service->generateSku($is_hi_ten);

        return view('adhoc_service.form', [
            'sku' => $sku,
        ]);
    }

    public function edit(AdhocService $service)
    {
        return view('adhoc_service.form', [
            'service' => $service,
        ]);
    }

    public function upsert(Request $req)
    {
        // Validate request
        $validator = Validator::make($req->all(), [
            'name' => 'required|max:250',
            'amount' => 'required|numeric|min:0',
            'status' => 'required',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            if ($req->service_id != null) {
                // Update
                $service = AdhocService::find($req->service_id);
                $service->update([
                    'name' => $req->name,
                    'amount' => $req->amount,
                    'is_active' => $req->status,
                ]);

                DB::commit();

                return redirect(route('adhoc_service.index'))->with('success', 'Ad-hoc Service updated');
            } else {
                // Create
                $is_hi_ten = Session::get('is_hi_ten') ?? false;
                $service = $this->service::create([
                    'sku' => $this->service->generateSku($is_hi_ten),
                    'name' => $req->name,
                    'amount' => $req->amount,
                    'is_active' => $req->status,
                ]);
                (new Branch)->assign(AdhocService::class, $service->id);

                DB::commit();

                if ($req->create_again == true) {
                    return redirect(route('adhoc_service.create'))->with('success', 'Ad-hoc Service created');
                }

                return redirect(route('adhoc_service.index'))->with('success', 'Ad-hoc Service created');
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator')->withInput();
        }
    }

    public function delete(AdhocService $service)
    {
        $service->delete();

        return back()->with('success', 'Ad-hoc Service deleted');
    }

    public function search(Request $req)
    {
        $keyword = $req->keyword;

        $services = AdhocService::where('is_active', true)
            ->where(function ($q) use ($keyword) {
                $q->where('sku', 'like', '%' . $keyword . '%')
                    ->orWhere('name', 'like', '%' . $keyword . '%');
            })
            ->limit(20)
            ->get();

        return Response::json([
            'services' => $services,
        ], HttpFoundationResponse::HTTP_OK);
    }
}
