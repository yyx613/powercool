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
            <x-app.input.label id="open_until" class="mb-1">{{ __('Validity') }} <span
                    class="text-sm text-red-500">*</span></x-app.input.label>
            <x-app.input.input name="open_until" id="open_until" :hasError="$errors->has('open_until')"
                value="{{ isset($sale) ? $sale->open_until : null }}" />
            <x-app.message.error id="open_until_err" />
        </div>
        <div class="flex flex-col">
            <x-app.input.label id="customer" class="mb-1">{{ __('Company') }} <span
                    class="text-sm text-red-500">*</span></x-app.input.label>
            <div class="relative">
                <x-app.input.select name="customer" id="customer" :hasError="$errors->has('customer')"
                    placeholder="{{ __('Select a company') }}">
                    <option value="">{{ __('Select a company') }}</option>
                    @if (isset($customers))
                        @foreach ($customers as $cus)
                            <option value="{{ $cus->id }}" @selected(old('customer', isset($replicate) ? $replicate->customer_id : (isset($sale) ? $sale->customer_id : null)) == $cus->id)>{{ $cus->company_name }} -
                                {{ $cus->company_group == 1 ? 'Power Cool' : 'Hi-Ten' }}</option>
                        @endforeach
                    @endif
                </x-app.input.select>
            </div>
            <x-app.message.error id="customer_err" />
        </div>
        <div class="flex flex-col">
            <x-app.input.label id="mobile" class="mb-1">{{ __('Mobile') }}</x-app.input.label>
            <x-app.input.input name="mobile" id="mobile" :hasError="$errors->has('mobile')" disabled="true" />
            <x-app.message.error id="mobile_err" />
        </div>
        <div class="flex flex-col">
            <x-app.input.label id="reference" class="mb-1">{{ __('Reference') }}</x-app.input.label>
            <x-app.input.input name="reference" id="reference" :hasError="$errors->has('reference')"
                value="{{ isset($replicate) ? $replicate->reference : (isset($sale) ? $sale->reference : null) }}" />
            <x-app.message.error id="reference_err" />
        </div>
        <div class="flex flex-col">
            <x-app.input.label id="from" class="mb-1">{{ __('From') }}</x-app.input.label>
            <x-app.input.input name="from" id="from" :hasError="$errors->has('from')"
                value="{{ isset($replicate) ? $replicate->quo_from : (isset($sale) ? $sale->quo_from : null) }}" />
            <x-app.message.error id="from_err" />
        </div>
        <div class="flex flex-col">
            <x-app.input.label id="cc" class="mb-1">{{ __('C.C.') }}</x-app.input.label>
            <x-app.input.input name="cc" id="cc" :hasError="$errors->has('cc')"
                value="{{ isset($replicate) ? $replicate->quo_cc : (isset($sale) ? $sale->quo_cc : null) }}" />
            <x-app.message.error id="cc_err" />
        </div>
        <div class="flex flex-col">
            <x-app.input.label id="warehouse" class="mb-1">{{ __('Warehouse') }}</x-app.input.label>
            <x-app.input.input name="warehouse" id="warehouse" value="{{ $warehouse ?? '' }}" :hasError="$errors->has('warehouse')" disabled="true" />
            <x-app.message.error id="warehouse_err" />
        </div>
        <div class="flex flex-col">
            <x-app.input.label id="store" class="mb-1">{{ __('Store') }}</x-app.input.label>
            <x-app.input.input name="store" id="store" :hasError="$errors->has('store')"
                value="{{ isset($replicate) ? $replicate->store : (isset($sale) ? $sale->store : null) }}" />
            <x-app.message.error id="store_err" />
        </div>
        <div class="flex flex-col">
            <x-app.input.label id="sale" class="mb-1">{{ __('Sales Agent') }} <span
                    class="text-sm text-red-500">*</span></x-app.input.label>
            <x-app.input.select name="sale" id="sale" :hasError="$errors->has('sale')"
                placeholder="{{ __('Select a sales agent') }}">
                <option value="">{{ __('Select a sales agent') }}</option>
                @foreach ($sales_agents as $sa)
                    <option value="{{ $sa->id }}" @selected(old('sale', isset($replicate) ? $replicate->sale_id : (isset($sale) ? $sale->sale_id : null)) == $sa->id)>{{ $sa->name }}</option>
                @endforeach
            </x-app.input.select>
            <x-app.message.error id="sale_err" />
        </div>
        <div class="flex flex-col">
            <x-app.input.label id="report_type" class="mb-1">{{ __('Type') }} <span
                    class="text-sm text-red-500">*</span></x-app.input.label>
            <x-app.input.select name="report_type" id="report_type" :hasError="$errors->has('report_type')">
                <option value="">{{ __('Select a type') }}</option>
                @foreach ($report_types as $type)
                    <option value="{{ $type->id }}" @selected(old('report_type', isset($replicate) ? $replicate->report_type : (isset($sale) ? $sale->report_type : null)) == $type->id)>{{ $type->name }}</option>
                @endforeach
            </x-app.input.select>
            <x-app.message.error id="report_type_err" />
        </div>
        <div class="flex flex-col">
            <x-app.input.label id="attention_to" class="mb-1">{{ __('Attention To') }}</x-app.input.label>
            <x-app.input.input name="attention_to" id="attention_to" :hasError="$errors->has('attention_to')"
                value="{{ isset($replicate) ? $replicate->quo_cc : (isset($sale) ? $sale->quo_cc : null) }}"
                disabled="true" />
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
            <x-app.input.label id="payment_method" class="mb-1">{{ __('Payment Method') }}</x-app.input.label>
            <x-app.input.select2 name="payment_method" id="payment_method" :hasError="$errors->has('payment_method')"
                placeholder="{{ __('Select a method') }}">
                <option value=""></option>
                @foreach ($payment_methods as $method)
                    <option value="{{ $method->id }}" @selected(old('payment_method', isset($replicate) ? $replicate->payment_method : (isset($sale) ? $sale->payment_method : null)) == $method->id)>{{ $method->name }}</option>
                @endforeach
                </x-app.input.selec2>
                @if (isset($sale) && $sale->payment_method_status != null)
                    <div class="col-span-4 mt-1.5">
                        @if ($sale->payment_method_revised == 1)
                            <span
                                class="border rounded border-blue-500 text-blue-500 text-xs font-medium px-1 py-0.5">{{ __('Revised') }}</span>
                        @endif
                        @if ($sale->payment_method_status == 4)
                            <span
                                class="border rounded border-slate-500 text-slate-500 text-xs font-medium px-1 py-0.5">{{ __('Pending Approval') }}</span>
                        @elseif ($sale->payment_method_status == 5)
                            <span
                                class="border rounded border-green-600 text-green-600 text-xs font-medium px-1 py-0.5">{{ __('Approved') }}</span>
                        @elseif ($sale->payment_method_status == 7)
                            <span
                                class="border rounded border-red-600 text-red-600 text-xs font-medium px-1 py-0.5">{{ __('Rejected') }}</span>
                        @endif
                    </div>
                @endif
                <div class="hidden mt-1" id="credit-term-hint-container">
                    <span class="text-xs font-medium">Credit Terms: </span>
                    <span class="text-xs text-slate-600" id="value"></span>
                </div>
                <x-app.message.error id="payment_method_err" />
        </div>
        <div class="flex flex-col">
            <x-app.input.label id="status" class="mb-1">{{ __('Status') }} <span
                    class="text-sm text-red-500">*</span></x-app.input.label>
            <x-app.input.select name="status" id="status" :hasError="$errors->has('status')">
                <option value="">{{ __('Select a status') }}</option>
                @if (isset($sale) && $sale->status == 4)
                    <option value="4" selected>{{ __('Pending Approval') }}</option>
                @elseif (isset($sale) && $sale->status == 7)
                    <option value="7" selected>{{ __('Rejected') }}</option>
                @elseif (isset($sale) && $sale->status == 5)
                    <option value="5" selected>{{ __('Approved') }}</option>
                @else
                    <option value="1" @selected(old('status', isset($replicate) ? 1 : (isset($sale) ? $sale->status : null)) == 1)>{{ __('Active') }}</option>
                    <option value="0" @selected(old('status', isset($sale) ? $sale->status : null) === 0)>{{ __('Inactive') }}</option>
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
        QUOTATION_DETAILS_INIT_EDIT = true
        CUSTOMERS = @json($customers ?? []);
        CREDIT_PAYMENT_METHOD_IDS = @json($credit_payment_method_ids ?? []);
        SEARCH_CUSTOMERS_URL = '{{ route('customer.get_by_keyword') }}'

        $(document).ready(function() {
            buildCompanySelect2()

            if (SALE != null) {
                $('select[name="customer"]').trigger('change')
                $('select[name="billing_address"]').trigger('change')
            }
            QUOTATION_DETAILS_INIT_EDIT = false
        })

        $('input[name="open_until"]').daterangepicker(datepickerParam)
        $('input[name="open_until"]').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD'));
        });

        $('select[name="billing_address"]').on('change', function() {
            let val = $(this).val()

            if (val == 'null' || val == null || val == '') {
                $('#new-billing-address input').attr('disabled', false)
                $('#new-billing-address input').attr('aria-disabled', false)
                $('#new-billing-address input').parent().attr('aria-disabled', false)
            } else {
                $('#new-billing-address input').val(null)
                $('#new-billing-address input').attr('disabled', true)
                $('#new-billing-address input').attr('aria-disabled', true)
                $('#new-billing-address input').parent().attr('aria-disabled', true)
            }
        })
        $('select[name="delivery_address"]').on('change', function() {
            let val = $(this).val()
            console.debug(val)

            if (val == 'null' || val == null || val == '') {
                $('#new-delivery-address input').attr('disabled', false)
                $('#new-delivery-address input').attr('aria-disabled', false)
                $('#new-delivery-address input').parent().attr('aria-disabled', false)
            } else {
                $('#new-delivery-address input').val(null)
                $('#new-delivery-address input').attr('disabled', true)
                $('#new-delivery-address input').attr('aria-disabled', true)
                $('#new-delivery-address input').parent().attr('aria-disabled', true)
            }
        })
        $('select[name="payment_method"]').on('change', function() {
            let val = $(this).val()

            if (CREDIT_PAYMENT_METHOD_IDS.includes(parseInt(val))) {
                $('#payment-term-container').removeClass('hidden')
            } else {
                $('#payment-term-container').addClass('hidden')
            }
        })
        $('select[name="customer"]').on('change', function() {
            let customer_id = $(this).val()

            $('select[name="sale"]').val(null).trigger('change')

            var element = CUSTOMERS[customer_id]
            $('input[name="attention_to"]').val(element.name)
            $('input[name="mobile"]').val(element.phone ?? element.mobile_number)
            // Update payment term
            $(`select[name="payment_term"]`).find('option').not(':first').remove();

            let creditTerms = []
            for (let i = 0; i < element.credit_terms.length; i++) {
                const term = element.credit_terms[i];
                creditTerms.push(term.credit_term.name)

                let opt = new Option(term.credit_term.name, term.credit_term.id)
                $(`select[name="payment_term"]`).append(opt)
            }
            if (creditTerms.length > 0) {
                $('#credit-term-hint-container').removeClass('hidden')
                $('#credit-term-hint-container #value').text(creditTerms.join(', '))
            }
            if (QUOTATION_DETAILS_INIT_EDIT) {
                $('select[name="sale"]').val(SALE.sale_id).trigger('change')
                $('select[name="payment_term"]').val(SALE.payment_term).trigger('change')
            } else if (QUOTATION_DETAILS_INIT_EDIT == false && element.sales_agents.length === 1) {
                $('select[name="sale"]').val(element.sales_agents[0].sales_agent_id).trigger('change')
            }

            let url = '{{ route('customer.get_location') }}'
            url = `${url}?customer_id=${customer_id}`

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: url,
                type: 'GET',
                async: false,
                success: function(res) {
                    $('select[name="billing_address"] option').remove()

                    // Default option
                    let opt = new Option("{!! __('Select a billing address') !!}", null)
                    $('select[name="billing_address"]').append(opt)

                    for (let i = 0; i < res.locations.length; i++) {
                        const loc = res.locations[i];

                        if (loc.type == 1 || loc.type == 3) {
                            var addr = loc.address1
                            if (loc.address2 != null) {
                                addr = `${addr}, ${loc.address2}`
                            }
                            if (loc.address3 != null) {
                                addr = `${addr}, ${loc.address3}`
                            }
                            if (loc.address4 != null) {
                                addr = `${addr}, ${loc.address4}`
                            }

                            let opt = new Option(
                                addr, loc.id,
                                false, QUOTATION_DETAILS_INIT_EDIT == true ? loc.id ==
                                SALE.billing_address_id : loc.is_default)
                            $('select[name="billing_address"]').append(opt)
                        }
                        if (loc.type == 2 || loc.type == 3) {
                            var addr = loc.address1
                            if (loc.address2 != null) {
                                addr = `${addr}, ${loc.address2}`
                            }
                            if (loc.address3 != null) {
                                addr = `${addr}, ${loc.address3}`
                            }
                            if (loc.address4 != null) {
                                addr = `${addr}, ${loc.address4}`
                            }

                            let opt = new Option(
                                addr, loc.id,
                                false, QUOTATION_DETAILS_INIT_EDIT == true ? loc.id ==
                                SALE.delivery_address_id : loc.is_default)
                            $('select[name="delivery_address"]').append(opt)
                        }
                    }
                    $('select[name="billing_address"]').trigger('change')
                    $('select[name="delivery_address"]').trigger('change')
                },
            });
        })
        $('body').on('focus', '[aria-labelledby="select2-customer-container"]', function() {
            $('select[name="customer"]').select2('open')
        })

        function buildCompanySelect2() {
            $('select[name="customer"]').select2({
                minimumInputLength: 1,
                placeholder: 'Search for a company',
                ajax: {
                    url: `${SEARCH_CUSTOMERS_URL}?is_edit=${REPLICATE == null && SALE != null}`,
                    delay: DEBOUNCE_DURATION,
                    dataType: 'json',
                    data: function(params) {
                        var query = {
                            keyword: params.term,
                        }
                        return query;
                    },
                    processResults: function(data) {
                        CUSTOMERS = data.customers

                        return {
                            results: $.map(data.customers, function(item) {
                                return {
                                    id: item.id,
                                    text: `${item.company_name} - ${item.company_group == 1 ? 'Power Cool' : 'Hi-Ten'}`
                                };
                            })
                        }
                    }
                }
            })
            $('select[name="customer"]').parent().addClass('border border-gray-300 rounded-md overflow-hidden')
        }
    </script>
@endpush
