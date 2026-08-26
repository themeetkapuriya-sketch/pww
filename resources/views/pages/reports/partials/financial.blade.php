<!-- 3. FINANCIAL REPORT VIEW PARTIAL -->
<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 shadow-xs">
            <span class="text-[10px] font-bold text-slate-400 dark:text-slate-400 uppercase tracking-wider block">Accounts Receivable (Client Dues)</span>
            <span class="text-xl font-black text-amber-600 block mt-1">₹{{ number_format($financials['outstanding_receivables'] ?? 0, 2) }}</span>
            <span class="text-[10px] text-slate-400 dark:text-slate-500">Total unpaid & partial invoices</span>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 shadow-xs">
            <span class="text-[10px] font-bold text-slate-400 dark:text-slate-400 uppercase tracking-wider block">Accounts Payable (Vendor Dues)</span>
            <span class="text-xl font-black text-rose-600 block mt-1">₹{{ number_format($financials['outstanding_payables'] ?? 0, 2) }}</span>
            <span class="text-[10px] text-slate-400 dark:text-slate-500">Total unpaid supplier bills</span>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 shadow-xs">
            <span class="text-[10px] font-bold text-slate-400 dark:text-slate-400 uppercase tracking-wider block">Bank Account Collections</span>
            <span class="text-xl font-black text-blue-600 block mt-1">₹{{ number_format($financials['bank_collections'] ?? 0, 2) }}</span>
            <span class="text-[10px] text-slate-400 dark:text-slate-500">Received via NEFT / UPI / Cheque</span>
        </div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 shadow-xs">
            <span class="text-[10px] font-bold text-slate-400 dark:text-slate-400 uppercase tracking-wider block">Cash In Hand Collections</span>
            <span class="text-xl font-black text-emerald-600 block mt-1">₹{{ number_format($financials['cash_collections'] ?? 0, 2) }}</span>
            <span class="text-[10px] text-slate-400 dark:text-slate-500">Received via liquid cash</span>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
            <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center">
                <svg class="w-5 h-5 mr-2 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 01-2-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Statement of Net Profit / Loss
            </h3>
            <div class="flex items-center space-x-2">
                <a href="{{ route('reports.export.pdf', ['start_date' => $startDate, 'end_date' => $endDate, 'filter_period' => $period, 'filter_month' => $filterMonth ?? '', 'filter_year' => $filterYear ?? '', 'report_type' => $reportType]) }}" 
                   class="p-1 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-xl transition hover:scale-105 flex items-center justify-center no-ajax"
                   title="Export PDF Document">
                    <svg class="w-6 h-6 shrink-0" viewBox="0 0 24 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M 2.5 3 C 2.5 1.343 3.843 0 5.5 0 L 15.5 0 L 23.5 8 L 23.5 25 C 23.5 26.657 22.157 28 20.5 28 L 5.5 28 C 3.843 28 2.5 26.657 2.5 25 Z" fill="#DC2626"/>
                        <path d="M 15.5 0 L 23.5 8 L 15.5 8 Z" fill="#F87171"/>
                        <text x="13" y="19" font-family="system-ui, -apple-system, sans-serif" font-weight="900" font-size="7.5" fill="#FFFFFF" text-anchor="middle" letter-spacing="-0.2">PDF</text>
                    </svg>
                </a>
                <a href="{{ route('reports.export', ['start_date' => $startDate, 'end_date' => $endDate, 'filter_period' => $period, 'filter_month' => $filterMonth ?? '', 'filter_year' => $filterYear ?? '', 'report_type' => $reportType]) }}" 
                   class="p-1 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 rounded-xl transition hover:scale-105 flex items-center justify-center no-ajax"
                   title="Export CSV File">
                    <svg class="w-6 h-6 shrink-0" viewBox="0 0 24 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M 2.5 3 C 2.5 1.343 3.843 0 5.5 0 L 15.5 0 L 23.5 8 L 23.5 25 C 23.5 26.657 22.157 28 20.5 28 L 5.5 28 C 3.843 28 2.5 26.657 2.5 25 Z" fill="#047857"/>
                        <path d="M 15.5 0 L 23.5 8 L 15.5 8 Z" fill="#34D399"/>
                        <rect x="7" y="11" width="4" height="2" rx="0.5" fill="#FFFFFF"/>
                        <rect x="13" y="11" width="4" height="2" rx="0.5" fill="#FFFFFF"/>
                        <rect x="7" y="14.5" width="4" height="2" rx="0.5" fill="#FFFFFF"/>
                        <rect x="13" y="14.5" width="4" height="2" rx="0.5" fill="#FFFFFF"/>
                        <rect x="7" y="18" width="4" height="2" rx="0.5" fill="#FFFFFF"/>
                        <rect x="13" y="18" width="4" height="2" rx="0.5" fill="#FFFFFF"/>
                        <rect x="7" y="21.5" width="4" height="2" rx="0.5" fill="#FFFFFF"/>
                        <rect x="13" y="21.5" width="4" height="2" rx="0.5" fill="#FFFFFF"/>
                    </svg>
                </a>
            </div>
        </div>
        <div class="overflow-x-auto w-full max-w-full border border-slate-200 dark:border-slate-800 rounded-xl">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800 text-sm">
                <thead class="bg-[#EDF4FA] dark:bg-slate-800 text-black dark:text-slate-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase">Accounting Item</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase">Description</th>
                        <th class="px-6 py-3 text-right text-xs font-bold uppercase">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800 bg-white dark:bg-slate-900">
                    <tr>
                        <td class="px-6 py-4 font-semibold text-slate-800 dark:text-slate-200">Total Billed Sales (A)</td>
                        <td class="px-6 py-4 text-slate-500 dark:text-slate-400 text-xs">Sum of total invoiced amounts of all generated compliance invoices. (Includes GST tax)</td>
                        <td class="px-6 py-4 text-right font-bold text-emerald-600 dark:text-emerald-400">₹{{ format_indian($financials['revenue'], 2) }}</td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 font-semibold text-slate-800 dark:text-slate-200">Total Purchases (B)</td>
                        <td class="px-6 py-4 text-slate-500 dark:text-slate-400 text-xs">Total outlay for raw materials, machinery, tools, and vendor inventory purchases.</td>
                        <td class="px-6 py-4 text-right font-bold text-rose-600 dark:text-rose-400">- ₹{{ format_indian($financials['purchases'], 2) }}</td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 font-semibold text-slate-800 dark:text-slate-200">Total Expenses (C)</td>
                        <td class="px-6 py-4 text-slate-500 dark:text-slate-400 text-xs">Total operational overheads (salaries, electricity, transport, administration, etc.).</td>
                        <td class="px-6 py-4 text-right font-bold text-rose-600 dark:text-rose-400">- ₹{{ format_indian($financials['expenses'], 2) }}</td>
                    </tr>
                    <tr class="bg-slate-50 dark:bg-slate-800/60 font-bold border-t border-slate-200 dark:border-slate-700">
                        <td class="px-6 py-4 text-slate-800 dark:text-slate-100 text-base">Net Profit / Revenue (A - B - C)</td>
                        <td class="px-6 py-4 text-slate-500 dark:text-slate-400 text-xs">PWW net earnings (Total Sales − Purchases − Expenses) for this audit period.</td>
                        <td class="px-6 py-4 text-right text-base {{ $financials['net_profit'] >= 0 ? 'text-emerald-700 dark:text-emerald-400' : 'text-rose-700 dark:text-rose-400' }}">
                            ₹{{ format_indian($financials['net_profit'], 2) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
