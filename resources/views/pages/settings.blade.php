@extends('layouts.app')

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

    <!-- Alert Notifications -->
    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3.5 rounded-xl text-sm font-medium flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 font-bold">&times;</button>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3.5 rounded-xl text-sm font-medium flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('error') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700 font-bold">&times;</button>
        </div>
    @endif

    <!-- Navigation Tabs -->
    <div class="bg-white rounded-2xl p-2 border border-slate-200/80 shadow-sm flex flex-wrap gap-2">
        <button onclick="switchSettingsTab('modules')" id="tabBtn-modules" class="tab-btn active-tab-btn flex items-center gap-2 px-5 py-3 rounded-xl text-xs font-bold transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z" />
            </svg>
            Active ERP Modules
        </button>

        <button onclick="switchSettingsTab('users')" id="tabBtn-users" class="tab-btn flex items-center gap-2 px-5 py-3 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-50 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            User Access & Roles
        </button>

        <button onclick="switchSettingsTab('profile')" id="tabBtn-profile" class="tab-btn flex items-center gap-2 px-5 py-3 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-50 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
            Business Profile & Branding
        </button>

        <button onclick="switchSettingsTab('bank')" id="tabBtn-bank" class="tab-btn flex items-center gap-2 px-5 py-3 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-50 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
            </svg>
            Bank & Billing Defaults
        </button>
    </div>

    <!-- TAB 1: Active ERP Modules (Module Toggle Engine) -->
    <div id="settingsTab-modules" class="tab-content">
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-6">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div>
                    <h2 class="text-lg font-bold text-slate-800">Module Visibility Controls</h2>
                    <p class="text-slate-500 text-xs">Turn off unused modules to simplify the interface. Unused items disappear from the sidebar automatically. No data is lost.</p>
                </div>
            </div>

            <form action="{{ route('settings.modules') }}" method="POST" class="space-y-6">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">

                    <!-- Invoices -->
                    <div class="p-4 rounded-xl border border-slate-200/80 bg-slate-50/50 flex items-center justify-between">
                        <div>
                            <span class="block text-sm font-bold text-slate-800">Invoices & Billing</span>
                            <span class="text-[11px] text-slate-500 font-medium">Generate GST Tax Invoices</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="module_invoices" value="true" {{ $modules['module_invoices'] ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>

                    <!-- Sales Orders -->
                    <div class="p-4 rounded-xl border border-slate-200/80 bg-slate-50/50 flex items-center justify-between">
                        <div>
                            <span class="block text-sm font-bold text-slate-800">Sales Orders</span>
                            <span class="text-[11px] text-slate-500 font-medium">Manage B2B POs & Challans</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="module_orders" value="true" {{ $modules['module_orders'] ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>

                    <!-- Purchase Ledger -->
                    <div class="p-4 rounded-xl border border-slate-200/80 bg-slate-50/50 flex items-center justify-between">
                        <div>
                            <span class="block text-sm font-bold text-slate-800">Purchase Ledger</span>
                            <span class="text-[11px] text-slate-500 font-medium">Raw Material Purchases & Suppliers</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="module_purchases" value="true" {{ $modules['module_purchases'] ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>

                    <!-- Clients & Plants -->
                    <div class="p-4 rounded-xl border border-slate-200/80 bg-slate-50/50 flex items-center justify-between">
                        <div>
                            <span class="block text-sm font-bold text-slate-800">Clients & Plants</span>
                            <span class="text-[11px] text-slate-500 font-medium">Client Directory & Multi-plant Shipping</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="module_clients" value="true" {{ $modules['module_clients'] ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>

                    <!-- Expenses Ledger -->
                    <div class="p-4 rounded-xl border border-slate-200/80 bg-slate-50/50 flex items-center justify-between">
                        <div>
                            <span class="block text-sm font-bold text-slate-800">Expense Ledger</span>
                            <span class="text-[11px] text-slate-500 font-medium">Operational Factory Expenses</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="module_expenses" value="true" {{ $modules['module_expenses'] ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>

                    <!-- Production Logs -->
                    <div class="p-4 rounded-xl border border-slate-200/80 bg-slate-50/50 flex items-center justify-between">
                        <div>
                            <span class="block text-sm font-bold text-slate-800">Production Logs</span>
                            <span class="text-[11px] text-slate-500 font-medium">Batch Manufacturing & Yield</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="module_production" value="true" {{ $modules['module_production'] ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>

                    <!-- Bill of Materials (BOM) -->
                    <div class="p-4 rounded-xl border border-slate-200/80 bg-slate-50/50 flex items-center justify-between">
                        <div>
                            <span class="block text-sm font-bold text-slate-800">Bill of Materials (BOM)</span>
                            <span class="text-[11px] text-slate-500 font-medium">Product Material Recipes</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="module_bom" value="true" {{ $modules['module_bom'] ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>

                    <!-- Raw Materials & Products -->
                    <div class="p-4 rounded-xl border border-slate-200/80 bg-slate-50/50 flex items-center justify-between">
                        <div>
                            <span class="block text-sm font-bold text-slate-800">Raw Materials Inventory</span>
                            <span class="text-[11px] text-slate-500 font-medium">Raw Stock Thresholds & Items</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="module_inventory" value="true" {{ $modules['module_inventory'] ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>

                    <!-- Employee Payroll -->
                    <div class="p-4 rounded-xl border border-slate-200/80 bg-slate-50/50 flex items-center justify-between">
                        <div>
                            <span class="block text-sm font-bold text-slate-800">Employee Payroll</span>
                            <span class="text-[11px] text-slate-500 font-medium">Worker Wage Payouts</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="module_payroll" value="true" {{ $modules['module_payroll'] ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>

                </div>

                <div class="pt-4 border-t border-slate-100 flex justify-end">
                    <button type="submit" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-xl text-sm transition shadow-sm">
                        Save Module Visibility
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- TAB 2: User Access & Role Permissions -->
    <div id="settingsTab-users" class="tab-content hidden space-y-6">
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-6">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div>
                    <h2 class="text-lg font-bold text-slate-800">System Users & Role Permissions</h2>
                    <p class="text-slate-500 text-xs">Create team accounts and set permission rules for Super Admin, Accountant, Production Manager, or View-Only</p>
                </div>
                <button onclick="openAddUserModal()" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-xl text-xs transition shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add System User
                </button>
            </div>

            <!-- Users Table -->
            <div class="overflow-x-auto rounded-xl border border-slate-200/60">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 border-b border-slate-200/60 text-xs font-bold text-slate-600 uppercase">
                        <tr>
                            <th class="py-3 px-4">User</th>
                            <th class="py-3 px-4">Email</th>
                            <th class="py-3 px-4">Assigned Role</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($users as $u)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="py-3.5 px-4 font-bold text-slate-800 flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 font-black flex items-center justify-center text-xs uppercase">
                                        {{ substr($u->name, 0, 1) }}
                                    </div>
                                    {{ $u->name }}
                                </td>
                                <td class="py-3.5 px-4 font-mono text-xs text-slate-600">{{ $u->email }}</td>
                                <td class="py-3.5 px-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200 capitalize">
                                        {{ str_replace('_', ' ', $u->role) }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold uppercase {{ $u->status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                        {{ $u->status }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right space-x-2">
                                    <button onclick='openEditUserModal(@json($u))' class="text-xs font-bold text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-lg transition">
                                        Edit & Permissions
                                    </button>
                                    @if(Auth::id() !== $u->id)
                                        <form action="{{ route('settings.users.delete', $u->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete user {{ $u->name }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs font-bold text-rose-600 hover:text-rose-800 bg-rose-50 hover:bg-rose-100 px-3 py-1.5 rounded-lg transition">
                                                Delete
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB 3: Business Profile & Branding -->
    <div id="settingsTab-profile" class="tab-content hidden space-y-6">
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

    <!-- TAB 4: Bank & Billing Defaults -->
    <div id="settingsTab-bank" class="tab-content hidden space-y-6">
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-6">
            <h2 class="text-lg font-bold text-slate-800 border-b border-slate-100 pb-4">Bank Account & Default Billing Terms</h2>

            <form action="{{ route('settings.bank') }}" method="POST" class="space-y-6">
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
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Invoice Prefix</label>
                        <input type="text" name="invoice_prefix" value="{{ \App\Models\Setting::get('invoice_prefix', 'PWW-') }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-semibold text-slate-800">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Sales Order Prefix</label>
                        <input type="text" name="order_prefix" value="{{ \App\Models\Setting::get('order_prefix', 'PWW-ORD-') }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-semibold text-slate-800">
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
</div>

<!-- Modal: Add New System User -->
<div id="addUserModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl max-w-xl w-full p-6 border border-slate-200 shadow-xl space-y-5">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-lg font-bold text-slate-800">Add New System User</h3>
            <button onclick="closeAddUserModal()" class="text-slate-400 hover:text-slate-600 font-bold text-xl">&times;</button>
        </div>

        <form action="{{ route('settings.users.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Full Name</label>
                <input type="text" name="name" required class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-medium text-slate-800">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Login Email</label>
                <input type="email" name="email" required class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-medium text-slate-800">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Password</label>
                <input type="password" name="password" required minlength="6" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-medium text-slate-800">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Assign System Role</label>
                <select name="role" required class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-3.5 text-sm font-semibold text-slate-800">
                    @foreach($roles as $key => $r)
                        <option value="{{ $key }}">{{ $r['name'] }} — {{ $r['description'] }}</option>
                    @endforeach
                </select>
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end gap-2">
                <button type="button" onclick="closeAddUserModal()" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100">Cancel</button>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-5 rounded-xl text-xs shadow-sm">Create Account</button>
            </div>
        </form>
    </div>
</div>

<script>
function switchSettingsTab(tabName) {
    document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('.tab-btn').forEach(el => {
        el.classList.remove('active-tab-btn', 'bg-blue-50', 'text-blue-700');
        el.classList.add('text-slate-600');
    });

    document.getElementById('settingsTab-' + tabName).classList.remove('hidden');
    const activeBtn = document.getElementById('tabBtn-' + tabName);
    activeBtn.classList.add('active-tab-btn', 'bg-blue-50', 'text-blue-700');
}

function openAddUserModal() {
    document.getElementById('addUserModal').classList.remove('hidden');
}

function closeAddUserModal() {
    document.getElementById('addUserModal').classList.add('hidden');
}
</script>

<style>
.active-tab-btn {
    background-color: #eff6ff !important;
    color: #1d4ed8 !important;
    border: 1px solid #bfdbfe !important;
}
</style>
@endsection
