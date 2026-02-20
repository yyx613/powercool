<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sale Order | {{ $sale->sku }}</title>
</head>

<style>
    @page {
        margin: 440px 25px 50px 25px;
    }

    header {
        position: fixed;
        top: -415px;
        left: 0px;
        right: 0px;
    }

    p {
        margin: 0;
        padding: 0;
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
                <td style="width: 33.33%; border-bottom: solid 1px black; padding: 0 0 10px 0; text-align: center; vertical-align: top;">
                    <span style="font-size: 16px; font-weight: 700;">HI-TEN TRADING SDN BHD <span
                            style="font-size: 12px;">[200501027542 (709676-X)]</span></span><br>
                    <span style="font-size: 14px;">NO. 12, RCI PARK, JALAN KESIDANG 2,</span><br>
                    <span style="font-size: 14px;">KAWASAN PERINDUSTRIAN SUNGAI CHOH,</span><br>
                    <span style="font-size: 14px;">48200 SERENDAH, SELANGOR DARUL EHSAN, MALAYSIA.</span><br>
                    <span style="font-size: 14px;">H/P:012-386 8210, 03-6094 1122</span><br>
                    <span style="font-size: 14px;">Service Hotline (HQ-Selangor) : 012-386 8743</span><br>
                    <span style="font-size: 14px;">Email add : <a
                            href="mailto:enquiry@powercool.com.my">enquiry@powercool.com.my</a></span><br>
                    <span style="font-size: 14px;">Website : <a
                            href="imaxrefrigerator.com.my">imaxrefrigerator.com.my</a></span>
                </td>
            </tr>
        </table>
        <table
            style="width: 100%; font-family: sans-serif; border-collapse: collapse; padding: 0 0 0 0; border-bottom: solid 1px black;">
            <tr>
                <td colspan="2"
                    style="font-size: 16px; font-weight: 700; width: 65%; padding: 15px 35px 10px 0; text-align: right;">
                    {{ $is_proforma_invoice ? 'PROFORMA INVOICE' : 'SALES ORDER' }}</td>
                <td style="font-size: 14px; font-weight: 700; width: 35%; padding: 15px 0 10px 0; text-align: center;">
                    No. :
                    {{ $is_proforma_invoice ? str_replace('SO', 'PRO', $sale->sku) : $sale->sku }}</td>
            </tr>
            @if ($sale->status == 3)
                <tr>
                    <td style="font-size: 14px; font-weight: 700; width: 33.33%; padding: 0px 0 10px 0; text-align: center;">
                    </td>
                    <td style="font-size: 14px; font-weight: 700; width: 33.33%; padding: 0px 35px 10px 0; text-align: center;"
                        id="invalid">
                        VOIDED
                    </td>
                    <td style="font-size: 14px; font-weight: 700; width: 33.33%; padding: 0px 0 10px 0; text-align: center;">
                    </td>
                </tr>
            @endif
            <tr>
                <td colspan="2" style="padding: 0 35px 0 0; vertical-align: top;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="font-size: 14px; vertical-align: top;" colspan="2">
                                <span style="font-weight: 700;">{{ $customer->tin_number ?? '' }}</span><br>
                                {{ $customer->company_name }}<br>
                                {{ $billing_address->address1 ?? '' }}<br>
                                {{ $billing_address->address2 ?? '' }}<br>
                                {{ $billing_address->address3 ?? '' }}<br>
                                {{ $billing_address->address4 ?? '' }}<br>
                            </td>
                        </tr>
                        <tr>
                            <td style="font-size: 14px; padding: 5px 5px 0 0; vertical-align: start;">TEL: {{ $customer->phone }}</td>
                            <td style="font-size: 14px; padding: 5px 0 0 0; vertical-align: start;">ATT: {{ strtoupper($customer->prefix ?? '') }} {{ $customer->name ?? '' }}</td>
                        </tr>
                    </table>
                </td>
                <td style="padding: 0 0 25px 0;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="font-size: 14px; width: 40%;">Quotation No.</td>
                            <td style="font-size: 14px; width: 10%;">:</td>
                            <td style="font-size: 14px;">{{ $quo_skus ?? '' }}</td>
                        </tr>
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
                            <td style="font-size: 14px;">Warehouse</td>
                            <td style="font-size: 14px;">:</td>
                            <td style="font-size: 14px;">{{ $sale->warehouse ?? '' }}</td>
                        </tr>
                        <tr>
                            <td style="font-size: 14px;">Store</td>
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
    </header>

    <main>
        <!-- Item -->
        <table style="width: 100%; font-family: sans-serif; border-collapse: collapse;">
            <tr>
                <td
                    style="font-size: 12px; border-bottom: solid 1px black; padding: 0 0 5px 0; text-align: left; width: 5%;">
                    Item</td>
                <td
                    style="font-size: 12px; border-bottom: solid 1px black; padding: 0 0 5px 0; text-align: left; width: 5%;">
                    Item Code</td>
                <td style="font-size: 12px; border-bottom: solid 1px black; padding: 0 0 5px 0; text-align: left;">
                    Description</td>
                <td
                    style="font-size: 12px; border-bottom: solid 1px black; padding: 0 0 5px 0; text-align: center; width: 5%;">
                    Qty</td>
                <td
                    style="font-size: 12px; border-bottom: solid 1px black; padding: 0 0 5px 5px; text-align: center; width: 5%;">
                    FOC Qty</td>
                <td
                    style="font-size: 12px; border-bottom: solid 1px black; padding: 0 0 5px 5px; text-align: center; width: 5%;">
                    UOM</td>
                <td
                    style="font-size: 12px; border-bottom: solid 1px black; padding: 0 0 5px 5px; text-align: right; width: 10%;">
                    U/Price (RM)</td>
                <td
                    style="font-size: 12px; border-bottom: solid 1px black; padding: 0 0 5px 5px; text-align: right; width: 10%;">
                    Discount (RM)</td>
                <td
                    style="font-size: 12px; border-bottom: solid 1px black; padding: 0 0 5px 5px; text-align: right; width: 10%;">
                    Promotion (RM)</td>
                <td
                    style="font-size: 12px; border-bottom: solid 1px black; padding: 0 0 5px 5px; text-align: right; width: 10%;">
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
                        style="vertical-align: start; font-size: 12px; text-align: left; padding: {{ $key == 0 ? '0' : '20px' }} 10px 0 0;">
                        {{ $prod->product->sku }}</td>
                    <td
                        style="vertical-align: start; font-size: 12px; text-align: left; padding: {{ $key == 0 ? '0' : '20px' }} 0 0 0;">
                        {{ $prod->desc }}</td>
                    <td
                        style="vertical-align: start; font-size: 12px; text-align: center; padding: {{ $key == 0 ? '0' : '20px' }} 0 0 0;">
                        {{ $prod->is_foc == true ? '' : $prod->qty }}</td>
                    <td
                        style="vertical-align: start; font-size: 12px; text-align: center; padding: {{ $key == 0 ? '0' : '20px' }} 0 0 5px;">
                        {{ $prod->is_foc == false ? '' : $prod->qty }}
                    </td>
                    <td
                        style="vertical-align: start; font-size: 12px; text-align: right; padding: {{ $key == 0 ? '0' : '20px' }} 0 0 5px;">
                        {{ $prod->uom }}</td>
                    <td
                        style="vertical-align: start; font-size: 12px; text-align: right; padding: {{ $key == 0 ? '0' : '20px' }} 0 0 5px;">
                        {{ number_format($prod->override_selling_price ?? $prod->unit_price, 2) }}</td>
                    <td
                        style="vertical-align: start; font-size: 12px; text-align: right; padding: {{ $key == 0 ? '0' : '20px' }} 0 0 5px;">
                        {{ number_format($prod->discount, 2) }}</td>
                    <td
                        style="vertical-align: start; font-size: 12px; text-align: right; padding: {{ $key == 0 ? '0' : '20px' }} 0 0 5px;">
                        {{ number_format($prod->promotionAmount() ?? 0, 2) }}
                    </td>
                    <td
                        style="vertical-align: start; font-size: 12px; text-align: right; padding: {{ $key == 0 ? '0' : '20px' }} 0 0 5px;">
                        {{ number_format($prod->qty * ($prod->override_selling_price ?? $prod->unit_price) - $prod->discountAmount(), 2) }}
                    </td>
                </tr>
                <!-- Product Remark -->
                @if ($prod->remark != null && $prod->remark !== '<p><br></p>')
                    <tr>
                        <td colspan="2"></td>
                        <td style="font-size: 10px; text-align: left; font-weight: 700;">
                            Remark:
                        </td>
                        <td style="font-size: 10px; text-align: left;" colspan="7"></td>
                    </tr>
                    <tr>
                        <td colspan="2"></td>
                        <td style="font-size: 10px; text-align: left;">
                            {!! nl2br($prod->remark) !!}
                        </td>
                        <td style="font-size: 10px; text-align: left;" colspan="7"></td>
                    </tr>
                @endif
                <!-- Accessories -->
                @if ($prod->accessories != null && count($prod->accessories) > 0)
                    <tr>
                        <td colspan="2"></td>
                        <td style="font-size: 10px; text-align: left; font-weight: 700;">
                            Accessories:
                        </td>
                        <td style="font-size: 10px; text-align: left;" colspan="7"></td>
                    </tr>
                    @foreach ($prod->accessories as $accessory)
                        <tr>
                            <td colspan="2"></td>
                            <td style="font-size: 10px; text-align: left;">
                                - {{ $accessory->product->model_desc ?? 'N/A' }}
                                (Qty: {{ $accessory->qty ?? 1 }})
                                @if($accessory->is_foc)
                                    - FOC
                                @else
                                    @php
                                        $unit_price = $accessory->override_selling_price ?? ($accessory->sellingPrice->price ?? 0);
                                        $acc_qty = $accessory->qty ?? 1;
                                        $total_price = $unit_price * $acc_qty;
                                    @endphp
                                    - RM {{ number_format($unit_price, 2) }}/unit = RM {{ number_format($total_price, 2) }}
                                @endif
                            </td>
                            <td style="font-size: 10px; text-align: left;" colspan="7"></td>
                        </tr>
                    @endforeach
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
                            <td colspan="2"></td>
                            <td style="font-size: 10px; text-align: left; font-weight: 700;">
                                Warranty:
                            </td>
                            <td style="font-size: 10px; text-align: left;" colspan="7"></td>
                        </tr>
                        @foreach ($warranty as $key => $w)
                            <tr>
                                <td colspan="2"></td>
                                <td style="font-size: 10px; text-align: left;">
                                    @if (count($warranty) == $key + 1)
                                        {{ $w }}
                                    @else
                                        {{ $w }}<br>
                                    @endif
                                </td>
                                <td style="font-size: 10px; text-align: left;" colspan="7"></td>
                            </tr>
                        @endforeach
                    @endif
                @endif
                <!-- Serial No -->
                @if ($prod->serial_no != null)
                    <tr>
                        <td colspan="2"></td>
                        <td style="font-size: 10px; text-align: left;"><b>Serial No:</b><br>
                            {{ join(', ', $prod->serial_no) }}</td>
                        <td style="font-size: 10px; text-align: left;" colspan="7"></td>
                    </tr>
                @endif
                @php
                    // Calculate accessory total for this product
                    $accessory_total = 0;
                    foreach ($prod->accessories as $acc) {
                        if (!$acc->is_foc) {
                            $acc_price = $acc->override_selling_price ?? ($acc->sellingPrice->price ?? 0);
                            $accessory_total += $acc_price * ($acc->qty ?? 1);
                        }
                    }
                    $total +=
                        $prod->qty * ($prod->override_selling_price ?? $prod->unit_price) - $prod->discountAmount() + $accessory_total;
                    $total_tax += $prod->sst_amount ?? 0;
                @endphp
            @endforeach
            <!-- Remark -->
            @if ($sale->remark != null)
                <tr>
                    <td colspan="2"></td>
                    <td style="font-size: 10px; padding: 15px 0;"><span
                            style="font-weight: 700;">REMARK:</span><br>{!! nl2br($sale->remark) !!}</td>
                    <td colspan="7"></td>
                </tr>
            @endif
            <!-- Payment Remark -->
            @if ($sale->payment_remark != null)
                <tr>
                    <td colspan="2"></td>
                    <td style="font-size: 10px; padding: 15px 0;"><span
                            style="font-weight: 700;">PAYMENT
                            REMARK:</span><br>{{ $sale->payment_remark }}</td>
                    <td colspan="7"></td>
                </tr>
            @endif
            <!-- Ad-hoc Services -->
            @if(isset($adhocServices) && count($adhocServices) > 0)
                <tr>
                    <td colspan="2"></td>
                    <td style="font-size: 10px; text-align: left; font-weight: 700; padding-top: 15px;">
                        Ad-hoc Services:
                    </td>
                    <td colspan="7"></td>
                </tr>
                @foreach($adhocServices as $service)
                    @php
                        $service_amount = $service->getEffectiveAmount();
                        $service_sst = $service->is_sst ? ($service_amount * $sst_value / 100) : 0;
                    @endphp
                    <tr>
                        <td colspan="2"></td>
                        <td style="font-size: 10px; text-align: left;">
                            - {{ $service->adhocService->name ?? 'N/A' }}
                            @if($service->is_sst)
                                (incl. {{ $sst_value }}% SST)
                            @endif
                            - RM {{ number_format($service_amount, 2) }}
                            @if($service->is_sst)
                                + RM {{ number_format($service_sst, 2) }} SST
                            @endif
                        </td>
                        <td colspan="7"></td>
                    </tr>
                    @php
                        $total += $service_amount;
                        $total_tax += $service_sst;
                    @endphp
                @endforeach
            @endif
        </table>
        <!-- Item Summary -->
        <table style="width: 100%; font-family: sans-serif; border-collapse: collapse; padding: 30px 0 0 0;">
            <tr>
                <td
                    style="font-size: 12px; padding: 10px 0 0 0; border-top: solid 1px black; text-transform: uppercase;">
                    {{ priceToWord(number_format($total, 2)) }}</td>
                <td
                    style="font-size: 12px; font-weight: 700; text-align: right; padding: 10px 0 0 0; border-top: solid 1px black; width: 15%; vertical-align: text-top;">
                    Total {{ number_format($total + $total_tax, 2) }}</td>
            </tr>
        </table>

        <!-- Footer -->
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
                <td style="font-size: 12px; width: 100%; text-align: left;">Prices are subjected to change without
                    prior notice.</td>
            </tr>
            <tr>
                <td style="font-size: 12px; vertical-align: start;">7.</td>
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
                <td style="font-size: 12px; vertical-align: start;">8.</td>
                <td style="font-size: 12px; width: 100%; text-align: left;">Customer collect\ unloading\ handling by
                    own, Warranty will be bear by customer.</td>
            </tr>
            <tr>
                <td style="font-size: 12px; vertical-align: start;">9.</td>
                <td style="font-size: 12px; width: 100%; text-align: left;">Wear and tear not included in Warranty
                    Claim.</td>
            </tr>
            <tr>
                <td style="font-size: 12px; vertical-align: start;">10.</td>
                <td style="font-size: 12px; width: 100%; text-align: left;">
                    The warranty will be considered void if the refrigerator has been modified or its original
                    specifications
                    altered without prior authorization from the manufacturer.
                </td>
            </tr>
            <tr>
                <td style="font-size: 12px; vertical-align: start;">11.</td>
                <td style="font-size: 12px; width: 100%; text-align: left;">Request E-invoice after 72hrs which
                    original invoice have validated by IRB will be charge 5% of
                    the total invoice amount.</td>
            </tr>
            <tr>
                <td style="font-size: 12px; vertical-align: start;">12.</td>
                <td style="font-size: 12px; width: 100%; text-align: left;">Company will not obligation on those
                    customers are not require to issue E-invoice.</td>
            </tr>
        </table>

        <table style="width: 100%; font-family: sans-serif; border-collapse: collapse;">
            <tr>
                <td style="font-size: 12px; padding: 0 0 47px 0; text-align: center; width: 33%; font-weight: 700;">
                    HI-TEN TRADING SDN BHD</td>
                <td style="font-size: 12px; padding: 0 0 40px 0; text-align: center; width: 33%;"></td>
                <td style="font-size: 12px; padding: 0 0 40px 0; text-align: center; width: 33%; font-weight: 700;">
                    KNOWLEDGED BY:
                </td>
            </tr>
            <tr>
                <td style="font-size: 12px; text-align: center; width: 33%;">
                    {{ $sale->saleperson->name ?? '' }}</td>
                <td style="font-size: 12px; text-align: center; width: 33%;"></td>
                <td style="font-size: 12px; text-align: center; width: 33%;">
                </td>
            </tr>
            <tr>
                <td
                    style="font-weight: 700; font-size: 12px; text-align: center; width: 33%; border-top: solid 1px black; padding: 0px 0 0 0;">
                    Authorised Signature</td>
                <td style="font-size: 12px; padding: 0 0 40px 0; text-align: center; width: 33%;"></td>
                <td
                    style="font-weight: 700; font-size: 12px; text-align: center; width: 33%; border-top: solid 1px black; padding: 0px 0 0 0;">
                    {{ $sale->customer->company_name ?? '' }}
                </td>
            </tr>
            <tr>
                <td style="font-size: 12px; padding: 20px 0 0 0;" colspan="3">This is a computer generated
                    documents no signature required except for the acknowledgement signature by customer.</td>
            </tr>
        </table>
    </main>

</body>

</html>
