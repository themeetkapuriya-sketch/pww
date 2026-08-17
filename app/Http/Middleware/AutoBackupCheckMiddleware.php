<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use App\Services\BackupService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class AutoBackupCheckMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip middleware checks on authentication/logout/export routes for instant performance
        if ($request->is('logout') || $request->routeIs('logout') || $request->is('login') || $request->is('invoices/*/export-eway-json') || $request->routeIs('invoice.exportEwayJson')) {
            return $next($request);
        }

        if (Auth::check()) {
            // 1. Enforce Configured Session Inactivity Timeout
            $timeoutMinutes = (int) Setting::get('session_timeout_minutes', '120');
            $lastActivity = Session::get('last_activity_time');
            $currentTime = time();

            if ($lastActivity && ($currentTime - $lastActivity) > ($timeoutMinutes * 60)) {
                Auth::logout();
                Session::flush();

                if ($request->expectsJson() || $request->ajax() || $request->hasHeader('X-PWW-SPA')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Session expired due to inactivity. Please log in again.',
                        'redirect' => route('login'),
                    ], 401);
                }

                return redirect()->route('login')->with('error', 'Session expired due to inactivity. Please log in again.');
            }

            Session::put('last_activity_time', $currentTime);
        }

        return $next($request);
    }

    /**
     * Handle tasks AFTER the HTTP response has already been sent to the client browser.
     * Keeps user navigation and logout instantaneous (< 50ms).
     */
    public function terminate(Request $request, Response $response): void
    {
        // Skip background backup checks on auth/logout/export routes
        if ($request->is('logout') || $request->routeIs('logout') || $request->is('login') || $request->is('invoices/*/export-eway-json') || $request->routeIs('invoice.exportEwayJson')) {
            return;
        }

        if (Auth::check()) {
            // Throttle backup verification check to at most once every 30 minutes for maximum request throughput
            $cacheKey = 'pww_auto_backup_last_check';
            if (! \Illuminate\Support\Facades\Cache::has($cacheKey)) {
                try {
                    app(BackupService::class)->ensureAutomaticBackupExists();
                    \Illuminate\Support\Facades\Cache::put($cacheKey, true, now()->addMinutes(30));
                } catch (\Throwable $e) {
                    // Silently ignore background backup errors to preserve system availability
                }
            }
        }
    }
}
