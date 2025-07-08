<?php

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

// Health check route
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'MaxCon SaaS API is working!',
        'timestamp' => now(),
        'php_version' => PHP_VERSION,
        'laravel_version' => app()->version(),
        'environment' => app()->environment()
    ]);
});

// Test route
Route::get('/test', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'API Test successful',
        'data' => [
            'server_time' => now(),
            'timezone' => config('app.timezone'),
            'locale' => config('app.locale'),
            'database' => [
                'connection' => config('database.default'),
                'host' => config('database.connections.mysql.host'),
                'database' => config('database.connections.mysql.database')
            ]
        ]
    ]);
});

// User route (requires authentication)
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Basic auth routes (to be implemented later)
Route::prefix('auth')->group(function () {
    Route::post('/login', function () {
        return response()->json(['message' => 'Login endpoint - to be implemented']);
    });
    
    Route::post('/register', function () {
        return response()->json(['message' => 'Register endpoint - to be implemented']);
    });
    
    Route::middleware('auth:sanctum')->post('/logout', function () {
        return response()->json(['message' => 'Logout endpoint - to be implemented']);
    });
});

// Basic tenant routes (to be implemented later)
Route::prefix('tenant')->group(function () {
    Route::get('/info', function () {
        return response()->json(['message' => 'Tenant info - to be implemented']);
    });
});

// Basic dashboard route
Route::middleware('auth:sanctum')->get('/dashboard', function () {
    return response()->json([
        'message' => 'Dashboard data',
        'user' => auth()->user(),
        'stats' => [
            'total_users' => 0,
            'total_sales' => 0,
            'total_products' => 0,
            'total_customers' => 0
        ]
    ]);
});
