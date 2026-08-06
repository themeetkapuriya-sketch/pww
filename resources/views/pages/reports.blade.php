@extends('layouts.app')

@section('title', 'Reports')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between pb-4 border-b border-slate-200">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">
                @if ($reportType === 'invoice')
                    Sales Report
                @elseif ($reportType === 'purchase')
                    Purchase Report
                @elseif ($reportType === 'financial')
                    Financial Report (P&L)
                @elseif ($reportType === 'expense')
                    Expense Report
                @else
                    Operational & Financial Reports
                @endif
            </h1>
            <p class="text-sm text-slate-500">
                @if ($reportType === 'invoice')
                    Analyze PWW sales revenue, taxable values, and invoice status.
                @elseif ($reportType === 'purchase')
                    Track logged factory vendor bills, raw materials restocking, and capital expenditures.
                @elseif ($reportType === 'financial')
                    Review statement of PWW net corporate earnings, profit margins, and direct overheads.
                @elseif ($reportType === 'expense')
                    Track factory operational overheads, electricity, gas, salary disbursements, transport, and administrative costs.
                @else
                    Analyze PWW profit margins, purchase ledger logs, and expense reports.
                @endif
            </p>
        </div>
    </div>

    <!-- Interactive Navigation Tabs -->
    <div class="flex flex-wrap border-b border-slate-200 bg-white p-2 rounded-2xl shadow-xs gap-1">
        <a href="{{ route('reports') }}?report_type=invoice" 
           class="flex-1 min-w-[120px] text-center py-2.5 px-3 rounded-xl text-xs font-bold transition {{ $reportType === 'invoice' ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-50' }}">
            🧾 Sales Report
        </a>
        <a href="{{ route('reports') }}?report_type=purchase" 
           class="flex-1 min-w-[120px] text-center py-2.5 px-3 rounded-xl text-xs font-bold transition {{ $reportType === 'purchase' ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-50' }}">
            📦 Purchase Report
        </a>
        <a href="{{ route('reports') }}?report_type=financial" 
           class="flex-1 min-w-[120px] text-center py-2.5 px-3 rounded-xl text-xs font-bold transition {{ $reportType === 'financial' ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-50' }}">
            📊 Financial Report (P&L)
        </a>
        <a href="{{ route('reports') }}?report_type=expense" 
           class="flex-1 min-w-[120px] text-center py-2.5 px-3 rounded-xl text-xs font-bold transition {{ $reportType === 'expense' ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-50' }}">
            💸 Expense Report
        </a>
        <a href="{{ route('reports') }}?report_type=gst" 
           class="flex-1 min-w-[140px] text-center py-2.5 px-3 rounded-xl text-xs font-bold transition {{ $reportType === 'gst' ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-50' }}">
            ⚖️ GST Tax Reports
        </a>
    </div>

    <!-- Filter Form (Unified Capsule Filters & Dates) -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
        <form method="GET" action="{{ route('reports') }}" class="space-y-4" id="reportFilterForm">
            <input type="hidden" name="report_type" value="{{ $reportType }}">
            <input type="hidden" name="filter_period" id="filterPeriodInput" value="{{ $period }}">

            <!-- Capsule Period Bar (Matches User's UI style but with theme-blue) -->
            <div class="flex flex-wrap items-center gap-2" id="capsuleBar">
                <span class="text-xs font-black uppercase text-slate-400 tracking-wider flex items-center mr-2">
                    <svg class="w-4 h-4 mr-1 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                    Filter Period:
                </span>
                
                <button type="button" onclick="setPeriod('all')" 
                        class="px-4 py-1.5 rounded-full text-xs font-bold border transition duration-150 {{ $period === 'all' ? 'bg-blue-600 border-blue-600 text-white shadow-sm' : 'border-blue-600/30 text-blue-700 hover:bg-blue-50 bg-white' }}">
                    All Records
                </button>
                <button type="button" onclick="setPeriod('month')" 
                        class="px-4 py-1.5 rounded-full text-xs font-bold border transition duration-150 {{ $period === 'month' ? 'bg-blue-600 border-blue-600 text-white shadow-sm' : 'border-blue-600/30 text-blue-700 hover:bg-blue-50 bg-white' }}">
                    Month
                </button>
                <button type="button" onclick="setPeriod('year')" 
                        class="px-4 py-1.5 rounded-full text-xs font-bold border transition duration-150 {{ $period === 'year' ? 'bg-blue-600 border-blue-600 text-white shadow-sm' : 'border-blue-600/30 text-blue-700 hover:bg-blue-50 bg-white' }}">
                    Year
                </button>
                <button type="button" onclick="setPeriod('custom')" 
                        class="px-4 py-1.5 rounded-full text-xs font-bold border transition duration-150 {{ $period === 'custom' ? 'bg-blue-600 border-blue-600 text-white shadow-sm' : 'border-blue-600/30 text-blue-700 hover:bg-blue-50 bg-white' }}">
                    Custom Range
                </button>
            </div>

            <!-- Dynamic Input Fields (Revealed conditionally) -->
            <div id="dynamicFilterFields" class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-3 border-t border-slate-100 {{ $period === 'all' ? 'hidden' : '' }}">
                <!-- Month Selection Container -->
                <div id="monthFilterContainer" class="{{ $period === 'month' ? '' : 'hidden' }}">
                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Select Month</label>
                    <input type="month" name="filter_month" id="filterMonthInput" value="{{ $filterMonth }}"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-medium">
                </div>

                <!-- Financial Year Selection Container -->
                <div id="yearFilterContainer" class="{{ $period === 'year' ? '' : 'hidden' }}">
                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Select Financial Year</label>
                    <select name="filter_year" id="filterYearSelect"
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-medium font-bold">
                        @for ($y = date('Y') + 1; $y >= 2020; $y--)
                            @php
                                $nextYearShort = substr($y + 1, 2, 2);
                            @endphp
                            <option value="{{ $y }}" {{ $filterYear == $y ? 'selected' : '' }}>FY {{ $y }}-{{ $nextYearShort }}</option>
                        @endfor
                    </select>
                </div>

                <!-- Custom Date Range Containers -->
                <div id="customRangeContainer" class="col-span-1 md:col-span-2 grid grid-cols-2 gap-4 {{ $period === 'custom' ? '' : 'hidden' }}">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Start Date</label>
                        <input type="date" name="start_date" value="{{ $startDate }}"
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-medium">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">End Date</label>
                        <input type="date" name="end_date" value="{{ $endDate }}"
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-medium">
                    </div>
                </div>

                <!-- Submit Action Button -->
                <div class="flex items-end">
                    <button type="submit" class="btn-primary w-full py-2.5 px-4 text-xs font-bold shadow-xs">
                        Apply Filter Range
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Active Report Rendering -->
    @if ($reportType === 'invoice')
        <!-- 1. INVOICE REPORT VIEW -->
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
                                    @if($isRawMaterial)
                                        <span class="text-amber-800 font-bold uppercase tracking-wider bg-amber-50 px-2 py-0.5 rounded border border-amber-200">NILL</span>
                                    @else
                                        <a href="{{ route('invoice.preview', $inv->id) }}" class="text-blue-600 hover:underline">{{ $inv->invoice_number }}</a>
                                    @endif
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
                                <td class="px-4 py-3 text-center">
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

    @elseif ($reportType === 'purchase')
        <!-- 2. PURCHASE REPORT VIEW -->
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

    @elseif ($reportType === 'financial')
        <!-- 3. FINANCIAL REPORT VIEW -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Accounts Receivable (Client Dues)</span>
                <span class="text-xl font-black text-amber-600 block mt-1">₹{{ number_format($financials['outstanding_receivables'] ?? 0, 2) }}</span>
                <span class="text-[10px] text-slate-400">Total unpaid & partial invoices</span>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Accounts Payable (Vendor Dues)</span>
                <span class="text-xl font-black text-rose-600 block mt-1">₹{{ number_format($financials['outstanding_payables'] ?? 0, 2) }}</span>
                <span class="text-[10px] text-slate-400">Total unpaid supplier bills</span>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Bank Account Collections</span>
                <span class="text-xl font-black text-blue-600 block mt-1">₹{{ number_format($financials['bank_collections'] ?? 0, 2) }}</span>
                <span class="text-[10px] text-slate-400">Received via NEFT / UPI / Cheque</span>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Cash In Hand Collections</span>
                <span class="text-xl font-black text-emerald-600 block mt-1">₹{{ number_format($financials['cash_collections'] ?? 0, 2) }}</span>
                <span class="text-[10px] text-slate-400">Received via liquid cash</span>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                <h3 class="text-base font-bold text-slate-800 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 01-2-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Statement of Net Profit / Loss
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
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-[#EDF4FA] text-black divide-x divide-slate-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase">Accounting Item</th>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase">Description</th>
                            <th class="px-6 py-3 text-right text-xs font-bold uppercase">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-x divide-slate-100 bg-white">
                        <tr>
                            <td class="px-6 py-4 font-semibold text-slate-800">Total Billed Sales (A)</td>
                            <td class="px-6 py-4 text-slate-500 text-xs">Sum of total invoiced amounts of all generated compliance invoices. (Includes GST tax)</td>
                            <td class="px-6 py-4 text-right font-bold text-emerald-600">₹{{ format_indian($financials['revenue'], 2) }}</td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 font-semibold text-slate-800">Total Purchases (B)</td>
                            <td class="px-6 py-4 text-slate-500 text-xs">Total outlay for raw materials, machinery, tools, and vendor inventory purchases.</td>
                            <td class="px-6 py-4 text-right font-bold text-rose-600">- ₹{{ format_indian($financials['purchases'], 2) }}</td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 font-semibold text-slate-800">Total Expenses (C)</td>
                            <td class="px-6 py-4 text-slate-500 text-xs">Total operational overheads (salaries, electricity, transport, administration, etc.).</td>
                            <td class="px-6 py-4 text-right font-bold text-rose-600">- ₹{{ format_indian($financials['expenses'], 2) }}</td>
                        </tr>
                        <tr class="bg-slate-50 font-bold border-t-2 border-slate-300">
                            <td class="px-6 py-4 text-slate-800 text-base">Net Profit / Revenue (A - B - C)</td>
                            <td class="px-6 py-4 text-slate-500 text-xs">PWW net earnings (Total Sales − Purchases − Expenses) for this audit period.</td>
                            <td class="px-6 py-4 text-right text-base {{ $financials['net_profit'] >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                                ₹{{ format_indian($financials['net_profit'], 2) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    @elseif ($reportType === 'expense')
        <!-- 4. EXPENSE REPORT VIEW -->
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
    @elseif ($reportType === 'gst')
        @php
            $gstType = request('gst_type', 'gstr3b');
        @endphp

        <!-- GST Return Type Capsule Sub-Bar -->
        <div class="flex flex-wrap border-b border-slate-200 bg-white p-1.5 rounded-2xl shadow-xs gap-1.5 mb-5">
            <a href="{{ route('reports', ['report_type' => 'gst', 'gst_type' => 'gstr3b', 'filter_period' => $period, 'filter_month' => $filterMonth ?? '', 'filter_year' => $filterYear ?? '', 'start_date' => $startDate, 'end_date' => $endDate]) }}" 
               class="flex-1 min-w-[150px] text-center py-2 px-3 rounded-xl text-xs font-bold transition {{ $gstType === 'gstr3b' ? 'bg-[#4371D7] text-white shadow-xs' : 'text-slate-600 hover:bg-slate-50' }}">
                ⚖️ GSTR-3B (Monthly Summary)
            </a>
            <a href="{{ route('reports', ['report_type' => 'gst', 'gst_type' => 'gstr1', 'filter_period' => $period, 'filter_month' => $filterMonth ?? '', 'filter_year' => $filterYear ?? '', 'start_date' => $startDate, 'end_date' => $endDate]) }}" 
               class="flex-1 min-w-[150px] text-center py-2 px-3 rounded-xl text-xs font-bold transition {{ $gstType === 'gstr1' ? 'bg-[#4371D7] text-white shadow-xs' : 'text-slate-600 hover:bg-slate-50' }}">
                📄 GSTR-1 (Sales Return)
            </a>
            <a href="{{ route('reports', ['report_type' => 'gst', 'gst_type' => 'gstr2', 'filter_period' => $period, 'filter_month' => $filterMonth ?? '', 'filter_year' => $filterYear ?? '', 'start_date' => $startDate, 'end_date' => $endDate]) }}" 
               class="flex-1 min-w-[150px] text-center py-2 px-3 rounded-xl text-xs font-bold transition {{ $gstType === 'gstr2' ? 'bg-[#4371D7] text-white shadow-xs' : 'text-slate-600 hover:bg-slate-50' }}">
                🛒 GSTR-2 (Purchase ITC)
            </a>
            <a href="{{ route('reports', ['report_type' => 'gst', 'gst_type' => 'combined', 'filter_period' => $period, 'filter_month' => $filterMonth ?? '', 'filter_year' => $filterYear ?? '', 'start_date' => $startDate, 'end_date' => $endDate]) }}" 
               class="flex-1 min-w-[180px] text-center py-2 px-3 rounded-xl text-xs font-bold transition {{ $gstType === 'combined' ? 'bg-purple-600 text-white shadow-xs' : 'text-purple-700 bg-purple-50 hover:bg-purple-100 border border-purple-200' }}">
                📦 Combined Master Package (GSTR 1+2+3B)
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

        @elseif ($gstType === 'combined')
            <!-- 5.4 COMBINED MASTER GST AUDIT PACKAGE VIEW -->
            <div class="bg-gradient-to-r from-purple-900 to-indigo-900 rounded-2xl p-6 text-white shadow-md mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <span class="px-3 py-1 bg-purple-500/30 text-purple-200 border border-purple-400/40 rounded-full text-xs font-black uppercase tracking-wider">Master Audit Package</span>
                    <h3 class="text-xl font-black mt-2">Combined GST Returns (GSTR-1 + GSTR-2 + GSTR-3B)</h3>
                    <p class="text-xs text-purple-200 mt-1">Single master file export containing all Outward Sales, Inward Purchase ITC, and Net Settlement Returns.</p>
                </div>
                <div class="flex items-center space-x-3 shrink-0">
                    <a href="{{ route('reports.export.pdf', ['start_date' => $startDate, 'end_date' => $endDate, 'filter_period' => $period, 'filter_month' => $filterMonth ?? '', 'filter_year' => $filterYear ?? '', 'report_type' => 'gst', 'gst_type' => 'combined']) }}" 
                       class="px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold shadow-sm transition flex items-center gap-2 no-ajax">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"/></svg>
                        Download Combined PDF
                    </a>
                    <a href="{{ route('reports.export', ['start_date' => $startDate, 'end_date' => $endDate, 'filter_period' => $period, 'filter_month' => $filterMonth ?? '', 'filter_year' => $filterYear ?? '', 'report_type' => 'gst', 'gst_type' => 'combined']) }}" 
                       class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-sm transition flex items-center gap-2 no-ajax">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M3 3a1 1 0 011-1h12a1 1 0 011 1v14a1 1 0 01-1 1H4a1 1 0 01-1-1V3z"/></svg>
                        Export Combined CSV
                    </a>
                </div>
            </div>

            <!-- GSTR-3B Summary Card -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 mb-6">
                <h4 class="text-sm font-black text-slate-800 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <span>⚖️</span> Section 1: GSTR-3B Tax Settlement Summary
                </h4>
                @php $netGst = $invoiceSummary['total_gst'] - $purchaseSummary['total_gst']; @endphp
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="p-4 bg-slate-50 rounded-xl border border-slate-100">
                        <span class="text-[10px] font-bold text-slate-400 uppercase">Sales GST Output Liability</span>
                        <span class="text-lg font-black text-rose-600 block mt-1">₹{{ format_indian($invoiceSummary['total_gst'], 2) }}</span>
                    </div>
                    <div class="p-4 bg-slate-50 rounded-xl border border-slate-100">
                        <span class="text-[10px] font-bold text-slate-400 uppercase">Purchase Input Tax Credit (ITC)</span>
                        <span class="text-lg font-black text-emerald-600 block mt-1">₹{{ format_indian($purchaseSummary['total_gst'], 2) }}</span>
                    </div>
                    <div class="p-4 bg-slate-50 rounded-xl border border-slate-100">
                        <span class="text-[10px] font-bold text-slate-400 uppercase">Net Tax Payable / (Credit)</span>
                        <span class="text-lg font-black {{ $netGst > 0 ? 'text-rose-600' : 'text-emerald-600' }} block mt-1">
                            ₹{{ format_indian(abs($netGst), 2) }} {{ $netGst > 0 ? 'DUE' : 'ITC CREDIT' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- GSTR-1 Outward Sales Ledger -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 mb-6 space-y-4">
                <h4 class="text-sm font-black text-slate-800 uppercase tracking-wider flex items-center gap-2">
                    <span>📄</span> Section 2: GSTR-1 Outward B2B Sales Ledger
                </h4>
                <div class="overflow-x-auto w-full max-w-full border border-slate-200 rounded-xl">
                    <table class="erp-datatable min-w-full divide-y divide-slate-200 text-xs">
                        <thead class="bg-slate-50 text-slate-600">
                            <tr>
                                <th class="px-4 py-2.5 text-left font-bold uppercase">Invoice No.</th>
                                <th class="px-4 py-2.5 text-left font-bold uppercase">Client Company</th>
                                <th class="px-4 py-2.5 text-right font-bold uppercase">Taxable Value</th>
                                <th class="px-4 py-2.5 text-right font-bold uppercase">CGST (9%)</th>
                                <th class="px-4 py-2.5 text-right font-bold uppercase">SGST (9%)</th>
                                <th class="px-4 py-2.5 text-right font-bold uppercase">IGST (18%)</th>
                                <th class="px-4 py-2.5 text-right font-bold uppercase">Total Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse($invoices as $inv)
                                @php $isRm = ($inv->invoice_mode === 'raw_material' || str_starts_with($inv->invoice_number, 'RMS-')); @endphp
                                <tr class="hover:bg-slate-50/50 transition">
                                    <td class="px-4 py-2.5 font-bold font-mono text-blue-600">{{ $isRm ? 'NILL' : $inv->invoice_number }}</td>
                                    <td class="px-4 py-2.5 font-bold text-slate-800">{{ $isRm ? ($inv->custom_client_name ?? 'Direct Buyer') : ($inv->plant->client->company_name ?? 'N/A') }}</td>
                                    <td class="px-4 py-2.5 text-right font-semibold text-slate-700">₹{{ number_format($inv->total_taxable_value, 2) }}</td>
                                    <td class="px-4 py-2.5 text-right text-slate-500">₹{{ number_format($inv->cgst, 2) }}</td>
                                    <td class="px-4 py-2.5 text-right text-slate-500">₹{{ number_format($inv->sgst, 2) }}</td>
                                    <td class="px-4 py-2.5 text-right text-slate-500">₹{{ number_format($inv->igst, 2) }}</td>
                                    <td class="px-4 py-2.5 text-right font-bold text-slate-900">₹{{ number_format($inv->total_amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-6 text-center text-slate-400">No GSTR-1 sales records found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- GSTR-2 Inward Purchases Ledger -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-4">
                <h4 class="text-sm font-black text-slate-800 uppercase tracking-wider flex items-center gap-2">
                    <span>🛒</span> Section 3: GSTR-2 Inward Purchases & ITC Ledger
                </h4>
                <div class="overflow-x-auto w-full max-w-full border border-slate-200 rounded-xl">
                    <table class="erp-datatable min-w-full divide-y divide-slate-200 text-xs">
                        <thead class="bg-slate-50 text-slate-600">
                            <tr>
                                <th class="px-4 py-2.5 text-left font-bold uppercase">Bill No.</th>
                                <th class="px-4 py-2.5 text-left font-bold uppercase">Vendor Name</th>
                                <th class="px-4 py-2.5 text-left font-bold uppercase">Item Description</th>
                                <th class="px-4 py-2.5 text-center font-bold uppercase">GST Rate</th>
                                <th class="px-4 py-2.5 text-right font-bold uppercase">ITC GST Paid</th>
                                <th class="px-4 py-2.5 text-right font-bold uppercase">Total Bill</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse($purchases as $pur)
                                <tr class="hover:bg-slate-50/50 transition">
                                    <td class="px-4 py-2.5 font-bold font-mono text-slate-700">{{ $pur->bill_number ?? 'N/A' }}</td>
                                    <td class="px-4 py-2.5 font-bold text-slate-800">{{ $pur->vendor_name }}</td>
                                    <td class="px-4 py-2.5 text-slate-600">{{ $pur->item_name }}</td>
                                    <td class="px-4 py-2.5 text-center font-bold text-slate-500">{{ number_format($pur->gst_rate, 0) }}%</td>
                                    <td class="px-4 py-2.5 text-right font-bold text-rose-600">₹{{ number_format($pur->gst_amount, 2) }}</td>
                                    <td class="px-4 py-2.5 text-right font-bold text-slate-900">₹{{ number_format($pur->total_amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-6 text-center text-slate-400">No GSTR-2 purchase records found.</td>
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
    @endif
</div>

<script>
    function setPeriod(period) {
        document.getElementById('filterPeriodInput').value = period;
        
        const dynamicFields = document.getElementById('dynamicFilterFields');
        const monthContainer = document.getElementById('monthFilterContainer');
        const yearContainer = document.getElementById('yearFilterContainer');
        const customContainer = document.getElementById('customRangeContainer');

        if (period === 'all') {
            if (dynamicFields) dynamicFields.classList.add('hidden');
            // Auto submit immediately for All Records (triggers jQuery SPA submit interceptor)
            $('#reportFilterForm').submit();
            return;
        }

        if (dynamicFields) dynamicFields.classList.remove('hidden');
        
        if (monthContainer) {
            if (period === 'month') monthContainer.classList.remove('hidden');
            else monthContainer.classList.add('hidden');
        }
        
        if (yearContainer) {
            if (period === 'year') yearContainer.classList.remove('hidden');
            else yearContainer.classList.add('hidden');
        }
        
        if (customContainer) {
            if (period === 'custom') customContainer.classList.remove('hidden');
            else customContainer.classList.add('hidden');
        }

        // Dynamically update active/inactive capsule classes in real-time
        const buttons = document.querySelectorAll('#capsuleBar button');
        buttons.forEach(btn => {
            const isTarget = btn.getAttribute('onclick').includes(period);
            if (isTarget) {
                btn.className = "px-4 py-1.5 rounded-full text-xs font-bold border transition duration-150 bg-blue-600 border-blue-600 text-white shadow-sm";
            } else {
                btn.className = "px-4 py-1.5 rounded-full text-xs font-bold border transition duration-150 border-blue-600/30 text-blue-700 hover:bg-blue-50 bg-white";
            }
        });
    }

    // Intercept form submission to disable inactive parameters (keeps URL clean and secure)
    $(document).off('submit', '#reportFilterForm').on('submit', '#reportFilterForm', function() {
        const period = document.getElementById('filterPeriodInput').value;
        const monthInput = document.getElementById('filterMonthInput');
        const yearSelect = document.getElementById('filterYearSelect');
        const startDateInput = document.querySelector('input[name="start_date"]');
        const endDateInput = document.querySelector('input[name="end_date"]');

        // Reset all to enabled first
        if (monthInput) monthInput.disabled = false;
        if (yearSelect) yearSelect.disabled = false;
        if (startDateInput) startDateInput.disabled = false;
        if (endDateInput) endDateInput.disabled = false;

        // Disable inactive inputs so they are excluded from FormData serialization
        if (period === 'all') {
            if (monthInput) monthInput.disabled = true;
            if (yearSelect) yearSelect.disabled = true;
            if (startDateInput) startDateInput.disabled = true;
            if (endDateInput) endDateInput.disabled = true;
        } else if (period === 'month') {
            if (yearSelect) yearSelect.disabled = true;
            if (startDateInput) startDateInput.disabled = true;
            if (endDateInput) endDateInput.disabled = true;
        } else if (period === 'year') {
            if (monthInput) monthInput.disabled = true;
            if (startDateInput) startDateInput.disabled = true;
            if (endDateInput) endDateInput.disabled = true;
        } else if (period === 'custom') {
            if (monthInput) monthInput.disabled = true;
            if (yearSelect) yearSelect.disabled = true;
        }
    });
</script>
@endsection
