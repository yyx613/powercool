@extends('layouts.app')
@section('title', 'Service Form')

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
        <x-app.page-title class="mb-4 md:mb-0">{{ __('Service Form') }}</x-app.page-title>
        <div class="flex gap-x-2">
            @can('service_form.create')
            <a href="{{ route('service_form.create') }}" class="bg-yellow-400 shadow rounded-md py-2 px-4 flex items-center gap-x-2">
                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Capa_1" x="0px" y="0px" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve" width="512" height="512">
                    <path d="M480,224H288V32c0-17.673-14.327-32-32-32s-32,14.327-32,32v192H32c-17.673,0-32,14.327-32,32s14.327,32,32,32h192v192   c0,17.673,14.327,32,32,32s32-14.327,32-32V288h192c17.673,0,32-14.327,32-32S497.673,224,480,224z"/>
                </svg>
                {{ __('New') }}
            </a>
            @endcan
        </div>
    </div>
    @include('components.app.alert.parent')
    <div>
        <!-- Filters -->
        <div class="flex max-w-xs w-full mb-4">
            <div class="flex-1">
                <x-app.input.input name="filter_search" id="filter_search" class="flex items-center" placeholder="{{ __('Search') }}">
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
                    <th>{{ __('SKU') }}</th>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Customer') }}</th>
                    <th>{{ __('Technician') }}</th>
                    <th>{{ __('Created At') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
    <x-app.modal.delete-modal/>
    <x-app.modal.pdf-type-modal/>
@endsection

@push('scripts')
    <script>
        INIT_LOAD = true;
        DEFAULT_PAGE = @json($default_page ?? null);

        // Datatable
        var dt = new DataTable('#data-table', {
            dom: 'rtip',
            pagingType: 'numbers',
            pageLength: 10,
            processing: true,
            serverSide: true,
            order: [],
            displayStart: DEFAULT_PAGE != null ? (DEFAULT_PAGE - 1) * 10 : 0,
            columns: [
                { data: 'sku' },
                { data: 'date' },
                { data: 'customer_name' },
                { data: 'technician' },
                { data: 'created_at' },
                { data: 'action' },
            ],
            columnDefs: [
                {
                    "width": "12%",
                    "targets": 0,
                    orderable: false,
                    render: function(data, type, row) {
                        return data
                    }
                },
                {
                    "width": "12%",
                    "targets": 1,
                    orderable: false,
                    render: function(data, type, row) {
                        return data
                    }
                },
                {
                    "width": "25%",
                    "targets": 2,
                    orderable: false,
                    render: function(data, type, row) {
                        return data
                    }
                },
                {
                    "width": "15%",
                    "targets": 3,
                    orderable: false,
                    render: function(data, type, row) {
                        return data
                    }
                },
                {
                    "width": "12%",
                    "targets": 4,
                    orderable: false,
                    render: function(data, type, row) {
                        return data
                    }
                },
                {
                    "width": "12%",
                    "targets": 5,
                    "orderable": false,
                    render: function (data, type, row) {
                        let html = `<div class="flex items-center justify-end gap-x-2 px-2">`

                        // PDF Button
                        html += `<button type="button" class="rounded-full p-2 bg-yellow-200 pdf-btns" data-id="${row.id}" title="{!! __('PDF') !!}">
                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                        </button>`

                        @can('service_form.edit')
                        html += `<a href="{{ config('app.url') }}/service-form/edit/${row.id}" class="rounded-full p-2 bg-blue-200 inline-block" title="{!! __('Edit') !!}">
                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M22.853,1.148a3.626,3.626,0,0,0-5.124,0L1.465,17.412A4.968,4.968,0,0,0,0,20.947V23a1,1,0,0,0,1,1H3.053a4.966,4.966,0,0,0,3.535-1.464L22.853,6.271A3.626,3.626,0,0,0,22.853,1.148ZM5.174,21.122A3.022,3.022,0,0,1,3.053,22H2V20.947a2.98,2.98,0,0,1,.879-2.121L15.222,6.483l2.3,2.3ZM21.438,4.857,18.932,7.364l-2.3-2.295,2.507-2.507a1.623,1.623,0,1,1,2.295,2.3Z"/></svg>
                        </a>`
                        @endcan

                        @can('service_form.delete')
                        html += `<button type="button" class="rounded-full p-2 bg-red-200 delete-btns" data-id="${row.id}" title="{!! __('Delete') !!}">
                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512" height="512"><path d="M21,4H17.9A5.009,5.009,0,0,0,13,0H11A5.009,5.009,0,0,0,6.1,4H3A1,1,0,0,0,3,6H4V19a5.006,5.006,0,0,0,5,5h6a5.006,5.006,0,0,0,5-5V6h1a1,1,0,0,0,0-2ZM11,2h2a3.006,3.006,0,0,1,2.829,2H8.171A3.006,3.006,0,0,1,11,2Zm7,17a3,3,0,0,1-3,3H9a3,3,0,0,1-3-3V6H18Z"/><path d="M10,18a1,1,0,0,0,1-1V11a1,1,0,0,0-2,0v6A1,1,0,0,0,10,18Z"/><path d="M14,18a1,1,0,0,0,1-1V11a1,1,0,0,0-2,0v6A1,1,0,0,0,14,18Z"/></svg>
                        </button>`
                        @endcan

                        html += `</div>`

                        return html
                    }
                },
            ],
            ajax: {
                data: function(){
                    var info = $('#data-table').DataTable().page.info();
                    var url = "{{ route('service_form.get_data') }}"

                    url = `${url}?page=${ INIT_LOAD == true && DEFAULT_PAGE != null ? DEFAULT_PAGE : info.page + 1 }`
                    $('#data-table').DataTable().ajax.url(url);

                    INIT_LOAD = false
                },
            },
        });

        $('#filter_search').on('keyup', function() {
            dt.search($(this).val()).draw()
        })

        // Delete
        $('#data-table').on('click', '.delete-btns', function() {
            let id = $(this).data('id')
            $('#delete-modal #yes-btn').attr('href', `{{ config('app.url') }}/service-form/delete/${id}`)
            $('#delete-modal').addClass('show-modal')
        })

        // PDF - Open modal
        $('#data-table').on('click', '.pdf-btns', function() {
            let id = $(this).data('id')
            // Update PDF links with the record ID
            $('#pdf-type-modal #pdf-service-form-link').attr('href', `{{ config('app.url') }}/service-form/pdf/${id}`)
            $('#pdf-type-modal #pdf-quotation-link').attr('href', `{{ config('app.url') }}/service-form/quotation-pdf/${id}`)
            $('#pdf-type-modal #pdf-cash-sale-link').attr('href', `{{ config('app.url') }}/service-form/cash-sale-pdf/${id}`)
            $('#pdf-type-modal #pdf-invoice-link').attr('href', `{{ config('app.url') }}/service-form/invoice-pdf/${id}`)
            $('#pdf-type-modal').addClass('show-modal')
        })
    </script>
@endpush
