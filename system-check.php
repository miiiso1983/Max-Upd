<?php

/**
 * MaxCon SaaS - System Check Script
 * 
 * This script checks if all requirements are met for running MaxCon SaaS
 */

echo "🔍 فحص متطلبات النظام لـ MaxCon SaaS...\n\n";

$errors = [];
$warnings = [];
$success = [];

// Check PHP version
echo "📋 فحص PHP...\n";
$phpVersion = PHP_VERSION;
if (version_compare($phpVersion, '8.2.0', '>=')) {
    $success[] = "✅ PHP Version: $phpVersion";
} else {
    $errors[] = "❌ PHP Version: $phpVersion (مطلوب 8.2 أو أحدث)";
}

// Check required PHP extensions
$requiredExtensions = [
    'gd' => 'معالجة الصور',
    'xml' => 'معالجة XML',
    'mbstring' => 'دعم Unicode',
    'curl' => 'HTTP requests',
    'zip' => 'ضغط الملفات',
    'pdo_mysql' => 'قاعدة البيانات MySQL',
    'intl' => 'التدويل',
    'json' => 'معالجة JSON',
    'openssl' => 'التشفير',
    'tokenizer' => 'Laravel Tokenizer',
    'fileinfo' => 'معلومات الملفات'
];

echo "\n📦 فحص PHP Extensions...\n";
foreach ($requiredExtensions as $ext => $description) {
    if (extension_loaded($ext)) {
        $success[] = "✅ $ext: متوفر ($description)";
    } else {
        $errors[] = "❌ $ext: مفقود ($description)";
    }
}

// Check important functions
echo "\n🔧 فحص PHP Functions...\n";
$requiredFunctions = [
    'highlight_file' => 'تمييز الكود',
    'exec' => 'تنفيذ الأوامر',
    'shell_exec' => 'تنفيذ Shell',
    'file_get_contents' => 'قراءة الملفات',
    'file_put_contents' => 'كتابة الملفات'
];

foreach ($requiredFunctions as $func => $description) {
    if (function_exists($func)) {
        $success[] = "✅ $func(): متوفر ($description)";
    } else {
        $warnings[] = "⚠️ $func(): مفقود ($description)";
    }
}

// Check file permissions
echo "\n📁 فحص أذونات الملفات...\n";
$directories = [
    'storage' => 'مجلد التخزين',
    'storage/logs' => 'سجلات النظام',
    'storage/framework' => 'ملفات Laravel المؤقتة',
    'bootstrap/cache' => 'ذاكرة Bootstrap المؤقتة'
];

foreach ($directories as $dir => $description) {
    if (is_dir($dir)) {
        if (is_writable($dir)) {
            $success[] = "✅ $dir: قابل للكتابة ($description)";
        } else {
            $errors[] = "❌ $dir: غير قابل للكتابة ($description)";
        }
    } else {
        $warnings[] = "⚠️ $dir: المجلد غير موجود ($description)";
    }
}

// Check important files
echo "\n📄 فحص الملفات المهمة...\n";
$files = [
    '.env' => 'ملف البيئة',
    'composer.json' => 'تبعيات PHP',
    'artisan' => 'Laravel Artisan',
    'public/index.php' => 'نقطة الدخول'
];

foreach ($files as $file => $description) {
    if (file_exists($file)) {
        $success[] = "✅ $file: موجود ($description)";
    } else {
        $errors[] = "❌ $file: مفقود ($description)";
    }
}

// Check .env configuration
echo "\n⚙️ فحص إعدادات .env...\n";
if (file_exists('.env')) {
    $envContent = file_get_contents('.env');
    
    $envChecks = [
        'APP_KEY=' => 'مفتاح التطبيق',
        'DB_DATABASE=' => 'اسم قاعدة البيانات',
        'DB_USERNAME=' => 'مستخدم قاعدة البيانات',
        'DB_PASSWORD=' => 'كلمة مرور قاعدة البيانات'
    ];
    
    foreach ($envChecks as $key => $description) {
        if (strpos($envContent, $key) !== false) {
            $line = '';
            foreach (explode("\n", $envContent) as $envLine) {
                if (strpos($envLine, $key) === 0) {
                    $line = trim($envLine);
                    break;
                }
            }
            
            if (strpos($line, $key) === 0 && strlen($line) > strlen($key)) {
                $success[] = "✅ $key مُعرّف ($description)";
            } else {
                $warnings[] = "⚠️ $key فارغ ($description)";
            }
        } else {
            $errors[] = "❌ $key مفقود ($description)";
        }
    }
} else {
    $errors[] = "❌ ملف .env مفقود";
}

// Check Laravel installation
echo "\n🚀 فحص Laravel...\n";
if (file_exists('vendor/autoload.php')) {
    $success[] = "✅ Composer dependencies مُثبتة";
    
    // Try to load Laravel
    try {
        require_once 'vendor/autoload.php';
        $success[] = "✅ Laravel Autoloader يعمل";
    } catch (Exception $e) {
        $errors[] = "❌ Laravel Autoloader: " . $e->getMessage();
    }
} else {
    $errors[] = "❌ Composer dependencies غير مُثبتة";
}

// Display results
echo "\n" . str_repeat("=", 60) . "\n";
echo "📊 نتائج الفحص\n";
echo str_repeat("=", 60) . "\n\n";

if (!empty($success)) {
    echo "✅ العناصر الصحيحة (" . count($success) . "):\n";
    foreach ($success as $item) {
        echo "   $item\n";
    }
    echo "\n";
}

if (!empty($warnings)) {
    echo "⚠️ التحذيرات (" . count($warnings) . "):\n";
    foreach ($warnings as $item) {
        echo "   $item\n";
    }
    echo "\n";
}

if (!empty($errors)) {
    echo "❌ الأخطاء (" . count($errors) . "):\n";
    foreach ($errors as $item) {
        echo "   $item\n";
    }
    echo "\n";
}

// Final recommendation
echo str_repeat("-", 60) . "\n";
if (empty($errors)) {
    if (empty($warnings)) {
        echo "🎉 ممتاز! النظام جاهز تماماً لتشغيل MaxCon SaaS\n";
    } else {
        echo "✅ النظام جاهز لتشغيل MaxCon SaaS مع بعض التحذيرات\n";
    }
} else {
    echo "🚨 يجب إصلاح الأخطاء قبل تشغيل MaxCon SaaS\n";
    echo "\n💡 الخطوات المقترحة:\n";
    echo "1. قم بتشغيل: chmod +x cloudways-fix.sh && ./cloudways-fix.sh\n";
    echo "2. تحقق من إعدادات PHP في Cloudways\n";
    echo "3. قم بتثبيت التبعيات: composer install\n";
    echo "4. أعد تشغيل هذا الفحص\n";
}

echo str_repeat("=", 60) . "\n";
echo "تم الانتهاء من الفحص\n";

// Return exit code
exit(empty($errors) ? 0 : 1);
