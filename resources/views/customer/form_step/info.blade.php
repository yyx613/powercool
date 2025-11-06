<form action="" method="POST" enctype="multipart/form-data" id="info-form" class="flex flex-col gap-8">
    <!-- 1st panel -->
    <div class="bg-white p-4 border rounded-md">
        <div class="flex justify-between mb-6">
            <div class="flex items-center border-l-8 border-yellow-400 px-3 py-1 bg-yellow-50 w-fit">
                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512"
                    height="512">
                    <path
                        d="M12,0A12,12,0,1,0,24,12,12.013,12.013,0,0,0,12,0Zm0,22A10,10,0,1,1,22,12,10.011,10.011,0,0,1,12,22Z" />
                    <path d="M12,10H11a1,1,0,0,0,0,2h1v6a1,1,0,0,0,2,0V12A2,2,0,0,0,12,10Z" />
                    <circle cx="12" cy="6.5" r="1.5" />
                </svg>
                <span class="text-lg ml-3 font-bold">{{ __('Information') }}</span>
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" name="for_einvoice" id="for_einvoice" class="rounded-sm"
                    @checked(isset($duplicate) ? $duplicate->for_einvoice : (isset($customer) ? $customer->for_einvoice : null)) />
                <x-app.input.label id="for_einvoice">{{ __('For E-Invoice') }}</x-app.input.label>
            </div>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-6 md:gap-8 w-full mb-8">
            <div class="flex flex-col">
                <x-app.input.label id="category" class="mb-1">{{ __('Category') }} <span
                        class="text-sm text-red-500">*</span></x-app.input.label>
                <x-app.input.select2 name="category" id="category" :hasError="$errors->has('category')"
                    placeholder="{{ __('Select a category') }}">
                    <option value="">{{ __('Select a category') }}</option>
                    @foreach ($business_types as $key => $value)
                        <option value="{{ $key }}" @selected(old('category', isset($duplicate) ? $duplicate->category: (isset($customer) ? $customer->category : null)) == $key)>{{ $value }}</option>
                    @endforeach
                </x-app.input.select2>
                <x-app.message.error id="category_err" />
            </div>
            <div class="flex flex-col hidden" id="local_oversea-container">
                <x-app.input.label id="local_oversea" class="mb-1">{{ __('Type') }} <span
                        class="text-sm text-red-500 hidden for_einvoice-required">*</span></x-app.input.label>
                <x-app.input.select name="local_oversea" id="local_oversea" :hasError="$errors->has('local_oversea')">
                    <option value="">{{ __('Select a type') }}</option>
                    <option value="1" @selected(old('type', isset($duplicate) ? $duplicate->type : (isset($customer) ? $customer->type : null)) == 1)>{{ __('Local') }}</option>
                    <option value="2" @selected(old('type', isset($duplicate) ? $duplicate->type : (isset($customer) ? $customer->type : null)) == 2)>{{ __('Oversea') }}</option>
                </x-app.input.select>
                <x-app.message.error id="local_oversea_err" />
            </div>
            <div class="flex flex-col hidden for-all">
                <x-app.input.label id="tin_number" class="mb-1">{{ __('TIN') }} <span
                        class="text-sm text-red-500 hidden for_einvoice-required">*</span></x-app.input.label>
                <x-app.input.input name="tin_number" id="tin_number" :hasError="$errors->has('tin_number')"
                    value="{{ old('tin_number', isset($duplicate) ? $duplicate->tin_number : (isset($customer) ? $customer->tin_number : null)) }}" />
                <x-app.message.error id="tin_number_err" />
            </div>
            <div class="flex flex-col hidden non-individual-fields-container">
                <x-app.input.label id="company_registration_number" class="mb-1">{{ __('Business Reg No.') }} <span
                        class="text-sm text-red-500 hidden for_einvoice-required">*</span></x-app.input.label>
                <x-app.input.input name="company_registration_number" id="company_registration_number" :hasError="$errors->has('company_registration_number')"
                    value="{{ old('company_registration_number', isset($duplicate) ? $duplicate->company_registration_number : (isset($customer) ? $customer->company_registration_number : null)) }}" />
                <x-app.message.error id="company_registration_number_err" />
            </div>
            <div class="flex flex-col hidden non-individual-fields-container">
                <x-app.input.label id="msic_code" class="mb-1">{{ __('MSIC Code') }} <span
                        class="text-sm text-red-500 hidden for_einvoice-required">*</span></x-app.input.label>
                <x-app.input.select2 name="msic_code" id="msic_code" :hasError="$errors->has('msic_code')"
                    placeholder="{{ __('Select a MSIC Code') }}">
                    <option value="">{{ __('Select a Msic Code') }}</option>
                    @foreach ($msics as $msic)
                        <option value="{{ $msic->id }}" @selected(old('msic_code', isset($duplicate) && $duplicate->msicCode != null ? $duplicate->msicCode->id : (isset($customer) && $customer->msicCode != null ? $customer->msicCode->id : null)) == $msic->id)>{{ $msic->code }} -
                            {{ $msic->description }}</option>
                    @endforeach
                </x-app.input.select2>
                <x-app.message.error id="msic_code_err" />
            </div>
            <div class="flex flex-col hidden non-individual-fields-container">
                <x-app.input.label id="business_activity_desc"
                    class="mb-1">{{ __('Business Activity Desc.') }}</x-app.input.label>
                <x-app.input.input name="business_activity_desc" id="business_activity_desc" :hasError="$errors->has('business_activity_desc')"
                    value="{{ isset($duplicate) ? $duplicate->business_act_desc : ($customer->business_act_desc ?? null) }}" :disabled="true" />
                <x-app.message.error id="business_activity_desc_err" />
            </div>
            <div class="flex flex-col hidden non-individual-fields-container">
                <x-app.input.label id="sst_number" class="mb-1">{{ __('SST Reg No.') }}</x-app.input.label>
                <x-app.input.input name="sst_number" id="sst_number" :hasError="$errors->has('sst_number')"
                    value="{{ old('sst_number', isset($duplicate) ? $duplicate->sst_number : (isset($customer) ? $customer->sst_number : null)) }}" />
                <x-app.message.error id="sst_number_err" />
            </div>
            <div class="flex flex-col hidden non-individual-fields-container">
                <x-app.input.label id="tourism_tax_reg_no"
                    class="mb-1">{{ __('Tourism Tax Reg No.') }}</x-app.input.label>
                <x-app.input.input name="tourism_tax_reg_no" id="tourism_tax_reg_no" :hasError="$errors->has('tourism_tax_reg_no')"
                    value="{{ old('tourism_tax_reg_no', isset($duplicate) ? $duplicate->tourism_tax_reg_no : (isset($customer) ? $customer->tourism_tax_reg_no : null)) }}" />
                <x-app.message.error id="tourism_tax_reg_no_err" />
            </div>
            <div class="flex flex-col hidden non-individual-fields-container">
                <x-app.input.label id="prev_gst_reg_no"
                    class="mb-1">{{ __('Prev. GST Reg No.') }}</x-app.input.label>
                <x-app.input.input name="prev_gst_reg_no" id="prev_gst_reg_no" :hasError="$errors->has('prev_gst_reg_no')"
                    value="{{ old('prev_gst_reg_no', isset($duplicate) ? $duplicate->prev_gst_reg_no : (isset($customer) ? $customer->prev_gst_reg_no : null)) }}" />
                <x-app.message.error id="prev_gst_reg_no_err" />
            </div>
            <div class="flex flex-col hidden individual-fields-container">
                <x-app.input.label id="identity_type" class="mb-1">{{ __('Identity Type') }}
                    <span class="text-sm text-red-500 hidden for_einvoice-required">*</span>
                </x-app.input.label>
                <x-app.input.input name="identity_type" id="identity_type" :hasError="$errors->has('identity_type')"
                    value="{{ old('identity_type', isset($duplicate) ? $duplicate->identity_type : (isset($customer) ? $customer->identity_type : null)) }}" />
                <x-app.message.error id="identity_type_err" />
            </div>
            <div class="flex flex-col hidden individual-fields-container">
                <x-app.input.label id="identity_no" class="mb-1">{{ __('Identity No.') }} <span
                        class="text-sm text-red-500 hidden for_einvoice-required">*</span></x-app.input.label>
                <x-app.input.input name="identity_no" id="identity_no" :hasError="$errors->has('identity_no')"
                    value="{{ old('identity_no', isset($duplicate) ? $duplicate->identity_no : (isset($customer) ? $customer->identity_no : null)) }}" />
                <x-app.message.error id="identity_no_err" />
            </div>
            <div class="flex flex-col hidden for-all">
                <x-app.input.label id="registered_name" class="mb-1">{{ __('Registered Name') }} <span
                        class="text-sm text-red-500 hidden for_einvoice-required">*</span></x-app.input.label>
                <x-app.input.input name="registered_name" id="registered_name" :hasError="$errors->has('registered_name')"
                    value="{{ old('registered_name', isset($duplicate) ? $duplicate->registered_name : (isset($customer) ? $customer->registered_name : null)) }}" />
                <x-app.message.error id="registered_name_err" />
            </div>
            <div class="flex flex-col hidden for-all">
                <x-app.input.label id="trade_name" class="mb-1">{{ __('Trade Name') }} </x-app.input.label>
                <x-app.input.input name="trade_name" id="trade_name" :hasError="$errors->has('trade_name')"
                    value="{{ old('trade_name', isset($duplicate) ? $duplicate->trade_name : (isset($customer) ? $customer->trade_name : null)) }}" />
                <x-app.message.error id="trade_name_err" />
            </div>
            <div class="flex flex-col hidden for-all">
                <x-app.input.label id="phone_number" class="mb-1">{{ __('Phone Number') }} <span
                        class="text-sm text-red-500 hidden for_einvoice-required">*</span></x-app.input.label>
                <x-app.input.input name="phone_number" id="phone_number" :hasError="$errors->has('phone_number')"
                    value="{{ old('phone_number', isset($duplicate) ? $duplicate->phone : (isset($customer) ? $customer->phone : null)) }}" />
                <span class="text-sm text-slate-500">{{ __('"+6" is not required') }}</span>
                <x-app.message.error id="phone_number_err" />
            </div>
            <div class="flex flex-col hidden for-all">
                <x-app.input.label id="email" class="mb-1">{{ __('Email Address') }} <span
                        class="text-sm text-red-500 hidden for_einvoice-required">*</span></x-app.input.label>
                <x-app.input.input name="email" id="email" type="email" :hasError="$errors->has('email')"
                    value="{{ old('email', isset($duplicate) ? $duplicate->email : (isset($customer) ? $customer->email : null)) }}" />
                <x-app.message.error id="email_err" />
            </div>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-6 md:gap-8 w-full mb-4 border-t border-slate-200 pt-8">
            <div class="flex flex-col">
                <x-app.input.label id="address" class="mb-1">{{ __('Address') }} </x-app.input.label>
                <x-app.input.input name="address" id="address" :hasError="$errors->has('address')"
                    value="{{ old('city', isset($duplicate) ? $duplicate->address : (isset($customer) ? $customer->address : null)) }}" />
                <x-app.message.error id="address_err" />
            </div>
            <div class="flex flex-col">
                <x-app.input.label id="city" class="mb-1">{{ __('City') }}</x-app.input.label>
                <x-app.input.input name="city" id="city" :hasError="$errors->has('city')"
                    value="{{ old('city', isset($duplicate) ? $duplicate->city : (isset($customer) ? $customer->city : null)) }}" />
                <x-app.message.error id="city_err" />
            </div>
            <div class="flex flex-col">
                <x-app.input.label id="zip_code" class="mb-1">{{ __('Zip Code') }}</x-app.input.label>
                <x-app.input.input name="zip_code" id="zip_code" :hasError="$errors->has('zip_code')"
                    value="{{ old('zip_code', isset($duplicate) ? $duplicate->zipcode : (isset($customer) ? $customer->zipcode : null)) }}" class="int-input" />
                <x-app.message.error id="zip_code_err" />
            </div>
        </div>
    </div>
    <!-- 2nd Panel -->
    <div class="bg-white p-4 border rounded-md">
        <div class="grid grid-cols-2 md:grid-cols-3 gap-6 md:gap-8 w-full mb-4">
            {{-- <div class="flex flex-col">
                <x-app.input.label class="mb-1">Picture</x-app.input.label>
                <x-app.input.file id="picture[]" :hasError="$errors->has('picture')"/>
                <x-app.message.error id="picture_err"/>
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
            </div> --}}
            @if (isset($customer))
                <div class="flex flex-col">
                    <x-app.input.label id="code" class="mb-1">{{ __('Code') }}</x-app.input.label>
                    <x-app.input.input name="code" id="code" :hasError="$errors->has('code')"
                        value="{{ old('code', isset($customer) ? $customer->sku : null) }}" disabled="true" />
                    <x-app.message.error id="code_err" />
                </div>
            @endif
            <div class="flex flex-col">
                <x-app.input.label id="company_group" class="mb-1">{{ __('Company Group') }} <span
                        class="text-sm text-red-500">*</span></x-app.input.label>
                <x-app.input.select2 name="company_group" id="company_group" :hasError="$errors->has('company_group')"
                    placeholder="{{ __('Select a company group') }}">
                    <option value="">{{ __('Select a company group') }}</option>
                    @foreach ($company_group as $key => $value)
                        <option value="{{ $key }}" @selected(old('company_group', isset($duplicate) ? $duplicate->company_group : (isset($customer) ? $customer->company_group : null)) == $key)>{{ $value }}</option>
                    @endforeach
                </x-app.input.select2>
                <x-app.message.error id="company_group_err" />
            </div>
            {{-- <div class="flex flex-col">
                <x-app.input.label id="prefix" class="mb-1">{{ __('Prefix') }}</x-app.input.label>
                <x-app.input.select2 name="prefix" id="prefix" :hasError="$errors->has('prefix')"
                    placeholder="{{ __('Select a prefix') }}">
                    <option value="">{{ __('Select a prefix') }}</option>
                    @foreach ($prefix as $key => $value)
                        <option value="{{ $key }}" @selected(old('prefix', isset($duplicate) ? $duplicate->prefix : (isset($customer) ? $customer->prefix : null)) == $key)>{{ $value }}</option>
                    @endforeach
                </x-app.input.select2>
                <x-app.message.error id="prefix_err" />
            </div> --}}
            <div class="flex flex-col">
                <x-app.input.label id="customer_name" class="mb-1">{{ __('Customer Name') }} <span
                        class="text-sm text-red-500">*</span></x-app.input.label>
                <x-app.input.input name="customer_name" id="customer_name" :hasError="$errors->has('customer_name')"
                    value="{{ old('customer_name', isset($duplicate) ? $duplicate->name : (isset($customer) ? $customer->name : null)) }}" />
                <x-app.message.error id="customer_name_err" />
            </div>
            <div class="flex flex-col">
                <x-app.input.label id="company_name" class="mb-1">{{ __('Company Name') }} </x-app.input.label>
                <x-app.input.input name="company_name" id="company_name" :hasError="$errors->has('company_name')"
                    value="{{ old('company_name', isset($duplicate) ? $duplicate->company_name : (isset($customer) ? $customer->company_name : null)) }}" />
                <x-app.message.error id="company_name_err" />
            </div>
            <div class="flex flex-col">
                <div class="flex justify-between items-center mb-1">
                    <x-app.input.label id="mobile_number">{{ __('Mobile Number') }}</x-app.input.label>
                    <button type="button" id="add-mobile-number-btn" class="text-xs bg-blue-500 text-white px-2 py-1 rounded hover:bg-blue-600">+ {{ __('Add') }}</button>
                </div>
                <div id="mobile-numbers-container">
                    @if(isset($customer) && $customer->mobile_number && is_array($customer->mobile_number) && count($customer->mobile_number) > 0)
                        @foreach($customer->mobile_number as $index => $mobile)
                            <div class="flex items-center gap-2 mb-2 mobile-number-row">
                                <x-app.input.input name="mobile_number[]" class="flex-1" value="{{ $mobile }}" />
                                @if($index > 0)
                                    <button type="button" class="remove-mobile-number-btn bg-rose-500 text-white p-2 rounded-full hover:bg-rose-600">
                                        <svg class="h-3 w-3 fill-white" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1"
                                            viewBox="0 0 24 24" width="512" height="512">
                                            <path
                                                d="M13.93,12L21.666,2.443c.521-.644,.422-1.588-.223-2.109-.645-.522-1.588-.421-2.109,.223l-7.334,9.06L4.666,.557c-1.241-1.519-3.56,.357-2.332,1.887l7.736,9.557L2.334,21.557c-.521,.644-.422,1.588,.223,2.109,.64,.519,1.586,.424,2.109-.223l7.334-9.06,7.334,9.06c.524,.647,1.47,.742,2.109,.223,.645-.521,.744-1.466,.223-2.109l-7.736-9.557Z" />
                                        </svg>
                                    </button>
                                @endif
                            </div>
                        @endforeach
                    @elseif(isset($duplicate) && $duplicate->mobile_number)
                        @php
                            $duplicateMobiles = is_array($duplicate->mobile_number) ? $duplicate->mobile_number : [$duplicate->mobile_number];
                        @endphp
                        @foreach($duplicateMobiles as $index => $mobile)
                            <div class="flex items-center gap-2 mb-2 mobile-number-row">
                                <x-app.input.input name="mobile_number[]" class="flex-1" value="{{ $mobile }}" />
                                @if($index > 0)
                                    <button type="button" class="remove-mobile-number-btn bg-rose-500 text-white p-2 rounded-full hover:bg-rose-600">
                                        <svg class="h-3 w-3 fill-white" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1"
                                            viewBox="0 0 24 24" width="512" height="512">
                                            <path
                                                d="M13.93,12L21.666,2.443c.521-.644,.422-1.588-.223-2.109-.645-.522-1.588-.421-2.109,.223l-7.334,9.06L4.666,.557c-1.241-1.519-3.56,.357-2.332,1.887l7.736,9.557L2.334,21.557c-.521,.644-.422,1.588,.223,2.109,.64,.519,1.586,.424,2.109-.223l7.334-9.06,7.334,9.06c.524,.647,1.47,.742,2.109,.223,.645-.521,.744-1.466,.223-2.109l-7.736-9.557Z" />
                                        </svg>
                                    </button>
                                @endif
                            </div>
                        @endforeach
                    @else
                        <div class="flex items-center gap-2 mb-2 mobile-number-row">
                            <x-app.input.input name="mobile_number[]" class="flex-1" value="" />
                        </div>
                    @endif
                </div>
                <x-app.message.error id="mobile_number_err" />
            </div>
            <div class="flex flex-col">
                <x-app.input.label id="website" class="mb-1">{{ __('Website') }}</x-app.input.label>
                <x-app.input.input name="website" id="website" :hasError="$errors->has('website')"
                    value="{{ old('website', isset($duplicate) ? $duplicate->website : (isset($customer) ? $customer->website : null)) }}" />
                <x-app.message.error id="website_err" />
            </div>
            @if (!isCreateLink())
                <div class="flex flex-col">
                    <x-app.input.label id="currency" class="mb-1">{{ __('Currency') }}</x-app.input.label>
                    <x-app.input.select name="currency" id="currency" :hasError="$errors->has('currency')">
                        <option value="">{{ __('Select a currency') }}</option>
                        @foreach ($currencies as $curr)
                            <option value="{{ $curr->id }}" @selected(old('currency', isset($duplicate) ? $duplicate->currency_id : (isset($customer) ? $customer->currency_id : null)) == $curr->id)>{{ $curr->name }}
                            </option>
                        @endforeach
                    </x-app.input.select>
                    <x-app.message.error id="currency_err" />
                </div>
            @endif
            @if (!isCreateLink())
                <div class="flex flex-col">
                    <x-app.input.label id="area" class="mb-1">{{ __('Area') }}</x-app.input.label>
                    <x-app.input.select name="area" id="area" :hasError="$errors->has('area')">
                        <option value="">{{ __('Select a area') }}</option>
                        @foreach ($areas as $area)
                            <option value="{{ $area->id }}" @selected(old('area', isset($duplicate) ? $duplicate->area : (isset($customer) ? $customer->area_id : null)) == $area->id)>{{ $area->name }}
                            </option>
                        @endforeach
                    </x-app.input.select>
                    <x-app.message.error id="area_err" />
                </div>
            @endif
            @if (!isCreateLink())
                <div class="flex flex-col">
                    <x-app.input.label id="debtor_type" class="mb-1">{{ __('Debtor Type') }}</x-app.input.label>
                    <x-app.input.select name="debtor_type" id="debtor_type" :hasError="$errors->has('debtor_type')">
                        <option value="">{{ __('Select a debtor type') }}</option>
                        @foreach ($debtor_types as $debtor_type)
                            <option value="{{ $debtor_type->id }}" @selected(old('debtor_type', isset($duplicate) ? $duplicate->debtor_type_id : (isset($customer) ? $customer->debtor_type_id : null)) == $debtor_type->id)>
                                {{ $debtor_type->name }}</option>
                        @endforeach
                    </x-app.input.select>
                    <x-app.message.error id="debtor_type_err" />
                </div>
            @endif
            @if (!isCreateLink())
                <div class="flex flex-col">
                    <x-app.input.label id="sale_agent" class="mb-1">{{ __('Sale Agent') }}</x-app.input.label>
                    <x-app.input.select2 name="sale_agent[]" id="sale_agent" :hasError="$errors->has('sale_agent')"
                        placeholder="{{ __('Select a sale agent') }}" multiple>
                        <option value="">{{ __('Select a sale agent') }}</option>
                        @foreach ($sales_agents as $sa)
                            <option value="{{ $sa->id }}" @selected(isset($sales_agent_ids) ? in_array($sa->id, $sales_agent_ids) : null)>{{ $sa->name }}
                            </option>
                        @endforeach
                    </x-app.input.select2>
                    <x-app.message.error id="sale_agent_err" />
                </div>
            @endif
            @if (!isCreateLink())
                <div class="flex flex-col">
                    <x-app.input.label id="platform" class="mb-1">{{ __('Platform') }}</x-app.input.label>
                    <x-app.input.select name="platform" id="platform" :hasError="$errors->has('platform')">
                        <option value="">{{ __('Select a platform') }}</option>
                        @foreach ($platforms as $platform)
                            <option value="{{ $platform->id }}" @selected(old('platform',isset($duplicate) ? $duplicate->platform_id : (isset($customer) ? $customer->platform_id : null)) == $platform->id)>{{ $platform->name }}
                            </option>
                        @endforeach
                    </x-app.input.select>
                    <x-app.message.error id="platform_err" />
                </div>
            @endif
            @if (!isCreateLink())
                <div class="flex flex-col col-span">
                    <x-app.input.label id="credit_term" class="mb-1">{{ __('Credit Terms') }}</x-app.input.label>
                    <x-app.input.select name="credit_term[]" multiple>
                        @foreach ($credit_terms as $ct)
                            <option value="{{ $ct->id }}" @selected(old('credit_term', isset($duplicate) ? in_array($ct->id, $duplicate->creditTerms()->pluck('credit_term_id')->toArray()) : (isset($customer) ? in_array($ct->id, $customer->creditTerms()->pluck('credit_term_id')->toArray()) : null)))>{{ $ct->name }}
                            </option>
                        @endforeach
                    </x-app.input.select>
                    <x-app.message.error id="credit_term_err" />
                    @if (isset($customer) && $customer->status != null)
                        <div class="col-span-4 mt-1.5">
                            @if ($customer->revised == 1)
                                <span
                                    class="border rounded border-blue-500 text-blue-500 text-xs font-medium px-1 py-0.5">{{ __('Revised') }}</span>
                            @endif
                            @if ($customer->status == 3)
                                <span
                                    class="border rounded border-slate-500 text-slate-500 text-xs font-medium px-1 py-0.5">{{ __('Pending Approval') }}</span>
                            @elseif ($customer->status == 5)
                                <span
                                    class="border rounded border-green-600 text-green-600 text-xs font-medium px-1 py-0.5">{{ __('Approved') }}</span>
                            @elseif ($customer->status == 4)
                                <span
                                    class="border rounded border-red-600 text-red-600 text-xs font-medium px-1 py-0.5">{{ __('Rejected') }}</span>
                            @endif
                        </div>
                    @endif
                </div>
            @endif
            <div class="flex flex-col">
                <x-app.input.label id="status" class="mb-1">{{ __('Status') }} <span
                        class="text-sm text-red-500">*</span></x-app.input.label>
                <x-app.input.select name="status" id="status" :hasError="$errors->has('status')">
                    @if (isCreateLink())
                        <option value="2" selected>{{ __('Pending Fill Up Info') }}</option>
                    @else
                        <option value="">{{ __('Select a Active/Inactive') }}</option>
                        @if (isset($customer) && $customer->status == 4)
                            <option value="4" @selected(old('status', isset($duplicate) ? $duplicate->status : (isset($customer) ? $customer->status : null)) == 4)>{{ __('Rejected') }}</option>
                        @elseif (isset($customer) && $customer->status == 5)
                            <option value="5" @selected(old('status', isset($duplicate) ? $duplicate->status : (isset($customer) ? $customer->status : null)) == 5)>{{ __('Approved') }}</option>
                        @else
                            <option value="1" @selected(old('status', isset($duplicate) ? $duplicate->status : (isset($customer) ? $customer->status : null)) == 1)>{{ __('Active') }}</option>
                            <option value="0" @selected(old('status', isset($duplicate) ? $duplicate->status : (isset($customer) ? $customer->status : null)) === 0)>{{ __('Inactive') }}</option>
                        @endif
                    @endif
                </x-app.input.select>
                <x-app.message.error id="status_err" />
            </div>
            <div class="flex flex-col col-span-2">
                <x-app.input.label id="remark" class="mb-1">{{ __('Remark') }}</x-app.input.label>
                <x-app.input.input name="remark" id="remark" :hasError="$errors->has('remark')"
                    value="{{ old('remark', isset($duplicate) ? $duplicate->remark : (isset($customer) ? $customer->remark : null)) }}" />
                <x-app.message.error id="remark_err" />
            </div>
        </div>
        <div class="mt-8 hidden">
            <x-app.button.submit id="submit-btn">{{ __('Save and Update') }}</x-app.button.submit>
        </div>
    </div>
