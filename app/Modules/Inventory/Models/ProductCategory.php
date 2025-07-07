<?php

namespace App\Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_ar',
        'code',
        'description',
        'description_ar',
        'parent_id',
        'image',
        'icon',
        'color',
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
     * Get the parent category
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'parent_id');
    }

    /**
     * Get the child categories
     */
    public function children(): HasMany
    {
        return $this->hasMany(ProductCategory::class, 'parent_id')->orderBy('sort_order');
    }

    /**
     * Get all descendant categories
     */
    public function descendants(): HasMany
    {
        return $this->children()->with('descendants');
    }

    /**
     * Get the products in this category
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    /**
     * Get the user who created the category
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Get the user who last updated the category
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    /**
     * Scope to filter active categories
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to filter root categories (no parent)
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
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
     * Get the full path of the category
     */
    public function getFullPathAttribute(): string
    {
        $path = [$this->display_name];
        $parent = $this->parent;

        while ($parent) {
            array_unshift($path, $parent->display_name);
            $parent = $parent->parent;
        }

        return implode(' > ', $path);
    }

    /**
     * Get the level of the category (0 for root, 1 for first level, etc.)
     */
    public function getLevelAttribute(): int
    {
        $level = 0;
        $parent = $this->parent;

        while ($parent) {
            $level++;
            $parent = $parent->parent;
        }

        return $level;
    }

    /**
     * Check if this category has children
     */
    public function hasChildren(): bool
    {
        return $this->children()->exists();
    }

    /**
     * Check if this category is a root category
     */
    public function isRoot(): bool
    {
        return is_null($this->parent_id);
    }

    /**
     * Check if this category is a descendant of another category
     */
    public function isDescendantOf(ProductCategory $category): bool
    {
        $parent = $this->parent;

        while ($parent) {
            if ($parent->id === $category->id) {
                return true;
            }
            $parent = $parent->parent;
        }

        return false;
    }

    /**
     * Get all products in this category and its descendants
     */
    public function getAllProducts()
    {
        $categoryIds = $this->getAllDescendantIds();
        $categoryIds[] = $this->id;

        return Product::whereIn('category_id', $categoryIds);
    }

    /**
     * Get all descendant category IDs
     */
    public function getAllDescendantIds(): array
    {
        $ids = [];
        
        foreach ($this->children as $child) {
            $ids[] = $child->id;
            $ids = array_merge($ids, $child->getAllDescendantIds());
        }

        return $ids;
    }

    /**
     * Get the products count in this category
     */
    public function getProductsCountAttribute(): int
    {
        return $this->products()->count();
    }

    /**
     * Get the total products count including descendants
     */
    public function getTotalProductsCountAttribute(): int
    {
        return $this->getAllProducts()->count();
    }

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($category) {
            // Prevent deletion if category has products
            if ($category->products()->exists()) {
                throw new \Exception('Cannot delete category with products. Please move or delete products first.');
            }

            // Move children to parent or root
            $category->children()->update(['parent_id' => $category->parent_id]);
        });
    }

    /**
     * Get categories as a tree structure
     */
    public static function getTree($parentId = null): array
    {
        $categories = static::where('parent_id', $parentId)
                           ->active()
                           ->ordered()
                           ->get();

        $tree = [];

        foreach ($categories as $category) {
            $node = $category->toArray();
            $node['children'] = static::getTree($category->id);
            $tree[] = $node;
        }

        return $tree;
    }

    /**
     * Get categories as a flat list with indentation
     */
    public static function getFlatList($parentId = null, $level = 0): array
    {
        $categories = static::where('parent_id', $parentId)
                           ->active()
                           ->ordered()
                           ->get();

        $list = [];

        foreach ($categories as $category) {
            $item = $category->toArray();
            $item['level'] = $level;
            $item['indented_name'] = str_repeat('— ', $level) . $category->display_name;
            $list[] = $item;

            $children = static::getFlatList($category->id, $level + 1);
            $list = array_merge($list, $children);
        }

        return $list;
    }
}
