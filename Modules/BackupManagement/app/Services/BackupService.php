<?php

namespace Modules\BackupManagement\app\Services;

use App\Models\Tenant;
use Modules\BackupManagement\app\Models\TenantBackup;
use Modules\BackupManagement\app\Models\BackupRestoreLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;
use Exception;

class BackupService
{
    protected $backupDisk;
    protected $encryptionService;

    public function __construct()
    {
        // Services will be initialized lazily when needed
    }

    protected function getBackupDisk()
    {
        if (!$this->backupDisk) {
            $this->backupDisk = Storage::disk('backups');
        }
        return $this->backupDisk;
    }

    protected function getEncryptionService()
    {
        if (!$this->encryptionService) {
            $this->encryptionService = new BackupEncryptionService();
        }
        return $this->encryptionService;
    }

    /**
     * Create a full backup for a tenant
     */
    public function createFullBackup(Tenant $tenant, array $options = [])
    {
        $backup = $this->initializeBackup($tenant, TenantBackup::TYPE_FULL, $options);
        $log = BackupRestoreLog::logOperation(
            $tenant->id, 
            BackupRestoreLog::OPERATION_BACKUP,
            'Creating full backup for tenant: ' . $tenant->name,
            $backup->id,
            $options['created_by'] ?? null
        );

        try {
            $backup->markAsStarted();
            $log->appendLog('Backup process started');

            // Create backup directory
            $backupPath = $this->createBackupPath($tenant, $backup);
            $log->appendLog('Created backup directory: ' . $backupPath);

            // Backup database
            $databaseBackupPath = $this->backupDatabase($tenant, $backupPath, $log);
            $log->appendLog('Database backup completed: ' . $databaseBackupPath);

            // Backup files
            $filesBackupPath = $this->backupFiles($tenant, $backupPath, $log);
            $log->appendLog('Files backup completed: ' . $filesBackupPath);

            // Create backup archive
            $archivePath = $this->createArchive($backupPath, $backup, $log);
            $log->appendLog('Archive created: ' . $archivePath);

            // Encrypt if required
            if ($backup->encrypted) {
                $archivePath = $this->getEncryptionService()->encryptFile($archivePath, $backup);
                $log->appendLog('Backup encrypted');
            }

            // Calculate file size and checksum
            $fileSize = $this->getBackupDisk()->size($archivePath);
            $checksum = hash_file('sha256', $this->getBackupDisk()->path($archivePath));

            // Update backup record
            $backup->markAsCompleted($archivePath, $fileSize, $checksum);
            $backup->update([
                'file_name' => basename($archivePath),
                'backup_metadata' => [
                    'database_size' => $this->getDatabaseSize($tenant),
                    'files_count' => $this->getFilesCount($tenant),
                    'backup_version' => '1.0',
                    'php_version' => PHP_VERSION,
                    'laravel_version' => app()->version(),
                ],
            ]);

            // Cleanup temporary files
            $this->cleanupTemporaryFiles($backupPath);
            $log->appendLog('Temporary files cleaned up');

            $log->markAsCompleted('Backup completed successfully');

            return $backup;

        } catch (Exception $e) {
            $backup->markAsFailed($e->getMessage());
            $log->markAsFailed($e->getMessage(), 'Backup failed: ' . $e->getMessage());
            
            // Cleanup on failure
            $this->cleanupFailedBackup($backup);
            
            throw $e;
        }
    }

    /**
     * Create an incremental backup
     */
    public function createIncrementalBackup(Tenant $tenant, array $options = [])
    {
        // Find the last full backup
        $lastFullBackup = TenantBackup::where('tenant_id', $tenant->id)
            ->where('backup_type', TenantBackup::TYPE_FULL)
            ->where('status', TenantBackup::STATUS_COMPLETED)
            ->latest('completed_at')
            ->first();

        if (!$lastFullBackup) {
            throw new Exception('No full backup found. Please create a full backup first.');
        }

        $backup = $this->initializeBackup($tenant, TenantBackup::TYPE_INCREMENTAL, $options);
        $log = BackupRestoreLog::logOperation(
            $tenant->id,
            BackupRestoreLog::OPERATION_BACKUP,
            'Creating incremental backup for tenant: ' . $tenant->name,
            $backup->id,
            $options['created_by'] ?? null
        );

        try {
            $backup->markAsStarted();
            $log->appendLog('Incremental backup process started');

            // Create backup directory
            $backupPath = $this->createBackupPath($tenant, $backup);

            // Backup only changed data since last backup
            $this->backupIncrementalData($tenant, $backupPath, $lastFullBackup, $log);

            // Create archive
            $archivePath = $this->createArchive($backupPath, $backup, $log);

            // Encrypt if required
            if ($backup->encrypted) {
                $archivePath = $this->getEncryptionService()->encryptFile($archivePath, $backup);
            }

            // Calculate file size and checksum
            $fileSize = $this->getBackupDisk()->size($archivePath);
            $checksum = hash_file('sha256', $this->getBackupDisk()->path($archivePath));

            $backup->markAsCompleted($archivePath, $fileSize, $checksum);
            $this->cleanupTemporaryFiles($backupPath);
            $log->markAsCompleted('Incremental backup completed successfully');

            return $backup;

        } catch (Exception $e) {
            $backup->markAsFailed($e->getMessage());
            $log->markAsFailed($e->getMessage());
            $this->cleanupFailedBackup($backup);
            throw $e;
        }
    }

