<?php

namespace App\Modules\Reports\Services;

use App\Modules\Sales\Models\Customer;
use App\Modules\Suppliers\Models\Supplier;
use App\Modules\Inventory\Models\Product;
use App\Modules\Accounting\Models\Invoice;
use App\Modules\Accounting\Models\Payment;
use App\Modules\HR\Models\Employee;
use App\Modules\HR\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BusinessIntelligenceService
{
    /**
     * Generate Executive Dashboard KPIs
     */
    public function generateExecutiveDashboard($startDate = null, $endDate = null)
    {
        $startDate = $startDate ? Carbon::parse($startDate) : now()->startOfMonth();
        $endDate = $endDate ? Carbon::parse($endDate) : now()->endOfMonth();
        
        return [
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ],
            'financial_kpis' => $this->getFinancialKPIs($startDate, $endDate),
            'sales_kpis' => $this->getSalesKPIs($startDate, $endDate),
            'inventory_kpis' => $this->getInventoryKPIs(),
            'hr_kpis' => $this->getHRKPIs($startDate, $endDate),
            'operational_kpis' => $this->getOperationalKPIs($startDate, $endDate),
        ];
    }

    /**
     * Get Financial KPIs
     */
    private function getFinancialKPIs($startDate, $endDate)
    {
        // Revenue metrics
        $totalRevenue = Invoice::sales()
                              ->whereBetween('issue_date', [$startDate, $endDate])
                              ->sum('total_amount');
        
        $paidRevenue = Invoice::sales()
                             ->whereBetween('issue_date', [$startDate, $endDate])
                             ->sum('paid_amount');
        
        $outstandingReceivables = Invoice::sales()
                                        ->where('status', '!=', 'paid')
                                        ->sum('balance_due');
        
        // Expense metrics
        $totalExpenses = Invoice::purchase()
                               ->whereBetween('issue_date', [$startDate, $endDate])
                               ->sum('total_amount');
        
        $outstandingPayables = Invoice::purchase()
                                     ->where('status', '!=', 'paid')
                                     ->sum('balance_due');
        
        // Cash flow metrics
        $cashInflow = Payment::receipts()
                            ->completed()
                            ->whereBetween('payment_date', [$startDate, $endDate])
                            ->sum('amount');
        
        $cashOutflow = Payment::payments()
                             ->completed()
                             ->whereBetween('payment_date', [$startDate, $endDate])
                             ->sum('amount');
        
        $netCashFlow = $cashInflow - $cashOutflow;
        
        return [
            'revenue' => [
                'total_revenue' => $totalRevenue,
                'paid_revenue' => $paidRevenue,
                'collection_rate' => $totalRevenue > 0 ? ($paidRevenue / $totalRevenue) * 100 : 0,
                'outstanding_receivables' => $outstandingReceivables,
            ],
            'expenses' => [
                'total_expenses' => $totalExpenses,
                'outstanding_payables' => $outstandingPayables,
            ],
            'profitability' => [
                'gross_profit' => $totalRevenue - $totalExpenses,
                'profit_margin' => $totalRevenue > 0 ? (($totalRevenue - $totalExpenses) / $totalRevenue) * 100 : 0,
            ],
            'cash_flow' => [
                'cash_inflow' => $cashInflow,
                'cash_outflow' => $cashOutflow,
                'net_cash_flow' => $netCashFlow,
            ],
        ];
    }

    /**
     * Get Sales KPIs
     */
    private function getSalesKPIs($startDate, $endDate)
    {
        $totalInvoices = Invoice::sales()
                               ->whereBetween('issue_date', [$startDate, $endDate])
                               ->count();
        
        $totalCustomers = Customer::active()->count();
        $newCustomers = Customer::whereBetween('created_at', [$startDate, $endDate])->count();
        
        $averageOrderValue = $totalInvoices > 0 ? 
            Invoice::sales()
                   ->whereBetween('issue_date', [$startDate, $endDate])
                   ->avg('total_amount') : 0;
        
        // Top customers by revenue
        $topCustomers = Invoice::sales()
                              ->whereBetween('issue_date', [$startDate, $endDate])
                              ->select('customer_id', DB::raw('SUM(total_amount) as total_revenue'))
                              ->with('customer')
                              ->groupBy('customer_id')
                              ->orderByDesc('total_revenue')
                              ->limit(5)
                              ->get();
        
        return [
            'sales_volume' => [
                'total_invoices' => $totalInvoices,
                'average_order_value' => $averageOrderValue,
            ],
            'customer_metrics' => [
                'total_customers' => $totalCustomers,
                'new_customers' => $newCustomers,
                'customer_growth_rate' => $totalCustomers > 0 ? ($newCustomers / $totalCustomers) * 100 : 0,
            ],
            'top_customers' => $topCustomers->map(function ($invoice) {
                return [
                    'customer_id' => $invoice->customer_id,
                    'customer_name' => $invoice->customer->name ?? 'Unknown',
                    'total_revenue' => $invoice->total_revenue,
                ];
            }),
        ];
    }

    /**
     * Get Inventory KPIs
     */
    private function getInventoryKPIs()
    {
        try {
            $totalProducts = Product::active()->count();

            // Check if inventory columns exist
            $hasInventoryColumns = \Schema::hasColumn('products', 'current_stock') &&
                                  \Schema::hasColumn('products', 'minimum_stock_level');

            if ($hasInventoryColumns) {
                $lowStockProducts = Product::active()
                                          ->whereRaw('current_stock <= minimum_stock_level')
                                          ->count();

                $totalInventoryValue = Product::active()
                                             ->selectRaw('SUM(current_stock * cost_price) as total_value')
                                             ->first()
                                             ->total_value ?? 0;

                $outOfStockProducts = Product::active()
                                            ->where('current_stock', '<=', 0)
                                            ->count();
            } else {
                $lowStockProducts = 0;
                $totalInventoryValue = 0;
                $outOfStockProducts = 0;
            }

            return [
                'inventory_levels' => [
                    'total_products' => $totalProducts,
                    'low_stock_products' => $lowStockProducts,
                    'out_of_stock_products' => $outOfStockProducts,
                    'stock_health_rate' => $totalProducts > 0 ?
                        (($totalProducts - $lowStockProducts - $outOfStockProducts) / $totalProducts) * 100 : 0,
                ],
                'inventory_value' => [
                    'total_inventory_value' => $totalInventoryValue,
                    'average_product_value' => $totalProducts > 0 ? $totalInventoryValue / $totalProducts : 0,
                ],
                'note' => $hasInventoryColumns ? null : 'Inventory tracking columns not available',
            ];
        } catch (\Exception $e) {
            return [
                'inventory_levels' => [
                    'total_products' => 0,
                    'low_stock_products' => 0,
                    'out_of_stock_products' => 0,
                    'stock_health_rate' => 0,
                ],
                'inventory_value' => [
                    'total_inventory_value' => 0,
                    'average_product_value' => 0,
                ],
                'error' => 'Inventory data not available',
            ];
        }
    }

    /**
     * Get HR KPIs
     */
    private function getHRKPIs($startDate, $endDate)
    {
        $totalEmployees = Employee::active()->count();
        $newHires = Employee::whereBetween('hire_date', [$startDate, $endDate])->count();
        $terminations = Employee::where('status', 'terminated')
                               ->whereBetween('termination_date', [$startDate, $endDate])
                               ->count();
        
        // Attendance metrics
        $totalAttendanceDays = Attendance::whereBetween('date', [$startDate, $endDate])->count();
        $presentDays = Attendance::whereBetween('date', [$startDate, $endDate])
                                 ->whereIn('status', ['present', 'late', 'overtime'])
                                 ->count();
        
        $attendanceRate = $totalAttendanceDays > 0 ? ($presentDays / $totalAttendanceDays) * 100 : 0;
        
        // Average salary
        $averageSalary = Employee::active()->avg('basic_salary');
        
        return [
            'workforce' => [
                'total_employees' => $totalEmployees,
                'new_hires' => $newHires,
                'terminations' => $terminations,
                'turnover_rate' => $totalEmployees > 0 ? ($terminations / $totalEmployees) * 100 : 0,
            ],
            'attendance' => [
                'attendance_rate' => $attendanceRate,
                'total_attendance_days' => $totalAttendanceDays,
                'present_days' => $presentDays,
            ],
            'compensation' => [
                'average_salary' => $averageSalary,
                'total_payroll_cost' => $totalEmployees * $averageSalary,
            ],
        ];
    }

    /**
     * Get Operational KPIs
     */
    private function getOperationalKPIs($startDate, $endDate)
    {
        // Invoice processing metrics
        $totalInvoices = Invoice::whereBetween('issue_date', [$startDate, $endDate])->count();
        $paidInvoices = Invoice::whereBetween('issue_date', [$startDate, $endDate])
                              ->where('status', 'paid')
                              ->count();
        
        $overdueInvoices = Invoice::overdue()->count();
        
        // Payment processing metrics
        $totalPayments = Payment::whereBetween('payment_date', [$startDate, $endDate])->count();
        $completedPayments = Payment::whereBetween('payment_date', [$startDate, $endDate])
                                   ->where('status', 'completed')
                                   ->count();
        
        return [
            'invoice_processing' => [
                'total_invoices' => $totalInvoices,
                'paid_invoices' => $paidInvoices,
                'payment_rate' => $totalInvoices > 0 ? ($paidInvoices / $totalInvoices) * 100 : 0,
                'overdue_invoices' => $overdueInvoices,
            ],
            'payment_processing' => [
                'total_payments' => $totalPayments,
                'completed_payments' => $completedPayments,
                'payment_success_rate' => $totalPayments > 0 ? ($completedPayments / $totalPayments) * 100 : 0,
            ],
        ];
    }

    /**
     * Generate Sales Analytics
     */
    public function generateSalesAnalytics($startDate = null, $endDate = null)
    {
        $startDate = $startDate ? Carbon::parse($startDate) : now()->startOfYear();
        $endDate = $endDate ? Carbon::parse($endDate) : now();
        
        // Monthly sales trend
        $monthlySales = Invoice::sales()
                              ->whereBetween('issue_date', [$startDate, $endDate])
                              ->selectRaw('YEAR(issue_date) as year, MONTH(issue_date) as month, SUM(total_amount) as total_sales, COUNT(*) as invoice_count')
                              ->groupByRaw('YEAR(issue_date), MONTH(issue_date)')
                              ->orderByRaw('YEAR(issue_date), MONTH(issue_date)')
                              ->get();
        
        // Customer analysis
        $customerAnalysis = Invoice::sales()
                                  ->whereBetween('issue_date', [$startDate, $endDate])
                                  ->select('customer_id', 
                                          DB::raw('COUNT(*) as order_count'),
                                          DB::raw('SUM(total_amount) as total_revenue'),
                                          DB::raw('AVG(total_amount) as avg_order_value'))
                                  ->with('customer')
                                  ->groupBy('customer_id')
                                  ->orderByDesc('total_revenue')
                                  ->get();
        
        return [
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ],
            'monthly_trend' => $monthlySales,
            'customer_analysis' => $customerAnalysis,
            'summary' => [
                'total_revenue' => $monthlySales->sum('total_sales'),
                'total_invoices' => $monthlySales->sum('invoice_count'),
                'average_monthly_sales' => $monthlySales->avg('total_sales'),
                'top_customer_revenue' => $customerAnalysis->first()?->total_revenue ?? 0,
            ],
        ];
    }
}
