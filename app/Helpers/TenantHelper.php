<?php

namespace App\Helpers;

use App\Models\Tenant;

/**
 * @phpstan-ignore-next-line
 * @SuppressWarnings(PHPMD.UndefinedProperty)
 */
class TenantHelper
{
    /**
     * Get current tenant company information
     */
    public static function getCompanyInfo(): array
    {
        /** @var Tenant|null $tenant */
        $tenant = Tenant::current();
        
        return [
            // @phpstan-ignore-next-line
            'name' => $tenant ? $tenant->company_name : 'شركة MaxCon للأدوية',
            // @phpstan-ignore-next-line
            'type' => $tenant ? $tenant->company_type : 'pharmacy',
            // @phpstan-ignore-next-line
            'contact_person' => $tenant ? $tenant->contact_person : 'مدير الشركة',
            // @phpstan-ignore-next-line
            'phone' => $tenant ? $tenant->phone : '+964 770 123 4567',
            // @phpstan-ignore-next-line
            'email' => $tenant ? $tenant->email : 'info@maxcon.iq',
            // @phpstan-ignore-next-line
            'address' => $tenant ? $tenant->address : 'العراق - بغداد - شارع الكرادة',
            // @phpstan-ignore-next-line
            'city' => $tenant ? $tenant->city : 'بغداد',
            // @phpstan-ignore-next-line
            'governorate' => $tenant ? $tenant->governorate : 'Baghdad',
            // @phpstan-ignore-next-line
            'license_key' => $tenant ? $tenant->license_key : '12345/2024',
            // @phpstan-ignore-next-line
            'domain' => $tenant ? $tenant->domain : 'demo',
            // @phpstan-ignore-next-line
            'is_active' => $tenant ? $tenant->is_active : true,
            'tenant' => $tenant
        ];
    }

    /**
     * Get company name only
     */
    public static function getCompanyName(): string
    {
        /** @var Tenant|null $tenant */
        $tenant = Tenant::current();
        // @phpstan-ignore-next-line
        return $tenant ? $tenant->company_name : 'شركة MaxCon للأدوية';
    }

    /**
     * Get company type in Arabic
     */
    public static function getCompanyTypeArabic(): string
    {
        /** @var Tenant|null $tenant */
        $tenant = Tenant::current();
        // @phpstan-ignore-next-line
        $type = $tenant ? $tenant->company_type : 'pharmacy';
        
        $types = [
            'pharmacy' => 'صيدلية',
            'medical_distributor' => 'موزع أدوية',
            'clinic' => 'عيادة',
            'hospital' => 'مستشفى',
            'other' => 'أخرى'
        ];
        
        return $types[$type] ?? 'صيدلية';
    }

    /**
     * Get formatted company address
     */
    public static function getFormattedAddress(): string
    {
        /** @var Tenant|null $tenant */
        $tenant = Tenant::current();
        
        if (!$tenant) {
            return 'العراق - بغداد - شارع الكرادة';
        }
        
        $parts = [];

        // @phpstan-ignore-next-line
        if ($tenant->governorate) {
            // @phpstan-ignore-next-line
            $parts[] = $tenant->governorate;
        }

        // @phpstan-ignore-next-line
        if ($tenant->city) {
            // @phpstan-ignore-next-line
            $parts[] = $tenant->city;
        }

        // @phpstan-ignore-next-line
        if ($tenant->address) {
            // @phpstan-ignore-next-line
            $parts[] = $tenant->address;
        }
        
        return implode(' - ', $parts) ?: 'العراق - بغداد';
    }

    /**
     * Get company contact information for vCard
     */
    public static function getVCardInfo(): array
    {
        $company = self::getCompanyInfo();
        
        return [
            'name' => $company['name'],
            'organization' => $company['name'],
            'phone' => $company['phone'],
            'email' => $company['email'],
            'address' => $company['address'],
            'website' => 'https://' . $company['domain'] . '.maxcon.iq'
        ];
    }

    /**
     * Check if tenant has valid license
     */
    public static function hasValidLicense(): bool
    {
        $tenant = Tenant::current();
        return $tenant ? $tenant->hasValidLicense() : false;
    }

