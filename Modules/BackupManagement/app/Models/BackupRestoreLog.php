<?php

namespace Modules\BackupManagement\app\Models;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BackupRestoreLog extends Model
{
    use HasFactory;

    protected $connection = 'mysql'; // Use landlord database

    protected $fillable = [
        'tenant_id',
        'backup_id',
        'operation_type',
        'status',
        'operation_details',
        'operation_metadata',
        'started_at',
        'completed_at',
        'duration_seconds',
        'error_message',
        'operation_log',
        'ip_address',
        'user_agent',
        'performed_by',
    ];

    protected $casts = [
        'operation_metadata' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // Operation type constants
    const OPERATION_BACKUP = 'backup';
    const OPERATION_RESTORE = 'restore';
    const OPERATION_CLEANUP = 'cleanup';
    const OPERATION_VERIFICATION = 'verification';

    // Status constants
    const STATUS_STARTED = 'started';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Relationships
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function backup(): BelongsTo
    {
        return $this->belongsTo(TenantBackup::class, 'backup_id');
    }

    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    /**
     * Scopes
     */
    public function scopeByTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeByOperation($query, $operationType)
    {
        return $query->where('operation_type', $operationType);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('started_at', '>=', now()->subDays($days));
    }

    /**
     * Accessors
     */
    public function getDurationHumanAttribute()
    {
        if (!$this->duration_seconds) {
            return 'Unknown';
        }

        $hours = floor($this->duration_seconds / 3600);
        $minutes = floor(($this->duration_seconds % 3600) / 60);
        $seconds = $this->duration_seconds % 60;

        if ($hours > 0) {
            return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        }

        return sprintf('%02d:%02d', $minutes, $seconds);
    }

    public function getOperationTypeLabelAttribute()
    {
        return match($this->operation_type) {
            self::OPERATION_BACKUP => 'Backup',
            self::OPERATION_RESTORE => 'Restore',
            self::OPERATION_CLEANUP => 'Cleanup',
            self::OPERATION_VERIFICATION => 'Verification',
            default => 'Unknown',
        };
    }

    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            self::STATUS_STARTED => 'Started',
            self::STATUS_IN_PROGRESS => 'In Progress',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_FAILED => 'Failed',
            self::STATUS_CANCELLED => 'Cancelled',
            default => 'Unknown',
        };
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            self::STATUS_STARTED => 'info',
            self::STATUS_IN_PROGRESS => 'warning',
            self::STATUS_COMPLETED => 'success',
            self::STATUS_FAILED => 'danger',
            self::STATUS_CANCELLED => 'secondary',
            default => 'secondary',
        };
    }

    /**
     * Methods
     */
    public function markAsCompleted($additionalLog = null)
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now(),
            'duration_seconds' => $this->started_at ? now()->diffInSeconds($this->started_at) : null,
            'operation_log' => $additionalLog ? $this->operation_log . "\n" . $additionalLog : $this->operation_log,
        ]);
    }

    public function markAsFailed($errorMessage, $additionalLog = null)
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'completed_at' => now(),
            'error_message' => $errorMessage,
            'duration_seconds' => $this->started_at ? now()->diffInSeconds($this->started_at) : null,
            'operation_log' => $additionalLog ? $this->operation_log . "\n" . $additionalLog : $this->operation_log,
        ]);
    }

    public function appendLog($message)
    {
        $timestamp = now()->format('Y-m-d H:i:s');
        $logEntry = "[{$timestamp}] {$message}";
        
        $this->update([
            'operation_log' => $this->operation_log ? $this->operation_log . "\n" . $logEntry : $logEntry,
        ]);
    }

    /**
     * Static methods
     */
    public static function logOperation($tenantId, $operationType, $details = null, $backupId = null, $performedBy = null)
    {
        return static::create([
            'tenant_id' => $tenantId,
            'backup_id' => $backupId,
            'operation_type' => $operationType,
            'status' => self::STATUS_STARTED,
            'operation_details' => $details,
            'started_at' => now(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'performed_by' => $performedBy ?? auth()->id(),
        ]);
    }

    public static function getOperationStatistics($tenantId = null, $days = 30)
    {
        $query = static::query();
        
        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        $query->where('started_at', '>=', now()->subDays($days));

        return [
            'total_operations' => $query->count(),
            'successful_operations' => $query->where('status', self::STATUS_COMPLETED)->count(),
            'failed_operations' => $query->where('status', self::STATUS_FAILED)->count(),
            'backup_operations' => $query->where('operation_type', self::OPERATION_BACKUP)->count(),
            'restore_operations' => $query->where('operation_type', self::OPERATION_RESTORE)->count(),
            'average_duration' => $query->whereNotNull('duration_seconds')->avg('duration_seconds'),
        ];
    }
}
