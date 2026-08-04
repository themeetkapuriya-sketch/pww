@extends('layouts.app')

@section('title', 'User Activity Audit Logs')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-purple-100 border border-purple-200 text-purple-700 flex items-center justify-center font-bold text-sm">
                    🛡️
                </div>
                User Activity Audit Logs
            </h1>
            <p class="text-sm text-slate-500 mt-1">Real-time system audit trail & security action logs. Restricted to Super Admin.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('activity-logs.export', request()->all()) }}" class="bg-slate-800 hover:bg-slate-900 text-white font-bold py-2.5 px-4 rounded-xl text-xs transition shadow-xs flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Export Audit Logs (CSV)
            </a>
        </div>
    </div>

    <!-- KPI Metric Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total System Logs Today</span>
                <div class="text-2xl font-black text-slate-800 mt-1">{{ number_format($totalToday) }}</div>
            </div>
            <div class="w-11 h-11 rounded-2xl bg-blue-50 border border-blue-100 text-blue-600 flex items-center justify-center font-bold text-lg">
                ⚡
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Deletions & High-Priority Actions</span>
                <div class="text-2xl font-black text-rose-600 mt-1">{{ number_format($highPriorityCount) }}</div>
            </div>
            <div class="w-11 h-11 rounded-2xl bg-rose-50 border border-rose-100 text-rose-600 flex items-center justify-center font-bold text-lg">
                🚨
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Super Admin Executions</span>
                <div class="text-2xl font-black text-purple-700 mt-1">{{ number_format($superAdminActions) }}</div>
            </div>
            <div class="w-11 h-11 rounded-2xl bg-purple-50 border border-purple-100 text-purple-600 flex items-center justify-center font-bold text-lg">
                👑
            </div>
        </div>
    </div>

    <!-- Filters Bar -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs">
        <form method="GET" action="{{ route('activity-logs') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Search Keywords</label>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search description, IP..." class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs font-medium text-slate-800">
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Module</label>
                <select name="module" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs font-semibold text-slate-800">
                    <option value="">All Modules</option>
                    @foreach ($modules as $mod)
                        <option value="{{ $mod }}" {{ request('module') === $mod ? 'selected' : '' }}>{{ $mod }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Action Type</label>
                <select name="action" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs font-semibold text-slate-800">
                    <option value="">All Actions</option>
                    <option value="created" {{ request('action') === 'created' ? 'selected' : '' }}>Created (🟢)</option>
                    <option value="updated" {{ request('action') === 'updated' ? 'selected' : '' }}>Updated (🟡)</option>
                    <option value="deleted" {{ request('action') === 'deleted' ? 'selected' : '' }}>Deleted (🔴)</option>
                    <option value="login" {{ request('action') === 'login' ? 'selected' : '' }}>Login / Logout</option>
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">User Account</label>
                <select name="user_id" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs font-semibold text-slate-800">
                    <option value="">All Users</option>
                    @foreach ($usersList as $u)
                        <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }} ({{ ucfirst($u->role) }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">From Date</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs font-medium text-slate-800">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-xl text-xs transition shadow-xs">
                    Filter Logs
                </button>
                <a href="{{ route('activity-logs') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold py-2 px-3 rounded-xl text-xs transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Activity Logs Table -->
    <div class="bg-white rounded-2xl shadow-xs border border-slate-200 p-6 space-y-4">
        <div class="overflow-x-auto w-full max-w-full">
            <table class="erp-datatable min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-[#EDF4FA] text-black divide-x divide-slate-200">
                    <tr>
                        <th class="px-4 py-3.5 text-center text-xs font-bold uppercase w-12">#</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold uppercase">Date & Time</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold uppercase">User & Role</th>
                        <th class="px-6 py-3.5 text-center text-xs font-bold uppercase">Module</th>
                        <th class="px-6 py-3.5 text-center text-xs font-bold uppercase">Action</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold uppercase">Event Description</th>
                        <th class="px-6 py-3.5 text-right text-xs font-bold uppercase">IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($logs as $log)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-4 py-3.5 text-center text-xs font-mono text-slate-400">{{ $log->id }}</td>
                            <td class="px-6 py-3.5 text-xs text-slate-700 whitespace-nowrap">
                                <span class="font-bold block text-slate-800">{{ $log->created_at->format('d M Y') }}</span>
                                <span class="text-slate-400 text-[11px] font-mono">{{ $log->created_at->format('h:i:s A') }}</span>
                            </td>
                            <td class="px-6 py-3.5 text-xs whitespace-nowrap">
                                <span class="font-bold text-slate-800 block">{{ $log->user_name }}</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $log->user_role === 'super_admin' ? 'bg-purple-100 text-purple-800 border border-purple-200' : 'bg-slate-100 text-slate-700' }}">
                                    {{ str_replace('_', ' ', $log->user_role) }}
                                </span>
                            </td>
                            <td class="px-6 py-3.5 text-center whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-xl font-bold text-xs bg-slate-100 text-slate-700 border border-slate-200/70">
                                    {{ $log->module }}
                                </span>
                            </td>
                            <td class="px-6 py-3.5 text-center whitespace-nowrap">
                                @if (in_array($log->action, ['created', 'login']))
                                    <span class="px-2.5 py-1 rounded-xl text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 inline-flex items-center gap-1">
                                        🟢 {{ strtoupper($log->action) }}
                                    </span>
                                @elseif ($log->action === 'updated')
                                    <span class="px-2.5 py-1 rounded-xl text-xs font-bold bg-amber-100 text-amber-900 border border-amber-200 inline-flex items-center gap-1">
                                        🟡 {{ strtoupper($log->action) }}
                                    </span>
                                @elseif (in_array($log->action, ['deleted', 'security']))
                                    <span class="px-2.5 py-1 rounded-xl text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200 inline-flex items-center gap-1">
                                        🔴 {{ strtoupper($log->action) }}
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-xl text-xs font-bold bg-blue-100 text-blue-800 border border-blue-200 inline-flex items-center gap-1">
                                        🔵 {{ strtoupper($log->action) }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-3.5 text-xs text-slate-800 font-medium max-w-md">
                                {{ $log->description }}
                            </td>
                            <td class="px-6 py-3.5 text-right text-xs font-mono text-slate-500 whitespace-nowrap">
                                {{ $log->ip_address ?? '127.0.0.1' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-slate-400 text-xs font-medium">
                                No activity audit log entries found matching the filter criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pt-3">
            {{ $logs->links() }}
        </div>
    </div>
</div>
@endsection
