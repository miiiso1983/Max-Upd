<?php

namespace App\Modules\RegulatoryAffairs\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class PharmaceuticalInspection extends Model
{
    use HasFactory;

    protected $fillable = [
        'inspection_number',
        'inspection_type',
        'inspected_entity_type',
        'pharmaceutical_company_id',
        'pharmaceutical_product_id',
        'pharmaceutical_batch_id',
        'regulatory_authority',
        'inspection_team_lead',
        'inspection_team_members',
        'inspection_date',
        'inspection_start_date',
        'inspection_end_date',
        'inspection_start_time',
        'inspection_end_time',
        'inspection_scope',
        'inspection_objectives',
        'areas_inspected',
        'systems_inspected',
        'inspection_status',
        'inspection_result',
        'total_observations',
        'critical_observations',
        'major_observations',
        'minor_observations',
        'key_findings',
        'observations',
        'non_conformities',
        'corrective_actions_required',
        'corrective_action_deadline',
        'corrective_action_status',
        'company_response',
        'company_response_date',
        'follow_up_required',
        'follow_up_date',
        'follow_up_status',
        'regulatory_action',
        'regulatory_action_type',
        'fine_amount',
        'public_disclosure',
        'public_disclosure_date',
        'inspection_report_number',
        'report_issue_date',
        'documents',
        'notes',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'inspection_date' => 'date',
        'inspection_start_date' => 'date',
        'inspection_end_date' => 'date',
        'inspection_start_time' => 'datetime',
        'inspection_end_time' => 'datetime',
        'corrective_action_deadline' => 'date',
        'company_response_date' => 'date',
        'follow_up_date' => 'date',
        'public_disclosure_date' => 'date',
        'report_issue_date' => 'date',
        'inspection_team_members' => 'array',
        'areas_inspected' => 'array',
        'systems_inspected' => 'array',
        'observations' => 'array',
        'non_conformities' => 'array',
        'corrective_actions_required' => 'array',
        'documents' => 'array',
        'fine_amount' => 'decimal:2',
        'follow_up_required' => 'boolean',
        'public_disclosure' => 'boolean',
    ];

