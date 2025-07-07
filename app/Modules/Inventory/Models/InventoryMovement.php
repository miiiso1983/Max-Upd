<?php

namespace App\Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_number',
        'warehouse_id',
        'product_id',
        'type',
        'source_type',
        'source_id',
        'quantity',
        'unit_cost',
        'total_cost',
        'quantity_before',
        'quantity_after',
        'from_warehouse_id',
        'to_warehouse_id',
        'movement_date',
        'movement_time',
        'batch_number',
        'expiry_date',
        'serial_numbers',
        'reason',
        'notes',
        'attributes',
        'status',
        'approved_by',
        'approved_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'quantity_before' => 'decimal:3',
        'quantity_after' => 'decimal:3',
        'movement_date' => 'date',
        'movement_time' => 'datetime:H:i:s',
        'expiry_date' => 'date',
        'approved_at' => 'datetime',
        'attributes' => 'array',
    ];

    protected $attributes = [
        'status' => 'completed',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($movement) {
            if (!$movement->reference_number) {
                $movement->reference_number = static::generateReferenceNumber($movement->type);
            }
            
            if (!$movement->movement_date) {
                $movement->movement_date = now();
            }
            
            if (!$movement->movement_time) {
                $movement->movement_time = now();
            }
        });

        static::created(function ($movement) {
            // Update inventory after movement is created
            if ($movement->status === 'completed') {
                $movement->updateInventory();
            }
        });
    }

    /**
     * Get the warehouse that owns the movement
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the product that owns the movement
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the source warehouse (for transfers)
     */
    public function fromWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    /**
     * Get the destination warehouse (for transfers)
     */
    public function toWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    /**
     * Get the user who approved the movement
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by');
    }

    /**
     * Get the user who created the movement
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Get the user who last updated the movement
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    /**
     * Scope to filter by warehouse
     */
    public function scopeInWarehouse($query, $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    /**
     * Scope to filter by product
     */
    public function scopeForProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope to filter by type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to filter by source type
     */
    public function scopeFromSource($query, $sourceType)
    {
        return $query->where('source_type', $sourceType);
    }

    /**
     * Scope to filter by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('movement_date', [$startDate, $endDate]);
    }

    /**
     * Scope to filter by status
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Generate reference number
     */
    public static function generateReferenceNumber($type): string
    {
        $prefix = match($type) {
            'in' => 'IN',
            'out' => 'OUT',
            'transfer' => 'TRF',
            'adjustment' => 'ADJ',
            'return' => 'RET',
            'damage' => 'DMG',
            'loss' => 'LOSS',
            default => 'MOV',
        };

        $date = now()->format('Ymd');
        $sequence = static::where('type', $type)
                         ->whereDate('created_at', now())
                         ->count() + 1;

        return $prefix . '-' . $date . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Update inventory based on movement
     */
    public function updateInventory(): void
    {
        $inventory = Inventory::firstOrCreate(
            [
                'warehouse_id' => $this->warehouse_id,
                'product_id' => $this->product_id,
                'batch_number' => $this->batch_number,
            ],
            [
                'quantity' => 0,
                'reserved_quantity' => 0,
                'available_quantity' => 0,
                'cost_price' => $this->unit_cost,
                'average_cost' => $this->unit_cost,
                'last_cost' => $this->unit_cost,
                'expiry_date' => $this->expiry_date,
                'created_by' => $this->created_by,
            ]
        );

        $oldQuantity = $inventory->quantity;

        // Update quantity based on movement type
        switch ($this->type) {
            case 'in':
                $inventory->quantity += $this->quantity;
                break;
            case 'out':
                $inventory->quantity -= $this->quantity;
                break;
            case 'adjustment':
                $inventory->quantity = $this->quantity_after;
                break;
        }

        // Update cost information
        if ($this->type === 'in' && $this->unit_cost > 0) {
            // Calculate weighted average cost
            $totalValue = ($oldQuantity * $inventory->average_cost) + ($this->quantity * $this->unit_cost);
            $totalQuantity = $oldQuantity + $this->quantity;
            
            if ($totalQuantity > 0) {
                $inventory->average_cost = $totalValue / $totalQuantity;
            }
            
            $inventory->last_cost = $this->unit_cost;
        }

        $inventory->available_quantity = $inventory->quantity - $inventory->reserved_quantity;
        $inventory->last_movement_date = $this->movement_date;
        $inventory->updated_by = $this->created_by;

        $inventory->save();

        // Handle transfers
        if ($this->type === 'transfer' && $this->from_warehouse_id && $this->to_warehouse_id) {
            // Create outbound movement for source warehouse
            if ($this->warehouse_id === $this->to_warehouse_id) {
                static::create([
                    'reference_number' => $this->reference_number . '-OUT',
                    'warehouse_id' => $this->from_warehouse_id,
                    'product_id' => $this->product_id,
                    'type' => 'out',
                    'source_type' => 'transfer',
                    'source_id' => $this->id,
                    'quantity' => $this->quantity,
                    'unit_cost' => $this->unit_cost,
                    'total_cost' => $this->total_cost,
                    'movement_date' => $this->movement_date,
                    'movement_time' => $this->movement_time,
                    'batch_number' => $this->batch_number,
                    'expiry_date' => $this->expiry_date,
                    'reason' => 'Transfer to ' . $this->toWarehouse->name,
                    'notes' => $this->notes,
                    'status' => $this->status,
                    'created_by' => $this->created_by,
                ]);
            }
        }
    }

    /**
     * Approve the movement
     */
    public function approve($userId = null): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }

        $this->status = 'completed';
        $this->approved_by = $userId ?: auth()->id();
        $this->approved_at = now();

        if ($this->save()) {
            $this->updateInventory();
            return true;
        }

        return false;
    }

    /**
     * Cancel the movement
     */
    public function cancel(): bool
    {
        if ($this->status === 'completed') {
            return false;
        }

        $this->status = 'cancelled';
        return $this->save();
    }

    /**
     * Get movement type in Arabic
     */
    public function getTypeArabicAttribute(): string
    {
        return match($this->type) {
            'in' => 'وارد',
            'out' => 'صادر',
            'transfer' => 'نقل',
            'adjustment' => 'تسوية',
            'return' => 'مرتجع',
            'damage' => 'تالف',
            'loss' => 'فقدان',
            default => $this->type,
        };
    }

    /**
     * Get source type in Arabic
     */
    public function getSourceTypeArabicAttribute(): string
    {
        return match($this->source_type) {
            'purchase' => 'مشتريات',
            'sale' => 'مبيعات',
            'transfer' => 'نقل',
            'adjustment' => 'تسوية',
            'return' => 'مرتجع',
            'production' => 'إنتاج',
            'manual' => 'يدوي',
            default => $this->source_type,
        };
    }

    /**
     * Get status in Arabic
     */
    public function getStatusArabicAttribute(): string
    {
        return match($this->status) {
            'pending' => 'في الانتظار',
            'completed' => 'مكتمل',
            'cancelled' => 'ملغى',
            default => $this->status,
        };
    }
}
