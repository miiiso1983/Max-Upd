<?php

namespace App\Modules\BusinessIntelligence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class KPI extends Model
{
    use HasFactory;

    protected $table = 'kpis';

    protected $fillable = [
        'name',
        'name_ar',
        'description',
        'description_ar',
        'category',
        'metric_type',
        'calculation_method',
        'data_source',
        'query',
        'target_value',
        'current_value',
        'previous_value',
        'unit',
        'unit_ar',
        'format',
        'trend_direction',
        'status',
        'threshold_green',
        'threshold_yellow',
        'threshold_red',
        'frequency',
        'last_calculated_at',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'query' => 'array',
        'target_value' => 'decimal:2',
        'current_value' => 'decimal:2',
        'previous_value' => 'decimal:2',
        'threshold_green' => 'decimal:2',
        'threshold_yellow' => 'decimal:2',
        'threshold_red' => 'decimal:2',
        'last_calculated_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    // Category constants
    const CATEGORY_SALES = 'sales';
    const CATEGORY_FINANCIAL = 'financial';
    const CATEGORY_INVENTORY = 'inventory';
    const CATEGORY_CUSTOMER = 'customer';
    const CATEGORY_OPERATIONAL = 'operational';
    const CATEGORY_HR = 'hr';
    const CATEGORY_QUALITY = 'quality';

    // Metric type constants
    const METRIC_COUNT = 'count';
    const METRIC_SUM = 'sum';
    const METRIC_AVERAGE = 'average';
    const METRIC_PERCENTAGE = 'percentage';
    const METRIC_RATIO = 'ratio';
    const METRIC_RATE = 'rate';

    // Trend direction constants
    const TREND_UP = 'up';
    const TREND_DOWN = 'down';
    const TREND_STABLE = 'stable';

    // Status constants
    const STATUS_EXCELLENT = 'excellent';
    const STATUS_GOOD = 'good';
    const STATUS_WARNING = 'warning';
    const STATUS_CRITICAL = 'critical';

    // Frequency constants
    const FREQUENCY_REAL_TIME = 'real_time';
    const FREQUENCY_HOURLY = 'hourly';
    const FREQUENCY_DAILY = 'daily';
    const FREQUENCY_WEEKLY = 'weekly';
    const FREQUENCY_MONTHLY = 'monthly';
    const FREQUENCY_QUARTERLY = 'quarterly';
    const FREQUENCY_YEARLY = 'yearly';

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

    // public function history()
    // {
    //     return $this->hasMany(KPIHistory::class);
    // }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Accessors
     */
    public function getCategoryLabelAttribute()
    {
        $labels = [
            self::CATEGORY_SALES => 'Sales KPIs',
            self::CATEGORY_FINANCIAL => 'Financial KPIs',
            self::CATEGORY_INVENTORY => 'Inventory KPIs',
            self::CATEGORY_CUSTOMER => 'Customer KPIs',
            self::CATEGORY_OPERATIONAL => 'Operational KPIs',
            self::CATEGORY_HR => 'HR KPIs',
            self::CATEGORY_QUALITY => 'Quality KPIs',
        ];

        return $labels[$this->category] ?? 'Unknown';
    }

    public function getCategoryLabelArAttribute()
    {
        $labels = [
            self::CATEGORY_SALES => 'مؤشرات المبيعات',
            self::CATEGORY_FINANCIAL => 'المؤشرات المالية',
            self::CATEGORY_INVENTORY => 'مؤشرات المخزون',
            self::CATEGORY_CUSTOMER => 'مؤشرات العملاء',
            self::CATEGORY_OPERATIONAL => 'المؤشرات التشغيلية',
            self::CATEGORY_HR => 'مؤشرات الموارد البشرية',
            self::CATEGORY_QUALITY => 'مؤشرات الجودة',
        ];

        return $labels[$this->category] ?? 'غير معروف';
    }

    public function getStatusLabelAttribute()
    {
        $labels = [
            self::STATUS_EXCELLENT => 'Excellent',
            self::STATUS_GOOD => 'Good',
            self::STATUS_WARNING => 'Warning',
            self::STATUS_CRITICAL => 'Critical',
        ];

        return $labels[$this->status] ?? 'Unknown';
    }

    public function getStatusLabelArAttribute()
    {
        $labels = [
            self::STATUS_EXCELLENT => 'ممتاز',
            self::STATUS_GOOD => 'جيد',
            self::STATUS_WARNING => 'تحذير',
            self::STATUS_CRITICAL => 'حرج',
        ];

        return $labels[$this->status] ?? 'غير معروف';
    }

    public function getTrendDirectionLabelAttribute()
    {
        $labels = [
            self::TREND_UP => 'Trending Up',
            self::TREND_DOWN => 'Trending Down',
            self::TREND_STABLE => 'Stable',
        ];

        return $labels[$this->trend_direction] ?? 'Unknown';
    }

    public function getVarianceAttribute()
    {
        if ($this->target_value > 0) {
            return (($this->current_value - $this->target_value) / $this->target_value) * 100;
        }
        return 0;
    }

    public function getChangeFromPreviousAttribute()
    {
        if ($this->previous_value > 0) {
            return (($this->current_value - $this->previous_value) / $this->previous_value) * 100;
        }
        return 0;
    }

    public function getFormattedCurrentValueAttribute()
    {
        return $this->formatValue($this->current_value);
    }

    public function getFormattedTargetValueAttribute()
    {
        return $this->formatValue($this->target_value);
    }

    /**
     * Methods
     */
    public function calculate()
    {
        $previousValue = $this->current_value;
        
        try {
            $newValue = $this->executeCalculation();
            
            $this->update([
                'previous_value' => $previousValue,
                'current_value' => $newValue,
                'last_calculated_at' => now(),
                'trend_direction' => $this->calculateTrend($previousValue, $newValue),
                'status' => $this->calculateStatus($newValue),
            ]);
            
            // Store in history (disabled for now)
            // $this->history()->create([
            //     'value' => $newValue,
            //     'calculated_at' => now(),
            //     'calculation_method' => $this->calculation_method,
            // ]);
            
            return $newValue;
        } catch (\Exception $e) {
            \Log::error("KPI calculation failed for {$this->name}: " . $e->getMessage());
            throw $e;
        }
    }

    private function executeCalculation()
    {
        // This would be implemented based on the data source and calculation method
        // For now, return a sample calculation
        switch ($this->category) {
            case self::CATEGORY_SALES:
                return $this->calculateSalesKPI();
            case self::CATEGORY_INVENTORY:
                return $this->calculateInventoryKPI();
            case self::CATEGORY_FINANCIAL:
                return $this->calculateFinancialKPI();
            default:
                return rand(1, 100);
        }
    }

    private function calculateSalesKPI()
    {
        // Sample sales KPI calculation
        return rand(50, 150);
    }

    private function calculateInventoryKPI()
    {
        // Sample inventory KPI calculation
        return rand(20, 80);
    }

    private function calculateFinancialKPI()
    {
        // Sample financial KPI calculation
        return rand(100, 200);
    }

    private function calculateTrend($previousValue, $currentValue)
    {
        if ($previousValue == 0) {
            return self::TREND_STABLE;
        }
        
        $change = (($currentValue - $previousValue) / $previousValue) * 100;
        
        if ($change > 5) {
            return self::TREND_UP;
        } elseif ($change < -5) {
            return self::TREND_DOWN;
        } else {
            return self::TREND_STABLE;
        }
    }

    private function calculateStatus($value)
    {
        if ($value >= $this->threshold_green) {
            return self::STATUS_EXCELLENT;
        } elseif ($value >= $this->threshold_yellow) {
            return self::STATUS_GOOD;
        } elseif ($value >= $this->threshold_red) {
            return self::STATUS_WARNING;
        } else {
            return self::STATUS_CRITICAL;
        }
    }

    private function formatValue($value)
    {
        switch ($this->format) {
            case 'currency':
                return number_format($value, 2) . ' ' . ($this->unit ?? 'IQD');
            case 'percentage':
                return number_format($value, 1) . '%';
            case 'decimal':
                return number_format($value, 2);
            case 'integer':
                return number_format($value, 0);
            default:
                return $value . ' ' . ($this->unit ?? '');
        }
    }

    public function getHistoricalData($days = 30)
    {
        // Historical data disabled for now
        return collect([]);
        // return $this->history()
        //            ->where('calculated_at', '>=', now()->subDays($days))
        //            ->orderBy('calculated_at')
        //            ->get();
    }

    public static function calculateAllActive()
    {
        $kpis = static::active()->get();
        
        foreach ($kpis as $kpi) {
            try {
                $kpi->calculate();
            } catch (\Exception $e) {
                \Log::error("Failed to calculate KPI {$kpi->id}: " . $e->getMessage());
            }
        }
        
        return $kpis->count();
    }
}
