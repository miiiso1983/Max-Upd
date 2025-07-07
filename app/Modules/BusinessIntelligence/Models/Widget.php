<?php

namespace App\Modules\BusinessIntelligence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class Widget extends Model
{
    use HasFactory;

    protected $fillable = [
        'dashboard_id',
        'name',
        'name_ar',
        'type',
        'data_source',
        'query',
        'config',
        'position',
        'size',
        'refresh_interval',
        'auto_refresh',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'query' => 'array',
        'config' => 'array',
        'position' => 'array',
        'size' => 'array',
        'refresh_interval' => 'integer',
        'auto_refresh' => 'boolean',
        'is_active' => 'boolean',
    ];

    // Widget type constants
    const TYPE_KPI = 'kpi';
    const TYPE_CHART_LINE = 'chart_line';
    const TYPE_CHART_BAR = 'chart_bar';
    const TYPE_CHART_PIE = 'chart_pie';
    const TYPE_CHART_DONUT = 'chart_donut';
    const TYPE_CHART_AREA = 'chart_area';
    const TYPE_TABLE = 'table';
    const TYPE_LIST = 'list';
    const TYPE_GAUGE = 'gauge';
    const TYPE_PROGRESS = 'progress';
    const TYPE_MAP = 'map';
    const TYPE_CALENDAR = 'calendar';
    const TYPE_TIMELINE = 'timeline';
    const TYPE_FUNNEL = 'funnel';
    const TYPE_HEATMAP = 'heatmap';
    const TYPE_TREEMAP = 'treemap';
    const TYPE_SCATTER = 'scatter';
    const TYPE_RADAR = 'radar';

    // Data source constants
    const SOURCE_SALES = 'sales';
    const SOURCE_INVENTORY = 'inventory';
    const SOURCE_FINANCIAL = 'financial';
    const SOURCE_HR = 'hr';
    const SOURCE_CRM = 'crm';
    const SOURCE_CUSTOM = 'custom';

