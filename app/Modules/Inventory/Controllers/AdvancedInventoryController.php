<?php

namespace App\Modules\Inventory\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Inventory\Models\Product;
use App\Modules\Inventory\Models\ProductBatch;
use App\Modules\Inventory\Models\StockLevel;
use App\Modules\Inventory\Models\StockMovement;
use App\Modules\Inventory\Models\StockCount;
use App\Modules\Inventory\Models\ProductBarcode;
use App\Modules\Inventory\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdvancedInventoryController extends Controller
{
    /**
     * Get inventory dashboard
     */
    public function dashboard(Request $request)
    {
        $warehouseId = $request->get('warehouse_id');
        
        $stats = [
            'total_products' => Product::active()->count(),
            'total_stock_value' => $this->getTotalStockValue($warehouseId),
            'low_stock_products' => $this->getLowStockCount($warehouseId),
            'out_of_stock_products' => $this->getOutOfStockCount($warehouseId),
            'expiring_batches' => $this->getExpiringBatchesCount($warehouseId),
            'expired_batches' => $this->getExpiredBatchesCount($warehouseId),
            'recent_movements' => $this->getRecentMovements($warehouseId, 10),
            'top_moving_products' => $this->getTopMovingProducts($warehouseId, 10),
            'stock_alerts' => $this->getStockAlerts($request, $warehouseId),
        ];

        return response()->json($stats);
    }

    /**
     * Barcode scanning endpoint
     */
    public function scanBarcode(Request $request)
    {
        $validated = $request->validate([
            'barcode' => 'required|string',
            'warehouse_id' => 'nullable|exists:warehouses,id',
        ]);

        // Find product by barcode
        $product = ProductBarcode::findProductByBarcode($validated['barcode']);
        
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found for barcode: ' . $validated['barcode'],
                'message_ar' => 'المنتج غير موجود للرمز الشريطي: ' . $validated['barcode'],
            ], 404);
        }

        // Get stock information
        $stockInfo = [];
        if (isset($validated['warehouse_id'])) {
            $stockLevel = $product->stockLevels()
                                 ->where('warehouse_id', $validated['warehouse_id'])
                                 ->first();
            
            if ($stockLevel) {
                $stockInfo = [
                    'current_stock' => $stockLevel->current_stock,
                    'available_stock' => $stockLevel->available_stock,
                    'reserved_stock' => $stockLevel->reserved_stock,
                    'reorder_point' => $stockLevel->reorder_point,
                    'stock_status' => $stockLevel->stock_status,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'product' => $product->load(['category', 'manufacturer']),
            'stock_info' => $stockInfo,
            'batches' => $product->batches()
                               ->when(isset($validated['warehouse_id']), function ($q) use ($validated) {
                                   $q->where('warehouse_id', $validated['warehouse_id']);
                               })
                               ->where('quantity_remaining', '>', 0)
                               ->orderBy('expiry_date')
                               ->get(),
        ]);
    }

    /**
     * Stock adjustment
     */
    public function adjustStock(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'adjustment_type' => 'required|in:add,remove,set',
            'quantity' => 'required|numeric|min:0',
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'batch_id' => 'nullable|exists:product_batches,id',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $stockLevel = $product->stockLevels()
                             ->where('warehouse_id', $validated['warehouse_id'])
                             ->first();

        if (!$stockLevel) {
            $stockLevel = $product->stockLevels()->create([
                'warehouse_id' => $validated['warehouse_id'],
                'current_stock' => 0,
                'reserved_stock' => 0,
            ]);
        }

        $oldQuantity = $stockLevel->current_stock;
        $newQuantity = $oldQuantity;

        switch ($validated['adjustment_type']) {
            case 'add':
                $newQuantity = $oldQuantity + $validated['quantity'];
                $movementType = StockMovement::TYPE_ADJUSTMENT_IN;
                break;
            case 'remove':
                $newQuantity = max(0, $oldQuantity - $validated['quantity']);
                $movementType = StockMovement::TYPE_ADJUSTMENT_OUT;
                break;
            case 'set':
                $newQuantity = $validated['quantity'];
                $movementType = $newQuantity > $oldQuantity ? 
                    StockMovement::TYPE_ADJUSTMENT_IN : StockMovement::TYPE_ADJUSTMENT_OUT;
                break;
        }

        $difference = $newQuantity - $oldQuantity;

        // Update stock level
        $stockLevel->update(['current_stock' => $newQuantity]);

        // Create stock movement
        if ($difference != 0) {
            StockMovement::create([
                'product_id' => $validated['product_id'],
                'warehouse_id' => $validated['warehouse_id'],
                'batch_id' => $validated['batch_id'] ?? null,
                'movement_type' => $movementType,
                'quantity' => abs($difference),
                'reference_type' => 'manual_adjustment',
                'notes' => $validated['reason'] . ($validated['notes'] ? ': ' . $validated['notes'] : ''),
                'created_by' => auth()->id(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Stock adjusted successfully',
            'message_ar' => 'تم تعديل المخزون بنجاح',
            'old_quantity' => $oldQuantity,
            'new_quantity' => $newQuantity,
            'difference' => $difference,
            'stock_level' => $stockLevel->fresh(),
        ]);
    }

    /**
     * Stock transfer between warehouses
     */
    public function transferStock(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'from_warehouse_id' => 'required|exists:warehouses,id',
            'to_warehouse_id' => 'required|exists:warehouses,id|different:from_warehouse_id',
            'quantity' => 'required|numeric|min:0.01',
            'batch_id' => 'nullable|exists:product_batches,id',
            'notes' => 'nullable|string',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        
        // Check source stock
        $sourceStock = $product->stockLevels()
                              ->where('warehouse_id', $validated['from_warehouse_id'])
                              ->first();

        if (!$sourceStock || $sourceStock->available_stock < $validated['quantity']) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient stock available for transfer',
                'message_ar' => 'المخزون المتاح غير كافي للنقل',
            ], 400);
        }

        // Get or create destination stock
        $destinationStock = $product->stockLevels()
                                   ->firstOrCreate(
                                       ['warehouse_id' => $validated['to_warehouse_id']],
                                       ['current_stock' => 0, 'reserved_stock' => 0]
                                   );

        // Perform transfer
        $sourceStock->decrement('current_stock', $validated['quantity']);
        $destinationStock->increment('current_stock', $validated['quantity']);

        // Create stock movements
        $transferId = 'TRF-' . time();

        StockMovement::create([
            'product_id' => $validated['product_id'],
            'warehouse_id' => $validated['from_warehouse_id'],
            'batch_id' => $validated['batch_id'] ?? null,
            'movement_type' => StockMovement::TYPE_OUT,
            'quantity' => $validated['quantity'],
            'reference_type' => 'transfer_out',
            'reference_id' => $transferId,
            'to_warehouse_id' => $validated['to_warehouse_id'],
            'notes' => 'Transfer out: ' . ($validated['notes'] ?? ''),
            'created_by' => auth()->id(),
        ]);

        StockMovement::create([
            'product_id' => $validated['product_id'],
            'warehouse_id' => $validated['to_warehouse_id'],
            'batch_id' => $validated['batch_id'] ?? null,
            'movement_type' => StockMovement::TYPE_IN,
            'quantity' => $validated['quantity'],
            'reference_type' => 'transfer_in',
            'reference_id' => $transferId,
            'from_warehouse_id' => $validated['from_warehouse_id'],
            'notes' => 'Transfer in: ' . ($validated['notes'] ?? ''),
            'created_by' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Stock transferred successfully',
            'message_ar' => 'تم نقل المخزون بنجاح',
            'transfer_id' => $transferId,
            'source_stock' => $sourceStock->fresh(),
            'destination_stock' => $destinationStock->fresh(),
        ]);
    }

    /**
     * Generate cycle counts
     */
    public function generateCycleCounts(Request $request)
    {
        $validated = $request->validate([
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'category_id' => 'nullable|exists:product_categories,id',
            'limit' => 'nullable|integer|min:1|max:500',
        ]);

        $counts = StockCount::generateCycleCounts(
            $validated['warehouse_id'] ?? null,
            $validated['category_id'] ?? null,
            $validated['limit'] ?? 50
        );

        return response()->json([
            'success' => true,
            'message' => 'Cycle counts generated successfully',
            'message_ar' => 'تم إنشاء جرد دوري بنجاح',
            'count' => $counts->count(),
            'stock_counts' => StockCount::whereIn('id', $counts->pluck('id'))->with(['product', 'warehouse'])->get(),
        ]);
    }

    /**
     * Update stock count
     */
    public function updateStockCount(Request $request, StockCount $stockCount)
    {
        $validated = $request->validate([
            'counted_quantity' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $stockCount->update([
            'counted_quantity' => $validated['counted_quantity'],
            'difference' => $validated['counted_quantity'] - $stockCount->system_quantity,
            'status' => StockCount::STATUS_COUNTED,
            'notes' => $validated['notes'],
            'counted_by' => auth()->id(),
            'counted_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Stock count updated successfully',
            'message_ar' => 'تم تحديث الجرد بنجاح',
            'stock_count' => $stockCount->fresh(),
        ]);
    }

    /**
     * Get stock alerts
     */
    public function getStockAlerts(Request $request, $warehouseId = null)
    {
        $alerts = [];

        // Low stock alerts
        $lowStockQuery = StockLevel::lowStock()
                                  ->with(['product', 'warehouse']);
        
        if ($warehouseId) {
            $lowStockQuery->where('warehouse_id', $warehouseId);
        }

        $lowStock = $lowStockQuery->get();
        foreach ($lowStock as $stock) {
            $alerts[] = [
                'type' => 'low_stock',
                'severity' => 'warning',
                'product' => $stock->product,
                'warehouse' => $stock->warehouse,
                'current_stock' => $stock->current_stock,
                'reorder_point' => $stock->reorder_point,
                'message' => "Low stock: {$stock->product->name}",
                'message_ar' => "مخزون منخفض: {$stock->product->name_ar}",
            ];
        }

        // Expiring batches
        $expiringBatches = ProductBatch::expiringSoon(30)
                                     ->with(['product', 'warehouse'])
                                     ->when($warehouseId, function ($q) use ($warehouseId) {
                                         $q->where('warehouse_id', $warehouseId);
                                     })
                                     ->get();

        foreach ($expiringBatches as $batch) {
            $alerts[] = [
                'type' => 'expiring_batch',
                'severity' => 'warning',
                'product' => $batch->product,
                'warehouse' => $batch->warehouse,
                'batch_number' => $batch->batch_number,
                'expiry_date' => $batch->expiry_date,
                'days_until_expiry' => $batch->days_until_expiry,
                'quantity' => $batch->quantity_remaining,
                'message' => "Batch expiring soon: {$batch->batch_number}",
                'message_ar' => "دفعة تنتهي صلاحيتها قريباً: {$batch->batch_number}",
            ];
        }

        // Expired batches
        $expiredBatches = ProductBatch::expired()
                                    ->with(['product', 'warehouse'])
                                    ->when($warehouseId, function ($q) use ($warehouseId) {
                                        $q->where('warehouse_id', $warehouseId);
                                    })
                                    ->get();

        foreach ($expiredBatches as $batch) {
            $alerts[] = [
                'type' => 'expired_batch',
                'severity' => 'error',
                'product' => $batch->product,
                'warehouse' => $batch->warehouse,
                'batch_number' => $batch->batch_number,
                'expiry_date' => $batch->expiry_date,
                'quantity' => $batch->quantity_remaining,
                'message' => "Expired batch: {$batch->batch_number}",
                'message_ar' => "دفعة منتهية الصلاحية: {$batch->batch_number}",
            ];
        }

        return response()->json([
            'alerts' => $alerts,
            'summary' => [
                'total_alerts' => count($alerts),
                'low_stock_count' => $lowStock->count(),
                'expiring_batches_count' => $expiringBatches->count(),
                'expired_batches_count' => $expiredBatches->count(),
            ],
        ]);
    }

    /**
     * Helper methods
     */
    private function getTotalStockValue($warehouseId = null)
    {
        $query = StockLevel::with('product');
        
        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        return $query->get()->sum(function ($stockLevel) {
            return $stockLevel->current_stock * $stockLevel->product->cost_price;
        });
    }

    private function getLowStockCount($warehouseId = null)
    {
        $query = StockLevel::lowStock();
        
        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        return $query->count();
    }

    private function getOutOfStockCount($warehouseId = null)
    {
        $query = StockLevel::outOfStock();
        
        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        return $query->count();
    }

    private function getExpiringBatchesCount($warehouseId = null, $days = 30)
    {
        $query = ProductBatch::expiringSoon($days);
        
        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        return $query->count();
    }

    private function getExpiredBatchesCount($warehouseId = null)
    {
        $query = ProductBatch::expired();
        
        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        return $query->count();
    }

    private function getRecentMovements($warehouseId = null, $limit = 10)
    {
        $query = StockMovement::with(['product', 'warehouse'])
                             ->orderBy('created_at', 'desc');
        
        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        return $query->limit($limit)->get();
    }

    private function getTopMovingProducts($warehouseId = null, $limit = 10)
    {
        $query = StockMovement::selectRaw('product_id, SUM(quantity) as total_movement')
                             ->where('movement_type', StockMovement::TYPE_OUT)
                             ->where('created_at', '>=', now()->subDays(30))
                             ->groupBy('product_id')
                             ->orderByDesc('total_movement')
                             ->with('product');
        
        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        return $query->limit($limit)->get();
    }
}
