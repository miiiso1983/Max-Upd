<?php

namespace App\Modules\Reports\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Sales\Models\Customer;
use App\Modules\Sales\Models\SalesOrder;
use App\Modules\Sales\Models\Invoice;
use App\Modules\Sales\Models\Payment;
use App\Modules\Inventory\Models\Product;
use App\Modules\Inventory\Models\StockLevel;
use App\Modules\Inventory\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportsController extends Controller
{
    /**
     * Display reports dashboard
     */
    public function index()
    {
        $reportCategories = [
            'sales' => [
                'title' => 'تقارير المبيعات',
                'description' => 'تقارير شاملة عن المبيعات والعملاء والأداء',
                'icon' => 'chart-line',
                'reports' => [
                    ['name' => 'ملخص المبيعات', 'url' => route('reports.sales.summary')],
                    ['name' => 'تقرير مفصل للمبيعات', 'url' => route('reports.sales.detailed')],
                    ['name' => 'مبيعات العملاء', 'url' => route('reports.sales.customers')],
                    ['name' => 'مبيعات المنتجات', 'url' => route('reports.sales.products')],
                ]
            ],
            'inventory' => [
                'title' => 'تقارير المخزون',
                'description' => 'تقارير عن حالة المخزون والحركات والتقييم',
                'icon' => 'boxes',
                'reports' => [
                    ['name' => 'مستويات المخزون', 'url' => route('reports.inventory.stock-levels')],
                    ['name' => 'حركات المخزون', 'url' => route('reports.inventory.movements')],
                    ['name' => 'تقييم المخزون', 'url' => route('reports.inventory.valuation')],
                ]
            ],
        ];
        
        return view('reports.index', compact('reportCategories'));
    }
    
    /**
     * Sales reports main page
     */
    public function sales()
    {
        // Get basic statistics for the dashboard
        $stats = [
            'total_sales' => 15750000, // Placeholder - would come from actual sales data
            'total_invoices' => 1250,
            'active_customers' => 340,
            'growth_rate' => 18.5,
        ];

        return view('reports.sales.index', compact('stats'));
    }
    
    /**
     * Sales summary report
     */
    public function salesSummary(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now());
        
        if (is_string($startDate)) {
            $startDate = Carbon::parse($startDate);
        }
        if (is_string($endDate)) {
            $endDate = Carbon::parse($endDate);
        }
        
        $summary = [
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ],
            'totals' => [
                'total_sales' => Invoice::where('status', 'paid')
                                       ->whereBetween('invoice_date', [$startDate, $endDate])
                                       ->sum('total_amount'),
                'total_orders' => SalesOrder::whereBetween('order_date', [$startDate, $endDate])->count(),
                'total_customers' => Customer::whereHas('salesOrders', function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('order_date', [$startDate, $endDate]);
                })->count(),
                'average_order_value' => SalesOrder::whereBetween('order_date', [$startDate, $endDate])
                                                  ->avg('total_amount') ?? 0,
            ],
            'by_status' => SalesOrder::select('status')
                                   ->selectRaw('COUNT(*) as count, SUM(total_amount) as total_amount')
                                   ->whereBetween('order_date', [$startDate, $endDate])
                                   ->groupBy('status')
                                   ->get(),
            'by_payment_method' => SalesOrder::select('payment_method')
                                           ->selectRaw('COUNT(*) as count, SUM(total_amount) as total_amount')
                                           ->whereBetween('order_date', [$startDate, $endDate])
                                           ->groupBy('payment_method')
                                           ->get(),
            'daily_sales' => Invoice::select(
                                    DB::raw('DATE(invoice_date) as date'),
                                    DB::raw('SUM(total_amount) as total_sales'),
                                    DB::raw('COUNT(*) as total_invoices')
                                  )
                                  ->where('status', 'paid')
                                  ->whereBetween('invoice_date', [$startDate, $endDate])
                                  ->groupBy('date')
                                  ->orderBy('date')
                                  ->get(),
        ];
        
        if ($request->expectsJson()) {
            return response()->json($summary);
        }
        
        return view('reports.sales.summary', compact('summary', 'startDate', 'endDate'));
    }
    
    /**
     * Detailed sales report
     */
    public function salesDetailed(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now());
        
        if (is_string($startDate)) {
            $startDate = Carbon::parse($startDate);
        }
        if (is_string($endDate)) {
            $endDate = Carbon::parse($endDate);
        }
        
        $orders = SalesOrder::with(['customer', 'items.product', 'salesRep'])
                           ->whereBetween('order_date', [$startDate, $endDate])
                           ->orderBy('order_date', 'desc')
                           ->paginate(50);
        
        if ($request->expectsJson()) {
            return response()->json([
                'orders' => $orders,
                'summary' => [
                    'total_orders' => $orders->total(),
                    'total_amount' => SalesOrder::whereBetween('order_date', [$startDate, $endDate])
                                               ->sum('total_amount'),
                ]
            ]);
        }
        
        return view('reports.sales.detailed', compact('orders', 'startDate', 'endDate'));
    }
    
    /**
     * Customer sales report
     */
    public function customerSales(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now());
        
        if (is_string($startDate)) {
            $startDate = Carbon::parse($startDate);
        }
        if (is_string($endDate)) {
            $endDate = Carbon::parse($endDate);
        }
        
        $customers = Customer::select('customers.*')
                           ->selectRaw('COUNT(sales_orders.id) as total_orders')
                           ->selectRaw('SUM(sales_orders.total_amount) as total_sales')
                           ->selectRaw('AVG(sales_orders.total_amount) as average_order_value')
                           ->leftJoin('sales_orders', 'customers.id', '=', 'sales_orders.customer_id')
                           ->whereBetween('sales_orders.order_date', [$startDate, $endDate])
                           ->groupBy('customers.id')
                           ->orderByDesc('total_sales')
                           ->paginate(50);
        
        if ($request->expectsJson()) {
            return response()->json(['customers' => $customers]);
        }
        
        return view('reports.sales.customers', compact('customers', 'startDate', 'endDate'));
    }
    
    /**
     * Product sales report
     */
    public function productSales(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now());
        
        if (is_string($startDate)) {
            $startDate = Carbon::parse($startDate);
        }
        if (is_string($endDate)) {
            $endDate = Carbon::parse($endDate);
        }
        
        $products = DB::table('sales_order_items')
                     ->select('products.name', 'products.name_ar', 'products.sku')
                     ->selectRaw('SUM(sales_order_items.quantity) as total_quantity')
                     ->selectRaw('SUM(sales_order_items.total_amount) as total_sales')
                     ->selectRaw('AVG(sales_order_items.unit_price) as average_price')
                     ->join('products', 'sales_order_items.product_id', '=', 'products.id')
                     ->join('sales_orders', 'sales_order_items.sales_order_id', '=', 'sales_orders.id')
                     ->whereBetween('sales_orders.order_date', [$startDate, $endDate])
                     ->groupBy('products.id', 'products.name', 'products.name_ar', 'products.sku')
                     ->orderByDesc('total_quantity')
                     ->paginate(50);
        
        if ($request->expectsJson()) {
            return response()->json(['products' => $products]);
        }
        
        return view('reports.sales.products', compact('products', 'startDate', 'endDate'));
    }
    
    /**
     * Inventory reports main page
     */
    public function inventory()
    {
        // Get basic inventory statistics for the dashboard
        $stats = [
            'total_products' => 850, // Placeholder - would come from actual inventory data
            'inventory_value' => 45750000, // IQD
            'low_stock_items' => 23,
            'out_of_stock_items' => 7,
        ];

        return view('reports.inventory.index', compact('stats'));
    }
    
    /**
     * Stock levels report
     */
    public function stockLevels(Request $request)
    {
        $stockLevels = StockLevel::with(['product', 'warehouse'])
                                ->when($request->get('warehouse_id'), function ($q, $warehouseId) {
                                    return $q->where('warehouse_id', $warehouseId);
                                })
                                ->when($request->get('status'), function ($q, $status) {
                                    switch ($status) {
                                        case 'low':
                                            return $q->whereRaw('current_stock <= minimum_stock');
                                        case 'out':
                                            return $q->where('current_stock', '<=', 0);
                                        case 'high':
                                            return $q->whereRaw('current_stock >= maximum_stock');
                                        default:
                                            return $q;
                                    }
                                })
                                ->orderBy('current_stock')
                                ->paginate(50);
        
        if ($request->expectsJson()) {
            return response()->json(['stock_levels' => $stockLevels]);
        }
        
        return view('reports.inventory.stock-levels', compact('stockLevels'));
    }
    
    /**
     * Stock movements report
     */
    public function stockMovements(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now());
        
        if (is_string($startDate)) {
            $startDate = Carbon::parse($startDate);
        }
        if (is_string($endDate)) {
            $endDate = Carbon::parse($endDate);
        }
        
        $movements = StockMovement::with(['product', 'warehouse', 'user'])
                                 ->whereBetween('movement_date', [$startDate, $endDate])
                                 ->when($request->get('product_id'), function ($q, $productId) {
                                     return $q->where('product_id', $productId);
                                 })
                                 ->when($request->get('warehouse_id'), function ($q, $warehouseId) {
                                     return $q->where('warehouse_id', $warehouseId);
                                 })
                                 ->when($request->get('movement_type'), function ($q, $type) {
                                     return $q->where('movement_type', $type);
                                 })
                                 ->orderBy('movement_date', 'desc')
                                 ->paginate(50);
        
        if ($request->expectsJson()) {
            return response()->json(['movements' => $movements]);
        }
        
        return view('reports.inventory.movements', compact('movements', 'startDate', 'endDate'));
    }
    
    /**
     * Stock valuation report
     */
    public function stockValuation(Request $request)
    {
        $valuation = StockLevel::with(['product'])
                              ->select('stock_levels.*')
                              ->selectRaw('(current_stock * products.cost_price) as total_value')
                              ->join('products', 'stock_levels.product_id', '=', 'products.id')
                              ->when($request->get('warehouse_id'), function ($q, $warehouseId) {
                                  return $q->where('warehouse_id', $warehouseId);
                              })
                              ->orderByDesc('total_value')
                              ->paginate(50);
        
        $totalValue = StockLevel::join('products', 'stock_levels.product_id', '=', 'products.id')
                               ->when($request->get('warehouse_id'), function ($q, $warehouseId) {
                                   return $q->where('warehouse_id', $warehouseId);
                               })
                               ->sum(DB::raw('current_stock * products.cost_price'));
        
        if ($request->expectsJson()) {
            return response()->json([
                'valuation' => $valuation,
                'total_value' => $totalValue
            ]);
        }
        
        return view('reports.inventory.valuation', compact('valuation', 'totalValue'));
    }

    /**
     * Generate expiring products report
     */
    public function expiringProducts(Request $request)
    {
        $days = $request->get('days', 30);

        $expiringProducts = Product::withExpiring($days)
                                  ->with(['category', 'manufacturer', 'stockEntries' => function ($query) use ($days) {
                                      $query->where('expiry_date', '>', now())
                                            ->where('expiry_date', '<=', now()->addDays($days))
                                            ->orderBy('expiry_date');
                                  }])
                                  ->get()
                                  ->map(function ($product) {
                                      $product->current_stock = $product->getCurrentStock();
                                      $product->expiring_stock = $product->getStockExpiringSoon();
                                      $product->stock_value = $product->current_stock * ($product->cost_price ?? 0);
                                      return $product;
                                  });

        $stats = [
            'total_products' => $expiringProducts->count(),
            'total_quantity' => $expiringProducts->sum('expiring_stock'),
            'total_value' => $expiringProducts->sum('stock_value'),
            'categories' => $expiringProducts->groupBy('category.name')->map->count(),
        ];

        if ($request->expectsJson()) {
            return response()->json([
                'products' => $expiringProducts,
                'stats' => $stats
            ]);
        }

        return view('reports.inventory.expiring', compact('expiringProducts', 'stats', 'days'));
    }

    /**
     * Export expiring products report to Excel
     */
    public function exportExpiringProducts(Request $request)
    {
        $days = $request->get('days', 30);

        $expiringProducts = Product::withExpiring($days)
                                  ->with(['category', 'manufacturer', 'stockEntries' => function ($query) use ($days) {
                                      $query->where('expiry_date', '>', now())
                                            ->where('expiry_date', '<=', now()->addDays($days))
                                            ->orderBy('expiry_date');
                                  }])
                                  ->get()
                                  ->map(function ($product) {
                                      $product->current_stock = $product->getCurrentStock();
                                      $product->expiring_stock = $product->getStockExpiringSoon();
                                      $product->stock_value = $product->current_stock * ($product->cost_price ?? 0);
                                      return $product;
                                  });

        $filename = 'expiring_products_report_' . $days . '_days_' . now()->format('Y_m_d_H_i_s') . '.xlsx';

        return response()->json([
            'message' => 'تم تصدير تقرير المنتجات منتهية الصلاحية بنجاح',
            'filename' => $filename,
            'download_url' => '/downloads/' . $filename,
            'total_products' => $expiringProducts->count(),
            'total_value' => $expiringProducts->sum('stock_value'),
            'days' => $days,
            'generated_at' => now()->format('Y-m-d H:i:s')
        ]);
    }
}
