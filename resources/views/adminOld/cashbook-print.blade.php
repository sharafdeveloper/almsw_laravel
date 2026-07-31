<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Cashbook {{ $from }} - {{ $to }}</title>
    <style>
        @page { size: A4; margin: 12mm; }
        body { font-family: Arial, sans-serif; color:#222; padding: 8px; }
        h1 { margin: 0 0 4px; }
        table { width:100%; border-collapse:collapse; margin-top: 12px; font-size: 12px; }
        th, td { border:1px solid #ccc; padding: 5px 8px; }
        th { background:#f3f4f6; text-align:left; }
        .right { text-align:right; }
        .totals td { font-weight: bold; background: #f9fafb; }
        .summary { margin-top: 14px; }
        @media print { .no-print { display:none; } }
    </style>
</head>
<body>
    <div style="display:flex; justify-content:space-between;">
        <div>
            <h1>{{ config('app.name') }}</h1>
            <div style="color:#666">Cashbook Report</div>
        </div>
        <div style="text-align:right; font-size: 12px">
            <div><strong>Period:</strong> {{ $from }} to {{ $to }}</div>
            <div><strong>Opening Balance:</strong> Rs {{ number_format($opening, 2) }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Description</th>
                <th>Voucher/Ref</th>
                <th class="right">Cash In</th>
                <th class="right">Cash Out</th>
                <th class="right">Balance</th>
            </tr>
        </thead>
        <tbody>
            <tr><td colspan="5"><em>Opening Balance</em></td><td class="right">Rs {{ number_format($opening, 2) }}</td></tr>
            @foreach($entries as $e)
                <tr>
                    <td>{{ $e['date'] }}</td>
                    <td>{{ $e['description'] }}</td>
                    <td>{{ $e['voucher'] }}</td>
                    <td class="right">{{ $e['in']  > 0 ? 'Rs ' . number_format($e['in'], 2) : '' }}</td>
                    <td class="right">{{ $e['out'] > 0 ? 'Rs ' . number_format($e['out'], 2) : '' }}</td>
                    <td class="right">Rs {{ number_format($e['balance'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        @if(count($entries))
            <tfoot class="totals">
                <tr><td colspan="3" class="right">Totals:</td><td class="right">Rs {{ number_format($totals['in'], 2) }}</td><td class="right">Rs {{ number_format($totals['out'], 2) }}</td><td class="right">Rs {{ number_format($totals['closing'], 2) }}</td></tr>
            </tfoot>
        @endif
    </table>

    <div class="summary">
        <strong>Net Movement:</strong> Rs {{ number_format($totals['net'], 2) }}<br>
        <strong>Closing Balance:</strong> Rs {{ number_format($totals['closing'], 2) }}
    </div>

    <div class="no-print" style="margin-top:14px"><button onclick="window.print()">Print</button></div>
    <script>window.onload = ()=>setTimeout(()=>window.print(), 200);</script>
</body>
</html>
