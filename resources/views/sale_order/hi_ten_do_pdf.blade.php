<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Order | {{ $sku }}</title>
</head>
<body>
    <!-- Header -->
    <table style="width: 100%; font-family: sans-serif; border-collapse: collapse;">
        <tr>
            <td style="width: 70%; border-bottom: solid 1px black; padding: 0 50px 10px 0; text-align: center;">
                <span style="font-size: 18px;">HI-TEN TRADING SDN BHD <span style="font-size: 14px;">(709676-X)</span></span><br>
                <span style="font-size: 14px;">NO. 12, RCI PARK, JALAN KESIDANG 2,</span><br>
                <span style="font-size: 14px;">KAWASAN PERINDUSTRIAN SUNGAI CHOH,</span><br>
                <span style="font-size: 14px;">48200 SERENDAH, SELANGOR DARUL EHSAN, MALAYSIA.</span><br>
                <span style="font-size: 14px;">H/P:012-386 8164, 012-263 2919</span><br>
                <span style="font-size: 14px;">Service Hotline (HQ-Selangor) : 012-386 8743</span><br>
                <span style="font-size: 14px;">Service Hotline (Penang) : 012-386 8477</span><br>
                <span style="font-size: 14px;">Email add : <a href="mailto:imax.hiten_sales@powercool.com.my">imax.hiten_sales@powercool.com.my</a></span>
            </td>
            <td style="width: 50%; border-bottom: solid 1px black; padding: 0 0 10px 0; vertical-align: text-top;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="font-size: 14px; font-weight: 700;">Delivery Order No.</td>
                        <td style="font-size: 14px; font-weight: 700; width: 5%;">:</td>
                        <td style="font-size: 14px; font-weight: 700; width: 50%;">{{ $sku }}</td>
                    </tr>
                    <tr>
                        <td style="font-size: 14px;">Date</td>
                        <td style="font-size: 14px;">:</td>
                        <td style="font-size: 14px;">{{ $date }}</td>
                    </tr>
                    <tr>
                        <td style="font-size: 14px;">Your P/O No.</td>
                        <td style="font-size: 14px;">:</td>
                        <td style="font-size: 14px;"></td>
                    </tr>
                    <tr>
                        <td style="font-size: 14px;">Your S/O No.</td>
                        <td style="font-size: 14px;">:</td>
                        <td style="font-size: 14px;">
                            @php
                                $skus = [];
                                for ($i=0; $i < count($sale_orders) ;$i++) {
                                    $skus[] = $sale_orders[$i]->sku;
                                }
                            @endphp
                            {{ join(', ', $skus) }}
                        </td>
                    </tr>
                    <tr>
                        <td style="font-size: 14px;">Terms</td>
                        <td style="font-size: 14px;">:</td>
                        <td style="font-size: 14px;">{{ $terms == 'cod' ? 'C.O.D' : $terms . ' Days' }}</td>
                    </tr>
                    <tr>
                        <td style="font-size: 14px;">Salesperson</td>
                        <td style="font-size: 14px;">:</td>
                        <td style="font-size: 14px;">{{ $salesperson->name }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <table style="width: 100%; font-family: sans-serif; border-collapse: collapse; padding: 0 0 25px 0;">
        <tr>
            <td colspan="2" style="font-size: 18px; font-weight: 700; width: 65%; padding: 15px 35px 10px 0; text-align: center;">DELIVERY ORDER</td>
        </tr>
        <tr>
            <td style="padding: 0 35px 0 0;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="font-size: 16px; font-weight: 700;">Billing Address</td>
                    </tr>
                    <tr>
                        <td style="font-size: 14px;" colspan="2">
                            {{ $customer->company_name }}<br>
                            {{ $billing_address->address ?? '' }}<br>
                            {{ $billing_address->city ?? '' }}<br>
                            {{ $billing_address->zip_code ?? '' }}<br>
                            {{ $billing_address->state ?? '' }}<br>
                        </td>
                    </tr>
                    <tr>
                        <td style="font-size: 14px; padding: 10px 0 0 0;">TEL: {{ $customer->phone }}</td>
                        <td style="font-size: 14px; padding: 10px 0 0 0; text-align: start;">FAX: </td>
                    </tr>
                </table>
            </td>
            <td style="padding: 0 35px 0 0;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="font-size: 16px; font-weight: 700;">Delivery Address</td>
                    </tr>
                    <tr>
                        <td style="font-size: 14px;" colspan="2">
                            {{ $customer->company_name ?? '' }}<br>
                            {{ $delivery_address->address ?? '' }}<br>
                            {{ $delivery_address->city ?? '' }}<br>
                            {{ $delivery_address->zip_code ?? '' }}<br>
                            {{ $delivery_address->state ?? '' }}<br>
                        </td>
                    </tr>
                    <tr>
                        <td style="font-size: 14px; padding: 10px 0 0 0;">TEL: {{ $customer->phone }}</td>
                        <td style="font-size: 14px; padding: 10px 0 0 0; text-align: start;">FAX: </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <!-- Item -->
    <table style="width: 100%; font-family: sans-serif; border-collapse: collapse;">
        <tr>
            <td style="font-size: 14px; border-top: solid 1px black; border-bottom: solid 1px black; padding: 5px 0; text-align: left; width: 5%;">Item</td>
            <td style="font-size: 14px; border-top: solid 1px black; border-bottom: solid 1px black; text-align: left; width: 10%;">Stock Code</td>
            <td style="font-size: 14px; border-top: solid 1px black; border-bottom: solid 1px black; text-align: left; width: 45%;">Description</td>
            <td style="font-size: 14px; border-top: solid 1px black; border-bottom: solid 1px black; text-align: right; width: 5%;">FOC Qty</td>
            <td style="font-size: 14px; border-top: solid 1px black; border-bottom: solid 1px black; text-align: right; width: 5%;">Qty</td>
        </tr>
        @php
            $total = 0;
        @endphp
        <!-- Product List -->
        @foreach ($dopcs as $key => $prod)
            <tr>
                <td style="font-size: 14px; padding: 5px 0; text-align: left;">{{ $key + 1 }}</td>
                <td style="font-size: 14px; text-align: left;">{{ $prod->productChild->sku }}</td>
                <td style="font-size: 14px; text-align: left;">{{ $prod->productChild->parent->desc }}</td>
                <td style="font-size: 14px; text-align: right;"></td>
                <td style="font-size: 14px; text-align: right;">1</td>
            </tr>
            @php
                $total += 1;
            @endphp
        @endforeach
        <!-- Remark -->
        @foreach ($sale_orders as $key => $so)
            @if ($so->remark != null)
                <tr>
                    <td colspan="2"></td>
                    <td colspan="2" style="font-size: 14px; padding: 15px 0;"><span style="font-weight: 700;">REMARK:</span><br>{{ $so->remark }}</td>
                    <td></td>
                </tr>
            @endif
        @endforeach
    </table>
    <!-- Item Summary -->
    <table style="width: 100%; font-family: sans-serif; border-collapse: collapse;">
        <tr>
            <td style="font-size: 14px; padding: 10px 0 0 0; border-top: solid 1px black; width: 60%;">Goods sold are neither returnable nor refundable. Otherwise a cancellation fee of 20% on purchase price will be imposed.</td>
            <td style="font-size: 14px; font-weight: 700; text-align: right; padding: 10px 0 0 0; border-top: solid 1px black; vertical-align: text-top;">Total {{ $total }}</td>
        </tr>
    </table>
    <!-- Footer -->
    <table style="width: 100%; font-family: sans-serif; border-collapse: collapse;">
        <tr>
            <td style="font-size: 14px; padding: 25px 0 75px 0;" colspan="4">For : HI-TEN TRADING SDN BHD</td>
            <td style="font-size: 14px; padding: 25px 0 75px 0; text-align: center;" colspan="3">GOODS RECEIVED IN GOOD ORDER & CONDITION.</td>
        </tr>
        <tr>
            <td style="width: 20%; font-size: 14px; text-align: left; border-top: solid 1px black; padding: 10px 0 0 0; font-weight: 700; vertical-align: text-top;">Authorised Signature</td>
            <td style="width: 10px;"></td>
            <td style="width: 20%; font-size: 14px; text-align: left; border-top: solid 1px black; padding: 10px 0 0 0; font-weight: 700; vertical-align: text-top;">Goods Prepared Signature</td>
            <td style="width: 10px;"></td>
            <td style="width: 20%; font-size: 14px; text-align: left; border-top: solid 1px black; padding: 10px 0 0 0; font-weight: 700; vertical-align: text-top;">Recipient's Chop & Signature<br>Name:<br>IC:</td>
            <td style="width: 10px;"></td>
            <td style="width: 20%; font-size: 14px; text-align: left; border-top: solid 1px black; padding: 10px 0 0 0; font-weight: 700; vertical-align: text-top;">Driver Signature</td>
        </tr>
    </table>
    
</body>
</html>