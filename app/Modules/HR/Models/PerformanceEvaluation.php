<?php

namespace App\Modules\HR\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerformanceEvaluation extends Model
{
    use HasFactory;

    const STATUS_DRAFT = 'draft';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_REVIEWED = 'reviewed';
    const STATUS_APPROVED = 'approved';

    const PERIOD_QUARTERLY = 'quarterly';
    const PERIOD_SEMI_ANNUAL = 'semi_annual';
    const PERIOD_ANNUAL = 'annual';

    protected $fillable = [
        'employee_id',
        'evaluator_id',
        'period_type',
        'evaluation_period_start',
        'evaluation_period_end',
        'overall_rating',
        'goals_achievement',
        'technical_skills',
        'communication_skills',
        'teamwork',
        'leadership',
        'initiative',
        'punctuality',
        'quality_of_work',
        'productivity',
        'strengths',
        'areas_for_improvement',
        'goals_for_next_period',
        'training_recommendations',
        'evaluator_comments',
        'employee_comments',
        'hr_comments',
        'status',
        'submitted_at',
        'reviewed_at',
        'approved_at',
        'approved_by',
        'created_by',
    ];

    protected $casts = [
        'evaluation_period_start' => 'date',
        'evaluation_period_end' => 'date',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'approved_at' => 'datetime',
        'overall_rating' => 'decimal:1',
        'goals_achievement' => 'decimal:1',
        'technical_skills' => 'decimal:1',
        'communication_skills' => 'decimal:1',
        'teamwork' => 'decimal:1',
        'leadership' => 'decimal:1',
        'initiative' => 'decimal:1',
        'punctuality' => 'decimal:1',
        'quality_of_work' => 'decimal:1',
        'productivity' => 'decimal:1',
    ];

    protected $attributes = [
        'status' => self::STATUS_DRAFT,
        'period_type' => self::PERIOD_ANNUAL,
    ];

    /**
     * Get the employee being evaluated
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the evaluator
     */
    public function evaluator()
    {
        return $this->belongsTo(Employee::class, 'evaluator_id');
    }

    /**
     * Get the user who approved the evaluation
     */
    public function approver()
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by');
    }

    /**
     * Get the user who created the evaluation
     */
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Calculate overall rating based on individual scores
     */
    public function calculateOverallRating()
    {
        $scores = [
            $this->goals_achievement,
            $this->technical_skills,
            $this->communication_skills,
            $this->teamwork,
            $this->leadership,
            $this->initiative,
            $this->punctuality,
            $this->quality_of_work,
            $this->productivity,
        ];

        $validScores = array_filter($scores, function($score) {
            return $score !== null && $score > 0;
        });

        if (empty($validScores)) {
            return 0;
        }

        return round(array_sum($validScores) / count($validScores), 1);
    }

    /**
     * Get performance level based on rating
     */
    public function getPerformanceLevelAttribute()
    {
        $rating = $this->overall_rating;
        
        if ($rating >= 4.5) {
            return 'Exceptional';
        } elseif ($rating >= 3.5) {
            return 'Exceeds Expectations';
        } elseif ($rating >= 2.5) {
            return 'Meets Expectations';
        } elseif ($rating >= 1.5) {
            return 'Below Expectations';
        } else {
            return 'Unsatisfactory';
        }
    }

    /**
     * Get performance level in Arabic
     */
    public function getPerformanceLevelArAttribute()
    {
        $rating = $this->overall_rating;
        
        if ($rating >= 4.5) {
            return 'استثنائي';
        } elseif ($rating >= 3.5) {
            return 'يتجاوز التوقعات';
        } elseif ($rating >= 2.5) {
            return 'يلبي التوقعات';
        } elseif ($rating >= 1.5) {
            return 'أقل من التوقعات';
        } else {
            return 'غير مرضي';
        }
    }

    /**
     * Submit evaluation
     */
    public function submit()
    {
        $this->update([
            'status' => self::STATUS_SUBMITTED,
            'submitted_at' => now(),
        ]);
    }

    /**
     * Review evaluation
     */
    public function review()
    {
        $this->update([
            'status' => self::STATUS_REVIEWED,
            'reviewed_at' => now(),
        ]);
    }

    /**
     * Approve evaluation
     */
    public function approve($approvedBy)
    {
        $this->update([
            'status' => self::STATUS_APPROVED,
            'approved_by' => $approvedBy,
            'approved_at' => now(),
        ]);
    }

    /**
     * Check if evaluation can be edited
     */
    public function canBeEdited()
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_SUBMITTED]);
    }

    /**
     * Get available statuses
     */
    public static function getStatuses()
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_SUBMITTED => 'Submitted',
            self::STATUS_REVIEWED => 'Reviewed',
            self::STATUS_APPROVED => 'Approved',
        ];
    }

    /**
     * Get available statuses in Arabic
     */
    public static function getStatusesAr()
    {
        return [
            self::STATUS_DRAFT => 'مسودة',
            self::STATUS_SUBMITTED => 'مقدم',
            self::STATUS_REVIEWED => 'تمت المراجعة',
            self::STATUS_APPROVED => 'موافق عليه',
        ];
    }

    /**
     * Get period types
     */
    public static function getPeriodTypes()
    {
        return [
            self::PERIOD_QUARTERLY => 'Quarterly',
            self::PERIOD_SEMI_ANNUAL => 'Semi-Annual',
            self::PERIOD_ANNUAL => 'Annual',
        ];
    }

    /**
     * Get period types in Arabic
     */
    public static function getPeriodTypesAr()
    {
        return [
            self::PERIOD_QUARTERLY => 'ربع سنوي',
            self::PERIOD_SEMI_ANNUAL => 'نصف سنوي',
            self::PERIOD_ANNUAL => 'سنوي',
        ];
    }

    /**
     * Get rating scale
     */
    public static function getRatingScale()
    {
        return [
            1 => 'Poor',
            2 => 'Below Average',
            3 => 'Average',
            4 => 'Good',
            5 => 'Excellent',
        ];
    }

    /**
     * Get rating scale in Arabic
     */
    public static function getRatingScaleAr()
    {
        return [
            1 => 'ضعيف',
            2 => 'أقل من المتوسط',
            3 => 'متوسط',
            4 => 'جيد',
            5 => 'ممتاز',
        ];
    }

    /**
     * Scope for evaluations by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for evaluations by period
     */
    public function scopeByPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('evaluation_period_start', [$startDate, $endDate]);
    }

    /**
     * Scope for pending evaluations
     */
    public function scopePending($query)
    {
        return $query->whereIn('status', [self::STATUS_DRAFT, self::STATUS_SUBMITTED]);
    }

    /**
     * Boot method to auto-calculate overall rating
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($evaluation) {
            // Auto-calculate overall rating if individual scores are set
            if ($evaluation->goals_achievement || $evaluation->technical_skills) {
                $evaluation->overall_rating = $evaluation->calculateOverallRating();
            }
        });
    }
}
