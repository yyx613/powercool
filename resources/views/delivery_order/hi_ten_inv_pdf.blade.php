<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice | {{ $sku }}</title>
</head>
<style>
    @page {
        margin: 370px 25px 50px 25px;
    }

    header {
        position: fixed;
        top: -345px;
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
                    <span style="font-size: 16px; font-weight: 700;">HI-TEN TRADING SDN BHD <span
                            style="font-size: 12px;">[200501027542
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
                            <td style="font-size: 12px; font-weight: 700;">Invoice No.</td>
                            <td style="font-size: 12px; font-weight: 700; width: 5%; vertical-align: start;">:</td>
                            <td style="font-size: 12px; font-weight: 700; width: 50%; vertical-align: start;">
                                {{ $sku }}</td>
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
                            <td style="font-size: 12px;">Your D/O No.</td>
                            <td style="font-size: 12px;">:</td>
                            <td style="font-size: 12px;">{{ $do_sku }}</td>
                        </tr>
                        <tr>
                            <td style="font-size: 12px;">Terms</td>
                            <td style="font-size: 12px;">:</td>
                            <td style="font-size: 12px;">
                                {{ $terms == null ? null : ($terms == 'cod' ? 'C.O.D' : $terms . ' Days') }}</td>
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
                {{-- <td style="width: 50%; border-bottom: solid 1px black; padding: 0 0 10px 0; vertical-align: text-top;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="font-size: 12px; font-weight: 700;">No.</td>
                            <td style="font-size: 12px; font-weight: 700; width: 5%;">:</td>
                            <td style="font-size: 12px; font-weight: 700; width: 50%;">{{ $sku }}</td>
                        </tr>
                        <tr>
                            <td style="font-size: 12px;">Date</td>
                            <td style="font-size: 12px;">:</td>
                            <td style="font-size: 12px;">{{ $date }}</td>
                        </tr>
                        <tr>
                            <td style="font-size: 12px;">D/O No.</td>
                            <td style="font-size: 12px;">:</td>
                            <td style="font-size: 12px;">{{ $do_sku }}</td>
                        </tr>
                        <tr>
                            <td style="font-size: 12px;">Terms</td>
                            <td style="font-size: 12px;">:</td>
                            <td style="font-size: 12px;">{{ $terms == 'cod' ? 'C.O.D' : $terms . ' Days' }}</td>
                        </tr>
                    </table>
                </td> --}}
            </tr>
        </table>
        <table
            style="width: 100%; font-family: sans-serif; border-collapse: collapse; padding: 0 0 0 0; border-bottom: solid 1px black;">
            <tr>
                <td colspan="2"
                    style="font-size: 16px; font-weight: 700; width: 65%; padding: 15px 35px 10px 0; text-align: center;">
                    INVOICE</td>
            </tr>
            @if (isset($inv_status) && $inv_status == 1)
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
                <td style="padding: 0 35px 15px 0; vertical-align: top;"></td>
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
                    Stock Code</td>
                <td
                    style="font-size: 12px; border-bottom: solid 1px black; padding: 0 0 5px 0; text-align: left; width: 35%;">
                    Description</td>
                <td
                    style="font-size: 12px; border-bottom: solid 1px black; padding: 0 0 5px 0; text-align: right; width: 5%;">
                    Qty</td>
                <td
                    style="font-size: 12px; border-bottom: solid 1px black; padding: 0 0 5px 0; text-align: right; width: 10%;">
                    U/Price<br>(RM)</td>
                <td
                    style="font-size: 12px; border-bottom: solid 1px black; padding: 0 0 5px 0; text-align: right; width: 10%;">
                    Discount<br>(RM)</td>
                <td
                    style="font-size: 12px; border-bottom: solid 1px black; padding: 0 0 5px 0; text-align: right; width: 10%;">
                    Promotion<br>(RM)</td>
                <td
                    style="font-size: 12px; border-bottom: solid 1px black; padding: 0 0 5px 0; text-align: right; width: 10%;">
                    Total<br>(RM)</td>
            </tr>
            @foreach ($products as $key => $prod)
                <tr>
                    <td
                        style="font-size: 12px; text-align: left; vertical-align: start; padding: {{ $key == 0 ? '0' : '20px' }} 0 0 0;">
                        {{ $key + 1 }}</td>
                    <td
                        style="font-size: 12px; text-align: left; vertical-align: start; padding: {{ $key == 0 ? '0' : '20px' }} 0 0 0;">
                        {{ $prod['stock_code'] }}</td>
                    <td
                        style="font-size: 12px; text-align: left; vertical-align: start; padding: {{ $key == 0 ? '0' : '20px' }} 0 0 0;">
                        {{ $prod['model_name'] }}</td>
                    <td
                        style="font-size: 12px; text-align: right; vertical-align: start; padding: {{ $key == 0 ? '0' : '20px' }} 0 0 0;">
                        {{ $prod['qty'] }}</td>
                    <td
                        style="font-size: 12px; text-align: right; vertical-align: start; padding: {{ $key == 0 ? '0' : '20px' }} 0 0 0;">
                        {{ $prod['unit_price'] }}</td>
                    <td
                        style="font-size: 12px; text-align: right; vertical-align: start; padding: {{ $key == 0 ? '0' : '20px' }} 0 0 0;">
                        {{ $prod['discount'] }}</td>
                    <td
                        style="font-size: 12px; text-align: right; vertical-align: start; padding: {{ $key == 0 ? '0' : '20px' }} 0 0 0;">
                        {{ $prod['promotion'] }}</td>
                    <td
                        style="font-size: 12px; text-align: right; vertical-align: start; padding: {{ $key == 0 ? '0' : '20px' }} 0 0 0;">
                        {{ $prod['total'] }}</td>
                </tr>
                <!-- Product Remark -->
                @if (isset($prod['remark']) && $prod['remark'] != null && $prod['remark'] !== '<p><br></p>')
                    <tr>
                        <td colspan="2"></td>
                        <td style="font-size: 10px; text-align: left; font-weight: 700;">
                            Remark:
                        </td>
                        <td style="font-size: 10px; text-align: left;" colspan="5"></td>
                    </tr>
                    <tr>
                        <td colspan="2"></td>
                        <td style="font-size: 10px; text-align: left;">
                            {!! nl2br($prod['remark']) !!}
                        </td>
                        <td style="font-size: 10px; text-align: left;" colspan="5"></td>
                    </tr>
                @endif
                <!-- Warranty -->
                @if ($prod['warranty_periods'] != null)
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
                        <td colspan="5"></td>
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
                            <td colspan="5"></td>
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
                        <td colspan="5"></td>
                    </tr>
                @endif
                <!-- Accessories -->
                @if (isset($prod['accessories']) && count($prod['accessories']) > 0)
                    <tr>
                        <td style="font-size: 10px; text-align: left;" colspan="2"></td>
                        <td style="font-size: 10px; text-align: left; font-weight: 700;">
                            Accessories:
                        </td>
                        <td colspan="5"></td>
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
                            <td colspan="5"></td>
                        </tr>
                    @endforeach
                @endif
                @if ($key + 1 == count($products))
                    <tr>
                        <td style="padding: 5px;"></td>
                    </tr>
                @endif
            @endforeach
            <!-- Remark -->
            @foreach ($sale_orders as $key => $so)
                @if ($so->remark != null)
                    <tr>
                        <td colspan="2"></td>
                        <td colspan="5" style="font-size: 12px; padding: 15px 0;"><span
                                style="font-weight: 700;">REMARK:</span><br>{!! $so->remark !!}</td>
                        <td></td>
                    </tr>
                @endif
            @endforeach
        </table>
        <!-- Item Summary -->
        <table style="width: 100%; font-family: sans-serif; border-collapse: collapse;">
            <tr>
                <td
                    style="font-size: 12px; padding: 10px 0 0 0; border-top: solid 1px black; text-transform: uppercase;">
                    {{ priceToWord(number_format($overall_total, 2)) }}</td>
                <td
                    style="font-size: 12px; font-weight: 700; text-align: right; padding: 10px 0 0 0; border-top: solid 1px black; width: 15%; vertical-align: text-top;">
                    Total {{ number_format($overall_total, 2) }}</td>
            </tr>
        </table>
    </main>

    <footer>
        <table style="width: 100%; font-family: sans-serif; border-collapse: collapse; padding: 0 0 25px 0;">
            <tr>
                <td style="font-size: 12px; padding: 25px 0 0 0;" colspan="2">E.& O.E.</td>
            </tr>
            <tr>
                <td style="font-size: 12px; padding: 0px 0 0 0;" colspan="2">Note:</td>
            </tr>
            <tr>
                <td style="font-size: 12px; vertical-align: start;">1.</td>
                <td style="font-size: 12px; width: 100%; text-align: left;">Cheque should make payable to <span
                        style="font-weight: 700;">HI-TEN TRADING SDN BHD</span></td>
            </tr>
            <tr>
                <td style="font-size: 12px; vertical-align: start;">2.</td>
                <td style="font-size: 12px; width: 100%; text-align: left;">Please remit your payment to : <span
                        style="font-weight: 700;">PUBLIC BANK Account No.: 3983 23 3530 CIMB Account No.: 8603 16
                        3872</span></td>
            </tr>
            <tr>
                <td style="font-size: 12px; vertical-align: start;">3.</td>
                <td style="font-size: 12px; width: 100%; text-align: left;">The Company reserves the right to charge
                    interest 1.5% per month on overdue accounts.</td>
            </tr>
            <tr>
                <td style="font-size: 12px; vertical-align: start;">4.</td>
                <td style="font-size: 12px; width: 100%; text-align: left;">Goods sold and deposit are not returnable &
                    refundable. A cancellation fee of 20% on purchase
                    price will be imposed. If cancel order with credit card payment, will have 10% credit card
                    charges.</td>
            </tr>
            <tr>
                <td style="font-size: 12px; vertical-align: start;">5.</td>
                <td style="font-size: 12px; width: 100%; text-align: left;">Any queries or complaints regarding this
                    invoice must be made within 7 days from date hereof,
                    otherwise any discrepancy will not be entertained.</td>
            </tr>
            <tr>
                <td style="font-size: 12px; vertical-align: start;">6.</td>
                <td style="font-size: 12px; width: 100%; text-align: left;">For more information about our policies,
                    please visit our website at:
                    <a href="https://imaxrefrigerator.com.my/warranty-policy"
                        style="font-weight: 700;">https://imaxrefrigerator.com.my/warranty-policy</a><br>
                    · 3 /5 Years Compressor Warranty with T&C apply.</span><br>
                    . 6 months General Service conduct by IMAX</span><br>
                    · Limited to 1 time change only</span><br>
                </td>
            </tr>
            <tr>
                <td style="font-size: 12px; vertical-align: start;">7.</td>
                <td style="font-size: 12px; width: 100%; text-align: left;">Customer collect\ unloading\ handling by
                    own, Warranty will be bear by customer.</td>
            </tr>
            <tr>
                <td style="font-size: 12px; vertical-align: start;">8.</td>
                <td style="font-size: 12px; width: 100%; text-align: left;">Wear and tear not included in Warranty
                    Claim.</td>
            </tr>
            <tr>
                <td style="font-size: 12px; vertical-align: start;">9.</td>
                <td style="font-size: 12px; width: 100%; text-align: left;">
                    The warranty will be considered void if the refrigerator has been modified or its original
                    specifications
                    altered without prior authorization from the manufacturer.
                </td>
            </tr>
            <tr>
                <td style="font-size: 12px; vertical-align: start;">10.</td>
                <td style="font-size: 12px; width: 100%; text-align: left;">Request E-invoice after 72hrs which
                    original invoice have validated by IRB will be charge 5% of
                    the total invoice amount.</td>
            </tr>
            <tr>
                <td style="font-size: 12px; vertical-align: start;">11.</td>
                <td style="font-size: 12px; width: 100%; text-align: left;">Company will not obligation on those
                    customers are not require to issue E-invoice.</td>
            </tr>
            <tr>
                <td style="font-size: 12px; padding: 20px 0 0 0;" colspan="2">This is a computer generated
                    documents no signature required except for the acknowledgement signature by customer.</td>
            </tr>
        </table>
        {{-- <table style="width: 100%; font-family: sans-serif; border-collapse: collapse;">
            <tr>
                <td style="font-size: 12px; padding: 0 0 50px 0; text-align: center; font-weight: 700;">HI-TEN TRADING
                    SDN
                    BHD</td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td
                    style="font-size: 12px; text-align: center; width: 33%; border-top: solid 1px black; padding: 10px 0 0 0; font-weight: 700;">
                    Authorised Signature</td>
                <td></td>
                <td></td>
            </tr>
        </table> --}}
    </footer>

</body>

</html>
