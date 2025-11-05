@php
    $title = 'Finish Good';
    if (!$is_product) {
        $title = 'Raw Material';
    }
    if ($is_production) {
        $title = 'Production ' . $title;
    }
@endphp

@extends('layouts.app')
@section('title', $title)

@section('content')
    <div class="mb-6">
        <x-app.page-title url="{{ $is_product ? route('product.index') : route('raw_material.index') }}">
            {{ __($is_product ? (isset($prod) ? 'Edit Finish Good - ' . $prod->sku : 'Create Finish Good') : (isset($prod) ? 'Edit Raw Material - ' . $prod->sku : 'Create Raw Material')) }}
        </x-app.page-title>
    </div>
    @include('components.app.alert.parent')
    <form action="{{ $is_product ? route('product.upsert', ['is_product' => true]) : route('raw_material.upsert') }}"
        method="POST" enctype="multipart/form-data" id="form">
        @csrf
        @if (isset($prod))
            <x-app.input.input name="product_id" id="product_id" value="{{ $prod->id }}" class="hidden" />
        @endif
        <!-- Info -->
        <div class="bg-white p-4 border rounded-md">
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-8 w-full items-start">
                <div class="flex flex-col">
                    <x-app.input.label id="brand" class="mb-1">{{ __('Brand') }} <span
                            class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select2 name="brand" id="brand" :hasError="$errors->has('brand')"
                        placeholder="{{ __('Select a brand') }}">
                        <option value="">{{ __('Select a brand') }}</option>
                        @foreach ($brands as $key => $value)
                            <option value="{{ $key }}" @selected(old('brand', isset($prod) ? $prod->brand : null) == $key)>{{ $value }}</option>
                        @endforeach
                    </x-app.input.select2>
                    <x-input-error :messages="$errors->get('brand')" class="mt-2" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="company_group" class="mb-1">{{ __('Company Group') }} <span
                            class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select2 name="company_group" id="company_group" :hasError="$errors->has('company_group')"
                        placeholder="{{ __('Select a company group') }}">
                        <option value="">{{ __('Select a company group') }}</option>
                        @foreach ($company_group as $key => $value)
                            <option value="{{ $key }}" @selected(old('company_group', isset($prod) ? $prod->company_group : null) == $key)>{{ $value }}</option>
                        @endforeach
                    </x-app.input.select2>
                    <x-input-error :messages="$errors->get('company_group')" class="mt-2" />
                </div>
                @if ($is_product == false)
                    <div class="flex flex-col">
                        <x-app.input.label id="is_sparepart" class="mb-1">{{ __('Is Spare part') }} <span
                                class="text-sm text-red-500">*</span></x-app.input.label>
                        <x-app.input.select name="is_sparepart" id="is_sparepart">
                            <option value="">{{ __('Select a Yes/No') }}</option>
                            <option value="1" @selected(old('is_sparepart', isset($prod) ? $prod->is_sparepart : null) == '1')>Yes</option>
                            <option value="0" @selected(old('is_sparepart', isset($prod) ? $prod->is_sparepart : null) == '0')>No</option>
                        </x-app.input.select>
                        <x-input-error :messages="$errors->get('is_sparepart')" class="mt-1" />
                    </div>
                @endif
                <div class="flex flex-col" id="initial-container">
                    <x-app.input.label id="initial_for_production" class="mb-1">{{ __('Initial For Production') }} <span
                            class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="initial_for_production" id="initial_for_production"
                        value="{{ old('initial_for_production', isset($prod) ? $prod->initial_for_production : null) }}" />
                    <x-input-error :messages="$errors->get('initial_for_production')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="model_code" class="mb-1">{{ __('Model Code / Supplier Barcode Info') }} <span
                            class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="model_code" id="model_code"
                        value="{{ old('model_code', isset($prod) ? $prod->sku : null) }}" />
                    <x-input-error :messages="$errors->get('model_code')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="model_name" class="mb-1">{{ __('Model Name') }} <span
                            class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="model_name" id="model_name"
                        value="{{ old('model_name', isset($prod) ? $prod->model_name : ($dup_prod != null ? $dup_prod->model_name : null)) }}" />
                    <x-input-error :messages="$errors->get('model_name')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="model_desc" class="mb-1">{{ __('Model Description') }} <span
                            class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="model_desc" id="model_desc"
                        value="{{ old('model_desc', isset($prod) ? $prod->model_desc : ($dup_prod != null ? $dup_prod->model_desc : null)) }}" />
                    <x-input-error :messages="$errors->get('model_desc')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="uom" class="mb-1">{{ __('UOM') }} <span
                            class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select name="uom" id="uom">
                        <option value="">{{ __('Select a UOM') }}</option>
                        @foreach ($uoms as $uom)
                            <option value="{{ $uom->id }}" @selected(old('uom', isset($prod) ? $prod->uom : ($dup_prod != null ? $dup_prod->uom : null)) == $uom->id)>{{ $uom->name }}</option>
                        @endforeach
                    </x-app.input.select>
                    <x-input-error :messages="$errors->get('uom')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="category_id" class="mb-1">{{ __('Category') }} <span
                            class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select name="category_id" id="category_id">
                        <option value="">{{ __('Select a category') }}</option>
                        @foreach ($inv_cats as $cat)
                            <option value="{{ $cat->id }}" @selected(old('category_id', isset($prod) ? $prod->inventory_category_id : ($dup_prod != null ? $dup_prod->inventory_category_id : null)) == $cat->id)>{{ $cat->name }}</option>
                        @endforeach
                    </x-app.input.select>
                    <x-input-error :messages="$errors->get('category_id')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="item_type" class="mb-1">{{ __('Item Type') }} <span
                            class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select name="item_type" id="item_type">
                        <option value="">{{ __('Select a item type') }}</option>
                        @foreach ($inventory_types as $key => $value)
                            <option value="{{ $key }}" @selected(old('item_type', isset($prod) ? $prod->item_type : ($dup_prod != null ? $dup_prod->item_type : null)) == $key)>{{ $value }}</option>
                        @endforeach
                    </x-app.input.select>
                    <x-input-error :messages="$errors->get('item_type')" class="mt-1" />
                </div>
                @if ($is_product == false)
                    <div class="flex flex-col" id="qty-container">
                        <x-app.input.label id="qty" class="mb-1">{{ __('Quantity') }} <span
                                class="text-sm text-red-500">*</span></x-app.input.label>
                        <x-app.input.input name="qty" id="qty" class="int-input"
                            value="{{ old('qty', isset($prod) ? $prod->qty : ($dup_prod != null ? $dup_prod->qty : null)) }}" />
                        <x-input-error :messages="$errors->get('qty')" class="mt-1" />
                    </div>
                @endif
                <div class="flex flex-col">
                    <x-app.input.label id="low_stock_threshold"
                        class="mb-1">{{ __('Low Stock Threshold') }}</x-app.input.label>
                    <x-app.input.input name="low_stock_threshold" id="low_stock_threshold" class="int-input"
                        value="{{ old('low_stock_threshold', isset($prod) ? $prod->low_stock_threshold : ($dup_prod != null ? $dup_prod->low_stock_threshold : null)) }}" />
                    <x-input-error :messages="$errors->get('low_stock_threshold')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="min_price" class="mb-1">{{ __('Selling Price') }} <span
                            class="text-sm text-red-500 required-star">*</span></x-app.input.label>
                    <div class="flex gap-4">
                        <x-app.input.input name="min_price" id="min_price" class="flex-1 decimal-input"
                            placeholder="Min Price" value="{{ old('min_price', $prod->min_price ?? null) }}" />
                        <x-app.input.input name="max_price" id="max_price" class="flex-1 decimal-input"
                            placeholder="Max Price" value="{{ old('max_price', $prod->max_price ?? null) }}" />
                    </div>
                    <x-input-error :messages="$errors->get('min_price')" class="mt-1" />
                    <x-input-error :messages="$errors->get('max_price')" class="mt-1" />
                </div>
                <div class="flex flex-col" id="cost-container">
                    <x-app.input.label id="cost" class="mb-1">{{ __('Cost') }} <span
                            class="text-sm text-red-500 required-star">*</span></x-app.input.label>
                    <x-app.input.input name="cost" id="cost" class="decimal-input flex-1"
                        value="{{ old('cost', isset($prod) ? $prod->cost : ($dup_prod != null ? $dup_prod->cost : null)) }}" />
                    <x-input-error :messages="$errors->get('cost')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="status" class="mb-1">{{ __('Status') }} <span
                            class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select name="status" id="status">
                        <option value="">{{ __('Select a Active/Inactive') }}</option>
                        <option value="1" @selected(old('status', isset($prod) ? $prod->is_active : ($dup_prod != null ? $dup_prod->is_active : null)) == 1)>Active</option>
                        <option value="0" @selected(old('status', isset($prod) ? $prod->is_active : ($dup_prod != null ? $dup_prod->is_active : null)) === 0)>Inactive</option>
                    </x-app.input.select>
                    <x-input-error :messages="$errors->get('status')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label class="mb-1">{{ __('Image') }}</x-app.input.label>
                    <x-app.input.file id="image[]" :hasError="$errors->has('image')" multiple />
                    <x-input-error :messages="$errors->get('image')" class="mt-1" />
                    <div class="uploaded-file-preview-container" data-id="image">
                        <div class="p-y.5 px-1.5 rounded bg-blue-50 mt-2 hidden" id="uploaded-file-template">
                            <a href="" target="_blank" class="text-blue-700 text-xs"></a>
                        </div>
                        @if (isset($prod) && $prod->images != null)
                            @foreach ($prod->images as $img)
                                <div class="p-y.5 px-1.5 rounded bg-blue-50 mt-2 old-preview">
                                    <a href="{{ $img->url }}" target="_blank"
                                        class="text-blue-700 text-xs">{{ $img->src }}</a>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
                @if ($is_product == false)
                    <div class="flex flex-col">
                        <x-app.input.label id="supplier_id" class="mb-1">{{ __('Supplier') }} <span
                                class="text-sm text-red-500">*</span></x-app.input.label>
                        <x-app.input.select2 name="supplier_id" id="supplier_id">
                            <option value="">{{ __('Select a supplier') }}</option>
                            @foreach ($suppliers as $sup)
                                <option value="{{ $sup->id }}" @selected(old('supplier_id', isset($prod) ? $prod->supplier_id : null) == $sup->id)>
                                    {{ $sup->company_name }}
                                </option>
                            @endforeach
                        </x-app.input.select2>
                        <x-input-error :messages="$errors->get('supplier_id')" class="mt-1" />
                    </div>
                @endif
                @if ($is_product == true)
                    <div class="flex flex-col hidden" id="hi_ten_stock_code-container">
                        <x-app.input.label id="hi_ten_stock_code"
                            class="mb-1">{{ __('Power Cool stock code') }}</x-app.input.label>
                        <x-app.input.select2 name="hi_ten_stock_code" id="hi_ten_stock_code" :hasError="$errors->has('hi_ten_stock_code')"
                            placeholder="{{ __('Select a Power Cool stock code') }}">
                            <option value="">{{ __('Select a Power Cool stock code') }}</option>
                            @foreach ($hi_ten_products as $hi_ten_prod)
                                <option value="{{ $hi_ten_prod->id }}" @selected(old('hi_ten_stock_code', isset($prod) ? $prod->hi_ten_stock_code : null) == $hi_ten_prod->id)>
                                    {{ $hi_ten_prod->model_name }}</option>
                            @endforeach
                            </x-app.input.select>
                            <x-input-error :messages="$errors->get('hi_ten_stock_code')" class="mt-1" />
                    </div>
                @endif
                @if ($is_product == true)
                    <div class="flex items-center gap-2 h-full">
                        <input type="checkbox" name="sst" id="sst" class="rounded-sm"
                            @checked(old('sst', isset($prod) ? $prod->sst : null)) />
                        <x-app.input.label id="sst">{{ __('SST') }}</x-app.input.label>
                        <x-input-error :messages="$errors->get('sst')" class="mt-1" />
                    </div>
                @endif
            </div>
        </div>
        <!-- Selling Prices -->
        <div class="bg-white p-4 border rounded-md mt-4">
            <div class="mb-2 flex items-center justify-between">
                <h6 class="font-medium text-lg">{{ __('Selling Prices') }}</h6>
            </div>
            <div class="grid grid-cols-2 lg:grid-cols-2 gap-8 w-full mb-4 hidden" id="selling-price-template">
                <div class="flex flex-col col-span-2 lg:col-span-1">
                    <x-app.input.label id="selling_price_name" class="mb-1">{{ __('Name') }}</x-app.input.label>
                    <x-app.input.input name="selling_price_name[]" id="selling_price_name" />
                    <x-input-error :messages="$errors->get('selling_price_name')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="selling_price" class="mb-1">{{ __('Price') }}</x-app.input.label>
                    <x-app.input.input name="selling_price[]" id="selling_price" class="decimal-input" />
                    <x-input-error :messages="$errors->get('selling_price')" class="mt-1" />
                </div>
            </div>
            <div id="selling-price-container">
                @if (old('selling_price_name') != null)
                    @foreach (old('selling_price_name') as $key => $val)
                        <div class="grid grid-cols-2 gap-8 w-full mb-4 selling-prices">
                            <div class="flex flex-col">
                                <x-app.input.label id="selling_price_name"
                                    class="mb-1">{{ __('Name') }}</x-app.input.label>
                                <x-app.input.input name="selling_price_name[]" id="selling_price_name"
                                    value="{{ old('selling_price_name.' . $key) }}" />
                                <x-input-error :messages="$errors->get('selling_price_name.' . $key)" class="mt-1" />
                            </div>
                            <div class="flex flex-col">
                                <x-app.input.label id="selling_price"
                                    class="mb-1">{{ __('Price') }}</x-app.input.label>
                                <x-app.input.input name="selling_price[]" id="selling_price" class="decimal-input"
                                    value="{{ old('selling_price.' . $key) }}" />
                                <x-input-error :messages="$errors->get('selling_price.' . $key)" class="mt-1" />
                            </div>
                        </div>
                    @endforeach
                @elseif (isset($prod))
                    @foreach ($prod->sellingPrices as $sp)
                        <div class="grid grid-cols-2 gap-8 w-full mb-4 selling-prices">
                            <div class="flex flex-col">
                                <x-app.input.label id="selling_price_name"
                                    class="mb-1">{{ __('Name') }}</x-app.input.label>
                                <x-app.input.input name="selling_price_name[]" id="selling_price_name"
                                    value="{{ $sp->name }}" />
                                <x-input-error :messages="$errors->get('selling_price_name')" class="mt-1" />
                            </div>
                            <div class="flex flex-col">
                                <x-app.input.label id="selling_price"
                                    class="mb-1">{{ __('Price') }}</x-app.input.label>
                                <x-app.input.input name="selling_price[]" id="selling_price" class="decimal-input"
                                    value="{{ $sp->price }}" />
                                <x-input-error :messages="$errors->get('selling_price')" class="mt-1" />
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
            <!-- Add Items -->
            <div class="flex justify-end mt-8">
                <button type="button"
                    class="bg-yellow-400 rounded-md py-1.5 px-3 flex items-center gap-x-2 transition duration-300 hover:bg-yellow-300 hover:shadow"
                    id="add-selling-price-btn">
                    <svg class="h-3 w-3" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                        version="1.1" id="Capa_1" x="0px" y="0px" viewBox="0 0 512 512"
                        style="enable-background:new 0 0 512 512;" xml:space="preserve" width="512" height="512">
                        <path
                            d="M480,224H288V32c0-17.673-14.327-32-32-32s-32,14.327-32,32v192H32c-17.673,0-32,14.327-32,32s14.327,32,32,32h192v192   c0,17.673,14.327,32,32,32s32-14.327,32-32V288h192c17.673,0,32-14.327,32-32S497.673,224,480,224z" />
                    </svg>
                    <span class="text-sm">{{ __('Add Item') }}</span>
                </button>
            </div>
        </div>
        <!-- Barcode Details -->
        <div class="bg-white p-4 border rounded-md mt-4">
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-8 w-full">
                <div class="flex flex-col col-span-2 lg:col-span-1">
                    <x-app.input.label class="mb-1">{{ __('Dimension (LxWxH) (In MM)') }}</x-app.input.label>
                    <div class="flex gap-x-2">
                        <div class="bg-gray-100 flex items-center">
                            <span class="font-black p-2">L</span>
                            <x-app.input.input name="dimension_length" id="dimension_length" class="decimal-input"
                                value="{{ old('dimension_length', isset($prod) ? $prod->length : ($dup_prod != null ? $dup_prod->length : null)) }}" />
                        </div>
                        <div class="bg-gray-100 flex items-center">
                            <span class="font-black p-2">W</span>
                            <x-app.input.input name="dimension_width" id="dimension_width" class="decimal-input"
                                value="{{ old('dimension_width', isset($prod) ? $prod->width : ($dup_prod != null ? $dup_prod->width : null)) }}" />
                        </div>
                        <div class="bg-gray-100 flex items-center">
                            <span class="font-black p-2">H</span>
                            <x-app.input.input name="dimension_height" id="dimension_height" class="decimal-input"
                                value="{{ old('dimension_height', isset($prod) ? $prod->height : ($dup_prod != null ? $dup_prod->height : null)) }}" />
                        </div>
                    </div>
                    <x-input-error :messages="$errors->get('dimension_length')" class="mt-1" />
                    <x-input-error :messages="$errors->get('dimension_width')" class="mt-1" />
                    <x-input-error :messages="$errors->get('dimension_height')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="capacity" class="mb-1">{{ __('Capacity') }}</x-app.input.label>
                    <x-app.input.input name="capacity" id="capacity"
                        value="{{ old('capacity', isset($prod) ? $prod->capacity : ($dup_prod != null ? $dup_prod->capacity : null)) }}" />
                    <x-input-error :messages="$errors->get('capacity')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="weight" class="mb-1">{{ __('Weight (In KG)') }}</x-app.input.label>
                    <x-app.input.input name="weight" id="weight" class="decimal-input"
                        value="{{ old('weight', isset($prod) ? $prod->weight : ($dup_prod != null ? $dup_prod->weight : null)) }}" />
                    <x-input-error :messages="$errors->get('weight')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="refrigerant" class="mb-1">{{ __('Refrigerant') }}</x-app.input.label>
                    <x-app.input.input name="refrigerant" id="refrigerant"
                        value="{{ old('refrigerant', isset($prod) ? $prod->refrigerant : ($dup_prod != null ? $dup_prod->refrigerant : null)) }}" />
                    <x-input-error :messages="$errors->get('refrigerant')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="power_input" class="mb-1">{{ __('Power Input') }}</x-app.input.label>
                    <x-app.input.input name="power_input" id="power_input"
                        value="{{ old('power_input', isset($prod) ? $prod->power_input : ($dup_prod != null ? $dup_prod->power_input : null)) }}" />
                    <x-input-error :messages="$errors->get('power_input')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="power_consumption" class="mb-1">{{ __('Power Consumption (KWH/24H)') }}</x-app.input.label>
                    <x-app.input.input name="power_consumption" id="power_consumption"
                        value="{{ old('power_consumption', isset($prod) ? $prod->power_consumption : ($dup_prod != null ? $dup_prod->power_consumption : null)) }}" />
                    <x-input-error :messages="$errors->get('power_consumption')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="voltage_frequency"
                        class="mb-1">{{ __('Voltage / Frequency') }}</x-app.input.label>
                    <x-app.input.input name="voltage_frequency" id="voltage_frequency"
                        value="{{ old('voltage_frequency', isset($prod) ? $prod->voltage_frequency : ($dup_prod != null ? $dup_prod->voltage_frequency : null)) }}" />
                    <x-input-error :messages="$errors->get('voltage_frequency')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="standard_features"
                        class="mb-1">{{ __('Standard Features') }}</x-app.input.label>
                    <x-app.input.input name="standard_features" id="standard_features"
                        value="{{ old('standard_features', isset($prod) ? $prod->standard_features : ($dup_prod != null ? $dup_prod->standard_features : null)) }}" />
                    <x-input-error :messages="$errors->get('standard_features')" class="mt-1" />
                </div>
            </div>
        </div>
        <!-- Classification Code  -->
        <div class="bg-white p-4 border rounded-md mt-4">
            <div class="grid grid-cols-3 gap-8 w-full">
                <div class="flex flex-col col-span-4">
                    <x-app.input.label id="classification_code" class="mb-1">{{ __('Classification Code') }} <span
                            class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select2 name="classification_code[]" multiple>
                        @foreach ($classificationCodes as $classificationCode)
                            <option value="{{ $classificationCode->id }}" @selected(in_array($classificationCode->id, old('classification_code', isset($prod) ? $prod->classificationCodes->pluck('id')->toArray() : [])))>
                                {{ $classificationCode->code }} - {{ $classificationCode->description }}</option>
                        @endforeach
                    </x-app.input.select2>
                    <x-input-error :messages="$errors->get('classification_code')" class="mt-1" />
                </div>
            </div>
        </div>
        <!-- Platform -->
        <div class="bg-white p-4 border rounded-md mt-4">
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-8 w-full">
                <div class="flex flex-col">
                    <x-app.input.label id="lazada_sku" class="mb-1">{{ __('Lazada Sku') }}</x-app.input.label>
                    <x-app.input.input name="lazada_sku" id="lazada_sku"
                        value="{{ old('lazada_sku', isset($prod) ? $prod->lazada_sku : ($dup_prod != null ? $dup_prod->lazada_sku : null)) }}" />
                    <x-input-error :messages="$errors->get('lazada_sku')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="shopee_sku" class="mb-1">{{ __('Shopee Sku') }}</x-app.input.label>
                    <x-app.input.input name="shopee_sku" id="shopee_sku"
                        value="{{ old('shopee_sku', isset($prod) ? $prod->shopee_sku : ($dup_prod != null ? $dup_prod->shopee_sku : null)) }}" />
                    <x-input-error :messages="$errors->get('shopee_sku')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="tiktok_sku" class="mb-1">{{ __('Tiktok Sku') }}</x-app.input.label>
                    <x-app.input.input name="tiktok_sku" id="tiktok_sku"
                        value="{{ old('tiktok_sku', isset($prod) ? $prod->tiktok_sku : ($dup_prod != null ? $dup_prod->tiktok_sku : null)) }}" />
                    <x-input-error :messages="$errors->get('tiktok_sku')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="woo_commerce_sku"
                        class="mb-1">{{ __('Woo Commerce Sku') }}</x-app.input.label>
                    <x-app.input.input name="woo_commerce_sku" id="woo_commerce_sku"
                        value="{{ old('woo_commerce_sku', isset($prod) ? $prod->woo_commerce_sku : ($dup_prod != null ? $dup_prod->woo_commerce_sku : null)) }}" />
                    <x-input-error :messages="$errors->get('woo_commerce_sku')" class="mt-1" />
                </div>
            </div>
        </div>
        {{-- Milestones --}}
        @if (isset($prod) && $is_product)
            <div class="bg-white p-4 border rounded-md mt-6" id="milestone-container">
                <div class="flex justify-between mb-2">
                    <h6 class="font-medium text-lg">{{ __('Milestones') }}</h6>
                    <p class="text-xs text-end mb-2 text-slate-500">
                        {{ __("'Yes' represent the milestone is required to fill up material use.") }}</p>
                </div>
                <div id="milestone-list-container">
                    {{-- Template --}}
                    <div class="flex justify-between mb-2 hidden cursor-grab hover:bg-slate-50" id="milestone-template">
                        <div class="flex items-center first-half">
                            <span title="{{ __('Current milestone') }}">
                                <svg class="h-4 w-4 fill-blue-400 hidden" xmlns="http://www.w3.org/2000/svg"
                                    id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512"
                                    height="512">
                                    <path
                                        d="M1,24a1,1,0,0,0,.707-.293l6.619-6.619L9.574,18.38a5.169,5.169,0,0,0,3.605,1.614,3.991,3.991,0,0,0,1.339-.227,3.63,3.63,0,0,0,2.435-3.122,8.486,8.486,0,0,0-.222-3.027l-.214-1.042a1,1,0,0,1,.264-.943l1.587-1.588a.34.34,0,0,1,.236-.1.17.17,0,0,1,.167.065,3.077,3.077,0,0,0,3.971.432,3,3,0,0,0,.379-4.565L18.2.954a3.085,3.085,0,0,0-3.938-.4,3,3,0,0,0-.38,4.565l.076.076a.308.308,0,0,1,0,.434l-1.6,1.6a1,1,0,0,1-.954.261l-.817-.209a8.632,8.632,0,0,0-3.082-.233A3.863,3.863,0,0,0,4.25,9.634a4,4,0,0,0,.928,4.2l1.758,1.82L.293,22.293A1,1,0,0,0,1,24ZM6.135,10.3A1.856,1.856,0,0,1,7.713,9.036,6.7,6.7,0,0,1,8.406,9a6.622,6.622,0,0,1,1.681.217l.823.21a3.01,3.01,0,0,0,2.862-.785l1.6-1.6a2.31,2.31,0,0,0,0-3.262l-.076-.076a1,1,0,0,1,.134-1.528,1.084,1.084,0,0,1,1.356.19l4.924,4.924h0a1,1,0,0,1-.134,1.528,1.085,1.085,0,0,1-1.368-.2,2.212,2.212,0,0,0-1.584-.672,2.4,2.4,0,0,0-1.667.684l-1.586,1.587a3,3,0,0,0-.8,2.8l.219,1.058a6.646,6.646,0,0,1,.181,2.366,1.655,1.655,0,0,1-1.115,1.444,2.8,2.8,0,0,1-2.85-.9l-4.4-4.55A2.027,2.027,0,0,1,6.135,10.3Z" />
                                </svg>
                            </span>
                            <span class="text-sm ms-name"></span>
                        </div>
                        <div class="flex items-center second-half">
                            <button type="button" class="mr-3 view-material-use-selection-btns hidden"
                                title="View Material Use Selection">
                                <svg class="h-4 w-4 fill-slate-400 hover:fill-black" xmlns="http://www.w3.org/2000/svg"
                                    id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24">
                                    <path
                                        d="M23.707,22.293l-5.969-5.969c1.412-1.725,2.262-3.927,2.262-6.324C20,4.486,15.514,0,10,0S0,4.486,0,10s4.486,10,10,10c2.397,0,4.599-.85,6.324-2.262l5.969,5.969c.195,.195,.451,.293,.707,.293s.512-.098,.707-.293c.391-.391,.391-1.023,0-1.414ZM2,10C2,5.589,5.589,2,10,2s8,3.589,8,8-3.589,8-8,8S2,14.411,2,10Zm13.933-1.261c-.825-1.21-2.691-3.239-5.933-3.239s-5.108,2.03-5.933,3.239c-.522,.766-.522,1.755,0,2.521,.825,1.21,2.692,3.24,5.933,3.24s5.108-2.03,5.933-3.239c.522-.766,.522-1.755,0-2.521Zm-1.652,1.395c-.735,1.08-2.075,2.366-4.28,2.366s-3.544-1.287-4.28-2.367c-.056-.081-.056-.185,0-.267,.735-1.08,2.075-2.366,4.28-2.366s3.545,1.287,4.28,2.366h0c.056,.082,.056,.186,0,.268Zm-2.78-.134c0,.829-.671,1.5-1.5,1.5s-1.5-.671-1.5-1.5,.671-1.5,1.5-1.5,1.5,.671,1.5,1.5Z" />
                                </svg>
                            </button>
                            <label
                                class="flex items-center rounded-full overflow-hidden relative cursor-pointer select-none border border-grey-200 w-24 h-7">
                                <input type="checkbox" class="hidden peer" name="required_serial_no[]" />
                                <div class="flex items-center w-full">
                                    <span
                                        class="flex-1 font-medium uppercase z-20 text-center text-xs">{{ __('No') }}</span>
                                    <span
                                        class="flex-1 font-medium uppercase z-20 text-center text-xs">{{ __('Yes') }}</span>
                                </div>
                                <span
                                    class="w-1/2 h-6 peer-checked:translate-x-full absolute rounded-full transition-all bg-blue-200 border border-black" />
                            </label>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="milestones" />
            </div>
        @endif
        <!-- Serial No -->
        <div class="bg-white p-4 border rounded-md mt-6" id="serial-no-container">
            <div class="mb-2 flex items-center justify-between">
                <h6 class="font-medium text-lg">{{ __('Serial No') }}</h6>
                <span class="text-sm text-slate-500">{{ __('Serial No Qty:') }} <span id="serial-no-qty">0</span></span>
            </div>
            <x-app.input.input name="serial_no_ipt" id="serial_no_ipt" placeholder="{{ __('Enter Serial No') }}" value="{{ old('serial_no_ipt') }}" />
            <x-app.input.input name="order_idx" id="order_idx" class="hidden" />
            <ul class="my-2" id="serial_no_list">
                <!-- Template -->
                <li class="hidden group flex items-center rounded hover:bg-slate-100" id="serial-no-template">
                    <input type="hidden" name="serial_no[]">
                    <div class="flex justify-between w-full">
                        <div class="py-1 px-1.5 flex items-center">
                            <svg class="h-4 w-4 mr-1 fill-blue-500" xmlns="http://www.w3.org/2000/svg" id="Layer_1"
                                data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512">
                                <path
                                    d="M12,7c-2.76,0-5,2.24-5,5s2.24,5,5,5,5-2.24,5-5-2.24-5-5-5Zm0,8c-1.65,0-3-1.35-3-3s1.35-3,3-3,3,1.35,3,3-1.35,3-3,3Z" />
                            </svg>
                            <span class="text-sm"></span>
                        </div>
                        <button type="button"
                            class="text-red-500 p-1.5 rounded text-xs font-semibold opacity-0 group-hover:opacity-100 delete-serial-no-btns"
                            title="Remove">
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

    <x-app.modal.product-material-use-modal />
