<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use App\Models\Role;
use App\Services\AuditLogService;

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
     * Handle fast & secure login request with Rate Limiting protection.
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

        // Throttle key based on combined lowercased Email + IP address (Max 5 attempts per 60 seconds)
        $throttleKey = Str::transliterate(Str::lower($request->input('email')) . '|' . $request->ip());

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
        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            /** @var \App\Models\User $user */
            $user = Auth::user();

            // Check Account & Role Active Status (Super Admin is always exempt)
            if ($user->role !== 'super_admin') {
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

                $roleRecord = Role::where('slug', $user->role)->first();
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

            // Secure session regeneration & rate limiter reset
            $request->session()->regenerate();
            RateLimiter::clear($throttleKey);

            AuditLogService::log('Auth', 'login', "User '{$user->name}' logged in successfully.");

            $redirectUrl = route('overview');

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Login successful!',
                    'redirect' => $redirectUrl
                ]);
            }

            return redirect()->intended($redirectUrl);
        }

        // Hit Rate Limiter on failed authentication
        RateLimiter::hit($throttleKey, 60);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'errors' => ['These credentials do not match our records.']
            ], 401);
        }

        return redirect()->back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => 'These credentials do not match our records.']);
    }

    /**
     * Handle fast & secure logout request.
     */
    public function logout(Request $request)
    {
        if (Auth::check()) {
            AuditLogService::log('Auth', 'logout', "User logged out.");
        }

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
