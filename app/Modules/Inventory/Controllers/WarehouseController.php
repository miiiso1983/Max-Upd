<?php

namespace App\Modules\Inventory\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Inventory\Models\Warehouse;
use App\Modules\Inventory\Models\Inventory;
use App\Modules\Inventory\Models\InventoryMovement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WarehouseController extends Controller
{
    /**
     * Display a listing of warehouses
     */
    public function index(Request $request)
    {
        $query = Warehouse::with(['manager']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('name_ar', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Filter by type
        if ($request->filled('type')) {
            if ($request->type === 'main') {
                $query->where('is_main', true);
            } elseif ($request->type === 'branch') {
                $query->where('is_main', false);
            }
        }

        $warehouses = $query->orderBy('is_main', 'desc')
                           ->orderBy('name')
                           ->paginate(20);

        // Calculate statistics
        $stats = [
            'total_warehouses' => Warehouse::count(),
            'active_warehouses' => Warehouse::where('is_active', true)->count(),
            'main_warehouses' => Warehouse::where('is_main', true)->count(),
            'total_capacity' => Warehouse::sum('capacity'),
            'total_utilization' => Warehouse::sum('current_utilization'),
        ];

        $managers = User::select('id', 'name', 'email')->get();

        return view('inventory.warehouses.index', compact('warehouses', 'stats', 'managers'));
    }

    /**
     * Show the form for creating a new warehouse
     */
    public function create()
    {
        $managers = User::select('id', 'name', 'email')->get();
        return view('inventory.warehouses.create', compact('managers'));
    }

    /**
     * Store a newly created warehouse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'code' => 'required|string|max:255|unique:warehouses,code',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'governorate' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'manager_id' => 'nullable|exists:users,id',
            'capacity' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'is_main' => 'boolean',
            'temperature_controlled' => 'boolean',
            'min_temperature' => 'nullable|numeric',
            'max_temperature' => 'nullable|numeric',
        ]);

        try {
            DB::beginTransaction();

            $warehouse = Warehouse::create([
                'name' => $request->name,
                'name_ar' => $request->name_ar,
                'code' => $request->code,
                'description' => $request->description,
                'description_ar' => $request->description_ar,
                'address' => $request->address,
                'city' => $request->city,
                'governorate' => $request->governorate,
                'phone' => $request->phone,
                'email' => $request->email,
                'manager_id' => $request->manager_id,
                'capacity' => $request->capacity,
                'is_active' => $request->boolean('is_active', true),
                'is_main' => $request->boolean('is_main', false),
                'temperature_controlled' => $request->boolean('temperature_controlled', false),
                'min_temperature' => $request->min_temperature,
                'max_temperature' => $request->max_temperature,
                'created_by' => auth()->id(),
            ]);

            DB::commit();

            return redirect()->route('inventory.warehouses.show', $warehouse)
                           ->with('success', 'تم إنشاء المخزن بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'حدث خطأ أثناء إنشاء المخزن: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified warehouse
     */
    public function show(Warehouse $warehouse)
    {
        $warehouse->load(['manager', 'inventory.product', 'movements' => function ($query) {
            $query->latest()->limit(10);
        }]);

        // Get inventory statistics
        $inventoryStats = [
            'total_products' => $warehouse->inventory()->distinct('product_id')->count(),
            'total_quantity' => $warehouse->inventory()->sum('quantity'),
            'total_value' => $warehouse->inventory()->sum(DB::raw('quantity * average_cost')),
            'low_stock_items' => $warehouse->inventory()->lowStock()->count(),
            'expiring_items' => $warehouse->inventory()->expiringSoon(30)->count(),
            'expired_items' => $warehouse->inventory()->expired()->count(),
        ];

        // Get recent movements
        $recentMovements = $warehouse->movements()
                                   ->with(['product', 'creator'])
                                   ->latest()
                                   ->limit(10)
                                   ->get();

        // Get top products by quantity
        $topProducts = $warehouse->inventory()
                                ->with('product')
                                ->orderBy('quantity', 'desc')
                                ->limit(10)
                                ->get();

        return view('inventory.warehouses.show', compact(
            'warehouse', 
            'inventoryStats', 
            'recentMovements', 
            'topProducts'
        ));
    }

    /**
     * Show the form for editing the specified warehouse
     */
    public function edit(Warehouse $warehouse)
    {
        $managers = User::select('id', 'name', 'email')->get();
        return view('inventory.warehouses.edit', compact('warehouse', 'managers'));
    }

    /**
     * Update the specified warehouse
     */
    public function update(Request $request, Warehouse $warehouse)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'code' => 'required|string|max:255|unique:warehouses,code,' . $warehouse->id,
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'governorate' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'manager_id' => 'nullable|exists:users,id',
            'capacity' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'is_main' => 'boolean',
            'temperature_controlled' => 'boolean',
            'min_temperature' => 'nullable|numeric',
            'max_temperature' => 'nullable|numeric',
        ]);

        try {
            DB::beginTransaction();

            $warehouse->update([
                'name' => $request->name,
                'name_ar' => $request->name_ar,
                'code' => $request->code,
                'description' => $request->description,
                'description_ar' => $request->description_ar,
                'address' => $request->address,
                'city' => $request->city,
                'governorate' => $request->governorate,
                'phone' => $request->phone,
                'email' => $request->email,
                'manager_id' => $request->manager_id,
                'capacity' => $request->capacity,
                'is_active' => $request->boolean('is_active', true),
                'is_main' => $request->boolean('is_main', false),
                'temperature_controlled' => $request->boolean('temperature_controlled', false),
                'min_temperature' => $request->min_temperature,
                'max_temperature' => $request->max_temperature,
                'updated_by' => auth()->id(),
            ]);

            DB::commit();

            return redirect()->route('inventory.warehouses.show', $warehouse)
                           ->with('success', 'تم تحديث المخزن بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'حدث خطأ أثناء تحديث المخزن: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified warehouse
     */
    public function destroy(Warehouse $warehouse)
    {
        try {
            // Check if warehouse has inventory
            if ($warehouse->inventory()->exists()) {
                return redirect()->back()
                               ->with('error', 'لا يمكن حذف المخزن لأنه يحتوي على مخزون');
            }

            // Check if warehouse has movements
            if ($warehouse->movements()->exists()) {
                return redirect()->back()
                               ->with('error', 'لا يمكن حذف المخزن لأنه يحتوي على حركات مخزون');
            }

            $warehouse->delete();

            return redirect()->route('inventory.warehouses.index')
                           ->with('success', 'تم حذف المخزن بنجاح');

        } catch (\Exception $e) {
            return redirect()->back()
                           ->with('error', 'حدث خطأ أثناء حذف المخزن: ' . $e->getMessage());
        }
    }

    /**
     * Get warehouse inventory
     */
    public function inventory(Warehouse $warehouse, Request $request)
    {
        $query = $warehouse->inventory()->with(['product.category', 'product.brand']);

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
            }
        }

        $inventory = $query->orderBy('quantity', 'desc')->paginate(20);

        return view('inventory.warehouses.inventory', compact('warehouse', 'inventory'));
    }

    /**
     * Get warehouse movements
     */
    public function movements(Warehouse $warehouse, Request $request)
    {
        $query = $warehouse->movements()->with(['product', 'creator', 'fromWarehouse', 'toWarehouse']);

        // Filter by date range
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('movement_date', [$request->date_from, $request->date_to]);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by product
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        $movements = $query->orderBy('movement_date', 'desc')
                          ->orderBy('created_at', 'desc')
                          ->paginate(20);

        return view('inventory.warehouses.movements', compact('warehouse', 'movements'));
    }
}
