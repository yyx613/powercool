@extends('layouts.app')

@section('content')
    <div class="mb-6">
        <x-app.page-title url="{{ $is_product ? route('product.index') : route('raw_material.index') }}">
            {{ __($is_product ? (isset($prod) ? 'Edit Product - ' . $prod->sku : 'Create Product') : (isset($prod) ? 'Edit Raw Material - ' . $prod->sku : 'Create Raw Material')) }}
        </x-app.page-title>
    </div>
    @include('components.app.alert.parent')
    <form action="{{ $is_product ? route('product.upsert', ['is_product' => true]) : route('raw_material.upsert') }}" method="POST" enctype="multipart/form-data" id="form">
        @csrf
        @if (isset($prod))
            <x-app.input.input name="product_id" id="product_id" value="{{ $prod->id }}" class="hidden" />
        @endif
        <!-- Info -->
        <div class="bg-white p-4 border rounded-md">
            <div class="grid grid-cols-3 gap-8 w-full">
                <div class="flex flex-col">
                    <x-app.input.label id="model_code" class="mb-1">{{ __('Model Code') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="model_code" id="model_code" value="{{ old('model_code', isset($prod) ? $prod->sku : null) }}" />
                    <x-input-error :messages="$errors->get('model_code')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="model_name" class="mb-1">{{ __('Model Name') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="model_name" id="model_name" value="{{ old('model_name', isset($prod) ? $prod->model_name : ($dup_prod != null ? $dup_prod->model_name : null)) }}" />
                    <x-input-error :messages="$errors->get('model_name')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="model_desc" class="mb-1">{{ __('Model Description') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="model_desc" id="model_desc" value="{{ old('model_desc', isset($prod) ? $prod->model_desc : ($dup_prod != null ? $dup_prod->model_desc : null)) }}" />
                    <x-input-error :messages="$errors->get('model_desc')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="barcode" class="mb-1">{{ __('Barcode') }}</x-app.input.label>
                    <x-app.input.input name="barcode" id="barcode" value="{{ old('barcode', isset($prod) ? $prod->barcode : ($dup_prod != null ? $dup_prod->barcode : null)) }}" />
                    <x-input-error :messages="$errors->get('barcode')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="uom" class="mb-1">{{ __('UOM') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select name="uom" id="uom">
                        <option value="">{{ __('Select a UOM') }}</option>
                        @foreach ($uoms as $uom)
                            <option value="{{ $uom->id }}" @selected(old('uom', isset($prod) ? $prod->uom : ($dup_prod != null ? $dup_prod->uom : null)) == $uom->id)>{{ $uom->name }}</option>
                        @endforeach
                    </x-app.input.select>
                    <x-input-error :messages="$errors->get('uom')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="category_id" class="mb-1">{{ __('Category') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select name="category_id" id="category_id">
                        <option value="">{{ __('Select a category') }}</option>
                        @foreach ($inv_cats as $cat)
                            <option value="{{ $cat->id }}" @selected(old('category_id', isset($prod) ? $prod->inventory_category_id : ($dup_prod != null ? $dup_prod->inventory_category_id : null)) == $cat->id)>{{ $cat->name }}</option>
                        @endforeach
                    </x-app.input.select>
                    <x-input-error :messages="$errors->get('category_id')" class="mt-1" />
                </div>
                @if ($is_product == false)
                    <div class="flex flex-col" id="qty-container">
                        <x-app.input.label id="qty" class="mb-1">{{ __('Quantity') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                        <x-app.input.input name="qty" id="qty" class="int-input" value="{{ old('qty', isset($prod) ? $prod->qty : ($dup_prod != null ? $dup_prod->qty : null)) }}" />
                        <x-input-error :messages="$errors->get('qty')" class="mt-1" />
                    </div>
                @endif
                <div class="flex flex-col">
                    <x-app.input.label id="low_stock_threshold" class="mb-1">{{ __('Low Stock Threshold') }}</x-app.input.label>
                    <x-app.input.input name="low_stock_threshold" id="low_stock_threshold" class="int-input" value="{{ old('low_stock_threshold', isset($prod) ? $prod->low_stock_threshold : ($dup_prod != null ? $dup_prod->low_stock_threshold : null)) }}" />
                    <x-input-error :messages="$errors->get('low_stock_threshold')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="min_price" class="mb-1">{{ __('Selling Price') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <div class="flex gap-x-4">
                        <x-app.input.input name="min_price" id="min_price" class="decimal-input flex-1" value="{{ old('min_price', isset($prod) ? $prod->min_price : ($dup_prod != null ? $dup_prod->min_price : null)) }}"/>
                        <x-app.input.input name="max_price" id="max_price" class="decimal-input flex-1" value="{{ old('max_price', isset($prod) ? $prod->max_price : ($dup_prod != null ? $dup_prod->max_price : null)) }}"/>
                    </div>
                    <x-input-error :messages="$errors->get('min_price')" class="mt-1" />
                    <x-input-error :messages="$errors->get('max_price')" class="mt-1" />
                </div>
                <div class="flex flex-col" id="cost-container">
                    <x-app.input.label id="cost" class="mb-1">{{ __('Cost') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="cost" id="cost" class="decimal-input flex-1" value="{{ old('cost', isset($prod) ? $prod->cost : ($dup_prod != null ? $dup_prod->cost : null)) }}"/>
                    <x-input-error :messages="$errors->get('cost')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="weight" class="mb-1">{{ __('Weight (In KG)') }}</x-app.input.label>
                    <x-app.input.input name="weight" id="weight" class="decimal-input" value="{{ old('weight', isset($prod) ? $prod->weight : ($dup_prod != null ? $dup_prod->weight : null)) }}"/>
                    <x-input-error :messages="$errors->get('weight')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label class="mb-1">{{ __('Dimension (LxWxH) (In CM)') }}</x-app.input.label>
                    <div class="flex gap-x-2">
                        <div class="bg-gray-100 flex items-center">
                            <span class="font-black p-2">L</span>
                            <x-app.input.input name="dimension_length" id="dimension_length" class="decimal-input" value="{{ old('dimension_length', isset($prod) ? $prod->length : ($dup_prod != null ? $dup_prod->length : null)) }}"/>
                        </div>
                        <div class="bg-gray-100 flex items-center">
                            <span class="font-black p-2">W</span>
                            <x-app.input.input name="dimension_width" id="dimension_width" class="decimal-input" value="{{ old('dimension_width', isset($prod) ? $prod->width : ($dup_prod != null ? $dup_prod->width : null)) }}"/>
                        </div>
                        <div class="bg-gray-100 flex items-center">
                            <span class="font-black p-2">H</span>
                            <x-app.input.input name="dimension_height" id="dimension_height" class="decimal-input" value="{{ old('dimension_height', isset($prod) ? $prod->height : ($dup_prod != null ? $dup_prod->height : null)) }}"/>
                        </div>
                    </div>
                    <x-input-error :messages="$errors->get('dimension_length')" class="mt-1" />
                    <x-input-error :messages="$errors->get('dimension_width')" class="mt-1" />
                    <x-input-error :messages="$errors->get('dimension_height')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="status" class="mb-1">{{ __('Status') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select name="status" id="status">
                        <option value="">{{ __('Select a Active/Inactive') }}</option>
                        <option value="1" @selected(old('status', isset($prod) ? $prod->is_active : ($dup_prod != null ? $dup_prod->is_active : null)) == 1)>Active</option>
                        <option value="0" @selected(old('status', isset($prod) ? $prod->is_active : ($dup_prod != null ? $dup_prod->is_active : null)) === 0)>Inactive</option>
                    </x-app.input.select>
                    <x-input-error :messages="$errors->get('status')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label class="mb-1">{{ __('Image') }}</x-app.input.label>
                    <x-app.input.file id="image[]" :hasError="$errors->has('image')"/>
                    <x-input-error :messages="$errors->get('image')" class="mt-1" />
                    <div class="uploaded-file-preview-container" data-id="image">
                        <div class="p-y.5 px-1.5 rounded bg-blue-50 mt-2 hidden" id="uploaded-file-template">
                            <a href="" target="_blank" class="text-blue-700 text-xs"></a>
                        </div>
                        @if (isset($prod) && $prod->image != null)
                            <div class="p-y.5 px-1.5 rounded bg-blue-50 mt-2 old-preview">
                                <a href="{{ $prod->image->url }}" target="_blank" class="text-blue-700 text-xs">{{ $prod->image->src }}</a>
                            </div>
                        @endif
                    </div>
                </div>
                @if ($is_product == false)
                    <div class="flex flex-col">
                        <x-app.input.label id="supplier_id" class="mb-1">{{ __('Supplier') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                        <x-app.input.select name="supplier_id" id="supplier_id">
                            <option value="">{{ __('Select a supplier') }}</option>
                            @foreach ($suppliers as $sup)
                                <option value="{{ $sup->id }}" @selected(old('supplier_id', isset($prod) ? $prod->supplier_id : null) == $sup->id)>{{ $sup->name }}</option>
                            @endforeach
                        </x-app.input.select>
                        <x-input-error :messages="$errors->get('supplier_id')" class="mt-1" />
                    </div>
                    <div class="flex flex-col">
                        <x-app.input.label id="is_sparepart" class="mb-1">{{ __('Is Spare part') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                        <x-app.input.select name="is_sparepart" id="is_sparepart">
                            <option value="">{{ __('Select a Yes/No') }}</option>
                            <option value="1" @selected(old('is_sparepart', isset($prod) ? $prod->is_sparepart : null) == '1')>Yes</option>
                            <option value="0" @selected(old('is_sparepart', isset($prod) ? $prod->is_sparepart : null) == '0')>No</option>
                        </x-app.input.select>
                        <x-input-error :messages="$errors->get('is_sparepart')" class="mt-1" />
                    </div>
                @endif
            </div>
            @if (!$is_product)
                <div class="mt-8 flex justify-end gap-x-4" id="info-submit-container">
                    @if (!isset($prod))
                        <x-app.button.submit id="submit-create-btn">{{ __('Save and Create') }}</x-app.button.submit>
                    @endif
                    <x-app.button.submit id="submit-btn">{{ __('Save and Update') }}</x-app.button.submit>
                </div>
            @endif
        </div>

        <div class="bg-white p-4 border rounded-md mt-4">
            <div class="grid grid-cols-3 gap-8 w-full">
                <div class="flex flex-col">
                    <x-app.input.label id="lazada_sku" class="mb-1">{{ __('Lazada Sku') }}</x-app.input.label>
                    <x-app.input.input name="lazada_sku" id="lazada_sku" value="{{ old('lazada_sku', isset($prod) ? $prod->lazada_sku : ($dup_prod != null ? $dup_prod->lazada_sku : null)) }}" />
                    <x-input-error :messages="$errors->get('lazada_sku')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="shopee_sku" class="mb-1">{{ __('Shopee Sku') }}</x-app.input.label>
                    <x-app.input.input name="shopee_sku" id="shopee_sku" value="{{ old('shopee_sku', isset($prod) ? $prod->shopee_sku : ($dup_prod != null ? $dup_prod->shopee_sku : null)) }}" />
                    <x-input-error :messages="$errors->get('shopee_sku')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="tiktok_sku" class="mb-1">{{ __('Tiktok Sku') }}</x-app.input.label>
                    <x-app.input.input name="tiktok_sku" id="tiktok_sku" value="{{ old('tiktok_sku', isset($prod) ? $prod->tiktok_sku : ($dup_prod != null ? $dup_prod->tiktok_sku : null)) }}" />
                    <x-input-error :messages="$errors->get('tiktok_sku')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="woo_commerce_sku" class="mb-1">{{ __('Woo Commerce Sku') }}</x-app.input.label>
                    <x-app.input.input name="woo_commerce_sku" id="woo_commerce_sku" value="{{ old('woo_commerce_sku', isset($prod) ? $prod->woo_commerce_sku : ($dup_prod != null ? $dup_prod->woo_commerce_sku : null)) }}" />
                    <x-input-error :messages="$errors->get('woo_commerce_sku')" class="mt-1" />
                </div>
               
               
            </div>
            @if (!$is_product)
                <div class="mt-8 flex justify-end gap-x-4" id="info-submit-container">
                    @if (!isset($prod))
                        <x-app.button.submit id="submit-create-btn">{{ __('Save and Create') }}</x-app.button.submit>
                    @endif
                    <x-app.button.submit id="submit-btn">{{ __('Save and Update') }}</x-app.button.submit>
                </div>
            @endif
        </div>

        
        <!-- Serial No -->
        <div class="bg-white p-4 border rounded-md mt-6" id="serial-no-container">
            <div class="mb-2 flex items-center justify-between">
                <h6 class="font-medium text-lg">{{ __('Serial No') }}</h6>
                <span class="text-sm text-slate-500">{{ __('Serial No Qty:') }} <span id="serial-no-qty">0</span></span>
            </div>
            <x-app.input.input name="serial_no_ipt" id="serial_no_ipt" placeholder="{{ __('Enter Serial No') }}" />
            <x-app.input.input name="order_idx" id="order_idx" class="hidden"/>
            <ul class="my-2" id="serial_no_list">
                <!-- Template -->
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
            <x-input-error :messages="$errors->get('serial_no')" class="mt-1" />
            <div class="mt-8 flex justify-end gap-x-4" id="info-serial-no-container">
                @if (!isset($prod))
                    <x-app.button.submit id="submit-create-btn">{{ __('Save and Create') }}</x-app.button.submit>
                @endif
                <x-app.button.submit id="submit-btn">{{ __('Save and Update') }}</x-app.button.submit>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    IS_PRODUCT = @json($is_product);
    PRODUCT = @json($prod ?? null);
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
    $('#submit-create-btn').on('click', function(e) {
        let url = $('#form').attr('action')
        url = `${url}?create_again=true`

        $('#form').attr('action', url)
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

            e.preventDefault();
            return false;
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
            $('#serial-no-container, #cost-container').removeClass('hidden')
            $('#qty-container').addClass('hidden')
            $('#form #info-submit-container').addClass('hidden')
            $('#form #info-submit-container').removeClass('block')
        } else {
            $('#serial-no-container, #cost-container').addClass('hidden')
            $('#qty-container').removeClass('hidden')
            $('#form #info-submit-container').addClass('block')
            $('#form #info-submit-container').removeClass('hidden')
        }
    })
    $('#form').one('submit', function(e) {
        e.preventDefault()

        let orderId = []
        $('.serial-no').each(function(i, obj) {
            orderId.push($(this).data('order-id') ?? null)
        })

        $('input[name="order_idx"]').val(JSON.stringify(orderId))

        $(this).submit()
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
        $('#serial-no-qty').text( $('.serial-no').length )
    }
</script>
@endpush
