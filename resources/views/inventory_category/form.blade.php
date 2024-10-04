@extends('layouts.app')

@section('content')
    <div class="mb-6 flex justify-between items-center">
        <x-app.page-title url="{{ route('inventory_category.index') }}">{{ isset($cat) ? 'Edit Category' : 'Create Category' }}</x-app.page-title>
    </div>
    @include('components.app.alert.parent')
    <div class="bg-white p-4 border rounded-md">
        <form action="{{ route('inventory_category.upsert') }}" method="POST" enctype="multipart/form-data" id="form">
            @csrf
            <div>
                @if (isset($cat))
                    <x-app.input.input name="category_id" id="category_id" value="{{ isset($cat) ? $cat->id : null }}" class="hidden" />
                @endif
                <div class="grid grid-cols-3 gap-8 w-full">
                    <div class="flex flex-col">
                        <x-app.input.label id="name" class="mb-1">Name <span class="text-sm text-red-500">*</span></x-app.input.label>
                        <x-app.input.input name="name" id="name" value="{{ isset($cat) ? $cat->name : null }}" />
                        <x-app.message.error id="name_err"/>
                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                    </div>
                    <div class="flex flex-col">
                        <x-app.input.label id="status" class="mb-1">Status <span class="text-sm text-red-500">*</span></x-app.input.label>
                        <x-app.input.select name="status" id="status">
                            <option value="">Select a Active/Inactive</option>
                            <option value="1" @selected(old('status', isset($cat) ? $cat->is_active : null) == 1)>Active</option>
                            <option value="0" @selected(old('status', isset($cat) ? $cat->is_active : null) === 0)>Inactive</option>
                        </x-app.input.select>
                        <x-input-error :messages="$errors->get('status')" class="mt-1" />
                    </div>
                </div>
            </div>
            <div class="mt-8 flex justify-end gap-x-4">
                @if (!isset($cat))
                    <x-app.button.submit id="submit-create-btn">Save and Create</x-app.button.submit>
                @endif
                <x-app.button.submit id="submit-update-btn">Save and Update</x-app.button.submit>
            </div>
        </form>
    </div>
    
@endsection

@push('scripts')
<script>
    CATEGORY = @json($cat ?? null);
    FORM_CAN_SUBMIT = true

    $('#submit-create-btn').on('click', function(e) {
        let url = $('#form').attr('action')
        url = `${url}?create_again=true`

        $('#form').attr('action', url)
    })
</script>
@endpush