    protected $dates = [
        'inspection_date',
        'inspection_start_date',
        'inspection_end_date',
        'inspection_start_time',
        'inspection_end_time',
        'corrective_action_deadline',
        'company_response_date',
        'follow_up_date',
        'public_disclosure_date',
        'report_issue_date',
    ];

    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(PharmaceuticalCompany::class, 'pharmaceutical_company_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(PharmaceuticalProduct::class, 'pharmaceutical_product_id');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(PharmaceuticalBatch::class, 'pharmaceutical_batch_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Get the documents for this inspection
     */
    public function documents()
    {
        return $this->hasMany(RegulatoryDocument::class, 'entity_id')
                    ->where('entity_type', 'inspection');
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

    public function scopeCompleted($query)
    {
        return $query->where('inspection_status', 'completed');
    }

    public function scopeScheduled($query)
    {
        return $query->where('inspection_status', 'scheduled');
    }

    public function scopeInProgress($query)
    {
        return $query->where('inspection_status', 'in_progress');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('inspection_type', $type);
    }

    public function scopeByResult($query, $result)
    {
        return $query->where('inspection_result', $result);
    }

    public function scopeByAuthority($query, $authority)
    {
        return $query->where('regulatory_authority', $authority);
    }

    public function scopeRequiringFollowUp($query)
    {
        return $query->where('follow_up_required', true)
                    ->where('follow_up_status', '!=', 'completed');
    }

    public function scopeOverdueCorrectiveActions($query)
    {
        return $query->where('corrective_action_deadline', '<', Carbon::now())
                    ->where('corrective_action_status', '!=', 'completed');
    }

    // Accessors
    public function getInspectionTypeArabicAttribute()
    {
        $types = [
            'routine' => 'روتيني',
            'for_cause' => 'لسبب محدد',
            'pre_approval' => 'ما قبل الموافقة',
            'surveillance' => 'مراقبة',
            'follow_up' => 'متابعة',
        ];

        return $types[$this->inspection_type] ?? $this->inspection_type;
    }

    public function getInspectionStatusArabicAttribute()
    {
        $statuses = [
            'scheduled' => 'مجدول',
            'in_progress' => 'قيد التنفيذ',
            'completed' => 'مكتمل',
            'cancelled' => 'ملغي',
            'postponed' => 'مؤجل',
        ];

        return $statuses[$this->inspection_status] ?? $this->inspection_status;
    }

    public function getInspectionResultArabicAttribute()
    {
        $results = [
            'satisfactory' => 'مرضي',
            'minor_deficiencies' => 'نواقص طفيفة',
            'major_deficiencies' => 'نواقص رئيسية',
            'critical_deficiencies' => 'نواقص حرجة',
            'non_compliant' => 'غير متوافق',
        ];

        return $results[$this->inspection_result] ?? $this->inspection_result;
    }

    public function getCorrectiveActionStatusArabicAttribute()
    {
        $statuses = [
            'not_required' => 'غير مطلوب',
            'pending' => 'في الانتظار',
            'in_progress' => 'قيد التنفيذ',
            'completed' => 'مكتمل',
            'overdue' => 'متأخر',
        ];

        return $statuses[$this->corrective_action_status] ?? $this->corrective_action_status;
    }

    public function getFollowUpStatusArabicAttribute()
    {
        $statuses = [
            'not_required' => 'غير مطلوب',
            'scheduled' => 'مجدول',
            'completed' => 'مكتمل',
        ];

        return $statuses[$this->follow_up_status] ?? $this->follow_up_status;
    }

    public function getRegulatoryActionTypeArabicAttribute()
    {
        $types = [
            'none' => 'لا يوجد',
            'warning_letter' => 'رسالة تحذير',
            'suspension' => 'إيقاف',
            'revocation' => 'إلغاء',
            'fine' => 'غرامة',
            'prosecution' => 'مقاضاة',
        ];

        return $types[$this->regulatory_action_type] ?? $this->regulatory_action_type;
    }

    // Helper methods
    public function isCompleted()
    {
        return $this->inspection_status === 'completed';
    }

    public function isScheduled()
    {
        return $this->inspection_status === 'scheduled';
    }

    public function isInProgress()
    {
        return $this->inspection_status === 'in_progress';
    }

    public function isSatisfactory()
    {
        return $this->inspection_result === 'satisfactory';
    }

    public function hasDeficiencies()
    {
        return in_array($this->inspection_result, [
            'minor_deficiencies',
            'major_deficiencies',
            'critical_deficiencies',
            'non_compliant'
        ]);
    }

    public function hasCriticalDeficiencies()
    {
        return $this->inspection_result === 'critical_deficiencies' || 
               $this->critical_observations > 0;
    }

    public function requiresCorrectiveAction()
    {
        return $this->corrective_action_status !== 'not_required' &&
               $this->corrective_action_status !== 'completed';
    }

    public function isCorrectiveActionOverdue()
    {
        return $this->corrective_action_deadline &&
               $this->corrective_action_deadline->lt(Carbon::now()) &&
               $this->corrective_action_status !== 'completed';
    }

    public function requiresFollowUp()
    {
        return $this->follow_up_required && 
               $this->follow_up_status !== 'completed';
    }

    public function isFollowUpOverdue()
    {
        return $this->follow_up_date &&
               $this->follow_up_date->lt(Carbon::now()) &&
               $this->follow_up_status !== 'completed';
    }

    public function hasRegulatoryAction()
    {
        return $this->regulatory_action_type && 
               $this->regulatory_action_type !== 'none';
    }

    public function getInspectionDuration()
    {
        if (!$this->inspection_start_date || !$this->inspection_end_date) {
            return null;
        }

        return $this->inspection_start_date->diffInDays($this->inspection_end_date) + 1;
    }

    public function getDaysUntilCorrectiveActionDeadline()
    {
        if (!$this->corrective_action_deadline) {
            return null;
        }

        return Carbon::now()->diffInDays($this->corrective_action_deadline, false);
    }

    public function getDaysUntilFollowUp()
    {
        if (!$this->follow_up_date) {
            return null;
        }

        return Carbon::now()->diffInDays($this->follow_up_date, false);
    }

    public function getObservationsSummary()
    {
        return [
            'total' => $this->total_observations,
            'critical' => $this->critical_observations,
            'major' => $this->major_observations,
            'minor' => $this->minor_observations,
        ];
    }

    public function getDeficiencyLevel()
    {
        if ($this->critical_observations > 0) {
            return 'critical';
        } elseif ($this->major_observations > 0) {
            return 'major';
        } elseif ($this->minor_observations > 0) {
            return 'minor';
        }

        return 'none';
    }

    public function getRiskScore()
    {
        $score = 0;

        // Base score from inspection result
        $resultScores = [
            'satisfactory' => 0,
            'minor_deficiencies' => 20,
            'major_deficiencies' => 50,
            'critical_deficiencies' => 80,
            'non_compliant' => 100,
        ];

        $score += $resultScores[$this->inspection_result] ?? 0;

        // Add points for observations
        $score += $this->critical_observations * 10;
        $score += $this->major_observations * 5;
        $score += $this->minor_observations * 1;

        // Add points for overdue actions
        if ($this->isCorrectiveActionOverdue()) {
            $score += 30;
        }

        if ($this->isFollowUpOverdue()) {
            $score += 20;
        }

        // Add points for regulatory action
        if ($this->hasRegulatoryAction()) {
            $actionScores = [
                'warning_letter' => 10,
                'suspension' => 50,
                'revocation' => 80,
                'fine' => 30,
                'prosecution' => 100,
            ];

            $score += $actionScores[$this->regulatory_action_type] ?? 0;
        }

        return min($score, 100); // Cap at 100
    }

    public function getComplianceLevel()
    {
        $riskScore = $this->getRiskScore();

        if ($riskScore >= 80) {
            return 'high_risk';
        } elseif ($riskScore >= 50) {
            return 'medium_risk';
        } elseif ($riskScore >= 20) {
            return 'low_risk';
        }

        return 'compliant';
    }

    public function getInspectedEntityName()
    {
        switch ($this->inspected_entity_type) {
            case 'company':
                return $this->company?->display_name;
            case 'product':
                return $this->product?->display_trade_name;
            case 'batch':
                return $this->batch?->batch_number;
            default:
                return null;
        }
    }

    public function canBeCompleted()
    {
        return $this->inspection_status === 'in_progress';
    }

    public function canBeStarted()
    {
        return $this->inspection_status === 'scheduled' &&
               $this->inspection_date->lte(Carbon::now());
    }
}
