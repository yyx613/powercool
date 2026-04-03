@extends('layouts.app')

@section('content')
    <div class="mb-6 flex justify-between items-center">
        <x-app.page-title url="{{ $for_role == 'driver' ? route('task.driver.index') : ($for_role == 'technician' ? route('task.technician.index') : route('task.sale.index')) }}">{{ __('View Task') }}</x-app.page-title>
    </div>
    @include('components.app.alert.parent')
    <div class="bg-white p-4 rounded-md shadow flex flex-col lg:flex-row">
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
                <div class="mb-4">
                    <h6 class="text-md font-semibold">{{ __('Milestones') }}</h6>
                    @if (count($task->milestones) > 0)
                        <ul class="mt-2">
                            @foreach ($task->milestones as $ms)
                                <li class="w-full flex items-center gap-x-2 py-1 transition duration-300 hover:bg-slate-50">
                                    <svg class="h-5 w-5 fill-blue-500 {{ $ms->pivot->submitted_at != null ? 'hidden' : '' }}"
                                        xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1"
                                        viewBox="0 0 24 24" width="512" height="512">
                                        <path d="M2.42,6.49c-.45-.32-.56-.94-.24-1.39,.8-1.13,1.78-2.11,2.91-2.91,.45-.32,1.08-.21,1.39,.24,.32,.45,.21,1.08-.24,1.39-.94,.67-1.76,1.48-2.43,2.43-.19,.28-.5,.42-.82,.42-.2,0-.4-.06-.58-.18Zm1.4,11.26c-.32-.45-.94-.56-1.39-.24-.45,.32-.56,.94-.24,1.39,.8,1.13,1.78,2.11,2.91,2.91,.17,.12,.38,.18,.58,.18,.31,0,.62-.15,.82-.42,.32-.45,.21-1.08-.24-1.39-.95-.67-1.76-1.48-2.43-2.43Zm-1.82-5.75c0-.61,.05-1.22,.16-1.81,.1-.54-.26-1.06-.8-1.16-.54-.1-1.06,.26-1.16,.8-.13,.71-.2,1.44-.2,2.17s.07,1.46,.2,2.17c.09,.48,.51,.82,.98,.82,.06,0,.12,0,.18-.02,.54-.1,.9-.62,.8-1.16-.11-.59-.16-1.2-.16-1.81Zm18.18,5.75c-.67,.95-1.48,1.76-2.43,2.43-.45,.32-.56,.94-.24,1.39,.19,.28,.5,.42,.82,.42,.2,0,.4-.06,.58-.18,1.13-.8,2.11-1.78,2.91-2.91,.32-.45,.21-1.08-.24-1.39-.45-.32-1.08-.21-1.39,.24Zm-6.37,4.09c-.59,.11-1.2,.16-1.81,.16s-1.22-.05-1.81-.16c-.54-.1-1.06,.26-1.16,.8-.1,.54,.26,1.06,.8,1.16,.71,.13,1.44,.2,2.17,.2s1.46-.07,2.17-.2c.54-.1,.9-.62,.8-1.16-.1-.54-.62-.91-1.16-.8ZM14.17,.2c-.71-.13-1.44-.2-2.17-.2s-1.46,.07-2.17,.2c-.54,.1-.9,.62-.8,1.16,.09,.48,.51,.82,.98,.82,.06,0,.12,0,.18-.02,.59-.11,1.2-.16,1.81-.16s1.22,.05,1.81,.16c.06,.01,.12,.02,.18,.02,.47,0,.89-.34,.98-.82,.1-.54-.26-1.06-.8-1.16Zm6.01,6.05c.19,.28,.5,.42,.82,.42,.2,0,.4-.06,.58-.18,.45-.32,.56-.94,.24-1.39-.8-1.13-1.78-2.11-2.91-2.91-.45-.32-1.08-.21-1.39,.24-.32,.45-.21,1.08,.24,1.39,.95,.67,1.76,1.48,2.43,2.43Zm3.62,3.58c-.1-.54-.62-.91-1.16-.8-.54,.1-.9,.62-.8,1.16,.11,.59,.16,1.2,.16,1.81s-.05,1.22-.16,1.81c-.1,.54,.26,1.06,.8,1.16,.06,.01,.12,.02,.18,.02,.47,0,.89-.34,.98-.82,.13-.71,.2-1.44,.2-2.17s-.07-1.46-.2-2.17Z"/>
                                    </svg>
                                    <svg class="h-5 w-5 fill-green-500 {{ $ms->pivot->submitted_at != null ? '' : 'hidden' }}"
                                        xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1"
                                        viewBox="0 0 24 24">
                                        <path d="m23.195,14.085l-2.159,2.157v1.722c0,1.654-1.346,3-3,3h-1.721l-2.158,2.157c-.565.566-1.319.879-2.121.879s-1.555-.312-2.121-.879l-2.157-2.157h-1.722c-1.654,0-3-1.346-3-3v-1.722l-2.157-2.157c-1.17-1.169-1.17-3.073,0-4.243l2.157-2.157v-1.721c0-1.654,1.346-3,3-3h1.722l2.157-2.158c1.134-1.135,3.111-1.132,4.243,0l2.157,2.157h1.721c.553,0,1,.448,1,1s-.447,1-1,1h-2.135c-.266,0-.52-.105-.707-.293l-2.451-2.451c-.377-.376-1.034-.378-1.414,0l-2.45,2.451c-.188.188-.441.293-.707.293h-2.136c-.552,0-1,.449-1,1v2.135c0,.265-.105.52-.293.707l-2.45,2.45c-.39.39-.39,1.024,0,1.415l2.45,2.45c.188.188.293.441.293.707v2.136c0,.552.448,1,1,1h2.136c.266,0,.52.105.707.293l2.45,2.45c.379.378,1.037.378,1.413,0l2.452-2.45c.188-.188.441-.293.707-.293h2.135c.552,0,1-.448,1-1v-2.136c0-.266.105-.52.293-.707l2.451-2.45c.39-.39.39-1.025,0-1.415-.39-.391-.39-1.024,0-1.414.391-.39,1.024-.391,1.415,0,1.168,1.169,1.168,3.072,0,4.242Zm-12.474-.423l-3.018-2.988c-.394-.39-1.025-.385-1.415.007-.389.392-.385,1.025.007,1.414l3.019,2.989c.614.608,1.422.913,2.229.913s1.617-.307,2.232-.918l8.93-8.871c.392-.389.394-1.022.004-1.414-.39-.392-1.022-.395-1.414-.005l-8.93,8.872c-.453.451-1.19.45-1.644,0Z"/>
                                    </svg>
                                    <div class="flex items-center justify-between w-full">
                                        <span class="flex-1 text-md">{{ $ms->name }}</span>
                                        @if ($ms->pivot->submitted_at != null)
                                            <div class="flex flex-col items-end">
                                                <span class="text-xs text-slate-600">{{ \Carbon\Carbon::parse($ms->pivot->submitted_at)->format('d M Y H:i') }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-sm text-slate-400 mt-1">{{ __('No milestones') }}</p>
                    @endif
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
                    @else
                        <p class="text-sm text-slate-400 mt-1">{{ __('No attachments') }}</p>
                    @endif
                </div>
                @if ($task->signed_off_at)
                    <div class="mt-4">
                        <h6 class="text-md font-semibold">{{ __('Customer Sign-Off') }}</h6>
                        <div class="mt-2 border rounded-md p-3 bg-slate-50">
                            @if ($task->signature_url)
                                <div class="mb-2">
                                    <img src="{{ $task->signature_url }}" alt="Customer Signature" class="max-h-24 border rounded bg-white p-1">
                                </div>
                            @endif
                            <span class="text-sm text-slate-600">{{ $task->signed_off_by }} &middot; {{ \Carbon\Carbon::parse($task->signed_off_at)->format('d M Y H:i') }}</span>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        <div class="flex-1 lg:pl-4 lg:ml-4 lg:border-l lg:border-t-0 lg:pt-0 lg:mt-0 pt-4 mt-4 border-t">
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
                            <span class="text-md">{{ join(', ', getUserRole($u)) }}</span>
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