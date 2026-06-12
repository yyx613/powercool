@inject('customer', 'App\Models\Customer')
@inject('customerLocation', 'App\Models\CustomerLocation')

<table>
    <tr>
        <td>{{ __('Code') }}</td>
        <td>{{ __('Company Group') }}</td>
        <td>{{ __('Prefix') }}</td>
        <td>{{ __('Customer Name') }}</td>
        <td>{{ __('Company Name') }}</td>
        <td>{{ __('Mobile Number') }}</td>
        <td>{{ __('Website') }}</td>
        <td>{{ __('Currency') }}</td>
        <td>{{ __('City') }}</td>
        <td>{{ __('Debtor Type') }}</td>
        <td>{{ __('Platform') }}</td>
        <td>{{ __('Credit Term') }}</td>
        <td>{{ __('Status') }}</td>
        <td>{{ __('Category') }}</td>
        <td>{{ __('Type') }}</td>
        <td>{{ __('TIN') }}</td>
        <td>{{ __('Business Reg No.') }}</td>
        <td>{{ __('MSIC Code') }}</td>
        <td>{{ __('Business Activity Desc.') }}</td>
        <td>{{ __('SST Reg No.') }}</td>
        <td>{{ __('Tourism Tax Reg No.') }}</td>
        <td>{{ __('Prev. GST Reg No.') }}</td>
        <td>{{ __('Registered Name') }}</td>
        <td>{{ __('Trade Name') }}</td>
        <td>{{ __('Phone Number') }}</td>
        <td>{{ __('Email Address') }}</td>

        <td>{{ __('Address') }}</td>
        <td>{{ __('City') }}</td>
        <td>{{ __('State') }}</td>
        <td>{{ __('Zip Code') }}</td>
        <td>{{ __('Type') }}</td>
        <td>{{ __('Is Default') }}</td>

        <td>{{ __('Remark') }}</td>
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
            <td>{{ is_array($cus->mobile_number) ? implode(', ', $cus->mobile_number) : $cus->mobile_number }}</td>
            <td>{{ $cus->website }}</td>
            <td>{{ $cus->currency->name ?? null }}</td>
            <td>{{ $cus->area->name ?? null }}</td>
            <td>{{ $cus->debtorType->name ?? null }}</td>
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
