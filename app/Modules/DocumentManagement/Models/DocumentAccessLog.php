<?php

namespace App\Modules\DocumentManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class DocumentAccessLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'user_id',
        'action',
        'ip_address',
        'user_agent',
        'session_id',
        'location',
        'device_info',
        'access_method',
        'duration',
        'pages_viewed',
        'download_attempted',
        'download_successful',
        'print_attempted',
        'print_successful',
        'share_attempted',
        'share_successful',
        'access_denied_reason',
        'security_level',
        'compliance_flags',
        'metadata',
    ];

    protected $casts = [
        'duration' => 'integer',
        'pages_viewed' => 'integer',
        'download_attempted' => 'boolean',
        'download_successful' => 'boolean',
        'print_attempted' => 'boolean',
        'print_successful' => 'boolean',
        'share_attempted' => 'boolean',
        'share_successful' => 'boolean',
        'compliance_flags' => 'array',
        'metadata' => 'array',
    ];

    // Action constants
    const ACTION_VIEW = 'view';
    const ACTION_DOWNLOAD = 'download';
    const ACTION_PRINT = 'print';
    const ACTION_SHARE = 'share';
    const ACTION_EDIT = 'edit';
    const ACTION_DELETE = 'delete';
    const ACTION_UPLOAD = 'upload';
    const ACTION_APPROVE = 'approve';
    const ACTION_REJECT = 'reject';
    const ACTION_SIGN = 'sign';
    const ACTION_COMMENT = 'comment';
    const ACTION_VERSION_CREATE = 'version_create';
    const ACTION_LOCK = 'lock';
    const ACTION_UNLOCK = 'unlock';
    const ACTION_ARCHIVE = 'archive';
    const ACTION_RESTORE = 'restore';
    const ACTION_ACCESS_DENIED = 'access_denied';
    const ACTION_SEARCH = 'search';
    const ACTION_EXPORT = 'export';

    // Access method constants
    const METHOD_WEB = 'web';
    const METHOD_API = 'api';
    const METHOD_MOBILE = 'mobile';
    const METHOD_EMAIL = 'email';
    const METHOD_FTP = 'ftp';
    const METHOD_SYNC = 'sync';

    // Security level constants
    const SECURITY_LOW = 'low';
    const SECURITY_MEDIUM = 'medium';
    const SECURITY_HIGH = 'high';
    const SECURITY_CRITICAL = 'critical';

    /**
     * Relationships
     */
    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scopes
     */
    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByDocument($query, $documentId)
    {
        return $query->where('document_id', $documentId);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    public function scopeSecurityEvents($query)
    {
        return $query->whereIn('action', [
            self::ACTION_ACCESS_DENIED,
            self::ACTION_DOWNLOAD,
            self::ACTION_PRINT,
            self::ACTION_SHARE,
            self::ACTION_EXPORT,
        ]);
    }

    public function scopeComplianceEvents($query)
    {
        return $query->whereNotNull('compliance_flags')
                    ->where('compliance_flags', '!=', '[]');
    }

    public function scopeFailedAccess($query)
    {
        return $query->where('action', self::ACTION_ACCESS_DENIED)
                    ->orWhere('download_attempted', true)
                    ->where('download_successful', false)
                    ->orWhere('print_attempted', true)
                    ->where('print_successful', false);
    }

    public function scopeBySecurityLevel($query, $level)
    {
        return $query->where('security_level', $level);
    }

    /**
     * Accessors
     */
    public function getActionLabelAttribute()
    {
        $labels = [
            self::ACTION_VIEW => 'View',
            self::ACTION_DOWNLOAD => 'Download',
            self::ACTION_PRINT => 'Print',
            self::ACTION_SHARE => 'Share',
            self::ACTION_EDIT => 'Edit',
            self::ACTION_DELETE => 'Delete',
            self::ACTION_UPLOAD => 'Upload',
            self::ACTION_APPROVE => 'Approve',
            self::ACTION_REJECT => 'Reject',
            self::ACTION_SIGN => 'Sign',
            self::ACTION_COMMENT => 'Comment',
            self::ACTION_VERSION_CREATE => 'Create Version',
            self::ACTION_LOCK => 'Lock',
            self::ACTION_UNLOCK => 'Unlock',
            self::ACTION_ARCHIVE => 'Archive',
            self::ACTION_RESTORE => 'Restore',
            self::ACTION_ACCESS_DENIED => 'Access Denied',
            self::ACTION_SEARCH => 'Search',
            self::ACTION_EXPORT => 'Export',
        ];

        return $labels[$this->action] ?? 'Unknown';
    }

    public function getActionLabelArAttribute()
    {
        $labels = [
            self::ACTION_VIEW => 'عرض',
            self::ACTION_DOWNLOAD => 'تحميل',
            self::ACTION_PRINT => 'طباعة',
            self::ACTION_SHARE => 'مشاركة',
            self::ACTION_EDIT => 'تحرير',
            self::ACTION_DELETE => 'حذف',
            self::ACTION_UPLOAD => 'رفع',
            self::ACTION_APPROVE => 'موافقة',
            self::ACTION_REJECT => 'رفض',
            self::ACTION_SIGN => 'توقيع',
            self::ACTION_COMMENT => 'تعليق',
            self::ACTION_VERSION_CREATE => 'إنشاء نسخة',
            self::ACTION_LOCK => 'قفل',
            self::ACTION_UNLOCK => 'إلغاء القفل',
            self::ACTION_ARCHIVE => 'أرشفة',
            self::ACTION_RESTORE => 'استعادة',
            self::ACTION_ACCESS_DENIED => 'رفض الوصول',
            self::ACTION_SEARCH => 'بحث',
            self::ACTION_EXPORT => 'تصدير',
        ];

        return $labels[$this->action] ?? 'غير معروف';
    }

    public function getMethodLabelAttribute()
    {
        $labels = [
            self::METHOD_WEB => 'Web Browser',
            self::METHOD_API => 'API',
            self::METHOD_MOBILE => 'Mobile App',
            self::METHOD_EMAIL => 'Email',
            self::METHOD_FTP => 'FTP',
            self::METHOD_SYNC => 'Sync',
        ];

        return $labels[$this->access_method] ?? 'Unknown';
    }

    public function getSecurityLevelLabelAttribute()
    {
        $labels = [
            self::SECURITY_LOW => 'Low',
            self::SECURITY_MEDIUM => 'Medium',
            self::SECURITY_HIGH => 'High',
            self::SECURITY_CRITICAL => 'Critical',
        ];

        return $labels[$this->security_level] ?? 'Medium';
    }

    public function getSecurityLevelLabelArAttribute()
    {
        $labels = [
            self::SECURITY_LOW => 'منخفض',
            self::SECURITY_MEDIUM => 'متوسط',
            self::SECURITY_HIGH => 'عالي',
            self::SECURITY_CRITICAL => 'حرج',
        ];

        return $labels[$this->security_level] ?? 'متوسط';
    }

    public function getDurationFormattedAttribute()
    {
        if (!$this->duration) {
            return 'N/A';
        }

        $minutes = floor($this->duration / 60);
        $seconds = $this->duration % 60;

        if ($minutes > 0) {
            return "{$minutes}m {$seconds}s";
        }

        return "{$seconds}s";
    }

    public function getIsSecurityEventAttribute()
    {
        return in_array($this->action, [
            self::ACTION_ACCESS_DENIED,
            self::ACTION_DOWNLOAD,
            self::ACTION_PRINT,
            self::ACTION_SHARE,
            self::ACTION_EXPORT,
        ]);
    }

    public function getIsComplianceEventAttribute()
    {
        return !empty($this->compliance_flags);
    }

    public function getIsFailedActionAttribute()
    {
        return $this->action === self::ACTION_ACCESS_DENIED ||
               ($this->download_attempted && !$this->download_successful) ||
               ($this->print_attempted && !$this->print_successful) ||
               ($this->share_attempted && !$this->share_successful);
    }

    /**
     * Static methods for logging
     */
    public static function logAccess($documentId, $action, $options = [])
    {
        $log = static::create([
            'document_id' => $documentId,
            'user_id' => auth()->id() ?? 1, // Default to user 1 for testing
            'action' => $action,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'session_id' => session()->getId(),
            'access_method' => $options['method'] ?? self::METHOD_WEB,
            'security_level' => $options['security_level'] ?? self::SECURITY_MEDIUM,
            'metadata' => $options['metadata'] ?? [],
        ]);

        // Add compliance flags if document requires special tracking
        $document = Document::find($documentId);
        if ($document && $document->visibility === Document::VISIBILITY_RESTRICTED) {
            $log->addComplianceFlag('restricted_access');
        }

        return $log;
    }

    public static function logView($documentId, $duration = null, $pagesViewed = null)
    {
        return static::logAccess($documentId, self::ACTION_VIEW, [
            'metadata' => [
                'duration' => $duration,
                'pages_viewed' => $pagesViewed,
            ],
        ]);
    }

    public static function logDownload($documentId, $successful = true)
    {
        return static::logAccess($documentId, self::ACTION_DOWNLOAD, [
            'metadata' => [
                'download_attempted' => true,
                'download_successful' => $successful,
            ],
        ]);
    }

    public static function logPrint($documentId, $successful = true)
    {
        return static::logAccess($documentId, self::ACTION_PRINT, [
            'metadata' => [
                'print_attempted' => true,
                'print_successful' => $successful,
            ],
        ]);
    }

    public static function logShare($documentId, $successful = true, $shareWith = null)
    {
        return static::logAccess($documentId, self::ACTION_SHARE, [
            'metadata' => [
                'share_attempted' => true,
                'share_successful' => $successful,
                'shared_with' => $shareWith,
            ],
        ]);
    }

    public static function logAccessDenied($documentId, $reason)
    {
        return static::logAccess($documentId, self::ACTION_ACCESS_DENIED, [
            'security_level' => self::SECURITY_HIGH,
            'metadata' => [
                'access_denied_reason' => $reason,
            ],
        ]);
    }

    public static function logEdit($documentId, $changes = [])
    {
        return static::logAccess($documentId, self::ACTION_EDIT, [
            'metadata' => [
                'changes' => $changes,
            ],
        ]);
    }

    public static function logApproval($documentId, $approved = true, $comments = null)
    {
        $action = $approved ? self::ACTION_APPROVE : self::ACTION_REJECT;
        
        return static::logAccess($documentId, $action, [
            'metadata' => [
                'comments' => $comments,
            ],
        ]);
    }

    public static function logSignature($documentId, $signatureType)
    {
        return static::logAccess($documentId, self::ACTION_SIGN, [
            'security_level' => self::SECURITY_HIGH,
            'metadata' => [
                'signature_type' => $signatureType,
            ],
        ]);
    }

    /**
     * Methods
     */
    public function addComplianceFlag($flag, $details = null)
    {
        $flags = $this->compliance_flags ?? [];
        $flags[] = [
            'flag' => $flag,
            'details' => $details,
            'timestamp' => now()->toISOString(),
        ];
        
        $this->update(['compliance_flags' => $flags]);
        
        return $this;
    }

    public function updateDuration($duration)
    {
        $this->update(['duration' => $duration]);
        return $this;
    }

    public function updatePagesViewed($pages)
    {
        $this->update(['pages_viewed' => $pages]);
        return $this;
    }

    public function markDownloadAttempted($successful = false)
    {
        $this->update([
            'download_attempted' => true,
            'download_successful' => $successful,
        ]);
        
        return $this;
    }

    public function markPrintAttempted($successful = false)
    {
        $this->update([
            'print_attempted' => true,
            'print_successful' => $successful,
        ]);
        
        return $this;
    }

    public function markShareAttempted($successful = false)
    {
        $this->update([
            'share_attempted' => true,
            'share_successful' => $successful,
        ]);
        
        return $this;
    }

    public function getAuditTrail()
    {
        return [
            'log_id' => $this->id,
            'document_id' => $this->document_id,
            'document_title' => $this->document->title ?? 'Unknown',
            'user_id' => $this->user_id,
            'user_name' => $this->user->name ?? 'Unknown',
            'action' => $this->action,
            'action_label' => $this->action_label,
            'timestamp' => $this->created_at->toISOString(),
            'ip_address' => $this->ip_address,
            'user_agent' => $this->user_agent,
            'access_method' => $this->access_method,
            'security_level' => $this->security_level,
            'duration' => $this->duration_formatted,
            'compliance_flags' => $this->compliance_flags,
            'metadata' => $this->metadata,
        ];
    }

    public static function generateComplianceReport($documentId = null, $startDate = null, $endDate = null)
    {
        $query = static::query();
        
        if ($documentId) {
            $query->where('document_id', $documentId);
        }
        
        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }
        
        return $query->with(['document', 'user'])
                    ->orderBy('created_at', 'desc')
                    ->get()
                    ->map(function ($log) {
                        return $log->getAuditTrail();
                    });
    }

    public static function getSecurityEvents($days = 30)
    {
        return static::securityEvents()
                    ->where('created_at', '>=', now()->subDays($days))
                    ->with(['document', 'user'])
                    ->orderBy('created_at', 'desc')
                    ->get();
    }

    public static function getFailedAccessAttempts($days = 7)
    {
        return static::failedAccess()
                    ->where('created_at', '>=', now()->subDays($days))
                    ->with(['document', 'user'])
                    ->orderBy('created_at', 'desc')
                    ->get();
    }
}
