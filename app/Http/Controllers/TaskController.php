<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Attachment;
use App\Models\Branch;
use App\Models\Invoice;
use App\Models\Milestone;
use App\Models\Sale;
use App\Models\Service;
use App\Models\Task;
use App\Models\TaskMilestone;
use App\Models\TaskService;
use App\Models\Ticket;
use App\Models\User;
use App\Models\UserTask;
use App\Notifications\MobileAppNotification;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    const DRIVER_SALE_FORM_RULES = [
        'ticket' => 'nullable',
        'sale_order_id' => 'nullable',
        'customer' => 'required',
        'name' => 'required|max:250',
        'desc' => 'required|max:250',
        'start_date' => 'required',
        'due_date' => 'required',
        'estimated_time' => 'required',
        'customer' => 'required',
        'remark' => 'nullable|max:250',
        'status' => 'required',
        'assign' => 'required',
        'assign.*' => 'exists:users,id',
        'milestone' => 'required_without:custom_milestone',
        'custom_milestone' => 'required_without:milestone',
        'amount_to_collect' => 'nullable',
        'attachment' => 'nullable',
        'attachment.*' => 'file',
    ];

    const TECHNICIAN_FORM_RULES = [
        'ticket' => 'nullable',
        'sale_order_id' => 'nullable',
        'product_id' => 'required_with:sale_order_id',
        'product_child_id' => 'required_with:sale_order_id',
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
        'attachment.*' => 'file',
        'services' => 'nullable',
    ];

    public function index()
    {
        if (str_contains(Route::currentRouteName(), '.technician.')) {
            $for_role = 'technician';
            $role_id = Task::TYPE_TECHNICIAN;
        } elseif (str_contains(Route::currentRouteName(), '.sale.')) {
            $for_role = 'sale';
            $role_id = Task::TYPE_SALE;
        } elseif (str_contains(Route::currentRouteName(), '.driver.')) {
            $for_role = 'driver';
            $role_id = Task::TYPE_DRIVER;
        }

        $data = [
            'for_role' => $for_role,
            'due_today' => Task::where('type', $role_id)->where('due_date', now()->format('Y-m-d'))->count(),
            'to_do' => Task::where('type', $role_id)->where('status', Task::STATUS_TO_DO)->count(),
            'doing' => Task::where('type', $role_id)->where('status', Task::STATUS_DOING)->count(),
            'in_review' => Task::where('type', $role_id)->where('status', Task::STATUS_IN_REVIEW)->count(),
            'completed' => Task::where('type', $role_id)->where('status', Task::STATUS_COMPLETED)->count(),
        ];

        return view('task.list', $data);
    }

    public function getData(Request $req)
    {
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

        $records = Task::where('type', $role);

        // Search
        if ($req->has('search') && $req->search['value'] != null) {
            $keyword = $req->search['value'];

            $records->where(function ($q) use ($keyword) {
                $q->where('sku', 'like', '%'.$keyword.'%')
                    ->orWhere('name', 'like', '%'.$keyword.'%')
                    ->orWhere('desc', 'like', '%'.$keyword.'%')
                    ->orWhere('remark', 'like', '%'.$keyword.'%')
                    ->orWhere('amount_to_collect', 'like', '%'.$keyword.'%');
            });
        }
        // Order
        if ($req->has('order')) {
            $map = [
                0 => 'sku',
                1 => 'name',
                2 => 'due_date',
                3 => 'amount_to_collect',
            ];
            foreach ($req->order as $order) {
                $records->orderBy($map[$order['column']], $order['dir']);
            }
        } else {
            $records->orderBy('id', 'desc');
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
            $whatsapp_url = null;
            if ($role == Task::TYPE_DRIVER && $record->sale_order_id != null) {
                $driver = $record->users[0];
                if ($driver->phone_number != null) {
                    $whatsapp_url = 'https://wa.me/'.$record->customer->phone.'?text='.getWhatsAppContent($driver->name, $driver->phone_number, $driver->car_plate, $record->estimated_time, Carbon::parse($record->start_date)->format('d/m/y'));
                }
            }

            $data['data'][] = [
                'id' => $record->id,
                'sku' => $record->sku,
                'name' => $record->name,
                'due_date' => $record->due_date,
                'amount_to_collect' => $record->amount_to_collect == 0 ? null : number_format($record->amount_to_collect, 2),
                'status' => $record->status,
                'can_edit' => hasPermission('task.edit'),
                'can_delete' => hasPermission('task.delete'),
                'whatsapp_url' => $whatsapp_url,
            ];
        }

        return response()->json($data);
    }

    public function create(Request $req)
    {
        $data = [];

        if ($req->has('tic_id')) {
            $ticket = Ticket::findOrFail($req->tic_id);

            $data['from_ticket'] = $ticket;
            $data['so_inv'] = $ticket->so_inv == null ? null : explode(',', $ticket->so_inv);
            $data['so_inv_type'] = $ticket->so_inv_type == null ? null : explode(',', $ticket->so_inv_type);
            $data['product'] = $ticket->product_id == null ? null : explode(',', $ticket->product_id);
            $data['product_child'] = $ticket->product_child_id == null ? null : explode(',', $ticket->product_child_id);

            if ($data['so_inv_type'] != null) {
                $so_inv_labels = [];
                for ($i = 0; $i < count($data['so_inv_type']); $i++) {
                    if ($data['so_inv_type'][$i] == 'so') {
                        $so_inv_labels[] = Sale::where('id', $data['so_inv'][$i])->first();
                    } elseif ($data['so_inv_type'][$i] == 'inv') {
                        $so_inv_labels[] = Invoice::where('id', $data['so_inv'][$i])->first();
                    }
                }

                $data['so_inv_labels'] = $so_inv_labels;
                $data['so_inv_idx'] = $req->so_inv_idx ?? 0;
            }
        }
        // dd($data);

        return view('task.form', $data);
    }

    public function driverStore(Request $req)
    {
        if ($req->amount_to_collect == null) {
            $req->merge(['amount_to_collect' => 0]);
        }
        // Validate request
        $validator = Validator::make($req->all(), self::DRIVER_SALE_FORM_RULES, [], [
            'sale_order_id' => 'sale order',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $task = Task::create([
                'sku' => (new Task)->generateSku(),
                'type' => Task::TYPE_DRIVER,
                'ticket_id' => $req->ticket,
                'sale_order_id' => $req->sale_order_id,
                'customer_id' => $req->customer,
                'name' => $req->name,
                'desc' => $req->desc,
                'start_date' => $req->start_date,
                'due_date' => $req->due_date,
                'estimated_time' => $req->estimated_time,
                'remark' => $req->remark,
                'status' => $req->status,
                'amount_to_collect' => $req->amount_to_collect,
            ]);
            (new Branch)->assign(Task::class, $task->id);

            if ($req->ticket != null) {
                Ticket::where('id', $req->ticket)->delete();
            }

            foreach ($req->assign as $assign_id) {
                UserTask::create([
                    'user_id' => $assign_id,
                    'task_id' => $task->id,
                ]);
            }

            foreach ($req->milestone as $ms_id) {
                TaskMilestone::create([
                    'task_id' => $task->id,
                    'milestone_id' => $ms_id,
                ]);
            }
            // Force to have Payment Collection milestone whenever amount to collect is greater than 0
            if ($req->amount_to_collect > 0 && ! array_intersect($req->milestone, getPaymentCollectionIds())) {
                TaskMilestone::create([
                    'task_id' => $task->id,
                    'milestone_id' => Milestone::where('type', Milestone::TYPE_DRIVER_TASK)->where('name', 'Payment Collection')->value('id'),
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
            } elseif ($req->ticket != null) {
                $atts = Attachment::where([
                    'object_type' => Ticket::class,
                    'object_id' => $req->ticket,
                ])->get();

                for ($i = 0; $i < count($atts); $i++) {
                    $extension = explode('.', $atts[$i]->src)[1];

                    while (true) {
                        $filename = generateRandomAlphabet(40).'.'.$extension;

                        $exists = Storage::exists(Attachment::TASK_PATH.'/'.$filename);
                        if (! $exists) {
                            break;
                        }
                    }
                    Attachment::create([
                        'object_type' => Task::class,
                        'object_id' => $task->id,
                        'src' => $filename,
                    ]);
                    Storage::copy(Attachment::TICKET_PATH.'/'.$atts[$i]->src, Attachment::TASK_PATH.'/'.$filename);
                }
            }

            $this->createLog($task, 'Task created');

            Notification::send(User::whereIn('id', $req->assign)->get(), new MobileAppNotification([
                'type' => 'task_created',
                'assigned_by' => Auth::user()->id,
                'task_id' => $task->id,
            ]));

            DB::commit();

            return redirect(route('task.driver.index'))->with('success', 'Task created');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator')->withInput();
        }
    }

    public function technicianStore(Request $req)
    {
        if ($req->amount_to_collect == null) {
            $req->merge(['amount_to_collect' => 0]);
        }
        // Validate request
        $validator = Validator::make($req->all(), self::TECHNICIAN_FORM_RULES, [], [
            'sale_order_id' => 'sale order',
            'product_id' => 'product',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $task = Task::create([
                'sku' => (new Task)->generateSku(),
                'type' => Task::TYPE_TECHNICIAN,
                'ticket_id' => $req->ticket,
                'sale_order_id' => $req->sale_order_id,
                'product_id' => $req->product_id,
                'product_child_id' => $req->product_child_id,
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
            (new Branch)->assign(Task::class, $task->id);

            // Services
            if ($req->services != null) {
                foreach ($req->services as $service_id) {
                    TaskService::create([
                        'task_id' => $task->id,
                        'service_id' => $service_id,
                        'amount' => Service::where('id', $service_id)->value('amount'),
                    ]);
                }
            }
            // Assign
            foreach ($req->assign as $assign_id) {
                UserTask::create([
                    'user_id' => $assign_id,
                    'task_id' => $task->id,
                ]);
            }
            // Milestones
            foreach ($req->milestone as $ms_id) {
                TaskMilestone::create([
                    'task_id' => $task->id,
                    'milestone_id' => $ms_id,
                ]);
            }
            // Force to have Payment Collection milestone whenever amount to collect is greater than 0
            if ($req->amount_to_collect > 0 && ! array_intersect($req->milestone, getPaymentCollectionIds())) {
                TaskMilestone::create([
                    'task_id' => $task->id,
                    'milestone_id' => Milestone::where('type', $req->task)->where('name', 'Payment Collection')->value('id'),
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
            // Attachment
            if ($req->hasFile('attachment')) {
                foreach ($req->file('attachment') as $key => $file) {
                    $path = Storage::putFile(Attachment::TASK_PATH, $file);
                    Attachment::create([
                        'object_type' => Task::class,
                        'object_id' => $task->id,
                        'src' => basename($path),
                    ]);
                }
            } elseif ($req->ticket != null) {
                $atts = Attachment::where([
                    'object_type' => Ticket::class,
                    'object_id' => $req->ticket,
                ])->get();

                for ($i = 0; $i < count($atts); $i++) {
                    $extension = explode('.', $atts[$i]->src)[1];

                    while (true) {
                        $filename = generateRandomAlphabet(40).'.'.$extension;

                        $exists = Storage::exists(Attachment::TASK_PATH.'/'.$filename);
                        if (! $exists) {
                            break;
                        }
                    }
                    Attachment::create([
                        'object_type' => Task::class,
                        'object_id' => $task->id,
                        'src' => $filename,
                    ]);
                    Storage::copy(Attachment::TICKET_PATH.'/'.$atts[$i]->src, Attachment::TASK_PATH.'/'.$filename);
                }
            }

            $this->createLog($task, 'Task created');

            Notification::send(User::whereIn('id', $req->assign)->get(), new MobileAppNotification([
                'type' => 'task_created',
                'assigned_by' => Auth::user()->id,
                'task_id' => $task->id,
            ]));

            DB::commit();

            $ticket = Ticket::where('id', $req->ticket)->first();
            $so_inv_count = count(explode(',', $ticket->so_inv)) ?? null;
            if ($so_inv_count != null && ($req->so_inv_idx + 1) != $so_inv_count) {
                return redirect(route('task.technician.create', ['tic_id' => $req->ticket, 'so_inv_idx' => $req->so_inv_idx + 1]))->with('success', 'Task created');
            }

            if ($req->ticket != null) {
                Ticket::where('id', $req->ticket)->delete();
            }

            return redirect(route('task.technician.index'))->with('success', 'Task created');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator')->withInput();
        }
    }

    public function saleStore(Request $req)
    {
        if ($req->amount_to_collect == null) {
            $req->merge(['amount_to_collect' => 0]);
        }
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
                'amount_to_collect' => $req->amount_to_collect,
            ]);
            (new Branch)->assign(Task::class, $task->id);

            if ($req->ticket != null) {
                Ticket::where('id', $req->ticket)->delete();
            }

            foreach ($req->assign as $assign_id) {
                UserTask::create([
                    'user_id' => $assign_id,
                    'task_id' => $task->id,
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
            } elseif ($req->ticket != null) {
                $atts = Attachment::where([
                    'object_type' => Ticket::class,
                    'object_id' => $req->ticket,
                ])->get();

                for ($i = 0; $i < count($atts); $i++) {
                    $extension = explode('.', $atts[$i]->src)[1];

                    while (true) {
                        $filename = generateRandomAlphabet(40).'.'.$extension;

                        $exists = Storage::exists(Attachment::TASK_PATH.'/'.$filename);
                        if (! $exists) {
                            break;
                        }
                    }
                    Attachment::create([
                        'object_type' => Task::class,
                        'object_id' => $task->id,
                        'src' => $filename,
                    ]);
                    Storage::copy(Attachment::TICKET_PATH.'/'.$atts[$i]->src, Attachment::TASK_PATH.'/'.$filename);
                }
            }

            $this->createLog($task, 'Task created');

            Notification::send(User::whereIn('id', $req->assign)->get(), new MobileAppNotification([
                'type' => 'task_created',
                'assigned_by' => Auth::user()->id,
                'task_id' => $task->id,
            ]));

            DB::commit();

            return redirect(route('task.sale.index'))->with('success', 'Task created');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator')->withInput();
        }
    }

    public function view(Task $task)
    {
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

    public function edit(Task $task)
    {
        $task->load('users', 'milestones', 'attachments', 'services');

        return view('task.form', [
            'task' => $task,
        ]);
    }

    public function driverUpdate(Request $req, Task $task)
    {
        if ($req->amount_to_collect == null) {
            $req->merge(['amount_to_collect' => 0]);
        }
        // Validate request
        $validator = Validator::make($req->all(), self::DRIVER_SALE_FORM_RULES, [], [
            'sale_order_id' => 'sale order',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $task->update([
                'ticket_id' => $req->ticket,
                'sale_order_id' => $req->sale_order_id,
                'customer_id' => $req->customer,
                'name' => $req->name,
                'desc' => $req->desc,
                'start_date' => $req->start_date,
                'due_date' => $req->due_date,
                'estimated_time' => $req->estimated_time,
                'remark' => $req->remark,
                'status' => $req->status,
                'amount_to_collect' => $req->amount_to_collect,
            ]);

            UserTask::where('task_id', $task->id)->delete();
            foreach ($req->assign as $assign_id) {
                UserTask::create([
                    'user_id' => $assign_id,
                    'task_id' => $task->id,
                ]);
            }

            TaskMilestone::where('task_id', $task->id)->whereNotIn('milestone_id', $req->amount_to_collect > 0 ? array_merge($req->milestone, getPaymentCollectionIds()) : $req->milestone)->delete();
            foreach ($req->milestone as $ms_id) {
                $ms = TaskMilestone::where('task_id', $task->id)->where('milestone_id', $ms_id)->first();
                if ($ms == null) {
                    TaskMilestone::create([
                        'task_id' => $task->id,
                        'milestone_id' => $ms_id,
                    ]);
                }
            }
            // Force to have Payment Collection milestone whenever amount to collect is greater than 0
            if ($req->amount_to_collect > 0 && ! TaskMilestone::where('task_id', $task->id)->whereIn('milestone_id', getPaymentCollectionIds())->exists()) {
                TaskMilestone::create([
                    'task_id' => $task->id,
                    'milestone_id' => Milestone::where('type', Milestone::TYPE_DRIVER_TASK)->where('name', 'Payment Collection')->value('id'),
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
                Attachment::where([
                    ['object_type', Task::class],
                    ['object_id', $task->id],
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

    public function technicianUpdate(Request $req, Task $task)
    {
        if ($req->amount_to_collect == null) {
            $req->merge(['amount_to_collect' => 0]);
        }
        // Validate request
        $validator = Validator::make($req->all(), self::TECHNICIAN_FORM_RULES, [], [
            'sale_order_id' => 'sale order',
            'product_id' => 'product',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $task->update([
                'ticket_id' => $req->ticket,
                'task_type' => $req->task,
                'sale_order_id' => $req->sale_order_id,
                'product_id' => $req->product_id,
                'product_child_id' => $req->product_child_id,
                'customer_id' => $req->customer,
                'name' => $req->name,
                'desc' => $req->desc,
                'start_date' => $req->start_date,
                'due_date' => $req->due_date,
                'remark' => $req->remark,
                'status' => $req->status,
                'amount_to_collect' => $req->amount_to_collect,
            ]);
            // Services
            TaskService::where('task_id', $task->id)->delete();
            if ($req->services != null) {
                foreach ($req->services as $service_id) {
                    TaskService::create([
                        'task_id' => $task->id,
                        'service_id' => $service_id,
                        'amount' => Service::where('id', $service_id)->value('amount'),
                    ]);
                }
            }
            // Assign
            UserTask::where('task_id', $task->id)->delete();
            foreach ($req->assign as $assign_id) {
                UserTask::create([
                    'user_id' => $assign_id,
                    'task_id' => $task->id,
                ]);
            }
            // Milestone
            TaskMilestone::where('task_id', $task->id)->whereNotIn('milestone_id', $req->amount_to_collect > 0 ? array_merge($req->milestone, getPaymentCollectionIds()) : $req->milestone)->delete();
            foreach ($req->milestone as $ms_id) {
                $ms = TaskMilestone::where('task_id', $task->id)->where('milestone_id', $ms_id)->first();
                if ($ms == null) {
                    TaskMilestone::create([
                        'task_id' => $task->id,
                        'milestone_id' => $ms_id,
                    ]);
                }
            }
            // Force to have Payment Collection milestone whenever amount to collect is greater than 0
            if ($req->amount_to_collect > 0 && ! TaskMilestone::where('task_id', $task->id)->whereIn('milestone_id', getPaymentCollectionIds())->exists()) {
                TaskMilestone::create([
                    'task_id' => $task->id,
                    'milestone_id' => Milestone::where('type', $req->task)->where('name', 'Payment Collection')->value('id'),
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
            }

            $this->createLog($task, 'Task updated');

            DB::commit();

            return redirect(route('task.technician.index'))->with('success', 'Task updated');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator')->withInput();
        }
    }

    public function saleUpdate(Request $req, Task $task)
    {
        if ($req->amount_to_collect == null) {
            $req->merge(['amount_to_collect' => 0]);
        }
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
                    'task_id' => $task->id,
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
                    ['object_id', $task->id],
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

    public function delete(Task $task)
    {
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

    public function generate99ServiceReport(Request $req)
    {
        $task_ids = explode(',', $req->task_ids);

        $photo_equipment_ms_id = Milestone::where('type', Milestone::TYPE_SERVICE_TASK)
            ->where('is_custom', false)
            ->where('name', 'Photo of Equipment')
            ->value('id');
        $before_service_ms_id = Milestone::where('type', Milestone::TYPE_SERVICE_TASK)
            ->where('is_custom', false)
            ->where('name', 'Before Service')
            ->value('id');
        $afer_service_ms_id = Milestone::where('type', Milestone::TYPE_SERVICE_TASK)
            ->where('is_custom', false)
            ->where('name', 'After Service')
            ->value('id');

        $tasks = Task::where('type', Task::TYPE_TECHNICIAN)
            ->whereIn('id', $task_ids)
            ->orderBy('id', 'desc')
            ->get();

        $technicians = [];
        $dates = [];
        $records = [];

        for ($i = 0; $i < count($tasks); $i++) {
            // Technicians
            for ($j = 0; $j < count($tasks[$i]->users); $j++) {
                $user = $tasks[$i]->users[$j];
                if (! in_array($user->name, $technicians)) {
                    $technicians[] = $user->name;
                }
            }
            // Dates
            $dates[] = Carbon::parse($tasks[$i]->start_date)->format('d M Y');
            // Images
            $equipment_img = TaskMilestone::with('attachments')->where('task_id', $tasks[$i]->id)->where('milestone_id', $photo_equipment_ms_id)->whereNotNull('submitted_at')->first();
            $before_img = TaskMilestone::with('attachments')->where('task_id', $tasks[$i]->id)->where('milestone_id', $before_service_ms_id)->whereNotNull('submitted_at')->first();
            $after_img = TaskMilestone::with('attachments')->where('task_id', $tasks[$i]->id)->where('milestone_id', $afer_service_ms_id)->whereNotNull('submitted_at')->first();

            $records[] = [
                'task_name' => $tasks[$i]->name,
                'equipment_img' => $equipment_img != null && count($equipment_img->attachments) > 0 ? $equipment_img->attachments[0]->url : null,
                'before_img' => $before_img != null && count($before_img->attachments) > 0 ? $before_img->attachments[0]->url : null,
                'after_img' => $after_img != null && count($after_img->attachments) > 0 ? $after_img->attachments[0]->url : null,
            ];
        }

        $pdf = Pdf::loadView('task.report_pdf', [
            'records' => $records,
            'technicians' => implode(', ', $technicians),
            'dates' => implode(', ', $dates),
            'photo_equipment_ms_id' => $photo_equipment_ms_id,
            'before_service_ms_id' => $before_service_ms_id,
            'afer_service_ms_id' => $afer_service_ms_id,
        ]);
        $pdf->setPaper('A4', 'letter');

        return $pdf->download('99-service-report.pdf');
    }

    private function createLog(Task $task, string $desc)
    {
        $task->load('users', 'milestones', 'attachments');

        $task->formatted_created_at = Carbon::parse($task->created_at)->format('d M Y');
        $task->start_date = Carbon::parse($task->start_date)->format('d M Y');
        $task->due_date = Carbon::parse($task->due_date)->format('d M Y');
        $task->status = (new Task)->statusToHumanRead($task->status);
        $task->progress = (new Task)->getProgress($task);

        (new ActivityLog)->store(Task::class, $task->id, $desc, $task);
    }
}
