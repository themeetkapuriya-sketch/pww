@extends('layouts.app')

@section('title', 'Backup & System Restore')

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
        @php
            $isRestoreMsg = str_contains(strtolower(session('success')), 'restore') || str_contains(strtolower(session('success')), 'restored');
            $isDeleteMsg = str_contains(strtolower(session('success')), 'delete') || str_contains(strtolower(session('success')), 'deleted');
        @endphp
        @if($isRestoreMsg)
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Database Restored!',
                            text: @json(session('success')),
                            icon: 'success',
                            confirmButtonColor: '#10B981',
                            confirmButtonText: 'Awesome!',
                            customClass: {
                                popup: 'rounded-2xl',
                                confirmButton: 'rounded-xl font-bold px-6 py-2.5'
                            }
                        });
                    }
                });
            </script>
        @else
            <div class="{{ $isDeleteMsg ? 'bg-rose-50 border border-rose-200 text-rose-800' : 'bg-emerald-50 border border-emerald-200 text-emerald-800' }} px-4 py-3.5 rounded-xl text-sm font-medium flex items-center justify-between">
                <div class="flex items-center gap-2">
                    @if($isDeleteMsg)
                        <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    @else
                        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    @endif
                    <span>{{ session('success') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="{{ $isDeleteMsg ? 'text-rose-500 hover:text-rose-700' : 'text-emerald-500 hover:text-emerald-700' }} font-bold">&times;</button>
            </div>
        @endif
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

    <!-- System Health & Storage Overview Card -->
    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-4">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 pb-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                        System Health & Database Storage Monitor
                        <span id="dbHealthBadge" class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            ● {{ $systemHealth['health_status'] ?? 'Optimal' }}
                        </span>
                    </h2>
                    <p class="text-slate-500 text-xs mt-0.5">Real-time database footprint, table record counters, and instant index defragmentation</p>
                </div>
            </div>

            <button type="button" id="optimizeDbBtn" onclick="runDatabaseOptimization()" class="inline-flex items-center gap-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold py-2.5 px-4 rounded-xl transition shadow-xs cursor-pointer">
                <svg id="optimizeSpinIcon" class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                <span id="optimizeBtnText">Optimize Database</span>
            </button>
        </div>

        <!-- Metric Counters Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-3 text-center">
                <span class="text-[10px] font-bold uppercase text-slate-400 block mb-0.5">Database Size</span>
                <span id="dbSizeText" class="text-base font-black font-mono text-slate-800">{{ $systemHealth['size_formatted'] ?? '0 KB' }}</span>
                <span class="text-[9px] font-semibold text-slate-500 block mt-0.5">{{ $systemHealth['driver'] ?? 'MySQL' }} ({{ $systemHealth['tables_count'] ?? 0 }} Tables)</span>
            </div>

            <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-3 text-center">
                <span class="text-[10px] font-bold uppercase text-slate-400 block mb-0.5">1. Sales / Invoices</span>
                <span class="text-base font-black font-mono text-blue-700">{{ number_format($tableCounts['invoices'] ?? 0) }}</span>
                <span class="text-[9px] font-semibold text-slate-500 block mt-0.5">{{ number_format($tableCounts['orders'] ?? 0) }} Sales Orders</span>
            </div>

            <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-3 text-center">
                <span class="text-[10px] font-bold uppercase text-slate-400 block mb-0.5">2. Purchases / Expenses</span>
                <span class="text-base font-black font-mono text-indigo-700">{{ number_format($tableCounts['purchases'] ?? 0) }}</span>
                <span class="text-[9px] font-semibold text-slate-500 block mt-0.5">{{ number_format($tableCounts['expenses'] ?? 0) }} Expenses Logged</span>
            </div>

            <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-3 text-center">
                <span class="text-[10px] font-bold uppercase text-slate-400 block mb-0.5">3. Stock / Salary</span>
                <span class="text-base font-black font-mono text-emerald-700">{{ number_format($tableCounts['salaries'] ?? 0) }}</span>
                <span class="text-[9px] font-semibold text-slate-500 block mt-0.5">{{ number_format(($tableCounts['materials'] ?? 0) + ($tableCounts['products'] ?? 0)) }} Stock Catalog Items</span>
            </div>

            <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-3 text-center">
                <span class="text-[10px] font-bold uppercase text-slate-400 block mb-0.5">4. Daily Attendance</span>
                <span class="text-base font-black font-mono text-amber-700">{{ number_format($tableCounts['attendance'] ?? 0) }}</span>
                <span class="text-[9px] font-semibold text-slate-500 block mt-0.5">6-Mo Auto Retention</span>
            </div>

            <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-3 text-center">
                <span class="text-[10px] font-bold uppercase text-slate-400 block mb-0.5">5. Audits / Others</span>
                <span class="text-base font-black font-mono text-slate-700">{{ number_format($tableCounts['audit_logs'] ?? 0) }}</span>
                <span class="text-[9px] font-semibold text-slate-500 block mt-0.5">{{ number_format(($tableCounts['clients'] ?? 0) + ($tableCounts['production'] ?? 0)) }} Clients & Masters</span>
            </div>
        </div>
    </div>

    <!-- 2 Top Cards: Backup & Restore -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Card 1: 1-Click Full Backup -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm flex flex-col justify-between hover:shadow-md transition">
            <div>
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">1-Click Full System Backup</h2>
                        <p class="text-slate-500 text-xs mt-0.5">Download 100% complete database SQL dump</p>
                    </div>
                </div>
                <p class="text-slate-500 text-xs leading-relaxed">Includes all master entries, clients, products, vouchers, user accounts, and system settings in a single SQL backup snapshot.</p>
            </div>
            <div class="mt-6 pt-4 border-t border-slate-100">
                <a href="{{ route('backup.full') }}" target="downloadFrame" onclick="handleFullBackupDownload(event)" class="w-full inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-xl text-sm transition shadow-sm hover:shadow">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Download Full Backup (.sql)
                </a>
            </div>
        </div>

        <!-- Card 2: Restore System Database -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm flex flex-col justify-between hover:shadow-md transition">
            <form id="restoreDbForm" action="{{ route('backup.restore') }}" method="POST" enctype="multipart/form-data" onsubmit="return confirmRestore(event);" novalidate class="h-full flex flex-col justify-between">
                @csrf
                <div>
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-slate-800">Restore System Database</h2>
                            <p class="text-slate-500 text-xs mt-0.5">Upload a `.sql` backup file to restore database</p>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">Select Backup File (.sql)</label>
                            <input type="file" id="restoreFileInput" name="backup_file" accept=".sql,.txt" onchange="validateRestoreFileSelection(this)" required class="w-full bg-slate-50 border border-slate-200 rounded-xl py-1.5 px-3 text-sm text-slate-700 file:mr-4 file:py-1.5 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100 transition-all">
                            <div id="fileValidationHelper" class="mt-1.5 text-xs font-bold hidden"></div>
                        </div>
                        <p class="text-[11px] font-semibold text-slate-500 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Safety Notice: Automatic pre-restore safety snapshot saved before restore.
                        </p>
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-slate-100">
                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 bg-amber-600 hover:bg-amber-700 text-white font-bold py-3 px-4 rounded-xl text-sm transition shadow-sm hover:shadow">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Restore Database Now
                    </button>
                </div>
            </form>
        </div>

    </div>

    <!-- Backup Archive Table -->
    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-slate-800">Stored Local Backups & Auto Snapshots</h2>
                <p class="text-slate-500 text-xs mt-0.5">Backups automatically saved on server disk at <code class="bg-slate-100 px-1.5 py-0.5 rounded text-slate-700 font-mono">storage/app/backups/</code></p>
            </div>
            <span id="totalBackupFilesCount" class="text-xs font-bold text-slate-600 bg-slate-100 border border-slate-200/80 px-3 py-1.5 rounded-xl shadow-2xs">Total Files: {{ count($backups) }}</span>
        </div>

        <div class="overflow-x-auto rounded-xl border border-slate-200/80">
            <table id="backupDatatable" class="erp-datatable min-w-full divide-y divide-slate-200 text-xs">
                <thead class="bg-slate-100/80 border-b border-slate-200 text-slate-700 uppercase font-bold tracking-wider">
                    <tr>
                        <th class="px-4 py-3 text-left">Filename</th>
                        <th class="px-4 py-3 text-left">Type</th>
                        <th class="px-4 py-3 text-left">Size</th>
                        <th class="px-4 py-3 text-left">Created Date</th>
                        <th class="px-4 py-3 text-center w-28">Actions</th>
                    </tr>
                </thead>
                <tbody id="backupTableBody" class="divide-y divide-slate-100 bg-white">
                    @forelse($backups as $b)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-4 py-3 align-middle font-mono text-xs font-semibold text-slate-800">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 01-2-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <span>{{ $b['filename'] }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 align-middle">
                                @if($b['type'] === 'Automated Monthly')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Automated Monthly
                                    </span>
                                @elseif($b['type'] === 'Safety Snapshot')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        Safety Snapshot
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10.5px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                        {{ $b['type'] }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 align-middle text-xs font-semibold text-slate-600 whitespace-nowrap">{{ $b['size'] }}</td>
                            <td class="px-4 py-3 align-middle text-xs text-slate-500 font-medium whitespace-nowrap">{{ $b['created_at'] }}</td>
                            <td class="px-4 py-3 align-middle text-center whitespace-nowrap">
                                <div class="flex items-center justify-center space-x-2">
                                    <!-- Download Solid Action Button -->
                                    <a href="{{ route('backup.downloadFile', $b['filename']) }}" target="downloadFrame" onclick="showDownloadToast('Backup file downloaded successfully!')" class="w-8 h-8 inline-flex items-center justify-center rounded-xl bg-blue-600 hover:bg-blue-700 text-white shadow-xs transition duration-150 transform hover:scale-105" title="Download Backup File">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                        </svg>
                                    </a>

                                    <!-- Delete Solid Action Button with SweetAlert2 Confirmation -->
                                    <form action="{{ route('backup.deleteFile', $b['filename']) }}" method="POST" class="inline" onsubmit="return confirmDeleteBackup(event, this, '{{ $b['filename'] }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-8 h-8 inline-flex items-center justify-center rounded-xl bg-[#F43F5E] hover:bg-[#E11D48] text-white shadow-xs transition duration-150 transform hover:scale-105" title="Delete Backup File">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="empty-row">
                            <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center space-y-2">
                                    <svg class="w-10 h-10 mx-auto text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                    </svg>
                                    <p class="text-sm font-bold text-slate-600">No Records Found</p>
                                    <p class="text-xs text-slate-400">No stored local backup files found.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Danger Zone: Factory Reset & Fresh System Start -->
    <div class="bg-rose-50/70 dark:bg-rose-950/20 border border-rose-200/80 dark:border-rose-900/50 rounded-2xl p-6 shadow-xs space-y-4">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-rose-100 dark:bg-rose-900/40 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-rose-900 dark:text-rose-200 flex items-center gap-2">
                        Danger Zone: Factory Reset / Fresh System Start
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-200/80 dark:bg-rose-900/70 text-rose-800 dark:text-rose-200 border border-rose-300/80 dark:border-rose-700/60">Admin Protected</span>
                    </h3>
                    <p class="text-xs text-rose-700 dark:text-rose-300/80 mt-0.5">
                        Wipe all test transactions, sales orders, invoices, purchases, payroll logs, and expenses to start completely fresh. Automatically takes an emergency backup snapshot first.
                    </p>
                </div>
            </div>

            <button type="button" onclick="openFactoryResetModal()" class="px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl text-xs transition shadow-xs flex items-center gap-2 cursor-pointer shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                <span>Reset to Fresh System</span>
            </button>
        </div>
    </div>
</div>

<!-- Modal: Factory Reset Authentication -->
<div id="factoryResetModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 sm:p-8 max-w-md w-full shadow-2xl border border-rose-200 dark:border-slate-700 space-y-5 animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center gap-3 border-b border-rose-100 dark:border-slate-700 pb-4">
            <div class="w-12 h-12 rounded-2xl bg-rose-100 dark:bg-rose-900/40 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-black text-rose-900 dark:text-rose-200">Authorize Factory Reset</h3>
                <p class="text-xs text-rose-600 dark:text-rose-400 font-medium">Clear transactional data to start fresh</p>
            </div>
        </div>

        <div class="p-3.5 bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/60 rounded-2xl text-xs text-amber-900 dark:text-amber-200 space-y-1.5">
            <p class="font-bold flex items-center gap-1.5 text-amber-800 dark:text-amber-300">
                <span>🛡️</span> Zero-Risk Safety Net:
            </p>
            <p class="text-[11px] leading-relaxed text-amber-900/90 dark:text-amber-200/90">
                The system will <strong>automatically create an emergency backup snapshot</strong> before wiping. Your Admin account, business profile, and roles will be preserved.
            </p>
        </div>

        <form id="factoryResetForm" action="{{ route('backup.reset') }}" method="POST" class="space-y-4" onsubmit="return handleFactoryResetSubmit(event);">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">
                    Enter Admin Account Password <span class="text-rose-500">*</span>
                </label>
                <input type="password" name="admin_password" id="resetAdminPassword" required placeholder="••••••••" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl py-2.5 px-3.5 text-sm font-semibold text-slate-800 dark:text-white focus:bg-white focus:border-rose-500 focus:ring-2 focus:ring-rose-200 dark:focus:ring-rose-900/50 transition">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">
                    Type <span class="text-rose-600 dark:text-rose-400 font-mono">RESET</span> to confirm <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="confirm_phrase" id="resetConfirmPhrase" required placeholder="RESET" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl py-2.5 px-3.5 text-sm font-bold text-rose-700 dark:text-rose-400 font-mono uppercase focus:bg-white focus:border-rose-500 focus:ring-2 focus:ring-rose-200 dark:focus:ring-rose-900/50 transition">
            </div>

            <div class="flex items-center gap-2 pt-1">
                <input type="checkbox" name="keep_masters" id="resetKeepMasters" value="1" checked class="w-4 h-4 text-blue-600 rounded border-slate-300 dark:border-slate-600 focus:ring-blue-500">
                <label for="resetKeepMasters" class="text-xs font-medium text-slate-600 dark:text-slate-400 cursor-pointer">
                    Keep Products, Materials & Clients Master Catalog
                </label>
            </div>

            <div class="pt-2 flex items-center justify-end gap-3 border-t border-slate-100 dark:border-slate-700">
                <button type="button" onclick="closeFactoryResetModal()" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 font-bold rounded-xl text-xs transition cursor-pointer">
                    Cancel
                </button>
                <button type="submit" id="resetSubmitBtn" class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl text-xs transition shadow-sm cursor-pointer flex items-center gap-2">
                    <span id="resetBtnText">Wipe & Reset System</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Hidden iframe for seamless background downloads without navigating away -->
<iframe name="downloadFrame" id="downloadFrame" style="display:none;"></iframe>

<script>
function showDownloadToast(msg, type = 'success') {
    let container = document.getElementById('toastNotificationContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toastNotificationContainer';
        document.body.appendChild(container);
    }

    const isDanger = (type === 'danger' || type === 'delete' || type === 'error');
    const bgClass = isDanger ? 'bg-[#F43F5E] border-rose-500' : 'bg-emerald-600 border-emerald-500';
    const iconSvg = isDanger 
        ? `<svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>`
        : `<svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>`;

    container.className = `fixed top-5 right-5 z-50 ${bgClass} text-white font-bold px-5 py-3.5 rounded-xl shadow-xl border flex items-center gap-3 transition-all duration-300 transform translate-y-0 opacity-100`;

    container.innerHTML = `
        ${iconSvg}
        <span class="text-xs">${msg}</span>
    `;

    setTimeout(() => {
        container.classList.add('opacity-0', '-translate-y-2');
        setTimeout(() => container.remove(), 300);
    }, 4000);
}

function refreshBackupTable() {
    $.getJSON("{{ route('backup.listJson') }}", function(data) {
        if (!data.success) return;

        $('#totalBackupFilesCount').text('Total Files: ' + data.count);

        if ($.fn.DataTable && $.fn.DataTable.isDataTable('#backupDatatable')) {
            $('#backupDatatable').DataTable().destroy();
        }

        let tbody = $('#backupTableBody');
        tbody.empty();

        if (data.backups.length === 0) {
            tbody.append(`
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-slate-400 text-xs font-medium">
                        No stored local backup files found. Click "Download Full Backup" to generate one.
                    </td>
                </tr>
            `);
        } else {
            data.backups.forEach(function(b) {
                let badgeClass = 'bg-blue-50 text-blue-700 border-blue-200';
                if (b.type === 'Automated Monthly') badgeClass = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                else if (b.type === 'Safety Snapshot') badgeClass = 'bg-amber-50 text-amber-700 border-amber-200';

                let downloadUrl = "{{ route('backup.downloadFile', ':file') }}".replace(':file', encodeURIComponent(b.filename));
                let deleteUrl = "{{ route('backup.deleteFile', ':file') }}".replace(':file', encodeURIComponent(b.filename));
                let csrfToken = "{{ csrf_token() }}";

                let tr = `
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="px-4 py-3 align-middle font-mono text-xs font-semibold text-slate-800">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 01-2-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span>${b.filename}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 align-middle">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10.5px] font-bold ${badgeClass} border">
                                ${b.type}
                            </span>
                        </td>
                        <td class="px-4 py-3 align-middle text-xs font-semibold text-slate-600 whitespace-nowrap">${b.size}</td>
                        <td class="px-4 py-3 align-middle text-xs text-slate-500 font-medium whitespace-nowrap">${b.created_at}</td>
                        <td class="px-4 py-3 align-middle text-center whitespace-nowrap">
                            <div class="flex items-center justify-center space-x-2">
                                <a href="${downloadUrl}" target="downloadFrame" onclick="showDownloadToast('Backup file downloaded successfully!')" class="w-8 h-8 inline-flex items-center justify-center rounded-xl bg-blue-600 hover:bg-blue-700 text-white shadow-xs transition duration-150 transform hover:scale-105" title="Download Backup File">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                </a>

                                <form action="${deleteUrl}" method="POST" class="inline" onsubmit="return confirmDeleteBackup(event, this, '${b.filename}')">
                                    <input type="hidden" name="_token" value="${csrfToken}">
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button type="submit" class="w-8 h-8 inline-flex items-center justify-center rounded-xl bg-[#F43F5E] hover:bg-[#E11D48] text-white shadow-xs transition duration-150 transform hover:scale-105" title="Delete Backup File">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                `;
                tbody.append(tr);
            });
        }

        if (window.initErpDataTables) {
            window.initErpDataTables();
        }
    });
}

function handleFullBackupDownload(e) {
    showDownloadToast('Full database backup (.sql) generated & downloading!');
    setTimeout(() => {
        refreshBackupTable();
    }, 1200);
}

function confirmDeleteBackup(e, form, filename) {
    e.preventDefault();
    const actionUrl = $(form).attr('action');

    const performDelete = function() {
        $.ajax({
            url: actionUrl,
            type: 'DELETE',
            data: {
                _token: "{{ csrf_token() }}"
            },
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            success: function(res) {
                showDownloadToast(res.message || `Backup file '${filename}' deleted successfully.`, 'danger');
                refreshBackupTable();
            },
            error: function(xhr) {
                let err = xhr.responseJSON ? xhr.responseJSON.message : 'Failed to delete backup file.';
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Error', err, 'error');
                } else {
                    alert(err);
                }
            }
        });
    };

    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Delete Backup File?',
            text: `Are you sure you want to delete backup file "${filename}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#F43F5E',
            cancelButtonColor: '#64748B',
            confirmButtonText: 'Yes, Delete',
            cancelButtonText: 'Cancel',
            customClass: {
                popup: 'rounded-2xl',
                confirmButton: 'rounded-xl font-bold px-4 py-2.5',
                cancelButton: 'rounded-xl font-bold px-4 py-2.5'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                performDelete();
            }
        });
    } else {
        if (confirm(`Are you sure you want to delete "${filename}"?`)) {
            performDelete();
        }
    }
    return false;
}

function validateRestoreFileSelection(input) {
    const $input = $(input);
    const $helper = $('#fileValidationHelper');
    if (!input.files || input.files.length === 0) {
        $input.removeClass('ring-2 ring-rose-500 border-rose-500 ring-emerald-500 border-emerald-500');
        $helper.addClass('hidden');
        return;
    }
    const file = input.files[0];
    const ext = file.name.split('.').pop().toLowerCase();
    if (ext !== 'sql' && ext !== 'txt') {
        $input.removeClass('ring-2 ring-emerald-500 border-emerald-500').addClass('ring-2 ring-rose-500 border-rose-500');
        $helper.removeClass('hidden text-emerald-600').addClass('text-rose-600').html(`❌ Invalid extension ".${ext}". Please select a .sql backup file.`);
    } else if (file.size === 0) {
        $input.removeClass('ring-2 ring-emerald-500 border-emerald-500').addClass('ring-2 ring-rose-500 border-rose-500');
        $helper.removeClass('hidden text-emerald-600').addClass('text-rose-600').html(`❌ File "${file.name}" is empty (0 bytes).`);
    } else {
        const sizeMb = (file.size / 1024 / 1024).toFixed(2);
        $input.removeClass('ring-2 ring-rose-500 border-rose-500').addClass('ring-2 ring-emerald-500 border-emerald-500');
        $helper.removeClass('hidden text-rose-600').addClass('text-emerald-600').html(`✓ Ready to restore: <strong>${file.name}</strong> (${sizeMb} MB)`);
    }
}

function confirmRestore(e) {
    if (e) {
        e.preventDefault();
        e.stopPropagation();
    }
    const form = document.getElementById('restoreDbForm') || (e ? e.target : null);
    const fileInput = document.getElementById('restoreFileInput') || (form ? form.querySelector('input[type="file"]') : null);
    const $helper = $('#fileValidationHelper');

    // Validation 1: File selection check
    if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
        $(fileInput).removeClass('ring-2 ring-emerald-500 border-emerald-500').addClass('ring-2 ring-rose-500 border-rose-500').focus();
        if ($helper.length) {
            $helper.removeClass('hidden text-emerald-600').addClass('text-rose-600').html('⚠️ Please choose a .sql backup file to restore.');
        }
        return false;
    }

    const selectedFile = fileInput.files[0];
    const fileName = selectedFile.name;
    const fileSize = selectedFile.size;
    const ext = fileName.split('.').pop().toLowerCase();

    // Validation 2: File extension check (.sql or .txt)
    if (ext !== 'sql' && ext !== 'txt') {
        $(fileInput).addClass('ring-2 ring-rose-500 border-rose-500');
        if ($helper.length) {
            $helper.removeClass('hidden text-emerald-600').addClass('text-rose-600').html(`❌ Invalid file type "${ext}". Only .sql database backup files are allowed.`);
        }
        Swal.fire({
            icon: 'error',
            title: 'Invalid File Format!',
            html: `File <strong>"${fileName}"</strong> is not a valid SQL backup file.<br><br>Please select a <strong>.sql</strong> database file.`,
            confirmButtonColor: '#EF4444',
            customClass: {
                popup: 'rounded-2xl shadow-2xl p-6 border border-slate-100',
                confirmButton: 'px-5 py-2.5 rounded-xl font-bold text-sm bg-rose-600 hover:bg-rose-700 text-white cursor-pointer'
            }
        });
        return false;
    }

    // Validation 3: File size check (> 0 Bytes)
    if (fileSize === 0) {
        $(fileInput).addClass('ring-2 ring-rose-500 border-rose-500');
        if ($helper.length) {
            $helper.removeClass('hidden text-emerald-600').addClass('text-rose-600').html(`❌ File "${fileName}" is empty (0 bytes).`);
        }
        Swal.fire({
            icon: 'error',
            title: 'Empty File Error!',
            text: `The selected file "${fileName}" is empty (0 bytes). Please upload a valid backup.`,
            confirmButtonColor: '#EF4444',
            customClass: {
                popup: 'rounded-2xl shadow-2xl p-6 border border-slate-100',
                confirmButton: 'px-5 py-2.5 rounded-xl font-bold text-sm bg-rose-600 hover:bg-rose-700 text-white cursor-pointer'
            }
        });
        return false;
    }

    // Clear validation error styling
    $(fileInput).removeClass('ring-2 ring-rose-500 border-rose-500');
    if ($helper.length) {
        const readableSize = (fileSize / 1024 / 1024).toFixed(2);
        $helper.removeClass('hidden text-rose-600').addClass('text-emerald-600').html(`✓ Valid Backup: <strong>${fileName}</strong> (${readableSize} MB)`);
    }

    const performRestoreAjax = function() {
        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"]');
        let origHtml = submitBtn ? submitBtn.innerHTML : '';
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="inline-flex items-center gap-2"><svg class="animate-spin h-4 w-4 text-white shrink-0" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Restoring Database...</span>';
        }

        $.ajax({
            url: form.action,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || '',
                'Accept': 'application/json'
            },
            success: function(res) {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = origHtml;
                }
                form.reset();
                if ($helper.length) $helper.addClass('hidden');
                Swal.fire({
                    title: 'Database Restored!',
                    text: res.message || 'System database restored successfully.',
                    icon: 'success',
                    timer: 2000,
                    timerProgressBar: true,
                    showConfirmButton: false,
                    customClass: { popup: 'rounded-2xl shadow-2xl p-6 border border-slate-100' }
                }).then(() => {
                    refreshBackupTable();
                });
            },
            error: function(xhr) {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = origHtml;
                }
                let err = xhr.responseJSON ? xhr.responseJSON.message : 'Failed to restore database.';
                Swal.fire({
                    title: 'Restore Failed',
                    text: err,
                    icon: 'error',
                    confirmButtonColor: '#EF4444',
                    customClass: { popup: 'rounded-2xl shadow-2xl p-6 border border-slate-100' }
                });
            }
        });
    };

    if (typeof Swal !== 'undefined') {
        const formattedSize = (fileSize / 1024 / 1024).toFixed(2);
        Swal.fire({
            title: 'Restore System Database?',
            html: `WARNING: Restoring <strong>"${fileName}"</strong> (${formattedSize} MB) will overwrite current database records with the backup state.<br><br>An automatic pre-restore safety snapshot will be saved first.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#D97706',
            cancelButtonColor: '#64748B',
            confirmButtonText: 'Yes, Restore Now',
            cancelButtonText: 'Cancel',
            customClass: {
                popup: 'rounded-2xl shadow-2xl p-6 border border-slate-100',
                confirmButton: 'rounded-xl font-bold px-4 py-2.5 bg-amber-600 hover:bg-amber-700 text-white cursor-pointer',
                cancelButton: 'rounded-xl font-bold px-4 py-2.5 bg-slate-500 hover:bg-slate-600 text-white cursor-pointer'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                performRestoreAjax();
            }
        });
        return false;
    }

    performRestoreAjax();
    return false;
}

