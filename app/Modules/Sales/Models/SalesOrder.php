<?php

namespace App\Modules\Sales\Models;

use App\Modules\Inventory\Models\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class SalesOrder extends Model
{
    use HasFactory, SoftDeletes;

    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_PROCESSING = 'processing';
    const STATUS_SHIPPED = 'shipped';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_CANCELLED = 'cancelled';

    const PAYMENT_METHOD_CASH = 'cash';
    const PAYMENT_METHOD_CREDIT = 'credit';
    const PAYMENT_METHOD_BANK_TRANSFER = 'bank_transfer';
    const PAYMENT_METHOD_CHECK = 'check';

    protected $fillable = [
        'order_number',
        'customer_id',
        'order_date',
        'delivery_date',
        'status',
        'payment_method',
        'payment_terms',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'shipping_amount',
        'total_amount',
        'notes',
        'internal_notes',
        'shipping_address',
        'billing_address',
        'sales_rep_id',
        'warehouse_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'order_date' => 'date',
        'delivery_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'shipping_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'payment_terms' => 'integer',
    ];

    protected $attributes = [
        'status' => self::STATUS_DRAFT,
        'payment_method' => self::PAYMENT_METHOD_CASH,
        'payment_terms' => 30,
        'subtotal' => 0,
        'tax_amount' => 0,
        'discount_amount' => 0,
        'shipping_amount' => 0,
        'total_amount' => 0,
    ];

    /**
     * Get the customer that owns the sales order
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the sales order items
     */
    public function items()
    {
        return $this->hasMany(SalesOrderItem::class);
    }

    /**
     * Get the sales representative
     */
    public function salesRep()
    {
        return $this->belongsTo(\App\Models\User::class, 'sales_rep_id');
    }

    /**
     * Get the warehouse
     */
    public function warehouse()
    {
        return $this->belongsTo(\App\Modules\Inventory\Models\Warehouse::class);
    }

    /**
     * Get the invoice for this order
     */
    public function invoice()
    {
        return $this->hasOne(Invoice::class);
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
            self::STATUS_PROCESSING => 'Processing',
            self::STATUS_SHIPPED => 'Shipped',
            self::STATUS_DELIVERED => 'Delivered',
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
            self::STATUS_PROCESSING => 'قيد المعالجة',
            self::STATUS_SHIPPED => 'تم الشحن',
            self::STATUS_DELIVERED => 'تم التسليم',
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
        return !in_array($this->status, [self::STATUS_DELIVERED, self::STATUS_CANCELLED]);
    }

    /**
     * Check if order is invoiced
     */
    public function isInvoiced()
    {
        return $this->invoice()->exists();
    }

    /**
     * Calculate totals based on items
     */
    public function calculateTotals()
    {
        $this->subtotal = $this->items()->sum(DB::raw('quantity * unit_price'));
        $this->tax_amount = $this->subtotal * 0.1; // 10% tax
        $this->total_amount = $this->subtotal + $this->tax_amount - $this->discount_amount + $this->shipping_amount;
        $this->save();
    }

    /**
     * Confirm the order
     */
    public function confirm()
    {
        if ($this->status === self::STATUS_PENDING) {
            $this->status = self::STATUS_CONFIRMED;
            $this->save();
            
            // Check stock availability
            $this->checkStockAvailability();
        }
    }

    /**
     * Check stock availability for all items
     */
    public function checkStockAvailability()
    {
        foreach ($this->items as $item) {
            $availableStock = $item->product->getCurrentStockInWarehouse($this->warehouse_id);
            if ($availableStock < $item->quantity) {
                throw new \Exception("Insufficient stock for product: {$item->product->name}");
            }
        }
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
     * Scope for orders by customer
     */
    public function scopeForCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
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
                $order->order_number = "SO-{$year}{$month}-" . str_pad($count, 4, '0', STR_PAD_LEFT);
            }
            
            if (empty($order->order_date)) {
                $order->order_date = now();
            }
        });
    }
}
