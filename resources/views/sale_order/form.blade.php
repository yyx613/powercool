@extends('layouts.app')

@section('content')
    <div class="mb-6 flex justify-between items-center">
        <div>
            <x-app.page-title
                url="{{ route('sale_order.index') }}">{{ isset($sale) ? __('Edit Sale Order - ') . $sale->sku : __('Create Sale Order') }}</x-app.page-title>
        </div>
        @if (!isset($sale))
            <div class="flex flex-col items-end">
                <span class="text-xs text-slate-600 leading-none">{{ __('Next ID') }}</span>
                <span class="text-md font-semibold" id="next-sku">-</span>
            </div>
        @endif
    </div>

    <div class="grid gap-y-8">
        <form action="{{ isset($sale) ? route('sale.upsert_details', ['sale' => $sale]) : route('sale.upsert_details') }}"
            method="POST" enctype="multipart/form-data" class="flex flex-col gap-4">
            @csrf

            @include('sale_order.form_step.quotation_details')
            @include('sale_order.form_step.product_details')
            @include('sale_order.form_step.payment_details')
            @include('sale_order.form_step.remarks')

            <div class="flex justify-end">
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
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        FORM_CAN_SUBMIT = true
        SALE = @json($sale ?? null);
        QUO = @json($quo ?? null);
        PAYMENT_EDITABLE_ONLY = @json($payment_editable_only ?? null);

        $(document).ready(function() {
            if (SALE == null) {
                getNextSku()

            } else if (PAYMENT_EDITABLE_ONLY == true) {
                $('#quotation-details-container input, #quotation-details-container select, #product-details-container select[name="product_id[]"], #product-details-container select[name="selling_price[]"], #product-details-container select[name="promotion[]"], #product-details-container input, #product-details-container textarea, #additional-remark-container input').attr('disabled', true)
                $('#quotation-details-container input, #quotation-details-container select, #product-details-container select[name="selling_price[]"], #product-details-container select[name="promotion[]"], #product-details-container .select2, #product-details-container input, #product-details-container textarea, #additional-remark-container input').addClass('!bg-gray-100')
                $('#quotation-details-container input, #additional-remark-container input, #product-details-container textarea, #product-details-container input[name="discount"], #product-details-container input[name="override_selling_price"], #product-details-container input[name="qty"]').parent().addClass('!bg-gray-100')
                $('#quotation-details-container .select2, #product-details-container .select2-selection--multiple').css('backgroundColor', '#eee')
            }
        })

        $('form').on('submit', function(e) {
            e.preventDefault()

            if (!FORM_CAN_SUBMIT) return

            FORM_CAN_SUBMIT = false

            $('form #submit-btn').text('Updating')
            $('form #submit-btn').removeClass('bg-yellow-400 shadow')
            $('.err_msg').addClass('hidden') // Remove error messages
            // Prepare products details
            let prodOrderId = []
            let prodId = []
            let customizeProd = []
            let prodDesc = []
            let qty = []
            let foc = []
            let uom = []
            let sellingPrice = []
            let unitPrice = []
            let promo = []
            let discount = []
            let prodSerialNo = []
            let warrantyPeriod = []
            let remark = []
            let overrideSellingPrice = []
            $('#product-details-container .items').each(function(i, obj) {
                prodOrderId.push($(this).data('product-id') ?? null)
                prodId.push($(this).find('select[name="product_id[]"]').val())
                customizeProd.push($(this).find('input[name="customize_product"]').val())
                prodDesc.push($(this).find('input[name="product_desc"]').val())
                qty.push($(this).find('input[name="qty"]').val())
                foc.push($(this).find('.foc-btns').data('is-foc'))
                uom.push($(this).find('input[name="uom"]').val())
                sellingPrice.push($(this).find('select[name="selling_price[]"]').val())
                unitPrice.push($(this).find('input[name="unit_price[]"]').val())
                promo.push($(this).find('select[name="promotion[]"]').val())
                discount.push($(this).find('input[name="discount"]').val())
                remark.push($(this).find('textarea[name="remark"]').val())
                overrideSellingPrice.push($(this).find('input[name="override_selling_price"]').val())
                if ($(this).find('select[name="product_serial_no[]"]').val().length <= 0) {
                    prodSerialNo.push(null)
                } else {
                    prodSerialNo.push($(this).find('select[name="product_serial_no[]"]').val())
                }
                warrantyPeriod.push($(this).find('select[name="warranty_period[]"]').val())
            })
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
            let url = '{{ route('sale.upsert_details') }}'
            url = `${url}?type=so`

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: url,
                type: 'POST',
                data: {
                    'sale_id': SALE != null ? SALE.id : null,
                    'quo_id': QUO != null ? QUO.id : null,
                    'sale': $('select[name="sale"]').val(),
                    'customer': $('select[name="customer"]').val(),
                    'billing_address': $('select[name="billing_address"]').val() == 'null' ? null : $(
                        'select[name="billing_address"]').val(),
                    'new_billing_address': $('#new-billing-address input[name="address"]').val(),
                    'new_billing_city': $('#new-billing-address input[name="city"]').val(),
                    'new_billing_state': $('#new-billing-address input[name="state"]').val(),
                    'new_billing_zip_code': $('#new-billing-address input[name="zip_code"]').val(),
                    'delivery_address': $('select[name="delivery_address"]').val() == 'null' ? null : $(
                        'select[name="delivery_address"]').val(),
                    'new_delivery_address': $('#new-delivery-address input[name="address"]').val(),
                    'new_delivery_city': $('#new-delivery-address input[name="city"]').val(),
                    'new_delivery_state': $('#new-delivery-address input[name="state"]').val(),
                    'new_delivery_zip_code': $('#new-delivery-address input[name="zip_code"]').val(),

                    'reference': $('input[name="reference"]').val(),
                    'status': $('select[name="status"]').val(),
                    'report_type': $('select[name="report_type"]').val(),

                    'product_order_id': prodOrderId,
                    'product_id': prodId,
                    'customize_product': customizeProd,
                    'product_desc': prodDesc,
                    'qty': qty,
                    'foc': foc,
                    'uom': uom,
                    'selling_price': sellingPrice,
                    'unit_price': unitPrice,
                    'promotion_id': promo,
                    'product_serial_no': prodSerialNo,
                    'warranty_period': warrantyPeriod,
                    'discount': discount,
                    'product_remark': remark,
                    'override_selling_price': overrideSellingPrice,

                    'payment_term': $('select[name="payment_term"]').val(),
                    'payment_method': $('select[name="payment_method"]').val(),
                    'payment_due_date': $('input[name="payment_due_date"]').val(),
                    'payment_remark': $('input[name="payment_remark"]').val(),
                    'by_pass_conversion': $('input[name="by_pass_conversion"]').val(),
                    'account_amount': accountAmount,
                    'account_date': accountDate,
                    'account_ref_no': accountRefNo,

                    // 'driver': $('select[name="driver"]').val(),
                    // 'delivery_date': $('input[name="delivery_date"]').val(),
                    // 'delivery_time': $('input[name="delivery_time"]').val(),
                    // 'delivery_instruction': $('input[name="delivery_instruction"]').val(),
                    // 'delivery_address': $('select[name="delivery_address"]').val() === 'null' ? null : $('select[name="delivery_address"]').val(),
                    // 'delivery_status': $('select[name="delivery_status"]').val(),

                    'remark': $('#additional-remark-container textarea[name="remark"]').val(),
                },
                success: function(res) {
                    if (res.data.sale) {
                        SALE = res.data.sale
                    }

                    let product_ids = res.data.product_ids
                    $('#product-details-container .items').each(function(i, obj) {
                        $(this).attr('data-product-id', product_ids[i])
                    })

                    if (res.data.can_by_pass_conversion) {
                        $('#by-pass-conversion-hint').removeClass('hidden')
                    }

                    setTimeout(() => {
                        $('form #submit-btn').text('Updated')
                        $('form #submit-btn').addClass('bg-green-400 shadow')

                        setTimeout(() => {
                            window.location.href = '{{ route('sale_order.index') }}'
                            // $('form #submit-btn').text('Save and Update')
                            // $('form #submit-btn').removeClass('bg-green-400')
                            // $('form #submit-btn').addClass('bg-yellow-400 shadow')

                            // FORM_CAN_SUBMIT = true
                        }, 1000);
                    }, 300);
                },
                error: function(err) {
                    setTimeout(() => {
                        if (err.status == StatusCodes.UNPROCESSABLE_ENTITY || err.status ==
                            StatusCodes.BAD_REQUEST) {
                            let errors = err.responseJSON.errors

                            for (const key in errors) {
                                if (key.includes('account_err_msg')) {
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

        function getNextSku(selected_product = false) {
            let url = '{{ route('quotation.get_next_sku') }}'
            url = `${url}?type=so&is_hi_ten=${selected_product}`

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: url,
                type: 'GET',
                success: function(res) {
                    $('#next-sku').text(res)
                },
            })
        }
    </script>
@endpush
