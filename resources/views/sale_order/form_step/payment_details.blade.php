<div class="bg-white p-4 border rounded-md" id="payment-details-container">
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
            <x-app.input.label id="payment_method" class="mb-1">{{ __('Payment Method') }} </x-app.input.label>
            <x-app.input.select2 name="payment_method" id="payment_method" :hasError="$errors->has('payment_method')"
                placeholder="{{ __('Select a method') }}">
                <option value=""></option>
                @foreach ($payment_methods as $method)
                    <option value="{{ $method->id }}" @selected(old('payment_method', isset($sale) ? $sale->payment_method : null) == $method->id)>{{ $method->name }}</option>
                @endforeach
                </x-app.input.selec2t>
                <x-app.message.error id="payment_method_err" />
        </div>
        <div class="flex flex-col hidden" id="payment-term-container">
            <x-app.input.label id="payment_term" class="mb-1">{{ __('Payment Term') }} <span
                    class="text-sm text-red-500">*</span></x-app.input.label>
            <x-app.input.select2 name="payment_term" id="payment_term" :hasError="$errors->has('payment_term')"
                placeholder="{{ __('Select a term') }}">
                <option value=""></option>
                @foreach ($credit_terms as $term)
                    <option value="{{ $term->id }}" @selected(old('payment_term', isset($sale) ? $sale->payment_term : null) == $term->id)>{{ $term->name }}</option>
                @endforeach
            </x-app.input.select2>
            <x-app.message.error id="payment_term_err" />
        </div>
        <div class="flex flex-col">
            <x-app.input.label id="payment_due_date" class="mb-1">{{ __('Payment Due Date') }} </x-app.input.label>
            <x-app.input.input name="payment_due_date" id="payment_due_date" :hasError="$errors->has('payment_due_date')"
                value="{{ isset($sale) ? $sale->payment_due_date : null }}" />
            <x-app.message.error id="payment_due_date_err" />
        </div>
        <div class="flex flex-col">
            <x-app.input.label id="payment_remark" class="mb-1">{{ __('Payment Remark') }}</x-app.input.label>
            <x-app.input.input name="payment_remark" id="payment_remark" :hasError="$errors->has('payment_remark')"
                value="{{ isset($sale) ? $sale->payment_remark : null }}" />
            <x-app.message.error id="payment_remark_err" />
        </div>
    </div>
    @if ($can_payment_amount)
        {{-- Payment Amounts --}}
        <div id="payment-amounts-container" class="pt-4 border-t border-slate-200">
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-8 w-full p-4 hover:bg-slate-50 relative group hidden"
                id="payment-amount-template">
                <input type="hidden" name="existing_payment_id" class="existing-payment-id" value="">
                <div class="hidden col-span-full" id="approval-status-container">
                    <span id="pending-status"
                        class="hidden border rounded border-slate-500 text-slate-500 text-sm font-medium px-1 py-0.5">{{ __('Pending Approval') }}</span>
                    <span id="rejected-status"
                        class="hidden border rounded border-red-600 text-red-600 text-sm font-medium px-1 py-0.5">{{ __('Rejected') }}</span>
                    <span class="rejection-remark hidden text-red-600 text-xs ml-2">
                        <span class="remark-text"></span>
                    </span>
                </div>
                <button type="button"
                    class="bg-rose-400 p-2 rounded-full absolute top-[-5px] right-[-5px] hidden group-hover:block delete-payment-amount-btns"
                    title="Delete Payment Amount">
                    <svg class="h-3 w-3 fill-white" xmlns="http://www.w3.org/2000/svg" id="Layer_1"
                        data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512">
                        <path
                            d="M13.93,12L21.666,2.443c.521-.644,.422-1.588-.223-2.109-.645-.522-1.588-.421-2.109,.223l-7.334,9.06L4.666,.557c-1.241-1.519-3.56,.357-2.332,1.887l7.736,9.557L2.334,21.557c-.521,.644-.422,1.588,.223,2.109,.64,.519,1.586,.424,2.109-.223l7.334-9.06,7.334,9.06c.524,.647,1.47,.742,2.109,.223,.645-.521,.744-1.466,.223-2.109l-7.736-9.557Z" />
                    </svg>
                </button>
                <div class="flex flex-col">
                    <x-app.input.label id="account_payment_method" class="mb-1">{{ __('Payment Method') }}</x-app.input.label>
                    <x-app.input.select name="account_payment_method" id="account_payment_method" :hasError="$errors->has('account_payment_method')" class="account-payment-method-select">
                        <option value="">{{ __('Select a method') }}</option>
                        @foreach ($payment_methods as $method)
                            <option value="{{ $method->id }}">{{ $method->name }}</option>
                        @endforeach
                    </x-app.input.select>
                    <x-app.message.error id="account_payment_method_err" />
                </div>
                <div class="flex flex-col account-payment-term-container hidden">
                    <x-app.input.label id="account_payment_term" class="mb-1">{{ __('Payment Term') }}</x-app.input.label>
                    <x-app.input.select name="account_payment_term" id="account_payment_term" :hasError="$errors->has('account_payment_term')" class="account-payment-term-select">
                        <option value="">{{ __('Select a term') }}</option>
                        @foreach ($credit_terms as $term)
                            <option value="{{ $term->id }}">{{ $term->name }}</option>
                        @endforeach
                    </x-app.input.select>
                    <x-app.message.error id="account_payment_term_err" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="account_amount" class="mb-1">{{ __('Amount') }}</x-app.input.label>
                    <x-app.input.input name="account_amount" id="account_amount" :hasError="$errors->has('account_amount')" class="int-input" />
                    <x-app.message.error id="account_amount_err" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="account_date" class="mb-1">{{ __('Date') }}</x-app.input.label>
                    <x-app.input.input name="account_date" id="account_date" :hasError="$errors->has('account_date')" />
                    <x-app.message.error id="account_date_err" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="account_ref_no" class="mb-1">{{ __('Reference No') }}</x-app.input.label>
                    <x-app.input.input name="account_ref_no" id="account_ref_no" :hasError="$errors->has('account_ref_no')" />
                    <x-app.message.error id="account_ref_no_err" />
                </div>
            </div>
        </div>
        <x-app.message.error id="account_err" />
        <!-- Add Items -->
        <div class="flex justify-end px-4 mt-4">
            <button type="button"
                class="bg-yellow-400 rounded-md py-1.5 px-3 flex items-center gap-x-2 transition duration-300 hover:bg-yellow-300 hover:shadow"
                id="add-payment-amount-btn">
                <svg class="h-3 w-3" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                    version="1.1" id="Capa_1" x="0px" y="0px" viewBox="0 0 512 512"
                    style="enable-background:new 0 0 512 512;" xml:space="preserve" width="512" height="512">
                    <path
                        d="M480,224H288V32c0-17.673-14.327-32-32-32s-32,14.327-32,32v192H32c-17.673,0-32,14.327-32,32s14.327,32,32,32h192v192   c0,17.673,14.327,32,32,32s32-14.327,32-32V288h192c17.673,0,32-14.327,32-32S497.673,224,480,224z" />
                </svg>
                <span class="text-sm">{{ __('Add Payment Record') }}</span>
            </button>
        </div>
    @endif
