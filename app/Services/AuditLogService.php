<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class AuditLogService
{
    /**
     * Record a system activity audit log entry.
     *
     * @param  string  $module  (e.g. Invoices, Purchases, Inventory, Expenses, Payroll, Clients, Settings, Auth)
     * @param  string  $action  (e.g. created, updated, deleted, login, logout, security)
     */
    public static function log(string $module, string $action, string $description, ?array $changes = null): ?ActivityLog
    {
        try {
            $user = Auth::user();
            $req = request();

            return ActivityLog::create([
                'user_id' => $user ? $user->id : null,
                'user_name' => $user ? ($user->name ?? $user->email) : 'System',
                'user_role' => $user ? ($user->role ?? 'user') : 'system',
                'module' => $module,
                'action' => strtolower($action),
                'description' => $description,
                'changes' => $changes,
                'ip_address' => $req ? $req->ip() : null,
                'user_agent' => $req ? substr($req->userAgent() ?? '', 0, 255) : null,
            ]);
        } catch (Throwable $e) {
            Log::error('AuditLogService failure: '.$e->getMessage());

            return null;
        }
    }
}
