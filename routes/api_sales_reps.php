<?php

use Illuminate\Support\Facades\Route;
use App\Modules\SalesReps\Http\Controllers\Api\SalesRepController;
use App\Modules\SalesReps\Http\Controllers\Api\VisitController;
use App\Modules\SalesReps\Http\Controllers\Api\TerritoryController;
use App\Modules\SalesReps\Http\Controllers\Api\TaskController;
use App\Modules\SalesReps\Http\Controllers\Api\ReportController;
use App\Modules\SalesReps\Http\Controllers\Api\MobileAuthController;

/*
|--------------------------------------------------------------------------
| Sales Representatives API Routes
|--------------------------------------------------------------------------
|
| Here are the API routes for the Sales Representatives module.
| These routes are used by both the web interface and mobile app.
|
*/

// Mobile Authentication Routes
Route::prefix('mobile')->group(function () {
    Route::post('login', [MobileAuthController::class, 'login']);
    Route::post('refresh', [MobileAuthController::class, 'refresh']);
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [MobileAuthController::class, 'logout']);
        Route::get('profile', [MobileAuthController::class, 'profile']);
        Route::put('profile', [MobileAuthController::class, 'updateProfile']);
        Route::post('change-password', [MobileAuthController::class, 'changePassword']);
    });
});

