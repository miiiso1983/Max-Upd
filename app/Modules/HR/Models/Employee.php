<?php

namespace App\Modules\HR\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_TERMINATED = 'terminated';
    const STATUS_SUSPENDED = 'suspended';

    const GENDER_MALE = 'male';
    const GENDER_FEMALE = 'female';

    const MARITAL_STATUS_SINGLE = 'single';
    const MARITAL_STATUS_MARRIED = 'married';
    const MARITAL_STATUS_DIVORCED = 'divorced';
    const MARITAL_STATUS_WIDOWED = 'widowed';

    const EMPLOYMENT_TYPE_FULL_TIME = 'full_time';
    const EMPLOYMENT_TYPE_PART_TIME = 'part_time';
    const EMPLOYMENT_TYPE_CONTRACT = 'contract';
    const EMPLOYMENT_TYPE_INTERN = 'intern';

    protected $fillable = [
        'employee_id',
        'user_id',
        'first_name',
        'last_name',
        'first_name_ar',
        'last_name_ar',
        'email',
        'phone',
        'mobile',
        'national_id',
        'passport_number',
        'date_of_birth',
        'gender',
        'marital_status',
        'nationality',
        'address',
        'city',
        'governorate',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
        'department_id',
        'position_id',
        'manager_id',
        'hire_date',
        'termination_date',
        'employment_type',
        'status',
        'basic_salary',
        'hourly_rate',
        'currency',
        'bank_name',
        'bank_account',
        'iban',
        'tax_number',
        'social_security_number',
        'health_insurance_number',
        'can_be_manager',
        'notes',
        'profile_photo',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'hire_date' => 'date',
        'termination_date' => 'date',
        'basic_salary' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'can_be_manager' => 'boolean',
    ];

    protected $attributes = [
        'status' => self::STATUS_ACTIVE,
        'employment_type' => self::EMPLOYMENT_TYPE_FULL_TIME,
        'currency' => 'IQD',
        'nationality' => 'Iraqi',
    ];

    /**
     * Get the user associated with the employee
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    /**
     * Get the department
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the position
     */
    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    /**
     * Get the manager
     */
    public function manager()
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    /**
     * Get subordinates
     */
    public function subordinates()
    {
        return $this->hasMany(Employee::class, 'manager_id');
    }

    /**
     * Get attendance records
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Get leave requests
     */
    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    /**
     * Get payroll records
     */
    public function payrolls()
    {
        return $this->hasMany(Payroll::class);
    }

    /**
     * Get performance evaluations
     */
    public function evaluations()
    {
        return $this->hasMany(PerformanceEvaluation::class);
    }

    /**
     * Get employee documents
     */
    public function documents()
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    /**
     * Get full name
     */
    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    /**
     * Get full name in Arabic
     */
    public function getFullNameArAttribute()
    {
        return $this->first_name_ar . ' ' . $this->last_name_ar;
    }

    /**
     * Get age
     */
    public function getAgeAttribute()
    {
        return $this->date_of_birth ? $this->date_of_birth->age : null;
    }

    /**
     * Get years of service
     */
    public function getYearsOfServiceAttribute()
    {
        return $this->hire_date ? $this->hire_date->diffInYears(now()) : 0;
    }

    /**
     * Check if employee is active
     */
    public function isActive()
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if employee is terminated
     */
    public function isTerminated()
    {
        return $this->status === self::STATUS_TERMINATED;
    }

    /**
     * Get available statuses
     */
    public static function getStatuses()
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
            self::STATUS_TERMINATED => 'Terminated',
            self::STATUS_SUSPENDED => 'Suspended',
        ];
    }

    /**
     * Get available statuses in Arabic
     */
    public static function getStatusesAr()
    {
        return [
            self::STATUS_ACTIVE => 'نشط',
            self::STATUS_INACTIVE => 'غير نشط',
            self::STATUS_TERMINATED => 'منتهي الخدمة',
            self::STATUS_SUSPENDED => 'معلق',
        ];
    }

    /**
     * Get available genders
     */
    public static function getGenders()
    {
        return [
            self::GENDER_MALE => 'Male',
            self::GENDER_FEMALE => 'Female',
        ];
    }

    /**
     * Get available genders in Arabic
     */
    public static function getGendersAr()
    {
        return [
            self::GENDER_MALE => 'ذكر',
            self::GENDER_FEMALE => 'أنثى',
        ];
    }

    /**
     * Get marital statuses
     */
    public static function getMaritalStatuses()
    {
        return [
            self::MARITAL_STATUS_SINGLE => 'Single',
            self::MARITAL_STATUS_MARRIED => 'Married',
            self::MARITAL_STATUS_DIVORCED => 'Divorced',
            self::MARITAL_STATUS_WIDOWED => 'Widowed',
        ];
    }

    /**
     * Get marital statuses in Arabic
     */
    public static function getMaritalStatusesAr()
    {
        return [
            self::MARITAL_STATUS_SINGLE => 'أعزب',
            self::MARITAL_STATUS_MARRIED => 'متزوج',
            self::MARITAL_STATUS_DIVORCED => 'مطلق',
            self::MARITAL_STATUS_WIDOWED => 'أرمل',
        ];
    }

    /**
     * Get employment types
     */
    public static function getEmploymentTypes()
    {
        return [
            self::EMPLOYMENT_TYPE_FULL_TIME => 'Full Time',
            self::EMPLOYMENT_TYPE_PART_TIME => 'Part Time',
            self::EMPLOYMENT_TYPE_CONTRACT => 'Contract',
            self::EMPLOYMENT_TYPE_INTERN => 'Intern',
        ];
    }

    /**
     * Get employment types in Arabic
     */
    public static function getEmploymentTypesAr()
    {
        return [
            self::EMPLOYMENT_TYPE_FULL_TIME => 'دوام كامل',
            self::EMPLOYMENT_TYPE_PART_TIME => 'دوام جزئي',
            self::EMPLOYMENT_TYPE_CONTRACT => 'عقد',
            self::EMPLOYMENT_TYPE_INTERN => 'متدرب',
        ];
    }

    /**
     * Scope for active employees
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope for employees by department
     */
    public function scopeInDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    /**
     * Scope for employees by employment type
     */
    public function scopeByEmploymentType($query, $type)
    {
        return $query->where('employment_type', $type);
    }

    /**
     * Scope for managers (employees who can be managers)
     * For now, return all active employees as potential managers
     */
    public function scopeManagers($query)
    {
        // For simplicity, return all active employees
        // Later this can be refined based on can_be_manager field or position level
        return $query->active();
    }

    /**
     * Get the user who created the employee
     */
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Get the user who last updated the employee
     */
    public function updater()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    /**
     * Generate employee ID if not provided
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($employee) {
            if (empty($employee->employee_id)) {
                $year = date('Y');
                $count = static::whereYear('created_at', $year)->count() + 1;
                $employee->employee_id = "EMP-{$year}-" . str_pad($count, 4, '0', STR_PAD_LEFT);
            }
        });
    }
}
