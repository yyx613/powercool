@extends('layouts.app')

@section('content')
    <div class="mb-6 flex justify-between items-center">
        <x-app.page-title url="{{ route('sales_agent.index') }}">{{ isset($agent) ? __('Edit Sales Agent') : __('Create Sales Agent') }}</x-app.page-title>
    </div>
    @include('components.app.alert.parent')
    <form action="{{ isset($agent) ? route('sales_agent.update', ['agent' => $agent]) : route('sales_agent.store') }}" method="POST" enctype="multipart/form-data" id="form">
        @csrf
        <div class="bg-white p-4 rounded-md shadow" id="content-container">
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-8 w-full mb-4">
                <div class="flex flex-col">
                    <x-app.input.label id="name" class="mb-1">{{ __('Name') }} <span class="text-sm text-red-500">*</span></x-app.input.label>
                    <x-app.input.input name="name" id="name" value="{{ old('name') ?? (isset($agent) ? $agent->name : null) }}" />
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>
            </div>
            <div class="mt-8 flex justify-end gap-x-4">
                @if (!isset($agent))
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