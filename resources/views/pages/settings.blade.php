@extends('layouts.app')

@section('title', 'System Settings & User Access')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-slate-200/80">
        <div>
            <h1 class="text-2xl font-black text-slate-800 tracking-tight flex items-center gap-2">
                <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                System Settings & User Access Hub
            </h1>
            <p class="text-slate-500 text-xs font-medium mt-1">Configure active ERP modules, manage team login accounts, set role permissions, and customize business branding</p>
        </div>
    </div>



    <!-- Navigation Tabs -->
    <!-- Navigation Tabs -->
    <div class="bg-white rounded-2xl p-2 border border-slate-200/80 shadow-sm flex flex-wrap gap-2">
        <button onclick="switchSettingsTab('profile')" id="tabBtn-profile" class="tab-btn active-tab-btn flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
            Business Profile
        </button>

        <button onclick="switchSettingsTab('bank')" id="tabBtn-bank" class="tab-btn flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-50 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
            </svg>
            Bank & Billing
        </button>

        <button onclick="switchSettingsTab('users')" id="tabBtn-users" class="tab-btn flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-50 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            User Roles
        </button>

        <button onclick="switchSettingsTab('modules')" id="tabBtn-modules" class="tab-btn flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-50 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z" />
            </svg>
            Active Modules
        </button>

        <!-- TAB 5: Other Settings Dropdown Menu Button -->
        <div class="relative inline-block text-left" id="otherSettingsDropdownWrapper">
            <button onclick="toggleOtherSettingsDropdown(event)" id="tabBtn-other" class="tab-btn flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-50 transition">
                <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                </svg>
                <span id="otherSettingsTabLabel">Other Settings</span>
                <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <!-- Dropdown Menu Popup -->
            <div id="otherSettingsDropdownMenu" class="hidden absolute right-0 mt-2 w-60 rounded-2xl bg-white shadow-xl border border-slate-200/90 p-1.5 z-50">
                <button type="button" onclick="selectOtherSettingsSub('serials')" id="otherOpt-serials" class="other-opt-btn w-full flex items-center gap-2.5 px-3 py-2.5 text-xs font-bold text-slate-700 hover:bg-blue-50 hover:text-blue-700 transition rounded-xl">
                    <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                    </svg>
                    <span>Auto Serial & Prefixes</span>
                </button>

                <button type="button" onclick="selectOtherSettingsSub('financial')" id="otherOpt-financial" class="other-opt-btn w-full flex items-center gap-2.5 px-3 py-2.5 text-xs font-bold text-slate-700 hover:bg-blue-50 hover:text-blue-700 transition rounded-xl">
                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Tax & Financial</span>
                </button>

                <button type="button" onclick="selectOtherSettingsSub('email')" id="otherOpt-email" class="other-opt-btn w-full flex items-center gap-2.5 px-3 py-2.5 text-xs font-bold text-slate-700 hover:bg-blue-50 hover:text-blue-700 transition rounded-xl">
                    <svg class="w-4 h-4 text-indigo-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <span>Email (SMTP)</span>
                </button>

                <button type="button" onclick="selectOtherSettingsSub('categories')" id="otherOpt-categories" class="other-opt-btn w-full flex items-center gap-2.5 px-3 py-2.5 text-xs font-bold text-slate-700 hover:bg-blue-50 hover:text-blue-700 transition rounded-xl">
                    <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    <span>Purchase & Expense Categories</span>
                </button>

                <button type="button" onclick="selectOtherSettingsSub('security')" id="otherOpt-security" class="other-opt-btn w-full flex items-center gap-2.5 px-3 py-2.5 text-xs font-bold text-slate-700 hover:bg-blue-50 hover:text-blue-700 transition rounded-xl">
                    <svg class="w-4 h-4 text-purple-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    <span>Security & Backups</span>
                </button>
            </div>
        </div>
    </div>

    <!-- TAB 1: Business Profile & Branding -->
    <div id="settingsTab-profile" class="tab-content space-y-6">
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-6">
            <h2 class="text-lg font-bold text-slate-800 border-b border-slate-100 pb-4">Business Information & Official Branding</h2>

            <form action="{{ route('settings.business') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Company / Business Name</label>
                        <input type="text" name="business_name" value="{{ \App\Models\Setting::get('business_name', 'Praful Welding Works') }}" required class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-semibold text-slate-800">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Business Subtitle</label>
                        <input type="text" name="business_subtitle" value="{{ \App\Models\Setting::get('business_subtitle', '') }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-semibold text-slate-800">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Official Email</label>
                        <input type="email" name="business_email" value="{{ \App\Models\Setting::get('business_email', 'vekariyah@gmail.com') }}" required class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-semibold text-slate-800">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Mobile Contact</label>
                        <input type="text" name="business_mobile" value="{{ \App\Models\Setting::get('business_mobile', '9409604420') }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-semibold text-slate-800">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">GSTIN Number</label>
                        <input type="text" name="gstin" value="{{ \App\Models\Setting::get('gstin', '24AFHPV5264M1ZU') }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-semibold text-slate-800 font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">MSME Udhyam Registration</label>
                        <input type="text" name="msme_number" value="{{ \App\Models\Setting::get('msme_number', 'UDYAM-GJ-20-0177569') }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-semibold text-slate-800">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Factory Registered Address</label>
                        <input type="text" name="address_line_1" value="{{ \App\Models\Setting::get('address_line_1', 'VILLAGE : KHORANA TA : RAJKOT DI : RAJKOT - 360 003') }}" required class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-semibold text-slate-800">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 border-t border-slate-100 pt-4">
                    <!-- Logo Upload -->
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-2">Company Logo</label>
                        <div class="flex items-center gap-4">
                            <img src="{{ asset(\App\Models\Setting::get('logo_path', 'logo.jpg')) }}" class="w-14 h-14 object-contain rounded-xl border border-slate-200 bg-slate-50 p-1">
                            <input type="file" name="logo" accept="image/*" class="text-xs text-slate-600 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700">
                        </div>
                    </div>

                    <!-- Signature Upload -->
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-2">Authorized Stamp / Signature</label>
                        <div class="flex items-center gap-4">
                            <img src="{{ asset(\App\Models\Setting::get('signature_path', 'uploads/signature_1785313553.png')) }}" class="w-14 h-14 object-contain rounded-xl border border-slate-200 bg-slate-50 p-1">
                            <input type="file" name="signature" accept="image/*" class="text-xs text-slate-600 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700">
                        </div>
                    </div>
                </div>

                <div class="pt-4 flex justify-end">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-xl text-sm transition shadow-sm">
                        Save Business Profile
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- TAB 2: Bank & Billing Defaults -->
    <div id="settingsTab-bank" class="tab-content hidden space-y-6">
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-6">
            <h2 class="text-lg font-bold text-slate-800 border-b border-slate-100 pb-4">Bank Account & Default Billing Terms</h2>

            <form action="{{ route('settings.bank') }}" method="POST" class="ajax-form space-y-6">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Bank Name</label>
                        <input type="text" name="bank_name" value="{{ \App\Models\Setting::get('bank_name', 'JIVAN COMMERCIAL CO OP BANK LTD') }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-semibold text-slate-800">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Account Holder Name</label>
                        <input type="text" name="bank_account_name" value="{{ \App\Models\Setting::get('bank_account_name', 'Praful Welding Works') }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-semibold text-slate-800">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Account Number</label>
                        <input type="text" name="bank_account_no" value="{{ \App\Models\Setting::get('bank_account_no', '443005101001972') }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-semibold text-slate-800 font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">IFSC Code</label>
                        <input type="text" name="bank_ifsc" value="{{ \App\Models\Setting::get('bank_ifsc', 'IBKL0JIVAN3') }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-semibold text-slate-800 font-mono">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Invoice Terms & Conditions</label>
                        <textarea name="terms_and_conditions" rows="4" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm font-medium text-slate-800">{{ \App\Models\Setting::get('terms_and_conditions', '') }}</textarea>
                    </div>
                </div>

                <div class="pt-4 flex justify-end">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-xl text-sm transition shadow-sm">
                        Save Bank Defaults
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- TAB 5: Other System Settings (Combines Auto Serial, Tax, Email, Security) -->
    <div id="settingsTab-other" class="tab-content hidden space-y-6">


        <!-- Sub Content 1: Auto-Increment Serial & Prefixes -->
        <div id="subTab-serials" class="sub-tab-content space-y-6">
            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-6">
                <div>
                    <h2 class="text-lg font-bold text-slate-800">Auto Increment Serial Reset & Document Prefixes</h2>
                    <p class="text-slate-500 text-xs mt-1">Configure serial number sequences and custom document prefixes for Tax Invoices, Sales Orders, Quotations, and Delivery Challans.</p>
                </div>

                <form action="{{ route('settings.serials') }}" method="POST" class="ajax-form space-y-6">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Invoice Prefix</label>
                            <input type="text" name="invoice_prefix" id="invoice_prefix_input" oninput="updateSerialPreview()" value="{{ \App\Models\Setting::get('invoice_prefix', 'PWW-') }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-semibold text-slate-800 font-mono uppercase">
                            <span class="text-[11px] text-slate-400">e.g. PWW- (Leave empty for pure serial number like 0001)</span>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Next Invoice Serial Number</label>
                            <input type="number" name="invoice_next_sequence" id="invoice_seq_input" oninput="updateSerialPreview()" value="{{ \App\Models\Setting::get('invoice_next_sequence', '1') }}" min="1" required class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-semibold text-slate-800 font-mono">
                            <span class="text-[11px] text-slate-400">Current Next Invoice Sequence (Set or Reset)</span>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Sales Order Prefix</label>
                            <input type="text" name="order_prefix" value="{{ \App\Models\Setting::get('order_prefix', 'PWW-ORD-') }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-semibold text-slate-800 font-mono uppercase">
                            <span class="text-[11px] text-slate-400">e.g. PWW-ORD- (Leave empty for pure serial number)</span>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Next Order Serial Number</label>
                            <input type="number" name="order_next_sequence" value="{{ \App\Models\Setting::get('order_next_sequence', '1') }}" min="1" required class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-semibold text-slate-800 font-mono">
                            <span class="text-[11px] text-slate-400">Current Next Sales Order Sequence</span>
                        </div>



                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Middle Date Portion Format</label>
                            <select name="serial_date_format" id="serial_date_format_select" onchange="updateSerialPreview()" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-semibold text-slate-800">
                                <option value="Ymd" {{ \App\Models\Setting::get('serial_date_format', 'Ymd') === 'Ymd' ? 'selected' : '' }}>Full Date Format (e.g. {{ date('Ymd') }})</option>
                                <option value="Ym" {{ \App\Models\Setting::get('serial_date_format') === 'Ym' ? 'selected' : '' }}>Year & Month (e.g. {{ date('Ym') }})</option>
                                <option value="ym" {{ \App\Models\Setting::get('serial_date_format') === 'ym' ? 'selected' : '' }}>Short Year & Month (e.g. {{ date('ym') }})</option>
                                <option value="FY" {{ \App\Models\Setting::get('serial_date_format') === 'FY' ? 'selected' : '' }}>Financial Year Format (e.g. 2627)</option>
                                <option value="none" {{ \App\Models\Setting::get('serial_date_format') === 'none' ? 'selected' : '' }}>No Date in Middle (Prefix + Serial Only)</option>
                            </select>
                            <span class="text-[11px] text-slate-400">Controls date pattern in middle of invoice numbers</span>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Ending Serial Number Digits</label>
                            <select name="serial_number_digits" id="serial_number_digits_select" onchange="updateSerialPreview()" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-semibold text-slate-800 font-mono">
                                <option value="4" {{ \App\Models\Setting::get('serial_number_digits', '4') == '4' ? 'selected' : '' }}>4 Digits (e.g. 0001)</option>
                                <option value="5" {{ \App\Models\Setting::get('serial_number_digits') == '5' ? 'selected' : '' }}>5 Digits (e.g. 00001)</option>
                                <option value="6" {{ \App\Models\Setting::get('serial_number_digits') == '6' ? 'selected' : '' }}>6 Digits (e.g. 000001)</option>
                                <option value="3" {{ \App\Models\Setting::get('serial_number_digits') == '3' ? 'selected' : '' }}>3 Digits (e.g. 001)</option>
                                <option value="1" {{ \App\Models\Setting::get('serial_number_digits') == '1' ? 'selected' : '' }}>No Zero-Padding (e.g. 1)</option>
                            </select>
                            <span class="text-[11px] text-slate-400">Controls total digit length of ending serial sequence</span>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Annual Auto-Reset Frequency</label>
                            <select name="serial_reset_frequency" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-semibold text-slate-800">
                                <option value="financial_year" {{ \App\Models\Setting::get('serial_reset_frequency', 'financial_year') === 'financial_year' ? 'selected' : '' }}>Reset serial sequence to 0001 at start of New Financial Year (April 1st)</option>
                                <option value="monthly" {{ \App\Models\Setting::get('serial_reset_frequency') === 'monthly' ? 'selected' : '' }}>Reset serial sequence monthly (1st of every month)</option>
                                <option value="never" {{ \App\Models\Setting::get('serial_reset_frequency') === 'never' ? 'selected' : '' }}>Continuous sequential numbers (Never reset automatically)</option>
                            </select>
                        </div>

                        <!-- Live Sample Preview Card -->
                        <div class="md:col-span-2 bg-gradient-to-r from-blue-50 to-indigo-50/50 border border-blue-100 rounded-xl p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-blue-600 text-white flex items-center justify-center font-bold text-xs shrink-0">
                                    #
                                </div>
                                <div>
                                    <span class="text-xs font-bold text-slate-700 block">Generated Invoice Number Sample Preview:</span>
                                    <span id="invoice_sample_preview" class="text-base font-black text-blue-700 font-mono tracking-wide">PWW-20260731-0001</span>
                                </div>
                            </div>
                            <span class="text-[11px] font-semibold text-blue-600 bg-white border border-blue-200 px-3 py-1 rounded-lg shadow-2xs">Real-Time Pattern Preview</span>
                        </div>
                    </div>

                    <div class="pt-4 flex justify-end">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-xl text-sm transition shadow-sm">
                            Save Serial & Prefix Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sub Content 2: Tax & Financial Settings -->
        <div id="subTab-financial" class="sub-tab-content hidden space-y-6">
            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-6">
                <div>
                    <h2 class="text-lg font-bold text-slate-800">Tax & Financial Settings</h2>
                    <p class="text-slate-500 text-xs mt-1">Configure default GST slabs, Financial Year cycle, and currency numbering display standards.</p>
                </div>

                <form action="{{ route('settings.financial') }}" method="POST" class="space-y-6">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Default GST Rate Slab (%)</label>
                            <select name="default_gst_rate" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-semibold text-slate-800">
                                <option value="18" {{ \App\Models\Setting::get('default_gst_rate', '18') == '18' ? 'selected' : '' }}>18% GST (Standard Metal Fabrication & Industrial Goods)</option>
                                <option value="12" {{ \App\Models\Setting::get('default_gst_rate') == '12' ? 'selected' : '' }}>12% GST</option>
                                <option value="5" {{ \App\Models\Setting::get('default_gst_rate') == '5' ? 'selected' : '' }}>5% GST</option>
                                <option value="0" {{ \App\Models\Setting::get('default_gst_rate') == '0' ? 'selected' : '' }}>0% (Exempt / Job Work)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Financial Year Start Month</label>
                            <select name="financial_year_start_month" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-semibold text-slate-800">
                                <option value="4" {{ \App\Models\Setting::get('financial_year_start_month', '4') == '4' ? 'selected' : '' }}>April (Indian Financial Year: Apr 1 - Mar 31)</option>
                                <option value="1" {{ \App\Models\Setting::get('financial_year_start_month') == '1' ? 'selected' : '' }}>January (Calendar Year: Jan 1 - Dec 31)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Currency & Number Format Style</label>
                            <select name="number_format_style" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-semibold text-slate-800">
                                <option value="indian" {{ \App\Models\Setting::get('number_format_style', 'indian') === 'indian' ? 'selected' : '' }}>Indian Format (₹ 1,00,000.00 - Lakhs / Crores)</option>
                                <option value="international" {{ \App\Models\Setting::get('number_format_style') === 'international' ? 'selected' : '' }}>Standard International (₹ 100,000.00 - Millions)</option>
                            </select>
                        </div>
                    </div>

                    <div class="pt-4 flex justify-end">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-xl text-sm transition shadow-sm">
                            Save Financial & Tax Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sub Content 3: Email / SMTP Settings -->
        <div id="subTab-email" class="sub-tab-content hidden space-y-6">
            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-6">
                <div>
                    <h2 class="text-lg font-bold text-slate-800">Automatic Outbound Email Settings</h2>
                    <p class="text-slate-500 text-xs mt-1">Configure your official email account to send tax invoices, sales orders, and reports directly to clients.</p>
                </div>

                <form action="{{ route('settings.email') }}" method="POST" class="space-y-6">
                    @csrf
                    
                    <!-- Email Provider Selection Preset -->
                    <div class="bg-blue-50/60 p-4 rounded-xl border border-blue-100 space-y-3">
                        <label class="block text-xs font-bold text-blue-900 uppercase">Select Email Service Provider</label>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <label onclick="applyEmailPreset('gmail')" class="flex items-center gap-3 p-3 bg-white border border-slate-200 rounded-xl cursor-pointer hover:border-blue-500 transition">
                                <input type="radio" name="email_provider_preset" value="gmail" checked class="w-4 h-4 text-blue-600">
                                <div>
                                    <span class="block text-xs font-bold text-slate-800">Gmail / Google Workspace</span>
                                    <span class="block text-[11px] text-slate-400">1-Click Auto Configured</span>
                                </div>
                            </label>

                            <label onclick="applyEmailPreset('outlook')" class="flex items-center gap-3 p-3 bg-white border border-slate-200 rounded-xl cursor-pointer hover:border-blue-500 transition">
                                <input type="radio" name="email_provider_preset" value="outlook" class="w-4 h-4 text-blue-600">
                                <div>
                                    <span class="block text-xs font-bold text-slate-800">Outlook / Office 365</span>
                                    <span class="block text-[11px] text-slate-400">Microsoft Mail</span>
                                </div>
                            </label>

                            <label onclick="applyEmailPreset('custom')" class="flex items-center gap-3 p-3 bg-white border border-slate-200 rounded-xl cursor-pointer hover:border-blue-500 transition">
                                <input type="radio" name="email_provider_preset" value="custom" class="w-4 h-4 text-blue-600">
                                <div>
                                    <span class="block text-xs font-bold text-slate-800">Custom Server</span>
                                    <span class="block text-[11px] text-slate-400">Advanced Server Options</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Simple Inputs for Non-Technical Users -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Your Official Email Address</label>
                            <input type="email" name="mail_from_address" value="{{ \App\Models\Setting::get('mail_from_address', 'vekariyah@gmail.com') }}" placeholder="e.g. vekariyah@gmail.com" required class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-semibold text-slate-800">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Company / Sender Display Name</label>
                            <input type="text" name="mail_from_name" value="{{ \App\Models\Setting::get('mail_from_name', 'Praful Welding Works') }}" placeholder="e.g. Praful Welding Works" required class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-semibold text-slate-800">
                        </div>

                        <div class="md:col-span-2">
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-xs font-bold text-slate-600 uppercase">Google App Password (16 Characters)</label>
                                <a href="https://myaccount.google.com/apppasswords" target="_blank" class="text-[11px] font-bold text-blue-600 hover:underline flex items-center gap-1">
                                    <span>Get Google App Password ↗</span>
                                </a>
                            </div>
                            <div class="relative">
                                <input type="password" id="smtpPasswordInput" name="mail_password" value="{{ \App\Models\Setting::get('mail_password', '') }}" placeholder="Enter 16-character App Password (e.g. abcd efgh ijkl mnop)" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 pl-3.5 pr-10 text-sm font-semibold text-slate-800 font-mono">
                                <button type="button" onclick="togglePasswordVisibility('smtpPasswordInput', 'smtpEyeIcon', 'smtpEyeOffIcon')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                                    <svg id="smtpEyeIcon" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <svg id="smtpEyeOffIcon" class="h-4 w-4 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858-5.908a10.018 10.018 0 014.122-.963c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21M3 3l18 18" />
                                    </svg>
                                </button>
                            </div>
                            <p class="text-[11px] text-slate-400 mt-1">For security, Gmail requires an <strong>App Password</strong> instead of your personal email password.</p>
                        </div>
                    </div>

                    <!-- Hidden / Collapsible Advanced Technical Parameters -->
                    <div class="border-t border-slate-100 pt-4">
                        <button type="button" onclick="document.getElementById('advancedSmtpSection').classList.toggle('hidden')" class="text-xs font-bold text-slate-500 hover:text-slate-800 flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            </svg>
                            <span>Advanced Technical Parameters (Optional)</span>
                        </button>

                        <div id="advancedSmtpSection" class="hidden grid grid-cols-1 md:grid-cols-3 gap-4 mt-4 bg-slate-50 p-4 rounded-xl border border-slate-200">
                            <div>
                                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">SMTP Host Server</label>
                                <input type="text" id="smtpHostInput" name="mail_host" value="{{ \App\Models\Setting::get('mail_host', 'smtp.gmail.com') }}" required class="w-full bg-white border border-slate-200 rounded-xl py-2 px-3 text-xs font-semibold text-slate-800 font-mono">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">SMTP Port</label>
                                <input type="number" id="smtpPortInput" name="mail_port" value="{{ \App\Models\Setting::get('mail_port', '587') }}" required class="w-full bg-white border border-slate-200 rounded-xl py-2 px-3 text-xs font-semibold text-slate-800 font-mono">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Encryption Protocol</label>
                                <select id="smtpEncryptionInput" name="mail_encryption" class="w-full bg-white border border-slate-200 rounded-xl py-2 px-3 text-xs font-semibold text-slate-800">
                                    <option value="tls" {{ \App\Models\Setting::get('mail_encryption', 'tls') === 'tls' ? 'selected' : '' }}>TLS (Port 587)</option>
                                    <option value="ssl" {{ \App\Models\Setting::get('mail_encryption') === 'ssl' ? 'selected' : '' }}>SSL (Port 465)</option>
                                    <option value="none" {{ \App\Models\Setting::get('mail_encryption') === 'none' ? 'selected' : '' }}>None</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 flex justify-end">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-xl text-sm transition shadow-sm">
                            Save Email Settings
                        </button>
                    </div>
                </form>

                <!-- Diagnostics Test Email Form -->
                <div class="border-t border-slate-100 pt-6 mt-6">
                    <h3 class="text-sm font-bold text-slate-800 mb-2">Send Diagnostic Test Email</h3>
                    <p class="text-xs text-slate-500 mb-4">Verify your mail server connectivity by sending an instant test message.</p>

                    <form action="{{ route('settings.email.test') }}" method="POST" class="flex flex-col sm:flex-row gap-3">
                        @csrf
                        <input type="email" name="test_email" value="{{ auth()->user()->email }}" placeholder="Enter recipient email address" required class="flex-1 bg-slate-50 border border-slate-200 rounded-xl py-2 px-3.5 text-sm font-semibold text-slate-800">
                        <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-5 rounded-xl text-xs transition shadow-sm flex items-center justify-center gap-2 shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                            </svg>
                            Send Test Email
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sub Content 3.5: Purchase & Expense Categories -->
        @php
            $purchaseCategoriesList = \App\Services\CategoryService::getPurchaseCategories();
            $expenseCategoriesList = \App\Services\CategoryService::getExpenseCategories();
        @endphp
        <div id="subTab-categories" class="sub-tab-content hidden space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- 1. Purchase Categories Manager -->
                <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div>
                            <h2 class="text-base font-bold text-slate-800 flex items-center gap-2">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                                Purchase Categories
                            </h2>
                            <p class="text-xs text-slate-500 mt-0.5">Manage categories available in the Purchase Ledger.</p>
                        </div>
                        <button type="button" onclick="openAddCategoryModal('purchase')" class="bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 text-xs font-bold py-1.5 px-3 rounded-xl transition flex items-center gap-1 cursor-pointer">
                            <span>+ Add Category</span>
                        </button>
                    </div>

                    <div class="space-y-2">
                        @foreach ($purchaseCategoriesList as $pCat)
                            <div class="flex items-center justify-between p-3 bg-slate-50 border border-slate-200/70 rounded-xl text-xs">
                                <div class="flex items-center space-x-2">
                                    <span class="font-bold text-slate-800">{{ $pCat['label'] }}</span>
                                    @if ($pCat['key'] === 'raw_material' || (!empty($pCat['protected']) && $pCat['protected']))
                                        <span class="px-2 py-0.5 bg-amber-100 text-amber-900 border border-amber-200 rounded font-bold text-[10px]" title="Mandatory system category required for automatic inventory restock. Cannot be deleted.">
                                            🔒 Mandatory System Category
                                        </span>
                                    @endif
                                </div>
                                <div class="flex items-center space-x-1.5">
                                    <button type="button" onclick="openEditCategoryModal('purchase', '{{ $pCat['key'] }}', '{{ addslashes($pCat['label']) }}')" class="w-7 h-7 inline-flex items-center justify-center rounded-lg bg-amber-500 hover:bg-amber-600 text-white shadow-2xs transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 012-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </button>
                                    @if ($pCat['key'] !== 'raw_material' && empty($pCat['protected']))
                                        <button type="button" onclick="deleteCategorySetting('purchase', '{{ $pCat['key'] }}', '{{ addslashes($pCat['label']) }}')" class="w-7 h-7 inline-flex items-center justify-center rounded-lg bg-rose-500 hover:bg-rose-600 text-white shadow-2xs transition">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- 2. Expense Categories Manager -->
                <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div>
                            <h2 class="text-base font-bold text-slate-800 flex items-center gap-2">
                                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                Expense Categories
                            </h2>
                            <p class="text-xs text-slate-500 mt-0.5">Manage categories available in the Expenses Ledger.</p>
                        </div>
                        <button type="button" onclick="openAddCategoryModal('expense')" class="bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 text-xs font-bold py-1.5 px-3 rounded-xl transition flex items-center gap-1 cursor-pointer">
                            <span>+ Add Category</span>
                        </button>
                    </div>

                    <div class="space-y-2">
                        @foreach ($expenseCategoriesList as $eCat)
                            <div class="flex items-center justify-between p-3 bg-slate-50 border border-slate-200/70 rounded-xl text-xs">
                                <div class="flex items-center space-x-2">
                                    <span class="font-bold text-slate-800">{{ $eCat['label'] }}</span>
                                    @if (in_array($eCat['key'], ['salary', 'gst_payment']) || (!empty($eCat['protected']) && $eCat['protected']))
                                        <span class="px-2 py-0.5 bg-amber-100 text-amber-900 border border-amber-200 rounded font-bold text-[10px]" title="Mandatory system category required for payroll and tax ledgers. Cannot be deleted.">
                                            🔒 Mandatory System Category
                                        </span>
                                    @endif
                                </div>
                                <div class="flex items-center space-x-1.5">
                                    <button type="button" onclick="openEditCategoryModal('expense', '{{ $eCat['key'] }}', '{{ addslashes($eCat['label']) }}')" class="w-7 h-7 inline-flex items-center justify-center rounded-lg bg-amber-500 hover:bg-amber-600 text-white shadow-2xs transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </button>
                                    @if (!in_array($eCat['key'], ['salary', 'gst_payment']) && empty($eCat['protected']))
                                        <button type="button" onclick="deleteCategorySetting('expense', '{{ $eCat['key'] }}', '{{ addslashes($eCat['label']) }}')" class="w-7 h-7 inline-flex items-center justify-center rounded-lg bg-rose-500 hover:bg-rose-600 text-white shadow-2xs transition">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Sub Content 4: Security & System Backups -->
        @php
            $settingBackupService = app(\App\Services\BackupService::class);
            $settingLocalBackups = $settingBackupService->listLocalBackups();
            $latestSettingBackup = $settingLocalBackups[0] ?? null;
            $savedTime = \App\Models\Setting::get('auto_backup_time', '00:00');
            $savedDay = \App\Models\Setting::get('auto_backup_day', 'Sunday');
            $savedFreq = \App\Models\Setting::get('auto_backup_frequency', 'monthly');
        @endphp
        <div id="subTab-security" class="sub-tab-content hidden space-y-6">
            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-6">
                <div>
                    <h2 class="text-lg font-bold text-slate-800">Security & Automated System Backups</h2>
                    <p class="text-slate-500 text-xs mt-1">Configure session timeout policies, automated backup schedules, execution times, and manage database recovery restore files.</p>
                </div>

                <!-- Automatic Backup Live Status Card -->
                <div class="bg-slate-50/80 border border-slate-200/80 rounded-xl p-4 flex flex-col md:flex-row md:items-center justify-between gap-3 text-xs">
                    <div class="flex items-center space-x-3">
                        <div class="w-9 h-9 rounded-xl bg-emerald-100 border border-emerald-200 text-emerald-700 flex items-center justify-center font-bold text-sm shrink-0">
                            ✓
                        </div>
                        <div>
                            <span class="text-slate-500 font-medium block">Latest Generated Local Backup</span>
                            @if ($latestSettingBackup)
                                <span class="font-bold text-slate-800 text-sm">{{ $latestSettingBackup['created_at'] }}</span>
                                <span class="text-slate-500 font-mono ml-2">({{ $latestSettingBackup['filename'] }} - {{ $latestSettingBackup['size'] }})</span>
                            @else
                                <span class="font-bold text-slate-500">No backup files generated yet</span>
                            @endif
                        </div>
                    </div>
                    <div class="text-right border-t md:border-t-0 md:border-l border-slate-200 pt-2 md:pt-0 md:pl-4">
                        <span class="text-slate-500 font-medium block">Schedule Rule</span>
                        <span class="font-bold text-blue-700 uppercase">{{ $savedFreq }}</span>
                        <span class="text-slate-600 font-medium">at {{ date('h:i A', strtotime("2026-01-01 $savedTime")) }}</span>
                        @if ($savedFreq === 'weekly')
                            <span class="text-slate-600 font-medium">({{ $savedDay }})</span>
                        @endif
                    </div>
                </div>

                <form action="{{ route('settings.security') }}" method="POST" class="ajax-form space-y-6">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Session Inactivity Timeout (Minutes)</label>
                            <input type="number" name="session_timeout_minutes" value="{{ \App\Models\Setting::get('session_timeout_minutes', '120') }}" min="15" max="1440" required class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-semibold text-slate-800">
                            <span class="text-[11px] text-slate-400">Auto logout idle users after inactive duration (Default: 120 mins)</span>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Automated Local Backup Schedule</label>
                            <select name="auto_backup_frequency" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-semibold text-slate-800">
                                <option value="monthly" {{ $savedFreq === 'monthly' ? 'selected' : '' }}>Automatic Monthly Data Backup (Recommended)</option>
                                <option value="weekly" {{ $savedFreq === 'weekly' ? 'selected' : '' }}>Automatic Weekly Data Backup</option>
                                <option value="daily" {{ $savedFreq === 'daily' ? 'selected' : '' }}>Automatic Daily Data Backup</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Preferred Execution Time</label>
                            <select name="auto_backup_time" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-semibold text-slate-800">
                                @for ($h = 0; $h < 24; $h++)
                                    @php
                                        $val = sprintf('%02d:00', $h);
                                        $formattedTime = date('h:i A', strtotime("2026-01-01 $val"));
                                    @endphp
                                    <option value="{{ $val }}" {{ $savedTime === $val ? 'selected' : '' }}>
                                        {{ $val }} ({{ $formattedTime }})
                                    </option>
                                @endfor
                            </select>
                            <span class="text-[11px] text-slate-400">Target hour of the day to generate automatic backups</span>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Weekly Backup Day (For Weekly Schedule)</label>
                            <select name="auto_backup_day" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-semibold text-slate-800">
                                @foreach (['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'] as $day)
                                    <option value="{{ $day }}" {{ $savedDay === $day ? 'selected' : '' }}>{{ $day }}</option>
                                @endforeach
                            </select>
                            <span class="text-[11px] text-slate-400">Selected day of week when weekly backups run</span>
                        </div>

                        <div>
                            @php
                                $savedRetention = \App\Models\Setting::get('auto_backup_retention', '3_months');
                            @endphp
                            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Auto-Delete Old Backups</label>
                            <select name="auto_backup_retention" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-semibold text-slate-800">
                                <option value="3_months" {{ $savedRetention === '3_months' ? 'selected' : '' }}>After 3 Months (Default - Keeps Latest 1)</option>
                                <option value="1_month" {{ $savedRetention === '1_month' ? 'selected' : '' }}>After 1 Month (Keeps Latest 1)</option>
                                <option value="6_months" {{ $savedRetention === '6_months' ? 'selected' : '' }}>After 6 Months (Keeps Latest 1)</option>
                                <option value="1_year" {{ $savedRetention === '1_year' ? 'selected' : '' }}>After 1 Year (Keeps Latest 1)</option>
                                <option value="never" {{ $savedRetention === 'never' ? 'selected' : '' }}>Never (Keep All Backups)</option>
                            </select>
                            <span class="text-[11px] text-slate-400">Backups older than this period will be deleted automatically, keeping the latest backup file safe.</span>
                        </div>

                        <div class="md:col-span-2">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" name="auto_backup_enabled" value="true" {{ \App\Models\Setting::get('auto_backup_enabled', 'true') === 'true' ? 'checked' : '' }} class="w-4 h-4 text-blue-600 rounded border-slate-300">
                                <span class="text-xs font-bold text-slate-700">Enable Automated Background Database Backups</span>
                            </label>
                        </div>
                    </div>

                    <div class="pt-4 flex justify-end">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-xl text-sm transition shadow-sm">
                            Save Security Preferences
                        </button>
                    </div>
                </form>

                <!-- Shortcut to Full Dedicated Backup & System Restore Module -->
                <div class="border-t border-slate-100 pt-6">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-gradient-to-r from-blue-50 to-indigo-50 p-5 rounded-2xl border border-blue-100/80">
                        <div class="space-y-1">
                            <h3 class="text-sm font-bold text-blue-950 flex items-center gap-2">
                                <svg class="w-5 h-5 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                                </svg>
                                Dedicated Backup & System Restore Module
                            </h3>
                            <p class="text-xs text-slate-600">Export full database dumps, filter data by Month/FY, restore safety snapshots, and manage backup storage files on the dedicated Backup page.</p>
                        </div>
                        <a href="{{ route('backup.index') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-5 rounded-xl text-xs transition shadow-sm flex items-center gap-2 shrink-0">
                            <span>Go to Backup Module</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 3: User Access & Role Permissions -->
    <div id="settingsTab-users" class="tab-content hidden space-y-6">

        <!-- INLINE FORM: Add / Edit System User (Collapsible Card placed ABOVE Table) -->
        <div id="createUserFormCard" class="hidden bg-white rounded-2xl shadow-md border-2 border-blue-500/30 p-6 transition-all duration-300 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 id="userFormCardTitleHeader" class="text-base font-bold text-slate-800 flex items-center gap-2">
                    <svg id="userFormHeaderSvg" class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span id="userFormCardTitle">Add New System User Account</span>
                </h3>
                <button type="button" onclick="toggleCreateUserForm()" class="text-xs font-bold text-slate-400 hover:text-slate-600 transition cursor-pointer">&times; Close</button>
            </div>

            <form id="userCardForm" action="{{ route('settings.users.store') }}" method="POST" class="ajax-form space-y-4">
                @csrf
                <input type="hidden" name="_method" id="userFormMethodInput" value="POST">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Full Name</label>
                        <input type="text" id="cardUserNameInput" name="name" required placeholder="e.g. Ramesh Patel" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Login Email</label>
                        <input type="email" id="cardUserEmailInput" name="email" required placeholder="e.g. ramesh@example.com" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">
                            Password <span id="passwordRequiredHint" class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="password" id="cardUserPasswordInput" name="password" required minlength="6" placeholder="••••••••" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 pl-4 pr-10 text-sm font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                            <button type="button" onclick="togglePasswordVisibility('cardUserPasswordInput', 'cardUserPasswordIconEye', 'cardUserPasswordIconEyeOff')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600" title="Toggle password visibility">
                                <svg id="cardUserPasswordIconEye" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg id="cardUserPasswordIconEyeOff" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Assign System Role</label>
                        <select id="cardUserRoleSelect" name="role" required class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @foreach($roles as $key => $r)
                                @if($key !== 'super_admin')
                                    <option value="{{ $key }}">{{ $r['name'] }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 flex justify-end gap-2">
                    <button type="button" onclick="toggleCreateUserForm()" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition">Cancel</button>
                    <button type="submit" id="userFormSubmitBtn" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-5 rounded-xl text-xs shadow-sm transition">Create Account</button>
                </div>
            </form>
        </div>

        <!-- 1. All System User Accounts Table Card -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-base font-bold text-slate-800">All System Users & Team Accounts</h3>
                <button onclick="toggleCreateUserForm()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-3.5 rounded-xl text-xs transition shadow-xs flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add System User
                </button>
            </div>

            <div class="overflow-x-auto w-full">
                <table class="erp-datatable min-w-full divide-y divide-slate-200 text-xs">
                    <thead class="bg-slate-50 text-slate-700 font-bold uppercase text-[10px]">
                        <tr>
                            <th class="p-3 text-center w-10">#</th>
                            <th class="p-3 text-left">User</th>
                            <th class="p-3 text-left">Email</th>
                            <th class="p-3 text-left">Role</th>
                            <th class="p-3 text-center">Status</th>
                            <th class="p-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach($users as $u)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="p-3 text-center font-bold text-slate-500">{{ $loop->iteration }}</td>
                                <td class="p-3 font-bold text-slate-800 flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-full bg-blue-100 text-blue-700 font-black flex items-center justify-center text-xs uppercase shrink-0">
                                        {{ substr($u->name, 0, 1) }}
                                    </div>
                                    <span>{{ $u->name }}</span>
                                </td>
                                <td class="p-3 font-mono text-slate-600">{{ $u->email }}</td>
                                <td class="p-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200 capitalize">
                                        {{ str_replace('_', ' ', $u->role) }}
                                    </span>
                                </td>
                                <td class="p-3 text-center user-status-cell">
                                    @if($u->is_active && ($u->status === 'active' || $u->status === 'approved'))
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[9px] font-bold uppercase bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[9px] font-bold uppercase bg-rose-50 text-rose-700 border border-rose-200">
                                            Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="p-3 text-center space-x-1.5 whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-1.5">
                                        @if(in_array($u->role, ['super_admin']))
                                            <!-- Protected Lock Badge -->
                                            <span class="w-7 h-7 rounded-lg bg-emerald-500 text-white flex items-center justify-center shadow-2xs inline-flex" title="Protected Super Admin Owner Account">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                                </svg>
                                            </span>
                                        @else
                                            @php
                                                $userIsActive = ($u->is_active && $u->status === 'active');
                                            @endphp

                                            <!-- 1. Active / Inactive Toggle Eye Button -->
                                            <button type="button" 
                                                    data-active="{{ $userIsActive ? '1' : '0' }}" 
                                                    onclick="toggleUserStatusAjax('{{ $u->id }}', '{{ addslashes($u->name) }}', this)" 
                                                    title="{{ $userIsActive ? 'Deactivate User Account' : 'Activate User Account' }}" 
                                                    class="w-7 h-7 p-1 inline-flex items-center justify-center rounded-lg {{ $userIsActive ? 'bg-emerald-500 hover:bg-emerald-600' : 'bg-rose-500 hover:bg-rose-600' }} text-white shadow-2xs transition duration-150 transform hover:scale-105">
                                                @if($userIsActive)
                                                    <!-- Active Eye Icon (Filled Solid) -->
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                                        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                                                    </svg>
                                                @else
                                                    <!-- Inactive Eye-Slash Icon (Filled Solid) -->
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z" clip-rule="evenodd" />
                                                        <path d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 1.002 0 1.97-.146 2.883-.404z" />
                                                    </svg>
                                                @endif
                                            </button>

                                            <!-- 2. Edit User Details Button -->
                                            <button type="button" onclick='openEditUserModal(@json($u))' title="Edit User Details" class="w-7 h-7 p-1 inline-flex items-center justify-center rounded-lg bg-amber-500 hover:bg-amber-600 text-white shadow-2xs transition duration-150 transform hover:scale-105">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </button>

                                            <!-- 3. Delete User Button -->
                                            <button type="button" 
                                                    onclick="deleteUserAjax('{{ $u->id }}', '{{ addslashes($u->name) }}', this)" 
                                                    class="w-7 h-7 p-1 inline-flex items-center justify-center rounded-lg bg-rose-500 hover:bg-rose-600 text-white shadow-2xs transition duration-150 transform hover:scale-105" 
                                                    title="Delete User Account">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 1. Role Actions & Pages Matrix Header & Table Card -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-6">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border-b border-slate-100 pb-4">
                <div class="space-y-1">
                    <h2 class="text-lg font-black text-slate-800 flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-100">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </span>
                        Role Actions & Pages Matrix
                    </h2>
                    <p class="text-slate-500 text-xs font-medium pl-10">Toggle view page access and action authorization (Insert, Update, Delete) per role in real-time.</p>
                </div>

                <div class="flex items-center gap-2">
                    <button onclick="openAddRoleModal()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-3.5 rounded-xl text-xs transition shadow-xs flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add Role
                    </button>
                </div>
            </div>

            <!-- Role Matrix Table with Inner Horizontal & Vertical Scroll -->
            <div class="overflow-x-auto max-w-full border border-slate-200/80 rounded-2xl relative shadow-2xs custom-horizontal-scrollbar">
                <table class="min-w-[1550px] w-full divide-y divide-slate-200 text-xs text-left whitespace-nowrap">
                    <thead class="bg-slate-50 text-slate-700 font-bold uppercase tracking-wider text-[9.5px] sticky top-0 z-20 shadow-2xs border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-3 text-left min-w-[160px] sticky left-0 bg-slate-50 z-30 border-r border-slate-200/60 shadow-xs">Role</th>
                            <th class="py-3 px-2 text-center min-w-[95px]">Overview</th>
                            <th class="py-3 px-2 text-center min-w-[95px]">Orders</th>
                            <th class="py-3 px-2 text-center min-w-[95px]">Invoices</th>
                            <th class="py-3 px-2 text-center min-w-[95px]">Purchases</th>
                            <th class="py-3 px-2 text-center min-w-[95px]">Expenses</th>
                            <th class="py-3 px-2 text-center min-w-[100px]">Raw Mat.</th>
                            <th class="py-3 px-2 text-center min-w-[95px]">Products</th>
                            <th class="py-3 px-2 text-center min-w-[85px]">BOM</th>
                            <th class="py-3 px-2 text-center min-w-[105px]">Production</th>
                            <th class="py-3 px-2 text-center min-w-[95px]">Clients</th>
                            <th class="py-3 px-2 text-center min-w-[95px]">Employees</th>
                            <th class="py-3 px-2 text-center min-w-[95px]">Reports</th>
                            <th class="py-3 px-2 text-center min-w-[90px] bg-emerald-50/70 text-emerald-900">Insert</th>
                            <th class="py-3 px-2 text-center min-w-[90px] bg-amber-50/70 text-amber-900">Update</th>
                            <th class="py-3 px-2 text-center min-w-[90px] bg-rose-50/70 text-rose-900">Delete</th>
                            <th class="py-3 px-2 text-center min-w-[85px]">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach($roles as $roleKey => $r)
                            @php
                                $rolePerms = \App\Services\RolePermissionService::getDefaultPermissionsForRole($roleKey);
                                $isSuperAdmin = ($roleKey === 'super_admin');
                            @endphp
                            <tr class="hover:bg-slate-50/60 transition group">
                                <td class="p-3 font-bold text-slate-800 sticky left-0 bg-white group-hover:bg-slate-50 transition z-10 border-r border-slate-100 shadow-2xs">
                                    <span class="block text-sm font-bold text-slate-900">{{ $r['name'] }}</span>
                                </td>

                                <!-- 1. Overview Dashboard Toggle -->
                                <td class="p-2.5 text-center">
                                    <label class="inline-flex items-center cursor-pointer select-none">
                                        <input type="checkbox" {{ $isSuperAdmin || in_array('page_overview', $rolePerms) ? 'checked' : '' }} {{ $isSuperAdmin ? 'disabled' : '' }} onchange="toggleRolePerm('{{ $roleKey }}', 'page_overview', this.checked)" class="matrix-toggle-input">
                                        <span class="matrix-toggle-slider"></span>
                                    </label>
                                </td>

                                <!-- 2. Sales Orders Toggle -->
                                <td class="p-2.5 text-center">
                                    <label class="inline-flex items-center cursor-pointer select-none">
                                        <input type="checkbox" {{ $isSuperAdmin || in_array('page_orders', $rolePerms) ? 'checked' : '' }} {{ $isSuperAdmin ? 'disabled' : '' }} onchange="toggleRolePerm('{{ $roleKey }}', 'page_orders', this.checked)" class="matrix-toggle-input">
                                        <span class="matrix-toggle-slider"></span>
                                    </label>
                                </td>

                                <!-- 3. Invoices Toggle -->
                                <td class="p-2.5 text-center">
                                    <label class="inline-flex items-center cursor-pointer select-none">
                                        <input type="checkbox" {{ $isSuperAdmin || in_array('page_invoices', $rolePerms) ? 'checked' : '' }} {{ $isSuperAdmin ? 'disabled' : '' }} onchange="toggleRolePerm('{{ $roleKey }}', 'page_invoices', this.checked)" class="matrix-toggle-input">
                                        <span class="matrix-toggle-slider"></span>
                                    </label>
                                </td>

                                <!-- 4. Purchases Toggle -->
                                <td class="p-2.5 text-center">
                                    <label class="inline-flex items-center cursor-pointer select-none">
                                        <input type="checkbox" {{ $isSuperAdmin || in_array('page_purchases', $rolePerms) ? 'checked' : '' }} {{ $isSuperAdmin ? 'disabled' : '' }} onchange="toggleRolePerm('{{ $roleKey }}', 'page_purchases', this.checked)" class="matrix-toggle-input">
                                        <span class="matrix-toggle-slider"></span>
                                    </label>
                                </td>

                                <!-- 5. Expenses Toggle -->
                                <td class="p-2.5 text-center">
                                    <label class="inline-flex items-center cursor-pointer select-none">
                                        <input type="checkbox" {{ $isSuperAdmin || in_array('page_expenses', $rolePerms) ? 'checked' : '' }} {{ $isSuperAdmin ? 'disabled' : '' }} onchange="toggleRolePerm('{{ $roleKey }}', 'page_expenses', this.checked)" class="matrix-toggle-input">
                                        <span class="matrix-toggle-slider"></span>
                                    </label>
                                </td>

                                <!-- 6. Raw Materials Toggle -->
                                <td class="p-2.5 text-center">
                                    <label class="inline-flex items-center cursor-pointer select-none">
                                        <input type="checkbox" {{ $isSuperAdmin || in_array('page_rawmaterial', $rolePerms) ? 'checked' : '' }} {{ $isSuperAdmin ? 'disabled' : '' }} onchange="toggleRolePerm('{{ $roleKey }}', 'page_rawmaterial', this.checked)" class="matrix-toggle-input">
                                        <span class="matrix-toggle-slider"></span>
                                    </label>
                                </td>

                                <!-- 7. Products Toggle -->
                                <td class="p-2.5 text-center">
                                    <label class="inline-flex items-center cursor-pointer select-none">
                                        <input type="checkbox" {{ $isSuperAdmin || in_array('page_product', $rolePerms) ? 'checked' : '' }} {{ $isSuperAdmin ? 'disabled' : '' }} onchange="toggleRolePerm('{{ $roleKey }}', 'page_product', this.checked)" class="matrix-toggle-input">
                                        <span class="matrix-toggle-slider"></span>
                                    </label>
                                </td>

                                <!-- 8. BOM Toggle -->
                                <td class="p-2.5 text-center">
                                    <label class="inline-flex items-center cursor-pointer select-none">
                                        <input type="checkbox" {{ $isSuperAdmin || in_array('page_bom', $rolePerms) ? 'checked' : '' }} {{ $isSuperAdmin ? 'disabled' : '' }} onchange="toggleRolePerm('{{ $roleKey }}', 'page_bom', this.checked)" class="matrix-toggle-input">
                                        <span class="matrix-toggle-slider"></span>
                                    </label>
                                </td>

                                <!-- 9. Production Logs Toggle -->
                                <td class="p-2.5 text-center">
                                    <label class="inline-flex items-center cursor-pointer select-none">
                                        <input type="checkbox" {{ $isSuperAdmin || in_array('page_production', $rolePerms) ? 'checked' : '' }} {{ $isSuperAdmin ? 'disabled' : '' }} onchange="toggleRolePerm('{{ $roleKey }}', 'page_production', this.checked)" class="matrix-toggle-input">
                                        <span class="matrix-toggle-slider"></span>
                                    </label>
                                </td>

                                <!-- 10. Clients & Plants Toggle -->
                                <td class="p-2.5 text-center">
                                    <label class="inline-flex items-center cursor-pointer select-none">
                                        <input type="checkbox" {{ $isSuperAdmin || in_array('page_clients', $rolePerms) ? 'checked' : '' }} {{ $isSuperAdmin ? 'disabled' : '' }} onchange="toggleRolePerm('{{ $roleKey }}', 'page_clients', this.checked)" class="matrix-toggle-input">
                                        <span class="matrix-toggle-slider"></span>
                                    </label>
                                </td>

                                <!-- 11. Employees Toggle -->
                                <td class="p-2.5 text-center">
                                    <label class="inline-flex items-center cursor-pointer select-none">
                                        <input type="checkbox" {{ $isSuperAdmin || in_array('page_employees', $rolePerms) ? 'checked' : '' }} {{ $isSuperAdmin ? 'disabled' : '' }} onchange="toggleRolePerm('{{ $roleKey }}', 'page_employees', this.checked)" class="matrix-toggle-input">
                                        <span class="matrix-toggle-slider"></span>
                                    </label>
                                </td>

                                <!-- 12. Reports Toggle -->
                                <td class="p-2.5 text-center">
                                    <label class="inline-flex items-center cursor-pointer select-none">
                                        <input type="checkbox" {{ $isSuperAdmin || in_array('page_reports', $rolePerms) ? 'checked' : '' }} {{ $isSuperAdmin ? 'disabled' : '' }} onchange="toggleRolePerm('{{ $roleKey }}', 'page_reports', this.checked)" class="matrix-toggle-input">
                                        <span class="matrix-toggle-slider"></span>
                                    </label>
                                </td>

                                <!-- Action Insert Toggle (Green) -->
                                <td class="p-2.5 text-center bg-emerald-50/30">
                                    <label class="inline-flex items-center cursor-pointer select-none">
                                        <input type="checkbox" {{ $isSuperAdmin || in_array('action_insert', $rolePerms) ? 'checked' : '' }} {{ $isSuperAdmin ? 'disabled' : '' }} onchange="toggleRolePerm('{{ $roleKey }}', 'action_insert', this.checked)" class="matrix-toggle-input matrix-toggle-input-green">
                                        <span class="matrix-toggle-slider"></span>
                                    </label>
                                </td>

                                <!-- Action Update Toggle (Yellow) -->
                                <td class="p-2.5 text-center bg-amber-50/30">
                                    <label class="inline-flex items-center cursor-pointer select-none">
                                        <input type="checkbox" {{ $isSuperAdmin || in_array('action_update', $rolePerms) ? 'checked' : '' }} {{ $isSuperAdmin ? 'disabled' : '' }} onchange="toggleRolePerm('{{ $roleKey }}', 'action_update', this.checked)" class="matrix-toggle-input matrix-toggle-input-amber">
                                        <span class="matrix-toggle-slider"></span>
                                    </label>
                                </td>

                                <!-- Action Delete Toggle (Red) -->
                                <td class="p-2.5 text-center bg-rose-50/30">
                                    <label class="inline-flex items-center cursor-pointer select-none">
                                        <input type="checkbox" {{ $isSuperAdmin || in_array('action_delete', $rolePerms) ? 'checked' : '' }} {{ $isSuperAdmin ? 'disabled' : '' }} onchange="toggleRolePerm('{{ $roleKey }}', 'action_delete', this.checked)" class="matrix-toggle-input matrix-toggle-input-rose">
                                        <span class="matrix-toggle-slider"></span>
                                    </label>
                                </td>

                                <!-- Actions Column -->
                                <td class="p-3 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-1.5">
                                        @if($isSuperAdmin)
                                            <span class="w-7 h-7 rounded-lg bg-emerald-500 text-white flex items-center justify-center shadow-xs" title="Protected Owner Role">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                                </svg>
                                            </span>
                                        @else
                                            @php
                                                $roleRecord = $customRolesList->firstWhere('slug', $roleKey);
                                                $roleIsActive = $roleRecord ? (bool)$roleRecord->is_active : true;
                                            @endphp
                                            <button type="button" 
                                                    data-active="{{ $roleIsActive ? '1' : '0' }}" 
                                                    onclick="toggleRoleStatusAjax('{{ $roleKey }}', '{{ addslashes($r['name']) }}', this)" 
                                                    title="{{ $roleIsActive ? 'Deactivate Role' : 'Activate Role' }}" 
                                                    class="w-7 h-7 p-1 inline-flex items-center justify-center rounded-lg {{ $roleIsActive ? 'bg-emerald-500 hover:bg-emerald-600' : 'bg-rose-500 hover:bg-rose-600' }} text-white shadow-2xs transition duration-150 transform hover:scale-105">
                                                @if($roleIsActive)
                                                    <!-- Active Eye Icon (Filled Solid) -->
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                                        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                                                    </svg>
                                                @else
                                                    <!-- Inactive Eye-Slash Icon (Filled Solid - Exact Match) -->
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z" clip-rule="evenodd" />
                                                        <path d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 1.002 0 1.97-.146 2.883-.404z" />
                                                    </svg>
                                                @endif
                                            </button>

                                            @if($roleKey !== 'super_admin')
                                                <button type="button" 
                                                        onclick="deleteRoleAjax('{{ $roleRecord ? $roleRecord->id : $roleKey }}', '{{ addslashes($r['name']) }}', this)" 
                                                        class="w-7 h-7 rounded-lg bg-rose-500 hover:bg-rose-600 text-white inline-flex items-center justify-center shadow-2xs transition duration-150 transform hover:scale-105" 
                                                        title="Delete Role">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB 4: Active ERP Modules -->
    <div id="settingsTab-modules" class="tab-content hidden space-y-6">
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-6">
            <style>
                .erp-toggle-input {
                    display: none !important;
                }
                .erp-toggle-slider {
                    position: relative;
                    display: inline-block;
                    width: 46px;
                    height: 24px;
                    background-color: #cbd5e1;
                    border-radius: 9999px;
                    transition: background-color 0.2s ease-in-out;
                    cursor: pointer;
                    box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.1);
                }
                .erp-toggle-slider::after {
                    content: "" !important;
                    position: absolute;
                    top: 2px;
                    left: 2px;
                    width: 20px;
                    height: 20px;
                    background-color: #ffffff !important;
                    border-radius: 50% !important;
                    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.25) !important;
                    transition: transform 0.2s ease-in-out;
                }
                .erp-toggle-input:checked + .erp-toggle-slider {
                    background-color: #2563eb !important;
                }
                .erp-toggle-input:checked + .erp-toggle-slider::after {
                    transform: translateX(22px) !important;
                }

                /* Matrix Table Custom Toggle Switches */
                .matrix-toggle-input {
                    display: none !important;
                }
                .matrix-toggle-slider {
                    position: relative;
                    display: inline-block;
                    width: 38px;
                    height: 20px;
                    background-color: #cbd5e1;
                    border-radius: 9999px;
                    transition: background-color 0.2s ease-in-out;
                    cursor: pointer;
                    box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.1);
                }
                .matrix-toggle-slider::after {
                    content: "" !important;
                    position: absolute;
                    top: 2px;
                    left: 2px;
                    width: 16px;
                    height: 16px;
                    background-color: #ffffff !important;
                    border-radius: 50% !important;
                    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.25) !important;
                    transition: transform 0.2s ease-in-out;
                }
                .matrix-toggle-input:checked + .matrix-toggle-slider {
                    background-color: #2563eb !important;
                }
                .matrix-toggle-input:checked + .matrix-toggle-slider::after {
                    transform: translateX(18px) !important;
                }
                .matrix-toggle-input-green:checked + .matrix-toggle-slider {
                    background-color: #10b981 !important;
                }
                .matrix-toggle-input-amber:checked + .matrix-toggle-slider {
                    background-color: #f59e0b !important;
                }
                .matrix-toggle-input-rose:checked + .matrix-toggle-slider {
                    background-color: #f43f5e !important;
                }
            </style>

            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div>
                    <h2 class="text-lg font-bold text-slate-800">Module Visibility Controls</h2>
                    <p class="text-slate-500 text-xs">Turn off unused modules to simplify the interface. Unused items disappear from the sidebar automatically. No data is lost.</p>
                </div>
            </div>

            <form action="{{ route('settings.modules') }}" method="POST" id="modulesVisibilityForm" onsubmit="saveModuleVisibilityAjax(event)" class="space-y-6">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">

                    <!-- Invoices -->
                    <div class="p-4 rounded-xl border border-slate-200/80 bg-slate-50/50 flex items-center justify-between">
                        <div>
                            <span class="block text-sm font-bold text-slate-800">Invoices & Billing</span>
                            <span class="text-[11px] text-slate-500 font-medium">Generate GST Tax Invoices</span>
                        </div>
                        <label class="inline-flex items-center cursor-pointer select-none">
                            <input type="checkbox" name="module_invoices" value="true" {{ $modules['module_invoices'] ? 'checked' : '' }} class="erp-toggle-input" onchange="saveModuleToggleAjax(this)">
                            <span class="erp-toggle-slider"></span>
                        </label>
                    </div>

                    <!-- Sales Orders -->
                    <div class="p-4 rounded-xl border border-slate-200/80 bg-slate-50/50 flex items-center justify-between">
                        <div>
                            <span class="block text-sm font-bold text-slate-800">Sales Orders</span>
                            <span class="text-[11px] text-slate-500 font-medium">Manage B2B POs & Challans</span>
                        </div>
                        <label class="inline-flex items-center cursor-pointer select-none">
                            <input type="checkbox" name="module_orders" value="true" {{ $modules['module_orders'] ? 'checked' : '' }} class="erp-toggle-input" onchange="saveModuleToggleAjax(this)">
                            <span class="erp-toggle-slider"></span>
                        </label>
                    </div>

                    <!-- Purchase Ledger -->
                    <div class="p-4 rounded-xl border border-slate-200/80 bg-slate-50/50 flex items-center justify-between">
                        <div>
                            <span class="block text-sm font-bold text-slate-800">Purchase Ledger</span>
                            <span class="text-[11px] text-slate-500 font-medium">Raw Material Purchases & Suppliers</span>
                        </div>
                        <label class="inline-flex items-center cursor-pointer select-none">
                            <input type="checkbox" name="module_purchases" value="true" {{ $modules['module_purchases'] ? 'checked' : '' }} class="erp-toggle-input" onchange="saveModuleToggleAjax(this)">
                            <span class="erp-toggle-slider"></span>
                        </label>
                    </div>

                    <!-- Clients & Plants -->
                    <div class="p-4 rounded-xl border border-slate-200/80 bg-slate-50/50 flex items-center justify-between">
                        <div>
                            <span class="block text-sm font-bold text-slate-800">Clients & Plants</span>
                            <span class="text-[11px] text-slate-500 font-medium">Client Directory & Multi-plant Shipping</span>
                        </div>
                        <label class="inline-flex items-center cursor-pointer select-none">
                            <input type="checkbox" name="module_clients" value="true" {{ $modules['module_clients'] ? 'checked' : '' }} class="erp-toggle-input" onchange="saveModuleToggleAjax(this)">
                            <span class="erp-toggle-slider"></span>
                        </label>
                    </div>

                    <!-- Expenses Ledger -->
                    <div class="p-4 rounded-xl border border-slate-200/80 bg-slate-50/50 flex items-center justify-between">
                        <div>
                            <span class="block text-sm font-bold text-slate-800">Expense Ledger</span>
                            <span class="text-[11px] text-slate-500 font-medium">Operational Factory Expenses</span>
                        </div>
                        <label class="inline-flex items-center cursor-pointer select-none">
                            <input type="checkbox" name="module_expenses" value="true" {{ $modules['module_expenses'] ? 'checked' : '' }} class="erp-toggle-input" onchange="saveModuleToggleAjax(this)">
                            <span class="erp-toggle-slider"></span>
                        </label>
                    </div>

                    <!-- Production Logs -->
                    <div class="p-4 rounded-xl border border-slate-200/80 bg-slate-50/50 flex items-center justify-between">
                        <div>
                            <span class="block text-sm font-bold text-slate-800">Production Logs</span>
                            <span class="text-[11px] text-slate-500 font-medium">Batch Manufacturing & Yield</span>
                        </div>
                        <label class="inline-flex items-center cursor-pointer select-none">
                            <input type="checkbox" name="module_production" value="true" {{ $modules['module_production'] ? 'checked' : '' }} class="erp-toggle-input" onchange="saveModuleToggleAjax(this)">
                            <span class="erp-toggle-slider"></span>
                        </label>
                    </div>

                    <!-- Bill of Materials (BOM) -->
                    <div class="p-4 rounded-xl border border-slate-200/80 bg-slate-50/50 flex items-center justify-between">
                        <div>
                            <span class="block text-sm font-bold text-slate-800">Bill of Materials (BOM)</span>
                            <span class="text-[11px] text-slate-500 font-medium">Product Material Recipes</span>
                        </div>
                        <label class="inline-flex items-center cursor-pointer select-none">
                            <input type="checkbox" name="module_bom" value="true" {{ $modules['module_bom'] ? 'checked' : '' }} class="erp-toggle-input" onchange="saveModuleToggleAjax(this)">
                            <span class="erp-toggle-slider"></span>
                        </label>
                    </div>

                    <!-- Raw Materials & Products -->
                    <div class="p-4 rounded-xl border border-slate-200/80 bg-slate-50/50 flex items-center justify-between">
                        <div>
                            <span class="block text-sm font-bold text-slate-800">Raw Materials Inventory</span>
                            <span class="text-[11px] text-slate-500 font-medium">Raw Stock Thresholds & Items</span>
                        </div>
                        <label class="inline-flex items-center cursor-pointer select-none">
                            <input type="checkbox" name="module_inventory" value="true" {{ $modules['module_inventory'] ? 'checked' : '' }} class="erp-toggle-input" onchange="saveModuleToggleAjax(this)">
                            <span class="erp-toggle-slider"></span>
                        </label>
                    </div>

                    <!-- Employee Payroll -->
                    <div class="p-4 rounded-xl border border-slate-200/80 bg-slate-50/50 flex items-center justify-between">
                        <div>
                            <span class="block text-sm font-bold text-slate-800">Employee Payroll</span>
                            <span class="text-[11px] text-slate-500 font-medium">Worker Wage Payouts</span>
                        </div>
                        <label class="inline-flex items-center cursor-pointer select-none">
                            <input type="checkbox" name="module_payroll" value="true" {{ $modules['module_payroll'] ? 'checked' : '' }} class="erp-toggle-input" onchange="saveModuleToggleAjax(this)">
                            <span class="erp-toggle-slider"></span>
                        </label>
                    </div>

                    <!-- Reports & Tax Returns -->
                    <div class="p-4 rounded-xl border border-slate-200/80 bg-slate-50/50 flex items-center justify-between">
                        <div>
                            <span class="block text-sm font-bold text-slate-800">Reports & Tax Returns</span>
                            <span class="text-[11px] text-slate-500 font-medium">GSTR-1, GSTR-3B & P&L Audits</span>
                        </div>
                        <label class="inline-flex items-center cursor-pointer select-none">
                            <input type="checkbox" name="module_reports" value="true" {{ ($modules['module_reports'] ?? true) ? 'checked' : '' }} class="erp-toggle-input" onchange="saveModuleToggleAjax(this)">
                            <span class="erp-toggle-slider"></span>
                        </label>
                    </div>

                    <!-- Backup & Restore Hub -->
                    <div class="p-4 rounded-xl border border-slate-200/80 bg-slate-50/50 flex items-center justify-between">
                        <div>
                            <span class="block text-sm font-bold text-slate-800">Backup & Restore Hub</span>
                            <span class="text-[11px] text-slate-500 font-medium">SQL Database Snapshots</span>
                        </div>
                        <label class="inline-flex items-center cursor-pointer select-none">
                            <input type="checkbox" name="module_backups" value="true" {{ ($modules['module_backups'] ?? true) ? 'checked' : '' }} class="erp-toggle-input" onchange="saveModuleToggleAjax(this)">
                            <span class="erp-toggle-slider"></span>
                        </label>
                    </div>

                    <!-- Activity Audit Logs -->
                    <div class="p-4 rounded-xl border border-slate-200/80 bg-slate-50/50 flex items-center justify-between">
                        <div>
                            <span class="block text-sm font-bold text-slate-800">Activity Audit Logs</span>
                            <span class="text-[11px] text-slate-500 font-medium">Real-Time Audit Trail (Super Admin)</span>
                        </div>
                        <label class="inline-flex items-center cursor-pointer select-none">
                            <input type="checkbox" name="module_activity_logs" value="true" {{ ($modules['module_activity_logs'] ?? true) ? 'checked' : '' }} class="erp-toggle-input" onchange="saveModuleToggleAjax(this)">
                            <span class="erp-toggle-slider"></span>
                        </label>
                    </div>

                    <!-- Automatic Stock Deductions -->
                    <div class="p-4 rounded-xl border border-blue-200/80 bg-blue-50/40 flex items-center justify-between col-span-1 md:col-span-3">
                        <div>
                            <span class="block text-sm font-bold text-blue-900 flex items-center gap-1.5">
                                📦 Automatic Inventory Stock Deductions
                            </span>
                            <span class="text-[11px] text-slate-600 font-medium">Auto-deduct item stock on Invoices. Turn OFF if client only does Billing and does not track Stock/BOM.</span>
                        </div>
                        <label class="inline-flex items-center cursor-pointer select-none">
                            <input type="checkbox" name="track_stock" value="true" {{ ($modules['track_stock'] ?? true) ? 'checked' : '' }} class="erp-toggle-input" onchange="saveModuleToggleAjax(this)">
                            <span class="erp-toggle-slider"></span>
                        </label>
                    </div>

                    <!-- Invoice Payment Status & Receivable Tracking -->
                    <div class="p-4 rounded-xl border border-emerald-200/80 bg-emerald-50/40 flex items-center justify-between col-span-1 md:col-span-3">
                        <div>
                            <span class="block text-sm font-bold text-emerald-900 flex items-center gap-1.5">
                                💳 Invoice Payment Status & Receivable Tracking
                            </span>
                            <span class="text-[11px] text-slate-600 font-medium">Track Unpaid / Partial / Paid statuses and Mark Paid actions. Turn OFF to auto-mark all new Invoices as PAID instantly upon creation.</span>
                        </div>
                        <label class="inline-flex items-center cursor-pointer select-none">
                            <input type="checkbox" name="track_payments" value="true" {{ ($modules['track_payments'] ?? true) ? 'checked' : '' }} class="erp-toggle-input" onchange="saveModuleToggleAjax(this)">
                            <span class="erp-toggle-slider"></span>
                        </label>
                    </div>

                </div>
            </form>
        </div>
    </div>
