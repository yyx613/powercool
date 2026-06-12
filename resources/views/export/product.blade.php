@inject('product', 'App\Models\Product')

<table>
    <tr>
        <td>{{ __('Company Group') }}</td>
        <td>{{ __('Initial for Production') }}</td>
        <td>{{ __('Model Code / Supplier Barcode Info') }}</td>
        <td>{{ __('Model Description') }}</td>
        <td>{{ __('UOM') }}</td>
        <td>{{ __('Category') }}</td>
        <td>{{ __('Item Type') }}</td>
        <td>{{ __('Low Stock Threshold') }}</td>
        <td>{{ __('Selling Price (Min)') }}</td>
        <td>{{ __('Selling Price (Max)') }}</td>
        <td>{{ __('Cost') }}</td>
        <td>{{ __('Status') }}</td>
        <td>{{ __('Hi-Ten Stock Code') }}</td>

        <td>{{ __('Selling Prices') }}</td>

        <td>{{ __('Dimension (L x W x H)(In MM)') }}</td>
        <td>{{ __('Capacity') }}</td>
        <td>{{ __('Weight (In KG)') }}</td>
        <td>{{ __('Refrigerant') }}</td>
        <td>{{ __('Power Input') }}</td>
        <td>{{ __('Voltage / Frequency') }}</td>
        <td>{{ __('Standard Features') }}</td>

        <td>{{ __('Classification Code') }}</td>

        <td>{{ __('Lazada Sku') }}</td>
        <td>{{ __('Shopee Sku') }}</td>
        <td>{{ __('Tiktok Sku') }}</td>
        <td>{{ __('Woo Commerce Sku') }}</td>

        <td>{{ __('Serial No') }}</td>
    </tr>
    @foreach ($products as $p)
        @php
            $selling_prices = [];

            $sps = $p->sellingPrices->toArray();
            for ($i = 0; $i < count($sps) ;$i++) {
                $selling_prices[] = $sps[$i]['name'] . ' - RM' . $sps[$i]['price'];
            }

            $classfication_codes = [];

            $ccs = $p->classificationCodes->toArray();
            for ($i = 0; $i < count($ccs) ;$i++) {
                $classfication_codes[] = $ccs[$i]['code'] . ' - ' . $ccs[$i]['description'];
            }
        @endphp
        <tr>
            <td>{{ $p->company_group == 1 ? 'Power Cool' : ($p->company_group == 2 ? 'Hi-Ten' : '') }}</td>
            <td>{{ $p->initial_for_production }}</td>
            <td>{{ $p->sku }}</td>
            <td>{{ $p->model_desc }}</td>
            <td>{{ $p->uom }}</td>
            <td>{{ $p->category->name ?? null }}</td>
            <td>{{ $p->itemType->name ?? null}}</td>
            <td>{{ $p->low_stock_threshold }}</td>
            <td>{{ $p->min_price }}</td>
            <td>{{ $p->max_price }}</td>
            <td>{{ $p->cost }}</td>
            <td>{{ $p->is_active == 1 ? 'Active' : 'Inactive' }}</td>
            <td>{{ $p->stockHiTen->sku ?? null }}</td>

            <td>{{ join(', ', $selling_prices) }}</td>

            <td>{{ $p->length ?? 0 }} MM x {{ $p->width ?? 0 }} MM x {{ $p->height ?? 0 }} MM</td>
            <td>{{ $p->capacity }}</td>
            <td>{{ $p->weight }} KG</td>
            <td>{{ $p->refrigerant }}</td>
            <td>{{ $p->power_input }}</td>
            <td>{{ $p->voltage_frequency }}</td>
            <td>{{ $p->standard_features }}</td>

            <td>{{ join(', ', $classfication_codes) }}</td>

            <td>{{ $p->lazada_sku }}</td>
            <td>{{ $p->shopee_sku }}</td>
            <td>{{ $p->tiktok_sku }}</td>
            <td>{{ $p->woo_commerce_sku }}</td>

            <td>{{ join(', ', $p->children->pluck('sku')->toArray()) }}</td>
        </tr>
    @endforeach
</table>
