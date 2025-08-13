@extends('layouts.app')

@section('content')
    <div class="max-w-screen-xl m-auto">
        <div class="mb-6 flex justify-between items-center">
            <x-app.page-title>{{ __('Customer Information') }}</x-app.page-title>
        </div>
        @include('components.app.alert.parent')

        <div class="grid gap-y-8">
            @include('customer.form_step.info', [
                'default_branch' => $default_branch,
            ])
            @include('customer.form_step.location')
        </div>
        @if (!isset($customer) || (isset($customer) && $customer->status != 3))
            <div class="mt-8 flex justify-end">
                <x-app.button.submit id="group-submit-btn">{{ __('Save and Update') }}</x-app.button.submit>
            </div>
        @endif
    </div>

    <x-app.modal.create-customer-link-created-modal />
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
