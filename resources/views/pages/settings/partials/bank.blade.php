<!-- TAB 2: Bank & Billing Defaults Partial -->
<div id="settingsTab-bank" class="tab-content hidden space-y-6">
    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-6">
        <h2 class="text-lg font-bold text-slate-800 border-b border-slate-100 pb-4">Bank Account & Default Billing Terms</h2>

        <form id="bankBillingForm" action="{{ route('settings.bank') }}" method="POST" class="space-y-6" novalidate onsubmit="return handleBankBillingSubmit(event);">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Bank Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="bank_name" value="{{ \App\Models\Setting::get('bank_name', 'JIVAN COMMERCIAL CO OP BANK LTD') }}" required class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-semibold text-slate-800 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Account Holder Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="bank_account_name" value="{{ \App\Models\Setting::get('bank_account_name', 'Praful Welding Works') }}" required class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-semibold text-slate-800 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Account Number <span class="text-rose-500">*</span></label>
                    <input type="text" name="bank_account_no" value="{{ \App\Models\Setting::get('bank_account_no', '443005101001972') }}" required class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-semibold text-slate-800 font-mono focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">IFSC Code <span class="text-rose-500">*</span></label>
                    <input type="text" name="bank_ifsc" value="{{ \App\Models\Setting::get('bank_ifsc', 'IBKL0JIVAN3') }}" required maxlength="11" oninput="this.value = this.value.toUpperCase()" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-semibold text-slate-800 font-mono uppercase focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Invoice Terms & Conditions <span class="text-rose-500">*</span></label>
                    <textarea name="terms_and_conditions" rows="4" required class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm font-medium text-slate-800 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition">{{ \App\Models\Setting::get('terms_and_conditions', '') }}</textarea>
                </div>
            </div>

            <div class="pt-4 flex justify-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-xl text-sm transition shadow-sm cursor-pointer">
                    Save Bank Defaults
                </button>
            </div>
        </form>
    </div>
</div>
