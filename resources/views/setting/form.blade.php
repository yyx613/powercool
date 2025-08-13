@extends('layouts.app')
@section('title', 'Settings')

@section('content')
    <div class="mb-6 flex justify-between items-center">
        <x-app.page-title url="{{ route('setting.index') }}">{{ isset($setting) ? __('Edit Setting') : __('Create Setting') }}</x-app.page-title>
    </div>
    @include('components.app.alert.parent')
    <form action="{{ isset($setting) ? route('setting.update', ['setting' => $setting]) : route('setting.store') }}" method="POST" enctype="multipart/form-data" id="form">
        @csrf
        <div class="bg-white p-4 rounded-md shadow" id="content-container">
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-8 w-full mb-4">
                <div class="flex flex-col">
                    <x-app.input.label id="name" class="mb-1">{{ __('Name') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="name" id="name" value="{{ old('name') ?? (isset($setting) ? $setting->name : null) }}" disabled />
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="value" class="mb-1">{{ __('Value') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="value" id="value" value="{{ old('value') ?? (isset($setting) ? $setting->value : null) }}" />
                    <x-input-error :messages="$errors->get('value')" class="mt-1" />
                </div>
            </div>
            <div class="mt-8 flex justify-end gap-x-4">
                <x-app.button.submit>{{ __('Save and Update') }}</x-app.button.submit>
            </div>
        </div>
    </form>
@endsection