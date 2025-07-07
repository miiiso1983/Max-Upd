<?php

namespace App\Modules\Suppliers\Models;

use App\Modules\Inventory\Models\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'product_id',
        'quantity',
        'unit_cost',
        'discount_percentage',
        'discount_amount',
        'total_amount',
        'notes',
        'supplier_sku',
        'expected_expiry_date',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_cost' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'expected_expiry_date' => 'date',
    ];

    protected $attributes = [
        'quantity' => 1,
        'discount_percentage' => 0,
        'discount_amount' => 0,
    ];

    /**
     * Get the purchase order that owns the item
     */
    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    /**
     * Get the product
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get received items for this purchase order item
     */
    public function receivedItems()
    {
        return $this->hasMany(PurchaseOrderReceiving::class, 'product_id', 'product_id')
                   ->where('purchase_order_id', $this->purchase_order_id);
    }

    /**
     * Calculate total amount
     */
    public function calculateTotal()
    {
        $subtotal = $this->quantity * $this->unit_cost;
        $this->total_amount = $subtotal - $this->discount_amount;
        return $this->total_amount;
    }

    /**
     * Get line total before discount
     */
    public function getSubtotal()
    {
        return $this->quantity * $this->unit_cost;
    }

    /**
     * Get discount amount based on percentage
     */
    public function getCalculatedDiscountAmount()
    {
        if ($this->discount_percentage > 0) {
            return ($this->quantity * $this->unit_cost) * ($this->discount_percentage / 100);
        }
        return $this->discount_amount;
    }

    /**
     * Get total received quantity for this item
     */
    public function getTotalReceivedQuantity()
    {
        return $this->receivedItems()->sum('received_quantity');
    }

    /**
     * Get remaining quantity to receive
     */
    public function getRemainingQuantity()
    {
        return $this->quantity - $this->getTotalReceivedQuantity();
    }

    /**
     * Check if item is fully received
     */
    public function isFullyReceived()
    {
        return $this->getTotalReceivedQuantity() >= $this->quantity;
    }

    /**
     * Get receiving progress percentage
     */
    public function getReceivingProgress()
    {
        if ($this->quantity === 0) {
            return 0;
        }
        
        return ($this->getTotalReceivedQuantity() / $this->quantity) * 100;
    }

    /**
     * Boot method to auto-calculate totals
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($item) {
            // Calculate discount amount from percentage if not set
            if ($item->discount_percentage > 0 && $item->discount_amount == 0) {
                $item->discount_amount = $item->getCalculatedDiscountAmount();
            }
            
            // Calculate total amount
            $item->total_amount = $item->calculateTotal();
        });

        static::saved(function ($item) {
            // Recalculate order totals when item is saved
            $item->purchaseOrder->calculateTotals();
        });

        static::deleted(function ($item) {
            // Recalculate order totals when item is deleted
            $item->purchaseOrder->calculateTotals();
        });
    }
}
