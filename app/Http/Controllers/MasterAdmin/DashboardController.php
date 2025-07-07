<?php

namespace App\Http\Controllers\MasterAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:super_admin');
    }

    /**
     * Display Master Admin Dashboard
     */
    public function index(Request $request)
    {
        try {
            $stats = $this->getDashboardStats();
            $recentActivity = $this->getRecentActivity();
            $systemHealth = $this->getSystemHealth();
            $revenueData = $this->getRevenueData();
            $tenantGrowth = $this->getTenantGrowth();
            $alerts = $this->getSystemAlerts();

            // Return JSON for API requests
            if ($request->expectsJson()) {
                return response()->json([
                    'stats' => $stats,
                    'recent_activity' => $recentActivity,
                    'system_health' => $systemHealth,
                    'revenue_data' => $revenueData,
                    'tenant_growth' => $tenantGrowth,
                    'alerts' => $alerts,
                ]);
            }

            // Return view for web requests
            return view('master-admin.dashboard', compact(
                'stats',
                'recentActivity',
                'systemHealth',
                'revenueData',
                'tenantGrowth',
                'alerts'
            ));
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Master Admin Dashboard Error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            // Return a simple dashboard with default values
            $stats = [
                'total_tenants' => 0,
                'active_tenants' => 0,
                'expired_tenants' => 0,
                'pending_tenants' => 0,
                'total_users' => 0,
                'active_users' => 0,
                'monthly_revenue' => 0,
                'total_revenue' => 0,
                'system_uptime' => '99.9%',
                'storage_usage' => '45%',
                'growth_rate' => 0,
                'churn_rate' => 0,
            ];

            $recentActivity = [];
            $systemHealth = [
                'cpu_usage' => 25,
                'memory_usage' => 45,
                'disk_usage' => 35,
                'active_sessions' => 0,
                'response_time' => 120,
            ];
            $revenueData = [];
            $tenantGrowth = [];
            $alerts = [];

            return view('master-admin.dashboard', compact(
                'stats',
                'recentActivity',
                'systemHealth',
                'revenueData',
                'tenantGrowth',
                'alerts'
            ));
        }
    }

    /**
     * Get dashboard statistics
     */
    private function getDashboardStats(): array
    {
        $totalTenants = Tenant::count();
        $activeTenants = Tenant::active()->count();
        $expiredTenants = Tenant::withExpiredLicense()->count();
        // Consider inactive tenants created in the last 30 days as "pending"
        $pendingTenants = Tenant::where('is_active', false)
                               ->where('created_at', '>=', now()->subDays(30))
                               ->count();
        
        $totalUsers = User::tenantUsers()->count();
        $activeUsers = User::tenantUsers()->where('is_active', true)->count();
        
        $monthlyRevenue = $this->calculateMonthlyRevenue();
        $totalRevenue = $this->calculateTotalRevenue();
        
        $systemUptime = $this->getSystemUptime();
        $storageUsage = $this->getStorageUsage();

        return [
            'total_tenants' => $totalTenants,
            'active_tenants' => $activeTenants,
            'expired_tenants' => $expiredTenants,
            'pending_tenants' => $pendingTenants,
            'total_users' => $totalUsers,
            'active_users' => $activeUsers,
            'monthly_revenue' => $monthlyRevenue,
            'total_revenue' => $totalRevenue,
            'system_uptime' => $systemUptime,
            'storage_usage' => $storageUsage,
            'growth_rate' => $this->calculateGrowthRate(),
            'churn_rate' => $this->calculateChurnRate(),
        ];
    }

    /**
     * Get recent activity
     */
    private function getRecentActivity(): array
    {
        $recentTenants = Tenant::with('creator')
                              ->latest()
                              ->take(5)
                              ->get()
                              ->map(function ($tenant) {
                                  return [
                                      'type' => 'tenant_created',
                                      'title' => 'مستأجر جديد',
                                      'description' => "تم إنشاء مستأجر جديد: {$tenant->name}",
                                      'time' => $tenant->created_at,
                                      'icon' => 'fas fa-building',
                                      'color' => 'green',
                                  ];
                              });

        $recentUsers = User::tenantUsers()
                          ->with('tenant')
                          ->latest()
                          ->take(5)
                          ->get()
                          ->map(function ($user) {
                              return [
                                  'type' => 'user_registered',
                                  'title' => 'مستخدم جديد',
                                  'description' => "مستخدم جديد انضم إلى {$user->tenant->name}",
                                  'time' => $user->created_at,
                                  'icon' => 'fas fa-user-plus',
                                  'color' => 'blue',
                              ];
                          });

        // Merge and sort by time
        $activities = $recentTenants->concat($recentUsers)
                                   ->sortByDesc('time')
                                   ->values()
                                   ->take(10);

        return $activities->toArray();
    }

    /**
     * Get system health metrics
     */
    private function getSystemHealth(): array
    {
        return [
            'cpu_usage' => rand(15, 45), // Simulated
            'memory_usage' => rand(40, 70), // Simulated
            'disk_usage' => rand(25, 60), // Simulated
            'database_connections' => rand(5, 25), // Simulated
            'active_sessions' => User::whereNotNull('last_login_at')
                                    ->where('last_login_at', '>', now()->subHours(24))
                                    ->count(),
            'response_time' => rand(50, 200), // Simulated in ms
            'uptime_percentage' => 99.8, // Simulated
            'last_backup' => now()->subHours(6), // Simulated
        ];
    }

    /**
     * Get revenue data for charts
     */
    private function getRevenueData(): array
    {
        $months = [];
        $revenue = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = $date->format('M Y');
            $revenue[] = rand(50000000, 150000000); // Simulated revenue in IQD
        }

        return [
            'labels' => $months,
            'data' => $revenue,
            'total' => array_sum($revenue),
            'average' => array_sum($revenue) / count($revenue),
        ];
    }

    /**
     * Get tenant growth data
     */
    private function getTenantGrowth(): array
    {
        $months = [];
        $tenants = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = $date->format('M');
            
            // Count tenants created up to this month
            $count = Tenant::where('created_at', '<=', $date->endOfMonth())->count();
            $tenants[] = $count;
        }

        return [
            'labels' => $months,
            'data' => $tenants,
        ];
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
                'action' => 'مراجعة التراخيص',
                'url' => route('master-admin.tenants.expired')
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
                'action' => 'تجديد التراخيص',
                'url' => route('master-admin.tenants.index', ['filter' => 'expiring'])
            ];
        }

        // Check for pending tenants (inactive tenants created recently)
        $pendingCount = Tenant::where('is_active', false)
                             ->where('created_at', '>=', now()->subDays(30))
                             ->count();
        if ($pendingCount > 0) {
            $alerts[] = [
                'type' => 'info',
                'title' => 'طلبات انتظار',
                'message' => "يوجد {$pendingCount} طلب مستأجر في الانتظار",
                'action' => 'مراجعة الطلبات',
                'url' => route('master-admin.tenants.pending')
            ];
        }

        // Check system health
        $systemHealth = $this->getSystemHealth();
        if ($systemHealth['disk_usage'] > 80) {
            $alerts[] = [
                'type' => 'warning',
                'title' => 'مساحة التخزين منخفضة',
                'message' => "استخدام القرص الصلب {$systemHealth['disk_usage']}%",
                'action' => 'تنظيف الملفات',
                'url' => route('master-admin.system.monitoring')
            ];
        }

        return $alerts;
    }

    /**
     * Calculate monthly revenue (simulated)
     */
    private function calculateMonthlyRevenue(): int
    {
        $activeTenants = Tenant::active()->count();
        $averagePrice = 500000; // Average price per tenant in IQD
        return $activeTenants * $averagePrice;
    }

    /**
     * Calculate total revenue (simulated)
     */
    private function calculateTotalRevenue(): int
    {
        return $this->calculateMonthlyRevenue() * 12; // Yearly estimate
    }

    /**
     * Calculate growth rate
     */
    private function calculateGrowthRate(): float
    {
        $currentMonth = Tenant::whereMonth('created_at', now()->month)
                             ->whereYear('created_at', now()->year)
                             ->count();
        
        $lastMonth = Tenant::whereMonth('created_at', now()->subMonth()->month)
                          ->whereYear('created_at', now()->subMonth()->year)
                          ->count();

        if ($lastMonth == 0) return 100.0;
        
        return round((($currentMonth - $lastMonth) / $lastMonth) * 100, 1);
    }

    /**
     * Calculate churn rate
     */
    private function calculateChurnRate(): float
    {
        $inactiveTenants = Tenant::inactive()->count();
        $totalTenants = Tenant::count();
        
        if ($totalTenants == 0) return 0.0;
        
        return round(($inactiveTenants / $totalTenants) * 100, 1);
    }

    /**
     * Get system uptime (simulated)
     */
    private function getSystemUptime(): string
    {
        return '99.8%'; // Simulated uptime
    }

    /**
     * Get storage usage
     */
    private function getStorageUsage(): array
    {
        $totalSpace = disk_total_space(storage_path());
        $freeSpace = disk_free_space(storage_path());
        $usedSpace = $totalSpace - $freeSpace;

        return [
            'total' => $this->formatBytes($totalSpace),
            'used' => $this->formatBytes($usedSpace),
            'free' => $this->formatBytes($freeSpace),
            'percentage' => round(($usedSpace / $totalSpace) * 100, 1),
        ];
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
