@inject('product', 'App\Models\Product')

<table>
    <tr>
        <td>Company Group</td>
        <td>Initial for Production</td>
        <td>Model Code / Supplier Barcode Info</td>
        <td>Model Name</td>
        <td>ModeL Description</td>
        <td>UOM</td>
        <td>Category</td>
        <td>Item Type</td>
        <td>Low Stock Threshold</td>
        <td>Selling Price (Min)</td>
        <td>Selling Price (Max)</td>
        <td>Cost</td>
        <td>Status</td>
        <td>Hi-Ten Stock Code</td>

        <td>Selling Prices</td>

        <td>Dimension (L x W x H)(In MM)</td>
        <td>Capacity</td>
        <td>Weight (In KG)</td>
        <td>Refrigerant</td>
        <td>Power Input</td>
        <td>Voltage / Frequency</td>
        <td>Standard Features</td>

        <td>Classification Code</td>

        <td>Lazada Sku</td>
        <td>Shopee Sku</td>
        <td>Tiktok Sku</td>
        <td>Woo Commerce Sku</td>

        <td>Serial No</td>
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
            <td>{{ $p->mode_name }}</td>
            <td>{{ $p->mode_desc }}</td>
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
