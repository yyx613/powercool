@extends('layouts.app')

@section('content')
    <div class="mb-6 flex justify-between items-center">
        <x-app.page-title url="{{ route('delivery_order.index') }}">{{ __('Generate Transport Acknowledgement') }}</x-app.page-title>
    </div>
    @include('components.app.alert.parent')

    <div class="bg-white p-4 border rounded-md max-w-screen-sm m-auto">
        <form action="{{ route('delivery_order.generate_transport_acknowledgement') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div id="content-container">
                <div class="grid gap-4 w-full mb-4">
                    <div class="flex flex-col">
                        <x-app.input.label id="delivery_order" class="mb-1">{{ __('Delivery Order') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                        <x-app.input.select2 name="delivery_order" id="delivery_order" :hasError="$errors->has('delivery_order')" placeholder="{{ __('Select a delivery order') }}">
                            <option value="">{{ __('Select a delivery order') }}</option>
                            @foreach ($delivery_orders as $do)
                                <option value="{{ $do->id }}" @selected(old('delivery_order') == $do->id)>{{ $do->sku }}</option>
                            @endforeach
                        </x-app.input.select2>
                        <x-input-error :messages="$errors->get('delivery_order')" class="mt-1" />
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
                </div>
                <div class="mt-8 flex justify-end">
                    <x-app.button.submit>{{ __('Generate') }}</x-app.button.submit>
                </div>
            </div>
        </form>
    </div>
@endsection
