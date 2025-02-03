@extends('layouts.app')

@section('content')
    <div class="mb-6 flex justify-between items-center">
        <x-app.page-title url="{{ route('credit_term.index') }}">{{ isset($credit) ? __('Edit Credit Term') : __('Create Credit Term') }}</x-app.page-title>
    </div>
    @include('components.app.alert.parent')
    <form action="{{ isset($credit) ? route('credit_term.update', ['credit' => $credit]) : route('credit_term.store') }}" method="POST" enctype="multipart/form-data" id="form">
        @csrf
        <div class="bg-white p-4 rounded-md shadow" id="content-container">
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-8 w-full mb-4">
                <div class="flex flex-col">
                    <x-app.input.label id="name" class="mb-1">{{ __('Name') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="name" id="name" value="{{ old('name') ?? (isset($credit) ? $credit->name : null) }}" :hasError="$errors->has('name')" />
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="by_pass_conversion" class="mb-1">{{ __('By Pass Conversion') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select name="by_pass_conversion" id="by_pass_conversion" :hasError="$errors->has('by_pass_conversion')">
                        <option value="">{{ __('Select a Active/Inactive') }}</option>
                        <option value="1" @selected(old('status', isset($credit) ? $credit->by_pass_conversion : null) == 1)>{{ __('Yes') }}</option>
                        <option value="0" @selected(old('status', isset($credit) ? $credit->by_pass_conversion : null) === 0)>{{ __('No') }}</option>
                    </x-app.input.select>
                    <x-input-error :messages="$errors->get('by_pass_conversion')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="status" class="mb-1">{{ __('Status') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select name="status" id="status" :hasError="$errors->has('status')">
                        <option value="">{{ __('Select a Active/Inactive') }}</option>
                        <option value="1" @selected(old('status', isset($credit) ? $credit->is_active : null) == 1)>{{ __('Active') }}</option>
                        <option value="0" @selected(old('status', isset($credit) ? $credit->is_active : null) === 0)>{{ __('Inactive') }}</option>
                    </x-app.input.select>
                    <x-input-error :messages="$errors->get('status')" class="mt-1" />
                </div>
            </div>
            <div class="mt-8 flex justify-end gap-x-4">
                @if (!isset($credit))
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
