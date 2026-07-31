@extends('layouts.admin')

@section('title', $invoice ? 'Edit Sale Invoice' : 'Create Sale Invoice')

@section('content')
    @php
        $existingItems = $invoice ? $invoice->items->map(fn($it) => [
            'product_id' => $it->product_id,
            'rate'       => (float) $it->rate,
            'quantity'   => (float) $it->quantity,
            'weight'     => (float) $it->weight,
            'amount'     => (float) $it->amount,
        ])->toArray() : [];
    @endphp

    <div x-data="saleInvoiceForm({
        invoiceId: {{ $invoice?->id ?? 'null' }},
        customer_id: {{ $invoice?->customer_id ?? 'null' }},
        bill_date: '{{ $invoice ? $invoice->bill_date->toDateString() : now()->toDateString() }}',
        description: @js($invoice?->description ?? ''),
        labour_cost: {{ $invoice?->labour_cost ?? 0 }},
        loading: {{ $invoice?->loading ?? 0 }},
        cash_received: {{ $invoice?->cash_received ?? 0 }},
        existingItems: {{ json_encode($existingItems) }}
    })" class="max-w-7xl mx-auto">

        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white">{{ $invoice ? 'Edit' : 'Create' }} Sale Invoice</h2>
            </div>
            <a href="{{ route('sale-invoice') }}" class="px-3 py-2 bg-gray-100 dark:bg-[#11151c] rounded text-sm"><i class="fa-solid fa-arrow-left mr-1"></i> Back</a>
        </div>

        @if($errors->any())
            <div class="mb-4 px-4 py-3 rounded-lg bg-red-50 text-red-700">
                <ul class="text-sm list-disc pl-5">
                    @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ $invoice ? route('sale-invoice.update', $invoice) : route('sale-invoice.store') }}" @submit="prepareSubmit($event)">
            @csrf
            @if($invoice) @method('PUT') @endif

            <div class="bg-white dark:bg-[#0b0e14] border border-gray-200 dark:border-[#1f2937] rounded-lg p-4 shadow-sm mb-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Customer *</label>
                        <select x-model="customer_id" name="customer_id" required class="w-full px-3 py-2 border rounded bg-white dark:bg-[#081118] border-gray-200 dark:border-[#1a202c]">
                            <option value="">-- Select Customer --</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->city }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Bill Date *</label>
                        <input type="date" x-model="bill_date" name="bill_date" required class="w-full px-3 py-2 border rounded bg-white dark:bg-[#081118] border-gray-200 dark:border-[#1a202c]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Description</label>
                        <input type="text" x-model="description" name="description" placeholder="Any note for this invoice" class="w-full px-3 py-2 border rounded bg-white dark:bg-[#081118] border-gray-200 dark:border-[#1a202c]">
                    </div>
                </div>
            </div>

            <!-- Line items -->
            <div class="bg-white dark:bg-[#0b0e14] border border-gray-200 dark:border-[#1f2937] rounded-lg p-4 shadow-sm mb-4">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-medium">Line Items</h3>
                    <span class="text-xs text-gray-400">Amount = Rate × Weight</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm table-fixed">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-[#0f1220]">
                                <th class="px-2 py-2 text-left" style="width:28%">Product</th>
                                <th class="px-2 py-2 text-left" style="width:13%">Rate</th>
                                <th class="px-2 py-2 text-left" style="width:13%">Quantity</th>
                                <th class="px-2 py-2 text-left" style="width:13%">Weight</th>
                                <th class="px-2 py-2 text-right" style="width:18%">Amount</th>
                                <th class="px-2 py-2" style="width:15%"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(row, i) in items" :key="i">
                                <tr class="border-t border-gray-100 dark:border-[#11151c]">
                                    <td class="px-2 py-2 align-top">
                                        <select :name="`items[${i}][product_id]`" x-model.number="row.product_id" required class="w-full px-2 py-1.5 border rounded bg-white dark:bg-[#081118] border-gray-200 dark:border-[#1a202c]">
                                            <option value="">--</option>
                                            @foreach($products as $p)
                                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-2 py-2 align-top"><input :name="`items[${i}][rate]`"     type="text" inputmode="decimal" pattern="^\d*(\.\d+)?$" required x-model="row.rate"     @input="recalc(i)" class="w-full px-2 py-1.5 border rounded text-left bg-white dark:bg-[#081118] border-gray-200 dark:border-[#1a202c]"></td>
                                    <td class="px-2 py-2 align-top"><input :name="`items[${i}][quantity]`" type="text" inputmode="decimal" pattern="^\d*(\.\d+)?$" required x-model="row.quantity"                    class="w-full px-2 py-1.5 border rounded text-left bg-white dark:bg-[#081118] border-gray-200 dark:border-[#1a202c]"></td>
                                    <td class="px-2 py-2 align-top"><input :name="`items[${i}][weight]`"   type="text" inputmode="decimal" pattern="^\d*(\.\d+)?$" required x-model="row.weight"   @input="recalc(i)" class="w-full px-2 py-1.5 border rounded text-left bg-white dark:bg-[#081118] border-gray-200 dark:border-[#1a202c]"></td>
                                    <td class="px-2 py-2 text-right align-top pt-3" x-text="formatMoney(row.amount)"></td>
                                    <td class="px-2 py-2 text-right align-top">
                                        <button type="button" @click="removeRow(i)" class="text-red-600 hover:text-red-800 px-2 py-1.5"><i class="fa-solid fa-trash"></i></button>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="items.length === 0"><td colspan="6" class="px-3 py-6 text-center text-gray-500">No items added. Click "Add Row" below.</td></tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    <button type="button" @click="addRow()" class="inline-flex items-center px-3 py-1.5 bg-indigo-50 text-indigo-700 rounded text-sm">
                        <i class="fa-solid fa-plus mr-1"></i> Add Row
                    </button>
                </div>
            </div>

            <!-- Footer summary -->
            <div class="bg-white dark:bg-[#0b0e14] border border-gray-200 dark:border-[#1f2937] rounded-lg p-4 shadow-sm">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div></div>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between items-center">
                            <span>Sub Total</span>
                            <span class="font-medium">Rs <span x-text="formatMoney(subTotal)"></span></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span>Labour Cost *</span>
                            <input type="text" inputmode="decimal" pattern="^\d*(\.\d+)?$" required x-model="labour_cost" name="labour_cost" class="w-32 px-2 py-1 border rounded text-right bg-white dark:bg-[#081118] border-gray-200 dark:border-[#1a202c]">
                        </div>
                        <div class="flex justify-between items-center">
                            <span>Loading *</span>
                            <input type="text" inputmode="decimal" pattern="^\d*(\.\d+)?$" required x-model="loading" name="loading" class="w-32 px-2 py-1 border rounded text-right bg-white dark:bg-[#081118] border-gray-200 dark:border-[#1a202c]">
                        </div>
                        <div class="flex justify-between items-center pt-2 border-t font-semibold text-base">
                            <span>Total</span>
                            <span>Rs <span x-text="formatMoney(total)"></span></span>
                        </div>
                        <div class="flex justify-between items-center pt-2">
                            <span>Cash Received *</span>
                            <input type="text" inputmode="decimal" pattern="^\d*(\.\d+)?$" required x-model="cash_received" name="cash_received" class="w-32 px-2 py-1 border rounded text-right bg-white dark:bg-[#081118] border-gray-200 dark:border-[#1a202c]">
                        </div>
                        <p class="text-xs text-gray-500">If cash received &gt; 0, an entry will be created in Cashbook automatically.</p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-2">
                    <a href="{{ route('sale-invoice') }}" class="px-4 py-2 bg-gray-100 dark:bg-[#11151c] rounded">Cancel</a>
                    <button type="submit" class="px-5 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded shadow"><i class="fa-solid fa-save mr-2"></i> Save Invoice</button>
                </div>
            </div>

        </form>
    </div>

    <script>
        function saleInvoiceForm(config) {
            return {
                invoiceId: config.invoiceId,
                customer_id: config.customer_id || '',
                bill_date: config.bill_date,
                description: config.description || '',
                // Blank by default on create; show real values on edit.
                labour_cost: config.invoiceId ? config.labour_cost : '',
                loading: config.invoiceId ? config.loading : '',
                cash_received: config.invoiceId ? config.cash_received : '',
                items: config.existingItems && config.existingItems.length
                    ? config.existingItems.map(r => ({...r}))
                    : [{ product_id: '', rate: '', quantity: '', weight: '', amount: 0 }],
                formatMoney(v) {
                    return Number(v || 0).toLocaleString('en-PK', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                },
                addRow() { this.items.push({ product_id: '', rate: '', quantity: '', weight: '', amount: 0 }); },
                removeRow(i) { this.items.splice(i, 1); },
                recalc(i) {
                    const r = this.items[i];
                    r.amount = Math.round(((Number(r.rate)||0) * (Number(r.weight)||0)) * 100) / 100;
                },
                get subTotal() {
                    return this.items.reduce((sum, r) => sum + (Number(r.amount)||0), 0);
                },
                get total() {
                    return Math.round((this.subTotal + (Number(this.labour_cost)||0) + (Number(this.loading)||0)) * 100) / 100;
                },
                prepareSubmit(e) {
                    // ensure amounts are up to date before submit
                    this.items.forEach((_, i) => this.recalc(i));
                }
            }
        }
    </script>
@endsection
