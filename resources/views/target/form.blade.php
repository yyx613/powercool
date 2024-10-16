@inject('carbon', 'Carbon\Carbon')

@extends('layouts.app')

@section('content')
    <div class="mb-6 flex justify-between items-center">
        <x-app.page-title url="{{ route('target.index') }}">{{ isset($target) ? __('Edit Target') : __('Create Target') }}</x-app.page-title>
    </div>
    @include('components.app.alert.parent')
    <div class="bg-white p-4 border rounded-md">
        <form action="{{ isset($target) ? route('target.update', ['target' => $target]) : route('target.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div>
                <div class="grid grid-cols-3 gap-8 w-full mb-8">
                    <div class="flex flex-col">
                        <x-app.input.label id="sale" class="mb-1">{{ __('Salesperson') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                        <x-app.input.select2 name="sale" id="sale" :hasError="$errors->has('sale')" placeholder="{{ __('Select a salesperson') }}">
                            <option value="">{{ __('Select a salesperson') }}</option>
                            @foreach ($sales as $sa)
                                <option value="{{ $sa->id }}" @selected(old('sale', isset($duplicate_target) ? $duplicate_target->sale_id : (isset($target) ? $target->sale_id : null)) == $sa->id)>{{ $sa->name }}</option>
                            @endforeach
                        </x-app.input.select2>
                        <x-input-error :messages="$errors->get('sale')" class="mt-2" />
                    </div>
                    <div class="flex flex-col">
                        <x-app.input.label id="date" class="mb-1">{{ __('Date') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                        <x-app.input.select name="date" id="date" :hasError="$errors->has('date')">
                            <option value="">{{ __('Select a date') }}</option>
                            @foreach($period as $p)
                                <option value="{{ $p->format('M Y') }}" @selected(old('date', isset($duplicate_target) ? $carbon::parse($duplicate_target->date)->format('M Y') : (isset($target) ? $carbon::parse($target->date)->format('M Y') : null)) == $p->format('M Y'))>{{ $p->format('M Y') }}</option>
                            @endforeach
                        </x-app.input.select>
                        <x-input-error :messages="$errors->get('date')" class="mt-2" />
                    </div>
                    <div class="flex flex-col">
                        <x-app.input.label id="amount" class="mb-1">{{ __('Target Amount') }}<span class="text-sm text-red-500">*</span></x-app.input.label>
                        <x-app.input.input name="amount" id="amount" :hasError="$errors->has('amount')" value="{{ isset($duplicate_target) ? $duplicate_target->amount : (isset($target) ? $target->amount : null) }}" class="decimal-input" />
                        <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                    </div>
                </div>
                <div class="mt-8 flex justify-end">
                    <x-app.button.submit id="submit-btn">{{ __('Save and Update') }}</x-app.button.submit>
                </div>
            </div>
        </form>
    </div>
@endsection