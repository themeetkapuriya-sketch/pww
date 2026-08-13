<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PWW ERP - Secure Authentication</title>
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
        .login-card {
            background-color: #ffffff !important;
            border-color: rgba(255, 255, 255, 0.8) !important;
            box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.08) !important;
        }
        .login-title {
            color: #1e293b !important;
        }
        .login-subtitle {
            color: #64748b !important;
        }
        .login-label {
            color: #475569 !important;
        }
        .login-input {
            background-color: #f8fafc !important;
            border-color: #e2e8f0 !important;
            color: #1e293b !important;
        }
        .login-input::placeholder {
            color: #94a3b8 !important;
        }
        .login-logo-box {
            background-color: #ffffff !important;
            border-color: #f1f5f9 !important;
        }
        .login-footer-text {
            color: #94a3b8 !important;
            border-color: #f1f5f9 !important;
        }

        /* Success Alert Box */
        .alert-success {
            background-color: #ecfdf5 !important;
            border-color: #a7f3d0 !important;
            color: #065f46 !important;
        }
        .alert-success svg {
            color: #059669 !important;
        }
        html.dark .alert-success {
            background-color: rgba(6, 78, 59, 0.4) !important;
            border-color: rgba(16, 185, 129, 0.4) !important;
            color: #a7f3d0 !important;
        }
        html.dark .alert-success svg {
            color: #34d399 !important;
        }

        /* Error Alert Box */
        .alert-danger {
            background-color: #fff1f2 !important;
            border-color: #fecdd3 !important;
            color: #9f1239 !important;
        }
        .alert-danger svg {
            color: #e11d48 !important;
        }
        html.dark .alert-danger {
            background-color: rgba(136, 19, 55, 0.4) !important;
            border-color: rgba(244, 63, 94, 0.4) !important;
            color: #fecdd3 !important;
        }
        html.dark .alert-danger svg {
            color: #fb7185 !important;
        }

        /* Dark Mode Overrides (Triggered when html element has 'dark' class) */
        html.dark body {
            background-color: #0f172a !important;
            color: #f8fafc !important;
        }
        html.dark .login-card {
            background-color: rgba(30, 41, 59, 0.95) !important;
            border-color: rgba(51, 65, 85, 0.8) !important;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6) !important;
        }
        html.dark .login-title {
            color: #ffffff !important;
        }
        html.dark .login-subtitle {
            color: #94a3b8 !important;
        }
        html.dark .login-label {
            color: #cbd5e1 !important;
        }
        html.dark .login-input {
            background-color: #0f172a !important;
            border-color: #334155 !important;
            color: #f8fafc !important;
        }
        html.dark .login-input::placeholder {
            color: #64748b !important;
        }
        html.dark .login-logo-box {
            background-color: #0f172a !important;
            border-color: #334155 !important;
        }
        html.dark .login-footer-text {
            color: #64748b !important;
            border-color: #334155 !important;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 relative overflow-hidden transition-colors duration-200">

    <!-- Glassmorphic Login Container Card -->
    <div class="max-w-[440px] w-full rounded-2xl shadow-2xl p-8 space-y-6 relative z-10 border login-card transition-all duration-200">
        
        <!-- Logo and Heading -->
        <div class="text-center space-y-3 flex flex-col items-center">
            <!-- PWW Brand Image Logo -->
            <div class="p-2 rounded-2xl border shadow-sm login-logo-box">
                <img class="h-14 w-14 object-contain rounded-xl" src="{{ asset('logo.jpg') }}" alt="PWW Logo">
            </div>
            <div>
                <h1 class="text-2xl font-black tracking-tight login-title">Praful Welding Works</h1>
                <p class="text-base text-blue-600 dark:text-blue-400 font-extrabold mt-1">Welcome To Admin Panel ! 👋</p>
                <p class="text-sm font-medium mt-1 login-subtitle">Please sign-in to your account and get access</p>
            </div>
        </div>

        <!-- Alert Container -->
        <div id="alertContainer" class="hidden text-sm p-4 rounded-xl border transition-all duration-200"></div>

        <!-- Login Form -->
        <form id="loginForm" action="{{ route('login') }}" method="POST" class="space-y-4" novalidate>
            @csrf
            <div>
                <label for="email" class="block text-xs font-bold uppercase mb-1 login-label">EMAIL ADDRESS</label>
                <input type="email" id="email" name="email" value="" placeholder="e.g. pww@gmail.com" autocomplete="username" required
                       class="w-full rounded-xl py-2.5 px-4 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 transition login-input">
                <p id="emailError" class="text-xs text-rose-500 dark:text-rose-400 font-semibold mt-1.5 hidden flex items-center">
                    <svg class="w-3.5 h-3.5 mr-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="error-text"></span>
                </p>
            </div>

            <div>
                <label for="password" class="block text-xs font-bold uppercase mb-1 login-label">PASSWORD</label>
                <div class="relative">
                    <input type="password" id="password" name="password" value="" placeholder="••••••••" autocomplete="current-password" required
                           class="w-full rounded-xl py-2.5 pl-4 pr-10 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 transition login-input">
                    <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300">
                        <svg id="eyeIconOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg id="eyeIconClose" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                        </svg>
                    </button>
                </div>
                <p id="passwordError" class="text-xs text-rose-500 dark:text-rose-400 font-semibold mt-1.5 hidden flex items-center">
                    <svg class="w-3.5 h-3.5 mr-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="error-text"></span>
                </p>
            </div>

            <button type="submit" id="submitBtn"
                    class="w-full bg-[#1E73BE] hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-xl shadow-md transition duration-150 ease-in-out text-sm flex items-center justify-center space-x-2">
                <span>Sign In to Dashboard</span>
            </button>
        </form>
        
        <div class="text-center text-xs mt-4 pt-2 border-t login-footer-text">
            Restricted access portal. Registered PWW accounts only.
        </div>
    </div>

    <!-- jQuery AJAX Validation Script -->
    <script>
        $(document).ready(function () {
            const $email = $('#email');
            const $password = $('#password');
            const $emailError = $('#emailError');
            const $passwordError = $('#passwordError');
            const $alertContainer = $('#alertContainer');
            const $submitBtn = $('#submitBtn');
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            // Flag to track whether the submit button has been clicked
            let formSubmitted = false;

            // Helper to set field error state
            function setFieldError($field, $errorEl, message) {
                if (message) {
                    $field.addClass('border-rose-400 focus:ring-rose-500 bg-rose-50/20')
                          .removeClass('border-slate-200 focus:ring-blue-500');
                    $errorEl.find('.error-text').text(message);
                    $errorEl.removeClass('hidden');
                } else {
                    $field.removeClass('border-rose-400 focus:ring-rose-500 bg-rose-50/20')
                          .addClass('border-slate-200 focus:ring-blue-500');
                    $errorEl.addClass('hidden').find('.error-text').text('');
                }
            }

            // Email Validation logic
            function validateEmail() {
                const val = ($email.val() || '').toString().trim();
                if (!val) {
                    setFieldError($email, $emailError, 'Email address is required.');
                    return false;
                } else if (!emailRegex.test(val)) {
                    setFieldError($email, $emailError, 'Please enter a valid email address.');
                    return false;
                } else {
                    setFieldError($email, $emailError, '');
                    return true;
                }
            }

            // Password Validation logic
            function validatePassword() {
                const val = $password.val();
                if (!val) {
                    setFieldError($password, $passwordError, 'Password is required.');
                    return false;
                } else if (val.length < 6) {
                    setFieldError($password, $passwordError, 'Password must be at least 6 characters.');
                    return false;
                } else {
                    setFieldError($password, $passwordError, '');
                    return true;
                }
            }

            // Bind input & blur handlers using jQuery
            $email.on('input blur', function () {
                $alertContainer.addClass('hidden').empty();
                if (formSubmitted) {
                    validateEmail();
                } else {
                    setFieldError($email, $emailError, '');
                }
            });

            $password.on('input blur', function () {
                $alertContainer.addClass('hidden').empty();
                if (formSubmitted) {
                    validatePassword();
                } else {
                    setFieldError($password, $passwordError, '');
                }
            });

            // Toggle Password Visibility
            $('#togglePassword').on('click', function () {
                const currentType = $password.attr('type');
                if (currentType === 'password') {
                    $password.attr('type', 'text');
                    $('#eyeIconOpen').addClass('hidden');
                    $('#eyeIconClose').removeClass('hidden');
                } else {
                    $password.attr('type', 'password');
                    $('#eyeIconOpen').removeClass('hidden');
                    $('#eyeIconClose').addClass('hidden');
                }
            });

            // Handle Form Submission via jQuery AJAX
            $('#loginForm').on('submit', function (e) {
                e.preventDefault();

                formSubmitted = true;

                $alertContainer.addClass('hidden').removeClass('alert-success alert-danger').empty();

                const isEmailValid = validateEmail();
                const isPasswordValid = validatePassword();

                if (!isEmailValid || !isPasswordValid) {
                    if (!isEmailValid) $email.focus();
                    else if (!isPasswordValid) $password.focus();
                    return false;
                }

                $submitBtn.prop('disabled', true)
                          .addClass('opacity-75 cursor-not-allowed')
                          .html(`
                              <svg class="animate-spin h-5 w-5 mr-3 text-white inline" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                              </svg>
                              <span>Authenticating...</span>
                          `);

                $.ajax({
                    url: '/login',
                    method: 'POST',
                    contentType: 'application/json',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': $('input[name="_token"]').val()
                    },
                    data: JSON.stringify({
                        email: ($email.val() || '').toString().trim(),
                        password: $password.val(),
                        remember: $('input[name="remember"]').is(':checked')
                    }),
                    success: function (res) {
                        if (res.success) {
                            $alertContainer.removeClass('hidden alert-danger')
                                           .addClass('alert-success p-4 rounded-xl border text-sm font-semibold flex items-center')
                                           .html(`
                                               <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                               <span>${res.message || 'Authentication successful! Redirecting...'}</span>
                                           `);

                            window.location.href = res.redirect || '/overview';
                        } else {
                            showGlobalErrors(res.errors || ['Authentication failed. Please try again.']);
                            resetSubmitBtn();
                        }
                    },
                    error: function (xhr) {
                        resetSubmitBtn();
                        if (xhr.status === 403 && xhr.responseJSON && xhr.responseJSON.redirect) {
                            window.location.href = xhr.responseJSON.redirect;
                            return;
                        }

                        if (xhr.status === 419) {
                            $.get('/login', function (html) {
                                const newToken = $(html).find('input[name="_token"]').val();
                                if (newToken) {
                                    $('input[name="_token"]').val(newToken);
                                    $loginForm.submit();
                                } else {
                                    window.location.reload();
                                }
                            }).fail(function () {
                                window.location.reload();
                            });
                            return;
                        }

                        let errors = [];

                        if (xhr.responseJSON) {
                            const resObj = xhr.responseJSON;
                            if (resObj.errors) {
                                const errObj = resObj.errors;
                                if (Array.isArray(errObj)) {
                                    errors = errObj;
                                } else if (typeof errObj === 'object') {
                                    Object.keys(errObj).forEach(key => {
                                        if (Array.isArray(errObj[key])) {
                                            errors.push(...errObj[key]);
                                        } else {
                                            errors.push(errObj[key]);
                                        }

                                        if (key === 'email') setFieldError($email, $emailError, errObj[key][0] || errObj[key]);
                                        if (key === 'password') setFieldError($password, $passwordError, errObj[key][0] || errObj[key]);
                                    });
                                } else if (typeof errObj === 'string') {
                                    errors.push(errObj);
                                }
                            }
                            if (errors.length === 0 && resObj.message) {
                                errors.push(resObj.message);
                            }
                        }

                        if (errors.length === 0) {
                            errors = ['Authentication failed. Please check your credentials and network connection.'];
                        }

                        showGlobalErrors(errors);
                    }
                });
            });

            function showGlobalErrors(errors) {
                let listHtml = '<ul class="list-disc list-inside space-y-1 mt-1">';
                errors.forEach(function (err) {
                    listHtml += `<li>${err}</li>`;
                });
                listHtml += '</ul>';

                $alertContainer.removeClass('hidden alert-success')
                               .addClass('alert-danger p-4 rounded-xl border text-sm')
                               .html(`<strong>Authentication Failed:</strong> ${listHtml}`);
            }

            function resetSubmitBtn() {
                $submitBtn.prop('disabled', false)
                          .removeClass('opacity-75 cursor-not-allowed')
                          .html('<span>Sign In to Dashboard</span>');
            }
        });
    </script>
</body>
</html>
