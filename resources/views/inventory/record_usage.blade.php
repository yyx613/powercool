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
    <div class="mb-6">
        <x-app.page-title url="{{ route('production_material.index') }}">
            {{ __('Record Usage') }}
        </x-app.page-title>
    </div>
    @include('components.app.alert.parent')
    <form action="{{ route('production_material.record_usage_submit', ['frm' => $frm->id]) }}" method="POST"
        enctype="multipart/form-data" id="form">
        @csrf
        <div class="bg-white p-4 border rounded-md">
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-8 w-full items-start">
                <div class="flex flex-col">
                    <x-app.input.label id="qty" class="mb-1">{{ __('Quantity') }} <span
                            class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="qty" id="qty" value="{{ old('qty') }}" class="int-input" />
                    <x-input-error :messages="$errors->get('qty')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="prodution_id" class="mb-1">{{ __('Production ID') }}</x-app.input.label>
                    <x-app.input.select2 name="prodution_id">
                        <option value="">{{ __('Select a production') }}</option>
                        @foreach ($productions as $production)
                            <option value="{{ $production->id }}">{{ $production->sku }}</option>
                        @endforeach
                    </x-app.input.select2>

                    <x-input-error :messages="$errors->get('prodution_id')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="uom" class="mb-1">{{ __('UOM') }}</x-app.input.label>
                    <x-app.input.select2 name="uom">
                        <option value="">{{ __('Select a UOM') }}</option>
                        @foreach ($uoms as $uom)
                            <option value="{{ $uom->id }}">{{ $uom->name }}</option>
                        @endforeach
                    </x-app.input.select2>
                    <x-input-error :messages="$errors->get('uom')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="date" class="mb-1">{{ __('Date') }}</x-app.input.label>
                    <x-app.input.input name="date" id="date" value="{{ $date }}" disabled />
                    <x-input-error :messages="$errors->get('date')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="by" class="mb-1">{{ __('Done By') }}</x-app.input.label>
                    <x-app.input.input name="by" id="by" value="{{ $by }}" disabled />
                    <x-input-error :messages="$errors->get('by')" class="mt-1" />
                </div>
                <div class="flex flex-col col-span-3">
                    <x-app.input.label id="remark" class="mb-1">{{ __('Remark') }}</x-app.input.label>
                    <x-app.input.textarea name="remark" id="remark" />
                    <x-input-error :messages="$errors->get('remark')" class="mt-1" />
                </div>
            </div>
            <div class="mt-8 flex justify-end gap-x-4">
                <x-app.button.submit id="submit-btn">{{ __('Save and Update') }}</x-app.button.submit>
            </div>
        </div>
    </form>
    {{-- History --}}
    <div class="mb-6">
        <x-app.page-title>
            {{ __('Record History') }}
        </x-app.page-title>
    </div>
    <div>
        <!-- Filters -->
        {{-- <div class="flex max-w-xs w-full mb-4">
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
        <!-- Table -->
        <table id="data-table" class="text-sm rounded-lg overflow-hidden" style="width: 100%;">
            <thead>
                <tr>
                    <th>{{ __('Qty') }}</th>
                    <th>{{ __('Production ID') }}</th>
                    <th>{{ __('UOM') }}</th>
                    <th>{{ __('Remark') }}</th>
                    <th>{{ __('By') }}</th>
                    <th>{{ __('Date') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
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
                    data: 'qty'
                },
                {
                    data: 'production'
                },
                {
                    data: 'uom'
                },
                {
                    data: 'remark'
                },
                {
                    data: 'done_by'
                },
                {
                    data: 'date'
                },
            ],
            columnDefs: [{
                    "width": "10%",
                    "targets": 0,
                    orderable: false,
                    render: function(data, type, row) {
                        return data
                    }
                }, {
                    "width": "30%",
                    "targets": 1,
                    orderable: false,
                    render: function(data, type, row) {
                        return data
                    }
                }, {
                    "width": "10%",
                    "targets": 2,
                    orderable: false,
                    render: function(data, type, row) {
                        return data
                    }
                }, {
                    "width": "20%",
                    "targets": 3,
                    orderable: false,
                    render: function(data, type, row) {
                        return data
                    }
                }, {
                    "width": "15%",
                    "targets": 4,
                    orderable: false,
                    render: function(data, type, row) {
                        return data
                    }
                }, {
                    "width": "20%",
                    "targets": 5,
                    orderable: false,
                    render: function(data, type, row) {
                        return data
                    }
                },
            ],
            ajax: {
                data: function() {
                    var info = $('#data-table').DataTable().page.info();
                    var url = "{{ route('production_material.record_usage_get_data') }}"

                    url = `${url}?page=${ info.page + 1 }`
                    $('#data-table').DataTable().ajax.url(url);
                },
            },
        });
        $('#filter_search').on('keyup', function() {
            dt.search($(this).val()).draw()
        })
    </script>
@endpush
