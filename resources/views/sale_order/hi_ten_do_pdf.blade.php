<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Order | {{ $sku }}</title>
</head>

<style>
    @page {
        margin: 225px 25px 50px 25px;
    }

    header {
        position: fixed;
        top: -200px;
        left: 0px;
        right: 0px;
    }

    #good-receives {
        position: fixed;
        bottom: 0px;
        left: 0px;
        right: 0px;
    }
</style>

<body>
    <!-- Header -->
    <header>
        <table style="width: 100%; font-family: sans-serif; border-collapse: collapse;">
            <tr>
                <td style="width: 70%; border-bottom: solid 1px black; padding: 0 50px 10px 0; text-align: center;">
                    <span style="font-size: 18px;">HI-TEN TRADING SDN BHD <span
                            style="font-size: 14px;">[200501027542 (709676-X)]</span></span><br>
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
                <td style="width: 50%; border-bottom: solid 1px black; padding: 0 0 10px 0; vertical-align: text-top;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="font-size: 14px; font-weight: 700;">Delivery Order No.</td>
                            <td style="font-size: 14px; font-weight: 700; width: 5%;">:</td>
                            <td style="font-size: 14px; font-weight: 700; width: 50%;">{{ $sku }}</td>
                        </tr>
                        <tr>
                            <td style="font-size: 14px;">Date</td>
                            <td style="font-size: 14px;">:</td>
                            <td style="font-size: 14px;">{{ $date }}</td>
                        </tr>
                        <tr>
                            <td style="font-size: 14px;">Your P/O No.</td>
                            <td style="font-size: 14px;">:</td>
                            <td style="font-size: 14px;"></td>
                        </tr>
                        <tr>
                            <td style="font-size: 14px;">Your S/O No.</td>
                            <td style="font-size: 14px;">:</td>
                            <td style="font-size: 14px;">
                                @php
                                    $skus = [];
                                    for ($i = 0; $i < count($sale_orders); $i++) {
                                        $skus[] = $sale_orders[$i]->sku;
                                    }
                                @endphp
                                {{ join(', ', $skus) }}
                            </td>
                        </tr>
                        <tr>
                            <td style="font-size: 14px;">Terms</td>
                            <td style="font-size: 14px;">:</td>
                            <td style="font-size: 14px;">{{ $terms == 'cod' ? 'C.O.D' : $terms . ' Days' }}</td>
                        </tr>
                        <tr>
                            <td style="font-size: 14px;">Salesperson</td>
                            <td style="font-size: 14px;">:</td>
                            <td style="font-size: 14px;">{{ $salesperson->name }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </header>

    <main>
        <table style="width: 100%; font-family: sans-serif; border-collapse: collapse; padding: 0 0 25px 0;">
            <tr>
                <td colspan="2"
                    style="font-size: 18px; font-weight: 700; width: 65%; padding: 15px 35px 10px 0; text-align: center;">
                    DELIVERY ORDER</td>
            </tr>
            <tr>
                <td style="padding: 0 35px 0 0;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="font-size: 16px; font-weight: 700;">Billing Address</td>
                        </tr>
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
                            <td style="font-size: 14px; padding: 10px 0 0 0; text-align: start;">FAX: </td>
                        </tr>
                    </table>
                </td>
                <td style="padding: 0 35px 0 0;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="font-size: 16px; font-weight: 700;">Delivery Address</td>
                        </tr>
                        <tr>
                            <td style="font-size: 14px;" colspan="2">
                                {{ $customer->company_name ?? '' }}<br>
                                {{ $delivery_address->address1 ?? '' }}<br>
                                {{ $delivery_address->address2 ?? '' }}<br>
                                {{ $delivery_address->address3 ?? '' }}<br>
                                {{ $delivery_address->address4 ?? '' }}<br>
                            </td>
                        </tr>
                        <tr>
                            <td style="font-size: 14px; padding: 10px 0 0 0;">TEL: {{ $customer->phone }}</td>
                            <td style="font-size: 14px; padding: 10px 0 0 0; text-align: start;">FAX: </td>
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
                    style="font-size: 14px; border-top: solid 1px black; border-bottom: solid 1px black; text-align: left; width: 10%;">
                    Stock Code</td>
                <td
                    style="font-size: 14px; border-top: solid 1px black; border-bottom: solid 1px black; text-align: left; width: 45%;">
                    Description</td>
                <td
                    style="font-size: 14px; border-top: solid 1px black; border-bottom: solid 1px black; text-align: right; width: 5%;">
                    Qty</td>
            </tr>
            @php
                $total = 0;
            @endphp
            <!-- Product List -->
            @foreach ($products as $key => $prod)
                <tr>
                    <td style="font-size: 14px; text-align: left; padding: {{ $key == 0 ? '0' : '20px' }} 0 0 0;">{{ $key + 1 }}</td>
                    <td style="font-size: 14px; text-align: left; padding: {{ $key == 0 ? '0' : '20px' }} 0 0 0;">{{ $prod['stock_code'] }}</td>
                    <td style="font-size: 14px; text-align: left; padding: {{ $key == 0 ? '0' : '20px' }} 0 0 0;">{{ $prod['desc'] }}</td>
                    <td style="font-size: 14px; text-align: right; padding: {{ $key == 0 ? '0' : '20px' }} 0 0 0;">{{ $prod['qty'] }}</td>
                </tr>
                <!-- Warranty -->
                @if ($prod['warranty_periods'] != null)
                    @php
                        $warranty = [];
                        foreach ($prod['warranty_periods'] as $wp) {
                            $warranty[] = $wp->warrantyPeriod->name;
                        }
                    @endphp
                    <tr>
                        <td style="font-size: 14px; text-align: left;" colspan="2"></td>
                        <td style="font-size: 14px; text-align: left; vertical-align: top; font-weight: 700;">Warranty:
                            {{ join(', ', $warranty) }}</td>
                        <td></td>
                    </tr>
                @endif
                <!-- Serial No -->
                @if ($prod['serial_no'] != null)
                    <tr>
                        <td style="font-size: 14px; text-align: left;" colspan="2"></td>
                        <td style="font-size: 14px; text-align: left; font-weight: 700;">Serial No:
                            {{ join(', ', $prod['serial_no']) }}</td>
                        <td></td>
                    </tr>
                @endif
                @if ($key + 1 == count($products))
                    <tr>
                        <td style="padding: 25px;"></td>
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
                        <td colspan="2" style="font-size: 14px; padding: 15px 0;"><span
                                style="font-weight: 700;">REMARK:</span><br>{{ $so->remark }}</td>
                        <td></td>
                    </tr>
                @endif
            @endforeach
        </table>
        <!-- Item Summary -->
        <table style="width: 100%; font-family: sans-serif; border-collapse: collapse;">
            <tr>
                <td style="font-size: 14px; padding: 10px 0 0 0; border-top: solid 1px black; width: 60%;">Goods sold
                    are
                    neither returnable nor refundable. Otherwise a cancellation fee of 20% on purchase price will be
                    imposed.<br>
                    For more information about our policies,please visit our website at<br>
                    https://imaxrefrigerator.com.my/warranty-policy/.
                </td>
                <td
                    style="font-size: 14px; font-weight: 700; text-align: right; padding: 10px 0 0 0; border-top: solid 1px black; vertical-align: text-top;">
                    Total {{ $total }}</td>
            </tr>
        </table>
        <!-- Footer -->
        <table style="width: 100%; font-family: sans-serif; border-collapse: collapse;">
            <tr>
                <td style="font-size: 14px; padding: 25px 0 75px 0;" colspan="4">For : HI-TEN TRADING SDN BHD</td>
            </tr>
            <tr>
                <td
                    style="width: 20%; font-size: 14px; text-align: left; border-top: solid 1px black; padding: 10px 0 0 0; font-weight: 700; vertical-align: text-top;">
                    Authorised Signature</td>
                <td style="width: 10px;"></td>
                <td
                    style="width: 20%; font-size: 14px; text-align: left; border-top: solid 1px black; padding: 10px 0 0 0; font-weight: 700; vertical-align: text-top;">
                    Goods Prepared Signature</td>
                <td style="width: 10px;"></td>
                <td
                    style="width: 20%; font-size: 14px; text-align: left; border-top: solid 1px black; padding: 10px 0 0 0; font-weight: 700; vertical-align: text-top;">
                    Recipient's Chop & Signature<br>Name:<br>IC:</td>
                <td style="width: 10px;"></td>
                <td
                    style="width: 20%; font-size: 14px; text-align: left; border-top: solid 1px black; padding: 10px 0 0 0; font-weight: 700; vertical-align: text-top;">
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
