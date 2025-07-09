<?php
/**
 * MaxCon SaaS - Fix Login Credentials Script
 * This script removes pre-filled credentials from login pages
 */

echo "🔧 بدء إصلاح صفحات تسجيل الدخول...\n";

// List of files to fix
$files = [
    'resources/views/auth/simple-login.blade.php',
    'resources/views/simple-login.blade.php',
    'resources/views/auth/test-login.blade.php',
    'resources/views/debug-login.blade.php',
    'public/admin.html'
];

$fixed = 0;
$errors = 0;

foreach ($files as $file) {
    echo "📁 معالجة ملف: $file\n";
    
    if (!file_exists($file)) {
        echo "⚠️  الملف غير موجود: $file\n";
        continue;
    }
    
    $content = file_get_contents($file);
    $originalContent = $content;
    
    // Remove credential display boxes
    $patterns = [
        // Remove credential display divs
        '/<div class="credentials">.*?<\/div>/s',
        '/بيانات Super Admin:.*?MaxCon@2025/s',
        '/بيانات الدخول الافتراضية:.*?MaxCon@2025/s',
        
        // Remove pre-filled email values
        '/value="admin@maxcon-erp\.com"/',
        '/value="admin@demo-pharmacy\.com"/',
        
        // Remove pre-filled password values
        '/value="MaxCon@2025"/',
        '/value="Demo@2025"/',
        
        // Remove credential text at bottom
        '/البريد الإلكتروني: admin@maxcon-erp\.com.*?كلمة المرور: MaxCon@2025/s',
    ];
    
    $replacements = [
        '', // Remove credential divs
        '', // Remove credential text
        '', // Remove default credentials text
        'value=""', // Empty email
        'value=""', // Empty email
        'value=""', // Empty password
        'value=""', // Empty password
        '', // Remove bottom credentials
    ];
    
    // Apply fixes
    $content = preg_replace($patterns, $replacements, $content);
    
    // Additional specific fixes for HTML files
    if (strpos($file, '.html') !== false) {
        $content = str_replace('admin@maxcon-erp.com', '', $content);
        $content = str_replace('MaxCon@2025', '', $content);
    }
    
    // Check if content changed
    if ($content !== $originalContent) {
        if (file_put_contents($file, $content)) {
            echo "✅ تم إصلاح: $file\n";
            $fixed++;
        } else {
            echo "❌ فشل في كتابة: $file\n";
            $errors++;
        }
    } else {
        echo "ℹ️  لا يحتاج إصلاح: $file\n";
    }
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "🎉 انتهى الإصلاح!\n";
echo "✅ تم إصلاح: $fixed ملف\n";
echo "❌ أخطاء: $errors ملف\n";

if ($fixed > 0) {
    echo "\n🔧 الخطوات التالية:\n";
    echo "1. امسح الكاش: php artisan cache:clear\n";
    echo "2. امسح كاش العروض: php artisan view:clear\n";
    echo "3. اختبر الموقع\n";
    
    // Clear caches automatically
    echo "\n🧹 مسح الكاش تلقائياً...\n";
    
    if (function_exists('exec')) {
        exec('php artisan cache:clear 2>&1', $output1, $return1);
        exec('php artisan view:clear 2>&1', $output2, $return2);
        exec('php artisan config:clear 2>&1', $output3, $return3);
        
        if ($return1 === 0) echo "✅ تم مسح كاش التطبيق\n";
        if ($return2 === 0) echo "✅ تم مسح كاش العروض\n";
        if ($return3 === 0) echo "✅ تم مسح كاش الإعدادات\n";
    } else {
        echo "⚠️  تشغيل الأوامر غير متاح، قم بتشغيلها يدوياً\n";
    }
}

echo "\n🌐 اختبر الموقع الآن:\n";
echo "https://phpstack-1486247-5676575.cloudwaysapps.com/simple-login\n";
echo "\n✨ يجب أن تكون صفحة تسجيل الدخول نظيفة الآن!\n";
?>
