@extends('layouts.admin')

@section('title', 'Balance Sheet')

@section('content')
    <div class="max-w-5xl mx-auto">

        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-3">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Balance Sheet</h2>
                <p class="text-sm text-gray-500">Closing balance of every customer (Debit / Credit)</p>
            </div>

            <div class="flex items-center gap-2">
                <form method="GET" action="{{ route('balance-sheet') }}" class="flex items-center gap-2">
                    <div class="relative">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                        <input type="text" name="q" value="{{ $q }}" placeholder="Search customer..."
                               class="pl-9 pr-3 py-2 border rounded-lg text-sm bg-white dark:bg-[#081118] border-gray-200 dark:border-[#1a202c] w-56">
                    </div>
                    <button class="px-4 py-2 bg-[#7c3aed] text-white rounded-lg text-sm">Search</button>
                    @if($q !== '')
                        <a href="{{ route('balance-sheet') }}" class="px-3 py-2 bg-gray-100 dark:bg-[#11151c] rounded-lg text-sm">Clear</a>
                    @endif
                </form>

                <a href="{{ route('balance-sheet.print', ['q' => $q]) }}" target="_blank"
                   class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg shadow text-sm">
                    <i class="fa-solid fa-print mr-2"></i> Print
                </a>
            </div>
        </div>

        <div class="bg-white dark:bg-[#0b0e14] border border-gray-200 dark:border-[#1f2937] rounded-lg p-4 shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm border border-gray-300 dark:border-[#222b38]">
                    <thead>
                        <tr class="bg-gray-900 text-white dark:bg-black">
                            <th class="px-3 py-2 text-left border border-gray-700 w-16">S.No</th>
                            <th class="px-3 py-2 text-left border border-gray-700">Name</th>
                            <th class="px-3 py-2 text-right border border-gray-700 w-40">Debit</th>
                            <th class="px-3 py-2 text-right border border-gray-700 w-40">Credit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $i => $r)
                            <tr class="border border-gray-200 dark:border-[#222b38]">
                                <td class="px-3 py-2 border border-gray-200 dark:border-[#222b38]">{{ $startIndex + $i + 1 }}</td>
                                <td class="px-3 py-2 border border-gray-200 dark:border-[#222b38]">{{ $r['name'] }}</td>
                                <td class="px-3 py-2 text-right border border-gray-200 dark:border-[#222b38]">{{ $r['debit'] > 0 ? number_format($r['debit'], 2) : '-' }}</td>
                                <td class="px-3 py-2 text-right border border-gray-200 dark:border-[#222b38]">{{ $r['credit'] > 0 ? number_format($r['credit'], 2) : '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-3 py-6 text-center text-gray-500">No customers found.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray-100 dark:bg-[#0f1220] font-bold">
                            <td class="px-3 py-2 border border-gray-300 dark:border-[#222b38]" colspan="2">Total</td>
                            <td class="px-3 py-2 text-right border border-gray-300 dark:border-[#222b38]">{{ number_format($totalDebit, 2) }}</td>
                            <td class="px-3 py-2 text-right border border-gray-300 dark:border-[#222b38]">{{ number_format($totalCredit, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="mt-4">{{ $rows->withQueryString()->links() }}</div>
            <p class="text-xs text-gray-400 mt-2">Note: Total Debit &amp; Credit shown above are for <strong>all</strong> matching customers, not just this page.</p>
        </div>
    </div>
@endsection
