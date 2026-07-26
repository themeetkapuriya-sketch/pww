@extends('layouts.app')

@section('title', 'Overview Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Top Header -->
    <div class="pb-4 border-b border-slate-200">
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

    <!-- 3 Net Revenue Cards (Lifetime Revenue, Annual Revenue, Monthly Revenue) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- 1. Lifetime Revenue -->
        <div class="bg-gradient-to-br from-emerald-600 to-teal-800 text-white rounded-2xl p-5 shadow-sm relative overflow-hidden group hover:shadow-md transition border border-emerald-500/50">
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
        <div class="bg-gradient-to-br from-indigo-700 to-indigo-900 text-white rounded-2xl p-5 shadow-sm relative overflow-hidden group hover:shadow-md transition border border-indigo-600/50">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold tracking-wider text-indigo-200 uppercase">2. Annual Revenue</span>
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
        <div class="bg-gradient-to-br from-blue-600 to-blue-800 text-white rounded-2xl p-5 shadow-sm relative overflow-hidden group hover:shadow-md transition border border-blue-500/50">
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
        <div class="bg-gradient-to-br from-indigo-700 to-indigo-900 text-white rounded-2xl p-5 shadow-sm relative overflow-hidden group hover:shadow-md transition border border-indigo-600/50">
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
                <div class="text-[11px] font-semibold text-indigo-300/90 mt-1 flex items-center gap-1">
                    <span>Taxable Base:</span>
                    <span class="font-bold text-white">₹{{ format_indian($yearlyTaxable, 2) }}</span>
                </div>
            </div>
            <div class="mt-3 pt-2 border-t border-indigo-800/40 text-[10px] text-indigo-300/70 font-medium">
                Annual Billed Sales Total (Incl. GST)
            </div>
        </div>

        <!-- 2. Monthly Revenue (Option 1) -->
        <div class="bg-gradient-to-br from-blue-600 to-blue-800 text-white rounded-2xl p-5 shadow-sm relative overflow-hidden group hover:shadow-md transition border border-blue-500/50">
            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-blue-400/10 rounded-full blur-xl group-hover:scale-125 transition"></div>
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

        <!-- 3. Outstanding Receivables -->
        <div class="bg-white rounded-2xl p-5 shadow-xs border border-slate-200 relative group hover:border-amber-300 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold tracking-wider text-slate-500 uppercase">3. Outstanding Receivables</span>
                <span class="p-2 bg-amber-50 text-amber-600 rounded-xl border border-amber-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black tracking-tight text-slate-900">₹{{ format_indian($totalReceivables, 2) }}</div>
                <div class="text-[11px] font-bold text-amber-600 mt-1 flex items-center gap-1">
                    <span class="inline-block w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                    <span>Pending Client Collections</span>
                </div>
            </div>
            <div class="mt-3 pt-2 border-t border-slate-100 text-[10px] text-slate-400 font-medium">
                Unpaid Client Invoice Balance Total
            </div>
        </div>

        <!-- 4. Net GST Payable -->
        <div class="bg-white rounded-2xl p-5 shadow-xs border border-slate-200 relative group hover:border-emerald-300 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold tracking-wider text-slate-500 uppercase">4. {{ date('M Y') }} Net GST Liability</span>
                @if($currentMonthGstPaid)
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
            <div class="mt-3">
                <div class="text-2xl font-black tracking-tight {{ $currentMonthGstPaid ? 'text-emerald-700' : 'text-rose-700' }}">₹{{ format_indian($currentMonthNetGst, 2) }}</div>
                <div class="text-[11px] font-semibold text-slate-600 mt-1 truncate">
                    Sales GST: <span class="font-bold text-slate-800">₹{{ format_indian($salesGstCollected, 2) }}</span> | ITC: <span class="font-bold text-emerald-600">₹{{ format_indian($purchasesItc, 2) }}</span>
                </div>
            </div>
            <div class="mt-3 pt-2 border-t border-slate-100 text-[10px] flex items-center justify-between font-medium">
                <span class="text-slate-400">Net Tax Payable (Outward − ITC)</span>
                @if($currentMonthGstPaid && isset($currentMonthGstExpense) && $currentMonthGstExpense)
                    <span class="text-emerald-700 font-bold">Paid on {{ \Carbon\Carbon::parse($currentMonthGstExpense->expense_date)->format('d/m/Y') }}</span>
                @elseif(!$currentMonthGstPaid)
                    <a href="{{ route('expenses', ['prefill_category' => 'gst_payment', 'prefill_amount' => $currentMonthNetGst, 'prefill_desc' => 'GSTR-3B Tax Paid via Bank Challan']) }}" class="text-rose-600 font-bold hover:underline">Log GST Expense →</a>
                @endif
            </div>
        </div>
    </div>

    <!-- Secondary Operational Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Active Sales Orders -->
        <div class="bg-white rounded-2xl p-4 shadow-xs border border-slate-200 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-blue-50 text-blue-600 rounded-xl border border-blue-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
                <div>
                    <div class="text-xs font-bold text-slate-500">Active Sales Orders</div>
                    <div class="text-lg font-black text-slate-800">{{ $activeOrdersCount }} Orders in Fabrication</div>
                </div>
            </div>
            <a href="{{ route('orders') }}" class="text-xs font-bold text-blue-600 hover:text-blue-800 hover:underline">View →</a>
        </div>

        <!-- Monthly Expenses -->
        <div class="bg-white rounded-2xl p-4 shadow-xs border border-slate-200 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-purple-50 text-purple-600 rounded-xl border border-purple-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <div>
                    <div class="text-xs font-bold text-slate-500">Monthly Factory Outflows</div>
                    <div class="text-lg font-black text-slate-800">₹{{ format_indian($monthlyExpenses, 2) }}</div>
                </div>
            </div>
            <a href="{{ route('expenses') }}" class="text-xs font-bold text-purple-600 hover:text-purple-800 hover:underline">Details →</a>
        </div>

        <!-- Raw Material Stock Alerts -->
        <div class="bg-white rounded-2xl p-4 shadow-xs border border-slate-200 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-rose-50 text-rose-600 rounded-xl border border-rose-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <div>
                    <div class="text-xs font-bold text-slate-500">Low Stock Reorder Alerts</div>
                    <div class="text-lg font-black text-slate-800">{{ $lowStockCount }} Raw Materials Low</div>
                </div>
            </div>
            <a href="{{ route('inventory') }}" class="text-xs font-bold text-rose-600 hover:text-rose-800 hover:underline">Restock →</a>
        </div>
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
                <span class="text-xs font-bold text-blue-600 bg-blue-50 px-2.5 py-1 rounded-lg border border-blue-100">Historical Comparison</span>
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

    <!-- Row 1 Below Chart: 1. Production Logs, 2. Active Sales Orders, 3. Recent Invoices (3-Column Grid) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        
        <!-- 1. Recent Production Logs -->
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
                            <div class="text-xs font-black text-slate-800">
                                {{ $log->product->product_name ?? 'Finished Good' }}
                            </div>
                            <div class="text-[11px] font-medium text-slate-500">
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

        <!-- 2. Active Sales Orders -->
        <div class="bg-white rounded-2xl p-5 shadow-xs border border-slate-200 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h2 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                    <span>📦</span> Active Sales Orders
                </h2>
                <a href="{{ route('orders') }}" class="text-xs font-bold text-blue-600 hover:underline">All Orders →</a>
            </div>
            <div class="space-y-2">
                @forelse($recentOrders as $order)
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 flex items-center justify-between hover:bg-white transition">
                        <div>
                            <div class="text-xs font-black text-slate-800">#{{ $order->order_number }}</div>
                            <div class="text-[11px] font-medium text-slate-500 truncate max-w-[180px]">
                                {{ $order->client->company_name ?? ($order->plant->client->company_name ?? 'Client') }}
                                @if($order->plant && $order->plant->plant_name)
                                    <span class="text-[10px] font-bold text-blue-600">({{ $order->plant->plant_name }})</span>
                                @endif
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-xs font-bold text-slate-900">₹{{ format_indian($order->total_amount, 2) }}</div>
                            <select onchange="updateDashboardOrderStatus({{ $order->id }}, this)" 
                                     class="text-[10px] font-bold uppercase rounded-full px-2 py-0.5 focus:outline-none border shadow-2xs bg-white mt-0.5 cursor-pointer 
                                     {{ $order->status === 'pending' ? 'text-amber-600 border-amber-300' : '' }}
                                     {{ $order->status === 'in_production' ? 'text-blue-600 border-blue-300' : '' }}
                                     {{ $order->status === 'ready_for_dispatch' ? 'text-indigo-600 border-indigo-300' : '' }}
                                     {{ $order->status === 'dispatched' || $order->status === 'completed' ? 'text-emerald-600 border-emerald-300' : '' }}
                                     {{ $order->status === 'cancelled' ? 'text-rose-600 border-rose-300' : '' }}"
                                     style="background-color: #ffffff !important;">
                                <option value="pending" class="bg-white text-amber-600 font-bold" style="background-color: #ffffff; color: #d97706;" {{ $order->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="in_production" class="bg-white text-blue-600 font-bold" style="background-color: #ffffff; color: #2563eb;" {{ $order->status === 'in_production' ? 'selected' : '' }}>In Production</option>
                                <option value="ready_for_dispatch" class="bg-white text-indigo-600 font-bold" style="background-color: #ffffff; color: #4f46e5;" {{ $order->status === 'ready_for_dispatch' ? 'selected' : '' }}>Ready For Dispatch</option>
                                <option value="dispatched" class="bg-white text-emerald-600 font-bold" style="background-color: #ffffff; color: #16a34a;" {{ $order->status === 'dispatched' || $order->status === 'completed' ? 'selected' : '' }}>Dispatched</option>
                                <option value="cancelled" class="bg-white text-rose-600 font-bold" style="background-color: #ffffff; color: #dc2626;" {{ $order->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 text-center py-4">No active sales orders.</p>
                @endforelse
            </div>
        </div>

        <!-- 3. Recent Invoices -->
        <div class="bg-white rounded-2xl p-5 shadow-xs border border-slate-200 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h2 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                    <span>🧾</span> Recent Invoices
                </h2>
                <a href="{{ route('invoices') }}" class="text-xs font-bold text-blue-600 hover:underline">All Invoices →</a>
            </div>
            <div class="space-y-2">
                @forelse($recentInvoices as $inv)
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 flex items-center justify-between hover:bg-white transition">
                        <div>
                            <div class="text-xs font-black text-slate-800">{{ $inv->invoice_number }}</div>
                            <div class="text-[11px] font-medium text-slate-500 truncate max-w-[180px]">
                                {{ $inv->plant->client->company_name ?? 'Client' }}
                                @if($inv->plant && $inv->plant->plant_name)
                                    <span class="text-[10px] font-bold text-blue-600">({{ $inv->plant->plant_name }})</span>
                                @endif
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-xs font-bold text-slate-900">₹{{ format_indian($inv->total_amount, 2) }}</div>
                            @if(($inv->payment_status ?? 'unpaid') === 'paid')
                                <span class="inline-block text-[10px] font-extrabold px-2 py-0.5 rounded-md mt-0.5 bg-emerald-100 text-emerald-700">
                                    PAID
                                </span>
                            @else
                                <button type="button" 
                                        onclick="openDashboardPayModal({{ $inv->id }}, '{{ $inv->invoice_number }}', {{ $inv->remaining_balance }})"
                                        class="inline-block text-[10px] font-extrabold px-2 py-0.5 rounded-md mt-0.5 transition cursor-pointer border shadow-2xs 
                                        {{ $inv->payment_status === 'partially_paid' ? 'bg-amber-100 text-amber-800 border-amber-300 hover:bg-amber-200' : 'bg-rose-100 text-rose-800 border-rose-300 hover:bg-rose-200' }}"
                                        title="Click to record payment directly from Dashboard">
                                    {{ $inv->payment_status === 'partially_paid' ? 'Partial (₹' . format_indian($inv->remaining_balance, 0) . ' Due)' : 'Pay (₹' . format_indian($inv->remaining_balance, 0) . ' Due)' }}
                                </button>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 text-center py-4">No recent invoices logged.</p>
                @endforelse
            </div>
        </div>

    </div>

    <!-- Row 2 Below Row 1: Recent 5 Purchase Bills, Recent 5 Factory Expenses, Low Stock Alerts (3-Column Grid) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <!-- 1. Latest 5 Purchase Records -->
        <div class="bg-white rounded-2xl p-5 shadow-xs border border-slate-200 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h2 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                    <span>🛒</span> Recent 5 Purchase Bills
                </h2>
                <a href="{{ route('purchases') }}" class="text-xs font-bold text-purple-600 hover:underline">View Purchases →</a>
            </div>
            <div class="space-y-2">
                @forelse($latestPurchases as $pur)
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 flex items-center justify-between hover:bg-white transition">
                        <div>
                            <div class="text-xs font-black text-slate-800">
                                {{ $pur->rawMaterial->material_name ?? ($pur->item_name ?? ucfirst(str_replace('_', ' ', $pur->purchase_type))) }}
                            </div>
                            <div class="text-[11px] font-medium text-slate-500">
                                {{ $pur->purchase_date ? $pur->purchase_date->format('d M Y') : 'N/A' }}
                                @if($pur->vendor_name)
                                    • <span class="font-semibold text-slate-700">{{ $pur->vendor_name }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-xs font-black text-purple-700">₹{{ format_indian($pur->total_amount, 2) }}</div>
                            @if($pur->quantity > 0)
                                <div class="text-[10px] font-bold text-slate-400">{{ format_indian($pur->quantity, 2) }} {{ $pur->unit ?? 'Units' }}</div>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 text-center py-4">No recent purchase records logged.</p>
                @endforelse
            </div>
        </div>

        <!-- 2. Latest 5 Expense Records -->
        <div class="bg-white rounded-2xl p-5 shadow-xs border border-slate-200 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h2 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                    <span>💸</span> Recent 5 Factory Expenses
                </h2>
                <a href="{{ route('expenses') }}" class="text-xs font-bold text-rose-600 hover:underline">View Expenses →</a>
            </div>
            <div class="space-y-2">
                @forelse($latestExpenses as $exp)
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 flex items-center justify-between hover:bg-white transition">
                        <div>
                            <div class="text-xs font-black text-slate-800 capitalize">
                                {{ $exp->expense_category === 'gst_payment' ? 'GST Payment / Tax' : str_replace('_', ' ', $exp->expense_category) }}
                            </div>
                            <div class="text-[11px] font-medium text-slate-500 truncate max-w-[200px]">
                                {{ $exp->expense_date ? $exp->expense_date->format('d M Y') : 'N/A' }}
                                @if($exp->description)
                                    • {{ $exp->description }}
                                @endif
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-xs font-black text-rose-600">₹{{ format_indian($exp->amount, 2) }}</div>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 text-center py-4">No recent expense records logged.</p>
                @endforelse
            </div>
        </div>

        <!-- 3. Low Stock Inventory Alerts -->
        <div class="bg-white rounded-2xl p-5 shadow-xs border border-slate-200 space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h2 class="text-sm font-bold text-slate-800 flex items-center gap-2 text-rose-700">
                    <span>⚠️</span> Low Stock Alerts
                </h2>
                <a href="{{ route('inventory') }}" class="text-xs font-bold text-rose-600 hover:underline">Inventory →</a>
            </div>
            <div class="space-y-2">
                @forelse($lowStockMaterials as $mat)
                    <div class="p-3 bg-rose-50/50 rounded-xl border border-rose-100 flex items-center justify-between">
                        <div>
                            <div class="text-xs font-bold text-slate-800">{{ $mat->material_name }}</div>
                            <div class="text-[11px] font-medium text-slate-500">Avg Rate: ₹{{ format_indian($mat->average_purchase_price, 2) }} / {{ $mat->unit }}</div>
                        </div>
                        <div class="text-right">
                            <div class="text-xs font-black text-rose-700">{{ format_indian($mat->current_stock, 2) }} {{ $mat->unit }}</div>
                            <div class="text-[10px] font-bold text-slate-400">Min: {{ format_indian($mat->safety_threshold, 2) }} {{ $mat->unit }}</div>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 text-center py-4">All raw material inventory levels are healthy.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Load Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Smooth Gradient Dual Line & Area Chart (6-Month Financial Performance)
    const trendCtx = document.getElementById('financialTrendChart').getContext('2d');
    
    // Create Emerald Sales Gradient
    const salesGrad = trendCtx.createLinearGradient(0, 0, 0, 260);
    salesGrad.addColorStop(0, 'rgba(16, 185, 129, 0.28)');
    salesGrad.addColorStop(1, 'rgba(16, 185, 129, 0.0)');

    // Create Indigo Outflows Gradient
    const expGrad = trendCtx.createLinearGradient(0, 0, 0, 260);
    expGrad.addColorStop(0, 'rgba(99, 102, 241, 0.20)');
    expGrad.addColorStop(1, 'rgba(99, 102, 241, 0.0)');

    new Chart(trendCtx, {
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

    const clientCtx = document.getElementById('topClientsChart').getContext('2d');
    new Chart(clientCtx, {
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
});

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
        success: async function(res) {
            closeDashboardPayModal();
            if (window.showToast) window.showToast('success', res.message || 'Payment recorded successfully!');
            if (window.loadPage) {
                await window.loadPage(window.location.href);
            } else {
                window.location.reload();
            }
        },
        error: function(xhr) {
            alert(xhr.responseJSON?.message || xhr.responseJSON?.errors?.[0] || 'Failed to record payment.');
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
@endsection
