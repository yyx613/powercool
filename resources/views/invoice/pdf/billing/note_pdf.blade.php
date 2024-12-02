<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice | {{ $sku }}</title>
</head>
<body>
    @php
        $svgQrCode = QrCode::size(100)->generate($validationLink);
        $base64QrCode = base64_encode($svgQrCode);
    @endphp

    <!-- Header -->
    <div style="display: flex; justify-content: flex-end; padding: 10px;">
        <img src="data:image/svg+xml;base64,{{ $base64QrCode }}" alt="QR Code">
    </div>
    <!-- Header -->
    <table style="width: 100%; font-family: sans-serif; border-collapse: collapse;">
        <tr>
            <td style="width: 60%; border-bottom: solid 1px black; padding: 0 0 10px 0;">
                <span style="font-size: 18px; font-weight: 700;">POWER COOL EQUIPMENTS (M) SDN BHD <span style="font-size: 14px; font-weight: 100;">(383045-D)</span></span><br>
                <span style="font-size: 14px;">NO:12,RCI PARK,JALAN KESIDANG 2,</span><br>
                <span style="font-size: 14px;">KAWASAN PERINDUSTRIAN SUNGAI CHOH,</span><br>
                <span style="font-size: 14px;">48200 SERENDAH,SELANGOR.</span><br>
                <span style="font-size: 14px;">Tel: 603-6094 1122 Service Hotline: 012-386 8743</span><br>
                <span style="font-size: 14px;">Email : enquiry@powercool.com.my</span><br>
                <span style="font-size: 14px;">Sales Tax ID No : B16-1809-22000036</span><br>
            </td>
            <td style="width: 40%; border-bottom: solid 1px black; padding: 0 0 10px 0; vertical-align: text-top;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="font-size: 14px; width: 45%; font-weight: 700;">Invoice No.</td>
                        <td style="font-size: 14px; width: 10%;">:</td>
                        <td style="font-size: 14px; font-weight: 700;">{{ $sku }}</td>
                    </tr>
                    <tr>
                        <td style="font-size: 14px;">Date</td>
                        <td style="font-size: 14px;">:</td>
                        <td style="font-size: 14px;">{{ $date }}</td>
                    </tr>
                    <tr>
                        <td style="font-size: 14px;">Your Ref</td>
                        <td style="font-size: 14px;">:</td>
                        <td style="font-size: 14px;"></td>
                    </tr>
                    <tr>
                        <td style="font-size: 14px;">Terms</td>
                        <td style="font-size: 14px;">:</td>
                        <td style="font-size: 14px;">{{ $terms }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table style="width: 100%; font-family: sans-serif; border-collapse: collapse; padding: 0 0 25px 0;">
        <tr>
            <td colspan="2" style="font-size: 18px; font-weight: 700; width: 65%; padding: 15px 35px 10px 0; text-align: center;">{{ $type }}</td>
        </tr>
        <tr>
            <td style="padding: 0 35px 0 0;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="font-size: 16px; font-weight: 700;">Billing Address</td>
                    </tr>
                    <tr>
                        <td style="font-size: 14px;" colspan="2">
                            HI-TEN TRADING SDN BHD<br>
                            NO 22, RCI PARK JALAN KESIDANG 2<br>
                            KAWASAN PERINDUSTRIAN SG CHOH<br>
                            48200 SERENDAH SELANGOR<br>
                            <br>
                            MS.LIM
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
                        <td style="font-size: 16px; font-weight: 700;">Delivery Address</td>
                    </tr>
                    <tr>
                        <td style="font-size: 14px; height: 100px;" colspan="2">
                        </td>
                    </tr>
                    <tr>
                        <td style="font-size: 14px; padding: 10px 0 0 0;">TEL: </td>
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
            <td style="font-size: 14px; border-top: solid 1px black; border-bottom: solid 1px black; text-align: left; width: 10%;">Tax Code</td>
            <td style="font-size: 14px; border-top: solid 1px black; border-bottom: solid 1px black; text-align: left; width: 30%;">Description</td>
            <td style="font-size: 14px; border-top: solid 1px black; border-bottom: solid 1px black; text-align: right; width: 5%;">FOC Qty</td>
            <td style="font-size: 14px; border-top: solid 1px black; border-bottom: solid 1px black; text-align: right; width: 5%;">Qty</td>
            <td style="font-size: 14px; border-top: solid 1px black; border-bottom: solid 1px black; text-align: right; width: 5%;">UOM</td>
            <td style="font-size: 14px; border-top: solid 1px black; border-bottom: solid 1px black; text-align: right; width: 10%;">U/Price<br>(RM)</td>
            <td style="font-size: 14px; border-top: solid 1px black; border-bottom: solid 1px black; text-align: right; width: 10%;">Discount<br>(RM)</td>
            <td style="font-size: 14px; border-top: solid 1px black; border-bottom: solid 1px black; text-align: right; width: 10%;">Total<br>(RM)</td>
        </tr>
        {{-- @php
            $total = 0;
        @endphp --}}
        
        @foreach ($productDetails as $prod)
            <tr>
                <td style="font-size: 14px; padding: 5px 0; text-align: left;">{{ $prod['index'] }}</td>
                <td style="font-size: 14px; text-align: left;"></td>
                <td style="font-size: 14px; text-align: left;">{{ $prod['model_name'] }}</td>
                <td style="font-size: 14px; text-align: right;"></td>
                <td style="font-size: 14px; text-align: right;">{{ $prod['qty'] }}</td>
                <td style="font-size: 14px; text-align: right;">{{ $prod['uom'] }}</td>
                <td style="font-size: 14px; text-align: right;">{{ number_format($prod['unit_price'], 2) }}</td>
                <td style="font-size: 14px; text-align: right;">0</td>

                <td style="font-size: 14px; text-align: right;">{{ number_format($prod['subtotal'], 2) }}</td>
            </tr>
        @endforeach
        <tr>
            <td colspan="8" style="text-align: right; font-weight: bold;">Total:</td>
            <td style="text-align: right; font-weight: bold;">{{ number_format($total, 2) }}</td>
        </tr>
    </table>
    <!-- Item Summary -->
    <table style="width: 100%; font-family: sans-serif; border-collapse: collapse;">
        <tr>
            <td style="font-size: 14px; width: 60%; padding: 10px 0 0 0; border-top: solid 1px black; text-transform: uppercase;">{{ priceToWord(number_format($total, 2)) }}</td>
            <td style="font-size: 14px; font-weight: 700; text-align: right; padding: 10px 0 0 0; border-top: solid 1px black; vertical-align: text-top;">Sub Total (Excluding SST) <span style="border: solid 1px black; padding: 2.5px 10px;">{{ number_format($total, 2) }}</span></td>
        </tr>
        <tr>
            <td style="font-size: 14px; font-weight: 700; text-align: right; padding: 10px 0 0 0; width: 15%; vertical-align: text-top;" colspan="2">Sales Tax @ 10% on 0.00 <span style="border: solid 1px black; padding: 2.5px 10px;">{{ number_format(0, 2) }}</span></td>
        </tr>
        <tr>
            <td style="font-size: 14px; font-weight: 700; text-align: right; padding: 10px 0 10px 0; width: 15%; vertical-align: text-top;" colspan="2">Total (Inclusive of SST) <span style="border: solid 1px black; padding: 2.5px 10px;">{{ number_format($total, 2) }}</span></td>
        </tr>
    </table>
    <!-- Footer -->
    <table style="width: 100%; font-family: sans-serif; border-collapse: collapse;">
        <tr>
            <td style="font-size: 14px; padding: 25px 0 0 0; border-top: solid 1px black;" colspan="3">Notes:</td>
        </tr>
        <tr>
            <td style="font-size: 12px; padding: 0 0 75px 0;" colspan="3">
                1. All cheques should be crossed and made payable to POWER COOL EQUIPMENTS (M) SDN BHD<br>
                2. Please remit your payment to : <span style="font-weight: 700;">Public Bank Ac No.: 314 1967 510</span><br>
                3. The Company reserves the right to charge interest 1.5% per month on overdue accounts.<br>
                4. Goods sold are not returnable & refundable. A cancellation fee of 20% on purchase price will be imposed.<br>
                5. Any queries or complaints regarding this invoice must be made within 7 days from date hereof,
                otherwise any discrepancy will not be entertained.
            </td>
        </tr>
        <tr>
            <td style="font-size: 11px; padding: 0 0 50px 0; text-align: center; font-weight: 700;">POWER COOL EQUIPMENTS (M) SDN BHD</td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td style="font-size: 12px; text-align: center; width: 33%; border-top: solid 1px black; padding: 10px 0 0 0; font-weight: 700;">Authorised Signature</td>
            <td></td>
            <td></td>
        </tr>
    </table>
    
</body>
</html>