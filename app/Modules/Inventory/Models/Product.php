<?php

namespace App\Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'name_ar',
        'description',
        'description_ar',
        'sku',
        'barcode',
        'category_id',
        'manufacturer_id',
        'unit_of_measure',
        'purchase_price',
        'selling_price',
        'min_stock_level',
        'max_stock_level',
        'reorder_level',
        'is_active',
        'is_prescription_required',
        'is_controlled_substance',
        'expiry_tracking',
        'batch_tracking',
        'image',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'min_stock_level' => 'integer',
        'max_stock_level' => 'integer',
        'reorder_level' => 'integer',
        'is_active' => 'boolean',
        'is_prescription_required' => 'boolean',
        'is_controlled_substance' => 'boolean',
        'expiry_tracking' => 'boolean',
        'batch_tracking' => 'boolean',
    ];

    protected $attributes = [
        'is_active' => true,
        'is_prescription_required' => false,
        'is_controlled_substance' => false,
        'expiry_tracking' => true,
        'batch_tracking' => true,
        'unit_of_measure' => 'piece',
    ];

    /**
     * Get the category that owns the product
     */
    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    /**
     * Get the brand that owns the product
     */
    public function brand()
    {
        return $this->belongsTo(ProductBrand::class, 'brand_id');
    }

    /**
     * Get the inventory records for this product
     */
    public function inventory()
    {
        return $this->hasMany(Inventory::class);
    }

    /**
     * Get the inventory movements for this product
     */
    public function movements()
    {
        return $this->hasMany(InventoryMovement::class);
    }

    /**
     * Get the manufacturer that owns the product
     */
    public function manufacturer()
    {
        return $this->belongsTo(Manufacturer::class);
    }

    /**
     * Get the stock entries for the product
     */
    public function stockEntries()
    {
        return $this->hasMany(StockEntry::class);
    }

    /**
     * Get the stock movements for the product
     */
    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Get the stock levels for the product
     */
    public function stockLevels()
    {
        return $this->hasMany(StockLevel::class);
    }

    /**
     * Get the product batches for the product
     */
    public function batches()
    {
        return $this->hasMany(ProductBatch::class);
    }

    /**
     * Get the product barcodes for the product
     */
    public function barcodes()
    {
        return $this->hasMany(ProductBarcode::class);
    }

    /**
     * Get current stock level across all warehouses
     */
    public function getCurrentStock()
    {
        return $this->stockEntries()
                   ->where('expiry_date', '>', now())
                   ->sum('quantity');
    }

    /**
     * Get current stock level for a specific warehouse
     */
    public function getCurrentStockInWarehouse($warehouseId)
    {
        return $this->stockEntries()
                   ->where('warehouse_id', $warehouseId)
                   ->where('expiry_date', '>', now())
                   ->sum('quantity');
    }

    /**
     * Check if product is low stock
     */
    public function isLowStock()
    {
        return $this->getCurrentStock() <= $this->reorder_level;
    }

    /**
     * Check if product is out of stock
     */
    public function isOutOfStock()
    {
        return $this->getCurrentStock() <= 0;
    }

    /**
     * Get expired stock
     */
    public function getExpiredStock()
    {
        return $this->stockEntries()
                   ->where('expiry_date', '<=', now())
                   ->sum('quantity');
    }

    /**
     * Get stock expiring soon (within 30 days)
     */
    public function getStockExpiringSoon($days = 30)
    {
        return $this->stockEntries()
                   ->where('expiry_date', '>', now())
                   ->where('expiry_date', '<=', now()->addDays($days))
                   ->sum('quantity');
    }

    /**
     * Scope for active products
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for low stock products
     */
    public function scopeLowStock($query)
    {
        return $query->whereRaw('(SELECT SUM(quantity) FROM stock_entries WHERE product_id = products.id AND expiry_date > NOW()) <= reorder_level');
    }

    /**
     * Scope for out of stock products
     */
    public function scopeOutOfStock($query)
    {
        return $query->whereRaw('(SELECT SUM(quantity) FROM stock_entries WHERE product_id = products.id AND expiry_date > NOW()) <= 0');
    }

    /**
     * Scope for products with expiring stock
     */
    public function scopeWithExpiring($query, $days = 30)
    {
        return $query->whereHas('stockEntries', function ($q) use ($days) {
            $q->where('expiry_date', '>', now())
              ->where('expiry_date', '<=', now()->addDays($days));
        });
    }

    /**
     * Get the user who created the product
     */
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Get the user who last updated the product
     */
    public function updater()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    /**
     * Generate SKU if not provided
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->sku)) {
                $product->sku = 'PRD-' . strtoupper(uniqid());
            }
        });
    }
}
