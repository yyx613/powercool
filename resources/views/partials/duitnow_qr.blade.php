{{-- Shared DuitNow QR (Scan to Pay) block for customer-facing PDFs.
     Params:
       $company      : 'powercool' (default) | 'hiten'
       $colspan      : number of columns to span in the parent footer table (default 2)
       $validationQr : optional base64 SVG of the e-invoice validation QR; when given,
                       it is rendered beside the DuitNow QR --}}
@php
    $isHiten = ($company ?? 'powercool') === 'hiten';
    $qrImage = $isHiten ? 'images/duitnow_qr_hiten.jpeg' : 'images/duitnow_qr_powercool.jpeg';
    $qrColspan = $colspan ?? 2;
    $duitNowQrBase64 = base64_encode(file_get_contents(public_path($qrImage)));
@endphp
<tr>
    <td colspan="{{ $qrColspan }}" style="padding: 12px 0 20px 0;">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="vertical-align: bottom;">
                    <span style="font-size: 12px; font-weight: 700;">Scan to Pay (DuitNow QR)</span><br>
                    <img src="data:image/jpeg;base64,{{ $duitNowQrBase64 }}" alt="DuitNow QR" style="width: 150px; margin-top: 5px;">
                </td>
                @if (!empty($validationQr))
                    <td style="text-align: right; vertical-align: bottom;">
                        <img src="data:image/svg+xml;base64,{{ $validationQr }}" alt="Validation QR" style="width: 110px;">
                    </td>
                @endif
            </tr>
        </table>
    </td>
</tr>
