<?php

namespace App\Modules\HR\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    use HasFactory;

    const TYPE_ANNUAL = 'annual';
    const TYPE_SICK = 'sick';
    const TYPE_MATERNITY = 'maternity';
    const TYPE_PATERNITY = 'paternity';
    const TYPE_EMERGENCY = 'emergency';
    const TYPE_UNPAID = 'unpaid';
    const TYPE_COMPENSATORY = 'compensatory';

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'employee_id',
        'type',
        'start_date',
        'end_date',
        'days_requested',
        'reason',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'notes',
        'attachment',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_at' => 'datetime',
        'days_requested' => 'integer',
    ];

    protected $attributes = [
        'status' => self::STATUS_PENDING,
    ];

    /**
     * Get the employee
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the user who approved the leave
     */
    public function approver()
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by');
    }

    /**
     * Get the user who created the leave request
     */
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Calculate number of days
     */
    public function calculateDays()
    {
        if ($this->start_date && $this->end_date) {
            return $this->start_date->diffInDays($this->end_date) + 1;
        }
        return 0;
    }

    /**
     * Check if leave request is pending
     */
    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if leave request is approved
     */
    public function isApproved()
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Check if leave request is rejected
     */
    public function isRejected()
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * Check if leave request can be cancelled
     */
    public function canBeCancelled()
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_APPROVED]) 
               && $this->start_date->isFuture();
    }

    /**
     * Approve leave request
     */
    public function approve($approvedBy, $notes = null)
    {
        $this->update([
            'status' => self::STATUS_APPROVED,
            'approved_by' => $approvedBy,
            'approved_at' => now(),
            'notes' => $notes,
        ]);
    }

    /**
     * Reject leave request
     */
    public function reject($rejectedBy, $reason)
    {
        $this->update([
            'status' => self::STATUS_REJECTED,
            'approved_by' => $rejectedBy,
            'approved_at' => now(),
            'rejection_reason' => $reason,
        ]);
    }

    /**
     * Cancel leave request
     */
    public function cancel()
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
        ]);
    }

    /**
     * Get available leave types
     */
    public static function getTypes()
    {
        return [
            self::TYPE_ANNUAL => 'Annual Leave',
            self::TYPE_SICK => 'Sick Leave',
            self::TYPE_MATERNITY => 'Maternity Leave',
            self::TYPE_PATERNITY => 'Paternity Leave',
            self::TYPE_EMERGENCY => 'Emergency Leave',
            self::TYPE_UNPAID => 'Unpaid Leave',
            self::TYPE_COMPENSATORY => 'Compensatory Leave',
        ];
    }

    /**
     * Get available leave types in Arabic
     */
    public static function getTypesAr()
    {
        return [
            self::TYPE_ANNUAL => 'إجازة سنوية',
            self::TYPE_SICK => 'إجازة مرضية',
            self::TYPE_MATERNITY => 'إجازة أمومة',
            self::TYPE_PATERNITY => 'إجازة أبوة',
            self::TYPE_EMERGENCY => 'إجازة طارئة',
            self::TYPE_UNPAID => 'إجازة بدون راتب',
            self::TYPE_COMPENSATORY => 'إجازة تعويضية',
        ];
    }

    /**
     * Get available statuses
     */
    public static function getStatuses()
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_CANCELLED => 'Cancelled',
        ];
    }

    /**
     * Get available statuses in Arabic
     */
    public static function getStatusesAr()
    {
        return [
            self::STATUS_PENDING => 'معلق',
            self::STATUS_APPROVED => 'موافق عليه',
            self::STATUS_REJECTED => 'مرفوض',
            self::STATUS_CANCELLED => 'ملغي',
        ];
    }

    /**
     * Scope for leave requests by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for leave requests by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for leave requests by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->where(function ($q) use ($startDate, $endDate) {
            $q->whereBetween('start_date', [$startDate, $endDate])
              ->orWhereBetween('end_date', [$startDate, $endDate])
              ->orWhere(function ($q2) use ($startDate, $endDate) {
                  $q2->where('start_date', '<=', $startDate)
                     ->where('end_date', '>=', $endDate);
              });
        });
    }

    /**
     * Scope for pending leave requests
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope for approved leave requests
     */
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Boot method to auto-calculate days
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($leaveRequest) {
            // Auto-calculate days if not set
            if (!$leaveRequest->days_requested) {
                $leaveRequest->days_requested = $leaveRequest->calculateDays();
            }
        });
    }
}
