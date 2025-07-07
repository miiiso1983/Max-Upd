<?php

namespace App\Modules\Suppliers\Models;

use App\Modules\Inventory\Models\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'product_id',
        'supplier_sku',
        'unit_cost',
        'minimum_order_quantity',
        'lead_time_days',
        'is_preferred',
        'last_order_date',
        'last_unit_cost',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'unit_cost' => 'decimal:2',
        'last_unit_cost' => 'decimal:2',
        'minimum_order_quantity' => 'integer',
        'lead_time_days' => 'integer',
        'is_preferred' => 'boolean',
        'last_order_date' => 'date',
    ];

    protected $attributes = [
        'minimum_order_quantity' => 1,
        'lead_time_days' => 7,
        'is_preferred' => false,
    ];

    /**
     * Get the supplier
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the product
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get cost difference from last order
     */
    public function getCostDifference()
    {
        if (!$this->last_unit_cost) {
            return 0;
        }
        
        return $this->unit_cost - $this->last_unit_cost;
    }

    /**
     * Get cost change percentage
     */
    public function getCostChangePercentage()
    {
        if (!$this->last_unit_cost || $this->last_unit_cost == 0) {
            return 0;
        }
        
        return (($this->unit_cost - $this->last_unit_cost) / $this->last_unit_cost) * 100;
    }

    /**
     * Check if cost has increased
     */
    public function hasCostIncreased()
    {
        return $this->getCostDifference() > 0;
    }

    /**
     * Check if cost has decreased
     */
    public function hasCostDecreased()
    {
        return $this->getCostDifference() < 0;
    }

    /**
     * Update last order information
     */
    public function updateLastOrder($unitCost, $orderDate = null)
    {
        $this->update([
            'last_unit_cost' => $this->unit_cost,
            'unit_cost' => $unitCost,
            'last_order_date' => $orderDate ?? now(),
        ]);
    }

    /**
     * Scope for preferred suppliers
     */
    public function scopePreferred($query)
    {
        return $query->where('is_preferred', true);
    }

    /**
     * Scope for suppliers with minimum order quantity
     */
    public function scopeWithMinimumQuantity($query, $quantity)
    {
        return $query->where('minimum_order_quantity', '<=', $quantity);
    }

    /**
     * Scope for suppliers with lead time within days
     */
    public function scopeWithLeadTime($query, $maxDays)
    {
        return $query->where('lead_time_days', '<=', $maxDays);
    }

    /**
     * Get the user who created the relationship
     */
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Get the user who last updated the relationship
     */
    public function updater()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }
}
