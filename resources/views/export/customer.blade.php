@inject('customer', 'App\Models\Customer')
@inject('customerLocation', 'App\Models\CustomerLocation')

<table>
    <tr>
        <td>Code</td>
        <td>Company Group</td>
        <td>Prefix</td>
        <td>Customer Name</td>
        <td>Company Name</td>
        <td>Mobile Number</td>
        <td>Website</td>
        <td>Currency</td>
        <td>Area</td>
        <td>Debtor Type</td>
        <td>Sale Agent</td>
        <td>Platform</td>
        <td>Credit Term</td>
        <td>Status</td>
        <td>Category</td>
        <td>Type</td>
        <td>TIN</td>
        <td>Business Reg No.</td>
        <td>MSIC Code</td>
        <td>Business Activity Desc.</td>
        <td>SST Reg No.</td>
        <td>Tourism Tax Reg No.</td>
        <td>Prev. GST Reg No.</td>
        <td>Registered Name</td>
        <td>Trade Name</td>
        <td>Phone Number</td>
        <td>Email Address</td>

        <td>Address</td>
        <td>City</td>
        <td>State</td>
        <td>Zip Code</td>
        <td>Type</td>
        <td>Is Default</td>

        <td>Remark</td>
    </tr>
    @php
        $customer_ids = [];
    @endphp
    @foreach ($customers as $cus)
        @php
            $credit_term_name = [];

            for ($i = 0; $i < count($cus->creditTerms); $i++) {
                $name = $cus->creditTerms[$i]->creditTerm->name ?? null;
                if ($name != null) {
                    $credit_term_name[] = $name;
                }
            }

            $customer_info_rendered = false;
            if (in_array($cus->id, $customer_ids,)) {
                $customer_info_rendered = true;
            } else {
                $customer_ids[] = $cus->id;
            }

            $locs = $cus->locations;
        @endphp
        <tr>
            <td>{{ $cus->sku }}</td>
            <td>{{ $cus->company_group == 1 ? 'Power Cool' : ($cus->company_group == 2 ? 'Hi-Ten' : '') }}</td>
            <td>{{ $cus->prefix }}</td>
            <td>{{ $cus->name }}</td>
            <td>{{ $cus->company_name }}</td>
            <td>{{ $cus->mobile_number }}</td>
            <td>{{ $cus->website }}</td>
            <td>{{ $cus->currency->name ?? null }}</td>
            <td>{{ $cus->area->name ?? null }}</td>
            <td>{{ $cus->debtorType->name ?? null }}</td>
            <td>{{ $cus->saleAgent->name ?? null }}</td>
            <td>{{ $cus->platform->name ?? null }}</td>
            <td>{{ implode(', ', $credit_term_name) }}</td>
            <td>{{ $cus->status == $customer::STATUS_ACTIVE ? 'Active' : 'Inactive' }}</td>
            <td>{{ $cus->category == 1 ? 'Business' : ($cus->category == 2 ? 'Individual' : ($cus->category == 3 ? 'Government' : '')) }}</td>
            <td>{{ $cus->type == 1 ? 'Local' : 'Oversea' }}</td>
            <td>{{ $cus->tim_numbr }}</td>
            <td>{{ $cus->company_registration_number }}</td>
            <td>{{ $cus->msicCode->code ?? null }}</td>
            <td>{{ $cus->msicCode->description ?? null }}</td>
            <td>{{ $cus->sst_number }}</td>
            <td>{{ $cus->tourism_tax_reg_no }}</td>
            <td>{{ $cus->prev_gst_reg_no }}</td>
            <td>{{ $cus->registered_name }}</td>
            <td>{{ $cus->trade_name }}</td>
            <td>{{ $cus->phone }}</td>
            <td>{{ $cus->email }}</td>

            <td>{{ count($locs) > 0 ? $locs[0]->address : '' }}</td>
            <td>{{ count($locs) > 0 ? $locs[0]->city : '' }}</td>
            <td>{{ count($locs) > 0 ? $locs[0]->state : ''}}</td>
            <td>{{ count($locs) > 0 ? $locs[0]->postcode : '' }}</td>
            <td>{{ count($locs) > 0 ? $locs[0]->type : '' }}</td>
            <td>{{ count($locs) > 0 ? $locs[0]->type == $customerLocation::TYPE_BILLING ? 'Billing' : ($locs[0]->type == $customerLocation::TYPE_DELIVERY ? 'Delivery' : 'Billing & Delivery') : '' }}</td>

            <td>{{ $cus->remark }}</td>
        </tr>
        @foreach($locs as $loc)
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>

                <td>{{ $loc->address ?? '' }}</td>
                <td>{{ $loc->city ?? '' }}</td>
                <td>{{ $loc->state ?? ''}}</td>
                <td>{{ $loc->postcode ?? '' }}</td>
                <td>{{ $loc->type ?? '' }}</td>
                <td>{{ $loc->type == $customerLocation::TYPE_BILLING ? 'Billing' : ($loc->type == $customerLocation::TYPE_DELIVERY ? 'Delivery' : 'Billing & Delivery') ?? '' }}</td>

                <td></td>
            </tr>
        @endforeach
    @endforeach
</table>
