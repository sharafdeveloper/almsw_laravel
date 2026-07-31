@extends('layouts.admin')
 
@section('title', 'Backup & Restore')
 
@section('content')
    <div class="max-w-7xl">
 
        <!-- Page Heading -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-3">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Backup & Restore</h2>
                <p class="text-sm text-gray-500">Manage database and application backups securely</p>
            </div>
        </div>
 
        @if(session('success'))
            <div class="mb-4 px-4 py-2 rounded-lg bg-green-50 text-green-700">{{ session('success') }}</div>
        @endif
 
        @if ($errors->any())
            <div class="mb-4 px-4 py-3 rounded-lg bg-red-50 text-red-700">
                <ul class="list-disc list-inside space-y-1 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
 
        <!-- Backup Action Boxes -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
 
            <!-- Database Backup -->
            <div class="bg-white dark:bg-[#161b22] border border-gray-200 dark:border-[#222b38] rounded-xl p-5 shadow-sm">
                <div class="flex items-center text-gray-500 dark:text-gray-400 text-sm font-medium mb-2">
                    <i class="fa-solid fa-database mr-2 text-indigo-500"></i>
                    Database Backupwhy
                </div>
                <div class="text-sm text-gray-600 dark:text-gray-300">
                    Download a complete backup of your database.
                </div>
                <a href="{{ route('backup.database') }}"
                   class="inline-flex items-center mt-4 px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white text-sm rounded-lg shadow">
                    <i class="fa-solid fa-download mr-2"></i> Download Database Backup
                </a>
            </div>
 
            <!-- Full Application Backup -->
            <div class="bg-white dark:bg-[#161b22] border border-gray-200 dark:border-[#222b38] rounded-xl p-5 shadow-sm">
                <div class="flex items-center text-gray-500 dark:text-gray-400 text-sm font-medium mb-2">
                    <i class="fa-solid fa-folder-tree mr-2 text-emerald-500"></i>
                    Full Application Backup
                </div>
                <div class="text-sm text-gray-600 dark:text-gray-300">
                    Download the complete Laravel application including uploads.
                </div>
                <a href="{{ route('backup.full') }}"
                   class="inline-flex items-center mt-4 px-4 py-2 bg-gradient-to-r from-emerald-600 to-green-600 text-white text-sm rounded-lg shadow">
                    <i class="fa-solid fa-download mr-2"></i> Download Full Backup
                </a>
            </div>
 
        </div>
 
        <!-- Recent Backups -->
        <div class="bg-white dark:bg-[#0b0e14] border border-gray-200 dark:border-[#1f2937] rounded-lg p-4 shadow-sm mb-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Recent Backups</h2>
            </div>
 
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-[#222b38]">
                    <thead class="bg-gray-50 dark:bg-[#0f1220]">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Backup Name</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Size</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-[#0b0e14] divide-y divide-gray-100 dark:divide-[#11151c]">
                        @forelse($backups as $backup)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $backup->original_name }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                    <span class="px-2 py-0.5 rounded text-xs bg-indigo-50 text-indigo-700">
                                        {{ ucfirst($backup->type) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                    {{ number_format($backup->file_size / 1024 / 1024, 2) }} MB
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                    {{ $backup->created_at->format('d M Y h:i A') }}
                                </td>
                                <td class="px-4 py-3 text-sm text-right whitespace-nowrap">
                                    <a href="{{ route('backup.download', $backup->id) }}"
                                       class="inline-flex items-center px-3 py-1.5 bg-emerald-50 text-emerald-700 rounded hover:bg-emerald-100">
                                        <i class="fa-solid fa-download mr-1"></i> Download
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-500">No backups available.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
 
        <!-- Today's Automatic Backup Status -->
        <div class="bg-white dark:bg-[#0b0e14] border border-gray-200 dark:border-[#1f2937] rounded-lg p-4 shadow-sm mb-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-white">
                    Today's Backup Status
                </h2>
                <span class="text-sm text-gray-500">
                    {{ now()->format('d M Y') }}
                </span>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-[#222b38]">
                    <thead class="bg-gray-50 dark:bg-[#0f1220]">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Backup Time
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Status
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Completed At
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-[#0b0e14] divide-y divide-gray-100 dark:divide-[#11151c]">
                        <!-- Morning Backup -->
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                Morning Backup (09:00 AM)
                            </td>
                            <td class="px-4 py-3 text-sm">
                                @if($morningBackupStatus == 'Completed')
                                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                        Completed
                                    </span>
                                @elseif($morningBackupStatus == 'Failed')
                                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700">
                                        Failed
                                    </span>
                                @else
                                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">
                                        Pending
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                {{ $morningBackupTime ?? '-' }}
                            </td>
                        </tr>
                        <!-- Night Backup -->
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                Night Backup (11:00 PM)
                            </td>
                            <td class="px-4 py-3 text-sm">
                                @if($nightBackupStatus == 'Completed')
                                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                        Completed
                                    </span>
                                @elseif($nightBackupStatus == 'Failed')
                                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700">
                                        Failed
                                    </span>
                                @else
                                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">
                                        Pending
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                {{ $nightBackupTime ?? '-' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
 
        <!-- Database Restore -->
        <div class="bg-white dark:bg-[#0b0e14] border border-gray-200 dark:border-[#1f2937] rounded-lg p-4 shadow-sm">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Database Restore</h2>
            </div>
 
            <form method="POST" action="{{ route('backup.validate') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
 
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Choose Database Backup</label>
                    <input
                        type="file"
                        name="backup_file"
                        id="backup_file"
                        accept=".sql,.gz"
                        required
                        class="w-full px-3 py-2 border rounded-md bg-white dark:bg-[#081118] border-gray-200 dark:border-[#1a202c] text-sm text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-purple-500 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:bg-indigo-50 file:text-indigo-700 file:text-sm">
                </div>
 
                <p id="selectedFile" class="text-xs text-gray-400">No file selected.</p>
 
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white text-sm rounded-lg shadow">
                    <i class="fa-solid fa-shield-halved mr-2"></i> Validate Backup
                </button>
            </form>
        </div>
 
    </div>
@endsection
 
@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('backup_file').addEventListener('change', function () {
        let file = this.files[0];
        if (file) {
            document.getElementById('selectedFile').innerHTML = "<strong>Selected:</strong> " + file.name;
        }
    });
});
</script>
@endsection