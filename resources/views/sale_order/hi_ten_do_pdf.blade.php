<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Order | {{ $sku }}</title>
</head>

<style>
    @page {
        margin: 390px 25px 50px 25px;
    }

    header {
        position: fixed;
        top: -365px;
        left: 0px;
        right: 0px;
    }

    #good-receives {
        position: fixed;
        bottom: 0px;
        left: 0px;
        right: 0px;
    }

    #invalid {
        color: red;
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
                <td style="width: 70%; border-bottom: solid 1px black; padding: 0 50px 10px 0; text-align: center;">
                    <span style="font-size: 16px; font-weight: 700;">HI-TEN TRADING SDN BHD <span style="font-size: 12px;">[200501027542
                            (709676-X)]</span></span><br>
                    <span style="font-size: 12px;">NO. 12, RCI PARK, JALAN KESIDANG 2,</span><br>
                    <span style="font-size: 12px;">KAWASAN PERINDUSTRIAN SUNGAI CHOH,</span><br>
                    <span style="font-size: 12px;">48200 SERENDAH, SELANGOR DARUL EHSAN, MALAYSIA.</span><br>
                    <span style="font-size: 12px;">H/P:012-386 8210, 03-6094 1122</span><br>
                    <span style="font-size: 12px;">Service Hotline (HQ-Selangor) : 012-386 8743</span><br>
                    <span style="font-size: 12px;">Email add : <a
                            href="mailto:enquiry@powercool.com.my">enquiry@powercool.com.my</a></span><br>
                    <span style="font-size: 12px;">Website : <a
                            href="imaxrefrigerator.com.my">imaxrefrigerator.com.my</a></span>
                </td>
                <td style="width: 30%; border-bottom: solid 1px black; padding: 0 0 10px 0; vertical-align: text-top;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="font-size: 12px; font-weight: 700;">Delivery Order No.</td>
                            <td style="font-size: 12px; font-weight: 700; width: 5%; vertical-align: start;">:</td>
                            <td style="font-size: 12px; font-weight: 700; width: 50%; vertical-align: start;">{{ $sku }}</td>
                        </tr>
                        <tr>
                            <td style="font-size: 12px;">Date</td>
                            <td style="font-size: 12px;">:</td>
                            <td style="font-size: 12px;">{{ $date }}</td>
                        </tr>
                        <tr>
                            <td style="font-size: 12px;">Your P/O No.</td>
                            <td style="font-size: 12px;">:</td>
                            <td style="font-size: 12px;"></td>
                        </tr>
                        <tr>
                            <td style="font-size: 12px;">Your S/O No.</td>
                            <td style="font-size: 12px;">:</td>
                            <td style="font-size: 12px;">
                                @php
                                    $skus = [];
                                    for ($i = 0; $i < count($sale_orders); $i++) {
                                        if (!in_array($sale_orders[$i]->sku, $skus)) {
                                            $skus[] = $sale_orders[$i]->sku;
                                        }
                                    }
                                @endphp
                                {{ join(', ', $skus) }}
                            </td>
                        </tr>
                        <tr>
                            <td style="font-size: 12px;">Terms</td>
                            <td style="font-size: 12px;">:</td>
                            <td style="font-size: 12px;">{{ $terms == null ? null : ($terms == 'cod' ? 'C.O.D' : $terms . ' Days') }}</td>
                        </tr>
                        <tr>
                            <td style="font-size: 12px;">Salesperson</td>
                            <td style="font-size: 12px;">:</td>
                            <td style="font-size: 12px;">{{ $salesperson->name }}</td>
                        </tr>
                        <tr>
                            <td style="font-size: 12px;">Warehouse</td>
                            <td style="font-size: 12px;">:</td>
                            <td style="font-size: 12px;">{{ $warehouse }}</td>
                        </tr>
                        <tr>
                            <td style="font-size: 12px;">Store</td>
                            <td style="font-size: 12px;">:</td>
                            <td style="font-size: 12px;">{{ $store }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        <table
            style="width: 100%; font-family: sans-serif; border-collapse: collapse; padding: 0 0 0 0;">
            <tr>
                <td colspan="2"
                    style="font-size: 16px; font-weight: 700; width: 65%; padding: 15px 35px 10px 0; text-align: center;">
                    DELIVERY ORDER</td>
            </tr>
            @if (isset($do_status) && $do_status == 1)
                <tr>
                    <td style="font-size: 14px; font-weight: 700; padding: 0px 35px 10px 0; text-align: center;"
                        colspan="2" id="invalid">
                        VOIDED
                    </td>
                </tr>
            @endif
            <tr>
                <td style="padding: 0 35px 15px 0; vertical-align: top;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="font-size: 14px; font-weight: 700;">Billing Address</td>
                        </tr>
                        <tr>
                            <td style="font-size: 12px;" colspan="2">
                                <span style="font-weight: 700;">{{ $customer->tin_number ?? '' }}</span><br>
                                {{ $customer->company_name }}<br>
                                {{ $billing_address->address1 ?? '' }}<br>
                                {{ $billing_address->address2 ?? '' }}<br>
                                {{ $billing_address->address3 ?? '' }}<br>
                                {{ $billing_address->address4 ?? '' }}<br>
                            </td>
                        </tr>
                        <tr>
                            <td style="font-size: 12px; padding: 10px 0 0 0;">TEL: {{ $customer->phone }}</td>
                            <td style="font-size: 12px; padding: 10px 0 0 0;">ATT: {{ strtoupper($customer->prefix ?? '') }} {{ $customer->name ?? '' }}</td>
                        </tr>
                    </table>
                </td>
                <td style="padding: 0 35px 15px 0; vertical-align: top;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="font-size: 14px; font-weight: 700;">Delivery Address</td>
                        </tr>
                        <tr>
                            <td style="font-size: 12px;" colspan="2">
                                <span style="font-weight: 700;">{{ $customer->tin_number ?? '' }}</span><br>
                                {{ $customer->company_name ?? '' }}<br>
                                {{ $delivery_address->address1 ?? '' }}<br>
                                {{ $delivery_address->address2 ?? '' }}<br>
                                {{ $delivery_address->address3 ?? '' }}<br>
                                {{ $delivery_address->address4 ?? '' }}<br>
                            </td>
                        </tr>
                        <tr>
                            <td style="font-size: 12px; padding: 10px 0 0 0;">TEL: {{ $customer->phone }}</td>
                            <td style="font-size: 12px; padding: 10px 0 0 0;">ATT: {{ strtoupper($customer->prefix ?? '') }} {{ $customer->name ?? '' }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </header>

    <main>
        <!-- Item -->
        <table style="width: 100%; font-family: sans-serif; border-collapse: collapse;">
            <tr>
                <td
                    style="font-size: 12px; border-top: solid 1px black; border-bottom: solid 1px black; padding: 5px 0; text-align: left; width: 5%;">
                    Item</td>
                <td
                    style="font-size: 12px; border-top: solid 1px black; border-bottom: solid 1px black; padding: 5px 0; text-align: left; width: 10%;">
                    Stock Code</td>
                <td
                    style="font-size: 12px; border-top: solid 1px black; border-bottom: solid 1px black; padding: 5px 0; text-align: left; width: 45%;">
                    Description</td>
                <td
                    style="font-size: 12px; border-top: solid 1px black; border-bottom: solid 1px black; padding: 5px 0; text-align: right; width: 5%;">
                    Qty</td>
                <td
                    style="font-size: 12px; border-top: solid 1px black; border-bottom: solid 1px black; padding: 5px 0; text-align: right; width: 5%;">
                    UOM</td>
            </tr>
            @php
                $total = 0;
            @endphp
            <!-- Product List -->
            @foreach ($products as $key => $prod)
                <tr>
                    <td style="font-size: 12px; text-align: left; padding: {{ $key == 0 ? '0' : '20px' }} 0 0 0;">
                        {{ $key + 1 }}</td>
                    <td style="font-size: 12px; text-align: left; padding: {{ $key == 0 ? '0' : '20px' }} 0 0 0;">
                        {{ $prod['stock_code'] }}</td>
                    <td style="font-size: 12px; text-align: left; padding: {{ $key == 0 ? '0' : '20px' }} 0 0 0;">
                        {{ $prod['desc'] }}</td>
                    <td style="font-size: 12px; text-align: right; padding: {{ $key == 0 ? '0' : '20px' }} 0 0 0;">
                        {{ $prod['qty'] }}</td>
                    <td style="font-size: 12px; text-align: right; padding: {{ $key == 0 ? '0' : '20px' }} 0 0 0;">
                        {{ $prod['uom'] }}</td>
                </tr>
                <!-- Product Remark -->
                @if (isset($prod['remark']) && $prod['remark'] != null && $prod['remark'] !== '<p><br></p>')
                    <tr>
                        <td colspan="2"></td>
                        <td style="font-size: 10px; text-align: left; font-weight: 700;">
                            Remark:
                        </td>
                        <td style="font-size: 10px; text-align: left;" colspan="2"></td>
                    </tr>
                    <tr>
                        <td colspan="2"></td>
                        <td style="font-size: 10px; text-align: left;">
                            {!! nl2br($prod['remark']) !!}
                        </td>
                        <td style="font-size: 10px; text-align: left;" colspan="2"></td>
                    </tr>
                @endif
                <!-- Warranty -->
                @if ($prod['warranty_periods'] != null && count($prod['warranty_periods']) > 0)
                    @php
                        $warranty = [];
                        foreach ($prod['warranty_periods'] as $wp) {
                            $warranty[] = $wp->warrantyPeriod->name;
                        }
                    @endphp
                    <tr>
                        <td style="font-size: 10px; text-align: left;" colspan="2"></td>
                        <td style="font-size: 10px; text-align: left; font-weight: 700;">
                            Warranty:
                        </td>
                        <td colspan="2"></td>
                    </tr>
                    @foreach ($warranty as $key => $w)
                        <tr>
                            <td style="font-size: 10px; text-align: left;" colspan="2"></td>
                            <td style="font-size: 10px; text-align: left;">
                                @if (count($warranty) == $key + 1)
                                    {{ $w }}
                                @else
                                    {{ $w }}<br>
                                @endif
                            </td>
                            <td colspan="2"></td>
                        </tr>
                    @endforeach
                @endif
                <!-- Serial No -->
                @if (isset($prod['serial_no']) && count($prod['serial_no']) > 0)
                    @php
                        $serial_nos = [];
                        foreach ($prod['serial_no'] as $sn) {
                            $serial_nos[] = $sn['sku'] . ($sn['remark'] ? ', ' . $sn['remark'] : '');
                        }
                    @endphp
                    <tr>
                        <td style="font-size: 10px; text-align: left;" colspan="2"></td>
                        <td style="font-size: 10px; text-align: left;"><b>Serial No:</b><br>
                            {{ join(', ', $serial_nos) }}</td>
                        <td colspan="2"></td>
                    </tr>
                @endif
                <!-- Accessories -->
                @if (isset($prod['accessories']) && count($prod['accessories']) > 0)
                    <tr>
                        <td style="font-size: 10px; text-align: left;" colspan="2"></td>
                        <td style="font-size: 10px; text-align: left; font-weight: 700;">
                            Accessories:
                        </td>
                        <td colspan="2"></td>
                    </tr>
                    @foreach ($prod['accessories'] as $acc)
                        <tr>
                            <td style="font-size: 10px; text-align: left;" colspan="2"></td>
                            <td style="font-size: 10px; text-align: left;">
                                - {{ $acc['sku'] }} - {{ $acc['name'] }} (Qty: {{ $acc['qty'] }})
                                @if ($acc['is_foc'])
                                    <span style="font-weight: bold;"> - FOC</span>
                                @endif
                            </td>
                            <td colspan="2"></td>
                        </tr>
                    @endforeach
                @endif
                @if ($key + 1 == count($products))
                    <tr>
                        <td style="padding: 5px;"></td>
                    </tr>
                @endif
                @php
                    $total += $prod['qty'];
                @endphp
            @endforeach
            <!-- Remark -->
            @foreach ($sale_orders as $key => $so)
                @if ($so->remark != null)
                    <tr>
                        <td colspan="2"></td>
                        <td colspan="2" style="font-size: 12px; padding: 15px 0;"><span
                                style="font-weight: 700;">REMARK:</span><br>{!! $so->remark !!}</td>
                        <td></td>
                    </tr>
                @endif
            @endforeach
        </table>
        <!-- Item Summary -->
        <table style="width: 100%; font-family: sans-serif; border-collapse: collapse;">
            <tr>
                <td style="font-size: 12px; padding: 10px 0 0 0; border-top: solid 1px black; width: 60%;">Goods sold
                    are
                    neither returnable nor refundable. Otherwise a cancellation fee of 20% on purchase price will be
                    imposed.<br>
                    For more information about our policies,please visit our website at<br>
                    https://imaxrefrigerator.com.my/warranty-policy/.
                </td>
                <td
                    style="font-size: 12px; font-weight: 700; text-align: right; padding: 10px 0 0 0; border-top: solid 1px black; vertical-align: text-top;">
                    Total {{ $total }}</td>
            </tr>
        </table>
        <!-- Footer -->
        <table style="width: 100%; font-family: sans-serif; border-collapse: collapse;">
            <tr>
                <td style="font-size: 12px; padding: 25px 0 100px 0;" colspan="4">For : HI-TEN TRADING SDN BHD</td>
            </tr>
            <tr>
                <td
                    style="width: 20%; font-size: 12px; text-align: left; border-top: solid 1px black; padding: 10px 0 0 0; font-weight: 700; vertical-align: text-top;">
                    Authorised Signature</td>
                <td style="width: 10px;"></td>
                <td
                    style="width: 20%; font-size: 12px; text-align: left; border-top: solid 1px black; padding: 10px 0 0 0; font-weight: 700; vertical-align: text-top;">
                    Goods Prepared Signature</td>
                <td style="width: 10px;"></td>
                <td
                    style="width: 20%; font-size: 12px; text-align: left; border-top: solid 1px black; padding: 10px 0 0 0; font-weight: 700; vertical-align: text-top;">
                    Recipient's Chop & Signature<br>Name:<br>IC:</td>
                <td style="width: 10px;"></td>
                <td
                    style="width: 20%; font-size: 12px; text-align: left; border-top: solid 1px black; padding: 10px 0 0 0; font-weight: 700; vertical-align: text-top;">
                    Driver Signature</td>
            </tr>
        </table>
        {{-- Good Receives --}}
        <table style="width: 100%; font-family: sans-serif; border-collapse: collapse;" id="good-receives">
            <tr>
                <td style="font-size: 12px; text-align: center; font-weight: bold; font-style: italic;">GOODS RECEIVED
                    IN GOOD ORDER & CONDITION</td>
            </tr>
        </table>
    </main>

</body>

</html>
