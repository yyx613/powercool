<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GRN</title>
</head>
<body>
    <!-- Header -->
    <table style="width: 100%; font-family: sans-serif; border-collapse: collapse;">
        <tr>
            <td style="width: 70%; border-bottom: solid 1px black; padding: 0 0 10px 0;">
                <span style="font-size: 18px; font-weight: 700;">POWER COOL EQUIPMENTS (M) SDN BHD <span style="font-size: 14px; font-weight: 100;">(383045-D)</span></span><br>
                <span style="font-size: 14px;">NO:12,RCI PARK,JALAN KESIDANG 2,</span><br>
                <span style="font-size: 14px;">KAWASAN PERINDUSTRIAN SUNGAI CHOH,</span><br>
                <span style="font-size: 14px;">48200 SERENDAH,SELANGOR.</span><br>
                <span style="font-size: 14px;">Tel: 603-6094 1122 Service Hotline: 012-386 8743</span><br>
                <span style="font-size: 14px;">Email : enquiry@powercool.com.my</span><br>
                <span style="font-size: 14px;">Sales Tax ID No : B16-1809-22000036</span><br>
            </td>
            <td style="width: 30%; border-bottom: solid 1px black; padding: 0 0 10px 0; vertical-align: text-top;">
            </td>
        </tr>
    </table>

    <table style="width: 100%; font-family: sans-serif; border-collapse: collapse; padding: 0 0 25px 0;">
        <tr>
            <td style="font-size: 18px; font-weight: 700; width: 65%; padding: 15px 35px 10px 0; text-align: right;">GOODS RECEIVED NOTE</td>
            <td style="font-size: 14px; font-weight: 700; width: 35%; padding: 15px 35px 10px 0;">No : {{ $sku }}</td>
        </tr>
        <tr>
            <td style="padding: 0 35px 0 0;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="font-size: 14px; width: 50%;" colspan="2">
                            {{ $supplier->company_name }}<br>
                            {{ $supplier->location }}
                        </td>
                    </tr>
                    <tr>
                        <td style="font-size: 14px; padding: 10px 0 0 0; width: 15%;">TEL: {{ $supplier->phone }}</td>
                        <td style="font-size: 14px; padding: 10px 0 0 0; width: 15%; text-align: start;">FAX: </td>
                    </tr>
                </table>
            </td>
            <td style="width: 30%; vertical-align: text-top;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="font-size: 14px;">Our P/O No</td>
                        <td style="font-size: 14px;">:</td>
                        <td style="font-size: 14px;">{{ $our_po_no }}</td>
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
                    <tr>
                        <td style="font-size: 14px;">Our P/O Date</td>
                        <td style="font-size: 14px;">:</td>
                        <td style="font-size: 14px;">{{ $our_po_date }}</td>
                    </tr>
                    <tr>
                        <td style="font-size: 14px;">Store</td>
                        <td style="font-size: 14px;">:</td>
                        <td style="font-size: 14px;">{{ $store }}</td>
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
            <td style="font-size: 14px; border-top: solid 1px black; border-bottom: solid 1px black; text-align: left; width: 10%;">Stock Code</td>
            <td style="font-size: 14px; border-top: solid 1px black; border-bottom: solid 1px black; text-align: left; width: 40%;">Description</td>
            <td style="font-size: 14px; border-top: solid 1px black; border-bottom: solid 1px black; text-align: center; width: 10%;">Qty</td>
            <td style="font-size: 14px; border-top: solid 1px black; border-bottom: solid 1px black; text-align: center; width: 10%;">FOC</td>
            <td style="font-size: 14px; border-top: solid 1px black; border-bottom: solid 1px black; text-align: center; width: 10%;">UOM</td>
            <td style="font-size: 14px; border-top: solid 1px black; border-bottom: solid 1px black; text-align: right; width: 15%;">U/Price<br>(RM)</td>
            <td style="font-size: 14px; border-top: solid 1px black; border-bottom: solid 1px black; text-align: right; width: 15%;">Disc<br>(RM)</td>
            <td style="font-size: 14px; border-top: solid 1px black; border-bottom: solid 1px black; text-align: right; width: 15%;">Total<br>(RM)</td>
        </tr>
        @php
            $total = 0;
        @endphp
        @foreach ($grns as $key => $grn)
            <tr>
                <td style="font-size: 14px; padding: 5px 0; text-align: left;">{{ $key + 1 }}</td>
                <td style="font-size: 14px; text-align: left;"></td>
                <td style="font-size: 14px; text-align: left;">{{  $grn->product->sku }}</td>
                <td style="font-size: 14px; text-align: left;">{{ $grn->product->model_desc }}</td>
                <td style="font-size: 14px; text-align: center;">{{ $grn->qty }}</td>
                <td style="font-size: 14px; text-align: center;"></td>
                <td style="font-size: 14px; text-align: center;">{{ $grn->uom }}</td>
                <td style="font-size: 14px; text-align: right;">{{ number_format($grn->unit_price, 2) }}</td>
                <td style="font-size: 14px; text-align: right;">{{ number_format(0, 2) }}</td>
                <td style="font-size: 14px; text-align: right;">{{ number_format($grn->total_price, 2) }}</td>
            </tr>
            @php
                $total += $grn->total_price;
            @endphp
        @endforeach
        <tr>
            <td colspan="3" style="height: 50px;"></td>
            <td style="font-size: 14px; text-align: left;">Delivery Date: As Soon As Possible</td>
            <td colspan="6"></td>
        </tr>
    </table>
    <!-- Item Summary -->
    <table style="width: 100%; font-family: sans-serif; border-collapse: collapse;">
        <tr>
            <td style="font-size: 14px; padding: 10px 0 0 0; border-top: solid 1px black; text-transform: uppercase;">{{ priceToWord(number_format($total, 2)) }}</td>
            <td style="font-size: 14px; font-weight: 700; text-align: right; padding: 10px 0 0 0; border-top: solid 1px black;">Sub Total (Excluding SST) <span style="border: solid 1px black; padding: 2.5px 10px;">{{ number_format($total, 2) }}</span></td>
        </tr>
        <tr>
            <td colspan="2" style="font-size: 14px; font-weight: 700; text-align: right; padding: 10px 0 0 0;">Tax @ 0% on <span style="border: solid 1px black; padding: 2.5px 10px;">{{ number_format(0, 2) }}</span></td>
        </tr>
        <tr>
            <td colspan="2" style="font-size: 14px; font-weight: 700; text-align: right; padding: 10px 0 0 0;">Total (Inclusive of SST) <span style="border: solid 1px black; padding: 2.5px 10px;">{{ number_format($total, 2) }}</span></td>
        </tr>
    </table>
    <table style="width: 100%; font-family: sans-serif; border-collapse: collapse; height: 150px; margin: 10px 0 0 0;">
        <tr>
            <td style="border-top: solid 1px black; font-size: 14px; font-weight: 700; text-align: right; padding: 10px 0 0 0;">GST Summary</td>
            <td style="border-top: solid 1px black; font-size: 14px; font-weight: 700; text-align: right; padding: 10px 0 0 0;">Amount (RM)</td>
            <td style="border-top: solid 1px black; font-size: 14px; font-weight: 700; text-align: right; padding: 10px 0 0 0;">Tax (RM)</td>
        </tr>
    </table>

    <!-- Footer -->
    <table style="width: 100%; font-family: sans-serif; border-collapse: collapse;">
        <tr>
            <td style="font-size: 16px; text-align: center; width: 33%; border-top: solid 1px black; padding: 10px 0 0 0;">Autorised Signature</td>
            <td></td>
        </tr>
    </table>
    
</body>
</html>