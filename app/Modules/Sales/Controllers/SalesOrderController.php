<?php

namespace App\Modules\Sales\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Sales\Models\SalesOrder;
use App\Modules\Sales\Models\SalesOrderItem;
use App\Modules\Sales\Models\Customer;
use App\Modules\Sales\Models\Invoice;
use App\Modules\Inventory\Models\Product;
use App\Modules\Inventory\Models\Warehouse;
use App\Modules\Inventory\Models\StockMovement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SalesOrderController extends Controller
{
    /**
     * Display a listing of sales orders
     */
    public function index(Request $request)
    {
        $query = SalesOrder::with(['customer', 'salesRep', 'warehouse']);

        // Search functionality
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($customerQuery) use ($search) {
                      $customerQuery->where('name', 'like', "%{$search}%")
                                   ->orWhere('name_ar', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by status
        if ($request->has('status')) {
            $query->byStatus($request->get('status'));
        }

        // Filter by customer
        if ($request->has('customer_id')) {
            $query->forCustomer($request->get('customer_id'));
        }

        // Filter by date range
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->dateRange($request->get('start_date'), $request->get('end_date'));
        }

        // Filter by sales rep
        if ($request->has('sales_rep_id')) {
            $query->where('sales_rep_id', $request->get('sales_rep_id'));
        }

        $orders = $query->orderBy('created_at', 'desc')
                       ->paginate(20);

        $filters = [
            'statuses' => SalesOrder::getStatuses(),
            'statuses_ar' => SalesOrder::getStatusesAr(),
            'payment_methods' => SalesOrder::getPaymentMethods(),
            'customers' => Customer::active()->get(['id', 'name', 'name_ar', 'type']),
            'warehouses' => Warehouse::active()->get(['id', 'name', 'name_ar']),
        ];

        // Return JSON for API requests
        if ($request->expectsJson()) {
            return response()->json([
                'orders' => $orders,
                'filters' => $filters
            ]);
        }

        // Return view for web requests
        return view('sales.orders.index', compact('orders', 'filters'));
    }

    /**
     * Show the form for creating a new sales order
     */
    public function create()
    {
        $customers = Customer::active()->get();
        $products = Product::active()->get();
        $warehouses = Warehouse::all();
        $salesReps = User::role('sales-rep')->get();

        return view('sales.orders.create', compact('customers', 'products', 'warehouses', 'salesReps'));
    }

    /**
     * Store a newly created sales order
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'delivery_date' => 'nullable|date|after:today',
            'payment_method' => 'required|in:' . implode(',', array_keys(SalesOrder::getPaymentMethods())),
            'payment_terms' => 'nullable|integer|min:0|max:365',
            'warehouse_id' => 'required|exists:warehouses,id',
            'sales_rep_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
            'shipping_address' => 'nullable|string',
            'billing_address' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_percentage' => 'nullable|numeric|min:0|max:100',
            'items.*.notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Get customer for default values
            $customer = Customer::findOrFail($validated['customer_id']);
            
            // Set default payment terms from customer if not provided
            if (!isset($validated['payment_terms'])) {
                $validated['payment_terms'] = $customer->payment_terms;
            }

            // Create the sales order
            $order = SalesOrder::create([
                'customer_id' => $validated['customer_id'],
                'order_date' => now(),
                'delivery_date' => $validated['delivery_date'] ?? null,
                'payment_method' => $validated['payment_method'],
                'payment_terms' => $validated['payment_terms'],
                'warehouse_id' => $validated['warehouse_id'],
                'sales_rep_id' => $validated['sales_rep_id'] ?? auth()->id(),
                'notes' => $validated['notes'] ?? null,
                'shipping_address' => $validated['shipping_address'] ?? $customer->address,
                'billing_address' => $validated['billing_address'] ?? $customer->address,
                'created_by' => auth()->id(),
            ]);

            // Add order items
            foreach ($validated['items'] as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);
                
                // Apply customer discount if no item discount specified
                $discountPercentage = $itemData['discount_percentage'] ?? $customer->discount_percentage;
                
                $order->items()->create([
                    'product_id' => $itemData['product_id'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'discount_percentage' => $discountPercentage,
                    'notes' => $itemData['notes'] ?? null,
                ]);
            }

            // Calculate totals (this is done automatically in the model)
            $order->calculateTotals();

            DB::commit();

            return response()->json([
                'message' => 'Sales order created successfully',
                'order' => $order->load(['customer', 'items.product', 'warehouse', 'salesRep'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create sales order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified sales order
     */
    public function show(SalesOrder $order)
    {

        $order->load([
            'customer',
            'items.product.category',
            'items.product.manufacturer',
            'warehouse',
            'salesRep',
            'invoice.payments',
            'creator'
        ]);

        // Add stock availability for each item
        $order->items->each(function ($item) use ($order) {
            $item->available_stock = $item->product->getCurrentStockInWarehouse($order->warehouse_id);
            $item->total_available_stock = $item->product->getCurrentStock();
        });

        // Return view for browser requests, JSON for API requests
        if (request()->expectsJson()) {
            return response()->json([
                'order' => $order
            ]);
        }

        return view('sales.orders.show', compact('order'));
    }

    /**
     * Print the specified sales order
     */
    public function print(SalesOrder $order)
    {
        $order->load([
            'customer',
            'items.product.category',
            'items.product.manufacturer',
            'warehouse',
            'salesRep',
            'creator'
        ]);

        // Add stock availability for each item
        $order->items->each(function ($item) use ($order) {
            $item->available_stock = $item->product->getCurrentStockInWarehouse($order->warehouse_id);
            $item->total_available_stock = $item->product->getCurrentStock();
        });

        return view('sales.orders.print', compact('order'));
    }

    /**
     * Update the specified sales order
     */
    public function update(Request $request, SalesOrder $salesOrder)
    {
        // Check if order can be edited
        if (!$salesOrder->canBeEdited()) {
            return response()->json([
                'message' => 'Order cannot be edited in current status'
            ], 422);
        }

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'delivery_date' => 'nullable|date|after:today',
            'payment_method' => 'required|in:' . implode(',', array_keys(SalesOrder::getPaymentMethods())),
            'payment_terms' => 'nullable|integer|min:0|max:365',
            'warehouse_id' => 'required|exists:warehouses,id',
            'sales_rep_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
            'shipping_address' => 'nullable|string',
            'billing_address' => 'nullable|string',
        ]);

        $validated['updated_by'] = auth()->id();

        $salesOrder->update($validated);

        return response()->json([
            'message' => 'Sales order updated successfully',
            'order' => $salesOrder->fresh()->load(['customer', 'items.product', 'warehouse', 'salesRep'])
        ]);
    }

    /**
     * Remove the specified sales order
     */
    public function destroy(SalesOrder $salesOrder)
    {
        // Check if order can be deleted
        if (!$salesOrder->canBeEdited()) {
            return response()->json([
                'message' => 'Order cannot be deleted in current status'
            ], 422);
        }

        // Check if order has been invoiced
        if ($salesOrder->isInvoiced()) {
            return response()->json([
                'message' => 'Cannot delete invoiced order'
            ], 422);
        }

        $salesOrder->delete();

        return response()->json([
            'message' => 'Sales order deleted successfully'
        ]);
    }

    /**
     * Add item to sales order
     */
    public function addItem(Request $request, SalesOrder $salesOrder)
    {
        if (!$salesOrder->canBeEdited()) {
            return response()->json([
                'message' => 'Order cannot be modified in current status'
            ], 422);
        }

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string',
        ]);

        // Check if item already exists
        $existingItem = $salesOrder->items()->where('product_id', $validated['product_id'])->first();
        if ($existingItem) {
            return response()->json([
                'message' => 'Product already exists in order. Use update instead.'
            ], 422);
        }

        $item = $salesOrder->items()->create($validated);

        return response()->json([
            'message' => 'Item added successfully',
            'item' => $item->load('product')
        ], 201);
    }

    /**
     * Update order item
     */
    public function updateItem(Request $request, SalesOrder $salesOrder, SalesOrderItem $item)
    {
        if (!$salesOrder->canBeEdited()) {
            return response()->json([
                'message' => 'Order cannot be modified in current status'
            ], 422);
        }

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string',
        ]);

        $item->update($validated);

        return response()->json([
            'message' => 'Item updated successfully',
            'item' => $item->fresh()->load('product')
        ]);
    }

    /**
     * Remove item from sales order
     */
    public function removeItem(SalesOrder $salesOrder, SalesOrderItem $item)
    {
        if (!$salesOrder->canBeEdited()) {
            return response()->json([
                'message' => 'Order cannot be modified in current status'
            ], 422);
        }

        // Check if this is the last item
        if ($salesOrder->items()->count() <= 1) {
            return response()->json([
                'message' => 'Cannot remove the last item from order'
            ], 422);
        }

        $item->delete();

        return response()->json([
            'message' => 'Item removed successfully'
        ]);
    }

    /**
     * Confirm the sales order
     */
    public function confirm(SalesOrder $salesOrder)
    {
        if ($salesOrder->status !== SalesOrder::STATUS_PENDING) {
            return response()->json([
                'message' => 'Only pending orders can be confirmed'
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Check stock availability
            $salesOrder->checkStockAvailability();

            // Confirm the order
            $salesOrder->confirm();

            DB::commit();

            return response()->json([
                'message' => 'Order confirmed successfully',
                'order' => $salesOrder->fresh()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to confirm order: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Cancel the sales order
     */
    public function cancel(Request $request, SalesOrder $salesOrder)
    {
        if (!$salesOrder->canBeCancelled()) {
            return response()->json([
                'message' => 'Order cannot be cancelled in current status'
            ], 422);
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        $salesOrder->update([
            'status' => SalesOrder::STATUS_CANCELLED,
            'internal_notes' => ($salesOrder->internal_notes ? $salesOrder->internal_notes . "\n\n" : '') . 
                              "Cancelled: " . $validated['reason'] . " (by " . auth()->user()->name . " at " . now() . ")",
            'updated_by' => auth()->id(),
        ]);

        return response()->json([
            'message' => 'Order cancelled successfully',
            'order' => $salesOrder->fresh()
        ]);
    }

    /**
     * Process the order (allocate stock)
     */
    public function process(SalesOrder $salesOrder)
    {
        if ($salesOrder->status !== SalesOrder::STATUS_CONFIRMED) {
            return response()->json([
                'message' => 'Only confirmed orders can be processed'
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Check stock availability again
            $salesOrder->checkStockAvailability();

            // Allocate stock for each item
            foreach ($salesOrder->items as $item) {
                $this->allocateStock($item, $salesOrder->warehouse_id);
            }

            // Update order status
            $salesOrder->update([
                'status' => SalesOrder::STATUS_PROCESSING,
                'updated_by' => auth()->id(),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Order processed successfully',
                'order' => $salesOrder->fresh()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to process order: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Ship the order
     */
    public function ship(Request $request, SalesOrder $salesOrder)
    {
        if ($salesOrder->status !== SalesOrder::STATUS_PROCESSING) {
            return response()->json([
                'message' => 'Only processing orders can be shipped'
            ], 422);
        }

        $validated = $request->validate([
            'tracking_number' => 'nullable|string|max:255',
            'shipping_method' => 'nullable|string|max:255',
            'shipping_notes' => 'nullable|string',
        ]);

        $salesOrder->update([
            'status' => SalesOrder::STATUS_SHIPPED,
            'internal_notes' => ($salesOrder->internal_notes ? $salesOrder->internal_notes . "\n\n" : '') .
                              "Shipped: " . ($validated['tracking_number'] ?? 'No tracking') .
                              " via " . ($validated['shipping_method'] ?? 'Standard') .
                              " (by " . auth()->user()->name . " at " . now() . ")",
            'updated_by' => auth()->id(),
        ]);

        return response()->json([
            'message' => 'Order shipped successfully',
            'order' => $salesOrder->fresh()
        ]);
    }

    /**
     * Mark order as delivered
     */
    public function deliver(Request $request, SalesOrder $salesOrder)
    {
        if ($salesOrder->status !== SalesOrder::STATUS_SHIPPED) {
            return response()->json([
                'message' => 'Only shipped orders can be marked as delivered'
            ], 422);
        }

        $validated = $request->validate([
            'delivery_notes' => 'nullable|string',
            'received_by' => 'nullable|string|max:255',
        ]);

        $salesOrder->update([
            'status' => SalesOrder::STATUS_DELIVERED,
            'internal_notes' => ($salesOrder->internal_notes ? $salesOrder->internal_notes . "\n\n" : '') .
                              "Delivered: " . ($validated['received_by'] ? "Received by " . $validated['received_by'] : 'Delivered') .
                              " (confirmed by " . auth()->user()->name . " at " . now() . ")",
            'updated_by' => auth()->id(),
        ]);

        return response()->json([
            'message' => 'Order marked as delivered successfully',
            'order' => $salesOrder->fresh()
        ]);
    }

    /**
     * Create invoice from sales order
     */
    public function createInvoice(SalesOrder $salesOrder)
    {
        if ($salesOrder->isInvoiced()) {
            return response()->json([
                'message' => 'Order is already invoiced'
            ], 422);
        }

        if (!in_array($salesOrder->status, [SalesOrder::STATUS_CONFIRMED, SalesOrder::STATUS_PROCESSING, SalesOrder::STATUS_SHIPPED, SalesOrder::STATUS_DELIVERED])) {
            return response()->json([
                'message' => 'Order must be confirmed or further along to create invoice'
            ], 422);
        }

        DB::beginTransaction();
        try {
            $invoice = Invoice::createFromSalesOrder($salesOrder);

            // Update invoice status to pending
            $invoice->update(['status' => Invoice::STATUS_PENDING]);

            DB::commit();

            return response()->json([
                'message' => 'Invoice created successfully',
                'invoice' => $invoice->load(['customer', 'items.product'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create invoice: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get order fulfillment status
     */
    public function fulfillmentStatus(SalesOrder $salesOrder)
    {
        $items = $salesOrder->items->map(function ($item) use ($salesOrder) {
            $availableStock = $item->product->getCurrentStockInWarehouse($salesOrder->warehouse_id);

            return [
                'product_id' => $item->product_id,
                'product_name' => $item->product->name,
                'product_name_ar' => $item->product->name_ar,
                'sku' => $item->product->sku,
                'ordered_quantity' => $item->quantity,
                'available_stock' => $availableStock,
                'can_fulfill' => $availableStock >= $item->quantity,
                'shortage' => max(0, $item->quantity - $availableStock),
            ];
        });

        $canFulfillCompletely = $items->every(function ($item) {
            return $item['can_fulfill'];
        });

        return response()->json([
            'order_id' => $salesOrder->id,
            'order_number' => $salesOrder->order_number,
            'status' => $salesOrder->status,
            'can_fulfill_completely' => $canFulfillCompletely,
            'items' => $items,
            'warehouse' => $salesOrder->warehouse,
        ]);
    }

    /**
     * Allocate stock for an order item
     */
    private function allocateStock(SalesOrderItem $item, $warehouseId)
    {
        $product = $item->product;
        $requiredQuantity = $item->quantity;

        // Get available stock entries (FIFO - First In, First Out)
        $stockEntries = $product->stockEntries()
                              ->where('warehouse_id', $warehouseId)
                              ->where('expiry_date', '>', now())
                              ->where('quantity', '>', 0)
                              ->orderBy('expiry_date')
                              ->orderBy('created_at')
                              ->get();

        $allocatedQuantity = 0;

        foreach ($stockEntries as $stockEntry) {
            if ($allocatedQuantity >= $requiredQuantity) {
                break;
            }

            $availableInEntry = $stockEntry->quantity;
            $toAllocate = min($availableInEntry, $requiredQuantity - $allocatedQuantity);

            // Create stock movement for allocation
            StockMovement::create([
                'product_id' => $product->id,
                'warehouse_id' => $warehouseId,
                'stock_entry_id' => $stockEntry->id,
                'type' => StockMovement::TYPE_OUT,
                'reason' => StockMovement::REASON_SALE,
                'quantity' => -$toAllocate, // Negative for outgoing
                'unit_cost' => $stockEntry->purchase_price,
                'reference_type' => SalesOrder::class,
                'reference_id' => $item->sales_order_id,
                'batch_number' => $stockEntry->batch_number,
                'expiry_date' => $stockEntry->expiry_date,
                'notes' => "Allocated for order {$item->salesOrder->order_number}",
                'created_by' => auth()->id(),
            ]);

            // Update stock entry quantity
            $stockEntry->decrement('quantity', $toAllocate);

            $allocatedQuantity += $toAllocate;

            // Update item with batch and expiry info (for the first allocation)
            if (!$item->batch_number && $stockEntry->batch_number) {
                $item->update([
                    'batch_number' => $stockEntry->batch_number,
                    'expiry_date' => $stockEntry->expiry_date,
                ]);
            }
        }

        if ($allocatedQuantity < $requiredQuantity) {
            throw new \Exception("Insufficient stock for product {$product->name}. Required: {$requiredQuantity}, Available: {$allocatedQuantity}");
        }
    }

    /**
     * Get order items
     */
    public function items(SalesOrder $salesOrder)
    {
        $items = $salesOrder->items()->with(['product'])->get();

        return response()->json(['items' => $items]);
    }

    /**
     * Export sales orders
     */
    public function export(Request $request)
    {
        $format = $request->get('format', 'excel');

        if ($format === 'pdf') {
            return $this->exportPdf();
        }

        return $this->exportExcel();
    }

    /**
     * Export orders to Excel
     */
    private function exportExcel()
    {
        // This would use Laravel Excel to export orders
        return response()->json([
            'message' => 'Excel export functionality will be implemented with Laravel Excel'
        ]);
    }

    /**
     * Export orders to PDF
     */
    private function exportPdf()
    {
        // This would use DomPDF to export orders
        return response()->json([
            'message' => 'PDF export functionality will be implemented with DomPDF'
        ]);
    }
}
