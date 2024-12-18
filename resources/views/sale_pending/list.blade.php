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
    <div class="mb-6 flex justify-between items-center">
        <x-app.page-title>{{ __('E-Order Assign') }}</x-app.page-title>
        <div class="flex gap-x-4">
            <a href="#" class="bg-green-200 shadow rounded-md py-2 px-4 flex items-center gap-x-2" id="assign-btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-add" viewBox="0 0 16 16">
                    <path d="M12.5 16a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7m.5-5v1h1a.5.5 0 0 1 0 1h-1v1a.5.5 0 0 1-1 0v-1h-1a.5.5 0 0 1 0-1h1v-1a.5.5 0 0 1 1 0m-2-6a3 3 0 1 1-6 0 3 3 0 0 1 6 0M8 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4"/>
                    <path d="M8.256 14a4.5 4.5 0 0 1-.229-1.004H3c.001-.246.154-.986.832-1.664C4.484 10.68 5.711 10 8 10q.39 0 .74.025c.226-.341.496-.65.804-.918Q8.844 9.002 8 9c-5 0-6 3-6 4s1 1 1 1z"/>
                  </svg>
                <span>{{ __('Assign to SalePerson') }}</span>
            </a>
        </div>
    </div>
    @include('components.app.alert.parent')
    <div>
        <!-- Filters -->
        <div class="flex max-w-xs w-full mb-4">
            <div class="flex-1">
                <x-app.input.input name="filter_search" id="filter_search" class="flex items-center" placeholder="{{ __('Search') }}">
                    <div class="rounded-md border border-transparent p-1 ml-1">
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24"><path d="M23.707,22.293l-5.969-5.969a10.016,10.016,0,1,0-1.414,1.414l5.969,5.969a1,1,0,0,0,1.414-1.414ZM10,18a8,8,0,1,1,8-8A8.009,8.009,0,0,1,10,18Z"/></svg>
                    </div>
                </x-app.input.input>
            </div>
        </div>

        <!-- Table -->
        <table id="data-table" class="text-sm rounded-lg overflow-hidden" style="width: 100%;">
            <thead>
                <tr>
                    <th>
                        <input type="checkbox" id="select-all">
                    </th>
                    <th>{{ __('E-Order Assign ID') }}</th>
                    <th>{{ __('Total Amount') }}</th>
                    <th>{{ __('Platform') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
    <x-app.modal.assign-sale-person-modal/>
    <x-app.modal.to-production-modal/>
    <x-app.modal.delete-modal/>
@endsection

@push('scripts')
    <script>
        $(document).ready(function(){
            dt.draw()
        })

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
                { data: 'total_amount' },
                { data: 'platform' }, 
                { data: 'status' },
                { data: 'action' },
            ],
            columnDefs: [
                {
                    "width": "5%",
                    "targets": 0,
                    orderable: false,
                    render: function(data, type, row) {
                        return `<input type="checkbox" class="order-checkbox" data-id="${data}" data-sku="${row.sku}">`;
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
                    "width": '10%', 
                    "targets": 3,
                    render: function(data, type, row) {
                        return data ?? '-';  
                    }
                },
                {
                    "width": '10%',
                    "targets": 4,
                    orderable: false,
                    render: function(data, type, row) {
                        switch (data) {
                            case 0:
                                return 'Inactive'
                            case 1:
                                return 'Active'
                            case 2:
                                return 'Converted'
                        }
                    }
                },
                {
                    "width": "5%",
                    "targets": 5,
                    orderable: false,
                    render: function (data, type, row) {
                       return  `<div class="flex items-center justify-end gap-x-2 px-2">
                            ${
                                row.can_edit ? `
                                <a href="{{ config('app.url') }}/sale-order/edit/${row.id}" class="rounded-full p-2 bg-blue-200 inline-block" title="{!! __('Edit') !!}">
                                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="m18.813,10c.309,0,.601-.143.79-.387s.255-.562.179-.861c-.311-1.217-.945-2.329-1.833-3.217l-3.485-3.485c-1.322-1.322-3.08-2.05-4.95-2.05h-4.515C2.243,0,0,2.243,0,5v14c0,2.757,2.243,5,5,5h3c.552,0,1-.448,1-1s-.448-1-1-1h-3c-1.654,0-3-1.346-3-3V5c0-1.654,1.346-3,3-3h4.515c.163,0,.325.008.485.023v4.977c0,1.654,1.346,3,3,3h5.813Zm-6.813-3V2.659c.379.218.732.488,1.05.806l3.485,3.485c.314.314.583.668.803,1.05h-4.338c-.551,0-1-.449-1-1Zm11.122,4.879c-1.134-1.134-3.11-1.134-4.243,0l-6.707,6.707c-.755.755-1.172,1.76-1.172,2.829v1.586c0,.552.448,1,1,1h1.586c1.069,0,2.073-.417,2.828-1.172l6.707-6.707c.567-.567.879-1.32.879-2.122s-.312-1.555-.878-2.121Zm-1.415,2.828l-6.708,6.707c-.377.378-.879.586-1.414.586h-.586v-.586c0-.534.208-1.036.586-1.414l6.708-6.707c.377-.378,1.036-.378,1.414,0,.189.188.293.439.293.707s-.104.518-.293.707Z"/></svg>
                                </a>` : ''
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
                data: function(){
                    var info = $('#data-table').DataTable().page.info();
                    var url = "{{ route('pending_order.get_data') }}"

                    url = `${url}?page=${ info.page + 1 }`
                    $('#data-table').DataTable().ajax.url(url);
                },
            },
        });
        
        $('#filter_search').on('keyup', function() {
            dt.search($(this).val()).draw()
        })

        $('#data-table').on('click', '.delete-btns', function() {
            id = $(this).data('id')

            $('#delete-modal #yes-btn').attr('href', `{{ config('app.url') }}/sale-order/delete/${id}`)
            $('#delete-modal').addClass('show-modal')
        })

        
        let selectedOrders = [];

        $('#select-all').on('click', function() {
            let checked = this.checked;
            $('.order-checkbox').each(function() {
                $(this).prop('checked', checked).trigger('change');
            });
        });

        $(document).on('change', '.order-checkbox', function() {
            let orderId = $(this).data('id');
            let orderSku = $(this).data('sku');

            if (this.checked) {
                if (!selectedOrders.some(order => order.id === orderId)) {
                    selectedOrders.push({ id: orderId, sku: orderSku });
                }
            } else {
                selectedOrders = selectedOrders.filter(order => order.id !== orderId);
            }

            checkSelectAllStatus();
            toggleAssignButton();
            console.log(selectedOrders);
        });

        function checkSelectAllStatus() {
            let allChecked = $('.order-checkbox').length === $('.order-checkbox:checked').length;
            $('#select-all').prop('checked', allChecked);
        }


        $('#assign-btn').on('click', function() {
            if ($(this).prop('disabled')) {
                e.preventDefault();
            }

            $('#selected-orders-list ul').empty();

            selectedOrders.forEach(order => {
                $('#selected-orders-list ul').append(`<li>Order ID:  ${order.sku}</li>`);
            });

            $('#assign-sale-person-modal select').empty();

            let id = $(this).data('id');
            $('#assign-sale-person-modal #yes-btn').attr('data-id', id);

            let url = "{{ config('app.url') }}";
            url = `${url}/pending-order/get-sale-person`;

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: url,
                type: 'GET',
                success: function(res) {
                    let opt = new Option('Select a Sale Person', null);
                    $('#assign-sale-person-modal select').append(opt);

                    for (let i = 0; i < res.salesPersons.length; i++) {
                        const salesperson = res.salesPersons[i];

                        let opt = new Option(salesperson.name, salesperson.id);
                        $('#assign-sale-person-modal select').append(opt);
                    }

                    $('#assign-sale-person-modal').addClass('show-modal');
                },
            });
        });

        $('#assign-sale-person-modal #yes-btn').one('click', function(e) {
            e.preventDefault();

            let salesPersonId = $('#assign-sale-person-modal select').val();


            if (!salesPersonId || selectedOrders.length === 0) {
                alert('请选择销售人员并确保至少选择一个订单');
                return;
            }

            let url = "{{ route('pending_order.assign_sale_person') }}";
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: url,
                type: 'POST',
                data: {
                    salesPersonId: salesPersonId === 'null' ? null : salesPersonId,
                    sales: selectedOrders
                },
                success: function(response) {
                    $('#assign-sale-person-modal').removeClass('show-modal');
                    dt.ajax.reload();
                    $(document).trigger('salePersonAssigned');
                    alert(response.message);
                },
                error: function(error) {
                    console.log(error)
                    alert(response.message);
                }
            });
        });

        function toggleAssignButton() {
            if (selectedOrders.length === 0) {
                $('#assign-btn').addClass('bg-gray-200 cursor-not-allowed').removeClass('bg-green-200').prop('disabled', true);
            } else {
                $('#assign-btn').removeClass('bg-gray-200 cursor-not-allowed').addClass('bg-green-200').prop('disabled', false);
            }
        }

        toggleAssignButton();
    </script>
@endpush
