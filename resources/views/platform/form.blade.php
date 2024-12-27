@extends('layouts.app')

@section('content')
    <div class="mb-6 flex justify-between items-center">
        <x-app.page-title url="{{ route('platform.index') }}">{{ isset($platform) ? __('Edit Platform') : __('Create Platform') }}</x-app.page-title>
    </div>
    @include('components.app.alert.parent')
    <form action="{{ isset($platform) ? route('platform.update', ['platform' => $platform]) : route('platform.store') }}" method="POST" enctype="multipart/form-data" id="form">
        @csrf
        <div class="bg-white p-4 rounded-md shadow" id="content-container">
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-8 w-full mb-4">
                <div class="flex flex-col">
                    <x-app.input.label id="name" class="mb-1">{{ __('Name') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="name" id="name" value="{{ old('name') ?? (isset($platform) ? $platform->name : null) }}" />
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="can_submit_einvoice" class="mb-1">{{ __('Can Submit Einvoice') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select name="can_submit_einvoice" id="can_submit_einvoice" :hasError="$errors->has('can_submit_einvoice')">
                        <option value="">{{ __('Select a Yes/No') }}</option>
                        <option value="1" @selected(old('can_submit_einvoice', isset($platform) ? $platform->can_submit_einvoice : null) == 1)>{{ __('Yes') }}</option>
                        <option value="0" @selected(old('can_submit_einvoice', isset($platform) ? $platform->can_submit_einvoice : null) === 0)>{{ __('No') }}</option>
                    </x-app.input.select>
                    <x-input-error :messages="$errors->get('can_submit_einvoice')" class="mt-1" />
                </div>   
                <div class="flex flex-col">
                    <x-app.input.label id="status" class="mb-1">{{ __('Status') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select name="status" id="status" :hasError="$errors->has('status')">
                        <option value="">{{ __('Select a Active/Inactive') }}</option>
                        <option value="1" @selected(old('status', isset($platform) ? $platform->is_active : null) == 1)>{{ __('Active') }}</option>
                        <option value="0" @selected(old('status', isset($platform) ? $platform->is_active : null) === 0)>{{ __('Inactive') }}</option>
                    </x-app.input.select>
                    <x-input-error :messages="$errors->get('status')" class="mt-1" />
                </div>    
            </div>
            <div class="mt-8 flex justify-end gap-x-4">
                @if (!isset($platform))
                    <x-app.button.submit id="submit-create-btn">{{ __('Save and Create') }}</x-app.button.submit>
                @endif
                <x-app.button.submit>{{ __('Save and Update') }}</x-app.button.submit>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    $('#submit-create-btn').on('click', function(e) {
        let url = $('#form').attr('action')
        url = `${url}?create_again=true`

        $('#form').attr('action', url)
    })
</script>
@endpush