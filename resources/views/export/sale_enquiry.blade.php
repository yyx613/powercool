@inject('saleEnquiry', 'App\Models\SaleEnquiry')

<table>
    <tr>
        <td>Enquiry ID</td>
        <td>Enquiry Date</td>
        <td>Customer Name</td>
        <td>Phone Number</td>
        <td>Email</td>
        <td>Source</td>
        <td>Product</td>
        <td>Assigned Staff</td>
        <td>Priority</td>
        <td>Quality</td>
        <td>Promotion</td>
        <td>Created By</td>
        <td>Status</td>
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
            <td>{{ $enquiry->product->model_desc ?? '' }}</td>
            <td>{{ $enquiry->assignedUser->name ?? '' }}</td>
            <td>{{ $priorityLabel }}</td>
            <td>{{ $qualityLabel }}</td>
            <td>{{ $enquiry->promotion->desc ?? '' }}</td>
            <td>{{ $enquiry->createdByUser->name ?? '' }}</td>
            <td>{{ $statusLabel }}</td>
        </tr>
    @endforeach
</table>
