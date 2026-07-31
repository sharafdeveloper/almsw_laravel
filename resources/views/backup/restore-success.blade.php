@extends('layouts.admin')
 
@section('title', 'Database Restore')
 
@section('content')
    <div class="max-w-7xl">
 
        <!-- Page Heading -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-3">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Database Restore Completed</h2>
                <p class="text-sm text-gray-500">Your database has been restored successfully</p>
            </div>
        </div>
 
        <div class="bg-white dark:bg-[#161b22] border border-gray-200 dark:border-[#222b38] rounded-xl p-6 shadow-sm">
 
            <!-- Success Banner -->
            <div class="flex items-center px-4 py-3 rounded-lg bg-green-50 mb-6">
                <i class="fa-solid fa-circle-check text-emerald-500 text-xl mr-3"></i>
                <div>
                    <h3 class="font-semibold text-green-700 text-sm">Success!</h3>
                    <p class="text-green-600 text-sm">The uploaded database has been restored successfully.</p>
                </div>
            </div>
 
            <h2 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Restore Summary</h2>
 
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-[#222b38] border border-gray-200 dark:border-[#222b38] rounded-lg">
                    <tbody class="bg-white dark:bg-[#0b0e14] divide-y divide-gray-100 dark:divide-[#11151c]">
                        <tr>
                            <td class="px-5 py-4 text-sm font-semibold text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-[#0f1220] w-1/3">
                                Restore Status
                            </td>
                            <td class="px-5 py-4 text-sm">
                                <span class="px-2.5 py-1 rounded-full text-xs bg-emerald-50 text-emerald-700">
                                    <i class="fa-solid fa-check mr-1"></i> Completed Successfully
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="px-5 py-4 text-sm font-semibold text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-[#0f1220]">
                                Previous Database Backup
                            </td>
                            <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-300">
                                {{ basename($currentBackup) }}
                            </td>
                        </tr>
                        <tr>
                            <td class="px-5 py-4 text-sm font-semibold text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-[#0f1220]">
                                Completion Time
                            </td>
                            <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-300">
                                {{ now()->format('d M Y - h:i A') }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
 
            <div class="flex justify-end mt-6">
                <a href="{{ route('backup.index') }}"
                   class="inline-flex items-center px-5 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white text-sm rounded-lg shadow">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Back to Backup Module
                </a>
            </div>
 
        </div>
 
    </div>
@endsection