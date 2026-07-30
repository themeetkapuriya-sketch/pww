@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80">
        <div>
            <h1 class="text-2xl font-black text-slate-800 tracking-tight flex items-center gap-2">
                <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                </svg>
                Backup & System Restore
            </h1>
            <p class="text-slate-500 text-xs font-medium mt-1">Export full system backups, filter data by month/financial year, and restore system snapshots</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                <span class="w-2 h-2 rounded-full bg-emerald-500 mr-2 animate-pulse"></span>
                Smart Auto-Backup Active
            </span>
        </div>
    </div>

    <!-- Alert Notifications -->
    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3.5 rounded-xl text-sm font-medium flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 font-bold">&times;</button>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3.5 rounded-xl text-sm font-medium flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('error') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700 font-bold">&times;</button>
        </div>
    @endif

    <!-- 3 Action Cards Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Card 1: 1-Click Full Backup -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm flex flex-col justify-between hover:shadow-md transition">
            <div>
                <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                </div>
                <h2 class="text-lg font-bold text-slate-800">1-Click Full System Backup</h2>
                <p class="text-slate-500 text-xs mt-1.5 leading-relaxed">Download 100% complete database SQL dump including all master entries, clients, products, vouchers, and settings.</p>
            </div>
            <div class="mt-6 pt-4 border-t border-slate-100">
                <a href="{{ route('backup.full') }}" class="w-full inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-xl text-sm transition shadow-sm hover:shadow">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Download Full Backup (.sql)
                </a>
            </div>
        </div>

        <!-- Card 2: Period-Filtered Export -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm flex flex-col justify-between lg:col-span-2 hover:shadow-md transition">
            <div>
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.447.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">Period-Filtered Backup Export</h2>
                        <p class="text-slate-500 text-xs">Filter data export by Month, Financial Year (Apr–Mar), or Custom Date Range</p>
                    </div>
                </div>

                <form id="filteredBackupForm" action="{{ route('backup.filtered') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase mb-1.5">Select Period Type</label>
                            <select id="periodTypeSelect" name="period_type" onchange="togglePeriodInputs()" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="current_month">Current Month ({{ \Carbon\Carbon::now()->format('F Y') }})</option>
                                <option value="specific_month">Select Specific Month</option>
                                <option value="financial_year">Financial Year (Apr 1 - Mar 31)</option>
                                <option value="custom">Custom Date Range</option>
                                <option value="all_time">All Time (Full Database)</option>
                            </select>
                        </div>

                        <!-- Specific Month Input -->
                        <div id="specificMonthContainer" class="hidden">
                            <label class="block text-xs font-bold text-slate-600 uppercase mb-1.5">Choose Month</label>
                            <input type="month" name="month" value="{{ \Carbon\Carbon::now()->format('Y-m') }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <!-- Financial Year Input -->
                        <div id="financialYearContainer" class="hidden">
                            <label class="block text-xs font-bold text-slate-600 uppercase mb-1.5">Choose Financial Year</label>
                            <select name="financial_year" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                @foreach($financialYears as $fy)
                                    <option value="{{ $fy['key'] }}">{{ $fy['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Custom Range Inputs -->
                    <div id="customRangeContainer" class="hidden grid grid-cols-1 md:grid-cols-2 gap-4 border-t border-slate-100 pt-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Start Date</label>
                            <input type="date" name="start_date" value="{{ \Carbon\Carbon::now()->startOfMonth()->toDateString() }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm font-medium text-slate-800">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">End Date</label>
                            <input type="date" name="end_date" value="{{ \Carbon\Carbon::now()->toDateString() }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm font-medium text-slate-800">
                        </div>
                    </div>

                    <div class="pt-2 flex justify-end">
                        <button type="submit" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-6 rounded-xl text-sm transition shadow-sm hover:shadow">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Export Filtered Backup (.sql)
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <!-- Restore Database Section -->
    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
            </div>
            <div>
                <h2 class="text-lg font-bold text-slate-800">Restore System Database</h2>
                <p class="text-slate-500 text-xs">Upload a previously downloaded `.sql` backup file to restore complete system database</p>
            </div>
        </div>

        <form action="{{ route('backup.restore') }}" method="POST" enctype="multipart/form-data" onsubmit="return confirmRestore(event)" class="bg-slate-50 p-5 rounded-xl border border-slate-200/60">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center">
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Select Backup File (.sql)</label>
                    <input type="file" name="backup_file" accept=".sql,.txt" required class="w-full bg-white border border-slate-300 rounded-xl py-2 px-3 text-sm text-slate-700 file:mr-4 file:py-1.5 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 bg-amber-600 hover:bg-amber-700 text-white font-bold py-2.5 px-4 rounded-xl text-sm transition shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Restore Database Now
                    </button>
                </div>
            </div>
            <p class="text-[11px] font-semibold text-slate-500 mt-2 flex items-center gap-1">
                <svg class="w-3.5 h-3.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Safety Notice: An automatic pre-restore safety snapshot will be saved before performing restoration.
            </p>
        </form>
    </div>

    <!-- Backup Archive Table -->
    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-base font-bold text-slate-800">Stored Local Backups & Auto Snapshots</h2>
                <p class="text-slate-500 text-xs">Backups automatically saved on server disk at <code class="bg-slate-100 px-1.5 py-0.5 rounded text-slate-700 font-mono">storage/app/backups/</code></p>
            </div>
            <span class="text-xs font-bold text-slate-500 bg-slate-100 px-3 py-1 rounded-lg">Total Files: {{ count($backups) }}</span>
        </div>

        <div class="overflow-x-auto rounded-xl border border-slate-200/60">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 border-b border-slate-200/60 text-xs font-bold text-slate-600 uppercase">
                    <tr>
                        <th class="py-3 px-4">Filename</th>
                        <th class="py-3 px-4">Type</th>
                        <th class="py-3 px-4">Size</th>
                        <th class="py-3 px-4">Created Date</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($backups as $b)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="py-3.5 px-4 font-mono text-xs font-semibold text-slate-800 flex items-center gap-2">
                                <svg class="w-4 h-4 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                {{ $b['filename'] }}
                            </td>
                            <td class="py-3.5 px-4">
                                @if($b['type'] === 'Automated Monthly')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Automated Monthly
                                    </span>
                                @elseif($b['type'] === 'Safety Snapshot')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        Safety Snapshot
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                        {{ $b['type'] }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-xs font-semibold text-slate-600">{{ $b['size'] }}</td>
                            <td class="py-3.5 px-4 text-xs text-slate-500 font-medium">{{ $b['created_at'] }}</td>
                            <td class="py-3.5 px-4 text-right space-x-2">
                                <a href="{{ route('backup.downloadFile', $b['filename']) }}" class="inline-flex items-center gap-1 text-xs font-bold text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-lg transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                    Download
                                </a>
                                <form action="{{ route('backup.deleteFile', $b['filename']) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete backup file {{ $b['filename'] }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center gap-1 text-xs font-bold text-rose-600 hover:text-rose-800 bg-rose-50 hover:bg-rose-100 px-3 py-1.5 rounded-lg transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400 text-xs">
                                No stored local backup files found. Click "Download Full Backup" to generate one.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function togglePeriodInputs() {
    const val = document.getElementById('periodTypeSelect').value;
    const monthContainer = document.getElementById('specificMonthContainer');
    const fyContainer = document.getElementById('financialYearContainer');
    const customContainer = document.getElementById('customRangeContainer');

    monthContainer.classList.add('hidden');
    fyContainer.classList.add('hidden');
    customContainer.classList.add('hidden');

    if (val === 'specific_month') {
        monthContainer.classList.remove('hidden');
    } else if (val === 'financial_year') {
        fyContainer.classList.remove('hidden');
    } else if (val === 'custom') {
        customContainer.classList.remove('hidden');
    }
}

function confirmRestore(e) {
    if (!confirm('WARNING: Restoring will overwrite current database records with the uploaded backup state.\n\nAn emergency safety snapshot of your current database will be saved first.\n\nDo you wish to proceed?')) {
        e.preventDefault();
        return false;
    }
    return true;
}
</script>
@endsection
