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

        /* PDF and Print Optimized Stylesheet */
        @page {
            size: A4 portrait;
            margin: 15px !important;
        }
        
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Outfit', 'DejaVu Sans', sans-serif;
            color: #0f172a;
            background-color: #f1f5f9;
            margin: 0;
            padding: 15px;
            font-size: 11px;
            line-height: 1.4;
        }
        
        /* Control bar styling matching exactly the mockup */
        .no-print-bar {
            background-color: #ffffff;
            border: 1px solid #cbd5e1;
            padding: 12px 24px;
            border-radius: 12px;
            margin: 0 auto 20px auto;
            max-width: 850px;
            display: table;
            width: 100%;
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
        .btn {
            padding: 8px 18px;
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
        
        /* Full Page Invoice Box with Clean Simple Outer Border */
        .invoice-box {
            max-width: 850px;
            margin: auto;
            background-color: #ffffff;
            border: 1px solid #94a3b8;
            padding: 24px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            box-sizing: border-box;
            width: 100%;
            min-height: 275mm;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        /* Top Header Grid */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 14px;
        }
        .header-table td {
            vertical-align: top;
        }
        .header-logo {
            width: 52px;
            height: 52px;
            object-fit: contain;
            border-radius: 6px;
            margin-right: 12px;
        }
        .business-title {
            font-size: 18px;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            margin: 0;
            letter-spacing: -0.3px;
        }
        .business-subtitle {
            font-size: 9px;
            color: #64748b;
            margin: 2px 0 0 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }
        .invoice-title {
            font-size: 16px;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 0 0 4px 0;
        }
        .invoice-number {
            font-size: 15px;
            font-weight: 700;
            color: #2563eb;
            margin: 0;
        }

        /* Metadata 4-Column Grid with Simple Borders */
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
            border: 1px solid #cbd5e1;
        }
        .meta-table td {
            width: 25%;
            vertical-align: top;
            padding: 0;
            border-right: 1px solid #cbd5e1;
        }
        .meta-table td:last-child {
            border-right: none;
        }
        .cell-header {
            background-color: #f8fafc;
            border-bottom: 1px solid #cbd5e1;
            padding: 6px 10px;
            font-size: 8.5px;
            font-weight: 800;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .cell-body {
            padding: 8px 10px;
            font-size: 10.5px;
            color: #334155;
            line-height: 1.5;
        }
        .meta-value-bold {
            font-weight: 700;
            color: #0f172a;
        }

        /* Middle Items Section Wrapper */
        .items-section-wrapper {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            margin-bottom: 18px;
        }

        /* Items Table with Clean Simple Borders */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #cbd5e1;
        }
        .items-table th {
            background-color: #f8fafc;
            color: #334155;
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
            padding: 8px 10px;
            letter-spacing: 0.5px;
            border-right: 1px solid #cbd5e1;
            border-bottom: 1px solid #cbd5e1;
        }
        .items-table th:last-child {
            border-right: none;
        }
        .items-table td {
            padding: 10px;
            border-right: 1px solid #e2e8f0;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: middle;
            font-size: 11px;
        }
        .items-table td:last-child {
            border-right: none;
        }
        .item-name {
            font-weight: 700;
            color: #0f172a;
            font-size: 11px;
        }
        .item-sku {
            font-size: 8.5px;
            color: #64748b;
            font-weight: 600;
            margin-top: 2px;
            text-transform: uppercase;
        }

        /* Bottom Section Wrapper */
        .bottom-section-wrapper {
            margin-top: auto;
        }

        /* Summary & Totals Block with Simple Grid Borders */
        .totals-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #cbd5e1;
            margin-bottom: 16px;
        }
        .totals-table td {
            vertical-align: top;
            padding: 12px 14px;
        }
        .bank-cell {
            width: 55%;
            border-right: 1px solid #cbd5e1;
            background-color: #fafafa;
        }
        .totals-cell {
            width: 45%;
            background-color: #ffffff;
        }
        .bank-title {
            font-weight: 800;
            color: #334155;
            text-transform: uppercase;
            font-size: 8.5px;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
            display: block;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 4px;
        }
        .bank-details-table {
            width: 100%;
            border-collapse: collapse;
        }
        .bank-details-table td {
            padding: 2.5px 0;
            font-size: 10px;
        }
        .total-row {
            display: table;
            width: 100%;
            margin-bottom: 6px;
            font-size: 10.5px;
            color: #475569;
            font-weight: 600;
        }
        .total-label {
            display: table-cell;
            text-align: left;
        }
        .total-value {
            display: table-cell;
            text-align: right;
            font-weight: 700;
            color: #0f172a;
        }
        .grand-total-row {
            display: table;
            width: 100%;
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            padding: 8px 10px;
            margin-top: 8px;
            font-size: 12.5px;
            font-weight: 800;
            color: #0f172a;
            border-radius: 4px;
        }

        /* Footer Terms & Signatures */
        .footer-table {
            width: 100%;
            border-collapse: collapse;
            border-top: 1px solid #cbd5e1;
            padding-top: 12px;
        }
        .footer-table td {
            padding-top: 10px;
            vertical-align: bottom;
            font-size: 9px;
            color: #64748b;
        }
        .terms-title {
            font-weight: 800;
            color: #334155;
            font-size: 8.5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: block;
            margin-bottom: 4px;
        }
        .terms-list {
            margin: 0;
            padding-left: 12px;
            font-size: 8.5px;
            color: #64748b;
            line-height: 1.4;
        }
        .signature-title {
            font-weight: 800;
            text-transform: uppercase;
            font-size: 8px;
            color: #475569;
            letter-spacing: 0.5px;
        }
        .signature-line {
            width: 130px;
            border-bottom: 1px solid #0f172a;
            margin-top: 30px;
            margin-bottom: 4px;
            display: inline-block;
        }
        
        /* Print and PDF overrides */
        @media print {
            .no-print-bar {
                display: none !important;
            }
            body {
                background-color: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .invoice-box {
                border: 1px solid #64748b !important;
                box-shadow: none !important;
                max-width: 100% !important;
                width: 100% !important;
                min-height: 275mm !important;
                border-radius: 0 !important;
                padding: 20px !important;
            }
        }

        @if(isset($isPdf) && $isPdf)
            @page {
                margin: 8mm !important;
            }
            body {
                background-color: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .invoice-box {
                border: 1px solid #64748b !important;
                box-shadow: none !important;
                max-width: 100% !important;
                width: 100% !important;
                min-height: 275mm !important;
                border-radius: 0 !important;
                background-color: #ffffff !important;
                padding: 20px !important;
            }
        @endif
    </style>
</head>
<body>

    @if(!isset($isPdf) || !$isPdf)
    <!-- Print Control Bar (Hidden on print) -->
    <div class="no-print-bar">
        <div class="control-left">
            <span style="font-weight: 800; font-size: 13px; color: #0f172a;">Tax Invoice View (A4 Print-Optimized)</span>
        </div>
        <div class="control-right">
            <button onclick="window.print()" class="btn btn-primary">Print / Save as PDF</button>
            <button onclick="window.close()" class="btn btn-secondary">Close</button>
        </div>
    </div>
    @endif

    <!-- Main Invoice Document -->
    <div class="invoice-box">
        
        <div>
            <!-- Header Table -->
            <table class="header-table">
                <tr>
                    <td style="width: 60%;">
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
                    <td style="width: 40%; text-align: right;">
                        <h2 class="invoice-title">Tax Invoice</h2>
                        <p class="invoice-number">{{ $invoice->invoice_number }}</p>
                    </td>
                </tr>
            </table>

            @php
            if (!function_exists('printResolveStateCode')) {
                function printResolveStateCode($stateName) {
                    $map = [
                        'Jammu & Kashmir' => '01', 'Himachal Pradesh' => '02', 'Punjab' => '03', 'Chandigarh' => '04',
                        'Uttarakhand' => '05', 'Haryana' => '06', 'Delhi' => '07', 'Rajasthan' => '08',
                        'Uttar Pradesh' => '09', 'Bihar' => '10', 'Sikkim' => '11', 'Arunachal Pradesh' => '12',
                        'Nagaland' => '13', 'Manipur' => '14', 'Mizoram' => '15', 'Tripura' => '16',
                        'Meghalaya' => '17', 'Assam' => '18', 'West Bengal' => '19', 'Jharkhand' => '20',
                        'Odisha' => '21', 'Chhattisgarh' => '22', 'Madhya Pradesh' => '23', 'Gujarat' => '24',
                        'Daman & Diu' => '25', 'Dadra & Nagar Haveli' => '26', 'Maharashtra' => '27',
                        'Andhra Pradesh' => '37', 'Karnataka' => '29', 'Goa' => '30', 'Lakshadweep' => '31',
                        'Kerala' => '32', 'Tamil Nadu' => '33', 'Puducherry' => '34', 'Andaman & Nicobar Islands' => '35',
                        'Telangana' => '36', 'Ladakh' => '38',
                    ];
                    return $map[trim($stateName ?? '')] ?? '24';
                }
            }
            $pState = $plant->state ?? 'Gujarat';
            $pCode = printResolveStateCode($pState);
            $billedAddress = (!empty($plant->shipping_address)) ? $plant->shipping_address : ($client->corporate_address ?? 'N/A');
            $billedGst = (!empty($plant->gst_number)) ? $plant->gst_number : ($client->gst_number ?? 'N/A');
            @endphp

            <!-- Metadata Block (Simple Clean Grid with Borders) -->
            <table class="meta-table">
                <tr>
                    <td>
                        <div class="cell-header">Seller (Issued By)</div>
                        <div class="cell-body">
                            <span class="meta-value-bold">{{ \App\Models\Setting::get('business_name', 'Praful Welding Works') }}</span><br>
                            {{ \App\Models\Setting::get('address_line_1', 'Plot No. 12, G.I.D.C. Metoda,') }}<br>
                            {{ \App\Models\Setting::get('address_line_2', 'Rajkot, Gujarat - 360021') }}<br>
                            <span style="font-weight: 700; color: #334155; font-family: monospace;">GSTIN: {{ \App\Models\Setting::get('gstin', '24PWWRK1234A1Z0') }}</span><br>
                            @php $msme = \App\Models\Setting::get('msme_number', 'UDYAM-GJ-24-0012345'); @endphp
                            @if(!empty($msme))
                                <span style="font-weight: 700; color: #334155; font-family: monospace;">MSME NO: {{ $msme }}</span><br>
                            @endif
                            State: <span class="meta-value-bold">Gujarat (24)</span>
                        </div>
                    </td>
                    <td>
                        <div class="cell-header">Billed To (Buyer)</div>
                        <div class="cell-body">
                            <span class="meta-value-bold">{{ $client->company_name ?? 'N/A' }}</span><br>
                            {{ $billedAddress }}<br>
                            <span style="font-weight: 700; color: #334155; font-family: monospace;">GSTIN: {{ $billedGst }}</span><br>
                            State: <span class="meta-value-bold">{{ $pState }} ({{ $pCode }})</span>
                        </div>
                    </td>
                    <td>
                        <div class="cell-header">Shipped To (Consignee)</div>
                        <div class="cell-body">
                            <span class="meta-value-bold">{{ $plant->plant_name ?? 'N/A' }}</span><br>
                            {{ $plant->shipping_address ?? 'N/A' }}<br>
                            <span style="font-weight: 700; color: #334155; font-family: monospace;">GSTIN: {{ $billedGst }}</span><br>
                            State: <span class="meta-value-bold">{{ $pState }} ({{ $pCode }})</span>
                        </div>
                    </td>
                    <td>
                        <div class="cell-header">Invoice Details</div>
                        <div class="cell-body">
                            Invoice Date: <span class="meta-value-bold">{{ \Carbon\Carbon::parse($invoice->invoice_date ?? $invoice->created_at)->format('d M Y') }}</span><br>
                            @if(!empty($invoice->vehicle_number))
                                Vehicle No: <span class="meta-value-bold" style="font-family: monospace;">{{ $invoice->vehicle_number }}</span><br>
                            @endif
                            Place of Supply: <span class="meta-value-bold">{{ $pState }} ({{ $pCode }})</span>
                        </div>
                    </td>
                </tr>
            </table>

            <!-- Items Table Section -->
            <div class="items-section-wrapper">
                <table class="items-table">
                    <thead>
                        <tr>
                            <th style="text-align: center; width: 6%;">#</th>
                            <th style="text-align: left; width: 44%;">Item Description / SKU</th>
                            <th style="text-align: right; width: 14%;">Quantity</th>
                            <th style="text-align: right; width: 18%;">Unit Rate (&#8377;)</th>
                            <th style="text-align: right; width: 18%;">Taxable Value (&#8377;)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($groupedItems as $index => $item)
                            <tr>
                                <td style="text-align: center; font-weight: 600; color: #64748b;">{{ $index + 1 }}</td>
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
            </div>
        </div>

        <!-- Bottom Section (Anchored cleanly at the bottom of full page A4 frame) -->
        <div class="bottom-section-wrapper">
            <!-- Summary & Totals Block -->
            <table class="totals-table">
                <tr>
                    <td class="bank-cell">
                        <span class="bank-title">Settlement Bank Accounts</span>
                        <table class="bank-details-table">
                            <tr>
                                <td style="font-weight: 700; color: #64748b; width: 90px;">Bank Name:</td>
                                <td style="color: #0f172a; font-weight: 600;">{{ \App\Models\Setting::get('bank_name', 'State Bank of India (SBI)') }}</td>
                            </tr>
                            <tr>
                                <td style="font-weight: 700; color: #64748b;">Account Name:</td>
                                <td style="color: #0f172a; font-weight: 600;">{{ \App\Models\Setting::get('bank_account_name', 'Praful Welding Works') }}</td>
                            </tr>
                            <tr>
                                <td style="font-weight: 700; color: #64748b;">A/C No:</td>
                                <td style="font-weight: 700; color: #0f172a; font-family: monospace;">{{ \App\Models\Setting::get('bank_account_no', '33445566778') }}</td>
                            </tr>
                            <tr>
                                <td style="font-weight: 700; color: #64748b;">IFSC Code:</td>
                                <td style="font-weight: 700; color: #0f172a; font-family: monospace;">{{ \App\Models\Setting::get('bank_ifsc', 'SBIN0001234') }}</td>
                            </tr>
                        </table>
                    </td>
                    <td class="totals-cell">
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
                            <span class="total-label" style="font-weight: 800;">Total Amount:</span>
                            <span class="total-value" style="font-size: 14px; color: #0f172a; font-weight: 900;">&#8377;{{ number_format($invoice->total_amount, 2) }}</span>
                        </div>
                    </td>
                </tr>
            </table>

            <!-- Footer Terms & Signatures -->
            <table class="footer-table">
                <tr>
                    <td style="width: 60%;">
                        <span class="terms-title">Terms & Conditions</span>
                        <ul class="terms-list">
                            <li>All disputes subject to Rajkot jurisdiction.</li>
                            <li>Interest @18% p.a. charged on overdue invoices.</li>
                            <li>Goods once sold will not be taken back or exchanged.</li>
                        </ul>
                    </td>
                    <td style="width: 40%; text-align: right;">
                        <div class="signature-title">Authorized Signatory</div>
                        <div class="signature-line"></div>
                        <div style="font-size: 9.5px; color: #0f172a; font-weight: 700;">Praful Welding Works</div>
                    </td>
                </tr>
            </table>
        </div>

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
