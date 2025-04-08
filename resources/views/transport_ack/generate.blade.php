@extends('layouts.app')

@section('content')
    <div class="mb-6 flex justify-between items-center">
        <x-app.page-title url="{{ route('transport_ack.index') }}">{{ __('Generate Transport Acknowledgement') }}</x-app.page-title>
    </div>
    @include('components.app.alert.parent')

    <form action="{{ route('transport_ack.generate_transport_acknowledgement') }}" method="POST" enctype="multipart/form-data" class="flex flex-col gap-y-8">
        @csrf
        <div class="bg-white p-4 border rounded-md">
            <div id="content-container">
                <div class="flex items-center mb-6 border-l-8 border-yellow-400 px-3 py-1 bg-yellow-50 w-fit">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512" height="512"><path d="M12,0A12,12,0,1,0,24,12,12.013,12.013,0,0,0,12,0Zm0,22A10,10,0,1,1,22,12,10.011,10.011,0,0,1,12,22Z"/><path d="M12,10H11a1,1,0,0,0,0,2h1v6a1,1,0,0,0,2,0V12A2,2,0,0,0,12,10Z"/><circle cx="12" cy="6.5" r="1.5"/></svg>
                    <span class="text-lg ml-3 font-bold">{{ __('Basic Details') }}</span>
                </div>
                <div class="grid grid-cols-2 lg:grid-cols-3 gap-8 w-full mb-4">
                    <div class="flex flex-col">
                        <x-app.input.label id="do_id" class="mb-1">{{ __('Delivery Order ID') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                        <x-app.input.input name="do_id" id="do_id" :hasError="$errors->has('do_id')" value="{{ old('do_id') ?? null }}" />
                        <x-input-error :messages="$errors->get('do_id')" class="mt-1" />
                    </div>
                    <div class="flex flex-col">
                        <x-app.input.label id="date" class="mb-1">{{ __('Date') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                        <x-app.input.input name="date" id="date" :hasError="$errors->has('date')" value="{{ old('date') ?? null }}" />
                        <x-input-error :messages="$errors->get('date')" class="mt-1" />
                    </div>
                    <div class="flex flex-col">
                        <x-app.input.label id="dealer" class="mb-1">{{ __('Dealer') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                        <x-app.input.select2 name="dealer" id="dealer" :hasError="$errors->has('dealer')" placeholder="{{ __('Select a dealer') }}">
                            <option value="">{{ __('Select a dealer') }}</option>
                            <option value="-1" @selected(old('dealer') == '-1')>Powercool</option>
                            <option value="-2" @selected(old('dealer') == '-2')>Hi Ten Trading</option>
                            @foreach ($dealers as $dealer)
                                <option value="{{ $dealer->id }}" @selected(old('dealer') == $dealer->id)>{{ $dealer->name }}</option>
                            @endforeach
                        </x-app.input.select2>
                         <x-input-error :messages="$errors->get('dealer')" class="mt-1" />
                    </div>
                    <div class="flex flex-col">
                        <x-app.input.label id="type" class="mb-1">{{ __('Type') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                        <x-app.input.select2 name="type" id="type" :hasError="$errors->has('type')" placeholder="{{ __('Select a type') }}">
                            <option value="">{{ __('Select a type') }}</option>
                            @foreach ($types as $key => $val)
                                <option value="{{ $key }}" @selected(old('type') == $key)>{{ $val }}</option>
                            @endforeach
                        </x-app.input.select2>
                        <x-input-error :messages="$errors->get('type')" class="mt-1" />
                    </div>
                    <div class="flex flex-col">
                        <x-app.input.label id="delivery_to" class="mb-1">{{ __('Delivery To') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                        <x-app.input.textarea name="delivery_to" id="delivery_to" :hasError="$errors->has('delivery_to')" text="{{ old('delivery_to') ?? null }}" />
                        <x-input-error :messages="$errors->get('delivery_to')" class="mt-1" />
                    </div>
                </div>
            </div>
        </div>
        <div class="bg-white p-4 border rounded-md">
            <div id="content-container">
                <div class="flex items-center mb-6 border-l-8 border-yellow-400 px-3 py-1 bg-yellow-50 w-fit">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512" height="512"><path d="M12,0A12,12,0,1,0,24,12,12.013,12.013,0,0,0,12,0Zm0,22A10,10,0,1,1,22,12,10.011,10.011,0,0,1,12,22Z"/><path d="M12,10H11a1,1,0,0,0,0,2h1v6a1,1,0,0,0,2,0V12A2,2,0,0,0,12,10Z"/><circle cx="12" cy="6.5" r="1.5"/></svg>
                    <span class="text-lg ml-3 font-bold">{{ __('Product Details') }}</span>
                </div>
                <div class="flex items-start gap-6 w-full mb-4 hidden" id="item-template">
                    <div class="flex flex-col flex-1">
                        <x-app.input.label id="product" class="mb-1">{{ __('Product') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                        <x-app.input.select name="product[]" id="product" :hasError="$errors->has('product')" placeholder="{{ __('Select a product') }}">
                            <option value="">{{ __('Select a product') }}</option>
                            @foreach ($products as $pro)
                                <option value="{{ $pro->id }}">{{ $pro->sku }} ({{ $pro->model_name }})</option>
                            @endforeach
                        </x-app.input.select>
                    </div>
                    <div class="flex flex-col flex-1">
                        <x-app.input.label id="qty" class="mb-1">{{ __('Quantity') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                        <x-app.input.input name="qty[]" id="qty" value="{{ old('qty.*') ?? null }}" class="int-input" />
                    </div>
                    <div class="flex items-end pb-2">
                        <button type="button" class="bg-red-500 rounded-full aspect-square w-7 flex items-center justify-center remove-item-btns">
                            <svg class="h-4 w-4 fill-white" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512" height="512"><path d="M21,4H17.9A5.009,5.009,0,0,0,13,0H11A5.009,5.009,0,0,0,6.1,4H3A1,1,0,0,0,3,6H4V19a5.006,5.006,0,0,0,5,5h6a5.006,5.006,0,0,0,5-5V6h1a1,1,0,0,0,0-2ZM11,2h2a3.006,3.006,0,0,1,2.829,2H8.171A3.006,3.006,0,0,1,11,2Zm7,17a3,3,0,0,1-3,3H9a3,3,0,0,1-3-3V6H18Z"/><path d="M10,18a1,1,0,0,0,1-1V11a1,1,0,0,0-2,0v6A1,1,0,0,0,10,18Z"/><path d="M14,18a1,1,0,0,0,1-1V11a1,1,0,0,0-2,0v6A1,1,0,0,0,14,18Z"/></svg>
                        </button>
                    </div>
                </div>
                <div id="item-container">
                    @if (old('product') != null)
                        <input type="hidden" name="has_old_val" value="{{ count(old('product')) }}">
                        @foreach(old('product') as $key => $old_pro)
                            <div class="flex items-start gap-6 w-full mb-4 items" data-id="{{ $key +1 }}">
                                <div class="flex flex-col flex-1">
                                    <x-app.input.label id="product" class="mb-1">{{ __('Product') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                                    <x-app.input.select name="product[]" id="product" :hasError="$errors->has('product')" placeholder="{{ __('Select a product') }}">
                                        <option value="">{{ __('Select a product') }}</option>
                                        @foreach ($products as $pro)
                                            <option value="{{ $pro->id }}" @selected(old('product.'.$key) == $pro->id)>{{ $pro->sku }} ({{ $pro->model_name }})</option>
                                        @endforeach
                                    </x-app.input.select>
                                    <x-input-error :messages="$errors->first('product.'.$key)" class="mt-1" />
                                </div>
                                <div class="flex flex-col flex-1">
                                    <x-app.input.label id="qty" class="mb-1">{{ __('Quantity') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                                    <x-app.input.input name="qty[]" id="qty" value="{{ old('qty.'.$key) ?? null }}" class="int-input" />
                                    <x-input-error :messages="$errors->first('qty.'.$key)" class="mt-1" />
                                </div>
                                <div class="flex flex-col">
                                    <button type="button" class="bg-red-500 rounded-full aspect-square w-7 flex items-center justify-center remove-item-btns" data-id="{{ $key + 1 }}">
                                        <svg class="h-4 w-4 fill-white" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512" height="512"><path d="M21,4H17.9A5.009,5.009,0,0,0,13,0H11A5.009,5.009,0,0,0,6.1,4H3A1,1,0,0,0,3,6H4V19a5.006,5.006,0,0,0,5,5h6a5.006,5.006,0,0,0,5-5V6h1a1,1,0,0,0,0-2ZM11,2h2a3.006,3.006,0,0,1,2.829,2H8.171A3.006,3.006,0,0,1,11,2Zm7,17a3,3,0,0,1-3,3H9a3,3,0,0,1-3-3V6H18Z"/><path d="M10,18a1,1,0,0,0,1-1V11a1,1,0,0,0-2,0v6A1,1,0,0,0,10,18Z"/><path d="M14,18a1,1,0,0,0,1-1V11a1,1,0,0,0-2,0v6A1,1,0,0,0,14,18Z"/></svg>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
                <!-- Add Items -->
                <div class="flex justify-end mt-8">
                    <button type="button" class="bg-yellow-400 rounded-md py-1.5 px-3 flex items-center gap-x-2 transition duration-300 hover:bg-yellow-300 hover:shadow" id="add-item-btn">
                        <svg class="h-3 w-3" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Capa_1" x="0px" y="0px" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve" width="512" height="512">
                            <path d="M480,224H288V32c0-17.673-14.327-32-32-32s-32,14.327-32,32v192H32c-17.673,0-32,14.327-32,32s14.327,32,32,32h192v192   c0,17.673,14.327,32,32,32s32-14.327,32-32V288h192c17.673,0,32-14.327,32-32S497.673,224,480,224z"/>
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
    ITEM_ID = 1

    $(document).ready(function() {
        if ($('input[name="has_old_val"]').length <= 0) {
            $('#add-item-btn').click()
        } else {
            ITEM_ID = $('input[name="has_old_val"]').val() + 1
        }
    })

    $('input[name="date"]').daterangepicker(datepickerParam)
    $('input[name="date"]').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('YYYY-MM-DD'));
    });

    $('#add-item-btn').on('click', function() {
        let clone = $('#item-template')[0].cloneNode(true);
        $(clone).removeClass('hidden')
        $(clone).addClass('items')
        $(clone).attr('data-id', ITEM_ID)
        $(clone).find('.remove-item-btns').attr('data-id', ITEM_ID)

        $('#item-container').append(clone)

        ITEM_ID++
    })
    $('body').on('click', '.remove-item-btns', function() {
        let id = $(this).data('id')

        $(`.items[data-id="${id}"]`).remove()
    })
    $('form').one('submit', function() {
        $('#item-template').remove()

        $(this).submit()
    })
</script>
@endpush
