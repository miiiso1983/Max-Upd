<?php

namespace App\Modules\HR\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Position extends Model
{
    use HasFactory, SoftDeletes;

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    const LEVEL_ENTRY = 'entry';
    const LEVEL_JUNIOR = 'junior';
    const LEVEL_SENIOR = 'senior';
    const LEVEL_LEAD = 'lead';
    const LEVEL_MANAGER = 'manager';
    const LEVEL_DIRECTOR = 'director';
    const LEVEL_EXECUTIVE = 'executive';

    protected $fillable = [
        'title',
        'title_ar',
        'code',
        'department_id',
        'level',
        'is_management',
        'description',
        'description_ar',
        'requirements',
        'requirements_ar',
        'responsibilities',
        'responsibilities_ar',
        'min_salary',
        'max_salary',
        'currency',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'min_salary' => 'decimal:2',
        'max_salary' => 'decimal:2',
        'is_management' => 'boolean',
    ];

    protected $attributes = [
        'status' => self::STATUS_ACTIVE,
        'currency' => 'IQD',
        'level' => self::LEVEL_ENTRY,
    ];

    /**
     * Get the department
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get employees in this position
     */
    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    /**
     * Get active employees count
     */
    public function getActiveEmployeesCountAttribute()
    {
        return $this->employees()->active()->count();
    }

    /**
     * Get salary range
     */
    public function getSalaryRangeAttribute()
    {
        if ($this->min_salary && $this->max_salary) {
            return number_format($this->min_salary) . ' - ' . number_format($this->max_salary) . ' ' . $this->currency;
        }
        return null;
    }

    /**
     * Check if position is active
     */
    public function isActive()
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Get available statuses
     */
    public static function getStatuses()
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
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
        ];
    }

    /**
     * Get available levels
     */
    public static function getLevels()
    {
        return [
            self::LEVEL_ENTRY => 'Entry Level',
            self::LEVEL_JUNIOR => 'Junior',
            self::LEVEL_SENIOR => 'Senior',
            self::LEVEL_LEAD => 'Lead',
            self::LEVEL_MANAGER => 'Manager',
            self::LEVEL_DIRECTOR => 'Director',
            self::LEVEL_EXECUTIVE => 'Executive',
        ];
    }

    /**
     * Get available levels in Arabic
     */
    public static function getLevelsAr()
    {
        return [
            self::LEVEL_ENTRY => 'مستوى مبتدئ',
            self::LEVEL_JUNIOR => 'مبتدئ',
            self::LEVEL_SENIOR => 'كبير',
            self::LEVEL_LEAD => 'قائد فريق',
            self::LEVEL_MANAGER => 'مدير',
            self::LEVEL_DIRECTOR => 'مدير عام',
            self::LEVEL_EXECUTIVE => 'تنفيذي',
        ];
    }

    /**
     * Scope for active positions
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope for positions by department
     */
    public function scopeInDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    /**
     * Scope for positions by level
     */
    public function scopeByLevel($query, $level)
    {
        return $query->where('level', $level);
    }

    /**
     * Get the user who created the position
     */
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Get the user who last updated the position
     */
    public function updater()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    /**
     * Generate position code if not provided
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($position) {
            if (empty($position->code)) {
                $prefix = strtoupper(substr($position->title, 0, 3));
                $position->code = $prefix . '-' . str_pad(static::count() + 1, 3, '0', STR_PAD_LEFT);
            }
        });
    }
}
