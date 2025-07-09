<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\SuperAdmin\DashboardController;
use App\Http\Controllers\SuperAdmin\TenantController;
use App\Modules\Inventory\Controllers\InventoryController;
use App\Modules\Inventory\Controllers\ProductController;
use App\Modules\Sales\Controllers\SalesController;
use App\Modules\Sales\Controllers\CustomerController;
use App\Modules\Sales\Controllers\SalesOrderController;
use App\Modules\Sales\Controllers\InvoiceController;
use App\Modules\Sales\Controllers\PaymentController;
use App\Modules\Suppliers\Controllers\SupplierController;
use App\Modules\Suppliers\Controllers\PurchaseOrderController;
use App\Modules\HR\Controllers\EmployeeController;
use App\Modules\HR\Controllers\DepartmentController;
use App\Modules\HR\Controllers\AttendanceController;
use App\Modules\Accounting\Controllers\AccountController;
use App\Modules\Accounting\Controllers\TransactionController;
use App\Modules\Accounting\Controllers\InvoiceController as AccountingInvoiceController;
use App\Modules\Accounting\Controllers\PaymentController as AccountingPaymentController;
use App\Modules\Reports\Controllers\ReportController;
use App\Modules\Reports\Controllers\ReportsController;
use App\Modules\Reports\Controllers\DashboardController as ReportsDashboardController;
use App\Modules\CRM\Controllers\LeadController;
use App\Modules\CRM\Controllers\OpportunityController;
use App\Modules\CRM\Controllers\CommunicationController;
use App\Modules\Documents\Controllers\DocumentController;
use App\Modules\Documents\Controllers\DocumentCategoryController;
use App\Modules\Documents\Controllers\DocumentFolderController;
use App\Modules\Inventory\Controllers\AdvancedInventoryController;
use App\Modules\BusinessIntelligence\Controllers\BusinessIntelligenceController;
use App\Modules\DocumentManagement\Controllers\DocumentManagementController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);
Route::get('/tenant/info', [TenantController::class, 'getTenantInfo']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/user', [AuthController::class, 'user']);
    Route::get('/user', function (Request $request) {
        return $request->user()->load(['roles', 'permissions', 'tenant']);
    });
    Route::put('/auth/profile', [AuthController::class, 'updateProfile']);
    Route::put('/auth/password', [AuthController::class, 'updatePassword']);
});

// Super Admin routes
Route::middleware(['auth:sanctum', 'ensure-super-admin'])->prefix('super-admin')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/dashboard/performance', [DashboardController::class, 'tenantPerformance']);
    Route::get('/dashboard/alerts', [DashboardController::class, 'alerts']);
    
    // Tenant management
    Route::apiResource('tenants', TenantController::class);
    Route::post('/tenants/{tenant}/toggle-status', [TenantController::class, 'toggleStatus']);
    Route::post('/tenants/{tenant}/extend-license', [TenantController::class, 'extendLicense']);
    Route::get('/statistics', [TenantController::class, 'statistics']);

    // Domain management
    Route::post('/tenants/generate-domain', [TenantController::class, 'generateDomain']);
    Route::post('/tenants/check-domain', [TenantController::class, 'checkDomain']);
});

