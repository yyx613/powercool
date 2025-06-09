@extends('layouts.app')

@vite(['resources/css/jquery.dataTables.min.css'])

@push('styles')
    <style>
        #data-table {
            border: solid 1px rgb(209 213 219);
        }

        #data-table thead th,
        #data-table tbody tr td {
            border-bottom: solid 1px rgb(209 213 219);
        }

        #data-table tbody tr:last-of-type td {
            border-bottom: none;
        }
    </style>
@endpush

@section('content')
    <div class="mb-6 flex justify-between items-start md:items-center flex-col md:flex-row">
        <x-app.page-title class="mb-4 md:mb-0">{{ __('Production') }}</x-app.page-title>
        <div class="flex gap-4">
            @can('production.export_excel')
                <x-app.button.button class="shadow gap-x-2 bg-emerald-300" id="export-btn">
                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24"
                        width="512" height="512">
                        <path
                            d="M18.66,20.9c-.41-.37-1.05-.33-1.41,.09-.57,.65-1.39,1.02-2.25,1.02H5c-1.65,0-3-1.35-3-3V5c0-1.65,1.35-3,3-3h4.51c.16,0,.33,0,.49,.02V7c0,1.65,1.35,3,3,3h5.81c.31,0,.6-.14,.79-.39s.25-.56,.18-.86c-.31-1.22-.94-2.33-1.83-3.22l-3.48-3.48c-1.32-1.32-3.08-2.05-4.95-2.05H5C2.24,0,0,2.24,0,5v14c0,2.76,2.24,5,5,5H15c1.43,0,2.8-.62,3.75-1.69,.37-.41,.33-1.05-.09-1.41ZM12,2.66c.38,.22,.73,.49,1.05,.81l3.48,3.48c.31,.31,.58,.67,.8,1.05h-4.34c-.55,0-1-.45-1-1V2.66Zm11.13,15.43l-1.61,1.61c-.2,.2-.45,.29-.71,.29s-.51-.1-.71-.29c-.39-.39-.39-1.02,0-1.41l1.29-1.29h-7.4c-.55,0-1-.45-1-1s.45-1,1-1h7.4l-1.29-1.29c-.39-.39-.39-1.02,0-1.41s1.02-.39,1.41,0l1.61,1.61c1.15,1.15,1.15,3.03,0,4.19Z" />
                    </svg>
                    {{ __('Export Excel') }}
                </x-app.button.button>
            @endcan
            @can('production.create')
                <a href="{{ route('production.create') }}"
                    class="bg-yellow-400 shadow rounded-md py-2 px-4 flex items-center gap-x-2">
                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                        version="1.1" id="Capa_1" x="0px" y="0px" viewBox="0 0 512 512"
                        style="enable-background:new 0 0 512 512;" xml:space="preserve" width="512" height="512">
                        <path
                            d="M480,224H288V32c0-17.673-14.327-32-32-32s-32,14.327-32,32v192H32c-17.673,0-32,14.327-32,32s14.327,32,32,32h192v192   c0,17.673,14.327,32,32,32s32-14.327,32-32V288h192c17.673,0,32-14.327,32-32S497.673,224,480,224z" />
                    </svg>
                    {{ __('New') }}
                </a>
            @endcan
        </div>
    </div>
    @include('components.app.alert.parent')
    <!-- Summary -->
    <div class="mb-6 flex gap-x-3">
        <x-app.task-summary-card value="{{ $all }}" label="{{ __('All') }}"
            class="flex-1 bg-slate-400 aspect-video cursor-pointer summary-cards" data-type="all" />
        <x-app.task-summary-card value="{{ $to_do }}" label="{{ __('New') }}"
            class="flex-1 bg-red-400 aspect-video cursor-pointer summary-cards" data-type="new" />
        <x-app.task-summary-card value="{{ $doing }}" label="{{ __('In Progress') }}"
            class="flex-1 bg-blue-400 aspect-video cursor-pointer summary-cards" data-type="in-progress" />
        <x-app.task-summary-card value="{{ $completed }}" label="{{ __('Completed') }}"
            class="flex-1 bg-emerald-400 aspect-video cursor-pointer summary-cards" data-type="completed" />
    </div>
    <div>
        <!-- Filters -->
        <div class="flex gap-x-4 max-w-screen-sm w-full mb-4">
            <div class="flex-1">
                <x-app.input.input name="filter_search" id="filter_search" class="flex items-center"
                    placeholder="{{ __('Search') }}">
                    <div class="rounded-md border border-transparent p-1 ml-1">
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24">
                            <path
                                d="M23.707,22.293l-5.969-5.969a10.016,10.016,0,1,0-1.414,1.414l5.969,5.969a1,1,0,0,0,1.414-1.414ZM10,18a8,8,0,1,1,8-8A8.009,8.009,0,0,1,10,18Z" />
                        </svg>
                    </div>
                </x-app.input.input>
            </div>
            @if ($can_start_task)
                <x-app.button.button class="gap-x-2 bg-orange-200" id="start-task-btn">
                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1"
                        viewBox="0 0 24 24">
                        <path
                            d="M20.426,10.097c-1.231-1.445-3.415-3.622-6.832-5.779-2.81-1.774-5.311-2.716-6.915-3.194-.9-.268-1.854-.101-2.612,.464-.758,.566-1.193,1.432-1.193,2.377V20.035c0,.945,.436,1.811,1.193,2.377,.521,.388,1.135,.589,1.761,.589,.284,0,.57-.042,.852-.125,1.604-.478,4.105-1.42,6.915-3.194,3.417-2.158,5.601-4.334,6.832-5.78,.938-1.102,.938-2.703,0-3.805Zm-1.523,2.509c-1.141,1.34-3.173,3.362-6.377,5.385-2.616,1.653-4.934,2.526-6.417,2.968-.291,.089-.6,.032-.846-.151-.248-.185-.39-.466-.39-.773V3.965c0-.307,.142-.589,.39-.773,.169-.126,.367-.191,.57-.191,.092,0,.185,.013,.275,.041,1.483,.442,3.801,1.315,6.417,2.968,3.204,2.023,5.236,4.045,6.377,5.384,.299,.352,.299,.861,0,1.212Z" />
                    </svg>
                    {{ __('Start Task') }}
                </x-app.button.button>
            @endif
            <x-app.button.button class="flex items-center gap-x-4 bg-sky-200 p-2 rounded w-fit" id="generate-barcode-btn">
                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1"
                    viewBox="0 0 24 24" width="512" height="512">
                    <path
                        d="M5,18c-.553,0-1-.448-1-1V7c0-.552,.447-1,1-1s1,.448,1,1v10c0,.552-.447,1-1,1Zm5-1V7c0-.552-.447-1-1-1s-1,.448-1,1v10c0,.552,.447,1,1,1s1-.448,1-1Zm10,0V7c0-.552-.447-1-1-1s-1,.448-1,1v10c0,.552,.447,1,1,1s1-.448,1-1Zm-6-.5V7.5c0-.829-.672-1.5-1.5-1.5s-1.5,.671-1.5,1.5v9c0,.829,.672,1.5,1.5,1.5s1.5-.671,1.5-1.5Zm-7,4.5c0-.552-.447-1-1-1h-2c-1.103,0-2-.897-2-2v-2c0-.552-.447-1-1-1s-1,.448-1,1v2c0,2.206,1.794,4,4,4h2c.553,0,1-.448,1-1Zm17-3v-2c0-.552-.447-1-1-1s-1,.448-1,1v2c0,1.103-.897,2-2,2h-2c-.553,0-1,.448-1,1s.447,1,1,1h2c2.206,0,4-1.794,4-4Zm0-10v-2c0-2.206-1.794-4-4-4h-2c-.553,0-1,.448-1,1s.447,1,1,1h2c1.103,0,2,.897,2,2v2c0,.552,.447,1,1,1s1-.448,1-1Zm-22,0v-2c0-1.103,.897-2,2-2h2c.553,0,1-.448,1-1s-.447-1-1-1h-2C1.794,2,0,3.794,0,6v2c0,.552,.447,1,1,1s1-.448,1-1Zm13.5,10h0c-.276,0-.5-.224-.5-.5V6.5c0-.276,.224-.5,.5-.5h0c.276,0,.5,.224,.5,.5v11c0,.276-.224,.5-.5,.5Z" />
                </svg>
                <span class="font-medium">{{ __('Generate Barcode') }}</span>
            </x-app.button.button>
        </div>

        <!-- Table -->
        <table id="data-table" class="text-sm rounded-lg overflow-hidden" style="width: 100%;">
            <thead>
                <tr>
                    <th>
                        <input type="checkbox" id="parent-row-checkbox" />
                    </th>
                    <th>{{ __('ID') }}</th>
                    <th>{{ __('From ID') }}</th>
                    <th>{{ __('Factory') }}</th>
                    <th>{{ __('Product Serial No') }}</th>
                    <th>{{ __('Start Date') }}</th>
                    <th>{{ __('Due Date') }}</th>
                    <th>{{ __('Days Left') }}</th>
                    <th>{{ __('Priority') }}</th>
                    <th>{{ __('Material Status') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Progress') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <x-app.modal.delete-modal />
@endsection

@push('scripts')
    <script>
        // Datatable
        var dt = new DataTable('#data-table', {
            dom: 'rtip',
            pagingType: 'numbers',
            pageLength: 10,
            processing: true,
            serverSide: true,
            order: [],
            columns: [{
                    data: 'checkbox'
                },
                {
                    data: 'sku'
                },
                {
                    data: 'old_production_sku'
                },
                {
                    data: 'factory'
                },
                {
                    data: 'product_serial_no'
                },
                {
                    data: 'start_date'
                },
                {
                    data: 'due_date'
                },
                {
                    data: 'days_left'
                },
                {
                    data: 'priority'
                },
                {
                    data: 'request_status'
                },
                {
                    data: 'status'
                },
                {
                    data: 'progress'
                },
                {
                    data: 'action'
                },
            ],
            columnDefs: [{
                    "width": "0%",
                    "targets": 0,
                    orderable: false,
                    render: function(data, type, row) {
                        return `<input type="checkbox" class="row-checkbox" data-production-id="${row.id}" />`
                    }
                },
                {
                    // "width": "20%",
                    "targets": 1,
                    render: function(data, type, row) {
                        return data
                    }
                },
                {
                    // "width": "20%",
                    "targets": 2,
                    orderable: false,
                    render: function(data, type, row) {
                        return data
                    }
                },
                {
                    // "width": "10%",
                    "targets": 3,
                    orderable: false,
                    render: function(data, type, row) {
                        return data
                    }
                },
                {
                    // "width": "20%",
                    "targets": 4,
                    orderable: false,
                    render: function(data, type, row) {
                        return data
                    }
                },
                {
                    // "width": "20%",
                    "targets": 5,
                    render: function(data, type, row) {
                        return data
                    }
                },
                {
                    // "width": "20%",
                    "targets": 6,
                    render: function(data, type, row) {
                        return data
                    }
                },
                {
                    // "width": "10%",
                    "targets": 7,
                    orderable: false,
                    render: function(data, type, row) {
                        return data
                    }
                },
                {
                    // "width": "10%",
                    "targets": 8,
                    orderable: false,
                    render: function(data, type, row) {
                        return data == null ? '-' : data.name
                    }
                },
                {
                    // "width": '10%',
                    "targets": 9,
                    orderable: false,
                    render: function(data, type, row) {
                        switch (data) {
                            case 1:
                                return "{!! __('In Progress') !!}"
                            case 2:
                                return "{!! __('Completed') !!}"
                            default:
                                return '-'
                        }
                    }
                },
                {
                    // "width": '10%',
                    "targets": 10,
                    orderable: false,
                    render: function(data, type, row) {
                        switch (data) {
                            case 1:
                                return "{!! __('New') !!}"
                            case 2:
                                return "{!! __('Doing') !!}"
                            case 3:
                                return "{!! __('Completed') !!}"
                            case 4:
                                return "{!! __('Transferred') !!}"
                            case 5:
                                return "{!! __('Modified') !!}"
                            case 6:
                                return "{!! __('Pending Approval') !!}"
                        }
                    }
                },
                {
                    // "width": "10%",
                    "targets": 11,
                    orderable: false,
                    render: function(data, type, row) {
                        return `<span class="text-lg font-semibold">${data}%</span>`
                    }
                },
                {
                    // "width": "5%",
                    "targets": 12,
                    "orderable": false,
                    render: function(data, type, row) {
                        if (row.status == 5) {
                            return `<div class="flex items-center justify-end gap-x-2 px-2">
                                <a href="{{ config('app.url') }}/production/view/${row.id}" class="rounded-full p-2 bg-green-200 inline-block" title="{!! __('View') !!}">
                                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512" height="512"><path d="M23.271,9.419C21.72,6.893,18.192,2.655,12,2.655S2.28,6.893.729,9.419a4.908,4.908,0,0,0,0,5.162C2.28,17.107,5.808,21.345,12,21.345s9.72-4.238,11.271-6.764A4.908,4.908,0,0,0,23.271,9.419Zm-1.705,4.115C20.234,15.7,17.219,19.345,12,19.345S3.766,15.7,2.434,13.534a2.918,2.918,0,0,1,0-3.068C3.766,8.3,6.781,4.655,12,4.655s8.234,3.641,9.566,5.811A2.918,2.918,0,0,1,21.566,13.534Z"/><path d="M12,7a5,5,0,1,0,5,5A5.006,5.006,0,0,0,12,7Zm0,8a3,3,0,1,1,3-3A3,3,0,0,1,12,15Z"/></svg>
                                </a>
                            </div>`
                        }

                        return `<div class="flex items-center justify-end gap-x-2 px-2">
                            ${
                                row.status == 4 ?
                                `
                                                                                                                                                            ${
                                                                                                                                                                row.can_duplicate ? `
                                            <a href="{{ config('app.url') }}/production/create?id=${row.id}" class="rounded-full p-2 bg-yellow-200 inline-block" title="{!! __('Duplicate') !!}">
                                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="m19,0h-6c-2.757,0-5,2.243-5,5v6c0,2.757,2.243,5,5,5h6c2.757,0,5-2.243,5-5v-6c0-2.757-2.243-5-5-5Zm3,11c0,1.654-1.346,3-3,3h-6c-1.654,0-3-1.346-3-3v-6c0-1.654,1.346-3,3-3h6c1.654,0,3,1.346,3,3v6Zm-6,8c0,2.757-2.243,5-5,5h-6c-2.757,0-5-2.243-5-5v-6c0-2.757,2.243-5,5-5,.553,0,1,.448,1,1s-.447,1-1,1c-1.654,0-3,1.346-3,3v6c0,1.654,1.346,3,3,3h6c1.654,0,3-1.346,3-3,0-.552.447-1,1-1s1,.448,1,1Z"/></svg>
                                            </a>`: '' 
                                                                                                                                                            }
                                                                                                                                                            ${
                                                                                                                                                                row.can_view ? `
                                            <a href="{{ config('app.url') }}/production/view/${row.id}" class="rounded-full p-2 bg-green-200 inline-block" title="{!! __('View') !!}">
                                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512" height="512"><path d="M23.271,9.419C21.72,6.893,18.192,2.655,12,2.655S2.28,6.893.729,9.419a4.908,4.908,0,0,0,0,5.162C2.28,17.107,5.808,21.345,12,21.345s9.72-4.238,11.271-6.764A4.908,4.908,0,0,0,23.271,9.419Zm-1.705,4.115C20.234,15.7,17.219,19.345,12,19.345S3.766,15.7,2.434,13.534a2.918,2.918,0,0,1,0-3.068C3.766,8.3,6.781,4.655,12,4.655s8.234,3.641,9.566,5.811A2.918,2.918,0,0,1,21.566,13.534Z"/><path d="M12,7a5,5,0,1,0,5,5A5.006,5.006,0,0,0,12,7Zm0,8a3,3,0,1,1,3-3A3,3,0,0,1,12,15Z"/></svg>
                                            </a>`:''
                                                                                                                                                            }
                                                                                                                                                                                        
                                                                                                                                                ` :

                                `
                                                                                                                                                                                        ${
                                                                                                                                                                                            row.progress < 100 ? `` : `
                                        <a href="{{ config('app.url') }}/production/create?id=${row.id}&is_modify=true" class="rounded-full p-2 bg-purple-200 inline-block" title="{!! __('Modify Product Code') !!}">
                                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24">
                                                <path d="m3.688,24c-.032,0-.063,0-.095,0-1.022-.027-1.963-.462-2.649-1.224-1.269-1.409-1.157-3.784.244-5.185l5.868-5.867c.253-.254.344-.631.241-1.009-.358-1.318-.393-2.676-.102-4.036C7.903,3.364,10.626.735,13.972.137c1.006-.18,2.015-.184,3.002-.007.731.129,1.299.625,1.52,1.325.251.799-.003,1.681-.682,2.359l-2.247,2.217c-.658.658-.758,1.69-.222,2.345.308.378.742.598,1.222.622.472.02.936-.155,1.271-.489l2.58-2.55c.539-.539,1.332-.735,2.07-.501.723.227,1.254.828,1.385,1.567h0c.175.987.172,1.998-.007,3.003-.6,3.347-3.229,6.07-6.544,6.777-1.363.291-2.721.256-4.036-.103-.377-.104-.754-.012-1.008.241l-5.976,5.975c-.69.69-1.637,1.081-2.612,1.081ZM15.61,1.993c-.422,0-.854.035-1.286.112-2.554.457-4.634,2.463-5.174,4.991-.224,1.045-.198,2.086.076,3.093.29,1.062,0,2.191-.756,2.948l-5.868,5.867c-.65.65-.732,1.81-.171,2.433.315.35.747.55,1.215.562.461.019.909-.163,1.241-.494l5.975-5.975c.755-.755,1.885-1.047,2.948-.757,1.004.274,2.045.3,3.093.076,2.528-.539,4.534-2.618,4.992-5.174.138-.772.14-1.547.006-2.301v-.007s-2.655,2.559-2.655,2.559c-.729.729-1.744,1.136-2.781,1.068-1.036-.052-2.009-.545-2.669-1.353-1.179-1.439-1.021-3.649.361-5.03l2.247-2.217c.179-.18.191-.314.184-.341-.315-.039-.643-.062-.976-.062Z"/>
                                            </svg>
                                        </a>`
                                                                                                                                                                                        }
                                                                                                                                                                                ${
                                                                                                                                                                                row.can_duplicate ? `
                                            <a href="{{ config('app.url') }}/production/create?id=${row.id}" class="rounded-full p-2 bg-yellow-200 inline-block" title="{!! __('Duplicate') !!}">
                                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="m19,0h-6c-2.757,0-5,2.243-5,5v6c0,2.757,2.243,5,5,5h6c2.757,0,5-2.243,5-5v-6c0-2.757-2.243-5-5-5Zm3,11c0,1.654-1.346,3-3,3h-6c-1.654,0-3-1.346-3-3v-6c0-1.654,1.346-3,3-3h6c1.654,0,3,1.346,3,3v6Zm-6,8c0,2.757-2.243,5-5,5h-6c-2.757,0-5-2.243-5-5v-6c0-2.757,2.243-5,5-5,.553,0,1,.448,1,1s-.447,1-1,1c-1.654,0-3,1.346-3,3v6c0,1.654,1.346,3,3,3h6c1.654,0,3-1.346,3-3,0-.552.447-1,1-1s1,.448,1,1Z"/></svg>
                                            </a>`:'' 
                                                                                                                                                                                }
                                                                                                                                                                                ${
                                                                                                                                                                                row.can_view ? `<a href="{{ config('app.url') }}/production/view/${row.id}" class="rounded-full p-2 bg-green-200 inline-block" title="{!! __('View') !!}">
                                                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512" height="512"><path d="M23.271,9.419C21.72,6.893,18.192,2.655,12,2.655S2.28,6.893.729,9.419a4.908,4.908,0,0,0,0,5.162C2.28,17.107,5.808,21.345,12,21.345s9.72-4.238,11.271-6.764A4.908,4.908,0,0,0,23.271,9.419Zm-1.705,4.115C20.234,15.7,17.219,19.345,12,19.345S3.766,15.7,2.434,13.534a2.918,2.918,0,0,1,0-3.068C3.766,8.3,6.781,4.655,12,4.655s8.234,3.641,9.566,5.811A2.918,2.918,0,0,1,21.566,13.534Z"/><path d="M12,7a5,5,0,1,0,5,5A5.006,5.006,0,0,0,12,7Zm0,8a3,3,0,1,1,3-3A3,3,0,0,1,12,15Z"/></svg>
                                                </a>`:''
                                                                                                                                                                                }
                                                                                                                                                                                        
                                                                                                                                                                                        
                                                                                                                                                                                        ${
                                                                                                                                                                                            row.can_edit ? `
                                    <a href="{{ config('app.url') }}/production/edit/${row.id}" class="rounded-full p-2 bg-blue-200 inline-block" title="Edit" title="{!! __('Edit') !!}">
                                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="m18.813,10c.309,0,.601-.143.79-.387s.255-.562.179-.861c-.311-1.217-.945-2.329-1.833-3.217l-3.485-3.485c-1.322-1.322-3.08-2.05-4.95-2.05h-4.515C2.243,0,0,2.243,0,5v14c0,2.757,2.243,5,5,5h3c.552,0,1-.448,1-1s-.448-1-1-1h-3c-1.654,0-3-1.346-3-3V5c0-1.654,1.346-3,3-3h4.515c.163,0,.325.008.485.023v4.977c0,1.654,1.346,3,3,3h5.813Zm-6.813-3V2.659c.379.218.732.488,1.05.806l3.485,3.485c.314.314.583.668.803,1.05h-4.338c-.551,0-1-.449-1-1Zm11.122,4.879c-1.134-1.134-3.11-1.134-4.243,0l-6.707,6.707c-.755.755-1.172,1.76-1.172,2.829v1.586c0,.552.448,1,1,1h1.586c1.069,0,2.073-.417,2.828-1.172l6.707-6.707c.567-.567.879-1.32.879-2.122s-.312-1.555-.878-2.121Zm-1.415,2.828l-6.708,6.707c-.377.378-.879.586-1.414.586h-.586v-.586c0-.534.208-1.036.586-1.414l6.708-6.707c.377-.378,1.036-.378,1.414,0,.189.188.293.439.293.707s-.104.518-.293.707Z"/></svg>
                                    </a>` : ''
                                                                                                                                                                                        }
                                                                                                                                                                                        ${
                                                                                                                                                                                            row.can_delete ? `
                                    <button class="rounded-full p-2 bg-red-200 inline-block delete-btns" data-id="${row.id}" title="Delete" title="{!! __('Delete') !!}">
                                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512" height="512"><path d="M21,4H17.9A5.009,5.009,0,0,0,13,0H11A5.009,5.009,0,0,0,6.1,4H3A1,1,0,0,0,3,6H4V19a5.006,5.006,0,0,0,5,5h6a5.006,5.006,0,0,0,5-5V6h1a1,1,0,0,0,0-2ZM11,2h2a3.006,3.006,0,0,1,2.829,2H8.171A3.006,3.006,0,0,1,11,2Zm7,17a3,3,0,0,1-3,3H9a3,3,0,0,1-3-3V6H18Z"/><path d="M10,18a1,1,0,0,0,1-1V11a1,1,0,0,0-2,0v6A1,1,0,0,0,10,18Z"/><path d="M14,18a1,1,0,0,0,1-1V11a1,1,0,0,0-2,0v6A1,1,0,0,0,14,18Z"/></svg>
                                    </button>` : ''
                                                                                                                                                                                        }
                                                                                                                                                                                    `
                            }
                       </div>`
                    }
                },
            ],
            ajax: {
                data: function() {
                    var info = $('#data-table').DataTable().page.info();
                    var url = "{{ route('production.get_data') }}"

                    url = `${url}?page=${ info.page + 1 }`
                    $('#data-table').DataTable().ajax.url(url);
                },
            },
        });
        $('#filter_search').on('keyup', function() {
            dt.search($(this).val()).draw()
        })

        $('#data-table').on('click', '.delete-btns', function() {
            id = $(this).data('id')

            $('#delete-modal #yes-btn').attr('href', `{{ config('app.url') }}/production/delete/${id}`)
            $('#delete-modal').addClass('show-modal')
        })
        $('#export-btn').on('click', function() {
            window.location.href = '{{ route('production.export') }}'
        })
        $('.summary-cards').on('click', function() {
            let type = $(this).data('type')

            let url = '{{ route('production.index') }}'
            if (type != 'all') {
                url = `${url}?type=${type}`
            }
            window.location.href = url
        })
        // Start task btn
        $('#start-task-btn').on('click', function() {
            var productionIds = []
            $('body').find('.row-checkbox:checked').each(function(i, obj) {
                productionIds.push($(this).data('production-id'))
            })
            if (productionIds.length <= 0) return

            let url = '{{ route('production.to_in_progress') }}'
            url = `${url}?productionIds=${productionIds.join(',')}`

            window.location.href = url
        })
        // Generate barcode btn
        $('#generate-barcode-btn').on('click', function() {
            var productionIds = []
            $('body').find('.row-checkbox:checked').each(function(i, obj) {
                productionIds.push($(this).data('production-id'))
            })
            if (productionIds.length <= 0) return

            let url = '{{ route('production.generate_barcode') }}'
            url = `${url}?productionIds=${productionIds.join(',')}`

            window.location.href = url
        })
        // Row Checkbox
        $('#parent-row-checkbox').on('change', function() {
            let isChecked = $(this).is(':checked')

            if (isChecked) {
                $('body').find('.row-checkbox').prop('checked', true)
            } else {
                $('body').find('.row-checkbox').prop('checked', false)
            }
        })
    </script>
@endpush
