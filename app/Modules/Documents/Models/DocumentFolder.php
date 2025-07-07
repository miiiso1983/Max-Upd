<?php

namespace App\Modules\Documents\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class DocumentFolder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'name_ar',
        'description',
        'description_ar',
        'parent_id',
        'path',
        'color',
        'icon',
        'visibility',
        'is_shared',
        'sort_order',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_shared' => 'boolean',
        'sort_order' => 'integer',
    ];

    // Visibility constants
    const VISIBILITY_PRIVATE = 'private';
    const VISIBILITY_INTERNAL = 'internal';
    const VISIBILITY_PUBLIC = 'public';
    const VISIBILITY_RESTRICTED = 'restricted';

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($folder) {
            if (empty($folder->path)) {
                $folder->path = static::generatePath($folder);
            }
        });
    }

    /**
     * Generate folder path
     */
    public static function generatePath($folder)
    {
        $paths = [];
        
        if ($folder->parent_id) {
            $parent = static::find($folder->parent_id);
            if ($parent) {
                $paths[] = $parent->path;
            }
        }
        
        $paths[] = \Str::slug($folder->name);
        
        return implode('/', array_filter($paths));
    }

    /**
     * Relationships
     */
    public function parent()
    {
        return $this->belongsTo(DocumentFolder::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(DocumentFolder::class, 'parent_id')->orderBy('sort_order');
    }

    public function documents()
    {
        return $this->hasMany(Document::class, 'folder_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function permissions()
    {
        return $this->hasMany(DocumentFolderPermission::class);
    }

    /**
     * Scopes
     */
    public function scopeRootFolders($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function scopeByVisibility($query, $visibility)
    {
        return $query->where('visibility', $visibility);
    }

    public function scopeAccessibleBy($query, $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('visibility', self::VISIBILITY_PUBLIC)
              ->orWhere('created_by', $userId)
              ->orWhereHas('permissions', function ($pq) use ($userId) {
                  $pq->where('user_id', $userId);
              });
        });
    }

    /**
     * Accessors
     */
    public function getFullNameAttribute()
    {
        $names = [];
        $folder = $this;
        
        while ($folder) {
            array_unshift($names, $folder->name);
            $folder = $folder->parent;
        }
        
        return implode(' / ', $names);
    }

    public function getFullNameArAttribute()
    {
        $names = [];
        $folder = $this;
        
        while ($folder) {
            array_unshift($names, $folder->name_ar ?? $folder->name);
            $folder = $folder->parent;
        }
        
        return implode(' / ', $names);
    }

    public function getDocumentCountAttribute()
    {
        return $this->documents()->count();
    }

    public function getTotalDocumentCountAttribute()
    {
        $count = $this->documents()->count();
        
        foreach ($this->getAllChildren() as $child) {
            $count += $child->documents()->count();
        }
        
        return $count;
    }

    public function getVisibilityLabelAttribute()
    {
        $labels = [
            self::VISIBILITY_PRIVATE => 'Private',
            self::VISIBILITY_INTERNAL => 'Internal',
            self::VISIBILITY_PUBLIC => 'Public',
            self::VISIBILITY_RESTRICTED => 'Restricted',
        ];

        return $labels[$this->visibility] ?? 'Unknown';
    }

    public function getVisibilityLabelArAttribute()
    {
        $labels = [
            self::VISIBILITY_PRIVATE => 'خاص',
            self::VISIBILITY_INTERNAL => 'داخلي',
            self::VISIBILITY_PUBLIC => 'عام',
            self::VISIBILITY_RESTRICTED => 'مقيد',
        ];

        return $labels[$this->visibility] ?? 'غير معروف';
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

    public function grantPermission($userId, $permission)
    {
        return $this->permissions()->updateOrCreate(
            ['user_id' => $userId],
            ['permission' => $permission]
        );
    }

    public function revokePermission($userId)
    {
        return $this->permissions()->where('user_id', $userId)->delete();
    }

    public function hasPermission($userId, $permission)
    {
        if ($this->created_by === $userId) {
            return true; // Creator has all permissions
        }
        
        return $this->permissions()
                   ->where('user_id', $userId)
                   ->where('permission', $permission)
                   ->exists();
    }

    public function updatePath()
    {
        $this->path = static::generatePath($this);
        $this->save();
        
        // Update children paths
        foreach ($this->children as $child) {
            $child->updatePath();
        }
    }
}
