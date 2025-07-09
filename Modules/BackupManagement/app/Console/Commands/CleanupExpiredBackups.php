<?php

namespace Modules\BackupManagement\app\Console\Commands;

use Modules\BackupManagement\app\Models\TenantBackup;
use Modules\BackupManagement\app\Services\BackupEncryptionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CleanupExpiredBackups extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'backup:cleanup 
                            {--dry-run : Show what would be cleaned up without actually deleting}
                            {--tenant= : Cleanup only specific tenant ID}
                            {--days= : Override default expiration days}';

    /**
     * The console command description.
     */
    protected $description = 'Cleanup expired backup files and records';

    protected $encryptionService;

    public function __construct()
    {
        parent::__construct();
    }

    protected function getEncryptionService()
    {
        if (!$this->encryptionService) {
            $this->encryptionService = new BackupEncryptionService();
        }
        return $this->encryptionService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting backup cleanup process...');
        
        try {
            if ($this->option('dry-run')) {
                $this->handleDryRun();
            } else {
                $this->handleActualCleanup();
            }
            
        } catch (\Exception $e) {
            $this->error('Failed to cleanup backups: ' . $e->getMessage());
            Log::error('Backup cleanup failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    protected function handleDryRun()
    {
        $this->info('DRY RUN MODE - No files will be deleted');
        
        $expiredBackups = $this->getExpiredBackups();
        
        if ($expiredBackups->isEmpty()) {
            $this->info('No expired backups found.');
            return;
        }

        $this->info("Found {$expiredBackups->count()} expired backups:");
        
        $headers = ['ID', 'Tenant', 'Name', 'Size', 'Expired At', 'File Exists'];
        $rows = [];
        $totalSize = 0;

        foreach ($expiredBackups as $backup) {
            $fileExists = $backup->fileExists();
            $size = $fileExists ? $backup->file_size : 0;
            $totalSize += $size;

            $rows[] = [
                $backup->id,
                $backup->tenant->name ?? 'Unknown',
                $backup->backup_name,
                $backup->file_size_human,
                $backup->expires_at ? $backup->expires_at->format('Y-m-d H:i:s') : 'Never',
                $fileExists ? 'Yes' : 'No',
            ];
        }

        $this->table($headers, $rows);
        $this->info("Total size to be freed: " . $this->formatBytes($totalSize));

        // Check for orphaned encryption keys
        $orphanedKeys = $this->getEncryptionService()->cleanupOrphanedKeys();
        if ($orphanedKeys > 0) {
            $this->info("Would cleanup {$orphanedKeys} orphaned encryption keys.");
        }
    }

    protected function handleActualCleanup()
    {
        $this->info('Cleaning up expired backups...');
        
        $expiredBackups = $this->getExpiredBackups();
        
        if ($expiredBackups->isEmpty()) {
            $this->info('No expired backups found.');
            return;
        }

        $deletedCount = 0;
        $freedSpace = 0;
        $failedCount = 0;

        $progressBar = $this->output->createProgressBar($expiredBackups->count());
        $progressBar->start();

        foreach ($expiredBackups as $backup) {
            try {
                $fileSize = $backup->file_size ?? 0;
                
                // Delete backup file
                if ($backup->fileExists()) {
                    $backup->deleteFile();
                    $freedSpace += $fileSize;
                }

                // Cleanup encryption key if encrypted
                if ($backup->encrypted) {
                    $this->getEncryptionService()->cleanupEncryptionKey($backup->id);
                }

                // Delete backup record
                $backup->delete();
                
                $deletedCount++;
                
            } catch (\Exception $e) {
                $failedCount++;
                Log::error('Failed to delete backup', [
                    'backup_id' => $backup->id,
                    'error' => $e->getMessage()
                ]);
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();

        $this->info("Cleanup completed:");
        $this->info("- Deleted: {$deletedCount} backups");
        $this->info("- Failed: {$failedCount} backups");
        $this->info("- Space freed: " . $this->formatBytes($freedSpace));

        // Cleanup orphaned encryption keys
        $orphanedKeys = $this->getEncryptionService()->cleanupOrphanedKeys();
        if ($orphanedKeys > 0) {
            $this->info("- Cleaned up {$orphanedKeys} orphaned encryption keys");
        }

        Log::info('Backup cleanup completed', [
            'deleted_count' => $deletedCount,
            'failed_count' => $failedCount,
            'space_freed' => $freedSpace,
            'orphaned_keys_cleaned' => $orphanedKeys
        ]);
    }

    protected function getExpiredBackups()
    {
        $query = TenantBackup::where('expires_at', '<', now());

        if ($tenantId = $this->option('tenant')) {
            $query->where('tenant_id', $tenantId);
        }

        if ($days = $this->option('days')) {
            $query->orWhere('created_at', '<', now()->subDays($days));
        }

        return $query->get();
    }

    protected function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
