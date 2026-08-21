<?php

namespace App\Services;

use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Throwable;

class BackupService
{
    protected string $backupDirectory;

    public function __construct()
    {
        $customPath = env('BACKUP_PATH');
        $resolvedPath = storage_path('app/backups');

        if (! empty($customPath)) {
            try {
                if (! File::exists($customPath)) {
                    File::makeDirectory($customPath, 0755, true);
                }
                if (File::isDirectory($customPath) && is_writable($customPath)) {
                    $resolvedPath = $customPath;
                }
            } catch (Throwable $e) {
                Log::warning("Custom BACKUP_PATH ({$customPath}) inaccessible: ".$e->getMessage().". Falling back to default storage/app/backups");
            }
        }

        if (! File::exists($resolvedPath)) {
            try {
                File::makeDirectory($resolvedPath, 0755, true);
            } catch (Throwable $e) {
                Log::error('Could not create default backup directory: '.$e->getMessage());
            }
        }

        $this->backupDirectory = $resolvedPath;
    }

    /**
     * Get path to the backup storage directory.
     */
    public function getBackupDirectory(): string
    {
        return $this->backupDirectory;
    }

    /**
     * Generate complete database SQL dump.
     */
    public function generateFullSqlDump(): string
    {
        $driver = DB::connection()->getDriverName();
        $sqlOutput = "-- PWW ERP Full Database Backup\n";
        $sqlOutput .= '-- Generated: '.Carbon::now()->toDateTimeString()."\n";
        $sqlOutput .= '-- Database: '.DB::getDatabaseName()."\n\n";
        $sqlOutput .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        $tables = $this->getDatabaseTables($driver);

        foreach ($tables as $tableName) {
            $createTableSql = $this->getCreateTableSql($driver, $tableName);
            $sqlOutput .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
            $sqlOutput .= $createTableSql.";\n\n";

            $rows = DB::table($tableName)->get();
            if ($rows->count() > 0) {
                foreach ($rows->chunk(100) as $chunk) {
                    $insertSql = "INSERT INTO `{$tableName}` VALUES ";
                    $valueStrings = [];
                    foreach ($chunk as $row) {
                        $values = array_map(function ($value) use ($driver) {
                            if (is_null($value)) {
                                return 'NULL';
                            }
                            if ($driver === 'sqlite') {
                                return "'".str_replace("'", "''", $value)."'";
                            }

                            return DB::connection()->getPdo()->quote($value);
                        }, (array) $row);
                        $valueStrings[] = '('.implode(', ', $values).')';
                    }
                    $insertSql .= implode(",\n", $valueStrings).";\n";
                    $sqlOutput .= $insertSql;
                }
                $sqlOutput .= "\n";
            }
        }

        $sqlOutput .= "SET FOREIGN_KEY_CHECKS=1;\n";

        return $sqlOutput;
    }

