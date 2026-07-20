<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tax Invoice - {{ $invoice->invoice_number }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            color: #1e293b;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
            font-size: 11px;
            line-height: 1.4;
        }
        .no-print-bar {
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 10px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: bold;
            cursor: pointer;
            border: none;
        }
        .btn-primary {
            background-color: #5287f7;
            color: white;
            text-decoration: none;
        }
        .btn-secondary {
            background-color: #e2e8f0;
            color: #475569;
            text-decoration: none;
        }
        
        /* Invoice container */
        .invoice-box {
            max-width: 100%;
            margin: auto;
            padding: 0;
        }
        
        /* Header table layout */
        .header-table, .meta-table, .items-table, .totals-table, .footer-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .header-table td {
            vertical-align: middle;
        }
        .header-logo {
            width: 50px;
            height: 50px;
            object-fit: contain;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            margin-right: 12px;
        }
        .business-title {
            font-size: 16px;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            margin: 0;
        }
        .business-subtitle {
            font-size: 9px;
            color: #64748b;
            margin: 2px 0 0 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .invoice-title-col {
            text-align: right;
            vertical-align: top;
        }
        .invoice-title {
            font-size: 10px;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 0;
        }
        .invoice-number {
            font-size: 18px;
            font-weight: bold;
            color: #0f172a;
            margin: 2px 0 0 0;
        }
        
        /* Metadata block */
        .meta-table td {
            width: 33.33%;
            vertical-align: top;
            padding-bottom: 15px;
            border-bottom: 1px solid #f1f5f9;
        }
        .section-title {
            font-size: 9px;
            font-weight: bold;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
            display: block;
        }
        .meta-value {
            font-size: 10px;
            color: #334155;
            line-height: 1.5;
        }
        .meta-value-bold {
            font-weight: bold;
            color: #0f172a;
        }
        
        /* Items Table */
        .items-table th {
            background-color: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
            color: #64748b;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 8px 10px;
        }
        .items-table td {
            padding: 10px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        .item-name {
            font-weight: bold;
            color: #0f172a;
            font-size: 11px;
        }
        .item-sku {
            font-size: 8px;
            color: #94a3b8;
            font-weight: bold;
            margin-top: 2px;
            text-transform: uppercase;
        }
        
        /* Totals & Bank details */
        .totals-table td {
            vertical-align: top;
            padding-top: 15px;
        }
        .bank-details-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px;
            font-size: 9px;
            color: #475569;
            line-height: 1.5;
            width: 250px;
        }
        .bank-title {
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            margin-bottom: 6px;
            display: block;
        }
        .totals-box {
            float: right;
            width: 250px;
        }
        .total-row {
            display: table;
            width: 100%;
            margin-bottom: 6px;
            font-size: 10px;
            color: #475569;
        }
        .total-label {
            display: table-cell;
            text-align: left;
        }
        .total-value {
            display: table-cell;
            text-align: right;
            font-weight: bold;
            font-family: 'DejaVu Sans', sans-serif;
        }
        .grand-total-row {
            display: table;
            width: 100%;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
            margin-top: 8px;
            font-size: 12px;
            font-weight: bold;
            color: #0f172a;
        }
        
        /* Footer signatures */
        .footer-table {
            margin-top: 40px;
            border-top: 1px solid #f1f5f9;
            padding-top: 15px;
        }
        .footer-table td {
            vertical-align: bottom;
            font-size: 9px;
            color: #64748b;
        }
        .terms-list {
            margin: 4px 0 0 0;
            padding-left: 12px;
            font-size: 8px;
            color: #94a3b8;
        }
        .signature-line {
            width: 100px;
            border-bottom: 1px solid #cbd5e1;
            margin-top: 25px;
            margin-bottom: 4px;
        }
        
        @media print {
            .no-print-bar {
                display: none !important;
            }
            body {
                background-color: #ffffff;
                padding: 0 !important;
            }
        }
    </style>
</head>
<body>

    <!-- Print Control Bar (Hidden on print) -->
    <div class="no-print-bar" style="max-width: 800px; margin: 0 auto 20px auto;">
        <div>
            <span style="font-weight: bold; font-size: 12px; color: #334155;">Tax Invoice View (A4 Print-Optimized)</span>
            <span style="background-color: #e2e8f0; color: #334155; font-size: 9px; font-weight: bold; padding: 2px 6px; border-radius: 4px; text-transform: uppercase; margin-left: 8px;">
                {{ $invoice->payment_status }}
            </span>
        </div>
        <div>
            <button onclick="window.print()" class="btn btn-primary">Print / Save as PDF</button>
            <button onclick="window.close()" class="btn btn-secondary" style="margin-left: 6px;">Close</button>
        </div>
    </div>

    <!-- Main Invoice Document -->
    <div class="invoice-box" style="max-width: 800px; margin: auto;">
        
        <!-- Header -->
        <table class="header-table">
            <tr>
                <td style="width: 70%;">
                    <table>
                        <tr>
                            <td>
                                <img src="{{ public_path(\App\Models\Setting::get('logo_path', 'logo.jpg')) }}" alt="Logo" class="header-logo">
                            </td>
                            <td>
                                <h1 class="business-title">{{ \App\Models\Setting::get('business_name', 'Praful Welding Works') }}</h1>
                                <p class="business-subtitle">{{ \App\Models\Setting::get('business_subtitle', 'Heavy Fabrication & Industrial Racks ERP') }}</p>
                            </td>
                        </tr>
                    </table>
                </td>
                <td class="invoice-title-col">
                    <h2 class="invoice-title">Tax Invoice</h2>
                    <p class="invoice-number">{{ $invoice->invoice_number }}</p>
                </td>
            </tr>
        </table>

        <!-- Metadata Block -->
        <table class="meta-table">
            <tr>
                <td>
                    <span class="section-title">Seller (Issued By)</span>
                    <div class="meta-value">
                        <span class="meta-value-bold">{{ \App\Models\Setting::get('business_name', 'Praful Welding Works') }}</span><br>
                        {{ \App\Models\Setting::get('address_line_1', 'Plot No. 12, G.I.D.C. Metoda,') }}<br>
                        {{ \App\Models\Setting::get('address_line_2', 'Rajkot, Gujarat - 360021') }}<br>
                        <span style="font-weight: bold; color: #475569;">GSTIN: {{ \App\Models\Setting::get('gstin', '24PWWRK1234A1Z0') }}</span>
                    </div>
                </td>
                <td>
                    <span class="section-title">Billed To (Client)</span>
                    <div class="meta-value">
                        <span class="meta-value-bold">{{ $client->client_name ?? 'N/A' }}</span><br>
                        {{ $plant->plant_name ?? 'N/A' }} Address:<br>
                        {{ $plant->destination_address ?? 'N/A' }}<br>
                        <span style="font-weight: bold; color: #475569;">GSTIN: {{ $client->gstin ?? 'N/A' }}</span>
                    </div>
                </td>
                <td style="text-align: right;">
                    <span class="section-title">Invoice Details</span>
                    <div class="meta-value">
                        Date: <span class="meta-value-bold">{{ $invoice->created_at->format('d M Y') }}</span><br>
                        Due Date: <span class="meta-value-bold">{{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') : \Carbon\Carbon::parse($invoice->created_at)->addDays(30)->format('d M Y') }}</span><br>
                        Status: <span class="meta-value-bold" style="text-transform: uppercase; color: {{ $invoice->payment_status === 'paid' ? '#059669' : '#e11d48' }}">{{ $invoice->payment_status }}</span>
                    </div>
                </td>
            </tr>
        </table>

        <!-- Items Table -->
        <table class="items-table" style="margin-top: 15px;">
            <thead>
                <tr>
                    <th style="text-align: left; width: 50%;">Items / SKU</th>
                    <th style="text-align: right; width: 15%;">Quantity</th>
                    <th style="text-align: right; width: 15%;">Unit Rate</th>
                    <th style="text-align: right; width: 20%;">Taxable Value</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($groupedItems as $item)
                    <tr>
                        <td>
                            <div class="item-name">{{ $item->product_name }}</div>
                            <div class="item-sku">SKU: {{ $item->sku }}</div>
                        </td>
                        <td style="text-align: right; font-weight: bold;">{{ $item->quantity }} units</td>
                        <td style="text-align: right; font-family: 'DejaVu Sans', sans-serif;">&#8377;{{ number_format($item->unit_price, 2) }}</td>
                        <td style="text-align: right; font-weight: bold; font-family: 'DejaVu Sans', sans-serif;">&#8377;{{ number_format($item->total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Summary Block -->
        <table class="totals-table">
            <tr>
                <td style="width: 55%;">
                    <div class="bank-details-box">
                        <span class="bank-title">Settlement Bank Accounts</span>
                        <table>
                            <tr>
                                <td style="font-weight: bold; color: #475569; width: 75px; padding: 2px 0;">Bank Name:</td>
                                <td>State Bank of India (SBI)</td>
                            </tr>
                            <tr>
                                <td style="font-weight: bold; color: #475569; padding: 2px 0;">Account:</td>
                                <td>Praful Welding Works</td>
                            </tr>
                            <tr>
                                <td style="font-weight: bold; color: #475569; padding: 2px 0;">A/C No:</td>
                                <td style="font-weight: bold;">33445566778</td>
                            </tr>
                            <tr>
                                <td style="font-weight: bold; color: #475569; padding: 2px 0;">IFSC:</td>
                                <td style="font-weight: bold;">SBIN0001234</td>
                            </tr>
                        </table>
                    </div>
                </td>
                <td style="width: 45%;">
                    <div class="totals-box">
                        <div class="total-row">
                            <span class="total-label">Taxable Subtotal:</span>
                            <span class="total-value">&#8377;{{ number_format($invoice->taxable_amount, 2) }}</span>
                        </div>
                        
                        @if ($invoice->igst > 0)
                            <div class="total-row">
                                <span class="total-label">IGST (18.0%):</span>
                                <span class="total-value">&#8377;{{ number_format($invoice->igst, 2) }}</span>
                            </div>
                        @else
                            <div class="total-row">
                                <span class="total-label">CGST (9.0%):</span>
                                <span class="total-value">&#8377;{{ number_format($invoice->cgst, 2) }}</span>
                            </div>
                            <div class="total-row">
                                <span class="total-label">SGST (9.0%):</span>
                                <span class="total-value">&#8377;{{ number_format($invoice->sgst, 2) }}</span>
                            </div>
                        @endif

                        <div class="grand-total-row">
                            <span class="total-label" style="font-weight: bold;">Total Amount:</span>
                            <span class="total-value" style="font-size: 14px; color: #5287f7; font-weight: bold;">&#8377;{{ number_format($invoice->total_amount, 2) }}</span>
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <!-- Bottom Terms & Signatures -->
        <table class="footer-table">
            <tr>
                <td style="width: 60%;">
                    <span style="font-weight: bold; color: #475569; font-size: 9px; text-transform: uppercase;">Terms & Conditions</span>
                    <ul class="terms-list">
                        <li>All disputes subject to Rajkot jurisdiction.</li>
                        <li>Interest @18% p.a. charged on overdue invoices.</li>
                    </ul>
                </td>
                <td style="width: 40%; text-align: right;">
                    <div style="font-weight: bold; text-transform: uppercase; font-size: 8px; color: #64748b;">Authorized Signatory</div>
                    <div class="signature-line" style="display: inline-block;"></div>
                    <div style="font-size: 8px; color: #94a3b8;">Praful Welding Works</div>
                </td>
            </tr>
        </table>

    </div>

    <script>
        window.addEventListener('load', function() {
            // Auto trigger browser print if screen view, not in pdf download
            if (!window.location.href.includes('download')) {
                setTimeout(function() {
                    window.print();
                }, 300);
            }
        });
    </script>
</body>
</html>
