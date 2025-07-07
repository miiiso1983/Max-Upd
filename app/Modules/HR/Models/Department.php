<?php

namespace App\Modules\HR\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use HasFactory, SoftDeletes;

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'name',
        'name_ar',
        'code',
        'description',
        'description_ar',
        'parent_id',
        'manager_id',
        'status',
        'budget',
        'location',
        'phone',
        'email',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'budget' => 'decimal:2',
    ];

    protected $attributes = [
        'status' => self::STATUS_ACTIVE,
    ];

    /**
     * Get the parent department
     */
    public function parent()
    {
        return $this->belongsTo(Department::class, 'parent_id');
    }

    /**
     * Get child departments
     */
    public function children()
    {
        return $this->hasMany(Department::class, 'parent_id');
    }

    /**
     * Get all descendants
     */
    public function descendants()
    {
        return $this->children()->with('descendants');
    }

    /**
     * Get the department manager
     */
    public function manager()
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    /**
     * Get employees in this department
     */
    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    /**
     * Get positions in this department
     */
    public function positions()
    {
        return $this->hasMany(Position::class);
    }

    /**
     * Get active employees count
     */
    public function getActiveEmployeesCountAttribute()
    {
        return $this->employees()->active()->count();
    }

    /**
     * Get total employees count (including sub-departments)
     */
    public function getTotalEmployeesCount()
    {
        $count = $this->employees()->count();
        foreach ($this->children as $child) {
            $count += $child->getTotalEmployeesCount();
        }
        return $count;
    }

    /**
     * Get department hierarchy path
     */
    public function getHierarchyPathAttribute()
    {
        $path = [$this->name];
        $parent = $this->parent;
        
        while ($parent) {
            array_unshift($path, $parent->name);
            $parent = $parent->parent;
        }
        
        return implode(' > ', $path);
    }

    /**
     * Get department hierarchy path in Arabic
     */
    public function getHierarchyPathArAttribute()
    {
        $path = [$this->name_ar ?: $this->name];
        $parent = $this->parent;
        
        while ($parent) {
            array_unshift($path, $parent->name_ar ?: $parent->name);
            $parent = $parent->parent;
        }
        
        return implode(' > ', $path);
    }

    /**
     * Check if department is active
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
     * Scope for active departments
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope for root departments (no parent)
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Get the user who created the department
     */
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Get the user who last updated the department
     */
    public function updater()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    /**
     * Generate department code if not provided
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($department) {
            if (empty($department->code)) {
                $prefix = strtoupper(substr($department->name, 0, 3));
                $department->code = $prefix . '-' . str_pad(static::count() + 1, 3, '0', STR_PAD_LEFT);
            }
        });
    }
}
