<?php

namespace App\Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class Location extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'warehouse_id',
        'code',
        'name',
        'name_ar',
        'description',
        'description_ar',
        'type',
        'zone',
        'aisle',
        'rack',
        'shelf',
        'bin',
        'barcode',
        'capacity',
        'current_utilization',
        'temperature_controlled',
        'temperature_min',
        'temperature_max',
        'humidity_controlled',
        'humidity_min',
        'humidity_max',
        'is_active',
        'is_pickable',
        'is_receivable',
        'priority',
        'notes',
        'notes_ar',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'capacity' => 'decimal:2',
        'current_utilization' => 'decimal:2',
        'temperature_min' => 'decimal:1',
        'temperature_max' => 'decimal:1',
        'humidity_min' => 'decimal:1',
        'humidity_max' => 'decimal:1',
        'temperature_controlled' => 'boolean',
        'humidity_controlled' => 'boolean',
        'is_active' => 'boolean',
        'is_pickable' => 'boolean',
        'is_receivable' => 'boolean',
        'priority' => 'integer',
    ];

    // Location type constants
    const TYPE_RECEIVING = 'receiving';
    const TYPE_STORAGE = 'storage';
    const TYPE_PICKING = 'picking';
    const TYPE_SHIPPING = 'shipping';
    const TYPE_QUARANTINE = 'quarantine';
    const TYPE_DAMAGE = 'damage';
    const TYPE_RETURN = 'return';
    const TYPE_STAGING = 'staging';

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($location) {
            if (empty($location->code)) {
                $location->code = static::generateLocationCode($location);
            }
        });
    }

    /**
     * Generate location code
     */
    public static function generateLocationCode($location)
    {
        $parts = [];
        
        if ($location->zone) {
            $parts[] = $location->zone;
        }
        
        if ($location->aisle) {
            $parts[] = str_pad($location->aisle, 2, '0', STR_PAD_LEFT);
        }
        
        if ($location->rack) {
            $parts[] = str_pad($location->rack, 2, '0', STR_PAD_LEFT);
        }
        
        if ($location->shelf) {
            $parts[] = str_pad($location->shelf, 2, '0', STR_PAD_LEFT);
        }
        
        if ($location->bin) {
            $parts[] = str_pad($location->bin, 2, '0', STR_PAD_LEFT);
        }
        
        return implode('-', $parts) ?: 'LOC-' . time();
    }

    /**
     * Relationships
     */
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

    public function stockLevels()
    {
        return $this->hasMany(StockLevel::class, 'warehouse_id', 'warehouse_id');
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class, 'warehouse_id', 'warehouse_id');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_locations')
                    ->withPivot(['minimum_stock', 'maximum_stock', 'current_stock'])
                    ->withTimestamps();
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopePickable($query)
    {
        return $query->where('is_pickable', true);
    }

    public function scopeReceivable($query)
    {
        return $query->where('is_receivable', true);
    }

    public function scopeInWarehouse($query, $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    public function scopeByZone($query, $zone)
    {
        return $query->where('zone', $zone);
    }

    public function scopeTemperatureControlled($query)
    {
        return $query->where('temperature_controlled', true);
    }

    public function scopeHumidityControlled($query)
    {
        return $query->where('humidity_controlled', true);
    }

    /**
     * Accessors
     */
    public function getTypeLabelAttribute()
    {
        $labels = [
            self::TYPE_RECEIVING => 'Receiving',
            self::TYPE_STORAGE => 'Storage',
            self::TYPE_PICKING => 'Picking',
            self::TYPE_SHIPPING => 'Shipping',
            self::TYPE_QUARANTINE => 'Quarantine',
            self::TYPE_DAMAGE => 'Damage',
            self::TYPE_RETURN => 'Return',
            self::TYPE_STAGING => 'Staging',
        ];

        return $labels[$this->type] ?? 'Unknown';
    }

    public function getTypeLabelArAttribute()
    {
        $labels = [
            self::TYPE_RECEIVING => 'الاستلام',
            self::TYPE_STORAGE => 'التخزين',
            self::TYPE_PICKING => 'الانتقاء',
            self::TYPE_SHIPPING => 'الشحن',
            self::TYPE_QUARANTINE => 'الحجر الصحي',
            self::TYPE_DAMAGE => 'التالف',
            self::TYPE_RETURN => 'المرتجع',
            self::TYPE_STAGING => 'التجهيز',
        ];

        return $labels[$this->type] ?? 'غير معروف';
    }

    public function getFullLocationCodeAttribute()
    {
        return $this->warehouse->code . '-' . $this->code;
    }

    public function getUtilizationPercentageAttribute()
    {
        if ($this->capacity > 0) {
            return ($this->current_utilization / $this->capacity) * 100;
        }
        return 0;
    }

    public function getAvailableCapacityAttribute()
    {
        return $this->capacity - $this->current_utilization;
    }

    public function getIsFullAttribute()
    {
        return $this->current_utilization >= $this->capacity;
    }

    public function getIsNearFullAttribute($threshold = 90)
    {
        return $this->utilization_percentage >= $threshold;
    }

    /**
     * Methods
     */
    public function canAccommodate($quantity)
    {
        return $this->available_capacity >= $quantity;
    }

    public function addUtilization($quantity)
    {
        if (!$this->canAccommodate($quantity)) {
            throw new \Exception('Location capacity exceeded');
        }
        
        $this->increment('current_utilization', $quantity);
        
        return $this;
    }

    public function removeUtilization($quantity)
    {
        if ($this->current_utilization < $quantity) {
            throw new \Exception('Cannot remove more than current utilization');
        }
        
        $this->decrement('current_utilization', $quantity);
        
        return $this;
    }

    public function isWithinTemperatureRange($temperature)
    {
        if (!$this->temperature_controlled) {
            return true;
        }
        
        return $temperature >= $this->temperature_min && $temperature <= $this->temperature_max;
    }

    public function isWithinHumidityRange($humidity)
    {
        if (!$this->humidity_controlled) {
            return true;
        }
        
        return $humidity >= $this->humidity_min && $humidity <= $this->humidity_max;
    }

    public function isSuitableForProduct(Product $product)
    {
        // Check if location type is suitable
        if ($product->is_refrigerated && !$this->temperature_controlled) {
            return false;
        }
        
        // Check if location has capacity
        if ($this->is_full) {
            return false;
        }
        
        // Check if location is active and receivable
        if (!$this->is_active || !$this->is_receivable) {
            return false;
        }
        
        return true;
    }

    public function generateBarcode()
    {
        if (empty($this->barcode)) {
            $this->barcode = 'LOC-' . $this->warehouse_id . '-' . $this->id . '-' . time();
            $this->save();
        }
        
        return $this->barcode;
    }

    public static function findByBarcode($barcode)
    {
        return static::where('barcode', $barcode)
                    ->where('is_active', true)
                    ->first();
    }

    public static function suggestLocation(Product $product, $warehouseId, $quantity = 1)
    {
        $query = static::active()
                      ->receivable()
                      ->inWarehouse($warehouseId)
                      ->where('available_capacity', '>=', $quantity);
        
        // Prefer temperature-controlled locations for refrigerated products
        if ($product->is_refrigerated) {
            $query->temperatureControlled();
        }
        
        // Order by priority and available capacity
        return $query->orderBy('priority')
                    ->orderByDesc('available_capacity')
                    ->first();
    }

    public function getProductsInLocation()
    {
        return $this->products()
                   ->wherePivot('current_stock', '>', 0)
                   ->get();
    }

    public function getTotalStockValue()
    {
        return $this->products()
                   ->wherePivot('current_stock', '>', 0)
                   ->get()
                   ->sum(function ($product) {
                       return $product->pivot->current_stock * $product->cost_price;
                   });
    }
}
