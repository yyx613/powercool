<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Attachment;
use App\Models\Milestone;
use App\Models\Role;
use App\Models\Task;
use App\Models\TaskMilestone;
use App\Models\Ticket;
use App\Models\User;
use App\Models\UserTask;
use App\Notifications\SystemNotification;
use Carbon\Carbon;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    const DRIVER_SALE_FORM_RULES = [
        'ticket' => 'nullable',
        'customer' => 'required',
        'name' => 'required|max:250',
        'desc' => 'required|max:250',
        'start_date' => 'required',
        'due_date' => 'required',
        'remark' => 'nullable|max:250',
        'status' => 'required',
        'assign' => 'required',
        'assign.*' => 'exists:users,id',
        'milestone' => 'required_without:custom_milestone',
        'custom_milestone' => 'required_without:milestone',
        'amount_to_collect' => 'nullable',
        'attachment' => 'nullable',
        'attachment.*' => 'file'
    ];
    const TECHNICIAN_FORM_RULES = [
        'ticket' => 'nullable',
        'task' => 'required',
        'customer' => 'required',
        'name' => 'required|max:250',
        'desc' => 'required|max:250',
        'start_date' => 'required',
        'due_date' => 'required',
        'remark' => 'nullable|max:250',
        'status' => 'required',
        'assign' => 'required',
        'assign.*' => 'exists:users,id',
        'milestone' => 'required_without:custom_milestone',
        'custom_milestone' => 'required_without:milestone',
        'amount_to_collect' => 'nullable',
        'attachment' => 'nullable',
        'attachment.*' => 'file'
    ];

    public function index() {
        $task_model = new Task;

        return view('task.list', [
            'due_today' => $task_model->where('due_date', now()->format('Y-m-d'))->count(),
            'to_do' => $task_model->where('status', Task::STATUS_TO_DO)->count(), 
            'doing' => $task_model->where('status', Task::STATUS_DOING)->count(),
            'in_review' => $task_model->where('status', Task::STATUS_IN_REVIEW)->count(),
            'completed' => $task_model->where('status', Task::STATUS_COMPLETED)->count(),
        ]);
    }

    public function getData(Request $req) {
        $role = null;

        switch ($req->role) {
            case 'driver':
                $role = Task::TYPE_DRIVER;
                break;
            case 'technician':
                $role = Task::TYPE_TECHNICIAN;
                break;
            case 'sale':
                $role = Task::TYPE_SALE;
                break;
        }

        if ($role == null) {
            return response()->json([]);
        }

        $records = Task::where('type', $role)->orderBy('id', 'desc');

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
                'id' => $record->id,
                'name' => $record->name,
                'due_date' => $record->due_date,
                'status' => $record->status,
            ];
        }

        return response()->json($data);
    }

    public function create(Request $req) {
        $data = [];

        if ($req->has('tic_id')) {
            $ticket = Ticket::findOrFail($req->tic_id);

            $data['from_ticket'] = $ticket;
        }

        return view('task.form', $data);
    }

    public function driverStore(Request $req) {
        // Validate request
        $validator = Validator::make($req->all(), self::DRIVER_SALE_FORM_RULES);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $task = Task::create([
                'sku' => (new Task)->generateSku(),
                'type' => Task::TYPE_DRIVER,
                'ticket_id' => $req->ticket,
                'customer_id' => $req->customer,
                'name' => $req->name,
                'desc' => $req->desc,
                'start_date' => $req->start_date,
                'due_date' => $req->due_date,
                'remark' => $req->remark,
                'status' => $req->status,
                'amount_to_collect' => $req->amount_to_collect ?? 0,
            ]);

            if ($req->ticket != null) {
                Ticket::where('id', $req->ticket)->delete();
            }

            foreach ($req->assign as $assign_id) {
                UserTask::create([
                    'user_id' => $assign_id,
                    'task_id' => $task->id
                ]);
            }

            foreach ($req->milestone as $ms_id) {
                TaskMilestone::create([
                    'task_id' => $task->id,
                    'milestone_id' => $ms_id,
                ]);
            }
            // Create custom milestones
            if ($req->custom_milestone != null) {
                foreach ($req->custom_milestone as $ms) {
                    $custom_ms = Milestone::create([
                        'type' => Milestone::TYPE_DRIVER_TASK,
                        'name' => $ms,
                        'is_custom' => true,
                    ]);
                    TaskMilestone::create([
                        'task_id' => $task->id,
                        'milestone_id' => $custom_ms->id,
                    ]);
                }
            }

            if ($req->hasFile('attachment')) {
                foreach ($req->file('attachment') as $key => $file) {
                    $path = Storage::putFile(Attachment::TASK_PATH, $file);
                    Attachment::create([
                        'object_type' => Task::class,
                        'object_id' => $task->id,
                        'src' => basename($path),
                    ]);
                }
            } else if ($req->ticket != null) {
                $atts = Attachment::where([
                    'object_type' => Ticket::class,
                    'object_id' => $req->ticket,
                ])->get();

                for ($i=0; $i < count($atts); $i++) { 
                    $extension = explode('.', $atts[$i]->src)[1];

                    while (true) {
                        $filename = generateRandomAlphabet(40) . '.' . $extension;
    
                        $exists = Storage::exists(Attachment::TASK_PATH . '/' . $filename);
                        if (!$exists) {
                            break;
                        }
                    }
                    Attachment::create([
                        'object_type' => Task::class,
                        'object_id' => $task->id,
                        'src' => $filename,
                    ]);
                    Storage::copy(Attachment::TICKET_PATH . '/' . $atts[$i]->src, Attachment::TASK_PATH . '/' . $filename);
                }
            }

            $this->createLog($task, 'Task created');

            Notification::send(User::whereIn('id', $req->assign)->get(), new SystemNotification([
                'type' => 'task_created',
                'assigned_by' => Auth::user()->id,
                'task_id' => $task->id
            ]));

            DB::commit();

            return redirect(route('task.driver.index'))->with('success', 'Task created');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator')->withInput();
        }
    }

    public function technicianStore(Request $req) {
        // Validate request
        $validator = Validator::make($req->all(), self::TECHNICIAN_FORM_RULES);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $task = Task::create([
                'sku' => (new Task)->generateSku(),
                'type' => Task::TYPE_TECHNICIAN,
                'ticket_id' => $req->ticket,
                'task_type' => $req->task,
                'customer_id' => $req->customer,
                'name' => $req->name,
                'desc' => $req->desc,
                'start_date' => $req->start_date,
                'due_date' => $req->due_date,
                'remark' => $req->remark,
                'status' => $req->status,
                'amount_to_collect' => $req->amount_to_collect ?? 0,
            ]);

            if ($req->ticket != null) {
                Ticket::where('id', $req->ticket)->delete();
            }

            foreach ($req->assign as $assign_id) {
                UserTask::create([
                    'user_id' => $assign_id,
                    'task_id' => $task->id
                ]);
            }

            foreach ($req->milestone as $ms_id) {
                TaskMilestone::create([
                    'task_id' => $task->id,
                    'milestone_id' => $ms_id,
                ]);
            }
            // Create custom milestones
            if ($req->custom_milestone != null) {
                foreach ($req->custom_milestone as $ms) {
                    $custom_ms = Milestone::create([
                        'type' => $req->task,
                        'name' => $ms,
                        'is_custom' => true,
                    ]);
                    TaskMilestone::create([
                        'task_id' => $task->id,
                        'milestone_id' => $custom_ms->id,
                    ]);
                }
            }

            if ($req->hasFile('attachment')) {
                foreach ($req->file('attachment') as $key => $file) {
                    $path = Storage::putFile(Attachment::TASK_PATH, $file);
                    Attachment::create([
                        'object_type' => Task::class,
                        'object_id' => $task->id,
                        'src' => basename($path),
                    ]);
                }
            } else if ($req->ticket != null) {
                $atts = Attachment::where([
                    'object_type' => Ticket::class,
                    'object_id' => $req->ticket,
                ])->get();

                for ($i=0; $i < count($atts); $i++) { 
                    $extension = explode('.', $atts[$i]->src)[1];

                    while (true) {
                        $filename = generateRandomAlphabet(40) . '.' . $extension;
    
                        $exists = Storage::exists(Attachment::TASK_PATH . '/' . $filename);
                        if (!$exists) {
                            break;
                        }
                    }
                    Attachment::create([
                        'object_type' => Task::class,
                        'object_id' => $task->id,
                        'src' => $filename,
                    ]);
                    Storage::copy(Attachment::TICKET_PATH . '/' . $atts[$i]->src, Attachment::TASK_PATH . '/' . $filename);
                }
            }

            $this->createLog($task, 'Task created');

            Notification::send(User::whereIn('id', $req->assign)->get(), new SystemNotification([
                'type' => 'task_created',
                'assigned_by' => Auth::user()->id,
                'task_id' => $task->id
            ]));

            DB::commit();

            return redirect(route('task.technician.index'))->with('success', 'Task created');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator')->withInput();
        }
    }

    public function saleStore(Request $req) {
        // Validate request
        $validator = Validator::make($req->all(), self::DRIVER_SALE_FORM_RULES);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $task = Task::create([
                'sku' => (new Task)->generateSku(),
                'type' => Task::TYPE_SALE,
                'ticket_id' => $req->ticket,
                'customer_id' => $req->customer,
                'name' => $req->name,
                'desc' => $req->desc,
                'start_date' => $req->start_date,
                'due_date' => $req->due_date,
                'remark' => $req->remark,
                'status' => $req->status,
                'amount_to_collect' => $req->amount_to_collect ?? 0,
            ]);

            if ($req->ticket != null) {
                Ticket::where('id', $req->ticket)->delete();
            }

            foreach ($req->assign as $assign_id) {
                UserTask::create([
                    'user_id' => $assign_id,
                    'task_id' => $task->id
                ]);
            }

            foreach ($req->milestone as $ms_id) {
                TaskMilestone::create([
                    'task_id' => $task->id,
                    'milestone_id' => $ms_id,
                ]);
            }
            // Create custom milestones
            if ($req->custom_milestone != null) {
                foreach ($req->custom_milestone as $ms) {
                    $custom_ms = Milestone::create([
                        'type' => Milestone::TYPE_SITE_VISIT,
                        'name' => $ms,
                        'is_custom' => true,
                    ]);
                    TaskMilestone::create([
                        'task_id' => $task->id,
                        'milestone_id' => $custom_ms->id,
                    ]);
                }
            }

            if ($req->hasFile('attachment')) {
                foreach ($req->file('attachment') as $key => $file) {
                    $path = Storage::putFile(Attachment::TASK_PATH, $file);
                    Attachment::create([
                        'object_type' => Task::class,
                        'object_id' => $task->id,
                        'src' => basename($path),
                    ]);
                }
            } else if ($req->ticket != null) {
                $atts = Attachment::where([
                    'object_type' => Ticket::class,
                    'object_id' => $req->ticket,
                ])->get();

                for ($i=0; $i < count($atts); $i++) { 
                    $extension = explode('.', $atts[$i]->src)[1];

                    while (true) {
                        $filename = generateRandomAlphabet(40) . '.' . $extension;
    
                        $exists = Storage::exists(Attachment::TASK_PATH . '/' . $filename);
                        if (!$exists) {
                            break;
                        }
                    }
                    Attachment::create([
                        'object_type' => Task::class,
                        'object_id' => $task->id,
                        'src' => $filename,
                    ]);
                    Storage::copy(Attachment::TICKET_PATH . '/' . $atts[$i]->src, Attachment::TASK_PATH . '/' . $filename);
                }
            }

            $this->createLog($task, 'Task created');

            Notification::send(User::whereIn('id', $req->assign)->get(), new SystemNotification([
                'type' => 'task_created',
                'assigned_by' => Auth::user()->id,
                'task_id' => $task->id
            ]));

            DB::commit();

            return redirect(route('task.sale.index'))->with('success', 'Task created');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator')->withInput();
        }
    }

    public function view(Task $task) {
        $task->load('users', 'milestones', 'attachments', 'logs.doneBy');

        $task->formatted_created_at = Carbon::parse($task->created_at)->format('d M Y');
        $task->start_date = Carbon::parse($task->start_date)->format('d M Y');
        $task->due_date = Carbon::parse($task->due_date)->format('d M Y');
        $task->status = (new Task)->statusToHumanRead($task->status);
        $task->progress = (new Task)->getProgress($task);

        return view('task.view', [
            'task' => $task,
            'is_view' => true,
        ]);
    }

    public function edit(Task $task) {
        $task->load('users', 'milestones', 'attachments');

        return view('task.form', [
            'task' => $task
        ]);
    }

    public function driverUpdate(Request $req, Task $task) {
        // Validate request
        $validator = Validator::make($req->all(), self::DRIVER_SALE_FORM_RULES);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $task->update([
                'ticket_id' => $req->ticket,
                'customer_id' => $req->customer,
                'name' => $req->name,
                'desc' => $req->desc,
                'start_date' => $req->start_date,
                'due_date' => $req->due_date,
                'remark' => $req->remark,
                'status' => $req->status,
                'amount_to_collect' => $req->amount_to_collect,
            ]);

            UserTask::where('task_id', $task->id)->delete();
            foreach ($req->assign as $assign_id) {
                UserTask::create([
                    'user_id' => $assign_id,
                    'task_id' => $task->id
                ]);
            }

            TaskMilestone::where('task_id', $task->id)->whereNotIn('milestone_id', $req->milestone)->delete();
            foreach ($req->milestone as $ms_id) {
                $ms = TaskMilestone::where('task_id', $task->id)->where('milestone_id', $ms_id)->first();
                if ($ms == null) {
                    TaskMilestone::create([
                        'task_id' => $task->id,
                        'milestone_id' => $ms_id,
                    ]);
                }
            }
            // Create custom milestones
            if ($req->custom_milestone != null) {
                foreach ($req->custom_milestone as $ms) {
                    $custom_ms = Milestone::create([
                        'type' => Milestone::TYPE_DRIVER_TASK,
                        'name' => $ms,
                        'is_custom' => true,
                    ]);
                    TaskMilestone::create([
                        'task_id' => $task->id,
                        'milestone_id' => $custom_ms->id,
                    ]);
                }
            } 

            if ($req->hasFile('attachment')) {
                Attachment::where([
                    ['object_type', Task::class],
                    ['object_id', $task->id]
                ])->delete();

                foreach ($req->file('attachment') as $key => $file) {
                    $path = Storage::putFile(Attachment::TASK_PATH, $file);
                    Attachment::create([
                        'object_type' => Task::class,
                        'object_id' => $task->id,
                        'src' => basename($path),
                    ]);
                }
            }

            $this->createLog($task, 'Task updated');

            DB::commit();

            return redirect(route('task.driver.index'))->with('success', 'Task updated');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator')->withInput();
        }
    }

    public function technicianUpdate(Request $req, Task $task) {
        // Validate request
        $validator = Validator::make($req->all(), self::TECHNICIAN_FORM_RULES);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $task->update([
                'ticket_id' => $req->ticket,
                'task_type' => $req->task,
                'customer_id' => $req->customer,
                'name' => $req->name,
                'desc' => $req->desc,
                'start_date' => $req->start_date,
                'due_date' => $req->due_date,
                'remark' => $req->remark,
                'status' => $req->status,
                'amount_to_collect' => $req->amount_to_collect,
            ]);

            UserTask::where('task_id', $task->id)->delete();
            foreach ($req->assign as $assign_id) {
                UserTask::create([
                    'user_id' => $assign_id,
                    'task_id' => $task->id
                ]);
            }

            TaskMilestone::where('task_id', $task->id)->whereNotIn('milestone_id', $req->milestone)->delete();
            foreach ($req->milestone as $ms_id) {
                $ms = TaskMilestone::where('task_id', $task->id)->where('milestone_id', $ms_id)->first();
                if ($ms == null) {
                    TaskMilestone::create([
                        'task_id' => $task->id,
                        'milestone_id' => $ms_id,
                    ]);
                }
            }
            // Create custom milestones
            if ($req->custom_milestone != null) {
                foreach ($req->custom_milestone as $ms) {
                    $custom_ms = Milestone::create([
                        'type' => $req->task,
                        'name' => $ms,
                        'is_custom' => true,
                    ]);
                    TaskMilestone::create([
                        'task_id' => $task->id,
                        'milestone_id' => $custom_ms->id,
                    ]);
                }
            }

            if ($req->hasFile('attachment')) {
                foreach ($req->file('attachment') as $key => $file) {
                    $path = Storage::putFile(Attachment::TASK_PATH, $file);
                    Attachment::create([
                        'object_type' => Task::class,
                        'object_id' => $task->id,
                        'src' => basename($path),
                    ]);
                }
            }

            $this->createLog($task, 'Task updated');

            DB::commit();

            return redirect(route('task.technician.index'))->with('success', 'Task created');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator')->withInput();
        }
    }

    public function saleUpdate(Request $req, Task $task) {
        // Validate request
        $validator = Validator::make($req->all(), self::DRIVER_SALE_FORM_RULES);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $task->update([
                'ticket_id' => $req->ticket,
                'customer_id' => $req->customer,
                'name' => $req->name,
                'desc' => $req->desc,
                'start_date' => $req->start_date,
                'due_date' => $req->due_date,
                'remark' => $req->remark,
                'status' => $req->status,
                'amount_to_collect' => $req->amount_to_collect,
            ]);

            UserTask::where('task_id', $task->id)->delete();
            foreach ($req->assign as $assign_id) {
                UserTask::create([
                    'user_id' => $assign_id,
                    'task_id' => $task->id
                ]);
            }

            TaskMilestone::where('task_id', $task->id)->whereNotIn('milestone_id', $req->milestone)->delete();
            foreach ($req->milestone as $ms_id) {
                $ms = TaskMilestone::where('task_id', $task->id)->where('milestone_id', $ms_id)->first();
                if ($ms == null) {
                    TaskMilestone::create([
                        'task_id' => $task->id,
                        'milestone_id' => $ms_id,
                    ]);
                }
            }
            // Create custom milestones
            if ($req->custom_milestone != null) {
                foreach ($req->custom_milestone as $ms) {
                    $custom_ms = Milestone::create([
                        'type' => Milestone::TYPE_SITE_VISIT,
                        'name' => $ms,
                        'is_custom' => true,
                    ]);
                    TaskMilestone::create([
                        'task_id' => $task->id,
                        'milestone_id' => $custom_ms->id,
                    ]);
                }
            }

            if ($req->hasFile('attachment')) {
                Attachment::where([
                    ['object_type', Task::class],
                    ['object_id', $task->id]
                ])->delete();

                foreach ($req->file('attachment') as $key => $file) {
                    $path = Storage::putFile(Attachment::TASK_PATH, $file);
                    Attachment::create([
                        'object_type' => Task::class,
                        'object_id' => $task->id,
                        'src' => basename($path),
                    ]);
                }
            }

            $this->createLog($task, 'Task updated');

            DB::commit();

            return redirect(route('task.sale.index'))->with('success', 'Task updated');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator')->withInput();
        }
    }

    public function delete(Task $task) {
        try {
            DB::beginTransaction();

            $task->delete();
    
            $this->createLog($task, 'Task deleted');
    
            DB::commit();

            return back()->with('success', 'Task deleted');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator');
        }
    }

    private function createLog(Task $task, string $desc) {
        $task->load('users', 'milestones', 'attachments');

        $task->formatted_created_at = Carbon::parse($task->created_at)->format('d M Y');
        $task->start_date = Carbon::parse($task->start_date)->format('d M Y');
        $task->due_date = Carbon::parse($task->due_date)->format('d M Y');
        $task->status = (new Task)->statusToHumanRead($task->status);
        $task->progress = (new Task)->getProgress($task);

        (new ActivityLog)->store(Task::class, $task->id, $desc, $task);
    }
}