    /**
     * Initialize a new backup record
     */
    protected function initializeBackup(Tenant $tenant, string $type, array $options = [])
    {
        $backupName = $options['name'] ?? $this->generateBackupName($tenant, $type);
        
        return TenantBackup::create([
            'tenant_id' => $tenant->id,
            'backup_name' => $backupName,
            'backup_type' => $type,
            'trigger_type' => $options['trigger_type'] ?? TenantBackup::TRIGGER_MANUAL,
            'compression_type' => $options['compression'] ?? 'gzip',
            'encrypted' => $options['encrypt'] ?? true,
            'encryption_method' => 'AES-256-CBC',
            'expires_at' => $options['expires_at'] ?? now()->addDays(30),
            'created_by' => $options['created_by'] ?? auth()->id(),
        ]);
    }

    /**
     * Generate a unique backup name
     */
    protected function generateBackupName(Tenant $tenant, string $type)
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        return "{$tenant->domain}_{$type}_backup_{$timestamp}";
    }

    /**
     * Create backup directory path
     */
    protected function createBackupPath(Tenant $tenant, TenantBackup $backup)
    {
        $path = "tenants/{$tenant->domain}/backups/" . now()->format('Y/m/d') . "/{$backup->id}";
        $this->getBackupDisk()->makeDirectory($path);
        return $path;
    }

    /**
     * Backup tenant database
     */
    protected function backupDatabase(Tenant $tenant, string $backupPath, BackupRestoreLog $log)
    {
        $databaseName = $tenant->database;
        $filename = 'database.sql';
        $filePath = $backupPath . '/' . $filename;
        
        $log->appendLog('Starting database backup for: ' . $databaseName);

        // Get database credentials
        $host = config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');

        // Create mysqldump command
        $command = sprintf(
            'mysqldump --host=%s --port=%s --user=%s --password=%s --single-transaction --routines --triggers %s',
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($databaseName)
        );

        // Execute mysqldump
        $result = Process::run($command);
        
        if ($result->failed()) {
            throw new Exception('Database backup failed: ' . $result->errorOutput());
        }

        // Save SQL dump to file
        $this->getBackupDisk()->put($filePath, $result->output());
        
        $log->appendLog('Database backup saved to: ' . $filePath);
        
        return $filePath;
    }

    /**
     * Backup tenant files
     */
    protected function backupFiles(Tenant $tenant, string $backupPath, BackupRestoreLog $log)
    {
        $log->appendLog('Starting files backup');

        // Define directories to backup
        $directories = [
            'storage/app/private',
            'storage/app/public',
        ];

        $filesPath = $backupPath . '/files';
        $this->getBackupDisk()->makeDirectory($filesPath);

        foreach ($directories as $directory) {
            $sourcePath = storage_path('app/' . $directory);
            $targetPath = $filesPath . '/' . basename($directory);

            if (is_dir($sourcePath)) {
                $this->copyDirectory($sourcePath, $this->getBackupDisk()->path($targetPath));
                $log->appendLog('Copied directory: ' . $directory);
            }
        }

        return $filesPath;
    }

    /**
     * Create compressed archive
     */
    protected function createArchive(string $backupPath, TenantBackup $backup, BackupRestoreLog $log)
    {
        $archiveName = $backup->backup_name . '.tar.gz';
        $archivePath = dirname($backupPath) . '/' . $archiveName;
        
        $log->appendLog('Creating archive: ' . $archiveName);

        $sourcePath = $this->getBackupDisk()->path($backupPath);
        $targetPath = $this->getBackupDisk()->path($archivePath);

        // Create tar.gz archive
        $command = sprintf(
            'tar -czf %s -C %s .',
            escapeshellarg($targetPath),
            escapeshellarg($sourcePath)
        );

        $result = Process::run($command);

        if ($result->failed()) {
            throw new Exception('Archive creation failed: ' . $result->errorOutput());
        }

        $log->appendLog('Archive created successfully');

        return $archivePath;
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
     * Get database size
     */
    protected function getDatabaseSize(Tenant $tenant)
    {
        $result = DB::connection('mysql')->select(
            "SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 1) AS 'size_mb' 
             FROM information_schema.tables 
             WHERE table_schema = ?",
            [$tenant->database]
        );

        return $result[0]->size_mb ?? 0;
    }

    /**
     * Get files count
     */
    protected function getFilesCount(Tenant $tenant)
    {
        // This is a simplified implementation
        // In a real scenario, you'd count files in tenant-specific directories
        return 0;
    }

    /**
     * Backup incremental data
     */
    protected function backupIncrementalData(Tenant $tenant, string $backupPath, TenantBackup $lastBackup, BackupRestoreLog $log)
    {
        $log->appendLog('Creating incremental backup since: ' . $lastBackup->completed_at);
        
        // This is a simplified implementation
        // In a real scenario, you'd identify and backup only changed data
        $this->backupDatabase($tenant, $backupPath, $log);
    }

    /**
     * Cleanup temporary files
     */
    protected function cleanupTemporaryFiles(string $backupPath)
    {
        if ($this->getBackupDisk()->exists($backupPath)) {
            $this->getBackupDisk()->deleteDirectory($backupPath);
        }
    }

    /**
     * Cleanup failed backup
     */
    protected function cleanupFailedBackup(TenantBackup $backup)
    {
        if ($backup->file_path && $this->getBackupDisk()->exists($backup->file_path)) {
            $this->getBackupDisk()->delete($backup->file_path);
        }
    }
}
