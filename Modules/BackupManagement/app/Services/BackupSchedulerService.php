<?php

namespace Modules\BackupManagement\app\Services;

use Modules\BackupManagement\app\Models\BackupSchedule;
use Modules\BackupManagement\app\Models\TenantBackup;
use Modules\BackupManagement\app\Models\BackupRestoreLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Exception;

class BackupSchedulerService
{
    protected $backupService;

    public function __construct()
    {
        $this->backupService = new BackupService();
    }

    /**
     * Process all due backup schedules
     */
    public function processDueSchedules()
    {
        $dueSchedules = BackupSchedule::getDueSchedules();
        $processedCount = 0;
        $failedCount = 0;

        Log::info('Processing due backup schedules', ['count' => $dueSchedules->count()]);

        foreach ($dueSchedules as $schedule) {
            try {
                $this->processSchedule($schedule);
                $processedCount++;
                Log::info('Backup schedule processed successfully', ['schedule_id' => $schedule->id]);
            } catch (Exception $e) {
                $failedCount++;
                Log::error('Failed to process backup schedule', [
                    'schedule_id' => $schedule->id,
                    'error' => $e->getMessage()
                ]);
                
                $schedule->markAsRun(false, $e->getMessage());
                $this->sendFailureNotification($schedule, $e->getMessage());
            }
        }

        Log::info('Backup schedule processing completed', [
            'processed' => $processedCount,
            'failed' => $failedCount
        ]);

        return [
            'processed' => $processedCount,
            'failed' => $failedCount,
            'total' => $dueSchedules->count()
        ];
    }

    /**
     * Process a single backup schedule
     */
    protected function processSchedule(BackupSchedule $schedule)
    {
        Log::info('Processing backup schedule', [
            'schedule_id' => $schedule->id,
            'tenant_id' => $schedule->tenant_id,
            'backup_type' => $schedule->backup_type
        ]);

        $tenant = $schedule->tenant;
        
        if (!$tenant || !$tenant->is_active) {
            throw new Exception('Tenant is not active or not found');
        }

        // Check if we need to cleanup old backups first
        $this->cleanupOldBackups($schedule);

        // Create backup options
        $backupOptions = [
            'name' => $this->generateScheduledBackupName($schedule),
            'trigger_type' => TenantBackup::TRIGGER_SCHEDULED,
            'encrypt' => $schedule->encrypt_backup,
            'compress' => $schedule->compress_backup,
            'expires_at' => now()->addDays($schedule->retention_days),
            'created_by' => $schedule->created_by,
        ];

        // Merge additional backup options
        if ($schedule->backup_options) {
            $backupOptions = array_merge($backupOptions, $schedule->backup_options);
        }

        // Create backup based on type
        switch ($schedule->backup_type) {
            case TenantBackup::TYPE_FULL:
                $backup = $this->backupService->createFullBackup($tenant, $backupOptions);
                break;
                
            case TenantBackup::TYPE_INCREMENTAL:
                $backup = $this->backupService->createIncrementalBackup($tenant, $backupOptions);
                break;
                
            default:
                throw new Exception('Unsupported backup type: ' . $schedule->backup_type);
        }

        // Mark schedule as successfully run
        $schedule->markAsRun(true);

        // Send success notification if configured
        $this->sendSuccessNotification($schedule, $backup);

        Log::info('Backup created successfully', [
            'backup_id' => $backup->id,
            'schedule_id' => $schedule->id
        ]);

        return $backup;
    }

    /**
     * Generate backup name for scheduled backup
     */
    protected function generateScheduledBackupName(BackupSchedule $schedule)
    {
        $tenant = $schedule->tenant;
        $timestamp = now()->format('Y-m-d_H-i-s');
        
        return "{$tenant->domain}_{$schedule->backup_type}_scheduled_{$timestamp}";
    }

    /**
     * Cleanup old backups based on schedule retention policy
     */
    protected function cleanupOldBackups(BackupSchedule $schedule)
    {
        $tenant = $schedule->tenant;
        
        // Get backups for this tenant that are older than retention period
        $oldBackups = TenantBackup::where('tenant_id', $tenant->id)
            ->where('trigger_type', TenantBackup::TRIGGER_SCHEDULED)
            ->where('created_at', '<', now()->subDays($schedule->retention_days))
            ->where('status', TenantBackup::STATUS_COMPLETED)
            ->get();

        // Also cleanup if we exceed max_backups limit
        $totalBackups = TenantBackup::where('tenant_id', $tenant->id)
            ->where('trigger_type', TenantBackup::TRIGGER_SCHEDULED)
            ->where('status', TenantBackup::STATUS_COMPLETED)
            ->count();

        if ($totalBackups >= $schedule->max_backups) {
            $excessBackups = TenantBackup::where('tenant_id', $tenant->id)
                ->where('trigger_type', TenantBackup::TRIGGER_SCHEDULED)
                ->where('status', TenantBackup::STATUS_COMPLETED)
                ->orderBy('created_at', 'asc')
                ->take($totalBackups - $schedule->max_backups + 1)
                ->get();

            $oldBackups = $oldBackups->merge($excessBackups);
        }

        foreach ($oldBackups as $backup) {
            $this->deleteBackup($backup);
        }

        if ($oldBackups->count() > 0) {
            Log::info('Cleaned up old backups', [
                'tenant_id' => $tenant->id,
                'deleted_count' => $oldBackups->count()
            ]);
        }
    }

