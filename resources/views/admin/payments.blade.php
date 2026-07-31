@extends('layouts.admin')

@section('title', 'Payments')

@section('content')
    <div x-data="paymentsManager()" class="max-w-7xl mx-auto">

        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-3">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Payments</h2>
                <p class="text-sm text-gray-500">Cash In &amp; Cash Out transactions (auto-synced to Cashbook)</p>
            </div>
            <button @click="openCreate()" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg shadow">
                <i class="fa-solid fa-plus mr-2"></i> Create Payment
            </button>
        </div>

        @if(session('success'))<div class="mb-4 px-4 py-2 rounded-lg bg-green-50 text-green-700">{{ session('success') }}</div>@endif
        @if(session('error'))  <div class="mb-4 px-4 py-2 rounded-lg bg-red-50  text-red-700">{{ session('error') }}</div>@endif

        <!-- Filters -->
        <form method="GET" action="{{ route('payments') }}" class="bg-white dark:bg-[#0b0e14] border border-gray-200 dark:border-[#1f2937] rounded-lg p-3 mb-4 flex flex-wrap gap-3 items-end">
            {{-- <div>
                <label class="block text-xs text-gray-500 mb-1">Search</label>
                <input type="text" name="q" value="{{ $filters['q'] }}" placeholder="Customer / method / note" class="px-3 py-1.5 border rounded text-sm bg-white dark:bg-[#081118] border-gray-200 dark:border-[#1a202c]">
            </div> --}}
            <div>
                <label class="block text-xs text-gray-500 mb-1">From</label>
                <input type="date" name="from" value="{{ $filters['from'] }}" class="px-3 py-1.5 border rounded text-sm bg-white dark:bg-[#081118] border-gray-200 dark:border-[#1a202c]">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">To</label>
                <input type="date" name="to"  value="{{ $filters['to'] }}"  class="px-3 py-1.5 border rounded text-sm bg-white dark:bg-[#081118] border-gray-200 dark:border-[#1a202c]">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Type</label>
                <select name="type" class="px-3 py-1.5 border rounded text-sm bg-white dark:bg-[#081118] border-gray-200 dark:border-[#1a202c]">
                    <option value="">All</option>
                    <option value="received" @selected($filters['type']==='received')>Cash In</option>
                    <option value="paid"     @selected($filters['type']==='paid')>Cash Out</option>
                </select>
            </div>
            <button class="px-4 py-1.5 bg-[#7c3aed] text-white rounded text-sm">Apply</button>
            <a href="{{ route('payments') }}" class="px-4 py-1.5 bg-gray-100 dark:bg-[#11151c] rounded text-sm">Reset</a>
        </form>

        <div class="bg-white dark:bg-[#0b0e14] border border-gray-200 dark:border-[#1f2937] rounded-lg p-4 shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-[#222b38]">
                    <thead class="bg-gray-50 dark:bg-[#0f1220]">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">PAY #</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Method</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-[#0b0e14] divide-y divide-gray-100 dark:divide-[#11151c]">
                        @forelse($payments as $p)
                            <tr>
                                <td class="px-4 py-3 text-sm font-mono">{{ $p->formattedId() }}</td>
                                <td class="px-4 py-3 text-sm">{{ $p->payment_date->toDateString() }}</td>
                                <td class="px-4 py-3 text-sm">{{ optional($p->customer)->name }}</td>
                                <td class="px-4 py-3 text-sm">
                                    @if($p->type === 'received')
                                        <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 text-xs">Cash In</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full bg-rose-50 text-rose-700 text-xs">Cash Out</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm">{{ $p->method ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-right">Rs {{ number_format((float)$p->amount, 2) }}</td>
                                <td class="px-4 py-3 text-sm text-right space-x-1">
                                    @if(auth()->check() && auth()->user()->isAdmin())
                                        <button @click='openEdit(@json($p))' class="inline-flex px-2 py-1 bg-amber-50 text-amber-700 rounded text-xs"><i class="fa-solid fa-pen"></i></button>
                                        <button @click="openDelete({{ $p->id }})" class="inline-flex px-2 py-1 bg-red-50 text-red-700 rounded text-xs"><i class="fa-solid fa-trash"></i></button>
                                    @else
                                        <span class="text-xs text-gray-400">No actions</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-4 py-6 text-center text-sm text-gray-500">No payments match the filter.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-4">{{ $payments->withQueryString()->links() }}</div>

        <!-- Create Modal (combined Cash In + Cash Out) -->
        <div x-show="showCreate" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/40" @click="showCreate=false"></div>
            <div class="bg-white dark:bg-[#0b0e14] rounded-lg shadow-xl w-full max-w-4xl z-50 overflow-hidden">
                <div class="px-6 py-4 border-b flex items-center justify-between">
                    <h3 class="text-lg font-medium">Create Payment</h3>
                    <button @click="showCreate=false" class="text-gray-400">✕</button>
                </div>
                <form method="POST" action="{{ route('payments.store') }}" class="px-6 py-6">
                    @csrf
                    <p class="text-xs text-gray-500 mb-4">Fill Cash In, Cash Out, or both. Empty side is ignored.</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Cash In -->
                        <div class="border border-emerald-200 dark:border-emerald-900 rounded-lg p-4 bg-emerald-50/40 dark:bg-emerald-950/20">
                            <h4 class="font-semibold text-emerald-700 dark:text-emerald-400 mb-3"><i class="fa-solid fa-arrow-down mr-2"></i>Cash In (Received)</h4>
                            <div class="space-y-3">
                                <!-- NAYA -->
                                <div x-data="partySearch('cash_in_customer')">
                                    <label class="block text-xs font-medium mb-1">Customer</label>
                                    <div class="relative">
                                        <input type="text" x-model="search"
                                            @input.debounce.300ms="fetchParties()"
                                            @focus="fetchParties()"
                                            @blur="setTimeout(() => open = false, 200)"
                                            placeholder="Search customer..."
                                            autocomplete="off"
                                            class="w-full px-3 py-2 border rounded text-sm bg-white dark:bg-[#081118] border-gray-200 dark:border-[#1a202c]">
                                        <input type="hidden" name="cash_in[customer_id]" :value="selectedId">
                                        <ul x-show="open && results.length > 0" x-transition
                                            class="absolute z-50 w-full bg-white dark:bg-[#0b0e14] border border-gray-200 dark:border-[#1a202c] rounded shadow-lg max-h-52 overflow-y-auto mt-1">
                                            <template x-for="c in results" :key="c.id">
                                                <li @mousedown.prevent="select(c)"
                                                    class="px-3 py-2 cursor-pointer hover:bg-indigo-50 dark:hover:bg-[#11151c] text-sm"
                                                    x-text="c.name + ' (' + c.city + ')'"></li>
                                            </template>
                                        </ul>
                                        <p x-show="open && results.length === 0 && search.length > 0"
                                            class="absolute z-50 w-full bg-white dark:bg-[#0b0e14] border border-gray-200 dark:border-[#1a202c] rounded shadow mt-1 px-3 py-2 text-sm text-gray-400">
                                            No customer found
                                        </p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-medium mb-1">Date</label>
                                        <input type="date" name="cash_in[payment_date]" value="{{ now()->toDateString() }}" class="w-full px-3 py-2 border rounded text-sm bg-white dark:bg-[#081118] border-gray-200 dark:border-[#1a202c]">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium mb-1">Amount</label>
                                        <input type="number" step="0.01" min="0" name="cash_in[amount]" placeholder="0.00" class="w-full px-3 py-2 border rounded text-sm bg-white dark:bg-[#081118] border-gray-200 dark:border-[#1a202c]">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium mb-1">Payment Method</label>
                                    <select name="cash_in[method]" class="w-full px-3 py-2 border rounded text-sm bg-white dark:bg-[#081118] border-gray-200 dark:border-[#1a202c]">
                                        <option value="">--</option>
                                        @foreach($methods_in as $m)<option>{{ $m }}</option>@endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium mb-1">Description</label>
                                    <textarea name="cash_in[description]" rows="2" class="w-full px-3 py-2 border rounded text-sm bg-white dark:bg-[#081118] border-gray-200 dark:border-[#1a202c]"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Cash Out -->
                        <div class="border border-rose-200 dark:border-rose-900 rounded-lg p-4 bg-rose-50/40 dark:bg-rose-950/20">
                            <h4 class="font-semibold text-rose-700 dark:text-rose-400 mb-3"><i class="fa-solid fa-arrow-up mr-2"></i>Cash Out (Paid)</h4>
                            <div class="space-y-3">
                                <!-- NAYA -->
                                <div x-data="partySearch('cash_out_customer')">
                                    <label class="block text-xs font-medium mb-1">Customer / Supplier</label>
                                    <div class="relative">
                                        <input type="text" x-model="search"
                                            @input.debounce.300ms="fetchParties()"
                                            @focus="fetchParties()"
                                            @blur="setTimeout(() => open = false, 200)"
                                            placeholder="Search customer..."
                                            autocomplete="off"
                                            class="w-full px-3 py-2 border rounded text-sm bg-white dark:bg-[#081118] border-gray-200 dark:border-[#1a202c]">
                                        <input type="hidden" name="cash_out[customer_id]" :value="selectedId">
                                        <ul x-show="open && results.length > 0" x-transition
                                            class="absolute z-50 w-full bg-white dark:bg-[#0b0e14] border border-gray-200 dark:border-[#1a202c] rounded shadow-lg max-h-52 overflow-y-auto mt-1">
                                            <template x-for="c in results" :key="c.id">
                                                <li @mousedown.prevent="select(c)"
                                                    class="px-3 py-2 cursor-pointer hover:bg-indigo-50 dark:hover:bg-[#11151c] text-sm"
                                                    x-text="c.name + ' (' + c.city + ')'"></li>
                                            </template>
                                        </ul>
                                        <p x-show="open && results.length === 0 && search.length > 0"
                                            class="absolute z-50 w-full bg-white dark:bg-[#0b0e14] border border-gray-200 dark:border-[#1a202c] rounded shadow mt-1 px-3 py-2 text-sm text-gray-400">
                                            No customer found
                                        </p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-medium mb-1">Date</label>
                                        <input type="date" name="cash_out[payment_date]" value="{{ now()->toDateString() }}" class="w-full px-3 py-2 border rounded text-sm bg-white dark:bg-[#081118] border-gray-200 dark:border-[#1a202c]">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium mb-1">Amount</label>
                                        <input type="number" step="0.01" min="0" name="cash_out[amount]" placeholder="0.00" class="w-full px-3 py-2 border rounded text-sm bg-white dark:bg-[#081118] border-gray-200 dark:border-[#1a202c]">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium mb-1">Payment Method</label>
                                    <select name="cash_out[method]" class="w-full px-3 py-2 border rounded text-sm bg-white dark:bg-[#081118] border-gray-200 dark:border-[#1a202c]">
                                        <option value="">--</option>
                                        @foreach($methods_out as $m)<option>{{ $m }}</option>@endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium mb-1">Description</label>
                                    <textarea name="cash_out[description]" rows="2" class="w-full px-3 py-2 border rounded text-sm bg-white dark:bg-[#081118] border-gray-200 dark:border-[#1a202c]"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-2 mt-6">
                        <button type="button" @click="showCreate=false" class="px-4 py-2 bg-gray-100 dark:bg-[#11151c] rounded">Cancel</button>
                        <button type="submit" class="px-5 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded shadow">Save Payment(s)</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Modal -->
        <div x-show="showEdit" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/40" @click="showEdit=false"></div>
            <div class="bg-white dark:bg-[#0b0e14] rounded-lg shadow-xl w-full max-w-lg z-50 overflow-hidden">
                <div class="px-6 py-4 border-b"><h3 class="text-lg font-medium">Edit Payment</h3></div>
                    <form class="px-6 py-6 space-y-4" @submit.prevent="saveEdit()" @customer-selected.window="form.customer_id = $event.detail.id">
                    <!-- NAYA -->
                    <div x-data="partySearch('edit_customer')" x-init="initEdit($watch)">
                        <label class="block text-sm font-medium mb-1">Customer *</label>
                        <div class="relative">
                            <input type="text" x-model="search"
                                @input.debounce.300ms="fetchParties()"
                                @focus="fetchParties()"
                                @blur="setTimeout(() => open = false, 200)"
                                placeholder="Search customer..."
                                autocomplete="off"
                                required
                                class="w-full px-3 py-2 border rounded bg-white dark:bg-[#081118] border-gray-200 dark:border-[#1a202c]">
                            <ul x-show="open && results.length > 0" x-transition
                                class="absolute z-50 w-full bg-white dark:bg-[#0b0e14] border border-gray-200 dark:border-[#1a202c] rounded shadow-lg max-h-52 overflow-y-auto mt-1">
                                <template x-for="c in results" :key="c.id">
                                    <li @mousedown.prevent="select(c); $dispatch('customer-selected', {id: c.id})"
                                        class="px-3 py-2 cursor-pointer hover:bg-indigo-50 dark:hover:bg-[#11151c] text-sm"
                                        x-text="c.name + ' (' + c.city + ')'"></li>
                                </template>
                            </ul>
                            <p x-show="open && results.length === 0 && search.length > 0"
                                class="absolute z-50 w-full bg-white dark:bg-[#0b0e14] border border-gray-200 dark:border-[#1a202c] rounded shadow mt-1 px-3 py-2 text-sm text-gray-400">
                                No customer found
                            </p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium mb-1">Date *</label>
                            <input type="date" x-model="form.payment_date" required class="w-full px-3 py-2 border rounded bg-white dark:bg-[#081118] border-gray-200 dark:border-[#1a202c]">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Amount *</label>
                            <input type="number" step="0.01" min="0.01" x-model="form.amount" required class="w-full px-3 py-2 border rounded bg-white dark:bg-[#081118] border-gray-200 dark:border-[#1a202c]">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium mb-1">Type *</label>
                            <select x-model="form.type" required class="w-full px-3 py-2 border rounded bg-white dark:bg-[#081118] border-gray-200 dark:border-[#1a202c]">
                                <option value="received">Cash In</option>
                                <option value="paid">Cash Out</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Method</label>
                            <select x-model="form.method" class="w-full px-3 py-2 border rounded bg-white dark:bg-[#081118] border-gray-200 dark:border-[#1a202c]">
                                <option value="">--</option>
                                @foreach($methods as $m)<option>{{ $m }}</option>@endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Description</label>
                        <textarea x-model="form.description" rows="2" class="w-full px-3 py-2 border rounded bg-white dark:bg-[#081118] border-gray-200 dark:border-[#1a202c]"></textarea>
                    </div>
                    <div class="flex justify-end space-x-2">
                        <button type="button" @click="showEdit=false" class="px-4 py-2 bg-gray-100 dark:bg-[#11151c] rounded">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded">Save</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Modal -->
        <div x-show="showDelete" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/40" @click="showDelete=false"></div>
            <div class="bg-white dark:bg-[#0b0e14] rounded-lg shadow-xl w-full max-w-md z-50 overflow-hidden">
                <div class="px-6 py-4 border-b"><h3 class="font-medium">Delete Payment</h3></div>
                <div class="px-6 py-4 text-sm">Delete this payment? Related cashbook entry will also be removed.</div>
                <div class="px-6 py-4 border-t flex justify-end space-x-2">
                    <button @click="showDelete=false" class="px-4 py-2 bg-gray-100 dark:bg-[#11151c] rounded">Cancel</button>
                    <button @click="confirmDelete()" class="px-4 py-2 bg-red-600 text-white rounded">Yes, Delete</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function paymentsManager() {
            return {
                showCreate: false, showEdit: false, showDelete: false,
                deletingId: null,
                form: { id: null, customer_id: '', payment_date: '{{ now()->toDateString() }}', amount: 0, type: 'received', method: '', description: '' },
                openCreate() { this.showCreate = true; },
                openEdit(p) {
                    this.form = { id: p.id, customer_id: p.customer_id, payment_date: p.payment_date.substring(0,10), amount: p.amount, type: p.type, method: p.method||'', description: p.description||'' };
                    this.showEdit = true;
                     // edit modal search field pre-fill karne ke liye
                        this.$nextTick(() => {
                            window.dispatchEvent(new CustomEvent('payment-edit-opened', {
                                detail: {
                                    customer_id:   p.customer_id,
                                    customer_name: p.customer ? p.customer.name + ' (' + p.customer.city + ')' : ''
                                }
                            }));
                        });
                },
                openDelete(id) { this.deletingId = id; this.showDelete = true; },
                async confirmDelete() {
                    const token = document.querySelector('meta[name=csrf-token]').content;
                    await fetch('/payments/' + this.deletingId, { method:'DELETE', headers:{'X-CSRF-TOKEN':token,'Accept':'application/json'} });
                    window.location.reload();
                },
                async saveEdit() {
                    const token = document.querySelector('meta[name=csrf-token]').content;
                    const res = await fetch('/payments/' + this.form.id, {
                        method: 'PUT',
                        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':token,'Accept':'application/json'},
                        body: JSON.stringify(this.form)
                    });
                    if (res.ok) window.location.reload();
                    else { const e = await res.json().catch(()=>({})); alert(e.message || 'Save failed'); }
                }
            }
        }

        function partySearch(key) {
            return {
                search: '',
                selectedId: '',
                results: [],
                open: false,
                // Edit modal mein pre-fill karne ke liye
                initEdit($watch) {
                    if (key !== 'edit_customer') return;
                    $watch('$store', () => {});
                    // parent se form watch karo
                    this.$nextTick(() => {
                        window.addEventListener('payment-edit-opened', (e) => {
                            this.selectedId = e.detail.customer_id || '';
                            this.search     = e.detail.customer_name || '';
                        });
                    });
                },
                async fetchParties() {
                    if (this.search.length < 1) { this.open = false; return; }
                    try {
                        const res = await fetch(`/admin/customers/search?q=${encodeURIComponent(this.search)}`);
                        this.results = await res.json();
                        this.open = true;
                    } catch(e) { console.error(e); }
                },
                select(c) {
                    this.selectedId = c.id;
                    this.search = c.name + ' (' + c.city + ')';
                    this.open = false;
                }
            }
        }
    </script>

@endsection
