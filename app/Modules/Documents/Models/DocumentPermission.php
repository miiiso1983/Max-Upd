<?php

namespace App\Modules\Documents\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class DocumentPermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'user_id',
        'permission',
        'granted_by',
        'granted_at',
        'expires_at',
    ];

    protected $casts = [
        'granted_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    // Permission constants
    const PERMISSION_READ = 'read';
    const PERMISSION_WRITE = 'write';
    const PERMISSION_DELETE = 'delete';
    const PERMISSION_SHARE = 'share';
    const PERMISSION_APPROVE = 'approve';

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($permission) {
            $permission->granted_at = now();
            $permission->granted_by = auth()->id();
        });
    }

    /**
     * Relationships
     */
    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function grantedBy()
    {
        return $this->belongsTo(User::class, 'granted_by');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    /**
     * Accessors
     */
    public function getPermissionLabelAttribute()
    {
        $labels = [
            self::PERMISSION_READ => 'Read',
            self::PERMISSION_WRITE => 'Write',
            self::PERMISSION_DELETE => 'Delete',
            self::PERMISSION_SHARE => 'Share',
            self::PERMISSION_APPROVE => 'Approve',
        ];

        return $labels[$this->permission] ?? 'Unknown';
    }

    public function getPermissionLabelArAttribute()
    {
        $labels = [
            self::PERMISSION_READ => 'قراءة',
            self::PERMISSION_WRITE => 'كتابة',
            self::PERMISSION_DELETE => 'حذف',
            self::PERMISSION_SHARE => 'مشاركة',
            self::PERMISSION_APPROVE => 'اعتماد',
        ];

        return $labels[$this->permission] ?? 'غير معروف';
    }

    public function getIsExpiredAttribute()
    {
        return $this->expires_at && $this->expires_at <= now();
    }

    /**
     * Methods
     */
    public function isValid()
    {
        return !$this->is_expired;
    }
}
