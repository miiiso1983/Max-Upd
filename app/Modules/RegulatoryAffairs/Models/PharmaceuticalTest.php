<?php

namespace App\Modules\RegulatoryAffairs\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class PharmaceuticalTest extends Model
{
    use HasFactory;

    protected $fillable = [
        'pharmaceutical_batch_id',
        'test_type',
        'test_name',
        'test_name_ar',
        'test_description',
        'test_method',
        'test_standard',
        'acceptance_criteria',
        'test_parameter',
        'unit_of_measurement',
        'expected_min_value',
        'expected_max_value',
        'expected_result',
        'actual_value',
        'actual_result',
        'test_result',
        'deviation_reason',
        'corrective_action',
        'test_date',
        'test_time',
        'tested_by',
        'reviewed_by',
        'approved_by',
        'review_date',
        'approval_date',
        'laboratory',
        'instrument_used',
        'instrument_id',
        'instrument_calibration_date',
        'reagent_lot',
        'reagent_expiry',
        'environmental_conditions',
        'temperature',
        'humidity',
        'sample_size',
        'sample_preparation',
        'test_procedure',
        'observations',
        'raw_data',
        'calculations',
        'certificate_number',
        'is_stability_test',
        'stability_condition',
        'stability_time_point',
        'stability_time_unit',
        'is_retest',
        'original_test_id',
        'retest_reason',
        'requires_investigation',
        'investigation_notes',
        'investigation_status',
        'attachments',
        'notes',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'test_date' => 'date',
        'test_time' => 'datetime',
        'review_date' => 'date',
        'approval_date' => 'date',
        'instrument_calibration_date' => 'date',
        'reagent_expiry' => 'date',
        'expected_min_value' => 'decimal:4',
        'expected_max_value' => 'decimal:4',
        'actual_value' => 'decimal:4',
        'temperature' => 'decimal:2',
        'humidity' => 'decimal:2',
        'raw_data' => 'array',
        'calculations' => 'array',
        'attachments' => 'array',
        'is_stability_test' => 'boolean',
        'is_retest' => 'boolean',
        'requires_investigation' => 'boolean',
    ];

    protected $dates = [
        'test_date',
        'test_time',
        'review_date',
        'approval_date',
        'instrument_calibration_date',
        'reagent_expiry',
    ];

    // Relationships
    public function batch(): BelongsTo
    {
        return $this->belongsTo(PharmaceuticalBatch::class, 'pharmaceutical_batch_id');
    }

    public function originalTest(): BelongsTo
    {
        return $this->belongsTo(PharmaceuticalTest::class, 'original_test_id');
    }

