<?php

namespace Modules\BackupManagement\app\Services;

use App\Models\Tenant;
use Modules\BackupManagement\app\Models\TenantBackup;
use Modules\BackupManagement\app\Models\BackupRestoreLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Process;
use Exception;

class RestoreService
{
    protected $backupDisk;
    protected $encryptionService;

    public function __construct()
    {
        $this->backupDisk = Storage::disk('backups');
        $this->encryptionService = new BackupEncryptionService();
    }

    /**
     * Restore a tenant from backup
     */
    public function restoreFromBackup(TenantBackup $backup, array $options = [])
    {
        if (!$backup->canBeRestored()) {
            throw new Exception('Backup cannot be restored. Check backup status and file integrity.');
        }

        $tenant = $backup->tenant;
        $log = BackupRestoreLog::logOperation(
            $tenant->id,
            BackupRestoreLog::OPERATION_RESTORE,
            'Restoring tenant from backup: ' . $backup->backup_name,
            $backup->id,
            $options['performed_by'] ?? auth()->id()
        );

        try {
            $log->appendLog('Restore process started');

            // Verify backup integrity
            if (!$backup->verifyIntegrity()) {
                throw new Exception('Backup file integrity check failed');
            }
            $log->appendLog('Backup integrity verified');

            // Create temporary restore directory
            $restorePath = $this->createRestorePath($tenant, $backup);
            $log->appendLog('Created restore directory: ' . $restorePath);

            // Extract backup archive
            $extractedPath = $this->extractBackup($backup, $restorePath, $log);
            $log->appendLog('Backup extracted to: ' . $extractedPath);

            // Backup current data before restore (safety measure)
            if ($options['create_safety_backup'] ?? true) {
                $this->createSafetyBackup($tenant, $log);
                $log->appendLog('Safety backup created');
            }

            // Restore database
            $this->restoreDatabase($tenant, $extractedPath, $log);
            $log->appendLog('Database restored successfully');

            // Restore files
            $this->restoreFiles($tenant, $extractedPath, $log);
            $log->appendLog('Files restored successfully');

            // Update backup record
            $backup->update([
                'restored_by' => $options['performed_by'] ?? auth()->id(),
                'restored_at' => now(),
                'restore_notes' => $options['notes'] ?? 'Restore completed successfully',
            ]);

            // Cleanup temporary files
            $this->cleanupRestoreFiles($restorePath);
            $log->appendLog('Temporary files cleaned up');

            $log->markAsCompleted('Restore completed successfully');

            return true;

        } catch (Exception $e) {
            $log->markAsFailed($e->getMessage(), 'Restore failed: ' . $e->getMessage());
            
            // Cleanup on failure
            if (isset($restorePath)) {
                $this->cleanupRestoreFiles($restorePath);
            }
            
            throw $e;
        }
    }

    /**
     * Create restore directory path
     */
    protected function createRestorePath(Tenant $tenant, TenantBackup $backup)
    {
        $path = "tenants/{$tenant->domain}/restore/" . now()->format('Y-m-d_H-i-s') . "/{$backup->id}";
        $this->backupDisk->makeDirectory($path);
        return $path;
    }

    /**
     * Extract backup archive
     */
    protected function extractBackup(TenantBackup $backup, string $restorePath, BackupRestoreLog $log)
    {
        $log->appendLog('Extracting backup archive');

        $backupFilePath = $backup->file_path;
        
        // Decrypt if necessary
        if ($backup->encrypted) {
            $log->appendLog('Decrypting backup file');
            $backupFilePath = $this->encryptionService->decryptFile($backupFilePath, $backup);
        }

        $sourcePath = $this->backupDisk->path($backupFilePath);
        $targetPath = $this->backupDisk->path($restorePath);

        // Extract tar.gz archive
        $command = sprintf(
            'tar -xzf %s -C %s',
            escapeshellarg($sourcePath),
            escapeshellarg($targetPath)
        );

        $result = Process::run($command);

        if ($result->failed()) {
            throw new Exception('Archive extraction failed: ' . $result->errorOutput());
        }

        $log->appendLog('Archive extracted successfully');

        return $restorePath;
    }

