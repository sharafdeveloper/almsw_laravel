@extends('layouts.admin')

@section('title', 'Customers')

@section('content')
    <div x-data="customerManager()" class="max-w-7xl mx-auto">

        <!-- NAYA -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-3">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Customers</h2>
                <p class="text-sm text-gray-500">Manage your clients and their ledgers</p>
            </div>
            <div class="flex items-center gap-2">
                <div class="relative" x-data="customerSearch()">
                    <input
                        type="text"
                        x-model="query"
                        @input.debounce.300ms="search()"
                        placeholder="Search by name..."
                        class="pl-9 pr-8 py-2 border rounded-lg text-sm bg-white dark:bg-[#081118] border-gray-200 dark:border-[#1a202c] focus:outline-none focus:ring-2 focus:ring-purple-500 w-56"
                    >
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <i x-show="query" @click="clearSearch()" class="fa-solid fa-xmark absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs cursor-pointer hover:text-gray-600"></i>
                </div>
                <button @click="openCreate()" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg shadow whitespace-nowrap">
                    <i class="fa-solid fa-plus mr-2"></i> Add Client
                </button>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-4 px-4 py-2 rounded-lg bg-green-50 text-green-700">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mb-4 px-4 py-2 rounded-lg bg-red-50 text-red-700">{{ session('error') }}</div>
        @endif

        <div class="bg-white dark:bg-[#0b0e14] border border-gray-200 dark:border-[#1f2937] rounded-lg p-4 shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-[#222b38]">
                    <thead class="bg-gray-50 dark:bg-[#0f1220]">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">City</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Opening Balance</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-[#0b0e14] divide-y divide-gray-100 dark:divide-[#11151c]">
                        @forelse($customers as $c)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $c->id }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $c->name }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $c->city }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                    Rs {{ number_format(abs((float)$c->opening_balance), 2) }}
                                    @if((float)$c->opening_balance < 0)
                                        <span class="ml-1 px-1.5 py-0.5 rounded text-xs bg-amber-50 text-amber-700">Cr</span>
                                    @else
                                        <span class="ml-1 px-1.5 py-0.5 rounded text-xs bg-emerald-50 text-emerald-700">Dr</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-right space-x-2 whitespace-nowrap">
                                    <a href="{{ route('customers.ledger', $c) }}" class="inline-flex items-center px-3 py-1.5 bg-indigo-50 text-indigo-700 rounded hover:bg-indigo-100">
                                        <i class="fa-solid fa-book-open mr-1"></i> Ledger
                                    </a>
                                    <button @click='openEdit(@json($c))' class="inline-flex items-center px-3 py-1.5 bg-gray-100 dark:bg-[#11151c] text-gray-700 dark:text-gray-200 rounded">
                                        <i class="fa-solid fa-pen mr-1"></i> Edit
                                    </button>
                                    <button @click='openDelete(@json($c))' class="inline-flex items-center px-3 py-1.5 bg-red-50 text-red-700 rounded">
                                        <i class="fa-solid fa-trash mr-1"></i> Delete
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-6 text-center text-sm text-gray-500">No customers yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-4">{{ $customers->links() }}</div>

        <!-- Create / Edit Modal -->
        <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/40" @click="closeModal()"></div>
            <div class="bg-white dark:bg-[#0b0e14] rounded-lg shadow-xl w-full max-w-lg z-50 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-[#11151c]">
                    <h3 class="text-lg font-medium text-gray-800 dark:text-white" x-text="modalTitle"></h3>
                </div>
                <form class="px-6 py-6 space-y-4" @submit.prevent="submitForm()">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name *</label>
                        <input type="text" x-model="form.name" required class="w-full px-3 py-2 border rounded-md bg-white dark:bg-[#081118] border-gray-200 dark:border-[#1a202c] text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">City *</label>
                        <select x-model="form.city" required class="w-full px-3 py-2 border rounded-md bg-white dark:bg-[#081118] border-gray-200 dark:border-[#1a202c] text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <option value="">-- Select City --</option>
                            @foreach($cities as $city)
                                <option value="{{ $city }}">{{ $city }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Opening Balance</label>
                        <div class="flex gap-2">
                            <select x-model="form.balance_type" class="px-3 py-2 border rounded-md bg-white dark:bg-[#081118] border-gray-200 dark:border-[#1a202c] text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-purple-500">
                                <option value="dr">Dr (Debit)</option>
                                <option value="cr">Cr (Credit)</option>
                            </select>
                            <input type="number" step="0.01" min="0" x-model="form.opening_amount" placeholder="0.00" class="flex-1 px-3 py-2 border rounded-md bg-white dark:bg-[#081118] border-gray-200 dark:border-[#1a202c] text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-purple-500">
                        </div>
                        <p class="text-xs text-gray-400 mt-1"><strong>Dr</strong> = customer aap ko paisa deta hai (owes you). <strong>Cr</strong> = aap customer ko dete ho (advance/credit).</p>
                    </div>
                    <div class="flex justify-end space-x-2 pt-2">
                        <button type="button" @click="closeModal()" class="px-4 py-2 bg-gray-100 dark:bg-[#11151c] rounded">Cancel</button>
                        <button type="submit" :disabled="saving" class="px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded">Save</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Modal -->
        <div x-show="showDelete" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/40" @click="showDelete=false"></div>
            <div class="bg-white dark:bg-[#0b0e14] rounded-lg shadow-xl w-full max-w-md z-50 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-[#11151c]">
                    <h3 class="text-lg font-medium">Delete Customer</h3>
                </div>
                <div class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                    Are you sure you want to delete <span class="font-semibold" x-text="deleting.name"></span>?
                    If they have invoices/payments, the record will be archived instead.
                </div>
                <div class="px-6 py-4 border-t flex justify-end space-x-2">
                    <button @click="showDelete=false" class="px-4 py-2 bg-gray-100 dark:bg-[#11151c] rounded">Cancel</button>
                    <button @click="confirmDelete()" class="px-4 py-2 bg-red-600 text-white rounded">Yes, Delete</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function customerManager() {
            return {
                showModal: false,
                showDelete: false,
                saving: false,
                modalTitle: 'Add Client',
                method: 'POST',
                form: { id: null, name: '', city: '', balance_type: 'dr', opening_amount: '' },
                deleting: { id: null, name: '' },
                openCreate() {
                    this.form = { id: null, name: '', city: '', balance_type: 'dr', opening_amount: '' };
                    this.method = 'POST';
                    this.modalTitle = 'Add Client';
                    this.showModal = true;
                },
                openEdit(c) {
                    const bal = Number(c.opening_balance) || 0;
                    this.form = {
                        id: c.id,
                        name: c.name,
                        city: c.city,
                        balance_type: bal < 0 ? 'cr' : 'dr',
                        opening_amount: Math.abs(bal),
                    };
                    this.method = 'PUT';
                    this.modalTitle = 'Edit Client';
                    this.showModal = true;
                },
                closeModal() { this.showModal = false; },
                openDelete(c) { this.deleting = { id: c.id, name: c.name }; this.showDelete = true; },
                async confirmDelete() {
                    const token = document.querySelector('meta[name=csrf-token]').content;
                    await fetch('/customers/' + this.deleting.id, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' }
                    });
                    window.location.reload();
                },
                async submitForm() {
                    this.saving = true;
                    const token = document.querySelector('meta[name=csrf-token]').content;
                    const url = this.method === 'PUT' ? '/customers/' + this.form.id : '/customers';
                    try {
                        const res = await fetch(url, {
                            method: this.method,
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                            body: JSON.stringify(this.form)
                        });
                        if (!res.ok) {
                            const err = await res.json().catch(()=>({}));
                            alert(err.message || 'Failed to save');
                        } else {
                            window.location.reload();
                        }
                    } finally { this.saving = false; }
                }
            }
        }


        function customerSearch() {
            return {
                query: '{{ $q ?? '' }}',
                search() {
                    const url = new URL(window.location.href);
                    url.searchParams.set('q', this.query);
                    url.searchParams.delete('page');
                    window.location.href = url.toString();
                },
                clearSearch() {
                    this.query = '';
                    this.search();
                }
            }
        }
    </script>
@endsection
