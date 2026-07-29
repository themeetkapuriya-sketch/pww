<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Tax Invoice - {{ $invoice->invoice_number }}</title>
    <!-- Outfit Font for browser rendering -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;950&display=swap" rel="stylesheet">
    
    <style>
        @font-face {
            font-family: 'Outfit';
            font-style: normal;
            font-weight: 400;
            src: url('{{ asset("fonts/outfit/Outfit-Regular.ttf") }}') format('truetype');
        }
        @font-face {
            font-family: 'Outfit';
            font-style: normal;
            font-weight: 600;
            src: url('{{ asset("fonts/outfit/Outfit-SemiBold.ttf") }}') format('truetype');
        }
        @font-face {
            font-family: 'Outfit';
            font-style: normal;
            font-weight: 700;
            src: url('{{ asset("fonts/outfit/Outfit-Bold.ttf") }}') format('truetype');
        }

        /* PDF and Print Optimized Stylesheet */
        @page {
            size: A4 portrait;
            margin: 4mm 5mm !important;
        }
        
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Outfit', sans-serif;
            color: #0f172a;
            background-color: #f1f5f9;
            margin: 0;
            padding: 15px;
            font-size: 12.5px;
            line-height: 1.45;
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
            border: 1.5px solid #334155;
            border-radius: 4px;
            padding: 14px 16px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            box-sizing: border-box;
            width: 100%;
            min-height: 285mm;
            display: flex;
            flex-direction: column;
            justify-content: flex-start !important;
            page-break-inside: avoid;
        }

        /* Header Layout */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            border-bottom: 2px solid #334155;
            padding-bottom: 14px;
        }
        .header-table td {
            vertical-align: top;
        }
        .header-logo {
            width: 58px;
            height: 58px;
            object-fit: contain;
            border-radius: 6px;
            margin-right: 14px;
        }
        .business-title {
            font-size: 24px;
            font-weight: 900;
            color: #0f172a;
            text-transform: uppercase;
            margin: 0;
            letter-spacing: -0.3px;
        }
        .business-subtitle {
            font-size: 11px;
            color: #475569;
            margin: 2px 0 0 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 700;
        }
        .tax-invoice-title {
            color: #0f172a;
            font-size: 18px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 2px;
            display: block;
        }
        .invoice-number {
            font-size: 16px;
            font-weight: 800;
            color: #0f172a;
            margin: 2px 0 0 0;
            font-family: monospace;
        }

        /* 3-Column Metadata Matrix */
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
            border: 1px solid #475569;
        }
        .meta-table td {
            vertical-align: top;
            padding: 0;
            border-right: 1px solid #475569;
        }
        .meta-table td:last-child {
            border-right: none;
        }
        .cell-header {
            background-color: #C8D1DD;
            color: #0f172a;
            padding: 7px 12px;
            font-size: 14px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            border-bottom: 1px solid #475569;
        }
        .cell-body {
            padding: 10px 12px;
            font-size: 14px;
            line-height: 1.5;
            color: #1e293b;
        }
        .meta-value-bold {
            font-weight: 800;
            color: #0f172a;
        }

        /* Line Items Table */
        .items-section-wrapper {
            flex: 1 1 auto;
            display: flex;
            flex-direction: column;
            margin-bottom: 0;
        }
        .items-table {
            width: 100%;
            height: 100%;
            border-collapse: collapse;
            border: 1px solid #475569;
        }
        .items-table th {
            background-color: #C8D1DD;
            color: #0f172a;
            border-bottom: 1px solid #475569;
            border-right: 1px solid #475569;
            padding: 8px 10px;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .items-table th:last-child {
            border-right: none;
        }
        .items-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #475569;
            border-right: 1px solid #475569;
            font-size: 11.5px;
            vertical-align: middle;
        }
        .items-table td:last-child {
            border-right: none;
        }
        .items-table tbody tr:last-child td {
            border-bottom: 1px solid #475569;
        }
        .empty-filler-row td {
            height: 22px;
            border-bottom: none;
            border-right: 1px solid #475569;
            background-color: #ffffff;
        }
        .empty-filler-row td:last-child {
            border-right: none;
        }
        .empty-filler-row:last-child td {
            border-bottom: 1px solid #475569;
        }
        .item-name {
            font-weight: 800;
            color: #0f172a;
            font-size: 13.5px;
            text-transform: uppercase;
        }
        .item-sku {
            font-size: 11px;
            color: #475569;
            font-style: italic;
            margin-top: 2px;
        }
        .hsn-text {
            color: #1e293b;
            font-size: 11px;
            font-weight: 700;
        }

        /* Bottom Section: Bank Details, Tax Summary & Words */
        .bottom-section-wrapper {
            margin-top: 0;
        }
        
        .words-bar {
            background-color: #ffffff;
            border: 1px solid #475569;
            border-radius: 4px;
            padding: 7px 12px;
            margin-bottom: 12px;
            font-size: 11px;
            color: #0f172a;
        }
        .words-title {
            font-weight: 800;
            text-transform: uppercase;
            color: #475569;
            font-size: 9.5px;
            margin-right: 6px;
        }

        .totals-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            border: 1px solid #475569;
            page-break-inside: avoid !important;
        }
        .totals-table td {
            vertical-align: top;
            padding: 0;
        }
        .bank-cell {
            width: 55%;
            padding: 0;
            border-right: 1px solid #475569;
            background-color: #ffffff;
        }
        .totals-cell {
            width: 45%;
            padding: 12px 18px;
            background-color: #ffffff;
        }
        
        .bank-title {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            color: #0f172a;
            background-color: #C8D1DD;
            padding: 6px 10px;
            margin: 0;
            border-bottom: 1px solid #475569;
            display: block;
            letter-spacing: 0.5px;
        }
        .bank-details-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            padding: 8px 10px;
        }
        .bank-details-table td {
            padding: 3px 10px;
            vertical-align: middle;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 5px 2px;
            font-size: 11.5px;
            color: #334155;
            border-bottom: 1px dashed #cbd5e1;
        }
        .total-label {
            font-weight: 700;
        }
        .total-value {
            font-weight: 800;
            color: #0f172a;
        }
        
        .grand-total-row {
            display: flex;
            justify-content: space-between;
            background-color: #C8D1DD;
            color: #0f172a;
            padding: 7px 12px;
            border-radius: 4px;
            margin-top: 8px;
            align-items: center;
            border: 1px solid #475569;
        }

        /* Footer & Signature Table */
        .footer-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #475569;
            background-color: #ffffff;
            page-break-inside: avoid !important;
        }
        .footer-section-wrapper {
            page-break-inside: avoid !important;
        }
        .footer-table td {
            padding: 0;
            vertical-align: top;
            border-right: 1px solid #475569;
        }
        .footer-table td:last-child {
            border-right: none;
        }
        .terms-title, .signature-title {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            color: #0f172a;
            background-color: #C8D1DD;
            padding: 6px 10px;
            margin: 0;
            border-bottom: 1px solid #475569;
            display: block;
        }
        .terms-list {
            margin: 0;
            padding-left: 14px;
            font-size: 8.5px;
            color: #475569;
            line-height: 1.4;
        }
        .signature-line {
            border-top: 1.5px solid #000000;
            width: 150px;
            margin: 30px auto 6px auto;
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


        /* Subtle Centered Background Watermark */
        .invoice-box {
            position: relative;
            overflow: hidden;
        }
        .watermark-container {
            position: absolute;
            top: 48%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 100%;
            text-align: center;
            opacity: 0.035;
            pointer-events: none;
            z-index: 0;
        }
        .watermark-img {
            max-width: 280px;
            max-height: 280px;
            object-fit: contain;
            filter: grayscale(100%);
            display: inline-block;
        }
        .watermark-text {
            font-size: 24px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 5px;
            color: #0f172a;
            margin-top: 10px;
            font-family: 'Outfit', sans-serif;
        }

        /* Print Media Overrides */
        @media print {
            .no-print-bar {
                display: none !important;
            }
            html, body {
                background-color: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
                height: 100% !important;
            }
            .invoice-box {
                box-shadow: none !important;
                border: 1.5px solid #0f172a !important;
                padding: 14px 16px !important;
                margin: 0 !important;
                max-width: 100% !important;
                width: 100% !important;
                box-sizing: border-box !important;
                min-height: 285mm !important;
                height: 285mm !important;
                display: flex !important;
                flex-direction: column !important;
                justify-content: flex-start !important;
                page-break-inside: avoid !important;
            }
        }
            .header-table {
                margin-bottom: 8px !important;
                padding-bottom: 8px !important;
            }
            .meta-table {
                margin-bottom: 10px !important;
            }
            .cell-body {
                padding: 6px !important;
            }
            .items-section-wrapper {
                display: block !important;
                flex-grow: 0 !important;
                margin-bottom: 6px !important;
            }
            .bottom-section-wrapper {
                display: block !important;
                margin-top: 0 !important;
            }
            .items-table {
                height: auto !important;
            }
            .items-table th, .items-table td {
                padding: 6px 8px !important;
            }
            .empty-filler-row td {
                height: 12px !important;
                padding: 2px 8px !important;
            }
            .totals-table {
                margin-bottom: 8px !important;
            }
            .totals-cell {
                padding: 8px 12px !important;
            }
            .words-bar {
                margin-bottom: 8px !important;
                padding: 5px 10px !important;
            }
            .footer-table table, .footer-table tr, .footer-table td {
                page-break-inside: avoid !important;
            }
        }
    </style>
