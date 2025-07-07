<?php

namespace App\Modules\Reports\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

class FinancialReportsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display financial reports dashboard
     */
    public function index(Request $request)
    {
        $stats = [
            'total_revenue' => 125000000, // IQD
            'total_expenses' => 85000000,
            'net_profit' => 40000000,
            'profit_margin' => 32.0,
            'cash_flow' => 15000000,
            'accounts_receivable' => 22000000,
            'accounts_payable' => 18000000,
            'current_ratio' => 1.85,
        ];

        $monthlyData = [
            'labels' => ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو'],
            'revenue' => [18000000, 19500000, 21000000, 20500000, 22000000, 24000000],
            'expenses' => [12000000, 13500000, 14000000, 13800000, 15000000, 16700000],
            'profit' => [6000000, 6000000, 7000000, 6700000, 7000000, 7300000],
        ];

        $expenseCategories = [
            ['category' => 'الرواتب والأجور', 'amount' => 35000000, 'percentage' => 41.2],
            ['category' => 'تكلفة البضائع', 'amount' => 25000000, 'percentage' => 29.4],
            ['category' => 'الإيجار والمرافق', 'amount' => 8000000, 'percentage' => 9.4],
            ['category' => 'التسويق والإعلان', 'amount' => 6000000, 'percentage' => 7.1],
            ['category' => 'مصاريف إدارية', 'amount' => 5000000, 'percentage' => 5.9],
            ['category' => 'أخرى', 'amount' => 6000000, 'percentage' => 7.0],
        ];

        $revenueStreams = [
            ['stream' => 'مبيعات المنتجات', 'amount' => 85000000, 'percentage' => 68.0],
            ['stream' => 'الخدمات', 'amount' => 25000000, 'percentage' => 20.0],
            ['stream' => 'العمولات', 'amount' => 10000000, 'percentage' => 8.0],
            ['stream' => 'أخرى', 'amount' => 5000000, 'percentage' => 4.0],
        ];

        if ($request->expectsJson()) {
            return response()->json([
                'stats' => $stats,
                'monthly_data' => $monthlyData,
                'expense_categories' => $expenseCategories,
                'revenue_streams' => $revenueStreams,
            ]);
        }

        return view('reports.financial.index', compact('stats', 'monthlyData', 'expenseCategories', 'revenueStreams'));
    }

    /**
     * Profit & Loss Statement
     */
    public function profitLoss(Request $request)
    {
        $period = $request->get('period', 'current_month');
        
        $data = [
            'period' => $this->getPeriodLabel($period),
            'revenue' => [
                'sales_revenue' => 85000000,
                'service_revenue' => 25000000,
                'other_revenue' => 5000000,
                'total_revenue' => 115000000,
            ],
            'cost_of_goods_sold' => [
                'materials' => 25000000,
                'labor' => 15000000,
                'overhead' => 8000000,
                'total_cogs' => 48000000,
            ],
            'gross_profit' => 67000000,
            'operating_expenses' => [
                'salaries' => 20000000,
                'rent' => 5000000,
                'utilities' => 3000000,
                'marketing' => 6000000,
                'administrative' => 5000000,
                'depreciation' => 2000000,
                'total_operating' => 41000000,
            ],
            'operating_income' => 26000000,
            'other_income' => [
                'interest_income' => 500000,
                'investment_income' => 1000000,
                'total_other' => 1500000,
            ],
            'other_expenses' => [
                'interest_expense' => 800000,
                'tax_expense' => 4000000,
                'total_other_exp' => 4800000,
            ],
            'net_income' => 22700000,
        ];

        if ($request->expectsJson()) {
            return response()->json(['profit_loss' => $data]);
        }

        return view('reports.financial.profit-loss', compact('data'));
    }

    /**
     * Balance Sheet
     */
    public function balanceSheet(Request $request)
    {
        $data = [
            'as_of_date' => now()->format('Y-m-d'),
            'assets' => [
                'current_assets' => [
                    'cash' => 15000000,
                    'accounts_receivable' => 22000000,
                    'inventory' => 35000000,
                    'prepaid_expenses' => 3000000,
                    'total_current' => 75000000,
                ],
                'fixed_assets' => [
                    'equipment' => 25000000,
                    'furniture' => 8000000,
                    'vehicles' => 12000000,
                    'accumulated_depreciation' => -8000000,
                    'total_fixed' => 37000000,
                ],
                'total_assets' => 112000000,
            ],
            'liabilities' => [
                'current_liabilities' => [
                    'accounts_payable' => 18000000,
                    'accrued_expenses' => 5000000,
                    'short_term_debt' => 7000000,
                    'total_current' => 30000000,
                ],
                'long_term_liabilities' => [
                    'long_term_debt' => 20000000,
                    'total_long_term' => 20000000,
                ],
                'total_liabilities' => 50000000,
            ],
            'equity' => [
                'capital' => 40000000,
                'retained_earnings' => 22000000,
                'total_equity' => 62000000,
            ],
            'total_liabilities_equity' => 112000000,
        ];

        if ($request->expectsJson()) {
            return response()->json(['balance_sheet' => $data]);
        }

        return view('reports.financial.balance-sheet', compact('data'));
    }

    /**
     * Cash Flow Statement
     */
    public function cashFlow(Request $request)
    {
        $data = [
            'period' => $this->getPeriodLabel($request->get('period', 'current_month')),
            'operating_activities' => [
                'net_income' => 22700000,
                'depreciation' => 2000000,
                'accounts_receivable_change' => -3000000,
                'inventory_change' => -5000000,
                'accounts_payable_change' => 2000000,
                'total_operating' => 18700000,
            ],
            'investing_activities' => [
                'equipment_purchase' => -5000000,
                'investment_purchase' => -2000000,
                'total_investing' => -7000000,
            ],
            'financing_activities' => [
                'loan_proceeds' => 10000000,
                'loan_payments' => -3000000,
                'dividends_paid' => -5000000,
                'total_financing' => 2000000,
            ],
            'net_cash_flow' => 13700000,
            'beginning_cash' => 1300000,
            'ending_cash' => 15000000,
        ];

        if ($request->expectsJson()) {
            return response()->json(['cash_flow' => $data]);
        }

        return view('reports.financial.cash-flow', compact('data'));
    }

    /**
     * Financial Ratios Analysis
     */
    public function ratios(Request $request)
    {
        $ratios = [
            'liquidity_ratios' => [
                'current_ratio' => ['value' => 2.5, 'benchmark' => 2.0, 'status' => 'good'],
                'quick_ratio' => ['value' => 1.3, 'benchmark' => 1.0, 'status' => 'good'],
                'cash_ratio' => ['value' => 0.5, 'benchmark' => 0.2, 'status' => 'excellent'],
            ],
            'profitability_ratios' => [
                'gross_profit_margin' => ['value' => 58.3, 'benchmark' => 50.0, 'status' => 'good'],
                'net_profit_margin' => ['value' => 19.7, 'benchmark' => 15.0, 'status' => 'excellent'],
                'return_on_assets' => ['value' => 20.3, 'benchmark' => 15.0, 'status' => 'good'],
                'return_on_equity' => ['value' => 36.6, 'benchmark' => 20.0, 'status' => 'excellent'],
            ],
            'efficiency_ratios' => [
                'inventory_turnover' => ['value' => 2.7, 'benchmark' => 3.0, 'status' => 'average'],
                'receivables_turnover' => ['value' => 5.2, 'benchmark' => 6.0, 'status' => 'average'],
                'asset_turnover' => ['value' => 1.03, 'benchmark' => 1.0, 'status' => 'good'],
            ],
            'leverage_ratios' => [
                'debt_to_equity' => ['value' => 0.81, 'benchmark' => 1.0, 'status' => 'good'],
                'debt_to_assets' => ['value' => 0.45, 'benchmark' => 0.5, 'status' => 'good'],
                'interest_coverage' => ['value' => 32.5, 'benchmark' => 5.0, 'status' => 'excellent'],
            ],
        ];

        if ($request->expectsJson()) {
            return response()->json(['ratios' => $ratios]);
        }

        return view('reports.financial.ratios', compact('ratios'));
    }

    /**
     * Budget vs Actual Report
     */
    public function budgetVsActual(Request $request)
    {
        $data = [
            'period' => $this->getPeriodLabel($request->get('period', 'current_month')),
            'revenue' => [
                'budget' => 120000000,
                'actual' => 115000000,
                'variance' => -5000000,
                'variance_percent' => -4.2,
            ],
            'expenses' => [
                'budget' => 90000000,
                'actual' => 85000000,
                'variance' => -5000000,
                'variance_percent' => -5.6,
            ],
            'net_income' => [
                'budget' => 30000000,
                'actual' => 30000000,
                'variance' => 0,
                'variance_percent' => 0.0,
            ],
            'categories' => [
                [
                    'category' => 'الإيرادات',
                    'budget' => 120000000,
                    'actual' => 115000000,
                    'variance' => -5000000,
                    'variance_percent' => -4.2,
                ],
                [
                    'category' => 'تكلفة البضائع',
                    'budget' => 50000000,
                    'actual' => 48000000,
                    'variance' => -2000000,
                    'variance_percent' => -4.0,
                ],
                [
                    'category' => 'المصاريف التشغيلية',
                    'budget' => 40000000,
                    'actual' => 37000000,
                    'variance' => -3000000,
                    'variance_percent' => -7.5,
                ],
            ],
        ];

        if ($request->expectsJson()) {
            return response()->json(['budget_vs_actual' => $data]);
        }

        return view('reports.financial.budget-vs-actual', compact('data'));
    }

    /**
     * Get period label
     */
    private function getPeriodLabel($period)
    {
        switch ($period) {
            case 'current_month':
                return 'الشهر الحالي - ' . now()->format('F Y');
            case 'last_month':
                return 'الشهر الماضي - ' . now()->subMonth()->format('F Y');
            case 'current_quarter':
                return 'الربع الحالي';
            case 'current_year':
                return 'السنة الحالية - ' . now()->format('Y');
            default:
                return 'الشهر الحالي';
        }
    }
}
