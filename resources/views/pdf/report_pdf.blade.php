<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PWW {{ ucfirst($reportType) }} Report</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            color: #1e293b;
            font-size: 10px;
            margin: 0;
            padding: 20px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .header-table td {
            vertical-align: top;
        }
        .company-name {
            font-size: 18px;
            font-weight: bold;
            color: #0f172a;
            margin: 0;
        }
        .company-details {
            font-size: 10px;
            color: #64748b;
            margin-top: 4px;
            line-height: 1.4;
        }
        .title-badge {
            text-align: right;
        }
        .doc-title {
            font-size: 18px;
            font-weight: 800;
            color: #4371D7;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .period-info {
            font-size: 10px;
            color: #64748b;
            margin-top: 4px;
            font-weight: bold;
        }
        .summary-box {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .summary-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 10px 14px;
            border-radius: 6px;
        }
        .card-label {
            font-size: 9px;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
        }
        .card-val {
            font-size: 14px;
            font-weight: bold;
            color: #0f172a;
            margin-top: 4px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .data-table th {
            background-color: #4371D7;
            color: #ffffff;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 8px 6px;
            border: 1px solid #4371D7;
        }
        .data-table td {
            padding: 7px 6px;
            border: 1px solid #e2e8f0;
            font-size: 9px;
        }
        .data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .text-blue { color: #2563eb; }
        .text-green { color: #16a34a; }
        .text-red { color: #dc2626; }
    </style>
</head>
<body>

    <!-- Header -->
    <table class="header-table">
        <tr>
            <td style="width: 60%;">
                <h1 class="company-name">PRAFUL WELDING WORKS</h1>
                <div class="company-details">
                    Industrial Rack Manufacturing & Metal Fabrication<br>
                    GSTIN: 24AAFFP1234A1Z9 | Phone: +91 98250 12345<br>
                    Plot No. 45, GIDC Industrial Estate, Rajkot, Gujarat - 360002
                </div>
            </td>
            <td class="title-badge" style="width: 40%;">
                <h2 class="doc-title">
                    @if($reportType === 'invoice')
                        Sales Audit Report
                    @elseif($reportType === 'purchase')
                        Purchase Audit Report
                    @elseif($reportType === 'financial')
                        Financial P&L Report
                    @elseif($reportType === 'expense')
                        Expense Audit Report
                    @else
                        Audit Report
                    @endif
                </h2>
                <div class="period-info">
                    Period: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
                </div>
            </td>
        </tr>
    </table>

    <!-- Active Report Table -->
    @if($reportType === 'invoice')
        <table class="summary-box">
            <tr>
                <td style="width: 24%; padding-right: 8px;">
                    <div class="summary-card">
                        <div class="card-label">Total Taxable Value</div>
                        <div class="card-val">₹{{ number_format($invoiceSummary['total_taxable'], 2) }}</div>
                    </div>
                </td>
                <td style="width: 24%; padding-right: 8px;">
                    <div class="summary-card">
                        <div class="card-label">Total GST Collected</div>
                        <div class="card-val text-blue">₹{{ number_format($invoiceSummary['total_gst'], 2) }}</div>
                    </div>
                </td>
                <td style="width: 26%; padding-right: 8px;">
                    <div class="summary-card">
                        <div class="card-label">Total Sales Revenue</div>
                        <div class="card-val text-green">₹{{ number_format($invoiceSummary['total_amount'], 2) }}</div>
                    </div>
                </td>
                <td style="width: 26%;">
                    <div class="summary-card">
                        <div class="card-label">Total Outstanding Due</div>
                        <div class="card-val text-red">₹{{ number_format($invoiceSummary['total_due'] ?? 0, 2) }}</div>
                    </div>
                </td>
            </tr>
        </table>

        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 14%;">Invoice No.</th>
                    <th style="width: 22%;">Client & Plant</th>
                    <th style="width: 10%;">Date</th>
                    <th class="text-right" style="width: 11%;">Taxable (₹)</th>
                    <th class="text-right" style="width: 9%;">CGST (₹)</th>
                    <th class="text-right" style="width: 9%;">SGST (₹)</th>
                    <th class="text-right" style="width: 9%;">IGST (₹)</th>
                    <th class="text-right" style="width: 12%;">Total (₹)</th>
                    <th class="text-right" style="width: 12%;">Due (₹)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $inv)
                    <tr>
                        <td class="font-bold text-blue">{{ $inv->invoice_number }}</td>
                        <td>{{ $inv->plant->client->company_name ?? 'N/A' }} ({{ $inv->plant->plant_name ?? 'HQ' }})</td>
                        <td>{{ \Carbon\Carbon::parse($inv->invoice_date ?? $inv->created_at)->format('d/m/Y') }}</td>
                        <td class="text-right">₹{{ number_format($inv->total_taxable_value, 2) }}</td>
                        <td class="text-right">₹{{ number_format($inv->cgst, 2) }}</td>
                        <td class="text-right">₹{{ number_format($inv->sgst, 2) }}</td>
                        <td class="text-right">₹{{ number_format($inv->igst, 2) }}</td>
                        <td class="text-right font-bold">₹{{ number_format($inv->total_amount, 2) }}</td>
                        <td class="text-right font-bold {{ $inv->remaining_balance > 0 ? 'text-red' : 'text-green' }}">
                            ₹{{ number_format($inv->remaining_balance, 2) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center">No invoice records found for this period.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

    @elseif($reportType === 'purchase')
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 15%;">Date</th>
                    <th style="width: 18%;">Bill No.</th>
                    <th style="width: 20%;">Vendor Name</th>
                    <th style="width: 15%;">Category</th>
                    <th style="width: 17%;">Item Name</th>
                    <th class="text-right" style="width: 15%;">Total Amount (₹)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($purchases as $pur)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($pur->purchase_date)->format('d/m/Y') }}</td>
                        <td>{{ $pur->bill_number ?? 'N/A' }}</td>
                        <td class="font-bold">{{ $pur->vendor_name }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $pur->purchase_type)) }}</td>
                        <td>{{ $pur->item_name }}</td>
                        <td class="text-right font-bold text-red">₹{{ number_format($pur->total_amount, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">No purchase records found for this period.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

    @elseif($reportType === 'financial')
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 50%;">Accounting Line Item</th>
                    <th class="text-right" style="width: 50%;">Amount (INR)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Total Sales Revenue (A)</td>
                    <td class="text-right font-bold text-green">₹{{ number_format($financials['revenue'], 2) }}</td>
                </tr>
                <tr>
                    <td>Total Purchases (B)</td>
                    <td class="text-right text-red">- ₹{{ number_format($financials['purchases'], 2) }}</td>
                </tr>
                <tr>
                    <td>Total Expenses (C)</td>
                    <td class="text-right text-red">- ₹{{ number_format($financials['expenses'], 2) }}</td>
                </tr>
                <tr style="background-color: #f1f5f9; font-weight: bold;">
                    <td style="font-size: 11px;">NET PROFIT / LOSS (A - B - C)</td>
                    <td class="text-right" style="font-size: 11px; {{ $financials['net_profit'] >= 0 ? 'color:#16a34a;' : 'color:#dc2626;' }}">
                        ₹{{ number_format($financials['net_profit'], 2) }}
                    </td>
                </tr>
            </tbody>
        </table>

    @elseif($reportType === 'expense')
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 15%;">Expense Date</th>
                    <th style="width: 25%;">Category</th>
                    <th style="width: 40%;">Description / Memo</th>
                    <th class="text-right" style="width: 20%;">Amount (₹)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($expenses as $exp)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($exp->expense_date)->format('d/m/Y') }}</td>
                        <td class="font-bold">{{ ucfirst(str_replace('_', ' ', $exp->expense_category)) }}</td>
                        <td>{{ $exp->description ?? 'N/A' }}</td>
                        <td class="text-right font-bold text-red">₹{{ number_format($exp->amount, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center">No expense records found for this period.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

    @elseif($reportType === 'gst')
        @php $gstType = request('gst_type', 'gstr3b'); @endphp

        @if($gstType === 'gstr1')
            <h3 style="color: #2563eb; margin-bottom: 10px;">GSTR-1 Outward Sales Return</h3>
            <table class="summary-box">
                <tr>
                    <td style="width: 32%; padding-right: 10px;">
                        <div class="summary-card">
                            <div class="card-label">Total Outward Taxable</div>
                            <div class="card-val">₹{{ number_format($invoiceSummary['total_taxable'], 2) }}</div>
                        </div>
                    </td>
                    <td style="width: 32%; padding-right: 10px;">
                        <div class="summary-card">
                            <div class="card-label">Total Output GST Collected</div>
                            <div class="card-val text-green">₹{{ number_format($invoiceSummary['total_gst'], 2) }}</div>
                        </div>
                    </td>
                    <td style="width: 36%;">
                        <div class="summary-card">
                            <div class="card-label">Total B2B Outward Invoices</div>
                            <div class="card-val text-blue">{{ count($invoices) }} Invoices</div>
                        </div>
                    </td>
                </tr>
            </table>

            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 15%;">Invoice No.</th>
                        <th style="width: 18%;">GSTIN</th>
                        <th style="width: 23%;">Client Company</th>
                        <th class="text-right" style="width: 11%;">Taxable (₹)</th>
                        <th class="text-right" style="width: 11%;">CGST (9%)</th>
                        <th class="text-right" style="width: 11%;">SGST (9%)</th>
                        <th class="text-right" style="width: 11%;">IGST (18%)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $inv)
                        <tr>
                            <td class="font-bold text-blue">{{ $inv->invoice_number }}</td>
                            <td>{{ $inv->plant->client->gstin ?? 'URP / Retail' }}</td>
                            <td>{{ $inv->plant->client->company_name ?? 'N/A' }}</td>
                            <td class="text-right">₹{{ number_format($inv->total_taxable_value, 2) }}</td>
                            <td class="text-right">₹{{ number_format($inv->cgst, 2) }}</td>
                            <td class="text-right">₹{{ number_format($inv->sgst, 2) }}</td>
                            <td class="text-right">₹{{ number_format($inv->igst, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">No GSTR-1 records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

        @elseif($gstType === 'gstr2')
            <h3 style="color: #2563eb; margin-bottom: 10px;">GSTR-2 Inward Purchase Input Tax Credit (ITC)</h3>
            <table class="summary-box">
                <tr>
                    <td style="width: 50%; padding-right: 10px;">
                        <div class="summary-card">
                            <div class="card-label">Total Purchase Outlay</div>
                            <div class="card-val">₹{{ number_format($purchaseSummary['total_spent'], 2) }}</div>
                        </div>
                    </td>
                    <td style="width: 50%;">
                        <div class="summary-card">
                            <div class="card-label">Total Input Tax Credit (ITC) Paid</div>
                            <div class="card-val text-blue">₹{{ number_format($purchaseSummary['total_gst'], 2) }}</div>
                        </div>
                    </td>
                </tr>
            </table>

            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 12%;">Bill Date</th>
                        <th style="width: 15%;">Bill No.</th>
                        <th style="width: 25%;">Supplier / Vendor Name</th>
                        <th style="width: 18%;">Item Description</th>
                        <th class="text-center" style="width: 10%;">GST Rate</th>
                        <th class="text-right" style="width: 20%;">ITC Tax Paid (₹)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchases as $pur)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($pur->purchase_date)->format('d/m/Y') }}</td>
                            <td>{{ $pur->bill_number ?? 'N/A' }}</td>
                            <td class="font-bold">{{ $pur->vendor_name }}</td>
                            <td>{{ $pur->item_name }}</td>
                            <td class="text-center">{{ number_format($pur->gst_rate, 0) }}%</td>
                            <td class="text-right font-bold text-red">₹{{ number_format($pur->gst_amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">No GSTR-2 ITC purchase records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

        @else
            <h3 style="color: #2563eb; margin-bottom: 10px;">GSTR-3B Monthly Return Summary</h3>
            @php
                $netGst = $invoiceSummary['total_gst'] - $purchaseSummary['total_gst'];
            @endphp
            <table class="summary-box">
                <tr>
                    <td style="width: 32%; padding-right: 10px;">
                        <div class="summary-card">
                            <div class="card-label">Sales GST Output Liability</div>
                            <div class="card-val text-red">₹{{ number_format($invoiceSummary['total_gst'], 2) }}</div>
                        </div>
                    </td>
                    <td style="width: 32%; padding-right: 10px;">
                        <div class="summary-card">
                            <div class="card-label">Purchase Input Tax Credit (ITC)</div>
                            <div class="card-val text-green">₹{{ number_format($purchaseSummary['total_gst'], 2) }}</div>
                        </div>
                    </td>
                    <td style="width: 36%;">
                        <div class="summary-card">
                            <div class="card-label">Net Tax Payable / (Credit)</div>
                            <div class="card-val {{ $netGst > 0 ? 'text-red' : 'text-green' }}">
                                ₹{{ number_format(abs($netGst), 2) }} {{ $netGst > 0 ? 'DUE' : 'ITC CREDIT' }}
                            </div>
                        </div>
                    </td>
                </tr>
            </table>

            <h4 style="margin-top: 15px; margin-bottom: 5px;">3.1 Details of Outward Supplies & Output Tax Liability</h4>
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 40%;">Nature of Supplies</th>
                        <th class="text-right" style="width: 20%;">Total Taxable (₹)</th>
                        <th class="text-right" style="width: 20%;">Integrated Tax (₹)</th>
                        <th class="text-right" style="width: 20%;">Central & State Tax (₹)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="font-bold">(a) Outward Taxable Supplies (Other than Zero-Rated)</td>
                        <td class="text-right font-bold">₹{{ number_format($invoiceSummary['total_taxable'], 2) }}</td>
                        <td class="text-right">₹{{ number_format($invoiceSummary['total_igst'], 2) }}</td>
                        <td class="text-right">₹{{ number_format($invoiceSummary['total_cgst'] + $invoiceSummary['total_sgst'], 2) }}</td>
                    </tr>
                </tbody>
            </table>

            <h4 style="margin-top: 15px; margin-bottom: 5px;">4. Eligible Input Tax Credit (ITC)</h4>
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 60%;">Details of ITC Available</th>
                        <th class="text-right" style="width: 40%;">Total ITC Amount (₹)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="font-bold">(A) Input Tax Credit Available (All Inward Purchases & Raw Material)</td>
                        <td class="text-right font-bold text-green">₹{{ number_format($purchaseSummary['total_gst'], 2) }}</td>
                    </tr>
                </tbody>
            </table>
        @endif
    @endif

</body>
</html>
