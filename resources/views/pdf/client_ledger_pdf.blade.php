<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Statement of Account - {{ $client->company_name }}</title>
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
            font-size: 20px;
            font-weight: 800;
            color: #2563eb;
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
        .info-box {
            width: 100%;
            border: 1px solid #e2e8f0;
            background-color: #f8fafc;
            border-radius: 6px;
            padding: 12px;
            margin-bottom: 20px;
        }
        .info-box table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-box td {
            vertical-align: top;
            font-size: 10px;
        }
        .client-name {
            font-size: 13px;
            font-weight: bold;
            color: #0f172a;
        }
        .summary-box {
            text-align: right;
        }
        .stat-label {
            font-size: 9px;
            text-transform: uppercase;
            color: #64748b;
            font-weight: bold;
        }
        .stat-val {
            font-size: 13px;
            font-weight: bold;
            color: #0f172a;
        }
        .stat-val.outstanding {
            color: #d97706;
            font-size: 14px;
            font-weight: bold;
        }
        .ledger-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .ledger-table th {
            background-color: #4371D7;
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
            padding: 8px 10px;
            text-align: left;
            border: 1px solid #3b82f6;
        }
        .ledger-table th.text-right {
            text-align: right;
        }
        .ledger-table th.text-center {
            text-align: center;
        }
        .ledger-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 10px;
        }
        .ledger-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .font-mono {
            font-family: 'DejaVu Sans', monospace;
            font-weight: bold;
        }
        .debit-text {
            color: #2563eb;
            font-weight: bold;
        }
        .credit-text {
            color: #16a34a;
            font-weight: bold;
        }
        .footer-note {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #e2e8f0;
            font-size: 9px;
            color: #94a3b8;
            text-align: center;
        }
        .bank-details-card {
            border: 1px border #cbd5e1;
            background: #ffffff;
            padding: 10px;
            border-radius: 6px;
            margin-top: 15px;
            font-size: 9.5px;
        }
    </style>
