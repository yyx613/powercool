@extends('layouts.app')
@section('title', 'Raw Material Request')

@vite(['resources/css/jquery.dataTables.min.css'])

@push('styles')
    <style>
        #data-table,
        #cost-table {
            border: solid 1px rgb(209 213 219);
        }

        #data-table thead th,
        #data-table tbody tr td,
        #cost-table thead th,
        #cost-table tbody tr td {
            border-bottom: solid 1px rgb(209 213 219);
        }

        #data-table tbody tr:last-of-type td,
        #cost-table tbody tr:last-of-type td {
            border-bottom: none;
        }
    </style>
@endpush

@section('content')
    <div class="mb-6">
        <x-app.page-title
            url="{{ route('raw_material_request.index') }}">{{ __('View Raw Material Request') }}</x-app.page-title>
    </div>
    @include('components.app.alert.parent')
    <!-- Table -->
    <table id="data-table" class="text-sm rounded-lg overflow-hidden" style="width: 100%;">
        <thead>
            <tr>
                <th>{{ __('No.') }}</th>
                <th>{{ __('Spare Part/Raw Material') }}</th>
                <th>{{ __('Total Request Quantity') }}</th>
                <th>{{ __('Balance Quantity') }}</th>
                <th>{{ __('Fulfilled Quantity') }}</th>
                <th>{{ __('Status') }}</th>
                <th></th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>

    <x-app.modal.raw-material-transfer-modal />
@endsection

