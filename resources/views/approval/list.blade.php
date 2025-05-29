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

        #data-table tbody tr td:nth-of-type(2) {
            text-transform: capitalize;
        }

        #data-table tbody tr:last-of-type td {
            border-bottom: none;
        }

        #data-table tbody tr[data-unread="true"] {
            font-weight: bold;
        }

        #data-table tbody tr[data-unread="false"] {
            color: grey;
        }
    </style>
@endpush

@section('content')
    <!-- Records -->
    <div class="mb-6 flex justify-between items-center">
        <x-app.page-title>{{ __('Approval') }}</x-app.page-title>
    </div>
    @include('components.app.alert.parent')
    <div>
        <!-- Filters -->
        <div class="flex justify-between items-center mb-4">
            <div class="flex gap-x-2 w-full max-w-xs">
                <div class="flex-1 flex">
                    <x-app.input.select name='filter_status' id='filter_status' class="w-full capitalize">
                        <option value="">Select a status</option>
                        @foreach ($statuses as $key => $status)
                            <option value="{{ $key }}" @selected(isset($default_status) && $default_status == $key)>{{ $status }}</option>
                        @endforeach
                    </x-app.input.select>
                </div>
            </div>
        </div>

        <!-- Table -->
        <table id="data-table" class="text-sm rounded-lg overflow-hidden" style="width: 100%;">
            <thead>
                <tr>
                    <th>{{ __('No.') }}</th>
                    <th>{{ __('Type') }}</th>
                    <th>{{ __('SKU') }}</th>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Description') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
@endsection

