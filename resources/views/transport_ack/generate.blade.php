@extends('layouts.app')
@section('title', 'Transport Acknowledgement')

@section('content')
    <div class="mb-6 flex justify-between items-center">
        <x-app.page-title
            url="{{ route('transport_ack.index') }}">{{ isset($ack) ? __('Edit Transport Acknowledgement') : __('Generate Transport Acknowledgement') }}</x-app.page-title>
    </div>
    @include('components.app.alert.parent')

    <form
        action="{{ isset($ack) ? route('transport_ack.generate_transport_acknowledgement', ['ack' => $ack]) : route('transport_ack.generate_transport_acknowledgement') }}"
        method="POST" enctype="multipart/form-data" class="flex flex-col gap-y-8">
        @csrf
        <div class="bg-white p-4 border rounded-md">
            <div id="content-container">
                <div class="flex items-center mb-6 border-l-8 border-yellow-400 px-3 py-1 bg-yellow-50 w-fit">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512"
                        height="512">
                        <path
                            d="M12,0A12,12,0,1,0,24,12,12.013,12.013,0,0,0,12,0Zm0,22A10,10,0,1,1,22,12,10.011,10.011,0,0,1,12,22Z" />
                        <path d="M12,10H11a1,1,0,0,0,0,2h1v6a1,1,0,0,0,2,0V12A2,2,0,0,0,12,10Z" />
                        <circle cx="12" cy="6.5" r="1.5" />
                    </svg>
                    <span class="text-lg ml-3 font-bold">{{ __('Basic Details') }}</span>
                </div>
                <div class="grid grid-cols-2 lg:grid-cols-3 gap-8 w-full mb-4">
                    <div class="flex flex-col">
                        <x-app.input.label id="do_id" class="mb-1">{{ __('Delivery Order ID') }}</x-app.input.label>
                        <x-app.input.input name="do_id" id="do_id" :hasError="$errors->has('do_id')"
                            value="{{ old('do_id', isset($ack) ? $ack->delivery_order_id : null) ?? null }}" />
                        <x-input-error :messages="$errors->get('do_id')" class="mt-1" />
                    </div>
                    <div class="flex flex-col">
                        <x-app.input.label id="date" class="mb-1">{{ __('Date') }} <span
                                class="text-sm text-red-500">*</span></x-app.input.label>
                        <x-app.input.input name="date" id="date" :hasError="$errors->has('date')"
                            value="{{ old('date', isset($ack) ? $ack->date : null) ?? null }}" />
                        <x-input-error :messages="$errors->get('date')" class="mt-1" />
                    </div>
                    <div class="flex flex-col">
                        <x-app.input.label id="dealer" class="mb-1">{{ __('Dealer') }} <span
                                class="text-sm text-red-500">*</span></x-app.input.label>
                        <x-app.input.select2 name="dealer" id="dealer" :hasError="$errors->has('dealer')"
                            placeholder="{{ __('Select a dealer') }}">
                            <option value="">{{ __('Select a dealer') }}</option>
                            <option value="-1" @selected(old('dealer', isset($ack) ? $ack->dealer_id : null) == '-1')>Powercool</option>
                            <option value="-2" @selected(old('dealer', isset($ack) ? $ack->dealer_id : null) == '-2')>Hi Ten Trading</option>
                            @foreach ($dealers as $dealer)
                                <option value="{{ $dealer->id }}" @selected(old('dealer', isset($ack) ? $ack->dealer_id : null) == $dealer->id)>{{ $dealer->name }}
                                </option>
                            @endforeach
                        </x-app.input.select2>
                        <x-input-error :messages="$errors->get('dealer')" class="mt-1" />
                    </div>
                    <div class="flex flex-col">
                        <x-app.input.label id="type" class="mb-1">{{ __('Type') }} <span
                                class="text-sm text-red-500">*</span></x-app.input.label>
                        <x-app.input.select2 name="type" id="type" :hasError="$errors->has('type')"
                            placeholder="{{ __('Select a type') }}">
                            <option value="">{{ __('Select a type') }}</option>
                            @foreach ($types as $key => $val)
                                <option value="{{ $key }}" @selected(old('type', isset($ack) ? $ack->type : null) == $key)>{{ $val }}
                                </option>
                            @endforeach
                        </x-app.input.select2>
                        <x-input-error :messages="$errors->get('type')" class="mt-1" />
                    </div>
                    <div class="flex flex-col">
                        <x-app.input.label id="company_name" class="mb-1">{{ __('Customer Name') }} <span
                                class="text-sm text-red-500">*</span></x-app.input.label>
                        <x-app.input.input name="company_name" id="company_name" :hasError="$errors->has('company_name')"
                            value="{{ old('company_name', isset($ack) ? $ack->company_name : null) ?? null }}" />
                        <x-input-error :messages="$errors->get('company_name')" class="mt-1" />
                    </div>
                    <div class="flex flex-col">
                        <x-app.input.label id="phone" class="mb-1">{{ __('Phone') }} <span
                                class="text-sm text-red-500">*</span></x-app.input.label>
                        <x-app.input.input name="phone" id="phone" :hasError="$errors->has('phone')"
                            value="{{ old('phone', isset($ack) ? $ack->phone : null) ?? null }}" />
                        <x-input-error :messages="$errors->get('phone')" class="mt-1" />
                    </div>
                    <div class="flex flex-col">
                        <x-app.input.label id="delivery_to" class="mb-1">{{ __('Address') }} <span
                                class="text-sm text-red-500">*</span></x-app.input.label>
                        <x-app.input.textarea name="delivery_to" id="delivery_to" :hasError="$errors->has('delivery_to')"
                            text="{{ old('delivery_to', isset($ack) ? $ack->address : null) ?? null }}" />
                        <x-input-error :messages="$errors->get('delivery_to')" class="mt-1" />
                    </div>
                </div>
            </div>
        </div>
        <div class="bg-white p-4 border rounded-md">
            <div id="content-container">
                <div class="flex items-center mb-6 border-l-8 border-yellow-400 px-3 py-1 bg-yellow-50 w-fit">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24"
                        width="512" height="512">
                        <path
                            d="M12,0A12,12,0,1,0,24,12,12.013,12.013,0,0,0,12,0Zm0,22A10,10,0,1,1,22,12,10.011,10.011,0,0,1,12,22Z" />
                        <path d="M12,10H11a1,1,0,0,0,0,2h1v6a1,1,0,0,0,2,0V12A2,2,0,0,0,12,10Z" />
                        <circle cx="12" cy="6.5" r="1.5" />
                    </svg>
                    <span class="text-lg ml-3 font-bold">{{ __('Product Details') }}</span>
                </div>
                <div class="grid grid-cols-3 items-start gap-6 w-full mb-4 p-4 relative group hidden transition durtion-300 hover:bg-slate-50"
                    id="item-template">
                    <button type="button"
                        class="bg-rose-400 p-2 rounded-full absolute top-[-5px] right-[-5px] hidden group-hover:block remove-item-btns"
                        title="Delete Product">
                        <svg class="h-3 w-3 fill-white" xmlns="http://www.w3.org/2000/svg" id="Layer_1"
                            data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512">
                            <path
                                d="M13.93,12L21.666,2.443c.521-.644,.422-1.588-.223-2.109-.645-.522-1.588-.421-2.109,.223l-7.334,9.06L4.666,.557c-1.241-1.519-3.56,.357-2.332,1.887l7.736,9.557L2.334,21.557c-.521,.644-.422,1.588,.223,2.109,.64,.519,1.586,.424,2.109-.223l7.334-9.06,7.334,9.06c.524,.647,1.47,.742,2.109,.223,.645-.521,.744-1.466,.223-2.109l-7.736-9.557Z" />
                        </svg>
                    </button>
                    <div class="flex flex-col">
                        <x-app.input.label id="product" class="mb-1">{{ __('Product') }} <span
                                class="text-sm text-red-500">*</span></x-app.input.label>
                        <x-app.input.select name="product[]" :hasError="$errors->has('product')"
                            placeholder="{{ __('Select a product') }}">
                            <option value="">{{ __('Select a product') }}</option>
                            @foreach ($products as $pro)
                                <option value="{{ $pro->id }}">{{ $pro->sku }} ({{ $pro->model_name }})
                                </option>
                            @endforeach
                        </x-app.input.select>
                    </div>
                    <div class="flex flex-col">
                        <x-app.input.label id="qty" class="mb-1">{{ __('Quantity') }} <span
                                class="text-sm text-red-500">*</span></x-app.input.label>
                        <x-app.input.input name="qty[]" id="qty" class="int-input" />
                    </div>
                    <div class="flex flex-col">
                        <x-app.input.label id="description" class="mb-1">{{ __('Description') }}</x-app.input.label>
                        <x-app.input.input name="description[]" id="description" />
                    </div>
                    <div class="flex flex-col">
                        <x-app.input.label id="remark" class="mb-1">{{ __('Remark') }}</x-app.input.label>
                        <x-app.input.textarea name="remark[]" id="remark" :hasError="$errors->has('remark')" />
                        <x-input-error :messages="$errors->get('remark')" class="mt-1" />
                    </div>
                    <div class="flex flex-col hidden" id="serial-no-container">
                        <x-app.input.label id="serial_no" class="mb-1">{{ __('Serial No') }}</x-app.input.label>
                        <x-app.input.select name="serial_no[]" placeholder="{{ __('Select a serial no') }}" multiple>
                            <option value="">{{ __('Select a serial no') }}</option>
                        </x-app.input.select>
                    </div>
                </div>
                <div id="item-container">
                    @if (old('product') != null)
                        <input type="hidden" name="has_old_val" value="{{ count(old('product')) }}">
                        @foreach (old('product') as $key => $old_pro)
                            <div class="grid grid-cols-3 items-start gap-6 w-full mb-4 p-4 relative group transition durtion-300 hover:bg-slate-50 items"
                                data-id="{{ $key + 1 }}">
                                <button type="button"
                                    class="bg-rose-400 p-2 rounded-full absolute top-[-5px] right-[-5px] hidden group-hover:block remove-item-btns"
                                    title="Delete Product" data-id="{{ $key + 1 }}">
                                    <svg class="h-3 w-3 fill-white" xmlns="http://www.w3.org/2000/svg" id="Layer_1"
                                        data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512">
                                        <path
                                            d="M13.93,12L21.666,2.443c.521-.644,.422-1.588-.223-2.109-.645-.522-1.588-.421-2.109,.223l-7.334,9.06L4.666,.557c-1.241-1.519-3.56,.357-2.332,1.887l7.736,9.557L2.334,21.557c-.521,.644-.422,1.588,.223,2.109,.64,.519,1.586,.424,2.109-.223l7.334-9.06,7.334,9.06c.524,.647,1.47,.742,2.109,.223,.645-.521,.744-1.466,.223-2.109l-7.736-9.557Z" />
                                    </svg>
                                </button>
                                <div class="flex flex-col">
                                    <x-app.input.label id="product" class="mb-1">{{ __('Product') }} <span
                                            class="text-sm text-red-500">*</span></x-app.input.label>
                                    <x-app.input.select2 name="product[]" :hasError="$errors->has('product')"
                                        placeholder="{{ __('Select a product') }}">
                                        <option value="">{{ __('Select a product') }}</option>
                                        @foreach ($products as $pro)
                                            <option value="{{ $pro->id }}" @selected(old('product.' . $key) == $pro->id)>
                                                {{ $pro->sku }} ({{ $pro->model_name }})</option>
                                        @endforeach
                                    </x-app.input.select2>
                                    <x-input-error :messages="$errors->first('product.' . $key)" class="mt-1" />
                                </div>
                                <div class="flex flex-col">
                                    <x-app.input.label id="qty" class="mb-1">{{ __('Quantity') }} <span
                                            class="text-sm text-red-500">*</span></x-app.input.label>
                                    <x-app.input.input name="qty[]" id="qty"
                                        value="{{ old('qty.' . $key) ?? null }}" class="int-input" />
                                    <x-input-error :messages="$errors->first('qty.' . $key)" class="mt-1" />
                                </div>
                                <div class="flex flex-col">
                                    <x-app.input.label id="description"
                                        class="mb-1">{{ __('Description') }}</x-app.input.label>
                                    <x-app.input.input name="description[]" id="description"
                                        value="{{ old('description.' . $key) ?? null }}" />
                                    <x-input-error :messages="$errors->first('description.' . $key)" class="mt-1" />
                                </div>
                                <div class="flex flex-col">
                                    <x-app.input.label id="remark"
                                        class="mb-1">{{ __('Remark') }}</x-app.input.label>
                                    <x-app.input.textarea name="remark[]" id="remark" :hasError="$errors->has('remark')" text="{{ old('remark.' . $key) ?? null }}" />
                                    <x-input-error :messages="$errors->get('remark')" class="mt-1" />
                                </div>
                                <div class="flex flex-col" id="serial-no-container">
                                    <x-app.input.label id="serial_no"
                                        class="mb-1">{{ __('Serial No') }}</x-app.input.label>
                                    <x-app.input.select2 name="serial_no_{{ $key + 1 }}[]"
                                        placeholder="{{ __('Select a serial no') }}" multiple>
                                        <option value="">{{ __('Select a serial no') }}</option>
                                        @foreach ($products as $pro)
                                            @if ($pro->id == old('product.' . $key))
                                                @foreach ($pro->children as $pc)
                                                    <option value="{{ $pc->id }}" @selected(old('serial_no_' . $pro->id) != null && in_array($pc->id, old('serial_no_' . $pro->id)))>
                                                        {{ $pc->sku }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        @endforeach
                                    </x-app.input.select2>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
                <!-- Add Items -->
                <div class="flex justify-end mt-8">
                    <button type="button"
                        class="bg-yellow-400 rounded-md py-1.5 px-3 flex items-center gap-x-2 transition duration-300 hover:bg-yellow-300 hover:shadow"
                        id="add-item-btn">
                        <svg class="h-3 w-3" xmlns="http://www.w3.org/2000/svg"
                            xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Capa_1" x="0px" y="0px"
                            viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve"
                            width="512" height="512">
                            <path
                                d="M480,224H288V32c0-17.673-14.327-32-32-32s-32,14.327-32,32v192H32c-17.673,0-32,14.327-32,32s14.327,32,32,32h192v192   c0,17.673,14.327,32,32,32s32-14.327,32-32V288h192c17.673,0,32-14.327,32-32S497.673,224,480,224z" />
                        </svg>
                        <span class="text-sm">{{ __('Add Item') }}</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <x-app.button.submit>{{ __('Generate') }}</x-app.button.submit>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        ITEM_ID = 0
        PRODUCTS = @json($products ?? []);
        TRANSPORT_ACK = @json($ack ?? null);

        $(document).ready(function() {
            if ($('input[name="has_old_val"]').length <= 0) {
                if (TRANSPORT_ACK != null) {
                    for (let i = 0; i < TRANSPORT_ACK.products.length; i++) {
                        const element = TRANSPORT_ACK.products[i];

                        $('#add-item-btn').click()

                        $(`.items[data-id=${ITEM_ID}] select[name="product[]"]`).val(element.product_id).trigger(
                            'change')
                        $(`.items[data-id=${ITEM_ID}] input[name="qty[]"]`).val(element.qty)
                        $(`.items[data-id=${ITEM_ID}] input[name="description[]"]`).val(element.desc)
                        $(`.items[data-id=${ITEM_ID}] textarea[name="remark[]"]`).text(element.remark)
                    }
                } else {
                    $('#add-item-btn').click()
                }
            } else {
                ITEM_ID = $('input[name="has_old_val"]').val()
            }
        })

        $('input[name="date"]').daterangepicker(datepickerParam)
        $('input[name="date"]').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD'));
        });

        $('#add-item-btn').on('click', function() {
            let clone = $('#item-template')[0].cloneNode(true);

            ITEM_ID++
            $(clone).removeAttr('id')
            $(clone).removeClass('hidden')
            $(clone).addClass('items')
            $(clone).attr('data-id', ITEM_ID)
            $(clone).find('.remove-item-btns').attr('data-id', ITEM_ID)

            $('#item-container').append(clone)

            buildProductSelect2(ITEM_ID)
        })
        $('body').on('click', '.remove-item-btns', function() {
            let id = $(this).data('id')

            $(`.items[data-id="${id}"]`).remove()
        })
        $('body').on('change', 'select[name="product[]"]', function() {
            let val = $(this).val()
            let id = $(this).parent().parent().data('id')

            $(`.items[data-id="${id}"] select[name="serial_no[]"]`).attr('name', `serial_no_${val}[]`)
            $(`.items[data-id="${id}"] select[name="serial_no_${val}[]"] option`).not(':first').remove()

            for (let i = 0; i < PRODUCTS.length; i++) {
                if (PRODUCTS[i].id == val) {
                    for (let j = 0; j < PRODUCTS[i].children.length; j++) {
                        const element = PRODUCTS[i].children[j];

                        let opt = new Option(element.sku, element.id)
                        $(`.items[data-id="${id}"] select[name="serial_no_${val}[]"]`).append(opt)
                    }
                    buildSerialNoSelect2(id, val)
                    $(`.items[data-id="${id}"] #serial-no-container`).removeClass('hidden')
                    break
                }
            }
        })

        $('form').one('submit', function() {
            $('#item-template').remove()

            $(this).submit()
        })

        function buildProductSelect2(item_id) {
            $(`.items[data-id="${item_id}"] select[name="product[]"]`).select2({
                placeholder: "{!! __('Select a product') !!}"
            })
            $(`.items[data-id="${item_id}"] .select2`).addClass('border border-gray-300 rounded-md overflow-hidden')
        }

        function buildSerialNoSelect2(item_id, product_id) {
            $(`.items[data-id="${item_id}"] select[name="serial_no_${product_id}[]"]`).select2({
                placeholder: "{!! __('Select a serial no') !!}"
            })
            $(`.items[data-id="${item_id}"] .select2`).addClass('border border-gray-300 rounded-md overflow-hidden')
        }
    </script>
@endpush
