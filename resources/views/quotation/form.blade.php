@extends('layouts.app')

@section('content')
    <div class="mb-6 flex justify-between items-center">
        <div>
            <x-app.page-title
                url="{{ route('quotation.index') }}">{{ isset($sale) ? __('Edit Quotation - ') . $sale->sku : __('Create Quotation') }}</x-app.page-title>
        </div>
        @if (!isset($sale))
            <div class="flex flex-col items-end">
                <span class="text-xs text-slate-600 leading-none">{{ __('Potential ID') }}</span>
                <span class="text-md font-semibold" id="next-sku">-</span>
            </div>
        @endif
    </div>
    @include('components.app.alert.parent')
    <div class="grid gap-y-8">
        <form action="{{ isset($sale) ? route('sale.upsert_details', ['sale' => $sale]) : route('sale.upsert_details') }}"
            method="POST" enctype="multipart/form-data" class="flex flex-col gap-4">
            @csrf

            @include('quotation.form_step.quotation_details')
            @include('quotation.form_step.product_details')
            @include('quotation.form_step.remarks')

            <div class="flex justify-end gap-x-4">
                @if (isset($sale) && $sale->status == 2)
                    <span
                        class="text-sm text-slate-500 border border-slate-500 py-1 px-1.5 w-fit rounded">{{ __('Converted') }}</span>
                @else
                    <x-app.button.submit id="save-as-draft-btn"
                        class="!bg-blue-200">{{ __('Save As Draft') }}</x-app.button.submit>
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

        $(document).ready(function() {
            if (SALE == null) {
                getNextSku()
            }
            if (SALE.is_draft == true) {
                draftData = SALE.draft_data
                // Quotation details
                $('input[name="open_until"]').val(draftData.open_until)
                $('select[name="customer"]').val(draftData.customer).trigger('change')
                $('input[name="reference"]').val(draftData.reference)
                $('input[name="from"]').val(draftData.from)
                $('input[name="cc"]').val(draftData.cc)
                $('input[name="store"]').val(draftData.store)
                $('select[name="sale"]').val(draftData.sale).trigger('change')
                $('select[name="report_type"]').val(draftData.report_type).trigger('change')
                $('select[name="billing_address"]').val(draftData.billing_address).trigger('change')
                $('select[name="status"]').val(draftData.status).trigger('change')
                $('#new-billing-address input[name="address1"]').val(draftData.new_billing_address1)
                $('#new-billing-address input[name="address2"]').val(draftData.new_billing_address2)
                $('#new-billing-address input[name="address3"]').val(draftData.new_billing_address3)
                $('#new-billing-address input[name="address4"]').val(draftData.new_billing_address4)
                // Product details
                for (let i = 0; i < draftData.product_id.length; i++) {
                    if (i != 0) {
                        $('#add-item-btn').click()
                    }
                    $(`#product-details-container .items[data-id=${i+1}] select[name="product_id[]"]`).val(draftData.product_id[i]).trigger('change')
                    $(`#product-details-container .items[data-id=${i+1}] input[name="qty"]`).val(draftData.qty[i])
                    $(`#product-details-container .items[data-id=${i+1}] input[name="product_desc"]`).val(draftData.product_desc[i])
                    $(`#product-details-container .items[data-id=${i+1}] input[name="discount"]`).val(draftData.discount[i])
                    $(`#product-details-container .items[data-id=${i+1}] select[name="warranty_period[]"]`).val(draftData.warranty_period[i]).trigger('change')
                    $(`#product-details-container .items[data-id=${i+1}] select[name="promotion[]"]`).val(draftData.promotion_id[i]).trigger('change')
                    $(`#product-details-container .items[data-id=${i+1}] textarea[name="remark"]`).text(draftData.product_remark[i])
                    if (draftData.foc[i] == 'true') {
                        $(`#product-details-container .items[data-id=${i+1}] .foc-btns`).click()
                    }
                    if (draftData.selling_price[i] != null) {
                        $(`#product-details-container .items[data-id=${i+1}] select[name="selling_price[]"]`).val(draftData.selling_price[i]).trigger('change')
                    } else {
                        $(`#product-details-container .items[data-id=${i+1}] input[name="override_selling_price"]`).val(draftData.override_selling_price[i]).trigger('keyup')
                    }
                }
                // Remarks
                $('#additional-remark-container textarea[name="remark"]').text(draftData.remark)
            }
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
            // Prepare data
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
                if ($(this).find('select[name="product_serial_no[]"]').val() == null || $(this).find(
                        'select[name="product_serial_no[]"]').val().length <= 0) {
                    prodSerialNo.push(null)
                } else {
                    prodSerialNo.push($(this).find('select[name="product_serial_no[]"]').val())
                }
                warrantyPeriod.push($(this).find('select[name="warranty_period[]"]').val())
            })
            // Submit
            let url = isSaveAsDraft == 'true' ? '{{ route('sale.save_as_draft') }}' :
                '{{ route('sale.upsert_details') }}'
            url = `${url}?type=quo`

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: url,
                type: 'POST',
                data: {
                    'sale_id': SALE != null ? SALE.id : null,
                    'sale': $('select[name="sale"]').val(),
                    'customer': $('select[name="customer"]').val(),
                    'open_until': $('input[name="open_until"]').val(),
                    'reference': $('input[name="reference"]').val(),
                    'store': $('input[name="store"]').val(),
                    'from': $('input[name="from"]').val(),
                    'cc': $('input[name="cc"]').val(),
                    'status': $('select[name="status"]').val(),
                    'report_type': $('select[name="report_type"]').val(),
                    'payment_term': $('select[name="payment_term"]').val(),
                    'billing_address': $('select[name="billing_address"]').val() == 'null' ? null : $(
                        'select[name="billing_address"]').val(),
                    'new_billing_address1': $('#new-billing-address input[name="address1"]').val(),
                    'new_billing_address2': $('#new-billing-address input[name="address2"]').val(),
                    'new_billing_address3': $('#new-billing-address input[name="address3"]').val(),
                    'new_billing_address4': $('#new-billing-address input[name="address4"]').val(),

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

                    'remark': $('#additional-remark-container textarea[name="remark"]').val(),
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
                    $(`form ${isSaveAsDraft == 'true' ? '#save-as-draft-btn' : '#submit-btn' }`).text(
                        'Updated')
                    $(`form ${isSaveAsDraft == 'true' ? '#save-as-draft-btn' : '#submit-btn' }`)
                        .addClass('bg-green-400 shadow')

                    setTimeout(() => {
                        window.location.href = '{{ route('quotation.index') }}'
                    }, 1000);
                },
                error: function(err) {
                    setTimeout(() => {
                        if (err.status == StatusCodes.UNPROCESSABLE_ENTITY || err.status ==
                            StatusCodes.BAD_REQUEST) {
                            let errors = err.responseJSON.errors

                            for (const key in errors) {
                                if (key.includes('new_billing_')) {
                                    $(`#new-billing-address #${key.replace('new_billing_', '') }_err`)
                                        .find('p').text(errors[key])
                                    $(`#new-billing-address #${key.replace('new_billing_', '') }_err`)
                                        .removeClass('hidden')
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
            url = `${url}?type=quo&is_hi_ten=${selected_product}`

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
