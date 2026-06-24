{{-- Shared Terms & Conditions block for customer-facing PDFs.
     Rendered as one <tr> per clause so the list can flow / break across pages
     instead of jumping as one un-splittable cell (which leaves a big gap).
     Params:
       $company   : 'powercool' (default) | 'hiten'
       $colspan   : number of columns to span in the parent footer table (default 2)
       $topBorder : draw the divider line above the heading (default true); pass false
                    when a preceding section already carries the line --}}
@php
    $isHiten = ($company ?? 'powercool') === 'hiten';
    $companyName = $isHiten ? 'HI-TEN TRADING SDN. BHD.' : 'POWER COOL EQUIPMENTS (M) SDN. BHD.';
    $tncColspan = $colspan ?? 2;
    $tncTopBorder = $topBorder ?? true;
    $rowStyle = 'font-size: 11px; line-height: 1.4; text-align: justify; padding: 0 0 3px 0;';
@endphp
<tr>
    <td colspan="{{ $tncColspan }}" style="font-size: 12px; font-weight: 700; padding: 18px 0 5px 0;{{ $tncTopBorder ? ' border-top: solid 1px black;' : '' }}">
        Terms &amp; Conditions (E. &amp; O.E.)
    </td>
</tr>
<tr>
    <td colspan="{{ $tncColspan }}" style="{{ $rowStyle }}">
        1. All cheques shall be made payable to <span style="font-weight: 700;">{{ $companyName }}</span>.
    </td>
</tr>
<tr>
    <td colspan="{{ $tncColspan }}" style="{{ $rowStyle }}">
        2. Please remit payment to the following bank accounts:<br>
        <span style="font-weight: 700;">Public Bank Berhad – Account No. : {{ $isHiten ? '3983 23 3530' : '3141967510' }}</span>@if ($isHiten)<br><span style="font-weight: 700;">CIMB Bank Berhad – Account No. : 8603 16 3872</span>@endif
    </td>
</tr>
<tr>
    <td colspan="{{ $tncColspan }}" style="{{ $rowStyle }}">
        3. The Company reserves the right to impose late payment interest at the rate of
        <span style="font-weight: 700;">1.5% per month</span> on all overdue accounts until full payment is received.
    </td>
</tr>
<tr>
    <td colspan="{{ $tncColspan }}" style="{{ $rowStyle }}">
        4. All deposits paid are <span style="font-weight: 700;">non-refundable</span>. Goods sold are not returnable or
        refundable. Any order cancellation accepted by the Company may be subject to a cancellation charge of up to
        <span style="font-weight: 700;">20% of the purchase price</span>. Any applicable payment processing fees shall be
        disclosed to the customer prior to payment.
    </td>
</tr>
<tr>
    <td colspan="{{ $tncColspan }}" style="{{ $rowStyle }}">
        5. Customers are advised to inspect the goods upon delivery or collection. Any discrepancy relating to quantity,
        visible damage, defects, or invoicing errors must be reported to the Company within
        <span style="font-weight: 700;">seven (7) days</span> from the date of delivery or invoice issuance, whichever is
        applicable.
    </td>
</tr>
<tr>
    <td colspan="{{ $tncColspan }}" style="{{ $rowStyle }}">
        6. For detailed warranty terms and conditions, please visit:<br>
        https://imaxrefrigerator.com.my/warranty-policy<br>
        Warranty coverage includes:<br>
        <span style="font-weight: 700;">Compressor warranty of 3 years or 5 years</span>, depending on the product model
        and subject to the applicable warranty terms and conditions.<br>
        <span style="font-weight: 700;">Six (6) months &amp; {{ $isHiten ? 'only ' : '' }}One (1) complimentary</span>
        general service inspection by IMAX during the warranty period{{ $isHiten ? ' &' : ',' }} where applicable.
    </td>
</tr>
<tr>
    <td colspan="{{ $tncColspan }}" style="{{ $rowStyle }}">
        7. Where the customer arranges self-collection, transportation, unloading, installation, or handling of the
        product, the Company shall not be liable for any loss or damage arising from such activities. Warranty coverage
        shall not apply to damage resulting from improper transportation, handling, installation, or storage by the
        customer or any third party engaged by the customer.
    </td>
</tr>
<tr>
    <td colspan="{{ $tncColspan }}" style="{{ $rowStyle }}">
        8. The warranty does not cover normal wear and tear, cosmetic deterioration, misuse, negligence, improper
        installation, unauthorized repairs, accidental damage, power surges, environmental factors, or damage caused by
        failure to follow the operating instructions provided by the manufacturer.
    </td>
</tr>
<tr>
    <td colspan="{{ $tncColspan }}" style="{{ $rowStyle }}">
        9. The warranty shall be deemed void if the refrigerator or any component thereof has been modified, altered,
        repaired, dismantled, or tampered with by any person not authorized by the manufacturer, or if the original
        product specifications have been changed without prior written approval from the manufacturer.
    </td>
</tr>
<tr>
    <td colspan="{{ $tncColspan }}" style="{{ $rowStyle }}">
        10. Requests for amendments, corrections, or reissuance of an e-Invoice in 72hrs after the original invoice has
        been validated by the Inland Revenue Board of Malaysia (IRBM/LHDN) may be subject to an
        <span style="font-weight: 700;">administrative fee 5% of the total invoice amount</span>, where permitted by
        applicable laws and regulations.
    </td>
</tr>
<tr>
    <td colspan="{{ $tncColspan }}" style="{{ $rowStyle }}">
        11. The Company shall not be obligated to issue an e-Invoice where the customer is not required to receive one
        under the applicable tax laws and regulations of Malaysia.
    </td>
</tr>
<tr>
    <td colspan="{{ $tncColspan }}" style="{{ $rowStyle }}">
        12. The Company shall not be liable for any delay or failure to perform its obligations due to circumstances
        beyond its reasonable control, including but not limited to natural disasters, fire, flood, pandemic,
        transportation disruptions, government actions, labour disputes, utility interruptions, or supply chain
        shortages.
    </td>
</tr>
