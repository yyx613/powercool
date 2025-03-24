@extends('layouts.app')

@section('content')
    <div class="mb-6 flex justify-between items-start lg:items-center flex-col lg:flex-row">
        <x-app.page-title class="mb-4 lg:mb-0">{{ __('Product Selection') }}</x-app.page-title>
    </div>
    @include('components.app.alert.parent')
    <div class="bg-white p-4 rounded-md shadow" id="content-container">
        <div class="w-full mb-4 flex flex-col gap-4">
            @foreach($products as $pro)
                <div class="p-2 rounded-md border border-slate-200 products" data-is-raw-material="{{ $pro->is_raw_material }}" data-product-id="{{ $pro->product->id }}">
                    <div class="flex-1 flex justify-between items-start">
                        <div>
                            <h6 class="font-semibold leading-none mb-2">{{ $pro->product->model_name }}</h6>
                            <p class="text-xs text-slate-500">{{ __('SKU') }}: {{ $pro->product->sku }}</p>
                            <p class="text-xs text-slate-500">{{ __('Description') }}: {{ $pro->product->model_desc }}</p>
                        </div>
                    </div>
                    <div class="border-t pt-4 mt-4">
                        @if (!$pro->is_raw_material)
                            @foreach ($pro->children as $pc)
                                @if (!isset($is_view) && $pc->selected)
                                    @continue;
                                @endif

                                <label for="{{ $pc->id }}" class="w-full block">
                                    <input type="checkbox" name="product_children" id="{{ $pc->id }}" value="{{ $pc->id }}" {{ $pc->selected == true ? 'checked' : '' }} class="rounded mr-1 border-gray-300">
                                    <span>{{ $pc->sku }}</span>
                                </label>
                            @endforeach
                        @elseif ($pro->qty > 0)
                            <x-app.input.input type="text" name="qty" class="int-input" value="{{ $pro->product->selected_qty ?? null }}" placeholder="{{ __('Enter quantity') }}" />
                            @if (!isset($is_view))
                                <p class="text-xs text-slate-500 mt-1 ml-1">{{ __('Quantity Left: ') . $pro->qty }}</p>
                            @endif
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
        @if (!isset($is_view))
            <div class="mt-8 flex justify-end">
                <x-app.button.submit id="return-btn">{{ __('Return Items') }}</x-app.button.submit>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
<script>
    INVOICE_ID = @json($invoice_id);

    $('#return-btn').on('click', function() {
        selected_products = []

        $('.products').each(function(i, obj) {
            if ($(this).data('is-raw-material') && $(this).find('input[type="text"]').val() != '') {
                selected_products.push({
                    'is_raw_material' : true,
                    'id' : $(this).data('product-id'),
                    'qty' : $(this).find('input[type="text"]').val(),
                })
            } else {
                $(this).find('input[name="product_children"]').each(function(i, obj) {
                    if (!$(this).is(':checked')) return;

                    selected_products.push({
                        'is_raw_material' : false,
                        'id' : $(this).val(),
                    })
                })
            }
        })

        if (selected_products.length <= 0) return;

        let url = `{{ config('app.url') }}/invoice-return/product-selection-submit/${INVOICE_ID}`
        url = `${url}?products=${ JSON.stringify(selected_products)}`

        window.location.href = url
    })
</script>
@endpush
