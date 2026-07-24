<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Expense;
use App\Models\LaborLog;
use App\Models\Purchase;
use App\Models\Client;
use App\Models\ClientPlant;
use App\Models\Payment;
use App\Models\ProductionLog;
use Carbon\Carbon;

class FinancialService
{
    /**
     * Calculate financial summary over a period.
     */
    public function getFinancialSummary($startDate = null, $endDate = null)
    {
        $start = $startDate ? Carbon::parse($startDate)->startOfDay() : Carbon::now()->subDays(30)->startOfDay();
        $end = $endDate ? Carbon::parse($endDate)->endOfDay() : Carbon::now()->endOfDay();

        // 1. Gross Revenue
        $revenue = Invoice::whereBetween('created_at', [$start, $end])->sum('total_taxable_value');

        // 2. Total COGS (Purchases + Production BOM Material Cost)
        $purchasesCogs = Purchase::whereBetween('purchase_date', [$start->toDateString(), $end->toDateString()])->sum('total_amount');
        
        $bomCogs = 0.00;
        $prodLogs = ProductionLog::with('product.bom.rawMaterial')
            ->where(function($q) use ($start, $end) {
                $q->whereBetween('production_date', [$start->toDateString(), $end->toDateString()])
                  ->orWhereBetween('created_at', [$start, $end]);
            })->get();
        foreach ($prodLogs as $pl) {
            $prod = $pl->product ?? $pl->finishedGood;
            if ($prod && $prod->bom) {
                foreach ($prod->bom as $bom) {
                    if ($bom->rawMaterial) {
                        $qtyNeeded = ($pl->quantity_manufactured + $pl->quantity_rejected) * $bom->required_quantity * (1 + ($bom->waste_percentage / 100));
                        $unitPrice = $bom->rawMaterial->average_purchase_price ?? $bom->rawMaterial->unit_price ?? 0;
                        $bomCogs += $qtyNeeded * $unitPrice;
                    }
                }
            }
        }
        $cogs = round($purchasesCogs + $bomCogs, 2);

        // 3. Direct Labor Wages
        $directWages = round(LaborLog::whereBetween('created_at', [$start, $end])->sum('calculated_payout'), 2);

        // 4. Overheads (Excluding Machinery Depreciation)
        $overheads = round(Expense::where('expense_category', '!=', 'machinery_depreciation')
            ->whereBetween('expense_date', [$start->toDateString(), $end->toDateString()])
            ->sum('amount'), 2);

        // 5. Machinery Depreciation
        $depreciation = round(Expense::where('expense_category', 'machinery_depreciation')
            ->whereBetween('expense_date', [$start->toDateString(), $end->toDateString()])
            ->sum('amount'), 2);

        // 6. Total Expenses
        $totalExpenses = round($cogs + $directWages + $overheads + $depreciation, 2);

        // 7. Net Profit
        $netProfit = round($revenue - $totalExpenses, 2);

        // Margin %
        $margin = $revenue > 0 ? round(($netProfit / $revenue) * 100, 2) : 0.00;

        // 8. Outstanding Receivables
        $invoices = Invoice::all();
        $outstandingReceivables = $invoices->sum(fn($inv) => $inv->remaining_balance);

        return [
            'revenue' => (float)$revenue,
            'cogs' => (float)$cogs,
            'direct_wages' => (float)$directWages,
            'overheads' => (float)$overheads,
            'depreciation' => (float)$depreciation,
            'total_expenses' => (float)$totalExpenses,
            'net_profit' => (float)$netProfit,
            'gross_profit_margin' => (float)$margin,
            'outstanding_receivables' => (float)$outstandingReceivables,
        ];
    }

