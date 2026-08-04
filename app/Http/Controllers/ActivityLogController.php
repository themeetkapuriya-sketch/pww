<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ActivityLogController extends Controller
{
    /**
     * Check if current user is Super Admin.
     */
    private function authorizeSuperAdmin()
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'super_admin') {
            abort(403, 'Unauthorized access. Activity Audit Logs are restricted exclusively to Super Admins.');
        }
    }

    /**
     * Display Super-Admin Activity Audit Logs viewer.
     */
    public function index(Request $request)
    {
        $this->authorizeSuperAdmin();

        $query = ActivityLog::query()->orderBy('created_at', 'desc');

        // Search Filter
        if ($request->filled('q')) {
            $search = '%' . trim($request->q) . '%';
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', $search)
                  ->orWhere('user_name', 'like', $search)
                  ->orWhere('module', 'like', $search)
                  ->orWhere('action', 'like', $search)
                  ->orWhere('ip_address', 'like', $search);
            });
        }

        // Module Filter
        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }

        // Action Filter
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // User Filter
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Date Range Filters
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // KPI Summary Counters
        $today = Carbon::today();
        $totalToday = ActivityLog::whereDate('created_at', $today)->count();
        $highPriorityCount = ActivityLog::whereIn('action', ['deleted', 'security'])->count();
        $superAdminActions = ActivityLog::where('user_role', 'super_admin')->count();

        // Unique modules & users for dropdown filter options
        $modules = ActivityLog::distinct()->pluck('module')->filter()->values();
        $usersList = User::orderBy('name')->get(['id', 'name', 'email', 'role']);

        $logs = $query->paginate(25)->withQueryString();

        return view('pages.activity_logs', compact(
            'logs',
            'totalToday',
            'highPriorityCount',
            'superAdminActions',
            'modules',
            'usersList'
        ));
    }

    /**
     * Export Audit Trail to CSV file.
     */
    public function exportCsv(Request $request)
    {
        $this->authorizeSuperAdmin();

        $query = ActivityLog::query()->orderBy('created_at', 'desc');

        if ($request->filled('q')) {
            $search = '%' . trim($request->q) . '%';
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', $search)
                  ->orWhere('user_name', 'like', $search)
                  ->orWhere('module', 'like', $search)
                  ->orWhere('action', 'like', $search);
            });
        }
        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $logs = $query->limit(5000)->get();

        $filename = 'audit_logs_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($logs) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Date & Time', 'User Name', 'Role', 'Module', 'Action', 'Description', 'IP Address', 'User Agent']);

            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->created_at->format('Y-m-d H:i:s'),
                    $log->user_name,
                    strtoupper($log->user_role),
                    $log->module,
                    strtoupper($log->action),
                    $log->description,
                    $log->ip_address,
                    $log->user_agent
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
