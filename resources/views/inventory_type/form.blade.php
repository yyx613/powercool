@extends('layouts.app')
@section('title', 'Product Type')

@section('content')
    <div class="mb-6 flex justify-between items-center">
        <x-app.page-title url="{{ route('inventory_type.index') }}">{{ __(isset($type) ? 'Edit Type' : 'Create Type') }}</x-app.page-title>
    </div>
    @include('components.app.alert.parent')
    <div class="bg-white p-4 border rounded-md">
        <form action="{{ route('inventory_type.upsert') }}" method="POST" enctype="multipart/form-data" id="form">
            @csrf
            <div>
                @if (isset($type))
                    <x-app.input.input name="type_id" id="type_id" value="{{ isset($type) ? $type->id : null }}" class="hidden" />
                @endif
                <div class="grid grid-cols-2 lg:grid-cols-3 gap-8 w-full">
                    <div class="flex flex-col">
                        <x-app.input.label id="name" class="mb-1">{{ __('Name') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                        <x-app.input.input name="name" id="name" value="{{ isset($type) ? $type->name : null }}" />
                        <x-app.message.error id="name_err"/>
                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                    </div>
                    <div class="flex flex-col">
                        <x-app.input.label id="type" class="mb-1">{{ __('Type') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                        <x-app.input.select name="type" id="type">
                            <option value="">{{ __('Select a type') }}</option>
                            @foreach ($inventory_types as $key => $val)
                                <option value="{{ $key }}" @selected(old('type', isset($type) ? $type->type : null) === $key)>{{ $val }}</option>
                            @endforeach
                        </x-app.input.select>
                        <x-input-error :messages="$errors->get('type')" class="mt-1" />
                    </div>
                    <div class="flex flex-col">
                        <x-app.input.label id="company_group" class="mb-1">{{ __('Company Group') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                        <x-app.input.select name="company_group" id="company_group">
                            <option value="">{{ __('Select a company group') }}</option>
                            @foreach ($company_group as $key => $val)
                                <option value="{{ $key }}" @selected(old('company_group', isset($type) ? $type->company_group : null) === $key)>{{ $val }}</option>
                            @endforeach
                        </x-app.input.select>
                        <x-input-error :messages="$errors->get('company_group')" class="mt-1" />
                    </div>
                    <div class="flex flex-col">
                        <x-app.input.label id="status" class="mb-1">{{ __('Status') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                        <x-app.input.select name="status" id="status">
                            <option value="">{{ __('Select a Active/Inactive') }}</option>
                            <option value="1" @selected(old('status', isset($type) ? $type->is_active : null) == 1)>{{ __('Active') }}</option>
                            <option value="0" @selected(old('status', isset($type) ? $type->is_active : null) === 0)>{{ __('Inactive') }}</option>
                        </x-app.input.select>
                        <x-input-error :messages="$errors->get('status')" class="mt-1" />
                    </div>
                </div>
            </div>
            <div class="mt-8 flex justify-end gap-x-4">
                @if (!isset($type))
                    <x-app.button.submit id="submit-create-btn">{{  __('Save and Create') }}</x-app.button.submit>
                @endif
                <x-app.button.submit id="submit-update-btn">{{ __('Save and Update') }}</x-app.button.submit>
            </div>
        </form>
    </div>

@endsection

@push('scripts')
<script>
    CATEGORY = @json($type ?? null);
    FORM_CAN_SUBMIT = true

    $('#submit-create-btn').on('click', function(e) {
        let url = $('#form').attr('action')
        url = `${url}?create_again=true`

        $('#form').attr('action', url)
    })
</script>
@endpush
