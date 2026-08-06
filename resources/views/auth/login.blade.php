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
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #EEF2F6;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 relative overflow-hidden">

    <!-- Glassmorphic Login Container Card -->
    <div class="max-w-[440px] w-full bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl p-8 space-y-6 relative z-10 border border-white/60">
        
        <!-- Logo and Heading -->
        <div class="text-center space-y-3 flex flex-col items-center">
            <!-- PWW Brand Image Logo -->
            <div class="p-2 bg-white rounded-2xl border border-slate-100 shadow-sm">
                <img class="h-14 w-14 object-contain rounded-xl" src="{{ asset('logo.jpg') }}" alt="PWW Logo">
            </div>
            <div>
                <h1 class="text-2xl font-black text-slate-800 tracking-tight">Praful Welding Works</h1>
                <p class="text-base text-blue-600 font-extrabold mt-1">Welcome To Admin Panel ! 👋</p>
                <p class="text-sm text-slate-500 font-medium mt-1">Please sign-in to your account and get access</p>
            </div>
        </div>

        <!-- Alert Container -->
        <div id="alertContainer" class="hidden text-sm p-4 rounded-xl border transition-all duration-200"></div>

        <!-- Login Form -->
        <form id="loginForm" action="{{ route('login') }}" method="POST" class="space-y-4" novalidate>
            @csrf
            <div>
                <label for="email" class="block text-xs font-bold text-slate-600 uppercase mb-1">EMAIL ADDRESS</label>
                <input type="email" id="email" name="email" value="" placeholder="e.g. pww@gmail.com"
                       class="w-full bg-slate-50/80 border border-slate-200 rounded-xl py-2.5 px-4 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 transition">
                <p id="emailError" class="text-xs text-rose-500 font-semibold mt-1.5 hidden flex items-center">
                    <svg class="w-3.5 h-3.5 mr-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="error-text"></span>
                </p>
            </div>

            <div>
                <label for="password" class="block text-xs font-bold text-slate-600 uppercase mb-1">PASSWORD</label>
                <div class="relative">
                    <input type="password" id="password" name="password" value="" placeholder="••••••••"
                           class="w-full bg-slate-50/80 border border-slate-200 rounded-xl py-2.5 pl-4 pr-10 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700 transition">
                    <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                        <svg id="eyeIconOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg id="eyeIconClose" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                        </svg>
                    </button>
                </div>
                <p id="passwordError" class="text-xs text-rose-500 font-semibold mt-1.5 hidden flex items-center">
                    <svg class="w-3.5 h-3.5 mr-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="error-text"></span>
                </p>
            </div>


            <button type="submit" id="submitBtn"
                    class="w-full bg-[#1E73BE] hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-xl shadow-md transition duration-150 ease-in-out text-sm flex items-center justify-center space-x-2">
                <span>Sign In to Dashboard</span>
            </button>
        </form>
        
        <div class="text-center text-xs text-slate-400 mt-4 pt-2 border-t border-slate-100/80">
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

            // Bind input & blur handlers using jQuery (only re-validate if submit button was clicked)
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

            // Handle Form Submission via jQuery AJAX (Validation runs ONLY after button click)
            $('#loginForm').on('submit', function (e) {
                e.preventDefault();

                // Set flag indicating submit button was clicked
                formSubmitted = true;

                // Clear general alert box
                $alertContainer.addClass('hidden').removeClass('bg-rose-50 border-rose-200 text-rose-800 bg-emerald-50 border-emerald-200 text-emerald-800').empty();

                // Run validation on button click
                const isEmailValid = validateEmail();
                const isPasswordValid = validatePassword();

                if (!isEmailValid || !isPasswordValid) {
                    if (!isEmailValid) $email.focus();
                    else if (!isPasswordValid) $password.focus();
                    return false;
                }

                // UI Loading state
                $submitBtn.prop('disabled', true)
                          .addClass('opacity-75 cursor-not-allowed')
                          .html(`
                              <svg class="animate-spin h-5 w-5 mr-3 text-white inline" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                              </svg>
                              <span>Authenticating...</span>
                          `);

                // Send jQuery AJAX POST Request
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
                            $alertContainer.removeClass('hidden')
                                           .addClass('bg-emerald-50 border-emerald-200 text-emerald-800 p-4 rounded-xl border text-sm font-semibold flex items-center')
                                           .html(`
                                               <svg class="w-5 h-5 mr-2 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
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
                            // Auto-fetch fresh CSRF token & retry login seamlessly
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

                $alertContainer.removeClass('hidden')
                               .addClass('bg-rose-50 border-rose-200 text-rose-800 p-4 rounded-xl border text-sm')
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
