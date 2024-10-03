@extends('layouts.app')

@section('content')
    <div class="mb-6 flex justify-between items-center">
        <x-app.page-title url="{{ route('promotion.index') }}">{{ isset($promo) ? 'Edit Promotion' : 'Create Promotion' }}</x-app.page-title>
    </div>
    @include('components.app.alert.parent')
    <form action="{{ isset($promo) ? route('promotion.update', ['promotion' => $promo]) : route('promotion.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="bg-white p-4 rounded-md shadow" id="content-container">
            <div class="grid grid-cols-3 gap-8 w-full mb-4">

                <div class="flex flex-col">
                    <x-app.input.label id="promo_code" class="mb-1">Promo Code <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="promo_code" id="promo_code" value="{{ old('promo_code') ?? (isset($promo) ? $promo->sku : null) }}" />
                    <x-input-error :messages="$errors->get('promo_code')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="amount_val" class="mb-1">Discount Amount (RM)</x-app.input.label>
                    <x-app.input.input name="amount_val" id="amount_val" class="decimal-input" value="{{ old('amount_val') ?? (isset($promo) && $promo->type == 'val' ? $promo->amount : null) }}" />
                    <x-input-error :messages="$errors->get('amount_val')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="amount_perc" class="mb-1">Discount Percentage (%)</x-app.input.label>
                    <x-app.input.input name="amount_perc" id="amount_perc" class="decimal-input" value="{{ old('amount_perc') ?? (isset($promo) && $promo->type == 'perc' ? $promo->amount : null) }}" />
                    <x-input-error :messages="$errors->get('amount_perc')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="desc" class="mb-1">Description <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="desc" id="desc" value="{{ old('desc') ?? (isset($promo) ? $promo->desc : null) }}" />
                    <x-input-error :messages="$errors->get('desc')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="valid_till" class="mb-1">Valid Till</x-app.input.label>
                    <x-app.input.input name="valid_till" id="valid_till" value="{{ old('valid_till') ?? (isset($promo) ? $promo->valid_till : null) }}" />
                    <x-input-error :messages="$errors->get('valid_till')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="product" class="mb-1">Product <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select name="product" id="product" :hasError="$errors->has('product')">
                        <option value="">Select a product</option>
                        @foreach ($products as $prod)
                            <option value="{{ $prod->id }}" @selected((isset($promo) ? $promo->product_id : null) == $prod->id)>{{ $prod->model_name }}</option>
                        @endforeach
                    </x-app.input.select>
                    <x-input-error :messages="$errors->get('product')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="status" class="mb-1">Status <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select name="status" id="status" :hasError="$errors->has('status')">
                        <option value="">Select a Active/Inactive</option>
                        <option value="1" @selected(old('status', isset($promo) ? $promo->status : null) == 1)>Active</option>
                        <option value="0" @selected(old('status', isset($promo) ? $promo->status : null) === 0)>Inactive</option>
                    </x-app.input.select>
                    <x-input-error :messages="$errors->get('status')" class="mt-1" />
                </div>    
            </div>
            <div class="mt-8 flex justify-end">
                <x-app.button.submit>{{ isset($promo) ? 'Update Promotion' : 'Create Promotion' }}</x-app.button.submit>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        $('input[name="valid_till"]').daterangepicker(datepickerParam)
        $('input[name="valid_till"]').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD'));
        });

        $('input[name="amount_val"]').on('keyup', function() {
            $('input[name="amount_perc"]').val(null)
        })
        $('input[name="amount_perc"]').on('keyup', function() {
            $('input[name="amount_val"]').val(null)
        })
    </script>
@endpush
