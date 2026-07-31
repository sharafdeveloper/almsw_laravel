@extends('layouts.admin')

@section('title', 'Ledger - ' . $customer->name)
<style>
    input[type="date"].date-input::-webkit-calendar-picker-indicator {
    filter: invert(1);
    opacity: 0.7;
    cursor: pointer;
}
</style>
@section('content')
    @php
        // Show absolute amount + Cr/Dr instead of a negative number.
        // Positive balance = customer owes you (Dr). Negative = you owe customer (Cr).
        $fmtBal = function ($v) {
            $v = (float) $v;
            $amount = 'Rs ' . number_format(abs($v), 2);
            if (abs($v) < 0.005) {
                return $amount;
            }
            $tag = $v > 0 ? 'Dr' : 'Cr';
            return $amount . ' ' . $tag;
        };
    @endphp
    <div class="max-w-7xl mx-auto">
       <!-- NAYA -->
<div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-3 no-print">
    <div>
        <h2 class="text-2xl font-bold text-gray-800 dark:text-white">{{ $customer->name }}</h2>
        <p class="text-sm text-gray-500">{{ $customer->city }}</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <form method="GET" action="{{ route('customers.ledger', $customer) }}" class="flex items-center gap-2">
            <div class="flex flex-col gap-1">
                <label class="text-xs text-gray-400">From</label>
                <input type="date" name="from" value="{{ $from ?? '' }}"
                    class="date-input bg-transparent border border-gray-200 dark:border-[#1f2937] rounded-lg px-3 py-2 text-sm focus:outline-none text-gray-700 dark:text-gray-200 w-36">
            </div>
            <div class="flex flex-col gap-1">
                <label class="text-xs text-gray-400">To</label>
                <input type="date" name="to" value="{{ $to ?? '' }}"
                    class="date-input bg-transparent border border-gray-200 dark:border-[#1f2937] rounded-lg px-3 py-2 text-sm focus:outline-none text-gray-700 dark:text-gray-200 w-36">
            </div>
            <button type="submit" class="inline-flex items-center px-3 py-2 bg-[#7c3aed] text-white rounded-lg text-sm mt-4">
                <i class="fa-solid fa-magnifying-glass mr-1"></i> Search
            </button>
            @if(!empty($from) || !empty($to))
                <a href="{{ route('customers.ledger', $customer) }}" class="inline-flex items-center px-3 py-2 bg-gray-100 dark:bg-[#11151c] rounded-lg text-sm mt-4">
                    <i class="fa-solid fa-xmark mr-1"></i> Clear
                </a>
            @endif
        </form>
        <a href="{{ route('customers.ledger.print', ['customer' => $customer, 'from' => $from, 'to' => $to]) }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded shadow">
            <i class="fa-solid fa-print mr-2"></i> Print / PDF
        </a>
        <a href="{{ route('customers') }}" class="inline-flex items-center px-3 py-2 bg-gray-100 dark:bg-[#11151c] rounded-lg text-sm">
            <i class="fa-solid fa-arrow-left mr-2"></i> Back
        </a>
    </div>
</div>

        <div class="bg-white dark:bg-[#0b0e14] border border-gray-200 dark:border-[#1f2937] rounded-lg p-4 shadow-sm print-area">
            <div class="flex justify-between mb-4">
                <div>
                    <h3 class="text-lg font-bold">{{ config('app.name') }}</h3>
                    <p class="text-sm text-gray-500">Customer Ledger Report</p>
                </div>
                <div class="text-right text-sm">
                    <div><strong>Customer:</strong> {{ $customer->name }}</div>
                    <div><strong>City:</strong> {{ $customer->city }}</div>
                    <div><strong>Opening Balance:</strong> {{ $fmtBal($opening) }}</div>
                </div>
            </div>

            <table class="min-w-full text-sm border border-gray-200 dark:border-[#1f2937]">
                <thead class="bg-gray-50 dark:bg-[#0f1220]">
                    <tr>
                        <th class="px-3 py-2 text-left">Date</th>
                        <th class="px-3 py-2 text-left">Bill ID</th>
                        <th class="px-3 py-2 text-left">Description</th>
                        <th class="px-3 py-2 text-right">Debit</th>
                        <th class="px-3 py-2 text-right">Credit</th>
                        <th class="px-3 py-2 text-right">Balance</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-t border-gray-100 dark:border-[#11151c] bg-gray-50/50 dark:bg-[#0f1220]/30">
                        <td class="px-3 py-2" colspan="5"><em>Opening Balance</em></td>
                        <td class="px-3 py-2 text-right font-medium">{{ $fmtBal($opening) }}</td>
                    </tr>
                    @forelse($rows as $r)
                        <tr class="border-t border-gray-100 dark:border-[#11151c]">
                            <td class="px-3 py-2">{{ $r['date'] }}</td>
                            <td class="px-3 py-2 font-mono text-xs">
                                @if(($r['ref_type'] ?? null) === 'sale')
                                    <a href="{{ route('sale-invoice.show', $r['ref_id']) }}" class="text-indigo-600 hover:underline">{{ $r['bill_id'] }}</a>
                                @elseif(($r['ref_type'] ?? null) === 'purchase')
                                    <a href="{{ route('purchase-invoice.show', $r['ref_id']) }}" class="text-indigo-600 hover:underline">{{ $r['bill_id'] }}</a>
                                @else
                                    {{ $r['bill_id'] }}
                                @endif
                            </td>
                            <td class="px-3 py-2 break-words whitespace-normal">
                                @if(in_array($r['ref_type'] ?? null, ['sale', 'purchase', 'payment']))
                                    {!! preg_replace('/\s*-\s*/', '<br>', $r['description'], 1) !!}
                                @else
                                    {{ $r['description'] }}
                                @endif
                            </td>
                            <td class="px-3 py-2 text-right">{{ $r['debit'] > 0 ? 'Rs ' . number_format($r['debit'], 2) : '-' }}</td>
                            <td class="px-3 py-2 text-right">{{ $r['credit'] > 0 ? 'Rs ' . number_format($r['credit'], 2) : '-' }}</td>
                            <td class="px-3 py-2 text-right font-medium">{{ $fmtBal($r['balance']) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-3 py-6 text-center text-gray-500">No transactions yet.</td></tr>
                    @endforelse
                </tbody>
                @if(!empty($rows))
                    <tfoot class="bg-gray-50 dark:bg-[#0f1220] font-semibold">
                        <tr>
                            <td colspan="5" class="px-3 py-2 text-right">Closing Balance:</td>
                            {{-- <td class="px-3 py-2 text-right">{{ $fmtBal(end($rows)['balance']) }}</td> --}}
                            <!-- NAYA -->
                            <td class="px-3 py-2 text-right">{{ $fmtBal($closingBalance) }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    <style>
        @media print {
            .no-print, aside, header { display: none !important; }
            main { padding: 0 !important; }
            .print-area { border: 0 !important; box-shadow: none !important; }
        }
    </style>
@endsection
