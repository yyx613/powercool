<div class="bg-white p-4 border rounded-md" id="parent-payment-details-container">
    <div class="flex items-center mb-6 border-l-8 border-yellow-400 px-3 py-1 bg-yellow-50 w-fit">
        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24"
            width="512" height="512">
            <path
                d="M14.541,5.472c1.196-1.02,2.459-2.548,2.459-4.472V0H7V1c0,1.924,1.263,3.451,2.459,4.472C4.754,7.149,1,13.124,1,18c0,3.309,2.691,6,6,6h10c3.309,0,6-2.691,6-6,0-4.876-3.754-10.851-8.459-12.528Zm-5.334-3.472h5.583c-.521,1.256-1.89,2.31-2.783,2.852-.752-.46-2.251-1.512-2.799-2.852Zm7.793,20H7c-2.206,0-4-1.794-4-4,0-5.161,4.59-10.983,8.998-10.983s9.002,5.823,9.002,10.983c0,2.206-1.794,4-4,4Zm-7-9c0,.378,.271,.698,.644,.76l3.042,.507c1.341,.223,2.315,1.373,2.315,2.733,0,1.654-1.346,3-3,3v1h-2v-1c-1.654,0-3-1.346-3-3h2c0,.551,.449,1,1,1h2c.551,0,1-.449,1-1,0-.378-.271-.698-.644-.76l-3.042-.507c-1.341-.223-2.315-1.373-2.315-2.733,0-1.654,1.346-3,3-3v-1h2v1c1.654,0,3,1.346,3,3h-2c0-.551-.449-1-1-1h-2c-.551,0-1,.449-1,1Z" />
        </svg>
        <span class="text-lg ml-3 font-bold">{{ __('Payment Details') }}</span>
    </div>
    <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-8 w-full mb-8">
        <div class="flex flex-col">
            <x-app.input.label id="display_payment_method" class="mb-1">{{ __('Payment Method') }} <svg class="inline-block w-4 h-4 ml-1 text-gray-400 cursor-help" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><title>{{ __('For PDF') }}</title><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></x-app.input.label>
            <x-app.input.select2 name="display_payment_method" id="display_payment_method" :hasError="false"
                placeholder="{{ __('Select a method') }}">
                <option value=""></option>
                @foreach ($payment_methods as $method)
                    <option value="{{ $method->id }}" @selected(old('display_payment_method', isset($sale) ? $sale->payment_method : (isset($quo) ? $quo->payment_method : null)) == $method->id)>{{ $method->name }}</option>
                @endforeach
            </x-app.input.select2>
        </div>
        <div class="flex flex-col hidden" id="parent-payment-term-container">
            <x-app.input.label id="display_payment_term" class="mb-1">{{ __('Payment Term') }} <svg class="inline-block w-4 h-4 ml-1 text-gray-400 cursor-help" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><title>{{ __('For the record only') }}</title><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></x-app.input.label>
            <x-app.input.select2 name="display_payment_term" id="display_payment_term" :hasError="false"
                placeholder="{{ __('Select a term') }}">
                <option value=""></option>
                @foreach ($credit_terms as $term)
                    <option value="{{ $term->id }}" @selected(old('display_payment_term', isset($sale) ? $sale->payment_term : null) == $term->id)>{{ $term->name }}</option>
                @endforeach
            </x-app.input.select2>
        </div>
        <div class="flex flex-col">
            <x-app.input.label id="display_payment_due_date" class="mb-1">{{ __('Payment Due Date') }}</x-app.input.label>
            <x-app.input.input name="display_payment_due_date" id="display_payment_due_date" :hasError="false"
                value="{{ isset($sale) ? $sale->payment_due_date : null }}" />
        </div>
        <div class="flex flex-col">
            <x-app.input.label id="payment_remark" class="mb-1">{{ __('Payment Remark') }}</x-app.input.label>
            <x-app.input.input name="display_payment_remark" id="display_payment_remark" :hasError="false"
                value="{{ isset($sale) ? $sale->payment_remark : null }}" />
        </div>
    </div>
</div>

@push('scripts')
    <script>
        PARENT_CREDIT_PAYMENT_METHOD_IDS = @json($credit_payment_method_ids ?? []);

        $(document).ready(function() {
            let paymentMethodVal = null

            if (SALE != null) {
                paymentMethodVal = SALE.payment_method
                if (SALE.payment_term) {
                    $('select[name="display_payment_term"]').val(SALE.payment_term).trigger('change')
                }
            } else if (typeof QUO !== 'undefined' && QUO != null) {
                paymentMethodVal = QUO.payment_method
            }

            if (paymentMethodVal) {
                if (PARENT_CREDIT_PAYMENT_METHOD_IDS.includes(parseInt(paymentMethodVal))) {
                    $('#parent-payment-term-container').removeClass('hidden')
                }
            }

            // Disable read-only fields via JS (select2 doesn't support disabled prop from blade)
            $('select[name="display_payment_method"]').prop('disabled', true)
            $('select[name="display_payment_term"]').prop('disabled', true)
            $('input[name="display_payment_due_date"]').attr('disabled', true)
            $('input[name="display_payment_remark"]').attr('disabled', true)

            // Apply consistent #eee background for disabled fields
            $('#parent-payment-details-container select[name="display_payment_method"]').css('backgroundColor', '#eee')
            $('#parent-payment-details-container .select2-container').css('backgroundColor', '#eee')
            $('#parent-payment-details-container input[name="display_payment_due_date"]').css('backgroundColor', '#eee')
            $('#parent-payment-details-container input[name="display_payment_remark"]').css('backgroundColor', '#eee')
            $('#parent-payment-details-container input[name="display_payment_due_date"]').parent().css('backgroundColor', '#eee')
            $('#parent-payment-details-container input[name="display_payment_remark"]').parent().css('backgroundColor', '#eee')
        })
    </script>
@endpush