@push('scripts')
    <script>
        DEFAULT_STATUS = @json($default_status ?? null);
        TABLE_FILTER = {
            'status': DEFAULT_STATUS ?? '',
        }

        // Datatable
        var dt = new DataTable('#data-table', {
            dom: 'rtip',
            pagingType: 'numbers',
            pageLength: 10,
            processing: true,
            serverSide: true,
            order: [],
            columns: [{
                    data: 'no'
                },
                {
                    data: 'type'
                },
                {
                    data: 'object_sku'
                },
                {
                    data: 'date'
                },
                {
                    data: 'description'
                },
                {
                    data: 'status'
                },
                {
                    data: 'action'
                },
            ],
            columnDefs: [{
                    "width": "0%",
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
                        if (data != null) {
                            if (data.includes('FactoryRawMaterial')) {
                                return 'Production Material Transfer Request';
                            } else if (data.includes('DeliveryOrder')) {
                                return 'Delivery Order';
                            } else if (row.data != null && row.data.is_quo == true) {
                                return 'Quotation';
                            } else {
                                return 'Sale Order'
                            }
                        }
                        return ''
                    }
                },
                {
                    "width": "10%",
                    "targets": 2,
                    'orderable': false,
                    render: function(data, type, row) {
                        return data
                    }
                },
                {
                    "width": "10%",
                    "targets": 3,
                    'orderable': false,
                    render: function(data, type, row) {
                        return `${ moment(data).format('DD MMM, YYYY')}<br>${moment(data).format('HH:mm') }`
                    }
                },
                {
                    "width": "20%",
                    "targets": 4,
                    'orderable': false,
                    render: function(data, type, row) {
                        return data
                    }
                },
                {
                    "width": "10%",
                    "targets": 5,
                    'orderable': false,
                    render: function(data, type, row) {
                        if (data == 0) {
                            return 'Pending Approval'
                        } else if (data == 1) {
                            return 'Approved'
                        } else if (data == 2) {
                            return 'Rejected'
                        }
                        return data
                    }
                },
                {
                    "width": "5%",
                    "targets": 6,
                    "orderable": false,
                    render: function(data, type, row) {
                        return `<div class="flex items-center justify-end gap-x-2 px-2">
                            ${
                                row.can_view ?
                                    `<a href="${row.view_url}" class="rounded-full p-2 bg-blue-200 inline-block" title="{!! __('View') !!}">
                                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512" height="512"><path d="M23.271,9.419C21.72,6.893,18.192,2.655,12,2.655S2.28,6.893.729,9.419a4.908,4.908,0,0,0,0,5.162C2.28,17.107,5.808,21.345,12,21.345s9.72-4.238,11.271-6.764A4.908,4.908,0,0,0,23.271,9.419Zm-1.705,4.115C20.234,15.7,17.219,19.345,12,19.345S3.766,15.7,2.434,13.534a2.918,2.918,0,0,1,0-3.068C3.766,8.3,6.781,4.655,12,4.655s8.234,3.641,9.566,5.811A2.918,2.918,0,0,1,21.566,13.534Z"/><path d="M12,7a5,5,0,1,0,5,5A5.006,5.006,0,0,0,12,7Zm0,8a3,3,0,1,1,3-3A3,3,0,0,1,12,15Z"/></svg>
                                    </a>` : ''
                            }
                            <button class="rounded-full p-2 bg-red-200 inline-block ${row.pending_approval ? '' : 'hidden'} reject-btns" data-id="${row.id}" title="{!! __('Reject') !!}">
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512" height="512"><path d="M16,8a1,1,0,0,0-1.414,0L12,10.586,9.414,8A1,1,0,0,0,8,9.414L10.586,12,8,14.586A1,1,0,0,0,9.414,16L12,13.414,14.586,16A1,1,0,0,0,16,14.586L13.414,12,16,9.414A1,1,0,0,0,16,8Z"/><path d="M12,0A12,12,0,1,0,24,12,12.013,12.013,0,0,0,12,0Zm0,22A10,10,0,1,1,22,12,10.011,10.011,0,0,1,12,22Z"/></svg>
                            </button>
                            <button class="rounded-full p-2 bg-green-200 inline-block ${row.pending_approval ? '' : 'hidden'} approve-btns" data-id="${row.id}" title="{!! __('Approve') !!}">
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="m18.214,9.098c.387.394.381,1.027-.014,1.414l-4.426,4.345c-.783.768-1.791,1.151-2.8,1.151-.998,0-1.996-.376-2.776-1.129l-1.899-1.867c-.394-.387-.399-1.02-.012-1.414.386-.395,1.021-.4,1.414-.012l1.893,1.861c.776.75,2.001.746,2.781-.018l4.425-4.344c.393-.388,1.024-.381,1.414.013Zm5.786,2.902c0,6.617-5.383,12-12,12S0,18.617,0,12,5.383,0,12,0s12,5.383,12,12Zm-2,0c0-5.514-4.486-10-10-10S2,6.486,2,12s4.486,10,10,10,10-4.486,10-10Z"/></svg>
                            </button>
                       </div>`
                    }
                },
            ],
            fnCreatedRow: function(nRow, aData, iDataIndex) {
                $(nRow).attr('data-noti-id', aData.id);
                $(nRow).attr('data-unread', aData.pending_approval == true);
            },
            ajax: {
                data: function() {
                    var info = $('#data-table').DataTable().page.info();
                    var url = "{{ route('approval.get_data') }}"

                    url = `${url}?page=${ info.page + 1 }&status=${ TABLE_FILTER['status'] }`
                    $('#data-table').DataTable().ajax.url(url);
                },
            },
        });

        // Filter status
        $('select[name="filter_status"]').on('change', function() {
            let status = $(this).val()

            TABLE_FILTER['status'] = status

            dt.draw()
        })

        // Approve action
        $('body').on('click', '.approve-btns', function() {
            let id = $(this).data('id')
            let url = '{{ config('app.url') }}'
            url = `${url}/approval/approve/${id}`

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: url,
                type: 'GET',
                success: function(res) {
                    $(`.reject-btns[data-id="${id}"]`).remove()
                    $(`.approve-btns[data-id="${id}"]`).remove()
                    $(`tr[data-noti-id="${id}"]`).attr('data-unread', false)
                    $(`tr[data-noti-id="${id}"] td:nth-of-type(5)`).text('Approved')
                },
            });
        })

        // Reject action
        $('body').on('click', '.reject-btns', function() {
            let id = $(this).data('id')
            let url = '{{ config('app.url') }}'
            url = `${url}/approval/reject/${id}`

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: url,
                type: 'GET',
                success: function(res) {
                    $(`.reject-btns[data-id="${id}"]`).remove()
                    $(`.approve-btns[data-id="${id}"]`).remove()
                    $(`tr[data-noti-id="${id}"]`).attr('data-unread', false)
                    $(`tr[data-noti-id="${id}"] td:nth-of-type(5)`).text('Rejected')
                },
            });
        })
    </script>
@endpush
