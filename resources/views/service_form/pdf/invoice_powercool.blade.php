<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice | {{ $service_form->sku }}</title>
</head>

<style>
    @page {
        margin: 380px 25px 50px 25px;
    }

    header {
        position: fixed;
        top: -360px;
        left: 0px;
        right: 0px;
    }

    p {
        margin: 0;
        padding: 0;
    }
</style>

<body>
    <!-- Header -->
    <header>
        <!-- Company Info + Reference Fields -->
        <table style="width: 100%; font-family: sans-serif; border-collapse: collapse;">
            <tr>
                <td style="width: 60%; border-bottom: solid 1px black; padding: 0 0 10px 0; vertical-align: top;">
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
                <td style="width: 40%; border-bottom: solid 1px black; padding: 0 0 10px 0; vertical-align: top;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="font-size: 14px; width: 40%; font-weight: 700;">Invoice No.</td>
                            <td style="font-size: 14px; width: 10%;">:</td>
                            <td style="font-size: 14px; font-weight: 700;">{{ $service_form->sku }}</td>
                        </tr>
                        <tr>
                            <td style="font-size: 14px;">Date</td>
                            <td style="font-size: 14px;">:</td>
                            <td style="font-size: 14px;">{{ $date }}</td>
                        </tr>
                        <tr>
                            <td style="font-size: 14px;">Your Ref.</td>
                            <td style="font-size: 14px;">:</td>
                            <td style="font-size: 14px;"></td>
                        </tr>
                        <tr>
                            <td style="font-size: 14px;">Our D/O No.</td>
                            <td style="font-size: 14px;">:</td>
                            <td style="font-size: 14px;"></td>
                        </tr>
                        <tr>
                            <td style="font-size: 14px;">Terms</td>
                            <td style="font-size: 14px;">:</td>
                            <td style="font-size: 14px;">C.O.D</td>
                        </tr>
                        <tr>
                            <td style="font-size: 14px;">Salesperson</td>
                            <td style="font-size: 14px;">:</td>
                            <td style="font-size: 14px;"></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        <!-- Title -->
        <table style="width: 100%; font-family: sans-serif; border-collapse: collapse;">
            <tr>
                <td style="font-size: 16px; font-weight: 700; padding: 15px 0 10px 0; text-align: center;">
                    INVOICE
                </td>
            </tr>
        </table>
        <!-- Billing & Delivery Address -->
        <table style="width: 100%; font-family: sans-serif; border-collapse: collapse;">
            <tr>
                <td style="width: 50%; padding: 0 10px 0 0; vertical-align: top;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="font-size: 14px; font-weight: 700; padding: 0 0 5px 0;" colspan="3">TIN No: {{ $customer->tin_number ?? '' }}</td>
                        </tr>
                        <tr>
                            <td style="font-size: 14px; font-weight: 700;" colspan="3">Billing Address</td>
                        </tr>
                        <tr>
                            <td style="font-size: 14px;" colspan="3">
                                @if($customer)
                                    {{ $customer->company_name ?? $customer->name ?? '' }}<br>
                                    {{ $address }}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td style="font-size: 14px; padding: 5px 0 0 0;" colspan="3">
                                {{ $service_form->contact_person ?? '' }}
                            </td>
                        </tr>
                        <tr>
                            <td style="font-size: 14px; padding: 0 0 0 0;" colspan="3">
                                TEL: {{ $customer->phone ?? '' }} &nbsp;&nbsp;&nbsp;&nbsp; FAX:
                            </td>
                        </tr>
                    </table>
                </td>
                <td style="width: 50%; padding: 0 0 0 10px; vertical-align: top;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="font-size: 14px; font-weight: 700;" colspan="3">Delivery Address</td>
                        </tr>
                        <tr>
                            <td style="font-size: 14px;" colspan="3">
                                @if($customer)
                                    {{ $customer->company_name ?? $customer->name ?? '' }}<br>
                                    {{ $address }}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td style="font-size: 14px; padding: 5px 0 0 0;" colspan="3">
                                {{ $service_form->contact_person ?? '' }}
                            </td>
                        </tr>
                        <tr>
                            <td style="font-size: 14px; padding: 0 0 0 0;" colspan="3">
                                TEL: {{ $customer->phone ?? '' }} &nbsp;&nbsp;&nbsp;&nbsp; FAX: &nbsp;&nbsp;&nbsp;&nbsp; Warehouse: HQ
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </header>

    <main>
        <!-- Item -->
        <table style="width: 100%; font-family: sans-serif; border-collapse: collapse; border-top: solid 1px black;">
            <tr>
                <td
                    style="font-size: 12px; border-bottom: solid 1px black; padding: 0 0 5px 0; text-align: left; width: 5%;">
                    Item</td>
                <td
                    style="font-size: 12px; border-bottom: solid 1px black; padding: 0 0 5px 0; text-align: left; width: 8%;">
                    Tax Code</td>
                <td
                    style="font-size: 12px; border-bottom: solid 1px black; padding: 0 0 5px 0; text-align: left; width: 10%;">
                    Stock Code</td>
                <td style="font-size: 12px; border-bottom: solid 1px black; padding: 0 0 5px 0; text-align: left;">
                    Description</td>
                <td
                    style="font-size: 12px; border-bottom: solid 1px black; padding: 0 0 5px 0; text-align: right; width: 6%;">
                    FOC Qty</td>
                <td
                    style="font-size: 12px; border-bottom: solid 1px black; padding: 0 0 5px 0; text-align: right; width: 5%;">
                    Qty</td>
                <td
                    style="font-size: 12px; border-bottom: solid 1px black; padding: 0 0 5px 0; text-align: right; width: 5%;">
                    UOM</td>
                <td
                    style="font-size: 12px; border-bottom: solid 1px black; padding: 0 0 5px 0; text-align: right; width: 10%;">
                    U/Price (RM)</td>
                <td
                    style="font-size: 12px; border-bottom: solid 1px black; padding: 0 0 5px 0; text-align: right; width: 8%;">
                    Disc. (RM)</td>
                <td
                    style="font-size: 12px; border-bottom: solid 1px black; padding: 0 0 5px 0; text-align: right; width: 10%;">
                    Total (RM)</td>
            </tr>
            @php
                $subtotal = 0;
                $totalTax = 0;
            @endphp
            @foreach ($products as $key => $prod)
                @php
                    $lineTotal = $prod->is_foc ? 0 : ($prod->qty * $prod->unit_price - ($prod->discount ?? 0));
                    $lineTotal = max(0, $lineTotal);
                    $sstAmount = ($prod->with_sst && !$prod->is_foc) ? ($lineTotal * $sst_value / 100) : 0;
                    $subtotal += $lineTotal;
                    $totalTax += $sstAmount;
                    $taxCode = ($prod->with_sst && !$prod->is_foc) ? 'SR' : '';
                    $focQty = $prod->is_foc ? $prod->qty : '';
                    $displayQty = $prod->is_foc ? '' : $prod->qty;
                @endphp
                <tr>
                    <td
                        style="vertical-align: start; font-size: 12px; text-align: left; padding: {{ $key == 0 ? '5px' : '15px' }} 0 0 0;">
                        {{ $key + 1 }}</td>
                    <td
                        style="vertical-align: start; font-size: 12px; text-align: left; padding: {{ $key == 0 ? '5px' : '15px' }} 0 0 0;">
                        {{ $taxCode }}</td>
                    <td
                        style="vertical-align: start; font-size: 12px; text-align: left; padding: {{ $key == 0 ? '5px' : '15px' }} 5px 0 0;">
                        {{ $prod->getItemCode() }}
                    </td>
                    <td
                        style="vertical-align: start; font-size: 12px; text-align: left; padding: {{ $key == 0 ? '5px' : '15px' }} 0 0 0;">
                        {{ $prod->getDescription() }}
                    </td>
                    <td
                        style="vertical-align: start; font-size: 12px; text-align: right; padding: {{ $key == 0 ? '5px' : '15px' }} 0px 0 0;">
                        {{ $focQty }}</td>
                    <td
                        style="vertical-align: start; font-size: 12px; text-align: right; padding: {{ $key == 0 ? '5px' : '15px' }} 0px 0 0;">
                        {{ $displayQty }}</td>
                    <td
                        style="vertical-align: start; font-size: 12px; text-align: right; padding: {{ $key == 0 ? '5px' : '15px' }} 0px 0 0;">
                        {{ $prod->uom ?? '-' }}</td>
                    <td
                        style="vertical-align: start; font-size: 12px; text-align: right; padding: {{ $key == 0 ? '5px' : '15px' }} 0px 0 0;">
                        {{ $prod->is_foc ? '-' : number_format($prod->unit_price, 2) }}</td>
                    <td
                        style="vertical-align: start; font-size: 12px; text-align: right; padding: {{ $key == 0 ? '5px' : '15px' }} 0px 0 0;">
                        {{ number_format($prod->discount ?? 0, 2) }}
                    </td>
                    <td
                        style="vertical-align: start; font-size: 12px; text-align: right; padding: {{ $key == 0 ? '5px' : '15px' }} 0 0 0;">
                        {{ number_format($lineTotal, 2) }}</td>
                </tr>
                <!-- Product Remark -->
                @if ($prod->remark != null)
                    <tr>
                        <td colspan="3"></td>
                        <td style="font-size: 10px; text-align: left; padding-top: 5px;">
                            <span style="font-weight: 700;">Remark:</span> {!! $prod->remark !!}
                        </td>
                        <td colspan="6"></td>
                    </tr>
                @endif
                <!-- Product Warranty -->
                @if ($prod->warrantyPeriods && $prod->warrantyPeriods->count() > 0)
                    <tr>
                        <td colspan="3"></td>
                        <td style="font-size: 10px; text-align: left; padding-top: 5px;">
                            <span style="font-weight: 700;">Warranty:</span>
                            @foreach ($prod->warrantyPeriods as $wp)
                                {{ $wp->warrantyPeriod->name }}@if (!$loop->last), @endif
                            @endforeach
                        </td>
                        <td colspan="6"></td>
                    </tr>
                @endif
            @endforeach
            <!-- Quotation Remark -->
            @if ($service_form->quotation_remark != null)
                <tr>
                    <td colspan="3"></td>
                    <td style="font-size: 12px; padding: 15px 0;"><span
                            style="font-weight: 700;">REMARK:</span><br>{!! nl2br(e($service_form->quotation_remark)) !!}</td>
                    <td colspan="6"></td>
                </tr>
            @endif
        </table>
        <!-- Item Summary -->
        <table style="width: 100%; font-family: sans-serif; border-collapse: collapse;">
            <tr>
                <td
                    style="font-size: 12px; padding: 10px 0 0 0; border-top: solid 1px black; width: 60%; text-transform: uppercase;">
                    {{ priceToWord(number_format($subtotal + $totalTax, 2)) }}
                </td>
                <td
                    style="font-size: 12px; font-weight: 700; text-align: right; padding: 10px 5px 0 0; border-top: solid 1px black;">
                    Sub Total (Excluding SST)
                </td>
                <td style="padding: 10px 0 0 0; border-top: solid 1px black; width: 12%;">
                    <p
                        style="margin: 0; font-size: 12px; font-weight: 700; border: solid 1px black; padding: 2.5px 10px; text-align: right;">
                        {{ number_format($subtotal, 2) }}</p>
                </td>
            </tr>
            <tr>
                <td></td>
                <td style="font-size: 12px; font-weight: 700; text-align: right; padding: 0px 5px 0 0;">
                    Tax @ {{ $sst_value }}% on
                </td>
                <td style="padding: 0px 0 0 0;">
                    <p
                        style="margin: 0; font-size: 12px; font-weight: 700; border: solid 1px black; padding: 2.5px 10px; text-align: right;">
                        {{ number_format($totalTax, 2) }}</p>
                </td>
            </tr>
            <tr>
                <td></td>
                <td style="font-size: 12px; font-weight: 700; text-align: right; padding: 0px 5px 0 0;">Total
                    (Inclusive of SST)
                </td>
                <td style="padding: 0px 0 0 0;">
                    <p
                        style="margin: 0; font-size: 12px; font-weight: 700; border: solid 1px black; padding: 2.5px 10px; text-align: right;">
                        {{ number_format($subtotal + $totalTax, 2) }}</p>
                </td>
            </tr>
        </table>

        <!-- Notes -->
        <table style="width: 100%; font-family: sans-serif; border-collapse: collapse; border-top: solid 1px black; padding: 10px 0 0 0;">
            <tr>
                <td style="font-size: 12px; padding: 10px 0 0 0;" colspan="3">
                    <span style="font-weight: 700;">Notes:</span><br>
                    1. All cheques should be crossed and made payable to POWER COOL EQUIPMENTS (M) SDN BHD<br>
                    2. Please remit your payment to: <span style="font-weight: 700;">Public Bank Ac No.: 3141967510</span><br>
                    3. The Company reserves the right to charge interest 1.5% per month on overdue accounts.<br>
                    4. Goods sold are not returnable & refundable. A cancellation fee of 20% on purchase price will be imposed.<br>
                    5. Any queries or complaints regarding this invoice must be made within 7 days from date hereof, otherwise any discrepancy will not be entertained.<br>
                    6. Request E-invoice after 72hrs which original invoice have validated by IRB will be charge 5% of the total invoice amount.<br>
                    7. Company will not obligation on those customers are note required to issue E-invoice.
                </td>
            </tr>
        </table>

        <!-- Signature -->
        <table style="width: 33%; font-family: sans-serif; border-collapse: collapse; padding: 25px 0 0 0;">
            <tr>
                <td style="font-size: 11px; padding: 0 0 50px 0; text-align: center; font-weight: 700;">POWER COOL EQUIPMENTS (M) SDN BHD</td>
            </tr>
                <td style="font-size: 12px; text-align: center; border-top: solid 1px black; padding: 10px 0 0 0; font-weight: 700;">Authorised Signature</td>
            </tr>
        </table>
    </main>

</body>

</html>
