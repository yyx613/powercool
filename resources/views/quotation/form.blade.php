@extends('layouts.app')

@section('content')
    <div class="mb-6 flex justify-between items-center">
        <x-app.page-title>{{ isset($sale) ? 'Edit Quotation - ' . $sale->sku : 'Create Quotation' }}</x-app.page-title>
    </div>

    <div class="grid gap-y-8">
        @include('quotation.form_step.quotation_details')
        @include('quotation.form_step.product_details')
        @include('quotation.form_step.remarks')
    </div>
@endsection

@push('scripts')
<script>
    SALE = @json($sale ?? null);
</script>
@endpush