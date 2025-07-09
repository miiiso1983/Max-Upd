<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Test API Routes (No Authentication Required)
|--------------------------------------------------------------------------
|
| These routes are for testing purposes only and should be removed in production
|
*/

// Test endpoint to check if Sales Reps module is working
Route::get('/test/sales-reps', function () {
    try {
        // Check if the model exists and can be loaded
        $salesRepsCount = \App\Modules\SalesReps\Models\SalesRepresentative::count();
        $territoriesCount = \App\Modules\SalesReps\Models\Territory::count();
        
        return response()->json([
            'success' => true,
            'message' => 'Sales Representatives module is working!',
            'data' => [
                'sales_representatives_count' => $salesRepsCount,
                'territories_count' => $territoriesCount,
                'module_status' => 'active',
                'database_connection' => 'working',
                'timestamp' => now()->toISOString(),
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error testing Sales Representatives module',
            'error' => $e->getMessage(),
            'timestamp' => now()->toISOString(),
        ], 500);
    }
});

// Test endpoint to get sample sales rep data (limited)
Route::get('/test/sales-reps/sample', function () {
    try {
        $salesReps = \App\Modules\SalesReps\Models\SalesRepresentative::with(['territories', 'user'])
            ->limit(3)
            ->get()
            ->map(function ($rep) {
                return [
                    'id' => $rep->id,
                    'name' => $rep->name,
                    'name_ar' => $rep->name_ar,
                    'email' => $rep->email,
                    'phone' => $rep->phone,
                    'status' => $rep->status,
                    'employee_code' => $rep->employee_code,
                    'governorate' => $rep->governorate,
                    'city' => $rep->city,
                    'monthly_target' => $rep->monthly_target,
                    'territories_count' => $rep->territories->count(),
                    'created_at' => $rep->created_at,
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Sample sales representatives data',
            'data' => $salesReps,
            'total_count' => \App\Modules\SalesReps\Models\SalesRepresentative::count(),
            'timestamp' => now()->toISOString(),
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error retrieving sample data',
            'error' => $e->getMessage(),
            'timestamp' => now()->toISOString(),
        ], 500);
    }
});

// Test endpoint to check territories
Route::get('/test/territories', function () {
    try {
        $territories = \App\Modules\SalesReps\Models\Territory::limit(5)
            ->get()
            ->map(function ($territory) {
                return [
                    'id' => $territory->id,
                    'name' => $territory->name,
                    'name_ar' => $territory->name_ar,
                    'code' => $territory->code,
                    'governorate' => $territory->governorate,
                    'cities' => $territory->cities,
                    'type' => $territory->type,
                    'estimated_customers' => $territory->estimated_customers,
                    'is_active' => $territory->is_active,
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Sample territories data',
            'data' => $territories,
            'total_count' => \App\Modules\SalesReps\Models\Territory::count(),
            'timestamp' => now()->toISOString(),
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error retrieving territories data',
            'error' => $e->getMessage(),
            'timestamp' => now()->toISOString(),
        ], 500);
    }
});

// Test endpoint for database tables check
Route::get('/test/database-tables', function () {
    try {
        $tables = [
            'sales_representatives',
            'territories', 
            'rep_territory_assignments',
            'rep_customer_assignments',
            'customer_visits',
            'rep_tasks',
            'rep_performance_metrics',
            'rep_location_tracking'
        ];
        
        $tableStatus = [];
        
        foreach ($tables as $table) {
            try {
                $count = \DB::table($table)->count();
                $tableStatus[$table] = [
                    'exists' => true,
                    'record_count' => $count,
                    'status' => 'OK'
                ];
            } catch (\Exception $e) {
                $tableStatus[$table] = [
                    'exists' => false,
                    'record_count' => 0,
                    'status' => 'ERROR',
                    'error' => $e->getMessage()
                ];
            }
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Database tables status check',
            'data' => $tableStatus,
            'timestamp' => now()->toISOString(),
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error checking database tables',
            'error' => $e->getMessage(),
            'timestamp' => now()->toISOString(),
        ], 500);
    }
});

// Test endpoint for API routes check
Route::get('/test/api-routes', function () {
    $routes = [
        'GET /api/sales-reps' => 'List all sales representatives',
        'POST /api/sales-reps' => 'Create new sales representative',
        'GET /api/sales-reps/{id}' => 'Get specific sales representative',
        'PUT /api/sales-reps/{id}' => 'Update sales representative',
        'DELETE /api/sales-reps/{id}' => 'Delete sales representative',
        'GET /api/visits' => 'List visits',
        'POST /api/visits' => 'Create new visit',
        'GET /api/my-tasks' => 'Get assigned tasks',
        'POST /api/mobile/login' => 'Mobile app login',
        'GET /api/mobile/profile' => 'Get user profile',
    ];
    
    return response()->json([
        'success' => true,
        'message' => 'Available API routes for Sales Representatives module',
        'data' => $routes,
        'note' => 'Most routes require authentication with Bearer token',
        'test_routes' => [
            'GET /api/test/sales-reps' => 'Test sales reps module (no auth)',
            'GET /api/test/sales-reps/sample' => 'Get sample data (no auth)',
            'GET /api/test/territories' => 'Test territories (no auth)',
            'GET /api/test/database-tables' => 'Check database tables (no auth)',
        ],
        'timestamp' => now()->toISOString(),
    ]);
});
