@extends('layouts.app')
@section('title', 'UOM')

@section('content')
    <div class="mb-6 flex justify-between items-center">
        <x-app.page-title url="{{ route('uom.index') }}">{{ isset($uom) ? __('Edit UOM') : __('Create UOM') }}</x-app.page-title>
    </div>
    @include('components.app.alert.parent')
    <form action="{{ isset($uom) ? route('uom.update', ['uom' => $uom]) : route('uom.store') }}" method="POST" enctype="multipart/form-data" id="form">
        @csrf
        <div class="bg-white p-4 rounded-md shadow" id="content-container">
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-8 w-full mb-4">
                <div class="flex flex-col">
                    <x-app.input.label id="name" class="mb-1">{{ __('Name') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="name" id="name" value="{{ old('name') ?? (isset($uom) ? $uom->name : null) }}" />
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="company_group" class="mb-1">{{ __('Company Group') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select name="company_group" id="company_group" :hasError="$errors->has('company_group')">
                        <option value="">{{ __('Select a company group') }}</option>
                        @foreach ($company_group as $key => $val)
                            <option value="{{ $key }}" @selected(old('company_group', isset($uom) ? $uom->company_group : null) === $key)>{{ $val }}</option>
                        @endforeach
                    </x-app.input.select>
                    <x-input-error :messages="$errors->get('company_group')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="status" class="mb-1">{{ __('Status') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select name="status" id="status" :hasError="$errors->has('status')">
                        <option value="">{{ __('Select a Active/Inactive') }}</option>
                        <option value="1" @selected(old('status', isset($uom) ? $uom->is_active : null) == 1)>{{ __('Active') }}</option>
                        <option value="0" @selected(old('status', isset($uom) ? $uom->is_active : null) === 0)>{{ __('Inactive') }}</option>
                    </x-app.input.select>
                    <x-input-error :messages="$errors->get('status')" class="mt-1" />
                </div>
            </div>
            <div class="mt-8 flex justify-end gap-x-4">
                @if (!isset($uom))
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