    /**
     * Relationships
     */
    public function dashboard()
    {
        return $this->belongsTo(Dashboard::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByDataSource($query, $source)
    {
        return $query->where('data_source', $source);
    }

    /**
     * Accessors
     */
    public function getTypeLabelAttribute()
    {
        $labels = [
            self::TYPE_KPI => 'KPI Card',
            self::TYPE_CHART_LINE => 'Line Chart',
            self::TYPE_CHART_BAR => 'Bar Chart',
            self::TYPE_CHART_PIE => 'Pie Chart',
            self::TYPE_CHART_DONUT => 'Donut Chart',
            self::TYPE_CHART_AREA => 'Area Chart',
            self::TYPE_TABLE => 'Data Table',
            self::TYPE_LIST => 'List View',
            self::TYPE_GAUGE => 'Gauge',
            self::TYPE_PROGRESS => 'Progress Bar',
            self::TYPE_MAP => 'Map',
            self::TYPE_CALENDAR => 'Calendar',
            self::TYPE_TIMELINE => 'Timeline',
            self::TYPE_FUNNEL => 'Funnel Chart',
            self::TYPE_HEATMAP => 'Heat Map',
            self::TYPE_TREEMAP => 'Tree Map',
            self::TYPE_SCATTER => 'Scatter Plot',
            self::TYPE_RADAR => 'Radar Chart',
        ];

        return $labels[$this->type] ?? 'Unknown';
    }

    public function getTypeLabelArAttribute()
    {
        $labels = [
            self::TYPE_KPI => 'بطاقة مؤشر الأداء',
            self::TYPE_CHART_LINE => 'مخطط خطي',
            self::TYPE_CHART_BAR => 'مخطط أعمدة',
            self::TYPE_CHART_PIE => 'مخطط دائري',
            self::TYPE_CHART_DONUT => 'مخطط حلقي',
            self::TYPE_CHART_AREA => 'مخطط منطقة',
            self::TYPE_TABLE => 'جدول البيانات',
            self::TYPE_LIST => 'عرض القائمة',
            self::TYPE_GAUGE => 'مقياس',
            self::TYPE_PROGRESS => 'شريط التقدم',
            self::TYPE_MAP => 'خريطة',
            self::TYPE_CALENDAR => 'تقويم',
            self::TYPE_TIMELINE => 'الجدول الزمني',
            self::TYPE_FUNNEL => 'مخطط قمعي',
            self::TYPE_HEATMAP => 'خريطة حرارية',
            self::TYPE_TREEMAP => 'خريطة شجرية',
            self::TYPE_SCATTER => 'مخطط مبعثر',
            self::TYPE_RADAR => 'مخطط رادار',
        ];

        return $labels[$this->type] ?? 'غير معروف';
    }

    public function getDataSourceLabelAttribute()
    {
        $labels = [
            self::SOURCE_SALES => 'Sales Data',
            self::SOURCE_INVENTORY => 'Inventory Data',
            self::SOURCE_FINANCIAL => 'Financial Data',
            self::SOURCE_HR => 'HR Data',
            self::SOURCE_CRM => 'CRM Data',
            self::SOURCE_CUSTOM => 'Custom Data',
        ];

        return $labels[$this->data_source] ?? 'Unknown';
    }

    public function getDataSourceLabelArAttribute()
    {
        $labels = [
            self::SOURCE_SALES => 'بيانات المبيعات',
            self::SOURCE_INVENTORY => 'بيانات المخزون',
            self::SOURCE_FINANCIAL => 'البيانات المالية',
            self::SOURCE_HR => 'بيانات الموارد البشرية',
            self::SOURCE_CRM => 'بيانات إدارة العملاء',
            self::SOURCE_CUSTOM => 'بيانات مخصصة',
        ];

        return $labels[$this->data_source] ?? 'غير معروف';
    }

    /**
     * Methods
     */
    public function generateData($filters = [])
    {
        switch ($this->data_source) {
            case self::SOURCE_SALES:
                return $this->generateSalesData($filters);
            case self::SOURCE_INVENTORY:
                return $this->generateInventoryData($filters);
            case self::SOURCE_FINANCIAL:
                return $this->generateFinancialData($filters);
            case self::SOURCE_HR:
                return $this->generateHRData($filters);
            case self::SOURCE_CRM:
                return $this->generateCRMData($filters);
            default:
                return $this->generateCustomData($filters);
        }
    }

    private function generateSalesData($filters = [])
    {
        // Implementation would depend on the specific query and type
        return [
            'type' => $this->type,
            'data' => [],
            'generated_at' => now()->toISOString(),
        ];
    }

    private function generateInventoryData($filters = [])
    {
        // Implementation would depend on the specific query and type
        return [
            'type' => $this->type,
            'data' => [],
            'generated_at' => now()->toISOString(),
        ];
    }

    private function generateFinancialData($filters = [])
    {
        // Implementation would depend on the specific query and type
        return [
            'type' => $this->type,
            'data' => [],
            'generated_at' => now()->toISOString(),
        ];
    }

    private function generateHRData($filters = [])
    {
        // Implementation would depend on the specific query and type
        return [
            'type' => $this->type,
            'data' => [],
            'generated_at' => now()->toISOString(),
        ];
    }

    private function generateCRMData($filters = [])
    {
        // Implementation would depend on the specific query and type
        return [
            'type' => $this->type,
            'data' => [],
            'generated_at' => now()->toISOString(),
        ];
    }

    private function generateCustomData($filters = [])
    {
        // Implementation would depend on the specific query and type
        return [
            'type' => $this->type,
            'data' => [],
            'generated_at' => now()->toISOString(),
        ];
    }

    public function updatePosition($x, $y, $width = null, $height = null)
    {
        $position = $this->position ?? [];
        $size = $this->size ?? [];
        
        $position['x'] = $x;
        $position['y'] = $y;
        
        if ($width !== null) {
            $size['width'] = $width;
        }
        
        if ($height !== null) {
            $size['height'] = $height;
        }
        
        $this->update([
            'position' => $position,
            'size' => $size,
        ]);
        
        return $this;
    }

    public function updateConfig($config)
    {
        $currentConfig = $this->config ?? [];
        $newConfig = array_merge($currentConfig, $config);
        
        $this->update(['config' => $newConfig]);
        
        return $this;
    }

    public function clone($dashboardId = null)
    {
        $clone = $this->replicate();
        $clone->dashboard_id = $dashboardId ?? $this->dashboard_id;
        $clone->name = $this->name . ' (Copy)';
        $clone->name_ar = $this->name_ar ? $this->name_ar . ' (نسخة)' : null;
        $clone->created_by = auth()->id();
        $clone->save();
        
        return $clone;
    }
}
