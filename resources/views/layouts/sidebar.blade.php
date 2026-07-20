<!-- Sidebar Navigation (Gemini Style) -->
<aside id="sidebar" class="bg-white border-r border-slate-200 flex flex-col fixed h-full z-30 shadow-xs transition-all duration-200 ease-in-out w-64">
    <!-- Sidebar Header -->
    <div class="sidebar-header px-3.5 py-3 border-b border-slate-100 flex items-center justify-between min-h-[60px]">
        <div class="sidebar-brand-container flex items-center space-x-3 min-w-0">
            <!-- Sidebar Toggle Button (Gemini Style) -->
            <button id="sidebarPinToggle" type="button" class="p-2 text-slate-600 hover:bg-slate-100 hover:text-slate-900 rounded-full focus:outline-none transition duration-150 flex-shrink-0 group relative" title="Toggle Sidebar">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h12"></path>
                </svg>
            </button>

            <!-- Brand Logo & Text -->
            <div class="sidebar-brand-details flex items-center space-x-2.5 min-w-0 overflow-hidden">
                <img class="h-8 w-8 object-contain rounded-lg flex-shrink-0 border border-slate-100" src="{{ asset(\App\Models\Setting::get('logo_path', 'logo.jpg')) }}" alt="Business Logo">
                <div class="sidebar-header-text flex flex-col min-w-0">
                    <span class="text-xs font-black tracking-tight text-slate-800 leading-none whitespace-nowrap">{{ \App\Models\Setting::get('business_name', 'Praful Welding Works') }}</span>
                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1">ERP Portal</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Links -->
    <nav class="flex-grow py-3 px-2.5 space-y-1.5 overflow-y-auto">
        <!-- Overview -->
        <a href="{{ route('overview') }}" title="Overview" class="nav-link-item group relative flex items-center px-3 py-2.5 rounded-xl text-sm font-semibold text-slate-600 hover:bg-slate-100/80 hover:text-slate-900 transition duration-150 {{ Route::is('overview') ? 'active-nav' : '' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z"></path></svg>
            <span class="sidebar-text ml-3">Overview</span>
            <span class="sidebar-tooltip">Overview</span>
        </a>

        <!-- Inventory Section -->
        <div class="nav-section space-y-1 pt-1">
            <span class="sidebar-category-header px-3 text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Inventory</span>
            <a href="{{ route('inventory', ['tab' => 'materials']) }}" title="Raw Materials" class="nav-link-item group relative flex items-center px-3 py-2 rounded-xl text-sm font-semibold transition duration-150 {{ Route::is('inventory') && request('tab', 'materials') === 'materials' ? 'active-nav' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                <span class="sidebar-text ml-3">Raw Materials</span>
                <span class="sidebar-tooltip">Raw Materials</span>
            </a>
            <a href="{{ route('inventory', ['tab' => 'goods']) }}" title="Finished Goods" class="nav-link-item group relative flex items-center px-3 py-2 rounded-xl text-sm font-semibold transition duration-150 {{ Route::is('inventory') && request('tab') === 'goods' ? 'active-nav' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                <span class="sidebar-text ml-3">Finished Goods</span>
                <span class="sidebar-tooltip">Finished Goods</span>
            </a>
        </div>

        <!-- Operations Section -->
        <div class="nav-section space-y-1 pt-1">
            <span class="sidebar-category-header px-3 text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Production & Dispatches</span>
            <a href="{{ route('bom') }}" title="Bill of Materials (BOM)" class="nav-link-item group relative flex items-center px-3 py-2 rounded-xl text-sm font-semibold transition duration-150 {{ Route::is('bom') ? 'active-nav' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                <span class="sidebar-text ml-3">Bill of Materials (BOM)</span>
                <span class="sidebar-tooltip">Bill of Materials (BOM)</span>
            </a>
            <a href="{{ route('production') }}" title="Production Logs" class="nav-link-item group relative flex items-center px-3 py-2 rounded-xl text-sm font-semibold transition duration-150 {{ Route::is('production') ? 'active-nav' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                <span class="sidebar-text ml-3">Production Logs</span>
                <span class="sidebar-tooltip">Production Logs</span>
            </a>
            <a href="{{ route('clients') }}" title="Clients & Plants" class="nav-link-item group relative flex items-center px-3 py-2 rounded-xl text-sm font-semibold transition duration-150 {{ Route::is('clients') ? 'active-nav' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                <span class="sidebar-text ml-3">Clients & Plants</span>
                <span class="sidebar-tooltip">Clients & Plants</span>
            </a>
            <a href="{{ route('challans') }}" title="Delivery Challans" class="nav-link-item group relative flex items-center px-3 py-2 rounded-xl text-sm font-semibold transition duration-150 {{ Route::is('challans') ? 'active-nav' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"></path></svg>
                <span class="sidebar-text ml-3">Delivery Challans</span>
                <span class="sidebar-tooltip">Delivery Challans</span>
            </a>
        </div>

        <!-- Billing Section -->
        <div class="nav-section space-y-1 pt-1">
            <span class="sidebar-category-header px-3 text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Billing & Finance</span>
            <a href="{{ route('invoices', ['tab' => 'manual-builder']) }}" title="Invoice Ledger" class="nav-link-item group relative flex items-center px-3 py-2 rounded-xl text-sm font-semibold transition duration-150 {{ Route::is('invoices') && (request('tab', 'manual-builder') === 'manual-builder') ? 'active-nav' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 01-2-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <span class="sidebar-text ml-3">Invoice Ledger</span>
                <span class="sidebar-tooltip">Invoice Ledger</span>
            </a>
            <a href="{{ route('invoices', ['tab' => 'challan-converter']) }}" title="Convert Challans" class="nav-link-item group relative flex items-center px-3 py-2 rounded-xl text-sm font-semibold transition duration-150 {{ Route::is('invoices') && request('tab') === 'challan-converter' ? 'active-nav' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                <span class="sidebar-text ml-3">Convert Challans</span>
                <span class="sidebar-tooltip">Convert Challans</span>
            </a>
            <a href="{{ route('employees') }}" title="Employees" class="nav-link-item group relative flex items-center px-3 py-2 rounded-xl text-sm font-semibold transition duration-150 {{ Route::is('employees') ? 'active-nav' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                <span class="sidebar-text ml-3">Employees</span>
                <span class="sidebar-tooltip">Employees</span>
            </a>
            <a href="{{ route('expenses') }}" title="Expenses Ledger" class="nav-link-item group relative flex items-center px-3 py-2 rounded-xl text-sm font-semibold text-slate-600 hover:bg-slate-100/80 hover:text-slate-900 transition duration-150 {{ Route::is('expenses') ? 'active-nav' : '' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                <span class="sidebar-text ml-3">Expenses Ledger</span>
                <span class="sidebar-tooltip">Expenses Ledger</span>
            </a>
        </div>

        <!-- Reporting Section -->
        <div class="nav-section space-y-1 pt-1">
            <span class="sidebar-category-header px-3 text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Audits</span>
            <a href="{{ route('reports') }}" title="Reports & Export" class="nav-link-item group relative flex items-center px-3 py-2 rounded-xl text-sm font-semibold text-slate-600 hover:bg-slate-100/80 hover:text-slate-900 transition duration-150 {{ Route::is('reports') ? 'active-nav' : '' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 01-2-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <span class="sidebar-text ml-3">Reports & Export</span>
                <span class="sidebar-tooltip">Reports & Export</span>
            </a>
        </div>
    </nav>

    <!-- Sidebar User Profile & Logout -->
    <div class="sidebar-footer p-2.5 border-t border-slate-100 flex flex-col space-y-1.5 flex-shrink-0">
        <a href="{{ route('profile') }}" class="group relative flex items-center justify-between hover:bg-slate-100/80 p-2 rounded-xl transition duration-150">
            <div class="flex items-center space-x-3 min-w-0">
                <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold uppercase group-hover:bg-blue-200 transition flex-shrink-0 text-sm">
                    {{ substr(Auth::user()->name ?? 'P', 0, 1) }}
                </div>
                <div class="sidebar-profile-detail flex flex-col min-w-0">
                    <span class="text-xs font-bold text-slate-800 truncate leading-none group-hover:text-blue-700 transition">{{ Auth::user()->name ?? 'Praful Patel' }}</span>
                    <span class="text-[10px] font-semibold text-slate-400 capitalize mt-1">{{ Auth::user()->role ?? 'Staff' }}</span>
                </div>
            </div>
            <div class="sidebar-profile-detail text-slate-400 group-hover:text-blue-600 transition flex-shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
            </div>
            <span class="sidebar-tooltip">Profile Settings</span>
        </a>
        <a href="{{ route('logout') }}" class="sidebar-logout-btn group relative w-full flex items-center px-3 py-2 rounded-xl text-xs font-semibold text-rose-600 hover:bg-rose-50 border border-transparent transition duration-150">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                <polyline points="16 17 21 12 16 7"></polyline>
                <line x1="21" y1="12" x2="9" y2="12"></line>
            </svg>
            <span class="sidebar-text ml-3">Logout</span>
            <span class="sidebar-tooltip">Logout</span>
        </a>
    </div>
</aside>

