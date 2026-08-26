@extends('layouts.app')

@section('title', 'Overview Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Top Header -->
    <div class="pb-4 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-2xl font-black text-slate-800 tracking-tight">Overview Dashboard</h1>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    Live System Status
                </span>
            </div>
            <p class="text-xs font-medium text-slate-500 mt-1">
                Real-time operational summary & financial performance for {{ \App\Models\Setting::get('business_name', 'Praful Welding Works') }}.
            </p>
        </div>

        @if(\App\Models\Setting::get('module_payroll', 'true') === 'true' && \App\Models\Setting::get('simplified_billing_mode', 'false') !== 'true')
        <div class="flex items-center gap-2">
            <button type="button" onclick="openQuickAttendanceModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-4 rounded-xl text-xs transition shadow-xs flex items-center gap-1.5 cursor-pointer transform hover:scale-[1.02]">
                <span>📅</span> Today's Attendance Sheet
            </button>
        </div>
        @endif
    </div>

    <!-- 3 Net Revenue Cards (Lifetime Revenue, Annual Revenue, Monthly Revenue) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- 1. Lifetime Revenue -->
        <div class="text-white rounded-2xl p-5 shadow-sm relative overflow-hidden group hover:shadow-md transition border border-emerald-500/50" style="background: linear-gradient(135deg, #059669 0%, #0d9488 100%);">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold tracking-wider text-emerald-100 uppercase">1. Lifetime Revenue</span>
                <span class="w-9 h-9 bg-white/10 text-white rounded-xl backdrop-blur-xs border border-white/10 text-xl flex items-center justify-center shrink-0">💵</span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black tracking-tight text-white">₹{{ format_indian($lifetimeRevenue, 2) }}</div>
                <div class="text-[11px] font-semibold text-emerald-100/90 mt-1">
                    Sales (₹{{ format_indian($lifetimeSales, 0) }}) − Purchases (₹{{ format_indian($lifetimePurchases, 0) }}) − All Expenses (₹{{ format_indian($lifetimeExpenses, 0) }})
                </div>
            </div>
        </div>

        <!-- 2. Annual Revenue -->
        <div class="text-white rounded-2xl p-5 shadow-sm relative overflow-hidden group hover:shadow-md transition border border-indigo-600/50" style="background: linear-gradient(135deg, #3730a3 0%, #312e81 100%);">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold tracking-wider text-indigo-200 uppercase">2. Annual Revenue <span class="normal-case opacity-80">(FY {{ $fyStartYear }}-{{ ($fyStartYear + 1) % 100 }})</span></span>
                <span class="w-9 h-9 bg-white/10 text-white rounded-xl backdrop-blur-xs border border-white/10 text-xl flex items-center justify-center shrink-0">📅</span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black tracking-tight text-white">₹{{ format_indian($annualRevenue, 2) }}</div>
                <div class="text-[11px] font-semibold text-indigo-200/90 mt-1">
                    Sales (₹{{ format_indian($yearlyRevenue, 0) }}) − Purchases (₹{{ format_indian($fyPurchasesTotal, 0) }}) − FY Expenses (₹{{ format_indian($fyExpensesTotal, 0) }})
                </div>
            </div>
        </div>

        <!-- 3. Monthly Revenue -->
        <div class="text-white rounded-2xl p-5 shadow-sm relative overflow-hidden group hover:shadow-md transition border border-blue-500/50" style="background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold tracking-wider text-blue-100 uppercase">3. Monthly Revenue ({{ date('M Y') }})</span>
                <span class="w-9 h-9 bg-white/10 text-white rounded-xl backdrop-blur-xs border border-white/10 text-xl flex items-center justify-center shrink-0">📆</span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black tracking-tight text-white">₹{{ format_indian($monthlyNetRevenue, 2) }}</div>
                <div class="text-[11px] font-semibold text-blue-100/90 mt-1">
                    Sales (₹{{ format_indian($monthlyRevenue, 0) }}) − Purchases (₹{{ format_indian($monthlyPurchasesTotalOnly, 0) }}) − Month Expenses (₹{{ format_indian($monthlyExpensesTotalOnly, 0) }})
                </div>
            </div>
        </div>
    </div>

    <!-- Executive KPI Cards - Primary Row (Specified Exact Order) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- 1. Annual Billed Sales -->
        @if(\App\Models\Setting::get('module_invoices', 'true') === 'true')
        <div class="text-white rounded-2xl p-5 shadow-sm relative overflow-hidden group hover:shadow-md transition border border-indigo-600/50" style="background: linear-gradient(135deg, #3730a3 0%, #312e81 100%);">
            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-indigo-400/10 rounded-full blur-xl group-hover:scale-125 transition"></div>
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold tracking-wider text-indigo-200 uppercase">1. {{ $fyStartYear }}-{{ $fyStartYear+1 }} Annual Sales</span>
                <span class="p-2 bg-white/10 text-white rounded-xl backdrop-blur-xs border border-white/10">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black tracking-tight text-white">₹{{ format_indian($yearlyRevenue, 2) }}</div>
                <div class="text-[11px] font-semibold text-indigo-200/90 mt-1 flex items-center gap-1">
                    <span>Taxable Base:</span>
                    <span class="font-bold text-white">₹{{ format_indian($yearlyTaxable, 2) }}</span>
                </div>
            </div>
            <div class="mt-3 pt-2 border-t border-indigo-800/40 text-[10px] text-indigo-200/70 font-medium">
                Annual Billed Sales Total (Incl. GST)
            </div>
        </div>
        @endif

        <!-- 2. Monthly Revenue -->
        @if(\App\Models\Setting::get('module_invoices', 'true') === 'true')
        <div class="text-white rounded-2xl p-5 shadow-sm relative overflow-hidden group hover:shadow-md transition border border-blue-500/50" style="background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);">
            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-blue-400/10 rounded-full blur-xl group-hover:scale-125 transition pointer-events-none"></div>
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold tracking-wider text-blue-100 uppercase">2. {{ date('M Y') }} Monthly Sales</span>
                <span class="p-2 bg-white/10 text-white rounded-xl backdrop-blur-xs border border-white/10">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black tracking-tight text-white">₹{{ format_indian($monthlyRevenue, 2) }}</div>
                <div class="text-[11px] font-semibold text-blue-100/90 mt-1 flex items-center gap-1">
                    <span>Taxable Base:</span>
                    <span class="font-bold text-white">₹{{ format_indian($monthlyTaxable, 2) }}</span>
                    <span class="text-blue-200 ml-1">({{ $monthlyInvoiceCount }} Inv)</span>
                </div>
            </div>
            <div class="mt-3 pt-2 border-t border-blue-500/40 text-[10px] text-blue-100/70 font-medium">
                Current Month Total Billed (Incl. GST)
            </div>
        </div>
        @endif

        <!-- 3. Outstanding Receivables -->
        @if(\App\Models\Setting::get('module_invoices', 'true') === 'true' && \App\Models\Setting::get('track_payments', 'true') === 'true')
        <div class="text-white rounded-2xl p-5 shadow-sm relative overflow-hidden group hover:shadow-md transition border border-amber-500/50" style="background: linear-gradient(135deg, #d97706 0%, #b45309 100%);">
            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-amber-400/10 rounded-full blur-xl group-hover:scale-125 transition pointer-events-none"></div>
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold tracking-wider text-amber-100 uppercase">3. Outstanding Receivables</span>
                <span class="p-2 bg-white/10 text-white rounded-xl backdrop-blur-xs border border-white/10">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black tracking-tight text-white">₹{{ format_indian($totalReceivables, 2) }}</div>
                <div class="text-[11px] font-bold text-amber-100/90 mt-1 flex items-center gap-1">
                    <span class="inline-block w-1.5 h-1.5 rounded-full bg-amber-200 animate-ping"></span>
                    <span>Pending Client Collections</span>
                </div>
            </div>
            <div class="mt-3 pt-2 border-t border-amber-500/40 text-[10px] text-amber-100/70 font-medium">
                Unpaid Client Invoice Balance Total
            </div>
        </div>
        @endif

        <!-- 4. Net GST Liability -->
        @if(\App\Models\Setting::get('module_reports', 'true') === 'true' || \App\Models\Setting::get('module_invoices', 'true') === 'true')
        @php
            $gstStatus = $currentMonthGstStatus ?? ($currentMonthGstPaid ? 'paid' : 'unpaid');
        @endphp
        <div class="text-white rounded-2xl p-5 shadow-sm relative overflow-hidden group hover:shadow-md transition border {{ $gstStatus === 'unpaid' ? 'border-rose-500/50' : 'border-emerald-500/50' }}" style="background: {{ $gstStatus === 'unpaid' ? 'linear-gradient(135deg, #be123c 0%, #9f1239 100%)' : 'linear-gradient(135deg, #059669 0%, #047857 100%)' }};">
            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-white/10 rounded-full blur-xl group-hover:scale-125 transition pointer-events-none"></div>
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold tracking-wider text-white/90 uppercase">4. {{ date('M Y') }} Net GST Liability</span>
                @if($gstStatus === 'no_due')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-white/20 text-white backdrop-blur-xs border border-white/30 whitespace-nowrap" title="Excess Input Tax Credit available - ₹0 Tax Payable to Govt">
                        <span class="w-2 h-2 rounded-full bg-emerald-300 shrink-0"></span>
                        <span>ITC CREDIT (₹0 DUE)</span>
                    </span>
                @elseif($gstStatus === 'paid')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-white/20 text-white backdrop-blur-xs border border-white/30 whitespace-nowrap">
                        <span class="w-2 h-2 rounded-full bg-emerald-300 shrink-0"></span>
                        <span>PAID</span>
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-white/20 text-white backdrop-blur-xs border border-white/30 whitespace-nowrap">
                        <span class="w-2 h-2 rounded-full bg-rose-300 animate-pulse shrink-0"></span>
                        <span>UNPAID</span>
                    </span>
                @endif
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black tracking-tight text-white">₹{{ format_indian($currentMonthNetGst, 2) }}</div>
                <div class="text-[11px] font-semibold text-white/90 mt-1 truncate">
                    Sales GST: <span class="font-bold text-white">₹{{ format_indian($salesGstCollected, 2) }}</span> | ITC: <span class="font-bold text-white/80">₹{{ format_indian($purchasesItc, 2) }}</span>
                </div>
            </div>
            <div class="mt-3 pt-2 border-t border-white/20 text-[10px] flex items-center justify-between gap-1.5 font-medium">
                <span class="text-white/70 truncate">Net Tax Payable</span>
                @if($gstStatus === 'no_due')
                    <span class="text-emerald-100 font-bold whitespace-nowrap shrink-0">Excess Credit (Carry Forward)</span>
                @elseif($gstStatus === 'paid' && isset($currentMonthGstExpense) && $currentMonthGstExpense)
                    <span class="text-white font-bold whitespace-nowrap shrink-0">Paid on {{ \Carbon\Carbon::parse($currentMonthGstExpense->expense_date)->format('d/m/Y') }}</span>
                @elseif($gstStatus === 'unpaid')
                    <a href="{{ route('expenses', ['prefill_category' => 'gst_payment', 'prefill_amount' => $currentMonthNetGst, 'prefill_desc' => 'GSTR-3B Tax Paid via Bank Challan']) }}" class="text-white font-black underline hover:text-rose-100 relative z-10 cursor-pointer whitespace-nowrap shrink-0">Log GST Expense →</a>
                @endif
            </div>
        </div>
        @endif
    </div>

    <!-- Secondary Operational Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 {{ \App\Models\Setting::get('simplified_billing_mode', 'false') === 'true' ? 'lg:grid-cols-2' : 'lg:grid-cols-4' }} gap-4">
        <!-- 1. Active Sales Orders -->
        @if(\App\Models\Setting::get('module_orders', 'true') === 'true' && \App\Models\Setting::get('simplified_billing_mode', 'false') !== 'true')
        <div class="bg-blue-100/90 rounded-2xl p-4 shadow-sm border border-blue-300 flex flex-col justify-between hover:shadow-md hover:border-blue-400 transition-all duration-200">
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-blue-600 text-white rounded-xl shadow-xs flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
                <div>
                    <div class="text-xs font-bold text-blue-900/70">Active Sales Orders</div>
                    <div class="text-base font-black text-slate-800">{{ $activeOrdersCount }} Orders in Fabrication</div>
                </div>
            </div>
            <div class="flex items-center justify-end mt-3">
                <a href="{{ route('orders') }}" class="px-3.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-2xs transition-all duration-150 flex items-center gap-1">
                    View <span class="text-xs">→</span>
                </a>
            </div>
        </div>
        @endif

        <!-- 2. Monthly Factory Expense -->
        @if(\App\Models\Setting::get('module_expenses', 'true') === 'true')
        <div class="bg-purple-100/90 rounded-2xl p-4 shadow-sm border border-purple-300 flex flex-col justify-between hover:shadow-md hover:border-purple-400 transition-all duration-200">
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-purple-600 text-white rounded-xl shadow-xs flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <div>
                    <div class="text-xs font-bold text-purple-900/70">Monthly Factory Expense</div>
                    <div class="text-base font-black text-slate-800">₹{{ format_indian($monthlyExpensesTotalOnly, 2) }}</div>
                </div>
            </div>
            <div class="flex items-center justify-end mt-3">
                <a href="{{ route('expenses') }}" class="px-3.5 py-1.5 bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold rounded-xl shadow-2xs transition-all duration-150 flex items-center gap-1">
                    Details <span class="text-xs">→</span>
                </a>
            </div>
        </div>
        @endif

        <!-- 3. Monthly Factory Purchase -->
        @if(\App\Models\Setting::get('module_purchases', 'true') === 'true')
        <div class="bg-amber-100/90 rounded-2xl p-4 shadow-sm border border-amber-300 flex flex-col justify-between hover:shadow-md hover:border-amber-400 transition-all duration-200">
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-amber-600 text-white rounded-xl shadow-xs flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"></path>
                    </svg>
                </div>
                <div>
                    <div class="text-xs font-bold text-amber-900/70">Monthly Factory Purchase</div>
                    <div class="text-base font-black text-slate-800">₹{{ format_indian($monthlyPurchasesTotalOnly, 2) }}</div>
                </div>
            </div>
            <div class="flex items-center justify-end mt-3">
                <a href="{{ route('purchases') }}" class="px-3.5 py-1.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-xl shadow-2xs transition-all duration-150 flex items-center gap-1">
                    Purchases <span class="text-xs">→</span>
                </a>
            </div>
        </div>
        @endif

        <!-- 4. Low Stock Reorder Alerts -->
        @if(\App\Models\Setting::get('module_inventory', 'true') === 'true' && \App\Models\Setting::get('simplified_billing_mode', 'false') !== 'true' && \App\Models\Setting::get('track_stock', 'true') === 'true')
        <div class="bg-rose-100/90 rounded-2xl p-4 shadow-sm border border-rose-300 flex flex-col justify-between hover:shadow-md hover:border-rose-400 transition-all duration-200">
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-rose-600 text-white rounded-xl shadow-xs flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <div>
                    <div class="text-xs font-bold text-rose-900/70">Low Stock Reorder Alerts</div>
                    <div class="text-base font-black text-slate-800">{{ $lowStockCount }} Raw Materials Low</div>
                </div>
            </div>
            <div class="flex items-center justify-end mt-3">
                <a href="{{ route('purchases', ['open' => 1]) }}" class="px-3.5 py-1.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl shadow-2xs transition-all duration-150 flex items-center gap-1">
                    Restock <span class="text-xs">→</span>
                </a>
            </div>
        </div>
        @endif
    </div>


    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- 6-Month Financial Trend Bar Chart -->
        <div class="lg:col-span-2 bg-white rounded-2xl p-5 shadow-xs border border-slate-200 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h2 class="text-base font-bold text-slate-800">6-Month Financial Performance</h2>
                    <p class="text-xs text-slate-500">Total Billed Sales vs Total Outflows (Purchases + Overheads)</p>
                </div>
            </div>
            <div class="relative h-64 w-full">
                <canvas id="financialTrendChart"></canvas>
            </div>
        </div>

        <!-- Top 5 Clients Donut Chart -->
        <div class="bg-white rounded-2xl p-5 shadow-xs border border-slate-200 space-y-4">
            <div class="pb-3 border-b border-slate-100">
                <h2 class="text-base font-bold text-slate-800">Top Client Share (Plant-Wise)</h2>
                <p class="text-xs text-slate-500">Top sales contributing client plants</p>
            </div>
            <div class="relative h-64 w-full flex items-center justify-center">
                <canvas id="topClientsChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Quick Attendance Sheet Modal -->
    <div id="quickAttendanceModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs hidden flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 max-w-4xl w-full shadow-2xl border border-slate-100 dark:border-slate-800 space-y-4 max-h-[90vh] flex flex-col animate-in fade-in zoom-in duration-200">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3 shrink-0">
                <div>
                    <h3 class="text-base font-black text-slate-800 dark:text-slate-100 flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center border border-indigo-100 dark:border-indigo-800 text-sm">📅</span>
                        Today's Quick Attendance Sheet
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-medium pl-10">Mark daily attendance for factory employees for <span class="font-bold text-slate-700 dark:text-slate-200">{{ \Carbon\Carbon::parse($todayDate)->format('d M, Y (l)') }}</span></p>
                </div>
                <button type="button" onclick="closeQuickAttendanceModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 font-bold text-xl cursor-pointer">&times;</button>
            </div>

            <div class="flex-1 overflow-y-auto pr-1 space-y-4 custom-scrollbar">
                <div class="flex items-center justify-between bg-slate-50 dark:bg-slate-800/60 p-3 rounded-2xl border border-slate-200/80 dark:border-slate-700">
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-200">Attendance Summary:</span>
                    @php
                        $pCount = $todayAttendance->where('status', 'present')->count();
                        $hdCount = $todayAttendance->where('status', 'half_day')->count();
                        $aCount = $todayAttendance->where('status', 'absent')->count();
                    @endphp
                    <div class="flex items-center gap-1.5 text-[11px] font-extrabold">
                        <span class="px-2.5 py-1 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 shadow-2xs">{{ $pCount }} Present</span>
                        <span class="px-2.5 py-1 rounded-lg bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800 shadow-2xs">{{ $hdCount }} Half Day</span>
                        <span class="px-2.5 py-1 rounded-lg bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800 shadow-2xs">{{ $aCount }} Absent</span>
                    </div>
                </div>

                @if(isset($allStaff) && $allStaff->count() > 0)
                    <form id="quickAttendanceForm" onsubmit="submitQuickAttendance(event)" class="space-y-4">
                        @csrf
                        <input type="hidden" name="date" value="{{ $todayDate }}">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            @foreach($allStaff as $staff)
                                @php
                                    $currentAtt = $todayAttendance[$staff->id]->status ?? 'present';
                                @endphp
                                <div class="p-3 bg-slate-50/80 dark:bg-slate-800/80 rounded-2xl border border-slate-200/80 dark:border-slate-700 flex items-center justify-between hover:bg-slate-100/60 dark:hover:bg-slate-750 transition group">
                                    <div class="space-y-0.5">
                                        <div class="text-xs font-black text-slate-800 dark:text-slate-100 flex items-center gap-1.5">
                                            <span class="group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition">{{ $staff->full_name }}</span>
                                            <span class="text-[9px] font-extrabold uppercase px-1.5 py-0.2 rounded bg-slate-200/80 dark:bg-slate-700 text-slate-700 dark:text-slate-200">
                                                {{ $staff->wage_type === 'per-day' ? '₹' . format_indian($staff->piece_rate_per_unit, 0) . '/day' : 'Fixed ₹' . format_indian($staff->monthly_salary, 0) }}
                                            </span>
                                        </div>
                                        <div class="text-[10px] text-slate-500 dark:text-slate-400 font-medium">Role: {{ $staff->designation ?? 'Staff Member' }}</div>
                                    </div>

                                    <div class="flex items-center gap-1 bg-white dark:bg-slate-900 p-1 rounded-xl border border-slate-200 dark:border-slate-700 shadow-2xs">
                                        <label class="cursor-pointer">
                                            <input type="radio" name="attendance[{{ $staff->id }}]" value="present" {{ $currentAtt === 'present' ? 'checked' : '' }} class="quick-att-input sr-only">
                                            <span class="quick-att-option quick-att-p px-2.5 py-1 rounded-lg text-[10px] font-black transition inline-block shadow-2xs" title="Present (Full Day 1.0)">P</span>
                                        </label>
                                        <label class="cursor-pointer">
                                            <input type="radio" name="attendance[{{ $staff->id }}]" value="half_day" {{ $currentAtt === 'half_day' ? 'checked' : '' }} class="quick-att-input sr-only">
                                            <span class="quick-att-option quick-att-hd px-2.5 py-1 rounded-lg text-[10px] font-black transition inline-block shadow-2xs" title="Half Day (0.5)">HD</span>
                                        </label>
                                        <label class="cursor-pointer">
                                            <input type="radio" name="attendance[{{ $staff->id }}]" value="absent" {{ $currentAtt === 'absent' ? 'checked' : '' }} class="quick-att-input sr-only">
                                            <span class="quick-att-option quick-att-a px-2.5 py-1 rounded-lg text-[10px] font-black transition inline-block shadow-2xs" title="Absent (0.0)">A</span>
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                            <span class="text-xs text-slate-500 dark:text-slate-400 font-semibold flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span> P = Present (1.0)
                                <span class="w-2 h-2 rounded-full bg-amber-500"></span> HD = Half Day (0.5)
                                <span class="w-2 h-2 rounded-full bg-rose-500"></span> A = Absent (0.0)
                            </span>
                            <div class="flex items-center gap-2">
                                <button type="button" onclick="closeQuickAttendanceModal()" class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-300 hover:text-slate-800 dark:hover:text-white">Cancel</button>
                                <button type="submit" id="saveQuickAttBtn" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-6 rounded-xl text-xs shadow-xs transition flex items-center gap-2 cursor-pointer transform hover:scale-[1.02]">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Save Attendance Sheet
                                </button>
                            </div>
                        </div>
                    </form>
                @else
                    <div class="p-5 bg-slate-50 dark:bg-slate-800/60 rounded-2xl text-center border border-slate-100 dark:border-slate-700 text-xs text-slate-500 dark:text-slate-400 font-medium space-y-2">
                        <p>No active staff members registered in payroll directory.</p>
                        <a href="{{ route('employees') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-600 text-white rounded-xl font-bold text-xs shadow-2xs hover:bg-indigo-700 transition">
                            + Add Employee Profile
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if(\App\Models\Setting::get('simplified_billing_mode', 'false') === 'true')
    <!-- Simplified Billing Mode: Single Balanced 3-Column Grid (Invoices | Purchases | Expenses) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        @include('pages.overview.partials.recent_invoices')
        @include('pages.overview.partials.recent_purchases')
        @include('pages.overview.partials.recent_expenses')
    </div>
    @else
    <!-- Full Manufacturing ERP Mode: 2 Rows of 3-Column Grids -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <!-- 1. Recent Production Logs -->
        @if(\App\Models\Setting::get('module_production', 'true') === 'true')
        <div class="bg-white rounded-2xl p-5 shadow-xs border border-slate-200 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h2 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                    <span>🔨</span> Recent Production Logs
                </h2>
                <a href="{{ route('production') }}" class="text-xs font-bold text-emerald-600 hover:underline">Production →</a>
            </div>
            <div class="space-y-2">
                @forelse($recentProductionLogs as $log)
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 flex items-center justify-between hover:bg-white transition">
                        <div>
                            <div class="text-xs font-black text-slate-800 flex items-center gap-1.5 flex-wrap">
                                <span>{{ $log->product->product_name ?? 'Finished Good' }}</span>
                                @if(isset($log->product->current_stock))
                                    <span class="text-[10px] font-extrabold text-blue-700 bg-blue-50 border border-blue-200 px-1.5 py-0.2 rounded">Stock: {{ format_indian($log->product->current_stock, 0) }} {{ $log->product->uom ?? $log->product->unit ?? 'Pcs' }}</span>
                                @endif
                            </div>
                            <div class="text-[11px] font-medium text-slate-500 mt-0.5">
                                {{ $log->production_date ? $log->production_date->format('d M Y') : 'N/A' }}
                                @if($log->quantity_rejected > 0)
                                    • <span class="text-rose-600 font-semibold">{{ $log->quantity_rejected }} Rej</span>
                                @endif
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-xs font-black text-emerald-600">+{{ format_indian($log->quantity_manufactured, 0) }} {{ $log->product->unit ?? 'Pcs' }}</div>
                            <span class="inline-block text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200 mt-0.5">
                                COMPLETED
                            </span>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 text-center py-4">No recent production logs recorded.</p>
                @endforelse
            </div>
        </div>
        @endif

        <!-- 2. Active Sales Orders -->
        @if(\App\Models\Setting::get('module_orders', 'true') === 'true')
        <div class="bg-white rounded-2xl p-5 shadow-xs border border-slate-200 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h2 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                    <span>📦</span> Active Sales Orders
                </h2>
                <a href="{{ route('orders') }}" class="text-xs font-bold text-blue-600 hover:underline">All Orders →</a>
            </div>
            <div class="space-y-2">
                @forelse($recentOrders as $order)
                    <div id="dash-order-card-{{ $order->id }}" class="p-3 bg-slate-50 rounded-xl border border-slate-100 flex items-center justify-between hover:bg-white transition">
                        <div>
                            <div class="text-xs font-black text-slate-800">#{{ $order->order_number }}</div>
                            <div class="text-[11px] font-medium text-slate-500 truncate max-w-[180px]">
                                {{ $order->client->company_name ?? ($order->plant->client->company_name ?? 'Client') }}
                                @if($order->plant && $order->plant->plant_name)
                                    <span class="text-[10px] font-bold text-blue-600">({{ $order->plant->plant_name }})</span>
                                @endif
                            </div>
                        </div>
                        <div class="text-right whitespace-nowrap">
                            <div class="text-xs font-bold text-slate-900">₹{{ format_indian($order->total_amount, 2) }}</div>
                            @php
                                $ordHasStock = $order->hasSufficientStock();
                                $trackStockEnabled = (\App\Models\Setting::get('track_stock', 'true') === 'true');
                            @endphp

                            <div class="dash-order-action-cell mt-1" id="dash-order-action-{{ $order->id }}">
                                @if ($order->status === 'pending')
                                    <button type="button" 
                                            onclick="updateOrderStatusFromDashboard({{ $order->id }}, 'in_production', this)"
                                            title="Click to start manufacturing order"
                                            class="px-2 py-0.5 bg-amber-500 hover:bg-amber-600 text-white text-[9.5px] font-extrabold rounded-md shadow-2xs transition inline-flex items-center space-x-1 cursor-pointer">
                                        <span>▶ Start Production</span>
                                    </button>
                                @elseif ($order->status === 'in_production')
                                    @if (!$trackStockEnabled || $ordHasStock)
                                        <button type="button" 
                                                onclick="updateOrderStatusFromDashboard({{ $order->id }}, 'ready_for_dispatch', this)"
                                                title="{{ $trackStockEnabled ? 'Stock ready! Click to mark Ready for Dispatch' : 'Click to mark Ready for Dispatch' }}"
                                                class="px-2 py-0.5 bg-blue-600 hover:bg-blue-700 text-white text-[9.5px] font-extrabold rounded-md shadow-2xs transition inline-flex items-center space-x-1 cursor-pointer {{ $trackStockEnabled ? 'animate-pulse' : '' }}">
                                            <span>📦 Mark Ready</span>
                                        </button>
                                    @else
                                        <span class="inline-block text-[9.5px] font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 border border-amber-300">
                                            ⏳ Awaiting Stock
                                        </span>
                                    @endif
                                @elseif ($order->status === 'ready_for_dispatch')
                                    <a href="{{ route('invoices', ['order_id' => $order->id]) }}" 
                                       title="Stock ready! Click to generate Tax Invoice & dispatch"
                                       class="px-2 py-0.5 bg-indigo-600 hover:bg-indigo-700 text-white text-[9.5px] font-extrabold rounded-md shadow-2xs transition inline-flex items-center space-x-1">
                                        <span>🚀 Gen Invoice</span>
                                    </a>
                                @else
                                    <span class="inline-block text-[9.5px] font-extrabold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-300">
                                        ✓ {{ strtoupper($order->status) }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 text-center py-4">No active sales orders.</p>
                @endforelse
            </div>
        </div>
        @endif

        <!-- 3. Recent Invoices -->
        @include('pages.overview.partials.recent_invoices')
    </div>

    <!-- Row 2 Below Row 1: Recent 5 Purchase Bills, Recent 5 Factory Expenses, Low Stock Alerts (3-Column Grid) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <!-- 1. Latest 5 Purchase Records -->
        @include('pages.overview.partials.recent_purchases')

        <!-- 2. Latest 5 Expense Records -->
        @include('pages.overview.partials.recent_expenses')

        <!-- 3. Low Stock Inventory Alerts -->
        @if(\App\Models\Setting::get('module_inventory', 'true') === 'true' && \App\Models\Setting::get('track_stock', 'true') === 'true')
        <div class="bg-white rounded-2xl p-5 shadow-xs border border-slate-200 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h2 class="text-sm font-bold text-slate-800 flex items-center gap-2 text-rose-700">
                    <span>⚠️</span> Low Stock Alerts
                </h2>
                <a href="{{ route('rawmaterial') }}" class="text-xs font-bold text-rose-600 hover:underline">Raw Materials →</a>
            </div>
            <div class="space-y-2">
                @forelse($lowStockMaterials as $mat)
                    @php
                        $suggestedQty = $mat->suggested_reorder_quantity;
                        $rate = (float)($mat->average_purchase_price ?? 0);
                    @endphp
                    <div class="p-2.5 bg-rose-50/60 rounded-xl border border-rose-100 flex items-center justify-between gap-2">
                        <div class="min-w-0">
                            <div class="text-xs font-bold text-slate-800 truncate">{{ $mat->material_name }}</div>
                            <div class="text-[10.5px] font-medium text-slate-500 flex items-center gap-1.5 mt-0.5">
                                <span class="font-bold text-rose-700 font-mono">{{ format_indian($mat->current_stock, 1) }}</span>
                                <span class="text-slate-400">/ Min: {{ format_indian($mat->safety_threshold, 1) }} {{ $mat->unit }}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5 shrink-0">
                            <a href="{{ route('purchases', ['prefill_raw_material' => $mat->id, 'prefill_qty' => $suggestedQty, 'prefill_price' => $rate]) }}"
                               title="1-Click Launch Purchase Bill"
                               class="px-2.5 py-1 bg-rose-600 hover:bg-rose-700 text-white text-[10px] font-bold rounded-lg shadow-2xs transition flex items-center gap-1 cursor-pointer">
                                <span>⚡ Restock</span>
                                <span class="font-mono text-[9px]">({{ number_format($suggestedQty, 0) }})</span>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="p-3 bg-emerald-50/60 rounded-xl border border-emerald-100 flex items-center justify-center space-x-2 text-emerald-700 text-xs font-semibold">
                        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span>All raw material stock levels are optimal and healthy.</span>
                    </div>
                @endforelse
            </div>
        </div>
        @endif
    </div>
    @endif
</div>

<script>
(function initOverviewCharts() {
    let retries = 0;
    function renderCharts() {
        const trendElem = document.getElementById('financialTrendChart');
        const clientElem = document.getElementById('topClientsChart');

        if (!trendElem || !clientElem) return;

        if (typeof window.Chart === 'undefined') {
            if (retries < 20) {
                retries++;
                setTimeout(renderCharts, 100);
            }
            return;
        }

        if (window.financialTrendChartInstance) {
            window.financialTrendChartInstance.destroy();
            window.financialTrendChartInstance = null;
        }
        if (window.topClientsChartInstance) {
            window.topClientsChartInstance.destroy();
            window.topClientsChartInstance = null;
        }

        // 1. Smooth Gradient Dual Line & Area Chart (6-Month Financial Performance)
        const trendCtx = trendElem.getContext('2d');
        
        // Create Emerald Sales Gradient
        const salesGrad = trendCtx.createLinearGradient(0, 0, 0, 260);
        salesGrad.addColorStop(0, 'rgba(16, 185, 129, 0.28)');
        salesGrad.addColorStop(1, 'rgba(16, 185, 129, 0.0)');

        // Create Indigo Outflows Gradient
        const expGrad = trendCtx.createLinearGradient(0, 0, 0, 260);
        expGrad.addColorStop(0, 'rgba(99, 102, 241, 0.20)');
        expGrad.addColorStop(1, 'rgba(99, 102, 241, 0.0)');

        window.financialTrendChartInstance = new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: @json($chartMonths),
                datasets: [
                    {
                        label: 'Total Billed Sales (₹)',
                        data: @json($chartSalesData),
                        borderColor: '#10b981',
                        backgroundColor: salesGrad,
                        fill: true,
                        tension: 0.4,
                        borderWidth: 3,
                        pointBackgroundColor: '#10b981',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 7,
                    },
                    {
                        label: 'Factory Outflows (₹)',
                        data: @json($chartExpenseData),
                        borderColor: '#6366f1',
                        backgroundColor: expGrad,
                        fill: true,
                        tension: 0.4,
                        borderWidth: 3,
                        pointBackgroundColor: '#6366f1',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 7,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            pointStyle: 'circle',
                            font: { family: 'Outfit, sans-serif', size: 11, weight: 'bold' }
                        }
                    },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        titleFont: { family: 'Outfit, sans-serif', size: 12, weight: 'bold' },
                        bodyFont: { family: 'Outfit, sans-serif', size: 11 },
                        padding: 10,
                        cornerRadius: 10,
                        callbacks: {
                            label: function(ctx) {
                                let label = ctx.dataset.label || '';
                                if (label) label += ': ';
                                label += '₹' + window.formatIndianCurrency(ctx.parsed.y, 2);
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f1f5f9' },
                        ticks: {
                            font: { family: 'Outfit, sans-serif', size: 10, weight: '600' },
                            callback: function(value) { return '₹' + window.formatIndianCurrency(value, 0); }
                        }
                    }
                }
            }
        });

        // 2. Vibrant Multi-Color Client Revenue Share Donut Chart
        const topClientsData = @json($topClientsData);
        const clientNames = topClientsData.map(c => c.name);
        const clientSales = topClientsData.map(c => c.sales);
        const totalSalesSum = clientSales.reduce((a, b) => a + b, 0);

        const clientCtx = clientElem.getContext('2d');
        window.topClientsChartInstance = new Chart(clientCtx, {
            type: 'doughnut',
            data: {
                labels: clientNames.length ? clientNames : ['General Clients'],
                datasets: [{
                    data: clientSales.length ? clientSales : [1],
                    backgroundColor: ['#10b981', '#3b82f6', '#8b5cf6', '#f59e0b', '#ec4899'],
                    hoverBackgroundColor: ['#059669', '#2563eb', '#7c3aed', '#d97706', '#db2777'],
                    borderWidth: 3,
                    borderColor: '#ffffff',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            pointStyle: 'circle',
                            font: { family: 'Outfit, sans-serif', size: 10, weight: 'bold' }
                        }
                    },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        titleFont: { family: 'Outfit, sans-serif', size: 12, weight: 'bold' },
                        bodyFont: { family: 'Outfit, sans-serif', size: 11 },
                        padding: 10,
                        cornerRadius: 10,
                        callbacks: {
                            label: function(ctx) {
                                const val = ctx.parsed;
                                const pct = totalSalesSum > 0 ? ((val / totalSalesSum) * 100).toFixed(1) : 0;
                                return ` ${ctx.label}: ₹${window.formatIndianCurrency(val, 2)} (${pct}%)`;
                            }
                        }
                    }
                }
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => setTimeout(renderCharts, 50));
    } else {
        renderCharts();
        setTimeout(renderCharts, 50);
    }
})();

