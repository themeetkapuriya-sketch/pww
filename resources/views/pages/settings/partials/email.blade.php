<!-- Sub Content 3: Email / SMTP Settings Partial -->
<div id="subTab-email" class="sub-tab-content {{ ($activeSubTab ?? 'serials') === 'email' ? '' : 'hidden' }} space-y-6">
    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-6">
        <div>
            <h2 class="text-lg font-bold text-slate-800">Automatic Outbound Email Settings</h2>
            <p class="text-slate-500 text-xs mt-1">Configure your official email account to send tax invoices, sales orders, and reports directly to clients.</p>
        </div>

        <form id="emailSettingsForm" action="{{ route('settings.email') }}" method="POST" class="space-y-6" novalidate onsubmit="return handleEmailSettingsSubmit(event);">
            @csrf
            
            <!-- Email Provider Selection Preset -->
            <div class="bg-blue-50/60 p-4 rounded-xl border border-blue-100 space-y-3">
                <label class="block text-xs font-bold text-blue-900 uppercase">Select Email Service Provider</label>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <label onclick="applyEmailPreset('gmail')" class="flex items-center gap-3 p-3 bg-white border border-slate-200 rounded-xl cursor-pointer hover:border-blue-500 transition">
                        <input type="radio" name="email_provider_preset" value="gmail" checked class="w-4 h-4 text-blue-600">
                        <div>
                            <span class="block text-xs font-bold text-slate-800">Gmail / Google Workspace</span>
                            <span class="block text-[11px] text-slate-400">1-Click Auto Configured</span>
                        </div>
                    </label>

                    <label onclick="applyEmailPreset('outlook')" class="flex items-center gap-3 p-3 bg-white border border-slate-200 rounded-xl cursor-pointer hover:border-blue-500 transition">
                        <input type="radio" name="email_provider_preset" value="outlook" class="w-4 h-4 text-blue-600">
                        <div>
                            <span class="block text-xs font-bold text-slate-800">Outlook / Office 365</span>
                            <span class="block text-[11px] text-slate-400">Microsoft Mail</span>
                        </div>
                    </label>

                    <label onclick="applyEmailPreset('custom')" class="flex items-center gap-3 p-3 bg-white border border-slate-200 rounded-xl cursor-pointer hover:border-blue-500 transition">
                        <input type="radio" name="email_provider_preset" value="custom" class="w-4 h-4 text-blue-600">
                        <div>
                            <span class="block text-xs font-bold text-slate-800">Custom Server</span>
                            <span class="block text-[11px] text-slate-400">Advanced Server Options</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Simple Inputs for Non-Technical Users -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Your Official Email Address <span class="text-rose-500">*</span></label>
                    <input type="email" name="mail_from_address" value="{{ \App\Models\Setting::get('mail_from_address', 'vekariyah@gmail.com') }}" placeholder="e.g. vekariyah@gmail.com" required class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-semibold text-slate-800 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Company / Sender Display Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="mail_from_name" value="{{ \App\Models\Setting::get('mail_from_name', 'Praful Welding Works') }}" placeholder="e.g. Praful Welding Works" required class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-semibold text-slate-800 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition">
                </div>

                <div class="md:col-span-2">
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-xs font-bold text-slate-600 uppercase">Google App Password (16 Characters)</label>
                        <a href="https://myaccount.google.com/apppasswords" target="_blank" class="text-[11px] font-bold text-blue-600 hover:underline flex items-center gap-1">
                            <span>Get Google App Password ↗</span>
                        </a>
                    </div>
                    <div class="relative">
                        @php
                            $rawMailPassword = \App\Models\Setting::get('mail_password', '');
                            $displayMailPassword = '';
                            if (!empty($rawMailPassword)) {
                                try {
                                    $displayMailPassword = \Illuminate\Support\Facades\Crypt::decryptString($rawMailPassword);
                                } catch (\Throwable $e) {
                                    $displayMailPassword = $rawMailPassword;
                                }
                            }
                        @endphp
                        <input type="password" id="smtpPasswordInput" name="mail_password" value="{{ $displayMailPassword }}" placeholder="Enter 16-character App Password (e.g. abcd efgh ijkl mnop)" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 pl-3.5 pr-10 text-sm font-semibold text-slate-800 font-mono focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition">
                        <button type="button" onclick="togglePasswordVisibility('smtpPasswordInput', 'smtpEyeIcon', 'smtpEyeOffIcon')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer" title="Toggle password visibility">
                            <svg id="smtpEyeIcon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg id="smtpEyeOffIcon" class="w-4 h-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                            </svg>
                        </button>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">For security, Gmail requires an <strong>App Password</strong> instead of your personal email password.</p>
                </div>
            </div>

            <!-- Hidden / Collapsible Advanced Technical Parameters -->
            <div class="border-t border-slate-100 pt-4">
                <button type="button" onclick="document.getElementById('advancedSmtpSection').classList.toggle('hidden')" class="text-xs font-bold text-slate-500 hover:text-slate-800 flex items-center gap-1.5 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    </svg>
                    <span>Advanced Technical Parameters (Optional)</span>
                </button>

                <div id="advancedSmtpSection" class="hidden grid grid-cols-1 md:grid-cols-3 gap-4 mt-4 bg-slate-50 p-4 rounded-xl border border-slate-200">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">SMTP Host Server</label>
                        <input type="text" id="smtpHostInput" name="mail_host" value="{{ \App\Models\Setting::get('mail_host', 'smtp.gmail.com') }}" required class="w-full bg-white border border-slate-200 rounded-xl py-2 px-3 text-xs font-semibold text-slate-800 font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">SMTP Port</label>
                        <input type="number" id="smtpPortInput" name="mail_port" value="{{ \App\Models\Setting::get('mail_port', '587') }}" required class="w-full bg-white border border-slate-200 rounded-xl py-2 px-3 text-xs font-semibold text-slate-800 font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Encryption Protocol</label>
                        <select id="smtpEncryptionInput" name="mail_encryption" class="w-full bg-white border border-slate-200 rounded-xl py-2 px-3 text-xs font-semibold text-slate-800">
                            <option value="tls" {{ \App\Models\Setting::get('mail_encryption', 'tls') === 'tls' ? 'selected' : '' }}>TLS (Port 587)</option>
                            <option value="ssl" {{ \App\Models\Setting::get('mail_encryption') === 'ssl' ? 'selected' : '' }}>SSL (Port 465)</option>
                            <option value="none" {{ \App\Models\Setting::get('mail_encryption') === 'none' ? 'selected' : '' }}>None</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="pt-4 flex justify-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-xl text-sm transition shadow-sm cursor-pointer">
                    Save Email Settings
                </button>
            </div>
        </form>

        <!-- Diagnostics Test Email Form -->
        <div class="border-t border-slate-100 pt-6 mt-6">
            <h3 class="text-sm font-bold text-slate-800 mb-2">Send Diagnostic Test Email</h3>
            <p class="text-xs text-slate-500 mb-4">Verify your mail server connectivity by sending an instant test message.</p>

            <form id="testEmailForm" action="{{ route('settings.email.test') }}" method="POST" class="flex flex-col sm:flex-row gap-3" onsubmit="return handleTestEmailSubmit(event);">
                @csrf
                <input type="email" name="test_email" value="{{ auth()->user()->email }}" placeholder="Enter recipient email address" required class="flex-1 bg-slate-50 border border-slate-200 rounded-xl py-2 px-3.5 text-sm font-semibold text-slate-800 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition">
                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-5 rounded-xl text-xs transition shadow-sm flex items-center justify-center gap-2 shrink-0 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                    <span>Send Test Email</span>
                </button>
            </form>
        </div>
    </div>
</div>
