@extends('layouts.app')
@section('title', 'Raw Material Request')

@vite(['resources/css/jquery.dataTables.min.css'])

@push('styles')
    <style>
        #data-table,
        #cost-table {
            border: solid 1px rgb(209 213 219);
        }

        #data-table thead th,
        #data-table tbody tr td,
        #cost-table thead th,
        #cost-table tbody tr td {
            border-bottom: solid 1px rgb(209 213 219);
        }

        #data-table tbody tr:last-of-type td,
        #cost-table tbody tr:last-of-type td {
            border-bottom: none;
        }
    </style>
@endpush

@section('content')
    <div class="mb-6">
        <x-app.page-title
            url="{{ route('raw_material_request.view', ['rmq' => $rmq->id]) }}">{{ __('View Raw Material Request Logs') }}</x-app.page-title>
    </div>
    @include('components.app.alert.parent')
    <!-- Table -->
    <table id="data-table" class="text-sm rounded-lg overflow-hidden" style="width: 100%;">
        <thead>
            <tr>
                <th>{{ __('No.') }}</th>
                <th>{{ __('Qty') }}</th>
                <th>{{ __('By') }}</th>
                <th>{{ __('Date') }}</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>

    <x-app.modal.raw-material-transfer-modal />
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
                    data: 'no'
                },
                {
                    data: 'qty'
                },
                {
                    data: 'by'
                },
                {
                    data: 'date'
                },
            ],
            columnDefs: [{
                    "width": "0%",
                    "targets": 0,
                    'orderable': false,
                    render: function(data, type, row) {
                        return data
                    }
                },
                {
                    "width": "20%",
                    "targets": 1,
                    'orderable': false,
                    render: function(data, type, row) {
                        return data
                    }
                },
                {
                    "width": "20%",
                    "targets": 2,
                    'orderable': false,
                    render: function(data, type, row) {
                        return data
                    }
                },
                {
                    "width": "20%",
                    "targets": 3,
                    'orderable': false,
                    render: function(data, type, row) {
                        return data
                    }
                },
            ],
            ajax: {
                data: function() {
                    var info = $('#data-table').DataTable().page.info();
                    var url = "{{ route('raw_material_request.view_logs_get_data') }}"

                    url = `${url}?page=${ info.page + 1 }`
                    $('#data-table').DataTable().ajax.url(url);
                },
            },
        });
    </script>
@endpush
