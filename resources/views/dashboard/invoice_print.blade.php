<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tax Invoice - {{ $invoice->invoice_number }}</title>
    <!-- Outfit Font for browser rendering -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;950&display=swap" rel="stylesheet">
    
    <style>
        /* Load local Outfit fonts for PDF generation and browser view consistency */
        @font-face {
            font-family: 'Outfit';
            font-style: normal;
            font-weight: 400;
            @if(isset($isPdf) && $isPdf)
                src: url('{{ public_path("fonts/outfit/Outfit-Regular.ttf") }}') format('truetype');
            @else
                src: url('{{ asset("fonts/outfit/Outfit-Regular.ttf") }}') format('truetype');
            @endif
        }
        @font-face {
            font-family: 'Outfit';
            font-style: normal;
            font-weight: 500;
            @if(isset($isPdf) && $isPdf)
                src: url('{{ public_path("fonts/outfit/Outfit-Medium.ttf") }}') format('truetype');
            @else
                src: url('{{ asset("fonts/outfit/Outfit-Medium.ttf") }}') format('truetype');
            @endif
        }
        @font-face {
            font-family: 'Outfit';
            font-style: normal;
            font-weight: 600;
            @if(isset($isPdf) && $isPdf)
                src: url('{{ public_path("fonts/outfit/Outfit-SemiBold.ttf") }}') format('truetype');
            @else
                src: url('{{ asset("fonts/outfit/Outfit-SemiBold.ttf") }}') format('truetype');
            @endif
        }
        @font-face {
            font-family: 'Outfit';
            font-style: normal;
            font-weight: 700;
            @if(isset($isPdf) && $isPdf)
                src: url('{{ public_path("fonts/outfit/Outfit-Bold.ttf") }}') format('truetype');
            @else
                src: url('{{ asset("fonts/outfit/Outfit-Bold.ttf") }}') format('truetype');
            @endif
        }
        @font-face {
            font-family: 'Outfit';
            font-style: normal;
            font-weight: 800;
            @if(isset($isPdf) && $isPdf)
                src: url('{{ public_path("fonts/outfit/Outfit-ExtraBold.ttf") }}') format('truetype');
            @else
                src: url('{{ asset("fonts/outfit/Outfit-ExtraBold.ttf") }}') format('truetype');
            @endif
        }

        /* PDF and Print Optimized Stylesheet */
        @page {
            size: A4 portrait;
            margin: 0 !important; /* Critical: Removes browser headers, footers, page titles, and URLs */
        }
        
        body {
            font-family: 'Outfit', 'DejaVu Sans', sans-serif;
            color: #1e293b;
            background-color: #f8fafc;
            margin: 0;
            padding: 20mm 15mm; /* Margin for screen view, acts as page margin on print */
            font-size: 11px;
            line-height: 1.4;
        }
        
        /* Control bar styling matching exactly the mockup */
        .no-print-bar {
            background-color: #ffffff;
            border: 1px solid #cbd5e1;
            padding: 12px 24px;
            border-radius: 12px;
            margin: 0 auto 24px auto;
            max-width: 820px;
            display: table;
            width: 100%;
            box-sizing: border-box;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        .control-left {
            display: table-cell;
            vertical-align: middle;
            text-align: left;
        }
        .control-right {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
        }
        .status-badge {
            background-color: #f1f5f9;
            color: #475569;
            font-size: 10px;
            font-weight: 800;
            padding: 3px 8px;
            border-radius: 6px;
            text-transform: uppercase;
            margin-left: 8px;
            border: 1px solid #cbd5e1;
        }
        .status-badge.paid {
            background-color: #ecfdf5;
            color: #047857;
            border-color: #a7f3d0;
        }
        .status-badge.unpaid {
            background-color: #fef2f2;
            color: #b91c1c;
            border-color: #fca5a5;
        }
        .btn {
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 11px;
            font-weight: bold;
            cursor: pointer;
            border: none;
            display: inline-block;
            text-decoration: none;
            transition: all 0.15s ease;
        }
        .btn-primary {
            background-color: #0f9d58;
            color: white;
        }
        .btn-primary:hover {
            background-color: #0b8043;
        }
        .btn-secondary {
            background-color: #f1f5f9;
            color: #475569;
            border: 1px solid #cbd5e1;
            margin-left: 8px;
        }
        .btn-secondary:hover {
            background-color: #e2e8f0;
        }
        
        /* Invoice container with card look in browser */
        .invoice-box {
            max-width: 820px;
            margin: auto;
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
            box-sizing: border-box;
        }
        
        /* Header table layout */
        .header-table, .meta-table, .items-table, .totals-table, .footer-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        
        .header-table td {
            vertical-align: middle;
        }
        .header-logo {
            width: 54px;
            height: 54px;
            object-fit: contain;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            margin-right: 15px;
        }
        .business-title {
            font-size: 18px;
            font-weight: 850;
            color: #0f172a;
            text-transform: uppercase;
            margin: 0;
            letter-spacing: -0.5px;
        }
        .business-subtitle {
            font-size: 9px;
            color: #64748b;
            margin: 3px 0 0 0;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            font-weight: bold;
        }
        .invoice-title-col {
            text-align: right;
            vertical-align: top;
        }
        .invoice-title {
            font-size: 9px;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 850;
            margin: 0;
        }
        .invoice-number {
            font-size: 20px;
            font-weight: 850;
            color: #0f172a;
            margin: 3px 0 0 0;
            letter-spacing: -0.5px;
        }
        
        /* Metadata block */
        .meta-table td {
            width: 33.33%;
            vertical-align: top;
            padding-bottom: 20px;
            border-bottom: 1px solid #f1f5f9;
        }
        .section-title {
            font-size: 9px;
            font-weight: 850;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 6px;
            display: block;
        }
        .meta-value {
            font-size: 11px;
            color: #475569;
            line-height: 1.6;
        }
        .meta-value-bold {
            font-weight: 700;
            color: #0f172a;
        }
        
        /* Items Table */
        .items-table {
            margin-top: 15px;
        }
        .items-table th {
            border-bottom: 1px solid #cbd5e1;
            color: #94a3b8;
            font-size: 9px;
            font-weight: 850;
            text-transform: uppercase;
            padding: 10px 0;
            letter-spacing: 0.8px;
        }
        .items-table td {
            padding: 14px 0;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        .item-name {
            font-weight: 700;
            color: #0f172a;
            font-size: 12px;
        }
        .item-sku {
            font-size: 8px;
            color: #94a3b8;
            font-weight: 855;
            margin-top: 3px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        /* Totals & Bank details */
        .totals-table td {
            vertical-align: top;
            padding-top: 20px;
        }
        .bank-details-box {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px;
            font-size: 9px;
            color: #475569;
            line-height: 1.6;
            width: 280px;
        }
        .bank-title {
            font-weight: 850;
            color: #0f172a;
            text-transform: uppercase;
            margin-bottom: 8px;
            display: block;
            letter-spacing: 0.8px;
        }
        .totals-box {
            float: right;
            width: 260px;
        }
        .total-row {
            display: table;
            width: 100%;
            margin-bottom: 8px;
            font-size: 11px;
            color: #64748b;
            font-weight: 500;
        }
        .total-label {
            display: table-cell;
            text-align: left;
        }
        .total-value {
            display: table-cell;
            text-align: right;
            font-weight: 700;
            color: #334155;
            font-family: 'Outfit', 'DejaVu Sans', sans-serif;
        }
        .grand-total-row {
            display: table;
            width: 100%;
            border-top: 1px solid #e2e8f0;
            padding-top: 12px;
            margin-top: 10px;
            font-size: 14px;
            font-weight: bold;
            color: #0f172a;
        }
        
        /* Footer signatures */
        .footer-table {
            margin-top: 40px;
            border-top: 1px solid #f1f5f9;
            padding-top: 20px;
        }
        .footer-table td {
            vertical-align: bottom;
            font-size: 9px;
            color: #94a3b8;
        }
        .terms-list {
            margin: 6px 0 0 0;
            padding-left: 14px;
            font-size: 9px;
            color: #94a3b8;
            line-height: 1.5;
        }
        .signature-line {
            width: 120px;
            border-bottom: 1px solid #cbd5e1;
            margin-top: 30px;
            margin-bottom: 5px;
        }
        
        @media print {
            .no-print-bar {
                display: none !important;
            }
            body {
                background-color: #ffffff !important;
                padding: 15mm 15mm !important; /* Forces content margin inside page boundaries */
            }
            .invoice-box {
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
                max-width: 100% !important;
                border-radius: 0 !important;
            }
        }

        @if(isset($isPdf) && $isPdf)
            @page {
                margin: 15mm !important;
            }
            body {
                background-color: #ffffff !important;
                padding: 0 !important;
            }
            .invoice-box {
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
                max-width: 100% !important;
                border-radius: 0 !important;
                background-color: #ffffff !important;
            }
        @endif
    </style>
</head>
<body>

    @if(!isset($isPdf) || !$isPdf)
    <!-- Print Control Bar (Hidden on print) -->
    <div class="no-print-bar">
        <div class="control-left">
            <span style="font-weight: 800; font-size: 13px; color: #0f172a; tracking: -0.2px;">Tax Invoice View (A4 Print-Optimized)</span>
            <span class="status-badge {{ strtolower($invoice->payment_status) }}">
                {{ $invoice->payment_status }}
            </span>
        </div>
        <div class="control-right">
            <button onclick="window.print()" class="btn btn-primary">Print / Save as PDF</button>
            <button onclick="window.close()" class="btn btn-secondary">Close</button>
        </div>
    </div>
    @endif

    <!-- Main Invoice Document -->
    <div class="invoice-box">
        
        <!-- Header -->
        <table class="header-table">
            <tr>
                <td style="width: 55%;">
                    <table cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <td>
                                @php
                                    $logoPath = \App\Models\Setting::get('logo_path', 'logo.jpg');
                                    $fullLogoPath = public_path($logoPath);
                                    if (file_exists($fullLogoPath)) {
                                        $logoData = base64_encode(file_get_contents($fullLogoPath));
                                        $logoSrc = 'data:image/' . pathinfo($fullLogoPath, PATHINFO_EXTENSION) . ';base64,' . $logoData;
                                    } else {
                                        $logoSrc = asset($logoPath);
                                    }
                                @endphp
                                <img src="{{ $logoSrc }}" alt="Logo" class="header-logo">
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
                        <span style="font-weight: bold; color: #475569; font-family: monospace;">GSTIN: {{ \App\Models\Setting::get('gstin', '24PWWRK1234A1Z0') }}</span>
                    </div>
                </td>
                <td>
                    <span class="section-title">Billed To (Client)</span>
                    <div class="meta-value">
                        <span class="meta-value-bold">{{ $client->company_name ?? 'N/A' }}</span><br>
                        {{ $plant->plant_name ?? 'N/A' }} Address:<br>
                        {{ $plant->shipping_address ?? 'N/A' }}<br>
                        <span style="font-weight: bold; color: #475569; font-family: monospace;">GSTIN: {{ $client->gst_number ?? 'N/A' }}</span>
                    </div>
                </td>
                <td style="text-align: right;">
                    <span class="section-title">Invoice Details</span>
                    <div class="meta-value">
                        Date: <span class="meta-value-bold">{{ $invoice->created_at->format('d M Y') }}</span><br>
                        Due Date: <span class="meta-value-bold">{{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') : \Carbon\Carbon::parse($invoice->created_at)->addDays(30)->format('d M Y') }}</span><br>
                        Status: <span class="meta-value-bold" style="text-transform: uppercase; color: {{ $invoice->payment_status === 'paid' ? '#047857' : '#b91c1c' }}">{{ $invoice->payment_status }}</span>
                    </div>
                </td>
            </tr>
        </table>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="text-align: left; width: 45%;">Items / SKU</th>
                    <th style="text-align: right; width: 15%;">Quantity</th>
                    <th style="text-align: right; width: 20%;">Unit Rate (&#8377;)</th>
                    <th style="text-align: right; width: 20%;">Taxable Value (&#8377;)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($groupedItems as $item)
                    <tr>
                        <td>
                            <div class="item-name">{{ $item->product_name }}</div>
                            <div class="item-sku">SKU: {{ $item->sku }}</div>
                        </td>
                        <td style="text-align: right; font-weight: 700; color: #0f172a;">{{ number_format($item->quantity) }} units</td>
                        <td style="text-align: right; font-family: 'Outfit', 'DejaVu Sans', sans-serif;">&#8377;{{ number_format($item->unit_price, 2) }}</td>
                        <td style="text-align: right; font-weight: 700; color: #0f172a; font-family: 'Outfit', 'DejaVu Sans', sans-serif;">&#8377;{{ number_format($item->total, 2) }}</td>
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
                        <table cellpadding="0" cellspacing="0" border="0" style="width: 100%; border-collapse: collapse !important; display: table !important;">
                            <tr>
                                <td style="font-weight: 700; color: #64748b; width: 100px; padding: 3px 0; display: table-cell !important; float: none !important; vertical-align: top !important; font-size: 10px; font-family: 'Outfit', 'DejaVu Sans', sans-serif !important;">Bank Name:</td>
                                <td style="color: #334155; font-weight: 600; padding: 3px 0; display: table-cell !important; float: none !important; vertical-align: top !important; font-size: 10px; font-family: 'Outfit', 'DejaVu Sans', sans-serif !important;">{{ \App\Models\Setting::get('bank_name', 'State Bank of India (SBI)') }}</td>
                            </tr>
                            <tr>
                                <td style="font-weight: 700; color: #64748b; padding: 3px 0; display: table-cell !important; float: none !important; vertical-align: top !important; font-size: 10px; font-family: 'Outfit', 'DejaVu Sans', sans-serif !important;">Account:</td>
                                <td style="color: #334155; font-weight: 600; padding: 3px 0; display: table-cell !important; float: none !important; vertical-align: top !important; font-size: 10px; font-family: 'Outfit', 'DejaVu Sans', sans-serif !important;">{{ \App\Models\Setting::get('bank_account_name', 'Praful Welding Works') }}</td>
                            </tr>
                            <tr>
                                <td style="font-weight: 700; color: #64748b; padding: 3px 0; display: table-cell !important; float: none !important; vertical-align: top !important; font-size: 10px; font-family: 'Outfit', 'DejaVu Sans', sans-serif !important;">A/C No:</td>
                                <td style="font-weight: 700; color: #0f172a; font-family: 'Outfit', 'DejaVu Sans', monospace !important; padding: 3px 0; display: table-cell !important; float: none !important; vertical-align: top !important; font-size: 10px;">{{ \App\Models\Setting::get('bank_account_no', '33445566778') }}</td>
                            </tr>
                            <tr>
                                <td style="font-weight: 700; color: #64748b; padding: 3px 0; display: table-cell !important; float: none !important; vertical-align: top !important; font-size: 10px; font-family: 'Outfit', 'DejaVu Sans', sans-serif !important;">IFSC:</td>
                                <td style="font-weight: 700; color: #0f172a; font-family: 'Outfit', 'DejaVu Sans', monospace !important; padding: 3px 0; display: table-cell !important; float: none !important; vertical-align: top !important; font-size: 10px;">{{ \App\Models\Setting::get('bank_ifsc', 'SBIN0001234') }}</td>
                            </tr>
                        </table>
                    </div>
                </td>
                <td style="width: 45%;">
                    <div class="totals-box">
                        <div class="total-row">
                            <span class="total-label">Taxable Subtotal:</span>
                            <span class="total-value">&#8377;{{ number_format($invoice->total_taxable_value, 2) }}</span>
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
                            <span class="total-label" style="font-weight: 850;">Total Amount:</span>
                            <span class="total-value" style="font-size: 16px; color: #0f172a; font-weight: 950;">&#8377;{{ number_format($invoice->total_amount, 2) }}</span>
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <!-- Bottom Terms & Signatures -->
        <table class="footer-table">
            <tr>
                <td style="width: 60%;">
                    <span style="font-weight: 850; color: #475569; font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px;">Terms & Conditions</span>
                    <ul class="terms-list">
                        <li>All disputes subject to Rajkot jurisdiction.</li>
                        <li>Interest @18% p.a. charged on overdue invoices.</li>
                    </ul>
                </td>
                <td style="width: 40%; text-align: right;">
                    <div style="font-weight: 850; text-transform: uppercase; font-size: 8px; color: #94a3b8; letter-spacing: 0.5px;">Authorized Signatory</div>
                    <div class="signature-line" style="display: inline-block;"></div>
                    <div style="font-size: 9px; color: #64748b; font-weight: 700;">Praful Welding Works</div>
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