    /**
     * Get license status in Arabic
     */
    public static function getLicenseStatusArabic(): string
    {
        $tenant = Tenant::current();
        
        if (!$tenant) {
            return 'غير محدد';
        }
        
        if (!$tenant->is_active) {
            return 'غير نشط';
        }
        
        if ($tenant->license_expires_at && $tenant->license_expires_at->isPast()) {
            return 'منتهي الصلاحية';
        }
        
        return 'نشط';
    }

    /**
     * Get days until license expiry
     */
    public static function getDaysUntilExpiry(): ?int
    {
        $tenant = Tenant::current();
        
        if (!$tenant || !$tenant->license_expires_at) {
            return null;
        }
        
        return now()->diffInDays($tenant->license_expires_at, false);
    }

    /**
     * Get company logo path (if exists)
     */
    public static function getCompanyLogo(): ?string
    {
        $tenant = Tenant::current();
        
        if (!$tenant) {
            return null;
        }
        
        // Check if logo exists in tenant settings
        $settings = $tenant->settings ?? [];
        
        if (isset($settings['logo_path']) && file_exists(public_path($settings['logo_path']))) {
            return $settings['logo_path'];
        }
        
        // Check default logo path
        $defaultLogo = 'storage/logos/' . $tenant->domain . '.png';
        if (file_exists(public_path($defaultLogo))) {
            return $defaultLogo;
        }
        
        return null;
    }

    /**
     * Get company colors from settings
     */
    public static function getCompanyColors(): array
    {
        $tenant = Tenant::current();
        
        $defaultColors = [
            'primary' => '#2a5298',
            'secondary' => '#1e3c72',
            'accent' => '#3498db',
            'text' => '#333333',
            'background' => '#ffffff'
        ];
        
        if (!$tenant) {
            return $defaultColors;
        }
        
        $settings = $tenant->settings ?? [];
        $colors = $settings['colors'] ?? [];
        
        return array_merge($defaultColors, $colors);
    }

    /**
     * Get invoice footer text
     */
    public static function getInvoiceFooter(): string
    {
        $company = self::getCompanyInfo();
        
        return sprintf(
            'شكراً لتعاملكم معنا - %s | نحو صحة أفضل للجميع',
            $company['name']
        );
    }

    /**
     * Get invoice header subtitle
     */
    public static function getInvoiceSubtitle(): string
    {
        $companyType = self::getCompanyTypeArabic();
        return "نظام إدارة الموارد المؤسسية - {$companyType}";
    }

    /**
     * Format phone number for display
     */
    public static function formatPhoneNumber(?string $phone): string
    {
        if (!$phone) {
            return '+964 770 123 4567';
        }
        
        // Remove any non-digit characters except +
        $cleaned = preg_replace('/[^\d+]/', '', $phone);
        
        // If doesn't start with +964, add it
        if (!str_starts_with($cleaned, '+964')) {
            $cleaned = '+964' . ltrim($cleaned, '0');
        }
        
        return $cleaned;
    }

    /**
     * Get tenant statistics for dashboard
     */
    public static function getTenantStats(): array
    {
        $tenant = Tenant::current();
        
        if (!$tenant) {
            return [
                'users_count' => 0,
                'license_status' => 'غير محدد',
                'days_until_expiry' => null,
                'is_active' => false
            ];
        }
        
        return [
            'users_count' => $tenant->users()->count(),
            'license_status' => self::getLicenseStatusArabic(),
            'days_until_expiry' => self::getDaysUntilExpiry(),
            'is_active' => $tenant->is_active,
            'max_users' => $tenant->max_users,
            'can_add_users' => $tenant->canAddUser()
        ];
    }

    /**
     * Check if current user can access tenant features
     */
    public static function canAccessFeatures(): bool
    {
        $tenant = Tenant::current();
        return $tenant && $tenant->hasValidLicense();
    }

    /**
     * Get tenant-specific settings
     */
    public static function getSetting(string $key, $default = null)
    {
        $tenant = Tenant::current();
        
        if (!$tenant) {
            return $default;
        }
        
        $settings = $tenant->settings ?? [];
        return $settings[$key] ?? $default;
    }

    /**
     * Update tenant setting
     */
    public static function updateSetting(string $key, $value): bool
    {
        $tenant = Tenant::current();
        
        if (!$tenant) {
            return false;
        }
        
        $settings = $tenant->settings ?? [];
        $settings[$key] = $value;
        
        return $tenant->update(['settings' => $settings]);
    }
}
