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
            margin: 8mm 10mm !important;
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
            font-size: 10.5px;
            line-height: 1.4;
        }
        
        /* Control bar styling matching panel aesthetic */
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
            background-color: #2563eb;
            color: white;
        }
        .btn-primary:hover {
            background-color: #1d4ed8;
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
        
        /* Full Page Executive Invoice Box */
        .invoice-box {
            max-width: 850px;
            margin: auto;
            background-color: #ffffff;
            border: 1.5px solid #0f172a;
            border-radius: 4px;
            padding: 16px 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            box-sizing: border-box;
            width: 100%;
            height: auto;
            page-break-inside: avoid;
        }

        /* Header Layout */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 14px;
        }
        .header-table td {
            vertical-align: top;
        }
        .header-logo {
            width: 56px;
            height: 56px;
            object-fit: contain;
            border-radius: 6px;
            margin-right: 14px;
        }
        .business-title {
            font-size: 20px;
            font-weight: 900;
            color: #0f172a;
            text-transform: uppercase;
            margin: 0;
            letter-spacing: -0.3px;
        }
        .business-subtitle {
            font-size: 9px;
            color: #475569;
            margin: 2px 0 0 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 700;
        }
        .tax-invoice-badge {
            background-color: #0f172a;
            color: #ffffff;
            padding: 5px 14px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            display: inline-block;
            margin-bottom: 4px;
        }
        .invoice-number {
            font-size: 15px;
            font-weight: 800;
            color: #2563eb;
            margin: 4px 0 0 0;
            font-family: monospace;
        }

        /* 4-Column Metadata Matrix */
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
            border: 1px solid #94a3b8;
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
            background-color: #0f172a;
            color: #ffffff;
            padding: 5px 8px;
            font-size: 8.5px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.6px;
        }
        .cell-body {
            padding: 8px;
            font-size: 10px;
            line-height: 1.45;
            color: #334155;
        }
        .meta-value-bold {
            font-weight: 700;
            color: #0f172a;
        }

        /* Line Items Table */
        .items-section-wrapper {
            flex-grow: 1;
            margin-bottom: 16px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #94a3b8;
        }
        .items-table th {
            background-color: #f1f5f9;
            color: #0f172a;
            border-bottom: 1.5px solid #0f172a;
            border-right: 1px solid #cbd5e1;
            padding: 7px 8px;
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .items-table th:last-child {
            border-right: none;
        }
        .items-table td {
            padding: 7px 8px;
            border-bottom: 1px solid #e2e8f0;
            border-right: 1px solid #e2e8f0;
            font-size: 10px;
            vertical-align: middle;
        }
        .items-table td:last-child {
            border-right: none;
        }
        .items-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .item-name {
            font-weight: 700;
            color: #0f172a;
            font-size: 10.5px;
        }
        .item-sku {
            font-size: 8.5px;
            color: #64748b;
            font-family: monospace;
            margin-top: 1px;
        }
        .hsn-badge {
            background-color: #e2e8f0;
            color: #334155;
            padding: 1px 5px;
            border-radius: 3px;
            font-size: 8.5px;
            font-family: monospace;
            font-weight: 700;
        }

        /* Bottom Section: Bank Details, Tax Summary & Words */
        .bottom-section-wrapper {
            margin-top: auto;
        }
        
        .words-bar {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 6px 12px;
            margin-bottom: 12px;
            font-size: 9.5px;
            color: #1e293b;
        }
        .words-title {
            font-weight: 800;
            text-transform: uppercase;
            color: #64748b;
            font-size: 8.5px;
            margin-right: 6px;
        }

        .totals-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            border: 1px solid #94a3b8;
        }
        .totals-table td {
            vertical-align: top;
            padding: 0;
        }
        .bank-cell {
            width: 55%;
            padding: 10px 12px;
            border-right: 1px solid #cbd5e1;
            background-color: #ffffff;
        }
        .totals-cell {
            width: 45%;
            padding: 8px 12px;
            background-color: #f8fafc;
        }
        
        .bank-title {
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
            color: #0f172a;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 3px;
            margin-bottom: 6px;
            display: block;
            letter-spacing: 0.5px;
        }
        .bank-details-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5px;
        }
        .bank-details-table td {
            padding: 2px 0;
            vertical-align: middle;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 3px 0;
            font-size: 10px;
            color: #334155;
            border-bottom: 1px dashed #e2e8f0;
        }
        .total-label {
            font-weight: 600;
        }
        .total-value {
            font-weight: 700;
            color: #0f172a;
        }
        
        .grand-total-row {
            display: flex;
            justify-content: space-between;
            background-color: #0f172a;
            color: #ffffff;
            padding: 6px 10px;
            border-radius: 4px;
            margin-top: 6px;
            align-items: center;
        }

        /* Footer & Signature Table */
        .footer-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #cbd5e1;
            background-color: #ffffff;
        }
        .footer-table td {
            padding: 10px 12px;
            vertical-align: top;
        }
        .terms-title {
            font-size: 8.5px;
            font-weight: 800;
            text-transform: uppercase;
            color: #64748b;
            display: block;
            margin-bottom: 4px;
        }
        .terms-list {
            margin: 0;
            padding-left: 14px;
            font-size: 8.5px;
            color: #475569;
            line-height: 1.4;
        }
        .signature-title {
            font-size: 8.5px;
            font-weight: 800;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 30px;
        }
        .signature-line {
            border-top: 1px solid #0f172a;
            width: 140px;
            margin-left: auto;
            margin-bottom: 4px;
        }
        .computer-gen-notice {
            text-align: center;
            font-size: 8px;
            color: #94a3b8;
            margin-top: 8px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        @if(isset($isPdf) && $isPdf)
            .no-print-bar {
                display: none !important;
            }
            body {
                background-color: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .invoice-box {
                box-shadow: none !important;
                border: 1.5px solid #0f172a !important;
                padding: 12px 14px !important;
                margin: 0 auto !important;
                max-width: 100% !important;
                width: 100% !important;
            }
        @endif

        /* Print Media Overrides */
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
                box-shadow: none !important;
                border: 1.5px solid #0f172a !important;
                padding: 14px !important;
                height: auto !important;
                min-height: auto !important;
            }
        }
    </style>
