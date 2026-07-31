<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ config('app.name') }} - Daily Cashbook</title>
    <style>
        @page { size: A4 landscape; margin: 8mm; }
        body { font-family: Arial, sans-serif; color:#222; padding: 0; margin:0; font-size: 12px; line-height: 1.2; }
        .report-container { border: 1px solid #999; padding: 8px; margin: 0; box-sizing: border-box; }
        .header { text-align: center; margin-bottom: 6px; min-height: 40px; }
        .header h1 { margin: 0; font-size: 18px; }
        .header p { margin: 2px 0 0; font-size: 12px; color:#444; }
        .section { display: inline-block; vertical-align: top; width: 49%; }
        .section + .section { margin-left: 1%; }
        table { width: 100%; border-collapse: collapse; margin-top: 4px; font-size: 12px; }
        th, td { border: 1px solid #999; padding: 4px 5px; }
        td { word-wrap: break-word; overflow-wrap: anywhere; white-space: normal; vertical-align: top; }
        td.date-cell { white-space: nowrap; }
        tr { page-break-inside: avoid; }
        th { background: #eee; text-align: left; }
        .right { text-align: right; }
        .summary-box { width: 100%; margin-top: 8px; }
        .summary-box table { width: 100%; border-collapse: collapse; }
        .summary-box td { border: 1px solid #999; padding: 5px 6px; }
        .summary-box .label { text-align: left; font-weight: bold; background: #f2f2f2; }
        .summary-box .value { text-align: right; }
        .print-page { page-break-after: always; margin-bottom: 0; }
        .print-page:last-child { page-break-after: auto; }
        @media print { .no-print { display:none; } body { padding:0; } .report-container { border: none; } }
    </style>
</head>
<body>
    @php
        $reportLabel = $from ?: $to ?: now()->toDateString();
    @endphp

    @foreach($printPages as $pageIndex => $page)
        <div class="report-container print-page">
            @if($pageIndex === 0)
                <div class="header">
                    <h1>{{ config('app.name') }}</h1>
                    <p>DAILY CASHBOOK: {{ $reportLabel }}</p>
                </div>
            @else
                <div class="header" style="min-height: 40px;"></div>
            @endif

            <div class="section">
                <div style="font-weight:bold; margin-bottom: 6px;">Cash In</div>
                <table>
                    <thead>
                        <tr>
                            <th style="width: 18%;">Date</th>
                            <th style="width: 62%;">Description</th>
                            <th class="right" style="width: 22%;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($page['in'] as $entry)
                            <tr>
                                <td class="date-cell">{{ $entry['date'] }}</td>
                                <td>{{ $entry['description'] }}</td>
                                <td class="right">Rs {{ number_format($entry['in'], 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="right">&nbsp;</td></tr>
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
                            <th style="width: 62%;">Description</th>
                            <th class="right" style="width: 22%;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($page['out'] as $entry)
                            <tr>
                                <td class="date-cell">{{ $entry['date'] }}</td>
                                <td>{{ $entry['description'] }}</td>
                                <td class="right">Rs {{ number_format($entry['out'], 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="right">&nbsp;</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($pageIndex === count($printPages) - 1)
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
            @endif
        </div>
    @endforeach

    <div class="no-print" style="margin-top: 20px;"><button onclick="window.print()">Print</button></div>
    <script>window.onload = ()=>setTimeout(()=>window.print(), 200);</script>
</body>
</html>
