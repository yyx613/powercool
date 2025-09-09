@extends('layouts.app')
@section('title', 'Cash Sale')

@section('content')
    <div class="mb-6 flex justify-between items-center">
        <x-app.page-title
            url="{{ route('cash_sale.index') }}">{{ isset($sale) ? __(isset($is_view) && $is_view == true ? 'View Cash Sale - ' : 'Edit Cash Sale - ') . $sale->sku : __('Create Cash Sale') }}</x-app.page-title>
    </div>
    @include('components.app.alert.parent')
    <div class="mb-2">
        @if (!isset($sale))
            <div class="flex flex-col">
                <span class="text-xs text-slate-600 leading-none">{{ __('Potential ID') }}</span>
                <span class="text-md font-semibold" id="next-sku">-</span>
            </div>
        @endif
    </div>

    <div class="grid gap-y-8">
        <form action="{{ isset($sale) ? route('sale.upsert_details', ['sale' => $sale]) : route('sale.upsert_details') }}"
            method="POST" enctype="multipart/form-data" class="flex flex-col gap-4">
            @csrf

            @include('cash_sale.form_step.quotation_details')
            @include('cash_sale.form_step.product_details')
            @include('cash_sale.form_step.payment_details')
            @include('cash_sale.form_step.remarks')

            <div class="flex justify-end gap-x-4">
                <x-app.button.submit id="save-as-draft-btn"
                    class="!bg-blue-200">{{ __('Save As Draft') }}</x-app.button.submit>
                <x-app.button.submit id="submit-btn">{{ __('Save and Update') }}</x-app.button.submit>
            </div>
        </form>
    </div>

    <x-app.modal.to-production-modal />
@endsection

@push('scripts')
    <script>
        FORM_CAN_SUBMIT = true
        SALE = @json($sale ?? null);
        QUO = @json($quo ?? null);
        CAN_EDIT_PAYMENT = @json($can_edit_payment ?? null);
        IS_VIEW = @json($is_view ?? null);

        $(document).ready(function() {
            getNextSku()
        })

        $('#save-as-draft-btn, #submit-btn').on('click', function() {
            $(this).attr('data-triggered', true)
        })

        $('form').on('submit', function(e) {
            e.preventDefault()

            if (!FORM_CAN_SUBMIT) return

            FORM_CAN_SUBMIT = false
            isSaveAsDraft = $('#save-as-draft-btn').attr('data-triggered')

            $(`form ${isSaveAsDraft == 'true' ? '#save-as-draft-btn' : '#submit-btn'}`).text('Updating')
            $(`form ${isSaveAsDraft == 'true' ? '#save-as-draft-btn' : '#submit-btn'}`).removeClass(
                '!bg-blue-200 bg-yellow-400 shadow')
            $('.err_msg').addClass('hidden') // Remove error messages
            // Prepare products details
            let prodOrderId = []
            let prodId = []
            let sequence = []
            let customizeProd = []
            let prodDesc = []
            let qty = []
            let foc = []
            let withSST = []
            let SSTAmount = []
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
                sequence.push($(this).data('sequence'))
                customizeProd.push($(this).find('input[name="customize_product"]').val())
                prodDesc.push($(this).find('input[name="product_desc"]').val())
                qty.push($(this).find('input[name="qty"]').val())
                foc.push($(this).find('.foc-btns').data('is-foc'))
                withSST.push($(this).find('.sst-btns').data('with-sst'))
                SSTAmount.push($(this).find('input[name="sst"]').val() == undefined ? null : $(this).find(
                    'input[name="sst"]').val().replaceAll(',', ''))
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
            let url = isSaveAsDraft == 'true' ? '{{ route('sale.save_as_draft') }}' :
                '{{ route('sale.upsert_details') }}'
            url = `${url}?type=cash-sale`

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: url,
                type: 'POST',
                data: {
                    'sale_id': SALE != null ? SALE.id : null,
                    'quo_id': QUO != null ? QUO.id : null,
                    'custom_date': $('input[name="custom_date"]').val(),
                    'sale': $('select[name="sale"]').val(),
                    'customer': $('select[name="customer"]').val(),
                    'billing_address': $('select[name="billing_address"]').val() == 'null' ? null : $(
                        'select[name="billing_address"]').val(),
                    // 'new_billing_addres1': $('#new-billing-address input[name="address1"]').val(),
                    // 'new_billing_addres2': $('#new-billing-address input[name="address2"]').val(),
                    // 'new_billing_addres3': $('#new-billing-address input[name="address3"]').val(),
                    // 'new_billing_addres4': $('#new-billing-address input[name="address4"]').val(),
                    'delivery_address': $('select[name="delivery_address"]').val() == 'null' ? null : $(
                        'select[name="delivery_address"]').val(),
                    // 'new_delivery_address1': $('#new-delivery-address input[name="address1"]').val(),
                    // 'new_delivery_address2': $('#new-delivery-address input[name="address2"]').val(),
                    // 'new_delivery_address3': $('#new-delivery-address input[name="address3"]').val(),
                    // 'new_delivery_address4': $('#new-delivery-address input[name="address4"]').val(),

                    'reference': $('input[name="reference_input"]').val(),
                    'status': $('select[name="status"]').val(),
                    'report_type': $('select[name="report_type"]').val(),

                    'product_order_id': prodOrderId,
                    'product_id': prodId,
                    'sequence': sequence,
                    'customize_product': customizeProd,
                    'product_desc': prodDesc,
                    'qty': qty,
                    'foc': foc,
                    'with_sst': withSST,
                    'sst_amount': SSTAmount,
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
                    
                    'remark': $('#additional-remark-container input[name="remark_input"]').val(),
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

                    $(`form ${isSaveAsDraft == 'true' ? '#save-as-draft-btn' : '#submit-btn' }`).text(
                        'Updated')
                    $(`form ${isSaveAsDraft == 'true' ? '#save-as-draft-btn' : '#submit-btn' }`)
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

                        if (isSaveAsDraft == 'true') {
                            $('form #save-as-draft-btn').text('Save As Draft')
                            $('form #save-as-draft-btn').addClass('!bg-blue-200 shadow')
                        } else {
                            $('form #submit-btn').text('Save and Update')
                            $('form #submit-btn').addClass('bg-yellow-400 shadow')
                        }

                        FORM_CAN_SUBMIT = true
                    }, 300);
                },
            });
        })

        function getNextSku(selected_product = false) {
            let url = '{{ route('quotation.get_next_sku') }}'
            url = `${url}?type=cash-sale&is_hi_ten=${selected_product}`

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
