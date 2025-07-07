<?php

namespace App\Modules\BusinessIntelligence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class Dashboard extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'name_ar',
        'description',
        'description_ar',
        'type',
        'layout',
        'widgets',
        'filters',
        'permissions',
        'is_public',
        'is_default',
        'is_active',
        'refresh_interval',
        'auto_refresh',
        'theme',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'layout' => 'array',
        'widgets' => 'array',
        'filters' => 'array',
        'permissions' => 'array',
        'is_public' => 'boolean',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'auto_refresh' => 'boolean',
        'refresh_interval' => 'integer',
    ];

    // Dashboard type constants
    const TYPE_EXECUTIVE = 'executive';
    const TYPE_OPERATIONAL = 'operational';
    const TYPE_FINANCIAL = 'financial';
    const TYPE_SALES = 'sales';
    const TYPE_INVENTORY = 'inventory';
    const TYPE_HR = 'hr';
    const TYPE_CUSTOM = 'custom';

    // Theme constants
    const THEME_LIGHT = 'light';
    const THEME_DARK = 'dark';
    const THEME_AUTO = 'auto';

    /**
     * Relationships
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function widgets()
    {
        return $this->hasMany(Widget::class);
    }

    public function reports()
    {
        return $this->belongsToMany(Report::class, 'dashboard_reports')
                    ->withPivot(['position', 'size', 'settings'])
                    ->withTimestamps();
    }

    public function userDashboards()
    {
        return $this->hasMany(UserDashboard::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('is_public', true)
              ->orWhere('created_by', $userId)
              ->orWhereHas('userDashboards', function ($subQ) use ($userId) {
                  $subQ->where('user_id', $userId);
              });
        });
    }

    /**
     * Accessors
     */
    public function getTypeLabelAttribute()
    {
        $labels = [
            self::TYPE_EXECUTIVE => 'Executive Dashboard',
            self::TYPE_OPERATIONAL => 'Operational Dashboard',
            self::TYPE_FINANCIAL => 'Financial Dashboard',
            self::TYPE_SALES => 'Sales Dashboard',
            self::TYPE_INVENTORY => 'Inventory Dashboard',
            self::TYPE_HR => 'HR Dashboard',
            self::TYPE_CUSTOM => 'Custom Dashboard',
        ];

        return $labels[$this->type] ?? 'Unknown';
    }

    public function getTypeLabelArAttribute()
    {
        $labels = [
            self::TYPE_EXECUTIVE => 'لوحة القيادة التنفيذية',
            self::TYPE_OPERATIONAL => 'لوحة العمليات',
            self::TYPE_FINANCIAL => 'لوحة المالية',
            self::TYPE_SALES => 'لوحة المبيعات',
            self::TYPE_INVENTORY => 'لوحة المخزون',
            self::TYPE_HR => 'لوحة الموارد البشرية',
            self::TYPE_CUSTOM => 'لوحة مخصصة',
        ];

        return $labels[$this->type] ?? 'غير معروف';
    }

    public function getWidgetCountAttribute()
    {
        return is_array($this->widgets) ? count($this->widgets) : 0;
    }

    public function getHasFiltersAttribute()
    {
        return is_array($this->filters) && count($this->filters) > 0;
    }

    /**
     * Methods
     */
    public function addWidget($widgetConfig)
    {
        $widgets = $this->widgets ?? [];
        $widgets[] = array_merge($widgetConfig, [
            'id' => uniqid('widget_'),
            'created_at' => now()->toISOString(),
        ]);
        
        $this->update(['widgets' => $widgets]);
        
        return $this;
    }

    public function removeWidget($widgetId)
    {
        $widgets = $this->widgets ?? [];
        $widgets = array_filter($widgets, function ($widget) use ($widgetId) {
            return $widget['id'] !== $widgetId;
        });
        
        $this->update(['widgets' => array_values($widgets)]);
        
        return $this;
    }

    public function updateWidget($widgetId, $config)
    {
        $widgets = $this->widgets ?? [];
        foreach ($widgets as &$widget) {
            if ($widget['id'] === $widgetId) {
                $widget = array_merge($widget, $config, [
                    'updated_at' => now()->toISOString(),
                ]);
                break;
            }
        }
        
        $this->update(['widgets' => $widgets]);
        
        return $this;
    }

    public function addFilter($filterConfig)
    {
        $filters = $this->filters ?? [];
        $filters[] = array_merge($filterConfig, [
            'id' => uniqid('filter_'),
            'created_at' => now()->toISOString(),
        ]);
        
        $this->update(['filters' => $filters]);
        
        return $this;
    }

    public function canAccess($userId)
    {
        // Public dashboards are accessible to all
        if ($this->is_public) {
            return true;
        }
        
        // Creator can always access
        if ($this->created_by === $userId) {
            return true;
        }
        
        // Check user-specific permissions
        return $this->userDashboards()
                   ->where('user_id', $userId)
                   ->where('can_view', true)
                   ->exists();
    }

    public function canEdit($userId)
    {
        // Creator can always edit
        if ($this->created_by === $userId) {
            return true;
        }
        
        // Check user-specific permissions
        return $this->userDashboards()
                   ->where('user_id', $userId)
                   ->where('can_edit', true)
                   ->exists();
    }

    public function clone($name = null, $userId = null)
    {
        $clone = $this->replicate();
        $clone->name = $name ?? $this->name . ' (Copy)';
        $clone->name_ar = $this->name_ar ? $this->name_ar . ' (نسخة)' : null;
        $clone->is_default = false;
        $clone->created_by = $userId ?? auth()->id();
        $clone->save();
        
        return $clone;
    }

    public static function getDefaultDashboard($type = null, $userId = null)
    {
        $query = static::active()->where('is_default', true);
        
        if ($type) {
            $query->where('type', $type);
        }
        
        if ($userId) {
            $query->forUser($userId);
        }
        
        return $query->first();
    }

    public function generateSnapshot()
    {
        return [
            'dashboard_id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'widgets' => $this->widgets,
            'generated_at' => now()->toISOString(),
            'data' => $this->generateWidgetData(),
        ];
    }

    private function generateWidgetData()
    {
        $data = [];
        
        foreach ($this->widgets ?? [] as $widget) {
            $data[$widget['id']] = $this->generateSingleWidgetData($widget);
        }
        
        return $data;
    }

    private function generateSingleWidgetData($widget)
    {
        // This would be implemented based on widget type
        // For now, return sample data structure
        return [
            'type' => $widget['type'] ?? 'unknown',
            'data' => [],
            'generated_at' => now()->toISOString(),
        ];
    }
}
