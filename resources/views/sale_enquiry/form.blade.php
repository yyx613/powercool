@extends('layouts.app')
@section('title', 'Sale Enquiry')

@section('content')
    <div class="mb-6 flex justify-between items-center">
        <x-app.page-title url="{{ route('sale_enquiry.index') }}">
            {{ isset($enquiry) ? __('Edit Sale Enquiry') : __('Create Sale Enquiry') }}
        </x-app.page-title>
    </div>
    @include('components.app.alert.parent')

    <form action="{{ isset($enquiry) ? route('sale_enquiry.update', ['enquiry' => $enquiry]) : route('sale_enquiry.store') }}" method="POST">
        @csrf
        <div class="bg-white p-4 rounded-md shadow">
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-8 w-full mb-4">

                <!-- Enquiry Date & Time -->
                <div class="flex flex-col">
                    <x-app.input.label id="enquiry_date" class="mb-1">{{ __('Enquiry Date & Time') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input type="datetime-local" name="enquiry_date" id="enquiry_date"
                        :hasError="$errors->has('enquiry_date')"
                        value="{{ old('enquiry_date', isset($enquiry) ? $enquiry->enquiry_date?->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}" />
                    <x-input-error :messages="$errors->get('enquiry_date')" class="mt-1" />
                </div>

                <!-- Enquiry Source -->
                <div class="flex flex-col">
                    <x-app.input.label id="enquiry_source" class="mb-1">{{ __('Enquiry Source') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select name="enquiry_source" id="enquiry_source" :hasError="$errors->has('enquiry_source')">
                        <option value="">{{ __('Select source') }}</option>
                        <option value="1" @selected(old('enquiry_source', isset($enquiry) ? $enquiry->enquiry_source : null) == 1)>{{ __('Website') }}</option>
                        <option value="2" @selected(old('enquiry_source', isset($enquiry) ? $enquiry->enquiry_source : null) == 2)>{{ __('WhatsApp') }}</option>
                        <option value="3" @selected(old('enquiry_source', isset($enquiry) ? $enquiry->enquiry_source : null) == 3)>{{ __('Phone Call') }}</option>
                        <option value="4" @selected(old('enquiry_source', isset($enquiry) ? $enquiry->enquiry_source : null) == 4)>{{ __('Walk-In') }}</option>
                        <option value="5" @selected(old('enquiry_source', isset($enquiry) ? $enquiry->enquiry_source : null) == 5)>{{ __('Social Media') }}</option>
                        <option value="6" @selected(old('enquiry_source', isset($enquiry) ? $enquiry->enquiry_source : null) == 6)>{{ __('Referral') }}</option>
                    </x-app.input.select>
                    <x-input-error :messages="$errors->get('enquiry_source')" class="mt-1" />
                </div>

                <!-- Name -->
                <div class="flex flex-col">
                    <x-app.input.label id="name" class="mb-1">{{ __('Name') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="name" id="name" :hasError="$errors->has('name')"
                        value="{{ old('name', isset($enquiry) ? $enquiry->name : null) }}" />
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>

                <!-- Phone Number -->
                <div class="flex flex-col">
                    <x-app.input.label id="phone_number" class="mb-1">{{ __('Phone Number') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="phone_number" id="phone_number" :hasError="$errors->has('phone_number')"
                        value="{{ old('phone_number', isset($enquiry) ? $enquiry->phone_number : null) }}" />
                    <x-input-error :messages="$errors->get('phone_number')" class="mt-1" />
                </div>

                <!-- Email (Optional) -->
                <div class="flex flex-col">
                    <x-app.input.label id="email" class="mb-1">{{ __('Email Address') }}</x-app.input.label>
                    <x-app.input.input type="email" name="email" id="email" :hasError="$errors->has('email')"
                        value="{{ old('email', isset($enquiry) ? $enquiry->email : null) }}" />
                    <x-input-error :messages="$errors->get('email')" class="mt-1" />
                </div>

                <!-- Preferred Contact Method (Optional) -->
                <div class="flex flex-col">
                    <x-app.input.label id="preferred_contact_method" class="mb-1">{{ __('Preferred Contact Method') }}</x-app.input.label>
                    <x-app.input.select name="preferred_contact_method" id="preferred_contact_method" :hasError="$errors->has('preferred_contact_method')">
                        <option value="">{{ __('Select method') }}</option>
                        <option value="1" @selected(old('preferred_contact_method', isset($enquiry) ? $enquiry->preferred_contact_method : null) == 1)>{{ __('WhatsApp') }}</option>
                        <option value="2" @selected(old('preferred_contact_method', isset($enquiry) ? $enquiry->preferred_contact_method : null) == 2)>{{ __('Call') }}</option>
                        <option value="3" @selected(old('preferred_contact_method', isset($enquiry) ? $enquiry->preferred_contact_method : null) == 3)>{{ __('Email') }}</option>
                    </x-app.input.select>
                    <x-input-error :messages="$errors->get('preferred_contact_method')" class="mt-1" />
                </div>

                <!-- Country (Optional) -->
                <div class="flex flex-col">
                    <x-app.input.label id="country" class="mb-1">{{ __('Country') }}</x-app.input.label>
                    <x-app.input.input name="country" id="country" :hasError="$errors->has('country')"
                        value="{{ old('country', isset($enquiry) ? $enquiry->country : null) }}" />
                    <x-input-error :messages="$errors->get('country')" class="mt-1" />
                </div>

                <!-- State (Optional) -->
                <div class="flex flex-col">
                    <x-app.input.label id="state" class="mb-1">{{ __('State') }}</x-app.input.label>
                    <x-app.input.input name="state" id="state" :hasError="$errors->has('state')"
                        value="{{ old('state', isset($enquiry) ? $enquiry->state : null) }}" />
                    <x-input-error :messages="$errors->get('state')" class="mt-1" />
                </div>

                <!-- Category (Optional) -->
                <div class="flex flex-col">
                    <x-app.input.label id="category" class="mb-1">{{ __('Category / Type of Enquiry') }}</x-app.input.label>
                    <x-app.input.input name="category" id="category" :hasError="$errors->has('category')"
                        value="{{ old('category', isset($enquiry) ? $enquiry->category : null) }}"
                        placeholder="{{ __('e.g., Product enquiry, Pricing, Support') }}" />
                    <x-input-error :messages="$errors->get('category')" class="mt-1" />
                </div>

                <!-- Product Interested In (Optional) -->
                <div class="flex flex-col" id="product-select-container">
                    <x-app.input.label class="mb-1">{{ __('Product / Service Interested In') }}</x-app.input.label>
                    <select name="product_id" id="product_id" class="@error('product_id') border-red-500 @enderror">
                        @if(isset($enquiry) && $enquiry->product)
                            <option value="{{ $enquiry->product->id }}" selected>{{ $enquiry->product->sku }} - {{ $enquiry->product->model_name }}</option>
                        @endif
                    </select>
                    <x-input-error :messages="$errors->get('product_id')" class="mt-1" />
                </div>

                <!-- Assigned Staff (Optional) -->
                <div class="flex flex-col">
                    <x-app.input.label id="assigned_user_id" class="mb-1">{{ __('Assigned Staff / Salesperson') }}</x-app.input.label>
                    <x-app.input.select2 name="assigned_user_id" id="assigned_user_id"
                        :hasError="$errors->has('assigned_user_id')"
                        placeholder="{{ __('Select staff') }}">
                        <option value="">{{ __('Select staff') }}</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" @selected(old('assigned_user_id', isset($enquiry) ? $enquiry->assigned_user_id : null) == $user->id)>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </x-app.input.select2>
                    <x-input-error :messages="$errors->get('assigned_user_id')" class="mt-1" />
                </div>

                <!-- Priority Level (Optional) -->
                <div class="flex flex-col">
                    <x-app.input.label id="priority" class="mb-1">{{ __('Priority Level') }}</x-app.input.label>
                    <x-app.input.select name="priority" id="priority" :hasError="$errors->has('priority')">
                        <option value="">{{ __('Select priority') }}</option>
                        <option value="1" @selected(old('priority', isset($enquiry) ? $enquiry->priority : null) == 1)>{{ __('Low') }}</option>
                        <option value="2" @selected(old('priority', isset($enquiry) ? $enquiry->priority : null) == 2)>{{ __('Medium') }}</option>
                        <option value="3" @selected(old('priority', isset($enquiry) ? $enquiry->priority : null) == 3)>{{ __('High') }}</option>
                    </x-app.input.select>
                    <x-input-error :messages="$errors->get('priority')" class="mt-1" />
                </div>

                <!-- Status -->
                <div class="flex flex-col">
                    <x-app.input.label id="status" class="mb-1">{{ __('Current Status') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select name="status" id="status" :hasError="$errors->has('status')">
                        <option value="">{{ __('Select status') }}</option>
                        <option value="1" @selected(old('status', isset($enquiry) ? $enquiry->status : 1) == 1)>{{ __('New') }}</option>
                        <option value="2" @selected(old('status', isset($enquiry) ? $enquiry->status : null) == 2)>{{ __('In Progress') }}</option>
                        <option value="3" @selected(old('status', isset($enquiry) ? $enquiry->status : null) == 3)>{{ __('Closed (Converted)') }}</option>
                        <option value="4" @selected(old('status', isset($enquiry) ? $enquiry->status : null) == 4)>{{ __('Closed (Dropped)') }}</option>
                    </x-app.input.select>
                    <x-input-error :messages="$errors->get('status')" class="mt-1" />
                </div>

                <!-- Description / Message (Optional) - Full width -->
                <div class="flex flex-col col-span-2 lg:col-span-3">
                    <x-app.input.label id="description" class="mb-1">{{ __('Description / Message') }}</x-app.input.label>
                    <x-app.input.textarea name="description" id="description" :hasError="$errors->has('description')"
                        :text="old('description', isset($enquiry) ? $enquiry->description : null)" />
                    <x-input-error :messages="$errors->get('description')" class="mt-1" />
                </div>
            </div>

            <div class="mt-8 flex justify-end">
                <x-app.button.submit id="submit-btn">
                    {{ isset($enquiry) ? __('Update Enquiry') : __('Create Enquiry') }}
                </x-app.button.submit>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    // Initialize AJAX Select2 for product
    bulidSelect2Ajax({
        selector: '#product_id',
        placeholder: '{{ __('Search a product') }}',
        url: '{{ route('sale_enquiry.get_products') }}',
        processResults: function(data) {
            return {
                results: $.map(data.products, function(item) {
                    return {
                        id: item.id,
                        text: `${item.sku} - ${item.model_name}`
                    };
                })
            }
        }
    })
    $(`#product-select-container .select2`).addClass('border border-gray-300 rounded-md overflow-hidden')

    $('#submit-btn').on('click', function() {
        $('form').submit()
    })
</script>
@endpush
