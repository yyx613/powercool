<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

class NotificationController extends Controller
{
    const TYPES = [
        1 => 'Reminder',
        2 => 'Approval',
    ];
    const STATUSES = [
        1 => 'Unread',
        2 => 'Read',
    ];

    public function index(Request $request) {
        return view('notification.list', [
            'types' => self::TYPES,
            'statuses' => self::STATUSES,
        ]);
    }

    public function getData(Request $request) {
        $user = $request->user();
        $records = $user->notifications()->orderBy('created_at', 'desc');

        if ($request->has('type') && $request->input('type') != null) {
            if ($request->input('type') == 1) {
                $records = $records->where('type', 'reminder');
            } else if ($request->input('type') == 2) {
                $records = $records->where('type', 'approval');
            }
        }
        if ($request->has('status') && $request->input('status') != null) {
            if ($request->input('status') == 1) {
                $records = $records->whereNull('read_at');
            } else if ($request->input('status') == 2) {
                $records = $records->whereNotNull('read_at');
            }
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
                'no' => ($key + 1),
                'id' => $record->id,
                'type' => $record->type,
                'desc' => $record->data['desc'],
                'data' => $record->data,
                'read_at' => $record->read_at,
                'date' => $record->created_at,
            ];
        }
                
        return response()->json($data);
    }

    public function read(Request $request, $id) {
        $unread_notis = $request->user()->unreadNotifications;
    
        foreach ($unread_notis as $noti) {
            if ($noti->id == $id) {
                $noti->markAsRead();
                break;
            }
        }
        
        return Response::json([
            'result' => true,
        ], HttpFoundationResponse::HTTP_OK);
    }
}
