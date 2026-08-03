<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Inactive - PWW ERP</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <!-- Local Fonts & Styles -->
    <link rel="stylesheet" href="{{ asset('fonts/outfit/outfit.css') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #EEF2F6;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 relative overflow-hidden">

    <!-- Glassmorphic Container Card (Identical to Login Card) -->
    <div class="max-w-[440px] w-full bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl p-8 space-y-6 relative z-10 border border-white/60">
        
        <!-- Logo and Heading -->
        <div class="text-center space-y-3 flex flex-col items-center">
            <!-- PWW Brand Image Logo -->
            <div class="p-2 bg-white rounded-2xl border border-slate-100 shadow-sm">
                <img class="h-14 w-14 object-contain rounded-xl" src="{{ asset('logo.jpg') }}" alt="PWW Logo">
            </div>
            <div>
                <h1 class="text-2xl font-black text-slate-800 tracking-tight">Praful Welding Works</h1>
                <p class="text-xs text-rose-600 font-bold uppercase tracking-widest mt-1">ACCOUNT ACCESS SUSPENDED</p>
            </div>
        </div>

        <!-- Alert Box (Identical to Login Alert) -->
        <div class="bg-rose-50 border border-rose-200 text-rose-800 p-4 rounded-xl text-xs font-medium leading-relaxed shadow-2xs">
            <div class="flex items-center gap-2 mb-1">
                <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <strong class="text-xs uppercase tracking-wider text-rose-900 font-extrabold">Access Deactivated</strong>
            </div>
            <p class="pl-6 text-rose-700">
                {{ $reason ?? 'Your user account or assigned role is currently inactive in the system.' }}
            </p>
        </div>

        <!-- Account Details Card -->
        <div class="bg-slate-50/80 border border-slate-200 rounded-xl p-4 space-y-2.5 text-xs">
            <div class="flex justify-between items-center pb-2 border-b border-slate-200/60">
                <span class="font-bold text-slate-500 uppercase text-[10px] tracking-wider">USER NAME</span>
                <span class="font-black text-slate-900">{{ $userName ?? auth()->user()->name ?? 'N/A' }}</span>
            </div>

            <div class="flex justify-between items-center pb-2 border-b border-slate-200/60">
                <span class="font-bold text-slate-500 uppercase text-[10px] tracking-wider">EMAIL ADDRESS</span>
                <span class="font-bold text-slate-700 font-mono">{{ $userEmail ?? auth()->user()->email ?? 'N/A' }}</span>
            </div>

            <div class="flex justify-between items-center pb-2 border-b border-slate-200/60">
                <span class="font-bold text-slate-500 uppercase text-[10px] tracking-wider">ASSIGNED ROLE</span>
                <span class="font-extrabold text-blue-700 bg-blue-100/80 px-2.5 py-0.5 rounded-full border border-blue-200/80 text-[11px]">{{ $roleName ?? 'Staff Member' }}</span>
            </div>

            <div class="flex justify-between items-center">
                <span class="font-bold text-slate-500 uppercase text-[10px] tracking-wider">STATUS</span>
                <span class="font-black text-rose-600 bg-rose-100/80 px-2.5 py-0.5 rounded-full border border-rose-200/80 text-[11px] flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span> INACTIVE
                </span>
            </div>
        </div>

        <!-- Back to Login Button (Identical to Sign In Button) -->
        <a href="{{ route('login') }}" 
           class="w-full bg-[#1E73BE] hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-xl shadow-md transition duration-150 ease-in-out text-sm flex items-center justify-center space-x-2 cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
            </svg>
            <span>Back to Login Screen</span>
        </a>
        
        <!-- Footer -->
        <div class="text-center text-xs text-slate-400 mt-4 pt-2 border-t border-slate-100/80">
            Restricted access portal. Registered PWW accounts only.
        </div>
    </div>

</body>
</html>