function runDatabaseOptimization() {
    const btn = document.getElementById('optimizeDbBtn');
    const spin = document.getElementById('optimizeSpinIcon');
    const text = document.getElementById('optimizeBtnText');

    if (btn) btn.disabled = true;
    if (spin) spin.classList.add('animate-spin');
    if (text) text.innerText = 'Optimizing...';

    $.ajax({
        url: "{{ route('backup.optimize') }}",
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || '',
            'Accept': 'application/json'
        },
        success: function(res) {
            if (btn) btn.disabled = false;
            if (spin) spin.classList.remove('animate-spin');
            if (text) text.innerText = 'Optimize Database';

            if (res.success) {
                if (res.metrics && document.getElementById('dbSizeText')) {
                    document.getElementById('dbSizeText').innerText = res.metrics.size_formatted;
                }
                showDownloadToast('⚡ ' + (res.message || 'Database optimized successfully!'));
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Database Optimized!',
                        text: res.message || 'Defragmented and search indexes rebuilt successfully.',
                        icon: 'success',
                        timer: 2500,
                        timerProgressBar: true,
                        showConfirmButton: false,
                        customClass: { popup: 'rounded-2xl shadow-2xl p-6 border border-slate-100' }
                    });
                }
            } else {
                showDownloadToast('❌ ' + (res.message || 'Optimization failed.'), 'error');
            }
        },
        error: function(xhr) {
            if (btn) btn.disabled = false;
            if (spin) spin.classList.remove('animate-spin');
            if (text) text.innerText = 'Optimize Database';
            const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Database optimization failed.';
            showDownloadToast('❌ ' + msg, 'error');
        }
    });
}