</div>



<!-- Modal: Edit System User & Permissions -->
<div id="editUserModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl max-w-xl w-full p-6 border border-slate-200 shadow-xl space-y-5 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-lg font-bold text-slate-800">Edit User & Permission Rules</h3>
            <button onclick="closeEditUserModal()" class="text-slate-400 hover:text-slate-600 font-bold text-xl">&times;</button>
        </div>

        <form id="editUserForm" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Full Name</label>
                <input type="text" id="editUserName" name="name" required class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-medium text-slate-800">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Login Email</label>
                <input type="email" id="editUserEmail" name="email" required class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-medium text-slate-800">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">New Password (leave empty to keep current)</label>
                <div class="relative">
                    <input type="password" id="editUserPasswordInput" name="password" minlength="6" placeholder="••••••••" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 pl-4 pr-10 text-sm font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                    <button type="button" onclick="togglePasswordVisibility('editUserPasswordInput', 'editUserPasswordIconEye', 'editUserPasswordIconEyeOff')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600" title="Toggle password visibility">
                        <svg id="editUserPasswordIconEye" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg id="editUserPasswordIconEyeOff" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">System Role</label>
                    <select id="editUserRole" name="role" required class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-semibold text-slate-800">
                        @foreach($roles as $key => $r)
                            @if($key !== 'super_admin')
                                <option value="{{ $key }}">{{ $r['name'] }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Account Status</label>
                    <select id="editUserStatus" name="status" required class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-semibold text-slate-800">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive / Suspended</option>
                    </select>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end gap-2">
                <button type="button" onclick="closeEditUserModal()" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100">Cancel</button>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-5 rounded-xl text-xs shadow-sm">Save Changes</button>
            </div>
        </form>
    </div>
</div>



<!-- Modal: Create Dynamic Role -->
<div id="addRoleModal" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl p-6 max-w-md w-full shadow-2xl border border-slate-100 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-base font-bold text-slate-800">Create Dynamic Role</h3>
            <button onclick="closeAddRoleModal()" class="text-slate-400 hover:text-slate-600">&times;</button>
        </div>

        <form action="{{ route('settings.roles.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Role Title / Name</label>
                <input type="text" name="name" placeholder="e.g. Inventory Auditor" required class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-semibold text-slate-800">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Role Description</label>
                <textarea name="description" rows="3" placeholder="Brief summary of what users in this role can manage..." class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm font-medium text-slate-800"></textarea>
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end gap-2">
                <button type="button" onclick="closeAddRoleModal()" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100">Cancel</button>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-5 rounded-xl text-xs shadow-sm">Save Role</button>
            </div>
        </form>
    </div>
</div>

<script>
function togglePasswordVisibility(inputId, eyeIconId, eyeOffIconId) {
    const input = document.getElementById(inputId);
    const eyeIcon = document.getElementById(eyeIconId);
    const eyeOffIcon = document.getElementById(eyeOffIconId);

    if (!input) return;

    if (input.type === 'password') {
        input.type = 'text';
        if (eyeIcon) eyeIcon.classList.add('hidden');
        if (eyeOffIcon) eyeOffIcon.classList.remove('hidden');
    } else {
        input.type = 'password';
        if (eyeIcon) eyeIcon.classList.remove('hidden');
        if (eyeOffIcon) eyeOffIcon.classList.add('hidden');
    }
}

function applyEmailPreset(preset) {
    const hostInput = document.getElementById('smtpHostInput');
    const portInput = document.getElementById('smtpPortInput');
    const encInput = document.getElementById('smtpEncryptionInput');
    const advSection = document.getElementById('advancedSmtpSection');

    if (preset === 'gmail') {
        if (hostInput) hostInput.value = 'smtp.gmail.com';
        if (portInput) portInput.value = '587';
        if (encInput) encInput.value = 'tls';
        if (advSection) advSection.classList.add('hidden');
    } else if (preset === 'outlook') {
        if (hostInput) hostInput.value = 'smtp.office365.com';
        if (portInput) portInput.value = '587';
        if (encInput) encInput.value = 'tls';
        if (advSection) advSection.classList.add('hidden');
    } else if (preset === 'custom') {
        if (advSection) advSection.classList.remove('hidden');
    }
}

function toggleOtherSettingsDropdown(e) {
    if (e) e.stopPropagation();
    const menu = document.getElementById('otherSettingsDropdownMenu');
    if (menu) {
        menu.classList.toggle('hidden');
    }
}

function selectOtherSettingsSub(subTabName) {
    const menu = document.getElementById('otherSettingsDropdownMenu');
    if (menu) {
        menu.classList.add('hidden');
    }

    // Highlight selected sub option inside dropdown
    document.querySelectorAll('.other-opt-btn').forEach(btn => {
        btn.classList.remove('bg-blue-50', 'text-blue-700');
    });
    const selectedOptBtn = document.getElementById('otherOpt-' + subTabName);
    if (selectedOptBtn) {
        selectedOptBtn.classList.add('bg-blue-50', 'text-blue-700');
    }

    // Update label on main tab button
    const labels = {
        'serials': 'Other Settings: Auto Serial',
        'financial': 'Other Settings: Tax & Financial',
        'email': 'Other Settings: Email (SMTP)',
        'security': 'Other Settings: Security & Backups'
    };
    const labelEl = document.getElementById('otherSettingsTabLabel');
    if (labelEl && labels[subTabName]) {
        labelEl.innerText = labels[subTabName];
    }

    switchSettingsTab('other');
    switchSubTab(subTabName);
}

document.addEventListener('click', function(e) {
    const wrapper = document.getElementById('otherSettingsDropdownWrapper');
    const menu = document.getElementById('otherSettingsDropdownMenu');
    if (menu && wrapper && !wrapper.contains(e.target)) {
        menu.classList.add('hidden');
    }
});

function switchSettingsTab(tabName) {
    const subTabsList = ['serials', 'financial', 'email', 'security'];
    if (subTabsList.includes(tabName)) {
        selectOtherSettingsSub(tabName);
        return;
    }

    document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('.tab-btn').forEach(el => {
        el.classList.remove('active-tab-btn', 'bg-blue-50', 'text-blue-700');
        el.classList.add('text-slate-600');
    });

    const targetTab = document.getElementById('settingsTab-' + tabName);
    if (targetTab) {
        targetTab.classList.remove('hidden');
    }
    const activeBtn = document.getElementById('tabBtn-' + tabName);
    if (activeBtn) {
        activeBtn.classList.add('active-tab-btn', 'bg-blue-50', 'text-blue-700');
    }

    if (tabName !== 'other') {
        const labelEl = document.getElementById('otherSettingsTabLabel');
        if (labelEl) labelEl.innerText = 'Other Settings';
    } else {
        const savedSub = localStorage.getItem('pww_active_sub_tab') || 'serials';
        switchSubTab(savedSub);
    }

    try {
        localStorage.setItem('pww_active_settings_tab', tabName);
        if (history.replaceState) {
            history.replaceState(null, null, window.location.pathname);
        }
    } catch (e) {}

    if (typeof window.initErpDataTables === 'function') {
        window.initErpDataTables();
    }
}

function switchSubTab(subTabName) {
    document.querySelectorAll('.sub-tab-content').forEach(el => el.classList.add('hidden'));

    const targetSub = document.getElementById('subTab-' + subTabName);
    if (targetSub) {
        targetSub.classList.remove('hidden');
    }

    try {
        localStorage.setItem('pww_active_sub_tab', subTabName);
        if (history.replaceState) {
            history.replaceState(null, null, window.location.pathname);
        }
    } catch (e) {}
}

async function toggleRolePerm(roleSlug, permKey, isChecked) {
    const toastType = isChecked ? 'success' : 'danger';
    try {
        const response = await fetch('{{ route("settings.roles.toggle-permission") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: JSON.stringify({
                role_slug: roleSlug,
                permission_key: permKey,
                enabled: isChecked ? 1 : 0
            })
        });

        const res = await response.json();
        if (response.ok && res.success) {
            if (window.showToast) {
                window.showToast(toastType, res.message || 'Role permissions updated.');
            }
        } else {
            if (window.showToast) window.showToast('danger', res.message || 'Failed to update permission.');
        }
    } catch (err) {
        if (window.showToast) window.showToast('danger', 'Network error updating role permission.');
    }
}

