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
                            <h6 class="font-semibold leading-none mb-2">{{ $pro->product->model_desc }}</h6>
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

    {{-- Return reason modal: a reason is required, then the return is sent for admin approval. --}}
    <div id="reason-modal" class="hidden fixed inset-0 z-50 items-center justify-center">
        <div id="reason-modal-overlay" class="absolute inset-0 bg-black bg-opacity-50"></div>
        <div class="relative bg-white rounded-md shadow-lg w-full max-w-md mx-4 p-5">
            <h2 class="text-base font-semibold text-gray-800 mb-3">{{ __('Return Items') }}</h2>
            <label for="reason" class="block text-sm text-gray-700 mb-1">
                {{ __('Reason') }} <span class="text-red-500">*</span>
            </label>
            <textarea id="reason" rows="4"
                class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500"
                placeholder="{{ __('Enter the reason for this return') }}"></textarea>
            <p id="reason-error" class="hidden text-sm text-red-500 mt-1"></p>
            <div class="mt-4 flex justify-end gap-x-2">
                <button id="reason-cancel-btn" type="button"
                    class="rounded-md bg-gray-200 px-4 py-2 text-sm font-medium text-gray-700">{{ __('Cancel') }}</button>
                <button id="reason-confirm-btn" type="button"
                    class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white">{{ __('Submit for Approval') }}</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    INVOICE_ID = @json($invoice_id);

    // Build a hidden form and POST it (carries the CSRF token).
    function postAction(url, fields = {}) {
        let form = $('<form>', { method: 'POST', action: url });
        form.append($('<input>', {
            type: 'hidden',
            name: '_token',
            value: $('meta[name="csrf-token"]').attr('content')
        }));
        $.each(fields, function (name, value) {
            form.append($('<input>', { type: 'hidden', name: name, value: value }));
        });
        form.appendTo('body').submit();
    }

    function collectSelectedProducts() {
        let selected_products = []

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

        return selected_products
    }

    // Pick items, then ask for a reason before submitting for approval.
    $('#return-btn').on('click', function() {
        if (collectSelectedProducts().length <= 0) return;

        $('#reason').val('')
        $('#reason-error').addClass('hidden').text('')
        $('#reason-modal').removeClass('hidden').addClass('flex')
    })

    $('#reason-cancel-btn, #reason-modal-overlay').on('click', function() {
        $('#reason-modal').addClass('hidden').removeClass('flex')
    })

    $('#reason-confirm-btn').on('click', function() {
        let selected_products = collectSelectedProducts()
        if (selected_products.length <= 0) return;

        let reason = $.trim($('#reason').val())
        if (reason === '') {
            $('#reason-error').removeClass('hidden').text("{!! __('Please enter a reason for this return.') !!}")
            return
        }

        postAction(`{{ config('app.url') }}/invoice-return/product-selection-submit/${INVOICE_ID}`, {
            products: JSON.stringify(selected_products),
            reason: reason,
        })
    })
</script>
@endpush