function openFactoryResetModal() {
    document.getElementById('factoryResetModal')?.classList.remove('hidden');
    document.getElementById('resetAdminPassword')?.focus();
}

function closeFactoryResetModal() {
    document.getElementById('factoryResetModal')?.classList.add('hidden');
    document.getElementById('factoryResetForm')?.reset();
}

function handleFactoryResetSubmit(e) {
    e.preventDefault();
    const form = e.target;
    const pwd = document.getElementById('resetAdminPassword').value;
    const phrase = document.getElementById('resetConfirmPhrase').value.trim().toUpperCase();

    if (phrase !== 'RESET') {
        alert("Please type 'RESET' exactly in the confirmation box.");
        return false;
    }

    const btn = document.getElementById('resetSubmitBtn');
    const text = document.getElementById('resetBtnText');
    if (btn) btn.disabled = true;
    if (text) text.innerText = 'Resetting System...';

    $.ajax({
        url: form.action,
        type: 'POST',
        data: $(form).serialize(),
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || '',
            'Accept': 'application/json'
        },
        success: function(res) {
            closeFactoryResetModal();
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'System Reset Complete!',
                    text: res.message || 'System has been reset to a fresh state. An emergency backup was saved.',
                    icon: 'success',
                    confirmButtonColor: '#10B981',
                    confirmButtonText: 'Continue to Dashboard',
                    customClass: {
                        popup: 'rounded-2xl',
                        confirmButton: 'rounded-xl font-bold px-6 py-2.5'
                    }
                }).then(() => {
                    window.location.href = res.redirect || "{{ route('overview') }}";
                });
            } else {
                alert(res.message);
                window.location.href = res.redirect || "{{ route('overview') }}";
            }
        },
        error: function(xhr) {
            if (btn) btn.disabled = false;
            if (text) text.innerText = 'Wipe & Reset System';
            const err = xhr.responseJSON ? xhr.responseJSON.message : 'Factory reset failed.';
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Reset Authorization Failed',
                    text: err,
                    icon: 'error',
                    confirmButtonColor: '#EF4444',
                    customClass: { popup: 'rounded-2xl' }
                });
            } else {
                alert(err);
            }
        }
    });

    return false;
}
</script>
@endsection
