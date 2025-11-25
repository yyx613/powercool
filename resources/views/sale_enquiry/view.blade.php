@extends('layouts.app')
@section('title', 'Sale Enquiry')

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
        <x-app.page-title url="{{ route('sale_enquiry.index') }}">{{ __('Sale Enquiry') }}: {{ $enquiry->sku }}</x-app.page-title>
    </div>
    @include('components.app.alert.parent')
    <div>
        <!-- Table -->
        <table id="data-table" class="text-sm rounded-lg overflow-hidden" style="width: 100%;">
            <thead>
                <tr>
                    <th>{{ __('Sale SKU') }}</th>
                    <th>{{ __('Customer') }}</th>
                    <th>{{ __('Payment Status') }}</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
@endsection

@push('scripts')
    <script>
        ENQUIRY_ID = @json($enquiry->id);

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
                { data: 'customer' },
                { data: 'payment_status' },
            ],
            columnDefs: [
                {
                    "width": "30%",
                    "targets": 0,
                    render: function(data, type, row) {
                        return data
                    }
                },
                {
                    "width": "40%",
                    "targets": 1,
                    orderable: false,
                    render: function(data, type, row) {
                        return data ?? '-'
                    }
                },
                {
                    "width": "30%",
                    "targets": 2,
                    orderable: false,
                    render: function(data, type, row) {
                        switch (data) {
                            case 1:
                                return `<span class="px-2 py-1 bg-red-100 text-red-800 rounded text-xs font-semibold">{!! __('Unpaid') !!}</span>`
                            case 2:
                                return `<span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded text-xs font-semibold">{!! __('Partially Paid') !!}</span>`
                            case 3:
                                return `<span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs font-semibold">{!! __('Paid') !!}</span>`
                            default:
                                return '-'
                        }
                    }
                },
            ],
            ajax: {
                data: function(){
                    var info = $('#data-table').DataTable().page.info();
                    var url = "{{ route('sale_enquiry.view_get_data') }}"

                    url = `${url}?enquiry_id=${ENQUIRY_ID}&page=${info.page + 1}`
                    $('#data-table').DataTable().ajax.url(url);
                },
            },
        });
    </script>
@endpush
