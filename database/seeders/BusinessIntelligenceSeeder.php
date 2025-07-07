<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\BusinessIntelligence\Models\Dashboard;
use App\Modules\BusinessIntelligence\Models\Widget;
use App\Modules\BusinessIntelligence\Models\Report;
use App\Modules\BusinessIntelligence\Models\KPI;

class BusinessIntelligenceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->createDashboards();
        $this->createKPIs();
        $this->createReports();
        $this->createWidgets();

        $this->command->info('Business Intelligence sample data created successfully!');
        $this->command->info('Created:');
        $this->command->info('- ' . Dashboard::count() . ' dashboards');
        $this->command->info('- ' . KPI::count() . ' KPIs');
        $this->command->info('- ' . Report::count() . ' reports');
        $this->command->info('- ' . Widget::count() . ' widgets');
    }

    private function createDashboards()
    {
        $dashboards = [
            [
                'name' => 'Executive Dashboard',
                'name_ar' => 'لوحة القيادة التنفيذية',
                'description' => 'High-level overview for executives and management',
                'description_ar' => 'نظرة عامة عالية المستوى للمديرين التنفيذيين والإدارة',
                'type' => Dashboard::TYPE_EXECUTIVE,
                'is_public' => true,
                'is_default' => true,
                'is_active' => true,
                'auto_refresh' => true,
                'refresh_interval' => 300,
                'created_by' => 1,
            ],
            [
                'name' => 'Sales Performance Dashboard',
                'name_ar' => 'لوحة أداء المبيعات',
                'description' => 'Sales metrics, trends, and performance analysis',
                'description_ar' => 'مقاييس المبيعات والاتجاهات وتحليل الأداء',
                'type' => Dashboard::TYPE_SALES,
                'is_public' => true,
                'is_default' => true,
                'is_active' => true,
                'auto_refresh' => true,
                'refresh_interval' => 600,
                'created_by' => 1,
            ],
            [
                'name' => 'Inventory Management Dashboard',
                'name_ar' => 'لوحة إدارة المخزون',
                'description' => 'Inventory levels, stock movements, and warehouse analytics',
                'description_ar' => 'مستويات المخزون وحركات المخزون وتحليلات المستودع',
                'type' => Dashboard::TYPE_INVENTORY,
                'is_public' => true,
                'is_default' => true,
                'is_active' => true,
                'auto_refresh' => true,
                'refresh_interval' => 300,
                'created_by' => 1,
            ],
            [
                'name' => 'Financial Overview Dashboard',
                'name_ar' => 'لوحة النظرة المالية العامة',
                'description' => 'Financial performance, cash flow, and profitability metrics',
                'description_ar' => 'الأداء المالي والتدفق النقدي ومقاييس الربحية',
                'type' => Dashboard::TYPE_FINANCIAL,
                'is_public' => true,
                'is_default' => true,
                'is_active' => true,
                'auto_refresh' => true,
                'refresh_interval' => 900,
                'created_by' => 1,
            ],
            [
                'name' => 'Operational Dashboard',
                'name_ar' => 'لوحة العمليات',
                'description' => 'Daily operations, productivity, and efficiency metrics',
                'description_ar' => 'العمليات اليومية ومقاييس الإنتاجية والكفاءة',
                'type' => Dashboard::TYPE_OPERATIONAL,
                'is_public' => true,
                'is_default' => false,
                'is_active' => true,
                'auto_refresh' => true,
                'refresh_interval' => 300,
                'created_by' => 1,
            ],
        ];

        foreach ($dashboards as $dashboardData) {
            Dashboard::create($dashboardData);
        }
    }

    private function createKPIs()
    {
        $kpis = [
            // Sales KPIs
            [
                'name' => 'Monthly Revenue',
                'name_ar' => 'الإيرادات الشهرية',
                'description' => 'Total revenue generated in the current month',
                'description_ar' => 'إجمالي الإيرادات المحققة في الشهر الحالي',
                'category' => KPI::CATEGORY_SALES,
                'metric_type' => KPI::METRIC_SUM,
                'target_value' => 150000,
                'current_value' => 125000,
                'previous_value' => 110000,
                'unit' => 'IQD',
                'unit_ar' => 'دينار عراقي',
                'format' => 'currency',
                'trend_direction' => KPI::TREND_UP,
                'status' => KPI::STATUS_GOOD,
                'threshold_green' => 140000,
                'threshold_yellow' => 120000,
                'threshold_red' => 100000,
                'frequency' => KPI::FREQUENCY_DAILY,
                'is_active' => true,
                'created_by' => 1,
            ],
            [
                'name' => 'Sales Growth Rate',
                'name_ar' => 'معدل نمو المبيعات',
                'description' => 'Month-over-month sales growth percentage',
                'description_ar' => 'نسبة نمو المبيعات شهرياً',
                'category' => KPI::CATEGORY_SALES,
                'metric_type' => KPI::METRIC_PERCENTAGE,
                'target_value' => 15,
                'current_value' => 12.5,
                'previous_value' => 8.2,
                'unit' => '%',
                'unit_ar' => '%',
                'format' => 'percentage',
                'trend_direction' => KPI::TREND_UP,
                'status' => KPI::STATUS_GOOD,
                'threshold_green' => 12,
                'threshold_yellow' => 8,
                'threshold_red' => 5,
                'frequency' => KPI::FREQUENCY_MONTHLY,
                'is_active' => true,
                'created_by' => 1,
            ],
            // Financial KPIs
            [
                'name' => 'Gross Profit Margin',
                'name_ar' => 'هامش الربح الإجمالي',
                'description' => 'Gross profit as percentage of revenue',
                'description_ar' => 'الربح الإجمالي كنسبة مئوية من الإيرادات',
                'category' => KPI::CATEGORY_FINANCIAL,
                'metric_type' => KPI::METRIC_PERCENTAGE,
                'target_value' => 40,
                'current_value' => 36,
                'previous_value' => 34,
                'unit' => '%',
                'unit_ar' => '%',
                'format' => 'percentage',
                'trend_direction' => KPI::TREND_UP,
                'status' => KPI::STATUS_GOOD,
                'threshold_green' => 35,
                'threshold_yellow' => 30,
                'threshold_red' => 25,
                'frequency' => KPI::FREQUENCY_MONTHLY,
                'is_active' => true,
                'created_by' => 1,
            ],
            [
                'name' => 'Cash Flow',
                'name_ar' => 'التدفق النقدي',
                'description' => 'Net cash flow for the current period',
                'description_ar' => 'صافي التدفق النقدي للفترة الحالية',
                'category' => KPI::CATEGORY_FINANCIAL,
                'metric_type' => KPI::METRIC_SUM,
                'target_value' => 25000,
                'current_value' => 18000,
                'previous_value' => 15000,
                'unit' => 'IQD',
                'unit_ar' => 'دينار عراقي',
                'format' => 'currency',
                'trend_direction' => KPI::TREND_UP,
                'status' => KPI::STATUS_WARNING,
                'threshold_green' => 20000,
                'threshold_yellow' => 15000,
                'threshold_red' => 10000,
                'frequency' => KPI::FREQUENCY_WEEKLY,
                'is_active' => true,
                'created_by' => 1,
            ],
            // Inventory KPIs
            [
                'name' => 'Inventory Turnover Rate',
                'name_ar' => 'معدل دوران المخزون',
                'description' => 'How quickly inventory is sold and replaced',
                'description_ar' => 'مدى سرعة بيع المخزون واستبداله',
                'category' => KPI::CATEGORY_INVENTORY,
                'metric_type' => KPI::METRIC_RATIO,
                'target_value' => 6,
                'current_value' => 4.5,
                'previous_value' => 4.2,
                'unit' => 'times/year',
                'unit_ar' => 'مرة/سنة',
                'format' => 'decimal',
                'trend_direction' => KPI::TREND_UP,
                'status' => KPI::STATUS_WARNING,
                'threshold_green' => 5.5,
                'threshold_yellow' => 4.5,
                'threshold_red' => 3.5,
                'frequency' => KPI::FREQUENCY_MONTHLY,
                'is_active' => true,
                'created_by' => 1,
            ],
            [
                'name' => 'Stock Accuracy',
                'name_ar' => 'دقة المخزون',
                'description' => 'Percentage of accurate stock counts',
                'description_ar' => 'نسبة دقة عد المخزون',
                'category' => KPI::CATEGORY_INVENTORY,
                'metric_type' => KPI::METRIC_PERCENTAGE,
                'target_value' => 98,
                'current_value' => 94.8,
                'previous_value' => 93.2,
                'unit' => '%',
                'unit_ar' => '%',
                'format' => 'percentage',
                'trend_direction' => KPI::TREND_UP,
                'status' => KPI::STATUS_GOOD,
                'threshold_green' => 95,
                'threshold_yellow' => 90,
                'threshold_red' => 85,
                'frequency' => KPI::FREQUENCY_WEEKLY,
                'is_active' => true,
                'created_by' => 1,
            ],
            // Customer KPIs
            [
                'name' => 'Customer Satisfaction Score',
                'name_ar' => 'نقاط رضا العملاء',
                'description' => 'Average customer satisfaction rating',
                'description_ar' => 'متوسط تقييم رضا العملاء',
                'category' => KPI::CATEGORY_CUSTOMER,
                'metric_type' => KPI::METRIC_AVERAGE,
                'target_value' => 4.5,
                'current_value' => 4.2,
                'previous_value' => 4.0,
                'unit' => '/5',
                'unit_ar' => '/5',
                'format' => 'decimal',
                'trend_direction' => KPI::TREND_UP,
                'status' => KPI::STATUS_GOOD,
                'threshold_green' => 4.2,
                'threshold_yellow' => 3.8,
                'threshold_red' => 3.5,
                'frequency' => KPI::FREQUENCY_WEEKLY,
                'is_active' => true,
                'created_by' => 1,
            ],
            // Operational KPIs
            [
                'name' => 'Order Fulfillment Rate',
                'name_ar' => 'معدل تنفيذ الطلبات',
                'description' => 'Percentage of orders fulfilled on time',
                'description_ar' => 'نسبة الطلبات المنفذة في الوقت المحدد',
                'category' => KPI::CATEGORY_OPERATIONAL,
                'metric_type' => KPI::METRIC_PERCENTAGE,
                'target_value' => 98,
                'current_value' => 96.5,
                'previous_value' => 95.8,
                'unit' => '%',
                'unit_ar' => '%',
                'format' => 'percentage',
                'trend_direction' => KPI::TREND_UP,
                'status' => KPI::STATUS_GOOD,
                'threshold_green' => 95,
                'threshold_yellow' => 90,
                'threshold_red' => 85,
                'frequency' => KPI::FREQUENCY_DAILY,
                'is_active' => true,
                'created_by' => 1,
            ],
        ];

        foreach ($kpis as $kpiData) {
            KPI::create($kpiData);
        }
    }

    private function createReports()
    {
        $reports = [
            [
                'name' => 'Sales Performance Report',
                'name_ar' => 'تقرير أداء المبيعات',
                'description' => 'Comprehensive sales analysis and performance metrics',
                'description_ar' => 'تحليل شامل للمبيعات ومقاييس الأداء',
                'type' => Report::TYPE_ANALYTICAL,
                'category' => Report::CATEGORY_SALES,
                'format' => Report::FORMAT_PDF,
                'is_public' => true,
                'is_active' => true,
                'created_by' => 1,
            ],
            [
                'name' => 'Inventory Status Report',
                'name_ar' => 'تقرير حالة المخزون',
                'description' => 'Current inventory levels, stock movements, and alerts',
                'description_ar' => 'مستويات المخزون الحالية وحركات المخزون والتنبيهات',
                'type' => Report::TYPE_SUMMARY,
                'category' => Report::CATEGORY_INVENTORY,
                'format' => Report::FORMAT_EXCEL,
                'is_public' => true,
                'is_active' => true,
                'created_by' => 1,
            ],
            [
                'name' => 'Financial Summary Report',
                'name_ar' => 'تقرير الملخص المالي',
                'description' => 'Financial performance, profit & loss, and cash flow',
                'description_ar' => 'الأداء المالي والربح والخسارة والتدفق النقدي',
                'type' => Report::TYPE_SUMMARY,
                'category' => Report::CATEGORY_FINANCIAL,
                'format' => Report::FORMAT_PDF,
                'is_public' => true,
                'is_active' => true,
                'created_by' => 1,
            ],
            [
                'name' => 'Customer Analysis Report',
                'name_ar' => 'تقرير تحليل العملاء',
                'description' => 'Customer behavior, satisfaction, and retention analysis',
                'description_ar' => 'تحليل سلوك العملاء والرضا والاحتفاظ',
                'type' => Report::TYPE_ANALYTICAL,
                'category' => Report::CATEGORY_CRM,
                'format' => Report::FORMAT_HTML,
                'is_public' => true,
                'is_active' => true,
                'created_by' => 1,
            ],
            [
                'name' => 'Operational Efficiency Report',
                'name_ar' => 'تقرير الكفاءة التشغيلية',
                'description' => 'Operational metrics, productivity, and efficiency analysis',
                'description_ar' => 'المقاييس التشغيلية وتحليل الإنتاجية والكفاءة',
                'type' => Report::TYPE_DASHBOARD,
                'category' => Report::CATEGORY_OPERATIONAL,
                'format' => Report::FORMAT_HTML,
                'is_public' => true,
                'is_active' => true,
                'created_by' => 1,
            ],
        ];

        foreach ($reports as $reportData) {
            Report::create($reportData);
        }
    }

    private function createWidgets()
    {
        $dashboards = Dashboard::all();
        
        foreach ($dashboards as $dashboard) {
            $this->createWidgetsForDashboard($dashboard);
        }
    }

    private function createWidgetsForDashboard($dashboard)
    {
        $widgets = [];
        
        switch ($dashboard->type) {
            case Dashboard::TYPE_EXECUTIVE:
                $widgets = $this->getExecutiveWidgets($dashboard->id);
                break;
            case Dashboard::TYPE_SALES:
                $widgets = $this->getSalesWidgets($dashboard->id);
                break;
            case Dashboard::TYPE_INVENTORY:
                $widgets = $this->getInventoryWidgets($dashboard->id);
                break;
            case Dashboard::TYPE_FINANCIAL:
                $widgets = $this->getFinancialWidgets($dashboard->id);
                break;
            default:
                $widgets = $this->getDefaultWidgets($dashboard->id);
                break;
        }
        
        foreach ($widgets as $widgetData) {
            Widget::create($widgetData);
        }
    }

    private function getExecutiveWidgets($dashboardId)
    {
        return [
            [
                'dashboard_id' => $dashboardId,
                'name' => 'Revenue KPI',
                'name_ar' => 'مؤشر الإيرادات',
                'type' => Widget::TYPE_KPI,
                'data_source' => Widget::SOURCE_SALES,
                'config' => ['metric' => 'revenue', 'format' => 'currency'],
                'position' => ['x' => 0, 'y' => 0],
                'size' => ['width' => 3, 'height' => 2],
                'is_active' => true,
                'created_by' => 1,
            ],
            [
                'dashboard_id' => $dashboardId,
                'name' => 'Sales Trend',
                'name_ar' => 'اتجاه المبيعات',
                'type' => Widget::TYPE_CHART_LINE,
                'data_source' => Widget::SOURCE_SALES,
                'config' => ['period' => '30d', 'metric' => 'daily_sales'],
                'position' => ['x' => 3, 'y' => 0],
                'size' => ['width' => 6, 'height' => 4],
                'is_active' => true,
                'created_by' => 1,
            ],
        ];
    }

    private function getSalesWidgets($dashboardId)
    {
        return [
            [
                'dashboard_id' => $dashboardId,
                'name' => 'Sales by Product',
                'name_ar' => 'المبيعات حسب المنتج',
                'type' => Widget::TYPE_CHART_BAR,
                'data_source' => Widget::SOURCE_SALES,
                'config' => ['groupBy' => 'product', 'metric' => 'revenue'],
                'position' => ['x' => 0, 'y' => 0],
                'size' => ['width' => 6, 'height' => 4],
                'is_active' => true,
                'created_by' => 1,
            ],
        ];
    }

    private function getInventoryWidgets($dashboardId)
    {
        return [
            [
                'dashboard_id' => $dashboardId,
                'name' => 'Stock Levels',
                'name_ar' => 'مستويات المخزون',
                'type' => Widget::TYPE_GAUGE,
                'data_source' => Widget::SOURCE_INVENTORY,
                'config' => ['metric' => 'stock_level', 'threshold' => 'reorder_point'],
                'position' => ['x' => 0, 'y' => 0],
                'size' => ['width' => 4, 'height' => 3],
                'is_active' => true,
                'created_by' => 1,
            ],
        ];
    }

    private function getFinancialWidgets($dashboardId)
    {
        return [
            [
                'dashboard_id' => $dashboardId,
                'name' => 'Profit Margin',
                'name_ar' => 'هامش الربح',
                'type' => Widget::TYPE_PROGRESS,
                'data_source' => Widget::SOURCE_FINANCIAL,
                'config' => ['metric' => 'profit_margin', 'target' => 40],
                'position' => ['x' => 0, 'y' => 0],
                'size' => ['width' => 4, 'height' => 2],
                'is_active' => true,
                'created_by' => 1,
            ],
        ];
    }

    private function getDefaultWidgets($dashboardId)
    {
        return [
            [
                'dashboard_id' => $dashboardId,
                'name' => 'Sample Widget',
                'name_ar' => 'عنصر واجهة عينة',
                'type' => Widget::TYPE_KPI,
                'data_source' => Widget::SOURCE_CUSTOM,
                'config' => ['metric' => 'sample'],
                'position' => ['x' => 0, 'y' => 0],
                'size' => ['width' => 3, 'height' => 2],
                'is_active' => true,
                'created_by' => 1,
            ],
        ];
    }
}
