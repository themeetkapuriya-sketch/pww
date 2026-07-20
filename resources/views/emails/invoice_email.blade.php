<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice #{{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: 'Outfit', 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f8fafc; color: #1e293b; margin: 0; padding: 20px; }
        .email-card { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; padding: 32px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .header { border-bottom: 2px solid #f1f5f9; padding-bottom: 16px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; }
        .logo-title { font-size: 20px; font-weight: 800; color: #1e293b; }
        .subtitle { font-size: 12px; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; }
        .badge { background: #eff6ff; color: #5287f7; padding: 6px 12px; border-radius: 9999px; font-size: 12px; font-weight: 700; }
        .content { font-size: 14px; line-height: 1.6; color: #334155; margin-bottom: 24px; white-space: pre-line; }
        .summary-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px 20px; margin-bottom: 24px; }
        .summary-row { display: flex; justify-content: space-between; padding: 6px 0; font-size: 13px; border-bottom: 1px dashed #cbd5e1; }
        .summary-row:last-child { border-bottom: none; font-weight: 800; font-size: 15px; color: #5287f7; padding-top: 8px; }
        .footer { font-size: 12px; color: #94a3b8; text-align: center; border-top: 1px solid #f1f5f9; padding-top: 16px; margin-top: 24px; }
    </style>
</head>
<body>
    <div class="email-card">
        <div class="header">
            <div>
                <div class="logo-title">{{ \App\Models\Setting::get('business_name', 'Praful Welding Works') }}</div>
                <div class="subtitle">Tax Invoice Statement</div>
            </div>
            <div class="badge">Invoice #{{ $invoice->invoice_number }}</div>
        </div>

        <div class="content">
{{ $messageBody }}
        </div>

        <div class="summary-box">
            <div class="summary-row">
                <span>Invoice Number:</span>
                <strong>{{ $invoice->invoice_number }}</strong>
            </div>
            <div class="summary-row">
                <span>Payment Status:</span>
                <strong style="text-transform: uppercase;">{{ $invoice->payment_status ?? 'UNPAID' }}</strong>
            </div>
            <div class="summary-row">
                <span>Total Amount Due:</span>
                <strong>₹{{ number_format($invoice->total_amount, 2) }}</strong>
            </div>
        </div>

        <p style="font-size: 13px; color: #64748b;">
            📎 <strong>Attachment:</strong> Your official PDF invoice (<code>Invoice-{{ $invoice->invoice_number }}.pdf</code>) is attached to this email.
        </p>

        <div class="footer">
            © {{ date('Y') }} {{ \App\Models\Setting::get('business_name', 'Praful Welding Works') }}. All rights reserved.<br>
            {{ \App\Models\Setting::get('business_address', 'At & Post G.I.D.C., Gujarat') }}
        </div>
    </div>
</body>
</html>
