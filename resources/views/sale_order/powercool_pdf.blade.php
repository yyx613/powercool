<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sale Order | {{ $sale->sku }}</title>
</head>

<style>
    @page {
        margin: 175px 25px 50px 25px;
    }

    header {
        position: fixed;
        top: -150px;
        left: 0px;
        right: 0px;
    }
</style>

<body>
    <header>
        <!-- Header -->
        <table style="width: 100%; font-family: sans-serif; border-collapse: collapse;">
            <tr>
                <td style="width: 70%; border-bottom: solid 1px black; padding: 0 0 10px 0;">
                    <span style="font-size: 18px; font-weight: 700;">POWER COOL EQUIPMENTS (M) SDN BHD <span
                            style="font-size: 14px; font-weight: 100;">(383045-D)</span></span><br>
                    <span style="font-size: 14px;">NO:12,RCI PARK,JALAN KESIDANG 2,</span><br>
                    <span style="font-size: 14px;">KAWASAN PERINDUSTRIAN SUNGAI CHOH,</span><br>
                    <span style="font-size: 14px;">48200 SERENDAH,SELANGOR.</span><br>
                    <span style="font-size: 14px;">Tel: 603-6094 1122 Service Hotline: 012-386 8743</span><br>
                    <span style="font-size: 14px;">Email : enquiry@powercool.com.my</span><br>
                    <span style="font-size: 14px;">Sales Tax ID No : B16-1809-22000036</span><br>
                </td>
                <td style="width: 30%; border-bottom: solid 1px black; padding: 0 0 10px 0; vertical-align: text-top;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="font-size: 14px; width: 40%; font-weight: 700;">No</td>
                            <td style="font-size: 14px; width: 10%;">:</td>
                            <td style="font-size: 14px; font-weight: 700;">{{ $sale->sku }}</td>
                        </tr>
                        <tr>
                            <td style="font-size: 14px;">Your P/O No.</td>
                            <td style="font-size: 14px;">:</td>
                            <td style="font-size: 14px;">{{ $sale->reference }}</td>
                        </tr>
                        <tr>
                            <td style="font-size: 14px;">From</td>
                            <td style="font-size: 14px;">:</td>
                            <td style="font-size: 14px;">{{ $sale->quo_from }}</td>
                        </tr>
                        <tr>
                            <td style="font-size: 14px;">Salesperson</td>
                            <td style="font-size: 14px;">:</td>
                            <td style="font-size: 14px;">{{ $sale->saleperson->name }}</td>
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
    </header>


    <main>
        <table style="width: 100%; font-family: sans-serif; border-collapse: collapse; padding: 0 0 25px 0;">
            <tr>
                <td style="font-size: 18px; font-weight: 700; width: 65%; padding: 15px 35px 10px 0; text-align: center;"
                    colspan="2">SALE ORDER</td>
            </tr>
            <tr>
                <td style="padding: 0 35px 0 0;" colspan="2">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="font-size: 14px; width: 50%;" colspan="2">
                                {{ $customer->company_name }}<br>
                                {{ $billing_address->address ?? '' }}<br>
                                {{ $billing_address->city ?? '' }}<br>
                                {{ $billing_address->state ?? '' }}<br>
                                {{ $billing_address->zip_code ?? '' }}<br>
                            </td>
                        </tr>
                        <tr>
                            <td style="font-size: 14px; padding: 10px 0 0 0; width: 15%;">TEL: {{ $customer->phone }}
                            </td>
                            <td style="font-size: 14px; padding: 10px 0 0 0; width: 15%; text-align: start;">FAX: </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td style="font-size: 14px; padding: 25px 0 0 0;">Thank you for your inquiry. We are pleased to submit
                    our
                    quote as follows:</td>
                <td style="font-size: 14px; padding: 25px 0 0 0;">Store:</td>
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
                    style="font-size: 14px; border-top: solid 1px black; border-bottom: solid 1px black; text-align: left; width: 40%;">
                    Description</td>
                <td
                    style="font-size: 14px; border-top: solid 1px black; border-bottom: solid 1px black; text-align: center; width: 5%;">
                    Qty</td>
                <td
                    style="font-size: 14px; border-top: solid 1px black; border-bottom: solid 1px black; text-align: center; width: 5%;">
                    UOM</td>
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
                    <td style="font-size: 14px; padding: 5px 0; text-align: left;">{{ $key + 1 }}</td>
                    <td style="font-size: 14px; text-align: left;">{{ $prod->product->sku }}</td>
                    <td style="font-size: 14px; text-align: left;">{{ $prod->product->model_name }}</td>
                    <td style="font-size: 14px; text-align: center;">{{ $prod->qty }}</td>
                    <td style="font-size: 14px; text-align: center;">{{ $prod->product->uom }}</td>
                    <td style="font-size: 14px; text-align: right;">{{ number_format($prod->unit_price, 2) }}</td>Price
                    <td style="font-size: 14px; text-align: right;">{{ number_format($prod->discount ?? 0, 2) }}</td>
                    <td style="font-size: 14px; text-align: right;">
                        {{ number_format($prod->promotionAmount() ?? 0, 2) }}
                    </td>
                    <td style="font-size: 14px; text-align: right;">
                        {{ number_format(($prod->override_selling_price ?? $prod->qty * $prod->unit_price) - $prod->discountAmount(), 2) }}
                    </td>
                </tr>
                @if ($prod->remark != null)
                    <tr>
                        <td style="font-size: 14px; padding: 5px 0; text-align: left;" colspan="2"></td>
                        <td style="font-size: 14px; text-align: left; font-weight: 700;">{!! nl2br($prod->remark) !!}</td>
                        <td style="font-size: 14px; padding: 5px 0; text-align: left;" colspan="4"></td>
                    </tr>
                @endif
                <!-- Warranty -->
                @if ($prod->warrantyPeriods != null)
                    @php
                        $warranty = [];
                        foreach ($prod->warrantyPeriods as $wp) {
                            $warranty[] = $wp->warrantyPeriod->name;
                        }
                    @endphp
                    <tr>
                        <td style="font-size: 14px; padding: 5px 0; text-align: left;" colspan="2"></td>
                        <td style="font-size: 14px; text-align: left; font-weight: 700;">Warranty:
                            {{ join(', ', $warranty) }}</td>
                        <td style="font-size: 14px; text-align: left;" colspan="4"></td>
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
                    <td colspan="2" style="font-size: 14px; padding: 15px 0;"><span
                            style="font-weight: 700;">REMARK:</span><br>{{ $sale->remark }}</td>
                    <td colspan="2"></td>
                </tr>
            @endif
        </table>
        <!-- Item Summary -->
        <table style="width: 100%; font-family: sans-serif; border-collapse: collapse; padding: 30px 0 0 0;">
            <tr>
                <td
                    style="font-size: 14px; font-weight: 700; padding: 10px 0 0 0; border-top: solid 1px black; width: 15%;">
                    Validity</td>
                <td
                    style="font-size: 14px; font-weight: 700; padding: 10px 0 0 0; border-top: solid 1px black; width: 5px;">
                    :</td>
                <td style="font-size: 14px; font-weight: 700; padding: 10px 0 0 0; border-top: solid 1px black;">
                    {{ $sale->open_until }}</td>
                <td
                    style="font-size: 14px; font-weight: 700; text-align: right; padding: 10px 0 0 0; border-top: solid 1px black;">
                    Sub Total (Excluding SST) <span
                        style="border: solid 1px black; padding: 2.5px 10px;">{{ number_format($total, 2) }}</span>
                </td>
            </tr>
            <tr>
                <td style="font-size: 14px; font-weight: 700;">Payment Term</td>
                <td style="font-size: 14px; font-weight: 700;">:</td>
                <td style="font-size: 14px; font-weight: 700;">{{ $sale->paymentTerm->name ?? null }}</td>
                <td style="font-size: 14px; font-weight: 700; text-align: right; padding: 10px 0 0 0;">Tax @ 0% on <span
                        style="border: solid 1px black; padding: 2.5px 10px;">{{ number_format(0, 2) }}</span></td>
            </tr>
            <tr>
                <td style="font-size: 14px; font-weight: 700;"></td>
                <td style="font-size: 14px; font-weight: 700;"></td>
                <td style="font-size: 14px; font-weight: 700;"></td>
                <td style="font-size: 14px; font-weight: 700; text-align: right; padding: 10px 0 0 0;">Total (Inclusive
                    of
                    SST) <span
                        style="border: solid 1px black; padding: 2.5px 10px;">{{ number_format($total, 2) }}</span>
                </td>
            </tr>
        </table>

        <!-- Footer -->
        <table style="width: 100%; font-family: sans-serif; border-collapse: collapse;">
            <tr>
                <td style="font-size: 12px; padding: 25px 0 15px 0;" colspan="2">Note</td>
            </tr>
            <tr>
                <td style="font-size: 12px; padding: 0 0 15px 0;" colspan="2">
                    1. All cheques should be crossed and makde payable to POWER COOL EQUIPMENTS (M) SDN BHD<br>
                    2. Please remit your payment to : <span style="font-weight: 700;">Public Bank Ac No.:
                        3141967510</span><br>
                    3. The Company reserves the right to charge interest 1.5% per month on overdue accounts.<br>
                    4. Goods sold and deposit are not returnable & refundable. A cancellation fee of 20% on purchase
                    price
                    will be imposed.<br>
                    5. Any queries or complaints regarding this invoice must be made within 7 days from date hereof,<br>
                    otherwise any discrepancy will not be entertained.<br>
                    6. Prices are subjected to change without prior notice.
                    7. · 3 Years Compressor Warranty with T&C apply<br>
                    · 6 months General Service conduct by IMAX<br>
                    · Limited to 1 time change only<br>
                    8. Wear and tear not included in warranty claim
                </td>
            </tr>
            <tr>
                <td style="font-size: 12px; padding: 0 0 35px 0;" colspan="2">
                    We hope that our quotation is favourable to you and looking forward to receive your valued orders in
                    due
                    course. Thank and regards.
                </td>
            </tr>
            <tr>
                <td style="font-size: 16px; padding: 0 0 50px 0; text-align: center; width: 33%;">PREPARED BY</td>
                <td></td>
            </tr>
            <tr>
                <td
                    style="font-size: 16px; text-align: center; width: 33%; border-top: solid 1px black; padding: 10px 0 0 0; font-family: serif;">
                    {{ $sale->saleperson->name }}</td>
                <td></td>
            </tr>
        </table>
    </main>

</body>

</html>
