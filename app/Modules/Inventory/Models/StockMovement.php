<?php

namespace App\Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use HasFactory;

    const TYPE_IN = 'in';
    const TYPE_OUT = 'out';
    const TYPE_TRANSFER = 'transfer';
    const TYPE_ADJUSTMENT = 'adjustment';
    const TYPE_ADJUSTMENT_IN = 'adjustment_in';
    const TYPE_ADJUSTMENT_OUT = 'adjustment_out';
    const TYPE_RETURN = 'return';
    const TYPE_DAMAGE = 'damage';
    const TYPE_EXPIRY = 'expiry';
    const TYPE_RESERVED = 'reserved';
    const TYPE_UNRESERVED = 'unreserved';

    const REASON_PURCHASE = 'purchase';
    const REASON_SALE = 'sale';
    const REASON_TRANSFER = 'transfer';
    const REASON_ADJUSTMENT = 'adjustment';
    const REASON_RETURN_FROM_CUSTOMER = 'return_from_customer';
    const REASON_RETURN_TO_SUPPLIER = 'return_to_supplier';
    const REASON_DAMAGE = 'damage';
    const REASON_EXPIRY = 'expiry';
    const REASON_THEFT = 'theft';
    const REASON_LOSS = 'loss';

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'batch_id',
        'stock_entry_id',
        'movement_type',
        'type',
        'reason',
        'quantity',
        'unit_cost',
        'total_cost',
        'reference_type',
        'reference_id',
        'from_warehouse_id',
        'to_warehouse_id',
        'location_from',
        'location_to',
        'batch_number',
        'expiry_date',
        'barcode_scanned',
        'notes',
        'notes_ar',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'expiry_date' => 'date',
    ];

    /**
     * Get the product that owns the stock movement
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the warehouse
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the stock entry
     */
    public function stockEntry()
    {
        return $this->belongsTo(StockEntry::class);
    }

    /**
     * Get the source warehouse (for transfers)
     */
    public function fromWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    /**
     * Get the destination warehouse (for transfers)
     */
    public function toWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    /**
     * Get the reference model (polymorphic)
     */
    public function reference()
    {
        return $this->morphTo();
    }

    /**
     * Get available movement types
     */
    public static function getTypes()
    {
        return [
            self::TYPE_IN => 'Stock In',
            self::TYPE_OUT => 'Stock Out',
            self::TYPE_TRANSFER => 'Transfer',
            self::TYPE_ADJUSTMENT => 'Adjustment',
            self::TYPE_RETURN => 'Return',
            self::TYPE_DAMAGE => 'Damage',
            self::TYPE_EXPIRY => 'Expiry',
        ];
    }

    /**
     * Get available movement reasons
     */
    public static function getReasons()
    {
        return [
            self::REASON_PURCHASE => 'Purchase',
            self::REASON_SALE => 'Sale',
            self::REASON_TRANSFER => 'Transfer',
            self::REASON_ADJUSTMENT => 'Adjustment',
            self::REASON_RETURN_FROM_CUSTOMER => 'Return from Customer',
            self::REASON_RETURN_TO_SUPPLIER => 'Return to Supplier',
            self::REASON_DAMAGE => 'Damage',
            self::REASON_EXPIRY => 'Expiry',
            self::REASON_THEFT => 'Theft',
            self::REASON_LOSS => 'Loss',
        ];
    }

    /**
     * Check if movement is stock in
     */
    public function isStockIn()
    {
        return $this->type === self::TYPE_IN;
    }

    /**
     * Check if movement is stock out
     */
    public function isStockOut()
    {
        return $this->type === self::TYPE_OUT;
    }

    /**
     * Check if movement is transfer
     */
    public function isTransfer()
    {
        return $this->type === self::TYPE_TRANSFER;
    }

    /**
     * Get movement impact on stock (positive for in, negative for out)
     */
    public function getStockImpact()
    {
        switch ($this->type) {
            case self::TYPE_IN:
            case self::TYPE_RETURN:
                return $this->quantity;
            case self::TYPE_OUT:
            case self::TYPE_DAMAGE:
            case self::TYPE_EXPIRY:
                return -$this->quantity;
            case self::TYPE_TRANSFER:
                // For transfers, impact depends on perspective
                return 0;
            case self::TYPE_ADJUSTMENT:
                // Adjustments can be positive or negative
                return $this->quantity;
            default:
                return 0;
        }
    }

    /**
     * Scope for stock in movements
     */
    public function scopeStockIn($query)
    {
        return $query->where('type', self::TYPE_IN);
    }

    /**
     * Scope for stock out movements
     */
    public function scopeStockOut($query)
    {
        return $query->where('type', self::TYPE_OUT);
    }

    /**
     * Scope for transfers
     */
    public function scopeTransfers($query)
    {
        return $query->where('type', self::TYPE_TRANSFER);
    }

    /**
     * Scope for specific warehouse
     */
    public function scopeForWarehouse($query, $warehouseId)
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
     * Scope for date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Get the user who created the movement
     */
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Boot method to auto-calculate total cost
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($movement) {
            if ($movement->unit_cost && $movement->quantity) {
                $movement->total_cost = $movement->unit_cost * abs($movement->quantity);
            }
        });
    }
}
