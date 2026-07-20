@extends('layouts.app')

@section('title', 'Invoice #' . $invoice->invoice_number . ' Preview')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <!-- Top Breadcrumb & Navigation -->
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-3">
            <a href="{{ route('invoices', ['tab' => 'manual-builder']) }}" class="p-2 bg-white rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Invoice Preview</h1>
                <p class="text-xs font-semibold text-slate-500">Invoice Reference: <span class="text-blue-600 font-bold">#{{ $invoice->invoice_number }}</span></p>
            </div>
        </div>
        <div>
            @if($invoice->payment_status === 'paid')
                <span class="px-3.5 py-1.5 rounded-full text-xs font-extrabold bg-emerald-100 text-emerald-700 border border-emerald-300">PAID</span>
            @elseif($invoice->payment_status === 'partially_paid')
                <span class="px-3.5 py-1.5 rounded-full text-xs font-extrabold bg-amber-100 text-amber-700 border border-amber-300">PARTIALLY PAID</span>
            @else
                <span class="px-3.5 py-1.5 rounded-full text-xs font-extrabold bg-rose-100 text-rose-700 border border-rose-300">UNPAID</span>
            @endif
        </div>
    </div>

    <!-- Layout Grid: Left Invoice Card & Right Action Column -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 items-start">
        
        <!-- Left: Main Frest-Style Invoice Preview Document (3 cols) -->
        <div class="lg:col-span-3 bg-white rounded-2xl border border-slate-200 shadow-xs p-6 md:p-10 space-y-8" id="printable-invoice">
            
            <!-- Invoice Document Header -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center pb-6 border-b border-slate-100 gap-6">
                <!-- Business Identity -->
                <div class="flex items-center space-x-4">
                    <img class="h-12 w-12 object-contain rounded-xl border border-slate-100 p-1 bg-white shadow-2xs" src="{{ asset(\App\Models\Setting::get('logo_path', 'logo.jpg')) }}" alt="Company Logo">
                    <div>
                        <h2 class="text-xl font-extrabold text-slate-900 tracking-tight leading-tight">{{ \App\Models\Setting::get('business_name', 'Praful Welding Works') }}</h2>
                        <p class="text-xs text-slate-500 font-medium mt-0.5">{{ \App\Models\Setting::get('business_address', 'At & Post G.I.D.C., Gujarat') }}</p>
                        <p class="text-xs text-slate-400 font-semibold mt-0.5">GSTIN: <span class="text-slate-700 font-bold">{{ \App\Models\Setting::get('gstin', '24AAAAA0000A1Z5') }}</span> | State: Gujarat (24)</p>
                    </div>
                </div>

                <!-- Invoice Meta Details -->
                <div class="text-left md:text-right space-y-1">
                    <h3 class="text-2xl font-black text-blue-600 tracking-tight">INVOICE #{{ $invoice->invoice_number }}</h3>
                    <p class="text-xs font-bold text-slate-500">Date Issued: <span class="text-slate-800 font-semibold">{{ \Carbon\Carbon::parse($invoice->created_at)->format('d/m/Y') }}</span></p>
                    <p class="text-xs font-bold text-slate-500">Due Date: <span class="text-slate-800 font-semibold">{{ \Carbon\Carbon::parse($invoice->created_at)->addDays(30)->format('d/m/Y') }}</span></p>
                </div>
            </div>

            <!-- Client & Destination Party Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-slate-50/70 rounded-xl p-5 border border-slate-100">
                <!-- Invoice To -->
                <div class="space-y-1.5">
                    <span class="text-[10px] font-black uppercase tracking-wider text-slate-400">Invoice To:</span>
                    <h4 class="text-sm font-bold text-slate-900">{{ $client->company_name ?? 'Direct Customer' }}</h4>
                    <p class="text-xs text-slate-600 font-medium">{{ $plant->plant_name ?? 'Standard Plant Destination' }}</p>
                    <p class="text-xs text-slate-500">{{ $plant->shipping_address ?? 'Gujarat, India' }}</p>
                    <p class="text-xs text-slate-500 font-semibold">GSTIN: <span class="text-slate-700 font-bold">{{ $client->gst_number ?? 'N/A' }}</span></p>
                    @if(!empty($client->client_email))
                        <p class="text-xs text-blue-600 font-medium">✉ {{ $client->client_email }}</p>
                    @endif
                </div>

                <!-- Bill To Summary -->
                <div class="space-y-1.5 md:text-right">
                    <span class="text-[10px] font-black uppercase tracking-wider text-slate-400">Bill Summary:</span>
                    <div class="text-sm font-bold text-slate-800">Total Due: <span class="text-base font-extrabold text-blue-600">₹{{ number_format($invoice->total_amount, 2) }}</span></div>
                    <p class="text-xs text-slate-500">Payment Status: <strong class="uppercase text-slate-800">{{ $invoice->payment_status ?? 'UNPAID' }}</strong></p>
                    <p class="text-xs text-slate-500">Tax Type: 
                        <span class="font-bold text-slate-700">
                            {{ ($plant && strtolower($plant->state) === 'gujarat') || str_contains(strtolower($plant->shipping_address ?? ''), 'gujarat') ? 'Intrastate (CGST + SGST @ 18%)' : 'Interstate (IGST @ 18%)' }}
                        </span>
                    </p>
                </div>
            </div>

            <!-- Itemized Products Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 text-slate-400 text-[11px] uppercase font-bold tracking-wider bg-slate-50">
                            <th class="py-3 px-4">Item / Product Name</th>
                            <th class="py-3 px-4 text-center">SKU</th>
                            <th class="py-3 px-4 text-right">Cost</th>
                            <th class="py-3 px-4 text-center">Qty</th>
                            <th class="py-3 px-4 text-right">Price</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm font-semibold text-slate-700">
                        @forelse($groupedItems as $item)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="py-3.5 px-4 font-bold text-slate-900">{{ $item->product_name }}</td>
                                <td class="py-3.5 px-4 text-center text-xs font-mono text-slate-500">{{ $item->sku }}</td>
                                <td class="py-3.5 px-4 text-right">₹{{ number_format($item->unit_price, 2) }}</td>
                                <td class="py-3.5 px-4 text-center font-bold text-slate-800">{{ $item->quantity }}</td>
                                <td class="py-3.5 px-4 text-right font-extrabold text-slate-900">₹{{ number_format($item->total, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-6 text-center text-slate-400 font-medium">No items registered for this invoice.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Invoice Calculation Totals -->
            <div class="flex flex-col md:flex-row justify-between items-start pt-4 border-t border-slate-200 gap-6">
                <div class="text-xs text-slate-400 max-w-sm space-y-1">
                    <p class="font-bold text-slate-600">Note & Declaration:</p>
                    <p>We declare that this invoice shows the actual price of the goods described and that all particulars are true and correct.</p>
                </div>

                <div class="w-full md:w-72 space-y-2 bg-slate-50 p-4 rounded-xl border border-slate-200/80">
                    <div class="flex justify-between text-xs font-semibold text-slate-600">
                        <span>Taxable Value:</span>
                        <span class="font-bold text-slate-800">₹{{ number_format($invoice->total_taxable_value, 2) }}</span>
                    </div>

                    @if($invoice->igst > 0)
                        <div class="flex justify-between text-xs font-semibold text-slate-600">
                            <span>IGST (18%):</span>
                            <span class="font-bold text-slate-800">₹{{ number_format($invoice->igst, 2) }}</span>
                        </div>
                    @else
                        <div class="flex justify-between text-xs font-semibold text-slate-600">
                            <span>CGST (9%):</span>
                            <span class="font-bold text-slate-800">₹{{ number_format($invoice->cgst, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-xs font-semibold text-slate-600">
                            <span>SGST (9%):</span>
                            <span class="font-bold text-slate-800">₹{{ number_format($invoice->sgst, 2) }}</span>
                        </div>
                    @endif

                    <div class="flex justify-between text-base font-black text-slate-900 border-t border-slate-200 pt-2">
                        <span>Invoice Total:</span>
                        <span class="text-blue-600">₹{{ number_format($invoice->total_amount, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: 4 Options Action Sidebar Card (1 col - Frest Style) -->
        <div class="lg:col-span-1 bg-white rounded-2xl border border-slate-200 shadow-xs p-5 space-y-3.5 sticky top-6">
            <h3 class="text-xs font-black uppercase tracking-wider text-slate-400 px-1 mb-2">Invoice Actions</h3>

            <!-- 1. Send Invoice Button -->
            <button 
                type="button" 
                onclick="openSendEmailModal()" 
                class="w-full flex items-center justify-center space-x-2 py-3 px-4 rounded-xl text-sm font-bold bg-blue-600 hover:bg-blue-700 text-white shadow-xs transition duration-150 transform hover:-translate-y-0.5"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                <span>Send Invoice</span>
            </button>

            <!-- 2. Download PDF Button -->
            <a 
                href="{{ route('invoice.download', $invoice->id) }}" 
                class="w-full flex items-center justify-center space-x-2 py-2.5 px-4 rounded-xl text-sm font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 transition duration-150"
            >
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                <span>Download PDF</span>
            </a>

            <!-- 3. Print Button -->
            <a 
                href="{{ route('invoice.print', $invoice->id) }}" 
                target="_blank" 
                class="w-full flex items-center justify-center space-x-2 py-2.5 px-4 rounded-xl text-sm font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 transition duration-150"
            >
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                <span>Print</span>
            </a>

            <!-- 4. Edit Invoice Button -->
            <a 
                href="{{ route('invoices', ['tab' => 'manual-builder']) }}" 
                class="w-full flex items-center justify-center space-x-2 py-2.5 px-4 rounded-xl text-sm font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 transition duration-150"
            >
                <span>Edit Invoice</span>
            </a>

            <div class="pt-2 border-t border-slate-100">
                @if($invoice->payment_status !== 'paid')
                    <form action="{{ route('invoice.pay', $invoice->id) }}" method="POST" onsubmit="return confirm('Mark this invoice as fully paid?');">
                        @csrf
                        <button type="submit" class="w-full flex items-center justify-center space-x-2 py-2.5 px-4 rounded-xl text-sm font-bold bg-emerald-500 hover:bg-emerald-600 text-white transition duration-150 shadow-2xs">
                            <span>Mark Paid</span>
                        </button>
                    </form>
                @endif
            </div>
        </div>

    </div>
</div>

<!-- Frest Style Slide-Over Send Invoice Email Modal -->
<div id="sendEmailModal" class="fixed inset-0 z-50 overflow-hidden hidden" aria-labelledby="slide-over-title" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-xs transition-opacity" onclick="closeSendEmailModal()"></div>

    <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
        <div class="pointer-events-auto w-screen max-w-md transform transition duration-300 ease-in-out bg-white shadow-2xl flex flex-col">
            
            <!-- Modal Header -->
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Send Invoice Email</h3>
                    <p class="text-xs text-slate-500">Attach and send invoice PDF directly to recipient</p>
                </div>
                <button type="button" onclick="closeSendEmailModal()" class="text-slate-400 hover:text-slate-700 p-1.5 rounded-lg hover:bg-slate-100 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <!-- Modal Form Body -->
            <form id="sendInvoiceEmailForm" action="{{ route('invoice.send-email', $invoice->id) }}" method="POST" class="flex-1 overflow-y-auto p-6 space-y-4">
                @csrf

                <!-- Inline Validation Error Alert Container -->
                <div id="emailFormAlert" class="hidden bg-rose-50 border border-rose-200 text-rose-800 p-4 rounded-xl text-xs font-semibold leading-relaxed"></div>
                
                <!-- From -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">From</label>
                    <input type="text" readonly value="{{ \App\Models\Setting::get('business_name', 'Praful Welding Works') }} <no-reply@pww.com>" class="w-full px-3.5 py-2.5 bg-slate-100 border border-slate-200 rounded-xl text-xs font-semibold text-slate-600 focus:outline-none">
                </div>

                <!-- To -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">To <span class="text-rose-500">*</span></label>
                    <input type="email" name="recipient_email" required value="{{ $client->email ?? '' }}" placeholder="client@company.com" class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-xs font-bold text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none">
                </div>

                <!-- Subject -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Subject <span class="text-rose-500">*</span></label>
                    <input type="text" name="subject" required value="Invoice #{{ $invoice->invoice_number }} from {{ \App\Models\Setting::get('business_name', 'Praful Welding Works') }}" class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-xs font-bold text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none">
                </div>

                <!-- Message Body -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Message <span class="text-rose-500">*</span></label>
                    <textarea name="message_body" rows="6" required class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-xs font-semibold text-slate-700 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none">Dear {{ $client->client_name ?? 'Customer' }},

Thank you for your business!

Please find attached your tax invoice #{{ $invoice->invoice_number }} for the amount of ₹{{ number_format($invoice->total_amount, 2) }}.

We appreciate prompt payment. If you have any questions, feel free to contact us.

Best regards,
{{ \App\Models\Setting::get('business_name', 'Praful Welding Works') }}</textarea>
                </div>

                <!-- Attachment Pill Indicator -->
                <div class="p-3 bg-blue-50/80 border border-blue-200 rounded-xl flex items-center space-x-2.5">
                    <svg class="w-5 h-5 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                    <div class="min-w-0 flex-1">
                        <span class="text-xs font-bold text-blue-800 block truncate">Invoice-{{ $invoice->invoice_number }}.pdf</span>
                        <span class="text-[10px] text-blue-600 font-semibold uppercase">PDF Attachment Attached Automatically</span>
                    </div>
                </div>

                <!-- Form Action Buttons -->
                <div class="pt-4 border-t border-slate-100 flex items-center space-x-3">
                    <button type="submit" id="sendEmailSubmitBtn" class="flex-1 flex items-center justify-center space-x-2 py-3 px-4 rounded-xl text-sm font-bold bg-blue-600 hover:bg-blue-700 text-white transition shadow-xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                        <span>Send Invoice</span>
                    </button>
                    <button type="button" onclick="closeSendEmailModal()" class="py-3 px-4 rounded-xl text-sm font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 transition">
                        Cancel
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<script>
function openSendEmailModal() {
    $('#emailFormAlert').addClass('hidden').html('');
    $('#sendInvoiceEmailForm').find('input, textarea').removeClass('border-rose-500');
    document.getElementById('sendEmailModal').classList.remove('hidden');
}

function closeSendEmailModal() {
    document.getElementById('sendEmailModal').classList.add('hidden');
}

// Handle AJAX email sending with jQuery Validation & inline alerts
document.addEventListener('DOMContentLoaded', () => {
    jQuery(document).ready(function($) {
        const $form = $('#sendInvoiceEmailForm');
        const $alert = $('#emailFormAlert');
        const $submitBtn = $('#sendEmailSubmitBtn');
        const originalText = $submitBtn.html();

        $form.on('submit', function(e) {
            e.preventDefault();
            
            // Clear previous errors
            $alert.addClass('hidden').html('');
            $form.find('input, textarea').removeClass('border-rose-500');

            // 1. Client-side Validations
            const recipientEmail = $form.find('input[name="recipient_email"]').val().trim();
            const subject = $form.find('input[name="subject"]').val().trim();
            const messageBody = $form.find('textarea[name="message_body"]').val().trim();
            
            let errors = [];

            if (!recipientEmail) {
                errors.push('Recipient email is required.');
                $form.find('input[name="recipient_email"]').addClass('border-rose-500');
            } else {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(recipientEmail)) {
                    errors.push('Please enter a valid email address.');
                    $form.find('input[name="recipient_email"]').addClass('border-rose-500');
                }
            }

            if (!subject) {
                errors.push('Subject is required.');
                $form.find('input[name="subject"]').addClass('border-rose-500');
            }

            if (!messageBody) {
                errors.push('Message body is required.');
                $form.find('textarea[name="message_body"]').addClass('border-rose-500');
            }

            if (errors.length > 0) {
                let errorHtml = '<strong class="block mb-1">Please fix the following validation errors:</strong><ul class="list-disc pl-4 space-y-0.5">';
                errors.forEach(err => {
                    errorHtml += `<li>${err}</li>`;
                });
                errorHtml += '</ul>';
                $alert.removeClass('hidden').html(errorHtml);
                return;
            }

            // 2. Show loading spinner state
            $submitBtn.prop('disabled', true).html(
                `<svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Sending...</span>`
            );

            // 3. AJAX Submission
            $.ajax({
                url: $form.attr('action'),
                method: 'POST',
                data: $form.serialize(),
                dataType: 'json',
                success: function(response) {
                    $submitBtn.prop('disabled', false).html(originalText);
                    if (response.success) {
                        closeSendEmailModal();
                        if (typeof window.showToast === 'function') {
                            window.showToast('success', response.message);
                        }
                    } else {
                        $alert.removeClass('hidden').html(`<strong>Error:</strong> ${response.message}`);
                    }
                },
                error: function(xhr) {
                    $submitBtn.prop('disabled', false).html(originalText);
                    let errorMsg = 'Failed to send email. Please try again.';
                    
                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                        const validationErrors = xhr.responseJSON.errors;
                        let errorHtml = '<strong class="block mb-1">Server validation errors:</strong><ul class="list-disc pl-4 space-y-0.5">';
                        Object.keys(validationErrors).forEach(field => {
                            validationErrors[field].forEach(msg => {
                                errorHtml += `<li>${msg}</li>`;
                            });
                            $form.find(`[name="${field}"]`).addClass('border-rose-500');
                        });
                        errorHtml += '</ul>';
                        errorMsg = errorHtml;
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = `<strong>Error:</strong> ${xhr.responseJSON.message}`;
                    }
                    
                    $alert.removeClass('hidden').html(errorMsg);
                }
            });
        });
    });
});
</script>
@endsection
