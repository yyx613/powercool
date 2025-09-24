@extends('layouts.app')
@section('title', 'Warranty')

@section('content')
    <div class="mb-6 flex justify-between items-center">
        <x-app.page-title url="{{ $back_url }}">{{ __('Create Material Used') }}</x-app.page-title>
    </div>
    @include('components.app.alert.parent')
    <form action="{{ route('warranty.store') }}" method="POST" enctype="multipart/form-data" id="form">
        @csrf
        <div class="bg-white p-4 rounded-md shadow" id="content-container">
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-8 w-full mb-4">
                <div class="flex flex-col">
                    <x-app.input.label id="serial_no" class="mb-1">{{ __('Serial No') }} <span
                            class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select2 name="serial_no" id="serial_no" placeholder="{{ __('Select a serial no') }}">
                        <option value="">{{ __('Select a serial no') }}</option>
                        @foreach ($pcs as $pc)
                            <option value="{{ $pc->id }}" @selected(old('serial_no') == $pc->id)>{{ $pc->sku }}</option>
                        @endforeach
                    </x-app.input.select2>
                    <x-input-error :messages="$errors->get('serial_no')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="qty" class="mb-1">{{ __('Qty') }} <span
                            class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="qty" id="qty" value="{{ old('name') }}" class="int-input" />
                    <x-input-error :messages="$errors->get('qty')" class="mt-1" />
                </div>
            </div>
            <div class="mt-8 flex justify-end gap-x-4">
                <x-app.button.submit>{{ __('Save and Update') }}</x-app.button.submit>
            </div>
        </div>
    </form>
@endsection
