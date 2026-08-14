<!-- TAB 3: MONTHLY SALARY LEDGER & PAYMENT -->
<div id="empTab-payment" class="emp-tab-content {{ ($activeTab ?? request('tab', 'directory')) === 'payment' ? '' : 'hidden' }} space-y-6">
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 pb-4">
            <div>
                <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Monthly Salary Payment & Status Ledger
                </h3>
                <p class="text-xs text-slate-500 font-medium">Track salary payment statuses (🟢 PAID / 🟡 PENDING). Paid salaries are automatically posted to Expenses Ledger.</p>
            </div>

            <div class="flex items-center gap-3">
                <button type="button" onclick="openAdvanceModal()" class="bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold py-2 px-3.5 rounded-xl shadow-xs transition flex items-center gap-1.5 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    <span>Give Salary Advance</span>
                </button>
                <label class="text-xs font-bold text-slate-600 uppercase">Selected Month:</label>
                <input type="month" id="paymentMonthInput" value="{{ $selectedMonth }}" onchange="filterPaymentMonth(this.value)" class="bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <div class="overflow-x-auto w-full max-w-full">
            <table class="erp-datatable min-w-full divide-y divide-slate-200 text-sm" id="salaryLedgerTable">
                <thead class="bg-[#EDF4FA] text-black divide-x divide-slate-200">
                    <tr>
                        <th class="px-4 py-3.5 text-center text-xs font-bold uppercase w-12">#</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold uppercase">Employee Name</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold uppercase">Wage Type</th>
                        <th class="px-6 py-3.5 text-right text-xs font-bold uppercase">Rate Details</th>
                        <th class="px-6 py-3.5 text-center text-xs font-bold uppercase">Present Days (Month)</th>
                        <th class="px-6 py-3.5 text-right text-xs font-bold uppercase">Calculated Salary</th>
                        <th class="px-6 py-3.5 text-center text-xs font-bold uppercase">Payment Status</th>
                        <th class="px-6 py-3.5 text-center text-xs font-bold uppercase">Payment Date</th>
                        <th class="px-6 py-3.5 text-center text-xs font-bold uppercase w-32">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($salaryStaffProfiles as $staff)
                        @php
                            $payment = $salaryPayments->get($staff->id);
                            $mPresent = $monthlyAttendance->get($staff->id, 0);
                            $daysPresent = $payment ? $payment->days_present : $mPresent;
                            $pAdvance = isset($pendingAdvances) ? ($pendingAdvances[$staff->id] ?? 0) : 0;
                            
                            if ($staff->wage_type === 'per-day') {
                                $rate = $staff->piece_rate_per_unit;
                                $totalSalary = $payment ? $payment->total_salary : max(0, ($daysPresent * $rate) - $pAdvance);
                            } else {
                                $rate = $staff->monthly_salary;
                                $totalSalary = $payment ? $payment->total_salary : max(0, $rate - $pAdvance);
                            }

                            $isPaid = $payment && $payment->status === 'paid';
                        @endphp
                        <tr class="hover:bg-slate-50 transition" id="row-payment-{{ $staff->id }}">
                            <td class="px-4 py-4 text-center font-bold text-slate-500">{{ $loop->iteration }}</td>
                            <td class="px-6 py-4 font-bold text-slate-800">
                                <div class="flex items-center gap-2">
                                    <span>{{ $staff->full_name }}</span>
                                    @if(! $staff->is_active)
                                        <span class="px-1.5 py-0.5 bg-rose-50 text-rose-700 border border-rose-200 rounded text-[9px] font-bold uppercase" title="Employee is inactive (Showing because current month salary is unpaid)">
                                            Inactive
                                        </span>
                                    @endif
                                </div>
                                @if($pAdvance > 0)
                                    <span class="px-2 py-0.5 bg-amber-50 text-amber-700 border border-amber-200 rounded text-[10px] font-bold block mt-1 w-fit" title="Pending advance to deduct">
                                        Advance: ₹{{ number_format($pAdvance, 2) }}
                                    </span>
                                @endif
                            </td>
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
                                @if($payment && $payment->advance_deduction > 0)
                                    <span class="block text-[10px] text-amber-700 font-normal">(Deducted: ₹{{ number_format($payment->advance_deduction, 2) }})</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                @if ($isPaid)
                                    <span class="px-3 py-1 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-full text-xs font-bold shadow-2xs">
                                        🟢 PAID
                                    </span>
                                @else
                                    <span class="px-3 py-1 bg-amber-50 border border-amber-200 text-amber-700 rounded-full text-xs font-bold shadow-2xs">
                                        🟡 PENDING / UNPAID
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                @if ($isPaid && $payment && $payment->payment_date)
                                    <span class="font-bold text-slate-800 text-xs block">{{ $payment->payment_date->format('d M Y') }}</span>
                                    <span class="text-[10px] text-slate-500 font-medium">via {{ $payment->payment_method ?? 'Cash' }}</span>
                                @elseif ($isPaid)
                                    <span class="text-xs font-semibold text-slate-700">Paid</span>
                                @else
                                    <span class="text-xs text-slate-400 font-medium italic">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button" 
                                            onclick="openEmployeeStatementModal('{{ $staff->id }}')" 
                                            class="w-7 h-7 p-1 inline-flex items-center justify-center rounded-lg bg-blue-600 hover:bg-blue-700 text-white shadow-2xs transition duration-150 transform hover:scale-105 cursor-pointer" 
                                            title="View Employee Financial Passbook & Statement">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                    </button>
                                    <button type="button" 
                                            data-staff="{{ json_encode(new \App\Http\Resources\StaffProfileResource($staff)) }}"
                                            data-payment="{{ json_encode($payment) }}"
                                            data-pending-advance="{{ $pAdvance }}"
                                            data-missing-dates="{{ json_encode($missingAttendanceDates->get($staff->id, [])) }}"
                                            onclick='openPaymentModal(this, "{{ $selectedMonth }}", {{ $daysPresent }}, {{ $totalSalary }})'
                                            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-1.5 px-3 rounded-xl text-xs transition shadow-xs inline-flex items-center gap-1.5 cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                        {{ $isPaid ? 'Edit Payment' : 'Pay Salary' }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center space-y-2">
                                    <svg class="w-10 h-10 mx-auto text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                    </svg>
                                    <p class="text-sm font-bold text-slate-600">No Records Found</p>
                                    <p class="text-xs text-slate-400">There are no active employee salary records for this month.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
