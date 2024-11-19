@extends('layouts.app')

@section('content')
    <div class="mb-6 flex justify-between items-center">
        <x-app.page-title url="{{ $for_role == 'driver' ? route('task.driver.index') : ($for_role == 'technician' ? route('task.technician.index') : route('task.sale.index')) }}">{{ __('View Task') }}</x-app.page-title>
    </div>
    @include('components.app.alert.parent')
    <div class="bg-white p-4 rounded-md shadow flex">
        <div class="flex-[2]">
            <div class="border rounded-md flex">
                <div class="flex-1 flex flex-col p-3">
                    <span class="text-md">{{ __('Created') }}</span>
                    <span class="text-lg mt-2 font-semibold">{{ $task->formatted_created_at }}</span>
                </div>
                <div class="flex-1 flex flex-col p-3 border-x">
                    <span class="text-md">{{ __('Start Date') }}</span>
                    <span class="text-lg mt-2 font-semibold">{{ $task->start_date }}</span>
                </div>
                <div class="flex-1 flex flex-col p-3">
                    <span class="text-md">{{ __('Due Date') }}</span>
                    <span class="text-lg mt-2 font-semibold">{{ $task->due_date }}</span>
                </div>
            </div>
            <div class="flex gap-x-4">
                <div class="flex-1 flex flex-col pt-4">
                    <div class="bg-blue-300 rounded-lg p-1.5 flex flex-col">
                        <span class="flex-1 uppercase text-lg text-center font-semibold">{{ $task->status }}</span>
                        <span class="text-xs text-center font-semibold mt-1 bg-white rounded-full">{{ __('Status') }}</span>
                    </div>
                </div>
                <div class="flex-1 flex flex-col pt-4">
                    <div class="bg-slate-300 rounded-lg p-1.5 flex flex-col">
                        <span class="flex-1 uppercase text-lg text-center font-semibold">{{ $task->progress }} %</span>
                        <span class="text-xs text-center font-semibold mt-1 bg-white rounded-full">{{ __('Progress') }}</span>
                    </div>
                </div>
            </div>
            <div class="border-t pt-4 mt-4">
                <div class="mb-4">
                    <h6 class="text-md font-semibold">{{ __('Task Name') }}</h6>
                    <span class="text-md text-slate-500">{{ $task->name }}</span>
                </div>
                <div class="mb-4">
                    <h6 class="text-md font-semibold">{{ __('Task Description') }}</h6>
                    <span class="text-md text-slate-500">{{ $task->desc }}</span>
                </div>
                <div>
                    <h6 class="text-md font-semibold">{{ __('Attachments') }}</h6>
                    @if (count($task->attachments) > 0)
                        <div class="flex flex-wrap gap-y-2 mt-2">
                            @foreach ($task->attachments as $att)
                                <a href="{{ $att->url }}" target="_blank" class="w-1/2 flex items-center gap-x-2 px-1.5 py-1.5 rounded-lg">
                                    <div class="">
                                        <svg class="h-6 w-6" id="Layer_1" height="512" viewBox="0 0 24 24" width="512" xmlns="http://www.w3.org/2000/svg" data-name="Layer 1"><path d="m17 14a1 1 0 0 1 -1 1h-8a1 1 0 0 1 0-2h8a1 1 0 0 1 1 1zm-4 3h-5a1 1 0 0 0 0 2h5a1 1 0 0 0 0-2zm9-6.515v8.515a5.006 5.006 0 0 1 -5 5h-10a5.006 5.006 0 0 1 -5-5v-14a5.006 5.006 0 0 1 5-5h4.515a6.958 6.958 0 0 1 4.95 2.05l3.484 3.486a6.951 6.951 0 0 1 2.051 4.949zm-6.949-7.021a5.01 5.01 0 0 0 -1.051-.78v4.316a1 1 0 0 0 1 1h4.316a4.983 4.983 0 0 0 -.781-1.05zm4.949 7.021c0-.165-.032-.323-.047-.485h-4.953a3 3 0 0 1 -3-3v-4.953c-.162-.015-.321-.047-.485-.047h-4.515a3 3 0 0 0 -3 3v14a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3z"/></svg>
                                    </div>
                                    <div class="p-y.5 px-1.5 rounded bg-slate-100 inline truncate">
                                        <span class="text-xs text-slate-500">{{ $att->src }}</span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="flex-1 pl-4 ml-4 border-l">
            <div class="bg-blue-900 rounded-lg p-2">
                <h1 class="font-black text-xl text-white">{{ __('Task ID') }}: {{ $task->sku }}</h1>
            </div>
            <div class="border-t pt-4 mt-4">
                <h6 class="text-md font-semibold">{{ __('Assigned') }}</h6>
                <ul>
                    @foreach ($task->users as $u)
                        <li class="flex items-center gap-x-4 my-2">
                            <span>
                                <div class="h-8 w-8 rounded-full border overflow-hidden">
                                    @if (count($u->pictures) > 0)
                                        <img src="{{ $u->latest_picture->url }}" alt="" class="h-full w-full object-cover">
                                    @else
                                        <img src="{{ asset('images/avatar.jpg') }}" alt="Avatar image" class="h-full w-full object-cover">
                                    @endif
                                </div>
                            </span>
                            <span class="flex-1 text-md">{{ $u->name }}</span>
                            <span class="text-md">{{ getUserRole($u) }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
            <div class="border-t pt-4 mt-4">
                <h6 class="text-md font-semibold">{{ __('Remark') }}</h6>
                <p class="text-md text-slate-500">{{ $task->remark ?? 'No Remark' }}</p>
            </div>
            <div class="border-t pt-4 mt-4">
                <h6 class="text-md font-semibold">{{ __('Last Activity') }}</h6>
                <ul class="overflow-y-auto max-h-60">
                    @foreach ($task->logs as $log)
                        <li class="my-2">
                            <div class="mb-1">
                                <p class="text-sm leading-none">{{ $log->desc }}</p>
                            </div>
                            <span class="text-sm text-slate-400 leading-none">{{ \Carbon\Carbon::parse($log->created_at)->format('d M Y H:i') }} &#x2022; {{ $log->doneBy == null && isSuperAdmin() ? 'Admin' : $log->doneBy->name }} &#x2022; <a href="{{ route('view_log', ['log' => $log]) }}" class="text-blue-500">View Data</a></span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endsection