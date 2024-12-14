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
<div class="mb-6 flex justify-between">
    <x-app.page-title>{{ __('Invoice') }}</x-app.page-title>
    <div class="flex gap-x-2">
        <button id="sync-button" class="bg-blue-400 shadow rounded-md py-2 px-4 flex items-center gap-x-2">
            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Sync (<span id="selected-count">0</span>)
        </button>
        @can('sale.billing.convert')
            <a href="{{ route('billing.to_billing') }}"
                class="bg-purple-200 shadow rounded-md py-2 px-4 flex items-center gap-x-2" id="convert-to-inv-btn">
                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="arrow-circle-down" viewBox="0 0 24 24"
                    width="512" height="512">
                    <g>
                        <path
                            d="M23,16H2.681l.014-.015L4.939,13.7a1,1,0,1,0-1.426-1.4L1.274,14.577c-.163.163-.391.413-.624.676a2.588,2.588,0,0,0,0,3.429c.233.262.461.512.618.67l2.245,2.284a1,1,0,0,0,1.426-1.4L2.744,18H23a1,1,0,0,0,0-2Z" />
                        <path
                            d="M1,8H21.255l-2.194,2.233a1,1,0,1,0,1.426,1.4l2.239-2.279c.163-.163.391-.413.624-.675a2.588,2.588,0,0,0,0-3.429c-.233-.263-.461-.513-.618-.67L20.487,2.3a1,1,0,0,0-1.426,1.4l2.251,2.29L21.32,6H1A1,1,0,0,0,1,8Z" />
                    </g>
                </svg>
                <span>{{ __('Convert to Billing') }}</span>
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
                <th>
                    <input type="checkbox" id="select-all" class="rounded">
                </th>
                <th>{{ __('Invoice ID') }}</th>
                <th></th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            dt.draw()
        })

        $('#data-table').on('draw.dt', function() {
            $('.order-checkbox').each(function() {
                let invoiceId = $(this).data('id');
                
                if (selectedInvoices.some(invoice => invoice.id === invoiceId)) {
                    $(this).prop('checked', true);
                } else {
                    $(this).prop('checked', false);
                }
            });

            checkSelectAllStatus();
        });

        // Datatable
        var dt = new DataTable('#data-table', {
            dom: 'rtip',
            pagingType: 'numbers',
            pageLength: 10,
            processing: true,
            serverSide: true,
            order: [],
            columns: [
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    width: '5%',
                    render: function (data, type, row) {
                        return `<input type="checkbox" class="row-checkbox rounded" value="${row.sku}">`;
                    }
                },
                { data: 'sku' },
                { data: 'company' },
                { data: 'invoice_date' },
                { data: 'convert_to' },
                { data: 'status' },
                { data: 'action' },
            ],
            columnDefs: [
                {
                    "width": "5%",
                    "targets": 0,
                    'orderable': false,
                },
                {
                    "width": "10%",
                    "targets": 1,
                    render: function (data, type, row) {
                        return data
                    }
                },
                {
                    "width": "5%",
                    "targets": 2,
                    orderable: false,
                    render: function (data, type, row) {
                        return `<div class="flex items-center justify-end gap-x-2 px-2">
                                <a href="{{ config('app.url') }}/download?file=${row.filename}&type=inv" class="rounded-full p-2 bg-green-200 inline-block" target="_blank">
                                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M17.974,7.146c-.332-.066-.603-.273-.742-.569-1.552-3.271-5.143-5.1-8.735-4.438-3.272,.6-5.837,3.212-6.384,6.501-.162,.971-.15,1.943,.033,2.89,.06,.309-.073,.653-.346,.901-1.145,1.041-1.801,2.524-1.801,4.07,0,3.032,2.467,5.5,5.5,5.5h11c4.136,0,7.5-3.364,7.5-7.5,0-3.565-2.534-6.658-6.026-7.354Zm-1.474,12.854H5.5c-1.93,0-3.5-1.57-3.5-3.5,0-.983,.418-1.928,1.146-2.59,.786-.715,1.155-1.773,.963-2.763-.138-.712-.146-1.445-.024-2.181,.403-2.422,2.365-4.421,4.771-4.862,.385-.07,.768-.104,1.145-.104,2.312,0,4.406,1.289,5.422,3.434,.414,.872,1.2,1.481,2.158,1.673,2.559,.511,4.417,2.778,4.417,5.394,0,3.032-2.467,5.5-5.5,5.5Zm-1.379-6.707c.391,.391,.391,1.023,0,1.414l-2.707,2.707c-.387,.387-.896,.582-1.405,.584l-.009,.002-.009-.002c-.509-.002-1.018-.197-1.405-.584l-2.707-2.707c-.391-.391-.391-1.023,0-1.414s1.023-.391,1.414,0l1.707,1.707v-5c0-.553,.448-1,1-1s1,.447,1,1v5l1.707-1.707c.391-.391,1.023-.391,1.414,0Z"/></svg>
                                </a>
                           </div>`
                    }
                },
            ],
            ajax: {
                data: function () {
                    var info = $('#data-table').DataTable().page.info();
                    var url = "{{ route('invoice.get_data') }}"

                    url = `${url}?page=${info.page + 1}`
                    $('#data-table').DataTable().ajax.url(url);
                },
            },
        });
        $('#filter_search').on('keyup', function () {
            dt.search($(this).val()).draw()
        })
        // Add select all functionality
        $('#select-all').on('change', function () {
            $('.row-checkbox').prop('checked', $(this).prop('checked'));
        });

        // Get selected rows
        function getSelectedRows() {
            return $('.row-checkbox:checked').map(function () {
                return $(this).val();
            }).get();
        }
        // First, hide the sync button initially
        $('#sync-button').hide();

        // Add event listeners for checkbox changes
        $(document).on('change', '.row-checkbox, #select-all', function () {
            let selectedCount = $('.row-checkbox:checked').length;
            $('#selected-count').text(selectedCount);

            if (selectedCount > 0) {
                $('#sync-button').show();
            } else {
                $('#sync-button').hide();
            }
        });

        // Update count when "Select All" is clicked
        $('#select-all').on('change', function () {
            let isChecked = $(this).prop('checked');
            $('.row-checkbox').prop('checked', isChecked);
            let selectedCount = isChecked ? $('.row-checkbox').length : 0;
            $('#selected-count').text(selectedCount);
        });

        function getSelectedSkus() {
            let selectedSkus = [];
            $('.row-checkbox:checked').each(function () {
                let row = dt.row($(this).closest('tr')).data();
                selectedSkus.push(row);
            });
            return selectedSkus;
        }

        $('#sync-button').on('click', function () {
            let selectedSkus = getSelectedSkus();
            console.log(selectedSkus);
            if (selectedSkus.length > 0) {
                $.ajax({
                    url: '{{ route("invoice.sync") }}',
                    type: 'POST',
                    data: { skus: selectedSkus },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        if (response.success) {
                            // Maybe refresh the table
                            dt.ajax.reload();
                            // Show success message
                            alert(response.message);
                        }
                    },
                    error: function (xhr) {
                        alert('Sync failed: ' + xhr.responseJSON.message);
                    }
                });
            }
        });
    </script>
@endpush
@push('styles')
<style>
    #loading-indicator {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }

    .loader {
        width: 48px;
        height: 48px;
        border: 5px solid #FFF;
        border-bottom-color: transparent;
        border-radius: 50%;
        display: inline-block;
        box-sizing: border-box;
        animation: rotation 1s linear infinite;
    }

    @keyframes rotation {
        0% {
            transform: rotate(0deg);
        }
        100% {
            transform: rotate(360deg);
        }
    }
</style>
@endpush