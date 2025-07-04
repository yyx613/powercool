<div id="list-sale-production-request-container" class="hidden">
    <!-- Filters -->
    <div class="flex justify-between">
        <div class="flex items-center gap-x-4 max-w-sm w-full mb-4">
            <div class="flex-1">
                <x-app.input.input name="sale-filter_search" id="sale-filter_search" class="flex items-center"
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

        <div class="flex items-center justify-end mb-4">
            <div class="rounded-md overflow-hidden flex" id="switch-view-btn">
                <button type="button" class="text-sm py-1.5 px-2 font-medium bg-slate-200"
                    id="view-normal-btn">{{ __('Normal') }}</button>
                <button type="button"
                    class="text-sm py-1.5 px-2 font-medium bg-emerald-200 cursor-auto">{{ __('Sale') }}</button>
            </div>
        </div>
    </div>

    <!-- Table -->
    <table id="data-table-sale" class="text-sm rounded-lg overflow-hidden" style="width: 100%;">
        <thead>
            <tr>
                <th>{{ __('No.') }}</th>
                <th>{{ __('Date') }}</th>
                <th>{{ __('Sale Order No') }}</th>
                <th>{{ __('Product') }}</th>
                <th>{{ __('Production') }}</th>
                <th>{{ __('Remark') }}</th>
                <th>{{ __('Status') }}</th>
                <th></th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

@push('scripts')
    <script>
        var columns = [{
                data: 'no'
            },
            {
                data: 'date'
            },
            {
                data: 'so_no'
            },
            {
                data: 'product'
            },
            {
                data: 'production'
            },
            {
                data: 'remark',
            },
            {
                data: 'status'
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
                orderable: false,
                render: function(data, type, row) {
                    return data
                }
            },
            {
                "width": "10%",
                "targets": 2,
                orderable: false,
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
                orderable: false,
                render: function(data, type, row) {
                    return data
                }
            },
            {
                "width": "20%",
                "targets": 5,
                orderable: false,
                render: function(data, type, row) {
                    return data
                }
            },
            {
                "width": "5%",
                "targets": 6,
                orderable: false,
                render: function(data, type, row) {
                    return data
                }
            },
            {
                "width": "5%",
                "targets": 7,
                orderable: false,
                render: function(data, type, row) {
                    return `<div class="flex items-center justify-end gap-x-2 px-2">
                                ${
                                    row.has_material_use ? '' : `<button class="rounded-full p-2 bg-slate-200 inline-block to-bom-btns" title="{!! __('To B.O.M Material Use') !!}" data-id="${row.id}">
                                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M8,5c0-2.206-1.794-4-4-4S0,2.794,0,5c0,1.86,1.277,3.428,3,3.873v7.253c-1.723,.445-3,2.013-3,3.873,0,2.206,1.794,4,4,4s4-1.794,4-4c0-1.86-1.277-3.428-3-3.873v-7.253c1.723-.445,3-2.013,3-3.873Zm-6,0c0-1.103,.897-2,2-2s2,.897,2,2-.897,2-2,2-2-.897-2-2Zm4,15c0,1.103-.897,2-2,2s-2-.897-2-2,.897-2,2-2,2,.897,2,2Zm15-3.873v-7.127c0-2.757-2.243-5-5-5h-3.471l2.196-2.311c.381-.4,.364-1.034-.036-1.414-.399-.379-1.033-.364-1.413,.036l-2.396,2.522c-1.17,1.169-1.17,3.073-.03,4.212l2.415,2.631c.196,.215,.466,.324,.736,.324,.242,0,.484-.087,.676-.263,.407-.374,.435-1.006,.061-1.413l-2.133-2.324h3.397c1.654,0,3,1.346,3,3v7.127c-1.724,.445-3,2.013-3,3.873,0,2.206,1.794,4,4,4s4-1.794,4-4c0-1.86-1.276-3.428-3-3.873Zm-1,5.873c-1.103,0-2-.897-2-2s.897-2,2-2,2,.897,2,2-.897,2-2,2Z"/></svg>
                                            </button>`
                                }
                                ${
                                    row.has_material_use ? `
                                            <a href="{{ config('app.url') }}/production-request/to-production/${row.sale_id}/${row.product_id}" class="rounded-full p-2 bg-purple-200 inline-block" title="{!! __('To Production') !!}">
                                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                                    id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24">
                                                    <path
                                                        d="m22.97,6.251c-.637-.354-1.415-.331-2.1.101l-4.87,3.649v-2.001c0-.727-.395-1.397-1.03-1.749-.637-.354-1.416-.331-2.1.101l-4.87,3.649V2c.553,0,1-.448,1-1s-.447-1-1-1H1C.447,0,0,.448,0,1s.447,1,1,1v17c0,2.757,2.243,5,5,5h13c2.757,0,5-2.243,5-5v-11c0-.727-.395-1.397-1.03-1.749Zm-.97,12.749c0,1.654-1.346,3-3,3H6c-1.654,0-3-1.346-3-3V2h3v9.991c0,.007,0,.014,0,.02v5.989c0,.552.447,1,1,1s1-.448,1-1v-5.5l6-4.5v4c0,.379.214.725.553.895s.743.134,1.047-.094l6.4-4.8v11Zm-8-2v1c0,.552-.448,1-1,1h-1c-.552,0-1-.448-1-1v-1c0-.552.448-1,1-1h1c.552,0,1,.448,1,1Zm2,1v-1c0-.552.448-1,1-1h1c.552,0,1,.448,1,1v1c0,.552-.448,1-1,1h-1c-.552,0-1-.448-1-1Z" />
                                                </svg>
                                            </a>` : ''
                                }
                            </div>`
                }
            },
        ]

        // Datatable
        var dtSale = new DataTable('#data-table-sale', {
            dom: 'rtip',
            pagingType: 'numbers',
            pageLength: 10,
            processing: true,
            serverSide: true,
            order: [],
            columns: columns,
            columnDefs: columnDefs,
            ajax: {
                data: function() {
                    var info = $('#data-table-sale').DataTable().page.info();
                    var url = "{{ route('production_request.get_data_sale_production_request') }}"

                    url = `${url}?page=${ info.page + 1 }`
                    $('#data-table-sale').DataTable().ajax.url(url);
                },
            },
        });
        $('#sale-filter_search').on('keyup', function() {
            dtSale.search($(this).val()).draw()
        })
        $('body').on('click', '.to-bom-btns', function() {
            let id = $(this).data('id')

            $('#to-material-use-modal #yes-btn').data('sale-production-request-id', id)
            $('#to-material-use-modal').addClass('show-modal')
        })
    </script>
@endpush
