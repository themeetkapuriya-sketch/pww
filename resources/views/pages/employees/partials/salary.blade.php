<!-- TAB 3: MONTHLY SALARY LEDGER & DISBURSAL -->
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
                                        data-staff="{{ json_encode(new \App\Http\Resources\StaffProfileResource($staff)) }}"
                                        data-disbursal="{{ json_encode($disbursal) }}"
                                        onclick='openDisburseModal(this, "{{ $selectedMonth }}", {{ $daysPresent }}, {{ $totalSalary }})'
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
