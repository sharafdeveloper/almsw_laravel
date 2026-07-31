@extends('layouts.admin')

@section('title', 'Sale Invoices')

@section('content')
    <div class="max-w-7xl mx-auto" x-data="{ showDelete: false, deletingId: null, deletingLabel: '' }">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Sale Invoices</h2>
                <p class="text-sm text-gray-500">List of all outgoing sales</p>
            </div>
            <a href="{{ route('sale-invoice.create') }}" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg shadow">
                <i class="fa-solid fa-plus mr-2"></i> Create Sale Invoice
            </a>
        </div>

        @if(session('success'))
            <div class="mb-4 px-4 py-2 rounded-lg bg-green-50 text-green-700">{{ session('success') }}</div>
        @endif

        <div class="bg-white dark:bg-[#0b0e14] border border-gray-200 dark:border-[#1f2937] rounded-lg p-4 shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-[#222b38]">
                    <thead class="bg-gray-50 dark:bg-[#0f1220]">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Invoice #</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bill Date</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Bill Amount</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Cash Received</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-[#0b0e14] divide-y divide-gray-100 dark:divide-[#11151c]">
                        @forelse($invoices as $inv)
                            <tr>
                                <td class="px-4 py-3 text-sm font-mono">{{ $inv->formattedId() }}</td>
                                <td class="px-4 py-3 text-sm">{{ optional($inv->customer)->name }}</td>
                                <td class="px-4 py-3 text-sm">{{ $inv->bill_date->toDateString() }}</td>
                                <td class="px-4 py-3 text-sm text-right">Rs {{ number_format((float)$inv->total, 2) }}</td>
                                <td class="px-4 py-3 text-sm text-right">Rs {{ number_format((float)$inv->cash_received, 2) }}</td>
                                <td class="px-4 py-3 text-sm text-right space-x-1 whitespace-nowrap">
                                    <a href="{{ route('sale-invoice.show', $inv) }}" class="inline-flex px-2 py-1 bg-blue-50 text-blue-700 rounded text-xs"><i class="fa-solid fa-eye mr-1"></i> View</a>
                                    <a href="{{ route('sale-invoice.print', $inv) }}" class="inline-flex px-2 py-1 bg-gray-100 dark:bg-[#11151c] rounded text-xs"><i class="fa-solid fa-print mr-1"></i> Print</a>
                                    <a href="{{ route('sale-invoice.edit', $inv) }}" class="inline-flex px-2 py-1 bg-amber-50 text-amber-700 rounded text-xs"><i class="fa-solid fa-pen mr-1"></i> Edit</a>
                                    <button @click="deletingId={{ $inv->id }}; deletingLabel='{{ $inv->formattedId() }}'; showDelete=true" class="inline-flex px-2 py-1 bg-red-50 text-red-700 rounded text-xs"><i class="fa-solid fa-trash mr-1"></i> Delete</button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-6 text-center text-sm text-gray-500">No invoices yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-4">{{ $invoices->links() }}</div>

        <div x-show="showDelete" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/40" @click="showDelete=false"></div>
            <div class="bg-white dark:bg-[#0b0e14] rounded-lg shadow-xl w-full max-w-md z-50 overflow-hidden">
                <div class="px-6 py-4 border-b"><h3 class="font-medium">Delete Invoice</h3></div>
                <div class="px-6 py-4 text-sm">Delete invoice <span x-text="deletingLabel" class="font-mono"></span>? Inventory will be reversed.</div>
                <div class="px-6 py-4 border-t flex justify-end space-x-2">
                    <button @click="showDelete=false" class="px-4 py-2 bg-gray-100 dark:bg-[#11151c] rounded">Cancel</button>
                    <form :action="'/sale-invoice/' + deletingId" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded">Yes, Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
