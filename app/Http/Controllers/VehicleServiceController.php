<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\VehicleService;
use App\Models\VehicleServiceItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        return view('vehicle_service.list');
    }

    public function getData(Request $req)
    {
        $records = $this->vs;

        // Search
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];

            $records = $records->where(function ($q) use ($keyword) {
                $q->whereHas('vehicle', function ($q) use ($keyword) {
                    $q->where('plate_number', 'like', '%'.$keyword.'%');
                })
                    ->orWhere('petrol', 'like', '%'.$keyword.'%')
                    ->orWhere('toll', 'like', '%'.$keyword.'%');
            });
        }
        $records = $records->orderBy('id', 'desc');

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
                'vehicle_plate_number' => $record->vehicle->plate_number,
                'insurance_date' => Carbon::parse($record->insurance_date)->format('Y M d'),
                'roadtax_date' => Carbon::parse($record->roadtax_date)->format('Y M d'),
                'inspection_date' => Carbon::parse($record->inspection_date)->format('Y M d'),
                'petrol' => $record->petrol,
                'toll' => $record->toll,
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
        // Validate request
        $validator = Validator::make($req->all(), [
            'vehicle' => 'required',
            'insurance_date' => 'nullable',
            'insurance_reminder' => 'nullable',
            'insurance_amount' => 'nullable',
            'roadtax_date' => 'nullable',
            'roadtax_reminder' => 'nullable',
            'roadtax_amount' => 'nullable',
            'inspection_date' => 'nullable',
            'inspection_reminder' => 'nullable',
            'mileage_reminder' => 'nullable',
            'petrol' => 'nullable',
            'toll' => 'nullable',
            'name' => 'required',
            'name.*' => 'required',
            'amount' => 'required',
            'amount.*' => 'required',
        ], [
            'name.*.required' => 'The name at row :position is required',
            'amount.*.required' => 'The amount at row :position is required',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            if ($service == null) {
                $new_service = $this->vs::create([
                    'vehicle_id' => $req->vehicle,
                    'insurance_date' => $req->insurance_date,
                    'insurance_remind_at' => $req->insurance_reminder,
                    'insurance_amount' => $req->insurance_amount,
                    'roadtax_date' => $req->roadtax_date,
                    'roadtax_remind_at' => $req->roadtax_reminder,
                    'roadtax_amount' => $req->roadtax_amount,
                    'inspection_date' => $req->inspection_date,
                    'inspection_remind_at' => $req->inspection_reminder,
                    'mileage_remind_at' => $req->mileage_reminder,
                    'petrol' => $req->petrol,
                    'toll' => $req->toll,
                ]);
                (new Branch)->assign(VehicleService::class, $new_service->id);
            } else {
                $service->update([
                    'vehicle_id' => $req->vehicle,
                    'insurance_date' => $req->insurance_date,
                    'insurance_remind_at' => $req->insurance_reminder,
                    'insurance_amount' => $req->insurance_amount,
                    'roadtax_date' => $req->roadtax_date,
                    'roadtax_remind_at' => $req->roadtax_reminder,
                    'roadtax_amount' => $req->roadtax_amount,
                    'inspection_date' => $req->inspection_date,
                    'inspection_remind_at' => $req->inspection_reminder,
                    'mileage_remind_at' => $req->mileage_reminder,
                    'petrol' => $req->petrol,
                    'toll' => $req->toll,
                ]);
            }
            // Items
            $this->vsItem::where('vehicle_service_id', $new_service->id ?? $service->id)->forcedelete();

            $data = [];
            for ($i = 0; $i < count($req->name); $i++) {
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
