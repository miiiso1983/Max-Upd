<?php

namespace App\Modules\Accounting\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    const TYPE_RECEIPT = 'receipt'; // Money received
    const TYPE_PAYMENT = 'payment'; // Money paid

    const METHOD_CASH = 'cash';
    const METHOD_BANK_TRANSFER = 'bank_transfer';
    const METHOD_CHECK = 'check';
    const METHOD_CREDIT_CARD = 'credit_card';
    const METHOD_MOBILE_PAYMENT = 'mobile_payment';

    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'payment_number',
        'type',
        'invoice_id',
        'customer_id',
        'supplier_id',
        'amount',
        'currency',
        'exchange_rate',
        'payment_date',
        'payment_method',
        'reference_number',
        'bank_account_id',
        'status',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'exchange_rate' => 'decimal:4',
        'payment_date' => 'date',
    ];

    protected $attributes = [
        'status' => self::STATUS_PENDING,
        'currency' => 'IQD',
        'exchange_rate' => 1.0000,
    ];

    /**
     * Get the invoice this payment is for
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get the customer (for receipts)
     */
    public function customer()
    {
        return $this->belongsTo(\App\Modules\Sales\Models\Customer::class);
    }

    /**
     * Get the supplier (for payments)
     */
    public function supplier()
    {
        return $this->belongsTo(\App\Modules\Suppliers\Models\Supplier::class);
    }

    /**
     * Get the bank account
     */
    public function bankAccount()
    {
        return $this->belongsTo(Account::class, 'bank_account_id');
    }



    /**
     * Get the accounting transactions
     */
    public function transactions()
    {
        return $this->morphMany(Transaction::class, 'reference');
    }

    /**
     * Get available payment types
     */
    public static function getTypes()
    {
        return [
            self::TYPE_RECEIPT => 'Receipt (Money In)',
            self::TYPE_PAYMENT => 'Payment (Money Out)',
        ];
    }

    /**
     * Get available payment types in Arabic
     */
    public static function getTypesAr()
    {
        return [
            self::TYPE_RECEIPT => 'استلام (أموال واردة)',
            self::TYPE_PAYMENT => 'دفع (أموال صادرة)',
        ];
    }

    /**
     * Get available payment methods
     */
    public static function getMethods()
    {
        return [
            self::METHOD_CASH => 'Cash',
            self::METHOD_BANK_TRANSFER => 'Bank Transfer',
            self::METHOD_CHECK => 'Check',
            self::METHOD_CREDIT_CARD => 'Credit Card',
            self::METHOD_MOBILE_PAYMENT => 'Mobile Payment',
        ];
    }

    /**
     * Get available payment methods in Arabic
     */
    public static function getMethodsAr()
    {
        return [
            self::METHOD_CASH => 'نقد',
            self::METHOD_BANK_TRANSFER => 'تحويل بنكي',
            self::METHOD_CHECK => 'شيك',
            self::METHOD_CREDIT_CARD => 'بطاقة ائتمان',
            self::METHOD_MOBILE_PAYMENT => 'دفع عبر الهاتف',
        ];
    }

    /**
     * Get available statuses
     */
    public static function getStatuses()
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_FAILED => 'Failed',
            self::STATUS_CANCELLED => 'Cancelled',
        ];
    }

    /**
     * Get available statuses in Arabic
     */
    public static function getStatusesAr()
    {
        return [
            self::STATUS_PENDING => 'معلق',
            self::STATUS_COMPLETED => 'مكتمل',
            self::STATUS_FAILED => 'فاشل',
            self::STATUS_CANCELLED => 'ملغي',
        ];
    }

    /**
     * Mark payment as completed
     */
    public function markAsCompleted()
    {
        $this->update(['status' => self::STATUS_COMPLETED]);
        
        // Create accounting transaction
        $this->createAccountingTransaction();
        
        // Update invoice payment status
        if ($this->invoice) {
            $this->invoice->updatePaymentStatus();
        }
    }

    /**
     * Create accounting transaction for payment
     */
    public function createAccountingTransaction()
    {
        if ($this->type === self::TYPE_RECEIPT) {
            return $this->createReceiptTransaction();
        } else {
            return $this->createPaymentTransaction();
        }
    }

    /**
     * Create receipt transaction (money received)
     */
    private function createReceiptTransaction()
    {
        $transaction = Transaction::create([
            'type' => Transaction::TYPE_RECEIPT,
            'reference_type' => get_class($this),
            'reference_id' => $this->id,
            'transaction_date' => $this->payment_date,
            'description' => "Receipt #{$this->payment_number}",
            'total_amount' => $this->amount,
            'created_by' => auth()->id(),
        ]);

        // Debit: Cash/Bank Account
        $cashAccountId = $this->payment_method === self::METHOD_CASH 
            ? Account::where('code', '1100')->first()->id // Cash
            : ($this->bank_account_id ?: Account::where('code', '1110')->first()->id); // Bank

        JournalEntry::create([
            'transaction_id' => $transaction->id,
            'account_id' => $cashAccountId,
            'type' => JournalEntry::TYPE_DEBIT,
            'amount' => $this->amount,
            'description' => "Receipt from customer",
            'created_by' => auth()->id(),
        ]);

        // Credit: Accounts Receivable
        JournalEntry::create([
            'transaction_id' => $transaction->id,
            'account_id' => Account::where('code', '1200')->first()->id, // Accounts Receivable
            'type' => JournalEntry::TYPE_CREDIT,
            'amount' => $this->amount,
            'description' => "Payment received from customer",
            'created_by' => auth()->id(),
        ]);

        return $transaction;
    }

    /**
     * Create payment transaction (money paid)
     */
    private function createPaymentTransaction()
    {
        $transaction = Transaction::create([
            'type' => Transaction::TYPE_PAYMENT,
            'reference_type' => get_class($this),
            'reference_id' => $this->id,
            'transaction_date' => $this->payment_date,
            'description' => "Payment #{$this->payment_number}",
            'total_amount' => $this->amount,
            'created_by' => auth()->id(),
        ]);

        // Debit: Accounts Payable
        JournalEntry::create([
            'transaction_id' => $transaction->id,
            'account_id' => Account::where('code', '2100')->first()->id, // Accounts Payable
            'type' => JournalEntry::TYPE_DEBIT,
            'amount' => $this->amount,
            'description' => "Payment to supplier",
            'created_by' => auth()->id(),
        ]);

        // Credit: Cash/Bank Account
        $cashAccountId = $this->payment_method === self::METHOD_CASH 
            ? Account::where('code', '1100')->first()->id // Cash
            : ($this->bank_account_id ?: Account::where('code', '1110')->first()->id); // Bank

        JournalEntry::create([
            'transaction_id' => $transaction->id,
            'account_id' => $cashAccountId,
            'type' => JournalEntry::TYPE_CREDIT,
            'amount' => $this->amount,
            'description' => "Payment made to supplier",
            'created_by' => auth()->id(),
        ]);

        return $transaction;
    }

    /**
     * Check if payment is completed
     */
    public function isCompleted()
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if payment can be modified
     */
    public function canBeModified()
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_FAILED]);
    }

    /**
     * Scope for receipts
     */
    public function scopeReceipts($query)
    {
        return $query->where('type', self::TYPE_RECEIPT);
    }

    /**
     * Scope for payments
     */
    public function scopePayments($query)
    {
        return $query->where('type', self::TYPE_PAYMENT);
    }

    /**
     * Scope for completed payments
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Get the user who created the payment
     */
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Get the user who last updated the payment
     */
    public function updater()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    /**
     * Generate payment number if not provided
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payment) {
            if (empty($payment->payment_number)) {
                $prefix = $payment->type === self::TYPE_RECEIPT ? 'RCP' : 'PAY';
                $year = date('Y');
                $count = static::where('type', $payment->type)
                              ->whereYear('created_at', $year)
                              ->count() + 1;
                
                $payment->payment_number = "{$prefix}-{$year}-" . str_pad($count, 5, '0', STR_PAD_LEFT);
            }
        });
    }
}
