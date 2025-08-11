@extends('layouts.app')
@section('title', 'Debtor')

@section('content')
    <div class="mb-6 flex justify-between items-center">
        <x-app.page-title url="{{ route('customer.index') }}">{{ isset($customer) ? __('Edit Debtor') : __('Create Debtor') }}</x-app.page-title>
    </div>
    @include('components.app.alert.parent')

    <div class="grid gap-y-8">
        @include('customer.form_step.info')
        @include('customer.form_step.location')
    </div>
@endsection

@push('scripts')
<script>
    CUSTOMER = @json($customer ?? null);
</script>
@endpush
