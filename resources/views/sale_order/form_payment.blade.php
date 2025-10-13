@extends('layouts.app')
@section('title', 'Sale Order')

@section('content')
    <div class="mb-6 flex justify-between items-center">
        <x-app.page-title
            url="{{ route('sale_order.index') }}">{{ isset($sale) ? __('Edit Sale Order Payment - ') . $sale->sku : __('Create Sale Order Payment') }}</x-app.page-title>
    </div>
    @include('components.app.alert.parent')
    <div class="mb-2">
        @if (!isset($sale))
            <div class="flex flex-col">
                <span class="text-xs text-slate-600 leading-none">{{ __('Potential ID') }}</span>
                <span class="text-md font-semibold" id="next-sku">-</span>
            </div>
        @else
            <div class="flex gap-4">
                @if (isset($transfer_to) && $transfer_to != null)
                    <div class="flex flex-col">
                        <span class="text-xs text-slate-600 leading-none">{{ __('Transfer To') }}</span>
                        <span class="text-md font-semibold">{{ $transfer_to }}</span>
                    </div>
                @endif
                @if (isset($transfer_from) && $transfer_from != null)
                    <div class="flex flex-col">
                        <span class="text-xs text-slate-600 leading-none">{{ __('Transfer From') }}</span>
                        <span class="text-md font-semibold">{{ $transfer_from }}</span>
                    </div>
                @endif
            </div>
        @endif
    </div>

    <div class="grid gap-y-8">
        <form action="{{ isset($sale) ? route('sale.upsert_details', ['sale' => $sale]) : route('sale.upsert_details') }}"
            method="POST" enctype="multipart/form-data" class="flex flex-col gap-4">
            @csrf

            @include('sale_order.form_step.payment_details')

            @if (!isset($is_view) || (isset($is_view) && $is_view == false))
                @if (!isset($has_pending_approval) || (isset($has_pending_approval) && $has_pending_approval == false))
                    <div class="flex justify-end gap-x-4">
                        @if (isset($sale) && $sale->status == 2)
                            <span
                                class="text-sm text-slate-500 border border-slate-500 py-1 px-1.5 w-fit rounded">{{ __('Converted') }}</span>
                        @elseif (isset($sale) && $sale->status == 3)
                            <span
                                class="text-sm text-slate-500 border border-slate-500 py-1 px-1.5 w-fit rounded">{{ __('Cancelled') }}</span>
                        @else
                            <x-app.button.submit id="submit-btn">{{ __('Save and Update') }}</x-app.button.submit>
                        @endif
                    </div>
                @endif
            @endif
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        FORM_CAN_SUBMIT = true
        SALE = @json($sale ?? null);
        IS_VIEW = @json($is_view ?? null);

        $('#save-as-draft-btn, #submit-btn').on('click', function() {
            $(this).attr('data-triggered', true)
        })

        $('form').on('submit', function(e) {
            e.preventDefault()

            if (!FORM_CAN_SUBMIT) return

            FORM_CAN_SUBMIT = false

            $(`form #submit-btn`).text('Updating')
            $(`form #submit-btn`).removeClass(
                '!bg-blue-200 bg-yellow-400 shadow')
            $('.err_msg').addClass('hidden') // Remove error messages
            // Prepare payment amounts 
            let accountAmount = []
            let accountDate = []
            let accountRefNo = []
            $('#payment-amounts-container .payment-amounts').each(function(i, obj) {
                accountAmount.push($(this).find('input[name="account_amount"]').val())
                accountDate.push($(this).find('input[name="account_date"]').val())
                accountRefNo.push($(this).find('input[name="account_ref_no"]').val())
            })
            // Submit
            let url = '{{ route('sale.upsert_pay_details') }}'

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: url,
                type: 'POST',
                data: {
                    'sale_id': SALE != null ? SALE.id : null,
                    'payment_term': $('select[name="payment_term"]').val(),
                    'payment_method': $('select[name="payment_method"]').val(),
                    'payment_due_date': $('input[name="payment_due_date"]').val(),
                    'payment_remark': $('input[name="payment_remark"]').val(),
                    'by_pass_conversion': $('input[name="by_pass_conversion"]').val(),
                    'account_amount': accountAmount,
                    'account_date': accountDate,
                    'account_ref_no': accountRefNo,
                },
                success: function(res) {
                    if (res.data != undefined && res.data.sale) {
                        SALE = res.data.sale
                    }

                    if (res.data != undefined) {
                        let product_ids = res.data.product_ids
                        $('#product-details-container .items').each(function(i, obj) {
                            $(this).attr('data-product-id', product_ids[i])
                        })
                    }

                    if (res.data != undefined && res.data.can_by_pass_conversion) {
                        $('#by-pass-conversion-hint').removeClass('hidden')
                    }

                    $(`form #submit-btn`).text(
                        'Updated')
                    $(`form  #submit-btn`)
                        .addClass('bg-green-400 shadow')

                    setTimeout(() => {
                        window.location.href = '{{ route('sale_order.index') }}'
                    }, 1000);
                },
                error: function(err) {
                    setTimeout(() => {
                        if (err.status == StatusCodes.UNPROCESSABLE_ENTITY || err.status ==
                            StatusCodes.BAD_REQUEST) {
                            let errors = err.responseJSON.errors

                            for (const key in errors) {
                                if (key.includes('err_msg')) {
                                    $(`#account_err`).find('p').text(errors[key])
                                    $(`#account_err`).removeClass('hidden')
                                } else if (key.includes('new_delivery_')) {
                                    $(`#new-delivery-address #${key.replace('new_delivery_', '') }_err`)
                                        .find('p').text(errors[key])
                                    $(`#new-delivery-address #${key.replace('new_delivery_', '') }_err`)
                                        .removeClass('hidden')
                                } else if (key.includes('new_billing_')) {
                                    $(`#new-billing-address #${key.replace('new_billing_', '') }_err`)
                                        .find('p').text(errors[key])
                                    $(`#new-billing-address #${key.replace('new_billing_', '') }_err`)
                                        .removeClass('hidden')
                                } else if (key.includes('account_')) {
                                    let field = key.split('.')[0]
                                    let idx = key.split('.')[1]
                                    idx++
                                    $(`.payment-amounts[data-id="${idx}"] #${field}_err`).find(
                                        'p').text(
                                        errors[key])
                                    $(`.payment-amounts[data-id="${idx}"] #${field}_err`)
                                        .removeClass(
                                            'hidden')
                                } else if (key.includes('.')) {
                                    let field = key.split('.')[0]
                                    let idx = key.split('.')[1]
                                    idx++
                                    $(`.items[data-id="${idx}"] #${field}_err`).find('p').text(
                                        errors[key])
                                    $(`.items[data-id="${idx}"] #${field}_err`).removeClass(
                                        'hidden')
                                } else {
                                    $(`#${key}_err`).find('p').text(errors[key])
                                    $(`#${key}_err`).removeClass('hidden')
                                }
                            }
                        } else if (err.status == StatusCodes.BAD_REQUEST) {
                            $(`.items #product_serial_no_err`).find('p').text(err.responseJSON
                                .product_serial_no)
                            $(`.items #product_serial_no_err`).removeClass('hidden')
                        }

                        $('form #submit-btn').text('Save and Update')
                        $('form #submit-btn').addClass('bg-yellow-400 shadow')

                        FORM_CAN_SUBMIT = true
                    }, 300);
                },
            });
        })
    </script>
@endpush