</head>
<body>

    <!-- Document Header -->
    <table class="header-table">
        <tr>
            <td>
                <h1 class="company-name">{{ \App\Models\Setting::get('business_name', 'Praful Welding Works') }}</h1>
                <div class="company-details">
                    {{ \App\Models\Setting::get('business_address', 'At & Post G.I.D.C., Gujarat') }}<br>
                    GSTIN: <strong>{{ \App\Models\Setting::get('gstin', '24PWWRK1234A1Z0') }}</strong> | MSME: <strong>{{ \App\Models\Setting::get('msme_number', 'UDYAM-GJ-24-0012345') }}</strong>
                </div>
            </td>
            <td class="title-badge">
                <h2 class="doc-title">Statement of Account</h2>
                <div class="period-info">
                    Period: {{ \Carbon\Carbon::parse($start_date)->format('d/m/Y') }} to {{ \Carbon\Carbon::parse($end_date)->format('d/m/Y') }}
                </div>
                <div style="font-size: 9px; color: #94a3b8; margin-top: 2px;">
                    Date Generated: {{ date('d/m/Y H:i') }}
                </div>
            </td>
        </tr>
    </table>

    <!-- Client & Balance Overview Box -->
    <div class="info-box">
        <table>
            <tr>
                <td style="width: 55%;">
                    <span style="font-size: 9px; color: #64748b; font-weight: bold; text-transform: uppercase;">
                        Statement For Client: {{ !empty($selected_plant) ? ' (Plant Location)' : ' (Corporate Level)' }}
                    </span>
                    <div class="client-name">{{ $client->company_name }}</div>
                    @if(!empty($selected_plant))
                        <div style="color: #2563eb; font-weight: bold; margin-top: 2px;">Factory Plant: {{ $selected_plant->plant_name }}</div>
                        <div style="color: #475569; margin-top: 1px;">{{ $selected_plant->shipping_address ?? $client->corporate_address }}</div>
                        <div style="margin-top: 2px;">Plant GSTIN: <strong class="font-mono">{{ $selected_plant->gstin ?? $client->gst_number ?? 'N/A' }}</strong></div>
                    @else
                        <div style="color: #475569; margin-top: 2px;">{{ $client->corporate_address ?? 'N/A' }}</div>
                        <div style="margin-top: 2px;">GSTIN: <strong class="font-mono">{{ $client->gst_number ?? 'N/A' }}</strong></div>
                    @endif
                </td>
                <td style="width: 45%;" class="summary-box">
                    <table style="width: 100%;">
                        <tr>
                            <td class="stat-label">Opening Balance:</td>
                            <td class="stat-val text-right">&#8377;{{ number_format($opening_balance, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="stat-label">Total Invoiced (+):</td>
                            <td class="stat-val text-right debit-text">&#8377;{{ number_format($total_debit, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="stat-label">Total Received (-):</td>
                            <td class="stat-val text-right credit-text">&#8377;{{ number_format($total_credit, 2) }}</td>
                        </tr>
                        <tr style="border-top: 1px solid #cbd5e1;">
                            <td class="stat-label" style="padding-top: 4px;">Closing Outstanding:</td>
                            <td class="stat-val outstanding text-right" style="padding-top: 4px;">&#8377;{{ number_format($closing_balance, 2) }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <!-- Ledger Transactions Table -->
    <table class="ledger-table">
        <thead>
            <tr>
                <th style="width: 12%;">Date</th>
                <th style="width: 18%;">Document / Ref #</th>
                <th style="width: 38%;">Description & Details</th>
                <th class="text-right" style="width: 16%;">Billed Amount (+)</th>
                <th class="text-right" style="width: 16%;">Payment Recd (-)</th>
                <th class="text-right" style="width: 18%;">Balance (&#8377;)</th>
            </tr>
        </thead>
        <tbody>
            <tr style="background-color: #f1f5f9; font-weight: bold;">
                <td colspan="3">Opening Balance (Before {{ \Carbon\Carbon::parse($start_date)->format('d/m/Y') }})</td>
                <td class="text-right">-</td>
                <td class="text-right">-</td>
                <td class="text-right font-mono">&#8377;{{ number_format($opening_balance, 2) }}</td>
            </tr>
            @forelse($entries as $row)
                <tr>
                    <td class="text-center">{{ \Carbon\Carbon::parse($row['date'])->format('d/m/Y') }}</td>
                    <td class="font-mono">{{ $row['reference'] }}</td>
                    <td>{{ $row['description'] }}</td>
                    <td class="text-right {{ $row['debit'] > 0 ? 'debit-text' : '' }}">
                        {{ $row['debit'] > 0 ? '&#8377;' . number_format($row['debit'], 2) : '-' }}
                    </td>
                    <td class="text-right {{ $row['credit'] > 0 ? 'credit-text' : '' }}">
                        {{ $row['credit'] > 0 ? '&#8377;' . number_format($row['credit'], 2) : '-' }}
                    </td>
                    <td class="text-right font-mono font-bold">&#8377;{{ number_format($row['running_balance'], 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: #94a3b8; padding: 20px; italic;">
                        No invoices or payments logged for this client during the selected date period.
                    </td>
                </tr>
            @endforelse
            <tr style="background-color: #f8fafc; font-weight: bold; border-top: 2px solid #cbd5e1;">
                <td colspan="3" class="text-right">TOTAL PERIOD SUMMARY:</td>
                <td class="text-right debit-text">₹{{ number_format($total_debit, 2) }}</td>
                <td class="text-right credit-text">₹{{ number_format($total_credit, 2) }}</td>
                <td class="text-right font-mono" style="color: #d97706; font-size: 11px;">₹{{ number_format($closing_balance, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Bank Details Card for Payment Remittance -->
    <div class="bank-details-card">
        <strong style="color: #0f172a;">Bank Remittance Details for Payment:</strong><br>
        Bank Name: <strong>ICICI Bank / HDFC Bank</strong> | A/c Name: <strong>{{ \App\Models\Setting::get('business_name', 'Praful Welding Works') }}</strong><br>
        Account No: <strong>987654321012</strong> | IFSC Code: <strong>ICIC0001234</strong> | Branch: <strong>G.I.D.C., Rajkot, Gujarat</strong>
    </div>

    <!-- Footer Note -->
    <div class="footer-note">
        This is a computer-generated Statement of Account. For any billing or ledger discrepancies, please contact our accounts department.<br>
        Thank you for your valued business with {{ \App\Models\Setting::get('business_name', 'Praful Welding Works') }}.
    </div>

</body>
</html>
