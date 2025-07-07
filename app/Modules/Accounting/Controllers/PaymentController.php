<?php

namespace App\Modules\Accounting\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Accounting\Models\Payment;
use App\Modules\Accounting\Models\Invoice;
use App\Modules\Accounting\Models\Account;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * Display a listing of payments
     */
    public function index(Request $request)
    {
        $query = Payment::with(['invoice', 'customer', 'supplier', 'bankAccount']);

        // Search functionality
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('payment_number', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%")
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

        // Filter by payment method
        if ($request->has('payment_method')) {
            $query->where('payment_method', $request->get('payment_method'));
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
            $query->whereBetween('payment_date', [$request->get('start_date'), $request->get('end_date')]);
        } else {
            // Default to current month
            $query->whereBetween('payment_date', [now()->startOfMonth(), now()->endOfMonth()]);
        }

        $payments = $query->orderBy('payment_date', 'desc')
                         ->orderBy('created_at', 'desc')
                         ->paginate(20);

        return response()->json([
            'payments' => $payments,
            'filters' => [
                'types' => Payment::getTypes(),
                'types_ar' => Payment::getTypesAr(),
                'methods' => Payment::getMethods(),
                'methods_ar' => Payment::getMethodsAr(),
                'statuses' => Payment::getStatuses(),
                'statuses_ar' => Payment::getStatusesAr(),
                'bank_accounts' => Account::ofType('asset')
                                        ->where('code', 'like', '11%') // Bank accounts
                                        ->active()
                                        ->get(['id', 'code', 'name', 'name_ar']),
            ]
        ]);
    }

    /**
     * Store a newly created payment
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:receipt,payment',
            'invoice_id' => 'nullable|exists:invoices,id',
            'customer_id' => 'required_if:type,receipt|exists:customers,id',
            'supplier_id' => 'required_if:type,payment|exists:suppliers,id',
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'nullable|string|max:3',
            'exchange_rate' => 'nullable|numeric|min:0',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:' . implode(',', array_keys(Payment::getMethods())),
            'reference_number' => 'nullable|string',
            'bank_account_id' => 'nullable|exists:accounts,id',
            'notes' => 'nullable|string',
        ]);

        // Validate payment amount against invoice
        if ($validated['invoice_id']) {
            $invoice = Invoice::find($validated['invoice_id']);
            if ($validated['amount'] > $invoice->balance_due) {
                return response()->json([
                    'message' => 'Payment amount cannot exceed invoice balance due',
                    'invoice_balance' => $invoice->balance_due,
                    'payment_amount' => $validated['amount'],
                ], 422);
            }
        }

        $validated['created_by'] = auth()->id();

        $payment = Payment::create($validated);

        return response()->json([
            'message' => 'Payment created successfully',
            'payment' => $payment->load(['invoice', 'customer', 'supplier', 'bankAccount'])
        ], 201);
    }

    /**
     * Display the specified payment
     */
    public function show(Payment $payment)
    {
        $payment->load([
            'invoice',
            'customer',
            'supplier',
            'bankAccount',
            'transactions.journalEntries.account',
            'creator',
            'updater'
        ]);

        return response()->json([
            'payment' => $payment
        ]);
    }

    /**
     * Update the specified payment
     */
    public function update(Request $request, Payment $payment)
    {
        if (!$payment->canBeModified()) {
            return response()->json([
                'message' => 'Only pending or failed payments can be modified'
            ], 422);
        }

        $validated = $request->validate([
            'type' => 'required|in:receipt,payment',
            'invoice_id' => 'nullable|exists:invoices,id',
            'customer_id' => 'required_if:type,receipt|exists:customers,id',
            'supplier_id' => 'required_if:type,payment|exists:suppliers,id',
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'nullable|string|max:3',
            'exchange_rate' => 'nullable|numeric|min:0',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:' . implode(',', array_keys(Payment::getMethods())),
            'reference_number' => 'nullable|string',
            'bank_account_id' => 'nullable|exists:accounts,id',
            'notes' => 'nullable|string',
        ]);

        // Validate payment amount against invoice
        if ($validated['invoice_id']) {
            $invoice = Invoice::find($validated['invoice_id']);
            $otherPayments = $invoice->payments()->where('id', '!=', $payment->id)->sum('amount');
            $availableBalance = $invoice->total_amount - $otherPayments;
            
            if ($validated['amount'] > $availableBalance) {
                return response()->json([
                    'message' => 'Payment amount cannot exceed available invoice balance',
                    'available_balance' => $availableBalance,
                    'payment_amount' => $validated['amount'],
                ], 422);
            }
        }

        $validated['updated_by'] = auth()->id();

        $payment->update($validated);

        return response()->json([
            'message' => 'Payment updated successfully',
            'payment' => $payment->fresh()->load(['invoice', 'customer', 'supplier', 'bankAccount'])
        ]);
    }

    /**
     * Remove the specified payment
     */
    public function destroy(Payment $payment)
    {
        if (!$payment->canBeModified()) {
            return response()->json([
                'message' => 'Only pending or failed payments can be deleted'
            ], 422);
        }

        $payment->delete();

        return response()->json([
            'message' => 'Payment deleted successfully'
        ]);
    }

    /**
     * Mark payment as completed
     */
    public function complete(Payment $payment)
    {
        if ($payment->status !== Payment::STATUS_PENDING) {
            return response()->json([
                'message' => 'Only pending payments can be completed'
            ], 422);
        }

        try {
            $payment->markAsCompleted();

            return response()->json([
                'message' => 'Payment completed successfully',
                'payment' => $payment->fresh()->load(['invoice', 'customer', 'supplier', 'bankAccount'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to complete payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark payment as failed
     */
    public function fail(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string',
        ]);

        if ($payment->status !== Payment::STATUS_PENDING) {
            return response()->json([
                'message' => 'Only pending payments can be marked as failed'
            ], 422);
        }

        $payment->update([
            'status' => Payment::STATUS_FAILED,
            'notes' => $payment->notes . "\nFailed: " . ($validated['notes'] ?? 'No reason provided'),
        ]);

        return response()->json([
            'message' => 'Payment marked as failed',
            'payment' => $payment->fresh()
        ]);
    }

    /**
     * Cancel payment
     */
    public function cancel(Payment $payment)
    {
        if (!$payment->canBeModified()) {
            return response()->json([
                'message' => 'Only pending or failed payments can be cancelled'
            ], 422);
        }

        $payment->update(['status' => Payment::STATUS_CANCELLED]);

        return response()->json([
            'message' => 'Payment cancelled successfully',
            'payment' => $payment->fresh()
        ]);
    }

    /**
     * Get payment statistics
     */
    public function statistics(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());

        $stats = [
            'total_payments' => Payment::whereBetween('payment_date', [$startDate, $endDate])->count(),
            'by_status' => Payment::whereBetween('payment_date', [$startDate, $endDate])
                                 ->selectRaw('status, COUNT(*) as count')
                                 ->groupBy('status')
                                 ->get(),
            'by_type' => Payment::whereBetween('payment_date', [$startDate, $endDate])
                               ->selectRaw('type, COUNT(*) as count')
                               ->groupBy('type')
                               ->get(),
            'by_method' => Payment::whereBetween('payment_date', [$startDate, $endDate])
                                 ->selectRaw('payment_method, COUNT(*) as count')
                                 ->groupBy('payment_method')
                                 ->get(),
            'total_receipts' => Payment::receipts()
                                     ->completed()
                                     ->whereBetween('payment_date', [$startDate, $endDate])
                                     ->sum('amount'),
            'total_payments_out' => Payment::payments()
                                          ->completed()
                                          ->whereBetween('payment_date', [$startDate, $endDate])
                                          ->sum('amount'),
            'net_cash_flow' => Payment::receipts()
                                     ->completed()
                                     ->whereBetween('payment_date', [$startDate, $endDate])
                                     ->sum('amount') -
                              Payment::payments()
                                     ->completed()
                                     ->whereBetween('payment_date', [$startDate, $endDate])
                                     ->sum('amount'),
        ];

        return response()->json($stats);
    }

    /**
     * Get customer payment history
     */
    public function customerHistory($customerId, Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfYear());
        $endDate = $request->get('end_date', now());

        $payments = Payment::where('customer_id', $customerId)
                          ->whereBetween('payment_date', [$startDate, $endDate])
                          ->with(['invoice'])
                          ->orderBy('payment_date', 'desc')
                          ->get();

        return response()->json([
            'customer_id' => $customerId,
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'payments' => $payments,
            'summary' => [
                'total_payments' => $payments->count(),
                'total_amount' => $payments->sum('amount'),
                'completed_amount' => $payments->where('status', Payment::STATUS_COMPLETED)->sum('amount'),
            ],
        ]);
    }

    /**
     * Get supplier payment history
     */
    public function supplierHistory($supplierId, Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfYear());
        $endDate = $request->get('end_date', now());

        $payments = Payment::where('supplier_id', $supplierId)
                          ->whereBetween('payment_date', [$startDate, $endDate])
                          ->with(['invoice'])
                          ->orderBy('payment_date', 'desc')
                          ->get();

        return response()->json([
            'supplier_id' => $supplierId,
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'payments' => $payments,
            'summary' => [
                'total_payments' => $payments->count(),
                'total_amount' => $payments->sum('amount'),
                'completed_amount' => $payments->where('status', Payment::STATUS_COMPLETED)->sum('amount'),
            ],
        ]);
    }
}
