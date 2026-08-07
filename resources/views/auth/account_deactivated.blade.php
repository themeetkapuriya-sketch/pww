<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Inactive - PWW ERP</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <!-- Local Fonts, Compiled CSS & jQuery -->
    <link rel="stylesheet" href="{{ asset('fonts/outfit/outfit.css') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="{{ asset('vendor/jquery.min.js') }}"></script>
    <script>
        if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    <style>
        /* Default Light Mode Explicit Styling */
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #EEF2F6 !important;
            color: #1e293b !important;
        }
        .deact-card {
            background-color: #ffffff !important;
            border-color: rgba(255, 255, 255, 0.8) !important;
            box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.08) !important;
        }
        .deact-title {
            color: #1e293b !important;
        }
        .deact-details-box {
            background-color: #f8fafc !important;
            border-color: #e2e8f0 !important;
        }
        .deact-label {
            color: #64748b !important;
        }
        .deact-value-name {
            color: #0f172a !important;
        }
        .deact-value-email {
            color: #334155 !important;
        }
        .deact-logo-box {
            background-color: #ffffff !important;
            border-color: #f1f5f9 !important;
        }
        .deact-footer-text {
            color: #94a3b8 !important;
            border-color: #f1f5f9 !important;
        }

        /* Deactivated Alert Box */
        .deact-alert-box {
            background-color: #fff1f2 !important;
            border-color: #fecdd3 !important;
            color: #9f1239 !important;
        }
        .deact-alert-box strong {
            color: #881337 !important;
        }
        .deact-alert-box p {
            color: #9f1239 !important;
        }
        .deact-alert-box svg {
            color: #e11d48 !important;
        }
        html.dark .deact-alert-box {
            background-color: rgba(136, 19, 55, 0.4) !important;
            border-color: rgba(244, 63, 94, 0.4) !important;
            color: #fecdd3 !important;
        }
        html.dark .deact-alert-box strong {
            color: #fecdd3 !important;
        }
        html.dark .deact-alert-box p {
            color: #fda4af !important;
        }
        html.dark .deact-alert-box svg {
            color: #fb7185 !important;
        }

        /* Role Badge */
        .deact-role-badge {
            background-color: #eff6ff !important;
            border-color: #bfdbfe !important;
            color: #1d4ed8 !important;
        }
        html.dark .deact-role-badge {
            background-color: rgba(30, 58, 138, 0.5) !important;
            border-color: rgba(59, 130, 246, 0.4) !important;
            color: #93c5fd !important;
        }

        /* Status Badge */
        .deact-status-badge {
            background-color: #fff1f2 !important;
            border-color: #fecdd3 !important;
            color: #be123c !important;
        }
        html.dark .deact-status-badge {
            background-color: rgba(136, 19, 55, 0.5) !important;
            border-color: rgba(244, 63, 94, 0.4) !important;
            color: #fecdd3 !important;
        }

        /* Dark Mode Overrides (Triggered when html element has 'dark' class) */
        html.dark body {
            background-color: #0f172a !important;
            color: #f8fafc !important;
        }
        html.dark .deact-card {
            background-color: rgba(30, 41, 59, 0.95) !important;
            border-color: rgba(51, 65, 85, 0.8) !important;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6) !important;
        }
        html.dark .deact-title {
            color: #ffffff !important;
        }
        html.dark .deact-details-box {
            background-color: #0f172a !important;
            border-color: #334155 !important;
        }
        html.dark .deact-label {
            color: #94a3b8 !important;
        }
        html.dark .deact-value-name {
            color: #ffffff !important;
        }
        html.dark .deact-value-email {
            color: #cbd5e1 !important;
        }
        html.dark .deact-logo-box {
            background-color: #0f172a !important;
            border-color: #334155 !important;
        }
        html.dark .deact-footer-text {
            color: #64748b !important;
            border-color: #334155 !important;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 relative overflow-hidden transition-colors duration-200">

    <!-- Container Card -->
    <div class="max-w-[440px] w-full rounded-2xl shadow-2xl p-8 space-y-6 relative z-10 border deact-card transition-all duration-200">
        
        <!-- Logo and Heading -->
        <div class="text-center space-y-3 flex flex-col items-center">
            <!-- PWW Brand Image Logo -->
            <div class="p-2 rounded-2xl border shadow-sm deact-logo-box">
                <img class="h-14 w-14 object-contain rounded-xl" src="{{ asset('logo.jpg') }}" alt="PWW Logo">
            </div>
            <div>
                <h1 class="text-2xl font-black tracking-tight deact-title">Praful Welding Works</h1>
                <p class="text-xs text-rose-600 dark:text-rose-400 font-bold uppercase tracking-widest mt-1">ACCOUNT ACCESS SUSPENDED</p>
            </div>
        </div>

        <!-- Alert Box -->
        <div class="deact-alert-box p-4 rounded-xl text-xs font-medium leading-relaxed shadow-2xs border">
            <div class="flex items-center gap-2 mb-1">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <strong class="text-xs uppercase tracking-wider font-extrabold">Access Deactivated</strong>
            </div>
            <p class="pl-6">
                {{ $reason ?? 'Your user account or assigned role is currently inactive in the system.' }}
            </p>
        </div>

        <!-- Account Details Card -->
        <div class="rounded-xl p-4 space-y-2.5 text-xs border deact-details-box">
            <div class="flex justify-between items-center pb-2 border-b border-slate-200/60 dark:border-slate-800">
                <span class="font-bold uppercase text-[10px] tracking-wider deact-label">USER NAME</span>
                <span class="font-black deact-value-name">{{ $userName ?? auth()->user()->name ?? 'N/A' }}</span>
            </div>

            <div class="flex justify-between items-center pb-2 border-b border-slate-200/60 dark:border-slate-800">
                <span class="font-bold uppercase text-[10px] tracking-wider deact-label">EMAIL ADDRESS</span>
                <span class="font-bold font-mono deact-value-email">{{ $userEmail ?? auth()->user()->email ?? 'N/A' }}</span>
            </div>

            <div class="flex justify-between items-center pb-2 border-b border-slate-200/60 dark:border-slate-800">
                <span class="font-bold uppercase text-[10px] tracking-wider deact-label">ASSIGNED ROLE</span>
                <span class="font-extrabold deact-role-badge px-2.5 py-0.5 rounded-full border text-[11px]">{{ $roleName ?? 'Staff Member' }}</span>
            </div>

            <div class="flex justify-between items-center">
                <span class="font-bold uppercase text-[10px] tracking-wider deact-label">STATUS</span>
                <span class="font-black deact-status-badge px-2.5 py-0.5 rounded-full border text-[11px] flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span> INACTIVE
                </span>
            </div>
        </div>

        <!-- Back to Login Button -->
        <a href="{{ route('login') }}" 
           class="w-full bg-[#1E73BE] hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-xl shadow-md transition duration-150 ease-in-out text-sm flex items-center justify-center space-x-2 cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
            </svg>
            <span>Back to Login Screen</span>
        </a>
        
        <!-- Footer -->
        <div class="text-center text-xs mt-4 pt-2 border-t deact-footer-text">
            Restricted access portal. Registered PWW accounts only.
        </div>
    </div>

</body>
</html>
