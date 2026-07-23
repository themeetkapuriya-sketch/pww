@if(request()->ajax() && !request()->pjax())
    <title>@yield('title', 'PWW ERP') - Praful Welding Works</title>
    @yield('content')
@else
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
                        'theme-blue': '#4371D7',
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
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
        }
        .theme-blue { color: #4371D7 !important; }
        .text-theme-blue { color: #4371D7 !important; }
        .bg-theme-blue { background-color: #4371D7 !important; }

        /* DataTables Custom Tailwind Integration Styles */
        .dataTables_wrapper {
            padding-top: 0.5rem;
            padding-bottom: 0.5rem;
        }
        .dataTables_wrapper .dataTables_length {
            margin-bottom: 1rem;
            font-size: 0.75rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
        }
        .dataTables_wrapper .dataTables_length select {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 0.5rem;
            padding: 0.35rem 0.75rem;
            font-size: 0.875rem;
            color: #334155;
            outline: none;
            margin: 0 0.5rem;
        }
        .dataTables_wrapper .dataTables_filter {
            margin-bottom: 1rem;
            font-size: 0.75rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
        }
        .dataTables_wrapper .dataTables_filter input {
            background-color: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 0.75rem;
            padding: 0.4rem 0.85rem;
            font-size: 0.875rem;
            color: #1e293b;
            outline: none;
            margin-left: 0.5rem;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            transition: all 0.15s ease;
        }
        .dataTables_wrapper .dataTables_filter input:focus {
            border-color: #4371D7;
            box-shadow: 0 0 0 3px rgba(67, 113, 215, 0.25);
        }
        .dataTables_wrapper .dataTables_info {
            padding-top: 1rem;
            font-size: 0.75rem;
            font-weight: 600;
            color: #64748b;
        }
        .dataTables_wrapper .dataTables_paginate {
            padding-top: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            border-radius: 0.5rem !important;
            border: 1px solid #e2e8f0 !important;
            background: #ffffff !important;
            color: #475569 !important;
            font-size: 0.75rem !important;
            font-weight: 700 !important;
            padding: 0.35rem 0.75rem !important;
            margin: 0 2px !important;
            transition: all 0.15s ease !important;
            cursor: pointer !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #f1f5f9 !important;
            color: #0f172a !important;
            border-color: #cbd5e1 !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
            background: #4371D7 !important;
            color: #ffffff !important;
            border-color: #4371D7 !important;
            box-shadow: 0 2px 4px -1px rgba(67, 113, 215, 0.3) !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover {
            opacity: 0.4 !important;
            cursor: not-allowed !important;
            background: #f8fafc !important;
        }

        /* DataTables Table Header Theme Styling */
        table.erp-datatable thead,
        table.erp-datatable thead tr,
        table.erp-datatable thead th,
        table.dataTable thead th {
            background-color: #4371D7 !important;
            color: #ffffff !important;
        }

        /* Vertical & Outer Border Lines for all tables (Start to End) */
        table.erp-datatable,
        table.dataTable {
            border: 1px solid #cbd5e1 !important;
            border-radius: 0.75rem !important;
            border-collapse: separate !important;
            border-spacing: 0 !important;
            overflow: hidden !important;
        }

        table.erp-datatable th,
        table.erp-datatable td,
        table.dataTable th,
        table.dataTable td {
            border-right: 1px solid #e2e8f0 !important;
            border-bottom: 1px solid #e2e8f0 !important;
        }
        table.erp-datatable th:first-child,
        table.erp-datatable td:first-child,
        table.dataTable th:first-child,
        table.dataTable td:first-child {
            border-left: 1px solid #cbd5e1 !important;
        }
        table.erp-datatable th:last-child,
        table.erp-datatable td:last-child,
        table.dataTable th:last-child,
        table.dataTable td:last-child {
            border-right: 1px solid #cbd5e1 !important;
        }

        table.erp-datatable thead th,
        table.dataTable thead th {
            border-right: 1px solid rgba(255, 255, 255, 0.25) !important;
            border-bottom: none !important;
        }
        table.erp-datatable thead th:first-child,
        table.dataTable thead th:first-child {
            border-left: 1px solid #4371D7 !important;
        }
        table.erp-datatable thead th:last-child,
        table.dataTable thead th:last-child {
            border-right: 1px solid #4371D7 !important;
        }
        
        /* Global Button Utility Styles */
        .btn-primary {
            background-color: #4371D7 !important;
            color: #ffffff !important;
            border-radius: 0.75rem !important;
            box-shadow: 0 4px 6px -1px rgba(67, 113, 215, 0.25), 0 2px 4px -2px rgba(67, 113, 215, 0.1) !important;
            transition: all 0.15s ease-in-out !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            border: 1px solid transparent !important;
            cursor: pointer !important;
        }
        .btn-primary:hover {
            background-color: #365ebd !important;
            color: #ffffff !important;
            box-shadow: 0 10px 15px -3px rgba(67, 113, 215, 0.35) !important;
            transform: translateY(-1px) !important;
        }
        .btn-primary:active {
            transform: translateY(0) !important;
            box-shadow: 0 2px 4px -1px rgba(82, 135, 247, 0.2) !important;
        }

        .btn-secondary {
            background-color: #f1f5f9 !important;
            color: #475569 !important;
            border-radius: 0.75rem !important;
            border: 1px solid #cbd5e1 !important;
            transition: all 0.15s ease-in-out !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            cursor: pointer !important;
        }
        .btn-secondary:hover {
            background-color: #e2e8f0 !important;
            color: #0f172a !important;
        }

        /* Project-wide Modal Backdrop Full Viewport Rules */
        [id*="Modal"], [id*="modal"] {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
            margin: 0 !important;
            z-index: 999999 !important;
            backdrop-filter: blur(2.5px) !important;
            -webkit-backdrop-filter: blur(2.5px) !important;
            background-color: rgba(15, 23, 42, 0.35) !important;
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
                display: flex;
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
    @include('layouts.sidebar')

    <!-- Floating Sidebar Toggle Button -->
    <button id="sidebarToggle" class="fixed top-4 left-4 z-40 bg-white hover:bg-slate-50 text-slate-600 hover:text-slate-900 p-2.5 rounded-xl border border-slate-200 shadow-sm transition-all duration-200 focus:outline-none md:hidden">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
        </svg>
    </button>

    <!-- Main Content Pane Wrapper -->
    <div id="main-content" class="flex-grow pl-0 flex flex-col min-h-screen transition-all duration-300">
        <!-- Header displaying Page Name and Today's Date -->
        @include('layouts.header')

        <!-- Toast Notification Area -->
        <div id="globalToast" class="fixed top-5 right-5 z-50 transform translate-y-[-100px] opacity-0 transition-all duration-300 pointer-events-none">
            <div class="bg-white border shadow-xl rounded-xl p-4 flex items-center space-x-3 max-w-sm">
                <div id="toastIcon" class="w-8 h-8 rounded-full flex items-center justify-center"></div>
                <div class="flex-grow">
                    <p id="toastMessage" class="text-sm font-semibold text-slate-800"></p>
                </div>
            </div>
        </div>

        <div id="page-content" class="p-4 md:px-8 md:pt-4 md:pb-8 flex-grow space-y-6">
            @yield('content')
        </div>

        <!-- Master Footer Partial -->
        @include('layouts.footer')
    </div>

    <!-- Core Application SPA & Sidebar Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="{{ asset('js/app-core.js') }}"></script>
    @stack('modals')
</body>
</html>
@endif
