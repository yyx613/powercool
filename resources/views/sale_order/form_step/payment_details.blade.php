<div class="bg-white p-4 border rounded-md">
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
            <x-app.input.label id="payment_term" class="mb-1">{{ __('Payment Term') }} <span
                    class="text-sm text-red-500">*</span></x-app.input.label>
            <x-app.input.select2 name="payment_term" id="payment_term" :hasError="$errors->has('payment_term')"
                placeholder="{{ __('Select a term') }}">
                <option value=""></option>
            </x-app.input.select2>
            <x-app.message.error id="payment_term_err" />
        </div>
        <div class="flex flex-col">
            <x-app.input.label id="payment_method" class="mb-1">{{ __('Payment Method') }} <span
                    class="text-sm text-red-500">*</span></x-app.input.label>
            <x-app.input.select2 name="payment_method" id="payment_method" :hasError="$errors->has('payment_method')"
                placeholder="{{ __('Select a method') }}">
                <option value=""></option>
                @foreach ($payment_methods as $method)
                    <option value="{{ $method->id }}" @selected(old('payment_method', isset($sale) ? $sale->payment_method : null) == $method->id)>{{ $method->name }}</option>
                @endforeach
                </x-app.input.selec2t>
                <x-app.message.error id="payment_method_err" />
        </div>
        <div class="flex flex-col">
            <x-app.input.label id="payment_due_date" class="mb-1">{{ __('Payment Due Date') }} <span
                    class="text-sm text-red-500">*</span></x-app.input.label>
            <x-app.input.input name="payment_due_date" id="payment_due_date" :hasError="$errors->has('payment_due_date')"
                value="{{ isset($sale) ? $sale->payment_due_date : null }}" />
            <x-app.message.error id="payment_due_date_err" />
        </div>
        <div class="flex flex-col">
            <x-app.input.label id="payment_status" class="mb-1">{{ __('Payment Status') }} <span
                    class="text-sm text-red-500">*</span></x-app.input.label>
            <x-app.input.select2 name="payment_status" id="payment_status" :hasError="$errors->has('payment_status')"
                placeholder="{{ __('Select a status') }}">
                <option value=""></option>
                @foreach ($payment_statuses as $key => $val)
                    <option value="{{ $key }}" @selected(old('payment_status', isset($sale) ? $sale->payment_status : null) == $key)>{{ __($val) }}</option>
                @endforeach
                </x-app.input.selec2t>
                <x-app.message.error id="payment_status_err" />
        </div>
        <div class="flex flex-col">
            <x-app.input.label id="payment_remark" class="mb-1">{{ __('Payment Remark') }}</x-app.input.label>
            <x-app.input.input name="payment_remark" id="payment_remark" :hasError="$errors->has('payment_remark')"
                value="{{ isset($sale) ? $sale->payment_remark : null }}" />
            <x-app.message.error id="payment_remark_err" />
        </div>
        <div class="flex flex-col">
            <x-app.input.label id="payment_amount" class="mb-1">{{ __('Payment Amount') }}</x-app.input.label>
            <x-app.input.input name="payment_amount" id="payment_amount" :hasError="$errors->has('payment_amount')" class="decimal-input" />
            <x-app.message.error id="payment_amount_err" />
            <div id="payment-amount-records-container" class="hidden">
                <span class="font-semibold mb-1 mt-1 text-xs inline-flex">{{ __('Payment History') }}</span>
                <p class="text-xs" id="template"></p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        // PAYMENT_FORM_CAN_SUBMIT = true
        PAYMENT_AMOUNT = @json(isset($sale) ? $sale->getFormattedPaymentAmount(true) : null);

        $('input[name="payment_due_date"]').daterangepicker(datepickerParam)
        $('input[name="payment_due_date"]').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD'));
        });

        $(document).ready(function() {
            if (SALE != null) {
                $('select[name="payment_term"]').val(SALE.payment_term).trigger('change')

                if (SALE.can_by_pass_conversion) $('#by-pass-conversion-hint').removeClass('hidden')

                updatePaymentAmountRecords()
            }
        })

        function updatePaymentAmountRecords() {
            $('#payment-amount-records-container p:not("#template")').remove()

            if (PAYMENT_AMOUNT != null) {
                $('#payment-amount-records-container').removeClass('hidden')

                for (let i = 0; i < PAYMENT_AMOUNT.length; i++) {
                    let clone = $('#payment-amount-records-container #template')[0].cloneNode(true);

                    $(clone).text(`${i+1}: RM ${PAYMENT_AMOUNT[i]}`)
                    $(clone).removeAttr('id')

                    $('#payment-amount-records-container').append(clone)
                }
            }
        }

        // $('#payment-form').on('submit', function(e) {
        //     e.preventDefault()

        //     if (!PAYMENT_FORM_CAN_SUBMIT) return

        //     PAYMENT_FORM_CAN_SUBMIT = false

        //     $('#payment-form #submit-btn').text('Updating')
        //     $('#payment-form #submit-btn').removeClass('bg-yellow-400 shadow')
        //     $('.err_msg').addClass('hidden') // Remove error messages
        //     // Submit
        //     let url = ''
        //     url = `${url}`

        //     $.ajax({
        //         headers: {
        //             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        //         },
        //         url: url,
        //         type: 'POST',
        //         data: {
        //             'sale_id': typeof SALE !== 'undefined' && SALE != null ? SALE.id : null,
        //             'payment_term': $('#payment-form select[name="payment_term"]').val(),
        //             'payment_method': $('#payment-form select[name="payment_method"]').val(),
        //             'payment_due_date': $('#payment-form input[name="payment_due_date"]').val(),
        //             'payment_amount': $('#payment-form input[name="payment_amount"]').val(),
        //             'payment_status': $('#payment-form select[name="payment_status"]').val(),
        //             'payment_remark': $('#payment-form input[name="payment_remark"]').val(),
        //             'by_pass_conversion': $('#payment-form input[name="by_pass_conversion"]').val(),
        //         },
        //         success: function(res) {
        //             if (res.new_payment_amount) {
        //                 PAYMENT_AMOUNT = res.new_payment_amount
        //                 updatePaymentAmountRecords()
        //             }
        //             if (res.can_by_pass_conversion) {
        //                 $('#by-pass-conversion-hint').removeClass('hidden')
        //             }

        //             setTimeout(() => {
        //                 $('#payment-form #submit-btn').text('Updated')
        //                 $('#payment-form #submit-btn').addClass('bg-green-400 shadow')

        //                 setTimeout(() => {
        //                     $('#payment-form #submit-btn').text('Save and Update')
        //                     $('#payment-form #submit-btn').removeClass('bg-green-400')
        //                     $('#payment-form #submit-btn').addClass('bg-yellow-400 shadow')

        //                     PAYMENT_FORM_CAN_SUBMIT = true
        //                 }, 2000);
        //             }, 300);
        //         },
        //         error: function(err) {
        //             setTimeout(() => {
        //                 if (err.status == StatusCodes.UNPROCESSABLE_ENTITY) {
        //                     let errors = err.responseJSON.errors

        //                     for (const key in errors) {
        //                         $(`#payment-form #${key}_err`).find('p').text(errors[key])
        //                         $(`#payment-form #${key}_err`).removeClass('hidden')
        //                     }
        //                 }
        //                 $('#payment-form #submit-btn').text('Save and Update')
        //                 $('#payment-form #submit-btn').addClass('bg-yellow-400 shadow')

        //                 PAYMENT_FORM_CAN_SUBMIT = true
        //             }, 300);
        //         },
        //     });
        // })
    </script>
@endpush
