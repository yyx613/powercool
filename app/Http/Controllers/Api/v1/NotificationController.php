<?php

namespace App\Http\Controllers\Api\v1;

use App\Models\Appointment;
use App\Models\Deal;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;
use App\Http\Controllers\Controller;
use App\Models\Milestone;
use App\Models\Task;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    public function getAll(Request $req) {
        $user = $req->user();

        $notifications = $user->notifications()->simplePaginate();

        $notifications->each(function($q) {
            if ($q->data) {
                $data = $q->data;

                if (isset($data['type'])) {
                    switch ($data['type']) {
                        case 'task_created':
                            $data['task'] = Task::withTrashed()->with('customer')->where('id', $data['task_id'])->first();
                            $data['assigned_by'] = User::withTrashed()->where('id', $data['assigned_by'])->first();
                            break;
                        case 'milestone_completed':
                            $data['task'] = Task::withTrashed()->with('customer')->where('id', $data['task_id'])->first();
                            $data['done_by'] = User::withTrashed()->where('id', $data['done_by'])->first();
                            $data['milestone'] = Milestone::where('id', $data['ms_id'])->first();
                            break;
                        case 'task_completed':
                            $data['task'] = Task::withTrashed()->with('customer')->where('id', $data['task_id'])->first();
                            $data['done_by'] = User::withTrashed()->where('id', $data['done_by'])->first();
                            break;
                    }
                }

                $q->data = $data;
            }
        });

        return Response::json([
            'result' => true,
            'notifications' => $notifications
        ], HttpFoundationResponse::HTTP_OK);
    }
    
    public function read(Request $req, $noti) {
        $user = $req->user();
        $noti = $user->notifications()->where('id', $noti)->first();
        if ($noti != null && $noti->read_at == null) {
            $noti->markAsRead();
        }

        return Response::json([
            'result' => true,
        ], HttpFoundationResponse::HTTP_OK);
    }
}
