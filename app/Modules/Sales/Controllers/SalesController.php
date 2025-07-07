<?php

namespace App\Modules\Sales\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Sales\Models\Customer;
use App\Modules\Sales\Models\SalesOrder;
use App\Modules\Sales\Models\Invoice;
use App\Modules\Sales\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesController extends Controller
{
    /**
     * Display sales dashboard page
     */
    public function index()
    {
        return view('sales.index');
    }

    /**
     * Get sales dashboard data
     */
    public function dashboard(Request $request)
    {
        $period = $request->get('period', 'month');

        // Calculate date range based on period
        switch ($period) {
            case 'day':
                $startDate = now()->startOfDay();
                $endDate = now()->endOfDay();
                break;
            case 'week':
                $startDate = now()->startOfWeek();
                $endDate = now()->endOfWeek();
                break;
            case 'year':
                $startDate = now()->startOfYear();
                $endDate = now()->endOfYear();
                break;
            default: // month
                $startDate = now()->startOfMonth();
                $endDate = now()->endOfMonth();
                break;
        }

        $stats = [
            'total_sales' => Invoice::where('status', 'paid')
                                  ->whereBetween('invoice_date', [$startDate, $endDate])
                                  ->sum('total_amount'),
            'total_orders' => SalesOrder::whereBetween('order_date', [$startDate, $endDate])->count(),
            'pending_invoices' => Invoice::where('status', 'sent')->count(),
            'average_order_value' => SalesOrder::whereBetween('order_date', [$startDate, $endDate])
                                              ->avg('total_amount') ?? 0,
        ];

        $recentOrders = SalesOrder::with(['customer'])
                                 ->latest()
                                 ->take(5)
                                 ->get();

        $recentInvoices = Invoice::with(['customer'])
                                ->latest()
                                ->take(5)
                                ->get();

        $topCustomers = Customer::select('customers.*')
                              ->selectRaw('SUM(sales_orders.total_amount) as total_sales')
                              ->join('sales_orders', 'customers.id', '=', 'sales_orders.customer_id')
                              ->whereBetween('sales_orders.order_date', [$startDate, $endDate])
                              ->groupBy('customers.id')
                              ->orderByDesc('total_sales')
                              ->take(5)
                              ->get();

        // Return JSON for API requests
        if ($request->expectsJson()) {
            return response()->json([
                'stats' => $stats,
                'recent_orders' => $recentOrders,
                'recent_invoices' => $recentInvoices,
                'top_customers' => $topCustomers,
            ]);
        }

        // Return view for web requests
        return view('sales.dashboard', compact(
            'stats',
            'recentOrders',
            'recentInvoices',
            'topCustomers',
            'period'
        ));
    }

    /**
     * Get analytics summary
     */
    public function analyticsSummary(Request $request)
    {
        $period = $request->get('period', 'month'); // day, week, month, year
        $startDate = $this->getStartDate($period);
        $endDate = now();

        $stats = [
            'total_sales' => $this->getTotalSales($startDate, $endDate),
            'total_orders' => $this->getTotalOrders($startDate, $endDate),
            'total_customers' => Customer::active()->count(),
            'pending_orders' => SalesOrder::byStatus('pending')->count(),
            'confirmed_orders' => SalesOrder::byStatus('confirmed')->count(),
            'pending_invoices' => Invoice::byStatus('sent')->count(),
            'overdue_invoices' => Invoice::overdue()->count(),
            'total_outstanding' => $this->getTotalOutstanding(),
            'average_order_value' => $this->getAverageOrderValue($startDate, $endDate),
        ];

        $recentOrders = SalesOrder::with(['customer', 'salesRep'])
                                 ->latest()
                                 ->take(10)
                                 ->get();

        $recentInvoices = Invoice::with(['customer'])
                                ->latest()
                                ->take(10)
                                ->get();

        $topCustomers = $this->getTopCustomers($startDate, $endDate);
        $salesTrend = $this->getSalesTrend($period);
        $paymentMethodStats = $this->getPaymentMethodStats($startDate, $endDate);

        return response()->json([
            'stats' => $stats,
            'recent_orders' => $recentOrders,
            'recent_invoices' => $recentInvoices,
            'top_customers' => $topCustomers,
            'sales_trend' => $salesTrend,
            'payment_method_stats' => $paymentMethodStats,
        ]);
    }

    /**
     * Get sales overview
     */
    public function overview(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now());

        $salesData = [
            'daily_sales' => $this->getDailySales($startDate, $endDate),
            'monthly_sales' => $this->getMonthlySales(),
            'sales_by_customer_type' => $this->getSalesByCustomerType($startDate, $endDate),
            'sales_by_payment_method' => $this->getSalesByPaymentMethod($startDate, $endDate),
            'top_selling_products' => $this->getTopSellingProducts($startDate, $endDate),
        ];

        return response()->json($salesData);
    }

    /**
     * Get sales reports
     */
    public function reports(Request $request)
    {
        $type = $request->get('type', 'summary');
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now());

        switch ($type) {
            case 'summary':
                return $this->getSalesSummaryReport($startDate, $endDate);
            case 'detailed':
                return $this->getDetailedSalesReport($startDate, $endDate);
            case 'customer':
                return $this->getCustomerSalesReport($startDate, $endDate);
            case 'product':
                return $this->getProductSalesReport($startDate, $endDate);
            default:
                return response()->json(['error' => 'Invalid report type'], 400);
        }
    }

    /**
     * Get alerts and notifications
     */
    public function alerts()
    {
        $alerts = [];

        // Overdue invoices
        $overdueInvoices = Invoice::overdue()->with('customer')->get();
        foreach ($overdueInvoices as $invoice) {
            $alerts[] = [
                'type' => 'danger',
                'title' => 'Overdue Invoice',
                'message' => "Invoice {$invoice->invoice_number} for {$invoice->customer->name} is overdue",
                'invoice_id' => $invoice->id,
                'days_overdue' => now()->diffInDays($invoice->due_date),
                'amount' => $invoice->balance_due,
                'created_at' => $invoice->due_date,
            ];
        }

        // Customers exceeding credit limit
        $customersOverLimit = Customer::exceededCreditLimit()->get();
        foreach ($customersOverLimit as $customer) {
            $alerts[] = [
                'type' => 'warning',
                'title' => 'Credit Limit Exceeded',
                'message' => "Customer {$customer->name} has exceeded their credit limit",
                'customer_id' => $customer->id,
                'credit_limit' => $customer->credit_limit,
                'outstanding_balance' => $customer->getOutstandingBalance(),
                'created_at' => now(),
            ];
        }

        // Pending orders requiring attention
        $pendingOrders = SalesOrder::byStatus('pending')
                                  ->where('created_at', '<', now()->subDays(2))
                                  ->with('customer')
                                  ->get();
        foreach ($pendingOrders as $order) {
            $alerts[] = [
                'type' => 'info',
                'title' => 'Pending Order',
                'message' => "Order {$order->order_number} has been pending for more than 2 days",
                'order_id' => $order->id,
                'customer' => $order->customer->name,
                'amount' => $order->total_amount,
                'created_at' => $order->created_at,
            ];
        }

        return response()->json([
            'alerts' => collect($alerts)->sortByDesc('created_at')->values()
        ]);
    }

    /**
     * Get start date based on period
     */
    private function getStartDate($period)
    {
        switch ($period) {
            case 'day':
                return now()->startOfDay();
            case 'week':
                return now()->startOfWeek();
            case 'month':
                return now()->startOfMonth();
            case 'year':
                return now()->startOfYear();
            default:
                return now()->startOfMonth();
        }
    }

    /**
     * Get total sales amount
     */
    private function getTotalSales($startDate, $endDate)
    {
        return Invoice::byStatus('paid')
                     ->whereBetween('invoice_date', [$startDate, $endDate])
                     ->sum('total_amount');
    }

    /**
     * Get total orders count
     */
    private function getTotalOrders($startDate, $endDate)
    {
        return SalesOrder::whereBetween('order_date', [$startDate, $endDate])
                        ->count();
    }

    /**
     * Get total outstanding amount
     */
    private function getTotalOutstanding()
    {
        return Invoice::whereIn('status', ['pending', 'overdue'])
                     ->sum('balance_due');
    }

    /**
     * Get average order value
     */
    private function getAverageOrderValue($startDate, $endDate)
    {
        return SalesOrder::whereBetween('order_date', [$startDate, $endDate])
                        ->avg('total_amount') ?? 0;
    }

    /**
     * Get top customers by sales
     */
    private function getTopCustomers($startDate, $endDate, $limit = 10)
    {
        return Customer::select('customers.*')
                      ->selectRaw('SUM(invoices.total_amount) as total_sales')
                      ->join('invoices', 'customers.id', '=', 'invoices.customer_id')
                      ->where('invoices.status', 'paid')
                      ->whereBetween('invoices.invoice_date', [$startDate, $endDate])
                      ->groupBy('customers.id')
                      ->orderByDesc('total_sales')
                      ->take($limit)
                      ->get();
    }

    /**
     * Get sales trend data
     */
    private function getSalesTrend($period)
    {
        $days = $period === 'week' ? 7 : ($period === 'month' ? 30 : 365);
        
        return Invoice::select(
                        DB::raw('DATE(invoice_date) as date'),
                        DB::raw('SUM(total_amount) as total_sales'),
                        DB::raw('COUNT(*) as total_invoices')
                      )
                      ->where('status', 'paid')
                      ->where('invoice_date', '>=', now()->subDays($days))
                      ->groupBy('date')
                      ->orderBy('date')
                      ->get();
    }

    /**
     * Get payment method statistics
     */
    private function getPaymentMethodStats($startDate, $endDate)
    {
        return Payment::select('payment_method')
                     ->selectRaw('COUNT(*) as count, SUM(amount) as total_amount')
                     ->where('status', 'completed')
                     ->whereBetween('payment_date', [$startDate, $endDate])
                     ->groupBy('payment_method')
                     ->get();
    }

    /**
     * Get daily sales data
     */
    private function getDailySales($startDate, $endDate)
    {
        return Invoice::select(
                        DB::raw('DATE(invoice_date) as date'),
                        DB::raw('SUM(total_amount) as total_sales'),
                        DB::raw('COUNT(*) as total_invoices')
                      )
                      ->where('status', 'paid')
                      ->whereBetween('invoice_date', [$startDate, $endDate])
                      ->groupBy('date')
                      ->orderBy('date')
                      ->get();
    }

    /**
     * Get monthly sales data
     */
    private function getMonthlySales()
    {
        return Invoice::select(
                        DB::raw('YEAR(invoice_date) as year'),
                        DB::raw('MONTH(invoice_date) as month'),
                        DB::raw('SUM(total_amount) as total_sales'),
                        DB::raw('COUNT(*) as total_invoices')
                      )
                      ->where('status', 'paid')
                      ->where('invoice_date', '>=', now()->subYear())
                      ->groupBy('year', 'month')
                      ->orderBy('year')
                      ->orderBy('month')
                      ->get();
    }

    /**
     * Get sales by customer type
     */
    private function getSalesByCustomerType($startDate, $endDate)
    {
        return Customer::select('type')
                      ->selectRaw('SUM(invoices.total_amount) as total_sales')
                      ->selectRaw('COUNT(invoices.id) as total_invoices')
                      ->join('invoices', 'customers.id', '=', 'invoices.customer_id')
                      ->where('invoices.status', 'paid')
                      ->whereBetween('invoices.invoice_date', [$startDate, $endDate])
                      ->groupBy('type')
                      ->get();
    }

    /**
     * Get sales by payment method
     */
    private function getSalesByPaymentMethod($startDate, $endDate)
    {
        return SalesOrder::select('payment_method')
                        ->selectRaw('SUM(total_amount) as total_sales')
                        ->selectRaw('COUNT(*) as total_orders')
                        ->whereBetween('order_date', [$startDate, $endDate])
                        ->groupBy('payment_method')
                        ->get();
    }

    /**
     * Get top selling products
     */
    private function getTopSellingProducts($startDate, $endDate, $limit = 10)
    {
        return DB::table('invoice_items')
                 ->select('products.name', 'products.name_ar', 'products.sku')
                 ->selectRaw('SUM(invoice_items.quantity) as total_quantity')
                 ->selectRaw('SUM(invoice_items.total_amount) as total_sales')
                 ->join('products', 'invoice_items.product_id', '=', 'products.id')
                 ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
                 ->where('invoices.status', 'paid')
                 ->whereBetween('invoices.invoice_date', [$startDate, $endDate])
                 ->groupBy('products.id', 'products.name', 'products.name_ar', 'products.sku')
                 ->orderByDesc('total_quantity')
                 ->take($limit)
                 ->get();
    }

    /**
     * Get sales summary report
     */
    private function getSalesSummaryReport($startDate, $endDate)
    {
        $summary = [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'totals' => [
                'total_sales' => $this->getTotalSales($startDate, $endDate),
                'total_orders' => $this->getTotalOrders($startDate, $endDate),
                'average_order_value' => $this->getAverageOrderValue($startDate, $endDate),
                'total_customers' => Customer::whereHas('salesOrders', function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('order_date', [$startDate, $endDate]);
                })->count(),
            ],
            'by_status' => SalesOrder::select('status')
                                   ->selectRaw('COUNT(*) as count, SUM(total_amount) as total_amount')
                                   ->whereBetween('order_date', [$startDate, $endDate])
                                   ->groupBy('status')
                                   ->get(),
            'by_payment_method' => $this->getSalesByPaymentMethod($startDate, $endDate),
            'by_customer_type' => $this->getSalesByCustomerType($startDate, $endDate),
        ];

        return response()->json($summary);
    }

    /**
     * Get detailed sales report
     */
    private function getDetailedSalesReport($startDate, $endDate)
    {
        $orders = SalesOrder::with(['customer', 'items.product', 'salesRep'])
                           ->whereBetween('order_date', [$startDate, $endDate])
                           ->orderBy('order_date', 'desc')
                           ->paginate(50);

        return response()->json([
            'orders' => $orders,
            'summary' => [
                'total_orders' => $orders->total(),
                'total_amount' => $orders->sum('total_amount'),
            ]
        ]);
    }

    /**
     * Get customer sales report
     */
    private function getCustomerSalesReport($startDate, $endDate)
    {
        $customers = Customer::with(['salesOrders' => function ($q) use ($startDate, $endDate) {
                               $q->whereBetween('order_date', [$startDate, $endDate]);
                           }])
                           ->whereHas('salesOrders', function ($q) use ($startDate, $endDate) {
                               $q->whereBetween('order_date', [$startDate, $endDate]);
                           })
                           ->get()
                           ->map(function ($customer) {
                               $customer->total_orders = $customer->salesOrders->count();
                               $customer->total_sales = $customer->salesOrders->sum('total_amount');
                               $customer->average_order_value = $customer->total_orders > 0 ? 
                                   $customer->total_sales / $customer->total_orders : 0;
                               return $customer;
                           })
                           ->sortByDesc('total_sales');

        return response()->json(['customers' => $customers->values()]);
    }

    /**
     * Get product sales report
     */
    private function getProductSalesReport($startDate, $endDate)
    {
        $products = $this->getTopSellingProducts($startDate, $endDate, 100);

        return response()->json(['products' => $products]);
    }

    /**
     * Get sales trends
     */
    public function salesTrends(Request $request)
    {
        $period = $request->get('period', 'month');
        $startDate = $this->getStartDate($period);
        $endDate = now();

        $trends = $this->getSalesTrends($startDate, $endDate);

        return response()->json(['trends' => $trends]);
    }

    /**
     * Get top products
     */
    public function topProducts(Request $request)
    {
        $period = $request->get('period', 'month');
        $limit = $request->get('limit', 10);
        $startDate = $this->getStartDate($period);
        $endDate = now();

        $products = $this->getTopSellingProducts($startDate, $endDate, $limit);

        return response()->json(['products' => $products]);
    }

    /**
     * Get top customers
     */
    public function topCustomers(Request $request)
    {
        $period = $request->get('period', 'month');
        $limit = $request->get('limit', 10);
        $startDate = $this->getStartDate($period);
        $endDate = now();

        $customers = $this->getTopCustomers($startDate, $endDate, $limit);

        return response()->json(['customers' => $customers]);
    }

    /**
     * Get sales performance
     */
    public function salesPerformance(Request $request)
    {
        $period = $request->get('period', 'month');
        $startDate = $this->getStartDate($period);
        $endDate = now();

        $performance = [
            'total_sales' => $this->getTotalSales($startDate, $endDate),
            'total_orders' => $this->getTotalOrders($startDate, $endDate),
            'average_order_value' => $this->getAverageOrderValue($startDate, $endDate),
            'conversion_rate' => $this->getConversionRate($startDate, $endDate),
            'growth_rate' => $this->getGrowthRate($period),
        ];

        return response()->json(['performance' => $performance]);
    }

    /**
     * Get conversion rate
     */
    private function getConversionRate($startDate, $endDate)
    {
        $totalOrders = SalesOrder::whereBetween('order_date', [$startDate, $endDate])->count();
        $completedOrders = SalesOrder::whereBetween('order_date', [$startDate, $endDate])
                                   ->where('status', 'completed')
                                   ->count();

        return $totalOrders > 0 ? ($completedOrders / $totalOrders) * 100 : 0;
    }

    /**
     * Get growth rate
     */
    private function getGrowthRate($period)
    {
        $currentPeriodStart = $this->getStartDate($period);
        $currentPeriodEnd = now();

        $previousPeriodStart = $this->getPreviousPeriodStart($period);
        $previousPeriodEnd = $currentPeriodStart;

        $currentSales = $this->getTotalSales($currentPeriodStart, $currentPeriodEnd);
        $previousSales = $this->getTotalSales($previousPeriodStart, $previousPeriodEnd);

        if ($previousSales == 0) {
            return $currentSales > 0 ? 100 : 0;
        }

        return (($currentSales - $previousSales) / $previousSales) * 100;
    }

    /**
     * Get previous period start date
     */
    private function getPreviousPeriodStart($period)
    {
        switch ($period) {
            case 'day':
                return now()->subDays(2)->startOfDay();
            case 'week':
                return now()->subWeeks(2)->startOfWeek();
            case 'year':
                return now()->subYears(2)->startOfYear();
            default: // month
                return now()->subMonths(2)->startOfMonth();
        }
    }
}
