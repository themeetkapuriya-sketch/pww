<!-- MODAL: INDIVIDUAL EMPLOYEE LEDGER STATEMENT & PASSBOOK -->
<div id="empStatementModal" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm hidden flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-3xl p-6 max-w-4xl w-full shadow-2xl border border-slate-100 space-y-6 max-h-[92vh] overflow-y-auto my-auto custom-scrollbar">
        
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-slate-100 pb-4 gap-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-blue-100 text-blue-700 font-black text-xl flex items-center justify-center uppercase shrink-0 shadow-xs" id="stmtAvatar">
                    E
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-lg font-bold text-slate-800" id="stmtEmpName">Employee Name</h3>
                        <span id="stmtStatusBadge" class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[10px] font-bold uppercase bg-emerald-50 text-emerald-700 border border-emerald-200">
                            Active
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 font-medium flex items-center gap-3 mt-0.5">
                        <span>📱 <span id="stmtEmpMobile">N/A</span></span>
                        <span>•</span>
                        <span id="stmtWageTypeBadge" class="font-bold text-blue-600">Per Day Wage</span>
                    </p>
                </div>
            </div>

            <button onclick="closeStatementModal()" class="text-slate-400 hover:text-slate-600 text-2xl font-bold cursor-pointer self-start sm:self-auto">&times;</button>
        </div>

        <!-- 4 Key Financial Metric Cards (Single Employee Summary) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- 1. Current Salary Rate -->
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-1">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold uppercase text-slate-500">Current Salary Rate</span>
                    <span class="p-1 rounded-lg bg-blue-100 text-blue-600">💳</span>
                </div>
                <p class="text-lg font-black text-slate-800 font-mono" id="stmtCurrentRate">₹0.00</p>
                <p class="text-[10px] text-slate-400 font-medium">Configured contract rate</p>
            </div>

            <!-- 2. Advance Paid / Pending -->
            <div class="p-4 rounded-2xl bg-amber-50/70 border border-amber-200/80 space-y-1">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold uppercase text-amber-800">Advance Paid / Pending</span>
                    <span class="p-1 rounded-lg bg-amber-100 text-amber-700">💸</span>
                </div>
                <p class="text-lg font-black text-amber-700 font-mono" id="stmtAdvancePaid">₹0.00</p>
                <p class="text-[10px] text-amber-600/80 font-medium">Outstanding advances to deduct</p>
            </div>

            <!-- 3. Current Month Gross Earnings -->
            <div class="p-4 rounded-2xl bg-blue-50/70 border border-blue-200/80 space-y-1">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold uppercase text-blue-800">Month Gross Earnings</span>
                    <span class="p-1 rounded-lg bg-blue-100 text-blue-700">📊</span>
                </div>
                <p class="text-lg font-black text-blue-800 font-mono" id="stmtGrossEarnings">₹0.00</p>
                <p class="text-[10px] text-blue-600/80 font-medium"><span id="stmtDaysPresent">0.0</span> Days Present in <span id="stmtSelectedMonthLabel">Month</span></p>
            </div>

            <!-- 4. Net Due Amount -->
            <div id="stmtNetDueCard" class="p-4 rounded-2xl bg-rose-50/70 border border-rose-200/80 space-y-1">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold uppercase text-rose-800">Net Due Amount</span>
                    <span class="p-1 rounded-lg bg-rose-100 text-rose-700">🚨</span>
                </div>
                <p class="text-lg font-black text-rose-700 font-mono" id="stmtNetDueAmount">₹0.00</p>
                <p class="text-[10px] text-rose-600/80 font-medium">Net payable after deductions</p>
            </div>
        </div>

        <!-- Filter Period Control Bar -->
        <div class="p-3 bg-slate-50/90 rounded-2xl border border-slate-200/80 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                    <span>Filter Statement Ledger Period:</span>
                </span>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <!-- Range Preset Select (Defaults to Current Month) -->
                <select id="stmtRangeSelect" onchange="onStatementFilterChange()" class="px-3 py-1.5 rounded-xl border border-slate-300 text-xs font-bold text-slate-700 bg-white focus:ring-2 focus:ring-blue-500 shadow-2xs cursor-pointer">
                    <option value="current_month" selected>📅 Current Month (Default)</option>
                    <option value="last_3_months">🗓️ Last 3 Months</option>
                    <option value="this_year">📆 This Year</option>
                    <option value="all">📂 All Time Records</option>
                </select>

                <!-- Month Picker Input -->
                <input type="month" id="stmtMonthInput" onchange="onStatementMonthPickerChange()" value="{{ date('Y-m') }}" class="px-3 py-1.5 rounded-xl border border-slate-300 text-xs font-bold font-mono text-slate-700 bg-white focus:ring-2 focus:ring-blue-500 shadow-2xs cursor-pointer">
            </div>
        </div>

        <!-- Transaction Passbook Table -->
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <h4 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <span>Passbook Transaction History</span>
                </h4>
                <span class="text-xs text-slate-500 font-semibold" id="stmtTxnCount">0 records</span>
            </div>

            <div class="overflow-x-auto w-full border border-slate-200/80 rounded-2xl">
                <table class="min-w-full divide-y divide-slate-200 text-xs">
                    <thead class="bg-slate-50 text-slate-700 font-bold uppercase">
                        <tr>
                            <th class="p-3 text-left">Date</th>
                            <th class="p-3 text-left">Transaction Type</th>
                            <th class="p-3 text-left">Description</th>
                            <th class="p-3 text-right">Gross Amount</th>
                            <th class="p-3 text-right">Deduction</th>
                            <th class="p-3 text-right">Net Amount</th>
                            <th class="p-3 text-center">Method</th>
                            <th class="p-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white" id="stmtTransactionsTableBody">
                        <tr>
                            <td colspan="8" class="p-6 text-center text-slate-400">Loading statement history...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Footer Actions -->
        <div class="pt-4 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <button type="button" onclick="triggerAdvanceFromStatement()" class="bg-amber-600 hover:bg-amber-700 text-white font-bold py-2 px-4 rounded-xl text-xs shadow-xs transition flex items-center gap-1.5 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    <span>Issue Advance</span>
                </button>
                <button type="button" onclick="triggerPaymentFromStatement()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-xl text-xs shadow-xs transition flex items-center gap-1.5 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    <span>Pay Due Salary</span>
                </button>
            </div>

            <button type="button" onclick="closeStatementModal()" class="px-5 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition cursor-pointer">
                Close Statement
            </button>
        </div>

    </div>
</div>
