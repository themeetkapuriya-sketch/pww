<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Purchase;
use App\Models\Payment;
use App\Models\Client;
use App\Models\ClientPlant;
use App\Models\ProductionLog;
use App\Models\LaborLog;
use App\Models\Expense;
use App\Models\RawMaterial;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class FinancialService
{
    /**
     * Get the financial summary across a date range.
     *
     * @param string|Carbon $startDate
     * @param string|Carbon $endDate
     * @return array
     */
    public function getFinancialSummary($startDate, $endDate): array
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        // 1. Total Invoiced Revenue (excl. Tax)
        $revenue = Invoice::whereBetween('created_at', [$start, $end])
            ->sum('total_taxable_value');

        // Total Gross Revenue (incl. Tax)
        $totalGrossRevenue = Invoice::whereBetween('created_at', [$start, $end])
            ->sum('total_amount');

        // 2. Cost of Consumed Raw Materials
        $productionLogs = ProductionLog::whereBetween('production_date', [$start, $end])
            ->with(['finishedGood.billOfMaterials.rawMaterial'])
            ->get();

        $cogs = 0.00;
        foreach ($productionLogs as $log) {
            $finishedGood = $log->finishedGood;
            if (!$finishedGood) {
                continue;
            }
            foreach ($finishedGood->billOfMaterials as $bom) {
                $rawMaterial = $bom->rawMaterial;
                if (!$rawMaterial) {
                    continue;
                }
                
                $wasteMultiplier = 1 + ($bom->waste_percentage / 100);
                $consumed = $log->quantity_manufactured * $bom->required_quantity * $wasteMultiplier;
                $cogs += $consumed * $rawMaterial->average_purchase_price;
            }
        }

        // 3. Direct Piece-Rate Wages Paid
        $directWages = LaborLog::whereBetween('created_at', [$start, $end])
            ->sum('calculated_payout');

        // 4. Logged Overheads (Expenses except machinery depreciation)
        $overheads = Expense::whereBetween('expense_date', [$start, $end])
            ->where('expense_category', '!=', 'machinery_depreciation')
            ->sum('amount');

        // 5. Scheduled Machinery Depreciation
        $depreciation = Expense::whereBetween('expense_date', [$start, $end])
            ->where('expense_category', 'machinery_depreciation')
            ->sum('amount');

        // Net Profit = Revenue - (COGS + Direct Wages + Overheads + Depreciation)
        $netProfit = $revenue - ($cogs + $directWages + $overheads + $depreciation);

        // Gross Profit Margin (%) = ((Revenue - COGS) / Revenue) * 100
        $grossProfit = $revenue - $cogs;
        $grossProfitMargin = $revenue > 0 ? ($grossProfit / $revenue) * 100 : 0.00;

        // Accounts Receivable (Outstanding Client Dues)
        $outstandingReceivables = (float) (Invoice::whereIn('payment_status', ['unpaid', 'partially_paid'])
            ->selectRaw('SUM(total_amount - paid_amount) as outstanding')
            ->value('outstanding') ?? 0.00);

        // Accounts Payable (Outstanding Vendor Dues)
        $outstandingPayables = Purchase::whereIn('payment_status', ['unpaid', 'partially_paid'])
            ->selectRaw('SUM(total_amount - paid_amount) as outstanding')
            ->value('outstanding') ?? 0.00;

        // Total Collections Received in date range
        $totalCollections = Payment::where('payment_type', 'received')
            ->whereBetween('payment_date', [$start, $end])
            ->sum('amount');

        // Bank vs Cash Collections in date range
        $bankCollections = Payment::where('payment_type', 'received')
            ->where('account_type', 'bank')
            ->whereBetween('payment_date', [$start, $end])
            ->sum('amount');

        $cashCollections = Payment::where('payment_type', 'received')
            ->where('account_type', 'cash')
            ->whereBetween('payment_date', [$start, $end])
            ->sum('amount');

        return [
            'revenue' => round($revenue, 2),
            'total_gross_revenue' => round($totalGrossRevenue, 2),
            'cogs' => round($cogs, 2),
            'direct_wages' => round($directWages, 2),
            'overheads' => round($overheads, 2),
            'depreciation' => round($depreciation, 2),
            'net_profit' => round($netProfit, 2),
            'gross_profit' => round($grossProfit, 2),
            'gross_profit_margin' => round($grossProfitMargin, 2),
            'outstanding_receivables' => round($outstandingReceivables, 2),
            'outstanding_payables' => round($outstandingPayables, 2),
            'total_collections' => round($totalCollections, 2),
            'bank_collections' => round($bankCollections, 2),
            'cash_collections' => round($cashCollections, 2),
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
        ];
    }

    /**
     * Compute chronological Client Account Ledger with running balance.
     *
     * @param int $clientId
     * @param string|null $startDate
     * @param string|null $endDate
     * @return array
     */
    public function getClientLedger(int $clientId, ?string $startDate = null, ?string $endDate = null, ?int $plantId = null): array
    {
        $client = Client::with('plants')->findOrFail($clientId);
        $selectedPlant = $plantId ? ClientPlant::where('client_id', $clientId)->find($plantId) : null;

        $start = $startDate ? Carbon::parse($startDate)->startOfDay() : Carbon::parse('2020-01-01')->startOfDay();
        $end = $endDate ? Carbon::parse($endDate)->endOfDay() : Carbon::now()->endOfDay();

        // 1. Calculate opening balance prior to $start date
        $priorInvoicesQuery = Invoice::where(function ($q) use ($clientId, $selectedPlant) {
            if ($selectedPlant) {
                $q->whereHas('deliveryChallan', fn($dc) => $dc->where('plant_id', $selectedPlant->id))
                  ->orWhereHas('deliveryChallans', fn($dc) => $dc->where('plant_id', $selectedPlant->id));
            } else {
                $q->whereHas('deliveryChallan', fn($dc) => $dc->where('client_id', $clientId))
                  ->orWhereHas('deliveryChallans', fn($dc) => $dc->where('client_id', $clientId));
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
                  ->orWhereHas('invoice.deliveryChallan', fn($dc) => $dc->where('plant_id', $selectedPlant->id))
                  ->orWhereHas('invoice.deliveryChallans', fn($dc) => $dc->where('plant_id', $selectedPlant->id));
            });
        }
        $priorPaymentsSum = $priorPaymentsQuery->sum('amount');

        $openingBalance = max(0.00, round($priorInvoicesSum - $priorPaymentsSum, 2));

        // 2. Fetch invoices within date range
        $invoicesQuery = Invoice::where(function ($q) use ($clientId, $selectedPlant) {
            if ($selectedPlant) {
                $q->whereHas('deliveryChallan', fn($dc) => $dc->where('plant_id', $selectedPlant->id))
                  ->orWhereHas('deliveryChallans', fn($dc) => $dc->where('plant_id', $selectedPlant->id));
            } else {
                $q->whereHas('deliveryChallan', fn($dc) => $dc->where('client_id', $clientId))
                  ->orWhereHas('deliveryChallans', fn($dc) => $dc->where('client_id', $clientId));
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
                  ->orWhereHas('invoice.deliveryChallan', fn($dc) => $dc->where('plant_id', $selectedPlant->id))
                  ->orWhereHas('invoice.deliveryChallans', fn($dc) => $dc->where('plant_id', $selectedPlant->id));
            });
        }
        $payments = $paymentsQuery->get();

        // 4. Merge into unified ledger timeline
        $entries = collect();

        foreach ($invoices as $inv) {
            $invDate = Carbon::parse($inv->invoice_date ?? $inv->created_at)->toDateString();
            $entries->push([
                'type' => 'invoice',
                'date' => $invDate,
                'created_at' => $inv->created_at->toDateTimeString(),
                'reference' => $inv->invoice_number,
                'description' => 'Sales Invoice #' . $inv->invoice_number,
                'debit' => (float)$inv->total_amount, // Amount billed to client (+)
                'credit' => 0.00,
                'model' => $inv,
            ]);
        }

        foreach ($payments as $pay) {
            $entries->push([
                'type' => 'payment',
                'date' => $pay->payment_date->toDateString(),
                'created_at' => $pay->created_at->toDateTimeString(),
                'reference' => $pay->payment_number,
                'description' => 'Payment Received (' . $pay->formatted_method . ' - Ref: ' . ($pay->reference_number ?? 'N/A') . ')',
                'debit' => 0.00,
                'credit' => (float)$pay->amount, // Payment received from client (-)
                'model' => $pay,
            ]);
        }

        // Sort chronologically
        $sortedEntries = $entries->sortBy(function ($item) {
            return $item['date'] . ' ' . $item['created_at'];
        })->values();

        // Compute running balance
        $runningBalance = $openingBalance;
        $totalDebit = 0.00;
        $totalCredit = 0.00;

        $ledgerRows = [];
        foreach ($sortedEntries as $row) {
            $runningBalance += ($row['debit'] - $row['credit']);
            $totalDebit += $row['debit'];
            $totalCredit += $row['credit'];

            $row['running_balance'] = round($runningBalance, 2);
            $ledgerRows[] = $row;
        }

        return [
            'client' => $client,
            'selected_plant' => $selectedPlant,
            'opening_balance' => round($openingBalance, 2),
            'closing_balance' => round($runningBalance, 2),
            'total_debit' => round($totalDebit, 2),
            'total_credit' => round($totalCredit, 2),
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'entries' => $ledgerRows,
        ];
    }
}
