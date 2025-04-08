@extends('layouts.app')

@section('content')
    <div class="mb-6 flex justify-between items-center">
        <x-app.page-title url="{{ route('vehicle_service.index') }}">{{ isset($service) ? __('Edit Vehicle Service') : __('Create Vehicle Service') }}</x-app.page-title>
    </div>
    @include('components.app.alert.parent')
    <form action="{{ route('vehicle_service.upsert', ['service' => isset($service) ? $service : null]) }}" method="POST" enctype="multipart/form-data" id="form" class="flex flex-col gap-8">
        @csrf
        <div class="bg-white p-4 rounded-md shadow">
            <div class="flex items-center mb-6 border-l-8 border-yellow-400 px-3 py-1 bg-yellow-50 w-fit">
                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M19.5,16c0,.553-.447,1-1,1h-2c-.553,0-1-.447-1-1s.447-1,1-1h2c.553,0,1,.447,1,1Zm4.5-1v5c0,2.206-1.794,4-4,4H4c-2.206,0-4-1.794-4-4v-5c0-2.206,1.794-4,4-4h1V4C5,1.794,6.794,0,9,0h6c2.206,0,4,1.794,4,4v7h1c2.206,0,4,1.794,4,4ZM7,11h10V4c0-1.103-.897-2-2-2h-6c-1.103,0-2,.897-2,2v7Zm-3,11h7V13H4c-1.103,0-2,.897-2,2v5c0,1.103,.897,2,2,2Zm18-7c0-1.103-.897-2-2-2h-7v9h7c1.103,0,2-.897,2-2v-5Zm-14.5,0h-2c-.553,0-1,.447-1,1s.447,1,1,1h2c.553,0,1-.447,1-1s-.447-1-1-1ZM14,5c0-.553-.447-1-1-1h-2c-.553,0-1,.447-1,1s.447,1,1,1h2c.553,0,1-.447,1-1Z"/></svg>
                <span class="text-lg ml-3 font-bold">{{ __('Basic Info') }}</span>
            </div>
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-8 w-full mb-4">
                <div class="flex flex-col">
                    <x-app.input.label id="vehicle" class="mb-1">{{ __('Vehicle') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select2 name="vehicle" id="vehicle" :hasError="$errors->has('vehicle')" placeholder="{{ __('Select a vehicle') }}">
                        <option value="">{{ __('Select a vehicle') }}</option>
                        @foreach ($vehicles as $vehicle)
                            <option value="{{ $vehicle->id }}" @selected(old('vehicle', isset($service) ? $service->vehicle_id : null) == $vehicle->id)>{{ $vehicle->plate_number }}</option>
                        @endforeach
                    </x-app.input.select2>
                    <x-input-error :messages="$errors->get('vehicle')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="insurance_date" class="mb-1">{{ __('Insurance Date') }}</x-app.input.label>
                    <x-app.input.input name="insurance_date" id="insurance_date" :hasError="$errors->has('insurance_date')" value="{{ old('insurance_date', isset($service) ? $service->insurance_date : null) }}" />
                    <x-input-error :messages="$errors->get('insurance_date')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="insurance_reminder" class="mb-1">{{ __('Insurance Reminder') }}</x-app.input.label>
                    <x-app.input.input name="insurance_reminder" id="insurance_reminder" :hasError="$errors->has('insurance_reminder')" value="{{ old('insurance_reminder', isset($service) ? $service->insurance_remind_at : null) }}" />
                    <x-input-error :messages="$errors->get('insurance_reminder')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="insurance_amount" class="mb-1">{{ __('Insurance Amount') }}</x-app.input.label>
                    <x-app.input.input name="insurance_amount" id="insurance_amount" :hasError="$errors->has('insurance_amount')" value="{{ old('insurance_amount', isset($service) ? $service->insurance_amount : null) }}" class="decimal-input" />
                    <x-input-error :messages="$errors->get('insurance_amount')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="roadtax_date" class="mb-1">{{ __('Roadtax Date') }}</x-app.input.label>
                    <x-app.input.input name="roadtax_date" id="roadtax_date" :hasError="$errors->has('roadtax_date')" value="{{ old('roadtax_date', isset($service) ? $service->roadtax_date : null) }}" />
                    <x-input-error :messages="$errors->get('roadtax_date')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="roadtax_reminder" class="mb-1">{{ __('Roadtax Reminder') }}</x-app.input.label>
                    <x-app.input.input name="roadtax_reminder" id="roadtax_reminder" :hasError="$errors->has('roadtax_reminder')" value="{{ old('roadtax_reminder', isset($service) ? $service->roadtax_remind_at : null) }}" />
                    <x-input-error :messages="$errors->get('roadtax_reminder')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="roadtax_amount" class="mb-1">{{ __('Roadtax Amount') }}</x-app.input.label>
                    <x-app.input.input name="roadtax_amount" id="roadtax_amount" :hasError="$errors->has('roadtax_amount')" value="{{ old('roadtax_amount', isset($service) ? $service->roadtax_amount : null) }}" class="decimal-input" />
                    <x-input-error :messages="$errors->get('roadtax_amount')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="inspection_date" class="mb-1">{{ __('Inspection Date') }}</x-app.input.label>
                    <x-app.input.input name="inspection_date" id="inspection_date" :hasError="$errors->has('inspection_date')" value="{{ old('inspection_date', isset($service) ? $service->inspection_date : null) }}" />
                    <x-input-error :messages="$errors->get('inspection_date')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="inspection_reminder" class="mb-1">{{ __('Inspection Reminder') }}</x-app.input.label>
                    <x-app.input.input name="inspection_reminder" id="inspection_reminder" :hasError="$errors->has('inspection_reminder')" value="{{ old('inspection_reminder', isset($service) ? $service->inspection_remind_at : null) }}" />
                    <x-input-error :messages="$errors->get('inspection_reminder')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="mileage_reminder" class="mb-1">{{ __('Mileage Reminder') }}</x-app.input.label>
                    <x-app.input.input name="mileage_reminder" id="mileage_reminder" :hasError="$errors->has('mileage_reminder')" value="{{ old('mileage_reminder', isset($service) ? $service->mileage_remind_at : null) }}" />
                    <x-input-error :messages="$errors->get('mileage_reminder')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="petrol" class="mb-1">{{ __('Petrol') }}</x-app.input.label>
                    <x-app.input.input name="petrol" id="petrol" :hasError="$errors->has('petrol')" value="{{ old('petrol', isset($service) ? $service->petrol : null) }}" class="decimal-input" />
                    <x-input-error :messages="$errors->get('petrol')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="toll" class="mb-1">{{ __('Toll') }}</x-app.input.label>
                    <x-app.input.input name="toll" id="toll" :hasError="$errors->has('mileage_reminder')" value="{{ old('toll', isset($service) ? $service->toll : null) }}" class="decimal-input" />
                    <x-input-error :messages="$errors->get('toll')" class="mt-1" />
                </div>
            </div>
        </div>
        <!-- Items -->
        <div class="bg-white p-4 rounded-md shadow">
            <div class="flex items-center mb-6 border-l-8 border-yellow-400 px-3 py-1 bg-yellow-50 w-fit">
                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" id="Layer_1" data-name="Layer 1" viewBox="0 0 24 24" width="512" height="512"><path d="M19.5,16c0,.553-.447,1-1,1h-2c-.553,0-1-.447-1-1s.447-1,1-1h2c.553,0,1,.447,1,1Zm4.5-1v5c0,2.206-1.794,4-4,4H4c-2.206,0-4-1.794-4-4v-5c0-2.206,1.794-4,4-4h1V4C5,1.794,6.794,0,9,0h6c2.206,0,4,1.794,4,4v7h1c2.206,0,4,1.794,4,4ZM7,11h10V4c0-1.103-.897-2-2-2h-6c-1.103,0-2,.897-2,2v7Zm-3,11h7V13H4c-1.103,0-2,.897-2,2v5c0,1.103,.897,2,2,2Zm18-7c0-1.103-.897-2-2-2h-7v9h7c1.103,0,2-.897,2-2v-5Zm-14.5,0h-2c-.553,0-1,.447-1,1s.447,1,1,1h2c.553,0,1-.447,1-1s-.447-1-1-1ZM14,5c0-.553-.447-1-1-1h-2c-.553,0-1,.447-1,1s.447,1,1,1h2c.553,0,1-.447,1-1Z"/></svg>
                <span class="text-lg ml-3 font-bold">{{ __('Item Details') }}</span>
            </div>
            <div class="flex items-start gap-8 w-full mb-4 hidden" id="item-template">
                <div class="flex flex-col flex-1">
                    <x-app.input.label id="name" class="mb-1">{{ __('Name') }}</x-app.input.label>
                    <x-app.input.input name="name[]" id="name" :hasError="$errors->has('name')" />
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>
                <div class="flex flex-col flex-1">
                    <x-app.input.label id="amount" class="mb-1">{{ __('Amount') }}</x-app.input.label>
                    <x-app.input.input name="amount[]" id="amount" :hasError="$errors->has('amount')" class="decimal-input" />
                    <x-input-error :messages="$errors->get('amount')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <button type="button" class="bg-red-500 rounded-full aspect-square w-7 flex items-center justify-center remove-item-btns">
                        <svg class="h-4 w-4 fill-white" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512" height="512"><path d="M21,4H17.9A5.009,5.009,0,0,0,13,0H11A5.009,5.009,0,0,0,6.1,4H3A1,1,0,0,0,3,6H4V19a5.006,5.006,0,0,0,5,5h6a5.006,5.006,0,0,0,5-5V6h1a1,1,0,0,0,0-2ZM11,2h2a3.006,3.006,0,0,1,2.829,2H8.171A3.006,3.006,0,0,1,11,2Zm7,17a3,3,0,0,1-3,3H9a3,3,0,0,1-3-3V6H18Z"/><path d="M10,18a1,1,0,0,0,1-1V11a1,1,0,0,0-2,0v6A1,1,0,0,0,10,18Z"/><path d="M14,18a1,1,0,0,0,1-1V11a1,1,0,0,0-2,0v6A1,1,0,0,0,14,18Z"/></svg>
                    </button>
                </div>
            </div>

            <div id="item-container">
                @if (isset($service) && old('name') == null)
                    <input type="hidden" name="old_val_count" value="{{ count($service->items) }}">
                    @foreach($service->items as $key => $item)
                        <div class="flex items-start gap-6 w-full mb-4 items" data-id="{{ $key +1 }}">
                            <div class="flex flex-col flex-1">
                                <x-app.input.label id="name" class="mb-1">{{ __('Name') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                                <x-app.input.input name="name[]" id="name" :hasError="$errors->has('name')" value="{{ $item->name }}" />
                                <x-input-error :messages="$errors->first('name.'.$key)" class="mt-1" />
                            </div>
                            <div class="flex flex-col flex-1">
                                <x-app.input.label id="amount" class="mb-1">{{ __('Amount') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                                <x-app.input.input name="amount[]" id="amount" value="{{ $item->amount }}" class="decimal-input" />
                                <x-input-error :messages="$errors->first('amount.'.$key)" class="mt-1" />
                            </div>
                            <div class="flex flex-col">
                                <button type="button" class="bg-red-500 rounded-full aspect-square w-7 flex items-center justify-center remove-item-btns" data-id="{{ $key + 1 }}">
                                    <svg class="h-4 w-4 fill-white" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512" height="512"><path d="M21,4H17.9A5.009,5.009,0,0,0,13,0H11A5.009,5.009,0,0,0,6.1,4H3A1,1,0,0,0,3,6H4V19a5.006,5.006,0,0,0,5,5h6a5.006,5.006,0,0,0,5-5V6h1a1,1,0,0,0,0-2ZM11,2h2a3.006,3.006,0,0,1,2.829,2H8.171A3.006,3.006,0,0,1,11,2Zm7,17a3,3,0,0,1-3,3H9a3,3,0,0,1-3-3V6H18Z"/><path d="M10,18a1,1,0,0,0,1-1V11a1,1,0,0,0-2,0v6A1,1,0,0,0,10,18Z"/><path d="M14,18a1,1,0,0,0,1-1V11a1,1,0,0,0-2,0v6A1,1,0,0,0,14,18Z"/></svg>
                                </button>
                            </div>
                        </div>
                    @endforeach
                @endif

                @if (old('name') != null)
                    <input type="hidden" name="old_val_count" value="{{ count(old('name')) }}">
                    @foreach(old('name') as $key => $old_item)
                        <div class="flex items-start gap-6 w-full mb-4 items" data-id="{{ $key +1 }}">
                            <div class="flex flex-col flex-1">
                                <x-app.input.label id="name" class="mb-1">{{ __('Name') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                                <x-app.input.input name="name[]" id="name" :hasError="$errors->has('name')" value="{{ old('name.'.$key) ?? null }}" />
                                <x-input-error :messages="$errors->first('name.'.$key)" class="mt-1" />
                            </div>
                            <div class="flex flex-col flex-1">
                                <x-app.input.label id="amount" class="mb-1">{{ __('Amount') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                                <x-app.input.input name="amount[]" id="amount" value="{{ old('amount.'.$key) ?? null }}" class="decimal-input" />
                                <x-input-error :messages="$errors->first('amount.'.$key)" class="mt-1" />
                            </div>
                            <div class="flex flex-col">
                                <button type="button" class="bg-red-500 rounded-full aspect-square w-7 flex items-center justify-center remove-item-btns" data-id="{{ $key + 1 }}">
                                    <svg class="h-4 w-4 fill-white" xmlns="http://www.w3.org/2000/svg" id="Outline" viewBox="0 0 24 24" width="512" height="512"><path d="M21,4H17.9A5.009,5.009,0,0,0,13,0H11A5.009,5.009,0,0,0,6.1,4H3A1,1,0,0,0,3,6H4V19a5.006,5.006,0,0,0,5,5h6a5.006,5.006,0,0,0,5-5V6h1a1,1,0,0,0,0-2ZM11,2h2a3.006,3.006,0,0,1,2.829,2H8.171A3.006,3.006,0,0,1,11,2Zm7,17a3,3,0,0,1-3,3H9a3,3,0,0,1-3-3V6H18Z"/><path d="M10,18a1,1,0,0,0,1-1V11a1,1,0,0,0-2,0v6A1,1,0,0,0,10,18Z"/><path d="M14,18a1,1,0,0,0,1-1V11a1,1,0,0,0-2,0v6A1,1,0,0,0,14,18Z"/></svg>
                                </button>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
            <!-- Add Item -->
            <div class="flex justify-end mt-8">
                <button type="button" class="bg-yellow-400 rounded-md py-1.5 px-3 flex items-center gap-x-2 transition duration-300 hover:bg-yellow-300 hover:shadow" id="add-item-btn">
                    <svg class="h-3 w-3" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Capa_1" x="0px" y="0px" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve" width="512" height="512">
                        <path d="M480,224H288V32c0-17.673-14.327-32-32-32s-32,14.327-32,32v192H32c-17.673,0-32,14.327-32,32s14.327,32,32,32h192v192   c0,17.673,14.327,32,32,32s32-14.327,32-32V288h192c17.673,0,32-14.327,32-32S497.673,224,480,224z"/>
                    </svg>
                    <span class="text-sm">{{ __('Add Item') }}</span>
                </button>
            </div>

        </div>
        <div class="mt-8 flex justify-end gap-x-4">
            @if (!isset($service))
                <x-app.button.submit id="submit-create-btn">{{ __('Save and Create') }}</x-app.button.submit>
            @endif
            <x-app.button.submit>{{ __('Save and Update') }}</x-app.button.submit>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        ITEM_ID = 1

        $('input[name="insurance_date"]').daterangepicker(datepickerParam)
        $('input[name="insurance_date"]').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD'));
        });
        $('input[name="insurance_reminder"]').daterangepicker(datepickerParam)
        $('input[name="insurance_reminder"]').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD'));
        });
        $('input[name="roadtax_date"]').daterangepicker(datepickerParam)
        $('input[name="roadtax_date"]').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD'));
        });
        $('input[name="roadtax_reminder"]').daterangepicker(datepickerParam)
        $('input[name="roadtax_reminder"]').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD'));
        });
        $('input[name="inspection_date"]').daterangepicker(datepickerParam)
        $('input[name="inspection_date"]').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD'));
        });
        $('input[name="inspection_reminder"]').daterangepicker(datepickerParam)
        $('input[name="inspection_reminder"]').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD'));
        });
        $('input[name="insurance_reminder"]').daterangepicker(datepickerParam)
        $('input[name="insurance_reminder"]').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD'));
        });
        $('input[name="mileage_reminder"]').daterangepicker(datepickerParam)
        $('input[name="mileage_reminder"]').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD'));
        });

        $(document).ready(function() {
            if ($('input[name="old_val_count"]').val() <= 0) {
                $('#add-item-btn').click()
            } else {
                ITEM_ID = $('input[name="old_val_count"]').val() + 1
            }
        })

        $('#add-item-btn').on('click', function() {
            let clone = $('#item-template')[0].cloneNode(true);
            $(clone).removeClass('hidden')
            $(clone).addClass('items')
            $(clone).attr('data-id', ITEM_ID)
            $(clone).find('.remove-item-btns').attr('data-id', ITEM_ID)

            $('#item-container').append(clone)

            ITEM_ID++
        })
        $('body').on('click', '.remove-item-btns', function() {
            let id = $(this).data('id')

            $(`.items[data-id="${id}"]`).remove()
        })
        $('form').one('submit', function() {
            $('#item-template').remove()

            $(this).submit()
        })
    </script>
@endpush
