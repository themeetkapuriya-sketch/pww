<!-- MODAL: ISSUE SALARY ADVANCE & AUTO-LOG EXPENSE -->
<div id="giveAdvanceModal" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm hidden flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-3xl p-6 max-w-md w-full shadow-2xl border border-slate-100 space-y-4 max-h-[88vh] overflow-y-auto my-auto custom-scrollbar">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                <span>Issue Salary Advance</span>
            </h3>
            <button onclick="closeAdvanceModal()" class="text-slate-400 hover:text-slate-600 text-xl font-bold cursor-pointer">&times;</button>
        </div>

        <form action="{{ route('employees.advance.store') }}" method="POST" class="ajax-form space-y-4" id="advanceForm" data-close-modal="#giveAdvanceModal" novalidate>
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Select Employee <span class="text-rose-500">*</span></label>
                <select name="staff_profile_id" id="advanceStaffSelect" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500" required>
                    <option value="">Select Employee...</option>
                    @foreach(($activeStaffProfiles ?? $staffProfiles->where('is_active', true)) as $sp)
                        <option value="{{ $sp->id }}">{{ $sp->full_name }} ({{ $sp->wage_type === 'per-day' ? 'Per Day' : 'Fixed' }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Advance Amount -->
            <div class="space-y-1">
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">
                    Advance Amount (₹) <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-sm font-bold text-slate-500">₹</span>
                    <input type="number" name="amount" id="advanceAmountInput" step="0.01" min="1" placeholder="e.g. 5000.00" required class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 pl-8 pr-3.5 text-lg font-black text-amber-600 font-mono focus:outline-none focus:ring-2 focus:ring-amber-500">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Payment Method</label>
                    <select name="payment_method" id="advanceMethodSelect" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500">
                        <option value="Cash">Cash</option>
                        <option value="Bank Transfer">Bank Transfer</option>
                        <option value="UPI">UPI / GPay</option>
                        <option value="Cheque">Cheque</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Advance Date</label>
                    <input type="date" name="advance_date" id="advanceDateInput" value="{{ now()->toDateString() }}" required class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Notes / Reason (Optional)</label>
                <input type="text" name="notes" id="advanceNotesInput" placeholder="e.g. Mid-month personal advance" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-xs font-medium text-slate-800">
            </div>

            <div class="p-3 bg-amber-50 border border-amber-200 rounded-2xl text-xs text-amber-800 font-semibold flex items-center gap-2">
                <svg class="w-5 h-5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>Issuing an advance automatically logs an expense under <strong>Employee Salary Advance</strong> in your Expenses Ledger.</span>
            </div>

            <div class="pt-3 border-t border-slate-100 flex justify-end gap-2">
                <button type="button" onclick="closeAdvanceModal()" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition cursor-pointer">Cancel</button>
                <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white font-bold py-2.5 px-6 rounded-xl text-xs shadow-xs transition cursor-pointer">Issue Advance</button>
            </div>
        </form>
    </div>
</div>
