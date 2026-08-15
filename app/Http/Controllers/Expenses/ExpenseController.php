<?php

namespace App\Http\Controllers\Expenses;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Services\AuditLogService;
use App\Services\RolePermissionService;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    /**
     * 9. Operational Expenses.
     */
    public function expenses()
    {
        $expenses = Expense::orderBy('expense_date', 'desc')->orderBy('id', 'desc')->paginate(20);

        return view('pages.expenses', compact('expenses'));
    }

    /**
     * Log Expense (AJAX).
     */
    public function logExpense(Request $request)
    {
        if ($res = RolePermissionService::authorizeAction($request, 'action_insert')) {
            return $res;
        }

        $validated = $request->validate([
            'expense_category' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        if (\App\Services\FinancialYearService::isFinancialYearLocked($validated['expense_date'])) {
            $fy = \App\Services\FinancialYearService::getFinancialYearForDate($validated['expense_date']);

            return response()->json([
                'success' => false,
                'message' => "Financial Year {$fy} is LOCKED for tax audit compliance. Creating expenses in locked periods is disabled.",
                'errors' => ["Financial Year {$fy} is locked."],
            ], 422);
        }

        $duplicateExists = Expense::where('expense_category', $validated['expense_category'])
            ->where('amount', $validated['amount'])
            ->whereDate('expense_date', $validated['expense_date'])
            ->where(function ($q) use ($validated) {
                if (! empty($validated['description'])) {
                    $q->where('description', $validated['description']);
                } else {
                    $q->whereNull('description')->orWhere('description', '');
                }
            })
            ->exists();

        if ($duplicateExists) {
            return response()->json([
                'success' => false,
                'message' => 'An identical expense record already exists for this category, date, and amount!',
                'errors' => ['amount' => ['An identical expense record already exists for this category, date, and amount!']],
            ], 422);
        }

        $expense = Expense::create($validated);

        AuditLogService::log('Expenses', 'created', "Logged expense in category '{$expense->expense_category}' (Amount: ₹".number_format($expense->amount, 2).')');

        return response()->json([
            'success' => true,
            'message' => "Expense logged successfully in category '".str_replace('_', ' ', $expense->expense_category)."'!",
            'data' => $expense,
        ]);
    }

    /**
     * Update Expense (AJAX).
     */
    public function updateExpense(Request $request, $id)
    {
        if ($res = RolePermissionService::authorizeAction($request, 'action_update')) {
            return $res;
        }

        $validated = $request->validate([
            'expense_category' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        if (\App\Services\FinancialYearService::isFinancialYearLocked($validated['expense_date'])) {
            $fy = \App\Services\FinancialYearService::getFinancialYearForDate($validated['expense_date']);

            return response()->json([
                'success' => false,
                'message' => "Financial Year {$fy} is LOCKED for tax audit compliance. Updating expenses in locked periods is disabled.",
                'errors' => ["Financial Year {$fy} is locked."],
            ], 422);
        }

        try {
            $expense = Expense::findOrFail($id);
            $expense->update($validated);

            AuditLogService::log('Expenses', 'updated', "Updated expense entry in category '{$expense->expense_category}' to ₹".number_format($expense->amount, 2));

            return response()->json([
                'success' => true,
                'message' => 'Expense entry updated successfully!',
                'data' => $expense,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => ['Failed to update expense: '.$e->getMessage()],
            ], 500);
        }
    }

    /**
     * Delete Expense (AJAX).
     */
    public function deleteExpense($id)
    {
        if ($res = RolePermissionService::authorizeAction(request(), 'action_delete')) {
            return $res;
        }

        try {
            $expense = Expense::findOrFail($id);

            $expDate = $expense->expense_date ? $expense->expense_date->format('Y-m-d') : $expense->created_at->format('Y-m-d');
            if (\App\Services\FinancialYearService::isFinancialYearLocked($expDate)) {
                $fy = \App\Services\FinancialYearService::getFinancialYearForDate($expDate);

                return response()->json([
                    'success' => false,
                    'message' => "Financial Year {$fy} is LOCKED for tax audit compliance. Deleting expenses from locked periods is disabled.",
                    'errors' => ["Financial Year {$fy} is locked."],
                ], 422);
            }

            $cat = str_replace('_', ' ', $expense->expense_category);
            $amt = $expense->amount;
            $expense->delete();

            AuditLogService::log('Expenses', 'deleted', "Deleted expense record '{$cat}' (Amount: ₹".number_format($amt, 2).')');

            return response()->json([
                'success' => true,
                'message' => "Expense record ('{$cat}') deleted successfully!",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => ['Failed to delete expense: '.$e->getMessage()],
            ], 500);
        }
    }
}