    /**
     * Restore database from backup
     */
    protected function restoreDatabase(Tenant $tenant, string $extractedPath, BackupRestoreLog $log)
    {
        $databaseFile = $extractedPath . '/database.sql';
        
        if (!$this->backupDisk->exists($databaseFile)) {
            throw new Exception('Database backup file not found in archive');
        }

        $log->appendLog('Starting database restore');

        $databaseName = $tenant->database;
        $sqlContent = $this->backupDisk->get($databaseFile);

        // Get database credentials
        $host = config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');

        // Drop existing database and recreate
        $log->appendLog('Dropping existing database: ' . $databaseName);
        DB::connection('mysql')->statement("DROP DATABASE IF EXISTS `{$databaseName}`");
        DB::connection('mysql')->statement("CREATE DATABASE `{$databaseName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

        // Restore database from SQL dump
        $tempSqlFile = tempnam(sys_get_temp_dir(), 'restore_');
        file_put_contents($tempSqlFile, $sqlContent);

        $command = sprintf(
            'mysql --host=%s --port=%s --user=%s --password=%s %s < %s',
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($databaseName),
            escapeshellarg($tempSqlFile)
        );

        $result = Process::run($command);

        // Cleanup temporary file
        unlink($tempSqlFile);

        if ($result->failed()) {
            throw new Exception('Database restore failed: ' . $result->errorOutput());
        }

        $log->appendLog('Database restored successfully');
    }

    /**
     * Restore files from backup
     */
    protected function restoreFiles(Tenant $tenant, string $extractedPath, BackupRestoreLog $log)
    {
        $filesPath = $extractedPath . '/files';
        
        if (!$this->backupDisk->exists($filesPath)) {
            $log->appendLog('No files directory found in backup, skipping file restore');
            return;
        }

        $log->appendLog('Starting files restore');

        // Define target directories
        $directories = [
            'private' => storage_path('app/private'),
            'public' => storage_path('app/public'),
        ];

        foreach ($directories as $dirName => $targetPath) {
            $sourcePath = $this->backupDisk->path($filesPath . '/' . $dirName);
            
            if (is_dir($sourcePath)) {
                // Backup existing directory
                if (is_dir($targetPath)) {
                    $backupDir = $targetPath . '_backup_' . now()->format('Y-m-d_H-i-s');
                    rename($targetPath, $backupDir);
                    $log->appendLog('Backed up existing directory: ' . $dirName);
                }

                // Restore directory
                $this->copyDirectory($sourcePath, $targetPath);
                $log->appendLog('Restored directory: ' . $dirName);
            }
        }

        $log->appendLog('Files restored successfully');
    }

    /**
     * Create safety backup before restore
     */
    protected function createSafetyBackup(Tenant $tenant, BackupRestoreLog $log)
    {
        $log->appendLog('Creating safety backup before restore');

        try {
            $backupService = new BackupService();
            $safetyBackup = $backupService->createFullBackup($tenant, [
                'name' => 'safety_backup_before_restore_' . now()->format('Y-m-d_H-i-s'),
                'trigger_type' => TenantBackup::TRIGGER_AUTOMATIC,
                'expires_at' => now()->addDays(7), // Keep for 7 days
            ]);

            $log->appendLog('Safety backup created with ID: ' . $safetyBackup->id);

        } catch (Exception $e) {
            $log->appendLog('Warning: Failed to create safety backup: ' . $e->getMessage());
            // Don't fail the restore process if safety backup fails
        }
    }

    /**
     * Copy directory recursively
     */
    protected function copyDirectory($source, $destination)
    {
        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $target = $destination . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
            
            if ($item->isDir()) {
                if (!is_dir($target)) {
                    mkdir($target, 0755, true);
                }
            } else {
                copy($item, $target);
            }
        }
    }

    /**
     * Cleanup restore temporary files
     */
    protected function cleanupRestoreFiles(string $restorePath)
    {
        if ($this->backupDisk->exists($restorePath)) {
            $this->backupDisk->deleteDirectory($restorePath);
        }
    }

    /**
     * Validate restore prerequisites
     */
    public function validateRestorePrerequisites(TenantBackup $backup)
    {
        $issues = [];

        // Check if backup exists and is complete
        if (!$backup->canBeRestored()) {
            $issues[] = 'Backup is not in a restorable state';
        }

        // Check if backup file exists
        if (!$backup->fileExists()) {
            $issues[] = 'Backup file does not exist';
        }

        // Check disk space
        $availableSpace = disk_free_space(storage_path());
        $requiredSpace = $backup->file_size * 3; // Need 3x space for extraction and safety backup

        if ($availableSpace < $requiredSpace) {
            $issues[] = 'Insufficient disk space for restore operation';
        }

        // Check database connectivity
        try {
            DB::connection('mysql')->getPdo();
        } catch (Exception $e) {
            $issues[] = 'Database connection failed: ' . $e->getMessage();
        }

        return $issues;
    }

    /**
     * Get restore preview information
     */
    public function getRestorePreview(TenantBackup $backup)
    {
        return [
            'backup_info' => [
                'name' => $backup->backup_name,
                'type' => $backup->backup_type,
                'size' => $backup->file_size_human,
                'created_at' => $backup->created_at,
                'database_info' => $backup->database_info,
                'file_info' => $backup->file_info,
            ],
            'restore_impact' => [
                'will_replace_database' => true,
                'will_replace_files' => true,
                'safety_backup_created' => true,
                'estimated_downtime' => $this->estimateRestoreTime($backup),
            ],
            'prerequisites' => $this->validateRestorePrerequisites($backup),
        ];
    }

    /**
     * Estimate restore time based on backup size
     */
    protected function estimateRestoreTime(TenantBackup $backup)
    {
        // Simple estimation: 1MB per second for database, 5MB per second for files
        $estimatedSeconds = ($backup->file_size / 1024 / 1024) * 2; // 2 seconds per MB average
        
        return gmdate('H:i:s', $estimatedSeconds);
    }
}
