@extends('layouts.app')
@section('title', 'Quotation')

@section('content')
    <div class="mb-6 flex justify-between items-center">
        <x-app.page-title
            url="{{ route('quotation.index') }}">{{ isset($sale) ? __('Edit Quotation - ') . $sale->sku : __('Create Quotation') }}</x-app.page-title>
    </div>
    @include('components.app.alert.parent')
    <div class="mb-2">
        @if (!isset($sale))
            <div class="flex flex-col">
                <span class="text-xs text-slate-600 leading-none">{{ __('Potential ID') }}</span>
                <span class="text-md font-semibold" id="next-sku">-</span>
            </div>
        @elseif (isset($transfer_to) && $transfer_to != null)
            <div class="flex flex-col">
                <span class="text-xs text-slate-600 leading-none">{{ __('Transfer To') }}</span>
                <span class="text-md font-semibold">{{ $transfer_to }}</span>
            </div>
        @endif
    </div>
    <div class="grid gap-y-8">
        <form action="{{ isset($sale) ? route('sale.upsert_details', ['sale' => $sale]) : route('sale.upsert_details') }}"
            method="POST" enctype="multipart/form-data" class="flex flex-col gap-4">
            @csrf

            @include('quotation.form_step.quotation_details')
            @include('quotation.form_step.product_details')
            @include('quotation.form_step.remarks')

            @if (!isset($is_view) || (isset($is_view) && $is_view != true))
                <div class="flex justify-end gap-x-4">
                    @if (isset($sale) && $sale->status == 2)
                        <span
                            class="text-sm text-slate-500 border border-slate-500 py-1 px-1.5 w-fit rounded">{{ __('Converted') }}</span>
                    @elseif (isset($sale) && $sale->status == 3)
                        <span
                            class="text-sm text-red-500 border border-red-500 py-1 px-1.5 w-fit rounded">{{ __('Cancelled') }}</span>
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
        REPLICATE = @json($replicate ?? null);
        if (REPLICATE != null && SALE == null) {
            SALE = REPLICATE
            getNextSku()
        }

        $(document).ready(function() {
            if (SALE == null) {
                getNextSku()
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
                if ($(this).find('select[name="product_serial_no[]"]').val() == null || $(this).find(
                        'select[name="product_serial_no[]"]').val().length <= 0) {
                    prodSerialNo.push(null)
                } else {
                    prodSerialNo.push($(this).find('select[name="product_serial_no[]"]').val())
                }
                warrantyPeriod.push($(this).find('select[name="warranty_period[]"]').val())
            })
            if (REPLICATE != null) {
                prodOrderId = []
            }
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
                    'sale_id': REPLICATE != null ? null : (SALE != null ? SALE.id : null),
                    'sale': $('select[name="sale"]').val(),
                    'customer': $('select[name="customer"]').val(),
                    'open_until': $('input[name="open_until"]').val(),
                    'reference': $('input[name="reference"]').val(),
                    'store': $('input[name="store"]').val(),
                    'from': $('input[name="from"]').val(),
                    'cc': $('input[name="cc"]').val(),
                    'status': $('select[name="status"]').val(),
                    'report_type': $('select[name="report_type"]').val(),
                    'payment_method': $('select[name="payment_method"]').val(),
                    'payment_term': $('select[name="payment_term"]').val(),
                    'billing_address': $('select[name="billing_address"]').val() == 'null' ? null : $(
                        'select[name="billing_address"]').val(),
                    'new_billing_address1': $('#new-billing-address input[name="address1"]').val(),
                    'new_billing_address2': $('#new-billing-address input[name="address2"]').val(),
                    'new_billing_address3': $('#new-billing-address input[name="address3"]').val(),
                    'new_billing_address4': $('#new-billing-address input[name="address4"]').val(),
                    'delivery_address': $('select[name="delivery_address"]').val() == 'null' ? null : $(
                        'select[name="delivery_address"]').val(),
                    'new_delivery_address1': $('#new-delivery-address input[name="address1"]').val(),
                    'new_delivery_address2': $('#new-delivery-address input[name="address2"]').val(),
                    'new_delivery_address3': $('#new-delivery-address input[name="address3"]').val(),
                    'new_delivery_address4': $('#new-delivery-address input[name="address4"]').val(),

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
                                if (key.includes('new_delivery_')) {
                                    $(`#new-delivery-address #${key.replace('new_delivery_', '') }_err`)
                                        .find('p').text(errors[key])
                                    $(`#new-delivery-address #${key.replace('new_delivery_', '') }_err`)
                                        .removeClass('hidden')
                                } else if (key.includes('new_billing_')) {
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
