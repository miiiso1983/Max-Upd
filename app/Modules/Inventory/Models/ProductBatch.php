<?php

namespace App\Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class ProductBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'batch_number',
        'lot_number',
        'quantity_received',
        'quantity_remaining',
        'quantity_reserved',
        'manufacture_date',
        'expiry_date',
        'received_date',
        'cost_price',
        'selling_price',
        'supplier_id',
        'purchase_order_id',
        'storage_location',
        'storage_conditions',
        'temperature_min',
        'temperature_max',
        'humidity_min',
        'humidity_max',
        'is_quarantined',
        'quarantine_reason',
        'quality_status',
        'quality_notes',
        'barcode',
        'notes',
        'notes_ar',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'quantity_received' => 'decimal:2',
        'quantity_remaining' => 'decimal:2',
        'quantity_reserved' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'temperature_min' => 'decimal:1',
        'temperature_max' => 'decimal:1',
        'humidity_min' => 'decimal:1',
        'humidity_max' => 'decimal:1',
        'manufacture_date' => 'date',
        'expiry_date' => 'date',
        'received_date' => 'date',
        'is_quarantined' => 'boolean',
    ];

    // Quality status constants
    const QUALITY_PENDING = 'pending';
    const QUALITY_APPROVED = 'approved';
    const QUALITY_REJECTED = 'rejected';
    const QUALITY_ON_HOLD = 'on_hold';

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

    public function supplier()
    {
        return $this->belongsTo(\App\Modules\Suppliers\Models\Supplier::class);
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(\App\Modules\Purchasing\Models\PurchaseOrder::class);
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
        return $this->hasMany(StockMovement::class, 'batch_id');
    }

    public function qualityChecks()
    {
        return $this->hasMany(QualityCheck::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('quantity_remaining', '>', 0);
    }

    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', now());
    }

    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->where('expiry_date', '>', now())
                    ->where('expiry_date', '<=', now()->addDays($days));
    }

    public function scopeQuarantined($query)
    {
        return $query->where('is_quarantined', true);
    }

    public function scopeByQualityStatus($query, $status)
    {
        return $query->where('quality_status', $status);
    }

    public function scopeInWarehouse($query, $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    /**
     * Accessors
     */
    public function getIsExpiredAttribute()
    {
        return $this->expiry_date && $this->expiry_date < now();
    }

    public function getIsExpiringSoonAttribute()
    {
        return $this->expiry_date && 
               $this->expiry_date > now() && 
               $this->expiry_date <= now()->addDays(30);
    }

    public function getDaysUntilExpiryAttribute()
    {
        if (!$this->expiry_date) {
            return null;
        }
        
        return $this->expiry_date->diffInDays(now(), false);
    }

    public function getAvailableQuantityAttribute()
    {
        return $this->quantity_remaining - $this->quantity_reserved;
    }

    public function getTotalValueAttribute()
    {
        return $this->quantity_remaining * $this->cost_price;
    }

    public function getPotentialSellingValueAttribute()
    {
        return $this->quantity_remaining * $this->selling_price;
    }

    public function getProfitMarginAttribute()
    {
        if ($this->cost_price <= 0) {
            return 0;
        }
        
        return (($this->selling_price - $this->cost_price) / $this->cost_price) * 100;
    }

    public function getQualityStatusLabelAttribute()
    {
        $labels = [
            self::QUALITY_PENDING => 'Pending',
            self::QUALITY_APPROVED => 'Approved',
            self::QUALITY_REJECTED => 'Rejected',
            self::QUALITY_ON_HOLD => 'On Hold',
        ];

        return $labels[$this->quality_status] ?? 'Unknown';
    }

    public function getQualityStatusLabelArAttribute()
    {
        $labels = [
            self::QUALITY_PENDING => 'في الانتظار',
            self::QUALITY_APPROVED => 'معتمد',
            self::QUALITY_REJECTED => 'مرفوض',
            self::QUALITY_ON_HOLD => 'معلق',
        ];

        return $labels[$this->quality_status] ?? 'غير معروف';
    }

    /**
     * Methods
     */
    public function reserveQuantity($quantity)
    {
        if ($this->available_quantity < $quantity) {
            throw new \Exception('Insufficient available quantity to reserve');
        }
        
        $this->increment('quantity_reserved', $quantity);
        
        return $this;
    }

    public function releaseReservation($quantity)
    {
        if ($this->quantity_reserved < $quantity) {
            throw new \Exception('Cannot release more than reserved quantity');
        }
        
        $this->decrement('quantity_reserved', $quantity);
        
        return $this;
    }

    public function consumeQuantity($quantity, $reason = 'sale')
    {
        if ($this->quantity_remaining < $quantity) {
            throw new \Exception('Insufficient quantity remaining');
        }
        
        $this->decrement('quantity_remaining', $quantity);
        
        // If quantity was reserved, reduce reservation
        if ($this->quantity_reserved > 0) {
            $reservedToReduce = min($quantity, $this->quantity_reserved);
            $this->decrement('quantity_reserved', $reservedToReduce);
        }
        
        // Log stock movement
        $this->stockMovements()->create([
            'product_id' => $this->product_id,
            'warehouse_id' => $this->warehouse_id,
            'movement_type' => StockMovement::TYPE_OUT,
            'quantity' => $quantity,
            'reference_type' => $reason,
            'notes' => "Consumed from batch {$this->batch_number}",
            'created_by' => auth()->id(),
        ]);
        
        return $this;
    }

    public function quarantine($reason)
    {
        $this->update([
            'is_quarantined' => true,
            'quarantine_reason' => $reason,
            'quality_status' => self::QUALITY_ON_HOLD,
        ]);
        
        return $this;
    }

    public function releaseFromQuarantine()
    {
        $this->update([
            'is_quarantined' => false,
            'quarantine_reason' => null,
            'quality_status' => self::QUALITY_APPROVED,
        ]);
        
        return $this;
    }

    public function approveQuality($notes = null)
    {
        $this->update([
            'quality_status' => self::QUALITY_APPROVED,
            'quality_notes' => $notes,
        ]);
        
        return $this;
    }

    public function rejectQuality($reason)
    {
        $this->update([
            'quality_status' => self::QUALITY_REJECTED,
            'quality_notes' => $reason,
            'is_quarantined' => true,
            'quarantine_reason' => "Quality rejected: {$reason}",
        ]);
        
        return $this;
    }

    public function generateBarcode()
    {
        if (empty($this->barcode)) {
            // Generate batch-specific barcode
            $this->barcode = 'BATCH-' . $this->product_id . '-' . $this->id . '-' . time();
            $this->save();
        }
        
        return $this->barcode;
    }

    public function isWithinStorageConditions($temperature, $humidity)
    {
        $temperatureOk = true;
        $humidityOk = true;
        
        if ($this->temperature_min !== null && $temperature < $this->temperature_min) {
            $temperatureOk = false;
        }
        
        if ($this->temperature_max !== null && $temperature > $this->temperature_max) {
            $temperatureOk = false;
        }
        
        if ($this->humidity_min !== null && $humidity < $this->humidity_min) {
            $humidityOk = false;
        }
        
        if ($this->humidity_max !== null && $humidity > $this->humidity_max) {
            $humidityOk = false;
        }
        
        return $temperatureOk && $humidityOk;
    }
}
