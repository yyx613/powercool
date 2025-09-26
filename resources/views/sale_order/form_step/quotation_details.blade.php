<div class="bg-white p-4 border rounded-md" id="quotation-details-container">
    <div class="flex items-center mb-6 border-l-8 border-yellow-400 px-3 py-1 bg-yellow-50 w-fit">
        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512"
            height="512">
            <path
                d="M12,0A12,12,0,1,0,24,12,12.013,12.013,0,0,0,12,0Zm0,22A10,10,0,1,1,22,12,10.011,10.011,0,0,1,12,22Z" />
            <path d="M12,10H11a1,1,0,0,0,0,2h1v6a1,1,0,0,0,2,0V12A2,2,0,0,0,12,10Z" />
            <circle cx="12" cy="6.5" r="1.5" />
        </svg>
        <span class="text-lg ml-3 font-bold">{{ __('Sale Order Details') }}</span>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 md:gap-8 w-full mb-8">
        @if (isSuperAdmin())
            <div class="flex flex-col">
                <x-app.input.label id="custom_date" class="mb-1">{{ __('Date') }}</x-app.input.label>
                <x-app.input.input name="custom_date" id="custom_date" :hasError="$errors->has('custom_date')"
                    value="{{ isset($sale) ? $sale->custom_date : null }}" />
                <x-app.message.error id="custom_date_err" />
            </div>
        @endif
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
            <x-app.input.label id="sale" class="mb-1">{{ __('Sales Agent') }} <span
                    class="text-sm text-red-500">*</span></x-app.input.label>
            <x-app.input.select name="sale" id="sale" :hasError="$errors->has('sale')"
                placeholder="{{ __('Select a sales agent') }}">
                <option value="">{{ __('Select a sales agent') }}</option>
                @foreach ($sales_agents as $sa)
                    <option value="{{ $sa->id }}" @selected(old('sale', isset($sale) ? $sale->sale_id : null) == $sa->id)>{{ $sa->name }}</option>
                @endforeach
            </x-app.input.select>
            <x-app.message.error id="sale_err" />
        </div>
        <div class="flex flex-col">
            <x-app.input.label id="warehouse" class="mb-1">{{ __('Warehouse') }}</x-app.input.label>
            <x-app.input.input name="warehouse" id="warehouse" value="{{ isset($sale) ? $sale->warehouse : null }}"
                :hasError="$errors->has('warehouse')" disabled="true" />
            <x-app.message.error id="warehouse_err" />
        </div>
        <div class="flex flex-col">
            <x-app.input.label id="store" class="mb-1">{{ __('Store') }}</x-app.input.label>
            <x-app.input.input name="store" id="store" :hasError="$errors->has('store')"
                value="{{ isset($sale) ? $sale->store : null }}" />
            <x-app.message.error id="store_err" />
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
            <x-app.message.error id="billing_address_err" />
        </div>
        <div class="flex flex-col">
            <x-app.input.label id="delivery_address" class="mb-1">{{ __('Delivery Address') }}</x-app.input.label>
            <x-app.input.select id="delivery_address" name="delivery_address">
                <option value="">{{ __('Select a delivery address') }}</option>
            </x-app.input.select>
            <x-app.message.error id="delivery_address_err" />
        </div>
        <div class="flex flex-col">
            <x-app.input.label id="third_party_address"
                class="mb-1">{{ __('Third Party Address') }}</x-app.input.label>
            <x-app.input.input id="third_party_address" name="third_party_address" />
            <div id="third-party-address-list" class="mt-1">
                @php
                    $third_party_address =
                        isset($sale) && $sale->third_party_address != null
                            ? json_decode($sale->third_party_address)
                            : [];
                @endphp
                @foreach ($third_party_address as $key => $val)
                    <div class="flex items-start hover:bg-slate-100 child" data-idx={{ $key }}>
                        <p class="text-xs flex-1">{{ $val }}</p>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="flex flex-col">
            <x-app.input.label id="status" class="mb-1">{{ __('Status') }} <span
                    class="text-sm text-red-500">*</span></x-app.input.label>
            <x-app.input.select name="status" id="status" :hasError="$errors->has('status')">
                <option value="">{{ __('Select a status') }}</option>
                @if (isset($sale) && $sale->status == 4)
                    <option value="4" @selected(old('status', isset($sale) ? $sale->status : null) === 4)>{{ __('Pending Approval') }}</option>
                @elseif (isset($sale) && $sale->status == 7)
                    <option value="7" selected>{{ __('Rejected') }}</option>
                @elseif (isset($sale) && $sale->status == 5)
                    <option value="5" selected>{{ __('Approved') }}</option>
                @else
                    <option value="1" @selected(old('status', isset($sale) ? $sale->status : null) == 1)>{{ __('Active') }}</option>
                    <option value="0" @selected(old('status', isset($sale) ? $sale->status : null) === 0)>{{ __('Inactive') }}</option>
                @endif
            </x-app.input.select>
            <x-app.message.error id="status_err" />
        </div>
    </div>
