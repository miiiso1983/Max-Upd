<?php

namespace App\Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class StockCount extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'batch_id',
        'system_quantity',
        'counted_quantity',
        'difference',
        'variance_percentage',
        'count_type',
        'status',
        'notes',
        'notes_ar',
        'counted_by',
        'verified_by',
        'counted_at',
        'verified_at',
        'created_by',
    ];

    protected $casts = [
        'system_quantity' => 'decimal:2',
        'counted_quantity' => 'decimal:2',
        'difference' => 'decimal:2',
        'variance_percentage' => 'decimal:2',
        'counted_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    // Count type constants
    const TYPE_FULL = 'full';
    const TYPE_CYCLE = 'cycle';
    const TYPE_SPOT = 'spot';
    const TYPE_BATCH = 'batch';

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_COUNTED = 'counted';
    const STATUS_VERIFIED = 'verified';
    const STATUS_ADJUSTED = 'adjusted';
    const STATUS_REJECTED = 'rejected';

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($stockCount) {
            // Calculate variance percentage
            if ($stockCount->system_quantity > 0) {
                $stockCount->variance_percentage = (($stockCount->difference / $stockCount->system_quantity) * 100);
            } else {
                $stockCount->variance_percentage = $stockCount->counted_quantity > 0 ? 100 : 0;
            }
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

    public function batch()
    {
        return $this->belongsTo(ProductBatch::class);
    }

    public function stockLevel()
    {
        return $this->belongsTo(StockLevel::class, 'product_id', 'product_id')
                   ->where('warehouse_id', $this->warehouse_id);
    }

    public function countedBy()
    {
        return $this->belongsTo(User::class, 'counted_by');
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scopes
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('count_type', $type);
    }

    public function scopeWithVariance($query, $threshold = 5)
    {
        return $query->where(function ($q) use ($threshold) {
            $q->where('variance_percentage', '>', $threshold)
              ->orWhere('variance_percentage', '<', -$threshold);
        });
    }

    public function scopeInWarehouse($query, $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    public function scopeForProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('counted_at', '>=', now()->subDays($days));
    }

    /**
     * Accessors
     */
    public function getCountTypeLabelAttribute()
    {
        $labels = [
            self::TYPE_FULL => 'Full Count',
            self::TYPE_CYCLE => 'Cycle Count',
            self::TYPE_SPOT => 'Spot Count',
            self::TYPE_BATCH => 'Batch Count',
        ];

        return $labels[$this->count_type] ?? 'Unknown';
    }

    public function getCountTypeLabelArAttribute()
    {
        $labels = [
            self::TYPE_FULL => 'جرد كامل',
            self::TYPE_CYCLE => 'جرد دوري',
            self::TYPE_SPOT => 'جرد فوري',
            self::TYPE_BATCH => 'جرد الدفعة',
        ];

        return $labels[$this->count_type] ?? 'غير معروف';
    }

    public function getStatusLabelAttribute()
    {
        $labels = [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_COUNTED => 'Counted',
            self::STATUS_VERIFIED => 'Verified',
            self::STATUS_ADJUSTED => 'Adjusted',
            self::STATUS_REJECTED => 'Rejected',
        ];

        return $labels[$this->status] ?? 'Unknown';
    }

    public function getStatusLabelArAttribute()
    {
        $labels = [
            self::STATUS_PENDING => 'في الانتظار',
            self::STATUS_COUNTED => 'تم الجرد',
            self::STATUS_VERIFIED => 'تم التحقق',
            self::STATUS_ADJUSTED => 'تم التعديل',
            self::STATUS_REJECTED => 'مرفوض',
        ];

        return $labels[$this->status] ?? 'غير معروف';
    }

    public function getHasVarianceAttribute()
    {
        return $this->difference != 0;
    }

    public function getIsSignificantVarianceAttribute($threshold = 5)
    {
        return abs($this->variance_percentage) > $threshold;
    }

    public function getVarianceTypeAttribute()
    {
        if ($this->difference > 0) {
            return 'overage';
        } elseif ($this->difference < 0) {
            return 'shortage';
        } else {
            return 'none';
        }
    }

    public function getVarianceTypeLabelAttribute()
    {
        $labels = [
            'overage' => 'Overage',
            'shortage' => 'Shortage',
            'none' => 'No Variance',
        ];

        return $labels[$this->variance_type] ?? 'Unknown';
    }

    public function getVarianceTypeLabelArAttribute()
    {
        $labels = [
            'overage' => 'زيادة',
            'shortage' => 'نقص',
            'none' => 'لا يوجد فرق',
        ];

        return $labels[$this->variance_type] ?? 'غير معروف';
    }

    /**
     * Methods
     */
    public function verify($userId = null, $notes = null)
    {
        $this->update([
            'status' => self::STATUS_VERIFIED,
            'verified_by' => $userId ?? auth()->id(),
            'verified_at' => now(),
            'notes' => $notes ? $this->notes . "\nVerification: " . $notes : $this->notes,
        ]);

        return $this;
    }

    public function reject($reason, $userId = null)
    {
        $this->update([
            'status' => self::STATUS_REJECTED,
            'verified_by' => $userId ?? auth()->id(),
            'verified_at' => now(),
            'notes' => $this->notes . "\nRejected: " . $reason,
        ]);

        return $this;
    }

    public function applyAdjustment($userId = null)
    {
        if ($this->status !== self::STATUS_VERIFIED) {
            throw new \Exception('Stock count must be verified before applying adjustment');
        }

        if ($this->difference == 0) {
            $this->update(['status' => self::STATUS_ADJUSTED]);
            return $this;
        }

        // Update stock level
        $stockLevel = $this->stockLevel;
        if ($stockLevel) {
            $stockLevel->update(['current_stock' => $this->counted_quantity]);
        }

        // Create stock movement
        StockMovement::create([
            'product_id' => $this->product_id,
            'warehouse_id' => $this->warehouse_id,
            'batch_id' => $this->batch_id,
            'movement_type' => $this->difference > 0 ? StockMovement::TYPE_ADJUSTMENT_IN : StockMovement::TYPE_ADJUSTMENT_OUT,
            'quantity' => abs($this->difference),
            'reference_type' => 'stock_count',
            'reference_id' => $this->id,
            'notes' => "Stock count adjustment - {$this->count_type} count",
            'created_by' => $userId ?? auth()->id(),
        ]);

        $this->update(['status' => self::STATUS_ADJUSTED]);

        return $this;
    }

    public function requiresApproval($threshold = 10)
    {
        return abs($this->variance_percentage) > $threshold;
    }

    public function getFinancialImpact()
    {
        $product = $this->product;
        if (!$product) {
            return 0;
        }

        return $this->difference * $product->cost_price;
    }

    public static function generateCycleCounts($warehouseId = null, $categoryId = null, $limit = 50)
    {
        $query = Product::active();

        if ($warehouseId) {
            $query->whereHas('stockLevels', function ($q) use ($warehouseId) {
                $q->where('warehouse_id', $warehouseId);
            });
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        // Prioritize products that haven't been counted recently
        $products = $query->with(['stockLevels' => function ($q) use ($warehouseId) {
                              if ($warehouseId) {
                                  $q->where('warehouse_id', $warehouseId);
                              }
                          }])
                          ->limit($limit)
                          ->get();

        $counts = [];
        foreach ($products as $product) {
            foreach ($product->stockLevels as $stockLevel) {
                $counts[] = static::create([
                    'product_id' => $product->id,
                    'warehouse_id' => $stockLevel->warehouse_id,
                    'system_quantity' => $stockLevel->current_stock,
                    'counted_quantity' => 0,
                    'difference' => 0,
                    'count_type' => self::TYPE_CYCLE,
                    'status' => self::STATUS_PENDING,
                    'created_by' => auth()->id() ?? 1,
                ]);
            }
        }

        return collect($counts);
    }
}
