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
        body { font-family: 'Times New Roman', Georgia, serif; color: #000; margin: 0; padding: 0; font-size: 14px; }

        .company-name { text-align: center; font-size: 30px; font-weight: bold; letter-spacing: 2px; margin: 0; }
        .tagline { text-align: center; font-weight: bold; font-size: 14px; margin: 2px 0 6px; }
        .addr { border: 1px solid #000; border-radius: 16px; text-align: center; padding: 4px 10px; font-size: 14px; margin-bottom: 4px; font-weight: bold; }
        .contacts { text-align: center; font-weight: bold; font-size: 14px; margin: 4px 0 8px; }

        .meta { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        .meta td { font-weight: bold; font-size: 14px; padding: 1px 0; }
        .meta .right { text-align: right; }

        .memo-bar { border: 1px solid #000; text-align: center; font-weight: bold; font-size: 14px; padding: 4px; margin-bottom: 4px; }

        table.items { width: 100%; border-collapse: collapse; }
        table.items th, table.items td { border: 1px solid #000; padding: 3px 5px; font-size: 14px; }
        table.items th { text-align: center; font-weight: bold; font-size: 14px;}
        table.items td.c { text-align: center; font-weight: bold; font-size: 14px; }
        table.items td.l { text-align: left;font-weight: bold; font-size: 14px;}
        table.items td.r { text-align: right; font-weight: bold; font-size: 14px;}

        .totals { width: 100%; border-collapse: collapse; margin-top: 2px; }
        .totals td { font-size: 14px; padding: 2px 5px; }
        .totals .label { text-align: right; font-weight: bold; width: 70%; }
        .totals .val { text-align: right; font-weight: bold; border-bottom: 1px solid #000; }

        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
<div class="company-name">{{ $co['name'] }}</div>
<div class="tagline">{{ $co['tagline'] }}</div>
<div class="addr">{{ $co['address1'] }}</div>
<!-- CONTACTS SECTION FIXED FOR PERFECT RIGHT ALIGNMENT --><!-- CONTACTS SECTION: FIXED FOR PERFECT SAME-ROW ALIGNMENT -->
<table style="width: 100%; border-collapse: collapse; margin: 4px 0 8px 0; font-size: 14px; font-weight: bold; font-family: 'Times New Roman', Georgia, serif;">
    <tr>
        <td style="text-align: center; line-height: 18px;">
            <div>(Proprietor) Muhammad Ismail Khan  &nbsp;&nbsp; 0304-8333161 , &nbsp; 0311-2300939</div>
        </td>
    </tr>
    <tr>
        <td style="text-align: center; line-height: 18px; padding-top: 4px;">
            <div>Muhammad Asad Khan &nbsp;&nbsp; 0322-2672878 , &nbsp; 0315-0011804</div>
        </td>
    </tr>
</table>
    <table class="meta">
        <tr>
            <td>Bill Id: almsw#{{ $invoice->id }}</td>
            <td class="right">Date: {{ $invoice->bill_date->toDateString() }}</td>
        </tr>
        <tr>
            <td>Customer Name : <u style="font-size: 15px;">{{ optional($invoice->supplier)->name }}</u></td>
            <td class="right"></td>
        </tr>
    </table>

    <div class="memo-bar">Purchase Memo</div>
      <!-- NAYA: Description Box -->
    <table class="items" style="margin-bottom: 4px;">
        <tr>
            <td style="width: 70px; font-weight: bold; border: 1px solid #000; padding: 4px 6px;">Description:</td>
            <td style="border: 1px solid #000; padding: 4px 6px; max-height: 28px; font-weight: bold; font-size: 14px; ">
                {{ $invoice->description ?? '' }}
            </td>
        </tr>
    </table>

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
            <!-- NAYA -->
                <tr>
                    <td class="c" style="border:none;"></td>
                    <td class="l" style="border:none; border-bottom:1px solid #000; font-weight:bold;">Total:</td>
                    <td class="c" style="border:none; border-bottom:1px solid #000; font-weight:bold;">{{ rtrim(rtrim(number_format($invoice->items->sum('quantity'), 2), '0'), '.') }}</td>
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