// Tenant routes (require tenant context)
Route::middleware(['auth:sanctum', 'ensure-tenant-access', 'check-license'])->prefix('tenant')->group(function () {
    // Tenant dashboard
    Route::get('/dashboard', function () {
        return response()->json(['message' => 'Tenant dashboard - to be implemented']);
    });
    
    // User management within tenant
    Route::get('/users', function () {
        return response()->json(['message' => 'Tenant users - to be implemented']);
    });
    
    // Inventory Module
    Route::prefix('inventory')->group(function () {
        // Dashboard and overview
        Route::get('/dashboard', [InventoryController::class, 'dashboard']);
        Route::get('/products', [InventoryController::class, 'products']);
        Route::get('/stock-levels', [InventoryController::class, 'stockLevels']);
        Route::get('/stock-movements', [InventoryController::class, 'stockMovements']);
        Route::get('/expiring-products', [InventoryController::class, 'expiringProducts']);
        Route::get('/alerts', [InventoryController::class, 'alerts']);

        // Product management
        Route::get('/products/barcode', [ProductController::class, 'getByBarcode']);
        Route::post('/products/bulk-update', [ProductController::class, 'bulkUpdate']);
        Route::post('/products/export', [ProductController::class, 'export']);
        Route::post('/products/import', [ProductController::class, 'import']);
        Route::get('/products/{product}/stock-history', [ProductController::class, 'stockHistory']);
        Route::apiResource('products', ProductController::class);
    });
    
    // Sales Module
    Route::prefix('sales')->group(function () {
        // Dashboard and overview
        Route::get('/dashboard', [SalesController::class, 'dashboard']);
        Route::get('/overview', [SalesController::class, 'overview']);
        Route::get('/reports', [SalesController::class, 'reports']);
        Route::get('/alerts', [SalesController::class, 'alerts']);

        // Customer management
        Route::get('/customers/export', [CustomerController::class, 'export']);
        Route::post('/customers/import', [CustomerController::class, 'import']);
        Route::post('/customers/bulk-update', [CustomerController::class, 'bulkUpdate']);
        Route::get('/customers/{customer}/statistics', [CustomerController::class, 'statistics']);
        Route::apiResource('customers', CustomerController::class);

        // Sales Order management
        Route::post('/orders/{salesOrder}/items', [SalesOrderController::class, 'addItem']);
        Route::put('/orders/{salesOrder}/items/{item}', [SalesOrderController::class, 'updateItem']);
        Route::delete('/orders/{salesOrder}/items/{item}', [SalesOrderController::class, 'removeItem']);
        Route::post('/orders/{salesOrder}/confirm', [SalesOrderController::class, 'confirm']);
        Route::post('/orders/{salesOrder}/process', [SalesOrderController::class, 'process']);
        Route::post('/orders/{salesOrder}/ship', [SalesOrderController::class, 'ship']);
        Route::post('/orders/{salesOrder}/deliver', [SalesOrderController::class, 'deliver']);
        Route::post('/orders/{salesOrder}/cancel', [SalesOrderController::class, 'cancel']);
        Route::post('/orders/{salesOrder}/invoice', [SalesOrderController::class, 'createInvoice']);
        Route::get('/orders/{salesOrder}/fulfillment', [SalesOrderController::class, 'fulfillmentStatus']);
        Route::apiResource('orders', SalesOrderController::class);

        // Invoice management
        Route::post('/invoices/{invoice}/items', [InvoiceController::class, 'addItem']);
        Route::put('/invoices/{invoice}/items/{item}', [InvoiceController::class, 'updateItem']);
        Route::delete('/invoices/{invoice}/items/{item}', [InvoiceController::class, 'removeItem']);
        Route::post('/invoices/{invoice}/send', [InvoiceController::class, 'send']);
        Route::post('/invoices/{invoice}/pay', [InvoiceController::class, 'markAsPaid']);
        Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'generatePdf']);
        Route::apiResource('invoices', InvoiceController::class);

        // Payment management
        Route::post('/payments/{payment}/confirm', [PaymentController::class, 'confirm']);
        Route::post('/payments/{payment}/cancel', [PaymentController::class, 'cancel']);
        Route::get('/payments/statistics', [PaymentController::class, 'statistics']);
        Route::get('/payments/customer/{customer}/history', [PaymentController::class, 'customerHistory']);
        Route::apiResource('payments', PaymentController::class);
    });
    
    Route::prefix('clients')->group(function () {
        Route::get('/', function () {
            return response()->json(['message' => 'Clients module - to be implemented']);
        });
    });
    
    Route::prefix('suppliers')->group(function () {
        // Supplier management
        Route::get('/suppliers/export', [SupplierController::class, 'export']);
        Route::post('/suppliers/import', [SupplierController::class, 'import']);
        Route::post('/suppliers/bulk-update', [SupplierController::class, 'bulkUpdate']);
        Route::get('/suppliers/{supplier}/statistics', [SupplierController::class, 'statistics']);
        Route::get('/suppliers/{supplier}/products', [SupplierController::class, 'products']);
        Route::post('/suppliers/{supplier}/products', [SupplierController::class, 'addProduct']);
        Route::put('/suppliers/{supplier}/products/{product}', [SupplierController::class, 'updateProduct']);
        Route::delete('/suppliers/{supplier}/products/{product}', [SupplierController::class, 'removeProduct']);
        Route::apiResource('suppliers', SupplierController::class);

        // Purchase Order management
        Route::post('/purchase-orders/{purchaseOrder}/items', [PurchaseOrderController::class, 'addItem']);
        Route::put('/purchase-orders/{purchaseOrder}/items/{item}', [PurchaseOrderController::class, 'updateItem']);
        Route::delete('/purchase-orders/{purchaseOrder}/items/{item}', [PurchaseOrderController::class, 'removeItem']);
        Route::post('/purchase-orders/{purchaseOrder}/submit', [PurchaseOrderController::class, 'submit']);
        Route::post('/purchase-orders/{purchaseOrder}/confirm', [PurchaseOrderController::class, 'confirm']);
        Route::post('/purchase-orders/{purchaseOrder}/cancel', [PurchaseOrderController::class, 'cancel']);
        Route::post('/purchase-orders/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receiveItems']);
        Route::post('/purchase-orders/{purchaseOrder}/receivings/{receiving}/quality-check', [PurchaseOrderController::class, 'qualityCheck']);
        Route::get('/purchase-orders/statistics', [PurchaseOrderController::class, 'statistics']);
        Route::get('/purchase-orders/supplier/{supplier}/history', [PurchaseOrderController::class, 'supplierHistory']);
        Route::apiResource('purchase-orders', PurchaseOrderController::class);
    });
    
    Route::prefix('accounting')->group(function () {
        // Chart of Accounts
        Route::get('/accounts/export', [AccountController::class, 'export']);
        Route::post('/accounts/import', [AccountController::class, 'import']);
        Route::get('/accounts/chart', [AccountController::class, 'chartOfAccounts']);
        Route::get('/accounts/{account}/balance-history', [AccountController::class, 'balanceHistory']);
        Route::get('/accounts/type/{type}', [AccountController::class, 'byType']);
        Route::post('/accounts/update-balances', [AccountController::class, 'updateBalances']);
        Route::apiResource('accounts', AccountController::class);

        // Transactions & Journal Entries
        Route::post('/transactions/{transaction}/post', [TransactionController::class, 'post']);
        Route::post('/transactions/{transaction}/reverse', [TransactionController::class, 'reverse']);
        Route::get('/transactions/statistics', [TransactionController::class, 'statistics']);
        Route::apiResource('transactions', TransactionController::class);

        // Invoicing
        Route::post('/invoices/{invoice}/send', [AccountingInvoiceController::class, 'send']);
        Route::post('/invoices/{invoice}/mark-paid', [AccountingInvoiceController::class, 'markAsPaid']);
        Route::post('/invoices/{invoice}/cancel', [AccountingInvoiceController::class, 'cancel']);
        Route::get('/invoices/statistics', [AccountingInvoiceController::class, 'statistics']);
        Route::apiResource('invoices', AccountingInvoiceController::class);

        // Payments
        Route::post('/payments/{payment}/complete', [AccountingPaymentController::class, 'complete']);
        Route::post('/payments/{payment}/fail', [AccountingPaymentController::class, 'fail']);
        Route::post('/payments/{payment}/cancel', [AccountingPaymentController::class, 'cancel']);
        Route::get('/payments/statistics', [AccountingPaymentController::class, 'statistics']);
        Route::get('/payments/customer/{customer}/history', [AccountingPaymentController::class, 'customerHistory']);
        Route::get('/payments/supplier/{supplier}/history', [AccountingPaymentController::class, 'supplierHistory']);
        Route::apiResource('payments', AccountingPaymentController::class);
    });
    
    Route::prefix('hr')->group(function () {
        // Department management
        Route::get('/departments/export', [DepartmentController::class, 'export']);
        Route::post('/departments/import', [DepartmentController::class, 'import']);
        Route::get('/departments/hierarchy', [DepartmentController::class, 'hierarchy']);
        Route::get('/departments/{department}/statistics', [DepartmentController::class, 'statistics']);
        Route::apiResource('departments', DepartmentController::class);

        // Employee management
        Route::get('/employees/{employee}/statistics', [EmployeeController::class, 'statistics']);
        Route::apiResource('employees', EmployeeController::class);

        // Attendance management
        Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn']);
        Route::post('/attendance/check-out', [AttendanceController::class, 'checkOut']);
        Route::get('/attendance/employee/{employee}/summary', [AttendanceController::class, 'employeeSummary']);
        Route::get('/attendance/statistics', [AttendanceController::class, 'statistics']);
        Route::apiResource('attendance', AttendanceController::class);
    });
    
    Route::prefix('medical-reps')->group(function () {
        Route::get('/', function () {
            return response()->json(['message' => 'Medical Reps module - to be implemented']);
        });
    });
    
    Route::prefix('analytics')->group(function () {
        Route::get('/', function () {
            return response()->json(['message' => 'Analytics module - to be implemented']);
        });
    });
    
    Route::prefix('reports')->group(function () {
        // Financial Reports
        Route::get('/financial/balance-sheet', [ReportController::class, 'balanceSheet']);
        Route::get('/financial/income-statement', [ReportController::class, 'incomeStatement']);
        Route::get('/financial/cash-flow-statement', [ReportController::class, 'cashFlowStatement']);
        Route::get('/financial/trial-balance', [ReportController::class, 'trialBalance']);
        Route::get('/financial/general-ledger', [ReportController::class, 'generalLedger']);
        Route::get('/financial/summary', [ReportController::class, 'financialSummary']);

        // Business Intelligence
        Route::get('/dashboard/executive', [ReportController::class, 'executiveDashboard']);
        Route::get('/analytics/sales', [ReportController::class, 'salesAnalytics']);

        // Custom Reports
        Route::post('/custom', [ReportController::class, 'customReport']);
        Route::post('/export', [ReportController::class, 'exportReport']);
        Route::post('/schedule', [ReportController::class, 'scheduleReport']);
        Route::get('/available', [ReportController::class, 'availableReports']);
    });

    Route::prefix('dashboard')->group(function () {
        // Dashboard Overview
        Route::get('/overview', [ReportsDashboardController::class, 'overview']);
        Route::get('/financial', [ReportsDashboardController::class, 'financial']);
        Route::get('/sales', [ReportsDashboardController::class, 'sales']);
        Route::get('/inventory', [ReportsDashboardController::class, 'inventory']);
        Route::get('/hr', [ReportsDashboardController::class, 'hr']);
    });

    Route::prefix('crm')->group(function () {
        // Lead Management
        Route::post('/leads/{lead}/convert-to-customer', [LeadController::class, 'convertToCustomer']);
        Route::post('/leads/{lead}/update-status', [LeadController::class, 'updateStatus']);
        Route::post('/leads/{lead}/schedule-follow-up', [LeadController::class, 'scheduleFollowUp']);
        Route::get('/leads/{lead}/activities', [LeadController::class, 'activities']);
        Route::post('/leads/{lead}/activities', [LeadController::class, 'addActivity']);
        Route::get('/leads/statistics', [LeadController::class, 'statistics']);
        Route::get('/leads/options', [LeadController::class, 'options']);
        Route::apiResource('leads', LeadController::class);

        // Opportunity Management
        Route::post('/opportunities/{opportunity}/move-to-stage', [OpportunityController::class, 'moveToStage']);
        Route::post('/opportunities/{opportunity}/mark-as-won', [OpportunityController::class, 'markAsWon']);
        Route::post('/opportunities/{opportunity}/mark-as-lost', [OpportunityController::class, 'markAsLost']);
        Route::post('/opportunities/{opportunity}/update-amount', [OpportunityController::class, 'updateAmount']);
        Route::post('/opportunities/{opportunity}/update-close-date', [OpportunityController::class, 'updateCloseDate']);
        Route::get('/opportunities/{opportunity}/activities', [OpportunityController::class, 'activities']);
        Route::post('/opportunities/{opportunity}/activities', [OpportunityController::class, 'addActivity']);
        Route::get('/opportunities/pipeline', [OpportunityController::class, 'pipeline']);
        Route::get('/opportunities/statistics', [OpportunityController::class, 'statistics']);
        Route::get('/opportunities/options', [OpportunityController::class, 'options']);
        Route::apiResource('opportunities', OpportunityController::class);

        // Communication Management
        Route::post('/communications/{communication}/mark-as-completed', [CommunicationController::class, 'markAsCompleted']);
        Route::post('/communications/{communication}/mark-as-failed', [CommunicationController::class, 'markAsFailed']);
        Route::post('/communications/{communication}/schedule', [CommunicationController::class, 'schedule']);
        Route::post('/communications/{communication}/send', [CommunicationController::class, 'send']);
        Route::post('/communications/{communication}/upload-attachment', [CommunicationController::class, 'uploadAttachment']);
        Route::delete('/communications/{communication}/remove-attachment', [CommunicationController::class, 'removeAttachment']);
        Route::get('/communications/by-related', [CommunicationController::class, 'byRelated']);
        Route::get('/communications/overdue', [CommunicationController::class, 'overdue']);
        Route::get('/communications/statistics', [CommunicationController::class, 'statistics']);
        Route::get('/communications/options', [CommunicationController::class, 'options']);
        Route::apiResource('communications', CommunicationController::class);
    });

    Route::prefix('documents')->group(function () {
        // Document Management
        Route::get('/documents/download/{document}', [DocumentController::class, 'download'])->name('documents.download');
        Route::get('/documents/preview/{document}', [DocumentController::class, 'preview'])->name('documents.preview');
        Route::post('/documents/{document}/upload-version', [DocumentController::class, 'uploadVersion']);
        Route::post('/documents/{document}/approve', [DocumentController::class, 'approve']);
        Route::post('/documents/{document}/reject', [DocumentController::class, 'reject']);
        Route::post('/documents/{document}/archive', [DocumentController::class, 'archive']);
        Route::post('/documents/{document}/grant-permission', [DocumentController::class, 'grantPermission']);
        Route::delete('/documents/{document}/revoke-permission', [DocumentController::class, 'revokePermission']);
        Route::get('/documents/{document}/activities', [DocumentController::class, 'activities']);
        Route::get('/documents/statistics', [DocumentController::class, 'statistics']);
        Route::get('/documents/options', [DocumentController::class, 'options']);
        Route::apiResource('documents', DocumentController::class);

        // Document Categories
        Route::get('/categories/tree', [DocumentCategoryController::class, 'tree']);
        Route::get('/categories/{documentCategory}/statistics', [DocumentCategoryController::class, 'statistics']);
        Route::post('/categories/reorder', [DocumentCategoryController::class, 'reorder']);
        Route::apiResource('categories', DocumentCategoryController::class, ['parameters' => ['categories' => 'documentCategory']]);

        // Document Folders
        Route::get('/folders/tree', [DocumentFolderController::class, 'tree']);
        Route::get('/folders/{documentFolder}/contents', [DocumentFolderController::class, 'contents']);
        Route::get('/folders/{documentFolder}/statistics', [DocumentFolderController::class, 'statistics']);
        Route::post('/folders/{documentFolder}/grant-permission', [DocumentFolderController::class, 'grantPermission']);
        Route::delete('/folders/{documentFolder}/revoke-permission', [DocumentFolderController::class, 'revokePermission']);
        Route::post('/folders/reorder', [DocumentFolderController::class, 'reorder']);
        Route::apiResource('folders', DocumentFolderController::class, ['parameters' => ['folders' => 'documentFolder']]);
    });

    Route::prefix('advanced-inventory')->group(function () {
        // Dashboard and Analytics
        Route::get('/dashboard', [AdvancedInventoryController::class, 'dashboard']);
        Route::get('/alerts', [AdvancedInventoryController::class, 'getStockAlerts']);

        // Barcode Scanning
        Route::post('/scan-barcode', [AdvancedInventoryController::class, 'scanBarcode']);

        // Stock Management
        Route::post('/adjust-stock', [AdvancedInventoryController::class, 'adjustStock']);
        Route::post('/transfer-stock', [AdvancedInventoryController::class, 'transferStock']);

        // Stock Counting
        Route::post('/generate-cycle-counts', [AdvancedInventoryController::class, 'generateCycleCounts']);
        Route::put('/stock-counts/{stockCount}', [AdvancedInventoryController::class, 'updateStockCount']);
    });

    Route::prefix('business-intelligence')->group(function () {
        // Executive Dashboards
        Route::get('/executive-dashboard', [BusinessIntelligenceController::class, 'executiveDashboard']);
        Route::get('/sales-dashboard', [BusinessIntelligenceController::class, 'salesDashboard']);
        Route::get('/inventory-dashboard', [BusinessIntelligenceController::class, 'inventoryDashboard']);
        Route::get('/financial-dashboard', [BusinessIntelligenceController::class, 'financialDashboard']);

        // KPIs
        Route::get('/kpis', [BusinessIntelligenceController::class, 'getKPIs']);
        Route::post('/kpis/calculate', [BusinessIntelligenceController::class, 'calculateKPIs']);

        // Analytics
        Route::get('/analytics', [BusinessIntelligenceController::class, 'getAnalytics']);

        // Export
        Route::post('/export-report', [BusinessIntelligenceController::class, 'exportReport']);

        // Reports
        Route::get('/reports', [BusinessIntelligenceController::class, 'getReports']);
        Route::post('/reports/{report}/execute', [BusinessIntelligenceController::class, 'executeReport']);
    });

    Route::prefix('document-management')->group(function () {
        // Document CRUD
        Route::get('/documents', [DocumentManagementController::class, 'index']);
        Route::post('/documents', [DocumentManagementController::class, 'store']);
        Route::get('/documents/{document}', [DocumentManagementController::class, 'show']);
        Route::put('/documents/{document}', [DocumentManagementController::class, 'update']);
        Route::delete('/documents/{document}', [DocumentManagementController::class, 'destroy']);

        // Document Actions
        Route::get('/documents/{document}/download', [DocumentManagementController::class, 'download']);
        Route::post('/documents/{document}/versions', [DocumentManagementController::class, 'createVersion']);
        Route::post('/documents/{document}/toggle-lock', [DocumentManagementController::class, 'toggleLock']);

        // Statistics
        Route::get('/statistics', [DocumentManagementController::class, 'getStatistics']);
    });


});

