<?php

namespace App\Http\Controllers;

use App\Exports\ServiceHistoryExport;
use App\Models\Customer;
use App\Models\ProductChild;
use App\Models\TaskMilestoneInventory;
use App\Models\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class InventoryServieHistoryController extends Controller
{
    public function index()
    {
        return view('service_history.list');
    }

    public function getData(Request $req)
    {
        $tmi_ids = TaskMilestoneInventory::where('inventory_type', ProductChild::class)->pluck('inventory_id');

        $records = ProductChild::whereIn('id', $tmi_ids);

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
                'serial_no' => $record->sku,
                'task_sku' => $record->taskMilestoneInventory->taskMilestone->task->sku,
                'technician' => $record->stockOutTo,
            ];
        }

        return response()->json($data);
    }

    public function export()
    {
        return Excel::download(new ServiceHistoryExport, 'service-history.xlsx');
    }
}
