<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Multitenancy\Models\Tenant as BaseTenant;

/**
 * @property int $id
 * @property string $name
 * @property string $domain
 * @property string $database
 * @property string $company_name
 * @property string $company_type
 * @property string $contact_person
 * @property string $email
 * @property string $phone
 * @property string $address
 * @property string $city
 * @property string $governorate
 * @property string $license_key
 * @property \Carbon\Carbon|null $license_expires_at
 * @property int $max_users
 * @property bool $is_active
 * @property array|null $settings
 * @property int|null $created_by
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Tenant extends BaseTenant
{
    use HasFactory;

    protected $fillable = [
        'name',
        'domain',
        'database',
        'company_name',
        'company_type',
        'contact_person',
        'email',
        'phone',
        'address',
        'city',
        'governorate',
        'license_key',
        'license_expires_at',
        'max_users',
        'is_active',
        'settings',
        'created_by',
    ];

    protected $casts = [
        'license_expires_at' => 'datetime',
        'is_active' => 'boolean',
        'settings' => 'array',
        'max_users' => 'integer',
    ];

    protected $attributes = [
        'is_active' => true,
        'max_users' => 10,
        'settings' => '{}',
    ];

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'domain';
    }

    /**
     * Get current tenant from request context
     */
    public static function current(): ?static
    {
        // Try to get tenant from request header
        $domain = request()->header('X-Tenant-Domain');

        // If not in header, try to get from query parameter
        if (!$domain) {
            $domain = request()->query('tenant');
        }

        // If not in query, try to extract from subdomain
        if (!$domain) {
            $domain = static::extractTenantFromDomain();
        }

        if ($domain) {
            return static::where('domain', $domain)
                        ->where('is_active', true)
                        ->first();
        }

        return null;
    }

    /**
     * Extract tenant domain from current request
     */
    private static function extractTenantFromDomain()
    {
        $hostname = request()->getHost();
        $parts = explode('.', $hostname);

        // If subdomain exists and it's not 'www'
        if (count($parts) > 2 && $parts[0] !== 'www') {
            return $parts[0];
        }

        return null;
    }

    /**
     * Check if the tenant's license is valid
     */
    public function hasValidLicense(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->license_expires_at && $this->license_expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Check if the tenant can add more users
     */
    public function canAddUser(): bool
    {
        return $this->users()->count() < $this->max_users;
    }

    /**
     * Get the tenant's users
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the super admin who created this tenant
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get tenant statistics
     */
    public function getStatistics(): array
    {
        return [
            'total_users' => $this->users()->count(),
            'active_users' => $this->users()->where('is_active', true)->count(),
            'license_status' => $this->hasValidLicense() ? 'valid' : 'expired',
            'license_expires_at' => $this->license_expires_at?->format('Y-m-d'),
            'days_until_expiry' => $this->license_expires_at ? 
                now()->diffInDays($this->license_expires_at, false) : null,
        ];
    }

    /**
     * Scope for active tenants
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for inactive tenants
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Scope for tenants with valid licenses
     */
    public function scopeWithValidLicense($query)
    {
        return $query->where('is_active', true)
                    ->where(function ($q) {
                        $q->whereNull('license_expires_at')
                          ->orWhere('license_expires_at', '>', now());
                    });
    }

    /**
     * Scope for expired licenses
     */
    public function scopeWithExpiredLicense($query)
    {
        return $query->whereNotNull('license_expires_at')
                    ->where('license_expires_at', '<', now());
    }

    /**
     * Check if tenant has active data that prevents deletion
     */
    public function hasActiveData(): bool
    {
        // Check if tenant has users
        if ($this->users()->count() > 0) {
            return true;
        }

        // Check if tenant has active subscription
        if ($this->hasValidLicense()) {
            return true;
        }

        // Add more checks as needed for other data
        return false;
    }
}