// Advanced Inventory Routes (Public for testing)
Route::prefix('advanced-inventory')->group(function () {
    // Dashboard and Analytics
    Route::get('/dashboard', [AdvancedInventoryController::class, 'dashboard']);
    Route::get('/alerts', [AdvancedInventoryController::class, 'getStockAlerts']);

    // Barcode Scanning
    Route::post('/scan-barcode', [AdvancedInventoryController::class, 'scanBarcode']);

    // Stock Management
    Route::post('/adjust-stock', [AdvancedInventoryController::class, 'adjustStock']);
    Route::post('/transfer-stock', [AdvancedInventoryController::class, 'transferStock']);

    // Stock Counting
    Route::post('/generate-cycle-counts', [AdvancedInventoryController::class, 'generateCycleCounts']);
    Route::put('/stock-counts/{stockCount}', [AdvancedInventoryController::class, 'updateStockCount']);
});

// Business Intelligence Routes (Public for testing)
Route::prefix('business-intelligence')->group(function () {
    // Executive Dashboards
    Route::get('/executive-dashboard', [BusinessIntelligenceController::class, 'executiveDashboard']);
    Route::get('/sales-dashboard', [BusinessIntelligenceController::class, 'salesDashboard']);
    Route::get('/inventory-dashboard', [BusinessIntelligenceController::class, 'inventoryDashboard']);
    Route::get('/financial-dashboard', [BusinessIntelligenceController::class, 'financialDashboard']);

    // KPIs
    Route::get('/kpis', [BusinessIntelligenceController::class, 'getKPIs']);
    Route::post('/kpis/calculate', [BusinessIntelligenceController::class, 'calculateKPIs']);

    // Export
    Route::post('/export-report', [BusinessIntelligenceController::class, 'exportReport']);

    // Analytics
    Route::get('/analytics', [BusinessIntelligenceController::class, 'getAnalytics']);

    // Reports
    Route::get('/reports', [BusinessIntelligenceController::class, 'getReports']);
    Route::post('/reports/{report}/execute', [BusinessIntelligenceController::class, 'executeReport']);
});

