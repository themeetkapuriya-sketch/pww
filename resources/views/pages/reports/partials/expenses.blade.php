<!-- 4. EXPENSE REPORT VIEW PARTIAL -->
<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <div class="flex items-center space-x-2">
                <span class="w-2.5 h-2.5 bg-rose-500 rounded-full"></span>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Expense Outflow</span>
            </div>
            <span class="text-xl font-black text-rose-600 block mt-2">₹{{ number_format($expenseSummary['total_spent'], 2) }}</span>
            <p class="text-[10px] text-slate-400 mt-1">Total factory overheads logged in period.</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <div class="flex items-center space-x-2">
                <span class="w-2.5 h-2.5 bg-blue-500 rounded-full"></span>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Logged Entries</span>
            </div>
            <span class="text-xl font-black text-slate-800 block mt-2">{{ number_format($expenseSummary['total_count']) }} Items</span>
            <p class="text-[10px] text-slate-400 mt-1">Number of expense records logged.</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <div class="flex items-center space-x-2">
                <span class="w-2.5 h-2.5 bg-amber-500 rounded-full"></span>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Salary Expenses</span>
            </div>
            @php
                $salarySpent = $expenseSummary['by_category']->get('salary') ?? 0;
            @endphp
            <span class="text-xl font-black text-slate-800 block mt-2">₹{{ number_format($salarySpent, 2) }}</span>
            <p class="text-[10px] text-slate-400 mt-1">Total salaries and wages paid in period.</p>
        </div>
    </div>

    <!-- Detailed Expenses Table -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
            <h3 class="text-sm font-bold text-blue-600 flex items-center">
                <svg class="w-4 h-4 mr-1.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                Detailed Expense Ledger
            </h3>
            <div class="flex items-center space-x-2">
                <a href="{{ route('reports.export.pdf', ['start_date' => $startDate, 'end_date' => $endDate, 'filter_period' => $period, 'filter_month' => $filterMonth ?? '', 'filter_year' => $filterYear ?? '', 'report_type' => $reportType]) }}" 
                   class="p-1 hover:bg-rose-50 rounded-xl transition hover:scale-105 flex items-center justify-center no-ajax"
                   title="Export PDF Document">
                    <svg class="w-6 h-6 shrink-0" viewBox="0 0 24 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M 2.5 3 C 2.5 1.343 3.843 0 5.5 0 L 15.5 0 L 23.5 8 L 23.5 25 C 23.5 26.657 22.157 28 20.5 28 L 5.5 28 C 3.843 28 2.5 26.657 2.5 25 Z" fill="#DC2626"/>
                        <path d="M 15.5 0 L 23.5 8 L 15.5 8 Z" fill="#F87171"/>
                        <text x="13" y="19" font-family="system-ui, -apple-system, sans-serif" font-weight="900" font-size="7.5" fill="#FFFFFF" text-anchor="middle" letter-spacing="-0.2">PDF</text>
                    </svg>
                </a>
                <a href="{{ route('reports.export', ['start_date' => $startDate, 'end_date' => $endDate, 'filter_period' => $period, 'filter_month' => $filterMonth ?? '', 'filter_year' => $filterYear ?? '', 'report_type' => $reportType]) }}" 
                   class="p-1 hover:bg-emerald-50 rounded-xl transition hover:scale-105 flex items-center justify-center no-ajax"
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
        <div class="overflow-x-auto w-full max-w-full">
            <table class="erp-datatable min-w-full divide-y divide-slate-200 text-xs">
                <thead class="bg-[#EDF4FA] text-black">
                    <tr>
                        <th class="px-4 py-2.5 text-center font-bold uppercase w-12">#</th>
                        <th class="px-4 py-2.5 text-left font-bold uppercase">Expense Date</th>
                        <th class="px-4 py-2.5 text-left font-bold uppercase">Category</th>
                        <th class="px-4 py-2.5 text-left font-bold uppercase">Memo / Description</th>
                        <th class="px-4 py-2.5 text-right font-bold uppercase">Amount (₹)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($expenses as $exp)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-4 py-3 text-center font-bold text-slate-500">{{ $loop->iteration }}</td>
                            <td class="px-4 py-3 text-slate-700 font-medium whitespace-nowrap">{{ $exp->expense_date->format('d M Y') }}</td>
                            <td class="px-4 py-3 text-slate-800 font-semibold capitalize">{{ $exp->expense_category === 'gst_payment' ? 'GST Payment' : str_replace('_', ' ', $exp->expense_category) }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ $exp->description ?? 'N/A' }}</td>
                            <td class="px-4 py-3 text-right font-bold text-rose-600">₹{{ number_format($exp->amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-slate-400 font-medium">No expense records found for this period.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
