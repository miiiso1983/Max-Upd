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
use Carbon\Carbon;

class ReportsController extends Controller
{
    /**
     * Show expiry alerts report
     */
    public function expiryAlerts(): View
    {
        $now = Carbon::now();
        $thirtyDaysFromNow = $now->copy()->addDays(30);

        // Get expired company licenses
        $expiredCompanies = PharmaceuticalCompany::where('license_expiry_date', '<', $now)
            ->where('license_status', '!=', 'cancelled')
            ->orderBy('license_expiry_date', 'desc')
            ->get();

        // Get expired product licenses
        $expiredProducts = PharmaceuticalProduct::with('company')
            ->where('license_expiry_date', '<', $now)
            ->where('license_status', '!=', 'cancelled')
            ->orderBy('license_expiry_date', 'desc')
            ->get();

        // Get expired batches
        $expiredBatches = PharmaceuticalBatch::with(['product.company'])
            ->where('expiry_date', '<', $now)
            ->where('batch_status', '!=', 'recalled')
            ->orderBy('expiry_date', 'desc')
            ->get();

        // Get items expiring soon (within 30 days)
        $expiringSoon = collect();

        // Companies expiring soon
        $companiesExpiringSoon = PharmaceuticalCompany::where('license_expiry_date', '>=', $now)
            ->where('license_expiry_date', '<=', $thirtyDaysFromNow)
            ->where('license_status', 'active')
            ->get();

        foreach ($companiesExpiringSoon as $company) {
            $expiringSoon->push([
                'type' => 'company',
                'name' => $company->display_name,
                'identifier' => $company->registration_number,
                'expiry_date' => $company->license_expiry_date->format('Y-m-d'),
                'days_until_expiry' => $company->license_expiry_date->diffInDays($now),
                'view_url' => route('regulatory-affairs.companies.show', $company),
                'edit_url' => route('regulatory-affairs.companies.edit', $company),
            ]);
        }

        // Products expiring soon
        $productsExpiringSoon = PharmaceuticalProduct::with('company')
            ->where('license_expiry_date', '>=', $now)
            ->where('license_expiry_date', '<=', $thirtyDaysFromNow)
            ->where('license_status', 'active')
            ->get();

        foreach ($productsExpiringSoon as $product) {
            $expiringSoon->push([
                'type' => 'product',
                'name' => $product->display_trade_name,
                'identifier' => $product->registration_number,
                'expiry_date' => $product->license_expiry_date->format('Y-m-d'),
                'days_until_expiry' => $product->license_expiry_date->diffInDays($now),
                'view_url' => route('regulatory-affairs.products.show', $product),
                'edit_url' => route('regulatory-affairs.products.edit', $product),
            ]);
        }

        // Batches expiring soon
        $batchesExpiringSoon = PharmaceuticalBatch::with(['product.company'])
            ->where('expiry_date', '>=', $now)
            ->where('expiry_date', '<=', $thirtyDaysFromNow)
            ->where('batch_status', 'released')
            ->get();

        foreach ($batchesExpiringSoon as $batch) {
            $expiringSoon->push([
                'type' => 'batch',
                'name' => $batch->batch_number,
                'identifier' => $batch->product->display_trade_name,
                'expiry_date' => $batch->expiry_date->format('Y-m-d'),
                'days_until_expiry' => $batch->expiry_date->diffInDays($now),
                'view_url' => route('regulatory-affairs.batches.show', $batch),
                'edit_url' => route('regulatory-affairs.batches.edit', $batch),
            ]);
        }

        // Sort expiring soon by days until expiry
        $expiringSoon = $expiringSoon->sortBy('days_until_expiry');

        return view('regulatory-affairs.reports.expiry-alerts', compact(
            'expiredCompanies',
            'expiredProducts',
            'expiredBatches',
            'expiringSoon'
        ));
    }

