@extends('layouts.admin')

@section('title', 'Purchase Invoices')

@section('content')
    <div class="max-w-7xl mx-auto" x-data="{ showDelete: false, deletingId: null, deletingLabel: '' }">
       <!-- NAYA -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-3">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Purchase Invoices</h2>
                <p class="text-sm text-gray-500">Incoming stock from suppliers</p>
            </div>
            <div class="flex items-center gap-2">
                <div class="relative" x-data="purchaseSearch()">
                    <input
                        type="text"
                        x-model="query"
                        @input.debounce.300ms="search()"
                        placeholder="Search by supplier..."
                        class="pl-9 pr-8 py-2 border rounded-lg text-sm bg-white dark:bg-[#081118] border-gray-200 dark:border-[#1a202c] focus:outline-none focus:ring-2 focus:ring-purple-500 w-56"
                    >
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <i x-show="query" @click="clearSearch()" class="fa-solid fa-xmark absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs cursor-pointer hover:text-gray-600"></i>
                </div>
                <a href="{{ route('purchase-invoice.create') }}" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg shadow whitespace-nowrap">
                    <i class="fa-solid fa-plus mr-2"></i> Create Purchase Invoice
                </a>
            </div>
        </div>

        @if(session('success'))<div class="mb-4 px-4 py-2 rounded-lg bg-green-50 text-green-700">{{ session('success') }}</div>@endif

        <div class="bg-white dark:bg-[#0b0e14] border border-gray-200 dark:border-[#1f2937] rounded-lg p-4 shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-[#222b38]">
                    <thead class="bg-gray-50 dark:bg-[#0f1220]">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">PI #</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Supplier</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bill Date</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Product</th>
                            {{-- <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Quantity</th> --}}
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Rate</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Weight</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total Amount</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-[#0b0e14] divide-y divide-gray-100 dark:divide-[#11151c]">
                        @forelse($invoices as $inv)
                            <tr>
                                <td class="px-4 py-3 text-sm font-mono whitespace-nowrap">{{ $inv->formattedId() }}</td>
                                <td class="px-4 py-3 text-sm whitespace-nowrap">{{ optional($inv->supplier)->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm whitespace-nowrap">{{ $inv->bill_date->toDateString() }}</td>
                                <td class="px-4 py-3 text-sm text-center">
                                    {{ $inv->items->pluck('product.name')->filter()->implode(', ') ?: '-' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-right whitespace-nowrap">
                                    {{ $inv->items->sum('weight') > 0 ? number_format($inv->total_amount / $inv->items->sum('weight'), 2) : '-' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-right whitespace-nowrap">
                                    {{ number_format($inv->items->sum('weight'), 2) }}
                                </td>
                                <td class="px-4 py-3 text-sm text-right whitespace-nowrap">Rs {{ number_format((float)$inv->total_amount, 2) }}</td>
                                <td class="px-4 py-3 text-sm text-right space-x-1 whitespace-nowrap">
                                    <a href="{{ route('purchase-invoice.print', $inv) }}"
                                       class="purchase-print-btn inline-flex px-2 py-1 bg-gray-100 dark:bg-[#11151c] rounded text-xs"
                                       data-url="{{ route('purchase-invoice.print-local', $inv) }}"
                                       data-invoice-id="{{ $inv->formattedId() }}"
                                       data-supplier="{{ optional($inv->supplier)->name ?? 'Unknown' }}">
                                        <i class="fa-solid fa-print mr-1"></i>
                                        Print
                                    </a>
                                    <a href="{{ route('purchase-invoice.edit', $inv) }}" class="inline-flex px-2 py-1 bg-amber-50 text-amber-700 rounded text-xs"><i class="fa-solid fa-pen mr-1"></i> Edit</a>
                                    <button type="button" @click="deletingId={{ $inv->id }}; deletingLabel='{{ $inv->formattedId() }}'; showDelete=true" class="inline-flex px-2 py-1 bg-red-50 text-red-700 rounded text-xs"><i class="fa-solid fa-trash mr-1"></i> Delete</button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-4 py-6 text-center text-sm text-gray-500">No purchase invoices yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-4">{{ $invoices->links() }}</div>

        <!-- Delete Modal -->
        <div x-show="showDelete" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/40" @click="showDelete=false"></div>
            <div class="bg-white dark:bg-[#0b0e14] rounded-lg shadow-xl w-full max-w-md z-50 overflow-hidden">
                <div class="px-6 py-4 border-b"><h3 class="font-medium">Delete Purchase Invoice</h3></div>
                <div class="px-6 py-4 text-sm">
                    Delete purchase invoice <span x-text="deletingLabel" class="font-mono"></span>?
                    This will reverse the inventory quantity &amp; weight for the purchased items.
                </div>
                <div class="px-6 py-4 border-t flex justify-end space-x-2">
                    <button @click="showDelete=false" class="px-4 py-2 bg-gray-100 dark:bg-[#11151c] rounded">Cancel</button>
                    <form :action="'/purchase-invoice/' + deletingId" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded">Yes, Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    function purchaseSearch() {
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

    <script src="{{ asset('js/local-file-manager.js') }}"></script>

    <script>
    document.addEventListener("DOMContentLoaded", () => {

        document.querySelectorAll(".purchase-print-btn").forEach(button => {

            button.addEventListener("click", async (event) => {

                event.preventDefault();

                const originalText = button.innerHTML;

                try {

                    /* Disable Button */
                    button.disabled = true;
                    button.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i> Preparing...';

                    /* Get Button Data */
                    const printUrl = button.href;
                    const localPdfUrl = button.dataset.url;
                    const invoiceId = button.dataset.invoiceId;
                    const supplierName = button.dataset.supplier || "Unknown";

                    /* Validate Data */
                    if (!localPdfUrl) {
                        throw new Error("Purchase Invoice PDF URL is missing.");
                    }

                    if (!invoiceId) {
                        throw new Error("Purchase Invoice ID is missing.");
                    }

                    /*
                    | IMPORTANT: Get Root Folder BEFORE fetch().
                    | showDirectoryPicker() needs user activation.
                    */
                    button.innerHTML = '<i class="fa-solid fa-folder-open mr-1"></i> Checking Folder...';

                    const rootHandle = await POSFileManager.getRootHandle();

                    if (!rootHandle) {
                        throw new Error("Could not access the selected root folder.");
                    }

                    /* Fetch Purchase Invoice PDF */
                    button.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i> Saving...';

                    const response = await fetch(localPdfUrl, {
                        method: "GET",
                        headers: {
                            "Accept": "application/pdf"
                        }
                    });

                    /* Check PDF Response */
                    if (!response.ok) {
                        throw new Error(`PDF request failed: ${response.status}`);
                    }

                    /* Convert Response To Blob */
                    const blob = await response.blob();

                    if (!blob || blob.size === 0) {
                        throw new Error("The generated PDF is empty.");
                    }

                    /* Check PDF Content Type */
                    if (blob.type && !blob.type.includes("pdf")) {
                        console.warn("Expected PDF but received:", blob.type);
                    }

                    
                    const safeSupplierName = supplierName.trim().replace(/[<>:"/\\|?*]/g, "_");

                    /* Current Date */
                    const now = new Date();

                    const day = String(now.getDate()).padStart(2, "0");
                    const month = String(now.getMonth() + 1).padStart(2, "0");
                    const year = now.getFullYear();

                    const today = `${day}-${month}-${year}`;

                    const filename = `${invoiceId}_${safeSupplierName}_${today}.pdf`;

                    
                    await POSFileManager.saveBlob(filename,blob,
                    [
                        today,
                        "Purchase Invoice",
                        safeSupplierName
                    ]
                    );

                    
                    alert(`Purchase Invoice saved successfully!\n\n${filename}`);

                    
                    button.innerHTML = originalText;
                    button.disabled = false;

                    /* Open Existing Print Page */
                    if (printUrl) {
                        window.open(printUrl, "_blank");
                    }

                } catch (error) {

                    console.error("Purchase Invoice Local PDF Error:", error);
                    console.error("Error Name:", error.name);
                    console.error("Error Message:", error.message);

                    /* Show Error */
                    alert("Could not save Purchase Invoice:\n\n" + error.message);

                    /* Restore Button */
                    button.innerHTML = originalText;
                    button.disabled = false;
                }

            });

        });

    });
    </script>
@endsection