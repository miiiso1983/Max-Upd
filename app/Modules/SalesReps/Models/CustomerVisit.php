<?php

namespace App\Modules\SalesReps\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;
use App\Modules\Sales\Models\Customer;
use App\Modules\Sales\Models\SalesOrder;
use App\Modules\Sales\Models\Payment;

class CustomerVisit extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'sales_rep_id',
        'customer_id',
        'visit_date',
        'check_in_time',
        'check_out_time',
        'duration_minutes',
        'visit_type',
        'status',
        'outcome',
        'check_in_latitude',
        'check_in_longitude',
        'check_out_latitude',
        'check_out_longitude',
        'location_accuracy_meters',
        'location_verified',
        'contact_person',
        'contact_phone',
        'visit_purpose',
        'discussion_points',
        'customer_feedback',
        'visit_notes',
        'next_action_required',
        'next_visit_date',
        'order_created',
        'order_amount',
        'payment_collected',
        'payment_amount',
        'complaint_received',
        'complaint_details',
        'photos',
        'documents',
        'voice_notes',
        'customer_satisfaction_rating',
        'visit_quality_rating',
        'improvement_suggestions',
        'device_info',
        'app_version',
        'synced',
        'synced_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'visit_date' => 'datetime',
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
        'next_visit_date' => 'date',
        'check_in_latitude' => 'decimal:8',
        'check_in_longitude' => 'decimal:8',
        'check_out_latitude' => 'decimal:8',
        'check_out_longitude' => 'decimal:8',
        'location_verified' => 'boolean',
        'order_created' => 'boolean',
        'order_amount' => 'decimal:2',
        'payment_collected' => 'boolean',
        'payment_amount' => 'decimal:2',
        'complaint_received' => 'boolean',
        'photos' => 'array',
        'documents' => 'array',
        'voice_notes' => 'array',
        'device_info' => 'array',
        'synced' => 'boolean',
        'synced_at' => 'datetime',
    ];

    protected $attributes = [
        'visit_type' => 'scheduled',
        'status' => 'planned',
        'location_verified' => false,
        'order_created' => false,
        'order_amount' => 0,
        'payment_collected' => false,
        'payment_amount' => 0,
        'complaint_received' => false,
        'synced' => false,
    ];

    // Visit type constants
    const TYPE_SCHEDULED = 'scheduled';
    const TYPE_UNSCHEDULED = 'unscheduled';
    const TYPE_FOLLOW_UP = 'follow_up';
    const TYPE_EMERGENCY = 'emergency';
    const TYPE_COLLECTION = 'collection';

    // Status constants
    const STATUS_PLANNED = 'planned';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_NO_SHOW = 'no_show';

    // Outcome constants
    const OUTCOME_SUCCESSFUL = 'successful';
    const OUTCOME_PARTIALLY_SUCCESSFUL = 'partially_successful';
    const OUTCOME_UNSUCCESSFUL = 'unsuccessful';
    const OUTCOME_RESCHEDULED = 'rescheduled';

    /**
     * Get the sales representative
     */
    public function salesRepresentative(): BelongsTo
    {
        return $this->belongsTo(SalesRepresentative::class, 'sales_rep_id');
    }

    /**
     * Get the customer
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get orders created during this visit
     */
    public function orders(): HasMany
    {
        return $this->hasMany(SalesOrder::class, 'visit_id');
    }

    /**
     * Get payments collected during this visit
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'visit_id');
    }

    /**
     * Get related tasks
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(RepTask::class, 'related_visit_id');
    }

    /**
     * Get the user who created this record
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this record
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope for visits by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for visits by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('visit_type', $type);
    }

    /**
     * Scope for completed visits
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope for visits in date range
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('visit_date', [$startDate, $endDate]);
    }

    /**
     * Scope for visits by sales rep
     */
    public function scopeBySalesRep($query, $salesRepId)
    {
        return $query->where('sales_rep_id', $salesRepId);
    }

    /**
     * Check if visit is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if visit is in progress
     */
    public function isInProgress(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    /**
     * Calculate visit duration
     */
    public function calculateDuration(): ?int
    {
        if ($this->check_in_time && $this->check_out_time) {
            return $this->check_in_time->diffInMinutes($this->check_out_time);
        }
        return null;
    }

    /**
     * Start visit (check in)
     */
    public function checkIn(float $latitude, float $longitude, array $deviceInfo = []): void
    {
        $this->update([
            'check_in_time' => now(),
            'check_in_latitude' => $latitude,
            'check_in_longitude' => $longitude,
            'status' => self::STATUS_IN_PROGRESS,
            'device_info' => $deviceInfo,
        ]);
    }

    /**
     * End visit (check out)
     */
    public function checkOut(float $latitude, float $longitude, array $data = []): void
    {
        $duration = $this->check_in_time ? 
            $this->check_in_time->diffInMinutes(now()) : null;

        $updateData = array_merge([
            'check_out_time' => now(),
            'check_out_latitude' => $latitude,
            'check_out_longitude' => $longitude,
            'duration_minutes' => $duration,
            'status' => self::STATUS_COMPLETED,
        ], $data);

        $this->update($updateData);
    }

    /**
     * Get check-in location
     */
    public function getCheckInLocation(): ?array
    {
        if ($this->check_in_latitude && $this->check_in_longitude) {
            return [
                'latitude' => $this->check_in_latitude,
                'longitude' => $this->check_in_longitude,
                'time' => $this->check_in_time,
            ];
        }
        return null;
    }

    /**
     * Get check-out location
     */
    public function getCheckOutLocation(): ?array
    {
        if ($this->check_out_latitude && $this->check_out_longitude) {
            return [
                'latitude' => $this->check_out_latitude,
                'longitude' => $this->check_out_longitude,
                'time' => $this->check_out_time,
            ];
        }
        return null;
    }

    /**
     * Verify location against customer location
     */
    public function verifyLocation(): bool
    {
        if (!$this->customer->latitude || !$this->customer->longitude) {
            return false;
        }

        if (!$this->check_in_latitude || !$this->check_in_longitude) {
            return false;
        }

        $distance = $this->calculateDistance(
            $this->customer->latitude,
            $this->customer->longitude,
            $this->check_in_latitude,
            $this->check_in_longitude
        );

        // Consider location verified if within 100 meters
        $isVerified = $distance <= 0.1; // 0.1 km = 100 meters

        $this->update(['location_verified' => $isVerified]);

        return $isVerified;
    }

    /**
     * Calculate distance between two coordinates
     */
    private function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // Earth's radius in kilometers

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Get visit summary
     */
    public function getSummary(): array
    {
        return [
            'id' => $this->id,
            'customer_name' => $this->customer->name,
            'visit_date' => $this->visit_date->format('Y-m-d H:i'),
            'duration' => $this->duration_minutes ? $this->duration_minutes . ' دقيقة' : 'غير محدد',
            'status' => $this->status,
            'outcome' => $this->outcome,
            'order_created' => $this->order_created,
            'order_amount' => $this->order_amount,
            'payment_collected' => $this->payment_collected,
            'payment_amount' => $this->payment_amount,
            'location_verified' => $this->location_verified,
        ];
    }
}
