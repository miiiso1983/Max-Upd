<?php

namespace App\Modules\Inventory\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Inventory\Models\Warehouse;
use App\Modules\Inventory\Models\Product;
use App\Modules\Inventory\Models\Inventory;
use App\Modules\Inventory\Models\InventoryMovement;
use App\Modules\Inventory\Models\ProductCategory;
use App\Modules\Inventory\Models\ProductBrand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    /**
     * Display the main inventory dashboard
     */
    public function index(Request $request)
    {
        // Get overall statistics
        $stats = [
            'total_warehouses' => Warehouse::where('is_active', true)->count(),
            'total_products' => Product::where('is_active', true)->count(),
            'total_categories' => ProductCategory::where('status', 'active')->count(),
            'total_brands' => ProductBrand::where('status', 'active')->count(),
            'total_inventory_value' => Inventory::join('products', 'inventory.product_id', '=', 'products.id')
                                              ->sum(DB::raw('inventory.quantity * inventory.average_cost')),
            'low_stock_items' => Inventory::lowStock()->count(),
            'out_of_stock_items' => Inventory::where('quantity', '<=', 0)->count(),
            'expiring_items' => Inventory::expiringSoon(30)->count(),
            'expired_items' => Inventory::expired()->count(),
        ];

        // Get recent movements
        $recentMovements = InventoryMovement::with(['warehouse', 'product', 'creator'])
                                          ->latest()
                                          ->limit(10)
                                          ->get();

        // Get low stock alerts
        $lowStockItems = Inventory::with(['warehouse', 'product.category'])
                                ->lowStock()
                                ->limit(10)
                                ->get();

        // Get expiring items
        $expiringItems = Inventory::with(['warehouse', 'product'])
                               ->expiringSoon(30)
                               ->limit(10)
                               ->get();

        // Get top products by value
        $topProductsByValue = Inventory::with(['product', 'warehouse'])
                                     ->select('product_id', DB::raw('SUM(quantity * average_cost) as total_value'))
                                     ->groupBy('product_id')
                                     ->orderBy('total_value', 'desc')
                                     ->limit(10)
                                     ->get();

        // Get warehouse utilization
        $warehouseUtilization = Warehouse::where('is_active', true)
                                       ->select('id', 'name', 'name_ar', 'capacity', 'current_utilization')
                                       ->get()
                                       ->map(function ($warehouse) {
                                           $warehouse->utilization_percentage = $warehouse->capacity > 0
                                               ? ($warehouse->current_utilization / $warehouse->capacity) * 100
                                               : 0;
                                           return $warehouse;
                                       });

        return view('inventory.index', compact(
            'stats',
            'recentMovements',
            'lowStockItems',
            'expiringItems',
            'topProductsByValue',
            'warehouseUtilization'
        ));
    }

    /**
     * Display inventory by warehouse
     */
    public function byWarehouse(Request $request)
    {
        $query = Inventory::with(['warehouse', 'product.category', 'product.brand']);

        // Filter by warehouse
        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('product', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('name_ar', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }

        // Filter by brand
        if ($request->filled('brand_id')) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('brand_id', $request->brand_id);
            });
        }

        // Filter by stock status
        if ($request->filled('stock_status')) {
            switch ($request->stock_status) {
                case 'low':
                    $query->lowStock();
                    break;
                case 'out':
                    $query->where('quantity', '<=', 0);
                    break;
                case 'available':
                    $query->available();
                    break;
                case 'expiring':
                    $query->expiringSoon(30);
                    break;
                case 'expired':
                    $query->expired();
                    break;
            }
        }

        $inventory = $query->orderBy('quantity', 'desc')->paginate(20);

        $warehouses = Warehouse::where('is_active', true)->get(['id', 'name', 'name_ar']);
        $categories = ProductCategory::where('status', 'active')->get(['id', 'name', 'name_ar']);
        $brands = ProductBrand::where('status', 'active')->get(['id', 'name', 'name_ar']);

        return view('inventory.by-warehouse', compact('inventory', 'warehouses', 'categories', 'brands'));
    }

    /**
     * Display inventory by product
     */
    public function byProduct(Request $request)
    {
        $query = Product::with(['category', 'brand', 'inventory.warehouse']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('name_ar', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by brand
        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        $products = $query->where('is_active', true)
                         ->orderBy('name')
                         ->paginate(20);

        $categories = ProductCategory::where('status', 'active')->get(['id', 'name', 'name_ar']);
        $brands = ProductBrand::where('status', 'active')->get(['id', 'name', 'name_ar']);

        return view('inventory.by-product', compact('products', 'categories', 'brands'));
    }

    /**
     * Display inventory movements
     */
    public function movements(Request $request)
    {
        $query = InventoryMovement::with(['warehouse', 'product', 'creator', 'fromWarehouse', 'toWarehouse']);

        // Filter by date range
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('movement_date', [$request->date_from, $request->date_to]);
        } elseif ($request->filled('date_from')) {
            $query->where('movement_date', '>=', $request->date_from);
        } elseif ($request->filled('date_to')) {
            $query->where('movement_date', '<=', $request->date_to);
        }

        // Filter by warehouse
        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        // Filter by product
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by source type
        if ($request->filled('source_type')) {
            $query->where('source_type', $request->source_type);
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reference_number', 'like', "%{$search}%")
                  ->orWhere('reason', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhereHas('product', function ($pq) use ($search) {
                      $pq->where('name', 'like', "%{$search}%")
                         ->orWhere('name_ar', 'like', "%{$search}%")
                         ->orWhere('sku', 'like', "%{$search}%");
                  });
            });
        }

        $movements = $query->orderBy('movement_date', 'desc')
                          ->orderBy('created_at', 'desc')
                          ->paginate(20);

        $warehouses = Warehouse::where('is_active', true)->get(['id', 'name', 'name_ar']);

        return view('inventory.movements', compact('movements', 'warehouses'));
    }

    /**
     * Show low stock report
     */
    public function lowStock(Request $request)
    {
        $query = Inventory::with(['warehouse', 'product.category', 'product.brand'])
                         ->lowStock();

        // Filter by warehouse
        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }

        $lowStockItems = $query->orderBy('quantity', 'asc')->paginate(20);

        $warehouses = Warehouse::where('is_active', true)->get(['id', 'name', 'name_ar']);
        $categories = ProductCategory::where('status', 'active')->get(['id', 'name', 'name_ar']);

        return view('inventory.low-stock', compact('lowStockItems', 'warehouses', 'categories'));
    }

    /**
     * Show expiring items report
     */
    public function expiring(Request $request)
    {
        $days = $request->get('days', 30);

        $query = Inventory::with(['warehouse', 'product.category', 'product.brand'])
                         ->expiringSoon($days);

        // Filter by warehouse
        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }

        $expiringItems = $query->orderBy('expiry_date', 'asc')->paginate(20);

        $warehouses = Warehouse::where('is_active', true)->get(['id', 'name', 'name_ar']);
        $categories = ProductCategory::where('status', 'active')->get(['id', 'name', 'name_ar']);

        return view('inventory.expiring', compact('expiringItems', 'warehouses', 'categories', 'days'));
    }

    /**
     * Adjust inventory quantity
     */
    public function adjust(Request $request)
    {
        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'product_id' => 'required|exists:products,id',
            'new_quantity' => 'required|numeric|min:0',
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $inventory = Inventory::firstOrCreate(
                [
                    'warehouse_id' => $request->warehouse_id,
                    'product_id' => $request->product_id,
                ],
                [
                    'quantity' => 0,
                    'reserved_quantity' => 0,
                    'available_quantity' => 0,
                    'created_by' => auth()->user()?->id,
                ]
            );

            $oldQuantity = $inventory->quantity;
            $newQuantity = $request->new_quantity;

            // Update inventory
            $inventory->updateQuantity($newQuantity, $request->reason);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم تعديل المخزون بنجاح',
                'old_quantity' => $oldQuantity,
                'new_quantity' => $newQuantity,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تعديل المخزون: ' . $e->getMessage(),
            ], 500);
        }
    }
}