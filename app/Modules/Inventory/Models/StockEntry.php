<?php

namespace App\Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'batch_number',
        'quantity',
        'purchase_price',
        'selling_price',
        'expiry_date',
        'manufacture_date',
        'supplier_id',
        'purchase_order_id',
        'location',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'purchase_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'expiry_date' => 'date',
        'manufacture_date' => 'date',
    ];

    /**
     * Get the product that owns the stock entry
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the warehouse that owns the stock entry
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the supplier
     */
    public function supplier()
    {
        return $this->belongsTo(\App\Modules\Suppliers\Models\Supplier::class);
    }

    /**
     * Get the purchase order
     */
    public function purchaseOrder()
    {
        return $this->belongsTo(\App\Modules\Purchasing\Models\PurchaseOrder::class);
    }

    /**
     * Get the stock movements for this entry
     */
    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Check if stock is expired
     */
    public function isExpired()
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    /**
     * Check if stock is expiring soon
     */
    public function isExpiringSoon($days = 30)
    {
        return $this->expiry_date && 
               $this->expiry_date->isFuture() && 
               $this->expiry_date->diffInDays(now()) <= $days;
    }

    /**
     * Get days until expiry
     */
    public function getDaysUntilExpiry()
    {
        if (!$this->expiry_date) {
            return null;
        }
        
        return $this->expiry_date->diffInDays(now(), false);
    }

    /**
     * Get total value of this stock entry
     */
    public function getTotalValue()
    {
        return $this->quantity * $this->purchase_price;
    }

    /**
     * Get potential selling value
     */
    public function getPotentialSellingValue()
    {
        return $this->quantity * $this->selling_price;
    }

    /**
     * Get profit margin
     */
    public function getProfitMargin()
    {
        if ($this->purchase_price <= 0) {
            return 0;
        }
        
        return (($this->selling_price - $this->purchase_price) / $this->purchase_price) * 100;
    }

    /**
     * Scope for non-expired stock
     */
    public function scopeNotExpired($query)
    {
        return $query->where('expiry_date', '>', now());
    }

    /**
     * Scope for expired stock
     */
    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<=', now());
    }

    /**
     * Scope for stock expiring soon
     */
    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->where('expiry_date', '>', now())
                    ->where('expiry_date', '<=', now()->addDays($days));
    }

    /**
     * Scope for specific warehouse
     */
    public function scopeInWarehouse($query, $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    /**
     * Scope for specific product
     */
    public function scopeForProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope for specific batch
     */
    public function scopeForBatch($query, $batchNumber)
    {
        return $query->where('batch_number', $batchNumber);
    }

    /**
     * Get the user who created the stock entry
     */
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Get the user who last updated the stock entry
     */
    public function updater()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }
}