</head>
<body>

    @php
        $rupee = '₹';

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

        $logoPath = \App\Models\Setting::get('logo_path', 'logo.jpg');
        $fullLogoPath = public_path($logoPath);
        $logoSrc = '';
        if (file_exists($fullLogoPath) && is_file($fullLogoPath)) {
            $logoData = base64_encode(file_get_contents($fullLogoPath));
            $logoSrc = 'data:image/' . pathinfo($fullLogoPath, PATHINFO_EXTENSION) . ';base64,' . $logoData;
        } else {
            $logoSrc = asset($logoPath);
        }
    @endphp

    @if(!isset($isPdf) || !$isPdf)
    <!-- Top Action Control Bar -->
    <div class="no-print-bar">
        <div class="control-left">
            <button onclick="window.close()" class="btn btn-secondary" style="margin-right: 12px; font-weight: 700; color: #0f172a; border: 1px solid #cbd5e1;">❌ Close</button>
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
        <!-- Subtle Background Watermark -->
        <div class="watermark-container">
            @if(!empty($logoSrc))
                <img src="{{ $logoSrc }}" class="watermark-img" alt="Watermark Logo"><br>
            @endif
            <div class="watermark-text">{{ \App\Models\Setting::get('business_name', 'PRAFUL WELDING WORKS') }}</div>
        </div>

        <div>
            <!-- Header Section -->
            <table class="header-table">
                <tr>
                    <td style="width: 60%;">
                        <table style="border-collapse: collapse;">
                            <tr>
                                <td style="vertical-align: middle;">
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
                            <div class="tax-invoice-title" style="color: #d97706;">RAW MATERIAL SALE MEMO</div>
                        @else
                            <div class="tax-invoice-title">TAX INVOICE</div>
                            <div class="invoice-number">{{ $invoice->invoice_number }}</div>
                        @endif
                    </td>
                </tr>
            </table>

            <!-- 3-Column Party Metadata Block -->
            <table class="meta-table">
                <tr>
                    <td style="width: 34%;">
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
                    <td style="width: 36%;">
                        <div class="cell-header">Billed To (Buyer)</div>
                        <div class="cell-body">
                            <span class="meta-value-bold" style="font-size: 12px;">{{ $client->company_name ?? ($invoice->custom_client_name ?? 'N/A') }}</span>
                            @if($plant && !empty($plant->plant_name))
                                <span style="font-size: 11px; font-weight: 700; color: #475569;">({{ $plant->plant_name }})</span>
                            @endif
                            <br>
                            @if(!empty($invoice->custom_client_name))
                                <span style="font-weight: 700; color: #0f172a; font-family: monospace;">GSTIN: {{ !empty($invoice->custom_buyer_gstin) ? $invoice->custom_buyer_gstin : 'URP (Unregistered Buyer)' }}</span><br>
                            @else
                                {{ $plant->shipping_address ?? ($billedAddress ?? 'N/A') }}<br>
                                <span style="font-weight: 700; color: #0f172a; font-family: monospace;">GSTIN: {{ $billedGst }}</span><br>
                            @endif
                            State: <span class="meta-value-bold">{{ $pState }} ({{ $pCode }})</span>
                        </div>
                    </td>
                    <td style="width: 30%;">
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
                            <th style="text-align: left; width: 38%;">DESCRIPTION OF GOODS</th>
                            <th style="text-align: center; width: 11%;">HSN/SAC</th>
                            <th style="text-align: right; width: 13%;">QTY</th>
                            <th style="text-align: right; width: 12%;">RATE ({!! $rupee !!})</th>
                            <th style="text-align: center; width: 7%;">per</th>
                            <th style="text-align: right; width: 15%;">AMOUNT ({!! $rupee !!})</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $groupedItems = isset($groupedItems) ? $groupedItems : ($invoice->items ?? []);
                            $calcSubtotal = 0;
                        @endphp
                        @foreach ($groupedItems as $index => $item)
                            @php
                                $isRaw = ($item->item_type === 'raw_material');
                                $rawMat = $item->rawMaterial ?? null;
                                $pGood = $item->finishedGood ?? $item->product ?? null;
                                
                                $pName = $item->item_name ?? ($isRaw ? ($rawMat->material_name ?? 'Raw Material') : ($pGood->product_name ?? 'Product'));
                                $pSku = $isRaw ? ('RM-' . ($item->raw_material_id ?? '0')) : (!empty($item->sku) ? $item->sku : ($pGood->sku ?? 'N/A'));
                                $pHsn = $isRaw ? '72040000' : ($pGood->hsn_code ?? '73089090');
                                $pUom = $item->billing_uom ?? ($isRaw ? ($rawMat->unit ?? 'kg') : ($pGood->uom ?? 'piece'));
                                $pGst = $isRaw ? 18.00 : ($pGood->gst_rate ?? 18.00);
                                $pTotal = isset($item->total) ? $item->total : ($item->total_price ?? ($item->quantity * $item->unit_price));
                                $calcSubtotal += (float)$pTotal;

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
                                <td style="text-align: center; font-weight: 700; color: #475569;">{{ $index + 1 }}</td>
                                <td>
                                    <div class="item-name">{{ $pName }}</div>
                                    <div class="item-sku">{{ $pSku }}</div>
                                </td>
                                <td style="text-align: center;">
                                    <span class="hsn-text">{{ $pHsn }}</span>
                                </td>
                                <td style="text-align: right; font-weight: 800; color: #0f172a;">
                                    <div>{{ $qtyFormatted }} {{ strtoupper($pUom) }}</div>
                                    @if($unitConversionNotice)
                                        <div style="font-size: 9px; font-weight: 700; color: #475569; margin-top: 2px;">{{ $unitConversionNotice }}</div>
                                    @endif
                                </td>
                                <td style="text-align: right; font-weight: 600;">{!! $rupee !!}{{ number_format($item->unit_price, 2) }}</td>
                                <td style="text-align: center; font-weight: 700; color: #334155; text-transform: uppercase;">
                                    {{ strtoupper($pUom) }}
                                </td>
                                <td style="text-align: right; font-weight: 800; color: #0f172a;">{!! $rupee !!}{{ number_format($pTotal, 2) }}</td>
                            </tr>
                        @endforeach
                        @php
                            $itemCount = count($groupedItems);
                            $minRows = ($itemCount <= 3) ? 16 : 10;
                        @endphp
                        @for ($emptyIdx = $itemCount; $emptyIdx < $minRows; $emptyIdx++)
                            <tr class="empty-filler-row">
                                <td style="text-align: center; color: transparent;">&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                            </tr>
                        @endfor
                        @php
                            $subtotalVal = (isset($invoice->total_taxable_value) && (float)$invoice->total_taxable_value > 0)
                                ? (float)$invoice->total_taxable_value
                                : $calcSubtotal;
                        @endphp
                        <tr class="total-row-item-table" style="background-color: #f1f5f9; font-weight: 800;">
                            <td colspan="6" style="text-align: right; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #0f172a; border-top: 1px solid #475569; border-bottom: 1px solid #475569; border-right: 1px solid #475569; padding: 7px 10px;">
                                Subtotal
                            </td>
                            <td style="text-align: right; font-size: 12px; font-weight: 800; color: #0f172a; border-top: 1px solid #475569; border-bottom: 1px solid #475569; padding: 7px 10px;">
                                {!! $rupee !!}{{ number_format($subtotalVal, 2) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Bottom Summary, Bank Details & Footer Signatures -->
        <div class="bottom-section-wrapper">
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
                        <table style="width: 100%; border-collapse: collapse; border: none; height: auto;">
                            <tr style="border-bottom: 1px dashed #cbd5e1;">
                                <td class="total-label" style="text-align: left; padding: 5px 2px; font-size: 11.5px; color: #334155; font-weight: 700; border: none;">Taxable Subtotal:</td>
                                <td class="total-value" style="text-align: right; padding: 5px 2px; font-size: 11.5px; color: #0f172a; font-weight: 800; border: none;">{!! $rupee !!}{{ number_format($invoice->total_taxable_value, 2) }}</td>
                            </tr>
                            
                            @php
                                $taxableBase = (float)$invoice->total_taxable_value;
                                if ($taxableBase > 0) {
                                    $igstPct = round(($invoice->igst / $taxableBase) * 100, 1);
                                    $cgstPct = round(($invoice->cgst / $taxableBase) * 100, 1);
                                    $sgstPct = round(($invoice->sgst / $taxableBase) * 100, 1);
                                } else {
                                    $igstPct = 18;
                                    $cgstPct = 9;
                                    $sgstPct = 9;
                                }
                            @endphp

                            @if ($invoice->igst > 0)
                                <tr style="border-bottom: 1px dashed #cbd5e1;">
                                    <td class="total-label" style="text-align: left; padding: 5px 2px; font-size: 11.5px; color: #334155; font-weight: 700; border: none;">IGST Total ({{ $igstPct }}%):</td>
                                    <td class="total-value" style="text-align: right; padding: 5px 2px; font-size: 11.5px; color: #0f172a; font-weight: 800; border: none;">{!! $rupee !!}{{ number_format($invoice->igst, 2) }}</td>
                                </tr>
                            @else
                                <tr style="border-bottom: 1px dashed #cbd5e1;">
                                    <td class="total-label" style="text-align: left; padding: 5px 2px; font-size: 11.5px; color: #334155; font-weight: 700; border: none;">CGST Total ({{ $cgstPct }}%):</td>
                                    <td class="total-value" style="text-align: right; padding: 5px 2px; font-size: 11.5px; color: #0f172a; font-weight: 800; border: none;">{!! $rupee !!}{{ number_format($invoice->cgst, 2) }}</td>
                                </tr>
                                <tr style="border-bottom: 1px dashed #cbd5e1;">
                                    <td class="total-label" style="text-align: left; padding: 5px 2px; font-size: 11.5px; color: #334155; font-weight: 700; border: none;">SGST Total ({{ $sgstPct }}%):</td>
                                    <td class="total-value" style="text-align: right; padding: 5px 2px; font-size: 11.5px; color: #0f172a; font-weight: 800; border: none;">{!! $rupee !!}{{ number_format($invoice->sgst, 2) }}</td>
                                </tr>
                            @endif
                        </table>

                        <!-- Grand Total Table (styled to look like a box/capsule) -->
                        <table style="width: 100%; border-collapse: collapse; margin-top: 8px; background-color: #C8D1DD; border: 1px solid #475569; border-radius: 4px; height: auto;">
                            <tr>
                                <td style="text-align: left; padding: 7px 12px; font-weight: 800; font-size: 11.5px; color: #0f172a; border: none;">Total Amount (Incl. Tax):</td>
                                <td style="text-align: right; padding: 7px 12px; font-size: 15px; color: #0f172a; font-weight: 900; border: none;">{!! $rupee !!}{{ number_format($invoice->total_amount, 2) }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <!-- Amount in Words (Positioned above Terms & Conditions) -->
            <div class="words-bar">
                <span class="words-title">Amount in Words:</span>
                <span style="font-weight: 700; font-style: italic; color: #0f172a;">{{ $amountInWords }}</span>
            </div>

            <!-- Footer Terms & Signatures Wrapper to prevent splitting across pages -->
            <div class="footer-section-wrapper">
                <!-- Footer Terms & Signatures -->
                <table class="footer-table">
                    <tr>
                        <td style="width: 58%; vertical-align: top; border-right: 1px solid #475569;">
                            <div class="terms-title">TERMS & CONDITIONS</div>
                            <div style="padding: 8px 12px;">
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
                            </div>
                        </td>
                        <td style="width: 42%; vertical-align: top; padding: 0;">
                            <table style="width: 100%; border-collapse: collapse; border: none; height: auto;">
                                <tr>
                                    <td style="border: none; padding: 0;">
                                        <div class="signature-title" style="text-align: center;">Authorized Signature</div>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="border: none; padding: 8px 12px; text-align: center; vertical-align: middle; height: 50px;">
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
                                            <img src="{{ $sigSrc }}" alt="Signature Stamp" style="max-height: 48px; max-width: 150px; object-fit: contain; display: inline-block;">
                                        @else
                                            <div style="height: 40px;"></div>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td style="border: none; border-top: 1px solid #475569; padding: 5px 8px; text-align: center; font-size: 10.5px; font-weight: 800; color: #0f172a; text-transform: uppercase; background-color: #ffffff;">
                                        FOR {{ strtoupper(\App\Models\Setting::get('business_name', 'PRAFUL WELDING WORKS')) }}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
    
                <div class="computer-gen-notice">
                    This is a computer-generated tax invoice • Issued under GST Rules, 2017
                </div>
            </div>
        </div>
    </div>

    @if(!isset($isPdf) || !$isPdf)
    <script>
        window.addEventListener('load', function() {
            if (!window.location.href.includes('download')) {
                setTimeout(function() {
                    window.print();
                }, 300);
            }
        });
    </script>
    @endif
</body>
</html>
