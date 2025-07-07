<?php

namespace App\Services;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LocalizationService
{
    /**
     * Get all available languages
     */
    public static function getAvailableLanguages(): array
    {
        return config('iraq.languages', [
            'ar' => [
                'name' => 'العربية',
                'native' => 'العربية',
                'direction' => 'rtl',
                'flag' => '🇮🇶',
            ],
            'en' => [
                'name' => 'English',
                'native' => 'English',
                'direction' => 'ltr',
                'flag' => '🇺🇸',
            ],
            'ku' => [
                'name' => 'کوردی',
                'native' => 'کوردی',
                'direction' => 'rtl',
                'flag' => '🏴',
            ],
        ]);
    }

    /**
     * Get current language
     */
    public static function getCurrentLanguage(): string
    {
        return App::getLocale();
    }

    /**
     * Set application language
     */
    public static function setLanguage(string $locale): void
    {
        if (array_key_exists($locale, self::getAvailableLanguages())) {
            App::setLocale($locale);
            Session::put('locale', $locale);
        }
    }

    /**
     * Get language direction (RTL/LTR)
     */
    public static function getDirection(string $locale = null): string
    {
        $locale = $locale ?? self::getCurrentLanguage();
        $languages = self::getAvailableLanguages();
        
        return $languages[$locale]['direction'] ?? 'rtl';
    }

    /**
     * Check if current language is RTL
     */
    public static function isRtl(string $locale = null): bool
    {
        return self::getDirection($locale) === 'rtl';
    }

    /**
     * Get Iraqi governorates
     */
    public static function getGovernorates(): array
    {
        return config('iraq.governorates', []);
    }

    /**
     * Get governorate name in current language
     */
    public static function getGovernorateName(string $code): string
    {
        $governorates = self::getGovernorates();
        $locale = self::getCurrentLanguage();
        
        if (!isset($governorates[$code])) {
            return $code;
        }

        $nameKey = $locale === 'en' ? 'name_en' : ($locale === 'ku' ? 'name_ku' : 'name_ar');
        
        return $governorates[$code][$nameKey] ?? $governorates[$code]['name_ar'] ?? $code;
    }

    /**
     * Get cities for a governorate
     */
    public static function getCities(string $governorate): array
    {
        $governorates = self::getGovernorates();
        
        return $governorates[$governorate]['cities'] ?? [];
    }

    /**
     * Get all cities
     */
    public static function getAllCities(): array
    {
        $governorates = self::getGovernorates();
        $cities = [];
        
        foreach ($governorates as $governorate) {
            if (isset($governorate['cities'])) {
                $cities = array_merge($cities, $governorate['cities']);
            }
        }
        
        return $cities;
    }

    /**
     * Get business types
     */
    public static function getBusinessTypes(): array
    {
        return config('iraq.business_types', []);
    }

    /**
     * Get business type name in current language
     */
    public static function getBusinessTypeName(string $type): string
    {
        $types = self::getBusinessTypes();
        $locale = self::getCurrentLanguage();
        
        if (!isset($types[$type])) {
            return $type;
        }

        $nameKey = $locale === 'en' ? 'name_en' : ($locale === 'ku' ? 'name_ku' : 'name_ar');
        
        return $types[$type][$nameKey] ?? $types[$type]['name_ar'] ?? $type;
    }

    /**
     * Format currency amount
     */
    public static function formatCurrency(float $amount, string $currency = 'IQD'): string
    {
        $config = config('iraq.currency');
        
        if ($currency === 'IQD') {
            $formatted = number_format(
                $amount,
                $config['decimals'],
                $config['decimal_separator'],
                $config['thousands_separator']
            );
            
            return $formatted . ' ' . $config['symbol'];
        }
        
        // For other currencies, use basic formatting
        return number_format($amount, 2) . ' ' . $currency;
    }

    /**
     * Get currency symbol
     */
    public static function getCurrencySymbol(string $currency = 'IQD'): string
    {
        if ($currency === 'IQD') {
            return config('iraq.currency.symbol', 'د.ع');
        }
        
        $symbols = [
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
        ];
        
        return $symbols[$currency] ?? $currency;
    }

    /**
     * Translate text with fallback
     */
    public static function trans(string $key, array $replace = [], string $locale = null): string
    {
        $locale = $locale ?? self::getCurrentLanguage();
        
        $translation = trans($key, $replace, $locale);
        
        // If translation not found and not in Arabic, try Arabic as fallback
        if ($translation === $key && $locale !== 'ar') {
            $translation = trans($key, $replace, 'ar');
        }
        
        // If still not found and not in English, try English as fallback
        if ($translation === $key && $locale !== 'en') {
            $translation = trans($key, $replace, 'en');
        }
        
        return $translation;
    }

    /**
     * Get localized date format
     */
    public static function getDateFormat(string $locale = null): string
    {
        $locale = $locale ?? self::getCurrentLanguage();
        
        $formats = [
            'ar' => 'd/m/Y',
            'en' => 'm/d/Y',
            'ku' => 'd/m/Y',
        ];
        
        return $formats[$locale] ?? 'd/m/Y';
    }

    /**
     * Get localized time format
     */
    public static function getTimeFormat(string $locale = null): string
    {
        $locale = $locale ?? self::getCurrentLanguage();
        
        $formats = [
            'ar' => 'H:i',
            'en' => 'h:i A',
            'ku' => 'H:i',
        ];
        
        return $formats[$locale] ?? 'H:i';
    }

    /**
     * Get localized datetime format
     */
    public static function getDateTimeFormat(string $locale = null): string
    {
        return self::getDateFormat($locale) . ' ' . self::getTimeFormat($locale);
    }

    /**
     * Format date according to locale
     */
    public static function formatDate($date, string $locale = null): string
    {
        if (!$date) {
            return '';
        }
        
        $format = self::getDateFormat($locale);
        
        if (is_string($date)) {
            $date = \Carbon\Carbon::parse($date);
        }
        
        return $date->format($format);
    }

    /**
     * Format time according to locale
     */
    public static function formatTime($time, string $locale = null): string
    {
        if (!$time) {
            return '';
        }
        
        $format = self::getTimeFormat($locale);
        
        if (is_string($time)) {
            $time = \Carbon\Carbon::parse($time);
        }
        
        return $time->format($format);
    }

    /**
     * Format datetime according to locale
     */
    public static function formatDateTime($datetime, string $locale = null): string
    {
        if (!$datetime) {
            return '';
        }
        
        $format = self::getDateTimeFormat($locale);
        
        if (is_string($datetime)) {
            $datetime = \Carbon\Carbon::parse($datetime);
        }
        
        return $datetime->format($format);
    }

    /**
     * Get number format settings for locale
     */
    public static function getNumberFormat(string $locale = null): array
    {
        $locale = $locale ?? self::getCurrentLanguage();
        
        $formats = [
            'ar' => [
                'decimal_separator' => '.',
                'thousands_separator' => ',',
                'decimals' => 2,
            ],
            'en' => [
                'decimal_separator' => '.',
                'thousands_separator' => ',',
                'decimals' => 2,
            ],
            'ku' => [
                'decimal_separator' => '.',
                'thousands_separator' => ',',
                'decimals' => 2,
            ],
        ];
        
        return $formats[$locale] ?? $formats['ar'];
    }

    /**
     * Format number according to locale
     */
    public static function formatNumber(float $number, int $decimals = null, string $locale = null): string
    {
        $format = self::getNumberFormat($locale);
        $decimals = $decimals ?? $format['decimals'];
        
        return number_format(
            $number,
            $decimals,
            $format['decimal_separator'],
            $format['thousands_separator']
        );
    }
}
