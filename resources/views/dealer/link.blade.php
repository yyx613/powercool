@extends('layouts.app')

@section('content')
    <div class="max-w-screen-xl m-auto">
        <div class="mb-6 flex justify-between items-center">
            <x-app.page-title>{{ __('Customer Information') }}</x-app.page-title>
        </div>
        @include('components.app.alert.parent')
        
        <div class="grid gap-y-8">
            @include('customer.form_step.info', [
                'default_branch' => $default_branch
            ])
            @include('customer.form_step.location')
        </div>
    </div>

    <x-app.modal.create-customer-link-created-modal/>
@endsection

@push('scripts')
<script>
    CUSTOMER = @json($customer ?? null);
</script>
@endpush