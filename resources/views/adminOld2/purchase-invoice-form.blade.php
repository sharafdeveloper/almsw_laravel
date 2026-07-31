@extends('layouts.admin')

@section('title', $invoice ? 'Edit Purchase Invoice' : 'Create Purchase Invoice')

@section('content')
    @php
        $existingItems = $invoice ? $invoice->items->map(fn($it) => [
            'product_id' => $it->product_id,
            'quantity'   => (float) $it->quantity,
            'price'      => (float) $it->price,
            'weight'     => (float) $it->weight,
            'amount'     => (float) $it->amount,
        ])->toArray() : [];
    @endphp

    <div x-data="purchaseInvoiceForm({
        supplier_id: {{ $invoice?->supplier_id ?? 'null' }},
        bill_date: '{{ $invoice ? $invoice->bill_date->toDateString() : now()->toDateString() }}',
        description: @js($invoice?->description ?? ''),
        existingItems: {{ json_encode($existingItems) }}
    })" class="max-w-7xl mx-auto">

        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white">{{ $invoice ? 'Edit' : 'Create' }} Purchase Invoice</h2>
            <a href="{{ route('purchase-invoice') }}" class="px-3 py-2 bg-gray-100 dark:bg-[#11151c] rounded text-sm"><i class="fa-solid fa-arrow-left mr-1"></i> Back</a>
        </div>

        @if($errors->any())
            <div class="mb-4 px-4 py-3 rounded-lg bg-red-50 text-red-700">
                <ul class="text-sm list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="POST" action="{{ $invoice ? route('purchase-invoice.update', $invoice) : route('purchase-invoice.store') }}" @submit="prepareSubmit()">
            @csrf
            @if($invoice) @method('PUT') @endif

            <div class="bg-white dark:bg-[#0b0e14] border border-gray-200 dark:border-[#1f2937] rounded-lg p-4 shadow-sm mb-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div x-data="supplierSearch({
                            initId: {{ $invoice?->supplier_id ?? 'null' }},
                            initName: '{{ $invoice?->supplier ? $invoice->supplier->name . ' (' . $invoice->supplier->city . ')' : '' }}'
                        })">
                        <label class="block text-sm font-medium mb-1">Supplier *</label>
                        <div class="relative">
                            <input
                                type="text"
                                x-model="search"
                                @input.debounce.300ms="fetchSuppliers()"
                                @focus="fetchSuppliers()"
                                @blur="setTimeout(() => open = false, 200)"
                                placeholder="Search supplier..."
                                autocomplete="off"
                                class="w-full px-3 py-2 border rounded bg-white dark:bg-[#081118] border-gray-200 dark:border-[#1a202c]"
                            >
                            <input type="hidden" name="supplier_id" :value="selectedId" required>

                            <ul
                                x-show="open && results.length > 0"
                                x-transition
                                class="absolute z-50 w-full bg-white dark:bg-[#0b0e14] border border-gray-200 dark:border-[#1a202c] rounded shadow-lg max-h-52 overflow-y-auto mt-1"
                            >
                                <template x-for="s in results" :key="s.id">
                                    <li
                                        @mousedown.prevent="selectSupplier(s)"
                                        class="px-3 py-2 cursor-pointer hover:bg-indigo-50 dark:hover:bg-[#11151c] text-sm"
                                        x-text="s.name + ' (' + s.city + ')'"
                                    ></li>
                                </template>
                            </ul>

                            <p x-show="open && results.length === 0 && search.length > 0" class="absolute z-50 w-full bg-white dark:bg-[#0b0e14] border border-gray-200 dark:border-[#1a202c] rounded shadow mt-1 px-3 py-2 text-sm text-gray-400">
                                No supplier found
                            </p>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">Total goes to this supplier's ledger.</p>
                    </div>


                    <div>
                        <label class="block text-sm font-medium mb-1">Bill Date *</label>
                        <input type="date" x-model="bill_date" name="bill_date" required class="w-full px-3 py-2 border rounded bg-white dark:bg-[#081118] border-gray-200 dark:border-[#1a202c]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Description</label>
                        <input type="text" x-model="description" name="description" class="w-full px-3 py-2 border rounded bg-white dark:bg-[#081118] border-gray-200 dark:border-[#1a202c]">
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-[#0b0e14] border border-gray-200 dark:border-[#1f2937] rounded-lg p-4 shadow-sm mb-4">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-medium">Line Items</h3>
                    <span class="text-xs text-gray-400">Amount = Price × Weight</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm table-fixed">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-[#0f1220]">
                                <th class="px-2 py-2 text-left" style="width:28%">Product</th>
                                <th class="px-2 py-2 text-left" style="width:13%">Quantity</th>
                                <th class="px-2 py-2 text-left" style="width:13%">Price</th>
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
                                            @foreach($products as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach
                                        </select>
                                    </td>
                                    <td class="px-2 py-2 align-top"><input :name="`items[${i}][quantity]`" type="text" inputmode="decimal" pattern="^\d*(\.\d+)?$" required x-model="row.quantity" class="w-full px-2 py-1.5 border rounded text-left bg-white dark:bg-[#081118] border-gray-200 dark:border-[#1a202c]"></td>
                                    <td class="px-2 py-2 align-top"><input :name="`items[${i}][price]`"    type="text" inputmode="decimal" pattern="^\d*(\.\d+)?$" required x-model="row.price"    @input="recalc(i)" class="w-full px-2 py-1.5 border rounded text-left bg-white dark:bg-[#081118] border-gray-200 dark:border-[#1a202c]"></td>
                                    <td class="px-2 py-2 align-top"><input :name="`items[${i}][weight]`"   type="text" inputmode="decimal" pattern="^\d*(\.\d+)?$" required x-model="row.weight"   @input="recalc(i)" class="w-full px-2 py-1.5 border rounded text-left bg-white dark:bg-[#081118] border-gray-200 dark:border-[#1a202c]"></td>
                                    <td class="px-2 py-2 text-right align-top pt-3" x-text="formatMoney(row.amount)"></td>
                                    <td class="px-2 py-2 text-right align-top">
                                        <button type="button" @click="removeRow(i)" class="text-red-600 hover:text-red-800 px-2 py-1.5"><i class="fa-solid fa-trash"></i></button>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="items.length === 0"><td colspan="6" class="px-3 py-6 text-center text-gray-500">No items added.</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    <button type="button" @click="addRow()" class="inline-flex items-center px-3 py-1.5 bg-indigo-50 text-indigo-700 rounded text-sm">
                        <i class="fa-solid fa-plus mr-1"></i> Add Row
                    </button>
                </div>
            </div>

            <div class="bg-white dark:bg-[#0b0e14] border border-gray-200 dark:border-[#1f2937] rounded-lg p-4 shadow-sm">
                <div class="flex justify-between items-center text-lg">
                    <span class="font-medium">Total Amount</span>
                    <span class="font-bold text-2xl">Rs <span x-text="formatMoney(total)"></span></span>
                </div>
                <div class="mt-6 flex justify-end space-x-2">
                    <a href="{{ route('purchase-invoice') }}" class="px-4 py-2 bg-gray-100 dark:bg-[#11151c] rounded">Cancel</a>
                    <button type="submit" class="px-5 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded shadow"><i class="fa-solid fa-save mr-2"></i> Save Purchase Invoice</button>
                </div>
            </div>

        </form>
    </div>

    <script>
        function supplierSearch({ initId, initName }) {
    return {
        search: initName || '',
        selectedId: initId || '',
        results: [],
        open: false,
        async fetchSuppliers() {
            if (this.search.length < 1) { this.open = false; return; }
            try {
                const res = await fetch(`/admin/suppliers/search?q=${encodeURIComponent(this.search)}`);
                this.results = await res.json();
                this.open = true;
            } catch(e) { console.error(e); }
        },
        selectSupplier(s) {
            this.selectedId = s.id;
            this.search = s.name + ' (' + s.city + ')';
            this.open = false;
        }
    }
}
        function purchaseInvoiceForm(config) {
            return {
                supplier_id: config.supplier_id || '',
                bill_date: config.bill_date,
                description: config.description || '',
                items: config.existingItems && config.existingItems.length
                    ? config.existingItems.map(r => ({...r}))
                    : [{ product_id: '', quantity: '', price: '', weight: '', amount: 0 }],
                formatMoney(v) { return Number(v||0).toLocaleString('en-PK', {minimumFractionDigits:2, maximumFractionDigits:2}); },
                addRow() { this.items.push({ product_id:'', quantity:'', price:'', weight:'', amount:0 }); },
                removeRow(i) { this.items.splice(i,1); },
                recalc(i) {
                    const r = this.items[i];
                    // Amount = Price * Weight (per client)
                    r.amount = Math.round(((Number(r.price)||0) * (Number(r.weight)||0)) * 100)/100;
                },
                get total() {
                    return Math.round(this.items.reduce((s,r)=>s+(Number(r.amount)||0),0)*100)/100;
                },
                prepareSubmit() { this.items.forEach((_,i)=>this.recalc(i)); }
            }
        }
    </script>
@endsection
