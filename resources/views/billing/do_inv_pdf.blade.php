<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice | {{ $sku }}</title>
</head>
<body>
    <!-- Header -->
    <table style="width: 100%; font-family: sans-serif; border-collapse: collapse; border-bottom: solid 1px black;">
        <tr>
            <td style="width: 15%;"></td>
            <td style="padding: 0 0 10px 0;">
                <span style="font-size: 18px; font-weight: 700;">POWER COOL EQUIPMENTS (M) SDN BHD <span style="font-size: 14px; font-weight: 100;">(383045-D)</span></span><br>
                <span style="font-size: 14px;">NO:12,RCI PARK,JALAN KESIDANG 2,</span><br>
                <span style="font-size: 14px;">KAWASAN PERINDUSTRIAN SUNGAI CHOH,</span><br>
                <span style="font-size: 14px;">48200 SERENDAH,SELANGOR.</span><br>
                <span style="font-size: 14px;">Tel: 603-6094 1122 Service Hotline: 012-386 8743</span><br>
                <span style="font-size: 14px;">Email : enquiry@powercool.com.my</span><br>
                <span style="font-size: 14px;">Sales Tax ID No : B16-1809-22000036</span><br>
            </td>
            <td style="width: 15%;"></td>
        </tr>
    </table>

    <table style="width: 100%; font-family: sans-serif; border-collapse: collapse; padding: 0 0 25px 0;">
        <tr>
            <td style="font-size: 18px; font-weight: 700; text-align: right; {{ $is_do ? 'padding: 5px 20px 20px 0;' : 'padding: 5px 50px 20px 0;' }}">{{ $is_do ? 'DELIVERY ORDER' : 'INVOICE' }}</td>
            <td style="font-size: 14px; font-weight: 700; padding: 5px 0 20px 0; text-align: right;">No.: {{ $sku }}</td>
        </tr>
        <tr>
            <td style="padding: 0 35px 0 0;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="font-size: 14px;" colspan="2">
                            HI-TEN TRADING SDN BHD<br>
                            MS.LIM
                            NO 22, RCI PARK JALAN KESIDANG 2<br>
                            KAWASAN PERINDUSTRIAN SG CHOH<br>
                            48200 SERENDAH SELANGOR<br>
                        </td>
                    </tr>
                    <tr>
                        <td style="font-size: 14px; padding: 10px 0 0 0;">TEL: 012-2632919</td>
                        <td style="font-size: 14px; padding: 10px 0 0 0; text-align: start;">FAX: </td>
                    </tr>
                </table>
            </td>
            <td style="padding: 0 35px 0 0;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td>
                            <table style="width: 100%; border-collapse: collapse;"> 
                                <tr>
                                    <td style="font-size: 14px;">Your Ref</td>
                                    <td style="font-size: 14px;">:</td>
                                    <td style="font-size: 14px;">{{ $your_ref }}</td>
                                </tr>
                                <tr>
                                    <td style="font-size: 14px;">Our D/O No.</td>
                                    <td style="font-size: 14px;">:</td>
                                    <td style="font-size: 14px;">{{ $our_do_no }}</td>
                                </tr>
                                <tr>
                                    <td style="font-size: 14px;">Terms</td>
                                    <td style="font-size: 14px;">:</td>
                                    <td style="font-size: 14px;">{{ $term }}</td>
                                </tr>
                                <tr>
                                    <td style="font-size: 14px;">Date</td>
                                    <td style="font-size: 14px;">:</td>
                                    <td style="font-size: 14px;">{{ $date }}</td>
                                </tr>
                            </table>
                        </td>
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
            <td style="font-size: 14px; border-top: solid 1px black; border-bottom: solid 1px black; text-align: left; width: 30%;">Description</td>
            <td style="font-size: 14px; border-top: solid 1px black; border-bottom: solid 1px black; text-align: right; width: 5%;">Qty</td>
            <td style="font-size: 14px; border-top: solid 1px black; border-bottom: solid 1px black; text-align: right; width: 5%;">UOM</td>
            <td style="font-size: 14px; border-top: solid 1px black; border-bottom: solid 1px black; text-align: right; width: 10%;">U/Price<br>(RM)</td>
            <td style="font-size: 14px; border-top: solid 1px black; border-bottom: solid 1px black; text-align: right; width: 10%;">Discount<br>(RM)</td>
            <td style="font-size: 14px; border-top: solid 1px black; border-bottom: solid 1px black; text-align: right; width: 10%;">Total<br>(RM)</td>
        </tr>
        @php
            $total = 0;
        @endphp
        @foreach ($dopcs as $key => $prod)
            <tr>
                <td style="font-size: 14px; padding: 5px 0; text-align: left;">{{ $key + 1 }}</td>
                <td style="font-size: 14px; text-align: left;">{{ $prod->productChild->sku }}</td>
                <td style="font-size: 14px; text-align: left;">{{ $prod->productChild->parent->model_name }}</td>
                <td style="font-size: 14px; text-align: right;">1</td>
                <td style="font-size: 14px; text-align: right;">{{ $prod->doProduct->saleProduct->uom }}</td>
                <td style="font-size: 14px; text-align: right;">
                    @php
                        $unit_price = $prod->doProduct->saleProduct->unit_price;
                        if ($custom_unit_price['custom-unit-price-' . $prod->productChild->parent->id] != null) {
                            $unit_price = $custom_unit_price['custom-unit-price-' . $prod->productChild->parent->id];
                        }
                    @endphp
                    {{ number_format($unit_price, 2) }}
                </td>
                <td style="font-size: 14px; text-align: right;">{{ number_format(0, 2) }}</td>
                <td style="font-size: 14px; text-align: right;">{{ number_format($unit_price, 2) }}</td>
            </tr>
            @php
                $total += ($unit_price);
            @endphp
        @endforeach
    </table>
    <!-- Item Summary -->
    <table style="width: 100%; font-family: sans-serif; border-collapse: collapse;">
        <tr>
            <td style="font-size: 14px; width: 60%; padding: 10px 0 20px 0; border-top: solid 1px black; text-transform: uppercase;">{{ priceToWord(number_format($total, 2)) }}</td>
            <td style="font-size: 14px; font-weight: 700; text-align: right; padding: 10px 0 20px 0; border-top: solid 1px black; vertical-align: text-top;">Total <span style="border: solid 1px black; padding: 2.5px 10px;">{{ number_format($total, 2) }}</span></td>
        </tr>
    </table>
    <!-- Footer -->
    <table style="width: 100%; font-family: sans-serif; border-collapse: collapse;">
        <tr>
            <td style="font-size: 14px; padding: 25px 0 0 0;" colspan="3">Notes:</td>
        </tr>
        <tr>
            <td style="font-size: 12px; padding: 0 0 75px 0;" colspan="3">
                1. All cheques should be crossed and made payable to POWER COOL EQUIPMENTS (M) SDN BHD<br>
                2.  Goods sold are neither returnable nor refundable. Otherwise a cancellation fee of 20% on purchase price will be imposed.
            </td>
        </tr>
        <tr>
            <td style="font-size: 12px; text-align: center; width: 33%; border-top: solid 1px black; padding: 10px 0 0 0; font-weight: 700;">Authorised Signature</td>
            <td></td>
            <td></td>
        </tr>
    </table>

</body>
</html>
