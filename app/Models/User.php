<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use App\Traits\TwoFactorAuthentication;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Carbon\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $phone
 * @property string|null $avatar
 * @property bool $is_active
 * @property bool $is_super_admin
 * @property int|null $tenant_id
 * @property \Carbon\Carbon|null $last_login_at
 * @property array|null $settings
 * @property string|null $employee_id
 * @property string|null $department
 * @property string|null $position
 * @property bool $two_factor_enabled
 * @property string|null $two_factor_secret
 * @property array|null $two_factor_recovery_codes
 * @property \Carbon\Carbon|null $two_factor_confirmed_at
 * @property string|null $two_factor_method
 * @property string|null $two_factor_phone
 * @property string|null $last_login_ip
 * @property array|null $login_attempts
 * @property \Carbon\Carbon|null $locked_until
 * @property array|null $notification_settings
 * @property string|null $remember_token
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasRoles, TwoFactorAuthentication;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'avatar',
        'is_active',
        'is_super_admin',
        'tenant_id',
        'last_login_at',
        'settings',
        'employee_id',
        'department',
        'position',
        'two_factor_enabled',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
        'two_factor_method',
        'two_factor_phone',
        'last_login_ip',
        'login_attempts',
        'locked_until',
        'notification_settings',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'is_super_admin' => 'boolean',
            'last_login_at' => 'datetime',
            'settings' => 'array',
            'two_factor_enabled' => 'boolean',
            'two_factor_confirmed_at' => 'datetime',
            'login_attempts' => 'array',
            'locked_until' => 'datetime',
            'notification_settings' => 'array',
        ];
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $attributes = [
        'is_active' => true,
        'is_super_admin' => false,
        'settings' => '{}',
    ];

    /**
     * Get the tenant that owns the user
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the user's preferences
     */
    public function preferences(): HasMany
    {
        return $this->hasMany(UserPreference::class);
    }

    /**
     * Get the user's notifications
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Check if user is a super admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->is_super_admin;
    }

    /**
     * Check if user is a tenant admin
     */
    public function isTenantAdmin(): bool
    {
        return $this->hasRole('tenant-admin');
    }

    /**
     * Scope for active users
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for super admins
     */
    public function scopeSuperAdmins($query)
    {
        return $query->where('is_super_admin', true);
    }

    /**
     * Scope for tenant users
     */
    public function scopeTenantUsers($query)
    {
        return $query->where('is_super_admin', false);
    }
}
