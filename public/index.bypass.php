<?php

/**
 * Emergency Bypass Index for MaxCon SaaS
 * This completely bypasses Laravel to show a working page
 */

// Disable all error reporting for now
error_reporting(0);
ini_set('display_errors', 0);

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MaxCon SaaS - نظام إدارة الموارد المؤسسية</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            padding: 40px;
            max-width: 600px;
            width: 90%;
            text-align: center;
        }
        
        .logo {
            font-size: 3em;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
        }
        
        .subtitle {
            font-size: 1.2em;
            color: #666;
            margin-bottom: 30px;
        }
        
        .status {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .status h3 {
            color: #856404;
            margin-bottom: 10px;
        }
        
        .status p {
            color: #856404;
            line-height: 1.6;
        }
        
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        
        .feature {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            text-align: right;
        }
        
        .feature h4 {
            color: #333;
            margin-bottom: 10px;
            font-size: 1.1em;
        }
        
        .feature p {
            color: #666;
            font-size: 0.9em;
            line-height: 1.5;
        }
        
        .icon {
            font-size: 2em;
            margin-bottom: 10px;
            display: block;
        }
        
        .btn {
            background: #667eea;
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 10px;
            font-size: 1.1em;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 10px;
            transition: all 0.3s ease;
        }
        
        .btn:hover {
            background: #5a6fd8;
            transform: translateY(-2px);
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #666;
            font-size: 0.9em;
        }
        
        .progress {
            background: #e9ecef;
            border-radius: 10px;
            height: 20px;
            margin: 20px 0;
            overflow: hidden;
        }
        
        .progress-bar {
            background: #667eea;
            height: 100%;
            width: 75%;
            border-radius: 10px;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.7; }
            100% { opacity: 1; }
        }
        
        .tech-info {
            background: #e3f2fd;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
            text-align: right;
        }
        
        .tech-info h4 {
            color: #1976d2;
            margin-bottom: 15px;
        }
        
        .tech-list {
            list-style: none;
            padding: 0;
        }
        
        .tech-list li {
            padding: 5px 0;
            color: #333;
        }
        
        .tech-list li:before {
            content: "✓ ";
            color: #4caf50;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">MaxCon SaaS</div>
        <div class="subtitle">نظام إدارة الموارد المؤسسية متعدد المستأجرين</div>
        
        <div class="status">
            <h3>🔧 النظام قيد الإعداد النهائي</h3>
            <p>يتم حالياً إعداد وتكوين النظام للعمل بشكل كامل. هذه الصفحة تؤكد أن الخادم يعمل بشكل صحيح.</p>
            
            <div class="progress">
                <div class="progress-bar"></div>
            </div>
            <p><strong>التقدم: 75% مكتمل</strong></p>
        </div>
        
        <div class="features">
            <div class="feature">
                <span class="icon">💼</span>
                <h4>إدارة المبيعات</h4>
                <p>نظام شامل لإدارة العملاء والفواتير والمدفوعات مع دعم رموز QR</p>
            </div>
            
            <div class="feature">
                <span class="icon">📦</span>
                <h4>إدارة المخزون</h4>
                <p>تتبع المنتجات والمستودعات مع نظام تنبيهات ذكي</p>
            </div>
            
            <div class="feature">
                <span class="icon">👥</span>
                <h4>الموارد البشرية</h4>
                <p>إدارة الموظفين والحضور والرواتب بنظام متطور</p>
            </div>
            
            <div class="feature">
                <span class="icon">📊</span>
                <h4>التقارير المالية</h4>
                <p>تقارير شاملة ولوحات تحكم تفاعلية</p>
            </div>
            
            <div class="feature">
                <span class="icon">🏥</span>
                <h4>الشؤون التنظيمية</h4>
                <p>نظام خاص للامتثال الصيدلاني والتنظيمي</p>
            </div>
            
            <div class="feature">
                <span class="icon">🌍</span>
                <h4>دعم عربي كامل</h4>
                <p>واجهة RTL مع دعم التقويم الهجري والعملة العراقية</p>
            </div>
        </div>
        
        <div class="tech-info">
            <h4>🚀 التقنيات المستخدمة</h4>
            <ul class="tech-list">
                <li>Laravel 11 - إطار العمل الأساسي</li>
                <li>PHP 8.2+ - لغة البرمجة</li>
                <li>MySQL 8.0+ - قاعدة البيانات</li>
                <li>Redis - التخزين المؤقت</li>
                <li>Bootstrap 5 - واجهة المستخدم</li>
                <li>نظام معياري متقدم</li>
                <li>أمان متعدد الطبقات</li>
                <li>نسخ احتياطي تلقائي</li>
            </ul>
        </div>
        
        <?php
        // Show some system information
        echo '<div class="tech-info">';
        echo '<h4>📋 معلومات النظام</h4>';
        echo '<ul class="tech-list">';
        echo '<li>PHP Version: ' . PHP_VERSION . '</li>';
        echo '<li>Server: ' . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . '</li>';
        echo '<li>Document Root: ' . ($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown') . '</li>';
        echo '<li>Current Time: ' . date('Y-m-d H:i:s') . '</li>';
        echo '<li>Server IP: ' . ($_SERVER['SERVER_ADDR'] ?? 'Unknown') . '</li>';
        
        // Check if important files exist
        $files = [
            '../.env' => 'ملف البيئة',
            '../vendor/autoload.php' => 'تبعيات Composer',
            '../artisan' => 'Laravel Artisan'
        ];
        
        foreach ($files as $file => $name) {
            $exists = file_exists($file) ? '✓' : '✗';
            $color = file_exists($file) ? '#4caf50' : '#f44336';
            echo '<li style="color: ' . $color . '">' . $exists . ' ' . $name . '</li>';
        }
        
        echo '</ul>';
        echo '</div>';
        ?>
        
        <div style="margin: 30px 0;">
            <a href="index.php" class="btn">🔄 محاولة تحميل Laravel</a>
            <a href="index.safe.php" class="btn">🛡️ الوضع الآمن</a>
        </div>
        
        <div class="footer">
            <p><strong>MaxCon Solutions</strong> - حلول إدارة الموارد المؤسسية</p>
            <p>مصمم خصيصاً للسوق العراقي | دعم فني 24/7</p>
            <p><small>للدعم الفني: support@maxcon.com | GitHub: MaxCon-SaaS</small></p>
        </div>
    </div>
    
    <script>
        // Auto-refresh every 30 seconds to check if Laravel is working
        setTimeout(function() {
            // Try to load the main Laravel app
            fetch('index.php')
                .then(response => {
                    if (response.ok && !response.url.includes('index.bypass.php')) {
                        // Laravel is working, redirect
                        window.location.href = 'index.php';
                    }
                })
                .catch(error => {
                    console.log('Laravel still not ready:', error);
                });
        }, 30000);
        
        // Add some interactivity
        document.querySelectorAll('.feature').forEach(feature => {
            feature.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
                this.style.boxShadow = '0 10px 20px rgba(0,0,0,0.1)';
            });
            
            feature.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = 'none';
            });
        });
    </script>
</body>
</html>
