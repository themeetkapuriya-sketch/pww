<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Throwable;

class SystemHealthService
{
    /**
     * Get Database Storage Metrics & Disk Footprint.
     */
    public static function getDatabaseMetrics(): array
    {
        $driver = DB::connection()->getDriverName();
        $sizeBytes = 0;
        $tablesCount = 0;

        try {
            if ($driver === 'sqlite') {
                $dbPath = config('database.connections.sqlite.database');
                if (File::exists($dbPath)) {
                    $sizeBytes = File::size($dbPath);
                }
                $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
                $tablesCount = count($tables);
            } else {
                $dbName = DB::getDatabaseName();
                $res = DB::selectOne("
                    SELECT 
                        COUNT(table_name) AS total_tables,
                        COALESCE(SUM(data_length + index_length), 0) AS total_size_bytes
                    FROM information_schema.TABLES 
                    WHERE table_schema = ?
                ", [$dbName]);

                if ($res) {
                    $sizeBytes = (int) ($res->total_size_bytes ?? 0);
                    $tablesCount = (int) ($res->total_tables ?? 0);
                }
            }
        } catch (Throwable $e) {
            Log::error('Failed to calculate database metrics: '.$e->getMessage());
        }

        $sizeFormatted = $sizeBytes >= 1048576
            ? number_format($sizeBytes / 1048576, 2).' MB'
            : number_format(max($sizeBytes / 1024, 1), 2).' KB';

        $healthStatus = 'Optimal';
        $healthColor = 'emerald';
        if ($sizeBytes > 100 * 1048576) {
            $healthStatus = 'Heavy Storage';
            $healthColor = 'amber';
        }

        return [
            'driver' => strtoupper($driver),
            'size_bytes' => $sizeBytes,
            'size_formatted' => $sizeFormatted,
            'tables_count' => $tablesCount,
            'health_status' => $healthStatus,
            'health_color' => $healthColor,
        ];
    }

    /**
     * Get Live Table Row Counts across core ERP modules.
     */
    public static function getTableRecordCounts(): array
    {
        $counts = [
            'invoices' => 0,
            'orders' => 0,
            'purchases' => 0,
            'production' => 0,
            'attendance' => 0,
            'salaries' => 0,
            'expenses' => 0,
            'audit_logs' => 0,
            'products' => 0,
            'materials' => 0,
            'clients' => 0,
            'staff' => 0,
        ];

        try {
            $counts['invoices'] = DB::table('invoices')->count();
            $counts['orders'] = DB::table('sales_orders')->count();
            $counts['purchases'] = DB::table('purchases')->count();
            $counts['production'] = DB::table('production_logs')->count();
            $counts['attendance'] = DB::table('attendance_records')->count();
            $counts['salaries'] = DB::table('salary_payments')->count();
            $counts['expenses'] = DB::table('expenses')->count();
            $counts['audit_logs'] = DB::table('activity_logs')->count();
            $counts['products'] = DB::table('products')->count();
            $counts['materials'] = DB::table('raw_materials')->count();
            $counts['clients'] = DB::table('clients')->count();
            $counts['staff'] = DB::table('staff_profiles')->count();
        } catch (Throwable $e) {
            Log::error('Failed to get table record counts: '.$e->getMessage());
        }

        $totalTransactions = $counts['invoices'] + $counts['orders'] + $counts['purchases'] +
            $counts['production'] + $counts['attendance'] + $counts['salaries'] + $counts['expenses'];

        $counts['total_transactions'] = $totalTransactions;

        return $counts;
    }

    /**
     * Run Database Defragmentation, Vacuum, Re-indexing and Cache/Session Pruning.
     */
    public static function optimizeDatabase(): array
    {
        $before = self::getDatabaseMetrics();
        $driver = DB::connection()->getDriverName();

        try {
            if ($driver === 'sqlite') {
                DB::statement('VACUUM;');
            } else {
                $tables = DB::select('SHOW TABLES');
                $dbKey = 'Tables_in_'.DB::getDatabaseName();
                foreach ($tables as $t) {
                    $tName = $t->$dbKey ?? null;
                    if ($tName) {
                        DB::statement("OPTIMIZE TABLE `{$tName}`");
                    }
                }
            }

            // Prune expired sessions older than 7 days
            try {
                if (DB::getSchemaBuilder()->hasTable('sessions')) {
                    DB::table('sessions')->where('last_activity', '<', Carbon::now()->subDays(7)->timestamp)->delete();
                }
            } catch (Throwable $e) {
            }

            // Clear compiled caches
            try {
                Artisan::call('view:clear');
            } catch (Throwable $e) {
            }

            $after = self::getDatabaseMetrics();
            $reclaimed = max(0, $before['size_bytes'] - $after['size_bytes']);
            $reclaimedFormatted = $reclaimed >= 1048576
                ? number_format($reclaimed / 1048576, 2).' MB'
                : number_format($reclaimed / 1024, 2).' KB';

            AuditLogService::log('System', 'optimized', "Optimized database and defragmented storage ({$reclaimedFormatted} reclaimed)");

            return [
                'success' => true,
                'message' => 'Database defragmented, search index trees rebuilt, and expired caches cleaned successfully!',
                'reclaimed' => $reclaimedFormatted,
                'before' => $before['size_formatted'],
                'after' => $after['size_formatted'],
                'metrics' => $after,
            ];
        } catch (Throwable $e) {
            Log::error('Database optimization failed: '.$e->getMessage());

            return [
                'success' => false,
                'message' => 'Optimization failed: '.$e->getMessage(),
            ];
        }
    }
}
