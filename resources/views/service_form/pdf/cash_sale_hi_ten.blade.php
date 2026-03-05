<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cash Sale | {{ $service_form->sku }}</title>
</head>

<style>
    @page {
        margin: 320px 25px 50px 25px;
    }

    header {
        position: fixed;
        top: -310px;
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
        <table style="width: 100%; font-family: sans-serif; border-collapse: collapse;">
            <tr>
                <td style="width: 100%; border-bottom: solid 1px black; padding: 0 0 10px 0; text-align: center;">
                    <span style="font-size: 16px; font-weight: 700;">HI-TEN TRADING SDN BHD <span
                            style="font-size: 12px;">[200501027542
                            (709676-X)]</span></span><br>
                    <span style="font-size: 14px;">NO. 12, RCI PARK, JALAN KESIDANG 2,</span><br>
                    <span style="font-size: 14px;">KAWASAN PERINDUSTRIAN SUNGAI CHOH,</span><br>
                    <span style="font-size: 14px;">48200 SERENDAH, SELANGOR DARUL EHSAN, MALAYSIA.</span><br>
                    <span style="font-size: 14px;">H/P:012-386 8210, 03-6094 1122</span><br>
                    <span style="font-size: 14px;">Service Hotline (HQ-Selangor) : 012-386 8743</span><br>
                    <span style="font-size: 14px;">Email add : <a
                            href="mailto:enquiry@powercool.com.my">enquiry@powercool.com.my</a></span><br>
                </td>
            </tr>
        </table>
        <table
            style="width: 100%; font-family: sans-serif; border-collapse: collapse; padding: 0 0 0 0;">
            <tr>
                <td
                    style="font-size: 14px; font-weight: 700; width: 33.33%; padding: 15px 0 10px 0; text-align: center;">
                </td>
                <td
                    style="font-size: 16px; font-weight: 700; width: 33.33%; padding: 15px 35px 10px 0; text-align: center;">
                    CASH SALE
                </td>
                <td
                    style="font-size: 14px; font-weight: 700; width: 33%.33; padding: 15px 0 10px 0; text-align: center;">
                    No. :
                    {{ $service_form->sku }}</td>
            </tr>
            <tr>
                <td colspan="2" style="padding: 0 35px 0 0; vertical-align: top;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="font-size: 14px;" colspan="2">
                                @if($customer)
                                    <span style="font-weight: 700;">{{ $customer->tin_number ?? '' }}</span><br>
                                    {{ $customer->company_name ?? $customer->name ?? '' }}<br>
                                    {{ $address }}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td style="font-size: 14px; padding: 10px 0 0 0;">TEL: {{ $customer->phone ?? '' }}</td>
                            <td style="font-size: 14px; padding: 10px 0 0 0;">ATT: {{ $service_form->contact_person ?? '' }}</td>
                        </tr>
                    </table>
                </td>
                <td>
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="font-size: 14px; width: 40%;">Date</td>
                            <td style="font-size: 14px; width: 10%;">:</td>
                            <td style="font-size: 14px;">{{ $date }}</td>
                        </tr>
                        <tr>
                            <td style="font-size: 14px;">D/O No.</td>
                            <td style="font-size: 14px;">:</td>
                            <td style="font-size: 14px;"></td>
                        </tr>
                        <tr>
                            <td style="font-size: 14px;">Terms</td>
                            <td style="font-size: 14px;">:</td>
                            <td style="font-size: 14px;">C.O.D</td>
                        </tr>
                        <tr>
                            <td style="font-size: 14px;">Technician</td>
                            <td style="font-size: 14px;">:</td>
                            <td style="font-size: 14px;">{{ $service_form->technician->name ?? '' }}</td>
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
                    style="font-size: 12px; border-bottom: solid 1px black; padding: 0 0 5px 0; text-align: left; width: 12%;">
                    Stock Code</td>
                <td style="font-size: 12px; border-bottom: solid 1px black; padding: 0 0 5px 0; text-align: left;">
                    Description</td>
                <td
                    style="font-size: 12px; border-bottom: solid 1px black; padding: 0 0 5px 0; text-align: center; width: 5%;">
                    Qty</td>
                <td
                    style="font-size: 12px; border-bottom: solid 1px black; padding: 0 0 5px 0; text-align: center; width: 8%;">
                    UOM</td>
                <td
                    style="font-size: 12px; border-bottom: solid 1px black; padding: 0 0 5px 0; text-align: right; width: 12%;">
                    U/Price (RM)</td>
                <td
                    style="font-size: 12px; border-bottom: solid 1px black; padding: 0 0 5px 0; text-align: right; width: 12%;">
                    Disc. (RM)</td>
                <td
                    style="font-size: 12px; border-bottom: solid 1px black; padding: 0 0 5px 0; text-align: right; width: 12%;">
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
                @endphp
                <tr>
                    <td
                        style="vertical-align: start; font-size: 12px; text-align: left; padding: {{ $key == 0 ? '5px' : '15px' }} 0 0 0;">
                        {{ $key + 1 }}</td>
                    <td
                        style="vertical-align: start; font-size: 12px; text-align: left; padding: {{ $key == 0 ? '5px' : '15px' }} 10px 0 0;">
                        {{ $prod->getItemCode() }}</td>
                    <td
                        style="vertical-align: start; font-size: 12px; text-align: left; padding: {{ $key == 0 ? '5px' : '15px' }} 0 0 0;">
                        {{ $prod->getDescription() }}
                        @if($prod->is_foc)
                            <span style="color: #666;">(FOC)</span>
                        @endif
                    </td>
                    <td
                        style="vertical-align: start; font-size: 12px; text-align: center; padding: {{ $key == 0 ? '5px' : '15px' }} 0 0 0;">
                        {{ $prod->qty }}</td>
                    <td
                        style="vertical-align: start; font-size: 12px; text-align: center; padding: {{ $key == 0 ? '5px' : '15px' }} 0 0 0;">
                        {{ $prod->uom ?? '-' }}</td>
                    <td
                        style="vertical-align: start; font-size: 12px; text-align: right; padding: {{ $key == 0 ? '5px' : '15px' }} 0px 0 0;">
                        {{ $prod->is_foc ? '-' : number_format($prod->unit_price, 2) }}</td>
                    <td
                        style="vertical-align: start; font-size: 12px; text-align: right; padding: {{ $key == 0 ? '5px' : '15px' }} 0px 0 0;">
                        {{ number_format($prod->discount ?? 0, 2) }}</td>
                    <td
                        style="vertical-align: start; font-size: 12px; text-align: right; padding: {{ $key == 0 ? '5px' : '15px' }} 0 0 0;">
                        {{ number_format($lineTotal, 2) }}</td>
                </tr>
                <!-- Product Remark -->
                @if ($prod->remark != null)
                    <tr>
                        <td colspan="2"></td>
                        <td style="font-size: 10px; text-align: left; padding-top: 5px;">
                            <span style="font-weight: 700;">Remark:</span> {!! $prod->remark !!}
                        </td>
                        <td colspan="5"></td>
                    </tr>
                @endif
                <!-- Product Warranty -->
                @if ($prod->warrantyPeriods && $prod->warrantyPeriods->count() > 0)
                    <tr>
                        <td colspan="2"></td>
                        <td style="font-size: 10px; text-align: left; padding-top: 5px;">
                            <span style="font-weight: 700;">Warranty:</span>
                            @foreach ($prod->warrantyPeriods as $wp)
                                {{ $wp->warrantyPeriod->name }}@if (!$loop->last), @endif
                            @endforeach
                        </td>
                        <td colspan="5"></td>
                    </tr>
                @endif
            @endforeach
            <!-- Quotation Remark -->
            @if ($service_form->quotation_remark != null)
                <tr>
                    <td colspan="2"></td>
                    <td style="font-size: 12px; padding: 15px 0;"><span
                            style="font-weight: 700;">REMARK:</span><br>{!! nl2br(e($service_form->quotation_remark)) !!}</td>
                    <td colspan="5"></td>
                </tr>
            @endif
        </table>
        <!-- Item Summary -->
        <table style="width: 100%; font-family: sans-serif; border-collapse: collapse;">
            <tr>
                <td
                    style="font-size: 12px; padding: 10px 0 0 0; border-top: solid 1px black; width: 70%; text-transform: uppercase;">
                    {{ priceToWord(number_format($subtotal + $totalTax, 2)) }}
                </td>
                <td
                    style="font-size: 12px; font-weight: 700; text-align: right; padding: 10px 0 0 0; border-top: solid 1px black;">
                    Total RM{{ number_format($subtotal + $totalTax, 2) }}</td>
            </tr>
        </table>

        <!-- Footer -->
        <table style="width: 50%; font-family: sans-serif; border-collapse: collapse;">
            <tr>
                <td style="font-size: 14px; padding: 50px 0 0 0;">Note:</td>
            </tr>
            <tr>
                <td style="font-size: 12px; padding: 0 0 50px 0;">
                    1. All cheques should be crossed and made payable to<br>
                    HI-TEN TRADING SDN BHD<br>
                    AMB BANK A/C 888-1039-10-4518<br>
                    CIMB BANK A/C 8603-16-3872<br>
                    2.  Goods sold are neither returnable nor refundable. Otherwise
                    a cancellation fee of 20% on purchase price will be imposed.
                </td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td style="font-size: 14px; text-align: center; border-top: solid 1px black; padding: 10px 0 0 0; font-weight: 700;">Authorised Signature</td>
            </tr>
        </table>
    </main>
</body>

</html>
