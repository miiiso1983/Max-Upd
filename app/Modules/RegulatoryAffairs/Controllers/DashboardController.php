<?php

namespace App\Modules\RegulatoryAffairs\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\RegulatoryAffairs\Models\PharmaceuticalCompany;
use App\Modules\RegulatoryAffairs\Models\PharmaceuticalProduct;
use App\Modules\RegulatoryAffairs\Models\PharmaceuticalBatch;
use App\Modules\RegulatoryAffairs\Models\PharmaceuticalTest;
use App\Modules\RegulatoryAffairs\Models\PharmaceuticalInspection;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Show the regulatory affairs dashboard
     */
    public function index(): View
    {
        // Get basic statistics
        $stats = $this->getDashboardStats();
        
        // Get recent activities
        $recentActivities = $this->getRecentActivities();
        
        // Get expiry alerts
        $expiryAlerts = $this->getExpiryAlerts();
        
        return view('regulatory-affairs.dashboard', compact(
            'stats',
            'recentActivities', 
            'expiryAlerts'
        ));
    }

    /**
     * Get dashboard statistics
     */
    private function getDashboardStats(): array
    {
        return [
            'companies' => [
                'total' => PharmaceuticalCompany::count(),
                'active' => PharmaceuticalCompany::where('license_status', 'active')->count(),
                'expiring' => PharmaceuticalCompany::where('license_expiry_date', '<=', now()->addDays(30))
                    ->where('license_expiry_date', '>', now())
                    ->count(),
            ],
            'products' => [
                'total' => PharmaceuticalProduct::count(),
                'active' => PharmaceuticalProduct::where('license_status', 'active')->count(),
                'expiring' => PharmaceuticalProduct::where('license_expiry_date', '<=', now()->addDays(30))
                    ->where('license_expiry_date', '>', now())
                    ->count(),
            ],
            'batches' => [
                'total' => PharmaceuticalBatch::count(),
                'released' => PharmaceuticalBatch::where('batch_status', 'released')->count(),
                'testing' => PharmaceuticalBatch::where('batch_status', 'testing')->count(),
                'expiring' => PharmaceuticalBatch::where('expiry_date', '<=', now()->addDays(30))
                    ->where('expiry_date', '>', now())
                    ->count(),
            ],
            'tests' => [
                'total' => PharmaceuticalTest::count(),
                'pending' => PharmaceuticalTest::where('test_result', 'pending')->count(),
                'in_progress' => PharmaceuticalTest::where('test_result', 'pending')->count(),
                'failed' => PharmaceuticalTest::where('test_result', 'fail')->count(),
            ],
            'inspections' => [
                'total' => PharmaceuticalInspection::count(),
                'scheduled' => PharmaceuticalInspection::where('inspection_status', 'scheduled')->count(),
                'completed' => PharmaceuticalInspection::where('inspection_status', 'completed')->count(),
                'satisfactory' => PharmaceuticalInspection::where('inspection_result', 'satisfactory')->count(),
            ],
        ];
    }

    /**
     * Get recent activities
     */
    private function getRecentActivities(): array
    {
        $activities = [];

        // Recent companies
        $recentCompanies = PharmaceuticalCompany::orderBy('created_at', 'desc')->limit(3)->get();
        foreach ($recentCompanies as $company) {
            $activities[] = [
                'type' => 'company',
                'title' => 'شركة جديدة: ' . $company->display_name,
                'date' => $company->created_at,
                'url' => route('regulatory-affairs.companies.show', $company),
                'icon' => 'fas fa-building',
                'color' => 'blue',
            ];
        }

        // Recent products
        $recentProducts = PharmaceuticalProduct::orderBy('created_at', 'desc')->limit(3)->get();
        foreach ($recentProducts as $product) {
            $activities[] = [
                'type' => 'product',
                'title' => 'منتج جديد: ' . $product->display_trade_name,
                'date' => $product->created_at,
                'url' => route('regulatory-affairs.products.show', $product),
                'icon' => 'fas fa-pills',
                'color' => 'green',
            ];
        }

        // Recent batches
        $recentBatches = PharmaceuticalBatch::orderBy('created_at', 'desc')->limit(3)->get();
        foreach ($recentBatches as $batch) {
            $activities[] = [
                'type' => 'batch',
                'title' => 'دفعة جديدة: ' . $batch->batch_number,
                'date' => $batch->created_at,
                'url' => route('regulatory-affairs.batches.show', $batch),
                'icon' => 'fas fa-vials',
                'color' => 'purple',
            ];
        }

        // Recent tests
        $recentTests = PharmaceuticalTest::orderBy('created_at', 'desc')->limit(3)->get();
        foreach ($recentTests as $test) {
            $activities[] = [
                'type' => 'test',
                'title' => 'فحص جديد: ' . $test->test_name,
                'date' => $test->created_at,
                'url' => route('regulatory-affairs.tests.show', $test),
                'icon' => 'fas fa-flask',
                'color' => 'orange',
            ];
        }

        // Sort by date and limit
        usort($activities, function ($a, $b) {
            return $b['date']->timestamp - $a['date']->timestamp;
        });

        return array_slice($activities, 0, 10);
    }

    /**
     * Get expiry alerts
     */
    private function getExpiryAlerts(): array
    {
        $alerts = [];

        // Company license expiry alerts
        $expiringCompanies = PharmaceuticalCompany::where('license_expiry_date', '<=', now()->addDays(30))
            ->where('license_expiry_date', '>', now())
            ->count();
        
        if ($expiringCompanies > 0) {
            $alerts[] = [
                'type' => 'warning',
                'title' => "{$expiringCompanies} تراخيص شركات تنتهي خلال 30 يوم",
                'message' => 'يجب تجديد التراخيص قبل انتهائها',
                'url' => route('regulatory-affairs.reports.expiry-alerts'),
                'icon' => 'fas fa-building',
            ];
        }

        // Product license expiry alerts
        $expiringProducts = PharmaceuticalProduct::where('license_expiry_date', '<=', now()->addDays(30))
            ->where('license_expiry_date', '>', now())
            ->count();
        
        if ($expiringProducts > 0) {
            $alerts[] = [
                'type' => 'warning',
                'title' => "{$expiringProducts} تراخيص منتجات تنتهي خلال 30 يوم",
                'message' => 'مراجعة تراخيص المنتجات المنتهية',
                'url' => route('regulatory-affairs.reports.expiry-alerts'),
                'icon' => 'fas fa-pills',
            ];
        }

        // Batch expiry alerts
        $expiringBatches = PharmaceuticalBatch::where('expiry_date', '<=', now()->addDays(30))
            ->where('expiry_date', '>', now())
            ->where('batch_status', 'released')
            ->count();
        
        if ($expiringBatches > 0) {
            $alerts[] = [
                'type' => 'danger',
                'title' => "{$expiringBatches} دفعات تنتهي صلاحيتها قريباً",
                'message' => 'مراجعة الدفعات المنتهية الصلاحية',
                'url' => route('regulatory-affairs.reports.expiry-alerts'),
                'icon' => 'fas fa-vials',
            ];
        }

        // Failed tests alerts
        $failedTests = PharmaceuticalTest::where('test_result', 'fail')
            ->where('created_at', '>=', now()->subDays(7))
            ->count();
        
        if ($failedTests > 0) {
            $alerts[] = [
                'type' => 'danger',
                'title' => "{$failedTests} فحوصات فاشلة خلال الأسبوع الماضي",
                'message' => 'مراجعة الفحوصات الفاشلة واتخاذ الإجراءات اللازمة',
                'url' => route('regulatory-affairs.reports.testing'),
                'icon' => 'fas fa-flask',
            ];
        }

        return $alerts;
    }

    /**
     * Get dashboard statistics via API
     */
    public function getStats(): JsonResponse
    {
        $stats = $this->getDashboardStats();
        return response()->json($stats);
    }
}
