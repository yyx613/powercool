@extends('layouts.app')
@section('title', 'Ad-hoc Services')

@section('content')
    <div class="mb-6 flex justify-between items-center">
        <x-app.page-title url="{{ route('adhoc_service.index') }}">{{ isset($service) ? __('Edit Ad-hoc Service') : __('Create Ad-hoc Service') }}</x-app.page-title>
    </div>
    @include('components.app.alert.parent')
    <form action="{{ route('adhoc_service.upsert') }}" method="POST" enctype="multipart/form-data" id="form">
        @csrf
        <input type="hidden" name="service_id" value="{{ isset($service) ? $service->id : null }}">
        <div class="bg-white p-4 rounded-md shadow" id="content-container">
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-8 w-full mb-4">
                <div class="flex flex-col">
                    <x-app.input.label id="sku" class="mb-1">{{ __('SKU') }}</x-app.input.label>
                    <x-app.input.input name="sku" id="sku" value="{{ isset($service) ? $service->sku : $sku }}" :disabled="true" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="name" class="mb-1">{{ __('Name') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="name" id="name" value="{{ old('name') ?? (isset($service) ? $service->name : null) }}" />
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="min_amount" class="mb-1">{{ __('Min Amount (RM)') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="min_amount" id="min_amount" class="decimal-input" value="{{ old('min_amount') ?? (isset($service) ? $service->min_amount : null) }}" />
                    <x-input-error :messages="$errors->get('min_amount')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="max_amount" class="mb-1">{{ __('Max Amount (RM)') }}</x-app.input.label>
                    <x-app.input.input name="max_amount" id="max_amount" class="decimal-input" value="{{ old('max_amount') ?? (isset($service) ? $service->max_amount : null) }}" />
                    <x-input-error :messages="$errors->get('max_amount')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="status" class="mb-1">{{ __('Status') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select name="status" id="status" :hasError="$errors->has('status')">
                        <option value="">{{ __('Select a Active/Inactive') }}</option>
                        <option value="1" @selected(old('status', isset($service) ? $service->is_active : 1) == 1)>{{ __('Active') }}</option>
                        <option value="0" @selected(old('status', isset($service) ? $service->is_active : null) === 0)>{{ __('Inactive') }}</option>
                    </x-app.input.select>
                    <x-input-error :messages="$errors->get('status')" class="mt-1" />
                </div>
            </div>
            <div class="mt-8 flex justify-end gap-x-4">
                @if (!isset($service))
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
