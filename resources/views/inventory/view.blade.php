@php
    $title = 'Finish Good';
    if (!$is_product) {
        $title = 'Raw Material';
    }
    if ($is_production) {
        $title = 'Production ' . $title;
    }
@endphp

@extends('layouts.app')
@section('title', $title)

@vite(['resources/css/jquery.dataTables.min.css'])

@push('styles')
    <style>
        #normal-table,
        #cost-table,
        #in-transit-table {
            border: solid 1px rgb(209 213 219);
        }

        #normal-table thead th,
        #normal-table tbody tr td,
        #cost-table thead th,
        #cost-table tbody tr td,
        #in-transit-table thead th,
        #in-transit-table tbody tr td {
            border-bottom: solid 1px rgb(209 213 219);
        }

        #normal-table tbody tr:last-of-type td,
        #cost-table tbody tr:last-of-type td,
        #in-transit-table tbody tr:last-of-type td {
            border-bottom: none;
        }
    </style>
@endpush

@section('content')
    <div class="mb-6">
        <x-app.page-title
            url="{{ $is_production ? ($is_product ? route('production_finish_good.index') : route('production_material.index')) : ($is_product ? route('product.index') : route('raw_material.index')) }}">{{ $is_product ? __('View Product') : ($is_production ? __('View Production Material') : __('View Raw Material')) }}</x-app.page-title>
    </div>
    @include('components.app.alert.parent')
    <!-- Summary -->
    @if (!$is_production)
        <div class="mb-6">
            <div class="flex gap-4 flex-col lg:flex-row">
                <div class="flex-1 bg-slate-200 p-2 rounded">
                    <div>
                        <span class="text-lg font-black">{{ __('Warehouse') }}</span>
                    </div>
                    <div class="flex border-t border-slate-300 mt-4">
                        <div class="flex-1 flex items-center justify-between pt-2">
                            <span class="text-md">{{ __('Available Stock') }}</span>
                            <span class="text-lg font-black">{{ $warehouse_available_stock }}</span>
                        </div>
                        <div class="flex-1 flex items-center justify-between pt-2 border-x border-slate-300 px-3 mx-3">
                            <span class="text-md">{{ __('Reserved Stock') }}</span>
                            <span class="text-lg font-black">{{ $warehouse_reserved_stock }}</span>
                        </div>
                        <div class="flex-1 flex items-center justify-between pt-2">
                            <span class="text-md">{{ __('On Hold Stock') }}</span>
                            <span class="text-lg font-black">{{ $warehouse_on_hold_stock }}</span>
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
                            <span class="text-lg font-black">{{ $production_stock }}</span>
                        </div>
                        <div class="flex-1 flex items-center justify-between pt-2">
                            <span class="text-md">{{ __('Reserved Stock') }}</span>
                            <span class="text-lg font-black">{{ $production_reserved_stock }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Product -->
    <div class="mb-6 flex gap-x-4">
        @if ($prod->images != null)
            @foreach ($prod->images as $img)
                <div class="h-12 w-12 rounded overflow-hidden">
                    <a href="{{ $img->url }}" target="_blank">
                        <img src="{{ $img->url }}" alt="{{ $prod->model_name }}" class="w-full h-full object-contain">
                    </a>
                </div>
            @endforeach
        @endif
    </div>
    <div class="mb-6 flex gap-x-4">
        <div class="flex flex-col flex-1">
            <div class="flex items-center mb-2 gap-x-4">
                <h1 class="text-lg font-semibold leading-none">{{ $prod->model_name }}</h1>
                <span
                    class="text-xs font-semibold py-1 px-3 rounded-full {{ $prod->is_active ? 'bg-green-300' : 'bg-red-300' }}">{{ $prod->is_active ? 'Active' : 'Inactive' }}</span>
            </div>
            <div class="flex gap-x-4 mb-1">
                <span class="text-xs font-semibold text-slate-500">{{ __('Code') }}: {{ $prod->sku }}</span>
                <span class="text-xs font-semibold text-slate-500">{{ __('Category') }}:
                    {{ $prod->category->name }}</span>
                @if ($prod->low_stock_threshold != null)
                    <span class="text-xs font-semibold text-slate-500">{{ __('Low Stock Threshold') }}:
                        {{ $prod->low_stock_threshold }}</span>
                @endif
            </div>
            @if ($prod->length != null || $prod->width != null || $prod->height != null || $prod->weight != null)
                <div class="flex gap-x-4 mb-1">
                    @if ($prod->length != null)
                        <span class="text-xs font-semibold text-slate-500">{{ __('Length') }}: {{ str_contains($prod->length, '.00') ? (int)$prod->length : $prod->length }}
                            MM</span>
                    @endif
                    @if ($prod->width != null)
                        <span class="text-xs font-semibold text-slate-500">{{ __('Width') }}: {{ str_contains($prod->width, '.00') ? (int)$prod->width : $prod->width }}
                            MM</span>
                    @endif
                    @if ($prod->height != null)
                        <span class="text-xs font-semibold text-slate-500">{{ __('Height') }}: {{ str_contains($prod->height, '.00') ? (int)$prod->height : $prod->height }}
                            MM</span>
                    @endif
                    @if ($prod->weight != null)
                        <span class="text-xs font-semibold text-slate-500">{{ __('Weight') }}: {{ str_contains($prod->weight, '.00') ? (int)$prod->weight : $prod->weight }}
                            KG</span>
                    @endif
                </div>
            @endif
            <div class="border-t mt-2.5 pt-2.5">
                <span class="text-xs text-slate-500">{{ $prod->model_desc }}</span>
            </div>
        </div>
    </div>

    @if ($table_type == 'normal')
        <div>
            @if ($is_production || $is_product || $prod->is_sparepart === 1)
                <div>
                    <!-- Filters -->
                    <div class="flex items-center max-w-screen-lg gap-x-2 w-full mb-4">
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
                        <div class="flex-1">
                            <x-app.input.select name="filter_table_type" id="filter_table_type" class="w-full">
                                <option value="normal" default @selected($table_type == 'normal')>{{ __('Normal') }}</option>
                                <option value="cost" @selected($table_type == 'cost')>{{ __('Cost') }}</option>
                                <option value="in-transit" @selected($table_type == 'in-transit')>{{ __('In Transit') }}</option>
                            </x-app.input.select>
                        </div>
                        <div>
                            <x-app.qr-scanner />
                        </div>
                        <div class="flex-1">
                            <a href="{{ $is_product ? route('product.generate_barcode') : route('raw_material.generate_barcode') }}"
                                class="flex items-center gap-x-4 bg-sky-200 p-2 rounded w-fit" id="generate-barcode-btn">
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1"
                                    viewBox="0 0 24 24" width="512" height="512">
                                    <path
                                        d="M5,18c-.553,0-1-.448-1-1V7c0-.552,.447-1,1-1s1,.448,1,1v10c0,.552-.447,1-1,1Zm5-1V7c0-.552-.447-1-1-1s-1,.448-1,1v10c0,.552,.447,1,1,1s1-.448,1-1Zm10,0V7c0-.552-.447-1-1-1s-1,.448-1,1v10c0,.552,.447,1,1,1s1-.448,1-1Zm-6-.5V7.5c0-.829-.672-1.5-1.5-1.5s-1.5,.671-1.5,1.5v9c0,.829,.672,1.5,1.5,1.5s1.5-.671,1.5-1.5Zm-7,4.5c0-.552-.447-1-1-1h-2c-1.103,0-2-.897-2-2v-2c0-.552-.447-1-1-1s-1,.448-1,1v2c0,2.206,1.794,4,4,4h2c.553,0,1-.448,1-1Zm17-3v-2c0-.552-.447-1-1-1s-1,.448-1,1v2c0,1.103-.897,2-2,2h-2c-.553,0-1,.448-1,1s.447,1,1,1h2c2.206,0,4-1.794,4-4Zm0-10v-2c0-2.206-1.794-4-4-4h-2c-.553,0-1,.448-1,1s.447,1,1,1h2c1.103,0,2,.897,2,2v2c0,.552,.447,1,1,1s1-.448,1-1Zm-22,0v-2c0-1.103,.897-2,2-2h2c.553,0,1-.448,1-1s-.447-1-1-1h-2C1.794,2,0,3.794,0,6v2c0,.552,.447,1,1,1s1-.448,1-1Zm13.5,10h0c-.276,0-.5-.224-.5-.5V6.5c0-.276,.224-.5,.5-.5h0c.276,0,.5,.224,.5,.5v11c0,.276-.224,.5-.5,.5Z" />
                                </svg>
                                <span class="font-medium">{{ __('Generate Barcode') }}</span>
                            </a>
                        </div>
                    </div>

                    <!-- Table -->
                    <table id="normal-table" class="text-sm rounded-lg overflow-hidden" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>
                                    <input type="checkbox" id="select-all-checkbox" class="border-slate-400 rounded">
                                </th>
                                <th>{{ __('Serial Number') }}</th>
                                <th>{{ __('Location') }}</th>
                                <th>{{ __('Assigned Order ID') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Done By') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <x-app.modal.stock-in-modal />
                @if ($prod->company_group == 1)
                    <x-app.modal.stock-out-to-hi-ten-modal />
                @else
                    <x-app.modal.stock-out-modal />
                @endif
                <x-app.modal.transfer-modal />
            @else
                <div>
                    <!-- Filters -->
                    <div class="flex items-center max-w-xs gap-x-2 w-full mb-4">
                        <div class="flex-1">
                            <x-app.input.select name="filter_table_type" id="filter_table_type" class="w-full">
                                <option value="normal" default @selected($table_type == 'normal')>{{ __('Normal') }}</option>
                                <option value="cost" @selected($table_type == 'cost')>{{ __('Cost') }}</option>
                            </x-app.input.select>
                        </div>
                    </div>
                    <!-- Table -->
                    <table id="normal-table" class="text-sm rounded-lg overflow-hidden" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>{{ __('Assigned Order ID') }}</th>
                                <th>{{ __('Qty') }}</th>
                                <th>{{ __('On Hold') }}</th>
                                <th>{{ __('At') }}</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            @endif
        </div>
    @elseif ($table_type == 'cost')
        <div>
            <!-- Filters -->
            <div class="flex items-center max-w-xs gap-x-2 w-full mb-4">
                <div class="flex-1">
                    <x-app.input.select name="filter_table_type" id="filter_table_type" class="w-full">
                        <option value="normal" default @selected($table_type == 'normal')>{{ __('Normal') }}</option>
                        <option value="cost" @selected($table_type == 'cost')>{{ __('Cost') }}</option>
                        @if ($prod->is_sparepart !== 0)
                            <option value="in-transit" @selected($table_type == 'in-transit')>{{ __('In Transit') }}</option>
                        @endif
                    </x-app.input.select>
                </div>
            </div>
            <!-- Table -->
            <table id="cost-table" class="text-sm rounded-lg overflow-hidden" style="width: 100%;">
                <thead>
                    <tr>
                        @if ($is_product || $prod->is_sparepart == true)
                            <th>{{ __('SKU') }}</th>
                        @else
                            <th>{{ __('Qty') }}</th>
                        @endif
                        <th>{{ __('Unit Price') }}</th>
                        <th>{{ __('Total Price') }}</th>
                        <th>{{ __('At') }}</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    @elseif ($table_type == 'in-transit')
        <div>
            <div>
                <!-- Filters -->
                <div class="flex items-center max-w-screen-lg gap-x-2 w-full mb-4">
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
                    <div class="flex-1">
                        <x-app.input.select name="filter_table_type" id="filter_table_type" class="w-full">
                            <option value="normal" default @selected($table_type == 'normal')>{{ __('Normal') }}</option>
                            <option value="cost" @selected($table_type == 'cost')>{{ __('Cost') }}</option>
                            <option value="in-transit" @selected($table_type == 'in-transit')>{{ __('In Transit') }}</option>
                        </x-app.input.select>
                    </div>
                    <div>
                        <x-app.qr-scanner />
                    </div>
                    <div class="flex-1">
                        <a href="{{ $is_product ? route('product.generate_barcode') : route('raw_material.generate_barcode') }}"
                            class="flex items-center gap-x-4 bg-sky-200 p-2 rounded w-fit" id="generate-barcode-btn">
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1"
                                viewBox="0 0 24 24" width="512" height="512">
                                <path
                                    d="M5,18c-.553,0-1-.448-1-1V7c0-.552,.447-1,1-1s1,.448,1,1v10c0,.552-.447,1-1,1Zm5-1V7c0-.552-.447-1-1-1s-1,.448-1,1v10c0,.552,.447,1,1,1s1-.448,1-1Zm10,0V7c0-.552-.447-1-1-1s-1,.448-1,1v10c0,.552,.447,1,1,1s1-.448,1-1Zm-6-.5V7.5c0-.829-.672-1.5-1.5-1.5s-1.5,.671-1.5,1.5v9c0,.829,.672,1.5,1.5,1.5s1.5-.671,1.5-1.5Zm-7,4.5c0-.552-.447-1-1-1h-2c-1.103,0-2-.897-2-2v-2c0-.552-.447-1-1-1s-1,.448-1,1v2c0,2.206,1.794,4,4,4h2c.553,0,1-.448,1-1Zm17-3v-2c0-.552-.447-1-1-1s-1,.448-1,1v2c0,1.103-.897,2-2,2h-2c-.553,0-1,.448-1,1s.447,1,1,1h2c2.206,0,4-1.794,4-4Zm0-10v-2c0-2.206-1.794-4-4-4h-2c-.553,0-1,.448-1,1s.447,1,1,1h2c1.103,0,2,.897,2,2v2c0,.552,.447,1,1,1s1-.448,1-1Zm-22,0v-2c0-1.103,.897-2,2-2h2c.553,0,1-.448,1-1s-.447-1-1-1h-2C1.794,2,0,3.794,0,6v2c0,.552,.447,1,1,1s1-.448,1-1Zm13.5,10h0c-.276,0-.5-.224-.5-.5V6.5c0-.276,.224-.5,.5-.5h0c.276,0,.5,.224,.5,.5v11c0,.276-.224,.5-.5,.5Z" />
                            </svg>
                            <span class="font-medium">{{ __('Generate Barcode') }}</span>
                        </a>
                    </div>
                </div>

                <!-- Table -->
                <table id="in-transit-table" class="text-sm rounded-lg overflow-hidden" style="width: 100%;">
                    <thead>
                        <tr>
                            <th>
                                <input type="checkbox" id="select-all-checkbox" class="border-slate-400 rounded">
                            </th>
                            <th>{{ __('Serial Number') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Done By') }}</th>
                            <th>{{ __('Remark') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            <x-app.modal.stock-in-modal />
            <x-app.modal.confirmation-modal />
            <x-app.modal.approval-reject-modal />
            @if ($prod->company_group == 1)
                <x-app.modal.stock-out-to-hi-ten-modal />
            @else
                <x-app.modal.stock-out-modal />
            @endif
        </div>
    @endif
@endsection

@push('scripts')
    <script>
        IS_PRODUCT = @json($is_product ?? null);
        IS_PRODUCTION = @json($is_production ?? null);
        PRODUCT = @json($prod ?? null);
        CHECKED_CHECKBOXES = []
        HAS_PERMISSION_TO_ACTION = @json($has_permission_to_action ?? null);
        TABLE_TYPE = @json($table_type);

        $(document).ready(function() {
            // Auto search if url provided 'search' keyword
            const urlParams = new URLSearchParams(window.location.search);
            const search = urlParams.get('search');

            if (search != null) {
                setTimeout(() => {
                    $('#filter_search').val(search).trigger('keyup')
                }, 200);
            }
        })

        if (TABLE_TYPE == 'normal') {
            if (PRODUCT.is_sparepart == false) {
                // Datatable
                var dt = new DataTable('#normal-table', {
                    dom: 'rtip',
                    pagingType: 'numbers',
                    pageLength: 10,
                    processing: true,
                    serverSide: true,
                    order: [],
                    columns: [{
                            data: 'order_id'
                        },
                        {
                            data: 'qty'
                        },
                        {
                            data: 'on_hold'
                        },
                        {
                            data: 'at'
                        },
                    ],
                    columnDefs: [{
                            "width": "10%",
                            "targets": 0,
                            'orderable': false,
                            render: function(data, type, row) {
                                return data
                            }
                        },
                        {
                            "width": "10%",
                            "targets": 1,
                            'orderable': false,
                            render: function(data, type, row) {
                                return data
                            }
                        },
                        {
                            "width": "10%",
                            "targets": 2,
                            'orderable': false,
                            render: function(data, type, row) {
                                return data == null ? '-' : data == true ? 'True' : 'False'
                            }
                        },
                        {
                            "width": "10%",
                            "targets": 3,
                            'orderable': false,
                            render: function(data, type, row) {
                                return data
                            }
                        },
                    ],
                    ajax: {
                        data: function() {
                            var info = $('#normal-table').DataTable().page.info();
                            var url = "{{ route('product.view_get_data_raw_material') }}"
    
                            url = `${url}?page=${ info.page + 1 }&product_id=${ PRODUCT.id }`
                            $('#normal-table').DataTable().ajax.url(url);
                        },
                    },
                });
            } else {
                // Datatable
                var dt = new DataTable('#normal-table', {
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
                            data: 'location'
                        },
                        {
                            data: 'order_id'
                        },
                        {
                            data: 'status'
                        },
                        {
                            data: 'done_by'
                        },
                        {
                            data: 'action'
                        },
                    ],
                    columnDefs: [{
                            "width": "1%",
                            "targets": 0,
                            orderable: false,
                            render: function(data, type, row) {
                                return `<input type="checkbox" name="checkbox" class="border-slate-400 rounded checkboxes" data-id=${row.id}>`
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
                                return `<span class="capitalize">${data}</span>`
                            }
                        },
                        {
                            "width": "10%",
                            "targets": 3,
                            orderable: false,
                            render: function(data, type, row) {
                                return data ?? '-'
                            }
                        },
                        {
                            "width": "5%",
                            "targets": 4,
                            orderable: false,
                            render: function(data, type, row) {
                                switch (data) {
                                    case 1:
                                        return `{!! __('Stocked Out') !!} (${ row.stock_out_to == 'production' ? 'Production' : (row.stock_out_to == null ? '' : row.stock_out_to.name) })`
                                    case 2:
                                        return "{!! __('In Transit') !!}"
                                    case 3:
                                        return "{!! __('To Be Received') !!}"
                                    case 4:
                                        return "{!! __('Received') !!}"
                                    case 5:
                                        return "{!! __('Pending Approval') !!}"
                                    case 6:
                                        return "{!! __('Transfer Approved') !!}"
                                    case 7:
                                        return "{!! __('Broken') !!}"
                                    case 14:
                                        return "{!! __('Transfer Rejected') !!}"
                                    default:
                                        return '-'
                                }
                                return data ?? '-'
                            }
                        },
                        {
                            "width": "5%",
                            "targets": 5,
                            orderable: false,
                            render: function(data, type, row) {
                                if (data != null)
                                    return `<span class="text-sm font-semibold">${data.name}</span><br><span class="text-xs">${row.done_at}</span>`
                                return data
                            }
                        },
                        {
                            "width": "5%",
                            "targets": 6,
                            orderable: false,
                            render: function(data, type, row) {
                                console.log(row)
                                if (HAS_PERMISSION_TO_ACTION == false) return ''
                                if (row.status == 5 || row.status == 7) return ''
    
                                if (row.status == 6) {
                                    return `<div class="flex items-center justify-end gap-x-2 px-2">
                                        <button class="rounded-full py-2 px-3 bg-purple-200 flex items-center gap-x-2 stock-in-btns" data-id="${row.id}" data-serial-no="${row.sku}" data-order-id="${row.order_id != null ? row.order_id.sku : row.order_id}">
                                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M21,12h-3c-1.103,0-2,.897-2,2s-.897,2-2,2h-4c-1.103,0-2-.897-2-2s-.897-2-2-2H3c-1.654,0-3,1.346-3,3v4c0,2.757,2.243,5,5,5h14c2.757,0,5-2.243,5-5v-4c0-1.654-1.346-3-3-3Zm1,7c0,1.654-1.346,3-3,3H5c-1.654,0-3-1.346-3-3v-4c0-.552,.448-1,1-1l3-.002v.002c0,2.206,1.794,4,4,4h4c2.206,0,4-1.794,4-4h3c.552,0,1,.448,1,1v4ZM7.293,7.121c-.391-.391-.391-1.023,0-1.414s1.023-.391,1.414,0l2.293,2.293V1c0-.553,.447-1,1-1s1,.447,1,1v7l2.293-2.293c.391-.391,1.023-.391,1.414,0s.391,1.023,0,1.414l-3.293,3.293c-.387,.387-.896,.582-1.405,.584l-.009,.002-.009-.002c-.509-.002-1.018-.197-1.405-.584l-3.293-3.293Z"/></svg>
                                            <span class="text-xs font-medium">${ "{!! __('Stock In') !!}" }</span>
                                        </button>
                                    </div>`
                                }
    
                                return `<div class="flex items-center justify-end gap-x-2 px-2">
                                    ${
                                        row.progress >= 100 || row.status == 3 ? `
                                                                    <button class="rounded-full py-2 px-3 bg-purple-200 flex items-center gap-x-2 stock-in-btns" data-id="${row.id}" data-serial-no="${row.sku}" data-order-id="${row.order_id != null ? row.order_id.sku : row.order_id}">
                                                                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M21,12h-3c-1.103,0-2,.897-2,2s-.897,2-2,2h-4c-1.103,0-2-.897-2-2s-.897-2-2-2H3c-1.654,0-3,1.346-3,3v4c0,2.757,2.243,5,5,5h14c2.757,0,5-2.243,5-5v-4c0-1.654-1.346-3-3-3Zm1,7c0,1.654-1.346,3-3,3H5c-1.654,0-3-1.346-3-3v-4c0-.552,.448-1,1-1l3-.002v.002c0,2.206,1.794,4,4,4h4c2.206,0,4-1.794,4-4h3c.552,0,1,.448,1,1v4ZM7.293,7.121c-.391-.391-.391-1.023,0-1.414s1.023-.391,1.414,0l2.293,2.293V1c0-.553,.447-1,1-1s1,.447,1,1v7l2.293-2.293c.391-.391,1.023-.391,1.414,0s.391,1.023,0,1.414l-3.293,3.293c-.387,.387-.896,.582-1.405,.584l-.009,.002-.009-.002c-.509-.002-1.018-.197-1.405-.584l-3.293-3.293Z"/></svg>
                                                                        <span class="text-xs font-medium">${ "{!! __('Stock In') !!}" }</span>
                                                                    </button>` : ''
                                    }
                                    ${
                                        IS_PRODUCTION || row.location == 'factory' || (row.status != null && row.status != 12) ? '' : `
                                                                    <button class="rounded-full py-2 px-3 bg-orange-200 flex items-center gap-x-2 stock-out-btns" data-id="${row.id}" data-serial-no="${row.sku}">
                                                                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M21,12h-3c-1.103,0-2,.897-2,2s-.897,2-2,2h-4c-1.103,0-2-.897-2-2s-.897-2-2-2H3c-1.654,0-3,1.346-3,3v4c0,2.757,2.243,5,5,5h14c2.757,0,5-2.243,5-5v-4c0-1.654-1.346-3-3-3Zm1,7c0,1.654-1.346,3-3,3H5c-1.654,0-3-1.346-3-3v-4c0-.552,.448-1,1-1l3-.002v.002c0,2.206,1.794,4,4,4h4c2.206,0,4-1.794,4-4h3c.552,0,1,.448,1,1v4ZM7.293,5.293c-.391-.391-.391-1.023,0-1.414L10.586,.586C10.972,.2,11.479,.006,11.986,.003l.014-.003,.014,.003c.508,.003,1.014,.197,1.4,.583l3.293,3.293c.391,.391,.391,1.023,0,1.414-.195,.195-.451,.293-.707,.293s-.512-.098-.707-.293l-2.293-2.293v7c0,.553-.447,1-1,1s-1-.447-1-1V3l-2.293,2.293c-.391,.391-1.023,.391-1.414,0Z"/></svg>
                                                                        <span class="text-xs font-medium">${ "{!! __('Stock Out') !!}" }</span>
                                                                    </button>`
                                    }
                                    ${
                                        IS_PRODUCTION || (row.status != null && row.status != 12) ? '' : `
                                                                    <button class="rounded-full py-2 px-3 bg-emerald-200 flex items-center gap-x-2 transfer-btns" data-id="${row.id}" data-serial-no="${row.sku}" data-order-id="${row != null && row.order_id != null ? row.order_id.sku : row.order_id}">
                                                                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M24,12.649a5,5,0,0,0-.256-1.581L22.405,7.051A3,3,0,0,0,19.559,5H17V4a3,3,0,0,0-3-3H3A3,3,0,0,0,0,4V19.5a3.517,3.517,0,0,0,6,2.447A3.517,3.517,0,0,0,12,19.5V19h3v.5a3.5,3.5,0,0,0,7,0V19h2ZM19.559,7a1,1,0,0,1,.948.684L21.613,11H17V7ZM2,4A1,1,0,0,1,3,3H14a1,1,0,0,1,1,1V17H2ZM3.5,21A1.5,1.5,0,0,1,2,19.5V19H5v.5A1.5,1.5,0,0,1,3.5,21ZM10,19.5a1.5,1.5,0,0,1-3,0V19h3Zm10,0a1.5,1.5,0,0,1-3,0V19h3ZM17,17V13h5v4Z"/></svg>
                                                                        <span class="text-xs font-medium">${ "{!! __('Transfer') !!}" }</span>
                                                                    </button>`
                                    }
                                    ${
                                        IS_PRODUCTION  ? `
                                                                    <button class="rounded-full py-2 px-3 bg-purple-200 flex items-center gap-x-2 to-warehouse-btns" data-id="${row.id}">
                                                                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M21,12h-3c-1.103,0-2,.897-2,2s-.897,2-2,2h-4c-1.103,0-2-.897-2-2s-.897-2-2-2H3c-1.654,0-3,1.346-3,3v4c0,2.757,2.243,5,5,5h14c2.757,0,5-2.243,5-5v-4c0-1.654-1.346-3-3-3Zm1,7c0,1.654-1.346,3-3,3H5c-1.654,0-3-1.346-3-3v-4c0-.552,.448-1,1-1l3-.002v.002c0,2.206,1.794,4,4,4h4c2.206,0,4-1.794,4-4h3c.552,0,1,.448,1,1v4ZM7.293,5.293c-.391-.391-.391-1.023,0-1.414L10.586,.586C10.972,.2,11.479,.006,11.986,.003l.014-.003,.014,.003c.508,.003,1.014,.197,1.4,.583l3.293,3.293c.391,.391,.391,1.023,0,1.414-.195,.195-.451,.293-.707,.293s-.512-.098-.707-.293l-2.293-2.293v7c0,.553-.447,1-1,1s-1-.447-1-1V3l-2.293,2.293c-.391,.391-1.023,.391-1.414,0Z"/></svg>
                                                                        <span class="text-xs font-medium">${ "{!! __('To Warehouse') !!}" }</span>
                                                                    </button>` : ''
                                    }
                                </div>`
                            }
                        },
                    ],
                    ajax: {
                        data: function() {
                            var info = $('#normal-table').DataTable().page.info();
                            var url = "{{ route('product.view_get_data') }}"
    
                            url =
                                `${url}?page=${ info.page + 1 }&product_id=${ PRODUCT.id }&is_production=${IS_PRODUCTION}&table-type=normal`
                            $('#normal-table').DataTable().ajax.url(url);
                        },
                    },
                    fnCreatedRow: function(nRow, aData, iDataIndex) {
                        if (CHECKED_CHECKBOXES.includes(aData.id)) {
                            dt.$(`.checkboxes[data-id="${ aData.id }"]`).prop('checked', true)
                        }
                    },
                });
            }
        } else if (TABLE_TYPE == 'cost') {
            var costDT = new DataTable('#cost-table', {
                dom: 'rtip',
                pagingType: 'numbers',
                pageLength: 10,
                processing: true,
                serverSide: true,
                order: [],
                columns: [{
                        data: 'qty_sku'
                    },
                    {
                        data: 'unit_price'
                    },
                    {
                        data: 'total_price'
                    },
                    {
                        data: 'at'
                    },
                ],
                columnDefs: [{
                        "width": "10%",
                        "targets": 0,
                        orderable: false,
                        render: function(data, type, row) {
                            return data
                        }
                    },
                    {
                        "width": "10%",
                        "targets": 1,
                        orderable: false,
                        render: function(data, type, row) {
                            return data
                        }
                    },
                    {
                        "width": "10%",
                        "targets": 2,
                        orderable: false,
                        render: function(data, type, row) {
                            return data
                        }
                    },
                    {
                        "width": "10%",
                        "targets": 3,
                        orderable: false,
                        render: function(data, type, row) {
                            return data
                        }
                    },
                ],
                ajax: {
                    data: function() {
                        var info = $('#cost-table').DataTable().page.info();
                        var url = "{{ route('product.view_get_data_cost') }}"

                        url = `${url}?page=${ info.page + 1 }&product_id=${ PRODUCT.id }`
                        $('#cost-table').DataTable().ajax.url(url);
                    },
                },
            });
        } else if (TABLE_TYPE == 'in-transit') {
            // Datatable
            var dt = new DataTable('#in-transit-table', {
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
                        data: 'status'
                    },
                    {
                        data: 'done_by'
                    },
                    {
                        data: 'remark'
                    },
                    {
                        data: 'action'
                    },
                ],
                columnDefs: [{
                        "width": "1%",
                        "targets": 0,
                        orderable: false,
                        render: function(data, type, row) {
                            return `<input type="checkbox" name="checkbox" class="border-slate-400 rounded checkboxes" data-id=${row.id}>`
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
                        "width": "5%",
                        "targets": 2,
                        orderable: false,
                        render: function(data, type, row) {
                            switch (data) {
                                case 1:
                                    return `{!! __('Stocked Out') !!} (${ row.stock_out_to == 'production' ? 'Production' : (row.stock_out_to == null ? '' : row.stock_out_to.name) })`
                                case 2:
                                    return "{!! __('In Transit') !!}"
                                case 3:
                                    return "{!! __('To Be Received') !!}"
                                case 4:
                                    return "{!! __('Received') !!}"
                                case 5:
                                    return "{!! __('Pending Approval') !!}"
                                case 6:
                                    return "{!! __('Transfer Approved') !!}"
                                case 7:
                                    return "{!! __('Broken') !!}"
                                case 8:
                                    return `{!! __('In Transit') !!} ({!! __('Factory') !!} ${row.stock_out_factory})`
                                case 9:
                                case 12:
                                    return `{!! __('Accepted') !!}`
                                case 10:
                                case 13:
                                    return `{!! __('Rejected') !!}`
                                case 11:
                                    return `{!! __('In Transit (Warehouse)') !!}`
                            }
                            return data ?? '-'
                        }
                    },
                    {
                        "width": "5%",
                        "targets": 3,
                        orderable: false,
                        render: function(data, type, row) {
                            if (data != null)
                                return `<span class="text-sm font-semibold">${data.name}</span><br><span class="text-xs">${row.done_at}</span>`
                            return data
                        }
                    },
                    {
                        "width": "5%",
                        "targets": 4,
                        orderable: false,
                        render: function(data, type, row) {
                            return data
                        }
                    },
                    {
                        "width": "5%",
                        "targets": 5,
                        orderable: false,
                        render: function(data, type, row) {
                            return `<div class="flex items-center justify-end gap-x-2 px-2">
                                ${
                                    row.progress >= 100 || row.status == 3 || (!IS_PRODUCTION && row.status == 10) || (IS_PRODUCTION && row.status == 13) ? `
                                        <button class="rounded-full py-2 px-3 bg-purple-200 flex items-center gap-x-2 stock-in-btns" data-id="${row.id}" data-serial-no="${row.sku}" data-order-id="${row.order_id != null ? row.order_id.sku : row.order_id}">
                                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M21,12h-3c-1.103,0-2,.897-2,2s-.897,2-2,2h-4c-1.103,0-2-.897-2-2s-.897-2-2-2H3c-1.654,0-3,1.346-3,3v4c0,2.757,2.243,5,5,5h14c2.757,0,5-2.243,5-5v-4c0-1.654-1.346-3-3-3Zm1,7c0,1.654-1.346,3-3,3H5c-1.654,0-3-1.346-3-3v-4c0-.552,.448-1,1-1l3-.002v.002c0,2.206,1.794,4,4,4h4c2.206,0,4-1.794,4-4h3c.552,0,1,.448,1,1v4ZM7.293,7.121c-.391-.391-.391-1.023,0-1.414s1.023-.391,1.414,0l2.293,2.293V1c0-.553,.447-1,1-1s1,.447,1,1v7l2.293-2.293c.391-.391,1.023-.391,1.414,0s.391,1.023,0,1.414l-3.293,3.293c-.387,.387-.896,.582-1.405,.584l-.009,.002-.009-.002c-.509-.002-1.018-.197-1.405-.584l-3.293-3.293Z"/></svg>
                                            <span class="text-xs font-medium">${ "{!! __('Stock In') !!}" }</span>
                                        </button>` : ''
                                }
                                ${
                                    IS_PRODUCTION || row.location == 'factory' || row.status != null ? '' : `
                                        <button class="rounded-full py-2 px-3 bg-orange-200 flex items-center gap-x-2 stock-out-btns" data-id="${row.id}" data-serial-no="${row.sku}">
                                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M21,12h-3c-1.103,0-2,.897-2,2s-.897,2-2,2h-4c-1.103,0-2-.897-2-2s-.897-2-2-2H3c-1.654,0-3,1.346-3,3v4c0,2.757,2.243,5,5,5h14c2.757,0,5-2.243,5-5v-4c0-1.654-1.346-3-3-3Zm1,7c0,1.654-1.346,3-3,3H5c-1.654,0-3-1.346-3-3v-4c0-.552,.448-1,1-1l3-.002v.002c0,2.206,1.794,4,4,4h4c2.206,0,4-1.794,4-4h3c.552,0,1,.448,1,1v4ZM7.293,5.293c-.391-.391-.391-1.023,0-1.414L10.586,.586C10.972,.2,11.479,.006,11.986,.003l.014-.003,.014,.003c.508,.003,1.014,.197,1.4,.583l3.293,3.293c.391,.391,.391,1.023,0,1.414-.195,.195-.451,.293-.707,.293s-.512-.098-.707-.293l-2.293-2.293v7c0,.553-.447,1-1,1s-1-.447-1-1V3l-2.293,2.293c-.391,.391-1.023,.391-1.414,0Z"/></svg>
                                            <span class="text-xs font-medium">${ "{!! __('Stock Out') !!}" }</span>
                                        </button>`
                                }
                                ${
                                    IS_PRODUCTION || row.status != null ? '' : `
                                        <button class="rounded-full py-2 px-3 bg-emerald-200 flex items-center gap-x-2 transfer-btns" data-id="${row.id}" data-serial-no="${row.sku}" data-order-id="${row != null && row.order_id != null ? row.order_id.sku : row.order_id}">
                                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M24,12.649a5,5,0,0,0-.256-1.581L22.405,7.051A3,3,0,0,0,19.559,5H17V4a3,3,0,0,0-3-3H3A3,3,0,0,0,0,4V19.5a3.517,3.517,0,0,0,6,2.447A3.517,3.517,0,0,0,12,19.5V19h3v.5a3.5,3.5,0,0,0,7,0V19h2ZM19.559,7a1,1,0,0,1,.948.684L21.613,11H17V7ZM2,4A1,1,0,0,1,3,3H14a1,1,0,0,1,1,1V17H2ZM3.5,21A1.5,1.5,0,0,1,2,19.5V19H5v.5A1.5,1.5,0,0,1,3.5,21ZM10,19.5a1.5,1.5,0,0,1-3,0V19h3Zm10,0a1.5,1.5,0,0,1-3,0V19h3ZM17,17V13h5v4Z"/></svg>
                                            <span class="text-xs font-medium">${ "{!! __('Transfer') !!}" }</span>
                                        </button>`
                                }
                                ${
                                    IS_PRODUCTION && ![8, 9, 10, 11, 12, 13].includes(row.status) ? `
                                        <button class="rounded-full py-2 px-3 bg-purple-200 flex items-center gap-x-2 to-warehouse-btns" data-id="${row.id}">
                                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M21,12h-3c-1.103,0-2,.897-2,2s-.897,2-2,2h-4c-1.103,0-2-.897-2-2s-.897-2-2-2H3c-1.654,0-3,1.346-3,3v4c0,2.757,2.243,5,5,5h14c2.757,0,5-2.243,5-5v-4c0-1.654-1.346-3-3-3Zm1,7c0,1.654-1.346,3-3,3H5c-1.654,0-3-1.346-3-3v-4c0-.552,.448-1,1-1l3-.002v.002c0,2.206,1.794,4,4,4h4c2.206,0,4-1.794,4-4h3c.552,0,1,.448,1,1v4ZM7.293,5.293c-.391-.391-.391-1.023,0-1.414L10.586,.586C10.972,.2,11.479,.006,11.986,.003l.014-.003,.014,.003c.508,.003,1.014,.197,1.4,.583l3.293,3.293c.391,.391,.391,1.023,0,1.414-.195,.195-.451,.293-.707,.293s-.512-.098-.707-.293l-2.293-2.293v7c0,.553-.447,1-1,1s-1-.447-1-1V3l-2.293,2.293c-.391,.391-1.023,.391-1.414,0Z"/></svg>
                                            <span class="text-xs font-medium">${ "{!! __('To Warehouse') !!}" }</span>
                                        </button>` : ''
                                }
                                ${
                                    (IS_PRODUCTION && row.status == 8) || (!IS_PRODUCTION && row.status == 11) ? `
                                        <button class="rounded-full py-2 px-3 bg-red-200 flex items-center gap-x-1 reject-btns" data-id="${row.id}" data-serial-no="${row.sku}">
                                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512" height="512"><path d="M18,6h0a1,1,0,0,0-1.414,0L12,10.586,7.414,6A1,1,0,0,0,6,6H6A1,1,0,0,0,6,7.414L10.586,12,6,16.586A1,1,0,0,0,6,18H6a1,1,0,0,0,1.414,0L12,13.414,16.586,18A1,1,0,0,0,18,18h0a1,1,0,0,0,0-1.414L13.414,12,18,7.414A1,1,0,0,0,18,6Z"/></svg>
                                            <span class="text-xs font-medium">${ "{!! __('Reject') !!}" }</span>
                                        </button>
                                        <button class="rounded-full py-2 px-3 bg-green-200 flex items-center gap-x-2 accept-btns" data-id="${row.id}" data-serial-no="${row.sku}">
                                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512" height="512"><path d="M22.319,4.431,8.5,18.249a1,1,0,0,1-1.417,0L1.739,12.9a1,1,0,0,0-1.417,0h0a1,1,0,0,0,0,1.417l5.346,5.345a3.008,3.008,0,0,0,4.25,0L23.736,5.847a1,1,0,0,0,0-1.416h0A1,1,0,0,0,22.319,4.431Z"/></svg>
                                            <span class="text-xs font-medium">${ "{!! __('Accept') !!}" }</span>
                                        </button>` : ''
                                }
                            </div>`
                        }
                    },
                ],
                ajax: {
                    data: function() {
                        var info = $('#in-transit-table').DataTable().page.info();
                        var url = "{{ route('product.view_get_data') }}"

                        url =
                            `${url}?page=${ info.page + 1 }&product_id=${ PRODUCT.id }&is_production=${IS_PRODUCTION}&table-type=in-transit`
                        $('#in-transit-table').DataTable().ajax.url(url);
                    },
                },
                fnCreatedRow: function(nRow, aData, iDataIndex) {
                    if (CHECKED_CHECKBOXES.includes(aData.id)) {
                        dt.$(`.checkboxes[data-id="${ aData.id }"]`).prop('checked', true)
                    }
                },
            });
        }
        $('#filter_search').on('keyup', function() {
            dt.search($(this).val()).draw()
        })
        $('#filter_table_type').on('change', function() {
            let value = $(this).val()

            if (IS_PRODUCT) {
                if (IS_PRODUCTION) {
                    window.location.href = `{{ route('production_finish_good.view', ['product' => $prod->id]) }}?table-type=${value}`
                } else {
                    window.location.href = `{{ route('product.view', ['product' => $prod->id]) }}?table-type=${value}`
                }
            } else {
                if (IS_PRODUCTION) {
                    window.location.href = `{{ route('production_material.view', ['product' => $prod->id]) }}?table-type=${value}`
                } else {
                    window.location.href = `{{ route('raw_material.view', ['product' => $prod->id]) }}?table-type=${value}`
                }
            }
        })

        $('body').on('click', '.reject-btns', function() {
            let productChildId = $(this).data('id')
            let serialNo = $(this).data('serial-no')

            $('#approval-reject-modal').addClass('show-modal')

            let url = "{{ config('app.url') }}"
            url = `${url}/inventory-category/reject-production-stock-out/${productChildId}`
            $('#approval-reject-modal #yes-btn').attr('href', url)
        })
        $('body').on('click', '.accept-btns', function() {
            let productChildId = $(this).data('id')
            let serialNo = $(this).data('serial-no')

            $('#confirmation-modal #msg').text(`{!! __('Are you sure you want to accept the serial no') !!} ${serialNo}?`)
            $('#confirmation-modal').addClass('show-modal')

            let url = "{{ config('app.url') }}"
            url = `${url}/inventory-category/accept-production-stock-out/${productChildId}`
            $('#confirmation-modal #yes-btn').attr('href', url)
        })
        $('#confirmation-modal #yes-btn').on('click', function() {
            let url = $(this).attr('href')

            window.location.href = url
        })
        $('#approval-reject-modal #yes-btn').on('click', function() {
            let url = $(this).attr('href')
            let remark = $('#approval-reject-modal textarea[name="remark"]').val()

            window.location.href = `${url}?remark=${encodeURIComponent(remark)}`
        })
        $('body').on('click', '.stock-in-btns', function() {
            let productChildId = $(this).data('id')
            let serialNo = $(this).data('serial-no')
            let orderId = $(this).data('order-id')

            $('#stock-in-modal #date').text(moment().format('D MMM YYYY HH:mm'))
            $('#stock-in-modal #serial-no').text(serialNo)
            $('#stock-in-modal #order-id').text(orderId ?? '-')
            $('#stock-in-modal').addClass('show-modal')

            let url = "{{ config('app.url') }}"
            url = `${url}/inventory-category/stock-in/${productChildId}`
            $('#stock-in-modal #yes-btn').attr('href', url)
        })
        $('body').on('click', '.stock-out-btns', function() {
            let productChildId = $(this).data('id')
            let serialNo = $(this).data('serial-no')
            let modal = PRODUCT.company_group == 2 ? 'stock-out-modal' : 'stock-out-to-hi-ten-modal'

            $(`#${modal} #date`).text(moment().format('D MMM YYYY HH:mm'))
            $(`#${modal} #serial-no`).text(serialNo)
            if (PRODUCT.company_group == 1) {
                $(`#${modal} #stock-out-to`).text(PRODUCT.model_name)
                $(`#${modal} #yes-btn`).attr('href',
                    `{{ config('app.url') }}/inventory-category/stock-out/${productChildId}`)
            }
            $(`#${modal}`).addClass('show-modal')

            let url = "{{ config('app.url') }}"
            url = `${url}/inventory-category/stock-out/${productChildId}`
            $('#stock-out-modal #yes-btn').attr('href', url)
        })
        $('body').on('click', '.transfer-btns', function() {
            let productChildId = $(this).data('id')
            let serialNo = $(this).data('serial-no')
            let orderId = $(this).data('order-id')

            $('#transfer-modal #date').text(moment().format('D MMM YYYY HH:mm'))
            $('#transfer-modal #serial-no').text(serialNo)
            $('#transfer-modal #order-id').text(orderId ?? '-')
            $('#transfer-modal select').val(null)
            $('#transfer-modal #yes-btn').addClass('hidden')
            $('#transfer-modal').addClass('show-modal')

            let url = "{{ config('app.url') }}"
            url = `${url}/inventory-category/transfer/${productChildId}`
            $('#transfer-modal #yes-btn').attr('href', url)
        })
        $('body').on('click', '.to-warehouse-btns', function() {
            let productChildId = $(this).data('id')

            let url = "{{ config('app.url') }}"
            url = `${url}/inventory-category/to-warehouse/${productChildId}`

            window.location.href = url
        })
        $('body').on('click', '.checkboxes', function() {
            let id = $(this).data('id')

            if (CHECKED_CHECKBOXES.includes(id)) {
                const index = CHECKED_CHECKBOXES.indexOf(id);
                if (index > -1) {
                    CHECKED_CHECKBOXES.splice(index, 1);
                }
            } else {
                CHECKED_CHECKBOXES.push(id)
            }
        })
        $('#generate-barcode-btn').on('click', function(e) {
            e.preventDefault()

            if (CHECKED_CHECKBOXES.length <= 0) return

            let url = $(this).attr('href')
            url = `${url}?ids=${CHECKED_CHECKBOXES}`

            window.location.href = url
        })
        $('#select-all-checkbox').on('change', function() {
            const isChecked = $(this).is(':checked')

            $('.checkboxes').each(function() {
                const id = $(this).data('id')

                if (isChecked) {
                    if (!CHECKED_CHECKBOXES.includes(id)) {
                        CHECKED_CHECKBOXES.push(id)
                    }
                } else {
                    const index = CHECKED_CHECKBOXES.indexOf(id);
                    if (index > -1) {
                        CHECKED_CHECKBOXES.splice(index, 1);
                    }
                }

                $(this).prop('checked', isChecked)
            })
        })
    </script>
@endpush
