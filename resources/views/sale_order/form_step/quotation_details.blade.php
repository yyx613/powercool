<div class="bg-white p-4 border rounded-md">
    <div class="flex items-center mb-6 border-l-8 border-yellow-400 px-3 py-1 bg-yellow-50 w-fit">
        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512"
            height="512">
            <path
                d="M12,0A12,12,0,1,0,24,12,12.013,12.013,0,0,0,12,0Zm0,22A10,10,0,1,1,22,12,10.011,10.011,0,0,1,12,22Z" />
            <path d="M12,10H11a1,1,0,0,0,0,2h1v6a1,1,0,0,0,2,0V12A2,2,0,0,0,12,10Z" />
            <circle cx="12" cy="6.5" r="1.5" />
        </svg>
        <span class="text-lg ml-3 font-bold">{{ __('Quotation Details') }}</span>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 md:gap-8 w-full mb-8">
        <div class="flex flex-col">
            <x-app.input.label id="customer" class="mb-1">{{ __('Company') }} <span
                    class="text-sm text-red-500">*</span></x-app.input.label>
            <x-app.input.select2 name="customer" id="customer" :hasError="$errors->has('customer')"
                placeholder="{{ __('Select a company') }}">
                <option value="">{{ __('Select a company') }}</option>
                @foreach ($customers as $cu)
                    <option value="{{ $cu->id }}" @selected(old('customer', isset($sale) ? $sale->customer_id : null) == $cu->id)>{{ $cu->company_name }}</option>
                @endforeach
            </x-app.input.select2>
            <x-app.message.error id="customer_err" />
        </div>
        <div class="flex flex-col">
            <x-app.input.label id="sale" class="mb-1">{{ __('Sales Agent') }} <span
                    class="text-sm text-red-500">*</span></x-app.input.label>
            <x-app.input.select2 name="sale" id="sale" :hasError="$errors->has('sale')"
                placeholder="{{ __('Select a sales agent') }}">
                <option value="">{{ __('Select a sales agent') }}</option>
                @foreach ($sales as $sa)
                    <option value="{{ $sa->id }}" @selected(old('sale', isset($sale) ? $sale->sale_id : null) == $sa->id)>{{ $sa->name }}</option>
                @endforeach
            </x-app.input.select2>
            <x-app.message.error id="sale_err" />
        </div>
        <div class="flex flex-col">
            <x-app.input.label id="reference" class="mb-1">{{ __('Your P/O No') }}</x-app.input.label>
            <x-app.input.multi-input name="reference" id="reference" :hasError="$errors->has('reference')"
                value="{{ isset($sale) ? $sale->reference : null }}" />
            <x-app.message.error id="reference_err" />
        </div>
        <div class="flex flex-col">
            <x-app.input.label id="report_type" class="mb-1">{{ __('Type') }} <span
                    class="text-sm text-red-500">*</span></x-app.input.label>
            <x-app.input.select name="report_type" id="report_type" :hasError="$errors->has('report_type')">
                <option value="">{{ __('Select a type') }}</option>
                @foreach ($report_types as $type)
                    <option value="{{ $type->id }}" @selected(old('report_type', isset($sale) ? $sale->report_type : null) == $type->id)>{{ $type->name }}</option>
                @endforeach
            </x-app.input.select>
            <x-app.message.error id="report_type_err" />
        </div>

        <div class="flex flex-col">
            <x-app.input.label id="attention_to" class="mb-1">{{ __('Attention To') }}</x-app.input.label>
            <x-app.input.input name="attention_to" id="attention_to" :hasError="$errors->has('attention_to')"
                value="{{ isset($sale) ? $sale->quo_cc : null }}" disabled="true" />
            <x-app.message.error id="attention_to_err" />
        </div>
        <div class="flex flex-col">
            <x-app.input.label id="billing_address" class="mb-1">{{ __('Billing Address') }}</x-app.input.label>
            <x-app.input.select id="billing_address" name="billing_address">
                <option value="">{{ __('Select a billing address') }}</option>
            </x-app.input.select>
            <p class="mt-1.5 text-sm text-slate-500 leading-none">
                {{ __('Please make it empty before entering a new address') }}
            </p>
            <x-app.message.error id="billing_address_err" />
        </div>
        <div class="flex flex-col">
            <x-app.input.label id="delivery_address" class="mb-1">{{ __('Delivery Address') }}</x-app.input.label>
            <x-app.input.select id="delivery_address" name="delivery_address">
                <option value="">{{ __('Select a delivery address') }}</option>
            </x-app.input.select>
            <p class="mt-1.5 text-sm text-slate-500 leading-none">
                {{ __('Please make it empty before entering a new address') }}
            </p>
            <x-app.message.error id="delivery_address_err" />
        </div>
        <div class="flex flex-col">
            <x-app.input.label id="status" class="mb-1">{{ __('Status') }} <span
                    class="text-sm text-red-500">*</span></x-app.input.label>
            <x-app.input.select name="status" id="status" :hasError="$errors->has('status')">
                <option value="">{{ __('Select a status') }}</option>
                <option value="1" @selected(old('status', isset($sale) ? $sale->status : null) == 1)>{{ __('Active') }}</option>
                <option value="0" @selected(old('status', isset($sale) ? $sale->status : null) === 0)>{{ __('Inactive') }}</option>
                @if (isset($sale) && $sale->status == 4)
                    <option value="4" @selected(old('status', isset($sale) ? $sale->status : null) === 4)>{{ __('Pending Approval') }}</option>
                @endif
            </x-app.input.select>
            <x-app.message.error id="status_err" />
        </div>
    </div>
    {{-- Custom Address --}}
    <div class="pt-4 border-t border-slate-200">
        <div class="mb-8" id="new-billing-address">
            @include('components.app.address-field', [
                'title' => 'Billing Address',
            ])
        </div>
        <div id="new-delivery-address">
            @include('components.app.address-field', [
                'title' => 'Delivery Address',
            ])
        </div>
    </div>
