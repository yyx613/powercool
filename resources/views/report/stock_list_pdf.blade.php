@inject('productionMsMaterial', 'App\Models\ProductionMilestoneMaterial')
@inject('product', 'App\Models\Product')

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body style="font-family: sans-serif;">

    <!-- Header -->
    <table style="width: 100%; font-family: sans-serif; border-collapse: collapse;">
        <tr>
            <td style="width: 70%; border-bottom: solid 1px black; padding: 0 0 10px 0;">
                <span style="font-size: 18px; font-weight: 700;">POWER COOL EQUIPMENTS (M) SDN BHD <span style="font-size: 14px; font-weight: 100;">(383045-D)</span></span><br>
                <span style="font-size: 14px;">NO:12,RCI PARK,JALAN KESIDANG 2,</span><br>
                <span style="font-size: 14px;">KAWASAN PERINDUSTRIAN SUNGAI CHOH,</span><br>
                <span style="font-size: 14px;">48200 SERENDAH,SELANGOR.</span><br>
                <span style="font-size: 14px;">Tel: 603-6094 1122 Service Hotline: 012-386 8743</span><br>
                <span style="font-size: 14px;">Email : enquiry@powercool.com.my</span><br>
                <span style="font-size: 14px;">Sales Tax ID No : B16-1809-22000036</span><br>
            </td>
            <td style="width: 30%; border-bottom: solid 1px black; padding: 0 0 10px 0; vertical-align: text-top;">
            </td>
        </tr>
    </table>
    
    <h1 style="font-size: 20px;">Stock Report</h1>
    
    <table style="border: solid 1px grey; border-collapse: collapse; width: 100%;">
        <tr>
            <th style="text-align: left; font-size: 14px; border: solid 1px grey; padding: 5px;">Product Name</th>
            <th style="text-align: left; font-size: 14px; border: solid 1px grey; padding: 5px;">Product Code</th>
            <th style="text-align: left; font-size: 14px; border: solid 1px grey; padding: 5px;">Available Qty</th>
            <th style="text-align: left; font-size: 14px; border: solid 1px grey; padding: 5px;">Reserved Qty</th>
            <th style="text-align: left; font-size: 14px; border: solid 1px grey; padding: 5px;">On Hold Qty</th>
        </tr>
        @foreach($records as $record)
        @php
            $is_raw_material = $record->is_sparepart !== null && $record->is_sparepart == false;

            if ($is_raw_material) {
                $reserved_stock = $productionMsMaterial::where('product_id', $record->id)->where('on_hold', false)->sum('qty');
                $on_hold_stock = $productionMsMaterial::where('product_id', $record->id)->where('on_hold', true)->sum('qty');
                $available_stock = $record->qty - $reserved_stock - $on_hold_stock;
            }
        @endphp
        <tr>
            <td style="font-size: 14px; border: solid 1px grey; padding: 5px;">{{ $record->model_name }}</td>
            <td style="font-size: 14px; border: solid 1px grey; padding: 5px;">{{ $record->sku }}</td>
            <td style="font-size: 14px; border: solid 1px grey; padding: 5px;">{{ $is_raw_material ? $available_stock : $product->warehouseAvailableStock($record->id) }}</td>
            <td style="font-size: 14px; border: solid 1px grey; padding: 5px;">{{ $is_raw_material ? $reserved_stock : $product->warehouseReservedStock($record->id) }}</td>
            <td style="font-size: 14px; border: solid 1px grey; padding: 5px;">{{ $is_raw_material ? $on_hold_stock : $product->warehouseOnHoldStock($record->id) }}</td>
        </tr>
        @endforeach
    </table>
    
</body>
</html>