    /**
     * Generate Period Filtered SQL Data export.
     */
    public function generateFilteredSqlDump(string $periodType, ?string $month = null, ?string $financialYear = null, ?string $startDate = null, ?string $endDate = null): array
    {
        $driver = DB::connection()->getDriverName();
        $dateRange = $this->calculateDateRange($periodType, $month, $financialYear, $startDate, $endDate);
        $from = $dateRange['start'];
        $to = $dateRange['end'];

        $sqlOutput = "-- PWW ERP Filtered Data Backup\n";
        $sqlOutput .= '-- Period Type: '.strtoupper($periodType)."\n";
        $sqlOutput .= '-- Range: '.$from->toDateString().' to '.$to->toDateString()."\n";
        $sqlOutput .= '-- Generated: '.Carbon::now()->toDateTimeString()."\n\n";
        $sqlOutput .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        $tables = $this->getDatabaseTables($driver);

        foreach ($tables as $tableName) {
            // Skip queue/session internal tables for filtered backup
            if (in_array($tableName, ['jobs', 'job_batches', 'failed_jobs', 'sessions', 'cache', 'cache_locks'])) {
                continue;
            }

            $createTableSql = $this->getCreateTableSql($driver, $tableName);
            $sqlOutput .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
            $sqlOutput .= $createTableSql.";\n\n";

            $query = DB::table($tableName);

            // Filter transaction tables by their primary date columns
            if (in_array($tableName, ['invoices'])) {
                $query->whereBetween('invoice_date', [$from->toDateString(), $to->toDateString()]);
            } elseif (in_array($tableName, ['invoice_items'])) {
                $invoiceIds = DB::table('invoices')->whereBetween('invoice_date', [$from->toDateString(), $to->toDateString()])->pluck('id');
                $query->whereIn('invoice_id', $invoiceIds);
            } elseif (in_array($tableName, ['expenses'])) {
                $query->whereBetween('expense_date', [$from->toDateString(), $to->toDateString()]);
            } elseif (in_array($tableName, ['production_logs'])) {
                $query->whereBetween('production_date', [$from->toDateString(), $to->toDateString()]);
            } elseif (in_array($tableName, ['labor_logs'])) {
                $query->whereBetween(DB::raw('DATE(created_at)'), [$from->toDateString(), $to->toDateString()]);
            } elseif (in_array($tableName, ['sales_orders'])) {
                $query->whereBetween('order_date', [$from->toDateString(), $to->toDateString()]);
            } elseif (in_array($tableName, ['sales_order_items'])) {
                $orderIds = DB::table('sales_orders')->whereBetween('order_date', [$from->toDateString(), $to->toDateString()])->pluck('id');
                $query->whereIn('sales_order_id', $orderIds);
            }

            $rows = $query->get();
            if ($rows->count() > 0) {
                foreach ($rows->chunk(100) as $chunk) {
                    $insertSql = "INSERT INTO `{$tableName}` VALUES ";
                    $valueStrings = [];
                    foreach ($chunk as $row) {
                        $values = array_map(function ($value) use ($driver) {
                            if (is_null($value)) {
                                return 'NULL';
                            }
                            if ($driver === 'sqlite') {
                                return "'".str_replace("'", "''", $value)."'";
                            }

                            return DB::connection()->getPdo()->quote($value);
                        }, (array) $row);
                        $valueStrings[] = '('.implode(', ', $values).')';
                    }
                    $insertSql .= implode(",\n", $valueStrings).";\n";
                    $sqlOutput .= $insertSql;
                }
                $sqlOutput .= "\n";
            }
        }

        $sqlOutput .= "SET FOREIGN_KEY_CHECKS=1;\n";

        return [
            'content' => $sqlOutput,
            'filename' => "pww_backup_{$periodType}_".$from->format('Ymd').'_to_'.$to->format('Ymd').'.sql',
            'start_date' => $from->toDateString(),
            'end_date' => $to->toDateString(),
        ];
    }

    /**
     * Get table list across MySQL and SQLite.
     */
    protected function getDatabaseTables(string $driver): array
    {
        if ($driver === 'sqlite') {
            $rows = DB::select("SELECT name AS table_name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");

            return array_map(fn ($r) => $r->table_name, $rows);
        }

        $dbNameKey = 'Tables_in_'.DB::getDatabaseName();
        $rows = DB::select('SHOW TABLES');

        return array_map(fn ($r) => $r->$dbNameKey, $rows);
    }

    /**
     * Get CREATE TABLE DDL string across MySQL and SQLite.
     */
    protected function getCreateTableSql(string $driver, string $tableName): string
    {
        if ($driver === 'sqlite') {
            $stmt = DB::selectOne("SELECT sql FROM sqlite_master WHERE type='table' AND name = ?", [$tableName]);

            return $stmt ? $stmt->sql : "CREATE TABLE `{$tableName}` (id INTEGER PRIMARY KEY)";
        }

        $stmt = DB::selectOne("SHOW CREATE TABLE `{$tableName}`");

        return $stmt->{'Create Table'} ?? "CREATE TABLE `{$tableName}`";
    }

    /**
     * Calculate start and end Carbon dates based on period filter options.
     */
    public function calculateDateRange(string $periodType, ?string $month = null, ?string $financialYear = null, ?string $startDate = null, ?string $endDate = null): array
    {
        switch ($periodType) {
            case 'current_month':
                return [
                    'start' => Carbon::now()->startOfMonth(),
                    'end' => Carbon::now()->endOfMonth(),
                ];

            case 'specific_month':
                $date = $month ? Carbon::parse($month.'-01') : Carbon::now();

                return [
                    'start' => $date->copy()->startOfMonth(),
                    'end' => $date->copy()->endOfMonth(),
                ];

            case 'financial_year':
                // Financial Year format: "2025-26" or "2025"
                $startYear = (int) ($financialYear ? explode('-', $financialYear)[0] : Carbon::now()->year);
                if (Carbon::now()->month < 4 && empty($financialYear)) {
                    $startYear--;
                }

                return [
                    'start' => Carbon::createFromDate($startYear, 4, 1)->startOfDay(),
                    'end' => Carbon::createFromDate($startYear + 1, 3, 31)->endOfDay(),
                ];

            case 'custom':
                $start = $startDate ? Carbon::parse($startDate)->startOfDay() : Carbon::now()->subMonth()->startOfDay();
                $end = $endDate ? Carbon::parse($endDate)->endOfDay() : Carbon::now()->endOfDay();

                return [
                    'start' => $start,
                    'end' => $end,
                ];

            case 'all_time':
            default:
                return [
                    'start' => Carbon::createFromDate(2026, 4, 1)->startOfDay(),
                    'end' => Carbon::now()->endOfDay(),
                ];
        }
    }

