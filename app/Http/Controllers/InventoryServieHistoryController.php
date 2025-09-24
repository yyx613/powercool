<?php

namespace App\Http\Controllers;

use App\Exports\ServiceHistoryExport;
use App\Models\Attachment;
use App\Models\Customer;
use App\Models\ProductChild;
use App\Models\Role;
use App\Models\TaskMilestoneInventory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class InventoryServieHistoryController extends Controller
{
    public function index()
    {
        $page = Session::get('dealer-page');

        return view('service_history.list', [
            'default_page' => $page ?? null,
        ]);
    }

    public function getData(Request $req)
    {
        Session::put('dealer-page', $req->page);

        $records = TaskMilestoneInventory::where('inventory_type', ProductChild::class);

        // Search
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];

            $records = $records->where(function ($q) use ($keyword) {
                $q->where('sku', 'like', '%'.$keyword.'%')
                    ->orWhereHas('taskMilestoneInventory.taskMilestone.task', function ($q) use ($keyword) {
                        $q->where('sku', 'like', '%'.$keyword.'%');
                    })
                    ->orWhereHasMorph(
                        'stockOutTo',
                        [Customer::class, User::class],
                        function ($q) use ($keyword) {
                            $q->where('sku', 'like', '%'.$keyword.'%');
                        }
                    );
            });
        }
        // Order
        if ($req->has('order')) {
            $map = [
                0 => 'sku',
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
            $photo = Attachment::where('object_type', TaskMilestoneInventory::class)->where('object_id', $record->id)->get();
            if ($record->task_milestone_id == null) {
                $technician = User::where('id', $record->service_by)->first();
            }

            $data['data'][] = [
                'serial_no' => $record->inventory->sku,
                'task_sku' => $record->taskMilestone == null ? null : $record->taskMilestone->task->sku,
                'technician' => $record->task_milestone_id == null ? $technician : $record->stockOutTo,
                'qty' => $record->qty,
                'service_date' => $record->service_date,
                'photo' => $photo,
            ];
        }

        return response()->json($data);
    }

    public function export()
    {
        return Excel::download(new ServiceHistoryExport, 'service-history.xlsx');
    }

    public function create()
    {
        $pcs = ProductChild::get();
        $technicians = User::whereHas('roles', function ($q) {
            $q->where('id', Role::TECHNICIAN);
        })->orderBy('id', 'desc')->get();

        return view('service_history.form', [
            'pcs' => $pcs,
            'technicians' => $technicians,
        ]);
    }

    public function store(Request $req)
    {
        $req->validate([
            'service_date' => 'required',
            'service_by' => 'required',
            'serial_no' => 'required',
            'qty' => 'required',
            'photo' => 'nullable',
            'photo.*' => 'file|mimes:jpg,png,jpeg',
        ], [], [
            'qty' => 'quantity',
        ]);

        try {
            DB::beginTransaction();

            $tmi = TaskMilestoneInventory::create([
                'inventory_type' => ProductChild::class,
                'inventory_id' => $req->serial_no,
                'qty' => $req->qty,
                'pc_id' => $req->serial_no,
                'service_date' => $req->service_date,
                'service_by' => $req->service_by,
            ]);

            if ($req->hasFile('photo')) {
                foreach ($req->file('photo') as $key => $file) {
                    $path = Storage::putFile(Attachment::TASK_MILESTONE_INVENTORY_PATH, $file);
                    Attachment::create([
                        'object_type' => TaskMilestoneInventory::class,
                        'object_id' => $tmi->id,
                        'src' => basename($path),
                    ]);
                }
            }

            DB::commit();

            return redirect(route('service_history.index'))->with('success', 'Service history created');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong');
        }
    }
}