function filterUserList(status) {
    document.querySelectorAll('.user-filter-btn').forEach(btn => {
        btn.classList.remove('bg-blue-50', 'text-blue-700', 'border-blue-200');
        btn.classList.add('bg-slate-100', 'text-slate-600');
    });

    const activeBtn = document.getElementById('userFilter-' + status);
    if (activeBtn) {
        activeBtn.classList.remove('bg-slate-100', 'text-slate-600');
        activeBtn.classList.add('bg-blue-50', 'text-blue-700', 'border-blue-200');
    }

    document.querySelectorAll('#usersMainTable .user-row').forEach(row => {
        const rowStatus = row.getAttribute('data-status');
        if (status === 'all' || rowStatus === status) {
            row.classList.remove('hidden');
        } else {
            row.classList.add('hidden');
        }
    });
}



function openAddRoleModal() {
    document.getElementById('addRoleModal').classList.remove('hidden');
}

function closeAddRoleModal() {
    document.getElementById('addRoleModal').classList.add('hidden');
}

async function toggleRoleStatusAjax(roleSlug, roleName, buttonEl) {
    const isCurrentlyActive = buttonEl.getAttribute('data-active') === '1';
    const actionTitle = isCurrentlyActive ? 'Deactivate Role?' : 'Activate Role?';
    const actionPrompt = isCurrentlyActive 
        ? 'Are you sure you want to deactivate this role?' 
        : 'Are you sure you want to activate this role?';
    const confirmBtnText = isCurrentlyActive ? 'Yes, deactivate it!' : 'Yes, activate it!';

    const result = await Swal.fire({
        title: actionTitle,
        text: actionPrompt,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#2563EB',
        cancelButtonColor: '#EF4444',
        confirmButtonText: confirmBtnText,
        cancelButtonText: 'Cancel',
        customClass: {
            popup: 'rounded-2xl shadow-xl',
            confirmButton: 'px-5 py-2.5 rounded-xl font-bold text-sm shadow-sm',
            cancelButton: 'px-5 py-2.5 rounded-xl font-bold text-sm shadow-sm'
        }
    });

    if (result.isConfirmed) {
        try {
            const response = await fetch(`/settings/roles/${roleSlug}/toggle-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                }
            });

            const res = await response.json();
            if (response.ok && res.success) {
                const newActive = !isCurrentlyActive;
                buttonEl.setAttribute('data-active', newActive ? '1' : '0');

                if (newActive) {
                    buttonEl.className = "w-7 h-7 p-1 inline-flex items-center justify-center rounded-lg bg-emerald-500 hover:bg-emerald-600 text-white shadow-2xs transition duration-150 transform hover:scale-105";
                    buttonEl.title = "Deactivate Role";
                    buttonEl.innerHTML = `<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M10 12a2 2 0 100-4 2 2 0 000 4z" /><path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" /></svg>`;
                } else {
                    buttonEl.className = "w-7 h-7 p-1 inline-flex items-center justify-center rounded-lg bg-rose-500 hover:bg-rose-600 text-white shadow-2xs transition duration-150 transform hover:scale-105";
                    buttonEl.title = "Activate Role";
                    buttonEl.innerHTML = `<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z" clip-rule="evenodd" /><path d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 1.002 0 1.97-.146 2.883-.404z" /></svg>`;
                }

                if (window.showToast) {
                    const toastType = newActive ? 'success' : 'danger';
                    window.showToast(toastType, res.message || `Role status updated successfully.`);
                }
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Update Failed',
                    text: res.message || 'Could not update role status.'
                });
            }
        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'Server Error',
                text: 'An error occurred while updating status.'
            });
        }
    }
}

async function deleteRoleAjax(roleKey, roleName, buttonEl) {
    const result = await Swal.fire({
        title: 'Delete Role?',
        text: `Are you sure you want to delete role '${roleName}'? This action cannot be undone.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#EF4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, Delete Role',
        cancelButtonText: 'Cancel',
        customClass: {
            popup: 'rounded-2xl shadow-xl',
            confirmButton: 'px-5 py-2.5 rounded-xl font-bold text-sm shadow-sm',
            cancelButton: 'px-5 py-2.5 rounded-xl font-bold text-sm shadow-sm'
        }
    });

    if (result.isConfirmed) {
        try {
            const response = await fetch(`/settings/roles/${roleKey}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({
                    _method: 'DELETE'
                })
            });

            const res = await response.json();
            if (response.ok && res.success) {
                const tr = buttonEl.closest('tr');
                if (tr) {
                    tr.style.transition = 'all 0.3s ease';
                    tr.style.opacity = '0';
                    setTimeout(() => tr.remove(), 300);
                }
                if (window.showToast) {
                    window.showToast('danger', res.message || `Role '${roleName}' deleted successfully.`);
                }
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Delete Failed',
                    text: res.message || 'Failed to delete role.'
                });
            }
        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'Server Error',
                text: 'An error occurred while deleting the role.'
            });
        }
    }
}

async function toggleUserStatusAjax(userId, userName, buttonEl) {
    const isCurrentlyActive = buttonEl.getAttribute('data-active') === '1';
    const actionTitle = isCurrentlyActive ? 'Deactivate User Account?' : 'Activate User Account?';
    const actionPrompt = isCurrentlyActive 
        ? `Are you sure you want to deactivate user '${userName}'?` 
        : `Are you sure you want to activate user '${userName}'?`;
    const confirmBtnText = isCurrentlyActive ? 'Yes, deactivate it!' : 'Yes, activate it!';

    const result = await Swal.fire({
        title: actionTitle,
        text: actionPrompt,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#2563EB',
        cancelButtonColor: '#EF4444',
        confirmButtonText: confirmBtnText,
        cancelButtonText: 'Cancel',
        customClass: {
            popup: 'rounded-2xl shadow-xl',
            confirmButton: 'px-5 py-2.5 rounded-xl font-bold text-sm shadow-sm',
            cancelButton: 'px-5 py-2.5 rounded-xl font-bold text-sm shadow-sm'
        }
    });

    if (result.isConfirmed) {
        try {
            const response = await fetch(`/settings/users/${userId}/toggle-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                }
            });

            const res = await response.json();
            if (response.ok && res.success) {
                const newActive = !isCurrentlyActive;
                buttonEl.setAttribute('data-active', newActive ? '1' : '0');

                if (newActive) {
                    buttonEl.className = "w-7 h-7 p-1 inline-flex items-center justify-center rounded-lg bg-emerald-500 hover:bg-emerald-600 text-white shadow-2xs transition duration-150 transform hover:scale-105";
                    buttonEl.title = "Deactivate User Account";
                    buttonEl.innerHTML = `<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M10 12a2 2 0 100-4 2 2 0 000 4z" /><path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" /></svg>`;
                } else {
                    buttonEl.className = "w-7 h-7 p-1 inline-flex items-center justify-center rounded-lg bg-rose-500 hover:bg-rose-600 text-white shadow-2xs transition duration-150 transform hover:scale-105";
                    buttonEl.title = "Activate User Account";
                    buttonEl.innerHTML = `<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z" clip-rule="evenodd" /><path d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 1.002 0 1.97-.146 2.883-.404z" /></svg>`;
                }

                const tr = buttonEl.closest('tr');
                if (tr) {
                    const statusTd = tr.querySelector('.user-status-cell');
                    if (statusTd) {
                        if (newActive) {
                            statusTd.innerHTML = `<span class="inline-flex items-center px-2 py-0.5 rounded-md text-[9px] font-bold uppercase bg-emerald-50 text-emerald-700 border border-emerald-200">Active</span>`;
                        } else {
                            statusTd.innerHTML = `<span class="inline-flex items-center px-2 py-0.5 rounded-md text-[9px] font-bold uppercase bg-rose-50 text-rose-700 border border-rose-200">Inactive</span>`;
                        }
                    }
                }

                if (window.showToast) {
                    const toastType = newActive ? 'success' : 'danger';
                    window.showToast(toastType, res.message || `User status updated successfully.`);
                }
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Update Failed',
                    text: res.message || 'Could not update user status.'
                });
            }
        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'Server Error',
                text: 'An error occurred while updating user status.'
            });
        }
    }
}

