@extends('layouts.app')

@section('title', 'Employees & Payroll Management')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between pb-4 border-b border-slate-200 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Employees Directory & Payroll Hub</h1>
            <p class="text-sm text-slate-500">Manage employee profiles, daily attendance sheets, monthly salary calculations, and salary payments.</p>
        </div>

        @php
            $currentEmpTab = $activeTab ?? request('tab', 'directory');
        @endphp
        <!-- Top Navigation Sub-Tabs -->
        <div class="inline-flex p-1 bg-slate-100/80 rounded-2xl border border-slate-200/80 self-start md:self-auto">
            <button type="button" onclick="switchEmpTab('directory')" id="tabBtn-directory" class="emp-tab-btn px-4 py-2 rounded-xl text-xs font-bold transition duration-150 {{ $currentEmpTab === 'directory' ? 'active-emp-tab bg-blue-50 text-blue-700' : 'text-slate-600 hover:text-slate-900' }}">
                👥 Employees Catalog
            </button>
            <button type="button" onclick="switchEmpTab('attendance')" id="tabBtn-attendance" class="emp-tab-btn px-4 py-2 rounded-xl text-xs font-bold transition duration-150 {{ $currentEmpTab === 'attendance' ? 'active-emp-tab bg-blue-50 text-blue-700' : 'text-slate-600 hover:text-slate-900' }}">
                📅 Daily Attendance
            </button>
            <button type="button" onclick="switchEmpTab('payment')" id="tabBtn-payment" class="emp-tab-btn px-4 py-2 rounded-xl text-xs font-bold transition duration-150 {{ $currentEmpTab === 'payment' ? 'active-emp-tab bg-blue-50 text-blue-700' : 'text-slate-600 hover:text-slate-900' }}">
                💳 Monthly Salary Ledger
            </button>
        </div>
    </div>

    <!-- TAB 1: EMPLOYEES CATALOG DIRECTORY -->
    @include('pages.employees.partials.directory')

    <!-- TAB 2: DAILY ATTENDANCE SHEET -->
    @include('pages.employees.partials.attendance')

    <!-- TAB 3: MONTHLY SALARY LEDGER & PAYMENT -->
    @include('pages.employees.partials.salary')

</div>

<!-- MODAL: PAY SALARY & AUTO-LOG EXPENSE -->
@include('pages.employees.partials.payment_modal')

<!-- MODAL: ISSUE SALARY ADVANCE & AUTO-LOG EXPENSE -->
@include('pages.employees.partials.advance_modal')

<!-- MODAL: INDIVIDUAL EMPLOYEE LEDGER STATEMENT & PASSBOOK -->
@include('pages.employees.partials.statement_modal')

<script>
let currentStaffRate = 0;
let currentWageType = 'per-day';
let currentStatementData = null;

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

    try {
        const url = new URL(window.location);
        url.searchParams.set('tab', tabName);
        window.history.replaceState({}, '', url);
    } catch(e) {}
}

