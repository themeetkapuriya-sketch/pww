<?php

namespace App\Http\Controllers\Expenses;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Expense;

class ExpenseController extends Controller
{
    /**
     * 9. Operational Expenses.
     */
    public function expenses()
    {
        $expenses = Expense::orderBy('expense_date', 'desc')->paginate(20);
        return view('pages.expenses', compact('expenses'));
    }

    /**
     * Log Expense (AJAX).
     */
    public function logExpense(Request $request)
    {
        $validated = $request->validate([
            'expense_category' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        $duplicateExists = Expense::where('expense_category', $validated['expense_category'])
            ->where('amount', $validated['amount'])
            ->whereDate('expense_date', $validated['expense_date'])
            ->where(function($q) use ($validated) {
                if (!empty($validated['description'])) {
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
                'errors' => ['amount' => ['An identical expense record already exists for this category, date, and amount!']]
            ], 422);
        }

        $expense = Expense::create($validated);

        return response()->json([
            'success' => true,
            'message' => "Expense logged successfully in category '" . str_replace('_', ' ', $expense->expense_category) . "'!",
            'data' => $expense
        ]);
    }

    /**
     * Update Expense (AJAX).
     */
    public function updateExpense(Request $request, $id)
    {
        $validated = $request->validate([
            'expense_category' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'expense_date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        try {
            $expense = Expense::findOrFail($id);
            $expense->update($validated);

            return response()->json([
                'success' => true,
                'message' => "Expense entry updated successfully!",
                'data' => $expense
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => ['Failed to update expense: ' . $e->getMessage()]
            ], 500);
        }
    }

    /**
     * Delete Expense (AJAX).
     */
    public function deleteExpense($id)
    {
        try {
            $expense = Expense::findOrFail($id);
            $cat = str_replace('_', ' ', $expense->expense_category);
            $expense->delete();

            return response()->json([
                'success' => true,
                'message' => "Expense record ('{$cat}') deleted successfully!"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => ['Failed to delete expense: ' . $e->getMessage()]
            ], 500);
        }
    }
}
