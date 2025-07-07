<?php

/**
 * Emergency Fix for MaxCon SaaS on Cloudways
 * Run this script to fix immediate deployment issues
 */

echo "🚨 إصلاح طارئ لـ MaxCon SaaS على Cloudways...\n\n";

// Step 1: Create .env file if missing
echo "1. فحص ملف .env...\n";
if (!file_exists('.env')) {
    if (file_exists('.env.emergency')) {
        copy('.env.emergency', '.env');
        echo "✅ تم إنشاء .env من .env.emergency\n";
    } elseif (file_exists('.env.example')) {
        copy('.env.example', '.env');
        echo "✅ تم إنشاء .env من .env.example\n";
    } else {
        // Create minimal .env
        $envContent = 'APP_NAME="MaxCon SaaS"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://your-domain.com

LOG_CHANNEL=single
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

MAIL_MAILER=log
';
        file_put_contents('.env', $envContent);
        echo "✅ تم إنشاء .env أساسي\n";
    }
} else {
    echo "✅ ملف .env موجود\n";
}

// Step 2: Generate APP_KEY
echo "\n2. توليد APP_KEY...\n";
$envContent = file_get_contents('.env');
if (strpos($envContent, 'APP_KEY=') === false || strpos($envContent, 'APP_KEY=base64:') === false || strpos($envContent, 'PLEASE_GENERATE') !== false) {
    // Generate a random key
    $key = base64_encode(random_bytes(32));
    $envContent = preg_replace('/APP_KEY=.*/', 'APP_KEY=base64:' . $key, $envContent);
    if (strpos($envContent, 'APP_KEY=base64:') === false) {
        $envContent .= "\nAPP_KEY=base64:" . $key;
    }
    file_put_contents('.env', $envContent);
    echo "✅ تم توليد APP_KEY جديد\n";
} else {
    echo "✅ APP_KEY موجود\n";
}

// Step 3: Set production environment
echo "\n3. تعيين بيئة الإنتاج...\n";
$envContent = file_get_contents('.env');
$envContent = preg_replace('/APP_ENV=.*/', 'APP_ENV=production', $envContent);
$envContent = preg_replace('/APP_DEBUG=.*/', 'APP_DEBUG=false', $envContent);
file_put_contents('.env', $envContent);
echo "✅ تم تعيين بيئة الإنتاج\n";

// Step 4: Set proper permissions
echo "\n4. تعيين الأذونات...\n";
$directories = ['storage', 'bootstrap/cache', 'storage/logs', 'storage/framework'];
foreach ($directories as $dir) {
    if (is_dir($dir)) {
        chmod($dir, 0755);
        echo "✅ تم تعيين أذونات $dir\n";
    } else {
        if (mkdir($dir, 0755, true)) {
            echo "✅ تم إنشاء وتعيين أذونات $dir\n";
        }
    }
}

// Step 5: Create storage directories
echo "\n5. إنشاء مجلدات التخزين...\n";
$storageDirs = [
    'storage/app',
    'storage/app/public',
    'storage/framework',
    'storage/framework/cache',
    'storage/framework/cache/data',
    'storage/framework/sessions',
    'storage/framework/views',
    'storage/logs'
];

foreach ($storageDirs as $dir) {
    if (!is_dir($dir)) {
        if (mkdir($dir, 0755, true)) {
            echo "✅ تم إنشاء $dir\n";
        }
    } else {
        chmod($dir, 0755);
        echo "✅ $dir موجود\n";
    }
}

// Step 6: Create basic bootstrap cache
echo "\n6. إنشاء ملفات Bootstrap...\n";
if (!is_dir('bootstrap/cache')) {
    mkdir('bootstrap/cache', 0755, true);
}

// Create empty cache files to prevent errors
$cacheFiles = [
    'bootstrap/cache/packages.php' => '<?php return [];',
    'bootstrap/cache/services.php' => '<?php return [];',
];

foreach ($cacheFiles as $file => $content) {
    if (!file_exists($file)) {
        file_put_contents($file, $content);
        echo "✅ تم إنشاء $file\n";
    }
}

// Step 7: Disable problematic service providers temporarily
echo "\n7. إصلاح مقدمي الخدمة...\n";
if (file_exists('bootstrap/providers.php')) {
    $providers = file_get_contents('bootstrap/providers.php');
    // Remove any problematic providers temporarily
    $providers = str_replace('App\Providers\IgnitionServiceProvider::class,', '// App\Providers\IgnitionServiceProvider::class,', $providers);
    file_put_contents('bootstrap/providers.php', $providers);
    echo "✅ تم تعطيل مقدمي الخدمة المشكلة مؤقتاً\n";
}

// Step 8: Create a simple index.php fallback
echo "\n8. إنشاء نسخة احتياطية من index.php...\n";
if (file_exists('public/index.php')) {
    $indexContent = file_get_contents('public/index.php');
    // Add error handling
    $errorHandler = '<?php
// Emergency error handling
ini_set("display_errors", 0);
error_reporting(0);

try {
';
    $indexContent = str_replace('<?php', $errorHandler, $indexContent);
    $indexContent .= '
} catch (Exception $e) {
    echo "<h1>MaxCon SaaS - Maintenance Mode</h1>";
    echo "<p>The application is currently being configured. Please try again in a few minutes.</p>";
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    exit;
} catch (Error $e) {
    echo "<h1>MaxCon SaaS - Maintenance Mode</h1>";
    echo "<p>The application is currently being configured. Please try again in a few minutes.</p>";
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    exit;
}
';
    file_put_contents('public/index.emergency.php', $indexContent);
    echo "✅ تم إنشاء نسخة احتياطية من index.php\n";
}

// Step 9: Test basic PHP functionality
echo "\n9. اختبار PHP الأساسي...\n";
if (function_exists('highlight_file')) {
    echo "✅ highlight_file() متوفر\n";
} else {
    echo "⚠️ highlight_file() غير متوفر - قد تحتاج لتفعيل PHP extensions\n";
}

if (extension_loaded('gd')) {
    echo "✅ GD extension متوفر\n";
} else {
    echo "⚠️ GD extension غير متوفر\n";
}

// Step 10: Instructions
echo "\n" . str_repeat("=", 60) . "\n";
echo "🎉 تم الانتهاء من الإصلاح الطارئ!\n";
echo str_repeat("=", 60) . "\n\n";

echo "📋 الخطوات التالية:\n\n";
echo "1. تحديث إعدادات قاعدة البيانات في .env:\n";
echo "   - DB_DATABASE=your_actual_database_name\n";
echo "   - DB_USERNAME=your_actual_username\n";
echo "   - DB_PASSWORD=your_actual_password\n\n";

echo "2. تشغيل الأوامر التالية:\n";
echo "   composer install --no-dev --optimize-autoloader\n";
echo "   php artisan migrate --force\n";
echo "   php artisan storage:link\n\n";

echo "3. في حالة استمرار المشاكل:\n";
echo "   - تحقق من PHP extensions في Cloudways\n";
echo "   - تأكد من تفعيل: gd, xml, mbstring, curl\n";
echo "   - راجع سجلات الأخطاء\n\n";

echo "4. للاختبار:\n";
echo "   - زر الموقع في المتصفح\n";
echo "   - إذا ظهرت أخطاء، استخدم: public/index.emergency.php\n\n";

echo "✅ MaxCon SaaS جاهز للاختبار!\n";
