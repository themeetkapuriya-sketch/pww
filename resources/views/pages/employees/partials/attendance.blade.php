<!-- TAB 2: DAILY ATTENDANCE SHEET -->
<div id="empTab-attendance" class="emp-tab-content {{ ($activeTab ?? request('tab', 'directory')) === 'attendance' ? '' : 'hidden' }} space-y-6">
    <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200/80 dark:border-slate-800 shadow-sm space-y-4">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 dark:border-slate-800 pb-4">
            <div>
                <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    Daily Attendance Entry Sheet
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Select a date to mark attendance. Present counts as 1.0 day, Half-Day counts as 0.5 day.</p>
            </div>

            <div class="flex items-center gap-3">
                <label class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase">Attendance Date:</label>
                <input type="date" id="attendanceDateInput" value="{{ $selectedDate }}" onchange="loadAttendanceForDate(this.value)" class="bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl py-2 px-3 text-xs font-bold text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <form action="{{ route('employees.attendance.store') }}" method="POST" class="ajax-form space-y-4" id="attendanceForm" novalidate>
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
                        @forelse ($staffProfiles as $staff)
                            @php
                                $currentStatus = $attendanceForDate[$staff->id] ?? 'present';
                            @endphp
                            <tr class="hover:bg-slate-50 transition {{ $staff->is_active ? '' : 'hidden' }}" id="att-row-emp-{{ $staff->id }}">
                                <td class="px-4 py-3.5 text-center font-bold text-slate-500">{{ $loop->iteration }}</td>
                                 <td class="px-6 py-3.5 font-bold text-slate-800 dark:text-slate-100">{{ $staff->full_name }}</td>
                                 <td class="px-6 py-3.5 font-medium text-slate-600 dark:text-slate-400">
                                     <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $staff->wage_type === 'per-day' ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700' }}">
                                         {{ $staff->wage_type === 'per-day' ? 'Per Day (₹' . number_format($staff->piece_rate_per_unit, 0) . ')' : 'Fixed Salary' }}
                                     </span>
                                 </td>
                                 <td class="px-6 py-3.5 text-center whitespace-nowrap">
                                     <div class="inline-flex p-1 bg-slate-100 dark:bg-slate-800/80 rounded-xl border border-slate-200/80 dark:border-slate-700 space-x-1 shadow-2xs">
                                         <label class="cursor-pointer">
                                             <input type="radio" name="attendance[{{ $staff->id }}]" value="present" class="quick-att-input sr-only" {{ $currentStatus === 'present' ? 'checked' : '' }}>
                                             <span class="quick-att-option quick-att-p px-3 py-1.5 rounded-lg text-xs font-bold transition inline-flex items-center gap-1.5 cursor-pointer">
                                                 🟢 Present (1.0)
                                             </span>
                                         </label>
                                         <label class="cursor-pointer">
                                             <input type="radio" name="attendance[{{ $staff->id }}]" value="half_day" class="quick-att-input sr-only" {{ $currentStatus === 'half_day' ? 'checked' : '' }}>
                                             <span class="quick-att-option quick-att-hd px-3 py-1.5 rounded-lg text-xs font-bold transition inline-flex items-center gap-1.5 cursor-pointer">
                                                 🟡 Half Day (0.5)
                                             </span>
                                         </label>
                                         <label class="cursor-pointer">
                                             <input type="radio" name="attendance[{{ $staff->id }}]" value="absent" class="quick-att-input sr-only" {{ $currentStatus === 'absent' ? 'checked' : '' }}>
                                             <span class="quick-att-option quick-att-a px-3 py-1.5 rounded-lg text-xs font-bold transition inline-flex items-center gap-1.5 cursor-pointer">
                                                 🔴 Absent (0.0)
                                             </span>
                                         </label>
                                     </div>
                                 </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-slate-400">
                                    <div class="flex flex-col items-center justify-center space-y-2">
                                        <svg class="w-10 h-10 mx-auto text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                        </svg>
                                        <p class="text-sm font-bold text-slate-600">No Active Employees Found</p>
                                        <p class="text-xs text-slate-400">There are no active employee profiles available to record daily attendance.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
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
