<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quotation | {{ $sale->sku }}</title>
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
                <td style="width: 33.33%; border-bottom: solid 1px black; padding: 0 0 10px 0; text-align: center;">
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
            </tr>
        </table>
    </header>

    <main>
        <table style="width: 100%; font-family: sans-serif; border-collapse: collapse; padding: 0 0 25px 0;">
            <tr>
                <td colspan="2"
                    style="font-size: 18px; font-weight: 700; width: 65%; padding: 15px 35px 10px 0; text-align: right;">
                    QUOTATION</td>
                <td style="font-size: 14px; font-weight: 700; width: 35%; padding: 15px 0 10px 0; text-align: center;">
                    No. :
                    {{ $sale->sku }}</td>
            </tr>
            <tr>
                <td colspan="2" style="padding: 0 35px 0 0;">
                    <table style="width: 100%; border-collapse: collapse;">
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
                <td>
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="font-size: 14px; width: 30%;">Your Ref</td>
                            <td style="font-size: 14px; width: 10%;">:</td>
                            <td style="font-size: 14px;">{{ $sale->reference }}</td>
                        </tr>
                        <tr>
                            <td style="font-size: 14px;">From</td>
                            <td style="font-size: 14px;">:</td>
                            <td style="font-size: 14px;">{{ $sale->quo_from }}</td>
                        </tr>
                        <tr>
                            <td style="font-size: 14px;">C. C.</td>
                            <td style="font-size: 14px;">:</td>
                            <td style="font-size: 14px;">{{ $sale->quo_cc }}</td>
                        </tr>
                        <tr>
                            <td style="font-size: 14px;">Date</td>
                            <td style="font-size: 14px;">:</td>
                            <td style="font-size: 14px;">{{ $date }}</td>
                        </tr>
                        <tr>
                            <td style="font-size: 14px;">Store</td>
                            <td style="font-size: 14px;">:</td>
                            <td style="font-size: 14px;">{{ $sale->store }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td style="font-size: 14px; padding: 25px 0 0 0;" colspan="3">Thank you for your inquiry. We are
                    pleased
                    to submit our quote as follows:</td>
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
                    Item Code</td>
                <td
                    style="font-size: 14px; border-top: solid 1px black; border-bottom: solid 1px black; text-align: left; width: 45%;">
                    Description</td>
                <td
                    style="font-size: 14px; border-top: solid 1px black; border-bottom: solid 1px black; text-align: center; width: 5%;">
                    Qty</td>
                <td
                    style="font-size: 14px; border-top: solid 1px black; border-bottom: solid 1px black; text-align: right; width: 15%;">
                    U/Price<br>(RM)</td>
                <td
                    style="font-size: 14px; border-top: solid 1px black; border-bottom: solid 1px black; text-align: right; width: 15%;">
                    Discount<br>(RM)</td>
                <td
                    style="font-size: 14px; border-top: solid 1px black; border-bottom: solid 1px black; text-align: right; width: 15%;">
                    Promotion<br>(RM)</td>
                <td
                    style="font-size: 14px; border-top: solid 1px black; border-bottom: solid 1px black; text-align: right; width: 15%;">
                    Total<br>(RM)</td>
            </tr>
            @php
                $total = 0;
            @endphp
            @foreach ($products as $key => $prod)
                <tr>
                    <td style="font-size: 14px; text-align: left; padding: {{ $key == 0 ? '0' : '20px' }} 0 0 0;">{{ $key + 1 }}</td>
                    <td style="font-size: 14px; text-align: left; padding: {{ $key == 0 ? '0' : '20px' }} 0 0 0;"></td>
                    <td style="font-size: 14px; text-align: left; padding: {{ $key == 0 ? '0' : '20px' }} 0 0 0;">{{ $prod->product->model_name }}</td>
                    <td style="font-size: 14px; text-align: center; padding: {{ $key == 0 ? '0' : '20px' }} 0 0 0;">{{ $prod->qty }}</td>
                    <td style="font-size: 14px; text-align: right; padding: {{ $key == 0 ? '0' : '20px' }} 0 0 0;">{{ number_format($prod->unit_price, 2) }}</td>
                    <td style="font-size: 14px; text-align: right; padding: {{ $key == 0 ? '0' : '20px' }} 0 0 0;">{{ number_format($prod->discount ?? 0, 2) }}</td>
                    <td style="font-size: 14px; text-align: right; padding: {{ $key == 0 ? '0' : '20px' }} 0 0 0;">
                        {{ number_format($prod->promotionAmount() ?? 0, 2) }}
                    </td>
                    <td style="font-size: 14px; text-align: right; padding: {{ $key == 0 ? '0' : '20px' }} 0 0 0;">
                        {{ number_format($prod->qty * $prod->unit_price - $prod->discountAmount(), 2) }}</td>
                </tr>
                @if ($prod->remark != null)
                    <tr>
                        <td style="font-size: 14px; padding: 5px 0; text-align: left;" colspan="2"></td>
                        <td style="font-size: 14px; text-align: left; font-weight: 700;">{!! nl2br($prod->remark) !!}</td>
                        <td style="font-size: 14px; padding: 5px 0; text-align: left;" colspan="4"></td>
                    </tr>
                @endif
                @php
                    $total += $prod->qty * $prod->unit_price - $prod->discountAmount();
                @endphp
            @endforeach
            <!-- Remark -->
            @if ($sale->remark != null)
                <tr>
                    <td colspan="2"></td>
                    <td colspan="2" style="font-size: 14px; padding: 0px 0;"><span
                            style="font-weight: 700;">REMARK:</span><br>{!! nl2br($sale->remark) !!}</td>
                    <td colspan="2"></td>
                </tr>
            @endif
        </table>
        <!-- Item Summary -->
        <table style="width: 100%; font-family: sans-serif; border-collapse: collapse;">
            <tr>
                <td
                    style="font-size: 14px; font-weight: 700; padding: 10px 0 0 0; border-top: solid 1px black; width: 15%;">
                    Validity</td>
                <td
                    style="font-size: 14px; font-weight: 700; padding: 10px 0 0 0; border-top: solid 1px black; width: 5px;">
                    :</td>
                <td
                    style="font-size: 14px; font-weight: 700; padding: 10px 0 0 0; border-top: solid 1px black; width: 15%;">
                    {{ $sale->open_until }}</td>
                <td
                    style="font-size: 14px; font-weight: 700; text-align: right; padding: 10px 0 0 0; border-top: solid 1px black;">
                    Total {{ number_format($total, 2) }}</td>
            </tr>
            <tr>
                <td style="font-size: 14px; font-weight: 700;">Payment Term</td>
                <td style="font-size: 14px; font-weight: 700;">:</td>
                <td style="font-size: 14px; font-weight: 700;">{{ $sale->paymentTerm->name ?? null }}</td>
                <td></td>
            </tr>
        </table>

        <!-- Footer -->
        <table style="width: 100%; font-family: sans-serif; border-collapse: collapse;">
            <tr>
                <td style="font-size: 14px; padding: 25px 0 15px 0;" colspan="2">E.& O.E.</td>
            </tr>
            <tr>
                <td style="font-size: 14px; padding: 0 0 15px 0;" colspan="2">
                    1. Cheque should make payable to HI-TEN TRADING SDN BHD<br>
                    2. Please remit your payment to : <span style="font-weight: 700;">PUBLIC BANK Account No.: 3983 23
                        3530
                        CIMB Account No.: 8603 16 3872</span><br>
                    3. The Company reserves the right to charge interest 1.5% per month on overdue accounts.<br>
                    4. Goods sold and deposit are not returnable & refundable. A cancellation fee of 20% on purchase<br>
                    5. Any queries or complaints regarding this invoice must be made within 7 days from date hereof,
                    otherwise any discrepancy will not be entertained.<br>
                    6. Prices are subjected to change without prior notice.<br>
                    7. · 3 Years Compressor Warranty with T&C apply<br>
                    <span style="padding: 0 0 0 18px;">· 6 months General Service conduct by IMAX</span><br>
                    <span style="padding: 0 0 0 18px;">· Limited to 1 time change only</span><br>
                    8. Wear and tear not included in warranty claim
                </td>
            </tr>
            <tr>
                <td style="font-size: 14px; padding: 0 0 25px 0;" colspan="2">
                    We hope that our quotation is favourable to you and looking forward to receive your valued orders in
                    due
                    course. Thank and regards.
                </td>
            </tr>
            <tr>
                <td style="font-size: 16px; padding: 0 0 50px 0;" colspan="2">Yours faithfully,</td>
            </tr>
            <tr>
                <td
                    style="font-size: 16px; text-align: center; width: 33%; border-bottom: solid 1px black; padding: 10px 0 0 0; font-family: serif;">
                    {{ $sale->saleperson->name }}</td>
                <td></td>
            </tr>
            <tr>
                <td style="font-size: 16px; padding: 10px 0 0 0; font-family: serif;" colspan="2">
                    This is a computer generated documents no signature required.
                </td>
            </tr>
        </table>
    </main>
</body>

</html>
