@extends('layouts.app')
@section('title', 'Debtor')

@section('content')
    <div class="mb-6 flex justify-between items-center">
        <x-app.page-title
            url="{{ route('customer.index') }}">{{ isset($customer) ? __('Edit Debtor') : __('Create Debtor') }}</x-app.page-title>
    </div>
    @include('components.app.alert.parent')

    <div class="grid gap-y-8">
        @include('customer.form_step.info')
        @include('customer.form_step.location')
    </div>
    @if (!isset($customer) || (isset($customer) && $customer->status != 3))
        <div class="mt-8 flex justify-end">
            <x-app.button.submit id="group-submit-btn">{{ __('Save and Update') }}</x-app.button.submit>
        </div>
    @endif
@endsection

@push('scripts')
    <script>
        GROUP_SUBMITTING = false
        CUSTOMER = @json($customer ?? null);

        $('#group-submit-btn').on('click', function() {
            if (GROUP_SUBMITTING) return
            GROUP_SUBMITTING = true

            $('#group-submit-btn').text('Updating')
            $('#group-submit-btn').removeClass('bg-yellow-400 shadow')
            $('.err_msg').addClass('hidden') // Remove error messages

            $('#info-form').submit()
        })
    </script>
@endpush
