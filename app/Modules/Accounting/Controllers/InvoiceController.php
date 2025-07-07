<?php

namespace App\Modules\Accounting\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Accounting\Models\Invoice;
use App\Modules\Accounting\Models\InvoiceItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    /**
     * Display a listing of invoices
     */
    public function index(Request $request)
    {
        $query = Invoice::with(['customer', 'supplier', 'items']);

        // Search functionality
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($customerQuery) use ($search) {
                      $customerQuery->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('supplier', function ($supplierQuery) use ($search) {
                      $supplierQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->get('type'));
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->get('status'));
        }

        // Filter by customer
        if ($request->has('customer_id')) {
            $query->where('customer_id', $request->get('customer_id'));
        }

        // Filter by supplier
        if ($request->has('supplier_id')) {
            $query->where('supplier_id', $request->get('supplier_id'));
        }

        // Filter by date range
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('issue_date', [$request->get('start_date'), $request->get('end_date')]);
        }

        // Filter overdue invoices
        if ($request->get('overdue') === 'true') {
            $query->overdue();
        }

        $invoices = $query->orderBy('issue_date', 'desc')
                         ->orderBy('created_at', 'desc')
                         ->paginate(20);

        // Add calculated fields
        $invoices->getCollection()->transform(function ($invoice) {
            $invoice->days_overdue = $invoice->getDaysOverdue();
            $invoice->payment_percentage = $invoice->getPaymentPercentage();
            return $invoice;
        });

        return response()->json([
            'invoices' => $invoices,
            'filters' => [
                'types' => Invoice::getTypes(),
                'types_ar' => Invoice::getTypesAr(),
                'statuses' => Invoice::getStatuses(),
                'statuses_ar' => Invoice::getStatusesAr(),
            ]
        ]);
    }

    /**
     * Store a newly created invoice
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:sales,purchase',
            'customer_id' => 'required_if:type,sales|exists:customers,id',
            'supplier_id' => 'required_if:type,purchase|exists:suppliers,id',
            'issue_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:issue_date',
            'payment_terms' => 'nullable|string',
            'discount_amount' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:3',
            'exchange_rate' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.description' => 'required|string',
            'items.*.description_ar' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_amount' => 'nullable|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        DB::beginTransaction();
        try {
            $validated['created_by'] = auth()->id();

            $invoice = Invoice::create($validated);

            // Create invoice items
            foreach ($validated['items'] as $itemData) {
                $itemData['invoice_id'] = $invoice->id;
                $itemData['created_by'] = auth()->id();
                InvoiceItem::create($itemData);
            }

            // Calculate totals
            $invoice->calculateTotals();

            DB::commit();

            return response()->json([
                'message' => 'Invoice created successfully',
                'invoice' => $invoice->load(['customer', 'supplier', 'items'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => 'Failed to create invoice',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified invoice
     */
    public function show(Invoice $invoice)
    {
        $invoice->load([
            'customer',
            'supplier',
            'items.product',
            'payments',
            'transactions.journalEntries.account',
            'creator',
            'updater'
        ]);

        // Add calculated fields
        $invoice->days_overdue = $invoice->getDaysOverdue();
        $invoice->payment_percentage = $invoice->getPaymentPercentage();

        return response()->json([
            'invoice' => $invoice
        ]);
    }

    /**
     * Update the specified invoice
     */
    public function update(Request $request, Invoice $invoice)
    {
        if ($invoice->status === Invoice::STATUS_PAID) {
            return response()->json([
                'message' => 'Cannot update paid invoice'
            ], 422);
        }

        $validated = $request->validate([
            'type' => 'required|in:sales,purchase',
            'customer_id' => 'required_if:type,sales|exists:customers,id',
            'supplier_id' => 'required_if:type,purchase|exists:suppliers,id',
            'issue_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:issue_date',
            'payment_terms' => 'nullable|string',
            'discount_amount' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:3',
            'exchange_rate' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.description' => 'required|string',
            'items.*.description_ar' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_amount' => 'nullable|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        DB::beginTransaction();
        try {
            $validated['updated_by'] = auth()->id();

            $invoice->update($validated);

            // Delete existing items
            $invoice->items()->delete();

            // Create new items
            foreach ($validated['items'] as $itemData) {
                $itemData['invoice_id'] = $invoice->id;
                $itemData['created_by'] = auth()->id();
                InvoiceItem::create($itemData);
            }

            // Calculate totals
            $invoice->calculateTotals();

            DB::commit();

            return response()->json([
                'message' => 'Invoice updated successfully',
                'invoice' => $invoice->fresh()->load(['customer', 'supplier', 'items'])
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => 'Failed to update invoice',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified invoice
     */
    public function destroy(Invoice $invoice)
    {
        if ($invoice->payments()->exists()) {
            return response()->json([
                'message' => 'Cannot delete invoice with payments'
            ], 422);
        }

        if ($invoice->status === Invoice::STATUS_PAID) {
            return response()->json([
                'message' => 'Cannot delete paid invoice'
            ], 422);
        }

        $invoice->delete();

        return response()->json([
            'message' => 'Invoice deleted successfully'
        ]);
    }

    /**
     * Send invoice to customer/supplier
     */
    public function send(Invoice $invoice)
    {
        if ($invoice->status !== Invoice::STATUS_DRAFT) {
            return response()->json([
                'message' => 'Only draft invoices can be sent'
            ], 422);
        }

        // Create accounting transaction
        $invoice->createAccountingTransaction();

        $invoice->update(['status' => Invoice::STATUS_SENT]);

        return response()->json([
            'message' => 'Invoice sent successfully',
            'invoice' => $invoice->fresh()
        ]);
    }

    /**
     * Mark invoice as paid
     */
    public function markAsPaid(Invoice $invoice)
    {
        $invoice->update([
            'status' => Invoice::STATUS_PAID,
            'paid_amount' => $invoice->total_amount,
            'balance_due' => 0,
        ]);

        return response()->json([
            'message' => 'Invoice marked as paid',
            'invoice' => $invoice->fresh()
        ]);
    }

    /**
     * Cancel invoice
     */
    public function cancel(Invoice $invoice)
    {
        if ($invoice->status === Invoice::STATUS_PAID) {
            return response()->json([
                'message' => 'Cannot cancel paid invoice'
            ], 422);
        }

        $invoice->update(['status' => Invoice::STATUS_CANCELLED]);

        return response()->json([
            'message' => 'Invoice cancelled successfully',
            'invoice' => $invoice->fresh()
        ]);
    }

    /**
     * Get invoice statistics
     */
    public function statistics(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());

        $stats = [
            'total_invoices' => Invoice::whereBetween('issue_date', [$startDate, $endDate])->count(),
            'by_status' => Invoice::whereBetween('issue_date', [$startDate, $endDate])
                                 ->selectRaw('status, COUNT(*) as count')
                                 ->groupBy('status')
                                 ->get(),
            'by_type' => Invoice::whereBetween('issue_date', [$startDate, $endDate])
                               ->selectRaw('type, COUNT(*) as count')
                               ->groupBy('type')
                               ->get(),
            'total_amount' => Invoice::whereBetween('issue_date', [$startDate, $endDate])->sum('total_amount'),
            'paid_amount' => Invoice::whereBetween('issue_date', [$startDate, $endDate])->sum('paid_amount'),
            'outstanding_amount' => Invoice::whereBetween('issue_date', [$startDate, $endDate])->sum('balance_due'),
            'overdue_count' => Invoice::overdue()->count(),
            'overdue_amount' => Invoice::overdue()->sum('balance_due'),
        ];

        return response()->json($stats);
    }
}
