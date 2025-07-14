<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class VehicleController extends Controller
{
    protected $vehi;

    public function __construct()
    {
        $this->vehi = new Vehicle;
    }

    public function index()
    {
        $page = Session::get('vehicle-page');

        return view('vehicle.list', [
            'default_page' => $page ?? null,
        ]);
    }

    public function getData(Request $req)
    {
        Session::put('vehicle-page', $req->page);

        $records = $this->vehi;

        // Search
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];

            $records = $records->where(function ($q) use ($keyword) {
                $q->where('plate_number', 'like', '%'.$keyword.'%')
                    ->orWhere('chasis', 'like', '%'.$keyword.'%')
                    ->orWhere('buatan_nama_model', 'like', '%'.$keyword.'%')
                    ->orWhere('keupayaan_enjin', 'like', '%'.$keyword.'%')
                    ->orWhere('bahan_bakar', 'like', '%'.$keyword.'%')
                    ->orWhere('status_asal', 'like', '%'.$keyword.'%')
                    ->orWhere('kelas_kegunaan', 'like', '%'.$keyword.'%')
                    ->orWhere('jenis_badan', 'like', '%'.$keyword.'%')
                    ->orWhere('tarikh_pendaftaran', 'like', '%'.$keyword.'%')
                    ->orWhere('department', 'like', '%'.$keyword.'%')
                    ->orWhere('area_control', 'like', '%'.$keyword.'%');
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
                'plate_number' => $record->plate_number,
                'chasis' => $record->chasis,
                'buatan_nama_model' => $record->buatan_nama_model,
                'keupayaan_enjin' => $record->keupayaan_enjin,
                'bahan_bakar' => $record->bahan_bakar,
                'status_asal' => $record->status_asal,
                'kelas_kegunaan' => $record->kelas_kegunaan,
                'jenis_badan' => $record->jenis_badan,
                'tarikh_pendaftaran' => $record->tarikh_pendaftaran,
                'department' => $record->department,
                'area_control' => $record->area_control,
            ];
        }

        return response()->json($data);
    }

    public function create()
    {
        return view('vehicle.form');
    }

    public function edit(Vehicle $vehicle)
    {
        return view('vehicle.form', [
            'vehicle' => $vehicle,
        ]);
    }

    public function upsert(Request $req, ?Vehicle $vehicle = null)
    {
        // Validate request
        $validator = Validator::make($req->all(), [
            'plate_number' => 'required|max:250',
            'chasis' => 'nullable|max:250',
            'buatan_nama_model' => 'nullable|max:250',
            'keupayaan_enjin' => 'nullable|max:250',
            'bahan_bakar' => 'nullable|max:250',
            'status_asal' => 'nullable|max:250',
            'kelas_kegunaan' => 'nullable|max:250',
            'jenis_badan' => 'nullable|max:250',
            'tarikh_pendaftaran' => 'nullable|max:250',
            'department' => 'nullable|max:250',
            'area_control' => 'nullable|max:250',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            if ($vehicle == null) {
                $new_vehicle = $this->vehi::create([
                    'plate_number' => $req->plate_number,
                    'chasis' => $req->chasis,
                    'buatan_nama_model' => $req->buatan_nama_model,
                    'keupayaan_enjin' => $req->keupayaan_enjin,
                    'bahan_bakar' => $req->bahan_bakar,
                    'status_asal' => $req->status_asal,
                    'kelas_kegunaan' => $req->kelas_kegunaan,
                    'jenis_badan' => $req->jenis_badan,
                    'tarikh_pendaftaran' => $req->tarikh_pendaftaran,
                    'department' => $req->department,
                    'area_control' => $req->area_control,
                ]);
                (new Branch)->assign(Vehicle::class, $new_vehicle->id);
            } else {
                $vehicle->update([
                    'plate_number' => $req->plate_number,
                    'chasis' => $req->chasis,
                    'buatan_nama_model' => $req->buatan_nama_model,
                    'keupayaan_enjin' => $req->keupayaan_enjin,
                    'bahan_bakar' => $req->bahan_bakar,
                    'status_asal' => $req->status_asal,
                    'kelas_kegunaan' => $req->kelas_kegunaan,
                    'jenis_badan' => $req->jenis_badan,
                    'tarikh_pendaftaran' => $req->tarikh_pendaftaran,
                    'department' => $req->department,
                    'area_control' => $req->area_control,
                ]);
            }

            DB::commit();

            if ($req->create_again == true) {
                return redirect(route('vehicle.create'))->with('success', 'Vehicle created');
            } elseif (isset($new_vehicle)) {
                return redirect(route('vehicle.index'))->with('success', 'Vehicle created');
            }

            return redirect(route('vehicle.index'))->with('success', 'Vehicle updated');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator')->withInput();
        }
    }
}