@push('scripts')
    <script>
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
                    data: 'product_name'
                },
                {
                    data: 'total_request_qty'
                },
                {
                    data: 'balance_qty'
                },
                {
                    data: 'fulfilled_qty'
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
                    "width": "20%",
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
                        return data
                    }
                },
                {
                    "width": "10%",
                    "targets": 4,
                    'orderable': false,
                    render: function(data, type, row) {
                        return data
                    }
                },
                {
                    "width": "10%",
                    "targets": 5,
                    orderable: false,
                    render: function(data, type, row) {
                        switch (data) {
                            case 1:
                                return "{{ __('In Progress') }}"
                            case 2:
                                return "{{ __('Completed') }}"
                        }
                    }
                },
                {
                    "width": "5%",
                    "targets": 6,
                    orderable: false,
                    render: function(data, type, row) {
                        if (row.parent_completed == true) {
                            return ''
                        }

                        return `<div class="flex items-center justify-end gap-x-2 px-2">
                            ${
                                row.is_sparepart && row.status == 1 ? 
                                `
                                                                <a href="{{ config('app.url') }}/raw-material-request/material/complete/${row.id}" class="rounded-full p-2 bg-green-200 inline-block" title="{!! __('Complete') !!}">
                                                                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Capa_1" x="0px" y="0px" viewBox="0 0 507.506 507.506" style="enable-background:new 0 0 507.506 507.506;" xml:space="preserve" width="512" height="512">
                                                                        <path d="M163.865,436.934c-14.406,0.006-28.222-5.72-38.4-15.915L9.369,304.966c-12.492-12.496-12.492-32.752,0-45.248l0,0   c12.496-12.492,32.752-12.492,45.248,0l109.248,109.248L452.889,79.942c12.496-12.492,32.752-12.492,45.248,0l0,0   c12.492,12.496,12.492,32.752,0,45.248L202.265,421.019C192.087,431.214,178.271,436.94,163.865,436.934z"/>
                                                                    </svg>
                                                                </a>
                                                            ` : !row.is_sparepart && row.status != 2 ?
                                `
                                                                                        <button type="button" data-rmrm-id="${row.id}" class="complete-btns rounded-full p-2 bg-green-200 inline-block" title="{!! __('Complete') !!}">
                                                                                           <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Capa_1" x="0px" y="0px" viewBox="0 0 507.506 507.506" style="enable-background:new 0 0 507.506 507.506;" xml:space="preserve" width="512" height="512">
                                                                                               <path d="M163.865,436.934c-14.406,0.006-28.222-5.72-38.4-15.915L9.369,304.966c-12.492-12.496-12.492-32.752,0-45.248l0,0   c12.496-12.492,32.752-12.492,45.248,0l109.248,109.248L452.889,79.942c12.496-12.492,32.752-12.492,45.248,0l0,0   c12.492,12.496,12.492,32.752,0,45.248L202.265,421.019C192.087,431.214,178.271,436.94,163.865,436.934z"/>
                                                                                           </svg>
                                                                                       </button>
                                                                                        ` : '' 
                            }
                            ${
                               row.is_sparepart && row.status == 2 ? `
                                                               <a href="{{ config('app.url') }}/raw-material-request/material/incomplete/${row.id}" class="rounded-full p-2 bg-red-200 inline-block" title="{!! __('Incomplete') !!}">
                                                                   <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Capa_1" x="0px" y="0px" viewBox="0 0 512.021 512.021" style="enable-background:new 0 0 512.021 512.021;" xml:space="preserve" width="512" height="512">
                                                                       <path d="M301.258,256.01L502.645,54.645c12.501-12.501,12.501-32.769,0-45.269c-12.501-12.501-32.769-12.501-45.269,0l0,0   L256.01,210.762L54.645,9.376c-12.501-12.501-32.769-12.501-45.269,0s-12.501,32.769,0,45.269L210.762,256.01L9.376,457.376   c-12.501,12.501-12.501,32.769,0,45.269s32.769,12.501,45.269,0L256.01,301.258l201.365,201.387   c12.501,12.501,32.769,12.501,45.269,0c12.501-12.501,12.501-32.769,0-45.269L301.258,256.01z"/>
                                                                   </svg>
                                                               </a>
                                                               ` : ''
                            }
                            ${
                               !row.is_sparepart ? `
                                                               <a href="{{ config('app.url') }}/raw-material-request/view/${row.id}/logs" class="rounded-full p-2 bg-purple-200 inline-block" title="{!! __('View Logs') !!}">
                                                                   <svg class="h-4 w-4" id="Layer_1" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" data-name="Layer 1"><path d="m8.722 18.368c0 .346-.28.627-.626.628l-1.468.003c-.347 0-.629-.28-.629-.627l-.005-4.744c0-.347.281-.628.628-.628s.627.28.627.626c.001 1.249.003 2.869.004 4.118.71-.001.592-.003.837-.004.348-.002.631.28.631.628zm4.757-3.368.008 2v.015c0 1.094-.902 1.985-2.012 1.985-1.086 0-1.967-.881-1.967-1.967l-.008-2.066c0-1.086.881-1.967 1.967-1.967 1.109 0 2.012.891 2.012 1.985zm-1.242 2.015-.008-2.015v-.015c0-.394-.342-.726-.75-.733-.402.007-.725.333-.724.736 0 .095.008 1.886.008 2.024 0 .403.322.729.724.736.408-.007.75-.338.75-.733zm5.62-1.013-.766.002c-.345.001-.625.281-.625.627 0 .349.284.631.633.627h.085c-.106.279-.378.485-.698.491-.402-.007-.725-.333-.724-.736 0-.138-.008-1.929-.008-2.024 0-.403.322-.729.724-.736.289.005.544.172.669.409.106.202.32.324.548.324.473 0 .776-.504.553-.921-.337-.632-1.009-1.064-1.782-1.064-1.086 0-1.967.881-1.967 1.967l.008 2.066c0 1.086.881 1.967 1.967 1.967 1.11 0 2.012-.891 2.012-1.985v-.389c-.003-.346-.284-.625-.63-.624zm4.143-5.516v8.515c0 2.757-2.243 5-5 5h-10c-2.757 0-5-2.243-5-5v-14.001c0-2.757 2.243-5 5-5h4.515c1.87 0 3.627.728 4.95 2.05l3.485 3.485c1.322 1.322 2.05 3.08 2.05 4.95zm-6.95-7.022c-.315-.315-.674-.564-1.05-.781v4.317c0 .551.449 1 1 1h4.317c-.217-.376-.466-.735-.781-1.05l-3.485-3.485zm4.95 7.021c0-.165-.032-.323-.047-.485h-4.953c-1.654 0-3-1.346-3-3v-4.953c-.162-.016-.32-.047-.485-.047h-4.515c-1.654 0-3 1.346-3 3v14c0 1.654 1.346 3 3 3h10c1.654 0 3-1.346 3-3z"/></svg>
                                                               </a>
                                                               ` : ''
                            }
                        </div>`
                    }
                },
            ],
            ajax: {
                data: function() {
                    var info = $('#data-table').DataTable().page.info();
                    var url = "{{ route('raw_material_request.view_get_data') }}"

                    url = `${url}?page=${ info.page + 1 }`
                    $('#data-table').DataTable().ajax.url(url);
                },
            },
        });
        $('body').on('click', '.complete-btns', function() {
            $('#raw-material-transfer-modal #title').text("{{ __('Complete by Quantity') }}")
            $('#raw-material-transfer-modal').addClass('show-modal')
        })
        $('#raw-material-transfer-modal #yes-btn').on('click', function() {
            let url = "{{ config('app.url') }}"
            url =
                `${url}/raw-material-request/material/complete/${$('.complete-btns').data('rmrm-id')}?qty=${ $('#raw-material-transfer-modal input').val() }`

            window.location.href = url
        })
    </script>
@endpush
