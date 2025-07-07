<?php

namespace App\Modules\Accounting\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    const TYPE_SALES = 'sales';
    const TYPE_PURCHASE = 'purchase';

    const STATUS_DRAFT = 'draft';
    const STATUS_SENT = 'sent';
    const STATUS_PAID = 'paid';
    const STATUS_PARTIAL = 'partial';
    const STATUS_OVERDUE = 'overdue';
    const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'invoice_number',
        'type',
        'customer_id',
        'supplier_id',
        'issue_date',
        'due_date',
        'payment_terms',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'paid_amount',
        'balance_due',
        'currency',
        'exchange_rate',
        'status',
        'notes',
        'terms_conditions',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance_due' => 'decimal:2',
        'exchange_rate' => 'decimal:4',
    ];

    protected $attributes = [
        'status' => self::STATUS_DRAFT,
        'currency' => 'IQD',
        'exchange_rate' => 1.0000,
        'paid_amount' => 0,
        'discount_amount' => 0,
        'tax_amount' => 0,
    ];

    /**
     * Get the invoice items
     */
    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Get the customer (for sales invoices)
     */
    public function customer()
    {
        return $this->belongsTo(\App\Modules\Sales\Models\Customer::class);
    }

    /**
     * Get the supplier (for purchase invoices)
     */
    public function supplier()
    {
        return $this->belongsTo(\App\Modules\Suppliers\Models\Supplier::class);
    }

    /**
     * Get the payments for this invoice
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get the accounting transactions
     */
    public function transactions()
    {
        return $this->morphMany(Transaction::class, 'reference');
    }

    /**
     * Get available invoice types
     */
    public static function getTypes()
    {
        return [
            self::TYPE_SALES => 'Sales Invoice',
            self::TYPE_PURCHASE => 'Purchase Invoice',
        ];
    }

    /**
     * Get available invoice types in Arabic
     */
    public static function getTypesAr()
    {
        return [
            self::TYPE_SALES => 'فاتورة مبيعات',
            self::TYPE_PURCHASE => 'فاتورة مشتريات',
        ];
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
            self::STATUS_PARTIAL => 'Partially Paid',
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
     * Calculate invoice totals
     */
    public function calculateTotals()
    {
        $this->subtotal = $this->items()->sum(\DB::raw('quantity * unit_price'));
        
        // Calculate tax (assuming 5% tax rate for Iraq)
        $taxRate = 0.05;
        $this->tax_amount = ($this->subtotal - $this->discount_amount) * $taxRate;
        
        $this->total_amount = $this->subtotal + $this->tax_amount - $this->discount_amount;
        $this->balance_due = $this->total_amount - $this->paid_amount;
        
        $this->save();
    }

    /**
     * Update payment status based on paid amount
     */
    public function updatePaymentStatus()
    {
        $this->paid_amount = $this->payments()->where('status', Payment::STATUS_COMPLETED)->sum('amount');
        $this->balance_due = $this->total_amount - $this->paid_amount;
        
        if ($this->paid_amount >= $this->total_amount) {
            $this->status = self::STATUS_PAID;
        } elseif ($this->paid_amount > 0) {
            $this->status = self::STATUS_PARTIAL;
        } elseif ($this->due_date && $this->due_date->isPast() && $this->status !== self::STATUS_CANCELLED) {
            $this->status = self::STATUS_OVERDUE;
        }
        
        $this->save();
    }

    /**
     * Check if invoice is overdue
     */
    public function isOverdue()
    {
        return $this->due_date && $this->due_date->isPast() && $this->balance_due > 0;
    }

    /**
     * Check if invoice is fully paid
     */
    public function isPaid()
    {
        return $this->status === self::STATUS_PAID;
    }

    /**
     * Check if invoice is partially paid
     */
    public function isPartiallyPaid()
    {
        return $this->status === self::STATUS_PARTIAL;
    }

    /**
     * Get days overdue
     */
    public function getDaysOverdue()
    {
        if (!$this->isOverdue()) {
            return 0;
        }
        
        return now()->diffInDays($this->due_date);
    }

    /**
     * Get payment percentage
     */
    public function getPaymentPercentage()
    {
        if ($this->total_amount == 0) {
            return 0;
        }
        
        return ($this->paid_amount / $this->total_amount) * 100;
    }

    /**
     * Create accounting transaction for invoice
     */
    public function createAccountingTransaction()
    {
        if ($this->type === self::TYPE_SALES) {
            return $this->createSalesTransaction();
        } else {
            return $this->createPurchaseTransaction();
        }
    }

    /**
     * Create sales transaction
     */
    private function createSalesTransaction()
    {
        $transaction = Transaction::create([
            'type' => Transaction::TYPE_SALES,
            'reference_type' => get_class($this),
            'reference_id' => $this->id,
            'transaction_date' => $this->issue_date,
            'description' => "Sales Invoice #{$this->invoice_number}",
            'total_amount' => $this->total_amount,
            'created_by' => auth()->id(),
        ]);

        // Debit: Accounts Receivable
        JournalEntry::create([
            'transaction_id' => $transaction->id,
            'account_id' => Account::where('code', '1200')->first()->id, // Accounts Receivable
            'type' => JournalEntry::TYPE_DEBIT,
            'amount' => $this->total_amount,
            'description' => "Sales to customer #{$this->customer_id}",
            'created_by' => auth()->id(),
        ]);

        // Credit: Sales Revenue
        JournalEntry::create([
            'transaction_id' => $transaction->id,
            'account_id' => Account::where('code', '4100')->first()->id, // Sales Revenue
            'type' => JournalEntry::TYPE_CREDIT,
            'amount' => $this->subtotal,
            'description' => "Sales revenue",
            'created_by' => auth()->id(),
        ]);

        // Credit: Tax Payable (if applicable)
        if ($this->tax_amount > 0) {
            JournalEntry::create([
                'transaction_id' => $transaction->id,
                'account_id' => Account::where('code', '2300')->first()->id, // Tax Payable
                'type' => JournalEntry::TYPE_CREDIT,
                'amount' => $this->tax_amount,
                'description' => "Sales tax",
                'created_by' => auth()->id(),
            ]);
        }

        return $transaction;
    }

    /**
     * Create purchase transaction
     */
    private function createPurchaseTransaction()
    {
        $transaction = Transaction::create([
            'type' => Transaction::TYPE_PURCHASE,
            'reference_type' => get_class($this),
            'reference_id' => $this->id,
            'transaction_date' => $this->issue_date,
            'description' => "Purchase Invoice #{$this->invoice_number}",
            'total_amount' => $this->total_amount,
            'created_by' => auth()->id(),
        ]);

        // Debit: Purchases/Inventory
        JournalEntry::create([
            'transaction_id' => $transaction->id,
            'account_id' => Account::where('code', '5100')->first()->id, // Purchases
            'type' => JournalEntry::TYPE_DEBIT,
            'amount' => $this->subtotal,
            'description' => "Purchase from supplier #{$this->supplier_id}",
            'created_by' => auth()->id(),
        ]);

        // Debit: Tax Receivable (if applicable)
        if ($this->tax_amount > 0) {
            JournalEntry::create([
                'transaction_id' => $transaction->id,
                'account_id' => Account::where('code', '1300')->first()->id, // Tax Receivable
                'type' => JournalEntry::TYPE_DEBIT,
                'amount' => $this->tax_amount,
                'description' => "Purchase tax",
                'created_by' => auth()->id(),
            ]);
        }

        // Credit: Accounts Payable
        JournalEntry::create([
            'transaction_id' => $transaction->id,
            'account_id' => Account::where('code', '2100')->first()->id, // Accounts Payable
            'type' => JournalEntry::TYPE_CREDIT,
            'amount' => $this->total_amount,
            'description' => "Purchase from supplier #{$this->supplier_id}",
            'created_by' => auth()->id(),
        ]);

        return $transaction;
    }

    /**
     * Scope for sales invoices
     */
    public function scopeSales($query)
    {
        return $query->where('type', self::TYPE_SALES);
    }

    /**
     * Scope for purchase invoices
     */
    public function scopePurchase($query)
    {
        return $query->where('type', self::TYPE_PURCHASE);
    }

    /**
     * Scope for overdue invoices
     */
    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
                    ->where('balance_due', '>', 0)
                    ->whereNotIn('status', [self::STATUS_PAID, self::STATUS_CANCELLED]);
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
                $prefix = $invoice->type === self::TYPE_SALES ? 'INV' : 'PINV';
                $year = date('Y');
                $count = static::where('type', $invoice->type)
                              ->whereYear('created_at', $year)
                              ->count() + 1;
                
                $invoice->invoice_number = "{$prefix}-{$year}-" . str_pad($count, 5, '0', STR_PAD_LEFT);
            }
        });

        static::saved(function ($invoice) {
            $invoice->calculateTotals();
        });
    }
}
