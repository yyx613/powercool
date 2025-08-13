<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quotation | {{ $sale->sku }}</title>
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

    #invalid {
        color: red;
    }
</style>

<body>
    <!-- Header -->
    <header>
        <table style="width: 100%; font-family: sans-serif; border-collapse: collapse;">
            <tr>
                <td style="width: 70%; border-bottom: solid 1px black; padding: 0 0 10px 0;">
                    <span style="font-size: 16px; font-weight: 700;">POWER COOL EQUIPMENTS (M) SDN BHD <span
                            style="font-size: 12px; font-weight: 100;">(383045-D)</span></span><br>
                    <span style="font-size: 12px;">NO:12,RCI PARK,JALAN KESIDANG 2,</span><br>
                    <span style="font-size: 12px;">KAWASAN PERINDUSTRIAN SUNGAI CHOH,</span><br>
                    <span style="font-size: 12px;">48200 SERENDAH,SELANGOR.</span><br>
                    <span style="font-size: 12px;">Tel: 603-6094 1122 Service Hotline: 012-386 8743</span><br>
                    <span style="font-size: 12px;">Email : enquiry@powercool.com.my</span><br>
                    <span style="font-size: 12px;">Sales Tax ID No : B16-1809-22000036</span><br>
                </td>
                <td style="width: 30%; border-bottom: solid 1px black; padding: 0 0 10px 0; vertical-align: text-top;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="font-size: 12px; width: 30%; font-weight: 700;">No</td>
                            <td style="font-size: 12px; width: 10%;">:</td>
                            <td style="font-size: 12px; font-weight: 700;">{{ $sale->sku }}</td>
                        </tr>
                        <tr>
                            <td style="font-size: 12px;">Your Ref</td>
                            <td style="font-size: 12px;">:</td>
                            <td style="font-size: 12px;">{{ $sale->reference }}</td>
                        </tr>
                        <tr>
                            <td style="font-size: 12px;">From</td>
                            <td style="font-size: 12px;">:</td>
                            <td style="font-size: 12px;">{{ $sale->quo_from }}</td>
                        </tr>
                        <tr>
                            <td style="font-size: 12px;">Salesperson</td>
                            <td style="font-size: 12px;">:</td>
                            <td style="font-size: 12px;">{{ $sale->saleperson->name }}</td>
                        </tr>
                        <tr>
                            <td style="font-size: 12px;">Date</td>
                            <td style="font-size: 12px;">:</td>
                            <td style="font-size: 12px;">{{ $date }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </header>

    <main>

        <table style="width: 100%; font-family: sans-serif; border-collapse: collapse; padding: 0 0 25px 0;">
            <tr>
                <td
                    style="font-size: 12px; font-weight: 700; width: 33.33%; padding: 15px 0 10px 0; text-align: center;">
                </td>
                <td
                    style="font-size: 16px; font-weight: 700; width: 33.33%; padding: 15px 35px 10px 0; text-align: center;">
                    QUOTATION</td>
                <td
                    style="font-size: 12px; font-weight: 700; width: 33.33%; padding: 15px 0 10px 0; text-align: center;">
                </td>
            </tr>
            @if ($sale->status == 3)
                <tr>
                    <td
                        style="font-size: 12px; font-weight: 700; width: 33.33%; padding: 0px 0 10px 0; text-align: center;">
                    </td>
                    <td style="font-size: 12px; font-weight: 700; width: 33.33%; padding: 0px 35px 10px 0; text-align: center;"
                        id="invalid">
                        CANCELLED
                    </td>
                    <td
                        style="font-size: 12px; font-weight: 700; width: 33%.33; padding: 0px 0 10px 0; text-align: center;">
                    </td>
                </tr>
            @endif
            <tr>
                <td style="padding: 0 35px 0 0;" colspan="3">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="font-size: 12px; width: 50%;" colspan="2">
                                {{ $customer->company_name }}<br>
                                {{ $billing_address->address1 ?? '' }}<br>
                                {{ $billing_address->address2 ?? '' }}<br>
                                {{ $billing_address->address3 ?? '' }}<br>
                                {{ $billing_address->address4 ?? '' }}<br>
                            </td>
                        </tr>
                        <tr>
                            <td style="font-size: 12px; padding: 10px 0 0 0; width: 15%;">TEL: {{ $customer->phone }}
                            </td>
                            <td style="font-size: 12px; padding: 10px 0 0 0; width: 15%;">AH:
                                {{ $customer->name ?? '' }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td style="font-size: 12px; padding: 25px 0 0 0;" colspan="2">Thank you for your inquiry. We are
                    pleased to submit
                    our
                    quote as follows:</td>
                <td style="font-size: 12px; padding: 25px 0 0 0;">Store: {{ $sale->store }}</td>
            </tr>
        </table>

        <!-- Item -->
        <table style="width: 100%; font-family: sans-serif; border-collapse: collapse;">
            <tr>
                <td
                    style="font-size: 12px; border-top: solid 1px black; border-bottom: solid 1px black; padding: 5px 0; text-align: left; width: 5%;">
                    Item</td>
                <td
                    style="font-size: 12px; border-top: solid 1px black; border-bottom: solid 1px black; padding: 5px 0; text-align: left; width: 5%;">
                    Tax Code</td>
                <td
                    style="font-size: 12px; border-top: solid 1px black; border-bottom: solid 1px black; text-align: left; width: 10%;">
                    Item Code</td>
                <td
                    style="font-size: 12px; border-top: solid 1px black; border-bottom: solid 1px black; text-align: left; width: 40%;">
                    Description</td>
                <td
                    style="font-size: 12px; border-top: solid 1px black; border-bottom: solid 1px black; text-align: center; width: 5%;">
                    Qty</td>
                <td
                    style="font-size: 12px; border-top: solid 1px black; border-bottom: solid 1px black; text-align: center; width: 5%;">
                    UOM</td>
                <td
                    style="font-size: 12px; border-top: solid 1px black; border-bottom: solid 1px black; text-align: right; width: 15%;">
                    U/Price<br>(RM)</td>
                <td
                    style="font-size: 12px; border-top: solid 1px black; border-bottom: solid 1px black; text-align: right; width: 15%;">
                    Discount<br>(RM)</td>
                <td
                    style="font-size: 12px; border-top: solid 1px black; border-bottom: solid 1px black; text-align: right; width: 15%;">
                    Total<br>(RM)</td>
            </tr>
            @php
                $total = 0;
                $total_tax = 0;
            @endphp
            @foreach ($products as $key => $prod)
                <tr>
                    <td style="font-size: 12px; text-align: left; padding: {{ $key == 0 ? '0' : '20px' }} 0 0 0;">
                        {{ $key + 1 }}</td>
                    <td style="font-size: 12px; text-align: left; padding: {{ $key == 0 ? '0' : '20px' }} 0 0 0;">
                        {{ $tax_code }}</td>
                    <td style="font-size: 12px; text-align: left; padding: {{ $key == 0 ? '0' : '20px' }} 10px 0 0;">
                        {{ $prod->product->sku }}
                    </td>
                    <td style="font-size: 12px; text-align: left; padding: {{ $key == 0 ? '0' : '20px' }} 0 0 0;">
                        {{ $prod->product->model_name }}</td>
                    <td style="font-size: 12px; text-align: center; padding: {{ $key == 0 ? '0' : '20px' }} 10px 0 0;">
                        {{ $prod->qty }}</td>
                    <td style="font-size: 12px; text-align: center; padding: {{ $key == 0 ? '0' : '20px' }} 10px 0 0;">
                        {{ $prod->uom }}</td>
                    <td style="font-size: 12px; text-align: right; padding: {{ $key == 0 ? '0' : '20px' }} 10px 0 0;">
                        {{ number_format($prod->unit_price, 2) }}</td>
                    <td style="font-size: 12px; text-align: right; padding: {{ $key == 0 ? '0' : '20px' }} 10px 0 0;">
                        {{ number_format($prod->discountAmount(), 2) }}
                    </td>
                    <td style="font-size: 12px; text-align: right; padding: {{ $key == 0 ? '0' : '20px' }} 0 0 0;">
                        {{ number_format($prod->qty * $prod->unit_price - $prod->discountAmount(), 2) }}</td>
                </tr>
                @if ($prod->remark != null)
                    <tr>
                        <td style="font-size: 12px; padding: 5px 0; text-align: left;" colspan="2"></td>
                        <td style="font-size: 12px; text-align: left; font-weight: 700;">{!! nl2br($prod->remark) !!}</td>
                        <td style="font-size: 12px; padding: 5px 0; text-align: left;" colspan="4"></td>
                    </tr>
                @endif
                @php
                    $total += $prod->qty * $prod->unit_price - $prod->discountAmount();
                    $total_tax += $prod->sst_amount ?? 0;
                @endphp
            @endforeach
            <!-- Remark -->
            @if ($sale->remark != null)
                <tr>
                    <td colspan="2"></td>
                    <td colspan="2" style="font-size: 12px; padding: 15px 0;"><span
                            style="font-weight: 700;">REMARK:</span><br>{!! nl2br($sale->remark) !!}</td>
                    <td colspan="2"></td>
                </tr>
            @endif
        </table>
        <!-- Item Summary -->
        <table style="width: 100%; font-family: sans-serif; border-collapse: collapse;">
            <tr>
                <td
                    style="font-size: 12px; font-weight: 700; padding: 10px 0 0 0; border-top: solid 1px black; width: 20%;">
                    Validity</td>
                <td
                    style="font-size: 12px; font-weight: 700; padding: 10px 0 0 0; border-top: solid 1px black; width: 5px;">
                    :</td>
                <td style="font-size: 12px; font-weight: 700; padding: 10px 0 0 0; border-top: solid 1px black;">
                    {{ $sale->open_until }}</td>
                <td
                    style="font-size: 12px; font-weight: 700; text-align: right; padding: 10px 0 0 0; border-top: solid 1px black;">
                    Sub Total (Excluding SST) <span
                        style="border: solid 1px black; padding: 2.5px 10px;">{{ number_format($total, 2) }}</span>
                </td>
            </tr>
            <tr>
                <td style="font-size: 12px; font-weight: 700;">Payment Method</td>
                <td style="font-size: 12px; font-weight: 700;">:</td>
                <td style="font-size: 12px; font-weight: 700;">{{ $sale->paymentMethod->name ?? null }}</td>
                <td style="font-size: 12px; font-weight: 700; text-align: right; padding: 10px 0 0 0;">
                    Tax @ {{ $sst_value }}% on <span
                        style="border: solid 1px black; padding: 2.5px 10px;">{{ number_format($total_tax, 2) }}</span>
                </td>
            </tr>
            @if ($show_payment_term)
                <tr>
                    <td style="font-size: 12px; font-weight: 700;">Payment Term</td>
                    <td style="font-size: 12px; font-weight: 700;">:</td>
                    <td style="font-size: 12px; font-weight: 700;">{{ $payment_term ?? null }}</td>
                    <td></td>
                </tr>
            @endif
            <tr>
                <td style="font-size: 12px; font-weight: 700;"></td>
                <td style="font-size: 12px; font-weight: 700;"></td>
                <td style="font-size: 12px; font-weight: 700;"></td>
                <td style="font-size: 12px; font-weight: 700; text-align: right; padding: 10px 0 0 0;">Total (Inclusive
                    of
                    SST) <span
                        style="border: solid 1px black; padding: 2.5px 10px;">{{ number_format($total - $total_tax, 2) }}</span>
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
                    <span style="padding: 0 0 0 18px;">· 6 months General Service conduct by IMAX</span><br>
                    <span style="padding: 0 0 0 18px;">· Limited to 1 time change only</span><br>
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
                <td style="font-size: 12px; padding: 0 0 40px 0; text-align: center; width: 33%;">PREPARED BY</td>
                <td></td>
            </tr>
            <tr>
                <td
                    style="font-size: 12px; text-align: center; width: 33%; border-top: solid 1px black; padding: 10px 0 0 0; font-family: serif;">
                    {{ $sale->saleperson->name }}</td>
                <td></td>
            </tr>
        </table>
    </main>

</body>

</html>