function updateDashboardOrderStatus(id, selectEl) {
    const status = (typeof selectEl === 'object' && selectEl !== null) ? selectEl.value : selectEl;
    if (typeof selectEl === 'object' && selectEl !== null) {
        applyStatusSelectColor(selectEl);
    }
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
    $.ajax({
        url: `/orders/${id}/status`,
        method: 'PATCH',
        data: { status: status, _token: token },
        success: function(res) {
            if (window.showToast) window.showToast('success', res.message);
        },
        error: function(xhr) {
            alert(xhr.responseJSON?.message || 'Failed to update order status.');
        }
    });
}

function applyStatusSelectColor(selectEl) {
    const val = selectEl.value;
    selectEl.classList.remove(
        'text-amber-600', 'border-amber-300',
        'text-blue-600', 'border-blue-300',
        'text-indigo-600', 'border-indigo-300',
        'text-emerald-600', 'border-emerald-300',
        'text-rose-600', 'border-rose-300'
    );
    selectEl.style.backgroundColor = '#ffffff';

    if (val === 'pending') {
        selectEl.classList.add('text-amber-600', 'border-amber-300');
    } else if (val === 'in_production') {
        selectEl.classList.add('text-blue-600', 'border-blue-300');
    } else if (val === 'ready_for_dispatch') {
        selectEl.classList.add('text-indigo-600', 'border-indigo-300');
    } else if (val === 'dispatched' || val === 'completed') {
        selectEl.classList.add('text-emerald-600', 'border-emerald-300');
    } else if (val === 'cancelled') {
        selectEl.classList.add('text-rose-600', 'border-rose-300');
    }
}

