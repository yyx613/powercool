@inject('saleEnquiry', 'App\Models\SaleEnquiry')

<table>
    <tr>
        <td>{{ __('Enquiry ID') }}</td>
        <td>{{ __('Enquiry Date') }}</td>
        <td>{{ __('Customer Name') }}</td>
        <td>{{ __('Phone Number') }}</td>
        <td>{{ __('Email') }}</td>
        <td>{{ __('Source') }}</td>
        <td>{{ __('Product') }}</td>
        <td>{{ __('Assigned Staff') }}</td>
        <td>{{ __('Priority') }}</td>
        <td>{{ __('Quality') }}</td>
        <td>{{ __('Promotion') }}</td>
        <td>{{ __('Created By') }}</td>
        <td>{{ __('Status') }}</td>
    </tr>
    @foreach ($enquiries as $enquiry)
        @php
            // Source mapping
            $sourceLabel = match($enquiry->enquiry_source) {
                $saleEnquiry::SOURCE_WEBSITE => 'Website',
                $saleEnquiry::SOURCE_FACEBOOK => 'Facebook',
                $saleEnquiry::SOURCE_SHOPEE => 'Shopee',
                $saleEnquiry::SOURCE_LAZADA => 'Lazada',
                $saleEnquiry::SOURCE_WALK_IN => 'Walk In',
                $saleEnquiry::SOURCE_REFERRAL => 'Referral',
                $saleEnquiry::SOURCE_INSTAGRAM => 'Instagram',
                $saleEnquiry::SOURCE_TIKTOK => 'Tiktok',
                $saleEnquiry::SOURCE_XHS => 'XHS',
                $saleEnquiry::SOURCE_PHONE => 'Phone Call',
                $saleEnquiry::SOURCE_WHATSAPP => 'WhatsApp (Not from Platform)',
                $saleEnquiry::SOURCE_GOOGLE => 'Google',
                default => '',
            };

            // Priority mapping
            $priorityLabel = match($enquiry->priority) {
                $saleEnquiry::PRIORITY_LOW => 'Low',
                $saleEnquiry::PRIORITY_MEDIUM => 'Medium',
                $saleEnquiry::PRIORITY_HIGH => 'High',
                default => '',
            };

            // Quality mapping
            $qualityLabel = match($enquiry->quality) {
                $saleEnquiry::QUALITY_SEEN_AND_REPLY => 'Seen and Reply',
                $saleEnquiry::QUALITY_SEEN_NO_REPLY => 'Seen No Reply',
                $saleEnquiry::QUALITY_NO_SEEN_NO_REPLY => 'No Seen No Reply',
                default => '',
            };

            // Status mapping
            $statusLabel = match($enquiry->status) {
                $saleEnquiry::STATUS_NEW => 'New',
                $saleEnquiry::STATUS_IN_PROGRESS => 'In Progress',
                $saleEnquiry::STATUS_CLOSED_CONVERTED => 'Closed Deal (Converted)',
                $saleEnquiry::STATUS_CLOSED_DROPPED => 'No Deal',
                default => '',
            };
        @endphp
        <tr>
            <td>{{ $enquiry->sku }}</td>
            <td>{{ $enquiry->enquiry_date ? $enquiry->enquiry_date->format('d M Y') : '' }}</td>
            <td>{{ $enquiry->name }}</td>
            <td>{{ $enquiry->phone_number }}</td>
            <td>{{ $enquiry->email }}</td>
            <td>{{ $sourceLabel }}</td>
            <td>{{ $enquiry->product_service_interested ?? '' }}</td>
            <td>{{ $enquiry->assignedUser->name ?? '' }}</td>
            <td>{{ $priorityLabel }}</td>
            <td>{{ $qualityLabel }}</td>
            <td>{{ $enquiry->promotion ? $enquiry->promotion->sku . ' - ' . ($enquiry->promotion->type == 'perc' ? number_format($enquiry->promotion->amount, 2) . '%' : 'RM' . number_format($enquiry->promotion->amount, 2)) : '' }}</td>
            <td>{{ $enquiry->createdByUser->name ?? '' }}</td>
            <td>{{ $statusLabel }}</td>
        </tr>
    @endforeach
</table>
