<?php

namespace App\Modules\BusinessIntelligence\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\BusinessIntelligence\Models\Dashboard;
use App\Modules\BusinessIntelligence\Models\Widget;
use App\Modules\BusinessIntelligence\Models\Report;
use App\Modules\BusinessIntelligence\Models\KPI;
// use App\Modules\BusinessIntelligence\Services\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BusinessIntelligenceController extends Controller
{
    // protected $analyticsService;

    public function __construct()
    {
        // $this->analyticsService = $analyticsService;
    }

    /**
     * Get executive dashboard
     */
    public function executiveDashboard(Request $request)
    {
        $dashboard = $this->getOrCreateExecutiveDashboard();
        
        $data = [
            'dashboard' => $dashboard,
            'kpis' => $this->getExecutiveKPIs(),
            'sales_overview' => $this->getSalesOverview($request),
            'financial_summary' => $this->getFinancialSummary($request),
            'inventory_status' => $this->getInventoryStatus($request),
            'customer_metrics' => $this->getCustomerMetrics($request),
            'operational_metrics' => $this->getOperationalMetrics($request),
            'alerts' => $this->getExecutiveAlerts(),
        ];

        return response()->json($data);
    }

    /**
     * Get sales dashboard
     */
    public function salesDashboard(Request $request)
    {
        $dashboard = $this->getOrCreateSalesDashboard();
        
        $data = [
            'dashboard' => $dashboard,
            'sales_metrics' => $this->getSalesMetrics($request),
            'revenue_trends' => $this->getRevenueTrends($request),
            'product_performance' => $this->getProductPerformance($request),
            'customer_analysis' => $this->getCustomerAnalysis($request),
            'sales_team_performance' => $this->getSalesTeamPerformance($request),
            'pipeline_analysis' => $this->getPipelineAnalysis($request),
        ];

        return response()->json($data);
    }

    /**
     * Get inventory dashboard
     */
    public function inventoryDashboard(Request $request)
    {
        $dashboard = $this->getOrCreateInventoryDashboard();
        
        $data = [
            'dashboard' => $dashboard,
            'inventory_overview' => $this->getInventoryOverview($request),
            'stock_levels' => $this->getStockLevels($request),
            'inventory_turnover' => $this->getInventoryTurnover($request),
            'expiry_analysis' => $this->getExpiryAnalysis($request),
            'warehouse_utilization' => $this->getWarehouseUtilization($request),
            'inventory_alerts' => $this->getInventoryAlerts($request),
        ];

        return response()->json($data);
    }

    /**
     * Get financial dashboard
     */
    public function financialDashboard(Request $request)
    {
        $dashboard = $this->getOrCreateFinancialDashboard();
        
        $data = [
            'dashboard' => $dashboard,
            'financial_overview' => $this->getFinancialOverview($request),
            'profit_loss' => $this->getProfitLoss($request),
            'cash_flow' => $this->getCashFlow($request),
            'accounts_receivable' => $this->getAccountsReceivable($request),
            'accounts_payable' => $this->getAccountsPayable($request),
            'financial_ratios' => $this->getFinancialRatios($request),
        ];

        return response()->json($data);
    }

    /**
     * Get all KPIs
     */
    public function getKPIs(Request $request)
    {
        $category = $request->get('category');
        $status = $request->get('status');
        
        $query = KPI::active()->with(['creator']);
        
        if ($category) {
            $query->where('category', $category);
        }
        
        if ($status) {
            $query->where('status', $status);
        }
        
        $kpis = $query->get();
        
        return response()->json([
            'kpis' => $kpis,
            'summary' => [
                'total_kpis' => $kpis->count(),
                'excellent' => $kpis->where('status', KPI::STATUS_EXCELLENT)->count(),
                'good' => $kpis->where('status', KPI::STATUS_GOOD)->count(),
                'warning' => $kpis->where('status', KPI::STATUS_WARNING)->count(),
                'critical' => $kpis->where('status', KPI::STATUS_CRITICAL)->count(),
            ],
        ]);
    }

    /**
     * Calculate KPIs
     */
    public function calculateKPIs(Request $request)
    {
        $kpiIds = $request->get('kpi_ids', []);
        
        if (empty($kpiIds)) {
            $calculatedCount = KPI::calculateAllActive();
        } else {
            $kpis = KPI::whereIn('id', $kpiIds)->get();
            $calculatedCount = 0;
            
            foreach ($kpis as $kpi) {
                try {
                    $kpi->calculate();
                    $calculatedCount++;
                } catch (\Exception $e) {
                    \Log::error("Failed to calculate KPI {$kpi->id}: " . $e->getMessage());
                }
            }
        }
        
        return response()->json([
            'success' => true,
            'message' => 'KPIs calculated successfully',
            'message_ar' => 'تم حساب مؤشرات الأداء بنجاح',
            'calculated_count' => $calculatedCount,
        ]);
    }

    /**
     * Get analytics data
     */
    public function getAnalytics(Request $request)
    {
        $type = $request->get('type', 'overview');
        $period = $request->get('period', '30d');
        $filters = $request->get('filters', []);
        
        $data = $this->getAnalyticsData($type, $period, $filters);
        
        return response()->json($data);
    }

    /**
     * Get reports
     */
    public function getReports(Request $request)
    {
        $category = $request->get('category');
        $type = $request->get('type');
        
        $query = Report::active()->with(['creator']);
        
        if ($category) {
            $query->where('category', $category);
        }
        
        if ($type) {
            $query->where('type', $type);
        }
        
        $reports = $query->paginate(20);
        
        return response()->json($reports);
    }

    /**
     * Execute report
     */
    public function executeReport(Request $request, Report $report)
    {
        $validated = $request->validate([
            'parameters' => 'nullable|array',
            'format' => 'nullable|in:pdf,excel,csv,html,json',
        ]);

        try {
            $execution = $report->execute(
                $validated['parameters'] ?? [],
                $validated['format'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Report executed successfully',
                'message_ar' => 'تم تنفيذ التقرير بنجاح',
                'execution' => $execution,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Report execution failed: ' . $e->getMessage(),
                'message_ar' => 'فشل في تنفيذ التقرير: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Export business intelligence report
     */
    public function exportReport(Request $request)
    {
        $validated = $request->validate([
            'format' => 'required|in:pdf,excel,csv,html,json',
            'data' => 'required|array',
            'metadata' => 'nullable|array',
        ]);

        try {
            $format = $validated['format'];
            $data = $validated['data'];
            $metadata = $validated['metadata'] ?? [];

            // Generate filename
            $timestamp = now()->format('Y-m-d-H-i-s');
            $filename = "business-intelligence-report-{$timestamp}";

            switch ($format) {
                case 'pdf':
                    return $this->exportToPDF($data, $metadata, $filename);
                case 'excel':
                    return $this->exportToExcel($data, $metadata, $filename);
                case 'csv':
                    return $this->exportToCSV($data, $metadata, $filename);
                case 'html':
                    return $this->exportToHTML($data, $metadata, $filename);
                case 'json':
                    return $this->exportToJSON($data, $metadata, $filename);
                default:
                    throw new \Exception('Unsupported export format');
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Export failed: ' . $e->getMessage(),
                'message_ar' => 'فشل في التصدير: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Helper methods for dashboard data
     */
    private function getOrCreateExecutiveDashboard()
    {
        return Dashboard::firstOrCreate(
            ['type' => Dashboard::TYPE_EXECUTIVE, 'is_default' => true],
            [
                'name' => 'Executive Dashboard',
                'name_ar' => 'لوحة القيادة التنفيذية',
                'description' => 'High-level overview for executives',
                'description_ar' => 'نظرة عامة عالية المستوى للمديرين التنفيذيين',
                'is_public' => true,
                'is_active' => true,
                'created_by' => auth()->id() ?? 1,
            ]
        );
    }

    private function getOrCreateSalesDashboard()
    {
        return Dashboard::firstOrCreate(
            ['type' => Dashboard::TYPE_SALES, 'is_default' => true],
            [
                'name' => 'Sales Dashboard',
                'name_ar' => 'لوحة المبيعات',
                'description' => 'Sales performance and analytics',
                'description_ar' => 'أداء المبيعات والتحليلات',
                'is_public' => true,
                'is_active' => true,
                'created_by' => auth()->id() ?? 1,
            ]
        );
    }

    private function getOrCreateInventoryDashboard()
    {
        return Dashboard::firstOrCreate(
            ['type' => Dashboard::TYPE_INVENTORY, 'is_default' => true],
            [
                'name' => 'Inventory Dashboard',
                'name_ar' => 'لوحة المخزون',
                'description' => 'Inventory levels and management',
                'description_ar' => 'مستويات المخزون والإدارة',
                'is_public' => true,
                'is_active' => true,
                'created_by' => auth()->id() ?? 1,
            ]
        );
    }

    private function getOrCreateFinancialDashboard()
    {
        return Dashboard::firstOrCreate(
            ['type' => Dashboard::TYPE_FINANCIAL, 'is_default' => true],
            [
                'name' => 'Financial Dashboard',
                'name_ar' => 'لوحة المالية',
                'description' => 'Financial performance and metrics',
                'description_ar' => 'الأداء المالي والمقاييس',
                'is_public' => true,
                'is_active' => true,
                'created_by' => auth()->id() ?? 1,
            ]
        );
    }

    private function getExecutiveKPIs()
    {
        return KPI::active()
                 ->whereIn('category', [
                     KPI::CATEGORY_SALES,
                     KPI::CATEGORY_FINANCIAL,
                     KPI::CATEGORY_OPERATIONAL
                 ])
                 ->limit(8)
                 ->get();
    }

    private function getSalesOverview($request)
    {
        return [
            'total_revenue' => 125000,
            'total_orders' => 450,
            'average_order_value' => 278,
            'growth_rate' => 12.5,
            'monthly_trend' => [
                ['month' => 'Jan', 'revenue' => 95000],
                ['month' => 'Feb', 'revenue' => 105000],
                ['month' => 'Mar', 'revenue' => 125000],
            ],
        ];
    }

    private function getFinancialSummary($request)
    {
        return [
            'gross_profit' => 45000,
            'net_profit' => 32000,
            'profit_margin' => 25.6,
            'cash_flow' => 18000,
        ];
    }

    private function getInventoryStatus($request)
    {
        return [
            'total_products' => 1250,
            'low_stock_items' => 45,
            'out_of_stock_items' => 12,
            'expiring_soon' => 23,
            'inventory_value' => 450000,
        ];
    }

    private function getCustomerMetrics($request)
    {
        return [
            'total_customers' => 850,
            'new_customers' => 45,
            'customer_retention_rate' => 85.5,
            'customer_lifetime_value' => 2500,
        ];
    }

    private function getOperationalMetrics($request)
    {
        return [
            'order_fulfillment_rate' => 96.5,
            'average_delivery_time' => 2.3,
            'employee_productivity' => 87.2,
            'quality_score' => 94.8,
        ];
    }

    private function getExecutiveAlerts()
    {
        return [
            [
                'type' => 'warning',
                'title' => 'Low Stock Alert',
                'title_ar' => 'تنبيه مخزون منخفض',
                'message' => '45 products are below reorder level',
                'message_ar' => '45 منتج أقل من مستوى إعادة الطلب',
            ],
            [
                'type' => 'info',
                'title' => 'Sales Target',
                'title_ar' => 'هدف المبيعات',
                'message' => 'Monthly sales target achieved 87%',
                'message_ar' => 'تم تحقيق 87% من هدف المبيعات الشهري',
            ],
        ];
    }

    /**
     * Export to PDF
     */
    private function exportToPDF($data, $metadata, $filename)
    {
        $html = $this->generateReportHTML($data, $metadata);

        // For now, return HTML content as PDF placeholder
        // In production, you would use a PDF library like DomPDF or wkhtmltopdf
        return response($html)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}.pdf\"");
    }

    /**
     * Export to Excel
     */
    private function exportToExcel($data, $metadata, $filename)
    {
        $csv = $this->generateCSVContent($data, $metadata);

        // For now, return CSV content as Excel placeholder
        // In production, you would use PhpSpreadsheet or similar
        return response($csv)
            ->header('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}.xlsx\"");
    }

    /**
     * Export to CSV
     */
    private function exportToCSV($data, $metadata, $filename)
    {
        $csv = $this->generateCSVContent($data, $metadata);

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}.csv\"");
    }

    /**
     * Export to HTML
     */
    private function exportToHTML($data, $metadata, $filename)
    {
        $html = $this->generateReportHTML($data, $metadata);

        return response($html)
            ->header('Content-Type', 'text/html')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}.html\"");
    }

    /**
     * Export to JSON
     */
    private function exportToJSON($data, $metadata, $filename)
    {
        $json = json_encode([
            'metadata' => $metadata,
            'data' => $data,
            'exported_at' => now()->toISOString(),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return response($json)
            ->header('Content-Type', 'application/json')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}.json\"");
    }

    /**
     * Generate CSV content
     */
    private function generateCSVContent($data, $metadata)
    {
        $output = fopen('php://temp', 'r+');

        // Add metadata as comments
        if (!empty($metadata)) {
            fputcsv($output, ['# تقرير ذكاء الأعمال']);
            fputcsv($output, ['# تاريخ الإنشاء: ' . ($metadata['generated_at'] ?? now())]);
            fputcsv($output, ['# المستخدم: ' . ($metadata['generated_by'] ?? 'غير محدد')]);
            fputcsv($output, []);
        }

        // Add KPIs section
        if (isset($data['kpis'])) {
            fputcsv($output, ['مؤشرات الأداء الرئيسية']);
            fputcsv($output, ['المؤشر', 'القيمة', 'الهدف', 'الاتجاه']);

            foreach ($data['kpis'] as $key => $kpi) {
                $name = $this->getKPINameInArabic($key);
                fputcsv($output, [
                    $name,
                    $kpi['value'] . '%',
                    $kpi['target'] . '%',
                    $kpi['trend'] === 'up' ? 'صاعد' : ($kpi['trend'] === 'down' ? 'هابط' : 'ثابت')
                ]);
            }
            fputcsv($output, []);
        }

        // Add competitor analysis
        if (isset($data['competitorAnalysis'])) {
            fputcsv($output, ['تحليل المنافسين']);
            fputcsv($output, ['الشركة', 'حصة السوق', 'النمو']);

            foreach ($data['competitorAnalysis'] as $competitor) {
                fputcsv($output, [
                    $competitor['competitor'],
                    $competitor['market_share'] . '%',
                    $competitor['growth'] . '%'
                ]);
            }
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    /**
     * Generate HTML report
     */
    private function generateReportHTML($data, $metadata)
    {
        $html = '<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تقرير ذكاء الأعمال</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; direction: rtl; }
        .header { text-align: center; margin-bottom: 30px; }
        .section { margin-bottom: 30px; }
        .kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; }
        .kpi-card { border: 1px solid #ddd; padding: 15px; border-radius: 8px; }
        .kpi-value { font-size: 24px; font-weight: bold; color: #3b82f6; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: right; }
        th { background-color: #f5f5f5; }
    </style>
</head>
<body>
    <div class="header">
        <h1>تقرير ذكاء الأعمال</h1>
        <p>تاريخ الإنشاء: ' . ($metadata['generated_at'] ?? now()) . '</p>
        <p>المستخدم: ' . ($metadata['generated_by'] ?? 'غير محدد') . '</p>
    </div>';

        // Add KPIs section
        if (isset($data['kpis'])) {
            $html .= '<div class="section">
                <h2>مؤشرات الأداء الرئيسية</h2>
                <div class="kpi-grid">';

            foreach ($data['kpis'] as $key => $kpi) {
                $name = $this->getKPINameInArabic($key);
                $trendIcon = $kpi['trend'] === 'up' ? '↗️' : ($kpi['trend'] === 'down' ? '↘️' : '➡️');

                $html .= '<div class="kpi-card">
                    <h3>' . $name . '</h3>
                    <div class="kpi-value">' . $kpi['value'] . '%</div>
                    <p>الهدف: ' . $kpi['target'] . '%</p>
                    <p>الاتجاه: ' . $trendIcon . '</p>
                </div>';
            }

            $html .= '</div></div>';
        }

        // Add competitor analysis
        if (isset($data['competitorAnalysis'])) {
            $html .= '<div class="section">
                <h2>تحليل المنافسين</h2>
                <table>
                    <thead>
                        <tr>
                            <th>الشركة</th>
                            <th>حصة السوق</th>
                            <th>النمو</th>
                        </tr>
                    </thead>
                    <tbody>';

            foreach ($data['competitorAnalysis'] as $competitor) {
                $html .= '<tr>
                    <td>' . $competitor['competitor'] . '</td>
                    <td>' . $competitor['market_share'] . '%</td>
                    <td>' . $competitor['growth'] . '%</td>
                </tr>';
            }

            $html .= '</tbody></table></div>';
        }

        $html .= '</body></html>';

        return $html;
    }

    /**
     * Get KPI name in Arabic
     */
    private function getKPINameInArabic($key)
    {
        $names = [
            'revenue_growth' => 'نمو الإيرادات',
            'customer_retention' => 'الاحتفاظ بالعملاء',
            'market_penetration' => 'اختراق السوق',
            'operational_efficiency' => 'الكفاءة التشغيلية',
        ];

        return $names[$key] ?? $key;
    }

    // Additional helper methods would be implemented here for other dashboard sections
    private function getSalesMetrics($request) { return []; }
    private function getRevenueTrends($request) { return []; }
    private function getProductPerformance($request) { return []; }
    private function getCustomerAnalysis($request) { return []; }
    private function getSalesTeamPerformance($request) { return []; }
    private function getPipelineAnalysis($request) { return []; }
    private function getInventoryOverview($request) { return []; }
    private function getStockLevels($request) { return []; }
    private function getInventoryTurnover($request) { return []; }
    private function getExpiryAnalysis($request) { return []; }
    private function getWarehouseUtilization($request) { return []; }
    private function getInventoryAlerts($request) { return []; }
    private function getFinancialOverview($request) { return []; }
    private function getProfitLoss($request) { return []; }
    private function getCashFlow($request) { return []; }
    private function getAccountsReceivable($request) { return []; }
    private function getAccountsPayable($request) { return []; }
    private function getFinancialRatios($request) { return []; }

    private function getAnalyticsData($type, $period, $filters)
    {
        // Sample analytics data
        return [
            'type' => $type,
            'period' => $period,
            'data' => [
                'overview' => [
                    'total_revenue' => 125000,
                    'total_orders' => 450,
                    'total_customers' => 850,
                    'growth_rate' => 12.5,
                ],
                'trends' => [
                    ['date' => '2025-01-01', 'value' => 95000],
                    ['date' => '2025-02-01', 'value' => 105000],
                    ['date' => '2025-03-01', 'value' => 125000],
                ],
            ],
            'generated_at' => now()->toISOString(),
        ];
    }
}
