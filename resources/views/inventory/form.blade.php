@extends('layouts.app')

@section('content')
    <div class="mb-6 flex justify-between items-center">
        <x-app.page-title>
            {{ $is_product ? (isset($prod) ? 'Edit Product - ' . $prod->sku : 'Create Product') : (isset($prod) ? 'Edit Raw Material - ' . $prod->sku : 'Create Raw Material') }}
        </x-app.page-title>
    </div>
    <!-- Info -->
    <div class="bg-white p-4 border rounded-md">
        <form action="" method="POST" enctype="multipart/form-data" id="form">
            <div>
                <div class="grid grid-cols-3 gap-8 w-full">
                    <div class="flex flex-col">
                        <x-app.input.label id="model_name" class="mb-1">Model Name <span class="text-sm text-red-500">*</span></x-app.input.label>
                        <x-app.input.input name="model_name" id="model_name" value="{{ isset($prod) ? $prod->model_name : null }}" />
                        <x-app.message.error id="model_name_err"/>
                    </div>
                    <div class="flex flex-col">
                        <x-app.input.label id="model_desc" class="mb-1">Model Description <span class="text-sm text-red-500">*</span></x-app.input.label>
                        <x-app.input.input name="model_desc" id="model_desc" value="{{ isset($prod) ? $prod->model_desc : null }}" />
                        <x-app.message.error id="model_desc_err"/>
                    </div>
                    <div class="flex flex-col">
                        <x-app.input.label id="category_id" class="mb-1">Category <span class="text-sm text-red-500">*</span></x-app.input.label>
                        <x-app.input.select name="category_id" id="category_id">
                            <option value="">Select a category</option>
                            @foreach ($inv_cats as $cat)
                                <option value="{{ $cat->id }}" @selected(old('category_id', isset($prod) ? $prod->inventory_category_id : null) == $cat->id)>{{ $cat->name }}</option>
                            @endforeach
                        </x-app.input.select>
                        <x-app.message.error id="category_id_err"/>
                    </div>
                    @if ($is_product == false)
                        <div class="flex flex-col" id="qty-container">
                            <x-app.input.label id="qty" class="mb-1">Quantity <span class="text-sm text-red-500">*</span></x-app.input.label>
                            <x-app.input.input name="qty" id="qty" class="int-input" value="{{ isset($prod) ? $prod->qty : null }}" />
                            <x-app.message.error id="qty_err"/>
                        </div>
                    @endif
                    <div class="flex flex-col">
                        <x-app.input.label id="low_stock_threshold" class="mb-1">Low Stock Threshold</x-app.input.label>
                        <x-app.input.input name="low_stock_threshold" id="low_stock_threshold" class="int-input" value="{{ isset($prod) ? $prod->low_stock_threshold : null }}" />
                        <x-app.message.error id="low_stock_threshold_err"/>
                    </div>
                    <div class="flex flex-col">
                        <x-app.input.label id="price" class="mb-1">Price <span class="text-sm text-red-500">*</span></x-app.input.label>
                        <x-app.input.input name="price" id="price" class="decimal-input" value="{{ isset($prod) ? $prod->price : null }}"/>
                        <x-app.message.error id="price_err"/>
                    </div>
                    <div class="flex flex-col">
                        <x-app.input.label id="weight" class="mb-1">Weight (In KG)</x-app.input.label>
                        <x-app.input.input name="weight" id="weight" class="decimal-input" value="{{ isset($prod) ? $prod->weight : null }}"/>
                        <x-app.message.error id="weight_err"/>
                    </div>
                    <div class="flex flex-col">
                        <x-app.input.label class="mb-1">Dimension (L x W x H) (In CM)</x-app.input.label>
                        <div class="flex gap-x-2">
                            <div class="bg-gray-100 flex items-center">
                                <span class="font-black p-2">L</span>
                                <x-app.input.input name="dimension_length" id="dimension_length" class="decimal-input" value="{{ isset($prod) ? $prod->length : null }}"/>
                            </div>
                            <div class="bg-gray-100 flex items-center">
                                <span class="font-black p-2">W</span>
                                <x-app.input.input name="dimension_width" id="dimension_width" class="decimal-input" value="{{ isset($prod) ? $prod->width : null }}"/>
                            </div>
                            <div class="bg-gray-100 flex items-center">
                                <span class="font-black p-2">H</span>
                                <x-app.input.input name="dimension_height" id="dimension_height" class="decimal-input" value="{{ isset($prod) ? $prod->height : null }}"/>
                            </div>
                        </div>
                        <x-app.message.error id="dimension_err"/>
                    </div>
                    <div class="flex flex-col">
                        <x-app.input.label id="status" class="mb-1">Status <span class="text-sm text-red-500">*</span></x-app.input.label>
                        <x-app.input.select name="status" id="status">
                            <option value="">Select a Active/Inactive</option>
                            <option value="1" @selected(old('status', isset($prod) ? $prod->is_active : null) == '1')>Active</option>
                            <option value="0" @selected(old('status', isset($prod) ? $prod->is_active : null) === '0')>Inactive</option>
                        </x-app.input.select>
                        <x-app.message.error id="status_err"/>
                    </div>
                    <div class="flex flex-col">
                        <x-app.input.label class="mb-1">Image <span class="text-sm text-red-500">*</span></x-app.input.label>
                        <x-app.input.file id="image[]" :hasError="$errors->has('image')"/>
                        <x-app.message.error id="image_err"/>
                        <div class="uploaded-file-preview-container" data-id="image">
                            <div class="p-y.5 px-1.5 rounded bg-blue-50 mt-2 hidden" id="uploaded-file-template">
                                <a href="" target="_blank" class="text-blue-700 text-xs"></a>
                            </div>
                            @if (isset($prod))
                                <div class="p-y.5 px-1.5 rounded bg-blue-50 mt-2 old-preview">
                                    <a href="{{ $prod->image->url }}" target="_blank" class="text-blue-700 text-xs">{{ $prod->image->src }}</a>
                                </div>
                            @endif
                        </div>
                    </div>
                    @if ($is_product == false)
                        <div class="flex flex-col">
                            <x-app.input.label id="supplier_id" class="mb-1">Supplier <span class="text-sm text-red-500">*</span></x-app.input.label>
                            <x-app.input.select name="supplier_id" id="supplier_id">
                                <option value="">Select a supplier</option>
                                @foreach ($suppliers as $sup)
                                    <option value="{{ $sup->id }}" @selected(old('supplier_id', isset($prod) ? $prod->supplier_id : null) == $sup->id)>{{ $sup->name }}</option>
                                @endforeach
                            </x-app.input.select>
                            <x-app.message.error id="supplier_id_err"/>
                        </div>
                        <div class="flex flex-col">
                            <x-app.input.label id="is_sparepart" class="mb-1">Is Spare part <span class="text-sm text-red-500">*</span></x-app.input.label>
                            <x-app.input.select name="is_sparepart" id="is_sparepart">
                                <option value="">Select a Yes/No</option>
                                <option value="1" @selected(old('is_sparepart', isset($prod) ? $prod->is_sparepart : null) == '1')>Yes</option>
                                <option value="0" @selected(old('is_sparepart', isset($prod) ? $prod->is_sparepart : null) == '0')>No</option>
                            </x-app.input.select>
                            <x-app.message.error id="is_sparepart_err"/>
                        </div>
                    @endif
                </div>
            </div>
            <div class="mt-8 flex justify-end">
                <x-app.button.submit id="submit-btn">Save and Update</x-app.button.submit>
            </div>
        </form>
    </div>
    <!-- Serial No -->
    <div class="bg-white p-4 border rounded-md mt-6" id="serial-no-container">
        <div class="mb-2 flex items-center justify-between">
            <h6 class="font-medium text-lg">Serial No</h6>
            <span class="text-sm text-slate-500">Serial No Qty: <span id="serial-no-qty"></span></span>
        </div>
        <x-app.input.input name="serial_no_ipt" id="serial_no_ipt" placeholder="Enter Serial No" />
        <form action="" method="POST" enctype="multipart/form-data" id="serial-no-form">
            <ul class="my-2" id="serial_no_list">
                <li class="hidden group flex items-center rounded hover:bg-slate-100" id="serial-no-template">
                    <input type="hidden" name="serial_no[]">
                    <div class="flex justify-between w-full">
                        <div class="py-1 px-1.5 flex items-center">
                            <svg class="h-4 w-4 mr-1 fill-blue-500" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M12,7c-2.76,0-5,2.24-5,5s2.24,5,5,5,5-2.24,5-5-2.24-5-5-5Zm0,8c-1.65,0-3-1.35-3-3s1.35-3,3-3,3,1.35,3,3-1.35,3-3,3Z"/></svg>
                            <span class="text-sm"></span>
                        </div>
                        <button type="button" class="bg-rose-400 p-1.5 rounded text-white text-xs font-semibold opacity-0 group-hover:opacity-100 delete-serial-no-btns" title="Remove">
                            Remove
                        </button>
                    </div>
                </li>
            </ul>
            <x-app.message.error id="serial_no_err"/>
            <div class="mt-8 flex justify-end">
                <x-app.button.submit id="submit-btn">Save and Update</x-app.button.submit>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
    IS_PRODUCT = @json($is_product);
    PRODUCT = @json($prod ?? null);
    FORM_CAN_SUBMIT = true
    SERIAL_NO_FORM_CAN_SUBMIT = true
    SERIAL_NO_COUNT = 0

    $(document).ready(function(){
        if (PRODUCT != null) {
            for (let i = 0; i < PRODUCT.children.length; i++) {
                const child = PRODUCT.children[i];

                addSerialNo(child.sku, child.id)
            }

            $('select[name="is_sparepart"]').trigger('change')
        }
    })
    $('input[name="image[]"]').on('change', function() {
        let files = $(this).prop('files');

        $('.uploaded-file-preview-container[data-id="image"]').find('.old-preview').remove()

        for (let i = 0; i < files.length; i++) {
            const file = files[i];

            let clone = $('#uploaded-file-template')[0].cloneNode(true);
            $(clone).find('a').text(file.name)
            $(clone).find('a').attr('href', URL.createObjectURL(file))
            $(clone).addClass('old-preview')
            $(clone).removeClass('hidden')
            $(clone).removeAttr('id')

            $('.uploaded-file-preview-container[data-id="image"]').append(clone)
            $('.uploaded-file-preview-container[data-id="image"]').removeClass('hidden')
        }
    })
    $('input[name="serial_no_ipt"]').on('keypress', function(e) {
        if (e.key == ',') e.preventDefault()

        let val = $(this).val()

        if (val != '' && e.key.toLowerCase() == 'enter') {
            addSerialNo(val)

            $(this).val(null) // Reset
        }
    })
    $('body').on('click', '.delete-serial-no-btns', function() {
        let id = $(this).parent().parent().data('id')

        $(`.serial-no[data-id="${id}"]`).remove()

        calSerialNoQty()
    })
    $('select[name="is_sparepart"]').on('change', function() {
        let val = $(this).val()

        if (val == true) {
            $('#serial-no-container').removeClass('hidden')
            $('#qty-container').addClass('hidden')
        } else {
            $('#serial-no-container').addClass('hidden')
            $('#qty-container').removeClass('hidden')
        }
    })
    $('#form').on('submit', function(e) {
        e.preventDefault()

        if (!FORM_CAN_SUBMIT) return

        FORM_CAN_SUBMIT = false

        $('#form #submit-btn').text('Updating')
        $('#form #submit-btn').removeClass('bg-yellow-400 shadow')
        $('.err_msg').addClass('hidden') // Remove error messages
        // Submit
        let url = '{{ route("product.upsert") }}'
        url = `${url}?is_product=${IS_PRODUCT}`

        var formData = new FormData(this);

        if (PRODUCT != null) formData.append('product_id', PRODUCT != null ? PRODUCT.id : null)

        let picture = $('input[name="image[]"]').prop('files')
        if (picture.length > 0) formData.append('image[]', picture)

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: url,
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(res) {
                if (PRODUCT == null) {
                    PRODUCT = res.product
                }

                setTimeout(() => {
                    $('#form #submit-btn').text('Updated')
                    $('#form #submit-btn').addClass('bg-green-400 shadow')

                    setTimeout(() => {
                        $('#form #submit-btn').text('Save and Update')
                        $('#form #submit-btn').removeClass('bg-green-400')
                        $('#form #submit-btn').addClass('bg-yellow-400 shadow')

                        FORM_CAN_SUBMIT = true
                    }, 2000);
                }, 300);
            },
            error: function(err) {
                setTimeout(() => {
                    if (err.status == StatusCodes.UNPROCESSABLE_ENTITY) {
                        let errors = err.responseJSON.errors

                        for (const key in errors) {
                            if (key.includes('picture')) {
                                $(`#form #image_err`).find('p').text(errors[key])
                                $(`#form #image_err`).removeClass('hidden')
                            } else {
                                $(`#form #${key}_err`).find('p').text(errors[key])
                                $(`#form #${key}_err`).removeClass('hidden')
                            }
                        }
                    }
                    $('#form #submit-btn').text('Save and Update')
                    $('#form #submit-btn').addClass('bg-yellow-400 shadow')

                    FORM_CAN_SUBMIT = true
                }, 300);
            },
        });
    })
    $('#serial-no-form').on('submit', function(e) {
        e.preventDefault()

        if (!SERIAL_NO_FORM_CAN_SUBMIT) return

        SERIAL_NO_FORM_CAN_SUBMIT = false

        $('#serial-no-form #submit-btn').text('Updating')
        $('#serial-no-form #submit-btn').removeClass('bg-yellow-400 shadow')
        $('.err_msg').addClass('hidden') // Remove error messages
        // Submit
        let url = '{{ route("product.upsert_serial_no") }}'

        let orderId = []
        let serialNo = []
        $('#serial-no-form .serial-no').each(function(i, obj) {
            orderId.push($(this).data('order-id') ?? null)
            serialNo.push($(this).find('input[name="serial_no[]"]').val())
        })

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: url,
            type: 'POST',
            data: {
                'product_id': PRODUCT != null ? PRODUCT.id : null,
                'order_idx': orderId,
                'serial_no': serialNo
            },
            success: function(res) {
                if (res.product_children_ids.length > 0) {
                    let product_children_ids = res.product_children_ids
                    $('#serial-no-form .serial-no').each(function(i, obj) {
                        $(this).attr('data-order-id', product_children_ids[i])
                    })
                }

                setTimeout(() => {
                    $('#serial-no-form #submit-btn').text('Updated')
                    $('#serial-no-form #submit-btn').addClass('bg-green-400 shadow')

                    setTimeout(() => {
                        $('#serial-no-form #submit-btn').text('Save and Update')
                        $('#serial-no-form #submit-btn').removeClass('bg-green-400')
                        $('#serial-no-form #submit-btn').addClass('bg-yellow-400 shadow')

                        SERIAL_NO_FORM_CAN_SUBMIT = true
                    }, 2000);
                }, 300);
            },
            error: function(err) {
                setTimeout(() => {
                    if (err.status == StatusCodes.UNPROCESSABLE_ENTITY) {
                        let errors = err.responseJSON.errors

                        for (const key in errors) {
                            $(`#serial-no-form #serial_no_err`).find('p').text(errors[key])
                            $(`#serial-no-form #serial_no_err`).removeClass('hidden')
                        }
                    } else if (err.status == StatusCodes.BAD_REQUEST) {
                        $(`#serial-no-form #serial_no_err`).find('p').text(err.responseJSON.serial_no)
                        $(`#serial-no-form #serial_no_err`).removeClass('hidden')
                    }
                    $('#serial-no-form #submit-btn').text('Save and Update')
                    $('#serial-no-form #submit-btn').addClass('bg-yellow-400 shadow')

                    SERIAL_NO_FORM_CAN_SUBMIT = true
                }, 300);
            },
        });
    })

    function addSerialNo(val, order_id=null) {
        SERIAL_NO_COUNT++
        let clone = $('#serial-no-template')[0].cloneNode(true);

        if (order_id != null) $(clone).attr('data-order-id', order_id)
        $(clone).attr('data-id', SERIAL_NO_COUNT)
        $(clone).addClass('serial-no')
        $(clone).find('span').text(val)
        $(clone).find('input').val(val)
        $(clone).removeClass('hidden')
        $(clone).removeAttr('id')

        $('#serial_no_list').prepend(clone)

        calSerialNoQty()
    }
    function calSerialNoQty() {
        $('#serial-no-qty').text( $('#serial-no-form .serial-no').length )
    }
</script>
@endpush
