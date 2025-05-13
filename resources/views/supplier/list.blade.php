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
        <x-app.page-title class="mb-4 md:mb-0">{{ __('Supplier') }}</x-app.page-title>
        @can('supplier.create')
        <div class="flex gap-x-2">
            <a href="#" class="bg-purple-200 shadow rounded-md py-2 px-4 flex items-center gap-x-2" id="sync-btn">
                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="arrow-circle-down" viewBox="0 0 24 24" width="512" height="512"><g><path d="M23,16H2.681l.014-.015L4.939,13.7a1,1,0,1,0-1.426-1.4L1.274,14.577c-.163.163-.391.413-.624.676a2.588,2.588,0,0,0,0,3.429c.233.262.461.512.618.67l2.245,2.284a1,1,0,0,0,1.426-1.4L2.744,18H23a1,1,0,0,0,0-2Z"/><path d="M1,8H21.255l-2.194,2.233a1,1,0,1,0,1.426,1.4l2.239-2.279c.163-.163.391-.413.624-.675a2.588,2.588,0,0,0,0-3.429c-.233-.263-.461-.513-.618-.67L20.487,2.3a1,1,0,0,0-1.426,1.4l2.251,2.29L21.32,6H1A1,1,0,0,0,1,8Z"/></g></svg>
                <span>{{ __('Sync to Autocount') }}</span>
            </a>
            <x-app.button.button class="shadow gap-x-2 bg-emerald-300" id="export-btn">
                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M18.66,20.9c-.41-.37-1.05-.33-1.41,.09-.57,.65-1.39,1.02-2.25,1.02H5c-1.65,0-3-1.35-3-3V5c0-1.65,1.35-3,3-3h4.51c.16,0,.33,0,.49,.02V7c0,1.65,1.35,3,3,3h5.81c.31,0,.6-.14,.79-.39s.25-.56,.18-.86c-.31-1.22-.94-2.33-1.83-3.22l-3.48-3.48c-1.32-1.32-3.08-2.05-4.95-2.05H5C2.24,0,0,2.24,0,5v14c0,2.76,2.24,5,5,5H15c1.43,0,2.8-.62,3.75-1.69,.37-.41,.33-1.05-.09-1.41ZM12,2.66c.38,.22,.73,.49,1.05,.81l3.48,3.48c.31,.31,.58,.67,.8,1.05h-4.34c-.55,0-1-.45-1-1V2.66Zm11.13,15.43l-1.61,1.61c-.2,.2-.45,.29-.71,.29s-.51-.1-.71-.29c-.39-.39-.39-1.02,0-1.41l1.29-1.29h-7.4c-.55,0-1-.45-1-1s.45-1,1-1h7.4l-1.29-1.29c-.39-.39-.39-1.02,0-1.41s1.02-.39,1.41,0l1.61,1.61c1.15,1.15,1.15,3.03,0,4.19Z"/></svg>
                {{ __('Export Excel') }}
            </x-app.button.button>
            <a href="{{ route('supplier.create') }}" class="bg-yellow-400 shadow rounded-md py-2 px-4 flex items-center gap-x-2">
                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Capa_1" x="0px" y="0px" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve" width="512" height="512">
                    <path d="M480,224H288V32c0-17.673-14.327-32-32-32s-32,14.327-32,32v192H32c-17.673,0-32,14.327-32,32s14.327,32,32,32h192v192   c0,17.673,14.327,32,32,32s32-14.327,32-32V288h192c17.673,0,32-14.327,32-32S497.673,224,480,224z"/>
                </svg>
                {{ __('New') }}
            </a>
        </div>
        @endcan
    </div>
    @include('components.app.alert.parent')
    <div>
        <!-- Filters -->
        <div class="flex gap-2 max-w-2xl w-full mb-4">
            <div class="flex-1">
                <x-app.input.input name="filter_search" id="filter_search" class="flex items-center" placeholder="{{ __('Search') }}">
                    <div class="rounded-md border border-transparent p-1 ml-1">
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24"><path d="M23.707,22.293l-5.969-5.969a10.016,10.016,0,1,0-1.414,1.414l5.969,5.969a1,1,0,0,0,1.414-1.414ZM10,18a8,8,0,1,1,8-8A8.009,8.009,0,0,1,10,18Z"/></svg>
                    </div>
                </x-app.input.input>
            </div>
            <div class="flex-1 flex">
                <x-app.input.select name='filter_company_group' id='filter_company_group' class="w-full capitalize">
                    <option value="">Select a company group</option>
                    @foreach ($company_group as $key => $val)
                        <option value="{{ $key }}">{{ $val }}</option>
                    @endforeach
                </x-app.input.select>
            </div>
        </div>

        <!-- Table -->
        <table id="data-table" class="text-sm rounded-lg overflow-hidden" style="width: 100%;">
            <thead>
                <tr>
                    <th>
                        <input type="checkbox" id="select-all">
                    </th>
                    <th>{{ __('Code') }}</th>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Phone Number') }}</th>
                    <th>{{ __('Company Name') }}</th>
                    <th>{{ __('Company Group') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <x-app.modal.delete-modal/>
    <div id="loading-indicator" style="display: none;">
        <span class="loader"></span>
    </div>
@endsection

@push('scripts')
    <script>
        TABLE_FILTER = {
            'company_group': '',
        }
        FOR_ROLE = @json($for_role ?? null);

        // Datatable
        var dt = new DataTable('#data-table', {
            dom: 'rtip',
            pagingType: 'numbers',
            pageLength: 10,
            processing: true,
            serverSide: true,
            order: [],
            columns: [
                { data: 'id' },
                { data: 'sku' },
                { data: 'name' },
                { data: 'phone_number' },
                { data: 'company_name' },
                { data: 'company_group' },
                { data: 'action' },
            ],
            columnDefs: [
                {
                    "width": "2%",
                    "targets": 0,
                    orderable: false,
                    render: function(data, type, row) {
                        var disabled = row.enable ? 'disabled' : '';
                        var style = row.enable ? 'style="opacity: 0.5; cursor: not-allowed;"' : '';
                        return `<input type="checkbox" class="order-checkbox" data-id="${data}" ${disabled} ${style}>`;
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
                    orderable: false,
                    render: function(data, type, row) {
                        return data
                    }
                },
                {
                    "width": "5%",
                    "targets": 6,
                    "orderable": false,
                    render: function (data, type, row) {
                       return  `<div class="flex items-center justify-end gap-x-2 px-2">
                            <a href="{{ config('app.url') }}/supplier/grn-history/${row.id}" class="rounded-full p-2 bg-emerald-200 inline-block" title="{!! __('GRN History') !!}">
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512" height="512"><path d="M12,0A11.972,11.972,0,0,0,4,3.073V1A1,1,0,0,0,2,1V4A3,3,0,0,0,5,7H8A1,1,0,0,0,8,5H5a.854.854,0,0,1-.1-.021A9.987,9.987,0,1,1,2,12a1,1,0,0,0-2,0A12,12,0,1,0,12,0Z"/><path d="M12,6a1,1,0,0,0-1,1v5a1,1,0,0,0,.293.707l3,3a1,1,0,0,0,1.414-1.414L13,11.586V7A1,1,0,0,0,12,6Z"/></svg>
                            </a>
                            ${
                                row.can_edit ? `
                            <a href="{{ config('app.url') }}/supplier/edit/${row.id}" class="rounded-full p-2 bg-blue-200 inline-block" title="{!! __('Edit') !!}">
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
            ],
            ajax: {
                data: function(){
                    var info = $('#data-table').DataTable().page.info();
                    var url = "{{ route('supplier.get_data') }}"

                    url = `${url}?page=${ info.page + 1 }&company_group=${ TABLE_FILTER['company_group'] }`
                    $('#data-table').DataTable().ajax.url(url);
                },
            },
        });
        $('#filter_search').on('keyup', function() {
            dt.search($(this).val()).draw()
        })
        $('#filter_company_group').on('change', function() {
            TABLE_FILTER['company_group'] = $(this).val()

            dt.draw()
        })

        $('#data-table').on('click', '.delete-btns', function() {
            id = $(this).data('id')

            $('#delete-modal #yes-btn').attr('href', `{{ config('app.url') }}/supplier/delete/${id}`)
            $('#delete-modal').addClass('show-modal')
        })

        let selectedSupplier = [];

        $(document).on('change', '.order-checkbox', function() {
            let supplierId = $(this).data('id');
            let isChecked = this.checked;


            if (isChecked) {

                selectedSupplier.push({ id: supplierId });
            } else {
                selectedSupplier = selectedSupplier.filter(supplier => supplier.id !== supplierId);
            }

            checkSelectAllStatus();
        });


        function checkSelectAllStatus() {
            let enabledCheckboxes = $('.order-checkbox:not(:disabled)').length;
            let checkedEnabledCheckboxes = $('.order-checkbox:not(:disabled):checked').length;

            let allChecked = enabledCheckboxes > 0 && enabledCheckboxes === checkedEnabledCheckboxes;
            $('#select-all').prop('checked', allChecked);
        }

        $('#select-all').on('click', function() {
            let checked = this.checked;
            $('.order-checkbox').each(function() {
                if (!$(this).prop('disabled')) {
                    $(this).prop('checked', checked).trigger('change');
                }
            });
        });

        $('#sync-btn').on('click', function(e) {
            e.preventDefault();
            if (selectedSupplier.length === 0) {
                alert("Please select at least one order to sync.");
                return;
            }
            syncSupplier()
        });

        function syncSupplier(){
            const loadingIndicator = document.getElementById('loading-indicator');
            loadingIndicator.style.display = 'flex';

            let url = "{{ config('app.url') }}";
            url = `${url}/supplier/sync`;
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: url,
                type: 'POST',
                data: JSON.stringify({ suppliers: selectedSupplier }),
                contentType: 'application/json',
                success: function(response) {
                    loadingIndicator.style.display = 'none';
                    $('.order-checkbox').prop('checked', false);
                    selectedSupplier = [];
                    $('#select-all').prop('checked', false);
                    const modal = document.getElementById('update-invoice-date-modal');
                    if (modal && modal.classList.contains('show-modal')) {
                        modal.classList.remove('show-modal');
                    }
                    alert("Sync successful Autocount will be updated within few minutes");
                },
                error: function(error) {
                    loadingIndicator.style.display = 'none';

                    let errorMessage = "An error occurred.";

                    alert(errorMessage);
                }
            });
        }
        $('#export-btn').on('click', function() {
            window.location.href = '{{ route("supplier.export") }}'
        })
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
