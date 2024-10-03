@extends('layouts.app')

@section('content')
    <div class="mb-6 flex justify-between items-center">
        <x-app.page-title url="{{ route('debtor_type.index') }}">{{ isset($debtor_type) ? 'Edit Debtor Type' : 'Create Debtor Type' }}</x-app.page-title>
    </div>
    @include('components.app.alert.parent')
    <form action="{{ isset($debtor_type) ? route('debtor_type.update', ['debtor' => $debtor_type]) : route('debtor_type.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="bg-white p-4 rounded-md shadow" id="content-container">
            <div class="grid grid-cols-3 gap-8 w-full mb-4">
                <div class="flex flex-col">
                    <x-app.input.label id="name" class="mb-1">Name <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="name" id="name" value="{{ old('name') ?? (isset($debtor_type) ? $debtor_type->name : null) }}" />
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="status" class="mb-1">Status <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select name="status" id="status" :hasError="$errors->has('status')">
                        <option value="">Select a Active/Inactive</option>
                        <option value="1" @selected(old('status', isset($debtor_type) ? $debtor_type->is_active : null) == 1)>Active</option>
                        <option value="0" @selected(old('status', isset($debtor_type) ? $debtor_type->is_active : null) === 0)>Inactive</option>
                    </x-app.input.select>
                    <x-input-error :messages="$errors->get('status')" class="mt-1" />
                </div>    
            </div>
            <div class="mt-8 flex justify-end">
                <x-app.button.submit>{{ isset($debtor_type) ? 'Update Debtor Type' : 'Create Debtor Type' }}</x-app.button.submit>
            </div>
        </div>
    </form>
@endsection
