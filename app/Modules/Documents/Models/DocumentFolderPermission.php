<?php

namespace App\Modules\Documents\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class DocumentFolderPermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_folder_id',
        'user_id',
        'permission',
        'granted_by',
        'granted_at',
    ];

    protected $casts = [
        'granted_at' => 'datetime',
    ];

    // Permission constants
    const PERMISSION_READ = 'read';
    const PERMISSION_WRITE = 'write';
    const PERMISSION_DELETE = 'delete';
    const PERMISSION_SHARE = 'share';

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
    public function folder()
    {
        return $this->belongsTo(DocumentFolder::class, 'document_folder_id');
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
     * Accessors
     */
    public function getPermissionLabelAttribute()
    {
        $labels = [
            self::PERMISSION_READ => 'Read',
            self::PERMISSION_WRITE => 'Write',
            self::PERMISSION_DELETE => 'Delete',
            self::PERMISSION_SHARE => 'Share',
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
        ];

        return $labels[$this->permission] ?? 'غير معروف';
    }
}
