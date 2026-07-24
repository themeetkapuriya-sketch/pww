@if(request()->ajax() && !request()->pjax())
    <title>@yield('title', 'PWW ERP') - Praful Welding Works</title>
    <div id="page-content" class="p-4 md:px-8 md:pt-4 md:pb-8 flex-grow space-y-6">
        @yield('content')
    </div>
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
    <!-- Chart.js & SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- DataTables CSS & TomSelect CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
    <style>
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
    <div id="main-content" class="flex-grow pl-0 md:pl-64 flex flex-col min-h-screen transition-all duration-300">
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
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="{{ asset('js/app-core.js') }}"></script>
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
            const rem = (remainingBalance !== undefined && remainingBalance !== null) ? parseFloat(remainingBalance) : 0;
            document.getElementById('globalModalRemainingText').innerText = rem.toFixed(2);
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
            const formData = new FormData(e.target);
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
                    closeGlobalInvoicePaymentModal();
                    if (window.showToast) {
                        window.showToast('success', response.message || 'Payment recorded successfully!');
                    }
                    if (window.loadPage) {
                        await window.loadPage(window.location.href);
                    } else {
                        window.location.reload();
                    }
                },
                error: function(xhr) {
                    const msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Failed to record payment.';
                    alert(msg);
                }
            });
        };

        window.deleteInvoiceRecord = function(id, invoiceNumber) {
            const doDelete = function() {
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
            };

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Delete Invoice?',
                    text: `Are you sure you want to permanently delete Invoice '${invoiceNumber}'? This action cannot be undone!`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#f43f5e',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Yes, Delete Invoice',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        doDelete();
                    }
                });
            } else if (confirm(`Are you sure you want to delete Invoice '${invoiceNumber}'?`)) {
                doDelete();
            }
        };
    </script>
</body>
</html>
@endif
