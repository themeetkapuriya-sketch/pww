<!-- TAB 1: EMPLOYEES CATALOG DIRECTORY -->
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
            <form action="{{ route('employees.store') }}" method="POST" class="ajax-form space-y-4" novalidate>
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Full Name <span class="text-rose-500">*</span></label>
                        <input type="text" name="full_name" placeholder="e.g. Ramesh Patel" required
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-800 font-medium">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Wage Configuration Type <span class="text-rose-500">*</span></label>
                        <select name="wage_type" id="wageTypeSelect" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-800 font-semibold" required>
                            <option value="per-day">Per Day Wage (Daily Payout Rate)</option>
                            <option value="fixed">Fixed Salary (Monthly regular payout)</option>
                        </select>
                    </div>
                </div>

                <div id="rateFieldContainer" class="space-y-1">
                    <label id="rateFieldLabel" class="block text-xs font-bold text-slate-600 uppercase mb-1">Per Day Wage Rate (₹ / day) <span class="text-rose-500">*</span></label>
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
            <form id="editEmployeeForm" method="POST" class="ajax-form space-y-4" novalidate>
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Full Name <span class="text-rose-500">*</span></label>
                        <input type="text" name="full_name" id="edit_full_name" required
                               class="w-full bg-white border border-amber-200 rounded-xl py-2.5 px-3.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 text-slate-800 font-medium">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Wage Configuration Type <span class="text-rose-500">*</span></label>
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
                    @forelse ($staffProfiles as $staff)
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
                                            data-staff="{{ json_encode(new \App\Http\Resources\StaffProfileResource($staff)) }}"
                                            onclick="openEditEmployeeForm(this)"
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
                    @empty
                        <tr class="empty-row">
                            <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center space-y-2">
                                    <svg class="w-10 h-10 mx-auto text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                    </svg>
                                    <p class="text-sm font-bold text-slate-600">No Records Found</p>
                                    <p class="text-xs text-slate-400">There are no employee profiles registered yet.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
