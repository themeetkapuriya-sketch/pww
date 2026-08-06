<?php

namespace App\Console\Commands;

use App\Services\BackupService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class MonthlyBackupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:monthly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate automated monthly database backup snapshot';

    /**
     * Execute the console command.
     */
    public function handle(BackupService $backupService): int
    {
        $this->info('Starting automated monthly database backup...');

        try {
            $filePath = $backupService->ensureAutomaticBackupExists();
            $this->info("Automatic backup verified and created successfully at: {$filePath}");
            Log::info("Monthly backup command executed successfully: {$filePath}");
            return Command::SUCCESS;
        } catch (Throwable $e) {
            $this->error("Monthly backup command failed: " . $e->getMessage());
            Log::error("Monthly backup command failed: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
