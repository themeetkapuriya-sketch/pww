<!-- 5. GST TAX REPORTS VIEW PARTIAL -->
<div class="space-y-6">
    @php
        $gstType = request('gst_type', 'gstr3b');
    @endphp

    <!-- GST Return Type Capsule Sub-Bar -->
    <div class="flex border-b border-slate-200 bg-white p-1.5 rounded-2xl shadow-xs space-x-1.5 mb-5">
        <a href="{{ route('reports', ['report_type' => 'gst', 'gst_type' => 'gstr3b', 'filter_period' => $period, 'filter_month' => $filterMonth ?? '', 'filter_year' => $filterYear ?? '', 'start_date' => $startDate, 'end_date' => $endDate]) }}" 
           data-preserve-scroll="true"
           class="flex-1 text-center py-2 px-3 rounded-xl text-xs font-bold transition {{ $gstType === 'gstr3b' ? 'bg-[#4371D7] text-white shadow-xs' : 'text-slate-600 hover:bg-slate-50' }}">
            ⚖️ GSTR-3B (Monthly Summary)
        </a>
        <a href="{{ route('reports', ['report_type' => 'gst', 'gst_type' => 'gstr1', 'filter_period' => $period, 'filter_month' => $filterMonth ?? '', 'filter_year' => $filterYear ?? '', 'start_date' => $startDate, 'end_date' => $endDate]) }}" 
           data-preserve-scroll="true"
           class="flex-1 text-center py-2 px-3 rounded-xl text-xs font-bold transition {{ $gstType === 'gstr1' ? 'bg-[#4371D7] text-white shadow-xs' : 'text-slate-600 hover:bg-slate-50' }}">
            📄 GSTR-1 (Sales Return)
        </a>
        <a href="{{ route('reports', ['report_type' => 'gst', 'gst_type' => 'gstr2', 'filter_period' => $period, 'filter_month' => $filterMonth ?? '', 'filter_year' => $filterYear ?? '', 'start_date' => $startDate, 'end_date' => $endDate]) }}" 
           data-preserve-scroll="true"
           class="flex-1 text-center py-2 px-3 rounded-xl text-xs font-bold transition {{ $gstType === 'gstr2' ? 'bg-[#4371D7] text-white shadow-xs' : 'text-slate-600 hover:bg-slate-50' }}">
            📦 GSTR-2 (Purchase ITC)
        </a>
    </div>

    @if ($gstType === 'gstr1')
        <!-- 5.1 GSTR-1 OUTWARD SUPPLIES VIEW -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Total B2B Taxable Supplies</span>
                <span class="text-xl font-black text-slate-800 block mt-1">₹{{ number_format($invoiceSummary['total_taxable'], 2) }}</span>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Output GST Liability Collected</span>
                <span class="text-xl font-black text-emerald-600 block mt-1">₹{{ number_format($invoiceSummary['total_gst'], 2) }}</span>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Total Outward Invoices</span>
                <span class="text-xl font-black text-blue-600 block mt-1">{{ count($invoices) }} Invoices</span>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                <h3 class="text-base font-bold text-slate-800 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    GSTR-1 Outward Sales Return Statement
                </h3>
                <div class="flex items-center space-x-2">
                    <a href="{{ route('reports.export.pdf', ['start_date' => $startDate, 'end_date' => $endDate, 'filter_period' => $period, 'filter_month' => $filterMonth ?? '', 'filter_year' => $filterYear ?? '', 'report_type' => 'gst', 'gst_type' => 'gstr1']) }}" 
                       class="p-1 hover:bg-rose-50 rounded-xl transition hover:scale-105 flex items-center justify-center no-ajax"
                       title="Export GSTR-1 PDF">
                        <x-icon-export-pdf class="w-6 h-6 shrink-0" />
                    </a>
                    <a href="{{ route('reports.export', ['start_date' => $startDate, 'end_date' => $endDate, 'filter_period' => $period, 'filter_month' => $filterMonth ?? '', 'filter_year' => $filterYear ?? '', 'report_type' => 'gst', 'gst_type' => 'gstr1']) }}" 
                       class="p-1 hover:bg-emerald-50 rounded-xl transition hover:scale-105 flex items-center justify-center no-ajax"
                       title="Export GSTR-1 CSV">
                        <x-icon-export-csv class="w-6 h-6 shrink-0" />
                    </a>
                </div>
            </div>
            <div class="overflow-x-auto w-full max-w-full border border-slate-200 rounded-xl">
                <table class="erp-datatable min-w-full divide-y divide-slate-200 text-xs">
                    <thead class="bg-[#EDF4FA] text-black">
                        <tr>
                            <th class="px-3 py-2.5 text-left font-bold uppercase">Invoice No.</th>
                            <th class="px-3 py-2.5 text-left font-bold uppercase">Client GSTIN</th>
                            <th class="px-3 py-2.5 text-left font-bold uppercase">Client Name</th>
                            <th class="px-3 py-2.5 text-left font-bold uppercase">Invoice Date</th>
                            <th class="px-3 py-2.5 text-right font-bold uppercase">Taxable Value (₹)</th>
                            <th class="px-3 py-2.5 text-right font-bold uppercase">CGST (9%)</th>
                            <th class="px-3 py-2.5 text-right font-bold uppercase">SGST (9%)</th>
                            <th class="px-3 py-2.5 text-right font-bold uppercase">IGST (18%)</th>
                            <th class="px-3 py-2.5 text-right font-bold uppercase">Total Bill (₹)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($invoices as $inv)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-3 py-2.5 font-bold text-blue-600 font-mono">{{ $inv->invoice_number }}</td>
                                <td class="px-3 py-2.5 font-mono text-slate-700">{{ $inv->plant->client->gstin ?? 'URP / Retail' }}</td>
                                <td class="px-3 py-2.5 text-slate-800 font-semibold">{{ $inv->plant->client->company_name ?? 'N/A' }}</td>
                                <td class="px-3 py-2.5 text-slate-500">{{ \Carbon\Carbon::parse($inv->invoice_date ?? $inv->created_at)->format('d/m/Y') }}</td>
                                <td class="px-3 py-2.5 text-right text-slate-700 font-medium">₹{{ number_format($inv->total_taxable_value, 2) }}</td>
                                <td class="px-3 py-2.5 text-right text-slate-500">₹{{ number_format($inv->cgst, 2) }}</td>
                                <td class="px-3 py-2.5 text-right text-slate-500">₹{{ number_format($inv->sgst, 2) }}</td>
                                <td class="px-3 py-2.5 text-right text-slate-500">₹{{ number_format($inv->igst, 2) }}</td>
                                <td class="px-3 py-2.5 text-right font-bold text-slate-900">₹{{ number_format($inv->total_amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-3 py-6 text-center text-slate-400 font-medium">No GSTR-1 outward records available for this period.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    @elseif ($gstType === 'gstr2')
        <!-- 5.2 GSTR-2 INWARD SUPPLIES (ITC) VIEW -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Total Inward Purchase Outlay</span>
                <span class="text-xl font-black text-slate-800 block mt-1">₹{{ number_format($purchaseSummary['total_spent'], 2) }}</span>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Total Input Tax Credit (ITC) Available</span>
                <span class="text-xl font-black text-blue-600 block mt-1">₹{{ number_format($purchaseSummary['total_gst'], 2) }}</span>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                <h3 class="text-base font-bold text-slate-800 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-2m-4-1v8m0 0l3-3m-3 3L9 8"></path></svg>
                    GSTR-2 Inward Purchase Input Tax Credit (ITC)
                </h3>
                <div class="flex items-center space-x-2">
                    <a href="{{ route('reports.export.pdf', ['start_date' => $startDate, 'end_date' => $endDate, 'filter_period' => $period, 'filter_month' => $filterMonth ?? '', 'filter_year' => $filterYear ?? '', 'report_type' => 'gst', 'gst_type' => 'gstr2']) }}" 
                       class="p-1 hover:bg-rose-50 rounded-xl transition hover:scale-105 flex items-center justify-center no-ajax"
                       title="Export GSTR-2 PDF">
                        <x-icon-export-pdf class="w-6 h-6 shrink-0" />
                    </a>
                    <a href="{{ route('reports.export', ['start_date' => $startDate, 'end_date' => $endDate, 'filter_period' => $period, 'filter_month' => $filterMonth ?? '', 'filter_year' => $filterYear ?? '', 'report_type' => 'gst', 'gst_type' => 'gstr2']) }}" 
                       class="p-1 hover:bg-emerald-50 rounded-xl transition hover:scale-105 flex items-center justify-center no-ajax"
                       title="Export GSTR-2 CSV">
                        <x-icon-export-csv class="w-6 h-6 shrink-0" />
                    </a>
                </div>
            </div>
            <div class="overflow-x-auto w-full max-w-full border border-slate-200 rounded-xl">
                <table class="erp-datatable min-w-full divide-y divide-slate-200 text-xs">
                    <thead class="bg-[#EDF4FA] text-black">
                        <tr>
                            <th class="px-3 py-2.5 text-left font-bold uppercase">Bill Date</th>
                            <th class="px-3 py-2.5 text-left font-bold uppercase">Bill No.</th>
                            <th class="px-3 py-2.5 text-left font-bold uppercase">Supplier / Vendor</th>
                            <th class="px-3 py-2.5 text-left font-bold uppercase">Item Description</th>
                            <th class="px-3 py-2.5 text-center font-bold uppercase">GST Rate</th>
                            <th class="px-3 py-2.5 text-right font-bold uppercase">ITC GST Paid (₹)</th>
                            <th class="px-3 py-2.5 text-right font-bold uppercase">Total Bill (₹)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($purchases as $pur)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-3 py-2.5 text-slate-700 whitespace-nowrap">{{ \Carbon\Carbon::parse($pur->purchase_date)->format('d M Y') }}</td>
                                <td class="px-3 py-2.5 font-mono text-slate-700 font-bold">{{ $pur->bill_number ?? 'N/A' }}</td>
                                <td class="px-3 py-2.5 font-semibold text-slate-800">{{ $pur->vendor_name }}</td>
                                <td class="px-3 py-2.5 text-slate-600">{{ $pur->item_name }}</td>
                                <td class="px-3 py-2.5 text-center text-slate-500 font-bold">{{ number_format($pur->gst_rate, 0) }}%</td>
                                <td class="px-3 py-2.5 text-right font-bold text-blue-600">₹{{ number_format($pur->gst_amount, 2) }}</td>
                                <td class="px-3 py-2.5 text-right font-bold text-slate-900">₹{{ number_format($pur->total_amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-3 py-6 text-center text-slate-400 font-medium">No GSTR-2 purchase ITC records available for this period.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    @else
        @php
            if ($period === 'month' && !empty($filterMonth)) {
                $taxPeriodLabel = \Carbon\Carbon::parse($filterMonth . '-01')->format('F Y');
            } elseif ($period === 'year' && !empty($filterYear)) {
                $taxPeriodLabel = 'FY ' . $filterYear . '-' . substr($filterYear + 1, 2, 2);
            } elseif ($period === 'custom') {
                $taxPeriodLabel = \Carbon\Carbon::parse($startDate)->format('d M Y') . ' - ' . \Carbon\Carbon::parse($endDate)->format('d M Y');
            } else {
                $taxPeriodLabel = \Carbon\Carbon::parse($startDate)->format('F Y');
            }
        @endphp
        <!-- 5.3 GSTR-3B MONTHLY RETURN SUMMARY VIEW -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
                <div class="flex items-center space-x-2">
                    <span class="w-2.5 h-2.5 bg-emerald-500 rounded-full"></span>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">3.1 Output GST Liability (Sales)</span>
                </div>
                <span class="text-xl font-black text-emerald-600 block mt-2">₹{{ number_format($gstSummary['sales_total_gst'], 2) }}</span>
                <p class="text-[10px] text-slate-400 mt-1">Tax collected from client invoices.</p>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
                <div class="flex items-center space-x-2">
                    <span class="w-2.5 h-2.5 bg-rose-500 rounded-full"></span>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">4. Eligible ITC Credit (Purchases)</span>
                </div>
                <span class="text-xl font-black text-rose-600 block mt-2">₹{{ number_format($gstSummary['purchase_total_gst'], 2) }}</span>
                <p class="text-[10px] text-slate-400 mt-1">Input Tax Credit paid on vendor bills.</p>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <span class="w-2.5 h-2.5 {{ $gstSummary['status'] === 'unpaid' ? 'bg-rose-500' : ($gstSummary['status'] === 'no_due' ? 'bg-blue-500' : 'bg-emerald-500') }} rounded-full"></span>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">6.1 Net Tax Payable ({{ $taxPeriodLabel }})</span>
                        </div>
                        @if($gstSummary['status'] === 'no_due')
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-blue-100/90 text-blue-800 border border-blue-300 whitespace-nowrap" title="Excess Input Tax Credit available - ₹0 Tax Payable to Govt">
                                <span class="w-2.5 h-2.5 rounded-full bg-blue-500 shrink-0"></span>
                                <span>ITC CREDIT (₹0 DUE)</span>
                            </span>
                        @elseif($gstSummary['status'] === 'paid')
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-emerald-100/90 text-emerald-800 border border-emerald-300 whitespace-nowrap">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shrink-0"></span>
                                <span>PAID</span>
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-rose-100/90 text-rose-800 border border-rose-300 whitespace-nowrap">
                                <span class="w-2.5 h-2.5 rounded-full bg-rose-500 shrink-0"></span>
                                <span>UNPAID</span>
                            </span>
                        @endif
                    </div>
                    <span class="text-xl font-black {{ $gstSummary['status'] === 'unpaid' ? 'text-rose-600' : ($gstSummary['status'] === 'no_due' ? 'text-blue-700' : 'text-emerald-700') }} block mt-2">
                        ₹{{ format_indian(abs($gstSummary['net_gst_payable']), 2) }}
                    </span>
                </div>
                <div class="mt-2 pt-2 border-t border-slate-100 text-[10px]">
                    @if($gstSummary['status'] === 'no_due')
                        <span class="text-blue-700 font-bold flex items-center justify-between gap-2">
                            <span class="truncate">✓ Excess Input Tax Credit Available (Carry Forward)</span>
                        </span>
                    @elseif($gstSummary['status'] === 'paid')
                        <span class="text-emerald-700 font-bold flex items-center justify-between gap-2">
                            <span class="truncate">✓ Settled for {{ $taxPeriodLabel }} via Expense Ledger</span>
                            @if(!empty($gstSummary['expense_entry']))
                                <span class="whitespace-nowrap shrink-0">({{ \Carbon\Carbon::parse($gstSummary['expense_entry']->expense_date)->format('d/m/Y') }})</span>
                            @endif
                        </span>
                    @else
                        <div class="text-rose-600 font-bold flex items-center justify-between gap-2">
                            <span class="truncate">Net Tax Liability for <strong>{{ $taxPeriodLabel }}</strong></span>
                            <a href="{{ route('expenses', ['prefill_category' => 'gst_payment', 'prefill_amount' => abs($gstSummary['net_gst_payable']), 'prefill_desc' => 'GSTR-3B Tax Paid for ' . $taxPeriodLabel . ' via Bank Challan']) }}" class="underline hover:text-rose-800 whitespace-nowrap shrink-0">Log GST Expense →</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- GSTR-3B Table Summary -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <h3 class="text-base font-bold text-slate-800 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                    GSTR-3B Monthly Return Filing Computation
                </h3>
                <div class="flex items-center space-x-2">
                    <a href="{{ route('reports.export.pdf', ['start_date' => $startDate, 'end_date' => $endDate, 'filter_period' => $period, 'filter_month' => $filterMonth ?? '', 'filter_year' => $filterYear ?? '', 'report_type' => 'gst', 'gst_type' => 'gstr3b']) }}" 
                       class="p-1 hover:bg-rose-50 rounded-xl transition hover:scale-105 flex items-center justify-center no-ajax"
                       title="Export GSTR-3B PDF">
                        <x-icon-export-pdf class="w-6 h-6 shrink-0" />
                    </a>
                    <a href="{{ route('reports.export', ['start_date' => $startDate, 'end_date' => $endDate, 'filter_period' => $period, 'filter_month' => $filterMonth ?? '', 'filter_year' => $filterYear ?? '', 'report_type' => 'gst', 'gst_type' => 'gstr3b']) }}" 
                       class="p-1 hover:bg-emerald-50 rounded-xl transition hover:scale-105 flex items-center justify-center no-ajax"
                       title="Export GSTR-3B CSV">
                        <x-icon-export-csv class="w-6 h-6 shrink-0" />
                    </a>
                </div>
            </div>

            <!-- 3.1 Table -->
            <div class="space-y-2">
                <h4 class="font-bold text-xs uppercase tracking-wider text-slate-700">3.1 Details of Outward Taxable Supplies (Output Tax Liability)</h4>
                <div class="overflow-x-auto w-full max-w-full border border-slate-200 rounded-xl">
                    <table class="min-w-full divide-y divide-slate-200 text-xs">
                        <thead class="bg-[#EDF4FA] text-black">
                            <tr>
                                <th class="px-4 py-2.5 text-left font-bold uppercase">Nature of Supplies</th>
                                <th class="px-4 py-2.5 text-right font-bold uppercase">Total Taxable Value</th>
                                <th class="px-4 py-2.5 text-right font-bold uppercase">IGST</th>
                                <th class="px-4 py-2.5 text-right font-bold uppercase">CGST + SGST</th>
                                <th class="px-4 py-2.5 text-right font-bold uppercase">Total Output Tax</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white">
                            <tr>
                                <td class="px-4 py-3 font-semibold text-slate-800">(a) Outward Taxable Supplies (Other than zero rated)</td>
                                <td class="px-4 py-3 text-right font-bold text-slate-700">₹{{ number_format($invoiceSummary['total_taxable'], 2) }}</td>
                                <td class="px-4 py-3 text-right text-slate-600">₹{{ number_format($invoiceSummary['total_igst'], 2) }}</td>
                                <td class="px-4 py-3 text-right text-slate-600">₹{{ number_format($invoiceSummary['total_cgst'] + $invoiceSummary['total_sgst'], 2) }}</td>
                                <td class="px-4 py-3 text-right font-bold text-rose-600">₹{{ number_format($invoiceSummary['total_gst'], 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 4. Table -->
            <div class="space-y-2">
                <h4 class="font-bold text-xs uppercase tracking-wider text-slate-700">4. Eligible Input Tax Credit (ITC Available from Purchases)</h4>
                <div class="overflow-x-auto w-full max-w-full border border-slate-200 rounded-xl">
                    <table class="min-w-full divide-y divide-slate-200 text-xs">
                        <thead class="bg-[#EDF4FA] text-black">
                            <tr>
                                <th class="px-4 py-2.5 text-left font-bold uppercase">Details of ITC Available</th>
                                <th class="px-4 py-2.5 text-right font-bold uppercase">Total Input Tax Credit (₹)</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white">
                            <tr>
                                <td class="px-4 py-3 font-semibold text-slate-800">(A) ITC Available (All Inward Goods & Material Purchases)</td>
                                <td class="px-4 py-3 text-right font-bold text-emerald-600">₹{{ number_format($purchaseSummary['total_gst'], 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
