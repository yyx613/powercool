<div class="bg-white p-4 border rounded-md">
    <div class="flex items-center mb-6 border-l-8 border-yellow-400 px-3 py-1 bg-yellow-50 w-fit">
        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M19.5,16c0,.553-.447,1-1,1h-2c-.553,0-1-.447-1-1s.447-1,1-1h2c.553,0,1,.447,1,1Zm4.5-1v5c0,2.206-1.794,4-4,4H4c-2.206,0-4-1.794-4-4v-5c0-2.206,1.794-4,4-4h1V4C5,1.794,6.794,0,9,0h6c2.206,0,4,1.794,4,4v7h1c2.206,0,4,1.794,4,4ZM7,11h10V4c0-1.103-.897-2-2-2h-6c-1.103,0-2,.897-2,2v7Zm-3,11h7V13H4c-1.103,0-2,.897-2,2v5c0,1.103,.897,2,2,2Zm18-7c0-1.103-.897-2-2-2h-7v9h7c1.103,0,2-.897,2-2v-5Zm-14.5,0h-2c-.553,0-1,.447-1,1s.447,1,1,1h2c.553,0,1-.447,1-1s-.447-1-1-1ZM14,5c0-.553-.447-1-1-1h-2c-.553,0-1,.447-1,1s.447,1,1,1h2c.553,0,1-.447,1-1Z"/></svg>
        <span class="text-lg ml-3 font-bold">Product Details</span>
    </div>
    <form action="" method="POST" enctype="multipart/form-data" id="product-form">
        @csrf
        <div>
            <div class="grid grid-cols-4 gap-8 w-full mb-8 p-4 rounded-md relative group hidden transition durtion-300 hover:bg-slate-50" id="item-template">
                <button type="button" class="bg-rose-400 p-2 rounded-full absolute top-[-5px] right-[-5px] hidden group-hover:block delete-item-btns" title="Delete Product">
                    <svg class="h-3 w-3 fill-white" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M13.93,12L21.666,2.443c.521-.644,.422-1.588-.223-2.109-.645-.522-1.588-.421-2.109,.223l-7.334,9.06L4.666,.557c-1.241-1.519-3.56,.357-2.332,1.887l7.736,9.557L2.334,21.557c-.521,.644-.422,1.588,.223,2.109,.64,.519,1.586,.424,2.109-.223l7.334-9.06,7.334,9.06c.524,.647,1.47,.742,2.109,.223,.645-.521,.744-1.466,.223-2.109l-7.736-9.557Z"/></svg>
                </button>
                <div class="flex flex-col">
                    <x-app.input.label class="mb-1">Product Name <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select name="product_id[]">
                        <option value=""></option>
                    </x-app.input.select>
                    <x-app.message.error id="product_id_err"/>
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="qty" class="mb-1">Quantity <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="qty" id="qty" :hasError="$errors->has('qty')" class="int-input" />
                    <x-app.message.error id="qty_err"/>
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="unit_price" class="mb-1">Unit Price <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="unit_price" id="unit_price" :hasError="$errors->has('unit_price')" class="decimal-input" step=".01"/>
                    <x-app.message.error id="unit_price_err"/>
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="amount" class="mb-1">Amount</x-app.input.label>
                    <x-app.input.input name="amount" id="amount" :hasError="$errors->has('amount')" disabled="true" />
                    <x-app.message.error id="amount_err"/>
                </div>
                <div class="flex flex-col col-span-3">
                    <x-app.input.label id="product_desc" class="mb-1">Product Description</x-app.input.label>
                    <x-app.input.input name="product_desc" id="product_desc" :hasError="$errors->has('product_desc')" />
                    <x-app.message.error id="product_desc_err"/>
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="warranty_period" class="mb-1">Warranty Period <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="warranty_period" id="warranty_period" :hasError="$errors->has('warranty_period')" value="dummy text" />
                    <x-app.message.error id="warranty_period_err"/>
                </div>
                <div class="flex flex-col col-span-4">
                    <x-app.input.label id="product_serial_no" class="mb-1">Product Serial No</x-app.input.label>
                    <x-app.input.select name="product_serial_no[]" multiple class="h-36">
                    </x-app.input.select>
                    <x-app.message.error id="product_serial_no_err"/>
                </div>
            </div>
            <div id="items-container"></div>
            <!-- Add Items -->
            <div class="flex justify-end px-4">
                <button type="button" class="bg-yellow-400 rounded-md py-1.5 px-3 flex items-center gap-x-2 transition duration-300 hover:bg-yellow-300 hover:shadow" id="add-item-btn">
                    <svg class="h-3 w-3" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Capa_1" x="0px" y="0px" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve" width="512" height="512">
                        <path d="M480,224H288V32c0-17.673-14.327-32-32-32s-32,14.327-32,32v192H32c-17.673,0-32,14.327-32,32s14.327,32,32,32h192v192   c0,17.673,14.327,32,32,32s32-14.327,32-32V288h192c17.673,0,32-14.327,32-32S497.673,224,480,224z"/>
                    </svg>
                    <span class="text-sm">Add Item</span>
                </button>
            </div>
            <!-- Total -->
            <div class="flex justify-end mt-6 pt-6 border-t px-4 pb-4">
                <table>
                    <tbody>
                        <tr>
                            <td>Subtotal</td>
                            <td class="w-4 text-center">:</td>
                            <td id="subtotal">0.00</td>
                        </tr>
                        <tr>
                            <td>Promo</td>
                            <td class="w-4 text-center">:</td>
                            <td>0.00</td>
                        </tr>
                        <tr>
                            <td>Tax</td>
                            <td class="w-4 text-center">:</td>
                            <td>0.00</td>
                        </tr>
                        <tr>
                            <td>Total</td>
                            <td class="w-4 text-center">:</td>
                            <td id="total">0.00</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-8 flex justify-end">
            <x-app.button.submit id="submit-btn">Save and Update</x-app.button.submit>
        </div>
    </form>
