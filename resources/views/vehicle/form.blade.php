@extends('layouts.app')

@section('content')
    <div class="mb-6 flex justify-between items-center">
        <x-app.page-title url="{{ route('vehicle.index') }}">{{ isset($vehicle) ? __('Edit Vehicle') : __('Create Vehicle') }}</x-app.page-title>
    </div>
    @include('components.app.alert.parent')
    <form action="{{ route('vehicle.upsert', ['vehicle' => isset($vehicle) ? $vehicle : null]) }}" method="POST" enctype="multipart/form-data" id="form">
        @csrf
        <div class="bg-white p-4 rounded-md shadow" id="content-container">
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-8 w-full mb-4">
                <div class="flex flex-col">
                    <x-app.input.label id="plate_number" class="mb-1">{{ __('Plate Number') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="plate_number" id="plate_number" :hasError="$errors->has('plate_number')" value="{{ old('plate_number', isset($vehicle) ? $vehicle->plate_number : null) }}" />
                    <x-input-error :messages="$errors->get('plate_number')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="chasis" class="mb-1">{{ __('No Chasis / No Enjin') }}</x-app.input.label>
                    <x-app.input.input name="chasis" id="chasis" :hasError="$errors->has('chasis')" value="{{ old('chasis', isset($vehicle) ? $vehicle->chasis : null) }}" />
                    <x-input-error :messages="$errors->get('chasis')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="buatan_nama_model" class="mb-1">{{ __('Buatan / Nama Model') }}</x-app.input.label>
                    <x-app.input.input name="buatan_nama_model" id="buatan_nama_model" :hasError="$errors->has('buatan_nama_model')" value="{{ old('buatan_nama_model', isset($vehicle) ? $vehicle->buatan_nama_model : null) }}" />
                    <x-input-error :messages="$errors->get('buatan_nama_model')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="keupayaan_enjin" class="mb-1">{{ __('Keupayaan Enjin') }}</x-app.input.label>
                    <x-app.input.input name="keupayaan_enjin" id="keupayaan_enjin" :hasError="$errors->has('keupayaan_enjin')" value="{{ old('keupayaan_enjin', isset($vehicle) ? $vehicle->keupayaan_enjin : null) }}" />
                    <x-input-error :messages="$errors->get('keupayaan_enjin')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="bahan_bakar" class="mb-1">{{ __('Bahan Bakar') }}</x-app.input.label>
                    <x-app.input.input name="bahan_bakar" id="bahan_bakar" :hasError="$errors->has('bahan_bakar')" value="{{ old('bahan_bakar', isset($vehicle) ? $vehicle->bahan_bakar : null) }}" />
                    <x-input-error :messages="$errors->get('bahan_bakar')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="status_asal" class="mb-1">{{ __('Status Asal') }}</x-app.input.label>
                    <x-app.input.input name="status_asal" id="status_asal" :hasError="$errors->has('status_asal')" value="{{ old('status_asal', isset($vehicle) ? $vehicle->status_asal : null) }}" />
                    <x-input-error :messages="$errors->get('status_asal')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="kelas_kegunaan" class="mb-1">{{ __('Kelas Kegunaan') }}</x-app.input.label>
                    <x-app.input.input name="kelas_kegunaan" id="kelas_kegunaan" :hasError="$errors->has('kelas_kegunaan')" value="{{ old('kelas_kegunaan', isset($vehicle) ? $vehicle->kelas_kegunaan : null) }}" />
                    <x-input-error :messages="$errors->get('kelas_kegunaan')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="jenis_badan" class="mb-1">{{ __('Jenis Badan / Tahun Dibuat') }}</x-app.input.label>
                    <x-app.input.input name="jenis_badan" id="jenis_badan" :hasError="$errors->has('jenis_badan')" value="{{ old('jenis_badan', isset($vehicle) ? $vehicle->jenis_badan : null) }}" />
                    <x-input-error :messages="$errors->get('jenis_badan')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="tarikh_pendaftaran" class="mb-1">{{ __('Tarikh Pendaftaran') }}</x-app.input.label>
                    <x-app.input.input name="tarikh_pendaftaran" id="tarikh_pendaftaran" :hasError="$errors->has('tarikh_pendaftaran')" value="{{ old('tarikh_pendaftaran', isset($vehicle) ? $vehicle->tarikh_pendaftaran : null) }}" />
                    <x-input-error :messages="$errors->get('tarikh_pendaftaran')" class="mt-1" />
                </div>
            </div>
            <div class="mt-8 flex justify-end gap-x-4">
                @if (!isset($vehicle))
                    <x-app.button.submit id="submit-create-btn">{{ __('Save and Create') }}</x-app.button.submit>
                @endif
                <x-app.button.submit>{{ __('Save and Update') }}</x-app.button.submit>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        $('#submit-create-btn').on('click', function(e) {
            let url = $('#form').attr('action')
            url = `${url}?create_again=true`

            $('#form').attr('action', url)
        })
    </script>
@endpush
