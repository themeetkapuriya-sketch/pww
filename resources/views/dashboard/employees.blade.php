@extends('layouts.app')

@section('title', 'Employees Directory')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between pb-4 border-b border-slate-200">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Employees Directory</h1>
            <p class="text-sm text-slate-500">Manage and catalog welder personnel profiles, salary configurations, and fabrication rates.</p>
        </div>
        <button type="button" 
                onclick="toggleInlineForm('employeeFormContainer', this)" 
                class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold py-2.5 px-4 rounded-xl shadow-md transition duration-150 flex items-center space-x-2">
            <svg class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            <span>Add New Employee</span>
        </button>
    </div>

    <!-- 1. INSERT FORM AT THE TOP (Expandable) -->
    <div id="employeeFormContainer" class="hidden transition-all duration-300 ease-in-out">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <h3 class="text-base font-bold text-slate-800 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-theme-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Add New Employee
            </h3>
            <form action="{{ route('employees.store') }}" method="POST" class="ajax-form space-y-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Full Name</label>
                        <input type="text" name="full_name" placeholder="Employee full name..." required
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Wage Configuration Type</label>
                        <select name="wage_type" id="wageTypeSelect" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 font-medium" required>
                            <option value="per-day">Per Day Wage (Daily Payout Rate)</option>
                            <option value="fixed">Fixed Salary (Monthly regular payout)</option>
                        </select>
                    </div>
                </div>

                <div id="rateFieldContainer" class="space-y-1">
                    <label id="rateFieldLabel" class="block text-xs font-bold text-slate-600 uppercase mb-1">Per Day Wage Rate (₹ / day)</label>
                    <input type="number" id="rateInput" name="piece_rate_per_unit" step="0.01" min="0" placeholder="e.g. 500.00"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700" required>
                </div>

                <div id="fixedSalaryField" class="hidden space-y-1">
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Monthly Basic Fixed Salary (₹ / month)</label>
                    <input type="number" id="fixedInput" name="monthly_salary" step="0.01" min="0" placeholder="e.g. 20000.00"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                </div>

                <button type="submit" class="btn-primary py-2.5 px-6 text-sm font-bold">
                    Register Employee Profile
                </button>
            </form>
        </div>
    </div>

    <!-- 2. EDIT FORM CONTAINER (Hidden by default) -->
    <div id="editEmployeeFormCard" class="hidden transition-all duration-300 ease-in-out">
        <div class="bg-amber-50/50 rounded-2xl shadow-sm border border-amber-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-bold text-amber-900 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    Edit Employee Profile Details
                </h3>
                <button type="button" onclick="closeEditEmployeeForm()" class="text-amber-700 hover:text-amber-900 text-lg font-bold">&times; Close</button>
            </div>
            <form id="editEmployeeForm" method="POST" class="ajax-form space-y-4">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Full Name</label>
                        <input type="text" name="full_name" id="edit_full_name" required
                               class="w-full bg-white border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 text-slate-700">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Wage Configuration Type</label>
                        <select name="wage_type" id="edit_wage_type" class="w-full bg-white border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 text-slate-700 font-medium" required>
                            <option value="per-day">Per Day Wage (Daily Payout Rate)</option>
                            <option value="fixed">Fixed Salary (Monthly regular payout)</option>
                        </select>
                    </div>
                </div>

                <div id="editRateFieldContainer" class="space-y-1">
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Per Day Wage Rate (₹ / day)</label>
                    <input type="number" id="edit_rateInput" name="piece_rate_per_unit" step="0.01" min="0" placeholder="e.g. 500.00"
                           class="w-full bg-white border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 text-slate-700">
                </div>

                <div id="editFixedSalaryField" class="hidden space-y-1">
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Monthly Basic Fixed Salary (₹ / month)</label>
                    <input type="number" id="edit_fixedInput" name="monthly_salary" step="0.01" min="0" placeholder="e.g. 20000.00"
                           class="w-full bg-white border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 text-slate-700">
                </div>

                <div class="flex items-center justify-end space-x-3 pt-2">
                    <button type="button" onclick="closeEditEmployeeForm()" class="px-4 py-2 text-xs font-bold text-slate-600 hover:text-slate-800">Cancel</button>
                    <button type="submit" class="btn-primary py-2 px-6 text-xs font-bold">Update Employee Profile</button>
                </div>
            </form>
        </div>
    </div>

    <!-- 3. RECORDS LIST UNDERNEATH -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
        <h3 class="text-base font-bold text-slate-800 mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-theme-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            Employees Directory Ledger
        </h3>
        
        <div class="overflow-x-auto">
            <table class="erp-datatable min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-[#4371D7] text-white divide-x divide-white/25">
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
                            <td class="px-6 py-4 font-semibold text-slate-800">{{ $staff->full_name }}</td>
                            <td class="px-6 py-4 text-slate-600 font-medium">
                                <span class="px-2.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider
                                    {{ $staff->wage_type === 'per-day' ? 'bg-emerald-50 border border-emerald-200 text-emerald-700' : 'bg-slate-100 border border-slate-200 text-slate-700' }}">
                                    {{ $staff->wage_type === 'per-day' ? 'Per Day' : 'Fixed Salary' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right font-bold text-slate-700">
                                @if ($staff->wage_type === 'per-day')
                                    ₹{{ number_format($staff->piece_rate_per_unit, 2) }} / day
                                @else
                                    ₹{{ number_format($staff->monthly_salary, 2) }} / month basic
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <button type="button" 
                                            title="Edit Employee Profile"
                                            onclick="openEditEmployeeForm({{ $staff->id }}, '{{ addslashes($staff->full_name) }}', '{{ $staff->wage_type }}', '{{ $staff->monthly_salary ?? '' }}', '{{ $staff->piece_rate_per_unit ?? '' }}')"
                                            class="w-8 h-8 p-1.5 inline-flex items-center justify-center rounded-lg bg-amber-500 hover:bg-amber-600 text-white shadow-xs transition duration-150 transform hover:scale-105">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </button>
                                    <button type="button" 
                                            title="Delete Employee Profile"
                                            onclick="deleteEmployee({{ $staff->id }}, '{{ addslashes($staff->full_name) }}')"
                                            class="w-8 h-8 p-1.5 inline-flex items-center justify-center rounded-lg bg-rose-500 hover:bg-rose-600 text-white shadow-xs transition duration-150 transform hover:scale-105">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
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

<script>
    window.toggleInlineForm = function(containerId, btn) {
        const container = document.getElementById(containerId);
        if (!container) return;
        
        const isHidden = container.classList.contains('hidden');
        if (isHidden) {
            container.classList.remove('hidden');
            if (btn) {
                btn.classList.replace('bg-blue-600', 'bg-slate-700');
                btn.classList.replace('hover:bg-blue-700', 'hover:bg-slate-800');
                const icon = btn.querySelector('svg');
                if (icon) icon.style.transform = 'rotate(45deg)';
            }
        } else {
            container.classList.add('hidden');
            if (btn) {
                btn.classList.replace('bg-slate-700', 'bg-blue-600');
                btn.classList.replace('hover:bg-slate-800', 'hover:bg-blue-700');
                const icon = btn.querySelector('svg');
                if (icon) icon.style.transform = 'rotate(0deg)';
            }
        }
    };

    const wageTypeSelect = document.getElementById('wageTypeSelect');
    const rateFieldContainer = document.getElementById('rateFieldContainer');
    const rateFieldLabel = document.getElementById('rateFieldLabel');
    const rateInput = document.getElementById('rateInput');
    const fixedSalaryField = document.getElementById('fixedSalaryField');
    const fixedInput = document.getElementById('fixedInput');
    
    if (wageTypeSelect) {
        wageTypeSelect.addEventListener('change', function() {
            if (this.value === 'per-day') {
                rateFieldContainer.classList.remove('hidden');
                fixedSalaryField.classList.add('hidden');
                rateFieldLabel.innerText = 'Per Day Wage Rate (₹ / day)';
                rateInput.placeholder = 'e.g. 500.00';
                rateInput.required = true;
                fixedInput.required = false;
                fixedInput.value = '';
            } else {
                rateFieldContainer.classList.add('hidden');
                fixedSalaryField.classList.remove('hidden');
                rateInput.required = false;
                fixedInput.required = true;
                rateInput.value = '';
            }
        });
    }

    function openEditEmployeeForm(id, name, wage_type, monthly_salary, piece_rate_per_unit) {
        const editCard = document.getElementById('editEmployeeFormCard');
        const form = document.getElementById('editEmployeeForm');
        if (editCard && form) {
            form.action = "{{ url('/employees') }}/" + id;
            document.getElementById('edit_full_name').value = name;
            const select = document.getElementById('edit_wage_type');
            select.value = wage_type || 'per-day';
            
            toggleEditFields(select.value);
            if (select.value === 'per-day') {
                document.getElementById('edit_rateInput').value = piece_rate_per_unit || '';
            } else {
                document.getElementById('edit_fixedInput').value = monthly_salary || '';
            }

            editCard.classList.remove('hidden');
            editCard.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    }

    function toggleEditFields(wageType) {
        const rateContainer = document.getElementById('editRateFieldContainer');
        const fixedContainer = document.getElementById('editFixedSalaryField');
        const rateInput = document.getElementById('edit_rateInput');
        const fixedInput = document.getElementById('edit_fixedInput');

        if (wageType === 'per-day') {
            rateContainer.classList.remove('hidden');
            fixedContainer.classList.add('hidden');
            rateInput.required = true;
            fixedInput.required = false;
        } else {
            rateContainer.classList.add('hidden');
            fixedContainer.classList.remove('hidden');
            rateInput.required = false;
            fixedInput.required = true;
        }
    }

    document.getElementById('edit_wage_type')?.addEventListener('change', function() {
        toggleEditFields(this.value);
    });

    function closeEditEmployeeForm() {
        const editCard = document.getElementById('editEmployeeFormCard');
        if (editCard) editCard.classList.add('hidden');
    }

    function deleteEmployee(id, name) {
        window.confirmDelete(
            "Delete Employee Profile?",
            "Are you sure you want to delete employee '" + name + "'?",
            function() {
                $.ajax({
                    url: "{{ url('/employees') }}/" + id,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        _method: 'DELETE'
                    },
                    success: function(res) {
                        if (res.success) {
                            $('#row-emp-' + id).fadeOut(300, function() { $(this).remove(); });
                            if (window.showToast) window.showToast('success', res.message);
                        }
                    },
                    error: function(err) {
                        if (window.showToast) window.showToast('error', 'Failed to delete employee profile.');
                    }
                });
            }
        );
    }
</script>
@endsection
