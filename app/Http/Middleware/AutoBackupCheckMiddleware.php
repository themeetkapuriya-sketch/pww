<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use App\Services\BackupService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class AutoBackupCheckMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
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

            // 2. Run background automatic backup catch-up check
            try {
                $createdPath = app(BackupService::class)->ensureAutomaticBackupExists();

                if (!empty($createdPath) && File::exists($createdPath)) {
                    Session::flash('auto_download_backup_url', route('backup.downloadFile', ['filename' => basename($createdPath)]));
                }
            } catch (\Throwable $e) {
                // Silently ignore middleware check errors to avoid blocking user navigation
            }
        }

        return $next($request);
    }
}
