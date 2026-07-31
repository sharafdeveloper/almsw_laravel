@extends('layouts.admin')

@section('title', 'Inventory')

@section('content')
    <div x-data="inventoryManager()" class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Inventory</h2>
                <p class="text-sm text-gray-500">Inventory updates automatically when you create a Purchase Invoice</p>
            </div>
            <a href="{{ route('inventory.print') }}" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg shadow text-sm">
                <i class="fa-solid fa-print mr-2"></i> Print
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
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Quantity</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Price</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Weight</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Avg Price</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total Amount</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-[#0b0e14] divide-y divide-gray-100 dark:divide-[#11151c]">
                        @forelse($inventories as $inv)
                            @php $totalAmount = (float) $inv->weight * (float) $inv->avg_price; @endphp
                            <tr>
                                <td class="px-4 py-3 text-sm">{{ $inv->id }}</td>
                                <td class="px-4 py-3 text-sm">{{ optional($inv->product)->name }}</td>
                                <td class="px-4 py-3 text-sm text-right">{{ number_format((float)$inv->quantity, 2) }}</td>
                                <td class="px-4 py-3 text-sm text-right">Rs {{ number_format((float)$inv->price, 2) }}</td>
                                <td class="px-4 py-3 text-sm text-right">{{ number_format((float)$inv->weight, 2) }}</td>
                                <td class="px-4 py-3 text-sm text-right text-indigo-600 font-medium">Rs {{ number_format((float)$inv->avg_price, 2) }}</td>
                                <td class="px-4 py-3 text-sm text-right font-semibold">Rs {{ number_format($totalAmount, 2) }}</td>
                                <td class="px-4 py-3 text-sm text-right space-x-2">
                                    <button @click='openEdit(@json($inv))' class="inline-flex items-center px-3 py-1.5 bg-gray-100 dark:bg-[#11151c] rounded">
                                        <i class="fa-solid fa-pen mr-1"></i> Edit
                                    </button>
                                    {{-- Delete button hidden per client request (backend still implemented) --}}
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-4 py-6 text-center text-sm text-gray-500">No inventory yet. Create a Purchase Invoice to populate.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-4">{{ $inventories->links() }}</div>

        <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/40" @click="showModal=false"></div>
            <div class="bg-white dark:bg-[#0b0e14] rounded-lg shadow-xl w-full max-w-lg z-50 overflow-hidden">
                <div class="px-6 py-4 border-b"><h3 class="text-lg font-medium">Edit Inventory</h3></div>
                <form class="px-6 py-6 space-y-4" @submit.prevent="save()">
                    <div>
                        <label class="block text-sm font-medium mb-1">Quantity</label>
                        <input type="text" inputmode="decimal" pattern="^\d*(\.\d+)?$" x-model="form.quantity" class="w-full px-3 py-2 border rounded bg-white dark:bg-[#081118] border-gray-200 dark:border-[#1a202c]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Weight</label>
                        <input type="text" inputmode="decimal" pattern="^\d*(\.\d+)?$" x-model="form.weight" class="w-full px-3 py-2 border rounded bg-white dark:bg-[#081118] border-gray-200 dark:border-[#1a202c]">
                    </div>
                    <div class="flex justify-end space-x-2">
                        <button type="button" @click="showModal=false" class="px-4 py-2 bg-gray-100 dark:bg-[#11151c] rounded">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function inventoryManager() {
            return {
                showModal: false,
                form: { id: null, quantity: 0, weight: 0 },
                openEdit(inv) {
                    this.form = { id: inv.id, quantity: inv.quantity, weight: inv.weight };
                    this.showModal = true;
                },
                async save() {
                    const token = document.querySelector('meta[name=csrf-token]').content;
                    const res = await fetch('/inventory/' + this.form.id, {
                        method: 'PUT',
                        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':token,'Accept':'application/json'},
                        body: JSON.stringify({
                            quantity: Number(this.form.quantity) || 0,
                            weight:   Number(this.form.weight)   || 0,
                        })
                    });
                    if (res.ok) window.location.reload(); else alert('Save failed');
                }
            }
        }
    </script>
@endsection
