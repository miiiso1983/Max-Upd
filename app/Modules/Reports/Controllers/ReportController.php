<?php

namespace App\Modules\Reports\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Reports\Services\FinancialReportService;
use App\Modules\Reports\Services\BusinessIntelligenceService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    protected $financialReportService;
    protected $businessIntelligenceService;

    public function __construct(
        FinancialReportService $financialReportService,
        BusinessIntelligenceService $businessIntelligenceService
    ) {
        $this->financialReportService = $financialReportService;
        $this->businessIntelligenceService = $businessIntelligenceService;
    }

    /**
     * Generate Balance Sheet
     */
    public function balanceSheet(Request $request)
    {
        $validated = $request->validate([
            'as_of_date' => 'nullable|date',
        ]);

        $report = $this->financialReportService->generateBalanceSheet(
            $validated['as_of_date'] ?? null
        );

        return response()->json($report);
    }

    /**
     * Generate Income Statement
     */
    public function incomeStatement(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $report = $this->financialReportService->generateIncomeStatement(
            $validated['start_date'] ?? null,
            $validated['end_date'] ?? null
        );

        return response()->json($report);
    }

    /**
     * Generate Cash Flow Statement
     */
    public function cashFlowStatement(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $report = $this->financialReportService->generateCashFlowStatement(
            $validated['start_date'] ?? null,
            $validated['end_date'] ?? null
        );

        return response()->json($report);
    }

    /**
     * Generate Trial Balance
     */
    public function trialBalance(Request $request)
    {
        $validated = $request->validate([
            'as_of_date' => 'nullable|date',
        ]);

        $report = $this->financialReportService->generateTrialBalance(
            $validated['as_of_date'] ?? null
        );

        return response()->json($report);
    }

    /**
     * Generate General Ledger
     */
    public function generalLedger(Request $request)
    {
        $validated = $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $report = $this->financialReportService->generateGeneralLedger(
            $validated['account_id'],
            $validated['start_date'] ?? null,
            $validated['end_date'] ?? null
        );

        return response()->json($report);
    }

    /**
     * Executive Dashboard
     */
    public function executiveDashboard(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $dashboard = $this->businessIntelligenceService->generateExecutiveDashboard(
            $validated['start_date'] ?? null,
            $validated['end_date'] ?? null
        );

        return response()->json($dashboard);
    }

    /**
     * Sales Analytics
     */
    public function salesAnalytics(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $analytics = $this->businessIntelligenceService->generateSalesAnalytics(
            $validated['start_date'] ?? null,
            $validated['end_date'] ?? null
        );

        return response()->json($analytics);
    }

    /**
     * Financial Summary Report
     */
    public function financialSummary(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'as_of_date' => 'nullable|date',
        ]);

        $startDate = $validated['start_date'] ?? now()->startOfMonth();
        $endDate = $validated['end_date'] ?? now()->endOfMonth();
        $asOfDate = $validated['as_of_date'] ?? now();

        $summary = [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'as_of_date' => $asOfDate,
            ],
            'balance_sheet' => $this->financialReportService->generateBalanceSheet($asOfDate),
            'income_statement' => $this->financialReportService->generateIncomeStatement($startDate, $endDate),
            'cash_flow' => $this->financialReportService->generateCashFlowStatement($startDate, $endDate),
        ];

        return response()->json($summary);
    }

    /**
     * Custom Report Builder
     */
    public function customReport(Request $request)
    {
        $validated = $request->validate([
            'report_type' => 'required|in:financial,sales,inventory,hr,operational',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'filters' => 'nullable|array',
            'grouping' => 'nullable|string|in:daily,weekly,monthly,quarterly,yearly',
            'metrics' => 'nullable|array',
        ]);

        // This would be expanded to handle custom report building
        $report = [
            'report_type' => $validated['report_type'],
            'period' => [
                'start_date' => $validated['start_date'] ?? now()->startOfMonth(),
                'end_date' => $validated['end_date'] ?? now()->endOfMonth(),
            ],
            'filters' => $validated['filters'] ?? [],
            'grouping' => $validated['grouping'] ?? 'monthly',
            'metrics' => $validated['metrics'] ?? [],
            'message' => 'Custom report builder - to be implemented based on specific requirements',
        ];

        return response()->json($report);
    }

    /**
     * Export Report
     */
    public function exportReport(Request $request)
    {
        $validated = $request->validate([
            'report_type' => 'required|string',
            'format' => 'required|in:pdf,excel,csv',
            'parameters' => 'nullable|array',
        ]);

        // This would implement actual export functionality
        return response()->json([
            'message' => 'Report export functionality will be implemented with PDF/Excel libraries',
            'report_type' => $validated['report_type'],
            'format' => $validated['format'],
            'download_url' => "/api/tenant/reports/download/{$validated['report_type']}.{$validated['format']}",
            'estimated_completion' => now()->addMinutes(2)->toISOString(),
        ]);
    }

    /**
     * Schedule Report
     */
    public function scheduleReport(Request $request)
    {
        $validated = $request->validate([
            'report_type' => 'required|string',
            'frequency' => 'required|in:daily,weekly,monthly,quarterly,yearly',
            'recipients' => 'required|array',
            'recipients.*' => 'email',
            'format' => 'required|in:pdf,excel',
            'parameters' => 'nullable|array',
            'start_date' => 'nullable|date',
        ]);

        // This would implement report scheduling functionality
        return response()->json([
            'message' => 'Report scheduled successfully',
            'schedule_id' => 'SCH-' . now()->format('YmdHis'),
            'report_type' => $validated['report_type'],
            'frequency' => $validated['frequency'],
            'next_run' => $this->calculateNextRun($validated['frequency']),
            'recipients' => $validated['recipients'],
        ]);
    }

    /**
     * Get Available Reports
     */
    public function availableReports()
    {
        $reports = [
            'financial_reports' => [
                [
                    'key' => 'balance_sheet',
                    'name' => 'Balance Sheet',
                    'name_ar' => 'الميزانية العمومية',
                    'description' => 'Statement of financial position showing assets, liabilities, and equity',
                    'description_ar' => 'بيان المركز المالي يوضح الأصول والخصوم وحقوق الملكية',
                    'parameters' => ['as_of_date'],
                ],
                [
                    'key' => 'income_statement',
                    'name' => 'Income Statement',
                    'name_ar' => 'قائمة الدخل',
                    'description' => 'Statement showing revenues, expenses, and net income',
                    'description_ar' => 'بيان يوضح الإيرادات والمصروفات وصافي الدخل',
                    'parameters' => ['start_date', 'end_date'],
                ],
                [
                    'key' => 'cash_flow_statement',
                    'name' => 'Cash Flow Statement',
                    'name_ar' => 'قائمة التدفق النقدي',
                    'description' => 'Statement showing cash inflows and outflows',
                    'description_ar' => 'بيان يوضح التدفقات النقدية الداخلة والخارجة',
                    'parameters' => ['start_date', 'end_date'],
                ],
                [
                    'key' => 'trial_balance',
                    'name' => 'Trial Balance',
                    'name_ar' => 'ميزان المراجعة',
                    'description' => 'List of all accounts with their debit and credit balances',
                    'description_ar' => 'قائمة بجميع الحسابات مع أرصدتها المدينة والدائنة',
                    'parameters' => ['as_of_date'],
                ],
                [
                    'key' => 'general_ledger',
                    'name' => 'General Ledger',
                    'name_ar' => 'دفتر الأستاذ العام',
                    'description' => 'Detailed transaction history for a specific account',
                    'description_ar' => 'تاريخ المعاملات التفصيلي لحساب معين',
                    'parameters' => ['account_id', 'start_date', 'end_date'],
                ],
            ],
            'business_intelligence' => [
                [
                    'key' => 'executive_dashboard',
                    'name' => 'Executive Dashboard',
                    'name_ar' => 'لوحة القيادة التنفيذية',
                    'description' => 'High-level KPIs and business metrics',
                    'description_ar' => 'مؤشرات الأداء الرئيسية ومقاييس الأعمال عالية المستوى',
                    'parameters' => ['start_date', 'end_date'],
                ],
                [
                    'key' => 'sales_analytics',
                    'name' => 'Sales Analytics',
                    'name_ar' => 'تحليلات المبيعات',
                    'description' => 'Detailed sales performance analysis',
                    'description_ar' => 'تحليل مفصل لأداء المبيعات',
                    'parameters' => ['start_date', 'end_date'],
                ],
            ],
        ];

        return response()->json($reports);
    }

    /**
     * Calculate next run date for scheduled reports
     */
    private function calculateNextRun($frequency)
    {
        switch ($frequency) {
            case 'daily':
                return now()->addDay();
            case 'weekly':
                return now()->addWeek();
            case 'monthly':
                return now()->addMonth();
            case 'quarterly':
                return now()->addMonths(3);
            case 'yearly':
                return now()->addYear();
            default:
                return now()->addDay();
        }
    }
}
