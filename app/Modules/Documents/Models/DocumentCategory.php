<?php

namespace App\Modules\Documents\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class DocumentCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'name_ar',
        'description',
        'description_ar',
        'parent_id',
        'color',
        'icon',
        'sort_order',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Relationships
     */
    public function parent()
    {
        return $this->belongsTo(DocumentCategory::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(DocumentCategory::class, 'parent_id')->orderBy('sort_order');
    }

    public function documents()
    {
        return $this->hasMany(Document::class, 'category_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRootCategories($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Accessors
     */
    public function getFullNameAttribute()
    {
        $names = [];
        $category = $this;
        
        while ($category) {
            array_unshift($names, $category->name);
            $category = $category->parent;
        }
        
        return implode(' > ', $names);
    }

    public function getFullNameArAttribute()
    {
        $names = [];
        $category = $this;
        
        while ($category) {
            array_unshift($names, $category->name_ar ?? $category->name);
            $category = $category->parent;
        }
        
        return implode(' > ', $names);
    }

    public function getDocumentCountAttribute()
    {
        return $this->documents()->count();
    }

    /**
     * Methods
     */
    public function getAllChildren()
    {
        $children = collect();
        
        foreach ($this->children as $child) {
            $children->push($child);
            $children = $children->merge($child->getAllChildren());
        }
        
        return $children;
    }

    public function getAllDocuments()
    {
        $documents = $this->documents;
        
        foreach ($this->getAllChildren() as $child) {
            $documents = $documents->merge($child->documents);
        }
        
        return $documents;
    }

    public function canDelete()
    {
        return $this->documents()->count() === 0 && $this->children()->count() === 0;
    }
}
