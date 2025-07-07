<?php

namespace App\Modules\Suppliers\Models;

use App\Modules\Inventory\Models\Product;
use App\Modules\Inventory\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class PurchaseOrder extends Model
{
    use HasFactory, SoftDeletes;

    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_PARTIAL = 'partial';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    const PAYMENT_METHOD_CASH = 'cash';
    const PAYMENT_METHOD_CREDIT = 'credit';
    const PAYMENT_METHOD_BANK_TRANSFER = 'bank_transfer';
    const PAYMENT_METHOD_CHECK = 'check';

    protected $fillable = [
        'order_number',
        'supplier_id',
        'warehouse_id',
        'order_date',
        'expected_delivery_date',
        'delivered_date',
        'status',
        'payment_method',
        'payment_terms',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'shipping_amount',
        'total_amount',
        'notes',
        'terms_conditions',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_delivery_date' => 'date',
        'delivered_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'shipping_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'payment_terms' => 'integer',
    ];

    protected $attributes = [
        'status' => self::STATUS_DRAFT,
        'payment_method' => self::PAYMENT_METHOD_CREDIT,
        'payment_terms' => 30,
        'subtotal' => 0,
        'tax_amount' => 0,
        'discount_amount' => 0,
        'shipping_amount' => 0,
        'total_amount' => 0,
    ];

    /**
     * Get the supplier that owns the purchase order
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the warehouse for this purchase order
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the purchase order items
     */
    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    /**
     * Get the received items
     */
    public function receivedItems()
    {
        return $this->hasMany(PurchaseOrderReceiving::class);
    }

    /**
     * Get available statuses
     */
    public static function getStatuses()
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_PENDING => 'Pending',
            self::STATUS_CONFIRMED => 'Confirmed',
            self::STATUS_PARTIAL => 'Partially Received',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_CANCELLED => 'Cancelled',
        ];
    }

    /**
     * Get available statuses in Arabic
     */
    public static function getStatusesAr()
    {
        return [
            self::STATUS_DRAFT => 'مسودة',
            self::STATUS_PENDING => 'معلق',
            self::STATUS_CONFIRMED => 'مؤكد',
            self::STATUS_PARTIAL => 'مستلم جزئياً',
            self::STATUS_COMPLETED => 'مكتمل',
            self::STATUS_CANCELLED => 'ملغي',
        ];
    }

    /**
     * Get available payment methods
     */
    public static function getPaymentMethods()
    {
        return [
            self::PAYMENT_METHOD_CASH => 'Cash',
            self::PAYMENT_METHOD_CREDIT => 'Credit',
            self::PAYMENT_METHOD_BANK_TRANSFER => 'Bank Transfer',
            self::PAYMENT_METHOD_CHECK => 'Check',
        ];
    }

    /**
     * Check if order can be edited
     */
    public function canBeEdited()
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_PENDING]);
    }

    /**
     * Check if order can be cancelled
     */
    public function canBeCancelled()
    {
        return !in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_CANCELLED]);
    }

    /**
     * Check if order can be received
     */
    public function canBeReceived()
    {
        return in_array($this->status, [self::STATUS_CONFIRMED, self::STATUS_PARTIAL]);
    }

    /**
     * Calculate totals based on items
     */
    public function calculateTotals()
    {
        $this->subtotal = $this->items()->sum(DB::raw('quantity * unit_cost'));
        $this->tax_amount = $this->subtotal * 0.1; // 10% tax
        $this->total_amount = $this->subtotal + $this->tax_amount - $this->discount_amount + $this->shipping_amount;
        $this->save();
    }

    /**
     * Confirm the purchase order
     */
    public function confirm()
    {
        if ($this->status === self::STATUS_PENDING) {
            $this->status = self::STATUS_CONFIRMED;
            $this->save();
        }
    }

    /**
     * Get total received quantity for all items
     */
    public function getTotalReceivedQuantity()
    {
        return $this->receivedItems()->sum('received_quantity');
    }

    /**
     * Get total ordered quantity for all items
     */
    public function getTotalOrderedQuantity()
    {
        return $this->items()->sum('quantity');
    }

    /**
     * Check if order is fully received
     */
    public function isFullyReceived()
    {
        foreach ($this->items as $item) {
            $receivedQuantity = $this->receivedItems()
                                   ->where('product_id', $item->product_id)
                                   ->sum('received_quantity');
            
            if ($receivedQuantity < $item->quantity) {
                return false;
            }
        }
        return true;
    }

    /**
     * Update status based on received items
     */
    public function updateStatusBasedOnReceiving()
    {
        if ($this->isFullyReceived()) {
            $this->update([
                'status' => self::STATUS_COMPLETED,
                'delivered_date' => now(),
            ]);
        } elseif ($this->getTotalReceivedQuantity() > 0) {
            $this->update(['status' => self::STATUS_PARTIAL]);
        }
    }

    /**
     * Get receiving progress percentage
     */
    public function getReceivingProgress()
    {
        $totalOrdered = $this->getTotalOrderedQuantity();
        if ($totalOrdered === 0) {
            return 0;
        }
        
        $totalReceived = $this->getTotalReceivedQuantity();
        return ($totalReceived / $totalOrdered) * 100;
    }

    /**
     * Scope for orders by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for orders by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('order_date', [$startDate, $endDate]);
    }

    /**
     * Scope for orders by supplier
     */
    public function scopeForSupplier($query, $supplierId)
    {
        return $query->where('supplier_id', $supplierId);
    }

    /**
     * Scope for overdue orders
     */
    public function scopeOverdue($query)
    {
        return $query->whereIn('status', [self::STATUS_CONFIRMED, self::STATUS_PARTIAL])
                    ->where('expected_delivery_date', '<', now());
    }

    /**
     * Get the user who created the order
     */
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Get the user who last updated the order
     */
    public function updater()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    /**
     * Generate order number if not provided
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $year = date('Y');
                $month = date('m');
                $count = static::whereYear('created_at', $year)
                              ->whereMonth('created_at', $month)
                              ->count() + 1;
                $order->order_number = "PO-{$year}{$month}-" . str_pad($count, 4, '0', STR_PAD_LEFT);
            }
            
            if (empty($order->order_date)) {
                $order->order_date = now();
            }
        });
    }
}
