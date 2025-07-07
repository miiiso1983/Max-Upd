<?php

namespace App\Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warehouse extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'name_ar',
        'code',
        'description',
        'description_ar',
        'address',
        'city',
        'governorate',
        'phone',
        'email',
        'manager_id',
        'is_active',
        'is_main',
        'capacity',
        'current_utilization',
        'temperature_controlled',
        'min_temperature',
        'max_temperature',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_main' => 'boolean',
        'temperature_controlled' => 'boolean',
        'capacity' => 'decimal:2',
        'current_utilization' => 'decimal:2',
        'min_temperature' => 'decimal:1',
        'max_temperature' => 'decimal:1',
    ];

    protected $attributes = [
        'is_active' => true,
        'is_main' => false,
        'temperature_controlled' => false,
        'current_utilization' => 0,
    ];

    /**
     * Get the warehouse manager
     */
    public function manager()
    {
        return $this->belongsTo(\App\Models\User::class, 'manager_id');
    }

    /**
     * Get the inventory records for this warehouse
     */
    public function inventory()
    {
        return $this->hasMany(Inventory::class);
    }

    /**
     * Get the inventory movements for this warehouse
     */
    public function movements()
    {
        return $this->hasMany(InventoryMovement::class);
    }

    /**
     * Get the outbound movements from this warehouse
     */
    public function outboundMovements()
    {
        return $this->hasMany(InventoryMovement::class, 'from_warehouse_id');
    }

    /**
     * Get the inbound movements to this warehouse
     */
    public function inboundMovements()
    {
        return $this->hasMany(InventoryMovement::class, 'to_warehouse_id');
    }

    /**
     * Get the stock entries in this warehouse
     */
    public function stockEntries()
    {
        return $this->hasMany(StockEntry::class);
    }

    public function locations()
    {
        return $this->hasMany(Location::class);
    }

    /**
     * Get the stock movements for this warehouse
     */
    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Get current stock value in this warehouse
     */
    public function getCurrentStockValue()
    {
        return $this->stockEntries()
                   ->join('products', 'stock_entries.product_id', '=', 'products.id')
                   ->where('stock_entries.expiry_date', '>', now())
                   ->selectRaw('SUM(stock_entries.quantity * products.purchase_price) as total_value')
                   ->value('total_value') ?? 0;
    }

    /**
     * Get total products count in this warehouse
     */
    public function getTotalProductsCount()
    {
        return $this->stockEntries()
                   ->where('expiry_date', '>', now())
                   ->distinct('product_id')
                   ->count('product_id');
    }

    /**
     * Get total stock quantity in this warehouse
     */
    public function getTotalStockQuantity()
    {
        return $this->stockEntries()
                   ->where('expiry_date', '>', now())
                   ->sum('quantity');
    }

    /**
     * Get utilization percentage
     */
    public function getUtilizationPercentage()
    {
        if (!$this->capacity || $this->capacity <= 0) {
            return 0;
        }
        
        return ($this->current_utilization / $this->capacity) * 100;
    }

    /**
     * Check if warehouse is over capacity
     */
    public function isOverCapacity()
    {
        return $this->capacity > 0 && $this->current_utilization > $this->capacity;
    }

    /**
     * Get low stock products in this warehouse
     */
    public function getLowStockProducts()
    {
        return Product::whereHas('stockEntries', function ($query) {
            $query->where('warehouse_id', $this->id)
                  ->where('expiry_date', '>', now());
        })->whereRaw('(SELECT SUM(quantity) FROM stock_entries WHERE product_id = products.id AND warehouse_id = ? AND expiry_date > NOW()) <= products.reorder_level', [$this->id]);
    }

    /**
     * Get expired stock in this warehouse
     */
    public function getExpiredStock()
    {
        return $this->stockEntries()
                   ->where('expiry_date', '<=', now())
                   ->with('product')
                   ->get();
    }

    /**
     * Get stock expiring soon in this warehouse
     */
    public function getStockExpiringSoon($days = 30)
    {
        return $this->stockEntries()
                   ->where('expiry_date', '>', now())
                   ->where('expiry_date', '<=', now()->addDays($days))
                   ->with('product')
                   ->get();
    }

    /**
     * Scope for active warehouses
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for main warehouse
     */
    public function scopeMain($query)
    {
        return $query->where('is_main', true);
    }

    /**
     * Scope for temperature controlled warehouses
     */
    public function scopeTemperatureControlled($query)
    {
        return $query->where('temperature_controlled', true);
    }

    /**
     * Get the user who created the warehouse
     */
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Get the user who last updated the warehouse
     */
    public function updater()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    /**
     * Generate code if not provided
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($warehouse) {
            if (empty($warehouse->code)) {
                $warehouse->code = 'WH-' . strtoupper(uniqid());
            }
        });

        static::saving(function ($warehouse) {
            // Ensure only one main warehouse
            if ($warehouse->is_main) {
                static::where('id', '!=', $warehouse->id)->update(['is_main' => false]);
            }
        });
    }
}
