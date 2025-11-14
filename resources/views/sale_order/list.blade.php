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
                <a href="{{ route('quotation.to_sale_order') }}"
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
                    <span>{{ __('Convert From Quotation') }}</span>
                </a>
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
        <div class="flex max-w-lg gap-x-2 w-full mb-4">
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
            <div class="flex-1 flex">
                <x-app.input.select name="filter_transfer_type" id="filter_transfer_type" class="w-full capitalize">
                    @foreach ($so_types as $key => $status)
                        <option value="{{ $key }}" @selected(isset($default_so_type) && $default_so_type == $key)>{{ $status }}</option>
                    @endforeach
                </x-app.input.select>
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
                    <th>{{ __('Converted Qty') }}</th>
                    <th>{{ __('Paid Amount') }}</th>
                    <th>{{ __('Total') }}</th>
                    <th>{{ __('Payment Method') }}</th>
                    <th>{{ __('Payment Status') }}</th>
                    <th>{{ __('Created By') }}</th>
                    <th>{{ __('Updated By') }}</th>
                    <th>{{ __('Status') }}</th>
                    @if ($default_so_type == \App\Models\Sale::TRANSFER_TYPE_TRANSFER_TO)
                        <th>{{ __('Transfer To Branch') }}</th>
                    @elseif ($default_so_type == \App\Models\Sale::TRANSFER_TYPE_TRANSFER_FROM)
                        <th>{{ __('Transfer From Branch') }}</th>
                    @endif
                    <th></th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <x-app.modal.to-production-modal />
    <x-app.modal.delete-modal />
    <x-app.modal.do-inv-void-transfer-back-modal />
    <x-app.modal.transfer-so-modal />
@endsection

