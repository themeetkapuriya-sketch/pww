<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\Expense;
use App\Models\SalaryAdvance;
use App\Models\SalaryDisbursal;
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
     * 8. Employees Directory, Daily Attendance & Monthly Salary Disbursal Hub.
     */
    public function employees(Request $request)
    {
        $staffProfiles = StaffProfile::orderBy('full_name')->get();
        $selectedDate = $request->input('date', now()->toDateString());
        $selectedMonth = $request->input('month', now()->format('Y-m'));

        // Attendance for selected date
        $attendanceForDate = AttendanceRecord::where('date', $selectedDate)
            ->pluck('status', 'staff_profile_id')
            ->toArray();

        // Monthly Attendance summary (Calculates total present days per employee)
        $monthlyAttendance = AttendanceRecord::where('date', 'like', "{$selectedMonth}%")
            ->get()
            ->groupBy('staff_profile_id')
            ->map(function ($records) {
                $presentCount = $records->where('status', 'present')->count();
                $halfDayCount = $records->where('status', 'half_day')->count();

                return $presentCount + ($halfDayCount * 0.5);
            });

        // Salary disbursals for selected month
        $salaryDisbursals = SalaryDisbursal::where('month_year', $selectedMonth)
            ->with('staffProfile')
            ->get()
            ->keyBy('staff_profile_id');

        // Pending salary advances for each staff member
        $pendingAdvances = SalaryAdvance::where('status', 'pending')
            ->get()
            ->groupBy('staff_profile_id')
            ->map(function ($advances) {
                return $advances->sum('amount');
            });

        return view('pages.employees', compact(
            'staffProfiles',
            'selectedDate',
            'selectedMonth',
            'attendanceForDate',
            'monthlyAttendance',
            'salaryDisbursals',
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
     * Disburse Salary & Auto-Log Expense (Method 1 & Method 2).
     */
    public function disburseSalary(Request $request)
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

            // Check if disbursal already exists
            $disbursal = SalaryDisbursal::where('staff_profile_id', $staff->id)
                ->where('month_year', $validated['month_year'])
                ->first();

            $expenseId = $disbursal ? $disbursal->expense_id : null;

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

            $disbursalRecord = SalaryDisbursal::updateOrCreate(
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
                        'salary_disbursal_id' => $disbursalRecord->id,
                    ]);
            }

            return response()->json([
                'success' => true,
                'message' => $paymentStatus === 'paid'
                    ? 'Salary of ₹'.number_format($totalSalary, 2)." paid for '{$staff->full_name}' and posted to Expenses Ledger!"
                    : "Salary disbursal record updated as PENDING for '{$staff->full_name}'.",
                'data' => $disbursalRecord,
            ]);
        } catch (Throwable $e) {
            Log::error('Failed to process salary disbursal: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to process salary disbursal. Please try again.',
            ], 500);
        }
    }

    /**
     * Delete Salary Disbursal Record.
     */
    public function deleteDisbursal($id)
    {
        if ($res = RolePermissionService::authorizeAction(request(), 'action_delete')) {
            return $res;
        }

        try {
            $disbursal = SalaryDisbursal::findOrFail($id);
            if ($disbursal->expense_id) {
                Expense::where('id', $disbursal->expense_id)->delete();
            }
            $disbursal->delete();

            return response()->json([
                'success' => true,
                'message' => 'Salary disbursal record and linked expense deleted successfully!',
            ]);
        } catch (Throwable $e) {
            Log::error('Failed to delete salary disbursal: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete salary disbursal record.',
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
