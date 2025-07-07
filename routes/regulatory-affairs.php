<?php

use Illuminate\Support\Facades\Route;
use App\Modules\RegulatoryAffairs\Controllers\PharmaceuticalCompanyController;
use App\Modules\RegulatoryAffairs\Controllers\PharmaceuticalProductController;
use App\Modules\RegulatoryAffairs\Controllers\PharmaceuticalBatchController;
use App\Modules\RegulatoryAffairs\Controllers\PharmaceuticalTestController;
use App\Modules\RegulatoryAffairs\Controllers\PharmaceuticalInspectionController;
use App\Modules\RegulatoryAffairs\Controllers\ReportsController;
use App\Modules\RegulatoryAffairs\Controllers\DocumentController;
use App\Modules\RegulatoryAffairs\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| Regulatory Affairs Routes
|--------------------------------------------------------------------------
|
| Here is where you can register regulatory affairs routes for your application.
| These routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group.
|
*/

Route::middleware(['auth', 'tenant'])->prefix('regulatory-affairs')->name('regulatory-affairs.')->group(function () {

    // Route model binding for pharmaceutical companies
    Route::bind('company', function ($value) {
        return \App\Modules\RegulatoryAffairs\Models\PharmaceuticalCompany::findOrFail($value);
    });

    // Route model binding for pharmaceutical products
    Route::bind('product', function ($value) {
        return \App\Modules\RegulatoryAffairs\Models\PharmaceuticalProduct::findOrFail($value);
    });

    // Route model binding for pharmaceutical batches
    Route::bind('batch', function ($value) {
        return \App\Modules\RegulatoryAffairs\Models\PharmaceuticalBatch::findOrFail($value);
    });

    // Route model binding for pharmaceutical inspections
    Route::bind('inspection', function ($value) {
        return \App\Modules\RegulatoryAffairs\Models\PharmaceuticalInspection::findOrFail($value);
    });

    // Route model binding for pharmaceutical tests
    Route::bind('test', function ($value) {
        return \App\Modules\RegulatoryAffairs\Models\PharmaceuticalTest::findOrFail($value);
    });
    
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Pharmaceutical Companies Routes
    Route::prefix('companies')->name('companies.')->group(function () {
        Route::get('/', [PharmaceuticalCompanyController::class, 'index'])->name('index');
        Route::get('/create', [PharmaceuticalCompanyController::class, 'create'])->name('create');
        Route::post('/', [PharmaceuticalCompanyController::class, 'store'])->name('store');
        Route::get('/{company}', [PharmaceuticalCompanyController::class, 'show'])->name('show');
        Route::get('/{company}/edit', [PharmaceuticalCompanyController::class, 'edit'])->name('edit');
        Route::put('/{company}', [PharmaceuticalCompanyController::class, 'update'])->name('update');
        Route::delete('/{company}', [PharmaceuticalCompanyController::class, 'destroy'])->name('destroy');
        
        // Company status and reports
        Route::patch('/{company}/status', [PharmaceuticalCompanyController::class, 'updateStatus'])->name('update-status');
        Route::get('/reports/expiring-licenses', [PharmaceuticalCompanyController::class, 'expiringLicenses'])->name('expiring-licenses');
        Route::get('/reports/expiring-gmp', [PharmaceuticalCompanyController::class, 'expiringGmp'])->name('expiring-gmp');
        Route::get('/dashboard', [PharmaceuticalCompanyController::class, 'dashboard'])->name('dashboard');
    });

    // Pharmaceutical Products Routes
    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/', [PharmaceuticalProductController::class, 'index'])->name('index');
        Route::get('/create', [PharmaceuticalProductController::class, 'create'])->name('create');
        Route::post('/', [PharmaceuticalProductController::class, 'store'])->name('store');
        Route::get('/{product}', [PharmaceuticalProductController::class, 'show'])->name('show');
        Route::get('/{product}/edit', [PharmaceuticalProductController::class, 'edit'])->name('edit');
        Route::put('/{product}', [PharmaceuticalProductController::class, 'update'])->name('update');
        Route::delete('/{product}', [PharmaceuticalProductController::class, 'destroy'])->name('destroy');
        
        // Product status and reports
        Route::patch('/{product}/status', [PharmaceuticalProductController::class, 'updateStatus'])->name('update-status');
        Route::patch('/{product}/market-status', [PharmaceuticalProductController::class, 'updateMarketStatus'])->name('update-market-status');
        Route::get('/reports/expiring-licenses', [PharmaceuticalProductController::class, 'expiringLicenses'])->name('expiring-licenses');
    });

    // Pharmaceutical Batches Routes
    Route::prefix('batches')->name('batches.')->group(function () {
        Route::get('/', [PharmaceuticalBatchController::class, 'index'])->name('index');
        Route::get('/create', [PharmaceuticalBatchController::class, 'create'])->name('create');
        Route::post('/', [PharmaceuticalBatchController::class, 'store'])->name('store');
        Route::get('/{batch}', [PharmaceuticalBatchController::class, 'show'])->name('show');
        Route::get('/{batch}/edit', [PharmaceuticalBatchController::class, 'edit'])->name('edit');
        Route::put('/{batch}', [PharmaceuticalBatchController::class, 'update'])->name('update');
        Route::delete('/{batch}', [PharmaceuticalBatchController::class, 'destroy'])->name('destroy');
        
        // Batch status and actions
        Route::patch('/{batch}/status', [PharmaceuticalBatchController::class, 'updateStatus'])->name('update-status');
        Route::post('/{batch}/recall', [PharmaceuticalBatchController::class, 'issueRecall'])->name('issue-recall');
        Route::get('/reports/expiring', [PharmaceuticalBatchController::class, 'expiringBatches'])->name('expiring');
    });

    // Pharmaceutical Tests Routes
    Route::prefix('tests')->name('tests.')->group(function () {
        Route::get('/', [PharmaceuticalTestController::class, 'index'])->name('index');
        Route::get('/create', [PharmaceuticalTestController::class, 'create'])->name('create');
        Route::post('/', [PharmaceuticalTestController::class, 'store'])->name('store');
        Route::get('/{test}', [PharmaceuticalTestController::class, 'show'])->name('show');
        Route::get('/{test}/edit', [PharmaceuticalTestController::class, 'edit'])->name('edit');
        Route::put('/{test}', [PharmaceuticalTestController::class, 'update'])->name('update');
        Route::delete('/{test}', [PharmaceuticalTestController::class, 'destroy'])->name('destroy');
        
        // Test actions
        Route::patch('/{test}/result', [PharmaceuticalTestController::class, 'updateResult'])->name('update-result');
        Route::post('/{test}/retest', [PharmaceuticalTestController::class, 'createRetest'])->name('create-retest');
        Route::get('/batch/{batch}', [PharmaceuticalTestController::class, 'batchTests'])->name('batch-tests');
        Route::get('/reports/failed', [PharmaceuticalTestController::class, 'failedTests'])->name('failed-tests');
        Route::get('/reports/pending', [PharmaceuticalTestController::class, 'pendingTests'])->name('pending-tests');
    });

    // Pharmaceutical Inspections Routes
    Route::prefix('inspections')->name('inspections.')->group(function () {
        Route::get('/', [PharmaceuticalInspectionController::class, 'index'])->name('index');
        Route::get('/create', [PharmaceuticalInspectionController::class, 'create'])->name('create');
        Route::post('/', [PharmaceuticalInspectionController::class, 'store'])->name('store');
        Route::get('/{inspection}', [PharmaceuticalInspectionController::class, 'show'])->name('show');
        Route::get('/{inspection}/edit', [PharmaceuticalInspectionController::class, 'edit'])->name('edit');
        Route::put('/{inspection}', [PharmaceuticalInspectionController::class, 'update'])->name('update');
        Route::delete('/{inspection}', [PharmaceuticalInspectionController::class, 'destroy'])->name('destroy');
        
        // Inspection actions
        Route::patch('/{inspection}/status', [PharmaceuticalInspectionController::class, 'updateStatus'])->name('update-status');
        Route::post('/{inspection}/complete', [PharmaceuticalInspectionController::class, 'complete'])->name('complete');
        Route::get('/reports/scheduled', [PharmaceuticalInspectionController::class, 'scheduledInspections'])->name('scheduled');
        Route::get('/reports/overdue', [PharmaceuticalInspectionController::class, 'overdueInspections'])->name('overdue');
    });

    // Reports and Analytics Routes
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', function () {
            return view('regulatory-affairs.reports.index');
        })->name('index');

        Route::get('/expiry-alerts', [ReportsController::class, 'expiryAlerts'])->name('expiry-alerts');
        Route::get('/compliance', [ReportsController::class, 'complianceReport'])->name('compliance');
        Route::get('/testing', [ReportsController::class, 'testingReport'])->name('testing');
        Route::get('/batch-tracking', [ReportsController::class, 'batchTrackingReport'])->name('batch-tracking');

        // Legacy routes (keeping for backward compatibility)
        Route::get('/compliance-dashboard', function () {
            return redirect()->route('regulatory-affairs.reports.compliance');
        })->name('compliance-dashboard');

        Route::get('/inspection-summary', function () {
            return view('regulatory-affairs.reports.inspection-summary');
        })->name('inspection-summary');

        Route::get('/batch-quality', function () {
            return redirect()->route('regulatory-affairs.reports.batch-tracking');
        })->name('batch-quality');
    });

    // Document Management Routes
    Route::prefix('documents')->name('documents.')->group(function () {
        Route::post('/upload', [DocumentController::class, 'upload'])->name('upload');
        Route::get('/entity', [DocumentController::class, 'getDocuments'])->name('get-documents');
        Route::get('/{document}/download', [DocumentController::class, 'download'])->name('download');
        Route::get('/{document}/view', [DocumentController::class, 'view'])->name('view');
        Route::put('/{document}', [DocumentController::class, 'update'])->name('update');
        Route::delete('/{document}', [DocumentController::class, 'destroy'])->name('destroy');
        Route::patch('/{document}/approve', [DocumentController::class, 'approve'])->name('approve');
    });

    // API Routes for AJAX calls
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/companies/{company}/products', function ($companyId) {
            $products = \App\Modules\RegulatoryAffairs\Models\PharmaceuticalProduct::where('pharmaceutical_company_id', $companyId)
                                                                                  ->where('status', 'active')
                                                                                  ->get(['id', 'trade_name', 'trade_name_ar']);
            return response()->json($products);
        })->name('company-products');
        
        Route::get('/products/{product}/batches', function ($productId) {
            $batches = \App\Modules\RegulatoryAffairs\Models\PharmaceuticalBatch::where('pharmaceutical_product_id', $productId)
                                                                               ->where('status', 'active')
                                                                               ->get(['id', 'batch_number', 'manufacturing_date', 'expiry_date']);
            return response()->json($batches);
        })->name('product-batches');
        
        Route::get('/dashboard-stats', [DashboardController::class, 'getStats'])->name('dashboard-stats');
    });
});