// Sales Representatives Management Routes
Route::middleware('auth:sanctum')->group(function () {
    
    // Sales Representatives CRUD
    Route::apiResource('sales-reps', SalesRepController::class);
    
    // Additional Sales Rep Routes
    Route::prefix('sales-reps/{salesRep}')->group(function () {
        Route::get('performance', [SalesRepController::class, 'performance']);
        Route::get('location', [SalesRepController::class, 'location']);
        Route::post('location', [SalesRepController::class, 'updateLocation']);
        Route::get('customers', [SalesRepController::class, 'customers']);
        Route::get('territories', [SalesRepController::class, 'territories']);
    });
    
    // Bulk Operations
    Route::post('sales-reps/bulk-action', [SalesRepController::class, 'bulkAction']);

    // Territories Management
    Route::apiResource('territories', TerritoryController::class);
    Route::prefix('territories/{territory}')->group(function () {
        Route::get('representatives', [TerritoryController::class, 'representatives']);
        Route::get('customers', [TerritoryController::class, 'customers']);
        Route::get('statistics', [TerritoryController::class, 'statistics']);
        Route::post('assign-representative', [TerritoryController::class, 'assignRepresentative']);
        Route::delete('remove-representative/{salesRep}', [TerritoryController::class, 'removeRepresentative']);
    });

    // Customer Visits Management
    Route::apiResource('visits', VisitController::class);
    
    // Visit Actions
    Route::prefix('visits/{visit}')->group(function () {
        Route::post('check-in', [VisitController::class, 'checkIn']);
        Route::post('check-out', [VisitController::class, 'checkOut']);
        Route::post('photos', [VisitController::class, 'uploadPhotos']);
    });
    
    // Visit Queries
    Route::get('visits-today', [VisitController::class, 'today']);
    Route::get('visits-upcoming', [VisitController::class, 'upcoming']);
    Route::post('visits-sync', [VisitController::class, 'sync']);
    Route::get('visits-statistics', [VisitController::class, 'statistics']);

    // Tasks Management
    Route::apiResource('tasks', TaskController::class);
    Route::prefix('tasks/{task}')->group(function () {
        Route::post('start', [TaskController::class, 'start']);
        Route::post('complete', [TaskController::class, 'complete']);
        Route::post('cancel', [TaskController::class, 'cancel']);
    });
    
    // Task Queries
    Route::get('my-tasks', [TaskController::class, 'myTasks']);
    Route::get('tasks-today', [TaskController::class, 'today']);
    Route::get('tasks-overdue', [TaskController::class, 'overdue']);

    // Reports and Analytics
    Route::prefix('reports')->group(function () {
        // Performance Reports
        Route::get('performance/summary', [ReportController::class, 'performanceSummary']);
        Route::get('performance/detailed', [ReportController::class, 'performanceDetailed']);
        Route::get('performance/comparison', [ReportController::class, 'performanceComparison']);
        
        // Visit Reports
        Route::get('visits/summary', [ReportController::class, 'visitsSummary']);
        Route::get('visits/detailed', [ReportController::class, 'visitsDetailed']);
        Route::get('visits/map', [ReportController::class, 'visitsMap']);
        
        // Sales Reports
        Route::get('sales/summary', [ReportController::class, 'salesSummary']);
        Route::get('sales/by-rep', [ReportController::class, 'salesByRep']);
        Route::get('sales/by-territory', [ReportController::class, 'salesByTerritory']);
        Route::get('sales/trends', [ReportController::class, 'salesTrends']);
        
        // Collection Reports
        Route::get('collections/summary', [ReportController::class, 'collectionsSummary']);
        Route::get('collections/detailed', [ReportController::class, 'collectionsDetailed']);
        Route::get('collections/outstanding', [ReportController::class, 'outstandingCollections']);
        
        // Territory Reports
        Route::get('territories/coverage', [ReportController::class, 'territoryCoverage']);
        Route::get('territories/performance', [ReportController::class, 'territoryPerformance']);
        
        // Export Routes
        Route::post('export/excel', [ReportController::class, 'exportExcel']);
        Route::post('export/pdf', [ReportController::class, 'exportPdf']);
    });

    // Dashboard Data Routes
    Route::prefix('dashboard')->group(function () {
        Route::get('overview', [ReportController::class, 'dashboardOverview']);
        Route::get('kpis', [ReportController::class, 'dashboardKPIs']);
        Route::get('charts', [ReportController::class, 'dashboardCharts']);
        Route::get('recent-activities', [ReportController::class, 'recentActivities']);
        Route::get('alerts', [ReportController::class, 'alerts']);
    });

    // Mobile App Specific Routes
    Route::prefix('mobile')->group(function () {
        // Sync Routes
        Route::post('sync/customers', [MobileAuthController::class, 'syncCustomers']);
        Route::post('sync/products', [MobileAuthController::class, 'syncProducts']);
        Route::post('sync/visits', [VisitController::class, 'sync']);
        Route::post('sync/tasks', [TaskController::class, 'sync']);
        Route::post('sync/orders', [MobileAuthController::class, 'syncOrders']);
        Route::post('sync/payments', [MobileAuthController::class, 'syncPayments']);
        
        // Offline Data Routes
        Route::get('offline-data', [MobileAuthController::class, 'getOfflineData']);
        Route::get('sync-status', [MobileAuthController::class, 'getSyncStatus']);
        
        // Location Tracking
        Route::post('location/track', [SalesRepController::class, 'updateLocation']);
        Route::get('location/history', [ReportController::class, 'locationHistory']);
        
        // Quick Actions
        Route::post('quick-visit', [VisitController::class, 'quickVisit']);
        Route::post('quick-order', [MobileAuthController::class, 'quickOrder']);
        Route::post('quick-payment', [MobileAuthController::class, 'quickPayment']);
        
        // Notifications
        Route::get('notifications', [MobileAuthController::class, 'getNotifications']);
        Route::post('notifications/{id}/read', [MobileAuthController::class, 'markNotificationRead']);
        Route::post('notifications/read-all', [MobileAuthController::class, 'markAllNotificationsRead']);
    });

    // Settings and Configuration
    Route::prefix('settings')->group(function () {
        Route::get('app-config', [MobileAuthController::class, 'getAppConfig']);
        Route::get('territories/list', [TerritoryController::class, 'list']);
        Route::get('customers/list', [MobileAuthController::class, 'getCustomersList']);
        Route::get('products/list', [MobileAuthController::class, 'getProductsList']);
        Route::get('payment-methods', [MobileAuthController::class, 'getPaymentMethods']);
    });

    // File Upload Routes
    Route::prefix('uploads')->group(function () {
        Route::post('visit-photos', [VisitController::class, 'uploadPhotos']);
        Route::post('task-attachments', [TaskController::class, 'uploadAttachments']);
        Route::post('profile-photo', [MobileAuthController::class, 'uploadProfilePhoto']);
        Route::post('documents', [MobileAuthController::class, 'uploadDocuments']);
    });

    // Utility Routes
    Route::prefix('utils')->group(function () {
        Route::post('geocode', [MobileAuthController::class, 'geocodeAddress']);
        Route::post('reverse-geocode', [MobileAuthController::class, 'reverseGeocode']);
        Route::get('nearby-customers', [MobileAuthController::class, 'getNearbyCustomers']);
        Route::post('calculate-distance', [MobileAuthController::class, 'calculateDistance']);
        Route::post('optimize-route', [MobileAuthController::class, 'optimizeRoute']);
    });
});

// Public Routes (no authentication required)
Route::prefix('public')->group(function () {
    Route::get('app-version', [MobileAuthController::class, 'getAppVersion']);
    Route::get('server-status', [MobileAuthController::class, 'getServerStatus']);
    Route::post('device-registration', [MobileAuthController::class, 'registerDevice']);
});
