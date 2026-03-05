@extends('layouts.app')
@section('title', 'Priority')

@section('content')
    <div class="mb-6 flex justify-between items-center">
        <x-app.page-title url="{{ route('priority.index') }}">{{ isset($priority) ? __('Edit Priority') : __('Create Priority') }}</x-app.page-title>
    </div>
    @include('components.app.alert.parent')
    <form action="{{ isset($priority) ? route('priority.update', ['priority' => $priority]) : route('priority.store') }}" method="POST" enctype="multipart/form-data" id="form">
        @csrf
        <div class="bg-white p-4 rounded-md shadow" id="content-container">
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-8 w-full mb-4">
                <div class="flex flex-col">
                    <x-app.input.label id="priority" class="mb-1">{{ __('Priority') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="priority" id="priority" value="{{ old('priority') ?? (isset($priority) ? $priority->priority : null) }}" maxlength="10" placeholder="e.g. P1, P2" />
                    <x-input-error :messages="$errors->get('priority')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="name" class="mb-1">{{ __('Name') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="name" id="name" value="{{ old('name') ?? (isset($priority) ? $priority->name : null) }}" />
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="response_time" class="mb-1">{{ __('Response Time') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="response_time" id="response_time" value="{{ old('response_time') ?? (isset($priority) ? $priority->response_time : null) }}" maxlength="100" placeholder="e.g. Within 24 hrs" />
                    <x-input-error :messages="$errors->get('response_time')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="order" class="mb-1">{{ __('Order') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="order" id="order" type="number" value="{{ old('order') ?? (isset($priority) ? $priority->order : 1) }}" min="1" />
                    <x-input-error :messages="$errors->get('order')" class="mt-1" />
                </div>
            </div>
            <div class="grid grid-cols-1 gap-8 w-full mb-4">
                <div class="flex flex-col">
                    <x-app.input.label id="description" class="mb-1">{{ __('Description') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.textarea name="description" id="description" rows="3" maxlength="1000">{{ old('description') ?? (isset($priority) ? $priority->description : null) }}</x-app.input.textarea>
                    <x-input-error :messages="$errors->get('description')" class="mt-1" />
                </div>
            </div>
            <div class="mt-8 flex justify-end gap-x-4">
                @if (!isset($priority))
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