<?php

namespace App\Modules\CRM\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
use App\Modules\Sales\Models\Customer;

class Lead extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'lead_number',
        'company_name',
        'company_name_ar',
        'contact_person',
        'contact_person_ar',
        'email',
        'phone',
        'mobile',
        'address',
        'address_ar',
        'city',
        'city_ar',
        'country',
        'country_ar',
        'industry',
        'industry_ar',
        'source',
        'source_ar',
        'status',
        'priority',
        'estimated_value',
        'probability',
        'expected_close_date',
        'description',
        'description_ar',
        'notes',
        'notes_ar',
        'assigned_to',
        'converted_to_customer_id',
        'converted_at',
        'last_contact_date',
        'next_follow_up_date',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'estimated_value' => 'decimal:2',
        'probability' => 'integer',
        'expected_close_date' => 'date',
        'converted_at' => 'datetime',
        'last_contact_date' => 'datetime',
        'next_follow_up_date' => 'datetime',
    ];

    // Status constants
    const STATUS_NEW = 'new';
    const STATUS_CONTACTED = 'contacted';
    const STATUS_QUALIFIED = 'qualified';
    const STATUS_PROPOSAL = 'proposal';
    const STATUS_NEGOTIATION = 'negotiation';
    const STATUS_CONVERTED = 'converted';
    const STATUS_LOST = 'lost';
    const STATUS_UNQUALIFIED = 'unqualified';

    // Priority constants
    const PRIORITY_LOW = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';

    // Source constants
    const SOURCE_WEBSITE = 'website';
    const SOURCE_REFERRAL = 'referral';
    const SOURCE_COLD_CALL = 'cold_call';
    const SOURCE_EMAIL = 'email';
    const SOURCE_SOCIAL_MEDIA = 'social_media';
    const SOURCE_TRADE_SHOW = 'trade_show';
    const SOURCE_ADVERTISEMENT = 'advertisement';
    const SOURCE_OTHER = 'other';

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($lead) {
            if (empty($lead->lead_number)) {
                $lead->lead_number = static::generateLeadNumber();
            }
        });
    }

    /**
     * Generate unique lead number
     */
    public static function generateLeadNumber()
    {
        $year = now()->year;
        $month = now()->format('m');
        $prefix = "LEAD-{$year}{$month}-";
        
        $lastLead = static::where('lead_number', 'like', $prefix . '%')
                          ->orderBy('lead_number', 'desc')
                          ->first();
        
        if ($lastLead) {
            $lastNumber = (int) substr($lastLead->lead_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Relationships
     */
    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'converted_to_customer_id');
    }

    public function activities()
    {
        return $this->hasMany(LeadActivity::class);
    }

    public function opportunities()
    {
        return $this->hasMany(Opportunity::class);
    }

    public function communications()
    {
        return $this->hasMany(Communication::class, 'related_id')
                    ->where('related_type', self::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->whereNotIn('status', [self::STATUS_CONVERTED, self::STATUS_LOST, self::STATUS_UNQUALIFIED]);
    }

    public function scopeConverted($query)
    {
        return $query->where('status', self::STATUS_CONVERTED);
    }

    public function scopeLost($query)
    {
        return $query->where('status', self::STATUS_LOST);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeBySource($query, $source)
    {
        return $query->where('source', $source);
    }

    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeOverdue($query)
    {
        return $query->where('next_follow_up_date', '<', now())
                    ->whereNotIn('status', [self::STATUS_CONVERTED, self::STATUS_LOST, self::STATUS_UNQUALIFIED]);
    }

    /**
     * Accessors & Mutators
     */
    public function getStatusLabelAttribute()
    {
        $labels = [
            self::STATUS_NEW => 'New',
            self::STATUS_CONTACTED => 'Contacted',
            self::STATUS_QUALIFIED => 'Qualified',
            self::STATUS_PROPOSAL => 'Proposal Sent',
            self::STATUS_NEGOTIATION => 'In Negotiation',
            self::STATUS_CONVERTED => 'Converted',
            self::STATUS_LOST => 'Lost',
            self::STATUS_UNQUALIFIED => 'Unqualified',
        ];

        return $labels[$this->status] ?? 'Unknown';
    }

    public function getStatusLabelArAttribute()
    {
        $labels = [
            self::STATUS_NEW => 'جديد',
            self::STATUS_CONTACTED => 'تم التواصل',
            self::STATUS_QUALIFIED => 'مؤهل',
            self::STATUS_PROPOSAL => 'تم إرسال العرض',
            self::STATUS_NEGOTIATION => 'في التفاوض',
            self::STATUS_CONVERTED => 'تم التحويل',
            self::STATUS_LOST => 'مفقود',
            self::STATUS_UNQUALIFIED => 'غير مؤهل',
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

    public function getSourceLabelAttribute()
    {
        $labels = [
            self::SOURCE_WEBSITE => 'Website',
            self::SOURCE_REFERRAL => 'Referral',
            self::SOURCE_COLD_CALL => 'Cold Call',
            self::SOURCE_EMAIL => 'Email',
            self::SOURCE_SOCIAL_MEDIA => 'Social Media',
            self::SOURCE_TRADE_SHOW => 'Trade Show',
            self::SOURCE_ADVERTISEMENT => 'Advertisement',
            self::SOURCE_OTHER => 'Other',
        ];

        return $labels[$this->source] ?? 'Unknown';
    }

    public function getSourceLabelArAttribute()
    {
        $labels = [
            self::SOURCE_WEBSITE => 'الموقع الإلكتروني',
            self::SOURCE_REFERRAL => 'إحالة',
            self::SOURCE_COLD_CALL => 'اتصال بارد',
            self::SOURCE_EMAIL => 'البريد الإلكتروني',
            self::SOURCE_SOCIAL_MEDIA => 'وسائل التواصل الاجتماعي',
            self::SOURCE_TRADE_SHOW => 'معرض تجاري',
            self::SOURCE_ADVERTISEMENT => 'إعلان',
            self::SOURCE_OTHER => 'أخرى',
        ];

        return $labels[$this->source] ?? 'غير معروف';
    }

    /**
     * Methods
     */
    public function convertToCustomer($customerData = [])
    {
        if ($this->status === self::STATUS_CONVERTED) {
            return $this->customer;
        }

        // Create customer from lead data
        $customer = Customer::create(array_merge([
            'name' => $this->company_name,
            'name_ar' => $this->company_name_ar,
            'contact_person' => $this->contact_person,
            'contact_person_ar' => $this->contact_person_ar,
            'email' => $this->email,
            'phone' => $this->phone,
            'mobile' => $this->mobile,
            'address' => $this->address,
            'address_ar' => $this->address_ar,
            'city' => $this->city,
            'city_ar' => $this->city_ar,
            'country' => $this->country,
            'country_ar' => $this->country_ar,
            'industry' => $this->industry,
            'industry_ar' => $this->industry_ar,
            'created_by' => $this->assigned_to ?? $this->created_by,
        ], $customerData));

        // Update lead status
        $this->update([
            'status' => self::STATUS_CONVERTED,
            'converted_to_customer_id' => $customer->id,
            'converted_at' => now(),
        ]);

        // Log activity
        $this->logActivity('converted', 'Lead converted to customer: ' . $customer->name);

        return $customer;
    }

    public function logActivity($type, $description, $userId = null)
    {
        return $this->activities()->create([
            'type' => $type,
            'description' => $description,
            'description_ar' => $description, // Could be translated
            'activity_date' => now(),
            'created_by' => $userId ?? auth()->id() ?? $this->created_by ?? 1,
        ]);
    }

    public function updateStatus($status, $notes = null)
    {
        $oldStatus = $this->status;
        $this->update(['status' => $status]);
        
        $this->logActivity('status_change', "Status changed from {$oldStatus} to {$status}. Notes: {$notes}");
        
        return $this;
    }

    public function scheduleFollowUp($date, $notes = null)
    {
        $this->update(['next_follow_up_date' => $date]);
        
        $this->logActivity('follow_up_scheduled', "Follow-up scheduled for {$date}. Notes: {$notes}");
        
        return $this;
    }
}
