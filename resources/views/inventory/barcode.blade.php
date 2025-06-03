<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Barcode</title>
    <link rel="icon" href="{{ asset('/favicon.ico') }}">
</head>
<style>
    @page {
        size: 10cm 15cm;
        margin-left: 15px;
        margin-right: 15px;
    }
</style>

<body>
    @for ($i = 0; $i < count($barcode); $i++)
        <table style="width: 100%;">
            <tr>
                <td style="text-align: center; padding: 0 0 20px 0;" colspan="2">
                    <img src="{{ public_path('images/imax.jpg') }}" alt=""
                        style="height: 50px; width: 100px; margin: 0 0 10px 0;">
                    <div style="width: 300px; height: 30px; margin: auto;">{!! $renderer[$i] !!}</div>
                    <p style="margin: 0; font-size: 10px;">{{ $product_name[$i] }}</p>
                    <p style="margin: 0; font-size: 10px;">{{ $product_code[$i] }} [{{ $barcode[$i] }}]</p>
                </td>
            </tr>
            <tr>
                <td style="padding: 0 0 10px 0;">
                    <p style="margin: 0; font-size: 10px;">DIMENSION:</p>
                    <p style="margin: 0; font-size: 10px;">CAPACITY:</p>
                    <p style="margin: 0; font-size: 10px;">WEIGHT:</p>
                    <p style="margin: 0; font-size: 10px;">REFRIGERANT:</p>
                    <p style="margin: 0; font-size: 10px;">POWER INPUT:</p>
                    <p style="margin: 0; font-size: 10px;">VOLTAGE / FREQUENCY:</p>
                    <p style="margin: 0; font-size: 10px;">STANDARD FEATURES:</p>
                </td>
                <td style="padding: 0 0 10px 0;">
                    <p style="margin: 0; font-size: 10px; text-align: right;">{{ $dimension[$i] ?? '' }}</p>
                    <p style="margin: 0; font-size: 10px; text-align: right;">{{ $capacity[$i] ?? '' }}</p>
                    <p style="margin: 0; font-size: 10px; text-align: right;">{{ $weight[$i] ?? '' }} KG</p>
                    <p style="margin: 0; font-size: 10px; text-align: right;">{{ $refrigerant[$i] ?? '' }}</p>
                    <p style="margin: 0; font-size: 10px; text-align: right;">{{ $power_input[$i] ?? '' }}</p>
                    <p style="margin: 0; font-size: 10px; text-align: right;">{{ $voltage_frequency[$i] ?? '' }}</p>
                    <p style="margin: 0; font-size: 10px; text-align: right;">{{ $standard_features[$i] ?? '' }}</p>
                </td>
            </tr>
            <tr>
                <td style="text-align: left;">
                    <img src="{{ public_path('images/Imax Website Warranty Page QR.jpeg') }}" alt=""
                        style="height: 75px; width: 75px; margin: 0 0 5px 0;">
                    <p style="margin: 0; font-size: 10px;">Warranty Policy</p>
                </td>
                <td style="text-align: right;">
                    <img src="{{ public_path('images/Manual Book QR.jpeg') }}" alt=""
                        style="height: 75px; width: 75px; margin: 0 0 5px 0;">
                    <p style="margin: 0; font-size: 10px;">User Manual</p>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <p style="margin: 10px 0 5px 0; font-size: 10px; text-align: left;">
                        <img src="{{ public_path('images/whatsapp.jpg') }}" alt=""
                            style="height: 10px; width: 10px; margin: 0 5px 0 0;">
                        Hotline: 6012-3868250
                    </p>
                    <p style="margin: 0; font-size: 10px; text-align: left;">
                        <img src="{{ public_path('images/wrench-alt.jpg') }}" alt=""
                            style="height: 10px; width: 10px; margin: 0 5px 0 0;">
                        Careline: 6012-3868743
                    </p>
                    <p style="margin: 20px 0 0 0; font-size: 10px; text-align: center;">Made In Malaysia</p>
                    <p style="margin: 5px 0 0 0; font-size: 10px; text-align: center;">
                        Power Cool Equipments (M) Sdn Bhd<br>
                        (Reg no.:199601010696 [383045-D])<br>
                        No. 12, RCI Park, Jalan Kesidang 2, Kawasan Perindustrian Sungai Choh, 48200 Serendah, Selangor.
                    </p>
                </td>
            </tr>
        </table>
        @if ($i != count($barcode) - 1)
            <div style="page-break-before:always">&nbsp;</div>
        @endif
    @endfor
</body>

</html>
