<div class="bg-white p-4 border rounded-md" id="product-details-container">
    <div class="flex items-center mb-6 border-l-8 border-yellow-400 px-3 py-1 bg-yellow-50 w-fit">
        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24"
            width="512" height="512">
            <path
                d="M19.5,16c0,.553-.447,1-1,1h-2c-.553,0-1-.447-1-1s.447-1,1-1h2c.553,0,1,.447,1,1Zm4.5-1v5c0,2.206-1.794,4-4,4H4c-2.206,0-4-1.794-4-4v-5c0-2.206,1.794-4,4-4h1V4C5,1.794,6.794,0,9,0h6c2.206,0,4,1.794,4,4v7h1c2.206,0,4,1.794,4,4ZM7,11h10V4c0-1.103-.897-2-2-2h-6c-1.103,0-2,.897-2,2v7Zm-3,11h7V13H4c-1.103,0-2,.897-2,2v5c0,1.103,.897,2,2,2Zm18-7c0-1.103-.897-2-2-2h-7v9h7c1.103,0,2-.897,2-2v-5Zm-14.5,0h-2c-.553,0-1,.447-1,1s.447,1,1,1h2c.553,0,1-.447,1-1s-.447-1-1-1ZM14,5c0-.553-.447-1-1-1h-2c-.553,0-1,.447-1,1s.447,1,1,1h2c.553,0,1-.447,1-1Z" />
        </svg>
        <span class="text-lg ml-3 font-bold">{{ __('Product Details') }}</span>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 lg:gap-8 w-full mb-8 p-4 rounded-md relative group hidden transition durtion-300 hover:bg-slate-50"
        id="item-template">
        <button type="button"
            class="bg-rose-400 p-2 rounded-full absolute top-[-5px] right-[-5px] hidden group-hover:block delete-item-btns"
            title="Delete Product">
            <svg class="h-3 w-3 fill-white" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1"
                viewBox="0 0 24 24" width="512" height="512">
                <path
                    d="M13.93,12L21.666,2.443c.521-.644,.422-1.588-.223-2.109-.645-.522-1.588-.421-2.109,.223l-7.334,9.06L4.666,.557c-1.241-1.519-3.56,.357-2.332,1.887l7.736,9.557L2.334,21.557c-.521,.644-.422,1.588,.223,2.109,.64,.519,1.586,.424,2.109-.223l7.334-9.06,7.334,9.06c.524,.647,1.47,.742,2.109,.223,.645-.521,.744-1.466,.223-2.109l-7.736-9.557Z" />
            </svg>
        </button>
        <div class="flex col-span-4 justify-end hidden attached-do-msg">
            <p class="text-xs text-blue-700 border border-blue-700 p-1.5 rounded shadow">
                {{ __('Product is attached to DO') }}</p>
        </div>
        <div class="flex flex-col">
            <x-app.input.label class="mb-1">{{ __('Product') }} <span
                    class="text-sm text-red-500">*</span></x-app.input.label>
            <x-app.input.select name="product_id[]">
                <option value=""></option>
                @foreach ($products as $p)
                    <option value="{{ $p->id }}">({{ $p->sku }}) {{ $p->model_name }}</option>
                @endforeach
            </x-app.input.select>
            <x-app.message.error id="product_id_err" />
        </div>
        <div class="flex flex-col hidden customize-product-container">
            <x-app.input.label id="customize_product" class="mb-1">{{ __('Customize Product') }}</x-app.input.label>
            <x-app.input.input name="customize_product" id="customize_product" :hasError="$errors->has('customize_product')" />
            <x-app.message.error id="customize_product_err" />
        </div>
        <div class="flex flex-col">
            <x-app.input.label id="qty" class="mb-1">{{ __('Quantity') }} <span
                    class="text-sm text-red-500">*</span></x-app.input.label>
            <div class="flex border border-gray-300 rounded-md overflow-hidden">
                <x-app.input.input name="qty" id="qty" :hasError="$errors->has('qty')"
                    class="int-input border-none flex-1" />
                <button type="button"
                    class="foc-btns font-semibold text-sm px-1.5 border-l border-gray-300 data-[is-foc=false]:bg-slate-100 data-[is-foc=true]:bg-emerald-100"
                    data-is-foc="false">FOC</button>
            </div>
            <x-app.message.error id="qty_err" />
        </div>
        <div class="flex flex-col">
            <x-app.input.label id="selling_price" class="mb-1">{{ __('Selling Price') }} <span
                    class="text-xs mt-1 hidden" id="price-hint">(<span id="min_price"></span> - <span
                        id="max_price"></span>)</span></x-app.input.label>
            <x-app.input.select name="selling_price[]">
                <option value="">{{ __('Select a seling price') }}</option>
            </x-app.input.select>
            <x-app.message.error id="selling_price_err" />
        </div>
        <div class="flex flex-col">
            <x-app.input.label id="override_selling_price"
                class="mb-1">{{ __('Override Selling Price') }}</x-app.input.label>
            <x-app.input.input name="override_selling_price" id="override_selling_price" :hasError="$errors->has('override_selling_price')"
                class="decimal-input" />
            <x-app.message.error id="override_selling_price_err" />
        </div>
        <input type="hidden" name="unit_price[]" value="">
        <div class="flex flex-col">
            <x-app.input.label id="amount" class="mb-1">{{ __('Amount') }}</x-app.input.label>
            <x-app.input.input name="amount" id="amount" :hasError="$errors->has('amount')" disabled="true" />
            <x-app.message.error id="amount_err" />
        </div>
        <div class="flex flex-col">
            <x-app.input.label id="product_desc" class="mb-1">{{ __('Product Description') }}</x-app.input.label>
            <x-app.input.input name="product_desc" id="product_desc" :hasError="$errors->has('product_desc')" />
            <x-app.message.error id="product_desc_err" />
        </div>
        <div class="flex flex-col">
            <x-app.input.label id="uom" class="mb-1">{{ __('UOM') }} <span
                    class="text-sm text-red-500">*</span></x-app.input.label>
            <x-app.input.input name="uom" id="uom" :hasError="$errors->has('uom')" disabled="true" />
            <x-app.message.error id="uom_err" />
        </div>
        <div class="flex flex-col">
            <x-app.input.label id="promotion" class="mb-1">{{ __('Promotion') }} <span
                    class="text-xs text-red-400 font-semibold mt-1 hidden" id="promo-hint"></span></x-app.input.label>
            <x-app.input.select name="promotion[]">
                <option value="">{{ __('Select a promotion') }}</option>
            </x-app.input.select>
            <x-app.message.error id="promotion_err" />
        </div>
        <div class="flex flex-col">
            <x-app.input.label id="discount" class="mb-1">{{ __('Discount') }} <span
                    class="text-xs text-red-400 font-semibold mt-1 hidden"
                    id="discount-hint"></span></x-app.input.label>
            <x-app.input.input name="discount" id="discount" :hasError="$errors->has('discount')" class="decimal-input" />
            <x-app.message.error id="discount_err" />
        </div>
        <div class="flex flex-col">
            <x-app.input.label id="warranty_period" class="mb-1">{{ __('Warranty Period') }} <span
                    class="text-sm text-red-500">*</span></x-app.input.label>
            <x-app.input.select name="warranty_period[]" multiple>
                <option value=""></option>
            </x-app.input.select>
            <x-app.message.error id="warranty_period_err" />
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 lg:gap-8 col-span-2 md:col-span-4">
            <div class="flex flex-col flex-1 col-span-2">
                <x-app.input.label id="product_serial_no"
                    class="mb-1">{{ __('Product Serial No') }}</x-app.input.label>
                <x-app.input.select name="product_serial_no[]" multiple class="h-36 md:h-full">
                </x-app.input.select>
                <x-app.message.error id="product_serial_no_err" />
            </div>
            <div class="flex flex-col flex-1 col-span-2">
                <x-app.input.label id="remark" class="mb-1">{{ __('Remark') }}</x-app.input.label>
                <x-app.input.textarea name="remark" id="remark" :hasError="$errors->has('remark')" />
                <x-app.message.error id="remark_err" />
            </div>
        </div>
    </div>
    <div id="items-container"></div>
    <!-- Add Items -->
    <div
        class="flex justify-end px-4 {{ isset($sale) && ($sale->status == 2 || $sale->status == 3) ? 'hidden' : '' }} {{ isset($convert_from_quo) && $convert_from_quo ? 'hidden' : '' }}">
        <button type="button"
            class="bg-yellow-400 rounded-md py-1.5 px-3 flex items-center gap-x-2 transition duration-300 hover:bg-yellow-300 hover:shadow"
            id="add-item-btn">
            <svg class="h-3 w-3" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                version="1.1" id="Capa_1" x="0px" y="0px" viewBox="0 0 512 512"
                style="enable-background:new 0 0 512 512;" xml:space="preserve" width="512" height="512">
                <path
                    d="M480,224H288V32c0-17.673-14.327-32-32-32s-32,14.327-32,32v192H32c-17.673,0-32,14.327-32,32s14.327,32,32,32h192v192   c0,17.673,14.327,32,32,32s32-14.327,32-32V288h192c17.673,0,32-14.327,32-32S497.673,224,480,224z" />
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
                    <td>{{ __('Discount') }}</td>
                    <td class="w-4 text-center">:</td>
                    <td id="discount-amount">0.00</td>
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


