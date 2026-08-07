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

            <!-- Capsule Period Bar -->
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
                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Financial Year (April - March)</label>
                    <select name="filter_year" id="filterYearSelect" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-medium">
                        @php
                            $currentYr = date('Y');
                            $startYr = 2023;
                        @endphp
                        @for($y = $currentYr; $y >= $startYr; $y--)
                            <option value="{{ $y }}" {{ (string)$filterYear === (string)$y ? 'selected' : '' }}>
                                FY {{ $y }}-{{ substr($y+1, 2, 2) }}
                            </option>
                        @endfor
                    </select>
                </div>

                <!-- Custom Range Container -->
                <div id="customRangeContainer" class="col-span-2 grid grid-cols-2 gap-3 {{ $period === 'custom' ? '' : 'hidden' }}">
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

                <div class="flex items-end space-x-2">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-5 rounded-xl text-xs transition duration-150 shadow-xs flex items-center justify-center space-x-1 h-[42px] cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                        <span>Apply Filter</span>
                    </button>
                    <a href="{{ route('reports') }}?report_type={{ $reportType }}" class="bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold py-2 px-4 rounded-xl text-xs transition duration-150 flex items-center justify-center h-[42px]">
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Active Report Partials -->
    @if ($reportType === 'invoice')
        @include('pages.reports.partials.sales')
    @elseif ($reportType === 'purchase')
        @include('pages.reports.partials.purchases')
    @elseif ($reportType === 'financial')
        @include('pages.reports.partials.financial')
    @elseif ($reportType === 'expense')
        @include('pages.reports.partials.expenses')
    @elseif ($reportType === 'gst')
        @include('pages.reports.partials.gst')
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

    $(document).off('submit', '#reportFilterForm').on('submit', '#reportFilterForm', function() {
        const period = document.getElementById('filterPeriodInput').value;
        const monthInput = document.getElementById('filterMonthInput');
        const yearSelect = document.getElementById('filterYearSelect');
        const startDateInput = document.querySelector('input[name="start_date"]');
        const endDateInput = document.querySelector('input[name="end_date"]');

        if (monthInput) monthInput.disabled = false;
        if (yearSelect) yearSelect.disabled = false;
        if (startDateInput) startDateInput.disabled = false;
        if (endDateInput) endDateInput.disabled = false;

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
