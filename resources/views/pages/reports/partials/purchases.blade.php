<!-- 2. PURCHASE REPORT VIEW PARTIAL -->
<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Raw Materials Total</span>
            <span class="text-lg font-black text-slate-800 block mt-1">₹{{ number_format($purchaseSummary['total_raw_material'], 2) }}</span>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Machinery & Tools</span>
            <span class="text-lg font-black text-slate-800 block mt-1">₹{{ number_format($purchaseSummary['total_machinery'] + $purchaseSummary['total_supplies'], 2) }}</span>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Purchase GST Paid (ITC)</span>
            <span class="text-lg font-black text-blue-600 block mt-1">₹{{ number_format($purchaseSummary['total_gst'], 2) }}</span>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Total Spent Amount</span>
            <span class="text-lg font-black text-rose-600 block mt-1">₹{{ number_format($purchaseSummary['total_spent'], 2) }}</span>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 p-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
            <h3 class="text-base font-bold text-slate-800 flex items-center">
                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                Logged Purchase Ledger Invoice Records
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
        <div class="overflow-x-auto w-full max-w-full border border-slate-200 rounded-xl">
            <table class="erp-datatable min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-4 py-3 text-left font-bold text-xs">Bill / Invoice No.</th>
                        <th class="px-4 py-3 text-left font-bold text-xs">Supplier Name</th>
                        <th class="px-4 py-3 text-left font-bold text-xs">Category</th>
                        <th class="px-4 py-3 text-left font-bold text-xs">Item Name</th>
                        <th class="px-4 py-3 text-right font-bold text-xs">Qty</th>
                        <th class="px-4 py-3 text-center font-bold text-xs">GST Rate</th>
                        <th class="px-4 py-3 text-right font-bold text-xs">GST Amount</th>
                        <th class="px-4 py-3 text-right font-bold text-xs">Total Amount</th>
                        <th class="px-4 py-3 text-center font-bold text-xs">Purchase Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($purchases as $pur)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="px-4 py-3 font-mono text-slate-700 font-bold text-xs">{{ $pur->bill_number ?? 'N/A' }}</td>
                            <td class="px-4 py-3 text-xs font-bold text-slate-800">{{ $pur->vendor_name }}</td>
                            <td class="px-4 py-3 text-xs">
                                <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider {{ $pur->purchase_type === 'raw_material' ? 'bg-blue-50 text-blue-800 border border-blue-200' : ($pur->purchase_type === 'machinery' ? 'bg-amber-50 text-amber-800 border border-amber-200' : 'bg-slate-100 text-slate-800') }}">
                                    {{ str_replace('_', ' ', $pur->purchase_type) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-xs text-slate-600 font-medium">{{ $pur->item_name }}</td>
                            <td class="px-4 py-3 text-right text-xs font-medium text-slate-700">
                                {{ number_format($pur->quantity, 1) }} <span class="text-slate-400">{{ $pur->unit }}</span>
                            </td>
                            <td class="px-4 py-3 text-center text-xs font-bold text-slate-500">{{ number_format($pur->gst_rate, 0) }}%</td>
                            <td class="px-4 py-3 text-right text-xs font-semibold text-blue-600">₹{{ number_format($pur->gst_amount, 2) }}</td>
                            <td class="px-4 py-3 text-right text-xs font-bold text-slate-900">₹{{ number_format($pur->total_amount, 2) }}</td>
                            <td class="px-4 py-3 text-center text-xs text-slate-500">{{ $pur->purchase_date->format('d/m/Y') }}</td>
                        </tr>
                    @empty
                        <tr class="empty-row">
                            <td colspan="9" class="px-6 py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center space-y-2">
                                    <svg class="w-10 h-10 mx-auto text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                    </svg>
                                    <p class="text-sm font-bold text-slate-600">No Records Found</p>
                                    <p class="text-xs text-slate-400">There are no purchase records matching your selected report filter criteria.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
