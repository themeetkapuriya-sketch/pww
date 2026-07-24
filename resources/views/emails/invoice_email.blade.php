<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice Statement</title>
</head>
<body style="margin: 0; padding: 20px; font-family: 'Outfit', 'DejaVu Sans', 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f1f5f9; -webkit-font-smoothing: antialiased;">

    <table cellpadding="0" cellspacing="0" border="0" style="width: 100%; max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); border: 1px solid #e2e8f0;">
        
        <!-- Header Banner -->
        <tr>
            <td style="background-color: #1e73be; padding: 30px; text-align: center;">
                <h1 style="margin: 0; font-size: 22px; font-weight: 800; color: #ffffff; letter-spacing: 1px; text-transform: uppercase;">{{ \App\Models\Setting::get('business_name', 'Praful Welding Works') }}</h1>
                <p style="margin: 5px 0 0 0; font-size: 11px; color: #bfdbfe; font-weight: 600; text-transform: uppercase; letter-spacing: 1.5px;">B2B Tax Invoice & Billing Statement</p>
            </td>
        </tr>

        <!-- Body Content -->
        <tr>
            <td style="padding: 30px 40px;">
                <p style="margin: 0 0 10px 0; font-size: 15px; color: #334155;">Hello <strong style="color: #0f172a;">{{ $client->company_name ?? 'Client' }}</strong>,</p>
                <p style="margin: 0 0 25px 0; font-size: 13px; color: #64748b; line-height: 1.5;">Please find below the detailed tax statement for Invoice <strong style="color: #0f172a;">#{{ $invoice->invoice_number }}</strong>.</p>

                <!-- Metadata details box -->
                <table cellpadding="0" cellspacing="0" border="0" style="width: 100%; margin-bottom: 25px; font-size: 13px;">
                    <tr>
                        <td style="padding: 6px 0; color: #64748b; width: 140px;">Invoice Number:</td>
                        <td style="padding: 6px 0; color: #0f172a; font-weight: bold;">{{ $invoice->invoice_number }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; color: #64748b;">Due Date:</td>
                        <td style="padding: 6px 0; color: #ef4444; font-weight: bold;">{{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') : \Carbon\Carbon::parse($invoice->created_at)->addDays(30)->format('d M Y') }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; color: #64748b;">GSTIN:</td>
                        <td style="padding: 6px 0; color: #0f172a; font-weight: bold; font-family: monospace;">{{ !empty($plant->gst_number) ? $plant->gst_number : ($client->gst_number ?? 'N/A') }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; color: #64748b;">Plant Location:</td>
                        <td style="padding: 6px 0; color: #0f172a; font-weight: bold;">{{ $plant->plant_name ?? 'N/A' }} ({{ $plant->state ?? 'Gujarat' }})</td>
                    </tr>
                </table>

                <!-- Items Table -->
                <table cellpadding="0" cellspacing="0" border="0" style="width: 100%; border-collapse: collapse; margin-bottom: 25px; font-size: 12px; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
                    <thead>
                        <tr style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                            <th style="padding: 10px 15px; text-align: left; color: #475569; font-weight: bold; width: 50%;">Item</th>
                            <th style="padding: 10px 15px; text-align: center; color: #475569; font-weight: bold; width: 15%;">Qty</th>
                            <th style="padding: 10px 15px; text-align: right; color: #475569; font-weight: bold; width: 15%;">Rate</th>
                            <th style="padding: 10px 15px; text-align: right; color: #475569; font-weight: bold; width: 20%;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($groupedItems as $item)
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 12px 15px; color: #334155; font-weight: bold;">{{ $item->product_name }}</td>
                                <td style="padding: 12px 15px; text-align: center; color: #475569;">{{ $item->quantity }}</td>
                                <td style="padding: 12px 15px; text-align: right; color: #475569; font-family: 'DejaVu Sans', Arial, sans-serif;">&#8377;{{ number_format($item->unit_price, 2) }}</td>
                                <td style="padding: 12px 15px; text-align: right; color: #334155; font-weight: bold; font-family: 'DejaVu Sans', Arial, sans-serif;">&#8377;{{ number_format($item->total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Calculation Box -->
                <table cellpadding="0" cellspacing="0" border="0" style="width: 100%; background-color: #eff6ff; border-radius: 10px; border: 1px solid #bfdbfe; padding: 15px 20px; font-size: 12px;">
                    <tr>
                        <td style="padding: 4px 0; color: #475569;">Taxable Value:</td>
                        <td style="padding: 4px 0; text-align: right; color: #334155; font-weight: bold; font-family: 'DejaVu Sans', Arial, sans-serif;">&#8377;{{ number_format($invoice->total_taxable_value ?? ($invoice->total_amount - ($invoice->cgst + $invoice->sgst + $invoice->igst)), 2) }}</td>
                    </tr>
                    
                    @if ($invoice->igst > 0)
                        <tr>
                            <td style="padding: 4px 0; color: #475569;">IGST (18%):</td>
                            <td style="padding: 4px 0; text-align: right; color: #334155; font-weight: bold; font-family: 'DejaVu Sans', Arial, sans-serif;">&#8377;{{ number_format($invoice->igst, 2) }}</td>
                        </tr>
                    @else
                        <tr>
                            <td style="padding: 4px 0; color: #475569;">CGST (9%):</td>
                            <td style="padding: 4px 0; text-align: right; color: #334155; font-weight: bold; font-family: 'DejaVu Sans', Arial, sans-serif;">&#8377;{{ number_format($invoice->cgst, 2) }}</td>
                        </tr>
                        <tr>
                            <td style="padding: 4px 0; color: #475569;">SGST (9%):</td>
                            <td style="padding: 4px 0; text-align: right; color: #334155; font-weight: bold; font-family: 'DejaVu Sans', Arial, sans-serif;">&#8377;{{ number_format($invoice->sgst, 2) }}</td>
                        </tr>
                    @endif

                    <tr style="border-top: 1px solid #bfdbfe;">
                        <td style="padding: 8px 0 0 0; color: #0f172a; font-weight: 800; font-size: 14px;">Total Invoice Value:</td>
                        <td style="padding: 8px 0 0 0; text-align: right; color: #1e73be; font-weight: 800; font-size: 14px; font-family: 'DejaVu Sans', Arial, sans-serif;">&#8377;{{ number_format($invoice->total_amount, 2) }}</td>
                    </tr>
                </table>

            </td>
        </tr>

        <!-- Footer block -->
        <tr>
            <td style="padding: 25px 30px; background-color: #f8fafc; border-top: 1px solid #f1f5f9; text-align: center; font-size: 11px; color: #94a3b8; line-height: 1.6;">
                <p style="margin: 0 0 5px 0; color: #64748b;">This is a digitally generated tax statement. If you have any inquiries, please reach out to <a href="mailto:billing@pww.com" style="color: #1e73be; text-decoration: none;">billing@pww.com</a>.</p>
                <p style="margin: 0; font-weight: bold; color: #475569;">{{ \App\Models\Setting::get('business_name', 'Praful Welding Works') }} &bull; {{ \App\Models\Setting::get('address_line_1', 'Plot No. 12, G.I.D.C. Metoda,') }} {{ \App\Models\Setting::get('address_line_2', 'Rajkot, Gujarat - 360021') }}</p>
            </td>
        </tr>

    </table>

</body>
</html>
