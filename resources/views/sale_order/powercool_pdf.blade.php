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
                    <span style="font-size: 16px; font-weight: 700;">POWER COOL EQUIPMENTS (M) SDN BHD <span
                            style="font-size: 12px; font-weight: 100;">(383045-D)</span></span><br>
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
                            <td style="font-size: 14px; width: 45%; font-weight: 700;">Sale Order No</td>
                            <td style="font-size: 14px; width: 10%;">:</td>
                            <td style="font-size: 14px; font-weight: 700;">{{ $sale->sku }}</td>
                        </tr>
                        <tr>
                            <td style="font-size: 14px;">Date</td>
                            <td style="font-size: 14px;">:</td>
                            <td style="font-size: 14px;">{{ $date }}</td>
                        </tr>
                        <tr>
                            <td style="font-size: 14px;">Your P/O No.</td>
                            <td style="font-size: 14px;">:</td>
                            <td style="font-size: 14px;">{{ $sale->reference }}</td>
                        </tr>
                        <tr>
                            <td style="font-size: 14px;">Terms</td>
                            <td style="font-size: 14px;">:</td>
                            <td style="font-size: 14px;">{{ $terms->name ?? null }}</td>
                        </tr>
                        <tr>
                            <td style="font-size: 14px;">Salesperson</td>
                            <td style="font-size: 14px;">:</td>
                            <td style="font-size: 14px;">{{ $sale->saleperson->name ?? '' }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </header>

    <main>
        <table style="width: 100%; font-family: sans-serif; border-collapse: collapse; padding: 0 0 25px 0;">
            <tr>
                <td style="font-size: 16px; font-weight: 700; width: 65%; padding: 15px 35px 10px 0; text-align: center;"
                    colspan="2">SALES ORDER</td>
            </tr>
            <tr>
                <td style="padding: 0 35px 0 0;" colspan="2">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="font-size: 14px; padding: 10px 0 0 0; font-weight: 700; width: 50%;">Billing
                                Address:</td>
                            <td style="font-size: 14px; padding: 10px 0 0 0; font-weight: 700; width: 50%;">Delivery
                                Address:</td>
                        </tr>
                        <tr>
                            <td style="font-size: 14px; width: 50%;">
                                {{ $billing_address->address1 ?? '' }}<br>
                                {{ $billing_address->address2 ?? '' }}<br>
                                {{ $billing_address->address3 ?? '' }}<br>
                                {{ $billing_address->address4 ?? '' }}<br><br>
                                {{ $customer->company_name }}
                            </td>
                            <td style="font-size: 14px; width: 50%;">
                                {{ $delivery_address->address1 ?? '' }}<br>
                                {{ $delivery_address->address2 ?? '' }}<br>
                                {{ $delivery_address->address3 ?? '' }}<br>
                                {{ $delivery_address->address4 ?? '' }}<br><br>
                                {{ $customer->company_name }}
                            </td>
                        </tr>
                        <tr>
                            <td style="font-size: 14px; padding: 10px 0 0 0; width: 15%;">TEL: {{ $customer->phone }}
                            </td>
                            <td style="font-size: 14px; padding: 10px 0 0 0; width: 15%;">ATT:
                                {{ $customer->name ?? '' }}</td>
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
                    style="font-size: 10px; border-top: solid 1px black; border-bottom: solid 1px black; padding: 5px 0; text-align: left; width: 5%;">
                    Item</td>
                <td
                    style="font-size: 10px; border-top: solid 1px black; border-bottom: solid 1px black; padding: 5px 0; text-align: left; width: 5%;">
                    Tax Code</td>
                <td
                    style="font-size: 10px; border-top: solid 1px black; border-bottom: solid 1px black; text-align: left; width: 10%;">
                    Item Code</td>
                <td
                    style="font-size: 10px; border-top: solid 1px black; border-bottom: solid 1px black; text-align: left; width: 30%;">
                    Description</td>
                <td
                    style="font-size: 10px; border-top: solid 1px black; border-bottom: solid 1px black; text-align: center; width: 12.5%;">
                    Qty</td>
                <td
                    style="font-size: 10px; border-top: solid 1px black; border-bottom: solid 1px black; text-align: center; width: 12.5%;">
                    UOM</td>
                <td
                    style="font-size: 10px; border-top: solid 1px black; border-bottom: solid 1px black; text-align: right; width: 12.5%;">
                    U/Price (RM)</td>
                <td
                    style="font-size: 10px; border-top: solid 1px black; border-bottom: solid 1px black; text-align: right; width: 12.5%;">
                    Discount (RM)</td>
                <td
                    style="font-size: 10px; border-top: solid 1px black; border-bottom: solid 1px black; text-align: right; width: 12.5%;">
                    Promotion (RM)</td>
                <td
                    style="font-size: 10px; border-top: solid 1px black; border-bottom: solid 1px black; text-align: right; width: 12.5%;">
                    Total (RM)</td>
            </tr>
            @php
                $total = 0;
                $total_tax = 0;
            @endphp
            @foreach ($products as $key => $prod)
                <tr>
                    <td style="font-size: 10px; text-align: left; padding: {{ $key == 0 ? '0' : '20px' }} 0 0 0;">
                        {{ $key + 1 }}</td>
                    <td style="font-size: 10px; text-align: left; padding: {{ $key == 0 ? '0' : '20px' }} 0 0 0;">
                        {{ $tax_code }}</td>
                    <td style="font-size: 10px; text-align: left; padding: {{ $key == 0 ? '0' : '20px' }} 10px 0 0;">
                        {{ $prod->product->sku }}</td>
                    <td style="font-size: 10px; text-align: left; padding: {{ $key == 0 ? '0' : '20px' }} 0 0 0;">
                        {{ $prod->product->model_name }}</td>
                    <td style="font-size: 10px; text-align: center; padding: {{ $key == 0 ? '0' : '20px' }} 0 0 0;">
                        {{ $prod->qty }}</td>
                    <td style="font-size: 10px; text-align: center; padding: {{ $key == 0 ? '0' : '20px' }} 0 0 0;">
                        {{ $prod->uom }}</td>
                    <td style="font-size: 10px; text-align: right; padding: {{ $key == 0 ? '0' : '20px' }} 0 0 0;">
                        {{ number_format($prod->unit_price, 2) }}</td>Price
                    <td style="font-size: 10px; text-align: right; padding: {{ $key == 0 ? '0' : '20px' }} 0 0 0;">
                        {{ number_format($prod->discount ?? 0, 2) }}</td>
                    <td style="font-size: 10px; text-align: right; padding: {{ $key == 0 ? '0' : '20px' }} 0 0 0;">
                        {{ number_format($prod->promotionAmount() ?? 0, 2) }}
                    </td>
                    <td style="font-size: 10px; text-align: right; padding: {{ $key == 0 ? '0' : '20px' }} 0 0 0;">
                        {{ number_format(($prod->override_selling_price ?? $prod->qty * $prod->unit_price) - $prod->discountAmount(), 2) }}
                    </td>
                </tr>
                @if ($prod->remark != null)
                    <tr>
                        <td style="font-size: 10px; padding: 5px 0; text-align: left;" colspan="2"></td>
                        <td style="font-size: 10px; text-align: left; font-weight: 700;">{!! nl2br($prod->remark) !!}</td>
                        <td style="font-size: 10px; padding: 5px 0; text-align: left;" colspan="4"></td>
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
                        <td style="font-size: 10px; padding: 5px 0; text-align: left;" colspan="2"></td>
                        <td style="font-size: 10px; text-align: left; font-weight: 700;">Warranty:
                            {{ join(', ', $warranty) }}</td>
                        <td style="font-size: 10px; text-align: left;" colspan="4"></td>
                    </tr>
                @endif
                <!-- Serial No -->
                @if ($prod->serial_no != null)
                    <tr>
                        <td style="font-size: 10px; padding: 5px 0; text-align: left;" colspan="2"></td>
                        <td style="font-size: 10px; text-align: left; font-weight: 700;">Serial No:
                            {{ join(', ', $prod->serial_no) }}</td>
                        <td style="font-size: 10px; text-align: left;" colspan="4"></td>
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
                    <td colspan="2" style="font-size: 10px; padding: 15px 0;"><span
                            style="font-weight: 700;">REMARK:</span><br>{!! nl2br($sale->remark) !!}</td>
                    <td colspan="2"></td>
                </tr>
            @endif
        </table>
        <!-- Item Summary -->
        <table style="width: 100%; font-family: sans-serif; border-collapse: collapse; padding: 30px 0 0 0;">
            <tr>
                <td style="font-size: 12px; padding: 10px 0 0 0; border-top: solid 1px black; text-transform: uppercase;"
                    colspan="3">
                    {{ priceToWord(number_format($total, 2)) }}</td>
                <td
                    style="font-size: 12px; font-weight: 700; text-align: right; padding: 10px 5px 0 0; border-top: solid 1px black;">
                    Sub Total (Excluding SST)
                </td>
                <td style="padding: 10px 0 0 0; border-top: solid 1px black; width: 10%;">
                    <p
                        style="margin: 0; font-size: 12px; font-weight: 700; border: solid 1px black; padding: 2.5px 10px; text-align: right;">
                        {{ number_format($total, 2) }}</p>
                </td>
            </tr>
            <tr>
                <td style="font-size: 12px; font-weight: 700;"></td>
                <td style="font-size: 12px; font-weight: 700; text-align: right; padding: 0px 5px 0 0;" colspan="3">
                    Tax @ {{ $sst_value }}% on
                </td>
                <td style="padding: 0px 0 0 0;">
                    <p
                        style="margin: 0; font-size: 12px; font-weight: 700; border: solid 1px black; padding: 2.5px 10px; text-align: right;">
                        {{ number_format($total_tax, 2) }}</p>
                </td>
            </tr>
            <tr>
                <td style="font-size: 12px; font-weight: 700;"></td>
                <td style="font-size: 12px; font-weight: 700;"></td>
                <td style="font-size: 12px; font-weight: 700;"></td>
                <td style="font-size: 12px; font-weight: 700; text-align: right; padding: 0px 5px 0 0;">Total (Inclusive
                    of
                    SST)
                </td>
                <td style="padding: 0px 0 0 0;">
                    <p
                        style="margin: 0; font-size: 12px; font-weight: 700; border: solid 1px black; padding: 2.5px 10px; text-align: right;">
                        {{ number_format($total - $total_tax, 2) }}</p>
                </td>
            </tr>
        </table>

        <!-- Footer -->
        <table style="width: 100%; font-family: sans-serif; border-collapse: collapse; padding: 0 0 35px 0;">
            <tr>
                <td style="font-size: 12px; padding: 25px 0 0 0;" colspan="2">E.& O.E.</td>
            </tr>
            <tr>
                <td style="font-size: 12px; padding: 0 0 0 0;" colspan="2">Note</td>
            </tr>
            <tr>
                <td style="font-size: 12px; vertical-align: start;">1.</td>
                <td style="font-size: 12px; width: 95%; text-align: left;">Cheque should make payable to <span
                        style="font-weight: 700;">POWER COOL EQUIPMENTS (M) SDN BHD</span></td>
            </tr>
            <tr>
                <td style="font-size: 12px; vertical-align: start;">2.</td>
                <td style="font-size: 12px; width: 95%; text-align: left;">Please remit your payment to : <span
                        style="font-weight: 700;">PUBLIC BANK Account No.: 3141 96 7510</span></td>
            </tr>
            <tr>
                <td style="font-size: 12px; vertical-align: start;">3.</td>
                <td style="font-size: 12px; width: 95%; text-align: left;">The Company reserves the right to charge
                    interest 1.5% per month on overdue accounts.</td>
            </tr>
            <tr>
                <td style="font-size: 12px; vertical-align: start;">4.</td>
                <td style="font-size: 12px; width: 100%; text-align: left;">Goods sold and deposit are not returnable &
                    refundable. A cancellation fee of 20% on purchase price will be imposed. If cancel order with credit
                    card payment, will have 10% credit card charges.</td>
            </tr>
            <tr>
                <td style="font-size: 12px; vertical-align: start;">5.</td>
                <td style="font-size: 12px; width: 100%; text-align: left;">Any queries or complaints regarding this
                    invoice must be made within 7 days from date hereof, otherwise any discrepancy will not be
                    entertained.</td>
            </tr>
            <tr>
                <td style="font-size: 12px; vertical-align: start;">6.</td>
                <td style="font-size: 12px; width: 100%; text-align: left;">3 Years Compressor Warranty with T&C
                    apply.<br>
                    * 6 months General Service conduct by IMAX</span><br>
                    * Limited to 1 time change only</span>
                </td>
            </tr>
            <tr>
                <td style="font-size: 12px; vertical-align: start;">7.</td>
                <td style="font-size: 12px; width: 100%; text-align: left;">Customer collect\ unloading\ handling by
                    own, Warranty will be bear by customer.
                </td>
            </tr>
            <tr>
                <td style="font-size: 12px; vertical-align: start;">8.</td>
                <td style="font-size: 12px; width: 100%; text-align: left;">Wear and tear not included in Warranty
                    Claim.
                </td>
            </tr>
            <tr>
                <td style="font-size: 12px; vertical-align: start;">9.</td>
                <td style="font-size: 12px; width: 100%; text-align: left;">Request E-invoice after 72hrs which
                    original invoice have validated by IRB will be charge 5% of the total invoice amount.
                </td>
            </tr>
            <tr>
                <td style="font-size: 12px; vertical-align: start;">10.</td>
                <td style="font-size: 12px; width: 100%; text-align: left;">Company will not obligation on those
                    customers are not require to issue E-invoice.
                </td>
            </tr>
            <tr>
                <td style="font-size: 12px; vertical-align: start;">11.</td>
                <td style="font-size: 12px; width: 100%; text-align: left;">Prices are subject to change without prior
                    notice.
                </td>
            </tr>
        </table>

        <table style="width: 100%; font-family: sans-serif; border-collapse: collapse;">
            <tr>
                <td style="font-size: 12px; padding: 0 0 47px 0; text-align: center; width: 33%; font-weight: 700;">
                    POWER COOL EQUIPMENTS
                    (M) SDN BHD</td>
                <td style="font-size: 12px; padding: 0 0 40px 0; text-align: center; width: 33%;"></td>
                <td style="font-size: 12px; padding: 0 0 40px 0; text-align: center; width: 33%; font-weight: 700;">
                    KNOWLEDGEMENT BY:
                </td>
            </tr>
            <tr>
                <td
                    style="font-weight: 700; font-size: 12px; text-align: center; width: 33%; border-top: solid 1px black; padding: 5px 0 0 0;">
                    Authorised Signature</td>
                <td style="font-size: 12px; padding: 0 0 40px 0; text-align: center; width: 33%;"></td>
                <td
                    style="font-weight: 700; font-size: 12px; text-align: center; width: 33%; border-top: solid 1px black; padding: 5px 0 0 0;">
                    {{ $sale->saleperson->name ?? '' }}</td>
            </tr>
        </table>
    </main>

</body>

</html>
