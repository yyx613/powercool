@extends('layouts.app')
@section('title', 'Customize Products')

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
    <div class="mb-6">
        <x-app.page-title>{{ __('Customize Products') }}</x-app.page-title>
    </div>

    @include('components.app.alert.parent')

    <div>
        <!-- Search -->
        <div class="flex gap-x-4 max-w-xs w-full mb-4">
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
                    <th>{{ __('SKU') }}</th>
                    <th>{{ __('Production SKU') }}</th>
                    <th>{{ __('Dimensions') }}</th>
                    <th>{{ __('Weight') }}</th>
                    <th>{{ __('Capacity') }}</th>
                    <th>{{ __('Refrigerant') }}</th>
                    <th>{{ __('Power Input') }}</th>
                    <th>{{ __('Power Consumption') }}</th>
                    <th>{{ __('Voltage/Frequency') }}</th>
                    <th>{{ __('Standard Features') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
@endsection

@push('scripts')
    <script>
        INIT_LOAD = true;
        DEFAULT_PAGE = @json($default_page ?? null);
        DEFAULT_SEARCH = @json($default_search ?? null);

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
                    data: 'sku'
                },
                {
                    data: 'production_sku'
                },
                {
                    data: 'dimensions'
                },
                {
                    data: 'weight'
                },
                {
                    data: 'capacity'
                },
                {
                    data: 'refrigerant'
                },
                {
                    data: 'power_input'
                },
                {
                    data: 'power_consumption'
                },
                {
                    data: 'voltage_frequency'
                },
                {
                    data: 'standard_features'
                },
                {
                    data: 'action'
                },
            ],
            columnDefs: [{
                    "targets": 0,
                    render: function(data, type, row) {
                        return data
                    }
                },
                {
                    "targets": 1,
                    orderable: false,
                    render: function(data, type, row) {
                        return data ?? '-'
                    }
                },
                {
                    "targets": 2,
                    orderable: false,
                    render: function(data, type, row) {
                        return data ?? '-'
                    }
                },
                {
                    "targets": 3,
                    render: function(data, type, row) {
                        return data ?? '-'
                    }
                },
                {
                    "targets": 4,
                    orderable: false,
                    render: function(data, type, row) {
                        return data ?? '-'
                    }
                },
                {
                    "targets": 5,
                    orderable: false,
                    render: function(data, type, row) {
                        return data ?? '-'
                    }
                },
                {
                    "targets": 6,
                    orderable: false,
                    render: function(data, type, row) {
                        return data ?? '-'
                    }
                },
                {
                    "targets": 7,
                    orderable: false,
                    render: function(data, type, row) {
                        return data ?? '-'
                    }
                },
                {
                    "targets": 8,
                    orderable: false,
                    render: function(data, type, row) {
                        return data ?? '-'
                    }
                },
                {
                    "targets": 9,
                    orderable: false,
                    render: function(data, type, row) {
                        return data ?? '-'
                    }
                },
                {
                    "targets": 10,
                    "orderable": false,
                    render: function(data, type, row) {
                        return `<div class="flex items-center justify-end gap-x-2 px-2"> 
                            <a href="{{ config('app.url') }}/product/create?cp=${row.id}" class="rounded-full p-2 bg-yellow-200 inline-block" title="{!! __('Setup Finish Good') !!}">
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24">
                                    <path d="m23.915,15.893l-.576-1.915-11.733,3.526L7.226,2.482c-.216-.772-.72-1.413-1.418-1.806-.699-.394-1.507-.489-2.325-.26L.069,1.551l.631,1.898,3.368-1.12c.256-.074.526-.04.76.091.233.131.4.345.476.612l4.076,13.98c-1.873.065-3.379,1.6-3.379,3.488,0,1.93,1.57,3.5,3.5,3.5s3.5-1.57,3.5-3.5c0-.443-.091-.863-.242-1.254l11.157-3.353Zm-14.415,6.107c-.827,0-1.5-.673-1.5-1.5s.673-1.5,1.5-1.5,1.5.673,1.5,1.5-.673,1.5-1.5,1.5Zm3.566-7.04l8.644-2.619L17.972.045l-8.642,2.619,3.737,12.296Zm6.147-3.953l-4.815,1.459-.996-3.278,4.815-1.459.996,3.278Zm-2.574-8.469l.996,3.278-4.815,1.459-.996-3.278,4.815-1.459Z"/>
                                </svg>
                            </a>
                            ${
                                row.can_edit ? `
                                    <a href="{{ config('app.url') }}/customize/edit/${row.id}" class="rounded-full p-2 bg-blue-200 inline-block" title="{!! __('Edit') !!}">
                                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="m18.813,10c.309,0,.601-.143.79-.387s.255-.562.179-.861c-.311-1.217-.945-2.329-1.833-3.217l-3.485-3.485c-1.322-1.322-3.08-2.05-4.95-2.05h-4.515C2.243,0,0,2.243,0,5v14c0,2.757,2.243,5,5,5h3c.552,0,1-.448,1-1s-.448-1-1-1h-3c-1.654,0-3-1.346-3-3V5c0-1.654,1.346-3,3-3h4.515c.163,0,.325.008.485.023v4.977c0,1.654,1.346,3,3,3h5.813Zm-6.813-3V2.659c.379.218.732.488,1.05.806l3.485,3.485c.314.314.583.668.803,1.05h-4.338c-.551,0-1-.449-1-1Zm11.122,4.879c-1.134-1.134-3.11-1.134-4.243,0l-6.707,6.707c-.755.755-1.172,1.76-1.172,2.829v1.586c0,.552.448,1,1,1h1.586c1.069,0,2.073-.417,2.828-1.172l6.707-6.707c.567-.567.879-1.32.879-2.122s-.312-1.555-.878-2.121Zm-1.415,2.828l-6.708,6.707c-.377.378-.879.586-1.414.586h-.586v-.586c0-.534.208-1.036.586-1.414l6.708-6.707c.377-.378,1.036-.378,1.414,0,.189.188.293.439.293.707s-.104.518-.293.707Z"/></svg>
                                    </a>
                                ` : ''
                            }
                        </div>`
                    }
                },
            ],
            ajax: {
                data: function() {
                    var info = $('#data-table').DataTable().page.info();
                    var url = "{{ route('customize.get_data') }}"

                    url = `${url}?page=${INIT_LOAD == true && DEFAULT_PAGE != null ? DEFAULT_PAGE : info.page + 1}`
                    $('#data-table').DataTable().ajax.url(url);

                    INIT_LOAD = false
                },
            },
        });

        // Restore default search
        if (DEFAULT_SEARCH != null) {
            $('#filter_search').val(DEFAULT_SEARCH)
        }

        // Search with debounce
        $('#filter_search').on('keyup', $.debounce(DEBOUNCE_DURATION, function() {
            dt.search($(this).val()).draw()
        }))
    </script>
@endpush
