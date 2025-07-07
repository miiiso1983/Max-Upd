<?php

namespace App\Modules\CRM\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class Communication extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'communication_number',
        'related_type',
        'related_id',
        'type',
        'direction',
        'subject',
        'subject_ar',
        'content',
        'content_ar',
        'from_email',
        'to_email',
        'cc_email',
        'bcc_email',
        'phone_number',
        'duration_minutes',
        'status',
        'priority',
        'scheduled_at',
        'completed_at',
        'notes',
        'notes_ar',
        'attachments',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'completed_at' => 'datetime',
        'attachments' => 'array',
        'duration_minutes' => 'integer',
    ];

    // Type constants
    const TYPE_EMAIL = 'email';
    const TYPE_PHONE = 'phone';
    const TYPE_MEETING = 'meeting';
    const TYPE_SMS = 'sms';
    const TYPE_WHATSAPP = 'whatsapp';
    const TYPE_LETTER = 'letter';
    const TYPE_VISIT = 'visit';
    const TYPE_OTHER = 'other';

    // Direction constants
    const DIRECTION_INBOUND = 'inbound';
    const DIRECTION_OUTBOUND = 'outbound';

    // Status constants
    const STATUS_DRAFT = 'draft';
    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_SENT = 'sent';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_READ = 'read';
    const STATUS_REPLIED = 'replied';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';

    // Priority constants
    const PRIORITY_LOW = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($communication) {
            if (empty($communication->communication_number)) {
                $communication->communication_number = static::generateCommunicationNumber();
            }
        });
    }

    /**
     * Generate unique communication number
     */
    public static function generateCommunicationNumber()
    {
        $year = now()->year;
        $month = now()->format('m');
        $prefix = "COMM-{$year}{$month}-";
        
        $lastCommunication = static::where('communication_number', 'like', $prefix . '%')
                                   ->orderBy('communication_number', 'desc')
                                   ->first();
        
        if ($lastCommunication) {
            $lastNumber = (int) substr($lastCommunication->communication_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Relationships
     */
    public function related()
    {
        return $this->morphTo();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scopes
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByDirection($query, $direction)
    {
        return $query->where('direction', $direction);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeInbound($query)
    {
        return $query->where('direction', self::DIRECTION_INBOUND);
    }

    public function scopeOutbound($query)
    {
        return $query->where('direction', self::DIRECTION_OUTBOUND);
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', self::STATUS_SCHEDULED);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', [self::STATUS_DRAFT, self::STATUS_SCHEDULED]);
    }

    public function scopeOverdue($query)
    {
        return $query->where('scheduled_at', '<', now())
                    ->whereIn('status', [self::STATUS_SCHEDULED]);
    }

    /**
     * Accessors & Mutators
     */
    public function getTypeLabelAttribute()
    {
        $labels = [
            self::TYPE_EMAIL => 'Email',
            self::TYPE_PHONE => 'Phone Call',
            self::TYPE_MEETING => 'Meeting',
            self::TYPE_SMS => 'SMS',
            self::TYPE_WHATSAPP => 'WhatsApp',
            self::TYPE_LETTER => 'Letter',
            self::TYPE_VISIT => 'Visit',
            self::TYPE_OTHER => 'Other',
        ];

        return $labels[$this->type] ?? 'Unknown';
    }

    public function getTypeLabelArAttribute()
    {
        $labels = [
            self::TYPE_EMAIL => 'بريد إلكتروني',
            self::TYPE_PHONE => 'مكالمة هاتفية',
            self::TYPE_MEETING => 'اجتماع',
            self::TYPE_SMS => 'رسالة نصية',
            self::TYPE_WHATSAPP => 'واتساب',
            self::TYPE_LETTER => 'رسالة',
            self::TYPE_VISIT => 'زيارة',
            self::TYPE_OTHER => 'أخرى',
        ];

        return $labels[$this->type] ?? 'غير معروف';
    }

    public function getDirectionLabelAttribute()
    {
        $labels = [
            self::DIRECTION_INBOUND => 'Inbound',
            self::DIRECTION_OUTBOUND => 'Outbound',
        ];

        return $labels[$this->direction] ?? 'Unknown';
    }

    public function getDirectionLabelArAttribute()
    {
        $labels = [
            self::DIRECTION_INBOUND => 'واردة',
            self::DIRECTION_OUTBOUND => 'صادرة',
        ];

        return $labels[$this->direction] ?? 'غير معروف';
    }

    public function getStatusLabelAttribute()
    {
        $labels = [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_SCHEDULED => 'Scheduled',
            self::STATUS_SENT => 'Sent',
            self::STATUS_DELIVERED => 'Delivered',
            self::STATUS_READ => 'Read',
            self::STATUS_REPLIED => 'Replied',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_FAILED => 'Failed',
            self::STATUS_CANCELLED => 'Cancelled',
        ];

        return $labels[$this->status] ?? 'Unknown';
    }

    public function getStatusLabelArAttribute()
    {
        $labels = [
            self::STATUS_DRAFT => 'مسودة',
            self::STATUS_SCHEDULED => 'مجدولة',
            self::STATUS_SENT => 'مرسلة',
            self::STATUS_DELIVERED => 'تم التسليم',
            self::STATUS_READ => 'تم القراءة',
            self::STATUS_REPLIED => 'تم الرد',
            self::STATUS_COMPLETED => 'مكتملة',
            self::STATUS_FAILED => 'فشلت',
            self::STATUS_CANCELLED => 'ملغية',
        ];

        return $labels[$this->status] ?? 'غير معروف';
    }

    public function getPriorityLabelAttribute()
    {
        $labels = [
            self::PRIORITY_LOW => 'Low',
            self::PRIORITY_MEDIUM => 'Medium',
            self::PRIORITY_HIGH => 'High',
            self::PRIORITY_URGENT => 'Urgent',
        ];

        return $labels[$this->priority] ?? 'Unknown';
    }

    public function getPriorityLabelArAttribute()
    {
        $labels = [
            self::PRIORITY_LOW => 'منخفض',
            self::PRIORITY_MEDIUM => 'متوسط',
            self::PRIORITY_HIGH => 'عالي',
            self::PRIORITY_URGENT => 'عاجل',
        ];

        return $labels[$this->priority] ?? 'غير معروف';
    }

    public function getIsOverdueAttribute()
    {
        return $this->scheduled_at && 
               $this->scheduled_at < now() && 
               $this->status === self::STATUS_SCHEDULED;
    }

    /**
     * Methods
     */
    public function markAsCompleted($notes = null)
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now(),
            'notes' => $notes ? ($this->notes ? $this->notes . "\n" . $notes : $notes) : $this->notes,
        ]);

        return $this;
    }

    public function markAsFailed($reason = null)
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'notes' => $reason ? ($this->notes ? $this->notes . "\n" . $reason : $reason) : $this->notes,
        ]);

        return $this;
    }

    public function schedule($dateTime, $notes = null)
    {
        $this->update([
            'status' => self::STATUS_SCHEDULED,
            'scheduled_at' => $dateTime,
            'notes' => $notes ? ($this->notes ? $this->notes . "\n" . $notes : $notes) : $this->notes,
        ]);

        return $this;
    }

    public function send($notes = null)
    {
        $this->update([
            'status' => self::STATUS_SENT,
            'notes' => $notes ? ($this->notes ? $this->notes . "\n" . $notes : $notes) : $this->notes,
        ]);

        return $this;
    }

    public function addAttachment($filePath, $fileName = null)
    {
        $attachments = $this->attachments ?? [];
        $attachments[] = [
            'file_path' => $filePath,
            'file_name' => $fileName ?? basename($filePath),
            'uploaded_at' => now()->toISOString(),
        ];

        $this->update(['attachments' => $attachments]);

        return $this;
    }

    public function removeAttachment($index)
    {
        $attachments = $this->attachments ?? [];
        
        if (isset($attachments[$index])) {
            unset($attachments[$index]);
            $this->update(['attachments' => array_values($attachments)]);
        }

        return $this;
    }
}
