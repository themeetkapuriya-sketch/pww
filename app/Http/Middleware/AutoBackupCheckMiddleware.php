<?php

namespace App\Http\Middleware;

use App\Services\BackupService;
use Closure;
use Illuminate\Http\Request;
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
        // Run smart local catch-up check once per session / day for authenticated users
        if (\Illuminate\Support\Facades\Auth::check() && !Session::has('monthly_backup_checked')) {
            try {
                $createdPath = app(BackupService::class)->ensureMonthlyBackupExists();
                Session::put('monthly_backup_checked', true);

                if (!empty($createdPath) && \Illuminate\Support\Facades\File::exists($createdPath)) {
                    Session::flash('auto_download_backup_url', route('backup.downloadFile', ['filename' => basename($createdPath)]));
                }
            } catch (\Throwable $e) {
                // Silently ignore middleware check errors to avoid blocking user navigation
            }
        }

        return $next($request);
    }
}
