<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Balance Sheet</title>
    <style>
        @page { size: A4 portrait; margin: 12mm; }
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color-adjust: exact !important;
        }
        body { font-family: 'Calibri', Arial, Helvetica, sans-serif; color: #000; margin: 0; padding: 0; }

        .sheet { width: 100%; }

        /* Main title — black bold text on white background */
        .title {
            border: 2px solid #000;
            border-bottom: none;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            color: #000;
            background: #fff;
            padding: 8px 6px;
        }

        table { width: 100%; border-collapse: collapse; border: 2px solid #000; }
        th, td { border: 1px solid #000; padding: 4px 8px; font-size: 12px; text-align: center; }

        /* Column header row — grey background, white bold text */
        thead th {
            background: #808080;
            color: #fff;
            font-weight: bold;
        }
        th.sno, td.sno   { width: 46px; }
        th.name, td.name { width: auto; }
        th.amt, td.amt   { width: 120px; }

        tbody td { height: 19px; }

        tfoot td {
            font-weight: bold;
            background: #d9d9d9;
            color: #000;
        }

        thead { display: table-header-group; }
        tr { page-break-inside: avoid; }

        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="sheet">
        <div class="title">BALANCE SHEET: {{ config('app.name') }}</div>

        <table>
            <thead>
                <tr>
                    <th class="sno">S.No.</th>
                    <th class="name">Name</th>
                    <th class="amt">Debit</th>
                    <th class="amt">Credit</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $i => $r)
                    <tr>
                        <td class="sno">{{ $i + 1 }}</td>
                        <td class="name">{{ $r['name'] }}</td>
                        <td class="amt">{{ $r['debit']  > 0 ? number_format($r['debit'], 2)  : '-' }}</td>
                        <td class="amt">{{ $r['credit'] > 0 ? number_format($r['credit'], 2) : '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4">No customers found.</td></tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td class="sno"></td>
                    <td class="name" style="text-align:right; padding-right:12px;">Total</td>
                    <td class="amt">{{ number_format($totalDebit, 2) }}</td>
                    <td class="amt">{{ number_format($totalCredit, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    @if(($browserPrint ?? false))
        <div class="no-print" style="margin-top:16px; text-align:center;">
            <button onclick="window.print()" style="padding:8px 18px; font-size:14px; cursor:pointer;">Print / Save as PDF</button>
        </div>
        <script>window.onload = () => setTimeout(() => window.print(), 250);</script>
    @endif
</body>
</html>
