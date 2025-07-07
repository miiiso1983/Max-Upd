<?php

namespace App\Modules\Accounting\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    const TYPE_JOURNAL = 'journal';
    const TYPE_SALES = 'sales';
    const TYPE_PURCHASE = 'purchase';
    const TYPE_PAYMENT = 'payment';
    const TYPE_RECEIPT = 'receipt';
    const TYPE_ADJUSTMENT = 'adjustment';

    const STATUS_DRAFT = 'draft';
    const STATUS_POSTED = 'posted';
    const STATUS_REVERSED = 'reversed';

    protected $fillable = [
        'transaction_number',
        'type',
        'reference_type',
        'reference_id',
        'transaction_date',
        'description',
        'description_ar',
        'total_amount',
        'currency',
        'exchange_rate',
        'status',
        'posted_at',
        'posted_by',
        'reversed_at',
        'reversed_by',
        'reversal_reason',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'total_amount' => 'decimal:2',
        'exchange_rate' => 'decimal:4',
        'posted_at' => 'datetime',
        'reversed_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => self::STATUS_DRAFT,
        'currency' => 'IQD',
        'exchange_rate' => 1.0000,
    ];

    /**
     * Get the journal entries for this transaction
     */
    public function journalEntries()
    {
        return $this->hasMany(JournalEntry::class);
    }

    /**
     * Get the reference model (polymorphic)
     */
    public function reference()
    {
        return $this->morphTo();
    }

    /**
     * Get available transaction types
     */
    public static function getTypes()
    {
        return [
            self::TYPE_JOURNAL => 'Journal Entry',
            self::TYPE_SALES => 'Sales Transaction',
            self::TYPE_PURCHASE => 'Purchase Transaction',
            self::TYPE_PAYMENT => 'Payment',
            self::TYPE_RECEIPT => 'Receipt',
            self::TYPE_ADJUSTMENT => 'Adjustment',
        ];
    }

    /**
     * Get available transaction types in Arabic
     */
    public static function getTypesAr()
    {
        return [
            self::TYPE_JOURNAL => 'قيد يومية',
            self::TYPE_SALES => 'معاملة مبيعات',
            self::TYPE_PURCHASE => 'معاملة مشتريات',
            self::TYPE_PAYMENT => 'دفع',
            self::TYPE_RECEIPT => 'استلام',
            self::TYPE_ADJUSTMENT => 'تسوية',
        ];
    }

    /**
     * Get available statuses
     */
    public static function getStatuses()
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_POSTED => 'Posted',
            self::STATUS_REVERSED => 'Reversed',
        ];
    }

    /**
     * Get available statuses in Arabic
     */
    public static function getStatusesAr()
    {
        return [
            self::STATUS_DRAFT => 'مسودة',
            self::STATUS_POSTED => 'مرحل',
            self::STATUS_REVERSED => 'معكوس',
        ];
    }

    /**
     * Check if transaction is balanced
     */
    public function isBalanced()
    {
        $totalDebits = $this->journalEntries()->where('type', 'debit')->sum('amount');
        $totalCredits = $this->journalEntries()->where('type', 'credit')->sum('amount');
        
        return abs($totalDebits - $totalCredits) < 0.01; // Allow for rounding differences
    }

    /**
     * Post the transaction
     */
    public function post($postedBy = null)
    {
        if ($this->status !== self::STATUS_DRAFT) {
            throw new \Exception('Only draft transactions can be posted');
        }

        if (!$this->isBalanced()) {
            throw new \Exception('Transaction is not balanced');
        }

        $this->update([
            'status' => self::STATUS_POSTED,
            'posted_at' => now(),
            'posted_by' => $postedBy ?: auth()->id(),
        ]);

        // Update account balances
        foreach ($this->journalEntries as $entry) {
            $entry->account->updateBalance();
        }
    }

    /**
     * Reverse the transaction
     */
    public function reverse($reason, $reversedBy = null)
    {
        if ($this->status !== self::STATUS_POSTED) {
            throw new \Exception('Only posted transactions can be reversed');
        }

        // Create reversal entries
        foreach ($this->journalEntries as $entry) {
            JournalEntry::create([
                'transaction_id' => $this->id,
                'account_id' => $entry->account_id,
                'type' => $entry->type === 'debit' ? 'credit' : 'debit',
                'amount' => $entry->amount,
                'description' => 'Reversal: ' . $entry->description,
                'created_by' => $reversedBy ?: auth()->id(),
            ]);
        }

        $this->update([
            'status' => self::STATUS_REVERSED,
            'reversed_at' => now(),
            'reversed_by' => $reversedBy ?: auth()->id(),
            'reversal_reason' => $reason,
        ]);

        // Update account balances
        foreach ($this->journalEntries as $entry) {
            $entry->account->updateBalance();
        }
    }

    /**
     * Check if transaction can be edited
     */
    public function canBeEdited()
    {
        return $this->status === self::STATUS_DRAFT;
    }

    /**
     * Check if transaction can be posted
     */
    public function canBePosted()
    {
        return $this->status === self::STATUS_DRAFT && $this->isBalanced();
    }

    /**
     * Check if transaction can be reversed
     */
    public function canBeReversed()
    {
        return $this->status === self::STATUS_POSTED;
    }

    /**
     * Get total debits
     */
    public function getTotalDebits()
    {
        return $this->journalEntries()->where('type', 'debit')->sum('amount');
    }

    /**
     * Get total credits
     */
    public function getTotalCredits()
    {
        return $this->journalEntries()->where('type', 'credit')->sum('amount');
    }

    /**
     * Scope for transactions by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for transactions by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for transactions by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    /**
     * Scope for posted transactions
     */
    public function scopePosted($query)
    {
        return $query->where('status', self::STATUS_POSTED);
    }

    /**
     * Get the user who posted the transaction
     */
    public function poster()
    {
        return $this->belongsTo(\App\Models\User::class, 'posted_by');
    }

    /**
     * Get the user who reversed the transaction
     */
    public function reverser()
    {
        return $this->belongsTo(\App\Models\User::class, 'reversed_by');
    }

    /**
     * Get the user who created the transaction
     */
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Get the user who last updated the transaction
     */
    public function updater()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    /**
     * Generate transaction number if not provided
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            if (empty($transaction->transaction_number)) {
                $typePrefix = [
                    self::TYPE_JOURNAL => 'JE',
                    self::TYPE_SALES => 'ST',
                    self::TYPE_PURCHASE => 'PT',
                    self::TYPE_PAYMENT => 'PY',
                    self::TYPE_RECEIPT => 'RC',
                    self::TYPE_ADJUSTMENT => 'AJ',
                ];
                
                $prefix = $typePrefix[$transaction->type] ?? 'TX';
                $year = date('Y');
                $month = date('m');
                $count = static::where('type', $transaction->type)
                              ->whereYear('created_at', $year)
                              ->whereMonth('created_at', $month)
                              ->count() + 1;
                
                $transaction->transaction_number = "{$prefix}-{$year}{$month}-" . str_pad($count, 4, '0', STR_PAD_LEFT);
            }
        });

        static::saved(function ($transaction) {
            // Update total amount based on journal entries
            if ($transaction->journalEntries()->exists()) {
                $totalDebits = $transaction->getTotalDebits();
                $transaction->update(['total_amount' => $totalDebits]);
            }
        });
    }
}