function openDashboardPayModal(invoiceId, invoiceNumber, dueAmount) {
    document.getElementById('dash_pay_invoice_id').value = invoiceId;
    document.getElementById('dash_pay_invoice_num').value = invoiceNumber;
    document.getElementById('dash_pay_amount').value = dueAmount;
    document.getElementById('dashboardPayModal').classList.remove('hidden');
}

function closeDashboardPayModal() {
    document.getElementById('dashboardPayModal').classList.add('hidden');
}

function submitDashboardPayForm(e) {
    e.preventDefault();
    const id = document.getElementById('dash_pay_invoice_id').value;
    const form = document.getElementById('dashboardPayForm');
    const formData = new FormData(form);
    formData.append('_token', '{{ csrf_token() }}');

    $.ajax({
        url: `/invoices/${id}/record-payment`,
        method: 'POST',
        data: Object.fromEntries(formData),
        success: function(res) {
            closeDashboardPayModal();
            if (window.showToast) window.showToast('success', res.message || 'Payment recorded successfully!');
            const $payBtn = $(`#dash-pay-btn-${id}`);
            if ($payBtn.length) {
                $payBtn.replaceWith(`
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-300">
                        RECEIVED
                    </span>
                `);
            }
            if (window.clearPageCache) window.clearPageCache();
        },
        error: function(xhr) {
            const msg = xhr.responseJSON?.message || xhr.responseJSON?.errors?.[0] || 'Failed to record payment.';
            if (window.showToast) {
                window.showToast('danger', msg);
            } else {
                alert(msg);
            }
        }
    });
}
</script>