</div>

@push('scripts')
    <script>
        INIT_EDIT = true
        CUSTOMERS = @json($customers ?? []);
        SEARCH_CUSTOMERS_URL = '{{ route('customer.get_by_keyword') }}'

        $('input[name="custom_date"]').daterangepicker(datepickerParam)
        $('input[name="custom_date"]').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD'));
        });

        $(document).ready(function() {
            buildCompanySelect2()

            if (SALE != null) {
                if (SALE.status == 4) {
                    $('select[name="status"]').attr('disabled', true)
                    $('select[name="status"]').attr('aria-disabled', true)

                }
                $('select[name="customer"]').trigger('change')
                $('select[name="billing_address"]').trigger('change')
                $('select[name="delivery_address"]').trigger('change')
            }

            INIT_EDIT = false
        })

        $('select[name="customer"]').on('change', function() {
            var customer_id = $(this).val()
            var element = CUSTOMERS[customer_id]
            $('input[name="attention_to"]').val(element.name)
            $('input[name="mobile"]').val(element.phone ?? element.mobile_number)

            if (INIT_EDIT) {
                $('select[name="sale"]').val(SALE.sale_id).trigger('change')
                $('select[name="payment_term"]').val(SALE.payment_term).trigger('change')
            } else if (INIT_EDIT == false && element.sales_agents.length === 1) {
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
                                false, INIT_EDIT == true ? loc.id ==
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
                                false, INIT_EDIT == true ? loc.id ==
                                SALE.delivery_address_id : loc.is_default)
                            $('select[name="delivery_address"]').append(opt)
                        }
                    }
                },
            });
        })
        $('body').on('focus', '[aria-labelledby="select2-customer-container"]', function() {
            $('select[name="customer"]').select2('open')
        })

        function hintClickedCallback(customer_id, customer_label) {
            $('#customer_label_hints').addClass('hidden')
            $('input[name="customer_label"]').val(customer_label)
            $('input[name="customer"]').val(customer_id)

            var element = CUSTOMERS[customer_id]

            $('input[name="attention_to"]').val(element.name)

            if (INIT_EDIT) {
                $('select[name="sale"]').val(SALE.sale_id).trigger('change')
                $('select[name="payment_term"]').val(SALE.payment_term).trigger('change')
            } else if (INIT_EDIT == false && element.sales_agents.length === 1) {
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
                    // Billing Address
                    $('select[name="billing_address"] option').remove()

                    var opt = new Option("{!! __('Select a billing address') !!}", null)
                    $('select[name="billing_address"]').append(opt)

                    for (let i = 0; i < res.locations.length; i++) {
                        const loc = res.locations[i];

                        if (loc.type !== 1 && loc.type !== 3) continue

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
                            addr,
                            loc.id, false, INIT_EDIT == true && loc.id ==
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
                            addr,
                            loc.id, false, INIT_EDIT == true && loc.id ==
                            SALE.delivery_address_id)
                        $('select[name="delivery_address"]').append(opt)
                    }
                },
            });
        }

        function buildCompanySelect2() {
            $('select[name="customer"]').select2({
                minimumInputLength: 1,
                placeholder: 'Search for a company',
                ajax: {
                    url: `${SEARCH_CUSTOMERS_URL}?is_edit=${SALE != null}`,
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
