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
let employeeStatusChanged = false;

function switchEmpTab(tabName) {
    if (employeeStatusChanged && (tabName === 'payment' || tabName === 'attendance')) {
        employeeStatusChanged = false;
        if (window.clearPageCache) window.clearPageCache();
        const url = new URL(window.location.href);
        url.searchParams.set('tab', tabName);
        if (window.loadPage) {
            window.loadPage(url.href, true);
            return;
        }
    }

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
                    employeeStatusChanged = true;
                    if (window.showToast) {
                        window.showToast(data.is_active ? 'success' : 'danger', data.message);
                    }
                    
                    const isNowActive = !!data.is_active;
                    btn.setAttribute('data-active', isNowActive ? '1' : '0');
                    if (isNowActive) {
                        btn.classList.remove('bg-rose-500', 'hover:bg-rose-600');
                        btn.classList.add('bg-emerald-500', 'hover:bg-emerald-600');
                        btn.setAttribute('title', 'Deactivate Employee Profile');
                        btn.innerHTML = `
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                            </svg>
                        `;
                    } else {
                        btn.classList.remove('bg-emerald-500', 'hover:bg-emerald-600');
                        btn.classList.add('bg-rose-500', 'hover:bg-rose-600');
                        btn.setAttribute('title', 'Activate Employee Profile');
                        btn.innerHTML = `
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z" clip-rule="evenodd" />
                                <path d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 1.002 0 1.97-.146 2.883-.404z" />
                            </svg>
                        `;
                    }

                    // 1. Update Directory Tab Row
                    const tr = btn.closest('tr');
                    if (tr) {
                        const statusCell = tr.querySelector('.emp-status-cell');
                        if (statusCell) {
                            if (isNowActive) {
                                statusCell.innerHTML = `
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[10px] font-bold uppercase bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Active
                                    </span>
                                `;
                            } else {
                                statusCell.innerHTML = `
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[10px] font-bold uppercase bg-rose-50 text-rose-700 border border-rose-200">
                                        Inactive
                                    </span>
                                `;
                            }
                        }
                    }

                    // 2. Sync with Daily Attendance Tab
                    const attRow = document.getElementById(`att-row-emp-${empId}`);
                    if (attRow) {
                        if (isNowActive) {
                            $(attRow).removeClass('hidden').fadeIn(200);
                        } else {
                            $(attRow).fadeOut(200, function() { $(this).addClass('hidden'); });
                        }
                    }

                    // 3. Sync with Monthly Salary Ledger Tab (Instantly show/hide via DataTables filter)
                    const $salRow = $(`#row-payment-${empId}`);
                    if ($salRow.length) {
                        $salRow.attr('data-emp-active', isNowActive ? '1' : '0');
                        const $salTable = $salRow.closest('table');
                        if ($.fn.DataTable && $.fn.DataTable.isDataTable($salTable)) {
                            $salTable.DataTable().draw(false);
                        }
                    }

                    // 4. Sync with Advance Modal Select Dropdown
                    const advanceOpt = document.querySelector(`#advanceStaffSelect option[value="${empId}"]`);
                    if (advanceOpt) {
                        if (isNowActive) {
                            advanceOpt.disabled = false;
                            advanceOpt.classList.remove('hidden');
                        } else {
                            advanceOpt.disabled = true;
                            advanceOpt.classList.add('hidden');
                        }
                    }

                    if (window.clearPageCache) {
                        window.clearPageCache();
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
                    
                    // 1. Remove from Directory Tab
                    const tr = document.getElementById(`row-emp-${empId}`) || (btnEl ? btnEl.closest('tr') : null);
                    if (tr) {
                        $(tr).fadeOut(300, function() { $(this).remove(); });
                    }

                    // 2. Remove from Attendance Tab
                    const attRow = document.getElementById(`att-row-emp-${empId}`);
                    if (attRow) {
                        $(attRow).fadeOut(300, function() { $(this).remove(); });
                    }

                    // 3. Remove from Salary Ledger Tab
                    const salaryRow = document.getElementById(`row-payment-${empId}`);
                    if (salaryRow) {
                        $(salaryRow).fadeOut(300, function() { $(this).remove(); });
                    }

                    // 4. Remove from Advance Modal Select Dropdown
                    const advanceOpt = document.querySelector(`#advanceStaffSelect option[value="${empId}"]`);
                    if (advanceOpt) {
                        advanceOpt.remove();
                    }

                    if (window.clearPageCache) window.clearPageCache();
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
    const $container = $('#empTab-payment');
    if ($container.length) {
        $container.css('opacity', '0.5');
    }
    const targetUrl = `/employees?month=${monthVal}&tab=payment`;
    if (window.loadPage) {
        window.loadPage(targetUrl, true);
    } else {
        window.location.href = targetUrl;
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



if (typeof $ !== 'undefined' && $.fn && $.fn.dataTable && !window.salaryFilterRegistered) {
    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        const tableNode = settings.nTable;
        if ($(tableNode).hasClass('salary-datatable')) {
            const rowNode = settings.aoData[dataIndex]?.nTr;
            if (rowNode) {
                return $(rowNode).attr('data-emp-active') === '1';
            }
        }
        return true;
    });
    window.salaryFilterRegistered = true;
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
