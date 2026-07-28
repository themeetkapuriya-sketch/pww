<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StaffProfile;
use App\Services\PayrollService;
use Exception;

class EmployeeController extends Controller
{
    protected $payrollService;

    public function __construct(PayrollService $payrollService)
    {
        $this->payrollService = $payrollService;
    }

    /**
     * 8. Employees Directory.
     */
    public function employees()
    {
        $staffProfiles = StaffProfile::orderBy('full_name')->paginate(20);
        return view('pages.employees', compact('staffProfiles'));
    }

    /**
     * Create Employee Profile (AJAX).
     */
    public function storeEmployee(Request $request)
    {
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
                'errors' => ['full_name' => ["An employee profile for '{$validated['full_name']}' already exists!"]]
            ], 422);
        }

        $staff = StaffProfile::create($validated);

        return response()->json([
            'success' => true,
            'message' => "Employee profile for '{$staff->full_name}' created successfully!",
            'data' => $staff
        ]);
    }

    /**
     * Update employee profile (AJAX).
     */
    public function updateEmployee(Request $request, $id)
    {
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
            'data' => $staff
        ]);
    }

    /**
     * Delete employee profile (AJAX).
     */
    public function deleteEmployee($id)
    {
        $staff = StaffProfile::findOrFail($id);
        $name = $staff->full_name;
        $staff->delete();

        return response()->json([
            'success' => true,
            'message' => "Employee '{$name}' deleted successfully!"
        ]);
    }

    /**
     * Disburse payroll (AJAX).
     */
    public function payPayroll(Request $request)
    {
        $validated = $request->validate([
            'labor_log_ids' => 'required|array|min:1',
            'labor_log_ids.*' => 'required|exists:labor_logs,id',
        ]);

        try {
            $count = $this->payrollService->markWagesAsPaid($validated['labor_log_ids']);
            return response()->json([
                'success' => true,
                'message' => "Successfully paid compiled wages for {$count} logged runs!"
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => [$e->getMessage()]
            ], 500);
        }
    }
}
