<?php

namespace App\Modules\Reports\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Sales\Models\Customer;
use App\Modules\Suppliers\Models\Supplier;
use App\Modules\Inventory\Models\Product;
use App\Modules\Accounting\Models\Invoice;
use App\Modules\Accounting\Models\Payment;
use App\Modules\Accounting\Models\Account;
use App\Modules\HR\Models\Employee;
use App\Modules\HR\Models\Attendance;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    /**
     * Main Dashboard Overview
     */
    public function overview(Request $request)
    {
        $period = $request->get('period', 'month'); // day, week, month, quarter, year
        $dates = $this->getPeriodDates($period);

        return response()->json([
            'period' => $period,
            'dates' => $dates,
            'financial_overview' => $this->getFinancialOverview($dates),
            'sales_overview' => $this->getSalesOverview($dates),
            'inventory_overview' => $this->getInventoryOverview(),
            'hr_overview' => $this->getHROverview($dates),
            'recent_activities' => $this->getRecentActivities(),
            'alerts' => $this->getSystemAlerts(),
        ]);
    }

    /**
     * Financial Dashboard
     */
    public function financial(Request $request)
    {
        $period = $request->get('period', 'month');
        $dates = $this->getPeriodDates($period);

        // Revenue trend
        $revenueTrend = $this->getRevenueTrend($dates, $period);
        
        // Cash flow
        $cashFlow = $this->getCashFlowData($dates);
        
        // Account balances
        $accountBalances = $this->getKeyAccountBalances();
        
        // Outstanding amounts
        $outstanding = $this->getOutstandingAmounts();

        return response()->json([
            'period' => $period,
            'dates' => $dates,
            'revenue_trend' => $revenueTrend,
            'cash_flow' => $cashFlow,
            'account_balances' => $accountBalances,
            'outstanding' => $outstanding,
            'financial_ratios' => $this->getFinancialRatios($dates),
        ]);
    }

    /**
     * Sales Dashboard
     */
    public function sales(Request $request)
    {
        $period = $request->get('period', 'month');
        $dates = $this->getPeriodDates($period);

        return response()->json([
            'period' => $period,
            'dates' => $dates,
            'sales_metrics' => $this->getSalesMetrics($dates),
            'top_customers' => $this->getTopCustomers($dates),
            'sales_trend' => $this->getSalesTrend($dates, $period),
            'customer_analytics' => $this->getCustomerAnalytics($dates),
            'product_performance' => $this->getProductPerformance($dates),
        ]);
    }

    /**
     * Inventory Dashboard
     */
    public function inventory(Request $request)
    {
        return response()->json([
            'inventory_summary' => $this->getInventorySummary(),
            'stock_alerts' => $this->getStockAlerts(),
            'top_products' => $this->getTopProducts(),
            'inventory_value' => $this->getInventoryValue(),
            'stock_movements' => $this->getRecentStockMovements(),
        ]);
    }

    /**
     * HR Dashboard
     */
    public function hr(Request $request)
    {
        $period = $request->get('period', 'month');
        $dates = $this->getPeriodDates($period);

        return response()->json([
            'period' => $period,
            'dates' => $dates,
            'workforce_summary' => $this->getWorkforceSummary(),
            'attendance_metrics' => $this->getAttendanceMetrics($dates),
            'department_breakdown' => $this->getDepartmentBreakdown(),
            'recent_hires' => $this->getRecentHires($dates),
            'upcoming_events' => $this->getUpcomingHREvents(),
        ]);
    }

    /**
     * Get period dates based on selection
     */
    private function getPeriodDates($period)
    {
        switch ($period) {
            case 'day':
                return [
                    'start' => now()->startOfDay(),
                    'end' => now()->endOfDay(),
                ];
            case 'week':
                return [
                    'start' => now()->startOfWeek(),
                    'end' => now()->endOfWeek(),
                ];
            case 'month':
                return [
                    'start' => now()->startOfMonth(),
                    'end' => now()->endOfMonth(),
                ];
            case 'quarter':
                return [
                    'start' => now()->startOfQuarter(),
                    'end' => now()->endOfQuarter(),
                ];
            case 'year':
                return [
                    'start' => now()->startOfYear(),
                    'end' => now()->endOfYear(),
                ];
            default:
                return [
                    'start' => now()->startOfMonth(),
                    'end' => now()->endOfMonth(),
                ];
        }
    }

    /**
     * Get financial overview
     */
    private function getFinancialOverview($dates)
    {
        $revenue = Invoice::sales()
                         ->whereBetween('issue_date', [$dates['start'], $dates['end']])
                         ->sum('total_amount');

        $expenses = Invoice::purchase()
                          ->whereBetween('issue_date', [$dates['start'], $dates['end']])
                          ->sum('total_amount');

        $profit = $revenue - $expenses;
        $profitMargin = $revenue > 0 ? ($profit / $revenue) * 100 : 0;

        $cashInflow = Payment::receipts()
                            ->completed()
                            ->whereBetween('payment_date', [$dates['start'], $dates['end']])
                            ->sum('amount');

        $cashOutflow = Payment::payments()
                             ->completed()
                             ->whereBetween('payment_date', [$dates['start'], $dates['end']])
                             ->sum('amount');

        return [
            'revenue' => $revenue,
            'expenses' => $expenses,
            'profit' => $profit,
            'profit_margin' => $profitMargin,
            'cash_inflow' => $cashInflow,
            'cash_outflow' => $cashOutflow,
            'net_cash_flow' => $cashInflow - $cashOutflow,
        ];
    }

    /**
     * Get sales overview
     */
    private function getSalesOverview($dates)
    {
        $totalSales = Invoice::sales()
                            ->whereBetween('issue_date', [$dates['start'], $dates['end']])
                            ->sum('total_amount');

        $invoiceCount = Invoice::sales()
                              ->whereBetween('issue_date', [$dates['start'], $dates['end']])
                              ->count();

        $averageOrderValue = $invoiceCount > 0 ? $totalSales / $invoiceCount : 0;

        $newCustomers = Customer::whereBetween('created_at', [$dates['start'], $dates['end']])
                               ->count();

        return [
            'total_sales' => $totalSales,
            'invoice_count' => $invoiceCount,
            'average_order_value' => $averageOrderValue,
            'new_customers' => $newCustomers,
        ];
    }

    /**
     * Get inventory overview
     */
    private function getInventoryOverview()
    {
        try {
            $totalProducts = Product::active()->count();

            // Check if inventory columns exist
            $hasInventoryColumns = \Schema::hasColumn('products', 'current_stock') &&
                                  \Schema::hasColumn('products', 'minimum_stock_level');

            if ($hasInventoryColumns) {
                $lowStockCount = Product::active()
                                       ->whereRaw('current_stock <= minimum_stock_level')
                                       ->count();
                $outOfStockCount = Product::active()
                                         ->where('current_stock', '<=', 0)
                                         ->count();

                $totalValue = Product::active()
                                    ->selectRaw('SUM(current_stock * cost_price) as total')
                                    ->first()
                                    ->total ?? 0;
            } else {
                $lowStockCount = 0;
                $outOfStockCount = 0;
                $totalValue = 0;
            }

            return [
                'total_products' => $totalProducts,
                'low_stock_count' => $lowStockCount,
                'out_of_stock_count' => $outOfStockCount,
                'total_inventory_value' => $totalValue,
                'note' => $hasInventoryColumns ? null : 'Inventory tracking not available',
            ];
        } catch (\Exception $e) {
            return [
                'total_products' => 0,
                'low_stock_count' => 0,
                'out_of_stock_count' => 0,
                'total_inventory_value' => 0,
                'error' => 'Inventory data not available',
            ];
        }
    }

    /**
     * Get HR overview
     */
    private function getHROverview($dates)
    {
        $totalEmployees = Employee::active()->count();
        $newHires = Employee::whereBetween('hire_date', [$dates['start'], $dates['end']])
                           ->count();

        $attendanceRate = $this->calculateAttendanceRate($dates);

        return [
            'total_employees' => $totalEmployees,
            'new_hires' => $newHires,
            'attendance_rate' => $attendanceRate,
        ];
    }

    /**
     * Get recent activities
     */
    private function getRecentActivities()
    {
        $activities = [];

        // Recent invoices
        $recentInvoices = Invoice::with(['customer', 'supplier'])
                                ->latest()
                                ->take(5)
                                ->get()
                                ->map(function ($invoice) {
                                    return [
                                        'type' => 'invoice',
                                        'description' => "Invoice {$invoice->invoice_number} created",
                                        'description_ar' => "تم إنشاء الفاتورة {$invoice->invoice_number}",
                                        'amount' => $invoice->total_amount,
                                        'date' => $invoice->created_at,
                                        'entity' => $invoice->customer->name ?? $invoice->supplier->name ?? 'Unknown',
                                    ];
                                });

        // Recent payments
        $recentPayments = Payment::with(['customer', 'supplier'])
                                ->latest()
                                ->take(5)
                                ->get()
                                ->map(function ($payment) {
                                    return [
                                        'type' => 'payment',
                                        'description' => "Payment {$payment->payment_number} processed",
                                        'description_ar' => "تم معالجة الدفع {$payment->payment_number}",
                                        'amount' => $payment->amount,
                                        'date' => $payment->created_at,
                                        'entity' => $payment->customer->name ?? $payment->supplier->name ?? 'Unknown',
                                    ];
                                });

        return $recentInvoices->concat($recentPayments)
                             ->sortByDesc('date')
                             ->take(10)
                             ->values();
    }

    /**
     * Get system alerts
     */
    private function getSystemAlerts()
    {
        $alerts = [];

        try {
            // Low stock alerts (only if inventory columns exist)
            if (Schema::hasColumn('products', 'current_stock') &&
                Schema::hasColumn('products', 'minimum_stock_level')) {
                $lowStockProducts = Product::active()
                                          ->whereRaw('current_stock <= minimum_stock_level')
                                          ->count();
                if ($lowStockProducts > 0) {
                    $alerts[] = [
                        'type' => 'warning',
                        'title' => 'Low Stock Alert',
                        'title_ar' => 'تنبيه نقص المخزون',
                        'message' => "{$lowStockProducts} products are running low on stock",
                        'message_ar' => "{$lowStockProducts} منتج ينفد من المخزون",
                        'action_url' => '/inventory/low-stock',
                    ];
                }
            }

            // Overdue invoices
            $overdueInvoices = Invoice::overdue()->count();
            if ($overdueInvoices > 0) {
                $alerts[] = [
                    'type' => 'danger',
                    'title' => 'Overdue Invoices',
                    'title_ar' => 'فواتير متأخرة',
                    'message' => "{$overdueInvoices} invoices are overdue",
                    'message_ar' => "{$overdueInvoices} فاتورة متأخرة",
                    'action_url' => '/accounting/invoices/overdue',
                ];
            }
        } catch (\Exception $e) {
            // If there are any errors, just return empty alerts
        }

        return $alerts;
    }

    /**
     * Calculate attendance rate
     */
    private function calculateAttendanceRate($dates)
    {
        $totalDays = Attendance::whereBetween('date', [$dates['start'], $dates['end']])
                              ->count();

        $presentDays = Attendance::whereBetween('date', [$dates['start'], $dates['end']])
                                 ->whereIn('status', ['present', 'late', 'overtime'])
                                 ->count();

        return $totalDays > 0 ? ($presentDays / $totalDays) * 100 : 0;
    }

    /**
     * Get revenue trend data
     */
    private function getRevenueTrend($dates, $period)
    {
        $groupBy = $period === 'day' ? 'DATE(issue_date)' : 
                  ($period === 'week' ? 'YEARWEEK(issue_date)' : 'DATE_FORMAT(issue_date, "%Y-%m")');

        return Invoice::sales()
                     ->whereBetween('issue_date', [$dates['start'], $dates['end']])
                     ->selectRaw("{$groupBy} as period, SUM(total_amount) as revenue")
                     ->groupByRaw($groupBy)
                     ->orderByRaw($groupBy)
                     ->get();
    }

    /**
     * Get cash flow data
     */
    private function getCashFlowData($dates)
    {
        $inflow = Payment::receipts()
                        ->completed()
                        ->whereBetween('payment_date', [$dates['start'], $dates['end']])
                        ->selectRaw('DATE(payment_date) as date, SUM(amount) as amount')
                        ->groupBy('date')
                        ->orderBy('date')
                        ->get();

        $outflow = Payment::payments()
                         ->completed()
                         ->whereBetween('payment_date', [$dates['start'], $dates['end']])
                         ->selectRaw('DATE(payment_date) as date, SUM(amount) as amount')
                         ->groupBy('date')
                         ->orderBy('date')
                         ->get();

        return [
            'inflow' => $inflow,
            'outflow' => $outflow,
        ];
    }

    /**
     * Get key account balances
     */
    private function getKeyAccountBalances()
    {
        $keyAccounts = ['1100', '1110', '1200', '2100']; // Cash, Bank, AR, AP
        
        return Account::whereIn('code', $keyAccounts)
                     ->get()
                     ->map(function ($account) {
                         return [
                             'code' => $account->code,
                             'name' => $account->name,
                             'name_ar' => $account->name_ar,
                             'balance' => $account->calculateBalance(),
                         ];
                     });
    }

    /**
     * Get outstanding amounts
     */
    private function getOutstandingAmounts()
    {
        return [
            'receivables' => Invoice::sales()->where('status', '!=', 'paid')->sum('balance_due'),
            'payables' => Invoice::purchase()->where('status', '!=', 'paid')->sum('balance_due'),
        ];
    }

    /**
     * Get financial ratios
     */
    private function getFinancialRatios($dates)
    {
        $revenue = Invoice::sales()
                         ->whereBetween('issue_date', [$dates['start'], $dates['end']])
                         ->sum('total_amount');

        $expenses = Invoice::purchase()
                          ->whereBetween('issue_date', [$dates['start'], $dates['end']])
                          ->sum('total_amount');

        return [
            'gross_profit_margin' => $revenue > 0 ? (($revenue - $expenses) / $revenue) * 100 : 0,
            'expense_ratio' => $revenue > 0 ? ($expenses / $revenue) * 100 : 0,
        ];
    }

    /**
     * Get sales metrics
     */
    private function getSalesMetrics($dates)
    {
        $totalSales = Invoice::sales()
                            ->whereBetween('issue_date', [$dates['start'], $dates['end']])
                            ->sum('total_amount');

        $invoiceCount = Invoice::sales()
                              ->whereBetween('issue_date', [$dates['start'], $dates['end']])
                              ->count();

        $paidInvoices = Invoice::sales()
                              ->whereBetween('issue_date', [$dates['start'], $dates['end']])
                              ->where('status', 'paid')
                              ->count();

        return [
            'total_sales' => $totalSales,
            'invoice_count' => $invoiceCount,
            'average_order_value' => $invoiceCount > 0 ? $totalSales / $invoiceCount : 0,
            'payment_rate' => $invoiceCount > 0 ? ($paidInvoices / $invoiceCount) * 100 : 0,
        ];
    }

    /**
     * Get top customers
     */
    private function getTopCustomers($dates)
    {
        return Invoice::sales()
                     ->whereBetween('issue_date', [$dates['start'], $dates['end']])
                     ->select('customer_id', DB::raw('SUM(total_amount) as total_revenue'))
                     ->with('customer')
                     ->groupBy('customer_id')
                     ->orderByDesc('total_revenue')
                     ->limit(5)
                     ->get()
                     ->map(function ($invoice) {
                         return [
                             'customer_name' => $invoice->customer->name ?? 'Unknown',
                             'total_revenue' => $invoice->total_revenue,
                         ];
                     });
    }

    /**
     * Get sales trend
     */
    private function getSalesTrend($dates, $period)
    {
        $groupBy = $period === 'day' ? 'DATE(issue_date)' : 'DATE_FORMAT(issue_date, "%Y-%m")';

        return Invoice::sales()
                     ->whereBetween('issue_date', [$dates['start'], $dates['end']])
                     ->selectRaw("{$groupBy} as period, SUM(total_amount) as sales, COUNT(*) as count")
                     ->groupByRaw($groupBy)
                     ->orderByRaw($groupBy)
                     ->get();
    }

    /**
     * Get customer analytics
     */
    private function getCustomerAnalytics($dates)
    {
        $totalCustomers = Customer::active()->count();
        $newCustomers = Customer::whereBetween('created_at', [$dates['start'], $dates['end']])->count();

        return [
            'total_customers' => $totalCustomers,
            'new_customers' => $newCustomers,
            'growth_rate' => $totalCustomers > 0 ? ($newCustomers / $totalCustomers) * 100 : 0,
        ];
    }

    /**
     * Get product performance
     */
    private function getProductPerformance($dates)
    {
        // This would require invoice items to be properly linked
        return [
            'message' => 'Product performance analytics - requires invoice items integration',
        ];
    }

    /**
     * Get inventory summary
     */
    private function getInventorySummary()
    {
        return [
            'total_products' => Product::active()->count(),
            'low_stock_count' => Product::active()->whereRaw('current_stock <= minimum_stock_level')->count(),
            'out_of_stock_count' => Product::active()->where('current_stock', '<=', 0)->count(),
            'total_value' => Product::active()->selectRaw('SUM(current_stock * cost_price) as total')->first()->total ?? 0,
        ];
    }

    /**
     * Get stock alerts
     */
    private function getStockAlerts()
    {
        return Product::active()
                     ->whereRaw('current_stock <= minimum_stock_level')
                     ->select('name', 'name_ar', 'current_stock', 'minimum_stock_level')
                     ->limit(10)
                     ->get();
    }

    /**
     * Get top products
     */
    private function getTopProducts()
    {
        return Product::active()
                     ->orderByDesc('current_stock')
                     ->select('name', 'name_ar', 'current_stock', 'selling_price')
                     ->limit(10)
                     ->get();
    }

    /**
     * Get inventory value
     */
    private function getInventoryValue()
    {
        return [
            'total_cost_value' => Product::active()->selectRaw('SUM(current_stock * cost_price) as total')->first()->total ?? 0,
            'total_selling_value' => Product::active()->selectRaw('SUM(current_stock * selling_price) as total')->first()->total ?? 0,
        ];
    }

    /**
     * Get recent stock movements
     */
    private function getRecentStockMovements()
    {
        // This would require stock movement tracking
        return [
            'message' => 'Recent stock movements - requires stock movement tracking implementation',
        ];
    }

    /**
     * Get workforce summary
     */
    private function getWorkforceSummary()
    {
        return [
            'total_employees' => Employee::active()->count(),
            'by_department' => Employee::active()
                                     ->with('department')
                                     ->get()
                                     ->groupBy('department.name')
                                     ->map(function ($employees, $department) {
                                         return [
                                             'department' => $department,
                                             'count' => $employees->count(),
                                         ];
                                     })
                                     ->values(),
        ];
    }

    /**
     * Get attendance metrics
     */
    private function getAttendanceMetrics($dates)
    {
        $totalDays = Attendance::whereBetween('date', [$dates['start'], $dates['end']])->count();
        $presentDays = Attendance::whereBetween('date', [$dates['start'], $dates['end']])
                                 ->whereIn('status', ['present', 'late', 'overtime'])
                                 ->count();

        return [
            'attendance_rate' => $totalDays > 0 ? ($presentDays / $totalDays) * 100 : 0,
            'total_days' => $totalDays,
            'present_days' => $presentDays,
            'absent_days' => $totalDays - $presentDays,
        ];
    }

    /**
     * Get department breakdown
     */
    private function getDepartmentBreakdown()
    {
        return Employee::active()
                      ->with('department')
                      ->get()
                      ->groupBy('department.name')
                      ->map(function ($employees, $department) {
                          return [
                              'department' => $department,
                              'employee_count' => $employees->count(),
                              'average_salary' => $employees->avg('basic_salary'),
                          ];
                      })
                      ->values();
    }

    /**
     * Get recent hires
     */
    private function getRecentHires($dates)
    {
        return Employee::whereBetween('hire_date', [$dates['start'], $dates['end']])
                      ->with(['department', 'position'])
                      ->select('first_name', 'last_name', 'hire_date', 'department_id', 'position_id')
                      ->latest('hire_date')
                      ->get();
    }

    /**
     * Get upcoming HR events
     */
    private function getUpcomingHREvents()
    {
        // This would include birthdays, work anniversaries, etc.
        return [
            'message' => 'Upcoming HR events - to be implemented with employee events tracking',
        ];
    }
}
