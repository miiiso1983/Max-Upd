<?php

/**
 * MaxCon SaaS - Optimized Index File
 */

// Error handling for production
ini_set("display_errors", 0);
error_reporting(0);

// Set error handlers
set_error_handler(function($severity, $message, $file, $line) {
    error_log("PHP Error: $message in $file on line $line");
    return true;
});

set_exception_handler(function($exception) {
    error_log("Uncaught Exception: " . $exception->getMessage());
    
    // Show maintenance page
    include __DIR__ . "/index.bypass.php";
    exit;
});

try {
    // Check requirements
    if (!file_exists(__DIR__ . "/../.env")) {
        throw new Exception(".env file not found");
    }
    
    if (!file_exists(__DIR__ . "/../vendor/autoload.php")) {
        throw new Exception("Composer dependencies not installed");
    }
    
    // Load Laravel
    require_once __DIR__ . "/../vendor/autoload.php";
    
    // Bootstrap Laravel
    $app = require_once __DIR__ . "/../bootstrap/app.php";
    
    // Handle the request
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    
    $request = Illuminate\Http\Request::capture();
    $response = $kernel->handle($request);
    
    $response->send();
    
    $kernel->terminate($request, $response);
    
} catch (Exception $e) {
    error_log("Laravel Error: " . $e->getMessage());
    include __DIR__ . "/index.bypass.php";
} catch (Error $e) {
    error_log("PHP Fatal Error: " . $e->getMessage());
    include __DIR__ . "/index.bypass.php";
}
