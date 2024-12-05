@extends('layouts.app')

@section('content')
    <div class="mb-6 flex justify-between items-center">
        <x-app.page-title url="{{ route('service_history.index') }}">{{ isset($sh) ? __('Edit Service History') : __('Create Service History') }}</x-app.page-title>
    </div>
    @include('components.app.alert.parent')
    <form action="{{ isset($sh) ? route('service_history.upsert', ['sh' => $sh]) : route('service_history.upsert') }}" method="POST" enctype="multipart/form-data" id="form">
        @csrf
        <div class="bg-white p-4 rounded-md shadow" id="content-container">
            <div class="grid grid-cols-3 gap-8 w-full mb-4">
                <div class="flex flex-col">
                    <x-app.input.label id="sale_order" class="mb-1">{{ __('Sale Order') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select2 name="sale_order" id="sale_order" :hasError="$errors->has('sale_order')" placeholder="{{ __('Select a sale order') }}">
                        <option value="">{{ __('Select a sale order') }}</option>
                        @foreach ($sale_orders as $so)
                            <option value="{{ $so->id }}">{{ $so->sku }}</option>
                        @endforeach
                    </x-app.input.select2>
                    <x-input-error :messages="$errors->get('sale_order')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="product" class="mb-1">{{ __('Product Serial No') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select2 name="product" id="product" :hasError="$errors->has('product')" placeholder="{{ __('Select a product serial no') }}">
                        <option value="">{{ __('Select a product serial no') }}</option>
                    </x-app.input.select2>
                    <x-input-error :messages="$errors->get('product')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="next_service_date" class="mb-1">{{ __('Next Service Date') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="next_service_date" id="next_service_date" :hasError="$errors->has('next_service_date')" value="{{ old('next_service_date') }}" />
                    <x-input-error :messages="$errors->get('next_service_date')" class="mt-2" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="remind" class="mb-1">{{ __('Remind before next service date (In days)') }}</x-app.input.label>
                    <x-app.input.input name="remind" id="remind" :hasError="$errors->has('remind')" class="int-input" value="{{ old('remind') }}" />
                    <x-input-error :messages="$errors->get('remind')" class="mt-2" />
                </div>
            </div>
            <div class="mt-8 flex justify-end gap-x-4">
                @if (!isset($sh))
                    <x-app.button.submit id="submit-create-btn">{{ __('Save and Create') }}</x-app.button.submit>
                @endif
                <x-app.button.submit>{{ __('Save and Update') }}</x-app.button.submit>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    SALE_ORDERS = @json($sale_orders ?? null);

    $('input[name="next_service_date"]').daterangepicker(datepickerParam)
    $('input[name="next_service_date"]').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('YYYY-MM-DD'));
    });

    $('select[name="sale_order"]').on('change', function() {
        $(`select[name="product"]`).find('option').not(':first').remove();
        
        let val = $(this).val()

        for (let i = 0; i < SALE_ORDERS.length; i++) {
            if (val == SALE_ORDERS[i].id) {
                for (let j = 0; j < SALE_ORDERS[i].products.length; j++) {
                    for (let k = 0; k < SALE_ORDERS[i].products[j].children.length; k++) {
                        let opt = new Option(SALE_ORDERS[i].products[j].children[k].product_child.sku, SALE_ORDERS[i].products[j].children[k].product_child.id)
                        $('select[name="product"]').append(opt)
                    }
                }
                break
            }            
        }
    })

    $('#submit-create-btn').on('click', function(e) {
        let url = $('#form').attr('action')
        url = `${url}?create_again=true`

        $('#form').attr('action', url)
    })
</script>
@endpush