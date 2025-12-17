@extends('layouts.app')
@section('title', 'Sale Order')

@section('content')
    <div class="mb-6 flex justify-between items-center">
        <x-app.page-title
            url="{{ route('sale_order.index') }}">{{ isset($sale) ? __(isset($is_view) && $is_view == true ? 'View Sale Order - ' : 'Edit Sale Order - ') . $sale->sku : __('Create Sale Order') }}</x-app.page-title>
        @if ($can_edit_payment && isset($sale))
            <a href="{{ route('sale_order.edit_payment', ['sale' => $sale->id]) }}" class="text-sm bg-yellow-400 p-2 rounded hover:bg-yellow-300 hover:shadow transition-all duration-300">{{ __('Create / Edit Payment') }}</a>
        @endif
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

            @include('sale_order.form_step.quotation_details')
            @include('sale_order.form_step.product_details')
            @include('sale_order.form_step.remarks')

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
                            <x-app.button.submit id="save-and-preview-btn"
                                class="!bg-green-200">{{ __('Save and Preview') }}</x-app.button.submit>
                            <x-app.button.submit id="save-as-draft-btn"
                                class="!bg-blue-200">{{ __('Save As Draft') }}</x-app.button.submit>
                            <x-app.button.submit id="submit-btn">{{ __('Save and Update') }}</x-app.button.submit>
                        @endif
                    </div>
                @endif
            @endif
        </form>
    </div>

    <x-app.modal.to-production-modal />
@endsection

@push('scripts')
    <script>
        FORM_CAN_SUBMIT = true
        SALE = @json($sale ?? null);
        QUO = @json($quo ?? null);
        IS_VIEW = @json($is_view ?? null);

        $(document).ready(function() {
            if (SALE == null) {
                getNextSku()
            } else {
                $('#quotation-details-container input, #quotation-details-container select, #product-details-container input[name="qty"], #product-details-container input[name="override_selling_price"], #product-details-container input[name="discount"], #product-details-container input[name="product_desc"], #product-details-container textarea[name="remark"], #additional-remark-container input').css('backgroundColor', '#eee')
                $('#quotation-details-container input, #product-details-container input[name="qty"], #product-details-container input[name="override_selling_price"], #product-details-container input[name="discount"], #product-details-container input[name="product_desc"], #product-details-container textarea[name="remark"], #additional-remark-container input').parent().css('backgroundColor', '#eee')
                $('#product-details-container select[name="promotion[]"]').css('backgroundColor', '#eee')
                $('#quotation-details-container .select2, #product-details-container .select2-container--disabled').css('backgroundColor', '#eee')

                $('#quotation-details-container input, #quotation-details-container select, #product-details-container input[name="qty"], #product-details-container input[name="override_selling_price"], #product-details-container input[name="discount"], #product-details-container input[name="product_desc"], #product-details-container textarea[name="remark"], #additional-remark-container input').attr('disabled', true)
                $('#product-details-container select[name="promotion[]"]').attr('disabled', true)

                $('#quotation-details-container input[name="custom_date"]').attr('disabled', false)
                $('#quotation-details-container input[name="custom_date"]').css('backgroundColor', '#fff')
                $('#quotation-details-container input[name="custom_date"]').parent().css('backgroundColor', '#fff')

                @if (in_array(\App\Models\Role::SALE, getUserRoleId(Auth::user())))
                    $('#product-details-container select[name="product_serial_no[]"]').attr('disabled', false)
                    $('#product-details-container select[name="product_serial_no[]"]').css('backgroundColor', '#eee')
                @endif
            }
        })

        $('#save-as-draft-btn, #submit-btn, #save-and-preview-btn').on('click', function() {
            $(this).attr('data-triggered', true)
        })

        $('form').on('submit', function(e) {
            e.preventDefault()

            if (!FORM_CAN_SUBMIT) return

            FORM_CAN_SUBMIT = false
            isSaveAsDraft = $('#save-as-draft-btn').attr('data-triggered')
            isSaveAndPreview = $('#save-and-preview-btn').attr('data-triggered')

            // Determine which button to update
            let activeBtn = isSaveAsDraft == 'true' ? '#save-as-draft-btn' : (isSaveAndPreview == 'true' ? '#save-and-preview-btn' : '#submit-btn')
            $(`form ${activeBtn}`).text('Updating')
            $(`form ${activeBtn}`).removeClass('!bg-blue-200 !bg-green-200 bg-yellow-400 shadow')
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
            let accessory = []
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
                accessory.push($(this).find('select[name="accessory_id[]"]').val())
            })
            let additionalRemark = $('#additional-remark-container .quill-wrapper .ql-editor').html()
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
                    'custom_date': $('input[name="custom_date"]').val(),
                    'sale': $('select[name="sale"]').val(),
                    'customer': $('select[name="customer"]').val(),
                    'billing_address': $('select[name="billing_address"]').val() == 'null' ? null : $(
                        'select[name="billing_address"]').val(),
                    'delivery_address': $('select[name="delivery_address"]').val() == 'null' ? null : $(
                        'select[name="delivery_address"]').val(),

                    'reference': $('input[name="reference_input"]').val(),
                    'store': $('input[name="store"]').val(),
                    'warehouse': $('input[name="warehouse"]').val(),
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
                    'accessory': accessory,
                    'discount': discount,
                    'product_remark': remark,
                    'override_selling_price': overrideSellingPrice,

                    'remark': additionalRemark,
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

                    $(`form ${activeBtn}`).text('Updated')
                    $(`form ${activeBtn}`).addClass('bg-green-400 shadow')

                    // If save and preview, open PDF in new tab
                    if (isSaveAndPreview == 'true' && res.data != undefined && res.data.sale) {
                        window.open(`{{ config('app.url') }}/sale-order/pdf/${res.data.sale.id}`, '_blank')
                    }

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
                        } else if (isSaveAndPreview == 'true') {
                            $('form #save-and-preview-btn').text('Save and Preview')
                            $('form #save-and-preview-btn').addClass('!bg-green-200 shadow')
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
