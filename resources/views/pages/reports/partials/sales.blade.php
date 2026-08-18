<!-- 1. SALES REPORT VIEW PARTIAL -->
<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Total Taxable Value</span>
            <span class="text-xl font-black text-slate-800 block mt-1">₹{{ number_format($invoiceSummary['total_taxable'], 2) }}</span>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Total GST Collected</span>
            <span class="text-xl font-black text-blue-600 block mt-1">₹{{ number_format($invoiceSummary['total_gst'], 2) }}</span>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Total Sales Amount</span>
            <span class="text-xl font-black text-emerald-600 block mt-1">₹{{ number_format($invoiceSummary['total_amount'], 2) }}</span>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Total Outstanding Due</span>
            <span class="text-xl font-black text-rose-600 block mt-1">₹{{ number_format($invoiceSummary['total_due'] ?? 0, 2) }}</span>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 p-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
            <h3 class="text-base font-bold text-slate-800 flex items-center">
                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                Logged Sales Invoices Audit
            </h3>
            <div class="flex items-center space-x-2">
                <a href="{{ route('reports.export.pdf', ['start_date' => $startDate, 'end_date' => $endDate, 'filter_period' => $period, 'filter_month' => $filterMonth ?? '', 'filter_year' => $filterYear ?? '', 'report_type' => $reportType]) }}" 
                   class="p-1 hover:bg-rose-50 rounded-xl transition hover:scale-105 flex items-center justify-center no-ajax"
                   title="Export PDF Document">
                    <x-icon-export-pdf class="w-6 h-6 shrink-0" />
                </a>
                <a href="{{ route('reports.export', ['start_date' => $startDate, 'end_date' => $endDate, 'filter_period' => $period, 'filter_month' => $filterMonth ?? '', 'filter_year' => $filterYear ?? '', 'report_type' => $reportType]) }}" 
                   class="p-1 hover:bg-emerald-50 rounded-xl transition hover:scale-105 flex items-center justify-center no-ajax"
                   title="Export CSV File">
                    <x-icon-export-csv class="w-6 h-6 shrink-0" />
                </a>
            </div>
        </div>
        <div class="overflow-x-auto w-full max-w-full border border-slate-200 rounded-xl">
            <table class="erp-datatable min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-4 py-3 text-left font-bold text-xs">Invoice No.</th>
                        <th class="px-4 py-3 text-left font-bold text-xs">Client & Plant</th>
                        <th class="px-4 py-3 text-left font-bold text-xs">Invoice Date</th>
                        <th class="px-4 py-3 text-right font-bold text-xs">Taxable Value</th>
                        <th class="px-4 py-3 text-right font-bold text-xs">CGST (9%)</th>
                        <th class="px-4 py-3 text-right font-bold text-xs">SGST (9%)</th>
                        <th class="px-4 py-3 text-right font-bold text-xs">IGST (18%)</th>
                        <th class="px-4 py-3 text-right font-bold text-xs">Total Bill</th>
                        <th class="px-4 py-3 text-right font-bold text-xs">Due Amount</th>
                        <th class="px-4 py-3 text-center font-bold text-xs">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($invoices as $inv)
                        @php
                            $isRawMaterial = ($inv->invoice_mode === 'raw_material' || str_starts_with($inv->invoice_number, 'RMS-'));
                            $clientName = $isRawMaterial ? ($inv->custom_client_name ?? 'Direct Buyer') : ($inv->client ? $inv->client->company_name : 'N/A');
                            $plantName = $isRawMaterial ? 'Raw Material Sale' : ($inv->plant ? $inv->plant->plant_name : 'HQ');
                        @endphp
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="px-4 py-3 font-bold font-mono text-xs">
                                <a href="{{ route('invoice.preview', $inv->id) }}" class="text-blue-600 hover:underline">{{ $inv->invoice_number }}</a>
                            </td>
                            <td class="px-4 py-3 text-xs">
                                <div class="font-bold text-slate-800">{{ $clientName }}</div>
                                <div class="text-slate-400">{{ $plantName }}</div>
                            </td>
                            <td class="px-4 py-3 text-xs text-slate-500 font-medium">
                                {{ \Carbon\Carbon::parse($inv->invoice_date ?? $inv->created_at)->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-slate-700">₹{{ number_format($inv->total_taxable_value, 2) }}</td>
                            <td class="px-4 py-3 text-right text-slate-500">₹{{ number_format($inv->cgst, 2) }}</td>
                            <td class="px-4 py-3 text-right text-slate-500">₹{{ number_format($inv->sgst, 2) }}</td>
                            <td class="px-4 py-3 text-right text-slate-500">₹{{ number_format($inv->igst, 2) }}</td>
                            <td class="px-4 py-3 text-right font-bold text-slate-900">₹{{ number_format($inv->total_amount, 2) }}</td>
                            <td class="px-4 py-3 text-right font-bold {{ $inv->remaining_balance > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                                ₹{{ number_format($inv->remaining_balance, 2) }}
                            </td>
                            <td class="px-4 py-3 text-center inv-status-cell">
                                @if(($inv->payment_status ?? 'unpaid') === 'paid')
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-300">
                                        PAID
                                    </span>
                                @elseif(($inv->payment_status ?? 'unpaid') === 'partially_paid')
                                    <button type="button" 
                                            onclick="payInvoiceRecord({{ $inv->id }}, '{{ $inv->invoice_number }}', {{ $inv->remaining_balance }})"
                                            title="Click to record payment"
                                            class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-amber-100 text-amber-800 border border-amber-300 hover:bg-amber-200 transition cursor-pointer">
                                        PARTIAL
                                    </button>
                                @else
                                    <button type="button" 
                                            onclick="payInvoiceRecord({{ $inv->id }}, '{{ $inv->invoice_number }}', {{ $inv->remaining_balance }})"
                                            title="Click to record payment"
                                            class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-rose-100 text-rose-800 border border-rose-300 hover:bg-rose-200 transition cursor-pointer">
                                        UNPAID
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr class="empty-row">
                            <td colspan="10" class="px-6 py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center space-y-2">
                                    <svg class="w-10 h-10 mx-auto text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                    </svg>
                                    <p class="text-sm font-bold text-slate-600">No Records Found</p>
                                    <p class="text-xs text-slate-400">There are no sales records matching your selected report filter criteria.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
