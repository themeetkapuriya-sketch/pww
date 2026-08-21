@if(request()->header('X-PWW-SPA') === '1')
    <script>
        // Self-Healing Guard: If browser ever renders this partial without the main application layout shell, reload full page immediately
        if (typeof window.loadPage !== 'function' && window.location.pathname !== '/login') {
            window.location.replace(window.location.href);
        }
    </script>
    <title>@yield('title', 'PWW ERP') - Praful Welding Works</title>
    <div id="spa-header-content" class="hidden">
        @include('layouts.header')
    </div>
    <div id="page-content" class="p-4 md:px-8 md:pt-4 md:pb-8 flex-grow space-y-6">
        @yield('content')
        @if(session('auto_download_backup_url'))
            <script>
                (function() {
                    setTimeout(function() {
                        const a = document.createElement('a');
                        a.href = "{{ session('auto_download_backup_url') }}";
                        a.download = '';
                        document.body.appendChild(a);
                        a.click();
                        document.body.removeChild(a);
                        if (window.showToast) {
                            window.showToast('success', '📦 Backup file automatically downloaded to your local Downloads folder!');
                        }
                    }, 400);
                })();
            </script>
        @endif
    </div>
@else
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'PWW ERP') - Praful Welding Works</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>
        window.AppConfig = Object.freeze({
            baseUrl: "{{ url('/') }}",
            csrfToken: "{{ csrf_token() }}",
            sessionTimeoutMinutes: {{ (int) \App\Models\Setting::get('session_timeout_minutes', '120') }},
            routes: {
                toggleUserStatus: "{{ url('/settings/users/:id/toggle-status') }}",
                deleteUser: "{{ url('/settings/users/:id') }}",
                toggleRoleStatus: "{{ url('/settings/roles/:slug/toggle-status') }}",
                deleteRole: "{{ url('/settings/roles/:key') }}",
                toggleRolePermission: "{{ route('settings.roles.toggle-permission') }}",
            }
        });
    </script>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <!-- Local Fonts & Styles -->
    <link rel="stylesheet" href="{{ asset('fonts/outfit/outfit.css') }}">
    <!-- Local Compiled Vite Tailwind CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Local Tailwind Engine -->
    <script src="{{ asset('vendor/tailwindcss.js') }}"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        'theme-blue': '#4371D7',
                    }
                }
            }
        }
    </script>
    <!-- Local Vendor CSS Files (100% Offline Support) -->
    <link rel="stylesheet" href="{{ asset('vendor/sweetalert2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/jquery.dataTables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/tom-select.min.css') }}">
    <script>
        if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    <style>
        /* Global Bold Text for all Form Input Boxes across all pages */
        input,
        select,
        textarea,
        .combobox-search-input,
        .ts-control,
        .ts-control input,
        .ts-control .item {
            font-weight: 700 !important;
        }

        /* Dark Mode Comprehensive UI Styling Rules */
        html.dark {
            color-scheme: dark;
            background-color: #0f172a !important;
        }

        html.dark body {
            background-color: #0f172a !important;
            color: #f8fafc !important;
        }

        /* Headings & Text Colors */
        html.dark h1, html.dark h2, html.dark h3, html.dark h4, html.dark h5, html.dark h6,
        html.dark .text-slate-900, html.dark .text-slate-800, html.dark .text-slate-700,
        html.dark .text-gray-900, html.dark .text-gray-800, html.dark .text-gray-700 {
            color: #f8fafc !important;
        }

        html.dark .text-slate-600, html.dark .text-slate-500,
        html.dark .text-gray-600, html.dark .text-gray-500 {
            color: #cbd5e1 !important;
        }

        html.dark .text-slate-400, html.dark .text-gray-400 {
            color: #94a3b8 !important;
        }

        /* Containers, Header, Sidebar & Cards */
        html.dark header,
        html.dark aside#sidebar,
        html.dark .bg-white,
        html.dark .bg-slate-50,
        html.dark .bg-slate-100,
        html.dark .bg-slate-200,
        html.dark .bg-gray-50,
        html.dark .bg-gray-100,
        html.dark .bg-gray-200,
        html.dark [class*="bg-slate-50"],
        html.dark [class*="bg-slate-100"] {
            background-color: #1e293b !important;
            border-color: #334155 !important;
            color: #f8fafc !important;
        }

        /* Form Controls, Inputs, Selects, Textareas */
        html.dark input,
        html.dark select,
        html.dark textarea,
        html.dark .combobox-search-input,
        html.dark .ts-control,
        html.dark .ts-control input {
            background-color: #334155 !important;
            color: #f8fafc !important;
            border-color: #475569 !important;
        }

        html.dark input::placeholder,
        html.dark select::placeholder,
        html.dark textarea::placeholder {
            color: #94a3b8 !important;
        }

        /* Dropdowns & TomSelect Options */
        html.dark .ts-dropdown,
        html.dark .ts-dropdown .ts-dropdown-content,
        html.dark .combobox-dropdown {
            background-color: #1e293b !important;
            color: #f8fafc !important;
            border-color: #475569 !important;
        }

        html.dark .ts-dropdown .option,
        html.dark .combobox-option {
            color: #e2e8f0 !important;
        }

        html.dark .ts-dropdown .option.active,
        html.dark .ts-dropdown .option:hover,
        html.dark .combobox-option:hover,
        html.dark .other-opt-btn:hover,
        html.dark button.hover\:bg-blue-50:hover,
        html.dark a.hover\:bg-blue-50:hover,
        html.dark [class*="hover:bg-blue-50"]:hover {
            background-color: #334155 !important;
            color: #60a5fa !important;
        }

        html.dark .other-opt-btn.bg-blue-50 {
            background-color: #1e3a8a !important;
            color: #93c5fd !important;
        }

        /* Cancel, Close, and Secondary Buttons */
        html.dark button.bg-slate-100,
        html.dark button.bg-slate-200,
        html.dark button.hover\:bg-slate-100:hover,
        html.dark button.hover\:bg-slate-200:hover,
        html.dark .btn-secondary {
            background-color: #334155 !important;
            color: #f8fafc !important;
            border-color: #475569 !important;
        }

        html.dark button.bg-slate-100:hover,
        html.dark button.bg-slate-200:hover {
            background-color: #475569 !important;
            color: #ffffff !important;
        }

        /* Badges & Pills (Vehicle Numbers, Badges, etc.) */
        html.dark .bg-slate-100,
        html.dark .bg-slate-200,
        html.dark .bg-blue-50,
        html.dark .bg-emerald-50,
        html.dark .bg-indigo-50,
        html.dark .bg-purple-50 {
            background-color: #334155 !important;
            border-color: #475569 !important;
            color: #f8fafc !important;
        }

        html.dark .text-blue-700, html.dark .text-blue-800, html.dark .text-blue-900,
        html.dark .text-indigo-700, html.dark .text-indigo-800, html.dark .text-indigo-900 {
            color: #93c5fd !important;
        }

        /* DataTables Full Dark Mode Styling */
        html.dark table.erp-datatable,
        html.dark table.dataTable,
        html.dark table.erp-datatable tbody,
        html.dark table.dataTable tbody,
        html.dark table.erp-datatable tbody tr,
        html.dark table.dataTable tbody tr,
        html.dark table.erp-datatable tbody td,
        html.dark table.dataTable tbody td,
        html.dark table.dataTable.no-footer {
            background-color: #1e293b !important;
            color: #f8fafc !important;
            border-color: #334155 !important;
        }

        html.dark table.erp-datatable tbody tr:hover,
        html.dark table.dataTable tbody tr:hover {
            background-color: #334155 !important;
        }

        html.dark table.erp-datatable thead th,
        html.dark table.dataTable thead th {
            background-color: #0f172a !important;
            color: #cbd5e1 !important;
            border-color: #334155 !important;
        }

        html.dark .dataTables_empty,
        html.dark td.dataTables_empty {
            background-color: #1e293b !important;
            color: #94a3b8 !important;
        }

        /* DataTables Controls: Length Menu Dropdown & Search Input */
        html.dark .dataTables_length select,
        html.dark .dataTables_filter input,
        html.dark .dt-length select,
        html.dark .dt-search input,
        html.dark input[type="search"] {
            background-color: #0f172a !important;
            color: #f8fafc !important;
            border: 1px solid #334155 !important;
        }

        html.dark .dataTables_length label,
        html.dark .dataTables_filter label,
        html.dark .dataTables_info,
        html.dark .dataTables_paginate {
            color: #cbd5e1 !important;
        }

        /* DataTables Pagination Buttons */
        html.dark .dataTables_paginate .paginate_button,
        html.dark .dt-paging .dt-paging-button {
            background-color: #1e293b !important;
            color: #cbd5e1 !important;
            border-color: #334155 !important;
        }

        html.dark .dataTables_paginate .paginate_button.current,
        html.dark .dataTables_paginate .paginate_button.current:hover,
        html.dark .dt-paging .dt-paging-button.current {
            background-color: #2563eb !important;
            color: #ffffff !important;
            border-color: #2563eb !important;
        }

        html.dark .dataTables_paginate .paginate_button:hover,
        html.dark .dt-paging .dt-paging-button:hover {
            background-color: #334155 !important;
            color: #ffffff !important;
        }

        /* SweetAlert2 Comprehensive Dark Mode Styling */
        html.dark .swal2-popup,
        html.dark div.swal2-popup {
            background-color: #1e293b !important;
            color: #f8fafc !important;
            border: 1px solid #334155 !important;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7) !important;
        }

        html.dark .swal2-title,
        html.dark h2.swal2-title,
        html.dark #swal2-title {
            color: #ffffff !important;
        }

        html.dark .swal2-html-container,
        html.dark .swal2-content,
        html.dark #swal2-html-container {
            color: #cbd5e1 !important;
        }

        html.dark .swal2-icon.swal2-warning {
            border-color: #f59e0b !important;
            color: #f59e0b !important;
        }

        html.dark .swal2-icon.swal2-error,
        html.dark .swal2-icon.swal2-danger {
            border-color: #ef4444 !important;
            color: #ef4444 !important;
        }

        html.dark .swal2-icon.swal2-success {
            border-color: #10b981 !important;
            color: #10b981 !important;
        }

        html.dark .swal2-icon.swal2-info {
            border-color: #3b82f6 !important;
            color: #3b82f6 !important;
        }

        html.dark .swal2-validation-message {
            background-color: #0f172a !important;
            color: #f87171 !important;
        }

        .combobox-search-input,
        .ts-control,
        .ts-control input,
        .ts-control .item {
            font-weight: 700 !important;
        }

        /* TomSelect Dropdown Styling: Solid White Background & Floating Layer */
        .ts-dropdown, 
        .ts-dropdown .ts-dropdown-content,
        .ts-dropdown .option,
        .ts-control {
            background-color: #ffffff !important;
            background: #ffffff !important;
        }

        .ts-dropdown {
            z-index: 9999 !important;
            border-radius: 0.75rem !important;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
            border: 1px solid #cbd5e1 !important;
            overflow: hidden !important;
            margin-top: 4px !important;
        }

        .ts-dropdown .option {
            padding: 8px 12px !important;
            font-size: 0.75rem !important;
            color: #334155 !important;
            border-bottom: 1px solid #f1f5f9 !important;
        }

        .ts-dropdown .option:hover,
        .ts-dropdown .option.active {
            background-color: #eff6ff !important;
            color: #1d4ed8 !important;
            font-weight: 700 !important;
        }

        /* TomSelect Single Line Flex Alignment & Side Cursor */
        .ts-control {
            display: flex !important;
            align-items: center !important;
            flex-wrap: nowrap !important;
            border-radius: 0.75rem !important;
            border: 1px solid #cbd5e1 !important;
            padding: 7px 12px !important;
            font-size: 0.875rem !important;
            min-height: 42px !important;
            max-height: 42px !important;
            overflow: hidden !important;
            background-color: #ffffff !important;
        }

        .ts-control > .item {
            display: inline-flex !important;
            align-items: center !important;
            margin: 0 4px 0 0 !important;
            padding: 0 !important;
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            max-width: calc(100% - 25px) !important;
            color: #334155 !important;
            font-weight: 500 !important;
        }

        .ts-control > input {
            display: inline-block !important;
            position: relative !important;
            left: 0 !important;
            opacity: 1 !important;
            flex: 1 1 auto !important;
            min-width: 15px !important;
            margin: 0 !important;
            padding: 0 !important;
            border: none !important;
            background: transparent !important;
            box-shadow: none !important;
            caret-color: #2563eb !important;
            height: auto !important;
            line-height: normal !important;
        }

        /* DataTables Custom Tailwind Integration Styles */
        .dataTables_wrapper {
            width: 100% !important;
            max-width: 100% !important;
            overflow: visible !important;
            padding-top: 0.5rem;
            padding-bottom: 0.5rem;
        }
        .erp-datatable-scroll-container {
            width: 100% !important;
            max-width: 100% !important;
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch !important;
            border-radius: 0.75rem;
        }
        /* DataTables Custom Controls (Searchbar & Show Entries Dropdown) */
        .dataTables_wrapper .dataTables_length,
        div.dataTables_wrapper div.dataTables_length {
            margin-bottom: 0;
            font-size: 0.75rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
        }
        .dataTables_wrapper .dataTables_length label,
        div.dataTables_wrapper div.dataTables_length label {
            display: inline-flex !important;
            align-items: center !important;
            gap: 0.35rem !important;
        }
        .dataTables_wrapper .dataTables_length select,
        div.dataTables_wrapper div.dataTables_length select {
            background-color: #ffffff !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 0.5rem !important;
            padding: 0.35rem 0.75rem !important;
            font-size: 0.875rem !important;
            font-weight: 600 !important;
            color: #334155 !important;
            outline: none !important;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.04) !important;
            cursor: pointer !important;
        }
        .dataTables_wrapper .dataTables_filter,
        div.dataTables_wrapper div.dataTables_filter {
            margin-bottom: 0;
            font-size: 0.75rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
        }
        .dataTables_wrapper .dataTables_filter label,
        div.dataTables_wrapper div.dataTables_filter label {
            display: inline-flex !important;
            align-items: center !important;
            gap: 0.35rem !important;
        }
        .dataTables_wrapper .dataTables_filter input,
        div.dataTables_wrapper div.dataTables_filter input {
            background-color: #ffffff !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 0.75rem !important;
            padding: 0.45rem 1rem !important;
            font-size: 0.875rem !important;
            font-weight: 500 !important;
            color: #1e293b !important;
            outline: none !important;
            margin-left: 0.25rem !important;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.04) !important;
            transition: all 0.15s ease-in-out !important;
            min-width: 14rem !important;
        }
        .dataTables_wrapper .dataTables_filter input:focus,
        div.dataTables_wrapper div.dataTables_filter input:focus {
            border-color: #4371D7 !important;
            box-shadow: 0 0 0 3px rgba(67, 113, 215, 0.25) !important;
        }

        .dataTables_wrapper .dataTables_info,
        div.dataTables_wrapper div.dataTables_info {
            padding-top: 0.5rem;
            font-size: 0.8125rem !important;
            font-weight: 600 !important;
            color: #475569 !important;
        }

        /* DataTables Custom Pagination (Rounded Pill Buttons matching mockup) */
        .dataTables_wrapper .dataTables_paginate,
        div.dataTables_wrapper div.dataTables_paginate,
        div.dataTables_wrapper .dataTables_paginate {
            padding-top: 0.5rem;
            display: inline-flex !important;
            align-items: center !important;
            gap: 0.4rem !important;
            float: right !important;
        }
        .dataTables_wrapper .dataTables_paginate span,
        div.dataTables_wrapper div.dataTables_paginate span {
            display: inline-flex !important;
            align-items: center !important;
            gap: 0.4rem !important;
            margin: 0 !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.first,
        .dataTables_wrapper .dataTables_paginate .paginate_button.last {
            display: none !important;
        }
        
        /* Base Styling for ALL Pagination Buttons (‹, 1, 2, ›) */
        .dataTables_wrapper .dataTables_paginate .paginate_button,
        div.dataTables_wrapper div.dataTables_paginate .paginate_button,
        .dataTables_wrapper .dataTables_paginate span .paginate_button,
        div.dataTables_wrapper div.dataTables_paginate span .paginate_button {
            border-radius: 0.6rem !important;
            border: 1px solid #e2e8f0 !important;
            background-color: #ffffff !important;
            background: #ffffff !important;
            color: #334155 !important;
            font-size: 0.875rem !important;
            font-weight: 700 !important;
            width: 2.25rem !important;
            height: 2.25rem !important;
            min-width: 2.25rem !important;
            padding: 0 !important;
            margin: 0 !important;
            transition: all 0.15s ease-in-out !important;
            cursor: pointer !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
            text-decoration: none !important;
        }
        
        /* Hover state for inactive buttons */
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover,
        div.dataTables_wrapper div.dataTables_paginate .paginate_button:hover {
            background-color: #f8fafc !important;
            background: #f8fafc !important;
            border-color: #cbd5e1 !important;
            color: #0f172a !important;
        }

        /* Active Current Page Button (Solid Royal Blue `#4371D7` with White Text) */
        .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover,
        div.dataTables_wrapper div.dataTables_paginate .paginate_button.current,
        div.dataTables_wrapper div.dataTables_paginate .paginate_button.current:hover,
        .dataTables_wrapper .dataTables_paginate span .paginate_button.current,
        .dataTables_wrapper .dataTables_paginate span .paginate_button.current:hover {
            background-color: #4371D7 !important;
            background: #4371D7 !important;
            color: #ffffff !important;
            border-color: #4371D7 !important;
            font-weight: 800 !important;
            box-shadow: 0 4px 6px -1px rgba(67, 113, 215, 0.35) !important;
        }

        /* Disabled Navigation Arrows (‹ or › when disabled) */
        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover,
        div.dataTables_wrapper div.dataTables_paginate .paginate_button.disabled,
        div.dataTables_wrapper div.dataTables_paginate .paginate_button.disabled:hover {
            opacity: 0.45 !important;
            cursor: not-allowed !important;
            background-color: #ffffff !important;
            background: #ffffff !important;
            border-color: #e2e8f0 !important;
            color: #94a3b8 !important;
            box-shadow: none !important;
        }

        /* Page Background Styling */
        body {
            background-color: #F3F4F4 !important;
        }

        /* DataTables Table Header Theme Styling */
        table.erp-datatable thead,
        table.erp-datatable thead tr,
        table.erp-datatable thead th,
        table.dataTable thead th,
        thead.bg-\[\#EDF4FA\],
        .bg-\[\#EDF4FA\] {
            background-color: #EDF4FA !important;
            color: #000000 !important;
        }

        html.dark table.erp-datatable thead,
        html.dark table.erp-datatable thead tr,
        html.dark table.erp-datatable thead th,
        html.dark table.dataTable thead th,
        html.dark thead.bg-\[\#EDF4FA\],
        html.dark table thead,
        html.dark table thead tr,
        html.dark table thead th,
        html.dark .bg-\[\#EDF4FA\],
        html.dark [class*="bg-[#EDF4FA]"] {
            background-color: #0f172a !important;
            color: #e2e8f0 !important;
            border-color: #334155 !important;
        }

        html.dark table.erp-datatable,
        html.dark table.dataTable,
        html.dark table {
            border-color: #334155 !important;
        }

        html.dark table.erp-datatable th,
        html.dark table.erp-datatable td,
        html.dark table.dataTable th,
        html.dark table.dataTable td,
        html.dark table th,
        html.dark table td {
            border-color: #334155 !important;
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
            border-right: 1px solid #cbd5e1 !important;
            border-bottom: 1px solid #cbd5e1 !important;
        }
        table.erp-datatable thead th:first-child,
        table.dataTable thead th:first-child {
            border-left: 1px solid #cbd5e1 !important;
        }
        table.erp-datatable thead th:last-child,
        table.dataTable thead th:last-child {
            border-right: 1px solid #cbd5e1 !important;
        }
        
        /* DataTables Empty State Container Styling */
        table.erp-datatable td.dataTables_empty,
        table.dataTable td.dataTables_empty {
            padding: 2.5rem 1rem !important;
            text-align: center !important;
            background-color: #ffffff !important;
            border-bottom: 1px solid #e2e8f0 !important;
        }
        
        /* Global Button Utility Styles */
        .btn-primary {
            background-color: #2563EB !important;
            color: #ffffff !important;
            border-radius: 0.75rem !important;
            box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.25), 0 2px 4px -2px rgba(37, 99, 235, 0.1) !important;
            transition: all 0.15s ease-in-out !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            border: 1px solid transparent !important;
            cursor: pointer !important;
        }
        .btn-primary:hover {
            background-color: #1d4ed8 !important;
            color: #ffffff !important;
            box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.35) !important;
            transform: translateY(-1px) !important;
        }
        .btn-primary:active {
            transform: translateY(0) !important;
            box-shadow: 0 2px 4px -1px rgba(37, 99, 235, 0.2) !important;
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

        /* Smooth In-Place Animations & Transitions */
        @keyframes fadeInRow {
            0% { opacity: 0; transform: translateY(-6px); }
            100% { opacity: 1; transform: translateY(0); }
        }
        .row-fade-in {
            animation: fadeInRow 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        .row-updated-glow {
            transition: background-color 0.8s ease !important;
            background-color: rgba(59, 130, 246, 0.15) !important;
        }
        .tab-pane-transition {
            transition: opacity 0.22s cubic-bezier(0.4, 0, 0.2, 1), transform 0.22s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .tab-pane-transition.hidden {
            display: none !important;
            opacity: 0;
            transform: translateY(4px);
        }
        .tab-pane-transition:not(.hidden) {
            display: block;
            opacity: 1;
            transform: translateY(0);
        }

        /* Universal Spinner Utility */
        @keyframes erpSpinner {
            to { transform: rotate(360deg); }
        }
        .erp-spinner {
            display: inline-block;
            width: 1rem;
            height: 1rem;
            vertical-align: -0.125em;
            border: 0.15em solid currentColor;
            border-right-color: transparent;
            border-radius: 50%;
            animation: erpSpinner 0.75s linear infinite;
        }

        /* Project-wide Modal Rules */
        div[id*="Modal"].hidden, div[id*="modal"].hidden {
            display: none !important;
        }
        div.fixed[id*="Modal"]:not(.hidden), div.fixed[id*="modal"]:not(.hidden) {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
            margin: 0 !important;
            z-index: 999999 !important;
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
                display: none !important;
                opacity: 0 !important;
                max-width: 0 !important;
                overflow: hidden !important;
                white-space: nowrap !important;
            }
            #sidebar.sidebar-collapsed .sidebar-category-header {
                visibility: hidden !important;
                opacity: 0 !important;
                height: 0px !important;
                margin: 0 !important;
                padding: 0 !important;
                overflow: hidden !important;
            }
            #sidebar.sidebar-collapsed .sidebar-profile-detail {
                display: none !important;
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
                opacity: 1 !important;
                width: auto !important;
                overflow: visible !important;
                pointer-events: auto !important;
                margin-left: 0.75rem !important;
                display: inline !important;
            }
            #sidebar.sidebar-collapsed:hover .sidebar-header-text {
                display: flex !important;
                opacity: 1 !important;
                max-width: 200px !important;
                overflow: visible !important;
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
<body class="min-h-screen md:flex bg-[#F3F4F4] overflow-x-hidden">

    <!-- Sidebar Navigation -->
    @include('layouts.sidebar')

    <!-- Floating Sidebar Toggle Button -->
    <button id="sidebarToggle" class="fixed top-4 left-4 z-40 bg-white hover:bg-slate-50 text-slate-600 hover:text-slate-900 p-2.5 rounded-xl border border-slate-200 shadow-sm transition-all duration-200 focus:outline-none md:hidden">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
        </svg>
    </button>

    <!-- Main Content Pane Wrapper -->
    <div id="main-content" class="flex-grow pl-0 md:pl-64 flex flex-col min-h-screen transition-all duration-300 overflow-x-hidden w-full max-w-full">
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

        <div id="page-content" class="p-4 md:px-8 md:pt-4 md:pb-8 flex-grow space-y-6 flex flex-col">
            @yield('content')
        </div>

        <!-- Master Footer Partial -->
        @include('layouts.footer')
    </div>

    <!-- Core Application Vendor Scripts (100% Offline Support) -->
    <script src="{{ asset('vendor/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('vendor/chart.min.js') }}"></script>
    <script src="{{ asset('vendor/tom-select.complete.min.js') }}"></script>
    <script src="{{ asset('vendor/jquery.dataTables.min.js') }}"></script>
    @stack('modals')

    <!-- Global Record Invoice Payment Modal -->
    <div id="globalRecordInvoicePaymentModal" class="fixed inset-0 z-50 overflow-y-auto hidden" style="display: none;" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs transition-opacity" onclick="closeGlobalInvoicePaymentModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            
            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-200">
                <div class="bg-slate-50 px-6 py-4 border-b border-slate-100 flex justify-between items-center">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Record Invoice Payment</h3>
                        <p class="text-xs text-slate-500 font-medium">Invoice: <span class="text-blue-600 font-bold" id="globalModalInvoiceNum"></span> | Remaining Balance: <span class="text-emerald-600 font-bold">₹<span id="globalModalRemainingText">0.00</span></span></p>
                    </div>
                    <button type="button" onclick="closeGlobalInvoicePaymentModal()" class="text-slate-400 hover:text-slate-600 text-lg font-bold">&times;</button>
                </div>

                <form id="globalRecordPaymentForm" action="" method="POST" onsubmit="submitGlobalInvoicePayment(event)">
                    @csrf
                    <input type="hidden" id="globalModalInvoiceId" name="invoice_id">

                    <div class="p-6 space-y-4 text-xs">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block font-bold text-slate-600 uppercase mb-1">Payment Amount (₹)</label>
                                <input type="number" step="0.01" min="0.01" name="amount" id="globalModalPayAmount" required
                                       class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-800 font-extrabold">
                            </div>
                            <div>
                                <label class="block font-bold text-slate-600 uppercase mb-1">Payment Date</label>
                                <input type="date" name="payment_date" id="globalModalPayDate" required
                                       class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-800 font-medium">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block font-bold text-slate-600 uppercase mb-1">Payment Mode</label>
                                <select name="payment_method" required class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-800 font-bold">
                                    <option value="bank_transfer">Bank Transfer (NEFT/RTGS)</option>
                                    <option value="cheque">Cheque</option>
                                    <option value="upi">UPI / Online Transfer</option>
                                    <option value="cash">Cash</option>
                                </select>
                            </div>
                            <div>
                                <label class="block font-bold text-slate-600 uppercase mb-1">Account Type</label>
                                <select name="account_type" required class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-800 font-bold">
                                    <option value="bank">Bank Account</option>
                                    <option value="cash">Cash in Hand</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-600 uppercase mb-1">Reference / UTR / Cheque No.</label>
                            <input type="text" name="reference_number" placeholder="e.g. UTR123456789 or Cheque #000123"
                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-800 font-mono">
                        </div>

                        <div>
                            <label class="block font-bold text-slate-600 uppercase mb-1">Internal Payment Notes</label>
                            <textarea name="notes" rows="2" placeholder="Optional notes for accounting ledger..."
                                      class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 px-3 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-800"></textarea>
                        </div>
                    </div>

                    <div class="bg-slate-50 px-6 py-3 border-t border-slate-100 flex justify-end space-x-3">
                        <button type="button" onclick="closeGlobalInvoicePaymentModal()" class="px-4 py-2 text-xs font-bold text-slate-600 hover:text-slate-800">Cancel</button>
                        <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white py-2 px-5 text-xs font-bold rounded-xl shadow-xs transition">
                            Confirm & Record Payment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        window.openInvoicePaymentModal = function(id, invoiceNumber, remainingBalance) {
            var modal = document.getElementById('globalRecordInvoicePaymentModal');
            if (!modal) return;
            document.getElementById('globalModalInvoiceId').value = id;
            document.getElementById('globalModalInvoiceNum').innerText = invoiceNumber;
            const cleanRemStr = (remainingBalance !== undefined && remainingBalance !== null) ? remainingBalance.toString().replace(/,/g, '').trim() : '0';
            const rem = parseFloat(cleanRemStr) || 0;
            document.getElementById('globalModalRemainingText').innerText = rem.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            document.getElementById('globalModalPayAmount').value = rem > 0 ? rem.toFixed(2) : '';
            document.getElementById('globalModalPayDate').value = new Date().toISOString().split('T')[0];
            
            modal.classList.remove('hidden');
            modal.style.display = 'block';
        };

        window.payInvoiceRecord = function(id, invoiceNumber, remainingBalance) {
            window.openInvoicePaymentModal(id, invoiceNumber, remainingBalance);
        };

        window.closeGlobalInvoicePaymentModal = function() {
            var modal = document.getElementById('globalRecordInvoicePaymentModal');
            if (modal) {
                modal.classList.add('hidden');
                modal.style.display = 'none';
            }
        };

        window.submitGlobalInvoicePayment = function(e) {
            e.preventDefault();
            const invId = document.getElementById('globalModalInvoiceId').value;
            const $submitBtn = $(e.target).find('button[type="submit"]');
            if (window.setButtonLoading) window.setButtonLoading($submitBtn, true, 'Recording...');

            const formData = new FormData(e.target);
            
            // Clean comma-formatted amount string before sending to backend
            let rawAmount = formData.get('amount') || '';
            rawAmount = rawAmount.toString().replace(/,/g, '').trim();
            formData.set('amount', rawAmount);

            const token = $('meta[name="csrf-token"]').attr('content') || '';

            $.ajax({
                url: `/invoices/${invId}/record-payment`,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                success: async function(response) {
                    if (window.setButtonLoading) window.setButtonLoading($submitBtn, false);
                    closeGlobalInvoicePaymentModal();
                    if (window.showToast) {
                        window.showToast('success', response.message || 'Payment recorded successfully!');
                    }

                    const $row = $(`#row-inv-${invId}, #invoice-row-${invId}`);
                    if ($row.length) {
                        const paidAmount = parseFloat(rawAmount) || 0;
                        const currentBalText = document.getElementById('globalModalRemainingText') ? document.getElementById('globalModalRemainingText').innerText.replace(/,/g, '') : '0';
                        const prevBal = parseFloat(currentBalText) || 0;
                        const newBal = Math.max(0, prevBal - paidAmount);

                        $row.find('.inv-balance-cell').text('₹' + (window.formatIndianCurrency ? window.formatIndianCurrency(newBal.toFixed(2)) : newBal.toFixed(2)));
                        
                        let $statusCell = $row.find('.inv-status-cell, .status-badge-cell, td:has(button[onclick*="openInvoicePaymentModal"]), td:has(button[onclick*="payInvoiceRecord"]), td:has(.bg-emerald-100), td:has(.bg-amber-100), td:has(.bg-rose-100)');
                        if (!$statusCell.length) {
                            $statusCell = $row.find('td:nth-last-child(2)');
                        }

                        if (newBal <= 0) {
                            $statusCell.html(`
                                <span class="px-2 py-0.5 rounded-full text-[9.5px] font-extrabold uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-300 shadow-2xs">
                                    RECEIVED
                                </span>
                            `);
                            if (window.updateStatCounter) {
                                window.updateStatCounter('#statUnpaidInvoices', -1);
                                window.updateStatCounter('#statPaidInvoices', +1);
                            }
                        } else {
                            const invNo = $row.find('.inv-no-cell, td:nth-child(2)').text().trim();
                            $statusCell.html(`
                                <button type="button" 
                                        onclick="openInvoicePaymentModal(${invId}, '${invNo}', ${newBal})"
                                        title="Click to record next payment for this invoice"
                                        class="px-2 py-0.5 rounded-full text-[9.5px] font-extrabold uppercase tracking-wider bg-amber-100 text-amber-800 border border-amber-300 hover:bg-amber-200 transition cursor-pointer shadow-2xs">
                                    PARTIAL (₹${window.formatIndianCurrency ? window.formatIndianCurrency(newBal.toFixed(0)) : newBal.toFixed(0)} DUE)
                                </button>
                            `);
                        }
                        if (window.ERPTableHelper) window.ERPTableHelper.highlightRow($row);
                    } else if (window.loadPage) {
                        await window.loadPage(window.location.href);
                    }
                },
                error: function(xhr) {
                    if (window.setButtonLoading) window.setButtonLoading($submitBtn, false);
                    let msg = 'Failed to record payment.';
                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.errors && typeof xhr.responseJSON.errors === 'object') {
                            const firstKey = Object.keys(xhr.responseJSON.errors)[0];
                            const firstErr = xhr.responseJSON.errors[firstKey];
                            msg = Array.isArray(firstErr) ? firstErr[0] : firstErr;
                        } else if (xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                    }
                    if (window.showToast) {
                        window.showToast('error', msg);
                    } else {
                        alert(msg);
                    }
                }
            });
        };

        window.deleteInvoiceRecord = function(id, invoiceNumber) {
            window.confirmDelete(
                'Delete Invoice?',
                `Are you sure you want to permanently delete Invoice '${invoiceNumber}'? This action cannot be undone!`,
                function() {
                    const token = $('meta[name="csrf-token"]').attr('content') || '';
                    $.ajax({
                        url: `/invoices/${id}`,
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json'
                        },
                        success: async function(response) {
                            if (window.showToast) {
                                window.showToast('success', response.message || 'Invoice deleted successfully!');
                            }
                            if (window.loadPage) {
                                await window.loadPage(window.location.href);
                            } else {
                                window.location.reload();
                            }
                        },
                        error: function(xhr) {
                            const msg = xhr.responseJSON && xhr.responseJSON.message ? (xhr.responseJSON.message || (xhr.responseJSON.errors ? xhr.responseJSON.errors[0] : 'Failed to delete invoice.')) : 'Failed to delete invoice.';
                            if (window.showToast) {
                                window.showToast('error', msg);
                            } else {
                                alert(msg);
                            }
                        }
                    });
                }
            );
        };

        // Global ERP Combobox Manager (Event-Delegated Engine)
        window.ERPComboboxManager = {
            init: function() {
                if (window._ERPComboboxDelegated) return;
                window._ERPComboboxDelegated = true;

                const getWrapper = (target) => target ? target.closest('.combobox-wrapper') : null;

                document.addEventListener('focusin', function(e) {
                    const wrapper = getWrapper(e.target);
                    if (wrapper && e.target.classList.contains('combobox-search-input')) {
                        window.ERPComboboxManager.show(wrapper);
                    }
                });

                document.addEventListener('click', function(e) {
                    const wrapper = getWrapper(e.target);
                    
                    // Close all other open comboboxes
                    document.querySelectorAll('.combobox-wrapper').forEach(w => {
                        if (w !== wrapper) {
                            window.ERPComboboxManager.hide(w);
                        }
                    });

                    if (!wrapper) return;

                    if (e.target.classList.contains('combobox-search-input')) {
                        window.ERPComboboxManager.show(wrapper);
                    } else if (e.target.classList.contains('combobox-clear-btn')) {
                        e.stopPropagation();
                        window.ERPComboboxManager.clear(wrapper);
                    } else {
                        const opt = e.target.closest('.combobox-option');
                        if (opt) {
                            window.ERPComboboxManager.select(wrapper, opt);
                        }
                    }
                });

                document.addEventListener('input', function(e) {
                    const wrapper = getWrapper(e.target);
                    if (wrapper && e.target.classList.contains('combobox-search-input')) {
                        window.ERPComboboxManager.filter(wrapper);
                    }
                });

                document.addEventListener('keydown', function(e) {
                    const wrapper = getWrapper(e.target);
                    if (!wrapper || !e.target.classList.contains('combobox-search-input')) return;

                    const dropdown = wrapper.querySelector('.combobox-dropdown');
                    const options = Array.from(wrapper.querySelectorAll('.combobox-option:not(.hidden)'));
                    let activeOpt = wrapper.querySelector('.combobox-option.bg-blue-100');
                    let activeIndex = activeOpt ? options.indexOf(activeOpt) : -1;

                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        if (dropdown && dropdown.classList.contains('hidden')) window.ERPComboboxManager.show(wrapper);
                        if (options.length > 0) {
                            activeIndex = (activeIndex + 1) % options.length;
                            window.ERPComboboxManager.highlight(options, activeIndex);
                        }
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        if (options.length > 0) {
                            activeIndex = (activeIndex - 1 + options.length) % options.length;
                            window.ERPComboboxManager.highlight(options, activeIndex);
                        }
                    } else if (e.key === 'Enter') {
                        if (activeIndex >= 0 && options[activeIndex]) {
                            e.preventDefault();
                            window.ERPComboboxManager.select(wrapper, options[activeIndex]);
                        } else {
                            window.ERPComboboxManager.hide(wrapper);
                        }
                    } else if (e.key === 'Escape') {
                        window.ERPComboboxManager.hide(wrapper);
                    }
                });

                // Initial sync for any prefilled values
                document.querySelectorAll('.combobox-wrapper').forEach(w => {
                    window.ERPComboboxManager.syncDisplay(w);
                });
            },

            show: function(wrapper) {
                const dropdown = wrapper.querySelector('.combobox-dropdown');
                if (dropdown) dropdown.classList.remove('hidden');
                this.filter(wrapper);
            },

            hide: function(wrapper) {
                const dropdown = wrapper.querySelector('.combobox-dropdown');
                if (dropdown) dropdown.classList.add('hidden');
            },

            filter: function(wrapper) {
                const searchInput = wrapper.querySelector('.combobox-search-input');
                const hiddenInput = wrapper.querySelector('.combobox-hidden-input');
                const dropdown = wrapper.querySelector('.combobox-dropdown');
                const noMatch = wrapper.querySelector('.combobox-no-match');
                const options = Array.from(wrapper.querySelectorAll('.combobox-option'));
                const allowCustom = wrapper.dataset.allowCustom === 'true';

                const q = (searchInput ? searchInput.value : '').toLowerCase().trim();
                if (allowCustom && hiddenInput && searchInput) {
                    hiddenInput.value = searchInput.value;
                    hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
                }

                let visibleCount = 0;
                options.forEach(opt => {
                    const searchStr = opt.dataset.search || '';
                    if (!q || searchStr.includes(q)) {
                        opt.classList.remove('hidden');
                        visibleCount++;
                    } else {
                        opt.classList.add('hidden');
                    }
                });

                if (noMatch) noMatch.classList.toggle('hidden', visibleCount > 0);
                if (dropdown && dropdown.classList.contains('hidden') && visibleCount > 0) dropdown.classList.remove('hidden');
            },

            highlight: function(options, activeIndex) {
                options.forEach((opt, idx) => {
                    opt.classList.toggle('bg-blue-100', idx === activeIndex);
                    opt.classList.toggle('font-bold', idx === activeIndex);
                    if (idx === activeIndex) opt.scrollIntoView({ block: 'nearest' });
                });
            },

            select: function(wrapper, opt) {
                const hiddenInput = wrapper.querySelector('.combobox-hidden-input');
                const searchInput = wrapper.querySelector('.combobox-search-input');
                const clearBtn = wrapper.querySelector('.combobox-clear-btn');

                if (hiddenInput) {
                    hiddenInput.value = opt.dataset.value;
                    hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
                }
                if (searchInput) searchInput.value = opt.dataset.label;
                if (clearBtn) clearBtn.classList.remove('hidden');

                if (typeof wrapper.onComboboxSelect === 'function') {
                    wrapper.onComboboxSelect(opt);
                }
                this.hide(wrapper);
            },

            clear: function(wrapper) {
                const hiddenInput = wrapper.querySelector('.combobox-hidden-input');
                const searchInput = wrapper.querySelector('.combobox-search-input');
                const clearBtn = wrapper.querySelector('.combobox-clear-btn');

                if (hiddenInput) {
                    hiddenInput.value = '';
                    hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
                }
                if (searchInput) {
                    searchInput.value = '';
                    searchInput.focus();
                }
                if (clearBtn) clearBtn.classList.add('hidden');
                this.show(wrapper);
            },

            syncDisplay: function(wrapper) {
                if (!wrapper) return;
                const hiddenInput = wrapper.querySelector('.combobox-hidden-input');
                const searchInput = wrapper.querySelector('.combobox-search-input');
                const clearBtn = wrapper.querySelector('.combobox-clear-btn');
                const options = Array.from(wrapper.querySelectorAll('.combobox-option'));
                const allowCustom = wrapper.dataset.allowCustom === 'true';

                if (!hiddenInput || !searchInput) return;
                const val = hiddenInput.value;
                const matchedOpt = options.find(o => o.dataset.value === val);
                if (matchedOpt) {
                    searchInput.value = matchedOpt.dataset.label;
                    if (clearBtn) clearBtn.classList.remove('hidden');
                } else if (allowCustom && val) {
                    searchInput.value = val;
                    if (clearBtn) clearBtn.classList.remove('hidden');
                } else {
                    searchInput.value = '';
                    if (clearBtn) clearBtn.classList.add('hidden');
                }
            }
        };

        document.addEventListener('DOMContentLoaded', () => {
            window.ERPComboboxManager.init();
        });
    </script>
    @if(session('auto_download_backup_url'))
        <script>
            (function() {
                setTimeout(function() {
                    const a = document.createElement('a');
                    a.href = "{{ session('auto_download_backup_url') }}";
                    a.download = '';
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    if (window.showToast) {
                        window.showToast('success', '📦 Backup file automatically downloaded to your local Downloads folder!');
                    }
                }, 400);
            })();
        </script>
        <iframe src="{{ session('auto_download_backup_url') }}" style="display:none; width:0; height:0;" aria-hidden="true"></iframe>
    @endif
</body>
</html>
@endif
