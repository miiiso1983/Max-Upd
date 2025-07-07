<?php

namespace App\Modules\Accounting\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JournalEntry extends Model
{
    use HasFactory, SoftDeletes;

    const TYPE_DEBIT = 'debit';
    const TYPE_CREDIT = 'credit';

    protected $fillable = [
        'transaction_id',
        'account_id',
        'type',
        'amount',
        'description',
        'description_ar',
        'reference_type',
        'reference_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /**
     * Get the transaction this entry belongs to
     */
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * Get the account this entry affects
     */
    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Get the reference model (polymorphic)
     */
    public function reference()
    {
        return $this->morphTo();
    }

    /**
     * Get available entry types
     */
    public static function getTypes()
    {
        return [
            self::TYPE_DEBIT => 'Debit',
            self::TYPE_CREDIT => 'Credit',
        ];
    }

    /**
     * Get available entry types in Arabic
     */
    public static function getTypesAr()
    {
        return [
            self::TYPE_DEBIT => 'مدين',
            self::TYPE_CREDIT => 'دائن',
        ];
    }

    /**
     * Check if entry is debit
     */
    public function isDebit()
    {
        return $this->type === self::TYPE_DEBIT;
    }

    /**
     * Check if entry is credit
     */
    public function isCredit()
    {
        return $this->type === self::TYPE_CREDIT;
    }

    /**
     * Get the signed amount (positive for debit, negative for credit)
     */
    public function getSignedAmountAttribute()
    {
        return $this->isDebit() ? $this->amount : -$this->amount;
    }

    /**
     * Scope for debit entries
     */
    public function scopeDebits($query)
    {
        return $query->where('type', self::TYPE_DEBIT);
    }

    /**
     * Scope for credit entries
     */
    public function scopeCredits($query)
    {
        return $query->where('type', self::TYPE_CREDIT);
    }

    /**
     * Scope for entries by account
     */
    public function scopeForAccount($query, $accountId)
    {
        return $query->where('account_id', $accountId);
    }

    /**
     * Scope for entries by transaction
     */
    public function scopeForTransaction($query, $transactionId)
    {
        return $query->where('transaction_id', $transactionId);
    }

    /**
     * Scope for entries by date range (through transaction)
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereHas('transaction', function ($q) use ($startDate, $endDate) {
            $q->whereBetween('transaction_date', [$startDate, $endDate]);
        });
    }

    /**
     * Scope for posted entries (through transaction)
     */
    public function scopePosted($query)
    {
        return $query->whereHas('transaction', function ($q) {
            $q->where('status', Transaction::STATUS_POSTED);
        });
    }

    /**
     * Get the user who created the entry
     */
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Get the user who last updated the entry
     */
    public function updater()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    /**
     * Get transaction date through relationship
     */
    public function getTransactionDateAttribute()
    {
        return $this->transaction ? $this->transaction->transaction_date : null;
    }

    /**
     * Get transaction number through relationship
     */
    public function getTransactionNumberAttribute()
    {
        return $this->transaction ? $this->transaction->transaction_number : null;
    }

    /**
     * Get transaction status through relationship
     */
    public function getTransactionStatusAttribute()
    {
        return $this->transaction ? $this->transaction->status : null;
    }

    /**
     * Check if entry can be modified
     */
    public function canBeModified()
    {
        return $this->transaction && $this->transaction->canBeEdited();
    }

    /**
     * Validate entry amount
     */
    public function validateAmount()
    {
        if ($this->amount <= 0) {
            throw new \Exception('Journal entry amount must be positive');
        }
    }

    /**
     * Validate entry account
     */
    public function validateAccount()
    {
        if (!$this->account || !$this->account->isActive()) {
            throw new \Exception('Journal entry must reference an active account');
        }
    }

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($entry) {
            $entry->validateAmount();
            $entry->validateAccount();
        });

        static::updating(function ($entry) {
            if (!$entry->canBeModified()) {
                throw new \Exception('Cannot modify journal entry for posted transaction');
            }
            $entry->validateAmount();
            $entry->validateAccount();
        });

        static::deleting(function ($entry) {
            if (!$entry->canBeModified()) {
                throw new \Exception('Cannot delete journal entry for posted transaction');
            }
        });
    }
}
