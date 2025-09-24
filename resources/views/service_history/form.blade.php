@extends('layouts.app')
@section('title', 'Service History')

@section('content')
    <div class="mb-6 flex justify-between items-center">
        <x-app.page-title url="{{ route('service_history.index') }}">{{ __('Create Service History') }}</x-app.page-title>
    </div>
    @include('components.app.alert.parent')
    <form action="{{ route('service_history.store') }}" method="POST" enctype="multipart/form-data" id="form">
        @csrf
        <div class="bg-white p-4 rounded-md shadow" id="content-container">
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-8 w-full mb-4">
                <div class="flex flex-col">
                    <x-app.input.label id="service_date" class="mb-1">{{ __('Service Date') }} <span
                            class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="service_date" id="service_date" value="{{ old('service_date') }}" />
                    <x-input-error :messages="$errors->get('service_date')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="service_by" class="mb-1">{{ __('Service By') }} <span
                            class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select2 name="service_by" id="service_by" placeholder="{{ __('Select a technician') }}">
                        <option value="">{{ __('Select a technician') }}</option>
                        @foreach ($technicians as $technician)
                            <option value="{{ $technician->id }}" @selected(old('serial_no') == $technician->id)>{{ $technician->name }}</option>
                        @endforeach
                    </x-app.input.select2>
                    <x-input-error :messages="$errors->get('serial_no')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="serial_no" class="mb-1">{{ __('Serial No') }} <span
                            class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select2 name="serial_no" id="serial_no" placeholder="{{ __('Select a serial no') }}">
                        <option value="">{{ __('Select a serial no') }}</option>
                        @foreach ($pcs as $pc)
                            <option value="{{ $pc->id }}" @selected(old('serial_no') == $pc->id)>{{ $pc->sku }}</option>
                        @endforeach
                    </x-app.input.select2>
                    <x-input-error :messages="$errors->get('serial_no')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="qty" class="mb-1">{{ __('Qty') }} <span
                            class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="qty" id="qty" value="{{ old('name') }}" class="int-input" />
                    <x-input-error :messages="$errors->get('qty')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="photo" class="mb-1">{{ __('Photo') }}</x-app.input.label>
                    <x-app.input.file name="photo[]" id="photo[]" multiple />
                    <p class="text-sm mt-1" id="uploaded-n-file-label"></p>
                    <x-input-error :messages="$errors->get('photo[]')" class="mt-1" />
                </div>
            </div>
            <div class="mt-8 flex justify-end gap-x-4">
                <x-app.button.submit>{{ __('Save and Update') }}</x-app.button.submit>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        $('input[name="service_date"]').daterangepicker(datepickerParam)
        $('input[name="service_date"]').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD'));
        });

        $('input[type="file"]').on('change', function(e) {
            let files = e.target.files

            $('#uploaded-n-file-label').text(`Uploaded ${files.length} files`)
        })
    </script>
@endpush
