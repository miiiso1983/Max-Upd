<?php

/**
 * MaxCon SaaS - Enhanced Index File with PHP Function Fixes
 */

// Enhanced error handling for production
ini_set("display_errors", 0);
error_reporting(0);

// Define missing functions if they don't exist (Cloudways compatibility)
if (!function_exists('highlight_file')) {
    function highlight_file($filename, $return = false) {
        if (!file_exists($filename)) {
            return false;
        }
        $content = file_get_contents($filename);
        $highlighted = '<pre style="background:#f8f9fa;padding:10px;border-radius:5px;overflow:auto;">' . htmlspecialchars($content) . '</pre>';

        if ($return) {
            return $highlighted;
        } else {
            echo $highlighted;
            return true;
        }
    }
}

if (!function_exists('highlight_string')) {
    function highlight_string($str, $return = false) {
        $highlighted = '<pre style="background:#f8f9fa;padding:10px;border-radius:5px;overflow:auto;">' . htmlspecialchars($str) . '</pre>';

        if ($return) {
            return $highlighted;
        } else {
            echo $highlighted;
            return true;
        }
    }
}

// Set error handlers
set_error_handler(function($severity, $message, $file, $line) {
    error_log("PHP Error: $message in $file on line $line");
    return true;
});

set_exception_handler(function($exception) {
    error_log("Uncaught Exception: " . $exception->getMessage());

    // Check if it's a highlight_file related error
    if (strpos($exception->getMessage(), 'highlight_file') !== false) {
        error_log("highlight_file error detected, redirecting to bypass page");
    }

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

    // Check for critical PHP functions
    $required_functions = ['token_get_all', 'mb_strlen', 'json_encode'];
    foreach ($required_functions as $func) {
        if (!function_exists($func)) {
            throw new Exception("Required PHP function '$func' is not available");
        }
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
