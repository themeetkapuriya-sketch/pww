@extends('layouts.app')

@section('title', 'Client Account Ledger - ' . $client->company_name)

@section('content')
<div class="space-y-6">

    <!-- Header & Navigation -->
    <div class="flex items-center justify-between pb-4 border-b border-slate-200">
        <div class="flex items-center space-x-3">
            <a href="{{ route('clients') }}" class="p-2 bg-white rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Client Account Ledger</h1>
                <p class="text-xs text-slate-500 font-medium">Running balance statement and payment audit for <span class="text-blue-600 font-bold">{{ $client->company_name }}</span></p>
            </div>
        </div>

        <!-- Download PDF Action -->
        <a href="{{ route('clients.ledger.pdf', ['id' => $client->id, 'start_date' => $start_date, 'end_date' => $end_date, 'filter_period' => $period, 'plant_id' => $plant_id]) }}" 
           class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold py-2.5 px-4 rounded-xl shadow-md transition duration-150 flex items-center space-x-2 no-ajax">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 01-2-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            <span>Download Statement PDF</span>
        </a>
    </div>

    <!-- Filter Form Capsule Bar -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 space-y-4">
        <!-- Plant Selector Bar (For Multi-Plant Clients) -->
        @if($client->plants->count() > 0)
            <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200 flex flex-col md:flex-row md:items-center justify-between gap-3">
                <div class="flex items-center space-x-2">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    <span class="text-xs font-bold text-slate-700 uppercase tracking-tight">Factory Location Filter:</span>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('clients.ledger', array_merge(request()->except('plant_id'), ['id' => $client->id])) }}" 
                       class="px-3 py-1.5 rounded-lg text-xs font-bold transition duration-150 {{ empty($plant_id) ? 'bg-blue-600 text-white shadow-2xs' : 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-100' }}">
                        🏢 All Plants (Corporate Level)
                    </a>
                    @foreach($client->plants as $plant)
                        <a href="{{ route('clients.ledger', array_merge(request()->except('plant_id'), ['id' => $client->id, 'plant_id' => $plant->id])) }}" 
                           class="px-3 py-1.5 rounded-lg text-xs font-bold transition duration-150 {{ (int)$plant_id === $plant->id ? 'bg-blue-600 text-white shadow-2xs' : 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-100' }}">
                            🏭 {{ $plant->plant_name }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <form method="GET" action="{{ route('clients.ledger', $client->id) }}" class="space-y-4" id="ledgerFilterForm">
            @if(!empty($plant_id))
                <input type="hidden" name="plant_id" value="{{ $plant_id }}">
            @endif
            <input type="hidden" name="filter_period" id="filterPeriodInput" value="{{ $period }}">

            <!-- Capsule Period Bar (Matches Reports UI style) -->
            <div class="flex flex-wrap items-center gap-2" id="capsuleBar">
                <span class="text-xs font-black uppercase text-slate-400 tracking-wider flex items-center mr-2">
                    <svg class="w-4 h-4 mr-1 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                    Ledger Period:
                </span>
                
                <button type="button" onclick="setLedgerPeriod('all')" 
                        class="px-4 py-1.5 rounded-full text-xs font-bold border transition duration-150 {{ $period === 'all' ? 'bg-blue-600 border-blue-600 text-white shadow-sm' : 'border-blue-600/30 text-blue-700 hover:bg-blue-50 bg-white' }}">
                    All Records
                </button>
                <button type="button" onclick="setLedgerPeriod('month')" 
                        class="px-4 py-1.5 rounded-full text-xs font-bold border transition duration-150 {{ $period === 'month' ? 'bg-blue-600 border-blue-600 text-white shadow-sm' : 'border-blue-600/30 text-blue-700 hover:bg-blue-50 bg-white' }}">
                    Month
                </button>
                <button type="button" onclick="setLedgerPeriod('year')" 
                        class="px-4 py-1.5 rounded-full text-xs font-bold border transition duration-150 {{ $period === 'year' ? 'bg-blue-600 border-blue-600 text-white shadow-sm' : 'border-blue-600/30 text-blue-700 hover:bg-blue-50 bg-white' }}">
                    Year
                </button>
                <button type="button" onclick="setLedgerPeriod('custom')" 
                        class="px-4 py-1.5 rounded-full text-xs font-bold border transition duration-150 {{ $period === 'custom' ? 'bg-blue-600 border-blue-600 text-white shadow-sm' : 'border-blue-600/30 text-blue-700 hover:bg-blue-50 bg-white' }}">
                    Custom Range
                </button>
            </div>

            <!-- Dynamic Input Fields (Revealed conditionally like Reports) -->
            <div id="dynamicFilterFields" class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-3 border-t border-slate-100 {{ $period === 'all' ? 'hidden' : '' }}">
                <!-- Month Selection Container -->
                <div id="monthFilterContainer" class="{{ $period === 'month' ? '' : 'hidden' }}">
                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Select Month</label>
                    <input type="month" name="filter_month" id="filterMonthInput" value="{{ $filterMonth ?? date('Y-m') }}"
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
                            <option value="{{ $y }}" {{ ($filterYear ?? date('Y')) == $y ? 'selected' : '' }}>FY {{ $y }}-{{ $nextYearShort }}</option>
                        @endfor
                    </select>
                </div>

                <!-- Custom Date Range Containers -->
                <div id="customRangeContainer" class="col-span-1 md:col-span-2 grid grid-cols-2 gap-4 {{ $period === 'custom' ? '' : 'hidden' }}">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Start Date</label>
                        <input type="date" name="start_date" value="{{ $start_date }}"
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-medium">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">End Date</label>
                        <input type="date" name="end_date" value="{{ $end_date }}"
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

    <!-- Client Account Balance Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Opening Balance</span>
            <span class="text-lg font-black text-slate-700 block mt-1">₹{{ number_format($opening_balance, 2) }}</span>
            <span class="text-[10px] text-slate-400">Prior to {{ \Carbon\Carbon::parse($start_date)->format('d/m/Y') }}</span>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Total Billed Invoices (+)</span>
            <span class="text-lg font-black text-blue-600 block mt-1">₹{{ number_format($total_debit, 2) }}</span>
            <span class="text-[10px] text-slate-400">Debited during period</span>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Total Payments Received (-)</span>
            <span class="text-lg font-black text-emerald-600 block mt-1">₹{{ number_format($total_credit, 2) }}</span>
            <span class="text-[10px] text-slate-400">Credited during period</span>
        </div>
        <div class="bg-amber-500 text-white rounded-2xl p-5 shadow-md">
            <span class="text-[10px] font-bold uppercase tracking-wider opacity-90 block">Net Outstanding Balance</span>
            <span class="text-xl font-extrabold block mt-1">₹{{ number_format($closing_balance, 2) }}</span>
            <span class="text-[10px] opacity-80">Current amount due from client</span>
        </div>
    </div>

    <!-- Ledger Table -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs">
        <h3 class="text-base font-bold text-slate-800 mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            Chronological Account Ledger Entries
        </h3>

        <div class="overflow-x-auto border border-slate-200 rounded-xl">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-4 py-3 text-center font-bold text-xs">Date</th>
                        <th class="px-4 py-3 text-left font-bold text-xs">Reference #</th>
                        <th class="px-4 py-3 text-left font-bold text-xs">Description</th>
                        <th class="px-4 py-3 text-right font-bold text-xs">Billed (+)</th>
                        <th class="px-4 py-3 text-right font-bold text-xs">Received (-)</th>
                        <th class="px-4 py-3 text-right font-bold text-xs">Running Balance</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white text-xs">
                    <tr class="bg-slate-50 font-bold">
                        <td colspan="3" class="px-4 py-3 text-slate-700">Opening Balance (Before {{ \Carbon\Carbon::parse($start_date)->format('d/m/Y') }})</td>
                        <td class="px-4 py-3 text-right text-slate-400">-</td>
                        <td class="px-4 py-3 text-right text-slate-400">-</td>
                        <td class="px-4 py-3 text-right font-mono text-slate-900 font-extrabold">₹{{ number_format($opening_balance, 2) }}</td>
                    </tr>
                    @forelse($entries as $row)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-4 py-3 text-center font-medium text-slate-500">
                                {{ \Carbon\Carbon::parse($row['date'])->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-3 font-mono font-bold text-blue-600">
                                @if($row['type'] === 'invoice')
                                    <a href="{{ route('invoice.preview', $row['model']->id) }}" class="hover:underline">{{ $row['reference'] }}</a>
                                @else
                                    {{ $row['reference'] }}
                                @endif
                            </td>
                            <td class="px-4 py-3 font-medium text-slate-800">
                                {{ $row['description'] }}
                            </td>
                            <td class="px-4 py-3 text-right font-bold {{ $row['debit'] > 0 ? 'text-blue-600' : 'text-slate-400' }}">
                                {{ $row['debit'] > 0 ? '₹' . number_format($row['debit'], 2) : '-' }}
                            </td>
                            <td class="px-4 py-3 text-right font-bold {{ $row['credit'] > 0 ? 'text-emerald-600' : 'text-slate-400' }}">
                                {{ $row['credit'] > 0 ? '₹' . number_format($row['credit'], 2) : '-' }}
                            </td>
                            <td class="px-4 py-3 text-right font-mono font-extrabold text-slate-900">
                                ₹{{ number_format($row['running_balance'], 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-slate-400 font-semibold italic">
                                No Records Available
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
function setLedgerPeriod(period) {
    const input = document.getElementById('filterPeriodInput');
    if (input) input.value = period;

    const dynamicFields = document.getElementById('dynamicFilterFields');
    const monthContainer = document.getElementById('monthFilterContainer');
    const yearContainer = document.getElementById('yearFilterContainer');
    const customContainer = document.getElementById('customRangeContainer');

    if (period === 'all') {
        if (dynamicFields) dynamicFields.classList.add('hidden');
        document.getElementById('ledgerFilterForm').submit();
    } else {
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
}

$(document).off('submit', '#ledgerFilterForm').on('submit', '#ledgerFilterForm', function() {
    const period = document.getElementById('filterPeriodInput').value;
    const monthInput = document.getElementById('filterMonthInput');
    const yearSelect = document.getElementById('filterYearSelect');
    const startDateInput = document.querySelector('#ledgerFilterForm input[name="start_date"]');
    const endDateInput = document.querySelector('#ledgerFilterForm input[name="end_date"]');

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
