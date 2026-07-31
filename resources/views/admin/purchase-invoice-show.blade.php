@extends('layouts.admin')

@section('title', 'Purchase Invoice ' . $invoice->formattedId())

@section('content')
    <div class="max-w-5xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-2xl font-bold">{{ $invoice->formattedId() }}</h2>
                <p class="text-sm text-gray-500">{{ $invoice->bill_date->toDateString() }}</p>
            </div>
            <div class="space-x-2">
                <a href="{{ route('purchase-invoice') }}" class="px-3 py-2 bg-gray-100 dark:bg-[#11151c] rounded text-sm"><i class="fa-solid fa-arrow-left mr-1"></i> Back</a>
                <a href="{{ route('purchase-invoice.edit', $invoice) }}" class="px-3 py-2 bg-amber-50 text-amber-700 rounded text-sm"><i class="fa-solid fa-pen mr-1"></i> Edit</a>
                <a href="{{ route('purchase-invoice.print', $invoice) }}" class="px-3 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded text-sm"><i class="fa-solid fa-print mr-1"></i> Print</a>
            </div>
        </div>

        @if(session('success'))<div class="mb-4 px-4 py-2 rounded-lg bg-green-50 text-green-700">{{ session('success') }}</div>@endif

        <div class="bg-white dark:bg-[#0b0e14] border border-gray-200 dark:border-[#1f2937] rounded-lg p-6 shadow-sm">
            <div class="grid grid-cols-2 mb-4">
                <div>
                    <div class="text-xs text-gray-500">Supplier</div>
                    <div class="font-semibold">{{ optional($invoice->supplier)->name ?? '-' }}</div>
                </div>
                <div class="text-right">
                    <div class="text-xs text-gray-500">Date</div>
                    <div class="font-semibold">{{ $invoice->bill_date->toDateString() }}</div>
                </div>
            </div>

            <table class="min-w-full text-sm border">
                <thead class="bg-gray-50 dark:bg-[#0f1220]">
                    <tr>
                        <th class="px-3 py-2 text-left">Product</th>
                        <th class="px-3 py-2 text-right">Quantity</th>
                        <th class="px-3 py-2 text-right">Price</th>
                        <th class="px-3 py-2 text-right">Weight</th>
                        <th class="px-3 py-2 text-right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->items as $it)
                        <tr class="border-t">
                            <td class="px-3 py-2">{{ optional($it->product)->name }}</td>
                            <td class="px-3 py-2 text-right">{{ number_format((float)$it->quantity, 2) }}</td>
                            <td class="px-3 py-2 text-right">{{ number_format((float)$it->price, 2) }}</td>
                            <td class="px-3 py-2 text-right">{{ number_format((float)$it->weight, 2) }}</td>
                            <td class="px-3 py-2 text-right">Rs {{ number_format((float)$it->amount, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50 dark:bg-[#0f1220] font-bold">
                    <tr><td colspan="4" class="px-3 py-2 text-right">Total Amount:</td><td class="px-3 py-2 text-right">Rs {{ number_format((float)$invoice->total_amount, 2) }}</td></tr>
                </tfoot>
            </table>
        </div>
    </div>
@endsection
