@extends('layouts.app')

@vite(['resources/css/jquery.dataTables.min.css'])

@push('styles')
    <style>
        #role-table {
            border: solid 1px rgb(209 213 219);
        }
        #role-table thead th,
        #role-table tbody tr td {
            border-bottom: solid 1px rgb(209 213 219);
        }
        #role-table tbody tr:last-of-type td {
            border-bottom: none;
        }
    </style>
@endpush

@section('content')
    <div class="mb-6">
        <x-app.page-title>Role Management</x-app.page-title>
    </div>
    @include('components.app.alert.parent')
    <div>
        <!-- Filters -->
        <div class="flex max-w-xs w-full mb-4">
            <div class="flex-1">
                <x-app.input.input name="filter_search" id="filter_search" class="flex items-center" placeholder="Search">
                    <div class="rounded-md border border-transparent p-1 ml-2">
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24"><path d="M23.707,22.293l-5.969-5.969a10.016,10.016,0,1,0-1.414,1.414l5.969,5.969a1,1,0,0,0,1.414-1.414ZM10,18a8,8,0,1,1,8-8A8.009,8.009,0,0,1,10,18Z"/></svg>
                    </div>
                </x-app.input.input>
            </div>
        </div>

        <!-- Table -->
        <table id="role-table" class="text-sm rounded-lg overflow-hidden" style="width: 100%;">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Role</th>
                    <th>No of Users</th>
                    <th></th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function(){
            updateTableData()
        })

        // Datatable
        var roleDataTable = new DataTable('#role-table', {
            bFilter: false,
            dom: 'rtip',
            pagingType: 'numbers',
            // ordering: false,
            pageLength: 10,
            columns: [
                { data: 'no' },
                { data: 'role' },
                { data: 'user_count_under_role' },
                { data: 'action' },
            ],
            columnDefs: [
                { "width": "5%", "targets": 0 },
                { "width": "15%", "targets": 1 },
                { "width": "15%", "targets": 2 },
                { "width": "5%", "targets": 3, "orderable": false },
            ]
        });

        // Get table data
        function updateTableData(keyword='') {
            setTimeout(() => {
                let url = '{{ route("role_management.get_data") }}'
                url = `${url}?keyword=${ keyword }`

                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: url,
                    type: 'GET',
                    success: function(res) {
                        roleDataTable.clear();

                        if (res.length > 0) {
                            for (let i = 0; i < res.length; i++) {
                                res[i].action = `
                                    <div class="flex items-center justify-end gap-x-2 px-2">
                                        <a href="{{ config('app.url') }}/role-management/edit/${res[i].id}" class="rounded-full p-2 bg-blue-200 inline-block">
                                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="m18.813,10c.309,0,.601-.143.79-.387s.255-.562.179-.861c-.311-1.217-.945-2.329-1.833-3.217l-3.485-3.485c-1.322-1.322-3.08-2.05-4.95-2.05h-4.515C2.243,0,0,2.243,0,5v14c0,2.757,2.243,5,5,5h3c.552,0,1-.448,1-1s-.448-1-1-1h-3c-1.654,0-3-1.346-3-3V5c0-1.654,1.346-3,3-3h4.515c.163,0,.325.008.485.023v4.977c0,1.654,1.346,3,3,3h5.813Zm-6.813-3V2.659c.379.218.732.488,1.05.806l3.485,3.485c.314.314.583.668.803,1.05h-4.338c-.551,0-1-.449-1-1Zm11.122,4.879c-1.134-1.134-3.11-1.134-4.243,0l-6.707,6.707c-.755.755-1.172,1.76-1.172,2.829v1.586c0,.552.448,1,1,1h1.586c1.069,0,2.073-.417,2.828-1.172l6.707-6.707c.567-.567.879-1.32.879-2.122s-.312-1.555-.878-2.121Zm-1.415,2.828l-6.708,6.707c-.377.378-.879.586-1.414.586h-.586v-.586c0-.534.208-1.036.586-1.414l6.708-6.707c.377-.378,1.036-.378,1.414,0,.189.188.293.439.293.707s-.104.518-.293.707Z"/></svg>
                                        </a>
                                    </div>
                                `
                                
                                roleDataTable.rows.add([res[i]]).draw();
                            }
                        } else {
                            roleDataTable.draw();
                        }
                    },
                });
            }, 300);
        }

        $('input[name="filter_search"]').on('keyup', function() {
            let keyword = $('input[name="filter_search"]').val()

            updateTableData(keyword)
        })
    </script>
@endpush