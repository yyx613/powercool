@extends('layouts.app')

@section('content')
    <div class="mb-6 flex justify-between items-center">
        <x-app.page-title>{{ isset($supplier) ? 'Edit Supplier' : 'Create Supplier' }}</x-app.page-title>
    </div>
    @include('components.app.alert.parent')
    
    <form action="{{ isset($supplier) ? route('supplier.upsert', ['supplier' => $supplier->id]) : route('supplier.upsert') }}" method="POST" enctype="multipart/form-data" id="info-form">
        @csrf
        <div class="bg-white p-4 rounded-md shadow" id="content-container">
            <div class="grid grid-cols-3 gap-8 w-full mb-4">
                <div class="flex flex-col">
                    <x-app.input.label class="mb-1">Picture</x-app.input.label>
                    <x-app.input.file id="picture[]" :hasError="$errors->has('picture')"/>
                    <x-input-error :messages="$errors->get('picture')" class="mt-2" />
                    <div class="uploaded-file-preview-container" data-id="picture">
                        <div class="p-y.5 px-1.5 rounded bg-blue-50 mt-2 hidden" id="uploaded-file-template">
                            <a href="" target="_blank" class="text-blue-700 text-xs"></a>
                        </div>
                        @if (isset($supplier))
                            @foreach ($supplier->pictures as $att)
                                <div class="p-y.5 px-1.5 rounded bg-blue-50 mt-2 old-preview">
                                    <a href="{{ $att->url }}" target="_blank" class="text-blue-700 text-xs">{{ $att->src }}</a>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="prefix" class="mb-1">Prefix</x-app.input.label>
                    <x-app.input.select2 name="prefix" id="prefix" :hasError="$errors->has('prefix')" placeholder="Select a prefix">
                        <option value="">Select a prefix</option>
                        @foreach ($prefix as $key => $value)
                            <option value="{{ $key }}" @selected(old('prefix', isset($supplier) ? $supplier->prefix : null) == $key)>{{ $value }}</option>
                        @endforeach
                    </x-app.input.select2>
                    <x-input-error :messages="$errors->get('prefix')" class="mt-2" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="customer_name" class="mb-1">Supplier Name <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="customer_name" id="customer_name" :hasError="$errors->has('customer_name')" value="{{ old('customer_name', isset($supplier) ? $supplier->name : null) }}" />
                    <x-input-error :messages="$errors->get('customer_name')" class="mt-2" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="company_name" class="mb-1">Company Name</x-app.input.label>
                    <x-app.input.input name="company_name" id="company_name" :hasError="$errors->has('company_name')" value="{{ old('company_name', isset($supplier) ? $supplier->company_name : null) }}" />
                    <x-input-error :messages="$errors->get('company_name')" class="mt-2" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="company_registration_number" class="mb-1">Company Registration Number</x-app.input.label>
                    <x-app.input.input name="company_registration_number" id="company_registration_number" :hasError="$errors->has('company_registration_number')" value="{{ old('company_registration_number', isset($supplier) ? $supplier->company_registration_number : null) }}"/>
                    <x-input-error :messages="$errors->get('company_registration_number')" class="mt-2" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="phone_number" class="mb-1">Phone Number <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="phone_number" id="phone_number" :hasError="$errors->has('phone_number')" value="{{ old('phone_number', isset($supplier) ? $supplier->phone : null) }}"/>
                    <x-input-error :messages="$errors->get('phone_number')" class="mt-2" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="email" class="mb-1">Email</x-app.input.label>
                    <x-app.input.input name="email" id="email" type="email" :hasError="$errors->has('email')" value="{{ old('email', isset($supplier) ? $supplier->email : null) }}"/>
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="website" class="mb-1">Website</x-app.input.label>
                    <x-app.input.input name="website" id="website" :hasError="$errors->has('website')" value="{{ old('website', isset($supplier) ? $supplier->website : null) }}"/>
                    <x-input-error :messages="$errors->get('website')" class="mt-2" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="under_warranty" class="mb-1">Under Warranty <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select name="under_warranty" id="under_warranty" :hasError="$errors->has('under_warranty')">
                        <option value="">Select a Yes/No</option>
                        <option value="1" @selected(old('under_warranty', isset($supplier) ? $supplier->under_warranty : null) == 1)>Yes</option>
                        <option value="0" @selected(old('under_warranty', isset($supplier) ? $supplier->under_warranty : null) === 0)>No</option>
                    </x-app.input.select>
                    <x-input-error :messages="$errors->get('under_warranty')" class="mt-2" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="status" class="mb-1">Status <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select name="status" id="status" :hasError="$errors->has('status')">
                        <option value="">Select a Active/Inactive</option>
                        <option value="1" @selected(old('status', isset($supplier) ? $supplier->is_active : null) == 1)>Active</option>
                        <option value="0" @selected(old('status', isset($supplier) ? $supplier->is_active : null) === 0)>Inactive</option>
                    </x-app.input.select>
                    <x-input-error :messages="$errors->get('status')" class="mt-2" />
                </div>
                <div class="flex flex-col col-span-2">
                    <x-app.input.label id="remark" class="mb-1">Remark</x-app.input.label>
                    <x-app.input.input name="remark" id="remark" :hasError="$errors->has('remark')" value="{{ old('remark', isset($supplier) ? $supplier->remark : null) }}" />
                    <x-input-error :messages="$errors->get('remark')" class="mt-2" />
                </div>
                <div class="flex flex-col col-span-3">
                    <x-app.input.label id="location" class="mb-1">Location <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.textarea name="location" id="location" :hasError="$errors->has('location')" text="{{ old('location', isset($supplier) ? $supplier->location : null) }}" />
                    <x-input-error :messages="$errors->get('location')" class="mt-2" />
                </div>
            </div>
            <div class="mt-8 flex justify-end">
                <x-app.button.submit id="submit-btn">Save and Update</x-app.button.submit>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    SUPPLIER = @json($supplier ?? null);

    $('input[name="picture[]"]').on('change', function() {
        let files = $(this).prop('files');

        $('.uploaded-file-preview-container[data-id="picture"]').find('.old-preview').remove()
    
        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            
            let clone = $('#uploaded-file-template')[0].cloneNode(true);
            $(clone).find('a').text(file.name)
            $(clone).find('a').attr('href', URL.createObjectURL(file))
            $(clone).addClass('old-preview')
            $(clone).removeClass('hidden')
            $(clone).removeAttr('id')

            $('.uploaded-file-preview-container[data-id="picture"]').append(clone)
            $('.uploaded-file-preview-container[data-id="picture"]').removeClass('hidden')
        }
    })
</script>
@endpush