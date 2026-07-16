<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice #{{ $invoice->invoice_number }}</title>
</head>
<body style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f8fafc; margin: 0; padding: 20px; color: #334155;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -1px rgba(0,0,0,0.03); border: 1px solid #e2e8f0;">
        <!-- Header -->
        <div style="background-color: #1E73BE; padding: 24px; text-align: center; color: #ffffff;">
            <h2 style="margin: 0; font-size: 20px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px;">Praful Welding Works</h2>
            <p style="margin: 4px 0 0 0; font-size: 12px; opacity: 0.9;">B2B Tax Invoice & Billing Statement</p>
        </div>

        <!-- Body -->
        <div style="padding: 24px;">
            <p style="font-size: 14px; line-height: 1.5; color: #475569;">
                Hello <strong>{{ $client->company_name ?? 'Valued Client' }}</strong>,
            </p>
            <p style="font-size: 14px; line-height: 1.5; color: #475569; margin-bottom: 20px;">
                Please find below the detailed tax statement for Invoice <strong>#{{ $invoice->invoice_number }}</strong>.
            </p>

            <!-- Invoice Info Table -->
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 13px;">
                <tr>
                    <td style="padding: 6px 0; color: #64748b; font-weight: 600; width: 35%;">Invoice Number:</td>
                    <td style="padding: 6px 0; color: #0f172a; font-weight: bold;">{{ $invoice->invoice_number }}</td>
                </tr>
                <tr>
                    <td style="padding: 6px 0; color: #64748b; font-weight: 600;">Due Date:</td>
                    <td style="padding: 6px 0; color: #ef4444; font-weight: bold;">{{ $invoice->due_date ? $invoice->due_date->format('d M Y') : 'N/A' }}</td>
                </tr>
                <tr>
                    <td style="padding: 6px 0; color: #64748b; font-weight: 600;">GSTIN:</td>
                    <td style="padding: 6px 0; color: #0f172a; font-family: monospace;">{{ $client->gst_number ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td style="padding: 6px 0; color: #64748b; font-weight: 600;">Plant Location:</td>
                    <td style="padding: 6px 0; color: #0f172a;">{{ $plant->plant_name ?? 'N/A' }} ({{ $plant->state ?? 'N/A' }})</td>
                </tr>
            </table>

            <!-- Line Items Table -->
            <div style="border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; margin-bottom: 20px;">
                <table style="width: 100%; border-collapse: collapse; font-size: 12px; text-align: left;">
                    <thead>
                        <tr style="background-color: #f1f5f9; border-bottom: 1px solid #e2e8f0; color: #475569;">
                            <th style="padding: 10px 12px; font-weight: 700;">Item</th>
                            <th style="padding: 10px 12px; font-weight: 700; text-align: right;">Qty</th>
                            <th style="padding: 10px 12px; font-weight: 700; text-align: right;">Rate</th>
                            <th style="padding: 10px 12px; font-weight: 700; text-align: right;">Amount</th>
                        </tr>
                    </thead>
                    <tbody style="color: #334155;">
                        @foreach ($items as $item)
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 10px 12px;">{{ $item->product_name }}</td>
                                <td style="padding: 10px 12px; text-align: right;">{{ number_format($item->quantity) }}</td>
                                <td style="padding: 10px 12px; text-align: right;">₹{{ number_format($item->unit_price, 2) }}</td>
                                <td style="padding: 10px 12px; text-align: right; font-weight: 500;">₹{{ number_format($item->quantity * $item->unit_price, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Tax Summary -->
            <div style="background-color: #f8fafc; border-radius: 8px; padding: 16px; margin-bottom: 20px; font-size: 13px;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 4px 0; color: #64748b;">Taxable Value:</td>
                        <td style="padding: 4px 0; text-align: right; color: #334155;">₹{{ number_format($invoice->total_taxable_value, 2) }}</td>
                    </tr>
                    @if ($invoice->cgst > 0)
                        <tr>
                            <td style="padding: 4px 0; color: #64748b;">CGST (9%):</td>
                            <td style="padding: 4px 0; text-align: right; color: #334155;">₹{{ number_format($invoice->cgst, 2) }}</td>
                        </tr>
                        <tr>
                            <td style="padding: 4px 0; color: #64748b;">SGST (9%):</td>
                            <td style="padding: 4px 0; text-align: right; color: #334155;">₹{{ number_format($invoice->sgst, 2) }}</td>
                        </tr>
                    @endif
                    @if ($invoice->igst > 0)
                        <tr>
                            <td style="padding: 4px 0; color: #64748b;">IGST (18%):</td>
                            <td style="padding: 4px 0; text-align: right; color: #334155;">₹{{ number_format($invoice->igst, 2) }}</td>
                        </tr>
                    @endif
                    <tr style="border-top: 1px solid #e2e8f0; font-size: 14px; font-weight: 700;">
                        <td style="padding: 8px 0; color: #0f172a;">Total Invoice Value:</td>
                        <td style="padding: 8px 0; text-align: right; color: #1E73BE;">₹{{ number_format($invoice->total_amount, 2) }}</td>
                    </tr>
                </table>
            </div>

            <!-- Footer Notes -->
            <p style="font-size: 12px; color: #64748b; text-align: center; margin-top: 24px; line-height: 1.5;">
                This is a digitally generated tax statement. If you have any inquiries, please reach out to <a href="mailto:billing@pww.com" style="color: #1E73BE; text-decoration: none;">billing@pww.com</a>.<br>
                <strong>Praful Welding Works</strong> • Metoda GIDC, Rajkot, Gujarat
            </p>
        </div>
    </div>
</body>
</html>
