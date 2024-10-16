@extends('layouts.app')

@section('content')
    <div class="mb-6 flex justify-between items-center">
        <x-app.page-title url="{{ route('sale_order.index') }}">{{ isset($sale) ? __('Edit Sale Order - ') . $sale->sku : __('Create Sale Order') }}</x-app.page-title>
    </div>

    <div class="grid gap-y-8">
        @include('sale_order.form_step.quotation_details')
        @include('sale_order.form_step.product_details')
        @include('sale_order.form_step.payment_details')
        @include('sale_order.form_step.delivery_schedule')
        @include('sale_order.form_step.remarks')
    </div>
@endsection

@push('scripts')
<script>
    SALE = @json($sale ?? null);
    QUO = @json($quo ?? null);
</script>
@endpush