    public function retests()
    {
        return $this->hasMany(PharmaceuticalTest::class, 'original_test_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Get the documents for this test
     */
    public function documents()
    {
        return $this->hasMany(RegulatoryDocument::class, 'entity_id')
                    ->where('entity_type', 'test');
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

    public function scopePassed($query)
    {
        return $query->where('test_result', 'pass');
    }

    public function scopeFailed($query)
    {
        return $query->where('test_result', 'fail');
    }

    public function scopePending($query)
    {
        return $query->where('test_result', 'pending');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('test_type', $type);
    }

    public function scopeByLaboratory($query, $laboratory)
    {
        return $query->where('laboratory', $laboratory);
    }

    public function scopeStabilityTests($query)
    {
        return $query->where('is_stability_test', true);
    }

    public function scopeRetests($query)
    {
        return $query->where('is_retest', true);
    }

    public function scopeRequiringInvestigation($query)
    {
        return $query->where('requires_investigation', true);
    }

    public function scopeByBatch($query, $batchId)
    {
        return $query->where('pharmaceutical_batch_id', $batchId);
    }

    // Accessors
    public function getDisplayTestNameAttribute()
    {
        return $this->test_name_ar ?: $this->test_name;
    }

    public function getTestResultArabicAttribute()
    {
        $results = [
            'pass' => 'نجح',
            'fail' => 'فشل',
            'pending' => 'في الانتظار',
            'retest' => 'إعادة فحص',
            'out_of_specification' => 'خارج المواصفات',
        ];

        return $results[$this->test_result] ?? $this->test_result;
    }

    /**
     * Get status in Arabic
     */
    public function getStatusArabicAttribute(): string
    {
        $statuses = [
            'active' => 'نشط',
            'cancelled' => 'ملغي',
            'superseded' => 'محدث',
        ];

        return $statuses[$this->status] ?? $this->status;
    }

    public function getInvestigationStatusArabicAttribute()
    {
        $statuses = [
            'not_required' => 'غير مطلوب',
            'pending' => 'في الانتظار',
            'ongoing' => 'جاري',
            'completed' => 'مكتمل',
        ];

        return $statuses[$this->investigation_status] ?? $this->investigation_status;
    }

    public function getStabilityTimeUnitArabicAttribute()
    {
        $units = [
            'days' => 'أيام',
            'weeks' => 'أسابيع',
            'months' => 'أشهر',
            'years' => 'سنوات',
        ];

        return $units[$this->stability_time_unit] ?? $this->stability_time_unit;
    }

    // Helper methods
    public function isPassed()
    {
        return $this->test_result === 'pass';
    }

    public function isFailed()
    {
        return $this->test_result === 'fail';
    }

    public function isPending()
    {
        return $this->test_result === 'pending';
    }

    public function isRetest()
    {
        return $this->is_retest;
    }

    public function isStabilityTest()
    {
        return $this->is_stability_test;
    }

    public function isOutOfSpecification()
    {
        return $this->test_result === 'out_of_specification';
    }

    public function needsInvestigation()
    {
        return $this->requires_investigation && 
               $this->investigation_status !== 'completed';
    }

    public function isWithinSpecification()
    {
        if (!$this->actual_value) {
            return $this->actual_result === $this->expected_result;
        }

        $withinMin = !$this->expected_min_value || $this->actual_value >= $this->expected_min_value;
        $withinMax = !$this->expected_max_value || $this->actual_value <= $this->expected_max_value;

        return $withinMin && $withinMax;
    }

    public function getDeviation()
    {
        if (!$this->actual_value || !$this->expected_min_value || !$this->expected_max_value) {
            return null;
        }

        $target = ($this->expected_min_value + $this->expected_max_value) / 2;
        return $this->actual_value - $target;
    }

    public function getDeviationPercentage()
    {
        $deviation = $this->getDeviation();
        if (!$deviation || !$this->expected_min_value || !$this->expected_max_value) {
            return null;
        }

        $target = ($this->expected_min_value + $this->expected_max_value) / 2;
        return round(($deviation / $target) * 100, 2);
    }

    public function isInstrumentCalibrated()
    {
        return $this->instrument_calibration_date && 
               $this->instrument_calibration_date->gte(Carbon::now()->subYear());
    }

    public function isReagentValid()
    {
        return !$this->reagent_expiry || 
               $this->reagent_expiry->gte($this->test_date);
    }

    public function hasValidConditions()
    {
        return $this->isInstrumentCalibrated() && $this->isReagentValid();
    }

    public function getTestDuration()
    {
        if (!$this->test_time) {
            return null;
        }

        // Assuming test duration is stored in test_time or calculated from start/end times
        return $this->test_time->format('H:i');
    }

    public function getRetestCount()
    {
        return $this->retests()->count();
    }

    public function hasRetests()
    {
        return $this->getRetestCount() > 0;
    }

    public function getLatestRetest()
    {
        return $this->retests()->latest('test_date')->first();
    }

    public function getStabilityConditionDescription()
    {
        if (!$this->is_stability_test) {
            return null;
        }

        $condition = $this->stability_condition;
        $timePoint = $this->stability_time_point;
        $timeUnit = $this->getStabilityTimeUnitArabicAttribute();

        return "{$condition} - {$timePoint} {$timeUnit}";
    }

    public function canBeRetested()
    {
        return $this->isFailed() && !$this->isRetest();
    }

    public function shouldBeInvestigated()
    {
        return $this->isFailed() || 
               $this->isOutOfSpecification() || 
               !$this->isWithinSpecification();
    }

    public function getQualityScore()
    {
        $score = 0;

        // Test result (60 points)
        if ($this->isPassed()) {
            $score += 60;
        } elseif ($this->test_result === 'conditional') {
            $score += 40;
        } elseif ($this->isPending()) {
            $score += 20;
        }

        // Specification compliance (20 points)
        if ($this->isWithinSpecification()) {
            $score += 20;
        }

        // Valid conditions (10 points)
        if ($this->hasValidConditions()) {
            $score += 10;
        }

        // Investigation status (10 points)
        if (!$this->needsInvestigation()) {
            $score += 10;
        } elseif ($this->investigation_status === 'completed') {
            $score += 5;
        }

        return $score;
    }

    public function getComplianceStatus()
    {
        if ($this->isFailed() || $this->isOutOfSpecification()) {
            return 'non_compliant';
        }

        if ($this->isPassed() && $this->isWithinSpecification()) {
            return 'compliant';
        }

        if ($this->isPending()) {
            return 'pending';
        }

        return 'conditional';
    }
}
