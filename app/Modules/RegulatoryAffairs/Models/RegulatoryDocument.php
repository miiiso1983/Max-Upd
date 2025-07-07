<?php

namespace App\Modules\RegulatoryAffairs\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use Carbon\Carbon;

class RegulatoryDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_number',
        'title',
        'title_ar',
        'description',
        'description_ar',
        'entity_type',
        'entity_id',
        'file_name',
        'file_path',
        'file_size',
        'mime_type',
        'file_hash',
        'document_type',
        'document_category',
        'document_date',
        'expiry_date',
        'status',
        'version',
        'supersedes_id',
        'confidentiality_level',
        'access_permissions',
        'uploaded_by',
        'approved_by',
        'approved_at',
        'metadata',
        'notes',
        'is_required',
        'is_public',
    ];

    protected $casts = [
        'document_date' => 'date',
        'expiry_date' => 'date',
        'approved_at' => 'datetime',
        'access_permissions' => 'array',
        'metadata' => 'array',
        'is_required' => 'boolean',
        'is_public' => 'boolean',
    ];

    /**
     * Get the entity that owns the document (polymorphic)
     */
    public function entity(): MorphTo
    {
        return $this->morphTo('entity', 'entity_type', 'entity_id');
    }

    /**
     * Get the user who uploaded the document
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get the user who approved the document
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the document this one supersedes
     */
    public function supersedes(): BelongsTo
    {
        return $this->belongsTo(self::class, 'supersedes_id');
    }

    /**
     * Get documents that supersede this one
     */
    public function supersededBy()
    {
        return $this->hasMany(self::class, 'supersedes_id');
    }

    /**
     * Get the company this document belongs to
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(PharmaceuticalCompany::class, 'entity_id')
                    ->where('entity_type', 'company');
    }

    /**
     * Get the product this document belongs to
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(PharmaceuticalProduct::class, 'entity_id')
                    ->where('entity_type', 'product');
    }

    /**
     * Get the batch this document belongs to
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(PharmaceuticalBatch::class, 'entity_id')
                    ->where('entity_type', 'batch');
    }

    /**
     * Get the test this document belongs to
     */
    public function test(): BelongsTo
    {
        return $this->belongsTo(PharmaceuticalTest::class, 'entity_id')
                    ->where('entity_type', 'test');
    }

    /**
     * Get the inspection this document belongs to
     */
    public function inspection(): BelongsTo
    {
        return $this->belongsTo(PharmaceuticalInspection::class, 'entity_id')
                    ->where('entity_type', 'inspection');
    }

    /**
     * Scope for active documents
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for expired documents
     */
    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', now());
    }

    /**
     * Scope for expiring soon
     */
    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->where('expiry_date', '<=', now()->addDays($days))
                    ->where('expiry_date', '>=', now());
    }

    /**
     * Scope for specific entity
     */
    public function scopeForEntity($query, $entityType, $entityId)
    {
        return $query->where('entity_type', $entityType)
                    ->where('entity_id', $entityId);
    }

    /**
     * Scope for specific document type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('document_type', $type);
    }

    /**
     * Check if document is expired
     */
    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    /**
     * Check if document is expiring soon
     */
    public function isExpiringSoon($days = 30): bool
    {
        return $this->expiry_date && 
               $this->expiry_date->isFuture() && 
               $this->expiry_date->diffInDays(now()) <= $days;
    }

    /**
     * Get days until expiry
     */
    public function getDaysUntilExpiry(): int
    {
        if (!$this->expiry_date) {
            return 0;
        }
        
        return max(0, $this->expiry_date->diffInDays(now()));
    }

    /**
     * Get file URL
     */
    public function getFileUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }

    /**
     * Get formatted file size
     */
    public function getFormattedFileSizeAttribute(): string
    {
        $bytes = (int) $this->file_size;
        
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    /**
     * Get display title (Arabic if available)
     */
    public function getDisplayTitleAttribute(): string
    {
        return $this->title_ar ?: $this->title;
    }

    /**
     * Get display description (Arabic if available)
     */
    public function getDisplayDescriptionAttribute(): string
    {
        return $this->description_ar ?: $this->description;
    }

    /**
     * Get document type in Arabic
     */
    public function getDocumentTypeArabicAttribute(): string
    {
        $types = [
            'license' => 'ترخيص',
            'certificate' => 'شهادة',
            'report' => 'تقرير',
            'specification' => 'مواصفات',
            'sop' => 'إجراء تشغيل معياري',
            'protocol' => 'بروتوكول',
            'validation' => 'تحقق',
            'registration' => 'تسجيل',
            'inspection_report' => 'تقرير تفتيش',
            'test_report' => 'تقرير فحص',
            'other' => 'أخرى',
        ];

        return $types[$this->document_type] ?? $this->document_type;
    }

    /**
     * Get status in Arabic
     */
    public function getStatusArabicAttribute(): string
    {
        $statuses = [
            'active' => 'نشط',
            'expired' => 'منتهي',
            'superseded' => 'محدث',
            'archived' => 'مؤرشف',
        ];

        return $statuses[$this->status] ?? $this->status;
    }

    /**
     * Get confidentiality level in Arabic
     */
    public function getConfidentialityLevelArabicAttribute(): string
    {
        $levels = [
            'public' => 'عام',
            'internal' => 'داخلي',
            'confidential' => 'سري',
            'restricted' => 'مقيد',
        ];

        return $levels[$this->confidentiality_level] ?? $this->confidentiality_level;
    }

    /**
     * Generate unique document number
     */
    public static function generateDocumentNumber($entityType, $documentType): string
    {
        $prefix = strtoupper(substr($entityType, 0, 3)) . '-' . strtoupper(substr($documentType, 0, 3));
        $year = date('Y');
        $month = date('m');
        
        $lastDocument = self::where('document_number', 'like', "{$prefix}-{$year}{$month}-%")
                           ->orderBy('document_number', 'desc')
                           ->first();
        
        if ($lastDocument) {
            $lastNumber = (int) substr($lastDocument->document_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . '-' . $year . $month . '-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Delete file when document is deleted
     */
    protected static function boot()
    {
        parent::boot();
        
        static::deleting(function ($document) {
            if (Storage::exists($document->file_path)) {
                Storage::delete($document->file_path);
            }
        });
    }
}
