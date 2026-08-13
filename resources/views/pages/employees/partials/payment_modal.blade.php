<!-- MODAL: PAY SALARY & AUTO-LOG EXPENSE -->
<div id="paymentSalaryModal" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm hidden flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-3xl p-6 max-w-md w-full shadow-2xl border border-slate-100 space-y-4 max-h-[88vh] overflow-y-auto my-auto custom-scrollbar">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span id="paymentModalTitle">Process Salary Payment</span>
            </h3>
            <button onclick="closePaymentModal()" class="text-slate-400 hover:text-slate-600 text-xl font-bold cursor-pointer">&times;</button>
        </div>

        <form action="{{ route('employees.salary.payment') }}" method="POST" class="ajax-form space-y-4" id="paymentForm" data-close-modal="#paymentSalaryModal" novalidate>
            @csrf
            <input type="hidden" name="staff_profile_id" id="paymentStaffId">
            <input type="hidden" name="month_year" id="paymentMonthYear">
            <input type="hidden" name="payment_status" id="paymentStatusSelect" value="paid">

            <div class="p-3 bg-blue-50/70 border border-blue-100 rounded-2xl flex items-center justify-between">
                <div>
                    <p id="paymentEmployeeName" class="text-sm font-bold text-slate-800">Employee Name</p>
                    <p id="paymentWageDetails" class="text-xs text-slate-500 font-medium">Rate Details</p>
                </div>
                <span id="paymentMonthBadge" class="px-2.5 py-1 bg-white text-blue-700 text-xs font-bold rounded-lg border border-blue-200 font-mono">
                    2026-07
                </span>
            </div>

            <!-- Notice for dates where attendance was missing and automatically marked present -->
            <div id="paymentMissingDatesAlert" class="hidden p-3 bg-rose-50/80 border border-rose-200 rounded-2xl text-xs text-rose-800 font-semibold space-y-1">
                <div class="flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <span class="font-bold">Missing Attendance Dates (Automatically Treated as Present):</span>
                </div>
                <p id="paymentMissingDatesList" class="font-normal text-[11px] leading-relaxed text-rose-700 pl-5"></p>
            </div>

            <!-- Method 2: Manual / Auto Days Present Input -->
            <div id="paymentDaysContainer" class="space-y-1">
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">
                    Present Days Worked in Month
                </label>
                <input type="number" name="days_present" id="paymentDaysInput" step="0.5" min="0" max="31" oninput="calculateModalSalary()" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Outstanding Advance & Deduction Section -->
            <div id="paymentAdvanceContainer" class="p-3 bg-amber-50/80 border border-amber-200 rounded-2xl space-y-2">
                <div class="flex items-center justify-between text-xs font-bold text-amber-900">
                    <span>Outstanding Advance:</span>
                    <span id="paymentPendingAdvanceBadge" class="font-mono text-sm text-amber-700 font-black">₹0.00</span>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Advance Amount to Deduct (₹)</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-xs font-bold text-slate-400">₹</span>
                        <input type="number" name="advance_deduction" id="paymentAdvanceDeductionInput" step="0.01" min="0" value="0.00" oninput="calculateModalSalary()" class="w-full bg-white border border-amber-300 rounded-xl py-1.5 pl-7 pr-3 text-sm font-bold text-amber-900 font-mono focus:outline-none focus:ring-2 focus:ring-amber-500">
                    </div>
                </div>
            </div>

            <!-- Editable Net Salary Amount Input -->
            <div class="space-y-1">
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">
                    Net Salary Payable (₹) <span class="text-slate-400 font-normal lowercase">(after advance deduction)</span>
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-sm font-bold text-slate-500">₹</span>
                    <input type="number" name="total_salary" id="paymentTotalSalaryInput" step="0.01" min="0" placeholder="0.00" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 pl-8 pr-3.5 text-lg font-black text-emerald-600 font-mono focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Payment Method</label>
                    <select name="payment_method" id="paymentMethodSelect" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="Cash">Cash</option>
                        <option value="Bank Transfer">Bank Transfer</option>
                        <option value="UPI">UPI / GPay</option>
                        <option value="Cheque">Cheque</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Payment Date</label>
                    <input type="date" name="payment_date" id="paymentPaymentDate" value="{{ now()->toDateString() }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Notes / Memo (Optional)</label>
                <input type="text" name="notes" id="paymentNotes" placeholder="e.g. July salary paid via HDFC Bank" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-xs font-medium text-slate-800">
            </div>

            <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-2xl text-xs text-emerald-800 font-semibold flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>Paying salary automatically logs an expense under <strong>Employee Salary / Payroll</strong> in your Expenses Ledger.</span>
            </div>

            <div class="pt-3 border-t border-slate-100 flex justify-end gap-2">
                <button type="button" onclick="closePaymentModal()" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition cursor-pointer">Cancel</button>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-xl text-xs shadow-xs transition cursor-pointer">Pay Salary</button>
            </div>
        </form>
    </div>
</div>
