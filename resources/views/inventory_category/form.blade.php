@extends('layouts.app')

@section('content')
    <div class="mb-6 flex justify-between items-center">
        <x-app.page-title
            url="{{ route('inventory_category.index') }}">{{ __(isset($cat) ? 'Edit Category' : 'Create Category') }}</x-app.page-title>
    </div>
    @include('components.app.alert.parent')
    <div class="bg-white p-4 border rounded-md">
        <form action="{{ route('inventory_category.upsert') }}" method="POST" enctype="multipart/form-data" id="form">
            @csrf
            <div>
                @if (isset($cat))
                    <x-app.input.input name="category_id" id="category_id" value="{{ isset($cat) ? $cat->id : null }}"
                        class="hidden" />
                @endif
                <div class="grid grid-cols-2 lg:grid-cols-3 gap-8 w-full">
                    <div class="flex flex-col">
                        <x-app.input.label id="name" class="mb-1">{{ __('Name') }} <span
                                class="text-sm text-red-500">*</span></x-app.input.label>
                        <x-app.input.input name="name" id="name" value="{{ isset($cat) ? $cat->name : null }}" />
                        <x-app.message.error id="name_err" />
                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                    </div>
                    <div class="flex flex-col">
                        <x-app.input.label id="company_group" class="mb-1">{{ __('Company Group') }} <span
                                class="text-sm text-red-500">*</span></x-app.input.label>
                        <x-app.input.select name="company_group" id="company_group" :hasError="$errors->has('company_group')">
                            <option value="">{{ __('Select a company group') }}</option>
                            @foreach ($company_group as $key => $val)
                                <option value="{{ $key }}" @selected(old('company_group', isset($cat) ? $cat->company_group : null) == $key)>{{ $val }}
                                </option>
                            @endforeach
                        </x-app.input.select>
                        <x-input-error :messages="$errors->get('company_group')" class="mt-1" />
                    </div>
                    <div class="flex flex-col">
                        <x-app.input.label id="factory" class="mb-1">{{ __('Factory') }} <span
                                class="text-sm text-red-500">*</span></x-app.input.label>
                        <x-app.input.select name="factory" id="factory" :hasError="$errors->has('factory')">
                            <option value="">{{ __('Select a factory') }}</option>
                            @foreach ($factories as $key => $val)
                                <option value="{{ $key }}" @selected(old('factory', isset($cat) ? $cat->factory : null) === $key)>{{ $val }}
                                </option>
                            @endforeach
                        </x-app.input.select>
                        <x-input-error :messages="$errors->get('factory')" class="mt-1" />
                    </div>
                    <div class="flex flex-col">
                        <x-app.input.label id="status" class="mb-1">{{ __('Status') }} <span
                                class="text-sm text-red-500">*</span></x-app.input.label>
                        <x-app.input.select name="status" id="status">
                            <option value="">{{ __('Select a Active/Inactive') }}</option>
                            <option value="1" @selected(old('status', isset($cat) ? $cat->is_active : null) == 1)>{{ __('Active') }}</option>
                            <option value="0" @selected(old('status', isset($cat) ? $cat->is_active : null) === 0)>{{ __('Inactive') }}</option>
                        </x-app.input.select>
                        <x-input-error :messages="$errors->get('status')" class="mt-1" />
                    </div>
                </div>
            </div>
            <div class="mt-8 flex justify-end gap-x-4">
                @if (!isset($cat))
                    <x-app.button.submit id="submit-create-btn">{{ __('Save and Create') }}</x-app.button.submit>
                @endif
                <x-app.button.submit id="submit-update-btn">{{ __('Save and Update') }}</x-app.button.submit>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        CATEGORY = @json($cat ?? null);
        FORM_CAN_SUBMIT = true

        $('#submit-create-btn').on('click', function(e) {
            let url = $('#form').attr('action')
            url = `${url}?create_again=true`

            $('#form').attr('action', url)
        })
    </script>
@endpush
