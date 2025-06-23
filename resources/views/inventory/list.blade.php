@extends('layouts.app')

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
        @php
            $title = 'Finish Good';
            if (!$is_product) {
                $title = 'Raw Material';
            }
            if ($is_production) {
                $title = 'Production ' . $title;
            }
        @endphp

        <x-app.page-title class="mb-4 md:mb-0">{{ __($title) }}</x-app.page-title>
        <div class="flex gap-4">
            <x-app.button.button class="shadow gap-x-2 bg-emerald-300 !p-2" id="export-btn">
                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24"
                    width="512" height="512">
                    <path
                        d="M18.66,20.9c-.41-.37-1.05-.33-1.41,.09-.57,.65-1.39,1.02-2.25,1.02H5c-1.65,0-3-1.35-3-3V5c0-1.65,1.35-3,3-3h4.51c.16,0,.33,0,.49,.02V7c0,1.65,1.35,3,3,3h5.81c.31,0,.6-.14,.79-.39s.25-.56,.18-.86c-.31-1.22-.94-2.33-1.83-3.22l-3.48-3.48c-1.32-1.32-3.08-2.05-4.95-2.05H5C2.24,0,0,2.24,0,5v14c0,2.76,2.24,5,5,5H15c1.43,0,2.8-.62,3.75-1.69,.37-.41,.33-1.05-.09-1.41ZM12,2.66c.38,.22,.73,.49,1.05,.81l3.48,3.48c.31,.31,.58,.67,.8,1.05h-4.34c-.55,0-1-.45-1-1V2.66Zm11.13,15.43l-1.61,1.61c-.2,.2-.45,.29-.71,.29s-.51-.1-.71-.29c-.39-.39-.39-1.02,0-1.41l1.29-1.29h-7.4c-.55,0-1-.45-1-1s.45-1,1-1h7.4l-1.29-1.29c-.39-.39-.39-1.02,0-1.41s1.02-.39,1.41,0l1.61,1.61c1.15,1.15,1.15,3.03,0,4.19Z" />
                </svg>
                {{ __('Export Excel') }}
            </x-app.button.button>
            @if ($is_product && !$is_production)
                @can('inventory.product.create')
                    <a href="{{ route('product.create') }}"
                        class="bg-yellow-400 shadow rounded-md py-2 px-4 flex items-center gap-x-2">
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                            version="1.1" id="Capa_1" x="0px" y="0px" viewBox="0 0 512 512"
                            style="enable-background:new 0 0 512 512;" xml:space="preserve" width="512" height="512">
                            <path
                                d="M480,224H288V32c0-17.673-14.327-32-32-32s-32,14.327-32,32v192H32c-17.673,0-32,14.327-32,32s14.327,32,32,32h192v192   c0,17.673,14.327,32,32,32s32-14.327,32-32V288h192c17.673,0,32-14.327,32-32S497.673,224,480,224z" />
                        </svg>
                        {{ __('New') }}
                    </a>
                @endcan
            @elseif (!$is_product && !$is_production)
                @can('inventory.raw_material.create')
                    <a href="{{ route('raw_material.create') }}"
                        class="bg-yellow-400 shadow rounded-md py-2 px-4 flex items-center gap-x-2">
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                            version="1.1" id="Capa_1" x="0px" y="0px" viewBox="0 0 512 512"
                            style="enable-background:new 0 0 512 512;" xml:space="preserve" width="512" height="512">
                            <path
                                d="M480,224H288V32c0-17.673-14.327-32-32-32s-32,14.327-32,32v192H32c-17.673,0-32,14.327-32,32s14.327,32,32,32h192v192   c0,17.673,14.327,32,32,32s32-14.327,32-32V288h192c17.673,0,32-14.327,32-32S497.673,224,480,224z" />
                        </svg>
                        {{ __('New') }}
                    </a>
                @endcan
            @endif
        </div>
    </div>
    @include('components.app.alert.parent')
    <div>
        <!-- Filters -->
        <div class="flex items-center gap-x-4 max-w-screen-sm w-full mb-4">
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
            <div class="{{ !$is_product ? '' : 'flex-1' }}">
                <x-app.qr-scanner />
            </div>
            @if (!$is_production && !$is_product)
                @if ($type != 'waiting')
                    <a href="{{ route('raw_material.index') }}?type=waiting"
                        class="bg-green-200 shadow rounded-md py-2 px-2 flex items-center gap-x-2">
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1"
                            viewBox="0 0 24 24">
                            <path
                                d="m8,11h-3c-.552,0-1-.448-1-1s.448-1,1-1h3c.552,0,1,.448,1,1s-.448,1-1,1Zm15.759,12.651c-.198.23-.478.349-.76.349-.23,0-.462-.079-.65-.241l-2.509-2.151c-1.041.868-2.379,1.391-3.84,1.391-3.314,0-6-2.686-6-6s2.686-6,6-6,6,2.686,6,6c0,1.13-.318,2.184-.862,3.087l2.513,2.154c.419.359.468.991.108,1.41Zm-6.78-4.325l2.703-2.614c.398-.383.411-1.016.029-1.414-.383-.399-1.017-.41-1.414-.029l-2.713,2.624c-.143.141-.379.144-.522.002l-1.354-1.331c-.396-.388-1.028-.381-1.414.014-.387.395-.381,1.027.014,1.414l1.354,1.332c.46.449,1.062.674,1.663.674s1.201-.225,1.653-.671Zm-5.979,3.674c0,.552-.448,1-1,1h-5c-2.757,0-5-2.243-5-5V5C0,2.243,2.243,0,5,0h4.515c1.87,0,3.627.728,4.95,2.05l3.485,3.485c.888.888,1.521,2,1.833,3.217.077.299.011.617-.179.861s-.481.387-.79.387h-5.813c-1.654,0-3-1.346-3-3V2.023c-.16-.015-.322-.023-.485-.023h-4.515c-1.654,0-3,1.346-3,3v14c0,1.654,1.346,3,3,3h5c.552,0,1,.448,1,1Zm1-16c0,.551.449,1,1,1h4.338c-.219-.382-.489-.736-.803-1.05l-3.485-3.485c-.318-.318-.671-.587-1.05-.806v4.341Zm-5,6h-2c-.552,0-1,.448-1,1s.448,1,1,1h2c.552,0,1-.448,1-1s-.448-1-1-1Zm0,4h-2c-.552,0-1,.448-1,1s.448,1,1,1h2c.552,0,1-.448,1-1s-.448-1-1-1Z" />
                        </svg>
                        {{ __('Waiting List') }}
                    </a>
                @else
                    <a href="{{ route('raw_material.index') }}"
                        class="bg-slate-200 shadow rounded-md py-2 px-2 flex items-center gap-x-2">
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1"
                            viewBox="0 0 24 24">
                            <path
                                d="m8,11h-3c-.552,0-1-.448-1-1s.448-1,1-1h3c.552,0,1,.448,1,1s-.448,1-1,1Zm15.759,12.651c-.198.23-.478.349-.76.349-.23,0-.462-.079-.65-.241l-2.509-2.151c-1.041.868-2.379,1.391-3.84,1.391-3.314,0-6-2.686-6-6s2.686-6,6-6,6,2.686,6,6c0,1.13-.318,2.184-.862,3.087l2.513,2.154c.419.359.468.991.108,1.41Zm-6.78-4.325l2.703-2.614c.398-.383.411-1.016.029-1.414-.383-.399-1.017-.41-1.414-.029l-2.713,2.624c-.143.141-.379.144-.522.002l-1.354-1.331c-.396-.388-1.028-.381-1.414.014-.387.395-.381,1.027.014,1.414l1.354,1.332c.46.449,1.062.674,1.663.674s1.201-.225,1.653-.671Zm-5.979,3.674c0,.552-.448,1-1,1h-5c-2.757,0-5-2.243-5-5V5C0,2.243,2.243,0,5,0h4.515c1.87,0,3.627.728,4.95,2.05l3.485,3.485c.888.888,1.521,2,1.833,3.217.077.299.011.617-.179.861s-.481.387-.79.387h-5.813c-1.654,0-3-1.346-3-3V2.023c-.16-.015-.322-.023-.485-.023h-4.515c-1.654,0-3,1.346-3,3v14c0,1.654,1.346,3,3,3h5c.552,0,1,.448,1,1Zm1-16c0,.551.449,1,1,1h4.338c-.219-.382-.489-.736-.803-1.05l-3.485-3.485c-.318-.318-.671-.587-1.05-.806v4.341Zm-5,6h-2c-.552,0-1,.448-1,1s.448,1,1,1h2c.552,0,1-.448,1-1s-.448-1-1-1Zm0,4h-2c-.552,0-1,.448-1,1s.448,1,1,1h2c.552,0,1-.448,1-1s-.448-1-1-1Z" />
                        </svg>
                        {{ __('Waiting List') }}
                    </a>
                @endif
            @endif
            @if ($is_production && !$is_product)
                @if ($type != 'usage')
                    <a href="{{ route('production_material.index') }}?type=usage"
                        class="bg-green-200 shadow rounded-md py-2 px-2 flex items-center gap-x-2">
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1"
                            viewBox="0 0 24 24">
                            <path
                                d="m12,0C5.383,0,0,5.383,0,12s5.383,12,12,12,12-5.383,12-12S18.617,0,12,0Zm0,22c-5.514,0-10-4.486-10-10S6.486,2,12,2s10,4.486,10,10-4.486,10-10,10Zm3-15c0-1.103-.897-2-2-2h-2c-1.103,0-2,.897-2,2v1h-3v2h1v6c0,1.654,1.346,3,3,3h4c1.654,0,3-1.346,3-3v-6h1v-2h-3v-1Zm-4,0h2v1h-2v-1Zm4,9c0,.551-.448,1-1,1h-4c-.552,0-1-.449-1-1v-6h6v6Z" />
                        </svg>
                        {{ __('Usage') }}
                    </a>
                @else
                    <a href="{{ route('production_material.index') }}"
                        class="bg-slate-200 shadow rounded-md py-2 px-2 flex items-center gap-x-2">
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1"
                            viewBox="0 0 24 24">
                            <path
                                d="m12,0C5.383,0,0,5.383,0,12s5.383,12,12,12,12-5.383,12-12S18.617,0,12,0Zm0,22c-5.514,0-10-4.486-10-10S6.486,2,12,2s10,4.486,10,10-4.486,10-10,10Zm3-15c0-1.103-.897-2-2-2h-2c-1.103,0-2,.897-2,2v1h-3v2h1v6c0,1.654,1.346,3,3,3h4c1.654,0,3-1.346,3-3v-6h1v-2h-3v-1Zm-4,0h2v1h-2v-1Zm4,9c0,.551-.448,1-1,1h-4c-.552,0-1-.449-1-1v-6h6v6Z" />
                        </svg>
                        {{ __('Usage') }}
                    </a>
                @endif
            @endif
        </div>

        <!-- Table -->
        <table id="data-table" class="text-sm rounded-lg overflow-hidden" style="width: 100%;">
            <thead>
                <tr>
                    <th>{{ __('Product ID') }}</th>
                    <th>{{ __('Model Name') }}</th>
                    <th>{{ __('Category') }}</th>
                    <th>{{ __('Qty') }}</th>
                    @if ($is_production == false)
                        <th>{{ __('Price') }}</th>
                    @endif
                    @if ($is_product == false)
                        <th>{{ __('Is Spare part') }}</th>
                    @endif
                    @if ($is_production == false)
                        <th>{{ __('Status') }}</th>
                    @endif
                    <th></th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <x-app.modal.delete-modal />
    <x-app.modal.raw-material-transfer-modal />
