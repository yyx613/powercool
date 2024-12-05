<div class="bg-white p-4 border rounded-md">
    <div class="flex items-center mb-6 border-l-8 border-yellow-400 px-3 py-1 bg-yellow-50 w-fit">
        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M19.5,16c0,.553-.447,1-1,1h-2c-.553,0-1-.447-1-1s.447-1,1-1h2c.553,0,1,.447,1,1Zm4.5-1v5c0,2.206-1.794,4-4,4H4c-2.206,0-4-1.794-4-4v-5c0-2.206,1.794-4,4-4h1V4C5,1.794,6.794,0,9,0h6c2.206,0,4,1.794,4,4v7h1c2.206,0,4,1.794,4,4ZM7,11h10V4c0-1.103-.897-2-2-2h-6c-1.103,0-2,.897-2,2v7Zm-3,11h7V13H4c-1.103,0-2,.897-2,2v5c0,1.103,.897,2,2,2Zm18-7c0-1.103-.897-2-2-2h-7v9h7c1.103,0,2-.897,2-2v-5Zm-14.5,0h-2c-.553,0-1,.447-1,1s.447,1,1,1h2c.553,0,1-.447,1-1s-.447-1-1-1ZM14,5c0-.553-.447-1-1-1h-2c-.553,0-1,.447-1,1s.447,1,1,1h2c.553,0,1-.447,1-1Z"/></svg>
        <span class="text-lg ml-3 font-bold">{{ __('Product Details') }}</span>
    </div>
    <form action="" method="POST" enctype="multipart/form-data" id="product-form">
        @csrf
        <div>
            <div class="grid grid-cols-4 gap-8 w-full mb-8 p-4 rounded-md relative hidden group transition durtion-300 hover:bg-slate-50" id="item-template">
                <button type="button" class="bg-rose-400 p-2 rounded-full absolute top-[-5px] right-[-5px] hidden group-hover:block delete-item-btns" title="Delete Product">
                    <svg class="h-3 w-3 fill-white" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M13.93,12L21.666,2.443c.521-.644,.422-1.588-.223-2.109-.645-.522-1.588-.421-2.109,.223l-7.334,9.06L4.666,.557c-1.241-1.519-3.56,.357-2.332,1.887l7.736,9.557L2.334,21.557c-.521,.644-.422,1.588,.223,2.109,.64,.519,1.586,.424,2.109-.223l7.334-9.06,7.334,9.06c.524,.647,1.47,.742,2.109,.223,.645-.521,.744-1.466,.223-2.109l-7.736-9.557Z"/></svg>
                </button>
                <div class="flex col-span-4 justify-end hidden attached-do-msg">
                    <p class="text-xs text-blue-700 border border-blue-700 p-1.5 rounded shadow">{{ __('Product is attached to DO') }}</p>
                </div>
                <div class="flex flex-col">
                    <x-app.input.label class="mb-1">{{ __('Product Name') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select name="product_id[]">
                        <option value=""></option>
                    </x-app.input.select>
                    <x-app.message.error id="product_id_err"/>
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="qty" class="mb-1">{{ __('Quantity') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="qty" id="qty" :hasError="$errors->has('qty')" class="int-input" />
                    <x-app.message.error id="qty_err"/>
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="unit_price" class="mb-1">{{ __('Unit Price') }} <span class="text-xs mt-1 hidden" id="price-hint">(<span id="min_price"></span> - <span id="max_price"></span>)</span> <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="unit_price" id="unit_price" :hasError="$errors->has('unit_price')" class="decimal-input" step=".01"/>
                    <x-app.message.error id="unit_price_err"/>
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="amount" class="mb-1">{{ __('Amount') }}</x-app.input.label>
                    <x-app.input.input name="amount" id="amount" :hasError="$errors->has('amount')" disabled="true" />
                    <x-app.message.error id="amount_err"/>
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="product_desc" class="mb-1">{{ __('Product Description') }}</x-app.input.label>
                    <x-app.input.input name="product_desc" id="product_desc" :hasError="$errors->has('product_desc')" />
                    <x-app.message.error id="product_desc_err"/>
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="uom" class="mb-1">{{ __('UOM') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="uom" id="uom" :hasError="$errors->has('uom')" disabled="true" />
                    <x-app.message.error id="uom_err"/>
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="promotion" class="mb-1">{{ __('Promotion') }} <span class="text-xs text-red-400 font-semibold mt-1 hidden" id="promo-hint"></span></x-app.input.label>
                    <x-app.input.select name="promotion[]">
                        <option value="">{{ __('Select a promotion') }}</option>
                    </x-app.input.select>
                    <x-app.message.error id="promotion_err"/>
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="warranty_period" class="mb-1">{{ __('Warranty Period') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select name="warranty_period[]">
                        <option value=""></option>
                    </x-app.input.select>
                    <x-app.message.error id="warranty_period_err"/>
                </div>
                <div class="flex flex-col col-span-4">
                    <x-app.input.label id="product_serial_no" class="mb-1">{{ __('Product Serial No') }}</x-app.input.label>
                    <x-app.input.select name="product_serial_no[]" multiple class="h-36">
                    </x-app.input.select>
                    <x-app.message.error id="product_serial_no_err"/>
                </div> 
            </div>
            <div id="items-container"></div>
            <!-- Add Items -->
            <div class="flex justify-end px-4 {{ isset($sale) && ($sale->status == 2 || $sale->status == 3) ? 'hidden' : '' }}">
                <button type="button" class="bg-yellow-400 rounded-md py-1.5 px-3 flex items-center gap-x-2 transition duration-300 hover:bg-yellow-300 hover:shadow" id="add-item-btn">
                    <svg class="h-3 w-3" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Capa_1" x="0px" y="0px" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve" width="512" height="512">
                        <path d="M480,224H288V32c0-17.673-14.327-32-32-32s-32,14.327-32,32v192H32c-17.673,0-32,14.327-32,32s14.327,32,32,32h192v192   c0,17.673,14.327,32,32,32s32-14.327,32-32V288h192c17.673,0,32-14.327,32-32S497.673,224,480,224z"/>
                    </svg>
                    <span class="text-sm">{{ __('Add Item') }}</span>
                </button>
            </div>
            <!-- Total -->
            <div class="flex justify-end mt-6 pt-6 border-t px-4 pb-4">
                <table>
                    <tbody>
                        <tr>
                            <td>{{ __('Subtotal') }}</td>
                            <td class="w-4 text-center">:</td>
                            <td id="subtotal">0.00</td>
                        </tr>
                        <tr>
                            <td>{{ __('Promo') }}</td>
                            <td class="w-4 text-center">:</td>
                            <td id="promo-amount">0.00</td>
                        </tr>
                        <tr>
                            <td>{{ __('Tax') }}</td>
                            <td class="w-4 text-center">:</td>
                            <td>0.00</td>
                        </tr>
                        <tr>
                            <td>{{ __('Total') }}</td>
                            <td class="w-4 text-center">:</td>
                            <td id="total">0.00</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        @if (isset($sale) && $sale->status == 2)
            <div class="mt-8 flex justify-end">
                <span class="text-sm text-slate-500 border border-slate-500 py-1 px-1.5 w-fit rounded">{{ __('Converted') }}</span>
            </div>
        @elseif (isset($sale) && $sale->status == 3)
            <div class="mt-8 flex justify-end">
                <span class="text-sm text-slate-500 border border-slate-500 py-1 px-1.5 w-fit rounded">{{ __('Cancelled') }}</span>
            </div>
        @else
            <div class="mt-8 flex justify-end">
                <x-app.button.submit id="submit-btn">{{ __('Save and Update') }}</x-app.button.submit>
            </div>
        @endif
    </form>
</div>


@push('scripts')
<script>
    PRODUCTS = @json($products ?? []);
    WARRANTY_PERIODS = @json($warranty_periods ?? []);
    PROMOTIONS = @json($promotions ?? []);
    UOMS = @json($uoms ?? []);
    PRODUCT_FORM_CAN_SUBMIT = true
    ITEMS_COUNT = 0
    INIT_EDIT = true

    $(document).ready(function(){
        if (SALE != null) {
            console.debug(SALE)

            for (let i = 0; i < SALE.products.length; i++) {
                const sp = SALE.products[i];

                $('#add-item-btn').click()

                $(`.items[data-id="${i+1}"]`).attr('data-product-id', sp.id)
                $(`.items[data-id="${i+1}"] select[name="product_id[]"]`).val(sp.product_id).trigger('change')
                $(`.items[data-id="${i+1}"] input[name="qty"]`).val(sp.qty)
                $(`.items[data-id="${i+1}"] input[name="uom"]`).val(sp.uom)
                $(`.items[data-id="${i+1}"] input[name="unit_price"]`).val(sp.unit_price)
                $(`.items[data-id="${i+1}"] input[name="product_desc"]`).val(sp.desc)
                $(`.items[data-id="${i+1}"] select[name="warranty_period[]"]`).val(sp.warranty_period_id)
                setTimeout(() => {
                    $(`.items[data-id="${i+1}"] select[name="promotion[]"]`).val(sp.promotion_id).trigger('change')
                }, 1);

                $(`.items[data-id="${i+1}"] input[name="qty"]`).trigger('keyup')
                if (sp.attached_to_do == true) {
                    $(`.items[data-id="${i+1}"] .delete-item-btns`).remove()
                    $(`.items[data-id="${i+1}"] .attached-do-msg`).removeClass('hidden')
                }
                
                buildSerialNoOptions(sp.product_id, i+1, sp.id)
                buildPromotionSelect(i+1, sp.product_id)
            }
            if (SALE.products.length <= 0) $('#add-item-btn').click()

            $('select[name="promotion_id"]').trigger('change')
        } else {
            $('#add-item-btn').click()
        }

        INIT_EDIT = false
    })
    $('#add-item-btn').on('click', function() {
        let clone = $('#item-template')[0].cloneNode(true);
        
        ITEMS_COUNT++
        $(clone).attr('data-id', ITEMS_COUNT)
        $(clone).find('.delete-item-btns').attr('data-id', ITEMS_COUNT)
        $(clone).addClass('items')
        $(clone).removeClass('hidden')
        $(clone).removeAttr('id')
        
        $('#items-container').append(clone)

        $(`.items[data-id="${ITEMS_COUNT}"] select[name="product_id[]"]`).select2({
            placeholder: "{!! __('Select a product') !!}"
        })
        for (let i = 0; i < PRODUCTS.length; i++) {
            const element = PRODUCTS[i];
            
            let opt = new Option(element.model_name, element.id)
            $(`.items[data-id="${ITEMS_COUNT}"] select[name="product_id[]"]`).append(opt)
        }
        // Build warranty period select2
        buildWarrantyPeriodSelect2(ITEMS_COUNT)
        if (!INIT_EDIT) {
            buildPromotionSelect(ITEMS_COUNT) // Build promotion select
        }

        $(`.items[data-id="${ITEMS_COUNT}"] .select2`).addClass('border border-gray-300 rounded-md overflow-hidden')

        hideDeleteBtnWhenOnlyOneItem()
    })
    $('body').on('click', '.delete-item-btns', function() {
        let id = $(this).data('id')

        $(`.items[data-id="${id}"]`).remove()

        ITEMS_COUNT = 0
        $('.items').each(function(i, obj) {
            ITEMS_COUNT++
            $(this).attr('data-id', ITEMS_COUNT)
            $(this).find('.delete-item-btns').attr('data-id', ITEMS_COUNT)
        })
        hideDeleteBtnWhenOnlyOneItem()
        calSummary()
    })
    $('body').on('keydown', 'input[name="unit_price"]', function(e) {
        let val = $(this).val()
        let valEntered = e.key
        
        let split = val.split('.')
        if (split.length > 1) {
            let regX = RegExp(/^\d+$/)

            if (regX.test(valEntered)) {
                let valAfterDecimal = `${split[1]}${valEntered}`
    
                if (valAfterDecimal.length > 2) { // Max 2 decimal places
                    e.preventDefault()
                    return
                }
            }
        }
    });
    $('body').on('keyup', 'input[name="qty"], input[name="unit_price"]', function() {
        let idx = $(this).parent().parent().parent().data('id')

        calItemTotal(idx)
    })
    $('body').on('change', 'select[name="promotion[]"]', function() {
        let idx = $(this).parent().parent().data('id')

        calItemTotal(idx)
    })
    $('body').on('change', 'select[name="product_id[]"]', function() {
        let id = $(this).parent().parent().attr('data-id')
        let val = $(this).val()

        for (let i = 0; i < PRODUCTS.length; i++) {
            const prod = PRODUCTS[i];
         
            if (prod.id == val) {
                $(`.items[data-id="${id}"] input[name="uom"]`).val(null)

                for (let j = 0; j < UOMS.length; j++) {
                    if(UOMS[j].id == prod.uom) {
                        $(`.items[data-id="${id}"] input[name="uom"]`).val(UOMS[j].name)
                        break
                    }
                }
                $(`.items[data-id="${id}"] input[name="product_desc"]`).val(prod.model_desc)
                $(`.items[data-id="${id}"] #min_price`).text(priceFormat(prod.min_price))
                $(`.items[data-id="${id}"] #max_price`).text(priceFormat(prod.max_price))
                $(`.items[data-id="${id}"] #price-hint`).removeClass('hidden')
                break
            }
        }
        buildSerialNoOptions(val, id)
        buildPromotionSelect(id, val)
        $(`.items[data-id="${id}"] #promo-hint`).addClass('hidden')
    })
    $('select[name="promotion_id"]').on('change', function() {
        let val = $(this).val()
        let foundPromo = false

        if (val != '') {
            for (let i = 0; i < PROMOTIONS.length; i++) {
                const element = PROMOTIONS[i];
                
                if (element.id == val) {
                    calSummary(element.type, element.amount)
                    foundPromo = true
                    break
                }
            }
        }

        if (!foundPromo) calSummary()
    })
    $('#product-form').on('submit', function(e) {
        e.preventDefault()

        if (!PRODUCT_FORM_CAN_SUBMIT) return

        PRODUCT_FORM_CAN_SUBMIT = false

        $('#product-form #submit-btn').text('Updating')
        $('#product-form #submit-btn').removeClass('bg-yellow-400 shadow')
        $('.err_msg').addClass('hidden') // Remove error messages
        // Submit
        let url = '{{ route("sale.upsert_pro_details") }}'
        url = `${url}`

        let prodOrderId = []
        let prodId = []
        let prodDesc = []
        let qty = []
        let uom = []
        let unitPrice = []
        let promo = []
        let prodSerialNo = []
        let warrantyPeriod = []
        $('#product-form .items').each(function(i, obj) {
            prodOrderId.push($(this).data('product-id') ?? null)
            prodId.push($(this).find('select[name="product_id[]"]').val())
            prodDesc.push($(this).find('input[name="product_desc"]').val())
            qty.push($(this).find('input[name="qty"]').val())
            uom.push($(this).find('input[name="uom"]').val())
            unitPrice.push($(this).find('input[name="unit_price"]').val())
            promo.push($(this).find('select[name="promotion[]"]').val())
            if ($(this).find('select[name="product_serial_no[]"]').val().length <= 0) {
                prodSerialNo.push(null)
            } else {
                prodSerialNo.push($(this).find('select[name="product_serial_no[]"]').val())
            }
            warrantyPeriod.push($(this).find('select[name="warranty_period[]"]').val())
        })

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: url,
            type: 'POST',
            data: {
                'sale_id': SALE != null ? SALE.id : null,
                'product_order_id': prodOrderId,
                'product_id': prodId,
                'product_desc': prodDesc,
                'qty': qty,
                'uom': uom,
                'unit_price': unitPrice,
                'promotion_id': promo,
                'product_serial_no': prodSerialNo,
                'warranty_period': warrantyPeriod,
            },
            success: function(res) {
                setTimeout(() => {
                    $('#product-form #submit-btn').text('Updated')
                    $('#product-form #submit-btn').addClass('bg-green-400 shadow')

                    let product_ids = res.product_ids
                    $('#product-form .items').each(function(i, obj) {
                        $(this).attr('data-product-id', product_ids[i])
                    })

                    setTimeout(() => {
                        $('#product-form #submit-btn').text('Save and Update')
                        $('#product-form #submit-btn').removeClass('bg-green-400')
                        $('#product-form #submit-btn').addClass('bg-yellow-400 shadow')
                        
                        PRODUCT_FORM_CAN_SUBMIT = true
                    }, 2000);
                }, 300);
            },
            error: function(err) {
                setTimeout(() => {
                    if (err.status == StatusCodes.UNPROCESSABLE_ENTITY) {
                        let errors = err.responseJSON.errors

                        for (const key in errors) {
                            let field = key.split('.')[0]
                            let idx = key.split('.')[1]
                            idx++
                            $(`#product-form .items[data-id="${idx}"] #${field}_err`).find('p').text(errors[key])
                            $(`#product-form .items[data-id="${idx}"] #${field}_err`).removeClass('hidden')
                        }
                    } else if (err.status == StatusCodes.BAD_REQUEST) {
                        $(`#product-form .items #product_serial_no_err`).find('p').text(err.responseJSON.product_serial_no)
                        $(`#product-form .items #product_serial_no_err`).removeClass('hidden')
                    }
                    $('#product-form #submit-btn').text('Save and Update')
                    $('#product-form #submit-btn').addClass('bg-yellow-400 shadow')

                    PRODUCT_FORM_CAN_SUBMIT = true
                }, 300);
            },
        });
    })

    function calItemTotal(idx) {
        let qty = $(`.items[data-id="${idx}"] input[name="qty"]`).val()
        let unitPrice = $(`.items[data-id="${idx}"] input[name="unit_price"]`).val()
        let promo = $(`.items[data-id="${idx}"] select[name="promotion[]"]`).val()
        let subtotal = (qty * unitPrice)
        
        // Apply Promotion
        let discountAmount = 0
        if (promo != '') {
            for (let i = 0; i < PROMOTIONS.length; i++) {
                const element = PROMOTIONS[i];
                
                if (element.id == promo) {
                    if (element.type == 'val') {
                        discountAmount = element.amount
                    } else if (element.type == 'perc') {
                        discountAmount = subtotal * element.amount / 100
                    }
                    $(`.items[data-id="${idx}"] #promo-hint`).text(`( -${priceFormat(discountAmount)} )`)
                    $(`.items[data-id="${idx}"] #promo-hint`).removeClass('hidden')
                    break
                }
            }
        } else {
            $(`.items[data-id="${idx}"] #promo-hint`).addClass('hidden')
        }
        
        $(`.items[data-id="${idx}"] input[name="amount"]`).val(priceFormat(subtotal - discountAmount))

        calSummary()
    }
    function calSummary() {
        let overallSubtotal = 0
        let overallDiscountAmount = 0

        $('.items').each(function(i, obj) {
            let qty = $(this).find('input[name="qty"]').val()
            let unitPrice = $(this).find('input[name="unit_price"]').val()
            let promo = $(this).find('select[name="promotion[]"]').val()
            let subtotal = (qty * unitPrice)

            // Apply Promotion
            let discountAmount = 0
            if (promo != '') {
                for (let i = 0; i < PROMOTIONS.length; i++) {
                    const element = PROMOTIONS[i];
                    
                    if (element.id == promo) {
                        if (element.type == 'val') {
                            discountAmount = element.amount
                        } else if (element.type == 'perc') {
                            discountAmount = subtotal * element.amount / 100
                        }
                        break
                    }
                }
            }
            
            overallSubtotal += (subtotal * 1)
            overallDiscountAmount += (discountAmount * 1)
        })

        $('#subtotal').text(priceFormat(overallSubtotal))
        $('#promo-amount').text(priceFormat(overallDiscountAmount))
        $('#total').text(priceFormat(overallSubtotal - overallDiscountAmount))
    }
    function buildWarrantyPeriodSelect2(item_id) {
        $(`.items[data-id="${item_id}"] select[name="warranty_period[]"]`).select2({
            placeholder: "{!! __('Select a warranty') !!}"
        })

        for (let i = 0; i < WARRANTY_PERIODS.length; i++) {
            const wp = WARRANTY_PERIODS[i];
         
            let opt = new Option(wp.name, wp.id)
            $(`.items[data-id="${item_id}"] select[name="warranty_period[]"]`).append(opt)
        }
    }
    function buildSerialNoOptions(product_id, item_id, sale_product_id=null) {
        for (let i = 0; i < PRODUCTS.length; i++) {
            const prod = PRODUCTS[i];
         
            if (prod.id == product_id) {
                $(`.items[data-id="${item_id}"] select[name="product_serial_no[]"]`).empty()

                for (let j = 0; j < prod.children.length; j++) {
                    const child = prod.children[j];
                    let selected = selectedSerialNo(child.id, sale_product_id)
                   
                    let opt = new Option(child.sku, child.id, selected, selected)
                    opt.selected = selected
                    opt.value = child.id
                    $(`.items[data-id="${item_id}"] select[name="product_serial_no[]"]`).append(opt)
                }
                break
            }
        }
    }
    function buildPromotionSelect(item_id, product_id=null) {
        $(`.items[data-id="${item_id}"] select[name="promotion[]"]`).find('option').not(':first').remove();

        for (let i = 0; i < PROMOTIONS.length; i++) {
            const promo = PROMOTIONS[i];
         
            if (product_id != null && product_id != promo.product_id) {
                continue;
            }

            let opt = new Option(promo.sku, promo.id)
            $(`.items[data-id="${item_id}"] select[name="promotion[]"]`).append(opt)
        }
    }
    function selectedSerialNo($product_child_id, sale_product_id=null) {
        if (SALE != null && SALE.products != null) {
            for (let i = 0; i < SALE.products.length; i++) {
                const prod = SALE.products[i];

                for (let k = 0; k < prod.children.length; k++) {
                    const elem = prod.children[k];

                    if (prod.id == sale_product_id && elem.product_children_id == $product_child_id) {
                        return true
                    }
                }
            }            
        }
        return false
    }
    function hideDeleteBtnWhenOnlyOneItem() {
        if ($('.items').length == 1) {
            $('.items:first .delete-item-btns').removeClass('group-hover:block')
        } else {
            $('.items:first .delete-item-btns').addClass('group-hover:block')
        }
    }
</script>
@endpush