// Document Management Routes (Public for testing)
Route::prefix('document-management')->group(function () {
    // Document CRUD
    Route::get('/documents', [DocumentManagementController::class, 'index']);
    Route::post('/documents', [DocumentManagementController::class, 'store']);
    Route::get('/documents/{document}', [DocumentManagementController::class, 'show']);
    Route::put('/documents/{document}', [DocumentManagementController::class, 'update']);

    // Document Actions
    Route::get('/documents/{document}/download', [DocumentManagementController::class, 'download']);
    Route::post('/documents/{document}/versions', [DocumentManagementController::class, 'createVersion']);
    Route::post('/documents/{document}/toggle-lock', [DocumentManagementController::class, 'toggleLock']);

    // Statistics
    Route::get('/statistics', [DocumentManagementController::class, 'getStatistics']);
});



// Domain Management Routes (Public for testing)
Route::prefix('domain-management')->group(function () {
    Route::post('/generate-domain', [TenantController::class, 'generateDomain']);
    Route::post('/check-domain', [TenantController::class, 'checkDomain']);
});

// Dashboard API Routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index']);

    // Sales Dashboard API
    Route::prefix('sales')->group(function () {
        Route::get('/dashboard', [SalesController::class, 'dashboard']);

        // Customers API
        Route::apiResource('customers', CustomerController::class);
        Route::post('/customers/{customer}/toggle-status', [CustomerController::class, 'toggleStatus']);
        Route::get('/customers/{customer}/orders', [CustomerController::class, 'orders']);
        Route::get('/customers/{customer}/invoices', [CustomerController::class, 'invoices']);
        Route::get('/customers/{customer}/payments', [CustomerController::class, 'payments']);
        Route::get('/customers/{customer}/statistics', [CustomerController::class, 'statistics']);

        // Sales Orders API
        Route::apiResource('orders', SalesOrderController::class);
        Route::post('/orders/{order}/confirm', [SalesOrderController::class, 'confirm']);
        Route::post('/orders/{order}/cancel', [SalesOrderController::class, 'cancel']);
        Route::post('/orders/{order}/fulfill', [SalesOrderController::class, 'fulfill']);
        Route::get('/orders/{order}/items', [SalesOrderController::class, 'items']);
        Route::post('/orders/{order}/items', [SalesOrderController::class, 'addItem']);
        Route::put('/orders/{order}/items/{item}', [SalesOrderController::class, 'updateItem']);
        Route::delete('/orders/{order}/items/{item}', [SalesOrderController::class, 'removeItem']);

        // Invoices API
        Route::apiResource('invoices', InvoiceController::class);
        Route::post('/invoices/{invoice}/pay', [InvoiceController::class, 'recordPayment']);
        Route::post('/invoices/{invoice}/void', [InvoiceController::class, 'void']);
        Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'downloadPdf']);
        Route::post('/invoices/{invoice}/send', [InvoiceController::class, 'sendToCustomer']);
        Route::get('/invoices/{invoice}/items', [InvoiceController::class, 'items']);

        // Payments API
        Route::apiResource('payments', PaymentController::class);
        Route::post('/payments/{payment}/refund', [PaymentController::class, 'refund']);
        Route::get('/payments/methods', [PaymentController::class, 'paymentMethods']);

        // Sales Analytics API
        Route::get('/analytics/summary', [SalesController::class, 'analyticsSummary']);
        Route::get('/analytics/trends', [SalesController::class, 'salesTrends']);
        Route::get('/analytics/top-products', [SalesController::class, 'topProducts']);
        Route::get('/analytics/top-customers', [SalesController::class, 'topCustomers']);
        Route::get('/analytics/performance', [SalesController::class, 'salesPerformance']);

        // Export API
        Route::get('/export/customers', [CustomerController::class, 'export']);
        Route::get('/export/orders', [SalesOrderController::class, 'export']);
        Route::get('/export/invoices', [InvoiceController::class, 'export']);
        Route::get('/export/payments', [PaymentController::class, 'export']);
    });

    // Inventory Dashboard API
    Route::prefix('inventory')->group(function () {
        Route::get('/dashboard', [InventoryController::class, 'dashboard']);
        Route::get('/products', [ProductController::class, 'index']);
        Route::get('/products/export', [ProductController::class, 'exportProducts']);
        Route::get('/products/low-stock', [ProductController::class, 'lowStock']);
        Route::get('/products/expiring', [ProductController::class, 'expiring']);
        Route::get('/products/low-stock/export', [ProductController::class, 'exportLowStock']);
        Route::get('/products/expiring/export', [ProductController::class, 'exportExpiring']);
        Route::get('/products/template', [\App\Http\Controllers\ProductImportController::class, 'downloadTemplate']);
        Route::post('/products/import', [\App\Http\Controllers\ProductImportController::class, 'importProducts']);
        Route::get('/products/{product}', [ProductController::class, 'show']);
        Route::delete('/products/{product}', [ProductController::class, 'destroy']);
    });

    // Reports API
    Route::prefix('reports')->group(function () {
        Route::get('/sales/summary', [ReportsController::class, 'salesSummary']);
        Route::get('/sales/detailed', [ReportsController::class, 'salesDetailed']);
        Route::get('/sales/customers', [ReportsController::class, 'customerSales']);
        Route::get('/sales/products', [ReportsController::class, 'productSales']);
        Route::get('/inventory/stock-levels', [ReportsController::class, 'stockLevels']);
        Route::get('/inventory/movements', [ReportsController::class, 'stockMovements']);
        Route::get('/inventory/valuation', [ReportsController::class, 'stockValuation']);
        Route::get('/inventory/expiring/export', [ReportsController::class, 'exportExpiringProducts']);
    });

    // Notifications API
    Route::prefix('notifications')->group(function () {
        Route::get('/', [App\Http\Controllers\NotificationController::class, 'getNotifications']);
        Route::get('/unread-count', [App\Http\Controllers\NotificationController::class, 'getUnreadCount']);
        Route::get('/recent', [App\Http\Controllers\NotificationController::class, 'getRecent']);
        Route::get('/statistics', [App\Http\Controllers\NotificationController::class, 'getStatistics']);
        Route::get('/settings', [App\Http\Controllers\NotificationController::class, 'getSettings']);
        Route::get('/export', [App\Http\Controllers\NotificationController::class, 'export']);

        Route::post('/mark-all-as-read', [App\Http\Controllers\NotificationController::class, 'markAllAsRead']);
        Route::post('/settings', [App\Http\Controllers\NotificationController::class, 'updateSettings']);
        Route::post('/send-test', [App\Http\Controllers\NotificationController::class, 'sendTest']);
        Route::post('/{notification}/mark-as-read', [App\Http\Controllers\NotificationController::class, 'markAsRead']);
        Route::post('/{notification}/mark-as-unread', [App\Http\Controllers\NotificationController::class, 'markAsUnread']);

        Route::delete('/delete-all-read', [App\Http\Controllers\NotificationController::class, 'deleteAllRead']);
        Route::delete('/{notification}', [App\Http\Controllers\NotificationController::class, 'destroy']);
    });

    // User Preferences API
    Route::prefix('preferences')->group(function () {
        Route::get('/', [App\Http\Controllers\UserPreferenceController::class, 'getPreferences']);
        Route::get('/category/{category}', [App\Http\Controllers\UserPreferenceController::class, 'getByCategory']);
        Route::get('/options', [App\Http\Controllers\UserPreferenceController::class, 'getOptions']);
        Route::get('/config/{key}', [App\Http\Controllers\UserPreferenceController::class, 'getConfig']);
        Route::get('/export', [App\Http\Controllers\UserPreferenceController::class, 'export']);
        Route::get('/theme.css', [App\Http\Controllers\UserPreferenceController::class, 'getThemeCSS']);

        Route::post('/update', [App\Http\Controllers\UserPreferenceController::class, 'update']);
        Route::post('/update-single', [App\Http\Controllers\UserPreferenceController::class, 'updateSingle']);
        Route::post('/reset', [App\Http\Controllers\UserPreferenceController::class, 'reset']);
        Route::post('/reset-all', [App\Http\Controllers\UserPreferenceController::class, 'resetAll']);
        Route::post('/toggle-sidebar', [App\Http\Controllers\UserPreferenceController::class, 'toggleSidebar']);
        Route::post('/theme', [App\Http\Controllers\UserPreferenceController::class, 'setTheme']);
        Route::post('/language', [App\Http\Controllers\UserPreferenceController::class, 'setLanguage']);
        Route::post('/dashboard-widgets', [App\Http\Controllers\UserPreferenceController::class, 'setDashboardWidgets']);
        Route::post('/import', [App\Http\Controllers\UserPreferenceController::class, 'import']);
    });
});

