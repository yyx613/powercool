<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Card Finished Good</title>
    <style>
        body { font-family: sans-serif; font-size: 9px; }
        .meta { width: 100%; }
        .meta td { vertical-align: top; }
        .meta .right { text-align: right; font-size: 9px; }
        h1.title { text-align: center; font-size: 18px; margin: 8px 0 4px 0; }
        .company-line { text-align: left; font-size: 11px; font-weight: 700; }
        .col-head { border-top: solid 1px #000; border-bottom: solid 1px #000; padding: 4px 0; }
        .col-head th { text-align: left; font-size: 9px; font-weight: 700; padding: 2px 4px; }
        .col-head th.num { text-align: right; }
        .item-row td { background: #f3f3f3; font-weight: 700; padding: 4px; border-top: solid 1px #999; }
        .loc-row td { padding: 4px; font-weight: 700; }
        .mv-row td { padding: 2px 4px; font-size: 9px; }
        .totals-row td { padding: 4px; border-top: solid 1px #999; font-weight: 700; }
        .num { text-align: right; }
        .neg { color: #b91c1c; }
        table.body { width: 100%; border-collapse: collapse; table-layout: fixed; }
        table.body td { word-wrap: break-word; overflow-wrap: break-word; }
        .footer-criteria { margin-top: 18px; border-top: solid 1px #000; padding-top: 6px; font-size: 9px; }
    </style>
</head>
<body>

<table class="meta">
    <tr>
        <td>&nbsp;</td>
        <td class="right">
            Date : {{ now()->format('d-m-Y H:i:s') }}<br>
            User ID : {{ optional(auth()->user())->name }}
        </td>
    </tr>
</table>

<h1 class="title">Stock Card Finished Good</h1>

<table class="meta">
    <tr>
        <td class="company-line"><strong>Company:</strong> {{ $company_header }}</td>
        <td class="right">&nbsp;</td>
    </tr>
    @if(!empty($brand_header))
        <tr>
            <td class="company-line"><strong>Brand:</strong> {{ $brand_header }}</td>
            <td class="right">&nbsp;</td>
        </tr>
    @endif
</table>

<table class="body">
    <thead>
        <tr class="col-head">
            <th style="width: 8%;">Location<br>Date</th>
            <th style="width: 12%;">UOM<br>Type Doc. No.</th>
            <th style="width: 25%;">Batch No.<br>Desc.</th>
            <th style="width: 7%;" class="num">In/Out Qty</th>
            <th style="width: 8%;" class="num">B/F Qty<br>Bal Qty</th>
            <th style="width: 10%;" class="num">Unit Cost</th>
            <th style="width: 14%;" class="num">Total Cost</th>
            <th style="width: 16%;" class="num">B/F Cost<br>Bal Cost</th>
        </tr>
    </thead>
    <tbody>
        @forelse($items as $item)
            @php $product = $item['product']; @endphp
            <tr class="item-row">
                <td colspan="2"><strong>Item :</strong> {{ $product->sku }}</td>
                <td colspan="4">{{ $product->model_desc }}</td>
                <td colspan="2"><strong>Company :</strong> {{ $item['company_label'] ?? 'Unassigned' }} | <strong>Brand :</strong> {{ $item['brand_label'] ?? 'Unassigned' }}</td>
            </tr>

            @foreach($item['locations'] as $loc)
                <tr class="loc-row">
                    <td>{{ strtoupper($loc['location_label']) }}</td>
                    <td>{{ strtoupper($loc['uom']) }}</td>
                    <td>&nbsp;</td>
                    <td class="num">&nbsp;</td>
                    <td class="num">{{ number_format($loc['bf_qty']) }}</td>
                    <td class="num">&nbsp;</td>
                    <td class="num">&nbsp;</td>
                    <td class="num {{ $loc['bf_cost'] < 0 ? 'neg' : '' }}">{{ number_format($loc['bf_cost'], 4) }}</td>
                </tr>

                @foreach($loc['movements'] as $mv)
                    <tr class="mv-row">
                        <td>{{ $mv['date'] }}</td>
                        <td>{{ $mv['type'] }}&nbsp;&nbsp;{{ $mv['doc_no'] }}</td>
                        <td>{{ $mv['description'] }}</td>
                        <td class="num {{ $mv['in_out_qty'] < 0 ? 'neg' : '' }}">{{ number_format($mv['in_out_qty']) }}</td>
                        <td class="num {{ $mv['bal_qty'] < 0 ? 'neg' : '' }}">{{ number_format($mv['bal_qty']) }}</td>
                        <td class="num">{{ number_format($mv['unit_cost'], 4) }}</td>
                        <td class="num {{ $mv['total_cost'] < 0 ? 'neg' : '' }}">{{ number_format($mv['total_cost'], 4) }}</td>
                        <td class="num {{ $mv['bal_cost'] < 0 ? 'neg' : '' }}">{{ number_format($mv['bal_cost'], 4) }}</td>
                    </tr>
                @endforeach

                <tr class="totals-row">
                    <td colspan="3" class="num">Closing Balance</td>
                    <td class="num">&nbsp;</td>
                    <td class="num {{ $loc['closing_qty'] < 0 ? 'neg' : '' }}">{{ number_format($loc['closing_qty']) }}</td>
                    <td class="num">&nbsp;</td>
                    <td class="num">&nbsp;</td>
                    <td class="num {{ $loc['closing_cost'] < 0 ? 'neg' : '' }}">{{ number_format($loc['closing_cost'], 4) }}</td>
                </tr>
            @endforeach
        @empty
            <tr>
                <td colspan="8" style="text-align: center; padding: 20px;">No stock movements found for the selected period.</td>
            </tr>
        @endforelse
    </tbody>
</table>

<div class="footer-criteria">
    <strong>End of Report</strong><br>
    Report Criteria:<br>
    Filter Options: From Date: {{ $start_date ?? '—' }} To Date: {{ $end_date ?? '—' }}<br>
    Company: {{ $company_group_label ?? 'All' }}<br>
    Brand: {{ $brand_label ?? 'All' }}<br>
    Movement Types: GR (Goods Receipt), DO (Delivery Order), AS (Stock Assembly), ST (Stock Transfer)<br>
    Include Zero Balance: No<br>
    <em>Note: AS/ST cost columns reflect current Product.cost (not historical at time of movement).</em>
</div>

</body>
</html>
