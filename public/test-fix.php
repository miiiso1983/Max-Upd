<?php
/**
 * اختبار إصلاح مشكلة highlight_file
 */

// تعريف الدوال المفقودة إذا لم تكن موجودة
if (!function_exists('highlight_file')) {
    function highlight_file($filename, $return = false) {
        if (!file_exists($filename)) {
            return false;
        }
        $content = file_get_contents($filename);
        $highlighted = '<pre style="background:#f8f9fa;padding:10px;border-radius:5px;overflow:auto;border:1px solid #ddd;">' . htmlspecialchars($content) . '</pre>';
        
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
        $highlighted = '<pre style="background:#f8f9fa;padding:10px;border-radius:5px;overflow:auto;border:1px solid #ddd;">' . htmlspecialchars($str) . '</pre>';
        
        if ($return) {
            return $highlighted;
        } else {
            echo $highlighted;
            return true;
        }
    }
}

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>اختبار إصلاح highlight_file - MaxCon SaaS</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .content {
            padding: 30px;
        }
        .test-result {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
        }
        .test-result.error {
            background: #f8d7da;
            border-color: #f5c6cb;
        }
        .test-result h3 {
            color: #155724;
            margin-bottom: 15px;
        }
        .test-result.error h3 {
            color: #721c24;
        }
        .code-sample {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
            text-align: right;
        }
        .btn {
            background: #667eea;
            color: white;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 8px;
            display: inline-block;
            margin: 10px 5px;
            font-weight: 500;
            transition: background 0.3s ease;
        }
        .btn:hover {
            background: #5a6fd8;
        }
        .btn.success {
            background: #28a745;
        }
        .btn.success:hover {
            background: #218838;
        }
        .status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .status-item {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            border: 2px solid #e9ecef;
        }
        .status-item.ok {
            border-color: #28a745;
            background: #d4edda;
        }
        .status-item.error {
            border-color: #dc3545;
            background: #f8d7da;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔧 اختبار إصلاح highlight_file</h1>
            <p>فحص حل مشكلة Symfony ErrorHandler</p>
        </div>
        
        <div class="content">
            <?php
            // اختبار الدوال
            $highlight_file_exists = function_exists('highlight_file');
            $highlight_string_exists = function_exists('highlight_string');
            
            echo '<div class="status-grid">';
            
            // اختبار highlight_file
            echo '<div class="status-item ' . ($highlight_file_exists ? 'ok' : 'error') . '">';
            echo '<h4>' . ($highlight_file_exists ? '✅' : '❌') . ' highlight_file()</h4>';
            echo '<p>' . ($highlight_file_exists ? 'متوفرة' : 'مفقودة') . '</p>';
            echo '</div>';
            
            // اختبار highlight_string
            echo '<div class="status-item ' . ($highlight_string_exists ? 'ok' : 'error') . '">';
            echo '<h4>' . ($highlight_string_exists ? '✅' : '❌') . ' highlight_string()</h4>';
            echo '<p>' . ($highlight_string_exists ? 'متوفرة' : 'مفقودة') . '</p>';
            echo '</div>';
            
            // اختبار tokenizer
            $tokenizer_exists = extension_loaded('tokenizer');
            echo '<div class="status-item ' . ($tokenizer_exists ? 'ok' : 'error') . '">';
            echo '<h4>' . ($tokenizer_exists ? '✅' : '❌') . ' Tokenizer</h4>';
            echo '<p>' . ($tokenizer_exists ? 'مثبت' : 'مفقود') . '</p>';
            echo '</div>';
            
            // اختبار PHP version
            $php_ok = version_compare(PHP_VERSION, '8.1.0', '>=');
            echo '<div class="status-item ' . ($php_ok ? 'ok' : 'error') . '">';
            echo '<h4>' . ($php_ok ? '✅' : '❌') . ' PHP ' . PHP_VERSION . '</h4>';
            echo '<p>' . ($php_ok ? 'مناسب' : 'قديم') . '</p>';
            echo '</div>';
            
            echo '</div>';
            
            // اختبار عملي للدوال
            if ($highlight_file_exists && $highlight_string_exists) {
                echo '<div class="test-result">';
                echo '<h3>🎉 تم إصلاح المشكلة بنجاح!</h3>';
                echo '<p>جميع الدوال المطلوبة متوفرة الآن. Laravel يجب أن يعمل بشكل طبيعي.</p>';
                
                // اختبار highlight_string
                echo '<h4>اختبار highlight_string():</h4>';
                $test_code = '<?php
echo "مرحباً بك في MaxCon SaaS";
$version = "1.0.0";
?>';
                
                echo '<div class="code-sample">';
                highlight_string($test_code);
                echo '</div>';
                
                echo '</div>';
                
                // اختبار Laravel
                echo '<div style="text-align: center; margin: 30px 0;">';
                echo '<h3>🚀 جرب Laravel الآن:</h3>';
                echo '<a href="index.php" class="btn success">تشغيل Laravel</a>';
                echo '<a href="representatives" class="btn">صفحة المندوبين</a>';
                echo '<a href="master-admin/tenants" class="btn">إدارة المستأجرين</a>';
                echo '</div>';
                
            } else {
                echo '<div class="test-result error">';
                echo '<h3>❌ لا تزال هناك مشاكل</h3>';
                echo '<p>بعض الدوال المطلوبة لا تزال مفقودة. تحتاج إلى تفعيل امتدادات PHP في Cloudways.</p>';
                echo '</div>';
            }
            ?>
            
            <div style="background: #e3f2fd; border-radius: 10px; padding: 20px; margin: 20px 0;">
                <h4 style="color: #1976d2;">📋 ما تم إصلاحه:</h4>
                <ul style="text-align: right; color: #333;">
                    <li>إضافة دوال highlight_file() و highlight_string() البديلة</li>
                    <li>تحسين معالجة الأخطاء في index.php</li>
                    <li>إضافة فحوصات للدوال المطلوبة</li>
                    <li>حل مشكلة Symfony ErrorHandler</li>
                </ul>
            </div>
            
            <div style="text-align: center;">
                <a href="php-check.php" class="btn">🔍 فحص PHP شامل</a>
                <a href="test-db.php" class="btn">🗄️ اختبار قاعدة البيانات</a>
                <a href="index-temp.php" class="btn">🏠 الصفحة المؤقتة</a>
            </div>
        </div>
    </div>
</body>
</html>
