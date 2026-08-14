<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Session Expired - PWW ERP</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="stylesheet" href="{{ asset('fonts/outfit/outfit.css') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.classList.add('dark');
        }
    </script>
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #EEF2F6;
        }
        html.dark body {
            background-color: #0f172a;
            color: #f8fafc;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white dark:bg-slate-800 rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-700 p-8 text-center space-y-6">
        <div class="w-16 h-16 rounded-2xl bg-amber-50 dark:bg-amber-950/60 border border-amber-200 dark:border-amber-700 flex items-center justify-center mx-auto text-3xl">
            ⏳
        </div>
        <div>
            <h2 class="text-xl font-extrabold text-slate-800 dark:text-white">Session Expired</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-2 font-medium">
                Your login session has expired due to inactivity. Redirecting you to the secure login page...
            </p>
        </div>
        <div class="pt-2">
            <a href="{{ route('login') }}" class="w-full inline-flex items-center justify-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl shadow-md transition">
                Click here to Login Now →
            </a>
        </div>
    </div>
    <script>
        setTimeout(function() {
            window.location.replace("{{ route('login') }}");
        }, 1200);
    </script>
</body>
</html>
