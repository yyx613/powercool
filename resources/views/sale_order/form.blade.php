@extends('layouts.app')

@section('content')
    <div class="mb-6 flex justify-between items-center">
        <x-app.page-title>{{ isset($sale) ? 'Edit Sale Order - ' . $sale->sku : 'Create Sale Order' }}</x-app.page-title>
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
    INIT_EDIT = false;
    SALE = @json($sale ?? null);
    QUO = @json($quo ?? null);

    if (SALE != null) {
        INIT_EDIT = true
    }

    $(document).ready(function() {
        $('#quotation-form select[name="customer"]').trigger('change')

        if (SALE != null) {
            INIT_EDIT = false
        }
    })

    $('#quotation-form select[name="customer"]').on('change', function() {
        let val = $(this).val()

        let url = '{{ route("customer.get_location") }}'
        url = `${url}?customer_id=${val}`

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: url,
            type: 'GET',
            async: false,
            success: function(res) {
                $('#delivery-form select[name="delivery_address"] option').remove()

                // Default option
                let opt = new Option('Select an address', null)
                $('#delivery-form select[name="delivery_address"]').append(opt)

                for (let i = 0; i < res.locations.length; i++) {
                    const loc = res.locations[i];
                    
                    let opt = new Option(loc.address, loc.id, false, INIT_EDIT == true && loc.id == SALE.delivery_address_id)
                    $('#delivery-form select[name="delivery_address"]').append(opt)
                }
            },
        });
    })
</script>
@endpush