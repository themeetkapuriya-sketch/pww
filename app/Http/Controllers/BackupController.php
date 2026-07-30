<?php

namespace App\Http\Controllers;

use App\Services\BackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
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
        // Ensure monthly backup catch-up check runs when accessing dashboard
        $this->backupService->ensureMonthlyBackupExists();

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
                'label' => "FY {$y1}–{$y2} (Apr 1, {$y1} - Mar 31, " . ($y1 + 1) . ")"
            ];
        }

        return view('pages.backup', compact('backups', 'financialYears'));
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
            'backups' => $backups
        ]);
    }

    /**
     * Download Full Database SQL Backup.
     */
    public function downloadFull()
    {
        try {
            $sqlContent = $this->backupService->generateFullSqlDump();
            $filename = "pww_full_backup_" . Carbon::now()->format('Ymd_His') . ".sql";

            // Also save copy in storage/app/backups
            $filePath = $this->backupService->getBackupDirectory() . DIRECTORY_SEPARATOR . $filename;
            File::put($filePath, $sqlContent);

            return response()->download($filePath, $filename, [
                'Content-Type' => 'application/octet-stream',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        } catch (Throwable $e) {
            Log::error("Full Backup Download Failed: " . $e->getMessage());
            return back()->with('error', 'Failed to generate full backup: ' . $e->getMessage());
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
            $filePath = $this->backupService->getBackupDirectory() . DIRECTORY_SEPARATOR . $filename;
            File::put($filePath, $sqlContent);

            return response()->download($filePath, $filename, [
                'Content-Type' => 'application/octet-stream',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        } catch (Throwable $e) {
            Log::error("Filtered Backup Download Failed: " . $e->getMessage());
            return back()->with('error', 'Failed to generate filtered backup: ' . $e->getMessage());
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
            Log::error("Database Restore Failed: " . $e->getMessage());
            return back()->with('error', 'Database restoration failed: ' . $e->getMessage());
        }
    }

    /**
     * Download stored backup file from server disk.
     */
    public function downloadFile(string $filename)
    {
        $filePath = $this->backupService->getBackupDirectory() . DIRECTORY_SEPARATOR . basename($filename);

        if (!File::exists($filePath)) {
            return back()->with('error', 'Backup file not found.');
        }

        return response()->download($filePath);
    }

    /**
     * Delete stored backup file from server disk.
     */
    public function deleteFile(Request $request, string $filename)
    {
        $filePath = $this->backupService->getBackupDirectory() . DIRECTORY_SEPARATOR . basename($filename);

        if (File::exists($filePath)) {
            File::delete($filePath);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Backup file '{$filename}' deleted successfully."
                ]);
            }

            return back()->with('success', "Backup file '{$filename}' deleted successfully.");
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'Backup file not found.'
            ], 404);
        }

        return back()->with('error', 'Backup file not found.');
    }
}