async function deleteUserAjax(userId, userName, buttonEl) {
    const result = await Swal.fire({
        title: 'Delete User Account?',
        text: `Are you sure you want to delete user '${userName}'? This action cannot be undone.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#EF4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, Delete User',
        cancelButtonText: 'Cancel',
        customClass: {
            popup: 'rounded-2xl shadow-xl',
            confirmButton: 'px-5 py-2.5 rounded-xl font-bold text-sm shadow-sm',
            cancelButton: 'px-5 py-2.5 rounded-xl font-bold text-sm shadow-sm'
        }
    });

    if (result.isConfirmed) {
        try {
            const response = await fetch(`/settings/users/${userId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({
                    _method: 'DELETE'
                })
            });

            const res = await response.json();
            if (response.ok && res.success) {
                const tr = buttonEl.closest('tr');
                if (tr) {
                    tr.style.transition = 'all 0.3s ease';
                    tr.style.opacity = '0';
                    setTimeout(() => tr.remove(), 300);
                }
                if (window.showToast) {
                    window.showToast('danger', res.message || `User '${userName}' deleted successfully.`);
                }
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Delete Failed',
                    text: res.message || 'Failed to delete user.'
                });
            }
        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'Server Error',
                text: 'An error occurred while deleting the user.'
            });
        }
    }
}

