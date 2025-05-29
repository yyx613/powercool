@extends('layouts.app')

@section('content')
    <div class="mb-6">
        <x-app.page-title url="{{ route('raw_material_request.index') }}">
            {{ __('Create Raw Material Request') }}
        </x-app.page-title>
    </div>
    @include('components.app.alert.parent')
    <form action="{{ route('raw_material_request.store') }}" method="POST" enctype="multipart/form-data" id="form">
        @csrf
        <div class="bg-white p-4 border rounded-md">
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-8 w-full items-start">
                <div class="flex flex-col">
                    <x-app.input.label id="production_id" class="mb-1">{{ __('Production ID') }}</x-app.input.label>
                    <x-app.input.select2 name="production_id" id="production_id" :hasError="$errors->has('production_id')"
                        placeholder="{{ __('Select a production') }}">
                        <option value="">{{ __('Select a production') }}</option>
                        @foreach ($productions as $val)
                            <option value="{{ $val->id }}" @selected(old('production_id') == $val->id)>{{ $val->sku }}
                            </option>
                        @endforeach
                    </x-app.input.select2>

                    <x-input-error :messages="$errors->get('production_id')" class="mt-2" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="product" class="mb-1">{{ __('Product') }} <span
                            class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select2 name="product" id="product" :hasError="$errors->has('product')"
                        placeholder="{{ __('Select a product') }}">
                        <option value="">{{ __('Select a product') }}</option>
                        @foreach ($products as $val)
                            <option value="{{ $val->id }}" @selected(old('product') == $val->id)>{{ $val->model_name }}
                            </option>
                        @endforeach
                    </x-app.input.select2>
                    <x-input-error :messages="$errors->get('product')" class="mt-2" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="qty" class="mb-1">{{ __('Quantity') }} <span
                            class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="qty" id="qty" value="{{ old('qty') }}" :hasError="$errors->has('qty')"
                        class="int-input" />
                    <x-input-error :messages="$errors->get('qty')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="remark" class="mb-1">{{ __('Remark') }}</x-app.input.label>
                    <x-app.input.input name="remark" id="remark" value="{{ old('remark') }}" :hasError="$errors->has('remark')" />
                    <x-input-error :messages="$errors->get('remark')" class="mt-1" />
                </div>
            </div>
            <div class="mt-8 flex justify-end">
                <x-app.button.submit id="submit-btn">{{ __('Save and Update') }}</x-app.button.submit>
            </div>
        </div>
    </form>
@endsection
