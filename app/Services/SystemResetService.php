<?php

namespace App\Services;

use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Throwable;

class SystemResetService
{
    /**
     * Perform Factory Reset / Fresh System Wipe with emergency backup snapshot.
     */
    public static function resetSystemData(bool $keepMasterData = true): array
    {
        $backupService = app(BackupService::class);
        $safetyFilename = 'pre_reset_safety_'.Carbon::now()->format('Ymd_His').'.sql';

        try {
            // 1. Take emergency safety backup before reset
            $sqlContent = $backupService->generateFullSqlDump();
            $backupPath = $backupService->getBackupDirectory().DIRECTORY_SEPARATOR.$safetyFilename;
            File::put($backupPath, $sqlContent);
            Log::info("Safety backup snapshot recorded before factory reset: {$safetyFilename}");
        } catch (Throwable $e) {
            Log::error('Safety backup before reset failed: '.$e->getMessage());
            throw new \Exception('Could not create safety backup snapshot before resetting. Reset aborted for safety: '.$e->getMessage());
        }

        $driver = DB::connection()->getDriverName();

        try {
            // 2. Disable Foreign Key Checks
            if ($driver !== 'sqlite') {
                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            }

            // 3. Transactional & Operational Tables to Truncate
            $transactionTables = [
                'invoice_items',
                'invoices',
                'sales_order_items',
                'sales_orders',
                'purchase_items',
                'purchases',
                'production_logs',
                'labor_logs',
                'stock_adjustments',
                'payments',
                'expenses',
                'attendance_records',
                'salary_advances',
                'salary_disbursals',
                'salary_payments',
                'activity_logs',
            ];

            foreach ($transactionTables as $table) {
                if (DB::getSchemaBuilder()->hasTable($table)) {
                    if ($driver === 'sqlite') {
                        DB::statement("DELETE FROM `{$table}`;");
                        DB::statement("DELETE FROM sqlite_sequence WHERE name='{$table}';");
                    } else {
                        DB::statement("TRUNCATE TABLE `{$table}`;");
                    }
                }
            }

            // 4. Optionally clear master catalog if requested
            if (! $keepMasterData) {
                $masterTables = [
                    'bill_of_materials',
                    'client_plants',
                    'clients',
                    'products',
                    'raw_materials',
                ];

                foreach ($masterTables as $table) {
                    if (DB::getSchemaBuilder()->hasTable($table)) {
                        if ($driver === 'sqlite') {
                            DB::statement("DELETE FROM `{$table}`;");
                            DB::statement("DELETE FROM sqlite_sequence WHERE name='{$table}';");
                        } else {
                            DB::statement("TRUNCATE TABLE `{$table}`;");
                        }
                    }
                }
            }

            // 5. Reset document serial numbers to start fresh at #1
            Setting::set('invoice_next_sequence', '1');
            Setting::set('order_next_sequence', '1');

            // 6. Re-enable Foreign Key Checks
            if ($driver !== 'sqlite') {
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            }

            // 7. Log Audit event
            AuditLogService::log('System', 'reset', "Performed Factory Reset / Fresh System Wipe. Emergency snapshot: '{$safetyFilename}'");

            return [
                'success' => true,
                'message' => "System successfully reset to fresh state! Emergency snapshot saved as '{$safetyFilename}'. All invoice and order serial numbers reset to #1.",
                'safety_backup' => $safetyFilename,
            ];
        } catch (Throwable $e) {
            if ($driver !== 'sqlite') {
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            }
            Log::error('Factory reset failed: '.$e->getMessage());
            throw $e;
        }
    }
}