    /**
     * Get Client Account Ledger (Statement of Account).
     */
    public function getClientLedger($clientId, $startDate = null, $endDate = null, $plantId = null)
    {
        $client = Client::with('plants')->findOrFail($clientId);
        $selectedPlant = $plantId ? ClientPlant::where('client_id', $clientId)->find($plantId) : null;

        $start = $startDate ? Carbon::parse($startDate)->startOfDay() : Carbon::parse('2020-01-01')->startOfDay();
        $end = $endDate ? Carbon::parse($endDate)->endOfDay() : Carbon::now()->endOfDay();

        // 1. Calculate opening balance prior to $start date
        $priorInvoicesQuery = Invoice::where(function ($q) use ($clientId, $selectedPlant) {
            if ($selectedPlant) {
                $q->where('plant_id', $selectedPlant->id);
            } else {
                $q->whereHas('plant', fn($p) => $p->where('client_id', $clientId));
            }
        })->where(function($q) use ($start) {
            $q->where('invoice_date', '<', $start->toDateString())
              ->orWhere(function($q2) use ($start) {
                  $q2->whereNull('invoice_date')->where('created_at', '<', $start);
              });
        });
        $priorInvoicesSum = $priorInvoicesQuery->sum('total_amount');

        $priorPaymentsQuery = Payment::where('client_id', $clientId)
            ->where('payment_type', 'received')
            ->where('payment_date', '<', $start->toDateString());

        if ($selectedPlant) {
            $priorPaymentsQuery->where(function($q) use ($selectedPlant) {
                $q->where('plant_id', $selectedPlant->id)
                  ->orWhereHas('invoice', fn($inv) => $inv->where('plant_id', $selectedPlant->id));
            });
        }
        $priorPaymentsSum = $priorPaymentsQuery->sum('amount');

        $openingBalance = max(0.00, round($priorInvoicesSum - $priorPaymentsSum, 2));

        // 2. Fetch invoices within date range
        $invoicesQuery = Invoice::with(['items.product', 'plant'])->where(function ($q) use ($clientId, $selectedPlant) {
            if ($selectedPlant) {
                $q->where('plant_id', $selectedPlant->id);
            } else {
                $q->whereHas('plant', fn($p) => $p->where('client_id', $clientId));
            }
        })->where(function($q) use ($start, $end) {
            $q->whereBetween('invoice_date', [$start->toDateString(), $end->toDateString()])
              ->orWhere(function($q2) use ($start, $end) {
                  $q2->whereNull('invoice_date')->whereBetween('created_at', [$start, $end]);
              });
        });
        $invoices = $invoicesQuery->get();

        // 3. Fetch payments within date range
        $paymentsQuery = Payment::where('client_id', $clientId)
            ->where('payment_type', 'received')
            ->whereBetween('payment_date', [$start->toDateString(), $end->toDateString()]);

        if ($selectedPlant) {
            $paymentsQuery->where(function($q) use ($selectedPlant) {
                $q->where('plant_id', $selectedPlant->id)
                  ->orWhereHas('invoice', fn($inv) => $inv->where('plant_id', $selectedPlant->id));
            });
        }
        $payments = $paymentsQuery->get();

        // 4. Merge transactions in chronological order
        $transactions = collect();

        foreach ($invoices as $inv) {
            $invDate = $inv->invoice_date ? $inv->invoice_date->format('Y-m-d') : $inv->created_at->format('Y-m-d');
            $plantName = $inv->plant->plant_name ?? 'Main Plant';

            $transactions->push((object)[
                'date' => $invDate,
                'created_at' => $inv->created_at,
                'type' => 'invoice',
                'reference' => $inv->invoice_number,
                'description' => "Tax Invoice #{$inv->invoice_number} ({$plantName})",
                'debit' => (float)$inv->total_amount, // Increases amount client owes
                'credit' => 0.00,
                'model' => $inv,
            ]);
        }

        foreach ($payments as $pay) {
            $transactions->push((object)[
                'date' => $pay->payment_date,
                'created_at' => $pay->created_at,
                'type' => 'payment',
                'reference' => $pay->payment_number,
                'description' => "Payment Received (" . strtoupper(str_replace('_', ' ', $pay->payment_method)) . ($pay->reference_number ? " - Ref: {$pay->reference_number}" : "") . ")",
                'debit' => 0.00,
                'credit' => (float)$pay->amount, // Decreases amount client owes
                'model' => $pay,
            ]);
        }

        // Sort by date ascending
        $sortedTransactions = $transactions->sortBy(fn($t) => $t->date . '_' . $t->created_at)->values();

        // 5. Calculate running balances
        $runningBalance = $openingBalance;
        $processedTransactions = $sortedTransactions->map(function ($tx) use (&$runningBalance) {
            $runningBalance = round($runningBalance + $tx->debit - $tx->credit, 2);
            return [
                'date' => $tx->date,
                'created_at' => $tx->created_at,
                'type' => $tx->type,
                'reference' => $tx->reference,
                'description' => $tx->description,
                'debit' => $tx->debit,
                'credit' => $tx->credit,
                'running_balance' => $runningBalance,
                'model' => $tx->model,
            ];
        });

        $totalInvoiced = $invoices->sum('total_amount');
        $totalReceived = $payments->sum('amount');
        $closingBalance = max(0.00, round($openingBalance + $totalInvoiced - $totalReceived, 2));

        return [
            'client' => $client,
            'selected_plant' => $selectedPlant,
            'startDate' => $start->format('Y-m-d'),
            'endDate' => $end->format('Y-m-d'),
            'opening_balance' => $openingBalance,
            'closing_balance' => $closingBalance,
            'total_invoiced' => $totalInvoiced,
            'total_received' => $totalReceived,
            'total_debit' => $totalInvoiced,
            'total_credit' => $totalReceived,
            'entries' => $processedTransactions,
            'transactions' => $processedTransactions,
        ];
    }
}
