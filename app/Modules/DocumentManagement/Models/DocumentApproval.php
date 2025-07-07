<?php

namespace App\Modules\DocumentManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class DocumentApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'workflow_id',
        'step_number',
        'approver_id',
        'approver_type',
        'approver_role',
        'status',
        'action',
        'comments',
        'comments_ar',
        'approved_at',
        'rejected_at',
        'due_date',
        'escalated_at',
        'escalated_to',
        'escalation_reason',
        'delegation_from',
        'delegation_to',
        'delegation_reason',
        'priority',
        'notification_sent',
        'reminder_count',
        'last_reminder_at',
        'metadata',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'due_date' => 'datetime',
        'escalated_at' => 'datetime',
        'notification_sent' => 'boolean',
        'reminder_count' => 'integer',
        'last_reminder_at' => 'datetime',
        'metadata' => 'array',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_DELEGATED = 'delegated';
    const STATUS_ESCALATED = 'escalated';
    const STATUS_SKIPPED = 'skipped';
    const STATUS_CANCELLED = 'cancelled';

    // Action constants
    const ACTION_APPROVE = 'approve';
    const ACTION_REJECT = 'reject';
    const ACTION_DELEGATE = 'delegate';
    const ACTION_ESCALATE = 'escalate';
    const ACTION_REQUEST_CHANGES = 'request_changes';
    const ACTION_SKIP = 'skip';

    // Approver type constants
    const APPROVER_USER = 'user';
    const APPROVER_ROLE = 'role';
    const APPROVER_DEPARTMENT = 'department';
    const APPROVER_GROUP = 'group';

    // Priority constants
    const PRIORITY_LOW = 'low';
    const PRIORITY_NORMAL = 'normal';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';
    const PRIORITY_CRITICAL = 'critical';

    /**
     * Relationships
     */
    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function workflow()
    {
        return $this->belongsTo(ApprovalWorkflow::class, 'workflow_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function escalatedTo()
    {
        return $this->belongsTo(User::class, 'escalated_to');
    }

    public function delegationFrom()
    {
        return $this->belongsTo(User::class, 'delegation_from');
    }

    public function delegationTo()
    {
        return $this->belongsTo(User::class, 'delegation_to');
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', self::STATUS_PENDING)
                    ->whereNotNull('due_date')
                    ->where('due_date', '<', now());
    }

    public function scopeByApprover($query, $approverId)
    {
        return $query->where('approver_id', $approverId);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeCurrentStep($query)
    {
        return $query->where('status', self::STATUS_PENDING)
                    ->orderBy('step_number');
    }

    /**
     * Accessors
     */
    public function getStatusLabelAttribute()
    {
        $labels = [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_DELEGATED => 'Delegated',
            self::STATUS_ESCALATED => 'Escalated',
            self::STATUS_SKIPPED => 'Skipped',
            self::STATUS_CANCELLED => 'Cancelled',
        ];

        return $labels[$this->status] ?? 'Unknown';
    }

    public function getStatusLabelArAttribute()
    {
        $labels = [
            self::STATUS_PENDING => 'في الانتظار',
            self::STATUS_APPROVED => 'معتمد',
            self::STATUS_REJECTED => 'مرفوض',
            self::STATUS_DELEGATED => 'مفوض',
            self::STATUS_ESCALATED => 'مصعد',
            self::STATUS_SKIPPED => 'متجاوز',
            self::STATUS_CANCELLED => 'ملغي',
        ];

        return $labels[$this->status] ?? 'غير معروف';
    }

    public function getActionLabelAttribute()
    {
        $labels = [
            self::ACTION_APPROVE => 'Approve',
            self::ACTION_REJECT => 'Reject',
            self::ACTION_DELEGATE => 'Delegate',
            self::ACTION_ESCALATE => 'Escalate',
            self::ACTION_REQUEST_CHANGES => 'Request Changes',
            self::ACTION_SKIP => 'Skip',
        ];

        return $labels[$this->action] ?? 'Unknown';
    }

    public function getPriorityLabelAttribute()
    {
        $labels = [
            self::PRIORITY_LOW => 'Low',
            self::PRIORITY_NORMAL => 'Normal',
            self::PRIORITY_HIGH => 'High',
            self::PRIORITY_URGENT => 'Urgent',
            self::PRIORITY_CRITICAL => 'Critical',
        ];

        return $labels[$this->priority] ?? 'Normal';
    }

    public function getPriorityLabelArAttribute()
    {
        $labels = [
            self::PRIORITY_LOW => 'منخفض',
            self::PRIORITY_NORMAL => 'عادي',
            self::PRIORITY_HIGH => 'عالي',
            self::PRIORITY_URGENT => 'عاجل',
            self::PRIORITY_CRITICAL => 'حرج',
        ];

        return $labels[$this->priority] ?? 'عادي';
    }

    public function getIsOverdueAttribute()
    {
        return $this->status === self::STATUS_PENDING &&
               $this->due_date &&
               $this->due_date->isPast();
    }

    public function getDaysOverdueAttribute()
    {
        if (!$this->is_overdue) {
            return 0;
        }

        return $this->due_date->diffInDays(now());
    }

    public function getDaysRemainingAttribute()
    {
        if ($this->status !== self::STATUS_PENDING || !$this->due_date) {
            return null;
        }

        return $this->due_date->isFuture() ? now()->diffInDays($this->due_date) : 0;
    }

    /**
     * Methods
     */
    public function approve($comments = null, $approverId = null)
    {
        $this->update([
            'status' => self::STATUS_APPROVED,
            'action' => self::ACTION_APPROVE,
            'comments' => $comments,
            'approved_at' => now(),
            'approver_id' => $approverId ?: $this->approver_id,
        ]);

        // Check if this was the final approval step
        $this->checkWorkflowCompletion();

        return $this;
    }

    public function reject($comments = null, $approverId = null)
    {
        $this->update([
            'status' => self::STATUS_REJECTED,
            'action' => self::ACTION_REJECT,
            'comments' => $comments,
            'rejected_at' => now(),
            'approver_id' => $approverId ?: $this->approver_id,
        ]);

        // Reject the entire workflow
        $this->rejectWorkflow();

        return $this;
    }

    public function delegate($delegateToId, $reason = null, $delegatedBy = null)
    {
        $this->update([
            'status' => self::STATUS_DELEGATED,
            'action' => self::ACTION_DELEGATE,
            'delegation_from' => $delegatedBy ?: $this->approver_id,
            'delegation_to' => $delegateToId,
            'delegation_reason' => $reason,
            'approver_id' => $delegateToId,
        ]);

        // Send notification to the new approver
        $this->sendDelegationNotification();

        return $this;
    }

    public function escalate($escalateToId, $reason = null)
    {
        $this->update([
            'status' => self::STATUS_ESCALATED,
            'action' => self::ACTION_ESCALATE,
            'escalated_at' => now(),
            'escalated_to' => $escalateToId,
            'escalation_reason' => $reason,
            'approver_id' => $escalateToId,
        ]);

        // Send escalation notification
        $this->sendEscalationNotification();

        return $this;
    }

    public function skip($reason = null)
    {
        $this->update([
            'status' => self::STATUS_SKIPPED,
            'action' => self::ACTION_SKIP,
            'comments' => $reason,
        ]);

        // Move to next approval step
        $this->moveToNextStep();

        return $this;
    }

    public function requestChanges($comments)
    {
        $this->update([
            'action' => self::ACTION_REQUEST_CHANGES,
            'comments' => $comments,
        ]);

        // Send document back to creator for changes
        $this->document->update(['status' => Document::STATUS_UNDER_REVIEW]);

        return $this;
    }

    private function checkWorkflowCompletion()
    {
        $workflow = $this->workflow;
        if (!$workflow) {
            return;
        }

        // Check if all required approvals are complete
        $pendingApprovals = DocumentApproval::where('document_id', $this->document_id)
                                          ->where('workflow_id', $this->workflow_id)
                                          ->where('status', self::STATUS_PENDING)
                                          ->count();

        if ($pendingApprovals === 0) {
            // All approvals complete - approve the document
            $this->document->approve();
        } else {
            // Move to next approval step
            $this->moveToNextStep();
        }
    }

    private function rejectWorkflow()
    {
        // Cancel all pending approvals in this workflow
        DocumentApproval::where('document_id', $this->document_id)
                       ->where('workflow_id', $this->workflow_id)
                       ->where('status', self::STATUS_PENDING)
                       ->update(['status' => self::STATUS_CANCELLED]);

        // Reject the document
        $this->document->reject($this->comments);
    }

    private function moveToNextStep()
    {
        $nextApproval = DocumentApproval::where('document_id', $this->document_id)
                                       ->where('workflow_id', $this->workflow_id)
                                       ->where('step_number', '>', $this->step_number)
                                       ->where('status', self::STATUS_PENDING)
                                       ->orderBy('step_number')
                                       ->first();

        if ($nextApproval) {
            $nextApproval->sendNotification();
        }
    }

    public function sendNotification()
    {
        // Send notification to approver
        // This would typically send an email or in-app notification
        $this->update([
            'notification_sent' => true,
            'last_reminder_at' => now(),
        ]);

        return true; // Placeholder
    }

    public function sendReminder()
    {
        if ($this->status !== self::STATUS_PENDING) {
            return false;
        }

        // Send reminder notification
        $this->increment('reminder_count');
        $this->update(['last_reminder_at' => now()]);

        return true; // Placeholder
    }

    public function sendDelegationNotification()
    {
        // Send notification about delegation
        return true; // Placeholder
    }

    public function sendEscalationNotification()
    {
        // Send notification about escalation
        return true; // Placeholder
    }

    public static function createWorkflowApprovals($documentId, $workflowId, $approvers)
    {
        $approvals = [];
        
        foreach ($approvers as $index => $approver) {
            $approval = static::create([
                'document_id' => $documentId,
                'workflow_id' => $workflowId,
                'step_number' => $index + 1,
                'approver_id' => $approver['user_id'],
                'approver_type' => $approver['type'] ?? self::APPROVER_USER,
                'approver_role' => $approver['role'] ?? null,
                'status' => self::STATUS_PENDING,
                'due_date' => $approver['due_date'] ?? now()->addDays(7),
                'priority' => $approver['priority'] ?? self::PRIORITY_NORMAL,
                'metadata' => $approver['metadata'] ?? [],
            ]);

            $approvals[] = $approval;
        }

        // Send notification to first approver
        if (!empty($approvals)) {
            $approvals[0]->sendNotification();
        }

        return $approvals;
    }

    public function getApprovalHistory()
    {
        return DocumentApproval::where('document_id', $this->document_id)
                              ->where('workflow_id', $this->workflow_id)
                              ->with(['approver', 'delegationFrom', 'delegationTo', 'escalatedTo'])
                              ->orderBy('step_number')
                              ->get();
    }

    public function canApprove($userId)
    {
        return $this->status === self::STATUS_PENDING &&
               $this->approver_id === $userId;
    }

    public function canDelegate($userId)
    {
        return $this->status === self::STATUS_PENDING &&
               $this->approver_id === $userId;
    }

    public function canEscalate($userId)
    {
        return $this->status === self::STATUS_PENDING &&
               ($this->approver_id === $userId || $this->is_overdue);
    }

    public function getApprovalUrl()
    {
        return route('documents.approve', [
            'document' => $this->document_id,
            'approval' => $this->id,
            'token' => $this->generateApprovalToken(),
        ]);
    }

    private function generateApprovalToken()
    {
        return hash('sha256', $this->id . $this->document_id . $this->approver_id . config('app.key'));
    }

    public function validateApprovalToken($token)
    {
        return hash_equals($this->generateApprovalToken(), $token);
    }
}
