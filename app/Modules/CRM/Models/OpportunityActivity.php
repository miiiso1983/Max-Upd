<?php

namespace App\Modules\CRM\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class OpportunityActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'opportunity_id',
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
    const TYPE_STAGE_CHANGE = 'stage_change';
    const TYPE_AMOUNT_CHANGE = 'amount_change';
    const TYPE_CLOSE_DATE_CHANGE = 'close_date_change';
    const TYPE_PROBABILITY_CHANGE = 'probability_change';
    const TYPE_EMAIL_SENT = 'email_sent';
    const TYPE_CALL_MADE = 'call_made';
    const TYPE_MEETING_HELD = 'meeting_held';
    const TYPE_PROPOSAL_SENT = 'proposal_sent';
    const TYPE_WON = 'won';
    const TYPE_LOST = 'lost';
    const TYPE_NOTE_ADDED = 'note_added';
    const TYPE_ASSIGNED = 'assigned';
    const TYPE_OTHER = 'other';

    /**
     * Relationships
     */
    public function opportunity()
    {
        return $this->belongsTo(Opportunity::class);
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
            self::TYPE_CREATED => 'Opportunity Created',
            self::TYPE_STAGE_CHANGE => 'Stage Changed',
            self::TYPE_AMOUNT_CHANGE => 'Amount Changed',
            self::TYPE_CLOSE_DATE_CHANGE => 'Close Date Changed',
            self::TYPE_PROBABILITY_CHANGE => 'Probability Changed',
            self::TYPE_EMAIL_SENT => 'Email Sent',
            self::TYPE_CALL_MADE => 'Call Made',
            self::TYPE_MEETING_HELD => 'Meeting Held',
            self::TYPE_PROPOSAL_SENT => 'Proposal Sent',
            self::TYPE_WON => 'Won',
            self::TYPE_LOST => 'Lost',
            self::TYPE_NOTE_ADDED => 'Note Added',
            self::TYPE_ASSIGNED => 'Assigned',
            self::TYPE_OTHER => 'Other',
        ];

        return $labels[$this->type] ?? 'Unknown';
    }

    public function getTypeLabelArAttribute()
    {
        $labels = [
            self::TYPE_CREATED => 'تم إنشاء الفرصة',
            self::TYPE_STAGE_CHANGE => 'تم تغيير المرحلة',
            self::TYPE_AMOUNT_CHANGE => 'تم تغيير المبلغ',
            self::TYPE_CLOSE_DATE_CHANGE => 'تم تغيير تاريخ الإغلاق',
            self::TYPE_PROBABILITY_CHANGE => 'تم تغيير الاحتمالية',
            self::TYPE_EMAIL_SENT => 'تم إرسال بريد إلكتروني',
            self::TYPE_CALL_MADE => 'تم إجراء مكالمة',
            self::TYPE_MEETING_HELD => 'تم عقد اجتماع',
            self::TYPE_PROPOSAL_SENT => 'تم إرسال العرض',
            self::TYPE_WON => 'فوز',
            self::TYPE_LOST => 'خسارة',
            self::TYPE_NOTE_ADDED => 'تم إضافة ملاحظة',
            self::TYPE_ASSIGNED => 'تم التعيين',
            self::TYPE_OTHER => 'أخرى',
        ];

        return $labels[$this->type] ?? 'غير معروف';
    }
}