@push('scripts')
    <script>
        INIT_LOAD = true;
        DEFAULT_PAGE = @json($default_page ?? null);
        IS_SALE_COORDINATOR_ONLY = @json($is_sale_coordinator_only ?? null);
        DEFAULT_TRANSFER_TYPE = @json($default_so_type ?? null);
        IS_SALES_ROLE = @json(in_array(\App\Models\Role::SALE, getUserRoleId(Auth::user())));
        const urlParams = new URLSearchParams(window.location.search);
        const PROVIDED_SKU = urlParams.get('sku')
        
        // Datatable
        var columns = [{
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
        ]
        var columnDefs = [{
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
                                <div class="group-hover:opacity-100 group-hover:z-10 absolute bottom-3.5 opacity-0 z-[-10] w-56">
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
                                return `{!! __('Voided') !!} ({!! __('Charge') !!}: RM ${row.cancellation_charge})"}`
                            }
                            return `{!! __('Voided') !!}`
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
                            row.can_to_sale_production_request ? `
                                    <button class="rounded-full p-2 bg-purple-200 inline-block to-production-btns" data-id="${row.id}" title="{!! __('To Sale Production Request') !!}">
                                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M8,5c0-2.206-1.794-4-4-4S0,2.794,0,5c0,1.86,1.277,3.428,3,3.873v7.253c-1.723,.445-3,2.013-3,3.873,0,2.206,1.794,4,4,4s4-1.794,4-4c0-1.86-1.277-3.428-3-3.873v-7.253c1.723-.445,3-2.013,3-3.873Zm-6,0c0-1.103,.897-2,2-2s2,.897,2,2-.897,2-2,2-2-.897-2-2Zm4,15c0,1.103-.897,2-2,2s-2-.897-2-2,.897-2,2-2,2,.897,2,2Zm15-3.873v-7.127c0-2.757-2.243-5-5-5h-3.471l2.196-2.311c.381-.4,.364-1.034-.036-1.414-.399-.379-1.033-.364-1.413,.036l-2.396,2.522c-1.17,1.169-1.17,3.073-.03,4.212l2.415,2.631c.196,.215,.466,.324,.736,.324,.242,0,.484-.087,.676-.263,.407-.374,.435-1.006,.061-1.413l-2.133-2.324h3.397c1.654,0,3,1.346,3,3v7.127c-1.724,.445-3,2.013-3,3.873,0,2.206,1.794,4,4,4s4-1.794,4-4c0-1.86-1.276-3.428-3-3.873Zm-1,5.873c-1.103,0-2-.897-2-2s.897-2,2-2,2,.897,2,2-.897,2-2,2Z"/></svg>
                                    </button>` : ''
                        }
                        ${
                            row.can_transfer && !IS_SALES_ROLE ? `
                                    <button class="rounded-full p-2 bg-emerald-200 inline-block transfer-btns" data-id="${row.id}" data-sku="${row.doc_no}" title="{!! __('Transfer') !!}">
                                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M24,12.649a5,5,0,0,0-.256-1.581L22.405,7.051A3,3,0,0,0,19.559,5H17V4a3,3,0,0,0-3-3H3A3,3,0,0,0,0,4V19.5a3.517,3.517,0,0,0,6,2.447A3.517,3.517,0,0,0,12,19.5V19h3v.5a3.5,3.5,0,0,0,7,0V19h2ZM19.559,7a1,1,0,0,1,.948.684L21.613,11H17V7ZM2,4A1,1,0,0,1,3,3H14a1,1,0,0,1,1,1V17H2ZM3.5,21A1.5,1.5,0,0,1,2,19.5V19H5v.5A1.5,1.5,0,0,1,3.5,21ZM10,19.5a1.5,1.5,0,0,1-3,0V19h3Zm10,0a1.5,1.5,0,0,1-3,0V19h3ZM17,17V13h5v4Z"/></svg>
                                    </button>` : ''
                        }
                        ${
                            row.can_view_pdf ?
                                `<a href="{{ config('app.url') }}/sale-order/pdf/${row.id}" class="rounded-full p-2 bg-yellow-200 inline-block" target="_blank" title="{!! __('View PDF') !!}">
                                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512" height="512"><path d="M16,0H8A5.006,5.006,0,0,0,3,5V23a1,1,0,0,0,1.564.825L6.67,22.386l2.106,1.439a1,1,0,0,0,1.13,0l2.1-1.439,2.1,1.439a1,1,0,0,0,1.131,0l2.1-1.438,2.1,1.437A1,1,0,0,0,21,23V5A5.006,5.006,0,0,0,16,0Zm3,21.1-1.1-.752a1,1,0,0,0-1.132,0l-2.1,1.439-2.1-1.439a1,1,0,0,0-1.131,0l-2.1,1.439-2.1-1.439a1,1,0,0,0-1.129,0L5,21.1V5A3,3,0,0,1,8,2h8a3,3,0,0,1,3,3Z"/><rect x="7" y="8" width="10" height="2" rx="1"/><rect x="7" y="12" width="8" height="2" rx="1"/></svg>
                                    </a>
                                    <a href="{{ config('app.url') }}/sale-order/proforma-invoice/pdf/${row.id}" class="rounded-full p-2 bg-green-200 inline-block" target="_blank" title="{!! __('View Proforma Invoice') !!}">
                                        <svg class="h-4 w-4" id="Layer_1" height="512" viewBox="0 0 24 24" width="512" xmlns="http://www.w3.org/2000/svg" data-name="Layer 1"><path d="m17 14a1 1 0 0 1 -1 1h-8a1 1 0 0 1 0-2h8a1 1 0 0 1 1 1zm-4 3h-5a1 1 0 0 0 0 2h5a1 1 0 0 0 0-2zm9-6.515v8.515a5.006 5.006 0 0 1 -5 5h-10a5.006 5.006 0 0 1 -5-5v-14a5.006 5.006 0 0 1 5-5h4.515a6.958 6.958 0 0 1 4.95 2.05l3.484 3.486a6.951 6.951 0 0 1 2.051 4.949zm-6.949-7.021a5.01 5.01 0 0 0 -1.051-.78v4.316a1 1 0 0 0 1 1h4.316a4.983 4.983 0 0 0 -.781-1.05zm4.949 7.021c0-.165-.032-.323-.047-.485h-4.953a3 3 0 0 1 -3-3v-4.953c-.162-.015-.321-.047-.485-.047h-4.515a3 3 0 0 0 -3 3v14a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3z"/></svg>
                                    </a>
                                    ` : ''
                        }
                        ${
                            row.can_edit_payment ? `<a href="{{ config('app.url') }}/sale-order/edit/payment/${row.id}" class="rounded-full p-2 bg-amber-200 inline-block" title="{!! __('Payment') !!}">
                                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M12,16c-2.206,0-4-1.794-4-4s1.794-4,4-4,4,1.794,4,4-1.794,4-4,4Zm0-6c-1.103,0-2,.897-2,2s.897,2,2,2,2-.897,2-2-.897-2-2-2Zm-7-3c-.552,0-1,.448-1,1s.448,1,1,1,1-.448,1-1-.448-1-1-1Zm13,3c0,.552,.448,1,1,1s1-.448,1-1-.448-1-1-1-1,.448-1,1Zm-13,3c-.552,0-1,.448-1,1s.448,1,1,1,1-.448,1-1-.448-1-1-1Zm13,3c0,.552,.448,1,1,1s1-.448,1-1-.448-1-1-1-1,.448-1,1Zm-1.001,5c-1.634,0-3.098-.399-4.513-.785-1.348-.368-2.62-.715-3.986-.715-1.571,0-2.562,.101-3.419,.349-1.206,.347-2.474,.113-3.48-.644-1.017-.765-1.6-1.933-1.6-3.205v-7.548c0-2.063,1.299-3.944,3.233-4.681,1.341-.512,2.609-.771,3.768-.771,1.634,0,3.097,.399,4.513,.785,1.348,.368,2.62,.715,3.986,.715,1.57,0,2.562-.101,3.419-.349,1.208-.347,2.476-.113,3.481,.644,1.017,.765,1.6,1.933,1.6,3.205v7.548h0c0,2.063-1.3,3.944-3.234,4.681-1.341,.512-2.608,.771-3.768,.771Zm-8.499-3.5c1.634,0,3.097,.399,4.513,.785,1.348,.368,2.62,.715,3.986,.715,.914,0,1.942-.215,3.056-.64,1.183-.45,1.946-1.554,1.946-2.812v-7.548c0-.637-.293-1.223-.803-1.606-.499-.375-1.126-.493-1.725-.321-1.051,.303-2.202,.427-3.974,.427-1.634,0-3.097-.399-4.513-.785-1.348-.368-2.62-.715-3.986-.715-.915,0-1.942,.215-3.056,.64-1.183,.45-1.946,1.554-1.946,2.812v7.548c0,.637,.293,1.223,.803,1.606,.499,.375,1.126,.493,1.724,.32,1.051-.303,2.203-.427,3.974-.427Z"/></svg>
                                </a>` : ''
                        }
                        ${
                            row.can_edit ? `
                                        <a href="{{ config('app.url') }}/sale-order/edit/${row.id}" class="rounded-full p-2 bg-blue-200 inline-block" title="{!! __('Edit') !!}">
                                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="m18.813,10c.309,0,.601-.143.79-.387s.255-.562.179-.861c-.311-1.217-.945-2.329-1.833-3.217l-3.485-3.485c-1.322-1.322-3.08-2.05-4.95-2.05h-4.515C2.243,0,0,2.243,0,5v14c0,2.757,2.243,5,5,5h3c.552,0,1-.448,1-1s-.448-1-1-1h-3c-1.654,0-3-1.346-3-3V5c0-1.654,1.346-3,3-3h4.515c.163,0,.325.008.485.023v4.977c0,1.654,1.346,3,3,3h5.813Zm-6.813-3V2.659c.379.218.732.488,1.05.806l3.485,3.485c.314.314.583.668.803,1.05h-4.338c-.551,0-1-.449-1-1Zm11.122,4.879c-1.134-1.134-3.11-1.134-4.243,0l-6.707,6.707c-.755.755-1.172,1.76-1.172,2.829v1.586c0,.552.448,1,1,1h1.586c1.069,0,2.073-.417,2.828-1.172l6.707-6.707c.567-.567.879-1.32.879-2.122s-.312-1.555-.878-2.121Zm-1.415,2.828l-6.708,6.707c-.377.378-.879.586-1.414.586h-.586v-.586c0-.534.208-1.036.586-1.414l6.708-6.707c.377-.378,1.036-.378,1.414,0,.189.188.293.439.293.707s-.104.518-.293.707Z"/></svg>
                                        </a>` : ''
                        }
                        ${
                            row.can_view ? `
                                        <a href="{{ config('app.url') }}/sale-order/view/${row.id}" class="rounded-full p-2 bg-blue-200 inline-block" title="{!! __('View') !!}">
                                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512" height="512"><path d="M23.271,9.419C21.72,6.893,18.192,2.655,12,2.655S2.28,6.893.729,9.419a4.908,4.908,0,0,0,0,5.162C2.28,17.107,5.808,21.345,12,21.345s9.72-4.238,11.271-6.764A4.908,4.908,0,0,0,23.271,9.419Zm-1.705,4.115C20.234,15.7,17.219,19.345,12,19.345S3.766,15.7,2.434,13.534a2.918,2.918,0,0,1,0-3.068C3.766,8.3,6.781,4.655,12,4.655s8.234,3.641,9.566,5.811A2.918,2.918,0,0,1,21.566,13.534Z"/><path d="M12,7a5,5,0,1,0,5,5A5.006,5.006,0,0,0,12,7Zm0,8a3,3,0,1,1,3-3A3,3,0,0,1,12,15Z"/></svg>
                                        </a>` : ''
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
        ]
        if (DEFAULT_TRANSFER_TYPE == {{ \App\Models\Sale::TRANSFER_TYPE_TRANSFER_TO }}) {
            columns.splice(18, 0, {
                data: 'transfer_to_branch'
            })
            columnDefs.splice(18, 0, {
                "width": "10%",
                "targets": 18,
                orderable: false,
                render: function(data, type, row) {
                    return data
                }
            })
            columnDefs[columnDefs.length - 1].targets = 19
        } else if (DEFAULT_TRANSFER_TYPE == {{ \App\Models\Sale::TRANSFER_TYPE_TRANSFER_FROM }}) {
            columns.splice(18, 0, {
                data: 'transfer_from_branch'
            })
            columnDefs.splice(18, 0, {
                "width": "10%",
                "targets": 18,
                orderable: false,
                render: function(data, type, row) {
                    return data
                }
            })
            columnDefs[columnDefs.length - 1].targets = 19
        }
        var dt = new DataTable('#data-table', {
            dom: 'rtip',
            pagingType: 'numbers',
            pageLength: 10,
            processing: true,
            serverSide: true,
            order: [],
            displayStart: DEFAULT_PAGE != null ? (DEFAULT_PAGE - 1) * 10 : 0,
            columns: columns,
            columnDefs: columnDefs,
            ajax: {
                data: function() {
                    var info = $('#data-table').DataTable().page.info();
                    var url = "{{ route('sale_order.get_data') }}"

                    if (PROVIDED_SKU != null) {
                        url = `${url}?page=${ info.page + 1 }&transfer_type=${$('#filter_transfer_type').val()}&sku=${PROVIDED_SKU}`
                    } else {
                        url =
                            `${url}?page=${ INIT_LOAD == true && DEFAULT_PAGE != null ? DEFAULT_PAGE : info.page + 1 }&transfer_type=${$('#filter_transfer_type').val()}`
                    }
                    $('#data-table').DataTable().ajax.url(url);

                    INIT_LOAD = false
                },
            },
        });
        $('#filter_search').on('keyup', function() {
            dt.search($(this).val()).draw()
        })
        $('#filter_transfer_type').on('change', function() {
            window.location.href = `{{ config('app.url') }}/sale-order?transfer_type=${$(this).find('option:selected').text()}`
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
                        var totalCount = res.sale_product_details[i].qty
                        var assignedCount = 0
                        var requestedCount = 0
                        var saleProductId = null

                        for (let j = 0; j < res.sale_product_details.length; j++) {
                            if (res.sale_product_details[j].product_id == elem.id) {
                                totalCount = res.sale_product_details[j].qty
                                assignedCount = res.sale_product_details[j].children.length
                                saleProductId = res.sale_product_details[j].id
                                break
                            }
                        }
                        for (let j = 0; j < res.requested_details.length; j++) {
                            if (res.requested_details[j].product_id == elem.id) {
                                requestedCount = res.requested_details[j].count
                                break
                            }
                        }

                        let opt = new Option(
                            `${elem.sku} - Assigned x${assignedCount}, Requested x${requestedCount}, Pending x${totalCount - assignedCount - requestedCount}`,
                            elem.id)
                        $(opt).data('sp-id', saleProductId)
                        $('#to-production-modal select').append(opt)
                    }

                    $('#to-production-modal').addClass('show-modal')
                },
            });
        })
        $('body').on('click', '.transfer-btns', function() {
            let soId = $(this).data('id')
            let soSku = $(this).data('sku')

            $('#transfer-so-modal form').attr('action', `{{ config('app.url') }}/sale-order/transfer/${soId}`)
            $('#transfer-so-modal #date').text(moment().format('D MMM YYYY HH:mm'))
            $('#transfer-so-modal #sale-order').text(soSku)
            $('#transfer-so-modal #yes-btn').addClass('hidden')
            $('#transfer-so-modal').addClass('show-modal')
        })
    </script>
@endpush
