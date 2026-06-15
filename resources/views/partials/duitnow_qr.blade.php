{{-- Shared DuitNow QR (Scan to Pay) block for customer-facing PDFs.
     Params:
       $company : 'powercool' (default) | 'hiten'
       $colspan : number of columns to span in the parent footer table (default 2) --}}
@php
    $isHiten = ($company ?? 'powercool') === 'hiten';
    $qrImage = $isHiten ? 'images/duitnow_qr_hiten.jpeg' : 'images/duitnow_qr_powercool.jpeg';
    $qrColspan = $colspan ?? 2;
    $duitNowQrBase64 = base64_encode(file_get_contents(public_path($qrImage)));
@endphp
<tr>
    <td colspan="{{ $qrColspan }}" style="padding: 12px 0 20px 0;">
        <span style="font-size: 12px; font-weight: 700;">Scan to Pay (DuitNow QR)</span><br>
        <img src="data:image/jpeg;base64,{{ $duitNowQrBase64 }}" alt="DuitNow QR" style="width: 150px; margin-top: 5px;">
    </td>
</tr>
