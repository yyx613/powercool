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
                        <option value="2" @selected(old('enquiry_source', isset($enquiry) ? $enquiry->enquiry_source : null) == 2)>{{ __('Facebook') }}</option>
                        <option value="3" @selected(old('enquiry_source', isset($enquiry) ? $enquiry->enquiry_source : null) == 3)>{{ __('Shopee') }}</option>
                        <option value="4" @selected(old('enquiry_source', isset($enquiry) ? $enquiry->enquiry_source : null) == 4)>{{ __('Lazada') }}</option>
                        <option value="5" @selected(old('enquiry_source', isset($enquiry) ? $enquiry->enquiry_source : null) == 5)>{{ __('Walk In') }}</option>
                        <option value="6" @selected(old('enquiry_source', isset($enquiry) ? $enquiry->enquiry_source : null) == 6)>{{ __('Referral') }}</option>
                        <option value="7" @selected(old('enquiry_source', isset($enquiry) ? $enquiry->enquiry_source : null) == 7)>{{ __('Instagram') }}</option>
                        <option value="8" @selected(old('enquiry_source', isset($enquiry) ? $enquiry->enquiry_source : null) == 8)>{{ __('Tiktok') }}</option>
                        <option value="9" @selected(old('enquiry_source', isset($enquiry) ? $enquiry->enquiry_source : null) == 9)>{{ __('XHS') }}</option>
                        <option value="10" @selected(old('enquiry_source', isset($enquiry) ? $enquiry->enquiry_source : null) == 10)>{{ __('Phone Call') }}</option>
                        <option value="11" @selected(old('enquiry_source', isset($enquiry) ? $enquiry->enquiry_source : null) == 11)>{{ __('WhatsApp (Not from Platform)') }}</option>
                        <option value="12" @selected(old('enquiry_source', isset($enquiry) ? $enquiry->enquiry_source : null) == 12)>{{ __('Google') }}</option>
                    </x-app.input.select>
                    <x-input-error :messages="$errors->get('enquiry_source')" class="mt-1" />
                </div>

                <!-- Name -->
                <div class="flex flex-col">
                    <x-app.input.label id="name" class="mb-1">{{ __('Customer Name') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
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

                <!-- Preferred Contact Method -->
                <div class="flex flex-col">
                    <x-app.input.label id="preferred_contact_method" class="mb-1">{{ __('Preferred Contact Method') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select name="preferred_contact_method" id="preferred_contact_method" :hasError="$errors->has('preferred_contact_method')">
                        <option value="">{{ __('Select method') }}</option>
                        <option value="1" @selected(old('preferred_contact_method', isset($enquiry) ? $enquiry->preferred_contact_method : null) == 1)>{{ __('WhatsApp') }}</option>
                        <option value="2" @selected(old('preferred_contact_method', isset($enquiry) ? $enquiry->preferred_contact_method : null) == 2)>{{ __('Call') }}</option>
                        <option value="3" @selected(old('preferred_contact_method', isset($enquiry) ? $enquiry->preferred_contact_method : null) == 3)>{{ __('Email') }}</option>
                    </x-app.input.select>
                    <x-input-error :messages="$errors->get('preferred_contact_method')" class="mt-1" />
                </div>

                <!-- Country -->
                <div class="flex flex-col" id="country-select-container">
                    <x-app.input.label id="country_id" class="mb-1">{{ __('Country') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select2 name="country_id" id="country_id"
                        :hasError="$errors->has('country_id')"
                        placeholder="{{ __('Select country') }}">
                        <option value="">{{ __('Select country') }}</option>
                        @foreach ($countries as $country)
                            <option value="{{ $country->id }}" @selected(old('country_id', isset($enquiry) ? $enquiry->country_id : null) == $country->id)>
                                {{ $country->name }}
                            </option>
                        @endforeach
                    </x-app.input.select2>
                    <x-input-error :messages="$errors->get('country_id')" class="mt-1" />
                </div>

                <!-- State -->
                <div class="flex flex-col" id="state-select-container">
                    <x-app.input.label id="state_id" class="mb-1">{{ __('State') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select2 name="state_id" id="state_id"
                        :hasError="$errors->has('state_id')"
                        placeholder="{{ __('Select state') }}">
                        <option value="">{{ __('Select state') }}</option>
                        @if(isset($enquiry) && $enquiry->stateModel)
                            <option value="{{ $enquiry->stateModel->id }}" selected>{{ $enquiry->stateModel->name }}</option>
                        @endif
                    </x-app.input.select2>
                    <x-input-error :messages="$errors->get('state_id')" class="mt-1" />
                </div>

                <!-- Category -->
                <div class="flex flex-col">
                    <x-app.input.label id="category" class="mb-1">{{ __('Category / Type of Enquiry') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select name="category" id="category" :hasError="$errors->has('category')">
                        <option value="">{{ __('Select type of enquiry') }}</option>
                        <option value="1" @selected(old('category', isset($enquiry) ? $enquiry->category : null) == 1)>{{ __('Product / Pricing Enquiry') }}</option>
                        <option value="2" @selected(old('category', isset($enquiry) ? $enquiry->category : null) == 2)>{{ __('Service Enquiry') }}</option>
                        <option value="3" @selected(old('category', isset($enquiry) ? $enquiry->category : null) == 3)>{{ __('Relocation Fridge Enquiry') }}</option>
                        <option value="4" @selected(old('category', isset($enquiry) ? $enquiry->category : null) == 4)>{{ __('Trade-IN Enquiry') }}</option>
                        <option value="5" @selected(old('category', isset($enquiry) ? $enquiry->category : null) == 5)>{{ __('Rental Enquiry') }}</option>
                    </x-app.input.select>
                    <x-input-error :messages="$errors->get('category')" class="mt-1" />
                </div>

                <!-- Product Interested In -->
                <div class="flex flex-col" id="product-select-container">
                    <x-app.input.label class="mb-1">{{ __('Product / Service Interested In') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <select name="product_id" id="product_id" class="@error('product_id') border-red-500 @enderror">
                        @if(isset($enquiry) && $enquiry->product)
                            <option value="{{ $enquiry->product->id }}" selected>{{ $enquiry->product->sku }} - {{ $enquiry->product->model_desc }}</option>
                        @endif
                    </select>
                    <x-input-error :messages="$errors->get('product_id')" class="mt-1" />
                </div>

                <!-- Assigned Staff -->
                <div class="flex flex-col">
                    <x-app.input.label id="assigned_user_id" class="mb-1">{{ __('Assigned Staff / Salesperson') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
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

                <!-- Priority Level -->
                <div class="flex flex-col">
                    <x-app.input.label id="priority" class="mb-1">{{ __('Priority Level') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
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
                        <option value="3" @selected(old('status', isset($enquiry) ? $enquiry->status : null) == 3)>{{ __('Closed Deal (Converted)') }}</option>
                        <option value="4" @selected(old('status', isset($enquiry) ? $enquiry->status : null) == 4)>{{ __('No Deal') }}</option>
                    </x-app.input.select>
                    <x-input-error :messages="$errors->get('status')" class="mt-1" />
                </div>

                <!-- Quality -->
                <div class="flex flex-col">
                    <x-app.input.label id="quality" class="mb-1">{{ __('Quality') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select name="quality" id="quality" :hasError="$errors->has('quality')">
                        <option value="">{{ __('Select quality') }}</option>
                        <option value="1" @selected(old('quality', isset($enquiry) ? $enquiry->quality : null) == 1)>{{ __('Seen and Reply') }}</option>
                        <option value="2" @selected(old('quality', isset($enquiry) ? $enquiry->quality : null) == 2)>{{ __('Seen No Reply') }}</option>
                        <option value="3" @selected(old('quality', isset($enquiry) ? $enquiry->quality : null) == 3)>{{ __('No Seen No Reply') }}</option>
                    </x-app.input.select>
                    <x-input-error :messages="$errors->get('quality')" class="mt-1" />
                </div>

                <!-- Promotion Type -->
                <div class="flex flex-col">
                    <x-app.input.label id="promotion_id" class="mb-1">{{ __('Promotion Type') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select2 name="promotion_id" id="promotion_id"
                        :hasError="$errors->has('promotion_id')"
                        placeholder="{{ __('Select promotion') }}">
                        <option value="">{{ __('Select promotion') }}</option>
                        @foreach ($promotions as $promotion)
                            <option value="{{ $promotion->id }}" @selected(old('promotion_id', isset($enquiry) ? $enquiry->promotion_id : null) == $promotion->id)>
                                {{ $promotion->sku }} - {{ $promotion->desc }}
                            </option>
                        @endforeach
                    </x-app.input.select2>
                    <x-input-error :messages="$errors->get('promotion_id')" class="mt-1" />
                </div>

                <!-- Description / Message (Optional) - Full width -->
                <div class="flex flex-col col-span-2 lg:col-span-3">
                    <x-app.input.label id="description" class="mb-1">{{ __('Customer Message/ Remark') }}</x-app.input.label>
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
                        text: `${item.sku} - ${item.model_desc}`
                    };
                })
            }
        }
    })
    $(`#product-select-container .select2`).addClass('border border-gray-300 rounded-md overflow-hidden')

    // Country/State cascading dropdown (use select2:select event for Select2 dropdowns)
    $('select[name="country_id"]').on('change', function(e) {
        var countryId = $(this).val();
        var $stateSelect = $('select[name="state_id"]');

        // Clear and disable state dropdown
        $stateSelect.empty().append('<option value="">{{ __('Select state') }}</option>');

        if (countryId) {
            // Fetch states for selected country
            $.ajax({
                url: '{{ route("country.get_states", ":country") }}'.replace(':country', countryId),
                type: 'GET',
                success: function(data) {
                    $.each(data, function(index, state) {
                        $stateSelect.append(
                            $('<option></option>')
                                .val(state.id)
                                .text(state.name)
                        );
                    });
                    // Trigger Select2 to refresh
                    $stateSelect.trigger('change.select2');
                },
                error: function() {
                    console.log('Failed to fetch states');
                }
            });
        }
    });

    // On page load, if country is already selected (edit mode OR old value from validation failure), load states
    @php
        $loadCountryId = old('country_id', isset($enquiry) ? $enquiry->country_id : null);
        $loadStateId = old('state_id', isset($enquiry) ? $enquiry->state_id : null);
    @endphp
    @if($loadCountryId)
        $(document).ready(function() {
            var countryId = '{{ $loadCountryId }}';
            var selectedStateId = '{{ $loadStateId }}';
            var $stateSelect = $('select[name="state_id"]');

            if (countryId) {
                $.ajax({
                    url: '{{ route("country.get_states", ":country") }}'.replace(':country', countryId),
                    type: 'GET',
                    success: function(data) {
                        $stateSelect.empty().append('<option value="">{{ __('Select state') }}</option>');
                        $.each(data, function(index, state) {
                            var $option = $('<option></option>')
                                .val(state.id)
                                .text(state.name);
                            if (state.id == selectedStateId) {
                                $option.attr('selected', 'selected');
                            }
                            $stateSelect.append($option);
                        });
                        $stateSelect.trigger('change.select2');
                    }
                });
            }
        });
    @endif

    $('#submit-btn').on('click', function() {
        $('form').submit()
    })
</script>
@endpush
