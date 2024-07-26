@extends('layouts.app')

@section('content')
    <div class="mb-6 flex justify-between items-center">
        <x-app.page-title>{{ isset($customer) ? 'Edit Customer' : 'Create Customer' }}</x-app.page-title>
    </div>
    @include('components.app.alert.parent')
    <form action="{{ isset($customer) ? route('customer.update', ['customer' => $customer]) : route('customer.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="bg-white p-4 rounded-md shadow" id="content-container">
            <div class="grid grid-cols-3 gap-8 w-full mb-4">
                <div class="flex flex-col">
                    <x-app.input.label class="mb-1">Picture</x-app.input.label>
                    <x-app.input.file id="picture[]" :hasError="$errors->has('picture')"/>
                    <x-input-error :messages="$errors->get('picture')" class="mt-1" />
                    <div class="uploaded-file-preview-container" data-id="picture">
                        <div class="p-y.5 px-1.5 rounded bg-blue-50 mt-2 hidden" id="uploaded-file-template">
                            <a href="" target="_blank" class="text-blue-700 text-xs"></a>
                        </div>
                        @if (isset($customer))
                            @foreach ($customer->pictures as $att)
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
                            <option value="{{ $key }}" @selected(old('prefix', isset($customer) ? $customer->prefix : null) == $key)>{{ $value }}</option>
                        @endforeach
                    </x-app.input.select2>
                    <x-input-error :messages="$errors->get('prefix')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="customer_name" class="mb-1">Customer Name <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="customer_name" id="customer_name" :hasError="$errors->has('customer_name')" value="{{ old('customer_name', isset($customer) ? $customer->name : null) }}" />
                    <x-input-error :messages="$errors->get('customer_name')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="company_name" class="mb-1">Company Name</x-app.input.label>
                    <x-app.input.input name="company_name" id="company_name" :hasError="$errors->has('company_name')" value="{{ old('company_name', isset($customer) ? $customer->company_name : null) }}" />
                    <x-input-error :messages="$errors->get('company_name')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="company_address" class="mb-1">Company Address</x-app.input.label>
                    <x-app.input.input name="company_address" id="company_address" :hasError="$errors->has('company_address')" value="{{ old('company_address', isset($customer) ? $customer->company_address : null) }}" />
                    <x-input-error :messages="$errors->get('company_address')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="city" class="mb-1">City</x-app.input.label>
                    <x-app.input.input name="city" id="city" :hasError="$errors->has('city')" value="{{ old('city', isset($customer) ? $customer->city : null) }}" />
                    <x-input-error :messages="$errors->get('city')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="state" class="mb-1">State</x-app.input.label>
                    <x-app.input.input name="state" id="state" :hasError="$errors->has('state')" value="{{ old('state', isset($customer) ? $customer->state : null) }}" />
                    <x-input-error :messages="$errors->get('state')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="zip_code" class="mb-1">Zip Code</x-app.input.label>
                    <x-app.input.input name="zip_code" id="zip_code" :hasError="$errors->has('zip_code')" value="{{ old('zip_code', isset($customer) ? $customer->zip_code : null) }}" class="int-input" />
                    <x-input-error :messages="$errors->get('zip_code')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="company_registration_number" class="mb-1">Company Registration Number</x-app.input.label>
                    <x-app.input.input name="company_registration_number" id="company_registration_number" :hasError="$errors->has('company_registration_number')" value="{{ old('company_registration_number', isset($customer) ? $customer->company_registration_number : null) }}"/>
                    <x-input-error :messages="$errors->get('company_registration_number')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="phone_number" class="mb-1">Phone Number <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="phone_number" id="phone_number" :hasError="$errors->has('phone_number')" value="{{ old('phone_number', isset($customer) ? $customer->phone : null) }}"/>
                    <x-input-error :messages="$errors->get('phone_number')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="email" class="mb-1">Email</x-app.input.label>
                    <x-app.input.input name="email" id="email" type="email" :hasError="$errors->has('email')" value="{{ old('email', isset($customer) ? $customer->email : null) }}"/>
                    <x-input-error :messages="$errors->get('email')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="website" class="mb-1">Website</x-app.input.label>
                    <x-app.input.input name="website" id="website" :hasError="$errors->has('website')" value="{{ old('website', isset($customer) ? $customer->website : null) }}"/>
                    <x-input-error :messages="$errors->get('website')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="under_warranty" class="mb-1">Under Warranty <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select name="under_warranty" id="under_warranty" :hasError="$errors->has('under_warranty')">
                        <option value="">Select a Yes/No</option>
                        <option value="1" @selected(old('under_warranty', isset($customer) ? $customer->under_warranty : null) == 1)>Yes</option>
                        <option value="0" @selected(old('under_warranty', isset($customer) ? $customer->under_warranty : null) === 0)>No</option>
                    </x-app.input.select>
                    <x-input-error :messages="$errors->get('under_warranty')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="status" class="mb-1">Status <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select name="status" id="status" :hasError="$errors->has('status')">
                        <option value="">Select a Active/Inactive</option>
                        <option value="1" @selected(old('status', isset($customer) ? $customer->is_active : null) == 1)>Active</option>
                        <option value="0" @selected(old('status', isset($customer) ? $customer->is_active : null) === 0)>Inactive</option>
                    </x-app.input.select>
                    <x-input-error :messages="$errors->get('status')" class="mt-1" />
                </div>
                <div class="flex flex-col col-span-2">
                    <x-app.input.label id="remark" class="mb-1">Remark</x-app.input.label>
                    <x-app.input.input name="remark" id="remark" :hasError="$errors->has('remark')" value="{{ old('remark', isset($customer) ? $customer->remark : null) }}" />
                    <x-input-error :messages="$errors->get('remark')" class="mt-1" />
                </div>
            </div>
            <div class="mt-4 flex justify-end">
                <x-app.button.submit>{{ isset($customer) ? 'Update Customer' : 'Create New Customer' }}</x-app.button.submit>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
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