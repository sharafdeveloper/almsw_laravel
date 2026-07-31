@extends('layouts.admin')

@section('title', 'Products')

@section('content')
    <div x-data="productManager()" class="max-w-7xl mx-auto">
        <!-- NAYA -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-3">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Products</h2>
                <p class="text-sm text-gray-500">Manage product master list. Use the button to add new products.</p>
            </div>
            <div class="flex items-center gap-2">
                <!-- Search Box -->
                <div class="relative" x-data="productSearch()">
                    <input
                        type="text"
                        x-model="query"
                        @input.debounce.300ms="search()"
                        placeholder="Search products..."
                        class="pl-9 pr-4 py-2 border rounded-lg text-sm bg-white dark:bg-[#081118] border-gray-200 dark:border-[#1a202c] focus:outline-none focus:ring-2 focus:ring-purple-500 w-56"
                    >
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <i x-show="query" @click="clearSearch()" class="fa-solid fa-xmark absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs cursor-pointer hover:text-gray-600"></i>
                </div>
                <button @click="openCreate()" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg shadow hover:opacity-95 focus:outline-none whitespace-nowrap">
                    <i class="fa-solid fa-plus mr-2"></i> Add Product
                </button>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-4 px-4 py-2 rounded-lg bg-green-50 text-green-700 flash-message">{{ session('success') }}</div>
        @endif
        @if(session('warning'))
            <div class="mb-4 px-4 py-2 rounded-lg bg-yellow-50 text-yellow-700 flash-message">{{ session('warning') }}</div>
        @endif
        @if(session('error'))
            <div class="mb-4 px-4 py-2 rounded-lg bg-red-50 text-red-700 flash-message">{{ session('error') }}</div>
        @endif

        <div class="bg-white dark:bg-[#0b0e14] border border-gray-200 dark:border-[#1f2937] rounded-lg p-4 shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-[#222b38]">
                    <thead class="bg-gray-50 dark:bg-[#0f1220]">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-[#0b0e14] divide-y divide-gray-100 dark:divide-[#11151c]">
                        @forelse($products as $product)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $product->id }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $product->name }}</td>
                                <td class="px-4 py-3 text-sm text-right space-x-2">
                                    <button type="button" @click='openEdit(@json($product))' class="inline-flex items-center px-3 py-1.5 bg-gray-100 dark:bg-[#11151c] text-gray-700 dark:text-gray-200 rounded hover:bg-gray-200">
                                        <i class="fa-solid fa-pen-to-square mr-2"></i> Edit
                                    </button>

                                    <button type="button" @click='openDelete(@json($product))' class="inline-flex items-center px-3 py-1.5 bg-red-50 text-red-700 rounded hover:bg-red-100">
                                        <i class="fa-solid fa-trash mr-2"></i> Delete
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-6 text-center text-sm text-gray-500">No products found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">{{ $products->links() }}</div>

        <!-- Modal -->
        <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/40" @click="closeModal()"></div>

            <div class="bg-white dark:bg-[#0b0e14] rounded-lg shadow-xl w-full max-w-lg z-50 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-[#11151c] flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-800 dark:text-white" x-text="modalTitle"></h3>
                    <button @click="closeModal()" class="text-gray-500 hover:text-gray-700">✕</button>
                </div>

                <form class="px-6 py-6" @submit.prevent="submitForm()">
                    @csrf
                    <input type="hidden" name="_method" :value="method">

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name</label>
                        <input type="text" name="name" x-model="name" required class="w-full px-3 py-2 border rounded-md bg-white dark:bg-[#081118] border-gray-200 dark:border-[#1a202c] text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>

                    <div class="flex items-center justify-end space-x-2">
                        <button type="button" @click="closeModal()" class="px-4 py-2 bg-gray-100 dark:bg-[#11151c] rounded">Cancel</button>
                        <button type="submit" x-bind:disabled="saving" class="px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded">Save</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div x-show="showDeleteModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/40" @click="closeDelete()"></div>

            <div class="bg-white dark:bg-[#0b0e14] rounded-lg shadow-xl w-full max-w-md z-50 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-[#11151c]">
                    <h3 class="text-lg font-medium text-gray-800 dark:text-white">Delete Product</h3>
                </div>

                <div class="px-6 py-4">
                    <p class="text-sm text-gray-600 dark:text-gray-300">Are you sure you want to permanently delete <span class="font-semibold" x-text="deletingName"></span>? This action cannot be undone.</p>
                </div>

                <div class="px-6 py-4 border-t border-gray-100 dark:border-[#11151c] flex justify-end space-x-2">
                    <button type="button" @click="closeDelete()" class="px-4 py-2 bg-gray-100 dark:bg-[#11151c] rounded">Cancel</button>
                    <button type="button" @click="confirmDelete()" x-bind:disabled="deleteSaving" class="px-4 py-2 bg-red-600 text-white rounded">Yes, Delete</button>
                </div>
            </div>
        </div>

    </div>

    <script>
        function productManager() {
            return {
                showModal: false,
                method: 'POST',
                formAction: '{{ route('products.store') }}',
                name: '',
                modalTitle: 'Create Product',
                saving: false,

                openCreate() {
                    this.method = 'POST';
                    this.formAction = '{{ route('products.store') }}';
                    this.name = '';
                    this.modalTitle = 'Create Product';
                    this.showModal = true;
                },

                openEdit(product) {
                    this.method = 'PUT';
                    this.formAction = '/products/' + product.id;
                    this.name = product.name || '';
                    this.modalTitle = 'Edit Product';
                    this.showModal = true;
                },

                closeModal() {
                    this.showModal = false;
                },

                // Delete modal state and actions
                deletingId: null,
                deletingName: '',
                showDeleteModal: false,
                deleteSaving: false,

                openDelete(product) {
                    this.deletingId = product.id;
                    this.deletingName = product.name || '';
                    this.showDeleteModal = true;
                },

                closeDelete() {
                    this.deletingId = null;
                    this.deletingName = '';
                    this.showDeleteModal = false;
                },

                async confirmDelete() {
                    if (!this.deletingId) return;
                    this.deleteSaving = true;
                    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    try {
                        const res = await fetch('/products/' + this.deletingId, {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': token
                            }
                        });

                        if (!res.ok) {
                            const err = await res.json().catch(() => ({}));
                            const msg = err.message || 'Failed to delete product.';
                            alert(msg);
                            this.deleteSaving = false;
                            return;
                        }

                        // server sets session flash; reload to show message and updated table
                        this.closeDelete();
                        window.location.reload();
                    } catch (e) {
                        alert('Network error');
                    } finally {
                        this.deleteSaving = false;
                    }
                },

                async submitForm() {
                    this.saving = true;
                    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    const url = this.formAction;
                    const method = this.method === 'PUT' ? 'PUT' : 'POST';

                    try {
                        const res = await fetch(url, {
                            method: method,
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': token
                            },
                            body: JSON.stringify({ name: this.name })
                        });

                        if (!res.ok) {
                            const err = await res.json().catch(() => ({}));
                            const msg = err.message || (err.errors ? Object.values(err.errors).flat().join(', ') : 'Failed to save product.');
                            alert(msg);
                            this.saving = false;
                            return;
                        }

                        // success - reload to refresh table and show flash message
                        this.closeModal();
                        window.location.reload();
                    } catch (e) {
                        alert('Network error');
                    } finally {
                        this.saving = false;
                    }
                }
            }
        }

        function productSearch() {
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

    <script>
        // Auto-hide flash messages after 3 seconds
        (function(){
            const flashes = document.querySelectorAll('.flash-message');
            if (!flashes.length) return;
            setTimeout(() => {
                flashes.forEach(el => {
                    el.style.transition = 'opacity 300ms ease';
                    el.style.opacity = '0';
                    setTimeout(() => el.remove(), 350);
                });
            }, 3000);
        })();
    </script>
@endsection
