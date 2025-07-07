<?php

namespace App\Modules\Suppliers\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Suppliers\Models\Supplier;
use App\Modules\Suppliers\Models\SupplierProduct;
use App\Modules\Inventory\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SupplierController extends Controller
{
    /**
     * Display a listing of suppliers
     */
    public function index(Request $request)
    {
        $query = Supplier::query();

        // Search functionality
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('name_ar', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter by type
        if ($request->has('type')) {
            $query->ofType($request->get('type'));
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->get('status'));
        }

        // Filter by country
        if ($request->has('country')) {
            $query->fromCountry($request->get('country'));
        }

        // Filter by preferred
        if ($request->get('preferred') === 'true') {
            $query->preferred();
        }

        $suppliers = $query->orderBy('name')
                          ->paginate(20);

        // Add calculated fields
        $suppliers->getCollection()->transform(function ($supplier) {
            $supplier->total_purchases = $supplier->getTotalPurchases();
            $supplier->outstanding_amount = $supplier->getOutstandingAmount();
            $supplier->last_order_date = $supplier->getLastOrderDate();
            $supplier->total_orders = $supplier->getTotalOrdersCount();
            $supplier->average_order_value = $supplier->getAverageOrderValue();
            $supplier->performance_rating = $supplier->getPerformanceRating();
            return $supplier;
        });

        $filters = [
            'types' => Supplier::getTypes(),
            'types_ar' => Supplier::getTypesAr(),
            'statuses' => Supplier::getStatuses(),
            'statuses_ar' => Supplier::getStatusesAr(),
            'countries' => $this->getCountries(),
        ];

        // Return JSON for API requests
        if ($request->expectsJson()) {
            return response()->json([
                'suppliers' => $suppliers,
                'filters' => $filters
            ]);
        }

        // Return view for web requests
        return view('suppliers.index', compact('suppliers', 'filters'));
    }

    /**
     * Show the form for creating a new supplier
     */
    public function create()
    {
        return view('suppliers.create');
    }

    /**
     * Store a newly created supplier
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'type' => 'required|in:' . implode(',', array_keys(Supplier::getTypes())),
            'code' => 'nullable|string|max:255|unique:suppliers,code',
            'status' => 'nullable|in:' . implode(',', array_keys(Supplier::getStatuses())),
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'fax' => 'nullable|string|max:20',
            'website' => 'nullable|url|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'governorate' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'tax_number' => 'nullable|string|max:50',
            'license_number' => 'nullable|string|max:50',
            'contact_person' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email|max:255',
            'payment_terms' => 'nullable|integer|min:0|max:365',
            'credit_limit' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:3',
            'bank_name' => 'nullable|string|max:255',
            'bank_account' => 'nullable|string|max:255',
            'iban' => 'nullable|string|max:34',
            'swift_code' => 'nullable|string|max:11',
            'rating' => 'nullable|numeric|min:0|max:10',
            'notes' => 'nullable|string',
            'is_preferred' => 'boolean',
        ]);

        $validated['created_by'] = auth()->id();

        $supplier = Supplier::create($validated);

        return response()->json([
            'message' => 'Supplier created successfully',
            'supplier' => $supplier
        ], 201);
    }

    /**
     * Display the specified supplier
     */
    public function show(Supplier $supplier)
    {
        $supplier->load(['purchaseOrders.items.product', 'products']);
        
        $supplier->total_purchases = $supplier->getTotalPurchases();
        $supplier->outstanding_amount = $supplier->getOutstandingAmount();
        $supplier->last_order_date = $supplier->getLastOrderDate();
        $supplier->total_orders = $supplier->getTotalOrdersCount();
        $supplier->average_order_value = $supplier->getAverageOrderValue();
        $supplier->performance_rating = $supplier->getPerformanceRating();

        // Get recent orders
        $recentOrders = $supplier->purchaseOrders()
                               ->with(['items.product'])
                               ->latest()
                               ->take(10)
                               ->get();

        // Get purchase history (last 12 months)
        $purchaseHistory = $supplier->purchaseOrders()
                                  ->selectRaw('YEAR(order_date) as year, MONTH(order_date) as month, SUM(total_amount) as total_purchases')
                                  ->where('status', 'completed')
                                  ->where('order_date', '>=', now()->subYear())
                                  ->groupBy('year', 'month')
                                  ->orderBy('year')
                                  ->orderBy('month')
                                  ->get();

        // Check if request expects JSON (for API calls)
        if (request()->expectsJson()) {
            return response()->json([
                'supplier' => $supplier,
                'recent_orders' => $recentOrders,
                'purchase_history' => $purchaseHistory,
            ]);
        }

        // Return view for web requests
        return view('suppliers.show', compact('supplier', 'recentOrders', 'purchaseHistory'));
    }

    /**
     * Show the form for editing the specified supplier
     */
    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    /**
     * Update the specified supplier
     */
    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'type' => 'required|in:' . implode(',', array_keys(Supplier::getTypes())),
            'code' => ['nullable', 'string', 'max:255', Rule::unique('suppliers')->ignore($supplier->id)],
            'status' => 'nullable|in:' . implode(',', array_keys(Supplier::getStatuses())),
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'fax' => 'nullable|string|max:20',
            'website' => 'nullable|url|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'governorate' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'tax_number' => 'nullable|string|max:50',
            'license_number' => 'nullable|string|max:50',
            'contact_person' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email|max:255',
            'payment_terms' => 'nullable|integer|min:0|max:365',
            'credit_limit' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:3',
            'bank_name' => 'nullable|string|max:255',
            'bank_account' => 'nullable|string|max:255',
            'iban' => 'nullable|string|max:34',
            'swift_code' => 'nullable|string|max:11',
            'rating' => 'nullable|numeric|min:0|max:10',
            'notes' => 'nullable|string',
            'is_preferred' => 'boolean',
        ]);

        $validated['updated_by'] = auth()->id();

        $supplier->update($validated);

        return response()->json([
            'message' => 'Supplier updated successfully',
            'supplier' => $supplier->fresh()
        ]);
    }

    /**
     * Remove the specified supplier
     */
    public function destroy(Supplier $supplier)
    {
        // Check if supplier has purchase orders
        if ($supplier->purchaseOrders()->exists()) {
            return response()->json([
                'message' => 'Cannot delete supplier with existing purchase orders'
            ], 422);
        }

        $supplier->delete();

        return response()->json([
            'message' => 'Supplier deleted successfully'
        ]);
    }

    /**
     * Get supplier products
     */
    public function products(Supplier $supplier, Request $request)
    {
        $query = $supplier->products();

        // Search products
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('products.name', 'like', "%{$search}%")
                  ->orWhere('products.name_ar', 'like', "%{$search}%")
                  ->orWhere('products.sku', 'like', "%{$search}%")
                  ->orWhere('supplier_products.supplier_sku', 'like', "%{$search}%");
            });
        }

        $products = $query->withPivot([
                           'supplier_sku', 'unit_cost', 'last_unit_cost', 
                           'minimum_order_quantity', 'lead_time_days', 
                           'is_preferred', 'last_order_date'
                       ])
                       ->paginate(20);

        return response()->json([
            'products' => $products
        ]);
    }

    /**
     * Add product to supplier
     */
    public function addProduct(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'supplier_sku' => 'nullable|string|max:255',
            'unit_cost' => 'required|numeric|min:0',
            'minimum_order_quantity' => 'nullable|integer|min:1',
            'lead_time_days' => 'nullable|integer|min:0',
            'is_preferred' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        // Check if product already exists for this supplier
        if ($supplier->products()->where('product_id', $validated['product_id'])->exists()) {
            return response()->json([
                'message' => 'Product already exists for this supplier'
            ], 422);
        }

        $validated['created_by'] = auth()->id();

        $supplierProduct = SupplierProduct::create([
            'supplier_id' => $supplier->id,
            'product_id' => $validated['product_id'],
            'supplier_sku' => $validated['supplier_sku'],
            'unit_cost' => $validated['unit_cost'],
            'minimum_order_quantity' => $validated['minimum_order_quantity'] ?? 1,
            'lead_time_days' => $validated['lead_time_days'] ?? 7,
            'is_preferred' => $validated['is_preferred'] ?? false,
            'notes' => $validated['notes'],
            'created_by' => $validated['created_by'],
        ]);

        return response()->json([
            'message' => 'Product added to supplier successfully',
            'supplier_product' => $supplierProduct->load('product')
        ], 201);
    }

    /**
     * Update supplier product
     */
    public function updateProduct(Request $request, Supplier $supplier, Product $product)
    {
        $supplierProduct = SupplierProduct::where('supplier_id', $supplier->id)
                                         ->where('product_id', $product->id)
                                         ->firstOrFail();

        $validated = $request->validate([
            'supplier_sku' => 'nullable|string|max:255',
            'unit_cost' => 'required|numeric|min:0',
            'minimum_order_quantity' => 'nullable|integer|min:1',
            'lead_time_days' => 'nullable|integer|min:0',
            'is_preferred' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $validated['updated_by'] = auth()->id();

        $supplierProduct->update($validated);

        return response()->json([
            'message' => 'Supplier product updated successfully',
            'supplier_product' => $supplierProduct->fresh()->load('product')
        ]);
    }

    /**
     * Remove product from supplier
     */
    public function removeProduct(Supplier $supplier, Product $product)
    {
        $supplierProduct = SupplierProduct::where('supplier_id', $supplier->id)
                                         ->where('product_id', $product->id)
                                         ->firstOrFail();

        $supplierProduct->delete();

        return response()->json([
            'message' => 'Product removed from supplier successfully'
        ]);
    }

    /**
     * Get supplier statistics
     */
    public function statistics(Supplier $supplier, Request $request)
    {
        $startDate = $request->get('start_date', now()->subYear());
        $endDate = $request->get('end_date', now());

        $stats = [
            'total_orders' => $supplier->purchaseOrders()
                                     ->whereBetween('order_date', [$startDate, $endDate])
                                     ->count(),
            'total_purchases' => $supplier->purchaseOrders()
                                        ->where('status', 'completed')
                                        ->whereBetween('order_date', [$startDate, $endDate])
                                        ->sum('total_amount'),
            'average_order_value' => $supplier->purchaseOrders()
                                            ->whereBetween('order_date', [$startDate, $endDate])
                                            ->avg('total_amount') ?? 0,
            'outstanding_amount' => $supplier->getOutstandingAmount(),
            'performance_rating' => $supplier->getPerformanceRating(),
            'last_order_date' => $supplier->getLastOrderDate(),
            'products_count' => $supplier->products()->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Export suppliers to Excel
     */
    public function export(Request $request)
    {
        // This would implement Excel export using Maatwebsite/Excel
        return response()->json([
            'message' => 'Excel export will be implemented with Maatwebsite/Excel',
            'download_url' => '/api/tenant/suppliers/export'
        ]);
    }

    /**
     * Import suppliers from Excel
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv'
        ]);

        // This would implement Excel import using Maatwebsite/Excel
        return response()->json([
            'message' => 'Excel import will be implemented with Maatwebsite/Excel'
        ]);
    }

    /**
     * Bulk update suppliers
     */
    public function bulkUpdate(Request $request)
    {
        $validated = $request->validate([
            'supplier_ids' => 'required|array',
            'supplier_ids.*' => 'exists:suppliers,id',
            'action' => 'required|in:activate,deactivate,set_preferred,unset_preferred,delete',
            'data' => 'nullable|array',
        ]);

        $suppliers = Supplier::whereIn('id', $validated['supplier_ids']);

        switch ($validated['action']) {
            case 'activate':
                $suppliers->update(['status' => Supplier::STATUS_ACTIVE]);
                break;
            case 'deactivate':
                $suppliers->update(['status' => Supplier::STATUS_INACTIVE]);
                break;
            case 'set_preferred':
                $suppliers->update(['is_preferred' => true]);
                break;
            case 'unset_preferred':
                $suppliers->update(['is_preferred' => false]);
                break;
            case 'delete':
                // Check if any supplier has purchase orders
                $suppliersWithOrders = $suppliers->whereHas('purchaseOrders')->count();
                if ($suppliersWithOrders > 0) {
                    return response()->json([
                        'message' => 'Cannot delete suppliers with existing purchase orders'
                    ], 422);
                }
                $suppliers->delete();
                break;
        }

        return response()->json([
            'message' => 'Bulk update completed successfully'
        ]);
    }

    /**
     * Get countries list
     */
    private function getCountries()
    {
        return [
            'Iraq' => 'العراق',
            'Jordan' => 'الأردن',
            'Lebanon' => 'لبنان',
            'Syria' => 'سوريا',
            'Turkey' => 'تركيا',
            'Iran' => 'إيران',
            'Kuwait' => 'الكويت',
            'Saudi Arabia' => 'السعودية',
            'UAE' => 'الإمارات',
            'Egypt' => 'مصر',
            'India' => 'الهند',
            'China' => 'الصين',
            'Germany' => 'ألمانيا',
            'USA' => 'الولايات المتحدة',
            'UK' => 'المملكة المتحدة',
        ];
    }

    /**
     * Export suppliers to Excel
     */
    public function exportSuppliers(Request $request)
    {
        try {
            $filters = [
                'type' => $request->get('type'),
                'status' => $request->get('status'),
                'country' => $request->get('country'),
                'is_preferred' => $request->get('is_preferred')
            ];

            $filename = 'suppliers_export_' . now()->format('Y_m_d_H_i_s') . '.xlsx';

            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\SuppliersExport($filters),
                $filename
            );

        } catch (\Exception $e) {
            // If it's an AJAX request, return JSON
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'حدث خطأ أثناء تصدير الموردين: ' . $e->getMessage()
                ], 500);
            }

            // Otherwise redirect back with error
            return redirect()->back()->with('error', 'حدث خطأ أثناء تصدير الموردين: ' . $e->getMessage());
        }
    }
}
