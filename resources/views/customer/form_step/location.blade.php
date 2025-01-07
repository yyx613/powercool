<div class="bg-white p-4 border rounded-md">
    <div class="flex items-center mb-6 border-l-8 border-yellow-400 px-3 py-1 bg-yellow-50 w-fit">
        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512" height="512"><path d="M12,6a4,4,0,1,0,4,4A4,4,0,0,0,12,6Zm0,6a2,2,0,1,1,2-2A2,2,0,0,1,12,12Z"/><path d="M12,24a5.271,5.271,0,0,1-4.311-2.2c-3.811-5.257-5.744-9.209-5.744-11.747a10.055,10.055,0,0,1,20.11,0c0,2.538-1.933,6.49-5.744,11.747A5.271,5.271,0,0,1,12,24ZM12,2.181a7.883,7.883,0,0,0-7.874,7.874c0,2.01,1.893,5.727,5.329,10.466a3.145,3.145,0,0,0,5.09,0c3.436-4.739,5.329-8.456,5.329-10.466A7.883,7.883,0,0,0,12,2.181Z"/></svg>
        <span class="text-lg ml-3 font-bold">{{ __('Locations') }}</span>
    </div>
    <form action="" method="POST" enctype="multipart/form-data" id="location-form">
        @csrf
        <div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-8 w-full mb-8 p-4 rounded-md relative hidden group transition durtion-300 hover:bg-slate-50" id="item-template">
                <button type="button" class="bg-rose-400 p-2 rounded-full absolute top-[-5px] right-[-5px] hidden group-hover:block delete-item-btns" title="Delete Product">
                    <svg class="h-3 w-3 fill-white" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M13.93,12L21.666,2.443c.521-.644,.422-1.588-.223-2.109-.645-.522-1.588-.421-2.109,.223l-7.334,9.06L4.666,.557c-1.241-1.519-3.56,.357-2.332,1.887l7.736,9.557L2.334,21.557c-.521,.644-.422,1.588,.223,2.109,.64,.519,1.586,.424,2.109-.223l7.334-9.06,7.334,9.06c.524,.647,1.47,.742,2.109,.223,.645-.521,.744-1.466,.223-2.109l-7.736-9.557Z"/></svg>
                </button>
                <div class="flex col-span-4 hidden default-billing-msg">
                    <p class="text-xs text-blue-700 border border-blue-700 p-1.5 rounded shadow">{{ __('Default Billing Address') }}</p>
                </div>
                <div class="flex col-span-4 hidden default-delivery-msg">
                    <p class="text-xs text-blue-700 border border-blue-700 p-1.5 rounded shadow">{{ __('Default Delivery Address') }}</p>
                </div>
                <div class="flex col-span-4 hidden default-billing-and-delivery-msg">
                    <p class="text-xs text-blue-700 border border-blue-700 p-1.5 rounded shadow">{{ __('Default Billing & Delivery Address') }}</p>
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="address" class="mb-1">{{ __('Address') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="address" id="address" :hasError="$errors->has('address')" />
                    <x-app.message.error id="address_err"/>
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="city" class="mb-1">{{ __('City') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="city" id="city" :hasError="$errors->has('city')" value="{{ old('city', isset($customer) ? $customer->city : null) }}" />
                    <x-app.message.error id="city_err"/>
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="state" class="mb-1">{{ __('State') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="state" id="state" :hasError="$errors->has('state')" value="{{ old('state', isset($customer) ? $customer->state : null) }}" />
                    <x-app.message.error id="state_err"/>
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="zip_code" class="mb-1">{{ __('Zip Code') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="zip_code" id="zip_code" :hasError="$errors->has('zip_code')" value="{{ old('zip_code', isset($customer) ? $customer->zip_code : null) }}" class="int-input" />
                    <x-app.message.error id="zip_code_err"/>
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="type" class="mb-1">{{ __('Type') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select name="type" id="type">
                        <option value="">{{ __('Select a type') }}</option>
                        <option value="1">{{ __('Billing') }}</option>
                        <option value="2">{{ __('Delivery') }}</option>
                        <option value="3">{{ __('Billing & Delivery') }}</option>
                    </x-app.input.select>
                    <x-app.message.error id="type_err"/>
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="is_default" class="mb-1">{{ __('Is Default') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select name="is_default" id="is_default">
                        <option value="">{{ __('Select is default') }}</option>
                        <option value="1">{{ __('Yes') }}</option>
                        <option value="0">{{ __('No') }}</option>
                    </x-app.input.select>
                    <x-app.message.error id="is_default_err"/>
                </div>
            </div>
            <div id="items-container"></div>
        </div>
        <div class="mt-8 flex justify-between">
            <!-- Add Items -->
            <button type="button" class="bg-yellow-400 rounded-md py-1.5 px-3 flex items-center gap-x-2 transition duration-300 hover:bg-yellow-300 hover:shadow" id="add-item-btn">
                <svg class="h-3 w-3" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Capa_1" x="0px" y="0px" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve" width="512" height="512">
                    <path d="M480,224H288V32c0-17.673-14.327-32-32-32s-32,14.327-32,32v192H32c-17.673,0-32,14.327-32,32s14.327,32,32,32h192v192   c0,17.673,14.327,32,32,32s32-14.327,32-32V288h192c17.673,0,32-14.327,32-32S497.673,224,480,224z"/>
                </svg>
                <span class="text-sm">{{ __('Add Location') }}</span>
            </button>
            <!-- Submit -->
            <x-app.button.submit id="submit-btn">{{ __('Save and Update') }}</x-app.button.submit>
        </div>
    </form>
</div>


@push('scripts')
<script>
    LOCATION_FORM_CAN_SUBMIT = true
    ITEMS_COUNT = 0

    $(document).ready(function(){
        if (CUSTOMER != null) {
            for (let i = 0; i < CUSTOMER.locations.length; i++) {
                const loc = CUSTOMER.locations[i];

                $('#add-item-btn').click()

                $(`.items[data-id="${i+1}"]`).attr('data-location-id', loc.id)
                $(`.items[data-id="${i+1}"] input[name="address"]`).val(loc.address)
                $(`.items[data-id="${i+1}"] input[name="city"]`).val(loc.city)
                $(`.items[data-id="${i+1}"] input[name="state"]`).val(loc.state)
                $(`.items[data-id="${i+1}"] input[name="zip_code"]`).val(loc.zip_code)
                $(`.items[data-id="${i+1}"] select[name="type"]`).val(loc.type)
                $(`.items[data-id="${i+1}"] select[name="is_default"]`).val(loc.is_default)

                if (loc.is_default == true) {
                    if (loc.type == 1) $(`.items[data-id="${i+1}"] .default-billing-msg`).removeClass('hidden')
                    else if (loc.type == 2) $(`.items[data-id="${i+1}"] .default-delivery-msg`).removeClass('hidden')
                    else $(`.items[data-id="${i+1}"] .default-billing-and-delivery-msg`).removeClass('hidden')
                }
            }

            if (CUSTOMER.locations.length <= 0) $('#add-item-btn').click()
        } else {
            $('#add-item-btn').click()
        }
    })
    $('#add-item-btn').on('click', function() {
        let clone = $('#item-template')[0].cloneNode(true);

        ITEMS_COUNT++
        $(clone).attr('data-id', ITEMS_COUNT)
        $(clone).find('.delete-item-btns').attr('data-id', ITEMS_COUNT)
        $(clone).addClass('items')
        $(clone).removeClass('hidden')
        $(clone).removeAttr('id')

        $('#items-container').append(clone)
    })
    $('body').on('click', '.delete-item-btns', function() {
        let id = $(this).data('id')

        $(`.items[data-id="${id}"]`).remove()

        ITEMS_COUNT = 0
        $('.items').each(function(i, obj) {
            ITEMS_COUNT++
            $(this).attr('data-id', ITEMS_COUNT)
            $(this).find('.delete-item-btns').attr('data-id', ITEMS_COUNT)
        })
    })
    $('#location-form').on('submit', function(e) {
        e.preventDefault()

        if (!LOCATION_FORM_CAN_SUBMIT) return

        LOCATION_FORM_CAN_SUBMIT = false

        $('#location-form #submit-btn').text('Updating')
        $('#location-form #submit-btn').removeClass('bg-yellow-400 shadow')
        $('.err_msg').addClass('hidden') // Remove error messages
        // Submit
        let url = '{{ route("customer.upsert_location") }}'

        let locId = []
        let address = []
        let city = []
        let state = []
        let zipCode = []
        let type = []
        let isDefault = []
        $('#location-form .items').each(function(i, obj) {
            locId.push($(this).data('location-id') ?? null)
            address.push($(this).find('input[name="address"]').val())
            city.push($(this).find('input[name="city"]').val())
            state.push($(this).find('input[name="state"]').val())
            zipCode.push($(this).find('input[name="zip_code"]').val())
            type.push($(this).find('select[name="type"]').val())
            isDefault.push($(this).find('select[name="is_default"]').val())
        })

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: url,
            type: 'POST',
            data: {
                'customer_id': CUSTOMER != null ? CUSTOMER.id : null,
                'location_id': locId,
                'address': address,
                'city': city,
                'state': state,
                'zip_code': zipCode,
                'type': type,
                'is_default': isDefault,
            },
            success: function(res) {
                setTimeout(() => {
                    $('#location-form #submit-btn').text('Updated')
                    $('#location-form #submit-btn').addClass('bg-green-400 shadow')

                    let locIds = res.location_ids
                    $('#location-form .items').each(function(i, obj) {
                        $(this).attr('data-location-id', locIds[i])
                    })

                    $(`#location-form .items .default-billing-msg`).addClass('hidden')
                    if (res.default_billing_address_id != null) {
                        $(`#location-form .items[data-location-id="${res.default_billing_address_id}"] .default-billing-msg`).removeClass('hidden')
                    }
                    $(`#location-form .items .default-delivery-msg`).addClass('hidden')
                    if (res.default_delivery_address_id != null) {
                        $(`#location-form .items[data-location-id="${res.default_delivery_address_id}"] .default-delivery-msg`).removeClass('hidden')
                    }
                    $(`#location-form .items .default-billing-and-delivery-msg`).addClass('hidden')
                    if (res.default_billing_and_delivery_address_id != null) {
                        $(`#location-form .items[data-location-id="${res.default_billing_and_delivery_address_id}"] .default-billing-and-delivery-msg`).removeClass('hidden')
                    }

                    setTimeout(() => {
                        $('#location-form #submit-btn').text('Save and Update')
                        $('#location-form #submit-btn').removeClass('bg-green-400')
                        $('#location-form #submit-btn').addClass('bg-yellow-400 shadow')

                        LOCATION_FORM_CAN_SUBMIT = true
                    }, 2000);
                }, 300);
            },
            error: function(err) {
                setTimeout(() => {
                    if (err.status == StatusCodes.UNPROCESSABLE_ENTITY) {
                        let errors = err.responseJSON.errors

                        for (const key in errors) {
                            let field = key.split('.')[0]
                            let idx = key.split('.')[1]
                            idx++
                            $(`#location-form .items[data-id="${idx}"] #${field}_err`).find('p').text(errors[key])
                            $(`#location-form .items[data-id="${idx}"] #${field}_err`).removeClass('hidden')
                        }
                    } else if (err.status == StatusCodes.BAD_REQUEST) {
                        $(`#location-form .items #is_default_err`).find('p').text(err.responseJSON.is_default)
                        $(`#location-form .items #is_default_err`).removeClass('hidden')
                    }
                    $('#location-form #submit-btn').text('Save and Update')
                    $('#location-form #submit-btn').addClass('bg-yellow-400 shadow')

                    LOCATION_FORM_CAN_SUBMIT = true
                }, 300);
            },
        });
    })
</script>
@endpush
