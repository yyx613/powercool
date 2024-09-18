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
    <div class="mb-6 flex justify-between items-center">
        <x-app.page-title>{{ $for_role == 'driver' ? 'Driver' : ($for_role == 'technician' ? 'Technician' : 'Sale') }} Task</x-app.page-title>
        @can('task.create')
        <a href="{{ $for_role == 'driver' ? route('task.driver.create') : ($for_role == 'technician' ? route('task.technician.create') : route('task.sale.create')) }}" class="bg-yellow-400 shadow rounded-md py-2 px-4 flex items-center gap-x-2">
            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Capa_1" x="0px" y="0px" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve" width="512" height="512">
                <path d="M480,224H288V32c0-17.673-14.327-32-32-32s-32,14.327-32,32v192H32c-17.673,0-32,14.327-32,32s14.327,32,32,32h192v192   c0,17.673,14.327,32,32,32s32-14.327,32-32V288h192c17.673,0,32-14.327,32-32S497.673,224,480,224z"/>
            </svg>
            New
        </a>
        @endcan
    </div>
    @include('components.app.alert.parent')
    <!-- Summary -->
    <div class="mb-6 flex gap-x-3">
        <x-app.task-summary-card value="{{ $due_today }}" label="Due Today" class="flex-1 bg-indigo-400" />
        <x-app.task-summary-card value="{{ $to_do }}" label="New" class="flex-1 bg-red-400" />
        <x-app.task-summary-card value="{{ $doing }}" label="Doing" class="flex-1 bg-orange-400" />
        <x-app.task-summary-card value="{{ $in_review }}" label="In Review" class="flex-1 bg-blue-400" />
        <x-app.task-summary-card value="{{ $completed }}" label="Done" class="flex-1 bg-teal-400" />
    </div>
    <div>
        <!-- Filters -->
        <div class="flex max-w-xs w-full mb-4">
            <div class="flex-1">
                <x-app.input.input name="filter_search" id="filter_search" class="flex items-center" placeholder="Search">
                    <div class="rounded-md border border-transparent p-1 ml-1">
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24"><path d="M23.707,22.293l-5.969-5.969a10.016,10.016,0,1,0-1.414,1.414l5.969,5.969a1,1,0,0,0,1.414-1.414ZM10,18a8,8,0,1,1,8-8A8.009,8.009,0,0,1,10,18Z"/></svg>
                    </div>
                </x-app.input.input>
            </div>
        </div>

        <!-- Table -->
        <table id="data-table" class="text-sm rounded-lg overflow-hidden" style="width: 100%;">
            <thead>
                <tr>
                    <th>Task ID</th>
                    <th>Task Name</th>
                    <th>Due Date</th>
                    <th>Amount to Collect</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <x-app.modal.delete-modal/>
@endsection

