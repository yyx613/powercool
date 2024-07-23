@extends('layouts.app')

@section('content')
    <div class="mb-6 flex justify-between items-center">
        <x-app.page-title>{{ isset($task) ? 'Edit Task' : 'Create New Task' }}</x-app.page-title>
    </div>
    @include('components.app.alert.parent')
    <form action="{{ isset($task) ? route($form_route_name, ['task' => $task]) : route($form_route_name) }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="bg-white p-4 rounded-md shadow" id="content-container">
            <div class="grid grid-cols-3 gap-8 w-full mb-4">
                <div class="flex flex-col">
                    <x-app.input.label id="ticket" class="mb-1">Ticket</x-app.input.label>
                    <x-app.input.select2 name="ticket" id="ticket" :hasError="$errors->has('ticket')" placeholder="Select a ticket">
                        <option value="">Select a ticket</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" >{{ $user->name }}</option>
                        @endforeach
                    </x-app.input.select2>
                    <x-input-error :messages="$errors->get('ticket')" class="mt-1" />
                </div>
                @if ($for_role == 'technician')
                    <div class="flex flex-col">
                        <x-app.input.label id="task" class="mb-1">Task</x-app.input.label>
                        <x-app.input.select2 name="task" id="task" :hasError="$errors->has('task')" placeholder="Select a task">
                            <option value="">Select a task</option>
                            @foreach ($task_types as $key => $val)
                                <option value="{{ $key }}" @selected(old('task', isset($task) ? $task->task_type : null) == $key)>{{ $val }}</option>
                            @endforeach
                        </x-app.input.select2>
                        <x-input-error :messages="$errors->get('task')" class="mt-1" />
                    </div>
                @endif
                <div class="flex flex-col">
                    <x-app.input.label id="customer" class="mb-1">Customer</x-app.input.label>
                    <x-app.input.select2 name="customer" id="customer" :hasError="$errors->has('customer')" placeholder="Select a customer">
                        <option value="">Select a customer</option>
                        @foreach ($customers as $cu)
                            <option value="{{ $cu->id }}" @selected(old('customer', isset($task) ? $task->customer_id : null) == $cu->id)>{{ $cu->name }}</option>
                        @endforeach
                    </x-app.input.select2>
                    <x-input-error :messages="$errors->get('customer')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="name" class="mb-1">Name</x-app.input.label>
                    <x-app.input.input name="name" id="name" :hasError="$errors->has('name')" value="{{ old('name', isset($task) ? $task->name : null) }}" />
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>
                <div class="flex flex-col col-span-2">
                    <x-app.input.label id="desc" class="mb-1">Description</x-app.input.label>
                    <x-app.input.input name="desc" id="desc" :hasError="$errors->has('desc')" value="{{ old('desc', isset($task) ? $task->desc : null) }}" />
                    <x-input-error :messages="$errors->get('desc')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="start_date" class="mb-1">State Date</x-app.input.label>
                    <x-app.input.input name="start_date" id="start_date" :hasError="$errors->has('start_date')" value="{{ old('start_date', isset($task) ? $task->start_date : null) }}" />
                    <x-input-error :messages="$errors->get('start_date')" class="mt-1" />
                </div>
                <div class="flex flex-col col-span-2">
                    <x-app.input.label id="remark" class="mb-1">Remark</x-app.input.label>
                    <x-app.input.input name="remark" id="remark" :hasError="$errors->has('remark')" value="{{ old('remark', isset($task) ? $task->remark : null) }}" />
                    <x-input-error :messages="$errors->get('remark')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="priority" class="mb-1">Priority</x-app.input.label>
                    <x-app.input.select name="priority" id="priority" :hasError="$errors->has('priority')">
                        <option value="">Select a priority</option>
                        <option value="1" @selected(old('priority', isset($task) ? $task->priority : null) == 1)>Low</option>
                        <option value="2" @selected(old('priority', isset($task) ? $task->priority : null) == 2)>Medium</option>
                        <option value="3" @selected(old('priority', isset($task) ? $task->priority : null) == 3)>High</option>
                    </x-app.input.select>
                    <x-input-error :messages="$errors->get('priority')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="status" class="mb-1">Status</x-app.input.label>
                    <x-app.input.select name="status" id="status" :hasError="$errors->has('status')">
                        <option value="">Select a status</option>
                        <option value="1" @selected(old('status', isset($task) ? $task->status : null) == 1)>To Do</option>
                        <option value="2" @selected(old('status', isset($task) ? $task->status : null) == 2)>Doing</option>
                        <option value="3" @selected(old('status', isset($task) ? $task->status : null) == 3)>In Review</option>
                        <option value="4" @selected(old('status', isset($task) ? $task->status : null) == 4)>Completed</option>
                    </x-app.input.select>
                    <x-input-error :messages="$errors->get('status')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="collect_payment" class="mb-1">Collect Payment</x-app.input.label>
                    <x-app.input.select name="collect_payment" id="collect_payment" :hasError="$errors->has('collect_payment')">
                        <option value="">Select a Yes/No</option>
                        <option value="0" @selected(old('collect_payment', isset($task) ? $task->collect_payment : null) === 0)>No</option>
                        <option value="1" @selected(old('collect_payment', isset($task) ? $task->collect_payment : null) == 1)>Yes</option>
                    </x-app.input.select>
                    <x-input-error :messages="$errors->get('collect_payment')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label class="mb-1">Attachments</x-app.input.label>
                    <x-app.input.file id="attachment[]" :hasError="$errors->has('attachment')" multiple="true" />
                    <x-input-error :messages="$errors->get('attachment')" class="mt-1" />
                    <div class="uploaded-file-preview-container" data-id="attachment">
                        <div class="p-y.5 px-1.5 rounded bg-blue-50 mt-2 hidden" id="uploaded-file-template">
                            <a href="" target="_blank" class="text-blue-700 text-xs"></a>
                        </div>
                        @if (isset($task))
                            @foreach ($task->attachments as $att)
                                <div class="p-y.5 px-1.5 rounded bg-blue-50 mt-2 old-preview">
                                    <a href="{{ $att->url }}" target="_blank" class="text-blue-700 text-xs">{{ $att->src }}</a>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
                <div class="flex flex-col col-span-3">
                    <x-app.input.label id="assign" class="mb-1">Assigned</x-app.input.label>
                    <x-app.input.select name="assign[]" id="assign" :hasError="$errors->has('assign')" multiple>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" @selected(in_array($user->id, old('assign', isset($task) ? $task->users()->pluck('user_id')->toArray() : [])))>{{ $user->name }}</option>
                    @endforeach
                    </x-app.input.select>
                    <x-input-error :messages="$errors->get('assign')" class="mt-1" />
                </div>
                <div class="flex flex-col col-span-3">
                    <x-app.input.label class="mb-2">Service Task Milestones</x-app.input.label>
                    @foreach($milestones as $stone)
                        <div class="flex items-center gap-x-2 mb-2 {{ $for_role == 'technician' ? 'hidden' : '' }} milestone-selection" data-type="{{ $stone->type }}">
                            <input type="checkbox" name="milestone[]" id="{{ $stone->id }}" value="{{ $stone->id }}" class="rounded-sm" @checked(in_array($stone->id, old('milestone', isset($task) ? $task->milestones()->pluck('milestone_id')->toArray() : [])))>
                            <label for="{{ $stone->id }}" class="text-sm">{{ $stone->name }}</label>
                        </div>
                    @endforeach
                    <x-input-error :messages="$errors->get('milestone')" class="mt-1" />
                </div>
            </div>
            <div class="mt-4 flex justify-end">
                <x-app.button.submit>{{ isset($task) ? 'Update Task' : 'Create New Task' }}</x-app.button.submit>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        TASK = @json($task ?? null);
        INIT_EDIT = true;

        $(document).ready(function() {
            if (TASK != null && TASK.task_type != null) {
                $('select[name="task"]').trigger('change')
            }
            INIT_EDIT = false
        })

        var param = {
            singleDatePicker: true,
            showDropdowns: true,
            autoUpdateInput: false,
            locale: {
                format: 'YYYY-MM-DD'
            }
        }
        $('input[name="start_date"]').daterangepicker(param)
        $('input[name="start_date"]').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD'));
        });

        $('input[name="attachment[]"]').on('change', function() {
            let files = $(this).prop('files');

            $('.uploaded-file-preview-container[data-id="attachment"]').find('.old-preview').remove()
        
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                
                let clone = $('#uploaded-file-template')[0].cloneNode(true);
                $(clone).find('a').text(file.name)
                $(clone).find('a').attr('href', URL.createObjectURL(file))
                $(clone).addClass('old-preview')
                $(clone).removeClass('hidden')
                $(clone).removeAttr('id')

                $('.uploaded-file-preview-container[data-id="attachment"]').append(clone)
                $('.uploaded-file-preview-container[data-id="attachment"]').removeClass('hidden')
            }
        })

        $('select[name="task"]').on('change', function() {
            let val = $(this).val()

            if (!INIT_EDIT) {
                $('.milestone-selection').find('input').prop('checked', false)
                $('.milestone-selection').addClass('hidden')
            }

            $('.milestone-selection').each(function(i, obj) {
                if ($(obj).data('type') == val) {
                    $(obj).removeClass('hidden')
                }
            })
        })
    </script>
@endpush