    /**
     * Delete a backup and its files
     */
    protected function deleteBackup(TenantBackup $backup)
    {
        try {
            // Delete backup file
            $backup->deleteFile();
            
            // Cleanup encryption key if encrypted
            if ($backup->encrypted) {
                $encryptionService = new BackupEncryptionService();
                $encryptionService->cleanupEncryptionKey($backup->id);
            }
            
            // Delete backup record
            $backup->delete();
            
            Log::info('Backup deleted', ['backup_id' => $backup->id]);
            
        } catch (Exception $e) {
            Log::error('Failed to delete backup', [
                'backup_id' => $backup->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send success notification
     */
    protected function sendSuccessNotification(BackupSchedule $schedule, TenantBackup $backup)
    {
        $notificationSettings = $schedule->notification_settings ?? [];
        
        if (!($notificationSettings['email_on_success'] ?? false)) {
            return;
        }

        $recipients = $notificationSettings['recipients'] ?? [];
        
        if (empty($recipients)) {
            return;
        }

        try {
            // In a real implementation, you would send actual emails
            Log::info('Backup success notification sent', [
                'schedule_id' => $schedule->id,
                'backup_id' => $backup->id,
                'recipients' => $recipients
            ]);
            
        } catch (Exception $e) {
            Log::error('Failed to send success notification', [
                'schedule_id' => $schedule->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send failure notification
     */
    protected function sendFailureNotification(BackupSchedule $schedule, string $errorMessage)
    {
        $notificationSettings = $schedule->notification_settings ?? [];
        
        if (!($notificationSettings['email_on_failure'] ?? true)) {
            return;
        }

        $recipients = $notificationSettings['recipients'] ?? [];
        
        if (empty($recipients)) {
            return;
        }

        try {
            // In a real implementation, you would send actual emails
            Log::error('Backup failure notification sent', [
                'schedule_id' => $schedule->id,
                'error' => $errorMessage,
                'recipients' => $recipients
            ]);
            
        } catch (Exception $e) {
            Log::error('Failed to send failure notification', [
                'schedule_id' => $schedule->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Update all schedule next run times
     */
    public function updateScheduleNextRuns()
    {
        $schedules = BackupSchedule::active()->get();
        $updatedCount = 0;

        foreach ($schedules as $schedule) {
            try {
                $schedule->calculateNextRun();
                $updatedCount++;
            } catch (Exception $e) {
                Log::error('Failed to update schedule next run', [
                    'schedule_id' => $schedule->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        Log::info('Updated schedule next run times', ['updated_count' => $updatedCount]);

        return $updatedCount;
    }

    /**
     * Get scheduler statistics
     */
    public function getSchedulerStatistics()
    {
        return [
            'total_schedules' => BackupSchedule::count(),
            'active_schedules' => BackupSchedule::active()->count(),
            'due_schedules' => BackupSchedule::due()->count(),
            'schedules_by_frequency' => BackupSchedule::selectRaw('frequency, COUNT(*) as count')
                ->groupBy('frequency')
                ->pluck('count', 'frequency'),
            'recent_runs' => BackupSchedule::whereNotNull('last_run_at')
                ->where('last_run_at', '>=', now()->subDays(7))
                ->count(),
            'failed_runs_today' => BackupSchedule::where('last_run_at', '>=', now()->startOfDay())
                ->whereNotNull('last_error')
                ->count(),
        ];
    }

    /**
     * Validate schedule configuration
     */
    public function validateSchedule(BackupSchedule $schedule)
    {
        $issues = [];

        // Check if tenant is active
        if (!$schedule->tenant || !$schedule->tenant->is_active) {
            $issues[] = 'Tenant is not active';
        }

        // Check backup type
        if (!in_array($schedule->backup_type, [TenantBackup::TYPE_FULL, TenantBackup::TYPE_INCREMENTAL])) {
            $issues[] = 'Invalid backup type';
        }

        // Check frequency
        if (!in_array($schedule->frequency, [
            BackupSchedule::FREQUENCY_DAILY,
            BackupSchedule::FREQUENCY_WEEKLY,
            BackupSchedule::FREQUENCY_MONTHLY,
            BackupSchedule::FREQUENCY_CUSTOM
        ])) {
            $issues[] = 'Invalid frequency';
        }

        // Check custom cron expression
        if ($schedule->frequency === BackupSchedule::FREQUENCY_CUSTOM && !$schedule->cron_expression) {
            $issues[] = 'Custom frequency requires cron expression';
        }

        // Check retention settings
        if ($schedule->retention_days < 1) {
            $issues[] = 'Retention days must be at least 1';
        }

        if ($schedule->max_backups < 1) {
            $issues[] = 'Max backups must be at least 1';
        }

        return $issues;
    }
}
