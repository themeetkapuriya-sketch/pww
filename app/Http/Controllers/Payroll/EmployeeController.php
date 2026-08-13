<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\Expense;
use App\Models\SalaryAdvance;
use App\Models\SalaryPayment;
use App\Models\StaffProfile;
use App\Services\PayrollService;
use App\Services\RolePermissionService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class EmployeeController extends Controller
{
    protected $payrollService;

    public function __construct(PayrollService $payrollService)
    {
        $this->payrollService = $payrollService;
    }

    /**
     * 8. Employees Directory, Daily Attendance & Monthly Salary Payment Hub.
     */
    public function employees(Request $request)
    {
        $staffProfiles = StaffProfile::orderBy('full_name')->get();
        $activeStaffProfiles = $staffProfiles->where('is_active', true);
        $selectedDate = $request->input('date', now()->toDateString());
        $selectedMonth = $request->input('month', now()->format('Y-m'));

        // Attendance for selected date
        $attendanceForDate = AttendanceRecord::where('date', $selectedDate)
            ->pluck('status', 'staff_profile_id')
            ->toArray();

        // Monthly Attendance summary (Calculates total present days per employee, treating missing/unrecorded dates as present)
        $carbonMonth = \Carbon\Carbon::parse($selectedMonth . '-01');
        $daysInMonth = $carbonMonth->daysInMonth;
        
        $now = \Carbon\Carbon::now();
        if ($carbonMonth->format('Y-m') === $now->format('Y-m')) {
            $limitDay = $now->day;
        } elseif ($carbonMonth->isPast()) {
            $limitDay = $daysInMonth;
        } else {
            $limitDay = 0;
        }

        $allAttendance = AttendanceRecord::where('date', 'like', "{$selectedMonth}%")
            ->get()
            ->groupBy('staff_profile_id');

        $monthlyAttendance = collect();
        $missingAttendanceDates = collect();

        foreach ($staffProfiles as $staff) {
            $staffRecords = $allAttendance->get($staff->id, collect())->keyBy(function($r) {
                return $r->date instanceof \Carbon\Carbon ? $r->date->format('Y-m-d') : substr($r->date, 0, 10);
            });

            $payableDays = 0.00;
            $missingDates = [];

            for ($d = 1; $d <= $limitDay; $d++) {
                $dateStr = sprintf('%s-%02d', $selectedMonth, $d);
                if ($staffRecords->has($dateStr)) {
                    $status = $staffRecords->get($dateStr)->status;
                    if ($status === 'present') {
                        $payableDays += 1.0;
                    } elseif ($status === 'half_day') {
                        $payableDays += 0.5;
                    }
                } else {
                    // Treat missing attendance as present
                    $payableDays += 1.0;
                    $missingDates[] = \Carbon\Carbon::parse($dateStr)->format('d M');
                }
            }

            $monthlyAttendance->put($staff->id, $payableDays);
            $missingAttendanceDates->put($staff->id, $missingDates);
        }

        // Salary payments for selected month
        $salaryPayments = SalaryPayment::where('month_year', $selectedMonth)
            ->with('staffProfile')
            ->get()
            ->keyBy('staff_profile_id');

        // Staff profiles for salary table: active ones + inactive ones that have a payment record for selected month
        $salaryStaffProfiles = $staffProfiles->filter(function ($s) use ($salaryPayments) {
            return $s->is_active || $salaryPayments->has($s->id);
        });

        // Pending salary advances for each staff member up to the selected month (advances issued on/before end of selected month)
        $selectedMonthEnd = \Carbon\Carbon::parse($selectedMonth . '-01')->endOfMonth()->format('Y-m-d');
        $pendingAdvances = SalaryAdvance::where('status', 'pending')
            ->where('advance_date', '<=', $selectedMonthEnd)
            ->get()
            ->groupBy('staff_profile_id')
            ->map(function ($advances) {
                return $advances->sum('amount');
            });

        $activeTab = $request->input('tab', 'directory');

        return view('pages.employees', compact(
            'staffProfiles',
            'activeStaffProfiles',
            'salaryStaffProfiles',
            'selectedDate',
            'selectedMonth',
            'activeTab',
            'attendanceForDate',
            'monthlyAttendance',
            'missingAttendanceDates',
            'salaryPayments',
            'pendingAdvances'
        ));
    }

    /**
     * Create Employee Profile (AJAX).
     */
    public function storeEmployee(Request $request)
    {
        if ($res = RolePermissionService::authorizeAction($request, 'action_insert')) {
            return $res;
        }

        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'mobile_number' => 'nullable|string|max:20',
            'wage_type' => 'required|in:fixed,per-day',
            'monthly_salary' => 'nullable|required_if:wage_type,fixed|numeric|min:0',
            'piece_rate_per_unit' => 'nullable|required_if:wage_type,per-day|numeric|min:0',
        ]);

        $existingStaff = StaffProfile::where('full_name', $validated['full_name'])->first();
        if ($existingStaff) {
            return response()->json([
                'success' => false,
                'message' => "An employee profile for '{$validated['full_name']}' already exists!",
                'errors' => ['full_name' => ["An employee profile for '{$validated['full_name']}' already exists!"]],
            ], 422);
        }

        $staff = StaffProfile::create($validated);

        return response()->json([
            'success' => true,
            'message' => "Employee profile for '{$staff->full_name}' created successfully!",
            'data' => $staff,
        ]);
    }

    /**
     * Update employee profile (AJAX).
     */
    public function updateEmployee(Request $request, $id)
    {
        if ($res = RolePermissionService::authorizeAction($request, 'action_update')) {
            return $res;
        }

        $staff = StaffProfile::findOrFail($id);

        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'mobile_number' => 'nullable|string|max:20',
            'wage_type' => 'required|in:fixed,per-day',
            'monthly_salary' => 'nullable|required_if:wage_type,fixed|numeric|min:0',
            'piece_rate_per_unit' => 'nullable|required_if:wage_type,per-day|numeric|min:0',
        ]);

        if ($validated['wage_type'] === 'fixed') {
            $validated['piece_rate_per_unit'] = null;
        } else {
            $validated['monthly_salary'] = null;
        }

        $staff->update($validated);

        return response()->json([
            'success' => true,
            'message' => "Employee profile for '{$staff->full_name}' updated successfully!",
            'data' => $staff,
        ]);
    }

    /**
     * Toggle active/inactive status of employee profile (AJAX).
     */
    public function toggleStatus(Request $request, $id)
    {
        if ($res = RolePermissionService::authorizeAction($request, 'action_update')) {
            return $res;
        }

        $staff = StaffProfile::findOrFail($id);
        $staff->is_active = ! $staff->is_active;
        $staff->save();

        $actionText = $staff->is_active ? 'activated' : 'deactivated';

        return response()->json([
            'success' => true,
            'is_active' => (bool) $staff->is_active,
            'message' => "Employee profile '{$staff->full_name}' {$actionText} successfully!",
        ]);
    }

    /**
     * Get individual employee financial statement & passbook ledger (JSON).
     */
    public function getEmployeeStatement(Request $request, $id)
    {
        $staff = StaffProfile::findOrFail($id);
        $range = $request->input('range', 'current_month');
        $selectedMonth = $request->input('month', now()->format('Y-m'));

        // Attendance & Gross Earnings for selected month
        $carbonMonth = \Carbon\Carbon::parse($selectedMonth . '-01');
        $daysInMonth = $carbonMonth->daysInMonth;
        
        $now = \Carbon\Carbon::now();
        if ($carbonMonth->format('Y-m') === $now->format('Y-m')) {
            $limitDay = $now->day;
        } elseif ($carbonMonth->isPast()) {
            $limitDay = $daysInMonth;
        } else {
            $limitDay = 0;
        }

        $staffAttendanceRecords = AttendanceRecord::where('staff_profile_id', $staff->id)
            ->where('date', 'like', "{$selectedMonth}%")
            ->get()
            ->keyBy(function($r) {
                return $r->date instanceof \Carbon\Carbon ? $r->date->format('Y-m-d') : substr($r->date, 0, 10);
            });

        $daysPresent = 0.00;
        for ($d = 1; $d <= $limitDay; $d++) {
            $dateStr = sprintf('%s-%02d', $selectedMonth, $d);
            if ($staffAttendanceRecords->has($dateStr)) {
                $status = $staffAttendanceRecords->get($dateStr)->status;
                if ($status === 'present') {
                    $daysPresent += 1.0;
                } elseif ($status === 'half_day') {
                    $daysPresent += 0.5;
                }
            } else {
                $daysPresent += 1.0;
            }
        }

        $rateAmount = $staff->wage_type === 'per-day' ? (float) $staff->piece_rate_per_unit : (float) $staff->monthly_salary;
        if ($staff->wage_type === 'per-day') {
            $grossEarnings = round($daysPresent * $rateAmount, 2);
        } else {
            $grossEarnings = round($rateAmount, 2);
        }

        $selectedMonthEnd = \Carbon\Carbon::parse($selectedMonth . '-01')->endOfMonth()->format('Y-m-d');
        $pendingAdvanceTotal = (float) $staff->pendingAdvanceTotal($selectedMonthEnd);

        $existingPayment = SalaryPayment::where('staff_profile_id', $staff->id)
            ->where('month_year', $selectedMonth)
            ->first();

        if ($existingPayment) {
            $daysPresent = (float) $existingPayment->days_present;
            $advanceDeducted = (float) $existingPayment->advance_deduction;
            $netPaidOrDue = (float) $existingPayment->total_salary;
            $paymentStatus = $existingPayment->status;
        } else {
            $advanceDeducted = $pendingAdvanceTotal;
            $netPaidOrDue = max(0, round($grossEarnings - $pendingAdvanceTotal, 2));
            $paymentStatus = 'pending';
        }

        // Transactions History Passbook Query with Range Filter
        $paymentsQuery = SalaryPayment::where('staff_profile_id', $staff->id);
        $advancesQuery = SalaryAdvance::where('staff_profile_id', $staff->id);

        if ($range === 'current_month') {
            $paymentsQuery->where('month_year', $selectedMonth);
            $advancesQuery->where('advance_date', 'like', "{$selectedMonth}%");
        } elseif ($range === 'last_3_months') {
            $cutoff = now()->subMonths(3)->startOfMonth()->format('Y-m-d');
            $paymentsQuery->where('created_at', '>=', $cutoff);
            $advancesQuery->where('advance_date', '>=', $cutoff);
        } elseif ($range === 'this_year') {
            $yearStr = now()->format('Y');
            $paymentsQuery->where('month_year', 'like', "{$yearStr}%");
            $advancesQuery->where('advance_date', 'like', "{$yearStr}%");
        }

        $payments = $paymentsQuery->get()->map(function ($p) {
            return [
                'id' => 'pay-' . $p->id,
                'raw_date' => $p->payment_date ? $p->payment_date->format('Y-m-d') : $p->created_at->format('Y-m-d'),
                'date_formatted' => $p->payment_date ? $p->payment_date->format('d M Y') : $p->created_at->format('d M Y'),
                'type' => 'Salary Payment',
                'badge_class' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                'description' => "Salary for {$p->month_year} (" . number_format($p->days_present, 1) . " Days)",
                'gross_amount' => (float) $p->total_salary + (float) $p->advance_deduction,
                'advance_deductions' => (float) $p->advance_deduction,
                'net_amount' => (float) $p->total_salary,
                'payment_method' => $p->payment_method ?? 'Cash',
                'status' => strtoupper($p->status),
                'status_class' => $p->status === 'paid' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200',
                'notes' => $p->notes ?? '',
            ];
        });

        $advances = $advancesQuery->get()->map(function ($a) {
            return [
                'id' => 'adv-' . $a->id,
                'raw_date' => $a->advance_date ? $a->advance_date->format('Y-m-d') : $a->created_at->format('Y-m-d'),
                'date_formatted' => $a->advance_date ? $a->advance_date->format('d M Y') : $a->created_at->format('d M Y'),
                'type' => 'Salary Advance',
                'badge_class' => 'bg-amber-50 text-amber-700 border-amber-200',
                'description' => 'Advance Payout Issued',
                'gross_amount' => (float) $a->amount,
                'advance_deductions' => 0.00,
                'net_amount' => (float) $a->amount,
                'payment_method' => $a->payment_method ?? 'Cash',
                'status' => strtoupper($a->status),
                'status_class' => $a->status === 'deducted' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200',
                'notes' => $a->notes ?? '',
            ];
        });

        $transactions = $payments->concat($advances)->sortByDesc('raw_date')->values();

        return response()->json([
            'success' => true,
            'staff' => [
                'id' => $staff->id,
                'full_name' => $staff->full_name,
                'mobile_number' => $staff->mobile_number ?: 'N/A',
                'wage_type' => $staff->wage_type,
                'wage_type_label' => $staff->wage_type === 'per-day' ? 'Per Day Wage' : 'Fixed Salary',
                'is_active' => (bool) $staff->is_active,
            ],
            'selected_month' => $selectedMonth,
            'current_rate' => $rateAmount,
            'current_rate_formatted' => $staff->wage_type === 'per-day' ? '₹' . number_format($rateAmount, 2) . ' / day' : '₹' . number_format($rateAmount, 2) . ' / month',
            'days_present' => $daysPresent,
            'gross_earnings' => $grossEarnings,
            'pending_advances_total' => $pendingAdvanceTotal,
            'advance_deducted' => $advanceDeducted,
            'net_due_amount' => $netPaidOrDue,
            'payment_status' => $paymentStatus,
            'transactions' => $transactions,
        ]);
    }

    /**
     * Delete employee profile (AJAX).
     */
    public function deleteEmployee($id)
    {
        if ($res = RolePermissionService::authorizeAction(request(), 'action_delete')) {
            return $res;
        }

        $staff = StaffProfile::findOrFail($id);
        $name = $staff->full_name;
        $staff->delete();

        return response()->json([
            'success' => true,
            'message' => "Employee '{$name}' deleted successfully!",
        ]);
    }

    /**
     * Store Daily Attendance Sheet (Method 1).
     */
    public function storeAttendance(Request $request)
    {
        if ($res = RolePermissionService::authorizeAction($request, 'action_insert')) {
            return $res;
        }

        $validated = $request->validate([
            'date' => 'required|date',
            'attendance' => 'required|array',
            'attendance.*' => 'required|in:present,half_day,absent',
        ]);

        try {
            foreach ($validated['attendance'] as $staffId => $status) {
                AttendanceRecord::updateOrCreate(
                    [
                        'staff_profile_id' => $staffId,
                        'date' => $validated['date'],
                    ],
                    [
                        'status' => $status,
                    ]
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Daily attendance for '.Carbon::parse($validated['date'])->format('d M, Y').' saved successfully!',
            ]);
        } catch (Throwable $e) {
            Log::error('Failed to save attendance: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to save attendance. Please try again.',
            ], 500);
        }
    }

    /**
     * Get monthly attendance summary (JSON endpoint for Method 1 calculation).
     */
    public function getMonthlySummary(Request $request)
    {
        $month = $request->input('month', now()->format('Y-m'));

        $summary = AttendanceRecord::where('date', 'like', "{$month}%")
            ->get()
            ->groupBy('staff_profile_id')
            ->map(function ($records) {
                $presentCount = $records->where('status', 'present')->count();
                $halfDayCount = $records->where('status', 'half_day')->count();

                return $presentCount + ($halfDayCount * 0.5);
            });

        return response()->json([
            'success' => true,
            'month' => $month,
            'summary' => $summary,
        ]);
    }

    /**
     * Pay Salary & Auto-Log Expense (Method 1 & Method 2).
     */
    public function paySalary(Request $request)
    {
        if ($res = RolePermissionService::authorizeAction($request, 'action_insert')) {
            return $res;
        }

        $validated = $request->validate([
            'staff_profile_id' => 'required|exists:staff_profiles,id',
            'month_year' => 'required|string|max:7',
            'days_present' => 'nullable|numeric|min:0',
            'total_salary' => 'nullable|numeric|min:0',
            'advance_deduction' => 'nullable|numeric|min:0',
            'payment_status' => 'required|in:paid,pending',
            'payment_date' => 'nullable|date',
            'payment_method' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        try {
            $staff = StaffProfile::findOrFail($validated['staff_profile_id']);
            $daysPresent = (float) ($validated['days_present'] ?? 0);
            $paymentStatus = $validated['payment_status'];
            $paymentDate = $validated['payment_date'] ?? now()->toDateString();
            $paymentMethod = $validated['payment_method'] ?? 'Cash';
            $advanceDeduction = round((float) ($validated['advance_deduction'] ?? 0), 2);

            $rate = $staff->wage_type === 'per-day' ? (float) $staff->piece_rate_per_unit : (float) $staff->monthly_salary;

            if ($request->filled('total_salary')) {
                $totalSalary = round((float) $request->input('total_salary'), 2);
            } else {
                if ($staff->wage_type === 'per-day') {
                    $totalSalary = round(($daysPresent * $rate) - $advanceDeduction, 2);
                } else {
                    $totalSalary = round($rate - $advanceDeduction, 2);
                }
            }
            if ($totalSalary < 0) {
                $totalSalary = 0;
            }

            // Check if payment already exists
            $payment = SalaryPayment::where('staff_profile_id', $staff->id)
                ->where('month_year', $validated['month_year'])
                ->first();

            $expenseId = $payment ? $payment->expense_id : null;

            // If marking as PAID, create/update linked Expense entry in Expenses Ledger
            if ($paymentStatus === 'paid') {
                $expenseNotes = "Salary payment for {$staff->full_name} ({$validated['month_year']}) via {$paymentMethod}";
                if ($advanceDeduction > 0) {
                    $expenseNotes .= ' (Net ₹'.number_format($totalSalary, 2).' after ₹'.number_format($advanceDeduction, 2).' advance deduction)';
                }

                if ($expenseId) {
                    $expense = Expense::find($expenseId);
                    if ($expense) {
                        $expense->update([
                            'expense_date' => $paymentDate,
                            'amount' => $totalSalary,
                            'description' => $expenseNotes,
                        ]);
                    }
                }

                if (! $expenseId) {
                    $expense = Expense::create([
                        'expense_date' => $paymentDate,
                        'expense_category' => 'Employee Salary / Payroll',
                        'amount' => $totalSalary,
                        'description' => $expenseNotes,
                    ]);
                    $expenseId = $expense->id;
                }
            }

            $paymentRecord = SalaryPayment::updateOrCreate(
                [
                    'staff_profile_id' => $staff->id,
                    'month_year' => $validated['month_year'],
                ],
                [
                    'wage_type' => $staff->wage_type,
                    'rate_amount' => $rate,
                    'days_present' => $daysPresent,
                    'total_salary' => $totalSalary,
                    'advance_deduction' => $advanceDeduction,
                    'status' => $paymentStatus,
                    'payment_date' => $paymentDate,
                    'payment_method' => $paymentMethod,
                    'expense_id' => $expenseId,
                    'notes' => $validated['notes'] ?? null,
                ]
            );

            // Reconcile pending advances if paid and advance deduction applied
            if ($paymentStatus === 'paid' && $advanceDeduction > 0) {
                SalaryAdvance::where('staff_profile_id', $staff->id)
                    ->where('status', 'pending')
                    ->update([
                        'status' => 'deducted',
                        'salary_payment_id' => $paymentRecord->id,
                    ]);
            }

            return response()->json([
                'success' => true,
                'message' => $paymentStatus === 'paid'
                    ? 'Salary of ₹'.number_format($totalSalary, 2)." paid for '{$staff->full_name}' and posted to Expenses Ledger!"
                    : "Salary payment record updated as PENDING for '{$staff->full_name}'.",
                'data' => $paymentRecord,
            ]);
        } catch (Throwable $e) {
            Log::error('Failed to process salary payment: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to process salary payment. Please try again.',
            ], 500);
        }
    }

    /**
     * Delete Salary Payment Record.
     */
    public function deletePayment($id)
    {
        if ($res = RolePermissionService::authorizeAction(request(), 'action_delete')) {
            return $res;
        }

        try {
            $payment = SalaryPayment::findOrFail($id);
            if ($payment->expense_id) {
                Expense::where('id', $payment->expense_id)->delete();
            }
            $payment->delete();

            return response()->json([
                'success' => true,
                'message' => 'Salary payment record and linked expense deleted successfully!',
            ]);
        } catch (Throwable $e) {
            Log::error('Failed to delete salary payment: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete salary payment record.',
            ], 500);
        }
    }

    /**
     * Issue Salary Advance & Auto-Log Expense.
     */
    public function storeAdvance(Request $request)
    {
        if ($res = RolePermissionService::authorizeAction($request, 'action_insert')) {
            return $res;
        }

        $validated = $request->validate([
            'staff_profile_id' => 'required|exists:staff_profiles,id',
            'amount' => 'required|numeric|min:1',
            'advance_date' => 'required|date',
            'payment_method' => 'required|string',
            'notes' => 'nullable|string|max:255',
        ]);

        try {
            $staff = StaffProfile::findOrFail($validated['staff_profile_id']);
            $amount = (float) $validated['amount'];
            $advanceDate = $validated['advance_date'];
            $method = $validated['payment_method'];

            // Log Expense entry
            $expense = Expense::create([
                'expense_date' => $advanceDate,
                'expense_category' => 'Employee Salary Advance',
                'amount' => $amount,
                'description' => "Salary advance to {$staff->full_name}".(! empty($validated['notes']) ? " ({$validated['notes']})" : '')." via {$method}",
            ]);

            // Save Advance Record
            $advance = SalaryAdvance::create([
                'staff_profile_id' => $staff->id,
                'advance_date' => $advanceDate,
                'amount' => $amount,
                'payment_method' => $method,
                'status' => 'pending',
                'expense_id' => $expense->id,
                'notes' => $validated['notes'] ?? null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Salary advance of ₹'.number_format($amount, 2)." issued to '{$staff->full_name}' and posted to Expenses Ledger!",
                'data' => $advance,
            ]);
        } catch (Throwable $e) {
            Log::error('Failed to record salary advance: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to record salary advance: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete Salary Advance Record.
     */
    public function deleteAdvance(Request $request, $id)
    {
        if ($res = RolePermissionService::authorizeAction($request, 'action_delete')) {
            return $res;
        }

        try {
            $advance = SalaryAdvance::findOrFail($id);
            if ($advance->expense_id) {
                Expense::where('id', $advance->expense_id)->delete();
            }
            $advance->delete();

            return response()->json([
                'success' => true,
                'message' => 'Salary advance record deleted successfully.',
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete salary advance record.',
            ], 500);
        }
    }

    /**
     * Disburse payroll (AJAX legacy endpoint).
     */
    public function payPayroll(Request $request)
    {
        if ($res = RolePermissionService::authorizeAction($request, 'action_update')) {
            return $res;
        }

        $validated = $request->validate([
            'labor_log_ids' => 'required|array|min:1',
            'labor_log_ids.*' => 'required|exists:labor_logs,id',
        ]);

        try {
            $count = $this->payrollService->markWagesAsPaid($validated['labor_log_ids']);

            return response()->json([
                'success' => true,
                'message' => "Successfully paid compiled wages for {$count} logged runs!",
            ]);
        } catch (Exception $e) {
            Log::error('Failed to pay payroll: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'errors' => ['Failed to process payroll payment. Please try again.'],
            ], 500);
        }
    }
}
