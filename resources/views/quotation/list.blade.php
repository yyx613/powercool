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
        <x-app.page-title class="mb-4 md:mb-0">{{ __('Quotation') }}</x-app.page-title>
        <div class="flex gap-x-4">
            @can('sale.quotation.convert')
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
                    <span>{{ __('Convert to Sale Order') }}</span>
                </a>
            @endcan
            @can('sale.quotation.create')
                <a href="{{ route('quotation.create') }}"
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
        <table id="data-table" class="text-sm rounded-lg overflow-hidden" style="width: 100%;">
            <thead>
                <tr>
                    <th>{{ __('Doc No.') }}</th>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Validity') }}</th>
                    <th>{{ __('Transfer To') }}</th>
                    <th>{{ __('Debtor Code') }}</th>
                    <th>{{ __('Debtor Name') }}</th>
                    <th>{{ __('Agent') }}</th>
                    <th>{{ __('Store') }}</th>
                    <th>{{ __('Curr. Code') }}</th>
                    <th>{{ __('Total') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <x-app.modal.delete-modal />
    <x-app.modal.cancel-sale-order-modal />
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
                    data: 'validity'
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
                    data: 'total'
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
                        if (row.is_draft == true) {
                            return `
                                <span>${data}</span>
                                <span class="text-xs text-blue-700">Draft</span>
                            `
                        }
                        return data
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
                {
                    "width": "10%",
                    "targets": 4,
                    render: function(data, type, row) {
                        return data
                    }
                },
                {
                    "width": "10%",
                    "targets": 5,
                    render: function(data, type, row) {
                        return data
                    }
                },
                {
                    "width": "10%",
                    "targets": 6,
                    render: function(data, type, row) {
                        return data
                    }
                },
                {
                    "width": "10%",
                    "targets": 7,
                    orderable: false,
                    render: function(data, type, row) {
                        return data
                    }
                },
                {
                    "width": "10%",
                    "targets": 8,
                    orderable: false,
                    render: function(data, type, row) {
                        return data
                    }
                },
                {
                    "width": "10%",
                    "targets": 9,
                    render: function(data, type, row) {
                        return `RM ${data}`
                    }
                },
                {
                    "width": '10%',
                    "targets": 10,
                    render: function(data, type, row) {
                        switch (data) {
                            case 0:
                                return "{!! __('Inactive') !!}"
                            case 1:
                                return "{!! __('Active') !!}"
                            case 2:
                                return "{!! __('Converted') !!}"
                            case 3:
                                return "{!! __('Cancelled') !!}"
                            case 4:
                                if (row.is_approval_cancellation == true) {
                                    return "{!! __('Pending Cancellation') !!}"
                                } else {
                                    return "{!! __('Pending Approval') !!}"
                                }
                            case 5:
                                return "{!! __('Approved') !!}"
                            case 6:
                            case 7:
                                return "{!! __('Rejected') !!}"
                            default:
                                return data
                        }
                    }
                },
                {
                    "width": "5%",
                    "targets": 11,
                    orderable: false,
                    render: function(data, type, row) {
                        return `<div class="flex flex-wrap w-32 items-center justify-end gap-2 px-2">
                            <a href="{{ config('app.url') }}/quotation/create?quo=${row.id}" class="rounded-full p-2 bg-green-200 inline-block" title="{!! __('Replicate') !!}">
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="m19,0h-6c-2.757,0-5,2.243-5,5v6c0,2.757,2.243,5,5,5h6c2.757,0,5-2.243,5-5v-6c0-2.757-2.243-5-5-5Zm3,11c0,1.654-1.346,3-3,3h-6c-1.654,0-3-1.346-3-3v-6c0-1.654,1.346-3,3-3h6c1.654,0,3,1.346,3,3v6Zm-6,8c0,2.757-2.243,5-5,5h-6c-2.757,0-5-2.243-5-5v-6c0-2.757,2.243-5,5-5,.553,0,1,.448,1,1s-.447,1-1,1c-1.654,0-3,1.346-3,3v6c0,1.654,1.346,3,3,3h6c1.654,0,3-1.346,3-3,0-.552.447-1,1-1s1,.448,1,1Z"/></svg>
                            </a>
                            ${
                                row.can_reuse ?
                                    `<a href="{{ config('app.url') }}/quotation/reuse/${row.id}" class="rounded-full p-2 bg-green-200 inline-block" title="{!! __('Reuse') !!}">
                                                                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24">
                                                                                <path d="m24,12.5c0,2.062-.819,3.984-2.308,5.412-.399.382-1.031.369-1.414-.029-.382-.398-.369-1.031.029-1.414,1.092-1.047,1.692-2.457,1.692-3.969,0-2.614-1.857-4.882-4.417-5.394-.958-.191-1.744-.8-2.158-1.673-1.183-2.494-3.814-3.832-6.568-3.329-2.406.44-4.368,2.439-4.771,4.862-.122.737-.114,1.471.023,2.18.191.989-.178,2.047-.962,2.762-.729.663-1.147,1.606-1.147,2.591,0,1.17.582,2.259,1.557,2.911.459.308.582.929.274,1.388-.193.288-.51.443-.832.443-.191,0-.385-.055-.556-.169-1.53-1.024-2.443-2.734-2.443-4.573,0-1.546.656-3.03,1.801-4.07.272-.248.405-.594.346-.902-.184-.943-.194-1.916-.033-2.889C2.66,3.349,5.226.736,8.497.137c3.59-.658,7.184,1.167,8.734,4.439.141.295.411.502.742.569,3.492.697,6.026,3.79,6.026,7.354Zm-12-.5h-1.086l1.793-1.793c.391-.391.391-1.023,0-1.414s-1.023-.391-1.414,0l-2.75,2.75c-.78.78-.78,2.049,0,2.828l2.75,2.75c.195.195.451.293.707.293s.512-.098.707-.293c.391-.391.391-1.023,0-1.414l-1.707-1.707h1c2.206,0,4,1.794,4,4s-1.794,4-4,4-4-1.794-4-4c0-.271.026-.54.08-.802.109-.541-.241-1.068-.782-1.179-.539-.11-1.068.241-1.179.782-.079.392-.119.795-.119,1.198,0,3.309,2.691,6,6,6s6-2.691,6-6-2.691-6-6-6Z"/>
                                                                                </svg>
                                                                            </a>` : ''
                            }
                            ${
                                row.can_cancel ?
                                    `<button class="rounded-full p-2 bg-violet-200 inline-block cancel-btns" data-id="${row.id}" title="{!! __('Cancel') !!}">
                                                                                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512" height="512"><path d="M16,8a1,1,0,0,0-1.414,0L12,10.586,9.414,8A1,1,0,0,0,8,9.414L10.586,12,8,14.586A1,1,0,0,0,9.414,16L12,13.414,14.586,16A1,1,0,0,0,16,14.586L13.414,12,16,9.414A1,1,0,0,0,16,8Z"/><path d="M12,0A12,12,0,1,0,24,12,12.013,12.013,0,0,0,12,0Zm0,22A10,10,0,1,1,22,12,10.011,10.011,0,0,1,12,22Z"/></svg>
                                                                                        </button>` : ''
                            }
                            ${
                                row.can_view_pdf ? `
                                                                                        <a href="{{ config('app.url') }}/quotation/pdf/${row.id}" class="rounded-full p-2 bg-yellow-200 inline-block" target="_blank" title="{!! __('View PDF') !!}">
                                                                                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512" height="512"><path d="M16,0H8A5.006,5.006,0,0,0,3,5V23a1,1,0,0,0,1.564.825L6.67,22.386l2.106,1.439a1,1,0,0,0,1.13,0l2.1-1.439,2.1,1.439a1,1,0,0,0,1.131,0l2.1-1.438,2.1,1.437A1,1,0,0,0,21,23V5A5.006,5.006,0,0,0,16,0Zm3,21.1-1.1-.752a1,1,0,0,0-1.132,0l-2.1,1.439-2.1-1.439a1,1,0,0,0-1.131,0l-2.1,1.439-2.1-1.439a1,1,0,0,0-1.129,0L5,21.1V5A3,3,0,0,1,8,2h8a3,3,0,0,1,3,3Z"/><rect x="7" y="8" width="10" height="2" rx="1"/><rect x="7" y="12" width="8" height="2" rx="1"/></svg>
                                                                                        </a>` : ''
                            }
                            ${
                                row.can_edit ? 
                                    (row.view_only ?
                                        `<a href="{{ config('app.url') }}/quotation/view/${row.id}" class="rounded-full p-2 bg-blue-200 inline-block" title="{!! __('View') !!}">
                                                                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512" height="512"><path d="M23.271,9.419C21.72,6.893,18.192,2.655,12,2.655S2.28,6.893.729,9.419a4.908,4.908,0,0,0,0,5.162C2.28,17.107,5.808,21.345,12,21.345s9.72-4.238,11.271-6.764A4.908,4.908,0,0,0,23.271,9.419Zm-1.705,4.115C20.234,15.7,17.219,19.345,12,19.345S3.766,15.7,2.434,13.534a2.918,2.918,0,0,1,0-3.068C3.766,8.3,6.781,4.655,12,4.655s8.234,3.641,9.566,5.811A2.918,2.918,0,0,1,21.566,13.534Z"/><path d="M12,7a5,5,0,1,0,5,5A5.006,5.006,0,0,0,12,7Zm0,8a3,3,0,1,1,3-3A3,3,0,0,1,12,15Z"/></svg>
                                                                </a>`:
                                        `<a href="{{ config('app.url') }}/quotation/edit/${row.id}" class="rounded-full p-2 bg-blue-200 inline-block" title="{!! __('Edit') !!}">
                                                                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="m18.813,10c.309,0,.601-.143.79-.387s.255-.562.179-.861c-.311-1.217-.945-2.329-1.833-3.217l-3.485-3.485c-1.322-1.322-3.08-2.05-4.95-2.05h-4.515C2.243,0,0,2.243,0,5v14c0,2.757,2.243,5,5,5h3c.552,0,1-.448,1-1s-.448-1-1-1h-3c-1.654,0-3-1.346-3-3V5c0-1.654,1.346-3,3-3h4.515c.163,0,.325.008.485.023v4.977c0,1.654,1.346,3,3,3h5.813Zm-6.813-3V2.659c.379.218.732.488,1.05.806l3.485,3.485c.314.314.583.668.803,1.05h-4.338c-.551,0-1-.449-1-1Zm11.122,4.879c-1.134-1.134-3.11-1.134-4.243,0l-6.707,6.707c-.755.755-1.172,1.76-1.172,2.829v1.586c0,.552.448,1,1,1h1.586c1.069,0,2.073-.417,2.828-1.172l6.707-6.707c.567-.567.879-1.32.879-2.122s-.312-1.555-.878-2.121Zm-1.415,2.828l-6.708,6.707c-.377.378-.879.586-1.414.586h-.586v-.586c0-.534.208-1.036.586-1.414l6.708-6.707c.377-.378,1.036-.378,1.414,0,.189.188.293.439.293.707s-.104.518-.293.707Z"/></svg>
                                                                </a>`
                                    ) : ''
                            }
                            ${

                                row.can_delete && row.status != 2 ? `
                                                                                                    <button class="rounded-full p-2 bg-red-200 inline-block delete-btns" data-id="${row.id}" title="{!! __('Delete') !!}">
                                                                                                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512" height="512"><path d="M21,4H17.9A5.009,5.009,0,0,0,13,0H11A5.009,5.009,0,0,0,6.1,4H3A1,1,0,0,0,3,6H4V19a5.006,5.006,0,0,0,5,5h6a5.006,5.006,0,0,0,5-5V6h1a1,1,0,0,0,0-2ZM11,2h2a3.006,3.006,0,0,1,2.829,2H8.171A3.006,3.006,0,0,1,11,2Zm7,17a3,3,0,0,1-3,3H9a3,3,0,0,1-3-3V6H18Z"/><path d="M10,18a1,1,0,0,0,1-1V11a1,1,0,0,0-2,0v6A1,1,0,0,0,10,18Z"/><path d="M14,18a1,1,0,0,0,1-1V11a1,1,0,0,0-2,0v6A1,1,0,0,0,14,18Z"/></svg>
                                                                                                    </button>` : ''
                            }
                       </div>`
                    }
                },
            ],
            ajax: {
                data: function() {
                    var info = $('#data-table').DataTable().page.info();
                    var url = "{{ route('quotation.get_data') }}"

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

        // $('#data-table').on('click', '.to-production-btns', function() {
        //     $('#to-production-modal select').empty()

        //     let id = $(this).data('id')
        //     $('#to-production-modal #yes-btn').attr('data-id', id)

        //     let url = "{{ config('app.url') }}"
        //     url = `${url}/sale/get-products/${id}`

        //     $.ajax({
        //         headers: {
        //             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        //         },
        //         url: url,
        //         type: 'GET',
        //         success: function(res) {
        //             let opt = new Option('Select a product', null)
        //             $('#to-production-modal select').append(opt)

        //             for (let i = 0; i < res.products.length; i++) {
        //                 const elem = res.products[i];

        //                 let opt = new Option(elem.model_name, elem.id)
        //                 $('#to-production-modal select').append(opt)
        //             }

        //             $('#to-production-modal').addClass('show-modal')
        //         },
        //     });
        // })

        $('#data-table').on('click', '.cancel-btns', function() {
            id = $(this).data('id')

            $('#cancel-sale-order-modal #title').text('{{ __('Cancel Confirmation') }}')
            $('#cancel-sale-order-modal #charge-container').addClass('hidden')
            $('#cancel-sale-order-modal form').attr('action', `{{ config('app.url') }}/quotation/cancel/${id}`)
            $('#cancel-sale-order-modal').addClass('show-modal')
        })
        $('#confirmation-modal #yes-btn').on('click', function() {
            let url = $(this).attr('data-link')

            window.location.href = url
        })
        $('#data-table').on('click', '.delete-btns', function() {
            id = $(this).data('id')

            $('#delete-modal #yes-btn').attr('href', `{{ config('app.url') }}/quotation/delete/${id}`)
            $('#delete-modal').addClass('show-modal')
        })
    </script>
@endpush
