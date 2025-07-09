<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Modules\Sales\Models\Customer;
use App\Modules\Sales\Models\SalesOrder;
use App\Modules\Sales\Models\Invoice;
use App\Modules\Inventory\Models\Product;
use App\Modules\Inventory\Models\StockLevel;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the main dashboard
     */
    public function index(Request $request)
    {
        // Check if user is super admin and redirect to master admin dashboard
        if (auth()->user()->hasRole('super-admin') || auth()->user()->hasRole('super_admin') || auth()->user()->is_super_admin) {
            return redirect()->route('master-admin.dashboard');
        }

        // Ensure tenant context for regular users
        $tenantId = auth()->user()->tenant_id ?? session('tenant_id');
        if (!$tenantId) {
            return redirect()->route('login')->with('error', 'يجب تحديد المستأجر أولاً');
        }

        try {
            // Get dashboard statistics
            $stats = $this->getDashboardStats();

            // Get recent activities
            $recentOrders = SalesOrder::with(['customer'])
                                     ->latest()
                                     ->take(5)
                                     ->get();

            $recentInvoices = Invoice::with(['customer'])
                                    ->latest()
                                    ->take(5)
                                    ->get();

            // Get alerts
            $alerts = $this->getDashboardAlerts();

            // Get chart data
            $salesChart = $this->getSalesChartData();
            $inventoryChart = $this->getInventoryChartData();

            return view('dashboard.index', compact(
                'stats',
                'recentOrders',
                'recentInvoices',
                'alerts',
                'salesChart',
                'inventoryChart'
            ));
        } catch (\Exception $e) {
            // If there's an error with dashboard data, show a simple dashboard
            return view('dashboard.simple');
        }
    }
    
    /**
     * Get dashboard statistics
     */
    private function getDashboardStats()
    {
        $today = now();
        $thisMonth = now()->startOfMonth();
        $lastMonth = now()->subMonth()->startOfMonth();
        
        return [
            // Sales Stats
            'total_revenue' => Invoice::where('status', 'paid')
                                    ->whereBetween('invoice_date', [$thisMonth, $today])
                                    ->sum('total_amount'),
            
            'total_customers' => Customer::where('is_active', true)->count(),

            'total_products' => Product::where('is_active', true)->count(),

            'total_sales_reps' => \App\Modules\SalesReps\Models\SalesRepresentative::count(),

            'active_sales_reps' => \App\Modules\SalesReps\Models\SalesRepresentative::where('status', 'active')->count(),
            
            'pending_orders' => SalesOrder::where('status', 'pending')->count(),
            
            // Monthly comparisons
            'revenue_growth' => $this->calculateGrowth(
                Invoice::where('status', 'paid')
                       ->whereBetween('invoice_date', [$lastMonth, $lastMonth->copy()->endOfMonth()])
                       ->sum('total_amount'),
                Invoice::where('status', 'paid')
                       ->whereBetween('invoice_date', [$thisMonth, $today])
                       ->sum('total_amount')
            ),
            
            'orders_growth' => $this->calculateGrowth(
                SalesOrder::whereBetween('order_date', [$lastMonth, $lastMonth->copy()->endOfMonth()])->count(),
                SalesOrder::whereBetween('order_date', [$thisMonth, $today])->count()
            ),
            
            'customers_growth' => $this->calculateGrowth(
                Customer::where('created_at', '<', $thisMonth)->count(),
                Customer::count()
            ),
        ];
    }
    
    /**
     * Calculate growth percentage
     */
    private function calculateGrowth($previous, $current)
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }
        
        return round((($current - $previous) / $previous) * 100, 1);
    }
    
    /**
     * Get dashboard alerts
     */
    private function getDashboardAlerts()
    {
        $alerts = [];
        
        // Low stock alerts
        $lowStockProducts = StockLevel::where('current_stock', '<=', DB::raw('minimum_stock'))
                                    ->with('product')
                                    ->take(5)
                                    ->get();
        
        foreach ($lowStockProducts as $stockLevel) {
            $alerts[] = [
                'type' => 'warning',
                'title' => 'مخزون منخفض',
                'message' => "المنتج {$stockLevel->product->name} يحتاج إعادة تموين",
                'url' => route('inventory.products.show', $stockLevel->product->id),
                'created_at' => now(),
            ];
        }
        
        // Overdue invoices
        $overdueInvoices = Invoice::where('status', 'pending')
                                 ->where('due_date', '<', now())
                                 ->with('customer')
                                 ->take(5)
                                 ->get();
        
        foreach ($overdueInvoices as $invoice) {
            $alerts[] = [
                'type' => 'danger',
                'title' => 'فاتورة متأخرة',
                'message' => "فاتورة رقم {$invoice->invoice_number} للعميل {$invoice->customer->name} متأخرة",
                'url' => route('sales.invoices.show', $invoice->id),
                'created_at' => $invoice->due_date,
            ];
        }
        
        // Pending orders
        $pendingOrders = SalesOrder::where('status', 'pending')
                                  ->where('created_at', '<', now()->subDays(2))
                                  ->with('customer')
                                  ->take(3)
                                  ->get();
        
        foreach ($pendingOrders as $order) {
            $alerts[] = [
                'type' => 'info',
                'title' => 'طلب معلق',
                'message' => "طلب رقم {$order->order_number} معلق لأكثر من يومين",
                'url' => route('sales.orders.show', $order->id),
                'created_at' => $order->created_at,
            ];
        }
        
        return collect($alerts)->sortByDesc('created_at')->take(10)->values();
    }
    
    /**
     * Get sales chart data for the last 30 days
     */
    private function getSalesChartData()
    {
        $startDate = now()->subDays(30);
        $endDate = now();
        
        return Invoice::select(
                        DB::raw('DATE(invoice_date) as date'),
                        DB::raw('SUM(total_amount) as total_sales'),
                        DB::raw('COUNT(*) as total_invoices')
                      )
                      ->where('status', 'paid')
                      ->whereBetween('invoice_date', [$startDate, $endDate])
                      ->groupBy('date')
                      ->orderBy('date')
                      ->get()
                      ->map(function ($item) {
                          return [
                              'date' => $item->date,
                              'sales' => (float) $item->total_sales,
                              'invoices' => (int) $item->total_invoices,
                          ];
                      });
    }
    
    /**
     * Get inventory chart data
     */
    private function getInventoryChartData()
    {
        return [
            'low_stock' => StockLevel::where('current_stock', '<=', DB::raw('minimum_stock'))->count(),
            'normal_stock' => StockLevel::where('current_stock', '>', DB::raw('minimum_stock'))
                                      ->where('current_stock', '<', DB::raw('maximum_stock'))
                                      ->count(),
            'high_stock' => StockLevel::where('current_stock', '>=', DB::raw('maximum_stock'))->count(),
            'out_of_stock' => StockLevel::where('current_stock', '<=', 0)->count(),
        ];
    }
}
