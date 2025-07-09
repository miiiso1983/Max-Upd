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

class Territory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'name_ar',
        'code',
        'description',
        'governorate',
        'cities',
        'areas',
        'postal_codes',
        'boundaries',
        'center_latitude',
        'center_longitude',
        'radius_km',
        'type',
        'estimated_customers',
        'estimated_potential',
        'difficulty_level',
        'transportation_info',
        'is_active',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'cities' => 'array',
        'areas' => 'array',
        'postal_codes' => 'array',
        'boundaries' => 'array',
        'center_latitude' => 'decimal:8',
        'center_longitude' => 'decimal:8',
        'estimated_potential' => 'decimal:2',
        'transportation_info' => 'array',
        'is_active' => 'boolean',
    ];

    protected $attributes = [
        'type' => 'mixed',
        'difficulty_level' => 'medium',
        'estimated_customers' => 0,
        'estimated_potential' => 0,
        'is_active' => true,
    ];

    // Type constants
    const TYPE_URBAN = 'urban';
    const TYPE_RURAL = 'rural';
    const TYPE_MIXED = 'mixed';

    // Difficulty level constants
    const DIFFICULTY_EASY = 'easy';
    const DIFFICULTY_MEDIUM = 'medium';
    const DIFFICULTY_HARD = 'hard';

    /**
     * Get assigned sales representatives
     */
    public function salesRepresentatives(): BelongsToMany
    {
        return $this->belongsToMany(SalesRepresentative::class, 'rep_territory_assignments')
                    ->withPivot(['assigned_date', 'effective_from', 'effective_to', 'assignment_type', 'is_active', 'target_amount', 'target_visits_per_month'])
                    ->withTimestamps();
    }

    /**
     * Get customers in this territory
     */
    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class, 'territory_id');
    }

    /**
     * Get visits in this territory
     */
    public function visits(): HasMany
    {
        return $this->hasMany(CustomerVisit::class);
    }

    /**
     * Get tasks for this territory
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(RepTask::class);
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
     * Scope for active territories
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for territories in specific governorate
     */
    public function scopeInGovernorate($query, $governorate)
    {
        return $query->where('governorate', $governorate);
    }

    /**
     * Scope for territories by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get full name with Arabic fallback
     */
    public function getFullNameAttribute(): string
    {
        return $this->name_ar ?: $this->name;
    }

    /**
     * Check if territory is active
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Get center coordinates
     */
    public function getCenterCoordinates(): ?array
    {
        if ($this->center_latitude && $this->center_longitude) {
            return [
                'latitude' => $this->center_latitude,
                'longitude' => $this->center_longitude,
            ];
        }
        return null;
    }

    /**
     * Check if coordinates are within territory
     */
    public function containsCoordinates(float $latitude, float $longitude): bool
    {
        if (!$this->center_latitude || !$this->center_longitude || !$this->radius_km) {
            return false;
        }

        $distance = $this->calculateDistance(
            $this->center_latitude,
            $this->center_longitude,
            $latitude,
            $longitude
        );

        return $distance <= $this->radius_km;
    }

    /**
     * Calculate distance between two coordinates (Haversine formula)
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
     * Get territory statistics
     */
    public function getStatistics(): array
    {
        $activeReps = $this->salesRepresentatives()
                          ->wherePivot('is_active', true)
                          ->count();

        $totalCustomers = $this->customers()->count();
        $activeCustomers = $this->customers()->where('is_active', true)->count();

        $thisMonthVisits = $this->visits()
                               ->where('visit_date', '>=', now()->startOfMonth())
                               ->count();

        return [
            'active_representatives' => $activeReps,
            'total_customers' => $totalCustomers,
            'active_customers' => $activeCustomers,
            'visits_this_month' => $thisMonthVisits,
            'coverage_percentage' => $totalCustomers > 0 ? 
                round(($thisMonthVisits / $totalCustomers) * 100, 2) : 0,
        ];
    }

    /**
     * Get current primary representative
     */
    public function getPrimaryRepresentative(): ?SalesRepresentative
    {
        return $this->salesRepresentatives()
                   ->wherePivot('assignment_type', 'primary')
                   ->wherePivot('is_active', true)
                   ->first();
    }

    /**
     * Get all active representatives
     */
    public function getActiveRepresentatives()
    {
        return $this->salesRepresentatives()
                   ->wherePivot('is_active', true)
                   ->get();
    }
}
