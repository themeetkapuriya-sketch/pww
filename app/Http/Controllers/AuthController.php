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
            $request->session()->regenerate();
            
            // Clear rate limiter counter on successful login
            RateLimiter::clear($throttleKey);

            return response()->json([
                'success' => true,
                'message' => 'Login successful! Redirecting...',
                'redirect' => route('overview')
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
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->ajax()) {
            return response()->json(['success' => true, 'redirect' => route('login')]);
        }

        return redirect()->route('login');
    }
}
