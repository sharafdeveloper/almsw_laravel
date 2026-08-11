```blade
@extends('layouts.admin')

@section('title', 'Sale Invoices')

@section('content')

    <div class="max-w-7xl mx-auto"
         x-data="{ showDelete: false, deletingId: null, deletingLabel: '' }">

        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-3">

            <div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white">
                    Sale Invoices
                </h2>

                <p class="text-sm text-gray-500">
                    List of all outgoing sales
                </p>
            </div>


            <div class="flex items-center gap-2">

                <!-- Search -->
                <div class="relative" x-data="saleSearch()">

                    <input
                        type="text"
                        x-model="query"
                        @input.debounce.300ms="search()"
                        placeholder="Search by customer..."
                        class="pl-9 pr-8 py-2 border rounded-lg text-sm
                               bg-white dark:bg-[#081118]
                               border-gray-200 dark:border-[#1a202c]
                               focus:outline-none focus:ring-2
                               focus:ring-purple-500 w-56"
                    >

                    <i class="fa-solid fa-magnifying-glass
                              absolute left-3 top-1/2 -translate-y-1/2
                              text-gray-400 text-xs">
                    </i>

                    <i
                        x-show="query"
                        @click="clearSearch()"
                        class="fa-solid fa-xmark
                               absolute right-3 top-1/2 -translate-y-1/2
                               text-gray-400 text-xs cursor-pointer
                               hover:text-gray-600">
                    </i>

                </div>


                <!-- Create Invoice -->
                <a
                    href="{{ route('sale-invoice.create') }}"
                    class="inline-flex items-center px-4 py-2
                           bg-gradient-to-r from-indigo-600 to-purple-600
                           text-white rounded-lg shadow whitespace-nowrap"
                >
                    <i class="fa-solid fa-plus mr-2"></i>
                    Create Sale Invoice
                </a>

            </div>

        </div>


        <!-- Success Message -->
        @if(session('success'))

            <div class="mb-4 px-4 py-2 rounded-lg
                        bg-green-50 text-green-700">

                {{ session('success') }}

            </div>

        @endif


        <!-- Invoice Table -->
        <div class="bg-white dark:bg-[#0b0e14]
                    border border-gray-200 dark:border-[#1f2937]
                    rounded-lg p-4 shadow-sm">

            <div class="overflow-x-auto">

                <table class="min-w-full divide-y
                              divide-gray-200 dark:divide-[#222b38]">

                    <!-- Table Header -->
                    <thead class="bg-gray-50 dark:bg-[#0f1220]">

                        <tr>

                            <th class="px-4 py-3 text-left text-xs
                                       font-medium text-gray-500 uppercase">
                                Invoice #
                            </th>

                            <th class="px-4 py-3 text-left text-xs
                                       font-medium text-gray-500 uppercase">
                                Customer
                            </th>

                            <th class="px-4 py-3 text-left text-xs
                                       font-medium text-gray-500 uppercase">
                                Bill Date
                            </th>

                            <th class="px-4 py-3 text-center text-xs
                                       font-medium text-gray-500 uppercase">
                                Product
                            </th>

                            <th class="px-4 py-3 text-right text-xs
                                       font-medium text-gray-500 uppercase">
                                Rate
                            </th>

                            <th class="px-4 py-3 text-right text-xs
                                       font-medium text-gray-500 uppercase">
                                Weight
                            </th>

                            <th class="px-4 py-3 text-right text-xs
                                       font-medium text-gray-500 uppercase">
                                Bill Amount
                            </th>

                            <th class="px-4 py-3 text-left text-xs
                                       font-medium text-gray-500 uppercase">
                                Cash Received
                            </th>

                            <th class="px-4 py-3 text-center text-xs
                                       font-medium text-gray-500 uppercase">
                                Actions
                            </th>

                        </tr>

                    </thead>


                    <!-- Table Body -->
                    <tbody class="bg-white dark:bg-[#0b0e14]
                                 divide-y divide-gray-100
                                 dark:divide-[#11151c]">

                        @forelse($invoices as $inv)

                            <tr>

                                <!-- Invoice ID -->
                                <td class="px-4 py-3 text-sm font-mono
                                           whitespace-nowrap">

                                    {{ $inv->formattedId() }}

                                </td>


                                <!-- Customer -->
                                <td class="px-4 py-3 text-sm
                                           whitespace-nowrap">

                                    {{ optional($inv->customer)->name }}

                                </td>


                                <!-- Bill Date -->
                                <td class="px-4 py-3 text-sm
                                           whitespace-nowrap">

                                    {{ $inv->bill_date->toDateString() }}

                                </td>


                                <!-- Product -->
                                <td class="px-4 py-3 text-sm text-center">

                                    {{ $inv->items->pluck('product.name')->filter()->implode(', ') ?: '-' }}

                                </td>


                                <!-- Rate -->
                                <td class="px-4 py-3 text-sm text-right
                                           whitespace-nowrap">

                                    {{
                                        $inv->items->sum('weight') > 0
                                            ? number_format(
                                                $inv->sub_total /
                                                $inv->items->sum('weight'),
                                                2
                                            )
                                            : '-'
                                    }}

                                </td>


                                <!-- Weight -->
                                <td class="px-4 py-3 text-sm text-right
                                           whitespace-nowrap">

                                    {{ number_format($inv->items->sum('weight'), 2) }}

                                </td>


                                <!-- Bill Amount -->
                                <td class="px-4 py-3 text-sm text-right
                                           whitespace-nowrap">

                                    Rs {{ number_format((float) $inv->total, 2) }}

                                </td>


                                <!-- Cash Received -->
                                <td class="px-4 py-3 text-sm text-right
                                           whitespace-nowrap">

                                    Rs {{ number_format((float) $inv->cash_received, 2) }}

                                </td>


                                <!-- Actions -->
                                <td class="px-4 py-3 text-sm text-right
                                           space-x-1 whitespace-nowrap">


                                    <!-- Print / Save Local PDF -->
                                    <a
                                        href="{{ route('sale-invoice.print', $inv) }}"
                                        class="local-print-btn inline-flex
                                               px-2 py-1
                                               bg-gray-100
                                               dark:bg-[#11151c]
                                               rounded text-xs"
                                        data-url="{{ route('sale-invoice.print-local', $inv) }}"
                                        data-invoice-id="{{ $inv->formattedId() }}"
                                        data-customer="{{ optional($inv->customer)->name ?? 'Unknown' }}"
                                    >

                                        <i class="fa-solid fa-print mr-1"></i>

                                        Print

                                    </a>


                                    <!-- Admin Actions -->
                                    @if(auth()->check() && auth()->user()->isAdmin())

                                        <!-- Edit -->
                                        <a
                                            href="{{ route('sale-invoice.edit', $inv) }}"
                                            class="inline-flex px-2 py-1
                                                   bg-amber-50
                                                   text-amber-700
                                                   rounded text-xs"
                                        >

                                            <i class="fa-solid fa-pen mr-1"></i>

                                            Edit

                                        </a>


                                        <!-- Delete -->
                                        <button
                                            @click="
                                                deletingId={{ $inv->id }};
                                                deletingLabel='{{ $inv->formattedId() }}';
                                                showDelete=true
                                            "
                                            class="inline-flex px-2 py-1
                                                   bg-red-50
                                                   text-red-700
                                                   rounded text-xs"
                                        >

                                            <i class="fa-solid fa-trash mr-1"></i>

                                            Delete

                                        </button>

                                    @endif

                                </td>

                            </tr>


                        @empty

                            <tr>

                                <td
                                    colspan="9"
                                    class="px-4 py-6 text-center
                                           text-sm text-gray-500"
                                >

                                    No invoices yet.

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>


        <!-- Pagination -->
        <div class="mt-4">

            {{ $invoices->links() }}

        </div>


        <!-- Delete Modal -->
        <div
            x-show="showDelete"
            x-cloak
            class="fixed inset-0 z-50 flex items-center
                   justify-center p-4"
        >

            <!-- Background -->
            <div
                class="absolute inset-0 bg-black/40"
                @click="showDelete=false"
            >
            </div>


            <!-- Modal -->
            <div
                class="bg-white dark:bg-[#0b0e14]
                       rounded-lg shadow-xl w-full max-w-md
                       z-50 overflow-hidden"
            >

                <!-- Modal Header -->
                <div class="px-6 py-4 border-b">

                    <h3 class="font-medium">
                        Delete Invoice
                    </h3>

                </div>


                <!-- Modal Body -->
                <div class="px-6 py-4 text-sm">

                    Delete invoice

                    <span
                        x-text="deletingLabel"
                        class="font-mono"
                    ></span>?

                    Inventory will be reversed.

                </div>


                <!-- Modal Footer -->
                <div
                    class="px-6 py-4 border-t
                           flex justify-end space-x-2"
                >

                    <!-- Cancel -->
                    <button
                        @click="showDelete=false"
                        class="px-4 py-2
                               bg-gray-100
                               dark:bg-[#11151c]
                               rounded"
                    >

                        Cancel

                    </button>


                    <!-- Delete Form -->
                    <form
                        :action="'/sale-invoice/' + deletingId"
                        method="POST"
                        class="inline"
                    >

                        @csrf

                        @method('DELETE')


                        <button
                            type="submit"
                            class="px-4 py-2
                                   bg-red-600
                                   text-white
                                   rounded"
                        >

                            Yes, Delete

                        </button>

                    </form>

                </div>

            </div>

        </div>

    </div>


    <!-- Sale Search Script -->
    <script>

        function saleSearch() {

            return {

                query: '{{ $q ?? '' }}',

                search() {

                    const url =
                        new URL(window.location.href);

                    url.searchParams.set(
                        'q',
                        this.query
                    );

                    url.searchParams.delete('page');

                    window.location.href =
                        url.toString();

                },


                clearSearch() {

                    this.query = '';

                    this.search();

                }

            }

        }

    </script>


    <!-- Local File Manager -->
    <script src="{{ asset('js/local-file-manager.js') }}"></script>


    <!-- Local PDF Save + Print -->
    <script>

        document.addEventListener(
            "DOMContentLoaded",
            () => {

                /*
                |--------------------------------------------------------------------------
                | Find all local print buttons
                |--------------------------------------------------------------------------
                */

                document
                    .querySelectorAll(".local-print-btn")
                    .forEach(button => {


                        button.addEventListener(
                            "click",
                            async (event) => {

                                /*
                                |--------------------------------------------------------------------------
                                | Stop normal link
                                |--------------------------------------------------------------------------
                                */

                                event.preventDefault();


                                try {

                                    /*
                                    |--------------------------------------------------------------------------
                                    | Disable button
                                    |--------------------------------------------------------------------------
                                    */

                                    button.disabled = true;


                                    const originalText =
                                        button.innerHTML;


                                    button.innerHTML =
                                        '<i class="fa-solid fa-spinner fa-spin mr-1"></i> Saving...';


                                    /*
                                    |--------------------------------------------------------------------------
                                    | Invoice information
                                    |--------------------------------------------------------------------------
                                    */

                                    const printUrl =
                                        button.href;


                                    const localPdfUrl =
                                        button.dataset.url;


                                    const invoiceId =
                                        button.dataset.invoiceId;


                                    const customerName =
                                        button.dataset.customer ||
                                        "Unknown";


                                    /*
                                    |--------------------------------------------------------------------------
                                    | Request PDF from Laravel
                                    |--------------------------------------------------------------------------
                                    */

                                    const response =
                                        await fetch(
                                            localPdfUrl,
                                            {
                                                method: "GET",

                                                headers: {
                                                    "Accept":
                                                        "application/pdf"
                                                }
                                            }
                                        );


                                    /*
                                    |--------------------------------------------------------------------------
                                    | Check response
                                    |--------------------------------------------------------------------------
                                    */

                                    if (!response.ok) {

                                        throw new Error(
                                            `PDF request failed: ${response.status}`
                                        );

                                    }


                                    /*
                                    |--------------------------------------------------------------------------
                                    | Convert response to Blob
                                    |--------------------------------------------------------------------------
                                    */

                                    const blob =
                                        await response.blob();


                                    /*
                                    |--------------------------------------------------------------------------
                                    | Make customer name safe
                                    |--------------------------------------------------------------------------
                                    */

                                    const safeCustomerName =
                                        customerName
                                            .trim()
                                            .replace(
                                                /[<>:"/\\|?*]/g,
                                                "_"
                                            );


                                    /*
                                    |--------------------------------------------------------------------------
                                    | Get today's date
                                    |--------------------------------------------------------------------------
                                    */

                                    const today =
                                        new Date()
                                            .toISOString()
                                            .slice(0, 10);


                                    /*
                                    |--------------------------------------------------------------------------
                                    | Create filename
                                    |--------------------------------------------------------------------------
                                    */

                                    const filename =
                                        `${invoiceId}_${safeCustomerName}_${today}.pdf`;


                                    /*
                                    |--------------------------------------------------------------------------
                                    | Save PDF locally
                                    |--------------------------------------------------------------------------
                                    */

                                    await POSFileManager.saveBlob(
                                        filename,
                                        blob,
                                        [
                                            "Sale Invoice",
                                            safeCustomerName
                                        ]
                                    );


                                    /*
                                    |--------------------------------------------------------------------------
                                    | Success message
                                    |--------------------------------------------------------------------------
                                    */

                                    alert(
                                        `Invoice saved successfully!\n\n${filename}`
                                    );


                                    /*
                                    |--------------------------------------------------------------------------
                                    | Restore button
                                    |--------------------------------------------------------------------------
                                    */

                                    button.innerHTML =
                                        originalText;


                                    button.disabled =
                                        false;


                                    /*
                                    |--------------------------------------------------------------------------
                                    | Open normal print page
                                    |--------------------------------------------------------------------------
                                    */

                                    window.open(
                                        printUrl,
                                        "_blank"
                                    );

                                }


                                catch (error) {

                                    /*
                                    |--------------------------------------------------------------------------
                                    | Log error
                                    |--------------------------------------------------------------------------
                                    */

                                    console.error(
                                        "Local PDF Save Error:",
                                        error
                                    );


                                    /*
                                    |--------------------------------------------------------------------------
                                    | Show error
                                    |--------------------------------------------------------------------------
                                    */

                                    alert(
                                        "Could not save invoice:\n\n" +
                                        error.message
                                    );


                                    /*
                                    |--------------------------------------------------------------------------
                                    | Re-enable button
                                    |--------------------------------------------------------------------------
                                    */

                                    button.disabled =
                                        false;


                                    /*
                                    |--------------------------------------------------------------------------
                                    | Restore button
                                    |--------------------------------------------------------------------------
                                    */

                                    button.innerHTML =
                                        '<i class="fa-solid fa-print mr-1"></i> Print';

                                }

                            }
                        );

                    });

            }
        );

    </script>

@endsection
```
