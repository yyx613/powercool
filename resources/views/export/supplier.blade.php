<table>
    <tr>
        <td>{{ __('Code') }}</td>
        <td>{{ __('Company Group') }}</td>
        <td>{{ __('Prefix') }}</td>
        <td>{{ __('Supplier Name') }}</td>
        <td>{{ __('Company Name') }}</td>
        <td>{{ __('Mobile Number') }}</td>
        <td>{{ __('Website') }}</td>
        <td>{{ __('Currency') }}</td>
        <td>{{ __('City') }}</td>
        <td>{{ __('Debtor Type') }}</td>
        <td>{{ __('Sale Agent') }}</td>
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
        <td>{{ __('Location') }}</td>
        <td>{{ __('Remark') }}</td>
    </tr>
    @php
        $supplier_ids = [];
    @endphp
    @foreach ($suppliers as $supp)
        @php
            $credit_term_name = [];

            for ($i = 0; $i < count($supp->creditTerms); $i++) {
                $name = $supp->creditTerms[$i]->creditTerm->name ?? null;
                if ($name != null) {
                    $credit_term_name[] = $name;
                }
            }
        @endphp
        <tr>
            <td>{{ $supp->sku }}</td>
            <td>{{ $supp->company_group == 1 ? 'Power Cool' : ($supp->company_group == 2 ? 'Hi-Ten' : '') }}</td>
            <td>{{ $supp->prefix }}</td>
            <td>{{ $supp->name }}</td>
            <td>{{ $supp->company_name }}</td>
            <td>{{ $supp->mobile_number }}</td>
            <td>{{ $supp->website }}</td>
            <td>{{ $supp->currency->name ?? null }}</td>
            <td>{{ $supp->area->name ?? null }}</td>
            <td>{{ $supp->debtorType->name ?? null }}</td>
            <td>{{ $supp->saleAgent->name ?? null }}</td>
            <td>{{ implode(', ', $credit_term_name) }}</td>
            <td>{{ $supp->status == $supp::STATUS_ACTIVE ? 'Active' : 'Inactive' }}</td>
            <td>{{ $supp->category == 1 ? 'Business' : ($supp->category == 2 ? 'Individual' : ($supp->category == 3 ? 'Government' : '')) }}</td>
            <td>{{ $supp->type == 1 ? 'Local' : 'Oversea' }}</td>
            <td>{{ $supp->tim_numbr }}</td>
            <td>{{ $supp->company_registration_number }}</td>
            <td>{{ $supp->msicCode->code ?? null }}</td>
            <td>{{ $supp->msicCode->description ?? null }}</td>
            <td>{{ $supp->sst_number }}</td>
            <td>{{ $supp->tourism_tax_reg_no }}</td>
            <td>{{ $supp->prev_gst_reg_no }}</td>
            <td>{{ $supp->registered_name }}</td>
            <td>{{ $supp->trade_name }}</td>
            <td>{{ $supp->phone }}</td>
            <td>{{ $supp->email }}</td>
            <td>{{ $supp->remark }}</td>
        </tr>
    @endforeach
</table>
