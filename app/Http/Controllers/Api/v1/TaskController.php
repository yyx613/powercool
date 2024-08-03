<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Attachment;
use App\Models\Milestone;
use App\Models\Task;
use App\Models\TaskMilestone;
use App\Models\User;
use App\Models\UserTask;
use App\Notifications\SystemNotification;
use Carbon\Carbon;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class TaskController extends Controller
{
    public function getStatistic(Request $req) {
        try {
            $today_completed_task_count = 0;
            $today_task_count = 0;
            $cash_collected = 0;
            $outstanding = 0;
            
            $tasks = Task::where('start_date', now()->format('Y-m-d'))->whereHas('users', function(Builder $q) use ($req) {
                $q->where('user_id', $req->user()->id);
            })->get();

            $today_task_count = count($tasks);
            for ($i=0; $i < count($tasks); $i++) { 
                $not_completed = TaskMilestone::where('task_id', $tasks[$i]->id)->whereNull('submitted_at')->exists();

                if (!$not_completed) {
                    $today_completed_task_count++;
                }

                // Get cash collected
                foreach ($tasks[$i]->milestones as $task_ms) {
                    if (in_array($task_ms->pivot->milestone_id, getPaymentCollectionIds())) {
                        $cash_collected += $task_ms->pivot->amount_collected;
                    }
                }
                $outstanding += $tasks[$i]->amount_to_collect;
            }
            
            return Response::json([
                'cash_collected' => $cash_collected,
                'outstanding' => $outstanding,
                'today_task_count' => $today_task_count,
                'today_completed_task_count' => $today_completed_task_count,
            ], HttpFoundationResponse::HTTP_OK);
        } catch (\Throwable $th) {
            report($th);

            return Response::json([
                'msg' => 'something went wrong'
            ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getAll(Request $req) {
        try {
            $tasks = Task::whereHas('users', function(Builder $q) use ($req) {
                $q->where('user_id', $req->user()->id);
            })->orderBy('id', 'desc');

            // Filters
            if ($req->has('category')) { // By category
                if ($req->category == 'today') {
                    $tasks->where(function($q) {
                        $q->where('start_date', now()->format('Y-m-d'));
                    });
                }
            }
            if ($req->has('keyword') && $req->keyword != null && $req->keyword != '') { // By keyword
                $tasks->where(function($q) use ($req) {
                    $q->where('sku', 'like', '%' . $req->keyword . '%')
                        ->orWhere('name', 'like', '%' . $req->keyword . '%')
                        ->orWhere('desc', 'like', '%' . $req->keyword . '%')
                        ->orWhere('remark', 'like', '%' . $req->keyword . '%')
                        ->orWhereHas('customer', function($qq) use ($req) {
                            $qq->where('name', 'like', '%' . $req->keyword . '%')
                                ->orWhere('phone', 'like', '%' . $req->keyword . '%')
                                ->orWhere('company_name', 'like', '%' . $req->keyword . '%')
                                ->orWhere('company_address', 'like', '%' . $req->keyword . '%')
                                ->orWhere('company_registration_number', 'like', '%' . $req->keyword . '%');
                        });
                });
            }
            if ($req->has('date')) { // By date
                $tasks->where(function($q) use ($req) {
                    $q->where('start_date', Carbon::parse($req->date)->format('Y-m-d'));
                });
            }

            $tasks = $tasks->simplePaginate();

            $tasks->each(function($q) {
                $q->load('milestones');

                $q->milestones->each(function($qq) use ($q) {
                    $task_ms = TaskMilestone::where('task_id', $q->id)->where('milestone_id', $qq->id)->first();
                    $qq->id = $task_ms->id;
                    $qq->pivot->attachments = Attachment::where('object_type', TaskMilestone::class)
                        ->where('object_id', $task_ms->id)
                        ->get();
                });
            });

            return Response::json([
                'tasks' => $tasks,
            ], HttpFoundationResponse::HTTP_OK);
        } catch (\Throwable $th) {
            report($th);

            return Response::json([
                'msg' => 'something went wrong'
            ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getDetail(Task $task) {
        try {
            $task->load('customer', 'attachments', 'milestones');

            $task->milestones->each(function($q) use ($task) {
                $task_ms = TaskMilestone::where('task_id', $task->id)->where('milestone_id', $q->id)->first();
                $q->id = $task_ms->id;
                $q->pivot->attachments = Attachment::where('object_type', TaskMilestone::class)
                    ->where('object_id', $task_ms->id)
                    ->get();
            });

            return Response::json([
                'task' => $task,
            ], HttpFoundationResponse::HTTP_OK);
        } catch (\Throwable $th) {
            report($th);

            return Response::json([
                'msg' => 'something went wrong'
            ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function updateMilestone(Request $req, TaskMilestone $task_ms) {
        // Validate form
        $rules = [
            'address' => 'required',
            'datetime' => 'required',
            'amount_collected' => 'nullable|numeric',
            'remark' => 'nullable',
            'attachment' => 'nullable',
            'attachment.*' => 'file|mimes:jpg,png,jpeg',
        ];
        $validator = Validator::make($req->all(), $rules, [], [
            'attachment.*' => 'attachment',
        ]);
        if ($validator->fails()) {
            return Response::json($validator->errors(), HttpFoundationResponse::HTTP_BAD_REQUEST);
        }

        try {
            DB::beginTransaction();

            $task_ms->update([
                'address' => $req->address,
                'datetime' => Carbon::parse($req->datetime)->format('Y-m-d H:i:s'),
                'amount_collected' => $req->amount_collected,
                'remark' => $req->remark,
                'submitted_at' => now(),
            ]);

            if ($req->hasFile('attachment')) {
                Attachment::where([
                    'object_type' => TaskMilestone::class,
                    'object_id' => $task_ms->id,
                ])->delete();

                foreach ($req->file('attachment') as $key => $file) {
                    $path = Storage::putFile(Attachment::TASK_MILESTONE_PATH, $file);
                    Attachment::create([
                        'object_type' => TaskMilestone::class,
                        'object_id' => $task_ms->id,
                        'src' => basename($path),
                    ]);
                }
            }

            $ms = Milestone::where('id', $task_ms->milestone_id)->first();
            $ms->id = $task_ms->id;
            $task_ms->attachments = Attachment::where('object_type', TaskMilestone::class)
                ->where('object_id', $task_ms->id)
                ->get();

            $this->createLog($task_ms, $ms->name . ' submitted');

            Notification::send(User::whereIn('id', UserTask::where('task_id', $task_ms->task_id)->pluck('user_id'))->get(), new SystemNotification([
                'type' => 'milestone_completed',
                'done_by' => $req->user()->id,
                'task_id' => $task_ms->task_id,
                'ms_id' =>  Milestone::where('id', $task_ms->milestone_id)->value('id'),
            ]));

            $not_completed = TaskMilestone::where('task_id', $task_ms->task_id)->whereNull('submitted_at')->exists();
            if (!$not_completed) { // If all milestones completed
                Notification::send(User::whereIn('id', UserTask::where('task_id', $task_ms->task_id)->pluck('user_id'))->get(), new SystemNotification([
                    'type' => 'task_completed',
                    'done_by' => $req->user()->id,
                    'task_id' => $task_ms->task_id,
                ]));
            }

            DB::commit();

            return Response::json([
                'milestone' => $ms,
                'task_milestone' => $task_ms
            ], HttpFoundationResponse::HTTP_OK);
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return Response::json([
                'msg' => 'something went wrong'
            ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function createLog(TaskMilestone $task_ms, string $desc) {
        $task = Task::where('id', $task_ms->task_id)->first();
        $task->load('users', 'milestones', 'attachments');

        $task->formatted_created_at = Carbon::parse($task->created_at)->format('d M Y');
        $task->start_date = Carbon::parse($task->start_date)->format('d M Y');
        $task->due_date = Carbon::parse($task->due_date)->format('d M Y');
        $task->status = (new Task)->statusToHumanRead($task->status);
        $task->progress = (new Task)->getProgress($task);
        $task->updated_milestone = $task_ms;

        (new ActivityLog)->store(Task::class, $task->id, $desc, $task);
    }
}
