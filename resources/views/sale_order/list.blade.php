@extends('layouts.app')
@section('title', 'Sale Order')

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
        <x-app.page-title class="mb-4 md:mb-0">{{ __('Sale Order') }}</x-app.page-title>
        <div class="flex gap-x-4">
            @can('sale.sale_order.convert')
                <a href="{{ route('sale_order.to_delivery_order') }}"
                    class="bg-green-200 shadow rounded-md py-2 px-4 flex items-center gap-x-2" id="convert-to-inv-btn">
                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="arrow-circle-down" viewBox="0 0 24 24"
                        width="512" height="512">
                        <g>
                            <path
                                d="M23,16H2.681l.014-.015L4.939,13.7a1,1,0,1,0-1.426-1.4L1.274,14.577c-.163.163-.391.413-.624.676a2.588,2.588,0,0,0,0,3.429c.233.262.461.512.618.67l2.245,2.284a1,1,0,0,0,1.426-1.4L2.744,18H23a1,1,0,0,0,0-2Z" />
                            <path
                                d="M1,8H21.255l-2.194,2.233a1,1,0,1,0,1.426,1.4l2.239-2.279c.163-.163.391-.413.624-.675a2.588,2.588,0,0,0,0-3.429c-.233-.263-.461-.513-.618-.67L20.487,2.3a1,1,0,0,0-1.426,1.4l2.251,2.29L21.32,6H1A1,1,0,0,0,1,8Z" />
                        </g>
                    </svg>
                    <span>{{ __('Convert to Delivery Order') }}</span>
                </a>
            @endcan
            {{-- @can('sale.sale_order.create')
                <a href="{{ route('sale_order.create') }}"
                    class="bg-yellow-400 shadow rounded-md py-2 px-4 flex items-center gap-x-2">
                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                        version="1.1" id="Capa_1" x="0px" y="0px" viewBox="0 0 512 512"
                        style="enable-background:new 0 0 512 512;" xml:space="preserve" width="512" height="512">
                        <path
                            d="M480,224H288V32c0-17.673-14.327-32-32-32s-32,14.327-32,32v192H32c-17.673,0-32,14.327-32,32s14.327,32,32,32h192v192   c0,17.673,14.327,32,32,32s32-14.327,32-32V288h192c17.673,0,32-14.327,32-32S497.673,224,480,224z" />
                    </svg>
                    {{ __('New') }}
                </a>
            @endcan --}}
        </div>
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
        <table id="data-table" class="text-sm rounded-lg" style="width: 100%;">
            <thead>
                <tr>
                    <th>{{ __('Doc No.') }}</th>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Transfer From') }}</th>
                    <th>{{ __('Transfer To') }}</th>
                    <th>{{ __('Debtor Code') }}</th>
                    <th>{{ __('Debtor Name') }}</th>
                    <th>{{ __('Agent') }}</th>
                    <th>{{ __('Store') }}</th>
                    <th>{{ __('Curr. Code') }}</th>
                    <th>{{ __('Serial No Qty') }}</th>
                    <th>{{ __('Remaining Qty') }}</th>
                    <th>{{ __('Paid Amount') }}</th>
                    <th>{{ __('Total') }}</th>
                    <th>{{ __('Payment Method') }}</th>
                    <th>{{ __('Payment Status') }}</th>
                    <th>{{ __('Created By') }}</th>
                    <th>{{ __('Updated By') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <x-app.modal.to-production-modal />
    <x-app.modal.delete-modal />
    <x-app.modal.do-inv-void-transfer-back-modal />
    {{-- <x-app.modal.cancel-sale-order-modal />
    <x-app.modal.transfer-back-sale-order-modal /> --}}
@endsection

@push('scripts')
    <script>
        INIT_LOAD = true;
        DEFAULT_PAGE = @json($default_page ?? null);
        IS_SALE_COORDINATOR_ONLY = @json($is_sale_coordinator_only ?? null);
        const urlParams = new URLSearchParams(window.location.search);
        const PROVIDED_SKU = urlParams.get('sku')

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
                    data: 'doc_no'
                },
                {
                    data: 'date'
                },
                {
                    data: 'transfer_from'
                },
                {
                    data: 'transfer_to'
                },
                {
                    data: 'debtor_code'
                },
                {
                    data: 'debtor_name'
                },
                {
                    data: 'agent'
                },
                {
                    data: 'store'
                },
                {
                    data: 'curr_code'
                },
                {
                    data: 'serial_no_qty'
                },
                {
                    data: 'remaining_qty'
                },
                {
                    data: 'paid'
                },
                {
                    data: 'total'
                },
                {
                    data: 'payment_method'
                },
                {
                    data: 'payment_status'
                },
                {
                    data: 'created_by'
                },
                {
                    data: 'updated_by'
                },
                {
                    data: 'status'
                },
                {
                    data: 'action'
                },
            ],
            columnDefs: [{
                    "width": "10%",
                    "targets": 0,
                    render: function(data, type, row) {
                        let convertable = row.conditions_to_convert.is_draft == false && row
                            .conditions_to_convert.payment_method_filled == true && row
                            .conditions_to_convert.payment_due_date_filled == true && row
                            .conditions_to_convert.has_product == true && row.conditions_to_convert
                            .has_serial_no == true && row.conditions_to_convert.is_active_or_approved ==
                            true && row.conditions_to_convert.no_pending_approval == true &&
                            row.conditions_to_convert.not_in_production == true && row.conditions_to_convert
                            .filled_for_e_invoice == true && row.conditions_to_convert.by_pass_for_unpaid ==
                            true

                        return `
                            <span>${data}</span>
                            <div class="flex items-center gap-2 mt-1.5">
                                <div class="group relative">
                                    ${
                                        convertable || row.status == 2 ?
                                        `<svg class="h-3.5 w-3.5 fill-green-500" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="m16.298,8.288l1.404,1.425-5.793,5.707c-.387.387-.896.58-1.407.58s-1.025-.195-1.416-.585l-2.782-2.696,1.393-1.437,2.793,2.707,5.809-5.701Zm7.702,3.712c0,6.617-5.383,12-12,12S0,18.617,0,12,5.383,0,12,0s12,5.383,12,12Zm-2,0c0-5.514-4.486-10-10-10S2,6.486,2,12s4.486,10,10,10,10-4.486,10-10Z"/></svg>` :
                                        `<svg class="h-3.5 w-3.5 fill-blue-500" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512" height="512"><path d="M12,0A12,12,0,1,0,24,12,12.013,12.013,0,0,0,12,0Zm0,22A10,10,0,1,1,22,12,10.011,10.011,0,0,1,12,22Z"/><path d="M12,10H11a1,1,0,0,0,0,2h1v6a1,1,0,0,0,2,0V12A2,2,0,0,0,12,10Z"/><circle cx="12" cy="6.5" r="1.5"/></svg>`
                                    }
                                    <div class="group-hover:opacity-100 group-hover:z-10 absolute bottom-0 opacity-0 z-[-10] w-56">
                                        <div class="rounded shadow border bg-white">
                                            <div class="flex items-center gap-2 border-b px-3 py-2">
                                                <svg class="h-3.5 w-3.5 fill-blue-500" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512" height="512"><path d="M12,0A12,12,0,1,0,24,12,12.013,12.013,0,0,0,12,0Zm0,22A10,10,0,1,1,22,12,10.011,10.011,0,0,1,12,22Z"/><path d="M12,10H11a1,1,0,0,0,0,2h1v6a1,1,0,0,0,2,0V12A2,2,0,0,0,12,10Z"/><circle cx="12" cy="6.5" r="1.5"/></svg>
                                                <p class="font-medium">Conditions to convert</p>
                                            </div>
                                            <div class="px-3 py-2">
                                                <ul class="list-disc pl-4">
                                                    <li class="text-sm ${row.conditions_to_convert.is_draft == false ? 'line-through text-slate-400' : ''}">Not draft</li>
                                                    <li class="text-sm ${row.conditions_to_convert.payment_method_filled == true ? 'line-through text-slate-400' : ''}">Payment method filled</li>
                                                    <li class="text-sm ${row.conditions_to_convert.payment_due_date_filled == true ? 'line-through text-slate-400' : ''}">Payment due date filled</li>
                                                    <li class="text-sm ${row.conditions_to_convert.has_product == true ? 'line-through text-slate-400' : ''}">Has product</li>
                                                    <li class="text-sm ${row.conditions_to_convert.has_serial_no == true ? 'line-through text-slate-400' : ''}">Has serial no</li>
                                                    <li class="text-sm ${row.conditions_to_convert.is_active_or_approved == true || row.status == 2 ? 'line-through text-slate-400' : ''}">Status is either Active / Approved</li>
                                                    <li class="text-sm ${row.conditions_to_convert.no_pending_approval == true ? 'line-through text-slate-400' : ''}">No pending approval</li>
                                                    <li class="text-sm ${row.conditions_to_convert.not_in_production == true ? 'line-through text-slate-400' : ''}">Not in production</li>
                                                    <li class="text-sm ${row.conditions_to_convert.filled_for_e_invoice == true ? 'line-through text-slate-400' : ''}">Filled for E-Invoice</li>
                                                    <li class="text-sm ${row.conditions_to_convert.by_pass_for_unpaid == true ? 'line-through text-slate-400' : ''}">Can by pass for unpaid</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <span class="text-xs text-blue-700 ${row.is_draft == true ? '' : 'hidden'}">Draft</span>
                            </div>
                        `
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
                    "width": '10%',
                    "targets": 2,
                    orderable: false,
                    render: function(data, type, row) {
                        return data;
                    }
                },
                {
                    "width": '10%',
                    "targets": 3,
                    orderable: false,
                    render: function(data, type, row) {
                        return data;
                    }
                },
                {
                    "width": '10%',
                    "targets": 4,
                    render: function(data, type, row) {
                        return data;
                    }
                },
                {
                    "width": '10%',
                    "targets": 5,
                    render: function(data, type, row) {
                        return `${row.debtor_name}, ${row.debtor_company_group == 1 ? 'Power Cool' : 'Hi-Ten'}`
                    }
                },
                {
                    "width": '10%',
                    "targets": 6,
                    render: function(data, type, row) {
                        return data;
                    }
                },
                {
                    "width": '10%',
                    "targets": 7,
                    orderable: false,
                    render: function(data, type, row) {
                        return data;
                    }
                },
                {
                    "width": '10%',
                    "targets": 8,
                    orderable: false,
                    render: function(data, type, row) {
                        return data;
                    }
                },
                {
                    "width": '10%',
                    "targets": 9,
                    orderable: false,
                    render: function(data, type, row) {
                        return `${row.serial_no_qty}/${row.qty ?? 0}`;
                    }
                },
                {
                    "width": '10%',
                    "targets": 10,
                    orderable: false,
                    render: function(data, type, row) {
                        return `${row.not_converted_serial_no_qty}/${row.serial_no_qty ?? 0}`;
                    }
                },
                {
                    "width": '10%',
                    "targets": 11,
                    render: function(data, type, row) {
                        return `RM ${data}`;
                    }
                },
                {
                    "width": '10%',
                    "targets": 12,
                    render: function(data, type, row) {
                        return `RM ${data}`;
                    }
                },
                {
                    "width": '10%',
                    "targets": 13,
                    render: function(data, type, row) {
                        return data
                    }
                },
                {
                    "width": '10%',
                    "targets": 14,
                    render: function(data, type, row) {
                        switch (data) {
                            case 1:
                                return "{!! __('Unpaid') !!}"
                            case 2:
                                return "{!! __('Partially Paid') !!}"
                            case 3:
                                return "{!! __('Paid') !!}"
                            default:
                                return data
                        }
                    }
                },
                {
                    "width": '10%',
                    "targets": 15,
                    orderable: false,
                    render: function(data, type, row) {
                        return data
                    }
                },
                {
                    "width": '10%',
                    "targets": 16,
                    orderable: false,
                    render: function(data, type, row) {
                        return data
                    }
                },
                {
                    "width": '10%',
                    "targets": 17,
                    render: function(data, type, row) {
                        switch (data) {
                            case 0:
                                return "{!! __('Inactive') !!}"
                            case 1:
                                return "{!! __('Active') !!}"
                            case 2:
                                return "{!! __('Converted') !!}"
                            case 3:
                                if (row.cancellation_charge != null) {
                                    return `{!! __('Cancelled') !!} ({!! __('Charge') !!}: RM ${row.cancellation_charge})"}`
                                }
                                return `{!! __('Cancelled') !!}`
                            case 4:
                                return "{!! __('Pending Approval') !!}"
                            case 5:
                                return "{!! __('Approved') !!}"
                            case 6:
                                return "{!! __('Rejected') !!}"
                            case 7:
                                return "{!! __('Rejected') !!}"
                            case 8:
                                return "{!! __('Partially Converted') !!}"
                            default:
                                return data
                        }
                    }
                },
                {
                    "width": "5%",
                    "targets": 18,
                    orderable: false,
                    render: function(data, type, row) {
                        if (IS_SALE_COORDINATOR_ONLY == true) {
                            return `<div class="flex flex-wrap w-32 items-center justify-end gap-2 px-2">
                                ${
                                    row.can_edit ? `
                                                                                <a href="{{ config('app.url') }}/sale-order/edit/${row.id}" class="rounded-full p-2 bg-blue-200 inline-block" title="{!! __('Edit') !!}">
                                                                                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="m18.813,10c.309,0,.601-.143.79-.387s.255-.562.179-.861c-.311-1.217-.945-2.329-1.833-3.217l-3.485-3.485c-1.322-1.322-3.08-2.05-4.95-2.05h-4.515C2.243,0,0,2.243,0,5v14c0,2.757,2.243,5,5,5h3c.552,0,1-.448,1-1s-.448-1-1-1h-3c-1.654,0-3-1.346-3-3V5c0-1.654,1.346-3,3-3h4.515c.163,0,.325.008.485.023v4.977c0,1.654,1.346,3,3,3h5.813Zm-6.813-3V2.659c.379.218.732.488,1.05.806l3.485,3.485c.314.314.583.668.803,1.05h-4.338c-.551,0-1-.449-1-1Zm11.122,4.879c-1.134-1.134-3.11-1.134-4.243,0l-6.707,6.707c-.755.755-1.172,1.76-1.172,2.829v1.586c0,.552.448,1,1,1h1.586c1.069,0,2.073-.417,2.828-1.172l6.707-6.707c.567-.567.879-1.32.879-2.122s-.312-1.555-.878-2.121Zm-1.415,2.828l-6.708,6.707c-.377.378-.879.586-1.414.586h-.586v-.586c0-.534.208-1.036.586-1.414l6.708-6.707c.377-.378,1.036-.378,1.414,0,.189.188.293.439.293.707s-.104.518-.293.707Z"/></svg>
                                                                                </a>` : ''
                                }
                                ${
                                    row.can_view ? `<a href="{{ config('app.url') }}/sale-order/view/${row.id}" class="rounded-full p-2 bg-green-300 inline-block" title="{!! __('View') !!}">
                                                                                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512" height="512"><path d="M23.271,9.419C21.72,6.893,18.192,2.655,12,2.655S2.28,6.893.729,9.419a4.908,4.908,0,0,0,0,5.162C2.28,17.107,5.808,21.345,12,21.345s9.72-4.238,11.271-6.764A4.908,4.908,0,0,0,23.271,9.419Zm-1.705,4.115C20.234,15.7,17.219,19.345,12,19.345S3.766,15.7,2.434,13.534a2.918,2.918,0,0,1,0-3.068C3.766,8.3,6.781,4.655,12,4.655s8.234,3.641,9.566,5.811A2.918,2.918,0,0,1,21.566,13.534Z"/><path d="M12,7a5,5,0,1,0,5,5A5.006,5.006,0,0,0,12,7Zm0,8a3,3,0,1,1,3-3A3,3,0,0,1,12,15Z"/></svg>
                                                                                        </a>` : ''
                                }
                                </div>`
                        }

                        return `<div class="flex flex-wrap w-32 items-center justify-end gap-2 px-2">
                            ${
                                row.status != 1 ? '' : `
                                                                <button class="rounded-full p-2 bg-purple-200 inline-block to-production-btns" data-id="${row.id}" title="{!! __('To Sale Production Request') !!}">
                                                                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M8,5c0-2.206-1.794-4-4-4S0,2.794,0,5c0,1.86,1.277,3.428,3,3.873v7.253c-1.723,.445-3,2.013-3,3.873,0,2.206,1.794,4,4,4s4-1.794,4-4c0-1.86-1.277-3.428-3-3.873v-7.253c1.723-.445,3-2.013,3-3.873Zm-6,0c0-1.103,.897-2,2-2s2,.897,2,2-.897,2-2,2-2-.897-2-2Zm4,15c0,1.103-.897,2-2,2s-2-.897-2-2,.897-2,2-2,2,.897,2,2Zm15-3.873v-7.127c0-2.757-2.243-5-5-5h-3.471l2.196-2.311c.381-.4,.364-1.034-.036-1.414-.399-.379-1.033-.364-1.413,.036l-2.396,2.522c-1.17,1.169-1.17,3.073-.03,4.212l2.415,2.631c.196,.215,.466,.324,.736,.324,.242,0,.484-.087,.676-.263,.407-.374,.435-1.006,.061-1.413l-2.133-2.324h3.397c1.654,0,3,1.346,3,3v7.127c-1.724,.445-3,2.013-3,3.873,0,2.206,1.794,4,4,4s4-1.794,4-4c0-1.86-1.276-3.428-3-3.873Zm-1,5.873c-1.103,0-2-.897-2-2s.897-2,2-2,2,.897,2,2-.897,2-2,2Z"/></svg>
                                                                </button>`
                            }
                            ${
                                row.can_view_pdf ?
                                    `<a href="{{ config('app.url') }}/sale-order/pdf/${row.id}" class="rounded-full p-2 bg-yellow-200 inline-block" target="_blank" title="{!! __('View PDF') !!}">
                                                                                                                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512" height="512"><path d="M16,0H8A5.006,5.006,0,0,0,3,5V23a1,1,0,0,0,1.564.825L6.67,22.386l2.106,1.439a1,1,0,0,0,1.13,0l2.1-1.439,2.1,1.439a1,1,0,0,0,1.131,0l2.1-1.438,2.1,1.437A1,1,0,0,0,21,23V5A5.006,5.006,0,0,0,16,0Zm3,21.1-1.1-.752a1,1,0,0,0-1.132,0l-2.1,1.439-2.1-1.439a1,1,0,0,0-1.131,0l-2.1,1.439-2.1-1.439a1,1,0,0,0-1.129,0L5,21.1V5A3,3,0,0,1,8,2h8a3,3,0,0,1,3,3Z"/><rect x="7" y="8" width="10" height="2" rx="1"/><rect x="7" y="12" width="8" height="2" rx="1"/></svg>
                                                                                                                </a>` : ''
                            }
                            ${
                                row.can_edit ? `
                                                                                                                    <a href="{{ config('app.url') }}/sale-order/edit/${row.id}" class="rounded-full p-2 bg-blue-200 inline-block" title="{!! __('Edit') !!}">
                                                                                                                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="m18.813,10c.309,0,.601-.143.79-.387s.255-.562.179-.861c-.311-1.217-.945-2.329-1.833-3.217l-3.485-3.485c-1.322-1.322-3.08-2.05-4.95-2.05h-4.515C2.243,0,0,2.243,0,5v14c0,2.757,2.243,5,5,5h3c.552,0,1-.448,1-1s-.448-1-1-1h-3c-1.654,0-3-1.346-3-3V5c0-1.654,1.346-3,3-3h4.515c.163,0,.325.008.485.023v4.977c0,1.654,1.346,3,3,3h5.813Zm-6.813-3V2.659c.379.218.732.488,1.05.806l3.485,3.485c.314.314.583.668.803,1.05h-4.338c-.551,0-1-.449-1-1Zm11.122,4.879c-1.134-1.134-3.11-1.134-4.243,0l-6.707,6.707c-.755.755-1.172,1.76-1.172,2.829v1.586c0,.552.448,1,1,1h1.586c1.069,0,2.073-.417,2.828-1.172l6.707-6.707c.567-.567.879-1.32.879-2.122s-.312-1.555-.878-2.121Zm-1.415,2.828l-6.708,6.707c-.377.378-.879.586-1.414.586h-.586v-.586c0-.534.208-1.036.586-1.414l6.708-6.707c.377-.378,1.036-.378,1.414,0,.189.188.293.439.293.707s-.104.518-.293.707Z"/></svg>
                                                                                                                    </a>
                                                                                                                ` : ''
                            }
                            ${
                                row.can_view ? `
                                                                                                                    <a href="{{ config('app.url') }}/sale-order/view/${row.id}" class="rounded-full p-2 bg-blue-200 inline-block" title="{!! __('View') !!}">
                                                                                                                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512" height="512"><path d="M23.271,9.419C21.72,6.893,18.192,2.655,12,2.655S2.28,6.893.729,9.419a4.908,4.908,0,0,0,0,5.162C2.28,17.107,5.808,21.345,12,21.345s9.72-4.238,11.271-6.764A4.908,4.908,0,0,0,23.271,9.419Zm-1.705,4.115C20.234,15.7,17.219,19.345,12,19.345S3.766,15.7,2.434,13.534a2.918,2.918,0,0,1,0-3.068C3.766,8.3,6.781,4.655,12,4.655s8.234,3.641,9.566,5.811A2.918,2.918,0,0,1,21.566,13.534Z"/><path d="M12,7a5,5,0,1,0,5,5A5.006,5.006,0,0,0,12,7Zm0,8a3,3,0,1,1,3-3A3,3,0,0,1,12,15Z"/></svg>
                                                                                                                    </a>
                                                                                                                ` : ''
                            }
                            ${
                                row.can_cancel ?
                                `<button class="rounded-full p-2 bg-violet-200 inline-block cancel-btns" data-id="${row.id}" title="{!! __('Cancel') !!}">
                                                                                                                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512" height="512"><path d="M16,8a1,1,0,0,0-1.414,0L12,10.586,9.414,8A1,1,0,0,0,8,9.414L10.586,12,8,14.586A1,1,0,0,0,9.414,16L12,13.414,14.586,16A1,1,0,0,0,16,14.586L13.414,12,16,9.414A1,1,0,0,0,16,8Z"/><path d="M12,0A12,12,0,1,0,24,12,12.013,12.013,0,0,0,12,0Zm0,22A10,10,0,1,1,22,12,10.011,10.011,0,0,1,12,22Z"/></svg>
                                                                                                                    </button>` : ''
                            }
                       </div>`
                    }
                },
            ],
            ajax: {
                data: function() {
                    var info = $('#data-table').DataTable().page.info();
                    var url = "{{ route('sale_order.get_data') }}"

                    if (PROVIDED_SKU != null) {
                        url = `${url}?page=${ info.page + 1 }&sku=${PROVIDED_SKU}`
                    } else {
                        url =
                            `${url}?page=${ INIT_LOAD == true && DEFAULT_PAGE != null ? DEFAULT_PAGE : info.page + 1 }`
                    }
                    $('#data-table').DataTable().ajax.url(url);

                    INIT_LOAD = false
                },
            },
        });
        $('#filter_search').on('keyup', function() {
            dt.search($(this).val()).draw()
        })

        $('#data-table').on('click', '.cancel-btns', function() {
            id = $(this).data('id')

            getOtherInvolvedSO(id);
        })
        $('#do-inv-void-transfer-back-modal a').on('click', function(e) {
            e.preventDefault()

            let url = $(this).attr('href')
            let charge = $('#do-inv-void-transfer-back-modal input[name="charge"]').val()
            let reason = $('#do-inv-void-transfer-back-modal textarea[name="remark"]').text()
            url = `${url}&charge=${charge}&reason=${reason}`

            window.location.href = url
        })

        function getOtherInvolvedSO(so_id) {
            $('#do-inv-void-transfer-back-modal .cancellation-hint').remove()

            let url = "{{ config('app.url') }}"
            url = `${url}/delivery-order/get-cancellation-involved-so/${so_id}`

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: url,
                type: 'GET',
                contentType: 'application/json',
                success: function(res) {
                    for (const key in res.involved) {
                        const element = res.involved[key];

                        let clone = $('#do-inv-void-transfer-back-modal #info-template')[0].cloneNode(true);

                        $(clone).find('#main').text(key)
                        for (let i = 0; i < element.length; i++) {
                            let soClone = $('#do-inv-void-transfer-back-modal #info-body-container #sub')[0]
                                .cloneNode(
                                    true);

                            $(soClone).text(element[i])
                            $(clone).append(soClone)
                        }
                        $(clone).addClass('cancellation-hint')
                        $(clone).removeClass('hidden')

                        $('#do-inv-void-transfer-back-modal #info-body-container').append(clone)
                    }

                    $('#do-inv-void-transfer-back-modal #warning-txt').text("{!! __('Following SO will be cancelled, QUO will be remain as active.') !!}")
                    $('#do-inv-void-transfer-back-modal #void-btn').attr('href',
                        `{{ config('app.url') }}/sale-order/cancel?involved_so_skus=${res.involved_so_skus}&involved_quo_skus=${JSON.stringify(res.involved_quo_skus)}`
                    )
                    $('#do-inv-void-transfer-back-modal #transfer-back-btn').attr('href',
                        `{{ config('app.url') }}/sale-order/transfer-back?involved_so_skus=${res.involved_so_skus}&involved_quo_skus=${JSON.stringify(res.involved_quo_skus)}`
                    )
                    $('#do-inv-void-transfer-back-modal form').removeClass('hidden')
                    $('#do-inv-void-transfer-back-modal').addClass('show-modal')
                }
            });
        }



        // $('#data-table').on('click', '.cancel-btns', function() {
        //     id = $(this).data('id')

        //     $('#cancel-sale-order-modal form').attr('action', `{{ config('app.url') }}/sale-order/cancel/${id}`)
        //     $('#cancel-sale-order-modal').addClass('show-modal')
        // })

        // $('#data-table').on('click', '.transfer-back-btns', function() {
        //     id = $(this).data('id')

        //     $('#transfer-back-sale-order-modal #yes-btn').attr('data-id', id)
        //     $('#transfer-back-sale-order-modal').addClass('show-modal')
        // })

        // $('#transfer-back-sale-order-modal #yes-btn').on('click', function() {
        //     let url = '{{ config('app.url') }}'
        //     url = `${url}/sale-order/transfer-back/${$('#transfer-back-sale-order-modal #yes-btn').data('id')}`
        //     window.location.href = url
        // })

        $('#data-table').on('click', '.to-production-btns', function() {
            $('#to-production-modal select').empty()

            let id = $(this).data('id')
            $('#to-production-modal #yes-btn').attr('data-id', id)

            let url = "{{ config('app.url') }}"
            url = `${url}/sale/get-products/${id}`

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: url,
                type: 'GET',
                success: function(res) {
                    let opt = new Option('Select a product', null)
                    $('#to-production-modal select').append(opt)

                    for (let i = 0; i < res.products.length; i++) {
                        const elem = res.products[i];
                        var totalCount = 0
                        var assignedCount = 0
                        var requestedCount = 0

                        for (let j = 0; j < res.sale_product_details.length; j++) {
                            if (res.sale_product_details[j].product_id == elem.id) {
                                totalCount = res.sale_product_details[j].qty
                                assignedCount = res.sale_product_details[j].children.length
                                break
                            }
                        }
                        for (let j = 0; j < res.requested_details.length; j++) {
                            if (res.requested_details[j].product_id == elem.id) {
                                totalCount = res.requested_details[j].count
                                break
                            }
                        }

                        let opt = new Option(
                            `${elem.sku} - Assigned x${assignedCount}, Requested x${requestedCount}, Pending x${totalCount - assignedCount - requestedCount}`,
                            elem.id)
                        $('#to-production-modal select').append(opt)
                    }

                    $('#to-production-modal').addClass('show-modal')
                },
            });
        })
    </script>
@endpush
