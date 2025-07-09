<?php

namespace App\Modules\SalesReps\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\User;
use App\Modules\Sales\Models\Customer;
use App\Modules\Sales\Models\SalesOrder;
use App\Modules\Sales\Models\Invoice;
use App\Modules\Sales\Models\Payment;

class SalesRepresentative extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'employee_code',
        'name',
        'name_ar',
        'email',
        'phone',
        'mobile',
        'address',
        'city',
        'governorate',
        'hire_date',
        'birth_date',
        'gender',
        'national_id',
        'base_salary',
        'commission_rate',
        'monthly_target',
        'quarterly_target',
        'annual_target',
        'employment_type',
        'status',
        'supervisor_id',
        'emergency_contact',
        'bank_details',
        'documents',
        'notes',
        'can_create_orders',
        'can_collect_payments',
        'can_view_all_customers',
        'max_discount_percentage',
        'max_order_amount',
        'working_hours',
        'gps_settings',
        'last_location_update',
        'last_latitude',
        'last_longitude',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'hire_date' => 'date',
        'birth_date' => 'date',
        'base_salary' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'monthly_target' => 'decimal:2',
        'quarterly_target' => 'decimal:2',
        'annual_target' => 'decimal:2',
        'can_create_orders' => 'boolean',
        'can_collect_payments' => 'boolean',
        'can_view_all_customers' => 'boolean',
        'max_discount_percentage' => 'decimal:2',
        'max_order_amount' => 'decimal:2',
        'emergency_contact' => 'array',
        'bank_details' => 'array',
        'documents' => 'array',
        'working_hours' => 'array',
        'gps_settings' => 'array',
        'last_location_update' => 'datetime',
        'last_latitude' => 'decimal:8',
        'last_longitude' => 'decimal:8',
    ];

    protected $attributes = [
        'status' => 'active',
        'employment_type' => 'full_time',
        'commission_rate' => 0,
        'can_create_orders' => true,
        'can_collect_payments' => true,
        'can_view_all_customers' => false,
        'max_discount_percentage' => 0,
        'max_order_amount' => 0,
    ];

    // Status constants
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_SUSPENDED = 'suspended';
    const STATUS_TERMINATED = 'terminated';

    // Employment type constants
    const EMPLOYMENT_FULL_TIME = 'full_time';
    const EMPLOYMENT_PART_TIME = 'part_time';
    const EMPLOYMENT_CONTRACT = 'contract';
    const EMPLOYMENT_FREELANCE = 'freelance';

    /**
     * Get the user associated with this sales representative
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the supervisor (another sales representative)
     */
    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(SalesRepresentative::class, 'supervisor_id');
    }

    /**
     * Get subordinates (sales representatives under this supervisor)
     */
    public function subordinates(): HasMany
    {
        return $this->hasMany(SalesRepresentative::class, 'supervisor_id');
    }

    /**
     * Get assigned territories
     */
    public function territories(): BelongsToMany
    {
        return $this->belongsToMany(Territory::class, 'rep_territory_assignments')
                    ->withPivot(['assigned_date', 'effective_from', 'effective_to', 'assignment_type', 'is_active', 'target_amount', 'target_visits_per_month'])
                    ->withTimestamps();
    }

    /**
     * Get assigned customers
     */
    public function customers(): BelongsToMany
    {
        return $this->belongsToMany(Customer::class, 'rep_customer_assignments')
                    ->withPivot(['assigned_date', 'effective_from', 'effective_to', 'assignment_type', 'is_active', 'visit_frequency_days', 'monthly_target', 'priority'])
                    ->withTimestamps();
    }

    /**
     * Get customer visits
     */
    public function visits(): HasMany
    {
        return $this->hasMany(CustomerVisit::class);
    }

    /**
     * Get assigned tasks
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(RepTask::class);
    }

    /**
     * Get performance metrics
     */
    public function performanceMetrics(): HasMany
    {
        return $this->hasMany(RepPerformanceMetric::class);
    }

    /**
     * Get location tracking records
     */
    public function locationTracking(): HasMany
    {
        return $this->hasMany(RepLocationTracking::class);
    }

    /**
     * Get sales orders created by this rep
     */
    public function salesOrders(): HasMany
    {
        return $this->hasMany(SalesOrder::class, 'sales_rep_id');
    }

    /**
     * Get invoices associated with this rep
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'sales_rep_id');
    }

    /**
     * Get payments collected by this rep
     */
    public function paymentsCollected(): HasMany
    {
        return $this->hasMany(Payment::class, 'collected_by_rep_id');
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
     * Scope for active representatives
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope for representatives in a specific territory
     */
    public function scopeInTerritory($query, $territoryId)
    {
        return $query->whereHas('territories', function ($q) use ($territoryId) {
            $q->where('territory_id', $territoryId)->where('is_active', true);
        });
    }

    /**
     * Scope for representatives with specific supervisor
     */
    public function scopeUnderSupervisor($query, $supervisorId)
    {
        return $query->where('supervisor_id', $supervisorId);
    }

    /**
     * Get full name with Arabic fallback
     */
    public function getFullNameAttribute(): string
    {
        return $this->name_ar ?: $this->name;
    }

    /**
     * Check if representative is active
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if representative can perform specific action
     */
    public function canCreateOrders(): bool
    {
        return $this->can_create_orders && $this->isActive();
    }

    public function canCollectPayments(): bool
    {
        return $this->can_collect_payments && $this->isActive();
    }

    /**
     * Get current location
     */
    public function getCurrentLocation(): ?array
    {
        if ($this->last_latitude && $this->last_longitude) {
            return [
                'latitude' => $this->last_latitude,
                'longitude' => $this->last_longitude,
                'updated_at' => $this->last_location_update,
            ];
        }
        return null;
    }

    /**
     * Update location
     */
    public function updateLocation(float $latitude, float $longitude): void
    {
        $this->update([
            'last_latitude' => $latitude,
            'last_longitude' => $longitude,
            'last_location_update' => now(),
        ]);
    }

    /**
     * Get performance summary for a period
     */
    public function getPerformanceSummary(string $period = 'monthly'): array
    {
        $metric = $this->performanceMetrics()
                      ->where('period_type', $period)
                      ->where('metric_date', '>=', now()->startOfMonth())
                      ->first();

        if (!$metric) {
            return [
                'visits_completed' => 0,
                'orders_created' => 0,
                'total_order_value' => 0,
                'payments_collected' => 0,
                'target_achievement_rate' => 0,
            ];
        }

        return [
            'visits_completed' => $metric->visits_completed,
            'orders_created' => $metric->orders_created,
            'total_order_value' => $metric->total_order_value,
            'payments_collected' => $metric->payments_collected,
            'target_achievement_rate' => $metric->target_achievement_rate,
        ];
    }
}
