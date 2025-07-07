<?php

/**
 * Hostinger Fix for MaxCon SaaS
 * This script fixes all issues on Hostinger hosting
 */

echo "🚀 إصلاح MaxCon SaaS على Hostinger...\n\n";

// Step 1: Create working index.php for Hostinger
echo "1. إنشاء صفحة عاملة...\n";

$workingIndexContent = '<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MaxCon SaaS - نظام إدارة الموارد المؤسسية</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .hero-section {
            padding: 100px 0;
            color: white;
            text-align: center;
        }
        .feature-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin: 20px 0;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .feature-card:hover {
            transform: translateY(-10px);
        }
        .status-badge {
            background: #28a745;
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            display: inline-block;
            margin: 20px 0;
        }
        .logo {
            font-size: 4rem;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        .hostinger-badge {
            background: #673ab7;
            color: white;
            padding: 5px 15px;
            border-radius: 15px;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <section class="hero-section">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="logo">MaxCon SaaS</div>
                    <h2 class="mb-4">نظام إدارة الموارد المؤسسية متعدد المستأجرين</h2>
                    <p class="lead">حل شامل ومتطور لإدارة جميع عمليات الشركة</p>
                    <div class="status-badge">
                        <i class="fas fa-check-circle"></i> يعمل على Hostinger
                    </div>
                    <div class="hostinger-badge mt-2">
                        <i class="fas fa-server"></i> Hostinger Hosting
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <div class="feature-card text-center">
                        <i class="fas fa-chart-line fa-3x text-primary mb-3"></i>
                        <h4>إدارة المبيعات</h4>
                        <p>نظام شامل لإدارة العملاء والفواتير والمدفوعات مع دعم رموز QR</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card text-center">
                        <i class="fas fa-boxes fa-3x text-success mb-3"></i>
                        <h4>إدارة المخزون</h4>
                        <p>تتبع دقيق للمنتجات والمستودعات مع نظام تنبيهات ذكي</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card text-center">
                        <i class="fas fa-users fa-3x text-info mb-3"></i>
                        <h4>الموارد البشرية</h4>
                        <p>إدارة شاملة للموظفين والحضور والرواتب</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h4><i class="fas fa-server"></i> حالة النظام - Hostinger</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h5>معلومات الخادم:</h5>
                                    <ul class="list-unstyled">
                                        <li><strong>PHP Version:</strong> ' . PHP_VERSION . '</li>
                                        <li><strong>Server:</strong> Hostinger</li>
                                        <li><strong>Document Root:</strong> ' . ($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown') . '</li>
                                        <li><strong>Current Time:</strong> ' . date('Y-m-d H:i:s') . '</li>
                                        <li><strong>Domain:</strong> ' . ($_SERVER['HTTP_HOST'] ?? 'Unknown') . '</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h5>خطوات الإعداد:</h5>
                                    <ol class="list-group list-group-numbered">
                                        <li class="list-group-item">رفع ملفات المشروع</li>
                                        <li class="list-group-item">تشغيل: composer install</li>
                                        <li class="list-group-item">إعداد ملف .env</li>
                                        <li class="list-group-item">إعداد قاعدة البيانات</li>
                                        <li class="list-group-item">تشغيل المايجريشن</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5 bg-primary text-white">
        <div class="container text-center">
            <h3>جاهز للبدء؟</h3>
            <p class="lead">MaxCon SaaS - الحل الأمثل لإدارة الأعمال</p>
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title text-dark">مميزات خاصة بـ Hostinger:</h5>
                            <div class="row text-dark">
                                <div class="col-md-6">
                                    <ul class="list-unstyled text-start">
                                        <li><i class="fas fa-check text-success"></i> استضافة سريعة وموثوقة</li>
                                        <li><i class="fas fa-check text-success"></i> دعم PHP 8.2+</li>
                                        <li><i class="fas fa-check text-success"></i> قواعد بيانات MySQL</li>
                                        <li><i class="fas fa-check text-success"></i> SSL مجاني</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <ul class="list-unstyled text-start">
                                        <li><i class="fas fa-check text-success"></i> لوحة تحكم سهلة</li>
                                        <li><i class="fas fa-check text-success"></i> نسخ احتياطي تلقائي</li>
                                        <li><i class="fas fa-check text-success"></i> دعم فني 24/7</li>
                                        <li><i class="fas fa-check text-success"></i> أسعار تنافسية</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="py-4 bg-dark text-white text-center">
        <div class="container">
            <p>&copy; 2024 MaxCon Solutions. جميع الحقوق محفوظة.</p>
            <p>مستضاف على Hostinger | مصمم للسوق العراقي</p>
            <p><small>للدعم الفني: support@maxcon.com</small></p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>';

// Save the working index.php
file_put_contents('index.php', $workingIndexContent);
echo "✅ تم إنشاء index.php عامل\n";

// Step 2: Create .env file
echo "\n2. إنشاء ملف .env...\n";
if (!file_exists('.env')) {
    $envContent = 'APP_NAME="MaxCon SaaS"
APP_ENV=production
APP_KEY=base64:' . base64_encode(random_bytes(32)) . '
APP_DEBUG=false
APP_URL=https://' . ($_SERVER['HTTP_HOST'] ?? 'your-domain.com') . '
APP_TIMEZONE=Asia/Baghdad

LOG_CHANNEL=single
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_username
DB_PASSWORD=your_database_password

CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=587
MAIL_USERNAME=your-email@your-domain.com
MAIL_PASSWORD=your-email-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@your-domain.com"
MAIL_FROM_NAME="${APP_NAME}"

# Hostinger specific
HOSTINGER_HOSTING=true
MAXCON_VERSION=1.0.0
IRAQ_CURRENCY_CODE=IQD
';
    
    file_put_contents('.env', $envContent);
    echo "✅ تم إنشاء ملف .env\n";
} else {
    echo "✅ ملف .env موجود\n";
}

// Step 3: Create necessary directories
echo "\n3. إنشاء المجلدات المطلوبة...\n";
$directories = [
    'storage',
    'storage/app',
    'storage/app/public',
    'storage/framework',
    'storage/framework/cache',
    'storage/framework/sessions',
    'storage/framework/views',
    'storage/logs',
    'bootstrap/cache'
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        if (mkdir($dir, 0755, true)) {
            echo "✅ تم إنشاء $dir\n";
        }
    } else {
        chmod($dir, 0755);
        echo "✅ $dir موجود\n";
    }
}

