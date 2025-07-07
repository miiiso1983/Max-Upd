<?php

namespace App\Modules\Sales\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    const TYPE_INDIVIDUAL = 'individual';
    const TYPE_PHARMACY = 'pharmacy';
    const TYPE_CLINIC = 'clinic';
    const TYPE_HOSPITAL = 'hospital';
    const TYPE_DISTRIBUTOR = 'distributor';
    const TYPE_GOVERNMENT = 'government';

    protected $fillable = [
        'name',
        'name_ar',
        'type',
        'code',
        'email',
        'phone',
        'mobile',
        'address',
        'city',
        'governorate',
        'postal_code',
        'tax_number',
        'license_number',
        'contact_person',
        'contact_phone',
        'contact_email',
        'credit_limit',
        'payment_terms',
        'discount_percentage',
        'is_active',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'payment_terms' => 'integer',
        'is_active' => 'boolean',
    ];

    protected $attributes = [
        'is_active' => true,
        'type' => self::TYPE_INDIVIDUAL,
        'payment_terms' => 30,
        'discount_percentage' => 0,
        'credit_limit' => 0,
    ];

    /**
     * Get the sales orders for this customer
     */
    public function salesOrders()
    {
        return $this->hasMany(SalesOrder::class);
    }

    /**
     * Get the invoices for this customer
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Get the payments for this customer
     */
    public function payments()
    {
        return $this->hasManyThrough(Payment::class, Invoice::class);
    }

    /**
     * Get customer types
     */
    public static function getTypes()
    {
        return [
            self::TYPE_INDIVIDUAL => 'Individual',
            self::TYPE_PHARMACY => 'Pharmacy',
            self::TYPE_CLINIC => 'Clinic',
            self::TYPE_HOSPITAL => 'Hospital',
            self::TYPE_DISTRIBUTOR => 'Distributor',
            self::TYPE_GOVERNMENT => 'Government',
        ];
    }

    /**
     * Get customer types in Arabic
     */
    public static function getTypesAr()
    {
        return [
            self::TYPE_INDIVIDUAL => 'فرد',
            self::TYPE_PHARMACY => 'صيدلية',
            self::TYPE_CLINIC => 'عيادة',
            self::TYPE_HOSPITAL => 'مستشفى',
            self::TYPE_DISTRIBUTOR => 'موزع',
            self::TYPE_GOVERNMENT => 'حكومي',
        ];
    }

    /**
     * Get total sales amount for this customer
     */
    public function getTotalSales()
    {
        return $this->invoices()
                   ->where('status', 'paid')
                   ->sum('total_amount');
    }

    /**
     * Get outstanding balance
     */
    public function getOutstandingBalance()
    {
        return $this->invoices()
                   ->whereIn('status', ['pending', 'overdue'])
                   ->sum('total_amount');
    }

    /**
     * Get available credit
     */
    public function getAvailableCredit()
    {
        return max(0, $this->credit_limit - $this->getOutstandingBalance());
    }

    /**
     * Check if customer has exceeded credit limit
     */
    public function hasExceededCreditLimit()
    {
        return $this->credit_limit > 0 && $this->getOutstandingBalance() > $this->credit_limit;
    }

    /**
     * Get last order date
     */
    public function getLastOrderDate()
    {
        return $this->salesOrders()
                   ->latest()
                   ->value('created_at');
    }

    /**
     * Get total orders count
     */
    public function getTotalOrdersCount()
    {
        return $this->salesOrders()->count();
    }

    /**
     * Scope for active customers
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for customers by type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for customers with outstanding balance
     */
    public function scopeWithOutstandingBalance($query)
    {
        return $query->whereHas('invoices', function ($q) {
            $q->whereIn('status', ['pending', 'overdue']);
        });
    }

    /**
     * Scope for customers who exceeded credit limit
     */
    public function scopeExceededCreditLimit($query)
    {
        return $query->where('credit_limit', '>', 0)
                    ->whereHas('invoices', function ($q) {
                        $q->whereIn('status', ['pending', 'overdue'])
                          ->havingRaw('SUM(total_amount) > customers.credit_limit');
                    });
    }

    /**
     * Get the user who created the customer
     */
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Get the user who last updated the customer
     */
    public function updater()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    /**
     * Generate customer code if not provided
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($customer) {
            if (empty($customer->code)) {
                $prefix = strtoupper(substr($customer->type, 0, 3));
                $customer->code = $prefix . '-' . str_pad(static::count() + 1, 6, '0', STR_PAD_LEFT);
            }
        });
    }
}