</div>

@push('scripts')
    <script>
        CREDIT_PAYMENT_METHOD_IDS = @json($credit_payment_method_ids ?? []);
        PAYMENT_AMOUNT_COUNT = 0

        $('input[name="payment_due_date"]').daterangepicker(datepickerParam)
        $('input[name="payment_due_date"]').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD'));
        });

        $(document).ready(function() {
            if (SALE != null) {
                $('select[name="payment_method"]').trigger('change')
                $('select[name="payment_term"]').val(SALE.payment_term).trigger('change')

                if (SALE.can_by_pass_conversion) $('#by-pass-conversion-hint').removeClass('hidden')

                if (SALE.payment_amounts.length <= 0) {
                    $('#add-payment-amount-btn').click()
                } else {
                    for (let i = 0; i < SALE.payment_amounts.length; i++) {
                        $('#add-payment-amount-btn').click()

                        let $row = $(`.payment-amounts[data-id="${i+1}"]`)
                        $row.find('select[name="account_payment_method"]').val(SALE.payment_amounts[i].payment_method)
                        $row.find('select[name="account_payment_method"]').trigger('change')
                        $row.find('select[name="account_payment_term"]').val(SALE.payment_amounts[i].payment_term)
                        $row.find('input[name="account_amount"]').val(SALE.payment_amounts[i].amount)
                        $row.find('input[name="account_date"]').val(SALE.payment_amounts[i].date)
                        $row.find('input[name="account_ref_no"]').val(SALE.payment_amounts[i].reference_number)

                        // Set existing payment ID
                        $row.find('.existing-payment-id').val(SALE.payment_amounts[i].id)

                        // Check for pending approval status (2 = pending edit, 3 = pending delete)
                        if (SALE.payment_amounts[i].approval_status == 2 || SALE.payment_amounts[i].approval_status == 3) {
                            $row.find('input, select').prop('disabled', true)
                            $row.find('#approval-status-container').removeClass('hidden')
                            $row.find('#pending-status').removeClass('hidden')
                            $row.find('.delete-payment-amount-btns').removeClass('group-hover:block').addClass('hidden')
                        }

                        // Show rejection remark if exists
                        if (SALE.payment_amounts[i].approval && SALE.payment_amounts[i].approval.reject_remark) {
                            $row.find('#approval-status-container').removeClass('hidden')
                            $row.find('#rejected-status').removeClass('hidden')
                            $row.find('.rejection-remark').removeClass('hidden')
                            $row.find('.rejection-remark .remark-text').text(SALE.payment_amounts[i].approval.reject_remark)
                        }
                    }
                }
            } else {
                $('#add-payment-amount-btn').click()
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
        $('#add-payment-amount-btn').on('click', function() {
            let clone = $('#payment-amount-template')[0].cloneNode(true);

            PAYMENT_AMOUNT_COUNT++
            $(clone).attr('data-id', PAYMENT_AMOUNT_COUNT)
            $(clone).find('.delete-payment-amount-btns').attr('data-id', PAYMENT_AMOUNT_COUNT)
            $(clone).addClass('payment-amounts')
            $(clone).removeClass('hidden')
            $(clone).removeAttr('id')

            $(clone).find('input[name="account_date"]').daterangepicker(datepickerParam)
            $(clone).find('input[name="account_date"]').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD'));
            });

            // Handle payment term visibility when payment method changes
            $(clone).find('select[name="account_payment_method"]').on('change', function() {
                let val = $(this).val()
                let container = $(this).closest('.payment-amounts').find('.account-payment-term-container')

                if (CREDIT_PAYMENT_METHOD_IDS.includes(parseInt(val))) {
                    container.removeClass('hidden')
                } else {
                    container.addClass('hidden')
                    container.find('select[name="account_payment_term"]').val('')
                }
            })

            $('#payment-amounts-container').append(clone)
        })
        $('body').on('click', '.delete-payment-amount-btns', function() {
            let id = $(this).data('id')

            $(`.payment-amounts[data-id="${id}"]`).remove()
        })
    </script>
@endpush