function resetUserFormCard() {
    const card = document.getElementById('createUserFormCard');
    const headerTitle = document.getElementById('userFormCardTitleHeader');
    const headerSvg = document.getElementById('userFormHeaderSvg');
    const title = document.getElementById('userFormCardTitle');
    const form = document.getElementById('userCardForm');
    const methodInput = document.getElementById('userFormMethodInput');
    const nameInput = document.getElementById('cardUserNameInput');
    const emailInput = document.getElementById('cardUserEmailInput');
    const passInput = document.getElementById('cardUserPasswordInput');
    const passHint = document.getElementById('passwordRequiredHint');
    const roleSelect = document.getElementById('cardUserRoleSelect');
    const submitBtn = document.getElementById('userFormSubmitBtn');

    if (form) {
        form.action = "{{ route('settings.users.store') }}";
        form.reset();
    }
    if (window.resetFormAndErrors) {
        window.resetFormAndErrors('#userCardForm');
    }
    if (methodInput) methodInput.value = 'POST';
    if (card) {
        card.className = "hidden bg-white rounded-2xl shadow-md border-2 border-blue-500/30 p-6 transition-all duration-300 space-y-4";
    }
    if (headerTitle) headerTitle.className = "text-base font-bold text-slate-800 flex items-center gap-2";
    if (headerSvg) {
        headerSvg.className = "w-5 h-5 text-blue-600";
        headerSvg.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />`;
    }
    if (title) title.innerText = 'Add New System User Account';
    if (nameInput) nameInput.value = '';
    if (emailInput) emailInput.value = '';
    if (passInput) {
        passInput.value = '';
        passInput.setAttribute('required', 'required');
        passInput.placeholder = '••••••••';
    }
    if (passHint) passHint.classList.remove('hidden');
    if (submitBtn) {
        submitBtn.className = "bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-5 rounded-xl text-xs shadow-sm transition cursor-pointer";
        submitBtn.innerText = 'Create Account';
    }
}

function toggleCreateUserForm() {
    const card = document.getElementById('createUserFormCard');
    if (card) {
        if (!card.classList.contains('hidden')) {
            card.classList.add('hidden');
            resetUserFormCard();
        } else {
            resetUserFormCard();
            card.classList.remove('hidden');
            card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    }
}

function openAddUserModal() {
    toggleCreateUserForm();
}

function closeAddUserModal() {
    const card = document.getElementById('createUserFormCard');
    if (card) {
        card.classList.add('hidden');
        resetUserFormCard();
    }
}

function openEditUserCard(user) {
    const card = document.getElementById('createUserFormCard');
    const headerTitle = document.getElementById('userFormCardTitleHeader');
    const headerSvg = document.getElementById('userFormHeaderSvg');
    const title = document.getElementById('userFormCardTitle');
    const form = document.getElementById('userCardForm');
    const methodInput = document.getElementById('userFormMethodInput');
    const nameInput = document.getElementById('cardUserNameInput');
    const emailInput = document.getElementById('cardUserEmailInput');
    const passInput = document.getElementById('cardUserPasswordInput');
    const passHint = document.getElementById('passwordRequiredHint');
    const roleSelect = document.getElementById('cardUserRoleSelect');
    const submitBtn = document.getElementById('userFormSubmitBtn');

    if (!card || !form) return;

    form.action = `/settings/users/${user.id}`;
    if (methodInput) methodInput.value = 'PUT';

    // Apply Amber tint theme matching Expenses page screenshot
    card.className = "bg-amber-50/40 rounded-2xl shadow-md border-2 border-amber-300/80 p-6 transition-all duration-300 space-y-4";
    if (headerTitle) headerTitle.className = "text-base font-bold text-amber-900 flex items-center gap-2";
    if (headerSvg) {
        headerSvg.className = "w-5 h-5 text-amber-600";
        headerSvg.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />`;
    }
    if (title) title.innerText = `Edit User Details: ${user.name}`;
    if (nameInput) nameInput.value = user.name || '';
    if (emailInput) emailInput.value = user.email || '';
    if (passInput) {
        passInput.value = '';
        passInput.removeAttribute('required');
        passInput.placeholder = 'Leave blank to keep current password';
    }
    if (passHint) passHint.classList.add('hidden');
    if (roleSelect) roleSelect.value = user.role || 'staff';
    if (submitBtn) {
        submitBtn.className = "bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-5 rounded-xl text-xs shadow-sm transition cursor-pointer";
        submitBtn.innerText = 'Update User Details';
    }

    card.classList.remove('hidden');
    card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function openEditUserModal(user) {
    openEditUserCard(user);
}

function updateSerialPreview() {
    const prefixInput = document.getElementById('invoice_prefix_input');
    const seqInput = document.getElementById('invoice_seq_input');
    const dateFormatSelect = document.getElementById('serial_date_format_select');
    const digitsSelect = document.getElementById('serial_number_digits_select');
    const previewEl = document.getElementById('invoice_sample_preview');

    if (!previewEl) return;

    const prefix = (prefixInput?.value || '').trim().toUpperCase();
    const seq = parseInt(seqInput?.value || '1', 10);
    const dateFormat = dateFormatSelect?.value || 'Ymd';
    const digits = parseInt(digitsSelect?.value || '4', 10);

    let datePart = '';
    const now = new Date();
    const YYYY = now.getFullYear();
    const MM = String(now.getMonth() + 1).padStart(2, '0');
    const DD = String(now.getDate()).padStart(2, '0');
    const YY = String(YYYY).slice(-2);

    if (dateFormat === 'Ymd') {
        datePart = `${YYYY}${MM}${DD}`;
    } else if (dateFormat === 'Ym') {
        datePart = `${YYYY}${MM}`;
    } else if (dateFormat === 'ym') {
        datePart = `${YY}${MM}`;
    } else if (dateFormat === 'FY') {
        const fyStart = now.getMonth() >= 3 ? YY : String(YYYY - 1).slice(-2);
        const fyEnd = now.getMonth() >= 3 ? String(YYYY + 1).slice(-2) : YY;
        datePart = `${fyStart}${fyEnd}`;
    }

    const paddedSeq = String(seq).padStart(digits, '0');

    if (datePart !== '') {
        if (!prefix) {
            previewEl.innerText = `${datePart}-${paddedSeq}`;
        } else {
            const separator = (prefix.endsWith('-') || prefix.endsWith('/')) ? '' : '-';
            previewEl.innerText = `${prefix}${separator}${datePart}-${paddedSeq}`;
        }
    } else {
        previewEl.innerText = prefix ? `${prefix}${paddedSeq}` : paddedSeq;
    }
}

// Global AJAX Form Submitter & Tab Persistence for Settings Hub
function initSettingsPage() {
    updateSerialPreview();
    // Restore active tab on page refresh or SPA navigation
    let urlParams = new URLSearchParams(window.location.search);
    let tabParam = urlParams.get('tab');
    let hashTab = window.location.hash.replace('#', '');
    let savedTab = localStorage.getItem('pww_active_settings_tab');
    let initialTab = tabParam || hashTab || savedTab || 'profile';

    switchSettingsTab(initialTab);
}

initSettingsPage();
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initSettingsPage);
}

