{{-- Reason for return checklist, rendered as footer-table rows directly above the
     Terms & Conditions on CREDIT NOTE PDFs only. The boxes are blank for staff to
     tick by hand and the "Other" line is left empty for a free-text reason.
     Params:
       $colspan : number of columns to span in the parent footer table (default 2) --}}
@php
    $rrColspan = $colspan ?? 2;
@endphp
<tr>
    <td colspan="{{ $rrColspan }}" style="font-size: 14px; font-weight: 700; padding: 18px 0 8px 0; border-top: solid 1px black;">Reason for Return:</td>
</tr>
<tr>
    <td colspan="{{ $rrColspan }}" style="padding: 0 0 6px 0;">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="font-size: 14px; padding: 0 0 6px 0; white-space: nowrap;">
                    <span style="display: inline-block; width: 12px; height: 12px; border: solid 1px black; vertical-align: middle;"></span>
                    <span style="vertical-align: middle; padding: 0 0 0 4px;">Wrong Issue Debtor</span>
                </td>
                <td style="font-size: 14px; padding: 0 0 6px 0; white-space: nowrap;">
                    <span style="display: inline-block; width: 12px; height: 12px; border: solid 1px black; vertical-align: middle;"></span>
                    <span style="vertical-align: middle; padding: 0 0 0 4px;">Double Issue</span>
                </td>
                <td style="font-size: 14px; padding: 0 0 6px 0; white-space: nowrap;">
                    <span style="display: inline-block; width: 12px; height: 12px; border: solid 1px black; vertical-align: middle;"></span>
                    <span style="vertical-align: middle; padding: 0 0 0 4px;">Customer Return</span>
                </td>
            </tr>
            <tr>
                <td style="font-size: 14px; padding: 4px 0 0 0;" colspan="3">
                    <span style="display: inline-block; width: 12px; height: 12px; border: solid 1px black; vertical-align: middle;"></span>
                    <span style="vertical-align: middle; padding: 0 0 0 4px;">Other:</span>
                    <span style="display: inline-block; border-bottom: solid 1px black; width: 75%; vertical-align: middle;">&nbsp;</span>
                </td>
            </tr>
        </table>
    </td>
</tr>
