<!-- Sidebar Navigation -->
<aside id="sidebar" class="w-64 bg-white border-r border-slate-200 flex flex-col fixed top-0 left-0 h-screen z-40 shadow-md transition-all duration-300 transform -translate-x-full md:translate-x-0">
    <!-- Sidebar Brand Header -->
    <div class="sidebar-header px-4 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
        <div class="sidebar-brand-container flex items-center space-x-2.5 min-w-0 overflow-hidden">
            <!-- Brand Image Logo -->
            <img class="h-9 w-9 object-contain rounded-lg flex-shrink-0 border border-slate-100" src="{{ asset(\App\Models\Setting::get('logo_path', 'logo.jpg')) }}" alt="Business Logo">
            <div class="sidebar-header-text flex flex-col min-w-0 overflow-hidden">
                <span class="text-xs font-black tracking-tight text-slate-800 leading-none truncate">{{ \App\Models\Setting::get('business_name', 'Praful Welding Works') }}</span>
                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1">ERP PORTAL</span>
            </div>
        </div>
        <!-- Pin Button -->
        <button id="sidebarPinToggle" class="text-blue-600 hover:text-blue-800 p-1 rounded-full focus:outline-none transition-colors duration-150 flex-shrink-0 relative w-6 h-6 flex items-center justify-center border-2 border-blue-500 hidden md:flex">
            <span id="sidebarPinDot" class="w-2.5 h-2.5 rounded-full bg-blue-500 transition-all duration-200"></span>
        </button>
    </div>

    <!-- Navigation Links -->
    <nav class="flex-grow p-4 space-y-3 overflow-y-auto">
        <!-- 1. Overview -->
        <a href="{{ route('overview') }}" class="nav-link-item flex items-center space-x-3 px-4 py-2 rounded-xl text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition duration-150 {{ Route::is('overview') ? 'active-nav' : '' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z"></path></svg>
            <span class="sidebar-text">Overview</span>
        </a>

        <!-- Operations & Billing Section -->
        <div class="space-y-1">
            <span class="sidebar-category-header px-4 text-[9px] font-bold text-slate-400 uppercase tracking-wider block">Operations & Billing</span>
            
            @hasPermission('page_production')
                <!-- Production Logs -->
                <a id="sidebar-module-production" href="{{ route('production') }}" class="nav-link-item flex items-center space-x-3 px-4 py-2 rounded-xl text-sm font-semibold transition duration-150 {{ \App\Models\Setting::get('module_production', 'true') === 'true' ? '' : 'hidden' }} {{ Route::is('production') ? 'active-nav' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.9 6.91a2.12 2.12 0 01-3-3l6.91-6.9a6 6 0 017.94-7.94l-3.76 3.76z"></path></svg>
                    <span class="sidebar-text">Production Logs</span>
                </a>
            @endhasPermission

            @hasPermission('page_orders')
                <!-- Sales Orders -->
                <a id="sidebar-module-orders" href="{{ route('orders') }}" class="nav-link-item flex items-center space-x-3 px-4 py-2 rounded-xl text-sm font-semibold transition duration-150 {{ \App\Models\Setting::get('module_orders', 'true') === 'true' ? '' : 'hidden' }} {{ Route::is('orders') ? 'active-nav' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    <span class="sidebar-text">Sales Orders</span>
                </a>
            @endhasPermission

            @hasPermission('page_invoices')
                <!-- Invoice Ledger -->
                <a id="sidebar-module-invoices" href="{{ route('invoices') }}" class="nav-link-item flex items-center space-x-3 px-4 py-2 rounded-xl text-sm font-semibold transition duration-150 {{ \App\Models\Setting::get('module_invoices', 'true') === 'true' ? '' : 'hidden' }} {{ Route::is('invoices') ? 'active-nav' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m-6 4h6m-6 4h4m5 6H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v13a2 2 0 01-2 2z"></path></svg>
                    <span class="sidebar-text">Invoice Ledger</span>
                </a>
            @endhasPermission

            @hasPermission('page_purchases')
                <!-- Purchase Ledger -->
                <a id="sidebar-module-purchases" href="{{ route('purchases') }}" class="nav-link-item flex items-center space-x-3 px-4 py-2 rounded-xl text-sm font-semibold transition duration-150 {{ \App\Models\Setting::get('module_purchases', 'true') === 'true' ? '' : 'hidden' }} {{ Route::is('purchases') ? 'active-nav' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"></path></svg>
                    <span class="sidebar-text">Purchase Ledger</span>
                </a>
            @endhasPermission

            @hasPermission('page_expenses')
                <!-- Expense Ledger -->
                <a id="sidebar-module-expenses" href="{{ route('expenses') }}" class="nav-link-item flex items-center space-x-3 px-4 py-2 rounded-xl text-sm font-semibold transition duration-150 {{ \App\Models\Setting::get('module_expenses', 'true') === 'true' ? '' : 'hidden' }} {{ Route::is('expenses') ? 'active-nav' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    <span class="sidebar-text">Expenses Ledger</span>
                </a>
            @endhasPermission
        </div>

        <!-- Inventory & Manufacturing Section -->
        <div id="sidebar-section-inventory-bom" class="space-y-1 {{ (\App\Models\Setting::get('module_inventory', 'true') === 'true' || \App\Models\Setting::get('module_bom', 'true') === 'true') ? '' : 'hidden' }}">
            <span class="sidebar-category-header px-4 text-[9px] font-bold text-slate-400 uppercase tracking-wider block">Inventory & Manufacturing</span>
            
            @hasPermission('page_rawmaterial')
                <!-- Raw Materials -->
                <a id="sidebar-module-rawmaterial" href="{{ route('rawmaterial') }}" class="nav-link-item flex items-center space-x-3 px-4 py-2 rounded-xl text-sm font-semibold transition duration-150 {{ \App\Models\Setting::get('module_inventory', 'true') === 'true' ? '' : 'hidden' }} {{ Route::is('rawmaterial') ? 'active-nav' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    <span class="sidebar-text">Raw Materials</span>
                </a>
            @endhasPermission

            @hasPermission('page_product')
                <!-- Products -->
                <a id="sidebar-module-product" href="{{ route('product') }}" class="nav-link-item flex items-center space-x-3 px-4 py-2 rounded-xl text-sm font-semibold transition duration-150 {{ \App\Models\Setting::get('module_inventory', 'true') === 'true' ? '' : 'hidden' }} {{ Route::is('product') ? 'active-nav' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    <span class="sidebar-text">Products</span>
                </a>
            @endhasPermission

            @hasPermission('page_bom')
                <!-- Bill of Materials (BOM) -->
                <a id="sidebar-module-bom" href="{{ route('bom') }}" class="nav-link-item flex items-center space-x-3 px-4 py-2 rounded-xl text-sm font-semibold transition duration-150 {{ \App\Models\Setting::get('module_bom', 'true') === 'true' ? '' : 'hidden' }} {{ Route::is('bom') ? 'active-nav' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                    <span class="sidebar-text">Bill of Materials (BOM)</span>
                </a>
            @endhasPermission
        </div>

        <!-- Management & Audits Section -->
        <div class="space-y-1">
            <span class="sidebar-category-header px-4 text-[9px] font-bold text-slate-400 uppercase tracking-wider block">Management & Audits</span>
            
            @hasPermission('page_clients')
                <!-- Clients & Plants -->
                <a id="sidebar-module-clients" href="{{ route('clients') }}" class="nav-link-item flex items-center space-x-3 px-4 py-2 rounded-xl text-sm font-semibold transition duration-150 {{ \App\Models\Setting::get('module_clients', 'true') === 'true' ? '' : 'hidden' }} {{ Route::is('clients') ? 'active-nav' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    <span class="sidebar-text">Clients & Plants</span>
                </a>
            @endhasPermission

            @hasPermission('page_employees')
                <!-- Employees -->
                <a id="sidebar-module-payroll" href="{{ route('employees') }}" class="nav-link-item flex items-center space-x-3 px-4 py-2 rounded-xl text-sm font-semibold transition duration-150 {{ \App\Models\Setting::get('module_payroll', 'true') === 'true' ? '' : 'hidden' }} {{ Route::is('employees') ? 'active-nav' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    <span class="sidebar-text">Employees</span>
                </a>
            @endhasPermission

            @hasPermission('page_reports')
                <!-- Reports -->
                <a href="{{ route('reports') }}" class="nav-link-item flex items-center space-x-3 px-4 py-2 rounded-xl text-sm font-semibold transition duration-150 {{ Route::is('reports') ? 'active-nav' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path></svg>
                    <span class="sidebar-text">Reports</span>
                </a>
            @endhasPermission

            @hasPermission('backups_settings_manage')
                <!-- Backup & Restore -->
                <a href="{{ route('backup.index') }}" class="nav-link-item flex items-center space-x-3 px-4 py-2 rounded-xl text-sm font-semibold transition duration-150 {{ Route::is('backup.*') ? 'active-nav' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    <span class="sidebar-text">Backup & Restore</span>
                </a>

                <!-- System Settings -->
                <a href="{{ route('settings.index') }}" class="nav-link-item flex items-center space-x-3 px-4 py-2 rounded-xl text-sm font-semibold transition duration-150 {{ Route::is('settings.*') ? 'active-nav' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    <span class="sidebar-text">System Settings</span>
                </a>
            @endhasPermission
        </div>
    </nav>
</aside>
