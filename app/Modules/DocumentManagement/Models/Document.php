<?php

namespace App\Modules\DocumentManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class Document extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'title_ar',
        'description',
        'description_ar',
        'document_number',
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
        'file_size' => 'integer',
        'is_template' => 'boolean',
        'tags' => 'array',
        'metadata' => 'array',
        'download_count' => 'integer',
        'last_accessed_at' => 'datetime',
        'expires_at' => 'datetime',
        'approved_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Document type constants
    const TYPE_SOP = 'sop'; // Standard Operating Procedure
    const TYPE_POLICY = 'policy';
    const TYPE_PROCEDURE = 'procedure';
    const TYPE_FORM = 'form';
    const TYPE_TEMPLATE = 'template';
    const TYPE_REPORT = 'report';
    const TYPE_CERTIFICATE = 'certificate';
    const TYPE_LICENSE = 'license';
    const TYPE_SPECIFICATION = 'specification';
    const TYPE_VALIDATION = 'validation';
    const TYPE_TRAINING = 'training';
    const TYPE_AUDIT = 'audit';
    const TYPE_COMPLAINT = 'complaint';
    const TYPE_DEVIATION = 'deviation';
    const TYPE_CAPA = 'capa'; // Corrective and Preventive Action
    const TYPE_CHANGE_CONTROL = 'change_control';
    const TYPE_BATCH_RECORD = 'batch_record';
    const TYPE_QUALITY_MANUAL = 'quality_manual';
    const TYPE_CONTRACT = 'contract';
    const TYPE_CORRESPONDENCE = 'correspondence';

    // Status constants
    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING_REVIEW = 'pending_review';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_ARCHIVED = 'archived';
    const STATUS_EXPIRED = 'expired';

    // Visibility constants (replaces classification)
    const VISIBILITY_PRIVATE = 'private';
    const VISIBILITY_INTERNAL = 'internal';
    const VISIBILITY_PUBLIC = 'public';
    const VISIBILITY_RESTRICTED = 'restricted';

    // Confidentiality level constants
    const CONFIDENTIALITY_LOW = 'low';
    const CONFIDENTIALITY_MEDIUM = 'medium';
    const CONFIDENTIALITY_HIGH = 'high';
    const CONFIDENTIALITY_CRITICAL = 'critical';

    // Approval status constants
    const APPROVAL_NOT_REQUIRED = 'not_required';
    const APPROVAL_PENDING = 'pending';
    const APPROVAL_APPROVED = 'approved';
    const APPROVAL_REJECTED = 'rejected';

    /**
     * Relationships
     */
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

    public function locker()
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    public function parentDocument()
    {
        return $this->belongsTo(Document::class, 'parent_document_id');
    }

    public function versions()
    {
        return $this->hasMany(Document::class, 'parent_document_id')->orderBy('version', 'desc');
    }

    public function currentVersion()
    {
        return $this->hasOne(Document::class, 'parent_document_id')->where('is_current_version', true);
    }

    public function signatures()
    {
        return $this->hasMany(DocumentSignature::class);
    }

    public function approvals()
    {
        return $this->hasMany(DocumentApproval::class);
    }

    public function accessLogs()
    {
        return $this->hasMany(DocumentAccessLog::class);
    }

    public function shares()
    {
        return $this->hasMany(DocumentShare::class);
    }

    public function comments()
    {
        return $this->hasMany(DocumentComment::class);
    }

    public function attachments()
    {
        return $this->hasMany(DocumentAttachment::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', [self::STATUS_APPROVED]);
    }

    public function scopeCurrentVersions($query)
    {
        return $query->where('is_current_version', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('document_type', $type);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->whereNotNull('expiry_date')
                    ->where('expiry_date', '<=', now()->addDays($days))
                    ->where('expiry_date', '>', now());
    }

    public function scopeExpired($query)
    {
        return $query->whereNotNull('expiry_date')
                    ->where('expiry_date', '<', now());
    }

    public function scopeForReview($query, $days = 30)
    {
        return $query->whereNotNull('review_date')
                    ->where('review_date', '<=', now()->addDays($days))
                    ->where('review_date', '>', now());
    }

    public function scopeAccessibleBy($query, $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('created_by', $userId)
              ->orWhere('visibility', self::VISIBILITY_PUBLIC);
        });
    }

    /**
     * Accessors
     */
    public function getTypeLabelAttribute()
    {
        $labels = [
            self::TYPE_SOP => 'Standard Operating Procedure',
            self::TYPE_POLICY => 'Policy',
            self::TYPE_PROCEDURE => 'Procedure',
            self::TYPE_FORM => 'Form',
            self::TYPE_TEMPLATE => 'Template',
            self::TYPE_REPORT => 'Report',
            self::TYPE_CERTIFICATE => 'Certificate',
            self::TYPE_LICENSE => 'License',
            self::TYPE_SPECIFICATION => 'Specification',
            self::TYPE_VALIDATION => 'Validation',
            self::TYPE_TRAINING => 'Training Material',
            self::TYPE_AUDIT => 'Audit Document',
            self::TYPE_COMPLAINT => 'Complaint',
            self::TYPE_DEVIATION => 'Deviation',
            self::TYPE_CAPA => 'CAPA',
            self::TYPE_CHANGE_CONTROL => 'Change Control',
            self::TYPE_BATCH_RECORD => 'Batch Record',
            self::TYPE_QUALITY_MANUAL => 'Quality Manual',
            self::TYPE_CONTRACT => 'Contract',
            self::TYPE_CORRESPONDENCE => 'Correspondence',
        ];

        return $labels[$this->document_type] ?? 'Unknown';
    }

    public function getTypeLabelArAttribute()
    {
        $labels = [
            self::TYPE_SOP => 'إجراء تشغيل معياري',
            self::TYPE_POLICY => 'سياسة',
            self::TYPE_PROCEDURE => 'إجراء',
            self::TYPE_FORM => 'نموذج',
            self::TYPE_TEMPLATE => 'قالب',
            self::TYPE_REPORT => 'تقرير',
            self::TYPE_CERTIFICATE => 'شهادة',
            self::TYPE_LICENSE => 'رخصة',
            self::TYPE_SPECIFICATION => 'مواصفات',
            self::TYPE_VALIDATION => 'تحقق',
            self::TYPE_TRAINING => 'مواد تدريبية',
            self::TYPE_AUDIT => 'وثيقة تدقيق',
            self::TYPE_COMPLAINT => 'شكوى',
            self::TYPE_DEVIATION => 'انحراف',
            self::TYPE_CAPA => 'إجراء تصحيحي ووقائي',
            self::TYPE_CHANGE_CONTROL => 'مراقبة التغيير',
            self::TYPE_BATCH_RECORD => 'سجل الدفعة',
            self::TYPE_QUALITY_MANUAL => 'دليل الجودة',
            self::TYPE_CONTRACT => 'عقد',
            self::TYPE_CORRESPONDENCE => 'مراسلات',
        ];

        return $labels[$this->document_type] ?? 'غير معروف';
    }

    public function getStatusLabelAttribute()
    {
        $labels = [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_UNDER_REVIEW => 'Under Review',
            self::STATUS_PENDING_APPROVAL => 'Pending Approval',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_SUPERSEDED => 'Superseded',
            self::STATUS_OBSOLETE => 'Obsolete',
            self::STATUS_ARCHIVED => 'Archived',
            self::STATUS_REJECTED => 'Rejected',
        ];

        return $labels[$this->status] ?? 'Unknown';
    }

    public function getStatusLabelArAttribute()
    {
        $labels = [
            self::STATUS_DRAFT => 'مسودة',
            self::STATUS_UNDER_REVIEW => 'قيد المراجعة',
            self::STATUS_PENDING_APPROVAL => 'في انتظار الموافقة',
            self::STATUS_APPROVED => 'معتمد',
            self::STATUS_ACTIVE => 'نشط',
            self::STATUS_SUPERSEDED => 'محل',
            self::STATUS_OBSOLETE => 'منتهي الصلاحية',
            self::STATUS_ARCHIVED => 'مؤرشف',
            self::STATUS_REJECTED => 'مرفوض',
        ];

        return $labels[$this->status] ?? 'غير معروف';
    }

    public function getFileSizeFormattedAttribute()
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getIsExpiredAttribute()
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function getIsExpiringSoonAttribute()
    {
        return $this->expiry_date && 
               $this->expiry_date->isFuture() && 
               $this->expiry_date->diffInDays(now()) <= 30;
    }

    public function getIsForReviewAttribute()
    {
        return $this->review_date && 
               $this->review_date->isFuture() && 
               $this->review_date->diffInDays(now()) <= 30;
    }

    public function getCanEditAttribute()
    {
        return !$this->is_locked && 
               in_array($this->status, [self::STATUS_DRAFT, self::STATUS_UNDER_REVIEW]);
    }

    public function getCanApproveAttribute()
    {
        return $this->approval_required && 
               $this->approval_status === self::APPROVAL_PENDING &&
               $this->status === self::STATUS_PENDING_APPROVAL;
    }

    /**
     * Methods
     */
    public function createNewVersion($attributes = [])
    {
        // Mark current version as not current
        $this->update(['is_current_version' => false]);
        
        // Create new version
        $newVersion = $this->replicate();
        $newVersion->fill($attributes);
        $newVersion->version = $this->version + 0.1;
        $newVersion->is_current_version = true;
        $newVersion->parent_document_id = $this->parent_document_id ?: $this->id;
        $newVersion->status = self::STATUS_DRAFT;
        $newVersion->approval_status = $this->approval_required ? self::APPROVAL_PENDING : self::APPROVAL_NOT_REQUIRED;
        $newVersion->created_by = auth()->id();
        $newVersion->save();
        
        return $newVersion;
    }

    public function lock($userId = null)
    {
        $this->update([
            'is_locked' => true,
            'locked_by' => $userId ?: auth()->id(),
            'locked_at' => now(),
        ]);
        
        return $this;
    }

    public function unlock()
    {
        $this->update([
            'is_locked' => false,
            'locked_by' => null,
            'locked_at' => null,
        ]);
        
        return $this;
    }

    public function approve($userId = null)
    {
        $this->update([
            'approval_status' => self::APPROVAL_APPROVED,
            'approved_by' => $userId ?: auth()->id(),
            'approved_at' => now(),
            'status' => self::STATUS_APPROVED,
        ]);
        
        return $this;
    }

    public function reject($reason = null)
    {
        $this->update([
            'approval_status' => self::APPROVAL_REJECTED,
            'status' => self::STATUS_REJECTED,
        ]);
        
        // Log rejection reason if provided
        if ($reason) {
            $this->comments()->create([
                'content' => 'Document rejected: ' . $reason,
                'type' => 'rejection',
                'user_id' => auth()->id(),
            ]);
        }
        
        return $this;
    }

    public function activate()
    {
        $this->update(['status' => self::STATUS_ACTIVE]);
        
        // Mark previous versions as superseded
        if ($this->parent_document_id) {
            Document::where('parent_document_id', $this->parent_document_id)
                   ->where('id', '!=', $this->id)
                   ->where('status', self::STATUS_ACTIVE)
                   ->update(['status' => self::STATUS_SUPERSEDED]);
        }
        
        return $this;
    }

    public function archive()
    {
        $this->update(['status' => self::STATUS_ARCHIVED]);
        return $this;
    }

    public function incrementViewCount()
    {
        $this->increment('view_count');
        return $this;
    }

    public function incrementDownloadCount()
    {
        $this->increment('download_count');
        return $this;
    }

    public function canAccess($userId)
    {
        // Creator can always access
        if ($this->created_by === $userId) {
            return true;
        }

        // Public documents are accessible to all
        if ($this->visibility === self::VISIBILITY_PUBLIC) {
            return true;
        }

        // Internal documents are accessible to authenticated users
        if ($this->visibility === self::VISIBILITY_INTERNAL && $userId) {
            return true;
        }

        return false;
    }

    public function generateChecksum()
    {
        if ($this->file_path && file_exists(storage_path('app/' . $this->file_path))) {
            $this->checksum = hash_file('sha256', storage_path('app/' . $this->file_path));
            $this->save();
        }
        
        return $this->checksum;
    }

    public function verifyIntegrity()
    {
        if (!$this->checksum || !$this->file_path) {
            return true; // Skip verification if no checksum
        }

        if (!file_exists(storage_path('app/private/' . $this->file_path))) {
            return false;
        }

        $currentChecksum = hash_file('md5', storage_path('app/private/' . $this->file_path));
        return $currentChecksum === $this->checksum;
    }
}
