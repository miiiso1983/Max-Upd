<?php

namespace App\Modules\Accounting\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{
    use HasFactory, SoftDeletes;

    const TYPE_ASSET = 'asset';
    const TYPE_LIABILITY = 'liability';
    const TYPE_EQUITY = 'equity';
    const TYPE_REVENUE = 'revenue';
    const TYPE_EXPENSE = 'expense';

    const SUBTYPE_CURRENT_ASSET = 'current_asset';
    const SUBTYPE_NON_CURRENT_ASSET = 'non_current_asset';
    const SUBTYPE_CURRENT_LIABILITY = 'current_liability';
    const SUBTYPE_NON_CURRENT_LIABILITY = 'non_current_liability';
    const SUBTYPE_OWNERS_EQUITY = 'owners_equity';
    const SUBTYPE_OPERATING_REVENUE = 'operating_revenue';
    const SUBTYPE_NON_OPERATING_REVENUE = 'non_operating_revenue';
    const SUBTYPE_OPERATING_EXPENSE = 'operating_expense';
    const SUBTYPE_NON_OPERATING_EXPENSE = 'non_operating_expense';

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'code',
        'name',
        'name_ar',
        'type',
        'subtype',
        'parent_id',
        'description',
        'description_ar',
        'status',
        'is_system_account',
        'opening_balance',
        'current_balance',
        'currency',
        'tax_account',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'is_system_account' => 'boolean',
        'tax_account' => 'boolean',
    ];

    protected $attributes = [
        'status' => self::STATUS_ACTIVE,
        'currency' => 'IQD',
        'opening_balance' => 0,
        'current_balance' => 0,
        'is_system_account' => false,
        'tax_account' => false,
    ];

    /**
     * Get the parent account
     */
    public function parent()
    {
        return $this->belongsTo(Account::class, 'parent_id');
    }

    /**
     * Get child accounts
     */
    public function children()
    {
        return $this->hasMany(Account::class, 'parent_id');
    }

    /**
     * Get all descendants
     */
    public function descendants()
    {
        return $this->children()->with('descendants');
    }

    /**
     * Get journal entries for this account
     */
    public function journalEntries()
    {
        return $this->hasMany(JournalEntry::class);
    }

    /**
     * Get debit entries
     */
    public function debitEntries()
    {
        return $this->journalEntries()->where('type', 'debit');
    }

    /**
     * Get credit entries
     */
    public function creditEntries()
    {
        return $this->journalEntries()->where('type', 'credit');
    }

    /**
     * Get account types
     */
    public static function getTypes()
    {
        return [
            self::TYPE_ASSET => 'Asset',
            self::TYPE_LIABILITY => 'Liability',
            self::TYPE_EQUITY => 'Equity',
            self::TYPE_REVENUE => 'Revenue',
            self::TYPE_EXPENSE => 'Expense',
        ];
    }

    /**
     * Get account types in Arabic
     */
    public static function getTypesAr()
    {
        return [
            self::TYPE_ASSET => 'أصول',
            self::TYPE_LIABILITY => 'خصوم',
            self::TYPE_EQUITY => 'حقوق الملكية',
            self::TYPE_REVENUE => 'إيرادات',
            self::TYPE_EXPENSE => 'مصروفات',
        ];
    }

    /**
     * Get account subtypes
     */
    public static function getSubtypes()
    {
        return [
            self::SUBTYPE_CURRENT_ASSET => 'Current Asset',
            self::SUBTYPE_NON_CURRENT_ASSET => 'Non-Current Asset',
            self::SUBTYPE_CURRENT_LIABILITY => 'Current Liability',
            self::SUBTYPE_NON_CURRENT_LIABILITY => 'Non-Current Liability',
            self::SUBTYPE_OWNERS_EQUITY => 'Owner\'s Equity',
            self::SUBTYPE_OPERATING_REVENUE => 'Operating Revenue',
            self::SUBTYPE_NON_OPERATING_REVENUE => 'Non-Operating Revenue',
            self::SUBTYPE_OPERATING_EXPENSE => 'Operating Expense',
            self::SUBTYPE_NON_OPERATING_EXPENSE => 'Non-Operating Expense',
        ];
    }

    /**
     * Get account subtypes in Arabic
     */
    public static function getSubtypesAr()
    {
        return [
            self::SUBTYPE_CURRENT_ASSET => 'أصول متداولة',
            self::SUBTYPE_NON_CURRENT_ASSET => 'أصول غير متداولة',
            self::SUBTYPE_CURRENT_LIABILITY => 'خصوم متداولة',
            self::SUBTYPE_NON_CURRENT_LIABILITY => 'خصوم غير متداولة',
            self::SUBTYPE_OWNERS_EQUITY => 'حقوق المالكين',
            self::SUBTYPE_OPERATING_REVENUE => 'إيرادات تشغيلية',
            self::SUBTYPE_NON_OPERATING_REVENUE => 'إيرادات غير تشغيلية',
            self::SUBTYPE_OPERATING_EXPENSE => 'مصروفات تشغيلية',
            self::SUBTYPE_NON_OPERATING_EXPENSE => 'مصروفات غير تشغيلية',
        ];
    }

    /**
     * Calculate account balance
     */
    public function calculateBalance($startDate = null, $endDate = null)
    {
        $query = $this->journalEntries()->whereHas('transaction', function ($q) use ($startDate, $endDate) {
            $q->where('status', 'posted');

            if ($startDate) {
                $q->where('transaction_date', '>=', $startDate);
            }

            if ($endDate) {
                $q->where('transaction_date', '<=', $endDate);
            }
        });

        $debits = (clone $query)->where('type', 'debit')->sum('amount');
        $credits = (clone $query)->where('type', 'credit')->sum('amount');

        // For asset and expense accounts, debit increases balance
        if (in_array($this->type, [self::TYPE_ASSET, self::TYPE_EXPENSE])) {
            return $this->opening_balance + $debits - $credits;
        }

        // For liability, equity, and revenue accounts, credit increases balance
        return $this->opening_balance + $credits - $debits;
    }

    /**
     * Update current balance
     */
    public function updateBalance()
    {
        $this->current_balance = $this->calculateBalance();
        $this->save();
    }

    /**
     * Check if account is debit normal
     */
    public function isDebitNormal()
    {
        return in_array($this->type, [self::TYPE_ASSET, self::TYPE_EXPENSE]);
    }

    /**
     * Check if account is credit normal
     */
    public function isCreditNormal()
    {
        return in_array($this->type, [self::TYPE_LIABILITY, self::TYPE_EQUITY, self::TYPE_REVENUE]);
    }

    /**
     * Get account hierarchy path
     */
    public function getHierarchyPathAttribute()
    {
        $path = [$this->name];
        $parent = $this->parent;
        
        while ($parent) {
            array_unshift($path, $parent->name);
            $parent = $parent->parent;
        }
        
        return implode(' > ', $path);
    }

    /**
     * Get account hierarchy path in Arabic
     */
    public function getHierarchyPathArAttribute()
    {
        $path = [$this->name_ar ?: $this->name];
        $parent = $this->parent;
        
        while ($parent) {
            array_unshift($path, $parent->name_ar ?: $parent->name);
            $parent = $parent->parent;
        }
        
        return implode(' > ', $path);
    }

    /**
     * Check if account is active
     */
    public function isActive()
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Scope for active accounts
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope for accounts by type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for root accounts (no parent)
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Get the user who created the account
     */
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Get the user who last updated the account
     */
    public function updater()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    /**
     * Generate account code if not provided
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($account) {
            if (empty($account->code)) {
                $typePrefix = [
                    self::TYPE_ASSET => '1',
                    self::TYPE_LIABILITY => '2',
                    self::TYPE_EQUITY => '3',
                    self::TYPE_REVENUE => '4',
                    self::TYPE_EXPENSE => '5',
                ];
                
                $prefix = $typePrefix[$account->type] ?? '9';
                $count = static::where('type', $account->type)->count() + 1;
                $account->code = $prefix . str_pad($count, 3, '0', STR_PAD_LEFT);
            }
        });
    }
}
