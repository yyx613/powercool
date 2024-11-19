@extends('layouts.app')

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
        <x-app.page-title url="{{ $is_product ? route('product.index') : route('raw_material.index') }}">{{ $is_product ? __('View Product') : __('View Raw Material') }}</x-app.page-title>
    </div>
    @include('components.app.alert.parent')
    <!-- Summary -->
    <div class="mb-6">
        <div class="flex gap-4">
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

    <!-- Product -->
    <div class="mb-6 flex gap-x-4">
        @if ($prod->image != null)
            <div class="h-12 w-12 rounded overflow-hidden">
                <img src="{{ $prod->image->url }}" alt="{{ $prod->model_name }}" class="w-full h-full object-contain">
            </div>
        @endif
        <div class="flex flex-col flex-1">
            <div class="flex items-center mb-2 gap-x-4">
                <h1 class="text-lg font-semibold leading-none">{{ $prod->model_name }}</h1>
                <span class="text-xs font-semibold py-1 px-3 rounded-full {{ $prod->is_active ? 'bg-green-300' : 'bg-red-300' }}">{{ $prod->is_active ? 'Active' : 'Inactive' }}</span>
            </div>
            <div class="flex gap-x-4 mb-1">
                <span class="text-xs font-semibold text-slate-500">{{ __('Code') }}: {{ $prod->sku }}</span>
                <span class="text-xs font-semibold text-slate-500">{{ __('Category') }}: {{ $prod->category->name }}</span>
            </div>
            @if ($prod->length != null || $prod->width != null || $prod->height != null || $prod->weight != null)
                <div class="flex gap-x-4 mb-1">
                    @if ($prod->length != null)
                        <span class="text-xs font-semibold text-slate-500">{{ __('Length') }}: {{ $prod->length }} CM</span>
                    @endif
                    @if ($prod->width != null)
                        <span class="text-xs font-semibold text-slate-500">{{ __('Width') }}: {{ $prod->width }} CM</span>
                    @endif
                    @if ($prod->height != null)
                        <span class="text-xs font-semibold text-slate-500">{{ __('Height') }}: {{ $prod->height }} CM</span>
                    @endif
                    @if ($prod->weight != null)
                        <span class="text-xs font-semibold text-slate-500">{{ __('Weight') }}: {{ $prod->weight }} KG</span>
                    @endif
                </div>
            @endif
            <div class="border-t mt-2.5 pt-2.5">
                <span class="text-xs text-slate-500">{{ $prod->model_desc }}</span>
            </div>
        </div>
    </div>

    <div class="flex justify-end mb-4">
        <div class="rounded-md overflow-hidden flex" id="switch-view-btn">
            <button type="button" class="text-sm p-2 font-medium bg-emerald-200" id="view-list-btn">{{ __('View List') }}</button>
            <button type="button" class="text-sm p-2 font-medium bg-slate-200" id="view-cost-btn">{{ __('View Cost') }}</button>
        </div>
    </div>
    
    <div id="data-table-container">
        @if ($is_product || $prod->is_sparepart == true)
            <div>
                <!-- Filters -->
                <div class="flex items-center max-w-screen-sm gap-x-4 w-full mb-4">
                    <div class="flex-1">
                        <x-app.input.input name="filter_search" id="filter_search" class="flex items-center" placeholder="{{ __('Search') }}">
                            <div class="rounded-md border border-transparent p-1 ml-1">
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24"><path d="M23.707,22.293l-5.969-5.969a10.016,10.016,0,1,0-1.414,1.414l5.969,5.969a1,1,0,0,0,1.414-1.414ZM10,18a8,8,0,1,1,8-8A8.009,8.009,0,0,1,10,18Z"/></svg>
                            </div>
                        </x-app.input.input>
                    </div>
                    <div>
                        <button type="button" class="flex items-center gap-x-4 bg-sky-300 p-2 rounded w-fit" id="scanner-btn">
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24">
                                <path d="m24,11.5c0,.829-.671,1.5-1.5,1.5H1.5c-.829,0-1.5-.671-1.5-1.5s.671-1.5,1.5-1.5h21c.829,0,1.5.671,1.5,1.5ZM1.5,8c.829,0,1.5-.671,1.5-1.5v-1c0-1.378,1.122-2.5,2.5-2.5h1c.829,0,1.5-.671,1.5-1.5s-.671-1.5-1.5-1.5h-1C2.467,0,0,2.467,0,5.5v1c0,.829.671,1.5,1.5,1.5Zm5,13h-1c-1.378,0-2.5-1.122-2.5-2.5v-1c0-.829-.671-1.5-1.5-1.5s-1.5.671-1.5,1.5v1c0,3.033,2.467,5.5,5.5,5.5h1c.829,0,1.5-.671,1.5-1.5s-.671-1.5-1.5-1.5Zm16-5c-.829,0-1.5.671-1.5,1.5v1c0,1.378-1.122,2.5-2.5,2.5h-1c-.829,0-1.5.671-1.5,1.5s.671,1.5,1.5,1.5h1c3.033,0,5.5-2.467,5.5-5.5v-1c0-.829-.671-1.5-1.5-1.5ZM18.5,0h-1c-.829,0-1.5.671-1.5,1.5s.671,1.5,1.5,1.5h1c1.378,0,2.5,1.122,2.5,2.5v1c0,.829.671,1.5,1.5,1.5s1.5-.671,1.5-1.5v-1c0-3.033-2.467-5.5-5.5-5.5Z"/>
                            </svg>
                            <span class="font-medium">{{ __('Scan Barcode') }}</span>
                        </button>
                    </div>
                    <div class="flex-1">
                        <a href="{{ $is_product ? route('product.generate_barcode') : route('raw_material.generate_barcode') }}" class="flex items-center gap-x-4 bg-sky-200 p-2 rounded w-fit" id="generate-barcode-btn">
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M5,18c-.553,0-1-.448-1-1V7c0-.552,.447-1,1-1s1,.448,1,1v10c0,.552-.447,1-1,1Zm5-1V7c0-.552-.447-1-1-1s-1,.448-1,1v10c0,.552,.447,1,1,1s1-.448,1-1Zm10,0V7c0-.552-.447-1-1-1s-1,.448-1,1v10c0,.552,.447,1,1,1s1-.448,1-1Zm-6-.5V7.5c0-.829-.672-1.5-1.5-1.5s-1.5,.671-1.5,1.5v9c0,.829,.672,1.5,1.5,1.5s1.5-.671,1.5-1.5Zm-7,4.5c0-.552-.447-1-1-1h-2c-1.103,0-2-.897-2-2v-2c0-.552-.447-1-1-1s-1,.448-1,1v2c0,2.206,1.794,4,4,4h2c.553,0,1-.448,1-1Zm17-3v-2c0-.552-.447-1-1-1s-1,.448-1,1v2c0,1.103-.897,2-2,2h-2c-.553,0-1,.448-1,1s.447,1,1,1h2c2.206,0,4-1.794,4-4Zm0-10v-2c0-2.206-1.794-4-4-4h-2c-.553,0-1,.448-1,1s.447,1,1,1h2c1.103,0,2,.897,2,2v2c0,.552,.447,1,1,1s1-.448,1-1Zm-22,0v-2c0-1.103,.897-2,2-2h2c.553,0,1-.448,1-1s-.447-1-1-1h-2C1.794,2,0,3.794,0,6v2c0,.552,.447,1,1,1s1-.448,1-1Zm13.5,10h0c-.276,0-.5-.224-.5-.5V6.5c0-.276,.224-.5,.5-.5h0c.276,0,.5,.224,.5,.5v11c0,.276-.224,.5-.5,.5Z"/></svg>
                            <span class="font-medium">{{ __('Generate Barcode') }}</span>
                        </a>
                    </div>
                </div>
    
                <!-- Table -->
                <table id="data-table" class="text-sm rounded-lg overflow-hidden" style="width: 100%;">
                    <thead>
                        <tr>
                            <th></th>
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
    
            <x-app.modal.stock-in-modal/>
            <x-app.modal.stock-out-modal/>
            <x-app.modal.transfer-modal/>
        @else
            <!-- Table -->
            <table id="data-table" class="text-sm rounded-lg overflow-hidden" style="width: 100%;">
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
        @endif
    </div>

    <!-- Cost Table -->
    <div id="cost-table-container" class="hidden">
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
@endsection

