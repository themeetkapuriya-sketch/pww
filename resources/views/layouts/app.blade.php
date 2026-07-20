<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'PWW ERP') - Praful Welding Works</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'theme-blue': '#1E73BE',
                    }
                }
            }
        }
    </script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <style>
        body, html {
            font-family: 'Outfit', sans-serif;
            background-color: #f1f5f9 !important;
            color: #1e293b;
        }
        
        /* Unified Global Color System */
        .theme-blue, .text-theme-blue { color: #5287f7 !important; }
        .bg-theme-blue { background-color: #5287f7 !important; }
        .active-nav {
            background-color: #eff6ff !important;
            color: #5287f7 !important;
            font-weight: 700 !important;
        }
        .active-nav svg, .active-nav span {
            color: #5287f7 !important;
        }

        /* Unified Button Classes */
        .btn-primary {
            background-color: #5287f7 !important;
            color: #ffffff !important;
            font-weight: 600 !important;
            border-radius: 0.75rem !important;
            transition: all 0.15s ease !important;
            box-shadow: 0 4px 12px rgba(82, 135, 247, 0.25) !important;
            border: none !important;
        }
        .btn-primary:hover {
            background-color: #4075e6 !important;
            opacity: 0.95 !important;
            transform: translateY(-1px) !important;
            box-shadow: 0 6px 16px rgba(82, 135, 247, 0.35) !important;
        }

        /* Unified Badge Classes */
        .badge-success {
            background-color: #ecfdf5 !important;
            color: #047857 !important;
            border: 1px solid #a7f3d0 !important;
        }
        .badge-warning {
            background-color: #fffbeb !important;
            color: #b45309 !important;
            border: 1px solid #fde68a !important;
        }
        .badge-danger {
            background-color: #fff1f2 !important;
            color: #be123c !important;
            border: 1px solid #fecdd3 !important;
        }
        .badge-info {
            background-color: #eff6ff !important;
            color: #1d4ed8 !important;
            border: 1px solid #bfdbfe !important;
        }

        /* Hide scrollbar for the sidebar navigation */
        #sidebar nav::-webkit-scrollbar {
            display: none;
        }
        #sidebar nav {
            -ms-overflow-style: none;  /* IE and Edge */
            scrollbar-width: none;  /* Firefox */
        }

        /* Frest Theme Collapsible Sidebar System */
        #sidebar {
            transition: width 0.22s cubic-bezier(0.4, 0, 0.2, 1), transform 0.22s ease, box-shadow 0.22s ease !important;
        }
        #main-content {
            transition: padding-left 0.22s cubic-bezier(0.4, 0, 0.2, 1) !important;
        }

        /* Desktop Viewport Rules (>= 768px) */
        @media (min-width: 768px) {
            /* Image 3: Collapsed Unpinned State (70px icon strip) */
            #sidebar.sidebar-collapsed {
                width: 70px !important;
                overflow-x: hidden !important;
            }
            #sidebar.sidebar-collapsed .sidebar-header-text,
            #sidebar.sidebar-collapsed .sidebar-text,
            #sidebar.sidebar-collapsed .sidebar-category-header,
            #sidebar.sidebar-collapsed .sidebar-profile-detail,
            #sidebar.sidebar-collapsed .sidebar-chevron,
            #sidebar.sidebar-collapsed .sidebar-submenu {
                display: none !important;
            }
            #sidebar.sidebar-collapsed .sidebar-header {
                padding-left: 0.75rem !important;
                padding-right: 0.75rem !important;
                justify-content: space-between !important;
            }
            #sidebar.sidebar-collapsed .nav-link-item {
                justify-content: center !important;
                padding-left: 0 !important;
                padding-right: 0 !important;
            }
            #sidebar.sidebar-collapsed .nav-link-item svg {
                margin: 0 !important;
            }
            #sidebar.sidebar-collapsed .sidebar-divider {
                display: block !important;
                margin: 0.5rem auto !important;
                width: 32px !important;
                border-top: 1px solid #e2e8f0 !important;
            }
            #sidebar.sidebar-collapsed .sidebar-footer {
                padding-left: 0.5rem !important;
                padding-right: 0.5rem !important;
                align-items: center !important;
            }

            /* Image 2: Hovered Unpinned State (Floats expanding to 260px) */
            #sidebar.sidebar-collapsed:hover {
                width: 256px !important;
                box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.12), 0 8px 10px -6px rgba(0, 0, 0, 0.04) !important;
                z-index: 50 !important;
            }
            #sidebar.sidebar-collapsed:hover .sidebar-header-text {
                display: flex !important;
            }
            #sidebar.sidebar-collapsed:hover .sidebar-text {
                display: inline-block !important;
                opacity: 1 !important;
                margin-left: 0.75rem !important;
                visibility: visible !important;
            }
            #sidebar.sidebar-collapsed:hover .sidebar-category-header {
                display: block !important;
                visibility: visible !important;
                opacity: 1 !important;
                margin-top: 10px !important;
                margin-bottom: 4px !important;
            }
            #sidebar.sidebar-collapsed:hover .sidebar-profile-detail {
                display: flex !important;
            }
            #sidebar.sidebar-collapsed:hover .sidebar-chevron {
                display: block !important;
            }
            #sidebar.sidebar-collapsed:hover .nav-link-item {
                justify-content: flex-start !important;
                padding-left: 1rem !important;
                padding-right: 1rem !important;
            }
            #sidebar.sidebar-collapsed:hover .nav-link-item svg {
                margin-right: 0.75rem !important;
            }
            #sidebar.sidebar-collapsed:hover .sidebar-divider {
                display: none !important;
            }
            #sidebar.sidebar-collapsed:hover .sidebar-header {
                padding-left: 1rem !important;
                padding-right: 1rem !important;
            }
            #sidebar.sidebar-collapsed:hover .sidebar-footer {
                padding-left: 1rem !important;
                padding-right: 1rem !important;
                align-items: stretch !important;
            }
        }

        /* Custom DataTables Styling (Financial Ledger Design) */
        .dataTables_wrapper,
        .dataTables_wrapper *,
        table.dataTable,
        table.dataTable * {
            font-family: 'Outfit', sans-serif !important;
        }
        .dataTables_wrapper {
            padding: 0 !important;
        }
        .dataTables_wrapper .dataTables_length {
            margin-bottom: 1rem;
            color: #64748b !important;
            font-size: 0.875rem;
            font-weight: 500;
            float: left;
        }
        .dataTables_wrapper .dataTables_length select {
            padding: 0.35rem 1.75rem 0.35rem 0.75rem !important;
            border-radius: 0.5rem !important;
            border: 1px solid #cbd5e1 !important;
            background-color: #ffffff !important;
            color: #334155 !important;
            font-weight: 600;
            outline: none !important;
        }
        .dataTables_wrapper .dataTables_filter {
            margin-bottom: 1rem;
            color: #64748b !important;
            font-size: 0.875rem;
            font-weight: 500;
            float: right;
        }
        .dataTables_wrapper .dataTables_filter input {
            padding: 0.4rem 0.85rem !important;
            border-radius: 0.5rem !important;
            border: 1px solid #cbd5e1 !important;
            background-color: #ffffff !important;
            color: #1e293b !important;
            outline: none !important;
            font-size: 0.875rem;
            margin-left: 0.5rem !important;
        }
        .dataTables_wrapper .dataTables_filter input:focus {
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2) !important;
        }

        /* Table Headers & Rows */
        table.dataTable {
            width: 100% !important;
            border-collapse: collapse !important;
            margin-top: 0 !important;
            margin-bottom: 0 !important;
            border-radius: 0.75rem !important;
            overflow: hidden !important;
        }
        table.dataTable thead th {
            background-color: #5287f7 !important; /* Swatch Matching Blue Header */
            color: #ffffff !important;
            font-size: 0.75rem !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.05em !important;
            padding: 0.85rem 1rem !important;
            border-bottom: none !important;
            border-right: 1px solid rgba(255, 255, 255, 0.25) !important; /* Vertical Line Separators */
        }
        table.dataTable thead th:last-child {
            border-right: none !important;
        }
        table.dataTable thead .sorting,
        table.dataTable thead .sorting_asc,
        table.dataTable thead .sorting_desc {
            color: #ffffff !important;
        }
        table.dataTable tbody td {
            padding: 0.9rem 1rem !important;
            border-bottom: 1px solid #f1f5f9 !important;
            border-right: 1px solid #e2e8f0 !important; /* Vertical Line for Record Columns */
            color: #334155;
            vertical-align: middle;
        }
        table.dataTable tbody td:last-child {
            border-right: none !important;
        }
        table.dataTable tbody tr:hover {
            background-color: #f8fafc !important;
        }
        table.dataTable.no-footer {
            border-bottom: 1px solid #e2e8f0 !important;
        }

        /* Footer & Pagination */
        .dataTables_wrapper .dataTables_info {
            padding-top: 1rem !important;
            color: #64748b !important;
            font-size: 0.75rem !important;
            font-weight: 500;
            float: left;
        }
        .dataTables_wrapper .dataTables_paginate {
            padding-top: 0.85rem !important;
            float: right;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            border-radius: 0.5rem !important;
            padding: 0.35rem 0.75rem !important;
            font-size: 0.875rem !important;
            font-weight: 600 !important;
            color: #475569 !important;
            border: 1px solid #e2e8f0 !important;
            background: #ffffff !important;
            margin-left: 0.25rem !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
            background: #5287f7 !important;
            color: #ffffff !important;
            border-color: #5287f7 !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #f1f5f9 !important;
            color: #1e293b !important;
            border-color: #cbd5e1 !important;
        }

        /* Sidebar Width & Transitions (Gemini Light Style) */
        #sidebar {
            transition: width 0.2s cubic-bezier(0.4, 0, 0.2, 1), transform 0.2s ease, box-shadow 0.2s ease;
        }
        #main-content {
            transition: padding-left 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .sidebar-text, .sidebar-header-text, .sidebar-category-header, .sidebar-profile-detail {
            transition: opacity 0.15s ease;
        }

        /* Tooltip styles (hidden by default) */
        .sidebar-tooltip {
            display: none;
        }

        /* Collapsed State Styles (Desktop >= 768px) */
        @media (min-width: 768px) {
            #sidebar.sidebar-collapsed,
            #sidebar.sidebar-collapsed nav,
            #sidebar.sidebar-collapsed .nav-section {
                overflow: visible !important;
            }

            #sidebar.sidebar-collapsed {
                width: 64px; /* Mini rail width like Gemini */
            }
            #sidebar.sidebar-collapsed .sidebar-header {
                padding-left: 0.5rem;
                padding-right: 0.5rem;
                justify-content: center;
            }
            #sidebar.sidebar-collapsed .sidebar-brand-details {
                display: none;
            }
            #sidebar.sidebar-collapsed .sidebar-text,
            #sidebar.sidebar-collapsed .sidebar-profile-detail {
                display: none !important;
            }
            #sidebar.sidebar-collapsed .sidebar-category-header {
                display: none !important;
            }
            #sidebar.sidebar-collapsed .nav-section {
                padding-top: 0.25rem;
                border-top: 1px solid #f1f5f9;
                margin-top: 0.25rem;
            }
            #sidebar.sidebar-collapsed .nav-link-item,
            #sidebar.sidebar-collapsed .sidebar-logout-btn {
                justify-content: center;
                padding-left: 0;
                padding-right: 0;
            }
            #sidebar.sidebar-collapsed .nav-link-item svg,
            #sidebar.sidebar-collapsed .sidebar-logout-btn svg {
                margin: 0 !important;
            }
            #sidebar.sidebar-collapsed .sidebar-footer {
                padding-left: 0.5rem;
                padding-right: 0.5rem;
                align-items: center;
            }

            /* Floating Light Pill Tooltips on Hover (Exact Gemini Style) */
            #sidebar.sidebar-collapsed .sidebar-tooltip {
                display: block !important;
                position: absolute;
                left: 64px;
                top: 50%;
                transform: translateY(-50%);
                background-color: #e2e8f0; /* Soft light gray pill background as in Image 2 */
                color: #0f172a; /* Dark text */
                font-size: 12px;
                font-weight: 600;
                padding: 6px 14px;
                border-radius: 9999px; /* Rounded pill shape */
                white-space: nowrap;
                opacity: 0;
                visibility: hidden;
                pointer-events: none;
                transition: opacity 0.15s ease, visibility 0.15s ease, transform 0.15s ease;
                box-shadow: 0 4px 14px rgba(0, 0, 0, 0.12);
                z-index: 9999 !important;
            }
            #sidebar.sidebar-collapsed .group:hover .sidebar-tooltip,
            #sidebar.sidebar-collapsed a:hover .sidebar-tooltip {
                opacity: 1 !important;
                visibility: visible !important;
                transform: translateY(-50%) translateX(6px);
            }
        }
    </style>
</head>
<body class="min-h-screen md:flex bg-[#f1f5f9]">

    <!-- Sidebar Navigation -->
    @include('layouts.sidebar')

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

        <div id="page-content" class="p-4 md:p-8 flex-grow space-y-6">
            @yield('content')
        </div>
    </div>

    <!-- Core Application SPA & Sidebar Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="{{ asset('js/app-core.js') }}"></script>
</body>
</html>
