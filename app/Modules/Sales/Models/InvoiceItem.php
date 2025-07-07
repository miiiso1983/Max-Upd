<?php

namespace App\Modules\Sales\Models;

use App\Modules\Inventory\Models\Product;
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
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'expiry_date' => 'date',
    ];

    protected $attributes = [
        'quantity' => 1,
        'discount_amount' => 0,
    ];

    /**
     * Get the invoice that owns the item
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
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
     * Boot method to auto-calculate totals
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($item) {
            $item->total_amount = $item->calculateTotal();
        });

        static::saved(function ($item) {
            // Recalculate invoice totals when item is saved
            $item->invoice->calculateTotals();
        });

        static::deleted(function ($item) {
            // Recalculate invoice totals when item is deleted
            $item->invoice->calculateTotals();
        });
    }
}
