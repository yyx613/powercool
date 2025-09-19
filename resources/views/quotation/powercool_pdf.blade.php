<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quotation | {{ $sale->sku }}</title>
</head>

<style>
    @page {
        margin: 450px 25px 50px 25px;
    }

    header {
        position: fixed;
        top: -425px;
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
                    <span style="font-size: 16px; font-weight: 700;">POWER COOL EQUIPMENTS (M) SDN BHD</span><br>
                    <span style="font-size: 10px;">[199601010696 (383045-D)]</span><br>
                    <span style="font-size: 14px;">NO:12,RCI PARK,JALAN KESIDANG 2,</span><br>
                    <span style="font-size: 14px;">KAWASAN PERINDUSTRIAN SUNGAI CHOH,</span><br>
                    <span style="font-size: 14px;">48200 SERENDAH,SELANGOR.</span><br>
                    <span style="font-size: 14px;">Tel: 603-6094 1122 <span style="padding: 0 0 0 15px;">Service
                            Hotline: 012-386 8743</span></span><br>
                    <span style="font-size: 14px;">Email : enquiry@powercool.com.my</span><br>
                    <span style="font-size: 14px;">Sales Tax ID No : B16-1809-22000036</span><br>
                </td>
                <td style="width: 30%; border-bottom: solid 1px black; padding: 0 0 10px 0; vertical-align: text-top;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="font-size: 14px; width: 30%; font-weight: 700;">No</td>
                            <td style="font-size: 14px; width: 10%;">:</td>
                            <td style="font-size: 14px; font-weight: 700;">{{ $sale->sku }}</td>
                        </tr>
                        <tr>
                            <td style="font-size: 14px;">Your Ref</td>
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
                        <tr>
                            <td style="font-size: 14px;">Warehouse</td>
                            <td style="font-size: 14px;">:</td>
                            <td style="font-size: 14px;">{{ $sale->warehouse ?? '' }}</td>
                        </tr>
                        <tr>
                            <td style="font-size: 14px;">Store</td>
                            <td style="font-size: 14px;">:</td>
                            <td style="font-size: 14px;">{{ $sale->store ?? '' }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        <table
            style="width: 100%; font-family: sans-serif; border-collapse: collapse; padding: 0 0 0 0; border-bottom: solid 1px black;">
            <tr>
                <td
                    style="font-size: 14px; font-weight: 700; width: 33.33%; padding: 15px 0 10px 0; text-align: center;">
                </td>
                <td
                    style="font-size: 16px; font-weight: 700; width: 33.33%; padding: 15px 35px 10px 0; text-align: center;">
                    QUOTATION</td>
                <td
                    style="font-size: 14px; font-weight: 700; width: 33.33%; padding: 15px 0 10px 0; text-align: center;">
                </td>
            </tr>
            @if ($sale->status == 3)
                <tr>
                    <td
                        style="font-size: 14px; font-weight: 700; width: 33.33%; padding: 0px 0 10px 0; text-align: center;">
                    </td>
                    <td style="font-size: 14px; font-weight: 700; width: 33.33%; padding: 0px 35px 10px 0; text-align: center;"
                        id="invalid">
                        CANCELLED
                    </td>
                    <td
                        style="font-size: 14px; font-weight: 700; width: 33%.33; padding: 0px 0 10px 0; text-align: center;">
                    </td>
                </tr>
            @endif
            <tr>
                <td style="padding: 0 35px 0 0;" colspan="3">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="font-size: 14px; padding: 10px 0 0 0; font-weight: 700; width: 50%;">Billing
                                Address:</td>
                            <td style="font-size: 14px; padding: 10px 0 0 0; font-weight: 700; width: 50%;">Delivery
                                Address:</td>
                        </tr>
                        <tr>
                            <td style="font-size: 14px; width: 50%;">
                                <span style="font-weight: 700;">{{ $customer->tin_number ?? '' }}</span><br>
                                {{ $customer->company_name }}<br>
                                {{ $billing_address->address1 ?? '' }}<br>
                                {{ $billing_address->address2 ?? '' }}<br>
                                {{ $billing_address->address3 ?? '' }}<br>
                                {{ $billing_address->address4 ?? '' }}<br>
                            </td>
                            <td style="font-size: 14px; width: 50%;">
                                <span style="font-weight: 700;">{{ $customer->tin_number ?? '' }}</span><br>
                                {{ $customer->company_name }}<br>
                                {{ $delivery_address->address1 ?? '' }}<br>
                                {{ $delivery_address->address2 ?? '' }}<br>
                                {{ $delivery_address->address3 ?? '' }}<br>
                                {{ $delivery_address->address4 ?? '' }}<br>
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
                <td style="font-size: 14px; padding: 15px 0 15px 0;" colspan="3">Thank you for your inquiry. We are
                    pleased to submit
                    our
                    quote as follows:</td>
            </tr>
        </table>
    </header>

    <main>
        <!-- Item -->
        <table style="width: 100%; font-family: sans-serif; border-collapse: collapse;">
            <tr>
                <td
                    style="font-size: 12px; border-bottom: solid 1px black; padding: 0 0 5px 0; text-align: left; width: 5%;">
                    Item</td>
                <td
                    style="font-size: 12px; border-bottom: solid 1px black; padding: 0 0 5px 0; text-align: left; width: 10%;">
                    Tax Code</td>
                <td
                    style="font-size: 12px; border-bottom: solid 1px black; padding: 0 0 5px 0; text-align: left; width: 10%;">
                    Item Code</td>
                <td style="font-size: 12px; border-bottom: solid 1px black; padding: 0 0 5px 0; text-align: left;">
                    Description</td>
                <td
                    style="font-size: 12px; border-bottom: solid 1px black; padding: 0 0 5px 0; text-align: right; width: 5%;">
                    Qty</td>
                <td
                    style="font-size: 12px; border-bottom: solid 1px black; padding: 0 0 5px 0; text-align: right; width: 5%;">
                    UOM</td>
                <td
                    style="font-size: 12px; border-bottom: solid 1px black; padding: 0 0 5px 0; text-align: right; width: 12.5%;">
                    U/Price (RM)</td>
                <td
                    style="font-size: 12px; border-bottom: solid 1px black; padding: 0 0 5px 0; text-align: right; width: 12.5%;">
                    Discount (RM)</td>
                <td
                    style="font-size: 12px; border-bottom: solid 1px black; padding: 0 0 5px 0; text-align: right; width: 12.5%;">
                    Total (RM)</td>
            </tr>
            @php
                $total = 0;
                $total_tax = 0;
            @endphp
            @foreach ($products as $key => $prod)
                <tr>
                    <td
                        style="vertical-align: start; font-size: 12px; text-align: left; padding: {{ $key == 0 ? '0' : '20px' }} 0 0 0;">
                        {{ $key + 1 }}</td>
                    <td
                        style="vertical-align: start; font-size: 12px; text-align: left; padding: {{ $key == 0 ? '0' : '20px' }} 0 0 0;">
                        {{ $tax_code }}</td>
                    <td
                        style="vertical-align: start; font-size: 12px; text-align: left; padding: {{ $key == 0 ? '0' : '20px' }} 10px 0 0;">
                        {{ $prod->product->sku }}
                    </td>
                    <td
                        style="vertical-align: start; font-size: 12px; text-align: left; padding: {{ $key == 0 ? '0' : '20px' }} 0 0 0;">
                        {{ $prod->product->model_name }}</td>
                    <td
                        style="vertical-align: start; font-size: 12px; text-align: right; padding: {{ $key == 0 ? '0' : '20px' }} 0px 0 0;">
                        {{ $prod->qty }}</td>
                    <td
                        style="vertical-align: start; font-size: 12px; text-align: right; padding: {{ $key == 0 ? '0' : '20px' }} 0px 0 0;">
                        {{ $prod->uom }}</td>
                    <td
                        style="vertical-align: start; font-size: 12px; text-align: right; padding: {{ $key == 0 ? '0' : '20px' }} 0px 0 0;">
                        {{ number_format($prod->unit_price, 2) }}</td>
                    <td
                        style="vertical-align: start; font-size: 12px; text-align: right; padding: {{ $key == 0 ? '0' : '20px' }} 0px 0 0;">
                        {{ number_format($prod->discountAmount(), 2) }}
                    </td>
                    <td
                        style="vertical-align: start; font-size: 12px; text-align: right; padding: {{ $key == 0 ? '0' : '20px' }} 0 0 0;">
                        {{ number_format($prod->qty * $prod->unit_price - $prod->discountAmount(), 2) }}</td>
                </tr>
                @if ($prod->remark != null)
                    <tr>
                        <td colspan="7" style="padding: 15px 0 0 0;"></td>
                    </tr>
                    <tr>
                        <td style="font-size: 12px; padding: 5px 0; text-align: left;" colspan="2"></td>
                        <td style="font-size: 12px; text-align: left; font-weight: 700;" colspan="2">
                            {!! nl2br($prod->remark) !!}</td>
                        <td style="font-size: 12px; padding: 5px 0; text-align: left;" colspan="3"></td>
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
                <td style="font-size: 12px; font-weight: 700;">Payment Method</td>
                <td style="font-size: 12px; font-weight: 700;">:</td>
                <td style="font-size: 12px; font-weight: 700;">{{ $sale->paymentMethod->name ?? null }}</td>
                <td style="font-size: 12px; font-weight: 700; text-align: right; padding: 0px 5px 0 0;">
                    Tax @ {{ $sst_value }}% on
                </td>
                <td style="padding: 0px 0 0 0;">
                    <p
                        style="margin: 0; font-size: 12px; font-weight: 700; border: solid 1px black; padding: 2.5px 10px; text-align: right;">
                        {{ number_format($total_tax, 2) }}</p>
                </td>
            </tr>
            @if ($show_payment_term)
                <tr>
                    <td style="font-size: 12px; font-weight: 700;">Payment Term</td>
                    <td style="font-size: 12px; font-weight: 700;">:</td>
                    <td style="font-size: 12px; font-weight: 700;">{{ $payment_term ?? null }}</td>
                    <td></td>
                    <td></td>
                </tr>
            @endif
            <tr>
                <td style="font-size: 12px; font-weight: 700;"></td>
                <td style="font-size: 12px; font-weight: 700;"></td>
                <td style="font-size: 12px; font-weight: 700;"></td>
                <td style="font-size: 12px; font-weight: 700; text-align: right; padding: 0px 5px 0 0;">Total
                    (Inclusive
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
        <table style="width: 100%; font-family: sans-serif; border-collapse: collapse; padding: 0 0 15px 0;">
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
                <td style="font-size: 12px; width: 100%; text-align: left;">
                    The warranty will be considered void if the refrigerator has been modified or its original
                    specifications altered without prior authorization from the manufacturer.
                </td>
            </tr>
            <tr>
                <td style="font-size: 12px; vertical-align: start;">10.</td>
                <td style="font-size: 12px; width: 100%; text-align: left;">Request E-invoice after 72hrs which
                    original invoice have validated by IRB will be charge 5% of the total invoice amount.
                </td>
            </tr>
            <tr>
                <td style="font-size: 12px; vertical-align: start;">11.</td>
                <td style="font-size: 12px; width: 100%; text-align: left;">Company will not obligation on those
                    customers are not require to issue E-invoice.
                </td>
            </tr>
            <tr>
                <td style="font-size: 12px; vertical-align: start;">12.</td>
                <td style="font-size: 12px; width: 100%; text-align: left;">Prices are subject to change without prior
                    notice.
                </td>
            </tr>
        </table>

        <table style="width: 100%; font-family: sans-serif; border-collapse: collapse;">
            <tr>
                <td style="font-size: 12px; padding: 0 0 35px 0;" colspan="2">
                    We hope that our quotation is favourable to you and looking forward to receive your valued orders in
                    due
                    course. Thank and regards.
                </td>
            </tr>
            <tr>
                <td style="font-size: 12px; padding: 0 0 25px 0; text-align: center; width: 33%;">Your Faithfully</td>
                <td></td>
            </tr>
            <tr>
                <td
                    style="font-size: 12px; text-align: center; width: 33%; border-bottom: solid 1px black; padding: 10px 0 0 0; font-family: serif;">
                    {{ $sale->saleperson->name }}</td>
                <td></td>
            </tr>
            <tr>
                <td style="font-size: 12px; padding: 30px 0 0 0;" colspan="2">This is a computer generated
                    documents no signature required.</td>
            </tr>
        </table>
    </main>

</body>

</html>
