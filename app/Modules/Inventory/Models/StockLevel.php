<?php

namespace App\Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class StockLevel extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'current_stock',
        'reserved_stock',
        'available_stock',
        'minimum_stock',
        'maximum_stock',
        'reorder_point',
        'reorder_quantity',
        'last_counted_at',
        'last_movement_at',
        'average_daily_usage',
        'lead_time_days',
        'safety_stock',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'current_stock' => 'decimal:2',
        'reserved_stock' => 'decimal:2',
        'available_stock' => 'decimal:2',
        'minimum_stock' => 'decimal:2',
        'maximum_stock' => 'decimal:2',
        'reorder_point' => 'decimal:2',
        'reorder_quantity' => 'decimal:2',
        'average_daily_usage' => 'decimal:4',
        'safety_stock' => 'decimal:2',
        'lead_time_days' => 'integer',
        'last_counted_at' => 'datetime',
        'last_movement_at' => 'datetime',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($stockLevel) {
            // Calculate available stock
            $stockLevel->available_stock = $stockLevel->current_stock - $stockLevel->reserved_stock;
        });
    }

    /**
     * Relationships
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class, 'product_id', 'product_id')
                   ->where('warehouse_id', $this->warehouse_id);
    }

    public function stockCounts()
    {
        return $this->hasMany(StockCount::class);
    }

    /**
     * Scopes
     */
    public function scopeLowStock($query)
    {
        return $query->whereRaw('current_stock <= reorder_point');
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('current_stock', '<=', 0);
    }

    public function scopeOverStock($query)
    {
        return $query->whereRaw('current_stock > maximum_stock');
    }

    public function scopeNeedsReorder($query)
    {
        return $query->whereRaw('available_stock <= reorder_point');
    }

    public function scopeInWarehouse($query, $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    /**
     * Accessors
     */
    public function getIsLowStockAttribute()
    {
        return $this->current_stock <= $this->reorder_point;
    }

    public function getIsOutOfStockAttribute()
    {
        return $this->current_stock <= 0;
    }

    public function getIsOverStockAttribute()
    {
        return $this->current_stock > $this->maximum_stock;
    }

    public function getNeedsReorderAttribute()
    {
        return $this->available_stock <= $this->reorder_point;
    }

    public function getStockStatusAttribute()
    {
        if ($this->is_out_of_stock) {
            return 'out_of_stock';
        } elseif ($this->is_low_stock) {
            return 'low_stock';
        } elseif ($this->is_over_stock) {
            return 'over_stock';
        } else {
            return 'normal';
        }
    }

    public function getStockStatusLabelAttribute()
    {
        $labels = [
            'out_of_stock' => 'Out of Stock',
            'low_stock' => 'Low Stock',
            'over_stock' => 'Over Stock',
            'normal' => 'Normal',
        ];

        return $labels[$this->stock_status] ?? 'Unknown';
    }

    public function getStockStatusLabelArAttribute()
    {
        $labels = [
            'out_of_stock' => 'نفد المخزون',
            'low_stock' => 'مخزون منخفض',
            'over_stock' => 'مخزون زائد',
            'normal' => 'طبيعي',
        ];

        return $labels[$this->stock_status] ?? 'غير معروف';
    }

    public function getDaysOfStockAttribute()
    {
        if ($this->average_daily_usage > 0) {
            return $this->available_stock / $this->average_daily_usage;
        }
        return null;
    }

    public function getStockTurnoverRateAttribute()
    {
        // Calculate based on last 30 days of movements
        $thirtyDaysAgo = now()->subDays(30);
        $totalOut = $this->stockMovements()
                        ->where('movement_type', StockMovement::TYPE_OUT)
                        ->where('created_at', '>=', $thirtyDaysAgo)
                        ->sum('quantity');
        
        if ($this->current_stock > 0) {
            return $totalOut / $this->current_stock;
        }
        return 0;
    }

    /**
     * Methods
     */
    public function addStock($quantity, $reason = 'manual_adjustment')
    {
        $this->increment('current_stock', $quantity);
        $this->update(['last_movement_at' => now()]);
        
        // Log stock movement
        StockMovement::create([
            'product_id' => $this->product_id,
            'warehouse_id' => $this->warehouse_id,
            'movement_type' => StockMovement::TYPE_IN,
            'quantity' => $quantity,
            'reference_type' => $reason,
            'notes' => "Stock added: {$reason}",
            'created_by' => auth()->id(),
        ]);
        
        return $this;
    }

    public function removeStock($quantity, $reason = 'manual_adjustment')
    {
        if ($this->available_stock < $quantity) {
            throw new \Exception('Insufficient available stock');
        }
        
        $this->decrement('current_stock', $quantity);
        $this->update(['last_movement_at' => now()]);
        
        // Log stock movement
        StockMovement::create([
            'product_id' => $this->product_id,
            'warehouse_id' => $this->warehouse_id,
            'movement_type' => StockMovement::TYPE_OUT,
            'quantity' => $quantity,
            'reference_type' => $reason,
            'notes' => "Stock removed: {$reason}",
            'created_by' => auth()->id(),
        ]);
        
        return $this;
    }

    public function reserveStock($quantity, $reason = 'sale')
    {
        if ($this->available_stock < $quantity) {
            throw new \Exception('Insufficient available stock to reserve');
        }
        
        $this->increment('reserved_stock', $quantity);
        
        // Log reservation
        StockMovement::create([
            'product_id' => $this->product_id,
            'warehouse_id' => $this->warehouse_id,
            'movement_type' => StockMovement::TYPE_RESERVED,
            'quantity' => $quantity,
            'reference_type' => $reason,
            'notes' => "Stock reserved: {$reason}",
            'created_by' => auth()->id(),
        ]);
        
        return $this;
    }

    public function releaseReservation($quantity, $reason = 'cancelled')
    {
        if ($this->reserved_stock < $quantity) {
            throw new \Exception('Cannot release more than reserved quantity');
        }
        
        $this->decrement('reserved_stock', $quantity);
        
        // Log reservation release
        StockMovement::create([
            'product_id' => $this->product_id,
            'warehouse_id' => $this->warehouse_id,
            'movement_type' => StockMovement::TYPE_UNRESERVED,
            'quantity' => $quantity,
            'reference_type' => $reason,
            'notes' => "Reservation released: {$reason}",
            'created_by' => auth()->id(),
        ]);
        
        return $this;
    }

    public function updateStockCount($countedQuantity, $notes = null)
    {
        $difference = $countedQuantity - $this->current_stock;
        
        // Create stock count record
        $stockCount = $this->stockCounts()->create([
            'product_id' => $this->product_id,
            'warehouse_id' => $this->warehouse_id,
            'system_quantity' => $this->current_stock,
            'counted_quantity' => $countedQuantity,
            'difference' => $difference,
            'notes' => $notes,
            'counted_by' => auth()->id(),
            'counted_at' => now(),
        ]);
        
        // Update stock level
        $this->update([
            'current_stock' => $countedQuantity,
            'last_counted_at' => now(),
        ]);
        
        // Log adjustment if there's a difference
        if ($difference != 0) {
            StockMovement::create([
                'product_id' => $this->product_id,
                'warehouse_id' => $this->warehouse_id,
                'movement_type' => $difference > 0 ? StockMovement::TYPE_ADJUSTMENT_IN : StockMovement::TYPE_ADJUSTMENT_OUT,
                'quantity' => abs($difference),
                'reference_type' => 'stock_count',
                'reference_id' => $stockCount->id,
                'notes' => "Stock count adjustment: {$notes}",
                'created_by' => auth()->id(),
            ]);
        }
        
        return $stockCount;
    }

    public function calculateReorderPoint()
    {
        // Reorder Point = (Average Daily Usage × Lead Time) + Safety Stock
        if ($this->average_daily_usage > 0 && $this->lead_time_days > 0) {
            $calculatedReorderPoint = ($this->average_daily_usage * $this->lead_time_days) + $this->safety_stock;
            
            $this->update(['reorder_point' => $calculatedReorderPoint]);
            
            return $calculatedReorderPoint;
        }
        
        return $this->reorder_point;
    }

    public function updateAverageDailyUsage($days = 30)
    {
        $startDate = now()->subDays($days);
        
        $totalUsage = $this->stockMovements()
                          ->where('movement_type', StockMovement::TYPE_OUT)
                          ->where('created_at', '>=', $startDate)
                          ->sum('quantity');
        
        $averageDailyUsage = $totalUsage / $days;
        
        $this->update(['average_daily_usage' => $averageDailyUsage]);
        
        return $averageDailyUsage;
    }
}
