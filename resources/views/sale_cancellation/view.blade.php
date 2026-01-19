@extends('layouts.app')
@section('title', 'Sale Cancellation Details')

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

        .badge {
            padding: 0.25rem 0.75rem;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-cancelled {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .badge-used {
            background-color: #dbeafe;
            color: #1e40af;
        }
    </style>
@endpush

@section('content')
    <div class="mb-6">
        <div class="flex items-center mb-2">
            <x-app.page-title
                url="{{ route('sale_cancellation.index') }}">{{ __('Sale Cancelaltion Details') }}</x-app.page-title>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="text-sm font-semibold text-gray-600">{{ __('Salesperson') }}</label>
                    <p class="text-base font-medium">{{ $saleperson->name ?? 'N/A' }}</p>
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-600">{{ __('Product') }}</label>
                    <p class="text-base font-medium">{{ ($product->sku . ' - ' . $product->model_desc) ?? 'N/A' }}</p>
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-600">{{ __('Available Qty') }}</label>
                    <p class="text-base font-medium text-green-600">{{ $available_qty }}</p>
                </div>
            </div>
        </div>
    </div>

    @include('components.app.alert.parent')

    <div>
        <h3 class="text-lg font-semibold mb-4">{{ __('Transaction History') }}</h3>

        <!-- Table -->
        <table id="data-table" class="text-sm rounded-lg overflow-hidden" style="width: 100%;">
            <thead>
                <tr>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Type') }}</th>
                    <th>{{ __('Sale Order') }}</th>
                    <th>{{ __('Reference SO') }}</th>
                    <th>{{ __('Qty') }}</th>
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
            order: [
                [0, 'desc']
            ],
            displayStart: DEFAULT_PAGE != null ? (DEFAULT_PAGE - 1) * 10 : 0,
            columns: [{
                    data: 'date'
                },
                {
                    data: 'type'
                },
                {
                    data: 'sale_sku'
                },
                {
                    data: 'reference_sku'
                },
                {
                    data: 'qty'
                },
            ],
            columnDefs: [{
                    "width": "15%",
                    "targets": 0,
                    render: function(data, type, row) {
                        return data
                    }
                },
                {
                    "width": "15%",
                    "targets": 1,
                    render: function(data, type, row) {
                        return data
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
                    "width": "20%",
                    "targets": 3,
                    render: function(data, type, row) {
                        return data ?? '-'
                    }
                },
                {
                    "width": "10%",
                    "targets": 4,
                    render: function(data, type, row) {
                        return data
                    }
                },
            ],
            ajax: {
                data: function() {
                    var info = $('#data-table').DataTable().page.info();
                    var url =
                        "{{ route('sale_cancellation.get_view_data', ['saleperson_id' => $saleperson_id, 'product_id' => $product_id]) }}"

                    url =
                        `${url}?page=${ INIT_LOAD == true && DEFAULT_PAGE != null ? DEFAULT_PAGE : info.page + 1 }`
                    $('#data-table').DataTable().ajax.url(url);

                    INIT_LOAD = false
                },
            },
        });
    </script>
@endpush