@push('scripts')
    <script>
        FOR_ROLE = @json($for_role ?? null);

        $(document).ready(function(){
            dt.draw()
        })

        // Datatable
        var dt = new DataTable('#data-table', {
            dom: 'rtip',
            pagingType: 'numbers',
            pageLength: 10,
            processing: true,
            serverSide: true,
            order: [],
            columns: [
                { data: 'sku' },
                { data: 'name' },
                { data: 'due_date' },
                { data: 'amount_to_collect' },
                { data: 'status' },
                { data: 'action' },
            ],
            columnDefs: [
                {
                    "width": "10%",
                    "targets": 0,
                    render: function(data, type, row) {
                        return data
                    }
                },
                {
                    "width": "10%",
                    "targets": 1,
                    render: function(data, type, row) {
                        return data
                    }
                },
                {
                    "width": "10%",
                    "targets": 2,
                    render: function(data, type, row) {
                        return data
                    }
                },
                {
                    "width": "10%",
                    "targets": 3,
                    render: function(data, type, row) {
                        return data
                    }
                },
                {
                    "width": '10%',
                    "targets": 4,
                    orderable: false,
                    render: function(data, type, row) {
                        switch (data) {
                            case 1:
                                return '<span class="text-red-500">To Do</span>'
                            case 2:
                                return '<span class="text-yellow-500">Doing</span>'
                            case 3:
                                return '<span class="text-blue-500">In Review</span>'
                            case 4:
                                return '<span class="text-green-500">Completed</span>'
                        }
                    }
                },
                {
                    "width": "5%",
                    "targets": 5,
                    orderable: false,
                    render: function (data, type, row) {
                       return  `<div class="flex items-center justify-end gap-x-2 px-2">
                            <a href="{{ config('app.url') }}/task/${FOR_ROLE == 'driver' ? 'driver' : FOR_ROLE == 'technician' ? 'technician' : 'sale'}/view/${row.id}" class="rounded-full p-2 bg-green-200 inline-block">
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512" height="512"><path d="M23.271,9.419C21.72,6.893,18.192,2.655,12,2.655S2.28,6.893.729,9.419a4.908,4.908,0,0,0,0,5.162C2.28,17.107,5.808,21.345,12,21.345s9.72-4.238,11.271-6.764A4.908,4.908,0,0,0,23.271,9.419Zm-1.705,4.115C20.234,15.7,17.219,19.345,12,19.345S3.766,15.7,2.434,13.534a2.918,2.918,0,0,1,0-3.068C3.766,8.3,6.781,4.655,12,4.655s8.234,3.641,9.566,5.811A2.918,2.918,0,0,1,21.566,13.534Z"/><path d="M12,7a5,5,0,1,0,5,5A5.006,5.006,0,0,0,12,7Zm0,8a3,3,0,1,1,3-3A3,3,0,0,1,12,15Z"/></svg>
                            </a>
                            ${
                                row.can_edit ? `
                            <a href="{{ config('app.url') }}/task/${FOR_ROLE == 'driver' ? 'driver' : FOR_ROLE == 'technician' ? 'technician' : 'sale'}/edit/${row.id}" class="rounded-full p-2 bg-blue-200 inline-block">
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="m18.813,10c.309,0,.601-.143.79-.387s.255-.562.179-.861c-.311-1.217-.945-2.329-1.833-3.217l-3.485-3.485c-1.322-1.322-3.08-2.05-4.95-2.05h-4.515C2.243,0,0,2.243,0,5v14c0,2.757,2.243,5,5,5h3c.552,0,1-.448,1-1s-.448-1-1-1h-3c-1.654,0-3-1.346-3-3V5c0-1.654,1.346-3,3-3h4.515c.163,0,.325.008.485.023v4.977c0,1.654,1.346,3,3,3h5.813Zm-6.813-3V2.659c.379.218.732.488,1.05.806l3.485,3.485c.314.314.583.668.803,1.05h-4.338c-.551,0-1-.449-1-1Zm11.122,4.879c-1.134-1.134-3.11-1.134-4.243,0l-6.707,6.707c-.755.755-1.172,1.76-1.172,2.829v1.586c0,.552.448,1,1,1h1.586c1.069,0,2.073-.417,2.828-1.172l6.707-6.707c.567-.567.879-1.32.879-2.122s-.312-1.555-.878-2.121Zm-1.415,2.828l-6.708,6.707c-.377.378-.879.586-1.414.586h-.586v-.586c0-.534.208-1.036.586-1.414l6.708-6.707c.377-.378,1.036-.378,1.414,0,.189.188.293.439.293.707s-.104.518-.293.707Z"/></svg>
                                </a>` : ''
                            }
                            ${
                                row.can_delete ? `
                            <button class="rounded-full p-2 bg-red-200 inline-block delete-btns" data-id="${row.id}">
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512" height="512"><path d="M21,4H17.9A5.009,5.009,0,0,0,13,0H11A5.009,5.009,0,0,0,6.1,4H3A1,1,0,0,0,3,6H4V19a5.006,5.006,0,0,0,5,5h6a5.006,5.006,0,0,0,5-5V6h1a1,1,0,0,0,0-2ZM11,2h2a3.006,3.006,0,0,1,2.829,2H8.171A3.006,3.006,0,0,1,11,2Zm7,17a3,3,0,0,1-3,3H9a3,3,0,0,1-3-3V6H18Z"/><path d="M10,18a1,1,0,0,0,1-1V11a1,1,0,0,0-2,0v6A1,1,0,0,0,10,18Z"/><path d="M14,18a1,1,0,0,0,1-1V11a1,1,0,0,0-2,0v6A1,1,0,0,0,14,18Z"/></svg>
                            </button>` : ''
                            }
                       </div>`
                    }
                },
            ],
            ajax: {
                data: function(){
                    var info = $('#data-table').DataTable().page.info();
                    var url = "{{ route('task.get_data') }}"

                    url = `${url}?page=${ info.page + 1 }&role=${ FOR_ROLE == 'driver' ? 'driver' : (FOR_ROLE == 'technician' ? 'technician' : 'sale') }`
                    $('#data-table').DataTable().ajax.url(url);
                },
            },
        });
        $('#filter_search').on('keyup', function() {
            dt.search($(this).val()).draw()
        })

        $('#data-table').on('click', '.delete-btns', function() {
            id = $(this).data('id')

            $('#delete-modal #yes-btn').attr('href', `{{ config('app.url') }}/task/delete/${id}`)
            $('#delete-modal').addClass('show-modal')
        })
    </script>
@endpush