@push('scripts')
    <script>
        PRODUCT = @json($prod ?? null);
        CHECKED_CHECKBOXES = []

        if (PRODUCT.is_sparepart == false) {
            // Datatable
            var dt = new DataTable('#data-table', {
                dom: 'rtip',
                pagingType: 'numbers',
                pageLength: 10,
                processing: true,
                serverSide: true,
                order: [],
                columns: [
                    { data: 'order_id' },
                    { data: 'qty' },
                    { data: 'on_hold' },
                    { data: 'at' },
                ],
                columnDefs: [
                    {
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
                    data: function(){
                        var info = $('#data-table').DataTable().page.info();
                        var url = "{{ route('product.view_get_data_raw_material') }}"

                        url = `${url}?page=${ info.page + 1 }&product_id=${ PRODUCT.id }`
                        $('#data-table').DataTable().ajax.url(url);
                    },
                },
            });
        } else {
            // Datatable
            var dt = new DataTable('#data-table', {
                dom: 'rtip',
                pagingType: 'numbers',
                pageLength: 10,
                processing: true,
                serverSide: true,
                order: [],
                columns: [
                    { data: 'checkbox' },
                    { data: 'sku' },
                    { data: 'location' },
                    { data: 'order_id' },
                    { data: 'status' },
                    { data: 'done_by' },
                    { data: 'action' },
                ],
                columnDefs: [
                    {
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
                            if (data != null) {
                                return data.sku
                            }
                            return data ?? '-'
                        }
                    },
                    {
                        "width": "5%",
                        "targets": 4,
                        orderable: false,
                        render: function(data, type, row) {
                            console.debug(row)
                            switch (data) {
                                case 1:
                                    return `{!! __('Stocked Out') !!} (${row.stock_out_to == null ? '' : row.stock_out_to.name})`
                                case 2:
                                    return "{!! __('In Transit') !!}"
                                case 3:
                                    return "{!! __('To Be Received') !!}"
                                case 4:
                                    return "{!! __('Received') !!}"
                            }
                            return data ?? '-'
                        }
                    },
                    {
                        "width": "5%",
                        "targets": 5,
                        orderable: false,
                        render: function(data, type, row) {
                            if (data != null) return `<span class="text-sm font-semibold">${data.name}</span><br><span class="text-xs">${row.done_at}</span>`
                            return data
                        }
                    },
                    {
                        "width": "5%",
                        "targets": 6,
                        orderable: false,
                        render: function (data, type, row) {
                            return  `<div class="flex items-center justify-end gap-x-2 px-2">
                                ${
                                    row.progress >= 100 || row.status == 3 ? `
                                        <button class="rounded-full py-2 px-3 bg-purple-200 flex items-center gap-x-2 stock-in-btns" data-id="${row.id}" data-serial-no="${row.sku}" data-order-id="${row != null && row.order_id != null ? row.order_id.sku : row.order_id}">
                                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M21,12h-3c-1.103,0-2,.897-2,2s-.897,2-2,2h-4c-1.103,0-2-.897-2-2s-.897-2-2-2H3c-1.654,0-3,1.346-3,3v4c0,2.757,2.243,5,5,5h14c2.757,0,5-2.243,5-5v-4c0-1.654-1.346-3-3-3Zm1,7c0,1.654-1.346,3-3,3H5c-1.654,0-3-1.346-3-3v-4c0-.552,.448-1,1-1l3-.002v.002c0,2.206,1.794,4,4,4h4c2.206,0,4-1.794,4-4h3c.552,0,1,.448,1,1v4ZM7.293,7.121c-.391-.391-.391-1.023,0-1.414s1.023-.391,1.414,0l2.293,2.293V1c0-.553,.447-1,1-1s1,.447,1,1v7l2.293-2.293c.391-.391,1.023-.391,1.414,0s.391,1.023,0,1.414l-3.293,3.293c-.387,.387-.896,.582-1.405,.584l-.009,.002-.009-.002c-.509-.002-1.018-.197-1.405-.584l-3.293-3.293Z"/></svg>
                                            <span class="text-xs font-medium">${ "{!! __('Stock In') !!}" }</span>
                                        </button>` : ''
                                }
                                ${
                                    row.location == 'factory' || row.status != null ? '' : `
                                        <button class="rounded-full py-2 px-3 bg-orange-200 flex items-center gap-x-2 stock-out-btns" data-id="${row.id}" data-serial-no="${row.sku}">
                                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M21,12h-3c-1.103,0-2,.897-2,2s-.897,2-2,2h-4c-1.103,0-2-.897-2-2s-.897-2-2-2H3c-1.654,0-3,1.346-3,3v4c0,2.757,2.243,5,5,5h14c2.757,0,5-2.243,5-5v-4c0-1.654-1.346-3-3-3Zm1,7c0,1.654-1.346,3-3,3H5c-1.654,0-3-1.346-3-3v-4c0-.552,.448-1,1-1l3-.002v.002c0,2.206,1.794,4,4,4h4c2.206,0,4-1.794,4-4h3c.552,0,1,.448,1,1v4ZM7.293,5.293c-.391-.391-.391-1.023,0-1.414L10.586,.586C10.972,.2,11.479,.006,11.986,.003l.014-.003,.014,.003c.508,.003,1.014,.197,1.4,.583l3.293,3.293c.391,.391,.391,1.023,0,1.414-.195,.195-.451,.293-.707,.293s-.512-.098-.707-.293l-2.293-2.293v7c0,.553-.447,1-1,1s-1-.447-1-1V3l-2.293,2.293c-.391,.391-1.023,.391-1.414,0Z"/></svg>
                                            <span class="text-xs font-medium">${ "{!! __('Stock Out') !!}" }</span>
                                        </button>`
                                }
                                ${
                                    row.status != null ? '' : `
                                        <button class="rounded-full py-2 px-3 bg-emerald-200 flex items-center gap-x-2 transfer-btns" data-id="${row.id}" data-serial-no="${row.sku}" data-order-id="${row != null && row.order_id != null ? row.order_id.sku : row.order_id}">
                                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M24,12.649a5,5,0,0,0-.256-1.581L22.405,7.051A3,3,0,0,0,19.559,5H17V4a3,3,0,0,0-3-3H3A3,3,0,0,0,0,4V19.5a3.517,3.517,0,0,0,6,2.447A3.517,3.517,0,0,0,12,19.5V19h3v.5a3.5,3.5,0,0,0,7,0V19h2ZM19.559,7a1,1,0,0,1,.948.684L21.613,11H17V7ZM2,4A1,1,0,0,1,3,3H14a1,1,0,0,1,1,1V17H2ZM3.5,21A1.5,1.5,0,0,1,2,19.5V19H5v.5A1.5,1.5,0,0,1,3.5,21ZM10,19.5a1.5,1.5,0,0,1-3,0V19h3Zm10,0a1.5,1.5,0,0,1-3,0V19h3ZM17,17V13h5v4Z"/></svg>
                                            <span class="text-xs font-medium">${ "{!! __('Transfer') !!}" }</span>
                                        </button>`
                                }
                            </div>`
                        }
                    },
                ],
                ajax: {
                    data: function(){
                        var info = $('#data-table').DataTable().page.info();
                        var url = "{{ route('product.view_get_data') }}"

                        url = `${url}?page=${ info.page + 1 }&product_id=${ PRODUCT.id }`
                        $('#data-table').DataTable().ajax.url(url);
                    },
                },
                fnCreatedRow: function( nRow, aData, iDataIndex ) {
                    if (CHECKED_CHECKBOXES.includes(aData.id)) {
                        dt.$(`.checkboxes[data-id="${ aData.id }"]`).prop('checked', true)
                    }
                },
            });
        }
       
        $('#filter_search').on('keyup', function() {
            dt.search($(this).val()).draw()
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

            $('#stock-out-modal #date').text(moment().format('D MMM YYYY HH:mm'))
            $('#stock-out-modal #serial-no').text(serialNo)
            $('#stock-out-modal').addClass('show-modal')

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

        // Cost Table
        var costDT = new DataTable('#cost-table', {
            dom: 'rtip',
            pagingType: 'numbers',
            pageLength: 10,
            processing: true,
            serverSide: true,
            order: [],
            columns: [
                { data: 'qty_sku' },
                { data: 'unit_price' },
                { data: 'total_price' },
                { data: 'at' },
            ],
            columnDefs: [
                {
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
                data: function(){
                    var info = $('#cost-table').DataTable().page.info();
                    var url = "{{ route('product.view_get_data_cost') }}"

                    url = `${url}?page=${ info.page + 1 }&product_id=${ PRODUCT.id }`
                    $('#cost-table').DataTable().ajax.url(url);
                },
            },
        });

        $('#switch-view-btn').on('click', function() {
            $('#view-list-btn, #view-cost-btn').toggleClass('bg-emerald-200 bg-slate-200')

            $('#data-table-container').toggleClass('hidden')
            $('#cost-table-container').toggleClass('hidden')
        })

        $('#scanner-btn').on('click', function() {
            $('input[name="filter_search"]').focus()
        })
    </script>
@endpush
