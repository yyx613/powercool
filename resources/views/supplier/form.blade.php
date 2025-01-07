@extends('layouts.app')

@section('content')
    <div class="mb-6 flex justify-between items-center">
        <x-app.page-title url="{{ route('supplier.index') }}">{{ isset($supplier) ? __('Edit Supplier') : __('Create Supplier') }}</x-app.page-title>
    </div>
    @include('components.app.alert.parent')
    <form action="{{ isset($supplier) ? route('supplier.upsert', ['supplier' => $supplier->id]) : route('supplier.upsert') }}" method="POST" enctype="multipart/form-data" class="flex flex-col gap-8">
        @csrf
        <!-- 1st Panel -->
        <div class="bg-white p-4 rounded-md shadow">
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4 md:gap-8 w-full mb-4">
                <div class="flex flex-col">
                    <x-app.input.label id="category" class="mb-1">{{ __('Category') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select2 name="category" id="category" :hasError="$errors->has('category')" placeholder="{{ __('Select a category') }}">
                        <option value="">{{ __('Select a category') }}</option>
                        @foreach ($business_types as $key => $value)
                            <option value="{{ $key }}" @selected(old('category', isset($supplier) ? $supplier->category : null) == $key)>{{ $value }}</option>
                        @endforeach
                    </x-app.input.select2>
                    <x-input-error :messages="$errors->get('category')" class="mt-2" />
                </div>
                <div class="flex flex-col hidden" id="local_oversea-container">
                    <x-app.input.label id="local_oversea" class="mb-1">{{ __('Type') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select name="local_oversea" id="local_oversea" :hasError="$errors->has('local_oversea')">
                        <option value="">{{ __('Select a type') }}</option>
                        <option value="1" @selected(old('local_oversea', isset($supplier) ? $supplier->type : null) == 1)>{{ __('Local') }}</option>
                        <option value="2" @selected(old('local_oversea', isset($supplier) ? $supplier->type : null) == 2)>{{ __('Oversea') }}</option>
                    </x-app.input.select>
                    <x-input-error :messages="$errors->get('local_oversea')" class="mt-2" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="tin_number" class="mb-1">{{ __('TIN') }} <span class="text-sm text-red-500" id="tin_number_required_star">*</span></x-app.input.label>
                    <x-app.input.input name="tin_number" id="tin_number" :hasError="$errors->has('tin_number')" value="{{ old('tin_number', isset($supplier) ? $supplier->tin_number : null) }}" />
                    <x-input-error :messages="$errors->get('tin_number')" class="mt-2" />
                </div>
                <div class="flex flex-col hidden non-individual-fields-container">
                    <x-app.input.label id="company_registration_number" class="mb-1">{{ __('Business Reg No.') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="company_registration_number" id="company_registration_number" :hasError="$errors->has('company_registration_number')" value="{{ old('company_registration_number', isset($supplier) ? $supplier->company_registration_number : null) }}"/>
                    <x-input-error :messages="$errors->get('company_registration_number')" class="mt-2" />
                </div>
                <div class="flex flex-col hidden non-individual-fields-container">
                    <x-app.input.label id="msic_code" class="mb-1">{{ __('MSIC Code') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select2 name="msic_code" id="msic_code" :hasError="$errors->has('msic_code')" placeholder="{{ __('Select a MSIC Code') }}">
                        <option value="">{{ __('Select a Msic Code') }}</option>
                        @foreach ($msics as $msic)
                            <option value="{{ $msic->id }}" @selected(old('msic_code', isset($supplier->msicCode->id) ? $supplier->msicCode->id == $msic->id : null))>{{ $msic->code }} - {{ $msic->description }}</option>
                        @endforeach
                    </x-app.input.select2>
                    <x-input-error :messages="$errors->get('msic_code')" class="mt-2" />
                </div>
                <div class="flex flex-col hidden non-individual-fields-container">
                    <x-app.input.label id="business_activity_desc" class="mb-1">{{ __('Business Activity Desc.') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select2 name="business_activity_desc" id="business_activity_desc" :hasError="$errors->has('business_activity_desc')" placeholder="{{ __('Select a business activity desc') }}">
                        <option value="">{{ __('Select a business activity desc') }}</option>
                        @foreach ($msics as $msic)
                            <option value="{{ $msic->description }}" @selected(old('business_activity_desc', isset($supplier) ? $supplier->business_act_desc : null) == $msic->description )>{{ $msic->description }}</option>
                        @endforeach
                    </x-app.input.select>
                    <x-input-error :messages="$errors->get('business_activity_desc')" class="mt-2" />
                </div>
                <div class="flex flex-col hidden non-individual-fields-container">
                    <x-app.input.label id="sst_number" class="mb-1">{{ __('SST Reg No.') }}</x-app.input.label>
                    <x-app.input.input name="sst_number" id="sst_number" :hasError="$errors->has('sst_number')" value="{{ old('sst_number', isset($supplier) ? $supplier->sst_number: null) }}"/>
                    <x-input-error :messages="$errors->get('sst_number')" class="mt-2" />
                </div>
                <div class="flex flex-col hidden non-individual-fields-container">
                    <x-app.input.label id="tourism_tax_reg_no" class="mb-1">{{ __('Tourism Tax Reg No.') }}</x-app.input.label>
                    <x-app.input.input name="tourism_tax_reg_no" id="tourism_tax_reg_no" :hasError="$errors->has('tourism_tax_reg_no')" value="{{ old('tourism_tax_reg_no', isset($supplier) ? $supplier->tourism_tax_reg_no: null) }}"/>
                    <x-input-error :messages="$errors->get('tourism_tax_reg_no')" class="mt-2" />
                </div>
                <div class="flex flex-col hidden non-individual-fields-container">
                    <x-app.input.label id="prev_gst_reg_no" class="mb-1">{{ __('Prev. GST Reg No.') }}</x-app.input.label>
                    <x-app.input.input name="prev_gst_reg_no" id="prev_gst_reg_no" :hasError="$errors->has('prev_gst_reg_no')" value="{{ old('prev_gst_reg_no', isset($supplier) ? $supplier->prev_gst_reg_no : null) }}"/>
                    <x-input-error :messages="$errors->get('prev_gst_reg_no')" class="mt-2" />
                </div>
                <div class="flex flex-col hidden individual-fields-container"">
                    <x-app.input.label id="identity_type" class="mb-1">{{ __('Identity Type') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="identity_type" id="identity_type" :hasError="$errors->has('identity_type')" value="{{ old('identity_type', isset($supplier) ? $supplier->identity_type : null) }}"/>
                    <x-input-error :messages="$errors->get('identity_type')" class="mt-2" />
                </div>
                <div class="flex flex-col hidden individual-fields-container"">
                    <x-app.input.label id="identity_no" class="mb-1">{{ __('Identity No.') }}</x-app.input.label>
                    <x-app.input.input name="identity_no" id="identity_no" :hasError="$errors->has('identity_no')" value="{{ old('identity_no', isset($supplier) ? $supplier->identity_no : null) }}"/>
                    <x-input-error :messages="$errors->get('identity_no')" class="mt-2" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="registered_name" class="mb-1">{{ __('Registered Name') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="registered_name" id="registered_name" :hasError="$errors->has('registered_name')" value="{{ old('registered_name', isset($supplier) ? $supplier->registered_name : null) }}"/>
                    <x-input-error :messages="$errors->get('registered_name')" class="mt-2" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="trade_name" class="mb-1">{{ __('Trade Name') }}</x-app.input.label>
                    <x-app.input.input name="trade_name" id="trade_name" :hasError="$errors->has('trade_name')" value="{{ old('trade_name', isset($supplier) ? $supplier->trade_name: null) }}"/>
                    <x-input-error :messages="$errors->get('trade_name')" class="mt-2" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="phone_number" class="mb-1">{{ __('Phone Number') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="phone_number" id="phone_number" :hasError="$errors->has('phone_number')" value="{{ old('phone_number', isset($supplier) ? $supplier->phone : null) }}"/>
                    <x-input-error :messages="$errors->get('phone_number')" class="mt-2" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="email" class="mb-1">{{ __('Email Address') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="email" id="email" type="email" :hasError="$errors->has('email')" value="{{ old('email', isset($supplier) ? $supplier->email : null) }}"/>
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>
            </div>
        </div>
        <!-- 2nd Panel -->
        <div class="bg-white p-4 rounded-md shadow">
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4 md:gap-8 w-full mb-4">
                <!-- <div class="flex flex-col">
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
                </div> -->
                @if (isset($supplier))
                    <div class="flex flex-col">
                        <x-app.input.label id="code" class="mb-1">{{ __('Code') }}</x-app.input.label>
                        <x-app.input.input name="code" id="code" :hasError="$errors->has('code')" value="{{ old('code', isset($supplier) ? $supplier->sku : null) }}" disabled="true"/>
                        <x-input-error :messages="$errors->get('code')" class="mt-2" />
                    </div>
                @endif
                <div class="flex flex-col">
                    <x-app.input.label id="company_group" class="mb-1">{{ __('Company Group') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select2 name="company_group" id="company_group" :hasError="$errors->has('company_group')" placeholder="{{ __('Select a company group') }}">
                        <option value="">{{ __('Select a company group') }}</option>
                        @foreach ($company_group as $key => $value)
                            <option value="{{ $key }}" @selected(old('company_group', isset($supplier) ? $supplier->company_group : null) == $key)>{{ $value }}</option>
                        @endforeach
                    </x-app.input.select2>
                    <x-input-error :messages="$errors->get('company_group')" class="mt-2" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="prefix" class="mb-1">{{ __('Prefix') }}</x-app.input.label>
                    <x-app.input.select2 name="prefix" id="prefix" :hasError="$errors->has('prefix')" placeholder="{{ __('Select a prefix') }}">
                        <option value="">{{ __('Select a prefix') }}</option>
                        @foreach ($prefix as $key => $value)
                            <option value="{{ $key }}" @selected(old('prefix', isset($supplier) ? $supplier->prefix : null) == $key)>{{ $value }}</option>
                        @endforeach
                    </x-app.input.select2>
                    <x-input-error :messages="$errors->get('prefix')" class="mt-2" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="customer_name" class="mb-1">{{ __('Supplier Name') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="customer_name" id="customer_name" :hasError="$errors->has('customer_name')" value="{{ old('customer_name', isset($supplier) ? $supplier->name : null) }}" />
                    <x-input-error :messages="$errors->get('customer_name')" class="mt-2" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="company_name" class="mb-1">{{ __('Company Name') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="company_name" id="company_name" :hasError="$errors->has('company_name')" value="{{ old('company_name', isset($supplier) ? $supplier->company_name : null) }}" />
                    <x-input-error :messages="$errors->get('company_name')" class="mt-2" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="mobile_number" class="mb-1">{{ __('Mobile Number') }}</x-app.input.label>
                    <x-app.input.input name="mobile_number" id="mobile_number" :hasError="$errors->has('mobile_number')" value="{{ old('mobile_number', isset($supplier) ? $supplier->mobile_number : null) }}"/>
                    <x-app.message.error id="mobile_number_err"/>
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="website" class="mb-1">{{ __('Website') }}</x-app.input.label>
                    <x-app.input.input name="website" id="website" :hasError="$errors->has('website')" value="{{ old('website', isset($supplier) ? $supplier->website : null) }}"/>
                    <x-input-error :messages="$errors->get('website')" class="mt-2" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="currency" class="mb-1">{{ __('Currency') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select name="currency" id="currency" :hasError="$errors->has('currency')">
                        <option value="">{{ __('Select a currency') }}</option>
                        @foreach ($currencies as $curr)
                            <option value="{{ $curr->id }}" @selected(old('currency', isset($supplier) ? $supplier->currency_id : null) == $curr->id)>{{ $curr->name }}</option>
                        @endforeach
                    </x-app.input.select>
                    <x-input-error :messages="$errors->get('currency')" class="mt-2" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="area" class="mb-1">{{ __('Area') }}</x-app.input.label>
                    <x-app.input.select name="area" id="area" :hasError="$errors->has('area')">
                        <option value="">{{ __('Select a area') }}</option>
                        @foreach ($areas as $area)
                            <option value="{{ $area->id }}" @selected(old('area', isset($supplier) ? $supplier->area_id : null) == $area->id)>{{ $area->name }}</option>
                        @endforeach
                    </x-app.input.select>
                    <x-input-error :messages="$errors->get('area')" class="mt-2" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="debtor_type" class="mb-1">{{ __('Debtor Type') }}</x-app.input.label>
                    <x-app.input.select name="debtor_type" id="debtor_type" :hasError="$errors->has('debtor_type')">
                        <option value="">{{ __('Select a debtor type') }}</option>
                        @foreach ($debtor_types as $debtor_type)
                            <option value="{{ $debtor_type->id }}" @selected(old('debtor_type', isset($supplier) ? $supplier->debtor_type_id : null) == $debtor_type->id)>{{ $debtor_type->name }}</option>
                        @endforeach
                    </x-app.input.select>
                    <x-input-error :messages="$errors->get('debtor_type')" class="mt-2" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="sale_agent" class="mb-1">{{ __('Sale Agent') }}</x-app.input.label>
                    <x-app.input.select2 name="sale_agent" id="sale_agent" :hasError="$errors->has('sale_agent')" placeholder="{{ __('Select a sale agent') }}">
                        <option value="">{{ __('Select a sale agent') }}</option>
                        @foreach ($sales as $sa)
                            <option value="{{ $sa->id }}" @selected(old('sale', isset($supplier) ? $supplier->sale_agent : null) == $sa->id)>{{ $sa->name }}</option>
                        @endforeach
                    </x-app.input.select2>
                    <x-input-error :messages="$errors->get('sale_agent')" class="mt-2" />
                </div>
                <div class="flex flex-col col-span">
                    <x-app.input.label id="credit_term" class="mb-1">{{ __('Credit Terms') }}</x-app.input.label>
                    <x-app.input.select name="credit_term[]" multiple>
                        @foreach ($credit_terms as $ct)
                            <option value="{{ $ct->id }}" @selected(old('credit_term', isset($supplier) ? in_array($ct->id, $supplier->creditTerms()->pluck('credit_term_id')->toArray()) : null))>{{ $ct->name }}</option>
                        @endforeach
                    </x-app.input.select>
                    <x-input-error :messages="$errors->get('credit_term')" class="mt-2" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="status" class="mb-1">{{ __('Status') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select name="status" id="status" :hasError="$errors->has('status')">
                        <option value="">{{ __('Select a status') }}</option>
                        <option value="1" @selected(old('status', isset($supplier) ? $supplier->is_active : null) == 1)>{{ __('Active') }}</option>
                        <option value="0" @selected(old('status', isset($supplier) ? $supplier->is_active : null) === 0)>{{ __('Inactive') }}</option>
                    </x-app.input.select>
                    <x-input-error :messages="$errors->get('status')" class="mt-2" />
                </div>
                <div class="flex flex-col col-span-2">
                    <x-app.input.label id="remark" class="mb-1">{{ __('Remark') }}</x-app.input.label>
                    <x-app.input.input name="remark" id="remark" :hasError="$errors->has('remark')" value="{{ old('remark', isset($supplier) ? $supplier->remark : null) }}" />
                    <x-input-error :messages="$errors->get('remark')" class="mt-2" />
                </div>
                <div class="flex flex-col col-span-2 md:col-span-3">
                    <x-app.input.label id="location" class="mb-1">{{ __('Location') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.textarea name="location" id="location" :hasError="$errors->has('location')" text="{{ old('location', isset($supplier) ? $supplier->location : null) }}" />
                    <x-input-error :messages="$errors->get('location')" class="mt-2" />
                </div>
            </div>
            <div class="mt-8 flex justify-end">
                <x-app.button.submit id="submit-btn">{{ __('Save and Update') }}</x-app.button.submit>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    SUPPLIER = @json($supplier ?? null);

    $(document).ready(function() {
        $('select[name="category"]').trigger('change')
    })

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

    $('select[name="local_oversea"]').on('change', function() {
            let val = $(this).val()

            if (val == 2) {
                $('input[name="tin_number"]').val('EI000000000020')
            } else {
                $('input[name="tin_number"]').val(null)
            }
        })
        $('select[name="category"]').on('change', function() {
            let val = $(this).val()

            if (val == 1) {
                $('#local_oversea-container').removeClass('hidden')
                $('#tin_number_required_star').removeClass('hidden')
                $('.individual-fields-container').addClass('hidden')
                $('.non-individual-fields-container').removeClass('hidden')

            }
            else if (val == 2) {
                $('#local_oversea-container').addClass('hidden')
                $('#tin_number_required_star').addClass('hidden')
                $('.individual-fields-container').removeClass('hidden')
                $('.non-individual-fields-container').addClass('hidden')
            }
            else if (val == 3) {
                $('#local_oversea-container').addClass('hidden')
                $('#tin_number_required_star').addClass('hidden')
                $('.individual-fields-container').addClass('hidden')
                $('.non-individual-fields-container').removeClass('hidden')
            }
        })
</script>
@endpush
