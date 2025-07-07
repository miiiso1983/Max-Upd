<?php

namespace Modules\BackupManagement\app\Models;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class TenantBackup extends Model
{
    use HasFactory;

    protected $connection = 'mysql'; // Use landlord database

    protected $fillable = [
        'tenant_id',
        'backup_name',
        'backup_type',
        'trigger_type',
        'status',
        'file_path',
        'file_name',
        'file_size',
        'compression_type',
        'encrypted',
        'encryption_method',
        'encryption_key_hash',
        'backup_metadata',
        'database_info',
        'file_info',
        'started_at',
        'completed_at',
        'duration_seconds',
        'error_message',
        'backup_log',
        'checksum',
        'expires_at',
        'is_restorable',
        'created_by',
        'restored_by',
        'restored_at',
        'restore_notes',
    ];

    protected $casts = [
        'backup_metadata' => 'array',
        'database_info' => 'array',
        'file_info' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'expires_at' => 'datetime',
        'restored_at' => 'datetime',
        'encrypted' => 'boolean',
        'is_restorable' => 'boolean',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';

    // Backup type constants
    const TYPE_FULL = 'full';
    const TYPE_INCREMENTAL = 'incremental';
    const TYPE_DIFFERENTIAL = 'differential';

    // Trigger type constants
    const TRIGGER_MANUAL = 'manual';
    const TRIGGER_SCHEDULED = 'scheduled';
    const TRIGGER_AUTOMATIC = 'automatic';

    /**
     * Relationships
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function restorer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'restored_by');
    }

    public function restoreLogs(): HasMany
    {
        return $this->hasMany(BackupRestoreLog::class, 'backup_id');
    }

    /**
     * Scopes
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeRestorable($query)
    {
        return $query->where('is_restorable', true)
                    ->where('status', self::STATUS_COMPLETED);
    }

    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    public function scopeByTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('backup_type', $type);
    }

    /**
     * Accessors & Mutators
     */
    public function getFileSizeHumanAttribute()
    {
        if (!$this->file_size) {
            return 'Unknown';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = $this->file_size;
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

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

    public function getIsExpiredAttribute()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Methods
     */
    public function markAsStarted()
    {
        $this->update([
            'status' => self::STATUS_IN_PROGRESS,
            'started_at' => now(),
        ]);
    }

    public function markAsCompleted($filePath, $fileSize, $checksum = null)
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now(),
            'file_path' => $filePath,
            'file_size' => $fileSize,
            'checksum' => $checksum,
            'duration_seconds' => $this->started_at ? now()->diffInSeconds($this->started_at) : null,
        ]);
    }

    public function markAsFailed($errorMessage)
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'completed_at' => now(),
            'error_message' => $errorMessage,
            'duration_seconds' => $this->started_at ? now()->diffInSeconds($this->started_at) : null,
        ]);
    }

    public function fileExists()
    {
        return $this->file_path && Storage::disk('backups')->exists($this->file_path);
    }

    public function getFileUrl()
    {
        if (!$this->fileExists()) {
            return null;
        }

        return Storage::disk('backups')->url($this->file_path);
    }

    public function deleteFile()
    {
        if ($this->fileExists()) {
            return Storage::disk('backups')->delete($this->file_path);
        }

        return true;
    }

    public function verifyIntegrity()
    {
        if (!$this->fileExists() || !$this->checksum) {
            return false;
        }

        $currentChecksum = hash_file('sha256', Storage::disk('backups')->path($this->file_path));
        return $currentChecksum === $this->checksum;
    }

    public function canBeRestored()
    {
        return $this->is_restorable 
            && $this->status === self::STATUS_COMPLETED 
            && !$this->is_expired 
            && $this->fileExists();
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            self::STATUS_PENDING => 'warning',
            self::STATUS_IN_PROGRESS => 'info',
            self::STATUS_COMPLETED => 'success',
            self::STATUS_FAILED => 'danger',
            self::STATUS_CANCELLED => 'secondary',
            default => 'secondary',
        };
    }

    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            self::STATUS_PENDING => 'Pending',
            self::STATUS_IN_PROGRESS => 'In Progress',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_FAILED => 'Failed',
            self::STATUS_CANCELLED => 'Cancelled',
            default => 'Unknown',
        };
    }

    /**
     * Static methods for backup management
     */
    public static function getBackupStatistics($tenantId = null)
    {
        $query = static::query();

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        return [
            'total_backups' => $query->count(),
            'completed_backups' => $query->where('status', self::STATUS_COMPLETED)->count(),
            'failed_backups' => $query->where('status', self::STATUS_FAILED)->count(),
            'total_size' => $query->where('status', self::STATUS_COMPLETED)->sum('file_size'),
            'latest_backup' => $query->where('status', self::STATUS_COMPLETED)
                                   ->latest('completed_at')
                                   ->first(),
            'expired_backups' => $query->where('expires_at', '<', now())->count(),
        ];
    }

    public static function cleanupExpiredBackups()
    {
        $expiredBackups = static::where('expires_at', '<', now())->get();

        foreach ($expiredBackups as $backup) {
            $backup->deleteFile();
            $backup->delete();
        }

        return $expiredBackups->count();
    }
}