</div>


@push('scripts')
<script>
    PRODUCTS = @json($products ?? []);
    PRODUCT_FORM_CAN_SUBMIT = true
    ITEMS_COUNT = 0

    $(document).ready(function(){
        if (SALE != null) {
            for (let i = 0; i < SALE.products.length; i++) {
                const sp = SALE.products[i];

                $('#add-item-btn').click()

                $(`.items[data-id="${i+1}"]`).attr('data-product-id', sp.id)
                $(`.items[data-id="${i+1}"] select[name="product_id[]"]`).val(sp.product_id)
                $(`.items[data-id="${i+1}"] input[name="qty"]`).val(sp.qty)
                $(`.items[data-id="${i+1}"] input[name="unit_price"]`).val(sp.unit_price)
                $(`.items[data-id="${i+1}"] input[name="product_desc"]`).val(sp.desc)
                $(`.items[data-id="${i+1}"] input[name="qty"]`).trigger('keyup')

                buildSerialNoOptions(sp.product_id, i+1, sp.id)
            }
            if (SALE.products.length <= 0) $('#add-item-btn').click()
        } else {
            $('#add-item-btn').click()
        }
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
            placeholder: 'Select a product'
        })
        $(`.items[data-id="${ITEMS_COUNT}"] .select2`).addClass('border border-gray-300 rounded-md overflow-hidden')

        for (let i = 0; i < PRODUCTS.length; i++) {
            const element = PRODUCTS[i];
            
            let opt = new Option(element.model_name, element.id)
            $(`.items[data-id="${ITEMS_COUNT}"] select[name="product_id[]"]`).append(opt)
        }
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
        let qty = $(`.items[data-id="${idx}"] input[name="qty"]`).val()
        let unitPrice = $(`.items[data-id="${idx}"] input[name="unit_price"]`).val()

        calItemTotal(idx, qty, unitPrice)
    })
    $('body').on('change', 'select[name="product_id[]"]', function() {
        let id = $(this).parent().parent().attr('data-id')
        let val = $(this).val()

        for (let i = 0; i < PRODUCTS.length; i++) {
            const prod = PRODUCTS[i];
         
            if (prod.id == val) {
                $(`.items[data-id="${id}"] input[name="product_desc"]`).val(prod.model_desc)
                $(`.items[data-id="${id}"] input[name="unit_price"]`).val(prod.price)
                $(`.items[data-id="${id}"] input[name="qty"]`).trigger('keyup')
                break
            }
        }
        buildSerialNoOptions(val, id)
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
        let unitPrice = []
        let prodSerialNo = []
        let warrantyPeriod = []
        $('#product-form .items').each(function(i, obj) {
            prodOrderId.push($(this).data('product-id') ?? null)
            prodId.push($(this).find('select[name="product_id[]"]').val())
            prodDesc.push($(this).find('input[name="product_desc"]').val())
            qty.push($(this).find('input[name="qty"]').val())
            unitPrice.push($(this).find('input[name="unit_price"]').val())
            prodSerialNo.push($(this).find('select[name="product_serial_no[]"]').val())
            warrantyPeriod.push($(this).find('input[name="warranty_period"]').val())
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
                'unit_price': unitPrice,
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
                    }
                    $('#product-form #submit-btn').text('Save and Update')
                    $('#product-form #submit-btn').addClass('bg-yellow-400 shadow')

                    PRODUCT_FORM_CAN_SUBMIT = true
                }, 300);
            },
        });
    })

    function calItemTotal(idx, qty, unit_price) {
        $(`.items[data-id="${idx}"] input[name="amount"]`).val(decimalPlace2(qty * unit_price))

        calSummary()
    }
    function calSummary() {
        let subtotal = 0

        $('.items').each(function(i, obj) {
            let qty = $(this).find('input[name="qty"]').val()
            let unitPrice = $(this).find('input[name="unit_price"]').val()
            
            subtotal += (qty * unitPrice)
        })

        $('#subtotal').text(priceFormat(subtotal))
        $('#total').text(priceFormat(subtotal))
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
</script>
@endpush