<?php

namespace App\Modules\BusinessIntelligence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class Report extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'name_ar',
        'description',
        'description_ar',
        'type',
        'category',
        'data_source',
        'query',
        'parameters',
        'columns',
        'filters',
        'sorting',
        'grouping',
        'aggregations',
        'format',
        'template',
        'schedule',
        'is_public',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'query' => 'array',
        'parameters' => 'array',
        'columns' => 'array',
        'filters' => 'array',
        'sorting' => 'array',
        'grouping' => 'array',
        'aggregations' => 'array',
        'schedule' => 'array',
        'is_public' => 'boolean',
        'is_active' => 'boolean',
    ];

    // Report type constants
    const TYPE_TABULAR = 'tabular';
    const TYPE_SUMMARY = 'summary';
    const TYPE_ANALYTICAL = 'analytical';
    const TYPE_DASHBOARD = 'dashboard';
    const TYPE_EXPORT = 'export';

    // Category constants
    const CATEGORY_SALES = 'sales';
    const CATEGORY_INVENTORY = 'inventory';
    const CATEGORY_FINANCIAL = 'financial';
    const CATEGORY_HR = 'hr';
    const CATEGORY_CRM = 'crm';
    const CATEGORY_OPERATIONAL = 'operational';
    const CATEGORY_COMPLIANCE = 'compliance';

    // Format constants
    const FORMAT_PDF = 'pdf';
    const FORMAT_EXCEL = 'excel';
    const FORMAT_CSV = 'csv';
    const FORMAT_HTML = 'html';
    const FORMAT_JSON = 'json';

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

    public function dashboards()
    {
        return $this->belongsToMany(Dashboard::class, 'dashboard_reports')
                    ->withPivot(['position', 'size', 'settings'])
                    ->withTimestamps();
    }

    public function executions()
    {
        return $this->hasMany(ReportExecution::class);
    }

    public function schedules()
    {
        return $this->hasMany(ReportSchedule::class);
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

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('is_public', true)
              ->orWhere('created_by', $userId);
        });
    }

    /**
     * Accessors
     */
    public function getTypeLabelAttribute()
    {
        $labels = [
            self::TYPE_TABULAR => 'Tabular Report',
            self::TYPE_SUMMARY => 'Summary Report',
            self::TYPE_ANALYTICAL => 'Analytical Report',
            self::TYPE_DASHBOARD => 'Dashboard Report',
            self::TYPE_EXPORT => 'Export Report',
        ];

        return $labels[$this->type] ?? 'Unknown';
    }

    public function getTypeLabelArAttribute()
    {
        $labels = [
            self::TYPE_TABULAR => 'تقرير جدولي',
            self::TYPE_SUMMARY => 'تقرير ملخص',
            self::TYPE_ANALYTICAL => 'تقرير تحليلي',
            self::TYPE_DASHBOARD => 'تقرير لوحة القيادة',
            self::TYPE_EXPORT => 'تقرير تصدير',
        ];

        return $labels[$this->type] ?? 'غير معروف';
    }

    public function getCategoryLabelAttribute()
    {
        $labels = [
            self::CATEGORY_SALES => 'Sales Reports',
            self::CATEGORY_INVENTORY => 'Inventory Reports',
            self::CATEGORY_FINANCIAL => 'Financial Reports',
            self::CATEGORY_HR => 'HR Reports',
            self::CATEGORY_CRM => 'CRM Reports',
            self::CATEGORY_OPERATIONAL => 'Operational Reports',
            self::CATEGORY_COMPLIANCE => 'Compliance Reports',
        ];

        return $labels[$this->category] ?? 'Unknown';
    }

    public function getCategoryLabelArAttribute()
    {
        $labels = [
            self::CATEGORY_SALES => 'تقارير المبيعات',
            self::CATEGORY_INVENTORY => 'تقارير المخزون',
            self::CATEGORY_FINANCIAL => 'التقارير المالية',
            self::CATEGORY_HR => 'تقارير الموارد البشرية',
            self::CATEGORY_CRM => 'تقارير إدارة العملاء',
            self::CATEGORY_OPERATIONAL => 'التقارير التشغيلية',
            self::CATEGORY_COMPLIANCE => 'تقارير الامتثال',
        ];

        return $labels[$this->category] ?? 'غير معروف';
    }

    public function getFormatLabelAttribute()
    {
        $labels = [
            self::FORMAT_PDF => 'PDF Document',
            self::FORMAT_EXCEL => 'Excel Spreadsheet',
            self::FORMAT_CSV => 'CSV File',
            self::FORMAT_HTML => 'HTML Page',
            self::FORMAT_JSON => 'JSON Data',
        ];

        return $labels[$this->format] ?? 'Unknown';
    }

    public function getExecutionCountAttribute()
    {
        return $this->executions()->count();
    }

    public function getLastExecutionAttribute()
    {
        return $this->executions()->latest()->first();
    }

    /**
     * Methods
     */
    public function execute($parameters = [], $format = null)
    {
        $execution = $this->executions()->create([
            'parameters' => $parameters,
            'format' => $format ?? $this->format,
            'status' => ReportExecution::STATUS_RUNNING,
            'started_at' => now(),
            'executed_by' => auth()->id(),
        ]);

        try {
            $data = $this->generateData($parameters);
            $output = $this->formatOutput($data, $format ?? $this->format);
            
            $execution->update([
                'status' => ReportExecution::STATUS_COMPLETED,
                'completed_at' => now(),
                'output' => $output,
                'row_count' => is_array($data) ? count($data) : 0,
            ]);
            
            return $execution;
        } catch (\Exception $e) {
            $execution->update([
                'status' => ReportExecution::STATUS_FAILED,
                'completed_at' => now(),
                'error_message' => $e->getMessage(),
            ]);
            
            throw $e;
        }
    }

    public function generateData($parameters = [])
    {
        // This would be implemented based on the data source and query
        // For now, return sample data structure
        return [
            'headers' => $this->getColumnHeaders(),
            'data' => [],
            'summary' => [],
            'generated_at' => now()->toISOString(),
        ];
    }

    public function formatOutput($data, $format)
    {
        switch ($format) {
            case self::FORMAT_PDF:
                return $this->generatePDF($data);
            case self::FORMAT_EXCEL:
                return $this->generateExcel($data);
            case self::FORMAT_CSV:
                return $this->generateCSV($data);
            case self::FORMAT_HTML:
                return $this->generateHTML($data);
            case self::FORMAT_JSON:
                return json_encode($data);
            default:
                return $data;
        }
    }

    private function generatePDF($data)
    {
        // PDF generation logic would go here
        return 'PDF content placeholder';
    }

    private function generateExcel($data)
    {
        // Excel generation logic would go here
        return 'Excel content placeholder';
    }

    private function generateCSV($data)
    {
        // CSV generation logic would go here
        return 'CSV content placeholder';
    }

    private function generateHTML($data)
    {
        // HTML generation logic would go here
        return 'HTML content placeholder';
    }

    public function getColumnHeaders()
    {
        if (is_array($this->columns)) {
            return array_column($this->columns, 'label');
        }
        
        return [];
    }

    public function addParameter($name, $type, $label, $required = false, $defaultValue = null)
    {
        $parameters = $this->parameters ?? [];
        $parameters[] = [
            'name' => $name,
            'type' => $type,
            'label' => $label,
            'required' => $required,
            'default_value' => $defaultValue,
        ];
        
        $this->update(['parameters' => $parameters]);
        
        return $this;
    }

    public function addColumn($name, $label, $type = 'string', $aggregation = null)
    {
        $columns = $this->columns ?? [];
        $columns[] = [
            'name' => $name,
            'label' => $label,
            'type' => $type,
            'aggregation' => $aggregation,
        ];
        
        $this->update(['columns' => $columns]);
        
        return $this;
    }

    public function clone($name = null)
    {
        $clone = $this->replicate();
        $clone->name = $name ?? $this->name . ' (Copy)';
        $clone->name_ar = $this->name_ar ? $this->name_ar . ' (نسخة)' : null;
        $clone->created_by = auth()->id();
        $clone->save();
        
        return $clone;
    }
}
