@inject('carbon', 'Carbon\Carbon')

@extends('layouts.app')
@section('title', 'Supplier')

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
        <x-app.page-title url="{{ route('supplier.index') }}">{{ $supplier->registered_name }}'s {{ __('GRN History') }}</x-app.page-title>
    </div>
    @include('components.app.alert.parent')
    <div>
        @if (count($formatted_grns) > 0)
            @foreach ($formatted_grns as $product_id => $grns)
                <div class="bg-white p-2 rounded-md mb-4 border">
                    <div class="mb-2 flex items-center justify-between">
                        <div>
                            <h6 class="text-lg font-semibold">{{ $formatted_products[$product_id]->model_desc }}</h6>
                            <span class="text-xs font-semibold text-slate-500">{{ __('SKU') }}: {{ $formatted_products[$product_id]->sku }}</span>
                        </div>
                        <div>
                            <span class="text-lg">x{{ count($grns) }} <span class="text-sm text-slate-500 font-medium">{{ __('GRN Involved') }}</span></span>
                        </div>
                    </div>
                    @foreach ($grns as $grn)
                        <div class="py-2 border-t transition-all duration-300 hover:bg-slate-50 hover:px-2">
                            <p class="text-sm font-semibold mb-2">{{ __('GRN SKU') }}: {{ $grn->sku }}</p>
                            <p class="text-sm">{{ __('UOM') }}: {{ $grn->uom }}</p>
                            <p class="text-sm">{{ __('Quantity') }}: {{ $grn->qty }}</p>
                            <p class="text-sm">{{ __('Unit Price') }}: RM{{ number_format($grn->unit_price, 2) }}</p>
                            <p class="text-sm">{{ __('Total Price') }}: RM{{ number_format($grn->total_price, 2) }}</p>
                            <p class="text-sm mt-2 text-slate-600">{{ __('Created At') }}: {{ $carbon::parse($grn->created_at)->format('d M Y, h:i A') }}</p>
                        </div>
                    @endforeach
                </div>
            @endforeach
        @else
            <div class="my-12">
                @include('components.app.no-data')
            </div>
        @endif
    </div>
@endsection
