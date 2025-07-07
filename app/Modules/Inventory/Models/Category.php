<?php

namespace App\Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'name_ar',
        'description',
        'description_ar',
        'parent_id',
        'code',
        'image',
        'is_active',
        'sort_order',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected $attributes = [
        'is_active' => true,
        'sort_order' => 0,
    ];

    /**
     * Get the parent category
     */
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Get the child categories
     */
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_order');
    }

    /**
     * Get all descendants (recursive)
     */
    public function descendants()
    {
        return $this->children()->with('descendants');
    }

    /**
     * Get the products in this category
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get all products including from subcategories
     */
    public function allProducts()
    {
        $productIds = collect([$this->id]);
        $this->collectDescendantIds($productIds);
        
        return Product::whereIn('category_id', $productIds);
    }

    /**
     * Collect all descendant category IDs
     */
    private function collectDescendantIds($collection)
    {
        $children = $this->children;
        foreach ($children as $child) {
            $collection->push($child->id);
            $child->collectDescendantIds($collection);
        }
    }

    /**
     * Get the full category path
     */
    public function getFullPath($separator = ' > ')
    {
        $path = collect();
        $category = $this;
        
        while ($category) {
            $path->prepend($category->name);
            $category = $category->parent;
        }
        
        return $path->implode($separator);
    }

    /**
     * Get the full Arabic category path
     */
    public function getFullPathAr($separator = ' > ')
    {
        $path = collect();
        $category = $this;
        
        while ($category) {
            $path->prepend($category->name_ar ?: $category->name);
            $category = $category->parent;
        }
        
        return $path->implode($separator);
    }

    /**
     * Scope for active categories
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for root categories (no parent)
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope for ordered categories
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Get the user who created the category
     */
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Get the user who last updated the category
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

        static::creating(function ($category) {
            if (empty($category->code)) {
                $category->code = 'CAT-' . strtoupper(uniqid());
            }
        });
    }
}
