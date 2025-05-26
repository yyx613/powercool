@extends('layouts.app')

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
            url="{{ route('production_request.index') }}">{{ __('View Production Request') }}</x-app.page-title>
    </div>
    @include('components.app.alert.parent')
    <!-- Table -->
    <table id="data-table" class="text-sm rounded-lg overflow-hidden" style="width: 100%;">
        <thead>
            <tr>
                <th>{{ __('Product') }}</th>
                <th>{{ __('Production ID') }}</th>
                <th>{{ __('Status') }}</th>
                <th></th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>

    <x-app.modal.production-request-complete-modal />
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
                    data: 'product_name'
                },
                {
                    data: 'production_sku'
                },
                {
                    data: 'status'
                },
                {
                    data: 'action'
                },
            ],
            columnDefs: [{
                    "width": "30%",
                    "targets": 0,
                    'orderable': false,
                    render: function(data, type, row) {
                        return data
                    }
                }, {
                    "width": "30%",
                    "targets": 1,
                    'orderable': false,
                    render: function(data, type, row) {
                        return data
                    }
                },

                {
                    "width": "10%",
                    "targets": 2,
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
                    "targets": 3,
                    orderable: false,
                    render: function(data, type, row) {
                        return `<div class="flex items-center justify-end gap-x-2 px-2">
                            ${
                                row.status == 1 ? `
                                                    <button type="button" data-link="{{ config('app.url') }}/production-request/material/complete/${row.id}" class="complete-btns rounded-full p-2 bg-green-200 inline-block" title="{!! __('Complete') !!}">
                                                       <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Capa_1" x="0px" y="0px" viewBox="0 0 507.506 507.506" style="enable-background:new 0 0 507.506 507.506;" xml:space="preserve" width="512" height="512">
                                                           <path d="M163.865,436.934c-14.406,0.006-28.222-5.72-38.4-15.915L9.369,304.966c-12.492-12.496-12.492-32.752,0-45.248l0,0   c12.496-12.492,32.752-12.492,45.248,0l109.248,109.248L452.889,79.942c12.496-12.492,32.752-12.492,45.248,0l0,0   c12.492,12.496,12.492,32.752,0,45.248L202.265,421.019C192.087,431.214,178.271,436.94,163.865,436.934z"/>
                                                       </svg>
                                                   </button>
                                                    ` : '' 
                            }
                            ${
                               row.status == 2 ? `
                                                   <a href="{{ config('app.url') }}/production-request/material/incomplete/${row.id}" class="rounded-full p-2 bg-red-200 inline-block" title="{!! __('Incomplete') !!}">
                                                       <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Capa_1" x="0px" y="0px" viewBox="0 0 512.021 512.021" style="enable-background:new 0 0 512.021 512.021;" xml:space="preserve" width="512" height="512">
                                                           <path d="M301.258,256.01L502.645,54.645c12.501-12.501,12.501-32.769,0-45.269c-12.501-12.501-32.769-12.501-45.269,0l0,0   L256.01,210.762L54.645,9.376c-12.501-12.501-32.769-12.501-45.269,0s-12.501,32.769,0,45.269L210.762,256.01L9.376,457.376   c-12.501,12.501-12.501,32.769,0,45.269s32.769,12.501,45.269,0L256.01,301.258l201.365,201.387   c12.501,12.501,32.769,12.501,45.269,0c12.501-12.501,12.501-32.769,0-45.269L301.258,256.01z"/>
                                                       </svg>
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
                    var url = "{{ route('production_request.view_get_data') }}"

                    url = `${url}?page=${ info.page + 1 }`
                    $('#data-table').DataTable().ajax.url(url);
                },
            },
        });
        $('body').on('click', '.complete-btns', function() {
            let link = $(this).data('link')

            $('#production-request-complete-modal #yes-btn').attr('href', link)
            $('#production-request-complete-modal').addClass('show-modal')
        })
        $('#production-request-complete-modal #yes-btn').on('click', function() {
            let link = $(this).attr('href')
            let productionId = $('#production-request-complete-modal input[name="production_id"]').val()
            link = `${link}?production_id=${productionId}`
            $(this).attr('href', link)
        })
    </script>
@endpush