function toggleEmployeeStatusAjax(empId, empName, btnEl) {
    try {
        const btn = btnEl.closest('button');
        const isActive = btn.getAttribute('data-active') === '1';
        const actionText = isActive ? 'deactivate' : 'activate';
        const title = `${isActive ? 'Deactivate' : 'Activate'} Employee Profile?`;
        const text = `Are you sure you want to ${actionText} employee profile '${empName}'? Inactive employees will be hidden from attendance, payroll, advances, and production logging.`;

        const performToggle = function() {
            fetch(`/employees/${empId}/toggle-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (window.showToast) {
                        window.showToast(data.is_active ? 'success' : 'danger', data.message);
                    }
                    if (window.clearPageCache) {
                        window.clearPageCache();
                    }
                    if (window.loadPage) {
                        const activeTabBtn = document.querySelector('.emp-tab-btn.active-emp-tab');
                        const tabKey = activeTabBtn ? activeTabBtn.id.replace('tabBtn-', '') : 'directory';
                        const url = new URL(window.location.href);
                        url.searchParams.set('tab', tabKey);
                        window.loadPage(url.href, true, true);
                    } else {
                        btn.setAttribute('data-active', data.is_active ? '1' : '0');
                        if (data.is_active) {
                            btn.classList.remove('bg-rose-500', 'hover:bg-rose-600');
                            btn.classList.add('bg-emerald-500', 'hover:bg-emerald-600');
                            btn.setAttribute('title', 'Deactivate Employee Profile');
                        } else {
                            btn.classList.remove('bg-emerald-500', 'hover:bg-emerald-600');
                            btn.classList.add('bg-rose-500', 'hover:bg-rose-600');
                            btn.setAttribute('title', 'Activate Employee Profile');
                        }
                    }
                }
            })
            .catch(err => {
                if (window.showToast) window.showToast('danger', 'Failed to toggle employee status.');
            });
        };

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: title,
                text: text,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: isActive ? '#ef4444' : '#10b981',
                cancelButtonColor: '#64748b',
                confirmButtonText: isActive ? 'Yes, deactivate' : 'Yes, activate',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    performToggle();
                }
            });
        } else {
            if (confirm(`${title}\n\n${text}`)) {
                performToggle();
            }
        }
    } catch (e) {
        console.error("Error in toggleEmployeeStatusAjax:", e);
    }
}
window.toggleEmployeeStatusAjax = toggleEmployeeStatusAjax;

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
                    if (window.showToast) window.showToast('danger', data.message);
                    if (window.clearPageCache) window.clearPageCache();
                    if (window.loadPage) {
                        const activeTabBtn = document.querySelector('.emp-tab-btn.active-emp-tab');
                        const tabKey = activeTabBtn ? activeTabBtn.id.replace('tabBtn-', '') : 'directory';
                        const url = new URL(window.location.href);
                        url.searchParams.set('tab', tabKey);
                        window.loadPage(url.href, true, true);
                    } else {
                        const tr = document.getElementById(`row-emp-${empId}`);
                        if (tr) tr.remove();
                    }
                }
            });
        }
    });
}

function loadAttendanceForDate(dateVal) {
    document.getElementById('attendanceFormDate').value = dateVal;
    if (window.loadPage) {
        window.loadPage(`/employees?date=${dateVal}&tab=attendance`);
    } else {
        window.location.href = `/employees?date=${dateVal}`;
    }
}

function filterPaymentMonth(monthVal) {
    if (window.loadPage) {
        window.loadPage(`/employees?month=${monthVal}&tab=payment`);
    } else {
        window.location.href = `/employees?month=${monthVal}`;
    }
}

function openAdvanceModal(staffId) {
    if (staffId && document.getElementById('advanceStaffSelect')) {
        document.getElementById('advanceStaffSelect').value = staffId;
    }
    const modal = document.getElementById('giveAdvanceModal');
    if (modal) modal.classList.remove('hidden');
}

function closeAdvanceModal() {
    const modal = document.getElementById('giveAdvanceModal');
    if (modal) modal.classList.add('hidden');
}
window.closeAdvanceModal = closeAdvanceModal;

let currentStatementStaffId = null;

function openEmployeeStatementModal(staffId, overrideRange = null, overrideMonth = null) {
    const modal = document.getElementById('empStatementModal');
    if (!modal) return;

    if (staffId) currentStatementStaffId = staffId;
    const activeStaffId = currentStatementStaffId;
    if (!activeStaffId) return;

    const tbody = document.getElementById('stmtTransactionsTableBody');
    if (tbody) {
        tbody.innerHTML = '<tr><td colspan="8" class="p-6 text-center text-slate-400">Loading statement history...</td></tr>';
    }
    modal.classList.remove('hidden');

    const rangeSelect = document.getElementById('stmtRangeSelect');
    const monthInput = document.getElementById('stmtMonthInput');

    const rangeVal = overrideRange !== null ? overrideRange : (rangeSelect ? rangeSelect.value : 'current_month');
    const monthVal = overrideMonth !== null ? overrideMonth : (monthInput ? monthInput.value : new Date().toISOString().slice(0, 7));

    if (monthInput && overrideMonth !== null) monthInput.value = overrideMonth;
    if (rangeSelect && overrideRange !== null) rangeSelect.value = overrideRange;

    fetch(`/employees/${activeStaffId}/statement?range=${rangeVal}&month=${monthVal}`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success) return;
        currentStatementData = data;

        const staff = data.staff;
        document.getElementById('stmtEmpName').innerText = staff.full_name;
        document.getElementById('stmtAvatar').innerText = (staff.full_name || 'E').slice(0, 1).toUpperCase();
        document.getElementById('stmtEmpMobile').innerText = staff.mobile_number || 'N/A';
        document.getElementById('stmtWageTypeBadge').innerText = staff.wage_type_label;
        
        const statusBadge = document.getElementById('stmtStatusBadge');
        if (statusBadge) {
            if (staff.is_active) {
                statusBadge.className = 'inline-flex items-center px-2.5 py-0.5 rounded-md text-[10px] font-bold uppercase bg-emerald-50 text-emerald-700 border border-emerald-200';
                statusBadge.innerText = 'Active';
            } else {
                statusBadge.className = 'inline-flex items-center px-2.5 py-0.5 rounded-md text-[10px] font-bold uppercase bg-rose-50 text-rose-700 border border-rose-200';
                statusBadge.innerText = 'Inactive';
            }
        }

        document.getElementById('stmtCurrentRate').innerText = data.current_rate_formatted;
        document.getElementById('stmtAdvancePaid').innerText = '₹' + (data.pending_advances_total || 0).toLocaleString('en-IN', { minimumFractionDigits: 2 });
        document.getElementById('stmtGrossEarnings').innerText = '₹' + (data.gross_earnings || 0).toLocaleString('en-IN', { minimumFractionDigits: 2 });
        document.getElementById('stmtDaysPresent').innerText = (data.days_present || 0).toFixed(1);
        document.getElementById('stmtSelectedMonthLabel').innerText = data.selected_month;
        
        const netDueEl = document.getElementById('stmtNetDueAmount');
        const netDueCard = document.getElementById('stmtNetDueCard');
        const dueVal = data.net_due_amount || 0;
        if (netDueEl) {
            netDueEl.innerText = '₹' + dueVal.toLocaleString('en-IN', { minimumFractionDigits: 2 });
        }
        if (netDueCard) {
            if (dueVal > 0) {
                netDueCard.className = 'p-4 rounded-2xl bg-rose-50/70 border border-rose-200/80 space-y-1';
            } else {
                netDueCard.className = 'p-4 rounded-2xl bg-emerald-50/70 border border-emerald-200/80 space-y-1';
            }
        }

        document.getElementById('stmtTxnCount').innerText = `${(data.transactions || []).length} records`;

        if (!data.transactions || data.transactions.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="p-6 text-center text-slate-400">No statement transaction records found for the selected period.</td></tr>';
            return;
        }

        let html = '';
        data.transactions.forEach(tx => {
            html += `
                <tr class="hover:bg-slate-50 transition">
                    <td class="p-3 font-semibold text-slate-700 font-mono">${tx.date_formatted}</td>
                    <td class="p-3 font-bold">
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold border ${tx.badge_class}">
                            ${tx.type}
                        </span>
                    </td>
                    <td class="p-3 font-medium text-slate-800">${tx.description}</td>
                    <td class="p-3 text-right font-mono font-bold text-slate-800">₹${(tx.gross_amount || 0).toLocaleString('en-IN', { minimumFractionDigits: 2 })}</td>
                    <td class="p-3 text-right font-mono text-amber-700 font-semibold">${tx.advance_deductions > 0 ? '-₹' + (tx.advance_deductions).toLocaleString('en-IN', { minimumFractionDigits: 2 }) : '-'}</td>
                    <td class="p-3 text-right font-mono font-black text-slate-900">₹${(tx.net_amount || 0).toLocaleString('en-IN', { minimumFractionDigits: 2 })}</td>
                    <td class="p-3 text-center text-slate-600 font-medium">${tx.payment_method}</td>
                    <td class="p-3 text-center whitespace-nowrap">
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold border ${tx.status_class}">
                            ${tx.status}
                        </span>
                    </td>
                </tr>
            `;
        });
        tbody.innerHTML = html;
    })
    .catch(err => {
        if (tbody) tbody.innerHTML = '<tr><td colspan="8" class="p-6 text-center text-rose-500 font-semibold">Failed to load statement details.</td></tr>';
    });
}

function onStatementFilterChange() {
    const rangeSelect = document.getElementById('stmtRangeSelect');
    if (!rangeSelect) return;
    openEmployeeStatementModal(currentStatementStaffId, rangeSelect.value, null);
}
window.onStatementFilterChange = onStatementFilterChange;

function onStatementMonthPickerChange() {
    const monthInput = document.getElementById('stmtMonthInput');
    if (!monthInput) return;
    openEmployeeStatementModal(currentStatementStaffId, 'current_month', monthInput.value);
}
window.onStatementMonthPickerChange = onStatementMonthPickerChange;

function closeStatementModal() {
    document.getElementById('empStatementModal')?.classList.add('hidden');
}
window.closeStatementModal = closeStatementModal;
window.openEmployeeStatementModal = openEmployeeStatementModal;

function triggerAdvanceFromStatement() {
    if (!currentStatementData || !currentStatementData.staff) return;
    closeStatementModal();
    openAdvanceModal(currentStatementData.staff.id);
}
window.triggerAdvanceFromStatement = triggerAdvanceFromStatement;

function triggerPaymentFromStatement() {
    if (!currentStatementData || !currentStatementData.staff) return;
    const staff = currentStatementData.staff;
    closeStatementModal();
    openPaymentModal(
        staff,
        currentStatementData.selected_month,
        currentStatementData.days_present,
        currentStatementData.net_due_amount,
        null
    );
}
window.triggerPaymentFromStatement = triggerPaymentFromStatement;

function openPaymentModal(btnOrStaff, monthYear, daysPresent, calculatedSalary, optionalPayment) {
    let staff = btnOrStaff;
    let payment = optionalPayment;
    let pendingAdvance = 0;
    let missingDates = [];

    if (btnOrStaff && btnOrStaff.dataset) {
        if (btnOrStaff.dataset.staff) {
            try { staff = JSON.parse(btnOrStaff.dataset.staff); } catch(e) {}
        }
        if (btnOrStaff.dataset.payment) {
            try { payment = JSON.parse(btnOrStaff.dataset.payment); } catch(e) {}
        }
        if (btnOrStaff.dataset.pendingAdvance) {
            pendingAdvance = parseFloat(btnOrStaff.dataset.pendingAdvance) || 0;
        }
        if (btnOrStaff.dataset.missingDates) {
            try { missingDates = JSON.parse(btnOrStaff.dataset.missingDates) || []; } catch(e) {}
        }
    }

    if (!staff) return;

    currentStaffRate = staff.wage_type === 'per-day' ? (parseFloat(staff.piece_rate_per_unit) || 0) : (parseFloat(staff.monthly_salary) || 0);
    currentWageType = staff.wage_type;

    document.getElementById('paymentStaffId').value = staff.id;
    document.getElementById('paymentMonthYear').value = monthYear;
    document.getElementById('paymentEmployeeName').innerText = staff.full_name;
    document.getElementById('paymentMonthBadge').innerText = monthYear;

    const missingAlert = document.getElementById('paymentMissingDatesAlert');
    const missingList = document.getElementById('paymentMissingDatesList');
    if (missingAlert && missingList) {
        if (missingDates && missingDates.length > 0) {
            missingList.innerText = missingDates.join(', ');
            missingAlert.classList.remove('hidden');
        } else {
            missingAlert.classList.add('hidden');
        }
    }
    document.getElementById('paymentWageDetails').innerText = staff.wage_type === 'per-day' 
        ? `Per Day Rate: ₹${currentStaffRate.toFixed(2)} / day` 
        : `Fixed Monthly Basic: ₹${currentStaffRate.toFixed(2)} / month`;

    const daysContainer = document.getElementById('paymentDaysContainer');
    const daysInput = document.getElementById('paymentDaysInput');

    if (daysContainer) daysContainer.classList.remove('hidden');
    if (daysInput) daysInput.value = daysPresent || 0;

    const advBadge = document.getElementById('paymentPendingAdvanceBadge');
    if (advBadge) advBadge.innerText = `₹${pendingAdvance.toFixed(2)}`;

    const advDeductInput = document.getElementById('paymentAdvanceDeductionInput');
    if (advDeductInput) {
        if (payment && payment.advance_deduction !== undefined && payment.advance_deduction !== null) {
            advDeductInput.value = parseFloat(payment.advance_deduction).toFixed(2);
        } else {
            advDeductInput.value = pendingAdvance.toFixed(2);
        }
    }

    if (payment) {
        document.getElementById('paymentMethodSelect').value = payment.payment_method || 'Cash';
        document.getElementById('paymentNotes').value = payment.notes || '';
        if (payment.payment_date && document.getElementById('paymentPaymentDate')) {
            let pDate = payment.payment_date;
            if (typeof pDate === 'string' && pDate.includes('T')) pDate = pDate.split('T')[0];
            document.getElementById('paymentPaymentDate').value = pDate;
        }
    } else {
        document.getElementById('paymentMethodSelect').value = 'Cash';
        document.getElementById('paymentNotes').value = '';
        if (document.getElementById('paymentPaymentDate')) {
            document.getElementById('paymentPaymentDate').value = new Date().toISOString().split('T')[0];
        }
    }

    calculateModalSalary();

    if (payment && payment.total_salary) {
        document.getElementById('paymentTotalSalaryInput').value = parseFloat(payment.total_salary).toFixed(2);
    }

    document.getElementById('paymentSalaryModal').classList.remove('hidden');
}

function calculateModalSalary() {
    let gross = 0;
    if (currentWageType === 'per-day') {
        const days = parseFloat(document.getElementById('paymentDaysInput').value) || 0;
        gross = days * currentStaffRate;
    } else {
        gross = currentStaffRate;
    }

    const advanceDeduction = parseFloat(document.getElementById('paymentAdvanceDeductionInput')?.value) || 0;
    let net = gross - advanceDeduction;
    if (net < 0) net = 0;

    const salaryInput = document.getElementById('paymentTotalSalaryInput');
    if (salaryInput) {
        salaryInput.value = net.toFixed(2);
    }
}

function closePaymentModal() {
    document.getElementById('paymentSalaryModal')?.classList.add('hidden');
}
window.closePaymentModal = closePaymentModal;

function toggleInlineForm(containerId, btnEl) {
    const container = document.getElementById(containerId);
    if (!container) return;
    container.classList.toggle('hidden');
}

function openEditEmployeeForm(btnOrStaff) {
    let staff = btnOrStaff;
    if (btnOrStaff && btnOrStaff.dataset && btnOrStaff.dataset.staff) {
        try { staff = JSON.parse(btnOrStaff.dataset.staff); } catch(e) {}
    }
    if (!staff) return;

    document.getElementById('edit_full_name').value = staff.full_name;
    document.getElementById('edit_mobile_number').value = staff.mobile_number || '';
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



(function() {
    const urlParams = new URLSearchParams(window.location.search);
    const activeTab = urlParams.get('tab') || "{{ $currentEmpTab ?? 'directory' }}";
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
})();
</script>

<style>
.active-emp-tab {
    background-color: #ffffff !important;
    color: #1d4ed8 !important;
    box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1);
}
</style>
@endsection
