<?php

namespace App\Modules\Sales\Models;

use App\Modules\Inventory\Models\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sales_order_id',
        'product_id',
        'quantity',
        'unit_price',
        'discount_percentage',
        'discount_amount',
        'total_amount',
        'notes',
        'batch_number',
        'expiry_date',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'expiry_date' => 'date',
    ];

    protected $attributes = [
        'quantity' => 1,
        'discount_percentage' => 0,
        'discount_amount' => 0,
    ];

    /**
     * Get the sales order that owns the item
     */
    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }

    /**
     * Get the product
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Calculate total amount
     */
    public function calculateTotal()
    {
        $subtotal = $this->quantity * $this->unit_price;
        $this->total_amount = $subtotal - $this->discount_amount;
        return $this->total_amount;
    }

    /**
     * Get line total before discount
     */
    public function getSubtotal()
    {
        return $this->quantity * $this->unit_price;
    }

    /**
     * Get discount amount based on percentage
     */
    public function getCalculatedDiscountAmount()
    {
        if ($this->discount_percentage > 0) {
            return ($this->quantity * $this->unit_price) * ($this->discount_percentage / 100);
        }
        return $this->discount_amount;
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
            $item->salesOrder->calculateTotals();
        });

        static::deleted(function ($item) {
            // Recalculate order totals when item is deleted
            $item->salesOrder->calculateTotals();
        });
    }
}
