<!-- Sub Content 4: Security & Database Backups Partial -->
<div id="subTab-security" class="sub-tab-content hidden space-y-6">
    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-6">
        <div>
            <h2 class="text-lg font-bold text-slate-800">Security Parameters & Automated Backups</h2>
            <p class="text-slate-500 text-xs mt-1">Configure session timeout policies, login security options, and automated database backups.</p>
        </div>

        <form id="securitySettingsForm" action="{{ route('settings.security') }}" method="POST" class="space-y-6" novalidate onsubmit="return handleSecuritySettingsSubmit(event);">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="sec_session_timeout" class="block text-xs font-bold text-slate-600 uppercase mb-1">Session Inactivity Timeout (Minutes)</label>
                    <input type="number" id="sec_session_timeout" name="session_timeout_minutes" value="{{ \App\Models\Setting::get('session_timeout_minutes', '120') }}" min="15" max="1440" required class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-semibold text-slate-800 focus:bg-white focus:border-blue-500 transition">
                    <span class="text-[11px] text-slate-400">Auto-logout inactive users (Min: 15 mins, Max: 1440 mins)</span>
                </div>

                <div>
                    <label for="sec_max_login_attempts" class="block text-xs font-bold text-slate-600 uppercase mb-1">Max Failed Login Attempts</label>
                    <input type="number" id="sec_max_login_attempts" name="max_login_attempts" value="{{ \App\Models\Setting::get('max_login_attempts', '5') }}" min="3" max="10" required class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-semibold text-slate-800 focus:bg-white focus:border-blue-500 transition">
                    <span class="text-[11px] text-slate-400">Lock user IP temporarily after N bad passwords</span>
                </div>

                <div>
                    <label for="sec_auto_backup_freq" class="block text-xs font-bold text-slate-600 uppercase mb-1">Automated Database Backup Frequency</label>
                    @php
                        $currEnabled = \App\Models\Setting::get('auto_backup_enabled', 'true') === 'true';
                        $currFreq = $currEnabled ? \App\Models\Setting::get('auto_backup_frequency', 'daily') : 'disabled';
                    @endphp
                    <select id="sec_auto_backup_freq" name="auto_backup_frequency" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-semibold text-slate-800 focus:bg-white focus:border-blue-500 transition">
                        <option value="daily" {{ $currFreq === 'daily' ? 'selected' : '' }}>Daily (Recommended for Active Production)</option>
                        <option value="weekly" {{ $currFreq === 'weekly' ? 'selected' : '' }}>Weekly Backup</option>
                        <option value="monthly" {{ $currFreq === 'monthly' ? 'selected' : '' }}>Monthly Backups</option>
                        <option value="disabled" {{ $currFreq === 'disabled' ? 'selected' : '' }}>Disabled (Manual Backups Only)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Scheduled Backup Alarm Time</label>
                    <input type="time" name="auto_backup_time" value="{{ \App\Models\Setting::get('auto_backup_time', '18:00') }}" required class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-semibold text-slate-800 focus:bg-white focus:border-blue-500 transition">
                    <span class="text-[11px] text-slate-400">Exact time of day when automatic backup generates</span>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Weekly Backup Scheduled Day</label>
                    @php $currDay = \App\Models\Setting::get('auto_backup_day', 'Wednesday'); @endphp
                    <select name="auto_backup_day" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-semibold text-slate-800 focus:bg-white focus:border-blue-500 transition">
                        @foreach(['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'] as $day)
                            <option value="{{ $day }}" {{ $currDay === $day ? 'selected' : '' }}>{{ $day }}</option>
                        @endforeach
                    </select>
                    <span class="text-[11px] text-slate-400">Day of the week for weekly backup dumps</span>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Auto-Delete Old Backups</label>
                    @php $currRetention = \App\Models\Setting::get('auto_backup_retention', '3_months'); @endphp
                    <select name="auto_backup_retention" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-semibold text-slate-800 focus:bg-white focus:border-blue-500 transition">
                        <option value="1_month" {{ $currRetention === '1_month' ? 'selected' : '' }}>After 1 Month (30 Days)</option>
                        <option value="3_months" {{ $currRetention === '3_months' ? 'selected' : '' }}>After 3 Months (90 Days - Recommended)</option>
                        <option value="6_months" {{ $currRetention === '6_months' ? 'selected' : '' }}>After 6 Months (180 Days)</option>
                        <option value="1_year" {{ $currRetention === '1_year' ? 'selected' : '' }}>After 1 Year (365 Days)</option>
                        <option value="never" {{ $currRetention === 'never' ? 'selected' : '' }}>Never (Keep All Backups Forever)</option>
                    </select>
                    <span class="text-[11px] text-slate-400">Automatically removes old backup files to save server storage</span>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Email Backup Attachment</label>
                    @php $currEmailBackup = \App\Models\Setting::get('auto_email_backup', 'true'); @endphp
                    <select name="auto_email_backup" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-semibold text-slate-800 focus:bg-white focus:border-blue-500 transition">
                        <option value="true" {{ $currEmailBackup === 'true' ? 'selected' : '' }}>Enabled (Send SQL File Attachment to Email)</option>
                        <option value="false" {{ $currEmailBackup === 'false' ? 'selected' : '' }}>Disabled (Save Local Backup File Only)</option>
                    </select>
                </div>
            </div>

            <div class="pt-4 flex justify-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-xl text-sm transition shadow-sm cursor-pointer">
                    Save Security Settings
                </button>
            </div>
        </form>

        <!-- Manual Backup Trigger Section -->
        <div class="border-t border-slate-100 pt-6 mt-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-slate-50/70 p-4 rounded-xl border border-slate-200/80">
            <div>
                <h3 class="text-sm font-bold text-slate-800">Instant Manual Database Backup</h3>
                <p class="text-xs text-slate-500 mt-0.5">Generate a complete SQL snapshot of your database right now.</p>
            </div>
            <a href="{{ route('backup.full') }}" class="no-ajax w-full sm:w-60 min-w-[240px] inline-flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 px-4 rounded-xl text-xs transition shadow-sm shrink-0 whitespace-nowrap" download>
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                <span>Download Full Backup</span>
            </a>
        </div>

        <!-- ⚡ 1-Click Self-Healing & System Cache Re-Sync -->
        <div class="border-t border-slate-100 pt-6 mt-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-blue-50/60 p-4 rounded-xl border border-blue-200/80">
            <div>
                <h3 class="text-sm font-bold text-slate-800 flex items-center gap-1.5">
                    <span>⚡ 1-Click Self-Repair & Cache Re-Sync</span>
                    <span class="px-2 py-0.5 bg-blue-100 text-blue-700 text-[10px] font-extrabold rounded-md">Self-Healing</span>
                </h3>
                <p class="text-xs text-slate-600 mt-0.5">Cleans compiled views, routes, and application cache. Use this anytime if a view feels stale or after copying new update files.</p>
            </div>
            <button type="button" id="btnResyncCache" onclick="runSystemResync(this)" class="w-full sm:w-60 min-w-[240px] inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-4 rounded-xl text-xs transition shadow-sm shrink-0 cursor-pointer whitespace-nowrap">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <span>Re-Sync System Cache</span>
            </button>
        </div>

        <!-- 🧹 Auto-Clean Old Activity Logs -->
        <div class="border-t border-slate-100 pt-6 mt-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-amber-50/50 p-4 rounded-xl border border-amber-200/80">
            <div class="space-y-1">
                <h3 class="text-sm font-bold text-slate-800 flex items-center gap-1.5">
                    <span>🧹 Auto-Clean Old Activity Logs</span>
                    <span class="px-2 py-0.5 bg-amber-100 text-amber-800 text-[10px] font-extrabold rounded-md">Storage Cleanup</span>
                </h3>
                <p class="text-xs text-slate-600">Automatically removes old activity logs to prevent disk clutter and keep database queries lightning-fast.</p>
                <div class="flex items-center gap-2 pt-1">
                    <label for="log_retention_days" class="text-xs font-semibold text-slate-700">Keep Logs For:</label>
                    <select id="log_retention_days" class="bg-white border border-slate-300 rounded-lg py-1 px-2.5 text-xs font-bold text-slate-800">
                        <option value="30" {{ \App\Models\Setting::get('audit_log_retention_days', '90') == '30' ? 'selected' : '' }}>30 Days (1 Month)</option>
                        <option value="90" {{ \App\Models\Setting::get('audit_log_retention_days', '90') == '90' ? 'selected' : '' }}>90 Days (3 Months - Recommended)</option>
                        <option value="180" {{ \App\Models\Setting::get('audit_log_retention_days') == '180' ? 'selected' : '' }}>180 Days (6 Months)</option>
                        <option value="365" {{ \App\Models\Setting::get('audit_log_retention_days') == '365' ? 'selected' : '' }}>365 Days (1 Year)</option>
                    </select>
                </div>
            </div>
            <button type="button" id="btnPruneLogs" onclick="runSystemPrune(this)" class="w-full sm:w-60 min-w-[240px] inline-flex items-center justify-center gap-2 bg-amber-600 hover:bg-amber-700 text-white font-bold py-2.5 px-4 rounded-xl text-xs transition shadow-sm shrink-0 cursor-pointer whitespace-nowrap">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                <span>Clean Old Logs Now</span>
            </button>
        </div>
    </div>
