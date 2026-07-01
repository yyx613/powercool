<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    public static function getAllowedNotificationTypes(): array
    {
        $map = [
            'notification.view_mobile_app' => 'mobile-app',
            'notification.view_service_reminder' => 'service-reminder',
            'notification.view_vehicle_service_reminder' => 'vehicle-service-reminder',
            'notification.view_production_completed' => 'production-completed',
        ];

        $allowedTypes = [];
        foreach ($map as $permission => $type) {
            if (hasPermission($permission)) {
                $allowedTypes[] = $type;
            }
        }

        // Directed/personal notifications are addressed to a specific recipient
        // (the assigned salesperson or the enquiry creator), so they are always
        // visible to whoever received them without a separate view permission.
        $allowedTypes[] = 'sale-enquiry-assigned';
        $allowedTypes[] = 'sale-enquiry-accepted';
        $allowedTypes[] = 'sale-enquiry-rejected';

        return $allowedTypes;
    }

    public function getData(Request $request)
    {
        Session::put('notification-page', $request->page);

        $user = $request->user();
        $records = $user->notifications();

        $allowedTypes = self::getAllowedNotificationTypes();
        $records = $records->whereIn('type', $allowedTypes);

        // Order
        if ($request->has('order')) {
            $map = [
                0 => 'created_at',
                1 => 'type',
                2 => DB::raw("JSON_UNQUOTE(JSON_EXTRACT(data, '$.desc'))"),
                3 => 'created_at',
            ];
            $ordered = false;
            foreach ($request->order as $order) {
                if (isset($map[$order['column']])) {
                    // reorder() drops the relation's default latest() ordering so
                    // the user-selected column actually takes effect.
                    $records = $ordered
                        ? $records->orderBy($map[$order['column']], $order['dir'])
                        : $records->reorder($map[$order['column']], $order['dir']);
                    $ordered = true;
                }
            }
        } else {
            $records = $records->orderBy('created_at', 'desc');
        }

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