@endsection

@push('scripts')
    <script>
        IS_PRODUCT = @json($is_product);
        PRODUCT = @json($prod ?? null);
        SERIAL_NO_COUNT = 0
        MATERIAL_USE = @json($material_use ?? null);
        MILESTONES = {} // milestone id: material use id
        SELECTED_MILESTONE_ID = null

        $(document).ready(function() {
            var elem = document.getElementById('milestone-list-container')
            if (elem != null) {
                var sortable = Sortable.create(elem, {
                    onEnd: function(evt) {
                        sortMilestone()
                    },
                })
            }

            if (PRODUCT != null) {
                for (let i = 0; i < PRODUCT.children.length; i++) {
                    const child = PRODUCT.children[i];

                    addSerialNo(child.sku, child.id)
                }
                $('select[name="is_sparepart"]').trigger('change')
                if (PRODUCT.company_group == 2) {
                    $('#hi_ten_stock_code-container').removeClass('hidden')
                }

                if (PRODUCT.selling_prices.length == 0) $('#add-selling-price-btn').trigger('click')

                $('select[name="category_id"]').trigger('change')
            } else {
                $('#add-selling-price-btn').trigger('click')
            }

            // Restore serial numbers from validation failure
            @if(old('serial_no'))
                const oldSerialNos = @json(old('serial_no'));
                if (Array.isArray(oldSerialNos)) {
                    oldSerialNos.forEach(function(serialNo) {
                        if (serialNo && serialNo.trim() !== '') {
                            addSerialNo(serialNo);
                        }
                    });
                }
            @endif
        })
        $('#submit-create-btn').on('click', function(e) {
            let url = $('#form').attr('action')
            if (url.includes('?')) {
                url = `${url}&create_again=true`
            } else {
                url = `${url}?create_again=true`
            }

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
                $('.required-star').removeClass('hidden')
                $('#initial-container').removeClass('hidden')
                $('#qty-container').addClass('hidden')
                $('#form #info-submit-container').addClass('hidden')
                $('#form #info-submit-container').removeClass('block')
            } else {
                $('.required-star').addClass('hidden')
                $('#initial-container').addClass('hidden')
                $('#qty-container').removeClass('hidden')
                $('#form #info-submit-container').addClass('block')
                $('#form #info-submit-container').removeClass('hidden')
            }
        })
        $('#add-selling-price-btn').on('click', function() {
            let clone = $('#selling-price-template')[0].cloneNode(true);

            $(clone).addClass('selling-prices')
            $(clone).removeClass('hidden')
            $(clone).removeAttr('id')

            $('#selling-price-container').append(clone)
        })
        $('#form').one('submit', function(e) {
            e.preventDefault()

            let orderId = []
            $('.serial-no').each(function(i, obj) {
                orderId.push($(this).data('order-id') ?? null)
            })

            $('input[name="order_idx"]').val(JSON.stringify(orderId))

            // Prepare selling price
            $('#selling-price-template').remove()
            // Milestones
            $('input[name="milestones"]').val(JSON.stringify(MILESTONES))

            $(this).submit()
        })
        $('select[name="company_group"]').on('change', function() {
            let val = $(this).val()

            if (val == 1) {
                $('#hi_ten_stock_code-container').addClass('hidden')
                $('select[name="hi_ten_stock_code"]').val(null).trigger('change')
            } else {
                $('#hi_ten_stock_code-container').removeClass('hidden')
            }
        })
        $('select[name="category_id"], select[name="item_type"]').on('change', function() {
            getMilestones()
        })
        // Toggle view material use selection
        $('body').on('change', 'input[name="required_serial_no[]"]', function() {
            let milestoneId = $(this).data('milestone-id')
            SELECTED_MILESTONE_ID = milestoneId

            if ($(this).is(':checked')) {
                $('#material-use-selection-container .material-use-selections').remove()

                for (let i = 0; i < MATERIAL_USE.length; i++) {
                    for (let j = 0; j < MATERIAL_USE[i].materials.length; j++) {
                        let clone = $('#material-use-selection-template')[0].cloneNode(true);

                        $(clone).find('input').attr('id', `material-use-${MATERIAL_USE[i].materials[j].id}`)
                        $(clone).find('label').attr('for', `material-use-${MATERIAL_USE[i].materials[j].id}`)
                        $(clone).find('#name').text(MATERIAL_USE[i].materials[j].material.model_name)
                        $(clone).find('label #qty').text(`Quantity needed: x${MATERIAL_USE[i].materials[j].qty}`)
                        $(clone).removeAttr('id')
                        $(clone).removeClass('hidden')
                        $(clone).addClass('flex material-use-selections')
                        $(clone).attr('data-material-use-product-id', MATERIAL_USE[i].materials[j].product_id)

                        if (j == MATERIAL_USE[i].materials.length - 1) {
                            $(clone).removeClass('border-b')
                        }

                        $('#material-use-selection-container').append(clone)
                    }
                }
                $('#product-material-use-modal #action-container').removeClass('hidden')
                $('#product-material-use-modal #action2-container').addClass('hidden')
                $('#product-material-use-modal').addClass('show-modal')
            } else {
                MILESTONES[SELECTED_MILESTONE_ID].material_use_product_ids = []
                $(`.milestones[data-milestone-id=${SELECTED_MILESTONE_ID}] .view-material-use-selection-btns`)
                    .addClass('hidden')
            }
        })
        $('body').on('click', '.view-material-use-selection-btns', function() {
            let milestoneId = $(this).parent().parent().data('milestone-id')

            $('#material-use-selection-container .material-use-selections').remove()

            for (let i = 0; i < MATERIAL_USE.length; i++) {
                for (let j = 0; j < MATERIAL_USE[i].materials.length; j++) {
                    if (!MILESTONES[milestoneId].material_use_product_ids.includes(MATERIAL_USE[i].materials[j]
                            .material.id)) {
                        continue
                    }

                    let clone = $('#material-use-selection-template')[0].cloneNode(true);

                    $(clone).find('input').attr('id', `material-use-${MATERIAL_USE[i].materials[j].id}`)
                    $(clone).find('input').attr('checked', true)
                    $(clone).find('label').attr('for', `material-use-${MATERIAL_USE[i].materials[j].id}`)
                    $(clone).find('#name').text(MATERIAL_USE[i].materials[j].material.model_name)
                    $(clone).find('label #qty').text(`Quantity needed: x${MATERIAL_USE[i].materials[j].qty}`)
                    $(clone).removeAttr('id')
                    $(clone).removeClass('hidden')
                    $(clone).addClass('flex material-use-selections')
                    $(clone).attr('data-material-use-product-id', MATERIAL_USE[i].materials[j].id)

                    $('#material-use-selection-container').append(clone)
                }
            }
            // Border bottom
            if ($('.material-use-selections').length > 1) {
                $('.material-use-selections').each(function(i, obj) {
                    if (i + 1 == $('.material-use-selections').length) {
                        $(this).removeClass('border-b')
                    }
                })
            } else {
                $('.material-use-selections').each(function(i, obj) {
                    $(this).removeClass('border-b')
                })
            }

            $('#product-material-use-modal #action-container').addClass('hidden')
            $('#product-material-use-modal #action2-container').removeClass('hidden')
            $('#product-material-use-modal').addClass('show-modal')
        })
        $('#product-material-use-modal #no-btn').on('click', function() {
            $(`input[name="required_serial_no[]"][data-milestone-id="${SELECTED_MILESTONE_ID}"]`).trigger('click')

            $('#product-material-use-modal').removeClass('show-modal')
            $('#product-material-use-modal input[name="search"]').val(null)
        })
        $('#product-material-use-modal #yes-btn').on('click', function() {
            if ($('.material-use-selections input:checked').length <= 0) {
                $('#product-material-use-modal #no-btn').trigger('click')
                return
            }
            $('.material-use-selections input[type="checkbox"]').each(function(i, obj) {
                if ($(this).is(':checked')) {
                    if (MILESTONES[SELECTED_MILESTONE_ID] == undefined) {
                        MILESTONES[SELECTED_MILESTONE_ID] = []
                    }
                    MILESTONES[SELECTED_MILESTONE_ID].material_use_product_ids.push($(this).parent()
                        .data('material-use-product-id'))
                }
            })

            $(`.milestones[data-milestone-id=${SELECTED_MILESTONE_ID}] .view-material-use-selection-btns`)
                .removeClass('hidden')

            $('#product-material-use-modal input[name="search"]').val(null)
            $('#product-material-use-modal').removeClass('show-modal')
        })
        $('#product-material-use-modal input[name="search"]').on('keyup', function() {
            let val = $(this).val()

            $(`.material-use-selections`).removeClass('hidden')

            if (val != '') {
                for (let i = 0; i < MATERIAL_USE.length; i++) {
                    for (let j = 0; j < MATERIAL_USE[i].materials.length; j++) {
                        included = false
                        if (
                            MATERIAL_USE[i].materials[j].material.model_name.includes(val) ||
                            MATERIAL_USE[i].materials[j].material.sku.includes(val)
                        ) {
                            included = true
                        }
                        if (!included) {
                            $(`.material-use-selections[data-material-use-product-id=${MATERIAL_USE[i].materials[j].product_id}]`)
                                .addClass('hidden')
                        }
                    }
                }
            }
        })

        function addSerialNo(val, order_id = null) {
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
            $('#serial-no-qty').text($('.serial-no').length)
        }

        function getMilestones() {
            let categoryId = $('select[name="category_id"]').val()
            let typeId = $('select[name="item_type"]').val()

            if (categoryId == '' || typeId == '' || PRODUCT == null) return

            let url = '{{ config('app.url') }}'
            url = `${url}/milestone/get/${categoryId}/${typeId}?product_id=${PRODUCT.id}`

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: url,
                type: 'GET',
                success: function(res) {
                    for (let i = 0; i < res.milestones.length; i++) {
                        const ms = res.milestones[i];

                        let clone = $('#milestone-template')[0].cloneNode(true);
                        $(clone).find('.ms-name').text(ms.name)
                        $(clone).find('.ms-name').attr('for', `ms-${ms.id}`)
                        $(clone).find('.second-half input').attr('data-milestone-id', ms.id)
                        $(clone).attr('data-milestone-id', ms.id)
                        $(clone).removeClass('hidden')
                        $(clone).addClass('milestones')
                        $(clone).removeAttr('id')

                        $('#milestone-list-container').append(clone)

                        MILESTONES[ms.id] = {
                            sequence: i + 1,
                            material_use_product_ids: []
                        }
                    }
                    // Initiate product milestones
                    for (let i = 0; i < PRODUCT.milestones.length; i++) {
                        $(`.milestones[data-milestone-id="${PRODUCT.milestones[i].milestone_id}"] .first-half input`)
                            .attr('checked', true)
                        $(`.milestones[data-milestone-id="${PRODUCT.milestones[i].milestone_id}"] .first-half svg`)
                            .removeClass('hidden')
                        $(`.milestones[data-milestone-id="${PRODUCT.milestones[i].milestone_id}"] .first-half`)
                            .addClass('gap-2')

                        if (PRODUCT.milestones[i].material_use_product_id.length > 0) {
                            $(`input[name="required_serial_no[]"][data-milestone-id="${PRODUCT.milestones[i].milestone_id}"`)
                                .attr('checked', true)
                            $(`.milestones[data-milestone-id="${PRODUCT.milestones[i].milestone_id}"] .view-material-use-selection-btns`)
                                .removeClass('hidden')
                        }
                        MILESTONES[PRODUCT.milestones[i].milestone_id].material_use_product_ids =
                            PRODUCT.milestones[i]
                            .material_use_product_id
                    }
                },
            });
        }

        function sortMilestone() {
            let sequence = 0

            $('.milestones').each(function(i, obj) {
                sequence++
                MILESTONES[$(this).attr('data-milestone-id')].sequence = sequence
            })
        }
    </script>
@endpush
