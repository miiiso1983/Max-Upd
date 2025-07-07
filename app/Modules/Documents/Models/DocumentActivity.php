<?php

namespace App\Modules\Documents\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class DocumentActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'type',
        'description',
        'description_ar',
        'activity_date',
        'ip_address',
        'user_agent',
        'metadata',
        'created_by',
    ];

    protected $casts = [
        'activity_date' => 'datetime',
        'metadata' => 'array',
    ];

    // Activity type constants
    const TYPE_CREATED = 'created';
    const TYPE_UPLOADED = 'uploaded';
    const TYPE_DOWNLOADED = 'downloaded';
    const TYPE_VIEWED = 'viewed';
    const TYPE_EDITED = 'edited';
    const TYPE_DELETED = 'deleted';
    const TYPE_SHARED = 'shared';
    const TYPE_APPROVED = 'approved';
    const TYPE_REJECTED = 'rejected';
    const TYPE_SIGNED = 'signed';
    const TYPE_VERSION_CREATED = 'version_created';
    const TYPE_PERMISSION_GRANTED = 'permission_granted';
    const TYPE_PERMISSION_REVOKED = 'permission_revoked';
    const TYPE_ARCHIVED = 'archived';
    const TYPE_RESTORED = 'restored';

    /**
     * Relationships
     */
    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scopes
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('activity_date', '>=', now()->subDays($days));
    }

    /**
     * Accessors
     */
    public function getTypeLabelAttribute()
    {
        $labels = [
            self::TYPE_CREATED => 'Created',
            self::TYPE_UPLOADED => 'Uploaded',
            self::TYPE_DOWNLOADED => 'Downloaded',
            self::TYPE_VIEWED => 'Viewed',
            self::TYPE_EDITED => 'Edited',
            self::TYPE_DELETED => 'Deleted',
            self::TYPE_SHARED => 'Shared',
            self::TYPE_APPROVED => 'Approved',
            self::TYPE_REJECTED => 'Rejected',
            self::TYPE_SIGNED => 'Signed',
            self::TYPE_VERSION_CREATED => 'Version Created',
            self::TYPE_PERMISSION_GRANTED => 'Permission Granted',
            self::TYPE_PERMISSION_REVOKED => 'Permission Revoked',
            self::TYPE_ARCHIVED => 'Archived',
            self::TYPE_RESTORED => 'Restored',
        ];

        return $labels[$this->type] ?? 'Unknown';
    }

    public function getTypeLabelArAttribute()
    {
        $labels = [
            self::TYPE_CREATED => 'تم الإنشاء',
            self::TYPE_UPLOADED => 'تم الرفع',
            self::TYPE_DOWNLOADED => 'تم التحميل',
            self::TYPE_VIEWED => 'تم العرض',
            self::TYPE_EDITED => 'تم التعديل',
            self::TYPE_DELETED => 'تم الحذف',
            self::TYPE_SHARED => 'تم المشاركة',
            self::TYPE_APPROVED => 'تم الاعتماد',
            self::TYPE_REJECTED => 'تم الرفض',
            self::TYPE_SIGNED => 'تم التوقيع',
            self::TYPE_VERSION_CREATED => 'تم إنشاء نسخة',
            self::TYPE_PERMISSION_GRANTED => 'تم منح الصلاحية',
            self::TYPE_PERMISSION_REVOKED => 'تم إلغاء الصلاحية',
            self::TYPE_ARCHIVED => 'تم الأرشفة',
            self::TYPE_RESTORED => 'تم الاستعادة',
        ];

        return $labels[$this->type] ?? 'غير معروف';
    }
}
