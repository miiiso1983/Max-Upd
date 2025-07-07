<?php

namespace App\Modules\Sales\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    const STATUS_DRAFT = 'draft';
    const STATUS_SENT = 'sent';
    const STATUS_PAID = 'paid';
    const STATUS_PARTIAL = 'partial';
    const STATUS_OVERDUE = 'overdue';
    const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'invoice_number',
        'customer_id',
        'sales_order_id',
        'invoice_date',
        'due_date',
        'status',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'paid_amount',
        'balance_due',
        'payment_terms',
        'notes',
        'terms_conditions',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance_due' => 'decimal:2',
        'payment_terms' => 'integer',
    ];

    protected $attributes = [
        'status' => self::STATUS_DRAFT,
        'subtotal' => 0,
        'tax_amount' => 0,
        'discount_amount' => 0,
        'total_amount' => 0,
        'paid_amount' => 0,
        'balance_due' => 0,
        'payment_terms' => 30,
    ];

    /**
     * Get the customer that owns the invoice
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the sales order
     */
    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }

    /**
     * Get the invoice items
     */
    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Get the payments for this invoice
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get available statuses
     */
    public static function getStatuses()
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_SENT => 'Sent',
            self::STATUS_PAID => 'Paid',
            self::STATUS_PARTIAL => 'Partial',
            self::STATUS_OVERDUE => 'Overdue',
            self::STATUS_CANCELLED => 'Cancelled',
        ];
    }

    /**
     * Get available statuses in Arabic
     */
    public static function getStatusesAr()
    {
        return [
            self::STATUS_DRAFT => 'مسودة',
            self::STATUS_SENT => 'مرسلة',
            self::STATUS_PAID => 'مدفوعة',
            self::STATUS_PARTIAL => 'مدفوعة جزئياً',
            self::STATUS_OVERDUE => 'متأخرة',
            self::STATUS_CANCELLED => 'ملغية',
        ];
    }

    /**
     * Check if invoice is overdue
     */
    public function isOverdue()
    {
        return $this->due_date &&
               $this->due_date->isPast() &&
               in_array($this->status, [self::STATUS_SENT, self::STATUS_PARTIAL]);
    }

    /**
     * Check if invoice is fully paid
     */
    public function isFullyPaid()
    {
        return $this->status === self::STATUS_PAID || $this->balance_due <= 0;
    }

    /**
     * Check if invoice can be edited
     */
    public function canBeEdited()
    {
        return in_array($this->status, [self::STATUS_DRAFT]);
    }

    /**
     * Mark invoice as paid
     */
    public function markAsPaid($amount = null)
    {
        $amount = $amount ?? $this->balance_due;
        
        $this->paid_amount += $amount;
        $this->balance_due = $this->total_amount - $this->paid_amount;
        
        if ($this->balance_due <= 0) {
            $this->status = self::STATUS_PAID;
            $this->balance_due = 0;
        }
        
        $this->save();
    }

    /**
     * Calculate totals based on items
     */
    public function calculateTotals()
    {
        // Calculate subtotal from items
        $this->subtotal = $this->items()->sum(DB::raw('quantity * unit_price'));

        // Ensure subtotal is not negative
        $this->subtotal = max(0, $this->subtotal);

        // Calculate tax (10% of subtotal)
        $this->tax_amount = $this->subtotal * 0.1;

        // Ensure discount doesn't exceed subtotal + tax
        $maxDiscount = $this->subtotal + $this->tax_amount;
        $this->discount_amount = min($this->discount_amount, $maxDiscount);

        // Calculate total amount
        $this->total_amount = $this->subtotal + $this->tax_amount - $this->discount_amount;

        // Ensure total is not negative
        $this->total_amount = max(0, $this->total_amount);

        // Calculate balance due
        $this->balance_due = $this->total_amount - $this->paid_amount;

        $this->save();
    }

    /**
     * Create invoice from sales order
     */
    public static function createFromSalesOrder(SalesOrder $salesOrder)
    {
        $invoice = static::create([
            'customer_id' => $salesOrder->customer_id,
            'sales_order_id' => $salesOrder->id,
            'invoice_date' => now(),
            'due_date' => now()->addDays($salesOrder->payment_terms),
            'subtotal' => $salesOrder->subtotal,
            'tax_amount' => $salesOrder->tax_amount,
            'discount_amount' => $salesOrder->discount_amount,
            'total_amount' => $salesOrder->total_amount,
            'balance_due' => $salesOrder->total_amount,
            'payment_terms' => $salesOrder->payment_terms,
            'created_by' => auth()->id(),
        ]);

        // Copy items from sales order
        foreach ($salesOrder->items as $orderItem) {
            $invoice->items()->create([
                'product_id' => $orderItem->product_id,
                'quantity' => $orderItem->quantity,
                'unit_price' => $orderItem->unit_price,
                'discount_amount' => $orderItem->discount_amount,
                'total_amount' => $orderItem->total_amount,
                'batch_number' => $orderItem->batch_number,
                'expiry_date' => $orderItem->expiry_date,
            ]);
        }

        return $invoice;
    }

    /**
     * Scope for invoices by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for overdue invoices
     */
    public function scopeOverdue($query)
    {
        return $query->whereIn('status', [self::STATUS_SENT, self::STATUS_PARTIAL])
                    ->where('due_date', '<', now());
    }

    /**
     * Scope for invoices by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('invoice_date', [$startDate, $endDate]);
    }

    /**
     * Get the user who created the invoice
     */
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Get the user who last updated the invoice
     */
    public function updater()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    /**
     * Generate invoice number if not provided
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            if (empty($invoice->invoice_number)) {
                $year = date('Y');
                $month = date('m');
                $count = static::whereYear('created_at', $year)
                              ->whereMonth('created_at', $month)
                              ->count() + 1;
                $invoice->invoice_number = "INV-{$year}{$month}-" . str_pad($count, 4, '0', STR_PAD_LEFT);
            }
            
            if (empty($invoice->invoice_date)) {
                $invoice->invoice_date = now();
            }
        });

        static::saved(function ($invoice) {
            // Update status to overdue if past due date
            if ($invoice->isOverdue() && in_array($invoice->status, [self::STATUS_SENT, self::STATUS_PARTIAL])) {
                $invoice->update(['status' => self::STATUS_OVERDUE]);
            }
        });
    }
}
