<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPreference extends Model
{
    protected $fillable = [
        'user_id',
        'key',
        'value',
        'type',
        'category',
    ];

    protected $casts = [
        'value' => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Preference categories
    public const CATEGORY_THEME = 'theme';
    public const CATEGORY_LAYOUT = 'layout';
    public const CATEGORY_DASHBOARD = 'dashboard';
    public const CATEGORY_NOTIFICATIONS = 'notifications';
    public const CATEGORY_LANGUAGE = 'language';
    public const CATEGORY_GENERAL = 'general';

    // Preference types
    public const TYPE_STRING = 'string';
    public const TYPE_INTEGER = 'integer';
    public const TYPE_BOOLEAN = 'boolean';
    public const TYPE_ARRAY = 'array';
    public const TYPE_JSON = 'json';

    /**
     * Get the user that owns the preference
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for preferences by category
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope for preferences by key
     */
    public function scopeByKey($query, string $key)
    {
        return $query->where('key', $key);
    }

    /**
     * Get typed value
     */
    public function getTypedValue()
    {
        $rawValue = $this->value;

        switch ($this->type) {
            case self::TYPE_BOOLEAN:
                return is_bool($rawValue) ? $rawValue : (bool) $rawValue;
            case self::TYPE_INTEGER:
                return is_int($rawValue) ? $rawValue : (int) $rawValue;
            case self::TYPE_ARRAY:
            case self::TYPE_JSON:
                return is_array($rawValue) ? $rawValue : (is_string($rawValue) ? json_decode($rawValue, true) : $rawValue);
            default:
                return $rawValue;
        }
    }

    /**
     * Set typed value
     */
    public function setTypedValue($value): void
    {
        switch ($this->type) {
            case self::TYPE_BOOLEAN:
            case self::TYPE_INTEGER:
            case self::TYPE_STRING:
                $this->value = $value;
                break;
            case self::TYPE_ARRAY:
            case self::TYPE_JSON:
                $this->value = is_array($value) ? $value : (is_string($value) ? json_decode($value, true) : $value);
                break;
            default:
                $this->value = $value;
        }
    }

    /**
     * Get default preferences
     */
    public static function getDefaults(): array
    {
        return [
            // Theme preferences
            'theme.mode' => ['value' => 'light', 'type' => self::TYPE_STRING, 'category' => self::CATEGORY_THEME],
            'theme.color' => ['value' => 'blue', 'type' => self::TYPE_STRING, 'category' => self::CATEGORY_THEME],
            'theme.sidebar_collapsed' => ['value' => false, 'type' => self::TYPE_BOOLEAN, 'category' => self::CATEGORY_THEME],

            // Layout preferences
            'layout.sidebar_position' => ['value' => 'right', 'type' => self::TYPE_STRING, 'category' => self::CATEGORY_LAYOUT],
            'layout.header_fixed' => ['value' => true, 'type' => self::TYPE_BOOLEAN, 'category' => self::CATEGORY_LAYOUT],
            'layout.footer_fixed' => ['value' => false, 'type' => self::TYPE_BOOLEAN, 'category' => self::CATEGORY_LAYOUT],

            // Dashboard preferences
            'dashboard.widgets' => ['value' => ['sales', 'inventory', 'notifications'], 'type' => self::TYPE_ARRAY, 'category' => self::CATEGORY_DASHBOARD],
            'dashboard.refresh_interval' => ['value' => 30, 'type' => self::TYPE_INTEGER, 'category' => self::CATEGORY_DASHBOARD],
            'dashboard.show_welcome' => ['value' => true, 'type' => self::TYPE_BOOLEAN, 'category' => self::CATEGORY_DASHBOARD],

            // Language preferences
            'language.locale' => ['value' => 'ar', 'type' => self::TYPE_STRING, 'category' => self::CATEGORY_LANGUAGE],
            'language.timezone' => ['value' => 'Asia/Baghdad', 'type' => self::TYPE_STRING, 'category' => self::CATEGORY_LANGUAGE],
            'language.date_format' => ['value' => 'Y-m-d', 'type' => self::TYPE_STRING, 'category' => self::CATEGORY_LANGUAGE],

            // General preferences
            'general.items_per_page' => ['value' => 20, 'type' => self::TYPE_INTEGER, 'category' => self::CATEGORY_GENERAL],
            'general.auto_save' => ['value' => true, 'type' => self::TYPE_BOOLEAN, 'category' => self::CATEGORY_GENERAL],
            'general.show_tooltips' => ['value' => true, 'type' => self::TYPE_BOOLEAN, 'category' => self::CATEGORY_GENERAL],
        ];
    }

    /**
     * Get available themes
     */
    public static function getAvailableThemes(): array
    {
        return [
            'light' => 'فاتح',
            'dark' => 'داكن',
            'auto' => 'تلقائي',
        ];
    }

    /**
     * Get available colors
     */
    public static function getAvailableColors(): array
    {
        return [
            'blue' => 'أزرق',
            'green' => 'أخضر',
            'purple' => 'بنفسجي',
            'red' => 'أحمر',
            'orange' => 'برتقالي',
            'teal' => 'أزرق مخضر',
        ];
    }

    /**
     * Get available languages
     */
    public static function getAvailableLanguages(): array
    {
        return [
            'ar' => 'العربية',
            'en' => 'English',
        ];
    }

    /**
     * Get available timezones
     */
    public static function getAvailableTimezones(): array
    {
        return [
            'Asia/Baghdad' => 'بغداد',
            'Asia/Riyadh' => 'الرياض',
            'Asia/Dubai' => 'دبي',
            'UTC' => 'UTC',
        ];
    }
}
