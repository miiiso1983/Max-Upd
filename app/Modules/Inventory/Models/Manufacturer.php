<?php

namespace App\Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Manufacturer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'name_ar',
        'code',
        'description',
        'description_ar',
        'country',
        'city',
        'address',
        'phone',
        'email',
        'website',
        'contact_person',
        'contact_phone',
        'contact_email',
        'is_active',
        'logo',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $attributes = [
        'is_active' => true,
    ];

    /**
     * Get the products for this manufacturer
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get active products count
     */
    public function getActiveProductsCount()
    {
        return $this->products()->active()->count();
    }

    /**
     * Get total products count
     */
    public function getTotalProductsCount()
    {
        return $this->products()->count();
    }

    /**
     * Get total stock value for this manufacturer's products
     */
    public function getTotalStockValue()
    {
        return $this->products()
                   ->join('stock_entries', 'products.id', '=', 'stock_entries.product_id')
                   ->where('stock_entries.expiry_date', '>', now())
                   ->selectRaw('SUM(stock_entries.quantity * stock_entries.purchase_price) as total_value')
                   ->value('total_value') ?? 0;
    }

    /**
     * Scope for active manufacturers
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for manufacturers with products
     */
    public function scopeWithProducts($query)
    {
        return $query->has('products');
    }

    /**
     * Get the user who created the manufacturer
     */
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Get the user who last updated the manufacturer
     */
    public function updater()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    /**
     * Generate code if not provided
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($manufacturer) {
            if (empty($manufacturer->code)) {
                $manufacturer->code = 'MFG-' . strtoupper(uniqid());
            }
        });
    }
}
