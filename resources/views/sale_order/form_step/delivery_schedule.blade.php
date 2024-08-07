<div class="bg-white p-4 border rounded-md">
    <div class="flex items-center mb-6 border-l-8 border-yellow-400 px-3 py-1 bg-yellow-50 w-fit">
        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24">
            <path d="m24,10c0-2.757-2.243-5-5-5h-2v-1c0-1.654-1.346-3-3-3h-2v2h2c.552,0,1,.449,1,1v13H2v-4H0v6h2.037c-.024.165-.037.331-.037.5,0,1.93,1.57,3.5,3.5,3.5s3.5-1.57,3.5-3.5c0-.169-.013-.335-.037-.5h6.074c-.024.165-.037.331-.037.5,0,1.93,1.57,3.5,3.5,3.5s3.5-1.57,3.5-3.5c0-.169-.013-.335-.037-.5h2.037v-9ZM7,19.5c0,.827-.673,1.5-1.5,1.5s-1.5-.673-1.5-1.5c0-.189.037-.356.091-.5h2.819c.054.144.091.311.091.5Zm12-12.5c1.654,0,3,1.346,3,3v1h-5v-4h2Zm1,12.5c0,.827-.673,1.5-1.5,1.5s-1.5-.673-1.5-1.5c0-.189.037-.356.091-.5h2.819c.054.144.091.311.091.5Zm-3-2.5v-4h5v4h-5ZM10,3H0V1h10v2Zm-2,4H0v-2h8v2Zm-2,4H0v-2h6v2Z"/>
        </svg>
        <span class="text-lg ml-3 font-bold">Delivery Schedule</span>
    </div>
    <form action="" method="POST" enctype="multipart/form-data" id="delivery-form">
        @csrf
        <div>
            <div class="grid grid-cols-3 gap-8 w-full mb-8">
                <div class="flex flex-col">
                    <x-app.input.label id="delivery_date" class="mb-1">Delivery Date</x-app.input.label>
                    <x-app.input.input name="delivery_date" id="delivery_date" :hasError="$errors->has('delivery_date')" value="{{ isset($sale) ? $sale->delivery_date : null }}" />
                    <x-app.message.error id="delivery_date_err"/>
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="delivery_time" class="mb-1">Delivery Time</x-app.input.label>
                    <x-app.input.input name="delivery_time" id="delivery_time" :hasError="$errors->has('delivery_time')" value="{{ isset($sale) ? $sale->delivery_time : null }}" type="time" />
                    <x-app.message.error id="delivery_time_err"/>
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="driver" class="mb-1">Driver <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select2 name="driver" id="driver" :hasError="$errors->has('driver')" placeholder="Select a driver">
                        <option value="">Select a driver</option>
                        @foreach ($drivers as $dr)
                            <option value="{{ $dr->id }}" @selected(old('driver', isset($sale) ? $sale->driver_id : null) == $dr->id)>{{ $dr->name }}</option>
                        @endforeach
                    </x-app.input.select2>
                    <x-app.message.error id="driver_err"/>
                </div>
                <div class="flex flex-col col-span-2">
                    <x-app.input.label id="delivery_instruction" class="mb-1">Delivery Instructions</x-app.input.label>
                    <x-app.input.input name="delivery_instruction" id="delivery_instruction" :hasError="$errors->has('delivery_instruction')" value="{{ isset($sale) ? $sale->delivery_instruction : null }}" />
                    <x-app.message.error id="delivery_instruction_err"/>
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="status" class="mb-1">Status <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select name="status" id="status" :hasError="$errors->has('status')">
                        <option value="">Select a Active/Inactive</option>
                        <option value="1" @selected(old('status', isset($sale) ? $sale->is_active : null) == 1)>Active</option>
                        <option value="0" @selected(old('status', isset($sale) ? $sale->is_active : null) === 0)>Inactive</option>
                    </x-app.input.select>
                    <x-app.message.error id="status_err"/>
                </div>
                <div class="flex flex-col col-span-2">
                    <x-app.input.label id="delivery_address" class="mb-1">Delivery Address</x-app.input.label>
                    <x-app.input.input name="delivery_address" id="delivery_address" :hasError="$errors->has('delivery_address')" value="{{ isset($sale) ? $sale->delivery_address : null }}" />
                    <x-app.message.error id="delivery_address_err"/>
                </div>
            </div>
            <div class="mt-4 flex justify-end">
                <x-app.button.submit id="submit-btn">Save and Update</x-app.button.submit>
            </div>
        </div>
    </form>
</div>

@push('scripts')
    <script>
        DELIVERY_FORM_CAN_SUBMIT = true

        $('input[name="delivery_date"]').daterangepicker(datepickerParam)
        $('input[name="delivery_date"]').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD'));
        });

        $('#delivery-form').on('submit', function(e) {
            e.preventDefault()

            if (!DELIVERY_FORM_CAN_SUBMIT) return

            DELIVERY_FORM_CAN_SUBMIT = false

            $('#delivery-form #submit-btn').text('Updating')
            $('#delivery-form #submit-btn').removeClass('bg-yellow-400 shadow')
            $('.err_msg').addClass('hidden') // Remove error messages
            // Submit
            let url = '{{ route("sale.upsert_delivery_schedule") }}'
            url = `${url}`

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: url,
                type: 'POST',
                data: {
                    'sale_id': typeof SALE !== 'undefined' && SALE != null ? SALE.id : null,
                    'driver': $('#delivery-form select[name="driver"]').val(),
                    'delivery_date': $('#delivery-form input[name="delivery_date"]').val(),
                    'delivery_time': $('#delivery-form input[name="delivery_time"]').val(),
                    'delivery_instruction': $('#delivery-form input[name="delivery_instruction"]').val(),
                    'delivery_address': $('#delivery-form input[name="delivery_address"]').val(),
                    'status': $('#delivery-form select[name="status"]').val(),
                },
                success: function(res) {
                    if (typeof QUO !== 'undefined') {
                        QUO = res.quo
                    }
                    
                    setTimeout(() => {
                        $('#delivery-form #submit-btn').text('Updated')
                        $('#delivery-form #submit-btn').addClass('bg-green-400 shadow')

                        setTimeout(() => {
                            $('#delivery-form #submit-btn').text('Save and Update')
                            $('#delivery-form #submit-btn').removeClass('bg-green-400')
                            $('#delivery-form #submit-btn').addClass('bg-yellow-400 shadow')
                            
                            DELIVERY_FORM_CAN_SUBMIT = true
                        }, 2000);
                    }, 300);
                },
                error: function(err) {
                    setTimeout(() => {
                        if (err.status == StatusCodes.UNPROCESSABLE_ENTITY) {
                            let errors = err.responseJSON.errors
    
                            for (const key in errors) {
                                $(`#delivery-form #${key}_err`).find('p').text(errors[key])
                                $(`#delivery-form #${key}_err`).removeClass('hidden')
                            }
                        }
                        $('#delivery-form #submit-btn').text('Save and Update')
                        $('#delivery-form #submit-btn').addClass('bg-yellow-400 shadow')

                        DELIVERY_FORM_CAN_SUBMIT = true
                    }, 300);
                },
            });
        })
    </script>
@endpush