<!-- Quick Invoice Payment Modal for Overview Dashboard -->
<div id="dashboardPayModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6 space-y-4 border border-slate-200 animate-in fade-in zoom-in duration-200">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                <span>💳</span> Record Invoice Payment
            </h3>
            <button type="button" onclick="closeDashboardPayModal()" class="text-slate-400 hover:text-slate-600 font-bold text-lg">&times;</button>
        </div>
        <form id="dashboardPayForm" onsubmit="submitDashboardPayForm(event)" class="space-y-4">
            @csrf
            <input type="hidden" id="dash_pay_invoice_id" name="invoice_id">
            
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Invoice Reference</label>
                <input type="text" id="dash_pay_invoice_num" readonly class="w-full bg-slate-100 border border-slate-200 rounded-xl py-2 px-3 text-xs font-mono font-bold text-slate-700">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Payment Amount (₹)</label>
                    <input type="number" step="0.01" min="0.01" id="dash_pay_amount" name="amount" required class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Payment Date</label>
                    <input type="date" id="dash_pay_date" name="payment_date" value="{{ date('Y-m-d') }}" required class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs font-medium text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Payment Mode</label>
                    <select name="payment_method" id="dash_pay_method" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="bank_transfer">Bank Transfer (NEFT/RTGS)</option>
                        <option value="upi">UPI / Online</option>
                        <option value="cheque">Cheque</option>
                        <option value="cash">Cash</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Account Type</label>
                    <select name="account_type" id="dash_pay_account" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="bank">Bank Account</option>
                        <option value="cash">Cash Account</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Reference / UTR No. (Optional)</label>
                <input type="text" name="reference_number" placeholder="e.g. UTR1298471203" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="closeDashboardPayModal()" class="px-4 py-2 text-xs font-bold text-slate-600 hover:text-slate-800">Cancel</button>
                <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-xs transition">Confirm & Save Payment</button>
            </div>
        </form>
    </div>
