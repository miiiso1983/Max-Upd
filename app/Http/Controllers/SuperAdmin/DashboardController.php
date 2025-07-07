<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{

    /**
     * Display the super admin dashboard
     */
    public function index()
    {
        $stats = $this->getDashboardStats();
        $recentActivity = $this->getRecentActivity();
        $systemHealth = $this->getSystemHealth();
        $alerts = $this->getSystemAlerts();
        $recentTenants = Tenant::latest()->take(5)->get();

        // Return JSON for API requests
        if (request()->expectsJson()) {
            return response()->json([
                'stats' => $stats,
                'recent_activity' => $recentActivity,
                'system_health' => $systemHealth,
                'alerts' => $alerts,
                'recent_tenants' => $recentTenants,
            ]);
        }

        // Return view for web requests
        return view('super-admin.dashboard', compact(
            'stats',
            'recentActivity',
            'systemHealth',
            'alerts',
            'recentTenants'
        ));
    }

    /**
     * Get dashboard statistics
     */
    private function getDashboardStats(): array
    {
        $totalTenants = Tenant::count();
        $activeTenants = Tenant::active()->count();
        $expiredLicenses = Tenant::withExpiredLicense()->count();
        $totalUsers = User::tenantUsers()->count();

        // Revenue calculation (if you have a billing system)
        $monthlyRevenue = 0; // Placeholder

        // Growth calculations
        $lastMonthTenants = Tenant::where('created_at', '>=', now()->subMonth())->count();
        $tenantGrowth = $totalTenants > 0 ? ($lastMonthTenants / $totalTenants) * 100 : 0;

        return [
            'total_tenants' => $totalTenants,
            'active_tenants' => $activeTenants,
            'inactive_tenants' => $totalTenants - $activeTenants,
            'expired_licenses' => $expiredLicenses,
            'total_users' => $totalUsers,
            'monthly_revenue' => $monthlyRevenue,
            'tenant_growth' => round($tenantGrowth, 2),
            'tenants_by_type' => Tenant::select('company_type', DB::raw('count(*) as count'))
                                     ->groupBy('company_type')
                                     ->get(),
            'tenants_by_governorate' => Tenant::select('governorate', DB::raw('count(*) as count'))
                                             ->groupBy('governorate')
                                             ->orderBy('count', 'desc')
                                             ->take(10)
                                             ->get(),
        ];
    }

    /**
     * Get recent activity
     */
    private function getRecentActivity(): array
    {
        $recentTenants = Tenant::with('creator')
                              ->latest()
                              ->take(10)
                              ->get()
                              ->map(function ($tenant) {
                                  return [
                                      'type' => 'tenant_created',
                                      'message' => "New tenant '{$tenant->name}' created",
                                      'tenant' => $tenant->name,
                                      'created_by' => $tenant->creator?->name,
                                      'created_at' => $tenant->created_at,
                                  ];
                              });

        $recentUsers = User::tenantUsers()
                          ->with('tenant')
                          ->latest()
                          ->take(10)
                          ->get()
                          ->map(function ($user) {
                              return [
                                  'type' => 'user_created',
                                  'message' => "New user '{$user->name}' registered",
                                  'user' => $user->name,
                                  'tenant' => $user->tenant?->name,
                                  'created_at' => $user->created_at,
                              ];
                          });

        // Merge and sort by created_at
        $activities = $recentTenants->concat($recentUsers)
                                   ->sortByDesc('created_at')
                                   ->values()
                                   ->take(15);

        return $activities->toArray();
    }

    /**
     * Get system health metrics
     */
    private function getSystemHealth(): array
    {
        $tenantsNearExpiry = Tenant::where('license_expires_at', '<=', now()->addDays(30))
                                  ->where('license_expires_at', '>', now())
                                  ->count();

        $inactiveTenants = Tenant::where('is_active', false)->count();

        // Database health (simplified)
        $databaseConnections = 1; // Placeholder

        // Storage usage (simplified)
        $storageUsage = 0; // Placeholder

        return [
            'tenants_near_expiry' => $tenantsNearExpiry,
            'inactive_tenants' => $inactiveTenants,
            'database_connections' => $databaseConnections,
            'storage_usage_gb' => $storageUsage,
            'last_backup' => now()->subHours(6), // Placeholder
            'system_status' => 'healthy', // This would be calculated based on various metrics
        ];
    }

    /**
     * Get tenant performance metrics
     */
    public function tenantPerformance()
    {
        $tenants = Tenant::with('users')
                        ->get()
                        ->map(function ($tenant) {
                            $stats = $tenant->getStatistics();
                            return [
                                'id' => $tenant->id,
                                'name' => $tenant->name,
                                'domain' => $tenant->domain,
                                'company_type' => $tenant->company_type,
                                'total_users' => $stats['total_users'],
                                'active_users' => $stats['active_users'],
                                'license_status' => $stats['license_status'],
                                'days_until_expiry' => $stats['days_until_expiry'],
                                'usage_percentage' => $stats['total_users'] > 0 ?
                                    ($stats['total_users'] / $tenant->max_users) * 100 : 0,
                            ];
                        });

        return response()->json([
            'tenants' => $tenants
        ]);
    }

    /**
     * Get system alerts
     */
    public function alerts()
    {
        $alerts = [];

        // License expiry alerts
        $expiringTenants = Tenant::where('license_expires_at', '<=', now()->addDays(7))
                                ->where('license_expires_at', '>', now())
                                ->get();

        foreach ($expiringTenants as $tenant) {
            $alerts[] = [
                'type' => 'warning',
                'title' => 'License Expiring Soon',
                'message' => "Tenant '{$tenant->name}' license expires in " .
                           now()->diffInDays($tenant->license_expires_at) . " days",
                'tenant_id' => $tenant->id,
                'created_at' => now(),
            ];
        }

        // Expired licenses
        $expiredTenants = Tenant::withExpiredLicense()->get();
        foreach ($expiredTenants as $tenant) {
            $alerts[] = [
                'type' => 'danger',
                'title' => 'License Expired',
                'message' => "Tenant '{$tenant->name}' license has expired",
                'tenant_id' => $tenant->id,
                'created_at' => now(),
            ];
        }

        // High usage alerts
        $highUsageTenants = Tenant::with('users')
                                 ->get()
                                 ->filter(function ($tenant) {
                                     $usage = $tenant->users()->count() / $tenant->max_users;
                                     return $usage >= 0.9;
                                 });

        foreach ($highUsageTenants as $tenant) {
            $alerts[] = [
                'type' => 'info',
                'title' => 'High User Usage',
                'message' => "Tenant '{$tenant->name}' is using " .
                           $tenant->users()->count() . "/" . $tenant->max_users . " user slots",
                'tenant_id' => $tenant->id,
                'created_at' => now(),
            ];
        }

        return response()->json([
            'alerts' => collect($alerts)->sortByDesc('created_at')->values()
        ]);
    }

    /**
     * Get system alerts
     */
    private function getSystemAlerts(): array
    {
        $alerts = [];

        // Check for expired licenses
        $expiredCount = Tenant::withExpiredLicense()->count();
        if ($expiredCount > 0) {
            $alerts[] = [
                'type' => 'danger',
                'title' => 'تراخيص منتهية الصلاحية',
                'message' => "يوجد {$expiredCount} ترخيص منتهي الصلاحية",
                'action' => 'تجديد التراخيص'
            ];
        }

        // Check for licenses expiring soon
        $expiringSoon = Tenant::where('license_expires_at', '<=', now()->addDays(30))
                             ->where('license_expires_at', '>', now())
                             ->count();
        if ($expiringSoon > 0) {
            $alerts[] = [
                'type' => 'warning',
                'title' => 'تراخيص تنتهي قريباً',
                'message' => "يوجد {$expiringSoon} ترخيص ينتهي خلال 30 يوم",
                'action' => 'مراجعة التراخيص'
            ];
        }

        // Check for inactive tenants
        $inactiveCount = Tenant::inactive()->count();
        if ($inactiveCount > 0) {
            $alerts[] = [
                'type' => 'info',
                'title' => 'مستأجرين غير نشطين',
                'message' => "يوجد {$inactiveCount} مستأجر غير نشط",
                'action' => 'مراجعة الحسابات'
            ];
        }

        return $alerts;
    }
}
