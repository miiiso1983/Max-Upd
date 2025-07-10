<?php
/**
 * صفحة فهرس مؤقتة لـ MaxCon SaaS
 * تحتوي على روابط لجميع الصفحات المتاحة
 */
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MaxCon SaaS - الصفحة الرئيسية</title>
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
            padding: 20px;
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }
        
        .logo {
            font-size: 3em;
            font-weight: bold;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .subtitle {
            font-size: 1.3em;
            opacity: 0.9;
        }
        
        .content {
            padding: 40px;
        }
        
        .status-alert {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .status-alert h3 {
            color: #856404;
            margin-bottom: 10px;
        }
        
        .status-alert p {
            color: #856404;
            line-height: 1.6;
        }
        
        .pages-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        
        .page-card {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        
        .page-card:hover {
            transform: translateY(-5px);
            border-color: #667eea;
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.15);
        }
        
        .page-icon {
            font-size: 3em;
            margin-bottom: 15px;
            display: block;
        }
        
        .page-title {
            font-size: 1.3em;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        
        .page-description {
            color: #666;
            margin-bottom: 20px;
            line-height: 1.5;
        }
        
        .page-link {
            background: #667eea;
            color: white;
            padding: 12px 25px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-block;
            font-weight: 500;
            transition: background 0.3s ease;
        }
        
        .page-link:hover {
            background: #5a6fd8;
        }
        
        .page-link.secondary {
            background: #6c757d;
        }
        
        .page-link.secondary:hover {
            background: #5a6268;
        }
        
        .page-link.success {
            background: #28a745;
        }
        
        .page-link.success:hover {
            background: #218838;
        }
        
        .tools-section {
            background: #e3f2fd;
            border-radius: 15px;
            padding: 30px;
            margin: 30px 0;
        }
        
        .tools-section h3 {
            color: #1976d2;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .tools-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .tool-item {
            background: white;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
        }
        
        .footer {
            background: #333;
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .footer p {
            margin: 5px 0;
        }
        
        .quick-access {
            background: #d4edda;
            border-radius: 15px;
            padding: 25px;
            margin: 20px 0;
            text-align: center;
        }
        
        .quick-access h3 {
            color: #155724;
            margin-bottom: 15px;
        }
        
        .quick-links {
            display: flex;
            justify-content: center;
            gap: 15px;
            flex-wrap: wrap;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">MaxCon SaaS</div>
            <div class="subtitle">نظام إدارة الموارد المؤسسية متعدد المستأجرين</div>
        </div>
        
        <div class="content">
            <div class="status-alert">
                <h3>🎉 النظام يعمل بنجاح!</h3>
                <p>تم حل مشكلة 401 وأصبح بإمكانك الوصول إلى جميع صفحات النظام. هذه صفحة مؤقتة تحتوي على روابط لجميع الوظائف المتاحة.</p>
            </div>
            
            <div class="quick-access">
                <h3>🚀 الوصول السريع</h3>
                <div class="quick-links">
                    <a href="login-temp.php" class="page-link success">🔐 تسجيل الدخول</a>
                    <a href="representatives-temp.php" class="page-link">👥 مندوبو المبيعات</a>
                    <a href="test-db.php" class="page-link secondary">🔍 اختبار قاعدة البيانات</a>
                </div>
            </div>
            
            <div class="pages-grid">
                <div class="page-card">
                    <span class="page-icon">🔐</span>
                    <div class="page-title">تسجيل الدخول</div>
                    <div class="page-description">صفحة تسجيل دخول مؤقتة مع بيانات افتراضية للوصول إلى النظام</div>
                    <a href="login-temp.php" class="page-link">دخول النظام</a>
                </div>
                
                <div class="page-card">
                    <span class="page-icon">👥</span>
                    <div class="page-title">مندوبو المبيعات</div>
                    <div class="page-description">عرض قائمة مندوبي المبيعات مع الإحصائيات والأهداف الشهرية</div>
                    <a href="representatives-temp.php" class="page-link">عرض المندوبين</a>
                </div>
                
                <div class="page-card">
                    <span class="page-icon">🔍</span>
                    <div class="page-title">اختبار قاعدة البيانات</div>
                    <div class="page-description">أداة لاختبار الاتصال بقاعدة البيانات والتحقق من الإعدادات</div>
                    <a href="test-db.php" class="page-link secondary">اختبار الاتصال</a>
                </div>
                
                <div class="page-card">
                    <span class="page-icon">ℹ️</span>
                    <div class="page-title">معلومات PHP</div>
                    <div class="page-description">عرض معلومات خادم PHP والإعدادات والامتدادات المثبتة</div>
                    <a href="info.php" class="page-link secondary">معلومات الخادم</a>
                </div>
                
                <div class="page-card">
                    <span class="page-icon">🏠</span>
                    <div class="page-title">الصفحة الرئيسية</div>
                    <div class="page-description">صفحة الترحيب الأساسية مع معلومات النظام والميزات</div>
                    <a href="index.bypass.php" class="page-link">الصفحة الرئيسية</a>
                </div>
                
                <div class="page-card">
                    <span class="page-icon">📱</span>
                    <div class="page-title">تطبيق الموبايل</div>
                    <div class="page-description">معلومات حول تطبيق Flutter للمندوبين (قيد التطوير)</div>
                    <a href="#" class="page-link secondary">قريباً</a>
                </div>
            </div>
            
            <div class="tools-section">
                <h3>🛠️ أدوات المطور</h3>
                <div class="tools-grid">
                    <div class="tool-item">
                        <h4>🔧 Laravel Artisan</h4>
                        <p>أوامر Laravel للصيانة</p>
                    </div>
                    <div class="tool-item">
                        <h4>📊 قاعدة البيانات</h4>
                        <p>إدارة الجداول والبيانات</p>
                    </div>
                    <div class="tool-item">
                        <h4>🔐 الأمان</h4>
                        <p>إعدادات الحماية والصلاحيات</p>
                    </div>
                    <div class="tool-item">
                        <h4>📝 السجلات</h4>
                        <p>مراقبة أخطاء النظام</p>
                    </div>
                </div>
            </div>
            
            <div style="background: #f8f9fa; border-radius: 15px; padding: 25px; text-align: center;">
                <h3 style="color: #333; margin-bottom: 15px;">📋 معلومات مهمة</h3>
                <p style="color: #666; line-height: 1.6;">
                    <strong>بيانات تسجيل الدخول الافتراضية:</strong><br>
                    البريد الإلكتروني: <code>admin@maxcon-erp.com</code><br>
                    كلمة المرور: <code>MaxCon@2025</code>
                </p>
                <p style="color: #666; margin-top: 15px; font-size: 0.9em;">
                    هذه صفحات مؤقتة للاختبار. بمجرد حل مشاكل Laravel، ستعمل الصفحات الأصلية بشكل طبيعي.
                </p>
            </div>
        </div>
        
        <div class="footer">
            <p><strong>MaxCon SaaS</strong> - نظام إدارة الموارد المؤسسية</p>
            <p>مصمم خصيصاً للسوق العراقي | دعم فني 24/7</p>
            <p><small>GitHub: https://github.com/miiiso1983/Max-Upd</small></p>
        </div>
    </div>
</body>
</html>
