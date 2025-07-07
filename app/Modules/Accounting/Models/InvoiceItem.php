<?php

namespace App\Modules\Accounting\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'product_id',
        'description',
        'description_ar',
        'quantity',
        'unit_price',
        'discount_amount',
        'tax_rate',
        'tax_amount',
        'total_amount',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    protected $attributes = [
        'quantity' => 1,
        'discount_amount' => 0,
        'tax_rate' => 5.00, // 5% default tax rate for Iraq
        'tax_amount' => 0,
    ];

    /**
     * Get the invoice this item belongs to
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get the product (if linked to inventory)
     */
    public function product()
    {
        return $this->belongsTo(\App\Modules\Inventory\Models\Product::class);
    }

    /**
     * Calculate item totals
     */
    public function calculateTotals()
    {
        $subtotal = $this->quantity * $this->unit_price;
        $discountedAmount = $subtotal - $this->discount_amount;
        $this->tax_amount = $discountedAmount * ($this->tax_rate / 100);
        $this->total_amount = $discountedAmount + $this->tax_amount;
        
        $this->save();
    }

    /**
     * Get subtotal (quantity * unit_price)
     */
    public function getSubtotalAttribute()
    {
        return $this->quantity * $this->unit_price;
    }

    /**
     * Get discounted amount
     */
    public function getDiscountedAmountAttribute()
    {
        return $this->subtotal - $this->discount_amount;
    }

    /**
     * Get discount percentage
     */
    public function getDiscountPercentageAttribute()
    {
        if ($this->subtotal == 0) {
            return 0;
        }
        
        return ($this->discount_amount / $this->subtotal) * 100;
    }

    /**
     * Get the user who created the item
     */
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Get the user who last updated the item
     */
    public function updater()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function ($item) {
            $item->calculateTotals();
            
            // Update invoice totals
            if ($item->invoice) {
                $item->invoice->calculateTotals();
            }
        });

        static::deleted(function ($item) {
            // Update invoice totals
            if ($item->invoice) {
                $item->invoice->calculateTotals();
            }
        });
    }
}
