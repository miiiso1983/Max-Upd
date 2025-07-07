<?php

namespace App\Modules\CRM\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class LeadActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'type',
        'description',
        'description_ar',
        'activity_date',
        'created_by',
    ];

    protected $casts = [
        'activity_date' => 'datetime',
    ];

    // Activity type constants
    const TYPE_CREATED = 'created';
    const TYPE_CONTACTED = 'contacted';
    const TYPE_EMAIL_SENT = 'email_sent';
    const TYPE_CALL_MADE = 'call_made';
    const TYPE_MEETING_HELD = 'meeting_held';
    const TYPE_STATUS_CHANGE = 'status_change';
    const TYPE_ASSIGNED = 'assigned';
    const TYPE_FOLLOW_UP_SCHEDULED = 'follow_up_scheduled';
    const TYPE_CONVERTED = 'converted';
    const TYPE_LOST = 'lost';
    const TYPE_NOTE_ADDED = 'note_added';
    const TYPE_OTHER = 'other';

    /**
     * Relationships
     */
    public function lead()
    {
        return $this->belongsTo(Lead::class);
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
            self::TYPE_CREATED => 'Lead Created',
            self::TYPE_CONTACTED => 'Contacted',
            self::TYPE_EMAIL_SENT => 'Email Sent',
            self::TYPE_CALL_MADE => 'Call Made',
            self::TYPE_MEETING_HELD => 'Meeting Held',
            self::TYPE_STATUS_CHANGE => 'Status Changed',
            self::TYPE_ASSIGNED => 'Assigned',
            self::TYPE_FOLLOW_UP_SCHEDULED => 'Follow-up Scheduled',
            self::TYPE_CONVERTED => 'Converted',
            self::TYPE_LOST => 'Lost',
            self::TYPE_NOTE_ADDED => 'Note Added',
            self::TYPE_OTHER => 'Other',
        ];

        return $labels[$this->type] ?? 'Unknown';
    }

    public function getTypeLabelArAttribute()
    {
        $labels = [
            self::TYPE_CREATED => 'تم إنشاء العميل المحتمل',
            self::TYPE_CONTACTED => 'تم التواصل',
            self::TYPE_EMAIL_SENT => 'تم إرسال بريد إلكتروني',
            self::TYPE_CALL_MADE => 'تم إجراء مكالمة',
            self::TYPE_MEETING_HELD => 'تم عقد اجتماع',
            self::TYPE_STATUS_CHANGE => 'تم تغيير الحالة',
            self::TYPE_ASSIGNED => 'تم التعيين',
            self::TYPE_FOLLOW_UP_SCHEDULED => 'تم جدولة المتابعة',
            self::TYPE_CONVERTED => 'تم التحويل',
            self::TYPE_LOST => 'مفقود',
            self::TYPE_NOTE_ADDED => 'تم إضافة ملاحظة',
            self::TYPE_OTHER => 'أخرى',
        ];

        return $labels[$this->type] ?? 'غير معروف';
    }
}
