<?php

namespace App\Modules\CRM\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
use App\Modules\Sales\Models\Customer;

class Opportunity extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'opportunity_number',
        'name',
        'name_ar',
        'description',
        'description_ar',
        'customer_id',
        'lead_id',
        'stage',
        'probability',
        'amount',
        'currency',
        'expected_close_date',
        'actual_close_date',
        'source',
        'source_ar',
        'type',
        'priority',
        'assigned_to',
        'created_by',
        'updated_by',
        'notes',
        'notes_ar',
        'lost_reason',
        'lost_reason_ar',
        'competitor',
        'competitor_ar',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'probability' => 'integer',
        'expected_close_date' => 'date',
        'actual_close_date' => 'date',
    ];

    // Stage constants
    const STAGE_PROSPECTING = 'prospecting';
    const STAGE_QUALIFICATION = 'qualification';
    const STAGE_NEEDS_ANALYSIS = 'needs_analysis';
    const STAGE_PROPOSAL = 'proposal';
    const STAGE_NEGOTIATION = 'negotiation';
    const STAGE_CLOSED_WON = 'closed_won';
    const STAGE_CLOSED_LOST = 'closed_lost';

    // Type constants
    const TYPE_NEW_BUSINESS = 'new_business';
    const TYPE_EXISTING_BUSINESS = 'existing_business';
    const TYPE_RENEWAL = 'renewal';
    const TYPE_UPSELL = 'upsell';
    const TYPE_CROSS_SELL = 'cross_sell';

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

        static::creating(function ($opportunity) {
            if (empty($opportunity->opportunity_number)) {
                $opportunity->opportunity_number = static::generateOpportunityNumber();
            }
        });
    }

    /**
     * Generate unique opportunity number
     */
    public static function generateOpportunityNumber()
    {
        $year = now()->year;
        $month = now()->format('m');
        $prefix = "OPP-{$year}{$month}-";
        
        $lastOpportunity = static::where('opportunity_number', 'like', $prefix . '%')
                                 ->orderBy('opportunity_number', 'desc')
                                 ->first();
        
        if ($lastOpportunity) {
            $lastNumber = (int) substr($lastOpportunity->opportunity_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Relationships
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

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

    public function activities()
    {
        return $this->hasMany(OpportunityActivity::class);
    }

    public function communications()
    {
        return $this->hasMany(Communication::class, 'related_id')
                    ->where('related_type', self::class);
    }

    /**
     * Scopes
     */
    public function scopeOpen($query)
    {
        return $query->whereNotIn('stage', [self::STAGE_CLOSED_WON, self::STAGE_CLOSED_LOST]);
    }

    public function scopeWon($query)
    {
        return $query->where('stage', self::STAGE_CLOSED_WON);
    }

    public function scopeLost($query)
    {
        return $query->where('stage', self::STAGE_CLOSED_LOST);
    }

    public function scopeByStage($query, $stage)
    {
        return $query->where('stage', $stage);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeOverdue($query)
    {
        return $query->where('expected_close_date', '<', now())
                    ->whereNotIn('stage', [self::STAGE_CLOSED_WON, self::STAGE_CLOSED_LOST]);
    }

    public function scopeClosingSoon($query, $days = 7)
    {
        return $query->whereBetween('expected_close_date', [now(), now()->addDays($days)])
                    ->whereNotIn('stage', [self::STAGE_CLOSED_WON, self::STAGE_CLOSED_LOST]);
    }

    /**
     * Accessors & Mutators
     */
    public function getStageLabelAttribute()
    {
        $labels = [
            self::STAGE_PROSPECTING => 'Prospecting',
            self::STAGE_QUALIFICATION => 'Qualification',
            self::STAGE_NEEDS_ANALYSIS => 'Needs Analysis',
            self::STAGE_PROPOSAL => 'Proposal',
            self::STAGE_NEGOTIATION => 'Negotiation',
            self::STAGE_CLOSED_WON => 'Closed Won',
            self::STAGE_CLOSED_LOST => 'Closed Lost',
        ];

        return $labels[$this->stage] ?? 'Unknown';
    }

    public function getStageLabelArAttribute()
    {
        $labels = [
            self::STAGE_PROSPECTING => 'البحث عن العملاء',
            self::STAGE_QUALIFICATION => 'التأهيل',
            self::STAGE_NEEDS_ANALYSIS => 'تحليل الاحتياجات',
            self::STAGE_PROPOSAL => 'العرض',
            self::STAGE_NEGOTIATION => 'التفاوض',
            self::STAGE_CLOSED_WON => 'مغلق - فوز',
            self::STAGE_CLOSED_LOST => 'مغلق - خسارة',
        ];

        return $labels[$this->stage] ?? 'غير معروف';
    }

    public function getTypeLabelAttribute()
    {
        $labels = [
            self::TYPE_NEW_BUSINESS => 'New Business',
            self::TYPE_EXISTING_BUSINESS => 'Existing Business',
            self::TYPE_RENEWAL => 'Renewal',
            self::TYPE_UPSELL => 'Upsell',
            self::TYPE_CROSS_SELL => 'Cross-sell',
        ];

        return $labels[$this->type] ?? 'Unknown';
    }

    public function getTypeLabelArAttribute()
    {
        $labels = [
            self::TYPE_NEW_BUSINESS => 'عمل جديد',
            self::TYPE_EXISTING_BUSINESS => 'عمل موجود',
            self::TYPE_RENEWAL => 'تجديد',
            self::TYPE_UPSELL => 'بيع إضافي',
            self::TYPE_CROSS_SELL => 'بيع متقاطع',
        ];

        return $labels[$this->type] ?? 'غير معروف';
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

    public function getWeightedAmountAttribute()
    {
        return $this->amount * ($this->probability / 100);
    }

    public function getIsOverdueAttribute()
    {
        return $this->expected_close_date < now() && 
               !in_array($this->stage, [self::STAGE_CLOSED_WON, self::STAGE_CLOSED_LOST]);
    }

    public function getIsClosingSoonAttribute()
    {
        return $this->expected_close_date <= now()->addDays(7) && 
               !in_array($this->stage, [self::STAGE_CLOSED_WON, self::STAGE_CLOSED_LOST]);
    }

    /**
     * Methods
     */
    public function moveToStage($stage, $notes = null)
    {
        $oldStage = $this->stage;
        
        // Update probability based on stage
        $stageProbabilities = [
            self::STAGE_PROSPECTING => 10,
            self::STAGE_QUALIFICATION => 25,
            self::STAGE_NEEDS_ANALYSIS => 50,
            self::STAGE_PROPOSAL => 75,
            self::STAGE_NEGOTIATION => 90,
            self::STAGE_CLOSED_WON => 100,
            self::STAGE_CLOSED_LOST => 0,
        ];

        $this->update([
            'stage' => $stage,
            'probability' => $stageProbabilities[$stage] ?? $this->probability,
            'actual_close_date' => in_array($stage, [self::STAGE_CLOSED_WON, self::STAGE_CLOSED_LOST]) ? now() : null,
        ]);

        $this->logActivity('stage_change', "Stage changed from {$oldStage} to {$stage}. Notes: {$notes}");

        return $this;
    }

    public function markAsWon($notes = null)
    {
        return $this->moveToStage(self::STAGE_CLOSED_WON, $notes);
    }

    public function markAsLost($reason = null, $competitor = null, $notes = null)
    {
        $this->update([
            'stage' => self::STAGE_CLOSED_LOST,
            'probability' => 0,
            'actual_close_date' => now(),
            'lost_reason' => $reason,
            'competitor' => $competitor,
        ]);

        $this->logActivity('lost', "Opportunity lost. Reason: {$reason}. Competitor: {$competitor}. Notes: {$notes}");

        return $this;
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

    public function updateAmount($amount, $notes = null)
    {
        $oldAmount = $this->amount;
        $this->update(['amount' => $amount]);
        
        $this->logActivity('amount_change', "Amount changed from {$oldAmount} to {$amount}. Notes: {$notes}");
        
        return $this;
    }

    public function updateCloseDate($date, $notes = null)
    {
        $oldDate = $this->expected_close_date;
        $this->update(['expected_close_date' => $date]);
        
        $this->logActivity('close_date_change', "Expected close date changed from {$oldDate} to {$date}. Notes: {$notes}");
        
        return $this;
    }
}
