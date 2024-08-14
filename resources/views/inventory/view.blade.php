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
        <x-app.page-title>View {{ $is_product ? 'Product' : 'Raw Material' }}</x-app.page-title>
    </div>
    @include('components.app.alert.parent')
    <!-- Summary -->
    <div class="mb-6 flex gap-x-4">
        @php
            $stocks = [
                [
                    'label' => 'Total Stock',
                    'value' => $total_stock_count,
                ],
                [
                    'label' => 'Reserved Stock',
                    'value' => $reserved_stock_count,
                ],
                [
                    'label' => 'Available Stock',
                    'value' => $total_stock_count - $reserved_stock_count,
                ],
            ];
        @endphp
        @foreach ($stocks as $stock)
            <div class="py-3 px-6 flex-1 flex justify-between items-center bg-slate-300 rounded-lg">
                <span class="text-md font-black">{{ $stock['label'] }}</span>
                <span class="text-2xl font-black">{{ $stock['value'] }}</span>
            </div>
        @endforeach
    </div>
    <!-- Product -->
    <div class="mb-6 flex items-center gap-x-4">
        <div class="h-12 w-12 rounded overflow-hidden">
            <img src="{{ $prod->image->url }}" alt="{{ $prod->model_name }}" class="w-full h-full object-contain">
        </div>
        <div>
            <h1 class="text-lg font-semibold">{{ $prod->model_name }}</h1>
            <span class="text-xs font-semibold text-slate-400 leading-none">{{ $prod->sku }}</span>
        </div>
    </div>
    <div>
        <!-- Filters -->
        <div class="flex max-w-xs w-full mb-4">
            <div class="flex-1">
                <x-app.input.input name="filter_search" id="filter_search" class="flex items-center" placeholder="Search">
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
                    <th>Serial Number</th>
                    <th>Location</th>
                    <th>Assigned Order ID</th>
                    <th></th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
@endsection

@push('scripts')
    <script>
        PRODUCT = @json($prod ?? null);
            
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
                { data: 'sku' },
                { data: 'location' },
                { data: 'order_id' },
                { data: 'action' },
            ],
            columnDefs: [
                { 
                    "width": "10%",
                    "targets": 0,
                    render: function(data, type, row) {
                        return data
                    }
                },
                { 
                    "width": "10%",
                    "targets": 1,
                    render: function(data, type, row) {
                        return `<span class="capitalize">${data}</span>`
                    }
                },
                { 
                    "width": "10%",
                    "targets": 2,
                    orderable: false,
                    render: function(data, type, row) {
                        if (data != null) {
                            if (data.type == 1) return `<a href="{{ config('app.url') }}/quotation/edit/${data.id}" class="text-blue-700">${data.sku}</a>`
                            else return `<a href="{{ config('app.url') }}/sale-order/edit/${data.id}" class="text-blue-700">${data.sku}</a>`
                        }
                        return data
                    }
                },
                { 
                    "width": "5%",
                    "targets": 3,
                    orderable: false,
                    render: function (data, type, row) {
                       return  `<div class="flex items-center justify-end gap-x-2 px-2">
                            <button class="rounded-full p-2 bg-purple-200 flex items-center gap-x-2 stock-in-btns" data-id="${row.id}">
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M21,12h-3c-1.103,0-2,.897-2,2s-.897,2-2,2h-4c-1.103,0-2-.897-2-2s-.897-2-2-2H3c-1.654,0-3,1.346-3,3v4c0,2.757,2.243,5,5,5h14c2.757,0,5-2.243,5-5v-4c0-1.654-1.346-3-3-3Zm1,7c0,1.654-1.346,3-3,3H5c-1.654,0-3-1.346-3-3v-4c0-.552,.448-1,1-1l3-.002v.002c0,2.206,1.794,4,4,4h4c2.206,0,4-1.794,4-4h3c.552,0,1,.448,1,1v4ZM7.293,7.121c-.391-.391-.391-1.023,0-1.414s1.023-.391,1.414,0l2.293,2.293V1c0-.553,.447-1,1-1s1,.447,1,1v7l2.293-2.293c.391-.391,1.023-.391,1.414,0s.391,1.023,0,1.414l-3.293,3.293c-.387,.387-.896,.582-1.405,.584l-.009,.002-.009-.002c-.509-.002-1.018-.197-1.405-.584l-3.293-3.293Z"/></svg>
                                <span class="text-xs font-medium">Stock In</span>
                            </button>
                            <button class="rounded-full p-2 bg-orange-200 flex items-center gap-x-2 stock-out-btns" data-id="${row.id}">
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M21,12h-3c-1.103,0-2,.897-2,2s-.897,2-2,2h-4c-1.103,0-2-.897-2-2s-.897-2-2-2H3c-1.654,0-3,1.346-3,3v4c0,2.757,2.243,5,5,5h14c2.757,0,5-2.243,5-5v-4c0-1.654-1.346-3-3-3Zm1,7c0,1.654-1.346,3-3,3H5c-1.654,0-3-1.346-3-3v-4c0-.552,.448-1,1-1l3-.002v.002c0,2.206,1.794,4,4,4h4c2.206,0,4-1.794,4-4h3c.552,0,1,.448,1,1v4ZM7.293,5.293c-.391-.391-.391-1.023,0-1.414L10.586,.586C10.972,.2,11.479,.006,11.986,.003l.014-.003,.014,.003c.508,.003,1.014,.197,1.4,.583l3.293,3.293c.391,.391,.391,1.023,0,1.414-.195,.195-.451,.293-.707,.293s-.512-.098-.707-.293l-2.293-2.293v7c0,.553-.447,1-1,1s-1-.447-1-1V3l-2.293,2.293c-.391,.391-1.023,.391-1.414,0Z"/></svg>
                                <span class="text-xs font-medium">Stock Out</span>
                            </button>
                       </div>`
                    }
                },
            ],
            ajax: {
                data: function(){
                    var info = $('#data-table').DataTable().page.info();
                    var url = "{{ route('product.view_get_data') }}"
                    
                    url = `${url}?page=${ info.page + 1 }&product_id=${ PRODUCT.id }`
                    $('#data-table').DataTable().ajax.url(url);
                },
            },
        });
        $('#filter_search').on('keyup', function() {
            dt.search($(this).val()).draw()
        })
    </script>
@endpush