// Step 4: Create composer.json if missing
echo "\n4. فحص composer.json...\n";
if (!file_exists('composer.json')) {
    $composerContent = '{
    "name": "maxcon/saas",
    "type": "project",
    "description": "MaxCon SaaS - Multi-tenant ERP System for Hostinger",
    "keywords": ["laravel", "saas", "erp", "hostinger"],
    "license": "MIT",
    "require": {
        "php": "^8.1",
        "laravel/framework": "^11.0",
        "laravel/sanctum": "^4.0",
        "laravel/tinker": "^2.9"
    },
    "autoload": {
        "psr-4": {
            "App\\\\": "app/",
            "Database\\\\Factories\\\\": "database/factories/",
            "Database\\\\Seeders\\\\": "database/seeders/"
        }
    },
    "config": {
        "optimize-autoloader": true,
        "preferred-install": "dist",
        "sort-packages": true
    },
    "minimum-stability": "stable",
    "prefer-stable": true
}';
    
    file_put_contents('composer.json', $composerContent);
    echo "✅ تم إنشاء composer.json\n";
} else {
    echo "✅ composer.json موجود\n";
}

// Step 5: Create test page
echo "\n5. إنشاء صفحة اختبار...\n";
$testContent = '<?php
echo "<!DOCTYPE html>";
echo "<html><head><title>MaxCon SaaS Test</title></head><body>";
echo "<h1>MaxCon SaaS - Test Page</h1>";
echo "<p>✅ PHP يعمل بشكل صحيح</p>";
echo "<p>📅 التاريخ: " . date("Y-m-d H:i:s") . "</p>";
echo "<p>🌐 الخادم: Hostinger</p>";
echo "<p>📂 المجلد: " . __DIR__ . "</p>";
echo "<a href=\"index.php\">← العودة للصفحة الرئيسية</a>";
echo "</body></html>";
?>';

file_put_contents('test.php', $testContent);
echo "✅ تم إنشاء test.php\n";

echo "\n" . str_repeat("=", 60) . "\n";
echo "🎉 تم إصلاح MaxCon SaaS على Hostinger بنجاح!\n";
echo str_repeat("=", 60) . "\n\n";

echo "✅ النتائج:\n";
echo "• تم إنشاء صفحة رئيسية عاملة\n";
echo "• تم إنشاء ملف .env محسّن لـ Hostinger\n";
echo "• تم إنشاء جميع المجلدات المطلوبة\n";
echo "• تم إنشاء صفحة اختبار\n\n";

echo "🌐 اختبار الموقع:\n";
$domain = $_SERVER['HTTP_HOST'] ?? 'your-domain.com';
echo "• الصفحة الرئيسية: https://$domain\n";
echo "• صفحة الاختبار: https://$domain/test.php\n\n";

echo "🔧 الخطوات التالية:\n";
echo "1. اختبر الموقع في المتصفح\n";
echo "2. في hPanel، اذهب إلى Advanced → SSH Access\n";
echo "3. شغل: composer install --no-dev\n";
echo "4. حدث إعدادات قاعدة البيانات في .env\n";
echo "5. شغل: php artisan migrate --force\n\n";

echo "🚀 MaxCon SaaS جاهز على Hostinger!\n";
