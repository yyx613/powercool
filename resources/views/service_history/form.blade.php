@extends('layouts.app')

@section('content')
    <div class="mb-6 flex justify-between items-center">
        <x-app.page-title url="{{ route('service_history.index') }}">{{ isset($sh) ? __('Edit Service History') : __('Create Service History') }}</x-app.page-title>
    </div>
    @include('components.app.alert.parent')
    <form action="{{ isset($sh) ? route('service_history.upsert', ['sh' => $sh]) : route('service_history.upsert') }}" method="POST" enctype="multipart/form-data" id="form">
        @csrf
        <input type="hidden" name="is_child">
        <div class="bg-white p-4 rounded-md shadow" id="content-container">
            <div class="grid grid-cols-3 gap-8 w-full mb-4">
                <div class="flex flex-col">
                    <x-app.input.label id="product" class="mb-1">{{ __('Product Code / Product Serial No') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select2 name="product" id="product" :hasError="$errors->has('product')" placeholder="{{ __('Select a Product Code / Product Serial No') }}">
                        <option value="">{{ __('Select a Product Code / Product Serial No') }}</option>
                        @foreach ($raw_materials as $rm)
                            <option value="{{ $rm->id }}" data-is-child="false">{{ $rm->sku }}</option>
                        @endforeach
                        @foreach ($product_children as $pc)
                            <option value="{{ $pc->id }}" data-is-child="true">{{ $pc->sku }}</option>
                        @endforeach
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
    $('input[name="next_service_date"]').daterangepicker(datepickerParam)
    $('input[name="next_service_date"]').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('YYYY-MM-DD'));
    });

    $('select[name="product"]').on('change', function() {
        $('input[name="is_child"]').val($(this).find('option:checked').data('is-child'))
    })

    $('#submit-create-btn').on('click', function(e) {
        let url = $('#form').attr('action')
        url = `${url}?create_again=true`

        $('#form').attr('action', url)
    })
</script>
@endpush