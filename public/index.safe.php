<?php

/**
 * Safe Index File for MaxCon SaaS
 * Use this if the main index.php is causing issues
 */

// Disable error display for production
ini_set('display_errors', 0);
error_reporting(0);

// Set error handler
set_error_handler(function($severity, $message, $file, $line) {
    error_log("PHP Error: $message in $file on line $line");
    return true;
});

set_exception_handler(function($exception) {
    error_log("Uncaught Exception: " . $exception->getMessage());
    
    // Show maintenance page
    http_response_code(503);
    echo '<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MaxCon SaaS - قيد الصيانة</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 40px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; margin-bottom: 20px; }
        p { color: #666; line-height: 1.6; }
        .logo { font-size: 2em; color: #007cba; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">MaxCon SaaS</div>
        <h1>النظام قيد الصيانة</h1>
        <p>نعتذر عن الإزعاج. النظام قيد التحديث والصيانة حالياً.</p>
        <p>يرجى المحاولة مرة أخرى خلال بضع دقائق.</p>
        <p><strong>MaxCon Solutions</strong> - حلول إدارة الموارد المؤسسية</p>
    </div>
</body>
</html>';
    exit;
});

try {
    // Check if .env exists
    if (!file_exists(__DIR__ . '/../.env')) {
        throw new Exception('.env file not found');
    }

    // Check if vendor exists
    if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
        throw new Exception('Composer dependencies not installed');
    }

    // Load Laravel
    require_once __DIR__ . '/../vendor/autoload.php';

    // Bootstrap Laravel
    $app = require_once __DIR__ . '/../bootstrap/app.php';

    // Handle the request
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

    $request = Illuminate\Http\Request::capture();
    $response = $kernel->handle($request);

    $response->send();

    $kernel->terminate($request, $response);

} catch (Exception $e) {
    // Log the error
    error_log("Laravel Bootstrap Error: " . $e->getMessage());
    
    // Show maintenance page
    http_response_code(503);
    echo '<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MaxCon SaaS - قيد الإعداد</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 40px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; margin-bottom: 20px; }
        p { color: #666; line-height: 1.6; }
        .logo { font-size: 2em; color: #007cba; margin-bottom: 20px; }
        .error { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .steps { text-align: right; background: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0; }
        .steps ol { margin: 0; padding-right: 20px; }
        .steps li { margin: 10px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">MaxCon SaaS</div>
        <h1>النظام قيد الإعداد</h1>
        <p>يتم حالياً إعداد وتكوين النظام للعمل على الخادم.</p>
        
        <div class="error">
            <strong>خطأ تقني:</strong> ' . htmlspecialchars($e->getMessage()) . '
        </div>
        
        <div class="steps">
            <h3>خطوات الإصلاح:</h3>
            <ol>
                <li>تشغيل: <code>php emergency-fix.php</code></li>
                <li>تشغيل: <code>composer install --no-dev</code></li>
                <li>تحديث إعدادات قاعدة البيانات في .env</li>
                <li>تشغيل: <code>php artisan migrate --force</code></li>
            </ol>
        </div>
        
        <p><strong>MaxCon Solutions</strong> - حلول إدارة الموارد المؤسسية</p>
        <p><small>للدعم الفني: support@maxcon.com</small></p>
    </div>
</body>
</html>';
} catch (Error $e) {
    // Handle fatal errors
    error_log("PHP Fatal Error: " . $e->getMessage());
    
    http_response_code(500);
    echo '<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MaxCon SaaS - خطأ في النظام</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 40px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #dc3545; margin-bottom: 20px; }
        p { color: #666; line-height: 1.6; }
        .logo { font-size: 2em; color: #007cba; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">MaxCon SaaS</div>
        <h1>خطأ في النظام</h1>
        <p>حدث خطأ غير متوقع في النظام.</p>
        <p>يرجى الاتصال بالدعم الفني لحل هذه المشكلة.</p>
        <p><strong>MaxCon Solutions</strong></p>
        <p><small>للدعم الفني: support@maxcon.com</small></p>
    </div>
</body>
</html>';
}
