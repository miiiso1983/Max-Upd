<?php

namespace App\Modules\Sales\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Accounting\Models\Payment;
use App\Modules\Sales\Models\Invoice;
use App\Modules\Sales\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    /**
     * Display a listing of payments
     */
    public function index(Request $request)
    {
        $query = Payment::with(['invoice', 'customer', 'creator']);

        // Search functionality
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('payment_number', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($customerQuery) use ($search) {
                      $customerQuery->where('name', 'like', "%{$search}%")
                                   ->orWhere('name_ar', 'like', "%{$search}%");
                  })
                  ->orWhereHas('invoice', function ($invoiceQuery) use ($search) {
                      $invoiceQuery->where('invoice_number', 'like', "%{$search}%");
                  });
            });
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

        // Filter by date range
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('payment_date', [$request->get('start_date'), $request->get('end_date')]);
        }

        $payments = $query->orderBy('created_at', 'desc')
                         ->paginate(20);

        // Get all payments for statistics (without pagination)
        $allPayments = Payment::with(['invoice', 'customer', 'creator'])->get();

        $paymentMethods = [
            'cash' => 'Cash',
            'bank_transfer' => 'Bank Transfer',
            'check' => 'Check',
            'credit_card' => 'Credit Card',
            'mobile_payment' => 'Mobile Payment',
        ];

        $paymentMethodsAr = [
            'cash' => 'نقدي',
            'bank_transfer' => 'تحويل مصرفي',
            'check' => 'شيك',
            'credit_card' => 'بطاقة ائتمان',
            'mobile_payment' => 'دفع عبر الهاتف',
        ];

        $filters = [
            'statuses' => Payment::getStatuses(),
            'statuses_ar' => Payment::getStatusesAr(),
            'payment_methods' => $paymentMethods,
            'payment_methods_ar' => $paymentMethodsAr,
            'customers' => Customer::active()->get(['id', 'name', 'name_ar', 'type']),
        ];

        // Return JSON for API requests
        if ($request->expectsJson()) {
            return response()->json([
                'payments' => $payments,
                'filters' => $filters
            ]);
        }

        // Return view for web requests
        return view('sales.payments.index', compact('payments', 'allPayments', 'filters'));
    }

    /**
     * Show the form for creating a new payment
     */
    public function create(Request $request)
    {
        $invoice = null;
        $customer = null;

        // If invoice_id is provided, load the invoice
        if ($request->has('invoice_id')) {
            $invoice = Invoice::with('customer')->findOrFail($request->get('invoice_id'));
            $customer = $invoice->customer;
        }

        // Get customers for dropdown
        $customers = Customer::active()->orderBy('name')->get(['id', 'name', 'name_ar']);

        // Get unpaid/partially paid invoices
        $unpaidInvoices = Invoice::with('customer')
            ->where('status', '!=', 'paid')
            ->when($customer, function ($query) use ($customer) {
                return $query->where('customer_id', $customer->id);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $paymentMethods = [
            'cash' => 'نقدي',
            'bank_transfer' => 'تحويل مصرفي',
            'check' => 'شيك',
            'credit_card' => 'بطاقة ائتمان',
            'mobile_payment' => 'دفع عبر الهاتف',
        ];

        return view('sales.payments.create', [
            'invoice' => $invoice,
            'customer' => $customer,
            'customers' => $customers,
            'unpaidInvoices' => $unpaidInvoices,
            'paymentMethods' => $paymentMethods,
        ]);
    }

    /**
     * Show the form for creating a new payment for a specific invoice
     */
    public function createForInvoice(Invoice $invoice)
    {
        // Load the invoice with customer
        $invoice->load('customer');

        // Get customers for dropdown
        $customers = Customer::active()->orderBy('name')->get(['id', 'name', 'name_ar']);

        // Get unpaid/partially paid invoices for this customer
        $unpaidInvoices = Invoice::with('customer')
            ->where('customer_id', $invoice->customer_id)
            ->where('status', '!=', 'paid')
            ->orderBy('created_at', 'desc')
            ->get();

        $paymentMethods = [
            'cash' => 'نقدي',
            'bank_transfer' => 'تحويل مصرفي',
            'check' => 'شيك',
            'credit_card' => 'بطاقة ائتمان',
            'mobile_payment' => 'دفع عبر الهاتف',
        ];

        return view('sales.payments.create', [
            'invoice' => $invoice,
            'customer' => $invoice->customer,
            'customers' => $customers,
            'unpaidInvoices' => $unpaidInvoices,
            'paymentMethods' => $paymentMethods,
        ]);
    }

    /**
     * Store a newly created payment
     */
    public function store(Request $request)
    {
        \Log::info('Payment store method called', $request->all());

        $validated = $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,bank_transfer,check,credit_card,mobile_payment',
            'payment_date' => 'nullable|date',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            \Log::info('Starting payment creation process');

            $invoice = Invoice::findOrFail($validated['invoice_id']);
            \Log::info('Invoice found', ['invoice_id' => $invoice->id]);

            // Check if payment amount doesn't exceed remaining amount
            $remainingAmount = $invoice->total_amount - $invoice->paid_amount;
            if ($validated['amount'] > $remainingAmount) {
                \Log::error('Payment amount exceeds remaining amount', [
                    'payment_amount' => $validated['amount'],
                    'remaining_amount' => $remainingAmount
                ]);
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'مبلغ الدفعة لا يمكن أن يتجاوز المبلغ المتبقي');
            }

            \Log::info('Creating payment with data', [
                'type' => 'receipt',
                'invoice_id' => $validated['invoice_id'],
                'customer_id' => $invoice->customer_id,
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'],
            ]);

            // Create payment
            $payment = Payment::create([
                'type' => 'receipt', // This is a receipt (money received)
                'invoice_id' => $validated['invoice_id'],
                'customer_id' => $invoice->customer_id,
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'payment_date' => $validated['payment_date'] ?? now(),
                'reference_number' => $validated['reference_number'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'status' => 'completed',
                'created_by' => auth()->id(),
            ]);

            \Log::info('Payment created successfully', ['payment_id' => $payment->id]);

            // Update invoice paid amount
            $invoice->paid_amount += $validated['amount'];
            if ($invoice->paid_amount >= $invoice->total_amount) {
                $invoice->status = 'paid';
            }
            $invoice->save();

            \Log::info('Invoice updated', ['new_paid_amount' => $invoice->paid_amount]);

            DB::commit();
            \Log::info('Transaction committed successfully');

            return redirect()->route('sales.payments.index')
                ->with('success', 'تم تسجيل الدفعة بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Payment creation failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()
                ->withInput()
                ->with('error', 'فشل في تسجيل الدفعة: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified payment
     */
    public function show(Payment $payment)
    {
        $payment->load(['invoice.items.product', 'customer', 'creator']);

        return view('sales.payments.show', compact('payment'));
    }

    /**
     * Print payment receipt
     */
    public function printReceipt(Payment $payment)
    {
        $payment->load(['invoice.items.product', 'customer', 'creator']);

        return view('sales.payments.print', compact('payment'));
    }

    /**
     * Download payment receipt as PDF
     */
    public function downloadPdf(Payment $payment)
    {
        $payment->load(['invoice.items.product', 'customer', 'creator']);

        $pdf = \PDF::loadView('sales.payments.pdf', compact('payment'));

        return $pdf->download('payment-receipt-' . $payment->payment_number . '.pdf');
    }

    /**
     * Generate WhatsApp message for payment receipt
     */
    public function generateWhatsAppMessage(Payment $payment)
    {
        try {
            $payment->load(['invoice', 'customer']);

            $customerName = $payment->customer->name_ar ?: $payment->customer->name;
            $companyName = config('app.name', 'MaxCon SaaS');

            // Generate receipt URL
            $receiptUrl = route('sales.payments.print', $payment);

            // Create WhatsApp message
            $message = "🧾 *إيصال استلام دفعة*\n\n";
            $message .= "عزيزي/عزيزتي *{$customerName}*\n\n";
            $message .= "نشكركم لتعاملكم معنا. إليكم تفاصيل الدفعة:\n\n";
            $message .= "📋 *تفاصيل الدفعة:*\n";
            $message .= "• رقم الإيصال: `{$payment->payment_number}`\n";
            $message .= "• المبلغ: *" . number_format($payment->amount) . " {$payment->currency}*\n";
            $message .= "• تاريخ الدفع: {$payment->payment_date->format('Y-m-d')}\n";
            $message .= "• طريقة الدفع: " . $this->getPaymentMethodArabic($payment->payment_method) . "\n";

            if ($payment->invoice) {
                $message .= "\n📄 *تفاصيل الفاتورة:*\n";
                $message .= "• رقم الفاتورة: `{$payment->invoice->invoice_number}`\n";
                $message .= "• إجمالي الفاتورة: " . number_format($payment->invoice->total_amount) . " د.ع\n";
                $message .= "• المبلغ المدفوع: " . number_format($payment->invoice->paid_amount) . " د.ع\n";
                $remainingAmount = $payment->invoice->total_amount - $payment->invoice->paid_amount;
                if ($remainingAmount > 0) {
                    $message .= "• المبلغ المتبقي: " . number_format($remainingAmount) . " د.ع\n";
                } else {
                    $message .= "• ✅ *تم سداد الفاتورة بالكامل*\n";
                }
            }

            if ($payment->reference_number) {
                $message .= "• رقم المرجع: `{$payment->reference_number}`\n";
            }

            $message .= "\n🔗 *رابط الإيصال:*\n";
            $message .= $receiptUrl . "\n\n";

            $message .= "📞 للاستفسارات يرجى التواصل معنا\n";
            $message .= "شكراً لثقتكم بنا\n\n";
            $message .= "_{$companyName}_";

            return response()->json([
                'success' => true,
                'message' => $message,
                'receipt_url' => $receiptUrl
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في تحضير الرسالة: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get payment method in Arabic
     */
    private function getPaymentMethodArabic($method)
    {
        $methods = [
            'cash' => 'نقدي',
            'bank_transfer' => 'تحويل مصرفي',
            'check' => 'شيك',
            'credit_card' => 'بطاقة ائتمان',
            'mobile_payment' => 'دفع عبر الهاتف',
        ];

        return $methods[$method] ?? $method;
    }

    /**
     * Show the form for editing the specified payment
     */
    public function edit(Payment $payment)
    {
        // Only pending payments can be edited
        if ($payment->status !== 'pending') {
            return redirect()->route('sales.payments.index')
                ->with('error', 'يمكن تعديل المدفوعات المعلقة فقط');
        }

        // Get customers for dropdown
        $customers = Customer::active()->orderBy('name')->get(['id', 'name', 'name_ar']);

        // Get unpaid/partially paid invoices for this customer
        $unpaidInvoices = Invoice::with('customer')
            ->where('customer_id', $payment->customer_id)
            ->where('status', '!=', 'paid')
            ->orderBy('created_at', 'desc')
            ->get();

        $paymentMethods = [
            'cash' => 'نقدي',
            'bank_transfer' => 'تحويل مصرفي',
            'check' => 'شيك',
            'credit_card' => 'بطاقة ائتمان',
            'mobile_payment' => 'دفع عبر الهاتف',
        ];

        return view('sales.payments.edit', [
            'payment' => $payment,
            'customers' => $customers,
            'unpaidInvoices' => $unpaidInvoices,
            'paymentMethods' => $paymentMethods,
        ]);
    }

    /**
     * Update the specified payment
     */
    public function update(Request $request, Payment $payment)
    {
        // Only pending payments can be updated
        if ($payment->status !== Payment::STATUS_PENDING) {
            return response()->json([
                'message' => 'Only pending payments can be updated'
            ], 422);
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,bank_transfer,check,credit_card,mobile_payment',
            'payment_date' => 'nullable|date',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'bank_name' => 'nullable|string|max:255',
            'check_number' => 'nullable|string|max:255',
            'check_date' => 'nullable|date',
        ]);

        // Check if new amount doesn't exceed balance due
        $invoice = $payment->invoice;
        $currentBalance = $invoice->balance_due + $payment->amount; // Add back current payment amount
        
        if ($validated['amount'] > $currentBalance) {
            return response()->json([
                'message' => 'Payment amount cannot exceed balance due'
            ], 422);
        }

        $payment->update($validated);

        return response()->json([
            'message' => 'Payment updated successfully',
            'payment' => $payment->fresh()->load(['invoice', 'customer'])
        ]);
    }

    /**
     * Remove the specified payment
     */
    public function destroy(Payment $payment)
    {
        // Only pending payments can be deleted
        if ($payment->status !== Payment::STATUS_PENDING) {
            return response()->json([
                'message' => 'Only pending payments can be deleted'
            ], 422);
        }

        $payment->delete();

        return response()->json([
            'message' => 'Payment deleted successfully'
        ]);
    }

    /**
     * Confirm a pending payment
     */
    public function confirm(Payment $payment)
    {
        if ($payment->status !== Payment::STATUS_PENDING) {
            return response()->json([
                'message' => 'Only pending payments can be confirmed'
            ], 422);
        }

        DB::beginTransaction();
        try {
            $payment->markAsCompleted();

            DB::commit();

            return response()->json([
                'message' => 'Payment confirmed successfully',
                'payment' => $payment->fresh()->load(['invoice', 'customer'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to confirm payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel a pending payment
     */
    public function cancel(Request $request, Payment $payment)
    {
        if ($payment->status !== Payment::STATUS_PENDING) {
            return response()->json([
                'message' => 'Only pending payments can be cancelled'
            ], 422);
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        $payment->update([
            'status' => Payment::STATUS_CANCELLED,
            'notes' => ($payment->notes ? $payment->notes . "\n\n" : '') . 
                      "Cancelled: " . $validated['reason'] . " (by " . auth()->user()->name . " at " . now() . ")",
        ]);

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
        $endDate = $request->get('end_date', now());

        $stats = [
            'total_payments' => Payment::completed()
                                     ->whereBetween('payment_date', [$startDate, $endDate])
                                     ->sum('amount'),
            'total_count' => Payment::completed()
                                   ->whereBetween('payment_date', [$startDate, $endDate])
                                   ->count(),
            'pending_payments' => Payment::pending()->sum('amount'),
            'pending_count' => Payment::pending()->count(),
            'by_method' => Payment::completed()
                                 ->whereBetween('payment_date', [$startDate, $endDate])
                                 ->selectRaw('payment_method, COUNT(*) as count, SUM(amount) as total_amount')
                                 ->groupBy('payment_method')
                                 ->get(),
            'daily_payments' => Payment::completed()
                                      ->whereBetween('payment_date', [$startDate, $endDate])
                                      ->selectRaw('DATE(payment_date) as date, SUM(amount) as total_amount, COUNT(*) as count')
                                      ->groupBy('date')
                                      ->orderBy('date')
                                      ->get(),
        ];

        return response()->json($stats);
    }

    /**
     * Get customer payment history
     */
    public function customerHistory(Customer $customer, Request $request)
    {
        $startDate = $request->get('start_date', now()->subYear());
        $endDate = $request->get('end_date', now());

        $payments = $customer->invoices()
                            ->with(['payments' => function ($query) use ($startDate, $endDate) {
                                $query->whereBetween('payment_date', [$startDate, $endDate])
                                      ->where('status', Payment::STATUS_COMPLETED);
                            }])
                            ->get()
                            ->flatMap->payments
                            ->sortByDesc('payment_date');

        $summary = [
            'total_paid' => $payments->sum('amount'),
            'payment_count' => $payments->count(),
            'average_payment' => $payments->count() > 0 ? $payments->avg('amount') : 0,
            'by_method' => $payments->groupBy('payment_method')
                                  ->map(function ($methodPayments, $method) {
                                      return [
                                          'method' => $method,
                                          'count' => $methodPayments->count(),
                                          'total_amount' => $methodPayments->sum('amount'),
                                      ];
                                  })
                                  ->values(),
        ];

        return response()->json([
            'payments' => $payments->values(),
            'summary' => $summary,
        ]);
    }

    /**
     * Refund a payment
     */
    public function refund(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'amount' => 'nullable|numeric|min:0.01',
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $refundAmount = $validated['amount'] ?? $payment->amount;

        if ($refundAmount > $payment->amount) {
            return response()->json([
                'message' => 'Refund amount cannot exceed payment amount'
            ], 422);
        }

        if ($payment->status !== Payment::STATUS_COMPLETED) {
            return response()->json([
                'message' => 'Only completed payments can be refunded'
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Create refund record
            $refund = $payment->refunds()->create([
                'amount' => $refundAmount,
                'reason' => $validated['reason'],
                'notes' => $validated['notes'] ?? null,
                'processed_by' => auth()->id(),
                'processed_at' => now(),
            ]);

            // Update payment status if fully refunded
            if ($refundAmount >= $payment->amount) {
                $payment->update(['status' => Payment::STATUS_REFUNDED]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Refund processed successfully',
                'refund' => $refund,
                'payment' => $payment->fresh()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to process refund: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get payment methods
     */
    public function paymentMethods()
    {
        $paymentMethods = [
            'cash' => 'Cash',
            'bank_transfer' => 'Bank Transfer',
            'check' => 'Check',
            'credit_card' => 'Credit Card',
            'mobile_payment' => 'Mobile Payment',
        ];

        $paymentMethodsAr = [
            'cash' => 'نقدي',
            'bank_transfer' => 'تحويل مصرفي',
            'check' => 'شيك',
            'credit_card' => 'بطاقة ائتمان',
            'mobile_payment' => 'دفع عبر الهاتف',
        ];

        return response()->json([
            'payment_methods' => $paymentMethods,
            'payment_methods_ar' => $paymentMethodsAr,
        ]);
    }

    /**
     * Export payments
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
     * Export payments to Excel
     */
    private function exportExcel()
    {
        return response()->json([
            'message' => 'Excel export functionality will be implemented with Laravel Excel'
        ]);
    }

    /**
     * Export payments to PDF
     */
    private function exportPdf()
    {
        return response()->json([
            'message' => 'PDF export functionality will be implemented with DomPDF'
        ]);
    }
}
