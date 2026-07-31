@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    @if(auth()->check() && auth()->user()->isAdmin())
        <div x-data="dashboard({{ json_encode($stats) }}, '{{ $from }}', '{{ $to }}')" class="max-w-7xl mx-auto">

            <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-3">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Dashboard</h2>
                    <p class="text-sm text-gray-500">Financial summary for the selected period</p>
                </div>

                <div class="flex items-center space-x-2 bg-white dark:bg-[#11151c] border border-gray-200 dark:border-[#1f2937] rounded-lg px-3 py-2">
                    <i class="fa-regular fa-calendar text-gray-400"></i>
                    <input type="date" x-model="from" @change="reload()" class="bg-transparent text-sm focus:outline-none text-gray-700 dark:text-gray-200">
                    <span class="text-gray-400">—</span>
                    <input type="date" x-model="to" @change="reload()" class="bg-transparent text-sm focus:outline-none text-gray-700 dark:text-gray-200">
                    <button @click="resetMonth()" class="text-xs text-[#7c3aed] hover:underline ml-2">This Month</button>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

                <div class="bg-white dark:bg-[#161b22] border border-gray-200 dark:border-[#222b38] rounded-xl p-5 shadow-sm">
                    <div class="flex items-center text-gray-500 dark:text-gray-400 text-sm font-medium mb-2">
                        <i class="fa-solid fa-hand-holding-dollar mr-2 text-emerald-500"></i>
                        Total Payments Received
                    </div>
                    <div class="text-2xl font-bold text-gray-800 dark:text-white">Rs <span x-text="formatMoney(stats.total_payments_received)"></span></div>
                    <div class="text-xs text-gray-400 mt-1">Sum of Cash In</div>
                </div>

                <div class="bg-white dark:bg-[#161b22] border border-gray-200 dark:border-[#222b38] rounded-xl p-5 shadow-sm">
                    <div class="flex items-center text-gray-500 dark:text-gray-400 text-sm font-medium mb-2">
                        <i class="fa-solid fa-truck-ramp-box mr-2 text-amber-500"></i>
                        Total Loading
                    </div>
                    <div class="text-2xl font-bold text-gray-800 dark:text-white">Rs <span x-text="formatMoney(stats.total_loading)"></span></div>
                    <div class="text-xs text-gray-400 mt-1">From sale invoices</div>
                </div>

                <div class="bg-white dark:bg-[#161b22] border border-gray-200 dark:border-[#222b38] rounded-xl p-5 shadow-sm">
                    <div class="flex items-center text-gray-500 dark:text-gray-400 text-sm font-medium mb-2">
                        <i class="fa-solid fa-people-carry-box mr-2 text-rose-500"></i>
                        Total Labour Cost
                    </div>
                    <div class="text-2xl font-bold text-gray-800 dark:text-white">Rs <span x-text="formatMoney(stats.total_labour_cost)"></span></div>
                    <div class="text-xs text-gray-400 mt-1">From sale invoices</div>
                </div>

                <div class="bg-white dark:bg-[#161b22] border border-gray-200 dark:border-[#222b38] rounded-xl p-5 shadow-sm">
                    <div class="flex items-center text-gray-500 dark:text-gray-400 text-sm font-medium mb-2">
                        <i class="fa-solid fa-file-invoice-dollar mr-2 text-indigo-500"></i>
                        Total Sale
                    </div>
                    <div class="text-2xl font-bold text-gray-800 dark:text-white">Rs <span x-text="formatMoney(stats.total_sale)"></span></div>
                    <div class="text-xs text-gray-400 mt-1">Sum of bill totals</div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white dark:bg-[#161b22] border border-gray-200 dark:border-[#222b38] rounded-xl p-5 shadow-sm">
                    <div class="flex items-center text-gray-500 dark:text-gray-400 text-sm font-medium mb-2">
                        <i class="fa-solid fa-weight-hanging mr-2 text-sky-500"></i>
                        Total Weight
                    </div>
                    <div class="text-2xl font-bold text-gray-800 dark:text-white"><span x-text="formatMoney(stats.total_weight)"></span> Kg</div>
                    <div class="text-xs text-gray-400 mt-1">Sum of all sale items weight</div>
                </div>

                <div class="bg-white dark:bg-[#161b22] border border-gray-200 dark:border-[#222b38] rounded-xl p-5 shadow-sm">
                    <div class="flex items-center text-gray-500 dark:text-gray-400 text-sm font-medium mb-2">
                        <i class="fa-solid fa-money-bill-trend-up mr-2 text-red-500"></i>
                        Total Expenses
                    </div>
                    <div class="text-2xl font-bold text-gray-800 dark:text-white">Rs <span x-text="formatMoney(stats.total_expenses)"></span></div>
                    <div class="text-xs text-gray-400 mt-1">Sum of Expenses</div>
                </div>
            </div>

        </div>
    @endif

    <script>
        function dashboard(initialStats, from, to) {
            return {
                stats: initialStats,
                from: from,
                to: to,
                formatMoney(v) {
                    if (v == null) return '0';
                    return Number(v).toLocaleString('en-PK', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                },
                resetMonth() {
                    const d = new Date();
                    this.from = new Date(d.getFullYear(), d.getMonth(), 1).toISOString().slice(0,10);
                    this.to   = d.toISOString().slice(0,10);
                    this.reload();
                },
                async reload() {
                    if (!this.from || !this.to) return;
                    try {
                        const url = `{{ route('dashboard') }}?from=${this.from}&to=${this.to}`;
                        const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                        if (res.ok) {
                            const data = await res.json();
                            this.stats = data;
                            sessionStorage.setItem('pos_dash_from', this.from);
                            sessionStorage.setItem('pos_dash_to',   this.to);
                        }
                    } catch (e) { console.error(e); }
                },
                init() {
                    const f = sessionStorage.getItem('pos_dash_from');
                    const t = sessionStorage.getItem('pos_dash_to');
                    if (f && t) { this.from = f; this.to = t; this.reload(); }
                }
            }
        }
    </script>
@endsection
