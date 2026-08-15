<?php

namespace App\Http\Controllers;

use App\Services\BackupService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Throwable;

class BackupController extends Controller
{
    protected BackupService $backupService;

    public function __construct(BackupService $backupService)
    {
        $this->backupService = $backupService;
    }

    /**
     * Display the Backup & Restore Dashboard page.
     */
    public function index()
    {
        // Ensure automatic backup catch-up check runs when accessing dashboard
        $this->backupService->ensureAutomaticBackupExists();

        $backups = $this->backupService->listLocalBackups();

        // Generate list of Financial Years for dropdown (e.g. 2026-27, 2025-26, 2024-25)
        $currentYear = Carbon::now()->year;
        if (Carbon::now()->month < 4) {
            $currentYear--;
        }

        $financialYears = [];
        for ($i = 0; $i < 5; $i++) {
            $y1 = $currentYear - $i;
            $y2 = substr((string) ($y1 + 1), -2);
            $financialYears[] = [
                'key' => "{$y1}-{$y2}",
                'label' => "FY {$y1}–{$y2} (Apr 1, {$y1} - Mar 31, ".($y1 + 1).')',
            ];
        }

        $systemHealth = \App\Services\SystemHealthService::getDatabaseMetrics();
        $tableCounts = \App\Services\SystemHealthService::getTableRecordCounts();

        return view('pages.backup', compact('backups', 'financialYears', 'systemHealth', 'tableCounts'));
    }

    /**
     * Get JSON list of stored local backups for AJAX table updates.
     */
    public function listJson()
    {
        $backups = $this->backupService->listLocalBackups();

        return response()->json([
            'success' => true,
            'count' => count($backups),
            'backups' => $backups,
        ]);
    }

    /**
     * Download Full Database SQL Backup.
     */
    public function downloadFull()
    {
        try {
            $sqlContent = $this->backupService->generateFullSqlDump();
            $filename = 'pww_full_backup_'.Carbon::now()->format('Ymd_His').'.sql';

            // Also save copy in storage/app/backups
            $filePath = $this->backupService->getBackupDirectory().DIRECTORY_SEPARATOR.$filename;
            File::put($filePath, $sqlContent);

            return response()->download($filePath, $filename, [
                'Content-Type' => 'application/octet-stream',
                'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            ]);
        } catch (Throwable $e) {
            Log::error('Full Backup Download Failed: '.$e->getMessage());

            return back()->with('error', 'Failed to generate full backup. Please try again.');
        }
    }

    /**
     * Download Period-Filtered SQL Backup.
     */
    public function downloadFiltered(Request $request)
    {
        $request->validate([
            'period_type' => 'required|string|in:current_month,specific_month,financial_year,custom,all_time',
            'month' => 'nullable|string',
            'financial_year' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        try {
            $result = $this->backupService->generateFilteredSqlDump(
                $request->period_type,
                $request->month,
                $request->financial_year,
                $request->start_date,
                $request->end_date
            );

            $filename = $result['filename'];
            $sqlContent = $result['content'];

            // Also save copy in storage/app/backups
            $filePath = $this->backupService->getBackupDirectory().DIRECTORY_SEPARATOR.$filename;
            File::put($filePath, $sqlContent);

            return response()->download($filePath, $filename, [
                'Content-Type' => 'application/octet-stream',
                'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            ]);
        } catch (Throwable $e) {
            Log::error('Filtered Backup Download Failed: '.$e->getMessage());

            return back()->with('error', 'Failed to generate filtered backup. Please try again.');
        }
    }

    /**
     * Restore database from uploaded SQL file.
     */
    public function restore(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|mimes:sql,txt,plain|max:51200', // max 50MB
        ]);

        try {
            $file = $request->file('backup_file');
            $tempPath = $file->getRealPath();

            $this->backupService->restoreFromSqlFile($tempPath);

            return back()->with('success', 'Database restored successfully! A safety snapshot of your previous state was automatically saved before restoration.');
        } catch (Throwable $e) {
            Log::error('Database Restore Failed: '.$e->getMessage());

            return back()->with('error', 'Database restoration failed. Please try again.');
        }
    }

    /**
     * Download stored backup file from server disk.
     */
    public function downloadFile(string $filename)
    {
        $filePath = $this->backupService->getBackupDirectory().DIRECTORY_SEPARATOR.basename($filename);

        if (! File::exists($filePath)) {
            return back()->with('error', 'Backup file not found.');
        }

        return response()->download($filePath);
    }

    /**
     * Delete stored backup file from server disk.
     */
    public function deleteFile(Request $request, string $filename)
    {
        $filePath = $this->backupService->getBackupDirectory().DIRECTORY_SEPARATOR.basename($filename);

        if (File::exists($filePath)) {
            File::delete($filePath);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Backup file '{$filename}' deleted successfully.",
                ]);
            }

            return back()->with('success', "Backup file '{$filename}' deleted successfully.");
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'Backup file not found.',
            ], 404);
        }

        return back()->with('error', 'Backup file not found.');
    }

    /**
     * One-Click Database Optimization (Vacuum & Index Defrag).
     */
    public function optimizeDatabase(Request $request)
    {
        $user = auth()->user();
        if (! $user || ! in_array(strtolower($user->role ?? ''), ['super_admin', 'admin', 'administrator', 'owner'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Admin access required to optimize database.',
            ], 403);
        }

        $res = \App\Services\SystemHealthService::optimizeDatabase();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($res);
        }

        return back()->with($res['success'] ? 'success' : 'error', $res['message']);
    }

    /**
     * Factory Reset / Fresh System Wipe with Admin Password Authentication.
     */
    public function resetSystem(Request $request)
    {
        $user = auth()->user();
        if (! $user || ! in_array(strtolower($user->role ?? ''), ['super_admin', 'admin', 'administrator', 'owner'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Only Super Admin can perform a factory reset.',
            ], 403);
        }

        $request->validate([
            'admin_password' => 'required|string',
            'confirm_phrase' => 'required|string|in:RESET,reset',
            'keep_masters' => 'nullable|boolean',
        ], [
            'admin_password.required' => 'Admin password is required to authorize factory reset.',
            'confirm_phrase.in' => "Please type 'RESET' exactly to confirm system wipe.",
        ]);

        if (! \Illuminate\Support\Facades\Hash::check($request->admin_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Incorrect Admin Password. Factory Reset authorization failed.',
                'errors' => ['admin_password' => ['Incorrect Admin password.']],
            ], 422);
        }

        try {
            $keepMasters = $request->boolean('keep_masters', true);
            $result = \App\Services\SystemResetService::resetSystemData($keepMasters);

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'redirect' => route('overview'),
            ]);
        } catch (Throwable $e) {
            Log::error('Factory Reset Failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Factory Reset failed: '.$e->getMessage(),
            ], 500);
        }
    }
}
