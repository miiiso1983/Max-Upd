<?php

namespace App\Modules\RegulatoryAffairs\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class PharmaceuticalCompany extends Model
{
    use HasFactory;

    protected $fillable = [
        'registration_number',
        'name',
        'name_ar',
        'name_en',
        'trade_name',
        'trade_name_ar',
        'description',
        'country_of_origin',
        'manufacturer_type',
        'company_type',
        'license_number',
        'license_issue_date',
        'license_expiry_date',
        'license_status',
        'regulatory_authority',
        'gmp_certificate',
        'gmp_expiry_date',
        'iso_certificate',
        'iso_expiry_date',
        'address',
        'city',
        'state',
        'postal_code',
        'phone',
        'fax',
        'email',
        'website',
        'contact_person',
        'contact_person_title',
        'contact_phone',
        'contact_email',
        'pharmacist_name',
        'pharmacist_license',
        'pharmacist_license_expiry',
        'product_categories',
        'therapeutic_areas',
        'annual_turnover',
        'employee_count',
        'notes',
        'documents',
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
        'gmp_expiry_date' => 'date',
        'iso_expiry_date' => 'date',
        'pharmacist_license_expiry' => 'date',
        'last_inspection_date' => 'date',
        'next_inspection_date' => 'date',
        'product_categories' => 'array',
        'therapeutic_areas' => 'array',
        'documents' => 'array',
        'annual_turnover' => 'decimal:2',
    ];

    protected $dates = [
        'license_issue_date',
        'license_expiry_date',
        'gmp_expiry_date',
        'iso_expiry_date',
        'pharmacist_license_expiry',
        'last_inspection_date',
        'next_inspection_date',
    ];

    // Relationships
    public function products(): HasMany
    {
        return $this->hasMany(PharmaceuticalProduct::class);
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
     * Get the documents for this company
     */
    public function documents()
    {
        return $this->hasMany(RegulatoryDocument::class, 'entity_id')
                    ->where('entity_type', 'company');
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

    public function scopeLicenseExpiring($query, $days = 30)
    {
        return $query->where('license_expiry_date', '<=', Carbon::now()->addDays($days))
                    ->where('license_status', 'active');
    }

    public function scopeGmpExpiring($query, $days = 30)
    {
        return $query->where('gmp_expiry_date', '<=', Carbon::now()->addDays($days))
                    ->whereNotNull('gmp_expiry_date');
    }

    public function scopeByCountry($query, $country)
    {
        return $query->where('country_of_origin', $country);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('company_type', $type);
    }

    public function scopeByRiskLevel($query, $level)
    {
        return $query->where('risk_level', $level);
    }

    // Accessors
    public function getDisplayNameAttribute()
    {
        return $this->name_ar ?: $this->name;
    }

    public function getDisplayTradeNameAttribute()
    {
        return $this->trade_name_ar ?: $this->trade_name;
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

    public function getCompanyTypeArabicAttribute()
    {
        $types = [
            'manufacturer' => 'مصنع',
            'distributor' => 'موزع',
            'importer' => 'مستورد',
            'exporter' => 'مصدر',
        ];

        return $types[$this->company_type] ?? $this->company_type;
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

    // Mutators
    public function setProductCategoriesAttribute($value)
    {
        $this->attributes['product_categories'] = is_array($value) ? json_encode($value) : $value;
    }

    public function setTherapeuticAreasAttribute($value)
    {
        $this->attributes['therapeutic_areas'] = is_array($value) ? json_encode($value) : $value;
    }

    public function setDocumentsAttribute($value)
    {
        $this->attributes['documents'] = is_array($value) ? json_encode($value) : $value;
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

    public function isGmpExpiring($days = 30)
    {
        return $this->gmp_expiry_date && 
               $this->gmp_expiry_date->lte(Carbon::now()->addDays($days));
    }

    public function isGmpExpired()
    {
        return $this->gmp_expiry_date && 
               $this->gmp_expiry_date->lt(Carbon::now());
    }

    public function isIsoExpiring($days = 30)
    {
        return $this->iso_expiry_date && 
               $this->iso_expiry_date->lte(Carbon::now()->addDays($days));
    }

    public function isIsoExpired()
    {
        return $this->iso_expiry_date && 
               $this->iso_expiry_date->lt(Carbon::now());
    }

    public function isPharmacistLicenseExpiring($days = 30)
    {
        return $this->pharmacist_license_expiry && 
               $this->pharmacist_license_expiry->lte(Carbon::now()->addDays($days));
    }

    public function isPharmacistLicenseExpired()
    {
        return $this->pharmacist_license_expiry && 
               $this->pharmacist_license_expiry->lt(Carbon::now());
    }

    public function needsInspection()
    {
        return $this->next_inspection_date && 
               $this->next_inspection_date->lte(Carbon::now());
    }

    public function getDaysUntilLicenseExpiry()
    {
        if (!$this->license_expiry_date) {
            return null;
        }

        return Carbon::now()->diffInDays($this->license_expiry_date, false);
    }

    public function getDaysUntilGmpExpiry()
    {
        if (!$this->gmp_expiry_date) {
            return null;
        }

        return Carbon::now()->diffInDays($this->gmp_expiry_date, false);
    }

    public function getActiveProductsCount()
    {
        return $this->products()->where('status', 'active')->count();
    }

    public function getLastInspectionResult()
    {
        return $this->inspections()
                   ->where('inspection_status', 'completed')
                   ->latest('inspection_date')
                   ->first()?->inspection_result;
    }

    public function hasValidLicense()
    {
        return $this->license_status === 'active' && !$this->isLicenseExpired();
    }

    public function hasValidGmp()
    {
        return $this->gmp_certificate && !$this->isGmpExpired();
    }

    public function hasValidIso()
    {
        return $this->iso_certificate && !$this->isIsoExpired();
    }

    public function isCompliant()
    {
        return $this->hasValidLicense() && 
               $this->status === 'active' &&
               (!$this->gmp_certificate || $this->hasValidGmp());
    }

    public function getComplianceScore()
    {
        $score = 0;
        $maxScore = 100;

        // License compliance (40 points)
        if ($this->hasValidLicense()) {
            $score += 40;
        } elseif ($this->license_status === 'active') {
            $score += 20; // Active but might be expiring
        }

        // GMP compliance (30 points)
        if ($this->hasValidGmp()) {
            $score += 30;
        } elseif ($this->gmp_certificate) {
            $score += 15; // Has certificate but might be expired
        }

        // ISO compliance (20 points)
        if ($this->hasValidIso()) {
            $score += 20;
        } elseif ($this->iso_certificate) {
            $score += 10; // Has certificate but might be expired
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