    /**
     * Ensure automatic backup exists based on configured frequency, day, and time settings.
     */
    public function ensureAutomaticBackupExists(): string
    {
        $enabled = Setting::get('auto_backup_enabled', 'true') === 'true';
        if (! $enabled) {
            return '';
        }

        $frequency = Setting::get('auto_backup_frequency', 'monthly');
        $timeStr = Setting::get('auto_backup_time', '18:00');
        $dayName = Setting::get('auto_backup_day', 'Wednesday');

        $now = Carbon::now();
        $timeParts = explode(':', $timeStr);
        $targetHour = (int) ($timeParts[0] ?? 18);
        $targetMin = (int) ($timeParts[1] ?? 0);

        if ($frequency === 'daily') {
            $scheduledToday = $now->copy()->setTime($targetHour, $targetMin, 0);
            $targetDate = $now->greaterThanOrEqualTo($scheduledToday) ? $now->copy() : $now->copy()->subDay();
            $filename = 'auto_backup_daily_'.$targetDate->format('Y_m_d').'.sql';
        } elseif ($frequency === 'weekly') {
            $daysOfWeek = ['Sunday' => 0, 'Monday' => 1, 'Tuesday' => 2, 'Wednesday' => 3, 'Thursday' => 4, 'Friday' => 5, 'Saturday' => 6];
            $targetDayIndex = $daysOfWeek[$dayName] ?? 3;

            $thisWeekTarget = $now->copy()->startOfWeek(0)->addDays($targetDayIndex)->setTime($targetHour, $targetMin, 0);
            $targetDate = $now->greaterThanOrEqualTo($thisWeekTarget) ? $thisWeekTarget : $thisWeekTarget->copy()->subWeek();
            $filename = 'auto_backup_weekly_'.$targetDate->format('Y_m_d').'.sql';
        } else {
            $thisMonthTarget = $now->copy()->startOfMonth()->setTime($targetHour, $targetMin, 0);
            $targetDate = $now->greaterThanOrEqualTo($thisMonthTarget) ? $now->copy() : $now->copy()->subMonth();
            $filename = 'auto_backup_monthly_'.$targetDate->format('Y_m').'.sql';
        }

        $filePath = $this->backupDirectory.DIRECTORY_SEPARATOR.$filename;

        if (! File::exists($filePath)) {
            $sqlContent = $this->generateFullSqlDump();
            File::put($filePath, $sqlContent);
            Log::info("Automatic Catch-Up Backup created successfully ({$frequency}): {$filename}");
            $this->sendBackupEmailNotification($filePath, $filename);
            $this->cleanOldBackups();

            return $filePath;
        }

        return '';
    }

