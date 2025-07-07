<?php

namespace App\Modules\HR\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    const STATUS_PRESENT = 'present';
    const STATUS_ABSENT = 'absent';
    const STATUS_LATE = 'late';
    const STATUS_HALF_DAY = 'half_day';
    const STATUS_OVERTIME = 'overtime';

    protected $fillable = [
        'employee_id',
        'date',
        'check_in_time',
        'check_out_time',
        'break_start_time',
        'break_end_time',
        'status',
        'late_minutes',
        'overtime_minutes',
        'total_hours',
        'notes',
        'approved_by',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
        'break_start_time' => 'datetime',
        'break_end_time' => 'datetime',
        'late_minutes' => 'integer',
        'overtime_minutes' => 'integer',
        'total_hours' => 'decimal:2',
    ];

    /**
     * Get the employee
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the user who approved the attendance
     */
    public function approver()
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by');
    }

    /**
     * Get the user who created the attendance record
     */
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Calculate total working hours
     */
    public function calculateTotalHours()
    {
        if (!$this->check_in_time || !$this->check_out_time) {
            return 0;
        }

        $workingMinutes = $this->check_in_time->diffInMinutes($this->check_out_time);
        
        // Subtract break time if available
        if ($this->break_start_time && $this->break_end_time) {
            $breakMinutes = $this->break_start_time->diffInMinutes($this->break_end_time);
            $workingMinutes -= $breakMinutes;
        }

        return round($workingMinutes / 60, 2);
    }

    /**
     * Calculate late minutes
     */
    public function calculateLateMinutes($standardStartTime = '09:00')
    {
        if (!$this->check_in_time) {
            return 0;
        }

        $standardStart = Carbon::createFromFormat('H:i', $standardStartTime);
        $checkIn = Carbon::parse($this->check_in_time->format('H:i'));

        if ($checkIn->gt($standardStart)) {
            return $checkIn->diffInMinutes($standardStart);
        }

        return 0;
    }

    /**
     * Calculate overtime minutes
     */
    public function calculateOvertimeMinutes($standardEndTime = '17:00')
    {
        if (!$this->check_out_time) {
            return 0;
        }

        $standardEnd = Carbon::createFromFormat('H:i', $standardEndTime);
        $checkOut = Carbon::parse($this->check_out_time->format('H:i'));

        if ($checkOut->gt($standardEnd)) {
            return $checkOut->diffInMinutes($standardEnd);
        }

        return 0;
    }

    /**
     * Determine attendance status
     */
    public function determineStatus($standardStartTime = '09:00', $standardEndTime = '17:00')
    {
        if (!$this->check_in_time) {
            return self::STATUS_ABSENT;
        }

        $lateMinutes = $this->calculateLateMinutes($standardStartTime);
        $totalHours = $this->calculateTotalHours();
        $overtimeMinutes = $this->calculateOvertimeMinutes($standardEndTime);

        if ($totalHours < 4) {
            return self::STATUS_HALF_DAY;
        } elseif ($overtimeMinutes > 60) {
            return self::STATUS_OVERTIME;
        } elseif ($lateMinutes > 15) {
            return self::STATUS_LATE;
        } else {
            return self::STATUS_PRESENT;
        }
    }

    /**
     * Get formatted working hours
     */
    public function getFormattedWorkingHoursAttribute()
    {
        if ($this->total_hours) {
            $hours = floor($this->total_hours);
            $minutes = ($this->total_hours - $hours) * 60;
            return sprintf('%02d:%02d', $hours, $minutes);
        }
        return '00:00';
    }

    /**
     * Get available statuses
     */
    public static function getStatuses()
    {
        return [
            self::STATUS_PRESENT => 'Present',
            self::STATUS_ABSENT => 'Absent',
            self::STATUS_LATE => 'Late',
            self::STATUS_HALF_DAY => 'Half Day',
            self::STATUS_OVERTIME => 'Overtime',
        ];
    }

    /**
     * Get available statuses in Arabic
     */
    public static function getStatusesAr()
    {
        return [
            self::STATUS_PRESENT => 'حاضر',
            self::STATUS_ABSENT => 'غائب',
            self::STATUS_LATE => 'متأخر',
            self::STATUS_HALF_DAY => 'نصف يوم',
            self::STATUS_OVERTIME => 'وقت إضافي',
        ];
    }

    /**
     * Scope for attendance by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope for attendance by employee
     */
    public function scopeForEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    /**
     * Scope for attendance by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for late attendance
     */
    public function scopeLate($query)
    {
        return $query->where('late_minutes', '>', 0);
    }

    /**
     * Scope for overtime attendance
     */
    public function scopeOvertime($query)
    {
        return $query->where('overtime_minutes', '>', 0);
    }

    /**
     * Boot method to auto-calculate fields
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($attendance) {
            // Auto-calculate total hours
            $attendance->total_hours = $attendance->calculateTotalHours();
            
            // Auto-calculate late minutes
            $attendance->late_minutes = $attendance->calculateLateMinutes();
            
            // Auto-calculate overtime minutes
            $attendance->overtime_minutes = $attendance->calculateOvertimeMinutes();
            
            // Auto-determine status
            if (!$attendance->status) {
                $attendance->status = $attendance->determineStatus();
            }
        });
    }
}