</div>

<script>
async function runSystemResync(btn) {
    if (!confirm('⚡ Re-sync system and clear all cached views and routes now?')) return;
    const origHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span>⚡ Re-syncing...</span>';

    try {
        const res = await fetch('{{ route('settings.resync') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        });
        const data = await res.json();
        if (data.success) {
            if (window.showToast) window.showToast('success', data.message);
            else alert(data.message);
        } else {
            if (window.showToast) window.showToast('danger', data.message || 'Failed to re-sync.');
            else alert(data.message);
        }
    } catch (err) {
        if (window.showToast) window.showToast('danger', 'Network error during system re-sync.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = origHtml;
    }
}

async function runSystemPrune(btn) {
    const days = document.getElementById('log_retention_days')?.value || '90';
    if (!confirm(`🧹 Are you sure you want to clean activity logs older than ${days} days?`)) return;

    const origHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span>🧹 Cleaning...</span>';

    try {
        const res = await fetch('{{ route('settings.prune') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ retention_days: days })
        });
        const data = await res.json();
        if (data.success) {
            if (window.showToast) window.showToast('success', data.message);
            else alert(data.message);
        } else {
            if (window.showToast) window.showToast('danger', data.message || 'Failed to clean logs.');
            else alert(data.message);
        }
    } catch (err) {
        if (window.showToast) window.showToast('danger', 'Network error during log cleanup.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = origHtml;
    }
}
</script>
