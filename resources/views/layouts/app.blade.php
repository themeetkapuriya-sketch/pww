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
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
        }
        .theme-blue { color: #1E73BE !important; }
        .text-theme-blue { color: #1E73BE !important; }
        .bg-theme-blue { background-color: #1E73BE !important; }
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
    <script src="{{ asset('js/app-core.js') }}"></script>
</body>
</html>
