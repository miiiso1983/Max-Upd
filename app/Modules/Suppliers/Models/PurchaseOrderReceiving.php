<?php

namespace App\Modules\Suppliers\Models;

use App\Modules\Inventory\Models\Product;
use App\Modules\Inventory\Models\StockEntry;
use App\Modules\Inventory\Models\StockMovement;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderReceiving extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'product_id',
        'received_quantity',
        'unit_cost',
        'batch_number',
        'expiry_date',
        'manufacture_date',
        'received_date',
        'location',
        'quality_check_status',
        'quality_notes',
        'received_by',
        'notes',
    ];

    protected $casts = [
        'received_quantity' => 'integer',
        'unit_cost' => 'decimal:2',
        'expiry_date' => 'date',
        'manufacture_date' => 'date',
        'received_date' => 'datetime',
    ];

    protected $attributes = [
        'quality_check_status' => 'pending',
        'received_date' => null,
    ];

    /**
     * Get the purchase order
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
     * Get the stock entry created from this receiving
     */
    public function stockEntry()
    {
        return $this->hasOne(StockEntry::class, 'purchase_order_id', 'purchase_order_id')
                   ->where('product_id', $this->product_id)
                   ->where('batch_number', $this->batch_number);
    }

    /**
     * Get the user who received the items
     */
    public function receiver()
    {
        return $this->belongsTo(\App\Models\User::class, 'received_by');
    }

    /**
     * Get total value of received items
     */
    public function getTotalValue()
    {
        return $this->received_quantity * $this->unit_cost;
    }

    /**
     * Check if quality check is passed
     */
    public function isQualityCheckPassed()
    {
        return $this->quality_check_status === 'passed';
    }

    /**
     * Check if quality check is failed
     */
    public function isQualityCheckFailed()
    {
        return $this->quality_check_status === 'failed';
    }

    /**
     * Check if quality check is pending
     */
    public function isQualityCheckPending()
    {
        return $this->quality_check_status === 'pending';
    }

    /**
     * Pass quality check and add to inventory
     */
    public function passQualityCheck($notes = null)
    {
        $this->update([
            'quality_check_status' => 'passed',
            'quality_notes' => $notes,
        ]);

        // Add to inventory
        $this->addToInventory();
    }

    /**
     * Fail quality check
     */
    public function failQualityCheck($notes)
    {
        $this->update([
            'quality_check_status' => 'failed',
            'quality_notes' => $notes,
        ]);
    }

    /**
     * Add received items to inventory
     */
    public function addToInventory()
    {
        if (!$this->isQualityCheckPassed()) {
            throw new \Exception('Cannot add to inventory: Quality check not passed');
        }

        // Create stock entry
        $stockEntry = StockEntry::create([
            'product_id' => $this->product_id,
            'warehouse_id' => $this->purchaseOrder->warehouse_id,
            'batch_number' => $this->batch_number,
            'quantity' => $this->received_quantity,
            'purchase_price' => $this->unit_cost,
            'selling_price' => $this->product->selling_price,
            'expiry_date' => $this->expiry_date,
            'manufacture_date' => $this->manufacture_date,
            'supplier_id' => $this->purchaseOrder->supplier_id,
            'purchase_order_id' => $this->purchase_order_id,
            'location' => $this->location,
            'notes' => $this->notes,
            'created_by' => $this->received_by,
        ]);

        // Create stock movement
        StockMovement::create([
            'product_id' => $this->product_id,
            'warehouse_id' => $this->purchaseOrder->warehouse_id,
            'stock_entry_id' => $stockEntry->id,
            'type' => StockMovement::TYPE_IN,
            'reason' => StockMovement::REASON_PURCHASE,
            'quantity' => $this->received_quantity,
            'unit_cost' => $this->unit_cost,
            'reference_type' => PurchaseOrder::class,
            'reference_id' => $this->purchase_order_id,
            'batch_number' => $this->batch_number,
            'expiry_date' => $this->expiry_date,
            'notes' => "Received from PO {$this->purchaseOrder->order_number}",
            'created_by' => $this->received_by,
        ]);

        return $stockEntry;
    }

    /**
     * Scope for items with specific quality check status
     */
    public function scopeWithQualityStatus($query, $status)
    {
        return $query->where('quality_check_status', $status);
    }

    /**
     * Scope for items received on specific date
     */
    public function scopeReceivedOn($query, $date)
    {
        return $query->whereDate('received_date', $date);
    }

    /**
     * Scope for items received by specific user
     */
    public function scopeReceivedBy($query, $userId)
    {
        return $query->where('received_by', $userId);
    }

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($receiving) {
            if (empty($receiving->received_date)) {
                $receiving->received_date = now();
            }
        });

        static::saved(function ($receiving) {
            // Update purchase order status based on receiving
            $receiving->purchaseOrder->updateStatusBasedOnReceiving();
        });
    }
}
