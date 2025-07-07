<?php

namespace Modules\BackupManagement\app\Console\Commands;

use Modules\BackupManagement\app\Services\BackupSchedulerService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessScheduledBackups extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'backup:process-scheduled 
                            {--dry-run : Show what would be processed without actually running}
                            {--tenant= : Process only specific tenant ID}';

    /**
     * The console command description.
     */
    protected $description = 'Process all due backup schedules for tenants';

    protected $schedulerService;

    public function __construct()
    {
        parent::__construct();
        $this->schedulerService = new BackupSchedulerService();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting scheduled backup processing...');
        
        try {
            if ($this->option('dry-run')) {
                $this->handleDryRun();
            } else {
                $this->handleActualRun();
            }
            
        } catch (\Exception $e) {
            $this->error('Failed to process scheduled backups: ' . $e->getMessage());
            Log::error('Scheduled backup processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    protected function handleDryRun()
    {
        $this->info('DRY RUN MODE - No backups will be created');
        
        $dueSchedules = \Modules\BackupManagement\app\Models\BackupSchedule::due()->get();
        
        if ($dueSchedules->isEmpty()) {
            $this->info('No backup schedules are due for processing.');
            return;
        }

        $this->info("Found {$dueSchedules->count()} due backup schedules:");
        
        $headers = ['ID', 'Tenant', 'Name', 'Type', 'Frequency', 'Next Run'];
        $rows = [];

        foreach ($dueSchedules as $schedule) {
            $rows[] = [
                $schedule->id,
                $schedule->tenant->name ?? 'Unknown',
                $schedule->name,
                $schedule->backup_type,
                $schedule->frequency_label,
                $schedule->next_run_at ? $schedule->next_run_at->format('Y-m-d H:i:s') : 'Not set',
            ];
        }

        $this->table($headers, $rows);
    }

    protected function handleActualRun()
    {
        $this->info('Processing due backup schedules...');
        
        $results = $this->schedulerService->processDueSchedules();
        
        $this->info("Backup processing completed:");
        $this->info("- Processed: {$results['processed']}");
        $this->info("- Failed: {$results['failed']}");
        $this->info("- Total: {$results['total']}");

        if ($results['failed'] > 0) {
            $this->warn("Some backups failed. Check the logs for details.");
        }

        // Update next run times for all schedules
        $this->info('Updating schedule next run times...');
        $updated = $this->schedulerService->updateScheduleNextRuns();
        $this->info("Updated {$updated} schedule next run times.");
    }
}
