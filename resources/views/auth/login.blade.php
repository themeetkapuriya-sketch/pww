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
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- jQuery CDN -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
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
                <p class="text-xs text-blue-600 font-bold uppercase tracking-widest mt-1">ERP Secure Login Portal</p>
            </div>
        </div>

        <!-- Alert Container -->
        <div id="alertContainer" class="hidden text-sm p-4 rounded-xl border transition-all duration-200"></div>

        <!-- Login Form -->
        <form id="loginForm" class="space-y-4" novalidate>
            @csrf
            <div>
                <label for="email" class="block text-xs font-bold text-slate-600 uppercase mb-1">EMAIL ADDRESS</label>
                <input type="email" id="email" name="email" value="" placeholder="e.g. pww@example.com"
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                        </svg>
                    </button>
                </div>
                <p id="passwordError" class="text-xs text-rose-500 font-semibold mt-1.5 hidden flex items-center">
                    <svg class="w-3.5 h-3.5 mr-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="error-text"></span>
                </p>
            </div>

            <div class="flex items-center justify-between text-xs">
                <label class="flex items-center text-slate-500 font-medium cursor-pointer select-none">
                    <input type="checkbox" name="remember" class="mr-1.5 rounded border-slate-300 text-blue-600 focus:ring-blue-500"> Remember this device
                </label>
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
                const val = $.trim($email.val());
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
                        email: $.trim($email.val()),
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

                            setTimeout(function () {
                                window.location.href = res.redirect || '/overview';
                            }, 500);
                        } else {
                            showGlobalErrors(res.errors || ['Authentication failed. Please try again.']);
                            resetSubmitBtn();
                        }
                    },
                    error: function (xhr) {
                        resetSubmitBtn();
                        let errors = [];

                        if (xhr.status === 422 && xhr.responseJSON) {
                            const errObj = xhr.responseJSON.errors || {};
                            if (Array.isArray(errObj)) {
                                errors = errObj;
                            } else if (typeof errObj === 'object') {
                                Object.keys(errObj).forEach(key => {
                                    if (Array.isArray(errObj[key])) {
                                        errors.push(...errObj[key]);
                                    } else {
                                        errors.push(errObj[key]);
                                    }

                                    // Mark inline error if key matches field
                                    if (key === 'email') setFieldError($email, $emailError, errObj[key][0] || errObj[key]);
                                    if (key === 'password') setFieldError($password, $passwordError, errObj[key][0] || errObj[key]);
                                });
                            }
                        } else if (xhr.status === 401 && xhr.responseJSON && xhr.responseJSON.errors) {
                            errors = xhr.responseJSON.errors;
                        } else {
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
