<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ config('app.name') }} - Daily Cashbook</title>
    <style>
        @page { size: A4 landscape; margin: 12mm; }
        body { font-family: Arial, sans-serif; color:#222; padding: 10px; }
        .report-container { border: 1px solid #999; padding: 14px; }
        .header { text-align: center; margin-bottom: 14px; }
        .header h1 { margin: 0; font-size: 22px; }
        .header p { margin: 4px 0 0; font-size: 13px; color:#444; }
        .section { display: inline-block; vertical-align: top; width: 49%; }
        .section + .section { margin-left: 1%; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; font-size: 12px; }
        th, td { border: 1px solid #999; padding: 6px 8px; }
        th { background: #eee; text-align: left; }
        .right { text-align: right; }
        .summary-box { width: 100%; margin-top: 16px; }
        .summary-box table { width: 100%; border-collapse: collapse; }
        .summary-box td { border: 1px solid #999; padding: 6px 8px; }
        .summary-box .label { text-align: left; font-weight: bold; background: #f2f2f2; }
        .summary-box .value { text-align: right; }
        @media print { .no-print { display:none; } }
    </style>
</head>
<body>
    @php
        $cashInEntries = collect($entries)->where('in', '>', 0);
        $cashOutEntries = collect($entries)->where('out', '>', 0);
        $reportLabel = $from ?: $to ?: now()->toDateString();
    @endphp

    <div class="report-container">
        <div class="header">
            <h1>{{ config('app.name') }}</h1>
            <p>DAILY CASHBOOK: {{ $reportLabel }}</p>
        </div>

        <div class="section">
        <div style="font-weight:bold; margin-bottom: 6px;">Cash In</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 18%;">Date</th>
                    <th>Description</th>
                    <th class="right" style="width: 22%;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($cashInEntries as $entry)
                    <tr>
                        <td>{{ $entry['date'] }}</td>
                        <td>{{ $entry['description'] }}</td>
                        <td class="right">Rs {{ number_format($entry['in'], 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="right">No Cash In records</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="section">
        <div style="font-weight:bold; margin-bottom: 6px;">Cash Out</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 18%;">Date</th>
                    <th>Description</th>
                    <th class="right" style="width: 22%;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($cashOutEntries as $entry)
                    <tr>
                        <td>{{ $entry['date'] }}</td>
                        <td>{{ $entry['description'] }}</td>
                        <td class="right">Rs {{ number_format($entry['out'], 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="right">No Cash Out records</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>

        <div class="summary-box">
            <table>
                <tbody>
                    <tr>
                        <td class="label">Opening Balance</td>
                        <td class="value">Rs {{ number_format($opening, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="label">Total Cash In</td>
                        <td class="value">Rs {{ number_format($totals['in'], 2) }}</td>
                    </tr>
                    <tr>
                        <td class="label">Total Cash Out</td>
                        <td class="value">Rs {{ number_format($totals['out'], 2) }}</td>
                    </tr>
                    <tr>
                        <td class="label">Closing Balance</td>
                        <td class="value">Rs {{ number_format($totals['closing'], 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="no-print" style="margin-top: 20px;"><button onclick="window.print()">Print</button></div>
    <script>window.onload = ()=>setTimeout(()=>window.print(), 200);</script>
</body>
</html>