</div>
<script>
    function updateOrderStatusFromDashboard(id, status, btnEl) {
        const $btn = btnEl ? $(btnEl) : null;
        if ($btn) {
            $btn.prop('disabled', true).addClass('opacity-50 pointer-events-none');
        }
        const token = $('meta[name="csrf-token"]').attr('content') || '';
        $.ajax({
            url: `/orders/${id}/status`,
            method: 'PATCH',
            data: { status: status, _token: token },
            success: function(res) {
                if (window.showToast) window.showToast('success', res.message || 'Status updated!');

                const actualStatus = res.status || status;
                const hasStock = res.has_stock !== undefined ? res.has_stock : true;
                const trackStockEnabled = res.track_stock !== undefined ? res.track_stock : true;

                const $actionCell = $(`#dash-order-action-${id}`);
                if ($actionCell.length) {
                    if (actualStatus === 'pending') {
                        $actionCell.html(`
                            <button type="button" 
                                    onclick="updateOrderStatusFromDashboard(${id}, 'in_production', this)"
                                    title="Click to start manufacturing order"
                                    class="px-2 py-0.5 bg-amber-500 hover:bg-amber-600 text-white text-[9.5px] font-extrabold rounded-md shadow-2xs transition inline-flex items-center space-x-1 cursor-pointer">
                                <span>▶ Start Production</span>
                            </button>
                        `);
                    } else if (actualStatus === 'in_production') {
                        if (!trackStockEnabled || hasStock) {
                            $actionCell.html(`
                                <button type="button" 
                                        onclick="updateOrderStatusFromDashboard(${id}, 'ready_for_dispatch', this)"
                                        title="${trackStockEnabled ? 'Stock ready! Click to mark Ready for Dispatch' : 'Click to mark Ready for Dispatch'}"
                                        class="px-2 py-0.5 bg-blue-600 hover:bg-blue-700 text-white text-[9.5px] font-extrabold rounded-md shadow-2xs transition inline-flex items-center space-x-1 cursor-pointer ${trackStockEnabled ? 'animate-pulse' : ''}">
                                    <span>📦 Mark Ready</span>
                                </button>
                            `);
                        } else {
                            $actionCell.html(`
                                <span class="inline-block text-[9.5px] font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 border border-amber-300">
                                    ⏳ Awaiting Stock
                                </span>
                            `);
                        }
                    } else if (actualStatus === 'ready_for_dispatch') {
                        $actionCell.html(`
                            <a href="/invoices?order_id=${id}" 
                               title="Stock ready! Click to generate Tax Invoice & dispatch"
                               class="px-2 py-0.5 bg-indigo-600 hover:bg-indigo-700 text-white text-[9.5px] font-extrabold rounded-md shadow-2xs transition inline-flex items-center space-x-1">
                                <span>🚀 Gen Invoice</span>
                            </a>
                        `);
                    } else {
                        $actionCell.html(`
                            <span class="inline-block text-[9.5px] font-extrabold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-300">
                                ✓ ${actualStatus.toUpperCase().replace('_', ' ')}
                            </span>
                        `);
                    }
                }
                if (window.clearPageCache) window.clearPageCache();
            },
            error: function(xhr) {
                if ($btn) {
                    $btn.prop('disabled', false).removeClass('opacity-50 pointer-events-none');
                }
                const msg = xhr.responseJSON?.message || 'Failed to update order status.';
                if (window.showToast) {
                    window.showToast('error', msg);
                } else {
                    alert(msg);
                }
            }
        });
    }

    function openQuickAttendanceModal() {
        const modal = document.getElementById('quickAttendanceModal');
        if (modal) modal.classList.remove('hidden');
    }

    function closeQuickAttendanceModal() {
        const modal = document.getElementById('quickAttendanceModal');
        if (modal) modal.classList.add('hidden');
    }

    function submitQuickAttendance(e) {
        e.preventDefault();
        const btn = document.getElementById('saveQuickAttBtn');
        const origHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = `
            <svg class="animate-spin w-4 h-4 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Saving...`;

        const form = document.getElementById('quickAttendanceForm');
        const formData = new FormData(form);
        const token = $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}';
        
        const attendanceObj = {};
        for (let [key, value] of formData.entries()) {
            if (key.startsWith('attendance[')) {
                const staffId = key.match(/\[(.*?)\]/)[1];
                attendanceObj[staffId] = value;
            }
        }

        $.ajax({
            url: '{{ route("employees.attendance.store") }}',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': token
            },
            contentType: 'application/json',
            data: JSON.stringify({
                date: '{{ $todayDate }}',
                attendance: attendanceObj
            }),
            success: function(res) {
                btn.disabled = false;
                btn.innerHTML = origHtml;
                closeQuickAttendanceModal();
                if (window.showToast) {
                    window.showToast('success', res.message || "Today's attendance saved successfully!");
                } else {
                    alert(res.message || "Attendance saved!");
                }
            },
            error: function(xhr) {
                btn.disabled = false;
                btn.innerHTML = origHtml;
                const msg = xhr.responseJSON?.message || 'Failed to save attendance.';
                if (window.showToast) {
                    window.showToast('error', msg);
                } else {
                    alert(msg);
                }
            }
        });
    }
</script>
@endsection
