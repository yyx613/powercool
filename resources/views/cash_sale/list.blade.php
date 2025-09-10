@extends('layouts.app')
@section('title', 'Cash Sale')

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
        <x-app.page-title class="mb-4 md:mb-0">{{ __('Cash Sale') }}</x-app.page-title>
        <div class="flex gap-x-4">
            @can('sale.cash_sale.create')
                <a href="{{ route('cash_sale.create') }}"
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
                    <th>{{ __('Debtor Name') }}</th>
                    <th>{{ __('Agent') }}</th>
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
            columns: [{
                    data: 'doc_no'
                },
                {
                    data: 'date'
                },
                {
                    data: 'debtor_name'
                },
                {
                    data: 'agent'
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
                            row.conditions_to_convert.not_in_production == true && row.conditions_to_convert.by_pass_for_unpaid ==
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
                    render: function(data, type, row) {
                        return data
                    }
                },
                {
                    "width": '10%',
                    "targets": 3,
                    render: function(data, type, row) {
                        return data;
                    }
                },
                {
                    "width": '10%',
                    "targets": 4,
                    orderable: false,
                    render: function(data, type, row) {
                        return `${row.serial_no_qty}/${row.qty ?? 0}`;
                    }
                },
                {
                    "width": '10%',
                    "targets": 5,
                    orderable: false,
                    render: function(data, type, row) {
                        return `${row.not_converted_serial_no_qty}/${row.serial_no_qty ?? 0}`;
                    }
                },
                {
                    "width": '10%',
                    "targets": 6,
                    render: function(data, type, row) {
                        return `RM ${data}`;
                    }
                },
                {
                    "width": '10%',
                    "targets": 7,
                    render: function(data, type, row) {
                        return `RM ${data}`;
                    }
                },
                {
                    "width": '10%',
                    "targets": 8,
                    render: function(data, type, row) {
                        return data
                    }
                },
                {
                    "width": '10%',
                    "targets": 9,
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
                    "targets": 10,
                    orderable: false,
                    render: function(data, type, row) {
                        return data
                    }
                },
                {
                    "width": '10%',
                    "targets": 11,
                    orderable: false,
                    render: function(data, type, row) {
                        return data
                    }
                },
                {
                    "width": '10%',
                    "targets": 12,
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
                            default:
                                return data
                        }
                    }
                },
                {
                    "width": "5%",
                    "targets": 13,
                    orderable: false,
                    render: function(data, type, row) {
                        return `<div class="flex flex-wrap w-32 items-center justify-end gap-2 px-2">
                            ${
                                row.can_view_pdf ?
                                    `<a href="{{ config('app.url') }}/cash-sale/pdf/${row.id}" class="rounded-full p-2 bg-yellow-200 inline-block" target="_blank" title="{!! __('View PDF') !!}">
                                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512" height="512"><path d="M16,0H8A5.006,5.006,0,0,0,3,5V23a1,1,0,0,0,1.564.825L6.67,22.386l2.106,1.439a1,1,0,0,0,1.13,0l2.1-1.439,2.1,1.439a1,1,0,0,0,1.131,0l2.1-1.438,2.1,1.437A1,1,0,0,0,21,23V5A5.006,5.006,0,0,0,16,0Zm3,21.1-1.1-.752a1,1,0,0,0-1.132,0l-2.1,1.439-2.1-1.439a1,1,0,0,0-1.131,0l-2.1,1.439-2.1-1.439a1,1,0,0,0-1.129,0L5,21.1V5A3,3,0,0,1,8,2h8a3,3,0,0,1,3,3Z"/><rect x="7" y="8" width="10" height="2" rx="1"/><rect x="7" y="12" width="8" height="2" rx="1"/></svg>
                                    </a>` : ''
                            }
                            ${
                                row.can_edit ? `
                                    <a href="{{ config('app.url') }}/cash-sale/edit/${row.id}" class="rounded-full p-2 bg-blue-200 inline-block" title="{!! __('Edit') !!}">
                                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="m18.813,10c.309,0,.601-.143.79-.387s.255-.562.179-.861c-.311-1.217-.945-2.329-1.833-3.217l-3.485-3.485c-1.322-1.322-3.08-2.05-4.95-2.05h-4.515C2.243,0,0,2.243,0,5v14c0,2.757,2.243,5,5,5h3c.552,0,1-.448,1-1s-.448-1-1-1h-3c-1.654,0-3-1.346-3-3V5c0-1.654,1.346-3,3-3h4.515c.163,0,.325.008.485.023v4.977c0,1.654,1.346,3,3,3h5.813Zm-6.813-3V2.659c.379.218.732.488,1.05.806l3.485,3.485c.314.314.583.668.803,1.05h-4.338c-.551,0-1-.449-1-1Zm11.122,4.879c-1.134-1.134-3.11-1.134-4.243,0l-6.707,6.707c-.755.755-1.172,1.76-1.172,2.829v1.586c0,.552.448,1,1,1h1.586c1.069,0,2.073-.417,2.828-1.172l6.707-6.707c.567-.567.879-1.32.879-2.122s-.312-1.555-.878-2.121Zm-1.415,2.828l-6.708,6.707c-.377.378-.879.586-1.414.586h-.586v-.586c0-.534.208-1.036.586-1.414l6.708-6.707c.377-.378,1.036-.378,1.414,0,.189.188.293.439.293.707s-.104.518-.293.707Z"/></svg>
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
                    var url = "{{ route('cash_sale.get_data') }}"

                    url =
                        `${url}?page=${ INIT_LOAD == true && DEFAULT_PAGE != null ? DEFAULT_PAGE : info.page + 1 }`
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
            console.debug(id)

            $('#delete-modal #title').text('{{ __("Void Confirmation") }}')
            $('#delete-modal #yes-btn').attr('href', `{{ config('app.url') }}/cash-sale/cancel/${id}`)
            $('#delete-modal').addClass('show-modal')
        })
    </script>
@endpush