window.saveModuleToggleAjax = async function(checkbox) {
    const modulesForm = document.getElementById('modulesVisibilityForm');
    if (!modulesForm || !checkbox) return;

    const cardLabel = checkbox.closest('div.p-4')?.querySelector('.font-bold')?.innerText?.trim() || 'Module';
    const cleanName = cardLabel.replace(/^[📦💳\s]+/, '').trim();
    const toastType = checkbox.checked ? 'success' : 'danger';
    const stateStr = checkbox.checked ? 'enabled' : 'disabled';
    const dynamicToastMsg = `'${cleanName}' ${stateStr}!`;

    try {
        const formData = new FormData(modulesForm);
        const response = await fetch(modulesForm.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            }
        });

        const res = await response.json();
        if (response.ok && res.success) {
            if (window.showToast) {
                window.showToast(toastType, dynamicToastMsg);
            }
            if (typeof window.clearPageCache === 'function') {
                window.clearPageCache();
            }
            
            // Live update Sidebar Navigation links instantly
            const isProd = modulesForm.querySelector('input[name="module_production"]')?.checked;
            const isOrders = modulesForm.querySelector('input[name="module_orders"]')?.checked;
            const isInvoices = modulesForm.querySelector('input[name="module_invoices"]')?.checked;
            const isPurchases = modulesForm.querySelector('input[name="module_purchases"]')?.checked;
            const isExpenses = modulesForm.querySelector('input[name="module_expenses"]')?.checked;
            const isInventory = modulesForm.querySelector('input[name="module_inventory"]')?.checked;
            const isBom = modulesForm.querySelector('input[name="module_bom"]')?.checked;
            const isClients = modulesForm.querySelector('input[name="module_clients"]')?.checked;
            const isPayroll = modulesForm.querySelector('input[name="module_payroll"]')?.checked;
            const isReports = modulesForm.querySelector('input[name="module_reports"]')?.checked;
            const isBackups = modulesForm.querySelector('input[name="module_backups"]')?.checked;
            const isActivityLogs = modulesForm.querySelector('input[name="module_activity_logs"]')?.checked;

            const toggleNav = (id, show) => {
                const el = document.getElementById(id);
                if (el) el.classList.toggle('hidden', !show);
            };

            toggleNav('sidebar-module-production', isProd);
            toggleNav('sidebar-module-orders', isOrders);
            toggleNav('sidebar-module-invoices', isInvoices);
            toggleNav('sidebar-module-purchases', isPurchases);
            toggleNav('sidebar-module-expenses', isExpenses);
            toggleNav('sidebar-module-rawmaterial', isInventory);
            toggleNav('sidebar-module-product', isInventory);
            toggleNav('sidebar-module-bom', isBom);
            toggleNav('sidebar-module-clients', isClients);
            toggleNav('sidebar-module-payroll', isPayroll);
            toggleNav('sidebar-module-reports', isReports);
            toggleNav('sidebar-module-backups', isBackups);
            toggleNav('sidebar-module-activity-logs', isActivityLogs);

            const sectionInvBom = document.getElementById('sidebar-section-inventory-bom');
            if (sectionInvBom) {
                sectionInvBom.classList.toggle('hidden', !isInventory && !isBom);
            }
        } else {
            if (window.showToast) {
                window.showToast('error', res.message || 'Failed to update module visibility');
            }
        }
    } catch (err) {
        console.error('Failed to auto-save module visibility:', err);
        if (window.showToast) {
            window.showToast('error', 'Failed to update module visibility.');
        }
    }
};

