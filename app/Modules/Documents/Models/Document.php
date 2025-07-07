<?php

namespace App\Modules\Documents\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

class Document extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'document_number',
        'title',
        'title_ar',
        'description',
        'description_ar',
        'file_name',
        'original_name',
        'file_path',
        'file_size',
        'mime_type',
        'file_extension',
        'category_id',
        'folder_id',
        'related_type',
        'related_id',
        'status',
        'visibility',
        'is_template',
        'template_type',
        'version',
        'parent_document_id',
        'tags',
        'metadata',
        'checksum',
        'download_count',
        'last_accessed_at',
        'expires_at',
        'created_by',
        'updated_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'tags' => 'array',
        'metadata' => 'array',
        'file_size' => 'integer',
        'download_count' => 'integer',
        'is_template' => 'boolean',
        'last_accessed_at' => 'datetime',
        'expires_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    // Status constants
    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING_REVIEW = 'pending_review';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_ARCHIVED = 'archived';
    const STATUS_EXPIRED = 'expired';

    // Visibility constants
    const VISIBILITY_PRIVATE = 'private';
    const VISIBILITY_INTERNAL = 'internal';
    const VISIBILITY_PUBLIC = 'public';
    const VISIBILITY_RESTRICTED = 'restricted';

    // Template types
    const TEMPLATE_CONTRACT = 'contract';
    const TEMPLATE_INVOICE = 'invoice';
    const TEMPLATE_PROPOSAL = 'proposal';
    const TEMPLATE_REPORT = 'report';
    const TEMPLATE_LETTER = 'letter';
    const TEMPLATE_FORM = 'form';

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($document) {
            if (empty($document->document_number)) {
                $document->document_number = static::generateDocumentNumber();
            }
            
            if (empty($document->version)) {
                $document->version = '1.0';
            }
        });

        static::deleting(function ($document) {
            // Delete physical file when document is deleted
            if ($document->file_path && Storage::exists($document->file_path)) {
                Storage::delete($document->file_path);
            }
        });
    }

    /**
     * Generate unique document number
     */
    public static function generateDocumentNumber()
    {
        $year = now()->year;
        $month = now()->format('m');
        $prefix = "DOC-{$year}{$month}-";
        
        $lastDocument = static::where('document_number', 'like', $prefix . '%')
                              ->orderBy('document_number', 'desc')
                              ->first();
        
        if ($lastDocument) {
            $lastNumber = (int) substr($lastDocument->document_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Relationships
     */
    public function category()
    {
        return $this->belongsTo(DocumentCategory::class);
    }

    public function folder()
    {
        return $this->belongsTo(DocumentFolder::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function parentDocument()
    {
        return $this->belongsTo(Document::class, 'parent_document_id');
    }

    public function versions()
    {
        return $this->hasMany(Document::class, 'parent_document_id')->orderBy('version', 'desc');
    }

    public function related()
    {
        return $this->morphTo();
    }

    public function permissions()
    {
        return $this->hasMany(DocumentPermission::class);
    }

    public function signatures()
    {
        return $this->hasMany(DocumentSignature::class);
    }

    public function workflows()
    {
        return $this->hasMany(DocumentWorkflow::class);
    }

    public function activities()
    {
        return $this->hasMany(DocumentActivity::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->whereNotIn('status', [self::STATUS_ARCHIVED, self::STATUS_EXPIRED]);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByVisibility($query, $visibility)
    {
        return $query->where('visibility', $visibility);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeByFolder($query, $folderId)
    {
        return $query->where('folder_id', $folderId);
    }

    public function scopeTemplates($query)
    {
        return $query->where('is_template', true);
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now());
    }

    public function scopeExpiringSoon($query, $days = 7)
    {
        return $query->whereBetween('expires_at', [now(), now()->addDays($days)]);
    }

    public function scopeAccessibleBy($query, $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('visibility', self::VISIBILITY_PUBLIC)
              ->orWhere('created_by', $userId)
              ->orWhereHas('permissions', function ($pq) use ($userId) {
                  $pq->where('user_id', $userId)
                     ->where('permission', 'read');
              });
        });
    }

    /**
     * Accessors & Mutators
     */
    public function getStatusLabelAttribute()
    {
        $labels = [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_PENDING_REVIEW => 'Pending Review',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_ARCHIVED => 'Archived',
            self::STATUS_EXPIRED => 'Expired',
        ];

        return $labels[$this->status] ?? 'Unknown';
    }

    public function getStatusLabelArAttribute()
    {
        $labels = [
            self::STATUS_DRAFT => 'مسودة',
            self::STATUS_PENDING_REVIEW => 'في انتظار المراجعة',
            self::STATUS_APPROVED => 'معتمد',
            self::STATUS_REJECTED => 'مرفوض',
            self::STATUS_ARCHIVED => 'مؤرشف',
            self::STATUS_EXPIRED => 'منتهي الصلاحية',
        ];

        return $labels[$this->status] ?? 'غير معروف';
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

    public function getFileSizeHumanAttribute()
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getDownloadUrlAttribute()
    {
        return route('documents.download', $this->id);
    }

    public function getPreviewUrlAttribute()
    {
        return route('documents.preview', $this->id);
    }

    public function getIsExpiredAttribute()
    {
        return $this->expires_at && $this->expires_at < now();
    }

    public function getIsExpiringSoonAttribute()
    {
        return $this->expires_at && $this->expires_at <= now()->addDays(7);
    }

    /**
     * Methods
     */
    public function incrementDownloadCount()
    {
        $this->increment('download_count');
        $this->update(['last_accessed_at' => now()]);
        
        $this->logActivity('downloaded', 'Document downloaded');
        
        return $this;
    }

    public function createNewVersion($filePath, $userId = null)
    {
        $versionParts = explode('.', $this->version);
        $majorVersion = (int) $versionParts[0];
        $minorVersion = isset($versionParts[1]) ? (int) $versionParts[1] : 0;
        
        $newVersion = $majorVersion . '.' . ($minorVersion + 1);
        
        $newDocument = $this->replicate();
        $newDocument->parent_document_id = $this->parent_document_id ?? $this->id;
        $newDocument->version = $newVersion;
        $newDocument->file_path = $filePath;
        $newDocument->status = self::STATUS_DRAFT;
        $newDocument->created_by = $userId ?? auth()->id();
        $newDocument->save();
        
        $this->logActivity('version_created', "New version {$newVersion} created");
        
        return $newDocument;
    }

    public function approve($userId = null, $notes = null)
    {
        $this->update([
            'status' => self::STATUS_APPROVED,
            'approved_by' => $userId ?? auth()->id(),
            'approved_at' => now(),
        ]);
        
        $this->logActivity('approved', "Document approved. Notes: {$notes}");
        
        return $this;
    }

    public function reject($userId = null, $reason = null)
    {
        $this->update(['status' => self::STATUS_REJECTED]);
        
        $this->logActivity('rejected', "Document rejected. Reason: {$reason}");
        
        return $this;
    }

    public function archive($userId = null, $reason = null)
    {
        $this->update(['status' => self::STATUS_ARCHIVED]);
        
        $this->logActivity('archived', "Document archived. Reason: {$reason}");
        
        return $this;
    }

    public function logActivity($type, $description, $userId = null)
    {
        return $this->activities()->create([
            'type' => $type,
            'description' => $description,
            'description_ar' => $description, // Could be translated
            'activity_date' => now(),
            'created_by' => $userId ?? auth()->id() ?? $this->created_by ?? 1,
        ]);
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
}
