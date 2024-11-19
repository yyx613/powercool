<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body style="font-family: sans-serif;">

    <p style="font-size: 14px;">General Service has done on {{ $dates }} by {{ $technicians }}</p>
   
    @foreach($records as $record)
        <p style="font-size: 14px;">{{ $record['task_name'] }}</p>
    @endforeach

    <table style="border: solid 1px grey; border-collapse: collapse; width: 100%;">
        <tr>
            <th style="text-align: left; font-size: 14px; border: solid 1px grey; padding: 5px; text-align: center; width: 33.33%;">Equipment</th>
            <th style="text-align: left; font-size: 14px; border: solid 1px grey; padding: 5px; text-align: center; width: 33.33%;">Before</th>
            <th style="text-align: left; font-size: 14px; border: solid 1px grey; padding: 5px; text-align: center; width: 33%.33;">After</th>
        </tr>
        @foreach($records as $record)
        <tr>
            <td style="font-size: 14px; border: solid 1px grey; padding: 5px;">
                <img src="{{ $record['equipment_img'] }}" alt="">
            </td>
            <td style="font-size: 14px; border: solid 1px grey; padding: 5px;">
                <img src="{{ $record['before_img'] }}" alt="">
            </td>
            <td style="font-size: 14px; border: solid 1px grey; padding: 5px;">
                <img src="{{ $record['after_img'] }}" alt="">
            </td>
        </tr>
        @endforeach
    </table>
    
</body>
</html>