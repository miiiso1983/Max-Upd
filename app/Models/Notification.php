<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Notification extends Model
{
    protected $fillable = [
        'type',
        'title',
        'message',
        'data',
        'user_id',
        'tenant_id',
        'notifiable_type',
        'notifiable_id',
        'read_at',
        'priority',
        'action_url',
        'action_text',
        'expires_at',
        'channels',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
        'expires_at' => 'datetime',
        'channels' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Notification types
    public const TYPE_INFO = 'info';
    public const TYPE_SUCCESS = 'success';
    public const TYPE_WARNING = 'warning';
    public const TYPE_ERROR = 'error';
    public const TYPE_SYSTEM = 'system';

    // Priority levels
    public const PRIORITY_LOW = 'low';
    public const PRIORITY_NORMAL = 'normal';
    public const PRIORITY_HIGH = 'high';
    public const PRIORITY_URGENT = 'urgent';

    // Notification channels
    public const CHANNEL_DATABASE = 'database';
    public const CHANNEL_EMAIL = 'email';
    public const CHANNEL_SMS = 'sms';
    public const CHANNEL_PUSH = 'push';
    public const CHANNEL_WHATSAPP = 'whatsapp';

    /**
     * Get the user that owns the notification
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the tenant that owns the notification
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the notifiable entity
     */
    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope for unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope for read notifications
     */
    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    /**
     * Scope for notifications by type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for notifications by priority
     */
    public function scopeByPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope for active notifications (not expired)
     */
    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(): void
    {
        $this->update(['read_at' => now()]);
    }

    /**
     * Mark notification as unread
     */
    public function markAsUnread(): void
    {
        $this->update(['read_at' => null]);
    }

    /**
     * Check if notification is read
     */
    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    /**
     * Check if notification is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at < now();
    }

    /**
     * Get notification types
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_INFO => 'معلومات',
            self::TYPE_SUCCESS => 'نجح',
            self::TYPE_WARNING => 'تحذير',
            self::TYPE_ERROR => 'خطأ',
            self::TYPE_SYSTEM => 'نظام',
        ];
    }

    /**
     * Get priority levels
     */
    public static function getPriorities(): array
    {
        return [
            self::PRIORITY_LOW => 'منخفض',
            self::PRIORITY_NORMAL => 'عادي',
            self::PRIORITY_HIGH => 'عالي',
            self::PRIORITY_URGENT => 'عاجل',
        ];
    }

    /**
     * Get notification channels
     */
    public static function getChannels(): array
    {
        return [
            self::CHANNEL_DATABASE => 'قاعدة البيانات',
            self::CHANNEL_EMAIL => 'البريد الإلكتروني',
            self::CHANNEL_SMS => 'رسالة نصية',
            self::CHANNEL_PUSH => 'إشعار فوري',
            self::CHANNEL_WHATSAPP => 'واتساب',
        ];
    }

    /**
     * Get icon for notification type
     */
    public function getIconAttribute(): string
    {
        $icons = [
            self::TYPE_INFO => 'fas fa-info-circle',
            self::TYPE_SUCCESS => 'fas fa-check-circle',
            self::TYPE_WARNING => 'fas fa-exclamation-triangle',
            self::TYPE_ERROR => 'fas fa-times-circle',
            self::TYPE_SYSTEM => 'fas fa-cog',
        ];

        return $icons[$this->type] ?? 'fas fa-bell';
    }

    /**
     * Get color for notification type
     */
    public function getColorAttribute(): string
    {
        $colors = [
            self::TYPE_INFO => 'blue',
            self::TYPE_SUCCESS => 'green',
            self::TYPE_WARNING => 'orange',
            self::TYPE_ERROR => 'red',
            self::TYPE_SYSTEM => 'gray',
        ];

        return $colors[$this->type] ?? 'blue';
    }

    /**
     * Get formatted time ago
     */
    public function getTimeAgoAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }
}
