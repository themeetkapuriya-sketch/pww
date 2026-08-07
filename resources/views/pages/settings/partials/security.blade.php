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
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Session Inactivity Timeout (Minutes)</label>
                    <input type="number" name="session_timeout_minutes" value="{{ \App\Models\Setting::get('session_timeout_minutes', '120') }}" min="15" max="1440" required class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-semibold text-slate-800 focus:bg-white focus:border-blue-500 transition">
                    <span class="text-[11px] text-slate-400">Auto-logout inactive users (Default: 120 mins)</span>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Max Failed Login Attempts</label>
                    <input type="number" name="max_login_attempts" value="{{ \App\Models\Setting::get('max_login_attempts', '5') }}" min="3" max="10" required class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-semibold text-slate-800 focus:bg-white focus:border-blue-500 transition">
                    <span class="text-[11px] text-slate-400">Lock user IP temporarily after N bad passwords</span>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Automated Database Backup Frequency</label>
                    @php
                        $currEnabled = \App\Models\Setting::get('auto_backup_enabled', 'true') === 'true';
                        $currFreq = $currEnabled ? \App\Models\Setting::get('auto_backup_frequency', 'daily') : 'disabled';
                    @endphp
                    <select name="auto_backup_frequency" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-semibold text-slate-800 focus:bg-white focus:border-blue-500 transition">
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
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Backup Retention Period</label>
                    @php $currRetention = \App\Models\Setting::get('auto_backup_retention', '3_months'); @endphp
                    <select name="auto_backup_retention" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-semibold text-slate-800 focus:bg-white focus:border-blue-500 transition">
                        <option value="1_month" {{ $currRetention === '1_month' ? 'selected' : '' }}>1 Month (Keep 30 Days)</option>
                        <option value="3_months" {{ $currRetention === '3_months' ? 'selected' : '' }}>3 Months (Keep 90 Days)</option>
                        <option value="6_months" {{ $currRetention === '6_months' ? 'selected' : '' }}>6 Months (Keep 180 Days)</option>
                        <option value="1_year" {{ $currRetention === '1_year' ? 'selected' : '' }}>1 Year (Keep 365 Days)</option>
                        <option value="never" {{ $currRetention === 'never' ? 'selected' : '' }}>Never Delete (Keep Indefinitely)</option>
                    </select>
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
            <a href="{{ route('backup.full') }}" class="no-ajax bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 px-5 rounded-xl text-xs transition shadow-sm flex items-center gap-2 shrink-0" download>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                <span>Download Instant Database Backup</span>
            </a>
        </div>
    </div>
</div>
