<?php

namespace App\Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductBrand extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_ar',
        'code',
        'description',
        'description_ar',
        'logo',
        'website',
        'email',
        'phone',
        'address',
        'country',
        'status',
        'sort_order',
        'attributes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'status' => 'string',
        'sort_order' => 'integer',
        'attributes' => 'array',
    ];

    protected $attributes = [
        'status' => 'active',
        'sort_order' => 0,
    ];

    /**
     * Get the products for this brand
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'brand_id');
    }

    /**
     * Get the user who created the brand
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Get the user who last updated the brand
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    /**
     * Scope to filter active brands
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to order by sort order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Get the display name (Arabic if available, otherwise English)
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->name_ar ?: $this->name;
    }

    /**
     * Get the display description (Arabic if available, otherwise English)
     */
    public function getDisplayDescriptionAttribute(): string
    {
        return $this->description_ar ?: $this->description;
    }

    /**
     * Get the products count for this brand
     */
    public function getProductsCountAttribute(): int
    {
        return $this->products()->count();
    }

    /**
     * Get the active products count for this brand
     */
    public function getActiveProductsCountAttribute(): int
    {
        return $this->products()->where('status', 'active')->count();
    }

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($brand) {
            // Prevent deletion if brand has products
            if ($brand->products()->exists()) {
                throw new \Exception('Cannot delete brand with products. Please move or delete products first.');
            }
        });
    }

    /**
     * Get brands with product counts
     */
    public static function withProductCounts()
    {
        return static::withCount(['products', 'products as active_products_count' => function ($query) {
            $query->where('status', 'active');
        }]);
    }

    /**
     * Search brands by name or code
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('name_ar', 'like', "%{$search}%")
              ->orWhere('code', 'like', "%{$search}%");
        });
    }
}
