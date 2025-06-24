<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sale Order | {{ $sale->sku }}</title>
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
                    <span style="font-size: 14px;">Service Hotline (Penang/Johor) : 012-386 8477 / 013-922
                        2000</span><br>
                    <span style="font-size: 14px;">Email add : <a
                            href="mailto:imax.hiten_sales@powercool.com.my">enquiry@powercool.com.my</a></span>
                </td>
            </tr>
        </table>
    </header>

    <main>
        <table style="width: 100%; font-family: sans-serif; border-collapse: collapse; padding: 0 0 25px 0;">
            <tr>
                <td colspan="2"
                    style="font-size: 18px; font-weight: 700; width: 65%; padding: 15px 35px 10px 0; text-align: right;">
                    SALES ORDER</td>
                <td style="font-size: 14px; font-weight: 700; width: 35%; padding: 15px 0 10px 0; text-align: center;">
                    No. :
                    {{ $sale->sku }}</td>
            </tr>
            <tr>
                <td colspan="2" style="padding: 0 35px 0 0;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="font-size: 14px; padding: 10px 0 0 0; font-weight: 700;">TIN: {{ $customer->tin_number }}</td>
                        </tr>
                        <tr>
                            <td style="font-size: 14px;" colspan="2">
                                {{ $customer->company_name }}<br>
                                {{ $billing_address->address ?? '' }}<br>
                                {{ $billing_address->city ?? '' }}<br>
                                {{ $billing_address->state ?? '' }}<br>
                                {{ $billing_address->zip_code ?? '' }}<br>
                            </td>
                        </tr>
                        <tr>
                            <td style="font-size: 14px; padding: 10px 0 0 0;">TEL: {{ $customer->phone }}</td>
                            <td style="font-size: 14px; padding: 10px 0 0 0; text-align: start;">FAX: </td>
                        </tr>
                    </table>
                </td>
                <td>
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="font-size: 14px; width: 40%;">Your P/O No.</td>
                            <td style="font-size: 14px; width: 10%;">:</td>
                            <td style="font-size: 14px;">{{ $sale->reference }}</td>
                        </tr>
                        <tr>
                            <td style="font-size: 14px;">Terms</td>
                            <td style="font-size: 14px;">:</td>
                            <td style="font-size: 14px;">{{ $sale->paymentTerm->name ?? null }}</td>
                        </tr>
                        <tr>
                            <td style="font-size: 14px;">Date</td>
                            <td style="font-size: 14px;">:</td>
                            <td style="font-size: 14px;">{{ $date }}</td>
                        </tr>
                        <tr>
                            <td style="font-size: 14px;">Salesperson</td>
                            <td style="font-size: 14px;">:</td>
                            <td style="font-size: 14px;">{{ $saleperson->name }}</td>
                        </tr>
                        <tr>
                            <td style="font-size: 14px;">Store</td>
                            <td style="font-size: 14px;">:</td>
                            <td style="font-size: 14px;"></td>
                        </tr>
                        <tr>
                            <td style="font-size: 14px; font-weight: 700;">Warehouse</td>
                            <td style="font-size: 14px; font-weight: 700;">:</td>
                            <td style="font-size: 14px; font-weight: 700;">HQ</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        <!-- Item -->
        <table style="width: 100%; font-family: sans-serif; border-collapse: collapse;">
            <tr>
                <td
                    style="font-size: 14px; border-top: solid 1px black; border-bottom: solid 1px black; padding: 5px 0; text-align: left; width: 5%;">
                    Item</td>
                <td
                    style="font-size: 14px; border-top: solid 1px black;  border-bottom: solid 1px black; text-align: left; width: 10%;">
                    Item Code</td>
                <td
                    style="font-size: 14px; border-top: solid 1px black; border-bottom: solid 1px black; text-align: left; width: 40%;">
                    Description</td>
                <td
                    style="font-size: 14px; border-top: solid 1px black; border-bottom: solid 1px black; text-align: center; width: 5%;">
                    Qty</td>
                <td
                    style="font-size: 14px; border-top: solid 1px black; border-bottom: solid 1px black; text-align: center; width: 5%;">
                    FOC Qty</td>
                <td
                    style="font-size: 14px; border-top: solid 1px black; border-bottom: solid 1px black; text-align: center; width: 5%;">
                    UOM</td>
                <td
                    style="font-size: 14px; border-top: solid 1px black; border-bottom: solid 1px black; text-align: right; width: 10%;">
                    U/Price<br>(RM)</td>
                <td
                    style="font-size: 14px; border-top: solid 1px black; border-bottom: solid 1px black; text-align: right; width: 15%;">
                    Discount<br>(RM)</td>
                <td
                    style="font-size: 14px; border-top: solid 1px black; border-bottom: solid 1px black; text-align: right; width: 15%;">
                    Promotion<br>(RM)</td>
                <td
                    style="font-size: 14px; border-top: solid 1px black; border-bottom: solid 1px black; text-align: right; width: 10%;">
                    Total<br>(RM)</td>
            </tr>
            @php
                $total = 0;
            @endphp
            @foreach ($products as $key => $prod)
                <tr>
                    <td style="font-size: 14px; padding: 5px 0; text-align: left;">{{ $key + 1 }}</td>
                    <td style="font-size: 14px; text-align: left;">{{ $prod->product->sku }}</td>
                    <td style="font-size: 14px; text-align: left;">{{ $prod->desc }}</td>
                    <td style="font-size: 14px; text-align: center;">{{ $prod->is_foc == true ? '' : $prod->qty }}</td>
                    <td style="font-size: 14px; text-align: center;">{{ $prod->is_foc == false ? '' : $prod->qty }}
                    </td>
                    <td style="font-size: 14px; text-align: right;">{{ $prod->uom }}</td>
                    <td style="font-size: 14px; text-align: right;">
                        {{ number_format($prod->override_selling_price ?? $prod->unit_price, 2) }}</td>
                    <td style="font-size: 14px; text-align: right;">{{ number_format($prod->discount, 2) }}</td>
                    <td style="font-size: 14px; text-align: right;">
                        {{ number_format($prod->promotionAmount() ?? 0, 2) }}
                    </td>
                    <td style="font-size: 14px; text-align: right;">
                        {{ number_format($prod->qty * ($prod->override_selling_price ?? $prod->unit_price) - $prod->discountAmount(), 2) }}
                    </td>
                </tr>
                @if ($prod->remark != null)
                    <tr>
                        <td style="font-size: 14px; padding: 5px 0; text-align: left;" colspan="2"></td>
                        <td style="font-size: 14px; text-align: left; font-weight: 700;">{!! nl2br($prod->remark) !!}</td>
                        <td style="font-size: 14px; text-align: left;" colspan="4"></td>
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
                    $total +=
                        $prod->qty * ($prod->override_selling_price ?? $prod->unit_price) - $prod->discountAmount();
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
            <!-- Payment Remark -->
            @if ($sale->payment_remark != null)
                <tr>
                    <td colspan="2"></td>
                    <td colspan="2" style="font-size: 14px; padding: 15px 0;"><span style="font-weight: 700;">PAYMENT
                            REMARK:</span><br>{{ $sale->payment_remark }}</td>
                    <td colspan="2"></td>
                </tr>
            @endif
        </table>
        <!-- Item Summary -->
        <table style="width: 100%; font-family: sans-serif; border-collapse: collapse; padding: 30px 0 0 0;">
            <tr>
                <td
                    style="font-size: 14px; padding: 10px 0 0 0; border-top: solid 1px black; text-transform: uppercase;">
                    {{ priceToWord(number_format($total, 2)) }}</td>
                <td
                    style="font-size: 14px; font-weight: 700; text-align: right; padding: 10px 0 0 0; border-top: solid 1px black; width: 15%; vertical-align: text-top;">
                    Total {{ number_format($total, 2) }}</td>
            </tr>
        </table>

        <!-- Footer -->
        <table style="width: 100%; font-family: sans-serif; border-collapse: collapse;">
            <tr>
                <td style="font-size: 14px; padding: 25px 0 15px 0;" colspan="3">E.& O.E.</td>
            </tr>
            <tr>
                <td style="font-size: 14px; padding: 0 0 75px 0;" colspan="3">
                    1. Cheque should make payable to HI-TEN TRADING SDN BHD<br>
                    2. Please remit your payment to : <span style="font-weight: 700;">PUBLIC BANK Account No.: 3983 23
                        3530
                        CIMB Account No.: 8603 16 3872</span><br>
                    3. The Company reserves the right to charge interest 1.5% per month on overdue accounts.<br>
                    4. Goods sold and deposit are not returnable & refundable. A cancellation fee of 20% on purchase price will be imposed.If cancel
                    order with credit card payment,will have 10% credit card charges.<br>
                    5. Any queries or complaints regarding this invoice must be made within 7 days from date hereof,
                    otherwise any discrepancy will not be entertained.<br>
                    6. For more information about our policies, please visit our website at :-<br>
                    https://imaxrefrigerator.com.my/warranty-policy/<br>
                    7. Customer collect\unloading\handling by own, warranty will be bear by customer.<br>
                    8. Request E-invoice after 72hrs which original invoice have validated by IRB will be charge 5% of the total invoice amount.<br>
                    9. Company will not obligation on those customers are not require to issue E-invoice.
                </td>
            </tr>
            <tr>
                <td
                    style="font-size: 16px; text-align: center; width: 33%; border-top: solid 1px black; padding: 10px 0 0 0; font-weight: 700;">
                    Authorised Signature</td>
                <td></td>
            </tr>
        </table>
    </main>

</body>

</html>
