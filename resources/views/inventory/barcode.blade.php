<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Barcode</title>
    <link rel="icon" href="{{ asset('/favicon.ico') }}">
</head>
<style>
    @page {
        size: 21cm 29.7cm;
    }
</style>
<body>
    
    <table>
        @for ($i = 0; $i < count($barcode); $i++)
            <tr>
                <td style="text-align: center; padding: 0 0 20px 0;">
                    <img src="{{ public_path('images/imax.jpg') }}" alt="" style="height: 35px; width: 75px; margin: 0 0 10px 0;">
                    <div style="width: 150px; height: 100px">{!! $renderer[$i] !!}</div>
                    <p style="margin: 0; font-size: 12px;">{{ $product_name[$i] }}</p>
                    <p style="margin: 0; font-size: 12px;">{{ $product_code[$i] }} [{{ $barcode[$i] }}]</p>
                </td>
                <td></td>
            </tr>
        @endfor
    </table>

</body>
</html>