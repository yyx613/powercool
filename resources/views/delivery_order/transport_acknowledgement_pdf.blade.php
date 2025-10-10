<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>
</head>
<style>
    .footer {
        position: fixed;
        bottom: 0px;
        height: 200px;
    }
</style>

<body>
    <!-- Title -->
    <h1 style="font-size: 22px; text-transform: uppercase;">Transport Acknowledgement</h1>
    <!-- IV/DO -->
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="width: 70%;"></td>
            <td>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td colspan='2'
                            style="text-transform: uppercase; font-size: 14px; font-weight: 500; text-align: center;">
                            {{ $is_delivery ? 'Delivery Note' : 'Collection' }}</td>
                    </tr>
                    <tr>
                        <td style="font-size: 14px; border: solid 1px black; width: 30%; padding: 0 5px;">ID</td>
                        <td style="font-size: 14px; border: solid 1px black; text-align: center; padding: 0 5px;">
                            {{ $sku }}</td>
                    </tr>
                    <tr>
                        <td style="font-size: 14px; border: solid 1px black; width: 30%; padding: 0 5px;">Date</td>
                        <td style="font-size: 14px; border: solid 1px black; text-align: center; padding: 0 5px;">
                            {{ $date }}</td>
                    </tr>
                    <tr>
                        <td style="font-size: 14px; border: solid 1px black; padding: 0 5px;">IV/DO</td>
                        <td style="font-size: 14px; border: solid 1px black; text-align: center; padding: 0 5px;">
                            {{ $do_sku }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <!-- Delivery Address -->
    <table style="width: 100%; border-collapse: collapse; margin: 20px 0 20px 0;">
        <tr>
            <td style="width: 70%;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td
                            style="font-size: 16px; text-transform: uppercase; border: solid 1px black; padding: 5px; font-family: sans-serif; font-weight: 700;">
                            {{ $is_delivery ? 'Delivery' : 'Collection' }} Address</td>
                    </tr>
                    <tr>
                        <td style="font-size: 14px; border: solid 1px black; padding: 5px; font-family: sans-serif;">
                            {{ $is_delivery ? 'Delivery To' : 'Collection From' }}: <br>
                            {{ $address->name }}<br>
                            {{ $address->mobile }}<br>
                            {!! $address->address !!}
                        </td>
                    </tr>
                </table>
            </td>
            <td></td>
        </tr>
    </table>
    <!-- Item -->
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td
                style="font-size: 14px; border: solid 1px black; border-bottom: solid 1px black; padding: 5px; text-align: center; padding: 0 10px; width: 10%; font-family: sans-serif; font-weight: 700;">
                Item</td>
            <td
                style="font-size: 14px; border: solid 1px black; border-bottom: solid 1px black; text-align: left; padding: 5px 10px; font-family: sans-serif; font-weight: 700;">
                Description</td>
            <td
                style="font-size: 14px; border: solid 1px black; border-bottom: solid 1px black; text-align: center; width: 10%; padding: 5px 10px; font-family: sans-serif; font-weight: 700;">
                Quantity</td>
        </tr>
        @foreach ($pcs as $key => $pc)
            <tr>
                <td
                    style="font-size: 14px; padding: 5px; text-align: center; padding: 0 10px; width: 10%; font-family: sans-serif;">
                    {{ $key + 1 }}</td>
                <td style="font-size: 14px; text-align: left; padding: 5px 10px; font-family: sans-serif;">
                    {{ $pc->sku }}</td>
                <td
                    style="font-size: 14px; text-align: center; width: 10%; padding: 5px 10px; font-family: sans-serif;">
                    1</td>
            </tr>
        @endforeach
    </table>
    <!-- behalf -->
    <table style="width: 100%; border-collapse: collapse; margin: 20px 0 0 0;" class="footer">
        <tr>
            <td colspan="2"
                style="font-size: 16px; text-transform: uppercase; padding: 5px; font-family: sans-serif; font-weight: 700;">
                {{ $is_delivery ? 'Delivery' : 'Collection' }} behalf of {{ $dealer_name }}</td>
        </tr>
        <tr>
            <td
                style="font-size: 12px; width: 70%; border: solid 1px black; text-align: center; font-family: sans-serif;">
                DRIVER'S PARTICULAR</td>
            <td style="font-size: 12px; border: solid 1px black; text-align: center; font-family: sans-serif;">
                ACKNOWLEDGEMENT</td>
        </tr>
        <tr>
            <td style="border-left: solid 1px black;"></td>
            <td
                style="font-family: sans-serif; font-size: 12px; padding: 5px 10px 75px 10px; border-right: solid 1px black; border-left: solid 1px black; text-align: center;">
                GOOD RECEIVED AND GOOD ORDER & CONDITION</td>
        </tr>
        <tr>
            <td style="border-left: solid 1px black;"></td>
            <td
                style="font-family: sans-serif; font-size: 12px; border-top: solid 1px black; border-right: solid 1px black; border-left: solid 1px black; padding: 0 5px 15px 5px; text-align: center;">
                Signature / Comp Stamp</td>
        </tr>
        <tr>
            <td style="border-left: solid 1px black;"></td>
            <td
                style="font-family: sans-serif; font-size: 12px; padding: 0 5px; border-right: solid 1px black; border-left: solid 1px black;">
                Name & IC:</td>
        </tr>
        <tr>
            <td style="border-left: solid 1px black; border-bottom: solid 1px black;"></td>
            <td
                style="border-bottom: solid 1px black; border-left: solid 1px black; font-family: sans-serif; font-size: 12px; padding: 0 5px; border-right: solid 1px black;">
                Date:</td>
        </tr>
    </table>


</body>

</html>