@endsection

@push('scripts')
    <script>
        TYPE = @json($type ?? null);
        IS_PRODUCT = @json($is_product);
        IS_PRODUCTION = @json($is_production);
        PRODUCT_ID_TO_TRANSFER = null
        FRM_ID_TO_TRANSFER = null
        TRANSFER_TYPE = null

        var columns = [{
                data: 'sku'
            },
            {
                data: 'model_name'
            },
            {
                data: 'category'
            },
            {
                data: 'qty'
            },
            {
                data: 'price'
            },
            {
                data: 'is_sparepart'
            },
            {
                data: 'status'
            },
            {
                data: 'action'
            },
        ]
        var columnDefs = [{
                "width": "10%",
                "targets": 0,
                render: function(data, type, row) {
                    if (IS_PRODUCTION || !IS_PRODUCT) {
                        return data
                    }

                    if (row.ready_for_production == true) {
                        return `<div>
                                    <span>${data}</span><br>
                                    <div class="mt-1">
                                        <span class="text-xs rounded-full text-green-700">B.O.M Ready</span>
                                    </div>
                                </div>`
                    } else {
                        return `<div>
                                    <span>${data}</span><br>
                                    <div class="mt-1">
                                        <span class="text-xs rounded-full text-slate-400">B.O.M Not Ready</span>
                                    </div>
                                </div>`
                    }
                }
            },
            {
                "width": "15%",
                "targets": 1,
                render: function(data, type, row) {
                    return `
                        <div class="flex items-center gap-x-2">
                            <div>
                                ${
                                    row.image != null ? `<img src="${ row.image.url }" class="h-8 w-8 object-contain" />` :
                                        `<x-app.no-image-icon class="h-8 w-8"/>`
                                }
                            </div>
                            <span>${data}</span>
                        </div>
                    `
                }
            },
            {
                "width": "10%",
                "targets": 2,
                render: function(data, type, row) {
                    return data
                }
            },
            {
                "width": "5%",
                "targets": 3,
                render: function(data, type, row) {
                    return data
                }
            },
            {
                "width": "10%",
                "targets": 4,
                orderable: false,
                render: function(data, type, row) {
                    return `${row.min_price} - ${row.max_price}`
                }
            },
            {
                "width": "10%",
                "targets": 5,
                orderable: false,
                render: function(data, type, row) {
                    switch (data) {
                        case 0:
                            return 'No'
                        case 1:
                            return 'Yes'
                        default:
                            return ''
                    }
                }
            },
            {
                "width": '5%',
                "targets": 6,
                orderable: false,
                render: function(data, type, row) {
                    switch (data) {
                        case 0:
                            return "{!! __('Inactive') !!}"
                        case 1:
                            return "{!! __('Active') !!}"
                    }
                }
            },
            {
                "width": "5%",
                "targets": 7,
                orderable: false,
                render: function(data, type, row) {
                    if (TYPE == 'waiting' && row.is_sparepart == false) {
                        return `<div class="flex items-center justify-end gap-x-2 px-2">
                                    <a href="{{ config('app.url') }}/approval/stock-in/${row.approval_id}" class="rounded-full py-2 px-3 bg-purple-200 flex items-center gap-x-2 stock-in-btns" data-approval-id="${row.approval_id}">
                                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M21,12h-3c-1.103,0-2,.897-2,2s-.897,2-2,2h-4c-1.103,0-2-.897-2-2s-.897-2-2-2H3c-1.654,0-3,1.346-3,3v4c0,2.757,2.243,5,5,5h14c2.757,0,5-2.243,5-5v-4c0-1.654-1.346-3-3-3Zm1,7c0,1.654-1.346,3-3,3H5c-1.654,0-3-1.346-3-3v-4c0-.552,.448-1,1-1l3-.002v.002c0,2.206,1.794,4,4,4h4c2.206,0,4-1.794,4-4h3c.552,0,1,.448,1,1v4ZM7.293,7.121c-.391-.391-.391-1.023,0-1.414s1.023-.391,1.414,0l2.293,2.293V1c0-.553,.447-1,1-1s1,.447,1,1v7l2.293-2.293c.391-.391,1.023-.391,1.414,0s.391,1.023,0,1.414l-3.293,3.293c-.387,.387-.896,.582-1.405,.584l-.009,.002-.009-.002c-.509-.002-1.018-.197-1.405-.584l-3.293-3.293Z"/></svg>
                                        <span class="text-xs font-medium">${ "{!! __('Stock In') !!}" }</span>
                                    </a>
                                </div>`
                    }

                    return IS_PRODUCTION ?
                        `<div class="flex items-center justify-end gap-x-2 px-2">
                            ${
                                row.is_sparepart == true ?
                                    `<a href="{{ config('app.url') }}/${IS_PRODUCT ? 'production-finish-good' : 'production-material' }/view/${row.id}" class="rounded-full p-2 bg-green-200 inline-block" title="{!! __('View') !!}">
                                                                                                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512" height="512"><path d="M23.271,9.419C21.72,6.893,18.192,2.655,12,2.655S2.28,6.893.729,9.419a4.908,4.908,0,0,0,0,5.162C2.28,17.107,5.808,21.345,12,21.345s9.72-4.238,11.271-6.764A4.908,4.908,0,0,0,23.271,9.419Zm-1.705,4.115C20.234,15.7,17.219,19.345,12,19.345S3.766,15.7,2.434,13.534a2.918,2.918,0,0,1,0-3.068C3.766,8.3,6.781,4.655,12,4.655s8.234,3.641,9.566,5.811A2.918,2.918,0,0,1,21.566,13.534Z"/><path d="M12,7a5,5,0,1,0,5,5A5.006,5.006,0,0,0,12,7Zm0,8a3,3,0,1,1,3-3A3,3,0,0,1,12,15Z"/></svg>
                                                                                                            </a>` : ''
                            }
                            ${
                                row.is_sparepart == false ?
                                    `
                                                                                                            <button type="button" data-product-id="${row.id}" data-frm-id="${row.frm_id}" class="transfer-to-warehouse-btns rounded-full p-2 bg-purple-200 inline-block" title="{!! __('Transfer To Warehouse') !!}">
                                                                                                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M21.8,5.579,14.8.855A4.981,4.981,0,0,0,9.2.855l-7,4.724A4.992,4.992,0,0,0,0,9.724V19a5.006,5.006,0,0,0,5,5H19a5.006,5.006,0,0,0,5-5V9.724A4.993,4.993,0,0,0,21.8,5.579ZM18,22H6V13a2,2,0,0,1,2-2h8a2,2,0,0,1,2,2Zm4-3a3,3,0,0,1-2,2.828V13a4,4,0,0,0-4-4H8a4,4,0,0,0-4,4v8.828A3,3,0,0,1,2,19V9.724A3,3,0,0,1,3.322,7.237l7-4.723a2.983,2.983,0,0,1,3.356,0l7,4.723A3,3,0,0,1,22,9.724Zm-8,0a1,1,0,0,1-1,1H11a1,1,0,0,1,0-2h2A1,1,0,0,1,14,19Z"/></svg>
                                                                                                            </button>  
                                                                                                            <a href="{{ config('app.url') }}/production-material/record-usage/${row.frm_id}" class="rounded-full p-2 bg-sky-200 inline-block" title="{!! __('Record Usage') !!}">
                                                                                                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1"
                                                                                                                    viewBox="0 0 24 24">
                                                                                                                    <path
                                                                                                                        d="m12,0C5.383,0,0,5.383,0,12s5.383,12,12,12,12-5.383,12-12S18.617,0,12,0Zm0,22c-5.514,0-10-4.486-10-10S6.486,2,12,2s10,4.486,10,10-4.486,10-10,10Zm3-15c0-1.103-.897-2-2-2h-2c-1.103,0-2,.897-2,2v1h-3v2h1v6c0,1.654,1.346,3,3,3h4c1.654,0,3-1.346,3-3v-6h1v-2h-3v-1Zm-4,0h2v1h-2v-1Zm4,9c0,.551-.448,1-1,1h-4c-.552,0-1-.449-1-1v-6h6v6Z" />
                                                                                                                </svg>
                                                                                                            </a>` : ''
                            }
                        </div>` :
                        `<div class="flex items-center justify-end gap-x-2 px-2">
                            ${
                                !IS_PRODUCT && row.is_sparepart == false ? `
                                                                                                                        <button type="button" data-product-id="${row.id}" class="transfer-to-factory-btns rounded-full p-2 bg-purple-200 inline-block" title="{!! __('Transfer To Factory') !!}">
                                                                                                                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24">
                                                                                                                                <path d="m22.97,6.251c-.637-.354-1.415-.331-2.1.101l-4.87,3.649v-2.001c0-.727-.395-1.397-1.03-1.749-.637-.354-1.416-.331-2.1.101l-4.87,3.649V2c.553,0,1-.448,1-1s-.447-1-1-1H1C.447,0,0,.448,0,1s.447,1,1,1v17c0,2.757,2.243,5,5,5h13c2.757,0,5-2.243,5-5v-11c0-.727-.395-1.397-1.03-1.749Zm-.97,12.749c0,1.654-1.346,3-3,3H6c-1.654,0-3-1.346-3-3V2h3v9.991c0,.007,0,.014,0,.02v5.989c0,.552.447,1,1,1s1-.448,1-1v-5.5l6-4.5v4c0,.379.214.725.553.895s.743.134,1.047-.094l6.4-4.8v11Zm-8-2v1c0,.552-.448,1-1,1h-1c-.552,0-1-.448-1-1v-1c0-.552.448-1,1-1h1c.552,0,1,.448,1,1Zm2,1v-1c0-.552.448-1,1-1h1c.552,0,1,.448,1,1v1c0,.552-.448,1-1,1h-1c-.552,0-1-.448-1-1Z"/>
                                                                                                                            </svg>
                                                                                                                        </button>    
                                                                                                                        <a href="{{ route('raw_material.generate_barcode') }}?is_rm=true&id=${row.id}" class="rounded-full p-2 bg-sky-200 inline-block" title="{!! __('Generate Barcode') !!}">
                                                                                                                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M5,18c-.553,0-1-.448-1-1V7c0-.552,.447-1,1-1s1,.448,1,1v10c0,.552-.447,1-1,1Zm5-1V7c0-.552-.447-1-1-1s-1,.448-1,1v10c0,.552,.447,1,1,1s1-.448,1-1Zm10,0V7c0-.552-.447-1-1-1s-1,.448-1,1v10c0,.552,.447,1,1,1s1-.448,1-1Zm-6-.5V7.5c0-.829-.672-1.5-1.5-1.5s-1.5,.671-1.5,1.5v9c0,.829,.672,1.5,1.5,1.5s1.5-.671,1.5-1.5Zm-7,4.5c0-.552-.447-1-1-1h-2c-1.103,0-2-.897-2-2v-2c0-.552-.447-1-1-1s-1,.448-1,1v2c0,2.206,1.794,4,4,4h2c.553,0,1-.448,1-1Zm17-3v-2c0-.552-.447-1-1-1s-1,.448-1,1v2c0,1.103-.897,2-2,2h-2c-.553,0-1,.448-1,1s.447,1,1,1h2c2.206,0,4-1.794,4-4Zm0-10v-2c0-2.206-1.794-4-4-4h-2c-.553,0-1,.448-1,1s.447,1,1,1h2c1.103,0,2,.897,2,2v2c0,.552,.447,1,1,1s1-.448,1-1Zm-22,0v-2c0-1.103,.897-2,2-2h2c.553,0,1-.448,1-1s-.447-1-1-1h-2C1.794,2,0,3.794,0,6v2c0,.552,.447,1,1,1s1-.448,1-1Zm13.5,10h0c-.276,0-.5-.224-.5-.5V6.5c0-.276,.224-.5,.5-.5h0c.276,0,.5,.224,.5,.5v11c0,.276-.224,.5-.5,.5Z"/></svg>
                                                                                                                        </a>` : ''
                            }
                            <a href="{{ config('app.url') }}/${IS_PRODUCT ? 'product' : 'raw-material'}/create?id=${row.id}" class="rounded-full p-2 bg-yellow-200 inline-block" title="{!! __('Duplicate') !!}">
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="m19,0h-6c-2.757,0-5,2.243-5,5v6c0,2.757,2.243,5,5,5h6c2.757,0,5-2.243,5-5v-6c0-2.757-2.243-5-5-5Zm3,11c0,1.654-1.346,3-3,3h-6c-1.654,0-3-1.346-3-3v-6c0-1.654,1.346-3,3-3h6c1.654,0,3,1.346,3,3v6Zm-6,8c0,2.757-2.243,5-5,5h-6c-2.757,0-5-2.243-5-5v-6c0-2.757,2.243-5,5-5,.553,0,1,.448,1,1s-.447,1-1,1c-1.654,0-3,1.346-3,3v6c0,1.654,1.346,3,3,3h6c1.654,0,3-1.346,3-3,0-.552.447-1,1-1s1,.448,1,1Z"/></svg>
                            </a>
                            <a href="{{ config('app.url') }}/${IS_PRODUCT ? 'product' : 'raw-material'}/view/${row.id}" class="rounded-full p-2 bg-green-200 inline-block" title="{!! __('View') !!}">
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512" height="512"><path d="M23.271,9.419C21.72,6.893,18.192,2.655,12,2.655S2.28,6.893.729,9.419a4.908,4.908,0,0,0,0,5.162C2.28,17.107,5.808,21.345,12,21.345s9.72-4.238,11.271-6.764A4.908,4.908,0,0,0,23.271,9.419Zm-1.705,4.115C20.234,15.7,17.219,19.345,12,19.345S3.766,15.7,2.434,13.534a2.918,2.918,0,0,1,0-3.068C3.766,8.3,6.781,4.655,12,4.655s8.234,3.641,9.566,5.811A2.918,2.918,0,0,1,21.566,13.534Z"/><path d="M12,7a5,5,0,1,0,5,5A5.006,5.006,0,0,0,12,7Zm0,8a3,3,0,1,1,3-3A3,3,0,0,1,12,15Z"/></svg>
                            </a>
                            ${
                                row.can_edit ? `
                                                                                                                                                                    <a href="{{ config('app.url') }}/${IS_PRODUCT ? 'product' : 'raw-material'}/edit/${row.id}" class="rounded-full p-2 bg-blue-200 inline-block" title="{!! __('Edit') !!}">
                                                                                                                                                                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="m18.813,10c.309,0,.601-.143.79-.387s.255-.562.179-.861c-.311-1.217-.945-2.329-1.833-3.217l-3.485-3.485c-1.322-1.322-3.08-2.05-4.95-2.05h-4.515C2.243,0,0,2.243,0,5v14c0,2.757,2.243,5,5,5h3c.552,0,1-.448,1-1s-.448-1-1-1h-3c-1.654,0-3-1.346-3-3V5c0-1.654,1.346-3,3-3h4.515c.163,0,.325.008.485.023v4.977c0,1.654,1.346,3,3,3h5.813Zm-6.813-3V2.659c.379.218.732.488,1.05.806l3.485,3.485c.314.314.583.668.803,1.05h-4.338c-.551,0-1-.449-1-1Zm11.122,4.879c-1.134-1.134-3.11-1.134-4.243,0l-6.707,6.707c-.755.755-1.172,1.76-1.172,2.829v1.586c0,.552.448,1,1,1h1.586c1.069,0,2.073-.417,2.828-1.172l6.707-6.707c.567-.567.879-1.32.879-2.122s-.312-1.555-.878-2.121Zm-1.415,2.828l-6.708,6.707c-.377.378-.879.586-1.414.586h-.586v-.586c0-.534.208-1.036.586-1.414l6.708-6.707c.377-.378,1.036-.378,1.414,0,.189.188.293.439.293.707s-.104.518-.293.707Z"/></svg>
                                                                                                                                                                    </a>` : ''
                            }
                            ${
                                row.can_delete ? `
                                                                                                                                                                    <button class="rounded-full p-2 bg-red-200 inline-block delete-btns" data-id="${row.id}" title="{!! __('Delete') !!}">
                                                                                                                                                                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512" height="512"><path d="M21,4H17.9A5.009,5.009,0,0,0,13,0H11A5.009,5.009,0,0,0,6.1,4H3A1,1,0,0,0,3,6H4V19a5.006,5.006,0,0,0,5,5h6a5.006,5.006,0,0,0,5-5V6h1a1,1,0,0,0,0-2ZM11,2h2a3.006,3.006,0,0,1,2.829,2H8.171A3.006,3.006,0,0,1,11,2Zm7,17a3,3,0,0,1-3,3H9a3,3,0,0,1-3-3V6H18Z"/><path d="M10,18a1,1,0,0,0,1-1V11a1,1,0,0,0-2,0v6A1,1,0,0,0,10,18Z"/><path d="M14,18a1,1,0,0,0,1-1V11a1,1,0,0,0-2,0v6A1,1,0,0,0,14,18Z"/></svg>
                                                                                                                                                                    </button>` : ''
                            }
                        </div>`
                }
            },
        ]
        if (IS_PRODUCTION) {
            columns.splice(4, 1)
            columnDefs.splice(4, 1)
            columnDefs[4]['targets'] = 4
            columnDefs[5]['targets'] = 5
            columnDefs[6]['targets'] = 6

            columns.splice(5, 1)
            columnDefs.splice(5, 1)
            columnDefs[5]['targets'] = 5

            columnDefs[3]['orderable'] = false
        } else {
            columnDefs[3]['orderable'] = false
            columnDefs[4]['orderable'] = false
        }
        if (IS_PRODUCT) {
            if (IS_PRODUCTION) {
                columns.splice(4, 1)
                columnDefs.splice(4, 1)
                columnDefs[4]['targets'] = 4
            } else {
                columns.splice(5, 1)
                columnDefs.splice(5, 1)
                columnDefs[5]['targets'] = 5
                columnDefs[6]['targets'] = 6
            }
        }

        // Datatable
        var dt = new DataTable('#data-table', {
            dom: 'rtip',
            pagingType: 'numbers',
            pageLength: 10,
            processing: true,
            serverSide: true,
            order: [],
            columns: columns,
            columnDefs: columnDefs,
            ajax: {
                data: function() {
                    var info = $('#data-table').DataTable().page.info();
                    var url = "{{ route('product.get_data') }}"

                    url =
                        `${url}?page=${ info.page + 1 }&is_product=${IS_PRODUCT}&is_production=${IS_PRODUCTION}`
                    $('#data-table').DataTable().ajax.url(url);
                },
            },
            fnDrawCallback: function(data) {
                if (data.json.is_product !== undefined && data.json.parent_id !== undefined && data.json
                    .search !== undefined) {
                    let url = '{{ config('app.url') }}'
                    url =
                        `${url}/${data.json.is_product == true ? 'product' : 'raw-material'}/view/${data.json.parent_id}?search=${data.json.search}`

                    window.location.href = url
                }
            }
        });
        $('#filter_search').on('keyup', function() {
            dt.search($(this).val()).draw()
        })

        $('#data-table').on('click', '.delete-btns', function() {
            id = $(this).data('id')

            $('#delete-modal #yes-btn').attr('href',
                `{{ config('app.url') }}/${IS_PRODUCT ? 'product' : 'raw-material'}/delete/${id}`)
            $('#delete-modal').addClass('show-modal')
        })
        $('#export-btn').on('click', function() {
            window.location.href = IS_PRODUCT ? '{{ route('product.export') }}' :
                '{{ route('raw_material.export') }}'
        })
        $('body').on('click', '.transfer-to-factory-btns', function() {
            let productId = $(this).data('product-id')
            PRODUCT_ID_TO_TRANSFER = productId
            TRANSFER_TYPE = 'to-factory'

            $('#raw-material-transfer-modal #title').text('{{ __('Transfer to Factory') }}')
            $('#raw-material-transfer-modal').addClass('show-modal')
        })
        $('body').on('click', '.transfer-to-warehouse-btns', function() {
            let productId = $(this).data('product-id')
            let frmId = $(this).data('frm-id')
            PRODUCT_ID_TO_TRANSFER = productId
            FRM_ID_TO_TRANSFER = frmId
            TRANSFER_TYPE = 'to-warehouse'

            $('#raw-material-transfer-modal #title').text('{{ __('Transfer to Warehouse') }}')
            $('#raw-material-transfer-modal').addClass('show-modal')
        })
        $('#raw-material-transfer-modal #yes-btn').on('click', function() {
            var url
            if (TRANSFER_TYPE == 'to-warehouse') {
                url = "{{ route('raw_material.transfer_to_warehouse') }}"
            } else {
                url = "{{ route('raw_material.transfer_to_factory') }}"
            }
            let qty = $('#raw-material-transfer-modal input[name="qty"]').val()
            url = `${url}?product_id=${PRODUCT_ID_TO_TRANSFER}&qty=${qty}&frm_id=${FRM_ID_TO_TRANSFER}`

            window.location.href = url
        })
    </script>
@endpush
