<?php

namespace App\Modules\RegulatoryAffairs\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class PharmaceuticalBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'pharmaceutical_product_id',
        'batch_number',
        'lot_number',
        'manufacturing_date',
        'expiry_date',
        'quantity_manufactured',
        'quantity_released',
        'quantity_recalled',
        'quantity_destroyed',
        'manufacturing_site',
        'packaging_site',
        'batch_status',
        'batch_record',
        'raw_materials',
        'packaging_materials',
        'production_line',
        'shift',
        'supervisor',
        'production_notes',
        'yield_percentage',
        'requires_testing',
        'testing_status',
        'testing_start_date',
        'testing_completion_date',
        'testing_laboratory',
        'testing_technician',
        'testing_notes',
        'test_results',
        'stability_testing_required',
        'stability_testing_start',
        'stability_testing_end',
        'stability_status',
        'release_date',
        'released_by',
        'release_notes',
        'certificate_of_analysis',
        'recall_issued',
        'recall_date',
        'recall_reason',
        'recall_level',
        'regulatory_notification_sent',
        'regulatory_notification_date',
        'documents',
        'notes',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'manufacturing_date' => 'date',
        'expiry_date' => 'date',
        'testing_start_date' => 'date',
        'testing_completion_date' => 'date',
        'stability_testing_start' => 'date',
        'stability_testing_end' => 'date',
        'release_date' => 'date',
        'recall_date' => 'date',
        'regulatory_notification_date' => 'date',
        'raw_materials' => 'array',
        'packaging_materials' => 'array',
        'test_results' => 'array',
        'documents' => 'array',
        'yield_percentage' => 'decimal:2',
        'requires_testing' => 'boolean',
        'stability_testing_required' => 'boolean',
        'recall_issued' => 'boolean',
        'regulatory_notification_sent' => 'boolean',
    ];

    protected $dates = [
        'manufacturing_date',
        'expiry_date',
        'testing_start_date',
        'testing_completion_date',
        'stability_testing_start',
        'stability_testing_end',
        'release_date',
        'recall_date',
        'regulatory_notification_date',
    ];

    // Relationships
    public function product(): BelongsTo
    {
        return $this->belongsTo(PharmaceuticalProduct::class, 'pharmaceutical_product_id');
    }

    public function tests(): HasMany
    {
        return $this->hasMany(PharmaceuticalTest::class);
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
     * Get the documents for this batch
     */
    public function documents()
    {
        return $this->hasMany(RegulatoryDocument::class, 'entity_id')
                    ->where('entity_type', 'batch');
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

    public function scopeReleased($query)
    {
        return $query->where('batch_status', 'released');
    }

    public function scopeInTesting($query)
    {
        return $query->where('testing_status', 'in_progress');
    }

    public function scopePendingTesting($query)
    {
        return $query->where('testing_status', 'pending');
    }

    public function scopeTestingPassed($query)
    {
        return $query->where('testing_status', 'passed');
    }

    public function scopeTestingFailed($query)
    {
        return $query->where('testing_status', 'failed');
    }

    public function scopeExpiring($query, $days = 30)
    {
        return $query->where('expiry_date', '<=', Carbon::now()->addDays($days))
                    ->where('batch_status', 'released');
    }

    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', Carbon::now())
                    ->where('batch_status', 'released');
    }

    public function scopeRecalled($query)
    {
        return $query->where('recall_issued', true);
    }

    public function scopeByProduct($query, $productId)
    {
        return $query->where('pharmaceutical_product_id', $productId);
    }

    // Accessors
    public function getBatchStatusArabicAttribute()
    {
        $statuses = [
            'in_production' => 'تحت الإنتاج',
            'testing' => 'تحت الفحص',
            'released' => 'مطلق',
            'quarantine' => 'حجر صحي',
            'rejected' => 'مرفوض',
            'recalled' => 'مسحوب',
            'destroyed' => 'متلف',
        ];

        return $statuses[$this->batch_status] ?? $this->batch_status;
    }

    public function getTestingStatusArabicAttribute()
    {
        $statuses = [
            'pending' => 'في الانتظار',
            'in_progress' => 'قيد التنفيذ',
            'passed' => 'نجح',
            'failed' => 'فشل',
            'conditional' => 'مشروط',
        ];

        return $statuses[$this->testing_status] ?? $this->testing_status;
    }

    public function getStabilityStatusArabicAttribute()
    {
        $statuses = [
            'not_started' => 'لم يبدأ',
            'ongoing' => 'جاري',
            'completed' => 'مكتمل',
            'failed' => 'فشل',
        ];

        return $statuses[$this->stability_status] ?? $this->stability_status;
    }

    public function getRecallLevelArabicAttribute()
    {
        $levels = [
            'consumer' => 'مستهلك',
            'retail' => 'تجزئة',
            'wholesale' => 'جملة',
        ];

        return $levels[$this->recall_level] ?? $this->recall_level;
    }

    // Helper methods
    public function isExpiring($days = 30)
    {
        return $this->expiry_date && 
               $this->expiry_date->lte(Carbon::now()->addDays($days)) &&
               $this->batch_status === 'released';
    }

    public function isExpired()
    {
        return $this->expiry_date && 
               $this->expiry_date->lt(Carbon::now());
    }

    public function isReleased()
    {
        return $this->batch_status === 'released';
    }

    public function isRecalled()
    {
        return $this->recall_issued;
    }

    public function canBeReleased()
    {
        return $this->testing_status === 'passed' && 
               $this->batch_status === 'testing' &&
               !$this->isExpired();
    }

    public function needsTesting()
    {
        return $this->requires_testing && 
               in_array($this->testing_status, ['pending', 'in_progress']);
    }

    public function needsStabilityTesting()
    {
        return $this->stability_testing_required && 
               $this->stability_status === 'not_started';
    }

    public function getDaysUntilExpiry()
    {
        if (!$this->expiry_date) {
            return null;
        }

        return Carbon::now()->diffInDays($this->expiry_date, false);
    }

    public function getShelfLifeRemaining()
    {
        if (!$this->expiry_date) {
            return null;
        }

        $totalShelfLife = $this->manufacturing_date->diffInDays($this->expiry_date);
        $daysUsed = $this->manufacturing_date->diffInDays(Carbon::now());
        
        return max(0, round((($totalShelfLife - $daysUsed) / $totalShelfLife) * 100, 1));
    }

    public function getQuantityAvailable()
    {
        return $this->quantity_released - $this->quantity_recalled - $this->quantity_destroyed;
    }

    public function getYieldPercentage()
    {
        if (!$this->yield_percentage) {
            return null;
        }

        return $this->yield_percentage;
    }

    public function getTestingDuration()
    {
        if (!$this->testing_start_date || !$this->testing_completion_date) {
            return null;
        }

        return $this->testing_start_date->diffInDays($this->testing_completion_date);
    }

    public function getPassedTestsCount()
    {
        return $this->tests()->where('test_result', 'pass')->count();
    }

    public function getFailedTestsCount()
    {
        return $this->tests()->where('test_result', 'fail')->count();
    }

    public function getPendingTestsCount()
    {
        return $this->tests()->where('test_result', 'pending')->count();
    }

    public function getTotalTestsCount()
    {
        return $this->tests()->count();
    }

    public function getTestingProgress()
    {
        $total = $this->getTotalTestsCount();
        if ($total === 0) {
            return 0;
        }

        $completed = $this->tests()->whereIn('test_result', ['pass', 'fail'])->count();
        return round(($completed / $total) * 100, 1);
    }

    public function hasFailedTests()
    {
        return $this->getFailedTestsCount() > 0;
    }

    public function allTestsPassed()
    {
        $total = $this->getTotalTestsCount();
        return $total > 0 && $this->getPassedTestsCount() === $total;
    }

    public function getComplianceScore()
    {
        $score = 0;
        $maxScore = 100;

        // Testing compliance (40 points)
        if ($this->testing_status === 'passed') {
            $score += 40;
        } elseif ($this->testing_status === 'conditional') {
            $score += 30;
        } elseif ($this->testing_status === 'in_progress') {
            $score += 20;
        }

        // Batch status (30 points)
        if ($this->batch_status === 'released') {
            $score += 30;
        } elseif ($this->batch_status === 'testing') {
            $score += 20;
        } elseif ($this->batch_status === 'in_production') {
            $score += 10;
        }

        // Expiry status (20 points)
        if (!$this->isExpired()) {
            if ($this->isExpiring(30)) {
                $score += 15; // Expiring soon
            } else {
                $score += 20; // Not expiring
            }
        }

        // Recall status (10 points)
        if (!$this->isRecalled()) {
            $score += 10;
        }

        return round(($score / $maxScore) * 100);
    }

    public function getQualityStatus()
    {
        if ($this->isRecalled()) {
            return 'recalled';
        }

        if ($this->testing_status === 'failed') {
            return 'failed';
        }

        if ($this->isExpired()) {
            return 'expired';
        }

        if ($this->isExpiring(30)) {
            return 'expiring';
        }

        if ($this->testing_status === 'passed' && $this->batch_status === 'released') {
            return 'good';
        }

        return 'pending';
    }
}
