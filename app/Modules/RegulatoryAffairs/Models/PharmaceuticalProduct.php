<?php

namespace App\Modules\RegulatoryAffairs\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class PharmaceuticalProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'registration_number',
        'trade_name',
        'trade_name_ar',
        'generic_name',
        'generic_name_ar',
        'active_ingredient',
        'active_ingredient_ar',
        'strength',
        'dosage_form',
        'dosage_form_ar',
        'route_of_administration',
        'route_of_administration_ar',
        'pack_size',
        'pack_type',
        'pharmaceutical_company_id',
        'manufacturer_name',
        'country_of_origin',
        'therapeutic_class',
        'therapeutic_class_ar',
        'atc_code',
        'indication',
        'indication_ar',
        'contraindication',
        'contraindication_ar',
        'side_effects',
        'side_effects_ar',
        'dosage_instructions',
        'dosage_instructions_ar',
        'storage_conditions',
        'storage_conditions_ar',
        'shelf_life_months',
        'license_number',
        'license_issue_date',
        'license_expiry_date',
        'license_status',
        'regulatory_authority',
        'prescription_status',
        'controlled_substance_schedule',
        'requires_cold_chain',
        'min_temperature',
        'max_temperature',
        'light_sensitive',
        'moisture_sensitive',
        'price_ceiling',
        'wholesale_price',
        'retail_price',
        'barcode',
        'ndc_number',
        'is_generic',
        'reference_product',
        'bioequivalence_required',
        'bioequivalence_expiry',
        'market_status',
        'market_launch_date',
        'withdrawal_reason',
        'documents',
        'notes',
        'status',
        'last_inspection_date',
        'next_inspection_date',
        'risk_level',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'license_issue_date' => 'date',
        'license_expiry_date' => 'date',
        'bioequivalence_expiry' => 'date',
        'market_launch_date' => 'date',
        'last_inspection_date' => 'date',
        'next_inspection_date' => 'date',
        'requires_cold_chain' => 'boolean',
        'light_sensitive' => 'boolean',
        'moisture_sensitive' => 'boolean',
        'is_generic' => 'boolean',
        'bioequivalence_required' => 'boolean',
        'documents' => 'array',
        'min_temperature' => 'decimal:2',
        'max_temperature' => 'decimal:2',
        'price_ceiling' => 'decimal:2',
        'wholesale_price' => 'decimal:2',
        'retail_price' => 'decimal:2',
    ];

    protected $dates = [
        'license_issue_date',
        'license_expiry_date',
        'bioequivalence_expiry',
        'market_launch_date',
        'last_inspection_date',
        'next_inspection_date',
    ];

    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(PharmaceuticalCompany::class, 'pharmaceutical_company_id');
    }

    public function batches(): HasMany
    {
        return $this->hasMany(PharmaceuticalBatch::class);
    }

    public function inspections(): HasMany
    {
        return $this->hasMany(PharmaceuticalInspection::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Get the documents for this product
     */
    public function documents()
    {
        return $this->hasMany(RegulatoryDocument::class, 'entity_id')
                    ->where('entity_type', 'product');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeMarketed($query)
    {
        return $query->where('market_status', 'marketed');
    }

    public function scopeLicenseExpiring($query, $days = 30)
    {
        return $query->where('license_expiry_date', '<=', Carbon::now()->addDays($days))
                    ->where('license_status', 'active');
    }

    public function scopeByTherapeuticClass($query, $class)
    {
        return $query->where('therapeutic_class', $class);
    }

    public function scopeByPrescriptionStatus($query, $status)
    {
        return $query->where('prescription_status', $status);
    }

    public function scopeGeneric($query)
    {
        return $query->where('is_generic', true);
    }

    public function scopeBrand($query)
    {
        return $query->where('is_generic', false);
    }

    public function scopeControlled($query)
    {
        return $query->where('prescription_status', 'controlled');
    }

    public function scopeColdChain($query)
    {
        return $query->where('requires_cold_chain', true);
    }

    // Accessors
    public function getDisplayTradeNameAttribute()
    {
        return $this->trade_name_ar ?: $this->trade_name;
    }

    public function getDisplayGenericNameAttribute()
    {
        return $this->generic_name_ar ?: $this->generic_name;
    }

    public function getDisplayActiveIngredientAttribute()
    {
        return $this->active_ingredient_ar ?: $this->active_ingredient;
    }

    public function getDisplayDosageFormAttribute()
    {
        return $this->dosage_form_ar ?: $this->dosage_form;
    }

    public function getDisplayTherapeuticClassAttribute()
    {
        return $this->therapeutic_class_ar ?: $this->therapeutic_class;
    }

    public function getLicenseStatusArabicAttribute()
    {
        $statuses = [
            'active' => 'نشط',
            'expired' => 'منتهي',
            'suspended' => 'معلق',
            'cancelled' => 'ملغي',
        ];

        return $statuses[$this->license_status] ?? $this->license_status;
    }

    public function getPrescriptionStatusArabicAttribute()
    {
        $statuses = [
            'prescription' => 'بوصفة طبية',
            'otc' => 'بدون وصفة طبية',
            'controlled' => 'مادة مخدرة',
        ];

        return $statuses[$this->prescription_status] ?? $this->prescription_status;
    }

    public function getMarketStatusArabicAttribute()
    {
        $statuses = [
            'marketed' => 'مطروح في السوق',
            'not_marketed' => 'غير مطروح',
            'discontinued' => 'متوقف',
            'withdrawn' => 'مسحوب',
        ];

        return $statuses[$this->market_status] ?? $this->market_status;
    }

    public function getStatusArabicAttribute()
    {
        $statuses = [
            'active' => 'نشط',
            'inactive' => 'غير نشط',
            'under_review' => 'تحت المراجعة',
            'suspended' => 'معلق',
        ];

        return $statuses[$this->status] ?? $this->status;
    }

    public function getRiskLevelArabicAttribute()
    {
        $levels = [
            'low' => 'منخفض',
            'medium' => 'متوسط',
            'high' => 'عالي',
        ];

        return $levels[$this->risk_level] ?? $this->risk_level;
    }

    // Helper methods
    public function isLicenseExpiring($days = 30)
    {
        return $this->license_expiry_date && 
               $this->license_expiry_date->lte(Carbon::now()->addDays($days)) &&
               $this->license_status === 'active';
    }

    public function isLicenseExpired()
    {
        return $this->license_expiry_date && 
               $this->license_expiry_date->lt(Carbon::now());
    }

    public function isBioequivalenceExpiring($days = 30)
    {
        return $this->bioequivalence_expiry && 
               $this->bioequivalence_expiry->lte(Carbon::now()->addDays($days));
    }

    public function isBioequivalenceExpired()
    {
        return $this->bioequivalence_expiry && 
               $this->bioequivalence_expiry->lt(Carbon::now());
    }

    public function needsInspection()
    {
        return $this->next_inspection_date && 
               $this->next_inspection_date->lte(Carbon::now());
    }

    public function hasValidLicense()
    {
        return $this->license_status === 'active' && !$this->isLicenseExpired();
    }

    public function isMarketable()
    {
        return $this->hasValidLicense() && 
               $this->status === 'active' &&
               $this->market_status === 'marketed';
    }

    public function getDaysUntilLicenseExpiry()
    {
        if (!$this->license_expiry_date) {
            return null;
        }

        return Carbon::now()->diffInDays($this->license_expiry_date, false);
    }

    public function getActiveBatchesCount()
    {
        return $this->batches()->where('batch_status', 'released')->count();
    }

    public function getLastInspectionResult()
    {
        return $this->inspections()
                   ->where('inspection_status', 'completed')
                   ->latest('inspection_date')
                   ->first()?->inspection_result;
    }

    public function getTemperatureRange()
    {
        if ($this->min_temperature && $this->max_temperature) {
            return "{$this->min_temperature}°C - {$this->max_temperature}°C";
        } elseif ($this->min_temperature) {
            return "أعلى من {$this->min_temperature}°C";
        } elseif ($this->max_temperature) {
            return "أقل من {$this->max_temperature}°C";
        }

        return 'غير محدد';
    }

    public function getStorageRequirements()
    {
        $requirements = [];

        if ($this->requires_cold_chain) {
            $requirements[] = 'سلسلة تبريد';
        }

        if ($this->light_sensitive) {
            $requirements[] = 'حماية من الضوء';
        }

        if ($this->moisture_sensitive) {
            $requirements[] = 'حماية من الرطوبة';
        }

        if ($this->min_temperature || $this->max_temperature) {
            $requirements[] = $this->getTemperatureRange();
        }

        return $requirements;
    }

    public function isControlledSubstance()
    {
        return $this->prescription_status === 'controlled';
    }

    public function requiresPrescription()
    {
        return in_array($this->prescription_status, ['prescription', 'controlled']);
    }

    public function getShelfLifeInYears()
    {
        return round($this->shelf_life_months / 12, 1);
    }

    public function getComplianceScore()
    {
        $score = 0;
        $maxScore = 100;

        // License compliance (50 points)
        if ($this->hasValidLicense()) {
            $score += 50;
        } elseif ($this->license_status === 'active') {
            $score += 25; // Active but might be expiring
        }

        // Bioequivalence compliance (20 points) - if required
        if ($this->bioequivalence_required) {
            if (!$this->isBioequivalenceExpired()) {
                $score += 20;
            } else {
                $score += 10; // Has study but expired
            }
        } else {
            $score += 20; // Not required, so full points
        }

        // Market status (20 points)
        if ($this->market_status === 'marketed') {
            $score += 20;
        } elseif ($this->market_status === 'not_marketed') {
            $score += 10;
        }

        // General status (10 points)
        if ($this->status === 'active') {
            $score += 10;
        } elseif ($this->status === 'under_review') {
            $score += 5;
        }

        return round(($score / $maxScore) * 100);
    }
}
