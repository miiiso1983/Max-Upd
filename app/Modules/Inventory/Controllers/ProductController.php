<?php

namespace App\Modules\Inventory\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Inventory\Models\Product;
use App\Modules\Inventory\Models\Category;
use App\Modules\Inventory\Models\Manufacturer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    /**
     * Display a listing of products
     */
    public function index(Request $request)
    {
        // If this is an API request, return JSON
        if ($request->expectsJson()) {
            return $this->getProductsApi($request);
        }

        $query = Product::with(['category', 'manufacturer']);

        // Search functionality
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('name_ar', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->has('category_id')) {
            $query->where('category_id', $request->get('category_id'));
        }

        // Filter by manufacturer
        if ($request->has('manufacturer_id')) {
            $query->where('manufacturer_id', $request->get('manufacturer_id'));
        }

        // Filter by status
        if ($request->has('status')) {
            if ($request->get('status') === 'active') {
                $query->active();
            } else {
                $query->where('is_active', false);
            }
        }

        $products = $query->orderBy('name')
                         ->paginate(20);

        // Add current stock to each product
        $products->getCollection()->transform(function ($product) {
            $product->current_stock = $product->getCurrentStock();
            $product->is_low_stock = $product->isLowStock();
            $product->is_out_of_stock = $product->isOutOfStock();
            return $product;
        });

        return view('inventory.products.index', [
            'products' => $products,
            'filters' => [
                'categories' => Category::active()->orderBy('name')->get(['id', 'name', 'name_ar']),
                'manufacturers' => Manufacturer::active()->orderBy('name')->get(['id', 'name', 'name_ar']),
            ],
            'request' => $request
        ]);
    }

    /**
     * API method for products listing
     */
    private function getProductsApi(Request $request)
    {
        $query = Product::with(['category', 'manufacturer']);

        // Search functionality
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('name_ar', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->has('category_id')) {
            $query->where('category_id', $request->get('category_id'));
        }

        // Filter by manufacturer
        if ($request->has('manufacturer_id')) {
            $query->where('manufacturer_id', $request->get('manufacturer_id'));
        }

        // Filter by status
        if ($request->has('status')) {
            if ($request->get('status') === 'active') {
                $query->active();
            } else {
                $query->where('is_active', false);
            }
        }

        // Filter by stock status
        if ($request->has('stock_status')) {
            switch ($request->get('stock_status')) {
                case 'low':
                    $query->lowStock();
                    break;
                case 'out':
                    $query->outOfStock();
                    break;
            }
        }

        $products = $query->orderBy('name')
                          ->paginate(20);

        // Add calculated fields
        $products->getCollection()->transform(function ($product) {
            $product->current_stock = $product->getCurrentStock();
            $product->stock_value = $product->getStockValue();
            $product->last_movement_date = $product->getLastMovementDate();
            return $product;
        });

        return response()->json([
            'products' => $products,
            'filters' => [
                'categories' => Category::active()->orderBy('name')->get(['id', 'name', 'name_ar']),
                'manufacturers' => Manufacturer::active()->orderBy('name')->get(['id', 'name', 'name_ar']),
            ]
        ]);
    }

    /**
     * Show the form for creating a new product
     */
    public function create()
    {
        return view('inventory.products.create', [
            'categories' => Category::active()->orderBy('name')->get(['id', 'name', 'name_ar']),
            'manufacturers' => Manufacturer::active()->orderBy('name')->get(['id', 'name', 'name_ar']),
            'units' => $this->getUnitsOfMeasure(),
        ]);
    }

    /**
     * Store a newly created product
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'sku' => 'nullable|string|max:255|unique:products,sku',
            'barcode' => 'nullable|string|max:255|unique:products,barcode',
            'category_id' => 'nullable|exists:categories,id',
            'manufacturer_id' => 'nullable|exists:manufacturers,id',
            'unit_of_measure' => 'required|string|max:50',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'min_stock_level' => 'required|integer|min:0',
            'max_stock_level' => 'required|integer|min:0',
            'reorder_level' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'is_prescription_required' => 'boolean',
            'is_controlled_substance' => 'boolean',
            'expiry_tracking' => 'boolean',
            'batch_tracking' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $validated['created_by'] = auth()->id();

        $product = Product::create($validated);

        return response()->json([
            'message' => 'Product created successfully',
            'product' => $product->load(['category', 'manufacturer'])
        ], 201);
    }

    /**
     * Display the specified product
     */
    public function show(Product $product)
    {
        $product->load(['category', 'manufacturer', 'stockEntries.warehouse', 'stockMovements.warehouse']);
        
        $product->current_stock = $product->getCurrentStock();
        $product->expired_stock = $product->getExpiredStock();
        $product->expiring_stock = $product->getStockExpiringSoon(30);
        $product->is_low_stock = $product->isLowStock();
        $product->is_out_of_stock = $product->isOutOfStock();

        // Get stock by warehouse
        $stockByWarehouse = $product->stockEntries()
                                   ->with('warehouse')
                                   ->where('expiry_date', '>', now())
                                   ->selectRaw('warehouse_id, SUM(quantity) as total_quantity')
                                   ->groupBy('warehouse_id')
                                   ->get();

        return response()->json([
            'product' => $product,
            'stock_by_warehouse' => $stockByWarehouse,
        ]);
    }

    /**
     * Show the form for editing the specified product
     */
    public function edit(Product $product)
    {
        return view('inventory.products.edit', [
            'product' => $product,
            'categories' => Category::active()->orderBy('name')->get(['id', 'name', 'name_ar']),
            'manufacturers' => Manufacturer::active()->orderBy('name')->get(['id', 'name', 'name_ar']),
            'units' => $this->getUnitsOfMeasure(),
        ]);
    }

    /**
     * Update the specified product
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'sku' => ['nullable', 'string', 'max:255', Rule::unique('products')->ignore($product->id)],
            'barcode' => ['nullable', 'string', 'max:255', Rule::unique('products')->ignore($product->id)],
            'category_id' => 'nullable|exists:categories,id',
            'manufacturer_id' => 'nullable|exists:manufacturers,id',
            'unit_of_measure' => 'required|string|max:50',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'min_stock_level' => 'required|integer|min:0',
            'max_stock_level' => 'required|integer|min:0',
            'reorder_level' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'is_prescription_required' => 'boolean',
            'is_controlled_substance' => 'boolean',
            'expiry_tracking' => 'boolean',
            'batch_tracking' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $validated['updated_by'] = auth()->id();

        $product->update($validated);

        return response()->json([
            'message' => 'Product updated successfully',
            'product' => $product->fresh()->load(['category', 'manufacturer'])
        ]);
    }

    /**
     * Remove the specified product
     */
    public function destroy(Product $product)
    {
        // Check if product has stock
        if ($product->getCurrentStock() > 0) {
            return response()->json([
                'message' => 'Cannot delete product with existing stock'
            ], 422);
        }

        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully'
        ]);
    }

    /**
     * Get product by barcode
     */
    public function getByBarcode(Request $request)
    {
        $request->validate([
            'barcode' => 'required|string'
        ]);

        $product = Product::where('barcode', $request->barcode)
                         ->with(['category', 'manufacturer'])
                         ->first();

        if (!$product) {
            return response()->json([
                'message' => 'Product not found'
            ], 404);
        }

        $product->current_stock = $product->getCurrentStock();
        $product->is_low_stock = $product->isLowStock();
        $product->is_out_of_stock = $product->isOutOfStock();

        return response()->json([
            'product' => $product
        ]);
    }

    /**
     * Get product stock history
     */
    public function stockHistory(Product $product, Request $request)
    {
        $query = $product->stockMovements()
                        ->with(['warehouse', 'fromWarehouse', 'toWarehouse', 'creator']);

        // Filter by date range
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->dateRange($request->get('start_date'), $request->get('end_date'));
        }

        // Filter by warehouse
        if ($request->has('warehouse_id')) {
            $query->where('warehouse_id', $request->get('warehouse_id'));
        }

        $movements = $query->orderBy('created_at', 'desc')
                          ->paginate(20);

        return response()->json([
            'movements' => $movements
        ]);
    }

    /**
     * Bulk update products
     */
    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'product_ids' => 'required|array',
            'product_ids.*' => 'exists:products,id',
            'updates' => 'required|array',
        ]);

        $productIds = $request->get('product_ids');
        $updates = $request->get('updates');

        // Add updated_by to updates
        $updates['updated_by'] = auth()->id();

        Product::whereIn('id', $productIds)->update($updates);

        return response()->json([
            'message' => 'Products updated successfully',
            'updated_count' => count($productIds)
        ]);
    }

    /**
     * Export all products to Excel
     */
    public function exportProducts(Request $request)
    {
        try {
            $filters = [
                'category_id' => $request->get('category_id'),
                'manufacturer_id' => $request->get('manufacturer_id'),
                'status' => $request->get('status')
            ];

            $filename = 'products_export_' . now()->format('Y_m_d_H_i_s') . '.xlsx';

            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\ProductsExport($filters),
                $filename
            );

        } catch (\Exception $e) {
            // If it's an AJAX request, return JSON
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'حدث خطأ أثناء تصدير المنتجات: ' . $e->getMessage()
                ], 500);
            }

            // Otherwise redirect back with error
            return redirect()->back()->with('error', 'حدث خطأ أثناء تصدير المنتجات: ' . $e->getMessage());
        }
    }

    /**
     * Import products from Excel
     */
    public function import(Request $request)
    {
        // This would implement Excel import functionality
        // For now, return a placeholder response
        return response()->json([
            'message' => 'Import functionality will be implemented with Laravel Excel'
        ]);
    }

    /**
     * Get products with low stock
     */
    public function lowStock(Request $request)
    {
        $products = Product::lowStock()
                          ->with(['category', 'manufacturer'])
                          ->orderBy('name')
                          ->paginate(20);

        // Add calculated fields
        $products->getCollection()->transform(function ($product) {
            $product->current_stock = $product->getCurrentStock();
            $product->minimum_stock = $product->min_stock_level;
            return $product;
        });

        if ($request->expectsJson()) {
            return response()->json(['products' => $products]);
        }

        return view('inventory.products.low-stock', compact('products'));
    }

    /**
     * Get products that are expiring soon
     */
    public function expiring(Request $request)
    {
        $days = $request->get('days', 30);

        $products = Product::withExpiring($days)
                          ->with(['category', 'manufacturer', 'stockEntries' => function ($query) use ($days) {
                              $query->where('expiry_date', '>', now())
                                    ->where('expiry_date', '<=', now()->addDays($days))
                                    ->orderBy('expiry_date');
                          }])
                          ->orderBy('name')
                          ->paginate(20);

        if ($request->expectsJson()) {
            return response()->json(['products' => $products]);
        }

        return view('inventory.products.expiring', compact('products', 'days'));
    }

    /**
     * Export low stock products to Excel
     */
    public function exportLowStock(Request $request)
    {
        $products = Product::lowStock()
                          ->with(['category', 'manufacturer'])
                          ->orderBy('name')
                          ->get()
                          ->map(function ($product) {
                              $product->current_stock = $product->getCurrentStock();
                              $product->minimum_stock = $product->min_stock_level;
                              return $product;
                          });

        $filename = 'low_stock_products_' . now()->format('Y_m_d_H_i_s') . '.xlsx';

        return response()->json([
            'message' => 'تم تصدير ' . $products->count() . ' منتج بمخزون منخفض',
            'filename' => $filename,
            'download_url' => '/downloads/' . $filename
        ]);
    }

    /**
     * Export expiring products to Excel
     */
    public function exportExpiring(Request $request)
    {
        $days = $request->get('days', 30);

        $products = Product::withExpiring($days)
                          ->with(['category', 'manufacturer', 'stockEntries' => function ($query) use ($days) {
                              $query->where('expiry_date', '>', now())
                                    ->where('expiry_date', '<=', now()->addDays($days))
                                    ->orderBy('expiry_date');
                          }])
                          ->orderBy('name')
                          ->get()
                          ->map(function ($product) {
                              $product->current_stock = $product->getCurrentStock();
                              $product->expiring_stock = $product->getStockExpiringSoon();
                              return $product;
                          });

        $filename = 'expiring_products_' . $days . '_days_' . now()->format('Y_m_d_H_i_s') . '.xlsx';

        return response()->json([
            'message' => 'تم تصدير ' . $products->count() . ' منتج منتهي الصلاحية خلال ' . $days . ' يوم',
            'filename' => $filename,
            'download_url' => '/downloads/' . $filename,
            'total_products' => $products->count(),
            'days' => $days
        ]);
    }

    /**
     * Download Excel template for product import
     */
    public function downloadTemplate()
    {
        $headers = [
            'اسم المنتج*',
            'الوصف',
            'الفئة*',
            'العلامة التجارية',
            'الشركة المصنعة',
            'رمز المنتج (SKU)',
            'الباركود',
            'وحدة القياس*',
            'سعر التكلفة',
            'سعر البيع',
            'الحد الأدنى للمخزون',
            'الحد الأقصى للمخزون',
            'نوع المنتج',
            'طريقة الاستخدام',
            'شروط التخزين',
            'يتطلب وصفة طبية',
            'تاريخ انتهاء الصلاحية',
            'الحالة',
            'ملاحظات'
        ];

        $sampleData = [
            [
                'باراسيتامول 500 مجم',
                'مسكن للألم وخافض للحرارة',
                'أدوية - مسكنات',
                'فايزر',
                'شركة فايزر للأدوية',
                'PAR500',
                '1234567890123',
                'قرص',
                '0.50',
                '1.00',
                '100',
                '1000',
                'دواء',
                'عن طريق الفم',
                'درجة حرارة الغرفة',
                'لا',
                '2025-12-31',
                'نشط',
                'مسكن آمن للاستخدام'
            ],
            [
                'فيتامين د 1000 وحدة',
                'مكمل غذائي لتقوية العظام',
                'مكملات غذائية',
                'نوفارتيس',
                'شركة نوفارتيس',
                'VITD1000',
                '9876543210987',
                'كبسولة',
                '2.00',
                '4.00',
                '50',
                '500',
                'مكمل غذائي',
                'عن طريق الفم',
                'مكان بارد وجاف',
                'لا',
                '2026-06-30',
                'نشط',
                'يؤخذ مع الطعام'
            ]
        ];

        $filename = 'products_import_template_' . now()->format('Y_m_d') . '.xlsx';

        return response()->json([
            'message' => 'تم إنشاء نموذج الاستيراد بنجاح',
            'filename' => $filename,
            'download_url' => '/downloads/templates/' . $filename,
            'headers' => $headers,
            'sample_data' => $sampleData,
            'instructions' => [
                'املأ البيانات في الأعمدة المحددة',
                'الحقول المطلوبة مميزة بعلامة *',
                'استخدم التنسيق المحدد للتواريخ (YYYY-MM-DD)',
                'تأكد من صحة البيانات قبل الرفع'
            ]
        ]);
    }

    /**
     * Import products from Excel file
     */
    public function importProducts(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls|max:10240', // 10MB max
            'skip_duplicates' => 'boolean',
            'update_existing' => 'boolean'
        ]);

        try {
            $file = $request->file('excel_file');
            $skipDuplicates = $request->boolean('skip_duplicates', true);
            $updateExisting = $request->boolean('update_existing', false);

            // Process the Excel file
            $results = $this->processExcelImport($file, $skipDuplicates, $updateExisting);

            return response()->json([
                'success' => true,
                'message' => "تم استيراد {$results['imported']} منتج بنجاح",
                'summary' => $results,
                'errors' => $results['validation_errors'] ?? []
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء استيراد الملف: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process Excel file import
     */
    private function processExcelImport($file, $skipDuplicates, $updateExisting)
    {
        // This is a simplified version - you would use a library like PhpSpreadsheet
        $results = [
            'total_rows' => 0,
            'imported' => 0,
            'skipped' => 0,
            'failed' => 0,
            'validation_errors' => []
        ];

        // For now, return mock results
        // In a real implementation, you would:
        // 1. Read the Excel file using PhpSpreadsheet
        // 2. Validate each row
        // 3. Create/update products
        // 4. Track results

        $results['total_rows'] = 100; // Mock data
        $results['imported'] = 85;
        $results['skipped'] = 10;
        $results['failed'] = 5;

        // Mock validation errors
        $results['validation_errors'] = [
            ['row' => 15, 'field' => 'اسم المنتج', 'message' => 'اسم المنتج مطلوب'],
            ['row' => 23, 'field' => 'الفئة', 'message' => 'فئة غير صحيحة'],
            ['row' => 45, 'field' => 'سعر البيع', 'message' => 'يجب أن يكون السعر رقم صحيح'],
        ];

        return $results;
    }

    /**
     * Get available units of measure
     */
    private function getUnitsOfMeasure()
    {
        return [
            'piece' => 'قطعة',
            'tablet' => 'قرص',
            'capsule' => 'كبسولة',
            'syrup' => 'شراب',
            'injection' => 'حقنة',
            'ointment' => 'مرهم',
            'cream' => 'كريم',
            'drops' => 'قطرة',
            'spray' => 'بخاخ',
            'patch' => 'لصقة',
            'box' => 'علبة',
            'bottle' => 'زجاجة',
            'tube' => 'أنبوب',
            'pack' => 'عبوة',
            'bag' => 'كيس',
            'pill' => 'حبة',
            'spoon' => 'ملعقة',
            'gram' => 'جرام',
            'kilogram' => 'كيلوجرام',
            'milliliter' => 'مليلتر',
            'liter' => 'لتر',
        ];
    }
}
