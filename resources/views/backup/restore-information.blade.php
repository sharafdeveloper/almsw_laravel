@extends('layouts.admin')
 
@section('title', 'Backup Information')
 
@section('content')
    <div class="max-w-7xl">
 
        <!-- Page Heading -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-3">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Backup Information</h2>
                <p class="text-sm text-gray-500">Review backup details before restoring database</p>
            </div>
        </div>
 
        <!-- Backup Details -->
        <div class="bg-white dark:bg-[#161b22] border border-gray-200 dark:border-[#222b38] rounded-xl p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-white mb-5">Database Backup Details</h2>
 
            <div class="divide-y divide-gray-100 dark:divide-[#11151c]">
 
                <div class="flex items-center justify-between py-3">
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Database</span>
                    <span class="text-sm text-gray-800 dark:text-gray-200">{{ $result['database'] }}</span>
                </div>
 
                <div class="flex items-center justify-between py-3">
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Server</span>
                    <span class="text-sm text-gray-800 dark:text-gray-200">{{ $result['server'] }}</span>
                </div>
 
                <div class="flex items-center justify-between py-3">
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Backup Size</span>
                    <span class="text-sm text-gray-800 dark:text-gray-200">{{ $result['size'] }}</span>
                </div>
 
                <div class="flex items-center justify-between py-3">
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Tables</span>
                    <span class="text-sm text-gray-800 dark:text-gray-200">{{ $result['tables'] }}</span>
                </div>
 
                <div class="flex items-center justify-between py-3">
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Insert Statements</span>
                    <span class="text-sm text-gray-800 dark:text-gray-200">{{ $result['records'] }}</span>
                </div>
 
                <div class="flex items-center justify-between py-3">
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</span>
                    @if($result['status'])
                        <span class="px-2.5 py-1 rounded-full text-xs bg-emerald-50 text-emerald-700">
                            <i class="fa-solid fa-check mr-1"></i> Valid Backup
                        </span>
                    @else
                        <span class="px-2.5 py-1 rounded-full text-xs bg-red-50 text-red-700">
                            <i class="fa-solid fa-xmark mr-1"></i> Invalid Backup
                        </span>
                    @endif
                </div>
 
            </div>
 
            <!-- Actions -->
            <form method="POST" action="{{ route('backup.restore.execute') }}" class="mt-6 flex items-center gap-3">
                @csrf
 
                <input type="hidden" name="database" value="{{ $result['database'] }}">
                <input type="hidden" name="backup_path" value="{{ $result['backup_path'] }}">
                <input type="hidden" name="server" value="{{ $result['server'] }}">
                <input type="hidden" name="size" value="{{ $result['size'] }}">
                <input type="hidden" name="tables" value="{{ $result['tables'] }}">
                <input type="hidden" name="records" value="{{ $result['records'] }}">
 
                <button type="submit"
                        class="inline-flex items-center px-5 py-2 bg-red-600 hover:bg-red-700 text-white text-sm rounded-lg shadow">
                    <i class="fa-solid fa-rotate-left mr-2"></i> Restore & Download Backup
                </button>
 
                <a href="{{ route('backup.index') }}"
                   class="inline-flex items-center px-5 py-2 bg-gray-100 dark:bg-[#11151c] text-gray-700 dark:text-gray-200 text-sm rounded-lg">
                    Cancel
                </a>
            </form>
 
        </div>
 
    </div>
@endsection