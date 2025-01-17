<?php

namespace App\Http\Controllers;

use App\Models\Approval;
use App\Models\DeliveryOrder;
use App\Models\Sale;
use App\Models\Scopes\ApprovedScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

class ApprovalController extends Controller
{
    const STATUSES = [
        0 => 'Pending Approval',
        1 => 'Approved',
        2 => 'Rejected',
    ];

    public function index()
    {
        return view('approval.list', [
            'statuses' => self::STATUSES,
        ]);
    }

    public function getData(Request $request)
    {
        $records = Approval::latest();

        if ($request->has('status') && $request->input('status') != null) {
            if ($request->input('status') == 0) {
                $records = $records->where('status', Approval::STATUS_PENDING_APPROVAL);
            } elseif ($request->input('status') == 1) {
                $records = $records->where('status', Approval::STATUS_PENDING_APPROVAL);
            } elseif ($request->input('status') == 2) {
                $records = $records->where('status', Approval::STATUS_REJECTED);
            }
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
            $obj = $record->object()->withoutGlobalScope(ApprovedScope::class)->withTrashed()->first();
            $view_url = null;
            if ($obj != null) {
                if (get_class($obj) == DeliveryOrder::class) {
                    $view_url = route('delivery_order.index', ['sku' => $obj->sku]);
                } else if (get_class($obj) == Sale::class) {
                    $view_url = route('sale_order.index', ['sku' => $obj->sku]);
                }
            }

            $data['data'][] = [
                'no' => ($key + 1),
                'id' => $record->id,
                'type' => $obj == null ? null : get_class($obj),
                'object_sku' => $obj == null ? null : $obj->sku,
                'date' => $record->created_at,
                'pending_approval' => $record->status == Approval::STATUS_PENDING_APPROVAL,
                'rejected' => $record->status == Approval::STATUS_REJECTED,
                'view_url' => $view_url,
                'status' => $record->status,
            ];
        }

        return response()->json($data);
    }

    public function approve(Approval $approval)
    {
        $approval->status = Approval::STATUS_APPROVED;
        $approval->save();

        return Response::json([
            'result' => true,
        ], HttpFoundationResponse::HTTP_OK);
    }

    public function reject(Approval $approval)
    {
        try {
            DB::beginTransaction();

            $obj = $approval->object()->withoutGlobalScope(ApprovedScope::class)->first();

            $approval->status = Approval::STATUS_REJECTED;
            $approval->save();

            if (get_class($obj) == DeliveryOrder::class) {
                $sale_orders = Sale::whereRaw('find_in_set('.$obj->id.', convert_to)')->get();

                for ($i = 0; $i < count($sale_orders); $i++) {
                    $sale_orders[$i]->status = Sale::STATUS_ACTIVE;

                    $current_do_ids = explode(',', $sale_orders[$i]->convert_to);
                    array_splice($current_do_ids, array_search($obj->id, $current_do_ids), 1);
                    $sale_orders[$i]->convert_to = implode(',', $current_do_ids);

                    $sale_orders[$i]->save();

                    $obj->delete();
                }
            } elseif (get_class($obj) == Sale::class) {

            }

            DB::commit();

            return Response::json([
                'result' => true,
            ], HttpFoundationResponse::HTTP_OK);
        } catch (\Throwable $th) {
            report($th);
            DB::rollBack();

            return Response::json([
                'result' => false,
            ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
