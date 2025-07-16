<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice | {{ $sku }}</title>
</head>
<style>
    @page {
        margin: 200px 25px 50px 25px;
    }

    header {
        position: fixed;
        top: -175px;
        left: 0px;
        right: 0px;
    }
</style>

<body>
    <!-- Header -->
    <header>
        <table style="width: 100%; font-family: sans-serif; border-collapse: collapse;">
            <tr>
                <td style="width: 70%; border-bottom: solid 1px black; padding: 0 50px 10px 0; text-align: center;">
                    <span style="font-size: 18px;">HI-TEN TRADING SDN BHD <span
                            style="font-size: 14px;">(709676-X)</span></span><br>
                    <span style="font-size: 14px;">NO. 12, RCI PARK, JALAN KESIDANG 2,</span><br>
                    <span style="font-size: 14px;">KAWASAN PERINDUSTRIAN SUNGAI CHOH,</span><br>
                    <span style="font-size: 14px;">48200 SERENDAH, SELANGOR DARUL EHSAN, MALAYSIA.</span><br>
                    <span style="font-size: 14px;">H/P:012-386 8164, 03-6094 1122</span><br>
                    <span style="font-size: 14px;">Service Hotline (HQ-Selangor) : 012-386 8743</span><br>
                    <span style="font-size: 14px;">Email add : <a
                            href="mailto:enquiry@powercool.com.my">enquiry@powercool.com.my</a></span><br>
                    <span style="font-size: 14px;">Website : <a
                            href="imaxrefrigerator.com.my">imaxrefrigerator.com.my</a></span>
                </td>
                <td style="width: 50%; border-bottom: solid 1px black; padding: 0 0 10px 0; vertical-align: text-top;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="font-size: 14px; font-weight: 700;">No.</td>
                            <td style="font-size: 14px; font-weight: 700; width: 5%;">:</td>
                            <td style="font-size: 14px; font-weight: 700; width: 50%;">{{ $sku }}</td>
                        </tr>
                        <tr>
                            <td style="font-size: 14px;">Date</td>
                            <td style="font-size: 14px;">:</td>
                            <td style="font-size: 14px;">{{ $date }}</td>
                        </tr>
                        <tr>
                            <td style="font-size: 14px;">D/O No.</td>
                            <td style="font-size: 14px;">:</td>
                            <td style="font-size: 14px;">{{ $do_sku }}</td>
                        </tr>
                        <tr>
                            <td style="font-size: 14px;">Terms</td>
                            <td style="font-size: 14px;">:</td>
                            <td style="font-size: 14px;">{{ $terms == 'cod' ? 'C.O.D' : $terms . ' Days' }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </header>

    <main>
        <table style="width: 100%; font-family: sans-serif; border-collapse: collapse; padding: 0 0 25px 0;">
            <tr>
                <td colspan="2"
                    style="font-size: 18px; font-weight: 700; width: 65%; padding: 15px 35px 10px 0; text-align: center;">
                    INVOICE</td>
            </tr>
            <tr>
                <td style="padding: 0 35px 0 0;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="font-size: 14px; font-weight: 700; padding: 0 0 10px 0;">{{ $customer->name }}
                            </td>
                        </tr>
                        <tr>
                            <td style="font-size: 14px;" colspan="2">
                                {{ $customer->company_name }}<br>
                                {{ $billing_address->address1 ?? '' }}<br>
                                {{ $billing_address->address2 ?? '' }}<br>
                                {{ $billing_address->address3 ?? '' }}<br>
                                {{ $billing_address->address4 ?? '' }}<br>
                            </td>
                        </tr>
                        <tr>
                            <td style="font-size: 14px; padding: 10px 0 0 0;">TEL: {{ $customer->phone }}</td>
                        </tr>
                    </table>
                </td>
                <td style="padding: 0 35px 0 0; vertical-align: text-bottom;"></td>
            </tr>
        </table>
        <!-- Item -->
        <table style="width: 100%; font-family: sans-serif; border-collapse: collapse;">
            <tr>
                <td
                    style="font-size: 14px; border-top: solid 1px black; border-bottom: solid 1px black; padding: 5px 0; text-align: left; width: 5%;">
                    Item</td>
                <td
                    style="font-size: 14px; border-top: solid 1px black; border-bottom: solid 1px black; text-align: left; width: 10%;">
                    Stock Code</td>
                <td
                    style="font-size: 14px; border-top: solid 1px black; border-bottom: solid 1px black; text-align: left; width: 35%;">
                    Description</td>
                <td
                    style="font-size: 14px; border-top: solid 1px black; border-bottom: solid 1px black; text-align: right; width: 5%;">
                    Qty</td>
                <td
                    style="font-size: 14px; border-top: solid 1px black; border-bottom: solid 1px black; text-align: right; width: 10%;">
                    U/Price<br>(RM)</td>
                <td
                    style="font-size: 14px; border-top: solid 1px black; border-bottom: solid 1px black; text-align: right; width: 10%;">
                    Discount<br>(RM)</td>
                <td
                    style="font-size: 14px; border-top: solid 1px black; border-bottom: solid 1px black; text-align: right; width: 10%;">
                    Promotion<br>(RM)</td>
                <td
                    style="font-size: 14px; border-top: solid 1px black; border-bottom: solid 1px black; text-align: right; width: 10%;">
                    Total<br>(RM)</td>
            </tr>
            @foreach ($products as $key => $prod)
                <tr>
                    <td style="font-size: 14px; text-align: left; padding: {{ $key == 0 ? '0' : '20px' }} 0 0 0;">{{ $key + 1 }}</td>
                    <td style="font-size: 14px; text-align: left; padding: {{ $key == 0 ? '0' : '20px' }} 0 0 0;">{{ $prod['stock_code'] }}</td>
                    <td style="font-size: 14px; text-align: left; padding: {{ $key == 0 ? '0' : '20px' }} 0 0 0;">{{ $prod['model_name'] }}</td>
                    <td style="font-size: 14px; text-align: right; padding: {{ $key == 0 ? '0' : '20px' }} 0 0 0;">{{ $prod['qty'] }}</td>
                    <td style="font-size: 14px; text-align: right; padding: {{ $key == 0 ? '0' : '20px' }} 0 0 0;">{{ $prod['unit_price'] }}</td>
                    <td style="font-size: 14px; text-align: right; padding: {{ $key == 0 ? '0' : '20px' }} 0 0 0;">{{ $prod['discount'] }}</td>
                    <td style="font-size: 14px; text-align: right; padding: {{ $key == 0 ? '0' : '20px' }} 0 0 0;">{{ $prod['promotion'] }}</td>
                    <td style="font-size: 14px; text-align: right; padding: {{ $key == 0 ? '0' : '20px' }} 0 0 0;">{{ $prod['total'] }}</td>
                </tr>
            @endforeach
        </table>
        <!-- Item Summary -->
        <table style="width: 100%; font-family: sans-serif; border-collapse: collapse;">
            <tr>
                <td
                    style="font-size: 14px; padding: 10px 0 0 0; border-top: solid 1px black; text-transform: uppercase;">
                    {{ priceToWord(number_format($overall_total, 2)) }}</td>
                <td
                    style="font-size: 14px; font-weight: 700; text-align: right; padding: 10px 0 0 0; border-top: solid 1px black; width: 15%; vertical-align: text-top;">
                    Total {{ number_format($overall_total, 2) }}</td>
            </tr>
        </table>
    </main>

    <footer>
        <!-- Footer -->
        <table style="width: 100%; font-family: sans-serif; border-collapse: collapse;">
            <tr>
                <td style="font-size: 14px; padding: 25px 0 0 0;" colspan="3">Note:</td>
            </tr>
            <tr>
                <td style="font-size: 14px; padding: 0 0 75px 0;" colspan="3">
                    1. All cheques should be crossed and made payable to <span style="font-weight: 700;">HI-TEN TRADING
                        SDN
                        BHD</span><br>
                    2. Please remit your payment to : <span style="font-weight: 700;">PUBLIC BANK Account No.: 3983 23
                        3530
                        CIMB Account No.: 8603 16 3872</span><br>
                    3. The Company reserves the right to charge interest 1.5% daily on overdue accounts.<br>
                    4. Goods sold are not returnable & refundable. A cancellation fee of 20% on purchase price will be
                    imposed.<br>
                    5. Any queries or complaints regarding this invoice must be made within 7 days from date hereof,
                    otherwise any discrepancy will not be entertained.<br>
                    6. For more information about our policies , please visit our website at
                    https://imaxrefrigerator.com.my/warranty-policy/.<br>
                    7. Request E-invoice after 72hrs which original invoice have validated by IRB will be charge 5% of
                    the
                    total invoice amount.<br>
                    8. Company will not obligation on those customers are not require to issue E-invoice.<br>
                </td>
            </tr>
            <tr>
                <td style="font-size: 14px; padding: 0 0 50px 0; text-align: center; font-weight: 700;">HI-TEN TRADING
                    SDN
                    BHD</td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td
                    style="font-size: 16px; text-align: center; width: 33%; border-top: solid 1px black; padding: 10px 0 0 0; font-weight: 700;">
                    Authorised Signature</td>
                <td></td>
                <td></td>
            </tr>
        </table>
    </footer>

</body>

</html>
