<?php

namespace App\Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inventory extends Model
{
    use HasFactory;

    protected $table = 'inventory';

    protected $fillable = [
        'warehouse_id',
        'product_id',
        'quantity',
        'reserved_quantity',
        'available_quantity',
        'cost_price',
        'average_cost',
        'last_cost',
        'location',
        'bin',
        'aisle',
        'shelf',
        'last_movement_date',
        'last_count_date',
        'last_count_quantity',
        'expiry_date',
        'batch_number',
        'serial_numbers',
        'attributes',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'reserved_quantity' => 'decimal:3',
        'available_quantity' => 'decimal:3',
        'cost_price' => 'decimal:2',
        'average_cost' => 'decimal:2',
        'last_cost' => 'decimal:2',
        'last_count_quantity' => 'decimal:3',
        'last_movement_date' => 'date',
        'last_count_date' => 'date',
        'expiry_date' => 'date',
        'attributes' => 'array',
    ];

    protected $attributes = [
        'quantity' => 0,
        'reserved_quantity' => 0,
        'available_quantity' => 0,
        'cost_price' => 0,
        'average_cost' => 0,
        'last_cost' => 0,
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($inventory) {
            // Calculate available quantity
            $inventory->available_quantity = $inventory->quantity - $inventory->reserved_quantity;
        });
    }

    /**
     * Get the warehouse that owns the inventory
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the product that owns the inventory
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user who created the inventory record
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Get the user who last updated the inventory record
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    /**
     * Scope to filter by warehouse
     */
    public function scopeInWarehouse($query, $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    /**
     * Scope to filter by product
     */
    public function scopeForProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope to filter by available quantity
     */
    public function scopeAvailable($query)
    {
        return $query->where('available_quantity', '>', 0);
    }

    /**
     * Scope to filter by low stock
     */
    public function scopeLowStock($query)
    {
        return $query->whereHas('product', function ($q) {
            $q->whereRaw('inventory.quantity <= products.min_stock_level');
        });
    }

    /**
     * Scope to filter by expiring soon
     */
    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->whereNotNull('expiry_date')
                    ->where('expiry_date', '<=', now()->addDays($days));
    }

    /**
     * Scope to filter by expired
     */
    public function scopeExpired($query)
    {
        return $query->whereNotNull('expiry_date')
                    ->where('expiry_date', '<', now());
    }

    /**
     * Check if the inventory is low stock
     */
    public function isLowStock(): bool
    {
        return $this->quantity <= $this->product->min_stock_level;
    }

    /**
     * Check if the inventory is expiring soon
     */
    public function isExpiringSoon($days = 30): bool
    {
        if (!$this->expiry_date) {
            return false;
        }

        return $this->expiry_date <= now()->addDays($days);
    }

    /**
     * Check if the inventory is expired
     */
    public function isExpired(): bool
    {
        if (!$this->expiry_date) {
            return false;
        }

        return $this->expiry_date < now();
    }

    /**
     * Get the stock value
     */
    public function getStockValueAttribute(): float
    {
        return $this->quantity * $this->average_cost;
    }

    /**
     * Get the available stock value
     */
    public function getAvailableStockValueAttribute(): float
    {
        return $this->available_quantity * $this->average_cost;
    }

    /**
     * Get the reserved stock value
     */
    public function getReservedStockValueAttribute(): float
    {
        return $this->reserved_quantity * $this->average_cost;
    }

    /**
     * Update inventory quantity
     */
    public function updateQuantity(float $quantity, ?string $reason = null): bool
    {
        $oldQuantity = $this->quantity;
        $this->quantity = $quantity;
        $this->available_quantity = $quantity - $this->reserved_quantity;
        $this->last_movement_date = now();

        if ($this->save()) {
            // Log the movement
            InventoryMovement::create([
                'reference_number' => 'ADJ-' . now()->format('YmdHis') . '-' . $this->id,
                'warehouse_id' => $this->warehouse_id,
                'product_id' => $this->product_id,
                'type' => $quantity > $oldQuantity ? 'in' : 'out',
                'source_type' => 'adjustment',
                'quantity' => abs($quantity - $oldQuantity),
                'unit_cost' => $this->average_cost,
                'total_cost' => abs($quantity - $oldQuantity) * $this->average_cost,
                'quantity_before' => $oldQuantity,
                'quantity_after' => $quantity,
                'movement_date' => now(),
                'reason' => $reason ?: 'Manual adjustment',
                'batch_number' => $this->batch_number,
                'expiry_date' => $this->expiry_date,
                'created_by' => auth()->id(),
            ]);

            return true;
        }

        return false;
    }

    /**
     * Reserve inventory quantity
     */
    public function reserve(float $quantity): bool
    {
        if ($this->available_quantity >= $quantity) {
            $this->reserved_quantity += $quantity;
            $this->available_quantity -= $quantity;
            return $this->save();
        }

        return false;
    }

    /**
     * Release reserved inventory quantity
     */
    public function release(float $quantity): bool
    {
        if ($this->reserved_quantity >= $quantity) {
            $this->reserved_quantity -= $quantity;
            $this->available_quantity += $quantity;
            return $this->save();
        }

        return false;
    }
}