async function saveModuleVisibilityAjax(e) {
    if (e) e.preventDefault();
    
    const form = document.getElementById('modulesVisibilityForm');
    if (!form) return;

    const confirmResult = await Swal.fire({
        title: 'Update Module Visibility?',
        text: 'Are you sure you want to update active module visibility settings? Unused modules will be hidden from sidebar navigation.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#2563eb',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, Save Visibility',
        cancelButtonText: 'Cancel',
        customClass: {
            popup: 'rounded-2xl shadow-2xl p-6 border border-slate-100',
            title: 'text-xl font-black text-slate-800 tracking-tight',
            htmlContainer: 'text-sm font-semibold text-slate-600',
            confirmButton: 'px-5 py-2.5 rounded-xl font-bold text-sm shadow-sm bg-blue-600 hover:bg-blue-700 text-white cursor-pointer',
            cancelButton: 'px-5 py-2.5 rounded-xl font-bold text-sm shadow-sm bg-slate-500 hover:bg-slate-600 text-white cursor-pointer'
        }
    });

    if (!confirmResult.isConfirmed) {
        return;
    }

    const btn = document.getElementById('saveModuleVisibilityBtn');
    const origHtml = btn ? btn.innerHTML : '';
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = `<svg class="animate-spin h-4 w-4 text-white shrink-0" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Saving Module Settings...`;
    }

    try {
        const formData = new FormData(form);
        const response = await fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            }
        });

        const res = await response.json();

        if (response.ok && res.success) {
            // Live update Sidebar Navigation without reloading the page
            const isProd = form.querySelector('input[name="module_production"]')?.checked;
            const isOrders = form.querySelector('input[name="module_orders"]')?.checked;
            const isInvoices = form.querySelector('input[name="module_invoices"]')?.checked;
            const isPurchases = form.querySelector('input[name="module_purchases"]')?.checked;
            const isExpenses = form.querySelector('input[name="module_expenses"]')?.checked;
            const isInventory = form.querySelector('input[name="module_inventory"]')?.checked;
            const isBom = form.querySelector('input[name="module_bom"]')?.checked;
            const isClients = form.querySelector('input[name="module_clients"]')?.checked;
            const isPayroll = form.querySelector('input[name="module_payroll"]')?.checked;

            const toggleNav = (id, show) => {
                const el = document.getElementById(id);
                if (el) el.classList.toggle('hidden', !show);
            };

            toggleNav('sidebar-module-production', isProd);
            toggleNav('sidebar-module-orders', isOrders);
            toggleNav('sidebar-module-invoices', isInvoices);
            toggleNav('sidebar-module-purchases', isPurchases);
            toggleNav('sidebar-module-expenses', isExpenses);
            toggleNav('sidebar-module-rawmaterial', isInventory);
            toggleNav('sidebar-module-product', isInventory);
            toggleNav('sidebar-module-bom', isBom);
            toggleNav('sidebar-module-clients', isClients);
            toggleNav('sidebar-module-payroll', isPayroll);

            const sectionInvBom = document.getElementById('sidebar-section-inventory-bom');
            if (sectionInvBom) {
                sectionInvBom.classList.toggle('hidden', !isInventory && !isBom);
            }

            Swal.fire({
                title: 'Module Visibility Saved!',
                text: res.message || 'Active ERP module visibility settings updated successfully.',
                icon: 'success',
                timer: 1800,
                timerProgressBar: true,
                showConfirmButton: false,
                customClass: {
                    popup: 'rounded-2xl shadow-2xl p-6 border border-slate-100',
                    title: 'text-xl font-black text-slate-800 tracking-tight',
                    htmlContainer: 'text-sm font-semibold text-slate-600'
                }
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Save Failed',
                text: res.message || 'Failed to update module visibility settings.',
                confirmButtonColor: '#ef4444',
                customClass: {
                    popup: 'rounded-2xl shadow-2xl p-6 border border-slate-100',
                    title: 'text-xl font-black text-slate-800 tracking-tight',
                    htmlContainer: 'text-sm font-semibold text-slate-600'
                }
            });
        }
    } catch (err) {
        Swal.fire({
            icon: 'error',
            title: 'Server Error',
            text: 'An error occurred while saving module visibility.',
            confirmButtonColor: '#ef4444',
            customClass: {
                popup: 'rounded-2xl shadow-2xl p-6 border border-slate-100',
                title: 'text-xl font-black text-slate-800 tracking-tight',
                htmlContainer: 'text-sm font-semibold text-slate-600'
            }
        });
    } finally {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = origHtml;
        }
    }
}
</script>

