@extends('layouts.app')

@section('title', 'Profile Information')

@section('content')
<div class="space-y-6">
    <!-- Header with Back Button -->
    <div class="flex items-center justify-between pb-4 border-b border-slate-200">
        <div class="flex items-center space-x-4">
            <h1 class="text-2xl font-bold text-slate-800">Profile Information</h1>
            <a href="{{ route('overview') }}" class="flex items-center space-x-1.5 bg-white hover:bg-slate-50 border border-slate-200 px-3 py-1.5 rounded-lg text-xs font-bold text-slate-700 shadow-sm transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                <span>Back to Panel</span>
            </a>
        </div>
    </div>

    <!-- Forms Layout Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        <!-- Form 1: Profile Information -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8 flex flex-col justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Profile Information</h2>
                <p class="text-xs text-slate-400 mt-1 mb-6">Update your account's profile information and email address.</p>
                
                <form action="{{ route('profile.update') }}" method="POST" class="ajax-form no-reset space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Name</label>
                        <input type="text" name="name" value="{{ Auth::user()->name }}" required
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-800 font-semibold">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Email</label>
                        <input type="email" name="email" value="{{ Auth::user()->email }}" required
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-800 font-semibold">
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="btn-primary py-2.5 px-6 text-sm font-bold">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Form 2: Update Password -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8 flex flex-col justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Update Password</h2>
                <p class="text-xs text-slate-400 mt-1 mb-6">Ensure your account is using a long, random password to stay secure.</p>
                
                <form action="{{ route('profile.password') }}" method="POST" class="ajax-form no-reset space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Current Password</label>
                        <input type="password" name="current_password" required
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-800">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">New Password</label>
                        <input type="password" name="new_password" required
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-800">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Confirm Password</label>
                        <input type="password" name="new_password_confirmation" required
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-800">
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="btn-primary py-2.5 px-6 text-sm font-bold">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <!-- Form 3: Business Settings (Full Width below) -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8 mt-8">
        <h2 class="text-xl font-bold text-slate-800">Business Profile & Invoice Settings</h2>
        <p class="text-xs text-slate-400 mt-1 mb-6">Manage the business details, GSTIN compliance, and logo used on printable tax invoices and the main panel.</p>
        
        <form action="{{ route('profile.business') }}" method="POST" enctype="multipart/form-data" class="ajax-form no-reset space-y-6">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Business / Company Name</label>
                    <input type="text" name="business_name" value="{{ \App\Models\Setting::get('business_name', 'Praful Welding Works') }}" required
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-800 font-semibold">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Invoice Subtitle / Tagline</label>
                    <input type="text" name="business_subtitle" value="{{ \App\Models\Setting::get('business_subtitle', 'Heavy Fabrication & Industrial Racks ERP') }}" required
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-800">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">GSTIN Number</label>
                    <input type="text" name="gstin" value="{{ \App\Models\Setting::get('gstin', '24PWWRK1234A1Z0') }}" required
                           minlength="15" maxlength="15" pattern="^[0-9]{2}[A-Za-z]{5}[0-9]{4}[A-Za-z]{1}[1-9A-Za-z]{1}[Zz][0-9A-Za-z]{1}$"
                           title="Please enter a valid 15-character GSTIN (e.g. 24AAAAA1111A1Z5)"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-800 font-mono uppercase">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">MSME / Udyam No.</label>
                    <input type="text" name="msme_number" value="{{ \App\Models\Setting::get('msme_number', 'UDYAM-GJ-24-0012345') }}" placeholder="UDYAM-GJ-24-0012345"
                           pattern="^UDYAM-[A-Za-z]{2}-[0-9]{2}-[0-9]{7}$"
                           title="Format: UDYAM-XX-00-0000000 (e.g. UDYAM-GJ-24-0012345)"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-800 font-mono uppercase">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Business Address</label>
                    <input type="text" name="address" value="{{ \App\Models\Setting::get('address', trim(\App\Models\Setting::get('address_line_1', 'Plot No. 12, G.I.D.C. Metoda,') . ' ' . \App\Models\Setting::get('address_line_2', 'Rajkot, Gujarat - 360021'))) }}" required
                           placeholder="Plot No. 12, G.I.D.C. Metoda, Rajkot, Gujarat - 360021"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-800 font-medium">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Business Email</label>
                    <input type="email" name="business_email" value="{{ \App\Models\Setting::get('business_email', 'pww@example.com') }}" required
                           placeholder="e.g. pww@example.com"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-800 font-medium">
                </div>
            </div>

            <div class="border-t border-slate-100 pt-6">
                <h3 class="text-sm font-bold text-slate-800 mb-4">Settlement Bank Account Details</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Bank Name</label>
                        <input type="text" name="bank_name" value="{{ \App\Models\Setting::get('bank_name', 'State Bank of India (SBI)') }}" required
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-800 font-semibold">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Account Holder Name</label>
                        <input type="text" name="bank_account_name" value="{{ \App\Models\Setting::get('bank_account_name', 'Praful Welding Works') }}" required
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-800">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">A/C Number</label>
                        <input type="text" name="bank_account_no" value="{{ \App\Models\Setting::get('bank_account_no', '33445566778') }}" required
                               minlength="9" maxlength="18" pattern="^[0-9A-Za-z]+$"
                               title="Please enter a valid 9 to 18 digit account number"
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-800 font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">IFSC Code</label>
                        <input type="text" name="bank_ifsc" value="{{ \App\Models\Setting::get('bank_ifsc', 'SBIN0001234') }}" required
                               minlength="11" maxlength="11" pattern="^[A-Za-z]{4}[0O][0-9A-Za-z]{6}$"
                               title="Format: 11-character Bank IFSC Code (e.g. SBIN0001234)"
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-800 font-mono uppercase">
                    </div>
                </div>
            </div>

            <div class="border-t border-slate-100 pt-6">
                <h3 class="text-sm font-bold text-slate-800 mb-4">Invoice & Order Prefix Formatting</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Invoice Number Prefix</label>
                        <input type="text" name="invoice_prefix" value="{{ \App\Models\Setting::get('invoice_prefix', 'PWW-') }}" placeholder="e.g. PWW- or PWW/2026-27/" required
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-800 font-mono uppercase">
                        <p class="text-[10px] text-slate-400 mt-1">Generated Invoice Format: <code class="font-bold text-blue-600">{{ \App\Models\Setting::get('invoice_prefix', 'PWW-') }}20260727-0001</code></p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Sales Order Prefix</label>
                        <input type="text" name="order_prefix" value="{{ \App\Models\Setting::get('order_prefix', 'PWW-ORD-') }}" placeholder="e.g. PWW-ORD- or ORD/" required
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-800 font-mono uppercase">
                        <p class="text-[10px] text-slate-400 mt-1">Generated Order Format: <code class="font-bold text-amber-600">{{ \App\Models\Setting::get('order_prefix', 'PWW-ORD-') }}20260727-0001</code></p>
                    </div>
                </div>
            </div>

            <div class="border-t border-slate-100 pt-6">
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Invoice Terms & Conditions (One rule per line)</label>
                @php
                    $defaultTerms = "1. All disputes are subject to Rajkot jurisdiction.\n2. Interest @18% p.a. charged on overdue payments after due date.\n3. Goods once dispatched/sold cannot be returned or exchanged.";
                    $termsVal = \App\Models\Setting::get('terms_and_conditions', $defaultTerms);
                @endphp
                <textarea name="terms_and_conditions" rows="3" placeholder="Enter terms and conditions line by line..."
                          class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-800 font-medium">{{ $termsVal }}</textarea>
                <p class="text-[10px] text-slate-400 mt-1">Each line will be printed as a bullet point at the bottom of tax invoices and sale bills.</p>
            </div>

            <div class="border-t border-slate-100 pt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-3">Company Brand Logo</label>
                    <div class="flex items-center space-x-4">
                        <div class="w-16 h-16 bg-slate-100 rounded-xl border border-slate-200 flex items-center justify-center overflow-hidden flex-shrink-0">
                            <img id="logo-preview-img" src="{{ asset(\App\Models\Setting::get('logo_path', 'logo.jpg')) }}" alt="Company Logo" class="w-full h-full object-contain">
                        </div>
                        <div class="flex-grow">
                            <input type="file" name="logo" accept="image/*" id="logo-file-input"
                                   class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            <p class="text-[10px] text-slate-400 mt-1.5">Recommended: Square PNG format. Max 2MB.</p>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-3">Authorized Signature Image / Stamp</label>
                    <div class="flex items-center space-x-4">
                        <div class="w-24 h-16 bg-slate-100 rounded-xl border border-slate-200 flex items-center justify-center overflow-hidden flex-shrink-0 p-1">
                            @php $sigPath = \App\Models\Setting::get('signature_path'); @endphp
                            <img id="signature-preview-img" src="{{ $sigPath ? asset($sigPath) : '' }}" alt="Signature Preview" class="{{ $sigPath ? '' : 'hidden' }} max-h-full max-w-full object-contain">
                            <span id="signature-placeholder" class="{{ $sigPath ? 'hidden' : '' }} text-[10px] text-slate-400 font-bold text-center">No Stamp</span>
                        </div>
                        <div class="flex-grow">
                            <input type="file" name="signature" accept="image/*" id="signature-file-input"
                                   class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            <p class="text-[10px] text-slate-400 mt-1.5">Printed above Authorized Signatory line. Max 2MB.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end">
                <button type="submit" class="btn-primary py-2.5 px-6 text-sm font-bold cursor-pointer">
                    Save Business Profile
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.getElementById('logo-file-input').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                document.getElementById('logo-preview-img').src = event.target.result;
            };
            reader.readAsDataURL(file);
        }
    });

    document.getElementById('signature-file-input').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                const img = document.getElementById('signature-preview-img');
                const ph = document.getElementById('signature-placeholder');
                if (img) {
                    img.src = event.target.result;
                    img.classList.remove('hidden');
                }
                if (ph) ph.classList.add('hidden');
            };
            reader.readAsDataURL(file);
        }
    });
</script>
@endsection
