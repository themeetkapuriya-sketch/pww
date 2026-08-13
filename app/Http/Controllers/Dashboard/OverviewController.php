<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\ProductionLog;
use App\Models\Purchase;
use App\Models\RawMaterial;
use App\Models\SalesOrder;
use App\Models\StaffProfile;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OverviewController extends Controller
{
    /**
     * 1. Overview Dashboard.
     */
    public function overview(Request $request)
    {
        $now = Carbon::now();

        // Today's Attendance Quick Data
        $todayDate = $now->toDateString();
        $allStaff = StaffProfile::all();
        $todayAttendance = AttendanceRecord::where('date', $todayDate)->get()->keyBy('staff_profile_id');

        // 1. Financial Year Range (April 1 to March 31)
        $fyStartYear = ($now->month >= 4) ? $now->year : ($now->year - 1);
        $fyStartDate = Carbon::create($fyStartYear, 4, 1)->startOfDay();
        $fyEndDate = Carbon::create($fyStartYear + 1, 3, 31)->endOfDay();

        // 2. Current Month Range
        $monthStart = $now->copy()->startOfMonth();
        $monthEnd = $now->copy()->endOfMonth();

        // Yearly Revenue & Taxable Base
        $yearlyStats = Invoice::where(function ($q) use ($fyStartDate, $fyEndDate) {
            $q->whereBetween('invoice_date', [$fyStartDate->toDateString(), $fyEndDate->toDateString()])
                ->orWhere(function ($sub) use ($fyStartDate, $fyEndDate) {
                    $sub->whereNull('invoice_date')->whereBetween('created_at', [$fyStartDate, $fyEndDate]);
                });
        })->selectRaw('SUM(total_amount) as total_rev, SUM(total_taxable_value) as total_tax')->first();

        $yearlyRevenue = (float) ($yearlyStats->total_rev ?? 0);
        $yearlyTaxable = (float) ($yearlyStats->total_tax ?? 0);

        // Monthly Revenue & Taxable Base
        $monthlyStats = Invoice::where(function ($q) use ($monthStart, $monthEnd) {
            $q->whereBetween('invoice_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                ->orWhere(function ($sub) use ($monthStart, $monthEnd) {
                    $sub->whereNull('invoice_date')->whereBetween('created_at', [$monthStart, $monthEnd]);
                });
        })->selectRaw('SUM(total_amount) as total_rev, SUM(total_taxable_value) as total_tax, COUNT(*) as cnt, SUM(cgst) as cgst_sum, SUM(sgst) as sgst_sum, SUM(igst) as igst_sum')->first();

        $monthlyRevenue = (float) ($monthlyStats->total_rev ?? 0);
        $monthlyTaxable = (float) ($monthlyStats->total_tax ?? 0);
        $monthlyInvoiceCount = (int) ($monthlyStats->cnt ?? 0);

        // Outstanding Receivables
        $totalReceivables = (float) DB::table('invoices')
            ->selectRaw('SUM(CASE WHEN total_amount > paid_amount THEN total_amount - paid_amount ELSE 0 END) as due')
            ->value('due') ?? 0;

        // Net GST Payable (Current Month)
        $salesGstCollected = (float) (($monthlyStats->cgst_sum ?? 0) + ($monthlyStats->sgst_sum ?? 0) + ($monthlyStats->igst_sum ?? 0));
        $purchasesItc = (float) Purchase::whereBetween('purchase_date', [$monthStart->toDateString(), $monthEnd->toDateString()])->sum('gst_amount');
        $currentMonthNetGst = round($salesGstCollected - $purchasesItc, 2);

        // Check Expense Ledger for GST Payment entry in Current Month
        $currentMonthGstExpense = Expense::whereIn('expense_category', ['gst_payment', 'tax_payment'])
            ->whereBetween('expense_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->orderBy('expense_date', 'desc')
            ->first();

        $currentMonthGstExpenseTotal = (float) Expense::whereIn('expense_category', ['gst_payment', 'tax_payment'])
            ->whereBetween('expense_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->sum('amount');

        if ($currentMonthNetGst <= 0) {
            $currentMonthGstPaid = true;
            $currentMonthGstStatus = 'no_due';
        } elseif ($currentMonthGstExpenseTotal >= $currentMonthNetGst || $currentMonthGstExpense !== null) {
            $currentMonthGstPaid = true;
            $currentMonthGstStatus = 'paid';
        } else {
            $currentMonthGstPaid = false;
            $currentMonthGstStatus = 'unpaid';
        }

        // Operational Metrics
        $activeOrdersCount = SalesOrder::whereIn('status', ['pending', 'confirmed', 'in_production', 'ready_for_dispatch'])->count();
        $monthlyExpensesTotal = Expense::whereBetween('expense_date', [$monthStart->toDateString(), $monthEnd->toDateString()])->sum('amount');
        $monthlyPurchasesTotalOnly = (float) Purchase::whereBetween('purchase_date', [$monthStart->toDateString(), $monthEnd->toDateString()])->sum('total_amount');
        $monthlyExpenses = round($monthlyExpensesTotal + $monthlyPurchasesTotalOnly, 2);
        $lowStockCount = RawMaterial::whereColumn('current_stock', '<=', 'safety_threshold')->count();

        // 3 Net Revenue Cards Metrics (Revenue = Total Sales - Purchases - Expenses)
        $lifetimeSales = (float) Invoice::sum('total_amount');
        $lifetimePurchases = (float) Purchase::sum('total_amount');
        $lifetimeExpenses = (float) Expense::sum('amount');
        $lifetimeRevenue = round($lifetimeSales - $lifetimePurchases - $lifetimeExpenses, 2);

        $fyPurchasesTotal = (float) Purchase::whereBetween('purchase_date', [$fyStartDate->toDateString(), $fyEndDate->toDateString()])->sum('total_amount');
        $fyExpensesTotal = (float) Expense::whereBetween('expense_date', [$fyStartDate->toDateString(), $fyEndDate->toDateString()])->sum('amount');
        $annualRevenue = round($yearlyRevenue - $fyPurchasesTotal - $fyExpensesTotal, 2);

        $monthlyExpensesTotalOnly = (float) $monthlyExpensesTotal;
        $monthlyNetRevenue = round($monthlyRevenue - $monthlyPurchasesTotalOnly - $monthlyExpensesTotalOnly, 2);

        // 6-Month Chart Data (Sales vs Expenses) - Batch Aggregated
        $chartMonths = [];
        $chartSalesData = [];
        $chartExpenseData = [];

        $sixMonthsAgo = $now->copy()->subMonths(5)->startOfMonth();
        $isSqlite = DB::getDriverName() === 'sqlite';

        $invDateSql = $isSqlite ? 'strftime("%Y-%m", COALESCE(invoice_date, created_at))' : 'DATE_FORMAT(COALESCE(invoice_date, created_at), "%Y-%m")';
        $expDateSql = $isSqlite ? 'strftime("%Y-%m", expense_date)' : 'DATE_FORMAT(expense_date, "%Y-%m")';
        $purDateSql = $isSqlite ? 'strftime("%Y-%m", purchase_date)' : 'DATE_FORMAT(purchase_date, "%Y-%m")';

        $invoiceChartData = DB::table('invoices')
            ->selectRaw("{$invDateSql} as m_key, SUM(total_amount) as total_sales")
            ->where(function ($q) use ($sixMonthsAgo, $monthEnd) {
                $q->whereBetween('invoice_date', [$sixMonthsAgo->toDateString(), $monthEnd->toDateString()])
                    ->orWhere(function ($sub) use ($sixMonthsAgo, $monthEnd) {
                        $sub->whereNull('invoice_date')->whereBetween('created_at', [$sixMonthsAgo, $monthEnd]);
                    });
            })
            ->groupBy('m_key')
            ->pluck('total_sales', 'm_key');

        $expenseChartData = DB::table('expenses')
            ->selectRaw("{$expDateSql} as m_key, SUM(amount) as total_exp")
            ->whereBetween('expense_date', [$sixMonthsAgo->toDateString(), $monthEnd->toDateString()])
            ->groupBy('m_key')
            ->pluck('total_exp', 'm_key');

        $purchaseChartData = DB::table('purchases')
            ->selectRaw("{$purDateSql} as m_key, SUM(total_amount) as total_pur")
            ->whereBetween('purchase_date', [$sixMonthsAgo->toDateString(), $monthEnd->toDateString()])
            ->groupBy('m_key')
            ->pluck('total_pur', 'm_key');

        for ($i = 5; $i >= 0; $i--) {
            $mStart = $now->copy()->subMonths($i)->startOfMonth();
            $mKey = $mStart->format('Y-m');
            $chartMonths[] = $mStart->format('M Y');

            $salesVal = (float) ($invoiceChartData[$mKey] ?? 0);
            $expVal = (float) ($expenseChartData[$mKey] ?? 0) + (float) ($purchaseChartData[$mKey] ?? 0);

            $chartSalesData[] = round($salesVal, 2);
            $chartExpenseData[] = round($expVal, 2);
        }

        // Top 5 Client Plants Revenue Breakdown (Plant-Wise)
        $topClientsData = DB::table('invoices')
            ->join('client_plants', 'invoices.plant_id', '=', 'client_plants.id')
            ->join('clients', 'client_plants.client_id', '=', 'clients.id')
            ->select(
                'clients.company_name',
                'client_plants.plant_name',
                DB::raw('SUM(invoices.total_amount) as sales')
            )
            ->groupBy('client_plants.id', 'clients.company_name', 'client_plants.plant_name')
            ->orderByDesc('sales')
            ->take(5)
            ->get()
            ->map(function ($item) {
                $displayName = $item->company_name;
                if (! empty($item->plant_name)) {
                    $displayName .= ' ('.$item->plant_name.')';
                }

                return [
                    'name' => $displayName,
                    'sales' => (float) $item->sales,
                ];
            });

        if ($topClientsData->isEmpty()) {
            $topClientsData = collect([
                ['name' => 'Main Plant', 'sales' => (float) Invoice::sum('total_amount')],
            ]);
        }

        // Recent Activity Feed
        $recentInvoices = Invoice::with(['plant.client', 'items.rawMaterial'])->orderBy('created_at', 'desc')->take(5)->get();
        $recentOrders = SalesOrder::with(['client', 'plant'])->orderBy('created_at', 'desc')->take(5)->get();
        $recentProductionLogs = ProductionLog::with('product')->orderBy('production_date', 'desc')->orderBy('id', 'desc')->take(5)->get();
        $latestPurchases = Purchase::with('rawMaterial')->orderBy('purchase_date', 'desc')->orderBy('id', 'desc')->take(5)->get();
        $latestExpenses = Expense::orderBy('expense_date', 'desc')->orderBy('id', 'desc')->take(5)->get();
        $lowStockMaterials = RawMaterial::whereColumn('current_stock', '<=', 'safety_threshold')->take(5)->get();
        if ($lowStockMaterials->isEmpty()) {
            $lowStockMaterials = RawMaterial::orderBy('current_stock', 'asc')->take(5)->get();
        }

        return view('pages.overview', compact(
            'yearlyRevenue', 'yearlyTaxable', 'fyStartYear',
            'monthlyRevenue', 'monthlyTaxable', 'monthlyInvoiceCount',
            'totalReceivables', 'currentMonthNetGst', 'salesGstCollected', 'purchasesItc',
            'currentMonthGstPaid', 'currentMonthGstStatus', 'currentMonthGstExpense', 'currentMonthGstExpenseTotal',
            'activeOrdersCount', 'monthlyExpenses', 'lowStockCount',
            'lifetimeSales', 'lifetimePurchases', 'lifetimeExpenses', 'lifetimeRevenue',
            'fyPurchasesTotal', 'fyExpensesTotal', 'annualRevenue',
            'monthlyPurchasesTotalOnly', 'monthlyExpensesTotalOnly', 'monthlyNetRevenue',
            'chartMonths', 'chartSalesData', 'chartExpenseData',
            'topClientsData', 'recentInvoices', 'recentOrders', 'recentProductionLogs', 'latestPurchases', 'latestExpenses', 'lowStockMaterials',
            'allStaff', 'todayAttendance', 'todayDate'
        ));
    }
}
