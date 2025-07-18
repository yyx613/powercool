<div id="list-production-request-container">
    <!-- Filters -->
    <div class="flex justify-end">
        {{-- <div class="flex items-center gap-x-4 max-w-sm w-full mb-4">
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
        </div> --}}

        <div class="flex items-center justify-end mb-4">
            <div class="rounded-md overflow-hidden flex" id="switch-view-btn">
                <button type="button"
                    class="text-sm py-1.5 px-2 font-medium bg-emerald-200 cursor-auto">{{ __('Normal') }}</button>
                <button type="button" class="text-sm py-1.5 px-2 font-medium bg-slate-200"
                    id="view-sale-btn">{{ __('Sale') }}</button>
            </div>
        </div>
    </div>

    <!-- Table -->
    <table id="data-table" class="text-sm rounded-lg overflow-hidden" style="width: 100%;">
        <thead>
            <tr>
                <th>{{ __('No.') }}</th>
                <th>{{ __('Date') }}</th>
                <th>{{ __('Total Request Quantity') }}</th>
                <th>{{ __('Balance Quantity') }}</th>
                <th>{{ __('Fulfilled Quantity') }}</th>
                <th>{{ __('Requested By') }}</th>
                <th>{{ __('Status') }}</th>
                <th>{{ __('Remark') }}</th>
                <th></th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

@push('scripts')
    <script>
        INIT_LOAD = true;
        DEFAULT_PAGE = @json($default_page ?? null);

        var columns = [{
                data: 'no'
            },
            {
                data: 'date'
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
                data: 'requested_by'
            },
            {
                data: 'status'
            },
            {
                data: 'remark'
            },
            {
                data: 'action'
            },
        ]
        var columnDefs = [{
                "width": "0%",
                "targets": 0,
                orderable: false,
                render: function(data, type, row) {
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
                    switch (data) {
                        case 1:
                            return "{{ __('In Progress') }}"
                        case 2:
                            return "{{ __('Completed') }}"
                    }
                }
            },
            {
                "width": "10%",
                "targets": 7,
                orderable: false,
                render: function(data, type, row) {
                    return data
                },
            },
            {
                "width": "5%",
                "targets": 8,
                orderable: false,
                render: function(data, type, row) {
                    return `<div class="flex items-center justify-end gap-x-2 px-2">
                            ${
                                row.qty_collected == row.qty_to_collect && row.status == 1 ? `
                                                                    <a href="{{ config('app.url') }}/production-request/complete/${row.id}" class="rounded-full p-2 bg-green-200 inline-block" title="{!! __('Complete') !!}">
                                                                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Capa_1" x="0px" y="0px" viewBox="0 0 507.506 507.506" style="enable-background:new 0 0 507.506 507.506;" xml:space="preserve" width="512" height="512">
                                                                            <path d="M163.865,436.934c-14.406,0.006-28.222-5.72-38.4-15.915L9.369,304.966c-12.492-12.496-12.492-32.752,0-45.248l0,0   c12.496-12.492,32.752-12.492,45.248,0l109.248,109.248L452.889,79.942c12.496-12.492,32.752-12.492,45.248,0l0,0   c12.492,12.496,12.492,32.752,0,45.248L202.265,421.019C192.087,431.214,178.271,436.94,163.865,436.934z"/>
                                                                        </svg>
                                                                    </a>
                                                                    ` : '' 
                            }
                             <a href="{{ config('app.url') }}/production-request/view/${row.id}" class="rounded-full p-2 bg-blue-200 inline-block" title="{!! __('View') !!}">
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512" height="512"><path d="M23.271,9.419C21.72,6.893,18.192,2.655,12,2.655S2.28,6.893.729,9.419a4.908,4.908,0,0,0,0,5.162C2.28,17.107,5.808,21.345,12,21.345s9.72-4.238,11.271-6.764A4.908,4.908,0,0,0,23.271,9.419Zm-1.705,4.115C20.234,15.7,17.219,19.345,12,19.345S3.766,15.7,2.434,13.534a2.918,2.918,0,0,1,0-3.068C3.766,8.3,6.781,4.655,12,4.655s8.234,3.641,9.566,5.811A2.918,2.918,0,0,1,21.566,13.534Z"/><path d="M12,7a5,5,0,1,0,5,5A5.006,5.006,0,0,0,12,7Zm0,8a3,3,0,1,1,3-3A3,3,0,0,1,12,15Z"/></svg>
                            </a>
                        </div>`
                }
            },
        ]

        // Datatable
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
                    var url = "{{ route('production_request.get_data') }}"

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
    </script>
@endpush
