<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cash Sale | {{ $sale->sku }}</title>
</head>

<style>
    @page {
        margin: 460px 25px 50px 25px;
    }

    header {
        position: fixed;
        top: -435px;
        left: 0px;
        right: 0px;
    }

    .remark-content p, .remark-content h1, .remark-content h2, .remark-content span {
        font-size: 12px !important;
    }
</style>

<body>
    <header>
        <!-- Header -->
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
                        <tr>
                            <td style="font-size: 14px;">Warehouse</td>
                            <td style="font-size: 14px;">:</td>
                            <td style="font-size: 14px;">{{ $sale->store ?? '' }}</td>
                        </tr>
                        <tr>
                            <td style="font-size: 14px;">Payment Due Date</td>
                            <td style="font-size: 14px; vertical-align: top;">:</td>
                            <td style="font-size: 14px; vertical-align: top;">
                                {{ $is_paid ? 'Paid' : $sale->payment_due_date ?? '' }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        <table
            style="width: 100%; font-family: sans-serif; border-collapse: collapse; padding: 0 0 0px 0; border-bottom: solid 1px black;">
            <tr>
                <td style="font-size: 16px; font-weight: 700; width: 65%; padding: 15px 35px 10px 0; text-align: center;"
                    colspan="2">CASH SALE</td>
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
                                {{ $sale->custom_customer }}<br>
                                {{ $billing_address->address1 ?? '' }}<br>
                                {{ $billing_address->address2 ?? '' }}<br>
                                {{ $billing_address->address3 ?? '' }}<br>
                                {{ $billing_address->address4 ?? '' }}<br>
                            </td>
                            <td style="font-size: 14px; width: 50%;">
                                {{ $sale->custom_customer }}<br>
                                {{ $delivery_address->address1 ?? '' }}<br>
                                {{ $delivery_address->address2 ?? '' }}<br>
                                {{ $delivery_address->address3 ?? '' }}<br>
                                {{ $delivery_address->address4 ?? '' }}<br>
                            </td>
                        </tr>
                        <tr>
                            <td style="font-size: 14px; padding: 10px 0 0 0; width: 15%;">TEL: {{ $sale->custom_mobile }}
                            </td>
                            <td style="font-size: 14px; padding: 10px 0 0 0; width: 15%;">ATT:
                                {{ $sale->custom_customer ?? '' }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td style="font-size: 14px; padding: 15px 0 15px 0;" colspan="2">Thank you for your inquiry. We are
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
                    style="font-size: 10px; border-bottom: solid 1px black; padding: 0 4px 5px 4px; text-align: left; width: 5%;">
                    Item</td>
                <td
                    style="font-size: 10px; border-bottom: solid 1px black; padding: 0 4px 5px 4px; text-align: left; width: 5%;">
                    Tax Code</td>
                <td
                    style="font-size: 10px; border-bottom: solid 1px black; padding: 0 4px 5px 4px; text-align: left; width: 10%;">
                    Item Code</td>
                <td style="font-size: 10px; border-bottom: solid 1px black; padding: 0 4px 5px 4px; text-align: left;">
                    Description</td>
                <td
                    style="font-size: 10px; border-bottom: solid 1px black; padding: 0 4px 5px 4px; text-align: right; width: 5%;">
                    Qty</td>
                <td
                    style="font-size: 10px; border-bottom: solid 1px black; padding: 0 4px 5px 4px; text-align: right; width: 5%;">
                    UOM</td>
                <td
                    style="font-size: 10px; border-bottom: solid 1px black; padding: 0 4px 5px 4px; text-align: right; width: 10%;">
                    U/Price (RM)</td>
                <td
                    style="font-size: 10px; border-bottom: solid 1px black; padding: 0 4px 5px 4px; text-align: right; width: 10%;">
                    Discount (RM)</td>
                <td
                    style="font-size: 10px; border-bottom: solid 1px black; padding: 0 4px 5px 4px; text-align: right; width: 10%;">
                    Promotion (RM)</td>
                <td
                    style="font-size: 10px; border-bottom: solid 1px black; padding: 0 4px 5px 4px; text-align: right; width: 10%;">
                    Total (RM)</td>
            </tr>
            @php
                $total = 0;
                $total_tax = 0;
            @endphp
            @foreach ($products as $key => $prod)
                <tr>
                    <td
                        style="vertical-align: start; font-size: 10px; text-align: left; padding: {{ $key == 0 ? '0' : '20px' }} 4px 0 4px;">
                        {{ $key + 1 }}</td>
                    <td
                        style="vertical-align: start; font-size: 10px; text-align: left; padding: {{ $key == 0 ? '0' : '20px' }} 4px 0 4px;">
                        {{ $tax_code }}</td>
                    <td
                        style="vertical-align: start; font-size: 10px; text-align: left; padding: {{ $key == 0 ? '0' : '20px' }} 10px 0 4px;">
                        {{ $prod->product->sku }}</td>
                    <td
                        style="vertical-align: start; font-size: 10px; text-align: left; padding: {{ $key == 0 ? '0' : '20px' }} 4px 0 4px;">
                        {{ $prod->product->model_desc }}</td>
                    <td
                        style="vertical-align: start; font-size: 10px; text-align: right; padding: {{ $key == 0 ? '0' : '20px' }} 4px 0 4px;">
                        {{ $prod->qty }}</td>
                    <td
                        style="vertical-align: start; font-size: 10px; text-align: right; padding: {{ $key == 0 ? '0' : '20px' }} 4px 0 4px;">
                        {{ $prod->uom }}</td>
                    <td
                        style="vertical-align: start; font-size: 10px; text-align: right; padding: {{ $key == 0 ? '0' : '20px' }} 4px 0 4px;">
                        {{ number_format($prod->unit_price, 2) }}</td>Price
                    <td
                        style="vertical-align: start; font-size: 10px; text-align: right; padding: {{ $key == 0 ? '0' : '20px' }} 4px 0 4px;">
                        {{ number_format($prod->manualDiscountAmount(), 2) }}</td>
                    <td
                        style="vertical-align: start; font-size: 10px; text-align: right; padding: {{ $key == 0 ? '0' : '20px' }} 4px 0 4px;">
                        {{ number_format($prod->promotionAmount() ?? 0, 2) }}
                    </td>
                    <td
                        style="vertical-align: start; font-size: 10px; text-align: right; padding: {{ $key == 0 ? '0' : '20px' }} 4px 0 4px;">
                        {{ number_format(($prod->override_selling_price ?? $prod->qty * $prod->unit_price) - $prod->discountAmount(), 2) }}
                    </td>
                </tr>
                @if ($prod->remark != null)
                    <tr>
                        <td colspan="10" style="padding: 15px 0 0 0;"></td>
                    </tr>
                    <tr>
                        <td style="font-size: 12px; padding: 5px 0; text-align: left;"></td>
                        <td class="remark-content" style="font-size: 12px; text-align: left; font-weight: 700;" colspan="3">
                            {!! nl2br($prod->remark) !!}</td>
                        <td style="font-size: 12px; padding: 5px 0; text-align: left;" colspan="6"></td>
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
                    @if (count($warranty) > 0)
                        <tr>
                            <td style="font-size: 10px; padding: 5px 0; text-align: left;"></td>
                            <td style="font-size: 10px; text-align: left; font-weight: 700;" colspan="3">
                                Warranty:<br>
                            <td style="font-size: 10px; text-align: left;" colspan="6"></td>
                        </tr>
                        @foreach ($warranty as $key => $w)
                            <tr>
                                <td style="font-size: 10px; padding: 5px 0; text-align: left;"></td>
                                <td style="font-size: 10px; text-align: left; font-weight: 700;" colspan="3">
                                    @if (count($warranty) == $key + 1)
                                        {{ $w }}
                                    @else
                                        {{ $w }}<br>
                                    @endif
                                <td style="font-size: 10px; text-align: left;" colspan="6"></td>
                            </tr>
                        @endforeach
                    @endif
                @endif
                <!-- Serial No -->
                @if ($prod->serial_no != null)
                    <tr>
                        <td style="font-size: 10px; padding: 5px 0; text-align: left;"></td>
                        <td style="font-size: 10px; text-align: left; font-weight: 700;" colspan="3">Serial No:<br>
                            {{ join(', ', $prod->serial_no) }}</td>
                        <td style="font-size: 10px; text-align: left;" colspan="6"></td>
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
                    <td></td>
                    <td class="remark-content" colspan="3" style="font-size: 12px; padding: 15px 0;"><span
                            style="font-weight: 700;">REMARK:</span><br>{!! nl2br($sale->remark) !!}</td>
                    <td colspan="6"></td>
                </tr>
            @endif
            <!-- Third Party Address -->
            @if (isset($third_party_addresses) && count($third_party_addresses) > 0)
                <tr>
                    <td></td>
                    <td colspan="3" style="font-size: 12px; padding: 15px 0 0 0; font-weight: 700;">THIRD PARTY ADDRESS:</td>
                    <td colspan="6"></td>
                </tr>
                @foreach ($third_party_addresses as $tpa)
                    <tr>
                        <td></td>
                        <td colspan="3" style="font-size: 12px; padding: 2px 0;">
                            {{ $tpa->name }} - {{ $tpa->address }} ({{ $tpa->mobile }})
                        </td>
                        <td colspan="6"></td>
                    </tr>
                @endforeach
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
                <td style="font-size: 12px; font-weight: 700; text-align: right; padding: 0px 5px 0 0;"
                    colspan="3">
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
        <table style="width: 100%; font-family: sans-serif; border-collapse: collapse; padding: 0 0 35px 0;">
            @include('partials.tnc', ['company' => 'powercool', 'colspan' => 2])
            @include('partials.duitnow_qr', ['company' => 'powercool', 'colspan' => 2])
        </table>

        <table style="width: 100%; font-family: sans-serif; border-collapse: collapse; page-break-inside: avoid; page-break-before: avoid;">
            <tr>
                <td style="width: 33%; vertical-align: top; padding: 30px 10px 0 0;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td colspan="2" style="font-size: 12px; font-weight: 700; padding: 0 0 60px 0; text-align: center;">
                                POWER COOL EQUIPMENTS (M) SDN BHD
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" style="border-bottom: solid 1px black; padding: 0;"></td>
                        </tr>
                        <tr>
                            <td colspan="2" style="font-size: 12px; font-weight: 700; padding: 4px 0 0 0; text-align: center;">
                                Authorised signature
                            </td>
                        </tr>
                    </table>
                </td>
                <td style="width: 33%;"></td>
                <td style="width: 33%; vertical-align: top; padding: 30px 0 0 10px;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td colspan="2" style="font-size: 12px; font-weight: 700; padding: 0 0 60px 0; text-align: center;">
                                {{ $sale->custom_customer }}<br>&nbsp;
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" style="border-bottom: solid 1px black; padding: 0;"></td>
                        </tr>
                        <tr>
                            <td colspan="2" style="font-size: 12px; font-weight: 700; padding: 4px 0 0 0; text-align: center;">
                                Recipient's Chop &amp; Signature
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" style="font-size: 12px; padding: 15px 0 0 0;">
                                <span style="font-weight: 700;">Name :</span>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" style="font-size: 12px; padding: 5px 0 0 0;">
                                <span style="font-weight: 700;">Passport No. / IC :</span>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </main>

</body>

</html>
