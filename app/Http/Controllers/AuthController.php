<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Show the login page.
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('overview');
        }
        return view('auth.login');
    }

    /**
     * Handle AJAX login request with Rate Limiting protection.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()->all()
            ], 422);
        }

        // Throttle key based on combined lowercased Email + IP address
        $throttleKey = Str::transliterate(Str::lower($request->input('email')) . '|' . $request->ip());

        // Check if rate limit exceeded (Max 5 attempts per 1 minute)
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $message = "Too many login attempts. Please try again in {$seconds} seconds.";

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'errors' => [$message]
                ], 429);
            }

            return redirect()->back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => $message]);
        }

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            /** @var \App\Models\User $user */
            $user = Auth::user();

            // Super Admin (Owner) is ALWAYS 100% active & exempt from all deactivation checks
            if ($user->role !== 'super_admin') {
                // 1. Check if user account itself is active
                if (!$user->is_active || !in_array($user->status, ['active', 'approved'])) {
                    Auth::logout();
                    $msg = 'Your user account has been deactivated or is pending administrator approval.';
                    
                    $request->session()->put([
                        'deactivated_reason' => $msg,
                        'deactivated_email' => $user->email,
                        'deactivated_name' => $user->name,
                        'deactivated_role' => ucfirst(str_replace('_', ' ', (string) $user->role)),
                    ]);

                    if ($request->expectsJson() || $request->ajax()) {
                        return response()->json([
                            'success' => false,
                            'redirect' => route('account.deactivated'),
                            'message' => $msg
                        ], 403);
                    }
                    return redirect()->route('account.deactivated');
                }

                // 2. Check if assigned user role is active
                $roleRecord = \App\Models\Role::where('slug', $user->role)->first();
                if ($roleRecord && !$roleRecord->is_active) {
                    Auth::logout();
                    $roleName = $roleRecord->name ?? ucfirst(str_replace('_', ' ', (string) $user->role));
                    $msg = "The '{$roleName}' role has been temporarily deactivated by administrator.";
                    
                    $request->session()->put([
                        'deactivated_reason' => $msg,
                        'deactivated_email' => $user->email,
                        'deactivated_name' => $user->name,
                        'deactivated_role' => $roleName,
                    ]);

                    if ($request->expectsJson() || $request->ajax()) {
                        return response()->json([
                            'success' => false,
                            'redirect' => route('account.deactivated'),
                            'message' => $msg
                        ], 403);
                    }
                    return redirect()->route('account.deactivated');
                }
            }

            $request->session()->regenerate();
            RateLimiter::clear($throttleKey);

            \App\Services\AuditLogService::log('Auth', 'login', "User '{$user->name}' logged in successfully.");

            $redirectUrl = route('overview');

            return response()->json([
                'success' => true,
                'message' => 'Login successful! Redirecting...',
                'redirect' => $redirectUrl
            ]);
        }

        // Increment rate limiter counter for failed login attempt (1 minute decay)
        RateLimiter::hit($throttleKey, 60);

        return response()->json([
            'success' => false,
            'errors' => ['These credentials do not match our records.']
        ], 401);
    }

    /**
     * Handle logout.
     */
    public function logout(Request $request)
    {
        \App\Services\AuditLogService::log('Auth', 'logout', "User logged out.");
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully.',
                'redirect' => route('login')
            ]);
        }

        return redirect()->route('login');
    }

    /**
     * Display Account / Role Deactivated notice screen.
     */
    public function accountDeactivated()
    {
        $reason = session('deactivated_reason', 'Your user account or assigned role is currently inactive.');
        $userEmail = session('deactivated_email', '');
        $userName = session('deactivated_name', '');
        $roleName = session('deactivated_role', '');

        return view('auth.account_deactivated', compact('reason', 'userEmail', 'userName', 'roleName'));
    }
}
