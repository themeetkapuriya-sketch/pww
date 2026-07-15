<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'PWW ERP') - Praful Welding Works</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
        }
        .active-nav {
            background-color: rgba(30, 115, 190, 0.08) !important;
            color: #1E73BE !important;
        }
        .active-nav svg, .active-nav span {
            color: #1E73BE !important;
        }

        /* Hide scrollbar for the sidebar navigation */
        #sidebar nav::-webkit-scrollbar {
            display: none;
        }
        #sidebar nav {
            -ms-overflow-style: none;  /* IE and Edge */
            scrollbar-width: none;  /* Firefox */
        }

        /* Sidebar Width Transitions */
        #sidebar {
            transition: width 0.22s cubic-bezier(0.4, 0, 0.2, 1), transform 0.22s ease, box-shadow 0.22s ease;
        }
        #main-content {
            transition: padding-left 0.22s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .sidebar-text {
            transition: opacity 0.15s ease;
        }

        /* Collapsed State Styles (Desktop) */
        @media (min-width: 768px) {
            #sidebar.sidebar-collapsed {
                width: 72px;
            }
            #sidebar.sidebar-collapsed .sidebar-text {
                opacity: 0;
                width: 0;
                overflow: hidden;
                white-space: nowrap;
                pointer-events: none;
                margin-left: 0 !important;
                display: none;
            }
            #sidebar.sidebar-collapsed .sidebar-header-text {
                display: none;
            }
            #sidebar.sidebar-collapsed .sidebar-category-header {
                visibility: hidden;
                opacity: 0;
                height: 0px;
                margin: 0 !important;
                padding: 0 !important;
                overflow: hidden;
            }
            #sidebar.sidebar-collapsed .sidebar-profile-detail {
                display: none;
            }
            #sidebar.sidebar-collapsed .nav-link-item {
                justify-content: center;
                padding-left: 0;
                padding-right: 0;
            }
            #sidebar.sidebar-collapsed .nav-link-item svg {
                margin: 0 !important;
            }

            /* Hover Expand Effect */
            #sidebar.sidebar-collapsed:hover {
                width: 256px; /* w-64 */
                box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
            }
            #sidebar.sidebar-collapsed:hover .sidebar-text {
                opacity: 1;
                width: auto;
                overflow: visible;
                pointer-events: auto;
                margin-left: 0.75rem !important;
                display: inline;
            }
            #sidebar.sidebar-collapsed:hover .sidebar-header-text {
                display: block;
            }
            #sidebar.sidebar-collapsed:hover .sidebar-category-header {
                visibility: visible;
                opacity: 1;
                height: auto;
                margin-top: 8px;
                margin-bottom: 4px;
            }
            #sidebar.sidebar-collapsed:hover .sidebar-profile-detail {
                display: flex;
            }
            #sidebar.sidebar-collapsed:hover .nav-link-item {
                justify-content: flex-start;
                padding-left: 1rem;
                padding-right: 1rem;
            }
            #sidebar.sidebar-collapsed:hover .nav-link-item svg {
                margin-right: 0.75rem !important;
            }

            /* Collapsed Tweaks: Hide pin button, disable scroll, center logo & footer */
            #sidebar.sidebar-collapsed #sidebarPinToggle {
                display: none !important;
            }
            #sidebar.sidebar-collapsed .sidebar-header {
                justify-content: center;
                padding-left: 0;
                padding-right: 0;
            }
            #sidebar.sidebar-collapsed .sidebar-brand-container {
                margin: 0 !important;
                space: 0 !important;
            }
            #sidebar.sidebar-collapsed nav {
                overflow-y: hidden !important;
            }
            #sidebar.sidebar-collapsed .sidebar-footer {
                padding-left: 0;
                padding-right: 0;
                align-items: center;
            }

            /* Show elements back on hover */
            #sidebar.sidebar-collapsed:hover #sidebarPinToggle {
                display: flex !important;
            }
            #sidebar.sidebar-collapsed:hover .sidebar-header {
                justify-content: space-between;
                padding-left: 1rem;
                padding-right: 1rem;
            }
            #sidebar.sidebar-collapsed:hover nav {
                overflow-y: auto !important;
            }
            #sidebar.sidebar-collapsed:hover .sidebar-footer {
                padding-left: 1rem;
                padding-right: 1rem;
                align-items: stretch;
            }
        }
    </style>
