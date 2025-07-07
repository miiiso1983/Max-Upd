<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecurityLog extends Model
{
    protected $fillable = [
        'event',
        'description',
        'user_id',
        'ip_address',
        'user_agent',
        'data',
        'severity',
        'tenant_id',
    ];

    protected $casts = [
        'data' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that triggered this security event
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the tenant associated with this log
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Scope for critical events
     */
    public function scopeCritical($query)
    {
        return $query->where('severity', 'critical');
    }

    /**
     * Scope for events by IP
     */
    public function scopeByIp($query, string $ip)
    {
        return $query->where('ip_address', $ip);
    }

    /**
     * Scope for events by user
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for recent events
     */
    public function scopeRecent($query, int $hours = 24)
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }

    /**
     * Get severity level
     */
    public function getSeverityAttribute(): string
    {
        $criticalEvents = [
            'login_failed_multiple',
            'account_locked',
            'unauthorized_access_attempt',
            'privilege_escalation_attempt',
            'data_breach_attempt',
            'sql_injection_attempt',
            'xss_attempt',
            'suspicious_file_upload',
        ];

        if (in_array($this->event, $criticalEvents)) {
            return 'critical';
        }

        $warningEvents = [
            'login_failed',
            'unusual_admin_access',
            'rapid_requests_detected',
            'ip_blocked_temporarily',
        ];

        if (in_array($this->event, $warningEvents)) {
            return 'warning';
        }

        return 'info';
    }

    /**
     * Get formatted event description
     */
    public function getDescriptionAttribute(): string
    {
        $descriptions = [
            'login_success' => 'تسجيل دخول ناجح',
            'login_failed' => 'فشل في تسجيل الدخول',
            'login_failed_multiple' => 'محاولات دخول متعددة فاشلة',
            'account_locked' => 'تم قفل الحساب',
            'logout' => 'تسجيل خروج',
            'password_changed' => 'تم تغيير كلمة المرور',
            'two_factor_enabled' => 'تم تفعيل المصادقة الثنائية',
            'two_factor_disabled' => 'تم إلغاء المصادقة الثنائية',
            'unauthorized_access_attempt' => 'محاولة وصول غير مصرح بها',
            'privilege_escalation_attempt' => 'محاولة رفع الصلاحيات',
            'data_access' => 'الوصول إلى البيانات',
            'data_modification' => 'تعديل البيانات',
            'file_upload' => 'رفع ملف',
            'suspicious_file_upload' => 'رفع ملف مشبوه',
            'sql_injection_attempt' => 'محاولة حقن SQL',
            'xss_attempt' => 'محاولة XSS',
            'rapid_requests_detected' => 'تم اكتشاف طلبات سريعة',
            'ip_blocked_temporarily' => 'تم حظر IP مؤقتاً',
            'unusual_admin_access' => 'وصول إداري غير عادي',
        ];

        return $descriptions[$this->event] ?? $this->event;
    }
}