</head>
<body>

    @php
        // Helper function for converting numbers to Indian Rupees Words
        if (!function_exists('numberToWordsIndianTaxInvoice')) {
            function numberToWordsIndianTaxInvoice($number) {
                $decimal = round($number - ($no = floor($number)), 2) * 100;
                $hundred = null;
                $digits_length = strlen($no);
                $i = 0;
                $str = array();
                $words = array(
                    0 => '', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five',
                    6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine', 10 => 'Ten',
                    11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
                    16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen', 19 => 'Nineteen', 20 => 'Twenty',
                    30 => 'Thirty', 40 => 'Forty', 50 => 'Fifty', 60 => 'Sixty', 70 => 'Seventy', 80 => 'Eighty', 90 => 'Ninety'
                );
                $digits = array('', 'Hundred', 'Thousand', 'Lakh', 'Crore');
                while ($i < $digits_length) {
                    $divider = ($i == 2) ? 10 : 100;
                    $number = floor($no % $divider);
                    $no = floor($no / $divider);
                    $i += $divider == 10 ? 1 : 2;
                    if ($number) {
                        $plural = (($counter = count($str)) && $number > 9) ? 's' : '';
                        $hundred = ($counter == 1 && $str[0]) ? ' and ' : '';
                        $str [] = ($number < 21) ? $words[$number] . ' ' . $digits[$counter] . $plural . ' ' . $hundred
                            : $words[floor($number / 10) * 10] . ' ' . $words[$number % 10] . ' ' . $digits[$counter] . $plural . ' ' . $hundred;
                    } else {
                        $str[] = null;
                    }
                }
                $Rupees = implode('', array_reverse($str));
                $paise = ($decimal > 0) ? " and " . ($words[$decimal - $decimal%10] . " " . $words[$decimal%10]) . ' Paise' : '';
                return ($Rupees ? 'Rupees ' . trim($Rupees) : 'Rupees Zero') . $paise . ' Only';
            }
        }

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

        $plant = $invoice->plant;
        $client = $plant ? $plant->client : null;
        $pState = $plant->state ?? 'Gujarat';
        $pCode = printResolveStateCode($pState);
        $billedAddress = (!empty($plant->shipping_address)) ? $plant->shipping_address : ($client->corporate_address ?? 'N/A');
        $billedGst = (!empty($plant->gst_number)) ? $plant->gst_number : ($client->gst_number ?? 'N/A');
        $sellerAddress = \App\Models\Setting::get('address', trim(\App\Models\Setting::get('address_line_1', 'Plot No. 12, G.I.D.C. Metoda,') . ' ' . \App\Models\Setting::get('address_line_2', 'Rajkot, Gujarat - 360021')));
        $sellerEmail = \App\Models\Setting::get('business_email', 'pww@example.com');
        $amountInWords = numberToWordsIndianTaxInvoice($invoice->total_amount);
    @endphp

    @if(!isset($isPdf) || !$isPdf)
    <!-- Top Action Control Bar -->
    <div class="no-print-bar">
        <div class="control-left">
            <a href="{{ route('invoices') }}" onclick="handleBackToERP(event)" class="btn btn-secondary" style="margin-right: 12px; text-decoration: none; font-weight: 700; color: #0f172a; border: 1px solid #cbd5e1;">← Back to System</a>
            @if($invoice->invoice_mode === 'raw_material')
                <span style="font-weight: 800; font-size: 13px; color: #b45309;">Raw Material / Scrap Sale Voucher</span>
                <span style="color: #64748b; font-size: 11px; margin-left: 8px;">| {{ $invoice->custom_client_name ?? 'Buyer' }}</span>
            @else
                <span style="font-weight: 800; font-size: 13px; color: #0f172a;">Tax Invoice #{{ $invoice->invoice_number }}</span>
                <span style="color: #64748b; font-size: 11px; margin-left: 8px;">| {{ $client->company_name ?? 'Client' }}</span>
            @endif
        </div>
        <div class="control-right">
            <button onclick="window.print()" class="btn btn-primary">🖨️ {{ $invoice->invoice_mode === 'raw_material' ? 'Print Sale Bill' : 'Print Invoice' }}</button>
            <a href="{{ route('invoice.download', $invoice->id) }}" class="btn btn-secondary">📥 Download PDF</a>
        </div>
    </div>
    @endif

    <!-- Main Printable Frame -->
    <div class="invoice-box">
        <div>
            <!-- Header Section -->
            <table class="header-table">
                <tr>
                    <td style="width: 60%;">
                        <table style="border-collapse: collapse;">
                            <tr>
                                <td style="vertical-align: middle;">
                                    @php
                                        $logoPath = \App\Models\Setting::get('logo_path', 'logo.jpg');
                                        $fullLogoPath = public_path($logoPath);
                                        if (file_exists($fullLogoPath) && is_file($fullLogoPath)) {
                                            $logoData = base64_encode(file_get_contents($fullLogoPath));
                                            $logoSrc = 'data:image/' . pathinfo($fullLogoPath, PATHINFO_EXTENSION) . ';base64,' . $logoData;
                                        } else {
                                            $logoSrc = asset($logoPath);
                                        }
                                    @endphp
                                    <img src="{{ $logoSrc }}" alt="Logo" class="header-logo">
                                </td>
                                <td style="vertical-align: middle;">
                                    <h1 class="business-title">{{ \App\Models\Setting::get('business_name', 'Praful Welding Works') }}</h1>
                                    <p class="business-subtitle">{{ \App\Models\Setting::get('business_subtitle', 'Heavy Fabrication & Industrial Racks ERP') }}</p>
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td style="width: 40%; text-align: right; vertical-align: top;">
                        @if($invoice->invoice_mode === 'raw_material')
                            <div class="tax-invoice-badge" style="background-color: #d97706; color: white;">RAW MATERIAL SALE MEMO</div>
                        @else
                            <div class="tax-invoice-badge">Tax Invoice</div>
                            <div class="invoice-number">{{ $invoice->invoice_number }}</div>
                        @endif
                    </td>
                </tr>
            </table>

            <!-- 4-Column Party Metadata Block -->
            <table class="meta-table">
                <tr>
                    <td>
                        <div class="cell-header">Seller (Issued By)</div>
                        <div class="cell-body">
                            <span class="meta-value-bold">{{ \App\Models\Setting::get('business_name', 'Praful Welding Works') }}</span><br>
                            {{ $sellerAddress }}<br>
                            Email: <span class="meta-value-bold">{{ $sellerEmail }}</span><br>
                            <span style="font-weight: 700; color: #0f172a; font-family: monospace;">GSTIN: {{ \App\Models\Setting::get('gstin', '24PWWRK1234A1Z0') }}</span><br>
                            @php $msme = \App\Models\Setting::get('msme_number', 'UDYAM-GJ-24-0012345'); @endphp
                            @if(!empty($msme))
                                <span style="font-weight: 700; color: #0f172a; font-family: monospace;">MSME: {{ $msme }}</span><br>
                            @endif
                            State: <span class="meta-value-bold">Gujarat (24)</span>
                        </div>
                    </td>
                    <td>
                        <div class="cell-header">Billed To (Buyer)</div>
                        <div class="cell-body">
                            <span class="meta-value-bold">{{ $invoice->custom_client_name ?? ($client->company_name ?? 'N/A') }}</span><br>
                            @if(!empty($invoice->custom_client_name))
                                <span style="font-weight: 700; color: #0f172a; font-family: monospace;">GSTIN: {{ !empty($invoice->custom_buyer_gstin) ? $invoice->custom_buyer_gstin : 'URP (Unregistered Buyer)' }}</span><br>
                            @else
                                {{ $billedAddress }}<br>
                                <span style="font-weight: 700; color: #0f172a; font-family: monospace;">GSTIN: {{ $billedGst }}</span><br>
                            @endif
                            State: <span class="meta-value-bold">{{ $pState }} ({{ $pCode }})</span>
                        </div>
                    </td>
                    <td>
                        <div class="cell-header">Shipped To (Consignee)</div>
                        <div class="cell-body">
                            <span class="meta-value-bold">{{ $plant->plant_name ?? 'N/A' }}</span><br>
                            {{ $plant->shipping_address ?? 'N/A' }}<br>
                            <span style="font-weight: 700; color: #0f172a; font-family: monospace;">GSTIN: {{ $billedGst }}</span><br>
                            State: <span class="meta-value-bold">{{ $pState }} ({{ $pCode }})</span>
                        </div>
                    </td>
                    <td>
                        <div class="cell-header">Invoice & Transport</div>
                        <div class="cell-body">
                            Date: <span class="meta-value-bold">{{ \Carbon\Carbon::parse($invoice->invoice_date ?? $invoice->created_at)->format('d M Y') }}</span><br>
                            Due Date: <span class="meta-value-bold">{{ \Carbon\Carbon::parse($invoice->due_date ?? ($invoice->invoice_date ? \Carbon\Carbon::parse($invoice->invoice_date)->addDays(30) : now()))->format('d M Y') }}</span><br>
                            @if(!empty($invoice->vehicle_number))
                                Vehicle No: <span class="meta-value-bold" style="font-family: monospace;">{{ $invoice->vehicle_number }}</span><br>
                            @endif
                            Place of Supply: <span class="meta-value-bold">{{ $pState }} ({{ $pCode }})</span>
                        </div>
                    </td>
                </tr>
            </table>

            <!-- Line Items Table -->
            <div class="items-section-wrapper">
                <table class="items-table">
                    <thead>
                        <tr>
                            <th style="text-align: center; width: 4%;">#</th>
                            <th style="text-align: left; width: 40%;">Item Description / SKU</th>
                            <th style="text-align: center; width: 10%;">HSN/SAC</th>
                            <th style="text-align: right; width: 9%;">QTY</th>
                            <th style="text-align: center; width: 6%;">UOM</th>
                            <th style="text-align: right; width: 11%;">Rate (&#8377;)</th>
                            <th style="text-align: center; width: 8%;">GST %</th>
                            <th style="text-align: right; width: 12%;">Taxable (&#8377;)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $groupedItems = isset($groupedItems) ? $groupedItems : ($invoice->items ?? []);
                        @endphp
                        @foreach ($groupedItems as $index => $item)
                            @php
                                $isRaw = ($item->item_type === 'raw_material');
                                $rawMat = $item->rawMaterial ?? null;
                                $pGood = $item->finishedGood ?? $item->product ?? null;
                                
                                $pName = $item->item_name ?? ($isRaw ? ($rawMat->material_name ?? 'Raw Material') : ($pGood->product_name ?? 'Product'));
                                $pSku = $isRaw ? ('RM-' . ($item->raw_material_id ?? '0')) : (isset($item->sku) ? $item->sku : ($pGood->sku ?? 'N/A'));
                                $pHsn = $isRaw ? '72040000' : ($pGood->hsn_code ?? '73089090');
                                $pUom = $item->billing_uom ?? ($isRaw ? ($rawMat->unit ?? 'kg') : ($pGood->uom ?? 'piece'));
                                $pGst = $isRaw ? 18.00 : ($pGood->gst_rate ?? 18.00);
                                $pTotal = isset($item->total) ? $item->total : ($item->total_price ?? ($item->quantity * $item->unit_price));

                                $qtyVal = (float)$item->quantity;
                                $qtyFormatted = ($qtyVal == (int)$qtyVal) ? number_format($qtyVal) : number_format($qtyVal, 2);
                                
                                $unitWeight = ($pGood && ($pGood->unit_weight_kg ?? 0) > 0) ? (float)$pGood->unit_weight_kg : 0;
                                
                                $uomLower = strtolower($pUom);
                                $unitConversionNotice = '';
                                
                                if (!$isRaw && $unitWeight > 0) {
                                    if ($uomLower === 'kg') {
                                        $pcsCount = $qtyVal / $unitWeight;
                                        $pcsFormatted = ($pcsCount == (int)$pcsCount) ? number_format($pcsCount) : number_format($pcsCount, 1);
                                        $unitConversionNotice = "≈ {$pcsFormatted} Pcs (@ {$unitWeight} Kg/pc)";
                                    } elseif ($uomLower === 'pcs' || $uomLower === 'piece') {
                                        $totalWt = $qtyVal * $unitWeight;
                                        $wtFormatted = ($totalWt == (int)$totalWt) ? number_format($totalWt) : number_format($totalWt, 2);
                                        $unitConversionNotice = "Total Wt: {$wtFormatted} Kg (@ {$unitWeight} Kg/pc)";
                                    }
                                }
                            @endphp
                            <tr>
                                <td style="text-align: center; font-weight: 700; color: #64748b;">{{ $index + 1 }}</td>
                                <td>
                                    <div class="item-name">{{ $pName }}</div>
                                    <div class="item-sku">SKU: {{ $pSku }}</div>
                                </td>
                                <td style="text-align: center;">
                                    <span class="hsn-badge">{{ $pHsn }}</span>
                                </td>
                                <td style="text-align: right; font-weight: 700; color: #0f172a;">
                                    <div>{{ $qtyFormatted }}</div>
                                    @if($unitConversionNotice)
                                        <div style="font-size: 8px; font-weight: 700; color: #475569; margin-top: 2px;">{{ $unitConversionNotice }}</div>
                                    @endif
                                </td>
                                <td style="text-align: center; font-weight: 700; color: #475569; text-transform: uppercase;">
                                    {{ $pUom }}
                                </td>
                                <td style="text-align: right;">&#8377;{{ number_format($item->unit_price, 2) }}</td>
                                <td style="text-align: center; font-weight: 700; color: #2563eb;">{{ number_format($pGst, 1) }}%</td>
                                <td style="text-align: right; font-weight: 700; color: #0f172a;">&#8377;{{ number_format($pTotal, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Bottom Summary, Bank Details & Footer Signatures -->
        <div class="bottom-section-wrapper">
            <!-- Amount in Words -->
            <div class="words-bar">
                <span class="words-title">Amount in Words:</span>
                <span style="font-weight: 700; font-style: italic; color: #0f172a;">{{ $amountInWords }}</span>
            </div>

            <!-- Settlement Bank & Totals Block -->
            <table class="totals-table">
                <tr>
                    <td class="bank-cell">
                        <span class="bank-title">Settlement Bank Account Details</span>
                        <table class="bank-details-table">
                            <tr>
                                <td style="font-weight: 700; color: #64748b; width: 95px;">Bank Name:</td>
                                <td style="color: #0f172a; font-weight: 700;">{{ \App\Models\Setting::get('bank_name', 'State Bank of India (SBI)') }}</td>
                            </tr>
                            <tr>
                                <td style="font-weight: 700; color: #64748b;">Account Name:</td>
                                <td style="color: #0f172a; font-weight: 700;">{{ \App\Models\Setting::get('bank_account_name', 'Praful Welding Works') }}</td>
                            </tr>
                            <tr>
                                <td style="font-weight: 700; color: #64748b;">Account No:</td>
                                <td style="font-weight: 800; color: #0f172a; font-family: monospace;">{{ \App\Models\Setting::get('bank_account_no', '33445566778') }}</td>
                            </tr>
                            <tr>
                                <td style="font-weight: 700; color: #64748b;">IFSC Code:</td>
                                <td style="font-weight: 800; color: #0f172a; font-family: monospace;">{{ \App\Models\Setting::get('bank_ifsc', 'SBIN0001234') }}</td>
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
                                <span class="total-label">IGST Total:</span>
                                <span class="total-value">&#8377;{{ number_format($invoice->igst, 2) }}</span>
                            </div>
                        @else
                            <div class="total-row">
                                <span class="total-label">CGST Total:</span>
                                <span class="total-value">&#8377;{{ number_format($invoice->cgst, 2) }}</span>
                            </div>
                            <div class="total-row">
                                <span class="total-label">SGST Total:</span>
                                <span class="total-value">&#8377;{{ number_format($invoice->sgst, 2) }}</span>
                            </div>
                        @endif

                        <div class="grand-total-row">
                            <span class="total-label" style="font-weight: 800; font-size: 11px;">Total Amount (Incl. Tax):</span>
                            <span class="total-value" style="font-size: 14px; color: #ffffff; font-weight: 900;">&#8377;{{ number_format($invoice->total_amount, 2) }}</span>
                        </div>
                    </td>
                </tr>
            </table>

            <!-- Footer Terms & Signatures -->
            <table class="footer-table">
                <tr>
                    <td style="width: 60%;">
                        <span class="terms-title">Terms & Conditions</span>
                        @php
                            $defaultTerms = "1. All disputes are subject to Rajkot jurisdiction.\n2. Interest @18% p.a. charged on overdue payments after due date.\n3. Goods once dispatched/sold cannot be returned or exchanged.";
                            $rawTerms = \App\Models\Setting::get('terms_and_conditions', $defaultTerms);
                            $termsLines = array_filter(array_map('trim', explode("\n", $rawTerms)));
                        @endphp
                        <ul class="terms-list">
                            @foreach($termsLines as $line)
                                <li>{{ $line }}</li>
                            @endforeach
                        </ul>
                    </td>
                    <td style="width: 40%; text-align: right; vertical-align: bottom;">
                        <div class="signature-title">Authorized Signatory</div>
                        @php
                            $sigPath = \App\Models\Setting::get('signature_path');
                            $hasSig = false;
                            $sigSrc = '';
                            if ($sigPath) {
                                $fullSigPath = public_path($sigPath);
                                if (file_exists($fullSigPath) && is_file($fullSigPath)) {
                                    $sigData = base64_encode(file_get_contents($fullSigPath));
                                    $sigSrc = 'data:image/' . pathinfo($fullSigPath, PATHINFO_EXTENSION) . ';base64,' . $sigData;
                                    $hasSig = true;
                                } else {
                                    $sigSrc = asset($sigPath);
                                    $hasSig = true;
                                }
                            }
                        @endphp
                        @if($hasSig)
                            <div style="height: 48px; margin: 4px 0; text-align: right;">
                                <img src="{{ $sigSrc }}" alt="Signature Stamp" style="max-height: 44px; max-width: 140px; object-fit: contain; display: inline-block;">
                            </div>
                        @else
                            <div class="signature-line"></div>
                        @endif
                        <div style="font-size: 9.5px; color: #0f172a; font-weight: 800; text-transform: uppercase;">{{ \App\Models\Setting::get('business_name', 'Praful Welding Works') }}</div>
                    </td>
                </tr>
            </table>

            <div class="computer-gen-notice">
                This is a computer-generated tax invoice • Issued under GST Rules, 2017
            </div>
        </div>
    </div>

    <script>
        function handleBackToERP(e) {
            if (window.opener && !window.opener.closed) {
                try {
                    window.opener.focus();
                    window.close();
                    e.preventDefault();
                    return false;
                } catch(err) {}
            }
            if (window.history.length > 1 && document.referrer && document.referrer.includes(window.location.host)) {
                e.preventDefault();
                window.history.back();
                return false;
            }
            // Fallback: Let default link href navigate to route('invoices')
        }

        window.addEventListener('load', function() {
            if (!window.location.href.includes('download')) {
                setTimeout(function() {
                    window.print();
                }, 300);
            }
        });
    </script>
</body>
</html>
