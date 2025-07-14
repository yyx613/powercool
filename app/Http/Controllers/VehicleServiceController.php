<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\VehicleService;
use App\Models\VehicleServiceItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class VehicleServiceController extends Controller
{
    protected $vs;

    protected $vsItem;

    public function __construct()
    {
        $this->vs = new VehicleService;
        $this->vsItem = new VehicleServiceItem;
    }

    public function index()
    {
        $page = Session::get('vehicle-service-page');

        return view('vehicle_service.list', [
            'default_page' => $page ?? null,
        ]);
    }

    public function getData(Request $req)
    {
        Session::put('vehicle-service-page', $req->page);

        $records = $this->vs;
        $records = $records
            ->select(
                'vehicle_services.*', 'vehicles.plate_number AS plateNumber',
            )
            ->leftJoin('vehicles', 'vehicles.id', '=', 'vehicle_services.vehicle_id');
            

        // Search
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];

            $records = $records->where(function ($q) use ($keyword) {
                $q->where('vehicles.plate_number', 'like', '%'.$keyword.'%')
                    ->orWhere('vehicle_services.amount', 'like', '%'.$keyword.'%');
            });
        }
        // Order
        if ($req->has('order')) {
            $map = [
                0 => 'vehicles.plate_number',
                2 => 'vehicle_services.date',
                3 => 'vehicle_services.remind_at',
                4 => 'vehicle_services.amount',
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
                'vehicle_plate_number' => $record->plateNumber,
                'service' => VehicleService::types[$record->type],
                'date' => $record->date == null ? null : Carbon::parse($record->date)->format('Y M d'),
                'reminder_date' => $record->remind_at == null ? null : Carbon::parse($record->remind_at)->format('Y M d'),
                'amount' => $record->amount == null ? null : number_format($record->amount, 2),
            ];
        }

        return response()->json($data);
    }

    public function create()
    {
        return view('vehicle_service.form');
    }

    public function edit(VehicleService $service)
    {
        $service->load('items');

        return view('vehicle_service.form', [
            'service' => $service,
        ]);
    }

    public function upsert(Request $req, ?VehicleService $service = null)
    {
        $rules = [
            'vehicle' => 'required',
            'service' => 'required',
            'name' => 'required',
            'name.*' => 'nullable',
            'amount' => 'required',
            'amount.*' => 'nullable',
        ];
        if ($req->service != null) {
            if ($req->service == 1 || $req->service == 2) {
                $rules['date'] = 'required';
                $rules['reminder_date'] = 'required';
                $rules['service_amount'] = 'required';
            } elseif ($req->service == 3) {
                $rules['date'] = 'required';
                $rules['reminder_date'] = 'required';
                $rules['service_amount'] = 'nullable';
            } elseif ($req->service == 4) {
                $rules['date'] = 'nullable';
                $rules['reminder_date'] = 'required';
                $rules['service_amount'] = 'nullable';
            } elseif ($req->service == 5 || $req->service == 6) {
                $rules['date'] = 'nullable';
                $rules['reminder_date'] = 'nullable';
                $rules['service_amount'] = 'nullable';
            } elseif ($req->service == 7 || $req->service == 8) {
                $rules['date'] = 'nullable';
                $rules['reminder_date'] = 'nullable';
                $rules['service_amount'] = 'required';
            }
        }
        // Validate request
        $validator = Validator::make($req->all(), $rules, [
            'name.*.required' => 'The name at row :position is required',
            'amount.*.required' => 'The amount at row :position is required',
        ], [
            'service_amount' => 'amount',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            if ($service == null) {
                $new_service = $this->vs::create([
                    'vehicle_id' => $req->vehicle,
                    'type' => $req->service,
                    'date' => $req->date,
                    'remind_at' => $req->reminder_date,
                    'amount' => $req->service_amount,
                ]);
                (new Branch)->assign(VehicleService::class, $new_service->id);
            } else {
                $service->update([
                    'vehicle_id' => $req->vehicle,
                    'type' => $req->service,
                    'date' => $req->date,
                    'remind_at' => $req->reminder_date,
                    'amount' => $req->service_amount,
                ]);
            }
            // Items
            $this->vsItem::where('vehicle_service_id', $new_service->id ?? $service->id)->forcedelete();

            $data = [];
            for ($i = 0; $i < count($req->name); $i++) {
                if ($req->name[$i] == null && $req->amount[$i] == null) {
                    continue;
                }

                $data[] = [
                    'vehicle_service_id' => $new_service->id ?? $service->id,
                    'name' => $req->name[$i],
                    'amount' => $req->amount[$i],
                ];
            }

            $this->vsItem::insert($data);

            DB::commit();

            if ($req->create_again == true || isset($new_service)) {
                return redirect(route('vehicle_service.create'))->with('success', 'Vehicle Service created');
            }

            return redirect(route('vehicle_service.index'))->with('success', 'Vehicle Service updated');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator')->withInput();
        }
    }
}
