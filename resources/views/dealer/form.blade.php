@extends('layouts.app')

@section('content')
    <div class="mb-6 flex justify-between items-center">
        <x-app.page-title url="{{ route('dealer.index') }}">{{ isset($dealer) ? __('Edit Dealer') : __('Create Dealer') }}</x-app.page-title>
    </div>
    @include('components.app.alert.parent')

    <div class="bg-white p-4 border rounded-md">
        <form action="{{ isset($dealer) ? route('dealer.upsert', ['dealer' => $dealer]) : route('dealer.upsert') }}" method="POST" enctype="multipart/form-data" id="info-form">
            @csrf
            <div id="content-container">
                <div class="grid grid-cols-2 md:grid-cols-3 gap-6 md:gap-8 w-full mb-4">
                    @if (isset($dealer))
                        <div class="flex flex-col">
                            <x-app.input.label id="code" class="mb-1">{{ __('Code') }}</x-app.input.label>
                            <x-app.input.input name="code" id="code" :hasError="$errors->has('code')" value="{{ old('code', isset($dealer) ? $dealer->sku : null) }}" disabled="true"/>
                        </div>
                    @endif
                    <div class="flex flex-col">
                        <x-app.input.label id="name" class="mb-1">{{ __('Dealer Name') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                        <x-app.input.input name="name" id="name" :hasError="$errors->has('name')" value="{{ old('name', isset($dealer) ? $dealer->name : null) }}" />
                    </div>
                </div>
                <div class="mt-8 flex justify-end">
                    <x-app.button.submit id="submit-btn">{{ __('Save and Update') }}</x-app.button.submit>
                </div>
            </div>
        </form>
    </div>
@endsection
