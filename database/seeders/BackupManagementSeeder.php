<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant;
use Modules\BackupManagement\app\Models\TenantBackup;
use Modules\BackupManagement\app\Models\BackupSchedule;
use Modules\BackupManagement\app\Models\BackupRestoreLog;
use Illuminate\Support\Facades\Storage;

class BackupManagementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->createSampleBackups();
        $this->createBackupSchedules();
        $this->createBackupLogs();

        $this->command->info('Backup Management sample data created successfully!');
        $this->command->info('Created:');
        $this->command->info('- ' . TenantBackup::count() . ' backup records');
        $this->command->info('- ' . BackupSchedule::count() . ' backup schedules');
        $this->command->info('- ' . BackupRestoreLog::count() . ' operation logs');
    }

    private function createSampleBackups()
    {
        // Get first tenant for demo
        $tenant = Tenant::first();
        
        if (!$tenant) {
            $this->command->warn('No tenants found. Creating a demo tenant...');
            $tenant = Tenant::create([
                'name' => 'Demo Pharmaceutical Company',
                'domain' => 'demo-pharma',
                'database' => 'demo_pharma_db',
                'is_active' => true,
            ]);
        }

        // Ensure backup directory exists
        Storage::disk('backups')->makeDirectory('tenants/' . $tenant->domain . '/backups');

        $backups = [
            [
                'tenant_id' => $tenant->id,
                'backup_name' => $tenant->domain . '_full_backup_' . now()->subDays(7)->format('Y-m-d_H-i-s'),
                'backup_type' => TenantBackup::TYPE_FULL,
                'trigger_type' => TenantBackup::TRIGGER_SCHEDULED,
                'status' => TenantBackup::STATUS_COMPLETED,
                'file_path' => 'tenants/' . $tenant->domain . '/backups/full_backup_week_ago.tar.gz',
                'file_name' => 'full_backup_week_ago.tar.gz',
                'file_size' => 52428800, // 50MB
                'compression_type' => 'gzip',
                'encrypted' => true,
                'encryption_method' => 'AES-256-CBC',
                'backup_metadata' => [
                    'database_size' => 45.2,
                    'files_count' => 1250,
                    'backup_version' => '1.0',
                    'php_version' => PHP_VERSION,
                    'laravel_version' => app()->version(),
                ],
                'database_info' => [
                    'tables_count' => 45,
                    'records_count' => 15420,
                    'size_mb' => 45.2,
                ],
                'file_info' => [
                    'directories' => ['storage/app/private', 'storage/app/public'],
                    'total_files' => 1250,
                    'total_size_mb' => 12.8,
                ],
                'started_at' => now()->subDays(7)->subMinutes(15),
                'completed_at' => now()->subDays(7),
                'duration_seconds' => 900, // 15 minutes
                'checksum' => hash('sha256', 'sample_backup_content_week_ago'),
                'expires_at' => now()->addDays(23), // 30 days from creation
                'is_restorable' => true,
                'created_by' => 1,
            ],
            [
                'tenant_id' => $tenant->id,
                'backup_name' => $tenant->domain . '_full_backup_' . now()->subDays(1)->format('Y-m-d_H-i-s'),
                'backup_type' => TenantBackup::TYPE_FULL,
                'trigger_type' => TenantBackup::TRIGGER_SCHEDULED,
                'status' => TenantBackup::STATUS_COMPLETED,
                'file_path' => 'tenants/' . $tenant->domain . '/backups/full_backup_yesterday.tar.gz',
                'file_name' => 'full_backup_yesterday.tar.gz',
                'file_size' => 54525952, // 52MB
                'compression_type' => 'gzip',
                'encrypted' => true,
                'encryption_method' => 'AES-256-CBC',
                'backup_metadata' => [
                    'database_size' => 47.1,
                    'files_count' => 1285,
                    'backup_version' => '1.0',
                    'php_version' => PHP_VERSION,
                    'laravel_version' => app()->version(),
                ],
                'database_info' => [
                    'tables_count' => 45,
                    'records_count' => 15890,
                    'size_mb' => 47.1,
                ],
                'file_info' => [
                    'directories' => ['storage/app/private', 'storage/app/public'],
                    'total_files' => 1285,
                    'total_size_mb' => 13.2,
                ],
                'started_at' => now()->subDays(1)->subMinutes(12),
                'completed_at' => now()->subDays(1),
                'duration_seconds' => 720, // 12 minutes
                'checksum' => hash('sha256', 'sample_backup_content_yesterday'),
                'expires_at' => now()->addDays(29), // 30 days from creation
                'is_restorable' => true,
                'created_by' => 1,
            ],
            [
                'tenant_id' => $tenant->id,
                'backup_name' => $tenant->domain . '_incremental_backup_' . now()->subHours(6)->format('Y-m-d_H-i-s'),
                'backup_type' => TenantBackup::TYPE_INCREMENTAL,
                'trigger_type' => TenantBackup::TRIGGER_SCHEDULED,
                'status' => TenantBackup::STATUS_COMPLETED,
                'file_path' => 'tenants/' . $tenant->domain . '/backups/incremental_backup_6h_ago.tar.gz',
                'file_name' => 'incremental_backup_6h_ago.tar.gz',
                'file_size' => 2097152, // 2MB
                'compression_type' => 'gzip',
                'encrypted' => true,
                'encryption_method' => 'AES-256-CBC',
                'backup_metadata' => [
                    'database_size' => 1.8,
                    'files_count' => 15,
                    'backup_version' => '1.0',
                    'base_backup_id' => 1, // References the full backup
                ],
                'database_info' => [
                    'tables_count' => 8, // Only changed tables
                    'records_count' => 125,
                    'size_mb' => 1.8,
                ],
                'file_info' => [
                    'directories' => ['storage/app/private'],
                    'total_files' => 15,
                    'total_size_mb' => 0.5,
                ],
                'started_at' => now()->subHours(6)->subMinutes(2),
                'completed_at' => now()->subHours(6),
                'duration_seconds' => 120, // 2 minutes
                'checksum' => hash('sha256', 'sample_incremental_backup_content'),
                'expires_at' => now()->addDays(7), // Shorter retention for incremental
                'is_restorable' => true,
                'created_by' => 1,
            ],
            [
                'tenant_id' => $tenant->id,
                'backup_name' => $tenant->domain . '_manual_backup_' . now()->subHours(2)->format('Y-m-d_H-i-s'),
                'backup_type' => TenantBackup::TYPE_FULL,
                'trigger_type' => TenantBackup::TRIGGER_MANUAL,
                'status' => TenantBackup::STATUS_COMPLETED,
                'file_path' => 'tenants/' . $tenant->domain . '/backups/manual_backup_2h_ago.tar.gz',
                'file_name' => 'manual_backup_2h_ago.tar.gz',
                'file_size' => 55574528, // 53MB
                'compression_type' => 'gzip',
                'encrypted' => false, // Manual backup without encryption
                'backup_metadata' => [
                    'database_size' => 47.5,
                    'files_count' => 1290,
                    'backup_version' => '1.0',
                    'manual_trigger_reason' => 'Pre-update backup',
                ],
                'database_info' => [
                    'tables_count' => 45,
                    'records_count' => 15920,
                    'size_mb' => 47.5,
                ],
                'file_info' => [
                    'directories' => ['storage/app/private', 'storage/app/public'],
                    'total_files' => 1290,
                    'total_size_mb' => 13.3,
                ],
                'started_at' => now()->subHours(2)->subMinutes(14),
                'completed_at' => now()->subHours(2),
                'duration_seconds' => 840, // 14 minutes
                'checksum' => hash('sha256', 'sample_manual_backup_content'),
                'expires_at' => now()->addDays(60), // Longer retention for manual backups
                'is_restorable' => true,
                'created_by' => 1,
            ],
            [
                'tenant_id' => $tenant->id,
                'backup_name' => $tenant->domain . '_backup_in_progress_' . now()->subMinutes(5)->format('Y-m-d_H-i-s'),
                'backup_type' => TenantBackup::TYPE_FULL,
                'trigger_type' => TenantBackup::TRIGGER_MANUAL,
                'status' => TenantBackup::STATUS_IN_PROGRESS,
                'compression_type' => 'gzip',
                'encrypted' => true,
                'encryption_method' => 'AES-256-CBC',
                'started_at' => now()->subMinutes(5),
                'is_restorable' => false,
                'created_by' => 1,
            ],
            [
                'tenant_id' => $tenant->id,
                'backup_name' => $tenant->domain . '_failed_backup_' . now()->subDays(3)->format('Y-m-d_H-i-s'),
                'backup_type' => TenantBackup::TYPE_FULL,
                'trigger_type' => TenantBackup::TRIGGER_SCHEDULED,
                'status' => TenantBackup::STATUS_FAILED,
                'compression_type' => 'gzip',
                'encrypted' => true,
                'encryption_method' => 'AES-256-CBC',
                'started_at' => now()->subDays(3)->subMinutes(5),
                'completed_at' => now()->subDays(3),
                'duration_seconds' => 300, // 5 minutes before failure
                'error_message' => 'Insufficient disk space to complete backup operation',
                'backup_log' => "Backup started at " . now()->subDays(3)->subMinutes(5) . "\nDatabase backup completed\nStarting file backup\nError: Insufficient disk space\nBackup failed",
                'is_restorable' => false,
                'created_by' => 1,
            ],
        ];

        foreach ($backups as $backupData) {
            // Create sample backup files for completed backups
            if ($backupData['status'] === TenantBackup::STATUS_COMPLETED && isset($backupData['file_path'])) {
                $this->createSampleBackupFile($backupData['file_path'], $backupData['file_size']);
            }
            
            TenantBackup::create($backupData);
        }
    }

    private function createSampleBackupFile($filePath, $fileSize)
    {
        // Create a sample backup file with appropriate size
        $content = "Sample backup file created for testing\n";
        $content .= "Created at: " . now()->toISOString() . "\n";
        $content .= "File size: " . $fileSize . " bytes\n";
        
        // Pad content to approximate the file size
        $paddingSize = max(0, $fileSize - strlen($content));
        $content .= str_repeat('X', min($paddingSize, 1024 * 1024)); // Max 1MB of padding
        
        Storage::disk('backups')->put($filePath, $content);
    }

    private function createBackupSchedules()
    {
        $tenant = Tenant::first();
        
        if (!$tenant) {
            return;
        }

        $schedules = [
            [
                'tenant_id' => $tenant->id,
                'name' => 'Daily Full Backup',
                'backup_type' => TenantBackup::TYPE_FULL,
                'frequency' => BackupSchedule::FREQUENCY_DAILY,
                'preferred_time' => '02:00:00',
                'is_active' => true,
                'retention_days' => 30,
                'max_backups' => 10,
                'compress_backup' => true,
                'encrypt_backup' => true,
                'notification_settings' => [
                    'email_on_success' => false,
                    'email_on_failure' => true,
                    'recipients' => ['admin@demo-pharma.com'],
                ],
                'last_run_at' => now()->subDay(),
                'next_run_at' => now()->addDay()->setTime(2, 0, 0),
                'successful_runs' => 25,
                'failed_runs' => 1,
                'created_by' => 1,
            ],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Weekly Archive Backup',
                'backup_type' => TenantBackup::TYPE_FULL,
                'frequency' => BackupSchedule::FREQUENCY_WEEKLY,
                'preferred_time' => '01:00:00',
                'days_of_week' => [0], // Sunday
                'is_active' => true,
                'retention_days' => 90,
                'max_backups' => 12,
                'compress_backup' => true,
                'encrypt_backup' => true,
                'notification_settings' => [
                    'email_on_success' => true,
                    'email_on_failure' => true,
                    'recipients' => ['admin@demo-pharma.com', 'backup@demo-pharma.com'],
                ],
                'last_run_at' => now()->subWeek(),
                'next_run_at' => now()->addWeek()->startOfWeek()->setTime(1, 0, 0),
                'successful_runs' => 12,
                'failed_runs' => 0,
                'created_by' => 1,
            ],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Hourly Incremental Backup',
                'backup_type' => TenantBackup::TYPE_INCREMENTAL,
                'frequency' => BackupSchedule::FREQUENCY_CUSTOM,
                'cron_expression' => '0 */6 * * *', // Every 6 hours
                'is_active' => false, // Disabled for now
                'retention_days' => 7,
                'max_backups' => 28, // 4 per day * 7 days
                'compress_backup' => true,
                'encrypt_backup' => true,
                'notification_settings' => [
                    'email_on_success' => false,
                    'email_on_failure' => true,
                    'recipients' => ['admin@demo-pharma.com'],
                ],
                'successful_runs' => 0,
                'failed_runs' => 0,
                'created_by' => 1,
            ],
        ];

        foreach ($schedules as $scheduleData) {
            $schedule = BackupSchedule::create($scheduleData);
            $schedule->calculateNextRun();
        }
    }

    private function createBackupLogs()
    {
        $tenant = Tenant::first();
        $backups = TenantBackup::where('tenant_id', $tenant->id)->get();
        
        foreach ($backups as $backup) {
            // Create backup operation log
            BackupRestoreLog::create([
                'tenant_id' => $tenant->id,
                'backup_id' => $backup->id,
                'operation_type' => BackupRestoreLog::OPERATION_BACKUP,
                'status' => $backup->status === TenantBackup::STATUS_COMPLETED ? 
                           BackupRestoreLog::STATUS_COMPLETED : 
                           ($backup->status === TenantBackup::STATUS_FAILED ? 
                            BackupRestoreLog::STATUS_FAILED : 
                            BackupRestoreLog::STATUS_IN_PROGRESS),
                'operation_details' => 'Automated backup operation for ' . $backup->backup_type . ' backup',
                'operation_metadata' => [
                    'backup_type' => $backup->backup_type,
                    'trigger_type' => $backup->trigger_type,
                    'file_size' => $backup->file_size,
                ],
                'started_at' => $backup->started_at,
                'completed_at' => $backup->completed_at,
                'duration_seconds' => $backup->duration_seconds,
                'error_message' => $backup->error_message,
                'operation_log' => $backup->backup_log ?? 'Backup operation completed successfully',
                'ip_address' => '127.0.0.1',
                'user_agent' => 'BackupScheduler/1.0',
                'performed_by' => 1,
            ]);

            // Create verification logs for completed backups
            if ($backup->status === TenantBackup::STATUS_COMPLETED) {
                BackupRestoreLog::create([
                    'tenant_id' => $tenant->id,
                    'backup_id' => $backup->id,
                    'operation_type' => BackupRestoreLog::OPERATION_VERIFICATION,
                    'status' => BackupRestoreLog::STATUS_COMPLETED,
                    'operation_details' => 'Backup file integrity verification',
                    'operation_metadata' => [
                        'checksum_verified' => true,
                        'file_exists' => true,
                        'file_size_match' => true,
                    ],
                    'started_at' => $backup->completed_at->addMinutes(1),
                    'completed_at' => $backup->completed_at->addMinutes(2),
                    'duration_seconds' => 60,
                    'operation_log' => 'File integrity verification completed successfully',
                    'ip_address' => '127.0.0.1',
                    'user_agent' => 'BackupVerifier/1.0',
                    'performed_by' => 1,
                ]);
            }
        }
    }
}
