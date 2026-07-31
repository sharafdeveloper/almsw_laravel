<!DOCTYPE html>
<html lang="en" x-data="{ theme: 'dark', isCollapsed: window.innerWidth < 768 }" :class="theme === 'dark' ? 'dark' : ''">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'AL-MAKKAH-STEEL WORKS')</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class', // Dark mode class-based enable kar diya
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 5px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        .dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #374151; }
        
        /* Smooth transitions for sidebar */
        aside, .nav-text { transition: all 0.3s ease-in-out; }
    </style>
</head>
<body class="bg-gray-50 dark:bg-[#0b0e14] text-gray-800 dark:text-white h-screen flex font-sans overflow-hidden transition-colors duration-200">

    <aside :class="isCollapsed ? 'w-[70px]' : 'w-[250px]'" class="bg-white dark:bg-[#11151c] border-r border-gray-200 dark:border-[#1f2937] flex flex-col flex-shrink-0 z-20 absolute md:relative h-full">
        <div class="h-16 flex items-center justify-between px-4 border-b border-gray-200 dark:border-[#1f2937]">
            <div class="flex items-center space-x-3 overflow-hidden whitespace-nowrap" x-show="!isCollapsed">
                <i class="fa-solid fa-store text-[#7c3aed] text-xl"></i>
                <span class="text-[#7c3aed] text-lg font-bold tracking-wide">AL-MAKKAH-STEEL <BR> WORKS</span>
            </div>
            <button @click="isCollapsed = !isCollapsed" class="text-gray-400 hover:text-gray-800 dark:hover:text-white focus:outline-none mx-auto" :class="isCollapsed ? 'rotate-180' : ''">
                <i class="fa-solid fa-chevron-left"></i>
            </button>
        </div>

        <nav class="flex-1 overflow-y-auto py-4 custom-scrollbar">
            <a href="{{ route('dashboard') }}" @click="isCollapsed = false" class="{{ request()->routeIs('dashboard') ? 'flex items-center px-4 py-3 mx-2 rounded-lg bg-gradient-to-r from-indigo-50 to-purple-50 dark:bg-[#0f1220] text-[#7c3aed] mb-2' : 'flex items-center px-4 py-3 mx-2 rounded-lg text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-[#0f1220] hover:text-gray-800 transition-colors mb-2' }}">
                <div class="w-6 flex justify-center"><i class="fa-solid fa-house text-lg"></i></div>
                <span class="ml-3 font-medium nav-text whitespace-nowrap" x-show="!isCollapsed">Dashboard</span>
            </a>

            <div x-show="!isCollapsed" class="px-6 mt-6 mb-2 flex items-center justify-between text-xs font-bold text-gray-400 uppercase tracking-wider">
                <span>Catalog</span>
            </div>

            @if(auth()->check() && auth()->user()->isAdmin())
                <a href="{{ route('products') }}" @click="isCollapsed = false" class="{{ request()->routeIs('products') ? 'flex items-center px-4 py-2.5 mx-2 rounded-lg bg-gradient-to-r from-indigo-50 to-purple-50 dark:bg-[#0f1220] text-[#7c3aed]' : 'flex items-center px-4 py-2.5 mx-2 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-[#1a202c] hover:text-gray-800 dark:hover:text-white transition-colors' }}">
                    <div class="w-6 flex justify-center"><i class="fa-solid fa-box text-lg"></i></div>
                    <span class="ml-3 font-medium text-sm nav-text whitespace-nowrap" x-show="!isCollapsed">Products</span>
                </a>
                <a href="{{ route('inventory') }}" @click="isCollapsed = false" class="{{ request()->routeIs('inventory') ? 'flex items-center px-4 py-2.5 mx-2 rounded-lg bg-gradient-to-r from-indigo-50 to-purple-50 dark:bg-[#0f1220] text-[#7c3aed]' : 'flex items-center px-4 py-2.5 mx-2 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-[#1a202c] hover:text-gray-800 dark:hover:text-white transition-colors' }}">
                    <div class="w-6 flex justify-center"><i class="fa-solid fa-boxes-stacked text-lg"></i></div>
                    <span class="ml-3 font-medium text-sm nav-text whitespace-nowrap" x-show="!isCollapsed">Inventory</span>
                </a>
            @endif
            
            {{-- Cashbook visible to Admin and Employee --}}
            @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isEmployee()))
                <a href="{{ route('cashbook') }}" @click="isCollapsed = false" class="{{ request()->routeIs('cashbook') ? 'flex items-center px-4 py-2.5 mx-2 rounded-lg bg-gradient-to-r from-indigo-50 to-purple-50 dark:bg-[#0f1220] text-[#7c3aed]' : 'flex items-center px-4 py-2.5 mx-2 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-[#1a202c] hover:text-gray-800 dark:hover:text-white transition-colors' }}">
                    <div class="w-6 flex justify-center"><i class="fa-solid fa-book text-lg"></i></div>
                    <span class="ml-3 font-medium text-sm nav-text whitespace-nowrap" x-show="!isCollapsed">Cashbook</span>
                </a>
            @endif

            {{-- Customers visible to Admin and Employee --}}
            @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isEmployee()))
                <a href="{{ route('customers') }}" @click="isCollapsed = false" class="{{ request()->routeIs('customers') ? 'flex items-center px-4 py-2.5 mx-2 rounded-lg bg-gradient-to-r from-indigo-50 to-purple-50 dark:bg-[#0f1220] text-[#7c3aed]' : 'flex items-center px-4 py-2.5 mx-2 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-[#1a202c] hover:text-gray-800 dark:hover:text-white transition-colors' }}">
                    <div class="w-6 flex justify-center"><i class="fa-solid fa-user-group text-lg"></i></div>
                    <span class="ml-3 font-medium text-sm nav-text whitespace-nowrap" x-show="!isCollapsed">Customers</span>
                </a>
            @endif

            <div x-show="!isCollapsed" class="px-6 mt-6 mb-2 flex items-center justify-between text-xs font-bold text-gray-400 uppercase tracking-wider">
                <span>Sales</span>
            </div>

            @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isEmployee()))
                <a href="{{ route('sale-invoice') }}" @click="isCollapsed = false" class="{{ request()->routeIs('sale-invoice') ? 'flex items-center px-4 py-2.5 mx-2 rounded-lg bg-gradient-to-r from-indigo-50 to-purple-50 dark:bg-[#0f1220] text-[#7c3aed]' : 'flex items-center px-4 py-2.5 mx-2 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-[#1a202c] hover:text-gray-800 dark:hover:text-white transition-colors' }}">
                    <div class="w-6 flex justify-center"><i class="fa-solid fa-file-invoice text-lg"></i></div>
                    <span class="ml-3 font-medium text-sm nav-text whitespace-nowrap" x-show="!isCollapsed">Sale Invoice</span>
                </a>
            @endif

            @if(auth()->check() && auth()->user()->isAdmin())
                <a href="{{ route('purchase-invoice') }}" @click="isCollapsed = false" class="{{ request()->routeIs('purchase-invoice') ? 'flex items-center px-4 py-2.5 mx-2 rounded-lg bg-gradient-to-r from-indigo-50 to-purple-50 dark:bg-[#0f1220] text-[#7c3aed]' : 'flex items-center px-4 py-2.5 mx-2 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-[#1a202c] hover:text-gray-800 dark:hover:text-white transition-colors' }}">
                    <div class="w-6 flex justify-center"><i class="fa-solid fa-receipt text-lg"></i></div>
                    <span class="ml-3 font-medium text-sm nav-text whitespace-nowrap" x-show="!isCollapsed">Purchase Invoice</span>
                </a>
            @endif

            @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isEmployee()))
                <a href="{{ route('payments') }}" @click="isCollapsed = false" class="{{ request()->routeIs('payments') ? 'flex items-center px-4 py-2.5 mx-2 rounded-lg bg-gradient-to-r from-indigo-50 to-purple-50 dark:bg-[#0f1220] text-[#7c3aed]' : 'flex items-center px-4 py-2.5 mx-2 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-[#1a202c] hover:text-gray-800 dark:hover:text-white transition-colors' }}">
                    <div class="w-6 flex justify-center"><i class="fa-solid fa-money-bill-transfer text-lg"></i></div>
                    <span class="ml-3 font-medium text-sm nav-text whitespace-nowrap" x-show="!isCollapsed">Payments</span>
                </a>
                @endif
                
            @if(auth()->check() && auth()->user()->isAdmin())
                <a href="{{ route('balance-sheet') }}" @click="isCollapsed = false" class="{{ request()->routeIs('balance-sheet') ? 'flex items-center px-4 py-2.5 mx-2 rounded-lg bg-gradient-to-r from-indigo-50 to-purple-50 dark:bg-[#0f1220] text-[#7c3aed]' : 'flex items-center px-4 py-2.5 mx-2 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-[#1a202c] hover:text-gray-800 dark:hover:text-white transition-colors' }}">
                    <div class="w-6 flex justify-center"><i class="fa-solid fa-scale-balanced text-lg"></i></div>
                    <span class="ml-3 font-medium text-sm nav-text whitespace-nowrap" x-show="!isCollapsed">Balance Sheet</span>
                </a>
            
            <a href="{{ route('backup.index') }}" @click="isCollapsed = false"
            class="{{ request()->routeIs('backup.index') ? 'flex items-center px-4 py-2.5 mx-2 rounded-lg bg-gradient-to-r from-indigo-50 to-purple-50 dark:bg-[#0f1220] text-[#7c3aed]' : 'flex items-center px-4 py-2.5 mx-2 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-[#1a202c] hover:text-gray-800 dark:hover:text-white transition-colors' }}">
            <div class="w-6 flex justify-center">
            <i class="fa-solid fa-database text-lg"></i>
            </div>
            <span class="ml-3 font-medium text-sm nav-text whitespace-nowrap" x-show="!isCollapsed">
            Backup & Restore
            </span>
            </a>
            
            @endif


        </nav>
    </aside>

    <div class="flex-1 flex flex-col h-screen overflow-hidden" :class="!isCollapsed && window.innerWidth < 768 ? 'opacity-50 pointer-events-none' : ''">
        
        <header class="h-16 flex items-center justify-between px-6 border-b border-gray-200 dark:border-[#1f2937] bg-white dark:bg-[#0b0e14] transition-colors duration-200">
            <div>
                <button @click="isCollapsed = !isCollapsed" class="md:hidden text-gray-500 text-xl focus:outline-none">
                    <i class="fa-solid fa-bars"></i>
                </button>
            </div> 
            
            <div class="flex items-center space-x-4">
                {{-- <div class="relative hidden sm:block">
                    <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                    <input type="text" placeholder="Search" 
                        class="bg-gray-100 dark:bg-[#181d26] border border-gray-200 dark:border-[#2d3748] text-gray-700 dark:text-gray-300 text-sm rounded-full pl-10 pr-4 py-1.5 focus:outline-none focus:border-[#7c3aed] focus:ring-1 focus:ring-[#7c3aed] w-[200px] md:w-[280px] transition-all">
                </div> --}}
                
                <div x-data="{ profileOpen: false }" class="relative">
                    <button @click="profileOpen = !profileOpen" @click.away="profileOpen = false" class="w-9 h-9 rounded-full bg-gray-800 dark:bg-black flex items-center justify-center text-sm font-bold text-white border-2 border-transparent hover:border-[#7c3aed] focus:outline-none transition-colors">
                        {{ strtoupper(substr(auth()->user()->name ?? 'SA', 0, 2)) }}
                    </button>

                    <div x-show="profileOpen" style="display: none;" 
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-64 bg-white dark:bg-[#161b22] border border-gray-200 dark:border-[#2d3748] rounded-xl shadow-xl z-50 overflow-hidden">
                        
                        <div class="px-4 py-3 border-b border-gray-200 dark:border-[#2d3748] flex items-center space-x-3">
                            <i class="fa-regular fa-circle-user text-xl text-gray-400"></i>
                            <div>
                                <p class="text-sm font-medium text-gray-800 dark:text-white">Super Admin</p>
                            </div>
                        </div>

                        {{-- <div class="p-3 border-b border-gray-200 dark:border-[#2d3748] flex justify-center space-x-2">
                            <button @click="theme = 'light'" :class="theme === 'light' ? 'bg-gray-200 text-gray-800' : 'text-gray-400 hover:text-white hover:bg-[#1f2937]'" class="p-2 rounded-lg transition-colors w-1/3 flex justify-center">
                                <i class="fa-regular fa-sun"></i>
                            </button>
                            <button @click="theme = 'dark'" :class="theme === 'dark' ? 'bg-[#1f2937] text-[#7c3aed]' : 'text-gray-400 hover:text-white hover:bg-[#1f2937]'" class="p-2 rounded-lg transition-colors w-1/3 flex justify-center">
                                <i class="fa-regular fa-moon"></i>
                            </button>
                        </div> --}}

                        <form method="POST" action="{{ route('logout') ?? '#' }}" class="p-2">
                            @csrf
                            <button type="submit" class="w-full text-left px-3 py-2 text-sm text-red-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-[#1f2937] hover:dark:text-white rounded-lg transition-colors flex items-center space-x-2">
                                <i class="fa-solid fa-arrow-right-from-bracket"></i>
                                <span>Sign out</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-6 md:p-8 bg-gray-50 dark:bg-[#0b0e14] transition-colors duration-200">
            @yield('content')
        </main>
        
    </div>
</body>
</html>