<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

class NotificationController extends Controller
{
    const STATUSES = [
        1 => 'Unread',
        2 => 'Read',
    ];

    public function index(Request $request)
    {
        if (Session::get('notification-status') != null) {
            $status = Session::get('notification-status');
        }
        $page = Session::get('notification-page');

        return view('notification.list', [
            'statuses' => self::STATUSES,
            'default_status' => $status ?? null,
            'default_page' => $page ?? null,
        ]);
    }

    public function getData(Request $request)
    {
        Session::put('notification-page', $request->page);

        $user = $request->user();
        $records = $user->notifications()->orderBy('created_at', 'desc');

        if ($request->has('status')) {
            if ($request->status == null) {
                Session::remove('notification-status');
            } else if ($request->status == 1) {
                $records = $records->whereNull('read_at');
                Session::put('notification-status', $request->status);
            } elseif ($request->status == 2) {
                $records = $records->whereNotNull('read_at');
                Session::put('notification-status', $request->status);
            }
        } else if (Session::get('notification-status') != null) {
            if (Session::get('notification-status') == 1) {
                $records = $records->whereNull('read_at');
            } elseif (Session::get('notification-status') == 2) {
                $records = $records->whereNotNull('read_at');
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
            $data['data'][] = [
                'no' => ($key + 1),
                'id' => $record->id,
                'type' => str_replace('-', ' ', $record->type),
                'desc' => $record->data['desc'],
                'data' => $record->data,
                'read_at' => $record->read_at,
                'date' => $record->created_at,
            ];
        }

        return response()->json($data);
    }

    public function read(Request $request, $id)
    {
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