<!-- Add / Edit Category Modal Dialog -->
<div id="categoryModal" onclick="if(event.target===this) window.closeCategoryModal()" class="hidden fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 p-6 max-w-md w-full transition-all duration-300">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
            <h3 id="categoryModalTitle" class="text-base font-bold text-slate-800">Add New Category</h3>
            <button type="button" onclick="window.closeCategoryModal()" class="text-xs font-bold text-slate-400 hover:text-slate-600 transition cursor-pointer">&times; Close</button>
        </div>

        <form id="categoryForm" action="" method="POST" class="ajax-form space-y-4">
            @csrf
            <input type="hidden" name="type" id="category_form_type" value="purchase">
            <input type="hidden" name="key" id="category_form_key" value="">

            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Category Label / Name</label>
                <input type="text" name="label" id="category_form_label" placeholder="e.g. Machine Maintenance & Spares" required
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-800 font-medium">
            </div>

            <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-100">
                <button type="button" onclick="window.closeCategoryModal()" class="bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold py-2.5 px-5 rounded-xl transition cursor-pointer">Cancel</button>
                <button type="submit" id="categorySubmitBtn" class="btn-primary py-2.5 px-6 text-xs font-bold bg-[#2563EB] hover:bg-blue-700 text-white rounded-xl shadow-xs cursor-pointer">
                    Save Category
                </button>
            </div>
        </form>
    </div>
</div>

<script>
window.openAddCategoryModal = function(type) {
    const modal = document.getElementById('categoryModal');
    const form = document.getElementById('categoryForm');
    const title = document.getElementById('categoryModalTitle');
    const typeInput = document.getElementById('category_form_type');
    const keyInput = document.getElementById('category_form_key');
    const labelInput = document.getElementById('category_form_label');

    if (modal && form) {
        form.action = "{{ route('settings.categories.store') }}";
        if (typeInput) typeInput.value = type;
        if (keyInput) keyInput.value = '';
        if (labelInput) labelInput.value = '';
        if (title) title.innerText = type === 'purchase' ? 'Add Purchase Category' : 'Add Expense Category';
        modal.classList.remove('hidden');
    }
};

window.openEditCategoryModal = function(type, key, currentLabel) {
    const modal = document.getElementById('categoryModal');
    const form = document.getElementById('categoryForm');
    const title = document.getElementById('categoryModalTitle');
    const typeInput = document.getElementById('category_form_type');
    const keyInput = document.getElementById('category_form_key');
    const labelInput = document.getElementById('category_form_label');

    if (modal && form) {
        form.action = "{{ route('settings.categories.update') }}";
        if (typeInput) typeInput.value = type;
        if (keyInput) keyInput.value = key;
        if (labelInput) labelInput.value = currentLabel;
        if (title) title.innerText = type === 'purchase' ? 'Edit Purchase Category' : 'Edit Expense Category';
        modal.classList.remove('hidden');
    }
};

window.closeCategoryModal = function() {
    document.querySelectorAll('#categoryModal').forEach(m => m.classList.add('hidden'));
};

window.deleteCategorySetting = function(type, key, label) {
    if (type === 'purchase' && key === 'raw_material') {
        if (window.showToast) window.showToast('danger', "Cannot delete 'Raw Material Purchase' category! It is required for automatic inventory restock.");
        return;
    }
    if (type === 'expense' && (key === 'salary' || key === 'gst_payment')) {
        if (window.showToast) window.showToast('danger', "Cannot delete 'Salary' or 'GST Payment' categories! They are required for payroll & tax ledgers.");
        return;
    }

    window.confirmDelete(
        "Delete Category Option?",
        "Are you sure you want to delete '" + label + "' from " + (type === 'purchase' ? 'Purchase' : 'Expense') + " categories?",
        function() {
            $.ajax({
                url: "{{ route('settings.categories.delete') }}",
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    type: type,
                    key: key
                },
                success: async function(res) {
                    if (res.success) {
                        if (window.showToast) window.showToast('success', res.message);
                        window.clearPageCache();
                        await window.loadPage(window.location.href, true);
                    } else {
                        if (window.showToast) window.showToast('danger', res.message);
                    }
                },
                error: function(err) {
                    const msg = err.responseJSON && err.responseJSON.message ? err.responseJSON.message : 'Failed to delete category.';
                    if (window.showToast) window.showToast('danger', msg);
                }
            });
        }
    );
};

// Global escape key handler to close category modal
$(document).off('keydown.categoryModal').on('keydown.categoryModal', function(e) {
    if (e.key === 'Escape') {
        window.closeCategoryModal();
    }
});
</script>

<style>
.active-tab-btn, .active-sub-tab-btn {
    background-color: #eff6ff !important;
    color: #1d4ed8 !important;
    border: 1px solid #bfdbfe !important;
}
</style>
@endsection
