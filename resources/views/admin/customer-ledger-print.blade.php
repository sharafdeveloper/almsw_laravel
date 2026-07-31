<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ config('app.name') }} - Customer Ledger</title>
    <style>
        @page { size: A4 landscape; margin: 12mm; }
        body { font-family: Arial, sans-serif; color: #000; margin: 0; padding: 0; }
        .page { padding: 10px; }
        .header { text-align: center; margin-bottom: 16px; }
        .header h1 { margin: 0; font-size: 30px; letter-spacing: 1px; font-weight: 700; }
        .header p { margin: 6px 0 0; font-size: 13px; }
        .meta { width: 100%; margin: 14px 0; font-size: 12px; }
        .meta div { margin-bottom: 4px; }
        .meta strong { display: inline-block; width: 90px; }
        table { width: 100%; border-collapse: collapse; font-size: 11px; }
        th, td { border: 1px solid #000; padding: 8px 10px; word-wrap: break-word; overflow-wrap: anywhere; }
        th { background: #f3f3f3; text-align: left; }
        td.text-right { text-align: right; }
        .description-cell { word-wrap: break-word; overflow-wrap: anywhere; }
        .balance-cell { display: flex; justify-content: space-between; align-items: center; }
        .balance-amount { flex: 1; text-align: left; }
        .balance-suffix { min-width: 36px; text-align: right; font-weight: 700; }
        .summary { margin-top: 14px; width: 100%; }
        .summary td { border: 1px solid #000; padding: 8px 10px; }
        .summary .label { font-weight: bold; background: #f3f3f3; }
        @media print {
            body { margin: 0; }
        }
    </style>
</head>
<body>
    @php
        $fmtBal = function ($v) {
            $v = (float) $v;
            $amount = 'Rs ' . number_format(abs($v), 2);
            if (abs($v) < 0.005) {
                return $amount;
            }
            return $amount . ' ' . ($v > 0 ? 'Dr' : 'Cr');
        };
    @endphp

    <div class="page">
        <div class="header">
            <h1>{{ config('app.name') }}</h1>
            <p>Customer Ledger Report</p>
        </div>

        <div class="meta">
            <div><strong>Customer:</strong> {{ $customer->name }}</div>
            <div><strong>City:</strong> {{ $customer->city }}</div>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 13%;">Date</th>
                    <th style="width: 17%;">Bill ID</th>
                    <th>Description</th>
                    <th style="width: 12%;" class="text-right">Debit</th>
                    <th style="width: 12%;" class="text-right">Credit</th>
                    <th style="width: 14%;" class="text-right">Balance</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="5"><em>Opening Balance</em></td>
                    <td class="balance-cell"><span class="balance-amount">Rs {{ number_format(abs($opening), 2) }}</span><span class="balance-suffix">{{ $opening > 0 ? 'Dr' : ($opening < 0 ? 'Cr' : '') }}</span></td>
                </tr>
                @foreach($rows as $r)
                    <tr>
                        <td>{{ $r['date'] }}</td>
                        <td>{{ $r['bill_id'] }}</td>
                        <td class="description-cell">{{ $r['description'] }}</td>
                        <td class="text-right">{{ $r['debit'] > 0 ? 'Rs ' . number_format($r['debit'], 2) : '-' }}</td>
                        <td class="text-right">{{ $r['credit'] > 0 ? 'Rs ' . number_format($r['credit'], 2) : '-' }}</td>
                        <td class="balance-cell"><span class="balance-amount">Rs {{ number_format(abs($r['balance']), 2) }}</span><span class="balance-suffix">{{ $r['balance'] > 0 ? 'Dr' : ($r['balance'] < 0 ? 'Cr' : '') }}</span></td>
                    </tr>
                @endforeach
                @if(empty($rows))
                    <tr>
                        <td colspan="6" style="padding: 16px; text-align: center;">No transactions found for this period.</td>
                    </tr>
                @endif
            </tbody>
        </table>

        <table class="summary">
            <tbody>
                <tr>
                    <td class="label" style="width: 88%; text-align: right;">Closing Balance</td>
                    <td class="balance-cell"><span class="balance-amount">Rs {{ number_format(abs($closingBalance), 2) }}</span><span class="balance-suffix">{{ $closingBalance > 0 ? 'Dr' : ($closingBalance < 0 ? 'Cr' : '') }}</span></td>
                </tr>
            </tbody>
        </table>

    </div>
</body>
</html>
