<div class="bg-white p-4 border rounded-md">
    <div class="flex items-center mb-6 border-l-8 border-yellow-400 px-3 py-1 bg-yellow-50 w-fit">
        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512" height="512"><path d="M12,0A12,12,0,1,0,24,12,12.013,12.013,0,0,0,12,0Zm0,22A10,10,0,1,1,22,12,10.011,10.011,0,0,1,12,22Z"/><path d="M12,10H11a1,1,0,0,0,0,2h1v6a1,1,0,0,0,2,0V12A2,2,0,0,0,12,10Z"/><circle cx="12" cy="6.5" r="1.5"/></svg>
        <span class="text-lg ml-3 font-bold">{{ __('Information') }}</span>
    </div>
    <form action="" method="POST" enctype="multipart/form-data" id="info-form">
        @csrf
        <div id="content-container">
            <div class="grid grid-cols-3 gap-8 w-full mb-4">
                <!-- <div class="flex flex-col">
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
                </div> -->
                @if (isset($customer))
                    <div class="flex flex-col">
                        <x-app.input.label id="code" class="mb-1">{{ __('Code') }}</x-app.input.label>
                        <x-app.input.input name="code" id="code" :hasError="$errors->has('code')" value="{{ old('code', isset($customer) ? $customer->sku : null) }}" disabled="true"/>
                        <x-app.message.error id="code_err"/>
                    </div>
                @endif
                <div class="flex flex-col">
                    <x-app.input.label id="prefix" class="mb-1">{{ __('Prefix') }}</x-app.input.label>
                    <x-app.input.select2 name="prefix" id="prefix" :hasError="$errors->has('prefix')" placeholder="{{ __('Select a prefix') }}">
                        <option value="">{{ __('Select a prefix') }}</option>
                        @foreach ($prefix as $key => $value)
                            <option value="{{ $key }}" @selected(old('prefix', isset($customer) ? $customer->prefix : null) == $key)>{{ $value }}</option>
                        @endforeach
                    </x-app.input.select2>
                    <x-app.message.error id="prefix_err"/>
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="customer_name" class="mb-1">{{ __('Customer Name') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="customer_name" id="customer_name" :hasError="$errors->has('customer_name')" value="{{ old('customer_name', isset($customer) ? $customer->name : null) }}" />
                    <x-app.message.error id="customer_name_err"/>
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="company_name" class="mb-1">{{ __('Company Name') }} </x-app.input.label>
                    <x-app.input.input name="company_name" id="company_name" :hasError="$errors->has('company_name')" value="{{ old('company_name', isset($customer) ? $customer->company_name : null) }}" />
                    <x-app.message.error id="company_name_err"/>
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="company_registration_number" class="mb-1">{{ __('Company Registration Number') }}</x-app.input.label>
                    <x-app.input.input name="company_registration_number" id="company_registration_number" :hasError="$errors->has('company_registration_number')" value="{{ old('company_registration_number', isset($customer) ? $customer->company_registration_number : null) }}"/>
                    <x-app.message.error id="company_registration_number_err"/>
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="phone_number" class="mb-1">{{ __('Phone Number') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="phone_number" id="phone_number" :hasError="$errors->has('phone_number')" value="{{ old('phone_number', isset($customer) ? $customer->phone : null) }}"/>
                    <x-app.message.error id="phone_number_err"/>
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="mobile_number" class="mb-1">{{ __('Mobile Number') }}</x-app.input.label>
                    <x-app.input.input name="mobile_number" id="mobile_number" :hasError="$errors->has('mobile_number')" value="{{ old('mobile_number', isset($customer) ? $customer->mobile_number : null) }}"/>
                    <x-app.message.error id="mobile_number_err"/>
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="email" class="mb-1">{{ __('Email') }}</x-app.input.label>
                    <x-app.input.input name="email" id="email" type="email" :hasError="$errors->has('email')" value="{{ old('email', isset($customer) ? $customer->email : null) }}"/>
                    <x-app.message.error id="email_err"/>
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="website" class="mb-1">{{ __('Website') }}</x-app.input.label>
                    <x-app.input.input name="website" id="website" :hasError="$errors->has('website')" value="{{ old('website', isset($customer) ? $customer->website : null) }}"/>
                    <x-app.message.error id="website_err"/>
                </div>
                @if (!isCreateLink())
                    <div class="flex flex-col">
                        <x-app.input.label id="currency" class="mb-1">{{ __('Currency') }}</x-app.input.label>
                        <x-app.input.select name="currency" id="currency" :hasError="$errors->has('currency')">
                            <option value="">{{ __('Select a currency') }}</option>
                            @foreach ($currencies as $curr)
                                <option value="{{ $curr->id }}" @selected(old('currency', isset($customer) ? $customer->currency_id : null) == $curr->id)>{{ $curr->name }}</option>
                            @endforeach
                        </x-app.input.select>
                        <x-app.message.error id="currency_err"/>
                    </div>
                @endif
                @if (!isCreateLink())
                    <div class="flex flex-col">
                        <x-app.input.label id="area" class="mb-1">{{ __('Area') }}</x-app.input.label>
                        <x-app.input.select name="area" id="area" :hasError="$errors->has('area')">
                            <option value="">{{ __('Select a area') }}</option>
                            @foreach ($areas as $area)
                                <option value="{{ $area->id }}" @selected(old('area', isset($customer) ? $customer->area_id : null) == $area->id)>{{ $area->name }}</option>
                            @endforeach
                        </x-app.input.select>
                        <x-app.message.error id="area_err"/>
                    </div>
                @endif
                @if (!isCreateLink())
                    <div class="flex flex-col">
                        <x-app.input.label id="debtor_type" class="mb-1">{{ __('Debtor Type') }}</x-app.input.label>
                        <x-app.input.select name="debtor_type" id="debtor_type" :hasError="$errors->has('debtor_type')">
                            <option value="">{{ __('Select a debtor type') }}</option>
                            @foreach ($debtor_types as $debtor_type)
                                <option value="{{ $debtor_type->id }}" @selected(old('debtor_type', isset($customer) ? $customer->debtor_type_id : null) == $debtor_type->id)>{{ $debtor_type->name }}</option>
                            @endforeach
                        </x-app.input.select>
                        <x-app.message.error id="debtor_type_err"/>
                    </div>
                @endif
                <div class="flex flex-col">
                    <x-app.input.label id="tin_number" class="mb-1">{{ __('TIN Number') }}</x-app.input.label>
                    <x-app.input.input name="tin_number" id="tin_number" :hasError="$errors->has('tin_number')" value="{{ old('tin_number', isset($customer) ? $customer->tin_number : null) }}" />
                    <x-app.message.error id="tin_number_err"/>
                </div>
                @if (!isCreateLink())
                    <div class="flex flex-col">
                        <x-app.input.label id="sale_agent" class="mb-1">{{ __('Sale Agent') }}</x-app.input.label>
                        <x-app.input.select2 name="sale_agent" id="sale_agent" :hasError="$errors->has('sale_agent')" placeholder="{{ __('Select a sale agent') }}">
                            <option value="">{{ __('Select a sale agent') }}</option>
                            @foreach ($sales as $sa)
                                <option value="{{ $sa->id }}" @selected(old('sale', isset($customer) ? $customer->sale_agent : null) == $sa->id)>{{ $sa->name }}</option>
                            @endforeach
                        </x-app.input.select2>
                        <x-app.message.error id="sale_agent_err"/>
                    </div>
                @endif
                @if (!isCreateLink())
                    <div class="flex flex-col">
                        <x-app.input.label id="platform" class="mb-1">{{ __('Platform') }}</x-app.input.label>
                        <x-app.input.select name="platform" id="platform" :hasError="$errors->has('platform')">
                            <option value="">{{ __('Select a platform') }}</option>
                            @foreach ($platforms as $platform)
                                <option value="{{ $platform->id }}" @selected(old('platform', isset($customer) ? $customer->platform_id : null) == $platform->id)>{{ $platform->name }}</option>
                            @endforeach
                        </x-app.input.select>
                        <x-app.message.error id="platform_err"/>
                    </div>
                @endif
                @if (!isCreateLink())
                    <div class="flex flex-col col-span">
                        <x-app.input.label id="credit_term" class="mb-1">{{ __('Credit Terms') }}</x-app.input.label>
                        <x-app.input.select name="credit_term[]" multiple>
                            @foreach ($credit_terms as $ct)
                                <option value="{{ $ct->id }}" @selected(old('credit_term', isset($customer) ? in_array($ct->id, $customer->creditTerms()->pluck('credit_term_id')->toArray()) : null))>{{ $ct->name }}</option>
                            @endforeach
                        </x-app.input.select>
                        <x-app.message.error id="credit_term_err"/>
                    </div>
                @endif
                <div class="flex flex-col">
                    <x-app.input.label id="status" class="mb-1">{{ __('Status') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select name="status" id="status" :hasError="$errors->has('status')">
                        @if (isCreateLink())
                            <option value="2" selected>{{ __('Pending Fill Up Info') }}</option>
                        @else
                            <option value="">{{ __('Select a Active/Inactive') }}</option>
                            <option value="1" @selected(old('status', isset($customer) ? $customer->is_active : null) == 1)>{{ __('Active') }}</option>
                            <option value="0" @selected(old('status', isset($customer) ? $customer->is_active : null) === 0)>{{ __('Inactive') }}</option>
                        @endif
                    </x-app.input.select>
                    <x-app.message.error id="status_err"/>
                </div>
                <div class="flex flex-col col-span-2">
                    <x-app.input.label id="remark" class="mb-1">{{ __('Remark') }}</x-app.input.label>
                    <x-app.input.input name="remark" id="remark" :hasError="$errors->has('remark')" value="{{ old('remark', isset($customer) ? $customer->remark : null) }}" />
                    <x-app.message.error id="remark_err"/>
                </div>
            </div>
            <div class="mt-8 flex justify-end">
                <x-app.button.submit id="submit-btn">{{ __('Save and Update') }}</x-app.button.submit>
            </div>
        </div>
    </form>
</div>

@push('scripts')
    <script>
        INFO_FORM_CAN_SUBMIT = true
        DEFAULT_BRANCH = @json($default_branch ?? null);

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

        $('#info-form').on('submit', function(e) {
            e.preventDefault()

            if (!INFO_FORM_CAN_SUBMIT) return

            INFO_FORM_CAN_SUBMIT = false

            $('#info-form #submit-btn').text('Updating')
            $('#info-form #submit-btn').removeClass('bg-yellow-400 shadow')
            $('.err_msg').addClass('hidden') // Remove error messages
            // Submit
            let url = '{{ route("customer.upsert_info") }}'

            var formData = new FormData(this);
            formData.append('customer_id', CUSTOMER != null ? CUSTOMER.id : null)

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
                    
                    setTimeout(() => {
                        $('#info-form #submit-btn').text('Updated')
                        $('#info-form #submit-btn').addClass('bg-green-400 shadow')

                        setTimeout(() => {
                            $('#info-form #submit-btn').text('Save and Update')
                            $('#info-form #submit-btn').removeClass('bg-green-400')
                            $('#info-form #submit-btn').addClass('bg-yellow-400 shadow')
                            
                            INFO_FORM_CAN_SUBMIT = true
                        }, 2000);
                    }, 300);
                },
                error: function(err) {
                    setTimeout(() => {
                        if (err.status == StatusCodes.UNPROCESSABLE_ENTITY) {
                            let errors = err.responseJSON.errors
    
                            for (const key in errors) {
                                if (key.includes('picture')) {
                                    $(`#info-form #picture_err`).find('p').text(errors[key])
                                    $(`#info-form #picture_err`).removeClass('hidden')
                                } else {
                                    $(`#info-form #${key}_err`).find('p').text(errors[key])
                                    $(`#info-form #${key}_err`).removeClass('hidden')
                                }
                            }
                        }
                        $('#info-form #submit-btn').text('Save and Update')
                        $('#info-form #submit-btn').addClass('bg-yellow-400 shadow')

                        INFO_FORM_CAN_SUBMIT = true
                    }, 300);
                },
            });
        })
    </script>
@endpush