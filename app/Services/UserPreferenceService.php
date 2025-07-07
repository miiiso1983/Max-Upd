<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Support\Facades\Cache;

class UserPreferenceService
{
    /**
     * Get user preference value
     */
    public static function get(User $user, string $key, $default = null)
    {
        $cacheKey = "user_preferences_{$user->id}";
        
        $preferences = Cache::remember($cacheKey, now()->addHours(24), function () use ($user) {
            return $user->preferences()->pluck('value', 'key')->toArray();
        });
        
        if (isset($preferences[$key])) {
            $preference = $user->preferences()->where('key', $key)->first();
            return $preference ? $preference->getTypedValue() : $default;
        }
        
        // Return default from predefined defaults
        $defaults = UserPreference::getDefaults();
        return $defaults[$key]['value'] ?? $default;
    }

    /**
     * Set user preference value
     */
    public static function set(User $user, string $key, $value, ?string $type = null, ?string $category = null): UserPreference
    {
        // Get default configuration if not provided
        $defaults = UserPreference::getDefaults();
        $defaultConfig = $defaults[$key] ?? [];
        
        $type = $type ?? $defaultConfig['type'] ?? UserPreference::TYPE_STRING;
        $category = $category ?? $defaultConfig['category'] ?? UserPreference::CATEGORY_GENERAL;
        
        $preference = UserPreference::updateOrCreate(
            ['user_id' => $user->id, 'key' => $key],
            [
                'value' => $value,
                'type' => $type,
                'category' => $category,
            ]
        );
        
        // Clear cache
        self::clearCache($user);
        
        return $preference;
    }

    /**
     * Get all preferences for user
     */
    public static function getAll(User $user): array
    {
        $cacheKey = "user_preferences_{$user->id}";
        
        return Cache::remember($cacheKey, now()->addHours(24), function () use ($user) {
            $preferences = [];
            $userPreferences = $user->preferences()->get();
            
            // Start with defaults
            $defaults = UserPreference::getDefaults();
            foreach ($defaults as $key => $config) {
                $preferences[$key] = $config['value'];
            }
            
            // Override with user preferences
            foreach ($userPreferences as $preference) {
                $preferences[$preference->key] = $preference->getTypedValue();
            }
            
            return $preferences;
        });
    }

    /**
     * Get preferences by category
     */
    public static function getByCategory(User $user, string $category): array
    {
        $allPreferences = self::getAll($user);
        $categoryPreferences = [];
        
        $defaults = UserPreference::getDefaults();
        foreach ($defaults as $key => $config) {
            if ($config['category'] === $category) {
                $categoryPreferences[$key] = $allPreferences[$key] ?? $config['value'];
            }
        }
        
        return $categoryPreferences;
    }

    /**
     * Set multiple preferences
     */
    public static function setMultiple(User $user, array $preferences): void
    {
        foreach ($preferences as $key => $value) {
            self::set($user, $key, $value);
        }
    }

    /**
     * Reset preference to default
     */
    public static function reset(User $user, string $key): void
    {
        UserPreference::where('user_id', $user->id)
                     ->where('key', $key)
                     ->delete();
        
        self::clearCache($user);
    }

    /**
     * Reset all preferences to defaults
     */
    public static function resetAll(User $user): void
    {
        UserPreference::where('user_id', $user->id)->delete();
        self::clearCache($user);
    }

    /**
     * Clear user preferences cache
     */
    public static function clearCache(User $user): void
    {
        Cache::forget("user_preferences_{$user->id}");
    }

    /**
     * Get theme preferences
     */
    public static function getThemePreferences(User $user): array
    {
        return self::getByCategory($user, UserPreference::CATEGORY_THEME);
    }

    /**
     * Get layout preferences
     */
    public static function getLayoutPreferences(User $user): array
    {
        return self::getByCategory($user, UserPreference::CATEGORY_LAYOUT);
    }

    /**
     * Get dashboard preferences
     */
    public static function getDashboardPreferences(User $user): array
    {
        return self::getByCategory($user, UserPreference::CATEGORY_DASHBOARD);
    }

    /**
     * Get language preferences
     */
    public static function getLanguagePreferences(User $user): array
    {
        return self::getByCategory($user, UserPreference::CATEGORY_LANGUAGE);
    }

