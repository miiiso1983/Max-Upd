<?php

namespace App\Modules\Suppliers\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    const TYPE_MANUFACTURER = 'manufacturer';
    const TYPE_DISTRIBUTOR = 'distributor';
    const TYPE_WHOLESALER = 'wholesaler';
    const TYPE_IMPORTER = 'importer';
    const TYPE_LOCAL = 'local';

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_SUSPENDED = 'suspended';
    const STATUS_BLACKLISTED = 'blacklisted';

    protected $fillable = [
        'name',
        'name_ar',
        'type',
        'code',
        'status',
        'email',
        'phone',
        'mobile',
        'fax',
        'website',
        'address',
        'city',
        'governorate',
        'country',
        'postal_code',
        'tax_number',
        'license_number',
        'contact_person',
        'contact_phone',
        'contact_email',
        'payment_terms',
        'credit_limit',
        'currency',
        'bank_name',
        'bank_account',
        'iban',
        'swift_code',
        'rating',
        'notes',
        'is_preferred',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'payment_terms' => 'integer',
        'credit_limit' => 'decimal:2',
        'rating' => 'decimal:1',
        'is_preferred' => 'boolean',
    ];

    protected $attributes = [
        'status' => self::STATUS_ACTIVE,
        'type' => self::TYPE_DISTRIBUTOR,
        'payment_terms' => 30,
        'credit_limit' => 0,
        'currency' => 'IQD',
        'rating' => 0,
        'is_preferred' => false,
        'country' => 'Iraq',
    ];

    /**
     * Get the purchase orders for this supplier
     */
    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    /**
     * Get the products supplied by this supplier
     */
    public function products()
    {
        return $this->belongsToMany(
            \App\Modules\Inventory\Models\Product::class,
            'supplier_products',
            'supplier_id',
            'product_id'
        )->withPivot(['supplier_sku', 'unit_cost', 'minimum_order_quantity', 'lead_time_days', 'is_preferred']);
    }

    /**
     * Get supplier types
     */
    public static function getTypes()
    {
        return [
            self::TYPE_MANUFACTURER => 'Manufacturer',
            self::TYPE_DISTRIBUTOR => 'Distributor',
            self::TYPE_WHOLESALER => 'Wholesaler',
            self::TYPE_IMPORTER => 'Importer',
            self::TYPE_LOCAL => 'Local Supplier',
        ];
    }

    /**
     * Get supplier types in Arabic
     */
    public static function getTypesAr()
    {
        return [
            self::TYPE_MANUFACTURER => 'مصنع',
            self::TYPE_DISTRIBUTOR => 'موزع',
            self::TYPE_WHOLESALER => 'تاجر جملة',
            self::TYPE_IMPORTER => 'مستورد',
            self::TYPE_LOCAL => 'مورد محلي',
        ];
    }

    /**
     * Get supplier statuses
     */
    public static function getStatuses()
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
            self::STATUS_SUSPENDED => 'Suspended',
            self::STATUS_BLACKLISTED => 'Blacklisted',
        ];
    }

    /**
     * Get supplier statuses in Arabic
     */
    public static function getStatusesAr()
    {
        return [
            self::STATUS_ACTIVE => 'نشط',
            self::STATUS_INACTIVE => 'غير نشط',
            self::STATUS_SUSPENDED => 'معلق',
            self::STATUS_BLACKLISTED => 'محظور',
        ];
    }

    /**
     * Get total purchase amount from this supplier
     */
    public function getTotalPurchases()
    {
        return $this->purchaseOrders()
                   ->where('status', 'completed')
                   ->sum('total_amount');
    }

    /**
     * Get outstanding purchase orders amount
     */
    public function getOutstandingAmount()
    {
        return $this->purchaseOrders()
                   ->whereIn('status', ['pending', 'confirmed', 'partial'])
                   ->sum('total_amount');
    }

    /**
     * Get last order date
     */
    public function getLastOrderDate()
    {
        return $this->purchaseOrders()
                   ->latest('order_date')
                   ->value('order_date');
    }

    /**
     * Get total orders count
     */
    public function getTotalOrdersCount()
    {
        return $this->purchaseOrders()->count();
    }

    /**
     * Get average order value
     */
    public function getAverageOrderValue()
    {
        return $this->purchaseOrders()->avg('total_amount') ?? 0;
    }

    /**
     * Get supplier performance rating
     */
    public function getPerformanceRating()
    {
        $totalOrders = $this->getTotalOrdersCount();
        if ($totalOrders === 0) {
            return 0;
        }

        $onTimeDeliveries = $this->purchaseOrders()
                                ->where('status', 'completed')
                                ->whereColumn('delivered_date', '<=', 'expected_delivery_date')
                                ->count();

        return ($onTimeDeliveries / $totalOrders) * 100;
    }

    /**
     * Check if supplier is active
     */
    public function isActive()
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if supplier is preferred
     */
    public function isPreferred()
    {
        return $this->is_preferred;
    }

    /**
     * Scope for active suppliers
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope for preferred suppliers
     */
    public function scopePreferred($query)
    {
        return $query->where('is_preferred', true);
    }

    /**
     * Scope for suppliers by type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for suppliers by country
     */
    public function scopeFromCountry($query, $country)
    {
        return $query->where('country', $country);
    }

    /**
     * Get the user who created the supplier
     */
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Get the user who last updated the supplier
     */
    public function updater()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    /**
     * Generate supplier code if not provided
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($supplier) {
            if (empty($supplier->code)) {
                $prefix = strtoupper(substr($supplier->type, 0, 3));
                $supplier->code = $prefix . '-' . str_pad(static::count() + 1, 6, '0', STR_PAD_LEFT);
            }
        });
    }
}
