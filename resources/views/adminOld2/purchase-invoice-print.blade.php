<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Purchase {{ $invoice->formattedId() }}</title>
    @php
        $co = config('pos.company');
        $netTotal = (float) $invoice->total_amount;
    @endphp
    <style>
        @page { size: A5 portrait; margin: 8mm; }
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        body { font-family: 'Times New Roman', Georgia, serif; color: #000; margin: 0; padding: 0; font-size: 12px; }

        .company-name { text-align: center; font-size: 30px; font-weight: bold; letter-spacing: 2px; margin: 0; }
        .tagline { text-align: center; font-weight: bold; font-size: 12px; margin: 2px 0 6px; }
        .addr { border: 1px solid #000; border-radius: 16px; text-align: center; padding: 4px 10px; font-size: 11px; margin-bottom: 4px; }
        .contacts { text-align: center; font-weight: bold; font-size: 11px; margin: 4px 0 8px; }

        .meta { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        .meta td { font-weight: bold; font-size: 12px; padding: 1px 0; }
        .meta .right { text-align: right; }

        .memo-bar { border: 1px solid #000; text-align: center; font-weight: bold; font-size: 14px; padding: 4px; margin-bottom: 4px; }

        table.items { width: 100%; border-collapse: collapse; }
        table.items th, table.items td { border: 1px solid #000; padding: 3px 5px; font-size: 11px; }
        table.items th { text-align: center; font-weight: bold; }
        table.items td.c { text-align: center; }
        table.items td.l { text-align: left; }
        table.items td.r { text-align: right; }

        .totals { width: 100%; border-collapse: collapse; margin-top: 2px; }
        .totals td { font-size: 12px; padding: 2px 5px; }
        .totals .label { text-align: right; font-weight: bold; width: 70%; }
        .totals .val { text-align: right; font-weight: bold; border-bottom: 1px solid #000; }

        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="company-name">{{ $co['name'] }}</div>
    <div class="tagline">{{ $co['tagline'] }}</div>
    <div class="addr">{{ $co['address1'] }}</div>
    <div class="addr">{{ $co['address2'] }}</div>
    <div class="contacts">{{ $co['contacts'] }}</div>

    <table class="meta">
        <tr>
            <td>Bill Id: {{ $invoice->id }}</td>
            <td class="right">Date: {{ $invoice->bill_date->toDateString() }}</td>
        </tr>
        <tr>
            <td>M/S : <u>{{ optional($invoice->supplier)->name }}</u></td>
            <td class="right"></td>
        </tr>
    </table>

    <div class="memo-bar">Purchase Memo</div>

    <table class="items">
        <thead>
            <tr>
                <th style="width:34px">S.No</th>
                <th>Products</th>
                <th style="width:40px">Qty</th>
                <th style="width:60px">Weight</th>
                <th style="width:55px">Price</th>
                <th style="width:70px">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $i => $it)
                <tr>
                    <td class="c">{{ $i + 1 }}</td>
                    <td class="l">{{ optional($it->product)->name }}</td>
                    <td class="c">{{ rtrim(rtrim(number_format((float)$it->quantity, 2), '0'), '.') }}</td>
                    <td class="c">{{ rtrim(rtrim(number_format((float)$it->weight, 2), '0'), '.') }}</td>
                    <td class="c">{{ rtrim(rtrim(number_format((float)$it->price, 2), '0'), '.') }}</td>
                    <td class="r">{{ number_format((float)$it->amount, 0) }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="3" class="r" style="border:none; font-weight:bold; padding-right:8px;">Total:</td>
                <td class="c" style="border:none; border-bottom:1px solid #000; font-weight:bold;">{{ rtrim(rtrim(number_format($invoice->items->sum('weight'), 2), '0'), '.') }}</td>
                <td class="c" style="border:none; border-bottom:1px solid #000; font-weight:bold;">
                    {{ rtrim(rtrim(number_format($invoice->items->sum('weight') > 0 ? $netTotal / $invoice->items->sum('weight') : 0, 2), '0'), '.') }}
                </td>
                <td class="r" style="border:none; border-bottom:1px solid #000; font-weight:bold;">{{ number_format($netTotal, 0) }}</td>
            </tr>
        </tbody>
    </table>

    <table class="totals">
        <tr><td class="label">Net Total :</td><td class="val">{{ number_format($netTotal, 0) }}</td></tr>
    </table>

    @if(($browserPrint ?? false))
        <div class="no-print" style="margin-top:14px; text-align:center;">
            <button onclick="window.print()" style="padding:6px 16px; cursor:pointer;">Print / Save as PDF</button>
        </div>
        <script>window.onload = () => setTimeout(() => window.print(), 250);</script>
    @endif
</body>
</html>
