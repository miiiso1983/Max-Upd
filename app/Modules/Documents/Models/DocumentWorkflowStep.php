<?php

namespace App\Modules\Documents\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class DocumentWorkflowStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_workflow_id',
        'step_number',
        'step_name',
        'step_name_ar',
        'status',
        'assigned_to',
        'completed_by',
        'due_date',
        'completed_at',
        'notes',
        'notes_ar',
        'step_data',
    ];

    protected $casts = [
        'step_data' => 'array',
        'due_date' => 'datetime',
        'completed_at' => 'datetime',
        'step_number' => 'integer',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_REJECTED = 'rejected';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_REASSIGNED = 'reassigned';

    /**
     * Relationships
     */
    public function workflow()
    {
        return $this->belongsTo(DocumentWorkflow::class, 'document_workflow_id');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function completedBy()
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    /**
     * Scopes
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
                    ->whereIn('status', [self::STATUS_PENDING, self::STATUS_IN_PROGRESS]);
    }

    /**
     * Accessors
     */
    public function getStatusLabelAttribute()
    {
        $labels = [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_IN_PROGRESS => 'In Progress',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_REASSIGNED => 'Reassigned',
        ];

        return $labels[$this->status] ?? 'Unknown';
    }

    public function getStatusLabelArAttribute()
    {
        $labels = [
            self::STATUS_PENDING => 'في الانتظار',
            self::STATUS_IN_PROGRESS => 'قيد التنفيذ',
            self::STATUS_COMPLETED => 'مكتمل',
            self::STATUS_REJECTED => 'مرفوض',
            self::STATUS_CANCELLED => 'ملغي',
            self::STATUS_REASSIGNED => 'معاد التعيين',
        ];

        return $labels[$this->status] ?? 'غير معروف';
    }

    public function getIsOverdueAttribute()
    {
        return $this->due_date && $this->due_date < now() && 
               in_array($this->status, [self::STATUS_PENDING, self::STATUS_IN_PROGRESS]);
    }

    /**
     * Methods
     */
    public function complete($userId = null, $notes = null)
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_by' => $userId ?? auth()->id(),
            'completed_at' => now(),
            'notes' => $notes,
        ]);

        return $this;
    }

    public function reject($userId = null, $reason = null)
    {
        $this->update([
            'status' => self::STATUS_REJECTED,
            'completed_by' => $userId ?? auth()->id(),
            'completed_at' => now(),
            'notes' => $reason,
        ]);

        return $this;
    }
}
