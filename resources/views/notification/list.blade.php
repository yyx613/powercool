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
        <x-app.page-title>Notification</x-app.page-title>
    </div>
    @include('components.app.alert.parent')
    <div>
        <!-- Filters -->
        <div class="flex justify-between items-center mb-4">
            <div class="flex gap-x-2 w-full max-w-xs">
                <!-- <div class="flex-1 flex">
                    <x-app.input.select name='filter_type' id='filter_type' class="w-full capitalize">
                        <option value="">Select a type</option>
                        @foreach ($types as $key => $type)
                            <option value="{{ $key }}">{{ $type }}</option>
                        @endforeach
                    </x-app.input.select>
                </div> -->
                <div class="flex-1 flex">
                    <x-app.input.select name='filter_status' id='filter_status' class="w-full capitalize">
                        <option value="">Select a status</option>
                        @foreach ($statuses as $key => $status)
                            <option value="{{ $key }}">{{ $status }}</option>
                        @endforeach
                    </x-app.input.select>
                </div>
            </div>
        </div>

        <!-- Table -->
        <table id="data-table" class="text-sm rounded-lg overflow-hidden" style="width: 100%;">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Type</th>
                    <th>Description</th>
                    <th>Date</th>
                    <th></th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
@endsection

@push('scripts')
    <script>
        TABLE_FILTER = {
            'type': '',
            'status': '',
        }

        // Datatable
        var dt = new DataTable('#data-table', {
            dom: 'rtip',
            pagingType: 'numbers',
            pageLength: 10,
            processing: true,
            serverSide: true,
            order: [],
            columns: [
                { data: 'no' },
                { data: 'type' },
                { data: 'desc' },
                { data: 'date' },
                { data: 'action' },
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
                        return data
                    }
                },
                { 
                    "width": "10%",
                    "targets": 3,
                    'orderable': false,
                    render: function(data, type, row) {
                        return `${ moment(data).format('DD MMM, YYYY')}<br>${moment(data.date).format('HH:mm') }`
                    }
                },
                { 
                    "width": "5%",
                    "targets": 4,
                    "orderable": false,
                    render: function (data, type, row) {
                       return  `<div class="flex items-center justify-end gap-x-2 px-2">
                            <button class="rounded-full p-2 bg-green-200 inline-block ${row.read_at != null ? 'hidden' : ''} read-btns" data-id="${row.id}" title="Mark as read">
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="m18.214,9.098c.387.394.381,1.027-.014,1.414l-4.426,4.345c-.783.768-1.791,1.151-2.8,1.151-.998,0-1.996-.376-2.776-1.129l-1.899-1.867c-.394-.387-.399-1.02-.012-1.414.386-.395,1.021-.4,1.414-.012l1.893,1.861c.776.75,2.001.746,2.781-.018l4.425-4.344c.393-.388,1.024-.381,1.414.013Zm5.786,2.902c0,6.617-5.383,12-12,12S0,18.617,0,12,5.383,0,12,0s12,5.383,12,12Zm-2,0c0-5.514-4.486-10-10-10S2,6.486,2,12s4.486,10,10,10,10-4.486,10-10Z"/></svg>
                            </button>
                       </div>`
                    }
                },
            ],
            fnCreatedRow: function( nRow, aData, iDataIndex ) {
                $(nRow).attr('data-noti-id', aData.id);
                $(nRow).attr('data-unread', aData.read_at == null);
            },
            ajax: {
                data: function(){
                    var info = $('#data-table').DataTable().page.info();
                    var url = "{{ route('notification.get_data') }}"
                    
                    url = `${url}?page=${ info.page + 1 }type=${ TABLE_FILTER['type'] }&status=${ TABLE_FILTER['status'] }`
                    $('#data-table').DataTable().ajax.url(url);
                },
            },
        });

        // Filter type
        $('select[name="filter_type"]').on('change', function() {
            let type = $(this).val()

            TABLE_FILTER['type'] = type

            updateTableData()
        })       

        // Filter status
        $('select[name="filter_status"]').on('change', function() {
            let status = $(this).val()

            TABLE_FILTER['status'] = status

            dt.draw()
        })

        // Approve subject action
        $('body').on('click', '.approve-btns', function() {
            let subjectId = $(this).data('subject-id')
            let subjectType = $(this).data('subject-type')
            let url = '{{ config("app.url") }}'
            url = `${url}/${subjectType.includes('warehouse') ? 'warehouse' : subjectType}/${subjectType == 'warehouse-check-in' ? 'approve-check-in' : (subjectType == 'warehouse-check-out' ? 'approve-check-out' : 'approve')}/${subjectId}`

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: url,
                type: 'GET',
                success: function(res) {
                    $(`.approve-btns[data-subject-id="${subjectId}"][data-subject-type="${subjectType}"]`).remove()
                },
            });
        })

        // Read notification
        $('body').on('click', '.read-btns', function() {
            let notiId = $(this).data('id')
            let url = '{{ config("app.url") }}/notification/read'
            url = `${url}/${notiId}`

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: url,
                type: 'GET',
                success: function(res) {
                    $(`.read-btns[data-id="${notiId}"]`).remove()
                    $(`tr[data-noti-id="${notiId}"]`).attr('data-unread', false)
                },
            });
        })
    </script>
@endpush