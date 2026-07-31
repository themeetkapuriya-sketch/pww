<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RawMaterial;
use App\Models\Product;
use App\Models\Invoice;
use App\Models\Expense;
use App\Models\Purchase;
use App\Models\SalesOrder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

use App\Models\ProductionLog;
use App\Models\StaffProfile;
use App\Models\AttendanceRecord;

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
        $yearlyInvoices = Invoice::where(function($q) use ($fyStartDate, $fyEndDate) {
            $q->whereBetween('invoice_date', [$fyStartDate->toDateString(), $fyEndDate->toDateString()])
              ->orWhere(function($sub) use ($fyStartDate, $fyEndDate) {
                  $sub->whereNull('invoice_date')->whereBetween('created_at', [$fyStartDate, $fyEndDate]);
              });
        })->get();
        $yearlyRevenue = (float)$yearlyInvoices->sum('total_amount');
        $yearlyTaxable = (float)$yearlyInvoices->sum('total_taxable_value');

        // Monthly Revenue & Taxable Base
        $monthlyInvoices = Invoice::where(function($q) use ($monthStart, $monthEnd) {
            $q->whereBetween('invoice_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
              ->orWhere(function($sub) use ($monthStart, $monthEnd) {
                  $sub->whereNull('invoice_date')->whereBetween('created_at', [$monthStart, $monthEnd]);
              });
        })->get();
        $monthlyRevenue = (float)$monthlyInvoices->sum('total_amount');
        $monthlyTaxable = (float)$monthlyInvoices->sum('total_taxable_value');
        $monthlyInvoiceCount = $monthlyInvoices->count();

        // Outstanding Receivables
        $allInvoices = Invoice::all();
        $totalReceivables = (float)$allInvoices->sum(fn($inv) => $inv->remaining_balance);

        // Net GST Payable (Current Month)
        $salesGstCollected = $monthlyInvoices->sum('cgst') + $monthlyInvoices->sum('sgst') + $monthlyInvoices->sum('igst');
        $monthlyPurchases = Purchase::whereBetween('purchase_date', [$monthStart->toDateString(), $monthEnd->toDateString()])->get();
        $purchasesItc = $monthlyPurchases->sum('gst_amount');
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
        } else if ($currentMonthGstExpenseTotal >= $currentMonthNetGst || $currentMonthGstExpense !== null) {
            $currentMonthGstPaid = true;
            $currentMonthGstStatus = 'paid';
        } else {
            $currentMonthGstPaid = false;
            $currentMonthGstStatus = 'unpaid';
        }

        // Operational Metrics
        $activeOrdersCount = SalesOrder::whereIn('status', ['pending', 'confirmed', 'in_production', 'ready_for_dispatch'])->count();
        $monthlyExpensesTotal = Expense::whereBetween('expense_date', [$monthStart->toDateString(), $monthEnd->toDateString()])->sum('amount');
        $monthlyPurchasesTotal = $monthlyPurchases->sum('total_amount');
        $monthlyExpenses = round($monthlyExpensesTotal + $monthlyPurchasesTotal, 2);
        $lowStockCount = RawMaterial::whereColumn('current_stock', '<=', 'safety_threshold')->count();

        // 3 Net Revenue Cards Metrics (Revenue = Total Sales - Purchases - Expenses)
        $lifetimeSales = (float) Invoice::sum('total_amount');
        $lifetimePurchases = (float) Purchase::sum('total_amount');
        $lifetimeExpenses = (float) Expense::sum('amount');
        $lifetimeRevenue = round($lifetimeSales - $lifetimePurchases - $lifetimeExpenses, 2);

        $fyPurchasesTotal = (float) Purchase::whereBetween('purchase_date', [$fyStartDate->toDateString(), $fyEndDate->toDateString()])->sum('total_amount');
        $fyExpensesTotal = (float) Expense::whereBetween('expense_date', [$fyStartDate->toDateString(), $fyEndDate->toDateString()])->sum('amount');
        $annualRevenue = round($yearlyRevenue - $fyPurchasesTotal - $fyExpensesTotal, 2);

        $monthlyPurchasesTotalOnly = (float) $monthlyPurchases->sum('total_amount');
        $monthlyExpensesTotalOnly = (float) Expense::whereBetween('expense_date', [$monthStart->toDateString(), $monthEnd->toDateString()])->sum('amount');
        $monthlyNetRevenue = round($monthlyRevenue - $monthlyPurchasesTotalOnly - $monthlyExpensesTotalOnly, 2);

        // 6-Month Chart Data (Sales vs Expenses)
        $chartMonths = [];
        $chartSalesData = [];
        $chartExpenseData = [];

        for ($i = 5; $i >= 0; $i--) {
            $mStart = $now->copy()->subMonths($i)->startOfMonth();
            $mEnd = $now->copy()->subMonths($i)->endOfMonth();
            $chartMonths[] = $mStart->format('M Y');

            $salesVal = Invoice::where(function($q) use ($mStart, $mEnd) {
                $q->whereBetween('invoice_date', [$mStart->toDateString(), $mEnd->toDateString()])
                  ->orWhere(function($sub) use ($mStart, $mEnd) {
                      $sub->whereNull('invoice_date')->whereBetween('created_at', [$mStart, $mEnd]);
                  });
            })->sum('total_amount');

            $expVal = Expense::whereBetween('expense_date', [$mStart->toDateString(), $mEnd->toDateString()])->sum('amount') +
                      Purchase::whereBetween('purchase_date', [$mStart->toDateString(), $mEnd->toDateString()])->sum('total_amount');

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
            ->map(function($item) {
                $displayName = $item->company_name;
                if (!empty($item->plant_name)) {
                    $displayName .= ' (' . $item->plant_name . ')';
                }
                return [
                    'name' => $displayName,
                    'sales' => (float)$item->sales,
                ];
            });

        if ($topClientsData->isEmpty()) {
            $topClientsData = collect([
                ['name' => 'Main Plant', 'sales' => (float)Invoice::sum('total_amount')]
            ]);
        }

        // Recent Activity Feed
        $recentInvoices = Invoice::with('plant.client')->orderBy('created_at', 'desc')->take(5)->get();
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
