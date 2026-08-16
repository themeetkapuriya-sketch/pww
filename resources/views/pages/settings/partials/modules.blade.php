<!-- TAB 4: Active ERP Modules Partial -->
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

        <form action="{{ route('settings.modules') }}" method="POST" id="modulesVisibilityForm" class="ajax-form no-reload no-reset space-y-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">

                <!-- 1-Click Master Mode: Simplified Billing & Accounting Mode -->
                <div class="p-5 rounded-2xl border-2 border-indigo-200 dark:border-indigo-700 bg-gradient-to-r from-indigo-50/90 via-blue-50/80 to-purple-50/90 dark:from-indigo-950/80 dark:via-slate-900/90 dark:to-purple-950/80 flex flex-col md:flex-row md:items-center justify-between gap-4 col-span-1 md:col-span-3 shadow-xs mb-2">
                    <div class="space-y-1">
                        <span class="block text-base font-extrabold text-indigo-950 dark:text-indigo-100 flex items-center gap-2">
                            ⚡ 1-Click Simplified Billing & Accounting Mode
                            <span id="simplified_billing_active_badge" class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-emerald-500 text-white shadow-2xs {{ ($modules['simplified_billing_mode'] ?? false) ? '' : 'hidden' }}">ACTIVE</span>
                        </span>
                        <p class="text-xs text-indigo-900/80 dark:text-indigo-200/90 font-medium leading-relaxed">
                            Turn <strong>ON</strong> to instantly configure the ERP for pure billing & accounting (Invoices, Purchases, Expenses, Clients & Reports). Automatically disables Stock Management, Production, Orders & Payroll for a clean, hassle-free layout!
                        </p>
                    </div>
                    <label class="inline-flex items-center cursor-pointer select-none shrink-0">
                        <input type="checkbox" id="simplified_billing_mode_toggle" name="simplified_billing_mode" value="true" {{ ($modules['simplified_billing_mode'] ?? false) ? 'checked' : '' }} class="erp-toggle-input" onchange="toggleSimplifiedBillingModeAjax(this)">
                        <span class="erp-toggle-slider"></span>
                    </label>
                </div>

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

                <!-- Stock Management -->
                @php
                    $isSimplifiedActive = $modules['simplified_billing_mode'] ?? false;
                @endphp
                <div id="track_stock_card" class="p-4 rounded-xl border border-blue-200/80 bg-blue-50/40 flex items-center justify-between col-span-1 md:col-span-3 {{ $isSimplifiedActive ? 'opacity-60 pointer-events-none' : '' }}">
                    <div>
                        <span class="block text-sm font-bold text-blue-900 flex items-center gap-1.5">
                            📦 Stock Management
                            <span id="track_stock_disabled_badge" class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-200 text-slate-700 border border-slate-300 {{ $isSimplifiedActive ? '' : 'hidden' }}">
                                🔒 Disabled in Simplified Billing Mode
                            </span>
                        </span>
                        <span class="text-[11px] text-slate-600 font-medium">Enable inventory stock tracking, auto-deductions on invoices, and stock-based order pipeline checks. Turn OFF if you only do billing.</span>
                    </div>
                    <label class="inline-flex items-center cursor-pointer select-none">
                        <input type="checkbox" name="track_stock" value="true" {{ ($modules['track_stock'] ?? true) && !$isSimplifiedActive ? 'checked' : '' }} {{ $isSimplifiedActive ? 'disabled' : '' }} class="erp-toggle-input" onchange="saveModuleToggleAjax(this)">
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
