<?php
/**
 * فحص امتدادات PHP المطلوبة لـ Laravel
 */

// تعطيل عرض الأخطاء مؤقتاً
error_reporting(E_ALL);
ini_set('display_errors', 1);

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فحص امتدادات PHP - MaxCon SaaS</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .content {
            padding: 30px;
        }
        .check-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            margin: 10px 0;
            border-radius: 8px;
            border-left: 4px solid #ccc;
        }
        .check-item.success {
            background: #d4edda;
            border-left-color: #28a745;
            color: #155724;
        }
        .check-item.error {
            background: #f8d7da;
            border-left-color: #dc3545;
            color: #721c24;
        }
        .check-item.warning {
            background: #fff3cd;
            border-left-color: #ffc107;
            color: #856404;
        }
        .status {
            font-weight: bold;
            padding: 5px 10px;
            border-radius: 4px;
        }
        .status.ok {
            background: #28a745;
            color: white;
        }
        .status.missing {
            background: #dc3545;
            color: white;
        }
        .status.optional {
            background: #ffc107;
            color: #212529;
        }
        .section {
            margin: 30px 0;
        }
        .section h3 {
            color: #333;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }
        .fix-suggestion {
            background: #e3f2fd;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .fix-suggestion h4 {
            color: #1976d2;
            margin-bottom: 15px;
        }
        .code {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 4px;
            padding: 10px;
            font-family: monospace;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔍 فحص امتدادات PHP</h1>
            <p>تشخيص المشكلة وحلول Laravel</p>
        </div>
        
        <div class="content">
            <?php
            // معلومات PHP الأساسية
            echo '<div class="section">';
            echo '<h3>📋 معلومات PHP الأساسية</h3>';
            
            $php_info = [
                'PHP Version' => PHP_VERSION,
                'Server Software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
                'Document Root' => $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown',
                'Script Filename' => $_SERVER['SCRIPT_FILENAME'] ?? 'Unknown',
                'Server Name' => $_SERVER['SERVER_NAME'] ?? 'Unknown'
            ];
            
            foreach ($php_info as $key => $value) {
                echo '<div class="check-item success">';
                echo '<span><strong>' . $key . ':</strong> ' . htmlspecialchars($value) . '</span>';
                echo '</div>';
            }
            echo '</div>';
            
            // فحص الدوال المطلوبة
            echo '<div class="section">';
            echo '<h3>🔧 الدوال المطلوبة</h3>';
            
            $required_functions = [
                'highlight_file' => 'مطلوبة لـ Symfony ErrorHandler',
                'highlight_string' => 'مطلوبة لـ Symfony ErrorHandler',
                'token_get_all' => 'مطلوبة لـ PHP Tokenizer',
                'mb_strlen' => 'مطلوبة لـ Multibyte String',
                'openssl_encrypt' => 'مطلوبة لـ OpenSSL',
                'curl_init' => 'مطلوبة لـ cURL',
                'json_encode' => 'مطلوبة لـ JSON',
                'mysqli_connect' => 'مطلوبة لـ MySQL'
            ];
            
            foreach ($required_functions as $function => $description) {
                $exists = function_exists($function);
                $class = $exists ? 'success' : 'error';
                $status = $exists ? 'ok' : 'missing';
                $icon = $exists ? '✅' : '❌';
                
                echo '<div class="check-item ' . $class . '">';
                echo '<span>' . $icon . ' <strong>' . $function . '()</strong> - ' . $description . '</span>';
                echo '<span class="status ' . $status . '">' . ($exists ? 'موجودة' : 'مفقودة') . '</span>';
                echo '</div>';
            }
            echo '</div>';
            
            // فحص الامتدادات المطلوبة
            echo '<div class="section">';
            echo '<h3>📦 امتدادات PHP المطلوبة</h3>';
            
            $required_extensions = [
                'tokenizer' => 'مطلوب لـ Laravel',
                'mbstring' => 'مطلوب للنصوص متعددة البايت',
                'openssl' => 'مطلوب للتشفير',
                'curl' => 'مطلوب لطلبات HTTP',
                'json' => 'مطلوب لـ JSON',
                'mysqli' => 'مطلوب لـ MySQL',
                'pdo' => 'مطلوب لقاعدة البيانات',
                'xml' => 'مطلوب لـ XML',
                'ctype' => 'مطلوب لفحص الأحرف',
                'fileinfo' => 'مطلوب لفحص الملفات',
                'filter' => 'مطلوب لتنقية البيانات'
            ];
            
            $missing_extensions = [];
            
            foreach ($required_extensions as $extension => $description) {
                $loaded = extension_loaded($extension);
                $class = $loaded ? 'success' : 'error';
                $status = $loaded ? 'ok' : 'missing';
                $icon = $loaded ? '✅' : '❌';
                
                if (!$loaded) {
                    $missing_extensions[] = $extension;
                }
                
                echo '<div class="check-item ' . $class . '">';
                echo '<span>' . $icon . ' <strong>' . $extension . '</strong> - ' . $description . '</span>';
                echo '<span class="status ' . $status . '">' . ($loaded ? 'مثبت' : 'مفقود') . '</span>';
                echo '</div>';
            }
            echo '</div>';
            
            // اختبار highlight_file خصيصاً
            echo '<div class="section">';
            echo '<h3>🎯 اختبار highlight_file خصيصاً</h3>';
            
            if (function_exists('highlight_file')) {
                echo '<div class="check-item success">';
                echo '<span>✅ دالة highlight_file() متوفرة</span>';
                echo '<span class="status ok">يعمل</span>';
                echo '</div>';
                
                // اختبار الدالة
                echo '<div class="check-item success">';
                echo '<span>🧪 اختبار الدالة:</span>';
                echo '</div>';
                
                // إنشاء ملف مؤقت للاختبار
                $test_code = '<?php echo "Hello World"; ?>';
                $temp_file = tempnam(sys_get_temp_dir(), 'test_highlight');
                file_put_contents($temp_file, $test_code);
                
                echo '<div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 10px 0;">';
                echo '<h4>نتيجة highlight_file():</h4>';
                
                ob_start();
                highlight_file($temp_file);
                $highlighted = ob_get_clean();
                
                if ($highlighted) {
                    echo '<div style="border: 1px solid #ddd; padding: 10px; background: white;">';
                    echo $highlighted;
                    echo '</div>';
                    echo '<p style="color: #28a745; font-weight: bold;">✅ الدالة تعمل بشكل صحيح!</p>';
                } else {
                    echo '<p style="color: #dc3545; font-weight: bold;">❌ الدالة لا تعمل بشكل صحيح</p>';
                }
                
                unlink($temp_file);
                echo '</div>';
                
            } else {
                echo '<div class="check-item error">';
                echo '<span>❌ دالة highlight_file() غير متوفرة</span>';
                echo '<span class="status missing">مفقودة</span>';
                echo '</div>';
            }
            echo '</div>';
            
            // حلول المشكلة
            if (!empty($missing_extensions) || !function_exists('highlight_file')) {
                echo '<div class="fix-suggestion">';
                echo '<h4>🛠️ حلول المشكلة</h4>';
                
                if (!function_exists('highlight_file')) {
                    echo '<h5>1. تفعيل امتداد Tokenizer:</h5>';
                    echo '<div class="code">extension=tokenizer</div>';
                    echo '<p>أضف هذا السطر إلى ملف php.ini</p>';
                }
                
                if (!empty($missing_extensions)) {
                    echo '<h5>2. تثبيت الامتدادات المفقودة:</h5>';
                    foreach ($missing_extensions as $ext) {
                        echo '<div class="code">extension=' . $ext . '</div>';
                    }
                }
                
                echo '<h5>3. في Cloudways:</h5>';
                echo '<ul>';
                echo '<li>اذهب إلى Server Management → PHP Settings</li>';
                echo '<li>تأكد من تفعيل جميع الامتدادات المطلوبة</li>';
                echo '<li>أعد تشغيل الخادم</li>';
                echo '</ul>';
                
                echo '<h5>4. حل مؤقت لـ Laravel:</h5>';
                echo '<p>يمكن تعطيل Symfony ErrorHandler مؤقتاً في ملف bootstrap/app.php</p>';
                echo '</div>';
            } else {
                echo '<div class="check-item success">';
                echo '<span>🎉 جميع المتطلبات متوفرة! Laravel يجب أن يعمل بشكل طبيعي.</span>';
                echo '</div>';
            }
            
            // روابط مفيدة
            echo '<div class="section">';
            echo '<h3>🔗 روابط مفيدة</h3>';
            echo '<div style="text-align: center;">';
            echo '<a href="info.php" style="background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;">📋 معلومات PHP كاملة</a> ';
            echo '<a href="test-db.php" style="background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;">🔍 اختبار قاعدة البيانات</a> ';
            echo '<a href="index-temp.php" style="background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;">🏠 الصفحة الرئيسية</a>';
            echo '</div>';
            echo '</div>';
            ?>
        </div>
    </div>
</body>
</html>
