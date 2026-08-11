@extends('layouts.admin')

@section('title', 'Cashbook')

@section('content')
    <div class="max-w-7xl mx-auto" x-data="{ showOpeningModal: false }">

        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-3">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Cashbook</h2>
                <p class="text-sm text-gray-500">Auto-generated read-only report (driven by Payments &amp; Sale Invoices)</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <form method="GET" action="{{ route('cashbook') }}" class="flex items-center space-x-2 bg-white dark:bg-[#11151c] border border-gray-200 dark:border-[#1f2937] rounded-lg px-3 py-2">
                    <input type="date" name="from" value="{{ $from }}" class="bg-transparent text-sm focus:outline-none">
                    <span class="text-gray-400">—</span>
                    <input type="date" name="to" value="{{ $to }}" class="bg-transparent text-sm focus:outline-none">
                    <button class="text-sm text-[#7c3aed] font-medium">Apply</button>
                </form>
                //print button and local saving 
                <a href="{{ route('cashbook.print', ['from'=>$from,'to'=>$to]) }}"
   data-local-url="{{ route('cashbook.print-local', ['from'=>$from,'to'=>$to]) }}"
   data-from="{{ $from }}"
   data-to="{{ $to }}"
   class="cashbook-print-btn inline-flex items-center px-3 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded text-sm">

    <i class="fa-solid fa-print mr-2"></i>
    Print / PDF

</a>
                <!-- <a href="{{ route('cashbook.print', ['from'=>$from,'to'=>$to]) }}" target="_blank" class="inline-flex items-center px-3 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded text-sm">
                    <i class="fa-solid fa-print mr-2"></i> Print / PDF
                </a> -->
            </div>
        </div>

        @if(session('success'))<div class="mb-4 px-4 py-2 rounded-lg bg-green-50 text-green-700">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="mb-4 px-4 py-2 rounded-lg bg-red-50 text-red-700">{{ session('error') }}</div>@endif

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
            <div class="bg-white dark:bg-[#161b22] border border-gray-200 dark:border-[#222b38] rounded-xl p-4">
                <div class="text-xs text-gray-500">Opening Balance (for range)</div>
                <div class="text-xl font-bold">Rs {{ number_format($opening, 2) }}</div>
            </div>
            <div class="bg-white dark:bg-[#161b22] border border-gray-200 dark:border-[#222b38] rounded-xl p-4">
                <div class="text-xs text-gray-500">Total Cash In</div>
                <div class="text-xl font-bold text-emerald-600">Rs {{ number_format($totalIn, 2) }}</div>
            </div>
            <div class="bg-white dark:bg-[#161b22] border border-gray-200 dark:border-[#222b38] rounded-xl p-4">
                <div class="text-xs text-gray-500">Total Cash Out</div>
                <div class="text-xl font-bold text-rose-600">Rs {{ number_format($totalOut, 2) }}</div>
            </div>
            <div class="bg-white dark:bg-[#161b22] border border-gray-200 dark:border-[#222b38] rounded-xl p-4">
                <div class="text-xs text-gray-500">Closing Balance</div>
                <div class="text-xl font-bold {{ $closingBalance >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">Rs {{ number_format($closingBalance, 2) }}</div>
            </div>
        </div>

        <div class="bg-white dark:bg-[#0b0e14] border border-gray-200 dark:border-[#1f2937] rounded-lg p-4 shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm border border-gray-300 dark:border-[#222b38]">
                    <thead>
                        <tr class="bg-gray-900 text-white dark:bg-black">
                            <th class="px-3 py-2 text-left border border-gray-700 w-16">S.No</th>
                            <th class="px-3 py-2 text-left border border-gray-700">Date</th>
                            <th class="px-3 py-2 text-left border border-gray-700">Type</th>
                            <th class="px-3 py-2 text-left border border-gray-700">Description</th>
                            <th class="px-3 py-2 text-right border border-gray-700 w-32">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($entries as $index => $entry)
                            <tr class="border border-gray-200 dark:border-[#222b38] break-inside-avoid">
                                <td class="px-3 py-2 border border-gray-200 dark:border-[#222b38] align-top">{{ ($entries->currentPage()-1) * $entries->perPage() + $index + 1 }}</td>
                                <td class="px-3 py-2 border border-gray-200 dark:border-[#222b38] align-top whitespace-nowrap">{{ $entry['date'] }}</td>
                                <td class="px-3 py-2 border border-gray-200 dark:border-[#222b38] align-top">{{ $entry['type'] }}</td>
                                <td class="px-3 py-2 border border-gray-200 dark:border-[#222b38] break-words whitespace-normal align-top">{{ $entry['description'] }}</td>
                                <td class="px-3 py-2 text-right border border-gray-200 dark:border-[#222b38] align-top">Rs {{ number_format($entry['amount'], 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">No cashbook records found for this period.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="mt-3">{{ $entries->links() }}</div>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/local-file-manager.js') }}"></script>

<script>
document.addEventListener("DOMContentLoaded", () => {

    document.querySelectorAll(".cashbook-print-btn").forEach(button => {

        button.addEventListener("click", async (event) => {

            event.preventDefault();

            const originalText = button.innerHTML;

            try {

                button.style.pointerEvents = "none";

                button.innerHTML =
                    '<i class="fa-solid fa-spinner fa-spin mr-2"></i> Saving...';

                const localPdfUrl = button.dataset.localUrl;
                const from = button.dataset.from;
                const to = button.dataset.to;

                if (!localPdfUrl) {
                    throw new Error("Cashbook PDF URL is missing.");
                }

                /*
                 * Get root folder first because showDirectoryPicker()
                 * requires user activation.
                 */
                await POSFileManager.getRootHandle();

                /*
                 * Fetch Cashbook PDF
                 */
                const response = await fetch(localPdfUrl, {
                    method: "GET",
                    headers: {
                        "Accept": "application/pdf"
                    }
                });

                if (!response.ok) {
                    throw new Error(
                        `PDF request failed: ${response.status}`
                    );
                }

                const blob = await response.blob();

                if (!blob || blob.size === 0) {
                    throw new Error("The generated Cashbook PDF is empty.");
                }

                /*
                 * Filename
                 */
                const filename =
                    `Cashbook_${from}_to_${to}.pdf`;

                /*
                 * Save:
                 *
                 * Documents
                 *    └── date
                 *         └── Cashbook
                 *              └── Cashbook_....pdf
                 */
                await POSFileManager.saveBlob(
                    filename,
                    blob,
                    [
                        "Cashbook"
                    ]
                );

                alert(
                    `Cashbook saved successfully!\n\n${filename}`
                );

                /*
                 * ALSO open the normal Print/PDF
                 */
                window.open(button.href, "_blank");

                button.innerHTML = originalText;
                button.style.pointerEvents = "";

            } catch (error) {

                console.error(
                    "Cashbook Local PDF Error:",
                    error
                );

                alert(
                    "Could not save Cashbook:\n\n" +
                    error.message
                );

                button.innerHTML = originalText;
                button.style.pointerEvents = "";
            }
        });
    });
});
</script>
@endsection