</form>

<x-app.modal.tin-info-modal />


@push('scripts')
    <script>
        CUSTOMER = @json($customer ?? null);
        DUPLICATE = @json($duplicate ?? null);
        INFO_FORM_CAN_SUBMIT = true
        DEFAULT_BRANCH = @json($default_branch ?? null);
        IS_CREATE_LINK = @json($is_create_link ?? null);
        MSIC_CODES = @json($msics ?? null);
        NEGLECT_TIN_VALIDATION = false
        INIT_DONE = false

        $(document).ready(function() {
            if ((CUSTOMER != null && CUSTOMER.category != null) || (DUPLICATE != null && DUPLICATE.category != null)) {
                $('select[name="category"]').trigger('change')
            }
            if (CUSTOMER != null || DUPLICATE != null)[
                $('select[name="msic_code"]').trigger('change')
            ]
            $('input[name="for_einvoice"]').trigger('change')
            INIT_DONE = true
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
            }
        })
        $('select[name="msic_code"]').on('change', function() {
            let val = $(this).val()

            for (i = 0; i < MSIC_CODES.length; i++) {
                if (MSIC_CODES[i].id == val) {
                    $('input[name="business_activity_desc"]').val(MSIC_CODES[i].description)
                    break
                }
            }
        })
        $('select[name="category"]').on('change', function() {
            let val = $(this).val()

            if (val == 1) {
                $('#local_oversea-container').removeClass('hidden')
                // $('#tin_number_required_star').removeClass('hidden')
                $('.individual-fields-container').addClass('hidden')
                $('.non-individual-fields-container').removeClass('hidden')
            } else if (val == 2) {
                $('#local_oversea-container').addClass('hidden')
                // $('#tin_number_required_star').addClass('hidden')
                $('.individual-fields-container').removeClass('hidden')
                $('.non-individual-fields-container').addClass('hidden')
            } else if (val == 3) {
                $('#local_oversea-container').addClass('hidden')
                // $('#tin_number_required_star').addClass('hidden')
                $('.individual-fields-container').addClass('hidden')
                $('.non-individual-fields-container').removeClass('hidden')
            }

            $('.for-all').removeClass('hidden')
        })
        $('#tin-info-modal #yes-btn').on('click', function() {
            NEGLECT_TIN_VALIDATION = true

            $('#info-form').submit()
            $('#tin-info-modal').removeClass('show-modal')
            NEGLECT_TIN_VALIDATION = false
            GROUP_SUBMITTING = true
        })
        $('input[name="for_einvoice"]').on('change', function() {
            let val = $(this).is(':checked')

            if (val == true) {
                $('.for_einvoice-required').removeClass('hidden')
            } else {
                $('.for_einvoice-required').addClass('hidden')
            }
        })

        $('#info-form').on('submit', function(e) {
            e.preventDefault()

            if (!INFO_FORM_CAN_SUBMIT) return

            INFO_FORM_CAN_SUBMIT = false

            $('#info-form #submit-btn').text('Updating')
            $('#info-form #submit-btn').removeClass('bg-yellow-400 shadow')
            $('.err_msg').addClass('hidden') // Remove error messages
            // Submit
            let url = '{{ route('customer.upsert_info') }}'

            var formData = new FormData(this);
            formData.append('customer_id', CUSTOMER != null ? CUSTOMER.id : null)
            formData.append('neglect_tin_validation', NEGLECT_TIN_VALIDATION)
            formData.append('for_einvoice', $('input[name="for_einvoice"]').is(':checked'))

            // let picture = $('input[name="picture[]"]').prop('files')
            // if (picture.length > 0) formData.append('picture[]', picture)

            if (DEFAULT_BRANCH != null) formData.append('branch', DEFAULT_BRANCH)

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: url,
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(res) {
                    CUSTOMER = res.customer

                    if (IS_CREATE_LINK) {
                        $('#create-customer-link-created-modal').addClass('show-modal')
                    }

                    if (GROUP_SUBMITTING) {
                        $('#location-form').submit()
                    } else {
                        setTimeout(() => {
                            $('#info-form #submit-btn').text('Updated')
                            $('#info-form #submit-btn').addClass('bg-green-400 shadow')

                            setTimeout(() => {
                                $('#info-form #submit-btn').text('Save and Update')
                                $('#info-form #submit-btn').removeClass('bg-green-400')
                                $('#info-form #submit-btn').addClass(
                                    'bg-yellow-400 shadow')

                                INFO_FORM_CAN_SUBMIT = true
                            }, 2000);
                        }, 300);
                    }
                },
                error: function(err) {
                    if (err.status == StatusCodes.UNPROCESSABLE_ENTITY) {
                        let errors = err.responseJSON.errors

                        for (const key in errors) {
                            if (key == 'tin_number_hasil') {
                                $('#tin-info-modal #msg').text(
                                    `TIN: '${ $('input[name="tin_number"]').val() }' did not pass LHDN validation. Are you sure you want to save it?`
                                )
                                $('#tin-info-modal').addClass('show-modal')
                            } else {
                                if (key.includes('picture')) {
                                    $(`#info-form #picture_err`).find('p').text(errors[key])
                                    $(`#info-form #picture_err`).removeClass('hidden')
                                } else {
                                    $(`#info-form #${key}_err`).find('p').text(errors[key])
                                    $(`#info-form #${key}_err`).removeClass('hidden')
                                }
                            }
                        }
                    }


                    setTimeout(() => {
                        $('#info-form #submit-btn, #group-submit-btn').text('Save and Update')
                        $('#info-form #submit-btn, #group-submit-btn').addClass(
                            'bg-yellow-400 shadow')

                        INFO_FORM_CAN_SUBMIT = true
                        GROUP_SUBMITTING = false
                    }, 300);
                },
            });
        })

        // Handle adding new mobile number field
        $('#add-mobile-number-btn').on('click', function() {
            const newRow = `
                <div class="flex items-center gap-2 mb-2 mobile-number-row">
                    <input type="text" name="mobile_number[]" class="flex-1 border border-gray-300 rounded px-3 py-2" value="" />
                    <button type="button" class="remove-mobile-number-btn bg-rose-500 text-white p-2 rounded-full hover:bg-rose-600">
                        <svg class="h-3 w-3 fill-white" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1"
                            viewBox="0 0 24 24" width="512" height="512">
                            <path
                                d="M13.93,12L21.666,2.443c.521-.644,.422-1.588-.223-2.109-.645-.522-1.588-.421-2.109,.223l-7.334,9.06L4.666,.557c-1.241-1.519-3.56,.357-2.332,1.887l7.736,9.557L2.334,21.557c-.521,.644-.422,1.588,.223,2.109,.64,.519,1.586,.424,2.109-.223l7.334-9.06,7.334,9.06c.524,.647,1.47,.742,2.109,.223,.645-.521,.744-1.466,.223-2.109l-7.736-9.557Z" />
                        </svg>
                    </button>
                </div>
            `;
            $('#mobile-numbers-container').append(newRow);
        })

        // Handle removing mobile number field
        $('body').on('click', '.remove-mobile-number-btn', function() {
            $(this).closest('.mobile-number-row').remove();
        })
    </script>
@endpush
