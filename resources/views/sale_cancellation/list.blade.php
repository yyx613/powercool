@extends('layouts.app')
@section('title', 'Sale Cancellation')

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
        <x-app.page-title class="mb-4 md:mb-0">{{ __('Sale Cancellation') }}</x-app.page-title>
    </div>
    @include('components.app.alert.parent')
    <div>
        <!-- Filters -->
        <div class="flex max-w-xs w-full mb-4">
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
        </div>

        <!-- Table -->
        <table id="data-table" class="text-sm rounded-lg overflow-hidden" style="width: 100%;">
            <thead>
                <tr>
                    <th>{{ __('Salesperson') }}</th>
                    <th>{{ __('Product') }}</th>
                    <th>{{ __('Qty') }}</th>
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
            columns: [{
                    data: 'saleperson'
                },
                {
                    data: 'product_sku'
                },
                {
                    data: 'qty'
                },
                {
                    data: 'action'
                },
            ],
            columnDefs: [{
                    "width": "20%",
                    "targets": 0,
                    render: function(data, type, row) {
                        return data
                    }
                },
                {
                    "width": "20%",
                    "targets": 1,
                    render: function(data, type, row) {
                        return `${row.product_sku} - ${row.product_name}`
                    }
                },
                {
                    "width": "20%",
                    "targets": 2,
                    render: function(data, type, row) {
                        return data
                    }
                },
                {
                    "width": "0%",
                    "targets": 3,
                    orderable: false,
                    render: function(data, type, row) {
                        const viewUrl = "{{ route('sale_cancellation.view', ['saleperson_id' => ':saleperson_id', 'product_id' => ':product_id']) }}"
                            .replace(':saleperson_id', row.saleperson_id)
                            .replace(':product_id', row.product_id);

                        return `
                            <div class="flex gap-2">
                                <a href="${viewUrl}" class="rounded-full p-2 bg-blue-200 inline-block" title="{!! __('View') !!}">
                                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512" height="512"><path d="M23.271,9.419C21.72,6.893,18.192,2.655,12,2.655S2.28,6.893.729,9.419a4.908,4.908,0,0,0,0,5.162C2.28,17.107,5.808,21.345,12,21.345s9.72-4.238,11.271-6.764A4.908,4.908,0,0,0,23.271,9.419Zm-1.705,4.115C20.234,15.7,17.219,19.345,12,19.345S3.766,15.7,2.434,13.534a2.918,2.918,0,0,1,0-3.068C3.766,8.3,6.781,4.655,12,4.655s8.234,3.641,9.566,5.811A2.918,2.918,0,0,1,21.566,13.534Z"/><path d="M12,7a5,5,0,1,0,5,5A5.006,5.006,0,0,0,12,7Zm0,8a3,3,0,1,1,3-3A3,3,0,0,1,12,15Z"/></svg>
                                </a>
                            </div>
                        `;
                    }
                },
            ],
            ajax: {
                data: function() {
                    var info = $('#data-table').DataTable().page.info();
                    var url = "{{ route('sale_cancellation.get_data') }}"

                    url =
                        `${url}?page=${ INIT_LOAD == true && DEFAULT_PAGE != null ? DEFAULT_PAGE : info.page + 1 }`
                    $('#data-table').DataTable().ajax.url(url);

                    INIT_LOAD = false
                },
            },
        });
        $('#filter_search').on('keyup', function() {
            dt.search($(this).val()).draw()
        })
    </script>
@endpush