    /**
     * Show compliance report
     */
    public function complianceReport(): View
    {
        // Get compliance statistics
        $stats = [
            'total_companies' => PharmaceuticalCompany::count(),
            'compliant_companies' => PharmaceuticalCompany::where('license_status', 'active')
                ->where('license_expiry_date', '>', now())
                ->count(),
            'total_products' => PharmaceuticalProduct::count(),
            'compliant_products' => PharmaceuticalProduct::where('license_status', 'active')
                ->where('license_expiry_date', '>', now())
                ->count(),
            'total_inspections' => PharmaceuticalInspection::count(),
            'satisfactory_inspections' => PharmaceuticalInspection::where('inspection_result', 'satisfactory')->count(),
        ];

        // Recent inspections
        $recentInspections = PharmaceuticalInspection::with(['company', 'product', 'batch'])
            ->orderBy('inspection_date', 'desc')
            ->limit(10)
            ->get();

        // Non-compliant items
        $nonCompliantCompanies = PharmaceuticalCompany::where(function ($query) {
            $query->where('license_status', '!=', 'active')
                  ->orWhere('license_expiry_date', '<=', now());
        })->get();

        $nonCompliantProducts = PharmaceuticalProduct::where(function ($query) {
            $query->where('license_status', '!=', 'active')
                  ->orWhere('license_expiry_date', '<=', now());
        })->get();

        return view('regulatory-affairs.reports.compliance-report', compact(
            'stats',
            'recentInspections',
            'nonCompliantCompanies',
            'nonCompliantProducts'
        ));
    }

    /**
     * Show testing report
     */
    public function testingReport(): View
    {
        // Get testing statistics
        $stats = [
            'total_tests' => PharmaceuticalTest::count(),
            'passed_tests' => PharmaceuticalTest::where('test_result', 'pass')->count(),
            'failed_tests' => PharmaceuticalTest::where('test_result', 'fail')->count(),
            'pending_tests' => PharmaceuticalTest::where('test_result', 'pending')->count(),
        ];

        // Recent tests
        $recentTests = PharmaceuticalTest::with(['batch.product.company'])
            ->orderBy('test_date', 'desc')
            ->limit(15)
            ->get();

        // Failed tests
        $failedTests = PharmaceuticalTest::with(['batch.product.company'])
            ->where('test_result', 'fail')
            ->orderBy('test_date', 'desc')
            ->limit(10)
            ->get();

        // Test statistics by type
        $testsByType = PharmaceuticalTest::selectRaw('test_type, COUNT(*) as count, 
                                                     SUM(CASE WHEN test_result = "pass" THEN 1 ELSE 0 END) as passed,
                                                     SUM(CASE WHEN test_result = "fail" THEN 1 ELSE 0 END) as failed')
            ->groupBy('test_type')
            ->get();

        return view('regulatory-affairs.reports.testing-report', compact(
            'stats',
            'recentTests',
            'failedTests',
            'testsByType'
        ));
    }

    /**
     * Show batch tracking report
     */
    public function batchTrackingReport(): View
    {
        // Get batch statistics
        $stats = [
            'total_batches' => PharmaceuticalBatch::count(),
            'released_batches' => PharmaceuticalBatch::where('batch_status', 'released')->count(),
            'testing_batches' => PharmaceuticalBatch::where('batch_status', 'testing')->count(),
            'rejected_batches' => PharmaceuticalBatch::where('batch_status', 'rejected')->count(),
        ];

        // Recent batches
        $recentBatches = PharmaceuticalBatch::with(['product.company'])
            ->orderBy('manufacturing_date', 'desc')
            ->limit(15)
            ->get();

        // Batches by status
        $batchesByStatus = PharmaceuticalBatch::selectRaw('batch_status, COUNT(*) as count')
            ->groupBy('batch_status')
            ->get();

        // Expiring batches
        $expiringBatches = PharmaceuticalBatch::with(['product.company'])
            ->where('expiry_date', '<=', now()->addDays(90))
            ->where('expiry_date', '>=', now())
            ->where('batch_status', 'released')
            ->orderBy('expiry_date')
            ->get();

        return view('regulatory-affairs.reports.batch-tracking-report', compact(
            'stats',
            'recentBatches',
            'batchesByStatus',
            'expiringBatches'
        ));
    }
}