</div>

@push('scripts')
    <script>
        INIT_EDIT = true
        CUSTOMERS = @json($customers ?? []);

        $(document).ready(function() {
            $('select[name="customer"]').trigger('change')

            if (SALE != null && SALE.status == 4) { // Pending Approval
                $('select[name="status"]').attr('disabled', true)
                $('select[name="status"]').attr('aria-disabled', true)
            }

            INIT_EDIT = false
        })

        $('select[name="customer"]').on('change', function() {
            let val = $(this).val()

            $('select[name="sale"]').val(null).trigger('change')

            for (let i = 0; i < CUSTOMERS.length; i++) {
                const element = CUSTOMERS[i];

                if (element.id == val) {
                    $('input[name="attention_to"]').val(element.name)
                    // Update payment term
                    $(`select[name="payment_term"]`).find('option').not(':first').remove();

                    $(`select[name="payment_term"]`).select2({
                        placeholder: 'Select a term'
                    })

                    for (let i = 0; i < element.credit_terms.length; i++) {
                        const term = element.credit_terms[i];

                        let opt = new Option(term.credit_term.name, term.credit_term.id)
                        $(`select[name="payment_term"]`).append(opt)
                    }
                    // Filter Sales agent
                    $(`select[name="sale"] option`).not(':first').addClass('hidden')
                    for (let j = 0; j < element.sales_agents.length; j++) {
                        $(`select[name="sale"] option[value="${element.sales_agents[j].sales_agent_id}"]`)
                            .removeClass('hidden')
                    }
                    if (INIT_EDIT) {
                        $('select[name="sale"]').val(SALE.sale_id).trigger('change')
                    }
                    break
                }
            }

            let url = '{{ route('customer.get_location') }}'
            url = `${url}?customer_id=${val}`

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: url,
                type: 'GET',
                async: false,
                success: function(res) {
                    // Billing Address
                    $('select[name="billing_address"] option').remove()

                    var opt = new Option("{!! __('Select a billing address') !!}", null)
                    $('select[name="billing_address"]').append(opt)

                    for (let i = 0; i < res.locations.length; i++) {
                        const loc = res.locations[i];

                        if (loc.type !== 1 && loc.type !== 3) continue

                        let opt = new Option(loc.address, loc.id, false, INIT_EDIT == true && loc.id ==
                            SALE.billing_address_id)
                        $('select[name="billing_address"]').append(opt)
                    }

                    // Delivery Address
                    $('select[name="delivery_address"] option').remove()

                    var opt = new Option("{!! __('Select a delivery address') !!}", null)
                    $('select[name="delivery_address"]').append(opt)

                    for (let i = 0; i < res.locations.length; i++) {
                        const loc = res.locations[i];

                        if (loc.type !== 2 && loc.type !== 3) continue

                        let opt = new Option(loc.address, loc.id, false, INIT_EDIT == true && loc.id ==
                            SALE.delivery_address_id)
                        $('select[name="delivery_address"]').append(opt)
                    }
                },
            });
        })

        // $('#quotation-form #submit-btn').on('click', function(e) {
        //     e.preventDefault()

        //     if (!QUOTATION_FORM_CAN_SUBMIT) return

        //     QUOTATION_FORM_CAN_SUBMIT = false

        //     $('#quotation-form #submit-btn').text('Updating')
        //     $('#quotation-form #submit-btn').removeClass('bg-yellow-400 shadow')
        //     $('.err_msg').addClass('hidden') // Remove error messages
        //     // Submit
        //     let url = ''
        //     url = `${url}?type=so`

        //     $.ajax({
        //         headers: {
        //             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        //         },
        //         url: url,
        //         type: 'POST',
        //         data: {
        //             'sale_id': typeof SALE !== 'undefined' && SALE != null ? SALE.id : null,
        //             'quo_id': typeof QUO !== 'undefined' && QUO != null ? QUO.id : null,
        //             'sale': $('#quotation-form select[name="sale"]').val(),
        //             'customer': $('#quotation-form select[name="customer"]').val(),
        //             'reference': $('#quotation-form input[name="reference"]').val(),
        //             'status': $('#quotation-form select[name="status"]').val(),
        //             'report_type': $('#quotation-form select[name="report_type"]').val(),
        //         },
        //         success: function(res) {
        //             if (typeof SALE !== 'undefined') {
        //                 SALE = res.sale
        //             }

        //             setTimeout(() => {
        //                 $('#quotation-form #submit-btn').text('Updated')
        //                 $('#quotation-form #submit-btn').addClass('bg-green-400 shadow')

        //                 setTimeout(() => {
        //                     $('#quotation-form #submit-btn').text('Save and Update')
        //                     $('#quotation-form #submit-btn').removeClass('bg-green-400')
        //                     $('#quotation-form #submit-btn').addClass('bg-yellow-400 shadow')

        //                     QUOTATION_FORM_CAN_SUBMIT = true
        //                 }, 2000);
        //             }, 300);
        //         },
        //         error: function(err) {
        //             setTimeout(() => {
        //                 if (err.status == StatusCodes.UNPROCESSABLE_ENTITY) {
        //                     let errors = err.responseJSON.errors

        //                     for (const key in errors) {
        //                         $(`#quotation-form #${key}_err`).find('p').text(errors[key])
        //                         $(`#quotation-form #${key}_err`).removeClass('hidden')
        //                     }
        //                 }
        //                 $('#quotation-form #submit-btn').text('Save and Update')
        //                 $('#quotation-form #submit-btn').addClass('bg-yellow-400 shadow')

        //                 QUOTATION_FORM_CAN_SUBMIT = true
        //             }, 300);
        //         },
        //     });
        // })
    </script>
@endpush
