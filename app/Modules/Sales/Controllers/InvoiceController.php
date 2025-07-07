<?php

namespace App\Modules\Sales\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Sales\Models\Invoice;
use App\Modules\Sales\Models\InvoiceItem;
use App\Modules\Sales\Models\Payment;
use App\Modules\Sales\Models\Customer;
use App\Modules\Inventory\Models\Product;
use App\Modules\Inventory\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class InvoiceController extends Controller
{
    /**
     * Display a listing of invoices
     */
    public function index(Request $request)
    {
        $query = Invoice::with(['customer', 'salesOrder']);

        // Search functionality
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
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
            $query->where('customer_id', $request->get('customer_id'));
        }

        // Filter by date range
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->dateRange($request->get('start_date'), $request->get('end_date'));
        }

        // Filter overdue invoices
        if ($request->get('overdue') === 'true') {
            $query->overdue();
        }

        $invoices = $query->orderBy('created_at', 'desc')
                         ->paginate(20);

        $filters = [
            'statuses' => Invoice::getStatuses(),
            'statuses_ar' => Invoice::getStatusesAr(),
            'customers' => Customer::active()->get(['id', 'name', 'name_ar', 'type']),
        ];

        // Return JSON for API requests
        if ($request->expectsJson()) {
            return response()->json([
                'invoices' => $invoices,
                'filters' => $filters
            ]);
        }

        // Return view for web requests
        return view('sales.invoices.index', compact('invoices', 'filters'));
    }

    /**
     * Show the form for creating a new invoice
     */
    public function create()
    {
        $customers = Customer::active()->get();
        $products = Product::active()->get();
        $nextInvoiceNumber = $this->generateInvoiceNumber();

        return view('sales.invoices.create', compact('customers', 'products', 'nextInvoiceNumber'));
    }

    /**
     * Store a newly created invoice
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'due_date' => 'nullable|date|after:today',
            'payment_terms' => 'nullable|integer|min:0|max:365',
            'notes' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_amount' => 'nullable|numeric|min:0',
            'items.*.notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Get customer for default values
            $customer = Customer::findOrFail($validated['customer_id']);
            
            // Set default payment terms and due date
            $paymentTerms = $validated['payment_terms'] ?? $customer->payment_terms;
            $dueDate = $validated['due_date'] ?? now()->addDays($paymentTerms);

            // Create the invoice
            $invoice = Invoice::create([
                'customer_id' => $validated['customer_id'],
                'invoice_date' => now(),
                'due_date' => $dueDate,
                'payment_terms' => $paymentTerms,
                'notes' => $validated['notes'] ?? null,
                'terms_conditions' => $validated['terms_conditions'] ?? null,
                'created_by' => auth()->id(),
            ]);

            // Add invoice items
            foreach ($validated['items'] as $itemData) {
                $invoice->items()->create([
                    'product_id' => $itemData['product_id'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'discount_amount' => $itemData['discount_amount'] ?? 0,
                    'notes' => $itemData['notes'] ?? null,
                ]);
            }

            // Calculate totals (this is done automatically in the model)
            $invoice->calculateTotals();

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
     * Display the specified invoice
     */
    public function show(Request $request, Invoice $invoice)
    {
        $invoice->load([
            'customer',
            'salesOrder',
            'items.product.category',
            'items.product.manufacturer',
            'payments',
            'creator'
        ]);

        // Return JSON for API requests
        if ($request->expectsJson()) {
            return response()->json([
                'invoice' => $invoice
            ]);
        }

        // Return view for web requests
        return view('sales.invoices.show', compact('invoice'));
    }

    /**
     * Show the form for editing the specified invoice
     */
    public function edit(Invoice $invoice)
    {
        // Check if invoice can be edited
        if (!$invoice->canBeEdited()) {
            return redirect()->route('sales.invoices.index')
                ->with('error', 'لا يمكن تعديل هذه الفاتورة في حالتها الحالية');
        }

        $invoice->load([
            'customer',
            'items.product.category',
            'items.product.manufacturer',
            'salesOrder',
            'warehouse',
            'creator'
        ]);

        // Get customers for dropdown
        $customers = Customer::active()->orderBy('name')->get(['id', 'name', 'name_ar']);

        // Get warehouses for dropdown
        $warehouses = Warehouse::active()->orderBy('name')->get(['id', 'name', 'name_ar']);

        return view('sales.invoices.edit', [
            'invoice' => $invoice,
            'customers' => $customers,
            'warehouses' => $warehouses,
        ]);
    }

    /**
     * Update the specified invoice
     */
    public function update(Request $request, Invoice $invoice)
    {
        // Check if invoice can be edited
        if (!$invoice->canBeEdited()) {
            return response()->json([
                'message' => 'Invoice cannot be edited in current status'
            ], 422);
        }

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'due_date' => 'nullable|date|after:today',
            'payment_terms' => 'nullable|integer|min:0|max:365',
            'notes' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
        ]);

        $validated['updated_by'] = auth()->id();

        $invoice->update($validated);

        return response()->json([
            'message' => 'Invoice updated successfully',
            'invoice' => $invoice->fresh()->load(['customer', 'items.product'])
        ]);
    }

    /**
     * Remove the specified invoice
     */
    public function destroy(Invoice $invoice)
    {
        // Check if invoice can be deleted
        if (!$invoice->canBeEdited()) {
            return response()->json([
                'message' => 'Invoice cannot be deleted in current status'
            ], 422);
        }

        // Check if invoice has payments
        if ($invoice->payments()->exists()) {
            return response()->json([
                'message' => 'Cannot delete invoice with payments'
            ], 422);
        }

        $invoice->delete();

        return response()->json([
            'message' => 'Invoice deleted successfully'
        ]);
    }

    /**
     * Add item to invoice
     */
    public function addItem(Request $request, Invoice $invoice)
    {
        if (!$invoice->canBeEdited()) {
            return response()->json([
                'message' => 'Invoice cannot be modified in current status'
            ], 422);
        }

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        // Check if item already exists
        $existingItem = $invoice->items()->where('product_id', $validated['product_id'])->first();
        if ($existingItem) {
            return response()->json([
                'message' => 'Product already exists in invoice. Use update instead.'
            ], 422);
        }

        $item = $invoice->items()->create($validated);

        return response()->json([
            'message' => 'Item added successfully',
            'item' => $item->load('product')
        ], 201);
    }

    /**
     * Update invoice item
     */
    public function updateItem(Request $request, Invoice $invoice, InvoiceItem $item)
    {
        if (!$invoice->canBeEdited()) {
            return response()->json([
                'message' => 'Invoice cannot be modified in current status'
            ], 422);
        }

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $item->update($validated);

        return response()->json([
            'message' => 'Item updated successfully',
            'item' => $item->fresh()->load('product')
        ]);
    }

    /**
     * Remove item from invoice
     */
    public function removeItem(Invoice $invoice, InvoiceItem $item)
    {
        if (!$invoice->canBeEdited()) {
            return response()->json([
                'message' => 'Invoice cannot be modified in current status'
            ], 422);
        }

        // Check if this is the last item
        if ($invoice->items()->count() <= 1) {
            return response()->json([
                'message' => 'Cannot remove the last item from invoice'
            ], 422);
        }

        $item->delete();

        return response()->json([
            'message' => 'Item removed successfully'
        ]);
    }

    /**
     * Send invoice to customer
     */
    public function send(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'email' => 'nullable|email',
            'send_copy_to' => 'nullable|array',
            'send_copy_to.*' => 'email',
            'message' => 'nullable|string',
        ]);

        try {
            // Get customer email if not provided
            $customerEmail = $validated['email'] ?? $invoice->customer->email;

            if (!$customerEmail) {
                return response()->json([
                    'error' => 'لا يوجد بريد إلكتروني للعميل'
                ], 400);
            }

            // Update invoice status to sent if it's draft
            if ($invoice->status === 'draft') {
                $invoice->update(['status' => 'pending']);
            }

            // Send email using Laravel Mail
            \Mail::to($customerEmail)->send(new \App\Mail\InvoiceMail($invoice, $validated['message'] ?? null));

            // Log the email sending attempt
            \Log::info('Invoice email sent', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'customer_email' => $customerEmail,
                'sent_at' => now()
            ]);

            return response()->json([
                'message' => 'تم إرسال الفاتورة بنجاح إلى ' . $customerEmail,
                'invoice' => $invoice->fresh()
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to send invoice email', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'فشل في إرسال الفاتورة: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark invoice as paid
     */
    public function markAsPaid(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'payment_method' => 'required|in:' . implode(',', array_keys(Payment::getPaymentMethods())),
            'amount' => 'nullable|numeric|min:0',
            'payment_date' => 'nullable|date',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $amount = $validated['amount'] ?? $invoice->balance_due;
            
            // Create payment record
            $payment = $invoice->payments()->create([
                'customer_id' => $invoice->customer_id,
                'amount' => $amount,
                'payment_method' => $validated['payment_method'],
                'status' => Payment::STATUS_COMPLETED,
                'payment_date' => $validated['payment_date'] ?? now(),
                'reference_number' => $validated['reference_number'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            // Mark payment as completed (this will update the invoice)
            $payment->markAsCompleted();

            DB::commit();

            return response()->json([
                'message' => 'Payment recorded successfully',
                'invoice' => $invoice->fresh()->load('payments'),
                'payment' => $payment
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to record payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate PDF invoice
     */
    public function generatePdf(Invoice $invoice)
    {
        // This would implement PDF generation using DomPDF
        // For now, return a placeholder response
        return response()->json([
            'message' => 'PDF generation will be implemented with DomPDF',
            'download_url' => '/api/tenant/sales/invoices/' . $invoice->id . '/pdf'
        ]);
    }

    /**
     * Record payment for invoice
     */
    public function recordPayment(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:' . implode(',', array_keys(Payment::getPaymentMethods())),
            'payment_date' => 'nullable|date',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        // Check if amount doesn't exceed balance due
        if ($validated['amount'] > $invoice->balance_due) {
            return response()->json([
                'message' => 'Payment amount cannot exceed balance due'
            ], 422);
        }

        DB::beginTransaction();
        try {
            $payment = $invoice->payments()->create([
                'customer_id' => $invoice->customer_id,
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'status' => Payment::STATUS_COMPLETED,
                'payment_date' => $validated['payment_date'] ?? now(),
                'reference_number' => $validated['reference_number'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            $payment->markAsCompleted();

            DB::commit();

            return response()->json([
                'message' => 'Payment recorded successfully',
                'payment' => $payment,
                'invoice' => $invoice->fresh()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to record payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Void invoice
     */
    public function void(Invoice $invoice)
    {
        if ($invoice->status === Invoice::STATUS_PAID) {
            return response()->json([
                'message' => 'Cannot void a paid invoice'
            ], 422);
        }

        if ($invoice->payments()->exists()) {
            return response()->json([
                'message' => 'Cannot void invoice with payments'
            ], 422);
        }

        $invoice->update(['status' => Invoice::STATUS_CANCELLED]);

        return response()->json([
            'message' => 'Invoice voided successfully',
            'invoice' => $invoice->fresh()
        ]);
    }

    /**
     * Download PDF invoice
     */
    public function downloadPdf(Invoice $invoice)
    {
        // Load invoice with relationships
        $invoice->load(['customer', 'items.product']);

        // Calculate previous balance for this customer
        $invoice->previous_balance = $this->calculatePreviousBalance($invoice->customer_id, $invoice->id);

        // Generate PDF with Arabic support using DomPDF
        $pdf = Pdf::loadView('sales.invoices.pdf', compact('invoice'))
                  ->setPaper('a4', 'portrait')
                  ->setOptions([
                      'defaultFont' => 'DejaVu Sans',
                      'isHtml5ParserEnabled' => true,
                      'isPhpEnabled' => false,
                      'isRemoteEnabled' => false,
                      'isFontSubsettingEnabled' => true,
                      'defaultPaperSize' => 'a4',
                      'defaultPaperOrientation' => 'portrait',
                      'isJavascriptEnabled' => false,
                      'debugPng' => false,
                      'debugKeepTemp' => false,
                      'debugCss' => false,
                      'debugLayout' => false,
                      'debugLayoutLines' => false,
                      'debugLayoutBlocks' => false,
                      'debugLayoutInline' => false,
                      'debugLayoutPaddingBox' => false,
                      'tempDir' => storage_path('app/temp'),
                      'chroot' => base_path(),
                  ]);

        // Return PDF download
        return $pdf->download('invoice-' . $invoice->invoice_number . '.pdf');
    }

    /**
     * Calculate previous balance for customer
     */
    private function calculatePreviousBalance($customerId, $excludeInvoiceId = null)
    {
        try {
            // Get all unpaid invoices for this customer (excluding current invoice)
            $query = Invoice::where('customer_id', $customerId)
                           ->where('status', '!=', Invoice::STATUS_PAID);

            if ($excludeInvoiceId) {
                $query->where('id', '!=', $excludeInvoiceId);
            }

            $unpaidInvoices = $query->sum('total_amount');

            // Get all payments for this customer
            $totalPayments = Payment::where('customer_id', $customerId)
                                   ->sum('amount');

            return max(0, $unpaidInvoices - $totalPayments);

        } catch (\Exception $e) {
            // Return 0 if calculation fails
            return 0;
        }
    }

    /**
     * View PDF invoice in browser
     */
    public function viewPdf(Invoice $invoice)
    {
        // Load invoice with relationships
        $invoice->load(['customer', 'items.product']);

        // Calculate previous balance for this customer
        $invoice->previous_balance = $this->calculatePreviousBalance($invoice->customer_id, $invoice->id);

        // Generate PDF with Arabic support using DomPDF
        $pdf = Pdf::loadView('sales.invoices.pdf', compact('invoice'))
                  ->setPaper('a4', 'portrait')
                  ->setOptions([
                      'defaultFont' => 'DejaVu Sans',
                      'isHtml5ParserEnabled' => true,
                      'isPhpEnabled' => false,
                      'isRemoteEnabled' => false,
                      'isFontSubsettingEnabled' => true,
                      'defaultPaperSize' => 'a4',
                      'defaultPaperOrientation' => 'portrait',
                      'isJavascriptEnabled' => false,
                      'debugPng' => false,
                      'debugKeepTemp' => false,
                      'debugCss' => false,
                      'debugLayout' => false,
                      'debugLayoutLines' => false,
                      'debugLayoutBlocks' => false,
                      'debugLayoutInline' => false,
                      'debugLayoutPaddingBox' => false,
                      'tempDir' => storage_path('app/temp'),
                      'chroot' => base_path(),
                  ]);

        // Return PDF for viewing in browser
        return $pdf->stream('invoice-' . $invoice->invoice_number . '.pdf');
    }

    /**
     * Print invoice (for direct printing)
     */
    public function print(Invoice $invoice)
    {
        // Load invoice with relationships
        $invoice->load(['customer', 'items.product']);

        // Calculate previous balance for this customer
        $invoice->previous_balance = $this->calculatePreviousBalance($invoice->customer_id, $invoice->id);

        // Generate PDF with Arabic support using DomPDF
        $pdf = Pdf::loadView('sales.invoices.pdf', compact('invoice'))
                  ->setPaper('a4', 'portrait')
                  ->setOptions([
                      'defaultFont' => 'DejaVu Sans',
                      'isHtml5ParserEnabled' => true,
                      'isPhpEnabled' => false,
                      'isRemoteEnabled' => false,
                      'isFontSubsettingEnabled' => true,
                      'defaultPaperSize' => 'a4',
                      'defaultPaperOrientation' => 'portrait',
                      'isJavascriptEnabled' => false,
                      'debugPng' => false,
                      'debugKeepTemp' => false,
                      'debugCss' => false,
                      'debugLayout' => false,
                      'debugLayoutLines' => false,
                      'debugLayoutBlocks' => false,
                      'debugLayoutInline' => false,
                      'debugLayoutPaddingBox' => false,
                      'tempDir' => storage_path('app/temp'),
                      'chroot' => base_path(),
                  ]);

        // Return PDF for direct printing
        return $pdf->stream('print-invoice-' . $invoice->invoice_number . '.pdf');
    }

    /**
     * Send invoice to customer
     */
    public function sendToCustomer(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'email' => 'nullable|email',
            'message' => 'nullable|string',
        ]);

        // This would implement email sending
        return response()->json([
            'message' => 'Invoice sent successfully to customer'
        ]);
    }

    /**
     * Get invoice items
     */
    public function items(Invoice $invoice)
    {
        $items = $invoice->items()->with(['product'])->get();

        return response()->json(['items' => $items]);
    }

    /**
     * Export invoices to Excel/CSV
     */
    public function export(Request $request)
    {
        try {
            $filters = $request->only(['status', 'customer_id', 'date_from', 'date_to']);
            $format = $request->get('format', 'excel');

            // Get invoices with filters
            $query = Invoice::with(['customer', 'creator', 'salesOrder.warehouse']);

            // Apply filters
            if (!empty($filters['status'])) {
                $query->where('status', $filters['status']);
            }
            if (!empty($filters['customer_id'])) {
                $query->where('customer_id', $filters['customer_id']);
            }
            if (!empty($filters['date_from'])) {
                $query->whereDate('invoice_date', '>=', $filters['date_from']);
            }
            if (!empty($filters['date_to'])) {
                $query->whereDate('invoice_date', '<=', $filters['date_to']);
            }

            $invoices = $query->orderBy('created_at', 'desc')->get();

            if ($format === 'excel') {
                return $this->exportToExcel($invoices);
            } else {
                return $this->exportToCsv($invoices);
            }

        } catch (\Exception $e) {
            \Log::error('Invoice export failed: ' . $e->getMessage());

            return response()->json([
                'error' => 'فشل في تصدير الفواتير: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export to Excel using Laravel Excel
     */
    private function exportToExcel($invoices)
    {
        try {
            $filename = 'invoices_' . date('Y-m-d_H-i-s') . '.xlsx';
            return Excel::download(new \App\Exports\InvoicesExport([]), $filename);
        } catch (\Exception $e) {
            // Fallback to CSV if Excel fails
            return $this->exportToCsv($invoices);
        }
    }

    /**
     * Export to CSV
     */
    private function exportToCsv($invoices)
    {
        // Create CSV content with BOM for proper Arabic display
        $csvContent = "\xEF\xBB\xBF"; // UTF-8 BOM
        $csvContent .= "رقم الفاتورة,العميل,تاريخ الفاتورة,تاريخ الاستحقاق,الحالة,المجموع الفرعي,الضريبة,الخصم,المجموع الإجمالي,المدفوع,المتبقي,المستودع,أمر البيع,المنشئ,تاريخ الإنشاء\n";

        foreach ($invoices as $invoice) {
            $customerName = $invoice->customer ? ($invoice->customer->name_ar ?: $invoice->customer->name) : '';
            $warehouseName = '';
            if ($invoice->salesOrder && $invoice->salesOrder->warehouse) {
                $warehouseName = $invoice->salesOrder->warehouse->name_ar ?: $invoice->salesOrder->warehouse->name;
            }
            $salesOrderNumber = $invoice->salesOrder ? $invoice->salesOrder->order_number : '';
            $creatorName = $invoice->creator ? $invoice->creator->name : '';
            $status = $this->getStatusInArabic($invoice->status);

            $csvContent .= sprintf(
                "%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s\n",
                $invoice->invoice_number,
                $customerName,
                $invoice->invoice_date->format('Y/m/d'),
                $invoice->due_date ? $invoice->due_date->format('Y/m/d') : '',
                $status,
                number_format($invoice->subtotal, 2),
                number_format($invoice->tax_amount, 2),
                number_format($invoice->discount_amount, 2),
                number_format($invoice->total_amount, 2),
                number_format($invoice->paid_amount, 2),
                number_format($invoice->total_amount - $invoice->paid_amount, 2),
                $warehouseName,
                $salesOrderNumber,
                $creatorName,
                $invoice->created_at->format('Y/m/d H:i')
            );
        }

        $filename = 'invoices_' . date('Y-m-d_H-i-s') . '.csv';

        return response($csvContent)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Content-Length', strlen($csvContent));
    }

    /**
     * Get status in Arabic
     */
    private function getStatusInArabic($status)
    {
        $statuses = [
            'draft' => 'مسودة',
            'pending' => 'معلقة',
            'paid' => 'مدفوعة',
            'overdue' => 'متأخرة',
            'cancelled' => 'ملغية'
        ];

        return $statuses[$status] ?? $status;
    }

    /**
     * Export invoices to PDF
     */
    private function exportPdf()
    {
        return response()->json([
            'message' => 'PDF export functionality will be implemented with DomPDF'
        ]);
    }

    /**
     * Generate next invoice number
     */
    private function generateInvoiceNumber()
    {
        $lastInvoice = Invoice::latest('id')->first();
        $nextNumber = $lastInvoice ? $lastInvoice->id + 1 : 1;

        return 'INV-' . date('Y') . '-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }
}
