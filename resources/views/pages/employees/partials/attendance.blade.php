<!-- TAB 2: DAILY ATTENDANCE SHEET -->
<div id="empTab-attendance" class="emp-tab-content hidden space-y-6">
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 pb-4">
            <div>
                <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    Daily Attendance Entry Sheet
                </h3>
                <p class="text-xs text-slate-500 font-medium">Select a date to mark attendance. Present counts as 1.0 day, Half-Day counts as 0.5 day.</p>
            </div>

            <div class="flex items-center gap-3">
                <label class="text-xs font-bold text-slate-600 uppercase">Attendance Date:</label>
                <input type="date" id="attendanceDateInput" value="{{ $selectedDate }}" onchange="loadAttendanceForDate(this.value)" class="bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
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