// Admin Security API Routes
Route::middleware(['auth:sanctum', 'role:admin|super_admin'])->prefix('admin/security')->group(function () {
    Route::get('/overview', [App\Http\Controllers\Admin\SecurityController::class, 'overview']);
    Route::get('/alerts', [App\Http\Controllers\Admin\SecurityController::class, 'alerts']);
    Route::get('/logs', [App\Http\Controllers\Admin\SecurityController::class, 'logs']);
    Route::get('/logs/{securityLog}', [App\Http\Controllers\Admin\SecurityController::class, 'show']);
    Route::get('/report', [App\Http\Controllers\Admin\SecurityController::class, 'report']);
    Route::get('/statistics', [App\Http\Controllers\Admin\SecurityController::class, 'statistics']);
    Route::get('/blocked-ips', [App\Http\Controllers\Admin\SecurityController::class, 'blockedIps']);
    Route::get('/settings', [App\Http\Controllers\Admin\SecurityController::class, 'getSettings']);

    Route::post('/clean-logs', [App\Http\Controllers\Admin\SecurityController::class, 'cleanLogs']);
    Route::post('/block-ip', [App\Http\Controllers\Admin\SecurityController::class, 'blockIp']);
    Route::post('/unblock-ip', [App\Http\Controllers\Admin\SecurityController::class, 'unblockIp']);
    Route::post('/settings', [App\Http\Controllers\Admin\SecurityController::class, 'updateSettings']);
    Route::get('/export-report', [App\Http\Controllers\Admin\SecurityController::class, 'exportReport']);
});

// Admin API Routes
Route::middleware(['auth:sanctum', 'role:admin|super_admin'])->prefix('admin')->group(function () {
    Route::get('/roles', [App\Http\Controllers\Admin\RoleController::class, 'apiIndex']);
    Route::post('/roles', [App\Http\Controllers\Admin\RoleController::class, 'apiStore']);
    Route::put('/roles/{role}', [App\Http\Controllers\Admin\RoleController::class, 'apiUpdate']);
    Route::delete('/roles/{role}', [App\Http\Controllers\Admin\RoleController::class, 'apiDestroy']);
});

// Super Admin API Routes
Route::middleware(['auth:sanctum', 'role:super_admin'])->prefix('super-admin')->group(function () {
    Route::get('/tenants', [TenantController::class, 'index']);
    Route::post('/tenants/{tenant}/toggle-status', [TenantController::class, 'toggleStatus']);
    Route::delete('/tenants/{tenant}', [TenantController::class, 'destroy']);
});

// Sales Representatives API Routes
require __DIR__.'/api_sales_reps.php';

// Test API Routes (No Authentication Required)
require __DIR__.'/api_test.php';

// Health check route
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now(),
        'version' => '1.0.0'
    ]);
});
