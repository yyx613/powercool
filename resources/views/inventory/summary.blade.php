@extends('layouts.app')
@section('title', 'Inventory Summary')

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

        .dataTables_wrapper {
            min-width: auto;
        }
    </style>
@endpush

@section('content')
    <div class="mb-6">
        <x-app.page-title>{{ __('Inventory Summary') }}</x-app.page-title>
    </div>
    @include('components.app.alert.parent')
    <!-- Summary -->
    <div class="mb-6">
        <div class="flex gap-4 flex-col md:flex-row">
            <div class="flex-1 bg-slate-200 p-2 rounded">
                <div>
                    <span class="text-lg font-black">{{ __('Warehouse') }}</span>
                </div>
                <div class="flex border-t border-slate-300 mt-4">
                    <div class="flex-1 flex items-center justify-between pt-2 border-r border-slate-300 pr-3 mr-3">
                        <span class="text-md">{{ __('Available Stock') }}</span>
                        <span class="text-lg font-black" id="warehouse-available-stock">-</span>
                    </div>
                    <div class="flex-1 flex items-center justify-between pt-2">
                        <span class="text-md">{{ __('Reserved Stock') }}</span>
                        <span class="text-lg font-black" id="warehouse-reserved-stock">-</span>
                    </div>
                </div>
            </div>
            <div class="flex-1 bg-slate-200 p-2 rounded">
                <div>
                    <span class="text-lg font-black">{{ __('Factory') }}</span>
                </div>
                <div class="flex border-t border-slate-300 mt-4">
                    <div class="flex-1 flex items-center justify-between pt-2 border-r border-slate-300 pr-3 mr-3">
                        <span class="text-md">{{ __('Production Stock') }}</span>
                        <span class="text-lg font-black" id="production-stock">-</span>
                    </div>
                    <div class="flex-1 flex items-center justify-between pt-2">
                        <span class="text-md">{{ __('Reserved Stock') }}</span>
                        <span class="text-lg font-black" id="production-reserved-stock">-</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Content -->
    <div class="flex gap-4 flex-col md:flex-row">
        <!-- Left -->
        <div class="flex-[2] flex flex-col gap-4">
            <!-- Low Quantity Stock (Products) -->
            <div class="border bg-white rounded-lg px-2 py-1">
                <h6 class="font-black text-xl mb-2">{{ __('Low Quantity Stock (Products)') }}</h6>
                <div class="max-h-64 overflow-y-auto">
                    @foreach ($products as $pro)
                        @if ($pro->isLowStock())
                            <div class="mb-2 flex items-center gap-4">
                                <div class="h-8 w-8">
                                    @if ($pro->image != null)
                                        <img src="{{ $pro->image->url }}" alt=""
                                            class="h-full w-full object-contain">
                                    @else
                                        <x-app.no-image-icon class="p-1" />
                                    @endif
                                </div>
                                <span class="flex-1 text-lg font-medium">{{ $pro->sku }}</span>
                                <span
                                    class="flex-1 text-slate-500 text-center flex justify-center items-center">{{ __('Remaining Qty:') }}
                                    <span class="text-2xl ml-1">{{ $pro->warehouseAvailableStock() }}</span></span>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
            <!-- Low Quantity Stock (Raw Materials) -->
            <div class="border bg-white rounded-lg px-2 py-1">
                <h6 class="font-black text-xl mb-2">{{ __('Low Quantity Stock (Raw Materials)') }}</h6>
                <div class="max-h-64 overflow-y-auto">
                    @foreach ($raw_materials as $pro)
                        @if ($pro->isLowStock())
                            <div class="mb-2 flex items-center gap-4">
                                <div class="h-8 w-8">
                                    @if ($pro->image != null)
                                        <img src="{{ $pro->image->url }}" alt=""
                                            class="h-full w-full object-contain">
                                    @else
                                        <x-app.no-image-icon class="p-1" />
                                    @endif
                                </div>
                                <span class="flex-1 text-lg font-medium">{{ $pro->model_name }}</span>
                                <span
                                    class="flex-1 text-slate-500 text-center flex justify-center items-center">{{ __('Remaining Qty:') }}
                                    <span class="text-2xl ml-1">{{ $pro->warehouseAvailableStock() }}</span></span>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
            <!-- Remaining products qty -->
            <div class="border bg-white rounded-lg px-2 py-1">
                <h6 class="font-black text-xl mb-2">{{ __('Quantity Remaining') }}</h6>
                <div class="p-2">
                    <!-- Filters -->
                    <div class="flex max-w-screen-sm w-full mb-4 gap-4">
                        <div class="flex-1">
                            <x-app.input.input name="filter_search" id="filter_search" class="flex items-center"
                                placeholder="{{ __('Search') }}">
                                <div class="rounded-md border border-transparent p-1 ml-1">
                                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Outline"
                                        viewBox="0 0 24 24">
                                        <path
                                            d="M23.707,22.293l-5.969-5.969a10.016,10.016,0,1,0-1.414,1.414l5.969,5.969a1,1,0,0,0,1.414-1.414ZM10,18a8,8,0,1,1,8-8A8.009,8.009,0,0,1,10,18Z" />
                                    </svg>
                                </div>
                            </x-app.input.input>
                        </div>
                        <div class="flex-1">
                            <x-app.input.select name="filter_category" id="filter_category" class="w-full">
                                <option value="">{{ __('Select a category') }}</option>
                                <option value="product">{{ __('Product') }}</option>
                                <option value="raw_material">{{ __('Raw Material') }}</option>
                                <option value="sparepart">{{ __('Sparepart') }}</option>
                            </x-app.input.select>
                        </div>
                    </div>
                    <!-- Table -->
                    <table id="data-table" class="text-sm rounded-lg overflow-hidden" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Remaining Qty') }}</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- Right -->
        <div class="flex-1 flex flex-col gap-4">
            <!-- Inventory Summary -->
            <div class="border bg-white rounded-lg px-2 py-1">
                <h6 class="font-black text-xl mb-2">{{ __('Inventory Summary') }}</h6>
                @php
                    $data = [
                        [
                            'label' => 'Active Products',
                            'value' => $active_product_count ?? 0,
                        ],
                        [
                            'label' => 'Inactive Products',
                            'value' => $inactive_product_count ?? 0,
                        ],
                        [
                            'label' => 'Quantity In Hand',
                            'value' => '-',
                        ],
                        [
                            'label' => 'Quantity To Be Received',
                            'value' => '-',
                        ],
                    ];
                @endphp
                @foreach ($data as $d)
                    <div class="flex justify-between my-1.5">
                        <span>{{ __($d['label']) }}</span>
                        <span id="{{ strtolower(str_replace(' ', '-', $d['label'])) }}">{{ $d['value'] }}</span>
                    </div>
                @endforeach
            </div>
            <!-- Category -->
            <div class="border bg-white rounded-lg px-2 py-1">
                <h6 class="font-black text-xl mb-2">{{ __('Inventory Category') }}</h6>
                <canvas id="chart1"></canvas>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        CATEGORIES = @json($categories);
        FILTER = {
            'search': '',
            'category': ''
        }

        $(document).ready(function() {
            getStatistics()
        })

        // Chart 1
        const ctx = document.getElementById('chart1');
        const data = {
            type: 'doughnut',
            data: {
                labels: CATEGORIES['label'],
                datasets: [{
                    data: CATEGORIES['data'],
                }, ],
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                }
            },
        }
        new Chart(ctx, data);

        // Datatable
        var dt = new DataTable('#data-table', {
            dom: 'rtip',
            pagingType: 'numbers',
            pageLength: 10,
            processing: true,
            serverSide: true,
            order: [],
            columns: [{
                    data: 'name'
                },
                {
                    data: 'remaining_qty'
                },
            ],
            columnDefs: [{
                    "width": "10%",
                    "targets": 0,
                    orderable: false,
                    render: function(data, type, row) {
                        return `
                            <div class="flex items-center gap-x-2">
                                <div class="h-8 w-8">
                                    ${
                                        row.image != null ? `<img src="${ row.image.url }" class="h-full w-full object-contain" />` :
                                            `<x-app.no-image-icon class="p-1"/>`
                                    }
                                </div>
                                <span>${data}</span>
                            </div>
                        `
                    }
                },
                {
                    "width": '10%',
                    "targets": 1,
                    orderable: false,
                    render: function(data, type, row) {
                        return data
                    }
                },
            ],
            ajax: {
                data: function() {
                    var info = $('#data-table').DataTable().page.info();
                    var url = "{{ route('inventory_summary.get_remaining_qty') }}"

                    url =
                        `${url}?page=${ info.page + 1 }&keyword=${FILTER['search']}&category=${FILTER['category']}`
                    $('#data-table').DataTable().ajax.url(url);
                },
            },
        });
        $('#filter_search').on('keyup', function() {
            FILTER['search'] = $(this).val()

            dt.draw()
        })
        $('#filter_category').on('change', function() {
            FILTER['category'] = $(this).val()

            dt.draw()
        })

        function getStatistics() {
            let url = '{{ route('inventory_summary.get_data_summary') }}'

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: url,
                type: 'GET',
                success: function(res) {
                    $('#warehouse-available-stock, #quantity-in-hand').text(res.warehouse_available_stock)
                    $('#warehouse-reserved-stock, #quantity-to-be-received').text(res.warehouse_reserved_stock)
                    $('#production-stock').text(res.production_stock)
                    $('#production-reserved-stock').text(res.production_reserved_stock)
                },
            });
        }
    </script>
@endpush
