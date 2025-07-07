<?php

namespace App\Modules\Documents\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class DocumentWorkflow extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'workflow_name',
        'workflow_name_ar',
        'current_step',
        'total_steps',
        'status',
        'assigned_to',
        'due_date',
        'completed_at',
        'notes',
        'notes_ar',
        'workflow_data',
        'created_by',
    ];

    protected $casts = [
        'workflow_data' => 'array',
        'due_date' => 'datetime',
        'completed_at' => 'datetime',
        'current_step' => 'integer',
        'total_steps' => 'integer',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_REJECTED = 'rejected';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Relationships
     */
    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function steps()
    {
        return $this->hasMany(DocumentWorkflowStep::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_IN_PROGRESS]);
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
                    ->whereIn('status', [self::STATUS_PENDING, self::STATUS_IN_PROGRESS]);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
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
        ];

        return $labels[$this->status] ?? 'غير معروف';
    }

    public function getProgressPercentageAttribute()
    {
        if ($this->total_steps === 0) {
            return 0;
        }

        return round(($this->current_step / $this->total_steps) * 100, 2);
    }

    public function getIsOverdueAttribute()
    {
        return $this->due_date && $this->due_date < now() && 
               in_array($this->status, [self::STATUS_PENDING, self::STATUS_IN_PROGRESS]);
    }

    /**
     * Methods
     */
    public function advance($userId = null, $notes = null)
    {
        if ($this->current_step < $this->total_steps) {
            $this->increment('current_step');
            
            if ($this->current_step >= $this->total_steps) {
                $this->update([
                    'status' => self::STATUS_COMPLETED,
                    'completed_at' => now(),
                ]);
            } else {
                $this->update(['status' => self::STATUS_IN_PROGRESS]);
            }
        }

        // Log step completion
        $this->steps()->create([
            'step_number' => $this->current_step,
            'step_name' => "Step {$this->current_step}",
            'status' => 'completed',
            'completed_by' => $userId ?? auth()->id(),
            'completed_at' => now(),
            'notes' => $notes,
        ]);

        return $this;
    }

    public function reject($userId = null, $reason = null)
    {
        $this->update(['status' => self::STATUS_REJECTED]);

        // Log rejection
        $this->steps()->create([
            'step_number' => $this->current_step,
            'step_name' => "Rejection",
            'status' => 'rejected',
            'completed_by' => $userId ?? auth()->id(),
            'completed_at' => now(),
            'notes' => $reason,
        ]);

        return $this;
    }

    public function cancel($userId = null, $reason = null)
    {
        $this->update(['status' => self::STATUS_CANCELLED]);

        // Log cancellation
        $this->steps()->create([
            'step_number' => $this->current_step,
            'step_name' => "Cancellation",
            'status' => 'cancelled',
            'completed_by' => $userId ?? auth()->id(),
            'completed_at' => now(),
            'notes' => $reason,
        ]);

        return $this;
    }

    public function reassign($userId, $assignedBy = null)
    {
        $this->update(['assigned_to' => $userId]);

        // Log reassignment
        $this->steps()->create([
            'step_number' => $this->current_step,
            'step_name' => "Reassignment",
            'status' => 'reassigned',
            'completed_by' => $assignedBy ?? auth()->id(),
            'completed_at' => now(),
            'notes' => "Reassigned to user {$userId}",
        ]);

        return $this;
    }
}
