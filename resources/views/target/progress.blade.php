@extends('layouts.app')
@section('title', 'Target')

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
        <x-app.page-title url="{{ route('target.index') }}">{{ __('View Target Progress') }}</x-app.page-title>
    </div>
    @include('components.app.alert.parent')
    <div>
        <!-- Table -->
        <table id="data-table" class="text-sm rounded-lg overflow-hidden" style="width: 100%;">
            <thead>
                <tr>
                    <th>{{ __('Invoice') }}</th>
                    <th>{{ __('Amount') }}</th>
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
                { data: 'doc_no' },
                { data: 'total' },
            ],
            columnDefs: [
                {
                    "width": "20%",
                    "targets": 0,
                    orderable: false,
                    render: function(data, type, row) {
                        return data
                    }
                },
                {
                    "width": "20%",
                    "targets": 1,
                    orderable: false,
                    render: function(data, type, row) {
                        return data
                    }
                },
            ],
            ajax: {
                data: function(){
                    var info = $('#data-table').DataTable().page.info();
                    var url = `{{ config('app.url') }}/target/get_data_view_progress/{{ $target->id }}`;

                    url = `${url}?page=${ INIT_LOAD == true && DEFAULT_PAGE != null ? DEFAULT_PAGE : info.page + 1 }`
                    $('#data-table').DataTable().ajax.url(url);

                    INIT_LOAD = false
                },
            },
        });
        $('#filter_search').on('keyup', function() {
            dt.search($(this).val()).draw()
        })

        $('#data-table').on('click', '.delete-btns', function() {
            id = $(this).data('id')

            $('#delete-modal #yes-btn').attr('href', `{{ config('app.url') }}/quotation/delete/${id}`)
            $('#delete-modal').addClass('show-modal')
        })
    </script>
@endpush
