<?php

namespace App\Console\Commands;

use App\Models\AttendanceRecord;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class PruneOldAttendanceRecords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:prune {--months=6 : Number of months of daily attendance to retain}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prune daily attendance records older than 6 months while preserving monthly salary ledger records';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $months = (int) $this->option('months');
        if ($months < 1) {
            $months = 6;
        }

        $cutoffDate = Carbon::now()->subMonths($months)->startOfMonth()->format('Y-m-d');
        $this->info("Pruning daily attendance records older than {$months} months (before {$cutoffDate})...");

        try {
            $deletedCount = AttendanceRecord::where('date', '<', $cutoffDate)->delete();

            $this->info("Successfully pruned {$deletedCount} old daily attendance records.");
            $this->info("Note: All monthly salary ledger records, paid amounts, and present days remain 100% intact in 'salary_payments'.");

            Log::info("Pruned {$deletedCount} daily attendance records older than {$cutoffDate}");

            return Command::SUCCESS;
        } catch (Throwable $e) {
            $this->error('Failed to prune old attendance records: '.$e->getMessage());
            Log::error('Failed to prune old attendance records: '.$e->getMessage());

            return Command::FAILURE;
        }
    }
}
