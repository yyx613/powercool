@extends('layouts.app')
@section('title', 'Service Form')

@section('content')
    <div class="mb-6 flex justify-between items-center">
        <x-app.page-title url="{{ route('service_form.index') }}">{{ isset($service_form) ? __('Edit Service Form') : __('Create Service Form') }}</x-app.page-title>
    </div>
    @include('components.app.alert.parent')
    <form action="{{ isset($service_form) ? route('service_form.upsert', ['service_form' => \Illuminate\Support\Facades\Crypt::encrypt($service_form->id)]) : route('service_form.upsert') }}" method="POST" enctype="multipart/form-data" id="form">
        @csrf
        <div class="bg-white p-4 rounded-md shadow" id="content-container">
            {{-- Basic Information --}}
            <div class="flex items-center mb-6 border-l-8 border-yellow-400 px-3 py-1 bg-yellow-50 w-fit">
                <span class="text-lg font-bold">{{ __('Basic Information') }}</span>
            </div>
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-8 w-full mb-8">
                <div class="flex flex-col">
                    <x-app.input.label id="sku" class="mb-1">{{ __('Ref No') }}</x-app.input.label>
                    <x-app.input.input name="sku" id="sku" value="{{ isset($service_form) ? $service_form->sku : $sku }}" :disabled="true" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="date" class="mb-1">{{ __('Date') }}</x-app.input.label>
                    <x-app.input.input name="date" id="date" value="{{ old('date', isset($service_form) && $service_form->date ? $service_form->date->format('Y-m-d') : null) }}" />
                    <x-input-error :messages="$errors->get('date')" class="mt-1" />
                </div>
            </div>

            {{-- Customer Information --}}
            <div class="flex items-center mb-6 border-l-8 border-yellow-400 px-3 py-1 bg-yellow-50 w-fit">
                <span class="text-lg font-bold">{{ __('Customer Information') }}</span>
            </div>
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-8 w-full mb-8">
                <div class="flex flex-col">
                    <x-app.input.label id="customer_id" class="mb-1">{{ __('Customer') }}</x-app.input.label>
                    <div class="relative">
                        <x-app.input.select name="customer_id" id="customer_id" placeholder="{{ __('Search a customer') }}">
                            <option value="">{{ __('Search a customer') }}</option>
                            @if (isset($service_form) && $service_form->customer)
                                <option value="{{ $service_form->customer->id }}" selected>{{ $service_form->customer->company_name ?? $service_form->customer->name }}</option>
                            @endif
                        </x-app.input.select>
                    </div>
                    <x-input-error :messages="$errors->get('customer_id')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="customer_location_id" class="mb-1">{{ __('Address') }}</x-app.input.label>
                    <x-app.input.select name="customer_location_id" id="customer_location_id">
                        <option value="">{{ __('Select an address') }}</option>
                    </x-app.input.select>
                    <x-input-error :messages="$errors->get('customer_location_id')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="contact_person" class="mb-1">{{ __('Contact Person') }}</x-app.input.label>
                    <x-app.input.input name="contact_person" id="contact_person" value="{{ old('contact_person', isset($service_form) ? $service_form->contact_person : null) }}" />
                    <x-input-error :messages="$errors->get('contact_person')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="contact_no" class="mb-1">{{ __('Contact No') }}</x-app.input.label>
                    <x-app.input.input name="contact_no" id="contact_no" value="{{ old('contact_no', isset($service_form) ? $service_form->contact_no : null) }}" />
                    <x-input-error :messages="$errors->get('contact_no')" class="mt-1" />
                </div>
            </div>

            {{-- Invoice Information --}}
            <div class="flex items-center mb-6 border-l-8 border-yellow-400 px-3 py-1 bg-yellow-50 w-fit">
                <span class="text-lg font-bold">{{ __('Invoice Information') }}</span>
            </div>
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-8 w-full mb-8">
                <div class="flex flex-col">
                    <x-app.input.label id="invoice_id" class="mb-1">{{ __('Invoice') }}</x-app.input.label>
                    <div class="relative">
                        <x-app.input.select name="invoice_id" id="invoice_id" placeholder="{{ __('Search an invoice (optional)') }}">
                            <option value="">{{ __('Search an invoice (optional)') }}</option>
                            @if (isset($service_form) && $service_form->invoice)
                                <option value="{{ $service_form->invoice->id }}" selected>{{ $service_form->invoice->sku }}</option>
                            @endif
                        </x-app.input.select>
                    </div>
                    <x-input-error :messages="$errors->get('invoice_id')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="invoice_no" class="mb-1">{{ __('Invoice No') }}</x-app.input.label>
                    <x-app.input.input name="invoice_no" id="invoice_no" value="{{ old('invoice_no', isset($service_form) ? $service_form->invoice_no : null) }}" />
                    <x-input-error :messages="$errors->get('invoice_no')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="invoice_date" class="mb-1">{{ __('Invoice Date') }}</x-app.input.label>
                    <x-app.input.input name="invoice_date" id="invoice_date" value="{{ old('invoice_date', isset($service_form) && $service_form->invoice_date ? $service_form->invoice_date->format('Y-m-d') : null) }}" />
                    <x-input-error :messages="$errors->get('invoice_date')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="warranty_status" class="mb-1">{{ __('Warranty Status') }}</x-app.input.label>
                    <x-app.input.select name="warranty_status" id="warranty_status">
                        <option value="">{{ __('Select warranty status') }}</option>
                        @foreach ($warranty_statuses as $key => $label)
                            <option value="{{ $key }}" @selected(old('warranty_status', isset($service_form) ? $service_form->warranty_status : null) == $key)>{{ __($label) }}</option>
                        @endforeach
                    </x-app.input.select>
                    <x-input-error :messages="$errors->get('warranty_status')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="warranty_period_id" class="mb-1">{{ __('Warranty Period') }}</x-app.input.label>
                    <x-app.input.select name="warranty_period_id" id="warranty_period_id">
                        <option value="">{{ __('Select warranty period') }}</option>
                        @foreach ($warranty_periods as $wp)
                            <option value="{{ $wp->id }}" @selected(old('warranty_period_id', isset($service_form) ? $service_form->warranty_period_id : null) == $wp->id)>{{ $wp->name }}</option>
                        @endforeach
                    </x-app.input.select>
                    <x-input-error :messages="$errors->get('warranty_period_id')" class="mt-1" />
                </div>
            </div>

            {{-- Product Information --}}
            <div class="flex items-center mb-6 border-l-8 border-yellow-400 px-3 py-1 bg-yellow-50 w-fit">
                <span class="text-lg font-bold">{{ __('Product Information') }}</span>
            </div>
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-8 w-full mb-8">
                <div class="flex flex-col">
                    <x-app.input.label id="product_id" class="mb-1">{{ __('Product') }}</x-app.input.label>
                    <div class="relative">
                        <x-app.input.select name="product_id" id="product_id" placeholder="{{ __('Search a product') }}">
                            <option value="">{{ __('Search a product') }}</option>
                            @if (isset($service_form) && $service_form->product)
                                <option value="{{ $service_form->product->id }}" selected>{{ $service_form->product->sku }} - {{ $service_form->product->model_desc }}</option>
                            @endif
                        </x-app.input.select>
                    </div>
                    <x-input-error :messages="$errors->get('product_id')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="model_no" class="mb-1">{{ __('Model No') }}</x-app.input.label>
                    <x-app.input.input name="model_no" id="model_no" value="{{ old('model_no', isset($service_form) ? $service_form->model_no : null) }}" />
                    <x-input-error :messages="$errors->get('model_no')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="serial_no" class="mb-1">{{ __('Serial No') }}</x-app.input.label>
                    <x-app.input.select name="serial_no" id="serial_no">
                        <option value="">{{ __('Select a serial no') }}</option>
                        @if (isset($service_form) && $service_form->serial_no)
                            <option value="{{ $service_form->serial_no }}" selected>{{ $service_form->serial_no }}</option>
                        @endif
                    </x-app.input.select>
                    <x-input-error :messages="$errors->get('serial_no')" class="mt-1" />
                </div>
            </div>

            {{-- Dealer Information --}}
            <div class="flex items-center mb-6 border-l-8 border-yellow-400 px-3 py-1 bg-yellow-50 w-fit">
                <span class="text-lg font-bold">{{ __('Dealer Information') }}</span>
            </div>
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-8 w-full mb-8">
                <div class="flex flex-col">
                    <x-app.input.label id="dealer_id" class="mb-1">{{ __('Dealer') }}</x-app.input.label>
                    <div class="relative">
                        <x-app.input.select name="dealer_id" id="dealer_id" placeholder="{{ __('Search a dealer') }}">
                            <option value="">{{ __('Search a dealer') }}</option>
                            @if (isset($service_form) && $service_form->dealer)
                                <option value="{{ $service_form->dealer->id }}" selected>{{ $service_form->dealer->sku }} - {{ $service_form->dealer->name }}</option>
                            @endif
                        </x-app.input.select>
                    </div>
                    <x-input-error :messages="$errors->get('dealer_id')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="dealer_name" class="mb-1">{{ __("Dealer's Name") }}</x-app.input.label>
                    <x-app.input.input name="dealer_name" id="dealer_name" value="{{ old('dealer_name', isset($service_form) ? $service_form->dealer_name : null) }}" />
                    <x-input-error :messages="$errors->get('dealer_name')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="dealer_contact_no" class="mb-1">{{ __("Dealer's Contact No") }}</x-app.input.label>
                    <x-app.input.input name="dealer_contact_no" id="dealer_contact_no" value="{{ old('dealer_contact_no', isset($service_form) ? $service_form->dealer_contact_no : null) }}" />
                    <x-input-error :messages="$errors->get('dealer_contact_no')" class="mt-1" />
                </div>
            </div>

            {{-- Service Details --}}
            <div class="flex items-center mb-6 border-l-8 border-yellow-400 px-3 py-1 bg-yellow-50 w-fit">
                <span class="text-lg font-bold">{{ __('Service Details') }}</span>
            </div>
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-8 w-full mb-8">
                <div class="flex flex-col col-span-2 lg:col-span-3">
                    <x-app.input.label id="nature_of_problem" class="mb-1">{{ __('Nature of Problem') }}</x-app.input.label>
                    <textarea name="nature_of_problem" id="nature_of_problem" rows="4" class="border border-gray-300 rounded-md p-2 focus:outline-none">{{ old('nature_of_problem', isset($service_form) ? $service_form->nature_of_problem : null) }}</textarea>
                    <x-input-error :messages="$errors->get('nature_of_problem')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="date_to_attend" class="mb-1">{{ __('Date to Attend') }}</x-app.input.label>
                    <x-app.input.input name="date_to_attend" id="date_to_attend" value="{{ old('date_to_attend', isset($service_form) && $service_form->date_to_attend ? $service_form->date_to_attend->format('Y-m-d') : null) }}" />
                    <x-input-error :messages="$errors->get('date_to_attend')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="technician_id" class="mb-1">{{ __('Technician') }}</x-app.input.label>
                    <div class="relative">
                        <x-app.input.select name="technician_id" id="technician_id">
                            <option value="">{{ __('Select a technician') }}</option>
                            @foreach ($technicians as $technician)
                                <option value="{{ $technician->id }}" @selected(old('technician_id', isset($service_form) ? $service_form->technician_id : null) == $technician->id)>{{ $technician->name }}</option>
                            @endforeach
                        </x-app.input.select>
                    </div>
                    <x-input-error :messages="$errors->get('technician_id')" class="mt-1" />
                </div>
            </div>

            {{-- Report Checklist --}}
            <div class="flex items-center mb-6 border-l-8 border-yellow-400 px-3 py-1 bg-yellow-50 w-fit">
                <span class="text-lg font-bold">{{ __('Report Checklist') }}</span>
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 w-full mb-8">
                @foreach ($checklist_items as $key => $label)
                    @php
                        // Handle both old format (boolean) and new format (array with checked/remark)
                        $checklistData = isset($service_form) && $service_form->report_checklist && isset($service_form->report_checklist[$key])
                            ? $service_form->report_checklist[$key]
                            : null;
                        $isChecked = false;
                        $remarkValue = '';
                        if ($checklistData !== null) {
                            if (is_array($checklistData)) {
                                $isChecked = $checklistData['checked'] ?? false;
                                $remarkValue = $checklistData['remark'] ?? '';
                            } else {
                                // Old format: boolean value
                                $isChecked = (bool) $checklistData;
                            }
                        }
                    @endphp
                    <div class="flex flex-col gap-2">
                        <div class="flex items-center">
                            <input type="checkbox" name="checklist_{{ $key }}" id="checklist_{{ $key }}"
                                class="w-4 h-4 border-gray-300 rounded"
                                @checked(old('checklist_' . $key, $isChecked))>
                            <label for="checklist_{{ $key }}" class="ml-2 text-sm text-gray-700">{{ __($label) }}</label>
                        </div>
                        <input type="text" name="checklist_{{ $key }}_remark"
                            class="w-full border border-gray-300 rounded-md px-2 py-1 text-sm focus:outline-none"
                            placeholder="{{ __('Remark') }}"
                            value="{{ old('checklist_' . $key . '_remark', $remarkValue) }}">
                    </div>
                @endforeach
            </div>

            {{-- Quotation Line Items --}}
            <div class="flex items-center mb-6 border-l-8 border-yellow-400 px-3 py-1 bg-yellow-50 w-fit">
                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="512" height="512">
                    <path d="M19.5,16c0,.553-.447,1-1,1h-2c-.553,0-1-.447-1-1s.447-1,1-1h2c.553,0,1,.447,1,1Zm4.5-1v5c0,2.206-1.794,4-4,4H4c-2.206,0-4-1.794-4-4v-5c0-2.206,1.794-4,4-4h1V4C5,1.794,6.794,0,9,0h6c2.206,0,4,1.794,4,4v7h1c2.206,0,4,1.794,4,4ZM7,11h10V4c0-1.103-.897-2-2-2h-6c-1.103,0-2,.897-2,2v7Zm-3,11h7V13H4c-1.103,0-2,.897-2,2v5c0,1.103,.897,2,2,2Zm18-7c0-1.103-.897-2-2-2h-7v9h7c1.103,0,2-.897,2-2v-5Zm-14.5,0h-2c-.553,0-1,.447-1,1s.447,1,1,1h2c.553,0,1-.447,1-1s-.447-1-1-1ZM14,5c0-.553-.447-1-1-1h-2c-.553,0-1,.447-1,1s.447,1,1,1h2c.553,0,1-.447,1-1Z"/>
                </svg>
                <span class="text-lg ml-3 font-bold">{{ __('Quotation/Cash Sale/Invoice Line Items') }}</span>
            </div>

            {{-- Hidden Item Template --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 lg:gap-8 w-full mb-8 p-4 rounded-md relative group hidden transition duration-300 hover:bg-slate-50" id="item-template">
                {{-- Delete Button --}}
                <button type="button" class="bg-rose-400 p-2 rounded-full absolute top-[-5px] right-[-5px] hidden group-hover:block delete-item-btns" title="{{ __('Delete Item') }}">
                    <svg class="h-3 w-3 fill-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="512" height="512">
                        <path d="M13.93,12L21.666,2.443c.521-.644,.422-1.588-.223-2.109-.645-.522-1.588-.421-2.109,.223l-7.334,9.06L4.666,.557c-1.241-1.519-3.56,.357-2.332,1.887l7.736,9.557L2.334,21.557c-.521,.644-.422,1.588,.223,2.109,.64,.519,1.586,.424,2.109-.223l7.334-9.06,7.334,9.06c.524,.647,1.47,.742,2.109,.223,.645-.521,.744-1.466,.223-2.109l-7.736-9.557Z"/>
                    </svg>
                </button>

                {{-- Move Buttons Row --}}
                <div class="col-span-4 flex items-center gap-4">
                    <div class="flex gap-2">
                        <button type="button" class="text-sm p-1 rounded-full bg-slate-200 move-down-btn" title="{{ __('Move Down') }}">
                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="512" height="512">
                                <path d="M17.71,12.71a1,1,0,0,0-1.42,0L13,16V6a1,1,0,0,0-2,0V16L7.71,12.71a1,1,0,0,0-1.42,0,1,1,0,0,0,0,1.41l4.3,4.29A2,2,0,0,0,12,19h0a2,2,0,0,0,1.4-.59l4.3-4.29A1,1,0,0,0,17.71,12.71Z"/>
                            </svg>
                        </button>
                        <button type="button" class="text-sm p-1 rounded-full bg-slate-200 move-up-btn" title="{{ __('Move Up') }}">
                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="512" height="512">
                                <g>
                                    <path d="M17.707,9.879,13.414,5.586a2,2,0,0,0-2.828,0L6.293,9.879l1.414,1.414L11,8V19h2V8l3.293,3.293Z"/>
                                </g>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Product Select --}}
                <div class="flex flex-col">
                    <x-app.input.label class="mb-1">{{ __('Product') }}</x-app.input.label>
                    <x-app.input.select name="line_product_id[]">
                        <option value=""></option>
                    </x-app.input.select>
                </div>

                {{-- Quantity with FOC button --}}
                <div class="flex flex-col">
                    <x-app.input.label class="mb-1">{{ __('Quantity') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <div class="flex border border-gray-300 rounded-md overflow-hidden">
                        <x-app.input.input name="line_qty[]" class="int-input border-none flex-1" value="1" />
                        <button type="button" class="foc-btns font-semibold text-sm px-1.5 border-l border-gray-300 data-[is-foc=false]:bg-slate-100 data-[is-foc=true]:bg-emerald-100" data-is-foc="false">FOC</button>
                    </div>
                    <input type="hidden" name="line_is_foc[]" value="0" class="line-is-foc-input" />
                </div>

                {{-- Selling Price Dropdown --}}
                <div class="flex flex-col selling-price-container">
                    <x-app.input.label class="mb-1">{{ __('Selling Price') }} <span class="text-xs mt-1 hidden" id="price-hint">(<span id="min_price"></span> - <span id="max_price"></span>)</span></x-app.input.label>
                    <x-app.input.select name="line_selling_price[]">
                        <option value="">{{ __('Select a selling price') }}</option>
                    </x-app.input.select>
                </div>

                {{-- Override Selling Price --}}
                <div class="flex flex-col">
                    <x-app.input.label class="mb-1">{{ __('Override Selling Price') }}</x-app.input.label>
                    <x-app.input.input name="line_override_selling_price[]" class="decimal-input" />
                </div>
                <input type="hidden" name="line_unit_price[]" value="">

                {{-- Amount (calculated, disabled) --}}
                <div class="flex flex-col">
                    <x-app.input.label class="mb-1">{{ __('Amount') }}</x-app.input.label>
                    <x-app.input.input name="line_amount[]" disabled="true" />
                </div>

                {{-- SST with toggle button --}}
                <div class="flex flex-col">
                    <x-app.input.label class="mb-1">{{ __('SST') }} ({{ $sst ?? 0 }}%)</x-app.input.label>
                    <div class="flex border border-gray-300 rounded-md overflow-hidden">
                        <x-app.input.input name="line_sst[]" disabled="true" class="border-none flex-1" />
                        <button type="button" class="sst-btns font-semibold text-sm px-1.5 border-l border-gray-300 data-[with-sst=false]:bg-slate-100 data-[with-sst=true]:bg-emerald-100" data-with-sst="false">SST</button>
                    </div>
                    <input type="hidden" name="line_with_sst[]" value="0" class="line-with-sst-input" />
                </div>

                {{-- UOM (auto-populated) --}}
                <div class="flex flex-col">
                    <x-app.input.label class="mb-1">{{ __('UOM') }}</x-app.input.label>
                    <x-app.input.input name="line_uom[]" disabled="true" />
                </div>

                {{-- Product Description --}}
                <div class="flex flex-col">
                    <x-app.input.label class="mb-1">{{ __('Product Description') }}</x-app.input.label>
                    <x-app.input.input name="line_custom_desc[]" />
                </div>

                {{-- Discount --}}
                <div class="flex flex-col">
                    <x-app.input.label class="mb-1">{{ __('Discount') }} <span class="text-xs text-red-400 font-semibold mt-1 hidden line-discount-hint"></span></x-app.input.label>
                    <x-app.input.input name="line_discount[]" class="decimal-input" value="0" />
                </div>

                {{-- Warranty Period --}}
                <div class="flex flex-col">
                    <x-app.input.label class="mb-1">{{ __('Warranty Period') }}</x-app.input.label>
                    <x-app.input.select name="line_warranty_period[0][]" multiple>
                        <option value=""></option>
                    </x-app.input.select>
                </div>

                {{-- Remark (full width) --}}
                <div class="flex flex-col col-span-2 md:col-span-4 line-remark-container">
                    <x-app.input.label class="mb-1">{{ __('Remark') }}</x-app.input.label>
                    <textarea name="line_remark[]" class="hidden line-remark-textarea"></textarea>
                    <div class="quill-wrapper rounded-md border border-gray-300 bg-white">
                        <div class="line-remark-editor"></div>
                    </div>
                </div>
            </div>

            {{-- Items Container --}}
            <div id="items-container" class="mb-4"></div>

            {{-- Add Item Button --}}
            <div class="flex justify-end px-4 mb-8">
                <button type="button" class="bg-yellow-400 rounded-md py-1.5 px-3 flex items-center gap-x-2 transition duration-300 hover:bg-yellow-300 hover:shadow" id="add-item-btn">
                    <svg class="h-3 w-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="512" height="512">
                        <path d="M480,224H288V32c0-17.673-14.327-32-32-32s-32,14.327-32,32v192H32c-17.673,0-32,14.327-32,32s14.327,32,32,32h192v192c0,17.673,14.327,32,32,32s32-14.327,32-32V288h192c17.673,0,32-14.327,32-32S497.673,224,480,224z"/>
                    </svg>
                    <span class="text-sm">{{ __('Add Item') }}</span>
                </button>
            </div>

            {{-- Totals Summary --}}
            <div class="flex justify-end mt-6 pt-6 border-t px-4 pb-4">
                <table>
                    <tbody>
                        <tr>
                            <td>{{ __('Subtotal') }}</td>
                            <td class="w-4 text-center">:</td>
                            <td id="display-subtotal" class="text-right">0.00</td>
                        </tr>
                        <tr>
                            <td>{{ __('Discount') }}</td>
                            <td class="w-4 text-center">:</td>
                            <td id="display-discount" class="text-right">0.00</td>
                        </tr>
                        <tr>
                            <td>{{ __('Tax') }} @ {{ $sst ?? 0 }}%</td>
                            <td class="w-4 text-center">:</td>
                            <td id="display-tax" class="text-right">0.00</td>
                        </tr>
                        <tr class="font-bold text-lg">
                            <td>{{ __('Total') }}</td>
                            <td class="w-4 text-center">:</td>
                            <td id="display-grand-total" class="text-right">0.00</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- HIDDEN: Quotation Details section
            <div class="flex items-center mb-6 border-l-8 border-yellow-400 px-3 py-1 bg-yellow-50 w-fit">
                <span class="text-lg font-bold">{{ __('Quotation Details') }}</span>
            </div>
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-8 w-full mb-8">
                <div class="flex flex-col">
                    <x-app.input.label id="validity" class="mb-1">{{ __('Validity') }}</x-app.input.label>
                    <x-app.input.input name="validity" id="validity" placeholder="{{ __('e.g., 2 weeks') }}" value="{{ old('validity', isset($service_form) ? $service_form->validity : null) }}" />
                    <x-input-error :messages="$errors->get('validity')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="payment_method_id" class="mb-1">{{ __('Payment Method') }}</x-app.input.label>
                    <x-app.input.select name="payment_method_id" id="payment_method_id">
                        <option value="">{{ __('Select payment method') }}</option>
                        @foreach ($payment_methods as $method)
                            <option value="{{ $method->id }}" @selected(old('payment_method_id', isset($service_form) ? $service_form->payment_method_id : null) == $method->id)>{{ $method->name }}</option>
                        @endforeach
                    </x-app.input.select>
                    <x-input-error :messages="$errors->get('payment_method_id')" class="mt-1" />
                </div>
                <div class="flex flex-col col-span-2 lg:col-span-3" id="quotation-remark-container">
                    <x-app.input.label id="quotation_remark" class="mb-1">{{ __('Quotation Remark') }}</x-app.input.label>
                    <textarea name="quotation_remark" id="quotation_remark" class="hidden">{!! old('quotation_remark', isset($service_form) ? $service_form->quotation_remark : null) !!}</textarea>
                    <x-input-error :messages="$errors->get('quotation_remark')" class="mt-1" />
                </div>
            </div>
            --}}

            <div class="mt-8 flex justify-end gap-x-4">
                @if (!isset($service_form))
                    <x-app.button.submit id="submit-create-btn">{{ __('Save and Create') }}</x-app.button.submit>
                @endif
                <x-app.button.submit>{{ __('Save and Update') }}</x-app.button.submit>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    SERVICE_FORM = @json($service_form ?? null);
    CUSTOMERS = {};
    PRODUCTS = {};
    INVOICES = {};
    DEALERS = {};
    LINE_PRODUCTS = {};
    SST_VALUE = {{ $sst ?? 0 }};
    UOMS = @json($uoms ?? []);
    WARRANTY_PERIODS = @json($warranty_periods ?? []);
    ITEM_INDEX = {{ isset($service_form) && $service_form->products ? count($service_form->products) : 0 }};

    $(document).ready(function() {
        // Date pickers
        $('input[name="date"]').daterangepicker(datepickerParam)
        $('input[name="date"]').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD'));
        });

        $('input[name="invoice_date"]').daterangepicker(datepickerParam)
        $('input[name="invoice_date"]').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD'));
        });

        $('input[name="date_to_attend"]').daterangepicker(datepickerParam)
        $('input[name="date_to_attend"]').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD'));
        });

        // Customer Select2 AJAX
        bulidSelect2Ajax({
            selector: 'select[name="customer_id"]',
            placeholder: '{{ __("Search a customer") }}',
            url: '{{ route("customer.get_by_keyword") }}',
            processResults: function(data) {
                CUSTOMERS = data.customers
                return {
                    results: $.map(data.customers, function(item) {
                        return {
                            id: item.id,
                            text: item.company_name || item.name
                        };
                    })
                }
            }
        })
        $('select[name="customer_id"]').parent().addClass('border border-gray-300 rounded-md overflow-hidden')

        // Product Select2 AJAX
        bulidSelect2Ajax({
            selector: 'select[name="product_id"]',
            placeholder: '{{ __("Search a product") }}',
            url: '{{ route("product.get_by_keyword") }}',
            processResults: function(data) {
                for (const key in data.products) {
                    PRODUCTS[key] = data.products[key];
                }
                return {
                    results: $.map(data.products, function(item) {
                        return {
                            id: item.id,
                            text: `${item.sku} - ${item.model_desc || ''}`
                        };
                    })
                }
            }
        })
        $('select[name="product_id"]').parent().addClass('border border-gray-300 rounded-md overflow-hidden')

        // Invoice Select2 AJAX
        bulidSelect2Ajax({
            selector: 'select[name="invoice_id"]',
            placeholder: '{{ __("Search an invoice (optional)") }}',
            url: '{{ route("service_form.get_invoice_by_keyword") }}',
            processResults: function(data) {
                INVOICES = data.invoices
                return {
                    results: $.map(data.invoices, function(item) {
                        return {
                            id: item.id,
                            text: item.sku
                        };
                    })
                }
            }
        })
        $('select[name="invoice_id"]').parent().addClass('border border-gray-300 rounded-md overflow-hidden')

        // Dealer Select2 AJAX
        bulidSelect2Ajax({
            selector: 'select[name="dealer_id"]',
            placeholder: '{{ __("Search a dealer") }}',
            url: '{{ route("service_form.get_dealer_by_keyword") }}',
            processResults: function(data) {
                DEALERS = data.dealers
                return {
                    results: $.map(data.dealers, function(item) {
                        return {
                            id: item.id,
                            text: `${item.sku} - ${item.name || ''}`
                        };
                    })
                }
            }
        })
        $('select[name="dealer_id"]').parent().addClass('border border-gray-300 rounded-md overflow-hidden')

        // Technician Select2 (static dropdown)
        $('select[name="technician_id"]').select2({
            placeholder: '{{ __("Select a technician") }}'
        })
        $('select[name="technician_id"]').parent().addClass('border border-gray-300 rounded-md overflow-hidden')

        // Customer change - populate contact person and contact no
        $('select[name="customer_id"]').on('change', function() {
            var customerId = $(this).val()
            var customer = CUSTOMERS[customerId]

            if (customer) {
                $('input[name="contact_person"]').val(customer.name || '')
                // Handle mobile_number as array or string
                if (customer.mobile_number) {
                    if (Array.isArray(customer.mobile_number)) {
                        $('input[name="contact_no"]').val(customer.mobile_number.length > 0 ? customer.mobile_number[0] : '')
                    } else {
                        $('input[name="contact_no"]').val(customer.mobile_number)
                    }
                } else {
                    $('input[name="contact_no"]').val(customer.phone || '')
                }

                // Fetch customer locations for address dropdown
                fetchCustomerLocations(customerId)
            }
        })

        // Product change - populate model_no and fetch serial numbers
        $('select[name="product_id"]').on('change', function() {
            var productId = $(this).val()
            var product = PRODUCTS[productId]

            if (product) {
                $('input[name="model_no"]').val(product.model_desc || '')
            }

            // Fetch serial numbers for the selected product
            fetchProductChildren(productId)
        })

        // Invoice change - populate invoice_no and invoice_date
        $('select[name="invoice_id"]').on('change', function() {
            var invoiceId = $(this).val()
            var invoice = INVOICES[invoiceId]

            if (invoice) {
                $('input[name="invoice_no"]').val(invoice.sku || '')
                if (invoice.invoice_date) {
                    $('input[name="invoice_date"]').val(invoice.invoice_date)
                }
            }
        })

        // Dealer change - populate dealer_name
        $('select[name="dealer_id"]').on('change', function() {
            var dealerId = $(this).val()
            var dealer = DEALERS[dealerId]

            if (dealer) {
                $('input[name="dealer_name"]').val(dealer.name || '')
            }
        })

        // If editing, load customer locations
        if (SERVICE_FORM && SERVICE_FORM.customer_id) {
            fetchCustomerLocations(SERVICE_FORM.customer_id, SERVICE_FORM.customer_location_id)
        }

        // If editing, load serial numbers for the selected product
        if (SERVICE_FORM && SERVICE_FORM.product_id) {
            fetchProductChildren(SERVICE_FORM.product_id, SERVICE_FORM.serial_no)
        }

        // Initialize QuillJS for quotation remark
        buildQuotationRemarkQuillEditor();
    })

    function fetchCustomerLocations(customerId, selectedLocationId = null) {
        let url = '{{ route("customer.get_location") }}'
        url = `${url}?customer_id=${customerId}`

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: url,
            type: 'GET',
            success: function(res) {
                $('select[name="customer_location_id"] option').remove()

                // Default option
                let opt = new Option("{!! __('Select an address') !!}", '')
                $('select[name="customer_location_id"]').append(opt)

                for (let i = 0; i < res.locations.length; i++) {
                    const loc = res.locations[i];

                    var addr = loc.address1 || ''
                    if (loc.address2) addr = `${addr}, ${loc.address2}`
                    if (loc.address3) addr = `${addr}, ${loc.address3}`
                    if (loc.address4) addr = `${addr}, ${loc.address4}`

                    let selected = selectedLocationId ? loc.id == selectedLocationId : loc.is_default
                    let opt = new Option(addr, loc.id, false, selected)
                    $('select[name="customer_location_id"]').append(opt)
                }
            },
        });
    }

    function fetchProductChildren(productId, selectedSerialNo = null) {
        // Clear serial_no dropdown
        $('select[name="serial_no"] option').remove()
        let defaultOpt = new Option("{!! __('Select a serial no') !!}", '')
        $('select[name="serial_no"]').append(defaultOpt)

        if (!productId) {
            return
        }

        let url = '{{ route("service_form.get_product_children") }}'
        url = `${url}?product_id=${productId}`

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: url,
            type: 'GET',
            success: function(res) {
                for (let i = 0; i < res.product_children.length; i++) {
                    const pc = res.product_children[i];
                    let selected = selectedSerialNo ? pc.sku == selectedSerialNo : false
                    let opt = new Option(pc.sku, pc.sku, false, selected)
                    $('select[name="serial_no"]').append(opt)
                }
            },
        });
    }

    $('#submit-create-btn').on('click', function(e) {
        let url = $('#form').attr('action')
        url = `${url}?create_again=true`

        $('#form').attr('action', url)
    })

    // Payment Method Select2
    $('select[name="payment_method_id"]').select2({
        placeholder: '{{ __("Select payment method") }}'
    })
    $('select[name="payment_method_id"]').parent().addClass('border border-gray-300 rounded-md overflow-hidden')

    // Quotation Line Items Functions (Grid-based QUO/SO style)
    ITEMS_COUNT = 0;
    LINE_INIT_EDIT = true;
    LINE_REMARK_QUILLS = {};

    // Add item button click handler
    $('#add-item-btn').on('click', function() {
        let clone = $('#item-template')[0].cloneNode(true);

        ITEMS_COUNT++;
        $(clone).attr('data-id', ITEMS_COUNT);
        $(clone).attr('data-sequence', ITEMS_COUNT);
        $(clone).find('.move-down-btn').attr('data-sequence', ITEMS_COUNT);
        $(clone).find('.move-up-btn').attr('data-sequence', ITEMS_COUNT);
        $(clone).find('.delete-item-btns').attr('data-id', ITEMS_COUNT);
        $(clone).find('.foc-btns').attr('data-id', ITEMS_COUNT);
        $(clone).find('.sst-btns').attr('data-id', ITEMS_COUNT);
        $(clone).addClass('line-items');
        $(clone).removeClass('hidden');
        $(clone).removeAttr('id');

        $('#items-container').append(clone);

        // Build product select2 with AJAX search
        bulidSelect2Ajax({
            selector: `.line-items[data-id="${ITEMS_COUNT}"] select[name="line_product_id[]"]`,
            placeholder: '{{ __("Search a product") }}',
            url: '{{ route("product.get_by_keyword") }}',
            processResults: function(data) {
                for (const key in data.products) {
                    const element = data.products[key];
                    if (LINE_PRODUCTS[key] !== undefined) continue;
                    LINE_PRODUCTS[key] = element;
                }
                return {
                    results: $.map(data.products, function(item) {
                        return {
                            id: item.id,
                            text: `${item.sku} - ${item.model_desc}`
                        };
                    })
                }
            }
        });

        // Build selling price select2
        $(`.line-items[data-id="${ITEMS_COUNT}"] select[name="line_selling_price[]"]`).select2({
            placeholder: "{!! __('Select a selling price') !!}"
        });

        // Update warranty period select name and build select2
        $(`.line-items[data-id="${ITEMS_COUNT}"] select[name="line_warranty_period[0][]"]`).attr('name', `line_warranty_period[${ITEMS_COUNT - 1}][]`);
        buildWarrantyPeriodSelect2(ITEMS_COUNT);

        $(`.line-items[data-id="${ITEMS_COUNT}"] .select2`).addClass('border border-gray-300 rounded-md overflow-hidden');

        // Initialize QuillJS for line item remark
        buildLineRemarkQuillEditor(ITEMS_COUNT);

        hideDeleteBtnWhenOnlyOneItem();

        if (LINE_INIT_EDIT == false) {
            $(`.line-items[data-id="${ITEMS_COUNT}"] select[name="line_product_id[]"]`).select2('open');
            sortLineItems();
        }
    });

    // Delete item button handler
    $('body').on('click', '.delete-item-btns', function() {
        let id = $(this).data('id');
        $(`.line-items[data-id="${id}"]`).remove();

        ITEMS_COUNT = 0;
        $('.line-items').each(function(i, obj) {
            ITEMS_COUNT++;
            $(this).attr('data-id', ITEMS_COUNT);
            $(this).find('.delete-item-btns').attr('data-id', ITEMS_COUNT);
        });
        hideDeleteBtnWhenOnlyOneItem();
        calLineSummary();
    });

    // Product selection change handler
    $('body').on('change', 'select[name="line_product_id[]"]', function() {
        let id = $(this).parent().parent().attr('data-id');
        let val = $(this).val();
        let product = LINE_PRODUCTS[val];

        if (product != undefined) {
            $(`.line-items[data-id="${id}"] .sst-btns`).attr('data-with-sst', product.sst === 1);
            $(`.line-items[data-id="${id}"] #min_price`).text(priceFormat(product.min_price));
            $(`.line-items[data-id="${id}"] #max_price`).text(priceFormat(product.max_price));
            $(`.line-items[data-id="${id}"] #price-hint`).removeClass('hidden');

            // Set UOM
            $(`.line-items[data-id="${id}"] input[name="line_uom[]"]`).val(null);
            for (let j = 0; j < UOMS.length; j++) {
                if (UOMS[j].id == product.uom) {
                    $(`.line-items[data-id="${id}"] input[name="line_uom[]"]`).val(UOMS[j].name);
                    break;
                }
            }

            // Set Custom Description from product model_desc
            $(`.line-items[data-id="${id}"] input[name="line_custom_desc[]"]`).val(product.model_desc || '');

            // Append selling prices
            $(`.line-items[data-id="${id}"] select[name="line_selling_price[]"]`).empty();
            $(`.line-items[data-id="${id}"] select[name="line_selling_price[]"]`).append('<option value="">{{ __("Select price") }}</option>');
            for (let j = 0; j < product.selling_prices.length; j++) {
                let opt = new Option(
                    `${product.selling_prices[j].name} (RM ${priceFormat(product.selling_prices[j].price)})`,
                    product.selling_prices[j].id
                );
                $(`.line-items[data-id="${id}"] select[name="line_selling_price[]"]`).append(opt);
            }
        }
    });

    // Selling price change handler
    $('body').on('change', 'select[name="line_selling_price[]"]', function() {
        let idx = $(this).parent().parent().data('id');
        let productId = $(`.line-items[data-id="${idx}"] select[name="line_product_id[]"]`).val();
        let val = $(this).val();
        let product = LINE_PRODUCTS[productId];

        $(`.line-items[data-id="${idx}"] input[name="line_override_selling_price[]"]`).val(null);

        if (product != undefined) {
            for (let j = 0; j < product.selling_prices.length; j++) {
                if (product.selling_prices[j].id == val) {
                    $(`.line-items[data-id="${idx}"] input[name="line_unit_price[]"]`).val(product.selling_prices[j].price);
                    break;
                }
            }
        }
        calLineItemTotal(idx);
    });

    // Override selling price keyup handler
    $('body').on('keyup', 'input[name="line_override_selling_price[]"]', function() {
        let idx = $(this).parent().parent().parent().data('id');
        $(`.line-items[data-id="${idx}"] select[name="line_selling_price[]"]`).val(null);
        $(`.line-items[data-id="${idx}"] input[name="line_unit_price[]"]`).val($(this).val());
        calLineItemTotal(idx);
    });

    // Quantity and discount keyup handlers
    $('body').on('keyup', 'input[name="line_qty[]"], input[name="line_discount[]"]', function() {
        let idx = $(this).parent().parent().parent().data('id');
        calLineItemTotal(idx);
    });

    // FOC button handler
    $('body').on('click', '.foc-btns', function() {
        let isFoc = $(this).attr('data-is-foc');
        let id = $(this).data('id');

        if (isFoc === 'true') {
            $(this).attr('data-is-foc', false);
            $(`.line-items[data-id="${id}"] .line-is-foc-input`).val(0);
            $(`.line-items[data-id="${id}"] select[name="line_selling_price[]"]`).attr('disabled', false);
            $(`.line-items[data-id="${id}"] select[name="line_selling_price[]"]`).attr('aria-disabled', false);
            $(`.line-items[data-id="${id}"] .selling-price-container .select2-container`).removeClass('!bg-[#eeeeee]');
            $(`.line-items[data-id="${id}"] input[name="line_override_selling_price[]"]`).attr('disabled', false);
            $(`.line-items[data-id="${id}"] input[name="line_override_selling_price[]"]`).attr('aria-disabled', false);
            $(`.line-items[data-id="${id}"] input[name="line_override_selling_price[]"]`).parent().attr('aria-disabled', false);
        } else {
            $(this).attr('data-is-foc', true);
            $(`.line-items[data-id="${id}"] .line-is-foc-input`).val(1);
            $(`.line-items[data-id="${id}"] select[name="line_selling_price[]"]`).val(null).trigger('change');
            $(`.line-items[data-id="${id}"] select[name="line_selling_price[]"]`).attr('disabled', true);
            $(`.line-items[data-id="${id}"] select[name="line_selling_price[]"]`).attr('aria-disabled', true);
            $(`.line-items[data-id="${id}"] .selling-price-container .select2-container`).addClass('!bg-[#eeeeee]');
            $(`.line-items[data-id="${id}"] input[name="line_override_selling_price[]"]`).val(null).trigger('keyup');
            $(`.line-items[data-id="${id}"] input[name="line_override_selling_price[]"]`).attr('disabled', true);
            $(`.line-items[data-id="${id}"] input[name="line_override_selling_price[]"]`).attr('aria-disabled', true);
            $(`.line-items[data-id="${id}"] input[name="line_override_selling_price[]"]`).parent().attr('aria-disabled', true);
        }
        calLineItemTotal(id);
    });

    // SST button handler
    $('body').on('click', '.sst-btns', function() {
        let withSST = $(this).attr('data-with-sst');
        let id = $(this).data('id');

        if (withSST === 'true') {
            $(this).attr('data-with-sst', false);
            $(`.line-items[data-id="${id}"] .line-with-sst-input`).val(0);
        } else {
            $(this).attr('data-with-sst', true);
            $(`.line-items[data-id="${id}"] .line-with-sst-input`).val(1);
        }
        calLineItemTax(id);
        calLineSummary();
    });

    // Move down button handler
    $('body').on('click', '.move-down-btn', function() {
        let itemSequence = $(this).data('sequence');
        $(`.line-items[data-sequence=${itemSequence}]`).insertAfter($(`.line-items[data-sequence=${itemSequence+1}]`));
        sortLineItems();
    });

    // Move up button handler
    $('body').on('click', '.move-up-btn', function() {
        let itemSequence = $(this).data('sequence');
        $(`.line-items[data-sequence=${itemSequence}]`).insertBefore($(`.line-items[data-sequence=${itemSequence-1}]`));
        sortLineItems();
    });

    // Calculate individual item total
    function calLineItemTotal(idx) {
        let productId = $(`.line-items[data-id="${idx}"] select[name="line_product_id[]"]`).val();
        let qty = $(`.line-items[data-id="${idx}"] input[name="line_qty[]"]`).val() || 0;
        let sellingPrice = $(`.line-items[data-id="${idx}"] select[name="line_selling_price[]"]`).val();
        let discount = $(`.line-items[data-id="${idx}"] input[name="line_discount[]"]`).val() || 0;
        let overrideSellingPrice = $(`.line-items[data-id="${idx}"] input[name="line_override_selling_price[]"]`).val();
        let isFoc = $(`.line-items[data-id="${idx}"] .foc-btns`).attr('data-is-foc') === 'true';
        let product = LINE_PRODUCTS[productId];

        let unitPrice = 0;
        if (overrideSellingPrice != '' && overrideSellingPrice != null) {
            unitPrice = overrideSellingPrice;
        } else {
            if (product != undefined) {
                for (let j = 0; j < product.selling_prices.length; j++) {
                    if (product.selling_prices[j].id == sellingPrice) {
                        unitPrice = product.selling_prices[j].price;
                        break;
                    }
                }
            }
        }

        // Show/hide discount hint
        if (discount != '' && discount != null && parseFloat(discount) > 0) {
            $(`.line-items[data-id="${idx}"] .line-discount-hint`).text(`( -${priceFormat(discount)} )`);
            $(`.line-items[data-id="${idx}"] .line-discount-hint`).removeClass('hidden');
        } else {
            $(`.line-items[data-id="${idx}"] .line-discount-hint`).addClass('hidden');
        }

        let amount = 0;
        if (!isFoc) {
            amount = (qty * unitPrice) - discount;
            amount = Math.max(0, amount);
        }

        $(`.line-items[data-id="${idx}"] input[name="line_amount[]"]`).val(priceFormat(amount));

        calLineItemTax(idx);
        calLineSummary();
    }

    // Calculate individual item tax
    function calLineItemTax(idx) {
        let enabledSST = $(`.line-items[data-id=${idx}] .sst-btns`).attr('data-with-sst');
        let amount = $(`.line-items[data-id=${idx}] input[name="line_amount[]"]`).val();

        if (enabledSST === 'true' && amount != undefined && amount != '') {
            amount = amount.toString().replaceAll(',', '');
            $(`.line-items[data-id="${idx}"] input[name="line_sst[]"]`).val(priceFormat(amount * SST_VALUE / 100));
        } else if (enabledSST === 'false') {
            $(`.line-items[data-id="${idx}"] input[name="line_sst[]"]`).val(null);
        }
    }

    // Calculate overall summary
    function calLineSummary() {
        let overallSubtotal = 0;
        let overallDiscountAmount = 0;
        let overallTaxAmount = 0;

        $('.line-items').each(function(i, obj) {
            let productId = $(this).find('select[name="line_product_id[]"]').val();
            let qty = $(this).find('input[name="line_qty[]"]').val() || 0;
            let discount = parseFloat($(this).find('input[name="line_discount[]"]').val()) || 0;
            let sellingPrice = $(this).find('select[name="line_selling_price[]"]').val();
            let overrideSellingPrice = $(this).find('input[name="line_override_selling_price[]"]').val();
            let isFoc = $(this).find('.foc-btns').attr('data-is-foc') === 'true';
            let unitPrice = 0;
            let product = LINE_PRODUCTS[productId];

            if (overrideSellingPrice != '' && overrideSellingPrice != null) {
                unitPrice = overrideSellingPrice;
            } else {
                if (product != undefined) {
                    for (let j = 0; j < product.selling_prices.length; j++) {
                        if (product.selling_prices[j].id == sellingPrice) {
                            unitPrice = product.selling_prices[j].price;
                            break;
                        }
                    }
                }
            }

            // Calculate subtotal before discount (qty * unitPrice)
            let lineSubtotal = 0;
            let lineDiscount = 0;
            let lineAfterDiscount = 0;
            if (!isFoc) {
                lineSubtotal = qty * unitPrice;
                lineDiscount = discount;
                lineAfterDiscount = lineSubtotal - lineDiscount;
                lineAfterDiscount = Math.max(0, lineAfterDiscount);
            }

            // Tax is calculated on amount after discount
            let enabledSST = $(this).find('.sst-btns').attr('data-with-sst');
            let taxAmount = 0;
            if (enabledSST === 'true' && !isFoc) {
                taxAmount = lineAfterDiscount * SST_VALUE / 100;
            }

            overallSubtotal += (lineSubtotal * 1);
            overallDiscountAmount += (lineDiscount * 1);
            overallTaxAmount += (taxAmount * 1);
        });

        $('#display-subtotal').text(priceFormat(overallSubtotal));
        $('#display-discount').text(priceFormat(overallDiscountAmount));
        $('#display-tax').text(priceFormat(overallTaxAmount));
        $('#display-grand-total').text(priceFormat(overallSubtotal - overallDiscountAmount + overallTaxAmount));
    }

    // Hide delete button when only one item
    function hideDeleteBtnWhenOnlyOneItem() {
        if ($('.line-items').length == 1) {
            $('.line-items:first .delete-item-btns').removeClass('group-hover:block');
        } else {
            $('.line-items:first .delete-item-btns').addClass('group-hover:block');
        }
    }

    // Sort line items and update sequences
    function sortLineItems() {
        let sequence = 0;
        $('#items-container .line-items').each(function(i, obj) {
            $(this).find('.move-down-btn').removeClass('hidden');
            $(this).find('.move-up-btn').removeClass('hidden');

            if ($('.line-items').length <= 1) {
                $(this).find('.move-down-btn').addClass('hidden');
                $(this).find('.move-up-btn').addClass('hidden');
            } else if (i == 0) {
                $(this).find('.move-up-btn').addClass('hidden');
            } else if (i + 1 == $('.line-items').length) {
                $(this).find('.move-down-btn').addClass('hidden');
            }

            sequence++;
            $(this).attr('data-sequence', sequence);
            $(this).find('.move-down-btn').attr('data-sequence', sequence);
            $(this).find('.move-up-btn').attr('data-sequence', sequence);
        });
    }

    // Build warranty period multi-select
    function buildWarrantyPeriodSelect2(item_id) {
        $(`.line-items[data-id="${item_id}"] select[name^="line_warranty_period"]`).select2({
            placeholder: "{!! __('Select warranty periods') !!}",
            templateSelection: function(data) {
                if (!data.id) {
                    return data.text;
                }

                var $selection = $(
                    '<span class="select2-selection__choice__custom">' +
                        '<button type="button" class="m-1 select2-selection__choice__remove__custom" tabindex="-1" title="Remove">' +
                            '<svg class="h-3 w-3 fill-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">' +
                                '<path d="M13.93,12L21.666,2.443c.521-.644,.422-1.588-.223-2.109-.645-.522-1.588-.421-2.109,.223l-7.334,9.06L4.666,.557c-1.241-1.519-3.56,.357-2.332,1.887l7.736,9.557L2.334,21.557c-.521,.644-.422,1.588,.223,2.109,.64,.519,1.586,.424,2.109-.223l7.334-9.06,7.334,9.06c.524,.647,1.47,.742,2.109,.223,.645-.521,.744-1.466,.223-2.109l-7.736-9.557Z"/>' +
                            '</svg>' +
                        '</button>' +
                        '<span class="select2-selection__choice__display">' + data.text + '</span>' +
                    '</span>'
                );

                $selection.find('.select2-selection__choice__remove__custom').on('click', function(e) {
                    e.stopPropagation();
                    var $select = $(`.line-items[data-id="${item_id}"] select[name^="line_warranty_period"]`);
                    var values = $select.val() || [];
                    var newValues = values.filter(function(v) { return v != data.id; });
                    $select.val(newValues).trigger('change');
                });

                return $selection;
            }
        });

        for (let i = 0; i < WARRANTY_PERIODS.length; i++) {
            const wp = WARRANTY_PERIODS[i];
            let opt = new Option(wp.name, wp.id);
            $(`.line-items[data-id="${item_id}"] select[name^="line_warranty_period"]`).append(opt);
        }
    }

    // Load existing products when editing
    function loadExistingLineItems() {
        if (SERVICE_FORM != null && SERVICE_FORM.products && SERVICE_FORM.products.length > 0) {
            for (let i = 0; i < SERVICE_FORM.products.length; i++) {
                const lineProduct = SERVICE_FORM.products[i];

                $('#add-item-btn').click();

                // Store product data if available
                if (lineProduct.product) {
                    LINE_PRODUCTS[lineProduct.product_id] = lineProduct.product;
                }

                $(`.line-items[data-id="${i+1}"]`).attr('data-line-product-id', lineProduct.id);

                // Set product
                if (lineProduct.product) {
                    let opt = new Option(`${lineProduct.product.sku} - ${lineProduct.product.model_desc}`, lineProduct.product_id, true, true);
                    $(`.line-items[data-id="${i+1}"] select[name="line_product_id[]"]`).append(opt).trigger('change');
                }

                // Set custom description
                $(`.line-items[data-id="${i+1}"] input[name="line_custom_desc[]"]`).val(lineProduct.custom_desc || '');

                // Set quantity
                $(`.line-items[data-id="${i+1}"] input[name="line_qty[]"]`).val(lineProduct.qty || 1);

                // Set FOC
                if (lineProduct.is_foc == 1) {
                    $(`.line-items[data-id="${i+1}"] .foc-btns`).attr('data-is-foc', 'false').trigger('click');
                }

                // Set SST
                if (lineProduct.with_sst == 1) {
                    $(`.line-items[data-id="${i+1}"] .sst-btns`).attr('data-with-sst', 'true');
                    $(`.line-items[data-id="${i+1}"] .line-with-sst-input`).val(1);
                } else {
                    $(`.line-items[data-id="${i+1}"] .sst-btns`).attr('data-with-sst', 'false');
                    $(`.line-items[data-id="${i+1}"] .line-with-sst-input`).val(0);
                }

                // Set UOM
                $(`.line-items[data-id="${i+1}"] input[name="line_uom[]"]`).val(lineProduct.uom || '');

                // Set discount
                $(`.line-items[data-id="${i+1}"] input[name="line_discount[]"]`).val(lineProduct.discount || 0);

                // Set remark (load into QuillJS editor)
                if (lineProduct.remark && LINE_REMARK_QUILLS[i+1]) {
                    LINE_REMARK_QUILLS[i+1].root.innerHTML = lineProduct.remark;
                    $(`.line-items[data-id="${i+1}"] .line-remark-textarea`).val(lineProduct.remark);
                }

                // Set warranty periods
                if (lineProduct.warranty_periods && lineProduct.warranty_periods.length > 0) {
                    let wpIds = lineProduct.warranty_periods.map(wp => wp.warranty_period_id.toString());
                    $(`.line-items[data-id="${i+1}"] select[name^="line_warranty_period"]`).val(wpIds).trigger('change');
                }

                // Set selling price from stored data
                if (lineProduct.product && lineProduct.product.selling_prices) {
                    $(`.line-items[data-id="${i+1}"] select[name="line_selling_price[]"]`).empty();
                    $(`.line-items[data-id="${i+1}"] select[name="line_selling_price[]"]`).append('<option value="">{{ __("Select price") }}</option>');
                    for (let j = 0; j < lineProduct.product.selling_prices.length; j++) {
                        let sp = lineProduct.product.selling_prices[j];
                        let opt = new Option(
                            `${sp.name} (RM ${priceFormat(sp.price)})`,
                            sp.id
                        );
                        $(`.line-items[data-id="${i+1}"] select[name="line_selling_price[]"]`).append(opt);
                    }
                }

                // Set selected selling price or override
                if (lineProduct.unit_price != null && lineProduct.unit_price > 0) {
                    // Check if there's a matching selling price
                    let matchedSellingPrice = false;
                    if (lineProduct.product && lineProduct.product.selling_prices) {
                        for (let j = 0; j < lineProduct.product.selling_prices.length; j++) {
                            let sp = lineProduct.product.selling_prices[j];
                            if (parseFloat(sp.price) === parseFloat(lineProduct.unit_price)) {
                                $(`.line-items[data-id="${i+1}"] select[name="line_selling_price[]"]`).val(sp.id).trigger('change');
                                matchedSellingPrice = true;
                                break;
                            }
                        }
                    }
                    // If no match, set as override
                    if (!matchedSellingPrice) {
                        $(`.line-items[data-id="${i+1}"] input[name="line_override_selling_price[]"]`).val(lineProduct.unit_price).trigger('keyup');
                    }
                }

                // Trigger calculations
                calLineItemTotal(i + 1);
            }
        }

        // Add a default empty item if no products exist
        if (SERVICE_FORM == null || !SERVICE_FORM.products || SERVICE_FORM.products.length == 0) {
            $('#add-item-btn').click();
        }

        sortLineItems();
        LINE_INIT_EDIT = false;
    }

    // Initialize on page load
    $(document).ready(function() {
        setTimeout(function() {
            loadExistingLineItems();
        }, 100);
    });

    // QuillJS for Line Item Remark
    function buildLineRemarkQuillEditor(itemId) {
        var container = $(`.line-items[data-id="${itemId}"] .line-remark-editor`)[0];

        var quill = new Quill(container, {
            theme: 'snow',
            placeholder: "{!! __('Remark (optional)') !!}",
            modules: {
                toolbar: {
                    container: [
                        [{ 'header': [1, 2, false] }],
                        ['bold', 'italic', 'underline'],
                        [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                        ['image'],
                    ],
                    handlers: {
                        image: function() {
                            var input = document.createElement('input');
                            input.setAttribute('type', 'file');
                            input.setAttribute('accept', 'image/*');
                            input.click();

                            input.onchange = function() {
                                var file = input.files[0];
                                if (!file) return;

                                if (!file.type.match('image.*')) {
                                    alert('Please select an image file.');
                                    return;
                                }

                                if (file.size > 5 * 1024 * 1024) {
                                    alert('Image size should be less than 5MB.');
                                    return;
                                }

                                var formData = new FormData();
                                formData.append('image', file);
                                var range = quill.getSelection(true);

                                quill.insertText(range.index, 'Uploading image...');
                                quill.setSelection(range.index + 19);

                                $.ajax({
                                    url: '{{ route("quill.upload.image") }}',
                                    type: 'POST',
                                    data: formData,
                                    processData: false,
                                    contentType: false,
                                    headers: {
                                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                    },
                                    success: function(response) {
                                        quill.deleteText(range.index, 19);
                                        quill.insertEmbed(range.index, 'image', response.url);
                                        quill.setSelection(range.index + 1);
                                        var html = quill.root.innerHTML;
                                        var isEmpty = html === '<p><br></p>' || quill.getText().trim() === '';
                                        $(`.line-items[data-id="${itemId}"] .line-remark-textarea`).val(isEmpty ? '' : html);
                                    },
                                    error: function(xhr) {
                                        quill.deleteText(range.index, 19);
                                        var errorMsg = 'Failed to upload image.';
                                        if (xhr.responseJSON && xhr.responseJSON.message) {
                                            errorMsg = xhr.responseJSON.message;
                                        }
                                        alert(errorMsg);
                                    }
                                });
                            };
                        }
                    }
                },
                resize: {}
            },
        });

        // Sync to hidden textarea on text change
        quill.on('text-change', function() {
            var html = quill.root.innerHTML;
            var isEmpty = html === '<p><br></p>' || quill.getText().trim() === '';
            $(`.line-items[data-id="${itemId}"] .line-remark-textarea`).val(isEmpty ? '' : html);
        });

        LINE_REMARK_QUILLS[itemId] = quill;
    }

    // QuillJS for Quotation Remark
    function buildQuotationRemarkQuillEditor() {
        var $quill = $(`
            <div class="quill-wrapper rounded-md border border-gray-300 bg-white">
                <div id="quotation-remark-editor"></div>
            </div>
        `);

        $('#quotation-remark-container textarea[name="quotation_remark"]').after($quill);

        var quill = new Quill('#quotation-remark-editor', {
            theme: 'snow',
            placeholder: "{!! __('Quotation Remark') !!}",
            modules: {
                toolbar: {
                    container: [
                        [{ 'header': [1, 2, false] }],
                        ['bold', 'italic', 'underline'],
                        [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                        ['image'],
                    ],
                    handlers: {
                        image: function() {
                            var input = document.createElement('input');
                            input.setAttribute('type', 'file');
                            input.setAttribute('accept', 'image/*');
                            input.click();

                            input.onchange = function() {
                                var file = input.files[0];
                                if (!file) return;

                                if (!file.type.match('image.*')) {
                                    alert('Please select an image file.');
                                    return;
                                }

                                if (file.size > 5 * 1024 * 1024) {
                                    alert('Image size should be less than 5MB.');
                                    return;
                                }

                                var formData = new FormData();
                                formData.append('image', file);
                                var range = quill.getSelection(true);

                                quill.insertText(range.index, 'Uploading image...');
                                quill.setSelection(range.index + 19);

                                $.ajax({
                                    url: '{{ route("quill.upload.image") }}',
                                    type: 'POST',
                                    data: formData,
                                    processData: false,
                                    contentType: false,
                                    headers: {
                                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                    },
                                    success: function(response) {
                                        quill.deleteText(range.index, 19);
                                        quill.insertEmbed(range.index, 'image', response.url);
                                        quill.setSelection(range.index + 1);
                                        var html = quill.root.innerHTML;
                                        var isEmpty = html === '<p><br></p>' || quill.getText().trim() === '';
                                        $('#quotation-remark-container textarea[name="quotation_remark"]').val(isEmpty ? '' : html);
                                    },
                                    error: function(xhr) {
                                        quill.deleteText(range.index, 19);
                                        var errorMsg = 'Failed to upload image.';
                                        if (xhr.responseJSON && xhr.responseJSON.message) {
                                            errorMsg = xhr.responseJSON.message;
                                        }
                                        alert(errorMsg);
                                    }
                                });
                            };
                        }
                    }
                },
                resize: {}
            },
        });

        // Load existing content
        setTimeout(function() {
            let existingContent = $('#quotation-remark-container textarea[name="quotation_remark"]').val();
            if (existingContent && existingContent.trim() !== '') {
                quill.root.innerHTML = existingContent;
            }
        }, 100);

        // Set toolbar titles
        var toolbar = quill.container.previousSibling;
        toolbar.querySelector('button.ql-bold').setAttribute('title', 'Bold');
        toolbar.querySelector('button.ql-italic').setAttribute('title', 'Italic');
        toolbar.querySelector('button.ql-underline').setAttribute('title', 'Underline');
        toolbar.querySelector('button.ql-list[aria-label="list: ordered"]').setAttribute('title', 'Ordered List');
        toolbar.querySelector('button.ql-list[aria-label="list: bullet"]').setAttribute('title', 'Bullet List');
        toolbar.querySelector('button.ql-image').setAttribute('title', 'Insert Image');

        // Store quill instance for form submission sync
        window.quotationRemarkQuill = quill;

        // Sync on text change
        quill.on('text-change', function() {
            var html = quill.root.innerHTML;
            var isEmpty = html === '<p><br></p>' || quill.getText().trim() === '';
            $('#quotation-remark-container textarea[name="quotation_remark"]').val(isEmpty ? '' : html);
        });
    }
</script>
@endpush
