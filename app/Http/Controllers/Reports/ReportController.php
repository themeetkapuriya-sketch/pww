<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Purchase;
use App\Services\FinancialService;
use App\Services\InvoicePdfService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    protected $financialService;

    protected $pdfService;

    public function __construct(FinancialService $financialService, InvoicePdfService $pdfService)
    {
        $this->financialService = $financialService;
        $this->pdfService = $pdfService;
    }

    private function getDateRange(Request $request)
    {
        $reportType = $request->input('report_type', 'invoice');
        $defaultPeriod = ($reportType === 'gst') ? 'month' : 'all';

        $period = $request->input('filter_period', $defaultPeriod);

        if ($request->has('start_date') && $request->has('end_date') && ! $request->has('filter_period')) {
            $period = 'custom';
        }

        $filterMonth = $request->input('filter_month', Carbon::now()->format('Y-m'));
        $filterYear = $request->input('filter_year', date('Y'));

        switch ($period) {
            case 'all':
                $startDate = '2026-04-01';
                $endDate = Carbon::now()->toDateString();
                break;
            case 'month':
                try {
                    $monthCarbon = Carbon::parse($filterMonth.'-01');
                    $startDate = $monthCarbon->startOfMonth()->toDateString();
                    $endDate = $monthCarbon->endOfMonth()->toDateString();
                } catch (\Exception $e) {
                    $startDate = Carbon::now()->startOfMonth()->toDateString();
                    $endDate = Carbon::now()->endOfMonth()->toDateString();
                }
                break;
            case 'year':
                $now = Carbon::now();
                $fyStartYear = ($now->month >= 4) ? $now->year : ($now->year - 1);
                $targetYear = (int) $request->input('filter_year', $fyStartYear);
                $startDate = Carbon::create($targetYear, 4, 1)->toDateString();
                $endDate = Carbon::create($targetYear + 1, 3, 31)->toDateString();
                break;
            case 'custom':
            default:
                $startDate = $request->input('start_date', Carbon::now()->subDays(30)->toDateString());
                $endDate = $request->input('end_date', Carbon::now()->toDateString());
                break;
        }

        return [$startDate, $endDate, $period, $filterMonth, $filterYear];
    }

    /**
     * 10. Reports Page.
     */
    public function reports(Request $request)
    {
        [$startDate, $endDate, $period, $filterMonth, $filterYear] = $this->getDateRange($request);
        $reportType = $request->input('report_type', 'invoice');

        // 1. Fetch Invoices
        $invoices = Invoice::with(['plant.client', 'items.product'])
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('invoice_date', [$startDate, $endDate])
                    ->orWhere(function ($sub) use ($startDate, $endDate) {
                        $sub->whereNull('invoice_date')
                            ->whereBetween('created_at', [Carbon::parse($startDate)->startOfDay(), Carbon::parse($endDate)->endOfDay()]);
                    });
            })
            ->orderBy('invoice_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        // 2. Fetch Purchases
        $purchases = Purchase::whereBetween('purchase_date', [$startDate, $endDate])
            ->orderBy('purchase_date', 'desc')
            ->get();

        // 3. Fetch Expenses
        $expenses = Expense::whereBetween('expense_date', [$startDate, $endDate])
            ->orderBy('expense_date', 'desc')
            ->get();

        // 4. Fetch Financials
        $financials = $this->financialService->getFinancialSummary($startDate, $endDate);

        // 5. Calculate Summaries
        $invoiceSummary = [
            'total_taxable' => $invoices->sum('total_taxable_value'),
            'total_cgst' => $invoices->sum('cgst'),
            'total_sgst' => $invoices->sum('sgst'),
            'total_igst' => $invoices->sum('igst'),
            'total_amount' => $invoices->sum('total_amount'),
            'total_due' => $invoices->sum(fn ($inv) => $inv->remaining_balance),
        ];
        $invoiceSummary['total_gst'] = $invoiceSummary['total_cgst'] + $invoiceSummary['total_sgst'] + $invoiceSummary['total_igst'];

        $purchaseSummary = [
            'total_spent' => $purchases->sum('total_amount'),
            'total_gst' => $purchases->sum('gst_amount'),
            'total_raw_material' => $purchases->where('purchase_type', 'raw_material')->sum('total_amount'),
            'total_machinery' => $purchases->where('purchase_type', 'machinery')->sum('total_amount'),
            'total_supplies' => $purchases->where('purchase_type', 'supplies')->sum('total_amount'),
        ];

        $expenseSummary = [
            'total_spent' => $expenses->sum('amount'),
            'total_count' => $expenses->count(),
            'by_category' => $expenses->groupBy('expense_category')->map(function ($group) {
                return $group->sum('amount');
            }),
        ];

        $gstSummary = [
            'sales_cgst' => $invoiceSummary['total_cgst'],
            'sales_sgst' => $invoiceSummary['total_sgst'],
            'sales_igst' => $invoiceSummary['total_igst'],
            'sales_total_gst' => $invoiceSummary['total_gst'],
            'purchase_total_gst' => $purchaseSummary['total_gst'],
        ];
        $gstSummary['net_gst_payable'] = round($gstSummary['sales_total_gst'] - $gstSummary['purchase_total_gst'], 2);

        $matchingGstExpenses = Expense::whereIn('expense_category', ['gst_payment', 'tax_payment'])
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->orderBy('expense_date', 'desc')
            ->get();

        $gstTotalPaid = (float) $matchingGstExpenses->sum('amount');
        $gstExpenseEntry = $matchingGstExpenses->first();

        if ($gstSummary['net_gst_payable'] <= 0) {
            $gstSummary['is_paid'] = true;
            $gstSummary['status'] = 'no_due';
            $gstSummary['status_label'] = 'ITC CREDIT (₹0 TAX DUE)';
            $gstSummary['expense_entry'] = null;
            $gstSummary['total_paid'] = 0.00;
        } elseif ($gstTotalPaid >= $gstSummary['net_gst_payable']) {
            $gstSummary['is_paid'] = true;
            $gstSummary['status'] = 'paid';
            $gstSummary['status_label'] = 'PAID via Expense Ledger';
            $gstSummary['expense_entry'] = $gstExpenseEntry;
            $gstSummary['total_paid'] = $gstTotalPaid;
        } else {
            $gstSummary['is_paid'] = false;
            $gstSummary['status'] = 'unpaid';
            $gstSummary['status_label'] = 'UNPAID (Pending Payment)';
            $gstSummary['expense_entry'] = null;
            $gstSummary['total_paid'] = 0.00;
        }

        $lifetimeSales = (float) Invoice::sum('total_amount');
        $lifetimePurchases = (float) Purchase::sum('total_amount');
        $lifetimeExpenses = (float) Expense::sum('amount');
        $lifetimeRevenue = round($lifetimeSales - $lifetimePurchases - $lifetimeExpenses, 2);

        $now = Carbon::now();
        $fyStartYear = ($now->month >= 4) ? $now->year : ($now->year - 1);
        $fyStartDate = Carbon::create($fyStartYear, 4, 1)->startOfDay();
        $fyEndDate = Carbon::create($fyStartYear + 1, 3, 31)->endOfDay();
        $fySales = (float) Invoice::where(function ($q) use ($fyStartDate, $fyEndDate) {
            $q->whereBetween('invoice_date', [$fyStartDate->toDateString(), $fyEndDate->toDateString()])
                ->orWhere(function ($sub) use ($fyStartDate, $fyEndDate) {
                    $sub->whereNull('invoice_date')->whereBetween('created_at', [$fyStartDate, $fyEndDate]);
                });
        })->sum('total_amount');
        $fyPurchases = (float) Purchase::whereBetween('purchase_date', [$fyStartDate->toDateString(), $fyEndDate->toDateString()])->sum('total_amount');
        $fyExpenses = (float) Expense::whereBetween('expense_date', [$fyStartDate->toDateString(), $fyEndDate->toDateString()])->sum('amount');
        $annualRevenue = round($fySales - $fyPurchases - $fyExpenses, 2);

        $mStart = $now->copy()->startOfMonth();
        $mEnd = $now->copy()->endOfMonth();
        $mSales = (float) Invoice::where(function ($q) use ($mStart, $mEnd) {
            $q->whereBetween('invoice_date', [$mStart->toDateString(), $mEnd->toDateString()])
                ->orWhere(function ($sub) use ($mStart, $mEnd) {
                    $sub->whereNull('invoice_date')->whereBetween('created_at', [$mStart, $mEnd]);
                });
        })->sum('total_amount');
        $mPurchases = (float) Purchase::whereBetween('purchase_date', [$mStart->toDateString(), $mEnd->toDateString()])->sum('total_amount');
        $mExpenses = (float) Expense::whereBetween('expense_date', [$mStart->toDateString(), $mEnd->toDateString()])->sum('amount');
        $monthlyNetRevenue = round($mSales - $mPurchases - $mExpenses, 2);

        return view('pages.reports', compact(
            'startDate', 'endDate', 'period', 'reportType',
            'invoices', 'purchases', 'expenses', 'financials',
            'invoiceSummary', 'purchaseSummary', 'expenseSummary', 'gstSummary',
            'lifetimeRevenue', 'annualRevenue', 'monthlyNetRevenue',
            'filterMonth', 'filterYear'
        ));
    }

    /**
     * Export Reports Data to CSV.
     */
    public function exportCsv(Request $request)
    {
        [$startDate, $endDate, $period, $filterMonth, $filterYear] = $this->getDateRange($request);
        $reportType = $request->input('report_type', 'invoice');

        $response = new StreamedResponse(function () use ($startDate, $endDate, $reportType, $request) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM

            if ($reportType === 'invoice') {
                $invoices = Invoice::with(['plant.client'])
                    ->where(function ($q) use ($startDate, $endDate) {
                        $q->whereBetween('invoice_date', [$startDate, $endDate])
                            ->orWhere(function ($sub) use ($startDate, $endDate) {
                                $sub->whereNull('invoice_date')
                                    ->whereBetween('created_at', [Carbon::parse($startDate)->startOfDay(), Carbon::parse($endDate)->endOfDay()]);
                            });
                    })
                    ->orderBy('invoice_date', 'desc')
                    ->get();

                fputcsv($handle, ['PRAFUL WELDING WORKS - SALES INVOICES AUDIT REPORT']);
                fputcsv($handle, ['Period:', $startDate, 'to', $endDate]);
                fputcsv($handle, []);
                fputcsv($handle, ['Invoice No.', 'Client Company', 'Plant Name', 'Invoice Date', 'Taxable Value (INR)', 'CGST (9%)', 'SGST (9%)', 'IGST (18%)', 'Total Bill Amount (INR)', 'Due Amount (INR)', 'Payment Status']);

                foreach ($invoices as $inv) {
                    $isRm = ($inv->invoice_mode === 'raw_material' || str_starts_with($inv->invoice_number, 'RMS-'));
                    fputcsv($handle, [
                        $isRm ? 'NILL' : $inv->invoice_number,
                        $isRm ? ($inv->custom_client_name ?? 'Direct Buyer') : ($inv->plant->client->company_name ?? 'N/A'),
                        $isRm ? 'Raw Material Sale' : ($inv->plant->plant_name ?? 'HQ'),
                        Carbon::parse($inv->invoice_date ?? $inv->created_at)->format('d/m/Y'),
                        $inv->total_taxable_value,
                        $inv->cgst,
                        $inv->sgst,
                        $inv->igst,
                        $inv->total_amount,
                        $inv->remaining_balance,
                        strtoupper($inv->payment_status ?? 'unpaid'),
                    ]);
                }
            } elseif ($reportType === 'purchase') {
                $purchases = Purchase::whereBetween('purchase_date', [$startDate, $endDate])
                    ->orderBy('purchase_date', 'desc')
                    ->get();

                fputcsv($handle, ['PRAFUL WELDING WORKS - PURCHASE LEDGER REPORT']);
                fputcsv($handle, ['Period:', $startDate, 'to', $endDate]);
                fputcsv($handle, []);
                fputcsv($handle, ['Purchase Date', 'Bill No.', 'Vendor Name', 'Category', 'Item Description', 'Quantity', 'Unit', 'GST Rate (%)', 'GST Amount (INR)', 'Total Amount (INR)', 'Payment Status']);

                foreach ($purchases as $pur) {
                    fputcsv($handle, [
                        Carbon::parse($pur->purchase_date)->format('d/m/Y'),
                        $pur->bill_number ?? 'N/A',
                        $pur->vendor_name,
                        ucwords(str_replace('_', ' ', $pur->purchase_type)),
                        $pur->item_name,
                        $pur->quantity,
                        $pur->unit,
                        $pur->gst_rate,
                        $pur->gst_amount,
                        $pur->total_amount,
                        strtoupper($pur->payment_status ?? 'paid'),
                    ]);
                }
            } elseif ($reportType === 'financial') {
                $financials = $this->financialService->getFinancialSummary($startDate, $endDate);

                fputcsv($handle, ['PRAFUL WELDING WORKS - STATEMENT OF PROFIT & LOSS']);
                fputcsv($handle, ['Period:', $startDate, 'to', $endDate]);
                fputcsv($handle, []);
                fputcsv($handle, ['Line Item', 'Accounting Description', 'Amount (INR)']);
                fputcsv($handle, ['Total Billed Sales (A)', 'Total invoiced amounts (incl. GST)', $financials['revenue']]);
                fputcsv($handle, ['Total Purchases (B)', 'Raw material, machinery, tools, and vendor purchases', $financials['purchases']]);
                fputcsv($handle, ['Total Expenses (C)', 'Operational overheads, salaries, rent, transport', $financials['expenses']]);
                fputcsv($handle, ['NET REVENUE / PROFIT', 'Calculation: Total Sales - Purchases - Expenses', $financials['net_profit']]);
                fputcsv($handle, ['Gross Profit Margin (%)', 'Margin Ratio', $financials['gross_profit_margin'].'%']);
            } elseif ($reportType === 'expense') {
                $expenses = Expense::whereBetween('expense_date', [$startDate, $endDate])
                    ->orderBy('expense_date', 'desc')
                    ->get();

                fputcsv($handle, ['PRAFUL WELDING WORKS - EXPENSES AUDIT REPORT']);
                fputcsv($handle, ['Period:', $startDate, 'to', $endDate]);
                fputcsv($handle, []);
                fputcsv($handle, ['Expense Date', 'Expense Category', 'Memo Description', 'Amount (INR)']);

                foreach ($expenses as $exp) {
                    fputcsv($handle, [
                        Carbon::parse($exp->expense_date)->format('d/m/Y'),
                        ucwords(str_replace('_', ' ', $exp->expense_category)),
                        $exp->description ?? 'N/A',
                        $exp->amount,
                    ]);
                }
            } elseif ($reportType === 'gst') {
                $gstType = $request->input('gst_type', 'gstr3b');

                if ($gstType === 'gstr1') {
                    $invoices = Invoice::with(['plant.client'])
                        ->where(function ($q) use ($startDate, $endDate) {
                            $q->whereBetween('invoice_date', [$startDate, $endDate])
                                ->orWhere(function ($sub) use ($startDate, $endDate) {
                                    $sub->whereNull('invoice_date')
                                        ->whereBetween('created_at', [Carbon::parse($startDate)->startOfDay(), Carbon::parse($endDate)->endOfDay()]);
                                });
                        })
                        ->orderBy('invoice_date', 'desc')
                        ->get();

                    fputcsv($handle, ['PRAFUL WELDING WORKS - GSTR-1 OUTWARD SALES RETURN']);
                    fputcsv($handle, ['Period:', $startDate, 'to', $endDate]);
                    fputcsv($handle, []);
                    fputcsv($handle, ['Invoice No.', 'Client GSTIN', 'Client Company', 'Plant Name', 'Invoice Date', 'Taxable Value (INR)', 'CGST (9%)', 'SGST (9%)', 'IGST (18%)', 'Total Invoice Amount (INR)']);

                    foreach ($invoices as $inv) {
                        $isRm = ($inv->invoice_mode === 'raw_material' || str_starts_with($inv->invoice_number, 'RMS-'));
                        fputcsv($handle, [
                            $isRm ? 'NILL' : $inv->invoice_number,
                            $isRm ? ($inv->custom_buyer_gstin ?? 'URP / Retail') : ($inv->plant->client->gstin ?? 'URP / Retail'),
                            $isRm ? ($inv->custom_client_name ?? 'Direct Buyer') : ($inv->plant->client->company_name ?? 'N/A'),
                            $isRm ? 'Raw Material Sale' : ($inv->plant->plant_name ?? 'HQ'),
                            Carbon::parse($inv->invoice_date ?? $inv->created_at)->format('d/m/Y'),
                            $inv->total_taxable_value,
                            $inv->cgst,
                            $inv->sgst,
                            $inv->igst,
                            $inv->total_amount,
                        ]);
                    }
                } elseif ($gstType === 'gstr2') {
                    $purchases = Purchase::whereBetween('purchase_date', [$startDate, $endDate])
                        ->orderBy('purchase_date', 'desc')
                        ->get();

                    fputcsv($handle, ['PRAFUL WELDING WORKS - GSTR-2 INWARD PURCHASES ITC RETURN']);
                    fputcsv($handle, ['Period:', $startDate, 'to', $endDate]);
                    fputcsv($handle, []);
                    fputcsv($handle, ['Bill Date', 'Bill No.', 'Vendor / Supplier Name', 'Category', 'Item Description', 'Quantity', 'GST Rate (%)', 'Input Tax Credit GST Paid (INR)', 'Total Amount (INR)']);

                    foreach ($purchases as $pur) {
                        fputcsv($handle, [
                            Carbon::parse($pur->purchase_date)->format('d/m/Y'),
                            $pur->bill_number ?? 'N/A',
                            $pur->vendor_name,
                            ucwords(str_replace('_', ' ', $pur->purchase_type)),
                            $pur->item_name,
                            $pur->quantity,
                            $pur->gst_rate,
                            $pur->gst_amount,
                            $pur->total_amount,
                        ]);
                    }
                } else { // gstr3b
                    $invoices = Invoice::where(function ($q) use ($startDate, $endDate) {
                        $q->whereBetween('invoice_date', [$startDate, $endDate])
                            ->orWhere(function ($sub) use ($startDate, $endDate) {
                                $sub->whereNull('invoice_date')
                                    ->whereBetween('created_at', [Carbon::parse($startDate)->startOfDay(), Carbon::parse($endDate)->endOfDay()]);
                            });
                    })->get();
                    $purchases = Purchase::whereBetween('purchase_date', [$startDate, $endDate])->get();

                    $totalSalesTaxable = $invoices->sum('total_taxable_value');
                    $totalSalesCgst = $invoices->sum('cgst');
                    $totalSalesSgst = $invoices->sum('sgst');
                    $totalSalesIgst = $invoices->sum('igst');
                    $totalSalesGst = $totalSalesCgst + $totalSalesSgst + $totalSalesIgst;
                    $totalPurchaseGst = $purchases->sum('gst_amount');
                    $netPayable = $totalSalesGst - $totalPurchaseGst;

                    fputcsv($handle, ['PRAFUL WELDING WORKS - GSTR-3B MONTHLY RETURN SUMMARY']);
                    fputcsv($handle, ['Period:', $startDate, 'to', $endDate]);
                    fputcsv($handle, []);
                    fputcsv($handle, ['Section', 'Details', 'Taxable Value (INR)', 'IGST (INR)', 'CGST & SGST (INR)', 'Total Tax (INR)']);
                    fputcsv($handle, ['3.1 (a)', 'Outward Taxable Supplies (Sales)', $totalSalesTaxable, $totalSalesIgst, ($totalSalesCgst + $totalSalesSgst), $totalSalesGst]);
                    fputcsv($handle, ['4. (A)', 'Eligible Input Tax Credit (Purchases)', '-', '-', '-', $totalPurchaseGst]);
                    fputcsv($handle, ['6.1', 'Net Tax Liability / Carry Forward', '-', '-', '-', $netPayable]);
                }
            }

            fclose($handle);
        });

        $gstTypeStr = $reportType === 'gst' ? '_'.strtoupper($request->input('gst_type', 'gstr1')) : '';
        $filename = 'PWW_'.ucfirst($reportType).$gstTypeStr.'_Report_'.$startDate.'_to_'.$endDate.'.csv';

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$filename.'"');
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }

    /**
     * Export Reports Data to PDF.
     */
    public function exportPdf(Request $request)
    {
        [$startDate, $endDate, $period, $filterMonth, $filterYear] = $this->getDateRange($request);
        $reportType = $request->input('report_type', 'invoice');

        $invoices = Invoice::with(['plant.client', 'items.product'])
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('invoice_date', [$startDate, $endDate])
                    ->orWhere(function ($sub) use ($startDate, $endDate) {
                        $sub->whereNull('invoice_date')
                            ->whereBetween('created_at', [Carbon::parse($startDate)->startOfDay(), Carbon::parse($endDate)->endOfDay()]);
                    });
            })
            ->orderBy('invoice_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $purchases = Purchase::whereBetween('purchase_date', [$startDate, $endDate])
            ->orderBy('purchase_date', 'desc')
            ->get();

        $expenses = Expense::whereBetween('expense_date', [$startDate, $endDate])
            ->orderBy('expense_date', 'desc')
            ->get();

        $financials = $this->financialService->getFinancialSummary($startDate, $endDate);

        $invoiceSummary = [
            'total_taxable' => $invoices->sum('total_taxable_value'),
            'total_cgst' => $invoices->sum('cgst'),
            'total_sgst' => $invoices->sum('sgst'),
            'total_igst' => $invoices->sum('igst'),
            'total_amount' => $invoices->sum('total_amount'),
            'total_due' => $invoices->sum(fn ($inv) => $inv->remaining_balance),
        ];
        $invoiceSummary['total_gst'] = $invoiceSummary['total_cgst'] + $invoiceSummary['total_sgst'] + $invoiceSummary['total_igst'];

        $purchaseSummary = [
            'total_spent' => $purchases->sum('total_amount'),
            'total_gst' => $purchases->sum('gst_amount'),
        ];

        $expenseSummary = [
            'total_spent' => $expenses->sum('amount'),
            'total_count' => $expenses->count(),
            'by_category' => $expenses->groupBy('expense_category')->map(fn ($g) => $g->sum('amount')),
        ];

        $pdfContent = $this->pdfService->renderViewToPdf('pdf.report_pdf', compact(
            'startDate', 'endDate', 'period', 'reportType',
            'invoices', 'purchases', 'expenses', 'financials',
            'invoiceSummary', 'purchaseSummary', 'expenseSummary'
        ));

        $filename = 'PWW_'.ucfirst($reportType)."_Report_{$startDate}_to_{$endDate}.pdf";

        return response()->streamDownload(
            fn () => print ($pdfContent),
            $filename,
            ['Content-Type' => 'application/pdf']
        );
    }
}
