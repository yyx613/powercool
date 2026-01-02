@extends('layouts.app')
@section('title', 'Invoice')

@section('content')
    <div class="mb-6 flex justify-between items-start lg:items-center flex-col lg:flex-row">
        <x-app.page-title class="mb-4 lg:mb-0">{{ __('Swap Serial No') }}</x-app.page-title>
    </div>
    <div class="bg-white p-4 border rounded-md">
        @if (!in_array(\App\Models\Role::SALE, getUserRoleId(Auth::user())))
            <div class="mb-4 flex items-center gap-3 p-3 bg-yellow-50 border border-yellow-200 rounded-md">
                <svg class="h-5 w-5 text-yellow-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-sm text-yellow-800">{{ __('Serial number swapping is disabled for your role') }}</p>
            </div>
        @endif
        <form method="POST" action="{{ route('invoice.swap_serial_no_post', ['inv' => $invoice->id]) }}">
            @csrf
            @foreach ($products as $product)
                <h6 class="text-lg font-medium mb-3">{{ $product->model_name }} ({{ $product->sku }})</h6>
                @foreach ($inv_products as $inv_product)
                    @if ($inv_product->saleProduct->product_id == $product->id)
                        @foreach ($inv_product->children as $dopc)
                            <div class="mb-4">
                                <div class="mb-2 border-l-4 border-yellow-400 px-2 bg-yellow-100 inline-flex">
                                    <p class="text-sm font-semibold">{{ __('Swap for ') }}{{ $dopc->productChild->sku }}</p>
                                </div>
                                <div class="grid md:grid-cols-8 grid-cols-4 gap-1">
                                    @foreach ($product->children as $pc)
                                        @if (!in_array($pc->id, $inv_product_child_ids))
                                            <div class="flex items-center gap-2">
                                                <input type="radio" id="swap_dopc_{{ $dopc->id }}_pc_{{ $pc->id }}" name="swap_dopc_{{ $dopc->id }}" value="{{ $pc->id }}" {{ in_array(\App\Models\Role::SALE, getUserRoleId(Auth::user())) ? 'disabled' : '' }} />
                                                <label for="swap_dopc_{{ $dopc->id }}_pc_{{ $pc->id }}" class="text-sm {{ in_array(\App\Models\Role::SALE, getUserRoleId(Auth::user())) ? 'text-slate-400' : '' }}">{{ $pc->sku }}</label>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    @endif
                @endforeach
            @endforeach
            <div class="mt-6 flex justify-end">
                @if (in_array(\App\Models\Role::SALE, getUserRoleId(Auth::user())))
                    <button type="button" disabled class="px-4 py-2 bg-slate-300 text-slate-500 rounded cursor-not-allowed">{{ __('Swap') }}</button>
                @else
                    <x-app.button.submit>{{ __('Swap') }}</x-app.button.submit>
                @endif
            </div>
        </form>
    </div>
@endsection

