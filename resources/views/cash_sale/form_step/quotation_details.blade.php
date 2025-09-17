<div class="bg-white p-4 border rounded-md" id="quotation-details-container">
    <div class="flex items-center mb-6 border-l-8 border-yellow-400 px-3 py-1 bg-yellow-50 w-fit">
        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512"
            height="512">
            <path
                d="M12,0A12,12,0,1,0,24,12,12.013,12.013,0,0,0,12,0Zm0,22A10,10,0,1,1,22,12,10.011,10.011,0,0,1,12,22Z" />
            <path d="M12,10H11a1,1,0,0,0,0,2h1v6a1,1,0,0,0,2,0V12A2,2,0,0,0,12,10Z" />
            <circle cx="12" cy="6.5" r="1.5" />
        </svg>
        <span class="text-lg ml-3 font-bold">{{ __('Cash Sale Details') }}</span>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 md:gap-8 w-full mb-8">
        @if (isSuperAdmin())
            <div class="flex flex-col">
                <x-app.input.label id="custom_date" class="mb-1">{{ __('Date') }}</x-app.input.label>
                <x-app.input.input name="custom_date" id="custom_date" :hasError="$errors->has('custom_date')"
                    value="{{ isset($sale) ? $sale->custom_date : null }}" />
                <x-app.message.error id="custom_date_err" />
            </div>
        @endif
        <div class="flex flex-col">
            <x-app.input.label id="company_group" class="mb-1">{{ __('Company Group') }} <span
                    class="text-sm text-red-500">*</span></x-app.input.label>
            <x-app.input.select name="company_group" id="company_group" :hasError="$errors->has('company_group')"
                placeholder="{{ __('Select a company group') }}">
                <option value="">{{ __('Select a company group') }}</option>
                <option value="1" @selected(old('company_group', isset($sale) ? $sale->company_group : null) == 1)>{{ __('Power Cool') }}</option>
                <option value="2" @selected(old('company_group', isset($sale) ? $sale->company_group : null) == 2)>{{ __('Hi-Ten') }}</option>
            </x-app.input.select>
            <x-app.message.error id="company_group_err" />
        </div>
        <div class="flex flex-col">
            <x-app.input.label id="custom_customer" class="mb-1">{{ __('Company') }} <span
                    class="text-sm text-red-500">*</span></x-app.input.label>
            <x-app.input.input name="custom_customer" id="custom_customer" :hasError="$errors->has('custom_customer')"
                value="{{ isset($sale) ? $sale->custom_customer : null }}" />
            <x-app.message.error id="custom_customer_err" />
        </div>
        <div class="flex flex-col">
            <x-app.input.label id="custom_mobile" class="mb-1">{{ __('Mobile') }}</x-app.input.label>
            <x-app.input.input name="custom_mobile" id="custom_mobile" :hasError="$errors->has('custom_mobile')"
                value="{{ isset($sale) ? $sale->custom_mobile : null }}" />

            <x-app.message.error id="custom_mobile_err" />
        </div>
        <div class="flex flex-col">
            <x-app.input.label id="sale" class="mb-1">{{ __('Sales Agent') }} <span
                    class="text-sm text-red-500">*</span></x-app.input.label>
            <x-app.input.select name="sale" id="sale" :hasError="$errors->has('sale')"
                placeholder="{{ __('Select a sales agent') }}">
                <option value="">{{ __('Select a sales agent') }}</option>
                @foreach ($sales_agents as $sa)
                    <option value="{{ $sa->id }}" @selected(old('sale', isset($sale) ? $sale->sale_id : null) == $sa->id)>{{ $sa->name }}</option>
                @endforeach
            </x-app.input.select>
            <x-app.message.error id="sale_err" />
        </div>
        <div class="flex flex-col">
            <x-app.input.label id="reference" class="mb-1">{{ __('Your P/O No') }}</x-app.input.label>
            <x-app.input.multi-input name="reference" id="reference" :hasError="$errors->has('reference')"
                value="{{ isset($sale) ? $sale->reference : null }}" />
            <x-app.message.error id="reference_err" />
        </div>
        <div class="flex flex-col">
            <x-app.input.label id="report_type" class="mb-1">{{ __('Type') }} <span
                    class="text-sm text-red-500">*</span></x-app.input.label>
            <x-app.input.select name="report_type" id="report_type" :hasError="$errors->has('report_type')">
                <option value="">{{ __('Select a type') }}</option>
                @foreach ($report_types as $type)
                    <option value="{{ $type->id }}" @selected(old('report_type', isset($sale) ? $sale->report_type : null) == $type->id)>{{ $type->name }}</option>
                @endforeach
            </x-app.input.select>
            <x-app.message.error id="report_type_err" />
        </div>
        <div class="flex flex-col">
            <x-app.input.label id="status" class="mb-1">{{ __('Status') }} <span
                    class="text-sm text-red-500">*</span></x-app.input.label>
            <x-app.input.select name="status" id="status" :hasError="$errors->has('status')">
                <option value="">{{ __('Select a status') }}</option>
                @if (isset($sale) && $sale->status == 4)
                    <option value="4" @selected(old('status', isset($sale) ? $sale->status : null) === 4)>{{ __('Pending Approval') }}</option>
                @elseif (isset($sale) && $sale->status == 7)
                    <option value="7" selected>{{ __('Rejected') }}</option>
                @elseif (isset($sale) && $sale->status == 5)
                    <option value="5" selected>{{ __('Approved') }}</option>
                @else
                    <option value="1" @selected(old('status', isset($sale) ? $sale->status : null) == 1)>{{ __('Active') }}</option>
                    <option value="0" @selected(old('status', isset($sale) ? $sale->status : null) === 0)>{{ __('Inactive') }}</option>
                @endif
            </x-app.input.select>
            <x-app.message.error id="status_err" />
        </div>
    </div>
    {{-- Custom Address --}}
    <div class="pt-4 border-t border-slate-200">
        <div class="mb-8" id="new-billing-address">
            @include('components.app.address-field', [
                'title' => 'Billing Address',
            ])
        </div>
        <div id="new-delivery-address">
            @include('components.app.address-field', [
                'title' => 'Delivery Address',
            ])
        </div>
    </div>
</div>

@push('scripts')
    <script>
        INIT_EDIT = true
        BILLING_ADDRESS = @json($billing_address ?? null);
        DELIVERY_ADDRESS = @json($delivery_address ?? null);

        $('input[name="custom_date"]').daterangepicker(datepickerParam)
        $('input[name="custom_date"]').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD'));
        });

        $(document).ready(function() {
            if (SALE != null) {
                if (SALE.status == 4) {
                    $('select[name="status"]').attr('disabled', true)
                    $('select[name="status"]').attr('aria-disabled', true)

                }
                $('#new-billing-address input[name="address1"]').val(BILLING_ADDRESS.address1)
                $('#new-billing-address input[name="address2"]').val(BILLING_ADDRESS.address2)
                $('#new-billing-address input[name="address3"]').val(BILLING_ADDRESS.address3)
                $('#new-billing-address input[name="address4"]').val(BILLING_ADDRESS.address4)
                $('#new-delivery-address input[name="address1"]').val(DELIVERY_ADDRESS.address1)
                $('#new-delivery-address input[name="address2"]').val(DELIVERY_ADDRESS.address2)
                $('#new-delivery-address input[name="address3"]').val(DELIVERY_ADDRESS.address3)
                $('#new-delivery-address input[name="address4"]').val(DELIVERY_ADDRESS.address4)
            }

            INIT_EDIT = false
        })
    </script>
@endpush
