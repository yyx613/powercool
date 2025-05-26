<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Attachment;
use App\Models\Milestone;
use App\Models\Product;
use App\Models\ProductChild;
use App\Models\Role;
use App\Models\Sale;
use App\Models\Target;
use App\Models\Task;
use App\Models\TaskMilestone;
use App\Models\TaskMilestoneInventory;
use App\Models\User;
use App\Models\UserTask;
use App\Notifications\MobileAppNotification;
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
    public function getStatistic(Request $req)
    {
        $user = $req->user();

        try {
            $today_completed_task_count = 0;
            $today_task_count = 0;
            $cash_collected = 0;
            $outstanding = 0;
            $monthly_sales = 0;
            $monthly_sales_target = 0;

            $tasks = Task::where('start_date', now()->format('Y-m-d'))->whereHas('users', function (Builder $q) use ($req) {
                $q->where('user_id', $req->user()->id);
            })->get();

            $today_task_count = count($tasks);

            for ($i = 0; $i < count($tasks); $i++) {
                $not_completed = TaskMilestone::where('task_id', $tasks[$i]->id)->whereNull('submitted_at')->exists();

                if (!$not_completed) {
                    $today_completed_task_count++;
                }

                if (count(array_intersect(getUserRoleId($user), [Role::DRIVER, Role::TECHNICIAN])) > 0) {
                    // Get cash collected
                    foreach ($tasks[$i]->milestones as $task_ms) {
                        if (in_array($task_ms->pivot->milestone_id, getPaymentCollectionIds())) {
                            $cash_collected += $task_ms->pivot->amount_collected;
                        }
                    }
                    $outstanding += $tasks[$i]->amount_to_collect;
                }
            }

            if (in_array(Role::SALE, getUserRoleId($user))) {
                $monthly_sales_target = Target::where('sale_id', $user->id)->where('date', now()->format('Y-m-01'))->value('amount');
                $monthly_sales = Sale::where('type', Sale::TYPE_SO)->where('sale_id', $user->id)->where('created_at', 'like', '%' . now()->format('Y-m') . '%')->sum('payment_amount');
            }

            return Response::json([
                'cash_collected' => $cash_collected,
                'outstanding' => $outstanding,
                'today_task_count' => $today_task_count,
                'today_completed_task_count' => $today_completed_task_count,
                'monthly_sales' => $monthly_sales ?? 0,
                'monthly_sales_target' => $monthly_sales_target ?? 0,
            ], HttpFoundationResponse::HTTP_OK);
        } catch (\Throwable $th) {
            report($th);

            return Response::json([
                'msg' => 'something went wrong'
            ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getAll(Request $req)
    {
        try {
            $tasks = Task::whereHas('users', function (Builder $q) use ($req) {
                $q->where('user_id', $req->user()->id);
            })->orderBy('id', 'desc');

            // Filters
            if ($req->has('category') && $req->category == 'today') { // By category
                $tasks->where(function ($q) {
                    $q->where('start_date', now()->format('Y-m-d'));
                });
            }
            if ($req->has('keyword') && $req->keyword != null && $req->keyword != '') { // By keyword
                $tasks->where(function ($q) use ($req) {
                    $q->where('sku', 'like', '%' . $req->keyword . '%')
                        ->orWhere('name', 'like', '%' . $req->keyword . '%')
                        ->orWhere('desc', 'like', '%' . $req->keyword . '%')
                        ->orWhere('remark', 'like', '%' . $req->keyword . '%')
                        ->orWhereHas('customer', function ($qq) use ($req) {
                            $qq->where('name', 'like', '%' . $req->keyword . '%')
                                ->orWhere('phone', 'like', '%' . $req->keyword . '%')
                                ->orWhere('company_name', 'like', '%' . $req->keyword . '%')
                                ->orWhere('company_registration_number', 'like', '%' . $req->keyword . '%');
                        });
                });
            }
            if ($req->has('date')) { // By date
                $tasks->where(function ($q) use ($req) {
                    $q->where('start_date', Carbon::parse($req->date)->format('Y-m-d'));
                });
            }

            $tasks = $tasks->simplePaginate();
            Log::info($tasks);

            $tasks->each(function ($q) {
                $q->load('milestones');

                $q->milestones->each(function ($qq) use ($q) {
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

    public function getDetail(Task $task)
    {
        try {
            $task->load('customer', 'attachments', 'milestones');

            $task->milestones->each(function ($q) use ($task) {
                $task_ms = TaskMilestone::where('task_id', $task->id)->where('milestone_id', $q->id)->first();
                $q->id = $task_ms->id;
                $q->pivot->attachments = Attachment::where('object_type', TaskMilestone::class)
                    ->where('object_id', $task_ms->id)
                    ->get();

                if ($task_ms->inventories != null && count($task_ms->inventories) > 0) {
                    $q->inventories = $this->formatTaskMilestoneInventory($task_ms);
                }
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

    public function updateMilestone(Request $req, TaskMilestone $task_ms)
    {
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
            Task::where('id', $task_ms->task_id)->where('status', Task::STATUS_TO_DO)->update([
                'status' => Task::STATUS_DOING
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

            Notification::send(User::whereIn('id', UserTask::where('task_id', $task_ms->task_id)->pluck('user_id'))->get(), new MobileAppNotification([
                'type' => 'milestone_completed',
                'done_by' => $req->user()->id,
                'task_id' => $task_ms->task_id,
                'ms_id' =>  Milestone::where('id', $task_ms->milestone_id)->value('id'),
            ]));

            $not_completed = TaskMilestone::where('task_id', $task_ms->task_id)->whereNull('submitted_at')->exists();
            if (!$not_completed) { // If all milestones completed
                Notification::send(User::whereIn('id', UserTask::where('task_id', $task_ms->task_id)->pluck('user_id'))->get(), new MobileAppNotification([
                    'type' => 'task_completed',
                    'done_by' => $req->user()->id,
                    'task_id' => $task_ms->task_id,
                ]));
                Task::where('id', $task_ms->task_id)->where('status', Task::STATUS_DOING)->update([
                    'status' => Task::STATUS_COMPLETED
                ]);
            }

            // Inventory
            if ($req->inventory != null) {
                foreach ($req->inventory as $prodId => $value) {
                    $value = json_decode($value);

                    if (is_array($value) && count($value) > 0) {
                        $child_ids = $value;

                        for ($i = 0; $i < count($child_ids); $i++) {
                            TaskMilestoneInventory::create([
                                'task_milestone_id' => $task_ms->id,
                                'inventory_type' => ProductChild::class,
                                'inventory_id' => $child_ids[$i],
                                'qty' => 1,
                            ]);
                        }
                    } else if ($value > 0) {
                        TaskMilestoneInventory::create([
                            'task_milestone_id' => $task_ms->id,
                            'inventory_type' => Product::class,
                            'inventory_id' => $prodId,
                            'qty' => $value,
                        ]);
                    }
                }
            }

            $data = [
                'milestone' => $ms,
                'task_milestone' => $task_ms,
            ];
            if ($task_ms->inventories != null && count($task_ms->inventories) > 0) {
                $data['inventories'] = $this->formatTaskMilestoneInventory($task_ms);
            }

            DB::commit();

            return Response::json($data, HttpFoundationResponse::HTTP_OK);
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return Response::json([
                'msg' => 'something went wrong'
            ], HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function createLog(TaskMilestone $task_ms, string $desc)
    {
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

    private function formatTaskMilestoneInventory(TaskMilestone $task_ms): array
    {
        $inventories = $task_ms->inventories;
        $newInventories = [];

        for ($i = 0; $i < count($inventories); $i++) {
            if (get_class($inventories[$i]->inventory) == ProductChild::class) {
                $newInventories[] = [
                    'product_id' => $inventories[$i]->inventory->parent->id,
                    'product_child_id' => $inventories[$i]->inventory_id,
                    'qty' => $inventories[$i]->qty,
                ];
            } else {
                $newInventories[] = [
                    'product_id' => $inventories[$i]->inventory_id,
                    'product_child_id' => null,
                    'qty' => $inventories[$i]->qty,
                ];
            }
        }

        return $newInventories;
    }
}
