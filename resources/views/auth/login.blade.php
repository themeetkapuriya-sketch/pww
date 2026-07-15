<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PWW ERP - Secure Authentication</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center bg-slate-50 p-4">

    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl border border-slate-200 p-8 space-y-6">
        
        <!-- Logo and Heading -->
        <div class="text-center space-y-4 flex flex-col items-center">
            <!-- PWW Brand Image Logo -->
            <img class="h-16 w-16 object-contain rounded-2xl border border-slate-100 shadow-sm" src="{{ asset('logo.jpg') }}" alt="PWW Logo">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight">Praful Welding Works</h1>
                <p class="text-sm text-slate-500 font-semibold uppercase tracking-wider mt-1">ERP Secure Login Portal</p>
            </div>
        </div>

        <!-- Alert Container -->
        <div id="alertContainer" class="hidden text-sm p-4 rounded-xl border"></div>

        <!-- Forms -->
        <form id="loginForm" class="space-y-4">
            @csrf
            <div>
                <label for="email" class="block text-xs font-bold text-slate-600 uppercase mb-1">EMAIL ADDRESS</label>
                <input type="email" id="email" name="email" required placeholder="e.g. pww@example.com"
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 transition">
            </div>

            <div>
                <label for="password" class="block text-xs font-bold text-slate-600 uppercase mb-1">PASSWORD</label>
                <div class="relative">
                    <input type="password" id="password" name="password" required placeholder="••••••••"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2.5 pl-4 pr-10 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 transition">
                    <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                        <svg id="eyeIconOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg id="eyeIconClose" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="flex items-center justify-between text-xs">
                <label class="flex items-center text-slate-500 font-medium">
                    <input type="checkbox" name="remember" class="mr-1.5 rounded border-slate-300 text-blue-600 focus:ring-blue-500"> Remember this device
                </label>
            </div>

            <button type="submit" id="submitBtn"
                    class="w-full bg-[#1E73BE] hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-xl shadow-md transition duration-150 ease-in-out text-sm flex items-center justify-center space-x-2">
                <span>Sign In to Dashboard</span>
            </button>
        </form>
        
        <div class="text-center text-xs text-slate-400 mt-4">
            Restricted access portal. Registered PWW accounts only.
        </div>
    </div>

    <!-- AJAX Script -->
    <script>
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = document.getElementById('submitBtn');
            const alertContainer = document.getElementById('alertContainer');
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const csrfToken = document.querySelector('input[name="_token"]').value;
            
            // UI Loading state
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-75');
            submitBtn.innerHTML = `
                <svg class="animate-spin h-5 w-5 mr-3 text-white" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Authenticating...</span>
            `;
            
            alertContainer.className = 'hidden';
            
            try {
                const response = await fetch('/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ email, password })
                });
                
                const data = await response.json();
                
                if (response.ok && data.success) {
                    // Redirect immediately for faster login response
                    window.location.href = data.redirect;
                } else {
                    displayErrors(data.errors || ['Invalid credentials. Please try again.']);
                    resetBtn(submitBtn);
                }
            } catch (err) {
                displayErrors(['A network error occurred. Please verify your connection.']);
                resetBtn(submitBtn);
            }
        });
        
        function displayErrors(errors) {
            const alertContainer = document.getElementById('alertContainer');
            alertContainer.className = 'bg-rose-50 border-rose-200 text-rose-800 p-4 rounded-xl border text-sm';
            
            let list = '<ul class="list-disc list-inside space-y-1">';
            errors.forEach(err => {
                list += `<li>${err}</li>`;
            });
            list += '</ul>';
            
            alertContainer.innerHTML = `<strong>Authentication Failed:</strong> ${list}`;
        }
        
        function resetBtn(btn) {
            btn.disabled = false;
            btn.classList.remove('opacity-75');
            btn.innerHTML = '<span>Sign In to Dashboard</span>';
        }
        // Toggle password visibility
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const eyeIconOpen = document.getElementById('eyeIconOpen');
        const eyeIconClose = document.getElementById('eyeIconClose');

        if (togglePassword && passwordInput) {
            togglePassword.addEventListener('click', function () {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                if (type === 'password') {
                    eyeIconOpen.classList.remove('hidden');
                    eyeIconClose.classList.add('hidden');
                } else {
                    eyeIconOpen.classList.add('hidden');
                    eyeIconClose.classList.remove('hidden');
                }
            });
        }
    </script>
</body>
</html>
