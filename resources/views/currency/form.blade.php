@extends('layouts.app')

@section('content')
    <div class="mb-6 flex justify-between items-center">
        <x-app.page-title url="{{ route('currency.index') }}">{{ isset($curr) ? 'Edit Currency' : 'Create Currency' }}</x-app.page-title>
    </div>
    @include('components.app.alert.parent')
    <form action="{{ isset($curr) ? route('currency.update', ['currency' => $curr]) : route('currency.store') }}" method="POST" enctype="multipart/form-data" id="form">
        @csrf
        <div class="bg-white p-4 rounded-md shadow" id="content-container">
            <div class="grid grid-cols-3 gap-8 w-full mb-4">
                <div class="flex flex-col">
                    <x-app.input.label id="name" class="mb-1">Name <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="name" id="name" value="{{ old('name') ?? (isset($curr) ? $curr->name : null) }}" />
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>
                <div class="flex flex-col">
                    <x-app.input.label id="status" class="mb-1">Status <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.select name="status" id="status" :hasError="$errors->has('status')">
                        <option value="">Select a Active/Inactive</option>
                        <option value="1" @selected(old('status', isset($curr) ? $curr->is_active : null) == 1)>Active</option>
                        <option value="0" @selected(old('status', isset($curr) ? $curr->is_active : null) === 0)>Inactive</option>
                    </x-app.input.select>
                    <x-input-error :messages="$errors->get('status')" class="mt-1" />
                </div>    
            </div>
            <div class="mt-8 flex justify-end gap-x-4">
                @if (!isset($curr))
                    <x-app.button.submit id="submit-create-btn">Save and Create</x-app.button.submit>
                @endif
                <x-app.button.submit>Save and Update</x-app.button.submit>
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