    /**
     * Set theme mode
     */
    public static function setThemeMode(User $user, string $mode): void
    {
        self::set($user, 'theme.mode', $mode, UserPreference::TYPE_STRING, UserPreference::CATEGORY_THEME);
    }

    /**
     * Set theme color
     */
    public static function setThemeColor(User $user, string $color): void
    {
        self::set($user, 'theme.color', $color, UserPreference::TYPE_STRING, UserPreference::CATEGORY_THEME);
    }

    /**
     * Toggle sidebar collapsed state
     */
    public static function toggleSidebar(User $user): bool
    {
        $current = self::get($user, 'theme.sidebar_collapsed', false);
        $new = !$current;
        self::set($user, 'theme.sidebar_collapsed', $new, UserPreference::TYPE_BOOLEAN, UserPreference::CATEGORY_THEME);
        return $new;
    }

    /**
     * Set dashboard widgets
     */
    public static function setDashboardWidgets(User $user, array $widgets): void
    {
        self::set($user, 'dashboard.widgets', $widgets, UserPreference::TYPE_ARRAY, UserPreference::CATEGORY_DASHBOARD);
    }

    /**
     * Set language locale
     */
    public static function setLanguage(User $user, string $locale): void
    {
        self::set($user, 'language.locale', $locale, UserPreference::TYPE_STRING, UserPreference::CATEGORY_LANGUAGE);
    }

    /**
     * Set timezone
     */
    public static function setTimezone(User $user, string $timezone): void
    {
        self::set($user, 'language.timezone', $timezone, UserPreference::TYPE_STRING, UserPreference::CATEGORY_LANGUAGE);
    }

    /**
     * Export user preferences
     */
    public static function export(User $user): array
    {
        return [
            'user_id' => $user->id,
            'preferences' => self::getAll($user),
            'exported_at' => now()->toISOString(),
        ];
    }

    /**
     * Import user preferences
     */
    public static function import(User $user, array $preferences): void
    {
        foreach ($preferences as $key => $value) {
            if (array_key_exists($key, UserPreference::getDefaults())) {
                self::set($user, $key, $value);
            }
        }
    }

    /**
     * Get preference configuration
     */
    public static function getPreferenceConfig(string $key): ?array
    {
        $defaults = UserPreference::getDefaults();
        return $defaults[$key] ?? null;
    }

    /**
     * Validate preference value
     */
    public static function validatePreference(string $key, $value): bool
    {
        $config = self::getPreferenceConfig($key);
        if (!$config) {
            return false;
        }

        switch ($config['type']) {
            case UserPreference::TYPE_BOOLEAN:
                return is_bool($value) || in_array($value, [0, 1, '0', '1', 'true', 'false']);
            case UserPreference::TYPE_INTEGER:
                return is_numeric($value);
            case UserPreference::TYPE_ARRAY:
                return is_array($value);
            case UserPreference::TYPE_STRING:
                return is_string($value) || is_numeric($value);
            default:
                return true;
        }
    }

    /**
     * Get available options for a preference
     */
    public static function getAvailableOptions(string $key): array
    {
        switch ($key) {
            case 'theme.mode':
                return UserPreference::getAvailableThemes();
            case 'theme.color':
                return UserPreference::getAvailableColors();
            case 'language.locale':
                return UserPreference::getAvailableLanguages();
            case 'language.timezone':
                return UserPreference::getAvailableTimezones();
            case 'layout.sidebar_position':
                return ['right' => 'يمين', 'left' => 'يسار'];
            case 'general.items_per_page':
                return [10 => '10', 20 => '20', 50 => '50', 100 => '100'];
            default:
                return [];
        }
    }

    /**
     * Initialize default preferences for new user
     */
    public static function initializeDefaults(User $user): void
    {
        $defaults = UserPreference::getDefaults();
        
        foreach ($defaults as $key => $config) {
            UserPreference::firstOrCreate(
                ['user_id' => $user->id, 'key' => $key],
                [
                    'value' => $config['value'],
                    'type' => $config['type'],
                    'category' => $config['category'],
                ]
            );
        }
        
        self::clearCache($user);
    }
}