@push('scripts')
    <script>
        PRODUCTS = @json($products ?? []);
        WARRANTY_PERIODS = @json($warranty_periods ?? []);
        PROMOTIONS = @json($promotions ?? []);
        UOMS = @json($uoms ?? []);
        CUSTOMIZE_PRODUCT_IDS = @json($customize_product_ids ?? []);
        PRODUCT_FORM_CAN_SUBMIT = true
        ITEMS_COUNT = 0
        INIT_EDIT = true

        $(document).ready(function() {
            if (SALE != null) {
                for (let i = 0; i < SALE.products.length; i++) {
                    const sp = SALE.products[i];

                    $('#add-item-btn').click()

                    $(`.items[data-id="${i+1}"]`).attr('data-product-id', sp.id)
                    $(`.items[data-id="${i+1}"] select[name="product_id[]"]`).val(sp.product_id).trigger('change')
                    $(`.items[data-id="${i+1}"] input[name="qty"]`).val(sp.qty)
                    $(`.items[data-id="${i+1}"] .foc-btns`).attr('data-is-foc', sp.is_foc == 1 ? false : true).trigger('click') // Reverse value for trigger click
                    $(`.items[data-id="${i+1}"] input[name="uom"]`).val(sp.uom)
                    $(`.items[data-id="${i+1}"] select[name="selling_price[]"]`).val(sp.selling_price_id).trigger(
                        'change')
                    $(`.items[data-id="${i+1}"] input[name="product_desc"]`).val(sp.desc)
                    let temp = []
                    for (let j = 0; j < sp.warranty_periods.length; j++) {
                        temp.push(sp.warranty_periods[j].warranty_period_id)
                    }
                    $(`.items[data-id="${i+1}"] select[name="warranty_period[]"]`).val(temp)
                    $(`.items[data-id="${i+1}"] input[name="discount"]`).val(sp.discount)
                    $(`.items[data-id="${i+1}"] textarea[name="remark"]`).val(sp.remark)
                    if (sp.override_selling_price != null) {
                        $(`.items[data-id="${i+1}"] input[name="override_selling_price"]`).val(sp
                            .override_selling_price).trigger('keyup')
                    }
                    setTimeout(() => {
                        $(`.items[data-id="${i+1}"] select[name="promotion[]"]`).val(sp.promotion_id)
                            .trigger('change')
                    }, 1);

                    $(`.items[data-id="${i+1}"] input[name="qty"]`).trigger('keyup')
                    if (sp.attached_to_do == true) {
                        $(`.items[data-id="${i+1}"] .delete-item-btns`).remove()
                        $(`.items[data-id="${i+1}"] .attached-do-msg`).removeClass('hidden')
                    }

                    buildSerialNoOptions(sp.product_id, i + 1, sp.id)
                    buildPromotionSelect(i + 1, sp.product_id)
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
            $(clone).find('.foc-btns').attr('data-id', ITEMS_COUNT)
            $(clone).addClass('items')
            $(clone).removeClass('hidden')
            $(clone).removeAttr('id')

            $('#items-container').append(clone)

            $(`.items[data-id="${ITEMS_COUNT}"] select[name="product_id[]"]`).select2({
                placeholder: "{!! __('Select a product') !!}"
            })
            // Build warranty period select2
            buildWarrantyPeriodSelect2(ITEMS_COUNT)
            if (!INIT_EDIT) {
                buildPromotionSelect(ITEMS_COUNT) // Build promotion select
            }

            $(`.items[data-id="${ITEMS_COUNT}"] .select2`).addClass(
                'border border-gray-300 rounded-md overflow-hidden')

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
        $('body').on('change', 'select[name="selling_price[]"]', function() {
            let idx = $(this).parent().parent().data('id')
            let productId = $(`.items[data-id="${idx}"] select[name="product_id[]"]`).val()
            let val = $(this).val()

            for (let i = 0; i < PRODUCTS.length; i++) {
                if (PRODUCTS[i].id == productId) {
                    for (let j = 0; j < PRODUCTS[i].selling_prices.length; j++) {
                        if (PRODUCTS[i].selling_prices[j].id == val) {
                            $(`.items[data-id="${idx}"] input[name="unit_price[]"]`).val(PRODUCTS[i].selling_prices[
                                j].price)
                            break
                        }
                    }
                    break;
                }
            }
        })
        $('body').on('keyup', 'input[name="override_selling_price"]', function() {
            let idx = $(this).parent().parent().parent().data('id')

            $(`.items[data-id="${idx}"] input[name="unit_price[]"]`).val($(this).val())
        })
        $('body').on('keyup', 'input[name="qty"], input[name="discount"], input[name="override_selling_price"]',
            function() {
                let idx = $(this).parent().parent().parent().data('id')

                calItemTotal(idx)
            })
        $('body').on('change', 'select[name="promotion[]"], select[name="selling_price[]"]', function() {
            let idx = $(this).parent().parent().data('id')

            calItemTotal(idx)
        })
        $('body').on('change', 'select[name="product_id[]"]', function() {
            let id = $(this).parent().parent().attr('data-id')
            let val = $(this).val()

            for (let i = 0; i < PRODUCTS.length; i++) {
                const prod = PRODUCTS[i];

                if (prod.id == val) {
                    $(`.items[data-id="${id}"] #min_price`).text(priceFormat(prod.min_price))
                    $(`.items[data-id="${id}"] #max_price`).text(priceFormat(prod.max_price))
                    $(`.items[data-id="${id}"] #price-hint`).removeClass('hidden')

                    $(`.items[data-id="${id}"] input[name="uom"]`).val(null)

                    for (let j = 0; j < UOMS.length; j++) {
                        if (UOMS[j].id == prod.uom) {
                            $(`.items[data-id="${id}"] input[name="uom"]`).val(UOMS[j].name)
                            break
                        }
                    }
                    $(`.items[data-id="${id}"] input[name="product_desc"]`).val(prod.model_desc)
                    // Append selling prices
                    for (let j = 0; j < prod.selling_prices.length; j++) {
                        let opt = new Option(
                            `${prod.selling_prices[j].name} (RM ${priceFormat(prod.selling_prices[j].price)})`,
                            prod.selling_prices[j].id)

                        $(`.items[data-id="${id}"] select[name="selling_price[]"]`).append(opt)
                    }
                    break
                }
            }
            buildSerialNoOptions(val, id)
            buildPromotionSelect(id, val)
            $(`.items[data-id="${id}"] #promo-hint`).addClass('hidden')
            // Customize product
            if (CUSTOMIZE_PRODUCT_IDS.includes(parseInt(val))) {
                $(`.items[data-id="${id}"] .customize-product-container`).removeClass('hidden')
            } else {
                $(`.items[data-id="${id}"] .customize-product-container`).addClass('hidden')
            }
        })
        $('body').on('click', '.foc-btns', function() {
            let isFoc = $(this).attr('data-is-foc')
            let id = $(this).data('id')

            if (isFoc === 'true') {
                $(this).attr('data-is-foc', false)

                $(`.items[data-id="${id}"] select[name="selling_price[]"]`).attr('disabled', false)
                $(`.items[data-id="${id}"] select[name="selling_price[]"]`).attr('aria-disabled', false)
                $(`.items[data-id="${id}"] input[name="override_selling_price"]`).attr('disabled', false)
                $(`.items[data-id="${id}"] input[name="override_selling_price"]`).attr('aria-disabled', false)
                $(`.items[data-id="${id}"] input[name="override_selling_price"]`).parent().attr('aria-disabled', false)
            } else {
                $(this).attr('data-is-foc', true)

                $(`.items[data-id="${id}"] select[name="selling_price[]"]`).val(null).trigger('change')
                $(`.items[data-id="${id}"] select[name="selling_price[]"]`).attr('disabled', true)
                $(`.items[data-id="${id}"] select[name="selling_price[]"]`).attr('aria-disabled', true)
                $(`.items[data-id="${id}"] input[name="override_selling_price"]`).val(null).trigger('keyup')
                $(`.items[data-id="${id}"] input[name="override_selling_price"]`).attr('disabled', true)
                $(`.items[data-id="${id}"] input[name="override_selling_price"]`).attr('aria-disabled', true)
                $(`.items[data-id="${id}"] input[name="override_selling_price"]`).parent().attr('aria-disabled', true)
            }
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


        function calItemTotal(idx) {
            let productId = $(`.items[data-id="${idx}"] select[name="product_id[]"]`).val()
            let qty = $(`.items[data-id="${idx}"] input[name="qty"]`).val()
            let sellingPrice = $(`.items[data-id="${idx}"] select[name="selling_price[]"]`).val()
            let promo = $(`.items[data-id="${idx}"] select[name="promotion[]"]`).val()
            let discount = $(`.items[data-id="${idx}"] input[name="discount"]`).val()
            let overrideSellingPrice = $(`.items[data-id="${idx}"] input[name="override_selling_price"]`).val()

            let unitPrice = 0
            if (overrideSellingPrice != '') {
                unitPrice = overrideSellingPrice
            } else {
                for (let i = 0; i < PRODUCTS.length; i++) {
                    if (PRODUCTS[i].id == productId) {
                        for (let j = 0; j < PRODUCTS[i].selling_prices.length; j++) {
                            if (PRODUCTS[i].selling_prices[j].id == sellingPrice) {
                                unitPrice = PRODUCTS[i].selling_prices[j].price
                                break
                            }
                        }
                        break;
                    }
                }
            }
            let subtotal = (qty * unitPrice)

            // Apply Promotion
            let promoAmount = 0
            if (promo != '') {
                for (let i = 0; i < PROMOTIONS.length; i++) {
                    const element = PROMOTIONS[i];

                    if (element.id == promo) {
                        if (element.type == 'val') {
                            promoAmount = element.amount
                        } else if (element.type == 'perc') {
                            promoAmount = subtotal * element.amount / 100
                        }
                        $(`.items[data-id="${idx}"] #promo-hint`).text(`( -${priceFormat(promoAmount)} )`)
                        $(`.items[data-id="${idx}"] #promo-hint`).removeClass('hidden')
                        break
                    }
                }
            } else {
                $(`.items[data-id="${idx}"] #promo-hint`).addClass('hidden')
            }
            // Apply Discount
            let discountAmount = 0
            if (discount != '' && discount != null) {
                discountAmount = discount
                $(`.items[data-id="${idx}"] #discount-hint`).text(`( -${priceFormat(discountAmount)} )`)
                $(`.items[data-id="${idx}"] #discount-hint`).removeClass('hidden')
            } else {
                $(`.items[data-id="${idx}"] #discount-hint`).addClass('hidden')
            }

            $(`.items[data-id="${idx}"] input[name="amount"]`).val(priceFormat(subtotal - promoAmount - discountAmount))

            calSummary()
        }

        function calSummary() {
            let overallSubtotal = 0
            let overallPromoAmount = 0
            let overallDiscountAmount = 0

            $('.items').each(function(i, obj) {
                let productId = $(this).find('select[name="product_id[]"]').val()
                let qty = $(this).find('input[name="qty"]').val()
                let promo = $(this).find('select[name="promotion[]"]').val()
                let discount = $(this).find('input[name="discount"]').val()
                let sellingPrice = $(this).find(`select[name="selling_price[]"]`).val()
                let overrideSellingPrice = $(this).find(`input[name="override_selling_price"]`).val()

                let unitPrice = 0
                if (overrideSellingPrice != '') {
                    unitPrice = overrideSellingPrice
                } else {
                    for (let i = 0; i < PRODUCTS.length; i++) {
                        if (PRODUCTS[i].id == productId) {
                            for (let j = 0; j < PRODUCTS[i].selling_prices.length; j++) {
                                if (PRODUCTS[i].selling_prices[j].id == sellingPrice) {
                                    unitPrice = PRODUCTS[i].selling_prices[j].price
                                    break
                                }
                            }
                            break;
                        }
                    }
                }
                let subtotal = (qty * unitPrice)

                // Apply Promotion
                let promoAmount = 0
                if (promo != '') {
                    for (let i = 0; i < PROMOTIONS.length; i++) {
                        const element = PROMOTIONS[i];

                        if (element.id == promo) {
                            if (element.type == 'val') {
                                promoAmount = element.amount
                            } else if (element.type == 'perc') {
                                promoAmount = subtotal * element.amount / 100
                            }
                            break
                        }
                    }
                }
                // Apply Discount
                let discountAmount = 0
                if (discount != '' && discount != null) {
                    discountAmount = discount
                }

                overallSubtotal += (subtotal * 1)
                overallPromoAmount += (promoAmount * 1)
                overallDiscountAmount += (discountAmount * 1)
            })

            $('#subtotal').text(priceFormat(overallSubtotal))
            $('#promo-amount').text(priceFormat(overallPromoAmount))
            $('#discount-amount').text(priceFormat(overallDiscountAmount))
            $('#total').text(priceFormat(overallSubtotal - overallPromoAmount - overallDiscountAmount))
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

        function buildSerialNoOptions(product_id, item_id, sale_product_id = null) {
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

        function buildPromotionSelect(item_id, product_id = null) {
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

        function selectedSerialNo($product_child_id, sale_product_id = null) {

            if (SALE != null && SALE.products != null) {
                for (let i = 0; i < SALE.products.length; i++) {
                    const prod = SALE.products[i];

                    if (prod.children != undefined) {
                        for (let k = 0; k < prod.children.length; k++) {
                            const elem = prod.children[k];

                            if (prod.id == sale_product_id && elem.product_children_id == $product_child_id) {
                                return true
                            }
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
