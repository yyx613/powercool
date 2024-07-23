<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Attachment;
use App\Models\Task;
use App\Models\TaskMilestone;
use App\Models\UserTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    public function index() {
        return view('task.list', [
            'dueDate' => 2,
            'toDo' => 3, 
            'doing' => 1,
            'inReview' => 4,
            'completed' => 5,
        ]);
    }

    public function driverGetData() {
        $data = $this->genericGetData(Task::TYPE_DRIVER);

        return response()->json($data);
    }

    public function technicianGetData() {
        $data = $this->genericGetData(Task::TYPE_TECHNICIAN);

        return response()->json($data);
    }

    public function saleGetData() {
        $data = $this->genericGetData(Task::TYPE_SALE);

        return response()->json($data);
    }

    public function create() {
        return view('task.form');
    }

    public function driverStore(Request $req) {
        // Validate request
        $validator = Validator::make($req->all(), [
            'ticket' => 'nullable',
            'customer' => 'required',
            'name' => 'required|max:250',
            'desc' => 'required|max:250',
            'start_date' => 'required',
            'remark' => 'nullable|max:250',
            'priority' => 'required',
            'status' => 'required',
            'assign' => 'required',
            'assign.*' => 'exists:users,id',
            'milestone' => 'required',
            'collect_payment' => 'required',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $task = Task::create([
                'type' => Task::TYPE_DRIVER,
                'customer_id' => $req->customer,
                'name' => $req->name,
                'desc' => $req->desc,
                'start_date' => $req->start_date,
                'remark' => $req->remark,
                'priority' => $req->priority,
                'status' => $req->status,
                'collect_payment' => $req->boolean('collect_payment'),
            ]);

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
        $validator = Validator::make($req->all(), [
            'ticket' => 'nullable',
            'task' => 'required',
            'customer' => 'required',
            'name' => 'required|max:250',
            'desc' => 'required|max:250',
            'start_date' => 'required',
            'remark' => 'nullable|max:250',
            'priority' => 'required',
            'status' => 'required',
            'assign' => 'required',
            'assign.*' => 'exists:users,id',
            'milestone' => 'required',
            'collect_payment' => 'required',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $task = Task::create([
                'type' => Task::TYPE_TECHNICIAN,
                'task_type' => $req->task,
                'customer_id' => $req->customer,
                'name' => $req->name,
                'desc' => $req->desc,
                'start_date' => $req->start_date,
                'remark' => $req->remark,
                'priority' => $req->priority,
                'status' => $req->status,
                'collect_payment' => $req->boolean('collect_payment'),
            ]);

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
        $validator = Validator::make($req->all(), [
            'ticket' => 'nullable',
            'customer' => 'required',
            'name' => 'required|max:250',
            'desc' => 'required|max:250',
            'start_date' => 'required',
            'remark' => 'nullable|max:250',
            'priority' => 'required',
            'status' => 'required',
            'assign' => 'required',
            'assign.*' => 'exists:users,id',
            'milestone' => 'required',
            'collect_payment' => 'required',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $task = Task::create([
                'type' => Task::TYPE_SALE,
                'customer_id' => $req->customer,
                'name' => $req->name,
                'desc' => $req->desc,
                'start_date' => $req->start_date,
                'remark' => $req->remark,
                'priority' => $req->priority,
                'status' => $req->status,
                'collect_payment' => $req->boolean('collect_payment'),
            ]);

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

            DB::commit();

            return redirect(route('task.sale.index'))->with('success', 'Task created');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator')->withInput();
        }
    }

    public function edit(Task $task) {
        $task->load('users', 'milestones', 'attachments');

        return view('task.form', [
            'task' => $task
        ]);
    }

    public function driverUpdate(Request $req, Task $task) {
        // Validate request
        $validator = Validator::make($req->all(), [
            'ticket_id' => 'nullable',
            'customer' => 'required',
            'name' => 'required|max:250',
            'desc' => 'required|max:250',
            'start_date' => 'required',
            'remark' => 'nullable|max:250',
            'priority' => 'required',
            'status' => 'required',
            'assign' => 'required',
            'assign.*' => 'exists:users,id',
            'milestone' => 'required',
            'collect_payment' => 'required',
            'attachment' => 'nullable',
            'attachment.*' => 'file'
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $task->update([
                'customer_id' => $req->customer,
                'name' => $req->name,
                'desc' => $req->desc,
                'start_date' => $req->start_date,
                'remark' => $req->remark,
                'priority' => $req->priority,
                'status' => $req->status,
                'collect_payment' => $req->boolean('collect_payment'),
            ]);

            UserTask::where('task_id', $task->id)->delete();
            foreach ($req->assign as $assign_id) {
                UserTask::create([
                    'user_id' => $assign_id,
                    'task_id' => $task->id
                ]);
            }

            TaskMilestone::where('task_id', $task->id)->delete();
            foreach ($req->milestone as $ms_id) {
                TaskMilestone::create([
                    'task_id' => $task->id,
                    'milestone_id' => $ms_id,
                ]);
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
        $validator = Validator::make($req->all(), [
            'ticket' => 'nullable',
            'task' => 'required',
            'customer' => 'required',
            'name' => 'required|max:250',
            'desc' => 'required|max:250',
            'start_date' => 'required',
            'remark' => 'nullable|max:250',
            'priority' => 'required',
            'status' => 'required',
            'assign' => 'required',
            'assign.*' => 'exists:users,id',
            'milestone' => 'required',
            'collect_payment' => 'required',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $task->update([
                'task_type' => $req->task,
                'customer_id' => $req->customer,
                'name' => $req->name,
                'desc' => $req->desc,
                'start_date' => $req->start_date,
                'remark' => $req->remark,
                'priority' => $req->priority,
                'status' => $req->status,
                'collect_payment' => $req->boolean('collect_payment'),
            ]);

            UserTask::where('task_id', $task->id)->delete();
            foreach ($req->assign as $assign_id) {
                UserTask::create([
                    'user_id' => $assign_id,
                    'task_id' => $task->id
                ]);
            }

            TaskMilestone::where('task_id', $task->id)->delete();
            foreach ($req->milestone as $ms_id) {
                TaskMilestone::create([
                    'task_id' => $task->id,
                    'milestone_id' => $ms_id,
                ]);
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
        $validator = Validator::make($req->all(), [
            'ticket_id' => 'nullable',
            'customer' => 'required',
            'name' => 'required|max:250',
            'desc' => 'required|max:250',
            'start_date' => 'required',
            'remark' => 'nullable|max:250',
            'priority' => 'required',
            'status' => 'required',
            'assign' => 'required',
            'assign.*' => 'exists:users,id',
            'milestone' => 'required',
            'collect_payment' => 'required',
            'attachment' => 'nullable',
            'attachment.*' => 'file'
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $task->update([
                'customer_id' => $req->customer,
                'name' => $req->name,
                'desc' => $req->desc,
                'start_date' => $req->start_date,
                'remark' => $req->remark,
                'priority' => $req->priority,
                'status' => $req->status,
                'collect_payment' => $req->boolean('collect_payment'),
            ]);

            UserTask::where('task_id', $task->id)->delete();
            foreach ($req->assign as $assign_id) {
                UserTask::create([
                    'user_id' => $assign_id,
                    'task_id' => $task->id
                ]);
            }

            TaskMilestone::where('task_id', $task->id)->delete();
            foreach ($req->milestone as $ms_id) {
                TaskMilestone::create([
                    'task_id' => $task->id,
                    'milestone_id' => $ms_id,
                ]);
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

            DB::commit();

            return redirect(route('task.sale.index'))->with('success', 'Task updated');
        } catch (\Throwable $th) {
            DB::rollBack();
            report($th);

            return back()->with('error', 'Something went wrong. Please contact administrator')->withInput();
        }
    }

    public function delete(Task $task) {
        $task->delete();

        return back()->with('success', 'Task deleted');
    }

    private function genericGetData(int $type) {
        $records = Task::where('type', $type)->orderBy('id', 'desc');

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
                'due_date' => $record->start_date,
                'status' => $record->status,
                'priority' => $record->priority
            ];
        }

        return $data;
    }
}
