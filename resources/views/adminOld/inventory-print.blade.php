<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Inventory</title>
    <style>
        @page { size: A4 portrait; margin: 12mm; }
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        body { font-family: 'Calibri', Arial, Helvetica, sans-serif; color: #000; margin: 0; padding: 0; }

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
        th, td { border: 1px solid #000; padding: 4px 6px; font-size: 11px; text-align: center; }

        thead th { background: #808080; color: #fff; font-weight: bold; }
        th.sno, td.sno   { width: 40px; }
        th.name, td.name { text-align: left; }
        td.r { text-align: right; }

        tbody td { height: 18px; }
        tfoot td { font-weight: bold; background: #d9d9d9; }

        thead { display: table-header-group; }
        tr { page-break-inside: avoid; }

        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    @php
        $grandTotal = 0;
    @endphp

    <div class="title">Inventory: {{ config('app.name') }}</div>

    <table>
        <thead>
            <tr>
                <th class="sno">S.No.</th>
                <th class="name">Product</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Weight</th>
                <th>Avg Price</th>
                <th>Total Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse($inventories as $i => $inv)
                @php
                    $total = (float) $inv->weight * (float) $inv->avg_price;
                    $grandTotal += $total;
                @endphp
                <tr>
                    <td class="sno">{{ $i + 1 }}</td>
                    <td class="name">{{ optional($inv->product)->name }}</td>
                    <td class="r">{{ number_format((float)$inv->quantity, 2) }}</td>
                    <td class="r">{{ number_format((float)$inv->price, 2) }}</td>
                    <td class="r">{{ number_format((float)$inv->weight, 2) }}</td>
                    <td class="r">{{ number_format((float)$inv->avg_price, 2) }}</td>
                    <td class="r">{{ number_format($total, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="7">No inventory records.</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td class="sno"></td>
                <td class="name" style="text-align:right">Total</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td class="r">{{ number_format($grandTotal, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    @if(($browserPrint ?? false))
        <div class="no-print" style="margin-top:16px; text-align:center;">
            <button onclick="window.print()" style="padding:8px 18px; font-size:14px; cursor:pointer;">Print / Save as PDF</button>
        </div>
        <script>window.onload = () => setTimeout(() => window.print(), 250);</script>
    @endif
</body>
</html>