</head>
<body class="min-h-screen md:flex bg-slate-50">

    <!-- Sidebar Navigation -->
    <aside id="sidebar" class="w-64 bg-white border-r border-slate-200 flex flex-col fixed h-full z-30 shadow-sm transition-transform duration-300 transform -translate-x-full">
        <!-- Sidebar Brand Header -->
        <div class="sidebar-header px-4 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <div class="sidebar-brand-container flex items-center space-x-2.5 min-w-0">
                <!-- PWW Brand Image Logo -->
                <img class="h-9 w-9 object-contain rounded-lg flex-shrink-0 border border-slate-100" src="{{ asset('logo.jpg') }}" alt="PWW Logo">
                <div class="sidebar-header-text flex flex-col min-w-0">
                    <span class="text-xs font-black tracking-tight text-slate-800 leading-none whitespace-nowrap">Praful Welding Works</span>
                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1">ERP Portal</span>
                </div>
            </div>
            <!-- Pin Button (Double Circle / Radio button style) -->
            <button id="sidebarPinToggle" class="text-blue-600 hover:text-blue-800 p-1 rounded-full focus:outline-none transition-colors duration-150 flex-shrink-0 relative w-6 h-6 flex items-center justify-center border-2 border-blue-500 hidden md:flex">
                <span id="sidebarPinDot" class="w-2.5 h-2.5 rounded-full bg-blue-500 transition-all duration-200"></span>
            </button>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-grow p-4 space-y-3 overflow-y-auto">
            <a href="{{ route('overview') }}" class="nav-link-item flex items-center space-x-3 px-4 py-2 rounded-xl text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition duration-150 {{ Route::is('overview') ? 'active-nav' : '' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z"></path></svg>
                <span class="sidebar-text">Overview</span>
            </a>

            <!-- Inventory Section -->
            <div class="space-y-1">
                <span class="sidebar-category-header px-4 text-[9px] font-bold text-slate-400 uppercase tracking-wider block">Inventory</span>
                <a href="{{ route('inventory', ['tab' => 'materials']) }}" class="nav-link-item flex items-center space-x-3 px-4 py-2 rounded-xl text-sm font-semibold transition duration-150 {{ Route::is('inventory') && request('tab', 'materials') === 'materials' ? 'active-nav' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    <span class="sidebar-text">Raw Materials</span>
                </a>
                <a href="{{ route('inventory', ['tab' => 'goods']) }}" class="nav-link-item flex items-center space-x-3 px-4 py-2 rounded-xl text-sm font-semibold transition duration-150 {{ Route::is('inventory') && request('tab') === 'goods' ? 'active-nav' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    <span class="sidebar-text">Finished Goods</span>
                </a>
            </div>

            <!-- Operations Section -->
            <div class="space-y-1">
                <span class="sidebar-category-header px-4 text-[9px] font-bold text-slate-400 uppercase tracking-wider block">Production & Dispatches</span>
                <a href="{{ route('bom') }}" class="nav-link-item flex items-center space-x-3 px-4 py-2 rounded-xl text-sm font-semibold transition duration-150 {{ Route::is('bom') ? 'active-nav' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                    <span class="sidebar-text">Bill of Materials (BOM)</span>
                </a>
                <a href="{{ route('production') }}" class="nav-link-item flex items-center space-x-3 px-4 py-2 rounded-xl text-sm font-semibold transition duration-150 {{ Route::is('production') ? 'active-nav' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    <span class="sidebar-text">Production Logs</span>
                </a>
                <a href="{{ route('clients') }}" class="nav-link-item flex items-center space-x-3 px-4 py-2 rounded-xl text-sm font-semibold transition duration-150 {{ Route::is('clients') ? 'active-nav' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    <span class="sidebar-text">Clients & Plants</span>
                </a>
                <a href="{{ route('challans') }}" class="nav-link-item flex items-center space-x-3 px-4 py-2 rounded-xl text-sm font-semibold transition duration-150 {{ Route::is('challans') ? 'active-nav' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"></path></svg>
                    <span class="sidebar-text">Delivery Challans</span>
                </a>
            </div>

            <!-- Billing Section -->
            <div class="space-y-1">
                <span class="sidebar-category-header px-4 text-[9px] font-bold text-slate-400 uppercase tracking-wider block">Billing & Finance</span>
                <a href="{{ route('invoices', ['tab' => 'ledger']) }}" class="nav-link-item flex items-center space-x-3 px-4 py-2 rounded-xl text-sm font-semibold transition duration-150 {{ Route::is('invoices') && request('tab', 'ledger') === 'ledger' ? 'active-nav' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 01-2-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <span class="sidebar-text">Invoices Ledger</span>
                </a>
                <a href="{{ route('invoices', ['tab' => 'challan-converter']) }}" class="nav-link-item flex items-center space-x-3 px-4 py-2 rounded-xl text-sm font-semibold transition duration-150 {{ Route::is('invoices') && request('tab') === 'challan-converter' ? 'active-nav' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                    <span class="sidebar-text">Convert Challans</span>
                </a>
                <a href="{{ route('invoices', ['tab' => 'manual-builder']) }}" class="nav-link-item flex items-center space-x-3 px-4 py-2 rounded-xl text-sm font-semibold transition duration-150 {{ Route::is('invoices') && request('tab') === 'manual-builder' ? 'active-nav' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="sidebar-text">Manual Invoice Builder</span>
                </a>
                <a href="{{ route('employees') }}" class="nav-link-item flex items-center space-x-3 px-4 py-2 rounded-xl text-sm font-semibold transition duration-150 {{ Route::is('employees') ? 'active-nav' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    <span class="sidebar-text">Employees</span>
                </a>
                <a href="{{ route('expenses') }}" class="nav-link-item flex items-center space-x-3 px-4 py-2 rounded-xl text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition duration-150 {{ Route::is('expenses') ? 'active-nav' : '' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                    <span class="sidebar-text">Expenses Ledger</span>
                </a>
            </div>

            <!-- Reporting Section -->
            <div class="space-y-1">
                <span class="sidebar-category-header px-4 text-[9px] font-bold text-slate-400 uppercase tracking-wider block">Audits</span>
                <a href="{{ route('reports') }}" class="nav-link-item flex items-center space-x-3 px-4 py-2 rounded-xl text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition duration-150 {{ Route::is('reports') ? 'active-nav' : '' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 01-2-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <span class="sidebar-text">Reports & Export</span>
                </a>
            </div>
        </nav>

        <!-- Sidebar User Profile & Logout -->
        <div class="sidebar-footer p-4 border-t border-slate-100 bg-slate-50/50 flex flex-col space-y-2 flex-shrink-0">
            <a href="{{ route('profile') }}" class="flex items-center justify-between hover:bg-slate-100/80 p-2 rounded-xl transition duration-150 group">
                <div class="flex items-center space-x-3 min-w-0">
                    <div class="w-9 h-9 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold uppercase group-hover:bg-blue-200 transition flex-shrink-0">
                        {{ substr(Auth::user()->name ?? 'P', 0, 1) }}
                    </div>
                    <div class="sidebar-profile-detail flex flex-col min-w-0">
                        <span class="text-sm font-bold text-slate-800 truncate leading-none group-hover:text-blue-700 transition">{{ Auth::user()->name ?? 'Praful Patel' }}</span>
                        <span class="text-[10px] font-semibold text-slate-400 capitalize mt-1">{{ Auth::user()->role ?? 'Staff' }}</span>
                    </div>
                </div>
                <div class="sidebar-profile-detail text-slate-400 group-hover:text-blue-600 transition flex-shrink-0">
                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                </div>
            </a>
            <a href="{{ route('logout') }}" class="sidebar-logout-btn w-full flex items-center justify-center space-x-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-rose-600 hover:bg-rose-50 border border-transparent hover:border-rose-100 transition duration-150">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                    <polyline points="16 17 21 12 16 7"></polyline>
                    <line x1="21" y1="12" x2="9" y2="12"></line>
                </svg>
                <span class="sidebar-text">Logout</span>
            </a>
        </div>
    </aside>

    <!-- Floating Sidebar Toggle Button -->
    <button id="sidebarToggle" class="fixed top-4 left-4 z-40 bg-white hover:bg-slate-50 text-slate-600 hover:text-slate-900 p-2.5 rounded-xl border border-slate-200 shadow-sm transition-all duration-200 focus:outline-none md:hidden">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
        </svg>
    </button>

    <!-- Main Content Pane Wrapper -->
    <div id="main-content" class="flex-grow pl-0 flex flex-col min-h-screen transition-all duration-300">
        <!-- Toast Notification Area -->
        <div id="globalToast" class="fixed top-5 right-5 z-50 transform translate-y-[-100px] opacity-0 transition-all duration-300 pointer-events-none">
            <div class="bg-white border shadow-xl rounded-xl p-4 flex items-center space-x-3 max-w-sm">
                <div id="toastIcon" class="w-8 h-8 rounded-full flex items-center justify-center"></div>
                <div class="flex-grow">
                    <p id="toastMessage" class="text-sm font-semibold text-slate-800"></p>
                </div>
            </div>
        </div>

        <div class="p-4 md:p-8 flex-grow space-y-6">
            @yield('content')
        </div>
    </div>

    <!-- AJAX Submission Scripts -->
    <script>
        document.addEventListener('submit', async function(e) {
            const form = e.target.closest('.ajax-form');
            if (!form) return;
            
            e.preventDefault();
            
            const submitBtn = form.querySelector('button[type="submit"]');
            const alertBox = form.querySelector('.form-alert') || createFormAlert(form);
            const originalBtnHtml = submitBtn.innerHTML;
            
            // Set loading state
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-75');
            submitBtn.innerHTML = `
                <svg class="animate-spin h-5 w-5 mr-2 text-white inline" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Processing...</span>
            `;
            
            alertBox.className = 'hidden';
            
            const formData = new FormData(form);
            const dataObj = {};
            formData.forEach((value, key) => {
                if (key.endsWith('[]')) {
                    const cleanKey = key.slice(0, -2);
                    if (!dataObj[cleanKey]) dataObj[cleanKey] = [];
                    dataObj[cleanKey].push(value);
                } else if (key.startsWith('labor[')) {
                    // Extract staff ID from name, e.g. labor[3]
                    const staffId = key.match(/\[(.*?)\]/)[1];
                    if (!dataObj['labor']) dataObj['labor'] = {};
                    dataObj['labor'][staffId] = value;
                } else {
                    dataObj[key] = value;
                }
            });
            
            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(dataObj)
                });
                
                const responseData = await response.json();
                
                if (response.ok) {
                    showToast('success', responseData.message || 'Operation completed successfully!');
                    
                    if (!form.classList.contains('no-reset')) {
                        form.reset();
                    }
                    
                    // Reload dynamic elements or reload page smoothly
                    setTimeout(() => {
                        window.location.reload();
                    }, 800);
                } else {
                    const errors = responseData.errors 
                        ? (Array.isArray(responseData.errors) ? responseData.errors : Object.values(responseData.errors).flat())
                        : [responseData.message || 'Validation error. Please verify input.'];
                    
                    displayFormErrors(alertBox, errors);
                    resetSubmitButton(submitBtn, originalBtnHtml);
                }
            } catch (err) {
                console.error(err);
                displayFormErrors(alertBox, ['A system network failure occurred. Please try again.']);
                resetSubmitButton(submitBtn, originalBtnHtml);
            }
        });
        
        function createFormAlert(form) {
            const div = document.createElement('div');
            div.className = 'form-alert hidden text-sm p-4 rounded-xl border mb-4';
            form.insertBefore(div, form.firstChild);
            return div;
        }
        
        function displayFormErrors(alertBox, errors) {
            alertBox.className = 'form-alert bg-rose-50 border-rose-200 text-rose-800 p-4 rounded-xl border text-xs mb-4';
            let list = '<ul class="list-disc list-inside space-y-0.5">';
            errors.forEach(err => {
                list += `<li>${err}</li>`;
            });
            list += '</ul>';
            alertBox.innerHTML = `<strong>Submission Failed:</strong> ${list}`;
        }
        
        function resetSubmitButton(btn, originalHtml) {
            btn.disabled = false;
            btn.classList.remove('opacity-75');
            btn.innerHTML = originalHtml;
        }
        
        function showToast(type, message) {
            const toast = document.getElementById('globalToast');
            const icon = document.getElementById('toastIcon');
            const msgText = document.getElementById('toastMessage');
            
            msgText.innerText = message;
            
            if (type === 'success') {
                icon.className = 'w-8 h-8 rounded-full flex items-center justify-center bg-emerald-100 text-emerald-600';
                icon.innerHTML = `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>`;
            } else {
                icon.className = 'w-8 h-8 rounded-full flex items-center justify-center bg-rose-100 text-rose-600';
                icon.innerHTML = `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>`;
            }
            
            toast.classList.remove('translate-y-[-100px]', 'opacity-0');
            toast.classList.add('translate-y-0', 'opacity-100');
            
            setTimeout(() => {
                toast.classList.remove('translate-y-0', 'opacity-100');
                toast.classList.add('translate-y-[-100px]', 'opacity-0');
            }, 3000);
        }

        // Sidebar Toggle Logic
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('main-content');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebarPinToggle = document.getElementById('sidebarPinToggle');
        const sidebarPinDot = document.getElementById('sidebarPinDot');

        function isDesktop() {
            return window.innerWidth >= 768;
        }

        // Apply visual states without side-effects on localStorage
        function applySidebarState(pinned) {
            if (isDesktop()) {
                if (pinned) {
                    sidebar.classList.remove('sidebar-collapsed');
                    sidebar.classList.remove('-translate-x-full');
                    sidebar.classList.add('translate-x-0');
                    mainContent.classList.add('pl-64');
                    mainContent.classList.remove('pl-[72px]', 'pl-0');
                    if (sidebarPinDot) {
                        sidebarPinDot.classList.remove('bg-transparent', 'scale-0');
                        sidebarPinDot.classList.add('bg-blue-500', 'scale-100');
                    }
                } else {
                    sidebar.classList.add('sidebar-collapsed');
                    sidebar.classList.remove('-translate-x-full');
                    sidebar.classList.add('translate-x-0');
                    mainContent.classList.add('pl-[72px]');
                    mainContent.classList.remove('pl-64', 'pl-0');
                    if (sidebarPinDot) {
                        sidebarPinDot.classList.remove('bg-blue-500', 'scale-100');
                        sidebarPinDot.classList.add('bg-transparent', 'scale-0');
                    }
                }
                sidebarToggle.classList.add('hidden');
            } else {
                // Mobile layout: always start with sidebar hidden offscreen
                sidebar.classList.add('sidebar-collapsed');
                sidebar.classList.add('-translate-x-full');
                sidebar.classList.remove('translate-x-0');
                mainContent.classList.add('pl-0');
                mainContent.classList.remove('pl-64', 'pl-[72px]');
                sidebarToggle.classList.remove('hidden');
            }
        }

        // Handle sidebar open on mobile (via mobile toggle button)
        function openMobileSidebar() {
            sidebar.classList.remove('-translate-x-full');
            sidebar.classList.add('translate-x-0');
            sidebarToggle.classList.add('hidden');
        }

        // Initialize state
        const isPinned = localStorage.getItem('sidebar_pinned') !== 'false'; // Default to true (pinned)
        applySidebarState(isPinned);

        // Pin toggle click event
        if (sidebarPinToggle) {
            sidebarPinToggle.addEventListener('click', (e) => {
                e.stopPropagation();
                const currentPinned = localStorage.getItem('sidebar_pinned') !== 'false';
                const newPinned = !currentPinned;
                localStorage.setItem('sidebar_pinned', newPinned ? 'true' : 'false');
                applySidebarState(newPinned);
            });
        }

        // Mobile toggle button click
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', (e) => {
                e.stopPropagation();
                openMobileSidebar();
            });
        }

        // Close sidebar on option select (for mobile)
        const navLinks = sidebar.querySelectorAll('.nav-link-item, .sidebar-logout-btn, .sidebar-footer a');
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (!isDesktop()) {
                    sidebar.classList.add('-translate-x-full');
                    sidebar.classList.remove('translate-x-0');
                    sidebarToggle.classList.remove('hidden');
                }
            });
        });

        // Click outside to close on mobile
        document.addEventListener('click', (e) => {
            if (!isDesktop() && sidebar && !sidebar.contains(e.target) && sidebarToggle && !sidebarToggle.contains(e.target)) {
                sidebar.classList.add('-translate-x-full');
                sidebar.classList.remove('translate-x-0');
                sidebarToggle.classList.remove('hidden');
            }
        });

        // Resize handler
        window.addEventListener('resize', () => {
            const currentPinned = localStorage.getItem('sidebar_pinned') !== 'false';
            applySidebarState(currentPinned);
        });
    </script>
</body>
</html>
