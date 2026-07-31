@extends('layouts.app')

@section('title', 'Employees & Payroll Management')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between pb-4 border-b border-slate-200 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Employees Directory & Payroll Hub</h1>
            <p class="text-sm text-slate-500">Manage employee profiles, daily attendance sheets, monthly salary calculations, and payment disbursals.</p>
        </div>

        <!-- Top Navigation Sub-Tabs -->
        <div class="inline-flex p-1 bg-slate-100/80 rounded-2xl border border-slate-200/80 self-start md:self-auto">
            <button type="button" onclick="switchEmpTab('directory')" id="tabBtn-directory" class="emp-tab-btn px-4 py-2 rounded-xl text-xs font-bold transition duration-150 active-emp-tab">
                👥 Employees Catalog
            </button>
            <button type="button" onclick="switchEmpTab('attendance')" id="tabBtn-attendance" class="emp-tab-btn px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:text-slate-900 transition duration-150">
                📅 Daily Attendance
            </button>
            <button type="button" onclick="switchEmpTab('disbursal')" id="tabBtn-disbursal" class="emp-tab-btn px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:text-slate-900 transition duration-150">
                💳 Monthly Salary Ledger
            </button>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 1: EMPLOYEES CATALOG DIRECTORY -->
    <!-- ========================================================================= -->
    <div id="empTab-directory" class="emp-tab-content space-y-6">
        <div class="flex justify-end">
            <button type="button" 
                    onclick="toggleInlineForm('employeeFormContainer', this)" 
                    class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold py-2.5 px-4 rounded-xl shadow-xs transition duration-150 flex items-center space-x-2 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span>Add New Employee</span>
            </button>
        </div>

        <!-- INSERT FORM AT THE TOP (Expandable) -->
        <div id="employeeFormContainer" class="hidden transition-all duration-300 ease-in-out">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Add New Employee Profile
                    </h3>
                    <button type="button" onclick="toggleInlineForm('employeeFormContainer', document.querySelector('button[onclick*=\'employeeFormContainer\']'))" class="text-xs font-bold text-slate-400 hover:text-slate-600 transition cursor-pointer">&times; Close</button>
                </div>
                <form action="{{ route('employees.store') }}" method="POST" class="ajax-form space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Full Name</label>
                            <input type="text" name="full_name" placeholder="e.g. Ramesh Patel" required
                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-800 font-medium">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Wage Configuration Type</label>
                            <select name="wage_type" id="wageTypeSelect" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-800 font-semibold" required>
                                <option value="per-day">Per Day Wage (Daily Payout Rate)</option>
                                <option value="fixed">Fixed Salary (Monthly regular payout)</option>
                            </select>
                        </div>
                    </div>

                    <div id="rateFieldContainer" class="space-y-1">
                        <label id="rateFieldLabel" class="block text-xs font-bold text-slate-600 uppercase mb-1">Per Day Wage Rate (₹ / day)</label>
                        <input type="number" id="rateInput" name="piece_rate_per_unit" step="0.01" min="0" placeholder="e.g. 500.00"
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-800 font-medium" required>
                    </div>

                    <div id="fixedSalaryField" class="hidden space-y-1">
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Monthly Basic Fixed Salary (₹ / month)</label>
                        <input type="number" id="fixedInput" name="monthly_salary" step="0.01" min="0" placeholder="e.g. 20000.00"
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-800 font-medium">
                    </div>

                    <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-100">
                        <button type="button" onclick="toggleInlineForm('employeeFormContainer', document.querySelector('button[onclick*=\'employeeFormContainer\']'))" class="bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold py-2.5 px-5 rounded-xl transition cursor-pointer">Cancel</button>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold py-2.5 px-6 rounded-xl shadow-xs transition">
                            Register Employee Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- EDIT FORM CONTAINER (Hidden by default) -->
        <div id="editEmployeeFormCard" class="hidden transition-all duration-300 ease-in-out">
            <div class="bg-amber-50/40 rounded-2xl shadow-sm border-2 border-amber-300/80 p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-amber-200/60 pb-3">
                    <h3 class="text-base font-bold text-amber-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        Edit Employee Profile Details
                    </h3>
                    <button type="button" onclick="closeEditEmployeeForm()" class="text-xs font-bold text-slate-400 hover:text-slate-600 transition cursor-pointer">&times; Close</button>
                </div>
                <form id="editEmployeeForm" method="POST" class="ajax-form space-y-4">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Full Name</label>
                            <input type="text" name="full_name" id="edit_full_name" required
                                   class="w-full bg-white border border-amber-200 rounded-xl py-2.5 px-3.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 text-slate-800 font-medium">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Wage Configuration Type</label>
                            <select name="wage_type" id="edit_wage_type" class="w-full bg-white border border-amber-200 rounded-xl py-2.5 px-3.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 text-slate-800 font-semibold" required>
                                <option value="per-day">Per Day Wage (Daily Payout Rate)</option>
                                <option value="fixed">Fixed Salary (Monthly regular payout)</option>
                            </select>
                        </div>
                    </div>

                    <div id="editRateFieldContainer" class="space-y-1">
                        <label id="editRateFieldLabel" class="block text-xs font-bold text-slate-600 uppercase mb-1">Per Day Wage Rate (₹ / day)</label>
                        <input type="number" id="edit_rateInput" name="piece_rate_per_unit" step="0.01" min="0" placeholder="e.g. 500.00"
                               class="w-full bg-white border border-amber-200 rounded-xl py-2.5 px-3.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 text-slate-800 font-medium">
                    </div>

                    <div id="editFixedSalaryField" class="hidden space-y-1">
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Monthly Basic Fixed Salary (₹ / month)</label>
                        <input type="number" id="edit_fixedInput" name="monthly_salary" step="0.01" min="0" placeholder="e.g. 20000.00"
                               class="w-full bg-white border border-amber-200 rounded-xl py-2.5 px-3.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 text-slate-800 font-medium">
                    </div>

                    <div class="flex items-center justify-end space-x-3 pt-3 border-t border-amber-200/60">
                        <button type="button" onclick="closeEditEmployeeForm()" class="bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold py-2.5 px-5 rounded-xl transition cursor-pointer">Cancel</button>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold py-2.5 px-6 rounded-xl shadow-xs transition">
                            Update Employee Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- RECORDS LIST TABLE -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
            <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                Employees Directory Ledger
            </h3>
            
            <div class="overflow-x-auto w-full max-w-full">
                <table class="erp-datatable min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-[#EDF4FA] text-black divide-x divide-slate-200">
                        <tr>
                            <th class="px-4 py-3.5 text-center text-xs font-bold uppercase w-12">#</th>
                            <th class="px-6 py-3.5 text-left text-xs font-bold uppercase">Employee Name</th>
                            <th class="px-6 py-3.5 text-left text-xs font-bold uppercase">Wage Type</th>
                            <th class="px-6 py-3.5 text-right text-xs font-bold uppercase">Salary Rate Details</th>
                            <th class="px-6 py-3.5 text-center text-xs font-bold uppercase w-28">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach ($staffProfiles as $staff)
                            <tr class="hover:bg-slate-50 transition" id="row-emp-{{ $staff->id }}">
                                <td class="px-4 py-4 text-center font-bold text-slate-500">{{ $loop->iteration }}</td>
                                <td class="px-6 py-4 font-bold text-slate-800">{{ $staff->full_name }}</td>
                                <td class="px-6 py-4 text-slate-600 font-medium">
                                    <span class="px-2.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider
                                        {{ $staff->wage_type === 'per-day' ? 'bg-emerald-50 border border-emerald-200 text-emerald-700' : 'bg-slate-100 border border-slate-200 text-slate-700' }}">
                                        {{ $staff->wage_type === 'per-day' ? 'Per Day' : 'Fixed Salary' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right font-mono font-bold text-slate-800">
                                    @if ($staff->wage_type === 'per-day')
                                        ₹{{ number_format($staff->piece_rate_per_unit, 2) }} <span class="text-xs text-slate-500 font-sans font-normal">/ day</span>
                                    @else
                                        ₹{{ number_format($staff->monthly_salary, 2) }} <span class="text-xs text-slate-500 font-sans font-normal">/ month basic</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button type="button" 
                                                onclick='openEditEmployeeForm(@json($staff))'
                                                class="w-7 h-7 p-1 inline-flex items-center justify-center rounded-lg bg-amber-500 hover:bg-amber-600 text-white shadow-2xs transition duration-150 transform hover:scale-105"
                                                title="Edit Employee">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </button>
                                        <button type="button" 
                                                onclick="deleteEmployeeAjax('{{ $staff->id }}', '{{ addslashes($staff->full_name) }}', this)" 
                                                class="w-7 h-7 p-1 inline-flex items-center justify-center rounded-lg bg-rose-500 hover:bg-rose-600 text-white shadow-2xs transition duration-150 transform hover:scale-105"
                                                title="Delete Employee">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 2: DAILY ATTENDANCE SHEET (METHOD 1) -->
    <!-- ========================================================================= -->
    <div id="empTab-attendance" class="emp-tab-content hidden space-y-6">
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 pb-4">
                <div>
                    <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        Method 1: Daily Attendance Entry Sheet
                    </h3>
                    <p class="text-xs text-slate-500 font-medium">Select a date to mark attendance. Present counts as 1.0 day, Half-Day counts as 0.5 day.</p>
                </div>

                <div class="flex items-center gap-3">
                    <label class="text-xs font-bold text-slate-600 uppercase">Attendance Date:</label>
                    <input type="date" id="attendanceDateInput" value="{{ $selectedDate }}" onchange="loadAttendanceForDate(this.value)" class="bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <form action="{{ route('employees.attendance.store') }}" method="POST" class="ajax-form space-y-4" id="attendanceForm">
                @csrf
                <input type="hidden" name="date" id="attendanceFormDate" value="{{ $selectedDate }}">

                <div class="overflow-x-auto w-full">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-[#EDF4FA] text-black">
                            <tr>
                                <th class="px-4 py-3 text-center text-xs font-bold uppercase w-12">#</th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase">Employee Name</th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase">Wage Type</th>
                                <th class="px-6 py-3 text-center text-xs font-bold uppercase">Daily Status Toggle</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach ($staffProfiles as $staff)
                                @php
                                    $currentStatus = $attendanceForDate[$staff->id] ?? 'present';
                                @endphp
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="px-4 py-3.5 text-center font-bold text-slate-500">{{ $loop->iteration }}</td>
                                    <td class="px-6 py-3.5 font-bold text-slate-800">{{ $staff->full_name }}</td>
                                    <td class="px-6 py-3.5 font-medium text-slate-600">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $staff->wage_type === 'per-day' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-700 border border-slate-200' }}">
                                            {{ $staff->wage_type === 'per-day' ? 'Per Day (₹' . number_format($staff->piece_rate_per_unit, 0) . ')' : 'Fixed Salary' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3.5 text-center whitespace-nowrap">
                                        <div class="inline-flex p-1 bg-slate-100 rounded-xl border border-slate-200/80 space-x-1">
                                            <label class="px-3 py-1.5 rounded-lg text-xs font-bold cursor-pointer transition flex items-center gap-1.5 has-[:checked]:bg-emerald-500 has-[:checked]:text-white text-slate-600">
                                                <input type="radio" name="attendance[{{ $staff->id }}]" value="present" class="hidden" {{ $currentStatus === 'present' ? 'checked' : '' }}>
                                                🟢 Present (1.0)
                                            </label>
                                            <label class="px-3 py-1.5 rounded-lg text-xs font-bold cursor-pointer transition flex items-center gap-1.5 has-[:checked]:bg-amber-500 has-[:checked]:text-white text-slate-600">
                                                <input type="radio" name="attendance[{{ $staff->id }}]" value="half_day" class="hidden" {{ $currentStatus === 'half_day' ? 'checked' : '' }}>
                                                🟡 Half Day (0.5)
                                            </label>
                                            <label class="px-3 py-1.5 rounded-lg text-xs font-bold cursor-pointer transition flex items-center gap-1.5 has-[:checked]:bg-rose-500 has-[:checked]:text-white text-slate-600">
                                                <input type="radio" name="attendance[{{ $staff->id }}]" value="absent" class="hidden" {{ $currentStatus === 'absent' ? 'checked' : '' }}>
                                                🔴 Absent (0.0)
                                            </label>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="flex justify-end pt-3 border-t border-slate-100">
                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 px-6 rounded-xl text-xs shadow-xs transition flex items-center gap-2 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Save Daily Attendance Sheet
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 3: MONTHLY SALARY LEDGER & DISBURSAL (METHOD 1 & METHOD 2) -->
    <!-- ========================================================================= -->
    <div id="empTab-disbursal" class="emp-tab-content hidden space-y-6">
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 pb-4">
                <div>
                    <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Monthly Salary Disbursal & Status Ledger
                    </h3>
                    <p class="text-xs text-slate-500 font-medium">Track salary payment statuses (🟢 PAID / 🟡 PENDING). Paid salaries are automatically posted to Expenses Ledger.</p>
                </div>

                <div class="flex items-center gap-3">
                    <label class="text-xs font-bold text-slate-600 uppercase">Selected Month:</label>
                    <input type="month" id="disbursalMonthInput" value="{{ $selectedMonth }}" onchange="filterDisbursalMonth(this.value)" class="bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div class="overflow-x-auto w-full max-w-full">
                <table class="erp-datatable min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-[#EDF4FA] text-black divide-x divide-slate-200">
                        <tr>
                            <th class="px-4 py-3.5 text-center text-xs font-bold uppercase w-12">#</th>
                            <th class="px-6 py-3.5 text-left text-xs font-bold uppercase">Employee Name</th>
                            <th class="px-6 py-3.5 text-left text-xs font-bold uppercase">Wage Type</th>
                            <th class="px-6 py-3.5 text-right text-xs font-bold uppercase">Rate Details</th>
                            <th class="px-6 py-3.5 text-center text-xs font-bold uppercase">Present Days (Month)</th>
                            <th class="px-6 py-3.5 text-right text-xs font-bold uppercase">Calculated Salary</th>
                            <th class="px-6 py-3.5 text-center text-xs font-bold uppercase">Payment Status</th>
                            <th class="px-6 py-3.5 text-center text-xs font-bold uppercase w-32">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach ($staffProfiles as $staff)
                            @php
                                $disbursal = $salaryDisbursals->get($staff->id);
                                $mPresent = $monthlyAttendance->get($staff->id, 0);
                                $daysPresent = $disbursal ? $disbursal->days_present : $mPresent;
                                
                                if ($staff->wage_type === 'per-day') {
                                    $rate = $staff->piece_rate_per_unit;
                                    $totalSalary = $disbursal ? $disbursal->total_salary : ($daysPresent * $rate);
                                } else {
                                    $rate = $staff->monthly_salary;
                                    $totalSalary = $disbursal ? $disbursal->total_salary : $rate;
                                }

                                $isPaid = $disbursal && $disbursal->status === 'paid';
                            @endphp
                            <tr class="hover:bg-slate-50 transition" id="row-disbursal-{{ $staff->id }}">
                                <td class="px-4 py-4 text-center font-bold text-slate-500">{{ $loop->iteration }}</td>
                                <td class="px-6 py-4 font-bold text-slate-800">{{ $staff->full_name }}</td>
                                <td class="px-6 py-4 font-medium text-slate-600">
                                    <span class="px-2.5 py-0.5 rounded text-[10px] font-bold uppercase
                                        {{ $staff->wage_type === 'per-day' ? 'bg-emerald-50 border border-emerald-200 text-emerald-700' : 'bg-slate-100 border border-slate-200 text-slate-700' }}">
                                        {{ $staff->wage_type === 'per-day' ? 'Per Day' : 'Fixed Salary' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right font-mono font-semibold text-slate-700">
                                    @if ($staff->wage_type === 'per-day')
                                        ₹{{ number_format($rate, 2) }} / day
                                    @else
                                        ₹{{ number_format($rate, 2) }} / month
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center font-mono font-bold text-slate-800">
                                    <span class="px-2.5 py-1 bg-blue-50 text-blue-700 rounded-lg border border-blue-100">
                                        {{ number_format($daysPresent, 1) }} Days
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right font-mono font-black text-slate-900 text-base">
                                    ₹{{ number_format($totalSalary, 2) }}
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    @if ($isPaid)
                                        <div class="inline-flex flex-col items-center">
                                            <span class="px-3 py-1 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-full text-xs font-bold shadow-2xs">
                                                🟢 PAID
                                            </span>
                                            <span class="text-[10px] text-slate-400 font-medium mt-0.5">
                                                {{ $disbursal->payment_date ? $disbursal->payment_date->format('d M') : 'Paid' }} ({{ $disbursal->payment_method }})
                                            </span>
                                        </div>
                                    @else
                                        <span class="px-3 py-1 bg-amber-50 border border-amber-200 text-amber-700 rounded-full text-xs font-bold shadow-2xs">
                                            🟡 PENDING / UNPAID
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <button type="button" 
                                            onclick='openDisburseModal(@json($staff), "{{ $selectedMonth }}", {{ $daysPresent }}, {{ $totalSalary }}, {{ json_encode($disbursal) }})'
                                            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-1.5 px-3 rounded-xl text-xs transition shadow-xs inline-flex items-center gap-1.5 cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                        {{ $isPaid ? 'Edit Disbursal' : 'Pay Salary' }}
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- ========================================================================= -->
<!-- MODAL: DISBURSE SALARY & AUTO-LOG EXPENSE -->
<!-- ========================================================================= -->
<div id="disburseSalaryModal" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm hidden flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-3xl p-6 max-w-md w-full shadow-2xl border border-slate-100 space-y-4 max-h-[88vh] overflow-y-auto my-auto custom-scrollbar">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span id="disburseModalTitle">Process Salary Disbursal</span>
            </h3>
            <button onclick="closeDisburseModal()" class="text-slate-400 hover:text-slate-600 text-xl font-bold cursor-pointer">&times;</button>
        </div>

        <form action="{{ route('employees.salary.disburse') }}" method="POST" class="ajax-form space-y-4" id="disburseForm">
            @csrf
            <input type="hidden" name="staff_profile_id" id="disburseStaffId">
            <input type="hidden" name="month_year" id="disburseMonthYear">
            <input type="hidden" name="payment_status" id="disburseStatusSelect" value="paid">

            <div class="p-3 bg-blue-50/70 border border-blue-100 rounded-2xl flex items-center justify-between">
                <div>
                    <p id="disburseEmployeeName" class="text-sm font-bold text-slate-800">Employee Name</p>
                    <p id="disburseWageDetails" class="text-xs text-slate-500 font-medium">Rate Details</p>
                </div>
                <span id="disburseMonthBadge" class="px-2.5 py-1 bg-white text-blue-700 text-xs font-bold rounded-lg border border-blue-200 font-mono">
                    2026-07
                </span>
            </div>

            <!-- Method 2: Manual / Auto Days Present Input -->
            <div id="disburseDaysContainer" class="space-y-1">
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">
                    Present Days Worked in Month
                </label>
                <input type="number" name="days_present" id="disburseDaysInput" step="0.5" min="0" max="31" oninput="calculateModalSalary()" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Editable Total Salary Amount Input -->
            <div class="space-y-1">
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">
                    Total Salary Amount (₹) <span class="text-slate-400 font-normal lowercase">(editable for bonus/deduction)</span>
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-sm font-bold text-slate-500">₹</span>
                    <input type="number" name="total_salary" id="disburseTotalSalaryInput" step="0.01" min="0" placeholder="0.00" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 pl-8 pr-3.5 text-lg font-black text-emerald-600 font-mono focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Payment Method</label>
                    <select name="payment_method" id="disburseMethodSelect" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="Cash">Cash</option>
                        <option value="Bank Transfer">Bank Transfer</option>
                        <option value="UPI">UPI / GPay</option>
                        <option value="Cheque">Cheque</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Payment Date</label>
                    <input type="date" name="payment_date" id="disbursePaymentDate" value="{{ now()->toDateString() }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Notes / Memo (Optional)</label>
                <input type="text" name="notes" id="disburseNotes" placeholder="e.g. July salary paid via HDFC Bank" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-xs font-medium text-slate-800">
            </div>

            <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-2xl text-xs text-emerald-800 font-semibold flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>Paying salary automatically logs an expense under <strong>Employee Salary / Payroll</strong> in your Expenses Ledger.</span>
            </div>

            <div class="pt-3 border-t border-slate-100 flex justify-end gap-2">
                <button type="button" onclick="closeDisburseModal()" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition cursor-pointer">Cancel</button>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-xl text-xs shadow-xs transition cursor-pointer">Disburse & Pay Salary</button>
            </div>
        </form>
    </div>
</div>

<script>
let currentStaffRate = 0;
let currentWageType = 'per-day';

function switchEmpTab(tabName) {
    document.querySelectorAll('.emp-tab-content').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('.emp-tab-btn').forEach(el => {
        el.classList.remove('active-emp-tab', 'bg-blue-50', 'text-blue-700');
        el.classList.add('text-slate-600');
    });

    const targetTab = document.getElementById('empTab-' + tabName);
    if (targetTab) targetTab.classList.remove('hidden');

    const activeBtn = document.getElementById('tabBtn-' + tabName);
    if (activeBtn) activeBtn.classList.add('active-emp-tab', 'bg-blue-50', 'text-blue-700');
}

function loadAttendanceForDate(dateVal) {
    document.getElementById('attendanceFormDate').value = dateVal;
    if (window.loadPage) {
        window.loadPage(`/employees?date=${dateVal}&tab=attendance`);
    } else {
        window.location.href = `/employees?date=${dateVal}`;
    }
}

function filterDisbursalMonth(monthVal) {
    if (window.loadPage) {
        window.loadPage(`/employees?month=${monthVal}&tab=disbursal`);
    } else {
        window.location.href = `/employees?month=${monthVal}`;
    }
}

function openDisburseModal(staff, monthYear, daysPresent, calculatedSalary, disbursal) {
    currentStaffRate = staff.wage_type === 'per-day' ? (parseFloat(staff.piece_rate_per_unit) || 0) : (parseFloat(staff.monthly_salary) || 0);
    currentWageType = staff.wage_type;

    document.getElementById('disburseStaffId').value = staff.id;
    document.getElementById('disburseMonthYear').value = monthYear;
    document.getElementById('disburseEmployeeName').innerText = staff.full_name;
    document.getElementById('disburseMonthBadge').innerText = monthYear;
    document.getElementById('disburseWageDetails').innerText = staff.wage_type === 'per-day' 
        ? `Per Day Rate: ₹${currentStaffRate.toFixed(2)} / day` 
        : `Fixed Monthly Basic: ₹${currentStaffRate.toFixed(2)} / month`;

    const daysContainer = document.getElementById('disburseDaysContainer');
    const daysInput = document.getElementById('disburseDaysInput');

    if (daysContainer) daysContainer.classList.remove('hidden');
    if (daysInput) daysInput.value = daysPresent || 0;

    if (disbursal) {
        document.getElementById('disburseMethodSelect').value = disbursal.payment_method || 'Cash';
        document.getElementById('disburseNotes').value = disbursal.notes || '';
    } else {
        document.getElementById('disburseMethodSelect').value = 'Cash';
        document.getElementById('disburseNotes').value = '';
    }

    calculateModalSalary();

    if (disbursal && disbursal.total_salary) {
        document.getElementById('disburseTotalSalaryInput').value = parseFloat(disbursal.total_salary).toFixed(2);
    }

    document.getElementById('disburseSalaryModal').classList.remove('hidden');
}

function calculateModalSalary() {
    let total = 0;
    if (currentWageType === 'per-day') {
        const days = parseFloat(document.getElementById('disburseDaysInput').value) || 0;
        total = days * currentStaffRate;
    } else {
        total = currentStaffRate;
    }
    const salaryInput = document.getElementById('disburseTotalSalaryInput');
    if (salaryInput) {
        salaryInput.value = total.toFixed(2);
    }
}

function closeDisburseModal() {
    document.getElementById('disburseSalaryModal').classList.add('hidden');
}

function toggleInlineForm(containerId, btnEl) {
    const container = document.getElementById(containerId);
    if (!container) return;
    container.classList.toggle('hidden');
}

function openEditEmployeeForm(staff) {
    document.getElementById('edit_full_name').value = staff.full_name;
    document.getElementById('edit_wage_type').value = staff.wage_type;
    
    if (staff.wage_type === 'fixed') {
        document.getElementById('editFixedSalaryField').classList.remove('hidden');
        document.getElementById('editRateFieldContainer').classList.add('hidden');
        document.getElementById('edit_fixedInput').value = staff.monthly_salary;
        document.getElementById('edit_rateInput').value = '';
    } else {
        document.getElementById('editFixedSalaryField').classList.add('hidden');
        document.getElementById('editRateFieldContainer').classList.remove('hidden');
        document.getElementById('edit_rateInput').value = staff.piece_rate_per_unit;
        document.getElementById('edit_fixedInput').value = '';
    }

    const editForm = document.getElementById('editEmployeeForm');
    editForm.action = `/employees/${staff.id}`;
    
    const card = document.getElementById('editEmployeeFormCard');
    card.classList.remove('hidden');
    card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function closeEditEmployeeForm() {
    document.getElementById('editEmployeeFormCard').classList.add('hidden');
}

function deleteEmployeeAjax(empId, empName, btnEl) {
    if (!window.Swal) {
        if (!confirm(`Are you sure you want to delete employee '${empName}'?`)) return;
    }
    
    Swal.fire({
        title: 'Delete Employee Profile?',
        text: `Are you sure you want to delete '${empName}'? This action cannot be undone.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#EF4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, Delete Employee',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/employees/${empId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const tr = document.getElementById(`row-emp-${empId}`);
                    if (tr) tr.remove();
                    if (window.showToast) window.showToast('danger', data.message);
                }
            });
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const activeTab = urlParams.get('tab') || 'directory';
    switchEmpTab(activeTab);

    const wageSelect = document.getElementById('wageTypeSelect');
    if (wageSelect) {
        wageSelect.addEventListener('change', function() {
            if (this.value === 'fixed') {
                document.getElementById('fixedSalaryField').classList.remove('hidden');
                document.getElementById('rateFieldContainer').classList.add('hidden');
                document.getElementById('fixedInput').required = true;
                document.getElementById('rateInput').required = false;
            } else {
                document.getElementById('fixedSalaryField').classList.add('hidden');
                document.getElementById('rateFieldContainer').classList.remove('hidden');
                document.getElementById('fixedInput').required = false;
                document.getElementById('rateInput').required = true;
            }
        });
    }

    const editWageSelect = document.getElementById('edit_wage_type');
    if (editWageSelect) {
        editWageSelect.addEventListener('change', function() {
            if (this.value === 'fixed') {
                document.getElementById('editFixedSalaryField').classList.remove('hidden');
                document.getElementById('editRateFieldContainer').classList.add('hidden');
            } else {
                document.getElementById('editFixedSalaryField').classList.add('hidden');
                document.getElementById('editRateFieldContainer').classList.remove('hidden');
            }
        });
    }
});
</script>

<style>
.active-emp-tab {
    background-color: #ffffff !important;
    color: #1d4ed8 !important;
    box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1);
}
</style>
@endsection
