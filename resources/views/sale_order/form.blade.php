@extends('layouts.app')

@section('content')
    <div class="mb-6 flex justify-between items-center">
        <div>
            <x-app.page-title
                url="{{ route('sale_order.index') }}">{{ isset($sale) ? __(isset($is_view) && $is_view == true ? 'View Sale Order - ' : 'Edit Sale Order - ') . $sale->sku : __('Create Sale Order') }}</x-app.page-title>
        </div>
        @if (!isset($sale))
            <div class="flex flex-col items-end">
                <span class="text-xs text-slate-600 leading-none">{{ __('Potential ID') }}</span>
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

            @if (
                !isset($is_view) ||
                    (isset($is_view) && $is_view == false) ||
                    !isset($has_pending_approval) ||
                    (isset($has_pending_approval) && $has_pending_approval == false))
                <div class="flex justify-end gap-x-4">
                    @if (isset($sale) && $sale->status == 2)
                        <span
                            class="text-sm text-slate-500 border border-slate-500 py-1 px-1.5 w-fit rounded">{{ __('Converted') }}</span>
                    @elseif (isset($sale) && $sale->status == 3)
                        <span
                            class="text-sm text-slate-500 border border-slate-500 py-1 px-1.5 w-fit rounded">{{ __('Cancelled') }}</span>
                    @else
                        <x-app.button.submit id="save-as-draft-btn"
                            class="!bg-blue-200">{{ __('Save As Draft') }}</x-app.button.submit>
                        <x-app.button.submit id="submit-btn">{{ __('Save and Update') }}</x-app.button.submit>
                    @endif
                </div>
            @endif
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        FORM_CAN_SUBMIT = true
        SALE = @json($sale ?? null);
        QUO = @json($quo ?? null);
        PAYMENT_EDITABLE_ONLY = @json($payment_editable_only ?? null);
        IS_VIEW = @json($is_view ?? null);
        IS_SALE_COORDINATOR_ONLY = @json($is_sale_coordinator_only ?? null);

        $(document).ready(function() {
            if (SALE == null) {
                getNextSku()
            } else if (PAYMENT_EDITABLE_ONLY == true || IS_VIEW == true || IS_SALE_COORDINATOR_ONLY == true) {
                $('#quotation-details-container input, #quotation-details-container select, #product-details-container select[name="product_id[]"], #product-details-container select[name="selling_price[]"], #product-details-container select[name="promotion[]"], #product-details-container input, #product-details-container textarea, #additional-remark-container input')
                    .attr('disabled', true)
                $('#quotation-details-container input, #quotation-details-container select, #product-details-container select[name="selling_price[]"], #product-details-container select[name="promotion[]"], #product-details-container .select2, #product-details-container input, #product-details-container textarea, #additional-remark-container input')
                    .addClass('!bg-gray-100')
                $('#quotation-details-container input, #additional-remark-container input, #product-details-container textarea, #product-details-container input[name="discount"], #product-details-container input[name="override_selling_price"], #product-details-container input[name="qty"], #product-details-container input[name="product_desc"]')
                    .parent().addClass('!bg-gray-100')
                $('#quotation-details-container .select2, #product-details-container .select2-selection--multiple')
                    .css('backgroundColor', '#eee')

                if (IS_SALE_COORDINATOR_ONLY == true) {
                    $('#payment-details-container input, #payment-details-container select').attr('disabled', true)
                    $('#payment-details-container input, #payment-details-container select').addClass('!bg-gray-100')
                    $('#payment-details-container input').parent().addClass('!bg-gray-100')
                    $('#payment-details-container .select2, #payment-details-container .select2-selection--multiple').css('backgroundColor', '#eee')
                }
            }
            // if (SALE != null && SALE.is_draft == true) {
            //     draftData = SALE.draft_data
            //     // Quotation details
            //     $('select[name="customer"]').val(draftData.customer).trigger('change')
            //     $('input[name="reference_input"]').val(draftData.reference)
            //     $('input[name="from"]').val(draftData.from)
            //     $('input[name="cc"]').val(draftData.cc)
            //     $('input[name="store"]').val(draftData.store)
            //     $('select[name="sale"]').val(draftData.sale).trigger('change')
            //     $('select[name="report_type"]').val(draftData.report_type).trigger('change')
            //     $('select[name="billing_address"]').val(draftData.billing_address).trigger('change')
            //     $('select[name="delivery_address"]').val(draftData.delivery_address).trigger('change')
            //     $('select[name="status"]').val(draftData.status).trigger('change')
            //     $('#new-billing-address input[name="address1"]').val(draftData.new_billing_address1)
            //     $('#new-billing-address input[name="address2"]').val(draftData.new_billing_address2)
            //     $('#new-billing-address input[name="address3"]').val(draftData.new_billing_address3)
            //     $('#new-billing-address input[name="address4"]').val(draftData.new_billing_address4)
            //     $('#new-delivery-address input[name="address1"]').val(draftData.new_delivery_address1)
            //     $('#new-delivery-address input[name="address2"]').val(draftData.new_delivery_address2)
            //     $('#new-delivery-address input[name="address3"]').val(draftData.new_delivery_address3)
            //     $('#new-delivery-address input[name="address4"]').val(draftData.new_delivery_address4)
            //     // Product details
            //     for (let i = 0; i < draftData.product_id.length; i++) {
            //         if (i != 0) {
            //             $('#add-item-btn').click()
            //         }
            //         $(`#product-details-container .items[data-id=${i+1}] select[name="product_id[]"]`).val(draftData
            //             .product_id[i]).trigger('change')
            //         $(`#product-details-container .items[data-id=${i+1}] input[name="qty"]`).val(draftData.qty[i])
            //         $(`#product-details-container .items[data-id=${i+1}] input[name="product_desc"]`).val(draftData
            //             .product_desc[i])
            //         $(`#product-details-container .items[data-id=${i+1}] input[name="discount"]`).val(draftData
            //             .discount[i])
            //         $(`#product-details-container .items[data-id=${i+1}] select[name="warranty_period[]"]`).val(
            //             draftData.warranty_period[i]).trigger('change')
            //         $(`#product-details-container .items[data-id=${i+1}] select[name="promotion[]"]`).val(draftData
            //             .promotion_id[i]).trigger('change')
            //         $(`#product-details-container .items[data-id=${i+1}] textarea[name="remark"]`).text(draftData
            //             .product_remark[i])
            //         if (draftData.foc[i] == 'true') {
            //             $(`#product-details-container .items[data-id=${i+1}] .foc-btns`).click()
            //         }
            //         if (draftData.selling_price[i] != null) {
            //             $(`#product-details-container .items[data-id=${i+1}] select[name="selling_price[]"]`).val(
            //                 draftData.selling_price[i]).trigger('change')
            //         } else {
            //             $(`#product-details-container .items[data-id=${i+1}] input[name="override_selling_price"]`)
            //                 .val(draftData.override_selling_price[i]).trigger('keyup')
            //         }
            //     }
            //     // Payment Details
            //     $('select[name="payment_method"]').val(draftData.payment_method).trigger('change')
            //     $('input[name="payment_remark"]').val(draftData.payment_remark)
            //     $('input[name="payment_due_date"]').val(draftData.payment_due_date)
            //     $('select[name="payment_term"]').val(draftData.payment_term).trigger('change')

            //     for (let i = 0; i < draftData.account_amount.length; i++) {
            //         if (i != 0) {
            //             $('#add-payment-amount-btn').click()
            //         } 
            //         $(`.payment-amounts[data-id=${i+1}] input[name="account_amount"]`).val(draftData.account_amount[i])
            //         $(`.payment-amounts[data-id=${i+1}] input[name="account_date"]`).val(draftData.account_date[i])
            //         $(`.payment-amounts[data-id=${i+1}] input[name="account_ref_no"]`).val(draftData.account_ref_no[i])
            //     }

            //     // Remarks
            //     $('#additional-remark-container input[name="remark_input"]').val(draftData.remark)
            // }
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
            let url = isSaveAsDraft == 'true' ? '{{ route('sale.save_as_draft') }}' :
                '{{ route('sale.upsert_details') }}'
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
                    'new_billing_addres1': $('#new-billing-address input[name="address1"]').val(),
                    'new_billing_addres2': $('#new-billing-address input[name="address2"]').val(),
                    'new_billing_addres3': $('#new-billing-address input[name="address3"]').val(),
                    'new_billing_addres4': $('#new-billing-address input[name="address4"]').val(),
                    'delivery_address': $('select[name="delivery_address"]').val() == 'null' ? null : $(
                        'select[name="delivery_address"]').val(),
                    'new_delivery_address1': $('#new-delivery-address input[name="address1"]').val(),
                    'new_delivery_address2': $('#new-delivery-address input[name="address2"]').val(),
                    'new_delivery_address3': $('#new-delivery-address input[name="address3"]').val(),
                    'new_delivery_address4': $('#new-delivery-address input[name="address4"]').val(),

                    'reference': $('input[name="reference_input"]').val(),
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
