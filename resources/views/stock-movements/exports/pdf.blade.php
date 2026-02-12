<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Stoku - PDF Export</title>
    <style>
        body {
            font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #222
        }

        .header {
            margin-bottom: 12px
        }

        h2 {
            margin: 0 0 6px 0
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 6px 8px;
            vertical-align: top
        }

        th {
            background: #f5f5f5;
            font-weight: 600
        }

        .small {
            font-size: 10px;
            color: #666
        }

        .imei-list {
            white-space: pre-wrap
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>Stoku i Produkteve</h2>
        <div class="small">Data: {{ now()->toDateTimeString() }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:24%">Produkti</th>
                <th style="width:14%">Kategoria</th>
                <th style="width:14%">Marka</th>
                <th style="width:10%">Çmimi</th>
                <th style="width:10%">Totali Stok</th>
                <th style="width:28%">IMEI në stok (pa u shitur)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($products as $product)
            <tr>
                <td>{{ $product->name }}</td>
                <td>{{ $product->category?->name ?? '' }}</td>
                <td>{{ $product->brand?->name ?? '' }}</td>
                <td>{{ number_format($product->unit_price ?? $product->price ?? 0, 2) }}</td>
                <td>{{ $product->warehouses->sum(fn($w)=> isset($w->pivot->quantity)?(int)$w->pivot->quantity:0) }}</td>
                <td class="imei-list">{!! isset($product->unsold_imeis) ? nl2br(e(implode("\n", $product->unsold_imeis))) : '' !!}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>