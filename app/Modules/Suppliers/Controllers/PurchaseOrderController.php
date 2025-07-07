<?php

namespace App\Modules\Suppliers\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Suppliers\Models\PurchaseOrder;
use App\Modules\Suppliers\Models\PurchaseOrderItem;
use App\Modules\Suppliers\Models\PurchaseOrderReceiving;
use App\Modules\Suppliers\Models\Supplier;
use App\Modules\Inventory\Models\Product;
use App\Modules\Inventory\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    /**
     * Display a listing of purchase orders
     */
    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['supplier', 'warehouse', 'creator']);

        // Search functionality
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('supplier', function ($supplierQuery) use ($search) {
                      $supplierQuery->where('name', 'like', "%{$search}%")
                                   ->orWhere('name_ar', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by status
        if ($request->has('status')) {
            $query->byStatus($request->get('status'));
        }

        // Filter by supplier
        if ($request->has('supplier_id')) {
            $query->forSupplier($request->get('supplier_id'));
        }

        // Filter by warehouse
        if ($request->has('warehouse_id')) {
            $query->where('warehouse_id', $request->get('warehouse_id'));
        }

        // Filter by date range
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->dateRange($request->get('start_date'), $request->get('end_date'));
        }

        // Filter overdue orders
        if ($request->get('overdue') === 'true') {
            $query->overdue();
        }

        $orders = $query->orderBy('created_at', 'desc')
                       ->paginate(20);

        // Add calculated fields
        $orders->getCollection()->transform(function ($order) {
            $order->receiving_progress = $order->getReceivingProgress();
            $order->total_received = $order->getTotalReceivedQuantity();
            $order->total_ordered = $order->getTotalOrderedQuantity();
            return $order;
        });

        $filters = [
            'statuses' => PurchaseOrder::getStatuses(),
            'statuses_ar' => PurchaseOrder::getStatusesAr(),
            'payment_methods' => PurchaseOrder::getPaymentMethods(),
            'suppliers' => Supplier::active()->get(['id', 'name', 'name_ar', 'type']),
            'warehouses' => Warehouse::active()->get(['id', 'name', 'name_ar', 'code']),
        ];

        // Return JSON for API requests
        if ($request->expectsJson()) {
            return response()->json([
                'orders' => $orders,
                'filters' => $filters
            ]);
        }

        // Return view for web requests
        return view('purchase-orders.index', compact('orders', 'filters'));
    }

    /**
     * Store a newly created purchase order
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'expected_delivery_date' => 'nullable|date|after:today',
            'payment_method' => 'nullable|in:' . implode(',', array_keys(PurchaseOrder::getPaymentMethods())),
            'payment_terms' => 'nullable|integer|min:0|max:365',
            'notes' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'items.*.discount_percentage' => 'nullable|numeric|min:0|max:100',
            'items.*.discount_amount' => 'nullable|numeric|min:0',
            'items.*.notes' => 'nullable|string',
            'items.*.supplier_sku' => 'nullable|string|max:255',
            'items.*.expected_expiry_date' => 'nullable|date|after:today',
        ]);

        DB::beginTransaction();
        try {
            // Get supplier for default values
            $supplier = Supplier::findOrFail($validated['supplier_id']);
            
            // Set default payment terms and delivery date
            $paymentTerms = $validated['payment_terms'] ?? $supplier->payment_terms;
            $expectedDeliveryDate = $validated['expected_delivery_date'] ?? now()->addDays(7);

            // Create the purchase order
            $order = PurchaseOrder::create([
                'supplier_id' => $validated['supplier_id'],
                'warehouse_id' => $validated['warehouse_id'],
                'expected_delivery_date' => $expectedDeliveryDate,
                'payment_method' => $validated['payment_method'] ?? PurchaseOrder::PAYMENT_METHOD_CREDIT,
                'payment_terms' => $paymentTerms,
                'notes' => $validated['notes'] ?? null,
                'terms_conditions' => $validated['terms_conditions'] ?? null,
                'created_by' => auth()->id(),
            ]);

            // Add order items
            foreach ($validated['items'] as $itemData) {
                $order->items()->create([
                    'product_id' => $itemData['product_id'],
                    'quantity' => $itemData['quantity'],
                    'unit_cost' => $itemData['unit_cost'],
                    'discount_percentage' => $itemData['discount_percentage'] ?? 0,
                    'discount_amount' => $itemData['discount_amount'] ?? 0,
                    'notes' => $itemData['notes'] ?? null,
                    'supplier_sku' => $itemData['supplier_sku'] ?? null,
                    'expected_expiry_date' => $itemData['expected_expiry_date'] ?? null,
                ]);
            }

            // Calculate totals (this is done automatically in the model)
            $order->calculateTotals();

            DB::commit();

            return response()->json([
                'message' => 'Purchase order created successfully',
                'order' => $order->load(['supplier', 'warehouse', 'items.product'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create purchase order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified purchase order
     */
    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load([
            'supplier',
            'warehouse',
            'items.product.category',
            'items.product.manufacturer',
            'receivedItems.receiver',
            'creator'
        ]);

        // Add calculated fields
        $purchaseOrder->receiving_progress = $purchaseOrder->getReceivingProgress();
        $purchaseOrder->total_received = $purchaseOrder->getTotalReceivedQuantity();
        $purchaseOrder->total_ordered = $purchaseOrder->getTotalOrderedQuantity();

        // Add receiving details for each item
        $purchaseOrder->items->each(function ($item) {
            $item->total_received = $item->getTotalReceivedQuantity();
            $item->remaining_quantity = $item->getRemainingQuantity();
            $item->receiving_progress = $item->getReceivingProgress();
            $item->is_fully_received = $item->isFullyReceived();
        });

        // Return JSON for API requests
        if (request()->expectsJson()) {
            return response()->json([
                'order' => $purchaseOrder
            ]);
        }

        // Return view for web requests
        return view('purchase-orders.show', compact('purchaseOrder'));
    }

    /**
     * Print the specified purchase order
     */
    public function print(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load([
            'supplier',
            'warehouse',
            'items.product.category',
            'items.product.manufacturer',
            'receivedItems.receiver',
            'creator'
        ]);

        // Add calculated fields
        $purchaseOrder->receiving_progress = $purchaseOrder->getReceivingProgress();
        $purchaseOrder->total_received = $purchaseOrder->getTotalReceivedQuantity();
        $purchaseOrder->total_ordered = $purchaseOrder->getTotalOrderedQuantity();

        // Add receiving details for each item
        $purchaseOrder->items->each(function ($item) {
            $item->total_received = $item->getTotalReceivedQuantity();
            $item->remaining_quantity = $item->getRemainingQuantity();
            $item->receiving_progress = $item->getReceivingProgress();
            $item->is_fully_received = $item->isFullyReceived();
        });

        // Return print view
        return view('purchase-orders.print', compact('purchaseOrder'));
    }

    /**
     * Update the specified purchase order
     */
    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        // Check if order can be edited
        if (!$purchaseOrder->canBeEdited()) {
            return response()->json([
                'message' => 'Purchase order cannot be edited in current status'
            ], 422);
        }

        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'expected_delivery_date' => 'nullable|date|after:today',
            'payment_method' => 'nullable|in:' . implode(',', array_keys(PurchaseOrder::getPaymentMethods())),
            'payment_terms' => 'nullable|integer|min:0|max:365',
            'notes' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
        ]);

        $validated['updated_by'] = auth()->id();

        $purchaseOrder->update($validated);

        return response()->json([
            'message' => 'Purchase order updated successfully',
            'order' => $purchaseOrder->fresh()->load(['supplier', 'warehouse', 'items.product'])
        ]);
    }

    /**
     * Remove the specified purchase order
     */
    public function destroy(PurchaseOrder $purchaseOrder)
    {
        // Check if order can be deleted
        if (!$purchaseOrder->canBeEdited()) {
            return response()->json([
                'message' => 'Purchase order cannot be deleted in current status'
            ], 422);
        }

        // Check if order has received items
        if ($purchaseOrder->receivedItems()->exists()) {
            return response()->json([
                'message' => 'Cannot delete purchase order with received items'
            ], 422);
        }

        $purchaseOrder->delete();

        return response()->json([
            'message' => 'Purchase order deleted successfully'
        ]);
    }

    /**
     * Add item to purchase order
     */
    public function addItem(Request $request, PurchaseOrder $purchaseOrder)
    {
        if (!$purchaseOrder->canBeEdited()) {
            return response()->json([
                'message' => 'Purchase order cannot be modified in current status'
            ], 422);
        }

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'unit_cost' => 'required|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'supplier_sku' => 'nullable|string|max:255',
            'expected_expiry_date' => 'nullable|date|after:today',
        ]);

        // Check if item already exists
        $existingItem = $purchaseOrder->items()->where('product_id', $validated['product_id'])->first();
        if ($existingItem) {
            return response()->json([
                'message' => 'Product already exists in purchase order. Use update instead.'
            ], 422);
        }

        $item = $purchaseOrder->items()->create($validated);

        return response()->json([
            'message' => 'Item added successfully',
            'item' => $item->load('product')
        ], 201);
    }

    /**
     * Update purchase order item
     */
    public function updateItem(Request $request, PurchaseOrder $purchaseOrder, PurchaseOrderItem $item)
    {
        if (!$purchaseOrder->canBeEdited()) {
            return response()->json([
                'message' => 'Purchase order cannot be modified in current status'
            ], 422);
        }

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            'unit_cost' => 'required|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'supplier_sku' => 'nullable|string|max:255',
            'expected_expiry_date' => 'nullable|date|after:today',
        ]);

        $item->update($validated);

        return response()->json([
            'message' => 'Item updated successfully',
            'item' => $item->fresh()->load('product')
        ]);
    }

    /**
     * Remove item from purchase order
     */
    public function removeItem(PurchaseOrder $purchaseOrder, PurchaseOrderItem $item)
    {
        if (!$purchaseOrder->canBeEdited()) {
            return response()->json([
                'message' => 'Purchase order cannot be modified in current status'
            ], 422);
        }

        // Check if this is the last item
        if ($purchaseOrder->items()->count() <= 1) {
            return response()->json([
                'message' => 'Cannot remove the last item from purchase order'
            ], 422);
        }

        $item->delete();

        return response()->json([
            'message' => 'Item removed successfully'
        ]);
    }

    /**
     * Submit purchase order for approval
     */
    public function submit(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== PurchaseOrder::STATUS_DRAFT) {
            return response()->json([
                'message' => 'Only draft orders can be submitted'
            ], 422);
        }

        $purchaseOrder->update([
            'status' => PurchaseOrder::STATUS_PENDING,
            'updated_by' => auth()->id(),
        ]);

        return response()->json([
            'message' => 'Purchase order submitted successfully',
            'order' => $purchaseOrder->fresh()
        ]);
    }

    /**
     * Confirm purchase order
     */
    public function confirm(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== PurchaseOrder::STATUS_PENDING) {
            return response()->json([
                'message' => 'Only pending orders can be confirmed'
            ], 422);
        }

        $purchaseOrder->confirm();

        return response()->json([
            'message' => 'Purchase order confirmed successfully',
            'order' => $purchaseOrder->fresh()
        ]);
    }

    /**
     * Cancel purchase order
     */
    public function cancel(Request $request, PurchaseOrder $purchaseOrder)
    {
        if (!$purchaseOrder->canBeCancelled()) {
            return response()->json([
                'message' => 'Purchase order cannot be cancelled in current status'
            ], 422);
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        $purchaseOrder->update([
            'status' => PurchaseOrder::STATUS_CANCELLED,
            'notes' => ($purchaseOrder->notes ? $purchaseOrder->notes . "\n\n" : '') . 
                      "Cancelled: " . $validated['reason'] . " (by " . auth()->user()->name . " at " . now() . ")",
            'updated_by' => auth()->id(),
        ]);

        return response()->json([
            'message' => 'Purchase order cancelled successfully',
            'order' => $purchaseOrder->fresh()
        ]);
    }

    /**
     * Receive items for purchase order
     */
    public function receiveItems(Request $request, PurchaseOrder $purchaseOrder)
    {
        if (!$purchaseOrder->canBeReceived()) {
            return response()->json([
                'message' => 'Purchase order cannot be received in current status'
            ], 422);
        }

        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.received_quantity' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'items.*.batch_number' => 'nullable|string|max:255',
            'items.*.expiry_date' => 'nullable|date|after:today',
            'items.*.manufacture_date' => 'nullable|date|before_or_equal:today',
            'items.*.location' => 'nullable|string|max:255',
            'items.*.notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $receivedItems = [];

            foreach ($validated['items'] as $itemData) {
                // Validate that product exists in the order
                $orderItem = $purchaseOrder->items()->where('product_id', $itemData['product_id'])->first();
                if (!$orderItem) {
                    throw new \Exception("Product ID {$itemData['product_id']} is not in this purchase order");
                }

                // Check if receiving quantity doesn't exceed remaining quantity
                $remainingQuantity = $orderItem->getRemainingQuantity();
                if ($itemData['received_quantity'] > $remainingQuantity) {
                    throw new \Exception("Cannot receive {$itemData['received_quantity']} units. Only {$remainingQuantity} units remaining for product {$orderItem->product->name}");
                }

                // Create receiving record
                $receiving = PurchaseOrderReceiving::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'product_id' => $itemData['product_id'],
                    'received_quantity' => $itemData['received_quantity'],
                    'unit_cost' => $itemData['unit_cost'],
                    'batch_number' => $itemData['batch_number'] ?? null,
                    'expiry_date' => $itemData['expiry_date'] ?? null,
                    'manufacture_date' => $itemData['manufacture_date'] ?? null,
                    'location' => $itemData['location'] ?? null,
                    'notes' => $itemData['notes'] ?? null,
                    'received_by' => auth()->id(),
                ]);

                $receivedItems[] = $receiving->load('product');
            }

            DB::commit();

            return response()->json([
                'message' => 'Items received successfully',
                'received_items' => $receivedItems,
                'order' => $purchaseOrder->fresh()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to receive items: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Perform quality check on received items
     */
    public function qualityCheck(Request $request, PurchaseOrder $purchaseOrder, PurchaseOrderReceiving $receiving)
    {
        $validated = $request->validate([
            'status' => 'required|in:passed,failed',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            if ($validated['status'] === 'passed') {
                $receiving->passQualityCheck($validated['notes']);
            } else {
                $receiving->failQualityCheck($validated['notes']);
            }

            DB::commit();

            return response()->json([
                'message' => 'Quality check completed successfully',
                'receiving' => $receiving->fresh(),
                'order' => $purchaseOrder->fresh()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to complete quality check: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get purchase order statistics
     */
    public function statistics(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now());

        $stats = [
            'total_orders' => PurchaseOrder::whereBetween('order_date', [$startDate, $endDate])->count(),
            'total_amount' => PurchaseOrder::where('status', 'completed')
                                          ->whereBetween('order_date', [$startDate, $endDate])
                                          ->sum('total_amount'),
            'pending_orders' => PurchaseOrder::where('status', 'pending')->count(),
            'overdue_orders' => PurchaseOrder::overdue()->count(),
            'by_status' => PurchaseOrder::whereBetween('order_date', [$startDate, $endDate])
                                       ->selectRaw('status, COUNT(*) as count, SUM(total_amount) as total_amount')
                                       ->groupBy('status')
                                       ->get(),
            'by_supplier' => PurchaseOrder::with('supplier')
                                         ->whereBetween('order_date', [$startDate, $endDate])
                                         ->selectRaw('supplier_id, COUNT(*) as count, SUM(total_amount) as total_amount')
                                         ->groupBy('supplier_id')
                                         ->orderByDesc('total_amount')
                                         ->take(10)
                                         ->get(),
            'daily_orders' => PurchaseOrder::whereBetween('order_date', [$startDate, $endDate])
                                          ->selectRaw('DATE(order_date) as date, COUNT(*) as count, SUM(total_amount) as total_amount')
                                          ->groupBy('date')
                                          ->orderBy('date')
                                          ->get(),
        ];

        return response()->json($stats);
    }

    /**
     * Get supplier purchase history
     */
    public function supplierHistory(Supplier $supplier, Request $request)
    {
        $startDate = $request->get('start_date', now()->subYear());
        $endDate = $request->get('end_date', now());

        $orders = $supplier->purchaseOrders()
                          ->with(['items.product'])
                          ->whereBetween('order_date', [$startDate, $endDate])
                          ->orderBy('order_date', 'desc')
                          ->paginate(20);

        $summary = [
            'total_orders' => $supplier->purchaseOrders()
                                     ->whereBetween('order_date', [$startDate, $endDate])
                                     ->count(),
            'total_amount' => $supplier->purchaseOrders()
                                     ->where('status', 'completed')
                                     ->whereBetween('order_date', [$startDate, $endDate])
                                     ->sum('total_amount'),
            'average_order_value' => $supplier->purchaseOrders()
                                            ->whereBetween('order_date', [$startDate, $endDate])
                                            ->avg('total_amount') ?? 0,
            'performance_rating' => $supplier->getPerformanceRating(),
        ];

        return response()->json([
            'orders' => $orders,
            'summary' => $summary,
        ]);
    }
}
