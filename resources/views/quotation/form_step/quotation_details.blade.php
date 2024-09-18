<div class="bg-white p-4 border rounded-md">
    <div class="flex items-center mb-6 border-l-8 border-yellow-400 px-3 py-1 bg-yellow-50 w-fit">
        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512" height="512"><path d="M12,0A12,12,0,1,0,24,12,12.013,12.013,0,0,0,12,0Zm0,22A10,10,0,1,1,22,12,10.011,10.011,0,0,1,12,22Z"/><path d="M12,10H11a1,1,0,0,0,0,2h1v6a1,1,0,0,0,2,0V12A2,2,0,0,0,12,10Z"/><circle cx="12" cy="6.5" r="1.5"/></svg>
        <span class="text-lg ml-3 font-bold">Quotation Details</span>
    </div>
    <form action="" method="POST" enctype="multipart/form-data" id="quotation-form">
        @csrf
        <div>
            <div class="grid grid-cols-3 gap-8 w-full mb-8">
                <div class="flex flex-col">
                    <x-app.input.label id="open_until" class="mb-1">Open Until <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="open_until" id="open_until" :hasError="$errors->has('open_until')" value="{{ isset($sale) ? $sale->open_until : null }}" />
                    <x-app.message.error id="open_until_err"/>
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="reference" class="mb-1">Reference <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="reference" id="reference" :hasError="$errors->has('reference')" value="{{ isset($sale) ? $sale->reference : null }}" />
                    <x-app.message.error id="reference_err"/>
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="from" class="mb-1">From</x-app.input.label>
                    <x-app.input.input name="from" id="from" :hasError="$errors->has('from')" value="{{ isset($sale) ? $sale->quo_from : null }}" />
                    <x-app.message.error id="from_err"/>
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="cc" class="mb-1">C.C.</x-app.input.label>
                    <x-app.input.input name="cc" id="cc" :hasError="$errors->has('cc')" value="{{ isset($sale) ? $sale->quo_cc : null }}" />
                    <x-app.message.error id="cc_err"/>
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="sale" class="mb-1">Assigned To <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select2 name="sale" id="sale" :hasError="$errors->has('sale')" placeholder="Select a sale">
                        <option value="">Select a sale</option>
                        @foreach ($sales as $sa)
                            <option value="{{ $sa->id }}" @selected(old('sale', isset($sale) ? $sale->sale_id : null) == $sa->id)>{{ $sa->name }}</option>
                        @endforeach
                    </x-app.input.select2>
                    <x-app.message.error id="sale_err"/>
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="customer" class="mb-1">Customer <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select2 name="customer" id="customer" :hasError="$errors->has('customer')" placeholder="Select a customer">
                        <option value="">Select a customer</option>
                        @foreach ($customers as $cu)
                            <option value="{{ $cu->id }}" @selected(old('customer', isset($sale) ? $sale->customer_id : null) == $cu->id)>{{ $cu->name }}</option>
                        @endforeach
                    </x-app.input.select2>
                    <x-app.message.error id="customer_err"/>
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="status" class="mb-1">Status <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select name="status" id="status" :hasError="$errors->has('status')">
                        <option value="">Select a status</option>
                        <option value="1" @selected(old('status', isset($sale) ? $sale->status : null) == 1)>Active</option>
                        <option value="0" @selected(old('status', isset($sale) ? $sale->status : null) === 0)>Inactive</option>
                    </x-app.input.select>
                    <x-app.message.error id="status_err"/>
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="report_type" class="mb-1">Type <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select name="report_type" id="report_type" :hasError="$errors->has('report_type')">
                        <option value="">Select a type</option>
                        @foreach ($report_types as $key => $val)
                            <option value="{{ $key }}" @selected(old('report_type', isset($sale) ? $sale->report_type : null) == $key)>{{ $val }}</option>
                        @endforeach
                    </x-app.input.select>
                    <x-app.message.error id="report_type_err"/>
                </div>
            </div>
            @if (isset($sale) && $sale->status == 2)
                <div class="mt-8 flex justify-end">
                    <span class="text-sm text-slate-500 border border-slate-500 py-1 px-1.5 w-fit rounded">Converted</span>
                </div>
            @else
                <div class="mt-8 flex justify-end">
                    <x-app.button.submit id="submit-btn">Save and Update</x-app.button.submit>
                </div>
            @endif
        </div>
    </form>
</div>

@push('scripts')
    <script>
        QUOTATION_FORM_CAN_SUBMIT = true

        $('input[name="open_until"]').daterangepicker(datepickerParam)
        $('input[name="open_until"]').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD'));
        });

        $('#quotation-form').on('submit', function(e) {
            e.preventDefault()

            if (!QUOTATION_FORM_CAN_SUBMIT) return

            QUOTATION_FORM_CAN_SUBMIT = false

            $('#quotation-form #submit-btn').text('Updating')
            $('#quotation-form #submit-btn').removeClass('bg-yellow-400 shadow')
            $('.err_msg').addClass('hidden') // Remove error messages
            // Submit
            let url = '{{ route("sale.upsert_quo_details") }}'
            url = `${url}?type=quo`

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: url,
                type: 'POST',
                data: {
                    'sale_id': typeof SALE !== 'undefined' && SALE != null ? SALE.id : null,
                    'sale': $('#quotation-form select[name="sale"]').val(),
                    'customer': $('#quotation-form select[name="customer"]').val(),
                    'open_until': $('#quotation-form input[name="open_until"]').val(),
                    'reference': $('#quotation-form input[name="reference"]').val(),
                    'from': $('#quotation-form input[name="from"]').val(),
                    'cc': $('#quotation-form input[name="cc"]').val(),
                    'status': $('#quotation-form select[name="status"]').val(),
                    'report_type': $('#quotation-form select[name="report_type"]').val(),
                },
                success: function(res) {
                    if (typeof SALE !== 'undefined') {
                        SALE = res.sale
                    }
                    
                    setTimeout(() => {
                        $('#quotation-form #submit-btn').text('Updated')
                        $('#quotation-form #submit-btn').addClass('bg-green-400 shadow')

                        setTimeout(() => {
                            $('#quotation-form #submit-btn').text('Save and Update')
                            $('#quotation-form #submit-btn').removeClass('bg-green-400')
                            $('#quotation-form #submit-btn').addClass('bg-yellow-400 shadow')
                            
                            QUOTATION_FORM_CAN_SUBMIT = true
                        }, 2000);
                    }, 300);
                },
                error: function(err) {
                    setTimeout(() => {
                        if (err.status == StatusCodes.UNPROCESSABLE_ENTITY) {
                            let errors = err.responseJSON.errors
    
                            for (const key in errors) {
                                $(`#quotation-form #${key}_err`).find('p').text(errors[key])
                                $(`#quotation-form #${key}_err`).removeClass('hidden')
                            }
                        }
                        $('#quotation-form #submit-btn').text('Save and Update')
                        $('#quotation-form #submit-btn').addClass('bg-yellow-400 shadow')

                        QUOTATION_FORM_CAN_SUBMIT = true
                    }, 300);
                },
            });
        })
    </script>
@endpush