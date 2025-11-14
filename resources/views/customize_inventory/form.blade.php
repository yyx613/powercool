@extends('layouts.app')
@section('title', 'Edit Customize Product')

@section('content')
    <div class="mb-6">
        <x-app.page-title url="{{ $back_url ?? route('customize.index') }}">
            {{ __('Edit Customize Product - ' . $customizeProduct->sku) }}
        </x-app.page-title>
    </div>

    @include('components.app.alert.parent')

    <form action="{{ route('customize.update', ['customizeProduct' => $customizeProduct->id]) }}" method="POST" id="form">
        @csrf

        <!-- Physical Dimensions -->
        <div class="bg-white p-4 border rounded-md">
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-8 w-full">
                <div class="flex flex-col col-span-2 lg:col-span-1">
                    <x-app.input.label class="mb-1">{{ __('Dimension (LxWxH) (In MM)') }}</x-app.input.label>
                    <div class="flex gap-x-2">
                        <div class="bg-gray-100 flex items-center">
                            <span class="font-black p-2">L</span>
                            <x-app.input.input name="length" id="length" class="decimal-input"
                                value="{{ old('length', $customizeProduct->length) }}" />
                        </div>
                        <div class="bg-gray-100 flex items-center">
                            <span class="font-black p-2">W</span>
                            <x-app.input.input name="width" id="width" class="decimal-input"
                                value="{{ old('width', $customizeProduct->width) }}" />
                        </div>
                        <div class="bg-gray-100 flex items-center">
                            <span class="font-black p-2">H</span>
                            <x-app.input.input name="height" id="height" class="decimal-input"
                                value="{{ old('height', $customizeProduct->height) }}" />
                        </div>
                    </div>
                    <x-input-error :messages="$errors->get('length')" class="mt-1" />
                    <x-input-error :messages="$errors->get('width')" class="mt-1" />
                    <x-input-error :messages="$errors->get('height')" class="mt-1" />
                </div>

                <div class="flex flex-col">
                    <x-app.input.label id="capacity" class="mb-1">{{ __('Capacity') }}</x-app.input.label>
                    <x-app.input.input name="capacity" id="capacity"
                        value="{{ old('capacity', $customizeProduct->capacity) }}" />
                    <x-input-error :messages="$errors->get('capacity')" class="mt-1" />
                </div>

                <div class="flex flex-col">
                    <x-app.input.label id="weight" class="mb-1">{{ __('Weight (In KG)') }}</x-app.input.label>
                    <x-app.input.input name="weight" id="weight" class="decimal-input"
                        value="{{ old('weight', $customizeProduct->weight) }}" />
                    <x-input-error :messages="$errors->get('weight')" class="mt-1" />
                </div>

                <div class="flex flex-col">
                    <x-app.input.label id="refrigerant" class="mb-1">{{ __('Refrigerant') }}</x-app.input.label>
                    <x-app.input.input name="refrigerant" id="refrigerant"
                        value="{{ old('refrigerant', $customizeProduct->refrigerant) }}" />
                    <x-input-error :messages="$errors->get('refrigerant')" class="mt-1" />
                </div>

                <div class="flex flex-col">
                    <x-app.input.label id="power_input" class="mb-1">{{ __('Power Input') }}</x-app.input.label>
                    <x-app.input.input name="power_input" id="power_input"
                        value="{{ old('power_input', $customizeProduct->power_input) }}" />
                    <x-input-error :messages="$errors->get('power_input')" class="mt-1" />
                </div>

                <div class="flex flex-col">
                    <x-app.input.label id="power_consumption" class="mb-1">{{ __('Power Consumption (KWH/24H)') }}</x-app.input.label>
                    <x-app.input.input name="power_consumption" id="power_consumption"
                        value="{{ old('power_consumption', $customizeProduct->power_consumption) }}" />
                    <x-input-error :messages="$errors->get('power_consumption')" class="mt-1" />
                </div>

                <div class="flex flex-col">
                    <x-app.input.label id="voltage_frequency" class="mb-1">{{ __('Voltage / Frequency') }}</x-app.input.label>
                    <x-app.input.input name="voltage_frequency" id="voltage_frequency"
                        value="{{ old('voltage_frequency', $customizeProduct->voltage_frequency) }}" />
                    <x-input-error :messages="$errors->get('voltage_frequency')" class="mt-1" />
                </div>

                <div class="flex flex-col">
                    <x-app.input.label id="standard_features" class="mb-1">{{ __('Standard Features') }}</x-app.input.label>
                    <x-app.input.input name="standard_features" id="standard_features"
                        value="{{ old('standard_features', $customizeProduct->standard_features) }}" />
                    <x-input-error :messages="$errors->get('standard_features')" class="mt-1" />
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="mt-8 flex justify-end gap-x-4">
            <x-app.button.submit>{{ __('Save and Update') }}</x-app.button.submit>
        </div>
    </form>
@endsection