    /**
     * Send backup SQL file as email attachment if enabled.
     */
    public function sendBackupEmailNotification(string $filePath, string $filename): bool
    {
        try {
            $sendEmail = Setting::get('auto_email_backup', 'true') === 'true';
            if (! $sendEmail) {
                return false;
            }

            $toEmail = Setting::get('business_email', 'vekariyah@gmail.com');
            if (empty($toEmail)) {
                return false;
            }

            $businessName = Setting::get('business_name', 'Praful Welding Works');

            Mail::raw(
                "Hello,\n\nAn automated database backup snapshot '{$filename}' has been generated for {$businessName}.\n\nThe backup SQL file is attached to this email for off-site data safety.\n\nBest regards,\n{$businessName} ERP System",
                function ($message) use ($toEmail, $businessName, $filePath, $filename) {
                    $message->to($toEmail)
                        ->subject("📦 Automated Database Backup: {$filename} - {$businessName}")
                        ->attach($filePath, ['as' => $filename, 'mime' => 'text/plain']);
                }
            );

            Log::info("Backup email sent to {$toEmail} with attachment {$filename}");

            return true;
        } catch (Throwable $e) {
            Log::error('Failed to send backup email attachment: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Auto-purge old backup files based on retention policy setting (keeping latest 1 intact).
     */
    public function cleanOldBackups(): int
    {
        $retention = Setting::get('auto_backup_retention', '3_months');
        if ($retention === 'never') {
            return 0;
        }

        $now = Carbon::now();
        $cutoff = match ($retention) {
            '1_month' => $now->copy()->subDays(30),
            '6_months' => $now->copy()->subDays(180),
            '1_year' => $now->copy()->subDays(365),
            default => $now->copy()->subDays(90), // 3_months
        };

        $files = File::files($this->backupDirectory);
        $sqlFiles = [];
        foreach ($files as $file) {
            if ($file->getExtension() === 'sql') {
                $sqlFiles[] = [
                    'path' => $file->getPathname(),
                    'mtime' => $file->getMTime(),
                    'name' => $file->getFilename(),
                ];
            }
        }

        // Sort descending by modified time (newest first)
        usort($sqlFiles, fn ($a, $b) => $b['mtime'] <=> $a['mtime']);

        if (count($sqlFiles) <= 1) {
            return 0; // Always keep at least 1 latest backup intact
        }

        $deletedCount = 0;
        // Skip index 0 (the latest backup file)
        for ($i = 1; $i < count($sqlFiles); $i++) {
            if ($sqlFiles[$i]['mtime'] < $cutoff->timestamp) {
                try {
                    File::delete($sqlFiles[$i]['path']);
                    $deletedCount++;
                    Log::info("Auto-Purged old backup file ({$retention}): {$sqlFiles[$i]['name']}");
                } catch (Throwable $e) {
                    Log::error("Failed to auto-purge backup file {$sqlFiles[$i]['name']}: ".$e->getMessage());
                }
            }
        }

        return $deletedCount;
    }

    /**
     * Restore database from an uploaded SQL backup file.
     */
    public function restoreFromSqlFile(string $filePath): void
    {
        if (! File::exists($filePath)) {
            throw new \Exception("Backup file does not exist at: {$filePath}");
        }

        $sql = File::get($filePath);

        if (empty(trim($sql))) {
            throw new \Exception('Backup file is empty or invalid.');
        }

        // Create emergency safety snapshot before restore
        $safetyFilename = 'pre_restore_safety_'.Carbon::now()->format('Ymd_His').'.sql';
        File::put($this->backupDirectory.DIRECTORY_SEPARATOR.$safetyFilename, $this->generateFullSqlDump());

        $driver = DB::connection()->getDriverName();
        try {
            if ($driver !== 'sqlite') {
                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                DB::unprepared($sql);
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            } else {
                DB::unprepared($sql);
            }
            Log::info("Database restored successfully from file: {$filePath}");
        } catch (Throwable $e) {
            Log::error('Database Restoration Failed: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * List all local backups stored in storage/app/backups.
     */
    public function listLocalBackups(): array
    {
        $this->cleanOldBackups();

        $files = File::files($this->backupDirectory);
        $backups = [];

        foreach ($files as $file) {
            if ($file->getExtension() === 'sql') {
                $filename = $file->getFilename();
                $sizeBytes = $file->getSize();
                $sizeFormatted = $sizeBytes >= 1048576
                    ? number_format($sizeBytes / 1048576, 2).' MB'
                    : number_format($sizeBytes / 1024, 2).' KB';

                $type = 'Manual Full';
                if (str_contains($filename, 'auto_backup_monthly')) {
                    $type = 'Automated Monthly';
                } elseif (str_contains($filename, 'auto_backup_weekly')) {
                    $type = 'Automated Weekly';
                } elseif (str_contains($filename, 'auto_backup_daily')) {
                    $type = 'Automated Daily';
                } elseif (str_contains($filename, 'pre_restore_safety')) {
                    $type = 'Safety Snapshot';
                } elseif (str_contains($filename, 'pww_backup_')) {
                    $type = 'Period Filtered';
                }

                $backups[] = [
                    'filename' => $filename,
                    'size' => $sizeFormatted,
                    'created_at' => Carbon::createFromTimestamp($file->getMTime())->setTimezone(config('app.timezone', 'Asia/Kolkata'))->format('d M Y, h:i A'),
                    'timestamp' => $file->getMTime(),
                    'type' => $type,
                    'path' => $file->getPathname(),
                ];
            }
        }

        usort($backups, fn ($a, $b) => $b['timestamp'] <=> $a['timestamp']);

        return $backups;
    }
}
