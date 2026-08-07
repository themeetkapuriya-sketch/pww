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

    <!-- TAB 1: EMPLOYEES CATALOG DIRECTORY -->
    @include('pages.employees.partials.directory')

    <!-- TAB 2: DAILY ATTENDANCE SHEET -->
    @include('pages.employees.partials.attendance')

    <!-- TAB 3: MONTHLY SALARY LEDGER & DISBURSAL -->
    @include('pages.employees.partials.salary')

</div>

<!-- MODAL: DISBURSE SALARY & AUTO-LOG EXPENSE -->
@include('pages.employees.partials.disburse_modal')

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

function openDisburseModal(btnOrStaff, monthYear, daysPresent, calculatedSalary, optionalDisbursal) {
    let staff = btnOrStaff;
    let disbursal = optionalDisbursal;

    if (btnOrStaff && btnOrStaff.dataset) {
        if (btnOrStaff.dataset.staff) {
            try { staff = JSON.parse(btnOrStaff.dataset.staff); } catch(e) {}
        }
        if (btnOrStaff.dataset.disbursal) {
            try { disbursal = JSON.parse(btnOrStaff.dataset.disbursal); } catch(e) {}
        }
    }

    if (!staff) return;

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

function openEditEmployeeForm(btnOrStaff) {
    let staff = btnOrStaff;
    if (btnOrStaff && btnOrStaff.dataset && btnOrStaff.dataset.staff) {
        try { staff = JSON.parse(btnOrStaff.dataset.staff); } catch(e) {}
    }
    if (!staff) return;

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
